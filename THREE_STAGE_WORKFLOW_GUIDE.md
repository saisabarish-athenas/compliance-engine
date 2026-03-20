# Three-Stage Batch Workflow - Implementation Guide

## Overview

The system now correctly implements a three-stage workflow for compliance batch processing:

1. **Stage 1: Batch Creation** - Create batch and attach forms (no generation)
2. **Stage 2: Preview** - User previews individual forms (no database updates)
3. **Stage 3: Processing** - Generate all forms and update database

---

## Stage 1: Batch Creation

### Controller Method
```
ComplianceExecutionController::createBatch(Request $request)
```

### Flow
1. User selects Month + Year on dashboard
2. User clicks "Create Batch"
3. Controller validates input
4. Controller calls `BatchOrchestrator::createBatch()`

### BatchOrchestrator::createBatch()
```php
public function createBatch(int $tenantId, int $month, int $year): ComplianceExecutionBatch
```

**Responsibilities:**
- Validate branch exists
- Detect applicable forms using `FrequencyEngine::getApplicableForms($month)`
- Create `ComplianceExecutionBatch` record with status = `pending`
- Attach forms to `compliance_batch_forms` with:
  - `status` = `pending`
  - `file_path` = `null` (will be set in Stage 3)

**Frequency Rules:**
- `monthly` → every month
- `quarterly` → months 3, 6, 9, 12
- `half-yearly` → months 6, 12
- `yearly` → month 12

### Database Changes
```
compliance_execution_batches
├── id
├── tenant_id
├── branch_id
├── period_month
├── period_year
├── status = 'pending'
└── created_at

compliance_batch_forms
├── id
├── tenant_id
├── batch_id
├── form_code
├── status = 'pending'
├── file_path = NULL
└── created_at
```

### Result
- Batch created with status = `pending`
- Forms attached with status = `pending`
- Dashboard displays form list with preview buttons
- User can now preview forms

---

## Stage 2: Preview

### Controller Method
```
ComplianceExecutionController::previewForm(int $batch, string $form)
```

### Flow
1. User clicks preview button for a form
2. Controller calls `ComplianceOrchestrator::execute()` with mode = `preview`
3. Orchestrator fetches data and renders blade template
4. HTML returned to user (no database updates)

### ComplianceOrchestrator::execute() with mode='preview'
```php
public function execute(
    int $tenantId,
    int $branchId,
    int $month,
    int $year,
    string $formCode,
    string $mode = 'preview',
    ?int $batchId = null
): array
```

**When mode = 'preview':**
1. Validates inputs
2. Runs validation pipeline
3. Fetches data via API service or aggregator
4. Generates form data
5. Calls `executePreview()` which:
   - Resolves blade template
   - Renders HTML
   - Returns HTML (no database updates)

### Key Points
- **NO database updates** during preview
- User can preview multiple forms
- User can preview same form multiple times
- Preview data is fresh (not cached)

### Result
- HTML preview displayed to user
- User can review form data
- User can click "Proceed" to generate all forms

---

## Stage 3: Processing

### Controller Method
```
ComplianceExecutionController::processBatch(int $id)
```

### Flow
1. User clicks "Proceed" button
2. Controller validates batch status = `pending`
3. Controller calls `ComplianceExecutionService::processBatch()`
4. Service generates all forms and updates database

### ComplianceExecutionService::processBatch()
```php
public function processBatch(int $batchId): array
```

**Responsibilities:**
1. Fetch batch and validate
2. Validate payroll (FULL subscription only)
3. Update batch status = `processing`
4. For each pending form in `compliance_batch_forms`:
   - Call `ComplianceOrchestrator::execute()` with mode = `batch`
   - Orchestrator generates PDF
   - Update `compliance_batch_forms`:
     - `file_path` = path to generated PDF
     - `status` = `generated`
   - Log generation in `compliance_generation_logs`
5. Run audit automatically
6. Run certification automatically
7. Update batch status = `completed` or `partially_completed`

### ComplianceOrchestrator::execute() with mode='batch'
```php
public function executeBatch(
    string $formCode,
    array $formData,
    int $tenantId,
    int $branchId,
    ?int $batchId,
    int $month,
    int $year
): array
```

