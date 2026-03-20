# Compliance Dashboard - Quick Reference

## 🚀 Quick Start

### 1. Access Dashboard
```
http://your-app.com/compliance/manual-dashboard
```

### 2. View Batch Summary
```bash
GET /compliance/manual-batch/{batch_id}/summary
```

### 3. Upload Document
```bash
POST /compliance/manual-item/upload
Content-Type: multipart/form-data
item_id: 1
file: <binary>
```

### 4. Skip Compliance
```bash
POST /compliance/manual-item/skip
Content-Type: application/json
{"item_id": 1}
```

## 📊 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/compliance/manual-dashboard` | Display dashboard |
| GET | `/compliance/manual-batches` | Get all batches |
| GET | `/compliance/manual-batch/{id}/summary` | Get batch stats |
| GET | `/compliance/manual-batch/{id}` | Get batch items |
| POST | `/compliance/manual-item/upload` | Upload document |
| POST | `/compliance/manual-item/skip` | Skip compliance |

## 🔒 Multi-Tenant Safety

All endpoints enforce:
```php
->where('tenant_id', $tenantId)
->where('branch_id', $branchId)
```

## 📁 Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/ComplianceDashboardController.php` | Controller |
| `resources/views/compliance/manual_dashboard.blade.php` | View |
| `routes/compliance.php` | Routes |

## 🧪 Testing

### Test Dashboard
```bash
php artisan tinker
>>> auth()->loginUsingId(1)
>>> redirect('/compliance/manual-dashboard')
```

### Test API
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://your-app.com/compliance/manual-batch/1/summary
```

## 🎨 UI Features

- **Statistics Cards**: Total, Completed, Pending, Skipped
- **Progress Bar**: Visual completion percentage
- **Compliance Table**: Name, Act, Status, Document, Actions
- **Upload Modal**: File upload with validation
- **Batch Selector**: Dropdown to switch batches

## 🔧 Configuration

### File Upload
- Storage: `public` disk
- Path: `storage/app/public/compliance_documents`
- Formats: PDF, JPG, PNG
- Max Size: 5MB

### Customize
```php
// Change storage path
$path = $request->file('file')->store('custom-path', 'public');

// Add file types
'file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:5120'

// Change max size
'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240' // 10MB
```

## 🐛 Troubleshooting

### Dashboard Not Loading
1. Check authentication: `auth()->check()`
2. Verify routes: `php artisan route:list | grep manual-dashboard`
3. Check logs: `tail -f storage/logs/laravel.log`

### Batch Not Showing
1. Verify batch exists: `ComplianceExecutionBatch::find(1)`
2. Check tenant_id: `auth()->user()->tenant_id`
3. Check branch_id: `auth()->user()->branch_id`

### Upload Failing
1. Check file size: `< 5MB`
2. Check file type: `PDF, JPG, PNG`
3. Check storage permissions: `chmod 755 storage/app/public`
4. Check CSRF token: `<meta name="csrf-token">`

### AJAX Errors
1. Check browser console: `F12 → Console`
2. Check network tab: `F12 → Network`
3. Verify CSRF token in headers
4. Check API response status

## 📈 Performance Tips

### Enable Caching
```php
$summary = Cache::remember(
    "batch_summary_{$batchId}",
    3600,
    fn() => ManualComplianceBatchItem::where(...)->first()
);
```

### Add Pagination
```php
$items = ManualComplianceBatchItem::where(...)
    ->paginate(50);
```

### Optimize Queries
```php
// Use select to limit columns
->select(['id', 'status', 'document_path'])

// Use eager loading
->with('compliance')
```

## 🔐 Security Checklist

- [ ] CSRF token in forms
- [ ] Tenant validation on every request
- [ ] Branch validation on every request
- [ ] File type validation
- [ ] File size validation
- [ ] Authorization checks
- [ ] SQL injection prevention (use Eloquent)
- [ ] XSS prevention (use Blade escaping)

## 📱 Responsive Breakpoints

| Device | Breakpoint | Layout |
|--------|-----------|--------|
| Desktop | ≥992px | 4-column grid |
| Tablet | 768-991px | 2-column grid |
| Mobile | <768px | 1-column grid |

## 🎯 Common Tasks

### Get Batch Summary
```javascript
fetch('/compliance/manual-batch/1/summary')
  .then(r => r.json())
  .then(data => console.log(data))
```

### Upload Document
```javascript
const formData = new FormData();
formData.append('item_id', 1);
formData.append('file', fileInput.files[0]);

fetch('/compliance/manual-item/upload', {
  method: 'POST',
  body: formData,
  headers: {
    'X-CSRF-TOKEN': token
  }
})
```

### Skip Compliance
```javascript
fetch('/compliance/manual-item/skip', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': token
  },
  body: JSON.stringify({ item_id: 1 })
})
```

## 📊 Response Examples

### Batch Summary
```json
{
  "batch_id": 1,
  "month": 3,
  "year": 2026,
  "total": 25,
  "completed": 12,
  "pending": 10,
  "skipped": 3,
  "percentage": 48
}
```

### Batch Items
```json
{
  "batch_id": 1,
  "items": [
    {
      "item_id": 1,
      "compliance_name": "Submission of Annual Return",
      "act_name": "Factories Act",
      "status": "pending",
      "document_path": null
    }
  ]
}
```

### All Batches
```json
[
  {
    "batch_id": 1,
    "month": 3,
    "year": 2026,
    "total_tasks": 25,
    "completed": 12,
    "pending": 10
  }
]
```

## 🚀 Deployment

### 1. Copy Files
```bash
cp app/Http/Controllers/ComplianceDashboardController.php /production/
cp resources/views/compliance/manual_dashboard.blade.php /production/
```

### 2. Update Routes
```bash
# Edit routes/compliance.php
# Add dashboard routes
```

### 3. Test
```bash
php artisan route:list | grep manual-dashboard
curl http://your-app.com/compliance/manual-dashboard
```

### 4. Monitor
```bash
tail -f storage/logs/laravel.log
```

## 📞 Support

- Check logs: `storage/logs/laravel.log`
- Test API: `curl -v http://your-app.com/compliance/manual-batch/1/summary`
- Debug JS: `F12 → Console`
- Verify DB: `php artisan tinker`

---

**Last Updated**: 2026-03-25
**Status**: ✅ PRODUCTION READY
