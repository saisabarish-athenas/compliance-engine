# Demo Dataset Generator - Quick Reference

## Overview

The `compliance:generate-demo-dataset` command generates demo data for empty compliance datasets:
- `workforce_fines` - Register of Fines
- `workforce_advances` - Register of Advances

This ensures forms FORM_XX, FORM_XXII, and SHOPS_FINES render correctly during testing.

## Command

```bash
php artisan compliance:generate-demo-dataset
```

## Options

```bash
# With specific tenant and branch
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1

# With specific month and year
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1 --month=1 --year=2024

# All options
php artisan compliance:generate-demo-dataset \
  --tenant_id=1 \
  --branch_id=1 \
  --month=1 \
  --year=2024
```

## What It Does

### Fines Data
- Creates 15 demo fine records
- Assigns to active employees
- Includes:
  - employee_id
  - tenant_id
  - branch_id
  - fine_date (within the specified month)
  - amount (₹100-500)
  - reason (unauthorized absence, late arrival, etc.)
  - remarks

### Advances Data
- Creates 12 demo advance records
- Assigns to active employees
- Includes:
  - employee_id
  - tenant_id
  - branch_id
  - advance_date (within the specified month)
  - advance_amount (₹2,000-10,000)
  - reason (personal emergency, medical, etc.)
  - remarks

## Multi-Tenant Safety

All records include:
- ✅ tenant_id
- ✅ branch_id

Data is isolated per tenant and branch.

## Usage Example

```bash
# Generate demo data for tenant 1, branch 1, January 2024
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1 --month=1 --year=2024

# Output:
# Generating demo compliance data for Tenant 1, Branch 1
# ✓ Created 15 fines records
# ✓ Created 12 advances records
# ✅ Demo dataset generated successfully
```

## Verification

After running the command, verify data was created:

```bash
# Check fines
php artisan tinker
>>> DB::table('workforce_fines')->where('tenant_id', 1)->count()
=> 15

# Check advances
>>> DB::table('workforce_advances')->where('tenant_id', 1)->count()
=> 12
```

## Forms Affected

After running this command, these forms will render with data:

1. **FORM_XX** - Register of Fines
   - Shows all fines records for the period
   - Displays employee, date, amount, reason

2. **FORM_XXII** - Register of Advances
   - Shows all advances records for the period
   - Displays employee, date, amount, reason

3. **SHOPS_FINES** - Shops Register of Fines
   - Shows fines for shops establishment
   - Same data as FORM_XX

## Testing Forms

After generating demo data, test the forms:

```bash
# Preview form
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XX

# Generate PDF
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XXII
```

## Data Isolation

- Each tenant has separate fines and advances
- Each branch has separate records
- No cross-tenant data leakage
- Multi-tenant safety enforced

## Troubleshooting

### No employees found
- Ensure employees exist for the tenant/branch
- Run: `php artisan compliance:generate-demo-dataset` first to create employees

### Command not found
- Clear cache: `php artisan cache:clear`
- Regenerate autoload: `composer dump-autoload`

### Data not appearing in forms
- Verify data was inserted: Check database directly
- Verify tenant_id and branch_id match
- Run compliance trace: `php artisan compliance:trace-form-data`

## Notes

- Command uses database transactions (all-or-nothing)
- Generates realistic demo data
- Safe to run multiple times (creates new records each time)
- No existing data is deleted
- Respects multi-tenant isolation
