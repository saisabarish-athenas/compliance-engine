# IMMEDIATE ACTION CODE FIXES - CRITICAL FORMS

## PRIORITY 1: REMOVE BLADE CALCULATIONS (30 minutes)

### File: resources/views/compliance/forms/form_b.blade.php

**CURRENT (WRONG):**
```blade
@php
    $daysWorked = $row['total_days_worked'] ?? 0;
    $dailyRate = $daysWorked > 0 ? ($row['basic_earned'] ?? 0) / $daysWorked : 0;  // WRONG
    $others = ($row['hra_earned'] ?? 0);
    $otherCash = 0;
    $total = ($row['basic_earned'] ?? 0) + ($row['da_earned'] ?? 0) + ($row['overtime_wages'] ?? 0) + $others + $otherCash;  // WRONG
@endphp
```

**CORRECTED:**
```blade
@php
    $daysWorked = $row['total_days_worked'] ?? 0;
    $dailyRate = $row['daily_rate'] ?? 0;  // From service
    $others = $row['hra_earned'] ?? 0;
    $otherCash = 0;
    $total = $row['total_wages'] ?? 0;  // From service
    $deductions = 'PF: ' . number_format($row['pf_employee'] ?? 0, 2) . ', ESI: ' . number_format($row['esi_employee'] ?? 0, 2);
    if (($row['advances'] ?? 0) > 0) $deductions .= ', Adv: ' . number_format($row['advances'], 2);
    if (($row['fines'] ?? 0) > 0) $deductions .= ', Fine: ' . number_format($row['fines'], 2);
@endphp
```

---

## PRIORITY 2: ADD TAMIL NADU REFERENCES (2 hours)

### Global Rule Reference Mapping

Create: `config/tn_statutory_rules.php`

```php
<?php

return [
    // FACTORIES ACT FORMS
    'FORM_B' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 13',
        'rule' => 'Rule 26 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_10' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 59',
        'rule' => 'Rule 27 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_25' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 62',
        'rule' => 'Rule 28 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_12' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 67',
        'rule' => 'Rule 75 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_17' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 70',
        'rule' => 'Rule 76 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_2' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 79',
        'rule' => 'Rule 103 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_7' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 107 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_8' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 108 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_11' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 111 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_18' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 118 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_26' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 126 of the Tamil Nadu Factories Rules, 1950',
    ],
    'FORM_26A' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 88',
        'rule' => 'Rule 126A of the Tamil Nadu Factories Rules, 1950',
    ],
    'HAZARD_REG' => [
        'act' => 'Factories Act, 1948',
        'section' => 'Section 41B',
        'rule' => 'Rule 97A of the Tamil Nadu Factories Rules, 1950',
    ],
    
    // SHOPS & ESTABLISHMENTS FORMS
    'SHOPS_FORM_1' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 20 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_FORM_12' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 23 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_FORM_13' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 24 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_FORM_C' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 25 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_FORM_VI' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 26 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_FINES' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 27 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    'SHOPS_UNPAID' => [
        'act' => 'Tamil Nadu Shops and Establishments Act, 1947',
        'section' => 'Section 14',
        'rule' => 'Rule 28 of the Tamil Nadu Shops and Establishments Rules, 1948',
    ],
    
    // CLRA FORMS
    'FORM_XII' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 74 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XIII' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 75 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XIV' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 75A of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XVI' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 76 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XVII' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XIX' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 79 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XX' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 80 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XXI' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 81 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XXII' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 82 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XXIII' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 83 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XXIV' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 84 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'FORM_XXV' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 29',
        'rule' => 'Rule 85 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    'CLRA_LICENSE' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 12',
        'rule' => 'Rule 25 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
    
    // SOCIAL SECURITY FORMS
    'ESI_FORM_12' => [
        'act' => 'Employees State Insurance Act, 1948',
        'section' => 'Section 55',
        'rule' => 'Regulation 67 of the Employees State Insurance (General) Regulations, 1950',
    ],
    'EPF_INSPECTION' => [
        'act' => 'Employees Provident Funds and Miscellaneous Provisions Act, 1952',
        'section' => 'Section 17',
        'rule' => 'Paragraph 44 of the Employees Provident Funds Scheme, 1952',
    ],
    'CONTRACTOR_MASTER' => [
        'act' => 'Contract Labour (Regulation and Abolition) Act, 1970',
        'section' => 'Section 12',
        'rule' => 'Rule 21 of the Contract Labour (Regulation and Abolition) Central Rules, 1971',
    ],
];
```

