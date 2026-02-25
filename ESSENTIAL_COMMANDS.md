# 📋 ESSENTIAL COMMANDS REFERENCE

## PRODUCTION VALIDATION COMMANDS

### 1. Complete System Check
```bash
php artisan compliance:production-ready-check
```
**Purpose**: Validates all production requirements  
**Expected**: `🎉 SYSTEM IS PRODUCTION READY`  
**Checks**: Database, Tenant Isolation, Forms, Rules, Subscription, Memory

### 2. Test All Forms
```bash
php artisan compliance:test-generation --all
```
**Purpose**: Generates all 36 forms to verify functionality  
**Expected**: `Success: 36/36 | Failed: 0/36`  
**Time**: ~30 seconds  
**Memory**: ~370MB peak

### 3. Tenant Security Audit
```bash
php artisan compliance:tenant-integrity-audit
```
**Purpose**: Verifies tenant data isolation  
**Expected**: `✅ TENANT INTEGRITY: VERIFIED`  
**Checks**: Cross-tenant leakage, data boundaries

---

## DAILY OPERATIONS

### Process Payroll
```bash
php artisan compliance:repair-payroll-data {tenant_id} {month} {year}
```
**Example**: `php artisan compliance:repair-payroll-data 4 1 2026`  
**Purpose**: Processes payroll for a specific period  
**Required**: Before form generation

### Check Due Dates
```bash
php artisan compliance:check-due
```
**Purpose**: Updates overdue compliance timelines  
**Schedule**: Daily (automated via console.php)  
**Action**: Marks pending forms as overdue

### Generate Demo Data
```bash
php artisan compliance:generate-demo-dataset
```
**Purpose**: Creates test data for demos/training  
**Creates**: Tenants, employees, payroll, contractors

---

## DATABASE OPERATIONS

### Run Migrations
```bash
php artisan migrate --force
```
**Purpose**: Execute pending database migrations  
**Use**: After code updates

### Seed Database
```bash
php artisan db:seed --class=ComplianceFullDemoSeeder
```
**Purpose**: Populate database with demo data  
**Creates**: Complete test dataset

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```
**Purpose**: Clear all cached data  
**Use**: After configuration changes

---

## MONITORING & DEBUGGING

### View Logs
```bash
# Windows
type storage\logs\laravel.log

# Unix/Linux
tail -f storage/logs/laravel.log
```

### Check Database
```bash
php artisan db:show
php artisan db:table tenants
php artisan db:table workforce_employee
```

### List Routes
```bash
php artisan route:list
```

---

## PRODUCTION DEPLOYMENT

### Initial Setup
```bash
# 1. Clone repository
git clone <repository-url>
cd compliance-engine

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --force
php artisan db:seed --class=ComplianceFullDemoSeeder

# 5. Validate system
php artisan compliance:production-ready-check
php artisan compliance:test-generation --all
php artisan compliance:tenant-integrity-audit

# 6. Start server
php artisan serve
```

---

## TROUBLESHOOTING COMMANDS

### Issue: Forms Not Generating
```bash
# Check system status
php artisan compliance:production-ready-check

# Verify tenant subscription
php artisan tinker
>>> DB::table('tenants')->select('id', 'name', 'subscription_type')->get()

# Check payroll data
php artisan tinker
>>> DB::table('workforce_payroll_entry')->where('tenant_id', 4)->count()
```

### Issue: Memory Errors
```bash
# Check current memory limit
php -i | findstr memory_limit

# Increase memory limit (edit php.ini)
memory_limit = 512M

# Test with increased limit
php -d memory_limit=512M artisan compliance:test-generation --all
```

### Issue: Missing Data
```bash
# Check employees
php artisan tinker
>>> DB::table('workforce_employee')->where('tenant_id', 4)->count()

# Check attendance
>>> DB::table('workforce_attendance')->where('tenant_id', 4)->count()

# Repair payroll
php artisan compliance:repair-payroll-data 4 1 2026
```

---

## MAINTENANCE COMMANDS

### Optimize Application
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear Optimization
```bash
php artisan optimize:clear
```

### Storage Link
```bash
php artisan storage:link
```

---

## TESTING COMMANDS

### Run PHPUnit Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test --filter=ComplianceTest
```

---

## SCHEDULED TASKS

### View Scheduled Tasks
```bash
php artisan schedule:list
```

### Run Scheduled Tasks Manually
```bash
php artisan schedule:run
```

### Test Specific Scheduled Task
```bash
php artisan compliance:check-due
```

---

## QUICK VALIDATION SEQUENCE

Run these commands in order to validate production readiness:

```bash
# 1. System check
php artisan compliance:production-ready-check

# 2. Form generation test
php artisan compliance:test-generation --all

# 3. Tenant security audit
php artisan compliance:tenant-integrity-audit

# 4. Clear cache
php artisan cache:clear

# 5. Optimize
php artisan optimize
```

**Expected Result**: All commands should complete successfully with no errors.

---

## EMERGENCY COMMANDS

### Rollback Last Migration
```bash
php artisan migrate:rollback --step=1
```

### Reset Database (CAUTION)
```bash
php artisan migrate:fresh --seed
```

### Force Maintenance Mode
```bash
php artisan down
php artisan up
```

---

## USEFUL TINKER COMMANDS

```bash
php artisan tinker

# Check tenant count
>>> DB::table('tenants')->count()

# Check form configuration
>>> config('compliance_forms.FORM_B')

# Check statutory rules
>>> config('tn_statutory_rules.FORM_2')

# Get tenant details
>>> DB::table('tenants')->where('id', 4)->first()

# Check generation logs
>>> DB::table('compliance_generation_logs')->where('batch_id', 1)->get()
```

---

## COMMAND ALIASES (Optional)

Add to `.bashrc` or `.zshrc`:

```bash
alias comp-check='php artisan compliance:production-ready-check'
alias comp-test='php artisan compliance:test-generation --all'
alias comp-audit='php artisan compliance:tenant-integrity-audit'
alias comp-due='php artisan compliance:check-due'
```

---

**Last Updated**: February 24, 2026  
**System Version**: 2.0 (Production)  
**Status**: ✅ Production Ready
