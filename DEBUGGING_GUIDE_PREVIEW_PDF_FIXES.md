# DEBUGGING GUIDE: Preview-to-PDF Fixes Verification

## Quick Verification Steps

### Step 1: Verify Orchestrator Fix
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');
>>> $result['status']
=> "success"
>>> strlen($result['result']['html']) > 100
=> true
```

### Step 2: Verify Generator Output Format
```bash
php artisan tinker
>>> $generator = app(\App\Services\Compliance\FormGenerator\Form2Generator::class);
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> $formData = $generator->generate($data);
>>> is_string($formData['header']['tenant'])
=> true
>>> isset($formData['header']['factory_name'])
=> true
```

### Step 3: Verify API Service Field Mappings
```bash
php artisan tinker
>>> $api = app(\App\Services\Compliance\FormApis\Form26ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> count($data['records']) > 0 ? isset($data['records'][0]['employee_name']) : true
=> true
```

---

## Detailed Debugging for Each Component

### ComplianceOrchestrator::executePreview()

**What to Check:**
1. Header fields are spread into viewData
2. All header fields available as top-level variables
3. View renders without undefined variable errors

**Debug Code:**
```php
// In ComplianceOrchestrator::executePreview()
$viewData = array_merge(
    $formData['header'] ?? [],
    [
        'form_title' => $formData['header']['form_title'] ?? $formCode,
        // ... other fields
    ]
);

// Verify all fields are present
dd($viewData);  // Should show all header fields + standard fields
```

**Expected Output:**
```
array:25 [
  "form_title" => "FORM 2 - Notice of Periods of Work"
  "period" => "January 2024"
  "branch" => array:4 [...]
  "tenant" => "Tenant Name"  // ← STRING
  "tenant_details" => array:5 [...]
  "factory_name" => "Branch Name"
  "place" => "Address"
  "district" => "District"
  // ... more fields
]
```

---

### Generator Output Format

**What to Check:**
1. `$header['tenant']` is always a string
2. `$header['tenant_details']` is always an array
3. All form-specific fields present in header
4. Rows array properly formatted

**Debug Code:**
```php
// In any generator's prepareData()
$result = [
    'header' => [
        'form_title' => 'FORM X - Title',
        'period' => $this->formatPeriod($month, $year),
        'branch' => $branch,
        'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
        'tenant_details' => $tenant,
        // Form-specific fields
    ],
    'rows' => $rows,
    'totals' => $totals,
    'is_nil' => count($rows) === 0,
];

// Verify format
dd($result);
```

**Expected Output:**
```
array:5 [
  "header" => array:8 [
    "form_title" => "FORM 2 - Notice of Periods of Work"
    "period" => "January 2024"
    "branch" => array:4 [...]
    "tenant" => "Tenant Name"  // ← STRING, not array
    "tenant_details" => array:5 [...]
    "factory_name" => "Branch Name"
    "place" => "Address"
    "district" => "District"
  ]
  "rows" => array:5 [...]
  "totals" => array:0 []
  "is_nil" => false
]
```

---

### API Service Field Mappings

**What to Check:**
1. All required fields are selected
2. Joins are correct
3. Computed fields are properly aliased
4. Null values handled correctly

**Debug Code for Form26ApiService:**
```php
// In Form26ApiService::fetch()
$rows = DB::table('incidents as i')
    ->leftJoin('workforce_employee as e', 'e.id', '=', 'i.employee_id')
    ->where('i.tenant_id', $tenantId)
    ->where('i.branch_id', $branchId)
    ->whereYear('i.incident_date', $year)
    ->whereMonth('i.incident_date', $month)
    ->select([
        'i.id',
        'i.incident_date',
        'i.description',
        'i.severity',
        'i.status',
        DB::raw("COALESCE(e.name, 'N/A') as employee_name"),
        DB::raw("'Workplace' as location"),
        DB::raw("i.severity as nature_of_injury"),
    ])
    ->orderBy('i.incident_date')
    ->get()
    ->map(fn($row) => (array)$row)
    ->toArray();

// Verify fields
dd($rows[0] ?? []);
```

**Expected Output:**
```
array:8 [
  "id" => 1
  "incident_date" => "2024-01-15"
  "description" => "Worker injury"
  "severity" => "High"
  "status" => "Reported"
  "employee_name" => "John Doe"  // ← From join
  "location" => "Workplace"      // ← Computed
  "nature_of_injury" => "High"   // ← Mapped from severity
]
```

---

## Form-by-Form Verification

### FORM_2 Verification
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');
>>> $result['status'] === 'success'
=> true
>>> strpos($result['result']['html'], 'FORM 2') !== false
=> true
>>> strpos($result['result']['html'], 'Notice of Periods') !== false
=> true
```

### FORM_26 Verification
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_26', 'preview');
>>> $result['status'] === 'success'
=> true
>>> $result['result']['rows_count'] >= 0
=> true
```

### SHOPS_FORM_12 Verification
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'SHOPS_FORM_12', 'preview');
>>> $result['status'] === 'success'
=> true
>>> isset($result['result']['html'])
=> true
```

---

## Common Issues and Solutions

### Issue 1: "View not found" Error
**Cause:** Template doesn't exist or path is wrong

**Solution:**
```bash
# Check if template exists
ls resources/views/compliance/forms/form_2.blade.php

# Verify FormTemplateRegistry mapping
php artisan tinker
>>> \App\Services\Compliance\Registry\FormTemplateRegistry::resolve('FORM_2')
=> "compliance.forms.form_2"

# Check if view exists
>>> View::exists('compliance.forms.form_2')
=> true
```

### Issue 2: "Undefined variable" in Template
**Cause:** Variable not passed from orchestrator

**Solution:**
```bash
# Check what variables are passed
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');

# If error occurs, check generator output
>>> $generator = app(\App\Services\Compliance\FormGenerator\Form2Generator::class);
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> $formData = $generator->generate($data);
>>> dd($formData['header']);  // Check if variable is in header
```

### Issue 3: Empty PDF Generated
**Cause:** HTML is empty or rendering failed

**Solution:**
```bash
# Check if HTML is generated
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');
>>> strlen($result['result']['html'])
=> 5000  // Should be > 100

# Check if rows are empty
>>> $result['result']['rows_count']
=> 0  // If 0, check if data exists in database
```

### Issue 4: Generator Returns Wrong Format
**Cause:** Generator not updated with new format

**Solution:**
```bash
# Check generator output
php artisan tinker
>>> $generator = app(\App\Services\Compliance\FormGenerator\Form2Generator::class);
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> $formData = $generator->generate($data);

# Verify format
>>> is_string($formData['header']['tenant'])
=> true  // Should be true

>>> isset($formData['header']['factory_name'])
=> true  // Should be true
```

---

## Batch Testing Script

```bash
#!/bin/bash

# Test all 17 fixed forms
php artisan tinker << 'EOF'
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
$forms = [
    'FORM_2', 'FORM_8', 'FORM_17', 'FORM_18', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
    'FORM_XIV', 'FORM_XIX',
    'SHOPS_FORM_VI', 'SHOPS_FORM_12', 'SHOPS_FORM_13', 'SHOPS_FORM_C', 'SHOPS_UNPAID', 'SHOPS_FINES',
    'ESI_FORM_12', 'EPF_INSPECTION'
];

$results = [];
foreach ($forms as $form) {
    try {
        $result = $orchestrator->execute(1, 1, 1, 2024, $form, 'preview');
        $results[$form] = $result['status'] === 'success' ? 'PASS' : 'FAIL';
    } catch (\Exception $e) {
        $results[$form] = 'ERROR: ' . $e->getMessage();
    }
}

foreach ($results as $form => $status) {
    echo "$form: $status\n";
}

$passed = count(array_filter($results, fn($s) => $s === 'PASS'));
echo "\nTotal: $passed/17 PASSED\n";
EOF
```

---

## Performance Verification

### Check Execution Time
```bash
php artisan tinker
>>> $start = microtime(true);
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');
>>> $time = (microtime(true) - $start) * 1000;
>>> echo "Execution time: {$time}ms";
```

**Expected:** < 500ms for preview, < 2000ms for PDF

### Check Memory Usage
```bash
php artisan tinker
>>> $start = memory_get_usage();
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_2', 'preview');
>>> $used = (memory_get_usage() - $start) / 1024 / 1024;
>>> echo "Memory used: {$used}MB";
```

**Expected:** < 10MB per form

---

## Logging and Monitoring

### Enable Debug Logging
```php
// In .env
APP_DEBUG=true
LOG_LEVEL=debug

// In config/logging.php
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],
],
```

### Monitor Execution Logs
```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Check for errors
grep -i "error\|exception" storage/logs/laravel.log

# Check execution logs table
php artisan tinker
>>> DB::table('compliance_execution_logs')
    ->where('form_code', 'FORM_2')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();
```

---

## Rollback Procedure (if needed)

All changes are backward compatible and additive. No rollback needed.

However, if issues occur:

1. **Revert Orchestrator:** Remove array_merge() call
2. **Revert Generators:** Restore original prepareData() methods
3. **Revert API Services:** Restore original select() statements

All changes are isolated and can be reverted independently.

---

## Success Criteria

✅ All 17 forms render preview without errors
✅ All 17 forms generate PDFs successfully
✅ No undefined variable errors in templates
✅ No database query errors
✅ Execution time < 500ms for preview
✅ Memory usage < 10MB per form
✅ All header fields available in templates
✅ Backward compatibility maintained

---

**Status:** Ready for verification and deployment
