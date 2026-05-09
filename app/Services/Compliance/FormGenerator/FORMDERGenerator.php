<?php

namespace App\Services\Compliance\FormGenerator;

class FormDERGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_D_ER';
    protected string $view = 'compliance.forms.form_d_er';

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
                'category' => $record['category'] ?? $record['designation'] ?? '',
                'description' => $record['description'] ?? $record['designation'] ?? '',
                'men_count' => $record['men_count'] ?? 0,
                'women_count' => $record['women_count'] ?? 0,
                'rate_remuneration' => $record['rate_remuneration'] ?? 0,
                'basic_wage' => $record['basic_wage'] ?? 0,
                'da' => $record['da'] ?? 0,
                'hra' => $record['hra'] ?? 0,
                'other_allowance' => $record['other_allowance'] ?? 0,
                'cash_value' => $record['cash_value'] ?? 0,
            ];
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
                'form_title' => 'FORM D - Equal Remuneration Register',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'company_name' => $rawData['company_name'] ?? '',
                'contractor_name' => $rawData['contractor_name'] ?? '',
                'work_location' => $rawData['work_location'] ?? '',
                'principal_employer' => $rawData['principal_employer'] ?? '',
                'total_workers' => $rawData['total_workers'] ?? 0,
                'total_men' => $rawData['total_men'] ?? 0,
                'total_women' => $rawData['total_women'] ?? 0,
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
