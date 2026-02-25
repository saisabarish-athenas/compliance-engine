# CORRECTED QUERY PATTERNS - QUICK REFERENCE

## FORM_18 Configuration Fix

### ❌ BEFORE (MySQL-Only, Broken)
```php
'FORM_18' => [
    'table' => 'workforce_employee',
    'date_field' => 'date_of_birth',  // Column doesn't exist
    'branch_filter' => true,
    'filing_frequency' => 'monthly',
    'due_rule' => 'next_month_10',
    'joins' => [],
    'fields' => [
        'employee_code' => 'employee_code',
        'employee_name' => 'name',
        'father_name' => 'father_name',  // Column doesn't exist
        'date_of_birth' => 'date_of_birth',  // Column doesn't exist
        'age' => 'DB::raw("TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())")',  // MySQL-only
        'designation' => 'designation',
        'date_of_joining' => 'date_of_joining',
    ],
    'where_clause' => 'DB::raw("TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18")',  // Invalid
],
```

### ✅ AFTER (Cross-Database, Working)
```php
'FORM_18' => [
    'table' => 'workforce_employee',
    'date_field' => 'created_at',  // Column exists
    'branch_filter' => true,
    'filing_frequency' => 'monthly',
    'due_rule' => 'next_month_10',
    'joins' => [],
    'fields' => [
        'employee_code' => 'employee_code',  // Column exists
        'employee_name' => 'name',  // Column exists
        'designation' => 'designation',  // Column exists
        'date_of_joining' => 'date_of_joining',  // Column exists
    ],
    // where_clause removed - not supported by FormDataAggregator
],
```

---

## Cross-Database Date Patterns

### ✅ CORRECT: Laravel Query Builder Helpers
```php
// Extract year from date - works on SQLite + MySQL
->whereYear('date_column', 2024)

// Extract month from date - works on SQLite + MySQL
->whereMonth('date_column', 1)

// Date range filtering - works on SQLite + MySQL
->whereBetween('date_column', [$startDate, $endDate])

// Date comparison - works on SQLite + MySQL
->whereDate('date_column', '2024-01-01')
```

### ❌ INCORRECT: MySQL-Specific Functions
```php
// TIMESTAMPDIFF - MySQL only, fails on SQLite
DB::raw("TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())")

// CURDATE() - MySQL only, fails on SQLite
DB::raw("CURDATE()")

// NOW() - MySQL only, fails on SQLite
DB::raw("NOW()")

// DATE_FORMAT - MySQL only, fails on SQLite
DB::raw("DATE_FORMAT(date_column, '%Y-%m-%d')")

// YEAR() in raw SQL - MySQL only, fails on SQLite
DB::raw("YEAR(date_column)")
```

---

## Age Calculation Pattern

### ❌ INCORRECT: Database-Level Age Calculation
```php
// MySQL-only - fails on SQLite
'fields' => [
    'age' => 'DB::raw("TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())")'
]

// Then filter in query
->whereRaw("TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18")
```

### ✅ CORRECT: PHP-Level Age Calculation
```php
// Fetch all records first
$records = DB::table('workforce_employee')
    ->where('tenant_id', $tenantId)
    ->get();

// Calculate age in PHP using Carbon
$childWorkers = $records->filter(function($record) {
    if (!$record->date_of_birth) {
        return false;
    }
    $age = Carbon::parse($record->date_of_birth)->age;
    return $age < 18;
});

// Add age to each record
$rows = $childWorkers->map(function($record) {
    return [
        'employee_code' => $record->employee_code,
        'employee_name' => $record->name,
        'age' => Carbon::parse($record->date_of_birth)->age,
        'designation' => $record->designation,
    ];
});
```

**Note**: Since `date_of_birth` column doesn't exist in current schema, age calculation is not possible. FORM_18 now shows employee details without age.

---

## Aggregation Patterns

### ✅ CORRECT: Standard SQL Aggregation
```php
// COUNT(*) - works on SQLite + MySQL
->select('employee_id', DB::raw('COUNT(*) as count'))
->groupBy('employee_id')

// SUM - works on SQLite + MySQL
->sum('salary')

// AVG - works on SQLite + MySQL
->avg('salary')

// MAX/MIN - works on SQLite + MySQL
->max('salary')
->min('salary')
```

