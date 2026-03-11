# Form Data Architecture - Quick Reference

## Adding a New Form

### Step 1: Create the Builder
```php
// app/Compliance/Builders/MyFormBuilder.php
<?php

namespace App\Compliance\Builders;

class MyFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        // Fetch data from repositories
        $data = $this->payrollRepo->getByBranchAndPeriod(
            $this->tenantId,
            $this->branchId,
            $this->month,
            $this->year
        );

        if ($data->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $data->map(fn($item) => [
                'field1' => $item->field1 ?? 'N/A',
                'field2' => $item->field2 ?? 0,
            ])->toArray(),
            'total' => $data->sum('field2'),
        ];
    }
}
```

### Step 2: Register in FormRegistry
```php
// app/Compliance/Registry/FormRegistry.php
'MY_FORM' => [
    'builder' => \App\Compliance\Builders\MyFormBuilder::class,
    'template' => 'compliance.forms.my_form',
],
```

### Step 3: Create Blade Template
```blade
<!-- resources/views/compliance/forms/my_form.blade.php -->
@if($data['status'] === 'NIL')
    <p>No data available</p>
@else
    <table>
        @foreach($data['entries'] as $entry)
            <tr>
                <td>{{ $entry['field1'] }}</td>
                <td>{{ $entry['field2'] }}</td>
            </tr>
        @endforeach
    </table>
    <p>Total: {{ $data['total'] }}</p>
@endif
```

## Available Repositories

### PayrollRepository
```php
$this->payrollRepo->getByPeriod($tenantId, $month, $year);
$this->payrollRepo->getByBranchAndPeriod($tenantId, $branchId, $month, $year);
$this->payrollRepo->getByEmployee($employeeId, $month, $year);
$this->payrollRepo->getTotalDeductions($tenantId, $month, $year);
$this->payrollRepo->getTotalAdvances($tenantId, $month, $year);
$this->payrollRepo->getTotalFines($tenantId, $month, $year);
```

### AttendanceRepository
```php
$this->attendanceRepo->getByPeriod($tenantId, $month, $year);
$this->attendanceRepo->getByBranchAndPeriod($tenantId, $branchId, $month, $year);
$this->attendanceRepo->getByEmployee($employeeId, $month, $year);
$this->attendanceRepo->getDaysWorked($employeeId, $month, $year);
```

### IncidentRepository
```php
$this->incidentRepo->getByPeriod($tenantId, $month, $year);
$this->incidentRepo->getByBranchAndPeriod($tenantId, $branchId, $month, $year);
$this->incidentRepo->getByType($tenantId, $type, $month, $year);
$this->incidentRepo->getAll($tenantId);
```

### EmployeeRepository
```php
$this->employeeRepo->getByBranch($tenantId, $branchId);
$this->employeeRepo->getAll($tenantId);
$this->employeeRepo->getById($employeeId);
$this->employeeRepo->getActive($tenantId, $branchId);
```

### BonusRepository
```php
$this->bonusRepo->getByPeriod($tenantId, $month, $year);
$this->bonusRepo->getByBranchAndPeriod($tenantId, $branchId, $month, $year);
$this->bonusRepo->getTotalBonus($tenantId, $month, $year);
$this->bonusRepo->getUnpaid($tenantId, $month, $year);
```

### DeductionRepository
```php
$this->deductionRepo->getByPeriod($tenantId, $month, $year);
$this->deductionRepo->getByBranchAndPeriod($tenantId, $branchId, $month, $year);
$this->deductionRepo->getAdvances($tenantId, $month, $year);
$this->deductionRepo->getFines($tenantId, $month, $year);
```

### ContractorRepository
```php
$this->contractorRepo->getDeploymentsByPeriod($tenantId, $month, $year);
$this->contractorRepo->getDeploymentsByBranch($tenantId, $branchId, $month, $year);
$this->contractorRepo->getContractors($tenantId);
$this->contractorRepo->getContractorById($contractorId);
$this->contractorRepo->getActiveDeployments($tenantId, $month, $year);
```

## Using the Data Service

```php
// Inject the service
$dataService = app(App\Compliance\ComplianceDataService::class);

// Build form data
$data = $dataService->buildFormData(
    'FORM_B',      // Form code
    1,             // Tenant ID
    1,             // Branch ID
    12,            // Month
    2024           // Year
);

// Render form
$html = $dataService->renderForm('FORM_B', 1, 1, 12, 2024);
```

## Builder Best Practices

1. **Always check for empty data**
   ```php
   if ($data->isEmpty()) {
       return ['status' => 'NIL'];
   }
   ```

2. **Use null coalescing for safety**
   ```php
   'field' => $item->field ?? 'N/A',
   'amount' => $item->amount ?? 0,
   ```

3. **Map collections properly**
   ```php
   'entries' => $data->map(fn($item) => [
       'name' => $item->name,
       'value' => $item->value,
   ])->toArray(),
   ```

4. **Calculate totals where needed**
   ```php
   'total' => $data->sum('amount'),
   'count' => $data->count(),
   ```

5. **Always include period information**
   ```php
   'period' => "{$this->month}/{$this->year}",
   ```

## Debugging

```bash
# Test a specific form
php artisan tinker

$dataService = app(App\Compliance\ComplianceDataService::class);
$data = $dataService->buildFormData('FORM_B', 1, 1, 12, 2024);
dd($data);

# Check if form is registered
$registry = App\Compliance\Registry\FormRegistry::class;
dd($registry::isRegistered('FORM_B'));

# Get builder class
dd($registry::getBuilder('FORM_B'));
```

## Common Issues

### "Builder not found"
- Check FormRegistry has the form registered
- Verify builder class exists and extends BaseBuilder
- Ensure builder is in correct namespace

### "No data returned"
- Check repository query filters (tenant_id, branch_id)
- Verify data exists in database for the period
- Check if status is 'NIL' (expected for empty datasets)

### "Template not found"
- Verify template path in FormRegistry matches actual file
- Check template file exists in resources/views/compliance/forms/
- Ensure template name uses underscores, not hyphens

## Performance Tips

1. Use `with()` for eager loading relationships
2. Filter by branch_id to reduce dataset size
3. Use `sum()` and `count()` on collections, not in loops
4. Cache builder results if data doesn't change frequently
5. Use pagination for large datasets

## Security

- All queries automatically filter by tenant_id
- Branch filtering prevents cross-branch data leakage
- No direct database access from templates
- All user input validated in repositories
