# FORM DATA TRACE ANALYSIS - QUICK REFERENCE

## COMMANDS

### 1. Trace All Forms
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

**Output:**
- Console display with form-by-form status
- Report file: `storage/logs/form_data_trace_report.log`

**Example Output:**
```
Tracing: FORM_B
  Status: PASS
  Root Cause: No issues detected
  DB Records: 25
  Generator Rows: 25

Tracing: FORM_XX
  Status: WARNING_EMPTY_DATASET
  Root Cause: No records in dataset: workforce_deductions
  DB Records: 0
  Generator Rows: 0
```

### 2. Trace Specific Form
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form=FORM_B
```

### 3. Trace Specific Period
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --month=3 --year=2024
```

### 4. Generate Diagnostic Report
```bash
php artisan compliance:diagnostic-report --tenant_id=1 --branch_id=1
```

**Output:** Markdown report with:
- Tenant setup status
- Branch setup status
- Data availability summary
- Form registry analysis
- API service coverage
- Generator coverage
- Blade template coverage
- Recommendations

---

## INTERPRETING TRACE RESULTS

### Status Codes

| Status | Meaning | Action |
|--------|---------|--------|
| `PASS` | Form data flows correctly | ✅ No action needed |
| `WARNING_EMPTY_DATASET` | No records in database | ⚠️ Seed data or check filters |
| `WARNING_DATA_LOST_IN_GENERATOR` | DB has data but generator returns empty | ⚠️ Check field mapping |
| `FAIL_TEMPLATE_MISSING` | Blade template not found | ❌ Create template |
| `FAIL_NO_GENERATOR` | No generator registered | ❌ Register generator |
| `ERROR` | Exception during trace | ❌ Debug exception |

### Root Cause Examples

**"No records in dataset: workforce_payroll_entry"**
- Database query returned 0 records
- Check: Do employees exist? Do payroll entries exist for this period?

**"Data lost during generator prepareData() - check field mapping"**
- Database has records but generator output is empty
- Check: Are field names correct? Are values null?

**"Blade template not found: compliance.forms.form_b"**
- Template file doesn't exist
- Check: Does `resources/views/compliance/forms/form_b.blade.php` exist?

**"No generator registered for form"**
- Form not in FormGeneratorFactory
- Check: Is form in `$payrollForms`, `$contractorForms`, etc.?

---

## TRACE REPORT FORMAT

### File Location
`storage/logs/form_data_trace_report.log`

### Report Structure
```
FORM DATA TRACE ANALYSIS REPORT
Generated: 2024-03-15 10:30:45
Tenant: 1, Branch: 1, Period: 3/2024
================================================================================

FORM: FORM_B
--------------------------------------------------------------------------------
Status: PASS
Root Cause: No issues detected
API Service: App\Services\Compliance\FormApis\FormBApiService
Dataset: workforce_payroll_entry
Records Found: 25
Generator Rows: 25
Blade Template: compliance.forms.form_b
Template Exists: YES

FORM: FORM_XX
--------------------------------------------------------------------------------
Status: WARNING_EMPTY_DATASET
Root Cause: No records in dataset: workforce_deductions
API Service: App\Services\Compliance\FormApis\FormXXApiService
Dataset: workforce_deductions
Records Found: 0
Generator Rows: 0
Blade Template: compliance.forms.form_xx
Template Exists: YES
```

---

## DIAGNOSTIC REPORT FORMAT

### File Location
`storage/logs/form_diagnostic_report_YYYY-MM-DD_HH-MM-SS.md`

### Report Sections

#### 1. TENANT SETUP
```
Status: ✅ OK
Name: Acme Manufacturing
Issues: None
```

#### 2. BRANCH SETUP
```
Status: ✅ OK
Name: Main Factory
Issues: None
```

#### 3. DATA AVAILABILITY
```
| Dataset | Count |
|---------|-------|
| employees | ✅ 45 |
| payroll_entries | ✅ 45 |
| attendance_records | ✅ 1200 |
| contractors | ❌ 0 |
| contract_labour | ❌ 0 |
| deductions | ❌ 0 |
| fines | ❌ 0 |
| advances | ❌ 0 |
```

#### 4. FORM REGISTRY
```
- Total Registered: 40
- With Builders: 40
- With Templates: 38
```

#### 5. API SERVICES
```
- Forms with API Services: 13 / 40
- Forms without API Services:
  - FORM_12
  - FORM_2
  - FORM_7
  - ... (27 more)
```

#### 6. GENERATORS
```
- Forms with Generators: 40 / 40
- Forms without Generators: None
```

