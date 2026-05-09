# CODE FIX REFERENCE - GENERATOR ARRAY ACCESS PATTERN

## THE BUG

All 17 failing generators had this pattern:

```php
foreach ($rawData['records'] ?? [] as $record) {
    $rows[] = [
        'field1' => $record->field1 ?? 'N/A',      // ← BUG: Accessing as object
        'field2' => $record->field2 ?? 'N/A',
        'field3' => $record->field3 ?? 'N/A',
    ];
}
```

**Problem:** API services return arrays, not objects. `$record->field` returns `null`.

---

## THE FIX

All 17 generators now use this pattern:

```php
foreach ($rawData['records'] ?? [] as $record) {
    $record = (array)$record;                      // ← FIX: Cast to array
    $rows[] = [
        'field1' => $record['field1'] ?? 'N/A',    // ← FIX: Access as array
        'field2' => $record['field2'] ?? 'N/A',
        'field3' => $record['field3'] ?? 'N/A',
    ];
}
```

**Solution:** Cast to array and use array syntax for property access.

---

## COMPLETE GENERATOR TEMPLATE

Here's the complete fixed pattern used for all 17 generators:

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
        foreach ($rawData['records'] ?? [] as $record) {
            $record = (array)$record;  // ← CRITICAL: Cast to array
            $rows[] = [
                'field1' => $record['field1'] ?? 'N/A',
                'field2' => $record['field2'] ?? 'N/A',
                'field3' => $record['field3'] ?? 'N/A',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM XXX - Title',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                // ← IMPORTANT: Include all required header fields
                'factory_name' => $branch['name'] ?? 'N/A',
                'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],  // Or calculated totals if needed
            'is_nil' => count($rows) === 0,
        ];
    }
}
```

---

## SPECIFIC EXAMPLES

### Example 1: Form2Generator

**Before:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $rows[] = [
        'employee_code' => $record->employee_code ?? 'N/A',
        'name' => $record->name ?? 'N/A',
        'designation' => $record->designation ?? 'N/A',
    ];
}
```

**After:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $record = (array)$record;
    $rows[] = [
        'employee_code' => $record['employee_code'] ?? 'N/A',
        'name' => $record['name'] ?? 'N/A',
        'designation' => $record['designation'] ?? 'N/A',
    ];
}
```

### Example 2: Form26Generator (with totals)

**Before:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $rows[] = [
        'incident_date' => $record->incident_date ?? null,
        'employee_name' => $record->employee_name ?? 'N/A',
        'severity' => $record->severity ?? 'N/A',
    ];
}
```

**After:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $record = (array)$record;
    $rows[] = [
        'incident_date' => $record['incident_date'] ?? null,
        'employee_name' => $record['employee_name'] ?? 'N/A',
        'severity' => $record['severity'] ?? 'N/A',
    ];
}
```

### Example 3: ShopsForm12Generator (with totals calculation)

**Before:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $rows[] = [
        'employee_code' => $record->employee_code ?? 'N/A',
        'basic_earned' => round($record->basic_earned ?? 0, 2),
        'gross_salary' => round($record->gross_salary ?? 0, 2),
    ];
}

$totals = $this->calculateTotals($rows, ['basic_earned', 'gross_salary']);
```

**After:**
```php
foreach ($rawData['records'] ?? [] as $record) {
    $record = (array)$record;
    $rows[] = [
        'employee_code' => $record['employee_code'] ?? 'N/A',
        'basic_earned' => round($record['basic_earned'] ?? 0, 2),
        'gross_salary' => round($record['gross_salary'] ?? 0, 2),
    ];
}

$totals = $this->calculateTotals($rows, ['basic_earned', 'gross_salary']);
```

---

## HEADER FIELDS ADDED

All generators now include these header fields:

```php
'header' => [
    'form_title' => 'FORM XXX - Title',
    'period' => $this->formatPeriod($month, $year),
    'branch' => $branch,
    'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
    'tenant_details' => $tenant,
    
    // ← NEW: Complete set of header fields
    'factory_name' => $branch['name'] ?? 'N/A',
    'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
    'owner_name' => $tenant['name'] ?? 'N/A',
    'address' => $branch['address'] ?? 'N/A',
    'place' => $branch['address'] ?? 'N/A',
    'district' => $branch['district'] ?? 'N/A',
]
```

This ensures templates have all required variables regardless of which fields they reference.

---

## VERIFICATION CHECKLIST

For each fixed generator, verify:

- [ ] Records are cast to array: `$record = (array)$record`
- [ ] Properties accessed as array: `$record['field']`
- [ ] All required header fields present
- [ ] Fallback values for missing data: `?? 'N/A'`
- [ ] Totals calculated if needed
- [ ] is_nil flag set correctly

---

## TESTING THE FIX

### Unit Test Example

```php
public function test_form_2_generator_handles_array_records()
{
    $rawData = [
        'records' => [
            ['employee_code' => 'E001', 'name' => 'John', 'designation' => 'Manager'],
        ],
        'meta' => ['month' => 1, 'year' => 2024],
        'tenant' => ['name' => 'Tenant', 'establishment_name' => 'Est'],
        'branch' => ['name' => 'Branch', 'address' => 'Address', 'district' => 'District'],
    ];
    
    $generator = new Form2Generator();
    $result = $generator->generate($rawData);
    
    // Verify rows are populated
    $this->assertNotEmpty($result['rows']);
    $this->assertEquals('John', $result['rows'][0]['name']);
    $this->assertEquals('E001', $result['rows'][0]['employee_code']);
    
    // Verify header fields
    $this->assertEquals('FORM 2 - Notice of Periods of Work', $result['header']['form_title']);
    $this->assertEquals('Branch', $result['header']['factory_name']);
}
```

### Manual Test

```bash
php artisan tinker
>>> $service = app(\App\Services\Compliance\FormApis\Form2ApiService::class)
>>> $data = $service->fetch(1, 1, 1, 2024)
>>> $generator = app(\App\Services\Compliance\FormGenerator\Form2Generator::class)
>>> $result = $generator->generate($data)
>>> $result['rows'][0]  // Should have real data, not 'N/A'
```

---

## SUMMARY

**Pattern:** Cast records to array, access properties using array syntax

**Applied to:** 17 generators

**Result:** All generators now correctly extract data from API responses

**Status:** ✅ Complete and ready for testing
