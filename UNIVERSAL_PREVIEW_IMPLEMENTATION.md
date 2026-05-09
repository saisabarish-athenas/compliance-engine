# UNIVERSAL COMPLIANCE FORM PREVIEW SYSTEM - IMPLEMENTATION GUIDE

## Overview

A universal preview controller that automatically works for all 38 registered statutory forms without requiring separate controllers. The system intelligently detects form templates, fetches data from the database, and respects subscription levels.

---

## ARCHITECTURE FLOW

```
Database (Payroll, Attendance, Employees, etc.)
    ↓
Repositories (EmployeeRepository, PayrollRepository, etc.)
    ↓
Builders (WageRegisterBuilder, OvertimeRegisterBuilder, etc.)
    ↓
ComplianceDataService (buildFormData, normalizeData)
    ↓
CompliancePreviewController (universal preview)
    ↓
Blade Templates (compliance.forms.form_b, form_10, etc.)
```

---

## COMPONENTS IMPLEMENTED

### 1. CompliancePreviewController
**Location:** `app/Http/Controllers/Compliance/CompliancePreviewController.php`

**Responsibilities:**
- Accept form code as parameter
- Resolve tenant, branch, month, year from request or batch
- Check subscription level (FULL vs MINIMAL)
- Call ComplianceDataService to build form data
- Detect and validate blade template
- Pass normalized data to view

**Key Features:**
- Automatic blade template detection
- Subscription-aware data fetching
- Batch context support
- Comprehensive error handling
- Debug logging

**Usage:**
```
GET /compliance/preview/{formCode}?batch_id=1&month=1&year=2024
GET /compliance/preview/FORM_B
GET /compliance/preview/FORM_XIII
```

### 2. ComplianceDataService Enhancement
**Location:** `app/Compliance/ComplianceDataService.php`

**New Method:**
- `normalizeDataPublic()` - Exposes data normalization for external use

**Data Normalization Logic:**
- Handles NIL status → returns empty rows
- Bidirectional mapping: `entries` ↔ `rows`
- Ensures `totals` and `period` exist
- Standardizes data structure for all forms

### 3. Route Configuration
**Location:** `routes/compliance.php`

**New Route:**
```php
Route::get('/preview/{formCode}', 
    [CompliancePreviewController::class, 'preview']
)->name('compliance.preview');
```

**Accessible at:**
- `/compliance/preview/FORM_B`
- `/compliance/preview/FORM_10`
- `/compliance/preview/FORM_XIII`
- etc. (all 38 forms)

---

## SUPPORTED FORMS (38 Total)

### Factories Act Forms (12)
- FORM_B (Wage Register)
- FORM_10 (Overtime Register)
- FORM_25 (Attendance Register)
- FORM_12 (Employee Register)
- FORM_2 (Work Shift)
- FORM_7 (Inspection Register)
- FORM_8 (Incident)
- FORM_11 (Accident Register)
- FORM_17 (Health Register)
- FORM_18 (Accident Report)
- FORM_26 (Accident Register)
- FORM_26A (Dangerous Occurrence)

### CLRA Forms (13)
- FORM_XII (Contractor Master)
- FORM_XIII (Contractor Workmen)
- FORM_XIV (Employment Card)
- FORM_XVI (Contractor Muster)
- FORM_XVII (Contractor Wage Register)
- FORM_XIX (Contractor Wage Slip)
- FORM_XX (Deduction Register)
- FORM_XXI (Fines Register)
- FORM_XXII (Advance Register)
- FORM_XXIII (Contractor Overtime)
- FORM_XXIV (Contractor Half-Yearly)
- FORM_XXV (Principal Annual)

### Shops Act Forms (8)
- SHOPS_FORM_12 (Wage Register)
- SHOPS_FORM_13 (Leave Register)
- SHOPS_FORM_1 (Employee Register)
- SHOPS_FORM_C (Bonus Register)
- SHOPS_FORM_VI (Holiday Register)
- SHOPS_FINES (Fines Register)
- SHOPS_UNPAID (Unpaid Bonus)

### Social Security Forms (2)
- ESI_FORM_12 (Incident)
- EPF_INSPECTION (Inspection Register)

### Labour Welfare Forms (4)
- FORM_A (Employee Register)
- FORM_C (Deduction Register)
- FORM_D (Attendance Register)
- FORM_D_ER (Equal Remuneration)

