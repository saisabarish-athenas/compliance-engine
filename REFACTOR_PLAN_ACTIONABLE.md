# REFACTOR PLAN - TAMIL NADU COMPLIANCE

## PHASE 1: CRITICAL LEGAL FIXES (Priority: URGENT)

### Task 1.1: Tamil Nadu State Rules Adaptation
**Time: 2 days**  
**Files: 36 blade templates**

**Action:** Update all act/rule references

```blade
<!-- FACTORIES ACT FORMS -->
@section('rule_reference', '[See Rule 26 of the Tamil Nadu Factories Rules, 1950]')

<!-- SHOPS ACT FORMS -->
@section('act_reference', '[Under Tamil Nadu Shops and Establishments Act, 1947]')
@section('rule_reference', '[See Rule 23 of the Tamil Nadu Shops and Establishments Rules, 1948]')

<!-- CLRA FORMS (Central Act, but add Tamil Nadu adaptation note) -->
@section('rule_reference', '[See Rule 76 of the Contract Labour (Regulation and Abolition) Central Rules, 1971 as applicable in Tamil Nadu]')
```

**Specific Rule Numbers:**
```
FORM_B: Rule 26, TN Factories Rules, 1950
FORM_10: Rule 27, TN Factories Rules, 1950
FORM_25: Rule 28, TN Factories Rules, 1950
FORM_12: Rule 75, TN Factories Rules, 1950
FORM_17: Rule 76, TN Factories Rules, 1950
FORM_2: Rule 103, TN Factories Rules, 1950
FORM_7: Rule 107, TN Factories Rules, 1950
FORM_8: Rule 108, TN Factories Rules, 1950

SHOPS_FORM_1: Rule 20, TN S&E Rules, 1948
SHOPS_FORM_12: Rule 23, TN S&E Rules, 1948
SHOPS_FORM_13: Rule 24, TN S&E Rules, 1948
SHOPS_FORM_C: Rule 25, TN S&E Rules, 1948
SHOPS_FORM_VI: Rule 26, TN S&E Rules, 1948
```

---

### Task 1.2: Fix Wage Calculation Logic
**Time: 1 day**  
**File: PayrollBasedFormGenerator.php**

**Current Issue:**
```php
// WRONG - Calculates daily rate from earned wages
$dailyRate = $daysWorked > 0 ? ($row['basic_earned'] ?? 0) / $daysWorked : 0;
```

**Required Fix:**
```php
private function enrichFormBData(array $row, $record, array $rawData): array
{
    $employee = DB::table('workforce_employee')
        ->select('name', 'designation', 'basic_salary')
        ->where('id', $record->employee_id)
        ->first();

    if ($employee) {
        // CORRECT: Daily rate from basic salary
        $dailyRate = $employee->basic_salary / 26;
        
        // Calculate days from attendance
        $daysWorked = DB::table('workforce_attendance')
            ->where('employee_id', $record->employee_id)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->where('status', 'present')
            ->count();
        
        // CORRECT: Basic wages = rate × days
        $basicWages = $dailyRate * $daysWorked;
        
        // Validate calculation
        if (abs($basicWages - ($row['basic_earned'] ?? 0)) > 1.0) {
            Log::warning("Wage calculation mismatch", [
                'employee' => $employee->name,
                'calculated' => $basicWages,
                'payroll' => $row['basic_earned']
            ]);
        }
        
        $row['daily_rate'] = $dailyRate;
        $row['total_days_worked'] = $daysWorked;
        $row['basic_earned'] = $basicWages;
        
        // Recalculate totals
        $row['gross_salary'] = $basicWages + $row['da_earned'] + 
                               $row['hra_earned'] + $row['overtime_wages'];
        $row['net_salary'] = $row['gross_salary'] - $row['total_deductions'];
    }
    
    return $row;
}
```

**Apply to:** FORM_B, FORM_XVI, SHOPS_FORM_12

---

### Task 1.3: Update Declaration Wording
**Time: 1 day**  
**Files: All 36 blade templates**

