# Workflow Correction Plan - Three-Stage Batch Processing

## Current Problem

The system is incorrectly generating forms during batch creation, bypassing the preview and proceed stages.

## Required Three-Stage Workflow

### Stage 1: Batch Creation (Dashboard → Create Batch)
- User selects Month + Year
- System creates batch record
- System detects applicable forms using frequency rules
- System attaches forms with status = `pending`
- **NO form generation happens**
- Dashboard displays form list with preview buttons

### Stage 2: Preview Stage (User clicks Preview)
- User can preview individual forms
- System renders blade template with available data
- **NO database updates**
- User can preview multiple forms before proceeding

### Stage 3: Processing Stage (User clicks Proceed)
- User clicks "Proceed" button
- System processes entire batch
- System generates all forms
- System updates file_path in compliance_batch_forms
- System updates status to `generated`
- System runs audit and certification

## Architecture Changes

### 1. BatchOrchestrator (Stage 1 Only)
- `createBatch()` - Creates batch and attaches forms with pending status
- `detectApplicableForms()` - Uses FrequencyEngine
- `attachForms()` - Inserts into compliance_batch_forms with status=pending

### 2. ComplianceOrchestrator (Stages 2 & 3)
- `execute()` - Handles preview and batch generation
- `executePreview()` - Renders HTML without database updates
- `executeBatch()` - Generates PDF and updates file_path

### 3. ComplianceExecutionService (Stage 3)
- `processBatch()` - Orchestrates form generation for entire batch
- Calls ComplianceOrchestrator for each form
- Updates compliance_batch_forms with file_path and status=generated
- Runs audit and certification

### 4. ComplianceExecutionController
- `createBatch()` - Calls BatchOrchestrator (Stage 1)
- `previewForm()` - Calls ComplianceOrchestrator::execute() with mode='preview' (Stage 2)
- `processBatch()` - Calls ComplianceExecutionService::processBatch() (Stage 3)

## Database Schema

### compliance_execution_batches
- id
- tenant_id
- branch_id
- period_month
- period_year
- status (pending → processing → completed/failed)
- created_at

### compliance_batch_forms
- id
- tenant_id
- batch_id
- form_code
- status (pending → generated → success/failed)
- file_path (NULL until generated)
- created_at

## Modified Files

1. `app/Services/Compliance/BatchOrchestrator.php` - Stage 1 only
2. `app/Services/Compliance/ComplianceOrchestrator.php` - No changes needed (already correct)
3. `app/Services/Compliance/ComplianceExecutionService.php` - Stage 3 processing
4. `app/Http/Controllers/ComplianceExecutionController.php` - Route to correct stages

## Key Points

- Stage 1: Only creates batch and attaches forms (no generation)
- Stage 2: Only renders preview (no database updates)
- Stage 3: Generates forms and updates database
- Frequency engine detects applicable forms automatically
- Multi-tenant safety maintained at all stages
