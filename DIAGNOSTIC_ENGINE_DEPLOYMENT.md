# Deep Diagnostic Engine - Deployment Guide

## Installation

The Deep Diagnostic Engine has been fully implemented. All files are in place and ready to use.

## Verification

Verify the installation:

```bash
php artisan compliance:validate-diagnostics
```

Expected output:
```
✓ Engine Class
✓ Controller
✓ Command
✓ Service Provider
✓ Dashboard View
✓ Routes
✓ Documentation
```

## Quick Start

### 1. Run Diagnostics

```bash
php artisan compliance:diagnose
```

Output will show:
- System Health Score (0-100%)
- Component Summary
- Component Diagnostics Table
- Root Cause Analysis
- Execution Time

### 2. View Dashboard

Navigate to:
```
http://localhost/compliance/dashboard/testanalysisreport
```

Features:
- Real-time health score
- Component status
- Root cause analysis
- Copy to clipboard for Amazon Q

### 3. Save Report

```bash
php artisan compliance:diagnose --save
```

Report saved to: `storage/logs/diagnostic_report_YYYY-MM-DD_HH-MM-SS.json`

## API Usage

### Run Diagnostics
```bash
curl http://localhost/compliance/diagnostics/run
```

### Get Latest Report
```bash
curl http://localhost/compliance/diagnostics/latest
```

### Get Dashboard Data
```bash
curl http://localhost/compliance/diagnostics/dashboard
```

## Integration with Amazon Q

1. Run diagnostics and save:
   ```bash
   php artisan compliance:diagnose --save
   ```

2. View dashboard at `/compliance/dashboard/testanalysisreport`

3. Click "Copy Diagnostics for Amazon Q" button

4. Paste in Amazon Q chat:
   ```
   Fix these compliance system issues based on the root cause analysis:
   [paste diagnostics]
   ```

5. Amazon Q will generate fixes for detected issues

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

Then run:
```bash
php artisan schedule:work
```

## Health Score Interpretation

| Score | Status | Action |
|-------|--------|--------|
| 100% | Healthy | No action needed |
| 70-99% | Warning | Review and fix minor issues |
| <70% | Critical | Immediate action required |

## Common Scenarios

### Scenario 1: New Installation

1. Seed test data:
   ```bash
   php artisan db:seed
   ```

2. Run diagnostics:
   ```bash
   php artisan compliance:diagnose
   ```

3. Expected score: 100% (if all components are implemented)

### Scenario 2: After Code Changes

1. Run diagnostics:
   ```bash
   php artisan compliance:diagnose
   ```

2. Review root causes if score decreased

3. Use Amazon Q to fix issues

4. Re-run diagnostics to verify

### Scenario 3: Production Deployment

1. Run diagnostics:
   ```bash
   php artisan compliance:diagnose --save
   ```

2. Check health score >= 70%

3. Review root causes

4. Fix critical issues before deployment

5. Re-run diagnostics to verify

## Troubleshooting

### Command Not Found

```bash
php artisan list | grep compliance
```

Should show:
- `compliance:diagnose`
- `compliance:validate-diagnostics`

### No Test Data

```bash
php artisan db:seed
```

### Routes Not Working

Clear route cache:
```bash
php artisan route:clear
```

### Service Provider Not Registered

Check `bootstrap/providers.php` includes:
```php
App\Providers\DiagnosticServiceProvider::class,
```

## Files Deployed

| File | Location |
|------|----------|
| Engine | `app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php` |
| Controller | `app/Http/Controllers/Compliance/ComplianceDiagnosticController.php` |
| Command | `app/Console/Commands/RunComplianceDiagnostics.php` |
| Validator | `app/Console/Commands/ValidateDiagnosticEngine.php` |
| Provider | `app/Providers/DiagnosticServiceProvider.php` |
| Dashboard | `resources/views/compliance/dashboard/testanalysisreport.blade.php` |
| Routes | `routes/compliance.php` (updated) |
| Providers | `bootstrap/providers.php` (updated) |

## Documentation

| Document | Purpose |
|----------|---------|
| `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md` | Comprehensive guide |
| `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md` | Quick reference |
| `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md` | Implementation details |
| `DIAGNOSTIC_ENGINE_DEPLOYMENT.md` | This file |

## Performance

- **Execution Time:** 2-5 seconds
- **Test Data Required:** Yes
- **Real Workflows:** Yes (not mocked)
- **Frequency:** On-demand or daily

## Support

For issues:

1. Check documentation:
   - `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md`
   - `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md`

2. Run validation:
   ```bash
   php artisan compliance:validate-diagnostics
   ```

3. Review root cause analysis in dashboard

4. Check affected files

5. Follow recommended fixes

## Next Steps

1. **Verify Installation:**
   ```bash
   php artisan compliance:validate-diagnostics
   ```

2. **Run Diagnostics:**
   ```bash
   php artisan compliance:diagnose --save
   ```

3. **View Dashboard:**
   Navigate to `/compliance/dashboard/testanalysisreport`

4. **Use Amazon Q:**
   Copy diagnostics and paste in Amazon Q chat

5. **Schedule Daily Runs:**
   Add to `app/Console/Kernel.php`

## Success Criteria

✓ All 8 components tested
✓ Health score calculated accurately
✓ Root causes identified for failures
✓ Recommended fixes provided
✓ Amazon Q integration working
✓ Dashboard displaying correctly
✓ CLI commands functional
✓ API endpoints accessible

## Rollback

If needed to rollback:

1. Remove files:
   ```bash
   rm -rf app/Services/Compliance/Diagnostics/
   rm app/Http/Controllers/Compliance/ComplianceDiagnosticController.php
   rm app/Console/Commands/RunComplianceDiagnostics.php
   rm app/Console/Commands/ValidateDiagnosticEngine.php
   rm app/Providers/DiagnosticServiceProvider.php
   rm resources/views/compliance/dashboard/testanalysisreport.blade.php
   ```

2. Revert route changes in `routes/compliance.php`

3. Revert provider registration in `bootstrap/providers.php`

4. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan route:clear
   ```

## Support Contact

For issues or questions:
1. Review documentation
2. Check root cause analysis
3. Run validation command
4. Review affected files
5. Follow recommended fixes
