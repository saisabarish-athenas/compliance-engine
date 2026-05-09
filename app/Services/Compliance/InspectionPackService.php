<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Models\ComplianceAuditLog;
use Illuminate\Support\Facades\Storage;

class InspectionPackService
{
    public function generateInspectionPack(int $batchId): string
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);

        $forms = ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'success')
            ->get();

        // Filter out forms that failed audit
        $failedForms = ComplianceAuditLog::where('batch_id', $batchId)
            ->where('status', 'failed')
            ->pluck('form_code');

        $forms = $forms->reject(function($form) use ($failedForms) {
            return $failedForms->contains($form->form_code);
        });

        if ($forms->isEmpty()) {
            throw new \Exception('No valid forms available for inspection pack.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = storage_path("app/temp/inspection_{$batchId}.zip");
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
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw new \Exception('No valid files found for inspection pack.');
        }

        return $zipPath;
    }
}
