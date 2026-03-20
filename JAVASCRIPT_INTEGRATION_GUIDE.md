## JavaScript Integration Guide

### 📍 File Location
`resources/js/compliance-dashboard.js`

### 🔗 Integration Methods

#### Method 1: Include in Blade Template (Recommended)
```blade
@push('scripts')
    <script src="{{ asset('js/compliance-dashboard.js') }}"></script>
@endpush
```

#### Method 2: Inline in Dashboard Blade
```blade
@push('scripts')
    <script>
        // Copy entire content of compliance-dashboard.js here
    </script>
@endpush
```

#### Method 3: Vite/Laravel Mix
```javascript
// webpack.mix.js or vite.config.js
mix.js('resources/js/compliance-dashboard.js', 'public/js');
```

### ✅ Prerequisites

1. **Bootstrap Modal** - For preview modal
   ```html
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.x/dist/css/bootstrap.min.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.x/dist/js/bootstrap.bundle.min.js"></script>
   ```

2. **Ant Design Classes** - For styling
   ```html
   <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.x/antd.min.css" rel="stylesheet">
   ```

3. **CSRF Token Meta Tag**
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

4. **Font Awesome** (Optional, for icons)
   ```html
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.x/css/all.min.css" rel="stylesheet">
   ```

### 🎯 Required HTML Elements

```html
<!-- Batch Form -->
<form id="batchForm">
    <select id="period_month" name="period_month"></select>
    <select id="period_year" name="period_year"></select>
    <button id="createBatchBtn" type="submit">Create Batch</button>
    <span id="submitSpinner" class="spinner d-none"></span>
</form>

<!-- Batch Review Container -->
<div id="batch-review-container"></div>

<!-- Processing Container -->
<div id="batch-processing-container" style="display:none;">
    <!-- Processing UI will be rendered here -->
</div>

<!-- Preview Modal -->
<div class="modal fade" id="preview-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preview-title">Form Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="preview-content" style="max-height: 70vh; overflow-y: auto;">
                <!-- Preview content loaded here -->
            </div>
        </div>
    </div>
</div>
```

### 🔌 API Endpoints Required

```
POST   /compliance/batch/create
POST   /compliance/batch/{id}/process
GET    /compliance/batch/{id}/status
GET    /compliance/batch/{id}/preview/{formCode}
POST   /compliance/batch/{id}/fix-violations/{formCode}
POST   /compliance/batch/{id}/submit-fix/{formCode}
```

### 🚀 Usage Flow

```
1. User fills month/year in form
2. Clicks "Create Batch"
3. Batch review renders
4. User clicks "Proceed to Generate"
5. Processing UI replaces data availability section
6. Polling starts (every 3 seconds)
7. Progress bar updates
8. Forms table updates with status
9. Preview buttons appear for generated forms
10. User can click preview to see form
11. When complete, completion message shows
```

### 🧪 Testing

#### Test Batch Creation
```javascript
// In browser console
document.getElementById('period_month').value = '1';
document.getElementById('period_year').value = '2024';
document.getElementById('batchForm').dispatchEvent(new Event('submit'));
```

#### Test Proceed Button
```javascript
// After batch review renders
document.querySelector('.proceed-batch-btn').click();
```

#### Test Preview Button
```javascript
// After forms are generated
document.querySelector('.preview-btn').click();
```

#### Check Polling State
```javascript
console.log(DashboardState);
// Should show: { pollingIntervals: {...}, currentBatchId: 123 }
```

### 🐛 Debugging

#### Enable Console Logging
```javascript
// Add to compliance-dashboard.js
const DEBUG = true;

function log(msg, data) {
    if (DEBUG) {
        console.log(`[Dashboard] ${msg}`, data || '');
    }
}
```

#### Check Polling
```javascript
// In console
Object.keys(DashboardState.pollingIntervals)
// Should show batch IDs with active polling
```

#### Check Network Requests
```
Open DevTools → Network tab
Filter by XHR
Watch for:
- POST /compliance/batch/create
- POST /compliance/batch/{id}/process
- GET /compliance/batch/{id}/status (repeating)
- GET /compliance/batch/{id}/preview/{formCode}
```

