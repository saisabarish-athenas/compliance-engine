<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIX';
    protected string $view = 'compliance.forms.form_xix';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'total_days_worked' => $record['total_days_worked'] ?? 0,
                'basic_earned' => round($record['basic_earned'] ?? 0, 2),
                'gross_salary' => round($record['gross_salary'] ?? 0, 2),
            ];
        }

        $totals = $this->calculateTotals($rows, ['basic_earned', 'gross_salary']);

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM XIX - Muster Roll (CLRA)',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'principal_employer' => $branch['name'] ?? 'N/A',
                'factory_name' => $branch['name'] ?? 'N/A',
                'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
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
