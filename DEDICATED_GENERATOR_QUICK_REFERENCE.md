# Dedicated Generator Architecture - Quick Reference

## Overview

Each of the 40+ statutory forms now has its own dedicated generator class.

**Old Way:** 5 shared generators with complex conditional logic
**New Way:** 40+ dedicated generators with clear responsibility

## Generator Mapping

### Quick Lookup

```php
// Payroll Forms
FORM_B → FormBGenerator
FORM_10 → Form10Generator
FORM_25 → Form25Generator
FORM_XVI → FormXVIGenerator
FORM_XVII → FormXVIIGenerator
FORM_XIX → FormXIXGenerator
FORM_XXI → FormXXIGenerator
FORM_XXII → FormXXIIGenerator
FORM_XXIII → FormXXIIIGenerator
SHOPS_FORM_12 → ShopsForm12Generator
SHOPS_FINES → ShopsFinesGenerator
FORM_XXIV → FormXXIVGenerator
FORM_XXV → FormXXVGenerator

// Contractor Forms
FORM_XII → FormXIIGenerator
FORM_XIII → FormXIIIGenerator
FORM_XIV → FormXIVGenerator
CLRA_LICENSE → CLRALicenseGenerator
CLRA_RETURN → CLRAReturnGenerator
SHOPS_FORM_1 → ShopsForm1Generator
CONTRACTOR_MASTER → ContractorMasterGenerator
FORM_XX → FormXXGenerator

// Incident Forms
FORM_8 → Form8Generator
FORM_11 → Form11Generator
FORM_18 → Form18Generator
FORM_26 → Form26Generator
FORM_26A → Form26AGenerator
ESI_FORM_12 → ESIForm12Generator

// Inspection Forms
HAZARD_REG → HazardRegisterGenerator
EPF_INSPECTION → EPFInspectionGenerator
SHOPS_FORM_13 → ShopsForm13Generator

// Master Register Forms
FORM_2 → Form2Generator
FORM_7 → Form7Generator
FORM_12 → Form12Generator
FORM_17 → Form17Generator
FORM_A → FormAGenerator
FORM_C → FormCGenerator
FORM_D → FormDGenerator
FORM_D_ER → FormDERGenerator
SHOPS_FORM_C → ShopsFormCGenerator
SHOPS_FORM_VI → ShopsFormVIGenerator
```

## Creating a New Generator

### Template
```php
<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXX';
    protected string $view = 'compliance.forms.form_xxx';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'field1' => $record->field1 ?? 'N/A',
                'field2' => round($record->field2 ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, ['field2']);

        return [
            'header' => [
                'form_title' => 'FORM XXX - Title',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
```

### Steps
1. Create class extending BaseFormGenerator
2. Set `$formCode` and `$view`
3. Implement `prepareData(array $rawData): array`
4. Register in FormGeneratorFactory
5. Create Blade template

## Generator Responsibilities

### ✅ DO
- Transform API data
- Format fields (numbers, dates)
- Calculate totals
- Group records
- Map field names
- Validate data format

### ❌ DON'T
- Query database
- Call API services
- Validate business rules
- Fetch external data
- Orchestrate workflow

## Input/Output Contract

### Input (from API Service)
```php
[
    'records' => [...],              // Form records
    'tenant' => [                    // Tenant details
        'name' => '...',
        'establishment_name' => '...',
        'pf_code' => '...',
        'esi_code' => '...',
    ],
    'branch' => [                    // Branch details
        'name' => '...',
        'address' => '...',
        'pf_code' => '...',
        'esi_code' => '...',
    ],
    'period_month' => 1,
    'period_year' => 2024,
    // Form-specific metadata
]
```

### Output (to Blade Template)
```php
[
    'header' => [
        'form_title' => '...',
        'period' => '...',
        'branch' => [...],
        'tenant' => [...],
    ],
    'rows' => [
        ['field1' => '...', 'field2' => 0, ...],
    ],
    'totals' => [
        'field2' => 100,
    ],
    'is_nil' => false,
]
```

## Common Patterns

### Formatting Numbers
```php
'amount' => round($record->amount ?? 0, 2),
```

