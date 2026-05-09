# Compliance Orchestrator Layer - Implementation Guide

## Overview

The **Compliance Orchestrator** is a centralized service layer that coordinates the entire compliance generation workflow. It replaces scattered business logic across controllers and services with a unified, modular orchestration pattern.

## Architecture

```
HTTP Request
    ↓
ComplianceOrchestratorController
    ↓
ComplianceOrchestrator (Main Service)
    ├─ Input Validation
    ├─ Validation Pipeline
    │  ├─ StrictDataValidator
    │  ├─ PayrollValidationGuard
    │  └─ ProductionValidationGuard
    ├─ Data Aggregation (FormDataAggregator)
    ├─ Generator Selection (FormGeneratorFactory)
    ├─ Form Data Preparation
    ├─ Execution Mode Handler
    │  ├─ Preview Mode → Blade View
    │  ├─ PDF Mode → DomPDF Output
    │  └─ Batch Mode → File Storage
    ├─ Execution Logging
    └─ Response
```

## Components

### 1. ComplianceOrchestrator Service

**Location:** `app/Services/Compliance/ComplianceOrchestrator.php`

**Responsibilities:**
- Coordinate entire workflow
- Run validation pipeline
- Aggregate form data
- Select appropriate generator
- Execute in specified mode
- Log all executions

**Key Methods:**

```php
execute(
    int $tenantId,
    int $branchId,
    int $month,
    int $year,
    string $formCode,
    string $mode = 'batch',
    ?int $batchId = null
): array
```

Executes compliance workflow and returns structured result.

**Execution Modes:**

- **preview**: Returns HTML view for browser display
- **pdf**: Returns PDF content for download
- **batch**: Stores PDF in filesystem and logs execution

### 2. ComplianceOrchestratorController

**Location:** `app/Http/Controllers/Compliance/ComplianceOrchestratorController.php`

**Endpoints:**

#### Dashboard
```
GET /compliance/orchestrator
```
Displays orchestrator UI with form selector, branch selector, period selector, and execution mode options.

#### Run Execution
```
POST /compliance/orchestrator/run
```

**Request:**
```json
{
    "form_code": "FORM_B",
    "branch_id": 1,
    "month": 3,
    "year": 2024,
    "mode": "preview",
    "batch_id": null
}
```

**Response (Preview Mode):**
```json
{
    "status": "success",
    "html": "<html>...</html>",
    "is_nil": false,
    "rows_count": 25
}
```

**Response (PDF Mode):**
Binary PDF content with appropriate headers.

**Response (Batch Mode):**
```json
{
    "status": "success",
    "file_path": "generated_forms/1/123/FORM_B.pdf",
    "file_size": 45678,
    "execution_time": 1250,
    "records_generated": 25
}
```

#### Get Execution Logs
```
GET /compliance/orchestrator/logs?batch_id=123&form_code=FORM_B
```

**Response:**
```json
{
    "status": "success",
    "logs": [
        {
            "id": 1,
            "form_code": "FORM_B",
            "status": "success",
            "execution_time": 1250,
            "records_generated": 25,
            "execution_mode": "batch",
            "created_at": "2024-03-20T10:30:00Z"
        }
    ],
    "statistics": {
        "total_executions": 5,
        "successful": 4,
        "failed": 1,
        "total_execution_time": 5500,
        "total_records": 100,
        "average_time": 1100,
        "by_mode": {
            "batch": {"count": 3, "successful": 3, "failed": 0},
            "preview": {"count": 2, "successful": 1, "failed": 1}
        }
    }
}
```

#### Get Execution Statistics
```
GET /compliance/orchestrator/stats?batch_id=123
```

**Response:**
```json
{
    "status": "success",
    "statistics": {
        "total_executions": 5,
        "successful": 4,
        "failed": 1,
        "total_execution_time": 5500,
        "total_records": 100,
        "average_time": 1100,
        "by_mode": {...}
    }
}
```

### 3. Database Table: compliance_execution_logs

**Columns:**
- `id` - Primary key
- `tenant_id` - Multi-tenant isolation
- `branch_id` - Branch context
- `batch_id` - Associated batch
- `form_code` - Form identifier
- `status` - pending, processing, success, failed, preview
- `execution_time` - Milliseconds
- `records_generated` - Count of records
- `error_message` - Error details if failed
- `execution_mode` - preview, pdf, batch
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- `(tenant_id, batch_id)`
- `(batch_id, form_code)`
- `(status)`

### 4. Validation Pipeline

The orchestrator runs three validation steps:

#### StrictDataValidator
- Validates tenant setup (name, establishment details)
- Validates branch setup (unit name, address)
- Validates form data structure
- Ensures no N/A placeholders in data

#### PayrollValidationGuard
- Validates payroll consistency
- Ensures days worked matches wage components
- Validates overtime hours vs wages
- Prevents legal violations

