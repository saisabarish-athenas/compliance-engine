# QUICK TEST CHECKLIST

## Pre-Test Setup

- [ ] Ensure user has FULL subscription
- [ ] Ensure tenant has valid data
- [ ] Ensure branch exists
- [ ] Clear logs: `echo. > storage\logs\laravel.log`

---

## Test: Process FULL Subscription Batch

### Step 1: Create Batch
- [ ] Login as FULL user
- [ ] Navigate to Compliance Dashboard
- [ ] Select section (e.g., Factories Act)
- [ ] Select period (month/year)
- [ ] Select forms (e.g., FORM_B, FORM_XIII)
- [ ] Click "Create Batch"
- [ ] Note the Batch ID: _______

### Step 2: Process Batch
- [ ] Click "Process Batch" button
- [ ] Wait for completion
- [ ] Verify success message appears
- [ ] Verify batch status shows "Completed"

### Step 3: Check Logs
```bash
type storage\logs\laravel.log | findstr "batch_id"
```

**Expected Output:**
```
[batch_id => X, tenant_id => Y, is_full => true, form_code => 'FORM_B']
[post_insert_count => 1]
[batch_id => X, tenant_id => Y, is_full => true, form_code => 'FORM_XIII']
[post_insert_count => 2]
[batch_processing_complete => true, batch_id => X, final_form_count => 2]
```

- [ ] Logs show per-form processing
- [ ] Logs show incrementing post_insert_count
- [ ] Logs show final batch_processing_complete
- [ ] final_form_count matches number of forms

### Step 4: Check Database
```sql
SELECT * FROM compliance_batch_forms WHERE batch_id = [BATCH_ID];
```

- [ ] Records exist
- [ ] Count matches final_form_count
- [ ] All form_codes present
- [ ] All have status = 'success'
- [ ] All have valid file_path

### Step 5: Check Files
```bash
dir storage\app\generated_forms\[TENANT_ID]\[BATCH_ID]
```

- [ ] Directory exists
- [ ] PDF files exist
- [ ] File names match pattern: `{form_code}_{batch_id}_{timestamp}.pdf`
- [ ] File count matches database count

### Step 6: Download Inspection Pack
- [ ] Click "Inspection Pack" button
- [ ] Verify ZIP downloads automatically
- [ ] Extract ZIP file
- [ ] Verify all PDFs are inside
- [ ] Verify PDF count matches database count

---

## Test: MINIMAL Subscription (Verify Untouched)

### Step 1: Create Batch
- [ ] Login as MINIMAL user
- [ ] Create batch
- [ ] Process batch

### Step 2: Verify No Persistence
```sql
SELECT * FROM compliance_batch_forms WHERE batch_id = [BATCH_ID];
```

- [ ] Returns 0 rows (no records)

### Step 3: Verify Inspection Pack Blocked
- [ ] Attempt to access Inspection Pack
- [ ] Verify HTTP 403 error
- [ ] Verify error message: "Inspection Pack available only for FULL subscription."

---

## Verification Queries

### Count Forms Per Batch
```sql
SELECT 
    batch_id,
    COUNT(*) as form_count,
    GROUP_CONCAT(form_code) as forms
FROM compliance_batch_forms
GROUP BY batch_id;
```

### Check Recent Batches
```sql
SELECT 
    ceb.id,
    ceb.tenant_id,
    t.subscription_type,
    ceb.status,
    COUNT(cbf.id) as stored_forms
FROM compliance_execution_batches ceb
INNER JOIN tenants t ON ceb.tenant_id = t.id
LEFT JOIN compliance_batch_forms cbf ON ceb.id = cbf.batch_id
WHERE ceb.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY ceb.id, ceb.tenant_id, t.subscription_type, ceb.status;
```

### Verify No Orphans
```sql
SELECT COUNT(*) as orphaned_forms
FROM compliance_batch_forms cbf
LEFT JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE ceb.id IS NULL;
```

**Expected:** 0

---

## Success Criteria

### FULL Subscription:
✅ Batch processes successfully  
✅ Logs show all processing steps  
✅ Database has records  
✅ Files exist in storage  
✅ Inspection Pack downloads ZIP  
✅ ZIP contains all PDFs  

### MINIMAL Subscription:
✅ Batch processes successfully  
✅ No records in compliance_batch_forms  
✅ Inspection Pack returns 403  

### System Stability:
✅ No errors in logs  
✅ No 422 errors  
✅ No 500 errors  
✅ All features working  

---

## If Test Fails

### No logs appearing:
1. Check log file exists: `storage\logs\laravel.log`
2. Check log permissions
3. Check APP_DEBUG=true in .env

### No database records:
1. Check subscription type: `SELECT subscription_type FROM tenants WHERE id = X;`
2. Check logs for errors
3. Check if generator returned PDF content

### No files in storage:
1. Check directory permissions
2. Check storage path exists
3. Check logs for file save errors

### Inspection Pack 422:
1. Run: `SELECT * FROM compliance_batch_forms WHERE batch_id = X;`
2. If empty, batch didn't persist (check subscription)
3. If exists, check file_path values

---

## Quick Health Check

```sql
SELECT 
    'Total FULL Batches' as metric,
    COUNT(*) as count
FROM compliance_execution_batches ceb
INNER JOIN tenants t ON ceb.tenant_id = t.id
WHERE t.subscription_type = 'FULL'
UNION ALL
SELECT 
    'FULL Batches with Forms',
    COUNT(DISTINCT cbf.batch_id)
FROM compliance_batch_forms cbf
INNER JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
INNER JOIN tenants t ON ceb.tenant_id = t.id
WHERE t.subscription_type = 'FULL'
UNION ALL
SELECT 
    'Total Stored Forms',
    COUNT(*)
FROM compliance_batch_forms;
```

**Expected:** All counts > 0 after testing
