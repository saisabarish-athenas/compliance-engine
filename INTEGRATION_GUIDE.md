# Certification Engine Integration Guide

## Quick Start

### Step 1: Run Migration
```bash
php artisan migrate
```

This creates the `compliance_certification_logs` table.

### Step 2: Automatic Certification on Inspection Pack Download

The certification is **automatically triggered** when downloading inspection pack. No manual integration needed.

```php
// Already integrated in ComplianceExecutionController::downloadInspectionPack()
```

### Step 3: Manual Certification (Optional)

To manually certify a batch:

```javascript
// Frontend - Trigger certification
fetch(`/compliance/batch/${batchId}/certify`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => {
    if (data.certified) {
        alert(`✅ Certified! Score: ${data.score}%`);
    } else {
        alert(`❌ Not Certified. Score: ${data.score}%`);
        console.log('Violations:', data.violations);
    }
});
```

### Step 4: Display Certification Status

```javascript
// Check certification status
fetch(`/compliance/batch/${batchId}/certification-status`)
.then(response => response.json())
.then(data => {
    if (data.certified) {
        document.getElementById('cert-badge').innerHTML = 
            `<span class="badge badge-success">✅ Certified ${data.score}%</span>`;
    } else {
        document.getElementById('cert-badge').innerHTML = 
            `<span class="badge badge-danger">❌ Not Certified ${data.score}%</span>`;
    }
});
```

## Configuration

### Add New Form Rules

Edit `config/tn_statutory_rules.php`:

```php
'YOUR_FORM_CODE' => [
    'official_title' => 'Official Form Title',
    'mandatory_headers' => ['field1', 'field2'],
    'required_row_fields' => ['employee_name', 'wages'],
    'column_sequence' => ['sl_no', 'name', 'wages'],
    'date_fields' => ['payment_date'],
    'min_wage' => 450,
    'esi_rate' => 0.75,
    'epf_rate' => 12,
    'check_child_labour' => true,
    'overtime_multiplier' => 2,
],
```

## Validation Rules Reference

### Structural Validation
- `official_title`: Exact form title
- `mandatory_headers`: Required header fields
- `required_sections`: Required form sections
- `column_sequence`: Exact column order
- `date_fields`: Fields requiring dd-mm-yyyy format
- `register_number_format`: Regex for register number
- `establishment_fields`: Required establishment details

### Legal Validation
- `required_row_fields`: Mandatory row fields
- `min_wage`: Minimum wage threshold (₹)
- `overtime_multiplier`: Overtime rate multiplier (default: 2)
- `esi_rate`: ESI contribution % (default: 0.75)
- `epf_rate`: EPF contribution % (default: 12)
- `check_child_labour`: Enable age validation (>= 14)
- `gender_required`: Require gender field
- `wage_threshold`: Wage threshold for special compliance

### Layout Validation
- `column_sequence`: Enforces column order
- `tn_sequence_order`: TN statutory section order
- `layout_template`: Required layout sections

## Error Handling

### Handle Certification Failure

```php
try {
    $result = $certificationService->certifyBatch($batchId);
    
    if (!$result['certified']) {
        // Log violations
        Log::warning('Certification failed', [
            'batch_id' => $batchId,
            'score' => $result['score'],
            'violations' => $result['violations']
        ]);
        
        // Notify user
        return response()->json([
            'error' => 'Batch not certified',
            'violations' => $result['violations']
        ], 422);
    }
} catch (\Exception $e) {
    Log::error('Certification error', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Certification failed'], 500);
}
```

## Dashboard Integration

### Show Certification Badge

Add to your batch list view:

```blade
@if($batch->certification_score)
    @if($batch->certification_score == 100)
        <span class="badge badge-success">✅ Certified</span>
    @else
        <span class="badge badge-warning">⚠️ Score: {{ $batch->certification_score }}%</span>
    @endif
@else
    <span class="badge badge-secondary">Not Certified</span>
@endif
```

### Show Violations

```blade
@if($violations)
    <div class="alert alert-danger">
        <h5>Violations Found:</h5>
        <ul>
            @foreach($violations as $violation)
                <li>
                    <strong>{{ $violation['form_code'] ?? 'General' }}</strong>: 
                    {{ $violation['message'] }}
                    <small class="text-muted">({{ $violation['field'] }})</small>
                </li>
            @endforeach
        </ul>
    </div>
@endif
```

## Testing

### Unit Test Example

```php
use Tests\TestCase;
use App\Services\Compliance\Validation\ComplianceCertificationService;

class CertificationTest extends TestCase
{
    public function test_certification_passes_with_valid_data()
    {
        $service = app(ComplianceCertificationService::class);
        $result = $service->certifyBatch(1);
        
        $this->assertTrue($result['certified']);
        $this->assertEquals(100, $result['score']);
        $this->assertEmpty($result['violations']);
    }
    
    public function test_certification_fails_with_violations()
    {
        $service = app(ComplianceCertificationService::class);
        $result = $service->certifyBatch(2);
        
        $this->assertFalse($result['certified']);
        $this->assertLessThan(100, $result['score']);
        $this->assertNotEmpty($result['violations']);
    }
}
```

## Performance Optimization

### Cache Certification Results

```php
use Illuminate\Support\Facades\Cache;

$cacheKey = "certification_batch_{$batchId}";

$result = Cache::remember($cacheKey, 3600, function() use ($batchId, $service) {
    return $service->certifyBatch($batchId);
});

// Clear cache when batch is modified
Cache::forget($cacheKey);
```

## Troubleshooting

### Issue: Certification always fails
**Solution**: Check if TN statutory rules are configured for all forms in the batch.

### Issue: Cross-form validation errors
**Solution**: Ensure employee IDs are consistent across all forms.

### Issue: Computation errors
**Solution**: Verify wage calculation formulas match TN statutory requirements.

### Issue: Date format errors
**Solution**: Ensure all dates are in dd-mm-yyyy format, not yyyy-mm-dd.

## Support

Check logs in:
- `storage/logs/laravel.log`
- `compliance_certification_logs` table
- Browser console for frontend errors

## Next Steps

1. ✅ Run migration
2. ✅ Test with existing batch
3. ✅ Configure form rules
4. ✅ Add UI elements
5. ✅ Deploy to production
