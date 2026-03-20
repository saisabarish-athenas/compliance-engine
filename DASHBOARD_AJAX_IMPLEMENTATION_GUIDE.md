# Dashboard AJAX Refactoring - Implementation Guide

## Overview

This guide explains how to implement the new AJAX-based batch workflow that keeps everything on the dashboard without page redirects.

## What Changed

### Before (Old Workflow)
```
Dashboard → Create Batch → Redirect to /batch/review page
```

### After (New Workflow)
```
Dashboard → Create Batch (AJAX) → Show Review Inline → Proceed (AJAX) → Generate Forms
```

## Files Modified

### 1. Controller: `app/Http/Controllers/ComplianceExecutionController.php`

**Changes:**
- Modified `createBatch()` to return JSON for AJAX requests
- Modified `processBatch()` to return JSON for AJAX requests
- Maintains backward compatibility with form submissions

**Key Code:**
```php
public function createBatch(Request $request)
{
    // ... validation and batch creation ...
    
    if ($request->wantsJson()) {
        $reviewService = app(\App\Services\Compliance\BatchReviewService::class);
        $reviewData = $reviewService->prepareReviewData($batch->id);
        
        return response()->json([
            'status' => 'success',
            'batch_id' => $batch->id,
            'review_html' => view('compliance.partials.batch-review', $reviewData)->render(),
        ]);
    }
    
    // Fallback for form submissions
    return redirect()->route('compliance.batch.review', ['batch' => $batch->id]);
}
```

### 2. Blade Partial: `resources/views/compliance/partials/batch-review.blade.php`

**Purpose:** Renders the batch review section inline

**Contains:**
- Batch info card
- Forms to generate list
- Data availability check
- Data input options (if data missing)
- Action buttons (Cancel, Proceed)

### 3. Dashboard: `resources/views/compliance/dashboard.blade.php`

**Changes:**
- Form changed from POST to AJAX
- Added hidden container for batch review
- Added JavaScript event listeners
- Removed redirect logic

**Key HTML:**
```html
<form id="batchForm">
    <!-- Month and Year selects -->
    <button type="submit" id="createBatchBtn">Create Batch</button>
</form>

<!-- Hidden container for batch review -->
<div id="batch-review-container" style="display: none;"></div>
```

## Implementation Steps

### Step 1: Update Controller

Replace the `createBatch()` and `processBatch()` methods in `ComplianceExecutionController.php` with the new versions that support JSON responses.

### Step 2: Create Batch Review Partial

Create the file `resources/views/compliance/partials/batch-review.blade.php` with the batch review UI.

### Step 3: Update Dashboard

Replace the dashboard form with AJAX-based implementation.

### Step 4: Test the Workflow

1. Open dashboard
2. Select month and year
3. Click "Create Batch"
4. Verify batch review appears inline
5. Click "Proceed to Generate"
6. Verify forms are generated

## JavaScript Flow

### 1. Form Submission
```javascript
document.getElementById('batchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get month and year
    const month = document.getElementById('period_month').value;
    const year = document.getElementById('period_year').value;
    
    // Send AJAX request
    const response = await fetch('/compliance/batch/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            period_month: parseInt(month),
            period_year: parseInt(year)
        })
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
        // Insert HTML into container
        document.getElementById('batch-review-container').innerHTML = data.review_html;
        document.getElementById('batch-review-container').style.display = 'block';
        
        // Attach event listeners
        attachBatchReviewListeners(data.batch_id);
    }
});
```

### 2. Batch Review Listeners
```javascript
function attachBatchReviewListeners(batchId) {
    // Cancel button
    document.querySelector('.cancel-batch-btn').addEventListener('click', function() {
        document.getElementById('batch-review-container').style.display = 'none';
    });
    
    // Data input buttons
    document.querySelectorAll('.data-input-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            handleDataInput(this.dataset.action, batchId);
        });
    });
    
    // Proceed button
    document.querySelector('.proceed-batch-btn').addEventListener('click', async function() {
        const response = await fetch(`/compliance/batch/${batchId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            alert('Batch processed successfully!');
            window.location.reload();
        }
    });
}
```

### 3. Data Input Handlers
```javascript
function handleDataInput(action, batchId) {
    const container = document.getElementById('dataInputContainer');
    
    switch (action) {
        case 'manual':
            showManualDataEntry(container, batchId);
            break;
        case 'csv':
            showCSVUpload(container, batchId);
            break;
        case 'pdf':
            showPDFUpload(container, batchId);
            break;
        case 'template':
            downloadTemplate();
            break;
    }
}
```

## API Endpoints

### Create Batch (AJAX)
```
POST /compliance/batch/create
Content-Type: application/json
Accept: application/json

{
    "period_month": 3,
    "period_year": 2025
}

Response:
{
    "status": "success",
    "batch_id": 20,
    "period": "March 2025",
    "forms": [...],
    "data_availability": {...},
    "review_html": "<div>...</div>"
}
```

### Process Batch (AJAX)
```
POST /compliance/batch/{id}/process
Content-Type: application/json
Accept: application/json

Response:
{
    "status": "success",
    "message": "Batch processed successfully!",
    "batch_id": 20,
    "results": {...}
}
```

### Upload Data (AJAX)
```
POST /compliance/batch/{id}/upload-data
Content-Type: multipart/form-data

Form Data:
- file: <CSV file>
- dataset_type: employees|payroll|attendance

Response:
{
    "status": "success",
    "message": "Successfully parsed and stored 50 records for employees.",
    "records_inserted": 50
}
```

## Data Availability Check

The system checks for data in these tables:
- `workforce_employee` - Employee records
- `workforce_attendance` - Attendance records
- `payroll_entries` - Payroll data
- `contract_labour` - Contract labour data
- `bonus_records` - Bonus records
- `incident_documents` - Incident records
- `hazard_register` - Hazard register

If all data exists → Proceed button is enabled
If data is missing → Show data input options

## Backward Compatibility

The system maintains backward compatibility:
- Form submissions still work (redirect to review page)
- AJAX requests return JSON
- Both workflows are supported

## Testing Checklist

- [ ] Create batch with AJAX
- [ ] Verify review appears inline
- [ ] Check data availability detection
- [ ] Test manual data entry
- [ ] Test CSV upload
- [ ] Test PDF upload
- [ ] Test template download
- [ ] Click proceed and verify forms generate
- [ ] Verify page doesn't reload during workflow
- [ ] Test cancel button
- [ ] Verify batch appears in recent batches table

## Troubleshooting

### Batch review doesn't appear
- Check browser console for JavaScript errors
- Verify CSRF token is present
- Check network tab for failed requests

### Data input buttons don't work
- Verify event listeners are attached
- Check that container exists in DOM
- Verify data-action attributes are correct

### Proceed button is disabled
- Check data availability check results
- Verify all required data exists
- Try uploading missing data

## Performance Considerations

- AJAX requests are faster than page reloads
- No full page refresh needed
- Reduced server load
- Better user experience

## Security

- CSRF token validation on all POST requests
- Tenant ID validation in controller
- Branch ID validation
- No sensitive data in JSON responses

## Future Enhancements

- Add progress bar for batch processing
- Real-time data availability updates
- Batch preview before processing
- Bulk batch creation
- Scheduled batch processing
