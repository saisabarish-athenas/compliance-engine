# SQLite to MySQL Migration Checklist

## Pre-Migration Phase

### Environment Setup
- [ ] MySQL 8.0+ installed on target server
- [ ] MySQL service running and accessible
- [ ] PDO_MySQL PHP extension enabled
  ```bash
  php -m | grep -i pdo_mysql
  ```
- [ ] Database user created with proper privileges
  ```sql
  CREATE USER 'compliance_user'@'localhost' IDENTIFIED BY 'password';
  GRANT ALL PRIVILEGES ON compliance_engine.* TO 'compliance_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### Backup & Safety
- [ ] SQLite database backed up
  ```bash
  cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d_%H%M%S)
  ```
- [ ] Current `.env` backed up
  ```bash
  cp .env .env.backup.sqlite
  ```
- [ ] Git repository clean (no uncommitted changes)
  ```bash
  git status
  ```

### Configuration Verification
- [ ] `.env` updated with MySQL credentials
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=compliance_engine
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- [ ] `config/database.php` verified
  - [ ] MySQL connection configured
  - [ ] Charset: utf8mb4
  - [ ] Collation: utf8mb4_unicode_ci
  - [ ] Strict mode: true
  - [ ] Foreign key constraints: enabled

---

## Migration Phase

### Step 1: Create MySQL Database
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE compliance_engine 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

# Verify
SHOW DATABASES LIKE 'compliance_engine';
```

**Checklist:**
- [ ] Database created
- [ ] Charset verified: utf8mb4
- [ ] Collation verified: utf8mb4_unicode_ci

### Step 2: Clear Laravel Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Checklist:**
- [ ] All caches cleared
- [ ] No stale configuration

### Step 3: Run Migrations
```bash
# Fresh migration (WARNING: This will drop all tables)
php artisan migrate:fresh

# Or if you want to keep existing migrations:
php artisan migrate
```

**Checklist:**
- [ ] All 75 migrations completed successfully
- [ ] No migration errors
- [ ] All tables created
- [ ] All indexes created
- [ ] All foreign keys created

### Step 4: Verify Schema
```bash
# Check tables
php artisan tinker
>>> Schema::getTables()

# Check specific table
>>> Schema::getColumns('workforce_employee')
```

**Checklist:**
- [ ] All 18 core tables exist
- [ ] All columns present
- [ ] All indexes present
- [ ] All foreign keys present

### Step 5: Generate Demo Dataset
```bash
php artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1
```

**Checklist:**
- [ ] Demo data generated successfully
- [ ] Fines records created
- [ ] Advances records created
- [ ] No errors in output

### Step 6: Verify System Health
```bash
php artisan compliance:verify-mysql-migration
```

**Checklist:**
- [ ] Database connection successful
- [ ] Database engine verified
- [ ] Charset and collation verified
- [ ] All core tables exist
- [ ] Foreign keys verified
- [ ] Indexes verified
- [ ] Multi-tenant safety verified
- [ ] Data integrity verified
- [ ] API services verified
- [ ] Performance check passed

---

## Post-Migration Verification

### Step 1: Test Form Generation
```bash
php artisan compliance:test-generation --tenant_id=1 --branch_id=1
```

**Checklist:**
- [ ] All 34 forms generate successfully
- [ ] No errors in generation
- [ ] All forms have data
- [ ] Execution time acceptable

### Step 2: Test System Commands
```bash
# System check
php artisan compliance:system-check

# Trace form data
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1

# Audit compliance
php artisan compliance:audit
```

**Checklist:**
- [ ] All commands execute successfully
- [ ] No errors in output
- [ ] All forms traced correctly
- [ ] Audit passes

### Step 3: Test Multi-Tenant Isolation
```bash
php artisan tinker

# Test tenant 1
>>> $service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
>>> $data = $service->fetch(1, 1, 1, 2024);
>>> $data['meta']['tenant_id'] === 1
=> true

# Test tenant 2 (if exists)
>>> $data = $service->fetch(2, 1, 1, 2024);
>>> $data['meta']['tenant_id'] === 2
=> true
```

**Checklist:**
- [ ] Tenant 1 data isolated
- [ ] Tenant 2 data isolated (if applicable)
- [ ] No cross-tenant data leakage
- [ ] Branch filtering works

### Step 4: Test PDF Generation
```bash
php artisan tinker

>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FormB', 'pdf');
>>> $result['status']
=> "success"
>>> strlen($result['result']['content']) > 0
=> true
```

