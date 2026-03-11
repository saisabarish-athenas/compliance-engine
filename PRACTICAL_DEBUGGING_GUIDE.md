# PRACTICAL DEBUGGING GUIDE - Compliance Pipeline Tracing

## Quick Start: Test Individual Components

### Test 1: Check Database Records Exist

```bash
php artisan tinker

# Check workforce_employee records
>>> DB::table('workforce_employee')
    ->where('tenant_id', 1)
    ->where('branch_id', 1)
    ->count();

# Check incidents records
>>> DB::table('incidents')
    ->where('tenant_id', 1)
    ->where('branch_id', 1)
    ->count();

# Check payroll entries
>>> DB::table('workforce_payroll_entry')->count();

# Check payroll cycles
>>> DB::table('workforce_payroll_cycle')
    ->whereDate('period_from', '2024-01-01')
    ->count();
```

**Expected:** All should return > 0

---

### Test 2: Test API Service Directly

```bash
php artisan tinker

# Test FORM_2 API
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> echo "Records: " . count($data['records'] ?? []);
>>> echo "Tenant: " . ($data['tenant']['name'] ?? 'MISSING');
>>> echo "Branch: " . ($data['branch']['name'] ?? 'MISSING');

# Test FORM_26 API
>>> $api = app(\App\Services\Compliance\FormApis\Form26ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> echo "Records: " . count($data['records'] ?? []);

# Test HAZARD_REG API
>>> $api = app(\App\Services\Compliance\FormApis\HazardRegApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> echo "Records: " . count($data['records'] ?? []);
```

**Expected:** Records > 0, Tenant and Branch populated

---

### Test 3: Test Generator Directly

```bash
php artisan tinker

# Get API data
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $rawData = $api->fetch(1, 1, 1, 2024);

# Test generator
>>> $gen = app(\App\Services\Compliance\FormGenerator\Form2Generator::class);
>>> $formData = $gen->generate($rawData);

# Check output
>>> echo "Rows: " . count($formData['rows'] ?? []);
>>> echo "Header keys: " . implode(', ', array_keys($formData['header'] ?? []));
>>> echo "Has factory_name: " . (isset($formData['header']['factory_name']) ? 'YES' : 'NO');
>>> echo "Has place: " . (isset($formData['header']['place']) ? 'YES' : 'NO');
>>> echo "Has district: " . (isset($formData['header']['district']) ? 'YES' : 'NO');
```

**Expected:** Rows > 0, All header fields present

---

### Test 4: Test Orchestrator Preview

```bash
php artisan tinker

# Test orchestrator
>>> $orch = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orch->execute(1, 1, 1, 2024, 'FORM_2', 'preview');

# Check result
>>> echo "Status: " . $result['status'];
>>> echo "Error: " . ($result['error'] ?? 'NONE');
>>> echo "HTML length: " . strlen($result['result']['html'] ?? '');
>>> echo "Rows count: " . $result['result']['rows_count'];
```

**Expected:** Status = success, HTML length > 1000, Rows count > 0

---

### Test 5: Test Template Rendering

```bash
php artisan tinker

# Get orchestrator result
>>> $orch = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orch->execute(1, 1, 1, 2024, 'FORM_2', 'preview');

# Check if HTML contains expected content
>>> $html = $result['result']['html'];
>>> echo "Contains 'FORM 2': " . (strpos($html, 'FORM 2') !== false ? 'YES' : 'NO');
>>> echo "Contains 'factory_name': " . (strpos($html, 'factory_name') !== false ? 'YES' : 'NO');
>>> echo "Contains 'place': " . (strpos($html, 'place') !== false ? 'YES' : 'NO');
```

**Expected:** All should be YES

---

### Test 6: Test Batch Processing

```bash
php artisan tinker

# Get batch
>>> $batch = \App\Models\ComplianceExecutionBatch::find(1);
>>> echo "Batch ID: " . $batch->id;
>>> echo "Form IDs: " . implode(', ', $batch->form_ids);

# Process batch
>>> $service = app(\App\Services\Compliance\ComplianceExecutionService::class);
>>> $results = $service->processBatch($batch->id);

# Check results
>>> foreach ($results as $formId => $result) {
    $form = \App\Models\ComplianceFormsMaster::find($formId);
    echo $form->form_code . ": " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
}
```

**Expected:** All forms should show SUCCESS

---

## Debug Logging Setup

### Step 1: Create Debug Log Channel

Edit `config/logging.php`:

```php
'channels' => [
    'compliance' => [
        'driver' => 'single',
        'path' => storage_path('logs/compliance.log'),
        'level' => 'debug',
    ],
],
```

