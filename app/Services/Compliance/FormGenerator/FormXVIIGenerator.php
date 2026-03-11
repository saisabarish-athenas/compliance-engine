<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVII';
    protected string $view = 'compliance.forms.form_xvii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'basic' => $record['basic_earned'] ?? 0,
                'da' => $record['da_earned'] ?? 0,
                'hra' => $record['hra_earned'] ?? 0,
                'pf_deduction' => round($record['pf_deduction'] ?? 0, 2),
                'esi_deduction' => round($record['esi_deduction'] ?? 0, 2),
                'advances' => round($record['advances'] ?? 0, 2),
                'fines' => round($record['fines'] ?? 0, 2),
                'total_deductions' => round($record['total_deductions'] ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'pf_deduction', 'esi_deduction', 'advances', 'fines', 'total_deductions'
        ]);

        return [
            'header' => [
                'form_title' => 'FORM XVII - Register of Deductions',
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
