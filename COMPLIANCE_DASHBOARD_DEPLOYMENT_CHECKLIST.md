# Compliance Dashboard - Deployment Checklist

## Pre-Deployment

### Code Review
- [ ] Review `ComplianceDashboardController.php`
- [ ] Review `manual_dashboard.blade.php`
- [ ] Review route additions
- [ ] Check for SQL injection vulnerabilities
- [ ] Check for XSS vulnerabilities
- [ ] Verify CSRF protection

### Testing
- [ ] Test dashboard access
- [ ] Test batch loading
- [ ] Test batch selector
- [ ] Test statistics calculation
- [ ] Test progress bar
- [ ] Test compliance table
- [ ] Test upload functionality
- [ ] Test skip functionality
- [ ] Test multi-tenant isolation
- [ ] Test on mobile devices
- [ ] Test on different browsers

### Database
- [ ] Verify `compliance_manual_master` table exists
- [ ] Verify `compliance_manual_batch_items` table exists
- [ ] Verify `compliance_execution_batches` table exists
- [ ] Check indexes on `tenant_id`, `branch_id`, `batch_id`
- [ ] Verify foreign key relationships
- [ ] Test with sample data

### File System
- [ ] Verify `storage/app/public` directory exists
- [ ] Check write permissions: `chmod 755 storage/app/public`
- [ ] Verify `resources/views/compliance` directory exists
- [ ] Check symlink: `php artisan storage:link`

### Configuration
- [ ] Verify `FILESYSTEM_DISK=public` in `.env`
- [ ] Check file upload max size in `php.ini`
- [ ] Verify CSRF middleware is enabled
- [ ] Check authentication middleware

## Deployment

### 1. Copy Files
```bash
# Copy controller
cp app/Http/Controllers/ComplianceDashboardController.php \
   /production/app/Http/Controllers/

# Copy view
cp resources/views/compliance/manual_dashboard.blade.php \
   /production/resources/views/compliance/

# Copy documentation
cp COMPLIANCE_DASHBOARD_IMPLEMENTATION.md /production/docs/
cp COMPLIANCE_DASHBOARD_QUICK_REFERENCE.md /production/docs/
```

### 2. Update Routes
```bash
# Edit routes/compliance.php
# Add the following routes:
# GET  /compliance/manual-dashboard
# GET  /compliance/manual-batches
# GET  /compliance/manual-batch/{id}/summary
# GET  /compliance/manual-batch/{id}
# POST /compliance/manual-item/upload
# POST /compliance/manual-item/skip
```

### 3. Clear Cache
```bash
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

### 4. Verify Routes
```bash
php artisan route:list | grep manual-dashboard
php artisan route:list | grep manual-batch
php artisan route:list | grep manual-item
```

### 5. Test Endpoints
```bash
# Test dashboard view
curl http://your-app.com/compliance/manual-dashboard

# Test batch summary
curl http://your-app.com/compliance/manual-batch/1/summary

# Test batch items
curl http://your-app.com/compliance/manual-batch/1

# Test all batches
curl http://your-app.com/compliance/manual-batches
```

## Post-Deployment

### Verification
- [ ] Dashboard loads without errors
- [ ] Batch selector populates
- [ ] Statistics display correctly
- [ ] Progress bar shows correct percentage
- [ ] Compliance table loads items
- [ ] Upload modal opens
- [ ] Skip button works
- [ ] Multi-tenant isolation verified
- [ ] No console errors
- [ ] No server errors in logs

### Performance
- [ ] Dashboard loads in < 2 seconds
- [ ] API responses in < 500ms
- [ ] No N+1 queries
- [ ] Database indexes used
- [ ] Memory usage acceptable

### Security
- [ ] CSRF token present
- [ ] Tenant validation working
- [ ] Branch validation working
- [ ] File upload validation working
- [ ] No unauthorized access possible
- [ ] No SQL injection possible
- [ ] No XSS possible

### Monitoring
- [ ] Check error logs: `tail -f storage/logs/laravel.log`
- [ ] Monitor database queries
- [ ] Track API response times
- [ ] Monitor file uploads
- [ ] Check storage usage

## Rollback Plan

### If Issues Occur
```bash
# 1. Revert files
git checkout app/Http/Controllers/ComplianceDashboardController.php
git checkout resources/views/compliance/manual_dashboard.blade.php
git checkout routes/compliance.php

