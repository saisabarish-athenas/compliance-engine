<?php

namespace App\Services\Compliance\FormGenerator;

class Form26Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_26';
    protected string $view = 'compliance.forms.form_26';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'sl_no' => count($rows) + 1,
                'incident_date' => $record['incident_date'] ?? null,
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'location' => $record['location'] ?? 'N/A',
                'description' => $record['description'] ?? 'N/A',
                'nature_of_injury' => $record['nature_of_injury'] ?? 'N/A',
                'severity' => $record['severity'] ?? 'N/A',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM 26 - Register of Accidents',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'registration_number' => $branch['registration_number'] ?? 'N/A',
                'calendar_year' => $year,
                'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
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
