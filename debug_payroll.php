<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== PAYROLL DATA DEBUG ===\n\n";

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2025;

echo "Checking payroll_entry table:\n";
$payroll = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->select('id', 'employee_id', 'payment_date', 'created_at', 'gross_salary')
    ->limit(3)
    ->get();

echo "  Total records: " . DB::table('workforce_payroll_entry')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->count() . "\n";
echo "  Sample records:\n";
foreach ($payroll as $row) {
    echo "    - ID: {$row->id}, Payment Date: {$row->payment_date}, Created: {$row->created_at}\n";
}

echo "\nChecking by payment_date (Jan 2025):\n";
$byPaymentDate = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->whereYear('payment_date', $year)
    ->whereMonth('payment_date', $month)
    ->count();
echo "  Count: {$byPaymentDate}\n";

echo "\nChecking by created_at (Jan 2025):\n";
$byCreatedAt = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->whereYear('created_at', $year)
    ->whereMonth('created_at', $month)
    ->count();
echo "  Count: {$byCreatedAt}\n";

echo "\nChecking by created_at (Mar 2026 - today):\n";
$byCreatedAtToday = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->whereYear('created_at', 2026)
    ->whereMonth('created_at', 3)
    ->count();
echo "  Count: {$byCreatedAtToday}\n";

echo "\n";
