<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class EsiForm11Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('ESI FORM 11');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.date_of_notice as date_of_notice',
                'e.time_of_notice as time_of_notice',
                'e.injured_person as injured_person',
                'e.sex as sex',
                'e.age as age',
                'e.insurance_no as insurance_no',
                'e.occupation as occupation',
                'e.cause as cause',
                'e.nature as nature',
                'e.injury_date as injury_date',
                'e.injury_time as injury_time',
                'e.place as place',
                'e.activity as activity',
                'e.first_aid_person as first_aid_person',
                'e.signature as signature',
                'e.witnesses as witnesses',
                'e.remarks as remarks',
            ])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('ESI FORM 11', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'N/A',
            ],
            'period' => date('F Y', strtotime("$year-$month-01")),
        ];

        if (empty($rows)) {
            return [
                'header' => $header,
                'rows' => [],
                'is_nil' => true,
                'totals' => []
            ];
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'is_nil' => false,
            'totals' => []
        ];
    }
}