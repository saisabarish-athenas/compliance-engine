# Violation Correction Engine - Implementation Guide

## Overview
Complete automated violation correction workflow for Laravel 12 Compliance SaaS.

## Architecture

### Core Components

1. **ComplianceCorrectionService** (`app/Services/Compliance/Audit/ComplianceCorrectionService.php`)
   - Main service handling violation correction logic
   - Auto-fetches missing data from tenant/branch/payroll tables
   - Regenerates PDFs with corrected data
   - Updates audit logs and batch scores

2. **Controller Methods** (`ComplianceExecutionController.php`)
   - `fixViolations()` - Initiates auto-correction workflow
   - `submitFix()` - Handles user-submitted corrections
   - `reAudit()` - Re-audits without correction (legacy)

3. **Routes** (`routes/compliance.php`)
   - `POST /batch/{batch}/fix-violations/{form}` - Auto-fix endpoint
   - `POST /batch/{batch}/submit-fix/{form}` - User input endpoint

4. **Frontend** (`dashboard.blade.php`)
   - Dynamic modal for missing field input
   - Real-time UI updates after correction
   - Bootstrap 5 modal integration

---

## Workflow

### Step 1: User Clicks "Fix & Re-Audit"
```javascript
// Frontend triggers fix violations endpoint
fetch(`/compliance/batch/${batchId}/fix-violations/${formCode}`)
```

### Step 2: Auto-Fetch Data
```php
// Service attempts to auto-fetch from:
1. Tenant master (establishment_name, pf_code, esi_code)
2. Branch details (unit_name, address, factory_license_number)
3. Period fields (period_month, period_year)
4. Other forms in same batch
```

### Step 3A: All Data Found
```php
// If all violations can be auto-fixed:
return [
    'status' => 'success',
    'form_score' => 95,
    'batch_average_score' => 88,
    'violations' => [],
    'confidence_label' => 'Inspection Ready'
];
```

### Step 3B: Missing Data
```php
// If some fields still missing:
return [
    'status' => 'requires_input',
    'missing_fields' => [
        ['field' => 'establishment_name', 'message' => 'Missing required header field']
    ]
];
```

### Step 4: User Input Modal
```javascript
// Frontend shows Bootstrap modal with input fields
showFixModal(batchId, formCode, missingFields);
```

### Step 5: Submit Corrections
```javascript
// User submits form data
fetch(`/compliance/batch/${batchId}/submit-fix/${formCode}`, {
    body: JSON.stringify({ corrections: { establishment_name: 'ABC Ltd' } })
})
```

### Step 6: Regenerate & Replace
```php
// Service regenerates PDF
$pdfOutput = $generator->generate(...);

// Deletes old file
Storage::disk('local')->delete($batchForm->file_path);

// Saves new PDF (overwrites)
Storage::disk('local')->put($filePath, $pdfOutput);

// Updates batch form record
$batchForm->update(['file_path' => $filePath]);
```

### Step 7: Re-Audit
```php
// Runs audit on corrected data
$auditResult = $this->auditService->audit($formCode, $preparedData);

// Updates audit log
ComplianceAuditLog::updateOrCreate([...], [
    'audit_score' => $auditResult['score'],
    'violations' => $auditResult['violations']
]);
```

### Step 8: Recalculate Batch Score
```php
// Calculates average across all forms
$batchAverageScore = ComplianceAuditLog::where('batch_id', $batch->id)
    ->avg('audit_score');

// Updates batch record
$batch->update(['audit_score' => round($batchAverageScore)]);
```

### Step 9: Update UI
```javascript
// Updates all UI elements:
- Batch average score
- Progress bar
- Confidence label
- Form status badge
- Violations list
- Table score badge
```

---

## API Endpoints

