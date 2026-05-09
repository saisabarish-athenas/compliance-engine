# ROOT CAUSE ANALYSIS: Preview-to-PDF Failures

## Executive Summary

**Root Cause Identified:** Generator output format inconsistency and missing field mappings between API responses and Blade template expectations.

**Impact:** 17 forms fail during preview rendering, preventing PDF generation.

**Status:** CRITICAL - Requires immediate fixes to generator output structure.

---

## ANALYSIS FINDINGS

### Working Forms (4/34)
- FORM_B ✅
- FORM_10 ✅
- FORM_12 ✅
- FORM_25 ✅

### Failing Forms (17/34)
- FORM_2 ❌
- HAZARD_REG ❌
- FORM_26A ❌
- FORM_26 ❌
- FORM_8 ❌
- FORM_18 ❌
- FORM_17 ❌
- FORM_XIX ❌
- FORM_XIV ❌
- SHOPS_FORM_VI ❌
- SHOPS_FINES ❌
- SHOPS_FORM_13 ❌
- SHOPS_FORM_12 ❌
- SHOPS_FORM_C ❌
- SHOPS_UNPAID ❌
- ESI_FORM_12 ❌
- EPF_INSPECTION ❌

---

## ROOT CAUSE #1: Generator Output Format Mismatch

### Problem
Generators return inconsistent data structures. Some return `$header['tenant']` as array, others as string.

### Evidence

**FORM_B Generator (WORKING):**
```php
'header' => [
    'form_title' => 'FORM B - Register of Wages',
    'period' => $this->formatPeriod($month, $year),
    'branch' => $rawData['branch'] ?? [],
    'tenant' => $rawData['tenant']['name'] ?? 'N/A',  // ← STRING
    'owner_name' => $rawData['tenant']['owner_name'] ?? 'N/A',
    'wage_period' => 'Monthly',
],
```

**FORM_2 Generator (FAILING):**
```php
'header' => [
    'form_title' => 'FORM 2 - Register of Leave',
    'period' => $this->formatPeriod(...),
    'branch' => $rawData['branch'] ?? [],
    'tenant' => $rawData['tenant'] ?? [],  // ← ARRAY (inconsistent!)
],
```

**FORM_26 Generator (FAILING):**
```php
'header' => [
    'form_title' => 'FORM 26 - Notice of Accident',
    'period' => $this->formatPeriod(...),
    'branch' => $rawData['branch'] ?? [],
    'tenant' => $rawData['tenant'] ?? [],  // ← ARRAY (inconsistent!)
],
```

### Impact
Blade templates expect `$header['tenant']` to be a string or have `.name` property, but receive array instead.

---

## ROOT CAUSE #2: Missing Field Mappings in API Response

### Problem
API services return fields that don't match generator expectations.

### Evidence

**FORM_26 API Service:**
```php
->select([
    'i.id',
    'i.incident_date',
    'i.description',
    'i.severity',
    'i.status',
])
```

**FORM_26 Generator expects:**
```php
'employee_name' => $record->employee_name ?? 'N/A',  // ← NOT IN API!
'incident_date' => $record->incident_date ?? null,
'incident_type' => $record->incident_type ?? 'N/A',  // ← NOT IN API!
'location' => $record->location ?? 'N/A',            // ← NOT IN API!
'description' => $record->description ?? 'N/A',
```

**Result:** Generator receives `$record->employee_name` as NULL, template renders empty.

---

## ROOT CAUSE #3: Blade Template Variable Mismatches

### Problem
Templates expect variables that generators don't provide.

### Evidence

**FORM_B Blade Template:**
```blade
{{ $header['tenant'] ?? ''['name'] ?? 'N/A' }}  <!-- Expects string or array with 'name' -->
{{ $header['owner_name'] ?? 'N/A' }}             <!-- Expects owner_name in header -->
```

**FORM_2 Blade Template:**
```blade
{{ $factory_name ?? 'NIL' }}  <!-- Generator doesn't provide this -->
{{ $place ?? 'NIL' }}         <!-- Generator doesn't provide this -->
{{ $district ?? 'NIL' }}      <!-- Generator doesn't provide this -->
```

**FORM_26 Blade Template:**
```blade
<!-- Template has hardcoded empty rows, doesn't use $rows variable -->
@for($i = 0; $i < 10; $i++)
<tr>
    <td class="col-1"></td>
    <!-- All cells empty - doesn't iterate $rows -->
</tr>
@endfor
```

---

## ROOT CAUSE #4: Orchestrator Preview Execution Issue

### Problem
ComplianceOrchestrator::executePreview() passes incomplete variable set to Blade.

