# Integration Guide: Using Form Data Architecture in ComplianceExecutionService

## Current State

The `ComplianceExecutionService` now has access to `ComplianceDataService` through dependency injection.

## How to Integrate

### Step 1: Use ComplianceDataService in Form Generation

In `ComplianceExecutionService::processBatch()`, before PDF generation:

```php
// Get the form data using the new architecture
$data = $this->dataService->buildFormData(
    $form->form_code,
    $tenantId,
    $branchId,
    $month,
    $year
);

// Pass to PDF generator
$pdfContent = $generator->generate($tenantId, $branchId, $month, $year, $batchId, $data);
```

### Step 2: Update Form Generators

Modify form generators to accept and use the data:

```php
class BaseFormGenerator
{
    public function generate(
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        int $batchId,
        array $data = []
    ): string {
        // If no data provided, build it
        if (empty($data)) {
            $data = $this->dataService->buildFormData(
                $this->formCode,
                $tenantId,
                $branchId,
                $month,
                $year
            );
        }

        // Use data in PDF generation
        return view('compliance.pdf.' . $this->formCode, compact('data'))->render();
    }
}
```

### Step 3: Update Blade Templates

Templates now receive structured data:

```blade
@if($data['status'] === 'NIL')
    <div class="form-section">
        <p class="text-center font-bold">NIL</p>
    </div>
@else
    <table class="form-table">
        <thead>
            <tr>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['entries'] as $entry)
                <tr>
                    <td>{{ $entry['employee_code'] ?? 'N/A' }}</td>
                    <td>{{ $entry['employee_name'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($entry['gross_salary'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($entry['total_deductions'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($entry['net_salary'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="form-totals">
        <p>Total Gross: {{ number_format($data['total_gross'] ?? 0, 2) }}</p>
        <p>Total Deductions: {{ number_format($data['total_deductions'] ?? 0, 2) }}</p>
        <p>Total Net: {{ number_format($data['total_net'] ?? 0, 2) }}</p>
    </div>
@endif
```

## Complete Integration Example

### Updated ComplianceExecutionService

```php
<?php

namespace App\Services\Compliance;

use App\Compliance\ComplianceDataService;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;

class ComplianceExecutionService
{
    public function __construct(
        private ComplianceEngine $engine,
        private ComplianceTimelineService $timelineService,
        private ComplianceAuditService $auditService,
        private ComplianceDataService $dataService
    ) {}

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::with('section')->findOrFail($batchId);
        $tenantId = $batch->tenant_id;
        $branchId = $batch->branch_id ?? 1;
        $formIds = $batch->form_ids;

        $month = \Carbon\Carbon::parse($batch->period_from)->month;
        $year = \Carbon\Carbon::parse($batch->period_from)->year;

        $results = [];
        $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);

        foreach ($formIds as $formId) {
            try {
                $form = ComplianceFormsMaster::findOrFail($formId);

                // BUILD DATA USING NEW ARCHITECTURE
                $data = $this->dataService->buildFormData(
                    $form->form_code,
                    $tenantId,
                    $branchId,
                    $month,
                    $year
                );

                logger("Data built for {$form->form_code}", ['has_entries' => isset($data['entries'])]);

                // GENERATE PDF WITH DATA
                $generator = $factory::make($form->form_code);
                if (!$generator) {
                    $results[$formId] = ['success' => false, 'error' => 'No generator'];
                    continue;
                }

                $pdfContent = $generator->generate(
                    $tenantId,
                    $branchId,
                    $month,
                    $year,
                    $batchId,
                    $data  // PASS DATA TO GENERATOR
                );

                if (!is_string($pdfContent) || strlen($pdfContent) === 0) {
                    logger()->warning("Empty PDF for {$form->form_code}");
                    continue;
                }

                // STORE FILE
                $directory = "generated_forms/{$tenantId}/{$batchId}";
                \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($directory);

                $fileName = "{$form->form_code}.pdf";
                $filePath = "{$directory}/{$fileName}";

                \Illuminate\Support\Facades\Storage::disk('local')->put($filePath, $pdfContent);

                // LOG SUCCESS
                \App\Models\ComplianceBatchForm::create([
                    'tenant_id' => $tenantId,
                    'batch_id' => $batchId,
                    'form_code' => $form->form_code,
                    'section' => $form->section->section_name ?? 'General',
                    'file_path' => $filePath,
                    'status' => 'success',
                ]);

                $results[$formId] = [
                    'success' => true,
                    'form_code' => $form->form_code,
                    'file_path' => $filePath,
                    'status' => 'Generated',
                ];

            } catch (\Exception $e) {
                logger()->error("Form generation failed", [
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage(),
                ]);

                $results[$formId] = [
                    'success' => false,
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $finalStatus = $successCount === count($results) ? 'completed' : 'partially_completed';

        $batch->update([
            'status' => $finalStatus,
            'processed_at' => now(),
            'results' => $results,
        ]);

        return $results;
    }
}
```

## Testing the Integration

```php
// In tests/Feature/ComplianceDataServiceTest.php

namespace Tests\Feature;

use App\Compliance\ComplianceDataService;
use Tests\TestCase;

class ComplianceDataServiceTest extends TestCase
{
    public function test_wage_register_builder()
    {
        $dataService = app(ComplianceDataService::class);

        $data = $dataService->buildFormData(
            'FORM_B',
            1,  // tenant_id
            1,  // branch_id
            1,  // month
            2024  // year
        );

        $this->assertIsArray($data);
        $this->assertTrue(
            isset($data['entries']) || $data['status'] === 'NIL'
        );
    }

    public function test_nil_handling()
    {
        $dataService = app(ComplianceDataService::class);

        // For a period with no data
        $data = $dataService->buildFormData(
            'FORM_B',
            999,  // non-existent tenant
            999,  // non-existent branch
            12,
            2020
        );

        $this->assertEquals('NIL', $data['status']);
    }

    public function test_render_form()
    {
        $dataService = app(ComplianceDataService::class);

        $html = $dataService->renderForm(
            'FORM_B',
            1,
            1,
            1,
            2024
        );

        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }
}
```

## Migration Path

### Phase 1: Parallel Implementation
- Keep existing generators working
- Add new data service alongside
- Test both paths

### Phase 2: Gradual Migration
- Migrate one form at a time
- Test thoroughly
- Monitor performance

### Phase 3: Full Cutover
- Remove old data fetching code
- Use only new architecture
- Decommission old code

## Benefits of Integration

1. **Cleaner Code**: Separation of concerns
2. **Reusability**: Data service used everywhere
3. **Testability**: Easy to mock and test
4. **Maintainability**: Changes in one place
5. **Performance**: Optimized queries
6. **Scalability**: Handles 1000+ tenants

## Troubleshooting

### Issue: Data not appearing in forms

**Solution**: Check that:
1. Builder is registered in FormRegistry
2. Repository queries return data
3. Template uses correct data keys
4. Tenant/branch IDs are correct

### Issue: NIL appearing when data exists

**Solution**: Check that:
1. Data exists in database for period
2. Tenant/branch filters are correct
3. Date fields are populated
4. Builder logic is correct

### Issue: Performance degradation

**Solution**: Check that:
1. Queries use eager loading
2. Database indexes exist
3. No N+1 queries
4. Aggregations use database functions

## Next Steps

1. Create Blade templates for all 36 forms
2. Implement remaining builders
3. Run integration tests
4. Deploy to staging
5. Performance testing
6. Production deployment

---

**Integration Status**: Ready for implementation
**Estimated Time**: 4-6 hours for full integration
**Risk Level**: Low (backward compatible)
