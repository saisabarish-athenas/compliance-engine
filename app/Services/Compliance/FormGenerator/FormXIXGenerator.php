<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIX';
    protected string $view = 'compliance.forms.form_xix';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'workman_name' => $record['name'] ?? '',
                'father_name' => $record['father_name'] ?? 'N/A',
                'work_nature' => $record['work_nature'] ?? 'N/A',
                'work_location' => $branch['address'] ?? 'N/A',
                'period_ending' => $this->formatPeriod($month, $year),
                'days_worked' => $record['days_worked'] ?? 0,
                'piece_units' => $record['piece_units'] ?? 0,
                'daily_rate' => round($record['basic_earned'] ?? 0, 2),
                'overtime_wages' => round($record['hra_earned'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
                'total_deductions' => round(($record['pf_employee'] ?? 0) + ($record['esi_employee'] ?? 0), 2),
                'net_salary' => round($record['net_salary'] ?? 0, 2),
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XIX - Wage Slip (CLRA)',
                'period' => $this->formatPeriod($month, $year),
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'tenant' => $tenant,
                'branch' => $branch,
            ],
            'rows' => $rows,
            'slips' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