### Other Forms (1)
- CONTRACTOR_MASTER

---

## SUBSCRIPTION LOGIC

### FULL Subscription
- ✅ Fetches real data from database
- ✅ Displays all rows and entries
- ✅ Shows complete form data
- ✅ Supports all features

### MINIMAL Subscription
- ✅ Shows empty preview
- ✅ Displays form structure
- ✅ No data rows
- ✅ Upgrade prompt message

**Implementation:**
```php
if ($subscription === 'FULL') {
    $data = $this->dataService->buildFormData(...);
} else {
    $data = ['rows' => [], 'entries' => [], ...];
}
```

---

## BLADE TEMPLATE STANDARDIZATION

### Required Loop Pattern
All blade templates must use this pattern for compatibility:

```blade
@foreach($rows ?? $entries ?? [] as $row)
    <!-- Row rendering -->
@endforeach
```

### Data Variables Available
- `$rows` - Array of data rows
- `$entries` - Alias for rows (bidirectional)
- `$totals` - Summary totals
- `$period` - Month/Year period
- `$form_title` - Form name
- `$form_code` - Form code
- `$batch_id` - Batch ID (if from batch)
- `$subscription` - Subscription type
- `$tenant_id` - Tenant ID
- `$branch_id` - Branch ID

---

## USAGE EXAMPLES

### Direct Preview (No Batch)
```
GET /compliance/preview/FORM_B?month=1&year=2024
```

### Preview from Batch
```
GET /compliance/preview/FORM_B?batch_id=5
```

### With Branch Override
```
GET /compliance/preview/FORM_XIII?batch_id=5&branch_id=2
```

### In Blade Template
```blade
<a href="{{ route('compliance.preview', ['formCode' => 'FORM_B', 'batch_id' => $batch->id]) }}">
    Preview Form B
</a>
```

---

## ERROR HANDLING

### 404 Errors
- Form not found in registry
- Blade template not found
- Batch not found

### 403 Errors
- Unauthorized tenant access

### 500 Errors
- Data service failure
- Builder execution error
- Template rendering error

All errors are logged with context for debugging.

---

## LOGGING

Preview requests are logged with:
- Form code
- Batch ID
- Subscription type
- Row count
- Timestamp

**Log Location:** `storage/logs/laravel.log`

**Example Log Entry:**
```
[2024-01-15 10:30:45] local.INFO: Compliance Preview {"form":"FORM_B","batch_id":5,"subscription":"FULL","rows":25}
```

---

## TESTING CHECKLIST

- [ ] FORM_B preview loads with FULL subscription
- [ ] FORM_B preview shows empty with MINIMAL subscription
- [ ] FORM_XIII preview works
- [ ] SHOPS_FORM_12 preview works
- [ ] ESI_FORM_12 preview works
- [ ] Invalid form code returns 404
- [ ] Invalid batch ID returns 404
- [ ] Unauthorized tenant access returns 403
- [ ] Blade template renders correctly
- [ ] Data normalization works (entries ↔ rows)
- [ ] Totals are calculated
- [ ] Period is formatted correctly

---

## PERFORMANCE CONSIDERATIONS

1. **Database Queries:** Optimized through repositories
2. **Caching:** Consider caching form metadata
3. **Lazy Loading:** Blade templates loaded on-demand
4. **Batch Context:** Reuses batch data when available

---

## FUTURE ENHANCEMENTS

1. Add form preview caching
2. Implement preview PDF export
3. Add comparison between periods
4. Support for form templates customization
5. Preview data filtering options

---

## TROUBLESHOOTING

### Preview shows empty data
- Check subscription type: `$user->tenant->subscription_type`
- Verify database has data for period
- Check builder is registered in FormRegistry

### Blade template not found
- Verify template exists: `resources/views/compliance/forms/{formCode}.blade.php`
- Check template naming convention (lowercase form code)
- Ensure FormRegistry has correct template path

### Authorization errors
- Verify user is authenticated
- Check tenant_id matches
- Verify batch belongs to user's tenant

---

## SUMMARY

The Universal Compliance Form Preview System provides:
- ✅ Single controller for all 38 forms
- ✅ Automatic template detection
- ✅ Subscription-aware data fetching
- ✅ Standardized data normalization
- ✅ Comprehensive error handling
- ✅ Debug logging
- ✅ Batch context support
- ✅ Zero code duplication

**Result:** Forms preview automatically without separate controllers.
