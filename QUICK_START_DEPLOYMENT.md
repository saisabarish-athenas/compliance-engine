# COMPLIANCE ENGINE - QUICK START DEPLOYMENT GUIDE

## 5-MINUTE DEPLOYMENT

### Step 1: Backup (1 minute)
```bash
# Backup database
php artisan backup:run

# Verify backup
ls -lh storage/backups/
```

### Step 2: Deploy Code (2 minutes)
```bash
# Pull latest changes
git pull origin main

# Install dependencies
composer install

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Step 3: Verify (2 minutes)
```bash
# Run verification
php artisan compliance:verify-engine

# Check logs
tail -f storage/logs/laravel.log
```

---

## IMMEDIATE VERIFICATION

### Check 1: Audit Logs
```bash
php artisan tinker

# Should return > 0
DB::table('compliance_audit_logs')->count();
```

### Check 2: Certification Logs
```bash
# Should return > 0
DB::table('compliance_certification_logs')->count();
```

### Check 3: Dashboard
```
Navigate to: http://localhost/compliance/dashboard

Verify:
✓ Audit Score displays
✓ Audit Status displays
✓ Certification Status displays
✓ No errors in console
```

### Check 4: Preview Form
```
Navigate to: http://localhost/compliance/batch/1/preview/FORM_B

Verify:
✓ Form renders
✓ Header displays
✓ Rows display
✓ No undefined variables
```

---

## COMMON ISSUES & QUICK FIXES

### Issue: Audit Score Not Showing
```bash
# Check if audit logs exist
php artisan tinker
DB::table('compliance_audit_logs')->where('batch_id', 1)->get();

# If empty, run audit manually
$service = app(App\Services\Compliance\Audit\ComplianceAuditService::class);
$service->auditBatch(1);
```

### Issue: Preview Form Crashing
```bash
# Check logs
tail -f storage/logs/laravel.log | grep -i error

# Clear cache
php artisan cache:clear

# Try preview again
```

### Issue: Certification Not Updating
```bash
# Check if certification logs exist
php artisan tinker
DB::table('compliance_certification_logs')->where('batch_id', 1)->get();

# If empty, run certification manually
$service = app(App\Services\Compliance\Validation\ComplianceCertificationService::class);
$service->certifyBatch(1);
```

---

## ROLLBACK (If Needed)

```bash
# Revert changes
git revert HEAD

# Clear cache
php artisan cache:clear

# Verify
php artisan compliance:verify-engine
```

---

## MONITORING CHECKLIST

### Daily
- [ ] Check error logs
- [ ] Verify audit logs created
- [ ] Verify certification logs created
- [ ] Dashboard loads without errors

### Weekly
- [ ] Run verification script
- [ ] Check performance metrics
- [ ] Test correction engine
- [ ] Review system logs

---

## SUCCESS INDICATORS

✅ **System is working correctly if:**
- Audit logs created automatically
- Certification logs created automatically
- Dashboard displays audit scores
- Dashboard displays certification status
- Preview forms render without errors
- Correction engine updates scores
- Inspection pack downloads successfully

❌ **System has issues if:**
- Audit logs not created
- Certification logs not created
- Dashboard shows "Not Audited"
- Preview forms crash
- Correction engine fails
- Inspection pack fails

---

## SUPPORT

**Issue?** Check these in order:
1. Read SYSTEM_OPERATOR_GUIDE.md
2. Check logs: `tail -f storage/logs/laravel.log`
3. Run verification: `php artisan compliance:verify-engine`
4. Contact: development@company.com

---

## DEPLOYMENT SIGN-OFF

**Deployed By:** ___________________
**Date:** ___________________
**Time:** ___________________
**Status:** ✓ SUCCESS / ✗ ISSUES

**Issues Found:**
_________________________________

**Sign-Off:** ___________________

---

**Deployment Complete!** 🎉

The system is now stabilized and ready for production use.
