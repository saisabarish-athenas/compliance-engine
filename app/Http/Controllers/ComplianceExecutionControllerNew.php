<?php

namespace App\Http\Controllers;

use App\Models\ComplianceSection;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use App\Services\Compliance\ComplianceReportBuilder;
use App\Services\Compliance\ComplianceEngine;
use App\Services\Compliance\ComplianceTimelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ComplianceExecutionController extends Controller
{
    const ACTIVE_TENANT_ID = 1;

    public function __construct(
        private ComplianceExecutionService $executionService,
        private ComplianceReportBuilder $reportBuilder,
        private ComplianceEngine $engine,
        private ComplianceTimelineService $timelineService
    ) {}

    public function dashboard()
    {
        try {
            $user = Auth::user();
            $tenantId = self::ACTIVE_TENANT_ID;
            
            $tenant = DB::table('tenants')->where('id', $tenantId)->first();
            $branch = \App\Models\Branch::where('tenant_id', $tenantId)->first();
            $sections = ComplianceSection::where('is_active', true)->get();
            $batches = ComplianceExecutionBatch::with('section')
                ->where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $healthService = app(\App\Services\Compliance\ComplianceHealthService::class);
            $healthScore = $healthService->calculateScore($tenantId, now()->month, now()->year);
            $timelineMetrics = $this->timelineService->getTimelineMetrics($tenantId, now()->month, now()->year);

            return view('compliance.dashboard', compact('sections', 'batches', 'tenant', 'branch', 'user', 'healthScore', 'timelineMetrics'));
        } catch (\Exception $e) {
            return view('compliance.dashboard', [
                'sections' => [],
                'batches' => [],
                'tenant' => null,
                'branch' => null,
                'user' => Auth::user(),
                'healthScore' => null,
                'timelineMetrics' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createBatch(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:compliance_sections,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030',
            'form_ids' => 'required|array',
            'form_ids.*' => 'exists:compliance_forms_master,id',
        ]);

        $tenantId = self::ACTIVE_TENANT_ID;
        $periodFrom = \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->startOfMonth();
        $periodTo = $periodFrom->copy()->endOfMonth();

        $batch = $this->executionService->createBatch(
            $tenantId,
            $validated['section_id'],
            $periodFrom->format('Y-m-d'),
            $periodTo->format('Y-m-d'),
            $validated['form_ids'],
            null
        );

        $batch->update([
            'period_month' => $validated['period_month'],
            'period_year' => $validated['period_year'],
        ]);

        $this->timelineService->createTimelineOnBatchCreation($tenantId, $validated['period_month'], $validated['period_year']);

        return redirect()->route('compliance.dashboard')
            ->with('success', 'Batch created successfully!')
            ->with('batch_id', $batch->id)
            ->with('form_ids', $validated['form_ids'])
            ->with('section_id', $validated['section_id']);
    }

    public function processBatch(int $id)
    {
        if (Auth::user()->subscription_type !== 'FULL') {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'This feature requires FULL subscription.');
        }

        $results = $this->executionService->processBatch($id);

        return redirect()->route('compliance.dashboard')
            ->with('success', 'Batch processed successfully!')
            ->with('batch_id', $id)
            ->with('results', $results);
    }

    public function downloadInspectionPack(int $batch)
    {
        if (Auth::user()->subscription_type !== 'FULL') {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Inspection Pack requires FULL subscription.');
        }

        $batchModel = ComplianceExecutionBatch::findOrFail($batch);
        $tenantId = self::ACTIVE_TENANT_ID;

        $logs = DB::table('compliance_generation_logs')
            ->where('batch_id', $batch)
            ->where('tenant_id', $tenantId)
            ->where('status', 'success')
            ->whereNotNull('generated_file_path')
            ->get();

        if ($logs->isEmpty()) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'No generated forms found. Please process the batch first.');
        }

        $zipFileName = "inspection_pack_batch_{$batch}_" . time() . ".zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($logs as $log) {
            if ($log->generated_file_path && Storage::disk('local')->exists($log->generated_file_path)) {
                $zip->addFile(
                    Storage::disk('local')->path($log->generated_file_path),
                    "{$log->form_code}.pdf"
                );
            }
        }

        $zip->addFromString('SUMMARY.txt', "Inspection Pack - Batch {$batch}\nGenerated: " . now());
        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function uploadManualForm(Request $request)
    {
        $validated = $request->validate([
            'form_code' => 'required|string',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');
        $fileName = "manual_{$validated['form_code']}_" . time() . ".pdf";
        $filePath = $file->storeAs('compliance/manual_uploads', $fileName, 'local');

        DB::table('compliance_manual_uploads')->insert([
            'user_id' => Auth::id(),
            'form_code' => $validated['form_code'],
            'file_path' => $filePath,
            'uploaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
        ]);
    }
}
