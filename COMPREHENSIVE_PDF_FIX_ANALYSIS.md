# Comprehensive PDF Generation Fix Analysis

## Root Cause Identified

The orchestrator's `executePreview()` method (line 127-138) uses `array_merge()` which flattens `$formData['header']` into the root level:

```php
$viewData = array_merge(
    $formData['header'] ?? [],  // ← FLATTENS header into root
    [
        'header' => $formData['header'] ?? [],
        'rows' => $formData['rows'] ?? [],
        ...
    ]
);
```

This causes:
1. Header fields become root-level variables
2. Blade templates expecting `$header['field']` fail
3. Blade templates expecting `$field` work

## Solution

ALL generators must return data in this EXACT structure:

```php
return [
    'header' => [
        'form_title' => '...',
        'period' => '...',
        'tenant' => '...',
        'branch' => '...',
        // All header fields here
    ],
    'rows' => [...],
    'totals' => [...],
    'is_nil' => bool,
    'entries' => [...],  // For backward compatibility
    'slips' => [...],    // For backward compatibility
    'data' => [...]      // For backward compatibility
];
```

## Failing Forms Analysis

### FormXIV - Employment Card
**API**: Returns `records` ✓
**Generator**: Returns `cards` instead of `rows` ✗
**Blade**: Uses `@forelse($cards as $card)` ✓
**Fix**: Generator must return `rows` with `cards` as alias

### FormXVII - Register of Wages
**API**: Returns `records` ✓
**Generator**: Returns `rows` ✓
**Blade**: Uses `@if(isset($rows) && count($rows) > 0)` ✓
**Fix**: Already correct, but needs `entries` alias

### FormXIX - Wage Slip
**API**: Returns `records` ✓
**Generator**: Returns `slips` and `rows` ✓
**Blade**: Uses `@foreach($slips as $slip)` ✓
**Fix**: Already correct

### FormXXI - Register of Fines
**API**: Returns `records` ✓
**Generator**: Returns flat structure (contractor_name, work_nature, etc.) ✗
**Blade**: Uses `$contractor_name`, `$work_nature` ✓
**Fix**: Generator must wrap in `header` array

### FormXXII - Register of Advances
**API**: Returns `records` ✓
**Generator**: Returns flat structure ✗
**Blade**: Uses `$contractor_name`, `$month_year` ✓
**Fix**: Generator must wrap in `header` array

### FormXXIII - Register of Overtime
**API**: Returns `records` ✓
**Generator**: Returns flat structure ✗
**Blade**: Uses `$contractor_name`, `$month_year` ✓
**Fix**: Generator must wrap in `header` array

### FormD - Register of Attendance
**API**: Returns `records` ✓
**Generator**: Returns `header` with `establishment_name`, `owner_name`, `month_name`, `year` ✓
**Blade**: Uses `$establishment_name`, `$owner_name`, `$month_name`, `$year` ✓
**Fix**: Already correct

### Form12 - Register of Adult Workers
**API**: Returns `records` ✓
**Generator**: Returns `rows` and `entries` ✓
**Blade**: Uses `@forelse($rows ?? $entries ?? [] as $row)` ✓
**Fix**: Already correct

### ShopsForm13 - Leave Book
**API**: Returns `records` ✓
**Generator**: Returns `employees` dict instead of `rows` ✗
**Blade**: Uses `@forelse($employees as $employee)` ✓
**Fix**: Generator must return `rows` array

### ShopsFormC - Bonus Register
**API**: Returns `records` ✓
**Generator**: Returns `rows` ✓
**Blade**: Uses `@foreach($rows as $index => $row)` ✓
**Fix**: Already correct

### ShopsUnpaid - Fines and Unpaid Accumulations
**API**: Returns `records` (quarterly dict) ✓
**Generator**: Returns `data` dict ✓
**Blade**: Uses `$data['fines_realisation']['march']` ✓
**Fix**: Already correct

### ShopsFines - Register of Fines
**API**: Returns `records` ✓
**Generator**: Returns `rows` ✓
**Blade**: Uses `@foreach($rows as $index => $row)` ✓
**Fix**: Already correct

## Implementation Strategy

1. **Fix Orchestrator** - Ensure proper data passing
2. **Fix Generators** - Ensure consistent return structure
3. **Fix Blades** - Ensure consistent variable usage
4. **Test** - Verify all 12 forms generate PDFs

## Key Principle

**Generator Output Contract:**
```
{
  header: { all header/meta fields },
  rows: [ all data rows ],
  totals: { calculated totals },
  is_nil: boolean,
  entries: [ alias for rows ],
  slips: [ alias for rows if applicable ],
  data: { custom data structure if needed }
}
```

**Blade Variable Access:**
- Root level: `$header`, `$rows`, `$totals`, `$is_nil`, `$entries`, `$slips`, `$data`
- Header fields: `$header['field_name']`
- Row fields: `$row['field_name']`