### Fix Violations (Auto)
```http
POST /compliance/batch/{batch}/fix-violations/{form}
Content-Type: application/json
X-CSRF-TOKEN: {token}

Response (Success):
{
    "status": "success",
    "form_code": "FORM_B",
    "form_score": 95,
    "batch_average_score": 88,
    "audit_status": "passed",
    "violations": [],
    "confidence_label": "Inspection Ready",
    "file_path": "compliance/batch_123/FORM_B_corrected_1234567890.pdf"
}

Response (Requires Input):
{
    "status": "requires_input",
    "missing_fields": [
        {
            "field": "establishment_name",
            "message": "Missing required header field: establishment_name",
            "type": "header"
        }
    ],
    "auto_fixed": {
        "period_month": 12,
        "period_year": 2024
    }
}
```

### Submit Fix (User Input)
```http
POST /compliance/batch/{batch}/submit-fix/{form}
Content-Type: application/json
X-CSRF-TOKEN: {token}

Body:
{
    "corrections": {
        "establishment_name": "ABC Manufacturing Ltd",
        "factory_license_number": "TN/FAC/2024/001"
    }
}

Response:
{
    "status": "success",
    "form_code": "FORM_B",
    "form_score": 100,
    "batch_average_score": 92,
    "audit_status": "passed",
    "violations": [],
    "confidence_label": "Inspection Ready"
}
```

---

## Data Sources Priority

### Auto-Fetch Priority Order:
1. **Tenant Master** (`tenants` table)
   - establishment_name
   - factory_license_no
   - pf_code
   - esi_code

2. **Branch Details** (`branches` table)
   - unit_name / branch_name
   - address
   - factory_license_number

3. **Batch Context**
   - period_month
   - period_year

4. **Other Forms** (same batch)
   - Cross-form data extraction (future enhancement)

---

## File Replacement Strategy

### Old Approach (WRONG):
```php
// Creates duplicate entries
$newPath = "compliance/batch_{$batch->id}/{$formCode}_v2.pdf";
ComplianceBatchForm::create([...]);
```

### New Approach (CORRECT):
```php
// Overwrites existing file
$batchForm = ComplianceBatchForm::where('batch_id', $batch->id)
    ->where('form_code', $formCode)
    ->first();

// Delete old file
Storage::disk('local')->delete($batchForm->file_path);

// Save new file
$filePath = "compliance/batch_{$batch->id}/{$formCode}_corrected_" . time() . ".pdf";
Storage::disk('local')->put($filePath, $pdfOutput);

// Update existing record
$batchForm->update(['file_path' => $filePath]);
```

---

## Database Updates

### Audit Log Update:
```php
ComplianceAuditLog::updateOrCreate(
    [
        'tenant_id' => $batch->tenant_id,
        'batch_id' => $batch->id,
        'form_code' => $formCode,
    ],
    [
        'audit_score' => $auditResult['score'],
        'status' => $auditResult['status'],
        'violations' => $auditResult['violations'],
        'updated_at' => now(),
    ]
);
```

### Batch Score Update:
```php
$batchAverageScore = ComplianceAuditLog::where('batch_id', $batch->id)
    ->avg('audit_score');

$batch->update(['audit_score' => round($batchAverageScore)]);
```

---

## Frontend Integration

### Modal Structure:
```html
<div class="modal fade" id="fixModal_{batch}_{form}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>🔧 Fix Violations - {formCode}</h5>
            </div>
            <div class="modal-body">
                <form id="fixForm_{batch}_{form}">
                    <!-- Dynamic fields generated from missing_fields -->
                    <div class="mb-3">
                        <label><strong>{field}</strong></label>
                        <input type="text" name="{field}" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="submitFix_{batch}_{form}">
                    Submit & Regenerate
                </button>
            </div>
        </div>
    </div>
</div>
```

### UI Update Function:
```javascript
function updateAuditUI(batchId, formCode, data, btn) {
    // Updates 7 UI elements:
    // 1. Modal header score
    // 2. Progress bar
    // 3. Confidence badge
    // 4. Form status badge
    // 5. Form score badge
    // 6. Violations list
    // 7. Table score badge
}
```

---

## Error Handling

