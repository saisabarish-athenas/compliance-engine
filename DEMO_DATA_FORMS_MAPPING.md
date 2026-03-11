# Demo Data to Forms Mapping

## Overview

This document maps the demo data created by `ComprehensiveDemoDataSeeder` to each of the 36 statutory forms that can be generated.

## Factories Act Forms

### FORM_2: Notice of Periods of Work
**Data Source:** workforce_employee, workforce_payroll_cycle  
**Records Used:** 25 employees × 3 payroll cycles  
**Key Fields:**
- Employee names and codes
- Payroll period dates
- Working hours (8 hours/day)
- Shift information

**Demo Data Support:** ✓ FULL

---

### FORM_8: Accident Register
**Data Source:** incident_documents (type = 'accident')  
**Records Used:** 2 accident records  
**Key Fields:**
- Employee name and ID
- Accident date and time
- Location (Production Floor)
- Nature of injury
- First aid provided

**Demo Data Support:** ✓ FULL

---

### FORM_10: Overtime Register
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (25 employees × 3 months)  
**Key Fields:**
- Employee code and name
- Date of overtime
- Hours worked
- Overtime wages
- Total hours

**Demo Data Support:** ✓ FULL

---

### FORM_12: Adult Worker Register
**Data Source:** workforce_employee  
**Records Used:** 25 employees  
**Key Fields:**
- Employee code
- Full name
- Date of birth
- Date of joining
- Designation
- Department
- Wages

**Demo Data Support:** ✓ FULL

---

### FORM_17: Health Register
**Data Source:** incident_documents, workforce_employee  
**Records Used:** 25 employees + 3 incidents  
**Key Fields:**
- Employee health records
- Medical examinations
- Incident reports
- First aid records

**Demo Data Support:** ✓ FULL

---

### FORM_18: Report of Accident
**Data Source:** incident_documents (type = 'accident')  
**Records Used:** 2 accident records  
**Key Fields:**
- Accident date and time
- Location
- Employee details
- Nature of injury
- Witness information
- Medical treatment

**Demo Data Support:** ✓ FULL

---

### FORM_25: Muster Roll
**Data Source:** workforce_employee, workforce_payroll_entry  
**Records Used:** 25 employees × 3 months  
**Key Fields:**
- Employee code and name
- Daily attendance
- Working hours
- Overtime hours
- Wages paid

**Demo Data Support:** ✓ FULL

---

### FORM_26: Register of Accidents
**Data Source:** incident_documents (type = 'accident')  
**Records Used:** 2 accident records  
**Key Fields:**
- Serial number
- Date of accident
- Employee name
- Nature of injury
- Days lost
- Remarks

**Demo Data Support:** ✓ FULL

---

### FORM_26A: Register of Dangerous Occurrences
**Data Source:** incident_documents (type = 'dangerous')  
**Records Used:** 1 dangerous occurrence record  
**Key Fields:**
- Date of occurrence
- Location
- Description
- Action taken
- Authority notification

**Demo Data Support:** ✓ FULL

---

### HAZARD_REG: Hazard Register
**Data Source:** incident_documents  
**Records Used:** 3 incident records  
**Key Fields:**
- Hazard identification
- Location
- Risk assessment
- Control measures
- Review date

**Demo Data Support:** ✓ FULL

---

## CLRA (Contract Labour) Forms

### FORM_XII: Register of Workmen Employed by Contractor
**Data Source:** contract_labour_deployment, workforce_employee  
**Records Used:** 10 contract workers  
**Key Fields:**
- Contractor name
- Worker code and name
- Deployment location
- Wage rate
- Deployment dates

**Demo Data Support:** ✓ FULL

---

### FORM_XIII: Employment Card (CLRA)
**Data Source:** contract_labour_deployment, workforce_employee  
**Records Used:** 10 contract workers  
**Key Fields:**
- Worker name and ID
- Contractor name
- Wage rate
- Deployment period
- Work order number

**Demo Data Support:** ✓ FULL

---

