# UNIVERSAL PREVIEW SYSTEM - CODE EXAMPLES & INTEGRATION GUIDE

## Controller Implementation

### CompliancePreviewController.php

```php
<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Compliance\ComplianceDataService;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceExecutionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompliancePreviewController extends Controller
{
    public function __construct(private ComplianceDataService $dataService)
    {}

    public function preview(Request $request, string $formCode)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;
            $branchId = $request->get('branch_id', $user->branch_id ?? null);
            $batchId = $request->get('batch_id');

            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            // Validate batch if provided
            if ($batchId) {
                $batch = ComplianceExecutionBatch::where('tenant_id', $tenantId)
                    ->where('id', $batchId)
                    ->firstOrFail();
                
                $month = $batch->period_month;
                $year = $batch->period_year;
                $branchId = $batch->branch_id ?? $branchId;
            }

            // Resolve branch ID safely
            if (!$branchId) {
                $branchId = \App\Models\Branch::where('tenant_id', $tenantId)->first()?->id;
            }

            // Check subscription
            $subscription = $user->tenant->subscription_type ?? 'MINIMAL';

            // Build form data
            if ($subscription === 'FULL') {
                $data = $this->dataService->buildFormData(
                    $formCode,
                    $tenantId,
                    $branchId,
                    $month,
                    $year
                );
            } else {
                // MINIMAL: Empty preview
                $data = [
                    'rows' => [],
                    'entries' => [],
                    'totals' => [],
                    'period' => "{$month}/{$year}",
                    'is_preview' => true,
                ];
            }

            // Get form metadata
            $formMaster = ComplianceFormsMaster::where('form_code', $formCode)->first();
            if (!$formMaster) {
                abort(404, "Form {$formCode} not found");
            }

            // Detect blade template
            $blade = "compliance.forms." . strtolower($formCode);
            if (!view()->exists($blade)) {
                abort(404, "Blade template not found for form: {$formCode}");
            }

            // Add metadata
            $data['form_title'] = $formMaster->form_name;
            $data['form_code'] = $formCode;
            $data['batch_id'] = $batchId;
            $data['period_month'] = $month;
            $data['period_year'] = $year;
            $data['subscription'] = $subscription;
            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;

            Log::info('Compliance Preview', [
                'form' => $formCode,
                'batch_id' => $batchId,
                'subscription' => $subscription,
                'rows' => count($data['rows'] ?? []),
            ]);

            return view($blade, $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Batch or form not found');
        } catch (\Exception $e) {
            Log::error('Preview Error', [
                'form' => $formCode,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Preview failed: ' . $e->getMessage());
        }
    }
}
```

---

## Route Configuration

### routes/compliance.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceExecutionController;
use App\Http\Controllers\Compliance\ProjectSettingsController;
use App\Http\Controllers\Compliance\SignatureController;
use App\Http\Controllers\Compliance\CompliancePreviewController;
use App\Http\Controllers\ManualDataController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    // ... existing routes ...

    // Universal Preview - Works for ALL forms automatically
    Route::get('/preview/{formCode}', 
        [CompliancePreviewController::class, 'preview']
    )->name('compliance.preview');

    // ... rest of routes ...
});
```

---

## Blade Template Integration

### resources/views/compliance/forms/form_b.blade.php

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ $form_title }}</h1>
            <p>Form Code: {{ $form_code }}</p>
            <p>Period: {{ $period_month }}/{{ $period_year }}</p>
            
            @if($subscription === 'MINIMAL')
                <div class="alert alert-info">
                    Preview data limited to FULL subscription users. 
                    <a href="{{ route('upgrade') }}">Upgrade now</a>
                </div>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Wage</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows ?? $entries ?? [] as $row)
                        <tr>
                            <td>{{ $row['employee_id'] ?? '-' }}</td>
                            <td>{{ $row['name'] ?? '-' }}</td>
                            <td>{{ $row['wage'] ?? '-' }}</td>
                            <td>{{ $row['deductions'] ?? '-' }}</td>
                            <td>{{ $row['net_pay'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($totals)
                <div class="totals">
                    <h3>Totals</h3>
                    <p>Total Wage: {{ $totals['total_wage'] ?? 0 }}</p>
                    <p>Total Deductions: {{ $totals['total_deductions'] ?? 0 }}</p>
                    <p>Total Net Pay: {{ $totals['total_net_pay'] ?? 0 }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
```

