# Compliance Orchestrator - Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         HTTP LAYER                                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  GET /compliance/orchestrator          POST /compliance/orchestrator/run │
│  (Dashboard)                           (Execute Form)                    │
│                                                                           │
│  GET /compliance/orchestrator/logs     GET /compliance/orchestrator/stats│
│  (Execution Logs)                      (Statistics)                      │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│              ComplianceOrchestratorController                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  • Receives HTTP requests                                                │
│  • Validates parameters                                                  │
│  • Enforces multi-tenant isolation                                       │
│  • Delegates to orchestrator                                             │
│  • Returns structured responses                                          │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                    ComplianceOrchestrator                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 1. INPUT VALIDATION                                              │   │
│  │    • Validate tenant_id, branch_id, month, year, form_code      │   │
│  │    • Verify form exists in master                               │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 2. VALIDATION PIPELINE                                           │   │
│  │    ┌─────────────────────────────────────────────────────────┐   │   │
│  │    │ StrictDataValidator                                     │   │   │
│  │    │ • Validate tenant setup                                 │   │   │
│  │    │ • Validate branch setup                                 │   │   │
│  │    └─────────────────────────────────────────────────────────┘   │   │
│  │                            ↓                                      │   │
│  │    ┌─────────────────────────────────────────────────────────┐   │   │
│  │    │ PayrollValidationGuard                                  │   │   │
│  │    │ • Validate payroll consistency                          │   │   │
│  │    │ • Check wage components                                 │   │   │
│  │    └─────────────────────────────────────────────────────────┘   │   │
│  │                            ↓                                      │   │
│  │    ┌─────────────────────────────────────────────────────────┐   │   │
│  │    │ ProductionValidationGuard                               │   │   │
│  │    │ • Validate subscription type                            │   │   │
│  │    │ • Check branch configuration                            │   │   │
│  │    │ • Verify attendance data                                │   │   │
│  │    │ • Ensure payroll processed                              │   │   │
│  │    └─────────────────────────────────────────────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 3. DATA AGGREGATION                                              │   │
│  │    FormDataAggregator                                            │   │
│  │    • Fetch from configured tables                               │   │
│  │    • Apply tenant/branch filters                                │   │
│  │    • Join related data                                          │   │
│  │    • Return structured dataset                                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 4. GENERATOR SELECTION                                           │   │
│  │    FormGeneratorFactory                                          │   │
│  │    • Resolve correct generator for form_code                    │   │
│  │    • Return generator instance                                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 5. FORM DATA PREPARATION                                         │   │
│  │    Generator.prepareData()                                       │   │
│  │    • Transform raw data to form structure                        │   │
│  │    • Calculate totals                                            │   │
│  │    • Format for rendering                                       │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 6. FORM DATA VALIDATION                                          │   │
│  │    • Validate structure                                          │   │
│  │    • Check required fields                                       │   │
│  │    • Verify no N/A placeholders                                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 7. EXECUTION MODE HANDLER                                        │   │
│  │                                                                  │   │
│  │    ┌─────────────────┬──────────────────┬──────────────────┐    │   │
│  │    │                 │                  │                  │    │   │
│  │    ↓                 ↓                  ↓                  ↓    │   │
│  │  PREVIEW           PDF                BATCH              ERROR  │   │
│  │  ────────          ───                ─────              ─────  │   │
│  │  • Render          • Generate        • Generate         • Log   │   │
│  │    Blade             PDF via           PDF              • Return│   │
│  │  • Return            DomPDF          • Store in           error │   │
│  │    HTML            • Return            filesystem       • Return│   │
│  │  • Return            binary          • Log execution      error │   │
│  │    view              content         • Return path              │   │
│  │                                                                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 8. EXECUTION LOGGING                                             │   │
│  │    • Record status (success/failed)                              │   │
│  │    • Store execution time                                        │   │
│  │    • Count records generated                                     │   │
│  │    • Store error message if failed                               │   │
│  │    • Record execution mode                                       │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                      │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │ 9. RETURN RESULT                                                 │   │
│  │    • Status (success/failed)                                     │   │
│  │    • Execution time                                              │   │
│  │    • Records generated                                           │   │
│  │    • Mode-specific result                                        │   │
│  │    • Error message if failed                                     │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                    Execution Logging Layer                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  compliance_execution_logs Table                                         │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ id | tenant_id | branch_id | batch_id | form_code | status     │    │
│  │ execution_time | records_generated | error_message | mode      │    │
│  │ created_at | updated_at                                        │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                                                                           │
│  Indexes:                                                                │
│  • (tenant_id, batch_id)                                                │
│  • (batch_id, form_code)                                                │
│  • (status)                                                              │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
┌──────────────────┐
│  HTTP Request    │
│  (Form Execution)│
└────────┬─────────┘
         │
         ↓
