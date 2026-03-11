# Form Data Architecture - Quick Reference

## 36 Forms Mapping

### Factories Act Forms (12)
| Form | Builder | Table |
|------|---------|-------|
| FORM_B | WageRegisterBuilder | workforce_payroll_entry |
| FORM_10 | OvertimeRegisterBuilder | workforce_payroll_entry |
| FORM_25 | AttendanceRegisterBuilder | workforce_attendance |
| FORM_12 | EmployeeRegisterBuilder | workforce_employee |
| FORM_2 | WorkShiftBuilder | workforce_attendance |
| FORM_7 | InspectionRegisterBuilder | inspection_documents |
| FORM_8 | IncidentBuilder | incident_documents |
| FORM_11 | AccidentRegisterBuilder | incident_documents |
| FORM_17 | HealthRegisterBuilder | workforce_employee |
| FORM_18 | AccidentReportBuilder | workforce_employee |
| FORM_26 | AccidentRegisterBuilder | incident_documents |
| FORM_26A | DangerousOccurrenceBuilder | incident_documents |

### CLRA Forms (13)
| Form | Builder | Table |
|------|---------|-------|
| FORM_XII | ContractorMasterBuilder | contractor_master |
| FORM_XIII | ContractorWorkmenBuilder | contract_labour_deployment |
| FORM_XIV | EmploymentCardBuilder | contract_labour_deployment |
| FORM_XVI | ContractorMusterBuilder | contract_labour_deployment |
| FORM_XVII | ContractorWageRegisterBuilder | contract_labour_deployment |
| FORM_XIX | ContractorWageSlipBuilder | contract_labour_deployment |
| FORM_XX | DeductionRegisterBuilder | contract_labour_deployment |
| FORM_XXI | FinesRegisterBuilder | contract_labour_deployment |
| FORM_XXII | AdvanceRegisterBuilder | contract_labour_deployment |
| FORM_XXIII | ContractorOvertimeBuilder | contract_labour_deployment |
| FORM_XXIV | ContractorHalfYearlyBuilder | clra_returns |
| FORM_XXV | PrincipalAnnualBuilder | clra_returns |
| CLRA_LICENSE | ContractorMasterBuilder | contractor_compliance |

### Shops Act Forms (7)
| Form | Builder | Table |
|------|---------|-------|
| SHOPS_FORM_12 | ShopsWageRegisterBuilder | workforce_payroll_entry |
| SHOPS_FORM_13 | ShopsLeaveRegisterBuilder | workforce_attendance |
| SHOPS_FORM_1 | ShopsEmployeeRegisterBuilder | workforce_employee |
| SHOPS_FORM_C | BonusRegisterBuilder | bonus_records |
| SHOPS_FORM_VI | ShopsHolidayRegisterBuilder | workforce_attendance |
| SHOPS_FINES | ShopsFinesRegisterBuilder | workforce_payroll_entry |
| SHOPS_UNPAID | ShopsUnpaidBonusBuilder | bonus_records |

### Social Security Forms (2)
| Form | Builder | Table |
|------|---------|-------|
| ESI_FORM_12 | IncidentBuilder | incident_documents |
| EPF_INSPECTION | InspectionRegisterBuilder | inspection_documents |

### Labour Welfare Forms (2)
| Form | Builder | Table |
|------|---------|-------|
| FORM_A | EmployeeRegisterBuilder | workforce_employee |
| FORM_C | DeductionRegisterBuilder | workforce_payroll_entry |
| FORM_D | AttendanceRegisterBuilder | workforce_attendance |
| FORM_D_ER | EqualRemunerationBuilder | workforce_payroll_entry |

## Key Classes

### FormRegistry
```php
FormRegistry::getBuilder('FORM_B');      // Get builder class
FormRegistry::getTemplate('FORM_B');     // Get template path
FormRegistry::isRegistered('FORM_B');    // Check if registered
FormRegistry::all();                     // Get all mappings
```

### ComplianceDataService
```php
$dataService = app(ComplianceDataService::class);

// Build data
$data = $dataService->buildFormData('FORM_B', $tenantId, $branchId, $month, $year);

// Render form
$html = $dataService->renderForm('FORM_B', $tenantId, $branchId, $month, $year);
```

