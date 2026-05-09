# SQL VERIFICATION QUERIES

## 1. Check Batch ID Consistency

```sql
-- Verify all batch_ids in compliance_batch_forms exist in compliance_execution_batches
SELECT 
    cbf.batch_id,
    cbf.tenant_id,
    COUNT(*) as orphaned_forms
FROM compliance_batch_forms cbf
LEFT JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE ceb.id IS NULL
GROUP BY cbf.batch_id, cbf.tenant_id;

-- Expected: 0 rows (all batch_ids match)
```

## 2. Check Forms Per Batch

```sql
-- Count forms per batch
SELECT 
    batch_id,
    tenant_id,
    COUNT(*) as form_count,
    GROUP_CONCAT(form_code) as forms
FROM compliance_batch_forms
GROUP BY batch_id, tenant_id
ORDER BY batch_id DESC;
```

## 3. Verify Tenant Isolation

```sql
-- Check if batch_id and tenant_id match between tables
SELECT 
    cbf.batch_id,
    cbf.tenant_id as cbf_tenant_id,
    ceb.tenant_id as ceb_tenant_id,
    CASE 
        WHEN cbf.tenant_id = ceb.tenant_id THEN 'MATCH'
        ELSE 'MISMATCH'
    END as tenant_match
FROM compliance_batch_forms cbf
INNER JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE cbf.tenant_id != ceb.tenant_id;

-- Expected: 0 rows (all tenant_ids match)
```

## 4. Check File Paths

```sql
-- Verify file paths contain correct batch_id
SELECT 
    batch_id,
    form_code,
    file_path,
    CASE 
        WHEN file_path LIKE CONCAT('%/', batch_id, '/%') THEN 'CORRECT'
        ELSE 'INCORRECT'
    END as path_check
FROM compliance_batch_forms
WHERE file_path NOT LIKE CONCAT('%/', batch_id, '/%');

-- Expected: 0 rows (all paths contain correct batch_id)
```

## 5. Check Recent Batches

```sql
-- View recent batches with form counts
SELECT 
    ceb.id as batch_id,
    ceb.tenant_id,
    ceb.status,
    ceb.created_at,
    COUNT(cbf.id) as stored_forms
FROM compliance_execution_batches ceb
LEFT JOIN compliance_batch_forms cbf ON ceb.id = cbf.batch_id
WHERE ceb.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY ceb.id, ceb.tenant_id, ceb.status, ceb.created_at
ORDER BY ceb.created_at DESC;
```

## 6. Check Specific Batch

```sql
-- Replace [BATCH_ID] with actual batch ID
SET @batch_id = 1;

-- Check batch exists
SELECT * FROM compliance_execution_batches WHERE id = @batch_id;

-- Check forms for this batch
SELECT * FROM compliance_batch_forms WHERE batch_id = @batch_id;

-- Check generation logs
SELECT * FROM compliance_generation_logs WHERE batch_id = @batch_id;
```

## 7. Verify File Existence

```sql
-- Get file paths for verification
SELECT 
    batch_id,
    form_code,
    file_path,
    CONCAT('storage/app/', file_path) as full_path
FROM compliance_batch_forms
WHERE batch_id = 1
ORDER BY form_code;

-- Copy full_path values and check if files exist on filesystem
```

## 8. Check FULL Subscription Batches

```sql
-- Find batches for FULL subscription tenants
SELECT 
    ceb.id as batch_id,
    ceb.tenant_id,
    t.subscription_type,
    COUNT(cbf.id) as stored_forms
FROM compliance_execution_batches ceb
INNER JOIN tenants t ON ceb.tenant_id = t.id
LEFT JOIN compliance_batch_forms cbf ON ceb.id = cbf.batch_id
WHERE t.subscription_type = 'FULL'
GROUP BY ceb.id, ceb.tenant_id, t.subscription_type
ORDER BY ceb.id DESC
LIMIT 10;
```

## 9. Find Missing Forms

```sql
-- Batches that should have forms but don't
SELECT 
    ceb.id as batch_id,
    ceb.tenant_id,
    ceb.status,
    t.subscription_type,
    COUNT(cbf.id) as stored_forms
FROM compliance_execution_batches ceb
INNER JOIN tenants t ON ceb.tenant_id = t.id
LEFT JOIN compliance_batch_forms cbf ON ceb.id = cbf.batch_id
WHERE t.subscription_type = 'FULL'
  AND ceb.status IN ('completed', 'partially_completed')
  AND cbf.id IS NULL
GROUP BY ceb.id, ceb.tenant_id, ceb.status, t.subscription_type;

-- Expected: 0 rows (all completed FULL batches have forms)
```

## 10. Cleanup Orphaned Records (USE WITH CAUTION)

```sql
-- BACKUP FIRST!
-- Find orphaned records
SELECT * FROM compliance_batch_forms cbf
LEFT JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE ceb.id IS NULL;

-- Delete orphaned records (only if confirmed safe)
-- DELETE cbf FROM compliance_batch_forms cbf
-- LEFT JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
-- WHERE ceb.id IS NULL;
```

---

## Quick Health Check

```sql
-- Run this single query for overall health
SELECT 
    'Total Batches' as metric,
    COUNT(*) as count
FROM compliance_execution_batches
UNION ALL
SELECT 
    'Batches with Forms (FULL)',
    COUNT(DISTINCT cbf.batch_id)
FROM compliance_batch_forms cbf
INNER JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
INNER JOIN tenants t ON ceb.tenant_id = t.id
WHERE t.subscription_type = 'FULL'
UNION ALL
SELECT 
    'Total Stored Forms',
    COUNT(*)
FROM compliance_batch_forms
UNION ALL
SELECT 
    'Orphaned Forms',
    COUNT(*)
FROM compliance_batch_forms cbf
LEFT JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE ceb.id IS NULL
UNION ALL
SELECT 
    'Tenant Mismatches',
    COUNT(*)
FROM compliance_batch_forms cbf
INNER JOIN compliance_execution_batches ceb ON cbf.batch_id = ceb.id
WHERE cbf.tenant_id != ceb.tenant_id;
```

Expected output:
```
Total Batches: N
Batches with Forms (FULL): M
Total Stored Forms: X
Orphaned Forms: 0
Tenant Mismatches: 0
```
