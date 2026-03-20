# Live Preview System for Batch Processing

## Overview

A real-time form generation progress display system that shows live updates as forms are being generated during batch processing.

## What Was Added

### 1. Processing Screen View
**File:** `resources/views/compliance/batch-processing.blade.php`

Displays:
- Real-time form generation status
- Progress summary (Total, Generated, Processing, Pending)
- Form list with status indicators
- Preview buttons (appear when form is generated)
- Completion message
- Preview modal for viewing generated forms

### 2. Status API Route
**Route:** `GET /compliance/batch/{batch}/status`

Returns JSON array of all forms in batch with:
- `form_code` - Form identifier
- `status` - Current status (pending, processing, generated)
- `file_path` - Path to generated file (if available)

### 3. Processing Screen Route
**Route:** `GET /compliance/batch/{batch}/processing`

Displays the live processing screen with initial form list.

### 4. Controller Methods

#### `processingScreen(int $batch)`
- Loads batch and associated forms
- Renders the processing screen view
- Enforces tenant isolation

#### `getBatchStatus(int $batch)`
- Returns current status of all forms in batch
- Used by JavaScript polling
- Returns JSON response

#### `reviewBatch(int $batch)`
- Displays batch review page before processing
- Shows data availability check
- Allows user to proceed or cancel

#### Updated `processBatch(int $batch)`
- Now redirects to processing screen instead of returning JSON
- Dispatches background job
- Marks batch as processing

### 5. JavaScript Polling System

**Polling Interval:** 3 seconds

**Features:**
- Automatic status updates
- Real-time UI refresh
- Form status transitions (pending → processing → generated)
- Preview button visibility toggle
- Completion detection
- Auto-stop when all forms generated

**Status Indicators:**
- ⏳ Pending - Gray, waiting to start
- ⏳ Processing - Blue, currently generating
- ✔ Generated - Green, ready for preview

### 6. Preview Modal

**Features:**
- Click "Preview" button to view generated form
- Modal displays form HTML
- Close button to dismiss
- Click outside to close

## Workflow

```
Dashboard
  ↓
Create Batch
  ↓
Batch Review Screen
  ↓
User clicks "Proceed to Generate"
  ↓
Redirect to Processing Screen
  ↓
Background job starts generating forms
  ↓
JavaScript polls /compliance/batch/{batch}/status every 3 seconds
  ↓
UI updates as forms complete
  ↓
Preview buttons appear for generated forms
  ↓
All forms generated → Completion message shown
```

## Database Source

Uses `compliance_batch_forms` table:
- `batch_id` - Batch identifier
- `form_code` - Form code
- `status` - Current status (pending, processing, generated)
- `file_path` - Path to generated PDF file

## Status Values

- `pending` - Form not yet started
- `processing` - Form generation in progress
- `generated` - Form successfully generated

## UI Components

### Progress Summary
```
Total Forms: 34
Generated: 12
Processing: 3
Pending: 19
```

### Form Row
```
[Icon] FORM_10          ✔ Generated    [Preview]
[Icon] FORM_11          ✔ Generated    [Preview]
[Icon] FORM_12          ⏳ Processing...
[Icon] FORM_17          ⏳ Processing...
[Icon] FORM_26          Pending
```

### Completion Message
```
✓ All forms have been generated successfully!
You can now preview, download, or audit the generated forms.

[Back to Batch] [Dashboard]
```

## API Response Format

```json
[
  {
    "form_code": "FORM_10",
    "status": "generated",
    "file_path": "storage/app/compliance_pdfs/batch_1_form_10.pdf"
  },
  {
    "form_code": "FORM_11",
    "status": "processing",
    "file_path": null
  },
  {
    "form_code": "FORM_12",
    "status": "pending",
    "file_path": null
  }
]
```

## Security

- Tenant isolation enforced at controller level
- User must own the batch to view status
- Preview only available for generated forms
- All routes protected by auth middleware

## Performance

- Polling every 3 seconds (configurable)
- Minimal database queries
- No unnecessary data transfer
- Stops polling when complete

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Uses Fetch API
- CSS animations for loading indicators

## Customization

### Change Polling Interval
In `batch-processing.blade.php`, line with `setInterval(pollStatus, 3000)`:
```javascript
setInterval(pollStatus, 3000); // Change 3000 to desired milliseconds
```

### Customize Status Colors
Edit the status badge classes in `updateUI()` function:
```javascript
statusBadge.className = 'status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800';
```

### Add Sound Notification
Add to `showCompletionMessage()`:
```javascript
new Audio('/path/to/notification.mp3').play();
```

## Testing

### Manual Test
1. Create a batch from dashboard
2. Click "Proceed to Generate"
3. Should redirect to processing screen
4. Forms should update as they're generated
5. Preview buttons should appear
6. Completion message should show when done

### API Test
```bash
curl http://localhost/compliance/batch/1/status
```

Should return JSON array of form statuses.

## Troubleshooting

### Polling Not Working
- Check browser console for errors
- Verify JavaScript is enabled
- Check network tab for API calls
- Verify batch ID is correct

### Preview Not Loading
- Check file_path in database
- Verify file exists in storage
- Check file permissions
- Check browser console for errors

### Status Not Updating
- Check background job is running
- Verify database is being updated
- Check polling interval
- Verify tenant isolation

## Files Modified

1. `routes/compliance.php` - Added routes
2. `app/Http/Controllers/ComplianceExecutionController.php` - Added methods
3. `resources/views/compliance/dashboard.blade.php` - Updated proceed button
4. `resources/views/compliance/batch-processing.blade.php` - New view (created)

## No Breaking Changes

- Existing batch generation engine unchanged
- Database schema unchanged
- All existing functionality preserved
- Backward compatible with existing code

## Future Enhancements

- WebSocket for real-time updates (instead of polling)
- Sound notifications on completion
- Email notifications
- Batch processing history
- Performance metrics dashboard
- Retry failed forms
- Pause/resume processing
