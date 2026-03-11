<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXII';
    protected string $view = 'compliance.forms.form_xxii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? 'N/A',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'damage_amount' => round($record['damage_amount'] ?? 0, 2),
                'damage_date' => $record['damage_date'] ?? 'N/A',
                'description' => $record['description'] ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['damage_amount']);

        return [
            'header' => [
                'form_title' => 'FORM XXII - Register of Damage or Loss',
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
