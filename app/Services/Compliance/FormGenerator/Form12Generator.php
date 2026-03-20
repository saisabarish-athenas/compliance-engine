<?php

namespace App\Services\Compliance\FormGenerator;

class Form12Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_12';
    protected string $view = 'compliance.forms.form_12';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            
            $name = trim($record['name'] ?? '');
            $address = trim($record['address'] ?? '');
            $employeeName = $address ? "{$name}, {$address}" : $name;
            
            $rows[] = [
                'employee_name' => $employeeName,
                'father_name' => trim($record['father_name'] ?? ''),
                'designation' => trim($record['designation'] ?? ''),
                'group' => '',
                'relay' => '',
                'certificate_no' => '',
                'token_no' => '',
                'remarks' => '',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'FORM 12 - Register of Adult Workers',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => empty($rows),
        ];
    }
}
