<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIVApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->select([
                'we.id',
                DB::raw("COALESCE(we.name, '') as employee_name"),
                DB::raw("COALESCE(we.gender, '') as gender"),
                DB::raw("COALESCE(we.designation, '') as designation"),
                DB::raw("COALESCE(cld.deployment_start, '') as date_of_joining"),
                DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                DB::raw("COALESCE(cld.work_description, '') as work_description"),
            ])
            ->orderBy('cld.deployment_start')
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
            'record_count' => count($rows),
        ];
    }
}
