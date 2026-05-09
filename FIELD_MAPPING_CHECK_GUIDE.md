# Field Mapping Check - Complete Guide

## Overview

The `compliance:field-map-check` command validates field mappings across the three-layer compliance architecture:

```
API Service → Generator → Blade Template
```

It detects when field names don't match between layers, which causes forms to render blank rows.

## Command

```bash
php artisan compliance:field-map-check
```

## Options

```bash
# With specific tenant and branch
php artisan compliance:field-map-check --tenant_id=1 --branch_id=1
```

## What It Does

### 1. Analyzes API Service Output
- Calls `FormApiServiceFactory::make($formCode)`
- Executes `$api->fetch($tenantId, $branchId, $month, $year)`
- Extracts available fields from returned data

### 2. Analyzes Generator Output
- Calls `FormGeneratorFactory::make($formCode)`
- Executes `$generator->prepareData($apiData)`
- Extracts field names from prepared data

### 3. Analyzes Blade Template
- Loads template from `FormTemplateRegistry`
- Parses template file for variable usage
- Detects patterns like `$row->field` and `$row['field']`

### 4. Validates Mappings
- Checks if API fields exist in generator input
- Checks if generator output fields match template variables
- Reports missing fields at each layer

## Output

### Console Table
```
Form | API Fields | Generator Fields | Template Fields | Status | Issues
FORM_B | 10 | 10 | 10 | ✔ | OK
FORM_XX | 8 | 8 | 7 | ⚠ | Missing in Template: fine_amount
FORM_XII | 12 | 11 | 12 | ⚠ | Missing in Generator: nature_of_work
```

### Summary
```
Summary:
  Total Forms: 34
  ✔ OK: 32
  ⚠ Warnings: 2
  ❌ Errors: 0
```

### Report File
```
storage/logs/compliance_field_mapping_report.log
```

## Usage Examples

### Check All Forms
```bash
php artisan compliance:field-map-check
```

### Check Specific Tenant
```bash
php artisan compliance:field-map-check --tenant_id=2 --branch_id=1
```

## Interpreting Results

### Status: ✔ OK
- All fields match across layers
- Form should render correctly

### Status: ⚠ WARNING
- Some fields are missing
- Form may render blank rows
- Check "Issues" column for details

### Issues Column

**Missing in Generator**
- API returns field but generator doesn't include it
- Fix: Update generator's prepareData() method

**Missing in Template**
- Generator returns field but template doesn't use it
- Fix: Update blade template to use field

**Errors**
- Exception occurred during analysis
- Check logs for details

## Common Issues & Fixes

### Issue: Missing in Generator
```
Missing in Generator: overtime_hours
```

**Fix:** Update generator to include the field:
```php
public function prepareData($data): array
{
    return [
        'rows' => array_map(fn($row) => [
            'employee_code' => $row['employee_code'],
            'overtime_hours' => $row['overtime_hours'], // Add this
        ], $data['rows']),
    ];
}
```

### Issue: Missing in Template
```
Missing in Template: fine_amount
```

**Fix:** Update blade template to use the field:
```blade
@foreach($rows as $row)
    <tr>
        <td>{{ $row->employee_code }}</td>
        <td>{{ $row->fine_amount }}</td> <!-- Add this -->
    </tr>
@endforeach
```

## Field Extraction Details

### API Fields
- Extracted from first row of API response
- Represents all available database fields

### Generator Fields
- Extracted from prepared data structure
- Represents fields passed to template

### Template Fields
- Extracted by parsing blade file
- Detects `$row->field` and `$row['field']` patterns
- Represents fields actually used in template

## Report Format

The report file contains:
- Timestamp
- Form code
- Status (OK/WARNING)
- All field lists
- Missing fields at each layer
- Any errors encountered

Example:
```
Field Mapping Report - 2024-03-20 10:30:45

Form: FORM_B
Status: OK
API Fields: employee_code, name, designation, basic_salary, ...
Generator Fields: employee_code, name, designation, basic_salary, ...
Template Fields: employee_code, name, designation, basic_salary, ...

Form: FORM_XX
Status: WARNING
API Fields: employee_code, name, fine_date, amount, reason
Generator Fields: employee_code, name, fine_date, amount
Template Fields: employee_code, name, fine_date, amount, reason
Missing in Generator: reason
```

## Troubleshooting

### Command Not Found
```bash
php artisan cache:clear
composer dump-autoload
```

### No Forms Detected
- Verify FormGeneratorFactory has forms registered
- Check FormApiServiceFactory has services
- Verify FormTemplateRegistry has templates

### Template Not Found
- Check template path in FormTemplateRegistry
- Verify blade file exists at `resources/views/compliance/forms/`

### Field Extraction Issues
- Check blade template syntax
- Verify fields use `$row->field` or `$row['field']` patterns
- Complex expressions may not be detected

## Performance

- Checks all 34 forms
- Typical execution time: 5-10 seconds
- Minimal database queries (only fetch calls)

## Best Practices

1. **Run After Adding New Fields**
   - When adding fields to API service
   - When updating generator
   - When modifying blade template

2. **Fix Warnings Immediately**
   - Blank rows indicate field mapping issues
   - Use this command to identify problems
   - Fix before deploying to production

3. **Review Report Regularly**
   - Check report file for patterns
   - Identify forms with recurring issues
   - Plan refactoring if needed

## Integration with CI/CD

Add to your deployment pipeline:
```bash
php artisan compliance:field-map-check
if [ $? -ne 0 ]; then
    echo "Field mapping check failed"
    exit 1
fi
```

## Notes

- Command is safe to run multiple times
- No data is modified
- Read-only operation
- Can be run in production

---

**Status:** ✅ COMPLETE
