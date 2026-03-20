<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== DATABASE VERIFICATION FOR JANUARY 2025 ===\n\n";

$tenantId = 1;
$branchId = 1;

echo "Tenant ID: {$tenantId}\n";
echo "Branch ID: {$branchId}\n\n";

$payrollCount = DB::table('workforce_payroll_entry')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$bonusCount = DB::table('bonus_records')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$incidentCount = DB::table('incidents')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$employeeCount = DB::table('workforce_employee')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$attendanceCount = DB::table('workforce_attendance')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$hazardCount = DB::table('hazard_register')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

$contractCount = DB::table('contract_labour_deployment')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->count();

echo "RECORDS COUNT:\n";
echo "  Employees: {$employeeCount}\n";
echo "  Attendance: {$attendanceCount}\n";
echo "  Payroll Entries: {$payrollCount}\n";
echo "  Bonus Records: {$bonusCount}\n";
echo "  Incidents: {$incidentCount}\n";
echo "  Hazard Register: {$hazardCount}\n";
echo "  Contract Labour: {$contractCount}\n\n";

if ($payrollCount > 0) {
    echo "✓ Payroll data exists\n";
    $sample = DB::table('workforce_payroll_entry')
        ->where('tenant_id', $tenantId)
        ->where('branch_id', $branchId)
        ->first();
    echo "  Sample: Employee ID {$sample->employee_id}, Gross: {$sample->gross_salary}\n";
} else {
    echo "✗ Payroll data MISSING\n";
}

if ($bonusCount > 0) {
    echo "✓ Bonus data exists\n";
    $sample = DB::table('bonus_records')
        ->where('tenant_id', $tenantId)
        ->where('branch_id', $branchId)
        ->first();
    echo "  Sample: Employee ID {$sample->employee_id}, Amount: {$sample->bonus_amount}\n";
} else {
    echo "✗ Bonus data MISSING\n";
}

if ($incidentCount > 0) {
    echo "✓ Incident data exists\n";
    $sample = DB::table('incidents')
        ->where('tenant_id', $tenantId)
        ->where('branch_id', $branchId)
        ->first();
    echo "  Sample: Employee ID {$sample->employee_id}, Type: {$sample->injury_type}\n";
} else {
    echo "✗ Incident data MISSING\n";
}

echo "\n";
