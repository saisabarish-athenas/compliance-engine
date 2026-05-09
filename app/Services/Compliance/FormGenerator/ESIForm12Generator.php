<?php

namespace App\Services\Compliance\FormGenerator;

class ESIForm12Generator extends BaseFormGenerator
{
    protected string $formCode = 'ESI_FORM_12';
    protected string $view = 'compliance.forms.esi_form_12';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'incident_date' => $record['incident_date'] ?? 'N/A',
                'incident_type' => $record['incident_type'] ?? 'N/A',
                'location' => $record['location'] ?? 'N/A',
                'description' => $record['description'] ?? 'N/A',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'ESI FORM 12 - Accident Report',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'esi_code' => $branch['esi_code'] ?? 'N/A',
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
