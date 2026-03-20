<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXII';
    protected string $view = 'compliance.forms.form_xxii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'name' => $record['employee_name'] ?? 'N/A',
                'father_name' => $record['father_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'advance_date_amount_1' => ($record['advance_date'] ?? 'N/A') . ' - ' . ($record['advance_amount'] ?? 0),
                'advance_date_amount_2' => '',
                'purpose' => $record['purpose'] ?? 'N/A',
                'installments' => $record['installments'] ?? 1,
                'installment_repaid' => $record['installment_repaid'] ?? 'N/A',
                'last_installment_date' => $record['last_installment_date'] ?? 'N/A',
                'signature' => '',
            ];
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'contractor_name' => $tenant['name'] ?? 'N/A',
                'work_nature' => 'Manufacturing',
                'establishment_name' => $branch['name'] ?? 'N/A',
                'principal_employer' => $tenant['name'] ?? 'N/A',
                'month_year' => $this->formatPeriod($month, $year),
                'tenant' => $tenant,
                'branch' => $branch,
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
