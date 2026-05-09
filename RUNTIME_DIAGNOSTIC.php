<?php
/**
 * RUNTIME DIAGNOSTIC - Trace Form 2 Execution Pipeline
 * 
 * Simulates: GET /compliance/batch/1/preview/FORM_2
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Compliance\ComplianceOrchestrator;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2024;
$formCode = 'FORM_2';

echo "=== RUNTIME DIAGNOSTIC: FORM_2 EXECUTION ===\n\n";

// STEP 1: API Service
echo "STEP 1: API Service Fetch\n";
echo "------------------------\n";
$apiService = FormApiServiceFactory::make($formCode);
if (!$apiService) {
    echo "ERROR: No API service found for {$formCode}\n";
    exit(1);
}

$rawData = $apiService->fetch($tenantId, $branchId, $month, $year);
echo "API Response Structure:\n";
echo "  - records count: " . count($rawData['records'] ?? []) . "\n";
echo "  - meta: " . json_encode($rawData['meta'] ?? []) . "\n";
echo "  - tenant keys: " . implode(', ', array_keys($rawData['tenant'] ?? [])) . "\n";
echo "  - branch keys: " . implode(', ', array_keys($rawData['branch'] ?? [])) . "\n";
echo "  - period: " . ($rawData['period'] ?? 'N/A') . "\n";

if (isset($rawData['tenant'])) {
    echo "\nTenant Data:\n";
    foreach ($rawData['tenant'] as $k => $v) {
        echo "  - {$k}: {$v}\n";
    }
}

if (isset($rawData['branch'])) {
    echo "\nBranch Data:\n";
    foreach ($rawData['branch'] as $k => $v) {
        echo "  - {$k}: {$v}\n";
    }
}

// STEP 2: Generator
echo "\n\nSTEP 2: Generator Transform\n";
echo "----------------------------\n";
$generator = FormGeneratorFactory::make($formCode);
if (!$generator) {
    echo "ERROR: No generator found for {$formCode}\n";
    exit(1);
}

$formData = $generator->generate($rawData);
echo "Generator Output Structure:\n";
echo "  - header keys: " . implode(', ', array_keys($formData['header'] ?? [])) . "\n";
echo "  - rows count: " . count($formData['rows'] ?? []) . "\n";
echo "  - totals keys: " . implode(', ', array_keys($formData['totals'] ?? [])) . "\n";
echo "  - is_nil: " . ($formData['is_nil'] ? 'true' : 'false') . "\n";

echo "\nHeader Data:\n";
foreach ($formData['header'] ?? [] as $k => $v) {
    if (is_array($v)) {
        echo "  - {$k}: [array]\n";
    } else {
        echo "  - {$k}: {$v}\n";
    }
}

// STEP 3: View Data Preparation (Orchestrator)
echo "\n\nSTEP 3: View Data Preparation (Orchestrator)\n";
echo "---------------------------------------------\n";

$batchId = 1; // Simulate batch ID
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

echo "View Data Keys: " . implode(', ', array_keys($viewData)) . "\n";

// Check critical variables
$criticalVars = ['factory_name', 'place', 'district', 'establishment_name', 'tenant_name', 'batch_id'];
echo "\nCritical Variables Check:\n";
foreach ($criticalVars as $var) {
    $exists = isset($viewData[$var]);
    $value = $viewData[$var] ?? 'MISSING';
    echo "  - {$var}: " . ($exists ? "✓ ({$value})" : "✗ MISSING") . "\n";
}

// STEP 4: Template Rendering
echo "\n\nSTEP 4: Template Rendering\n";
echo "---------------------------\n";
$viewPath = 'compliance.forms.form_2';
if (!\Illuminate\Support\Facades\View::exists($viewPath)) {
    echo "ERROR: View not found: {$viewPath}\n";
    exit(1);
}

try {
    $html = \Illuminate\Support\Facades\View::make($viewPath, $viewData)->render();
    echo "Template rendered successfully\n";
    echo "HTML length: " . strlen($html) . " bytes\n";
    
    // Check if critical values appear in HTML
    echo "\nCritical Values in HTML:\n";
    foreach ($criticalVars as $var) {
        $value = $viewData[$var] ?? 'MISSING';
        if ($value !== 'MISSING' && strpos($html, $value) !== false) {
            echo "  - {$var}: ✓ Found in HTML\n";
        } else {
            echo "  - {$var}: ✗ NOT found in HTML\n";
        }
    }
} catch (\Exception $e) {
    echo "ERROR: Template rendering failed\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
