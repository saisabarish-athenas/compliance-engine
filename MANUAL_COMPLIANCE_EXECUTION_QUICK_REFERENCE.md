# Manual Compliance Execution Module - Quick Reference

## 🚀 Quick Start

### 1. Create a Batch
```bash
POST /compliance/manual-batch/create
{
  "tenant_id": 1,
  "branch_id": 1,
  "month": 3,
  "year": 2024
}
```

### 2. Get All Compliances
```bash
GET /compliance/manual-batch/{batch_id}
```

### 3. Upload Document
```bash
POST /compliance/manual-item/upload
Form Data:
  - item_id: 45
  - file: <pdf/jpg/jpeg/png, max 5MB>
```

### 4. Skip Compliance
```bash
POST /compliance/manual-item/skip
{
  "item_id": 45
}
```

## 📋 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/compliance/manual-batch/{batch_id}` | Get all compliances in batch |
| POST | `/compliance/manual-item/upload` | Upload document proof |
| POST | `/compliance/manual-item/skip` | Mark as skipped |

## 🔐 Multi-Tenant Safety

All endpoints enforce:
```php
if (auth()->user()->tenant_id !== $resource->tenant_id) {
    abort(403, 'Unauthorized');
}
```

## ✅ Validation Rules

### uploadDocument()
```
item_id: required, integer, exists in compliance_manual_batch_items
file: required, file, mimes:pdf,jpg,jpeg,png, max:5120KB
```

### skipCompliance()
```
item_id: required, integer, exists in compliance_manual_batch_items
```

## 📁 Files Modified/Created

| File | Action | Lines |
|------|--------|-------|
| `app/Http/Controllers/ManualComplianceExecutionController.php` | Created | 80 |
| `routes/compliance.php` | Updated | +3 routes |

## 🧪 Test Commands

### Tinker Test
```bash
php artisan tinker

# Get compliances
>>> $controller = app(\App\Http\Controllers\ManualComplianceExecutionController::class);
>>> $response = $controller->getBatchCompliances(1);
>>> $response->getData();

# Test authorization (should fail)
>>> auth()->user()->tenant_id = 999;
>>> $controller->getBatchCompliances(1);
```

### cURL Test
```bash
# Get compliances
curl -X GET http://localhost/compliance/manual-batch/1 \
  -H "Authorization: Bearer {token}"

# Upload document
curl -X POST http://localhost/compliance/manual-item/upload \
  -H "Authorization: Bearer {token}" \
  -F "item_id=1" \
  -F "file=@document.pdf"

# Skip compliance
curl -X POST http://localhost/compliance/manual-item/skip \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"item_id": 1}'
```

## 📊 Response Examples

### getBatchCompliances()
```json
{
  "batch_id": 12,
  "items": [
    {
      "item_id": 45,
      "compliance_name": "Submission of annual return",
      "act_name": "Factories Act",
      "status": "pending",
      "document_path": null
    }
  ]
}
```

### uploadDocument()
```json
{
  "success": true,
  "message": "Compliance document uploaded successfully"
}
```

### skipCompliance()
```json
{
  "success": true,
  "message": "Compliance marked as skipped"
}
```

## 🔍 Status Values

| Status | Meaning |
|--------|---------|
| pending | Not yet completed |
| completed | Document uploaded |
| skipped | Marked as not applicable |

## ⚠️ Error Responses

| Code | Scenario |
|------|----------|
| 403 | Unauthorized tenant access |
| 404 | Resource not found |
| 422 | Validation failed |
| 500 | Server error |

## 💾 Storage Location

Uploaded documents are stored in:
```
storage/app/public/compliance_documents/
```

Access via:
```
/storage/compliance_documents/{filename}
```

## 🔧 Configuration

### File Upload Limits
- **Max Size:** 5MB
- **Allowed Types:** pdf, jpg, jpeg, png
- **Storage Disk:** public

### Multi-Tenant
- **Enforcement:** Automatic via authorizeForTenant()
- **Scope:** All queries filtered by tenant_id and branch_id

## 📝 Code Structure

```php
class ManualComplianceExecutionController
{
    // Get all compliances in batch
    public function getBatchCompliances(int $batchId): JsonResponse
    
    // Upload document for compliance
    public function uploadDocument(Request $request): JsonResponse
    
    // Mark compliance as skipped
    public function skipCompliance(Request $request): JsonResponse
    
    // Enforce multi-tenant safety
    private function authorizeForTenant(int $tenantId): void
}
```

## 🚀 Deployment

1. Copy controller to `app/Http/Controllers/`
2. Update routes in `routes/compliance.php`
3. Ensure storage directory exists
4. Run `php artisan storage:link`
5. Test with sample data

## 📞 Troubleshooting

**Q: Getting 403 Unauthorized?**
A: Check that authenticated user's tenant_id matches the resource's tenant_id

**Q: File upload fails?**
A: Verify file type (pdf, jpg, jpeg, png) and size (max 5MB)

**Q: Item not found?**
A: Ensure item_id exists and belongs to your tenant

**Q: Storage directory not found?**
A: Run `php artisan storage:link` to create public symlink

---

**Version:** 1.0  
**Status:** Production Ready  
**Last Updated:** 2024
