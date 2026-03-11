# Violation Correction Engine - System Architecture

## Component Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  dashboard.blade.php                                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Audit Modal                                             │  │
│  │  ┌────────────────────────────────────────────────────┐  │  │
│  │  │  FORM_B [Failed] Score: 60/100                     │  │  │
│  │  │  ⚠️ Violations: establishment_name, period_month   │  │  │
│  │  │  [🔧 Fix & Re-Audit Button]                        │  │  │
│  │  └────────────────────────────────────────────────────┘  │  │
│  │                                                             │  │
│  │  Fix Modal (Dynamic)                                       │  │
│  │  ┌────────────────────────────────────────────────────┐  │  │
│  │  │  Missing Fields:                                   │  │  │
│  │  │  [establishment_name: ___________]                 │  │  │
│  │  │  [Submit & Regenerate]                             │  │  │
│  │  └────────────────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  JavaScript Functions:                                          │
│  • showFixModal()                                               │
│  • updateAuditUI()                                              │
│  • Event handlers                                               │
│                                                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ AJAX Requests
                         │
┌────────────────────────┴────────────────────────────────────────┐
│                         ROUTING LAYER                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  routes/compliance.php                                          │
│                                                                 │
│  POST /batch/{batch}/fix-violations/{form}                     │
│       → ComplianceExecutionController@fixViolations            │
│                                                                 │
│  POST /batch/{batch}/submit-fix/{form}                         │
│       → ComplianceExecutionController@submitFix                │
│                                                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │
┌────────────────────────┴────────────────────────────────────────┐
│                       CONTROLLER LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ComplianceExecutionController                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                                                          │  │
│  │  fixViolations($batchId, $formCode)                     │  │
│  │  ├─ Validate tenant ownership                           │  │
│  │  ├─ Call correctionService->fixFormViolations()         │  │
│  │  └─ Return JSON response                                │  │
│  │                                                          │  │
│  │  submitFix($request, $batchId, $formCode)               │  │
│  │  ├─ Validate input                                      │  │
│  │  ├─ Call correctionService->fixWithUserInput()          │  │
│  │  └─ Return JSON response                                │  │
│  │                                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │
┌────────────────────────┴────────────────────────────────────────┐
│                        SERVICE LAYER                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ComplianceCorrectionService                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                                                          │  │
│  │  fixFormViolations($batchId, $formCode)                 │  │
│  │  ├─ Read audit log violations                           │  │
│  │  ├─ Loop through violations                             │  │
│  │  │  └─ autoFetchFieldValue()                            │  │
│  │  ├─ Check if all data found                             │  │
│  │  ├─ If missing: return requires_input                   │  │
│  │  └─ If complete: regenerateAndAudit()                   │  │
│  │                                                          │  │
│  │  fixWithUserInput($batchId, $formCode, $userInput)      │  │
│  │  └─ regenerateAndAudit()                                │  │
│  │                                                          │  │
│  │  autoFetchFieldValue($field, $batch, $formCode)         │  │
│  │  ├─ Try tenant master                                   │  │
│  │  ├─ Try branch details                                  │  │
│  │  ├─ Try batch context                                   │  │
│  │  └─ Try other forms                                     │  │
│  │                                                          │  │
│  │  regenerateAndAudit($batch, $formCode, $corrections)    │  │
│  │  ├─ Get raw data from aggregator                        │  │
│  │  ├─ Merge corrections                                   │  │
│  │  ├─ Generate PDF                                        │  │
│  │  ├─ Delete old file                                     │  │
│  │  ├─ Save new file                                       │  │
│  │  ├─ Update batch form record                            │  │
│  │  ├─ Re-audit                                            │  │
│  │  ├─ Update audit log                                    │  │
│  │  └─ Recalculate batch score                             │  │
│  │                                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
└────────┬────────────────────────────┬───────────────────────────┘
         │                            │
         │                            │
