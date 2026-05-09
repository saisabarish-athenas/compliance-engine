# COMPLIANCE PIPELINE - QUICK REFERENCE

## QUICK START

### Test Pipeline
```bash
php artisan compliance:verify-pipeline
```

### Generate Forms
```bash
php artisan compliance:generate-pack
```

### Test Individual Form
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'preview');
>>> $result['status']
=> "success"
```

---

## ORCHESTRATOR API

### Execute Preview
```php
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);

$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview'
);

// Returns:
// [
//     'status' => 'success',
//     'result' => [
//         'html' => '...',
//         'is_nil' => false,
//         'rows_count' => 10
//     ]
// ]
```

### Execute PDF
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'pdf'
);

// Returns:
// [
//     'status' => 'success',
//     'result' => [
//         'content' => '...',  // PDF binary
//         'size' => 12345,
//         'mime_type' => 'application/pdf'
//     ]
// ]
```

### Execute Batch
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'batch',
    batchId: 1
);

// Returns:
// [
//     'status' => 'success',
//     'result' => [
//         'file_path' => 'generated_forms/1/1/FORM_B.pdf',
//         'file_size' => 12345,
//         'stored' => true
//     ]
// ]
```

### Execute Inspection Pack
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'inspection_pack',
    batchId: 1
);

// Returns:
// [
//     'status' => 'success',
//     'result' => [
//         'zip_path' => 'compliance_inspection_packs/1/1/inspection_pack_1_123456.zip',
//         'zip_size' => 50000,
//         'file_count' => 1,
//         'created' => true
//     ]
// ]
```

---

## GENERATOR API

### Generate Form Data
```php
$generator = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class)->make('FORM_B');

$formData = $generator->generate([
    'records' => [...],
    'meta' => [
        'tenant_id' => 1,
        'branch_id' => 1,
        'month' => 1,
        'year' => 2024
    ],
    'tenant' => [...],
    'branch' => [...]
]);

// Returns:
// [
//     'header' => [...],
//     'rows' => [...],
//     'totals' => [...],
//     'is_nil' => false
// ]
```

### Generate PDF
```php
$pdfContent = $generator->generatePdf($formData);

// Returns: PDF binary content
```

---

## API SERVICE API

### Fetch Form Data
```php
$apiService = app(\App\Services\Compliance\FormApis\FormApiServiceFactory::class)->make('FORM_B');

$data = $apiService->fetch(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024
);

// Returns:
// [
//     'records' => [...],
//     'meta' => [
//         'tenant_id' => 1,
//         'branch_id' => 1,
//         'month' => 1,
//         'year' => 2024
//     ],
//     'tenant' => [...],
//     'branch' => [...],
//     'period' => 'January 2024'
// ]
```

---

## FORM CODES

### CLRA Forms (10)
- FORM_XII
- FORM_XIII
- FORM_XIV
- FORM_XVI
- FORM_XVII
- FORM_XIX
- FORM_XX
- FORM_XXI
- FORM_XXII
- FORM_XXIII

### Labour Welfare Forms (4)
- FORM_A
- FORM_C
- FORM_D
- FORM_D_ER

### Social Security Forms (3)
- FORM_11
- ESI_FORM_12
- EPF_INSPECTION

### Factories Act Forms (11)
- FORM_B
- FORM_2
- FORM_8
- FORM_10
- FORM_12
- FORM_17
- FORM_18
- FORM_25
- FORM_26
- FORM_26A
- HAZARD_REG

### Shops & Establishment Forms (6)
- SHOPS_FORM_12
- SHOPS_FORM_13
- SHOPS_FORM_C
- SHOPS_FORM_VI
- SHOPS_UNPAID
- SHOPS_FINES

---

## COMMON TASKS

### Render Form as HTML
```php
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
$result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'preview');
$html = $result['result']['html'];
```

### Generate PDF
```php
$result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'pdf');
$pdfContent = $result['result']['content'];
file_put_contents('form_b.pdf', $pdfContent);
```

### Store PDF in Batch
```php
$result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'batch', 1);
$filePath = $result['result']['file_path'];
```

### Create Inspection Pack
```php
$result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'inspection_pack', 1);
$zipPath = $result['result']['zip_path'];
```

