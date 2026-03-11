# MINIMAL Subscription - Verification Checklist

## 🔍 Pre-Deployment Verification

### Code Review
- [ ] All new files created successfully
- [ ] All modified files updated correctly
- [ ] No syntax errors in PHP files
- [ ] No syntax errors in Blade templates
- [ ] Routes properly defined
- [ ] Middleware correctly applied

### Database
- [ ] Migration file exists: `2026_02_26_000001_create_statutory_manual_data_table.php`
- [ ] Migration syntax is correct
- [ ] Unique constraint on (tenant_id, month, year)
- [ ] Foreign key to tenants table
- [ ] JSON columns properly defined

### Documentation
- [ ] Implementation guide created
- [ ] Quick reference created
- [ ] Change summary created
- [ ] Deployment script created
- [ ] All files properly formatted

---

## 🚀 Deployment Verification

### Step 1: Run Deployment Script
```bash
# Windows
deploy_minimal_feature.bat

# Linux/Mac
php artisan migrate
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Verify:**
- [ ] Migration runs without errors
- [ ] Table `statutory_manual_data` created
- [ ] Caches cleared successfully
- [ ] No error messages

### Step 2: Check Routes
```bash
php artisan route:list | grep manual-data
```

**Expected Output:**
```
GET    /compliance/manual-data/{month}/{year}  compliance.manual-data.show
POST   /compliance/manual-data/{month}/{year}  compliance.manual-data.save
```

**Verify:**
- [ ] Both routes exist
- [ ] Routes point to ManualDataController
- [ ] Route names are correct

### Step 3: Check Database
```sql
-- Check table exists
SHOW TABLES LIKE 'statutory_manual_data';

-- Check structure
DESCRIBE statutory_manual_data;

-- Expected columns:
-- id, tenant_id, month, year, 
-- establishment_details, employer_details, employee_summary,
-- wage_summary, attendance_summary, accident_details, contractor_summary,
-- created_at, updated_at
```

**Verify:**
- [ ] Table exists
- [ ] All columns present
- [ ] JSON columns have correct type
- [ ] Indexes created

---

## 🧪 Functional Testing

### Test 1: MINIMAL User - Data Entry

**Login:** minimal@demo.com

**Steps:**
1. [ ] Navigate to dashboard
2. [ ] See "Enter statutory data manually" message
3. [ ] Create new batch (select section, forms, month/year)
4. [ ] Click "Step 1: Enter Statutory Data"
5. [ ] Data entry form opens in new tab
6. [ ] Fill in establishment details
7. [ ] Fill in employer details
8. [ ] Fill in employee summary
9. [ ] Fill in wage summary
10. [ ] Click "Save Data"
11. [ ] See success message
12. [ ] Return to dashboard

**Expected Results:**
- [ ] Form loads without errors
- [ ] All fields visible and editable
- [ ] Save button works
- [ ] Success notification appears
- [ ] Data persists (refresh page, data still there)

### Test 2: MINIMAL User - Preview

**Steps:**
1. [ ] From dashboard with created batch
2. [ ] Click "Preview [FORM_CODE]" for any form
3. [ ] Preview opens in new tab
4. [ ] Form shows entered data
5. [ ] Data appears in correct fields
6. [ ] No "N/A" or empty critical fields

**Expected Results:**
- [ ] Preview loads without errors
- [ ] Manual data appears in form
- [ ] Format matches expected layout
- [ ] No console errors

### Test 3: MINIMAL User - Generate Forms

**Steps:**
1. [ ] From dashboard with created batch
2. [ ] Click "Step 3: Generate Forms"
3. [ ] Wait for processing
4. [ ] See success message
5. [ ] Click "Download Report"
6. [ ] PDF downloads successfully
7. [ ] Open PDF and verify content

**Expected Results:**
- [ ] Generation completes without errors
- [ ] Status changes to "Completed"
- [ ] Download link appears
- [ ] PDF contains entered data
- [ ] PDF format is correct

### Test 4: FULL User - No Regression

**Login:** full@demo.com

**Steps:**
1. [ ] Navigate to dashboard
2. [ ] See "Upgrade to FULL" message NOT shown
3. [ ] Create new batch
4. [ ] Preview forms (should use database)
5. [ ] Process batch
6. [ ] Download report
7. [ ] Download inspection pack

**Expected Results:**
- [ ] All existing features work
- [ ] No new errors
- [ ] Database-driven flow unchanged
- [ ] Inspection pack available
- [ ] Digital signature available

---

## 🔒 Security Verification

### Tenant Isolation
```sql
-- Test: User from tenant 1 cannot access tenant 2 data
SELECT * FROM statutory_manual_data WHERE tenant_id = 1;
-- Should only return tenant 1 data
```

**Verify:**
- [ ] Manual data filtered by tenant_id
- [ ] No cross-tenant data access
- [ ] Preview respects tenant isolation
- [ ] Generation respects tenant isolation

### Subscription Enforcement
**Test URLs:**
```
# As FULL user, try to access manual data entry
GET /compliance/manual-data/1/2024
# Should redirect with error