---

## Usage Examples

### Example 1: Direct Preview Link

```blade
<!-- In any blade template -->
<a href="{{ route('compliance.preview', ['formCode' => 'FORM_B']) }}" 
   class="btn btn-primary">
    Preview Form B
</a>
```

### Example 2: Preview with Batch

```blade
<!-- In batch detail page -->
<a href="{{ route('compliance.preview', [
    'formCode' => 'FORM_B',
    'batch_id' => $batch->id
]) }}" class="btn btn-primary">
    Preview Form B
</a>
```

### Example 3: Preview with Custom Period

```blade
<!-- In form selection page -->
<a href="{{ route('compliance.preview', [
    'formCode' => 'FORM_XIII',
    'month' => 1,
    'year' => 2024
]) }}" class="btn btn-primary">
    Preview Form XIII
</a>
```

### Example 4: Preview with Branch

```blade
<!-- In multi-branch scenario -->
<a href="{{ route('compliance.preview', [
    'formCode' => 'SHOPS_FORM_12',
    'batch_id' => $batch->id,
    'branch_id' => $branch->id
]) }}" class="btn btn-primary">
    Preview Shop Form 12
</a>
```

### Example 5: PHP Code

```php
// In controller or service
$url = route('compliance.preview', [
    'formCode' => 'FORM_B',
    'batch_id' => $batch->id,
    'month' => 1,
    'year' => 2024
]);

return redirect($url);
```

---

## Data Service Integration

### Using ComplianceDataService

```php
<?php

namespace App\Services;

use App\Compliance\ComplianceDataService;

class MyService
{
    public function __construct(private ComplianceDataService $dataService)
    {}

    public function getFormData(string $formCode, int $tenantId, int $branchId)
    {
        // Build form data
        $data = $this->dataService->buildFormData(
            $formCode,
            $tenantId,
            $branchId,
            now()->month,
            now()->year
        );

        // Normalize data
        $normalized = $this->dataService->normalizeDataPublic($data);

        return $normalized;
    }
}
```

---

## FormRegistry Integration

### Adding a New Form

```php
// In app/Compliance/Registry/FormRegistry.php

private static array $registry = [
    // ... existing forms ...

    'NEW_FORM' => [
        'builder' => \App\Compliance\Builders\NewFormBuilder::class,
        'template' => 'compliance.forms.new_form',
    ],
];
```

### Using FormRegistry

```php
<?php

use App\Compliance\Registry\FormRegistry;

// Check if form is registered
if (FormRegistry::isRegistered('FORM_B')) {
    // Get builder class
    $builderClass = FormRegistry::getBuilder('FORM_B');
    
    // Get template path
    $template = FormRegistry::getTemplate('FORM_B');
    
    // Get all forms
    $allForms = FormRegistry::all();
}
```

---

## Error Handling Examples

### Handling 404 Errors

```blade
<!-- In error view -->
@if($exception->getStatusCode() === 404)
    <div class="alert alert-danger">
        <h4>Form Not Found</h4>
        <p>The requested form could not be found.</p>
        <p>Please check the form code and try again.</p>
    </div>
@endif
```

### Handling 403 Errors

```blade
<!-- In error view -->
@if($exception->getStatusCode() === 403)
    <div class="alert alert-warning">
        <h4>Access Denied</h4>
        <p>You do not have permission to access this form.</p>
        <p>Please contact your administrator.</p>
    </div>
@endif
```

### Handling 500 Errors

```blade
<!-- In error view -->
@if($exception->getStatusCode() === 500)
    <div class="alert alert-danger">
        <h4>Server Error</h4>
        <p>An error occurred while processing your request.</p>
        <p>Please try again later or contact support.</p>
    </div>
@endif
```

---

## Testing Examples

### Unit Test

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\ComplianceFormsMaster;

