# DEPLOYMENT & VERIFICATION CHECKLIST

## PRE-DEPLOYMENT VERIFICATION

### Code Review
- [x] FrequencyEngine.php - Frequency matching logic
- [x] BatchOrchestrator.php - Batch creation orchestration
- [x] ComplianceExecutionController.php - Simplified createBatch()
- [x] ComplianceOrchestrator.php - File path updates
- [x] ComplianceBatchForm.php - Helper methods
- [x] Migration file - File path default value

### Database Schema
- [x] compliance_forms_master.frequency - Enum values correct
- [x] compliance_execution_batches.form_ids - JSON array
- [x] compliance_batch_forms.file_path - Default pending placeholder
- [x] compliance_batch_forms.status - Pending/success/failed
- [x] All tables have tenant_id for multi-tenant filtering

### Architecture
- [x] Separation of concerns - Each service has single responsibility
- [x] No code duplication - Frequency logic centralized
- [x] Backward compatibility - All existing systems intact
- [x] Error handling - Proper exception handling throughout
- [x] Logging - Execution logs captured

---

## DEPLOYMENT STEPS

### Step 1: Backup Database
```bash
# Create backup before migration
mysqldump -u root -p compliance_engine > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Run Migration
```bash
php artisan migrate
# Output: Migration 2026_03_20_000012_fix_batch_forms_file_path completed
```

### Step 3: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:cache
```

### Step 4: Verify Services Registered
```bash
php artisan tinker
>>> app(\App\Services\Compliance\FrequencyEngine::class)
>>> app(\App\Services\Compliance\BatchOrchestrator::class)
# Should return service instances without errors
```

### Step 5: Test Batch Creation
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
>>> $batch = $orchestrator->createBatch(1, 3, 2024);
>>> $batch->id
# Should return batch ID (e.g., 123)
```

### Step 6: Verify Forms Attached
```bash
>>> $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)->get();
>>> $forms->count()
# Should return number > 0
>>> $forms->first()->file_path
# Should return pending placeholder path
```

### Step 7: Test Dashboard
1. Navigate to `/compliance/dashboard`
2. Verify "Create Compliance Batch" form displays
3. Select Month = 3, Year = 2024
4. Click "Create Batch"
5. Verify success message
6. Verify batch appears in "Recent Batches" table

### Step 8: Test Form Preview
1. Click "Preview" on a form in recent batch
2. Verify form HTML renders
3. Verify data populated correctly

### Step 9: Test Batch Processing
1. Click "Process Batch"
2. Monitor logs for PDF generation
3. Verify file_path updated in database
4. Verify status changed to 'success'

### Step 10: Test Download
1. Click "Download" or "Inspection Pack"
2. Verify ZIP file downloads
3. Verify forms included in ZIP

---

## POST-DEPLOYMENT VERIFICATION

### Database Integrity
```sql
-- Check batch created
SELECT * FROM compliance_execution_batches 
WHERE id = (SELECT MAX(id) FROM compliance_execution_batches);

-- Check forms attached
SELECT COUNT(*) FROM compliance_batch_forms 
WHERE batch_id = (SELECT MAX(id) FROM compliance_execution_batches);

-- Check file paths not NULL
SELECT COUNT(*) FROM compliance_batch_forms 
WHERE file_path IS NULL;
# Should return 0

-- Check pending placeholders
SELECT COUNT(*) FROM compliance_batch_forms 
WHERE file_path LIKE '%pending%';
# Should return > 0 for new batches
```

### Application Logs
```bash
# Check for errors
tail -f storage/logs/laravel.log | grep -i error

# Check for warnings
tail -f storage/logs/laravel.log | grep -i warning
```

### Performance Metrics
```bash
# Monitor batch creation time
php artisan tinker
>>> $start = microtime(true);
>>> $batch = app(\App\Services\Compliance\BatchOrchestrator::class)->createBatch(1, 3, 2024);
>>> echo (microtime(true) - $start) . " seconds";
# Should be < 1 second
```

---

## ROLLBACK PROCEDURE

If issues occur:

### Step 1: Rollback Migration
```bash
php artisan migrate:rollback
```

### Step 2: Restore Backup
```bash
mysql -u root -p compliance_engine < backup_YYYYMMDD_HHMMSS.sql
```

### Step 3: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
```

### Step 4: Verify Rollback
```bash
php artisan tinker
>>> \App\Models\ComplianceExecutionBatch::count()
# Should return previous count
```

---

## TESTING SCENARIOS

### Scenario 1: Monthly Forms Only
```
Month: 1 (January)
Expected: Only monthly forms
Verify: No quarterly/half-yearly/yearly forms
```

