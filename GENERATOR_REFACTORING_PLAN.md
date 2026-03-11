# GENERATOR REFACTORING PLAN - DATABASE QUERIES AUDIT

## OBJECTIVE
Move all database queries from generators into API services. Generators must only transform data.

---

## AUDIT RESULTS

### Critical Issues Found

#### 1. PayrollBasedFormGenerator
**File:** `app/Services/Compliance/FormGenerator/PayrollBasedFormGenerator.php`

**Database Queries:**
- Line 127: `DB::table('workforce_employee')` - enrichForm10Data()
- Line 155: `DB::table('workforce_employee')` - enrichFormBData()
- Line 175: `DB::table('workforce_attendance')` - enrichFormBData()
- Line 195: `DB::table('workforce_attendance')->insertOrIgnore()` - autoRepairAttendance()

**Impact:** FORM_B and FORM_10 have database queries inside generators

**Action:** Move to FormBApiService and Form10ApiService

---

#### 2. ContractorBasedFormGenerator
**File:** `app/Services/Compliance/FormGenerator/ContractorBasedFormGenerator.php`

**Database Queries:**
- Line 56: `DB::table('contractor_master')` - prepareFormXX()

**Impact:** FORM_XX has database query inside generator

**Action:** Move to FormXXApiService

---

#### 3. BaseFormGenerator
**File:** `app/Services/Compliance/FormGenerator/BaseFormGenerator.php`

**Database Queries:**
- Line 48: `DB::table('tenants')` - generate()
- Line 73: `DB::table('tenants')` - validateStatutorySettings()
- Line 79: `DB::table('branches')` - validateStatutorySettings()

**Impact:** All generators inherit these queries

**Action:** Move to base API service or orchestrator

---

#### 4. FormAGenerator
**File:** `app/Services/Compliance/FormGenerator/FormAGenerator.php`

**Database Queries:**
- Line 10: `DB::table('workforce_employee')` - generate()

**Impact:** FORM_A has direct database query

**Action:** Move to FormAApiService

---

### Summary of Database Queries in Generators

| Generator | Form | Query | Table | Action |
|-----------|------|-------|-------|--------|
| PayrollBasedFormGenerator | FORM_B | enrichFormBData | workforce_employee, workforce_attendance | Move to API |
| PayrollBasedFormGenerator | FORM_10 | enrichForm10Data | workforce_employee | Move to API |
| PayrollBasedFormGenerator | FORM_B | autoRepairAttendance | workforce_attendance | Move to API |
| ContractorBasedFormGenerator | FORM_XX | prepareFormXX | contractor_master | Move to API |
| BaseFormGenerator | All | generate | tenants | Move to API |
| BaseFormGenerator | All | validateStatutorySettings | tenants, branches | Move to API |
| FormAGenerator | FORM_A | generate | workforce_employee | Move to API |

---

## REFACTORING STRATEGY

### Phase 1: Create Missing API Services
Create API services for forms that don't have them:
- FormBApiService (enhance existing)
- Form10ApiService (enhance existing)
- FormXXApiService (create new)
- FormAApiService (enhance existing)

### Phase 2: Move Database Queries
Move all database queries from generators to API services

### Phase 3: Update Generators
Update generators to accept enriched data from API services

### Phase 4: Update Orchestrator
Ensure orchestrator calls API services before generators

### Phase 5: Validation
Run trace analysis to verify all queries moved

---

## IMPLEMENTATION STEPS

### Step 1: Enhance FormBApiService
Add enrichment data:
- Employee details
- Attendance records
- Wage calculations

### Step 2: Enhance Form10ApiService
Add enrichment data:
- Employee details
- Piece worker status
- Wage calculations

### Step 3: Create FormXXApiService
Add contractor master data

### Step 4: Enhance FormAApiService
Already exists, verify it's being used

### Step 5: Update PayrollBasedFormGenerator
Remove all DB queries, accept enriched data

### Step 6: Update ContractorBasedFormGenerator
Remove all DB queries, accept enriched data

### Step 7: Update BaseFormGenerator
Remove all DB queries, move to orchestrator

### Step 8: Validate Pipeline
Run trace analysis to confirm

---

## EXPECTED OUTCOME

**Before Refactoring:**
```
Orchestrator → API Service → Generator (with DB queries) → Blade
```

**After Refactoring:**
```
Orchestrator → API Service (with all DB queries) → Generator (data transformation only) → Blade
```

---

## VERIFICATION CHECKLIST

- [ ] No DB::table() in any generator
- [ ] No Model::where() in any generator
- [ ] No Eloquent relationships in any generator
- [ ] All generators accept array input
- [ ] All generators return structured output
- [ ] All API services fetch complete data
- [ ] Trace analysis shows all queries in API services
- [ ] All forms render correctly