### Formatting Dates
```php
'date' => $record->date ? date('d-m-Y', strtotime($record->date)) : 'N/A',
```

### Calculating Totals
```php
$totals = $this->calculateTotals($rows, ['amount', 'quantity']);
```

### Handling Nil Forms
```php
'is_nil' => count($rows) === 0,
```

### Mapping Fields
```php
'employee_name' => $record->name ?? $record->employee_name ?? 'N/A',
```

## Testing

### Unit Test
```php
public function test_form_b_generator_transforms_data()
{
    $generator = new FormBGenerator();
    
    $apiData = [
        'records' => [
            (object)['employee_code' => 'E001', 'basic_earned' => 10000],
        ],
        'tenant' => ['name' => 'Test Tenant'],
        'branch' => ['name' => 'Test Branch'],
        'period_month' => 1,
        'period_year' => 2024,
    ];
    
    $result = $generator->prepareData($apiData);
    
    $this->assertCount(1, $result['rows']);
    $this->assertEquals(10000, $result['totals']['basic_earned']);
}
```

## Debugging

### Enable Trace Logging
```bash
php artisan compliance:trace-form-data \
  --tenant=1 \
  --branch=1 \
  --month=1 \
  --year=2024 \
  --form=FORM_B \
  --verbose
```

### Check Generator Output
```php
$generator = FormGeneratorFactory::make('FORM_B');
$formatted = $generator->prepareData($apiData);
dd($formatted);
```

### Check API Data
```php
$apiService = FormApiServiceFactory::make('FORM_B');
$data = $apiService->fetch(1, 1, 1, 2024);
dd($data);
```

## Performance Tips

1. **Use array_map for transformations**
   ```php
   $rows = array_map(fn($record) => [
       'field1' => $record->field1 ?? 'N/A',
   ], $rawData['records']);
   ```

2. **Pre-calculate totals in API service if possible**
   ```php
   // Better in API service
   $totals = DB::table('payroll_entry')
       ->selectRaw('SUM(basic_earned) as total')
       ->get();
   ```

3. **Use null coalescing for defaults**
   ```php
   'field' => $record->field ?? 'N/A',
   ```

## Troubleshooting

### Issue: Generator receives null data
**Cause:** API service not returning expected structure
**Fix:** Check API service returns all required keys

### Issue: Missing fields in output
**Cause:** Generator not mapping all fields
**Fix:** Add field mapping in prepareData()

### Issue: Totals don't match
**Cause:** calculateTotals() called with wrong fields
**Fix:** Verify field names in rows match totals config

### Issue: Form not rendering
**Cause:** Blade template not found
**Fix:** Check view path in generator matches template location

## File Locations

### Generators
```
app/Services/Compliance/FormGenerator/
├── FormBGenerator.php
├── Form10Generator.php
├── FormXIIGenerator.php
├── FormXIIIGenerator.php
├── ... (40+ generators)
└── FormGeneratorFactory.php
```

### Blade Templates
```
resources/views/compliance/forms/
├── form_b.blade.php
├── form_10.blade.php
├── form_xii.blade.php
├── form_xiii.blade.php
├── ... (40+ templates)
```

### API Services
```
app/Services/Compliance/FormApis/
├── FormBApiService.php
├── Form10ApiService.php
├── FormXIIApiService.php
├── FormXIIIApiService.php
├── ... (40+ API services)
└── FormApiServiceFactory.php
```

## Migration Checklist

- [x] Create dedicated generators for all forms
- [x] Update FormGeneratorFactory
- [x] Verify API services provide complete data
- [x] Test form generation
- [x] Verify PDF rendering
- [x] Check error logs
- [x] Document architecture

## Next Steps

1. **Run Trace Command**
   ```bash
   php artisan compliance:trace-form-data --form=FORM_B
   ```

2. **Test Each Form Type**
   - Payroll forms
   - Contractor forms
   - Incident forms
   - Inspection forms
   - Master register forms

3. **Monitor Performance**
   - Check execution time
   - Monitor memory usage
   - Check database queries

4. **Gather Feedback**
   - User testing
   - Performance metrics
   - Error logs

---

**Architecture:** Dedicated generators per form
**Status:** ✅ COMPLETE
**Ready for Production:** ✅ YES
