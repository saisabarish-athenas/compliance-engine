# API-Driven Forms - Developer Guide

## Quick Start

### 1. Fetch Form Data via API

```php
// Using HTTP client
$response = Http::get('/api/compliance/forms/formB', [
    'tenant_id' => 1,
    'branch_id' => 1,
    'month' => 1,
    'year' => 2024,
]);

$data = $response->json();
```

### 2. Fetch Form Data via Service

```php
// Direct service usage
$service = new FormBService();
$data = $service->generate(1, 1, 1, 2024);
```

### 3. Fetch Form Data via ComplianceExecutionService

```php
// Via execution service
$executionService = app(ComplianceExecutionService::class);
$data = $executionService->getFormDataViaAPI('FORM_B', 1, 1, 1, 2024);
```

## Common Tasks

### Display Form in Blade Template

```blade
@php
    $service = new FormBService();
    $data = $service->generate($tenantId, $branchId, $month, $year);
@endphp

<div class="form-container">
    <h1>{{ $data['header']['tenant_name'] }}</h1>
    <p>Period: {{ $data['period'] }}</p>

    @if($data['status'] === 'NIL')
        <p class="alert">No data available for this period</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Gross Salary</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                    <tr>
                        <td>{{ $row['employee_code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ number_format($row['gross_salary'], 2) }}</td>
                        <td>{{ number_format($row['net_salary'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong>{{ number_format($data['totals']['total_gross_salary'], 2) }}</strong></td>
                    <td><strong>{{ number_format($data['totals']['total_net_salary'], 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>
```

### Generate Multiple Forms

```php
$executionService = app(ComplianceExecutionService::class);

$forms = ['FORM_B', 'FORM_10', 'FORM_25'];
$results = [];

foreach ($forms as $formCode) {
    $results[$formCode] = $executionService->getFormDataViaAPI(
        $formCode,
        $tenantId,
        $branchId,
        $month,
        $year
    );
}

return response()->json($results);
```

### Export Form Data to CSV

```php
$service = new FormBService();
$data = $service->generate($tenantId, $branchId, $month, $year);

$csv = "Employee Code,Name,Gross Salary,Net Salary\n";

foreach ($data['rows'] as $row) {
    $csv .= "{$row['employee_code']},{$row['name']},{$row['gross_salary']},{$row['net_salary']}\n";
}

return response($csv)
    ->header('Content-Type', 'text/csv')
    ->header('Content-Disposition', 'attachment; filename="form_b.csv"');
```

### Filter Form Data

```php
$service = new FormBService();
$data = $service->generate($tenantId, $branchId, $month, $year);

// Filter by designation
$managers = array_filter($data['rows'], fn($row) => $row['designation'] === 'Manager');

// Filter by salary range
$highEarners = array_filter($data['rows'], fn($row) => $row['gross_salary'] > 50000);

// Get specific fields
$names = array_column($data['rows'], 'name');
```

### Calculate Additional Totals

```php
$service = new FormBService();
$data = $service->generate($tenantId, $branchId, $month, $year);

$additionalTotals = [
    'average_salary' => $data['totals']['total_gross_salary'] / count($data['rows']),
    'employee_count' => count($data['rows']),
    'total_deductions' => $data['totals']['total_deduction'],
    'deduction_percentage' => ($data['totals']['total_deduction'] / $data['totals']['total_gross_salary']) * 100,
];
```

## Creating New Forms

### Step 1: Analyze Requirements

```
Form Name: Form XX - Register of Advances
Database Tables: 
  - payroll_advances (main)
  - workforce_employee (join)
Fields:
  - employee_code, name, designation
  - advance_date, advance_amount, repayment_amount
  - balance
Filters:
  - tenant_id, branch_id, advance_date range
```

### Step 2: Create Service Class

```php
<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;

class FormXXService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('payroll_advances as pa')
            ->join('workforce_employee as e', 'e.id', '=', 'pa.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('pa.advance_date', [$startDate, $endDate])
            ->select([
                'e.employee_code',
                'e.name',
                'e.designation',
                'pa.advance_date',
                'pa.advance_amount',
                'pa.repayment_amount',
                DB::raw('COALESCE(pa.advance_amount, 0) - COALESCE(pa.repayment_amount, 0) as balance'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_advances' => array_sum(array_column($rows, 'advance_amount')),
            'total_repayments' => array_sum(array_column($rows, 'repayment_amount')),
            'total_balance' => array_sum(array_column($rows, 'balance')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
```

### Step 3: Add API Endpoint

```php
// app/Http/Controllers/API/ComplianceFormController.php

public function formXX(Request $request)
{
    $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
    $branchId = $request->query('branch_id', 1);
    $month = $request->query('month', now()->month);
    $year = $request->query('year', now()->year);

    $service = new FormXXService();
    $data = $service->generate($tenantId, $branchId, $month, $year);

    return response()->json($data);
}
```

### Step 4: Register Route

```php
// routes/api.php

Route::get('/formXX', [ComplianceFormController::class, 'formXX']);
```

### Step 5: Update Service Map

```php
// app/Services/Compliance/ComplianceExecutionService.php

$serviceMap = [
    // ... existing entries
    'FORM_XX' => FormXXService::class,
];
```

## Testing

### Unit Test

