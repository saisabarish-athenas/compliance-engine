# Simplified Batch Workflow - Quick Reference

## Quick Start

### For Users
1. Go to: `/compliance/batch/create-simplified`
2. Select Month and Year
3. Click "Create Batch"
4. Select data source method for each form
5. Click "Proceed"
6. Enter data or upload files
7. Click "Proceed to Generation"

### For Developers

#### Access the Feature
```
Route: /compliance/batch/create-simplified
Controller: SimplifiedBatchController
```

#### Filter Forms by Frequency
```php
$filterService = app(FormFrequencyFilterService::class);
$forms = $filterService->getApplicableFormsForMonth(6, 2024);
```

#### Create Batch Programmatically
```php
$controller = app(SimplifiedBatchController::class);
$request = Request::create('/compliance/batch/create-simplified', 'POST', [
    'period_month' => 6,
    'period_year' => 2024
]);
$controller->store($request);
```

## Form Frequency Rules

| Month | Forms Included |
|-------|----------------|
| January | Monthly only |
| February | Monthly only |
| March | Monthly + Quarterly |
| April | Monthly only |
| May | Monthly only |
| June | Monthly + Quarterly + Half-yearly |
| July | Monthly only |
| August | Monthly only |
| September | Monthly + Quarterly |
| October | Monthly only |
| November | Monthly only |
| December | Monthly + Quarterly + Half-yearly + Yearly |

## Data Entry Methods

### Manual Filling
- User enters data in form
- Data stored in `compliance_manual_data`
- Used by existing generators

### Upload PDF
- User uploads PDF file
- Stored in `storage/app/compliance/uploads/`
- Attached to batch without processing

### Upload CSV
- User uploads CSV file
- Parsed and stored
- Passed to existing generators

## Files Created

| File | Purpose |
|------|---------|
| `FormFrequencyFilterService.php` | Filter forms by frequency |
| `SimplifiedBatchController.php` | Main controller |
| `simplified-batch-create.blade.php` | Create batch UI |
| `simplified-batch-show.blade.php` | Form selection UI |
| `simplified-batch-data-entry.blade.php` | Data entry UI |

## Routes

| Method | Route | Name |
|--------|-------|------|
| GET | `/compliance/batch/create-simplified` | `compliance.simplified-batch.create` |
| POST | `/compliance/batch/create-simplified` | `compliance.simplified-batch.store` |
| POST | `/compliance/batch/get-applicable-forms` | `compliance.simplified-batch.get-forms` |
| GET | `/compliance/batch/{id}/show-simplified` | `compliance.simplified-batch.show` |
| GET | `/compliance/batch/{id}/download-template/{formCode}` | `compliance.simplified-batch.download-template` |
| GET | `/compliance/batch/{id}/data-entry` | `compliance.simplified-batch.data-entry` |
| POST | `/compliance/batch/{id}/proceed` | `compliance.simplified-batch.proceed` |

## Database Tables

| Table | Usage |
|-------|-------|
| `compliance_forms_master` | Form definitions with frequency |
| `compliance_execution_batches` | Batch records |
| `compliance_batch_forms` | Batch-form relationships |
| `compliance_manual_data` | Manual data entry |
| `compliance_manual_uploads` | File uploads |

## Key Classes

### FormFrequencyFilterService
```php
namespace App\Services\Compliance;

class FormFrequencyFilterService {
    public function getApplicableFormsForMonth(int $month, int $year): array
}
```

### SimplifiedBatchController
```php
namespace App\Http\Controllers\Compliance;

class SimplifiedBatchController {
    public function create()
    public function getApplicableForms(Request $request)
    public function store(Request $request)
    public function show(int $batchId)
    public function downloadTemplate(int $batchId, string $formCode)
    public function dataEntry(int $batchId)
    public function proceed(Request $request, int $batchId)
}
```

## Validation Rules

### Create Batch
- `period_month`: required, integer, 1-12
- `period_year`: required, integer, 2020-2030

### Get Forms
- `month`: required, integer, 1-12
- `year`: required, integer, 2020-2030

### Data Entry
- `form_data`: required, array
- File uploads: max 10MB, PDF/CSV only

## Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| No forms applicable | No forms match frequency | Select different month |
| Template not found | Blade file missing | Check form code |
| Batch not found | Invalid batch ID | Create new batch |
| File too large | Upload > 10MB | Reduce file size |

## Testing Checklist

- [ ] Create batch for January (monthly only)
- [ ] Create batch for March (monthly + quarterly)
- [ ] Create batch for June (monthly + quarterly + half-yearly)
- [ ] Create batch for December (all frequencies)
- [ ] Download template for each form
- [ ] Upload PDF file
- [ ] Upload CSV file
- [ ] Enter manual data
- [ ] Verify data stored correctly
- [ ] Generate forms successfully

## Performance Notes

- Form filtering: O(n) where n = total forms
- Batch creation: Uses existing service (optimized)
- Template download: File I/O only
- Data entry: Minimal database operations

## Backward Compatibility

✅ Existing batch creation still works
✅ Existing generators unchanged
✅ No breaking changes
✅ Can run alongside old workflow

## Next Steps

1. Test the workflow end-to-end
2. Verify form filtering for each month
3. Test data entry methods
4. Verify integration with generators
5. Deploy to production

## Support

For issues:
1. Check `SIMPLIFIED_BATCH_WORKFLOW.md` for detailed docs
2. Review application logs
3. Verify database schema
4. Check file permissions
