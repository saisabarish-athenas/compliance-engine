# FORM GROUPING STRATEGY - 36 FORMS

## GROUP 1: PAYROLL-BASED FORMS (13 forms)
**Data Source:** workforce_payroll_entry + workforce_employee
**Generator:** PayrollBasedFormGenerator

1. FORM_B - Register of Wages (Factories Act)
2. FORM_10 - Overtime Register (Factories Act)
3. FORM_25 - Muster Roll (Factories Act)
4. FORM_XVI - Register of Wages (CLRA)
5. FORM_XVII - Register of Deductions (CLRA)
6. FORM_XIX - Muster Roll (CLRA)
7. FORM_XXIII - Register of Overtime (CLRA)
8. SHOPS_FORM_12 - Register of Wages (Shops Act)
9. SHOPS_FINES - Register of Fines (Shops Act)
10. FORM_XXI - Register of Fines (CLRA)
11. FORM_XX - Register of Advances (CLRA)
12. FORM_XXII - Register of Damage or Loss (CLRA)
13. SHOPS_UNPAID - Unpaid Wages Register (Shops Act)

## GROUP 2: CONTRACTOR-BASED FORMS (7 forms)
**Data Source:** contract_labour_deployment + contractor_master + workforce_employee
**Generator:** ContractorBasedFormGenerator

1. FORM_XIII - Register of Contract Labour (CLRA)
2. FORM_XIV - Register of Workmen (CLRA)
3. FORM_XII - Register of Contractors (CLRA)
4. CLRA_LICENSE - License Register (CLRA)
5. FORM_XXIV - Annual Return (CLRA)
6. FORM_XXV - Half-Yearly Return (CLRA)
7. SHOPS_FORM_1 - Register of Employment (Shops Act)

## GROUP 3: INCIDENT-BASED FORMS (6 forms)
**Data Source:** incident_documents + workforce_employee
**Generator:** IncidentBasedFormGenerator

1. FORM_8 - Register of Accidents (Factories Act)
2. FORM_11 - Notice of Dangerous Occurrences (Factories Act)
3. FORM_26 - Notice of Accident (Factories Act)
4. FORM_26A - Notice of Dangerous Occurrence (Factories Act)
5. ESI_FORM_12 - Accident Register (Social Security)
6. FORM_18 - Register of Child Workers (Factories Act)

## GROUP 4: INSPECTION-BASED FORMS (4 forms)
**Data Source:** inspection_documents
**Generator:** InspectionBasedFormGenerator

1. FORM_7 - Notice of Periods for Adult Workers (Factories Act)
2. HAZARD_REG - Hazardous Process Register (Factories Act)
3. EPF_INSPECTION - EPF Inspection Register (Social Security)
4. SHOPS_FORM_13 - Attendance Register (Shops Act)

## GROUP 5: MASTER-REGISTER FORMS (6 forms)
**Data Source:** workforce_employee + branches
**Generator:** MasterRegisterFormGenerator

1. FORM_12 - Register of Adult Workers (Factories Act)
2. FORM_17 - Register of Young Persons (Factories Act)
3. FORM_2 - Register of Leave (Factories Act)
4. SHOPS_FORM_C - Bonus Register (Shops Act)
5. SHOPS_FORM_VI - Leave Register (Shops Act)
6. CONTRACTOR_MASTER - Contractor Master Register

## TOTAL: 36 FORMS
- Payroll-based: 13
- Contractor-based: 7
- Incident-based: 6
- Inspection-based: 4
- Master-register: 6
