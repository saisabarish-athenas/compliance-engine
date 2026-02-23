# Compliance Engine v2.0 - Upgrade Guide

## Overview
This upgrade adds snapshot storage, form versioning, dynamic due dates, branch management, and form source mapping to the existing Compliance Engine without data loss.

## New Features

### 1. Snapshot Storage
- **Table**: `compliance_generation_logs`
- **New Field**: `generated_snapshot` (JSON)
- **Purpose**: Stores immutable JSON dataset used for PDF generation
- **Benefit**: Complete audit trail, ability to regenerate PDFs from historical data

### 2. Form Versioning
- **Table**: `compliance_status`
- **New Fields**:
  - `version_number` (integer, default 1)
  - `is_revised` (boolean, default false)
  - `revised_from_id` (FK to self)
  - `revision_reason` (text, nullable)
- **Behavior**:
  - When form is regenerated, version_number increments
  - Old version remains locked
  - New version links to old version via revised_from_id
  - Revision reason is mandatory for regeneration

### 3. Dynamic Due Date Calculation
- **Table**: `compliance_forms_master`
- **New Fields**:
  - `due_day` (integer, nullable) - Day of month when form is due
  - `due_month` (integer, nullable) - Month when annual form is due
  - `grace_days` (integer, nullable) - Additional grace period
- **Logic**:
  - If due_day and due_month set: Use specific date
  - If only due_day set: Use that day of next month
  - Otherwise: Use frequency-based defaults
  - Grace days added to calculated due date

### 4. Branch Management
- **New Table**: `branches`
- **Fields**:
  - id, tenant_id, branch_name
  - factory_license_number, address
  - timestamps
- **Purpose**: Proper branch entity management
- **Foreign Key**: compliance_status.branch_id → branches.id

### 5. Form Source Mapping
- **New Table**: `compliance_form_sources`
- **Fields**:
  - id, form_id, source_table, source_type
  - timestamps
- **Source Types**: Payroll, Attendance, CLRA, Upload
- **Purpose**: Dynamic data source configuration per form
- **Benefit**: No hardcoded form logic, configurable data sources

## Migration Files Created

### 2024_01_04_000001_add_snapshot_to_generation_logs.php
Adds `generated_snapshot` JSON field to store complete data snapshot

### 2024_01_04_000002_add_versioning_to_compliance_status.php
Adds versioning fields:
- version_number (default 1)
- is_revised (default false)
- revised_from_id (self-referencing FK)
- revision_reason (text)

### 2024_01_04_000003_add_due_date_fields_to_forms_master.php
Adds due date configuration:
- due_day (1-31)
- due_month (1-12)
- grace_days (additional days)

### 2024_01_04_000004_create_branches_table.php
Creates branches table and adds FK to compliance_status

### 2024_01_04_000005_create_compliance_form_sources_table.php
Creates form source mapping table

## Models Created/Updated

### New Models
1. **Branch** - Branch management with tenant scope
2. **ComplianceFormSource** - Form source mapping

### Updated Models
1. **ComplianceStatus**
   - Added versioning fields to $fillable
   - Added is_revised to $casts
   - Added relationships: branch(), revisedFrom(), revisions()

2. **ComplianceFormsMaster**
   - Added due date fields to $fillable
   - Added relationship: sources()

3. **ComplianceGenerationLog**
   - Added generated_snapshot to $fillable
   - Added array cast for generated_snapshot

4. **Tenant**
   - Added relationship: branches()

## Service Layer Updates

### ComplianceEngine::generateForm()
**New Behavior**:
```php
// Check for existing locked version
$existingStatus = ComplianceStatus::where([...])->orderBy('version_number', 'desc')->first();

// If locked and no revision reason, throw exception
if ($existingStatus && $existingStatus->isLocked() && !$revisionReason) {
    throw new Exception('Provide revision_reason to create new version');
}

// Create new version
$complianceStatus = ComplianceStatus::create([
    'version_number' => $existingStatus ? $existingStatus->version_number + 1 : 1,
    'is_revised' => $existingStatus ? true : false,
    'revised_from_id' => $existingStatus?->id,
    'revision_reason' => $revisionReason,
]);

// Store snapshot in generation log
ComplianceGenerationLog::create([
    'generated_snapshot' => $data, // Full JSON dataset
]);
```

### ComplianceEngine::calculateDueDate()
**New Signature**:
```php
public function calculateDueDate(ComplianceFormsMaster $form, string $periodTo): string
```

**New Logic**:
1. If due_day AND due_month set → Use specific date (e.g., April 15)
2. If only due_day set → Use that day of next month
3. Otherwise → Use frequency-based defaults
4. Add grace_days if configured

