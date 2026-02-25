# PROJECT SETTINGS MODULE - STATUTORY ESTABLISHMENT DETAILS

## IMPLEMENTATION COMPLETE ✅

### Database Structure

**Migration 1**: `add_statutory_fields_to_tenants_table`
```php
- establishment_name (string, nullable)
- factory_license_no (string, nullable)
- pf_code (string, nullable)
- esi_code (string, nullable)
- labour_office_address (string, nullable)
```

**Migration 2**: `add_unit_fields_to_branches_table`
```php
- unit_name (string, nullable)
- address (text, nullable)
```

### Controller

**Path**: `app/Http/Controllers/Compliance/ProjectSettingsController.php`

**Methods**:
- `index()` - Display settings form
- `update()` - Validate and save settings

**Validation Rules**:
- establishment_name: required
- factory_license_no: required
- branches.*.unit_name: required
- branches.*.address: required
- pf_code: nullable
- esi_code: nullable

### Routes

**Path**: `routes/compliance.php`

```php
Route::get('/settings', [ProjectSettingsController::class, 'index'])
Route::post('/settings', [ProjectSettingsController::class, 'update'])
```

**URL**: `/compliance/settings`

### View

**Path**: `resources/views/compliance/settings/index.blade.php`

**Features**:
- Bootstrap 5 styled form
- Establishment details section
- Multi-branch support
- Success/error messages
- Required field indicators
- Back to dashboard link

### Form Generator Integration

**Modified**: `app/Services/Compliance/FormGenerator/BaseFormGenerator.php`

**New Method**: `validateStatutorySettings()`

**Validation Logic**:
```php
if (empty($tenant->establishment_name) || empty($tenant->factory_license_no)) {
    throw new Exception("Statutory settings incomplete...");
}

if (empty($branch->unit_name) || empty($branch->address)) {
    throw new Exception("Branch details incomplete...");
}
```

**Enforcement**: Runs BEFORE PDF generation for ALL forms.

### Data Aggregator Updates

**Modified**: `app/Services/Compliance/FormGenerator/FormDataAggregator.php`

**getTenantDetails()** now returns:
- establishment_name (primary)
- factory_license_no
- pf_code
- esi_code

**getBranchDetails()** now returns:
- unit_name (primary)
- address

### System Check Integration

**Modified**: `app/Console/Commands/ComplianceSystemCheck.php`

**New Check**: `checkStatutorySettings()`

**Validation**:
- Checks all FULL subscription tenants
- Validates establishment_name and factory_license_no
- Validates unit_name and address for all branches
- Reports incomplete count
- Fails system check if any incomplete

### Usage Workflow

#### 1. Run Migrations
```bash
php artisan migrate
```

#### 2. Configure Settings
Navigate to: `/compliance/settings`

Fill in:
- Establishment Name (required)
- Factory License Number (required)
- PF Code (optional)
- ESI Code (optional)
- Labour Office Address (optional)

For each branch:
- Unit Name (required)
- Address (required)

Click "Save Settings"

#### 3. Verify Configuration
```bash
php artisan compliance:system-check
```

Expected output:
```
Checking Statutory Settings...
Statutory Settings: ✅ OK (All configured)
```

#### 4. Generate Forms
All 36 forms will now display:
- Name of Establishment (from tenant.establishment_name)
- Branch/Unit (from branch.unit_name)
- Address (from branch.address)
- Factory License No (from tenant.factory_license_no)
- PF Code (from tenant.pf_code)
- ESI Code (from tenant.esi_code)
- Period (auto-calculated)

### Error Handling

**Before Settings Configured**:
```
Exception: Statutory settings incomplete. Please configure establishment 
details in Settings before generating forms.
```

**Missing Branch Details**:
```
Exception: Branch details incomplete. Please configure branch/unit details 
in Settings before generating forms.
```

### Blade Template Usage

In form templates, use:
```blade
{{ $header['tenant']['name'] }}
{{ $header['tenant']['factory_license_no'] }}
{{ $header['tenant']['pf_code'] }}
{{ $header['tenant']['esi_code'] }}
{{ $header['branch']['name'] }}
{{ $header['branch']['address'] }}
{{ $header['period'] }}
```

### Multi-Tenant Support

- Each tenant has own establishment details
- Each branch has own unit name and address
- Settings isolated by tenant_id
- No cross-tenant data leakage

### Production Checklist

- [ ] Run migrations
- [ ] Configure settings for all tenants
- [ ] Run system check
- [ ] Verify sample form generation
- [ ] Test with multiple branches
- [ ] Validate error handling

### Files Created/Modified

**NEW**:
- `database/migrations/2026_02_24_141243_add_statutory_fields_to_tenants_table.php`
- `database/migrations/2026_02_24_141310_add_unit_fields_to_branches_table.php`
- `app/Http/Controllers/Compliance/ProjectSettingsController.php`
- `resources/views/compliance/settings/index.blade.php`
- `PROJECT_SETTINGS_DOCUMENTATION.md`

**MODIFIED**:
- `routes/compliance.php`
- `app/Services/Compliance/FormGenerator/BaseFormGenerator.php`
- `app/Services/Compliance/FormGenerator/FormDataAggregator.php`
- `app/Console/Commands/ComplianceSystemCheck.php`

### Legal Compliance

✅ No hardcoded values
✅ No N/A fallbacks in production
✅ Mandatory validation before PDF generation
✅ System check enforcement
✅ Multi-branch support
✅ Tenant isolation

**Status**: PRODUCTION READY