### Repositories
```php
// EmployeeRepository
$employees = $employeeRepo->getByBranch($tenantId, $branchId);
$active = $employeeRepo->getActive($tenantId, $branchId);

// PayrollRepository
$entries = $payrollRepo->getByPeriod($tenantId, $month, $year);
$total = $payrollRepo->getTotalDeductions($tenantId, $month, $year);

// AttendanceRepository
$records = $attendanceRepo->getByPeriod($tenantId, $month, $year);
$days = $attendanceRepo->getDaysWorked($employeeId, $month, $year);

// ContractorRepository
$deployments = $contractorRepo->getDeploymentsByPeriod($tenantId, $month, $year);
$contractors = $contractorRepo->getContractors($tenantId);

// IncidentRepository
$incidents = $incidentRepo->getByPeriod($tenantId, $month, $year);
$byType = $incidentRepo->getByType($tenantId, 'accident', $month, $year);

// BonusRepository
$bonuses = $bonusRepo->getByPeriod($tenantId, $month, $year);
$total = $bonusRepo->getTotalBonus($tenantId, $month, $year);

// DeductionRepository
$deductions = $deductionRepo->getByPeriod($tenantId, $month, $year);
$advances = $deductionRepo->getAdvances($tenantId, $month, $year);
```

## Data Structure

### Empty Dataset
```php
['status' => 'NIL']
```

### Wage Register (FORM_B)
```php
[
    'period' => '1/2024',
    'entries' => [
        [
            'employee_code' => 'EMP001',
            'employee_name' => 'John Doe',
            'designation' => 'Manager',
            'basic_earned' => 15000,
            'gross_salary' => 20000,
            'total_deductions' => 2000,
            'net_salary' => 18000,
            'days_worked' => 26,
        ]
    ],
    'total_gross' => 500000,
    'total_deductions' => 50000,
    'total_net' => 450000,
]
```

### Attendance Register (FORM_25)
```php
[
    'period' => '1/2024',
    'entries' => [
        [
            'employee_code' => 'EMP001',
            'employee_name' => 'John Doe',
            'present_days' => 26,
            'absent_days' => 2,
            'leave_days' => 2,
            'total_days' => 30,
        ]
    ],
]
```

## Integration Points

### In Controllers
```php
use App\Compliance\ComplianceDataService;

public function show($formCode)
{
    $data = app(ComplianceDataService::class)->buildFormData(
        $formCode,
        auth()->user()->tenant_id,
        auth()->user()->branch_id,
        now()->month,
        now()->year
    );
    
    return view('compliance.forms.show', compact('data', 'formCode'));
}
```

### In PDF Generation
```php
$data = $this->dataService->buildFormData($formCode, $tenantId, $branchId, $month, $year);
$pdf = PDF::loadView('compliance.pdf.' . $formCode, compact('data'));
return $pdf->download($formCode . '.pdf');
```

### In Blade Templates
```blade
@if($data['status'] === 'NIL')
    <p class="text-center">NIL</p>
@else
    <table>
        @foreach($data['entries'] as $entry)
            <tr>
                <td>{{ $entry['employee_code'] ?? 'N/A' }}</td>
                <td>{{ $entry['employee_name'] ?? 'N/A' }}</td>
                <td>{{ number_format($entry['gross_salary'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
    </table>
@endif
```

## Extending the System

### Add New Builder
1. Create class in `app/Compliance/Builders/`
2. Extend `BaseBuilder`
3. Implement `getData()` method
4. Register in `FormRegistry`

### Add New Repository
1. Create class in `app/Compliance/Repositories/`
2. Add query methods
3. Register in `ComplianceServiceProvider`
4. Inject into builders

## Testing
```php
$dataService = app(ComplianceDataService::class);
$data = $dataService->buildFormData('FORM_B', 1, 1, 1, 2024);

$this->assertIsArray($data);
$this->assertTrue(isset($data['entries']) || $data['status'] === 'NIL');
```

## Performance Tips
- Use period-based queries (month/year)
- Leverage eager loading with `with()`
- Use database aggregations (`sum()`, `count()`)
- Cache registry lookups if needed
- Index date fields in database
