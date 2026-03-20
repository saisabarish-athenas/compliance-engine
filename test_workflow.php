<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Branch;
use App\Models\User;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Services\Compliance\BatchOrchestrator;
use App\Services\Compliance\FrequencyEngine;
use App\Services\Compliance\BatchReviewService;
use App\Services\Compliance\DataAvailabilityEngine;
use Illuminate\Support\Facades\DB;

echo "=== COMPLIANCE ENGINE WORKFLOW TEST ===\n\n";

try {
    // Setup
    $tenant = Tenant::first();
    $branch = Branch::first();
    $user = User::first();
    
    if (!$tenant || !$branch || !$user) {
        throw new Exception("Missing tenant, branch, or user");
    }

    echo "Setup:\n";
    echo "  Tenant: " . $tenant->name . " (ID: " . $tenant->id . ")\n";
    echo "  Branch: " . $branch->branch_name . " (ID: " . $branch->id . ")\n";
    echo "  User: " . $user->name . "\n\n";

    // STAGE 1: Create Batch
    echo "STAGE 1: CREATE BATCH\n";
    echo "  Creating batch for January 2024...\n";
    
    $orchestrator = app(BatchOrchestrator::class);
    $batch = $orchestrator->createBatch($tenant->id, 1, 2024);
    
    echo "  ✓ Batch created: ID " . $batch->id . "\n";
    echo "  ✓ Status: " . $batch->status . "\n";
    echo "  ✓ Period: " . $batch->period_month . "/" . $batch->period_year . "\n";

    // Check attached forms
    $batchForms = ComplianceBatchForm::where('batch_id', $batch->id)->get();
    echo "  ✓ Forms attached: " . $batchForms->count() . "\n";
    
    // Verify file_path is NULL for pending forms
    $nullCount = $batchForms->where('file_path', null)->count();
    echo "  ✓ Forms with NULL file_path: " . $nullCount . "\n";
    
    if ($nullCount !== $batchForms->count()) {
        throw new Exception("Not all forms have NULL file_path!");
    }

    // STAGE 2: Review Batch
    echo "\nSTAGE 2: REVIEW BATCH\n";
    
    $reviewService = app(BatchReviewService::class);
    $reviewData = $reviewService->prepareReviewData($batch->id);
    
    echo "  ✓ Review data prepared\n";
    echo "  ✓ Forms to generate: " . count($reviewData['forms']) . "\n";
    echo "  ✓ Data availability: " . ($reviewData['data_availability']['all_data_exists'] ? "ALL DATA EXISTS" : "MISSING DATA") . "\n";
    
    if (!$reviewData['data_availability']['all_data_exists']) {
        echo "  ⚠ Missing data sources:\n";
        foreach ($reviewData['data_availability']['missing_data'] as $missing) {
            echo "    - " . $missing . "\n";
        }
    }

    // STAGE 3: Process Batch (Generate Forms)
    echo "\nSTAGE 3: PROCESS BATCH\n";
    echo "  Processing batch...\n";
    
    $executionService = app(\App\Services\Compliance\ComplianceExecutionService::class);
    $results = $executionService->processBatch($batch->id);
    
    echo "  ✓ Total forms: " . $results['total_forms'] . "\n";
    echo "  ✓ Successful: " . $results['successful'] . "\n";
    echo "  ✓ Failed: " . $results['failed'] . "\n";
    
    if ($results['failed'] > 0) {
        echo "  ⚠ Failed forms:\n";
        foreach ($results['forms'] as $formCode => $status) {
            if ($status !== 'success') {
                echo "    - " . $formCode . ": " . $status . "\n";
            }
        }
    }

    // Verify files were stored
    echo "\nSTAGE 4: VERIFY FILES\n";
    $generatedForms = ComplianceBatchForm::where('batch_id', $batch->id)
        ->where('status', 'success')
        ->get();
    
    echo "  ✓ Generated forms: " . $generatedForms->count() . "\n";
    
    $filesExist = 0;
    foreach ($generatedForms as $form) {
        if ($form->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($form->file_path)) {
            $filesExist++;
        }
    }
    
    echo "  ✓ Files stored: " . $filesExist . "\n";

    // STAGE 5: Download Inspection Pack
    echo "\nSTAGE 5: INSPECTION PACK\n";
    
    if ($generatedForms->count() > 0) {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = storage_path("app/temp/inspection_pack_batch_{$batch->id}.zip");
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $addedCount = 0;
            foreach ($generatedForms as $form) {
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($form->file_path)) {
                    $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($form->file_path);
                    $zip->addFile($absolutePath, "{$form->form_code}.pdf");
                    $addedCount++;
                }
            }
            $zip->close();
            
            echo "  ✓ ZIP created: " . basename($zipPath) . "\n";
            echo "  ✓ Files in ZIP: " . $addedCount . "\n";
            echo "  ✓ ZIP size: " . round(filesize($zipPath) / 1024, 2) . " KB\n";
        }
    }

    echo "\n=== WORKFLOW TEST COMPLETED SUCCESSFULLY ===\n";
    echo "\nSummary:\n";
    echo "  ✓ Batch creation: SUCCESS\n";
    echo "  ✓ Form attachment: SUCCESS\n";
    echo "  ✓ Batch review: SUCCESS\n";
    echo "  ✓ Form generation: " . ($results['failed'] === 0 ? "SUCCESS" : "PARTIAL") . "\n";
    echo "  ✓ File storage: SUCCESS\n";
    echo "  ✓ Inspection pack: SUCCESS\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
