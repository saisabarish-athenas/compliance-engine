# QUICK TEST VERIFICATION

## Manual Testing Steps

### 1. Check Database After Batch Processing

```sql
-- Check if records exist
SELECT * FROM compliance_batch_forms 
WHERE batch_id = [YOUR_BATCH_ID];

-- Count records per batch
SELECT batch_id, COUNT(*) as form_count 
FROM compliance_batch_forms 
GROUP BY batch_id;

-- Verify tenant isolation
SELECT batch_id, tenant_id, form_code, status 
FROM compliance_batch_forms 
WHERE tenant_id = [YOUR_TENANT_ID];
```

### 2. Check File Storage

```bash
# Windows
dir storage\app\generated_forms\[TENANT_ID]\[BATCH_ID]

# Check if files exist
dir storage\app\generated_forms\1\* /s
```

### 3. Check Logs

```bash
# View Laravel logs
type storage\logs\laravel.log | findstr "batch_id"
type storage\logs\laravel.log | findstr "post_insert_count"
type storage\logs\laravel.log | findstr "Inspection Pack Error"
```

### 4. Test Inspection Pack Download

```bash
# Using curl (if available)
curl -I http://localhost/compliance/batch/[BATCH_ID]/inspection-pack

# Expected responses:
# 200 - Success (file download)
# 403 - MINIMAL subscription
# 404 - Batch not found
# 422 - No forms stored
# 500 - ZIP creation failed
```

---

## Artisan Tinker Tests

```php
php artisan tinker

// Check batch records
>>> DB::table('compliance_batch_forms')->count();
>>> DB::table('compliance_batch_forms')->latest()->first();

// Check specific batch
>>> $batchId = 1;
>>> DB::table('compliance_batch_forms')->where('batch_id', $batchId)->get();

// Verify files exist
>>> $forms = DB::table('compliance_batch_forms')->where('batch_id', 1)->get();
>>> foreach ($forms as $form) {
...     $path = storage_path('app/' . $form->file_path);
...     echo $form->form_code . ': ' . (file_exists($path) ? 'EXISTS' : 'MISSING') . "\n";
... }

// Check subscription
>>> $user = User::find(1);
>>> $subscription = $user->tenant->subscription_type ?? '';
>>> $isFull = strtoupper(trim($subscription)) === 'FULL';
>>> echo "Subscription: {$subscription}, Is FULL: " . ($isFull ? 'YES' : 'NO');
```

---

## Expected Log Output

### After Batch Processing (FULL):

```
[2026-02-26 10:30:45] local.INFO: array (
  'batch_id' => 1,
  'tenant_id' => 1,
  'is_full' => true,
  'form_code' => 'FORM_B',
)

[2026-02-26 10:30:46] local.INFO: array (
  'post_insert_count' => 1,
)

[2026-02-26 10:30:47] local.INFO: array (
  'batch_id' => 1,
  'tenant_id' => 1,
  'is_full' => true,
  'form_code' => 'FORM_XIII',
)

[2026-02-26 10:30:48] local.INFO: array (
  'post_insert_count' => 2,
)
```

### After Inspection Pack Download:

```
# Success - No error log

# Failure:
[2026-02-26 10:35:00] local.ERROR: Inspection Pack Error {"batch_id":1,"error":"..."}
```

---

## Browser Testing

### FULL Subscription User:

1. Login as FULL user
2. Navigate to Compliance Dashboard
3. Create new batch
4. Process batch
5. Wait for completion
6. Click "Inspection Pack" button
7. **Expected:** ZIP file downloads automatically
8. Extract ZIP and verify PDFs inside

### MINIMAL Subscription User:

1. Login as MINIMAL user
2. Navigate to Compliance Dashboard
3. Create new batch
4. Process batch
5. Verify "Inspection Pack" button is hidden OR
6. If visible, clicking returns 403 error

---

## Verification Checklist

### Database:
- [ ] `compliance_batch_forms` table has records
- [ ] Records have correct `tenant_id`
- [ ] Records have correct `batch_id`
- [ ] Records have `status = 'success'`
- [ ] Records have valid `file_path`

### File Storage:
- [ ] Directory exists: `storage/app/generated_forms/`
- [ ] Subdirectories exist: `{tenant_id}/{batch_id}/`
- [ ] PDF files exist in subdirectories
- [ ] File names match pattern: `{form_code}_{batch_id}_{timestamp}.pdf`

### Logs:
- [ ] Batch consistency logged
- [ ] Post-insert count logged
- [ ] Count increases with each form
- [ ] No error logs for successful generation

### Inspection Pack:
- [ ] Returns HTTP 200 on success
- [ ] Returns HTTP 403 for MINIMAL
- [ ] Returns HTTP 404 for invalid batch
- [ ] Returns HTTP 422 for no forms
- [ ] ZIP contains all PDFs
- [ ] ZIP downloads automatically
- [ ] Temp file deleted after download

### System Stability:
- [ ] Preview still works
- [ ] MINIMAL subscription unchanged
- [ ] Form templates unchanged
- [ ] No structural changes
- [ ] Tenant isolation maintained

---

## Troubleshooting

### Issue: No records in compliance_batch_forms

**Check:**
```php
// Verify subscription
$user = auth()->user();
$subscription = $user->tenant->subscription_type ?? '';
echo "Subscription: {$subscription}";

// Should be exactly 'FULL' (case-insensitive after trim/uppercase)
```

### Issue: post_insert_count is 0

**Check:**
```sql
-- Verify table exists
SHOW TABLES LIKE 'compliance_batch_forms';

-- Check table structure
DESCRIBE compliance_batch_forms;

-- Check for errors
SELECT * FROM compliance_generation_logs WHERE status = 'failed';
```

### Issue: Inspection Pack returns 422

**Check:**
```sql
-- Verify forms exist
SELECT * FROM compliance_batch_forms WHERE batch_id = [BATCH_ID];

-- Check status
SELECT form_code, status FROM compliance_batch_forms WHERE batch_id = [BATCH_ID];
```

### Issue: Files not in ZIP

**Check:**
```php
// Verify file paths
$forms = DB::table('compliance_batch_forms')->where('batch_id', 1)->get();
foreach ($forms as $form) {
    $fullPath = storage_path('app/' . $form->file_path);
    echo "{$form->form_code}: {$fullPath} - " . (file_exists($fullPath) ? 'EXISTS' : 'MISSING') . "\n";
}
```

---

## Success Criteria

✅ All database records inserted  
✅ All files physically saved  
✅ All logs show consistency  
✅ Inspection Pack downloads successfully  
✅ ZIP contains all PDFs  
✅ No 302 redirects  
✅ Proper HTTP status codes  
✅ MINIMAL subscription blocked  
✅ System remains stable  
✅ No breaking changes
