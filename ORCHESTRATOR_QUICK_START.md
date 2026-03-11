# Compliance Orchestrator - Quick Start Guide

## 5-Minute Setup

### 1. Verify Installation
```bash
# Check if migration exists
ls database/migrations/*compliance_execution_logs*

# Run migration
php artisan migrate

# Verify table
php artisan tinker
>>> DB::table('compliance_execution_logs')->count()
```

### 2. Test Basic Execution
```php
// In tinker or controller
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);

// Test preview
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview',
    batchId: 1
);

dd($result);
```

### 3. Check Execution Logs
```php
// View execution logs
$logs = $orchestrator->getExecutionLogs(1, 'FORM_B');
dd($logs);

// Get statistics
$stats = $orchestrator->getExecutionStats(1);
dd($stats);
```

## Common Tasks

### Generate Form Preview
```php
$orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);

$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'preview',
    batchId: 1
);

if ($result['status'] === 'success') {
    $html = $result['result']['html'];
    // Display in browser
    return view('preview', ['html' => $html]);
}
```

### Generate PDF
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'pdf',
    batchId: 1
);

if ($result['status'] === 'success') {
    $pdfContent = $result['result']['content'];
    return response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="FORM_B.pdf"'
    ]);
}
```

### Batch Processing
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'batch',
    batchId: 1
);

if ($result['status'] === 'success') {
    $filePath = $result['result']['file_path'];
    // File stored at: storage/app/{$filePath}
}
```

### Create Inspection Pack
```php
$result = $orchestrator->execute(
    tenantId: 1,
    branchId: 1,
    month: 3,
    year: 2024,
    formCode: 'FORM_B',
    mode: 'inspection_pack',
    batchId: 1
);

if ($result['status'] === 'success') {
    $zipPath = $result['result']['zip_path'];
    // ZIP stored at: storage/app/{$zipPath}
}
```

## Troubleshooting

### Issue: "Subscription access denied"
```php
// Check subscription
$tenant = \App\Models\Tenant::find(1);
echo $tenant->subscription_type; // Should be 'FULL'

// Update if needed
$tenant->update(['subscription_type' => 'FULL']);
```

### Issue: "Form not found in master"
```php
// Check if form exists
$form = \App\Models\ComplianceFormsMaster::where('form_code', 'FORM_B')->first();
if (!$form) {
    echo "Form not found";
}
```

### Issue: "No generator found"
```php
// Check if generator is registered
$generator = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::make('FORM_B');
if (!$generator) {
    echo "Generator not found";
}
```

### Issue: "View not found"
```php
// Check if view exists
if (!\Illuminate\Support\Facades\View::exists('compliance.forms.form_b')) {
    echo "View not found";
}
```

## API Service Pattern

### Create New API Service
```php
// 1. Create file: app/Services/Compliance/FormApis/FormXXXApiService.php
<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('table_name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('date_field', [$this->periodStart, $this->periodEnd])
            ->select([...])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

// 2. Register in FormApiServiceFactory
protected static array $apiServices = [
    'FORM_XXX' => FormXXXApiService::class,
];
```

## Testing

### Unit Test
```php
// tests/Unit/ComplianceOrchestratorTest.php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Compliance\ComplianceOrchestrator;

class ComplianceOrchestratorTest extends TestCase
{
    public function test_preview_mode()
    {
        $orchestrator = app(ComplianceOrchestrator::class);
        
        $result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
        
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('html', $result['result']);
    }

    public function test_subscription_validation()
    {
        $tenant = \App\Models\Tenant::find(1);
        $tenant->update(['subscription_type' => 'MINIMAL']);
        
        $orchestrator = app(ComplianceOrchestrator::class);
        
        $result = $orchestrator->execute(1, 1, 3, 2024, 'FORM_B', 'preview', 1);
        
        $this->assertEquals('failed', $result['status']);
        $this->assertStringContainsString('Subscription access denied', $result['error']);
    }
}
```

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test tests/Unit/ComplianceOrchestratorTest.php

# Run with coverage
php artisan test --coverage
```

## Monitoring

### Check Execution Logs
```bash
# In tinker
php artisan tinker

