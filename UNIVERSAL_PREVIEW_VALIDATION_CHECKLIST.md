# UNIVERSAL PREVIEW SYSTEM - VALIDATION CHECKLIST

## Pre-Implementation Verification

- [ ] CompliancePreviewController created at `app/Http/Controllers/Compliance/CompliancePreviewController.php`
- [ ] Route added to `routes/compliance.php`
- [ ] ComplianceDataService has `normalizeDataPublic()` method
- [ ] FormRegistry contains all 38 forms
- [ ] All blade templates exist in `resources/views/compliance/forms/`

## Controller Functionality Tests

### Basic Route Access
- [ ] `/compliance/preview/FORM_B` returns 200
- [ ] `/compliance/preview/FORM_XIII` returns 200
- [ ] `/compliance/preview/INVALID_FORM` returns 404
- [ ] Unauthenticated access redirects to login

### Parameter Handling
- [ ] `?month=1&year=2024` parameters accepted
- [ ] `?batch_id=1` parameter accepted
- [ ] `?branch_id=2` parameter accepted
- [ ] Invalid batch_id returns 404
- [ ] Invalid branch_id handled gracefully

### Subscription Logic
- [ ] FULL subscription shows data rows
- [ ] MINIMAL subscription shows empty rows
- [ ] Subscription type correctly read from tenant
- [ ] Data properly filtered based on subscription

### Tenant Isolation
- [ ] User can only access own tenant data
- [ ] Cross-tenant access returns 403
- [ ] Batch tenant validation works
- [ ] Branch tenant validation works

## Data Normalization Tests

### Entries ↔ Rows Mapping
- [ ] Builder returns `entries` → controller provides `rows`
- [ ] Builder returns `rows` → controller provides `entries`
- [ ] Both `entries` and `rows` available in blade
- [ ] No data loss during mapping

### NIL Status Handling
- [ ] Status 'NIL' returns empty rows
- [ ] Status 'NIL' returns empty entries
- [ ] Status 'NIL' returns empty totals
- [ ] Period still populated for NIL status

### Data Completeness
- [ ] `totals` array always present
- [ ] `period` always populated
- [ ] `form_title` added correctly
- [ ] `form_code` added correctly
- [ ] `batch_id` added when provided
- [ ] `subscription` type added
- [ ] `tenant_id` added
- [ ] `branch_id` added

## Blade Template Tests

### Template Detection
- [ ] Lowercase form code used for template path
- [ ] Template path: `compliance.forms.{formCode}`
- [ ] Non-existent template returns 404
- [ ] Template rendering succeeds

### Data Availability in Blade
- [ ] `$rows` variable accessible
- [ ] `$entries` variable accessible
- [ ] `$totals` variable accessible
- [ ] `$period` variable accessible
- [ ] `$form_title` variable accessible
- [ ] `$form_code` variable accessible
- [ ] `$batch_id` variable accessible
- [ ] `$subscription` variable accessible

### Loop Compatibility
- [ ] `@foreach($rows ?? $entries ?? [] as $row)` works
- [ ] Empty rows render without error
- [ ] Multiple rows render correctly
- [ ] Row data accessible in loop

## Form-Specific Tests

### Factories Act Forms
- [ ] FORM_B (Wage Register) - 25 rows
- [ ] FORM_10 (Overtime Register) - 10 rows
- [ ] FORM_25 (Attendance Register) - 30 rows
- [ ] FORM_12 (Employee Register) - 15 rows
- [ ] FORM_2 (Work Shift) - 5 rows
- [ ] FORM_7 (Inspection Register) - 8 rows
- [ ] FORM_8 (Incident) - 3 rows
- [ ] FORM_11 (Accident Register) - 2 rows
- [ ] FORM_17 (Health Register) - 4 rows
- [ ] FORM_18 (Accident Report) - 1 row
- [ ] FORM_26 (Accident Register) - 2 rows
- [ ] FORM_26A (Dangerous Occurrence) - 1 row

### CLRA Forms
- [ ] FORM_XII (Contractor Master) - 5 rows
- [ ] FORM_XIII (Contractor Workmen) - 20 rows
- [ ] FORM_XIV (Employment Card) - 1 row
- [ ] FORM_XVI (Contractor Muster) - 25 rows
- [ ] FORM_XVII (Contractor Wage Register) - 25 rows
- [ ] FORM_XIX (Contractor Wage Slip) - 25 rows
- [ ] FORM_XX (Deduction Register) - 15 rows
- [ ] FORM_XXI (Fines Register) - 5 rows
- [ ] FORM_XXII (Advance Register) - 10 rows
- [ ] FORM_XXIII (Contractor Overtime) - 8 rows
- [ ] FORM_XXIV (Contractor Half-Yearly) - 1 row
- [ ] FORM_XXV (Principal Annual) - 1 row

