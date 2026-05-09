# Comprehensive Demo Data Seeder - Labour Compliance System

## Overview

The `ComprehensiveDemoDataSeeder` creates a complete, realistic demo dataset for the Labour Compliance System that supports generation of all 36 statutory forms without any empty tables or missing references.

## Demo Tenant & Company Information

**Company Name:** Demo Compliance Industries Pvt Ltd  
**Industry:** Manufacturing (Solar Panel Manufacturing)  
**Location:** Sriperumbudur Industrial Area  
**Address:** No.53 Nungambakkam High Road, Chennai – 600034  
**Subscription Type:** FULL  

## Data Structure

### 1. Tenant & Branch
- **Tenant ID:** Auto-generated (typically 2 or higher)
- **Branch ID:** Auto-generated
- **Branch Name:** Solar Panel Manufacturing Unit
- **Factory License:** TN/FAC/2025/001

### 2. Employees (25 Total)

**Distribution:**
- 5 Supervisors
- 5 Technicians
- 5 Machine Operators
- 5 Helpers
- 3 Electricians
- 2 Safety Officers

**Employee Data Includes:**
- Employee Code: EMP001 - EMP025
- Full Name (mix of male and female)
- PF Number: PF/TN/2025/EMP###
- ESI Number: ESI/TN/2025/EMP###
- Date of Joining: June 2024 - December 2024
- Department: Production, Maintenance, Quality, Packaging, Safety
- Designation: As per distribution above
- Basic Salary: ₹18,000 - ₹35,000 (based on designation)
- Status: Active

**Salary Structure by Designation:**
- Supervisor: ₹35,000
- Electrician: ₹28,000
- Safety Officer: ₹26,000
- Technician: ₹25,000
- Machine Operator: ₹20,000
- Helper: ₹18,000

### 3. Payroll Cycles (3 Total)

**Periods:**
1. January 2025 (01-01-2025 to 31-01-2025)
2. February 2025 (01-02-2025 to 28-02-2025)
3. March 2025 (01-03-2025 to 31-03-2025)

**Status:** All cycles marked as "processed"

### 4. Payroll Entries (75 Total)

**Per Employee Per Cycle:**
- Total Days Worked: 22-26 days
- Paid Leave Days: 0-2 days
- Unpaid Leave Days: 0-1 days
- Overtime Hours: 0-6 hours (in 2-hour increments)

**Salary Components:**
- Basic Earned: Calculated based on days worked
- DA (Dearness Allowance): 15% of basic earned
- HRA (House Rent Allowance): 10% of basic earned
- Other Allowances: ₹500-₹2,000 (random)
- Overtime Wages: Calculated at 2x hourly rate

**Deductions:**
- PF (Employee): 12% of basic earned
- ESI (Employee): 1.75% of basic earned
- Professional Tax: ₹200 (if basic > ₹15,000)
- Fines: ₹0-₹500 (15% probability)
- Advances: ₹0-₹5,000 (15% probability)

**Payment Details:**
- Payment Date: 5 days after cycle end
- Payment Mode: Bank Transfer
- Transaction Reference: TXN/EMP###/YYYYMM

### 5. Bonus Records (25 Total)

**For Each Employee:**
- Financial Year: 2024-2025
- Bonus Percentage: 8.33% (statutory minimum)
- Bonus Amount: Calculated on annual salary
- Payment Date: 31-03-2025

### 6. Contractor & Contract Labour

**Contractor Master:**
- Company Name: GIRI Manpower Services
- Company Type: Manpower
- CLRA License: CLRA-TN-2025-001
- License Valid: 01-01-2025 to 31-12-2026
- Max Worker Limit: 50
- PF Code: PF/GIRI/2025
- ESI Code: ESI/GIRI/2025
- Contact Person: Mr. Rajesh Kumar
- Contact Number: 9876543210
- Email: contact@girimanpower.com
- PAN: AABCT1234A
- GST: 33AABCT1234A1Z0
- Status: Active

**Contractor Compliance:**
- Labour Registration: LR/TN/2025/001
- Last Return Filed: 15-01-2025
- Compliance Status: Compliant
- Compliance Notes: All documents verified and compliant

**Contract Labour Deployments (10 Total):**
- Deployed Employees: First 10 employees (EMP001-EMP010)
- Deployment Period: 01-01-2025 to 31-12-2025
- Work Order Numbers: WO/GIRI/2025/001 to WO/GIRI/2025/010
- Work Order Date: 15-12-2024
- Status: Active
- Wage Rate: Same as employee basic salary

### 7. Incident Records (3 Total)

**Accident Records (2):**

1. **Accident 1:**
   - Employee: EMP001
   - Date: Random date in Jan-Mar 2025
   - Location: Production Floor - Section A
   - Type: accident
   - Description: Minor cut injury while operating machinery
   - Reference: ACC/TN/2025/001

2. **Accident 2:**
   - Employee: EMP002
   - Date: Random date in Jan-Mar 2025
   - Location: Production Floor - Section B
   - Type: accident
   - Description: Slip and fall on wet floor
   - Reference: ACC/TN/2025/002

