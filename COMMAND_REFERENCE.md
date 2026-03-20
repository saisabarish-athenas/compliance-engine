# 🔧 COMPLIANCE ENGINE - COMMAND REFERENCE

## 🚀 SETUP COMMANDS

### Fresh Start (Complete Reset)
```bash
# Option 1: One-liner
php artisan migrate:fresh && php artisan db:seed --class=ComplianceFormsMasterSeeder && php artisan db:seed --class=FreshComplianceSeeder && php test_system_health.php

# Option 2: Step by step
php artisan migrate:fresh
php artisan db:seed --class=ComplianceFormsMasterSeeder
php artisan db:seed --class=FreshComplianceSeeder
php test_system_health.php
```

### Seed Only (Keep Existing Data)
```bash
# Seed forms only
php artisan db:seed --class=ComplianceFormsMasterSeeder

# Seed demo data only
php artisan db:seed --class=FreshComplianceSeeder

# Seed both
php artisan db:seed --class=ComplianceFormsMasterSeeder && php artisan db:seed --class=FreshComplianceSeeder
```

### Database Operations
```bash
# Run all migrations
php artisan migrate

# Fresh migrations (WARNING: Deletes all data)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Refresh migrations (rollback + migrate)
php artisan migrate:refresh
```

---

## 🧪 TESTING COMMANDS

### System Health Check
```bash
# Full system health check
php test_system_health.php

# Expected output: ✅ ALL TESTS PASSED
```

### Database Verification
```bash
# Check tenants
php artisan tinker
>>> App\Models\Tenant::count()

# Check employees
>>> App\Models\WorkforceEmployee::where('tenant_id', 1)->count()

# Check forms
>>> App\Models\ComplianceFormsMaster::where('is_active', true)->count()

# Check payroll
>>> App\Models\WorkforcePayrollEntry::where('tenant_id', 1)->count()

# Exit tinker
>>> exit
```

### SQL Queries
```bash
# Connect to MySQL
mysql -u root -p compliance_engine

# Check data
SELECT COUNT(*) FROM tenants;
SELECT COUNT(*) FROM workforce_employee WHERE tenant_id = 1;
SELECT COUNT(*) FROM compliance_forms_master WHERE is_active = 1;
SELECT COUNT(*) FROM workforce_payroll_entry WHERE tenant_id = 1;
SELECT COUNT(*) FROM incident_documents WHERE tenant_id = 1;
```

---

## 📋 FORM GENERATION COMMANDS

### Generate Form Preview
```bash
php artisan tinker

# Generate FORM_B preview
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
$result = $orchestrator->execute(1, 1, 1, 2025, 'FORM_B', 'preview');
echo $result['status'];  // Should be 'success'

# Generate FORM_25 preview
$result = $orchestrator->execute(1, 1, 1, 2025, 'FORM_25', 'preview');
echo $result['status'];

exit
```

### Generate PDF
```bash
php artisan tinker

$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
$result = $orchestrator->execute(1, 1, 1, 2025, 'FORM_B', 'pdf');

if ($result['status'] === 'success') {
    echo "PDF generated successfully";
    echo "Size: " . $result['result']['size'] . " bytes";
}

exit
```

### Create Batch
```bash
php artisan tinker

$batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
$batch = $batchOrchestrator->createBatch(1, 1, 2025);

echo "Batch ID: " . $batch->id;
echo "Status: " . $batch->status;
echo "Forms: " . count(json_decode($batch->form_ids));

exit
```

---

## 🔍 DEBUGGING COMMANDS

### Check Logs
```bash
# View latest logs
tail -f storage/logs/laravel.log

# View specific number of lines
tail -n 100 storage/logs/laravel.log

# Search for errors
grep -i error storage/logs/laravel.log

# Search for specific form
grep FORM_B storage/logs/laravel.log
```

### Database Debugging
```bash
php artisan tinker

# Check if forms are registered
$forms = App\Models\ComplianceFormsMaster::where('is_active', true)->get();
echo "Total forms: " . $forms->count();
$forms->each(fn($f) => echo $f->form_code . "\n");

# Check tenant setup
$tenant = App\Models\Tenant::find(1);
echo "Tenant: " . $tenant->name;
echo "Subscription: " . $tenant->subscription_type;

# Check branch setup
$branch = App\Models\Branch::find(1);
echo "Branch: " . $branch->branch_name;
echo "Address: " . $branch->address;

# Check employees
$employees = App\Models\WorkforceEmployee::where('tenant_id', 1)->get();
echo "Total employees: " . $employees->count();

# Check payroll
$payroll = App\Models\WorkforcePayrollEntry::where('tenant_id', 1)->first();
echo "Sample payroll - Gross: " . $payroll->gross_salary;

exit
```

### Service Debugging
```bash
php artisan tinker

# Check if orchestrator is available
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
echo "Orchestrator available: " . ($orchestrator ? "Yes" : "No");

# Check if factories are available
$apiFactory = \App\Services\Compliance\FormApis\FormApiServiceFactory::class;
echo "API Factory available: " . ($apiFactory ? "Yes" : "No");

$genFactory = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::class;
echo "Generator Factory available: " . ($genFactory ? "Yes" : "No");

# Check frequency engine
$frequencyEngine = app(\App\Services\Compliance\FrequencyEngine::class);
$forms = $frequencyEngine->getApplicableForms(1);
echo "Forms for January: " . $forms->count();

exit
```

