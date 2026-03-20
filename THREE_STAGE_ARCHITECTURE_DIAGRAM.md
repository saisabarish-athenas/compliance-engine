# Three-Stage Workflow - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          COMPLIANCE ENGINE                                  │
│                      Three-Stage Batch Workflow                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│ STAGE 1: BATCH CREATION                                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Dashboard                                                                   │
│      ↓                                                                       │
│  User selects Month + Year                                                  │
│      ↓                                                                       │
│  ComplianceExecutionController::createBatch()                               │
│      ↓                                                                       │
│  BatchOrchestrator::createBatch()                                           │
│      ├─ Validate branch exists                                              │
│      ├─ FrequencyEngine::getApplicableForms($month)                         │
│      ├─ Create ComplianceExecutionBatch (status=pending)                    │
│      └─ Attach forms to compliance_batch_forms (status=pending, file_path=NULL)
│      ↓                                                                       │
│  Database Updates:                                                           │
│      ├─ compliance_execution_batches                                        │
│      │   └─ status = 'pending'                                              │
│      └─ compliance_batch_forms                                              │
│          ├─ status = 'pending'                                              │
│          └─ file_path = NULL                                                │
│      ↓                                                                       │
│  Result: Dashboard displays form list with preview buttons                  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│ STAGE 2: PREVIEW                                                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  User clicks "Preview Form" button                                          │
│      ↓                                                                       │
│  ComplianceExecutionController::previewForm()                               │
│      ├─ Verify user owns batch (tenant_id check)                            │
│      └─ Call ComplianceOrchestrator::execute(mode='preview')                │
│      ↓                                                                       │
│  ComplianceOrchestrator::execute(mode='preview')                            │
│      ├─ Validate inputs                                                     │
│      ├─ Run validation pipeline                                             │
│      ├─ FormApiServiceFactory::make($formCode)                              │
│      │   └─ Fetch data from API or aggregator                               │
│      ├─ FormGeneratorFactory::make($formCode)                               │
│      │   └─ Generate form data                                              │
│      └─ executePreview()                                                    │
│          ├─ Resolve blade template                                          │
│          ├─ Render HTML                                                     │
│          └─ Return HTML (NO DATABASE UPDATES)                               │
│      ↓                                                                       │
│  Database: NO CHANGES                                                       │
│      ├─ compliance_batch_forms: status still = 'pending'                    │
│      └─ file_path still = NULL                                              │
│      ↓                                                                       │
│  Result: HTML preview displayed to user                                     │
│          User can preview multiple times                                    │
│          User can preview different forms                                   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│ STAGE 3: PROCESSING                                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  User clicks \"Proceed\" button                                              │
│      ↓                                                                       │
│  ComplianceExecutionController::processBatch()                              │
│      ├─ Verify batch status = 'pending'                                     │
│      └─ Call ComplianceExecutionService::processBatch()                     │
│      ↓                                                                       │
│  ComplianceExecutionService::processBatch()                                 │
│      ├─ Fetch batch and validate                                            │
│      ├─ Validate payroll (FULL subscription only)                           │
│      ├─ Update batch status = 'processing'                                  │
│      ├─ For each pending form in compliance_batch_forms:                    │
│      │   ├─ ComplianceOrchestrator::execute(mode='batch')                   │
│      │   │   ├─ Fetch data                                                  │
│      │   │   ├─ Generate form data                                          │
│      │   │   ├─ Generate PDF                                                │
│      │   │   └─ Store in storage/app/generated_forms/{tenantId}/{batchId}/  │
│      │   ├─ Update compliance_batch_forms:                                  │
│      │   │   ├─ file_path = 'storage/app/generated_forms/...'               │
│      │   │   └─ status = 'generated'                                        │
│      │   └─ Log in compliance_generation_logs                               │
│      ├─ Run ComplianceAuditService::auditBatch()                            │
│      ├─ Run ComplianceCertificationService::certifyBatch()                  │
│      └─ Update batch status = 'completed'                                   │
│      ↓                                                                       │
│  Database Updates:                                                           │
│      ├─ compliance_batch_forms                                              │
│      │   ├─ status: pending → generated                                     │
│      │   └─ file_path: NULL → storage/app/generated_forms/...               │
│      ├─ compliance_generation_logs                                          │
│      │   ├─ batch_id, form_code, status, file_path, checksum               │
│      │   └─ created_at                                                      │
│      └─ compliance_execution_batches                                        │
│          ├─ status: pending → processing → completed                        │
│          └─ processed_at                                                    │
│      ↓                                                                       │
│  Result: All forms generated                                                │
│          File paths stored in database                                      │
│          Audit and certification completed                                  │
│          User can download inspection pack                                  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Diagram

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         FREQUENCY ENGINE                                 │
│                                                                          │
│  compliance_forms_master.frequency                                      │
│      ├─ monthly → months 1-12                                           │
│      ├─ quarterly → months 3,6,9,12                                     │
│      ├─ half-yearly → months 6,12                                       │
│      └─ yearly → month 12                                               │
│                                                                          │
│  getApplicableForms($month) → Collection of forms                       │
└──────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                      BATCH ORCHESTRATOR                                  │
│                                                                          │
│  Stage 1: createBatch()                                                 │
│      ├─ Input: tenantId, month, year                                    │
│      ├─ Process: Detect forms, create batch, attach forms               │
│      └─ Output: ComplianceExecutionBatch (status=pending)               │
└──────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                    COMPLIANCE ORCHESTRATOR                               │
│                                                                          │
│  Stage 2: execute(mode='preview')                                       │
│      ├─ Input: tenantId, branchId, month, year, formCode, batchId       │
│      ├─ Process: Fetch data, generate form data, render HTML            │
│      └─ Output: HTML (no DB updates)                                    │
│                                                                          │
│  Stage 3: execute(mode='batch')                                         │
│      ├─ Input: tenantId, branchId, month, year, formCode, batchId       │
│      ├─ Process: Fetch data, generate form data, generate PDF           │
│      └─ Output: file_path, file_size                                    │
└──────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                   EXECUTION SERVICE                                      │
│                                                                          │
│  Stage 3: processBatch()                                                │
│      ├─ Input: batchId                                                  │
│      ├─ Process: For each form, call orchestrator, update DB            │
│      ├─ Audit: Run ComplianceAuditService                               │
│      ├─ Certify: Run ComplianceCertificationService                     │
│      └─ Output: results array                                           │
└──────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                      DATABASE UPDATES                                    │
│                                                                          │
│  compliance_execution_batches                                           │
│      └─ status: pending → processing → completed                        │
│                                                                          │
│  compliance_batch_forms                                                 │
│      ├─ status: pending → generated                                     │
│      └─ file_path: NULL → storage/app/generated_forms/...               │
│                                                                          │
│  compliance_generation_logs                                             │
│      └─ batch_id, form_code, status, file_path, checksum               │
│                                                                          │
│  compliance_audit_logs                                                  │
│      └─ batch_id, form_code, audit_score, status                        │
│                                                                          │
│  compliance_certification_logs                                          │
│      └─ batch_id, form_code, certification_score, certified             │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Multi-Tenant Safety

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    MULTI-TENANT ISOLATION                                │
│                                                                          │
│  Stage 1: Batch Creation                                                │
│      └─ Branch::where('tenant_id', $tenantId)->first()                  │
│                                                                          │
│  Stage 2: Preview                                                       │
│      └─ if ($batchModel->tenant_id !== Auth::user()->tenant_id)         │
│         abort(403)                                                      │
│                                                                          │
│  Stage 3: Processing                                                    │
│      └─ ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
│         ->where('id', $id)->firstOrFail()                               │
│                                                                          │
│  All queries enforce:                                                   │
│      ├─ tenant_id filtering at database level                           │
│      ├─ branch_id filtering at database level                           │
│      └─ User authentication at application level                        │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Status Transitions