**Standard Template:**
```blade
@section('declaration')
<div class="declaration-text">
    Certified that the above register is maintained in accordance with the provisions of the {{ $actName }} and the {{ $rulesName }}, and that the particulars entered therein are true to the best of my knowledge and belief.
</div>
@endsection
```

**Specific Examples:**
```blade
<!-- FORM_B -->
@section('declaration')
Certified that the above register is maintained in accordance with the provisions of the Factories Act, 1948 and the Tamil Nadu Factories Rules, 1950, and that the particulars entered therein are true to the best of my knowledge and belief.
@endsection

<!-- SHOPS_FORM_12 -->
@section('declaration')
Certified that the above register is maintained in accordance with the provisions of the Tamil Nadu Shops and Establishments Act, 1947 and the Tamil Nadu Shops and Establishments Rules, 1948, and that the particulars entered therein are true to the best of my knowledge and belief.
@endsection

<!-- FORM_XIII -->
@section('declaration')
Certified that the above register is maintained in accordance with the provisions of the Contract Labour (Regulation and Abolition) Act, 1970 and the Contract Labour (Regulation and Abolition) Central Rules, 1971, and that the particulars entered therein are true to the best of my knowledge and belief.
@endsection
```

---

### Task 1.4: Add Mandatory Columns
**Time: 1 day**  
**Files: FORM_XIII, FORM_XVI, FORM_10, FORM_25, FORM_12, FORM_17**

**FORM_XIII - Complete Structure:**
```blade
<table>
    <thead>
        <tr>
            <th rowspan="2">Sl. No.</th>
            <th rowspan="2">Name of Workman</th>
            <th rowspan="2">Father's/Husband's Name</th>
            <th rowspan="2">Sex</th>
            <th rowspan="2">Age</th>
            <th rowspan="2">Permanent Address</th>
            <th rowspan="2">Name of Contractor</th>
            <th rowspan="2">Nature of Employment</th>
            <th colspan="2">Period of Employment</th>
            <th rowspan="2">Rate of Wages</th>
            <th rowspan="2">Signature/Thumb Impression</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $row['worker_name'] }}</td>
            <td>{{ $row['father_name'] ?? '-' }}</td>
            <td class="text-center">{{ $row['sex'] ?? '-' }}</td>
            <td class="text-center">{{ $row['age'] ?? '-' }}</td>
            <td>{{ $row['address'] ?? '-' }}</td>
            <td>{{ $row['contractor_name'] }}</td>
            <td>{{ $row['nature_of_work'] ?? '-' }}</td>
            <td class="text-center">{{ $row['deployment_start'] }}</td>
            <td class="text-center">{{ $row['deployment_end'] ?? 'Continuing' }}</td>
            <td class="text-right">{{ number_format($row['wage_rate'], 2) }}</td>
            <td></td>
            <td>{{ $row['remarks'] ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

**Database Changes Required:**
```php
// Add to contract_labour_deployment migration
$table->string('father_name')->nullable();
$table->enum('sex', ['Male', 'Female', 'Other'])->nullable();
$table->integer('age')->nullable();
$table->text('permanent_address')->nullable();
$table->string('nature_of_work')->nullable();
```

---

## PHASE 2: FORMAT STANDARDIZATION (Priority: HIGH)

### Task 2.1: Create Single Tamil Nadu Layout
**Time: 1 day**  
**File: resources/views/compliance/layouts/tn_statutory_base.blade.php**

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('form_title')</title>
    <style>
        @page {
            margin: 15mm 10mm;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 8pt;
            }
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .statutory-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .form-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }
        
        .act-reference {
            font-size: 9pt;
            margin: 5px 0;
            font-style: italic;
        }
        
        .rule-reference {
            font-size: 9pt;
            margin: 5px 0;
        }
        
        .establishment-info {
            margin: 15px 0 20px 0;
            font-size: 9pt;
            line-height: 1.6;
            border: 1px solid #000;
            padding: 10px;
        }
        
        .establishment-info table {
            width: 100%;
            border: none;
        }
        
        .establishment-info td {
            border: none;
            padding: 3px 5px;
        }
        
        .establishment-label {
            font-weight: bold;
            width: 180px;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 5px 7px;
            text-align: left;
            vertical-align: middle;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .totals-row {
            font-weight: bold;
            background-color: #e8e8e8;
        }
        
        .nil-block {
            text-align: center;
            padding: 40px;
            border: 2px solid #000;
            margin: 30px 0;
            font-weight: bold;
            font-size: 12pt;
        }
        
        .signature-block {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .declaration-text {
            margin: 25px 0;
            font-size: 9pt;
            line-height: 1.7;
            text-align: justify;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #fafafa;
        }
        
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 30px;
        }
        
        .signature-table td {
            border: none;
            padding: 10px;
            vertical-align: top;
        }
        
        .signature-left {
            width: 40%;
            text-align: left;
        }
        
        .signature-right {
            width: 60%;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 60px 0 10px 0;
            display: inline-block;
        }
        
        .signature-label {
            font-size: 9pt;
            margin-top: 8px;
            line-height: 1.5;
        }
        
        .seal-placeholder {
            margin-top: 15px;
            font-size: 8pt;
            font-style: italic;
        }
    </style>
    @yield('additional_styles')
</head>
<body>
    <div class="statutory-header">
        <div class="form-title">@yield('form_title')</div>
        <div class="act-reference">@yield('act_reference')</div>
        <div class="rule-reference">@yield('rule_reference')</div>
    </div>
    
    <div class="establishment-info">
        @yield('establishment_info')
    </div>
    
    @yield('content')
    
    @if(!$is_nil)
    <div class="signature-block">
        <div class="declaration-text">
            @yield('declaration')
        </div>
        
        @yield('signature_block')
    </div>
    @endif
</body>
</html>
```

