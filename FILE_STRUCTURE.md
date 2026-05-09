# API Services File Structure

## Directory Layout

```
app/Services/Compliance/FormApis/
├── BaseFormApiService.php                    [Base class for all API services]
├── FormApiServiceFactory.php                 [Factory to resolve form codes to services]
│
├── CLRA Forms (10 services)
├── FormXIIApiService.php                     [Contractor Master]
├── FormXIIIApiService.php                    [Contract Labour Deployment]
├── FormXIVApiService.php                     [Employment Card]
├── FormXVIApiService.php                     [Contractor Muster Roll]
├── FormXVIIApiService.php                    [Contractor Wage Register]
├── FormXIXApiService.php                     [Contractor Wage Slip]
├── FormXXApiService.php                      [Deduction Register]
├── FormXXIApiService.php                     [Fines Register]
├── FormXXIIApiService.php                    [Advance Register]
├── FormXXIIIApiService.php                   [Contractor Overtime]
│
├── Labour Welfare Forms (4 services)
├── FormAApiService.php                       [Employee Register]
├── FormCApiService.php                       [Deduction Register]
├── FormDApiService.php                       [Attendance Register]
├── FormDERApiService.php                     [Equal Remuneration]
│
├── Social Security (3 services)
├── Form11ApiService.php                      [Social Security]
├── ESIForm12ApiService.php                   [ESI Form 12]
├── EPFInspectionApiService.php               [EPF Inspection]
│
├── Factories Act (11 services)
├── FormBApiService.php                       [Wage Register]
├── Form2ApiService.php                       [Work Shift]
├── Form8ApiService.php                       [Incident Register]
├── Form10ApiService.php                      [Overtime Register]
├── Form12ApiService.php                      [Employee Register]
├── Form17ApiService.php                      [Health Register]
├── Form18ApiService.php                      [Accident Report]
├── Form25ApiService.php                      [Attendance Register]
├── Form26ApiService.php                      [Accident Register]
├── Form26AApiService.php                     [Dangerous Occurrence]
├── HazardRegApiService.php                   [Hazard Register]
│
└── Shops & Establishment (6 services)
    ├── ShopsForm12ApiService.php             [Wage Register]
    ├── ShopsForm13ApiService.php             [Leave Register]
    ├── ShopsFormCApiService.php              [Bonus Register]
    ├── ShopsFormVIApiService.php             [Holiday Register]
    ├── ShopsUnpaidApiService.php             [Unpaid Bonus]
    └── ShopsFinesApiService.php              [Fines Register]
```

## File Sizes & Complexity

| File | Lines | Complexity |
|------|-------|-----------|
| BaseFormApiService.php | ~100 | Low |
| FormApiServiceFactory.php | ~60 | Low |
| Each Form API Service | ~50-60 | Low |
| **Total** | **~1,900** | **Low** |

## Class Hierarchy

```
BaseFormApiService (abstract)
├── FormXIIApiService
├── FormXIIIApiService
├── FormXIVApiService
├── FormXVIApiService
├── FormXVIIApiService
├── FormXIXApiService
├── FormXXApiService
├── FormXXIApiService
├── FormXXIIApiService
├── FormXXIIIApiService
├── FormAApiService
├── FormCApiService
├── FormDApiService
├── FormDERApiService
├── Form11ApiService
├── ESIForm12ApiService
├── EPFInspectionApiService
├── FormBApiService
├── Form2ApiService
├── Form8ApiService
├── Form10ApiService
├── Form12ApiService
├── Form17ApiService
├── Form18ApiService
├── Form25ApiService
├── Form26ApiService
├── Form26AApiService
├── HazardRegApiService
├── ShopsForm12ApiService
├── ShopsForm13ApiService
├── ShopsFormCApiService
├── ShopsFormVIApiService
├── ShopsUnpaidApiService
└── ShopsFinesApiService
```

## Namespace Structure

```
App\Services\Compliance\FormApis\
├── BaseFormApiService
├── FormApiServiceFactory
├── FormXIIApiService
├── FormXIIIApiService
├── FormXIVApiService
├── FormXVIApiService
├── FormXVIIApiService
├── FormXIXApiService
├── FormXXApiService
├── FormXXIApiService
├── FormXXIIApiService
├── FormXXIIIApiService
├── FormAApiService
├── FormCApiService
├── FormDApiService
├── FormDERApiService
├── Form11ApiService
├── ESIForm12ApiService
├── EPFInspectionApiService
├── FormBApiService
├── Form2ApiService
├── Form8ApiService
├── Form10ApiService
├── Form12ApiService
├── Form17ApiService
├── Form18ApiService
├── Form25ApiService
├── Form26ApiService
├── Form26AApiService
├── HazardRegApiService
├── ShopsForm12ApiService
├── ShopsForm13ApiService
├── ShopsFormCApiService
├── ShopsFormVIApiService
├── ShopsUnpaidApiService
└── ShopsFinesApiService
```

## Integration Points

### ComplianceOrchestrator
- Location: `app/Services/Compliance/ComplianceOrchestrator.php`
- Integration: Uses `FormApiServiceFactory::make($formCode)`
- Updated: Added multi-tenant validation

### FormGeneratorFactory
- Location: `app/Services/Compliance/FormGenerator/FormGeneratorFactory.php`
- Integration: Receives data from API services
- No changes needed

