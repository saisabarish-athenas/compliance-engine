<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use Illuminate\Support\Facades\Storage;

class ComplianceReportBuilder
{
    public function generateFinalReport(int $batchId): string
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        
        $reportData = [
            'batch_id' => $batch->id,
            'section_id' => $batch->section_id,
            'period_from' => $batch->period_from,
            'period_to' => $batch->period_to,
            'branch_id' => $batch->branch_id,
            'results' => $batch->results,
            'generated_at' => now()->toDateTimeString(),
        ];

        $fileName = "batch_report_{$batch->id}_" . time() . ".pdf";
        $filePath = "compliance/reports/{$fileName}";

        Storage::put($filePath . ".json", json_encode($reportData, JSON_PRETTY_PRINT));

        $batch->update(['generated_report_path' => $filePath]);

        return $filePath;
    }
}
