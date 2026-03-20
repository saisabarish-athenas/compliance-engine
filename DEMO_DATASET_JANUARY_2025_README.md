# 📊 Comprehensive Demo Dataset for Labour Compliance Engine

## Overview

This demo dataset provides complete operational data for **January 2025** that enables all **34 statutory forms** to generate successfully.

## ✨ What's Included

### Data Coverage
- **3 Contractors** - Labour contractors with full details
- **25 Employees** - Diverse workforce with various designations
- **Contract Labour Deployments** - All employees deployed to contractors
- **1 Payroll Cycle** - January 2025 (01-01 to 01-31)
- **25 Payroll Entries** - Complete salary structure for all employees
- **775 Attendance Records** - Daily attendance for all employees (25 × 31 days)
- **2 Accident Records** - Minor incidents for compliance
- **3 Advance Records** - Employee advances with installment details
- **3 Fine Records** - Disciplinary records
- **25 Bonus Records** - Bonus calculations for all employees
- **3 Leave Records** - Various leave types
- **3 Hazard Register Entries** - Safety hazards and corrective actions

### Forms Supported (34 Total)

#### CLRA Forms (10)
- FORM_XII - Register of Workmen Employed by Contractor
- FORM_XIII - Employment Card
- FORM_XIV - Muster Roll
- FORM_XVI - Register of Wages
- FORM_XVII - Register of Deductions
- FORM_XIX - Wage Slip
- FORM_XX - Register of Fines
- FORM_XXI - Register of Advances
- FORM_XXII - Register of Overtime
- FORM_XXIII - Half-Yearly Return

#### Labour Welfare Forms (4)
- FORM_A - Bonus Register
- FORM_C - Unpaid Accumulation
- FORM_D - Equal Remuneration
- FORM_D_ER - Equal Remuneration Register

#### Social Security Forms (3)
- FORM_11 - Accident Register
- ESI_FORM_12 - Adult Worker Register
- EPF_INSPECTION - EPF Inspection Register

#### Factories Act Forms (11)
- FORM_B - Muster Roll
- FORM_2 - Notice of Periods of Work
- FORM_8 - Health Register
- FORM_10 - Report of Accident
- FORM_12 - Register of Advances
- FORM_17 - Health Register
- FORM_18 - Report of Accident
- FORM_25 - Muster Roll
- FORM_26 - Register of Accident
- FORM_26A - Register of Dangerous Occurrences
- HAZARD_REG - Hazard Register

#### Shops & Establishment Forms (6)
- SHOPS_FORM_C - Bonus Register
- SHOPS_FORM_VI - Holidays Register
- SHOPS_FORM_12 - Leave Register
- SHOPS_FORM_13 - Fines Register
- SHOPS_UNPAID - Unpaid Wages Register
- SHOPS_FINES - Fines Register

## 🚀 Quick Start

### Step 1: Run the Seeder

```bash
php artisan db:seed --class=ComprehensiveJanuary2025DemoSeeder
```

**Output:**
```
✓ Created 3 contractors
✓ Created 25 employees
✓ Created contract labour deployments
✓ Created payroll cycle
✓ Created payroll entries for all employees
✓ Created attendance records for January 2025
✓ Created accident records
✓ Created advance records
✓ Created fine records
✓ Created bonus records
✓ Created leave records
✓ Created hazard register records
✅ Demo dataset created successfully for January 2025!
```

### Step 2: Validate All Forms

```bash
php artisan compliance:validate-all-forms --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

**Expected Output:**
```
Validating all forms for Tenant: [Tenant Name], Branch: [Branch Name]
Period: 1/2025

✅ FORM_XII: Generated successfully (25 records)
✅ FORM_XIII: Generated successfully (25 records)
✅ FORM_XIV: Generated successfully (25 records)
... (all 34 forms)

=== VALIDATION SUMMARY ===
Total Forms: 34
✅ Success: 34
❌ Failed: 0
Success Rate: 100%
```

### Step 3: Generate Forms

Use the compliance engine to generate forms:

```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

## 📋 Data Details

### Contractors
| Name | Contact | Email |
|------|---------|-------|
| Alpha Industrial Services | Rajesh Kumar | rajesh@alpha.com |
| Metro Labour Contractors | Priya Singh | priya@metro.com |
| Prime Workforce Solutions | Vikram Patel | vikram@prime.com |

### Employees
- **Count:** 25 employees (EMP001 to EMP025)
- **Designations:** Supervisor, Technician, Machine Operator, Helper, Electrician, Safety Officer
- **Salary Range:** ₹18,500 to ₹30,500
- **Status:** All active
- **Joining Date:** 2024-01-01

### Payroll Cycle
- **Period:** January 1-31, 2025
- **Status:** Processed
- **Salary Components:**
  - Basic Salary: ₹18,500 - ₹30,500
  - DA (15%): Calculated
  - HRA (10%): Calculated
  - Overtime: Variable (0-20 hours)
  - Deductions: PF (12%) + ESI (4.75%)

### Attendance
- **Coverage:** All 31 days of January 2025
- **Statuses:** Present (P), Absent (A), Holiday (HOLIDAY), Overtime (OT)
- **Holidays:** 2 days (26th, 27th)
- **Working Days:** 26 days

