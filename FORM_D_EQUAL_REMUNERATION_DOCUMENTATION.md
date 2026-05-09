# FORM 'D' – EQUAL REMUNERATION REGISTER
## Implementation Documentation

**Status:** ✅ COMPLETE  
**Version:** 1.0  
**Statutory Reference:** Rule 6, Equal Remuneration Rules, 1976  
**Form Code:** FORM_D_ER

---

## 📋 FORM STRUCTURE

### Header Section
```
FORM 'D'
(See rule 6)
Register to be maintained by the employer under Rule 6 of the Equal Remuneration Rules, 1976.
```

### Employer Information Section
8 rows with label-value pairs:
1. Name of the Company
2. Name of the Contractor
3. Total number of workers employed
4. Total number of men workers employed
5. Total number of women workers employed
6. Work location
7. Name of the Principal Employer
8. Month & Year

### Data Table (10 Columns - LOCKED)

| Col | Header | Data Source | Notes |
|-----|--------|-------------|-------|
| 1 | Category of workers | workforce_employee.designation | Job category |
| 2 | Brief description of work | workforce_employee.designation | Same as category |
| 3 | No. of men employed | COUNT(gender='Male') | Gender filter |
| 4 | No. of women employed | COUNT(gender='Female') | Gender filter |
| 5 | Rate of remuneration paid | AVG(gross_salary) | Average by designation |
| 6 | Basic wage or salary | AVG(basic_earned) | Average by designation |
| 7 | Dearness allowance | AVG(da_earned) | Average by designation |
| 8 | House rent allowance | AVG(hra_earned) | Average by designation |
| 9 | Other allowance | AVG(other_allowances) | Average by designation |
| 10 | Cash value of concessional supply | 0 (optional) | Placeholder |

### Column Number Row
```
1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10
```
Appears before column headers, bold, centered.

### Empty Data Behavior
If no women workers exist:
```
No Women Workers Employed on [Month] [Year]
```
Centered across all columns.

### Signature Area
```html
<div class="signature-space"></div>
```
Empty space for authorized signatory and seal (80px height).

---

## 🔧 IMPLEMENTATION FILES

### Template File
```
resources/views/compliance/forms/form_d_equal_remuneration.blade.php
```

**Key Features:**
- A4 Landscape orientation
- 10-column table structure
- Employer information grid
- Gender-based employee counting
- NIL message for no women workers
- Signature space placeholder

### Service Class
```
app/Services/Compliance/FormGenerator/FormDERGenerator.php
```

**Methods:**
- `generate()`: Main entry point
- `getEmployeesWithPayroll()`: Joins workforce_employee and workforce_payroll_entry
- `aggregateByDesignation()`: Groups by designation and gender
- `hasWomenWorkers()`: Checks if women workers exist
- `getCompanyName()`: From tenants table
- `getContractorName()`: From contractors table
- `getWorkLocation()`: From branches table
- `getPrincipalEmployer()`: From tenants table
- `getMonthName()`: Formats month number to name

---

## 📊 DATA MAPPING

### Source Tables

**workforce_employee**
```sql
- id
- tenant_id
- branch_id
- designation
- name
- gender (Male/Female/Not Specified)
- status (active/inactive)
```

**workforce_payroll_entry**
```sql
- id
- employee_id
- gross_salary
- basic_earned
- da_earned
- hra_earned
- other_allowances
```

### Join Condition
```sql
workforce_employee.id = workforce_payroll_entry.employee_id
```

### Filters
- `tenant_id` = Current tenant
- `branch_id` = Current branch
- `status` = 'active'
- `gender` IN ('Male', 'Female')

### Aggregation
- **Men Count**: COUNT WHERE gender = 'Male'
- **Women Count**: COUNT WHERE gender = 'Female'
- **Averages**: AVG(salary_component) GROUP BY designation

---

## 📝 DATA STRUCTURE

