# Simplified Batch Workflow - Verification & Testing Guide

## Pre-Deployment Verification

### 1. File Verification

Verify all files are in place:

```bash
# Service
ls -la app/Services/Compliance/FormFrequencyFilterService.php

# Controller
ls -la app/Http/Controllers/Compliance/SimplifiedBatchController.php

# Views
ls -la resources/views/compliance/simplified-batch-create.blade.php
ls -la resources/views/compliance/simplified-batch-show.blade.php
ls -la resources/views/compliance/simplified-batch-data-entry.blade.php

# Routes
grep "SimplifiedBatchController" routes/compliance.php
```

### 2. Database Verification

Verify required tables exist:

```sql
-- Check compliance_forms_master has frequency column
DESCRIBE compliance_forms_master;
-- Should show: frequency column with values: monthly, quarterly, half-yearly, yearly

-- Check compliance_execution_batches
DESCRIBE compliance_execution_batches;
-- Should have: period_month, period_year columns

-- Check compliance_batch_forms
DESCRIBE compliance_batch_forms;

-- Check compliance_manual_data
DESCRIBE compliance_manual_data;

-- Check compliance_manual_uploads
DESCRIBE compliance_manual_uploads;
```

### 3. Route Verification

```bash
# List all compliance routes
php artisan route:list | grep simplified

# Should show:
# GET|HEAD  /compliance/batch/create-simplified
# POST     /compliance/batch/create-simplified
# POST     /compliance/batch/get-applicable-forms
# GET|HEAD /compliance/batch/{id}/show-simplified
# GET|HEAD /compliance/batch/{id}/download-template/{formCode}
# GET|HEAD /compliance/batch/{id}/data-entry
# POST     /compliance/batch/{id}/proceed
```

## Testing Procedures

### Test 1: Form Filtering - January (Monthly Only)

```
1. Go to: http://localhost/compliance/batch/create-simplified
2. Select Month: January
3. Select Year: 2024
4. Observe: Only monthly forms should appear
5. Expected: Forms with frequency='monthly'
```

**Verification SQL**:
```sql
SELECT form_code, form_name, frequency 
FROM compliance_forms_master 
WHERE frequency = 'monthly' AND is_active = 1;
```

### Test 2: Form Filtering - March (Monthly + Quarterly)

```
1. Go to: http://localhost/compliance/batch/create-simplified
2. Select Month: March
3. Select Year: 2024
4. Observe: Monthly and quarterly forms should appear
5. Expected: Forms with frequency IN ('monthly', 'quarterly')
```

**Verification SQL**:
```sql
SELECT form_code, form_name, frequency 
FROM compliance_forms_master 
WHERE frequency IN ('monthly', 'quarterly') AND is_active = 1;
```

### Test 3: Form Filtering - June (Monthly + Quarterly + Half-Yearly)

```
1. Go to: http://localhost/compliance/batch/create-simplified
2. Select Month: June
3. Select Year: 2024
4. Observe: Monthly, quarterly, and half-yearly forms should appear
5. Expected: Forms with frequency IN ('monthly', 'quarterly', 'half-yearly')
```

### Test 4: Form Filtering - December (All Frequencies)

```
1. Go to: http://localhost/compliance/batch/create-simplified
2. Select Month: December
3. Select Year: 2024
4. Observe: All forms should appear
5. Expected: Forms with frequency IN ('monthly', 'quarterly', 'half-yearly', 'yearly')
```

### Test 5: Batch Creation

```
1. Select Month: June, Year: 2024
2. Click "Create Batch"
3. Observe: Redirected to batch details page
4. Verify: Batch ID displayed
5. Verify: Forms table shows applicable forms
6. Check DB: New record in compliance_execution_batches
```

**Verification SQL**:
```sql
SELECT * FROM compliance_execution_batches 
WHERE period_month = 6 AND period_year = 2024 
ORDER BY created_at DESC LIMIT 1;
```

### Test 6: Manual Data Entry

```
1. From batch details, select "Manual Filling" for a form
2. Click "Proceed"
3. Enter data in manual section
4. Click "Proceed to Generation"
5. Verify: Data stored in compliance_manual_data
```

**Verification SQL**:
```sql
SELECT * FROM compliance_manual_data 
WHERE batch_id = [BATCH_ID] 
ORDER BY created_at DESC;
```

### Test 7: PDF Upload

```
1. From batch details, select "Upload PDF" for a form
2. Click "Proceed"
3. Upload a PDF file
4. Click "Proceed to Generation"
5. Verify: File stored and linked to batch
```

**Verification SQL**:
```sql
SELECT * FROM compliance_manual_uploads 
WHERE batch_id = [BATCH_ID] 
ORDER BY uploaded_at DESC;
```

### Test 8: CSV Upload

```
1. From batch details, select "Upload CSV" for a form
2. Click "Proceed"
3. Download CSV template
4. Fill template with data
5. Upload CSV file
6. Click "Proceed to Generation"
7. Verify: Data parsed and stored
```

**Verification SQL**:
```sql
SELECT * FROM compliance_manual_data 
WHERE batch_id = [BATCH_ID] AND dataset_type = 'csv';
```

### Test 9: Template Download

```
1. From batch details, click "Download Template" for a form
2. Verify: File downloads successfully
3. Verify: File contains Blade template code
4. Verify: File name is [FORM_CODE]_template.blade.php
```

### Test 10: Integration with Existing System

