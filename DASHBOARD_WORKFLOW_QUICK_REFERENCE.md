# QUICK REFERENCE - NEW DASHBOARD WORKFLOW

## For Developers

### Creating a Batch Programmatically

```php
use App\Services\Compliance\BatchOrchestrator;

$orchestrator = app(BatchOrchestrator::class);
$batch = $orchestrator->createBatch(
    tenantId: 1,
    month: 3,      // March
    year: 2024
);

echo "Batch created: " . $batch->id;
```

### Getting Applicable Forms for a Month

```php
use App\Services\Compliance\FrequencyEngine;

$engine = app(FrequencyEngine::class);
$forms = $engine->getApplicableForms(month: 3);

foreach ($forms as $form) {
    echo $form->form_code . " - " . $form->form_name . "\n";
}
```

### Checking Form Status

```php
$batchForm = \App\Models\ComplianceBatchForm::find($id);

if ($batchForm->isPending()) {
    echo "Form not yet generated";
}

if ($batchForm->isGenerated()) {
    echo "Form ready: " . $batchForm->file_path;
}
```

### Updating Form After Generation

```php
$batchForm = \App\Models\ComplianceBatchForm::find($id);
$batchForm->updateFilePath(
    filePath: 'generated_forms/1/123/FormB.pdf',
    status: 'success'
);
```

---

## Frequency Rules Quick Reference

| Frequency | Months | Example |
|-----------|--------|---------|
| Monthly | 1-12 | Muster Roll |
| Quarterly | 3,6,9,12 | Quarterly Return |
| Half-Yearly | 6,12 | Half-yearly Report |
| Yearly | 12 | Annual Return |
| Event | Manual | Incident Report |

---

## Database Queries

### Get All Forms for a Batch

```sql
SELECT * FROM compliance_batch_forms 
WHERE batch_id = 123 
AND tenant_id = 1;
```

### Get Pending Forms

```sql
SELECT * FROM compliance_batch_forms 
WHERE batch_id = 123 
AND status = 'pending';
```

### Get Generated Forms

```sql
SELECT * FROM compliance_batch_forms 
WHERE batch_id = 123 
AND status = 'success' 
AND file_path NOT LIKE '%pending%';
```

### Get Forms by Frequency

```sql
SELECT * FROM compliance_forms_master 
WHERE frequency = 'Monthly' 
AND is_active = 1;
```

---

## Common Issues & Solutions

### Issue: No forms detected for month

**Cause:** Frequency column has different case
**Solution:** FrequencyEngine handles case-insensitive matching

### Issue: file_path is NULL

**Cause:** Old code inserting NULL values
**Solution:** Migration sets default pending placeholder

### Issue: Forms not updating after generation

**Cause:** ComplianceOrchestrator not updating batch_forms
**Solution:** Updated executeBatch() to update file_path

### Issue: Multi-tenant data leakage

**Cause:** Missing tenant_id filter
**Solution:** All queries enforce tenant_id filtering

---

## Testing Commands

### Create test batch
```bash
php artisan tinker
>>> $orchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
>>> $batch = $orchestrator->createBatch(1, 3, 2024);
>>> $batch->id
```

### Check forms attached
```bash
>>> $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)->get();
>>> $forms->count()
>>> $forms->first()->file_path
```

### Check frequency matching
```bash
>>> $engine = app(\App\Services\Compliance\FrequencyEngine::class);
>>> $forms = $engine->getApplicableForms(3);
>>> $forms->count()
```

---

## File Paths

| Component | Path |
|-----------|------|
| FrequencyEngine | `app/Services/Compliance/FrequencyEngine.php` |
| BatchOrchestrator | `app/Services/Compliance/BatchOrchestrator.php` |
| Controller | `app/Http/Controllers/ComplianceExecutionController.php` |
| Orchestrator | `app/Services/Compliance/ComplianceOrchestrator.php` |
| Model | `app/Models/ComplianceBatchForm.php` |
| Migration | `database/migrations/2026_03_20_000012_fix_batch_forms_file_path.php` |

---

## API Endpoints

### Create Batch
```
POST /compliance/batch/create
Parameters:
  - period_month: integer (1-12)
  - period_year: integer (2020-2030)
```

### Preview Form
```
GET /compliance/batch/{batch}/preview/{form}
```

### Process Batch
```
POST /compliance/batch/{id}/process
```

### Download Inspection Pack
```
GET /compliance/batch/{id}/download
```

---

## Key Takeaways

✅ **Simplified UI:** Users select Month + Year only
✅ **Auto Detection:** Forms detected by frequency rules
✅ **Clean Architecture:** Dedicated services for each concern
✅ **File Tracking:** Pending → Generated file path updates
✅ **Multi-Tenant Safe:** Tenant filtering at all levels
✅ **Backward Compatible:** All existing systems work unchanged

---

## Support

For issues or questions:
1. Check DASHBOARD_WORKFLOW_REFACTORING.md for detailed docs
2. Review test commands above
3. Check database queries for data verification
4. Verify tenant_id filtering in all queries