### ⚙️ Configuration

#### Change Polling Interval
```javascript
// In pollBatchStatus function
// Change from 3000 to desired milliseconds
DashboardState.pollingIntervals[batchId] = setInterval(poll, 5000); // 5 seconds
```

#### Change Progress Update Frequency
```javascript
// Polling already runs every 3 seconds
// To update more frequently, reduce interval above
```

#### Customize Status Badges
```javascript
// In pollBatchStatus updateUI function
if (form.status === 'generated') {
    statusBadge = '<span class="ant-tag ant-tag-success">✅ Generated</span>';
    // Customize here
}
```

### 🔒 Security

1. **CSRF Protection** - Automatically included
   ```javascript
   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
   ```

2. **Input Validation** - Server-side validation required
   ```javascript
   // Client-side check
   if (!month || !year) {
       alert('Please select both month and year');
       return;
   }
   ```

3. **Error Handling** - All fetch calls have error handlers
   ```javascript
   .catch(err => {
       console.error('Error:', err);
       alert('Error: ' + err.message);
   });
   ```

### 📱 Responsive Design

The JavaScript works with responsive layouts:
- Mobile: Single column
- Tablet: Two columns
- Desktop: Full layout

No JavaScript changes needed for responsiveness.

### 🎨 Styling

Uses Ant Design classes:
- `.ant-card` - Card container
- `.ant-tag` - Status badges
- `.ant-btn` - Buttons
- `.ant-table` - Tables
- `.ant-alert` - Alerts

Bootstrap classes:
- `.modal` - Modal dialogs
- `.progress` - Progress bars
- `.alert` - Alert messages

### 📊 State Management

```javascript
DashboardState = {
    pollingIntervals: {
        123: intervalId,  // Batch ID: interval ID
        124: intervalId
    },
    currentBatchId: 123
}
```

### 🔄 Event Flow

```
User Action
    ↓
Delegated Listener
    ↓
Handler Function
    ↓
API Call (fetch)
    ↓
Response Handler
    ↓
DOM Update
    ↓
UI Reflects Change
```

### 💾 Data Flow

```
Form Data
    ↓
POST /compliance/batch/create
    ↓
Response: { batch_id, forms, data_availability, ... }
    ↓
renderBatchReview(data)
    ↓
User clicks Proceed
    ↓
POST /compliance/batch/{id}/process
    ↓
pollBatchStatus(batchId)
    ↓
GET /compliance/batch/{id}/status (every 3s)
    ↓
Update Progress & Table
    ↓
User clicks Preview
    ↓
GET /compliance/batch/{id}/preview/{formCode}
    ↓
Display in Modal
```

### ✨ Features

✅ Batch creation with month/year selection
✅ Batch review with forms list
✅ Data availability check
✅ Processing UI with progress bar
✅ Real-time polling (3-second intervals)
✅ Dynamic preview buttons
✅ Form preview in modal
✅ Re-audit functionality
✅ Fix violations workflow
✅ Proper error handling
✅ Memory management
✅ Event delegation

### 📝 Notes

- All routes use `/compliance/batch/` prefix
- CSRF token required for all POST requests
- Polling automatically stops when complete
- Preview modal uses Bootstrap
- Status badges use Ant Design
- Progress calculation: (generated / total) * 100
- Polling interval: 3000ms (3 seconds)

### 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| Buttons not working | Check event delegation listener |
| Polling not updating | Check network tab for status endpoint |
| Preview not loading | Check preview endpoint returns HTML |
| Progress bar stuck | Check polling interval is running |
| Memory leak | Check intervals are cleared on completion |

### 📞 Support

For issues:
1. Check browser console for errors
2. Check network tab for failed requests
3. Verify all API endpoints exist
4. Verify CSRF token is present
5. Check Bootstrap and Ant Design are loaded

---

**Status:** ✅ Ready for Integration
**Last Updated:** 2024
**Version:** 1.0
