# QUICK TEST SCRIPT

## Test After Processing Batch 61

### 1. Check Logs
```bash
type storage\logs\laravel.log | findstr "Persisted count"
```

**Expected:**
```
Persisted count: 2
```

If shows 0 → Loop never executed, check subscription type

---

### 2. Check Database (Tinker)
```php
php artisan tinker

>>> DB::table('compliance_batch_forms')->where('batch_id', 61)->count();
```

**Expected:** > 0

```php
>>> DB::table('compliance_batch_forms')->where('batch_id', 61)->get();
```

**Expected:** Collection with records showing:
- tenant_id
- batch_id = 61
- form_code
- file_path
- status = 'success'

---

### 3. Check Files
```bash
# Replace [TENANT_ID] with actual tenant ID
dir storage\app\generated_forms\[TENANT_ID]\61
```

**Expected:**
```
FORM_B.pdf
FORM_XIII.pdf
...
```

---

### 4. Test Inspection Pack
```bash
# In browser or curl
curl -I http://localhost/compliance/batch/61/inspection-pack
```

**Expected:**
```
HTTP/1.1 200 OK
Content-Type: application/zip
Content-Disposition: attachment; filename="Inspection_Pack_Batch_61.zip"
```

---

## Quick SQL Queries

### Count forms per batch
```sql
SELECT batch_id, COUNT(*) as form_count
FROM compliance_batch_forms
GROUP BY batch_id;
```

### Check specific batch
```sql
SELECT * FROM compliance_batch_forms WHERE batch_id = 61;
```

### Verify file paths
```sql
SELECT form_code, file_path, status
FROM compliance_batch_forms
WHERE batch_id = 61;
```

### Check tenant isolation
```sql
SELECT batch_id, tenant_id, COUNT(*) as forms
FROM compliance_batch_forms
GROUP BY batch_id, tenant_id;
```

---

## If Test Fails

### Persisted count is 0:
1. Check subscription: `SELECT subscription_type FROM tenants WHERE id = X;`
2. Must be exactly 'FULL' (case-insensitive)
3. Check if processBatch was called
4. Check for errors in logs

### No database records:
1. Verify loop executed: `SELECT * FROM compliance_generation_logs WHERE batch_id = 61;`
2. If generation_logs has records but batch_forms doesn't → persistence block didn't run
3. Check subscription type again

### Files not found:
1. Check directory: `dir storage\app\generated_forms`
2. Check permissions
3. Verify file_path in database matches actual location

### Inspection Pack 422:
1. Run: `SELECT COUNT(*) FROM compliance_batch_forms WHERE batch_id = 61;`
2. If 0 → batch didn't persist
3. If > 0 → check file_path values and file existence

---

## One-Line Health Check

```sql
SELECT 
    (SELECT COUNT(*) FROM compliance_batch_forms WHERE batch_id = 61) as persisted_forms,
    (SELECT COUNT(*) FROM compliance_generation_logs WHERE batch_id = 61) as generation_logs;
```

**Expected:**
- persisted_forms: > 0
- generation_logs: > 0
- Both should match (same count)