```
1. Create batch using simplified workflow
2. Enter data for all forms
3. Click "Proceed to Generation"
4. Verify: Forms generate successfully
5. Verify: Data appears in generated forms
6. Verify: Existing workflow still works
```

## Automated Testing

### Unit Test - Form Filtering

```php
// tests/Unit/FormFrequencyFilterServiceTest.php

public function test_january_returns_monthly_only()
{
    $service = app(FormFrequencyFilterService::class);
    $forms = $service->getApplicableFormsForMonth(1, 2024);
    
    foreach ($forms as $form) {
        $this->assertEquals('monthly', $form->frequency);
    }
}

public function test_march_returns_monthly_and_quarterly()
{
    $service = app(FormFrequencyFilterService::class);
    $forms = $service->getApplicableFormsForMonth(3, 2024);
    
    $frequencies = $forms->pluck('frequency')->unique();
    $this->assertContains('monthly', $frequencies);
    $this->assertContains('quarterly', $frequencies);
}

public function test_december_returns_all_frequencies()
{
    $service = app(FormFrequencyFilterService::class);
    $forms = $service->getApplicableFormsForMonth(12, 2024);
    
    $frequencies = $forms->pluck('frequency')->unique();
    $this->assertContains('monthly', $frequencies);
    $this->assertContains('quarterly', $frequencies);
    $this->assertContains('half-yearly', $frequencies);
    $this->assertContains('yearly', $frequencies);
}
```

### Feature Test - Batch Creation

```php
// tests/Feature/SimplifiedBatchWorkflowTest.php

public function test_create_batch_with_simplified_workflow()
{
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post(route('compliance.simplified-batch.store'), [
        'period_month' => 6,
        'period_year' => 2024,
    ]);
    
    $this->assertDatabaseHas('compliance_execution_batches', [
        'period_month' => 6,
        'period_year' => 2024,
    ]);
}

public function test_get_applicable_forms()
{
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post(route('compliance.simplified-batch.get-forms'), [
        'month' => 6,
        'year' => 2024,
    ]);
    
    $response->assertStatus(200);
    $response->assertJsonStructure(['status', 'forms', 'count']);
}
```

## Performance Testing

### Load Test - Form Filtering

```bash
# Test with 1000 forms
ab -n 1000 -c 10 http://localhost/compliance/batch/get-applicable-forms

# Expected: < 100ms per request
```

### Load Test - Batch Creation

```bash
# Test batch creation
ab -n 100 -c 5 -p data.json http://localhost/compliance/batch/create-simplified

# Expected: < 500ms per request
```

## Rollback Procedure

If issues occur:

```bash
# 1. Remove new files
rm app/Services/Compliance/FormFrequencyFilterService.php
rm app/Http/Controllers/Compliance/SimplifiedBatchController.php
rm resources/views/compliance/simplified-batch-*.blade.php

# 2. Restore original routes
git checkout routes/compliance.php

# 3. Clear cache
php artisan route:cache
php artisan view:cache

# 4. Verify
php artisan route:list | grep compliance
```

## Monitoring

### Log Monitoring

```bash
# Watch for errors
tail -f storage/logs/laravel.log | grep -i "simplified\|batch"

# Check for exceptions
grep -i "exception\|error" storage/logs/laravel.log
```

### Database Monitoring

```sql
-- Monitor batch creation
SELECT COUNT(*) as total_batches,
       COUNT(CASE WHEN period_month = 6 THEN 1 END) as june_batches
FROM compliance_execution_batches
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Monitor data entry
SELECT COUNT(*) as total_entries
FROM compliance_manual_data
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Monitor uploads
SELECT COUNT(*) as total_uploads
FROM compliance_manual_uploads
WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

## Troubleshooting

### Issue: No forms appear for selected month

**Cause**: Forms don't have frequency set
**Solution**: 
```sql
UPDATE compliance_forms_master 
SET frequency = 'monthly' 
WHERE frequency IS NULL;
```

### Issue: Template download fails

**Cause**: Template file doesn't exist
**Solution**: 
```bash
# Check template exists
ls resources/views/compliance/forms/[FORM_CODE].blade.php

# If missing, create from reference
cp resources/views/compliance/forms/form_b.blade.php \
   resources/views/compliance/forms/[FORM_CODE].blade.php
```

### Issue: Data not stored after entry

**Cause**: Database permissions or table missing
**Solution**:
```sql
-- Verify table exists
SHOW TABLES LIKE 'compliance_manual_data';

-- Check permissions
SHOW GRANTS FOR 'user'@'localhost';
```

## Sign-Off Checklist

- [ ] All files deployed
- [ ] Routes verified
- [ ] Database tables verified
- [ ] Form filtering tested (all months)
- [ ] Batch creation tested
- [ ] Manual data entry tested
- [ ] PDF upload tested
- [ ] CSV upload tested
- [ ] Template download tested
- [ ] Integration with generators tested
- [ ] Existing workflow still works
- [ ] No errors in logs
- [ ] Performance acceptable
- [ ] Documentation complete

## Success Criteria

✅ Form filtering works for all months
✅ Batch creation succeeds
✅ Data entry methods work
✅ Template download works
✅ Integration with generators works
✅ No breaking changes
✅ Performance acceptable
✅ All tests pass

---

**Testing Date**: ___________
**Tested By**: ___________
**Status**: ✅ VERIFIED / ❌ ISSUES FOUND

**Notes**: ___________________________________________