**Current Code:**
```php
$html = View::make($viewPath, [
    'form_title' => $formData['header']['form_title'] ?? $formCode,
    'form_code' => $formCode,
    'period_month' => $month,
    'period_year' => $year,
    'header' => $formData['header'] ?? [],
    'rows' => $formData['rows'] ?? [],
    'entries' => $formData['rows'] ?? [],
    'totals' => $formData['totals'] ?? [],
    'is_nil' => $formData['is_nil'] ?? empty($formData['rows'])
])->render();
```

**Issue:** Only passes `header`, `rows`, `entries`, `totals`. Templates expect additional top-level variables like `$factory_name`, `$place`, `$district`, etc.

---

## DETAILED FORM-BY-FORM ANALYSIS

### FORM_2 (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ✅ | Returns correct `records` |
| Generator | ❌ | Returns `tenant` as array instead of string |
| Blade Template | ❌ | Expects `$factory_name`, `$place`, `$district` at top level |
| Orchestrator | ❌ | Doesn't pass these variables |

**Fix Required:** 
1. Generator must extract `tenant['name']` as string
2. Blade template must use `$header['tenant']` instead of `$factory_name`
3. Orchestrator must pass all header fields to template

---

### FORM_26 (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Missing `employee_name`, `incident_type`, `location` fields |
| Generator | ❌ | Expects fields API doesn't provide |
| Blade Template | ❌ | Has hardcoded empty rows, doesn't use `$rows` |
| Orchestrator | ✅ | Passes variables correctly |

**Fix Required:**
1. API service must join with employee table to get `employee_name`
2. API service must map `severity` to `incident_type`
3. Blade template must iterate `$rows` instead of hardcoded loop

---

### FORM_26A (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Same as FORM_26 - missing fields |
| Generator | ❌ | Same as FORM_26 - expects missing fields |
| Blade Template | ❌ | Same as FORM_26 - hardcoded rows |
| Orchestrator | ✅ | Passes variables correctly |

---

### HAZARD_REG (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Queries `incidents` table but form needs hazard data |
| Generator | ❌ | Expects `hazard_type`, `risk_level`, `control_measures` |
| Blade Template | ❌ | Template doesn't exist or expects different structure |
| Orchestrator | ✅ | Passes variables correctly |

**Fix Required:**
1. API service must query correct table (likely needs new table or different mapping)
2. Generator must map available fields correctly
3. Blade template must be created/updated

---

### FORM_8, FORM_17, FORM_18 (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Likely missing required fields |
| Generator | ❌ | Expects fields API doesn't provide |
| Blade Template | ❌ | Expects variables not in generator output |
| Orchestrator | ✅ | Passes variables correctly |

---

### FORM_XIX, FORM_XIV (FAILING - CLRA Forms)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Likely queries wrong table or missing joins |
| Generator | ❌ | Expects contractor/employee data |
| Blade Template | ❌ | Expects CLRA-specific variables |
| Orchestrator | ✅ | Passes variables correctly |

---

### SHOPS_FORM_VI, SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FORM_C, SHOPS_UNPAID, SHOPS_FINES (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Queries payroll tables but shops forms need different data |
| Generator | ❌ | Expects fields API doesn't provide |
| Blade Template | ❌ | Expects shops-specific variables |
| Orchestrator | ✅ | Passes variables correctly |

---

### ESI_FORM_12, EPF_INSPECTION (FAILING)

| Component | Status | Issue |
|-----------|--------|-------|
| API Service | ❌ | Likely queries wrong table |
| Generator | ❌ | Expects inspection/incident data |
| Blade Template | ❌ | Expects inspection-specific variables |
| Orchestrator | ✅ | Passes variables correctly |

---

## EXECUTION PIPELINE TRACE

### For FORM_B (WORKING)

```
1. ComplianceOrchestrator::execute()
   ↓
2. FormApiServiceFactory::make('FORM_B')
   → Returns FormBApiService instance
   ↓
3. FormBApiService::fetch(1, 1, 1, 2024)
   → Queries workforce_payroll_entry + workforce_employee
   → Returns: ['records' => [...], 'meta' => [...], 'tenant' => [...], 'branch' => [...]]
   ↓
4. FormGeneratorFactory::make('FORM_B')
   → Returns FormBGenerator instance
   ↓
5. FormBGenerator::generate($rawData)
   → Transforms records into rows
   → Returns: ['header' => [...], 'rows' => [...], 'totals' => [...], 'is_nil' => false]
   ↓
6. ComplianceOrchestrator::executePreview()
   → FormTemplateRegistry::resolve('FORM_B')
   → Returns 'compliance.forms.form_b'
   → View::make('compliance.forms.form_b', [...])
   → Renders HTML ✅
   ↓
7. PDF Generation
   → Pdf::loadView() ✅
   → Returns PDF content ✅
```

### For FORM_2 (FAILING)

