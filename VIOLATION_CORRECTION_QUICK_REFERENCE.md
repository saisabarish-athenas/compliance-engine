# Violation Correction Engine - Quick Reference

## 🚀 Quick Start

### User Flow
1. User views batch audit details
2. Clicks "Fix & Re-Audit" button on failed form
3. System auto-fetches missing data
4. If needed, modal shows for manual input
5. User submits corrections
6. System regenerates PDF and replaces old file
7. Audit log updated with new score
8. UI updates in real-time

---

## 📁 Files Modified/Created

### Created:
- `app/Services/Compliance/Audit/ComplianceCorrectionService.php`
- `VIOLATION_CORRECTION_ENGINE.md`

### Modified:
- `app/Http/Controllers/ComplianceExecutionController.php`
- `routes/compliance.php`
- `resources/views/compliance/dashboard.blade.php`

---

## 🔧 Key Methods

### ComplianceCorrectionService

```php
// Auto-fix violations
public function fixFormViolations(int $batchId, string $formCode): array

// Fix with user input
public function fixWithUserInput(int $batchId, string $formCode, array $userInput): array

// Auto-fetch field value
private function autoFetchFieldValue(string $field, ComplianceExecutionBatch $batch, string $formCode)

// Regenerate and audit
private function regenerateAndAudit(ComplianceExecutionBatch $batch, string $formCode, array $correctionData): array
```

### Controller Methods

```php
// Fix violations endpoint
public function fixViolations(int $batchId, string $formCode)

// Submit fix endpoint
public function submitFix(Request $request, int $batchId, string $formCode)
```

---

## 🌐 API Endpoints

```
POST /compliance/batch/{batch}/fix-violations/{form}
POST /compliance/batch/{batch}/submit-fix/{form}
```

---

## 📊 Response Formats

### Success Response:
```json
{
    "status": "success",
    "form_code": "FORM_B",
    "form_score": 95,
    "batch_average_score": 88,
    "audit_status": "passed",
    "violations": [],
    "confidence_label": "Inspection Ready",
    "file_path": "compliance/batch_123/FORM_B_corrected_1234567890.pdf"
}
```

### Requires Input Response:
```json
{
    "status": "requires_input",
    "missing_fields": [
        {
            "field": "establishment_name",
            "message": "Missing required header field: establishment_name",
            "type": "header"
        }
    ],
    "auto_fixed": {
        "period_month": 12,
        "period_year": 2024
    }
}
```

### Error Response:
```json
{
    "status": "error",
    "message": "Generator not found"
}
```

---

## 🎯 Auto-Fetch Data Sources

| Field | Source | Table | Column |
|-------|--------|-------|--------|
| establishment_name | Tenant | tenants | establishment_name / name |
| factory_license_no | Tenant | tenants | factory_license_no |
| pf_code | Tenant | tenants | pf_code |
| esi_code | Tenant | tenants | esi_code |
| unit_name | Branch | branches | unit_name / branch_name |
| address | Branch | branches | address |
| factory_license_number | Branch | branches | factory_license_number |
| period_month | Batch | - | $batch->period_month |
| period_year | Batch | - | $batch->period_year |

---

## 🔄 Workflow Diagram

```
User Click
    ↓
fixViolations()
    ↓
autoFetchFieldValue()
    ↓
All Found? ──Yes──→ regenerateAndAudit()
    ↓                       ↓
    No                  Generate PDF
    ↓                       ↓
Return missing_fields   Replace File
    ↓                       ↓
Show Modal              Update Audit Log
    ↓                       ↓
User Input              Recalculate Score
    ↓                       ↓
submitFix()             Return Success
    ↓                       ↓
regenerateAndAudit()    Update UI
```

---

## 💾 Database Operations

### Update Audit Log:
```php
ComplianceAuditLog::updateOrCreate(
    ['tenant_id' => $tenantId, 'batch_id' => $batchId, 'form_code' => $formCode],
    ['audit_score' => $score, 'status' => $status, 'violations' => $violations]
);
```

