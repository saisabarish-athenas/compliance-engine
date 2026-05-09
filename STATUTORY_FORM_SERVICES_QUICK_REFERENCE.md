# Statutory Form Services - Quick Reference

## Service Classes Location
```
app/Services/Compliance/Forms/
├── FormXIIService.php          (Register of Contractors)
├── FormXIIIService.php         (Register of Workmen)
├── FormXIVService.php          (Employment Card)
├── FormXVIService.php          (Muster Roll)
├── FormXVIIService.php         (Register of Wages)
├── FormXXIIIService.php        (Register of Overtime)
└── BaseFormService.php         (Base class with common methods)
```

## Usage Examples

### Direct Service Usage
```php
use App\Services\Compliance\Forms\FormXIIService;

$service = new FormXIIService();
$data = $service->generate(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024
);

// Returns:
// [
//     'header' => [...],
//     'rows' => [...],
//     'totals' => [...]
// ]
```

### Via ComplianceExecutionService
```php
use App\Services\Compliance\ComplianceExecutionService;

$service = new ComplianceExecutionService();
$data = $service->getFormDataViaAPI(
    formCode: 'FORM_XII',
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024
);
```

### Via Controller
```php
use App\Http\Controllers\ComplianceExecutionController;

// Preview
Route::get('/compliance/forms/FORM_XII/preview', [ComplianceExecutionController::class, 'previewForm']);

// PDF Download
Route::get('/compliance/forms/FORM_XII/pdf', [ComplianceExecutionController::class, 'downloadPDF']);
```

## Artisan Commands

### Inspect Form Data
```bash
# Basic inspection
php artisan compliance:inspect FORM_XII

# With specific tenant, branch, and period
php artisan compliance:inspect FORM_XIII --tenant=1 --branch=1 --month=1 --year=2024

# All forms
php artisan compliance:inspect FORM_XIV
php artisan compliance:inspect FORM_XVI
php artisan compliance:inspect FORM_XVII
php artisan compliance:inspect FORM_XXIII
```

## Data Structure Reference

### Header Structure (All Forms)
```php
'header' => [
    'tenant' => [
        'name' => 'Company Name',
        'address' => 'Company Address'
    ],
    'branch' => [
        'name' => 'Branch Name',
        'address' => 'Branch Address'
    ]
]
```

### Row Structure by Form

#### FORM XII (Register of Contractors)
```php
[
    'contractor_name' => 'ABC Contractors',
    'contractor_address' => '123 Main St',
    'nature_of_work' => 'Construction',
    'work_location' => 'Site A',
    'contract_from' => '2024-01-01',
    'contract_to' => '2024-01-31',
    'max_workers' => 50
]
```

#### FORM XIII (Register of Workmen)
```php
[
    'name' => 'John Doe',
    'age' => 35,
    'sex' => 'M',
    'father_name' => 'James Doe',
    'designation' => 'Laborer',
    'permanent_address' => 'Village, District',
    'local_address' => 'Local Address',
    'joining_date' => '2024-01-01',
    'termination_date' => '2024-01-31',
    'termination_reason' => 'Contract End',
    'remarks' => ''
]
```

#### FORM XIV (Employment Card)
```php
[
    'name' => 'John Doe',
    'employee_code' => 'EMP001',
    'designation' => 'Laborer',
    'daily_rate' => 500.00,
    'joining_date' => '2024-01-01',
    'tenure_end' => '2024-01-31',
    'contractor_name' => 'ABC Contractors',
    'contractor_address' => '123 Main St'
]
```

#### FORM XVI (Muster Roll)
```php
[
    'name' => 'John Doe',
    'father_name' => 'James Doe',
    'sex' => 'M',
    'contractor_name' => 'ABC Contractors',
    'day_1' => 'P',
    'day_2' => 'P',
    // ... day_3 to day_31
    'remarks' => ''
]
```

