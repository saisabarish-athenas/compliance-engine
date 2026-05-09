<?php

namespace App\Services\Compliance;

class PayrollValidationGuard
{
    public function validateBeforeRender(array $data): void
    {
        if (!isset($data['rows']) || empty($data['rows'])) {
            return;
        }

        foreach ($data['rows'] as $index => $row) {
            $employeeName = $row['employee_name'] ?? "Row {$index}";
            $daysWorked = $row['total_days_worked'] ?? 0;
            $basicWages = $row['basic_earned'] ?? 0;
            $da = $row['da_earned'] ?? 0;
            $hra = $row['hra_earned'] ?? 0;
            $overtimeHours = $row['overtime_hours'] ?? 0;
            $overtimeWages = $row['overtime_wages'] ?? 0;

            if ($daysWorked === 0) {
                if ($basicWages > 0 || $da > 0 || $hra > 0) {
                    throw new \Exception(
                        "LEGAL VIOLATION: {$employeeName} has daysWorked=0 but wage components exist. " .
                        "Basic={$basicWages}, DA={$da}, HRA={$hra}"
                    );
                }
            }

            if ($overtimeHours === 0 && $overtimeWages > 0) {
                throw new \Exception(
                    "LEGAL VIOLATION: {$employeeName} has overtimeHours=0 but overtimeWages={$overtimeWages}"
                );
            }

            if ($daysWorked > 0 && $basicWages === 0) {
                throw new \Exception(
                    "LEGAL VIOLATION: {$employeeName} has daysWorked={$daysWorked} but basicWages=0"
                );
            }
        }
    }
}
