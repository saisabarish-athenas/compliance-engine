<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use Illuminate\Support\Facades\DB;

class ComplianceExecutionService
{
    public function __construct(
        private ComplianceEngine $engine
    ) {}

    public function createBatch(int $tenantId, int $sectionId, string $periodFrom, string $periodTo, array $formIds, ?int $branchId = null): ComplianceExecutionBatch
    {
        return ComplianceExecutionBatch::create([
            'tenant_id' => $tenantId,
            'section_id' => $sectionId,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'form_ids' => $formIds,
            'branch_id' => $branchId,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        $batch->update(['status' => 'processing']);

        $results = [];
        
        foreach ($batch->form_ids as $formId) {
            try {
                $result = $this->engine->generateForm(
                    $formId,
                    $batch->period_from,
                    $batch->period_to,
                    $batch->branch_id
                );
                $results[$formId] = $result;
            } catch (\Exception $e) {
                $results[$formId] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        $batch->update([
            'status' => 'completed',
            'processed_at' => now(),
            'results' => $results,
        ]);

        return $results;
    }
}