#### FORM XVII (Register of Wages)
```php
[
    'name' => 'John Doe',
    'employee_code' => 'EMP001',
    'designation' => 'Laborer',
    'days_worked' => 20,
    'unit_work' => '',
    'daily_rate' => 500.00,
    'basic_wages' => 10000.00,
    'da' => 1000.00,
    'overtime' => 500.00,
    'other_cash' => 0.00,
    'gross_salary' => 11500.00,
    'esi' => 575.00,
    'pf' => 1150.00,
    'pt' => 0.00,
    'total_deductions' => 1725.00,
    'net_amount' => 9775.00
]
```

#### FORM XXIII (Register of Overtime)
```php
[
    'name' => 'John Doe',
    'father_name' => 'James Doe',
    'sex' => 'M',
    'designation' => 'Laborer',
    'overtime_dates' => '2024-01-15',
    'total_overtime' => 5.0,
    'normal_rate' => 500.00,
    'overtime_rate' => 750.00,
    'overtime_earnings' => 3750.00,
    'payment_date' => '2024-01-31',
    'remarks' => ''
]
```

## Totals Structure

### FORM XVII Totals
```php
'totals' => [
    'total_basic' => 100000.00,
    'total_da' => 10000.00,
    'total_overtime' => 5000.00,
    'total_gross' => 115000.00,
    'total_deductions' => 17250.00,
    'total_net' => 97750.00
]
```

### FORM XXIII Totals
```php
'totals' => [
    'total_overtime_hours' => 50.0,
    'total_overtime_earnings' => 37500.00
]
```

## Database Query Patterns

### Contractor-Based Forms (XII, XIII, XIV)
```sql
SELECT ... FROM contractor_master
LEFT JOIN contract_labour_deployment ON ...
LEFT JOIN workforce_employee ON ...
WHERE tenant_id = ? AND branch_id = ?
AND deployment_start BETWEEN ? AND ?
```

### Payroll-Based Forms (XVI, XVII, XXIII)
```sql
SELECT ... FROM workforce_payroll_entry
JOIN workforce_employee ON ...
WHERE tenant_id = ? AND branch_id = ?
AND period_start BETWEEN ? AND ?
```

## Validation Checklist

- [ ] All 6 forms generate data without errors
- [ ] Header contains correct tenant and branch info
- [ ] Row count matches expected data
- [ ] All required columns present in rows
- [ ] Totals calculated correctly
- [ ] Date formatting is consistent (YYYY-MM-DD)
- [ ] Numeric values are properly formatted
- [ ] Empty rows array when no data (no fake NIL rows)
- [ ] Multi-tenant filtering works correctly
- [ ] Period filtering by month/year works correctly

## Common Issues & Solutions

### Issue: No data returned
**Solution:** 
- Verify tenant_id and branch_id exist in database
- Check data exists in source tables for the period
- Verify period_month and period_year are valid

### Issue: Incorrect column values
**Solution:**
- Check database column names match service mappings
- Verify data types (especially dates)
- Ensure NULL values are handled with COALESCE

### Issue: Blade template shows "NIL"
**Solution:**
- Verify service returns non-empty rows array
- Check header data is populated correctly
- Ensure row keys match Blade template variable names

### Issue: Totals are incorrect
**Solution:**
- Verify array_sum() is receiving numeric values
- Check rows are not empty before calculating totals
- Ensure calculation logic matches form requirements

## Performance Tips

1. **Indexing:** Ensure foreign keys are indexed
   - `contractor_master.tenant_id`
   - `contract_labour_deployment.contractor_id`
   - `workforce_employee.tenant_id`
   - `workforce_payroll_entry.employee_id`

2. **Query Optimization:** Services use selective column selection
   - No SELECT * queries
   - Aggregation at database level
   - Proper WHERE clause ordering

3. **Caching:** Consider caching for frequently accessed forms
   ```php
   $data = Cache::remember("form_xii_{$tenantId}_{$branchId}_{$month}_{$year}", 3600, function() {
       return $service->generate(...);
   });
   ```

## Integration Checklist

- [x] All 6 services created and optimized
- [x] Database mappings verified
- [x] Multi-tenant support implemented
- [x] Period filtering implemented
- [x] Header data populated correctly
- [x] Row data matches Blade templates
- [x] Totals calculated where applicable
- [x] Factory registration complete
- [x] Artisan command created
- [x] Documentation complete