---

### Task 2.2: Standardize Serial Number Format
**Time: 0.5 days**  
**Action:** Replace all variations with "Sl. No."

```blade
<!-- STANDARD FORMAT -->
<th class="col-sno">Sl. No.</th>
```

---

### Task 2.3: Standardize NIL Return Format
**Time: 0.5 days**

```blade
@if($is_nil)
    <div class="nil-block">
        NIL RETURN<br>
        No entries for the period {{ $header['period'] }}
    </div>
@else
```

---

## PHASE 3: PERFORMANCE OPTIMIZATION (Priority: MEDIUM)

### Task 3.1: Optimize SHOPS_FORM_13
**Time: 1 day**

**Current Issue:** 172 MB, 6.08s for 930 records

**Solution:** Monthly summary instead of daily records

```php
// In FormDataAggregator or specific generator
public function aggregateAttendanceSummary($tenantId, $branchId, $month, $year)
{
    return DB::table('workforce_attendance as wa')
        ->join('workforce_employee as we', 'wa.employee_id', '=', 'we.id')
        ->select(
            'we.employee_code',
            'we.name',
            'we.designation',
            DB::raw('SUM(CASE WHEN wa.status = "present" THEN 1 ELSE 0 END) as present_days'),
            DB::raw('SUM(CASE WHEN wa.status = "absent" THEN 1 ELSE 0 END) as absent_days'),
            DB::raw('SUM(CASE WHEN wa.status = "leave" THEN 1 ELSE 0 END) as leave_days'),
            DB::raw('SUM(CASE WHEN wa.status = "half_day" THEN 1 ELSE 0 END) as half_days'),
            DB::raw('COUNT(*) as total_days')
        )
        ->where('wa.tenant_id', $tenantId)
        ->whereYear('wa.attendance_date', $year)
        ->whereMonth('wa.attendance_date', $month)
        ->groupBy('we.id', 'we.employee_code', 'we.name', 'we.designation')
        ->orderBy('we.employee_code')
        ->get();
}
```

**Expected Result:** < 20 MB, < 1s

---

### Task 3.2: Add Database Indexes
**Time: 0.5 days**

