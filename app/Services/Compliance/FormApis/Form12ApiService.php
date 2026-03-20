<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form12ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->select([
                'employee_code',
                'name',
                'permanent_address as address',
                'father_name',
                'designation',
                'date_of_joining',
                'date_of_birth',
                'gender',
            ])
            ->orderBy('employee_code')
            ->get()
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
