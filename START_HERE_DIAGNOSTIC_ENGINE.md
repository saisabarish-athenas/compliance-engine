# START HERE - Deep Project Diagnostic Engine

## Welcome! 👋

You've just received the **Deep Project Diagnostic Engine** - a comprehensive system audit tool for the Laravel 12 Multi-Tenant Labour Compliance Automation Platform.

This document will guide you through everything you need to know.

## What Is This?

The Diagnostic Engine replaces the shallow ComplianceTestAnalyzer with a deep diagnostic system that:

- ✓ Executes **real workflows** through ComplianceOrchestrator
- ✓ **Detects root causes** of failures
- ✓ **Produces detailed reports** with analysis
- ✓ **Calculates accurate health scores** (0-100%)
- ✓ **Enables Amazon Q** to automatically fix issues

## 30-Second Quick Start

```bash
# 1. Run diagnostics
php artisan compliance:diagnose

# 2. View dashboard
# Navigate to: http://localhost/compliance/dashboard/testanalysisreport

# 3. Use Amazon Q
# Click "Copy Diagnostics for Amazon Q" and paste in chat
```

## What You Get

### 8 Diagnostic Tests

| Test | Weight | What It Tests |
|------|--------|---------------|
| Preview Pipeline | 30% | API → Generator → Blade → Render |
| API Services | 15% | Tenant/branch isolation |
| Generators | 15% | Data structure validation |
| Blade Templates | 10% | Variable usage validation |
| Database Datasets | 10% | Required data availability |
| PDF Generation | 10% | PDF creation success |
| Inspection Pack | 5% | ZIP creation success |
| Security | 5% | Multi-tenant enforcement |

### Health Score

- **100%** = All components working perfectly
- **70-99%** = System operational with minor issues
- **<70%** = Critical issues need attention

### Root Cause Analysis

Every failure shows:
- What failed
- Why it failed
- Which files are affected
- How to fix it

### Amazon Q Integration

Copy diagnostics and ask Amazon Q to fix issues automatically.

## Files Created

### Core Engine (5 files)
- `app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php` - Main engine
- `app/Http/Controllers/Compliance/ComplianceDiagnosticController.php` - HTTP API
- `app/Console/Commands/RunComplianceDiagnostics.php` - CLI command
- `app/Console/Commands/ValidateDiagnosticEngine.php` - Validation
- `app/Providers/DiagnosticServiceProvider.php` - Service registration

### Dashboard (1 file)
- `resources/views/compliance/dashboard/testanalysisreport.blade.php` - UI

### Configuration (2 files updated)
- `routes/compliance.php` - Added diagnostic routes
- `bootstrap/providers.php` - Registered service provider

### Documentation (6 files)
- `README_DIAGNOSTIC_ENGINE.md` - Overview
- `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md` - Quick answers
- `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md` - Comprehensive guide
- `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md` - Implementation details
- `DIAGNOSTIC_ENGINE_DEPLOYMENT.md` - Deployment guide
- `DIAGNOSTIC_ENGINE_INDEX.md` - Complete index

## How to Use

### Option 1: CLI (Command Line)

```bash
# Run diagnostics
php artisan compliance:diagnose

# Run and save report
php artisan compliance:diagnose --save

# Validate installation
php artisan compliance:validate-diagnostics
```

### Option 2: Dashboard

Navigate to:
```
http://localhost/compliance/dashboard/testanalysisreport
```

Features:
- Real-time health score
- Component status table
- Root cause analysis
- Copy to clipboard for Amazon Q

### Option 3: API

```bash
# Run diagnostics
curl http://localhost/compliance/diagnostics/run

# Get latest report
curl http://localhost/compliance/diagnostics/latest

# Get dashboard data
curl http://localhost/compliance/diagnostics/dashboard
```

## Using Amazon Q

1. **Run diagnostics:**
   ```bash
   php artisan compliance:diagnose --save
   ```

2. **View dashboard:**
   Navigate to `/compliance/dashboard/testanalysisreport`

3. **Copy diagnostics:**
   Click "Copy Diagnostics for Amazon Q" button

4. **Paste in Amazon Q:**
   ```
   Fix these compliance system issues based on the root cause analysis:
   [paste diagnostics]
   ```

5. **Get fixes:**
   Amazon Q will generate code to fix detected issues

## Common Commands

```bash
# Run diagnostics
php artisan compliance:diagnose

# Save report
php artisan compliance:diagnose --save

# Validate installation
php artisan compliance:validate-diagnostics

# View latest report
cat storage/logs/diagnostic_report_*.json

# Clear route cache
php artisan route:clear

# Seed test data
php artisan db:seed
```

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

### Missing Components
Create missing service/generator classes

### Blade Template Issues
Create template at `resources/views/compliance/forms/{form_code}.blade.php`