```
Stage 1: Batch Creation
┌─────────────────────────────────────────────────────────────────┐
│ compliance_execution_batches                                    │
│                                                                 │
│ status: NULL → 'pending'                                        │
│                                                                 │
│ compliance_batch_forms                                          │
│ status: NULL → 'pending'                                        │
│ file_path: NULL → NULL                                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
Stage 2: Preview
┌─────────────────────────────────────────────────────────────────┐
│ compliance_execution_batches                                    │
│ status: 'pending' → 'pending' (no change)                       │
│                                                                 │
│ compliance_batch_forms                                          │
│ status: 'pending' → 'pending' (no change)                       │
│ file_path: NULL → NULL (no change)                              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
Stage 3: Processing
┌─────────────────────────────────────────────────────────────────┐
│ compliance_execution_batches                                    │
│ status: 'pending' → 'processing' → 'completed'                  │
│                                                                 │
│ compliance_batch_forms                                          │
│ status: 'pending' → 'generated'                                 │
│ file_path: NULL → 'storage/app/generated_forms/...'             │
└─────────────────────────────────────────────────────────────────┘
```

---

## File Storage Structure

```
storage/app/
├── generated_forms/
│   ├── {tenantId}/
│   │   ├── {batchId}/
│   │   │   ├── FORM_B.pdf
│   │   │   ├── FORM_12.pdf
│   │   │   ├── FORM_25.pdf
│   │   │   └── ...
│   │   └── {batchId}/
│   │       └── ...
│   └── {tenantId}/
│       └── ...
├── compliance/
│   ├── manual_uploads/
│   ├── compliance_pdfs/
│   └── temp/
└── ...
```

---

## Error Handling

```
Stage 1: Batch Creation
├─ No branch found → Exception
├─ No section configured → Exception
└─ No applicable forms → Exception

Stage 2: Preview
├─ Batch not found → 404
├─ User not authorized → 403
├─ Form not found → 400
├─ Template not found → Exception
└─ Data validation failed → Exception

Stage 3: Processing
├─ Batch not found → 404
├─ Batch status not pending → Error message
├─ Payroll not found (FULL) → Exception
├─ Form generation failed → Log error, continue
├─ PDF generation failed → Log error, continue
└─ Audit/Certification failed → Log warning, continue
```

---

## Summary

The three-stage workflow provides:
- ✅ Clear separation of concerns
- ✅ User control over batch processing
- ✅ Preview capability before generation
- ✅ Automatic form detection by frequency
- ✅ Multi-tenant safety at all stages
- ✅ Audit and certification automation
- ✅ Proper error handling and logging
