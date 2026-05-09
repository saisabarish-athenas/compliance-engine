# Deep Diagnostic Engine - Quick Reference

## Quick Start

### Run Diagnostics
```bash
php artisan compliance:diagnose
```

### View Dashboard
```
http://localhost/compliance/dashboard/testanalysisreport
```

### Get JSON Report
```bash
curl http://localhost/compliance/diagnostics/latest
```

## Health Score Weights

| Component | Weight | Purpose |
|-----------|--------|---------|
| Preview Pipeline | 30% | API → Generator → Blade → Render |
| API Services | 15% | Tenant/branch isolation |
| Generators | 15% | Data structure validation |
| Blade Templates | 10% | Variable usage validation |
| Database Datasets | 10% | Required data availability |
| PDF Generation | 10% | PDF creation success |
| Inspection Pack | 5% | ZIP creation success |
| Security | 5% | Multi-tenant enforcement |

## Common Issues & Fixes

### Issue: Health Score 0%

**Cause:** No test data available

**Fix:**
```bash
php artisan db:seed
```

### Issue: Preview Pipeline Failing

**Cause:** Missing Blade template

**Fix:**
1. Check template exists: `resources/views/compliance/forms/{form_code}.blade.php`
2. Verify variables: `$form_title`, `$rows`, `$header`, `$totals`
3. Use safe output: `{{ $variable ?? 'default' }}`

### Issue: API Services Failing

**Cause:** Missing tenant/branch filtering

**Fix:**
1. Add to API service: `->where('tenant_id', $tenantId)`
2. Add to API service: `->where('branch_id', $branchId)`

### Issue: Generator Failing

**Cause:** Missing prepareData() method

**Fix:**
```php
protected function prepareData(array $rawData): array
{
    return [
        'header' => [...],
        'rows' => [...],
        'totals' => [...],
        'is_nil' => false
    ];
}
```

### Issue: Database Datasets Empty

**Cause:** No test records

**Fix:**
```bash
php artisan db:seed --class=ComplianceFullDemoSeeder
```

## Root Cause Analysis

Each failure shows:
- **Component**: Which system layer failed
- **Root Cause**: Why it failed
- **Affected Files**: Which files to check
- **Recommended Fix**: How to fix it

## Using with Amazon Q

1. Run diagnostics:
   ```bash
   php artisan compliance:diagnose --save
   ```

2. Copy root causes from dashboard

3. Ask Amazon Q:
   ```
   Fix these compliance issues:
   [paste root causes]
   ```

## Diagnostic Report Fields

```json
{
  "health_score": 85,           // 0-100%
  "status": "warning",          // healthy|warning|critical
  "execution_time": 2500,       // milliseconds
  "timestamp": "2024-03-10...", // ISO 8601
  "diagnostics": {              // Component results
    "preview_pipeline": {
      "status": "pass",
      "weight": 30,
      "forms_tested": 3,
      "forms_passed": 3
    }
  },
  "root_causes": [              // Issues found
    {
      "component": "...",
      "root_cause": "...",
      "affected_files": [...],
      "recommended_fix": "..."
    }
  ],
  "summary": {                  // Overall stats
    "components_passed": 7,
    "components_failed": 1,
    "total_issues": 2
  }
}
```

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

## API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/compliance/diagnostics/run` | GET | Run diagnostics |
| `/compliance/diagnostics/latest` | GET | Get latest report |
| `/compliance/diagnostics/dashboard` | GET | Get dashboard data |

## Dashboard Features

- Real-time health score
- Component status table
- Root cause analysis
- Recommended fixes
- Copy to clipboard for Amazon Q
- Refresh button

## Performance

- Typical execution: 2-5 seconds
- Tests real workflows (not mocked)
- Can run on-demand
- Results stored in `storage/logs/`

## Troubleshooting Commands

```bash
# Check test data
php artisan tinker
>>> Tenant::count()
>>> Branch::count()

# View latest report
cat storage/logs/diagnostic_report_*.json

# Run with verbose output
php artisan compliance:diagnose -v

# Save report
php artisan compliance:diagnose --save
```

## Integration Points

- **ComplianceOrchestrator**: Executes real workflows
- **FormGeneratorFactory**: Creates generators
- **FormApiServiceFactory**: Creates API services
- **View**: Renders Blade templates
- **Database**: Queries tables

## Next Steps

1. Run diagnostics: `php artisan compliance:diagnose`
2. Review health score
3. Check root causes
4. Use Amazon Q to fix issues
5. Re-run diagnostics to verify

## Support

For issues:
1. Check DEEP_DIAGNOSTIC_ENGINE_GUIDE.md
2. Review root cause analysis
3. Check affected files
4. Follow recommended fixes
5. Re-run diagnostics
