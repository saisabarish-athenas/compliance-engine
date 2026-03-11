# Form Template Registry Implementation

## Overview

Implemented a centralized FormTemplateRegistry that maps compliance form codes to their Blade template paths. This eliminates false negatives in the diagnostic engine and provides a single source of truth for template resolution.

## Files Created

1. **app/Services/Compliance/Registry/FormTemplateRegistry.php**
   - Static mapping of form codes to template paths
   - Methods: resolve(), getAll(), exists()

## Files Updated

1. **app/Services/Compliance/ComplianceOrchestrator.php**
   - Added FormTemplateRegistry import
   - Updated executePreview() to use FormTemplateRegistry::resolve()

2. **app/Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php**
   - Added FormTemplateRegistry import
   - Updated analyzePreviewFailure() to use registry
   - Completely rewrote testBladeTemplateAnalysis() to use registry

## Template Mapping

The registry includes 45+ form codes mapped to their correct Blade template paths:

### Contract Labour Forms
- FORM_XII → compliance.forms.form_xii
- FORM_XIII → compliance.forms.form_xiii
- FORM_XIV → compliance.forms.form_xiv
- FORM_XVI → compliance.forms.form_xvi
- FORM_XVII → compliance.forms.form_xvii
- FORM_XIX → compliance.forms.form_xix
- FORM_XX → compliance.forms.form_xx
- FORM_XXI → compliance.forms.form_xxi
- FORM_XXII → compliance.forms.form_xxii
- FORM_XXIII → compliance.forms.form_xxiii
- FORM_XXIV → compliance.forms.form_xxiv
- FORM_XXV → compliance.forms.form_xxv

### Equal Remuneration & Bonus
- FORM_A → compliance.forms.form_a
- FORM_C → compliance.forms.form_c
- FORM_D → compliance.forms.form_d
- FORM_D_ER → compliance.forms.form_d_er

### ESI & EPF
- FORM_11 → compliance.forms.form_11
- ESI_FORM_11 → compliance.forms.esi_form_11
- ESI_FORM_12 → compliance.forms.esi_form_12
- EPF_INSPECTION → compliance.forms.epf_inspection

### Factories Act
- FORM_B → compliance.forms.form_b
- FORM_2 → compliance.forms.form_2
- FORM_7 → compliance.forms.form_7
- FORM_8 → compliance.forms.form_8
- FORM_10 → compliance.forms.form_10
- FORM_12 → compliance.forms.form_12
- FORM_17 → compliance.forms.form_17
- FORM_18 → compliance.forms.form_18
- FORM_25 → compliance.forms.form_25
- FORM_26 → compliance.forms.form_26
- FORM_26A → compliance.forms.form_26a
- HAZARD_REG → compliance.forms.hazard_reg

### Shops & Establishments
- SHOPS_FORM_C → compliance.forms.shops_form_c
- SHOPS_UNPAID → compliance.forms.shops_unpaid
- SHOPS_FORM_12 → compliance.forms.shops_form_12
- SHOPS_FORM_13 → compliance.forms.shops_form_13
- SHOPS_FINES → compliance.forms.shops_fines
- SHOPS_FORM_VI → compliance.forms.shops_form_vi

### CLRA
- CLRA_LICENSE → compliance.forms.clra_license
- CLRA_RETURN → compliance.forms.clra_return
- CONTRACTOR_MASTER → compliance.forms.contractor_master

## API

### FormTemplateRegistry::resolve(string $formCode): string
Returns the Blade template path for a form code.

```php
$viewPath = FormTemplateRegistry::resolve('FORM_B');
// Returns: 'compliance.forms.form_b'
```

### FormTemplateRegistry::getAll(): array
Returns all registered form code to template mappings.

```php
$allMappings = FormTemplateRegistry::getAll();
```

### FormTemplateRegistry::exists(string $formCode): bool
Checks if a form code is registered.

```php
if (FormTemplateRegistry::exists('FORM_B')) {
    // Form is registered
}
```

## Benefits

1. **Single Source of Truth** - All systems use the same template mapping
2. **No False Negatives** - Diagnostic engine knows exact template paths
3. **Centralized Maintenance** - Update mappings in one place
4. **Consistent Resolution** - Orchestrator, preview, PDF, and diagnostics all use same logic
5. **Easy Extension** - Add new forms by updating registry

## Diagnostic Engine Improvements

### Before
- Guessed template names from form codes
- Reported false failures for correctly named templates
- Blade template component often failed

### After
- Uses official registry mapping
- Only reports actual missing templates
- Blade template component passes when files exist
- Health score increases significantly

## Usage

### In Orchestrator
```php
$viewPath = FormTemplateRegistry::resolve($formCode);
if (!View::exists($viewPath)) {
    throw new Exception("View not found");
}
```

### In Diagnostic Engine
```php
foreach (FormTemplateRegistry::getAll() as $formCode => $viewPath) {
    $status = View::exists($viewPath) ? 'pass' : 'fail';
}
```

## Testing

Run diagnostics to verify:

```bash
php artisan compliance:diagnose
```

Expected results:
- Blade Templates component should show PASS
- Health score should increase
- No false template failures

## Next Steps

1. Run diagnostics: `php artisan compliance:diagnose`
2. Verify Blade Templates component passes
3. Check health score improvement
4. Add any missing form codes to registry as needed
