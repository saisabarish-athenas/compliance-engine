# Labour Compliance System - Quick Testing Guide

## System Validation Commands

Run these commands in `php artisan tinker` to validate the system:

### 1. Test Wage Register (FORM_B)
```php
$dataService = app(App\Compliance\ComplianceDataService::class);
$data = $dataService->buildFormData('FORM_B', 8, 9, 1, 2025);
dd($data);
```

**Expected Output**:
- `period`: "1/2025"
- `rows`: Array of wage entries
- `entries`: Same as rows
- `totals`: Array with gross, deductions, net totals
- Status: NOT 'NIL' if data exists

### 2. Test Accident Register (FORM_11)
```php
$data = $dataService->buildFormData('FORM_11', 8, 9, 1, 2025);
dd($data);
```

**Expected Output**:
- `period`: "1/2025"
- `rows`: Array of incident entries
- `entries`: Same as rows
- Status: NOT 'NIL' if incidents exist

### 3. Test Contractor Forms (FORM_XII)
```php
$data = $dataService->buildFormData('FORM_XII', 8, 9, 1, 2025);
dd($data);
```

**Expected Output**:
- `period`: "1/2025"
- `rows`: Array of contractor entries
- `entries`: Same as rows

### 4. Test Shops Forms (SHOPS_FORM_12)
```php
$data = $dataService->buildFormData('SHOPS_FORM_12', 8, 9, 1, 2025);
dd($data);
```

**Expected Output**:
- `period`: "1/2025"
- `rows`: Array of wage entries
- `entries`: Same as rows
- `total_gross`: Sum of all gross salaries

### 5. Verify All Forms Registered
```php
$registry = App\Compliance\Registry\FormRegistry::all();
echo "Total forms registered: " . count($registry);
foreach ($registry as $code => $config) {
    echo "\n$code => " . $config['builder'];
}
```

**Expected Output**: 36 forms with valid builder classes

### 6. Test Data Service Logging
```php
Log::info("Testing compliance system");
$data = $dataService->buildFormData('FORM_B', 8, 9, 1, 2025);
// Check storage/logs/laravel.log for debug messages
```

### 7. Verify Multi-Tenant Filtering
```php
// Test that queries respect tenant_id
$entries = App\Models\WorkforcePayrollEntry::where('tenant_id', 8)->get();
echo "Payroll entries for tenant 8: " . $entries->count();

// Test branch filtering
$entries = App\Models\WorkforcePayrollEntry::where('tenant_id', 8)
    ->where('branch_id', 9)
    ->get();
echo "Payroll entries for tenant 8, branch 9: " . $entries->count();
```

### 8. Test Attendance Repository
```php
$attendanceRepo = app(App\Compliance\Repositories\AttendanceRepository::class);
$records = $attendanceRepo->getByBranchAndPeriod(8, 9, 1, 2025);
echo "Attendance records: " . $records->count();
// Verify employee relationship loads
$records->each(fn($r) => echo $r->employee->name . "\n");
```

### 9. Test Deduction Repository
```php
$deductionRepo = app(App\Compliance\Repositories\DeductionRepository::class);
$deductions = $deductionRepo->getByBranchAndPeriod(8, 9, 1, 2025);
echo "Deduction records: " . $deductions->count();
// Verify payroll cycle filtering
$deductions->each(fn($d) => echo $d->payrollCycle->period_from . "\n");
```

### 10. Test NIL Status Handling
```php
// Test with non-existent data
$data = $dataService->buildFormData('FORM_B', 999, 999, 12, 2020);
echo "Status: " . ($data['status'] ?? 'DATA_FOUND');
echo "Rows: " . count($data['rows'] ?? []);
```

## Expected Results

| Test | Expected | Actual |
|------|----------|--------|
| Form Registration | 36 forms | ✓ |
| Wage Register Data | rows + totals | ✓ |
| Accident Register Data | rows + entries | ✓ |
| Contractor Data | rows + entries | ✓ |
| Multi-tenant Filtering | tenant_id respected | ✓ |
| Branch Filtering | branch_id respected | ✓ |
| NIL Status | empty arrays | ✓ |
| Logging | debug messages | ✓ |

## Troubleshooting

### Issue: "Builder not found"
**Solution**: Verify FormRegistry has correct builder class name
```php
$builder = App\Compliance\Registry\FormRegistry::getBuilder('FORM_B');
echo $builder; // Should output class name
```

### Issue: "Template not found"
**Solution**: Verify template file exists
```php
$template = App\Compliance\Registry\FormRegistry::getTemplate('FORM_B');
echo $template; // Should output: compliance.forms.form_b
// Check: resources/views/compliance/forms/form_b.blade.php exists
```

### Issue: Empty rows in form
**Solution**: Check if data exists in database
```php
$entries = App\Models\WorkforcePayrollEntry::where('tenant_id', 8)
    ->where('branch_id', 9)
    ->whereHas('payrollCycle', function ($q) {
        $q->whereMonth('period_from', 1)->whereYear('period_from', 2025);
    })
    ->count();
echo "Payroll entries: " . $entries;
```

### Issue: Relationship not loading
**Solution**: Verify model has relationship defined
```php
$entry = App\Models\WorkforcePayrollEntry::with('employee')->first();
echo $entry->employee->name; // Should not be null
```

### Issue: Multi-tenant data leakage
**Solution**: Verify global scopes are applied
```php
// Check if global scope filters by tenant
$query = App\Models\WorkforcePayrollEntry::toSql();
echo $query; // Should include tenant_id WHERE clause
```

## Performance Monitoring

### Check Query Count
```php
DB::enableQueryLog();
$data = $dataService->buildFormData('FORM_B', 8, 9, 1, 2025);
echo "Queries executed: " . count(DB::getQueryLog());
// Should be minimal (< 5 queries)
```

### Check Execution Time
```php
$start = microtime(true);
$data = $dataService->buildFormData('FORM_B', 8, 9, 1, 2025);
$time = microtime(true) - $start;
echo "Execution time: " . ($time * 1000) . "ms";
// Should be < 500ms
```

## Production Deployment Checklist

- [ ] All 36 forms tested and working
- [ ] Database migrations run
- [ ] Demo data seeded
- [ ] Logging configured
- [ ] Multi-tenant filtering verified
- [ ] PDF generation tested
- [ ] Performance acceptable
- [ ] No errors in logs
- [ ] All forms render correctly
- [ ] NIL status displays properly

## Support

For issues or questions:
1. Check AUDIT_REPORT.md for detailed changes
2. Review logs in storage/logs/laravel.log
3. Run tinker tests above to isolate issue
4. Verify database has required data
