<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XX';
    protected string $view = 'compliance.forms.form_xx';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $records = $rawData['records'] ?? [];
        
        if (is_object($records)) {
            $records = $records->toArray();
        }

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'workmen_name' => $record['workmen_name'] ?? $record['name'] ?? '',
                'father_husband_name' => $record['father_husband_name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'act_omission' => $record['act_omission'] ?? '',
                'date_of_offence' => $record['date_of_offence'] ?? '',
                'showed_cause' => $record['showed_cause'] ?? '',
                'person_present' => $record['person_present'] ?? '',
                'wage_period' => $record['wage_period'] ?? '',
                'amount_fine' => (float)($record['amount_fine'] ?? 0),
                'date_realised' => $record['date_realised'] ?? '',
                'remarks' => $record['remarks'] ?? '',
            ];
        }

        $totals = ['amount_fine' => array_sum(array_column($rows, 'amount_fine'))];

        return [
            'header' => [
                'form_title' => 'FORM XX - Register of Fines',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'contractor_name' => $rawData['contractor_name'] ?? '',
                'nature_of_work' => $rawData['nature_of_work'] ?? '',
                'establishment_name' => $rawData['establishment_name'] ?? '',
                'principal_employer' => $rawData['principal_employer'] ?? '',
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
