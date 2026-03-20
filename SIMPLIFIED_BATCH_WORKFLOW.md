# Simplified Compliance Batch Creation Workflow

## Overview

This implementation simplifies the compliance batch creation workflow by removing the need to manually select statutory sections. Instead, clients only need to select **Month** and **Year**, and the system automatically determines which forms apply based on their frequency.

## Key Features

### 1. Automatic Form Filtering
- **Monthly Forms**: Included every month
- **Quarterly Forms**: Included in March, June, September, December
- **Half-Yearly Forms**: Included in June, December
- **Yearly Forms**: Included in December only

### 2. Simplified UI
The new workflow removes the "Select Statutory Section" field and presents only:
- Month dropdown
- Year dropdown
- Create Batch button

### 3. Form Selection Interface
After batch creation, users see:
- Table of filtered forms for the selected month
- Form code, name, and frequency
- Data source method selection (radio buttons)
- Download template button for each form

### 4. Data Entry Methods
For each form, users can choose ONE of three methods:

#### Manual Filling
- Enter data directly in a form
- Stores data in `compliance_manual_data` table
- Data is used by existing generators

#### Upload PDF
- Upload a blank or filled PDF
- Stored in `storage/app/compliance/uploads/`
- Linked to batch without processing
- Attached to final compliance report

#### Upload CSV
- Upload CSV file with form data
- Parsed and mapped to form fields
- Stored in `compliance_manual_uploads` table
- Passed to existing compliance generator

### 5. Template Download
- Each form has a "Download Template" button
- Downloads the Blade template file
- Users can use it as reference for manual filling or CSV structure

## File Structure

### New Files Created

```
app/Services/Compliance/
├── FormFrequencyFilterService.php          [Form filtering logic]

app/Http/Controllers/Compliance/
├── SimplifiedBatchController.php           [Main controller]

resources/views/compliance/
├── simplified-batch-create.blade.php       [Create batch UI]
├── simplified-batch-show.blade.php         [Form selection UI]
├── simplified-batch-data-entry.blade.php   [Data entry UI]

routes/
├── compliance.php                          [Updated with new routes]
```

### Modified Files

```
routes/compliance.php                       [Added simplified batch routes]
```

## Routes

### New Routes Added

```php
// Create batch page
GET  /compliance/batch/create-simplified
     → SimplifiedBatchController@create
     → compliance.simplified-batch.create

// Store batch
POST /compliance/batch/create-simplified
     → SimplifiedBatchController@store
     → compliance.simplified-batch.store

// Get applicable forms (AJAX)
POST /compliance/batch/get-applicable-forms
     → SimplifiedBatchController@getApplicableForms
     → compliance.simplified-batch.get-forms

// Show batch details
GET  /compliance/batch/{id}/show-simplified
     → SimplifiedBatchController@show
     → compliance.simplified-batch.show

// Download form template
GET  /compliance/batch/{id}/download-template/{formCode}
     → SimplifiedBatchController@downloadTemplate
     → compliance.simplified-batch.download-template

// Data entry page
GET  /compliance/batch/{id}/data-entry
     → SimplifiedBatchController@dataEntry
     → compliance.simplified-batch.data-entry

// Process data entry
POST /compliance/batch/{id}/proceed
     → SimplifiedBatchController@proceed
     → compliance.simplified-batch.proceed
```

## Workflow

### Step 1: Create Batch
```
User visits: /compliance/batch/create-simplified
↓
Selects Month and Year
↓
System shows applicable forms for that month
↓
Clicks "Create Batch"
```

### Step 2: Select Forms & Methods
```
Batch created
↓
User sees filtered forms table
↓
For each form, selects data source method:
  - Manual Filling
  - Upload PDF
  - Upload CSV
↓
Clicks "Proceed"
```

### Step 3: Data Entry
```
User enters data based on selected method:
  - Manual: Fill form fields
  - PDF: Upload PDF file
  - CSV: Upload CSV file
↓
Clicks "Proceed to Generation"
```

### Step 4: Generate Forms
```
System processes data:
  - Manual data stored in compliance_manual_data
  - PDFs linked without processing
  - CSVs parsed and stored
↓
Existing ComplianceExecutionService generates forms
↓
Forms ready for download/filing
```

## Database Tables Used

### Existing Tables
- `compliance_forms_master` - Form definitions with frequency
- `compliance_execution_batches` - Batch records
- `compliance_batch_forms` - Batch-form relationships
- `compliance_manual_data` - Manual data entry
- `compliance_manual_uploads` - File uploads