```php
<?php

namespace Tests\Unit\Services\Compliance\Forms;

use App\Services\Compliance\Forms\FormBService;
use Tests\TestCase;

class FormBServiceTest extends TestCase
{
    public function test_form_b_service_returns_correct_structure()
    {
        $service = new FormBService();
        $data = $service->generate(1, 1, 1, 2024);

        $this->assertArrayHasKey('header', $data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('totals', $data);
        $this->assertArrayHasKey('period_month', $data);
        $this->assertArrayHasKey('period_year', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function test_form_b_service_returns_nil_for_empty_data()
    {
        $service = new FormBService();
        $data = $service->generate(999, 999, 1, 2020);

        $this->assertEquals('NIL', $data['status']);
        $this->assertEmpty($data['rows']);
    }

    public function test_form_b_service_calculates_totals()
    {
        $service = new FormBService();
        $data = $service->generate(1, 1, 1, 2024);

        if ($data['status'] !== 'NIL') {
            $this->assertArrayHasKey('total_gross_salary', $data['totals']);
            $this->assertArrayHasKey('total_net_salary', $data['totals']);
            $this->assertGreaterThan(0, $data['totals']['total_gross_salary']);
        }
    }
}
```

### API Test

```php
<?php

namespace Tests\Feature\API;

use Tests\TestCase;

class ComplianceFormControllerTest extends TestCase
{
    public function test_form_b_endpoint_returns_json()
    {
        $response = $this->get('/api/compliance/forms/formB?tenant_id=1&branch_id=1&month=1&year=2024');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'header',
            'rows',
            'totals',
            'period_month',
            'period_year',
            'status',
        ]);
    }

    public function test_form_b_endpoint_with_query_parameters()
    {
        $response = $this->get('/api/compliance/forms/formB', [
            'tenant_id' => 1,
            'branch_id' => 1,
            'month' => 1,
            'year' => 2024,
        ]);

        $response->assertStatus(200);
    }

    public function test_form_b_endpoint_uses_default_parameters()
    {
        $response = $this->get('/api/compliance/forms/formB');

        $response->assertStatus(200);
    }
}
```

## Performance Optimization

### 1. Add Database Indexes

```sql
-- Add indexes for faster queries
ALTER TABLE workforce_payroll_entry ADD INDEX idx_tenant_branch (tenant_id, branch_id);
ALTER TABLE workforce_employee ADD INDEX idx_tenant_branch (tenant_id, branch_id);
ALTER TABLE workforce_attendance ADD INDEX idx_tenant_branch (tenant_id, branch_id);
ALTER TABLE incident_documents ADD INDEX idx_tenant_branch (tenant_id, branch_id);
ALTER TABLE workforce_payroll_cycle ADD INDEX idx_period (period_from, period_to);
```

### 2. Use Query Caching

```php
$rows = DB::table('workforce_payroll_entry as pe')
    ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
    ->where('e.tenant_id', $tenantId)
    ->where('e.branch_id', $branchId)
    ->whereBetween('pc.period_from', [$startDate, $endDate])
    ->select(['e.employee_code', 'e.name', 'pe.gross_salary'])
    ->remember(60) // Cache for 60 minutes
    ->get()
    ->map(fn($row) => (array)$row)
    ->toArray();
```

### 3. Optimize Queries

```php
// Bad: N+1 query problem
$employees = Employee::all();
foreach ($employees as $employee) {
    $payroll = $employee->payroll; // Query in loop
}

// Good: Eager loading
$employees = Employee::with('payroll')->get();
```

## Debugging

### Enable Query Logging

```php
// In your service or controller
DB::enableQueryLog();

$service = new FormBService();
$data = $service->generate(1, 1, 1, 2024);

$queries = DB::getQueryLog();
foreach ($queries as $query) {
    logger()->info($query['query']);
    logger()->info($query['bindings']);
}
```

### Check Response Structure

```php
$service = new FormBService();
$data = $service->generate(1, 1, 1, 2024);

dd($data); // Dump and die to inspect structure
```

### Monitor Performance

```php
$start = microtime(true);

$service = new FormBService();
$data = $service->generate(1, 1, 1, 2024);

$duration = microtime(true) - $start;
logger()->info("Form generation took {$duration} seconds");
```

## Best Practices

1. **Always filter by tenant_id** - Ensure multi-tenant isolation
2. **Always filter by branch_id** - Support multi-branch operations
3. **Use date ranges** - Avoid loading unnecessary data
4. **Select specific fields** - Don't use SELECT *
5. **Use joins** - Avoid N+1 queries
6. **Calculate totals in database** - Use aggregation functions
7. **Handle NIL responses** - Check for empty data
8. **Add error handling** - Catch exceptions gracefully
9. **Log important operations** - Track form generation
10. **Test thoroughly** - Unit and integration tests

## Troubleshooting

### Issue: Empty Results

```php
// Check if data exists
$count = DB::table('workforce_payroll_entry')
    ->where('tenant_id', 1)
    ->where('branch_id', 1)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->count();

logger()->info("Found {$count} payroll entries");
```

### Issue: Slow Queries

```php
// Use EXPLAIN to analyze query
$query = DB::table('workforce_payroll_entry')
    ->where('tenant_id', 1)
    ->where('branch_id', 1)
    ->toSql();

logger()->info("Query: {$query}");

// Check if indexes exist
$indexes = DB::select("SHOW INDEX FROM workforce_payroll_entry");
dd($indexes);
```

### Issue: Missing Fields

```php
// Check database schema
$columns = DB::getSchemaBuilder()->getColumnListing('workforce_payroll_entry');
dd($columns);

// Verify field names match
$row = DB::table('workforce_payroll_entry')->first();
dd($row);
```

## Resources

- Full Documentation: `API_DRIVEN_FORMS_ARCHITECTURE.md`
- Quick Reference: `API_DRIVEN_FORMS_QUICK_REFERENCE.md`
- Implementation Summary: `API_DRIVEN_FORMS_IMPLEMENTATION_SUMMARY.md`
- Implementation Checklist: `API_DRIVEN_FORMS_IMPLEMENTATION_CHECKLIST.md`

## Support

For questions or issues:
1. Check the documentation files
2. Review service class implementations
3. Check database schema
4. Enable query logging
5. Run tests to verify functionality
