<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormXXIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch ALL active employees with LEFT JOIN on fines for the period.
        // Employees without fines are included — fine columns will be null → NIL in generator.
        $rows = DB::table('workforce_employee as e')
            ->leftJoin('workforce_payroll_entry as pe', function ($join) use ($month, $year) {
                $join->on('pe.employee_id', '=', 'e.id')
                     ->where('pe.fines', '>', 0);
            })
            ->leftJoin('workforce_payroll_cycle as pc', function ($join) use ($month, $year) {
                $join->on('pc.id', '=', 'pe.payroll_cycle_id')
                     ->whereYear('pc.period_from', $year)
                     ->whereMonth('pc.period_from', $month);
            })
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->select([
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                'e.employee_code',
                DB::raw('CASE WHEN pe.fines > 0 THEN "Misconduct" ELSE NULL END as act_or_omission'),
                DB::raw('CASE WHEN pe.fines > 0 THEN pc.period_from ELSE NULL END as date_of_offence'),
                DB::raw('CASE WHEN pe.fines > 0 THEN "Yes" ELSE NULL END as showed_cause'),
                DB::raw('CASE WHEN pe.fines > 0 THEN "Manager" ELSE NULL END as heard_by'),
                DB::raw('CASE WHEN pe.fines > 0 THEN pe.fines ELSE NULL END as fine_amount'),
                DB::raw('CASE WHEN pe.fines > 0 THEN pc.period_from ELSE NULL END as fine_realised'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $row['wage_period'] = $row['date_of_offence']
                    ? Carbon::parse($row['date_of_offence'])->format('F Y')
                    : null;
                $row['date_of_offence'] = $row['date_of_offence']
                    ? Carbon::parse($row['date_of_offence'])->format('d/m/Y')
                    : null;
                $row['fine_realised'] = $row['fine_realised']
                    ? Carbon::parse($row['fine_realised'])->format('d/m/Y')
                    : null;
                $row['remarks'] = null;
                return $row;
            })
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant'  => $this->getTenantDetails($tenantId),
            'branch'  => $this->getBranchDetails($branchId, $tenantId),
            'period'  => $this->formatPeriod(),
            'is_nil'  => count($rows) === 0,
        ];
    }
}
