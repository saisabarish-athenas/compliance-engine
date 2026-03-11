# Quick Start Guide - Compliance Forms System

## Prerequisites
- Laravel 12 installed
- SQLite database configured
- PHP 8.1+

## Setup Steps

### 1. Run Migrations
```bash
php artisan migrate --force
```

This will create/update:
- `workforce_employee` (with new columns: father_name, gender, date_of_birth, permanent_address, local_address)
- `workforce_fines` (new table)
- `workforce_advances` (new table)
- `workforce_attendance` (existing, with branch_id)
- `workforce_deductions` (existing)

### 2. Seed Demo Data
```bash
php artisan db:seed --class=ComplianceFormsDemoSeeder
```

This generates:
- 1 Tenant
- 1 Branch
- 15 Employees (with complete data)
- 1,350 Attendance records (Jan-Mar 2025)
- 5 Deduction records
- 8 Fine records
- 6 Advance records
- 10 Contract Labour Deployments
- 1 Contractor

### 3. Access Forms

#### Via Web Interface
```
GET /compliance/batch/{batch_id}/preview/FORM_XII
GET /compliance/batch/{batch_id}/preview/FORM_XIII
GET /compliance/batch/{batch_id}/preview/FORM_XVI
GET /compliance/batch/{batch_id}/preview/FORM_XX
GET /compliance/batch/{batch_id}/preview/FORM_XXI
GET /compliance/batch/{batch_id}/preview/FORM_XXII
```

#### Via Tinker (Testing)
```bash
php artisan tinker

# Test FORM_XII
$service = new \App\Services\Compliance\Forms\FormXIIService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);

# Test FORM_XIII
$service = new \App\Services\Compliance\Forms\FormXIIIService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);

# Test FORM_XVI
$service = new \App\Services\Compliance\Forms\FormXVIService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);

# Test FORM_XX
$service = new \App\Services\Compliance\Forms\FormXXService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);

# Test FORM_XXI
$service = new \App\Services\Compliance\Forms\FormXXIService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);

# Test FORM_XXII
$service = new \App\Services\Compliance\Forms\FormXXIIService();
$data = $service->generate(1, 1, 1, 2025);
dd($data);
```

## Form Details

### FORM_XII - Register of Contractors
- **Data Source**: contractor_master, contract_labour_deployment
- **Records**: 5 contractors with deployment details
- **Columns**: Name, Address, Nature of Work, Work Location, Contract Period, Max Workers

### FORM_XIII - Register of Workmen Employed by Contractor
- **Data Source**: contract_labour_deployment, workforce_employee
- **Records**: 15 employees with complete details
- **Columns**: Name, Age, Sex, Father's Name, Designation, Addresses, Joining Date, Termination Date

### FORM_XVI - Muster Roll
- **Data Source**: workforce_attendance, workforce_employee
- **Records**: 1,350 attendance records (15 employees × 90 days)
- **Columns**: Employee Name, Father's Name, Sex, Daily Attendance (31 days), Remarks

### FORM_XX - Register of Deductions
- **Data Source**: workforce_deductions, workforce_employee
- **Records**: 5 deduction records
- **Columns**: Employee Name, Father's Name, Designation, Damage Particulars, Amount, Instalments
- **Nil Handling**: Shows "Nil for the month" when no deductions

### FORM_XXI - Register of Fines
- **Data Source**: workforce_fines, workforce_employee
- **Records**: 8 fine records
- **Columns**: Employee Name, Father's Name, Designation, Reason, Amount, Date Realised
- **Nil Handling**: Shows "Nil for the month" when no fines

### FORM_XXII - Register of Advances
- **Data Source**: workforce_advances, workforce_employee
- **Records**: 6 advance records
- **Columns**: Employee Name, Father's Name, Designation, Advance Amount, Instalments, Repayment Period
- **Nil Handling**: Shows "Nil for the month" when no advances

## Database Schema

### workforce_employee
```
- id
- tenant_id
- branch_id
- employee_code
- name
- father_name (NEW)
- gender (NEW)
- date_of_birth (NEW)
- permanent_address (NEW)
- local_address (NEW)
- pf_number
- esi_number
- date_of_joining
- designation
- department
- basic_salary
- status
```

### workforce_fines (NEW)
```
- id
- tenant_id
- branch_id
- employee_id
- fine_date
- reason
- amount
- remarks
- timestamps & soft deletes
```

### workforce_advances (NEW)
```
- id
- tenant_id
- branch_id
- employee_id
- advance_date
- amount
- num_instalments
- first_month
- last_month
- remarks
- timestamps & soft deletes
```

## Troubleshooting

### Issue: "Form not found"
**Solution**: Ensure FormRegistry has the form registered in `app/Compliance/Registry/FormRegistry.php`

### Issue: "No data displayed"
**Solution**: Run the seeder to generate demo data:
```bash
php artisan db:seed --class=ComplianceFormsDemoSeeder
```

### Issue: "SQL error"
**Solution**: Ensure all migrations are run:
```bash
php artisan migrate --force
```

### Issue: "Blade template not found"
**Solution**: Check that the template exists in `resources/views/compliance/forms/`

## Testing Commands

### Validate All Forms
```bash
php artisan tinker << 'EOF'
$forms = ['FORM_XII', 'FORM_XIII', 'FORM_XVI', 'FORM_XX', 'FORM_XXI', 'FORM_XXII'];
$tenantId = DB::table('tenants')->first()->id;
$branchId = DB::table('branches')->where('tenant_id', $tenantId)->first()->id;

foreach ($forms as $form) {
    try {
        $service = match($form) {
            'FORM_XII' => new \App\Services\Compliance\Forms\FormXIIService(),
            'FORM_XIII' => new \App\Services\Compliance\Forms\FormXIIIService(),
            'FORM_XVI' => new \App\Services\Compliance\Forms\FormXVIService(),
            'FORM_XX' => new \App\Services\Compliance\Forms\FormXXService(),
            'FORM_XXI' => new \App\Services\Compliance\Forms\FormXXIService(),
            'FORM_XXII' => new \App\Services\Compliance\Forms\FormXXIIService(),
        };
        
        $data = $service->generate($tenantId, $branchId, 1, 2025);
        $rowCount = count($data['rows'] ?? []);
        echo "✓ $form: $rowCount records\n";
    } catch (\Exception $e) {
        echo "✗ $form: " . $e->getMessage() . "\n";
    }
}
EOF
```

### Check Database Records
```bash
php artisan tinker << 'EOF'
echo "Employees: " . DB::table('workforce_employee')->count() . "\n";
echo "Attendance: " . DB::table('workforce_attendance')->count() . "\n";
echo "Deductions: " . DB::table('workforce_deductions')->count() . "\n";
echo "Fines: " . DB::table('workforce_fines')->count() . "\n";
echo "Advances: " . DB::table('workforce_advances')->count() . "\n";
echo "Contractors: " . DB::table('contractor_master')->count() . "\n";
echo "Deployments: " . DB::table('contract_labour_deployment')->count() . "\n";
EOF
```

## Support

For issues or questions, refer to:
- `COMPLIANCE_REPAIR_COMPLETE.md` - Detailed repair report
- `app/Services/Compliance/Forms/` - Service implementations
- `resources/views/compliance/forms/` - Blade templates
- `app/Compliance/Builders/` - Data builders

---

**Status**: ✓ READY FOR PRODUCTION
**Last Updated**: 2025-03-20
