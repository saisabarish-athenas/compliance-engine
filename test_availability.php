<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== TESTING DATA AVAILABILITY ENGINE ===\n\n";

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2025;

$engine = app(\App\Services\Compliance\DataAvailabilityEngine::class);
$availability = $engine->checkDataAvailability($tenantId, $branchId, $month, $year);

echo "Tenant ID: {$tenantId}\n";
echo "Branch ID: {$branchId}\n";
echo "Period: January 2025\n\n";

echo "DATA AVAILABILITY RESULT:\n";
echo "  All Data Exists: " . ($availability['all_data_exists'] ? 'YES ✓' : 'NO ✗') . "\n";
echo "  Missing Data: " . (empty($availability['missing_data']) ? 'NONE' : implode(', ', $availability['missing_data'])) . "\n\n";

echo "DATA SUMMARY:\n";
foreach ($availability['data_summary'] as $key => $count) {
    $status = $count > 0 ? '✓' : '✗';
    echo "  {$status} " . ucfirst(str_replace('_', ' ', $key)) . ": {$count}\n";
}

echo "\n";

if ($availability['all_data_exists']) {
    echo "✅ SUCCESS! All required data is available for January 2025\n";
} else {
    echo "⚠️ WARNING! Missing data: " . implode(', ', $availability['missing_data']) . "\n";
}

echo "\n";
