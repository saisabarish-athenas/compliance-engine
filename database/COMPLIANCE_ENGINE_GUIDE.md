# Compliance Engine Module - Implementation Guide

## Overview
The Compliance Engine is a Service Layer module that reads data from workforce and contractor tables to generate compliance forms without duplicating payroll data.

## Database Tables Created

### 1. compliance_forms_master
Master table for all compliance forms
- Unique form_code (e.g., FORM_A, FORM_B, CLRA_WAGE)
- act_type: Factories, CLRA, Shops, EPF, ESI
- frequency: Monthly, Annual, HalfYearly, Event
- auto_generate: Boolean flag for automated generation
- upload_only: Boolean flag for event-based forms

### 2. compliance_status
Tracks compliance form status per tenant/branch/period
- Unique constraint: (tenant_id, branch_id, form_id, period_from, period_to)
- status: Pending, Generated, Uploaded, NIL, Locked
- Multi-branch support via nullable branch_id
- Approval workflow with approved_by and approved_at

### 3. compliance_generation_logs
Audit trail for all form generations
- Stores file_path and checksum_hash
- Captures ip_address and user_agent
- Links to compliance_status_id

### 4. compliance_reminders
Automated reminder system
- reminder_type: Monthly, Annual, Expiry, Event
- Tracks due_date and reminder_sent_at
- status: Pending, Sent, Skipped

### 5. compliance_attachments
Stores uploaded documents for event-based forms
- Links to compliance_status_id
- Stores reference_number and remarks
- Tracks uploaded_by user

## Models Created

1. **ComplianceFormsMaster** - Master form definitions
2. **ComplianceStatus** - Form status tracking with tenant scope
3. **ComplianceGenerationLog** - Audit trail
4. **ComplianceReminder** - Reminder management
5. **ComplianceAttachment** - Document uploads

All models include proper relationships and tenant scoping.

## Service Layer Architecture

### 1. ComplianceEngine (Core Service)
**Methods:**
- `generateForm()` - Main form generation workflow
- `validatePrerequisites()` - Validates payroll lock status
- `lockForm()` - Locks form after generation
- `markAsNIL()` - Marks form as NIL return
- `checkSubscription()` - Validates module subscription
- `calculateDueDate()` - Calculates form due dates

**Workflow:**
1. Check module subscription
2. Validate prerequisites (payroll lock)
3. Create/fetch compliance_status record
4. Aggregate data from source tables
5. Generate PDF and JSON snapshot
6. Create generation log with checksum
7. Update status to Generated
8. Lock form
9. Commit transaction

### 2. FormDataAggregator
**Methods:**
- `aggregateWageRegister()` - Fetches wage data from workforce_payroll_entry
- `aggregateOTRegister()` - Fetches overtime data
- `aggregateCLRAWage()` - Fetches contract labour wages
- `aggregateBonus()` - Fetches bonus records
- `aggregateAttendance()` - Fetches attendance data

**Data Sources:**
- workforce_payroll_entry
- workforce_payroll_cycle
- workforce_employee
- workforce_attendance
- workforce_bonus_record
- contract_labour_deployment
- contractor_master

### 3. ComplianceReminderService
**Methods:**
- `generateMonthlyReminders()` - Creates monthly form reminders
- `generateAnnualReminders()` - Creates annual form reminders
- `checkExpiringContractorLicense()` - Checks CLRA license expiry
- `sendPendingReminders()` - Sends pending notifications

### 4. ComplianceLockService
**Methods:**
- `lockAfterGeneration()` - Locks form after generation
- `preventEditIfLocked()` - Throws exception if locked
- `unlockForm()` - Admin unlock functionality

## Subscription Control

Module access controlled via `tenant_services` table:

```php
$moduleMap = [
    'Factories' => 'workforce',
    'CLRA' => 'clra',
    'Shops' => 'shops',
    'EPF' => 'epf',
    'ESI' => 'esi',
];
```

Forms only generate if corresponding module is enabled.

## Multi-Branch Support

- If `branch_id` is provided, compliance tracked per branch
- If `branch_id` is NULL, tracked at tenant level
- Unique constraint ensures no duplicate periods per branch

## Form Generation Flow

```
User Request
    ↓
Check Subscription → Fail: Throw Exception
    ↓
Validate Prerequisites (Payroll Lock) → Fail: Throw Exception
    ↓
Create/Fetch ComplianceStatus
    ↓
Check if Locked → Yes: Throw Exception
    ↓
Aggregate Data from Source Tables
    ↓
Generate PDF + JSON Snapshot
    ↓
Calculate Checksum
    ↓
Create Generation Log
    ↓
Update Status to Generated
    ↓
Lock Form
    ↓
Return Success
```

## Migration Order

1. `2024_01_03_000001_create_compliance_forms_master_table.php`
2. `2024_01_03_000002_create_compliance_status_table.php`
3. `2024_01_03_000003_create_compliance_generation_logs_table.php`
4. `2024_01_03_000004_create_compliance_reminders_table.php`
5. `2024_01_03_000005_create_compliance_attachments_table.php`

## Foreign Key Relationships

- compliance_status → compliance_forms_master (form_id)
- compliance_status → tenants (tenant_id)
- compliance_status → users (approved_by)
- compliance_generation_logs → compliance_status (compliance_status_id)
- compliance_generation_logs → users (generated_by)
- compliance_reminders → compliance_forms_master (form_id)
- compliance_attachments → compliance_status (compliance_status_id)
- compliance_attachments → users (uploaded_by)

## Usage Examples

### Generate Monthly Wage Register
```php
$engine = app(ComplianceEngine::class);
$result = $engine->generateForm(
    formId: 1,
    periodFrom: '2024-01-01',
    periodTo: '2024-01-31',
    branchId: 5
);
```

### Mark Form as NIL
```php
$engine->markAsNIL(
    complianceStatusId: 123,
    reason: 'No employees during this period'
);
```

### Generate Reminders
```php
$reminderService = app(ComplianceReminderService::class);
$reminderService->generateMonthlyReminders();
$reminderService->checkExpiringContractorLicense();
```

## Security Features

1. **Tenant Isolation** - Global scopes on all models
2. **Form Locking** - Prevents modification after generation
3. **Audit Trail** - Complete generation logs with checksums
4. **IP Tracking** - Captures IP and user agent
5. **Approval Workflow** - Optional approval before finalization

## Event-Based Forms

For upload-only forms (e.g., inspection reports):
- Set `upload_only = true` in compliance_forms_master
- Users upload documents via compliance_attachments
- No auto-generation occurs
- Status tracked in compliance_status

## Production Considerations

1. **Storage** - Configure file storage path for PDFs
2. **Notifications** - Implement email/SMS for reminders
3. **PDF Generation** - Integrate PDF library (e.g., DomPDF, Snappy)
4. **Cron Jobs** - Schedule reminder generation
5. **Permissions** - Implement role-based access control
6. **Backup** - Regular backup of compliance_generation_logs
7. **Archival** - Archive old compliance records

## Testing Checklist

- [ ] Subscription validation works
- [ ] Payroll lock validation works
- [ ] Multi-branch filtering works
- [ ] Form locking prevents edits
- [ ] Checksums validate correctly
- [ ] Reminders generate on schedule
- [ ] Tenant isolation enforced
- [ ] Foreign keys cascade properly
- [ ] Unique constraints prevent duplicates
- [ ] Audit trail captures all actions
