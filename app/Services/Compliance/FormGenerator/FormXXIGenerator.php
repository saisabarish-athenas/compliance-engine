<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXI';
    protected string $view = 'compliance.forms.form_xxi';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? 'N/A',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'fine_amount' => round($record['fine_amount'] ?? 0, 2),
                'fine_date' => $record['fine_date'] ?? 'N/A',
                'reason' => $record['reason'] ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['fine_amount']);

        return [
            'header' => [
                'form_title' => 'FORM XXI - Register of Fines',
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
