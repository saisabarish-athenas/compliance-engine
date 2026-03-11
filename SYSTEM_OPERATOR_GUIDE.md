# COMPLIANCE ENGINE - QUICK REFERENCE GUIDE

## SYSTEM WORKFLOW (CORRECTED)

### 1. CREATE BATCH
User selects:
- Statutory Section (e.g., Factories Act)
- Period (Month/Year)
- Forms to generate
- Branch (optional)

System creates `ComplianceExecutionBatch` record.

---

### 2. PROCESS BATCH
User clicks "Process" or system auto-processes.

**Execution Flow:**
```
For each form in batch:
  1. Generate PDF using FormGenerator
  2. Store PDF in storage/app/generated_forms/{tenant}/{batch}/{form}.pdf
  3. Create ComplianceBatchForm record
  4. Log generation in compliance_generation_logs

After all forms generated:
  5. Run ComplianceAuditService::auditBatch()
     - Audits each form
     - Creates ComplianceAuditLog records
     - Calculates batch average score
  
  6. Run ComplianceCertificationService::certifyBatch()
     - Validates all forms
     - Creates ComplianceCertificationLog records
     - Stores certification score and status

Batch status updated to: completed/partially_completed/failed
```

---

### 3. DASHBOARD DISPLAYS
Dashboard automatically shows:
- **Batch ID** - Unique identifier
- **Section** - Statutory section
- **Period** - Month/Year
- **Status** - Pending/Processing/Completed/Failed
- **Audit Score** - Average score from all forms (0-100)
- **Audit Status** - Passed/Failed/Partial
- **Certification** - Certified/Not Certified
- **Created Date** - When batch was created

**Data Sources:**
- Batch status: `compliance_generation_logs`
- Audit score: `compliance_audit_logs` (average)
- Audit status: `compliance_audit_logs` (passed count)
- Certification: `compliance_certification_logs` (BATCH_SUMMARY)

---

### 4. PREVIEW FORM
User clicks "Preview" on a form.

**System:**
1. Fetches form data using `ComplianceDataService::buildFormData()`
2. Normalizes data (ensures header, rows, entries, totals)
3. Renders Blade template with normalized data
4. Returns HTML/PDF preview

**Data Structure Guaranteed:**
```php
[
    'header' => [
        'tenant' => ['name' => '...'],
        'owner_name' => '...',
        'wage_period' => 'Monthly',
        'period' => '01/2024'
    ],
    'rows' => [...],           // Employee records
    'entries' => [...],        // Same as rows (bidirectional)
    'totals' => [...],         // Aggregated totals
    'is_nil' => false,         // NIL dataset flag
    'period' => '01/2024'
]
```

---

### 5. FIX VIOLATIONS
User clicks "Fix Issues" on a form with violations.

**System:**
1. Fetches violations from `compliance_audit_logs`
2. Attempts auto-fix (fetch from database)
3. If auto-fix incomplete, prompts user for missing fields
4. User submits corrections
5. System regenerates PDF with corrections
6. **CRITICAL:** Re-audits immediately
7. Updates `compliance_audit_logs` with new score
8. Dashboard refreshes with updated score

**Result:**
- Audit score updated
- Audit status updated
- Dashboard reflects changes immediately

---

### 6. DOWNLOAD INSPECTION PACK
User clicks "Inspection Pack".

**System:**
1. Checks certification status (must be ≥70 score)
2. Fetches all forms with status='success' from `compliance_batch_forms`
3. Filters out forms that failed audit
4. Creates ZIP file with all valid PDFs
5. Streams ZIP download
6. Deletes temporary ZIP file

**ZIP Contents:**
```
inspection_pack_batch_221.zip
├── FORM_B.pdf
├── FORM_10.pdf
├── FORM_12.pdf
├── FORM_17.pdf
└── ...
```

---

## SUBSCRIPTION TYPES

### FULL SUBSCRIPTION
- **Data Source:** Database (workforce_employee, workforce_payroll_entry, etc.)
- **Payroll Requirement:** Must have processed payroll for period
- **Form Generation:** Automatic from database
- **Audit:** Runs automatically
- **Certification:** Runs automatically

### MINIMAL SUBSCRIPTION
- **Data Source:** Manual file uploads (CSV/Excel)
- **Payroll Requirement:** None
- **Form Generation:** From uploaded data
- **Audit:** Runs automatically
- **Certification:** Runs automatically

---

## AUDIT SCORING

