<?php

namespace App\Services\Compliance\FormGenerator;

class Form10Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_10';
    protected string $view = 'compliance.forms.form_10';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'normal_rate' => round($record['normal_rate'] ?? 0, 2),
                'overtime_rate' => round($record['overtime_rate'] ?? 0, 2),
                'normal_earnings' => round($record['normal_earnings'] ?? 0, 2),
                'overtime_hours' => $record['overtime_hours'] ?? 0,
                'overtime_wages' => round($record['overtime_wages'] ?? 0, 2),
                'food_grain_benefit' => round($record['food_grain_benefit'] ?? 0, 2),
                'is_piece_worker' => $record['is_piece_worker'] ?? false,
                'piece_worker_overtime' => $record['piece_worker_overtime'] ?? 0,
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'normal_rate', 'overtime_rate', 'normal_earnings', 'overtime_hours',
            'overtime_wages', 'food_grain_benefit', 'piece_worker_overtime'
        ]);

        return [
            'header' => [
                'form_title' => 'FORM 10 - Overtime Register',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'total_workers' => count($rows),
                'contractor_name' => $rawData['contractor_name'] ?? 'N/A',
                'principal_employer' => $rawData['principal_employer'] ?? $rawData['tenant']['name'] ?? 'N/A',
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
