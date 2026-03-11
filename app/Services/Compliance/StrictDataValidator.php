<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;

class StrictDataValidator
{
    public function validateFormData(string $formCode, array $data): void
    {
        // ✅ If NIL form, skip strict validation
        if (!empty($data['is_nil']) && $data['is_nil'] === true) {
            logger()->info("{$formCode} generated as NIL — skipping strict validation.");
            return;
        }

        if (empty($data['rows'])) {
            return;
        }

        foreach ($data['rows'] as $index => $row) {
            $this->validateRow($formCode, $row, $index);
        }

        $this->validateHeader($formCode, $data['header']);
    }

    private function validateRow(string $formCode, array $row, int $index): void
    {
        $requiredFields = $this->getRequiredFieldsForForm($formCode);

        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || empty($row[$field])) {
                throw new \RuntimeException(
                    "Missing required field '{$field}' in {$formCode} row " . ($index + 1)
                );
            }

            if ($row[$field] === 'N/A') {
                throw new \RuntimeException(
                    "N/A placeholder found in '{$field}' for {$formCode} row " . ($index + 1)
                );
            }
        }
    }

    private function validateHeader(string $formCode, array $header): void
    {
        // Validate tenant details
        $tenantName = null;
        if (is_array($header['tenant'] ?? null)) {
            $tenantName = $header['tenant']['name'] ?? null;
        } else {
            $tenantName = $header['tenant'] ?? null;
        }
        
        if (empty($tenantName)) {
            throw new \RuntimeException("{$formCode}: Missing tenant establishment name");
        }

        // Validate branch details
        if (empty($header['branch']['name'] ?? null)) {
            throw new \RuntimeException("{$formCode}: Missing branch unit name");
        }

        if (empty($header['branch']['address'] ?? null)) {
            throw new \RuntimeException("{$formCode}: Missing branch address");
        }

        // Validate rule reference exists
        $ruleConfig = config("tn_statutory_rules.{$formCode}");
        if (!$ruleConfig) {
            logger()->warning("{$formCode}: Rule config missing — generating NIL form.");
        }
    }

    private function getRequiredFieldsForForm(string $formCode): array
    {
        $employeeBasedForms = [
            'FORM_10',
            'FORM_B',
            'FORM_25',
            'FORM_XVI',
            'FORM_XVII',
            'FORM_XIX',
            'FORM_XXIII',
            'SHOPS_FORM_12',
        ];

        if (in_array($formCode, $employeeBasedForms)) {
            return ['employee_code', 'employee_name', 'designation'];
        }

        return [];
    }

    public function validateTenantSetup(int $tenantId): array
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        if (!$tenant) {
            return ['valid' => false, 'errors' => ['Tenant not found']];
        }

        $errors = [];

        $name = $tenant->establishment_name ?? $tenant->name;
        if (empty($name)) {
            $errors[] = 'Missing establishment name';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    public function validateBranchSetup(int $branchId): array
    {
        $branch = DB::table('branches')->where('id', $branchId)->first();

        if (!$branch) {
            return ['valid' => false, 'errors' => ['Branch not found']];
        }

        $errors = [];

        $name = $branch->unit_name ?? $branch->branch_name;
        if (empty($name)) {
            $errors[] = 'Missing unit name';
        }

        if (empty($branch->address)) {
            $errors[] = 'Missing address';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
}
