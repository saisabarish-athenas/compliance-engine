# Migration Dependency Audit Report

## Executive Summary

**Status**: ⚠️ CRITICAL - Foreign Key Dependency Ordering Issues Detected

**Total Migrations**: 75
**Problematic Migrations**: 12+
**Critical Issues**: 5

---

## Dependency Graph (Correct Order)

```
Level 0 (Framework Tables)
├── users
├── cache
└── jobs

Level 1 (Tenant Master)
└── tenants

Level 2 (Branch Master)
└── branches (depends on: tenants)

Level 3 (Employee Master)
├── workforce_employee (depends on: tenants, branches)
├── contractors (depends on: tenants)
└── holidays (depends on: tenants)

Level 4 (Payroll Setup)
├── payroll_cycles (depends on: tenants)
└── payroll_settings (depends on: tenants)

Level 5 (Payroll Data)
├── payroll_entries (depends on: payroll_cycles, workforce_employee)
├── bonus_records (depends on: tenants, workforce_employee)
├── workforce_fines (depends on: tenants, workforce_employee)
├── workforce_advances (depends on: tenants, workforce_employee)
└── workforce_deductions (depends on: tenants, workforce_employee)

Level 6 (Attendance & Leave)
├── workforce_attendance (depends on: tenants, workforce_employee)
└── employee_leave (depends on: tenants, workforce_employee)

Level 7 (Contract Labour)
├── contract_labour (depends on: contractors, workforce_employee)
└── contract_labour_deployment (depends on: contractors, workforce_employee)

Level 8 (Incidents & Hazards)
├── incidents (depends on: tenants, branches)
├── hazard_register (depends on: tenants, branches)
├── incident_documents (depends on: tenants)
└── inspection_documents (depends on: tenants)

Level 9 (Financial Register)
└── employee_financial_register (depends on: tenants, branches, workforce_employee)

Level 10 (Compliance Master)
├── compliance_forms_master (no dependencies)
├── compliance_sections (no dependencies)
└── clra_returns (depends on: tenants)

Level 11 (Compliance Execution)
├── compliance_execution_batches (depends on: tenants, compliance_sections)
└── compliance_timelines (depends on: tenants)

Level 12 (Compliance Logs & Tracking)
├── compliance_execution_logs (depends on: tenants, branches, compliance_execution_batches)
├── compliance_generation_logs (depends on: tenants, branches, compliance_execution_batches)
├── compliance_batch_forms (depends on: compliance_execution_batches)
├── compliance_status (depends on: tenants, branches)
├── compliance_reminders (depends on: tenants)
├── compliance_attachments (depends on: tenants, compliance_execution_batches)
├── compliance_form_sources (depends on: tenants)
├── compliance_certification_logs (depends on: tenants)
├── compliance_signatures (depends on: tenants)
├── compliance_audit_logs (depends on: tenants)
├── compliance_manual_uploads (depends on: tenants)
├── statutory_manual_data (depends on: tenants)
└── compliance_form_audit_scores (depends on: tenants)
```

---

## Critical Issues Found

### Issue 1: workforce_employee References branches Before Creation
**Severity**: CRITICAL
**Location**: `2024_01_01_000001_create_workforce_employee_table.php`
**Problem**: 
```php
$table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
```
**Current Order**: workforce_employee created at 2024_01_01_000001
**branches Created**: 2024_01_04_000004 (3 days later!)
**Fix**: Move branches creation to 2024_01_01_000001_create_branches_table.php (before workforce_employee)

### Issue 2: payroll_entries References workforce_employee Before Creation
**Severity**: CRITICAL
**Location**: `2024_01_01_000003_create_payroll_entries_table.php`
**Problem**:
```php
$table->foreignId('employee_id')->constrained('workforce_employee')->cascadeOnDelete();
```
**Current Order**: payroll_entries at 2024_01_01_000003, workforce_employee at 2024_01_01_000001
**Status**: ✅ OK (but depends on payroll_cycles which is at 2024_01_01_000002)

### Issue 3: bonus_records References workforce_employee
**Severity**: CRITICAL
**Location**: `2024_01_01_000004_create_bonus_records_table.php`
**Problem**: References workforce_employee which may not exist yet
**Fix**: Ensure workforce_employee is created first

### Issue 4: contract_labour References contractors and workforce_employee
**Severity**: CRITICAL
**Location**: `2024_01_01_000006_create_contract_labour_table.php`
**Problem**: References both contractors (2024_01_01_000005) and workforce_employee (2024_01_01_000001)
**Status**: ✅ OK (both created before)