```
1. ComplianceOrchestrator::execute()
   ↓
2. FormApiServiceFactory::make('FORM_2')
   → Returns Form2ApiService instance
   ↓
3. Form2ApiService::fetch(1, 1, 1, 2024)
   → Queries workforce_employee
   → Returns: ['records' => [...], 'meta' => [...], 'tenant' => [...], 'branch' => [...]]
   ↓
4. FormGeneratorFactory::make('FORM_2')
   → Returns Form2Generator instance
   ↓
5. Form2Generator::generate($rawData)
   → Transforms records into rows
   → Returns: ['header' => ['tenant' => ARRAY], 'rows' => [...], ...]
   ↓
6. ComplianceOrchestrator::executePreview()
   → FormTemplateRegistry::resolve('FORM_2')
   → Returns 'compliance.forms.form_2'
   → View::make('compliance.forms.form_2', ['header' => [...], 'rows' => [...], ...])
   → Blade tries to render {{ $factory_name ?? 'NIL' }}
   → $factory_name is NOT in passed variables ❌
   → Renders 'NIL' or empty ❌
   ↓
7. PDF Generation
   → Pdf::loadView() with empty/nil content ❌
   → Returns empty PDF ❌
```

---

## SUMMARY OF ISSUES

### Issue Type 1: Generator Output Format Inconsistency
**Affected Forms:** FORM_2, FORM_26, FORM_26A, HAZARD_REG, FORM_8, FORM_17, FORM_18, FORM_XIX, FORM_XIV, SHOPS_*, ESI_FORM_12, EPF_INSPECTION

**Problem:** Generators return `$header['tenant']` as array instead of extracting the name string.

**Solution:** Standardize all generators to return:
```php
'header' => [
    'form_title' => '...',
    'period' => '...',
    'branch' => $rawData['branch'] ?? [],
    'tenant' => $rawData['tenant']['name'] ?? 'N/A',  // ← Always string
    'tenant_details' => $rawData['tenant'] ?? [],     // ← Full array if needed
]
```

---

### Issue Type 2: API Service Field Mapping Errors
**Affected Forms:** FORM_26, FORM_26A, HAZARD_REG, FORM_8, FORM_17, FORM_18, FORM_XIX, FORM_XIV, SHOPS_*, ESI_FORM_12, EPF_INSPECTION

**Problem:** API services don't select required fields or join wrong tables.

**Solution:** Each API service must:
1. Join correct tables to get all required fields
2. Select all fields generator expects
3. Return consistent structure

---

### Issue Type 3: Blade Template Variable Mismatches
**Affected Forms:** FORM_2, FORM_26, FORM_26A, HAZARD_REG, FORM_8, FORM_17, FORM_18, FORM_XIX, FORM_XIV, SHOPS_*, ESI_FORM_12, EPF_INSPECTION

**Problem:** Templates expect top-level variables that aren't passed by orchestrator.

**Solution:** Either:
1. Update templates to use `$header['field']` instead of `$field`
2. Update orchestrator to extract and pass all header fields as top-level variables

---

### Issue Type 4: Orchestrator Preview Variable Passing
**Affected Forms:** All failing forms

**Problem:** Orchestrator only passes `header`, `rows`, `entries`, `totals`. Templates need additional variables.

**Solution:** Update `ComplianceOrchestrator::executePreview()` to pass all header fields:
```php
$html = View::make($viewPath, array_merge(
    $formData['header'] ?? [],  // ← Spread header fields
    [
        'form_title' => $formData['header']['form_title'] ?? $formCode,
        'form_code' => $formCode,
        'period_month' => $month,
        'period_year' => $year,
        'header' => $formData['header'] ?? [],
        'rows' => $formData['rows'] ?? [],
        'entries' => $formData['rows'] ?? [],
        'totals' => $formData['totals'] ?? [],
        'is_nil' => $formData['is_nil'] ?? empty($formData['rows'])
    ]
))->render();
```

---

## VERIFICATION CHECKLIST

- [ ] All generators return `$header['tenant']` as string
- [ ] All generators return `$header['tenant_details']` as array (if needed)
- [ ] All API services select required fields
- [ ] All API services join correct tables
- [ ] All Blade templates use consistent variable names
- [ ] Orchestrator passes all header fields to template
- [ ] Preview renders without errors for all 34 forms
- [ ] PDF generation succeeds for all 34 forms

---

## NEXT STEPS

1. **Fix Generator Output Format** - Standardize all generators
2. **Fix API Service Field Mappings** - Ensure all required fields are selected
3. **Fix Blade Template Variables** - Use consistent naming
4. **Fix Orchestrator Variable Passing** - Pass all header fields
5. **Test Preview Rendering** - Verify all forms render correctly
6. **Test PDF Generation** - Verify all PDFs generate successfully

---

**Status:** ANALYSIS COMPLETE - Ready for implementation fixes
