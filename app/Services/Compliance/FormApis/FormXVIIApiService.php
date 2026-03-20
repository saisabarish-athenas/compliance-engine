<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXVIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $periodStart = $this->periodStart;
        $periodEnd = $this->periodEnd;

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.id as employee_id',
                'e.employee_code',
                'e.name',
                'e.designation',
                'pe.basic_earned',
                'pe.da_earned',
                'pe.hra_earned',
                'pe.gross_salary',
                'pe.pf_employee',
                'pe.esi_employee',
                'pe.net_salary',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(function($row) use ($tenantId, $periodStart, $periodEnd) {
                $row = (array)$row;
                $daysWorked = DB::table('workforce_attendance')
                    ->where('employee_id', $row['employee_id'])
                    ->where('tenant_id', $tenantId)
                    ->whereBetween('attendance_date', [$periodStart, $periodEnd])
                    ->whereIn('status', ['present', 'leave'])
                    ->count();
                
                $row['days_worked'] = $daysWorked;
                $row['daily_rate'] = $daysWorked > 0 ? round($row['gross_salary'] / $daysWorked, 2) : 0;
                return $row;
            })
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