### Update BaseFormGenerator to use config

```php
// app/Services/Compliance/FormGenerator/BaseFormGenerator.php

protected function getStatutoryReferences(): array
{
    $rules = config("tn_statutory_rules.{$this->formCode}");
    
    if (!$rules) {
        throw new \Exception("Statutory rules not configured for {$this->formCode}");
    }
    
    return [
        'act_reference' => "[Under {$rules['section']} of the {$rules['act']}]",
        'rule_reference' => "[See {$rules['rule']}]",
        'declaration' => $this->getDeclaration($rules),
    ];
}

protected function getDeclaration(array $rules): string
{
    $actName = $rules['act'];
    $ruleName = $rules['rule'];
    
    // Extract rule book name
    preg_match('/of the (.+)$/', $ruleName, $matches);
    $ruleBook = $matches[1] ?? 'applicable rules';
    
    return "Certified that the above register is maintained in accordance with the provisions of the {$actName} and the {$ruleBook}, and that the particulars entered therein are true to the best of my knowledge and belief.";
}

public function generate(int $tenantId, int $branchId, int $month, int $year, int $batchId): string
{
    // ... existing code ...
    
    $data = $this->prepareData($rawData);
    
    // Add statutory references
    $data['statutory'] = $this->getStatutoryReferences();
    
    // ... rest of code ...
}
```

### Update Blade templates to use statutory references

```blade
@section('act_reference', $statutory['act_reference'])
@section('rule_reference', $statutory['rule_reference'])

@section('declaration')
{{ $statutory['declaration'] }}
@endsection
```

---

## PRIORITY 3: SERVICE LAYER WAGE CALCULATIONS (1 day)

### File: app/Services/Compliance/FormGenerator/WageCalculationService.php

```php
<?php

namespace App\Services\Compliance\FormGenerator;

class WageCalculationService
{
    /**
     * Calculate daily rate as per Tamil Nadu standards
     * Formula: Basic Salary / 26 working days
     */
    public function calculateDailyRate(float $basicSalary): float
    {
        return $basicSalary / 26;
    }
    
    /**
     * Calculate basic wages
     * Formula: Daily Rate × Days Worked
     */
    public function calculateBasicWages(float $dailyRate, int $daysWorked): float
    {
        return $dailyRate * $daysWorked;
    }
    
    /**
     * Calculate overtime rate
     * Formula: (Daily Rate / 8) × 2
     */
    public function calculateOvertimeRate(float $dailyRate): float
    {
        $hourlyRate = $dailyRate / 8;
        return $hourlyRate * 2;
    }
    
    /**
     * Calculate overtime wages
     * Formula: Overtime Hours × Overtime Rate
     */
    public function calculateOvertimeWages(float $overtimeHours, float $overtimeRate): float
    {
        return $overtimeHours * $overtimeRate;
    }
    
    /**
     * Calculate gross wages
     * Formula: Basic + DA + HRA + Overtime + Other Allowances
     */
    public function calculateGrossWages(array $components): float
    {
        return ($components['basic'] ?? 0) +
               ($components['da'] ?? 0) +
               ($components['hra'] ?? 0) +
               ($components['overtime'] ?? 0) +
               ($components['other_allowances'] ?? 0);
    }
    
    /**
     * Calculate net wages
     * Formula: Gross Wages - Total Deductions
     */
    public function calculateNetWages(float $grossWages, float $totalDeductions): float
    {
        return $grossWages - $totalDeductions;
    }
    
    /**
     * Validate wage calculations
     */
    public function validateWageCalculation(array $data): array
    {
        $errors = [];
        
        // Validate daily rate
        $expectedDailyRate = $this->calculateDailyRate($data['basic_salary']);
        if (abs($expectedDailyRate - $data['daily_rate']) > 0.01) {
            $errors[] = "Daily rate mismatch: Expected {$expectedDailyRate}, Got {$data['daily_rate']}";
        }
        
        // Validate basic wages
        $expectedBasicWages = $this->calculateBasicWages($data['daily_rate'], $data['days_worked']);
        if (abs($expectedBasicWages - $data['basic_wages']) > 0.01) {
            $errors[] = "Basic wages mismatch: Expected {$expectedBasicWages}, Got {$data['basic_wages']}";
        }
        
        // Validate overtime
        if ($data['overtime_hours'] > 0) {
            $expectedOvertimeRate = $this->calculateOvertimeRate($data['daily_rate']);
            if (abs($expectedOvertimeRate - $data['overtime_rate']) > 0.01) {
                $errors[] = "Overtime rate mismatch: Expected {$expectedOvertimeRate}, Got {$data['overtime_rate']}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
```

