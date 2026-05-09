<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormAApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                DB::raw("'' as surname"),
                'e.father_name',
                'e.gender',
                'e.date_of_birth',
                'e.permanent_address',
                DB::raw('COALESCE(e.local_address, e.permanent_address) as present_address'),
                'e.date_of_joining',
                'e.designation',
                'e.department',
                'e.esi_number',
                'e.pf_number',
                'e.status',
                DB::raw("'Indian' as nationality"),
                DB::raw("'' as education_level"),
                DB::raw("'Temporary' as employment_type"),
                DB::raw("'' as category"),
                DB::raw("'' as mobile"),
                DB::raw("'' as uan_number"),
                DB::raw("'' as aadhaar_number"),
                DB::raw("'' as bank_name"),
                DB::raw("'' as bank_account_number"),
                DB::raw("'' as ifsc_code"),
                DB::raw("'' as date_of_exit"),
                DB::raw("'' as reason_for_exit"),
                DB::raw("'' as identification_mark"),
                DB::raw("'' as lwf"),
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
