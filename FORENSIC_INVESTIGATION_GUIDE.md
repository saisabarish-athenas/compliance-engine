# FORENSIC DEBUGGING INVESTIGATION GUIDE

## OBJECTIVE
Identify why 17 forms fail to generate while 4 forms work correctly.

## WORKING FORMS (Reference)
- FORM_B (Payroll-based)
- FORM_10 (Payroll-based)
- FORM_12 (Payroll-based)
- FORM_25 (Payroll-based)

## FAILING FORMS (17 total)
- FORM_2, FORM_8, FORM_17, FORM_18, FORM_26, FORM_26A (Factories Act)
- FORM_XIV, FORM_XIX (CLRA)
- HAZARD_REG (Factories Act)
- SHOPS_FORM_VI, SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FORM_C, SHOPS_UNPAID, SHOPS_FINES (Shops)
- ESI_FORM_12, EPF_INSPECTION (Social Security)

## INVESTIGATION STEPS

### STEP 1: RUN FORENSIC DEBUGGER

```bash
# Debug all failing forms
php artisan compliance:forensic-debug --tenant=1 --branch=1 --month=1 --year=2024

# Debug specific form
php artisan compliance:forensic-debug --form=FORM_2 --tenant=1 --branch=1 --month=1 --year=2024
```

**What to look for:**
- API Service: Does it return records?
- Generator: Does it produce rows?
- Template: Does it exist?
- Pipeline: Does full rendering work?

### STEP 2: INSPECT DATABASE

```bash
php artisan compliance:inspect-db --tenant=1 --branch=1 --month=1 --year=2024
```

**What to check:**
- Does workforce_employee table have records?
- Does workforce_payroll_entries have records for the period?
- Does workforce_attendance have records?
- Does workforce_incidents have records?
- Do other required tables have data?

### STEP 3: ANALYZE API SERVICE OUTPUT

For each failing form, check:

1. **API Service Registration**
   - Is the form code registered in FormApiServiceFactory?
   - Does the service class exist?

2. **API Service Query**
   - Does the query filter by tenant_id and branch_id?
   - Does the query return records?
   - Are all required fields selected?

3. **API Service Response Structure**
   - Does it return: records, meta, tenant, branch, period?
   - Are meta fields correct (tenant_id, branch_id, month, year)?

### STEP 4: ANALYZE GENERATOR OUTPUT

For each failing form, check:

1. **Generator Registration**
   - Is the form code registered in FormGeneratorFactory?
   - Does the generator class exist?

2. **Generator prepareData() Method**
   - Does it extract records from rawData['records']?
   - Does it build rows array?
   - Does it extract header information?

3. **Generator Output Structure**
   - Does it return: header, rows, totals, is_nil?
   - Are header keys present: form_title, tenant, factory_name, place, district?
   - Are rows populated when records exist?

### STEP 5: ANALYZE TEMPLATE VARIABLES

For each failing form, check:

1. **Template Registration**
   - Is the form code registered in FormTemplateRegistry?
   - Does the template file exist?

2. **Template Variable References**
   - What variables does the template reference?
   - Are they provided by the generator?
   - Are they provided by the orchestrator?

3. **Template Rendering**
   - Can the template render with provided variables?
   - Are there undefined variable errors?

### STEP 6: TRACE ORCHESTRATOR DATA PROPAGATION

Check ComplianceOrchestrator::executePreview():

```php
$viewData = array_merge(
    $formData['header'] ?? [],
    [
        'form_title' => $formData['header']['form_title'] ?? $formCode,
        'form_code' => $formCode,
        'period_month' => $month,
        'period_year' => $year,
        'header' => $formData['header'] ?? [],
        'rows' => $formData['rows'] ?? [],
        'entries' => $formData['rows'] ?? [],
        'totals' => $formData['totals'] ?? [],
        'is_nil' => $formData['is_nil'] ?? empty($formData['rows'])
    ]
);
```

**Verify:**
- Header fields are spread into viewData
- rows and entries both point to formData['rows']
- All required variables are present

