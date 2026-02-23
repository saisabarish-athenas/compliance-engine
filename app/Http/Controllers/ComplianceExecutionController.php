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

class ComplianceExecutionController extends Controller
{
    public function __construct(
        private ComplianceExecutionService $executionService,
        private ComplianceReportBuilder $reportBuilder,
        private ComplianceEngine $engine
    ) {}

    public function sections()
    {
        return response()->json(ComplianceSection::where('is_active', true)->get());
    }

    public function forms(string $section)
{
    $sectionModel = ComplianceSection::where('section_code', $section)->firstOrFail();

    $forms = ComplianceFormsMaster::where('section_id', $sectionModel->id)
        ->where('is_active', true)
        ->get();

    return response()->json($forms);
}

    public function createBatch(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:compliance_sections,id',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after:period_from',
            'form_ids' => 'required|array',
            'form_ids.*' => 'exists:compliance_forms_master,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $tenantId = Auth::check() ? Auth::user()->tenant_id : 1;

        $batch = $this->executionService->createBatch(
            $tenantId,
            $validated['section_id'],
            $validated['period_from'],
            $validated['period_to'],
            $validated['form_ids'],
            $validated['branch_id'] ?? null
        );

        return response()->json(['success' => true, 'batch_id' => $batch->id]);
    }

    public function processBatch(int $id)
    {
        $batch = ComplianceExecutionBatch::findOrFail($id);

        if (Auth::check() && $batch->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $results = $this->executionService->processBatch($id);

        return response()->json(['success' => true, 'results' => $results]);
    }

    public function download(int $id)
    {
        $batch = ComplianceExecutionBatch::findOrFail($id);

        if (Auth::check() && $batch->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if (!$batch->generated_report_path) {
            $this->reportBuilder->generateFinalReport($id);
            $batch->refresh();
        }

        return Storage::download($batch->generated_report_path);
    }
}
