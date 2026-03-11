# DEMO DATA SYSTEM - QUICK REFERENCE

## 🎯 ENABLE DEMO MODE

### Method 1: Environment Variable (Recommended)
```bash
# Edit .env file
DEMO_MODE=true

# Clear config cache
php artisan config:clear
```

### Method 2: Runtime Override
```php
config(['app.demo_mode' => true]);
```

---

## 📊 DEMO DATA SUMMARY

| Form Type | Records | Details |
|-----------|---------|---------|
| CLRA Forms | 30 employees | Full wage/deduction/attendance data |
| Incident Forms | 5 incidents | Varied types with descriptions |
| Employee Forms | 40 employees | Complete worker details |
| Attendance Forms | 780 records | 30 employees × 26 days |

---

## ✅ VERIFICATION COMMANDS

### Check Demo Mode Status
```bash
php artisan tinker --execute="echo config('app.demo_mode') ? 'ENABLED' : 'DISABLED';"
```

### Test CLRA Data Generation
```bash
php artisan tinker --execute="
\$data = \App\Services\Compliance\DemoDataProvider::for('FORM_XVI', 1, 1, 1, 2024);
echo 'Records: ' . \$data['records']->count();
"
```

### Test Incident Data Generation
```bash
php artisan tinker --execute="
\$data = \App\Services\Compliance\DemoDataProvider::for('FORM_8', 1, 1, 1, 2024);
echo 'Records: ' . \$data['records']->count();
"
```

### Test Employee Data Generation
```bash
php artisan tinker --execute="
\$data = \App\Services\Compliance\DemoDataProvider::for('FORM_12', 1, 1, 1, 2024);
echo 'Records: ' . \$data['records']->count();
"
```

---

## 🎨 FORM PREVIEW TESTING

### Test All Forms
```bash
# 1. Enable demo mode
DEMO_MODE=true

# 2. Navigate to dashboard
http://localhost:8000/compliance/dashboard

# 3. Create batch with forms:
- FORM_XVI (CLRA Wages)
- FORM_XVII (CLRA Deductions)
- FORM_XIX (CLRA Muster)
- FORM_XXI (CLRA Fines)
- FORM_8 (Accidents)
- FORM_11 (Dangerous Occurrences)
- FORM_12 (Adult Workers)
- FORM_17 (Young Persons)
- FORM_2 (Leave Register)
- FORM_18 (Child Workers)

# 4. Click "Preview" on each form
# Expected: Fully populated with 30-40 records
```

---

## 🔄 TOGGLE DEMO MODE

### Enable
```bash
# .env
DEMO_MODE=true

# Clear cache
php artisan config:clear
```

### Disable
```bash
# .env
DEMO_MODE=false

# Or remove line entirely
# Clear cache
php artisan config:clear
```

---

## 📋 SAMPLE DATA STRUCTURE

### CLRA Employee Record
```php
[
    'employee_code' => 'EMP0001',
    'employee_name' => 'Rajesh Kumar',
    'designation' => 'Operator',
    'contractor_name' => 'ABC Contractors Pvt Ltd',
    'wage_rate' => 650,
    'basic_earned' => 18500,
    'da_earned' => 3700,
    'hra_earned' => 2200,
    'overtime_hours' => 12,
    'overtime_wages' => 1800,
    'gross_salary' => 26200,
    'pf_employee' => 2400,
    'esi_employee' => 240,
    'advances' => 1000,
    'fines' => 200,
    'total_deductions' => 3840,
    'net_salary' => 22360,
    'total_days_worked' => 24,
]
```

### Incident Record
```php
[
    'employee_code' => 'EMP0001',
    'employee_name' => 'Rajesh Kumar',
    'designation' => 'Operator',
    'incident_type' => 'Minor Injury',
    'incident_date' => '2024-01-15',
    'location' => 'Production Floor 3',
    'description' => 'Incident occurred during routine operations. Immediate action taken.',
]
```

