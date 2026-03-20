<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIVGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIV';
    protected string $view = 'compliance.forms.form_xiv';

    protected function prepareData(array $rawData): array
    {
        $cards = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $serialNumber = 1;

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);

            $cards[] = [
                'contractor_name' => $record['contractor_name'] ?? 'NIL',
                'work_location' => $branch['address'] ?? 'NIL',
                'establishment_name' => $branch['name'] ?? 'NIL',
                'principal_employer' => is_array($tenant) ? ($tenant['name'] ?? 'NIL') : $tenant,
                'workman_name' => $record['employee_name'] ?? 'NIL',
                'register_serial' => $serialNumber,
                'designation' => $record['designation'] ?? $record['work_description'] ?? 'NIL',
                'wage_rate' => 'NIL',
                'wage_period' => 'Monthly',
                'tenure' => $record['date_of_joining'] ?? 'NIL',
                'remarks' => '',
            ];
            $serialNumber++;
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'FORM XIV - Employment Card (CLRA)',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
            ],
            'cards' => $cards,
            'is_nil' => count($cards) === 0,
        ];
    }
}
