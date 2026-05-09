# Labour Compliance Automation System - Lifecycle Redesign

## Overview

The system has been redesigned to support a full compliance automation lifecycle with automatic audit, certification, and correction workflows.

## Key Changes

### 1. ComplianceExecutionService (Enhanced)

**File:** `app/Services/Compliance/ComplianceExecutionService.php`

**Changes:**
- Added automatic certification trigger after batch audit completes
- Certification runs immediately after audit in the processing pipeline
- Both audit and certification results are logged for dashboard display

**Workflow:**
```
Forms Generated → Audit Each Form → Audit Batch → Certify Batch → Update Status
```

### 2. ComplianceAuditService (Enhanced)

**File:** `app/Services/Compliance/Audit/ComplianceAuditService.php`

**Changes:**
- `auditBatch()` now returns comprehensive audit results instead of void
- Returns batch-level statistics: average score, status, passed/total forms
- Calculates batch status: 'passed', 'failed', or 'partial'

**Return Structure:**
```php
[
    'status' => 'success',
    'batch_score' => 85,
    'batch_status' => 'partial',
    'passed_forms' => 3,
    'total_forms' => 5,
    'form_results' => [...]
]
```

### 3. ComplianceCorrectionService (Enhanced)

**File:** `app/Services/Compliance/Audit/ComplianceCorrectionService.php`

**Changes:**
- Improved automatic field value fetching from tenant/branch master data
- Better handling of user input for missing fields
- Regenerates PDF and re-audits automatically after corrections
- Updates file path to standard location: `compliance/generated/{batch_id}/{form_code}.pdf`

**Fix Workflow:**
```
Detect Violations → Auto-fetch Values → If Missing: Prompt User → Regenerate PDF → Re-audit → Update Score
```

### 4. InspectionPackService (New)

**File:** `app/Services/Compliance/InspectionPackService.php`

**Purpose:**
- Centralized inspection pack generation logic
- Creates ZIP file with all successful forms
- Filters out forms that failed audit
- Stores ZIP in: `storage/app/temp/inspection_{batch_id}.zip`

### 5. Dashboard Enhancements

**File:** `app/Http/Controllers/ComplianceExecutionController.php`

**Dashboard Display:**
- Batch ID
- Section
- Period (Month/Year)
- Status (Pending, Processing, Completed, Failed, Partially Completed)
- Audit Score (0-100)
- Audit Status (Passed, Failed, Partial, Not Audited)
- Certification Status (Inspection Ready, Review Required, Not Certified)
- Certification Score
- Actions: Preview, Fix Issues, Inspection Pack

**Batch Enrichment Logic:**
```php
foreach ($batches as $batch) {
    // Calculate display status from generation logs
    $batch->display_status = calculateStatus($logs);
    
    // Get audit data
    $auditLogs = ComplianceAuditLog::where('batch_id', $batch->id)->get();
    $batch->audit_score = round($auditLogs->avg('audit_score'));
    $batch->audit_status = determineAuditStatus($auditLogs);
    $batch->has_violations = $auditLogs->where('status', 'failed')->count() > 0;
    
    // Get certification data
    $certLog = DB::table('compliance_certification_logs')
        ->where('batch_id', $batch->id)
        ->where('form_code', 'BATCH_SUMMARY')
        ->first();
    $batch->certification_status = $certLog->certified ? 'Inspection Ready' : 'Review Required';
    $batch->certification_score = $certLog->certification_score;
}
```

## Subscription Behavior

### FULL Subscription
- Forms fetch data automatically from database
- Sources: workforce_employee, workforce_payroll_entry, contract_labour_deployments, incident_documents, branches, tenants
- ComplianceDataService builds datasets
- Blade templates render with real data
- PDFs stored in: `storage/app/compliance/generated/{batch_id}/{form_code}.pdf`

### MINIMAL Subscription
- Users upload Excel/CSV files
- Workflow: Upload → Parse → Map Columns → Build Dataset → Generate Forms → Generate PDFs
- Same audit/certification/inspection pack pipeline runs after generation

## Form Generation Rule

Forms are generated **section-wise**. Example for Factories Act:
- FORM_B, FORM_10, FORM_11, FORM_12, FORM_17, FORM_18, FORM_25, FORM_26, FORM_26A

If user selects a section, only forms belonging to that section are generated.

## Audit Engine

**Automatic Execution:** After forms are generated

**Checks:**
- Missing fields
- Invalid totals
- Invalid statutory structure
- Missing employee data

**Storage:** `compliance_audit_logs` table

**Fields:**
- batch_id
- form_code
- audit_score (0-100)
- status (passed/failed)
- violations (JSON array)

**Dashboard Display:**
- Audit Score
- Audit Status
- Fix Issues Button (if violations exist)

## Fix Issues Engine

**Trigger:** User clicks "Fix Issues" button

**Process:**
1. Detect violations from audit log
2. Attempt automatic corrections:
   - Recalculate totals
   - Fetch missing employee data from master tables
   - Fill default statutory values
3. If user input required:
   - Prompt user to enter missing fields
   - Return list of required fields
