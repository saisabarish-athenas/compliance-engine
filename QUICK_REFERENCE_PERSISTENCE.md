# QUICK REFERENCE: GUARANTEED PERSISTENCE

## SUBSCRIPTION CHECK (Use Everywhere)

```php
$subscription = auth()->user()->tenant->subscription_type ?? '';
$isFull = strtoupper(trim($subscription)) === 'FULL';
```

---

## PERSISTENCE FLOW (FULL Subscription)

### 1. Generator Returns PDF Content
```php
// BaseFormGenerator.php
if ($isFull) {
    return $pdfOutput; // Binary content
} else {
    return $filePath;  // String path
}
```

### 2. Service Saves & Records
```php
// ComplianceExecutionService.php
if ($isFull) {
    $directory = "generated_forms/{$tenantId}/{$batchId}";
    \Storage::makeDirectory($directory);
    
    $filePath = "{$directory}/{$fileName}";
    \Storage::put($filePath, $pdfContent);
    
    \DB::table('compliance_batch_forms')->updateOrInsert(
        ['tenant_id' => $tenantId, 'batch_id' => $batchId, 'form_code' => $formCode],
        ['section' => $section, 'file_path' => $filePath, 'status' => 'success', 'created_at' => now()]
    );
}
```

### 3. Inspection Pack Reads
```php
// ComplianceExecutionController.php
$forms = \DB::table('compliance_batch_forms')
    ->where('batch_id', $batchId)
    ->where('status', 'success')
    ->get();

foreach ($forms as $form) {
    $fullPath = storage_path('app/' . $form->file_path);
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, basename($form->file_path));
    }
}
```

---

## FILE PATHS

| Subscription | Storage Path                                      |
|--------------|---------------------------------------------------|
| FULL         | `storage/app/generated_forms/{tenant}/{batch}/`   |
| MINIMAL      | `storage/app/compliance/generated/{batch}/`       |

---

## DATABASE QUERIES

### Insert Record (FULL Only)
```php
\DB::table('compliance_batch_forms')->updateOrInsert(
    ['tenant_id' => $tenantId, 'batch_id' => $batchId, 'form_code' => $formCode],
    ['section' => $section, 'file_path' => $filePath, 'status' => 'success', 'created_at' => now()]
);
```

### Read Records (Inspection Pack)
```php
\DB::table('compliance_batch_forms')
    ->where('batch_id', $batchId)
    ->where('status', 'success')
    ->get();
```

---

## ERROR HANDLING

### Inspection Pack
```php
if ($forms->isEmpty()) {
    abort(404, 'No generated forms found for this batch.');
}

if (!$isFull) {
    abort(403, 'Inspection Pack available only for FULL subscription.');
}
```

### File Validation
```php
$fullPath = storage_path('app/' . $form->file_path);
if (file_exists($fullPath)) {
    // Add to ZIP
}
```

---

## CRITICAL RULES

1. ✅ Always use hardened subscription check
2. ✅ Save file BEFORE inserting record
3. ✅ Use `updateOrInsert` to prevent duplicates
4. ✅ Validate file existence in Inspection Pack
5. ✅ No try/catch swallowing in persistence block
6. ❌ Never regenerate PDFs in Inspection Pack
7. ❌ Never use cache for file operations
8. ❌ Never modify MINIMAL subscription flow

---

## TESTING COMMANDS

```bash
# Check storage directories
dir storage\app\generated_forms
dir storage\app\temp

# Check database records
php artisan tinker
>>> DB::table('compliance_batch_forms')->count();
>>> DB::table('compliance_batch_forms')->latest()->first();

# Test FULL subscription
# 1. Login as FULL user
# 2. Create batch
# 3. Process batch
# 4. Check compliance_batch_forms table
# 5. Download Inspection Pack

# Test MINIMAL subscription
# 1. Login as MINIMAL user
# 2. Create batch
# 3. Process batch
# 4. Verify no records in compliance_batch_forms
# 5. Verify Inspection Pack returns 403
```

---

## TROUBLESHOOTING

### Issue: No records in compliance_batch_forms
**Solution:** Check subscription type is exactly 'FULL' (case-sensitive after trim/uppercase)

### Issue: Inspection Pack returns 404
**Solution:** Verify records exist with status='success' for the batch

### Issue: Files not found in ZIP
**Solution:** Check file_path in database matches actual file location

### Issue: MINIMAL users see Inspection Pack button
**Solution:** Add blade condition: `@if($subscription === 'FULL')`

---

## BLADE TEMPLATE (Dashboard)

```blade
@if($subscription === 'FULL' && $batch->display_status === 'Completed')
    <a href="{{ route('compliance.batch.inspectionPack', $batch->id) }}" 
       class="btn btn-sm btn-primary">
        📦 Inspection Pack
    </a>
@endif
```
