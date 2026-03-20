<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XX';
    protected string $view = 'compliance.forms.form_xx';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $records = $rawData['records'] ?? [];
        
        if (is_object($records)) {
            $records = $records->toArray();
        }

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? '',
                'father_name' => $record['father_name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'damage_particulars' => $record['damage_particulars'] ?? '',
                'damage_date' => $record['damage_date'] ?? '',
                'showed_cause' => $record['showed_cause'] ?? false,
                'witness_name' => $record['witness_name'] ?? '',
                'deduction_amount' => (float)($record['deduction_amount'] ?? 0),
                'instalments' => $record['instalments'] ?? '',
                'first_month' => $record['first_month'] ?? '',
                'last_month' => $record['last_month'] ?? '',
                'remarks' => $record['remarks'] ?? '',
            ];
        }

        $totals = ['deduction_amount' => array_sum(array_column($rows, 'deduction_amount'))];

        return [
            'header' => [
                'form_title' => 'FORM XX - Register of Deductions for Damage or Loss',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'contractor_name' => $rawData['tenant']['name'] ?? '',
                'work_nature' => 'Manufacturing',
                'establishment_name' => $rawData['branch']['name'] ?? '',
                'principal_employer' => $rawData['tenant']['name'] ?? '',
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
