# 🚀 Demo Dataset - Quick Reference

## One-Command Setup

```bash
# 1. Run migrations
php artisan migrate

# 2. Generate demo data
php artisan compliance:generate-demo-dataset

# 3. Verify all forms
php artisan compliance:test-generation
```

## What Gets Created

### Tables (4 new)
- `employee_leave` - 30 leave records
- `holidays` - 10 holiday records
- `hazard_register` - 5 hazard entries
- `employee_financial_register` - 20 financial transactions

### Existing Tables (Populated)
- `workforce_employee` - 50 employees
- `workforce_attendance` - 1500 attendance records
- `payroll_entries` - 150 payroll entries
- `contractors` - 10 contractors
- `contract_labour_deployment` - 30 deployments
- `incident_documents` - 10 incidents
- `bonus_records` - 50 bonus records

## Forms Ready for Demo

| Category | Forms | Status |
|----------|-------|--------|
| CLRA | 10 forms | ✅ Ready |
| Labour Welfare | 4 forms | ✅ Ready |
| Social Security | 3 forms | ✅ Ready |
| Factories Act | 11 forms | ✅ Ready |
| Shops & Establishment | 6 forms | ✅ Ready |
| **Total** | **34 forms** | **✅ Ready** |

## Test Commands

```bash
# Generate dataset
php artisan compliance:generate-demo-dataset

# Test all forms
php artisan compliance:test-generation

# Check specific data
php artisan tinker
>>> App\Models\WorkforceEmployee::where('tenant_id', 1)->count()
=> 50
```

## Multi-Tenant Config

- **Tenant ID**: 1
- **Branch ID**: 1
- All data properly isolated

## Files Created

### Migrations
- `2026_03_20_000008_create_employee_leave_table.php`
- `2026_03_20_000009_create_holidays_table.php`
- `2026_03_20_000010_create_hazard_register_table.php`
- `2026_03_20_000011_create_employee_financial_register_table.php`

### Models
- `app/Models/EmployeeLeave.php`
- `app/Models/Holiday.php`
- `app/Models/HazardRegister.php`
- `app/Models/EmployeeFinancialRegister.php`

### Seeder
- `database/seeders/ComplianceDemoDatasetSeeder.php`

### Commands
- `app/Console/Commands/GenerateDemoDataset.php`
- `app/Console/Commands/TestGeneration.php`

## Expected Output

```
🚀 Starting Demo Dataset Generation...

🗑️  Truncating demo tables...
  ✓ Truncated employee_leave
  ✓ Truncated holidays
  ✓ Truncated hazard_register
  ✓ Truncated employee_financial_register
  ✓ Truncated bonus_records
  ✓ Truncated incident_documents
  ✓ Truncated contract_labour_deployment
  ✓ Truncated contractors
  ✓ Truncated payroll_entries
  ✓ Truncated workforce_attendance
  ✓ Truncated workforce_employee

🌱 Seeding demo data...

✔️  Verifying data counts...

| Data Type | Count | Status |
|-----------|-------|--------|
| Employees | 50 | ✅ |
| Attendance Records | 1500 | ✅ |
| Payroll Entries | 150 | ✅ |
| Contractors | 10 | ✅ |
| Contract Labour Deployments | 30 | ✅ |
| Incidents | 10 | ✅ |
| Hazard Register Entries | 5 | ✅ |
| Financial Transactions | 20 | ✅ |
| Bonus Records | 50 | ✅ |
| Leave Records | 30 | ✅ |
| Holidays | 10 | ✅ |

✅ Demo dataset generation completed successfully!
```

## Troubleshooting

### Migration Error
```bash
php artisan migrate:rollback
php artisan migrate
```

### Seeding Error
```bash
php artisan db:seed --class=ComplianceDemoDatasetSeeder
```

### Check Data
```bash
php artisan tinker
>>> App\Models\WorkforceEmployee::where('tenant_id', 1)->where('branch_id', 1)->count()
```

## Next Steps

1. ✅ Run migrations
2. ✅ Generate demo dataset
3. ✅ Verify forms
4. ✅ Test form preview
5. ✅ Test PDF generation
6. ✅ Client demonstration

---

**Status**: ✅ Complete and Ready for Demo
