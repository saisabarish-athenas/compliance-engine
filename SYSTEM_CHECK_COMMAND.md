# Compliance System Check Command

## Overview
The `compliance:system-check` command performs a comprehensive integrity check of the Compliance Engine, validating all critical components and configurations.

## Usage

```bash
php artisan compliance:system-check
```

## What It Checks

### 1. Form Generation Check ✅
- Tests generation of all 36 statutory forms
- Uses temporary storage (auto-cleanup)
- Counts success vs failures
- Catches exceptions safely
- **Pass Criteria:** All 36 forms generate successfully

### 2. Database Integrity Check ✅
- Verifies `compliance_timelines` table exists
- Verifies `workforce_attendance` table exists
- Validates required columns:
  - `tenant_id`
  - `period_month`
  - `period_year`
  - `due_date`
- **Pass Criteria:** All tables and columns present

### 3. Config Validation ✅
- Ensures 36 forms exist in `config/compliance_forms.php`
- Validates each form has:
  - `table` key
  - `date_field` key
- **Pass Criteria:** All 36 forms properly configured

### 4. Route Protection Check ✅
- Confirms automation routes use `CheckSubscriptionAccess` middleware:
  - `POST /compliance/batch/process/{id}`
  - `GET /compliance/batch/{batch}/preview/{form}`
  - `GET /compliance/batch/{batch}/inspection-pack`
- Confirms all compliance routes use `CheckSubscription` middleware
- **Pass Criteria:** All routes properly protected

### 5. Subscription Enforcement Check ✅
- Simulates MINIMAL subscription user
- Attempts to call `processBatch()`
- Confirms exception is thrown
- **Pass Criteria:** Exception contains "MINIMAL" keyword

### 6. Tenant Isolation Check ✅
- Verifies `FormDataAggregator` applies tenant filtering
- Checks for `tenant_id` and `where` clauses in source code
- **Pass Criteria:** Tenant filtering detected in aggregator

## Output Format

```
-----------------------------------------
COMPLIANCE SYSTEM INTEGRITY REPORT
-----------------------------------------

Checking Form Generation...
Forms: 36/36 ✅ PASS

Checking Database Integrity...
Timeline Table: ✅ OK
Attendance Table: ✅ OK

Checking Config Validation...
Config Mapping: ✅ OK (36/36 forms)

Checking Route Protection...
Route Protection: ✅ OK

Checking Subscription Enforcement...
Subscription Enforcement: ✅ OK

Checking Tenant Isolation...
Tenant Isolation: ✅ OK

-----------------------------------------
OVERALL STATUS: ✅ PASS
-----------------------------------------
```

## Exit Codes

- **0** - All checks passed
- **1** - One or more checks failed

## Error Examples

### Failed Form Generation
```
Forms: 30/36 ❌ FAIL (6 failed)
```

### Missing Database Table
```
Timeline Table: ❌ FAIL (table not found)
```

### Missing Config Keys
```
Config Mapping: ❌ FAIL (5 forms missing table/date_field)
```

### Missing Route Protection
```
Route Protection: ❌ FAIL (missing middleware)
```

### Subscription Enforcement Failure
```
Subscription Enforcement: ❌ FAIL (no exception thrown)
```

### Tenant Isolation Failure
```
Tenant Isolation: ❌ FAIL (no tenant filtering detected)
```

## When to Run

### Development
- After making changes to form generators
- After database migrations
- After modifying routes or middleware
- Before committing major changes

### Deployment
- Before deploying to staging
- Before deploying to production
- As part of CI/CD pipeline
- After deployment verification

### Maintenance
- Weekly health checks
- After system updates
- When investigating issues
- Before major releases

## Integration with CI/CD

### GitHub Actions Example
```yaml
- name: Run System Check
  run: php artisan compliance:system-check
```

### GitLab CI Example
```yaml
system_check:
  script:
    - php artisan compliance:system-check
```

### Jenkins Example
```groovy
stage('System Check') {
    steps {
        sh 'php artisan compliance:system-check'
    }
}
```

## Troubleshooting

### "Forms: SKIP (No test data)"
**Cause:** No FULL subscription tenant or branch in database  
**Solution:** Run seeders: `php artisan db:seed`

### "Subscription Enforcement: SKIP (No MINIMAL tenant)"
**Cause:** No MINIMAL subscription tenant in database  
**Solution:** Create MINIMAL tenant or run seeders

### "Subscription Enforcement: SKIP (No test batch)"
**Cause:** No batch exists for MINIMAL tenant  
**Solution:** Create a test batch for MINIMAL tenant

## Technical Details

### File Location
```
app/Console/Commands/ComplianceSystemCheck.php
```

### Dependencies
- `FormGeneratorFactory` - For form generation testing
- `ComplianceExecutionService` - For subscription enforcement testing
- `Route` facade - For route protection validation
- `DB` facade - For database integrity checks
- `Storage` facade - For temporary file management

### Performance
- **Execution Time:** ~5-10 seconds (with form generation)
- **Execution Time:** ~1-2 seconds (without test data)
- **Memory Usage:** Minimal (temp files auto-deleted)
- **Database Impact:** Read-only queries

### Safety
- ✅ No permanent file creation
- ✅ No database modifications
- ✅ No side effects
- ✅ Safe to run in production
- ✅ Exception handling on all checks

## Best Practices

1. **Run Before Deployment**
   ```bash
   php artisan compliance:system-check && git push
   ```

2. **Add to Pre-Commit Hook**
   ```bash
   #!/bin/bash
   php artisan compliance:system-check || exit 1
   ```

3. **Schedule Regular Checks**
   ```php
   // routes/console.php
   Schedule::command('compliance:system-check')
       ->weekly()
       ->emailOutputOnFailure('admin@example.com');
   ```

4. **Monitor in Production**
   ```bash
   # Cron job
   0 2 * * * cd /path/to/app && php artisan compliance:system-check
   ```

## Related Commands

- `php artisan compliance:test-generation --all` - Test form generation only
- `php artisan compliance:check-due` - Check and update overdue timelines
- `php artisan migrate:status` - Check migration status
- `php artisan route:list --path=compliance` - List compliance routes

## Changelog

### Version 1.0.0
- Initial release
- 6 comprehensive system checks
- Clean output format
- Exit code support
- Exception handling
- Temporary file cleanup

## Support

For issues or questions about the system check command:
1. Review this documentation
2. Check COMPREHENSIVE_AUDIT_REPORT.md
3. Run with verbose output: `php artisan compliance:system-check -v`
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Command Status:** ✅ Production Ready  
**Last Updated:** 2024-01-XX  
**Maintainer:** Compliance Engine Team
