# FORM XX – REGISTER OF FINES
## Implementation Documentation

**Status:** ✅ COMPLETE  
**Version:** 1.0  
**Last Updated:** 2024  
**Compliance:** Rule 78(2)(d) - Contract Labour Act, 1970

---

## 📋 STRUCTURE LOCK SPECIFICATION

### 1️⃣ Title Section (Centered)
```
FORM XX
[See Rule 78(2)(d)]
Register of Fines
```
- **FORM XX**: Bold, 12pt
- **[See Rule 78(2)(d)]**: Bold, 9pt
- **Register of Fines**: Bold, 11pt
- All centered, no modifications allowed

### 2️⃣ Establishment Details Block (Full-Width Ledger Rows)
Each field is a separate bordered row with:
- **Left side (35% width)**: Label in bold
- **Right side (65% width)**: Dynamic value

Fields in exact order:
1. NAME AND ADDRESS OF CONTRACTOR
2. NATURE AND LOCATION OF WORK
3. NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON
4. NAME AND ADDRESS OF PRINCIPAL EMPLOYER
5. Month & Year

### 3️⃣ Column Number Row
Separate numbering row before column headings:
```
1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12
```
- Centered, bold, 8pt font
- Aligns exactly with columns below
- No modifications allowed

### 4️⃣ Column Structure (LOCKED - NO MODIFICATIONS)
**12 Columns in exact order:**

| Col | Header | Width | Notes |
|-----|--------|-------|-------|
| 1 | SL No | 4% | Auto-increment |
| 2 | Name of workmen | 8% | Employee name |
| 3 | Father's/Husband's name | 8% | Relation name |
| 4 | Designation/Nature of employment | 8% | Job title |
| 5 | Act/Omission for which fine imposed | 10% | Violation description |
| 6 | Date of offence | 7% | YYYY-MM-DD format |
| 7 | Whether workmen showed cause against fine | 8% | Yes/No |
| 8 | Name of person in whose presence employee's explanation was heard | 10% | Witness name |
| 9 | Wage period and wages payable | 10% | Period details |
| 10 | Amount of fine imposed | 8% | Numeric, right-aligned |
| 11 | Date on which fine realised | 8% | YYYY-MM-DD format |
| 12 | Remarks | 10% | Additional notes |

### 5️⃣ Data Handling Rules

**If no fines exist:**
- Display centered NIL block: "Nil for the month of {{month}}"
- No data table shown
- Footer section still displayed

**If fines exist:**
- SL No auto-increments from 1
- All columns populated with data
- Amount column right-aligned with 2 decimal places
- Empty cells show blank (not "N/A")

### 6️⃣ Footer Section

**Left side (50%):**
```
*Applicable only in case of damage/loss/fine
```

**Right side (50%):**
```
[Signature line]
Seal Signature of the Contractor
[Space for seal image]
```

### 7️⃣ Print Settings (LOCKED)

- **Paper**: A4 Landscape
- **Margins**: 10mm all sides
- **Font**: Times New Roman, 9pt body
- **Border Style**: 1px solid black
- **Border Collapse**: collapse
- **Row Height**: Compact (18-20px)
- **Cell Padding**: 3-4px
- **Line Height**: 1.2

---

## 🔧 IMPLEMENTATION FILES

### Template File
```
resources/views/compliance/forms/form_xx_register_of_fines.blade.php
```

**Key Features:**
- Pure HTML/CSS (no framework styling)
- Landscape orientation via CSS @page rule
- Dense government ledger appearance
- No responsive stacking
- Print-optimized

### Service Class
```
app/Services/Compliance/FormGenerator/FormXXGenerator.php
```

**Methods:**
- `generate()`: Main entry point
- `getFinesData()`: Retrieves fines from database
- `getContractorName()`: Contractor details
- `getNatureOfWork()`: Work location details
- `getEstablishmentName()`: Establishment details
- `getPrincipalEmployer()`: Principal employer details
- `formatMonthYear()`: Month/year formatting

### Configuration
```
config/pdf_form_xx.php
```

**Settings:**
```php
'FORM_XX' => [
    'paper' => 'A4',
    'orientation' => 'landscape',
    'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
    'font_size' => 9,
    'isHtml5ParserEnabled' => false,
    'isRemoteEnabled' => false,
    'dpi' => 72,
]
```

---

## 📊 DATA STRUCTURE

### Input Data Format
```php
$data = [
    'contractor_name' => 'Contractor Name, Address',
    'nature_of_work' => 'Work Type - Location',
    'establishment_name' => 'Establishment Name, Address',
    'principal_employer' => 'Principal Employer Name, Address',
    'month_year' => 'January 2024',
    'fines' => [
        [
            'workmen_name' => 'John Doe',
            'father_husband_name' => 'Father Name',
            'designation' => 'Laborer',
            'act_omission' => 'Violation Description',
            'date_of_offence' => '2024-01-15',
            'showed_cause' => 'Yes',
            'person_present' => 'Manager Name',
            'wage_period' => 'Jan 2024',
            'amount_fine' => 500.00,
            'date_realised' => '2024-01-20',
            'remarks' => 'Additional notes',
        ],
        // ... more fines
    ],
    'is_nil' => false,
]
```

