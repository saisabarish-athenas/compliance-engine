<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXI';
    protected string $view = 'compliance.forms.form_xxi';

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
                'name' => $record['employee_name'] ?? 'N/A',
                'father_name' => $record['father_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'act_or_omission' => $record['act_or_omission'] ?? 'N/A',
                'date_of_offence' => $record['date_of_offence'] ?? 'N/A',
                'showed_cause' => $record['showed_cause'] ?? 'N/A',
                'heard_by' => $record['heard_by'] ?? 'N/A',
                'wage_period' => $record['wage_period'] ?? 'N/A',
                'fine_amount' => round($record['fine_amount'] ?? 0, 2),
                'fine_realised' => $record['fine_realised'] ?? 'N/A',
                'remarks' => $record['remarks'] ?? '',
            ];
        }

        return [
            'header' => [
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'work_nature' => 'Manufacturing',
                'establishment_name' => $branch['name'] ?? 'N/A',
                'principal_employer' => $tenant['name'] ?? 'N/A',
                'month_year' => $this->formatPeriod($month, $year),
                'tenant' => $tenant,
                'branch' => $branch,
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