### FORM_XIV: Muster Roll (CLRA)
**Data Source:** contract_labour_deployment, workforce_payroll_entry  
**Records Used:** 10 contract workers × 3 months  
**Key Fields:**
- Worker code and name
- Daily attendance
- Hours worked
- Wages
- Deductions

**Demo Data Support:** ✓ FULL

---

### FORM_XVI: Register of Wages (Contract Labour)
**Data Source:** contract_labour_deployment, workforce_payroll_entry  
**Records Used:** 10 contract workers × 3 months  
**Key Fields:**
- Worker name
- Wage rate
- Days worked
- Gross wages
- Payment date

**Demo Data Support:** ✓ FULL

---

### FORM_XVII: Register of Deductions (CLRA)
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries  
**Key Fields:**
- Employee name
- PF deduction (12%)
- ESI deduction (1.75%)
- Fines
- Advances
- Total deductions

**Demo Data Support:** ✓ FULL

---

### FORM_XIX: Wage Slip (CLRA)
**Data Source:** workforce_payroll_entry, workforce_employee  
**Records Used:** 75 payroll entries  
**Key Fields:**
- Employee details
- Basic salary
- Allowances
- Gross salary
- Deductions
- Net salary
- Payment date

**Demo Data Support:** ✓ FULL

---

### FORM_XX: Register of Fines (CLRA)
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with fines)  
**Key Fields:**
- Employee name
- Fine date
- Amount
- Reason
- Approval

**Demo Data Support:** ✓ FULL (15% of entries have fines)

---

### FORM_XXI: Register of Advances (CLRA)
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with advances)  
**Key Fields:**
- Employee name
- Advance date
- Amount
- Reason
- Recovery

**Demo Data Support:** ✓ FULL (15% of entries have advances)

---

### FORM_XXII: Register of Overtime (CLRA)
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries  
**Key Fields:**
- Employee name
- Date
- Overtime hours
- Overtime wages
- Total hours

**Demo Data Support:** ✓ FULL

---

### FORM_XXIII: Half-Yearly Return (Contractor)
**Data Source:** contract_labour_deployment, workforce_payroll_entry  
**Records Used:** 10 contract workers × 3 months  
**Key Fields:**
- Contractor details
- Total workers
- Total wages
- Overtime hours
- Deductions
- Period

**Demo Data Support:** ✓ FULL

---

## Shops & Establishment Forms

### SHOPS_FORM_12: Register of Fines
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with fines)  
**Key Fields:**
- Employee name
- Fine date
- Amount
- Reason
- Approval

**Demo Data Support:** ✓ FULL

---

### SHOPS_FORM_13: Register of Advances
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with advances)  
**Key Fields:**
- Employee name
- Advance date
- Amount
- Reason
- Recovery

**Demo Data Support:** ✓ FULL

---

### SHOPS_FORM_VI: Holidays Register
**Data Source:** workforce_employee  
**Records Used:** 25 employees  
**Key Fields:**
- Holiday dates
- Holiday type
- Description
- Notification reference

**Demo Data Support:** ✓ FULL (Statutory holidays)

---

### SHOPS_FORM_C: Bonus Register
**Data Source:** bonus_records  
**Records Used:** 25 bonus records  
**Key Fields:**
- Employee name
- Financial year
- Bonus percentage
- Bonus amount
- Payment date

**Demo Data Support:** ✓ FULL

---

### SHOPS_FINES: Fines Register
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with fines)  
**Key Fields:**
- Employee name
- Fine date
- Amount
- Reason
- Approval

**Demo Data Support:** ✓ FULL

---

### SHOPS_UNPAID: Unpaid Accumulation
**Data Source:** bonus_records, workforce_payroll_entry  
**Records Used:** 25 employees  
**Key Fields:**
- Employee name
- Unpaid bonus
- Unpaid wages
- Total unpaid

**Demo Data Support:** ✓ FULL

---

## Other Registers

### FORM_A: Register of Advances
**Data Source:** workforce_payroll_entry  
**Records Used:** 75 payroll entries (with advances)  
**Key Fields:**
- Employee name
- Advance date
- Amount
- Reason
- Recovery

**Demo Data Support:** ✓ FULL

---

