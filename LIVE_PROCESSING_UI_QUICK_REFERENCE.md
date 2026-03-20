# Live Processing UI - Quick Reference

## 📁 File Structure

```
resources/views/compliance/
├── dashboard.blade.php                          (MODIFIED)
│   ├── Batch processing container
│   ├── showLiveProcessing(batchId)
│   ├── updateProcessingUI(forms)
│   └── openPreview(batchId, formCode)
│
└── partials/
    └── batch-processing.blade.php               (NEW)
        ├── Progress bar
        ├── Forms table
        ├── Preview modal
        └── Completion message
```

## 🎯 Key Functions

### `showLiveProcessing(batchId)`
**Purpose:** Initialize live processing UI
**Called by:** Proceed button click
**Does:**
- Shows processing container
- Hides batch review
- Sets batch ID
- Starts polling

### `updateProcessingUI(forms)`
**Purpose:** Update UI with current form statuses
**Called by:** Polling every 3 seconds
**Does:**
- Updates progress bar
- Updates forms table
- Shows/hides preview buttons
- Detects completion

### `openPreview(batchId, formCode)`
**Purpose:** Open preview modal
**Called by:** Preview button click
**Does:**
- Opens modal
- Fetches form HTML
- Displays in modal

## 🔄 Data Flow

```
User Action
    ↓
processBatch() [Backend]
    ↓
Returns JSON {status: 'success'}
    ↓
showLiveProcessing(batchId)
    ↓
pollStatus() every 3 seconds
    ↓
/compliance/batch/{batch}/status [API]
    ↓
Returns [{form_code, status, file_path}, ...]
    ↓
updateProcessingUI(forms)
    ↓
DOM Updated
```

## 🎨 UI Elements

### Progress Bar
```html
<div class="progress" style="height: 30px;">
    <div class="progress-bar progress-bar-striped progress-bar-animated" 
         id="progress-bar" style="width: 0%">0%</div>
</div>
```

### Status Badges
```
Pending    → <span class="badge bg-secondary">Pending</span>
Processing → <span class="badge bg-info">⏳ Processing</span>
Generated  → <span class="badge bg-success">✔ Generated</span>
```

### Preview Button
```html
<button class="btn btn-sm btn-primary" onclick="openPreview(batchId, formCode)">
    👁️ Preview
</button>
```

## 📊 Status Values

| Value | Display | Color |
|-------|---------|-------|
| pending | Pending | Gray |
| processing | ⏳ Processing | Blue |
| generated | ✔ Generated | Green |

## 🔌 API Endpoints

### Get Batch Status
```
GET /compliance/batch/{batch}/status
Response: [{form_code, status, file_path}, ...]
```

### Get Form Preview
```
GET /compliance/batch/{batch}/preview/{form}
Response: HTML content
```

### Process Batch
```
POST /compliance/batch/{batch}/process
Response: {status: 'success', message: '...', batch_id: ...}
```

## ⚙️ Configuration

### Polling Interval
```javascript
pollingInterval = setInterval(pollStatus, 3000); // 3 seconds
```

### Progress Bar Update
```javascript
progressBar.style.width = percent + '%';
progressBar.textContent = percent + '%';
```

### Table Row Creation
```javascript
const row = document.createElement('tr');
row.appendChild(codeCell);
row.appendChild(statusCell);
row.appendChild(actionCell);
tbody.appendChild(row);
```

## 🧪 Testing Checklist

- [ ] Create batch
- [ ] Click "Proceed to Generate"
- [ ] Processing UI appears
- [ ] Progress bar updates
- [ ] Forms table updates
- [ ] Status badges change
- [ ] Preview buttons appear
- [ ] Click preview button
- [ ] Modal opens
- [ ] Form displays
- [ ] Close modal
- [ ] Wait for completion
- [ ] Completion message appears
- [ ] No page reload

## 🐛 Troubleshooting

### Processing UI not showing
- Check if `batch-processing-container` is visible
- Check browser console for errors
- Verify batch ID is correct

### Progress bar not updating
- Check if polling is running
- Check network tab for API calls
- Verify `/compliance/batch/{batch}/status` returns data

### Preview not loading
- Check if form is generated
- Check file_path in database
- Verify file exists in storage

### Forms table empty
- Check if API returns forms
- Verify forms have correct status
- Check browser console for errors

## 📝 Code Examples

### Add new status badge
```javascript
if (form.status === 'custom') {
    statusBadge.className += ' bg-warning';
    statusBadge.textContent = '⚠️ Custom Status';
}
```

### Add new action button
```javascript
if (form.status === 'generated') {
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm btn-primary';
    btn.textContent = 'Action';
    btn.onclick = () => doAction(batchId, form.form_code);
    actionCell.appendChild(btn);
}
```

### Change polling interval
```javascript
pollingInterval = setInterval(pollStatus, 5000); // 5 seconds
```

## 🎯 Best Practices

✅ Always check form status before showing preview button
✅ Stop polling when all forms are generated
✅ Show completion message instead of reloading
✅ Use semantic HTML for accessibility
✅ Keep JavaScript functions focused and small
✅ Use proper error handling in fetch calls
✅ Test on multiple devices and browsers

## 📚 Related Files

- `LIVE_PROCESSING_UI_REFACTORED.md` - Full documentation
- `LIVE_PROCESSING_UI_REFACTORED_COMPLETE.md` - Complete guide
- `resources/views/compliance/dashboard.blade.php` - Main file
- `resources/views/compliance/partials/batch-processing.blade.php` - UI partial

---

**Version:** 2.0
**Status:** Production Ready
**Last Updated:** 2024
