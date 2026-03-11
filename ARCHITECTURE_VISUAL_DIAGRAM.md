# COMPLIANCE ENGINE - CORRECTED ARCHITECTURE DIAGRAM

## SYSTEM FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE (DASHBOARD)                          │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │ Batch ID │ Section │ Period │ Status │ Audit Score │ Certification │   │
│  │    221   │ Factories│ 01/24  │ Done   │     85      │   Not Cert    │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│  Actions: [Preview] [Fix Issues] [Inspection Pack]                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    ↑
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
        ┌───────────▼──────────┐      ┌────────────▼──────────┐
        │  AUDIT LOGS TABLE    │      │ CERTIFICATION LOGS    │
        │  ┌────────────────┐  │      │ ┌──────────────────┐  │
        │  │ batch_id: 221  │  │      │ │ batch_id: 221    │  │
        │  │ form_code: B   │  │      │ │ form_code: BATCH │  │
        │  │ audit_score:85 │  │      │ │ cert_score: 85   │  │
        │  │ status: passed │  │      │ │ certified: false │  │
        │  └────────────────┘  │      │ └──────────────────┘  │
        └──────────────────────┘      └──────────────────────┘
                    ↑                           ↑
                    │                           │
        ┌───────────┴───────────┐   ┌──────────┴──────────┐
        │                       │   │                     │
        │  AUDIT ENGINE         │   │ CERTIFICATION ENGINE│
        │  ┌─────────────────┐  │   │ ┌────────────────┐  │
        │  │ Validate header │  │   │ │ Validate forms │  │
        │  │ Validate rows   │  │   │ │ Cross-form val │  │
        │  │ Apply rules     │  │   │ │ Calculate score│  │
        │  │ Calculate score │  │   │ │ Determine cert │  │
        │  └─────────────────┘  │   │ └────────────────┘  │
        └───────────┬───────────┘   └──────────┬──────────┘
                    │                           │
                    └───────────────┬───────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   FORM GENERATION COMPLETE    │
                    │  (All forms generated & stored)│
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   BATCH FORM RECORDS TABLE    │
                    │  ┌──────────────────────────┐ │
                    │  │ batch_id: 221            │ │
                    │  │ form_code: FORM_B        │ │
                    │  │ file_path: .../B.pdf     │ │
                    │  │ status: success          │ │
                    │  └──────────────────────────┘ │
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   FORM GENERATION ENGINE      │
                    │  ┌──────────────────────────┐ │
                    │  │ 1. Get raw data          │ │
                    │  │ 2. Prepare data          │ │
                    │  │ 3. Generate PDF          │ │
                    │  │ 4. Store file            │ │
                    │  └──────────────────────────┘ │
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   COMPLIANCE DATA SERVICE     │
                    │  ┌──────────────────────────┐ │
                    │  │ normalizeData()          │ │
                    │  │ ├─ header               │ │
                    │  │ ├─ rows/entries         │ │
                    │  │ ├─ totals               │ │
                    │  │ └─ is_nil flag          │ │
                    │  └──────────────────────────┘ │
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   FORM BUILDERS               │
                    │  ┌──────────────────────────┐ │
                    │  │ FormBBuilder             │ │
                    │  │ Form10Builder            │ │
                    │  │ Form12Builder            │ │
                    │  │ ... (36 forms)           │ │
                    │  └──────────────────────────┘ │
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   REPOSITORY LAYER            │
                    │  ┌──────────────────────────┐ │
                    │  │ EmployeeRepository       │ │
                    │  │ PayrollRepository        │ │
                    │  │ AttendanceRepository     │ │
                    │  │ ContractorRepository     │ │
                    │  │ IncidentRepository       │ │
                    │  │ BonusRepository          │ │
                    │  │ DeductionRepository      │ │
                    │  └──────────────────────────┘ │
                    └───────────────┬───────────────┘
                                    │
                    ┌───────────────▼───────────────┐
                    │   DATABASE LAYER              │
                    │  ┌──────────────────────────┐ │
                    │  │ workforce_employee       │ │
                    │  │ workforce_payroll_entry  │ │
                    │  │ workforce_attendance     │ │
                    │  │ contract_labour_deploy   │ │
                    │  │ incident_documents       │ │
                    │  │ branches                 │ │
                    │  │ tenants                  │ │
                    │  └──────────────────────────┘ │
                    └───────────────────────────────┘