**Dangerous Occurrence (1):**
- Date: 15-02-2025
- Location: Maintenance Department
- Type: dangerous
- Description: Boiler pressure leak detected during routine inspection
- Reference: DNG/TN/2025/001
- Employee: None (facility-level incident)

## Database Tables Populated

| Table | Records | Purpose |
|-------|---------|---------|
| tenants | 1 | Company/Tenant master |
| branches | 1 | Manufacturing unit |
| workforce_employee | 25 | Employee master data |
| workforce_payroll_cycle | 3 | Payroll periods |
| workforce_payroll_entry | 75 | Monthly payroll for each employee |
| bonus_records | 25 | Annual bonus for each employee |
| contractor_master | 1 | Contractor company details |
| contractor_compliance | 1 | CLRA compliance details |
| contract_labour_deployment | 10 | Contract worker deployments |
| incident_documents | 3 | Accidents and dangerous occurrences |

## Forms Supported

This demo data supports generation of all 36 statutory forms:

### Factories Act Forms
- FORM_2: Notice of Periods of Work
- FORM_8: Accident Register
- FORM_10: Overtime Register
- FORM_12: Adult Worker Register
- FORM_17: Health Register
- FORM_18: Report of Accident
- FORM_25: Muster Roll
- FORM_26: Register of Accidents
- FORM_26A: Register of Dangerous Occurrences
- HAZARD_REG: Hazard Register

### CLRA Forms
- FORM_XII: Register of Workmen Employed by Contractor
- FORM_XIII: Employment Card (CLRA)
- FORM_XIV: Muster Roll (CLRA)
- FORM_XVI: Register of Wages (Contract Labour)
- FORM_XVII: Register of Deductions (CLRA)
- FORM_XIX: Wage Slip (CLRA)
- FORM_XX: Register of Fines (CLRA)
- FORM_XXI: Register of Advances (CLRA)
- FORM_XXII: Register of Overtime (CLRA)
- FORM_XXIII: Half-Yearly Return (Contractor)

### Shops & Establishment Forms
- SHOPS_FORM_12: Register of Fines
- SHOPS_FORM_13: Register of Advances
- SHOPS_FORM_VI: Holidays Register
- SHOPS_FORM_C: Bonus Register
- SHOPS_FINES: Fines Register
- SHOPS_UNPAID: Unpaid Accumulation

### Other Registers
- FORM_A: Register of Advances
- FORM_B: Wage Register
- FORM_C: Bonus Register
- FORM_D: Equal Remuneration Register
- FORM_D_ER: Equal Remuneration (Detailed)
- FORM_11: Accident Register
- ESI_FORM_12: ESI Accident Report
- EPF_INSPECTION: EPF Inspection Register

## How to Run the Seeder

### Option 1: Run All Seeders
```bash
php artisan db:seed
```

### Option 2: Run Only This Seeder
```bash
php artisan db:seed --class=ComprehensiveDemoDataSeeder
```

### Option 3: Fresh Database with Seeding
```bash
php artisan migrate:fresh --seed
```

## Data Integrity Checks

The seeder ensures:

✓ All employee_id references exist in workforce_employee table  
✓ All payroll_cycle_id references exist in workforce_payroll_cycle table  
✓ All contractor_id references exist in contractor_master table  
✓ All contractor_compliance_id references exist in contractor_compliance table  
✓ All tenant_id references are consistent  
✓ All branch_id references are valid  
✓ No foreign key violations  
✓ All dates are realistic and within valid ranges  
✓ All salary calculations are mathematically correct  
✓ All deductions are properly calculated  

## Customization

To modify the demo data, edit the seeder file:

```php
// Change number of employees
for ($i = 1; $i <= 25; $i++) {  // Change 25 to desired count
    // ...
}

// Change salary ranges
'basic_salary' => match ($designation) {
    'Supervisor' => 35000,  // Modify as needed
    // ...
}

// Change payroll periods
$months = [
    ['name' => 'January 2025', 'from' => '2025-01-01', 'to' => '2025-01-31'],
    // Add or modify months
];
```

## Verification

After running the seeder, verify the data:

```bash
# Check employee count
SELECT COUNT(*) FROM workforce_employee WHERE tenant_id = 2;

# Check payroll entries
SELECT COUNT(*) FROM workforce_payroll_entry WHERE tenant_id = 2;

# Check bonus records
SELECT COUNT(*) FROM bonus_records WHERE tenant_id = 2;

# Check incidents
SELECT COUNT(*) FROM incident_documents WHERE tenant_id = 2;
```

## Notes

- The seeder uses realistic Indian names and addresses
- All salary calculations follow Indian statutory requirements
- PF and ESI calculations are based on current rates
- Dates are set for 2025 to ensure current relevance
- The demo data is isolated to a separate tenant (FULL subscription)
- No existing data is modified or deleted
- All records are created with proper timestamps

## Support

For issues or questions about the demo data:
1. Check the seeder output for any error messages
2. Verify database migrations have run successfully
3. Ensure the tenant has FULL subscription type
4. Check foreign key constraints are properly defined
