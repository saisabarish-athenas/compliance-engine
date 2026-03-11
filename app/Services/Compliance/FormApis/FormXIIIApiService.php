<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_date', [$this->periodStart, $this->periodEnd])
            ->select([
                'cld.id',
                DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                DB::raw("COALESCE(cld.workmen_count, 0) as workmen_count"),
                DB::raw("COALESCE(cld.deployment_date, cld.deployment_start) as deployment_date"),
                DB::raw("COALESCE(cld.work_description, '') as work_description"),
            ])
            ->orderBy('cld.deployment_date')
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
