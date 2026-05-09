<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsUnpaidGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_UNPAID';
    protected string $view = 'compliance.forms.shops_unpaid';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'unpaid_amount' => round($record['unpaid_amount'] ?? 0, 2),
                'period_from' => $record['period_from'] ?? 'N/A',
                'period_to' => $record['period_to'] ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['unpaid_amount']);

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'Unpaid Wages Register',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'owner_name' => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
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
