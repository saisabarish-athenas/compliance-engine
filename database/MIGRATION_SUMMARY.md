# Athens 2.0 Schema Migration Summary

## Migration Files Created

### 1. 2024_01_02_000001_standardize_tenant_id_column.php
**Purpose**: Standardize tenant column across all tables
- Converts `athens_tenant_id` → `tenant_id` (unsignedBigInteger)
- Preserves existing data during migration
- Adds index on tenant_id
- Affects tables: workforce_employee, workforce_payroll_cycle, workforce_attendance, contractor_master, contract_labour_deployment, clra_returns, incident_documents, inspection_documents, compliance_forms, bonus_records

### 2. 2024_01_02_000002_restructure_clra_module.php
**Purpose**: Restructure CLRA module with new contractor architecture
- Renames `contractors` → `contractor_master`
- Adds new fields to contractor_master:
  - company_type, company_address, contact_person, contact_number
  - email, pan_number, gst_number, status
- Creates new `contractor_compliance` table
- Renames `contract_labour` → `contract_labour_deployment`
- Adds new fields: tenant_id, contractor_compliance_id, project_id, branch_id, work_order_number, work_order_date, status
- Renames columns: employment_start → deployment_start, employment_end → deployment_end

### 3. 2024_01_02_000003_add_payroll_constraints.php
**Purpose**: Add validation constraints to payroll module
- Renames: `payroll_cycles` → `workforce_payroll_cycle`, `payroll_entries` → `workforce_payroll_entry`
- Adds unique constraint on workforce_payroll_cycle (tenant_id, cycle_name)
- Adds unique constraint on workforce_payroll_entry (payroll_cycle_id, employee_id)
- Adds foreign keys to workforce_employee

### 4. 2024_01_02_000004_add_soft_deletes.php
**Purpose**: Add soft delete capability
- Adds deleted_at to: workforce_employee, contractor_master, contractor_compliance, contract_labour_deployment, workforce_payroll_cycle, workforce_payroll_entry

### 5. 2024_01_02_000005_add_composite_indexes.php
**Purpose**: Add performance indexes
- workforce_attendance: (employee_id, date)
- workforce_payroll_entry: (tenant_id, payroll_cycle_id)
- contract_labour_deployment: (tenant_id, project_id)
- contractor_compliance: (contractor_id, branch_id)

## New Models Created

1. WorkforcePayrollCycle.php - Table: workforce_payroll_cycle
2. WorkforcePayrollEntry.php - Table: workforce_payroll_entry
3. ContractorMaster.php - Table: contractor_master
4. ContractorCompliance.php - Table: contractor_compliance
5. ContractLabourDeployment.php - Table: contract_labour_deployment

## Migration Execution Order

1. standardize_tenant_id_column
2. restructure_clra_module
3. add_payroll_constraints
4. add_soft_deletes
5. add_composite_indexes
