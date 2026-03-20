# Certification Feature Removal Plan

## Files to be Modified/Deleted

### 1. DELETE - Service Files
- `app/Services/Compliance/Validation/ComplianceCertificationService.php` - ENTIRE FILE

### 2. DELETE - Migration Files
- `database/migrations/2024_01_15_000001_create_compliance_certification_logs_table.php` - ENTIRE FILE

### 3. MODIFY - Controller Files
- `app/Http/Controllers/ComplianceExecutionController.php`
  - Remove `certifyBatch()` method
  - Remove `getCertificationStatus()` method
  - Remove certification logic from `downloadInspectionPack()` method
  - Remove certification logic from `dashboard()` method

### 4. MODIFY - Route Files
- `routes/compliance.php`
  - Remove certification routes:
    - `Route::post('/batch/{batch}/certify', ...)`
    - `Route::get('/batch/{batch}/certification-status', ...)`

### 5. MODIFY - View Files (if any)
- Check for certification UI references in blade templates

### 6. CREATE - Migration to Drop Table
- Create new migration to drop `compliance_certification_logs` table

## Workflow After Removal

The system will support:
1. ✅ Create Compliance Batch
2. ✅ Review Forms
3. ✅ Check Data Availability
4. ✅ Generate Forms
5. ✅ Download Inspection Pack (without certification check)

## Status
- [ ] Step 1: Delete service file
- [ ] Step 2: Delete migration file
- [ ] Step 3: Modify controller
- [ ] Step 4: Modify routes
- [ ] Step 5: Create drop migration
- [ ] Step 6: Verify no references remain
