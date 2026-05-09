<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXII';
    protected string $view     = 'compliance.forms.form_xxii';

    private function nil($value): string
    {
        $v = trim((string) ($value ?? ''));
        return ($v !== '' && $v !== '0') ? $v : 'NIL';
    }

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record        = $this->normalizeRecord($record);
            $advanceAmount = (float) ($record['advance_amount'] ?? 0);
            $hasAdvance    = $advanceAmount > 0;

            $rows[] = [
                'name'                  => $this->nil($record['employee_name'] ?? $record['name'] ?? null),
                'father_name'           => $this->nil($record['father_name']      ?? null),
                'designation'           => $this->nil($record['designation']      ?? null),
                'advance_date_amount_1' => $hasAdvance
                    ? $this->nil($record['advance_date'] ?? null) . ' / ₹' . number_format($advanceAmount, 2)
                    : 'NIL',
                'advance_date_amount_2' => 'NIL',
                'purpose'               => 'NIL',
                'installments'          => 'NIL',
                'installment_repaid'    => 'NIL',
                'last_installment_date' => 'NIL',
            ];
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? 2024;

        $allNil = !empty($rows) && collect($rows)->every(fn($r) => $r['advance_date_amount_1'] === 'NIL');

        return [
            'header' => [
                'contractor_name'    => $tenant['name'] ?? 'N/A',
                'work_nature'        => $branch['address'] ?? 'N/A',
                'establishment_name' => $branch['name']   ?? 'N/A',
                'principal_employer' => $tenant['name']   ?? 'N/A',
                'month_year'         => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'             => $rows,
            'is_nil'           => empty($rows),
            'all_nil_advances' => $allNil,
        ];
    }
}
