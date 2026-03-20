<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== TESTING API SERVICES FOR JANUARY 2025 ===\n\n";

$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2025;

// Test Payroll API (using a sample form)
echo "Testing Payroll Data Access:\n";
$payrollData = DB::table('workforce_payroll_entry as pe')
    ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->whereYear('pe.created_at', $year)
    ->whereMonth('pe.created_at', $month)
    ->select('e.name', 'pe.gross_salary', 'pe.net_salary')
    ->limit(3)
    ->get();

echo "  Records found: " . $payrollData->count() . "\n";
foreach ($payrollData as $row) {
    echo "    - {$row->name}: Gross ₹{$row->gross_salary}, Net ₹{$row->net_salary}\n";
}

// Test Bonus API
echo "\nTesting Bonus Data Access:\n";
$bonusData = DB::table('bonus_records as br')
    ->join('workforce_employee as e', 'e.id', '=', 'br.employee_id')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->select('e.name', 'br.bonus_amount', 'br.financial_year')
    ->limit(3)
    ->get();

echo "  Records found: " . $bonusData->count() . "\n";
foreach ($bonusData as $row) {
    echo "    - {$row->name}: ₹{$row->bonus_amount} ({$row->financial_year})\n";
}

// Test Incidents API (Form 11)
echo "\nTesting Incident Data Access (Form 11):\n";
$incidentData = DB::table('incidents as i')
    ->join('workforce_employee as e', 'e.id', '=', 'i.employee_id')
    ->where('i.tenant_id', $tenantId)
    ->where('i.branch_id', $branchId)
    ->whereYear('i.notice_date', $year)
    ->whereMonth('i.notice_date', $month)
    ->select('e.name', 'i.injury_type', 'i.notice_date', 'i.location')
    ->limit(3)
    ->get();

echo "  Records found: " . $incidentData->count() . "\n";
foreach ($incidentData as $row) {
    echo "    - {$row->name}: {$row->injury_type} at {$row->location} on {$row->notice_date}\n";
}

// Test Attendance
echo "\nTesting Attendance Data Access:\n";
$attendanceData = DB::table('workforce_attendance as wa')
    ->join('workforce_employee as e', 'e.id', '=', 'wa.employee_id')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->whereYear('wa.attendance_date', $year)
    ->whereMonth('wa.attendance_date', $month)
    ->select('e.name', 'wa.attendance_date', 'wa.status')
    ->limit(3)
    ->get();

echo "  Records found: " . $attendanceData->count() . "\n";
foreach ($attendanceData as $row) {
    echo "    - {$row->name}: {$row->status} on {$row->attendance_date}\n";
}

// Test Hazard Register
echo "\nTesting Hazard Register Data Access:\n";
$hazardData = DB::table('hazard_register')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->whereYear('hazard_date', $year)
    ->whereMonth('hazard_date', $month)
    ->select('hazard_type', 'severity', 'location')
    ->limit(3)
    ->get();

echo "  Records found: " . $hazardData->count() . "\n";
foreach ($hazardData as $row) {
    echo "    - {$row->hazard_type} ({$row->severity}) at {$row->location}\n";
}

// Test Contract Labour
echo "\nTesting Contract Labour Data Access:\n";
$contractData = DB::table('contract_labour_deployment as cld')
    ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
    ->where('cld.tenant_id', $tenantId)
    ->where('cld.branch_id', $branchId)
    ->select('e.name', 'cld.wage_rate', 'cld.work_order_number')
    ->limit(3)
    ->get();

echo "  Records found: " . $contractData->count() . "\n";
foreach ($contractData as $row) {
    echo "    - {$row->name}: ₹{$row->wage_rate} ({$row->work_order_number})\n";
}

echo "\n✓ All data sources verified and accessible!\n\n";
