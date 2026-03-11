<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXIII';
    protected string $view = 'compliance.forms.form_xxiii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'overtime_hours' => $record['overtime_hours'] ?? 0,
                'overtime_wages' => $record['overtime_wages'] ?? 0,
                'overtime_date' => $record['overtime_date'] ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, ['overtime_hours', 'overtime_wages']);

        return [
            'header' => [
                'form_title' => 'FORM XXIII - Register of Overtime',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
