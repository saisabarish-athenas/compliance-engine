# FORM_XX Verification Guide

## Quick Test Commands

### 1. Test Inspect Command
```bash
php artisan compliance:inspect FORM_XX --tenant=1 --branch=1 --month=3 --year=2024
```

**Expected Output:**
```
✓ FORM_XX Data Generated Successfully

Header:
+------------------+----------------------------------+
| Key              | Value                            |
+------------------+----------------------------------+
| contractor_name  | [Actual Contractor Name]         |
| work_nature      | [Actual Work Location]           |
| establishment_name | [Actual Establishment Name]    |
| principal_employer | [Actual Principal Employer]    |
| period           | March 2024                       |
+------------------+----------------------------------+

Rows: 0 records
```

### 2. Test Form Generation Directly
```php
// In tinker or test
$generator = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class)::make('FORM_XX');
$data = $generator->generate(1, 1, 3, 2024);
dd($data);
```

**Expected Output:**
```php
[
    'header' => [
        'contractor_name' => 'Contractor Name',
        'work_nature' => 'Work Location',
        'establishment_name' => 'Establishment',
        'principal_employer' => 'Principal Employer',
        'period' => 'March 2024',
        'tenant' => [...],
        'branch' => [...]
    ],
    'rows' => [],
    'totals' => [],
    'is_nil' => true
]
```

### 3. Test Preview Page
```
GET /compliance/batch/1/preview/FORM_XX
```

**Expected Result:**
- Page loads without errors
- Header section displays:
  - NAME AND ADDRESS OF CONTRACTOR: [Actual Name]
  - NATURE AND LOCATION OF WORK: [Actual Location]
  - NAME AND ADDRESS OF ESTABLISHMENT: [Actual Establishment]
  - NAME AND ADDRESS OF PRINCIPAL EMPLOYER: [Actual Employer]
  - Month & Year: March 2024

### 4. Test Batch Processing
```php
// Create batch with FORM_XX
$batch = \App\Models\ComplianceExecutionBatch::create([
    'tenant_id' => 1,
    'section_id' => 1,
    'period_from' => '2024-03-01',
    'period_to' => '2024-03-31',
    'form_ids' => [/* FORM_XX id */],
    'branch_id' => 1,
    'status' => 'pending'
]);

// Process batch
$service = app(\App\Services\Compliance\ComplianceExecutionService::class);
$results = $service->processBatch($batch->id);
dd($results);
```

**Expected Result:**
- Forms generated successfully
- No errors in logs
- Files stored in: `storage/app/compliance/generated/{batch_id}/FORM_XX.pdf`

## Debugging Steps

### If Header Shows "N/A"

**Step 1:** Check if contractor_master table has data
```sql
SELECT * FROM contractor_master WHERE tenant_id = 1 LIMIT 1;
```

**Step 2:** Check FormDataAggregator output
```php
$aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);
$tenant = $aggregator->getTenantDetails(1);
$branch = $aggregator->getBranchDetails(1, 1);
dd($tenant, $branch);
```

**Step 3:** Check if values are arrays or objects
```php
// In prepareFormXX()
Log::info('Tenant type: ' . gettype($tenant));
Log::info('Tenant data: ' . json_encode($tenant));
Log::info('Branch type: ' . gettype($branch));
Log::info('Branch data: ' . json_encode($branch));
```

### If Command Doesn't Recognize FORM_XX

**Step 1:** Verify FormGeneratorFactory has FORM_XX
```php
$supported = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::getSupportedForms();
dd(in_array('FORM_XX', $supported));
```

**Step 2:** Verify FormGeneratorFactory::make() returns generator
```php
$generator = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::make('FORM_XX');
dd($generator);
```

**Step 3:** Check if FORM_XX is in contractorForms array
```php
// In FormGeneratorFactory.php
// Verify: 'FORM_XX' is in $contractorForms array
```

### If Preview Page Shows Errors

**Step 1:** Check logs
```bash
tail -f storage/logs/laravel.log
```

**Step 2:** Check if Blade template exists
```bash
ls -la resources/views/compliance/forms/form_xx.blade.php
```

**Step 3:** Check if template variables are passed
```php
// In ComplianceExecutionController::previewForm()
// Verify: $data['header'] contains all required keys
```

## Database Verification

### Check Contractor Master
```sql
SELECT id, tenant_id, company_name, name FROM contractor_master WHERE tenant_id = 1;
```

### Check Branches
```sql
SELECT id, tenant_id, name, address FROM branches WHERE tenant_id = 1;
```

### Check Tenants
```sql
SELECT id, name FROM tenants WHERE id = 1;
```

### Check Generated Forms
```sql
SELECT * FROM compliance_batch_forms WHERE form_code = 'FORM_XX' ORDER BY created_at DESC LIMIT 5;
```

## File System Verification

### Check Generated PDF
```bash
ls -la storage/app/compliance/generated/*/FORM_XX.pdf
```

### Check Blade Template
```bash
cat resources/views/compliance/forms/form_xx.blade.php | head -20
```

## Log Analysis

### Check for Errors
```bash
grep -i "form_xx" storage/logs/laravel.log | tail -20
```

### Check for Warnings
```bash
grep -i "warning" storage/logs/laravel.log | grep -i "form_xx"
```

## Performance Check

### Measure Generation Time
```php
$start = microtime(true);
$generator = FormGeneratorFactory::make('FORM_XX');
$data = $generator->generate(1, 1, 3, 2024);
$time = microtime(true) - $start;
echo "Generation time: {$time}s";
```

**Expected:** < 1 second

## Integration Test

### Full Pipeline Test
```php
// 1. Create batch
$batch = \App\Models\ComplianceExecutionBatch::create([
    'tenant_id' => 1,
    'section_id' => 1,
    'period_from' => '2024-03-01',
    'period_to' => '2024-03-31',
    'form_ids' => [/* FORM_XX id */],
    'branch_id' => 1,
    'status' => 'pending'
]);

// 2. Process batch
$service = app(\App\Services\Compliance\ComplianceExecutionService::class);
$results = $service->processBatch($batch->id);

// 3. Verify results
assert($results[0]['success'] === true);
assert(file_exists(storage_path('app/compliance/generated/' . $batch->id . '/FORM_XX.pdf')));

// 4. Verify audit
$auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch->id)->get();
assert($auditLogs->count() > 0);

// 5. Verify certification
$certLog = DB::table('compliance_certification_logs')
    ->where('batch_id', $batch->id)
    ->where('form_code', 'BATCH_SUMMARY')
    ->first();
assert($certLog !== null);

echo "✓ All tests passed!";
```

## Success Criteria

- [x] `php artisan compliance:inspect FORM_XX` recognizes the form
- [x] Header fields display actual values (not "N/A")
- [x] Preview page loads without errors
- [x] PDF file is generated and stored
- [x] Audit runs automatically
- [x] Certification runs automatically
- [x] Inspection pack includes FORM_XX
- [x] No errors in logs
- [x] Generation time < 1 second

## Rollback Plan

If issues occur, revert to previous versions:

```bash
git checkout HEAD -- app/Console/Commands/ComplianceInspectForm.php
git checkout HEAD -- app/Services/Compliance/FormGenerator/ContractorBasedFormGenerator.php
php artisan cache:clear
php artisan config:clear
```

## Support

For issues, check:
1. FORM_XX_FIX_SUMMARY.md - Detailed explanation of fixes
2. Logs in storage/logs/laravel.log
3. Database queries above
4. File system paths above