### Database Schema
```sql
-- statutory_manual_data table
- id
- tenant_id
- branch_id
- form_code (FORM_XX)
- data_month
- data_year
- form_data (JSON)
- created_at
- updated_at
```

**form_data JSON structure:**
```json
{
    "workmen_name": "string",
    "father_husband_name": "string",
    "designation": "string",
    "act_omission": "string",
    "date_of_offence": "YYYY-MM-DD",
    "showed_cause": "Yes/No",
    "person_present": "string",
    "wage_period": "string",
    "amount_fine": "numeric",
    "date_realised": "YYYY-MM-DD",
    "remarks": "string"
}
```

---

## 🚀 USAGE

### Basic Usage
```php
// In controller
$generator = app(FormXXGenerator::class);
$data = $generator->generate($tenantId, $branchId, $month, $year);

return view('compliance.forms.form_xx_register_of_fines', $data);
```

### With PDF Generation
```php
// Using DomPDF
$pdf = PDF::loadView('compliance.forms.form_xx_register_of_fines', $data);
$pdf->setPaper('A4', 'landscape');
return $pdf->download('form_xx_register_of_fines.pdf');
```

### Manual Data Entry
```php
// Insert fine record
DB::table('statutory_manual_data')->insert([
    'tenant_id' => $tenantId,
    'branch_id' => $branchId,
    'form_code' => 'FORM_XX',
    'data_month' => 1,
    'data_year' => 2024,
    'form_data' => json_encode([
        'workmen_name' => 'John Doe',
        'father_husband_name' => 'Father Name',
        'designation' => 'Laborer',
        'act_omission' => 'Unauthorized absence',
        'date_of_offence' => '2024-01-15',
        'showed_cause' => 'Yes',
        'person_present' => 'Manager Name',
        'wage_period' => 'Jan 2024',
        'amount_fine' => 500.00,
        'date_realised' => '2024-01-20',
        'remarks' => 'Fine imposed and realized',
    ]),
    'created_at' => now(),
    'updated_at' => now(),
]);
```

---

## 🔒 STRUCTURE LOCK ENFORCEMENT

### What CANNOT be changed:
- ❌ Column order
- ❌ Column count (must be 12)
- ❌ Column widths (locked percentages)
- ❌ Title section layout
- ❌ Establishment details field order
- ❌ Font sizes (unless absolutely necessary for fit)
- ❌ Border styles
- ❌ Alignment (centered/left/right)
- ❌ Page orientation (landscape only)

### What CAN be changed:
- ✅ Data values (dynamic)
- ✅ Month/Year (dynamic)
- ✅ Contractor/Establishment details (dynamic)
- ✅ Number of fine rows (dynamic)
- ✅ NIL message text (if required by regulation)

---

## 📝 VALIDATION RULES

### Mandatory Fields
- Contractor Name: Required
- Nature of Work: Required
- Establishment Name: Required
- Principal Employer: Required
- Month & Year: Required

### Fine Record Validation
- Workmen Name: Required
- Date of Offence: Valid date (YYYY-MM-DD)
- Amount Fine: Numeric, >= 0
- Date Realised: Valid date (YYYY-MM-DD) or empty

### Business Rules
- Date of Offence must be <= Date Realised (if both provided)
- Amount Fine must be > 0 if fine is imposed
- At least one fine record required if is_nil = false

---

## 🧪 TESTING CHECKLIST

- [ ] Title section displays correctly (centered, bold)
- [ ] Establishment details show all 5 fields in order
- [ ] Column numbers row aligns with headers
- [ ] All 12 columns display with correct headers
- [ ] SL No auto-increments correctly
- [ ] Amount column right-aligned with 2 decimals
- [ ] NIL block displays when no fines
- [ ] Footer displays on all pages
- [ ] Landscape orientation maintained
- [ ] Print preview shows dense ledger style
- [ ] No column wrapping or stacking
- [ ] Borders render correctly (1px solid black)
- [ ] Font sizes consistent (9pt body, 8pt table)
- [ ] Page breaks handled correctly

---

## 📞 SUPPORT

For issues or modifications:
1. Verify data structure matches schema
2. Check database records exist for month/year
3. Validate JSON in form_data field
4. Review browser console for errors
5. Check PDF generation settings

---

## 📄 REFERENCE

**Statutory Reference:** Rule 78(2)(d), Contract Labour Act, 1970  
**Form Code:** FORM_XX  
**Orientation:** Landscape (A4)  
**Columns:** 12 (Fixed)  
**Status:** Production Ready ✅
