# Compliance Orchestrator - Deployment Checklist

## Pre-Deployment

### Database
- [ ] Migration `2026_03_20_000001_create_compliance_execution_logs_table.php` exists
- [ ] Run migration: `php artisan migrate`
- [ ] Verify table created: `SELECT * FROM compliance_execution_logs LIMIT 1;`
- [ ] Verify indexes created
- [ ] Backup existing data

### Code
- [ ] All API services implemented in `app/Services/Compliance/FormApis/`
- [ ] FormApiServiceFactory registered all services
- [ ] ComplianceOrchestrator updated with API service integration
- [ ] All imports updated (no duplicate FormDataAggregator)
- [ ] No syntax errors: `php artisan tinker` (test imports)

### Configuration
- [ ] `config/compliance_forms.php` updated with all forms
- [ ] Tenant subscription_type field exists
- [ ] Storage directories created:
  - [ ] `storage/app/generated_forms/`
  - [ ] `storage/app/compliance_inspection_packs/`
  - [ ] `storage/app/compliance_pdfs/`

### Dependencies
- [ ] DomPDF installed: `composer require barryvdh/laravel-dompdf`
- [ ] ZipArchive available (PHP built-in)
- [ ] All service providers registered

## Deployment

### Step 1: Code Deployment
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 2: Database Migration
```bash
# Run migrations
php artisan migrate --force

# Verify migration
php artisan migrate:status
```

### Step 3: Service Registration
```bash
# Verify services are registered
php artisan tinker
>>> app(ComplianceOrchestrator::class)
>>> app(FormApiServiceFactory::class)
```

### Step 4: Storage Setup
```bash
# Create storage directories
mkdir -p storage/app/generated_forms
mkdir -p storage/app/compliance_inspection_packs
mkdir -p storage/app/compliance_pdfs

# Set permissions
chmod -R 755 storage/app/generated_forms
chmod -R 755 storage/app/compliance_inspection_packs
chmod -R 755 storage/app/compliance_pdfs
```

### Step 5: Configuration Verification
```bash
# Verify config
php artisan config:show compliance_forms

# Verify tenant subscriptions
php artisan tinker
>>> DB::table('tenants')->select('id', 'subscription_type')->get()
```

## Post-Deployment

### Testing

#### Unit Tests
```bash
# Run orchestrator tests
php artisan test tests/Unit/ComplianceOrchestratorTest.php

# Run API service tests
php artisan test tests/Unit/FormApis/
```

#### Integration Tests
```bash
# Run full workflow tests
php artisan test tests/Feature/ComplianceWorkflowTest.php
```

#### Manual Testing
```php
// Test preview mode
$orchestrator = app(ComplianceOrchestrator::class);
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
dd($result);

// Test PDF mode
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'pdf', 1);
dd($result);

// Test batch mode
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'batch', 1);
dd($result);

// Test inspection pack mode
$result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'inspection_pack', 1);
dd($result);
```

### Verification

#### Execution Logs
```bash
# Check execution logs
php artisan tinker
>>> DB::table('compliance_execution_logs')->latest()->first()
>>> DB::table('compliance_execution_logs')->where('status', 'failed')->get()
```

#### File Storage
```bash
# Verify files are stored
ls -la storage/app/generated_forms/
ls -la storage/app/compliance_inspection_packs/

# Check file sizes
du -sh storage/app/generated_forms/
du -sh storage/app/compliance_inspection_packs/
```

#### Subscription Enforcement
```bash
# Test subscription validation
php artisan tinker
>>> $tenant = Tenant::find(1);
>>> $tenant->subscription_type = 'MINIMAL';
>>> $tenant->save();
>>> $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
// Should fail with subscription error
```

#### Multi-Tenant Isolation
```bash
# Verify data isolation
php artisan tinker
>>> DB::table('compliance_execution_logs')->where('tenant_id', 1)->count()
>>> DB::table('compliance_execution_logs')->where('tenant_id', 2)->count()
// Should be separate
```

## Rollback Plan

### If Issues Occur

#### Rollback Database
```bash
# Rollback last migration
php artisan migrate:rollback

# Verify rollback
php artisan migrate:status
```

#### Rollback Code
```bash
# Revert to previous version
git revert HEAD

# Clear caches
php artisan cache:clear
php artisan config:clear
```

#### Restore Data
```bash
# Restore from backup
mysql -u user -p database < backup.sql
```

## Monitoring

### Key Metrics
- [ ] Execution time by form (target: < 2 seconds)
- [ ] Success rate (target: > 99%)
- [ ] Failed executions (investigate any)
- [ ] Storage usage (monitor growth)
- [ ] Subscription access denials (should be 0 for FULL users)

### Queries to Monitor
```sql
-- Execution time by form
SELECT form_code, AVG(execution_time) as avg_time, COUNT(*) as count
FROM compliance_execution_logs
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY form_code
ORDER BY avg_time DESC;

-- Failed executions
SELECT form_code, COUNT(*) as count, error_message
FROM compliance_execution_logs
WHERE status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY form_code, error_message;

-- Storage usage
SELECT 
    SUM(CHAR_LENGTH(content)) / 1024 / 1024 as size_mb
FROM compliance_execution_logs
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Alerts to Set Up
- [ ] Execution time > 5 seconds
- [ ] Failure rate > 5%
- [ ] Storage usage > 10GB
- [ ] Subscription access denials > 10/hour
- [ ] Database errors in logs

## Documentation

### Update Documentation
- [ ] Update API documentation with new endpoints
- [ ] Update developer guide with orchestrator usage
- [ ] Update deployment guide
- [ ] Update troubleshooting guide
- [ ] Update architecture diagram

### Create Runbooks
- [ ] How to add new form
- [ ] How to debug execution failures
- [ ] How to monitor performance
- [ ] How to handle storage cleanup
- [ ] How to migrate data

## Sign-Off

### Development Team
- [ ] Code review completed
- [ ] All tests passing
- [ ] Documentation updated
- [ ] Performance acceptable

### QA Team
- [ ] Manual testing completed
- [ ] Edge cases tested
- [ ] Error handling verified
- [ ] Multi-tenant isolation verified

### Operations Team
- [ ] Deployment procedure verified
- [ ] Monitoring set up
- [ ] Alerts configured
- [ ] Rollback plan tested

### Product Team
- [ ] Feature meets requirements
- [ ] User experience acceptable
- [ ] Performance meets SLA
- [ ] Ready for production

## Post-Deployment Support

### First 24 Hours
- [ ] Monitor execution logs closely
- [ ] Check for any errors or warnings
- [ ] Verify all forms generating correctly
- [ ] Monitor storage usage
- [ ] Check subscription enforcement

### First Week
- [ ] Analyze performance metrics
- [ ] Optimize slow queries if needed
- [ ] Gather user feedback
- [ ] Document any issues
- [ ] Plan improvements

### Ongoing
- [ ] Weekly performance review
- [ ] Monthly storage cleanup
- [ ] Quarterly optimization review
- [ ] Annual architecture review

## Success Criteria

✓ All forms generating successfully
✓ Average execution time < 2 seconds
✓ Zero data isolation issues
✓ Subscription access properly enforced
✓ All execution logs recorded
✓ Storage organized and accessible
✓ No critical errors in logs
✓ Users can preview, generate, and download forms
✓ Inspection packs creating successfully
✓ Multi-tenant data properly isolated
