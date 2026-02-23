<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ComplianceFullDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. TENANTS
        DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'ABC Manufacturing Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. BRANCHES
        DB::table('branches')->insert([
            ['id' => 1, 'tenant_id' => 1, 'branch_name' => 'Main Factory', 'factory_license_number' => 'FAC/2024/001', 'address' => 'Industrial Area, Sector 5', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tenant_id' => 1, 'branch_name' => 'Unit 2', 'factory_license_number' => 'FAC/2024/002', 'address' => 'Industrial Area, Sector 8', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. USERS
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@abc.com', 'password' => Hash::make('password'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'HR Manager', 'email' => 'hr@abc.com', 'password' => Hash::make('password'), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. EMPLOYEES
        $employees = [];
        for ($i = 1; $i <= 10; $i++) {
            $employees[] = [
                'id' => $i,
                'tenant_id' => 1,
                'branch_id' => $i <= 5 ? 1 : 2,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'pf_number' => 'PF' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'date_of_joining' => now()->subMonths(rand(6, 24))->format('Y-m-d'),
                'designation' => ['Operator', 'Supervisor', 'Manager', 'Technician'][rand(0, 3)],
                'department' => ['Production', 'Quality', 'Maintenance'][rand(0, 2)],
                'basic_salary' => 15000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('workforce_employee')->insert($employees);

        // 5. PAYROLL CYCLES
        DB::table('workforce_payroll_cycle')->insert([
            ['id' => 1, 'tenant_id' => 1, 'cycle_name' => 'January 2024', 'period_from' => '2024-01-01', 'period_to' => '2024-01-31', 'status' => 'locked', 'processed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tenant_id' => 1, 'cycle_name' => 'February 2024', 'period_from' => '2024-02-01', 'period_to' => '2024-02-29', 'status' => 'draft', 'processed_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. PAYROLL ENTRIES
        $entries = [];
        foreach ($employees as $emp) {
            $entries[] = [
                'tenant_id' => 1,
                'payroll_cycle_id' => 1,
                'employee_id' => $emp['id'],
                'total_days_worked' => 26,
                'paid_leave_days' => 0,
                'unpaid_leave_days' => 0,
                'overtime_hours' => rand(0, 10),
                'basic_earned' => 15000,
                'da_earned' => 3000,
                'hra_earned' => 6000,
                'other_allowances' => 0,
                'overtime_wages' => rand(0, 10) * 150,
                'gross_salary' => 24000 + (rand(0, 10) * 150),
                'pf_employee' => 1800,
                'esi_employee' => 180,
                'professional_tax' => 200,
                'fines' => 0,
                'advances' => 0,
                'other_deductions' => 0,
                'total_deductions' => 2180,
                'net_salary' => 21820 + (rand(0, 10) * 150),
                'payment_date' => '2024-02-05',
                'payment_mode' => 'Bank Transfer',
                'transaction_reference' => 'TXN' . str_pad($emp['id'], 6, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('workforce_payroll_entry')->insert($entries);

        // 7. BONUS RECORDS
        DB::table('bonus_records')->insert([
            ['tenant_id' => 1, 'employee_id' => 1, 'financial_year' => '2023-2024', 'bonus_percentage' => 8.33, 'bonus_amount' => 2000, 'payment_date' => '2024-01-15', 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => 1, 'employee_id' => 2, 'financial_year' => '2023-2024', 'bonus_percentage' => 8.33, 'bonus_amount' => 2000, 'payment_date' => '2024-01-15', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. CONTRACTORS (will be renamed to contractor_master by migration)
        DB::table('contractor_master')->insert([
            ['id' => 1, 'tenant_id' => 1, 'company_name' => 'XYZ Contractors', 'license_number' => 'CLRA/2024/001', 'valid_from' => '2024-01-01', 'valid_to' => '2024-12-31', 'max_worker_limit' => 50, 'pf_code' => 'PF001', 'esi_code' => 'ESI001', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tenant_id' => 1, 'company_name' => 'PQR Services', 'license_number' => 'CLRA/2024/002', 'valid_from' => '2024-01-01', 'valid_to' => '2024-12-31', 'max_worker_limit' => 30, 'pf_code' => 'PF002', 'esi_code' => 'ESI002', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 9. CONTRACTOR COMPLIANCE
        DB::table('contractor_compliance')->insert([
            ['id' => 1, 'contractor_id' => 1, 'branch_id' => 1, 'clra_license_number' => 'CLRA/2024/001', 'license_valid_from' => '2024-01-01', 'license_valid_to' => '2024-12-31', 'max_worker_limit' => 50, 'pf_code' => 'PF001', 'esi_code' => 'ESI001', 'is_compliant' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'contractor_id' => 2, 'branch_id' => 1, 'clra_license_number' => 'CLRA/2024/002', 'license_valid_from' => '2024-01-01', 'license_valid_to' => '2024-12-31', 'max_worker_limit' => 30, 'pf_code' => 'PF002', 'esi_code' => 'ESI002', 'is_compliant' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 10. CONTRACT LABOUR DEPLOYMENT
        DB::table('contract_labour_deployment')->insert([
            ['tenant_id' => 1, 'contractor_id' => 1, 'contractor_compliance_id' => 1, 'branch_id' => 1, 'employee_id' => 6, 'deployment_start' => '2024-01-01', 'deployment_end' => null, 'wage_rate' => 500, 'work_order_number' => 'WO/2024/001', 'work_order_date' => '2024-01-01', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => 1, 'contractor_id' => 2, 'contractor_compliance_id' => 2, 'branch_id' => 1, 'employee_id' => 7, 'deployment_start' => '2024-01-01', 'deployment_end' => null, 'wage_rate' => 550, 'work_order_number' => 'WO/2024/002', 'work_order_date' => '2024-01-01', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 11. CLRA RETURNS
        DB::table('clra_returns')->insert([
            ['tenant_id' => 1, 'return_type' => 'half_yearly', 'period_from' => '2024-01-01', 'period_to' => '2024-06-30', 'total_workers' => 5, 'total_wages' => 75000, 'total_ot' => 5000, 'total_deductions' => 3000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 12. COMPLIANCE SECTIONS
        DB::table('compliance_sections')->insert([
            ['id' => 1, 'section_name' => 'Factories Act', 'section_code' => 'FACTORIES', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'section_name' => 'CLRA', 'section_code' => 'CLRA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'section_name' => 'Shops & Establishments', 'section_code' => 'SHOPS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 13. COMPLIANCE FORMS MASTER
        DB::table('compliance_forms_master')->insert([
            // Factories Act Forms
            ['id' => 1, 'section_id' => 1, 'form_code' => 'FORM_A', 'form_name' => 'Register of Adult Workers', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'section_id' => 1, 'form_code' => 'FORM_B', 'form_name' => 'Muster Roll', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'section_id' => 1, 'form_code' => 'FORM_C', 'form_name' => 'Overtime Register', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'Medium', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // CLRA Forms
            ['id' => 4, 'section_id' => 2, 'form_code' => 'CLRA_FORM_XIII', 'form_name' => 'Register of Workmen', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'section_id' => 2, 'form_code' => 'CLRA_WAGE', 'form_name' => 'Wage Register', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'section_id' => 2, 'form_code' => 'CLRA_RETURN', 'form_name' => 'Half Yearly Return', 'act_type' => 'CLRA', 'frequency' => 'HalfYearly', 'priority' => 'Medium', 'auto_generate' => false, 'upload_only' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Shops Forms
            ['id' => 7, 'section_id' => 3, 'form_code' => 'SHOPS_REG', 'form_name' => 'Employee Register', 'act_type' => 'Shops', 'frequency' => 'Annual', 'priority' => 'Low', 'auto_generate' => false, 'upload_only' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'section_id' => 3, 'form_code' => 'SHOPS_LEAVE', 'form_name' => 'Leave Register', 'act_type' => 'Shops', 'frequency' => 'Annual', 'priority' => 'Low', 'auto_generate' => false, 'upload_only' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'section_id' => 3, 'form_code' => 'SHOPS_ATTENDANCE', 'form_name' => 'Attendance Register', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'Medium', 'auto_generate' => true, 'upload_only' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 14. COMPLIANCE FORM SOURCES
        DB::table('compliance_form_sources')->insert([
            ['form_id' => 1, 'source_type' => 'Payroll', 'source_table' => 'workforce_payroll_entry', 'created_at' => now(), 'updated_at' => now()],
            ['form_id' => 2, 'source_type' => 'Payroll', 'source_table' => 'workforce_payroll_entry', 'created_at' => now(), 'updated_at' => now()],
            ['form_id' => 4, 'source_type' => 'CLRA', 'source_table' => 'contract_labour_deployment', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 15. COMPLIANCE EXECUTION BATCHES
        DB::table('compliance_execution_batches')->insert([
            ['id' => 1, 'tenant_id' => 1, 'section_id' => 1, 'period_from' => '2024-01-01', 'period_to' => '2024-01-31', 'form_ids' => json_encode([1, 2, 3]), 'branch_id' => 1, 'status' => 'completed', 'created_by' => 1, 'processed_at' => now(), 'results' => json_encode(['1' => ['success' => true], '2' => ['success' => true], '3' => ['success' => true]]), 'generated_report_path' => 'compliance/reports/batch_1.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tenant_id' => 1, 'section_id' => 2, 'period_from' => '2024-02-01', 'period_to' => '2024-02-29', 'form_ids' => json_encode([4, 5]), 'branch_id' => 1, 'status' => 'pending', 'created_by' => 1, 'processed_at' => null, 'results' => null, 'generated_report_path' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 16. COMPLIANCE STATUS
        DB::table('compliance_status')->insert([
            ['tenant_id' => 1, 'branch_id' => 1, 'form_id' => 1, 'period_from' => '2024-01-01', 'period_to' => '2024-01-31', 'status' => 'Generated', 'generated_at' => now(), 'uploaded_at' => null, 'approved_by' => null, 'approved_at' => null, 'version_number' => 1, 'is_revised' => false, 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => 1, 'branch_id' => 1, 'form_id' => 2, 'period_from' => '2024-01-01', 'period_to' => '2024-01-31', 'status' => 'Generated', 'generated_at' => now(), 'uploaded_at' => null, 'approved_by' => null, 'approved_at' => null, 'version_number' => 1, 'is_revised' => false, 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => 1, 'branch_id' => 1, 'form_id' => 3, 'period_from' => '2024-01-01', 'period_to' => '2024-01-31', 'status' => 'Locked', 'generated_at' => now(), 'uploaded_at' => now(), 'approved_by' => 1, 'approved_at' => now(), 'version_number' => 1, 'is_revised' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 17. COMPLIANCE GENERATION LOGS
        $snapshot = [
            'period_from' => '2024-01-01',
            'period_to' => '2024-01-31',
            'total_employees' => 10,
            'total_wages' => 240000,
            'entries' => [
                ['employee_code' => 'EMP0001', 'name' => 'Employee 1', 'gross' => 24000, 'net' => 22020],
                ['employee_code' => 'EMP0002', 'name' => 'Employee 2', 'gross' => 24000, 'net' => 22020],
            ]
        ];

        DB::table('compliance_generation_logs')->insert([
            ['tenant_id' => 1, 'form_id' => 1, 'compliance_status_id' => 1, 'generated_by' => 1, 'file_path' => 'compliance/form_a_jan2024.pdf', 'checksum_hash' => hash('sha256', 'dummy_content_1'), 'generated_snapshot' => json_encode($snapshot), 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()],
            ['tenant_id' => 1, 'form_id' => 2, 'compliance_status_id' => 2, 'generated_by' => 1, 'file_path' => 'compliance/form_b_jan2024.pdf', 'checksum_hash' => hash('sha256', 'dummy_content_2'), 'generated_snapshot' => json_encode($snapshot), 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()],
        ]);

        // 18. COMPLIANCE REMINDERS
        DB::table('compliance_reminders')->insert([
            ['tenant_id' => 1, 'form_id' => 1, 'reminder_type' => 'Monthly', 'due_date' => now()->addDays(10)->format('Y-m-d'), 'reminder_sent_at' => null, 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => 1, 'form_id' => 4, 'reminder_type' => 'Monthly', 'due_date' => now()->addDays(15)->format('Y-m-d'), 'reminder_sent_at' => null, 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 19. COMPLIANCE ATTACHMENTS
        DB::table('compliance_attachments')->insert([
            ['tenant_id' => 1, 'form_id' => 3, 'compliance_status_id' => 3, 'file_path' => 'attachments/form_c_supporting_doc.pdf', 'uploaded_by' => 1, 'reference_number' => 'ATT001', 'remarks' => 'Supporting document', 'created_at' => now()],
        ]);

        // 20. INCIDENT DOCUMENTS
        DB::table('incident_documents')->insert([
            ['tenant_id' => 1, 'employee_id' => 1, 'incident_type' => 'accident', 'incident_date' => '2024-01-15', 'location' => 'Production Floor', 'description' => 'Worker slipped on wet floor', 'uploaded_by' => 1, 'document_path' => 'incidents/incident_001.pdf', 'uploaded_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 21. INSPECTION DOCUMENTS
        DB::table('inspection_documents')->insert([
            ['tenant_id' => 1, 'inspection_type' => 'factory', 'inspection_date' => '2024-01-20', 'inspecting_authority' => 'Factory Inspector', 'reference_number' => 'INS001', 'document_path' => 'inspections/inspection_001.pdf', 'remarks' => 'Routine inspection', 'uploaded_by' => 1, 'uploaded_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
