# TEST ANALYSIS SYSTEM - QUICK REFERENCE

## Access Dashboard

**URL:** `http://127.0.0.1:8000/compliance/dashboard/testanalysisreport`

**Requirements:**
- User must be logged in
- Auth middleware enforced

## Dashboard Components

### 1. Health Score Card
- Visual progress bar (0-100%)
- Color coded: Green (80+), Yellow (60-79), Red (<60)
- Execution time in milliseconds
- Overall status badge

### 2. Test Results Table
- Component name
- Status indicator (✔ PASS, ⚠ WARNING, ❌ ERROR)
- Details (passed/total, execution time, message)

### 3. Errors Section
- Red header
- Lists all detected errors
- Each error has ERROR badge

### 4. Warnings Section
- Yellow header
- Lists all detected warnings
- Each warning has WARNING badge

### 5. Performance Metrics
- Execution times for each mode
- Preview mode time
- PDF mode time

### 6. Component Details
- Expandable accordion for each component
- JSON formatted details
- Collapsible for easy navigation

### 7. Summary
- Total tests run
- Error count
- Warning count

## Test Components

| Component | Tests | Status |
|-----------|-------|--------|
| Routes | 4 routes | ✔ |
| Controllers | 3 controllers | ✔ |
| Orchestrator | Preview mode | ✔ |
| Generators | prepareData method | ✔ |
| Blade Templates | header, rows, totals | ✔ |
| API Services | tenant/branch filtering | ✔ |
| Database | Tables and columns | ✔ |
| Security | Validation checks | ✔ |
| PDF Generation | DomPDF rendering | ✔ |
| Inspection Pack | Directory structure | ✔ |
| Performance | Execution times | ✔ |

## Status Indicators

```
✔ PASS   = Green   = Component working correctly
⚠ WARNING = Yellow = Component has issues but working
❌ ERROR  = Red    = Component failed or missing
```

## Health Score Ranges

```
80-100% = ✔ HEALTHY (Green)
60-79%  = ⚠ WARNING (Yellow)
0-59%   = ❌ CRITICAL (Red)
```

## Files Involved

### Service
- `app/Services/Compliance/Testing/ComplianceTestAnalyzer.php`

### Controller
- `app/Http/Controllers/Compliance/ComplianceTestAnalysisController.php`

### Route
- `routes/compliance.php` (line with testanalysisreport)

### View
- `resources/views/compliance/dashboard/testanalysisreport.blade.php`

## Test Methods

### testRoutes()
Validates compliance routes exist:
- /compliance/dashboard
- /compliance/preview/{formCode}
- /compliance/batch/{batch}/preview/{form}
- /compliance/batch/{batch}/inspection-pack

### testControllers()
Checks controller files:
- ComplianceExecutionController
- CompliancePreviewController
- ComplianceOrchestratorController

### testOrchestrator()
Executes real orchestrator:
- Mode: preview
- Form: FORM_B
- Measures execution time

### testGenerators()
Scans generator files:
- Checks for prepareData method
- Validates data structure

### testBladeTemplates()
Validates template variables:
- Checks for $header
- Checks for $rows
- Checks for $totals

### testApiServices()
Verifies API services:
- Checks tenant_id filtering
- Checks branch_id filtering

### testDatabase()
Validates database schema:
- Checks table existence
- Checks column existence

### testSecurity()
Validates security:
- Subscription validation
- Tenant ID validation
- Branch ID validation

### testPdfGeneration()
Tests PDF creation:
- Executes orchestrator PDF mode
- Checks file size
- Validates mime type

### testInspectionPack()
Verifies inspection pack:
- Checks directory exists
- Validates path

### testPerformance()
Measures execution times:
- Preview mode time
- PDF mode time

## Interpreting Results

### All Green (✔ PASS)
- Platform is healthy
- All components working
- No action needed

### Some Yellow (⚠ WARNING)
- Platform is functional
- Some components have issues
- Review warnings section
- May need attention

### Any Red (❌ ERROR)
- Platform has critical issues
- Review errors section
- Fix issues before production

## Performance Expectations

| Mode | Typical Time |
|------|--------------|
| Preview | 200-500ms |
| PDF | 500-1500ms |
| Total Test | 2-5 seconds |

## Troubleshooting

### Dashboard Not Loading
- Check authentication (must be logged in)
- Check route is registered
- Check controller exists

### Tests Failing
- Check database has test data
- Check file permissions
- Check storage directories exist

### Slow Performance
- Check system resources
- Check database performance
- Check file system performance

## Common Issues

### "No test tenant/branch available"
- Create test tenant and branch
- Run database seeders
- Populate test data

### "Missing table" errors
- Run migrations
- Check database connection
- Verify schema

### "Missing column" errors
- Run migrations
- Check migration files
- Verify schema

### "Controller not found"
- Check file paths
- Verify namespace
- Check file exists

## Accessing Component Details

1. Scroll to "Component Details" section
2. Click on component name to expand
3. View JSON formatted details
4. Click again to collapse

## Exporting Results

To export results:
1. Open browser developer tools (F12)
2. Go to Console tab
3. Copy JSON from report object
4. Paste into text editor
5. Save as .json file

## Monitoring

Run tests regularly to:
- Monitor platform health
- Detect regressions
- Track performance
- Identify issues early

## Best Practices

1. Run tests after deployments
2. Monitor health score trends
3. Address warnings promptly
4. Track performance metrics
5. Keep test data current

---

**Last Updated:** 2024
**Status:** ✔ READY FOR USE