### Get Execution Logs
```php
$logs = $orchestrator->getExecutionLogs(batchId: 1);
```

### Get Execution Statistics
```php
$stats = $orchestrator->getExecutionStats(batchId: 1);
// Returns: total_executions, successful, failed, total_execution_time, etc.
```

---

## ERROR HANDLING

### Check Status
```php
$result = $orchestrator->execute(...);

if ($result['status'] === 'success') {
    // Success
} else {
    // Error
    $error = $result['error'];
}
```

### Common Errors
```
"Tenant {id} not found"
"Branch {id} not found for tenant {id}"
"Form {code} not found in master"
"No generator found for {code}"
"View not found for {code}"
"PDF generation returned empty content"
"Subscription access denied"
```

---

## DEBUGGING

### Enable Query Logging
```php
DB::enableQueryLog();
$result = $orchestrator->execute(...);
dd(DB::getQueryLog());
```

### Check Execution Logs
```bash
php artisan tinker
>>> DB::table('compliance_execution_logs')->latest(10)->get()
```

### Verify Form Data
```php
$apiService = FormApiServiceFactory::make('FORM_B');
$data = $apiService->fetch(1, 1, 1, 2024);
dd($data);
```

### Test Generator
```php
$generator = FormGeneratorFactory::make('FORM_B');
$formData = $generator->generate($data);
dd($formData);
```

---

## PERFORMANCE TIPS

### Cache API Responses
```php
$data = Cache::remember("form_data_{$formCode}_{$tenantId}_{$branchId}_{$month}_{$year}", 3600, function() use ($apiService, ...) {
    return $apiService->fetch(...);
});
```

### Batch Process Forms
```php
foreach ($forms as $form) {
    GenerateFormPdfJob::dispatch($form->form_code, $tenantId, $branchId, $month, $year);
}
```

### Monitor Performance
```php
$start = microtime(true);
$result = $orchestrator->execute(...);
$duration = (microtime(true) - $start) * 1000;
logger()->info("Form generation took {$duration}ms");
```

---

## TESTING

### Unit Test
```php
public function test_form_b_preview()
{
    $orchestrator = app(ComplianceOrchestrator::class);
    $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'preview');
    
    $this->assertEquals('success', $result['status']);
    $this->assertNotEmpty($result['result']['html']);
}
```

### Integration Test
```php
public function test_complete_pipeline()
{
    $orchestrator = app(ComplianceOrchestrator::class);
    
    // Preview
    $preview = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'preview');
    $this->assertEquals('success', $preview['status']);
    
    // PDF
    $pdf = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'pdf');
    $this->assertEquals('success', $pdf['status']);
    
    // Batch
    $batch = $orchestrator->execute(1, 1, 1, 2024, 'FORM_B', 'batch', 1);
    $this->assertEquals('success', $batch['status']);
}
```

---

## TROUBLESHOOTING

### Preview Returns Empty HTML
1. Check Blade template exists
2. Verify generator returns correct structure
3. Check for template rendering errors in logs

### PDF Generation Fails
1. Verify Blade template is valid
2. Check PDF library is installed
3. Review error message in logs

### Batch Processing Slow
1. Enable query caching
2. Implement async processing
3. Add database indexes

### Multi-Tenant Data Leakage
1. Verify tenant_id in all queries
2. Check orchestrator validation
3. Review execution logs

---

## USEFUL COMMANDS

```bash
# Verify pipeline
php artisan compliance:verify-pipeline

# Generate pack
php artisan compliance:generate-pack

# Clear cache
php artisan cache:clear

# View logs
tail -f storage/logs/laravel.log

# Run tests
php artisan test

# Tinker
php artisan tinker
```

---

## DOCUMENTATION

- **PIPELINE_DEBUG_ANALYSIS.md** - Root cause analysis
- **PIPELINE_REPAIR_REPORT.md** - Detailed repairs
- **IMPLEMENTATION_GUIDE.md** - Deployment guide
- **EXECUTIVE_SUMMARY.md** - High-level overview

---

## SUPPORT

For issues:
1. Run `php artisan compliance:verify-pipeline`
2. Check `storage/logs/laravel.log`
3. Review documentation files
4. Contact support team

---

**Last Updated**: 2024
**Status**: Production Ready ✅

