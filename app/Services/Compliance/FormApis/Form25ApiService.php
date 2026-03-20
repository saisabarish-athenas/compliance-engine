<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form25ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee as we')
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->whereExists(function($query) use ($year, $month) {
                $query->select(DB::raw(1))
                    ->from('workforce_attendance as wa')
                    ->whereColumn('wa.employee_id', 'we.id')
                    ->whereYear('wa.attendance_date', $year)
                    ->whereMonth('wa.attendance_date', $month);
            })
            ->select([
                'we.id',
                'we.name',
                'we.father_name',
                'we.designation',
                'we.date_of_birth',
            ])
            ->orderBy('we.name')
            ->get()
            ->toArray();

        // Add attendance date from first attendance record for each employee
        foreach ($rows as &$row) {
            $attendance = DB::table('workforce_attendance')
                ->where('employee_id', $row->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->select('attendance_date')
                ->orderBy('attendance_date')
                ->first();
            $row->attendance_date = $attendance?->attendance_date ?? '';
            unset($row->id);
        }

        return [
            'records' => array_map(fn($row) => (array)$row, $rows),
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
