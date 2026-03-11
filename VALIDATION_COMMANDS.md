# Statutory Form Services - Validation Commands

## Quick Start

Run these commands to validate all statutory form data services:

### Individual Form Inspection

```bash
# FORM XII - Register of Contractors
php artisan compliance:inspect FORM_XII

# FORM XIII - Register of Workmen Employed by Contractor
php artisan compliance:inspect FORM_XIII

# FORM XIV - Employment Card
php artisan compliance:inspect FORM_XIV

# FORM XVI - Muster Roll
php artisan compliance:inspect FORM_XVI

# FORM XVII - Register of Wages
php artisan compliance:inspect FORM_XVII

# FORM XXIII - Register of Overtime
php artisan compliance:inspect FORM_XXIII
```

### With Specific Parameters

```bash
# Inspect FORM_XIII for specific tenant, branch, and period
php artisan compliance:inspect FORM_XIII --tenant=1 --branch=1 --month=1 --year=2024

# Inspect FORM_XIV for current month
php artisan compliance:inspect FORM_XIV --tenant=1 --branch=1

# Inspect FORM_XVI for specific year
php artisan compliance:inspect FORM_XVI --tenant=1 --branch=1 --year=2024
```

### Full Validation Script

```bash
# Run comprehensive validation for all forms
php validate_forms.php

# With specific parameters
php validate_forms.php --tenant=1 --branch=1 --month=1 --year=2024
```

---

## Expected Output

### Successful Form Inspection

```
================================================================================
FORM XII - Register of Contractors
================================================================================
✓ FORM_XII Data Generated Successfully

Header:
+------------------+---------------------------+
| Key              | Value                     |
+------------------+---------------------------+
| tenant.name      | Company Name              |
| tenant.address   | Company Address           |
| branch.name      | Branch Name               |
| branch.address   | Branch Address            |
+------------------+---------------------------+

Rows: 5 records
+------------------+------------------+------------------+------------------+
| contractor_name  | contractor_addr  | nature_of_work   | work_location    |
+------------------+------------------+------------------+------------------+
| ABC Contractors  | 123 Main St      | Construction     | Site A           |
| XYZ Builders     | 456 Oak Ave      | Renovation       | Site B           |
| ...              | ...              | ...              | ...              |
+------------------+------------------+------------------+------------------+

... and 2 more rows
```

### Validation Script Output

```
================================================================================
STATUTORY FORM SERVICES VALIDATION
================================================================================
Tenant ID: 1
Branch ID: 1
Period: 1/2024
================================================================================

Testing FORM_XII...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 5 records
  ✓ Totals: None
  ✓ Validation: 6/6 checks passed

Testing FORM_XIII...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 12 records
  ✓ Totals: None
  ✓ Validation: 6/6 checks passed

Testing FORM_XIV...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 8 records
  ✓ Totals: None
  ✓ Validation: 6/6 checks passed

Testing FORM_XVI...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 10 records
  ✓ Totals: None
  ✓ Validation: 6/6 checks passed

Testing FORM_XVII...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 15 records
  ✓ Totals: Present
  ✓ Validation: 6/6 checks passed

Testing FORM_XXIII...
  ✓ Data generated successfully
  ✓ Header: Present
  ✓ Rows: 3 records
  ✓ Totals: Present
  ✓ Validation: 6/6 checks passed

================================================================================
VALIDATION SUMMARY
================================================================================
FORM_XII: ✓ PASS (5 rows)
FORM_XIII: ✓ PASS (12 rows)
FORM_XIV: ✓ PASS (8 rows)
FORM_XVI: ✓ PASS (10 rows)
FORM_XVII: ✓ PASS (15 rows)
FORM_XXIII: ✓ PASS (3 rows)
================================================================================
Total: 6 passed, 0 failed
================================================================================

✓ All forms validated successfully!
```

---

## Verification Checklist

After running validation commands, verify:

