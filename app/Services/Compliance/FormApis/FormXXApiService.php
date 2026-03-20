<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_deductions as wd')
            ->join('workforce_employee as e', 'e.id', '=', 'wd.employee_id')
            ->where('wd.tenant_id', $tenantId)
            ->where('wd.branch_id', $branchId)
            ->whereYear('wd.deduction_date', $year)
            ->whereMonth('wd.deduction_date', $month)
            ->select([
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                'wd.particulars as damage_particulars',
                'wd.deduction_date as damage_date',
                'wd.amount as deduction_amount',
                'wd.showed_cause',
                'wd.witness_name',
                'wd.num_instalments as instalments',
                'wd.first_month',
                'wd.last_month',
                'wd.remarks',
            ])
            ->orderBy('wd.deduction_date')
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