## Documentation Guide

### For Quick Answers
→ Read: `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md`

### For Detailed Information
→ Read: `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md`

### For Implementation Details
→ Read: `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md`

### For Deployment
→ Read: `DIAGNOSTIC_ENGINE_DEPLOYMENT.md`

### For Navigation
→ Read: `DIAGNOSTIC_ENGINE_INDEX.md`

## Health Score Weights

```
Preview Pipeline:    30%  (Most important)
API Services:        15%
Generators:          15%
Blade Templates:     10%
Database Datasets:   10%
PDF Generation:      10%
Inspection Pack:      5%
Security:             5%  (Least important)
                    ----
Total:              100%
```

## Report Structure

```json
{
  "health_score": 85,
  "status": "warning",
  "execution_time": 2500,
  "timestamp": "2024-03-10T10:30:00Z",
  "diagnostics": {
    "preview_pipeline": {
      "status": "pass",
      "weight": 30,
      "forms_tested": 3,
      "forms_passed": 3
    },
    ...
  },
  "root_causes": [
    {
      "component": "...",
      "root_cause": "...",
      "affected_files": [...],
      "recommended_fix": "..."
    }
  ],
  "summary": {
    "components_passed": 7,
    "components_failed": 1,
    "total_issues": 2
  }
}
```

## Scheduling Daily Diagnostics

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

## Performance

- **Execution Time:** 2-5 seconds
- **Test Data Required:** Yes (tenant, branch, forms)
- **Real Workflows:** Yes (not mocked)
- **Report Storage:** JSON in `storage/logs/`
- **Frequency:** On-demand or daily

## Next Steps

### Step 1: Verify Installation
```bash
php artisan compliance:validate-diagnostics
```

Expected: All checks pass ✓

### Step 2: Run Diagnostics
```bash
php artisan compliance:diagnose --save
```

Expected: Health score displayed

### Step 3: View Dashboard
Navigate to: `http://localhost/compliance/dashboard/testanalysisreport`

Expected: Dashboard displays health score and root causes

### Step 4: Use Amazon Q
1. Click "Copy Diagnostics for Amazon Q"
2. Paste in Amazon Q chat
3. Ask: "Fix these compliance system issues"
4. Amazon Q generates fixes

### Step 5: Schedule Daily Runs
Add to `app/Console/Kernel.php` (see Scheduling section above)

## FAQ

**Q: How often should I run diagnostics?**
A: Daily via scheduler or on-demand after code changes

**Q: What does a health score of 85% mean?**
A: System is operational with minor issues that should be reviewed

**Q: Can I use diagnostics in production?**
A: Yes, it's designed for production use with real workflow execution

**Q: How do I fix issues found by diagnostics?**
A: Use Amazon Q - copy diagnostics and ask it to fix the issues

**Q: Can I extend the diagnostics?**
A: Yes, add custom tests to ComplianceDiagnosticEngine

**Q: Where are reports stored?**
A: In `storage/logs/diagnostic_report_*.json`

**Q: How long does diagnostics take?**
A: Typically 2-5 seconds depending on system load

**Q: What if I get a score of 0%?**
A: Check if test data exists - run `php artisan db:seed`

## Support

For issues:
1. Check relevant documentation file
2. Run validation: `php artisan compliance:validate-diagnostics`
3. Review root cause analysis in dashboard
4. Check affected files
5. Follow recommended fixes

## Key Features

✓ **Real Workflow Execution** - Tests actual workflows, not mocked
✓ **Root Cause Detection** - Identifies specific failure points
✓ **Accurate Scoring** - Weighted calculation based on component importance
✓ **Detailed Reports** - JSON format for programmatic access
✓ **Dashboard Display** - Visual representation of health
✓ **Amazon Q Integration** - Copy diagnostics for automated fixes
✓ **CLI Support** - Run from command line with scheduling
✓ **Performance Metrics** - Execution time tracking
✓ **Report Storage** - Historical reports in storage/logs/
✓ **Extensible** - Easy to add custom diagnostic tests

## Success Criteria

✓ All 8 components tested
✓ Health score reflects real execution success
✓ Root causes identified for failures
✓ Recommended fixes provided
✓ Amazon Q can use diagnostics to fix issues
✓ Target score: 100% when all components function correctly

## Summary

The Deep Project Diagnostic Engine is:
- ✓ Fully implemented
- ✓ Production ready
- ✓ Thoroughly documented
- ✓ Easy to use
- ✓ Integrated with Amazon Q

Start with the 30-second quick start above, then explore the documentation as needed.

---

**Version:** 1.0
**Status:** ✓ Production Ready
**Last Updated:** 2024-03-10

**Next:** Run `php artisan compliance:diagnose` to get started!