### FormDataAggregator::aggregateData()
**New Behavior**:
```php
// Check if form has configured sources
$sources = $form->sources;

if ($sources->isEmpty()) {
    // Fallback to hardcoded logic
    return match($form->form_code) { ... };
}

// Use dynamic source mapping
return $this->aggregateFromSources($sources, $periodFrom, $periodTo, $branchId);
```

## Usage Examples

### Generate Form with Revision
```php
$engine = app(ComplianceEngine::class);

// First generation
$result = $engine->generateForm(
    formId: 1,
    periodFrom: '2024-01-01',
    periodTo: '2024-01-31',
    branchId: 5
);
// Returns: ['version' => 1, 'file_path' => '...']

// Regenerate (revision)
$result = $engine->generateForm(
    formId: 1,
    periodFrom: '2024-01-01',
    periodTo: '2024-01-31',
    branchId: 5,
    revisionReason: 'Corrected employee count'
);
// Returns: ['version' => 2, 'file_path' => '...']
```

### Configure Form Sources
```php
$form = ComplianceFormsMaster::find(1);

ComplianceFormSource::create([
    'form_id' => $form->id,
    'source_table' => 'workforce_payroll_entry',
    'source_type' => 'Payroll',
]);

ComplianceFormSource::create([
    'form_id' => $form->id,
    'source_table' => 'workforce_attendance',
    'source_type' => 'Attendance',
]);
```

### Configure Due Dates
```php
// Monthly form due on 10th of next month with 5 grace days
$form->update([
    'due_day' => 10,
    'grace_days' => 5,
]);

// Annual form due on April 30
$form->update([
    'due_day' => 30,
    'due_month' => 4,
]);
```

### Create Branch
```php
$branch = Branch::create([
    'branch_name' => 'Mumbai Factory',
    'factory_license_number' => 'MH/FAC/2024/001',
    'address' => 'Plot 123, MIDC, Mumbai',
]);
```

### Access Version History
```php
$status = ComplianceStatus::find(1);

// Get original version
$original = $status->revisedFrom;

// Get all revisions
$revisions = $status->revisions;

// Check if revised
if ($status->is_revised) {
    echo "Revision Reason: " . $status->revision_reason;
}
```

### Access Snapshot Data
```php
$log = ComplianceGenerationLog::find(1);

// Access stored snapshot
$snapshot = $log->generated_snapshot;
echo "Total Employees: " . $snapshot['total_employees'];
echo "Total Wages: " . $snapshot['total_wages'];

// Regenerate PDF from snapshot
$pdf = $this->generatePDFFromSnapshot($snapshot);
```

## Migration Execution Order

Run migrations in this order:
1. `2024_01_04_000001_add_snapshot_to_generation_logs.php`
2. `2024_01_04_000002_add_versioning_to_compliance_status.php`
3. `2024_01_04_000003_add_due_date_fields_to_forms_master.php`
4. `2024_01_04_000004_create_branches_table.php`
5. `2024_01_04_000005_create_compliance_form_sources_table.php`

## Data Preservation

✅ All migrations use ALTER TABLE
✅ No existing data is dropped
✅ All new fields are nullable or have defaults
✅ Existing compliance_status records get version_number = 1
✅ Existing forms continue to work with fallback logic

## Breaking Changes

⚠️ **ComplianceEngine::calculateDueDate()** signature changed:
- Old: `calculateDueDate(string $frequency, string $periodTo)`
- New: `calculateDueDate(ComplianceFormsMaster $form, string $periodTo)`

⚠️ **ComplianceEngine::generateForm()** now accepts optional `$revisionReason`:
- Regenerating locked forms requires revision reason
- Returns version number in response

## Backward Compatibility

✅ Forms without configured sources use fallback logic
✅ Forms without due_day/due_month use frequency defaults
✅ Existing compliance_status records work as version 1
✅ Branch_id remains nullable for tenant-level compliance

## Testing Checklist

- [ ] Snapshot storage works and is immutable
- [ ] Version incrementing works correctly
- [ ] Old versions remain locked after revision
- [ ] Revision reason is mandatory for regeneration
- [ ] Due date calculation uses form configuration
- [ ] Grace days are added correctly
- [ ] Branch FK constraint works
- [ ] Form sources are read dynamically
- [ ] Fallback logic works when no sources configured
- [ ] Version history relationships work
- [ ] Snapshot data is retrievable
- [ ] Tenant isolation maintained

## Rollback Plan

If issues occur, rollback migrations in reverse order:
```bash
php artisan migrate:rollback --step=5
```

All data will be preserved as migrations only add columns/tables.