- [ ] FORM_XII returns contractor data with 7 columns
- [ ] FORM_XIII returns employee data with 12 columns
- [ ] FORM_XIV returns employment card data with 8 columns
- [ ] FORM_XVI returns muster roll with 31 day columns
- [ ] FORM_XVII returns wage data with 16 columns and totals
- [ ] FORM_XXIII returns overtime data with 12 columns and totals
- [ ] All forms have header with tenant and branch info
- [ ] All forms have non-empty rows array (or empty if no data)
- [ ] Totals are calculated correctly for FORM_XVII and FORM_XXIII
- [ ] No errors in console output

---

## Testing Preview & PDF

### Test Form Preview
```bash
# Navigate to preview URL in browser
http://localhost:8000/compliance/forms/FORM_XII/preview
http://localhost:8000/compliance/forms/FORM_XIII/preview
http://localhost:8000/compliance/forms/FORM_XIV/preview
http://localhost:8000/compliance/forms/FORM_XVI/preview
http://localhost:8000/compliance/forms/FORM_XVII/preview
http://localhost:8000/compliance/forms/FORM_XXIII/preview
```

### Test PDF Download
```bash
# Download PDF via API
http://localhost:8000/compliance/forms/FORM_XII/pdf
http://localhost:8000/compliance/forms/FORM_XIII/pdf
http://localhost:8000/compliance/forms/FORM_XIV/pdf
http://localhost:8000/compliance/forms/FORM_XVI/pdf
http://localhost:8000/compliance/forms/FORM_XVII/pdf
http://localhost:8000/compliance/forms/FORM_XXIII/pdf
```

---

## Troubleshooting

### Command Not Found
```bash
# Ensure command is registered
php artisan list | grep compliance:inspect

# If not found, run:
php artisan cache:clear
php artisan config:clear
```

### No Data Returned
```bash
# Check if data exists in database
php artisan tinker

# Check contractors
>>> DB::table('contractor_master')->count()

# Check employees
>>> DB::table('workforce_employee')->count()

# Check payroll entries
>>> DB::table('workforce_payroll_entry')->count()
```

### Validation Fails
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Run with verbose output
php artisan compliance:inspect FORM_XII -v

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()
```

---

## Performance Testing

### Check Query Performance
```bash
# Enable query logging
php artisan tinker

>>> DB::enableQueryLog()
>>> $service = new \App\Services\Compliance\Forms\FormXIIService()
>>> $data = $service->generate(1, 1, 1, 2024)
>>> DB::getQueryLog()
```

### Monitor Execution Time
```bash
# Add timing to validation script
time php validate_forms.php

# Expected: < 5 seconds for all 6 forms
```

---

## Integration Testing

### Test via API
```bash
# Get form data via API
curl -X GET "http://localhost:8000/api/compliance/forms/FORM_XII/data?tenant_id=1&branch_id=1&month=1&year=2024"

# Expected response:
{
    "status": "success",
    "data": {
        "header": {...},
        "rows": [...],
        "totals": {...}
    }
}
```

### Test via Controller
```php
// In controller
$service = new ComplianceExecutionService();
$data = $service->getFormDataViaAPI('FORM_XII', 1, 1, 1, 2024);

// Verify structure
assert(isset($data['header']));
assert(isset($data['rows']));
assert(isset($data['totals']));
```

---

## Production Deployment

Before deploying to production:

1. Run all validation commands
2. Test preview and PDF generation
3. Verify multi-tenant isolation
4. Check performance with large datasets
5. Monitor error logs
6. Test with different user roles
7. Verify caching works correctly

```bash
# Pre-deployment checklist
php artisan compliance:inspect FORM_XII
php artisan compliance:inspect FORM_XIII
php artisan compliance:inspect FORM_XIV
php artisan compliance:inspect FORM_XVI
php artisan compliance:inspect FORM_XVII
php artisan compliance:inspect FORM_XXIII

# Run full validation
php validate_forms.php

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize for production
php artisan optimize
```

---

## Support

For issues or questions:
1. Check `STATUTORY_FORM_SERVICES_COMPLETE.md` for detailed documentation
2. Review `STATUTORY_FORM_SERVICES_QUICK_REFERENCE.md` for examples
3. Check error logs in `storage/logs/laravel.log`
4. Run validation commands with verbose output

