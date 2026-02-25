# PRODUCTION DEPLOYMENT CHECKLIST

## Pre-Deployment

### 1. Schema Validation
```bash
php artisan compliance:repair-schema --dry-run
```
- [ ] No schema mismatches detected
- [ ] All required columns exist
- [ ] No duplicate column errors

### 2. Apply Schema Repairs (if needed)
```bash
php artisan compliance:repair-schema
```
- [ ] Schema repairs applied successfully
- [ ] No errors during repair
- [ ] All columns added

### 3. Configure Statutory Settings
Navigate to: `/compliance/settings`

For each tenant:
- [ ] Establishment Name filled
- [ ] Factory License Number filled
- [ ] PF Code filled (if applicable)
- [ ] ESI Code filled (if applicable)

For each branch:
- [ ] Unit Name filled
- [ ] Address filled

### 4. Run Production Ready Check
```bash
php artisan compliance:production-ready-check
```
- [ ] Schema Integrity: PASS
- [ ] Statutory Settings: PASS
- [ ] Generator Coverage: PASS (36/36)
- [ ] Config Mapping: PASS
- [ ] Tenant Isolation: PASS
- [ ] Memory Threshold: PASS (<150MB)
- [ ] Required Indexes: PASS

### 5. System Integrity Check
```bash
php artisan compliance:system-check
```
- [ ] Form Generation: PASS
- [ ] Database Integrity: PASS
- [ ] Config Validation: PASS
- [ ] Route Protection: PASS
- [ ] Subscription Enforcement: PASS
- [ ] Tenant Isolation: PASS
- [ ] Statutory Settings: PASS
- [ ] OVERALL STATUS: PASS

### 6. Test Form Generation
```bash
php artisan compliance:test-generation --all
```
- [ ] Success: 36/36 forms
- [ ] No memory errors
- [ ] All PDFs generated
- [ ] Peak memory <150MB

### 7. Validate Wage Calculations
```bash
php artisan compliance:validate-wages {tenant_id} 1 2026
```
- [ ] Compliant: All employees
- [ ] Violations: 0
- [ ] No wage inconsistencies

### 8. Repair Payroll Data (if needed)
```bash
php artisan compliance:repair-payroll-data {tenant_id} 1 2026
```
- [ ] Attendance records created
- [ ] Payroll entries updated
- [ ] All employees processed

## Deployment

### 9. Database Backup
```bash
# Backup production database
php artisan db:backup
```
- [ ] Database backed up
- [ ] Backup verified
- [ ] Backup stored securely

### 10. Run Migrations
```bash
php artisan migrate --force
```
- [ ] Migrations completed
- [ ] No errors
- [ ] All tables updated

### 11. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```
- [ ] Config cache cleared
- [ ] Application cache cleared
- [ ] Route cache cleared
- [ ] View cache cleared

### 12. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```
- [ ] Config cached
- [ ] Routes cached
- [ ] Views compiled
- [ ] Autoloader optimized

## Post-Deployment

### 13. Verify Production Environment
```bash
php artisan env
```
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_URL correct
- [ ] Database connection correct

### 14. Test Critical Paths

**Authentication**:
- [ ] Login works
- [ ] Logout works
- [ ] Session management correct

**Dashboard**:
- [ ] Dashboard loads
- [ ] Metrics display correctly
- [ ] No errors in console

**Settings**:
- [ ] Settings page loads
- [ ] Can update settings
- [ ] Changes persist

**Form Generation**:
- [ ] Can create batch
- [ ] Can process batch
- [ ] PDFs generate correctly
- [ ] Download works

### 15. Monitor Performance

**Memory Usage**:
- [ ] Peak memory <150MB
- [ ] No memory leaks
- [ ] Garbage collection working

**Response Times**:
- [ ] Dashboard <2s
- [ ] Form generation <5s per form
- [ ] Batch processing <3min for 36 forms

**Error Logs**:
- [ ] No critical errors
- [ ] No warnings
- [ ] Logs rotating correctly

### 16. Verify Data Integrity

**Tenants**:
- [ ] All tenants have statutory settings
- [ ] No missing establishment names
- [ ] No missing license numbers

**Branches**:
- [ ] All branches have unit names
- [ ] All branches have addresses
- [ ] Branch isolation working

**Employees**:
- [ ] All employees have basic_salary
- [ ] All employees have status
- [ ] Employee data intact

**Payroll**:
- [ ] Wage calculations correct
- [ ] No zero wages with attendance
- [ ] Totals consistent

**Attendance**:
- [ ] Attendance records present
- [ ] Date ranges correct
- [ ] Status values valid

### 17. Security Validation

**Subscription Enforcement**:
- [ ] MINIMAL users cannot access automation
- [ ] FULL users can access all features
- [ ] Middleware working correctly

**Tenant Isolation**:
- [ ] Users see only their tenant data
- [ ] No cross-tenant data leakage
- [ ] Queries filtered by tenant_id

**Route Protection**:
- [ ] All routes require authentication
- [ ] Automation routes protected
- [ ] Settings routes protected

### 18. Final Production Check
```bash
php artisan compliance:production-ready-check
```
- [ ] All 7 checks PASS
- [ ] SYSTEM STATUS: PRODUCTION READY

## Rollback Plan

If any check fails:

### 1. Stop Deployment
- [ ] Do not proceed
- [ ] Document failure
- [ ] Notify team

### 2. Restore Database
```bash
php artisan db:restore {backup_file}
```
- [ ] Database restored
- [ ] Data verified
- [ ] Application working

### 3. Investigate Issue
- [ ] Review error logs
- [ ] Identify root cause
- [ ] Create fix plan

### 4. Test Fix in Staging
- [ ] Apply fix in staging
- [ ] Run all checks
- [ ] Verify resolution

### 5. Retry Deployment
- [ ] Follow checklist again
- [ ] Monitor closely
- [ ] Document changes

## Success Criteria

All items checked ✅

**Critical Checks**:
- Schema integrity: PASS
- Statutory settings: PASS
- Form generation: 36/36
- Wage validation: 0 violations
- Production ready check: PASS
- System check: PASS

**Performance**:
- Memory peak: <150MB
- Form generation: <5s per form
- No errors in logs

**Security**:
- Subscription enforcement: Working
- Tenant isolation: Working
- Route protection: Working

---

**DEPLOYMENT STATUS**: 
- [ ] READY TO DEPLOY
- [ ] DEPLOYED SUCCESSFULLY
- [ ] VERIFIED IN PRODUCTION

**Deployed By**: _______________
**Date**: _______________
**Time**: _______________
**Version**: _______________
