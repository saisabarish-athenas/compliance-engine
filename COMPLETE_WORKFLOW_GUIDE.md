# 📋 Complete Compliance Forms Workflow Guide

## ✅ Database Setup Complete

Your demo database is now seeded with:
- **1 Tenant**: Demo Compliance Industries Pvt Ltd (ID: 1)
- **1 Branch**: Solar Panel Manufacturing Unit (ID: 1)
- **25 Employees**: With complete payroll data
- **3 Payroll Cycles**: January, February, March 2025
- **75 Payroll Entries**: 25 employees × 3 months
- **25 Bonus Records**: For all employees
- **1 Contractor**: GIRI Manpower Services
- **10 Contract Labour Deployments**: Active deployments
- **3 Incident Records**: Accidents and dangerous occurrences

---

## 🎯 Step 1: Generate Forms

### Option A: Using Artisan Command
```bash
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
```

### Option B: Using Tinker
```bash
php artisan tinker

# Generate a specific form
$service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
$data = $service->fetch(1, 1, 1, 2025);
dd($data);

# Check record count
echo $data['record_count'];
```

---

## 📄 Step 2: Preview Forms in Browser

### Access Form Preview
```
http://localhost:8000/compliance/forms/preview
```

### Available Forms to Preview
- **CLRA Forms**: Form XII, XIII, XIV, XVI, XVII, XIX, XX, XXI, XXII, XXIII
- **Labour Welfare**: Form A, C, D, DER
- **Social Security**: Form 11, ESI Form 12, EPF Inspection
- **Factories Act**: Form B, 2, 8, 10, 12, 17, 18, 25, 26, 26A, Hazard Register
- **Shops & Establishment**: Form 12, 13, C, VI, Unpaid, Fines

### Preview Parameters
```
/compliance/forms/preview?form_code=FORM_B&tenant_id=1&branch_id=1&month=1&year=2025
```

---

## 📑 Step 3: Generate PDF for Single Form

### Using Controller
```php
// In your controller
$pdfService = app(\App\Services\Compliance\PdfGenerationService::class);
$pdf = $pdfService->generateFormPdf('FORM_B', 1, 1, 1, 2025);
return $pdf->download('form_b_jan_2025.pdf');
```

### Using Artisan Command
```bash
php artisan compliance:generate-pdf --form_code=FORM_B --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

---

## 📦 Step 4: Create Inspection Pack (Batch Download)

### What is an Inspection Pack?
An inspection pack is a ZIP file containing:
- All generated forms for a specific period
- Organized by form category
- Ready for compliance inspection
- Includes metadata and summary

### Generate Inspection Pack

#### Option A: Using Controller
```php
// In your controller
$batchService = app(\App\Services\Compliance\BatchInspectionPackService::class);
$zipPath = $batchService->createInspectionPack(
    tenantId: 1,
    branchId: 1,
    month: 1,
    year: 2025,
    formCodes: ['FORM_B', 'FORM_XII', 'FORM_A'] // Optional: specific forms
);

return response()->download($zipPath, 'inspection_pack_jan_2025.zip');
```

#### Option B: Using Artisan Command
```bash
php artisan compliance:create-inspection-pack --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

#### Option C: Using Tinker
```bash
php artisan tinker

$service = app(\App\Services\Compliance\BatchInspectionPackService::class);
$zipPath = $service->createInspectionPack(1, 1, 1, 2025);
echo "Pack created at: $zipPath";
```

### Inspection Pack Structure
```
inspection_pack_jan_2025.zip
├── CLRA_Forms/
│   ├── Form_XII_Jan_2025.pdf
│   ├── Form_XIII_Jan_2025.pdf
│   └── ...
├── Labour_Welfare_Forms/
│   ├── Form_A_Jan_2025.pdf
│   ├── Form_C_Jan_2025.pdf
│   └── ...
├── Factories_Act_Forms/
│   ├── Form_B_Jan_2025.pdf
│   ├── Form_2_Jan_2025.pdf
│   └── ...
├── Social_Security_Forms/
│   ├── Form_11_Jan_2025.pdf
│   └── ...
├── Shops_Establishment_Forms/
│   ├── ShopsForm12_Jan_2025.pdf
│   └── ...
├── MANIFEST.json
└── README.txt
```

---

## 🔄 Step 5: Complete Workflow Example

### Scenario: Generate All Forms for January 2025 and Download

```php
// In your controller
public function downloadInspectionPack()
{
    $tenantId = auth()->user()->tenant_id;
    $branchId = request('branch_id', 1);
    $month = request('month', 1);
    $year = request('year', 2025);

    try {
        // Step 1: Verify data exists
        $dataService = app(\App\Services\Compliance\ComplianceDataService::class);
        $hasData = $dataService->verifyDataExists($tenantId, $branchId, $month, $year);
        
        if (!$hasData) {
            return back()->with('error', 'No data available for selected period');
        }

        // Step 2: Generate all forms
        $formCodes = config('compliance_forms.all_forms');
        $pdfService = app(\App\Services\Compliance\PdfGenerationService::class);
        
        foreach ($formCodes as $code) {
            $pdfService->generateFormPdf($code, $tenantId, $branchId, $month, $year);
        }

        // Step 3: Create inspection pack
        $packService = app(\App\Services\Compliance\BatchInspectionPackService::class);
        $zipPath = $packService->createInspectionPack($tenantId, $branchId, $month, $year);

        // Step 4: Download
        return response()->download($zipPath, "inspection_pack_{$month}_{$year}.zip");

    } catch (\Exception $e) {
        return back()->with('error', 'Error generating inspection pack: ' . $e->getMessage());
    }
}
```

