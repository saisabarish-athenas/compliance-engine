<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class FormDGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_D';
    protected string $view = 'compliance.forms.form_d';

    protected function prepareData(array $rawData): array
    {
        try {
            Log::info("FormDGenerator: Starting prepareData", ['records_count' => count($rawData['records'] ?? [])]);

            $employees = $this->groupByEmployee($rawData['records'] ?? []);
            Log::info("FormDGenerator: Grouped employees", ['employee_count' => count($employees)]);

            $rows = [];
            $totals = [
                'total_present' => 0,
                'paid_holidays' => 0,
                'paid_leave' => 0,
                'weekly_off' => 0,
                'absent_days' => 0,
                'total_days' => 0,
            ];

            foreach ($employees as $employeeCode => $records) {
                try {
                    $row = $this->buildEmployeeRow($records);
                    $rows[] = $row;

                    $totals['total_present'] += $row['total_present'];
                    $totals['paid_holidays'] += $row['paid_holidays'];
                    $totals['paid_leave'] += $row['paid_leave'];
                    $totals['weekly_off'] += $row['weekly_off'];
                    $totals['absent_days'] += $row['absent_days'];
                    $totals['total_days'] += $row['total_days'];
                } catch (\Exception $e) {
                    Log::error("FormDGenerator: Error building row for employee {$employeeCode}", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            Log::info("FormDGenerator: Built rows", ['row_count' => count($rows)]);

            $result = [
                'header' => [
                    'establishment_name' => $rawData['tenant']['name'] ?? '',
                    'owner_name' => $rawData['tenant']['owner_name'] ?? '',
                    'month_name' => $this->getMonthName($rawData['meta']['month'] ?? 1),
                    'year' => $rawData['meta']['year'] ?? 2024,
                    'tenant' => $rawData['tenant'] ?? [],
                    'branch' => $rawData['branch'] ?? [],
                ],
                'rows' => $rows,
                'totals' => $totals,
                'is_nil' => count($rows) === 0,
            ];

            Log::info("FormDGenerator: prepareData complete", ['is_nil' => $result['is_nil']]);
            return $result;
        } catch (\Exception $e) {
            Log::error("FormDGenerator: prepareData failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function groupByEmployee(array $records): array
    {
        $grouped = [];
        foreach ($records as $record) {
            $code = $record['employee_code'] ?? '';
            if (!isset($grouped[$code])) {
                $grouped[$code] = [];
            }
            $grouped[$code][] = $record;
        }
        return $grouped;
    }

    private function buildEmployeeRow(array $records): array
    {
        $row = [
            'employee_name' => $records[0]['name'] ?? '',
            'designation' => '',
            'remarks' => '',
        ];

        for ($day = 1; $day <= 31; $day++) {
            $row["day_{$day}"] = '';
        }

        $counts = [
            'present' => 0,
            'holiday' => 0,
            'leave' => 0,
            'weekly_off' => 0,
            'absent' => 0,
        ];

        foreach ($records as $record) {
            $date = $record['attendance_date'] ?? '';
            $status = strtolower($record['status'] ?? '');

            if ($date) {
                try {
                    $day = (int)date('d', strtotime($date));
                    $row["day_{$day}"] = $this->formatStatus($status);
                } catch (\Exception $e) {
                    Log::warning("FormDGenerator: Error parsing date {$date}", ['error' => $e->getMessage()]);
                }
            }

            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        $row['total_present'] = $counts['present'];
        $row['paid_holidays'] = $counts['holiday'];
        $row['paid_leave'] = $counts['leave'];
        $row['weekly_off'] = $counts['weekly_off'];
        $row['absent_days'] = $counts['absent'];
        $row['total_days'] = $counts['present'] + $counts['leave'] + $counts['weekly_off'] + $counts['holiday'];

        return $row;
    }

    private function formatStatus(string $status): string
    {
        return match(strtolower($status)) {
            'present' => 'P',
            'absent' => 'A',
            'leave' => 'PL',
            'holiday' => 'PH',
            default => ''
        };
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        return $months[$month] ?? '';
    }
}
