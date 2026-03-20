# Batch Workflow Refactoring - Quick Reference

## 30-Second Overview

The batch workflow now has **3 stages**:

1. **Create Batch** - User selects Month + Year → System detects forms automatically
2. **Review Batch** - System checks data availability → User reviews forms
3. **Process Batch** - System generates forms → User downloads inspection pack

**Key Change:** Forms are now **automatically detected** based on frequency. No manual selection.

---

## Files at a Glance

### New Files (3)
```
app/Services/Compliance/DataAvailabilityEngine.php      (200 lines)
app/Services/Compliance/BatchReviewService.php          (50 lines)
resources/views/compliance/batch-review.blade.php       (250 lines)
```

### Modified Files (2)
```
app/Http/Controllers/ComplianceExecutionController.php  (2 changes)
routes/compliance.php                                   (2 changes)
```

### Unchanged Files
```
All other files remain unchanged
All existing functionality preserved
No breaking changes
```

---

## Three-Stage Workflow

### Stage 1: Create Batch
```
User Input:
  - Month (1-12)
  - Year (2020-2030)

System Actions:
  - Validate branch exists
  - Detect applicable forms by frequency
  - Create batch (status = 'pending')
  - Attach forms (status = 'pending')

Output:
  - Redirect to review page
```

### Stage 2: Review Batch (NEW)
```
System Actions:
  - Get batch and forms
  - Check data availability
  - Prepare data summary

Display:
  - Batch information
  - Forms to be generated
  - Data availability status
  - Data summary table
  - Action buttons

User Actions:
  - Review forms
  - Review data status
  - Click Proceed or Cancel
```

### Stage 3: Process Batch
```
User Input:
  - Click "Proceed to Processing"

System Actions:
  - Generate all forms
  - Update file paths
  - Run audit
  - Run certification

Output:
  - Redirect to dashboard
  - Success message
```

---

## Frequency Rules

| Frequency | Months | Example |
|-----------|--------|---------|
| Monthly | Every month | 1,2,3,4,5,6,7,8,9,10,11,12 |
| Quarterly | 3,6,9,12 | March, June, Sept, Dec |
| Half-Yearly | 6,12 | June, December |
| Yearly | 12 | December |

**Example:** User selects March (month 3)
- Monthly forms: ✓ Included
- Quarterly forms: ✓ Included (Q1)
- Half-yearly forms: ✗ Not included
- Yearly forms: ✗ Not included

---

## Data Availability Check

### What Gets Checked
```
✓ Employees (at least 1)
✓ Attendance (for period)
✓ Payroll (for period)
✓ Contract Labour (at least 1)
✓ Bonus Records (for period)
✓ Incidents (for period)
✓ Hazard Register (at least 1)
```

### Result
```
all_data_exists: true/false
missing_data: ['employees', 'attendance']
data_summary: {
  employees: 50,
  attendance_records: 1200,
  ...
}
```

---

## Routes

### New Route
```
GET /compliance/batch/{batch}/review
  - Display review page
  - Auth required
  - Returns HTML view
```

### Modified Route
```
POST /compliance/batch/create
  - Redirect changed to review page
  - Functionality unchanged
```

### Unchanged Routes
```
All other routes remain the same
All API endpoints unchanged
All preview routes unchanged
```

---

## Code Examples

### Create Batch
```php
// Controller
public function createBatch(Request $request)
{
    $batchOrchestrator = app(BatchOrchestrator::class);
    $batch = $batchOrchestrator->createBatch(
        $tenantId,
        $month,
        $year
    );
    
    return redirect()->route('compliance.batch.review', ['batch' => $batch->id]);
}
```

### Review Batch
```php
// Controller
public function reviewBatch(int $batch)
{
    $reviewService = app(BatchReviewService::class);
    $reviewData = $reviewService->prepareReviewData($batch);
    
    return view('compliance.batch-review', $reviewData);
}
```

### Check Data Availability
```php
// Service
$engine = new DataAvailabilityEngine();
$result = $engine->checkDataAvailability($tenantId, $branchId, $month, $year);

if ($result['all_data_exists']) {
    // Proceed with processing
} else {
    // Show missing data notice
}
```

---

## Database Queries

### Get Applicable Forms
```sql
SELECT * FROM compliance_forms_master 
WHERE is_active = 1 
AND frequency IN ('monthly', 'quarterly', 'half-yearly', 'yearly');
```

### Get Batch Forms
```sql
SELECT * FROM compliance_batch_forms 
WHERE batch_id = ? 
AND status = 'pending';
```

### Check Employee Data
```sql
SELECT COUNT(*) FROM workforce_employee 
WHERE tenant_id = ? 
AND branch_id = ?;
```

### Check Attendance Data
```sql
SELECT COUNT(*) FROM workforce_attendance 
WHERE tenant_id = ? 
AND branch_id = ? 
AND YEAR(attendance_date) = ? 
AND MONTH(attendance_date) = ?;
```