### Scenario 2: Quarterly Forms
```
Month: 3 (March)
Expected: Monthly + Quarterly forms
Verify: Half-yearly and yearly forms NOT included
```

### Scenario 3: Half-Yearly Forms
```
Month: 6 (June)
Expected: Monthly + Quarterly + Half-yearly forms
Verify: Yearly forms NOT included
```

### Scenario 4: Yearly Forms
```
Month: 12 (December)
Expected: All forms (Monthly + Quarterly + Half-yearly + Yearly)
Verify: All applicable forms included
```

### Scenario 5: Multi-Tenant Isolation
```
Tenant A: Create batch
Tenant B: Try to access batch
Expected: Access denied
Verify: Tenant B cannot see Tenant A's batch
```

### Scenario 6: Form Generation
```
Create batch → Preview form → Process batch → Download
Expected: All steps succeed
Verify: File paths updated, PDFs generated, ZIP created
```

---

## MONITORING CHECKLIST

### Daily Checks
- [ ] No errors in application logs
- [ ] Batch creation succeeds
- [ ] Forms preview correctly
- [ ] PDFs generate without errors
- [ ] Downloads work properly

### Weekly Checks
- [ ] Database integrity verified
- [ ] Performance metrics acceptable
- [ ] Multi-tenant isolation working
- [ ] File storage not full
- [ ] Backup completed successfully

### Monthly Checks
- [ ] Review execution logs
- [ ] Analyze performance trends
- [ ] Check for deprecated code usage
- [ ] Verify all forms generating correctly
- [ ] Update documentation if needed

---

## TROUBLESHOOTING GUIDE

### Issue: "No forms applicable for month"

**Diagnosis:**
```bash
php artisan tinker
>>> $engine = app(\App\Services\Compliance\FrequencyEngine::class);
>>> $forms = $engine->getApplicableForms(3);
>>> $forms->count()
```

**Solutions:**
1. Check compliance_forms_master has active forms
2. Verify frequency column values (Monthly, Quarterly, etc.)
3. Check is_active = 1 for forms

### Issue: "No branch configured for this tenant"

**Diagnosis:**
```bash
>>> \App\Models\Branch::where('tenant_id', 1)->first()
```

**Solutions:**
1. Create branch for tenant
2. Verify tenant_id in branches table
3. Check branch is not soft-deleted

### Issue: "file_path is NULL"

**Diagnosis:**
```bash
>>> \App\Models\ComplianceBatchForm::whereNull('file_path')->count()
```

**Solutions:**
1. Run migration: `php artisan migrate`
2. Update existing records: 
   ```sql
   UPDATE compliance_batch_forms 
   SET file_path = 'storage/forms/pending/placeholder.pdf' 
   WHERE file_path IS NULL;
   ```

### Issue: "PDF generation failed"

**Diagnosis:**
```bash
# Check logs
tail -f storage/logs/laravel.log | grep -i pdf

# Check storage permissions
ls -la storage/app/generated_forms/
```

**Solutions:**
1. Verify storage directory writable: `chmod 755 storage/app`
2. Check PDF generator installed: `php -m | grep pdf`
3. Review form generator code for errors

### Issue: "Multi-tenant data leakage"

**Diagnosis:**
```bash
>>> \App\Models\ComplianceBatchForm::where('batch_id', 123)->get()
# Check if tenant_id filtering applied
```

**Solutions:**
1. Always filter by tenant_id
2. Use scopes in models
3. Review all queries for tenant filtering

---

## SUCCESS CRITERIA

✅ **Batch Creation**
- User selects Month + Year
- Batch created successfully
- Forms detected by frequency
- Forms attached with pending paths

✅ **Form Preview**
- Preview renders HTML
- Data populated correctly
- No SQL errors

✅ **Batch Processing**
- PDFs generated successfully
- File paths updated in database
- Status changed to 'success'

✅ **Download**
- ZIP file created
- All forms included
- Files readable

✅ **Multi-Tenant**
- Tenant A cannot access Tenant B's data
- All queries filter by tenant_id
- No data leakage

✅ **Performance**
- Batch creation < 1 second
- Form preview < 2 seconds
- PDF generation < 5 seconds

✅ **Backward Compatibility**
- All existing systems work
- No breaking changes
- All tests pass

---

## SIGN-OFF

**Deployment Date:** _______________

**Deployed By:** _______________

**Verified By:** _______________

**Status:** ✅ PRODUCTION READY

**Notes:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

---

## CONTACT & SUPPORT

For issues or questions:
1. Review DASHBOARD_WORKFLOW_REFACTORING.md
2. Check DASHBOARD_WORKFLOW_QUICK_REFERENCE.md
3. Review troubleshooting guide above
4. Check application logs
5. Contact development team
