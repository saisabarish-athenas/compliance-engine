<?php

namespace App\Http\Controllers;

use App\Models\ComplianceSection;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use App\Services\Compliance\ComplianceReportBuilder;
use App\Services\Compliance\ComplianceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ComplianceExecutionController extends Controller
{
    public function __construct(
        private ComplianceExecutionService $executionService,
        private ComplianceReportBuilder $reportBuilder,
        private ComplianceEngine $engine,
        private \App\Services\Compliance\ComplianceTimelineService $timelineService
    ) {}

    private function subscription(): string
    {
        return Auth::user()->tenant->subscription_type;
    }

    public function dashboard()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->tenant) {
                abort(500, 'User not authenticated or tenant not assigned');
            }

            $subscription = $this->subscription();
            $tenantId = $user->tenant_id;

            $branch = \App\Models\Branch::where('tenant_id', $tenantId)->first();
            $sections = ComplianceSection::where('is_active', true)->get();
            $batches = ComplianceExecutionBatch::with('section')
                ->where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Calculate dynamic status for each batch based on compliance_generation_logs
            foreach ($batches as $batch) {
                $logs = DB::table('compliance_generation_logs')
                    ->where('batch_id', $batch->id)
                    ->pluck('status');

                if ($logs->isEmpty()) {
                    $batch->display_status = 'Pending';
                } elseif ($logs->contains('processing')) {
                    $batch->display_status = 'Processing';
                } elseif ($logs->every(fn($status) => in_array($status, ['success', 'completed']))) {
                    $batch->display_status = 'Completed';
                } elseif ($logs->every(fn($status) => $status === 'failed')) {
                    $batch->display_status = 'Failed';
                } else {
                    $batch->display_status = 'Partially Completed';
                }
            }

            $healthService = app(\App\Services\Compliance\ComplianceHealthService::class);
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $healthScore = $healthService->calculateScore($tenantId, $currentMonth, $currentYear);

            $timelineMetrics = $this->timelineService->getTimelineMetrics($tenantId, $currentMonth, $currentYear);

            return view('compliance.dashboard', compact('sections', 'batches', 'subscription', 'branch', 'user', 'healthScore', 'timelineMetrics'));
        } catch (\Exception $e) {
            logger()->error('Dashboard Error', ['error' => $e->getMessage()]);
            return view('compliance.dashboard', [
                'sections' => [],
                'batches' => [],
                'subscription' => 'MINIMAL',
                'branch' => null,
                'user' => Auth::user(),
                'healthScore' => null,
                'timelineMetrics' => null,
                'error' => 'Failed to load dashboard: ' . $e->getMessage()
            ]);
        }
    }

    public function forms(string $section)
    {
        try {
            $sectionModel = ComplianceSection::where('section_code', $section)
                ->orWhere('id', $section)
                ->firstOrFail();

            $forms = ComplianceFormsMaster::where('section_id', $sectionModel->id)
                ->where('is_active', true)
                ->get();

            return response()->json($forms);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function createBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'section_id' => 'required|exists:compliance_sections,id',
                'period_month' => 'required|integer|min:1|max:12',
                'period_year' => 'required|integer|min:2020|max:2030',
                'form_ids' => 'required|array',
                'form_ids.*' => 'exists:compliance_forms_master,id',
                'branch_id' => 'nullable|exists:branches,id',
            ]);

            $tenantId = Auth::user()->tenant_id;

            // Calculate period_from and period_to from month/year
            $periodFrom = \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->startOfMonth()->format('Y-m-d');
            $periodTo = \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->endOfMonth()->format('Y-m-d');

            $batch = $this->executionService->createBatch(
                $tenantId,
                $validated['section_id'],
                $periodFrom,
                $periodTo,
                $validated['form_ids'],
                $validated['branch_id'] ?? null
            );

            // Update with month/year
            $batch->update([
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
            ]);

            // Create timeline entries
            $this->timelineService->createTimelineOnBatchCreation(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            return redirect()->route('compliance.dashboard')
                ->with('success', 'Batch created successfully! Batch ID: ' . $batch->id)
                ->with('batch_id', $batch->id)
                ->with('form_ids', $validated['form_ids'])
                ->with('section_id', $validated['section_id']);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to create batch: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function previewForm(int $batch, string $form)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);

            if (Auth::check() && $batchModel->tenant_id !== Auth::user()->tenant_id) {
                abort(403, 'Unauthorized access to batch');
            }

            if ($this->subscription() !== 'FULL') {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'Preview requires FULL subscription.');
            }

            // Resolve branch safely
            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batchModel->tenant_id,
                $batchModel->branch_id
            );

            // Validate context
            \App\Services\Compliance\ComplianceContextValidator::validate(
                $batchModel->tenant_id,
                $branchId,
                $batchModel->period_month,
                $batchModel->period_year
            );

            $formMaster = ComplianceFormsMaster::where('form_code', $form)->firstOrFail();
            $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
            $generator = $factory->make($form);

            $aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);
            $rawData = $aggregator->aggregate(
                $form,
                $batchModel->tenant_id,
                $branchId,
                $batchModel->period_month,
                $batchModel->period_year
            );

            $reflection = new \ReflectionClass($generator);
            $method = $reflection->getMethod('prepareData');
            $method->setAccessible(true);
            $data = $method->invoke($generator, $rawData);

            $data['form_title'] = $formMaster->form_name;
            $data['form_code'] = $form;
            $data['batch_id'] = $batch;
            $data['period_month'] = $batchModel->period_month;
            $data['period_year'] = $batchModel->period_year;

            $viewPath = "compliance.forms.{$form}";

            return view($viewPath, $data);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Preview failed: ' . $e->getMessage());
        }
    }

    public function refreshFormData(int $batch, string $form)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);

            if (Auth::check() && $batchModel->tenant_id !== Auth::user()->tenant_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batchModel->tenant_id,
                $batchModel->branch_id
            );

            $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
            $generator = $factory->make($form);
            $aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);

            $rawData = $aggregator->aggregate(
                $form,
                $batchModel->tenant_id,
                $branchId,
                $batchModel->period_month,
                $batchModel->period_year
            );

            $reflection = new \ReflectionClass($generator);
            $method = $reflection->getMethod('prepareData');
            $method->setAccessible(true);
            $data = $method->invoke($generator, $rawData);

            return response()->json([
                'rows' => $data['rows'] ?? [],
                'totals' => $data['totals'] ?? [],
                'is_nil' => $data['is_nil'] ?? false,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processBatch(int $id)
    {
        try {
            $batch = ComplianceExecutionBatch::findOrFail($id);

            if (Auth::check() && $batch->tenant_id !== Auth::user()->tenant_id) {
                abort(403);
            }

            if ($this->subscription() !== 'FULL') {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'Batch processing requires FULL subscription.');
            }

            $results = $this->executionService->processBatch($id);

            return redirect()->route('compliance.dashboard')
                ->with('success', 'Batch processed successfully!')
                ->with('batch_id', $id)
                ->with('results', $results);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to process batch: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        try {
            $batch = ComplianceExecutionBatch::findOrFail($id);

            if (Auth::check() && $batch->tenant_id !== Auth::user()->tenant_id) {
                abort(403);
            }

            // Generate report if not already generated
            if (!$batch->generated_report_path) {
                $this->reportBuilder->generateFinalReport($id);
                $batch->refresh();
            }

            $path = $batch->generated_report_path;

            // Verify file exists, regenerate once if missing
            if (!Storage::disk('local')->exists($path)) {
                try {
                    $this->reportBuilder->generateFinalReport($id);
                    $batch->refresh();
                    $path = $batch->generated_report_path;
                } catch (\Exception $e) {
                    return redirect()->route('compliance.dashboard')
                        ->with('error', 'Report generation failed. Please try again later.');
                }
                
                if (!Storage::disk('local')->exists($path)) {
                    return redirect()->route('compliance.dashboard')
                        ->with('error', 'Report file could not be generated. Please contact support.');
                }
            }

            // Use Storage facade for download
            return Storage::disk('local')->download($path);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Download failed. Please try again.');
        }
    }

    public function uploadForm(Request $request, int $batchId, int $formId)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'file' => 'required|file|mimes:pdf|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $batch = ComplianceExecutionBatch::find($batchId);
            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch not found'
                ], 404);
            }

            $form = \App\Models\ComplianceFormsMaster::find($formId);
            if (!$form) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Form not found'
                ], 404);
            }

            // Ensure directory exists
            if (!Storage::disk('local')->exists('compliance/manual_uploads')) {
                Storage::disk('local')->makeDirectory('compliance/manual_uploads');
            }

            $file = $request->file('file');
            $fileName = "batch_{$batchId}_{$form->form_code}_" . time() . ".pdf";
            $filePath = $file->storeAs('compliance/manual_uploads', $fileName, 'local');

            DB::table('compliance_manual_uploads')->insert([
                'user_id' => Auth::id(),
                'batch_id' => $batchId,
                'form_code' => $form->form_code,
                'file_path' => $filePath,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file_path' => $filePath
            ]);
        } catch (\Throwable $e) {
            logger()->error('Upload Error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    public function downloadInspectionPack(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);
            $user = Auth::user();

            if ($batchModel->tenant_id !== $user->tenant_id) {
                abort(403, 'Unauthorized access to batch');
            }

            if ($this->subscription() !== 'FULL') {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'Inspection Pack requires FULL subscription.');
            }

            // Resolve branch safely
            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batchModel->tenant_id,
                $batchModel->branch_id
            );

            // Get ALL generated forms for this batch - SECTION-AWARE
            $logs = DB::table('compliance_generation_logs')
                ->where('batch_id', $batch)
                ->where('tenant_id', $batchModel->tenant_id)  // Tenant isolation
                ->where('status', 'success')
                ->whereNotNull('generated_file_path')
                ->get();

            // Debug log
            logger()->info('Inspection Pack Request', [
                'batch_id' => $batch,
                'tenant_id' => $batchModel->tenant_id,
                'forms_found' => $logs->count()
            ]);

            if ($logs->isEmpty()) {
                return redirect()->route('compliance.dashboard')
                    ->with('error', 'No generated forms found in this batch. Please process the batch first.');
            }

            $zipFileName = "inspection_pack_batch_{$batch}_" . time() . ".zip";
            $zipPath = storage_path("app/temp/{$zipFileName}");

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Failed to create ZIP file');
            }

            $includedForms = [];
            $missingForms = [];

            // Add ALL generated PDFs to ZIP
            foreach ($logs as $log) {
                if ($log->generated_file_path && Storage::disk('local')->exists($log->generated_file_path)) {
                    $fileName = "{$log->form_code}.pdf";
                    $zip->addFile(
                        Storage::disk('local')->path($log->generated_file_path),
                        $fileName
                    );
                    $includedForms[] = $log->form_code;
                } else {
                    $missingForms[] = $log->form_code;
                }
            }

            // Generate summary report
            $aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);
            $tenantDetails = $aggregator->getTenantDetails($batchModel->tenant_id);
            $branchDetails = $aggregator->getBranchDetails($branchId);

            $summaryContent = "═══════════════════════════════════════════════════════\n";
            $summaryContent .= "  INSPECTION PACK SUMMARY\n";
            $summaryContent .= "═══════════════════════════════════════════════════════\n\n";
            $summaryContent .= "Organization: {$tenantDetails['name']}\n";
            $summaryContent .= "Branch: {$branchDetails['name']}\n";
            $summaryContent .= "Address: {$branchDetails['address']}\n";
            $summaryContent .= "Period: " . \Carbon\Carbon::create($batchModel->period_year, $batchModel->period_month, 1)->format('F Y') . "\n";
            $summaryContent .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
            $summaryContent .= "Batch ID: {$batch}\n\n";
            $summaryContent .= "═══════════════════════════════════════════════════════\n";
            $summaryContent .= "  INCLUDED FORMS ({" . count($includedForms) . "})\n";
            $summaryContent .= "═══════════════════════════════════════════════════════\n\n";

            foreach ($includedForms as $formCode) {
                $summaryContent .= "✓ {$formCode}.pdf\n";
            }

            if (!empty($missingForms)) {
                $summaryContent .= "\n═══════════════════════════════════════════════════════\n";
                $summaryContent .= "  MISSING FORMS ({" . count($missingForms) . "})\n";
                $summaryContent .= "═══════════════════════════════════════════════════════\n\n";
                foreach ($missingForms as $formCode) {
                    $summaryContent .= "✗ {$formCode}.pdf (File not found)\n";
                }
            }

            $summaryContent .= "\n═══════════════════════════════════════════════════════\n";
            $summaryContent .= "  VERIFICATION\n";
            $summaryContent .= "═══════════════════════════════════════════════════════\n\n";
            $summaryContent .= "This inspection pack contains all statutory compliance\n";
            $summaryContent .= "forms as required under Tamil Nadu Labour Laws.\n\n";
            $summaryContent .= "For verification, contact:\n";
            $summaryContent .= "Organization: {$tenantDetails['name']}\n";
            $summaryContent .= "Factory License: {$tenantDetails['factory_license_no']}\n";

            $zip->addFromString('INSPECTION_PACK_SUMMARY.txt', $summaryContent);
            $zip->close();

            // Log audit
            DB::table('compliance_audit_logs')->insert([
                'tenant_id' => $batchModel->tenant_id,
                'user_id' => Auth::id(),
                'action' => 'INSPECTION_PACK_DOWNLOADED',
                'form_code' => null,
                'batch_id' => $batch,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => json_encode([
                    'forms_count' => count($includedForms),
                    'missing_count' => count($missingForms),
                ]),
                'created_at' => now(),
            ]);

            /** @var \Illuminate\Contracts\Routing\ResponseFactory $responseFactory */
            $responseFactory = response();
            $response = $responseFactory->download($zipPath, $zipFileName);
            register_shutdown_function(function() use ($zipPath) {
                if (file_exists($zipPath)) {
                    @unlink($zipPath);
                }
            });
            return $response;
        } catch (\Exception $e) {
            logger()->error('Inspection Pack Error', [
                'batch_id' => $batch,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to generate inspection pack: ' . $e->getMessage());
        }
    }

    public function processManualUploads($batchId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $subscription = $user->tenant->subscription_type;

            if ($subscription !== 'MINIMAL') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only minimal plan can process uploads'
                ], 403);
            }

            $uploads = DB::table('compliance_manual_uploads')
                ->where('batch_id', $batchId)
                ->get();

            if ($uploads->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No uploads found for this batch'
                ], 400);
            }

            foreach ($uploads as $upload) {

                DB::table('compliance_generation_logs')->updateOrInsert(
                    [
                        'batch_id' => $batchId,
                        'form_code' => $upload->form_code,
                    ],
                    [
                        'tenant_id' => $user->tenant_id,
                        'status' => 'completed',
                        'generated_file_path' => $upload->file_path,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }

            DB::table('compliance_execution_batches')
                ->where('id', $batchId)
                ->update([
                    'status' => 'processed',
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Uploads processed successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
