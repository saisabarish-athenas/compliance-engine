<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== PAYROLL DIAGNOSTIC ===\n\n";

$tenantId = 1;
$branchId = 1;

// Check payroll cycles
echo "Payroll Cycles:\n";
$cycles = DB::table('workforce_payroll_cycle')
    ->where('tenant_id', $tenantId)
    ->get();

foreach ($cycles as $cycle) {
    echo "  ID: {$cycle->id}, Name: {$cycle->cycle_name}\n";
    echo "    Period: {$cycle->period_from} to {$cycle->period_to}\n";
}

// Check payroll entries
echo "\nPayroll Entries:\n";
$entries = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->get();

echo "  Total: " . $entries->count() . "\n";
if ($entries->count() > 0) {
    $sample = $entries->first();
    echo "  Sample Entry:\n";
    echo "    Employee ID: {$sample->employee_id}\n";
    echo "    Payroll Cycle ID: {$sample->payroll_cycle_id}\n";
    echo "    Gross Salary: {$sample->gross_salary}\n";
    echo "    Created At: {$sample->created_at}\n";
}

// Check bonus records
echo "\nBonus Records:\n";
$bonus = DB::table('bonus_records')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->get();

echo "  Total: " . $bonus->count() . "\n";
if ($bonus->count() > 0) {
    $sample = $bonus->first();
    echo "  Sample Bonus:\n";
    echo "    Employee ID: {$sample->employee_id}\n";
    echo "    Amount: {$sample->bonus_amount}\n";
    echo "    Financial Year: {$sample->financial_year}\n";
}

// Check incidents
echo "\nIncidents:\n";
$incidents = DB::table('incidents')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->get();

echo "  Total: " . $incidents->count() . "\n";
if ($incidents->count() > 0) {
    $sample = $incidents->first();
    echo "  Sample Incident:\n";
    echo "    Employee ID: {$sample->employee_id}\n";
    echo "    Notice Date: {$sample->notice_date}\n";
    echo "    Injury Type: {$sample->injury_type}\n";
}

echo "\n";
