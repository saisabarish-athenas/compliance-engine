<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'worker_name' => $record['worker_name'] ?? 'N/A',
                'contractor_name' => $record['contractor_name'] ?? 'N/A',
                'deployment_start' => $record['deployment_start'] ?? 'N/A',
                'wage_rate' => $record['wage_rate'] ?? 0,
                'work_order' => $record['work_order_number'] ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
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