class CompliancePreviewControllerTest extends TestCase
{
    public function test_preview_form_with_full_subscription()
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create([
                'subscription_type' => 'FULL'
            ])->id
        ]);

        $this->actingAs($user)
            ->get('/compliance/preview/FORM_B')
            ->assertStatus(200)
            ->assertViewHas('rows');
    }

    public function test_preview_form_with_minimal_subscription()
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create([
                'subscription_type' => 'MINIMAL'
            ])->id
        ]);

        $this->actingAs($user)
            ->get('/compliance/preview/FORM_B')
            ->assertStatus(200)
            ->assertViewHas('rows', []);
    }

    public function test_preview_invalid_form()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/compliance/preview/INVALID_FORM')
            ->assertStatus(404);
    }

    public function test_preview_requires_authentication()
    {
        $this->get('/compliance/preview/FORM_B')
            ->assertRedirect('/login');
    }
}
```

### Feature Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\ComplianceExecutionBatch;

class PreviewFormFeatureTest extends TestCase
{
    public function test_preview_with_batch_context()
    {
        $tenant = Tenant::factory()->create(['subscription_type' => 'FULL']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $batch = ComplianceExecutionBatch::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get("/compliance/preview/FORM_B?batch_id={$batch->id}")
            ->assertStatus(200)
            ->assertViewHas('batch_id', $batch->id);
    }

    public function test_preview_respects_tenant_isolation()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        
        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $batch2 = ComplianceExecutionBatch::factory()->create(['tenant_id' => $tenant2->id]);

        $this->actingAs($user1)
            ->get("/compliance/preview/FORM_B?batch_id={$batch2->id}")
            ->assertStatus(404);
    }
}
```

---

## Logging Examples

### Log Entry Format

```
[2024-01-15 10:30:45] local.INFO: Compliance Preview {
    "form":"FORM_B",
    "batch_id":5,
    "subscription":"FULL",
    "rows":25
}
```

### Reading Logs

```bash
# View recent logs
tail -f storage/logs/laravel.log

# Filter for preview logs
grep "Compliance Preview" storage/logs/laravel.log

# Filter for errors
grep "Preview Error" storage/logs/laravel.log
```

### Custom Logging

```php
// In controller or service
Log::info('Custom Preview Log', [
    'form' => $formCode,
    'tenant_id' => $tenantId,
    'branch_id' => $branchId,
    'month' => $month,
    'year' => $year,
    'subscription' => $subscription,
    'data_rows' => count($data['rows'] ?? []),
    'timestamp' => now()->toIso8601String(),
]);
```

---

## Performance Optimization

### Caching Example

```php
<?php

namespace App\Services;

use App\Compliance\ComplianceDataService;
use Illuminate\Support\Facades\Cache;

class CachedComplianceDataService
{
    public function __construct(private ComplianceDataService $dataService)
    {}

    public function buildFormData(
        string $formCode,
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): array {
        $cacheKey = "compliance_form_{$formCode}_{$tenantId}_{$branchId}_{$month}_{$year}";
        
        return Cache::remember($cacheKey, 3600, function () use (
            $formCode,
            $tenantId,
            $branchId,
            $month,
            $year
        ) {
            return $this->dataService->buildFormData(
                $formCode,
                $tenantId,
                $branchId,
                $month,
                $year
            );
        });
    }
}
```

---

## Troubleshooting Guide

### Issue: 404 Template Not Found

```php
// Check if template exists
if (!view()->exists('compliance.forms.form_b')) {
    // Create the template
    // File: resources/views/compliance/forms/form_b.blade.php
}

// Verify FormRegistry has correct template path
$template = FormRegistry::getTemplate('FORM_B');
// Should return: 'compliance.forms.form_b'
```

### Issue: Empty Data with FULL Subscription

```php
// Check if builder is registered
if (!FormRegistry::isRegistered('FORM_B')) {
    // Add to FormRegistry
}

// Check if builder returns data
$builder = new WageRegisterBuilder(...);
$data = $builder->build($tenantId, $branchId, $month, $year);
// Should have 'rows' or 'entries' key
```

### Issue: Slow Performance

```php
// Check database queries
DB::enableQueryLog();
$data = $this->dataService->buildFormData(...);
dd(DB::getQueryLog());

// Optimize repositories
// Add indexes to frequently queried columns
// Use eager loading for relationships
```

---

## Summary

The Universal Preview System provides:
- ✅ Single controller for all forms
- ✅ Automatic template detection
- ✅ Subscription-aware data fetching
- ✅ Comprehensive error handling
- ✅ Debug logging
- ✅ Easy integration
- ✅ Scalable architecture
- ✅ Production-ready code

**Ready for implementation and testing!**
