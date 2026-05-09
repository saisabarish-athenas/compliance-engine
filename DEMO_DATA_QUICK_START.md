# Quick Start Guide - Demo Data Seeder

## What Gets Created

✓ 1 Demo Tenant (Demo Compliance Industries Pvt Ltd)  
✓ 1 Branch (Solar Panel Manufacturing Unit)  
✓ 25 Employees with realistic data  
✓ 3 Payroll Cycles (Jan, Feb, Mar 2025)  
✓ 75 Payroll Entries (25 employees × 3 months)  
✓ 25 Bonus Records  
✓ 1 Contractor (GIRI Manpower Services)  
✓ 10 Contract Labour Deployments  
✓ 3 Incident Records (2 accidents + 1 dangerous occurrence)  

**Total: 143 records across 10 tables**

## Prerequisites

- Laravel application is set up
- Database migrations have been run: `php artisan migrate`
- Database is accessible

## Running the Seeder

### Method 1: Run Only Demo Data Seeder (Recommended)
```bash
php artisan db:seed --class=ComprehensiveDemoDataSeeder
```

### Method 2: Run All Seeders
```bash
php artisan db:seed
```

### Method 3: Fresh Database with Demo Data
```bash
php artisan migrate:fresh --seed
```

## Expected Output

```
✓ Created Tenant: 2
✓ Created Branch: 1
✓ Created 3 Payroll Cycles
✓ Created 25 Employees
✓ Created 75 Payroll Entries
✓ Created 25 Bonus Records
✓ Created Contractor: 1 with Compliance ID: 1
✓ Created 10 Contract Labour Deployments
✓ Created 3 Incident Records (2 Accidents + 1 Dangerous Occurrence)

═══════════════════════════════════════════════════════════════
  COMPREHENSIVE DEMO DATA SEEDING COMPLETE
═══════════════════════════════════════════════════════════════

TENANT INFORMATION:
  Company: Demo Compliance Industries Pvt Ltd
  Tenant ID: 2
  Branch: Solar Panel Manufacturing Unit
  Branch ID: 1
  Location: Sriperumbudur Industrial Area

RECORDS CREATED:
  Employees: 25
  Payroll Cycles: 3
  Payroll Entries: 75
  Bonus Records: 25
  Contractors: 1
  Contract Labour Deployments: 10
  Incident Records: 3

PAYROLL PERIODS:
  • January 2025
  • February 2025
  • March 2025

✓ All forms can now be generated with realistic data
═══════════════════════════════════════════════════════════════
```

## Verify Data Was Created

### Check Tenant
```bash
php artisan tinker
>>> DB::table('tenants')->where('name', 'Demo Compliance Industries Pvt Ltd')->first();
```

### Check Employees
```bash
>>> DB::table('workforce_employee')->where('tenant_id', 2)->count();
// Should return: 25
```

### Check Payroll
```bash
>>> DB::table('workforce_payroll_entry')->where('tenant_id', 2)->count();
// Should return: 75
```

### Check Incidents
```bash
>>> DB::table('incident_documents')->where('tenant_id', 2)->count();
// Should return: 3
```

## Generate Forms

Once demo data is seeded, you can generate all 36 statutory forms:

```bash
# Example: Generate FORM_B (Wage Register)
php artisan compliance:generate-form FORM_B --tenant=2 --branch=1 --month=1 --year=2025

# Example: Generate FORM_25 (Muster Roll)
php artisan compliance:generate-form FORM_25 --tenant=2 --branch=1 --month=1 --year=2025
```

## Data Details

### Employee Information
- **Employee Codes:** EMP001 to EMP025
- **Departments:** Production, Maintenance, Quality, Packaging, Safety
- **Designations:** Supervisor, Technician, Machine Operator, Helper, Electrician, Safety Officer
- **Salary Range:** ₹18,000 to ₹35,000
- **Joining Period:** June 2024 to December 2024

### Payroll Information
- **Cycles:** January 2025, February 2025, March 2025
- **Working Days:** 22-26 days per month
- **Overtime:** 0-6 hours per month
- **Deductions:** PF (12%), ESI (1.75%), Professional Tax, Fines, Advances

### Contractor Information
- **Company:** GIRI Manpower Services
- **CLRA License:** CLRA-TN-2025-001
- **Deployed Workers:** 10 (EMP001-EMP010)
- **Deployment Period:** Full year 2025

### Incident Information
- **Accidents:** 2 records
- **Dangerous Occurrences:** 1 record
- **Period:** January-March 2025

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1030"
**Solution:** Ensure database migrations have been run:
```bash
php artisan migrate
```

### Error: "Class ComprehensiveDemoDataSeeder not found"
**Solution:** Clear the autoloader cache:
```bash
composer dump-autoload
```

### Error: "Foreign key constraint fails"
**Solution:** Ensure all parent tables exist:
```bash
php artisan migrate
```

### No output or seeder doesn't run
**Solution:** Check if seeder is registered in DatabaseSeeder.php:
```php
public function run(): void
{
    $this->call([
        ComprehensiveDemoDataSeeder::class,
    ]);
}
```

## Resetting Demo Data

To clear and reseed the demo data:

```bash
# Option 1: Delete only demo tenant data
php artisan tinker
>>> DB::table('tenants')->where('name', 'Demo Compliance Industries Pvt Ltd')->delete();
>>> exit

# Then reseed
php artisan db:seed --class=ComprehensiveDemoDataSeeder

# Option 2: Fresh database
php artisan migrate:fresh --seed
```

## Next Steps

1. ✓ Run the seeder
2. ✓ Verify data was created
3. ✓ Generate forms using the compliance engine
4. ✓ Download and review generated PDFs
5. ✓ Test form generation for all 36 forms

## Support

For detailed information about the demo data structure, see: `DEMO_DATA_SEEDER_GUIDE.md`

For form generation details, see: `COMPLIANCE_ENGINE_GUIDE.md`
