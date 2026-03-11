# Developer Quick Reference Card

## System Overview

**Labour Compliance Automation System** - Full lifecycle automation with automatic audit, certification, and correction workflows.

## Key Services

### ComplianceExecutionService
```php
$service->processBatch($batchId)
// Returns: array of form generation results
// Triggers: Audit → Certification automatically
```

### ComplianceAuditService
```php
$service->auditBatch($batchId)
// Returns: ['status' => 'success', 'batch_score' => 85, 'batch_status' => 'partial', ...]

$service->reAuditForm($formCode, $tenantId, $branchId, $month, $year, $batchId)
// Returns: ['status' => 'success', 'new_score' => 90, ...]
```

### ComplianceCorrectionService
```php
$service->fixFormViolations($batchId, $formCode)
// Returns: ['status' => 'success'] or ['status' => 'requires_input', 'missing_fields' => [...]]

$service->fixWithUserInput($batchId, $formCode, $corrections)
// Returns: ['status' => 'success', 'form_score' => 85, ...]
```

### ComplianceCertificationService
```php
$service->certifyBatch($batchId)
// Returns: ['certified' => true, 'score' => 95, 'status' => 'Inspection Ready', ...]
```

### InspectionPackService
```php
$service->generateInspectionPack($batchId)
// Returns: string (path to ZIP file)
// Throws: Exception if no valid forms
```

## Database Tables

### compliance_audit_logs
```sql
batch_id, form_code, audit_score, status, violations, created_at, updated_at
```

### compliance_certification_logs
```sql
batch_id, form_code, certification_score, certified, violations, certified_at, created_at, updated_at
```

### compliance_batch_forms
```sql
batch_id, form_code, file_path, status, section, created_at
```

## API Endpoints

### Dashboard
```
GET /compliance/dashboard
```

### Batch Operations
```
POST /compliance/batches                          # Create batch
POST /compliance/batches/{id}/process             # Process batch
GET  /compliance/batches/{id}/audit-details       # Get audit details
GET  /compliance/batches/{id}/certification-details # Get certification details
POST /compliance/batches/{id}/re-audit            # Re-run audit
POST /compliance/batches/{id}/recertify           # Re-run certification
```

### Fix Violations
```
GET  /compliance/batches/{id}/violations          # Get violations
POST /compliance/batches/{id}/forms/{form}/fix    # Attempt auto-fix
POST /compliance/batches/{id}/forms/{form}/fix-submit # Submit user input
```

### Inspection Pack
```
GET /compliance/batches/{id}/inspection-pack      # Download ZIP
```

## Dashboard Batch Enrichment

```php
foreach ($batches as $batch) {
    // Display Status
    $batch->display_status = 'Completed'; // Pending, Processing, Completed, Failed, Partially Completed
    
    // Audit Data
    $batch->audit_score = 85;              // 0-100
    $batch->audit_status = 'Partial';      // Passed, Failed, Partial, Not Audited
    $batch->has_violations = true;         // Boolean
    
    // Certification Data
    $batch->certification_status = 'Inspection Ready'; // Inspection Ready, Review Required, Not Certified
    $batch->certification_score = 85;      // 0-100
}
```

## Status Codes

### Batch Status
- `pending` - Batch created, not processed
- `processing` - Forms being generated
- `completed` - All forms generated successfully
- `partially_completed` - Some forms generated
- `failed` - No forms generated

### Audit Status
- `passed` - All forms passed audit
- `failed` - All forms failed audit
- `partial` - Some forms passed, some failed
- `not_audited` - Audit not run yet

### Certification Status
- `Inspection Ready` - Score >= 90
- `Minor Issues` - Score 70-89
- `Correction Required` - Score < 70
- `Not Certified` - Certification not run yet

## Color Coding

### Audit Score
- Green: >= 90
- Yellow: 70-89
- Red: < 70

### Audit Status
- Green: Passed
- Yellow: Partial
- Red: Failed

### Certification Status
- Green: Inspection Ready
- Yellow: Review Required
- Gray: Not Certified

## File Paths

### Generated Forms
```
storage/app/compliance/generated/{batch_id}/{form_code}.pdf
```

### Manual Uploads
```
storage/app/compliance/manual_uploads/{filename}.pdf
```

### Inspection Pack
```
storage/app/temp/inspection_{batch_id}.zip
```

## Common Tasks

### Get Batch with Audit Data
```php
$batch = ComplianceExecutionBatch::find($batchId);
$auditLogs = ComplianceAuditLog::where('batch_id', $batchId)->get();
$batch->audit_score = round($auditLogs->avg('audit_score'));
```

