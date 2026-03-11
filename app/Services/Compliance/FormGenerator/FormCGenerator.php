<?php

namespace App\Services\Compliance\FormGenerator;

class FormCGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_C';
    protected string $view = 'compliance.forms.form_c';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? 'N/A',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'bonus_amount' => round($record['bonus_amount'] ?? 0, 2),
                'bonus_date' => $record['bonus_date'] ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['bonus_amount']);

        return [
            'header' => [
                'form_title' => 'FORM C - Bonus Register',
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
