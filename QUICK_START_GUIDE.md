# COMPLIANCE ENGINE - QUICK START GUIDE

## System Requirements
- PHP 8.2+
- Composer
- SQLite (included)
- Laravel 12

---

## Installation

### 1. Clone/Navigate to Project
```bash
cd E:\compliance-engine
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate:fresh --seed
```

### 5. Start Server
```bash
php artisan serve
```

### 6. Access Application
```
URL: http://localhost:8000
```

---

## Demo Credentials

### FULL Subscription (Automation Enabled)
- **Email:** admin@abc.com
- **Password:** password
- **Tenant:** ABC Manufacturing Pvt Ltd
- **Features:** Preview + Process + Download

### MINIMAL Subscription (Manual Upload Only)
- **Email:** minimal@demo.com
- **Password:** password
- **Tenant:** XYZ Enterprises
- **Features:** Manual Upload + Download

---

## Quick Test

### Test Form Generation
```bash
php artisan compliance:test-generation
```

Expected output:
```
✅ FORM_B: 1,275,352 bytes
✅ FORM_XIII: 1,270,860 bytes
✅ ESI_FORM_12: 1,271,720 bytes
✅ EPF_INSPECTION: 1,271,573 bytes

Success: 4/4 | Failed: 0/4
```

### Clear Caches
```bash
php artisan optimize:clear
```

### Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Key Files

### Models
- `app/Models/WorkforceEmployee.php` - Employee model
- `app/Models/IncidentDocument.php` - Incident tracking
- `app/Models/ContractLabourDeployment.php` - Contract labour
- `app/Models/ComplianceExecutionBatch.php` - Batch processing

### Controllers
- `app/Http/Controllers/ComplianceExecutionController.php` - Main controller

### Services
- `app/Services/Compliance/FormGenerator/` - Form generation logic
- `app/Services/Compliance/ComplianceExecutionService.php` - Batch processing

### Configuration
- `config/compliance_forms.php` - 35 forms mapped

### Views
- `resources/views/compliance/dashboard.blade.php` - Main dashboard
- `resources/views/compliance/forms/` - Form templates
- `resources/views/compliance/layouts/` - Layout templates

### Routes
- `routes/web.php` - Authentication routes
- `routes/compliance.php` - Compliance routes

---

## Common Tasks

### Add New Form
1. Add config to `config/compliance_forms.php`
2. Create Blade template in `resources/views/compliance/forms/`
3. Create generator class in `app/Services/Compliance/FormGenerator/`
4. Register in `FormGeneratorFactory.php`

### Add New Tenant
```bash
php artisan tinker
```
```php
$tenant = Tenant::create([
    'name' => 'New Company',
    'subscription_type' => 'FULL'
]);
```

### Add New Branch
```php
$branch = Branch::create([
    'tenant_id' => 1,
    'branch_name' => 'New Branch',
    'factory_license_number' => 'LIC123',
    'pf_code' => 'PF123',
    'esi_code' => 'ESI123'
]);
```

### Add New Employee
```php
$employee = WorkforceEmployee::create([
    'tenant_id' => 1,
    'branch_id' => 1,
    'employee_code' => 'EMP001',
    'name' => 'John Doe',
    'pf_number' => 'PF001',
    'esi_number' => 'ESI001',
    'date_of_joining' => '2026-01-01',
    'designation' => 'Worker',
    'basic_salary' => 15000
]);
```

---

## Troubleshooting

### Issue: Forms not generating
**Solution:** Check database has data for the period
```bash
php artisan tinker
```
```php
WorkforcePayrollEntry::whereMonth('pay_date', 1)
    ->whereYear('pay_date', 2026)
    ->count();
```

### Issue: Preview not loading
**Solution:** Clear view cache
```bash
php artisan view:clear
```

### Issue: Tenant not showing
**Solution:** Check user has tenant_id
```php
User::find(1)->tenant;
```

### Issue: Permission denied
**Solution:** Check storage permissions
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## Development Workflow

### 1. Make Changes
Edit files in `app/`, `resources/`, or `config/`

### 2. Clear Caches
```bash
php artisan optimize:clear
```

### 3. Test
```bash
php artisan compliance:test-generation
```

### 4. Verify in Browser
Navigate to http://localhost:8000

---

## Database Schema

### Key Tables
- `tenants` - Multi-tenant organizations
- `branches` - Branch locations
- `workforce_employee` - Employee records
- `workforce_payroll_cycle` - Payroll periods
- `workforce_payroll_entry` - Payroll data
- `contract_labour_deployment` - Contract workers
- `contractor_master` - Contractor details
- `incident_documents` - Accident records
- `inspection_documents` - Inspection records
- `compliance_execution_batches` - Batch tracking

### Relationships
```
Tenant
  └─ Branches
      └─ WorkforceEmployees
          └─ PayrollEntries
          └─ IncidentDocuments
          └─ ContractLabourDeployments
```

---

## API Endpoints

### Dashboard
```
GET /compliance/dashboard
```

### Get Forms by Section
```
GET /compliance/forms/{section_id}
```

### Create Batch
```
POST /compliance/batch/create
Body: {
    section_id, period_month, period_year, form_ids[], branch_id
}
```

### Preview Form
```
GET /compliance/batch/{batch_id}/preview/{form_code}
```

### Process Batch
```
POST /compliance/batch/process/{batch_id}
```

### Download Report
```
GET /compliance/batch/{batch_id}/download
```

### Upload Manual Form
```
POST /compliance/form/upload/{batch_id}/{form_id}
Body: file (PDF)
```

---

## Configuration

### Compliance Forms
Edit `config/compliance_forms.php` to:
- Add new forms
- Modify field mappings
- Change JOIN logic
- Update date fields

### Environment Variables
Edit `.env` to:
- Change database connection
- Modify app settings
- Update debug mode

---

## Testing

### Manual Testing Checklist
- [ ] Login with FULL subscription
- [ ] Create batch
- [ ] Preview forms
- [ ] Process batch
- [ ] Download report
- [ ] Login with MINIMAL subscription
- [ ] Upload manual forms
- [ ] Download report

### Automated Testing
```bash
php artisan test
```

---

## Support

### Documentation
- `FINAL_SYSTEM_VALIDATION_REPORT.md` - Complete system audit
- `PREVIEW_FEATURE_GUIDE.md` - Preview feature documentation
- `SYSTEM_AUDIT_SUMMARY.md` - Changes summary
- `reference_structure_map.md` - Form structure reference

### Logs
- `storage/logs/laravel.log` - Application logs
- Check for errors and warnings

---

## Production Deployment

### 1. Environment
```bash
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimize
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Permissions
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 4. Database
```bash
php artisan migrate --force
```

### 5. Queue Worker (Optional)
```bash
php artisan queue:work
```

---

**Version:** 1.0  
**Last Updated:** 2026-02-24  
**Status:** ✅ PRODUCTION READY
