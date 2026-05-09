<?php

namespace App\Services\Compliance\FormGenerator;

class FormAGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_A';
    protected string $view = 'compliance.forms.form_a';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $records = $rawData['records'] ?? [];
        
        if (is_object($records)) {
            $records = $records->toArray();
        }

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = $record;
        }

        $totals = [];
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $totals[$key] = ($totals[$key] ?? 0) + $value;
                }
            }
        }

        return [
            'header' => [
                'form_title' => $rawData['form_title'] ?? 'FORM A - Register of Employees',
                'period' => $rawData['period'] ?? $this->formatPeriod($rawData['period_month'] ?? 1, $rawData['period_year'] ?? date('Y')),
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