**Checklist:**
- [ ] PDF generation successful
- [ ] PDF content not empty
- [ ] PDF file valid
- [ ] All forms generate PDFs

### Step 5: Test Batch Processing
```bash
php artisan tinker

>>> $batch = \App\Models\ComplianceExecutionBatch::create([
    'tenant_id' => 1,
    'batch_name' => 'Test Batch',
    'period_month' => 1,
    'period_year' => 2024,
    'status' => 'pending'
]);

>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FormB', 'batch', $batch->id);
>>> $result['status']
=> "success"
```

**Checklist:**
- [ ] Batch creation successful
- [ ] Batch processing successful
- [ ] PDF stored correctly
- [ ] Batch status updated

### Step 6: Performance Testing
```bash
# Test query performance
php artisan tinker

>>> $start = microtime(true);
>>> DB::table('workforce_employee')->where('tenant_id', 1)->count();
>>> $duration = (microtime(true) - $start) * 1000;
>>> echo "Duration: {$duration}ms";
```

**Checklist:**
- [ ] Query performance acceptable (< 100ms)
- [ ] Index usage verified
- [ ] No N+1 queries
- [ ] Batch operations fast

### Step 7: Error Log Check
```bash
# Check for errors
tail -f storage/logs/laravel.log

# Or in tinker
>>> \Illuminate\Support\Facades\Log::channel('single')->getHandlers()
```

**Checklist:**
- [ ] No critical errors
- [ ] No database errors
- [ ] No connection errors
- [ ] All warnings reviewed

---

## Rollback Plan (If Issues Occur)

### Step 1: Stop Application
```bash
# Stop web server
sudo systemctl stop nginx
# or
sudo systemctl stop apache2
```

### Step 2: Revert Configuration
```bash
# Restore SQLite configuration
cp .env.backup.sqlite .env

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Step 3: Restore Database
```bash
# Restore SQLite backup
cp database/database.sqlite.backup database/database.sqlite

# Verify
php artisan tinker
>>> DB::connection('sqlite')->getPdo()
```

### Step 4: Restart Application
```bash
# Start web server
sudo systemctl start nginx
# or
sudo systemctl start apache2

# Verify
curl http://localhost
```

**Checklist:**
- [ ] Application stopped
- [ ] Configuration reverted
- [ ] Database restored
- [ ] Application restarted
- [ ] System functional

---

## Final Verification

### Production Readiness Checklist
- [ ] All migrations completed
- [ ] All tables created
- [ ] All indexes created
- [ ] All foreign keys created
- [ ] Demo data generated
- [ ] System health verified
- [ ] All forms generate correctly
- [ ] All PDFs generate correctly
- [ ] Batch processing works
- [ ] Multi-tenant isolation verified
- [ ] Performance acceptable
- [ ] No error logs
- [ ] All compliance commands work
- [ ] Rollback plan tested
- [ ] Team trained on new system

### Sign-Off
- [ ] DevOps Lead: _________________ Date: _______
- [ ] QA Lead: _________________ Date: _______
- [ ] Project Manager: _________________ Date: _______

---

## Troubleshooting

### Issue: "SQLSTATE[HY000]: General error: 1030 Got error 28 from storage engine"
**Solution**: Check disk space
```bash
df -h
```

### Issue: "SQLSTATE[HY000]: General error: 1040 Too many connections"
**Solution**: Increase max_connections in MySQL
```sql
SET GLOBAL max_connections = 1000;
```

### Issue: "SQLSTATE[42000]: Syntax error or access violation"
**Solution**: Check user privileges
```sql
SHOW GRANTS FOR 'compliance_user'@'localhost';
```

### Issue: "SQLSTATE[HY000]: General error: 2006 MySQL server has gone away"
**Solution**: Check MySQL service
```bash
sudo systemctl status mysql
sudo systemctl restart mysql
```

### Issue: "Charset mismatch"
**Solution**: Alter database charset
```sql
ALTER DATABASE compliance_engine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Support

For issues or questions:
1. Check error logs: `storage/logs/laravel.log`
2. Run verification: `php artisan compliance:verify-mysql-migration`
3. Check MySQL logs: `/var/log/mysql/error.log`
4. Contact DevOps team

---

**Migration Status**: ✅ READY
**Last Updated**: 2024
**Compatibility**: MySQL 8.0+
