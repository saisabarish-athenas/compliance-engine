# Compliance Orchestrator Implementation Guide

## Overview

The Compliance Orchestrator is the central execution engine for all compliance workflows in the Multi-Tenant Labour Compliance Automation Platform. It orchestrates data fetching, form generation, preview rendering, PDF generation, and inspection pack creation.

## Architecture

```
User Request
    ↓
ComplianceOrchestrator.execute()
    ↓
Subscription Validation (FULL required for preview/pdf/inspection_pack)
    ↓
Input Validation & Tenant/Branch Verification
    ↓
Validation Pipeline (Tenant, Branch, Production)
    ↓
API Service (FormApiServiceFactory)
    ↓
Form Generator (FormGeneratorFactory)
    ↓
Execution Mode Handler
    ├─ preview: Blade rendering
    ├─ pdf: PDF content return
    ├─ batch: PDF storage
    └─ inspection_pack: ZIP creation
    ↓
Execution Logging
    ↓
Response
```

## Key Components

### 1. ComplianceOrchestrator
**Location:** `app/Services/Compliance/ComplianceOrchestrator.php`

Central orchestrator that:
- Validates subscription access
- Coordinates data fetching via API services
- Manages form generation
- Handles execution modes
- Logs all executions

**Execution Modes:**
- `preview`: Returns HTML for form preview (requires FULL subscription)
- `pdf`: Returns PDF content (requires FULL subscription)
- `batch`: Stores PDF in storage (requires FULL subscription)
- `inspection_pack`: Creates ZIP archive (requires FULL subscription)

### 2. FormApiServiceFactory
**Location:** `app/Services/Compliance/FormApis/FormApiServiceFactory.php`

Factory for resolving API services by form code. Maps form codes to their corresponding API service classes.

### 3. BaseFormApiService
**Location:** `app/Services/Compliance/FormApis/BaseFormApiService.php`

Abstract base class for all form API services providing:
- Period initialization
- Tenant/branch details retrieval
- Validation methods
- Common data formatting

### 4. Form-Specific API Services
**Location:** `app/Services/Compliance/FormApis/`

Each form has a dedicated API service:
- `FormBApiService`: Wage Register
- `Form10ApiService`: Overtime Register
- `Form25ApiService`: Muster Roll
- `FormAApiService`: Employee Register
- `FormCApiService`: Deduction Register
- `FormDApiService`: Attendance Register
- `FormXIIApiService`: Contractor Master
- `FormXIIIApiService`: Contract Labour Register
- `FormXVIApiService`: Contract Labour Muster Roll
- `FormXVIIApiService`: Contract Labour Wage Register
- `FormXIXApiService`: Contract Labour Wage Slip
- `FormXXApiService`: Deduction Register (Damage)
- `FormXXIApiService`: Fines Register
- `FormXXIIIApiService`: Overtime Register (Contract Labour)

## Execution Flow

### Step 1: Subscription Validation
```php
$this->validateSubscriptionAccess($tenantId, $mode);
```
- Only FULL subscription can access preview, pdf, and inspection_pack modes
- Throws exception if subscription is insufficient

### Step 2: Input Validation
```php
$this->validateInputs($tenantId, $branchId, $month, $year, $formCode);
```
- Validates tenant_id, branch_id, month, year, form_code
- Verifies form exists in ComplianceFormsMaster

### Step 3: Validation Pipeline
```php
$this->runValidationPipeline($tenantId, $branchId, $month, $year);
```
- Validates tenant setup
- Validates branch setup
- Validates production requirements (non-blocking)

### Step 4: Data Fetching
```php
$apiService = FormApiServiceFactory::make($formCode);
if ($apiService) {
    $rawData = $apiService->fetch($tenantId, $branchId, $month, $year);
} else {
    $rawData = $this->aggregator->aggregate($formCode, $tenantId, $branchId, $month, $year);
}
```
- Attempts to use API service if available
- Falls back to FormDataAggregator if no API service exists

### Step 5: Form Generation
```php
$generator = $this->factory::make($formCode);
$formData = $this->prepareFormData($generator, $rawData);
```
- Gets appropriate generator from factory
- Prepares form data using generator's prepareData method

### Step 6: Execution Mode Handler
Based on mode, executes:
- **preview**: Renders Blade template and returns HTML
- **pdf**: Generates PDF and returns content
- **batch**: Generates PDF and stores in storage
- **inspection_pack**: Generates PDF and creates ZIP archive