### Update Batch Score:
```php
$avgScore = ComplianceAuditLog::where('batch_id', $batchId)->avg('audit_score');
$batch->update(['audit_score' => round($avgScore)]);
```

### Update Batch Form:
```php
$batchForm->update(['file_path' => $newPath, 'status' => 'success']);
```

---

## 🎨 Frontend Functions

### Show Fix Modal:
```javascript
showFixModal(batchId, formCode, missingFields, originalBtn)
```

### Update UI:
```javascript
updateAuditUI(batchId, formCode, data, btn)
```

### Handle Fix Button Click:
```javascript
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('re-audit-btn')) {
        // Trigger fix violations
    }
});
```

---

## 🧪 Testing Commands

### Test Auto-Fix:
```bash
curl -X POST http://localhost/compliance/batch/1/fix-violations/FORM_B \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}"
```

### Test Submit Fix:
```bash
curl -X POST http://localhost/compliance/batch/1/submit-fix/FORM_B \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}" \
  -d '{"corrections":{"establishment_name":"ABC Ltd"}}'
```

---

## 🐛 Common Issues

### Issue: "Generator not found"
**Solution:** Check if form code exists in FormGeneratorFactory

### Issue: "Batch form record not found"
**Solution:** Ensure form was generated before attempting fix

### Issue: Modal not showing
**Solution:** Verify Bootstrap 5 is loaded

### Issue: UI not updating
**Solution:** Check browser console for JavaScript errors

---

## 📝 Code Snippets

### Add New Auto-Fetch Source:
```php
// In autoFetchFieldValue() method
if ($field === 'new_field') {
    $data = DB::table('new_table')
        ->where('tenant_id', $batch->tenant_id)
        ->value('new_column');
    return $data;
}
```

### Add Custom Validation:
```php
// In regenerateAndAudit() method
if (empty($correctionData['required_field'])) {
    throw new \Exception('Required field missing');
}
```

### Customize Modal:
```javascript
// In showFixModal() function
modal.innerHTML = `
    <div class="modal-dialog modal-lg">
        <!-- Custom modal content -->
    </div>
`;
```

---

## 🔐 Security Checklist

- ✅ CSRF token validation
- ✅ Tenant ownership verification
- ✅ Input sanitization
- ✅ File path validation
- ✅ Authorization checks

---

## 📈 Performance Tips

1. **Cache tenant/branch data** for repeated lookups
2. **Use queue jobs** for bulk corrections
3. **Optimize PDF generation** with memory limits
4. **Index database columns** used in auto-fetch
5. **Implement rate limiting** on fix endpoints

---

## 🎓 Best Practices

1. Always validate user input before regeneration
2. Log all correction attempts for audit trail
3. Use transactions for database updates
4. Handle file deletion errors gracefully
5. Provide clear error messages to users
6. Test with various violation scenarios
7. Monitor memory usage during PDF generation

---

## 📞 Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review documentation: `VIOLATION_CORRECTION_ENGINE.md`
3. Test endpoints with Postman/curl
4. Verify database records manually

---

## 🚦 Status Codes

| Status | Meaning |
|--------|---------|
| success | Correction completed successfully |
| requires_input | User input needed for missing fields |
| no_violations | No violations found to fix |
| error | Error occurred during correction |

---

## 🎯 Next Steps

1. Test with real batch data
2. Monitor correction success rate
3. Gather user feedback
4. Optimize auto-fetch logic
5. Add more data sources
6. Implement bulk correction
7. Add correction history tracking

---

## 📚 Related Documentation

- `VIOLATION_CORRECTION_ENGINE.md` - Full implementation guide
- `AUTO_REAUDIT_IMPLEMENTATION.md` - Re-audit system
- `SENIOR_AUDITOR_COMPLIANCE_VALIDATION_REPORT.md` - Audit system

---

## ✨ Features Summary

✅ Automated violation correction
✅ Smart data auto-fetch
✅ User input modal for missing data
✅ PDF regeneration and replacement
✅ Audit log updates
✅ Batch score recalculation
✅ Real-time UI updates
✅ Error handling and logging
✅ Clean architecture
✅ No schema changes required
