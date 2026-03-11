<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVI';
    protected string $view = 'compliance.forms.form_xvi';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'attendance_date' => $record['attendance_date'] ?? '',
                'status' => $record['status'] ?? '',
                'basic_earned' => round($record['basic_earned'] ?? 0, 2),
                'da_earned' => round($record['da_earned'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
                'pf_employee' => round($record['pf_employee'] ?? 0, 2),
                'esi_employee' => round($record['esi_employee'] ?? 0, 2),
                'total_deductions' => round($record['total_deductions'] ?? 0, 2),
                'net_salary' => round($record['net_salary'] ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'da_earned', 'gross_salary', 'pf_employee',
            'esi_employee', 'total_deductions', 'net_salary'
        ]);

        return [
            'header' => [
                'form_title' => 'FORM XVI - Register of Wages (CLRA)',
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
