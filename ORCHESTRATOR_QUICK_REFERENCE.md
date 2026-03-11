# Compliance Orchestrator - Quick Reference

## File Structure

```
app/Services/Compliance/
├── ComplianceOrchestrator.php          (Main orchestrator)
├── FormApis/
│   ├── BaseFormApiService.php          (Base class)
│   ├── FormApiServiceFactory.php       (Factory)
│   └── FormApiServices.php             (All API services)
├── FormGenerator/
│   ├── BaseFormGenerator.php
│   ├── FormGeneratorFactory.php
│   └── [Specific generators]
├── Forms/
│   ├── BaseFormService.php
│   └── [Form services]
└── [Other services]

resources/views/compliance/forms/
├── form_b.blade.php
├── form_10.blade.php
├── form_a.blade.php
└── [Other form templates]

storage/app/
├── generated_forms/{tenant_id}/{batch_id}/
├── compliance_inspection_packs/{tenant_id}/{batch_id}/
└── compliance_pdfs/
```

## API Service Pattern

```php
class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('table_name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('date_field', [$this->periodStart, $this->periodEnd])
            ->select([...])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}
```

## Execution Modes

| Mode | Purpose | Subscription | Returns |
|------|---------|--------------|---------|
| preview | HTML preview | FULL | HTML string |
| pdf | PDF content | FULL | PDF binary |
| batch | Store PDF | FULL | File path |
| inspection_pack | ZIP archive | FULL | ZIP path |

## Subscription Types

| Type | Access |
|------|--------|
| FULL | All modes |
| MINIMAL | Batch only |

## Common Queries

### Get Execution Logs
```php
$logs = $orchestrator->getExecutionLogs($batchId, 'FORM_B');
```

### Get Execution Stats
```php
$stats = $orchestrator->getExecutionStats($batchId);
// Returns: total_executions, successful, failed, total_execution_time, total_records, average_time, by_mode
```

## Error Codes

| Error | Cause | Solution |
|-------|-------|----------|
| Subscription access denied | Insufficient subscription | Upgrade to FULL |
| Form not found in master | Form code doesn't exist | Check form_code |
| No generator found | Generator not registered | Register in factory |
| View not found | Blade template missing | Create template |
| Invalid tenant_id | Tenant doesn't exist | Verify tenant_id |
| Invalid branch_id | Branch doesn't exist | Verify branch_id |

## Database Queries

### Check Execution Logs
```sql
SELECT * FROM compliance_execution_logs 
WHERE batch_id = ? AND form_code = ? 
ORDER BY created_at DESC;
```

### Get Execution Stats
```sql
SELECT 
    status,
    COUNT(*) as count,
    AVG(execution_time) as avg_time,
    SUM(records_generated) as total_records
FROM compliance_execution_logs
WHERE batch_id = ?
GROUP BY status;
```

## Testing

### Test Preview
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
assert($result['status'] === 'success');
assert(isset($result['result']['html']));
```

### Test PDF
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'pdf', 1);
assert($result['status'] === 'success');
assert(strlen($result['result']['content']) > 0);
```

### Test Batch
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'batch', 1);
assert($result['status'] === 'success');
assert(Storage::exists($result['result']['file_path']));
```

### Test Inspection Pack
```php
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'inspection_pack', 1);
assert($result['status'] === 'success');
assert(Storage::exists($result['result']['zip_path']));
```

## Debugging

### Enable Query Logging
```php
DB::enableQueryLog();
$result = $orchestrator->execute(...);
dd(DB::getQueryLog());
```

### Check Execution Logs
```php
$logs = DB::table('compliance_execution_logs')
    ->where('batch_id', $batchId)
    ->where('status', 'failed')
    ->get();
```

### Verify Subscription
```php
$tenant = Tenant::find($tenantId);
dd($tenant->subscription_type);
```

## Performance Tips

1. Use API services instead of aggregator when possible
2. Ensure database indexes on tenant_id, branch_id, form_code
3. Chunk large datasets (500 records per chunk)
4. Cache tenant/branch details during execution
5. Use async logging for high-volume operations

## Common Issues

### Issue: "Subscription access denied"
```php
// Check subscription
$tenant = Tenant::find($tenantId);
if ($tenant->subscription_type !== 'FULL') {
    // Upgrade tenant
    $tenant->update(['subscription_type' => 'FULL']);
}
```

### Issue: "Form not found"
```php
// Verify form exists
$form = ComplianceFormsMaster::where('form_code', 'FORM_B')->first();
if (!$form) {
    // Create form in master
    ComplianceFormsMaster::create(['form_code' => 'FORM_B', ...]);
}
```

### Issue: "No data returned"
```php
// Check if API service exists
$apiService = FormApiServiceFactory::make('FORM_B');
if (!$apiService) {
    // Use aggregator instead
    $data = $aggregator->aggregate('FORM_B', $tenantId, $branchId, $month, $year);
}
```

## Next Steps

1. Register all API services in FormApiServiceFactory
2. Create Blade templates for all forms
3. Test all execution modes
4. Monitor execution logs
5. Optimize slow queries
