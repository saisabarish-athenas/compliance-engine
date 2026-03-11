# UNIVERSAL PREVIEW SYSTEM - ARCHITECTURE DIAGRAM

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         USER REQUEST                                    │
│                  GET /compliance/preview/FORM_B                         │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    ROUTE DISPATCHER                                     │
│              routes/compliance.php                                      │
│  Route::get('/preview/{formCode}', CompliancePreviewController)         │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│            COMPLIANCE PREVIEW CONTROLLER                                │
│  app/Http/Controllers/Compliance/CompliancePreviewController            │
│                                                                         │
│  1. Extract parameters (formCode, batch_id, month, year)               │
│  2. Resolve tenant_id, branch_id                                       │
│  3. Check subscription (FULL vs MINIMAL)                               │
│  4. Call ComplianceDataService                                         │
│  5. Detect blade template                                              │
│  6. Return view with data                                              │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
                    ▼                         ▼
        ┌──────────────────────┐  ┌──────────────────────┐
        │  FULL SUBSCRIPTION   │  │ MINIMAL SUBSCRIPTION │
        │  Fetch Real Data     │  │  Empty Preview       │
        └──────────┬───────────┘  └──────────┬───────────┘
                   │                         │
                   ▼                         ▼
        ┌──────────────────────────────────────────────┐
        │   COMPLIANCE DATA SERVICE                    │
        │   app/Compliance/ComplianceDataService       │
        │                                              │
        │   buildFormData(formCode, tenant, branch)    │
        │   normalizeData(data)                        │
        └──────────┬───────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────────────┐
        │   FORM REGISTRY                              │
        │   app/Compliance/Registry/FormRegistry       │
        │                                              │
        │   Maps: FORM_B → WageRegisterBuilder         │
        │         FORM_XIII → ContractorWorkmenBuilder │
        │         etc. (38 forms)                      │
        └──────────┬───────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────────────┐
        │   FORM BUILDERS                              │
        │   app/Compliance/Builders/                   │
        │                                              │
        │   WageRegisterBuilder                        │
        │   OvertimeRegisterBuilder                    │
        │   AttendanceRegisterBuilder                  │
        │   ... (38 builders)                          │
        │                                              │
        │   Each builder:                              │
        │   - Accepts repositories                     │
        │   - Fetches data from DB                     │
        │   - Builds form structure                    │
        │   - Returns normalized array                 │
        └──────────┬───────────────────────────────────┘
                   │
        ┌──────────┴──────────┬──────────────┬──────────────┐
        │                     │              │              │
        ▼                     ▼              ▼              ▼
    ┌────────────┐    ┌────────────┐  ┌────────────┐  ┌────────────┐
    │ Employee   │    │ Payroll    │  │ Attendance │  │ Contractor │
    │ Repository │    │ Repository │  │ Repository │  │ Repository │
    └────────────┘    └────────────┘  └────────────┘  └────────────┘
        │                  │               │               │
        └──────────┬───────┴───────┬───────┴───────┬───────┘
                   │               │               │
                   ▼               ▼               ▼
        ┌──────────────────────────────────────────────┐
        │   DATABASE                                   │
        │                                              │
        │   - workforce_employees                      │
        │   - payroll_entries                          │
        │   - workforce_attendance                     │
        │   - contractors                              │
        │   - contract_labour_deployments              │
        │   - bonus_records                            │
        │   - etc.                                     │
        └──────────────────────────────────────────────┘
```

## Data Flow Sequence

```
1. USER REQUEST
   GET /compliance/preview/FORM_B?batch_id=5

2. ROUTE MATCHING
   Route::get('/preview/{formCode}', CompliancePreviewController@preview)

3. CONTROLLER EXECUTION
   ├─ Extract formCode = 'FORM_B'
   ├─ Extract batch_id = 5
   ├─ Resolve tenant_id from Auth::user()
   ├─ Resolve branch_id from batch or user
   ├─ Check subscription type
   └─ Call dataService->buildFormData()

4. DATA SERVICE
   ├─ Check FormRegistry::isRegistered('FORM_B')
   ├─ Get builder class: WageRegisterBuilder
   ├─ Instantiate builder with repositories
   ├─ Call builder->build(tenant, branch, month, year)
   └─ Normalize data

5. BUILDER EXECUTION
   ├─ Query employees from EmployeeRepository
   ├─ Query payroll from PayrollRepository
   ├─ Query attendance from AttendanceRepository
   ├─ Calculate totals
   ├─ Format rows
   └─ Return array