### Step 7: Execution Logging
```php
$this->logExecution($tenantId, $branchId, $batchId, $formCode, $status, $executionTime, $recordsGenerated, $errorMessage, $mode);
```
- Logs to `compliance_execution_logs` table
- Records execution time, record count, status, and errors

## Database Schema

### compliance_execution_logs
```sql
CREATE TABLE compliance_execution_logs (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    batch_id BIGINT NOT NULL,
    form_code VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'success', 'failed', 'preview'),
    execution_time INT (milliseconds),
    records_generated INT DEFAULT 0,
    error_message TEXT,
    execution_mode VARCHAR(50) DEFAULT 'batch',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (batch_id) REFERENCES compliance_execution_batches(id),
    INDEX (tenant_id, batch_id),
    INDEX (batch_id, form_code),
    INDEX (status)
);
```

## Usage Examples

### Preview Form
```php
$orchestrator = app(ComplianceOrchestrator::class);
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview',
    batchId: 1
);

// Returns: ['status' => 'success', 'result' => ['html' => '...', 'is_nil' => false, 'rows_count' => 10]]
```

### Generate PDF
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'pdf',
    batchId: 1
);

// Returns: ['status' => 'success', 'result' => ['content' => '...', 'size' => 12345, 'mime_type' => 'application/pdf']]
```

### Batch Processing
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'batch',
    batchId: 1
);

// Returns: ['status' => 'success', 'result' => ['file_path' => 'generated_forms/1/1/FORM_B.pdf', 'file_size' => 12345, 'stored' => true]]
```

### Inspection Pack
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'inspection_pack',
    batchId: 1
);

// Returns: ['status' => 'success', 'result' => ['zip_path' => 'compliance_inspection_packs/1/1/inspection_pack_1_1234567890.zip', 'zip_size' => 12345, 'file_count' => 1, 'created' => true]]
```

## Subscription Access Control

### FULL Subscription
- Can access all modes: preview, pdf, batch, inspection_pack
- Can generate forms
- Can download inspection packs

### MINIMAL Subscription
- Can only access batch mode
- Cannot preview forms
- Cannot download inspection packs
- Cannot generate PDFs

## Multi-Tenant Safety

All queries enforce:
- `tenant_id` filtering
- `branch_id` filtering
- Data isolation between tenants

Example:
```php
$rows = DB::table('workforce_payroll_entry as pe')
    ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
    ->where('e.tenant_id', $tenantId)  // Tenant isolation
    ->where('e.branch_id', $branchId)  // Branch isolation
    ->get();
```

## Error Handling

All errors are:
- Caught and logged
- Returned in response with status 'failed'
- Recorded in compliance_execution_logs

Example error response:
```php
[
    'status' => 'failed',
    'mode' => 'preview',
    'form_code' => 'FORM_B',
    'execution_time' => 150,
    'error' => 'Subscription access denied. Mode \'preview\' requires FULL subscription'
]
```

## Performance Considerations

1. **API Services**: Optimized queries with proper indexing
2. **Chunking**: Large datasets chunked to reduce memory usage
3. **Caching**: Tenant/branch details cached during execution
4. **Logging**: Asynchronous logging to avoid blocking

## Adding New Forms

To add a new form to the orchestrator:

1. Create API service in `app/Services/Compliance/FormApis/`:
```php
class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        // Implement data fetching
    }
}
```

2. Register in `FormApiServiceFactory`:
```php
protected static array $apiServices = [
    'FORM_XXX' => FormXXXApiService::class,
];
```

3. Ensure generator exists in `FormGeneratorFactory`

4. Create Blade template in `resources/views/compliance/forms/form_xxx.blade.php`

## Troubleshooting

### Issue: "Subscription access denied"
- Check tenant subscription_type in tenants table
- Ensure it's set to 'FULL' for preview/pdf/inspection_pack modes

### Issue: "Form not found in master"
- Verify form_code exists in compliance_forms_master table
- Check form_code spelling

### Issue: "No generator found"
- Verify generator is registered in FormGeneratorFactory
- Check generator class exists

### Issue: "View not found"
- Verify Blade template exists at `resources/views/compliance/forms/{form_code}.blade.php`
- Check template path matches form code

## Next Steps

1. Implement remaining API services for all forms
2. Standardize all generators to return consistent structure
3. Add caching layer for frequently accessed data
4. Implement batch execution for multiple forms
5. Add webhook notifications for completion
