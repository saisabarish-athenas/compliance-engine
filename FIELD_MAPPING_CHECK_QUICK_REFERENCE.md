# Field Mapping Check - Quick Reference

## Command

```bash
php artisan compliance:field-map-check
```

## What It Checks

```
API Service
    ↓ (fields)
Generator
    ↓ (fields)
Blade Template
```

Validates that field names match at each layer.

## Output

### Console Table
Shows for each form:
- Form code
- Number of API fields
- Number of generator fields
- Number of template fields
- Status (✔ or ⚠)
- Issues (if any)

### Summary
```
Total Forms: 34
✔ OK: 32
⚠ Warnings: 2
❌ Errors: 0
```

### Report File
```
storage/logs/compliance_field_mapping_report.log
```

## Status Meanings

| Status | Meaning | Action |
|--------|---------|--------|
| ✔ | All fields match | No action needed |
| ⚠ | Some fields missing | Fix generator or template |
| ❌ | Error occurred | Check logs |

## Common Issues

### Missing in Generator
- API returns field but generator doesn't include it
- **Fix:** Update generator's prepareData() method

### Missing in Template
- Generator returns field but template doesn't use it
- **Fix:** Update blade template

### Errors
- Exception during analysis
- **Fix:** Check error message and logs

## Quick Fixes

### Add Field to Generator
```php
public function prepareData($data): array
{
    return [
        'rows' => array_map(fn($row) => [
            'field_name' => $row['field_name'], // Add this
        ], $data['rows']),
    ];
}
```

### Add Field to Template
```blade
<td>{{ $row->field_name }}</td>
```

## Usage

### Check All Forms
```bash
php artisan compliance:field-map-check
```

### Check Specific Tenant
```bash
php artisan compliance:field-map-check --tenant_id=2 --branch_id=1
```

### View Report
```bash
cat storage/logs/compliance_field_mapping_report.log
```

## Why This Matters

Forms render blank rows when:
- API returns data but generator doesn't pass it
- Generator prepares data but template doesn't use it
- Field names don't match between layers

This command identifies these issues automatically.

## Performance

- Checks all 34 forms
- Takes 5-10 seconds
- Safe to run anytime
- No data modified

---

**Use this command to debug blank rows in compliance forms!**