#### ProductionValidationGuard
- Validates subscription type
- Checks branch configuration
- Verifies attendance data exists
- Ensures payroll is processed

## Usage Examples

### 1. Preview Form in Browser

```php
$orchestrator = app(ComplianceOrchestrator::class);

$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview'
);

if ($result['status'] === 'success') {
    return view('form-preview', [
        'html' => $result['result']['html']
    ]);
}
```

### 2. Generate PDF

```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'pdf'
);

if ($result['status'] === 'success') {
    return response($result['result']['content'], 200)
        ->header('Content-Type', 'application/pdf');
}
```

### 3. Batch Execution

```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'batch',
    batchId: 123
);

if ($result['status'] === 'success') {
    $filePath = $result['result']['file_path'];
    // File stored at storage/app/{$filePath}
}
```

### 4. Get Execution Logs

```php
$logs = $orchestrator->getExecutionLogs(batchId: 123);
$stats = $orchestrator->getExecutionStats(batchId: 123);
```

## Testing

### Run Orchestrator Test Command

```bash
# Test all active forms
php artisan compliance:orchestrator-test

# Test specific form
php artisan compliance:orchestrator-test --form-code=FORM_B

# Test specific tenant and branch
php artisan compliance:orchestrator-test --tenant-id=1 --branch-id=1

# Test specific period
php artisan compliance:orchestrator-test --month=3 --year=2024

# Test specific mode
php artisan compliance:orchestrator-test --mode=pdf
```

**Output:**
```
=== Compliance Orchestrator Test ===
Tenant: Acme Corp (ID: 1)
Branch: Main Unit (ID: 1)
Period: 3/2024
Mode: preview

Testing 5 form(s)...
 ████████████████████████████████████████ 5/5 [100%] -- 2.5s

=== Execution Results ===

| Form Code | Status      | Time    | Records | Error |
|-----------|-------------|---------|---------|-------|
| FORM_B    | ✓ Success   | 1250ms  | 25      |       |
| FORM_10   | ✓ Success   | 980ms   | 18      |       |
| FORM_25   | ✓ Success   | 1100ms  | 22      |       |
| FORM_XII  | ✗ Failed    | 450ms   | 0       | Data... |
| FORM_XVII | ✓ Success   | 1200ms  | 20      |       |

Summary:
  Successful: 4
  Failed: 1
  Total Execution Time: 4980ms
  Total Records Generated: 85
  Average Time per Form: 1245ms
```

## Routes

```php
// Dashboard
GET /compliance/orchestrator

// Execution
POST /compliance/orchestrator/run

// Logs and Statistics
GET /compliance/orchestrator/logs
GET /compliance/orchestrator/stats
```

## Multi-Tenant Safety

All operations enforce tenant and branch isolation:

```php
// Verify batch belongs to tenant
$batch = ComplianceExecutionBatch::where('id', $batchId)
    ->where('tenant_id', $tenantId)
    ->firstOrFail();

// All queries include tenant_id filter
DB::table('compliance_execution_logs')
    ->where('tenant_id', $tenantId)
    ->where('batch_id', $batchId)
```

## Error Handling

The orchestrator returns structured error responses:

```json
{
    "status": "failed",
    "form_code": "FORM_B",
    "error": "Dataset workforce_fines missing for FORM_XX",
    "execution_time": 450
}
```

Errors are logged to `compliance_execution_logs` table with:
- `status`: "failed"
- `error_message`: Full error text
- `execution_time`: Time before failure

## Performance Considerations

1. **Execution Logging**: Async logging to prevent blocking
2. **Data Aggregation**: Chunked queries for large datasets
3. **PDF Generation**: Cached templates where possible
4. **Database Indexes**: Optimized for common queries

## Integration with Existing System

The orchestrator integrates seamlessly:

- ✅ Uses existing FormGeneratorFactory
- ✅ Uses existing FormDataAggregator
- ✅ Uses existing validators
- ✅ Uses existing Blade templates
- ✅ Uses existing DomPDF setup
- ✅ Maintains backward compatibility

## Future Enhancements

1. **Async Execution**: Queue-based batch processing
2. **Caching**: Cache aggregated data between executions
3. **Webhooks**: Notify external systems on completion
4. **Retry Logic**: Automatic retry on transient failures
5. **Performance Metrics**: Track and optimize slow forms

## Troubleshooting

### Form Not Found
```
Error: No generator found for FORM_XX
```
**Solution:** Verify form code exists in `compliance_forms_master` table.

### Validation Failed
```
Error: Tenant validation failed: Missing establishment name
```
**Solution:** Configure tenant details in `/compliance/settings`.

### Data Missing
```
Error: Dataset workforce_fines missing for FORM_XX
```
**Solution:** Ensure required data is uploaded or generated for the period.

### PDF Generation Failed
```
Error: PDF generation returned empty content
```
**Solution:** Check DomPDF configuration and Blade template syntax.
