<?php
/**
 * COMPREHENSIVE FORM RENDERING TEST
 * Tests all 17 failing forms
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use Illuminate\Support\Facades\View;

$failingForms = [
    'FORM_2', 'FORM_8', 'FORM_17', 'FORM_18', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
    'FORM_XIV', 'FORM_XIX', 'SHOPS_FORM_VI', 'SHOPS_FORM_12', 'SHOPS_FORM_13',
    'SHOPS_FORM_C', 'SHOPS_UNPAID', 'SHOPS_FINES', 'ESI_FORM_12', 'EPF_INSPECTION'
];

$workingForms = ['FORM_B', 'FORM_10', 'FORM_12', 'FORM_25'];

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2024;
$batchId = 1;

echo "=== COMPREHENSIVE FORM RENDERING TEST ===\n\n";

$results = [];

foreach (array_merge($failingForms, $workingForms) as $formCode) {
    echo "Testing {$formCode}... ";
    
    try {
        // Get API service
        $apiService = FormApiServiceFactory::make($formCode);
        if (!$apiService) {
            echo "❌ NO API SERVICE\n";
            $results[$formCode] = ['status' => 'failed', 'reason' => 'No API service'];
            continue;
        }
        
        // Fetch data
        $rawData = $apiService->fetch($tenantId, $branchId, $month, $year);
        $recordCount = count($rawData['records'] ?? []);
        
        // Get generator
        $generator = FormGeneratorFactory::make($formCode);
        if (!$generator) {
            echo "❌ NO GENERATOR\n";
            $results[$formCode] = ['status' => 'failed', 'reason' => 'No generator'];
            continue;
        }
        
        // Generate form data
        $formData = $generator->generate($rawData);
        $rowCount = count($formData['rows'] ?? []);
        
        // Prepare view data (simulating orchestrator)
        $viewData = array_merge(
            $formData['header'] ?? [],
            [
                'form_title' => $formData['header']['form_title'] ?? $formCode,
                'form_code' => $formCode,
                'period_month' => $month,
                'period_year' => $year,
                'batch_id' => $batchId,
                'header' => $formData['header'] ?? [],
                'rows' => $formData['rows'] ?? [],
                'entries' => $formData['rows'] ?? [],
                'totals' => $formData['totals'] ?? [],
                'is_nil' => $formData['is_nil'] ?? empty($formData['rows'])
            ]
        );
        
        // Try to render
        $viewPath = "compliance.forms." . strtolower(str_replace('_', '_', $formCode));
        
        // Map form codes to view paths
        $viewMap = [
            'FORM_2' => 'compliance.forms.form_2',
            'FORM_8' => 'compliance.forms.form_8',
            'FORM_17' => 'compliance.forms.form_17',
            'FORM_18' => 'compliance.forms.form_18',
            'FORM_26' => 'compliance.forms.form_26',
            'FORM_26A' => 'compliance.forms.form_26a',
            'HAZARD_REG' => 'compliance.forms.hazard_reg',
            'FORM_XIV' => 'compliance.forms.form_xiv',
            'FORM_XIX' => 'compliance.forms.form_xix',
            'SHOPS_FORM_VI' => 'compliance.forms.shops_form_vi',
            'SHOPS_FORM_12' => 'compliance.forms.shops_form_12',
            'SHOPS_FORM_13' => 'compliance.forms.shops_form_13',
            'SHOPS_FORM_C' => 'compliance.forms.shops_form_c',
            'SHOPS_UNPAID' => 'compliance.forms.shops_unpaid',
            'SHOPS_FINES' => 'compliance.forms.shops_fines',
            'ESI_FORM_12' => 'compliance.forms.esi_form_12',
            'EPF_INSPECTION' => 'compliance.forms.epf_inspection',
            'FORM_B' => 'compliance.forms.form_b',
            'FORM_10' => 'compliance.forms.form_10',
            'FORM_12' => 'compliance.forms.form_12',
            'FORM_25' => 'compliance.forms.form_25',
        ];
        
        $viewPath = $viewMap[$formCode] ?? $viewPath;
        
        if (!View::exists($viewPath)) {
            echo "❌ VIEW NOT FOUND ({$viewPath})\n";
            $results[$formCode] = ['status' => 'failed', 'reason' => "View not found: {$viewPath}"];
            continue;
        }
        
        $html = View::make($viewPath, $viewData)->render();
        
        if (!$html || strlen($html) === 0) {
            echo "❌ EMPTY HTML\n";
            $results[$formCode] = ['status' => 'failed', 'reason' => 'Empty HTML'];
            continue;
        }
        
        echo "✓ OK (Records: {$recordCount}, Rows: {$rowCount}, HTML: " . strlen($html) . " bytes)\n";
        $results[$formCode] = [
            'status' => 'success',
            'records' => $recordCount,
            'rows' => $rowCount,
            'html_size' => strlen($html)
        ];
        
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $results[$formCode] = ['status' => 'failed', 'reason' => $e->getMessage()];
    }
}

echo "\n\n=== SUMMARY ===\n";
echo "Failing Forms (Expected to Fix):\n";
$failingSuccess = 0;
foreach ($failingForms as $form) {
    $result = $results[$form];
    if ($result['status'] === 'success') {
        echo "  ✓ {$form}\n";
        $failingSuccess++;
    } else {
        echo "  ✗ {$form}: {$result['reason']}\n";
    }
}

echo "\nWorking Forms (Should Still Work):\n";
$workingSuccess = 0;
foreach ($workingForms as $form) {
    $result = $results[$form];
    if ($result['status'] === 'success') {
        echo "  ✓ {$form}\n";
        $workingSuccess++;
    } else {
        echo "  ✗ {$form}: {$result['reason']}\n";
    }
}

echo "\n=== RESULTS ===\n";
echo "Failing Forms Fixed: {$failingSuccess}/" . count($failingForms) . "\n";
echo "Working Forms Still OK: {$workingSuccess}/" . count($workingForms) . "\n";
echo "Total Success: " . ($failingSuccess + $workingSuccess) . "/" . (count($failingForms) + count($workingForms)) . "\n";