### Input Data Format
```php
$data = [
    'company_name' => 'Company Name',
    'contractor_name' => 'Contractor Name',
    'total_workers' => 45,
    'total_men' => 30,
    'total_women' => 15,
    'work_location' => 'City, State',
    'principal_employer' => 'Principal Employer Name',
    'month' => 'January',
    'year' => 2024,
    'rows' => [
        [
            'category' => 'Laborer',
            'description' => 'Laborer',
            'men_count' => 20,
            'women_count' => 10,
            'rate_remuneration' => 15000.00,
            'basic_wage' => 10000.00,
            'da' => 2000.00,
            'hra' => 2000.00,
            'other_allowance' => 1000.00,
            'cash_value' => 0.00,
        ],
        // ... more rows by designation
    ],
    'has_women_workers' => true,
]
```

---

## 🚀 USAGE

### Basic Usage
```php
// In controller
$generator = app(FormDERGenerator::class);
$data = $generator->generate($tenantId, $branchId, $month, $year);

return view('compliance.forms.form_d_equal_remuneration', $data);
```

### With PDF Generation
```php
// Using DomPDF
$pdf = PDF::loadView('compliance.forms.form_d_equal_remuneration', $data);
$pdf->setPaper('A4', 'landscape');
return $pdf->download('form_d_equal_remuneration.pdf');
```

### Data Retrieval Example
```php
// Get employees for a specific month/year
$employees = DB::table('workforce_employee as we')
    ->leftJoin('workforce_payroll_entry as wpe', 'we.id', '=', 'wpe.employee_id')
    ->where('we.tenant_id', $tenantId)
    ->where('we.branch_id', $branchId)
    ->where('we.status', 'active')
    ->select('we.designation', 'we.gender', 'wpe.gross_salary', 'wpe.basic_earned')
    ->get();
```

---

## ✅ VALIDATION RULES

### Mandatory Fields
- Company Name: Required
- Contractor Name: Required
- Work Location: Required
- Principal Employer: Required
- Month & Year: Required

### Employee Data Validation
- Designation: Required
- Gender: Must be 'Male', 'Female', or 'Not Specified'
- Status: Must be 'active'
- Salary Components: Numeric, >= 0

### Business Rules
- At least one active employee required
- If women_count = 0, display NIL message
- Averages calculated only for active employees
- All salary components must be non-negative

---

## 🧪 TESTING CHECKLIST

- [ ] Header displays correctly (FORM 'D', rule reference, subtitle)
- [ ] All 8 employer information fields display
- [ ] Month and year format correctly
- [ ] Column number row displays (1-10)
- [ ] All 10 column headers display
- [ ] Men count calculated correctly (gender = Male)
- [ ] Women count calculated correctly (gender = Female)
- [ ] Salary averages calculated by designation
- [ ] NIL message displays when no women workers
- [ ] Signature space renders (80px height)
- [ ] Landscape orientation maintained
- [ ] All cells have borders
- [ ] Font size 11px for body, 10px for table
- [ ] Numeric values right-aligned with 2 decimals

---

## 🔒 CONSTRAINTS

### What CANNOT be changed
- ❌ Column count (must be 10)
- ❌ Column order
- ❌ Column headers
- ❌ Employer information field order
- ❌ Page orientation (landscape only)
- ❌ Table structure

### What CAN be changed
- ✅ Data values (dynamic)
- ✅ Number of rows (based on designations)
- ✅ NIL message text (if required)
- ✅ Signature space height

---

## 📞 SUPPORT

For issues:
1. Verify workforce_employee records exist
2. Check workforce_payroll_entry records for the month/year
3. Validate gender field values
4. Ensure employee status = 'active'
5. Check database joins are working

---

## 📄 REFERENCE

**Statutory Reference:** Rule 6, Equal Remuneration Rules, 1976  
**Form Code:** FORM_D_ER  
**Orientation:** Landscape (A4)  
**Columns:** 10 (Fixed)  
**Status:** Production Ready ✅
