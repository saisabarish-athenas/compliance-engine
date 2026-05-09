<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIVGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIV';
    protected string $view = 'compliance.forms.form_xiv';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'worker_name' => $record['worker_name'] ?? 'N/A',
                'contractor_name' => $record['contractor_name'] ?? 'N/A',
                'deployment_start' => $record['deployment_start'] ?? 'N/A',
                'wage_rate' => $record['wage_rate'] ?? 0,
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM XIV - Employment Card (CLRA)',
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
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