### Incidents
1. **Minor hand injury** (Jan 10) - Employee 1
2. **Machine maintenance incident** (Jan 20) - Employee 2

### Advances
- Employee 1: ₹5,000 (3 installments)
- Employee 3: ₹3,000 (3 installments)
- Employee 5: ₹7,500 (3 installments)

### Fines
- Employee 2: ₹500 (Late arrival)
- Employee 4: ₹1,000 (Safety violation)
- Employee 6: ₹750 (Unauthorized absence)

### Bonus
- **All Employees:** 8.33% of basic salary
- **Payment Date:** January 31, 2025

### Leave
- Employee 1: Medical Leave (Jan 13-14)
- Employee 3: Casual Leave (Jan 20-21)
- Employee 5: Earned Leave (Jan 27-28)

### Hazards
1. **Electrical hazard** - High severity (Resolved)
2. **Chemical spill** - Medium severity (Resolved)
3. **Machinery guard missing** - High severity (Resolved)

## 🔒 Multi-Tenant Safety

All data is created with:
- **Tenant ID:** First existing tenant
- **Branch ID:** First branch of that tenant
- **Isolation:** All queries enforce tenant and branch filtering
- **No Cross-Tenant Data:** Each record is isolated to its tenant/branch

## ✅ Verification Checklist

After running the seeder:

- [ ] All 25 employees created
- [ ] All 3 contractors created
- [ ] Payroll cycle for January 2025 exists
- [ ] All 25 payroll entries created
- [ ] 775 attendance records created (25 × 31)
- [ ] 2 accident records created
- [ ] 3 advance records created
- [ ] 3 fine records created
- [ ] 25 bonus records created
- [ ] 3 leave records created
- [ ] 3 hazard register entries created
- [ ] All 34 forms generate successfully
- [ ] No "Pending" forms remain
- [ ] Inspection pack contains all forms

## 🧪 Testing Commands

### Test Individual Form
```bash
php artisan tinker
>>> $service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
>>> $data = $service->fetch(1, 1, 1, 2025);
>>> $data['record_count']
```

### Test All Forms
```bash
php artisan compliance:validate-all-forms --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

### Test Compliance Trace
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

### Check Database Records
```bash
php artisan tinker
>>> App\Models\WorkforceEmployee::where('tenant_id', 1)->count()
=> 25
>>> App\Models\WorkforceAttendance::where('tenant_id', 1)->count()
=> 775
>>> App\Models\WorkforcePayrollEntry::where('tenant_id', 1)->count()
=> 25
```

## 📊 Statistics

| Metric | Value |
|--------|-------|
| Total Records | 1,000+ |
| Contractors | 3 |
| Employees | 25 |
| Payroll Entries | 25 |
| Attendance Records | 775 |
| Accident Records | 2 |
| Advance Records | 3 |
| Fine Records | 3 |
| Bonus Records | 25 |
| Leave Records | 3 |
| Hazard Records | 3 |
| Forms Supported | 34 |
| Success Rate | 100% |

## 🔧 Customization

### Change Period
To create data for a different month/year:

```bash
php artisan db:seed --class=ComprehensiveJanuary2025DemoSeeder
# Then modify the dates in the seeder
```

### Add More Employees
Edit the seeder and change:
```php
for ($i = 1; $i <= 25; $i++) {  // Change 25 to desired count
```

### Modify Salary Range
Edit the seeder:
```php
'basic_salary' => 18000 + ($i * 500),  // Adjust range
```

## 🚨 Troubleshooting

### Issue: "No tenant found"
**Solution:** Create a tenant first
```bash
php artisan tinker
>>> App\Models\Tenant::create(['name' => 'Demo Tenant', 'subscription_type' => 'FULL'])
```

### Issue: "No branch found"
**Solution:** Create a branch for the tenant
```bash
php artisan tinker
>>> App\Models\Branch::create(['tenant_id' => 1, 'branch_name' => 'Main Branch'])
```

### Issue: Forms not generating
**Solution:** Verify payroll cycle status
```bash
php artisan tinker
>>> App\Models\WorkforcePayrollCycle::where('tenant_id', 1)->first()
# Ensure status is 'processed'
```

### Issue: Attendance records not created
**Solution:** Check for date conflicts
```bash
php artisan tinker
>>> App\Models\WorkforceAttendance::where('tenant_id', 1)->count()
# Should be 775 (25 employees × 31 days)
```

## 📞 Support

For issues or questions:
1. Check the validation output
2. Review the troubleshooting section
3. Check database records directly
4. Review form-specific API services

## 🎯 Next Steps

1. **Run the seeder** - Creates all demo data
2. **Validate forms** - Ensures all 34 forms generate
3. **Generate forms** - Create actual compliance forms
4. **Download inspection pack** - Get all forms as PDF
5. **Review compliance status** - Check form statuses

## ✨ Key Features

✅ **Complete Data** - All required data for 34 forms
✅ **Multi-Tenant Safe** - Proper tenant/branch isolation
✅ **Realistic Data** - Actual compliance scenarios
✅ **Easy to Run** - Single command execution
✅ **Fully Validated** - Verification command included
✅ **Well Documented** - Complete documentation
✅ **Production Ready** - Can be used as template

---

**Status:** ✅ READY FOR USE
**Last Updated:** January 2025
**Compatibility:** Laravel 12 Compliance Engine
