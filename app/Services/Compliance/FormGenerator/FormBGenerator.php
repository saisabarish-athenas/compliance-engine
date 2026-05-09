<?php

namespace App\Services\Compliance\FormGenerator;

class FormBGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_B';
    protected string $view = 'compliance.forms.form_b';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];
        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? 'N/A',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'total_days_worked' => $record['total_days_worked'] ?? 0,
                'daily_rate' => round($record['daily_rate'] ?? 0, 2),
                'basic_earned' => round($record['basic_earned'] ?? 0, 2),
                'da_earned' => round($record['da_earned'] ?? 0, 2),
                'hra_earned' => round($record['hra_earned'] ?? 0, 2),
                'overtime_hours' => $record['overtime_hours'] ?? 0,
                'overtime_wages' => round($record['overtime_wages'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
                'pf_employee' => round($record['pf_employee'] ?? 0, 2),
                'esi_employee' => round($record['esi_employee'] ?? 0, 2),
                'advances' => round($record['advances'] ?? 0, 2),
                'fines' => round($record['fines'] ?? 0, 2),
                'total_deductions' => round($record['total_deductions'] ?? 0, 2),
                'net_salary' => round($record['net_salary'] ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'da_earned', 'hra_earned', 'overtime_wages',
            'gross_salary', 'pf_employee', 'esi_employee', 'total_deductions', 'net_salary'
        ]);

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'FORM B - Register of Wages',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant']['name'] ?? 'N/A',
                'owner_name' => $rawData['tenant']['owner_name'] ?? 'N/A',
                'wage_period' => 'Monthly',
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
            'entries' => $rows,
        ];
    }
}
