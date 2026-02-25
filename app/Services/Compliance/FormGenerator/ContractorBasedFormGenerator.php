<?php

namespace App\Services\Compliance\FormGenerator;

class ContractorBasedFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    
    private array $formTitles = [
        'FORM_XIII' => 'FORM XIII - Register of Contract Labour',
        'FORM_XIV' => 'FORM XIV - Register of Workmen',
        'FORM_XII' => 'FORM XII - Register of Contractors',
        'CLRA_LICENSE' => 'License Register',
        'FORM_XXIV' => 'FORM XXIV - Annual Return',
        'FORM_XXV' => 'FORM XXV - Half-Yearly Return',
        'SHOPS_FORM_1' => 'SHOPS FORM 1 - Register of Employment',
    ];

    public function __construct(string $formCode)
    {
        $this->formCode = $formCode;
        $this->view = 'compliance.forms.' . strtolower($formCode);
        parent::__construct();
    }

    protected function prepareData(array $rawData): array
    {
        $aggregator = app(FormDataAggregator::class);
        
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'worker_name' => $record->worker_name ?? 'N/A',
                'contractor_name' => $record->contractor_name ?? 'N/A',
                'deployment_start' => $record->deployment_start ?? null,
                'deployment_end' => $record->deployment_end ?? null,
                'wage_rate' => $record->wage_rate ?? 0,
                'work_order' => $record->work_order ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['wage_rate']);

        return [
            'header' => [
                'form_title' => $this->formTitles[$this->formCode] ?? $this->formCode,
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $aggregator->getBranchDetails($rawData['branch_id'], $rawData['tenant_id']),
                'tenant' => $aggregator->getTenantDetails($rawData['tenant_id']),
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
