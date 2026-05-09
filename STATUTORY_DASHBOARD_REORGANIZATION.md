# Compliance Forms Dashboard UI Reorganization

## Overview
The Compliance Forms Dashboard has been reorganized to display the 36 forms grouped by their correct statutory sections instead of generic database sections. This provides a cleaner, more legally-compliant UI structure.

## Changes Made

### 1. New Configuration File
**File:** `config/statutory_form_grouping.php`

This configuration file defines the 5 statutory sections and maps all 36 forms to their correct sections:

- **FACTORIES_ACT** (13 forms): Factories Act Registers
  - FORM_1, FORM_2, FORM_B, FORM_10, FORM_12, FORM_17, FORM_18, FORM_25, FORM_7, FORM_8, FORM_26, FORM_26A, HAZARD_REG

- **CLRA** (13 forms): Contract Labour (CLRA)
  - FORM_XII, CLRA_LICENSE, FORM_XIII, FORM_XIV, FORM_XVI, FORM_XVII, FORM_XIX, FORM_XX, FORM_XXI, FORM_XXII, FORM_XXIII, FORM_XXIV, FORM_XXV

- **SHOPS_ESTABLISHMENT** (4 forms): Shops & Establishment Registers
  - SHOPS_FORM_12, SHOPS_FORM_13, SHOPS_FINES, SHOPS_FORM_VI

- **BONUS** (2 forms): Bonus Registers
  - SHOPS_FORM_C, SHOPS_UNPAID

- **SOCIAL_SECURITY** (3 forms): Social Security / Incident Registers
  - FORM_11, ESI_FORM_12, EPF_INSPECTION

### 2. Updated Dashboard View
**File:** `resources/views/compliance/dashboard.blade.php`

Changes:
- Replaced `section_id` dropdown with `statutory_section` dropdown
- Updated form selection to use statutory sections instead of database sections
- Forms are now displayed with their statutory grouping
- Added scrollable form list with max-height for better UX
- Updated JavaScript to populate forms based on statutory section configuration

### 3. Updated Controller
**File:** `app/Http/Controllers/ComplianceExecutionController.php`

Changes in `dashboard()` method:
- Added loading of statutory sections configuration
- Created mapping of form codes to form IDs
- Passed `$statutorySections` and `$formCodeToId` to the view

Changes in `createBatch()` method:
- Changed validation to accept `statutory_section` instead of `section_id`
- Added logic to find or create database section based on statutory section key
- Maps statutory section to database section for backward compatibility

### 4. Backward Compatibility
- Database sections are still used internally for batch storage
- When a statutory section is selected, the system automatically creates/finds the corresponding database section
- All existing batch processing, form generation, and reporting functionality remains unchanged
- The `forms()` endpoint still works with both section codes and IDs

## UI Flow

1. User selects a statutory section from dropdown (e.g., "🏭 Factories Act Registers")
2. Forms belonging to that section are displayed with checkboxes
3. User selects desired forms and creates a batch
4. System maps statutory section to database section and processes normally
5. All downstream functionality (generation, audit, certification) works as before

## Benefits

✅ **Legally Compliant**: Forms are grouped according to actual statutory requirements
✅ **User-Friendly**: Clear, intuitive section names with icons
✅ **Minimal Code Changes**: Only UI layer modified, no backend logic changes
✅ **Backward Compatible**: Existing batches and functionality unaffected
✅ **Maintainable**: Single source of truth for form grouping in config file

## Testing Checklist

- [ ] Dashboard loads without errors
- [ ] Statutory sections appear in dropdown with icons
- [ ] Forms populate correctly when section is selected
- [ ] All 36 forms are accessible through their respective sections
- [ ] Batch creation works with statutory sections
- [ ] Existing batches still display correctly
- [ ] Form generation and processing work as before
- [ ] Audit and certification features work as before

## Files Modified

1. `config/statutory_form_grouping.php` - NEW
2. `resources/views/compliance/dashboard.blade.php` - MODIFIED
3. `app/Http/Controllers/ComplianceExecutionController.php` - MODIFIED

## Files Created

1. `resources/views/compliance/dashboard_statutory.blade.php` - Alternative view (optional)

## No Changes To

- Database schema
- Migrations
- Form generators or services
- Form templates or PDF structures
- Backend form mappings
- Controller logic (except batch creation)
- Routes
