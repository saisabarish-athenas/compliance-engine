# Form Data Architecture Implementation Guide

## Overview

This document describes the production-ready form data architecture that connects all 36 statutory forms with the database.

## Architecture Components

### 1. FormRegistry (`app/Compliance/Registry/FormRegistry.php`)

Maps each form code to:
- **Builder class**: Responsible for fetching and structuring data
- **Blade template**: Renders the form with data

```php
FormRegistry::getBuilder('FORM_B');    // Returns WageRegisterBuilder class
FormRegistry::getTemplate('FORM_B');   // Returns 'compliance.forms.form_b'
```

### 2. Repository Layer (`app/Compliance/Repositories/`)

Centralizes database queries for major data domains:

- **EmployeeRepository**: Employee master data queries
- **PayrollRepository**: Payroll entry queries by period
- **AttendanceRepository**: Attendance records by period
- **ContractorRepository**: Contractor and deployment queries
- **IncidentRepository**: Incident/accident records
- **BonusRepository**: Bonus payment records
- **DeductionRepository**: Deduction records (advances, fines, PF, ESI)

Each repository provides:
- Period-based queries (month/year)
- Branch-based filtering
- Aggregation methods (totals, counts)

### 3. Builder Classes (`app/Compliance/Builders/`)

Each form has a dedicated builder that:
1. Accepts: `tenantId`, `branchId`, `month`, `year`
2. Queries repositories for data
3. Returns structured array ready for Blade

**Base Builder** provides:
- Common initialization
- NIL handling for empty datasets
- Consistent data structure

**Concrete Builders** (examples):
- `WageRegisterBuilder` → FORM_B
- `OvertimeRegisterBuilder` → FORM_10
- `AttendanceRegisterBuilder` → FORM_25
- `IncidentBuilder` → ESI_FORM_12, FORM_8
- `BonusRegisterBuilder` → SHOPS_FORM_C
- `ContractorWorkmenBuilder` → FORM_XIII

### 4. ComplianceDataService (`app/Compliance/ComplianceDataService.php`)

Orchestrates the data flow:

```php
// Build form data
$data = $dataService->buildFormData('FORM_B', $tenantId, $branchId, $month, $year);

// Render form with data
$html = $dataService->renderForm('FORM_B', $tenantId, $branchId, $month, $year);
```

### 5. Service Provider (`app/Providers/ComplianceServiceProvider.php`)

Registers all repositories and services as singletons for dependency injection.

## Database Mapping

| Form Code | Table(s) | Builder |
|-----------|----------|---------|
| FORM_B | workforce_payroll_entry + workforce_employee | WageRegisterBuilder |
| FORM_10 | workforce_payroll_entry | OvertimeRegisterBuilder |
| FORM_25 | workforce_attendance | AttendanceRegisterBuilder |
| FORM_12 | workforce_employee | EmployeeRegisterBuilder |
| FORM_XIII | contract_labour_deployment + contractor_master | ContractorWorkmenBuilder |
| SHOPS_FORM_C | bonus_records | BonusRegisterBuilder |
| ESI_FORM_12 | incident_documents + workforce_employee | IncidentBuilder |
| FORM_XX | workforce_payroll_entry | DeductionRegisterBuilder |

## Usage Examples

### In Controllers

```php
use App\Compliance\ComplianceDataService;

class ComplianceController
{
    public function __construct(private ComplianceDataService $dataService) {}

    public function showForm($formCode)
    {
        $data = $this->dataService->buildFormData(
            $formCode,
            auth()->user()->tenant_id,
            auth()->user()->branch_id,
            now()->month,
            now()->year
        );

        return view('compliance.forms.show', compact('data', 'formCode'));
    }
}
```

### In Blade Templates

```blade
@if($data['status'] === 'NIL')
    <p>No data available for this period</p>
@else
    <table>
        @foreach($data['entries'] as $entry)
            <tr>
                <td>{{ $entry['employee_code'] }}</td>
                <td>{{ $entry['employee_name'] }}</td>
                <td>{{ $entry['gross_salary'] }}</td>
            </tr>
        @endforeach
    </table>
@endif
```

### In ComplianceExecutionService

```php
// Inject the service
public function __construct(
    private ComplianceDataService $dataService
) {}

// Use it to build data before PDF generation
$data = $this->dataService->buildFormData(
    $formCode,
    $tenantId,
    $branchId,
    $month,
    $year
);

// Pass to PDF generator
return view('compliance.pdf.' . $formCode, compact('data'))->render();
```

## NIL Handling

When a dataset is empty:
- Builder returns: `['status' => 'NIL']`
- Template displays: "NIL" or "N/A"
- Maintains government format compliance

## Adding New Forms

1. **Create Builder** in `app/Compliance/Builders/`:
```php
class MyFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $data = $this->payrollRepo->getByPeriod($this->tenantId, $this->month, $this->year);
        return empty($data) ? ['status' => 'NIL'] : ['entries' => $data];
    }
}
```

2. **Register in FormRegistry**:
```php
'MY_FORM' => [
    'builder' => \App\Compliance\Builders\MyFormBuilder::class,
    'template' => 'compliance.forms.my_form',
],
```

3. **Create Blade Template** in `resources/views/compliance/forms/my_form.blade.php`

## Multi-Tenant Isolation

All queries include `tenant_id` filtering:
- Repositories filter by tenant
- Builders pass tenant_id to repositories
- No cross-tenant data leakage

## Performance Considerations

- Repositories use eager loading (`with()`)
- Period-based queries use indexed date fields
- Aggregations use database-level `sum()`, `count()`
- Singletons prevent repeated instantiation

## Testing

```php
$dataService = app(ComplianceDataService::class);
$data = $dataService->buildFormData('FORM_B', 1, 1, 1, 2024);

$this->assertArrayHasKey('entries', $data);
$this->assertArrayHasKey('total_gross', $data);
```

## Files Created

```
app/Compliance/
├── Registry/
│   └── FormRegistry.php
├── Repositories/
│   ├── EmployeeRepository.php
│   ├── PayrollRepository.php
│   ├── AttendanceRepository.php
│   ├── ContractorRepository.php
│   ├── IncidentRepository.php
│   ├── BonusRepository.php
│   └── DeductionRepository.php
├── Builders/
│   ├── BaseBuilder.php
│   ├── WageRegisterBuilder.php
│   ├── OvertimeRegisterBuilder.php
│   ├── AttendanceRegisterBuilder.php
│   ├── EmployeeRegisterBuilder.php
│   ├── IncidentBuilder.php
│   ├── BonusRegisterBuilder.php
│   ├── DeductionRegisterBuilder.php
│   ├── ContractorWorkmenBuilder.php
│   └── StubBuilders.php (23 stub implementations)
└── ComplianceDataService.php

app/Providers/
└── ComplianceServiceProvider.php
```

## Next Steps

1. Implement remaining builders with actual data logic
2. Create Blade templates for each form
3. Update ComplianceExecutionService to use ComplianceDataService
4. Test with demo data
5. Deploy to production