# As MINIMAL user, try to access inspection pack
GET /compliance/batch/1/inspection-pack
# Should redirect with error
```

**Verify:**
- [ ] MINIMAL users cannot access FULL features
- [ ] FULL users redirected from manual entry
- [ ] Proper error messages shown
- [ ] No unauthorized access

### CSRF Protection
**Verify:**
- [ ] All POST routes have CSRF token
- [ ] Forms include @csrf directive
- [ ] AJAX requests include X-CSRF-TOKEN header
- [ ] Invalid tokens rejected

---

## 📊 Data Integrity Verification

### Test Data Flow
```
Manual Entry → Database → Adapter → Generator → PDF
```

**Verify:**
1. [ ] Data saved correctly in JSON format
2. [ ] Adapter converts data properly
3. [ ] Generator receives correct format
4. [ ] PDF contains accurate data
5. [ ] No data loss in conversion

### Test Edge Cases
- [ ] Empty optional fields handled
- [ ] Special characters in text fields
- [ ] Large numbers in wage fields
- [ ] Date formats correct
- [ ] Multiple saves update correctly

---

## 🎯 Performance Verification

### Response Times
- [ ] Data entry form loads < 2 seconds
- [ ] Save operation completes < 1 second
- [ ] Preview loads < 3 seconds
- [ ] Form generation < 10 seconds per form
- [ ] Download starts immediately

### Memory Usage
- [ ] No memory leaks during generation
- [ ] Memory usage within limits
- [ ] Large batches complete successfully
- [ ] No timeout errors

---

## 📝 Log Verification

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

**During Testing, Verify:**
- [ ] No PHP errors
- [ ] No SQL errors
- [ ] No undefined variable warnings
- [ ] Successful generation logs present
- [ ] Proper subscription detection logged

### Expected Log Entries
```
[INFO] Form generated successfully
  form_code: FORM_B
  tenant_id: 1
  subscription: MINIMAL
  
[INFO] Manual data saved
  tenant_id: 1
  month: 1
  year: 2024
```

---

## 🔄 Rollback Verification

### If Issues Found
```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Restore old files (if needed)
git checkout HEAD -- [modified files]
```

**Verify:**
- [ ] Rollback completes without errors
- [ ] Table removed
- [ ] System returns to previous state
- [ ] No orphaned data

---

## ✅ Final Sign-Off

### Code Quality
- [ ] No syntax errors
- [ ] No logical errors
- [ ] Proper error handling
- [ ] Clean code structure
- [ ] Well documented

### Functionality
- [ ] MINIMAL subscription works
- [ ] FULL subscription unchanged
- [ ] All features tested
- [ ] Edge cases handled
- [ ] Performance acceptable

### Documentation
- [ ] Implementation guide complete
- [ ] Quick reference available
- [ ] Change summary accurate
- [ ] Deployment script works
- [ ] This checklist complete

### Production Readiness
- [ ] All tests passed
- [ ] No critical issues
- [ ] Logs clean
- [ ] Performance good
- [ ] Security verified

---

## 📋 Sign-Off

**Tested By:** _________________

**Date:** _________________

**Status:** 
- [ ] ✅ APPROVED - Ready for Production
- [ ] ⚠️ CONDITIONAL - Minor issues to fix
- [ ] ❌ REJECTED - Major issues found

**Notes:**
```
[Add any additional notes or observations here]
```

---

## 🚨 Issue Tracking

If issues found, document here:

### Issue 1
- **Severity:** [ ] Critical [ ] High [ ] Medium [ ] Low
- **Description:** 
- **Steps to Reproduce:**
- **Expected:**
- **Actual:**
- **Fix Required:**

### Issue 2
- **Severity:** [ ] Critical [ ] High [ ] Medium [ ] Low
- **Description:**
- **Steps to Reproduce:**
- **Expected:**
- **Actual:**
- **Fix Required:**

---

**End of Checklist**
