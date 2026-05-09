# Compliance Engine - Command Reference

## 🎯 Quick Command Guide

### System Health & Validation

#### System Integrity Check
```bash
php artisan compliance:system-check
```
**Purpose:** Comprehensive system validation (6 checks)  
**Checks:** Forms, Database, Config, Routes, Security, Isolation  
**Exit Code:** 0 = Pass, 1 = Fail  
**Duration:** ~5-10 seconds

#### Form Generation Test
```bash
php artisan compliance:test-generation --all
```
**Purpose:** Test all 36 forms generation  
**Output:** Success/Fail count per form  
**Exit Code:** 0 = All pass, 1 = Any fail  
**Duration:** ~2-3 seconds

#### Timeline Due Date Check
```bash
php artisan compliance:check-due
```
**Purpose:** Update overdue compliance timelines  
**Schedule:** Daily (automated)  
**Output:** Count of updated timelines  
**Duration:** <1 second

---

## 📋 System Check Details

### What Gets Validated

| Check | Description | Pass Criteria |
|-------|-------------|---------------|
| **Forms** | All 36 forms generate | 36/36 success |
| **Timeline Table** | compliance_timelines exists | Table + columns present |
| **Attendance Table** | workforce_attendance exists | Table present |
| **Config** | All forms configured | 36/36 with table + date_field |
| **Routes** | Middleware protection | All routes protected |
| **Subscription** | MINIMAL blocked | Exception thrown |
| **Isolation** | Tenant filtering | tenant_id filtering detected |

### Expected Output (All Pass)
```
-----------------------------------------
COMPLIANCE SYSTEM INTEGRITY REPORT
-----------------------------------------

Forms: 36/36 ✅ PASS
Timeline Table: ✅ OK
Attendance Table: ✅ OK
Config Mapping: ✅ OK (36/36 forms)
Route Protection: ✅ OK
Subscription Enforcement: ✅ OK
Tenant Isolation: ✅ OK

-----------------------------------------
OVERALL STATUS: ✅ PASS
-----------------------------------------
```

---

## 🚀 Common Workflows

### Pre-Deployment Check
```bash
# Run full system check
php artisan compliance:system-check

# If pass, deploy
git push origin main
```

### Development Workflow
```bash
# After code changes
php artisan compliance:system-check

# Test specific forms
php artisan compliance:test-generation

# Check timeline status
php artisan compliance:check-due
```

### CI/CD Integration
```yaml
# .github/workflows/test.yml
- name: System Integrity Check
  run: php artisan compliance:system-check
  
- name: Form Generation Test
  run: php artisan compliance:test-generation --all
```

### Production Monitoring
```bash
# Daily cron job
0 2 * * * cd /var/www/app && php artisan compliance:system-check
```

---

## 🔧 Troubleshooting

### Issue: "Forms: SKIP (No test data)"
**Solution:**
```bash
php artisan db:seed
```

### Issue: "Timeline Table: FAIL"
**Solution:**
```bash
php artisan migrate
```

### Issue: "Config Mapping: FAIL"
**Solution:**
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: "Route Protection: FAIL"
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
```

---

## 📊 Command Comparison

| Command | Purpose | Duration | Safety | Automation |
|---------|---------|----------|--------|------------|
| `system-check` | Full validation | 5-10s | ✅ Safe | Manual |
| `test-generation` | Form testing | 2-3s | ✅ Safe | Manual |
| `check-due` | Timeline update | <1s | ✅ Safe | Daily |

---

## 🎓 Best Practices

### 1. Run Before Every Deployment
```bash
php artisan compliance:system-check && deploy.sh
```

### 2. Add to Git Pre-Commit Hook
```bash
#!/bin/bash
php artisan compliance:system-check || exit 1
```

### 3. Schedule Weekly Health Checks
```php
// routes/console.php
Schedule::command('compliance:system-check')
    ->weekly()
    ->emailOutputOnFailure('admin@example.com');
```

### 4. Monitor in Production
```bash
# Cron: Every day at 2 AM
0 2 * * * cd /app && php artisan compliance:system-check >> /var/log/compliance-check.log 2>&1
```

---

## 📈 Success Metrics

### Healthy System
- ✅ All 6 checks pass
- ✅ 36/36 forms generate
- ✅ Exit code 0
- ✅ No errors in output

### Unhealthy System
- ❌ Any check fails
- ❌ Forms < 36/36
- ❌ Exit code 1
- ❌ Error messages present

---

## 🔗 Related Documentation

- **COMPREHENSIVE_AUDIT_REPORT.md** - Full system audit
- **SYSTEM_CHECK_COMMAND.md** - Detailed command docs
- **COMPLIANCE_TIMELINE_ENGINE_IMPLEMENTATION.md** - Timeline features
- **SUBSCRIPTION_ENFORCEMENT_SECURITY.md** - Security details

---

## 📞 Quick Help

```bash
# List all compliance commands
php artisan list compliance

# Get command help
php artisan help compliance:system-check

# Run with verbose output
php artisan compliance:system-check -v

# Check Laravel version
php artisan --version

# Check database connection
php artisan db:show
```

---

## ✅ Pre-Flight Checklist

Before running system check:
- [ ] Database connection working
- [ ] Migrations run
- [ ] Config cached (optional)
- [ ] Routes cached (optional)
- [ ] Storage writable

After system check passes:
- [ ] All 6 checks green
- [ ] Exit code 0
- [ ] No error messages
- [ ] Ready for deployment

---

**Last Updated:** 2024-01-XX  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