# Get recent executions
>>> DB::table('compliance_execution_logs')->latest()->limit(10)->get()

# Get failed executions
>>> DB::table('compliance_execution_logs')->where('status', 'failed')->get()

# Get statistics
>>> DB::table('compliance_execution_logs')->select('form_code', DB::raw('COUNT(*) as count'), DB::raw('AVG(execution_time) as avg_time'))->groupBy('form_code')->get()
```

### Performance Metrics
```php
// Get execution stats
$stats = $orchestrator->getExecutionStats($batchId);

echo "Total executions: " . $stats['total_executions'];
echo "Successful: " . $stats['successful'];
echo "Failed: " . $stats['failed'];
echo "Average time: " . $stats['average_time'] . "ms";
echo "Total records: " . $stats['total_records'];
```

## Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Http\Request;

class ComplianceFormController extends Controller
{
    public function __construct(private ComplianceOrchestrator $orchestrator) {}

    public function preview(Request $request)
    {
        $result = $this->orchestrator->execute(
            tenantId: auth()->user()->tenant_id,
            branchId: $request->branch_id,
            month: $request->month,
            year: $request->year,
            formCode: $request->form_code,
            mode: 'preview',
            batchId: $request->batch_id
        );

        if ($result['status'] === 'failed') {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json($result['result']);
    }

    public function download(Request $request)
    {
        $result = $this->orchestrator->execute(
            tenantId: auth()->user()->tenant_id,
            branchId: $request->branch_id,
            month: $request->month,
            year: $request->year,
            formCode: $request->form_code,
            mode: 'pdf',
            batchId: $request->batch_id
        );

        if ($result['status'] === 'failed') {
            return response()->json(['error' => $result['error']], 400);
        }

        return response($result['result']['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $request->form_code . '.pdf"'
        ]);
    }

    public function inspectionPack(Request $request)
    {
        $result = $this->orchestrator->execute(
            tenantId: auth()->user()->tenant_id,
            branchId: $request->branch_id,
            month: $request->month,
            year: $request->year,
            formCode: $request->form_code,
            mode: 'inspection_pack',
            batchId: $request->batch_id
        );

        if ($result['status'] === 'failed') {
            return response()->json(['error' => $result['error']], 400);
        }

        $zipPath = storage_path('app/' . $result['result']['zip_path']);
        return response()->download($zipPath);
    }
}
```

## Routes Example

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/compliance/preview', [ComplianceFormController::class, 'preview']);
    Route::post('/compliance/download', [ComplianceFormController::class, 'download']);
    Route::post('/compliance/inspection-pack', [ComplianceFormController::class, 'inspectionPack']);
});
```

## Next Steps

1. **Review Documentation**
   - Read `COMPLIANCE_ORCHESTRATOR_IMPLEMENTATION.md`
   - Review `ORCHESTRATOR_QUICK_REFERENCE.md`

2. **Test Locally**
   - Run preview, pdf, batch, inspection_pack modes
   - Check execution logs
   - Verify subscription enforcement

3. **Deploy to Staging**
   - Follow `ORCHESTRATOR_DEPLOYMENT_CHECKLIST.md`
   - Run full test suite
   - Monitor performance

4. **Deploy to Production**
   - Verify all prerequisites
   - Run migrations
   - Monitor closely for 24 hours

5. **Optimize**
   - Analyze performance metrics
   - Implement caching if needed
   - Optimize slow queries

## Resources

- **Implementation Guide**: `COMPLIANCE_ORCHESTRATOR_IMPLEMENTATION.md`
- **Quick Reference**: `ORCHESTRATOR_QUICK_REFERENCE.md`
- **Structural Analysis**: `STRUCTURAL_ANALYSIS_RECOMMENDATIONS.md`
- **Deployment Checklist**: `ORCHESTRATOR_DEPLOYMENT_CHECKLIST.md`
- **Summary**: `ORCHESTRATOR_IMPLEMENTATION_SUMMARY.md`

## Support

For issues or questions:
1. Check troubleshooting section above
2. Review documentation
3. Check execution logs
4. Contact development team
