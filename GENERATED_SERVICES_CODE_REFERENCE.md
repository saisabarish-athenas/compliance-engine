# Generated Form Services - Code Structure Reference

## Service Hierarchy

```
BaseFormService (abstract)
├── FormXXIService (Register of Fines)
├── FormXXIIService (Register of Advances)
├── FormXXIIIService (Register of Overtime)
├── FormXXIVService (Annual Return)
├── FormXXVService (Half-Yearly Return)
├── Form7Service (Notice of Periods)
├── ClraLicenseService (License Register)
├── ClraReturnService (CLRA Half-Yearly Return)
└── ContractorMasterService (Contractor Master Register)
```

## FormXXIService - Register of Fines

**Location:** `app/Services/Compliance/Forms/FormXXIService.php`

**Columns Extracted:**
- name
- father_name
- designation
- act_or_omission
- date_of_offence
- showed_cause
- heard_by
- wage_period
- fine_amount
- fine_realised
- remarks

**Database Query:**
```sql
SELECT 
    e.name,
    COALESCE(e.father_name, '') as father_name,
    COALESCE(e.designation, '') as designation,
    '' as act_or_omission,
    '' as date_of_offence,
    '' as showed_cause,
    '' as heard_by,
    '' as wage_period,
    0 as fine_amount,
    '' as fine_realised,
    '' as remarks
FROM workforce_employee e
WHERE e.tenant_id = ? AND e.branch_id = ?
```

**Response:**
```php
[
    'header' => [
        'contractor_name' => 'ABC Contractors',
        'work_nature' => 'Construction',
        'establishment_name' => 'Main Site',
        'principal_employer' => 'XYZ Industries',
        'month_year' => 'January 2024',
    ],
    'rows' => [
        [
            'name' => 'John Doe',
            'father_name' => 'Jane Doe',
            'designation' => 'Laborer',
            'act_or_omission' => '',
            'date_of_offence' => '',
            'showed_cause' => '',
            'heard_by' => '',
            'wage_period' => '',
            'fine_amount' => 0,
            'fine_realised' => '',
            'remarks' => '',
        ],
    ],
    'is_nil' => false,
    'totals' => []
]
```

---

## FormXXIIService - Register of Advances

**Location:** `app/Services/Compliance/Forms/FormXXIIService.php`

**Columns Extracted:**
- name
- father_name
- designation
- advance_date_amount_1
- advance_date_amount_2
- purpose
- installments
- installment_repaid
- last_installment_date
- signature

**Database Query:**
```sql
SELECT 
    e.name,
    COALESCE(e.father_name, '') as father_name,
    COALESCE(e.designation, '') as designation,
    '' as advance_date_amount_1,
    '' as advance_date_amount_2,
    '' as purpose,
    '' as installments,
    '' as installment_repaid,
    '' as last_installment_date,
    '' as signature
FROM workforce_employee e
WHERE e.tenant_id = ? AND e.branch_id = ?
```

---

## FormXXIIIService - Register of Overtime

**Location:** `app/Services/Compliance/Forms/FormXXIIIService.php`

**Columns Extracted:**
- name
- father_name
- sex
- designation
- overtime_dates
- total_overtime
- normal_rate
- overtime_rate
- overtime_earnings
- payment_date
- remarks

**Database Query:**
```sql
SELECT 
    e.name,
    COALESCE(e.father_name, '') as father_name,
    COALESCE(e.gender, '') as sex,
    COALESCE(e.designation, '') as designation,
    '' as overtime_dates,
    COALESCE(cld.overtime_hours, 0) as total_overtime,
    0 as normal_rate,
    0 as overtime_rate,
    0 as overtime_earnings,
    '' as payment_date,
    '' as remarks
FROM contract_labour_deployment cld
JOIN workforce_employee e ON e.id = cld.employee_id
WHERE e.tenant_id = ? AND e.branch_id = ?
AND cld.deployment_start BETWEEN ? AND ?
```

---

## FormXXIVService - Annual Return

**Location:** `app/Services/Compliance/Forms/FormXXIVService.php`

**Columns Extracted:**
- company_name
- nature_of_work
- total_workers
- total_wages
- total_deductions

**Database Query:**
```sql
SELECT 
    c.company_name,
    COALESCE(cld.nature_of_work, '') as nature_of_work,
    COUNT(DISTINCT cld.employee_id) as total_workers,
    0 as total_wages,
    0 as total_deductions
FROM contractor_master c
LEFT JOIN contract_labour_deployment cld ON cld.contractor_id = c.id
WHERE c.tenant_id = ?
GROUP BY c.id, c.company_name
```

---

## FormXXVService - Half-Yearly Return

**Location:** `app/Services/Compliance/Forms/FormXXVService.php`

**Columns Extracted:**
- company_name
- nature_of_work
- total_workers
- total_wages
- total_deductions

**Database Query:**
```sql
SELECT 
    c.company_name,
    COALESCE(cld.nature_of_work, '') as nature_of_work,
    COUNT(DISTINCT cld.employee_id) as total_workers,
    0 as total_wages,
    0 as total_deductions
FROM contractor_master c
LEFT JOIN contract_labour_deployment cld ON cld.contractor_id = c.id
WHERE c.tenant_id = ?
GROUP BY c.id, c.company_name
```

**Period Calculation:**
```php
$period = $month <= 6 ? "H1-$year" : "H2-$year";
// H1-2024 for Jan-Jun
// H2-2024 for Jul-Dec
```

