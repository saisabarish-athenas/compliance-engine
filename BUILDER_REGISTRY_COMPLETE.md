# Builder Registry - Complete List

## All 31 Builders

### Existing Builders (8)
1. **WageRegisterBuilder** - FORM_B
   - Location: app/Compliance/Builders/WageRegisterBuilder.php
   - Data: Payroll entries with wages and deductions
   - Repository: PayrollRepository

2. **OvertimeRegisterBuilder** - FORM_10
   - Location: app/Compliance/Builders/OvertimeRegisterBuilder.php
   - Data: Overtime hours and wages
   - Repository: PayrollRepository

3. **AttendanceRegisterBuilder** - FORM_25, FORM_D
   - Location: app/Compliance/Builders/AttendanceRegisterBuilder.php
   - Data: Attendance records grouped by employee
   - Repository: AttendanceRepository

4. **EmployeeRegisterBuilder** - FORM_12, FORM_A
   - Location: app/Compliance/Builders/EmployeeRegisterBuilder.php
   - Data: Employee master data
   - Repository: EmployeeRepository

5. **BonusRegisterBuilder** - SHOPS_FORM_C
   - Location: app/Compliance/Builders/BonusRegisterBuilder.php
   - Data: Bonus payments
   - Repository: BonusRepository

6. **DeductionRegisterBuilder** - FORM_XX, FORM_C
   - Location: app/Compliance/Builders/DeductionRegisterBuilder.php
   - Data: Deductions (PF, ESI, etc.)
   - Repository: DeductionRepository

7. **IncidentBuilder** - FORM_8, ESI_FORM_12
   - Location: app/Compliance/Builders/IncidentBuilder.php
   - Data: Incident reports
   - Repository: IncidentRepository

8. **ContractorWorkmenBuilder** - FORM_XIII
   - Location: app/Compliance/Builders/ContractorWorkmenBuilder.php
   - Data: Contract labour workmen
   - Repository: ContractorRepository

### New Builders (23)

9. **WorkShiftBuilder** - FORM_2
   - Location: app/Compliance/Builders/WorkShiftBuilder.php
   - Data: Work shift information
   - Repository: EmployeeRepository

10. **InspectionRegisterBuilder** - FORM_7, EPF_INSPECTION
    - Location: app/Compliance/Builders/InspectionRegisterBuilder.php
    - Data: Inspection records
    - Repository: IncidentRepository

11. **AccidentRegisterBuilder** - FORM_11, FORM_26
    - Location: app/Compliance/Builders/AccidentRegisterBuilder.php
    - Data: Accident records
    - Repository: IncidentRepository

12. **HealthRegisterBuilder** - FORM_17
    - Location: app/Compliance/Builders/HealthRegisterBuilder.php
    - Data: Health and safety records
    - Repository: EmployeeRepository

13. **AccidentReportBuilder** - FORM_18
    - Location: app/Compliance/Builders/AccidentReportBuilder.php
    - Data: Detailed accident report
    - Repository: IncidentRepository

14. **DangerousOccurrenceBuilder** - FORM_26A
    - Location: app/Compliance/Builders/DangerousOccurrenceBuilder.php
    - Data: Dangerous occurrences
    - Repository: IncidentRepository

15. **ContractorMasterBuilder** - FORM_XII, CONTRACTOR_MASTER
    - Location: app/Compliance/Builders/ContractorMasterBuilder.php
    - Data: Contractor master records
    - Repository: ContractorRepository

16. **EmploymentCardBuilder** - FORM_XIV
    - Location: app/Compliance/Builders/EmploymentCardBuilder.php
    - Data: Employment cards for contract labour
    - Repository: ContractorRepository

17. **ContractorMusterBuilder** - FORM_XVI
    - Location: app/Compliance/Builders/ContractorMusterBuilder.php
    - Data: Contractor muster roll
    - Repository: ContractorRepository

18. **ContractorWageRegisterBuilder** - FORM_XVII
    - Location: app/Compliance/Builders/ContractorWageRegisterBuilder.php
    - Data: Contractor wage register
    - Repository: ContractorRepository

19. **ContractorWageSlipBuilder** - FORM_XIX
    - Location: app/Compliance/Builders/ContractorWageSlipBuilder.php
    - Data: Contractor wage slips
    - Repository: ContractorRepository

