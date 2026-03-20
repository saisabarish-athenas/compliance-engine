# Batch Workflow Refactoring - Complete Architecture

## Overview

This document outlines the refactored batch workflow architecture that implements automation-first form detection based on the `frequency` column in `compliance_forms_master`.

## Core Principle

**Forms must be automatically detected and listed based ONLY on the selected Month and Year.**

Users never manually select forms or sections. Form selection is fully automated using frequency rules.

---

## Architecture Layers

```
Dashboard (View)
    ↓
ComplianceExecutionController
    ├─ createBatch() → Stage 1
    ├─ reviewBatch() → Stage 2 (NEW)
    └─ processBatch() → Stage 3
    ↓
BatchOrchestrator (Orchestration)
    ├─ createBatch()
    ├─ detectApplicableForms()
    └─ checkDataAvailability()
    ↓
FrequencyEngine (Form Detection)
    └─ getApplicableForms()
    ↓
DataAvailabilityEngine (NEW - Data Validation)
    └─ checkRequiredData()
    ↓
ComplianceExecutionService (Processing)
    └─ processBatch()
    ↓
ComplianceOrchestrator (Generation)
    └─ execute()
```

---

## Three-Stage Workflow

### Stage 1: Create Batch
- User selects Month + Year
- System detects applicable forms using frequency rules
- System creates batch record with `status = 'pending'`
- System attaches forms to batch with `status = 'pending'`
- **NO form generation happens**
- Redirect to Stage 2

### Stage 2: Review Batch (NEW)
- Display detected forms
- Check data availability
- If all data exists → Show "Proceed" button
- If data missing → Show input options (Manual Fill, CSV Upload, PDF Upload)
- User fills required data (if needed)
- User clicks "Proceed"

### Stage 3: Process Batch
- System generates all forms
- System updates file_path in compliance_batch_forms
- System updates status to 'generated'
- System runs audit and certification
- Redirect to dashboard with success message

---

## Database Structure

### compliance_execution_batches
```
id
tenant_id
branch_id
period_month
period_year
status (pending → processing → completed/failed)
created_at
updated_at
```

### compliance_batch_forms
```
id
tenant_id
batch_id
form_code
status (pending → generated → success/failed)
file_path (NULL until generated)
created_at
```

### compliance_forms_master
```
id
form_code
form_name
section_id
frequency (monthly, quarterly, half-yearly, yearly)
is_active
```

---

## Form Detection Logic

### Frequency Rules

```
monthly → every month
quarterly → months 3, 6, 9, 12
half-yearly → months 6, 12
yearly → month 12
```

### Example

User selects March (month 3):
- All monthly forms
- All quarterly forms (month 3 is Q1)

---

## Data Availability Engine

### Purpose
Check if required data exists before allowing batch processing.

### Data Sources
- workforce_employee
- workforce_attendance
- payroll_entries
- contract_labour
- bonus_records
- incident_documents
- hazard_register

### Logic
```
IF data exists → proceed normally
IF data missing → request input
```

---

## File Changes Summary

### New Files
1. `DataAvailabilityEngine.php` - Check required data
2. `BatchReviewService.php` - Prepare review stage data

### Modified Files
1. `ComplianceExecutionController.php` - Add reviewBatch() method
2. `BatchOrchestrator.php` - Add data availability check
3. `ComplianceExecutionService.php` - No changes needed

### Views
1. `compliance/batch-review.blade.php` - NEW review stage view

---

## Implementation Details

### 1. Form Detection (FrequencyEngine)
Already implemented. No changes needed.

### 2. Batch Creation (BatchOrchestrator)
Already implemented. No changes needed.

### 3. Data Availability Check (NEW)
Create `DataAvailabilityEngine.php` to check required data.

### 4. Review Stage (NEW)
Add `reviewBatch()` method to controller.
Create `batch-review.blade.php` view.

### 5. Processing (ComplianceExecutionService)
Already implemented. No changes needed.

---

## Workflow Verification

✅ User selects Month + Year
✅ System detects applicable forms
✅ System creates batch
✅ System checks data availability
✅ System displays review page
✅ User fills missing data (if needed)
✅ User clicks Proceed
✅ System processes batch
✅ System generates forms
✅ System updates database
✅ System runs audit and certification

---

## Key Constraints

- Do NOT break form preview
- Do NOT break inspection pack generation
- Do NOT break existing blade templates
- Do NOT break APIs
- Do NOT break multi-tenant architecture
- Only modify batch workflow logic

---

## Status

**Architecture:** ✅ COMPLETE
**Implementation:** Ready for coding
**Testing:** Ready for validation

