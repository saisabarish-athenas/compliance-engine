<?php

namespace App\Services\Compliance\FormGenerator;

class EPFInspectionGenerator extends BaseFormGenerator
{
    protected string $formCode = 'EPF_INSPECTION';
    protected string $view = 'compliance.forms.epf_inspection';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'inspection_date' => $record['inspection_date'] ?? 'N/A',
                'authority' => $record['inspecting_authority'] ?? 'N/A',
                'reference' => $record['reference_number'] ?? 'N/A',
                'remarks' => $record['remarks'] ?? 'N/A',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'EPF Inspection Register',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'pf_code' => $branch['pf_code'] ?? 'N/A',
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
