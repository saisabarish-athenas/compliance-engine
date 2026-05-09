# Automatic Mapping Engine - Quick Reference

## What Was Created

### 1. BladeMappingEngine
**File:** `app/Services/Compliance/FormGenerator/BladeMappingEngine.php`

Extracts columns from Blade templates and maps them to database fields.

```php
$engine = new BladeMappingEngine();
$columns = $engine->extractColumns($bladeContent);
$mapping = $engine->generateRowMapping($columns);
```

### 2. New Form Services (9 Total)

| Service | Form | Purpose |
|---------|------|---------|
| FormXXIService | FORM XXI | Register of Fines |
| FormXXIIService | FORM XXII | Register of Advances |
| FormXXIIIService | FORM XXIII | Register of Overtime |
| FormXXIVService | FORM XXIV | Annual Return |
| FormXXVService | FORM XXV | Half-Yearly Return |
| Form7Service | FORM 7 | Notice of Periods |
| ClraLicenseService | CLRA_LICENSE | License Register |
| ClraReturnService | CLRA_RETURN | CLRA Half-Yearly Return |
| ContractorMasterService | CONTRACTOR_MASTER | Contractor Master Register |

### 3. GenerateFormServices Command
**File:** `app/Console/Commands/GenerateFormServices.php`

Auto-generates services from Blade templates.

```bash
php artisan compliance:generate-form-services
php artisan compliance:generate-form-services --force
```

### 4. Updated FormGeneratorFactory
**File:** `app/Services/Compliance/FormGenerator/FormGeneratorFactory.php`

Registered all new forms in appropriate categories.

## Column Mapping Reference

### Employee Data
- `employee_name` → `workforce_employee.name`
- `name` → `workforce_employee.name`
- `father_name` → `workforce_employee.father_name`
- `designation` → `workforce_employee.designation`
- `sex` → `workforce_employee.gender`

### Attendance & Dates
- `damage_date` → `workforce_attendance.attendance_date`
- `joining_date` → `contract_labour_deployment.deployment_start`
- `termination_date` → `contract_labour_deployment.deployment_end`
- `overtime_dates` → (custom mapping)

### Financial Data
- `deduction_amount` → `(workforce_payroll_entry.fines + workforce_payroll_entry.other_deductions)`
- `fine_amount` → (custom mapping)
- `normal_rate` → (custom mapping)
- `overtime_rate` → (custom mapping)

### Contractor Data
- `contractor_name` → `contractor_master.company_name`
- `contractor_address` → `contractor_master.company_address`
- `nature_of_work` → `contract_labour_deployment.nature_of_work`
- `work_location` → `contract_labour_deployment.work_location`
- `max_workers` → `contract_labour_deployment.employee_id` (COUNT)

### Empty Mappings (Custom Data)
- `showed_cause` → ''
- `witness_name` → ''
- `damage_particulars` → ''
- `remarks` → ''
- `purpose` → ''
- `signature` → ''

## Service Structure

All services follow this pattern:

```php
class FormXXXService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        // 1. Start debugging
        FormDebugger::start('FORM_CODE');
        
        // 2. Set instance variables
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;
        
        // 3. Get date range
        [$startDate, $endDate] = $this->getDateRange();
        
        // 4. Query data with filters
        $rows = DB::table('...')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('date_field', [$startDate, $endDate])
            ->select([...])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();
        
        // 5. End debugging
        FormDebugger::end('FORM_CODE', $rows);
        
        // 6. Build header
        $header = [...];
        
        // 7. Return response
        return [
            'header' => $header,
            'rows' => $rows,
            'is_nil' => empty($rows),
            'totals' => []
        ];
    }
}
```

## Response Format

```php
[
    'header' => [
        'tenant' => ['name' => 'Company Name'],
        'branch' => ['name' => 'Branch Name', 'address' => 'Address'],
        'period' => 'January 2024',
    ],
    'rows' => [
        [
            'employee_name' => 'John Doe',
            'father_name' => 'Jane Doe',
            'designation' => 'Laborer',
            // ... more columns
        ],
    ],
    'is_nil' => false,
    'totals' => []
]
```

## Usage Examples

### In Controller
```php
use App\Services\Compliance\Forms\FormXXIService;

$service = new FormXXIService();
$data = $service->generate($tenantId, $branchId, $month, $year);
return view('compliance.forms.form_xxi', $data);
```

### With Factory
```php
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;

$generator = FormGeneratorFactory::make('FORM_XXI');
if ($generator) {
    $data = $generator->generate($tenantId, $branchId, $month, $year);
}
```

### In Command
```php
$service = app(FormXXIService::class);
$data = $service->generate($tenantId, $branchId, $month, $year);
```

## Multi-Tenant Safety

All services include:
```php
->where('tenant_id', $tenantId)
->where('branch_id', $branchId)
```

This ensures:
- Data isolation between tenants
- Branch-specific filtering
- No cross-tenant data leakage

## Date Filtering

Services use payroll cycle dates:
```php
[$startDate, $endDate] = $this->getDateRange();
->whereBetween('date_field', [$startDate, $endDate])
```

This ensures:
- Monthly data alignment
- Accurate period reporting
- Consistent date ranges

## Nil Handling

When no data exists:
```php
if (empty($rows)) {
    return [
        'header' => $header,
        'rows' => [],
        'is_nil' => true,
        'totals' => []
    ];
}
```

Blade templates check:
```blade
@if($is_nil)
    <div>Nil for the month of {{ $header['period'] }}</div>
@else
    <!-- Show table -->
@endif
```

## Adding New Forms

### Step 1: Create Service
```php
class FormXXXService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        // Implementation
    }
}
```

### Step 2: Register in Factory
```php
protected static array $payrollForms = [
    'FORM_XXX', // Add here
];
```

### Step 3: Use in Controller
```php
$service = new FormXXXService();
$data = $service->generate($tenantId, $branchId, $month, $year);
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Service not found | Check FormGeneratorFactory registration |
| Missing columns | Verify Blade template patterns |
| Nil data | Check date range and filters |
| Database error | Verify table/column names |
| Cross-tenant data | Ensure tenant_id filter exists |

## Files Modified/Created

### Created
- `app/Services/Compliance/FormGenerator/BladeMappingEngine.php`
- `app/Services/Compliance/Forms/FormXXIService.php`
- `app/Services/Compliance/Forms/FormXXIIService.php`
- `app/Services/Compliance/Forms/FormXXIIIService.php`
- `app/Services/Compliance/Forms/FormXXIVService.php`
- `app/Services/Compliance/Forms/FormXXVService.php`
- `app/Services/Compliance/Forms/Form7Service.php`
- `app/Services/Compliance/Forms/ClraLicenseService.php`
- `app/Services/Compliance/Forms/ClraReturnService.php`
- `app/Services/Compliance/Forms/ContractorMasterService.php`
- `app/Console/Commands/GenerateFormServices.php`

### Modified
- `app/Services/Compliance/FormGenerator/FormGeneratorFactory.php`

## Next Steps

1. Run `php artisan compliance:generate-form-services` to generate additional services
2. Test each service with sample data
3. Verify PDF rendering works correctly
4. Update controllers to use new services
5. Run compliance execution tests
