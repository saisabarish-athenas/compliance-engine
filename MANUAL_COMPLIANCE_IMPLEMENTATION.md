# Manual Compliance Tracking - Implementation Guide

## Overview

Manual Compliance Tracking extends the existing batch-based workflow to include manual and event-based compliances alongside automated forms. The system maintains complete separation from the automated form generation pipeline.

## Architecture

```
BatchOrchestrator::createBatch()
├── Attach automated forms (existing)
└── Load manual compliances (new)
    └── ManualComplianceLoaderService::loadForBatch()
        └── Insert records into compliance_manual_batch_items
```

## Database Schema

### compliance_manual_master
Master table for all manual compliance definitions.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| compliance_name | string | Name of compliance (e.g., "Factory License Renewal") |
| act_name | string | Associated act (e.g., "Factories Act") |
| frequency | enum | monthly, quarterly, annual, event |
| due_month | integer | Month when due (1-12), nullable for non-annual |
| requires_document | boolean | Whether document upload is required |
| is_event_based | boolean | Whether triggered by events |
| created_at | timestamp | |
| updated_at | timestamp | |

### compliance_manual_batch_items
Tracks manual compliance items per batch.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| batch_id | bigint | FK to compliance_execution_batches |
| tenant_id | bigint | Multi-tenant isolation |
| branch_id | bigint | Branch-level tracking |
| compliance_id | bigint | FK to compliance_manual_master |
| status | enum | pending, uploaded, skipped |
| document_path | string | Path to uploaded document |
| remarks | text | Additional notes |
| created_at | timestamp | |
| updated_at | timestamp | |

## Frequency Rules

### Monthly
- Included in every batch

### Quarterly
- Included only in months: 3, 6, 9, 12

### Annual
- Included only in the specified due_month

### Event
- Always included (triggered manually when events occur)

## Models

### ManualComplianceMaster
```php
use App\Models\ManualComplianceMaster;

$compliance = ManualComplianceMaster::find(1);
// Access: $compliance->compliance_name, $compliance->frequency, etc.
```

### ManualComplianceBatchItem
```php
use App\Models\ManualComplianceBatchItem;

$item = ManualComplianceBatchItem::find(1);
$item->batch;        // ComplianceExecutionBatch
$item->compliance;   // ManualComplianceMaster
```

## Service: ManualComplianceLoaderService

### Method: loadForBatch()

```php
use App\Services\Compliance\ManualComplianceLoaderService;

$loader = app(ManualComplianceLoaderService::class);
$loader->loadForBatch($batch);
```

**Logic:**
1. Extracts batch month and year
2. Queries compliance_manual_master for applicable compliances
3. Applies frequency rules
4. Inserts records into compliance_manual_batch_items with status "pending"

**Frequency Application:**
- Monthly: Always included
- Quarterly: Included if month in [3, 6, 9, 12]
- Annual: Included if due_month matches batch month
- Event: Always included

## Integration with BatchOrchestrator

The loader is automatically called after batch creation:

```php
// In BatchOrchestrator::createBatch()
$batch = ComplianceExecutionBatch::create([...]);
$this->attachFormsToBatch($batch, $applicableForms, $sectionName);
$this->manualLoader->loadForBatch($batch);  // NEW
return $batch;
```

**Dependency Injection:**
```php
public function __construct(
    private FrequencyEngine $frequencyEngine,
    private ManualComplianceLoaderService $manualLoader  // NEW
) {}
```

## Seeding Data

Run the seeder to populate example compliances:

```bash
php artisan db:seed --class=ManualComplianceMasterSeeder
```

**Seeded Compliances:**
- Factory License Renewal (annual, due_month=1)
- Fire Safety Certificate (annual)
- ESI Contribution Filing (monthly)
- EPF Contribution Filing (monthly)
- Accident Investigation Report (event)
- Industrial Dispute Filing (event)

## Multi-Tenant Safety

All records enforce tenant and branch isolation:

```php
// In ManualComplianceLoaderService::loadForBatch()
'tenant_id' => $batch->tenant_id,
'branch_id' => $batch->branch_id,
```

Queries automatically filter by tenant_id and branch_id through batch context.

## Workflow Example

### Scenario: Create batch for March 2024

```php
$batch = $orchestrator->createBatch(
    tenantId: 1,
    month: 3,
    year: 2024
);
```

**Result:**
1. Batch created with period_month=3
2. Automated forms attached (from FrequencyEngine)
3. Manual compliances loaded:
   - ESI Contribution Filing (monthly)
   - EPF Contribution Filing (monthly)
   - Quarterly compliances (month=3)
   - Event-based compliances

**Database State:**
- compliance_execution_batches: 1 record
- compliance_batch_forms: N records (automated)
- compliance_manual_batch_items: M records (manual)

## Important Notes

### No Modification to Existing Workflow
- Automated form generation pipeline unchanged
- API Services → Generators → Blade Templates unaffected
- Routes, controllers, existing services untouched

### Independent Operation
- Manual compliance loading runs after batch creation
- Failures in manual loading don't affect batch creation
- Can be extended independently

### Status Tracking
- pending: Awaiting document upload
- uploaded: Document uploaded
- skipped: Compliance skipped for this batch

## Testing

### Quick Test
```bash
php artisan tinker
>>> $batch = app(\App\Services\Compliance\BatchOrchestrator::class)->createBatch(1, 3, 2024);
>>> $batch->id
>>> DB::table('compliance_manual_batch_items')->where('batch_id', $batch->id)->count()
```

### Verify Frequency Rules
```bash
php artisan tinker
>>> $items = DB::table('compliance_manual_batch_items')->where('batch_id', 1)->get();
>>> $items->pluck('compliance_id')->unique()->count()
```

## File Structure

```
app/
├── Models/
│   ├── ManualComplianceMaster.php
│   └── ManualComplianceBatchItem.php
└── Services/Compliance/
    ├── ManualComplianceLoaderService.php
    └── BatchOrchestrator.php (updated)

database/
├── migrations/
│   ├── 2026_03_25_000003_create_compliance_manual_master_table.php
│   └── 2026_03_25_000004_create_compliance_manual_batch_items_table.php
└── seeders/
    └── ManualComplianceMasterSeeder.php
```

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed data: `php artisan db:seed --class=ManualComplianceMasterSeeder`
- [ ] Test batch creation: `php artisan tinker`
- [ ] Verify manual items created
- [ ] Check multi-tenant isolation
- [ ] Monitor logs for errors

## Future Extensions

- Add document upload handling
- Implement status update workflows
- Add compliance reminders
- Create reporting dashboard
- Add compliance history tracking
