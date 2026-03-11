# Generator Refactoring - Quick Reference

## The Rule
**Generators NEVER query the database. API services ALWAYS do.**

## Pipeline Flow

```
Request
  ↓
ComplianceOrchestrator::execute()
  ↓
FormApiServiceFactory::make($formCode)
  ├─ Queries database
  ├─ Fetches tenant, branch, records
  └─ Returns structured array
  ↓
FormGeneratorFactory::make($formCode)
  ├─ Calls prepareData($apiData)
  ├─ Transforms data only
  └─ Returns formatted structure
  ↓
Blade Template
  ├─ Receives formatted data
  └─ Renders PDF/HTML
```

## Creating a New Generator

### Step 1: Extend BaseFormGenerator
```php
class MyFormGenerator extends BaseFormGenerator
{
    protected string $formCode = 'MY_FORM';
    protected string $view = 'compliance.forms.my_form';
    
    protected function prepareData(array $rawData): array
    {
        // Transform API data only
    }
}
```

### Step 2: Implement prepareData()
```php
protected function prepareData(array $rawData): array
{
    // Input: $rawData from API service
    // - $rawData['records']
    // - $rawData['tenant']
    // - $rawData['branch']
    // - $rawData['period_month']
    // - $rawData['period_year']
    
    $rows = [];
    foreach ($rawData['records'] as $record) {
        $rows[] = [
            'field1' => $record->field1 ?? 'N/A',
            'field2' => $record->field2 ?? 0,
        ];
    }
    
    return [
        'header' => [
            'form_title' => 'My Form Title',
            'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
            'branch' => $rawData['branch'],
            'tenant' => $rawData['tenant'],
        ],
        'rows' => $rows,
        'totals' => $this->calculateTotals($rows, ['field2']),
        'is_nil' => count($rows) === 0,
    ];
}
```

### Step 3: Register in FormGeneratorFactory
```php
protected static array $myForms = ['MY_FORM'];

public static function make(string $formCode): ?BaseFormGenerator
{
    if (in_array($formCode, self::$myForms)) {
        return new MyFormGenerator();
    }
    // ...
}
```

## Creating a New API Service

### Step 1: Extend BaseFormApiService
```php
class MyFormApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        
        // Query database
        $records = DB::table('my_table')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$this->periodStart, $this->periodEnd])
            ->get();
        
        return [
            'records' => $records,
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period_month' => $month,
            'period_year' => $year,
            // Add form-specific data
            'contractor_name' => '...',
        ];
    }
}
```

### Step 2: Register in FormApiServiceFactory
```php
public static function make(string $formCode): ?BaseFormApiService
{
    return match($formCode) {
        'MY_FORM' => new MyFormApiService(),
        // ...
    };
}
```

## What Generators CAN Do

✅ Transform data structures
✅ Format numbers and dates
✅ Calculate totals
✅ Group records
✅ Map field names
✅ Validate data format
✅ Call helper methods

## What Generators CANNOT Do

❌ Query database (DB::table, Model::where, etc.)
❌ Call aggregator methods
❌ Validate business rules
❌ Fetch external data
❌ Call API services
❌ Orchestrate workflow

## Common Patterns

### Formatting Numbers
```php
'amount' => round($record->amount ?? 0, 2),
```

### Formatting Dates
```php
'date' => $record->date ? date('d-m-Y', strtotime($record->date)) : 'N/A',
```

### Calculating Totals
```php
$totals = $this->calculateTotals($rows, ['amount', 'quantity']);
```

### Handling Nil Forms
```php
'is_nil' => count($rows) === 0,
```

### Mapping Fields
```php
'employee_name' => $record->name ?? $record->employee_name ?? 'N/A',
```

## Testing

### Test Generator (No DB needed)
```php
public function test_generator_transforms_data()
{
    $generator = new MyFormGenerator();
    
    $apiData = [
        'records' => [
            (object)['field1' => 'value1', 'field2' => 100],
        ],
        'tenant' => ['name' => 'Test Tenant'],
        'branch' => ['name' => 'Test Branch'],
        'period_month' => 1,
        'period_year' => 2024,
    ];
    
    $result = $generator->prepareData($apiData);
    
    $this->assertCount(1, $result['rows']);
    $this->assertEquals('value1', $result['rows'][0]['field1']);
}
```

### Test API Service (With DB)
```php
public function test_api_service_fetches_data()
{
    $service = new MyFormApiService();
    
    $result = $service->fetch(1, 1, 1, 2024);
    
    $this->assertArrayHasKey('records', $result);
    $this->assertArrayHasKey('tenant', $result);
    $this->assertArrayHasKey('branch', $result);
}
```

## Debugging

### Enable Trace Logging
```bash
php artisan compliance:trace-form-data \
  --tenant=1 \
  --branch=1 \
  --month=1 \
  --year=2024 \
  --form=MY_FORM \
  --verbose
```

### Check API Data
```php
$apiService = FormApiServiceFactory::make('MY_FORM');
$data = $apiService->fetch(1, 1, 1, 2024);
dd($data);
```

### Check Generator Output
```php
$generator = FormGeneratorFactory::make('MY_FORM');
$formatted = $generator->prepareData($data);
dd($formatted);
```

## Performance Tips

1. **API services should cache results**
   ```php
   return Cache::remember("form_data_{$formCode}_{$tenantId}_{$branchId}_{$month}_{$year}", 
       3600, fn() => $this->fetchData());
   ```

2. **Generators should be lightweight**
   - Avoid complex loops
   - Use array_map for transformations
   - Pre-calculate totals in API service if possible

3. **Use select() to limit columns**
   ```php
   DB::table('table')->select('id', 'name', 'amount')->get();
   ```

## Troubleshooting

### Generator receives null data
**Cause:** API service not returning expected structure
**Fix:** Check API service returns all required keys

### Missing fields in output
**Cause:** Generator not mapping all fields
**Fix:** Add field mapping in prepareData()

### Totals don't match
**Cause:** calculateTotals() called with wrong fields
**Fix:** Verify field names in rows match totals config

### Performance issues
**Cause:** API service doing too much work
**Fix:** Move complex calculations to API service, cache results
