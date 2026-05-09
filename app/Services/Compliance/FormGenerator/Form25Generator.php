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
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'total_days_worked' => $record['total_days_worked'] ?? 0,
                'basic_earned' => round($record['basic_earned'] ?? 0, 2),
                'da_earned' => round($record['da_earned'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, ['basic_earned', 'da_earned', 'gross_salary']);

        return [
            'header' => [
                'form_title' => 'FORM 25 - Muster Roll',
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
