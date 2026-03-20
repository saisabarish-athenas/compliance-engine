<?php

namespace App\Services\Compliance\FormGenerator;

class Form25Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_25';
    protected string $view = 'compliance.forms.form_25';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name' => $record['name'] ?? '',
                'father_name' => $record['father_name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'date_of_birth' => $record['date_of_birth'] ?? '',
                'place_of_employment' => $rawData['branch']['address'] ?? '',
                'group' => '',
                'relay' => '',
                'periods_of_work' => '',
                'date' => $record['attendance_date'] ?? '',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM 25 - Muster Roll',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
