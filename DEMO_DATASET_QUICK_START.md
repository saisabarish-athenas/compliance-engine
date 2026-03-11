# Demo Dataset Generator - Quick Start

## 🚀 Get Started in 3 Steps

### Step 1: Generate Demo Data
```bash
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1 --month=1 --year=2024
```

**Output:**
```
Generating demo compliance data for Tenant 1, Branch 1
✓ Created 15 fines records
✓ Created 12 advances records
✅ Demo dataset generated successfully
```

### Step 2: Verify Data Created
```bash
php artisan tinker
>>> DB::table('workforce_fines')->where('tenant_id', 1)->count()
=> 15
>>> DB::table('workforce_advances')->where('tenant_id', 1)->count()
=> 12
```

### Step 3: Test Forms
```bash
# Test FORM_XX (Register of Fines)
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XX

# Test FORM_XXII (Register of Advances)
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XXII

# Test SHOPS_FINES
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=SHOPS_FINES
```

## 📋 What Gets Created

### Fines Data (15 records)
- Employee ID: From active employees
- Fine Date: Random within specified month
- Amount: ₹100-500
- Reason: Unauthorized absence, late arrival, insubordination, etc.
- Remarks: "Fine imposed as per company policy"

### Advances Data (12 records)
- Employee ID: From active employees
- Advance Date: Random within specified month
- Amount: ₹2,000-10,000
- Reason: Personal emergency, medical, education, etc.
- Remarks: "Advance approved and disbursed"

## 🎯 Forms That Will Now Render

| Form Code | Form Name | Status |
|-----------|-----------|--------|
| FORM_XX | Register of Fines | ✅ Will render with data |
| FORM_XXII | Register of Advances | ✅ Will render with data |
| SHOPS_FINES | Shops Register of Fines | ✅ Will render with data |

## 🔒 Multi-Tenant Safe

All data includes:
- ✅ tenant_id
- ✅ branch_id

Data is isolated per tenant and branch.

## 💡 Tips

### Generate for Different Periods
```bash
# January 2024
php artisan compliance:generate-demo-dataset --month=1 --year=2024

# February 2024
php artisan compliance:generate-demo-dataset --month=2 --year=2024

# December 2024
php artisan compliance:generate-demo-dataset --month=12 --year=2024
```

### Generate for Different Tenants
```bash
# Tenant 1, Branch 1
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1

# Tenant 2, Branch 1
php artisan compliance:generate-demo-dataset --tenant_id=2 --branch_id=1

# Tenant 1, Branch 2
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=2
```

### Run All Forms Test
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

## ✅ Verification

After running the command, verify:

1. **Data exists in database**
   ```bash
   php artisan tinker
   >>> DB::table('workforce_fines')->count()
   >>> DB::table('workforce_advances')->count()
   ```

2. **Forms render with data**
   ```bash
   php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XX
   ```

3. **PDFs generate successfully**
   ```bash
   php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_XXII --mode=pdf
   ```

## 🆘 Troubleshooting

### Command not found
```bash
php artisan cache:clear
composer dump-autoload
```

### No employees found
- Ensure employees exist for the tenant/branch
- Check: `DB::table('workforce_employee')->where('tenant_id', 1)->count()`

### Data not appearing in forms
- Verify tenant_id and branch_id match
- Check database directly: `DB::table('workforce_fines')->count()`
- Run compliance trace: `php artisan compliance:trace-form-data`

## 📚 More Information

For detailed information, see:
- `DEMO_DATASET_GENERATOR_GUIDE.md` - Complete guide
- `DEMO_DATASET_IMPLEMENTATION_SUMMARY.md` - Implementation details

## 🎉 You're Done!

Your compliance forms are now ready for testing with realistic demo data.

```bash
# One command to generate all demo data
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1

# One command to test all forms
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

That's it! 🚀
