<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form11Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_11');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('incident_documents as id')
            ->join('workforce_employee as e', 'e.id', '=', 'id.employee_id')
            ->where('id.tenant_id', $tenantId)
            ->where('id.branch_id', $branchId)
            ->where('id.incident_type', 'accident')
            ->whereBetween('id.incident_date', [$startDate, $endDate])
            ->select([
                DB::raw("DATE(id.incident_date, '%Y-%m-%d') as date_of_notice"),
                DB::raw("'' as time_of_notice"),
                'e.name as injured_person',
                DB::raw("'' as sex"),
                DB::raw("0 as age"),
                DB::raw("'' as insurance_no"),
                DB::raw("'' as occupation"),
                'id.description as cause',
                DB::raw("'' as nature"),
                'id.incident_date as injury_date',
                DB::raw("'' as injury_time"),
                DB::raw("'' as place"),
                'id.description as activity',
                DB::raw("'' as first_aid_person"),
                DB::raw("'' as signature"),
                DB::raw("'' as witnesses"),
                'id.action_taken as remarks',
            ])
            ->orderBy('id.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_11', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        return $this->buildResponse($rows);
    }
}