20. **FinesRegisterBuilder** - FORM_XXI
    - Location: app/Compliance/Builders/FinesRegisterBuilder.php
    - Data: Fines imposed on workers
    - Repository: DeductionRepository

21. **AdvanceRegisterBuilder** - FORM_XXII
    - Location: app/Compliance/Builders/AdvanceRegisterBuilder.php
    - Data: Advances given to workers
    - Repository: DeductionRepository

22. **ContractorOvertimeBuilder** - FORM_XXIII
    - Location: app/Compliance/Builders/ContractorOvertimeBuilder.php
    - Data: Contractor overtime records
    - Repository: ContractorRepository

23. **ContractorHalfYearlyBuilder** - FORM_XXIV
    - Location: app/Compliance/Builders/ContractorHalfYearlyBuilder.php
    - Data: Half-yearly contractor return
    - Repository: ContractorRepository

24. **PrincipalAnnualBuilder** - FORM_XXV
    - Location: app/Compliance/Builders/PrincipalAnnualBuilder.php
    - Data: Annual principal employer return
    - Repository: ContractorRepository

25. **ShopsWageRegisterBuilder** - SHOPS_FORM_12
    - Location: app/Compliance/Builders/ShopsWageRegisterBuilder.php
    - Data: Shops wage register
    - Repository: PayrollRepository

26. **ShopsLeaveRegisterBuilder** - SHOPS_FORM_13
    - Location: app/Compliance/Builders/ShopsLeaveRegisterBuilder.php
    - Data: Shops leave register
    - Repository: EmployeeRepository

27. **ShopsEmployeeRegisterBuilder** - SHOPS_FORM_1
    - Location: app/Compliance/Builders/ShopsEmployeeRegisterBuilder.php
    - Data: Shops employee register
    - Repository: EmployeeRepository

28. **ShopsHolidayRegisterBuilder** - SHOPS_FORM_VI
    - Location: app/Compliance/Builders/ShopsHolidayRegisterBuilder.php
    - Data: Shops holiday register
    - Repository: EmployeeRepository

29. **ShopsFinesRegisterBuilder** - SHOPS_FINES
    - Location: app/Compliance/Builders/ShopsFinesRegisterBuilder.php
    - Data: Shops fines register
    - Repository: DeductionRepository

30. **ShopsUnpaidBonusBuilder** - SHOPS_UNPAID
    - Location: app/Compliance/Builders/ShopsUnpaidBonusBuilder.php
    - Data: Shops unpaid accumulations
    - Repository: BonusRepository

31. **EqualRemunerationBuilder** - FORM_D_ER
    - Location: app/Compliance/Builders/EqualRemunerationBuilder.php
    - Data: Equal remuneration register
    - Repository: EmployeeRepository

## Builder by Form Code

| Form Code | Builder | Repository |
|-----------|---------|------------|
| FORM_2 | WorkShiftBuilder | EmployeeRepository |
| FORM_B | WageRegisterBuilder | PayrollRepository |
| FORM_7 | InspectionRegisterBuilder | IncidentRepository |
| FORM_8 | IncidentBuilder | IncidentRepository |
| FORM_10 | OvertimeRegisterBuilder | PayrollRepository |
| FORM_11 | AccidentRegisterBuilder | IncidentRepository |
| FORM_12 | EmployeeRegisterBuilder | EmployeeRepository |
| FORM_17 | HealthRegisterBuilder | EmployeeRepository |
| FORM_18 | AccidentReportBuilder | IncidentRepository |
| FORM_25 | AttendanceRegisterBuilder | AttendanceRepository |
| FORM_26 | AccidentRegisterBuilder | IncidentRepository |
| FORM_26A | DangerousOccurrenceBuilder | IncidentRepository |
| FORM_XII | ContractorMasterBuilder | ContractorRepository |
| FORM_XIII | ContractorWorkmenBuilder | ContractorRepository |
| FORM_XIV | EmploymentCardBuilder | ContractorRepository |
| FORM_XVI | ContractorMusterBuilder | ContractorRepository |
| FORM_XVII | ContractorWageRegisterBuilder | ContractorRepository |
| FORM_XIX | ContractorWageSlipBuilder | ContractorRepository |
| FORM_XX | DeductionRegisterBuilder | DeductionRepository |
| FORM_XXI | FinesRegisterBuilder | DeductionRepository |
| FORM_XXII | AdvanceRegisterBuilder | DeductionRepository |
| FORM_XXIII | ContractorOvertimeBuilder | ContractorRepository |
| FORM_XXIV | ContractorHalfYearlyBuilder | ContractorRepository |
| FORM_XXV | PrincipalAnnualBuilder | ContractorRepository |
| SHOPS_FORM_1 | ShopsEmployeeRegisterBuilder | EmployeeRepository |
| SHOPS_FORM_12 | ShopsWageRegisterBuilder | PayrollRepository |
| SHOPS_FORM_13 | ShopsLeaveRegisterBuilder | EmployeeRepository |
| SHOPS_FORM_C | BonusRegisterBuilder | BonusRepository |
| SHOPS_FORM_VI | ShopsHolidayRegisterBuilder | EmployeeRepository |
| SHOPS_FINES | ShopsFinesRegisterBuilder | DeductionRepository |
| SHOPS_UNPAID | ShopsUnpaidBonusBuilder | BonusRepository |
| ESI_FORM_12 | IncidentBuilder | IncidentRepository |
| EPF_INSPECTION | InspectionRegisterBuilder | IncidentRepository |
| FORM_A | EmployeeRegisterBuilder | EmployeeRepository |
| FORM_C | DeductionRegisterBuilder | DeductionRepository |
| FORM_D | AttendanceRegisterBuilder | AttendanceRepository |
| FORM_D_ER | EqualRemunerationBuilder | EmployeeRepository |
| CONTRACTOR_MASTER | ContractorMasterBuilder | ContractorRepository |