```

---

## CORRECTION ENGINE FLOW

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER CLICKS "FIX ISSUES"                     │
└────────────────────────────┬──────────────────────────────────┘
                             │
                ┌────────────▼────────────┐
                │ CORRECTION ENGINE       │
                │ ┌────────────────────┐  │
                │ │ Get violations     │  │
                │ │ Auto-fetch values  │  │
                │ │ Prompt user if     │  │
                │ │ needed             │  │
                │ └────────────────────┘  │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ REGENERATE PDF          │
                │ ┌────────────────────┐  │
                │ │ Merge corrections  │  │
                │ │ Generate new PDF   │  │
                │ │ Store file         │  │
                │ └────────────────────┘  │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ RE-AUDIT IMMEDIATELY    │
                │ ┌────────────────────┐  │
                │ │ Audit new data     │  │
                │ │ Calculate score    │  │
                │ │ Update audit log   │  │
                │ └────────────────────┘  │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ DASHBOARD UPDATES       │
                │ ┌────────────────────┐  │
                │ │ New audit score    │  │
                │ │ New audit status   │  │
                │ │ Batch avg updated  │  │
                │ └────────────────────┘  │
                └────────────────────────┘
```

---

## SUBSCRIPTION LOGIC FLOW

```
┌──────────────────────────────────────────────────────────────────┐
│                    BATCH PROCESSING STARTS                       │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                ┌────────────▼────────────┐
                │ CHECK SUBSCRIPTION TYPE │
                └────────────┬────────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
        ┌───────▼────────┐        ┌──────▼────────┐
        │ FULL           │        │ MINIMAL        │
        │ SUBSCRIPTION   │        │ SUBSCRIPTION   │
        └───────┬────────┘        └──────┬────────┘
                │                        │
        ┌───────▼────────┐        ┌──────▼────────┐
        │ VALIDATE       │        │ SKIP PAYROLL  │
        │ PAYROLL EXISTS │        │ VALIDATION    │
        └───────┬────────┘        └──────┬────────┘
                │                        │
        ┌───────▼────────┐        ┌──────▼────────┐
        │ FETCH FROM     │        │ FETCH FROM    │
        │ DATABASE       │        │ MANUAL UPLOAD │
        │ ├─ Employees   │        │ ├─ CSV files  │
        │ ├─ Payroll     │        │ ├─ Excel      │
        │ ├─ Attendance  │        │ └─ Parsed     │
        │ └─ Incidents   │        │   data        │
        └───────┬────────┘        └──────┬────────┘
                │                        │
                └────────────┬───────────┘
                             │
                ┌────────────▼────────────┐
                │ SAME FORM GENERATION    │
                │ PIPELINE FOR BOTH       │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ SAME AUDIT ENGINE       │
                │ FOR BOTH                │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ SAME CERTIFICATION      │
                │ ENGINE FOR BOTH         │
                └────────────────────────┘
```

---

## DATA NORMALIZATION FLOW

```
┌──────────────────────────────────────────────────────────────────┐
│                    BUILDER RETURNS DATA                          │
│  {                                                               │
│    'rows': [...],                                                │
│    'totals': {...},                                              │
│    'period': '01/2024'                                           │
│  }                                                               │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                ┌────────────▼────────────┐
                │ NORMALIZE DATA          │
                │ ┌────────────────────┐  │
                │ │ Ensure header      │  │
                │ │ Ensure rows        │  │
                │ │ Ensure entries     │  │
                │ │ Ensure totals      │  │
                │ │ Ensure period      │  │
                │ │ Add is_nil flag    │  │
                │ └────────────────────┘  │
                └────────────┬────────────┘
                             │
        ┌────────────────────▼────────────────────┐
        │  GUARANTEED DATA STRUCTURE              │
        │  {                                      │
        │    'header': {                          │
        │      'tenant': {'name': '...'},         │
        │      'owner_name': '...',               │
        │      'wage_period': 'Monthly',          │
        │      'period': '01/2024'                │
        │    },                                   │
        │    'rows': [...],                       │
        │    'entries': [...],  // same as rows   │
        │    'totals': {...},                     │
        │    'period': '01/2024',                 │
        │    'is_nil': false                      │
        │  }                                      │
        └────────────────────┬────────────────────┘
                             │
                ┌────────────▼────────────┐
                │ BLADE TEMPLATE RENDERS  │
                │ WITHOUT ERRORS          │
                └────────────────────────┘
```

---

## INSPECTION PACK GENERATION FLOW