### Score Calculation
```
Base Score: 100
For each violation: -5 points
Minimum Score: 0

Score ≥ 70 = PASSED
Score < 70 = FAILED
```

### Audit Status
- **Passed:** All forms scored ≥70
- **Failed:** All forms scored <70
- **Partial:** Some forms passed, some failed

### Batch Average Score
```
Average = Sum of all form scores / Number of forms
```

---

## CERTIFICATION SCORING

### Certification Rules
- **Score 100:** Inspection Ready (certified=true)
- **Score 70-99:** Minor Issues (certified=false)
- **Score <70:** Correction Required (certified=false)

### Certification Status
- **Certified:** Score=100 AND no critical errors
- **Not Certified:** Score<100 OR critical errors exist

---

## COMMON ISSUES & SOLUTIONS

### Issue: Audit Score Not Showing
**Cause:** Audit logs not created
**Solution:** 
1. Check `compliance_audit_logs` table
2. Verify batch was processed (status='completed')
3. Check logs: `tail -f storage/logs/laravel.log | grep "Batch audit"`

### Issue: Certification Not Updating
**Cause:** Certification not running after audit
**Solution:**
1. Check `compliance_certification_logs` table
2. Verify form_code='BATCH_SUMMARY' exists
3. Check logs: `tail -f storage/logs/laravel.log | grep "Batch certification"`

### Issue: Preview Form Failing
**Cause:** Inconsistent data structure
**Solution:**
1. Check builder returns all required fields
2. Verify `ComplianceDataService::normalizeData()` is called
3. Check logs for template errors

### Issue: Fix Issues Not Updating Score
**Cause:** Re-audit not running after correction
**Solution:**
1. Check `compliance_audit_logs` updated_at timestamp
2. Verify correction service calls `auditService->audit()`
3. Check logs: `tail -f storage/logs/laravel.log | grep "Violation correction"`

### Issue: Inspection Pack Missing Forms
**Cause:** Forms have status='failed' or audit failed
**Solution:**
1. Check `compliance_batch_forms` status
2. Check `compliance_audit_logs` for failed forms
3. Fix violations and re-audit

---

## DATABASE QUERIES

### Check Audit Logs
```sql
SELECT * FROM compliance_audit_logs 
WHERE batch_id = 221 
ORDER BY form_code;
```

### Check Certification Status
```sql
SELECT * FROM compliance_certification_logs 
WHERE batch_id = 221 
AND form_code = 'BATCH_SUMMARY';
```

### Check Batch Status
```sql
SELECT * FROM compliance_execution_batches 
WHERE id = 221;
```

### Check Generated Forms
```sql
SELECT * FROM compliance_batch_forms 
WHERE batch_id = 221 
AND status = 'success';
```

### Calculate Batch Average Score
```sql
SELECT 
    batch_id,
    ROUND(AVG(audit_score)) as avg_score,
    COUNT(*) as total_forms,
    SUM(CASE WHEN status='passed' THEN 1 ELSE 0 END) as passed_forms
FROM compliance_audit_logs 
WHERE batch_id = 221 
GROUP BY batch_id;
```

---

## MONITORING CHECKLIST

Daily:
- [ ] Check dashboard loads without errors
- [ ] Verify audit scores display
- [ ] Verify certification status displays
- [ ] Check no error logs

Weekly:
- [ ] Verify batch processing completes
- [ ] Check audit logs created
- [ ] Check certification logs created
- [ ] Test correction engine

Monthly:
- [ ] Review system performance
- [ ] Check database size
- [ ] Verify backup integrity
- [ ] Test disaster recovery

---

## PERFORMANCE METRICS

| Operation | Time | Notes |
|-----------|------|-------|
| Form Generation | 2-5s | Per form |
| Audit (single form) | 50ms | Negligible |
| Audit (batch) | 500ms-2s | Depends on form count |
| Certification | 100ms-500ms | Per batch |
| Dashboard Load | <1s | Optimized queries |
| Inspection Pack | 1-3s | Depends on file count |

---

## SUPPORT CONTACTS

For issues:
1. Check logs: `storage/logs/laravel.log`
2. Review this guide
3. Check database consistency
4. Contact development team

---

## VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024-01-20 | Initial stabilization |
| 1.1 | 2024-01-21 | Audit engine fixes |
| 1.2 | 2024-01-22 | Certification persistence |
| 1.3 | 2024-01-23 | Data normalization |

---

**Last Updated:** 2024-01-23
**Status:** PRODUCTION READY