### Blade Templates
- Location: `resources/views/compliance/forms/`
- Integration: Receives prepared data from generators
- No changes needed

## Data Flow

```
HTTP Request
    ↓
ComplianceExecutionController
    ↓
ComplianceOrchestrator::execute()
    ├─ Validate inputs
    ├─ Run validation pipeline
    ├─ FormApiServiceFactory::make($formCode)
    │   └─ Returns FormSpecificApiService instance
    ├─ $apiService->fetch($tenantId, $branchId, $month, $year)
    │   ├─ Query database with tenant/branch filtering
    │   └─ Return structured data array
    ├─ Validate multi-tenant safety
    ├─ FormGeneratorFactory::make($formCode)
    │   └─ Returns FormSpecificGenerator instance
    ├─ $generator->prepareData($rawData)
    │   └─ Transform API data for template
    ├─ Execute mode (preview/pdf/batch/inspection_pack)
    │   └─ Render Blade template or generate PDF
    └─ Return result
        ↓
    HTTP Response
```

## Database Tables Accessed

### Employee Data
- `workforce_employee` - Employee master
- `workforce_payroll_entry` - Payroll records
- `workforce_payroll_cycle` - Payroll cycles
- `workforce_attendance` - Attendance records

### Contractor Data
- `contractor_master` - Contractor information
- `contract_labour_deployment` - Contract labour deployments

### Incident Data
- `incidents` - Incident/accident records

### Tenant Data
- `tenants` - Tenant information
- `branches` - Branch information

## Configuration

### FormApiServiceFactory Mappings

```php
[
    // CLRA Forms (10)
    'FORM_XII' => FormXIIApiService::class,
    'FORM_XIII' => FormXIIIApiService::class,
    'FORM_XIV' => FormXIVApiService::class,
    'FORM_XVI' => FormXVIApiService::class,
    'FORM_XVII' => FormXVIIApiService::class,
    'FORM_XIX' => FormXIXApiService::class,
    'FORM_XX' => FormXXApiService::class,
    'FORM_XXI' => FormXXIApiService::class,
    'FORM_XXII' => FormXXIIApiService::class,
    'FORM_XXIII' => FormXXIIIApiService::class,

    // Labour Welfare Forms (4)
    'FORM_A' => FormAApiService::class,
    'FORM_C' => FormCApiService::class,
    'FORM_D' => FormDApiService::class,
    'FORM_D_ER' => FormDERApiService::class,

    // Social Security (3)
    'FORM_11' => Form11ApiService::class,
    'ESI_FORM_12' => ESIForm12ApiService::class,
    'EPF_INSPECTION' => EPFInspectionApiService::class,

    // Factories Act (11)
    'FORM_B' => FormBApiService::class,
    'FORM_2' => Form2ApiService::class,
    'FORM_10' => Form10ApiService::class,
    'FORM_12' => Form12ApiService::class,
    'FORM_17' => Form17ApiService::class,
    'FORM_18' => Form18ApiService::class,
    'FORM_25' => Form25ApiService::class,
    'FORM_8' => Form8ApiService::class,
    'FORM_26' => Form26ApiService::class,
    'FORM_26A' => Form26AApiService::class,
    'HAZARD_REG' => HazardRegApiService::class,

    // Shops & Establishment (6)
    'SHOPS_FORM_12' => ShopsForm12ApiService::class,
    'SHOPS_FORM_13' => ShopsForm13ApiService::class,
    'SHOPS_FORM_C' => ShopsFormCApiService::class,
    'SHOPS_FORM_VI' => ShopsFormVIApiService::class,
    'SHOPS_FINES' => ShopsFinesApiService::class,
    'SHOPS_UNPAID' => ShopsUnpaidApiService::class,
]
```

## Performance Considerations

### Query Optimization
- All queries use indexed columns (tenant_id, branch_id)
- Queries select only required columns
- Proper joins used for related data
- No N+1 queries

### Caching Opportunities
- Tenant details (rarely change)
- Branch details (rarely change)
- Period formatting (can be cached)

### Scalability
- Stateless services (no shared state)
- Can be horizontally scaled
- Database connection pooling recommended
- Consider query result caching for high-volume forms

## Security Considerations

### Multi-Tenant Isolation
- All queries filter by tenant_id
- All queries filter by branch_id
- ComplianceOrchestrator validates response IDs
- No cross-tenant data leakage possible

### Input Validation
- tenant_id must be > 0
- branch_id must be > 0
- month must be 1-12
- year must be 2020-2030
- form_code must exist in master

### Error Handling
- Exceptions thrown for invalid inputs
- Exceptions thrown for missing data
- Exceptions thrown for ID mismatches
- Errors logged for debugging

## Maintenance

### Adding New Forms
1. Create new API service class extending BaseFormApiService
2. Implement fetch() method
3. Register in FormApiServiceFactory
4. Create corresponding generator (if needed)
5. Create corresponding Blade template (if needed)

### Updating Existing Forms
1. Modify API service query
2. Update returned data structure
3. Update generator if needed
4. Update template if needed
5. Test with compliance:trace-form-data command

### Debugging
- Check FormApiServiceFactory for form code mapping
- Verify API service extends BaseFormApiService
- Verify fetch() method signature
- Check database queries for tenant/branch filtering
- Review ComplianceOrchestrator logs