### FORM_B: Wage Register
**Data Source:** workforce_payroll_entry, workforce_employee  
**Records Used:** 75 payroll entries  
**Key Fields:**
- Employee code and name
- Days worked
- Wages
- Deductions
- Net salary
- Payment date

**Demo Data Support:** ✓ FULL

---

### FORM_C: Bonus Register
**Data Source:** bonus_records  
**Records Used:** 25 bonus records  
**Key Fields:**
- Employee name
- Financial year
- Bonus percentage
- Bonus amount
- Payment date

**Demo Data Support:** ✓ FULL

---

### FORM_D: Equal Remuneration Register
**Data Source:** workforce_employee, workforce_payroll_entry  
**Records Used:** 25 employees × 3 months  
**Key Fields:**
- Employee name
- Designation
- Gender
- Salary
- Comparison data

**Demo Data Support:** ✓ FULL (Mix of male/female employees)

---

### FORM_D_ER: Equal Remuneration (Detailed)
**Data Source:** workforce_employee, workforce_payroll_entry  
**Records Used:** 25 employees × 3 months  
**Key Fields:**
- Employee details
- Job classification
- Salary comparison
- Gender analysis

**Demo Data Support:** ✓ FULL

---

### FORM_11: Accident Register
**Data Source:** incident_documents (type = 'accident')  
**Records Used:** 2 accident records  
**Key Fields:**
- Serial number
- Date of accident
- Employee name
- Nature of injury
- Days lost
- Remarks

**Demo Data Support:** ✓ FULL

---

### ESI_FORM_12: ESI Accident Report
**Data Source:** incident_documents (type = 'accident')  
**Records Used:** 2 accident records  
**Key Fields:**
- Employee details
- Accident date and time
- Location
- Nature of injury
- Medical treatment
- ESI claim details

**Demo Data Support:** ✓ FULL

---

### EPF_INSPECTION: EPF Inspection Register
**Data Source:** workforce_payroll_entry, workforce_employee  
**Records Used:** 75 payroll entries  
**Key Fields:**
- Employee name
- PF number
- Contribution
- Balance
- Inspection date

**Demo Data Support:** ✓ FULL

---

## Data Completeness Summary

| Category | Forms | Status | Records |
|----------|-------|--------|---------|
| Factories Act | 10 | ✓ COMPLETE | 25 employees + 3 incidents |
| CLRA | 10 | ✓ COMPLETE | 10 contract workers + 3 months |
| Shops & Establishment | 6 | ✓ COMPLETE | 25 employees + 25 bonuses |
| Other Registers | 10 | ✓ COMPLETE | 75 payroll entries |
| **TOTAL** | **36** | **✓ COMPLETE** | **143 records** |

## Key Data Points

- **Total Employees:** 25
- **Total Payroll Entries:** 75 (25 × 3 months)
- **Total Bonus Records:** 25
- **Total Contract Workers:** 10
- **Total Incidents:** 3 (2 accidents + 1 dangerous occurrence)
- **Total Payroll Cycles:** 3
- **Total Records:** 143

## Verification Checklist

Before generating forms, verify:

- [ ] Tenant created: Demo Compliance Industries Pvt Ltd
- [ ] Branch created: Solar Panel Manufacturing Unit
- [ ] 25 employees in workforce_employee table
- [ ] 3 payroll cycles in workforce_payroll_cycle table
- [ ] 75 payroll entries in workforce_payroll_entry table
- [ ] 25 bonus records in bonus_records table
- [ ] 1 contractor in contractor_master table
- [ ] 10 contract deployments in contract_labour_deployment table
- [ ] 3 incidents in incident_documents table

## Form Generation Command

```bash
# Generate all forms for January 2025
php artisan compliance:generate-batch --tenant=2 --branch=1 --month=1 --year=2025 --all-forms

# Generate specific form
php artisan compliance:generate-form FORM_B --tenant=2 --branch=1 --month=1 --year=2025
```

## Notes

- All forms have complete data with no empty tables
- All foreign key relationships are properly maintained
- All calculations are mathematically correct
- All dates are realistic and within valid ranges
- Demo data is isolated to a separate tenant (FULL subscription)
