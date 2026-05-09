<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XX';
    protected string $view = 'compliance.forms.form_xx';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];

        if (is_object($records)) {
            $records = $records->toArray();
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($records as $i => $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name'      => $record['employee_name'] ?? '',
                'father_name'        => 'Nil',
                'designation'        => 'Nil',
                'damage_particulars' => 'Nil',
                'damage_date'        => 'Nil',
                'showed_cause'       => 'Nil',
                'witness_name'       => 'Nil',
                'deduction_amount'   => 'Nil',
                'instalments'        => 'Nil',
                'first_month'        => 'Nil',
                'last_month'         => 'Nil',
                'remarks'            => '-',
            ];
        }

        $header = [
            'form_title'         => 'FORM XX - Register of Deductions for Damage or Loss',
            'period'             => $this->formatPeriod($month, $year),
            'contractor_name'    => $tenant['name'] ?? '',
            'work_nature'        => $branch['address'] ?? $branch['name'] ?? '',
            'establishment_name' => $branch['name'] ?? '',
            'principal_employer' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
        ];

        return [
            'header'  => $header,
            'rows'    => $rows,
            'totals'  => ['deduction_amount' => array_sum(array_column($rows, 'deduction_amount'))],
            'is_nil'  => true,
        ];
    }
}