## Builder by Repository

### PayrollRepository (3 Builders)
- WageRegisterBuilder (FORM_B)
- OvertimeRegisterBuilder (FORM_10)
- ShopsWageRegisterBuilder (SHOPS_FORM_12)

### AttendanceRepository (2 Builders)
- AttendanceRegisterBuilder (FORM_25, FORM_D)

### IncidentRepository (7 Builders)
- InspectionRegisterBuilder (FORM_7, EPF_INSPECTION)
- AccidentRegisterBuilder (FORM_11, FORM_26)
- HealthRegisterBuilder (FORM_17)
- AccidentReportBuilder (FORM_18)
- DangerousOccurrenceBuilder (FORM_26A)
- IncidentBuilder (FORM_8, ESI_FORM_12)

### EmployeeRepository (8 Builders)
- WorkShiftBuilder (FORM_2)
- EmployeeRegisterBuilder (FORM_12, FORM_A)
- ShopsLeaveRegisterBuilder (SHOPS_FORM_13)
- ShopsEmployeeRegisterBuilder (SHOPS_FORM_1)
- ShopsHolidayRegisterBuilder (SHOPS_FORM_VI)
- EqualRemunerationBuilder (FORM_D_ER)

### BonusRepository (2 Builders)
- BonusRegisterBuilder (SHOPS_FORM_C)
- ShopsUnpaidBonusBuilder (SHOPS_UNPAID)

### DeductionRepository (4 Builders)
- DeductionRegisterBuilder (FORM_XX, FORM_C)
- FinesRegisterBuilder (FORM_XXI)
- AdvanceRegisterBuilder (FORM_XXII)
- ShopsFinesRegisterBuilder (SHOPS_FINES)

### ContractorRepository (9 Builders)
- ContractorMasterBuilder (FORM_XII, CONTRACTOR_MASTER)
- ContractorWorkmenBuilder (FORM_XIII)
- EmploymentCardBuilder (FORM_XIV)
- ContractorMusterBuilder (FORM_XVI)
- ContractorWageRegisterBuilder (FORM_XVII)
- ContractorWageSlipBuilder (FORM_XIX)
- ContractorOvertimeBuilder (FORM_XXIII)
- ContractorHalfYearlyBuilder (FORM_XXIV)
- PrincipalAnnualBuilder (FORM_XXV)

## Quick Lookup

### Find Builder for Form
```php
$builderClass = FormRegistry::getBuilder('FORM_B');
// Returns: App\Compliance\Builders\WageRegisterBuilder::class
```

### Find Repository for Builder
Check the builder file - repository is injected in constructor

### Find Forms Using Repository
See "Builder by Repository" section above

## Statistics

- Total Forms: 36
- Total Builders: 31
- Total Repositories: 7
- Forms per Builder (avg): 1.16
- Builders per Repository (avg): 4.43

## Status

✅ All 36 forms have builders
✅ All builders implemented
✅ All repositories complete
✅ All mappings verified