### Employee Record
```php
[
    'employee_code' => 'EMP0001',
    'name' => 'Rajesh Kumar',
    'designation' => 'Operator',
    'date_of_joining' => '2020-03-15',
    'pf_number' => 'PF456789',
    'esi_number' => 'ESI789456',
    'department' => 'Production',
    'basic_salary' => 22000,
]
```

---

## 🚨 TROUBLESHOOTING

### Issue: Forms Still Show NIL

**Solution:**
```bash
# 1. Check demo mode
php artisan tinker --execute="var_dump(config('app.demo_mode'));"

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Restart server
php artisan serve
```

### Issue: Demo Data Not Realistic

**Solution:**
Edit `app/Services/Compliance/DemoDataProvider.php` and adjust:
- Employee names in `randomName()`
- Designations in `randomDesignation()`
- Wage ranges in `clraRecords()`
- Incident types in `incidentRecords()`

### Issue: Too Many/Few Records

**Solution:**
Edit `app/Services/Compliance/DemoDataProvider.php`:
```php
// Change loop count
for ($i = 1; $i <= 30; $i++) { // Change 30 to desired count
```

---

## 🔒 PRODUCTION SAFETY

### Before Deploying to Production

```bash
# 1. Disable demo mode
DEMO_MODE=false

# 2. Verify disabled
php artisan tinker --execute="
if (config('app.demo_mode')) {
    echo 'WARNING: Demo mode is ENABLED';
} else {
    echo 'OK: Demo mode is DISABLED';
}
"

# 3. Clear caches
php artisan config:clear
php artisan cache:clear

# 4. Deploy
```

### Production .env Should Have
```env
# Demo mode should be false or absent
DEMO_MODE=false

# Or simply don't include the line
# (default is false)
```

---

## 📁 FILES MODIFIED

1. **app/Services/Compliance/DemoDataProvider.php** (NEW)
   - Demo data generation service

2. **app/Services/Compliance/FormGenerator/FormDataAggregator.php**
   - Added 3-line fallback check

3. **config/app.php**
   - Added demo_mode config option

4. **.env**
   - Added DEMO_MODE variable

---

## 🎯 QUICK TESTS

### Test 1: Demo Mode Enabled
```bash
DEMO_MODE=true
php artisan config:clear
# Preview any form → Should show 30-40 records
```

### Test 2: Demo Mode Disabled
```bash
DEMO_MODE=false
php artisan config:clear
# Preview any form → Should show actual data or NIL
```

### Test 3: Data Quality
```bash
php artisan tinker --execute="
\$data = \App\Services\Compliance\DemoDataProvider::for('FORM_XVI', 1, 1, 1, 2024);
\$first = \$data['records']->first();
echo 'Employee: ' . \$first->employee_name . PHP_EOL;
echo 'Code: ' . \$first->employee_code . PHP_EOL;
echo 'Salary: ' . \$first->gross_salary . PHP_EOL;
"
```

---

## 📞 SUPPORT

### Check Implementation
```bash
# Verify files exist
ls app/Services/Compliance/DemoDataProvider.php
ls app/Services/Compliance/FormGenerator/FormDataAggregator.php

# Check config
php artisan config:show app.demo_mode
```

### Debug Mode
```bash
# Enable debug logging
APP_DEBUG=true

# Check logs
tail -f storage/logs/laravel.log
```

---

## ✅ SUCCESS CRITERIA

When demo mode is working correctly:

- ✅ All forms show 30-40 records
- ✅ No "NIL" messages appear
- ✅ No "N/A" values in fields
- ✅ All totals calculated
- ✅ Employee codes formatted (EMP0001)
- ✅ Realistic names and amounts
- ✅ Preview loads without errors
- ✅ Signature blocks intact

---

**Quick Reference Version:** 1.0
**Last Updated:** 2024
**Status:** PRODUCTION READY
