<?php

namespace App\Services\Compliance\FormGenerator;

class EpfFormGenerator extends BaseFormGenerator
{
    protected string $formCode = 'EPF_INSPECTION';
    protected string $view = 'compliance.forms.epf_inspection';

    protected function prepareData(array $rawData): array
    {
        $aggregator = app(FormDataAggregator::class);
        
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'inspection_date' => $record->inspection_date ?? 'N/A',
                'authority' => $record->inspecting_authority ?? 'N/A',
                'reference' => $record->reference_number ?? 'N/A',
                'remarks' => $record->remarks ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'EPF Inspection Register',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $aggregator->getBranchDetails($rawData['branch_id']),
                'tenant' => $aggregator->getTenantDetails($rawData['tenant_id']),
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
