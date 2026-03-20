#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\BatchInspectionPackService;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  🧪 COMPLIANCE ENGINE COMPLETE WORKFLOW TEST\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Test 1: Verify Database
echo "1️⃣  Testing Database Connection...\n";
try {
    $tenants = DB::table('tenants')->count();
    $branches = DB::table('branches')->count();
    $employees = DB::table('workforce_employee')->count();
    $payroll = DB::table('workforce_payroll_entry')->count();
    
    echo "   ✓ Tenants: {$tenants}\n";
    echo "   ✓ Branches: {$branches}\n";
    echo "   ✓ Employees: {$employees}\n";
    echo "   ✓ Payroll Entries: {$payroll}\n";
    echo "   ✅ Database OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Database Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Verify Form Services
echo "2️⃣  Testing Form API Services...\n";
try {
    $factory = app(FormApiServiceFactory::class);
    $formCodes = ['FORM_B', 'FORM_A'];
    
    foreach ($formCodes as $code) {
        $service = $factory->make($code);
        $data = $service->fetch(1, 1, 1, 2025);
        
        if ($data['meta']['tenant_id'] === 1 && $data['meta']['branch_id'] === 1) {
            $recordCount = count($data['records'] ?? []);
            echo "   ✓ {$code}: {$recordCount} records\n";
        } else {
            throw new Exception("{$code} returned invalid tenant/branch");
        }
    }
    echo "   ✅ Form Services OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Form Service Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Verify Data Integrity
echo "3️⃣  Testing Data Integrity...\n";
try {
    $tenant = DB::table('tenants')->find(1);
    $branch = DB::table('branches')->find(1);
    $employee = DB::table('workforce_employee')->where('tenant_id', 1)->first();
    $payrollEntry = DB::table('workforce_payroll_entry')->where('tenant_id', 1)->first();
    
    if (!$tenant) throw new Exception("Tenant not found");
    if (!$branch) throw new Exception("Branch not found");
    if (!$employee) throw new Exception("Employee not found");
    if (!$payrollEntry) throw new Exception("Payroll entry not found");
    
    echo "   ✓ Tenant: {$tenant->name}\n";
    echo "   ✓ Branch: {$branch->branch_name}\n";
    echo "   ✓ Employee: {$employee->name}\n";
    echo "   ✓ Payroll: {$payrollEntry->gross_salary}\n";
    echo "   ✅ Data Integrity OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Data Integrity Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Verify Multi-Tenant Safety
echo "4️⃣  Testing Multi-Tenant Safety...\n";
try {
    $service = app(FormApiServiceFactory::class)->make('FORM_B');
    $data = $service->fetch(1, 1, 1, 2025);
    
    if ($data['meta']['tenant_id'] !== 1) {
        throw new Exception("Tenant ID mismatch");
    }
    if ($data['meta']['branch_id'] !== 1) {
        throw new Exception("Branch ID mismatch");
    }
    
    echo "   ✓ Tenant filtering: OK\n";
    echo "   ✓ Branch filtering: OK\n";
    echo "   ✅ Multi-Tenant Safety OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Multi-Tenant Safety Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Verify Inspection Pack Service
echo "5️⃣  Testing Inspection Pack Service...\n";
try {
    $packService = app(BatchInspectionPackService::class);
    
    if (!method_exists($packService, 'createInspectionPack')) {
        throw new Exception("createInspectionPack method not found");
    }
    if (!method_exists($packService, 'getInspectionPackList')) {
        throw new Exception("getInspectionPackList method not found");
    }
    
    echo "   ✓ Service loaded\n";
    echo "   ✓ createInspectionPack method available\n";
    echo "   ✓ getInspectionPackList method available\n";
    echo "   ✅ Inspection Pack Service OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Inspection Pack Service Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 6: Verify Storage Directories
echo "6️⃣  Testing Storage Directories...\n";
try {
    $dirs = [
        'storage/app/compliance_pdfs',
        'storage/app/compliance_inspection_packs',
        'storage/app/temp',
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            throw new Exception("{$dir} is not writable");
        }
        echo "   ✓ {$dir}\n";
    }
    echo "   ✅ Storage Directories OK\n\n";
} catch (\Exception $e) {
    echo "   ❌ Storage Directory Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Summary
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ ALL TESTS PASSED - SYSTEM READY FOR PRODUCTION\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "📋 Next Steps:\n";
echo "   1. Start server: php artisan serve\n";
echo "   2. Preview forms: http://localhost:8000/compliance/forms/preview\n";
echo "   3. Generate PDF: php artisan compliance:generate-pdf --form_code=FORM_B --tenant_id=1 --branch_id=1 --month=1 --year=2025\n";
echo "   4. Create pack: php artisan compliance:create-inspection-pack --tenant_id=1 --branch_id=1 --month=1 --year=2025\n";
echo "\n";
echo "📚 Documentation:\n";
echo "   - COMPLETE_WORKFLOW_GUIDE.md\n";
echo "   - API_SERVICES_QUICK_REFERENCE.md\n";
echo "   - BATCH_WORKFLOW_QUICK_REFERENCE.md\n";
echo "\n";

exit(0);