### Get Batch with Certification Data
```php
$certLog = DB::table('compliance_certification_logs')
    ->where('batch_id', $batchId)
    ->where('form_code', 'BATCH_SUMMARY')
    ->first();
$batch->certification_status = $certLog->certified ? 'Inspection Ready' : 'Review Required';
```

### Check if Batch Has Violations
```php
$violations = ComplianceAuditLog::where('batch_id', $batchId)
    ->where('status', 'failed')
    ->exists();
```

### Check if Inspection Pack Available
```php
$certLog = DB::table('compliance_certification_logs')
    ->where('batch_id', $batchId)
    ->where('form_code', 'BATCH_SUMMARY')
    ->first();
$canDownload = $certLog && $certLog->certification_score >= 70;
```

## Error Handling

### Audit Errors
```php
try {
    $result = $auditService->auditBatch($batchId);
    if ($result['status'] !== 'success') {
        logger()->error('Audit failed', $result);
    }
} catch (\Exception $e) {
    logger()->error('Audit exception', ['error' => $e->getMessage()]);
}
```

### Certification Errors
```php
try {
    $result = $certService->certifyBatch($batchId);
    if (!$result['certified']) {
        logger()->warning('Batch not certified', ['score' => $result['score']]);
    }
} catch (\Exception $e) {
    logger()->error('Certification exception', ['error' => $e->getMessage()]);
}
```

### Fix Violations Errors
```php
try {
    $result = $correctionService->fixFormViolations($batchId, $formCode);
    if ($result['status'] === 'requires_input') {
        // Prompt user for missing fields
    } elseif ($result['status'] === 'error') {
        logger()->error('Fix failed', $result);
    }
} catch (\Exception $e) {
    logger()->error('Fix exception', ['error' => $e->getMessage()]);
}
```

## Testing Queries

### Check Batch Status
```sql
SELECT id, status, created_at FROM compliance_execution_batches WHERE id = ?;
```

### Check Audit Results
```sql
SELECT form_code, audit_score, status FROM compliance_audit_logs WHERE batch_id = ?;
```

### Check Certification Results
```sql
SELECT form_code, certification_score, certified FROM compliance_certification_logs WHERE batch_id = ?;
```

### Check Generated Forms
```sql
SELECT form_code, file_path, status FROM compliance_batch_forms WHERE batch_id = ?;
```

### Check for Violations
```sql
SELECT form_code, COUNT(*) as violation_count FROM compliance_audit_logs 
WHERE batch_id = ? AND status = 'failed' GROUP BY form_code;
```

## Performance Tips

1. **Cache audit results** - 1 hour TTL
2. **Cache certification results** - 1 hour TTL
3. **Index batch_id** in all audit/certification tables
4. **Use database transactions** for batch processing
5. **Implement queue jobs** for large batches
6. **Monitor query performance** - Log slow queries

## Debugging

### Enable Debug Logging
```php
// In .env
LOG_LEVEL=debug

// In code
logger()->debug('Message', ['context' => $data]);
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Test Batch Processing
```php
$batch = ComplianceExecutionBatch::find($batchId);
$results = app(ComplianceExecutionService::class)->processBatch($batchId);
dd($results);
```

### Test Audit
```php
$result = app(ComplianceAuditService::class)->auditBatch($batchId);
dd($result);
```

### Test Certification
```php
$result = app(ComplianceCertificationService::class)->certifyBatch($batchId);
dd($result);
```

## Important Notes

⚠️ **Do NOT modify:**
- Blade compliance forms
- ComplianceDataService builders
- Repository layer

✅ **Only modify:**
- ComplianceExecutionController
- ComplianceExecutionService
- ComplianceAuditService
- ComplianceCertificationService
- ComplianceCorrectionService

✅ **Can create:**
- New services (like InspectionPackService)
- New controller methods
- New routes
- New views

## Useful Commands

```bash
# Clear cache
php artisan cache:clear

# Clear config
php artisan config:clear

# Run migrations
php artisan migrate

# Run tests
php artisan test

# Check logs
tail -f storage/logs/laravel.log

# Tinker shell
php artisan tinker
```

## Resources

- COMPLIANCE_AUTOMATION_LIFECYCLE.md - System architecture
- DASHBOARD_VIEW_UPDATES.md - Frontend guide
- CONTROLLER_METHODS_EXAMPLES.md - Code examples
- IMPLEMENTATION_CHECKLIST.md - Deployment checklist
- EXECUTIVE_SUMMARY.md - Project overview

---

**Last Updated:** 2024
**Version:** 1.0
