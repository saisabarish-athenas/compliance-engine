# FORM COVERAGE - EXECUTION CHECKLIST

## Complete Test Sequence

### Step 1: Generate Period-Aligned Dataset
```bash
php artisan compliance:generate-demo-dataset 4 4 1 2026 --force-coverage
```

**Expected Output**:
```
✓ Created 40 employees
✓ Created attendance records
✓ Created 5 contractors
✓ Created contract labour deployments
✓ Created bonus records
✓ Created accident records
✓ Created inspection records
✓ Created leave records
✓ Created advances and fines
✓ Created CLRA returns

Processing payroll...

✅ Demo dataset generated successfully

Summary:
  Employees: 40
  Payroll Processed: 40
  Total Days Worked: 1,040
  Total Gross Wages: ₹1,456,789.50
  Total Net Wages: ₹1,278,456.30
  Contractors: 5
```

### Step 2: Validate Form Coverage
```bash
php artisan compliance:validate-form-coverage 4 4 1 2026
```

**Expected Output**:
```
✅ FORM_B: 40 rows
✅ FORM_10: 40 rows
✅ FORM_25: 40 rows
✅ FORM_XVI: 15 rows
✅ FORM_D: 15 rows
✅ FORM_1: 3 rows
✅ FORM_XIII: 5 rows
... (all 36 forms)

Summary:
  Populated Forms: 36/36
  NIL Forms: 0/36
```

**If NIL forms exist**:
- Check date field alignment
- Verify records created for period
- Re-run with `--force-coverage`

### Step 3: Validate Wages
```bash
php artisan compliance:validate-wages 4 1 2026 --full
```

**Expected Output**:
```
Validation Complete:
  Compliant: 40
  Violations: 0
```

### Step 4: Production Ready Check
```bash
php artisan compliance:production-ready-check
```

**Expected Output**:
```
[1/7] Schema Integrity: ✅ PASS
[2/7] Statutory Settings: ✅ PASS
[3/7] Generator Coverage: ✅ PASS (36/36)
[4/7] Config Mapping: ✅ PASS
[5/7] Tenant Isolation: ✅ PASS
[6/7] Memory Threshold: ✅ PASS
[7/7] Required Indexes: ✅ PASS

SYSTEM STATUS: PRODUCTION READY ✅
```

### Step 5: Generate All Forms
```bash
php artisan compliance:test-generation --all
```

**Expected Output**:
```
✅ FORM_B: 45,678 bytes | 0.55s | 12MB
✅ FORM_10: 38,234 bytes | 0.48s | 10MB
✅ FORM_25: 42,156 bytes | 0.52s | 11MB
... (all 36 forms)

Success: 36/36 | Failed: 0/36
Total Time: 19.65s | Peak Memory: 145MB
```

## Validation Checklist

### Data Integrity
- [ ] All employees have basic_salary
- [ ] All employees have attendance records
- [ ] All payroll entries computed from attendance
- [ ] All bonus records have payment_date in selected month
- [ ] All accident records have incident_date in selected month
- [ ] All inspection records have inspection_date in selected month
- [ ] All CLRA returns have period matching selected month

### Period Alignment
- [ ] Payroll cycle matches month/year
- [ ] Bonus payment_date within month
- [ ] Accident incident_date within month
- [ ] Inspection inspection_date within month
- [ ] CLRA period_from/period_to matches month
- [ ] Contractor deployments overlap month

### Form Coverage
- [ ] All 36 forms return data
- [ ] No NIL forms (except legally required)
- [ ] Each form has 3-5+ rows
- [ ] No empty columns
- [ ] No null critical values

### Tenant Isolation
- [ ] All records have correct tenant_id
- [ ] All records have correct branch_id
- [ ] No cross-tenant data leakage
- [ ] Filtering works correctly

## Troubleshooting Guide

### Issue: NIL Forms Persist

**Diagnosis**:
```bash
php artisan compliance:validate-form-coverage 4 4 1 2026
```

**Solutions**:
1. Check date field alignment in FormDataAggregator
2. Verify records exist: `SELECT * FROM [table] WHERE tenant_id = 4`
3. Re-generate with `--force-coverage` flag
4. Check form configuration in `config/compliance_forms.php`

### Issue: Period Mismatch

**Diagnosis**:
```sql
-- Check bonus records
SELECT payment_date FROM bonus_records WHERE tenant_id = 4;

-- Check accident records
SELECT incident_date FROM incident_documents WHERE tenant_id = 4;

-- Check inspection records
SELECT inspection_date FROM inspection_documents WHERE tenant_id = 4;
```

**Solutions**:
1. Verify demo generator uses correct month/year
2. Check Carbon date creation
3. Ensure date fields populated correctly

### Issue: Form Generation Fails

**Diagnosis**:
```bash
php artisan compliance:production-ready-check
```

**Solutions**:
1. Check statutory settings configured
2. Verify payroll processed
3. Check attendance exists
4. Run schema repair if needed

### Issue: Wage Violations

**Diagnosis**:
```bash
php artisan compliance:validate-wages 4 1 2026 --full
```

**Solutions**:
1. Re-process payroll: `php artisan compliance:process-payroll 4 4 1 2026`
2. Check attendance records exist
3. Verify WageCalculationService formulas

## Success Criteria

### ✅ All Checks Pass

- [x] Demo dataset generated
- [x] Form coverage: 36/36
- [x] Wage violations: 0
- [x] Production ready: PASS
- [x] Form generation: 36/36
- [x] Period alignment: Correct
- [x] Tenant isolation: Working
- [x] No NIL forms
- [x] No empty columns
- [x] No null values

### ✅ Performance Metrics

- Generation time: <2s
- Payroll processing: <1s
- Form generation: <20s total
- Memory usage: <150MB peak
- Data volume: 1,400+ records

### ✅ Inspector Ready

- 100% reproducible calculations
- All wages from attendance
- All dates within period
- All records tenant-isolated
- All forms populated

---

## FINAL CONFIRMATION

Run complete sequence:

```bash
php artisan compliance:generate-demo-dataset 4 4 1 2026 --force-coverage && \
php artisan compliance:validate-form-coverage 4 4 1 2026 && \
php artisan compliance:validate-wages 4 1 2026 --full && \
php artisan compliance:production-ready-check && \
php artisan compliance:test-generation --all
```

**Expected**: ALL PASS ✅

**Status**: ZERO NIL FORMS ✅
