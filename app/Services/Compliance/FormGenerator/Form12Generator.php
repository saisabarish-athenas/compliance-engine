<?php

namespace App\Services\Compliance\FormGenerator;

class Form12Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_12';
    protected string $view = 'compliance.forms.form_12';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? 'N/A',
                'name' => $record['name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'date_of_joining' => $record['date_of_joining'] ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM 12 - Register of Adult Workers',
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