6. DATA NORMALIZATION
   ├─ Map entries ↔ rows
   ├─ Ensure totals exist
   ├─ Ensure period exists
   ├─ Handle NIL status
   └─ Add metadata

7. TEMPLATE DETECTION
   ├─ Detect blade: compliance.forms.form_b
   ├─ Verify template exists
   └─ Prepare view data

8. VIEW RENDERING
   ├─ Pass data to blade template
   ├─ Render HTML
   └─ Return response

9. USER RECEIVES
   ├─ Rendered form with data
   ├─ Or empty preview (MINIMAL)
   └─ Or error page
```

## Component Interaction

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPLIANCE PREVIEW CONTROLLER                │
│                                                                 │
│  public function preview(Request $request, string $formCode)   │
│  {                                                              │
│    // 1. Resolve context                                       │
│    $user = Auth::user();                                        │
│    $tenantId = $user->tenant_id;                               │
│    $branchId = $request->get('branch_id', ...);                │
│    $month = $request->get('month', now()->month);              │
│    $year = $request->get('year', now()->year);                 │
│                                                                 │
│    // 2. Check subscription                                    │
│    $subscription = $user->tenant->subscription_type;           │
│                                                                 │
│    // 3. Build data                                            │
│    if ($subscription === 'FULL') {                             │
│      $data = $this->dataService->buildFormData(                │
│        $formCode, $tenantId, $branchId, $month, $year          │
│      );                                                         │
│    } else {                                                     │
│      $data = ['rows' => [], 'entries' => [], ...];             │
│    }                                                            │
│                                                                 │
│    // 4. Detect template                                       │
│    $blade = "compliance.forms." . strtolower($formCode);       │
│    if (!view()->exists($blade)) {                              │
│      abort(404, "Template not found");                         │
│    }                                                            │
│                                                                 │
│    // 5. Add metadata                                          │
│    $data['form_title'] = $formMaster->form_name;               │
│    $data['form_code'] = $formCode;                             │
│    $data['subscription'] = $subscription;                      │
│                                                                 │
│    // 6. Return view                                           │
│    return view($blade, $data);                                 │
│  }                                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Data Structure

```
INPUT: formCode = 'FORM_B'

PROCESSING:
  FormRegistry::getBuilder('FORM_B')
  → WageRegisterBuilder::class

  WageRegisterBuilder::build(tenant, branch, month, year)
  → [
      'rows' => [
        ['employee_id' => 1, 'name' => 'John', 'wage' => 5000, ...],
        ['employee_id' => 2, 'name' => 'Jane', 'wage' => 6000, ...],
        ...
      ],
      'totals' => ['total_wage' => 150000, ...],
      'period' => '1/2024',
      'status' => 'success'
    ]

NORMALIZATION:
  normalizeData(data)
  → [
      'rows' => [...],
      'entries' => [...],  // Bidirectional mapping
      'totals' => [...],
      'period' => '1/2024',
      'form_title' => 'Wage Register',
      'form_code' => 'FORM_B',
      'batch_id' => 5,
      'subscription' => 'FULL',
      'tenant_id' => 1,
      'branch_id' => 2
    ]

OUTPUT: Blade template receives normalized data
```

## Error Handling Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    ERROR HANDLING                               │
│                                                                 │
│  try {                                                          │
│    // Validation                                               │
│    if (!FormRegistry::isRegistered($formCode)) {               │
│      abort(404, "Form not found");                             │
│    }                                                            │
│                                                                 │
│    // Template check                                           │
│    if (!view()->exists($blade)) {                              │
│      abort(404, "Template not found");                         │
│    }                                                            │
│                                                                 │
│    // Authorization                                            │
│    if ($batch->tenant_id !== $user->tenant_id) {              │
│      abort(403, "Unauthorized");                               │
│    }                                                            │
│                                                                 │
│    // Data service                                             │
│    $data = $this->dataService->buildFormData(...);             │
│                                                                 │
│  } catch (ModelNotFoundException $e) {                          │
│    abort(404, "Batch or form not found");                      │
│  } catch (Exception $e) {                                      │
│    Log::error('Preview Error', [...]);                         │
│    abort(500, "Preview failed");                               │
│  }                                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Subscription Logic

```
┌─────────────────────────────────────────────────────────────────┐
│                    SUBSCRIPTION CHECK                           │
│                                                                 │
│  $subscription = $user->tenant->subscription_type;             │
│                                                                 │
│  if ($subscription === 'FULL') {                               │
│    ┌─────────────────────────────────────────────────────┐    │
│    │ FULL SUBSCRIPTION                                   │    │
│    │ ✓ Fetch real data from database                    │    │
│    │ ✓ Show all rows and entries                        │    │
│    │ ✓ Display complete form                            │    │
│    │ ✓ Support all features                             │    │
│    │                                                     │    │
│    │ $data = $this->dataService->buildFormData(...)     │    │
│    └─────────────────────────────────────────────────────┘    │
│  } else {                                                       │
│    ┌─────────────────────────────────────────────────────┐    │
│    │ MINIMAL SUBSCRIPTION                                │    │
│    │ ✓ Show empty preview                               │    │
│    │ ✓ Display form structure                           │    │
│    │ ✓ No data rows                                     │    │
│    │ ✓ Upgrade prompt                                   │    │
│    │                                                     │    │
│    │ $data = [                                           │    │
│    │   'rows' => [],                                     │    │
│    │   'entries' => [],                                 │    │
│    │   'totals' => [],                                  │    │
│    │   'period' => "{$month}/{$year}",                  │    │
│    │   'is_preview' => true                             │    │
│    │ ];                                                  │    │
│    └─────────────────────────────────────────────────────┘    │
│  }                                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Template Detection