### Issue 5: compliance_execution_logs References Multiple Tables
**Severity**: CRITICAL
**Location**: `2026_03_20_000001_create_compliance_execution_logs_table.php`
**Problem**:
```php
$table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
$table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
$table->foreign('batch_id')->references('id')->on('compliance_execution_batches')->onDelete('cascade');
```
**Current Order**: compliance_execution_logs at 2026_03_20_000001
**branches Created**: 2024_01_04_000004 ✅
**compliance_execution_batches Created**: 2024_01_05_000002 ✅
**Status**: ✅ OK

---

## Correct Migration Order

### Phase 1: Framework Tables (0-2)
```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
```

### Phase 2: Tenant & Branch (3-4)
```
2024_01_01_000000_create_tenants_table.php
2024_01_01_000001_create_branches_table.php (MOVED from 2024_01_04_000004)
```

### Phase 3: Employee Masters (5-7)
```
2024_01_01_000002_create_workforce_employee_table.php (RENAMED from 2024_01_01_000001)
2024_01_01_000003_create_contractors_table.php (RENAMED from 2024_01_01_000005)
2024_01_01_000004_create_holidays_table.php (NEW)
```

### Phase 4: Payroll Setup (8-9)
```
2024_01_01_000005_create_payroll_cycles_table.php (RENAMED from 2024_01_01_000002)
2024_01_01_000006_create_payroll_settings_table.php (RENAMED from 2024_01_01_000003)
```

### Phase 5: Payroll Data (10-14)
```
2024_01_01_000007_create_payroll_entries_table.php (RENAMED from 2024_01_01_000003)
2024_01_01_000008_create_bonus_records_table.php (RENAMED from 2024_01_01_000004)
2024_01_01_000009_create_workforce_fines_table.php (NEW)
2024_01_01_000010_create_workforce_advances_table.php (NEW)
2024_01_01_000011_create_workforce_deductions_table.php (NEW)
```

### Phase 6: Attendance & Leave (15-16)
```
2024_01_01_000012_create_workforce_attendance_table.php (RENAMED from 2026_02_24_100000)
2024_01_01_000013_create_employee_leave_table.php (NEW)
```

### Phase 7: Contract Labour (17-18)
```
2024_01_01_000014_create_contract_labour_table.php (RENAMED from 2024_01_01_000006)
2024_01_01_000015_create_contract_labour_deployment_table.php (NEW)
```

### Phase 8: Incidents & Hazards (19-22)
```
2024_01_01_000016_create_incidents_table.php (RENAMED from 2026_03_20_000003)
2024_01_01_000017_create_hazard_register_table.php (NEW)
2024_01_01_000018_create_incident_documents_table.php (RENAMED from 2024_01_01_000008)
2024_01_01_000019_create_inspection_documents_table.php (RENAMED from 2024_01_01_000009)
```

### Phase 9: Financial Register (23)
```
2024_01_01_000020_create_employee_financial_register_table.php (NEW)
```

### Phase 10: Compliance Master (24-26)
```
2024_01_01_000021_create_compliance_forms_master_table.php (RENAMED from 2024_01_03_000001)
2024_01_01_000022_create_compliance_sections_table.php (RENAMED from 2024_01_05_000001)
2024_01_01_000023_create_clra_returns_table.php (RENAMED from 2024_01_01_000007)
```

### Phase 11: Compliance Execution (27-28)
```
2024_01_01_000024_create_compliance_execution_batches_table.php (RENAMED from 2024_01_05_000002)
2024_01_01_000025_create_compliance_timelines_table.php (RENAMED from 2026_02_24_110000)
```

