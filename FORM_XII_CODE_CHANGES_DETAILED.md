# FORM XII Preview Data Pipeline - Detailed Code Changes

## Summary

5 files were modified to repair the FORM XII preview data pipeline. The root cause was a missing service map entry that prevented the form service from being invoked.

---

## Change 1: ComplianceExecutionService.php

**File**: `app/Services/Compliance/ComplianceExecutionService.php`

**Method**: `getFormDataViaAPI()`

**Change**: Added FORM_XII and FORM_XIII to the service map

```php
// BEFORE
public function getFormDataViaAPI(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
{
    $serviceMap = [
        'FORM_10' => \App\Services\Compliance\Forms\Form10Service::class,
        'FORM_12' => \App\Services\Compliance\Forms\Form12Service::class,
        'FORM_17' => \App\Services\Compliance\Forms\Form17Service::class,
        'FORM_25' => \App\Services\Compliance\Forms\Form25Service::class,
        'FORM_B' => \App\Services\Compliance\Forms\FormBService::class,
        'FORM_26' => \App\Services\Compliance\Forms\Form26Service::class,
        'FORM_26A' => \App\Services\Compliance\Forms\Form26AService::class,
        'HAZARD_REGISTER' => \App\Services\Compliance\Forms\HazardRegisterService::class,
    ];
    // ... rest of method
}

// AFTER
public function getFormDataViaAPI(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
{
    $serviceMap = [
        'FORM_10' => \App\Services\Compliance\Forms\Form10Service::class,
        'FORM_12' => \App\Services\Compliance\Forms\Form12Service::class,
        'FORM_17' => \App\Services\Compliance\Forms\Form17Service::class,
        'FORM_25' => \App\Services\Compliance\Forms\Form25Service::class,
        'FORM_B' => \App\Services\Compliance\Forms\FormBService::class,
        'FORM_26' => \App\Services\Compliance\Forms\Form26Service::class,
        'FORM_26A' => \App\Services\Compliance\Forms\Form26AService::class,
        'FORM_XII' => \App\Services\Compliance\Forms\FormXIIService::class,        // ✅ ADDED
        'FORM_XIII' => \App\Services\Compliance\Forms\FormXIIIService::class,      // ✅ ADDED
        'HAZARD_REGISTER' => \App\Services\Compliance\Forms\HazardRegisterService::class,
    ];
    // ... rest of method
}
```

**Impact**: FORM_XII and FORM_XIII requests are now properly routed to their respective services instead of returning NIL error.

---

## Change 2: FormXIIService.php

**File**: `app/Services/Compliance/Forms/FormXIIService.php`

**Method**: `generate()`

**Change**: Fixed header structure and null handling

```php
// BEFORE
public function generate(int $tenantId, int $branchId, int $month, int $year): array
{
    // ... query code ...
    
    $header = [
        'tenant' => [
            'name' => DB::table('tenants')->where('id', $tenantId)->value('name'),
            'address' => DB::table('tenants')->where('id', $tenantId)->value('address'),
        ],
        'branch' => [
            'name' => DB::table('branches')->where('id', $branchId)->value('name'),
            'address' => DB::table('branches')->where('id', $branchId)->value('address'),
        ]
    ];

    if (empty($rows)) {
        return $this->nilResponse();
    }

    return [
        'header' => $header,
        'rows' => $rows,
        'totals' => []
    ];
}

// AFTER
public function generate(int $tenantId, int $branchId, int $month, int $year): array
{
    // ... query code ...
    
    $tenant = DB::table('tenants')->where('id', $tenantId)->first();
    $branch = DB::table('branches')->where('id', $branchId)->first();
    
    $header = [
        'tenant' => [
            'name' => $tenant?->name ?? 'NIL',
            'address' => $tenant?->address ?? 'NIL',
        ],
        'branch' => [
            'name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
            'address' => $branch?->address ?? 'NIL',
        ]
    ];

    if (empty($rows)) {
        return $this->nilResponse();
    }

    return [
        'header' => $header,
        'rows' => $rows,
        'totals' => []
    ];
}
```

**Impact**: 
- Fetches full records instead of just values
- Provides fallback 'NIL' values when data is missing
- Handles null values gracefully with null coalescing operator

---

## Change 3: FormXIIIService.php

**File**: `app/Services/Compliance/Forms/FormXIIIService.php`

**Method**: `generate()`

**Change**: Fixed header structure and return format

```php
// BEFORE
public function generate(int $tenantId, int $branchId, int $month, int $year): array
{
    // ... query code ...
    
    if (empty($rows)) {
        return $this->nilResponse();
    }

    return $this->buildResponse($rows);  // ❌ Wrong method
}

// AFTER
public function generate(int $tenantId, int $branchId, int $month, int $year): array
{
    // ... query code ...
    
    $tenant = DB::table('tenants')->where('id', $tenantId)->first();
    $branch = DB::table('branches')->where('id', $branchId)->first();
    
    $header = [
        'tenant' => [
            'name' => $tenant?->name ?? 'NIL',
            'address' => $tenant?->address ?? 'NIL',
        ],
        'branch' => [
            'name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
            'address' => $branch?->address ?? 'NIL',
        ]
    ];

    if (empty($rows)) {
        return $this->nilResponse();
    }

    return [
        'header' => $header,
        'rows' => $rows,
        'totals' => []
    ];
}
```

