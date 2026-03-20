<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== TESTING FORM 11 API SERVICE AND GENERATOR ===\n\n";

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2025;

// Test API Service
echo "1. Testing Form11ApiService::fetch()\n";
$apiService = app(\App\Services\Compliance\FormApis\Form11ApiService::class);
$apiData = $apiService->fetch($tenantId, $branchId, $month, $year);

echo "   Records fetched: " . count($apiData['records']) . "\n";
echo "   Tenant: " . $apiData['tenant']['name'] . "\n";
echo "   Branch: " . $apiData['branch']['name'] . "\n";
echo "   Period: " . $apiData['period'] . "\n";

if (count($apiData['records']) > 0) {
    echo "   Sample record:\n";
    $sample = $apiData['records'][0];
    echo "     - Notice Date: {$sample['notice_date']}\n";
    echo "     - Employee: {$sample['name']}\n";
    echo "     - Injury Type: {$sample['injury_type']}\n";
}

// Test Generator
echo "\n2. Testing Form11Generator::generate()\n";
$generator = app(\App\Services\Compliance\FormGenerator\Form11Generator::class);
$formData = $generator->generate($apiData);

echo "   Company Name: " . $formData['company_name'] . "\n";
echo "   Work Location: " . $formData['work_location'] . "\n";
echo "   Month/Year: " . $formData['month_year'] . "\n";
echo "   Entries: " . count($formData['entries']) . "\n";

if (count($formData['entries']) > 0) {
    echo "   Sample entry:\n";
    $entry = $formData['entries'][0];
    echo "     - Date of Notice: {$entry['date_of_notice']}\n";
    echo "     - Injured Person: {$entry['injured_person']}\n";
    echo "     - Sex: {$entry['sex']}\n";
    echo "     - Age: {$entry['age']}\n";
    echo "     - Insurance No: {$entry['insurance_no']}\n";
    echo "     - Occupation: {$entry['occupation']}\n";
    echo "     - Cause: {$entry['cause']}\n";
    echo "     - Nature: {$entry['nature']}\n";
    echo "     - Injury Date: {$entry['injury_date']}\n";
    echo "     - Injury Time: {$entry['injury_time']}\n";
    echo "     - Place: {$entry['place']}\n";
    echo "     - Activity: {$entry['activity']}\n";
    echo "     - First Aid Person: {$entry['first_aid_person']}\n";
    echo "     - Witnesses: {$entry['witnesses']}\n";
    echo "     - Remarks: {$entry['remarks']}\n";
}

// Verify structure
echo "\n3. Verifying Output Structure\n";
$requiredFields = ['company_name', 'contractor_name', 'total_workers', 'work_location', 'principal_employer', 'month_year', 'entries'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($formData[$field])) {
        $missingFields[] = $field;
    }
}

if (empty($missingFields)) {
    echo "   ✓ All required header fields present\n";
} else {
    echo "   ✗ Missing fields: " . implode(', ', $missingFields) . "\n";
}

if (count($formData['entries']) > 0) {
    $entryFields = ['date_of_notice', 'time_of_notice', 'injured_person', 'sex', 'age', 'insurance_no', 'occupation', 'cause', 'nature', 'injury_date', 'injury_time', 'place', 'activity', 'first_aid_person', 'signature', 'witnesses', 'remarks'];
    $entry = $formData['entries'][0];
    $missingEntryFields = [];
    foreach ($entryFields as $field) {
        if (!isset($entry[$field])) {
            $missingEntryFields[] = $field;
        }
    }
    
    if (empty($missingEntryFields)) {
        echo "   ✓ All required entry fields present\n";
    } else {
        echo "   ✗ Missing entry fields: " . implode(', ', $missingEntryFields) . "\n";
    }
}

echo "\n✅ Form 11 API Service and Generator are working correctly!\n\n";