### New Data Stored
- `compliance_manual_data.form_code` - Form identifier
- `compliance_manual_data.data_payload` - JSON data
- `compliance_manual_uploads.file_path` - Upload location

## Service Classes

### FormFrequencyFilterService

**Purpose**: Filter forms based on frequency and selected month

**Methods**:
```php
public function getApplicableFormsForMonth(int $month, int $year): array
```

**Usage**:
```php
$filterService = app(FormFrequencyFilterService::class);
$forms = $filterService->getApplicableFormsForMonth(6, 2024);
// Returns forms applicable for June 2024
```

## Controller Methods

### SimplifiedBatchController

#### create()
- Returns simplified batch creation view
- No parameters

#### getApplicableForms(Request $request)
- AJAX endpoint
- Input: `month`, `year`
- Returns: JSON with applicable forms

#### store(Request $request)
- Creates batch with filtered forms
- Input: `period_month`, `period_year`
- Redirects to batch show page

#### show(int $batchId)
- Shows batch details and form selection
- Displays filtered forms table
- Allows method selection

#### downloadTemplate(int $batchId, string $formCode)
- Downloads Blade template file
- Returns file as attachment

#### dataEntry(int $batchId)
- Shows data entry form
- Displays manual/PDF/CSV options

#### proceed(Request $request, int $batchId)
- Processes data entry
- Stores data in appropriate tables
- Redirects to batch show

## Integration with Existing System

### Backward Compatibility
- ✅ Existing batch creation still works
- ✅ Existing generators unchanged
- ✅ Existing ComplianceExecutionService used
- ✅ No breaking changes

### Data Flow
```
SimplifiedBatchController
    ↓
FormFrequencyFilterService (filters forms)
    ↓
ComplianceExecutionService (creates batch)
    ↓
Existing Generators (process data)
    ↓
Blade Templates (render forms)
```

## Usage Examples

### Access Simplified Batch Creation
```
URL: /compliance/batch/create-simplified
```

### Create Batch Programmatically
```php
$controller = app(SimplifiedBatchController::class);
$request = new Request([
    'period_month' => 6,
    'period_year' => 2024
]);
$response = $controller->store($request);
```

### Get Applicable Forms
```php
$filterService = app(FormFrequencyFilterService::class);
$forms = $filterService->getApplicableFormsForMonth(3, 2024);
// Returns quarterly forms for March 2024
```

## Testing

### Test Form Filtering
```bash
# Visit the create page
http://localhost/compliance/batch/create-simplified

# Select June 2024
# Should show: monthly + quarterly forms

# Select December 2024
# Should show: monthly + quarterly + half-yearly + yearly forms
```

### Test Data Entry
```bash
# After batch creation
# Select data source method for each form
# Click Proceed
# Enter data or upload files
# Click "Proceed to Generation"
```

## Frequency Configuration

Forms are filtered based on the `frequency` column in `compliance_forms_master`:

| Frequency | Months |
|-----------|--------|
| monthly | 1-12 (all) |
| quarterly | 3, 6, 9, 12 |
| half-yearly | 6, 12 |
| yearly | 12 |

To add a new form with specific frequency:
```php
ComplianceFormsMaster::create([
    'form_code' => 'FORM_XYZ',
    'form_name' => 'Form XYZ',
    'frequency' => 'quarterly',
    'is_active' => true,
]);
```

## Error Handling

### No Forms Applicable
- Message: "No forms applicable for the selected month."
- User can select different month/year

### Missing Template
- Message: "Template not found for form {code}"
- Check template exists in `resources/views/compliance/forms/`

### Data Entry Validation
- All forms must have method selected
- Files must be valid format (PDF/CSV)
- File size limited to 10MB

## Future Enhancements

1. **Batch Templates**: Save and reuse batch configurations
2. **Auto-Generation**: Automatically generate forms after data entry
3. **Validation Rules**: Custom validation per form
4. **Bulk Upload**: Upload multiple files at once
5. **Data Mapping**: Auto-map CSV columns to form fields

## Support

For issues or questions:
1. Check form frequency in `compliance_forms_master`
2. Verify template files exist
3. Check database permissions
4. Review application logs

## Summary

The simplified batch creation workflow:
- ✅ Removes manual section selection
- ✅ Automatically filters forms by frequency
- ✅ Provides flexible data entry methods
- ✅ Maintains backward compatibility
- ✅ Integrates with existing generators
- ✅ Minimal code changes required
