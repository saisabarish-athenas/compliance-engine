# Deep Project Diagnostic Engine

A comprehensive system audit tool for the Laravel 12 Multi-Tenant Labour Compliance Automation Platform.

## What It Does

The Diagnostic Engine performs real workflow execution across all platform layers to:

- ✓ Execute real workflows through ComplianceOrchestrator
- ✓ Detect root causes of failures
- ✓ Produce detailed analysis reports
- ✓ Calculate accurate system health scores
- ✓ Enable Amazon Q to automatically fix detected issues

## Quick Start

### 1. Run Diagnostics
```bash
php artisan compliance:diagnose
```

### 2. View Dashboard
```
http://localhost/compliance/dashboard/testanalysisreport
```

### 3. Use Amazon Q
Click "Copy Diagnostics for Amazon Q" and paste in Amazon Q chat.

## Health Score

The system calculates a weighted health score (0-100%):

| Score | Status | Meaning |
|-------|--------|---------|
| 100% | Healthy | All components functioning correctly |
| 70-99% | Warning | System operational with minor issues |
| <70% | Critical | Critical issues requiring immediate attention |

## 8 Diagnostic Tests

1. **Preview Pipeline (30%)** - API → Generator → Blade → Render
2. **Form Generators (15%)** - Data structure validation
3. **Blade Templates (10%)** - Variable usage validation
4. **API Services (15%)** - Tenant/branch isolation
5. **Database Datasets (10%)** - Required data availability
6. **PDF Generation (10%)** - PDF creation success
7. **Inspection Pack (5%)** - ZIP creation success
8. **Security (5%)** - Multi-tenant enforcement

## Commands

```bash
# Run diagnostics
php artisan compliance:diagnose

# Run and save report
php artisan compliance:diagnose --save

# Validate installation
php artisan compliance:validate-diagnostics
```

## API Endpoints

```bash
# Run diagnostics
GET /compliance/diagnostics/run

# Get latest report
GET /compliance/diagnostics/latest

# Get dashboard data
GET /compliance/diagnostics/dashboard
```

## Root Cause Analysis

Each failure includes:
- Component name
- Root cause explanation
- Error message
- Affected files
- Recommended fix

## Amazon Q Integration

1. Run diagnostics: `php artisan compliance:diagnose --save`
2. View dashboard at `/compliance/dashboard/testanalysisreport`
3. Click "Copy Diagnostics for Amazon Q"
4. Paste in Amazon Q chat
5. Ask: "Fix these compliance system issues"
6. Amazon Q generates fixes

## Documentation

| Document | Purpose |
|----------|---------|
| `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md` | Quick answers |
| `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md` | Detailed guide |
| `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md` | Implementation details |
| `DIAGNOSTIC_ENGINE_DEPLOYMENT.md` | Deployment instructions |
| `DIAGNOSTIC_ENGINE_INDEX.md` | Complete index |

## Files Created

- `app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php`
- `app/Http/Controllers/Compliance/ComplianceDiagnosticController.php`
- `app/Console/Commands/RunComplianceDiagnostics.php`
- `app/Console/Commands/ValidateDiagnosticEngine.php`
- `app/Providers/DiagnosticServiceProvider.php`
- `resources/views/compliance/dashboard/testanalysisreport.blade.php`

## Scheduling

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('compliance:diagnose --save')
        ->daily()
        ->at('02:00');
}
```

## Performance

- Execution time: 2-5 seconds
- Real workflow execution (not mocked)
- Can run on-demand or scheduled
- Results stored in `storage/logs/`

## Troubleshooting

### No Test Data
```bash
php artisan db:seed
```

### Routes Not Working
```bash
php artisan route:clear
```

### Validation Failed
```bash
php artisan compliance:validate-diagnostics
```

## Next Steps

1. Verify installation: `php artisan compliance:validate-diagnostics`
2. Run diagnostics: `php artisan compliance:diagnose --save`
3. View dashboard: `/compliance/dashboard/testanalysisreport`
4. Use Amazon Q: Copy diagnostics and paste in chat
5. Schedule daily runs: Add to `app/Console/Kernel.php`

## Support

For detailed information, see:
- `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md` - Quick answers
- `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md` - Comprehensive guide
- `DIAGNOSTIC_ENGINE_INDEX.md` - Complete index

## Status

✓ Fully Implemented
✓ Production Ready
✓ Tested and Validated
✓ Documentation Complete

---

**Version:** 1.0
**Last Updated:** 2024-03-10
