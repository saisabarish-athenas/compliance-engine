<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XII';
    protected string $view = 'compliance.forms.form_xii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'contractor_name' => $record['contractor_name'] ?? 'N/A',
                'license_number' => $record['license_number'] ?? 'N/A',
                'registration_date' => $record['registration_date'] ?? 'N/A',
                'address' => $record['address'] ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XII - Register of Contractors',
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
