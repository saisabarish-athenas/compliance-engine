# FORM 10 - Statutory Format Reconstruction

## Overview
FORM 10 (Overtime Muster Roll for Exempted Workers) has been completely rebuilt to match the official Tamil Nadu Factories Rules statutory format with all 14 mandatory columns.

## Files Modified

### 1. Blade Template
**File:** `resources/views/compliance/forms/form_10.blade.php`

**Changes:**
- Replaced simplified layout with official statutory format
- Added boxed header with company details, contractor name, total workers, work location, principal employer, and month
- Implemented landscape orientation (A4 landscape)
- Reduced margins to 10mm top/bottom, 8mm left/right
- Set font size to 10px body, 9px table, 11px headers
- Applied tight spacing and border-collapse: collapse
- Removed all responsive styling and modern UI elements

**Header Structure:**
```
The Tamil Nadu Factories Rules
FORM 10
(Prescribed under Rule 78)
Overtime muster roll for exempted workers

[Bordered header grid with 6 fields in 3 rows × 2 columns]
```

**All 14 Statutory Columns:**
1. No. in register
2. Name
3. Department
4. Dates on which overtime has been worked
5. Extent of overtime on each occasion
6. Total overtime worked on production of piece-workers
7. Normal hours
8. Normal rate of pay
9. Overtime rate of pay
10. Normal earnings
11. Overtime earnings
12. Cash equivalent of advantages through concessional sale of food-grains and other articles
13. Total earnings
14. Dates on which overtime payments made

**Nil Declaration:**
- Full-width row with message: "NO BODY IN THE ORGANIZATION HAS WORKED OVERTIME FOR THE MONTH OF {Month Year}"
- Displayed inside table grid with borders

**Footer:**
- Right-aligned signatory block
- "For [Company Name]"
- "Authorized Signatory"

### 2. Data Generator
**File:** `app/Services/Compliance/FormGenerator/PayrollBasedFormGenerator.php`

**Changes:**
- Added `enrichForm10Data()` method to map payroll data to statutory columns
- Added `setForm10Defaults()` method for fallback values
- Enhanced `prepareData()` to include FORM 10 specific header fields:
  - `total_workers`: Count of employees with overtime
  - `contractor_name`: From raw data or 'N/A'
  - `principal_employer`: From raw data or tenant name
- Enhanced `calculateTotalsForForm()` to compute FORM 10 specific totals:
  - `normal_rate`: Sum of hourly rates
  - `overtime_rate`: Sum of overtime rates (2x normal)
  - `normal_earnings`: Sum of daily earnings
  - `food_grain_benefit`: Sum of food grain benefits
  - `piece_worker_overtime`: Sum of piece-worker overtime hours

**Data Mapping Logic:**
- **Normal Rate:** `basic_salary / 26 / 8` (hourly rate)
- **Overtime Rate:** `normal_rate × 2`
- **Normal Earnings:** `daily_rate × 1`
- **Overtime Earnings:** `overtime_rate × overtime_hours`
- **Piece-Worker Logic:** Display overtime hours only if `is_piece_worker` flag is true, else display 0
- **Food Grain Benefit:** From `food_grain_benefit` field or 0
- **Total Earnings:** `normal_earnings + overtime_wages + food_grain_benefit`

### 3. PDF Configuration
**File:** `config/pdf_form_10.php`

**Configuration:**
```php
[
    'paper' => 'A4',
    'orientation' => 'landscape',
    'margins' => ['top' => 10, 'right' => 8, 'bottom' => 10, 'left' => 8],
    'font_size' => 10,
    'table_font_size' => 9,
    'header_font_size' => 11,
    'border_color' => '#000000',
    'border_width' => 1,
    'cell_padding' => 2,
    'enable_page_numbers' => false,
    'dpi' => 72,
]
```

## Compliance with Requirements

✅ **A) Header Reconstruction**
- Boxed header layout with all required fields
- Tight spacing and bordered table structure
- No modern UI styling

✅ **B) Table Structure**
- All 14 statutory columns present
- Columns numbered 1-14 in header row
- Column descriptions in second header row
- Blank cells for unavailable data

✅ **C) Data Mapping**
- Uses existing payroll and overtime data
- Derives normal/overtime rates from basic salary
- Calculates earnings without modifying database
- Displays blank cells when data unavailable

✅ **D) Piece-Worker Logic**
- Conditional display: shows overtime hours only if `is_piece_worker = true`
- Otherwise displays 0
- No database modifications

✅ **E) Nil Declaration Row**
- Full-width row with statutory message
- Inside table grid with borders
- Month/year from header period

✅ **F) Layout Requirements**
- Landscape orientation (A4)
- Fixed table layout
- border-collapse: collapse
- 1px solid #000 borders
- 10-11px font sizes
- No responsive styling
- Compact government register style

✅ **G) Footer Block**
- Right-aligned signatory section
- "For [Company Name]"
- "Authorized Signatory"
- Inside bordered structure

## Database Impact
**NONE** - No database schema, migrations, models, controllers, or other forms modified.

## Data Flow
1. PayrollBasedFormGenerator receives raw payroll records
2. enrichForm10Data() enriches each record with statutory columns
3. calculateTotalsForForm() computes totals for all columns
4. Blade template renders with landscape orientation and all 14 columns
5. PDF generated with proper margins and font sizes

## Testing Checklist
- [ ] Generate FORM 10 with overtime records
- [ ] Verify all 14 columns display correctly
- [ ] Check nil declaration displays when no overtime
- [ ] Verify landscape orientation in PDF
- [ ] Confirm header fields populate correctly
- [ ] Test piece-worker logic (0 vs overtime hours)
- [ ] Verify totals row calculations
- [ ] Check footer signatory block alignment
- [ ] Validate border and spacing match statutory format
