<?php

namespace App\Services\Compliance\FormGenerator;

class FormDERGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_D_ER';
    protected string $view = 'compliance.forms.form_d_er';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        
        if (is_object($records)) {
            $records = $records->toArray();
        }

        // Group by designation
        $grouped = [];
        foreach ($records as $record) {
            $designation = $record['designation'] ?? 'Unknown';
            if (!isset($grouped[$designation])) {
                $grouped[$designation] = [];
            }
            $grouped[$designation][] = $record;
        }

        // Build rows from grouped data
        $rows = [];
        $totalMen = 0;
        $totalWomen = 0;

        foreach ($grouped as $designation => $employees) {
            $menCount = 0;
            $womenCount = 0;
            $totalGrossSalary = 0;
            $totalBasicEarned = 0;
            $totalDaEarned = 0;
            $count = count($employees);

            foreach ($employees as $emp) {
                $gender = strtolower($emp['gender'] ?? '');
                if (in_array($gender, ['male', 'm'])) {
                    $menCount++;
                } elseif (in_array($gender, ['female', 'f'])) {
                    $womenCount++;
                }
                $totalGrossSalary += (float)($emp['gross_salary'] ?? 0);
                $totalBasicEarned += (float)($emp['basic_earned'] ?? 0);
                $totalDaEarned += (float)($emp['da_earned'] ?? 0);
            }

            $avgGrossSalary = $count > 0 ? $totalGrossSalary / $count : 0;
            $avgBasicEarned = $count > 0 ? $totalBasicEarned / $count : 0;
            $avgDaEarned = $count > 0 ? $totalDaEarned / $count : 0;
            $avgOtherAllowance = $avgGrossSalary - $avgBasicEarned - $avgDaEarned;

            $rows[] = [
                'category' => $designation,
                'description' => $designation,
                'men_count' => $menCount,
                'women_count' => $womenCount,
                'rate_remuneration' => round($avgGrossSalary, 2),
                'basic_wage' => round($avgBasicEarned, 2),
                'da' => round($avgDaEarned, 2),
                'hra' => 0,
                'other_allowance' => round($avgOtherAllowance, 2),
                'cash_value' => 0,
            ];

            $totalMen += $menCount;
            $totalWomen += $womenCount;
        }

        // Calculate totals
        $totals = [];
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $totals[$key] = ($totals[$key] ?? 0) + $value;
                }
            }
        }

        return [
            'header' => [
                'form_title' => 'FORM D - Equal Remuneration Register',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'company_name' => $rawData['company_name'] ?? '',
                'contractor_name' => $rawData['contractor_name'] ?? '',
                'work_location' => $rawData['work_location'] ?? '',
                'principal_employer' => $rawData['principal_employer'] ?? '',
                'total_workers' => $totalMen + $totalWomen,
                'total_men' => $totalMen,
                'total_women' => $totalWomen,
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
