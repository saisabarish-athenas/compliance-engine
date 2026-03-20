<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFormCApiService extends BaseFormApiService
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
                'e.father_name',
                'e.date_of_birth',
                'e.designation',
                'pe.total_days_worked as days_worked',
                'pe.gross_salary as total_wages',
                DB::raw('0 as bonus_amount'),
                DB::raw('0 as puja_bonus'),
                DB::raw('0 as interim_bonus'),
                'pe.professional_tax as tax_deducted',
                'pe.other_deductions as loss_deduction',
                DB::raw('0 as bonus_paid'),
                DB::raw('NULL as bonus_payment_date'),
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
