<?php
/**
 * FINAL VERIFICATION - HTTP Endpoint Testing
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\Auth;

echo "=== FINAL VERIFICATION - HTTP ENDPOINT SIMULATION ===\n\n";

$user = \App\Models\User::first();
if (!$user) {
    echo "ERROR: No user found in database\n";
    exit(1);
}

Auth::login($user);

$batch = ComplianceExecutionBatch::first();
if (!$batch) {
    echo "ERROR: No batch found in database\n";
    exit(1);
}

$month = $batch->period_month ?? 1;
$year = $batch->period_year ?? 2024;

echo "Using Batch ID: {$batch->id}\n";
echo "Tenant ID: {$batch->tenant_id}\n";
echo "Branch ID: {$batch->branch_id}\n";
echo "Period: {$month}/{$year}\n\n";

$testForms = [
    'FORM_2', 'FORM_8', 'FORM_17', 'FORM_18', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
    'FORM_XIV', 'FORM_XIX', 'SHOPS_FORM_VI', 'SHOPS_FORM_12', 'SHOPS_FORM_13',
    'SHOPS_FORM_C', 'SHOPS_UNPAID', 'SHOPS_FINES', 'ESI_FORM_12', 'EPF_INSPECTION',
    'FORM_B', 'FORM_10', 'FORM_12', 'FORM_25'
];

$orchestrator = app(ComplianceOrchestrator::class);
$results = [];

echo "Testing Preview Endpoint Simulation:\n";
echo "====================================\n\n";

foreach ($testForms as $formCode) {
    echo "Testing {$formCode}... ";
    
    try {
        $result = $orchestrator->execute(
            $batch->tenant_id,
            $batch->branch_id,
            $month,
            $year,
            $formCode,
            'preview',
            $batch->id
        );
        
        if ($result['status'] === 'success') {
            $htmlSize = strlen($result['result']['html'] ?? '');
            $rowCount = $result['result']['rows_count'] ?? 0;
            echo "✓ OK ({$rowCount} rows, {$htmlSize} bytes)\n";
            $results[$formCode] = ['status' => 'success', 'rows' => $rowCount, 'html_size' => $htmlSize];
        } else {
            echo "✗ FAILED: {$result['error']}\n";
            $results[$formCode] = ['status' => 'failed', 'error' => $result['error']];
        }
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $results[$formCode] = ['status' => 'error', 'error' => $e->getMessage()];
    }
}

echo "\n\nTesting Batch Mode (PDF Generation):\n";
echo "====================================\n\n";

$batchResults = [];
foreach (array_slice($testForms, 0, 5) as $formCode) {
    echo "Testing {$formCode}... ";
    
    try {
        $result = $orchestrator->execute(
            $batch->tenant_id,
            $batch->branch_id,
            $month,
            $year,
            $formCode,
            'batch',
            $batch->id
        );
        
        if ($result['status'] === 'success') {
            $fileSize = $result['result']['file_size'] ?? 0;
            echo "✓ OK ({$fileSize} bytes)\n";
            $batchResults[$formCode] = ['status' => 'success', 'file_size' => $fileSize];
        } else {
            echo "✗ FAILED: {$result['error']}\n";
            $batchResults[$formCode] = ['status' => 'failed', 'error' => $result['error']];
        }
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $batchResults[$formCode] = ['status' => 'error', 'error' => $e->getMessage()];
    }
}

echo "\n\n=== SUMMARY ===\n";

$previewSuccess = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$previewTotal = count($results);

$batchSuccess = count(array_filter($batchResults, fn($r) => $r['status'] === 'success'));
$batchTotal = count($batchResults);

echo "Preview Mode: {$previewSuccess}/{$previewTotal} forms successful\n";
echo "Batch Mode: {$batchSuccess}/{$batchTotal} forms successful\n";

if ($previewSuccess === $previewTotal && $batchSuccess === $batchTotal) {
    echo "\n✅ ALL TESTS PASSED - PIPELINE WORKING CORRECTLY\n";
} else {
    echo "\n⚠️ SOME TESTS FAILED - CHECK ERRORS ABOVE\n";
}

echo "\n=== EXECUTION LOGS ===\n";
$logs = $orchestrator->getExecutionStats($batch->id);
echo "Total Executions: {$logs['total_executions']}\n";
echo "Successful: {$logs['successful']}\n";
echo "Failed: {$logs['failed']}\n";
echo "Total Execution Time: {$logs['total_execution_time']}ms\n";
echo "Average Time: {$logs['average_time']}ms\n";