### Step 2: Add Debug Logging to Orchestrator

```php
// In ComplianceOrchestrator::execute()

logger('compliance')->debug('PIPELINE_START', [
    'form_code' => $formCode,
    'mode' => $mode,
    'tenant_id' => $tenantId,
    'branch_id' => $branchId,
]);

// After API fetch
logger('compliance')->debug('API_RESPONSE', [
    'form_code' => $formCode,
    'records_count' => count($rawData['records'] ?? []),
    'has_tenant' => isset($rawData['tenant']),
    'has_branch' => isset($rawData['branch']),
]);

// After generator
logger('compliance')->debug('GENERATOR_OUTPUT', [
    'form_code' => $formCode,
    'rows_count' => count($formData['rows'] ?? []),
    'header_keys' => array_keys($formData['header'] ?? []),
]);

// Before template render
logger('compliance')->debug('TEMPLATE_RENDER', [
    'form_code' => $formCode,
    'template' => $viewPath,
    'view_data_keys' => array_keys($viewData),
]);
```

### Step 3: Monitor Logs

```bash
# Watch logs in real-time
tail -f storage/logs/compliance.log

# Filter for specific form
grep "FORM_2" storage/logs/compliance.log

# Filter for errors
grep "ERROR\|FAILED" storage/logs/compliance.log
```

---

## Batch Processing Trace

### Trace Batch Execution

```bash
php artisan tinker

# Get batch
>>> $batch = \App\Models\ComplianceExecutionBatch::find(1);

# Get execution logs
>>> $logs = DB::table('compliance_execution_logs')
    ->where('batch_id', $batch->id)
    ->get();

# Analyze results
>>> foreach ($logs as $log) {
    echo $log->form_code . ": " . $log->status . " (" . $log->execution_time . "ms)\n";
    if ($log->status === 'failed') {
        echo "  Error: " . $log->error_message . "\n";
    }
}
```

---

## Common Issues & Solutions

### Issue: "Missing tenant establishment name"

**Diagnosis:**
```bash
php artisan tinker
>>> $gen = app(\App\Services\Compliance\FormGenerator\Form2Generator::class);
>>> $rawData = ['records' => [], 'tenant' => null, 'branch' => null, 'meta' => []];
>>> $formData = $gen->generate($rawData);
>>> dd($formData['header']);
```

**Solution:** Ensure API returns tenant and branch data

---

### Issue: Empty rows in preview

**Diagnosis:**
```bash
php artisan tinker
>>> $api = app(\App\Services\Compliance\FormApis\Form2ApiService::class);
>>> $data = $api->fetch(1, 1, 1, 2024);
>>> echo "Records: " . count($data['records'] ?? []);
```

**Solution:** Check if database records exist for the period

---

### Issue: Template not found

**Diagnosis:**
```bash
php artisan tinker
>>> $template = \App\Services\Compliance\Registry\FormTemplateRegistry::resolve('FORM_2');
>>> echo "Template: " . $template;
>>> echo "Exists: " . (\Illuminate\Support\Facades\View::exists($template) ? 'YES' : 'NO');
```

**Solution:** Verify template exists at the resolved path

---

### Issue: Batch skips forms

**Diagnosis:**
```bash
php artisan tinker
>>> $batch = \App\Models\ComplianceExecutionBatch::find(1);
>>> echo "Form IDs: " . implode(', ', $batch->form_ids);
>>> $logs = DB::table('compliance_execution_logs')
    ->where('batch_id', $batch->id)
    ->pluck('form_code');
>>> echo "Processed: " . implode(', ', $logs->toArray());
```

**Solution:** Check if forms are in batch and if they're being processed

---

## Performance Monitoring

### Check Execution Times

```bash
php artisan tinker

>>> $logs = DB::table('compliance_execution_logs')
    ->where('batch_id', 1)
    ->get();

>>> echo "Total time: " . $logs->sum('execution_time') . "ms\n";
>>> echo "Average time: " . ($logs->sum('execution_time') / $logs->count()) . "ms\n";
>>> echo "Slowest: " . $logs->max('execution_time') . "ms\n";
>>> echo "Fastest: " . $logs->min('execution_time') . "ms\n";
```

---

## Summary

Use this guide to:
1. **Verify database records exist**
2. **Test API services individually**
3. **Test generators individually**
4. **Test orchestrator directly**
5. **Monitor batch processing**
6. **Enable debug logging**
7. **Trace execution flow**

This will help identify exactly where the pipeline breaks.