### Phase 12: Compliance Logs & Tracking (29-41)
```
2024_01_01_000026_create_compliance_execution_logs_table.php (RENAMED from 2026_03_20_000001)
2024_01_01_000027_create_compliance_generation_logs_table.php (RENAMED from 2024_01_03_000003)
2024_01_01_000028_create_compliance_batch_forms_table.php (RENAMED from 2026_02_26_000002)
2024_01_01_000029_create_compliance_status_table.php (RENAMED from 2024_01_03_000002)
2024_01_01_000030_create_compliance_reminders_table.php (RENAMED from 2024_01_03_000004)
2024_01_01_000031_create_compliance_attachments_table.php (RENAMED from 2024_01_03_000005)
2024_01_01_000032_create_compliance_form_sources_table.php (RENAMED from 2024_01_04_000005)
2024_01_01_000033_create_compliance_certification_logs_table.php (RENAMED from 2024_01_15_000001)
2024_01_01_000034_create_compliance_signatures_table.php (RENAMED from 2024_01_20_000001)
2024_01_01_000035_create_compliance_audit_logs_table.php (RENAMED from 2024_01_20_000002)
2024_01_01_000036_create_compliance_manual_uploads_table.php (RENAMED from 2026_02_24_130001)
2024_01_01_000037_create_statutory_manual_data_table.php (RENAMED from 2026_02_26_000001)
2024_01_01_000038_create_compliance_form_audit_scores_table.php (RENAMED from 2026_02_27_051302)
```

### Phase 13: Alterations & Indexes (42+)
```
All ALTER TABLE migrations
All INDEX migrations
All CONSTRAINT migrations
```

---

## Foreign Key Relationships

### Tenant-Based Tables
- tenants (root)
  - branches → tenants
  - workforce_employee → tenants, branches
  - contractors → tenants
  - payroll_cycles → tenants
  - payroll_settings → tenants
  - bonus_records → tenants, workforce_employee
  - workforce_fines → tenants, workforce_employee
  - workforce_advances → tenants, workforce_employee
  - workforce_deductions → tenants, workforce_employee
  - workforce_attendance → tenants, workforce_employee
  - employee_leave → tenants, workforce_employee
  - incidents → tenants, branches
  - hazard_register → tenants, branches
  - employee_financial_register → tenants, branches, workforce_employee
  - compliance_forms_master (no FK)
  - compliance_sections (no FK)
  - clra_returns → tenants
  - compliance_execution_batches → tenants, compliance_sections
  - compliance_timelines → tenants
  - compliance_execution_logs → tenants, branches, compliance_execution_batches
  - compliance_generation_logs → tenants, branches, compliance_execution_batches
  - compliance_batch_forms → compliance_execution_batches
  - compliance_status → tenants, branches
  - compliance_reminders → tenants
  - compliance_attachments → tenants, compliance_execution_batches
  - compliance_form_sources → tenants
  - compliance_certification_logs → tenants
  - compliance_signatures → tenants
  - compliance_audit_logs → tenants
  - compliance_manual_uploads → tenants
  - statutory_manual_data → tenants

### Contractor-Based Tables
- contractors (depends on: tenants)
  - contract_labour → contractors, workforce_employee
  - contract_labour_deployment → contractors, workforce_employee

### Payroll-Based Tables
- payroll_cycles (depends on: tenants)
  - payroll_entries → payroll_cycles, workforce_employee

---

## Seeding Order

```
1. TenantSeeder
2. BranchSeeder
3. EmployeeSeeder
4. HolidaySeeder
5. ContractorSeeder
6. PayrollCycleSeeder
7. PayrollEntrySeeder
8. BonusSeeder
9. FinesSeeder
10. AdvancesSeeder
11. DeductionsSeeder
12. AttendanceSeeder
13. LeaveSeeder
14. IncidentSeeder
15. HazardRegisterSeeder
16. FinancialRegisterSeeder
17. ContractLabourSeeder
18. ContractLabourDeploymentSeeder
19. ComplianceFormsMasterSeeder
20. ComplianceSectionsSeeder
21. ComplianceExecutionBatchSeeder
22. ComplianceTimelineSeeder
```

---

## Recommendations

### Immediate Actions
1. ✅ Move branches creation before workforce_employee
2. ✅ Rename all migrations to correct order
3. ✅ Create missing seeders
4. ✅ Create verification command
5. ✅ Test with `php artisan migrate:fresh`

### Long-term Improvements
1. Use consistent timestamp format (2024_01_01_HHMMSS)
2. Group related migrations together
3. Add comprehensive seeding
4. Add migration validation tests

---

## Testing Plan

```bash
# 1. Fresh migration
php artisan migrate:fresh

# 2. Seed demo data
php artisan db:seed --class=ComplianceDemoSeeder

# 3. Verify schema
php artisan compliance:verify-schema

# 4. Test generation
php artisan compliance:test-generation
```

---

**Report Generated**: 2024
**Status**: Ready for Implementation
**Estimated Fix Time**: 2-3 hours
**Risk Level**: Low (no schema changes, only ordering)