┌────────┴────────┐          ┌────────┴────────────────────────────┐
│  FormGenerator  │          │  ComplianceAuditService             │
│  Factory        │          │  ┌──────────────────────────────┐   │
│  ┌───────────┐  │          │  │  audit($formCode, $data)     │   │
│  │ make()    │  │          │  │  ├─ validateHeader()         │   │
│  │ generate()│  │          │  │  ├─ validateRows()           │   │
│  └───────────┘  │          │  │  ├─ applyStatutoryRules()    │   │
│                 │          │  │  └─ Calculate score          │   │
└─────────────────┘          │  └──────────────────────────────┘   │
                             └─────────────────────────────────────┘
         │                            │
         │                            │
┌────────┴────────────────────────────┴───────────────────────────┐
│                         DATA LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Database Tables:                                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  tenants                                                 │  │
│  │  ├─ establishment_name                                   │  │
│  │  ├─ factory_license_no                                   │  │
│  │  ├─ pf_code                                              │  │
│  │  └─ esi_code                                             │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  branches                                                │  │
│  │  ├─ unit_name / branch_name                             │  │
│  │  ├─ address                                              │  │
│  │  └─ factory_license_number                               │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  compliance_execution_batches                            │  │
│  │  ├─ period_month                                         │  │
│  │  ├─ period_year                                          │  │
│  │  └─ audit_score (updated)                                │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  compliance_batch_forms                                  │  │
│  │  ├─ file_path (updated)                                  │  │
│  │  └─ status                                               │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  compliance_audit_logs                                   │  │
│  │  ├─ audit_score (updated)                                │  │
│  │  ├─ status (updated)                                     │  │
│  │  └─ violations (updated)                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
         │
         │
┌────────┴────────────────────────────────────────────────────────┐
│                      FILE SYSTEM LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  storage/app/compliance/batch_{id}/                             │
│  ├─ FORM_B_old.pdf (deleted)                                    │
│  └─ FORM_B_corrected_1234567890.pdf (new)                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow Sequence

```
User Action → Frontend → Routes → Controller → Service → Database/Files
     ↓           ↓         ↓         ↓          ↓            ↓
  Click      AJAX      Route     Validate   Auto-Fetch   Read/Write
  Button     Request   Match     Tenant     Missing      Records
                                 Auth       Data
     ↓           ↓         ↓         ↓          ↓            ↓
  Wait       Show      Call      Call       Generate     Update
  Response   Modal     Method    Service    PDF          Tables
     ↓           ↓         ↓         ↓          ↓            ↓
  Receive    Update    Return    Return     Replace      Commit
  JSON       UI        JSON      Result     File         Changes
```

## Component Interactions

```
┌──────────────┐
│   Browser    │
└──────┬───────┘
       │ 1. Click Fix Button
       ↓
┌──────────────┐
│  JavaScript  │
└──────┬───────┘
       │ 2. POST /fix-violations
       ↓
┌──────────────┐
│  Controller  │
└──────┬───────┘
       │ 3. fixFormViolations()
       ↓
┌──────────────────────┐
│ CorrectionService    │
└──────┬───────────────┘
       │ 4. Read violations
       ↓
┌──────────────────────┐
│ ComplianceAuditLog   │
└──────┬───────────────┘
       │ 5. Get violations
       ↓
┌──────────────────────┐
│ CorrectionService    │
└──────┬───────────────┘
       │ 6. autoFetchFieldValue()
       ↓
┌──────────────────────┐
│ Tenant/Branch Tables │
└──────┬───────────────┘
       │ 7. Return data
       ↓
┌──────────────────────┐
│ CorrectionService    │
└──────┬───────────────┘
       │ 8. All found? → regenerateAndAudit()
       │    Missing?   → return requires_input
       ↓
┌──────────────────────┐
│ FormGenerator        │
└──────┬───────────────┘
       │ 9. Generate PDF
       ↓
┌──────────────────────┐
│ Storage              │
└──────┬───────────────┘
       │ 10. Save file
       ↓
┌──────────────────────┐
│ ComplianceBatchForm  │
└──────┬───────────────┘
       │ 11. Update file_path
       ↓
┌──────────────────────┐
│ AuditService         │
└──────┬───────────────┘
       │ 12. Re-audit
       ↓
┌──────────────────────┐
│ ComplianceAuditLog   │
└──────┬───────────────┘
       │ 13. Update scores
       ↓
┌──────────────────────┐
│ ExecutionBatch       │
└──────┬───────────────┘
       │ 14. Update avg score
       ↓
┌──────────────────────┐
│ Controller           │
└──────┬───────────────┘
       │ 15. Return JSON
       ↓
┌──────────────────────┐
│ JavaScript           │
└──────┬───────────────┘
       │ 16. updateAuditUI()
       ↓
┌──────────────────────┐
│ Browser              │
└──────────────────────┘
```

