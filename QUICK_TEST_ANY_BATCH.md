# QUICK TEST - ANY NEW BATCH

## Replace [BATCH_ID] with your actual batch ID (e.g., 62, 63, 64)

### 1. After Processing Batch

```php
php artisan tinker

>>> DB::table('compliance_batch_forms')->where('batch_id', [BATCH_ID])->count();
```

**Expected:** > 0

---

### 2. View Records

```php
>>> DB::table('compliance_batch_forms')->where('batch_id', [BATCH_ID])->get();
```

**Expected:** Collection showing:
- tenant_id
- batch_id = [BATCH_ID]
- form_code
- file_path
- status = 'success'

---

### 3. Check Files Exist

```bash
dir storage\app\generated_forms\[TENANT_ID]\[BATCH_ID]
```

**Expected:** PDF files listed

---

### 4. Check Logs

```bash
type storage\logs\laravel.log | findstr "Persisted count"
```

**Expected:** `Persisted count: N` (where N > 0)

---

### 5. Test Inspection Pack

**In Browser:**
```
http://localhost/compliance/batch/[BATCH_ID]/inspection-pack
```

**Expected:** ZIP file downloads

---

### 6. If Inspection Pack Fails (422)

**Check logs:**
```bash
type storage\logs\laravel.log | findstr "Inspection failed"
```

**Will show:**
```
Inspection failed. No records for batch: [BATCH_ID]
```

**Then verify:**
```sql
SELECT * FROM compliance_batch_forms WHERE batch_id = [BATCH_ID];
```

If empty → Persistence didn't happen, check subscription type

---

## One-Line Health Check

```sql
SELECT 
    batch_id,
    COUNT(*) as form_count,
    GROUP_CONCAT(form_code) as forms
FROM compliance_batch_forms
WHERE batch_id = [BATCH_ID]
GROUP BY batch_id;
```

**Expected:** 1 row with form_count > 0

---

## Verify Tenant Isolation

```sql
SELECT 
    cbf.batch_id,
    cbf.tenant_id as form_tenant,
    ceb.tenant_id as batch_tenant,
    CASE 
        WHEN cbf.tenant_id = ceb.tenant_id THEN 'MATCH'
        ELSE 'MISMATCH'
    END as check_result
FROM compliance_batch_forms cbf
INNER JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE cbf.batch_id = [BATCH_ID];
```

**Expected:** All rows show 'MATCH'

---

## Test Multiple Batches

```sql
-- Check last 5 batches
SELECT 
    batch_id,
    COUNT(*) as forms,
    MIN(created_at) as first_form,
    MAX(created_at) as last_form
FROM compliance_batch_forms
GROUP BY batch_id
ORDER BY batch_id DESC
LIMIT 5;
```

**Expected:** Each batch has forms > 0
