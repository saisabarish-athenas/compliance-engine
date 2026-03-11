# Deep Project Diagnostic Engine - Complete Index

## Overview

The Deep Project Diagnostic Engine is a comprehensive system audit tool that performs real workflow execution across all platform layers to detect root causes of failures and produce accurate system health scores.

**Status:** ✓ Fully Implemented and Ready to Use

## Quick Navigation

### For First-Time Users
1. Start with: [Quick Reference Guide](#quick-reference-guide)
2. Then read: [Deployment Guide](#deployment-guide)
3. Finally: [Run Your First Diagnostic](#run-your-first-diagnostic)

### For Developers
1. Read: [Implementation Summary](#implementation-summary)
2. Review: [Architecture](#architecture)
3. Check: [Files Created](#files-created)

### For Operations
1. Check: [Deployment Guide](#deployment-guide)
2. Review: [Scheduling](#scheduling)
3. Monitor: [Health Score](#health-score-interpretation)

## Documentation Files

### 1. Quick Reference Guide
**File:** `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md`

Quick start guide with:
- Common commands
- Health score weights
- Common issues & fixes
- API endpoints
- Troubleshooting

**When to use:** Need quick answers

### 2. Comprehensive Guide
**File:** `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md`

Complete documentation with:
- Architecture overview
- All 8 diagnostic tests
- Health score calculation
- Usage examples
- Troubleshooting
- Extending diagnostics

**When to use:** Need detailed information

### 3. Implementation Summary
**File:** `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md`

Implementation details with:
- Objective completion checklist
- Architecture overview
- All 8 components explained
- Health score calculation
- Integration with Amazon Q
- Files created
- Routes added

**When to use:** Understanding the implementation

### 4. Deployment Guide
**File:** `DIAGNOSTIC_ENGINE_DEPLOYMENT.md`

Deployment instructions with:
- Installation verification
- Quick start
- API usage
- Amazon Q integration
- Scheduling
- Troubleshooting
- Rollback procedures

**When to use:** Deploying or troubleshooting

## Quick Reference Guide

### Health Score Weights

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

### Health Score Interpretation

| Score | Status | Meaning |
|-------|--------|---------|
| 100% | Healthy | All components functioning correctly |
| 70-99% | Warning | System operational with minor issues |
| <70% | Critical | Critical issues requiring immediate attention |

## Run Your First Diagnostic

### Step 1: Verify Installation
```bash
php artisan compliance:validate-diagnostics
```

Expected: All checks pass ✓

### Step 2: Run Diagnostics
```bash
php artisan compliance:diagnose --save
```

Expected: Health score displayed with component status

### Step 3: View Dashboard
Navigate to:
```
http://localhost/compliance/dashboard/testanalysisreport
```

Expected: Dashboard displays health score and root causes

### Step 4: Use Amazon Q
1. Click "Copy Diagnostics for Amazon Q"
2. Paste in Amazon Q chat
3. Ask: "Fix these compliance system issues"
4. Amazon Q generates fixes

## Architecture

### Core Components

1. **ComplianceDiagnosticEngine**
   - Main diagnostic engine
   - Performs 8 comprehensive tests
   - Calculates weighted health score
   - Generates root-cause analysis

2. **ComplianceDiagnosticController**
   - HTTP API endpoints
   - Report storage and retrieval
   - Dashboard data provision

3. **RunComplianceDiagnostics Command**
   - CLI interface
   - Report display and storage
   - Scheduled execution support

4. **DiagnosticServiceProvider**
   - Dependency injection
   - Service registration

5. **Dashboard View**
   - Real-time health score display
   - Component status table
   - Root cause analysis
   - Amazon Q integration

### Diagnostic Tests (8 Components)

#### 1. Preview Pipeline (30%)
Tests: API Service → Generator → Blade Template → Preview Rendering

#### 2. Form Generators (15%)
Tests: All generator classes for prepareData() and structure

#### 3. Blade Templates (10%)
Tests: All templates for variable usage and safe output

#### 4. API Services (15%)
Tests: All API services for tenant/branch isolation

#### 5. Database Datasets (10%)
Tests: Required tables and record counts

#### 6. PDF Generation (10%)
Tests: PDF generation for multiple forms

#### 7. Inspection Pack (5%)
Tests: ZIP creation and PDF collection

#### 8. Security Isolation (5%)
Tests: Multi-tenant enforcement

## Files Created

| File | Purpose |
|------|---------|
| `app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php` | Main diagnostic engine |
| `app/Http/Controllers/Compliance/ComplianceDiagnosticController.php` | HTTP endpoints |
| `app/Console/Commands/RunComplianceDiagnostics.php` | CLI command |
| `app/Console/Commands/ValidateDiagnosticEngine.php` | Validation command |
| `app/Providers/DiagnosticServiceProvider.php` | Service provider |
| `resources/views/compliance/dashboard/testanalysisreport.blade.php` | Dashboard view |

## Routes Added

| Route | Method | Purpose |
|-------|--------|---------|
| `/compliance/diagnostics/run` | GET | Run diagnostics |
| `/compliance/diagnostics/latest` | GET | Get latest report |
| `/compliance/diagnostics/dashboard` | GET | Get dashboard data |
| `/compliance/dashboard/testanalysisreport` | GET | View dashboard |

## Commands Available

| Command | Purpose |
|---------|---------|
| `php artisan compliance:diagnose` | Run diagnostics |
| `php artisan compliance:diagnose --save` | Run and save report |
| `php artisan compliance:validate-diagnostics` | Validate installation |

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

## Integration with Amazon Q

1. Run diagnostics:
   ```bash
   php artisan compliance:diagnose --save
   ```

2. View dashboard at `/compliance/dashboard/testanalysisreport`

3. Click "Copy Diagnostics for Amazon Q"

4. Paste in Amazon Q chat with prompt:
   ```
   Fix these compliance system issues based on the root cause analysis
   ```

5. Amazon Q will generate fixes for detected issues

## Performance

- **Execution Time:** 2-5 seconds
- **Test Data Required:** Yes (tenant, branch, forms)
- **Real Workflows:** Yes (not mocked)
- **Report Storage:** JSON in storage/logs/
- **Frequency:** On-demand or daily

## Troubleshooting

### No Test Data
```bash
php artisan db:seed
```

### Missing Components
Create missing service/generator classes

### Blade Template Issues
Create template at `resources/views/compliance/forms/{form_code}.blade.php`

### Database Issues
```bash
php artisan migrate
```

### Routes Not Working
```bash
php artisan route:clear
```

## Success Criteria

✓ All 8 components tested
✓ Health score reflects real execution success
✓ Root causes identified for failures
✓ Recommended fixes provided
✓ Amazon Q can use diagnostics to fix issues
✓ Target score: 100% when all components function correctly

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

## Support Resources

| Resource | Location | Purpose |
|----------|----------|---------|
| Quick Reference | `DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md` | Quick answers |
| Comprehensive Guide | `DEEP_DIAGNOSTIC_ENGINE_GUIDE.md` | Detailed information |
| Implementation | `DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md` | Implementation details |
| Deployment | `DIAGNOSTIC_ENGINE_DEPLOYMENT.md` | Deployment instructions |
| This Index | `DIAGNOSTIC_ENGINE_INDEX.md` | Navigation and overview |

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

## Frequently Asked Questions

### Q: How often should I run diagnostics?
A: Daily via scheduler or on-demand after code changes

### Q: What does a health score of 85% mean?
A: System is operational with minor issues that should be reviewed

### Q: Can I use diagnostics in production?
A: Yes, it's designed for production use with real workflow execution

### Q: How do I fix issues found by diagnostics?
A: Use Amazon Q - copy diagnostics and ask it to fix the issues

### Q: Can I extend the diagnostics?
A: Yes, add custom tests to ComplianceDiagnosticEngine

### Q: Where are reports stored?
A: In `storage/logs/diagnostic_report_*.json`

### Q: How long does diagnostics take?
A: Typically 2-5 seconds depending on system load

### Q: What if I get a score of 0%?
A: Check if test data exists - run `php artisan db:seed`

## Contact & Support

For issues:
1. Check relevant documentation file
2. Run validation: `php artisan compliance:validate-diagnostics`
3. Review root cause analysis in dashboard
4. Check affected files
5. Follow recommended fixes

---

**Last Updated:** 2024-03-10
**Version:** 1.0
**Status:** Production Ready ✓