┌──────────────────────────────────────┐
│ ComplianceOrchestratorController     │
│ • Validate request                   │
│ • Enforce multi-tenant isolation     │
│ • Call orchestrator.execute()        │
└────────┬─────────────────────────────┘
         │
         ↓
┌──────────────────────────────────────┐
│ ComplianceOrchestrator               │
│ • Run validation pipeline            │
│ • Aggregate data                     │
│ • Select generator                   │
│ • Prepare form data                  │
│ • Execute mode handler               │
│ • Log execution                      │
└────────┬─────────────────────────────┘
         │
         ├─────────────────────────────────────┐
         │                                     │
         ↓                                     ↓
    ┌─────────────┐                  ┌──────────────────┐
    │ Validators  │                  │ Data Aggregator  │
    ├─────────────┤                  ├──────────────────┤
    │ • Strict    │                  │ • Query tables   │
    │ • Payroll   │                  │ • Apply filters  │
    │ • Production│                  │ • Join data      │
    └─────────────┘                  └──────────────────┘
         │                                     │
         └─────────────────────────────────────┘
                     │
                     ↓
         ┌──────────────────────────┐
         │ FormGeneratorFactory     │
         │ • Resolve generator      │
         │ • Return instance        │
         └──────────────┬───────────┘
                        │
                        ↓
         ┌──────────────────────────┐
         │ Generator.prepareData()  │
         │ • Transform data         │
         │ • Calculate totals       │
         │ • Format output          │
         └──────────────┬───────────┘
                        │
                        ↓
         ┌──────────────────────────┐
         │ Mode Handler             │
         ├──────────────────────────┤
         │ Preview → Blade View     │
         │ PDF → DomPDF             │
         │ Batch → File Storage     │
         └──────────────┬───────────┘
                        │
                        ↓
         ┌──────────────────────────┐
         │ Execution Logger         │
         │ • Record status          │
         │ • Store timing           │
         │ • Count records          │
         │ • Log errors             │
         └──────────────┬───────────┘
                        │
                        ↓
         ┌──────────────────────────┐
         │ Return Result            │
         │ • Status                 │
         │ • Execution time         │
         │ • Records generated      │
         │ • Mode-specific data     │
         └──────────────────────────┘