# 2. Clear cache
php artisan cache:clear
php artisan route:clear

# 3. Verify rollback
php artisan route:list | grep manual-dashboard
```

## Troubleshooting

### Dashboard Not Loading
```bash
# Check routes
php artisan route:list | grep manual-dashboard

# Check view exists
ls -la resources/views/compliance/manual_dashboard.blade.php

# Check controller exists
ls -la app/Http/Controllers/ComplianceDashboardController.php

# Check logs
tail -f storage/logs/laravel.log
```

### Batch Not Showing
```bash
# Check database
php artisan tinker
>>> ComplianceExecutionBatch::count()
>>> ManualComplianceBatchItem::count()

# Check tenant_id
>>> auth()->user()->tenant_id

# Check branch_id
>>> auth()->user()->branch_id
```

### Upload Failing
```bash
# Check storage permissions
ls -la storage/app/public

# Check file size
php -r "echo ini_get('upload_max_filesize');"

# Check storage link
ls -la public/storage

# Create if missing
php artisan storage:link
```

### AJAX Errors
```bash
# Check browser console (F12)
# Check network tab (F12 → Network)
# Check CSRF token
>>> csrf_token()

# Check API response
curl -v http://your-app.com/compliance/manual-batch/1/summary
```

## Performance Optimization

### Enable Query Caching
```php
// In ComplianceDashboardController
$summary = Cache::remember(
    "batch_summary_{$batchId}",
    3600,
    fn() => ManualComplianceBatchItem::where(...)->first()
);
```

### Add Database Indexes
```sql
CREATE INDEX idx_batch_tenant_branch 
ON compliance_manual_batch_items(batch_id, tenant_id, branch_id);

CREATE INDEX idx_batch_status 
ON compliance_manual_batch_items(batch_id, status);
```

### Optimize Queries
```php
// Use select to limit columns
->select(['id', 'status', 'document_path'])

// Use eager loading
->with('compliance')

// Use pagination
->paginate(50)
```

## Security Hardening

### CSRF Protection
```blade
<!-- In form -->
@csrf

<!-- In AJAX -->
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### Authorization
```php
// In controller
private function authorizeForTenant(int $tenantId): void
{
    if (auth()->user()->tenant_id !== $tenantId) {
        abort(403, 'Unauthorized access to this tenant');
    }
}
```

### File Upload Security
```php
// Validate file type
'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'

// Store outside web root
$path = $request->file('file')->store('compliance_documents', 'public');

// Serve through controller
return Storage::download($path);
```

## Monitoring & Alerts

### Set Up Alerts For
- [ ] High error rate (> 1%)
- [ ] Slow API responses (> 1s)
- [ ] Failed uploads
- [ ] Unauthorized access attempts
- [ ] Database connection errors
- [ ] Storage space issues

### Monitor Metrics
- [ ] API response time
- [ ] Error rate
- [ ] Upload success rate
- [ ] Database query time
- [ ] Storage usage
- [ ] User activity

## Documentation

### Update Documentation
- [ ] Add dashboard to user guide
- [ ] Add API documentation
- [ ] Add troubleshooting guide
- [ ] Add FAQ
- [ ] Add screenshots
- [ ] Add video tutorial

### Create User Guide
- [ ] How to access dashboard
- [ ] How to view batch progress
- [ ] How to upload documents
- [ ] How to skip compliances
- [ ] How to view compliance details

## Sign-Off

### Development
- [ ] Code review completed
- [ ] Unit tests passed
- [ ] Integration tests passed
- [ ] Performance tests passed

### QA
- [ ] Functional testing completed
- [ ] Security testing completed
- [ ] Performance testing completed
- [ ] Compatibility testing completed

### DevOps
- [ ] Deployment checklist completed
- [ ] Monitoring set up
- [ ] Alerts configured
- [ ] Rollback plan ready

### Product
- [ ] Feature meets requirements
- [ ] User experience acceptable
- [ ] Documentation complete
- [ ] Ready for production

## Final Checklist

- [ ] All files deployed
- [ ] Routes updated
- [ ] Cache cleared
- [ ] Tests passed
- [ ] Monitoring active
- [ ] Documentation updated
- [ ] Team notified
- [ ] Ready for production

---

**Deployment Date**: _______________
**Deployed By**: _______________
**Approved By**: _______________
**Status**: ✅ READY FOR PRODUCTION
