# Three-Stage Workflow - Quick Reference

## The Problem (Before)
```
User creates batch → Forms generated immediately → No preview → No proceed button
```

## The Solution (After)
```
Stage 1: Create batch (attach forms, no generation)
    ↓
Stage 2: Preview forms (render HTML, no DB updates)
    ↓
Stage 3: Process batch (generate PDFs, update database)
```

---

## Stage 1: Batch Creation

**Controller:** `ComplianceExecutionController::createBatch()`

**Service:** `BatchOrchestrator::createBatch()`

**What happens:**
1. User selects Month + Year
2. System detects applicable forms using frequency rules
3. System creates batch record with status = `pending`
4. System attaches forms with status = `pending` and file_path = `null`

**Database:**
```
compliance_execution_batches
├── status = 'pending'

compliance_batch_forms
├── status = 'pending'
├── file_path = NULL
```

**Result:** Dashboard shows form list with preview buttons

---

## Stage 2: Preview

**Controller:** `ComplianceExecutionController::previewForm()`

**Service:** `ComplianceOrchestrator::execute(mode='preview')`

**What happens:**
1. User clicks preview button
2. System fetches data and renders blade template
3. HTML returned to user
4. **NO database updates**

**Database:** No changes

**Result:** User sees form preview in browser

---

## Stage 3: Processing

**Controller:** `ComplianceExecutionController::processBatch()`

**Service:** `ComplianceExecutionService::processBatch()`

**What happens:**
1. User clicks "Proceed" button
2. For each pending form:
   - Generate PDF
   - Update file_path in database
   - Update status = `generated`
3. Run audit automatically
4. Run certification automatically
5. Update batch status = `completed`

**Database:**
```
compliance_batch_forms
├── status: pending → generated
├── file_path: NULL → storage/app/generated_forms/...

compliance_execution_batches
├── status: pending → processing → completed
```

**Result:** User can download inspection pack

---

## Frequency Rules

Forms are detected automatically based on frequency:

| Frequency | Months |
|-----------|--------|
| monthly | 1-12 (every month) |
| quarterly | 3, 6, 9, 12 |
| half-yearly | 6, 12 |
| yearly | 12 |

---

## Code Examples

### Stage 1: Create Batch
```php
// Controller
$batchOrchestrator = app(BatchOrchestrator::class);
$batch = $batchOrchestrator->createBatch(
    $tenantId,
    $month,
    $year
);
// Result: Batch created with forms attached (status=pending)
```

### Stage 2: Preview Form
```php
// Controller
$orchestrator = app(ComplianceOrchestrator::class);
$result = $orchestrator->execute(
    $tenantId,
    $branchId,
    $month,
    $year,
    $formCode,
    'preview',  // Mode = preview
    $batchId
);
// Result: HTML returned (no DB updates)
```

### Stage 3: Process Batch
```php
// Controller
$results = $this->executionService->processBatch($batchId);
// Result: Forms generated, file_path updated, audit/cert run
```

---

## Multi-Tenant Safety

All stages enforce tenant isolation:

```php
// Stage 1: Validate tenant has branch
$branch = Branch::where('tenant_id', $tenantId)->first();

// Stage 2: Verify user owns batch
if ($batchModel->tenant_id !== Auth::user()->tenant_id) {
    abort(403);
}

// Stage 3: Query only user's batches
$batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
    ->where('id', $id)
    ->firstOrFail();
```

---

## Testing

### Test Stage 1
```bash
# Create batch
curl -X POST http://localhost/compliance/batch \
  -d "period_month=1&period_year=2024"

# Verify
SELECT * FROM compliance_batch_forms WHERE batch_id = 1;
# Expected: status = 'pending', file_path = NULL
```

### Test Stage 2
```bash
# Preview form
curl http://localhost/compliance/batch/1/preview/FORM_B

# Verify no DB changes
SELECT * FROM compliance_batch_forms WHERE batch_id = 1;
# Expected: status = 'pending', file_path = NULL (unchanged)
```

### Test Stage 3
```bash
# Process batch
curl -X POST http://localhost/compliance/batch/1/process

# Verify
SELECT * FROM compliance_batch_forms WHERE batch_id = 1;
# Expected: status = 'generated', file_path = 'storage/app/generated_forms/...'
```

---

## Common Issues

### Issue: Forms not appearing
**Check:** `compliance_forms_master.frequency` is set
```sql
SELECT form_code, frequency FROM compliance_forms_master;
```

### Issue: Preview not working
**Check:** Blade template exists
```bash
ls resources/views/compliance/forms/
```

### Issue: Processing fails
**Check:** Logs
```bash
tail -f storage/logs/laravel.log
```

### Issue: File path not updating
**Check:** Storage permissions
```bash
chmod 755 storage/app/generated_forms
```

---

## Key Points

✅ **Stage 1:** Only creates batch and attaches forms (no generation)
✅ **Stage 2:** Only renders preview (no database updates)
✅ **Stage 3:** Generates forms and updates database
✅ **Frequency:** Automatic form detection by frequency rules
✅ **Multi-tenant:** Tenant isolation at all stages
✅ **Audit:** Runs automatically after generation
✅ **Certification:** Runs automatically after audit

---

## Files Modified

1. `app/Services/Compliance/BatchOrchestrator.php` - Stage 1
2. `app/Services/Compliance/ComplianceExecutionService.php` - Stage 3
3. `app/Http/Controllers/ComplianceExecutionController.php` - All stages

---

## Deployment

```bash
# 1. Backup
cp -r app/Services/Compliance app/Services/Compliance.backup

# 2. Deploy
cp BatchOrchestrator.php app/Services/Compliance/
cp ComplianceExecutionService.php app/Services/Compliance/
cp ComplianceExecutionController.php app/Http/Controllers/

# 3. Test
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1

# 4. Verify workflow
# - Create batch
# - Preview form
# - Process batch
```

---

## Rollback

```bash
cp -r app/Services/Compliance.backup/* app/Services/Compliance/
php artisan cache:clear
```

---

## Summary

The three-stage workflow provides:
- User control over batch processing
- Preview capability before generation
- Automatic form detection
- Multi-tenant safety
- Clean separation of concerns
- Audit and certification automation
