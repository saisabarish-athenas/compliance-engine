# Refactored Architecture - Quick Reference

## Execution Flow (ENFORCED)

```
HTTP Request
    ↓
Controller
    ├─ Validate Request
    ├─ Get Tenant/Branch/Period
    └─ Call Orchestrator
    ↓
ComplianceOrchestrator.execute()
    ├─ Validate Subscription (FULL required)
    ├─ Validate Inputs
    ├─ Run Validation Pipeline
    ├─ Fetch Data via API Service
    ├─ Execute Generator
    ├─ Log Execution
    └─ Return Result
    ↓
Controller Returns Response
    ├─ Preview: Render Blade
    ├─ PDF: Download PDF
    ├─ Batch: Store PDF
    └─ Inspection Pack: Download ZIP
```

## Controller Responsibilities

### ComplianceExecutionController
- `previewForm()`: Preview form via orchestrator
- `refreshFormData()`: Refresh data via orchestrator
- `processBatch()`: Process batch via orchestrator
- `downloadInspectionPack()`: Download ZIP via orchestrator

### CompliancePreviewController
- `preview()`: Preview form via orchestrator

### ComplianceOrchestratorController
- `run()`: Execute orchestrator directly
- `logs()`: Get execution logs
- `stats()`: Get execution statistics

## Orchestrator Methods

### execute()
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview',  // preview, pdf, batch, inspection_pack
    batchId: 1
);
```

**Returns**:
```php
[
    'status' => 'success|failed',
    'mode' => 'preview|pdf|batch|inspection_pack',
    'form_code' => 'FORM_B',
    'execution_time' => 1234,  // milliseconds
    'records_generated' => 10,
    'result' => [
        'html' => '...',  // for preview
        'content' => '...',  // for pdf
        'file_path' => '...',  // for batch
        'zip_path' => '...',  // for inspection_pack
    ],
    'error' => '...'  // if failed
]
```

### getExecutionLogs()
```php
$logs = $orchestrator->getExecutionLogs($batchId, 'FORM_B');
```

### getExecutionStats()
```php
$stats = $orchestrator->getExecutionStats($batchId);
```

## API Services

### Location
`app/Services/Compliance/FormApis/`

### Pattern
```php
class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        // Fetch data from database
        // Return structured data
    }
}
```

### Registration
```php
// FormApiServiceFactory.php
protected static array $apiServices = [
    'FORM_XXX' => FormXXXApiService::class,
];
```

## Generator Output Structure

### All Generators Return
```php
[
    'header' => [
        'form_title' => 'Form Title',
        'period' => 'March 2024',
        'tenant' => [...],
        'branch' => [...],
    ],
    'rows' => [
        ['employee_code' => '...', 'name' => '...', ...],
        ...
    ],
    'totals' => [
        'field1' => 100,
        'field2' => 200,
        ...
    ],
    'is_nil' => false,
]
```

## Multi-Tenant Safety

### All Queries Must Include
```php
->where('tenant_id', $tenantId)
->where('branch_id', $branchId)
```

### Orchestrator Enforces
- Tenant validation
- Branch validation
- Data isolation
- No cross-tenant access

## Subscription Validation

### FULL Subscription
- Can access all modes
- Can preview forms
- Can generate PDFs
- Can download inspection packs

### MINIMAL Subscription
- Can only access batch mode
- Cannot preview
- Cannot download inspection packs

### Enforcement
- Validated in orchestrator
- Checked before execution
- Logged in execution logs

## Execution Logging

### Table: compliance_execution_logs
```sql
CREATE TABLE compliance_execution_logs (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    batch_id BIGINT NOT NULL,
    form_code VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'success', 'failed', 'preview'),
    execution_time INT,
    records_generated INT DEFAULT 0,
    error_message TEXT,
    execution_mode VARCHAR(50) DEFAULT 'batch',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Query Logs
```php
$logs = DB::table('compliance_execution_logs')
    ->where('tenant_id', $tenantId)
    ->where('batch_id', $batchId)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Blade Template Variables

### All Templates Receive
```php
[
    'form_title' => 'Form Title',
    'form_code' => 'FORM_B',
    'header' => [...],
    'rows' => [...],
    'totals' => [...],
    'is_nil' => false,
    'period_month' => 3,
    'period_year' => 2024,
    'batch_id' => 1,
    'tenant_id' => 1,
    'branch_id' => 1,
]
```

## Common Tasks

### Preview Form
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
return view('compliance.forms.form_b', $result['result']);
```

### Generate PDF
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'pdf', 1);
return response($result['result']['content'], 200)
    ->header('Content-Type', 'application/pdf');
```

### Batch Processing
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'batch', 1);
// PDF stored at: storage/app/{$result['result']['file_path']}
```

### Inspection Pack
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'inspection_pack', 1);
// ZIP stored at: storage/app/{$result['result']['zip_path']}
```

## Validation Command

```bash
php artisan compliance:validate-refactoring
```

**Checks**:
- No duplicate aggregators
- No direct generator calls
- No direct aggregator calls
- Execution logs table exists
- Multi-tenant isolation
- ComplianceOrchestrator exists
- API services exist
- Blade templates exist

## Troubleshooting

### Issue: "Subscription access denied"
```php
$tenant = Tenant::find($tenantId);
// Check: $tenant->subscription_type === 'FULL'
```

### Issue: "Form not found"
```php
$form = ComplianceFormsMaster::where('form_code', 'FORM_B')->first();
// Verify form exists in master
```

### Issue: "No generator found"
```php
$generator = FormGeneratorFactory::make('FORM_B');
// Verify generator is registered
```

### Issue: "View not found"
```php
view()->exists('compliance.forms.form_b');
// Verify blade template exists
```

## Performance Tips

1. **Use API Services**: Faster than aggregator
2. **Cache Results**: Cache frequently accessed data
3. **Chunk Large Datasets**: Process in 500-record chunks
4. **Monitor Logs**: Check execution_time in logs
5. **Optimize Queries**: Add indexes on tenant_id, branch_id

## Security Checklist

✅ Subscription validation enforced
✅ Tenant isolation enforced
✅ Branch filtering enforced
✅ No cross-tenant data access
✅ Execution logging enabled
✅ Error messages sanitized
✅ Input validation enforced

## Deployment Checklist

✅ Database migration run
✅ Caches cleared
✅ Validation command passed
✅ Forms preview correctly
✅ PDFs generate successfully
✅ Inspection packs download correctly
✅ Execution logs recorded
✅ Multi-tenant isolation verified

---

**Architecture**: Orchestrator-Based
**Status**: ✅ PRODUCTION READY
**Last Updated**: 2024-03-20
