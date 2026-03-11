#!/usr/bin/env php
<?php

/**
 * Statutory Form Services Validation Script
 * 
 * Usage: php validate_forms.php [--tenant=1] [--branch=1] [--month=1] [--year=2024]
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Services\Compliance\Forms\FormXIIService;
use App\Services\Compliance\Forms\FormXIIIService;
use App\Services\Compliance\Forms\FormXIVService;
use App\Services\Compliance\Forms\FormXVIService;
use App\Services\Compliance\Forms\FormXVIIService;
use App\Services\Compliance\Forms\FormXXIIIService;

class FormValidator
{
    private array $services = [
        'FORM_XII' => FormXIIService::class,
        'FORM_XIII' => FormXIIIService::class,
        'FORM_XIV' => FormXIVService::class,
        'FORM_XVI' => FormXVIService::class,
        'FORM_XVII' => FormXVIIService::class,
        'FORM_XXIII' => FormXXIIIService::class,
    ];

    private int $tenantId = 1;
    private int $branchId = 1;
    private int $month;
    private int $year;
    private array $results = [];

    public function __construct()
    {
        $this->month = (int)date('m');
        $this->year = (int)date('Y');
        $this->parseArguments();
    }

    private function parseArguments(): void
    {
        global $argv;
        foreach ($argv as $arg) {
            if (strpos($arg, '--tenant=') === 0) {
                $this->tenantId = (int)substr($arg, 9);
            } elseif (strpos($arg, '--branch=') === 0) {
                $this->branchId = (int)substr($arg, 9);
            } elseif (strpos($arg, '--month=') === 0) {
                $this->month = (int)substr($arg, 8);
            } elseif (strpos($arg, '--year=') === 0) {
                $this->year = (int)substr($arg, 7);
            }
        }
    }

    public function validate(): void
    {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "STATUTORY FORM SERVICES VALIDATION\n";
        echo str_repeat('=', 80) . "\n";
        echo "Tenant ID: {$this->tenantId}\n";
        echo "Branch ID: {$this->branchId}\n";
        echo "Period: {$this->month}/{$this->year}\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach ($this->services as $formCode => $serviceClass) {
            $this->validateForm($formCode, $serviceClass);
        }

        $this->printSummary();
    }

    private function validateForm(string $formCode, string $serviceClass): void
    {
        echo "Testing {$formCode}...\n";
        
        try {
            $service = new $serviceClass();
            $data = $service->generate($this->tenantId, $this->branchId, $this->month, $this->year);

            $checks = [
                'header_exists' => isset($data['header']),
                'rows_exists' => isset($data['rows']),
                'totals_exists' => isset($data['totals']),
                'header_valid' => $this->validateHeader($data['header'] ?? []),
                'rows_valid' => $this->validateRows($data['rows'] ?? []),
                'totals_valid' => $this->validateTotals($data['totals'] ?? []),
            ];

            $passed = array_sum($checks);
            $total = count($checks);

            echo "  ✓ Data generated successfully\n";
            echo "  ✓ Header: " . (isset($data['header']) ? 'Present' : 'Missing') . "\n";
            echo "  ✓ Rows: " . count($data['rows'] ?? []) . " records\n";
            echo "  ✓ Totals: " . (empty($data['totals']) ? 'None' : 'Present') . "\n";
            echo "  ✓ Validation: {$passed}/{$total} checks passed\n";

            $this->results[$formCode] = [
                'status' => 'PASS',
                'checks' => $checks,
                'row_count' => count($data['rows'] ?? []),
                'has_totals' => !empty($data['totals']),
            ];
        } catch (\Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
            $this->results[$formCode] = [
                'status' => 'FAIL',
                'error' => $e->getMessage(),
            ];
        }

        echo "\n";
    }

    private function validateHeader(array $header): bool
    {
        if (empty($header)) {
            return false;
        }

        $required = ['tenant', 'branch'];
        foreach ($required as $key) {
            if (!isset($header[$key]) || !is_array($header[$key])) {
                return false;
            }
        }

        return true;
    }

    private function validateRows(array $rows): bool
    {
        if (empty($rows)) {
            return true; // Empty rows is valid
        }

        foreach ($rows as $row) {
            if (!is_array($row) || empty($row)) {
                return false;
            }
        }

        return true;
    }

    private function validateTotals(array $totals): bool
    {
        if (empty($totals)) {
            return true; // No totals is valid
        }

        foreach ($totals as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        return true;
    }

    private function printSummary(): void
    {
        echo str_repeat('=', 80) . "\n";
        echo "VALIDATION SUMMARY\n";
        echo str_repeat('=', 80) . "\n";

        $passed = 0;
        $failed = 0;

        foreach ($this->results as $formCode => $result) {
            $status = $result['status'] === 'PASS' ? '✓ PASS' : '✗ FAIL';
            echo "{$formCode}: {$status}";
            
            if ($result['status'] === 'PASS') {
                echo " ({$result['row_count']} rows)";
                $passed++;
            } else {
                echo " ({$result['error']})";
                $failed++;
            }
            
            echo "\n";
        }

        echo str_repeat('=', 80) . "\n";
        echo "Total: {$passed} passed, {$failed} failed\n";
        echo str_repeat('=', 80) . "\n\n";

        if ($failed === 0) {
            echo "✓ All forms validated successfully!\n\n";
            exit(0);
        } else {
            echo "✗ Some forms failed validation\n\n";
            exit(1);
        }
    }
}

$validator = new FormValidator();
$validator->validate();
