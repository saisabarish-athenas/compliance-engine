# API-Driven Forms - Quick Reference

## File Structure

```
app/Services/Compliance/Forms/
├── BaseFormService.php          # Abstract base class
├── Form10Service.php            # Overtime Register
├── Form12Service.php            # Adult Worker Register
├── Form17Service.php            # Health Register
├── Form25Service.php            # Muster Roll
├── FormBService.php             # Wage Register
├── Form26Service.php            # Accident Register
├── Form26AService.php           # Dangerous Occurrences
└── HazardRegisterService.php    # Hazard Register

app/Http/Controllers/API/
└── ComplianceFormController.php # API endpoints

routes/
└── api.php                      # API routes
```

## API Endpoints

```
GET /api/compliance/forms/form10
GET /api/compliance/forms/form12
GET /api/compliance/forms/form17
GET /api/compliance/forms/form25
GET /api/compliance/forms/formB
GET /api/compliance/forms/form26
GET /api/compliance/forms/form26A
GET /api/compliance/forms/hazard
```

## Query Parameters

```
?tenant_id=1&branch_id=1&month=1&year=2024
```

## Response Structure

```json
{
  "header": { ... },
  "rows": [ ... ],
  "totals": { ... },
  "period_month": 1,
  "period_year": 2024,
  "period": "1/2024",
  "status": "SUCCESS" or "NIL"
}
```

## Usage in Code

### Via API Endpoint

```php
$response = Http::get('/api/compliance/forms/formB', [
    'tenant_id' => 1,
    'branch_id' => 1,
    'month' => 1,
    'year' => 2024,
]);

$data = $response->json();
```

### Via Service Class

```php
$service = new FormBService();
$data = $service->generate($tenantId, $branchId, $month, $year);
```

### Via ComplianceExecutionService

```php
$executionService = app(ComplianceExecutionService::class);
$data = $executionService->getFormDataViaAPI('FORM_B', $tenantId, $branchId, $month, $year);
```

## Form Code Mapping

| Form Code | Service Class | Description |
|-----------|---------------|-------------|
| FORM_10 | Form10Service | Overtime Register |
| FORM_12 | Form12Service | Adult Worker Register |
| FORM_17 | Form17Service | Health Register |
| FORM_25 | Form25Service | Muster Roll |
| FORM_B | FormBService | Wage Register |
| FORM_26 | Form26Service | Accident Register |
| FORM_26A | Form26AService | Dangerous Occurrences |
| HAZARD_REGISTER | HazardRegisterService | Hazard Register |

## Database Tables Used

| Form | Tables |
|------|--------|
| FORM_10 | workforce_payroll_entry, workforce_employee, workforce_payroll_cycle |
| FORM_12 | workforce_employee, workforce_attendance |
| FORM_17 | incident_documents, workforce_employee |
| FORM_25 | workforce_attendance, workforce_employee |
| FORM_B | workforce_payroll_entry, workforce_employee, workforce_payroll_cycle |
| FORM_26 | incident_documents, workforce_employee |
| FORM_26A | incident_documents, workforce_employee |
| HAZARD_REGISTER | incident_documents, workforce_employee |

## Adding a New Form

### 1. Create Service

```php
class FormXXService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('table_name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('date_column', [$startDate, $endDate])
            ->select(['field1', 'field2'])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return empty($rows) ? $this->nilResponse() : $this->buildResponse($rows);
    }
}
```

### 2. Add Endpoint

```php
public function formXX(Request $request)
{
    $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
    $branchId = $request->query('branch_id', 1);
    $month = $request->query('month', now()->month);
    $year = $request->query('year', now()->year);

    $service = new FormXXService();
    return response()->json($service->generate($tenantId, $branchId, $month, $year));
}
```

### 3. Register Route

```php
Route::get('/formXX', [ComplianceFormController::class, 'formXX']);
```

### 4. Update Service Map

```php
$serviceMap = [
    'FORM_XX' => FormXXService::class,
];
```

## Key Methods

### BaseFormService

```php
// Set parameters and get date range
[$startDate, $endDate] = $this->getDateRange();

// Build response with rows and totals
$this->buildResponse($rows, $totals);

// Get header info (tenant, branch)
$this->getHeader();

// Return empty response
$this->nilResponse();
```

## Performance Tips

1. Use eager loading with joins
2. Select only required fields
3. Add indexes on tenant_id, branch_id, date columns
4. Use whereBetween for date ranges
5. Group and aggregate in database, not PHP

## Testing

```php
// Test service directly
$service = new FormBService();
$data = $service->generate(1, 1, 1, 2024);
$this->assertArrayHasKey('rows', $data);

// Test API endpoint
$response = $this->get('/api/compliance/forms/formB?tenant_id=1');
$response->assertStatus(200);
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Empty results | Check data exists for period |
| Missing fields | Verify SELECT clause in service |
| Slow queries | Add database indexes |
| Wrong data | Check tenant_id, branch_id filters |
| 404 error | Verify route is registered |

## Documentation

- Full docs: `API_DRIVEN_FORMS_ARCHITECTURE.md`
- This file: `API_DRIVEN_FORMS_QUICK_REFERENCE.md`