---

## 🧪 Testing the Complete Workflow

### Test 1: Verify Data
```bash
php artisan tinker

# Check tenant
DB::table('tenants')->where('id', 1)->first();

# Check branch
DB::table('branches')->where('id', 1)->first();

# Check employees
DB::table('workforce_employee')->where('tenant_id', 1)->count();

# Check payroll
DB::table('workforce_payroll_entry')->where('tenant_id', 1)->count();
```

### Test 2: Generate Single Form
```bash
php artisan tinker

$service = app(\App\Services\Compliance\FormApis\FormBApiService::class);
$data = $service->fetch(1, 1, 1, 2025);

// Verify data structure
echo "Tenant ID: " . $data['tenant_id'];
echo "Branch ID: " . $data['branch_id'];
echo "Record Count: " . $data['record_count'];
```

### Test 3: Preview Form
```
Open browser: http://localhost:8000/compliance/forms/preview?form_code=FORM_B&tenant_id=1&branch_id=1&month=1&year=2025
```

### Test 4: Generate PDF
```bash
php artisan compliance:generate-pdf --form_code=FORM_B --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

### Test 5: Create Inspection Pack
```bash
php artisan compliance:create-inspection-pack --tenant_id=1 --branch_id=1 --month=1 --year=2025
```

---

## 📊 Form Categories & Codes

### CLRA Forms (10)
- FORM_XII, FORM_XIII, FORM_XIV, FORM_XVI, FORM_XVII
- FORM_XIX, FORM_XX, FORM_XXI, FORM_XXII, FORM_XXIII

### Labour Welfare Forms (4)
- FORM_A, FORM_C, FORM_D, FORM_DER

### Social Security Forms (3)
- FORM_11, ESI_FORM_12, EPF_INSPECTION

### Factories Act Forms (11)
- FORM_B, FORM_2, FORM_8, FORM_10, FORM_12
- FORM_17, FORM_18, FORM_25, FORM_26, FORM_26A, HAZARD_REG

### Shops & Establishment Forms (6)
- SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FORM_C, SHOPS_FORM_VI
- SHOPS_UNPAID, SHOPS_FINES

---

## 🚀 Quick Commands Reference

```bash
# 1. Seed demo data
php artisan db:seed --class=FreshComplianceSeeder

# 2. Generate forms
php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1

# 3. Generate single PDF
php artisan compliance:generate-pdf --form_code=FORM_B --tenant_id=1 --branch_id=1 --month=1 --year=2025

# 4. Create inspection pack
php artisan compliance:create-inspection-pack --tenant_id=1 --branch_id=1 --month=1 --year=2025

# 5. Clear cache
php artisan cache:clear

# 6. Reset database
php artisan migrate:reset --force && php artisan migrate
```

---

## 📁 Storage Locations

- **Generated PDFs**: `storage/app/compliance_pdfs/`
- **Inspection Packs**: `storage/app/compliance_inspection_packs/`
- **Temporary Files**: `storage/app/temp/`
- **Logs**: `storage/logs/laravel.log`

---

## ✅ Verification Checklist

- [ ] Database seeded successfully
- [ ] 25 employees created
- [ ] 75 payroll entries created
- [ ] Forms preview working
- [ ] Single PDF generation working
- [ ] Inspection pack creation working
- [ ] ZIP file downloads correctly
- [ ] All forms included in pack
- [ ] Metadata and summary included

---

## 🆘 Troubleshooting

### Issue: "No data available for selected period"
**Solution**: Ensure payroll entries exist for the selected month/year
```bash
php artisan tinker
DB::table('workforce_payroll_entry')->where('tenant_id', 1)->count();
```

### Issue: "Form not found"
**Solution**: Verify form code is correct and service is registered
```bash
php artisan tinker
$factory = app(\App\Services\Compliance\FormApis\FormApiServiceFactory::class);
$service = $factory->make('FORM_B');
```

### Issue: "PDF generation failed"
**Solution**: Check storage permissions and DomPDF installation
```bash
php artisan storage:link
chmod -R 755 storage/
```

### Issue: "ZIP file not created"
**Solution**: Verify ZipArchive extension is installed
```bash
php -m | grep zip
```

---

## 📞 Support

For detailed information:
- API Services: See `API_SERVICES_QUICK_REFERENCE.md`
- Form Data: See `FORM_DATA_TRACE_ANALYSIS.md`
- Batch Processing: See `BATCH_WORKFLOW_QUICK_REFERENCE.md`
- PDF Generation: See `PREVIEW_FEATURE_GUIDE.md`

---

**Status**: ✅ Ready for Production
**Last Updated**: 2025-03-11
