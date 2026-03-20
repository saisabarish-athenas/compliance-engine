# Batch Workflow Refactoring - Implementation Guide

## Overview

This guide provides step-by-step implementation instructions for the refactored batch workflow architecture.

---

## Files Created

### 1. DataAvailabilityEngine.php
**Location:** `app/Services/Compliance/DataAvailabilityEngine.php`

**Purpose:** Check if required data exists for batch processing

**Key Methods:**
- `checkDataAvailability()` - Main method to check all data
- `hasEmployees()` - Check if employees exist
- `hasAttendance()` - Check if attendance exists for period
- `hasPayroll()` - Check if payroll exists for period
- `hasContractLabour()` - Check if contract labour exists
- `hasBonusRecords()` - Check if bonus records exist
- `hasIncidents()` - Check if incidents exist
- `hasHazardRegister()` - Check if hazard register exists
- `getDataSummary()` - Get count of each data type

**Returns:**
```php
[
    'all_data_exists' => bool,
    'missing_data' => array,
    'data_summary' => array
]
```

### 2. BatchReviewService.php
**Location:** `app/Services/Compliance/BatchReviewService.php`

**Purpose:** Prepare data for the review stage

**Key Methods:**
- `prepareReviewData()` - Prepare all data needed for review view

**Returns:**
```php
[
    'batch' => ComplianceExecutionBatch,
    'forms' => Collection,
    'form_count' => int,
    'all_data_exists' => bool,
    'missing_data' => array,
    'data_summary' => array,
    'can_proceed' => bool
]
```

### 3. batch-review.blade.php
**Location:** `resources/views/compliance/batch-review.blade.php`

**Purpose:** Display batch review page with forms and data availability

**Features:**
- Shows batch information
- Lists all detected forms
- Displays data availability status
- Shows data summary table
- Provides action buttons (Cancel, Proceed)

---

## Files Modified

### 1. ComplianceExecutionController.php
**Location:** `app/Http/Controllers/ComplianceExecutionController.php`

**Changes:**
- Modified `createBatch()` method to redirect to review page instead of dashboard
- Added `reviewBatch()` method for Stage 2

**Before:**
```php
return redirect()->route('compliance.dashboard')
    ->with('success', 'Batch created successfully!');
```

**After:**
```php
return redirect()->route('compliance.batch.review', ['batch' => $batch->id])
    ->with('success', 'Batch created successfully! Review forms and data availability.');
```

### 2. routes/compliance.php
**Location:** `routes/compliance.php`

**Changes:**
- Added route for batch review page
- Updated batch download route parameter name

**Added:**
```php
Route::get('/batch/{batch}/review', [ComplianceExecutionController::class, 'reviewBatch'])->name('compliance.batch.review');
```

---

## Workflow Flow

### Stage 1: Create Batch
```
User selects Month + Year
    ↓
POST /compliance/batch/create
    ↓
ComplianceExecutionController::createBatch()
    ↓
BatchOrchestrator::createBatch()
    ├─ Validate branch exists
    ├─ Get default section
    ├─ Detect applicable forms using FrequencyEngine
    ├─ Create batch record (status = 'pending')
    └─ Attach forms to batch (status = 'pending')
    ↓
Redirect to Stage 2
```

### Stage 2: Review Batch (NEW)
```
GET /compliance/batch/{batch}/review
    ↓
ComplianceExecutionController::reviewBatch()
    ↓
BatchReviewService::prepareReviewData()
    ├─ Get batch
    ├─ Get attached forms
    ├─ Check data availability
    └─ Prepare summary
    ↓
Display batch-review.blade.php
    ├─ Show batch info
    ├─ Show detected forms
    ├─ Show data availability
    ├─ Show data summary table
    └─ Show action buttons
    ↓
User clicks "Proceed to Processing"
```

### Stage 3: Process Batch
```
POST /compliance/batch/process/{id}
    ↓
ComplianceExecutionController::processBatch()
    ↓
ComplianceExecutionService::processBatch()
    ├─ Get batch forms
    ├─ For each form:
    │   ├─ Call ComplianceOrchestrator::execute()
    │   ├─ Generate form
    │   ├─ Update file_path
    │   └─ Update status to 'generated'
    ├─ Run audit
    └─ Run certification
    ↓
Redirect to dashboard with success
```

---

## Data Availability Check

### What Gets Checked

1. **Employees** - At least one employee exists
2. **Attendance** - Records exist for the period
3. **Payroll** - Entries exist for the period
4. **Contract Labour** - At least one record exists
5. **Bonus Records** - Records exist for the period
6. **Incidents** - Records exist for the period
7. **Hazard Register** - At least one record exists

### How It Works

```php
$dataCheck = $dataAvailabilityEngine->checkDataAvailability(
    $tenantId,
    $branchId,
    $month,
    $year
);

// Returns:
[
    'all_data_exists' => true/false,
    'missing_data' => ['employees', 'attendance'],
    'data_summary' => [
        'employees' => 50,
        'attendance_records' => 1200,
        // ... etc
    ]
]
```

---

