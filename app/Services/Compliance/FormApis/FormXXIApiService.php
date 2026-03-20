<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXIApiService extends BaseFormApiService
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
            ->where('pe.fines', '>', 0)
            ->select([
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                DB::raw('"Misconduct" as act_or_omission'),
                'pc.period_from as date_of_offence',
                DB::raw('"Yes" as showed_cause'),
                DB::raw('"Manager" as heard_by'),
                'pe.fines as fine_amount',
                'pc.period_from as fine_realised',
                DB::raw('"" as remarks'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        $rows = array_map(function($row) {
            $row['wage_period'] = \Carbon\Carbon::parse($row['date_of_offence'])->format('F Y');
            return $row;
        }, $rows);

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
            'is_nil' => count($rows) === 0,
        ];
    }
}
