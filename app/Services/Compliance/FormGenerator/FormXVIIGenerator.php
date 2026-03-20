<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVII';
    protected string $view = 'compliance.forms.form_xvii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'name' => $record['name'] ?? '',
                'employee_code' => $record['employee_code'] ?? '',
                'designation' => $record['designation'] ?? '',
                'days_worked' => $record['days_worked'] ?? 0,
                'unit_work' => '',
                'daily_rate' => $record['daily_rate'] ?? 0,
                'basic_wages' => $record['basic_earned'] ?? 0,
                'da' => $record['da_earned'] ?? 0,
                'overtime' => 0,
                'other_cash' => $record['hra_earned'] ?? 0,
                'gross_salary' => $record['gross_salary'] ?? 0,
                'esi' => $record['esi_employee'] ?? 0,
                'pf' => $record['pf_employee'] ?? 0,
                'pt' => 0,
                'total_deductions' => ($record['pf_employee'] ?? 0) + ($record['esi_employee'] ?? 0),
                'net_amount' => $record['net_salary'] ?? 0,
            ];
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'establishment_name' => $branch['name'] ?? 'N/A',
                'principal_employer' => $tenant['name'] ?? 'N/A',
                'work_nature' => 'Manufacturing',
                'work_location' => $branch['address'] ?? 'N/A',
                'wage_period' => $this->formatPeriod($month, $year),
                'tenant' => $tenant,
                'branch' => $branch,
            ],
            'rows' => $rows,
            'entries' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