---

## Testing Checklist

### Stage 1: Create Batch
- [ ] Batch created with status = 'pending'
- [ ] Forms detected correctly
- [ ] Forms attached to batch
- [ ] Redirected to review page

### Stage 2: Review Batch
- [ ] Review page displays
- [ ] Batch info shown
- [ ] Forms listed
- [ ] Data availability checked
- [ ] Data summary displayed
- [ ] Proceed button enabled/disabled correctly

### Stage 3: Process Batch
- [ ] Forms generated
- [ ] File paths updated
- [ ] Status updated to 'generated'
- [ ] Audit ran
- [ ] Certification ran
- [ ] Redirected to dashboard

---

## Common Issues & Solutions

### Issue: Batch not created
**Solution:** Check if branch exists
```sql
SELECT * FROM branches WHERE tenant_id = ?;
```

### Issue: No forms detected
**Solution:** Check if forms are active
```sql
SELECT * FROM compliance_forms_master WHERE is_active = 1;
```

### Issue: Review page not loading
**Solution:** Check if batch exists
```sql
SELECT * FROM compliance_execution_batches WHERE id = ?;
```

### Issue: Data availability check failing
**Solution:** Check if data exists
```sql
SELECT COUNT(*) FROM workforce_employee WHERE tenant_id = ?;
```

---

## Performance Tips

1. **Cache frequency rules**
   ```php
   Cache::remember('frequency_rules', 3600, function() {
       return ComplianceFormsMaster::where('is_active', true)->get();
   });
   ```

2. **Use database aggregation**
   ```php
   $counts = DB::table('workforce_employee')
       ->selectRaw('COUNT(*) as total')
       ->where('tenant_id', $tenantId)
       ->first();
   ```

3. **Batch queries**
   ```php
   $data = DB::table('workforce_employee')
       ->where('tenant_id', $tenantId)
       ->with('attendance', 'payroll')
       ->get();
   ```

---

## Security Checklist

- [ ] All queries filter by tenant_id
- [ ] All queries filter by branch_id
- [ ] User authentication required
- [ ] User authorization verified
- [ ] Input validation applied
- [ ] SQL injection prevented
- [ ] XSS prevention enabled
- [ ] CSRF token verified

---

## Deployment Steps

1. **Create new files**
   ```bash
   cp DataAvailabilityEngine.php app/Services/Compliance/
   cp BatchReviewService.php app/Services/Compliance/
   cp batch-review.blade.php resources/views/compliance/
   ```

2. **Update existing files**
   ```bash
   # Update ComplianceExecutionController.php
   # Update routes/compliance.php
   ```

3. **Clear cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

4. **Test**
   ```bash
   php artisan test
   ```

5. **Deploy**
   ```bash
   git push production main
   ```

---

## Rollback Steps

If issues occur:

1. **Revert code**
   ```bash
   git revert <commit_hash>
   ```

2. **Clear cache**
   ```bash
   php artisan cache:clear
   ```

3. **Verify**
   ```bash
   curl http://production/compliance/dashboard
   ```

---

## Key Points to Remember

✅ **Automation First**
- Forms detected automatically
- No manual selection
- Based on frequency rules

✅ **Data Validation**
- Check data before processing
- Prevent generation failures
- Clear feedback to users

✅ **Three Stages**
- Create → Review → Process
- Clear workflow
- Better UX

✅ **No Breaking Changes**
- All existing systems work
- All existing functionality preserved
- Backward compatible

✅ **Multi-Tenant Safe**
- Tenant isolation enforced
- Branch filtering applied
- User authorization verified

---

## Documentation Links

- **Architecture:** `BATCH_WORKFLOW_REFACTORING_ARCHITECTURE.md`
- **Implementation:** `BATCH_WORKFLOW_IMPLEMENTATION_GUIDE.md`
- **Verification:** `BATCH_WORKFLOW_VERIFICATION_GUIDE.md`
- **Changes:** `BATCH_WORKFLOW_CHANGE_SUMMARY.md`

---

## Quick Links

| Resource | Location |
|----------|----------|
| DataAvailabilityEngine | `app/Services/Compliance/DataAvailabilityEngine.php` |
| BatchReviewService | `app/Services/Compliance/BatchReviewService.php` |
| Review View | `resources/views/compliance/batch-review.blade.php` |
| Controller | `app/Http/Controllers/ComplianceExecutionController.php` |
| Routes | `routes/compliance.php` |

---

## Support

For questions:
1. Check the implementation guide
2. Review the verification guide
3. Check the logs
4. Contact the team

---

## Status

✅ **Architecture:** Complete
✅ **Implementation:** Complete
✅ **Documentation:** Complete
✅ **Ready for:** Testing & Deployment