### STEP 7: IDENTIFY DATA LOSS POINTS

Create a trace for each failing form:

```
API Service
  ↓ (records: X)
Generator
  ↓ (rows: Y)
Orchestrator
  ↓ (viewData keys: Z)
Template
  ↓ (rendered: success/fail)
```

**If records > 0 but rows = 0:**
- Generator is not extracting records correctly
- Check prepareData() implementation

**If rows > 0 but template fails:**
- Missing template variables
- Check template variable references

**If records = 0:**
- Database has no data for this period/tenant/branch
- Check database inspection results

### STEP 8: VERIFY FORM REGISTRY

```bash
php artisan tinker
>>> DB::table('compliance_forms_master')->count()
>>> DB::table('compliance_forms_master')->pluck('form_code')
```

**Check:**
- Are all 34 forms registered?
- Are there duplicate form codes?
- Are form codes correct?

### STEP 9: VERIFY BATCH PROCESSING

Check ComplianceExecutionService::processBatch():

```bash
php artisan tinker
>>> $batch = ComplianceExecutionBatch::find(1)
>>> $batch->form_ids
>>> $batch->period_month
>>> $batch->period_year
```

**Verify:**
- form_ids is an array
- period_month and period_year are set
- Orchestrator is called for each form

### STEP 10: GENERATE FORENSIC REPORT

After running all steps, create a report:

```
FORM_CODE | API_RECORDS | GENERATOR_ROWS | TEMPLATE_EXISTS | PIPELINE_SUCCESS | ROOT_CAUSE
FORM_2    | 0           | 0              | YES             | FAIL             | No employee data
FORM_8    | 5           | 0              | YES             | FAIL             | Generator bug
FORM_17   | 5           | 5              | YES             | FAIL             | Missing template var
```

## COMMON ISSUES TO CHECK

### Issue 1: API Service Returns 0 Records
**Cause:** Database has no data for the period/tenant/branch
**Solution:** Check database inspection results

### Issue 2: API Service Returns Records but Generator Produces 0 Rows
**Cause:** Generator's prepareData() is not extracting records correctly
**Solution:** Check generator implementation, verify it accesses rawData['records']

### Issue 3: Generator Produces Rows but Template Fails
**Cause:** Template references variables not provided by generator or orchestrator
**Solution:** Check template variable references, add missing variables to generator header

### Issue 4: Template Renders but PDF Generation Fails
**Cause:** PDF library issue or template has unsupported HTML
**Solution:** Check PDF generation logs, simplify template HTML

### Issue 5: Preview Works but Batch Fails
**Cause:** Batch processor not using orchestrator or different parameters
**Solution:** Verify ComplianceExecutionService::processBatch() uses orchestrator

## DEBUGGING COMMANDS

```bash
# Run forensic debugger on all failing forms
php artisan compliance:forensic-debug

# Run forensic debugger on specific form
php artisan compliance:forensic-debug --form=FORM_2

# Inspect database
php artisan compliance:inspect-db

# Test preview endpoint
curl http://localhost/compliance/batch/1/preview/FORM_2

# Test batch processing
php artisan tinker
>>> $service = app(\App\Services\Compliance\ComplianceExecutionService::class)
>>> $service->processBatch(1)

# Check logs
tail -f storage/logs/laravel.log
```

## EXPECTED OUTCOMES

### If All Forms Work
- All 34 forms generate successfully
- Preview and batch modes work identically
- PDF generation succeeds
- No missing template variables

### If Some Forms Fail
- Forensic report identifies root cause
- Code patches are applied
- Forms are re-tested
- Root cause is documented

### If Database Has No Data
- Forensic report shows 0 records for all forms
- Database seeding is required
- Test data is created
- Forms are re-tested with data

## NEXT STEPS

1. Run forensic debugger on all failing forms
2. Analyze results and identify patterns
3. Create code patches for identified issues
4. Test patches on failing forms
5. Verify all 34 forms work correctly
6. Document root causes and solutions
