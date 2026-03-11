<?php

namespace App\Services\Compliance\FormGenerator;

class Form11Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_11';
    protected string $view = 'compliance.forms.form_11';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'esi_number' => $record['esi_number'] ?? 'N/A',
                'incident_date' => $record['incident_date'] ?? null,
                'incident_type' => $record['incident_type'] ?? 'N/A',
                'location' => $record['location'] ?? 'N/A',
                'description' => $record['description'] ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM 11 - Accident Register',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
