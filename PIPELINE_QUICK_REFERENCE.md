# Compliance Pipeline - Quick Reference

## Standard API Response Structure

All 34 API services return this structure:

```php
[
    'records' => [              // Array of data rows
        ['field1' => 'value1', 'field2' => 'value2'],
        ['field1' => 'value3', 'field2' => 'value4'],
    ],
    'meta' => [                 // Metadata
        'tenant_id' => 1,
        'branch_id' => 1,
        'month' => 1,
        'year' => 2024,
    ],
    'tenant' => [               // Tenant details
        'name' => 'Company Name',
        'establishment_name' => 'Establishment',
        'factory_license_no' => 'LIC123',
        'pf_code' => 'PF123',
        'esi_code' => 'ESI123',
    ],
    'branch' => [               // Branch details
        'name' => 'Branch Name',
        'address' => 'Address',
        'pf_code' => 'PF456',
        'esi_code' => 'ESI456',
    ],
    'period' => 'January 2024', // Formatted period
]
```

## Using API Services

### Get Service Instance
```php
$service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
```

### Fetch Data
```php
$data = $service->fetch(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2024
);
```

### Access Data
```php
$records = $data['records'];           // Array of rows
$tenantId = $data['meta']['tenant_id'];
$month = $data['meta']['month'];
$year = $data['meta']['year'];
```

## Creating New API Service

### 1. Create Service Class
```php
<?php
namespace App\Services\Compliance\FormApis;

class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('table_name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->select(['field1', 'field2', 'field3'])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
```

### 2. Register in FormApiServiceFactory
```php
protected static array $generatorMap = [
    'FORM_XXX' => FormXXXApiService::class,
];
```

## Creating New Generator

### 1. Create Generator Class
```php
<?php
namespace App\Services\Compliance\FormGenerator;

class FormXXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXX';
    protected string $view = 'compliance.forms.form_xxx';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];

        foreach ($records as $record) {
            $rows[] = [
                'field1' => $record->field1 ?? 'N/A',
                'field2' => $record->field2 ?? 0,
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'FORM XXX - Title',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $this->calculateTotals($rows, ['field2']),
            'is_nil' => count($rows) === 0,
        ];
    }
}
```

### 2. Register in FormGeneratorFactory
```php
protected static array $generatorMap = [
    'FORM_XXX' => FormXXXGenerator::class,
];
```

## Diagnostic Commands

### Check Pipeline
```bash
php artisan compliance:pipeline-check
```

### Check Specific Form
```bash
php artisan compliance:pipeline-check --form=FORM_B
```

## Multi-Tenant Safety

### Always Filter by Tenant and Branch
```php
DB::table('table_name')
    ->where('tenant_id', $tenantId)
    ->where('branch_id', $branchId)
    ->get();
```

### Validate in Constructor
```php
$this->validateTenantAndBranch($tenantId, $branchId);
```

## Common Patterns

### Safe Record Access
```php
$records = $rawData['records'] ?? [];
foreach ($records as $record) {
    $value = $record->field ?? 'default';
}
```

### Safe Meta Access
```php
$month = $rawData['meta']['month'] ?? 1;
$year = $rawData['meta']['year'] ?? 2024;
```

### Calculate Totals
```php
$totals = $this->calculateTotals($rows, [
    'field1', 'field2', 'field3'
]);
```

### Format Period
```php
$period = $this->formatPeriod($month, $year);
// Returns: "January 2024"
```

## All 34 Forms

### CLRA Forms (10)
- FORM_XII, FORM_XIII, FORM_XIV, FORM_XVI, FORM_XVII
- FORM_XIX, FORM_XX, FORM_XXI, FORM_XXII, FORM_XXIII

### Labour Welfare (4)
- FORM_A, FORM_C, FORM_D, FORM_D_ER

### Social Security (3)
- FORM_11, ESI_FORM_12, EPF_INSPECTION

### Factories Act (11)
- FORM_B, FORM_2, FORM_8, FORM_10, FORM_12
- FORM_17, FORM_18, FORM_25, FORM_26, FORM_26A, HAZARD_REG

### Shops & Establishment (6)
- SHOPS_FORM_C, SHOPS_FORM_12, SHOPS_FORM_13
- SHOPS_FORM_VI, SHOPS_UNPAID, SHOPS_FINES

## Testing

### Test API Service
```php
$service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
$data = $service->fetch(1, 1, 1, 2024);

// Verify structure
assert(isset($data['records']));
assert(isset($data['meta']));
assert($data['meta']['tenant_id'] === 1);
assert($data['meta']['branch_id'] === 1);
```

### Test Generator
```php
$generator = app(\App\Services\Compliance\FormGenerator\FormBGenerator::class);
$formData = $generator->prepareData($apiData);

// Verify structure
assert(isset($formData['header']));
assert(isset($formData['rows']));
assert(isset($formData['totals']));
assert(isset($formData['is_nil']));
```

## Troubleshooting

### API returns empty records
- Check database has data for tenant/branch
- Verify date range filtering
- Check multi-tenant filtering

### Generator shows blank rows
- Verify API returns 'records' key
- Check generator reads from $data['records']
- Verify field names match template

### Template shows N/A values
- Check API returns all required fields
- Verify generator maps all fields
- Check null coalescing in generator

### Multi-tenant data leakage
- Verify API filters by tenant_id
- Verify API filters by branch_id
- Check BaseFormApiService validation

## Performance Tips

1. **Use Indexes**: Ensure tenant_id and branch_id are indexed
2. **Limit Results**: Add pagination for large datasets
3. **Cache Metadata**: Cache tenant/branch details
4. **Batch Operations**: Process multiple forms together

## Security

1. **Always validate tenant/branch**: Use validateTenantAndBranch()
2. **Never trust user input**: Use parameterized queries
3. **Filter at database level**: Don't filter in application
4. **Log access**: Monitor form access patterns

---

**Last Updated:** 2024
**Status:** Production Ready
**All 34 Forms:** Supported
