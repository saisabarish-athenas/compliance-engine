# COMPLIANCE HEALTH SCORE - DOCUMENTATION

**Feature:** Real-time compliance health monitoring system  
**Status:** ✅ IMPLEMENTED  
**Date:** 2026-02-24

---

## OVERVIEW

The Compliance Health Score provides a real-time assessment of an organization's compliance status based on five key metrics, each weighted at 20%.

---

## SCORING SYSTEM

### Total Score Calculation
**Formula:** Sum of all 5 metrics (each 0-20%)

### Status Levels
| Score Range | Status | Color | Meaning |
|-------------|--------|-------|---------|
| 85-100% | Excellent | Green | Full compliance |
| 60-84% | Good | Yellow | Acceptable with room for improvement |
| 0-59% | Risk | Red | Immediate attention required |

---

## METRICS BREAKDOWN

### 1. Payroll Available (20%)
**Checks:** Payroll entries exist for the current period

**Scoring:**
- 20% = Payroll data exists
- 0% = No payroll data

**Query:** Joins workforce_payroll_entry with workforce_payroll_cycle, checks for period overlap

### 2. Payroll Locked (20%)
**Checks:** Payroll period is locked (prevents modifications)

**Scoring:**
- 20% = Payroll locked
- 10% = Payroll not locked (if table doesn't exist, defaults to 20%)
- 0% = N/A

**Query:** Checks payroll_locks table for locked periods

### 3. Forms Generated (20%)
**Checks:** Compliance batches created for the period

**Scoring:**
- 20% = At least one batch exists
- 0% = No batches created

**Query:** Counts compliance_execution_batches for current month/year

### 4. No Generation Errors (20%)
**Checks:** All form generations succeeded without errors

**Scoring:**
- 20% = No failed generations
- 10% = Some failed generations
- 0% = No batches to check

**Query:** Checks compliance_generation_logs for failed status

### 5. Required Forms Present (20%)
**Checks:** Critical forms have been generated

**Required Forms:**
- FORM_B (Wage Register)
- FORM_XIII (Contract Labour Register)
- ESI_FORM_12 (Accident Register)
- EPF_INSPECTION (Inspection Register)

**Scoring:**
- 20% = All 4 required forms generated
- 15% = 3 forms generated
- 10% = 2 forms generated
- 5% = 1 form generated
- 0% = No forms generated

**Query:** Counts distinct successful form_codes in compliance_generation_logs

---

## IMPLEMENTATION

### PHASE 1 — Service

**File:** `app/Services/Compliance/ComplianceHealthService.php`

**Class:** `ComplianceHealthService`

**Main Method:**
```php
public function calculateScore(int $tenantId, int $month, int $year): array
```

**Returns:**
```php
[
    'percentage' => 80.0,
    'status' => 'Good',
    'breakdown' => [
        'Payroll Available' => 20,
        'Payroll Locked' => 20,
        'Forms Generated' => 20,
        'No Generation Errors' => 20,
        'Required Forms Present' => 0,
    ]
]
```

### PHASE 2 — Dashboard Card

**Location:** Top of dashboard, after Organization Information Card

**Features:**
- Large percentage display
- Color-coded status badge
- Detailed breakdown list
- Color-coded metric badges

**Colors:**
- Green (≥85%): Excellent
- Yellow (60-84%): Good
- Red (<60%): Risk

**Breakdown Badges:**
- Green: Score ≥18%
- Yellow: Score 10-17%
- Red: Score <10%

---

## USAGE

### For Users:
1. Login to dashboard
2. View Compliance Health Score card at top
3. Check overall percentage and status
4. Review breakdown to identify weak areas
5. Take action on low-scoring metrics

### For Developers:
```php
// Calculate health score
$healthService = app(\App\Services\Compliance\ComplianceHealthService::class);
$score = $healthService->calculateScore($tenantId, $month, $year);

// Access results
$percentage = $score['percentage']; // 80.0
$status = $score['status']; // "Good"
$breakdown = $score['breakdown']; // Array of metrics
```

---

## DASHBOARD DISPLAY

### Card Structure:
```
┌─────────────────────────────────────────────┐
│ 💚 Compliance Health Score                 │
├─────────────────────────────────────────────┤
│                                             │
│   80%          Score Breakdown:             │
│   Good         ✅ 20% Payroll Available     │
│                ✅ 20% Payroll Locked        │
│                ✅ 20% Forms Generated       │
│                ✅ 20% No Generation Errors  │
│                ❌ 0% Required Forms Present │
│                                             │
└─────────────────────────────────────────────┘
```

---

## TECHNICAL DETAILS

### Dependencies:
- Laravel DB facade
- Carbon for date handling
- Eloquent models (optional)

### Database Tables Used:
1. `workforce_payroll_entry`
2. `workforce_payroll_cycle`
3. `payroll_locks` (optional)
4. `compliance_execution_batches`
5. `compliance_generation_logs`

### Performance:
- 5 database queries per calculation
- Queries are optimized with proper indexes
- Results calculated on-demand (no caching)

### Error Handling:
- Graceful degradation if tables don't exist
- Null-safe operations
- Default values for missing data

---

## EXAMPLE SCENARIOS

### Scenario 1: Perfect Compliance
```
Score: 100%
Status: Excellent
- Payroll Available: 20%
- Payroll Locked: 20%
- Forms Generated: 20%
- No Generation Errors: 20%
- Required Forms Present: 20%
```

### Scenario 2: Good Compliance
```
Score: 80%
Status: Good
- Payroll Available: 20%
- Payroll Locked: 20%
- Forms Generated: 20%
- No Generation Errors: 20%
- Required Forms Present: 0%
```

### Scenario 3: At Risk
```
Score: 40%
Status: Risk
- Payroll Available: 0%
- Payroll Locked: 20%
- Forms Generated: 20%
- No Generation Errors: 0%
- Required Forms Present: 0%
```

---

## BENEFITS

1. **Real-time Monitoring:** Instant visibility into compliance status
2. **Actionable Insights:** Breakdown identifies specific issues
3. **Proactive Management:** Catch problems before they escalate
4. **Visual Clarity:** Color-coded system for quick assessment
5. **Comprehensive:** Covers all critical compliance aspects

---

## FUTURE ENHANCEMENTS

- [ ] Historical trend tracking
- [ ] Email alerts for low scores
- [ ] Customizable metric weights
- [ ] Additional metrics (attendance, incidents, etc.)
- [ ] Score comparison across branches
- [ ] Automated recommendations
- [ ] Score caching for performance
- [ ] Export score reports

---

## TROUBLESHOOTING

### Score shows 0%
**Cause:** No data for current period  
**Solution:** Create payroll and generate forms for current month

### Payroll Available shows 0% but data exists
**Cause:** Period mismatch  
**Solution:** Verify payroll_cycle period_from/period_to dates match current month

### Required Forms shows 0% but forms generated
**Cause:** Forms not in required list or generation failed  
**Solution:** Check compliance_generation_logs for status='success' and correct form_codes

---

## FILES MODIFIED

1. `app/Services/Compliance/ComplianceHealthService.php` - CREATED
2. `app/Http/Controllers/ComplianceExecutionController.php` - Added health score calculation
3. `resources/views/compliance/dashboard.blade.php` - Added health score card

---

## CHANGELOG

**v1.0 - 2026-02-24**
- Initial implementation
- 5 metrics with 20% weight each
- Color-coded status system
- Dashboard card with breakdown
- Real-time calculation

---

**Feature Status:** ✅ PRODUCTION READY  
**Tested:** ✅ Yes  
**Documented:** ✅ Yes
