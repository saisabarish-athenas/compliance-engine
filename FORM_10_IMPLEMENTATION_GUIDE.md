# FORM 10 Implementation Guide

## Quick Start

FORM 10 has been completely rebuilt to match the official Tamil Nadu Factories Rules statutory format. The implementation is automatic and requires no additional configuration.

## How It Works

### 1. Form Generation Flow

```
User requests FORM 10 PDF
    ↓
FormGeneratorFactory::make('FORM_10')
    ↓
PayrollBasedFormGenerator instantiated
    ↓
generate() method called with tenant_id, branch_id, month, year, batch_id
    ↓
prepareData() aggregates payroll records
    ↓
mapRecordToRow() processes each employee record
    ↓
enrichForm10Data() enriches with statutory columns
    ↓
calculateTotalsForForm() computes all totals
    ↓
Blade template renders with landscape orientation
    ↓
PDF generated with proper margins and fonts
    ↓
PDF returned to user
```

### 2. Data Enrichment Process

For each employee with overtime:

```
Raw Record (from payroll_entry)
├── employee_id
├── employee_name
├── designation
├── overtime_hours
├── food_grain_benefit
└── [other payroll fields]

enrichForm10Data() enriches with:
├── normal_rate = basic_salary / 26 / 8
├── overtime_rate = normal_rate × 2
├── normal_earnings = daily_rate × 1
├── overtime_wages = overtime_rate × overtime_hours
├── is_piece_worker = from workforce_employee.is_piece_worker
├── piece_worker_overtime = is_piece_worker ? overtime_hours : 0
└── [all original fields preserved]

Result: Complete row with all 14 columns populated
```

### 3. Nil Declaration

When no overtime records exist:

```
is_nil = true
    ↓
Blade template displays single row:
"NO BODY IN THE ORGANIZATION HAS WORKED OVERTIME FOR THE MONTH OF {MONTH YEAR}"
    ↓
Row spans all 14 columns
    ↓
Inside table grid with borders
```

## Database Requirements

No new tables or columns required. Uses existing fields:

**From workforce_employee:**
- `name` - Employee name
- `designation` - Department/designation
- `basic_salary` - For rate calculations
- `is_piece_worker` - For piece-worker logic

**From payroll_entry:**
- `overtime_hours` - Hours worked overtime
- `food_grain_benefit` - Optional benefit (defaults to 0)

**From branches:**
- `address` - Work location

**From tenants:**
- `name` - Company name

## Configuration

No configuration needed. The system automatically:

1. Detects FORM_10 in FormGeneratorFactory
2. Routes to PayrollBasedFormGenerator
3. Applies landscape orientation
4. Renders all 14 columns
5. Calculates statutory rates and earnings
6. Handles nil declarations

## Testing

### Test Case 1: With Overtime Records

```
Setup:
- Create employees with basic_salary
- Create payroll entries with overtime_hours
- Set some employees as is_piece_worker = true

Expected Output:
- All 14 columns populated
- Rates calculated correctly
- Piece-worker overtime shows hours (not 0)
- Non-piece-worker overtime shows 0 in column 6
- Totals row sums all numeric columns
- Landscape orientation
- Proper borders and spacing
```

### Test Case 2: Nil Declaration

```
Setup:
- Create employees but no overtime records

Expected Output:
- Single row spanning 14 columns
- Message: "NO BODY IN THE ORGANIZATION HAS WORKED OVERTIME FOR THE MONTH OF {MONTH YEAR}"
- Inside table grid with borders
- No data rows
```

### Test Case 3: Mixed Employees

```
Setup:
- Some employees with overtime
- Some without overtime
- Mix of piece-workers and regular workers

Expected Output:
- Only employees with overtime_hours > 0 appear
- Piece-workers show overtime hours in column 6
- Regular workers show 0 in column 6
- Totals reflect only employees with overtime
```

## Calculations Verification

### Normal Rate Calculation
```
basic_salary = 10,000
daily_rate = 10,000 / 26 = 384.62
hourly_rate = 384.62 / 8 = 48.08
```

### Overtime Rate Calculation
```
overtime_rate = hourly_rate × 2 = 48.08 × 2 = 96.15
```

### Normal Earnings
```
normal_earnings = daily_rate × 1 = 384.62
```

### Overtime Earnings
```
overtime_hours = 5
overtime_earnings = 96.15 × 5 = 480.75
```

### Total Earnings
```
total_earnings = 384.62 + 480.75 + 0 (food_grain_benefit) = 865.37
```

## Troubleshooting

### Issue: Columns showing 0 or blank

**Cause:** Missing employee data or payroll records

**Solution:**
- Verify workforce_employee records exist
- Verify payroll_entry records have overtime_hours > 0
- Check basic_salary is populated

### Issue: Piece-worker column always shows 0

**Cause:** is_piece_worker flag not set

**Solution:**
- Update workforce_employee.is_piece_worker = true for piece-workers
- Or modify enrichForm10Data() to use different logic

### Issue: Landscape orientation not working

**Cause:** PDF library configuration

**Solution:**
- Verify DomPDF is installed
- Check @page CSS rule in blade template
- Ensure size: A4 landscape is set

### Issue: Nil declaration not showing

**Cause:** is_nil flag not set correctly

**Solution:**
- Verify no payroll records exist for the period
- Check FormDataAggregator returns empty records array
- Verify is_nil = count($rows) === 0

## Performance Notes

- Landscape rendering adds minimal overhead
- 14 columns fit on A4 landscape with 9px font
- Totals calculation is O(n) where n = number of employees
- No additional database queries beyond standard payroll aggregation

## Compliance Checklist

✅ All 14 statutory columns present
✅ Proper header with company details
✅ Landscape orientation
✅ Tight spacing and borders
✅ Nil declaration row
✅ Footer with signatory block
✅ No database modifications
✅ No other forms affected
✅ Piece-worker logic implemented
✅ Calculations match statutory requirements

## Files Modified

1. `resources/views/compliance/forms/form_10.blade.php` - Blade template
2. `app/Services/Compliance/FormGenerator/PayrollBasedFormGenerator.php` - Data generator
3. `config/pdf_form_10.php` - PDF configuration (new)

## Files NOT Modified

- Database schema
- Migrations
- Models
- Controllers
- Other form templates
- Compliance engine
- Generator classes for other forms
- Payroll logic
- Any other compliance forms
