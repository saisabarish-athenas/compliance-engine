<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormBApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                DB::raw("COALESCE(e.pf_number, '') as uan"),
                DB::raw('COALESCE(e.basic_salary, pe.basic_earned, 0) as rate_of_wage'),
                'pe.basic_earned',
                'pe.total_days_worked',
                DB::raw('COALESCE(pe.overtime_hours, 0) as overtime_hours'),
                DB::raw('COALESCE(pe.da_earned, 0) as da_earned'),
                DB::raw('COALESCE(pe.hra_earned, 0) as hra_earned'),
                DB::raw('COALESCE(pe.other_allowances, 0) as special_allowance'),
                DB::raw('COALESCE(pe.overtime_wages, 0) as overtime_wages'),
                DB::raw('0 as other_earnings'),
                'pe.gross_salary',
                DB::raw('COALESCE(pe.pf_employee, 0) as pf_employee'),
                DB::raw('0 as pf_employer'),
                DB::raw('COALESCE(pe.esi_employee, 0) as esi_employee'),
                DB::raw('COALESCE(pe.other_deductions, 0) as other_deductions'),
                DB::raw('COALESCE(pe.professional_tax, 0) as pt_deduction'),
                DB::raw('COALESCE(pe.advances, 0) as recovery'),
                'pe.total_deductions',
                'pe.net_salary',
                DB::raw("COALESCE(pe.payment_date, '') as payment_date"),
                DB::raw("COALESCE(pe.transaction_reference, '') as bank_transaction_id"),
                DB::raw("'' as remarks"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
