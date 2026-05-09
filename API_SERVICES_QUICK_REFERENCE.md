# API Services Quick Reference

## How to Use

### 1. Fetch Data for a Form

```php
use App\Services\Compliance\FormApis\FormApiServiceFactory;

// Get API service for a form
$apiService = FormApiServiceFactory::make('FORM_B');

// Fetch data with tenant and branch filtering
$data = $apiService->fetch(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024
);

// Returns:
// [
//     'tenant_id' => 1,
//     'branch_id' => 1,
//     'month' => 1,
//     'year' => 2024,
//     'period' => 'January 2024',
//     'tenant' => [...],
//     'branch' => [...],
//     'rows' => [...],
//     'record_count' => 10
// ]
```

### 2. In ComplianceOrchestrator

The orchestrator automatically:
1. Calls `FormApiServiceFactory::make($formCode)`
2. Fetches data via `$apiService->fetch($tenantId, $branchId, $month, $year)`
3. Validates tenant/branch IDs match
4. Passes data to generator
5. Renders template

### 3. Add a New Form API Service

```php
<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('your_table')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            // ... your query
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}
```

Then register in FormApiServiceFactory:
```php
'FORM_XXX' => FormXXXApiService::class,
```

## Form Code Mapping

### CLRA Forms
| Form Code | Service Class |
|-----------|---------------|
| FORM_XII | FormXIIApiService |
| FORM_XIII | FormXIIIApiService |
| FORM_XIV | FormXIVApiService |
| FORM_XVI | FormXVIApiService |
| FORM_XVII | FormXVIIApiService |
| FORM_XIX | FormXIXApiService |
| FORM_XX | FormXXApiService |
| FORM_XXI | FormXXIApiService |
| FORM_XXII | FormXXIIApiService |
| FORM_XXIII | FormXXIIIApiService |

### Labour Welfare Forms
| Form Code | Service Class |
|-----------|---------------|
| FORM_A | FormAApiService |
| FORM_C | FormCApiService |
| FORM_D | FormDApiService |
| FORM_D_ER | FormDERApiService |

### Social Security
| Form Code | Service Class |
|-----------|---------------|
| FORM_11 | Form11ApiService |
| ESI_FORM_12 | ESIForm12ApiService |
| EPF_INSPECTION | EPFInspectionApiService |

### Factories Act
| Form Code | Service Class |
|-----------|---------------|
| FORM_B | FormBApiService |
| FORM_2 | Form2ApiService |
| FORM_8 | Form8ApiService |
| FORM_10 | Form10ApiService |
| FORM_12 | Form12ApiService |
| FORM_17 | Form17ApiService |
| FORM_18 | Form18ApiService |
| FORM_25 | Form25ApiService |
| FORM_26 | Form26ApiService |
| FORM_26A | Form26AApiService |
| HAZARD_REG | HazardRegApiService |

### Shops & Establishment
| Form Code | Service Class |
|-----------|---------------|
| SHOPS_FORM_12 | ShopsForm12ApiService |
| SHOPS_FORM_13 | ShopsForm13ApiService |
| SHOPS_FORM_C | ShopsFormCApiService |
| SHOPS_FORM_VI | ShopsFormVIApiService |
| SHOPS_FINES | ShopsFinesApiService |
| SHOPS_UNPAID | ShopsUnpaidApiService |

## BaseFormApiService Methods

### Protected Methods

```php
// Initialize period dates
protected function initializePeriod(int $month, int $year): void

// Get tenant details
protected function getTenantDetails(int $tenantId): array

// Get branch details
protected function getBranchDetails(int $branchId, int $tenantId): array

// Format period as string
protected function formatPeriod(): string

// Validate tenant and branch exist
protected function validateTenantAndBranch(int $tenantId, int $branchId): void
```

### Abstract Method

```php
// Must be implemented by each service
abstract public function fetch(int $tenantId, int $branchId, int $month, int $year): array
```

## Multi-Tenant Safety

All queries enforce:
```php
->where('tenant_id', $tenantId)
->where('branch_id', $branchId)
```

ComplianceOrchestrator validates:
```php
if (isset($rawData['tenant_id']) && $rawData['tenant_id'] !== $tenantId) {
    throw new Exception("Tenant ID mismatch");
}
if (isset($rawData['branch_id']) && $rawData['branch_id'] !== $branchId) {
    throw new Exception("Branch ID mismatch");
}
```

## Testing

```bash
# Trace form data for all forms
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1

# Test specific form
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1 --form_code=FORM_B
```

## Common Queries

### Employee Payroll Data
```php
DB::table('workforce_payroll_entry as pe')
    ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
    ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->whereYear('pc.period_from', $year)
    ->whereMonth('pc.period_from', $month)
```

### Attendance Data
```php
DB::table('workforce_attendance as wa')
    ->join('workforce_employee as we', 'we.id', '=', 'wa.employee_id')
    ->where('we.tenant_id', $tenantId)
    ->where('we.branch_id', $branchId)
    ->whereYear('wa.attendance_date', $year)
    ->whereMonth('wa.attendance_date', $month)
```

### Contractor Data
```php
DB::table('contractor_master as cm')
    ->where('cm.tenant_id', $tenantId)
    ->where('cm.branch_id', $branchId)
```

### Incident Data
```php
DB::table('incidents as i')
    ->where('i.tenant_id', $tenantId)
    ->where('i.branch_id', $branchId)
    ->whereYear('i.incident_date', $year)
    ->whereMonth('i.incident_date', $month)
```

## Troubleshooting

### API Service Returns Null
- Check form code exists in FormApiServiceFactory
- Verify service class is properly registered
- Check service class extends BaseFormApiService

### Multi-Tenant Validation Fails
- Ensure all queries include `where tenant_id = $tenantId`
- Ensure all queries include `where branch_id = $branchId`
- Verify returned data includes correct tenant_id and branch_id

### No Data Returned
- Check database has records for the period
- Verify tenant_id and branch_id exist
- Check date range filters (month/year)

## Performance Tips

1. Use indexed columns in WHERE clauses
2. Select only required columns
3. Use eager loading for relationships
4. Consider caching for frequently accessed data
5. Monitor query execution time in logs
