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
use Illuminate\Support\Facades\Log;


class ComplianceExecutionController extends Controller
{
    public function __construct(
        private ComplianceExecutionService $executionService,
        private ComplianceReportBuilder $reportBuilder,
        private ComplianceEngine $engine,
        private \App\Services\Compliance\ComplianceTimelineService $timelineService,
        private \App\Services\Compliance\Audit\ComplianceAuditService $auditService,
        private \App\Services\Compliance\Audit\ComplianceCorrectionService $correctionService
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

            // Load statutory form grouping
            $statutorySections = config('statutory_form_grouping.sections');
            $formCodeToId = ComplianceFormsMaster::pluck('id', 'form_code')->toArray();

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

                // CRITICAL: Fetch audit logs and calculate score
                $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch->id)->get();

                if ($auditLogs->isNotEmpty()) {
                    $batch->audit_score = round($auditLogs->avg('audit_score'));
                    $passedCount = $auditLogs->where('status', 'passed')->count();
                    $totalCount = $auditLogs->count();

                    if ($passedCount === $totalCount) {
                        $batch->audit_status = 'Passed';
                    } elseif ($passedCount === 0) {
                        $batch->audit_status = 'Failed';
                    } else {
                        $batch->audit_status = 'Partial';
                    }
                    $batch->audit_logs = $auditLogs;
                } else {
                    $batch->audit_score = null;
                    $batch->audit_status = 'Not Audited';
                    $batch->audit_logs = collect();
                }

                // CRITICAL: Fetch certification status
                $certLog = DB::table('compliance_certification_logs')
                    ->where('batch_id', $batch->id)
                    ->where('form_code', 'BATCH_SUMMARY')
                    ->first();