**Responsibilities:**
1. Generate PDF from form data
2. Store PDF in `storage/app/generated_forms/{tenantId}/{batchId}/{formCode}.pdf`
3. Return file path

### Database Changes
```
compliance_batch_forms
├── status: pending → generated
├── file_path: NULL → storage/app/generated_forms/...
└── updated_at

compliance_generation_logs
├── batch_id
├── form_code
├── status = 'success'
├── file_path
├── checksum_hash
└── created_at

compliance_execution_batches
├── status: pending → processing → completed
└── processed_at
```

### Result
- All forms generated
- File paths stored in database
- Batch status = `completed`
- Audit and certification run automatically
- User can download inspection pack

---

## Multi-Tenant Safety

All stages enforce multi-tenant isolation:

### Stage 1
```php
$branch = Branch::where('tenant_id', $tenantId)->first();
```

### Stage 2
```php
if ($batchModel->tenant_id !== Auth::user()->tenant_id) {
    abort(403, 'Unauthorized');
}
```

### Stage 3
```php
$batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
    ->where('id', $id)
    ->firstOrFail();
```

---

## Workflow Diagram

```
Dashboard
    ↓
User selects Month + Year
    ↓
User clicks "Create Batch" (Stage 1)
    ↓
BatchOrchestrator::createBatch()
├── Detect applicable forms
├── Create batch record
└── Attach forms with status=pending
    ↓
Dashboard displays form list with preview buttons
    ↓
User clicks "Preview Form" (Stage 2)
    ↓
ComplianceOrchestrator::execute(mode='preview')
├── Fetch data
├── Generate form data
└── Render HTML (no DB update)
    ↓
User reviews preview
    ↓
User clicks "Proceed" (Stage 3)
    ↓
ComplianceExecutionService::processBatch()
├── For each pending form:
│   ├── ComplianceOrchestrator::execute(mode='batch')
│   ├── Generate PDF
│   └── Update file_path and status
├── Run audit
└── Run certification
    ↓
Batch status = completed
    ↓
User can download inspection pack
```

---

## Key Differences from Previous Implementation

| Aspect | Previous | Current |
|--------|----------|---------|
| Batch Creation | Generated forms | Only creates batch and attaches forms |
| Preview | Not available | Available without DB updates |
| Proceed Button | Not available | Triggers form generation |
| Form Status | Immediate success | pending → generated → success |
| File Path | Set during creation | Set during processing |
| User Control | Automatic | Three explicit stages |

---

## Testing the Workflow

### Test Stage 1: Batch Creation
```bash
# Create batch for January
POST /compliance/batch
{
    "period_month": 1,
    "period_year": 2024
}

# Verify batch created
SELECT * FROM compliance_execution_batches WHERE id = 1;
# Expected: status = 'pending'

# Verify forms attached
SELECT * FROM compliance_batch_forms WHERE batch_id = 1;
# Expected: status = 'pending', file_path = NULL
```

### Test Stage 2: Preview
```bash
# Preview Form B
GET /compliance/batch/1/preview/FORM_B

# Verify no database updates
SELECT * FROM compliance_batch_forms WHERE batch_id = 1 AND form_code = 'FORM_B';
# Expected: status = 'pending', file_path = NULL (unchanged)
```

### Test Stage 3: Processing
```bash
# Process batch
POST /compliance/batch/1/process

# Verify batch status updated
SELECT * FROM compliance_execution_batches WHERE id = 1;
# Expected: status = 'completed'

# Verify forms generated
SELECT * FROM compliance_batch_forms WHERE batch_id = 1;
# Expected: status = 'generated', file_path = 'storage/app/generated_forms/...'
```

---

## Troubleshooting

### Issue: Forms not appearing in batch
**Solution:** Check `compliance_forms_master.frequency` is set correctly

### Issue: Preview not working
**Solution:** Verify blade template exists in `resources/views/compliance/forms/`

### Issue: Processing fails
**Solution:** Check logs in `storage/logs/laravel.log`

### Issue: File path not updating
**Solution:** Verify storage directory permissions: `chmod 755 storage/app/generated_forms`

---

## Summary

The three-stage workflow provides:
- ✅ User control over batch processing
- ✅ Preview capability before generation
- ✅ Automatic form detection by frequency
- ✅ Multi-tenant safety at all stages
- ✅ Clean separation of concerns
- ✅ Audit and certification automation