### Service Level:
```php
try {
    // Correction logic
} catch (\Exception $e) {
    Log::error('Violation correction failed', [
        'form_code' => $formCode,
        'batch_id' => $batch->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    return [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}
```

### Frontend Level:
```javascript
.catch(err => {
    alert('❌ Error: ' + err.message);
    btn.disabled = false;
    btn.innerHTML = '🔧 Fix & Re-Audit';
});
```

---

## Testing Scenarios

### Scenario 1: All Data Auto-Fixed
```
User clicks "Fix & Re-Audit"
→ System fetches all missing data
→ Regenerates PDF
→ Updates audit log
→ Shows success message
→ UI updates automatically
```

### Scenario 2: Partial Auto-Fix
```
User clicks "Fix & Re-Audit"
→ System fetches some data
→ Shows modal for remaining fields
→ User enters data
→ System regenerates PDF
→ Updates audit log
→ Shows success message
```

### Scenario 3: No Violations
```
User clicks "Fix & Re-Audit"
→ System finds no violations
→ Shows "No violations found" message
→ No regeneration needed
```

### Scenario 4: Error During Regeneration
```
User clicks "Fix & Re-Audit"
→ System attempts regeneration
→ Error occurs (e.g., invalid data)
→ Shows error message
→ Logs error details
→ Button re-enabled for retry
```

---

## Inspection Pack Integration

### Before Correction:
```php
// Failed forms excluded from inspection pack
$auditLogs = ComplianceAuditLog::where('batch_id', $batch)
    ->where('status', 'failed')
    ->pluck('form_code');

$forms = $forms->reject(function($form) use ($auditLogs) {
    return $auditLogs->contains($form->form_code);
});
```

### After Correction:
```php
// Corrected forms now included
// New PDF path automatically used
// Inspection pack contains corrected file
```

---

## Performance Considerations

1. **Memory Management**
   - Unsets large objects after PDF generation
   - Uses chunking for large datasets

2. **File Operations**
   - Deletes old files before creating new ones
   - Uses timestamped filenames to avoid conflicts

3. **Database Queries**
   - Uses updateOrCreate for atomic updates
   - Calculates batch average in single query

---

## Security

1. **Authorization**
   - Verifies tenant ownership before correction
   - Uses CSRF token for all POST requests

2. **Validation**
   - Validates user input before regeneration
   - Sanitizes field names and values

3. **File Access**
   - Uses Laravel Storage facade
   - Restricts file operations to local disk

---

## Future Enhancements

1. **Cross-Form Data Extraction**
   - Extract data from other PDFs in same batch
   - Use OCR for scanned documents

2. **AI-Powered Suggestions**
   - Suggest corrections based on historical data
   - Predict missing values using ML

3. **Bulk Correction**
   - Fix multiple forms simultaneously
   - Batch regeneration for efficiency

4. **Audit Trail**
   - Track all correction attempts
   - Store before/after snapshots

---

## Troubleshooting

### Issue: Modal Not Showing
```javascript
// Ensure Bootstrap 5 is loaded
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

### Issue: PDF Not Regenerating
```php
// Check generator exists
$generator = FormGeneratorFactory::make($formCode);
if (!$generator) {
    throw new \Exception('Generator not found');
}
```

### Issue: Audit Score Not Updating
```php
// Verify audit log update
$log = ComplianceAuditLog::where('batch_id', $batchId)
    ->where('form_code', $formCode)
    ->first();
dd($log->audit_score);
```

---

## Summary

✅ **Implemented:**
- ComplianceCorrectionService with auto-fetch logic
- Controller methods for fix violations and submit fix
- Routes for new endpoints
- Frontend modal for missing field input
- Real-time UI updates
- File replacement strategy
- Audit log updates
- Batch score recalculation

✅ **Benefits:**
- Automated violation correction
- Reduced manual intervention
- Improved compliance scores
- Better user experience
- Maintains data integrity
- No duplicate files
- Proper audit trail

✅ **Clean Architecture:**
- No generator modifications
- No database schema changes
- Follows Laravel conventions
- Minimal code footprint
- Reusable components