## Integration Points

### 1. Dashboard View
The dashboard should have a "Create Batch" button that:
- Allows user to select Month and Year
- Submits to `POST /compliance/batch/create`
- Gets redirected to review page

### 2. Review Page
The review page should:
- Display all detected forms
- Show data availability status
- Allow user to proceed or cancel
- Provide links to fill missing data (if needed)

### 3. Processing
The processing stage remains unchanged:
- Generates all forms
- Updates database
- Runs audit and certification

---

## Testing Checklist

### Stage 1: Create Batch
- [ ] User can select Month and Year
- [ ] Batch is created with status = 'pending'
- [ ] Forms are detected based on frequency
- [ ] Forms are attached to batch with status = 'pending'
- [ ] User is redirected to review page

### Stage 2: Review Batch
- [ ] Review page displays batch information
- [ ] Review page displays all detected forms
- [ ] Data availability is checked correctly
- [ ] Data summary shows correct counts
- [ ] "Proceed" button is enabled when all data exists
- [ ] "Proceed" button is disabled when data is missing
- [ ] User can cancel and return to dashboard

### Stage 3: Process Batch
- [ ] User can click "Proceed to Processing"
- [ ] Forms are generated successfully
- [ ] File paths are updated in database
- [ ] Status is updated to 'generated'
- [ ] Audit runs automatically
- [ ] Certification runs automatically
- [ ] User is redirected to dashboard with success message

---

## Frequency Rules

### Monthly
- Applicable every month

### Quarterly
- Applicable in months: 3, 6, 9, 12

### Half-Yearly
- Applicable in months: 6, 12

### Yearly
- Applicable in month: 12

### Example
- User selects March (month 3)
- System detects:
  - All monthly forms
  - All quarterly forms (March is Q1)

---

## Error Handling

### Batch Creation Errors
- No branch configured → Error message
- No forms applicable → Error message
- Database error → Error message

### Review Page Errors
- Batch not found → 404
- Unauthorized access → 403
- Database error → Error message

### Processing Errors
- Form generation fails → Log error, continue with next form
- Audit fails → Log error, continue
- Certification fails → Log error, continue

---

## Database Queries

### Get Applicable Forms
```php
$forms = ComplianceFormsMaster::where('is_active', true)
    ->get()
    ->filter(fn($form) => $this->isApplicable($form->frequency, $month));
```

### Get Batch Forms
```php
$forms = ComplianceBatchForm::where('batch_id', $batchId)
    ->where('status', 'pending')
    ->get();
```

### Check Employee Data
```php
$exists = WorkforceEmployee::where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->exists();
```

---

## Performance Considerations

### Batch Creation
- Frequency check: O(n) where n = number of forms
- Form attachment: Bulk insert for performance

### Review Page
- Data availability check: Multiple queries (can be optimized with caching)
- Data summary: Multiple count queries (can be optimized with aggregation)

### Optimization Tips
1. Cache frequency rules
2. Use database aggregation for counts
3. Batch database queries
4. Use eager loading for relationships

---

## Security Considerations

### Multi-Tenant Safety
- All queries filter by `tenant_id`
- All queries filter by `branch_id`
- User can only access their own batches

### Authorization
- User must be authenticated
- User must own the batch (tenant_id check)
- User must have permission to create batches

---

## Deployment Steps

1. **Create new files:**
   - `DataAvailabilityEngine.php`
   - `BatchReviewService.php`
   - `batch-review.blade.php`

2. **Update existing files:**
   - `ComplianceExecutionController.php`
   - `routes/compliance.php`

3. **Test:**
   - Create batch
   - Review batch
   - Process batch

4. **Deploy:**
   - Push to production
   - Run tests
   - Monitor logs

---

## Rollback Plan

If issues occur:

1. **Revert routes:**
   - Change redirect in `createBatch()` back to dashboard

2. **Revert controller:**
   - Remove `reviewBatch()` method

3. **Disable review page:**
   - Comment out route

4. **Restore old workflow:**
   - Users go directly from create to process

---

## Future Enhancements

1. **Manual Data Entry**
   - Add form to fill missing data on review page
   - Save to `compliance_manual_data` table

2. **CSV Upload**
   - Add CSV upload on review page
   - Parse and validate CSV
   - Save to database

3. **PDF Upload**
   - Add PDF upload on review page
   - Extract data from PDF
   - Save to database

4. **Data Caching**
   - Cache frequency rules
   - Cache data availability checks
   - Improve performance

5. **Batch Scheduling**
   - Schedule batch creation for specific dates
   - Auto-create batches on schedule
   - Send notifications

---

## Support

For questions or issues:
1. Check the testing checklist
2. Review the error handling section
3. Check the logs for errors
4. Contact the development team

---

## Summary

The refactored batch workflow provides:
- ✅ Automatic form detection based on frequency
- ✅ Data availability checking before processing
- ✅ Clear review stage for user confirmation
- ✅ Seamless integration with existing systems
- ✅ Multi-tenant safety
- ✅ Error handling and logging

The system is ready for deployment and testing.

