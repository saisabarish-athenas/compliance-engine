# Manual Compliance Batch System - Quick Reference

## Files Created

```
app/Services/ManualComplianceLoader.php
app/Http/Controllers/ManualComplianceController.php
routes/compliance.php (updated)
```

## API Endpoint

```
POST /compliance/manual-batch/create
```

## Request

```json
{
    "tenant_id": 1,
    "branch_id": 1,
    "month": 3,
    "year": 2024
}
```

## Response

```json
{
    "success": true,
    "batch_id": 123,
    "message": "Batch created and manual compliances loaded"
}
```

## Frequency Logic

| Frequency | Condition |
|-----------|-----------|
| monthly | Always included |
| quarterly | Included when due_month in (3,6,9,12) AND due_month <= current month |
| annual | Included when due_month = current month |
| event | Always included |

## Usage Examples

### Via API

```bash
curl -X POST http://localhost/compliance/manual-batch/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "tenant_id": 1,
    "branch_id": 1,
    "month": 3,
    "year": 2024
  }'
```

### Via Tinker

```php
php artisan tinker

# Create batch
$batch = \App\Models\ComplianceExecutionBatch::create([
    'tenant_id' => 1,
    'branch_id' => 1,
    'period_month' => 3,
    'period_year' => 2024,
    'status' => 'pending',
    'created_by' => 1,
]);

# Load compliances
app(\App\Services\ManualComplianceLoader::class)->load($batch);

# Verify
\App\Models\ManualComplianceBatchItem::where('batch_id', $batch->id)->count();
```

### Via Controller

```php
use App\Http\Controllers\ManualComplianceController;

$controller = app(ManualComplianceController::class);
$response = $controller->createBatch($request);
```

## Database Tables

### Input: compliance_manual_master
- id
- compliance_name
- act_name
- frequency (monthly, quarterly, annual, event)
- due_month
- requires_document
- is_event_based

### Output: compliance_manual_batch_items
- id
- batch_id
- tenant_id
- branch_id
- compliance_id
- status (pending)
- document_path (null)
- remarks (null)
- created_at
- updated_at

## Key Features

✅ Automatic compliance loading
✅ Frequency-based filtering
✅ Multi-tenant safe
✅ Branch-level isolation
✅ Batch insert for performance
✅ Request validation
✅ Clean architecture

## Validation Rules

```
tenant_id  → required, integer, exists:tenants,id
branch_id  → required, integer, exists:branches,id
month      → required, integer, min:1, max:12
year       → required, integer, min:2000
```

## Error Responses

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "tenant_id": ["The tenant_id field is required."]
    }
}
```

## Testing Checklist

- [ ] Service created
- [ ] Controller created
- [ ] Route added
- [ ] Batch created successfully
- [ ] Items inserted correctly
- [ ] Frequency logic working
- [ ] Multi-tenant safety verified
- [ ] Status set to 'pending'
- [ ] No data leakage

## Performance

- Single query for compliances
- Batch insert for items
- Indexed lookups
- No N+1 queries

## Security

- Authentication required
- Tenant isolation enforced
- Branch filtering applied
- Request validation
- No SQL injection

## Troubleshooting

**No items inserted?**
- Check compliance_manual_master has records
- Verify frequency values
- Check due_month values

**Wrong items?**
- Verify frequency logic
- Check due_month values
- Verify month comparison

**Multi-tenant issues?**
- Verify tenant_id passed
- Check batch creation
- Verify item inheritance

## Next Steps

1. Deploy files
2. Test API endpoint
3. Verify batch creation
4. Monitor logs
5. Gather feedback

---

**Ready to use!** 🚀