## File Structure

```
compliance-engine/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ComplianceExecutionController.php (modified)
│   │
│   ├── Models/
│   │   ├── ComplianceAuditLog.php
│   │   ├── ComplianceBatchForm.php
│   │   └── ComplianceExecutionBatch.php
│   │
│   └── Services/
│       └── Compliance/
│           ├── Audit/
│           │   ├── ComplianceAuditService.php
│           │   └── ComplianceCorrectionService.php (NEW)
│           │
│           └── FormGenerator/
│               ├── FormGeneratorFactory.php
│               ├── FormDataAggregator.php
│               └── BaseFormGenerator.php
│
├── routes/
│   └── compliance.php (modified)
│
├── resources/
│   └── views/
│       └── compliance/
│           └── dashboard.blade.php (modified)
│
├── storage/
│   └── app/
│       └── compliance/
│           └── batch_{id}/
│               └── {form}_corrected_{timestamp}.pdf
│
└── Documentation/
    ├── VIOLATION_CORRECTION_ENGINE.md (NEW)
    ├── VIOLATION_CORRECTION_QUICK_REFERENCE.md (NEW)
    ├── VIOLATION_CORRECTION_IMPLEMENTATION_SUMMARY.md (NEW)
    └── VIOLATION_CORRECTION_ARCHITECTURE.md (NEW)
```

## State Transitions

```
Initial State:
┌─────────────────────────────────────┐
│ Form Status: Failed                 │
│ Audit Score: 60/100                 │
│ Violations: 3                       │
│ File: FORM_B_original.pdf           │
└─────────────────────────────────────┘
              ↓
         User clicks Fix
              ↓
┌─────────────────────────────────────┐
│ State: Processing                   │
│ Action: Auto-fetching data          │
└─────────────────────────────────────┘
              ↓
    ┌─────────┴─────────┐
    │                   │
All Found          Some Missing
    │                   │
    ↓                   ↓
┌─────────┐    ┌──────────────────┐
│Regenerate│    │ State: Awaiting  │
│         │    │ Action: Show modal│
└────┬────┘    └────────┬─────────┘
     │                  │
     │                  ↓
     │         ┌──────────────────┐
     │         │ User enters data │
     │         └────────┬─────────┘
     │                  │
     └──────────────────┘
              ↓
┌─────────────────────────────────────┐
│ State: Regenerating                 │
│ Action: Generate PDF                │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ State: Replacing                    │
│ Action: Delete old, save new        │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ State: Re-auditing                  │
│ Action: Run audit                   │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ State: Updating                     │
│ Action: Update logs & scores        │
└─────────────────────────────────────┘
              ↓
Final State:
┌─────────────────────────────────────┐
│ Form Status: Passed                 │
│ Audit Score: 95/100                 │
│ Violations: 0                       │
│ File: FORM_B_corrected_xxx.pdf      │
└─────────────────────────────────────┘
```

## Integration Points

```
┌────────────────────────────────────────────────────────────┐
│              Violation Correction Engine                   │
└────────────────────────────────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ↓                  ↓                  ↓
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│ Form          │  │ Audit         │  │ Inspection    │
│ Generation    │  │ System        │  │ Pack          │
│ System        │  │               │  │ Builder       │
└───────────────┘  └───────────────┘  └───────────────┘
        │                  │                  │
        ↓                  ↓                  ↓
   Uses existing      Updates audit      Includes
   generators         logs & scores      corrected PDFs
```

## Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│ Layer 1: Authentication (Laravel Auth Middleware)           │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Layer 2: CSRF Protection (Token Validation)                 │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Layer 3: Tenant Authorization (Ownership Check)             │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Layer 4: Input Validation (Request Validation)              │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Layer 5: File Path Sanitization (Storage Facade)            │
└─────────────────────────────────────────────────────────────┘
```