---

## 🧹 CLEANUP COMMANDS

### Clear Cache
```bash
# Clear all cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

### Clean Storage
```bash
# Remove generated forms
rm -rf storage/app/generated_forms/*

# Remove inspection packs
rm -rf storage/app/compliance_inspection_packs/*

# Remove temp files
rm -rf storage/app/temp/*

# Clear logs (WARNING: Deletes all logs)
rm storage/logs/laravel.log
```

### Reset Database
```bash
# WARNING: This deletes all data!
php artisan migrate:fresh

# Then reseed
php artisan db:seed --class=ComplianceFormsMasterSeeder
php artisan db:seed --class=FreshComplianceSeeder
```

---

## 📊 MONITORING COMMANDS

### Check System Status
```bash
# Run health check
php test_system_health.php

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit

# Check Laravel version
php artisan --version

# Check PHP version
php --version
```

### Performance Monitoring
```bash
# Check execution logs
php artisan tinker

$logs = DB::table('compliance_execution_logs')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

$logs->each(fn($log) => echo 
    $log->form_code . " - " . 
    $log->status . " - " . 
    $log->execution_time . "ms\n"
);

exit
```

### Data Statistics
```bash
php artisan tinker

# Count records by type
echo "Tenants: " . App\Models\Tenant::count() . "\n";
echo "Branches: " . App\Models\Branch::count() . "\n";
echo "Employees: " . App\Models\WorkforceEmployee::count() . "\n";
echo "Payroll Entries: " . App\Models\WorkforcePayrollEntry::count() . "\n";
echo "Bonus Records: " . App\Models\BonusRecord::count() . "\n";
echo "Incidents: " . App\Models\IncidentDocument::count() . "\n";
echo "Forms: " . App\Models\ComplianceFormsMaster::where('is_active', true)->count() . "\n";

exit
```

---

## 🚀 PRODUCTION COMMANDS

### Pre-Deployment
```bash
# Run migrations
php artisan migrate

# Seed forms
php artisan db:seed --class=ComplianceFormsMasterSeeder

# Clear cache
php artisan cache:clear && php artisan config:clear

# Verify system
php test_system_health.php
```

### Deployment
```bash
# Start application
php artisan serve

# Or with specific host/port
php artisan serve --host=0.0.0.0 --port=8000

# Or use production server (nginx/apache)
# Configure web server to point to public/ directory
```

### Post-Deployment
```bash
# Monitor logs
tail -f storage/logs/laravel.log

# Check system status
php test_system_health.php

# Monitor performance
# Check database queries
# Monitor memory usage
# Check response times
```

---

## 🔐 Security Commands

### User Management
```bash
php artisan tinker

# Create new user
$user = App\Models\User::create([
    'name' => 'New User',
    'email' => 'user@example.com',
    'password' => Hash::make('password'),
    'tenant_id' => 1
]);

# List users
App\Models\User::all();

# Update user
$user = App\Models\User::find(1);
$user->update(['name' => 'Updated Name']);

# Delete user
App\Models\User::find(1)->delete();

exit
```

### Audit Trail
```bash
php artisan tinker

# Check execution logs
$logs = DB::table('compliance_execution_logs')
    ->where('tenant_id', 1)
    ->orderBy('created_at', 'desc')
    ->get();

$logs->each(fn($log) => echo 
    $log->created_at . " - " . 
    $log->form_code . " - " . 
    $log->status . "\n"
);

exit
```

---

## 📚 REFERENCE

### Form Codes
```
CLRA: FORM_XII, FORM_XIII, FORM_XIV, FORM_XVI, FORM_XVII, 
      FORM_XIX, FORM_XX, FORM_XXI, FORM_XXII, FORM_XXIII

Labour Welfare: FORM_A, FORM_C, FORM_D, FORM_D_ER

Social Security: FORM_11, ESI_FORM_12, EPF_INSPECTION

Factories: FORM_B, FORM_2, FORM_8, FORM_10, FORM_12, 
           FORM_17, FORM_18, FORM_25, FORM_26, FORM_26A, HAZARD_REG

Shops: SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FORM_C, 
       SHOPS_FORM_VI, SHOPS_UNPAID, SHOPS_FINES
```

### Execution Modes
```
preview - HTML preview
pdf - PDF generation
batch - Batch processing
inspection_pack - Inspection pack generation
```

### Frequencies
```
Monthly - Every month
Quarterly - Months 3, 6, 9, 12
HalfYearly - Months 6, 12
Annual - Month 12 only
Event - Manual trigger
```

---

## ✅ QUICK REFERENCE

| Command | Purpose |
|---------|---------|
| `php artisan migrate:fresh` | Fresh database |
| `php artisan db:seed --class=ComplianceFormsMasterSeeder` | Seed forms |
| `php artisan db:seed --class=FreshComplianceSeeder` | Seed demo data |
| `php test_system_health.php` | System health check |
| `php artisan serve` | Start application |
| `php artisan tinker` | Interactive shell |
| `tail -f storage/logs/laravel.log` | View logs |
| `php artisan cache:clear` | Clear cache |

---

**Last Updated:** 2026-03-11  
**Version:** 1.0
