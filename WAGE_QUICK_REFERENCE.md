# WAGE CALCULATION QUICK REFERENCE

## Commands

### 1. Repair Missing Data
```bash
php artisan compliance:repair-payroll-data 4 1 2026
```
Auto-creates missing attendance and payroll entries.

### 2. Validate Compliance
```bash
php artisan compliance:validate-wages 4 1 2026
```
Checks wage calculations against Tamil Nadu standards.

### 3. Test Generation
```bash
php artisan compliance:test-generation --all
```
Generates all 36 forms with automatic data repair.

---

## Government Formulas

```
Daily Rate = Basic Salary ÷ 26
Basic Wages = Daily Rate × Days Worked
Overtime Wages = (Daily Rate ÷ 8 × 2) × Overtime Hours
Prorated DA = (Full DA ÷ 26) × Days Worked
Prorated HRA = (Full HRA ÷ 26) × Days Worked
```

---

## Legal Rules

1. **Days Worked = 0** → All wage components MUST = 0
2. **Overtime Hours = 0** → Overtime Wages MUST = 0
3. **Days Worked > 0** → Basic Wages MUST > 0
4. **DA/HRA** → MUST be prorated by attendance
5. **Source of Truth** → workforce_attendance table

---

## Data Flow

```
workforce_employee.basic_salary
         ↓
WageCalculationService.calculateDailyRate()
         ↓
workforce_attendance (COUNT WHERE status='present')
         ↓
WageCalculationService.calculateBasicWages()
         ↓
WageCalculationService.prorateAllowance()
         ↓
PayrollValidationGuard.validateBeforeRender()
         ↓
PDF Generation
```

---

## Validation Points

### Service Layer (WageCalculationService)
- Formula correctness
- Rounding to 2 decimals
- Zero handling

### Generator Layer (PayrollBasedFormGenerator)
- Attendance fetching
- Auto-repair on missing data
- Wage recalculation

### Guard Layer (PayrollValidationGuard)
- Pre-render validation
- Zero tolerance enforcement
- Exception throwing

---

## Inspector Test

**Manual Verification**:
1. Open FORM_B PDF
2. Pick Employee Row 1
3. Query: `SELECT * FROM workforce_employee WHERE id = ?`
4. Query: `SELECT COUNT(*) FROM workforce_attendance WHERE employee_id = ? AND status = 'present'`
5. Calculate: `daily_rate = basic_salary / 26`
6. Calculate: `basic_wages = daily_rate × days_worked`
7. Compare with PDF values

**Expected**: 100% match, zero deviation.

---

## Error Messages

### "Wage components cannot exist when days_worked = 0"
**Cause**: Employee has DA/HRA/Basic but zero attendance
**Fix**: Run `compliance:repair-payroll-data`

### "Overtime wages cannot exist when overtime_hours = 0"
**Cause**: Payroll has overtime wages but zero hours
**Fix**: Update payroll entry or set overtime_wages = 0

### "LEGAL VIOLATION: daysWorked=0 but wage components exist"
**Cause**: Pre-render validation caught inconsistency
**Fix**: Run `compliance:repair-payroll-data` and regenerate

---

## Production Checklist

- [ ] Deploy code changes
- [ ] Run `compliance:repair-payroll-data` for all tenants
- [ ] Run `compliance:validate-wages` for sample tenants
- [ ] Regenerate all FORM_B documents
- [ ] Manual spot-check 5 random employees
- [ ] Enable production mode

---

## Support

**Issue**: Days Worked showing 0
**Solution**: Run repair command, attendance data missing

**Issue**: Basic Wages not matching
**Solution**: Check basic_salary in employee table, verify formula

**Issue**: DA/HRA too high
**Solution**: Check if prorated correctly (should be × daysWorked/26)

**Issue**: Validation failing
**Solution**: Check PayrollValidationGuard error message for specific violation