```
┌─────────────────────────────────────────────────────────────────┐
│                    TEMPLATE DETECTION                           │
│                                                                 │
│  Input: formCode = 'FORM_B'                                    │
│                                                                 │
│  Step 1: Convert to lowercase                                  │
│  $blade = strtolower('FORM_B')  → 'form_b'                     │
│                                                                 │
│  Step 2: Build path                                            │
│  $blade = "compliance.forms.form_b"                            │
│                                                                 │
│  Step 3: Verify exists                                         │
│  if (!view()->exists($blade)) {                                │
│    abort(404, "Template not found");                           │
│  }                                                              │
│                                                                 │
│  Step 4: Render                                                │
│  return view($blade, $data);                                   │
│                                                                 │
│  Result: resources/views/compliance/forms/form_b.blade.php     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Performance Characteristics

```
┌─────────────────────────────────────────────────────────────────┐
│                    PERFORMANCE METRICS                          │
│                                                                 │
│  Operation              │ Time    │ Notes                       │
│  ─────────────────────────────────────────────────────────────  │
│  Route matching         │ < 1ms   │ Laravel routing            │
│  Auth check             │ < 5ms   │ Session lookup             │
│  Tenant resolution      │ < 2ms   │ User relation              │
│  FormRegistry lookup    │ < 1ms   │ Array access               │
│  Builder instantiation  │ < 5ms   │ Dependency injection       │
│  Database queries       │ 50-200ms│ Depends on data volume     │
│  Data normalization     │ < 10ms  │ Array operations           │
│  Template rendering     │ 20-100ms│ Blade compilation          │
│  ─────────────────────────────────────────────────────────────  │
│  TOTAL (FULL)           │ 100-400ms                             │
│  TOTAL (MINIMAL)        │ 30-100ms                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Scalability

```
┌─────────────────────────────────────────────────────────────────┐
│                    SCALABILITY DESIGN                           │
│                                                                 │
│  Single Controller → All 38 Forms                              │
│  ✓ No code duplication                                         │
│  ✓ Easy to add new forms                                       │
│  ✓ Consistent behavior                                         │
│  ✓ Centralized error handling                                  │
│                                                                 │
│  FormRegistry → Dynamic Form Mapping                           │
│  ✓ Add form: 1 line in registry                                │
│  ✓ No controller changes needed                                │
│  ✓ No route changes needed                                     │
│  ✓ Automatic template detection                                │
│                                                                 │
│  Builders → Modular Data Fetching                              │
│  ✓ Each builder independent                                    │
│  ✓ Repositories handle DB queries                              │
│  ✓ Easy to optimize individual builders                        │
│  ✓ Testable in isolation                                       │
│                                                                 │
│  Data Normalization → Consistent Interface                     │
│  ✓ All forms use same data structure                           │
│  ✓ Blade templates standardized                                │
│  ✓ Easy to add features                                        │
│  ✓ Reduced maintenance burden                                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Summary

The Universal Preview System provides:
- **Single Controller** for all 38 forms
- **Automatic Template Detection** based on form code
- **Subscription-Aware** data fetching
- **Standardized Data Structure** for all forms
- **Comprehensive Error Handling** with logging
- **Zero Code Duplication** across forms
- **Scalable Architecture** for future forms
- **Consistent User Experience** across all forms
