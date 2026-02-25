# FORM PREVIEW FEATURE - USER GUIDE

## Overview

The Form Preview feature allows users to view compliance forms in their browser before generating final PDFs. This ensures data accuracy and correctness before committing to PDF generation.

---

## How to Use

### Step 1: Create a Batch
1. Login to the Compliance Engine
2. Select a section (e.g., Factories Act)
3. Select forms to generate
4. Choose period (Month + Year)
5. Click "Create Batch"

### Step 2: Preview Forms
After batch creation, preview buttons appear:
- 👁️ Preview FORM_B
- 👁️ Preview FORM_XIII
- 👁️ Preview ESI_FORM_12
- 👁️ Preview EPF_INSPECTION

Click any preview button to open the form in a new browser tab.

### Step 3: Verify Data
In the preview window:
- ✅ Check employee names and codes
- ✅ Verify salary amounts
- ✅ Confirm totals are correct
- ✅ Review establishment information
- ✅ Check period dates

### Step 4: Print (Optional)
Click the "🖨️ Print" button to print the preview or save as PDF from browser.

### Step 5: Process Batch
Return to dashboard and click "⚙️ Process Batch" to generate final PDFs.

---

## Features

### Browser Rendering
- Fast loading (no PDF generation overhead)
- Responsive layout
- Print-friendly styling

### Data Verification
- Same data as final PDF
- Live calculations
- Totals verification
- NIL scenario handling

### No Side Effects
- No database writes
- No file creation
- No batch status changes
- Read-only operation

---

## Technical Details

### Route
```
GET /compliance/batch/{batch}/preview/{form}
```

### Controller Method
```php
ComplianceExecutionController@previewForm($batchId, $formCode)
```

### Data Flow
```
User clicks preview
  ↓
Controller receives request
  ↓
FormGeneratorFactory creates generator
  ↓
FormDataAggregator fetches data
  ↓
Generator prepares data (same as PDF)
  ↓
Blade template renders HTML
  ↓
Browser displays form
```

### Supported Forms
- FORM_B (Factories Act - Wage Register)
- FORM_XIII (CLRA - Contract Labour Register)
- ESI_FORM_12 (Social Security - Accident Register)
- EPF_INSPECTION (Social Security - Inspection Register)

---

## Subscription Logic

### FULL Subscription
- ✅ Preview buttons visible
- ✅ Process batch button visible
- ✅ Automated PDF generation

### MINIMAL Subscription
- ❌ Preview buttons hidden
- ❌ Process batch button hidden
- ✅ Manual upload section visible

---

## Benefits

1. **Data Accuracy:** Verify data before PDF generation
2. **Cost Savings:** Avoid regenerating incorrect PDFs
3. **User Confidence:** See exactly what will be generated
4. **Quick Review:** Fast browser rendering
5. **Print Option:** Print directly from browser

---

## Troubleshooting

### Preview Not Loading
- Check batch ID is valid
- Verify form code is correct
- Ensure tenant has access to batch

### Data Missing
- Verify payroll data exists for period
- Check branch assignment
- Confirm employee records exist

### Preview Different from PDF
- This should not happen (same data contract)
- Report as bug if encountered

---

## Future Enhancements

- [ ] Add "Generate PDF" button in preview
- [ ] Add inline editing capability
- [ ] Add comparison with previous period
- [ ] Add export to Excel option
- [ ] Add email preview functionality

---

**Feature Status:** ✅ PRODUCTION READY  
**Version:** 1.0  
**Last Updated:** 2026-02-24
