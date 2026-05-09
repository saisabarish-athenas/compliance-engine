# Automatic Compliance Form Mapping Engine

## Overview

The Automatic Compliance Form Mapping Engine is a Laravel-based system that automatically extracts column definitions from Blade templates and generates corresponding form service classes with database mappings.

## Architecture

### Components

1. **BladeMappingEngine** (`app/Services/Compliance/FormGenerator/BladeMappingEngine.php`)
   - Extracts column variables from Blade templates
   - Maps columns to database fields using heuristic rules
   - Generates row mapping code

2. **Form Services** (`app/Services/Compliance/Forms/`)
   - Auto-generated or manually created service classes
   - Extend `BaseFormService`
   - Implement `generate(tenantId, branchId, month, year)` method
   - Return standardized response format

3. **FormGeneratorFactory** (`app/Services/Compliance/FormGenerator/FormGeneratorFactory.php`)
   - Routes form codes to appropriate generators
   - Categorizes forms by type (payroll, contractor, incident, inspection, master register)

4. **GenerateFormServices Command** (`app/Console/Commands/GenerateFormServices.php`)
   - CLI command to auto-generate services from blade templates
   - Supports force overwrite option

## Column Extraction Patterns

The engine recognizes three patterns in Blade templates:

### Pattern 1: Array Access
```blade
{{ $row['employee_name'] ?? '' }}
```

### Pattern 2: data_get() Function
```blade
{{ data_get($row, 'father_name') }}
```

### Pattern 3: Direct Variable Access
```blade
{{ $row['designation'] ?? '' }}
```

## Database Mapping Rules

The engine uses heuristic mapping to connect Blade columns to database fields:

| Blade Column | Database Mapping |
|---|---|
| `employee_name` | `workforce_employee.name` |
| `father_name` | `workforce_employee.father_name` |
| `designation` | `workforce_employee.designation` |
| `damage_date` | `workforce_attendance.attendance_date` |
| `deduction_amount` | `(workforce_payroll_entry.fines + workforce_payroll_entry.other_deductions)` |
| `joining_date` | `contract_labour_deployment.deployment_start` |
| `termination_date` | `contract_labour_deployment.deployment_end` |
| `contractor_name` | `contractor_master.company_name` |
| `nature_of_work` | `contract_labour_deployment.nature_of_work` |
| `work_location` | `contract_labour_deployment.work_location` |

## Generated Form Services

### Newly Generated Services

1. **FormXXIService** - Register of Fines
   - Extracts: name, father_name, designation, fine_amount, etc.
   - Source: workforce_employee, payroll data

2. **FormXXIIService** - Register of Advances
   - Extracts: name, father_name, designation, advance amounts
   - Source: workforce_employee, payroll data

3. **FormXXIIIService** - Register of Overtime
   - Extracts: name, father_name, sex, designation, overtime hours
   - Source: contract_labour_deployment, workforce_employee

4. **FormXXIVService** - Annual Return
   - Extracts: contractor info, total workers, wages, deductions
   - Source: contractor_master, contract_labour_deployment

5. **FormXXVService** - Half-Yearly Return
   - Extracts: contractor info, total workers, wages, deductions
   - Source: contractor_master, contract_labour_deployment

6. **Form7Service** - Notice of Periods
   - Placeholder service for Factories Act compliance
   - Source: workforce_employee

7. **ClraLicenseService** - License Register
   - Extracts: contractor license details
   - Source: contractor_master

8. **ClraReturnService** - CLRA Half-Yearly Return
   - Extracts: contract labour deployment data
   - Source: contract_labour_deployment, contractor_master

9. **ContractorMasterService** - Contractor Master Register
   - Extracts: contractor details, contact info, license info
   - Source: contractor_master

## Service Response Format

All form services return a standardized array:

```php
[
    'header' => [
        'tenant' => ['name' => '...'],
        'branch' => ['name' => '...', 'address' => '...'],
        'period' => 'January 2024',
    ],
    'rows' => [
        [
            'employee_name' => 'John Doe',
            'father_name' => 'Jane Doe',
            'designation' => 'Laborer',
            // ... more columns
        ],
        // ... more rows
    ],
    'is_nil' => false,
    'totals' => []
]
```

## Multi-Tenant & Branch Filtering

All services automatically filter by:
- `tenant_id` - Ensures data isolation
- `branch_id` - Filters by establishment
- Date range - Based on payroll cycle or deployment dates

Example:
```php
DB::table('workforce_employee as e')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->whereBetween('a.attendance_date', [$startDate, $endDate])
```

## Usage

### Generate Services from Blade Templates

```bash
php artisan compliance:generate-form-services
```

### Force Regenerate (Overwrite Existing)

```bash
php artisan compliance:generate-form-services --force
```

### Use in Controller

```php
use App\Services\Compliance\Forms\FormXXIService;

$service = new FormXXIService();
$data = $service->generate($tenantId, $branchId, $month, $year);

return view('compliance.forms.form_xxi', $data);
```

### Use with Factory

```php
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;

$generator = FormGeneratorFactory::make('FORM_XXI');
if ($generator) {
    $data = $generator->generate($tenantId, $branchId, $month, $year);
}
```

## Integration Points

### ComplianceExecutionController
Services are called during form execution:
```php
$service = new FormXXIService();
$formData = $service->generate($tenantId, $branchId, $month, $year);
```

### ComplianceInspectForm Command
Services provide data for inspection and validation:
```php
$service = app($serviceClass);
$data = $service->generate($tenantId, $branchId, $month, $year);
```

### PDF Rendering System
Services provide structured data for PDF generation:
```php
$pdf = PDF::loadView('compliance.forms.form_xxi', $data);
```

## Extending the Mapping Engine

### Add Custom Column Mapping

Edit `BladeMappingEngine::$columnMappings`:

```php
protected array $columnMappings = [
    'custom_column' => 'custom_table.custom_field',
    // ... existing mappings
];
```

### Create Custom Service

Extend `BaseFormService`:

```php
class CustomFormService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        // Custom implementation
    }
}
```

### Register in Factory

Add to appropriate form category in `FormGeneratorFactory`:

```php
protected static array $customForms = [
    'CUSTOM_FORM_1', 'CUSTOM_FORM_2'
];
```

## Best Practices

1. **Always filter by tenant_id and branch_id** - Ensures multi-tenant safety
2. **Use date ranges** - Filter by payroll cycle or deployment dates
3. **Handle nil cases** - Return empty rows with `is_nil: true`
4. **Use FormDebugger** - Log generation for troubleshooting
5. **Coalesce null values** - Use `COALESCE()` for optional fields
6. **Return consistent structure** - Always include header, rows, is_nil, totals

## Troubleshooting

### Service Not Found
- Check FormGeneratorFactory registration
- Verify form code matches factory array

### Missing Columns
- Verify Blade template uses recognized patterns
- Check BladeMappingEngine column extraction

### Nil Data
- Verify date range filtering
- Check tenant_id and branch_id filters
- Ensure data exists in source tables

### Database Errors
- Verify table names and column names
- Check for typos in mappings
- Ensure relationships are correct

## Performance Considerations

- Services use indexed queries on tenant_id, branch_id
- Date range filtering reduces result sets
- Grouping and aggregation done at database level
- Consider caching for frequently accessed forms

## Future Enhancements

1. **Dynamic Column Detection** - Auto-detect new columns from templates
2. **Relationship Mapping** - Handle complex joins automatically
3. **Validation Rules** - Generate validation from column types
4. **Export Formats** - Support CSV, Excel, JSON exports
5. **Audit Trail** - Track form generation history