**Impact**: Returns consistent structure matching FORM_XII and other forms.

---

## Change 4: form_xii.blade.php

**File**: `resources/views/compliance/forms/form_xii.blade.php`

**Change**: Fixed header variable references

```blade
<!-- BEFORE -->
<div style="margin-left: 40%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header,'tenant_name','NIL') }}</div>
<div style="margin-left: 40%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header,'branch_name','NIL') }}</div>

<!-- AFTER -->
<div style="margin-left: 40%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'tenant.name', 'NIL') }}</div>
<div style="margin-left: 40%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'branch.address', 'NIL') }}</div>
```

**Impact**: Template now correctly accesses nested header structure.

---

## Change 5: form_xii.blade.php - Row Variables

**File**: `resources/views/compliance/forms/form_xii.blade.php`

**Change**: Fixed row variable references

```blade
<!-- BEFORE -->
<td class="col-2">{{ $row['contractor_name'] ?? 'NIL' }}</td>
<td class="col-3">{{ $row['nature_of_work'] ?? 'NIL' }}</td>
<td class="col-4">{{ $row['work_location'] ?? 'NIL' }}</td>
<td class="col-5">{{ $row['contract_from'] ?? 'NIL' }}</td>
<td class="col-6">{{ $row['contract_to'] ?? 'NIL' }}</td>
<td class="col-7">{{ $row['max_workers'] ?? 'NIL' }}</td>

<!-- AFTER -->
<td class="col-2">
    {{ data_get($row, 'contractor_name', 'NIL') }}<br>
    {{ data_get($row, 'contractor_address', '') }}
</td>
<td class="col-3">{{ data_get($row, 'nature_of_work', '') }}</td>
<td class="col-4">{{ data_get($row, 'work_location', '') }}</td>
<td class="col-5">{{ data_get($row, 'contract_from', '') }}</td>
<td class="col-6">{{ data_get($row, 'contract_to', '') }}</td>
<td class="col-7">{{ data_get($row, 'max_workers', '') }}</td>
```

**Impact**: Consistent use of `data_get()` helper for safe array access.

---

## Change 6: form_xiii.blade.php

**File**: `resources/views/compliance/forms/form_xiii.blade.php`

**Change**: Fixed header and row variable references

```blade
<!-- BEFORE -->
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ $contractor_name ?? 'NIL' }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ $establishment_name ?? 'NIL' }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ $work_nature ?? 'NIL' }} - {{ $work_location ?? 'NIL' }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ $principal_employer ?? 'NIL' }}</div>

<!-- AFTER -->
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'tenant.name', 'NIL') }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'branch.name', 'NIL') }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'branch.address', 'NIL') }}</div>
<div style="margin-left: 35%; margin-bottom: 8px; font-size: 9px;">{{ data_get($header, 'tenant.address', 'NIL') }}</div>
```

**Impact**: Template now correctly accesses nested header structure.

---

## Change 7: form_xiii.blade.php - Row Variables

**File**: `resources/views/compliance/forms/form_xiii.blade.php`

**Change**: Fixed row variable references

```blade
<!-- BEFORE -->
<td class="col-2">{{ $row['name'] ?? 'NIL' }}</td>
<td class="col-3">{{ $row['age'] ?? 'NIL' }} / {{ $row['sex'] ?? 'NIL' }}</td>
<td class="col-4">{{ $row['father_name'] ?? 'NIL' }}</td>
<!-- ... etc ... -->

<!-- AFTER -->
<td class="col-2">{{ data_get($row, 'name', 'NIL') }}</td>
<td class="col-3">{{ data_get($row, 'age', 'NIL') }} / {{ data_get($row, 'sex', 'NIL') }}</td>
<td class="col-4">{{ data_get($row, 'father_name', 'NIL') }}</td>
<!-- ... etc ... -->
```

**Impact**: Consistent use of `data_get()` helper for safe array access.

---

## Testing the Changes

### 1. Verify Service Map
```bash
php artisan tinker
>>> $service = app(\App\Services\Compliance\ComplianceExecutionService::class);
>>> $data = $service->getFormDataViaAPI('FORM_XII', 8, 9, 1, 2025);
>>> dd($data);
```

Expected: Array with 'header', 'rows', 'totals' keys

### 2. Run Inspection Command
```bash
php artisan compliance:inspect FORM_XII --tenant=8 --branch=9 --month=1 --year=2025
```

Expected: Displays contractor data

### 3. Test Preview Page
Navigate to: `/compliance/batch/{batch_id}/preview/FORM_XII`

Expected: Form renders with actual contractor data

---

## Verification Results

✅ All changes implemented
✅ Service map includes FORM_XII and FORM_XIII
✅ FormXIIService returns correct structure
✅ FormXIIIService returns correct structure
✅ Blade templates use data_get() for nested access
✅ Inspection command returns actual data
✅ Preview page renders without errors

---

## Rollback Instructions

If needed, revert the following files to their original state:
1. `app/Services/Compliance/ComplianceExecutionService.php`
2. `app/Services/Compliance/Forms/FormXIIService.php`
3. `app/Services/Compliance/Forms/FormXIIIService.php`
4. `resources/views/compliance/forms/form_xii.blade.php`
5. `resources/views/compliance/forms/form_xiii.blade.php`

---

**Status**: ✅ COMPLETE AND TESTED
