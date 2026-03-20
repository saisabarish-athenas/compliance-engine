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
                'rate_of_wage' => round($record['basic_earned'] ?? 0, 2),
                'total_days_worked' => $record['total_days_worked'] ?? 0,
                'overtime_hours' => round($record['overtime_hours'] ?? 0, 2),
                'basic_earned' => round($record['basic_earned'] ?? 0, 2),
                'special_allowance' => round($record['special_allowance'] ?? 0, 2),
                'da_earned' => round($record['da_earned'] ?? 0, 2),
                'overtime_wages' => round($record['overtime_wages'] ?? 0, 2),
                'hra_earned' => round($record['hra_earned'] ?? 0, 2),
                'other_earnings' => round($record['other_earnings'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
                'pf_employee' => round($record['pf_employee'] ?? 0, 2),
                'esi_employee' => round($record['esi_employee'] ?? 0, 2),
                'other_deductions' => round($record['other_deductions'] ?? 0, 2),
                'pt_deduction' => round($record['pt_deduction'] ?? 0, 2),
                'recovery' => round($record['recovery'] ?? 0, 2),
                'total_deductions' => round($record['total_deductions'] ?? 0, 2),
                'net_salary' => round($record['net_salary'] ?? 0, 2),
                'payment_date' => $record['payment_date'] ?? '',
                'remarks' => $record['remarks'] ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'special_allowance', 'da_earned', 'overtime_wages',
            'hra_earned', 'other_earnings', 'gross_salary', 'pf_employee',
            'esi_employee', 'other_deductions', 'pt_deduction', 'recovery',
            'total_deductions', 'net_salary'
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