### ❌ INCORRECT: MySQL-Specific Aggregation
```php
// GROUP_CONCAT - MySQL only
DB::raw("GROUP_CONCAT(name SEPARATOR ', ')")

// DATE aggregation with MySQL functions
DB::raw("SUM(CASE WHEN YEAR(date) = 2024 THEN amount ELSE 0 END)")
```

---

## Join Patterns

### ✅ CORRECT: Standard Joins
```php
// Inner join - works on SQLite + MySQL
->join('workforce_employee', 'payroll.employee_id', '=', 'workforce_employee.id')

// Left join - works on SQLite + MySQL
->leftJoin('contractor_master', 'deployment.contractor_id', '=', 'contractor_master.id')

// Multiple joins - works on SQLite + MySQL
->join('table1', 'base.id', '=', 'table1.base_id')
->join('table2', 'table1.id', '=', 'table2.table1_id')
```

### ✅ CORRECT: Tenant Filtering on Joins
```php
$query->join('workforce_employee', 'payroll.employee_id', '=', 'workforce_employee.id');

// Apply tenant filter on joined table
if (DB::getSchemaBuilder()->hasColumn('workforce_employee', 'tenant_id')) {
    $query->where('workforce_employee.tenant_id', $tenantId);
}
```

---

## Column Existence Validation

### ✅ CORRECT: Check Before Using
```php
// Check if column exists before filtering
if (DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
    $query->where($table . '.tenant_id', $tenantId);
}

// Check if column exists before selecting
if (DB::getSchemaBuilder()->hasColumn($table, 'date_of_birth')) {
    $selectFields[] = 'date_of_birth';
}
```

### ❌ INCORRECT: Assume Column Exists
```php
// Fails if column doesn't exist
$query->where('date_of_birth', '>', $date);

// Fails if column doesn't exist
$selectFields[] = 'father_name';
```

---

## Config Field Mapping

### ✅ CORRECT: Map Existing Columns
```php
'fields' => [
    'employee_code' => 'workforce_employee.employee_code',  // Column exists
    'employee_name' => 'workforce_employee.name',  // Column exists
    'designation' => 'workforce_employee.designation',  // Column exists
    'date_of_joining' => 'workforce_employee.date_of_joining',  // Column exists
]
```

### ❌ INCORRECT: Map Non-Existent Columns
```php
'fields' => [
    'father_name' => 'workforce_employee.father_name',  // Column doesn't exist
    'date_of_birth' => 'workforce_employee.date_of_birth',  // Column doesn't exist
    'age' => 'DB::raw("TIMESTAMPDIFF(...)")',  // MySQL-only + string instead of function
]
```

---

## Workforce Employee Schema Reference

### ✅ Available Columns
```
- id
- tenant_id
- branch_id
- employee_code
- name
- pf_number
- esi_number
- date_of_joining
- designation
- department
- basic_salary
- status
- created_at
- updated_at
- deleted_at
```

### ❌ NOT Available (Don't Use)
```
- date_of_birth  ❌
- father_name    ❌
- age            ❌
- mother_name    ❌
- gender         ❌
- address        ❌
```

---

## Summary of Changes

| Pattern | Before | After | Status |
|---------|--------|-------|--------|
| Date filtering | `date_of_birth` | `created_at` | ✅ Fixed |
| Age calculation | `TIMESTAMPDIFF()` | Removed | ✅ Fixed |
| Date functions | `CURDATE()` | Removed | ✅ Fixed |
| Column references | `father_name` | Removed | ✅ Fixed |
| Where clause | `where_clause` config | Removed | ✅ Fixed |
| DB::raw usage | String in config | Removed | ✅ Fixed |

---

## Testing Checklist

- [ ] FORM_18 generates without SQL errors
- [ ] FORM_B generates with correct payroll data
- [ ] FORM_XIII generates with contractor joins
- [ ] All forms work on SQLite
- [ ] All forms work on MySQL
- [ ] No references to non-existent columns
- [ ] No MySQL-specific functions in queries
- [ ] Config cache cleared