### Update PayrollBasedFormGenerator to use WageCalculationService

```php
private function enrichFormBData(array $row, $record, array $rawData): array
{
    $wageCalc = app(WageCalculationService::class);
    
    $employee = DB::table('workforce_employee')
        ->select('name', 'designation', 'basic_salary')
        ->where('id', $record->employee_id)
        ->where('tenant_id', $rawData['tenant_id'])
        ->first();

    if ($employee) {
        $row['employee_name'] = $employee->name;
        $row['designation'] = $employee->designation;

        // Get days worked from attendance
        $daysWorked = DB::table('workforce_attendance')
            ->where('employee_id', $record->employee_id)
            ->where('tenant_id', $rawData['tenant_id'])
            ->whereBetween('attendance_date', [$rawData['period_start'], $rawData['period_end']])
            ->where('status', 'present')
            ->count();

        // Use WageCalculationService
        $dailyRate = $wageCalc->calculateDailyRate($employee->basic_salary);
        $basicWages = $wageCalc->calculateBasicWages($dailyRate, $daysWorked);
        
        // Calculate overtime if applicable
        $overtimeWages = 0;
        $overtimeRate = 0;
        if ($row['overtime_hours'] > 0) {
            $overtimeRate = $wageCalc->calculateOvertimeRate($dailyRate);
            $overtimeWages = $wageCalc->calculateOvertimeWages($row['overtime_hours'], $overtimeRate);
        }
        
        // Calculate gross and net
        $grossWages = $wageCalc->calculateGrossWages([
            'basic' => $basicWages,
            'da' => $row['da_earned'],
            'hra' => $row['hra_earned'],
            'overtime' => $overtimeWages,
        ]);
        
        $netWages = $wageCalc->calculateNetWages($grossWages, $row['total_deductions']);
        
        // Update row with calculated values
        $row['total_days_worked'] = $daysWorked;
        $row['daily_rate'] = $dailyRate;
        $row['basic_earned'] = $basicWages;
        $row['overtime_rate'] = $overtimeRate;
        $row['overtime_wages'] = $overtimeWages;
        $row['gross_salary'] = $grossWages;
        $row['total_wages'] = $grossWages;  // For Blade template
        $row['net_salary'] = $netWages;
        
        // Validate calculations
        $validation = $wageCalc->validateWageCalculation([
            'basic_salary' => $employee->basic_salary,
            'daily_rate' => $dailyRate,
            'days_worked' => $daysWorked,
            'basic_wages' => $basicWages,
            'overtime_hours' => $row['overtime_hours'],
            'overtime_rate' => $overtimeRate,
        ]);
        
        if (!$validation['valid']) {
            Log::warning("Wage calculation validation failed for {$employee->name}", $validation['errors']);
        }
    }

    return $row;
}
```

---

## PRIORITY 4: DATABASE MIGRATIONS (2 hours)

### Migration: add_mandatory_columns_to_employees

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workforce_employee', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('name');
            $table->enum('sex', ['Male', 'Female', 'Other'])->nullable()->after('father_name');
            $table->integer('age')->nullable()->after('sex');
        });
        
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            $table->string('father_name')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other'])->nullable();
            $table->integer('age')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('nature_of_work')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    public function down()
    {
        Schema::table('workforce_employee', function (Blueprint $table) {
            $table->dropColumn(['father_name', 'sex', 'age']);
        });
        
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            $table->dropColumn(['father_name', 'sex', 'age', 'permanent_address', 'nature_of_work', 'remarks']);
        });
    }
};
```

---

## TESTING CHECKLIST

After implementing fixes:

```bash
# Test wage calculations
php artisan test --filter=WageCalculationTest

# Test form generation
php artisan compliance:test-generation --all

# Validate statutory references
php artisan compliance:validate-statutory-references

# Check for Blade calculations
php artisan compliance:audit-blade-calculations
```

---

## DEPLOYMENT SEQUENCE

1. **Deploy config file** (tn_statutory_rules.php)
2. **Run database migrations**
3. **Deploy WageCalculationService**
4. **Update BaseFormGenerator**
5. **Update PayrollBasedFormGenerator**
6. **Update all Blade templates**
7. **Test critical forms** (FORM_B, FORM_XIII, SHOPS_FORM_12)
8. **Full regression test**
9. **Legal review**
10. **Production deployment**

**Estimated Time:** 3-4 hours for Priority 1-4 fixes