#### 7. BLADE TEMPLATES
```
- Total Templates: 40
- Existing: 38
- Missing: 2
- Missing Templates:
  - FORM_XXIV
  - FORM_XXV
```

#### 8. RECOMMENDATIONS
```
### ⚠️ No Contractors Found
Action: Seed contractor data

### ⚠️ Missing Blade Templates
Forms: FORM_XXIV, FORM_XXV
Action: Create blade templates in resources/views/compliance/forms/
```

---

## COMMON ISSUES & FIXES

### Issue: All Forms Return EMPTY_DATASET

**Cause:** No employees or payroll data

**Fix:**
```bash
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1 --employees=20
```

### Issue: Specific Form Returns EMPTY_DATASET

**Cause:** Missing data for that form type

**Examples:**
- FORM_XX (Deductions): No deduction records
- FORM_XXI (Fines): No fine records
- FORM_XIII (Contractors): No contractor data

**Fix:** Seed specific data type

### Issue: Form Returns DATA_LOST_IN_GENERATOR

**Cause:** Field mapping mismatch

**Debug:**
1. Check API service returns correct fields
2. Check generator expects same field names
3. Add logging to mapRecordToRow()

**Example:**
```php
// API returns: employee_code
// Generator expects: emp_code
// Result: Field is null, row is incomplete
```

### Issue: Form Returns TEMPLATE_MISSING

**Cause:** Blade template file doesn't exist

**Fix:**
```bash
# Create template
touch resources/views/compliance/forms/form_xx.blade.php

# Add basic structure
@extends('layouts.compliance')
@section('content')
    <table>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['employee_code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
            </tr>
        @endforeach
    </table>
@endsection
```

### Issue: Form Returns NO_GENERATOR

**Cause:** Form not registered in FormGeneratorFactory

**Fix:**
```php
// In FormGeneratorFactory.php
protected static array $payrollForms = [
    'FORM_B', 'FORM_10', 'FORM_25', 'FORM_XX', // Add form here
];
```

---

## VERIFICATION WORKFLOW

### Step 1: Run Trace
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

### Step 2: Review Report
```bash
cat storage/logs/form_data_trace_report.log
```

### Step 3: Identify Issues
Look for forms with status other than `PASS`

### Step 4: Run Diagnostic
```bash
php artisan compliance:diagnostic-report --tenant_id=1 --branch_id=1
```

### Step 5: Apply Fixes
Based on recommendations in diagnostic report

### Step 6: Re-run Trace
Verify fixes worked

---

## DATABASE QUERIES FOR MANUAL VERIFICATION

### Check Employee Count
```sql
SELECT COUNT(*) as total, 
       COUNT(DISTINCT branch_id) as branches
FROM workforce_employee 
WHERE tenant_id = 1;
```

### Check Payroll Entries
```sql
SELECT COUNT(*) as total,
       COUNT(DISTINCT employee_id) as employees,
       COUNT(DISTINCT payroll_cycle_id) as cycles
FROM workforce_payroll_entry 
WHERE tenant_id = 1 AND branch_id = 1;
```

### Check Payroll Cycles
```sql
SELECT id, cycle_name, period_from, period_to
FROM workforce_payroll_cycle
WHERE tenant_id = 1 AND branch_id = 1
ORDER BY period_from DESC;
```

### Check Deductions
```sql
SELECT COUNT(*) as total
FROM workforce_deductions
WHERE tenant_id = 1;
```

### Check Fines
```sql
SELECT COUNT(*) as total
FROM workforce_fines
WHERE tenant_id = 1;
```

### Check Contractors
```sql
SELECT COUNT(*) as total
FROM contractors
WHERE tenant_id = 1;
```

---

## PERFORMANCE NOTES

- Trace analysis scans all 40+ forms: ~5-10 seconds
- Each form trace includes: API service check, DB query, generator execution, template verification
- Diagnostic report includes data availability scan: ~2-3 seconds
- Reports are written to `storage/logs/` for archival

---

## TROUBLESHOOTING

### Command Not Found
```bash
# Ensure command is registered
php artisan list | grep trace

# If not found, run:
php artisan cache:clear
php artisan config:cache
```

### Permission Denied Writing Report
```bash
# Ensure storage/logs is writable
chmod -R 775 storage/logs
```

### Database Connection Error
```bash
# Verify .env database settings
php artisan tinker
>>> DB::connection()->getPdo();
```

### Out of Memory
```bash
# Increase PHP memory limit
php -d memory_limit=512M artisan compliance:trace-form-data
```