### Shops Act Forms
- [ ] SHOPS_FORM_12 (Wage Register) - 20 rows
- [ ] SHOPS_FORM_13 (Leave Register) - 15 rows
- [ ] SHOPS_FORM_1 (Employee Register) - 12 rows
- [ ] SHOPS_FORM_C (Bonus Register) - 8 rows
- [ ] SHOPS_FORM_VI (Holiday Register) - 10 rows
- [ ] SHOPS_FINES (Fines Register) - 5 rows
- [ ] SHOPS_UNPAID (Unpaid Bonus) - 3 rows

### Social Security Forms
- [ ] ESI_FORM_12 (Incident) - 3 rows
- [ ] EPF_INSPECTION (Inspection Register) - 8 rows

### Labour Welfare Forms
- [ ] FORM_A (Employee Register) - 15 rows
- [ ] FORM_C (Deduction Register) - 10 rows
- [ ] FORM_D (Attendance Register) - 30 rows
- [ ] FORM_D_ER (Equal Remuneration) - 5 rows

### Other Forms
- [ ] CONTRACTOR_MASTER - 5 rows

## Error Handling Tests

### 404 Errors
- [ ] Invalid form code: `/compliance/preview/INVALID`
- [ ] Invalid batch: `/compliance/preview/FORM_B?batch_id=99999`
- [ ] Missing template: Form with no blade file
- [ ] Error logged with context

### 403 Errors
- [ ] Cross-tenant access blocked
- [ ] Error logged with context
- [ ] User informed of authorization issue

### 500 Errors
- [ ] Builder execution failure handled
- [ ] Data service failure handled
- [ ] Template rendering failure handled
- [ ] Error logged with full trace

## Logging Tests

### Log Entries
- [ ] Preview request logged
- [ ] Form code logged
- [ ] Batch ID logged
- [ ] Subscription type logged
- [ ] Row count logged
- [ ] Errors logged with context
- [ ] Unauthorized access logged

### Log Format
- [ ] Timestamp present
- [ ] Log level correct (INFO, ERROR, WARNING)
- [ ] Context array populated
- [ ] No sensitive data logged

## Performance Tests

### Response Time
- [ ] FORM_B preview < 500ms
- [ ] FORM_XIII preview < 500ms
- [ ] Large form (30+ rows) < 1000ms
- [ ] Empty preview < 200ms

### Database Queries
- [ ] No N+1 queries
- [ ] Repositories optimize queries
- [ ] Batch context reuses data
- [ ] Indexes used effectively

### Memory Usage
- [ ] Large forms don't cause memory issues
- [ ] Blade rendering efficient
- [ ] No memory leaks

## Integration Tests

### With Batch Context
- [ ] Preview from batch uses batch period
- [ ] Preview from batch uses batch branch
- [ ] Preview from batch uses batch tenant
- [ ] Batch data persists correctly

### With Manual Data Entry
- [ ] MINIMAL subscription shows empty
- [ ] Manual data entry still works
- [ ] Preview respects subscription

### With Audit System
- [ ] Preview data matches audit data
- [ ] Audit scores consistent
- [ ] Violations detected correctly

## Security Tests

### Authentication
- [ ] Unauthenticated access blocked
- [ ] Session validation works
- [ ] Token validation works

### Authorization
- [ ] Tenant isolation enforced
- [ ] Branch isolation enforced
- [ ] User can only access own data
- [ ] Admin can access all data (if applicable)

### Input Validation
- [ ] Form code validated
- [ ] Batch ID validated
- [ ] Branch ID validated
- [ ] Month/year validated
- [ ] SQL injection prevented
- [ ] XSS prevented

## Documentation Tests

- [ ] UNIVERSAL_PREVIEW_IMPLEMENTATION.md complete
- [ ] UNIVERSAL_PREVIEW_QUICK_REFERENCE.md complete
- [ ] Code comments clear
- [ ] Route documentation updated
- [ ] API documentation updated

## Deployment Tests

- [ ] Code follows PSR-12 standards
- [ ] No PHP warnings/errors
- [ ] No Laravel warnings
- [ ] Tests pass (if applicable)
- [ ] Code review approved

## User Acceptance Tests

- [ ] Users can access preview
- [ ] Preview displays correctly
- [ ] Data is accurate
- [ ] Performance is acceptable
- [ ] Error messages are helpful
- [ ] UI is intuitive

## Final Verification

- [ ] All 38 forms tested
- [ ] All error scenarios tested
- [ ] All subscription levels tested
- [ ] All parameters tested
- [ ] All security checks passed
- [ ] All performance targets met
- [ ] Documentation complete
- [ ] Ready for production

---

## Sign-Off

- [ ] Developer: _________________ Date: _______
- [ ] QA: _________________ Date: _______
- [ ] Product Owner: _________________ Date: _______
- [ ] DevOps: _________________ Date: _______

---

## Notes

Use this space for any additional observations or issues found during testing:

```
[Add notes here]
```