4. After fixing:
   - Regenerate PDF
   - Re-run audit automatically
   - Update audit score in dashboard

**Response Structure:**
```php
// If auto-fixable
['status' => 'success', 'form_score' => 85, 'batch_average_score' => 82, ...]

// If requires user input
['status' => 'requires_input', 'missing_fields' => [...], 'auto_fixed' => [...]]

// If error
['status' => 'error', 'message' => '...']
```

## Certification Engine

**Automatic Execution:** After batch audit completes

**Rules:**
- Score >= 90 → Inspection Ready
- Score >= 70 → Minor Issues (Review Recommended)
- Score < 70 → Correction Required

**Storage:** `compliance_certification_logs` table

**Fields:**
- batch_id
- form_code (or 'BATCH_SUMMARY' for batch-level)
- certification_score
- certified (boolean)
- violations (JSON)

**Dashboard Display:**
- Certification Status
- Certification Score
- Violations/Warnings list

## Inspection Pack

**Purpose:** Download all generated PDF forms as a single ZIP file

**Trigger:** User clicks "Inspection Pack" button

**Process:**
1. Verify certification score >= 70
2. Collect all forms with status='success'
3. Filter out forms that failed audit
4. Create ZIP file: `storage/app/temp/inspection_{batch_id}.zip`
5. Add each form as: `{form_code}.pdf`
6. Download automatically

**Validation:**
- Batch must have certification score >= 70
- Only includes forms that passed audit
- Skips forms with failed audit status

## API Endpoints

### Dashboard
```
GET /compliance/dashboard
```
Returns batches with enriched audit/certification data

### Create Batch
```
POST /compliance/batches
```
Payload:
```json
{
    "statutory_section": "factories_act",
    "period_month": 3,
    "period_year": 2024,
    "form_ids": [1, 2, 3],
    "branch_id": 1
}
```

### Process Batch
```
POST /compliance/batches/{id}/process
```
Triggers form generation → audit → certification pipeline

### Fix Violations
```
POST /compliance/batches/{batch_id}/forms/{form_code}/fix
```
Attempts automatic corrections

### Submit User Input
```
POST /compliance/batches/{batch_id}/forms/{form_code}/fix-submit
```
Payload:
```json
{
    "corrections": {
        "field_name": "value",
        "another_field": "value"
    }
}
```

### Download Inspection Pack
```
GET /compliance/batches/{id}/inspection-pack
```
Downloads ZIP file with all forms

### Get Certification Status
```
GET /compliance/batches/{id}/certification-status
```
Returns certification details

## Database Tables

### compliance_audit_logs
- batch_id
- form_code
- audit_score
- status (passed/failed)
- violations (JSON)
- created_at, updated_at

### compliance_certification_logs
- batch_id
- form_code
- certification_score
- certified (boolean)
- violations (JSON)
- certified_at
- created_at, updated_at

### compliance_batch_forms
- batch_id
- form_code
- file_path
- status (success/failed)
- section
- created_at

### compliance_generation_logs
- batch_id
- form_code
- status (success/failed)
- generated_file_path
- created_at

## File Storage Structure

```
storage/app/
├── compliance/
│   ├── generated/
│   │   ├── {batch_id}/
│   │   │   ├── FORM_B.pdf
│   │   │   ├── FORM_10.pdf
│   │   │   └── ...
│   │   └── ...
│   └── manual_uploads/
│       └── ...
└── temp/
    └── inspection_{batch_id}.zip
```

## Implementation Checklist

- [x] Enhanced ComplianceExecutionService with automatic certification
- [x] Enhanced ComplianceAuditService with batch-level statistics
- [x] Enhanced ComplianceCorrectionService with better fix logic
- [x] Created InspectionPackService for ZIP generation
- [x] Updated dashboard to display audit scores and certification status
- [ ] Update Blade templates to show Fix Issues button
- [ ] Update Blade templates to show Inspection Pack button
- [ ] Add frontend logic for fix violations workflow
- [ ] Add frontend logic for inspection pack download
- [ ] Test full automation pipeline
- [ ] Test MINIMAL subscription workflow
- [ ] Test FULL subscription workflow

## Testing Scenarios

### Scenario 1: FULL Subscription - Successful Batch
1. Create batch with valid forms
2. Process batch
3. Verify forms generated
4. Verify audit passed
5. Verify certification ready
6. Download inspection pack

### Scenario 2: FULL Subscription - Violations
1. Create batch
2. Process batch
3. Verify audit failed
4. Click Fix Issues
5. Auto-fix violations
6. Verify audit passed
7. Download inspection pack

### Scenario 3: MINIMAL Subscription
1. Create batch
2. Upload CSV files
3. Process batch
4. Verify forms generated
5. Run audit/certification
6. Download inspection pack

## Notes

- All timestamps use `now()` for consistency
- Audit scores range from 0-100
- Certification scores range from 0-100
- Violations stored as JSON arrays
- File paths are relative to `storage/app/`
- ZIP files auto-delete after download
- Batch status updated after each stage