```

## Multi-Tenant Isolation

```
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Request                              │
│  POST /compliance/orchestrator/run                           │
│  {form_code, branch_id, month, year, mode}                  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              Extract tenant_id from Auth::user()             │
│              (Automatic - No user input)                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              Verify branch belongs to tenant                 │
│  Branch::where('id', $branchId)                             │
│         ->where('tenant_id', $tenantId)                     │
│         ->firstOrFail()                                      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              All queries include tenant_id filter            │
│  • Data aggregation                                          │
│  • Execution logging                                         │
│  • Batch verification                                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              Result returned to authenticated user           │
│              (No cross-tenant data leakage)                  │
└─────────────────────────────────────────────────────────────┘
```

## Execution Mode Comparison

```
┌──────────────────────────────────────────────────────────────────┐
│                    PREVIEW MODE                                   │
├──────────────────────────────────────────────────────────────────┤
│ Input:  Form data                                                │
│ Process: Render Blade template to HTML                           │
│ Output:  HTML string                                             │
│ Use:     Display in browser, preview before generation           │
│ Time:    1-2 seconds                                             │
│ Storage: None (in-memory)                                        │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                    PDF MODE                                       │
├──────────────────────────────────────────────────────────────────┤
│ Input:  Form data                                                │
│ Process: Render Blade → HTML → DomPDF → Binary                  │
│ Output:  PDF binary content                                      │
│ Use:     Download as file                                        │
│ Time:    2-3 seconds                                             │
│ Storage: None (streamed to client)                               │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                    BATCH MODE                                     │
├──────────────────────────────────────────────────────────────────┤
│ Input:  Form data, batch_id                                      │
│ Process: Generate PDF → Store in filesystem                      │
│ Output:  File path                                               │
│ Use:     Store for later retrieval, batch processing             │
│ Time:    1-2 seconds                                             │
│ Storage: storage/app/generated_forms/{tenant}/{batch}/{form}.pdf │
└──────────────────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
┌─────────────────────────────────────────┐
│         Orchestrator.execute()           │
└────────────────┬────────────────────────┘
                 │
                 ↓
         ┌───────────────┐
         │ Try Block     │
         └───────┬───────┘
                 │
         ┌───────┴────────────────────────────────┐
         │                                        │
         ↓                                        ↓
    ┌─────────────┐                      ┌──────────────┐
    │ Success     │                      │ Exception    │
    ├─────────────┤                      ├──────────────┤
    │ • Log       │                      │ • Catch      │
    │   success   │                      │ • Calculate  │
    │ • Return    │                      │   time       │
    │   result    │                      │ • Log failed │
    └─────────────┘                      │ • Return     │
         │                               │   error      │
         │                               └──────────────┘
         │                                      │
         └──────────────┬───────────────────────┘
                        │
                        ↓
         ┌──────────────────────────────┐
         │ ComplianceExecutionLog       │
         │ • status: success/failed     │
         │ • execution_time            │
         │ • records_generated         │
         │ • error_message (if failed) │
         │ • execution_mode            │
         └──────────────────────────────┘
```

## Performance Characteristics

```
┌─────────────────────────────────────────────────────────────┐
│              Execution Time Breakdown                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Validation Pipeline        ████░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ (100-200ms)                                                 │
│                                                              │
│ Data Aggregation           ██████░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ (150-300ms)                                                 │
│                                                              │
│ Form Data Preparation      ███░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ (50-100ms)                                                  │
│                                                              │
│ Preview Rendering          ████████░░░░░░░░░░░░░░░░░░░░░░░ │
│ (300-500ms)                                                 │
│                                                              │
│ PDF Generation             ██████████░░░░░░░░░░░░░░░░░░░░░ │
│ (500-1000ms)                                                │
│                                                              │
│ File Storage               ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ (50-100ms)                                                  │
│                                                              │
│ Execution Logging          █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ (50-100ms)                                                  │
│                                                              │
│ ─────────────────────────────────────────────────────────── │
│ Total (Preview):  1-2 seconds                               │
│ Total (PDF):      2-3 seconds                               │
│ Total (Batch):    1-2 seconds                               │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## Service Dependencies

```
ComplianceOrchestrator
├── StrictDataValidator
├── PayrollValidationGuard
├── ProductionValidationGuard
├── FormDataAggregator
│   ├── Database (via Eloquent)
│   └── Config (compliance_forms)
├── FormGeneratorFactory
│   └── Specific Generators
│       ├── PayrollBasedFormGenerator
│       ├── ContractorBasedFormGenerator
│       ├── IncidentBasedFormGenerator
│       ├── InspectionBasedFormGenerator
│       └── MasterRegisterFormGenerator
├── View (Blade rendering)
├── Storage (File system)
├── Database (Logging)
└── Carbon (Date/time)
```

## Deployment Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Production Environment                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Web Server (Nginx/Apache)                            │   │
│  │ • Routes HTTP requests                               │   │
│  │ • Serves static assets                               │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Laravel Application                                  │   │
│  │ • ComplianceOrchestratorController                   │   │
│  │ • ComplianceOrchestrator Service                     │   │
│  │ • Validators & Aggregators                           │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Database (MySQL/PostgreSQL)                          │   │
│  │ • compliance_execution_logs                          │   │
│  │ • compliance_forms_master                            │   │
│  │ • compliance_execution_batches                       │   │
│  │ • Other compliance tables                            │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ File Storage                                         │   │
│  │ • storage/app/generated_forms/                       │   │
│  │ • PDF files stored here                              │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```
