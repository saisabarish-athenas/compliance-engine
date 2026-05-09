# FORM 10 - 14 Column Structure Reference

## Column Definitions and Data Sources

| Col | Header | Data Source | Calculation | Display |
|-----|--------|-------------|-------------|---------|
| 1 | No. in register | Loop index | `$index + 1` | Centered |
| 2 | Name | `$row['employee_name']` | From workforce_employee.name | Left-aligned |
| 3 | Department | `$row['designation']` | From workforce_employee.designation | Left-aligned |
| 4 | Dates on which overtime has been worked | Empty | Manual entry field | Centered |
| 5 | Extent of overtime on each occasion | `$row['overtime_hours']` | From payroll_entry.overtime_hours | Right-aligned, 2 decimals |
| 6 | Total overtime worked on production of piece-workers | `$row['piece_worker_overtime']` | `overtime_hours if is_piece_worker else 0` | Right-aligned, 2 decimals |
| 7 | Normal hours | Fixed | `8` (standard working hours) | Right-aligned |
| 8 | Normal rate of pay | `$row['normal_rate']` | `basic_salary / 26 / 8` | Right-aligned, 2 decimals |
| 9 | Overtime rate of pay | `$row['overtime_rate']` | `normal_rate × 2` | Right-aligned, 2 decimals |
| 10 | Normal earnings | `$row['normal_earnings']` | `daily_rate × 1` | Right-aligned, 2 decimals |
| 11 | Overtime earnings | `$row['overtime_wages']` | `overtime_rate × overtime_hours` | Right-aligned, 2 decimals |
| 12 | Cash equivalent of advantages through concessional sale of food-grains and other articles | `$row['food_grain_benefit']` | From payroll_entry.food_grain_benefit or 0 | Right-aligned, 2 decimals |
| 13 | Total earnings | Calculated | `normal_earnings + overtime_wages + food_grain_benefit` | Right-aligned, 2 decimals |
| 14 | Dates on which overtime payments made | Empty | Manual entry field | Centered |

## Totals Row Calculations

```
TOTAL Row (Row 14 in table):
- Columns 1-4: "TOTAL" label spanning 4 columns
- Column 5: SUM(overtime_hours)
- Column 6: SUM(piece_worker_overtime)
- Column 7: Empty
- Column 8: SUM(normal_rate)
- Column 9: SUM(overtime_rate)
- Column 10: SUM(normal_earnings)
- Column 11: SUM(overtime_wages)
- Column 12: SUM(food_grain_benefit)
- Column 13: SUM(normal_earnings + overtime_wages + food_grain_benefit)
- Column 14: Empty
```

## Nil Declaration

When `is_nil = true` (no overtime records):

```
[Single row spanning 14 columns]
"NO BODY IN THE ORGANIZATION HAS WORKED OVERTIME FOR THE MONTH OF {MONTH YEAR}"
```

## Header Fields (Above Table)

```
Row 1: Name of the Company | [tenant.name] | Name of the Contractor | [contractor_name]
Row 2: Total number of workers employed | [count($rows)] | Work location | [branch.address]
Row 3: Name of the Principal Employer | [principal_employer] | Month | [period]
```

## Blade Template Variables

### Required in $header:
- `$header['tenant']['name']` - Company name
- `$header['branch']['address']` - Work location
- `$header['period']` - Month and year (formatted)
- `$header['total_workers']` - Count of workers with overtime
- `$header['contractor_name']` - Contractor name (optional, defaults to 'N/A')
- `$header['principal_employer']` - Principal employer name (optional, defaults to tenant name)

### Required in $rows (array of records):
- `$row['employee_name']` - Worker name
- `$row['designation']` - Department/designation
- `$row['overtime_hours']` - Hours worked overtime
- `$row['normal_rate']` - Hourly rate (calculated)
- `$row['overtime_rate']` - Overtime hourly rate (calculated)
- `$row['normal_earnings']` - Daily earnings (calculated)
- `$row['overtime_wages']` - Overtime earnings (calculated)
- `$row['food_grain_benefit']` - Food grain benefit (optional, defaults to 0)
- `$row['is_piece_worker']` - Boolean flag for piece-worker status
- `$row['piece_worker_overtime']` - Overtime hours if piece-worker, else 0

### Required in $totals:
- `$totals['overtime_hours']` - Sum of column 5
- `$totals['piece_worker_overtime']` - Sum of column 6
- `$totals['normal_rate']` - Sum of column 8
- `$totals['overtime_rate']` - Sum of column 9
- `$totals['normal_earnings']` - Sum of column 10
- `$totals['overtime_wages']` - Sum of column 11
- `$totals['food_grain_benefit']` - Sum of column 12

### Required flags:
- `$is_nil` - Boolean, true if no overtime records

## Calculations in PayrollBasedFormGenerator

### enrichForm10Data() Method

```php
$basicSalary = $employee->basic_salary ?? 0;
$dailyRate = $basicSalary / 26;           // 26 working days per month
$hourlyRate = $dailyRate / 8;             // 8 hours per day
$overtimeHourlyRate = $hourlyRate * 2;    // Double time for overtime

$normalEarnings = $dailyRate * 1;
$overtimeEarnings = $overtimeHourlyRate * $overtimeHours;
$foodGrainBenefit = $record->food_grain_benefit ?? 0;

$isPieceWorker = $employee->is_piece_worker ?? false;
$pieceWorkerOvertime = $isPieceWorker ? $overtimeHours : 0;
```

## Piece-Worker Logic

```php
// In Blade template:
{{ $row['is_piece_worker'] ? number_format($row['overtime_hours'] ?? 0, 2) : '0' }}

// If is_piece_worker = true: Display overtime_hours
// If is_piece_worker = false: Display 0
```

## Layout Specifications

- **Page Size:** A4 Landscape
- **Margins:** 10mm top/bottom, 8mm left/right
- **Font Family:** Times New Roman
- **Body Font Size:** 10px
- **Table Font Size:** 9px
- **Header Font Size:** 11px
- **Line Height:** 1.2
- **Borders:** 1px solid #000
- **Cell Padding:** 2px 3px
- **Table Layout:** Fixed
- **Border Collapse:** Collapse
- **Color:** Black (#000)

## Column Widths (Percentage)

```
1: 3.5%   (No. in register)
2: 3.5%   (Name)
3: 5%     (Department)
4: 6%     (Dates worked)
5: 8%     (Extent of overtime)
6: 8%     (Piece-worker overtime)
7: 6%     (Normal hours)
8: 6%     (Normal rate)
9: 6%     (Overtime rate)
10: 6%    (Normal earnings)
11: 6%    (Overtime earnings)
12: 8%    (Food grain benefit)
13: 8%    (Total earnings)
14: 8%    (Payment dates)
```

## Footer

```
For [Company Name]

[30px margin-top]

Authorized Signatory
```

Right-aligned, font size 9px.