                if ($certLog) {
                    $batch->certification_score = $certLog->certification_score;
                    $batch->certification_status = $certLog->certified ? 'Certified' : 'Not Certified';
                } else {
                    $batch->certification_score = null;
                    $batch->certification_status = 'Not Certified';
                }
            }

            $healthService = app(\App\Services\Compliance\ComplianceHealthService::class);
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $healthScore = $healthService->calculateScore($tenantId, $currentMonth, $currentYear);

            $timelineMetrics = $this->timelineService->getTimelineMetrics($tenantId, $currentMonth, $currentYear);

            return view('compliance.dashboard', compact('sections', 'batches', 'subscription', 'branch', 'user', 'healthScore', 'timelineMetrics', 'statutorySections', 'formCodeToId'));
        } catch (\Exception $e) {
            logger()->error('Dashboard Error', ['error' => $e->getMessage()]);
            $statutorySections = config('statutory_form_grouping.sections');
            $formCodeToId = ComplianceFormsMaster::pluck('id', 'form_code')->toArray();

            return view('compliance.dashboard', [
                'sections' => [],
                'batches' => [],
                'subscription' => 'MINIMAL',
                'branch' => null,
                'user' => Auth::user(),
                'healthScore' => null,
                'timelineMetrics' => null,
                'statutorySections' => $statutorySections,
                'formCodeToId' => $formCodeToId,
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
            $tenantId = Auth::user()->tenant_id;

            $validated = $request->validate([
                'statutory_section' => 'required|string',
                'period_month' => 'required|integer|min:1|max:12',
                'period_year' => 'required|integer|min:2020|max:2030',
                'form_ids' => 'required|array',
                'form_ids.*' => 'exists:compliance_forms_master,id',
                'branch_id' => 'nullable|exists:branches,id',
            ]);

            $branch = \App\Models\Branch::where('tenant_id', $tenantId)->first();
            if (!$branch) {
                throw new \Exception("No branch configured for this tenant.");
            }

            $validated['branch_id'] = $validated['branch_id'] ?? $branch->id;
            $statutorySections = config('statutory_form_grouping.sections');
            $sectionKey = $validated['statutory_section'];

            if (!isset($statutorySections[$sectionKey])) {
                throw new \Exception('Invalid statutory section');
            }

            $sectionTitle = $statutorySections[$sectionKey]['title'];

            // Find or create a section in the database for this statutory section
            $section = ComplianceSection::firstOrCreate(
                ['section_code' => $sectionKey],
                ['section_name' => $sectionTitle, 'is_active' => true]
            );

            // Calculate period_from and period_to from month/year
            $periodFrom = \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->startOfMonth()->format('Y-m-d');
            $periodTo = \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->endOfMonth()->format('Y-m-d');

            $batch = $this->executionService->createBatch(
                $tenantId,
                $section->id,
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
                ->with('section_id', $section->id);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to create batch: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function previewForm(int $batch, string $form)
    {
        $batchModel = ComplianceExecutionBatch::findOrFail($batch);
        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batchModel->tenant_id,
            $batchModel->branch_id
        );

        $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
        $result = $orchestrator->execute(
            $batchModel->tenant_id,
            $branchId,
            $batchModel->period_month,
            $batchModel->period_year,
            $form,
            'preview',
            $batch
        );

        if ($result['status'] === 'failed') {
            abort(400, $result['error']);
        }

        return response($result['result']['html'])
            ->header('Content-Type', 'text/html; charset=utf-8');
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

            $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
            $result = $orchestrator->execute(
                $batchModel->tenant_id,
                $branchId,
                $batchModel->period_month,
                $batchModel->period_year,
                $form,
                'preview',
                $batch
            );

            if ($result['status'] === 'failed') {
                return response()->json(['error' => $result['error']], 400);
            }

            return response()->json([
                'rows' => $result['result']['rows'] ?? [],
                'totals' => $result['result']['totals'] ?? [],
                'is_nil' => $result['result']['is_nil'] ?? false,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processBatch(int $id)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $id)
                ->firstOrFail();

            $results = $this->executionService->processBatch($batch->id);

            return redirect()->route('compliance.dashboard')
                ->with('success', 'Batch processed successfully!')
                ->with('batch_id', $batch->id)
                ->with('results', $results);
        } catch (\Exception $e) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to process batch: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        // Override standard download to fetch the full Inspection Pack instead
        return $this->downloadInspectionPack($id);
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
            $tenantId = Auth::user()->tenant_id;

            $batchModel = ComplianceExecutionBatch::where('tenant_id', $tenantId)
                ->where('id', $batch)
                ->firstOrFail();

            // PART 7: Check certification status before allowing download
            $certificationService = app(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
            $certificationResult = $certificationService->certifyBatch($batch);

            if (!$certificationResult['certified'] && $certificationResult['score'] < 70) {
                return redirect()->route('compliance.dashboard')
                    ->with('error', "Batch not legally certifiable for generation. Certification Score: {$certificationResult['score']}%. Resolve violations first.")
                    ->with('certification_violations', $certificationResult['violations'])
                    ->with('certification_critical', $certificationResult['critical_errors'] ?? []);
            }

            $forms = \App\Models\ComplianceBatchForm::where('tenant_id', $tenantId)
                ->where('batch_id', $batch)
                ->where('status', 'success')
                ->get();

            // Filter out forms that failed audit
            $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch)
                ->where('status', 'failed')
                ->pluck('form_code');

            $forms = $forms->reject(function ($form) use ($auditLogs) {
                return $auditLogs->contains($form->form_code);
            });

            if ($forms->isEmpty()) {
                abort(422, 'No generated forms stored for this batch.');
            }

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zipPath = storage_path("app/temp/inspection_pack_batch_{$batch}.zip");

            $zip = new \ZipArchive;

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Unable to create inspection ZIP.');
            }

            $addedCount = 0;

            foreach ($forms as $form) {

                if (Storage::disk('local')->exists($form->file_path)) {

                    $absolutePath = Storage::disk('local')->path($form->file_path);

                    $zip->addFile($absolutePath, "{$form->form_code}.pdf");
                    $addedCount++;
                } else {

                    logger()->warning("File missing for inspection pack", [
                        'batch_id' => $batch,
                        'form_code' => $form->form_code,
                        'expected_relative_path' => $form->file_path
                    ]);
                }
            }

            $zip->close();

            if ($addedCount === 0) {
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                abort(422, 'No valid files found for inspection pack.');
            }

            if (!file_exists($zipPath)) {
                throw new \Exception('Inspection ZIP not created.');
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            logger()->error('Inspection Pack Error', [
                'batch_id' => $batch,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Failed to generate inspection pack: ' . $e->getMessage());
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

    public function uploadDataFile(Request $request, int $batchId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if ($user->tenant->subscription_type !== 'MINIMAL') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File uploads are only available for MINIMAL subscriptions.'
                ], 403);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240',
                'dataset_type' => 'required|string|in:employees,payroll,attendance'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $batch = ComplianceExecutionBatch::where('tenant_id', $user->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $file = $request->file('file');
            $datasetType = $request->input('dataset_type');

            $handle = fopen($file->getRealPath(), "r");
            $headers = fgetcsv($handle, 1000, ",");
            $headers = array_map('strtolower', array_map('trim', $headers));

            $recordsInserted = 0;

            DB::beginTransaction();
            try {
                // Clear existing manual data for this batch and dataset type to allow clean re-uploads
                DB::table('compliance_manual_data')
                    ->where('batch_id', $batch->id)
                    ->where('dataset_type', $datasetType)
                    ->delete();

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($headers) == count($data)) {
                        $row = array_combine($headers, $data);

                        DB::table('compliance_manual_data')->insert([
                            'tenant_id' => $user->tenant_id,
                            'batch_id' => $batch->id,
                            'dataset_type' => $datasetType,
                            'data_payload' => json_encode($row),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $recordsInserted++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                throw $e;
            }

            fclose($handle);

            return response()->json([
                'status' => 'success',
                'message' => "Successfully parsed and stored {$recordsInserted} records for {$datasetType}.",
                'records_inserted' => $recordsInserted
            ]);
        } catch (\Exception $e) {
            logger()->error('CSV Upload Error', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reAudit(int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batch->tenant_id,
                $batch->branch_id
            );

            $result = $this->auditService->reAuditForm(
                $formCode,
                $batch->tenant_id,
                $branchId,
                $batch->period_month,
                $batch->period_year,
                $batchId
            );

            if ($result['status'] === 'success') {
                $batchAverageScore = \App\Models\ComplianceAuditLog::where('batch_id', $batchId)
                    ->avg('audit_score');

                $confidenceLabel = $batchAverageScore >= 90 ? 'Inspection Ready' : ($batchAverageScore >= 70 ? 'Moderate Risk – Review Recommended' :
                    'High Risk – Immediate Correction Required');

                return response()->json([
                    'status' => 'success',
                    'form_code' => $formCode,
                    'form_score' => $result['new_score'],
                    'batch_average_score' => round($batchAverageScore),
                    'batch_status' => $result['audit_status'],
                    'violations' => $result['violations'],
                    'confidence_label' => $confidenceLabel,
                ]);
            }

            return response()->json($result, 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function fixViolations(int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $result = $this->correctionService->fixFormViolations($batchId, $formCode);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function submitFix(Request $request, int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'corrections' => 'required|array',
            ]);

            $result = $this->correctionService->fixWithUserInput(
                $batchId,
                $formCode,
                $validated['corrections']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function certifyBatch(int $batchId)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $certificationService = app(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
            $result = $certificationService->certifyBatch($batchId);

            return response()->json([
                'status' => 'success',
                'certified' => $result['certified'],
                'score' => $result['score'],
                'certification_status' => $result['status'],
                'violations' => $result['violations'],
                'warnings' => $result['warnings'],
                'critical_errors' => $result['critical_errors'],
                'form_scores' => $result['form_scores'],
                'message' => $result['message'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCertificationStatus(int $batchId)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $certificationLog = DB::table('compliance_certification_logs')
                ->where('batch_id', $batchId)
                ->where('form_code', 'BATCH_SUMMARY')
                ->first();

            if (!$certificationLog) {
                return response()->json([
                    'status' => 'not_certified',
                    'message' => 'Batch not yet certified'
                ]);
            }

            $violations = json_decode($certificationLog->violations, true);

            return response()->json([
                'status' => 'success',
                'certified' => $certificationLog->certified,
                'score' => $certificationLog->certification_score,
                'violations' => $violations['violations'] ?? [],
                'warnings' => $violations['warnings'] ?? [],
                'critical_errors' => $violations['critical_errors'] ?? [],
                'certified_at' => $certificationLog->certified_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
