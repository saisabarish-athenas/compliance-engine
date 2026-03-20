# 🚀 COMPLIANCE ENGINE - QUICK START GUIDE

## ⚡ 5-Minute Setup

### Step 1: Fresh Database Setup
```bash
# Clear and migrate database
php artisan migrate:fresh

# Seed compliance forms (34 forms)
php artisan db:seed --class=ComplianceFormsMasterSeeder

# Seed demo data (139 records)
php artisan db:seed --class=FreshComplianceSeeder
```

### Step 2: Verify System
```bash
# Run health check
php test_system_health.php
```

**Expected Output:**
```
✅ ALL TESTS PASSED
System is ready for compliance forms
```

### Step 3: Start Application
```bash
# Start Laravel development server
php artisan serve

# Application available at: http://localhost:8000
```

---

## 📊 DEMO DATA CREATED

After seeding, you have:
- **1 Tenant:** Demo Compliance Industries Pvt Ltd
- **1 Branch:** Solar Panel Manufacturing Unit
- **25 Employees:** With realistic payroll data
- **75 Payroll Entries:** 3 months × 25 employees
- **25 Bonus Records:** Annual bonus calculations
- **3 Incident Records:** Safety incidents
- **10 Deployments:** Contract labour deployments
- **34 Forms:** All compliance forms configured

---

## 🔧 COMMON OPERATIONS

### Generate a Single Form
```php
// In tinker or controller
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);

$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2025,
    formCode: 'FORM_B',
    mode: 'preview'  // or 'pdf', 'batch'
);

// Result contains HTML preview or PDF content
```

### Create a Batch
```php
$batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);

$batch = $batchOrchestrator->createBatch(
    tenantId: 1,
    month: 1,
    year: 2025
);

// Batch created with all applicable forms attached
```

### Get Applicable Forms for Month
```php
$frequencyEngine = app(\App\Services\Compliance\FrequencyEngine::class);

$forms = $frequencyEngine->getApplicableForms(month: 1);
// Returns all forms applicable for January (monthly forms)

$forms = $frequencyEngine->getApplicableForms(month: 3);
// Returns monthly + quarterly forms for March
```

---

## 📋 FORM CATEGORIES

### CLRA Forms (10)
- FORM_XII, FORM_XIII, FORM_XIV, FORM_XVI, FORM_XVII
- FORM_XIX, FORM_XX, FORM_XXI, FORM_XXII, FORM_XXIII

### Labour Welfare Forms (4)
- FORM_A, FORM_C, FORM_D, FORM_D_ER

### Social Security Forms (3)
- FORM_11, ESI_FORM_12, EPF_INSPECTION

### Factories Act Forms (11)
- FORM_B, FORM_2, FORM_8, FORM_10, FORM_12
- FORM_17, FORM_18, FORM_25, FORM_26, FORM_26A, HAZARD_REG

### Shops & Establishment Forms (6)
- SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FORM_C
- SHOPS_FORM_VI, SHOPS_UNPAID, SHOPS_FINES

---

## 🔍 TROUBLESHOOTING

### Issue: "No forms applicable for month"
**Solution:** Ensure forms are seeded
```bash
php artisan db:seed --class=ComplianceFormsMasterSeeder
```

### Issue: "Tenant not found"
**Solution:** Ensure demo data is seeded
```bash
php artisan db:seed --class=FreshComplianceSeeder
```

### Issue: "Foreign key constraint violation"
**Solution:** Run fresh migrations and seed in order
```bash
php artisan migrate:fresh
php artisan db:seed --class=ComplianceFormsMasterSeeder
php artisan db:seed --class=FreshComplianceSeeder
```

### Issue: "No attendance data found"
**Solution:** This is expected - attendance is optional for demo
- System will generate forms with available data
- In production, ensure attendance is recorded

---

## 📊 DATABASE QUERIES

### Check Seeded Data
```sql
-- Check tenants
SELECT * FROM tenants;

-- Check employees
SELECT COUNT(*) FROM workforce_employee WHERE tenant_id = 1;

-- Check payroll entries
SELECT COUNT(*) FROM workforce_payroll_entry WHERE tenant_id = 1;

-- Check forms
SELECT COUNT(*) FROM compliance_forms_master WHERE is_active = 1;

-- Check incidents
SELECT * FROM incident_documents WHERE tenant_id = 1;
```

---

## 🎯 NEXT STEPS

1. **Test Form Generation**
   - Generate a preview of FORM_B
   - Generate PDF of FORM_25
   - Create a batch with multiple forms

2. **Verify Multi-Tenant Safety**
   - Create another tenant
   - Verify data isolation
   - Test cross-tenant access prevention

3. **Customize for Your Needs**
   - Update tenant/branch details
   - Add your employees
   - Configure payroll cycles
   - Generate forms for your data

4. **Deploy to Production**
   - Set up production database
   - Configure environment variables
   - Run migrations and seeders
   - Monitor logs and performance

---

## 📞 SUPPORT

### Key Services
- **ComplianceOrchestrator:** Main form generation service
- **BatchOrchestrator:** Batch processing
- **FormApiServiceFactory:** Data fetching for each form
- **FormGeneratorFactory:** Data transformation for each form
- **FrequencyEngine:** Form scheduling logic

### Documentation
- `PROJECT_ANALYSIS_DIAGNOSTIC_REPORT.md` - Complete analysis
- `API_SERVICES_QUICK_REFERENCE.md` - API services guide
- `IMPLEMENTATION_CHECKLIST.md` - Testing guide

---

## ✅ VERIFICATION CHECKLIST

After setup, verify:
- [ ] Database connected
- [ ] Migrations completed
- [ ] Forms seeded (34 forms)
- [ ] Demo data created (139 records)
- [ ] System health check passed
- [ ] Can generate form preview
- [ ] Can generate PDF
- [ ] Multi-tenant isolation working

---

**Status:** ✅ READY FOR USE  
**Last Updated:** 2026-03-11  
**Version:** 1.0