```php
// Migration: add_compliance_indexes
Schema::table('workforce_attendance', function (Blueprint $table) {
    $table->index(['employee_id', 'attendance_date', 'status'], 'idx_attendance_lookup');
    $table->index(['tenant_id', 'attendance_date'], 'idx_tenant_date');
});

Schema::table('workforce_payroll_entry', function (Blueprint $table) {
    $table->index(['tenant_id', 'created_at'], 'idx_payroll_period');
    $table->index(['employee_id', 'created_at'], 'idx_employee_payroll');
});

Schema::table('contract_labour_deployment', function (Blueprint $table) {
    $table->index(['tenant_id', 'deployment_start'], 'idx_deployment_period');
    $table->index(['contractor_id', 'deployment_start'], 'idx_contractor_deployment');
});
```

---

### Task 3.3: Optimize Query Chunking
**Time: 0.5 days**

**Already implemented, verify effectiveness:**
```php
// In FormDataAggregator
$query->orderBy($table . '.id')->chunk(500, function($records) use (&$data) {
    $data = $data->merge($records);
});
```

---

## PHASE 4: TESTING & VALIDATION (Priority: HIGH)

### Task 4.1: Create Validation Test Suite
**Time: 1 day**

```php
// tests/Feature/ComplianceFormValidationTest.php
class ComplianceFormValidationTest extends TestCase
{
    /** @test */
    public function form_b_has_correct_tamil_nadu_references()
    {
        $pdf = $this->generateForm('FORM_B', 4, 4, 1, 2026);
        
        $this->assertStringContainsString('Tamil Nadu Factories Rules, 1950', $pdf);
        $this->assertStringContainsString('Rule 26', $pdf);
    }
    
    /** @test */
    public function form_b_wage_calculation_is_correct()
    {
        $employee = Employee::factory()->create(['basic_salary' => 26000]);
        $attendance = Attendance::factory()->count(20)->create([
            'employee_id' => $employee->id,
            'status' => 'present'
        ]);
        
        $pdf = $this->generateForm('FORM_B', ...);
        
        $expectedDailyRate = 26000 / 26; // 1000
        $expectedBasicWages = 1000 * 20; // 20000
        
        $this->assertStringContainsString(number_format($expectedDailyRate, 2), $pdf);
        $this->assertStringContainsString(number_format($expectedBasicWages, 2), $pdf);
    }
    
    /** @test */
    public function all_forms_have_proper_declarations()
    {
        $forms = FormGeneratorFactory::getSupportedForms();
        
        foreach ($forms as $formCode) {
            $pdf = $this->generateForm($formCode, ...);
            $this->assertStringContainsString('Certified that', $pdf);
            $this->assertStringNotContainsString('Rule XX', $pdf);
        }
    }
}
```

---

## IMPLEMENTATION CHECKLIST

### Week 1: Critical Fixes
- [ ] Day 1-2: Update all Tamil Nadu rule references (36 forms)
- [ ] Day 3: Fix wage calculation logic (3 forms)
- [ ] Day 4: Update declaration wording (36 forms)
- [ ] Day 5: Add mandatory columns (6 forms)

### Week 2: Standardization
- [ ] Day 6: Create unified TN layout template
- [ ] Day 7: Migrate all forms to new layout
- [ ] Day 8: Standardize formatting (serial numbers, NIL returns)
- [ ] Day 9: Performance optimization
- [ ] Day 10: Database indexes and query optimization

### Week 3: Testing & Deployment
- [ ] Day 11: Create validation test suite
- [ ] Day 12: Run comprehensive tests
- [ ] Day 13: Legal review with TN labour consultant
- [ ] Day 14: Documentation and deployment
- [ ] Day 15: Buffer for fixes

---

## SUCCESS CRITERIA

1. ✅ All 36 forms reference Tamil Nadu-specific rules
2. ✅ No "Rule XX" placeholders remain
3. ✅ Wage calculations follow government formula
4. ✅ All mandatory columns present
5. ✅ Declarations match statutory wording
6. ✅ Performance: All forms < 2s, < 50 MB
7. ✅ Test coverage: 100% for critical forms
8. ✅ Legal sign-off obtained

**Target Compliance Score: 95%+**
