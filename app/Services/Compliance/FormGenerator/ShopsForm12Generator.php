<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsForm12Generator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_12';
    protected string $view = 'compliance.forms.shops_form_12';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
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

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'SHOPS FORM 12 - Register of Wages',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'owner_name' => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
                'wage_period' => 'Monthly',
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
