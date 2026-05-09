# FORM PREVIEW PIPELINE - QUICK REFERENCE

## How It Works

### 1. User Opens Form Preview
```
GET /compliance/batch/{batch}/preview/{form}
```

### 2. Controller Checks Subscription
```php
$subscription = $user->tenant->subscription_type; // 'FULL' or 'MINIMAL'
```

### 3. FULL Subscription: Fetch Real Data
```php
$dataService = app(\App\Compliance\ComplianceDataService::class);
$data = $dataService->buildFormData(
    $form,           // Form code (e.g., 'FORM_B')
    $tenantId,       // Tenant ID
    $branchId,       // Branch ID
    $month,          // Month (1-12)
    $year            // Year (2024)
);
```

### 4. Data Service: Build Form Data
```php
// 1. Get builder class from registry
$builderClass = FormRegistry::getBuilder($formCode);

// 2. Instantiate builder with repositories
$builder = new $builderClass(
    $employeeRepo,
    $payrollRepo,
    $attendanceRepo,
    // ... other repos
);

// 3. Build data from database
$data = $builder->build($tenantId, $branchId, $month, $year);

// 4. Normalize data structure
$data = $this->normalizeData($data);
```

### 5. Normalize Data
```php
// Ensure consistent structure
$data = [
    'rows' => [...],        // Array of records
    'entries' => [...],     // Same as rows (bidirectional)
    'totals' => [...],      // Summary totals
    'period' => 'M/Y',      // Month/Year
];
```

### 6. Pass to Blade Template
```php
$viewPath = "compliance.forms.{$form}";
return view($viewPath, $data);
```

### 7. Blade Template Renders Data
```blade
@forelse($rows ?? $entries ?? [] as $index => $row)
    <tr>
        <td>{{ $row['field_name'] ?? '' }}</td>
    </tr>
@empty
    <!-- Empty rows -->
@endforelse
```

---

## Key Components

### ComplianceDataService
**File**: `app/Compliance/ComplianceDataService.php`

**Methods**:
- `buildFormData($formCode, $tenantId, $branchId, $month, $year)` - Main method
- `renderForm($formCode, $tenantId, $branchId, $month, $year)` - Render to HTML
- `normalizeData($data)` - Normalize data structure

### FormRegistry
**File**: `app/Compliance/Registry/FormRegistry.php`

**Methods**:
- `getBuilder($formCode)` - Get builder class
- `getTemplate($formCode)` - Get template path
- `isRegistered($formCode)` - Check if form exists
- `all()` - Get all registered forms

### Builders
**Location**: `app/Compliance/Builders/`

**Pattern**:
```php
class FormNameBuilder extends BaseBuilder {
    protected function getData(): array {
        // Query database via repositories
        // Return ['rows' => [...], 'totals' => [...]]
    }
}
```

### Repositories
**Location**: `app/Compliance/Repositories/`

**Examples**:
- `EmployeeRepository` - Employee data
- `PayrollRepository` - Payroll data
- `AttendanceRepository` - Attendance data
- `ContractorRepository` - Contractor data

---

## Data Structure

### Input to Builder
```php
$tenantId = 1;      // Tenant ID
$branchId = 5;      // Branch ID
$month = 3;         // March
$year = 2024;       // 2024
```

### Output from Builder
```php
[
    'rows' => [
        [
            'employee_name' => 'John Doe',
            'basic_earned' => 15000,
            'da_earned' => 3000,
            // ... more fields
        ],
        // ... more rows
    ],
    'entries' => [...],  // Same as rows
    'totals' => [
        'basic_earned' => 450000,
        'da_earned' => 90000,
        // ... totals
    ],
    'period' => '3/2024',
]
```

### NIL Dataset
```php
[
    'status' => 'NIL',
    'rows' => [],
    'entries' => [],
    'totals' => [],
]
```

---

## Subscription Logic

### FULL Subscription
- Fetch real database data
- Display all records
- Show actual totals
- Available for all forms

### MINIMAL Subscription
- Show empty preview
- Display message: "Preview data limited to FULL subscription users"
- No database queries
- Upgrade prompt

---

## Adding a New Form

### 1. Create Builder
```php
// app/Compliance/Builders/MyFormBuilder.php
class MyFormBuilder extends BaseBuilder {
    protected function getData(): array {
        $entries = $this->employeeRepo->getByBranchAndPeriod(
            $this->tenantId,
            $this->branchId,
            $this->month,
            $this->year
        );
        
        return [
            'rows' => $entries->map(fn($e) => [...])->toArray(),
            'entries' => [...],
            'totals' => [...],
        ];
    }
}
```

### 2. Register Form
```php
// app/Compliance/Registry/FormRegistry.php
'MY_FORM' => [
    'builder' => \App\Compliance\Builders\MyFormBuilder::class,
    'template' => 'compliance.forms.my_form',
],
```

### 3. Create Template
```blade
<!-- resources/views/compliance/forms/my_form.blade.php -->
@forelse($rows ?? $entries ?? [] as $row)
    <tr>
        <td>{{ $row['field'] ?? '' }}</td>
    </tr>
@empty
@endforelse
```

---

## Debugging

### Enable Logging
```php
// In previewForm()
Log::info('Compliance Preview Data', [
    'form' => $form,
    'batch_id' => $batch,
    'subscription' => $subscription,
    'has_data' => !isset($data['status']) || $data['status'] !== 'NIL',
    'rows_count' => count($data['rows'] ?? []),
]);
```

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep "Compliance Preview"
```

### Test Manually
```php
$dataService = app(\App\Compliance\ComplianceDataService::class);
$data = $dataService->buildFormData('FORM_B', 1, 5, 3, 2024);
dd($data);
```

---

## Common Issues

### Issue: No data displays
**Solution**:
1. Check subscription type: `$user->tenant->subscription_type`
2. Check database has data for period
3. Check repositories return data
4. Check logs for errors

### Issue: Template error
**Solution**:
1. Verify @forelse syntax
2. Check variable names match builder output
3. Ensure fallback array provided
4. Check template path in registry

### Issue: Undefined variable
**Solution**:
1. Use safe syntax: `$row['field'] ?? ''`
2. Use @forelse with fallback
3. Check normalizeData() output

---

## Performance Tips

1. **Cache Results**: Cache builder output for same period
2. **Limit Rows**: Paginate large datasets
3. **Optimize Queries**: Use eager loading in repositories
4. **Index Database**: Index tenant_id, branch_id, period columns

---

## Testing

### Unit Test
```php
public function test_form_preview_displays_data() {
    $user = User::factory()->create(['subscription_type' => 'FULL']);
    $batch = ComplianceExecutionBatch::factory()->create();
    
    $response = $this->actingAs($user)
        ->get("/compliance/batch/{$batch->id}/preview/FORM_B");
    
    $response->assertStatus(200);
    $response->assertViewHas('rows');
}
```

### Integration Test
```php
public function test_all_38_forms_render() {
    $forms = FormRegistry::all();
    
    foreach ($forms as $code => $config) {
        $response = $this->preview($code);
        $this->assertStatus(200);
    }
}
```

---

## Reference

- **FormRegistry**: `app/Compliance/Registry/FormRegistry.php`
- **ComplianceDataService**: `app/Compliance/ComplianceDataService.php`
- **BaseBuilder**: `app/Compliance/Builders/BaseBuilder.php`
- **Controller**: `app/Http/Controllers/ComplianceExecutionController.php`
- **Routes**: `routes/compliance.php`

---

**Last Updated**: 2024
**Status**: PRODUCTION READY ✅