```
┌──────────────────────────────────────────────────────────────────┐
│              USER CLICKS "INSPECTION PACK"                       │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                ┌────────────▼────────────┐
                │ CHECK CERTIFICATION     │
                │ Score ≥ 70?             │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ FETCH BATCH FORMS       │
                │ WHERE status='success'  │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ FILTER OUT FAILED       │
                │ AUDITS                  │
                │ (check audit_logs)      │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ CREATE ZIP FILE         │
                │ ├─ FORM_B.pdf           │
                │ ├─ FORM_10.pdf          │
                │ ├─ FORM_12.pdf          │
                │ └─ ...                  │
                └────────────┬────────────┘
                             │
                ┌────────────▼────────────┐
                │ STREAM DOWNLOAD         │
                │ DELETE TEMP FILE        │
                └────────────────────────┘
```

---

## DATABASE SCHEMA RELATIONSHIPS

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPLIANCE TABLES                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  compliance_execution_batches                                   │
│  ├─ id (PK)                                                     │
│  ├─ tenant_id (FK)                                              │
│  ├─ section_id (FK)                                             │
│  ├─ period_from                                                 │
│  ├─ period_to                                                   │
│  ├─ form_ids (JSON)                                             │
│  ├─ status                                                      │
│  └─ created_at                                                  │
│                                                                 │
│  ↓ (1:N)                                                        │
│                                                                 │
│  compliance_batch_forms                                         │
│  ├─ id (PK)                                                     │
│  ├─ batch_id (FK)                                               │
│  ├─ form_code                                                   │
│  ├─ file_path                                                   │
│  ├─ status (success/failed)                                     │
│  └─ created_at                                                  │
│                                                                 │
│  ↓ (1:N)                                                        │
│                                                                 │
│  compliance_audit_logs ⭐ CRITICAL                              │
│  ├─ id (PK)                                                     │
│  ├─ batch_id (FK)                                               │
│  ├─ form_code                                                   │
│  ├─ audit_score (0-100)                                         │
│  ├─ status (passed/failed)                                      │
│  ├─ violations (JSON)                                           │
│  └─ updated_at                                                  │
│                                                                 │
│  ↓ (1:N)                                                        │
│                                                                 │
│  compliance_certification_logs ⭐ CRITICAL                      │
│  ├─ id (PK)                                                     │
│  ├─ batch_id (FK)                                               │
│  ├─ form_code (BATCH_SUMMARY for batch-level)                   │
│  ├─ certification_score (0-100)                                 │
│  ├─ certified (boolean)                                         │
│  ├─ violations (JSON)                                           │
│  └─ certified_at                                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## SYSTEM HEALTH INDICATORS

```
✅ HEALTHY STATE
├─ Audit logs created for all forms
├─ Certification logs created for batches
├─ Dashboard displays all metrics
├─ Correction engine updates scores
└─ Inspection pack downloads successfully

⚠️ WARNING STATE
├─ Audit logs missing for some forms
├─ Certification logs delayed
├─ Dashboard shows "Not Audited"
└─ Correction engine slow

❌ CRITICAL STATE
├─ No audit logs created
├─ No certification logs created
├─ Dashboard crashes
├─ Correction engine fails
└─ Inspection pack fails
```

---

## MONITORING DASHBOARD

```
┌─────────────────────────────────────────────────────────────────┐
│                    SYSTEM HEALTH DASHBOARD                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Audit Engine Status:        ✅ RUNNING                         │
│  ├─ Logs Created Today:      127                                │
│  ├─ Average Score:           82.5                               │
│  └─ Failed Forms:            12                                 │
│                                                                 │
│  Certification Engine Status: ✅ RUNNING                         │
│  ├─ Logs Created Today:      18                                 │
│  ├─ Certified Batches:       15                                 │
│  └─ Failed Batches:          3                                  │
│                                                                 │
│  Correction Engine Status:    ✅ RUNNING                         │
│  ├─ Corrections Today:       8                                  │
│  ├─ Success Rate:            87.5%                              │
│  └─ Avg Time:                2.3s                               │
│                                                                 │
│  Dashboard Status:           ✅ HEALTHY                         │
│  ├─ Load Time:               0.8s                               │
│  ├─ Errors:                  0                                  │
│  └─ Users Online:            12                                 │
│                                                                 │
│  Database Status:            ✅ HEALTHY                         │
│  ├─ Connections:            5/10                                │
│  ├─ Query Time:              45ms                               │
│  └─ Disk Usage:              45%                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

This architecture ensures:
- ✅ Automatic audit engine
- ✅ Automatic certification engine
- ✅ Consistent data structures
- ✅ Working correction engine
- ✅ Accurate dashboard display
- ✅ Reliable inspection pack generation
- ✅ Clear subscription logic