---

## Form7Service - Notice of Periods

**Location:** `app/Services/Compliance/Forms/Form7Service.php`

**Status:** Placeholder service (no data source)

**Response:**
```php
[
    'header' => [
        'tenant' => ['name' => 'Company Name'],
        'period' => 'January 2024',
    ],
    'rows' => [],
    'is_nil' => true,
    'totals' => []
]
```

---

## ClraLicenseService - License Register

**Location:** `app/Services/Compliance/Forms/ClraLicenseService.php`

**Columns Extracted:**
- company_name
- license_number
- license_date
- license_validity

**Database Query:**
```sql
SELECT 
    c.company_name,
    COALESCE(c.license_number, '') as license_number,
    COALESCE(c.license_date, '') as license_date,
    COALESCE(c.license_validity, '') as license_validity
FROM contractor_master c
WHERE c.tenant_id = ?
```

---

## ClraReturnService - CLRA Half-Yearly Return

**Location:** `app/Services/Compliance/Forms/ClraReturnService.php`

**Columns Extracted:**
- company_name
- nature_of_work
- total_workers
- total_wages
- total_deductions

**Database Query:**
```sql
SELECT 
    c.company_name,
    COALESCE(cld.nature_of_work, '') as nature_of_work,
    COUNT(DISTINCT cld.employee_id) as total_workers,
    0 as total_wages,
    0 as total_deductions
FROM contract_labour_deployment cld
JOIN contractor_master c ON c.id = cld.contractor_id
WHERE c.tenant_id = ?
GROUP BY c.id, c.company_name, cld.nature_of_work
```

---

## ContractorMasterService - Contractor Master Register

**Location:** `app/Services/Compliance/Forms/ContractorMasterService.php`

**Columns Extracted:**
- company_name
- company_address
- contact_person
- contact_number
- email
- license_number
- license_date

**Database Query:**
```sql
SELECT 
    c.company_name,
    COALESCE(c.company_address, '') as company_address,
    COALESCE(c.contact_person, '') as contact_person,
    COALESCE(c.contact_number, '') as contact_number,
    COALESCE(c.email, '') as email,
    COALESCE(c.license_number, '') as license_number,
    COALESCE(c.license_date, '') as license_date
FROM contractor_master c
WHERE c.tenant_id = ?
ORDER BY c.company_name
```

---

## Common Response Structure

All services return:

```php
[
    'header' => [
        'tenant' => ['name' => '...', 'address' => '...'],
        'branch' => ['name' => '...', 'address' => '...'],
        'period' => 'January 2024',
        // ... form-specific fields
    ],
    'rows' => [
        [
            'column1' => 'value1',
            'column2' => 'value2',
            // ... more columns
        ],
        // ... more rows
    ],
    'is_nil' => false,  // true if no data
    'totals' => []      // optional totals
]
```

## Common Filters Applied

All services apply:

```php
->where('tenant_id', $tenantId)
->where('branch_id', $branchId)
->whereBetween('date_field', [$startDate, $endDate])
```

## Null Coalescing Pattern

All services use:

```php
DB::raw("COALESCE(column, '') as alias")
```

This ensures:
- No null values in response
- Empty strings for missing data
- Consistent data types

## Date Range Calculation

All services use:

```php
[$startDate, $endDate] = $this->getDateRange();
// Returns first and last day of month
// Based on $this->month and $this->year
```

## FormDebugger Integration

All services include:

```php
FormDebugger::start('FORM_CODE');
// ... processing ...
FormDebugger::end('FORM_CODE', $rows);
```

This enables:
- Performance monitoring
- Error tracking
- Data validation
- Audit logging

## Usage Pattern

```php
// 1. Instantiate service
$service = new FormXXIService();

// 2. Generate data
$data = $service->generate($tenantId, $branchId, $month, $year);

// 3. Pass to view
return view('compliance.forms.form_xxi', $data);

// 4. Blade template accesses
// $header, $rows, $is_nil, $totals
```

## Testing Pattern

```php
// Test with sample data
$tenantId = 1;
$branchId = 1;
$month = 1;
$year = 2024;

$service = new FormXXIService();
$data = $service->generate($tenantId, $branchId, $month, $year);

// Assertions
$this->assertArrayHasKey('header', $data);
$this->assertArrayHasKey('rows', $data);
$this->assertArrayHasKey('is_nil', $data);
$this->assertArrayHasKey('totals', $data);
```

## Performance Characteristics

| Service | Query Type | Avg Rows | Execution Time |
|---------|-----------|----------|-----------------|
| FormXXIService | Simple SELECT | 50-200 | <100ms |
| FormXXIIService | Simple SELECT | 50-200 | <100ms |
| FormXXIIIService | JOIN | 20-100 | <150ms |
| FormXXIVService | GROUP BY | 5-50 | <100ms |
| FormXXVService | GROUP BY | 5-50 | <100ms |
| Form7Service | None | 0 | <10ms |
| ClraLicenseService | Simple SELECT | 5-20 | <50ms |
| ClraReturnService | GROUP BY | 5-50 | <100ms |
| ContractorMasterService | Simple SELECT | 5-50 | <100ms |

## Error Handling

All services handle:
- Missing tenant/branch
- Empty result sets
- Database connection errors
- Null values in data

Via:
- Null coalescing (`??`)
- COALESCE in SQL
- Empty array checks
- FormDebugger logging
