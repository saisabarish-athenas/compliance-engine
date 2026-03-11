# Deep Project Diagnostic Engine

## Overview

The Deep Project Diagnostic Engine is a comprehensive system audit tool that performs real workflow execution across all platform layers to detect root causes of failures and produce accurate system health scores.

## Architecture

### Components Tested

1. **Preview Pipeline (30% weight)**
   - Executes real preview workflows through ComplianceOrchestrator
   - Tests API service → Generator → Blade template → Preview rendering
   - Detects failures at each stage with root-cause analysis

2. **Form Generators (15% weight)**
   - Scans all generator classes in `app/Services/Compliance/FormGenerator`
   - Verifies `prepareData()` method exists
   - Validates output structure: header, rows, totals, is_nil

3. **Blade Templates (10% weight)**
   - Scans all templates in `resources/views/compliance/forms`
   - Validates variable usage and safe output
   - Detects missing form_title, rows iteration, control structures

4. **API Services (15% weight)**
   - Scans all API services in `app/Services/Compliance/FormApis`
   - Verifies tenant_id and branch_id filtering
   - Validates fetch() method implementation

5. **Database Datasets (10% weight)**
   - Checks required tables: tenants, branches, workforce_employee, payroll_entry, contractor
   - Verifies record counts
   - Detects missing or empty datasets

6. **PDF Generation (10% weight)**
   - Executes PDF generation for multiple forms
   - Verifies file size > 0 and correct MIME type
   - Records failures with error details

7. **Inspection Pack (5% weight)**
   - Tests ZIP creation and PDF collection
   - Verifies download path and file integrity

8. **Security Isolation (5% weight)**
   - Verifies subscription enforcement
   - Checks tenant isolation in API services
   - Validates branch isolation

## Health Score Calculation

```
Health Score = (Σ(component_score × weight)) / Σ(weights)

Where:
- component_score = 100 if status is 'pass', 0 if 'fail'
- weights = [30, 15, 10, 15, 10, 10, 5, 5] = 100%
```

### Score Interpretation

- **100%**: All components functioning correctly
- **70-99%**: System operational with minor issues
- **<70%**: Critical issues requiring immediate attention

## Usage

### Via CLI Command

```bash
# Run diagnostics and display results
php artisan compliance:diagnose

# Run diagnostics and save report
php artisan compliance:diagnose --save
```

### Via HTTP API

```bash
# Run diagnostics
GET /compliance/diagnostics/run

# Get latest report
GET /compliance/diagnostics/latest

# Get dashboard data
GET /compliance/diagnostics/dashboard
```

### Via Dashboard

Navigate to `/compliance/dashboard/testanalysisreport` to view:
- System health score
- Component status
- Root cause analysis
- Recommended fixes

## Root Cause Analysis

Each failure includes:

```json
{
  "component": "Preview Pipeline",
  "form_code": "FORM_B",
  "status": "fail",
  "root_cause": "Blade template not found",
  "error_message": "View not found for FORM_B",
  "affected_files": ["resources/views/compliance/forms/form_b.blade.php"],
  "recommended_fix": "Create resources/views/compliance/forms/form_b.blade.php"
}
```

## Integration with Amazon Q

The diagnostic report can be used with Amazon Q to automatically fix issues:

1. Run diagnostics: `php artisan compliance:diagnose --save`
2. Copy the root cause analysis from the dashboard
3. Paste into Amazon Q chat with prompt:
   ```
   Fix these compliance system issues based on the root cause analysis:
   [paste diagnostics JSON]
   ```

## Report Structure

```json
{
  "status": "healthy|warning|critical",
  "health_score": 85,
  "execution_time": 2500,
  "timestamp": "2024-03-10T10:30:00Z",
  "diagnostics": {
    "preview_pipeline": {
      "status": "pass|fail",
      "weight": 30,
      "forms_tested": 3,
      "forms_passed": 3,
      "results": {}
    },
    ...
  },
  "root_causes": [
    {
      "component": "...",
      "status": "fail",
      "root_cause": "...",
      "affected_files": [],
      "recommended_fix": "..."
    }
  ],
  "summary": {
    "total_components_tested": 8,
    "components_passed": 7,
    "components_failed": 1,
    "total_issues": 2
  }
}
```

## Diagnostic Tests

### Preview Pipeline Test

Tests the complete workflow:
1. API service fetches data
2. Generator prepares form data
3. Blade template renders
4. Preview returns HTML

**Failure Detection:**
- Missing API service or generator
- Blade template not found
- prepareData() method failed
- API service data fetch failed

### Generator Analysis Test

Scans all generator classes for:
- `prepareData()` method
- `header` structure
- `rows` array
- `totals` calculation
- `is_nil` flag

### Blade Template Analysis Test

Validates templates for:
- Form title variable
- Rows iteration
- Safe output syntax
- Control structures (@if, @foreach)

### API Service Analysis Test

Checks all API services for:
- `tenant_id` filtering
- `branch_id` filtering
- `fetch()` method
- Database queries

### Database Datasets Test

Verifies:
- Table existence
- Record counts
- Required columns

### PDF Generation Test

Tests:
- PDF file size > 0
- Correct MIME type
- Successful generation

### Inspection Pack Test

Validates:
- ZIP creation
- PDF collection
- File integrity

### Security Isolation Test

Checks:
- Subscription validation
- Tenant isolation
- Branch isolation

## Troubleshooting

### No Test Data Available

**Error:** "No test tenant available"

**Fix:** Seed test data
```bash
php artisan db:seed
```

### Missing Components

**Error:** "No API service or generator found"

**Fix:** Create missing service/generator classes

### Blade Template Issues

**Error:** "Blade template not found"

**Fix:** Create template at `resources/views/compliance/forms/{form_code}.blade.php`

### Database Issues

**Error:** "Table does not exist"

**Fix:** Run migrations
```bash
php artisan migrate
```

## Performance Considerations

- Diagnostics execute real workflows (not mocked)
- Typical execution time: 2-5 seconds
- Can be run on-demand or scheduled
- Results cached for 5 minutes

## Scheduling Diagnostics

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('compliance:diagnose --save')
        ->daily()
        ->at('02:00');
}
```

## Extending Diagnostics

To add custom diagnostic tests:

1. Add method to `ComplianceDiagnosticEngine`
2. Call from `runFullDiagnostics()`
3. Add to `$this->diagnostics` array
4. Include weight in calculation

Example:

```php
private function testCustomComponent(): void
{
    // Your test logic
    $this->diagnostics['custom_component'] = [
        'status' => 'pass|fail',
        'weight' => 5,
        'results' => []
    ];
}
```

## Files Created

- `app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php` - Main diagnostic engine
- `app/Http/Controllers/Compliance/ComplianceDiagnosticController.php` - HTTP endpoints
- `app/Console/Commands/RunComplianceDiagnostics.php` - CLI command
- `app/Providers/DiagnosticServiceProvider.php` - Service provider
- `resources/views/compliance/dashboard/testanalysisreport.blade.php` - Dashboard view

## Routes

- `GET /compliance/diagnostics/run` - Run diagnostics
- `GET /compliance/diagnostics/latest` - Get latest report
- `GET /compliance/diagnostics/dashboard` - Get dashboard data
- `GET /compliance/dashboard/testanalysisreport` - View dashboard

## Commands

- `php artisan compliance:diagnose` - Run diagnostics
- `php artisan compliance:diagnose --save` - Run and save report
