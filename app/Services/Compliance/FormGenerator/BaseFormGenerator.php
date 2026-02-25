<?php

namespace App\Services\Compliance\FormGenerator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Compliance\PayrollValidationGuard;
use App\Services\Compliance\ProductionValidationGuard;
use App\Services\Compliance\StrictDataValidator;

abstract class BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    protected array $config;

    public function __construct()
    {
        $this->config = config("compliance_forms.{$this->formCode}", []);
    }

    abstract protected function prepareData(array $rawData): array;

    public function generate(int $tenantId, int $branchId, int $month, int $year, int $batchId): string
    {
        // Validate context first
        \App\Services\Compliance\ComplianceContextValidator::validate($tenantId, $branchId, $month, $year);
        
        $guard = new ProductionValidationGuard();
        $guard->validateBeforeGeneration($tenantId, $branchId, $month, $year);
        
        $this->validateStatutorySettings($tenantId, $branchId);
        
        $validator = app(FormValidationService::class);
        $validation = $validator->validate($this->formCode, $tenantId, $branchId, $month, $year);
        
        if (!$validation['valid']) {
            Log::warning("Form validation failed for {$this->formCode}", [
                'errors' => $validation['errors'],
                'tenant_id' => $tenantId,
                'batch_id' => $batchId
            ]);
        }
        
        $aggregator = app(FormDataAggregator::class);
        $rawData = $aggregator->aggregate($this->formCode, $tenantId, $branchId, $month, $year);
        
        $data = $this->prepareData($rawData);
        
        // Strict validation - no N/A allowed
        $strictValidator = new StrictDataValidator();
        $strictValidator->validateFormData($this->formCode, $data);
        
        $this->validateTotals($data);
        
        if (in_array($this->formCode, ['FORM_B', 'FORM_XVI', 'SHOPS_FORM_12'])) {
            $payrollGuard = new PayrollValidationGuard();
            $payrollGuard->validateBeforeRender($data);
        }
        
        $memoryBefore = memory_get_usage(true) / 1024 / 1024;
        
        $pdf = Pdf::loadView($this->view, $data)
            ->setPaper('A4', 'portrait')
            ->setOption('isHtml5ParserEnabled', false)
            ->setOption('isRemoteEnabled', false)
            ->setOption('dpi', 72)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('chroot', [public_path()]);
        
        $memoryAfter = memory_get_usage(true) / 1024 / 1024;
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        if ($memoryUsed > 150) {
            throw new \RuntimeException(
                "Memory threshold exceeded: {$memoryUsed}MB > 150MB for form {$this->formCode}"
            );
        }
        
        $fileName = "{$this->formCode}_{$batchId}_" . time() . ".pdf";
        $filePath = "compliance/generated/{$batchId}/{$fileName}";
        
        Storage::disk('local')->put($filePath, $pdf->output());
        
        // Log generation
        Log::info("Form generated successfully", [
            'form_code' => $this->formCode,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'batch_id' => $batchId,
            'file_path' => $filePath
        ]);
        
        // Clear memory
        unset($pdf, $data, $rawData);
        
        return $filePath;
    }

    protected function validateStatutorySettings(int $tenantId, int $branchId): void
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        if (!$tenant) {
            throw new \RuntimeException("Tenant {$tenantId} not found");
        }

        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();
            
        if (!$branch) {
            throw new \RuntimeException("Branch {$branchId} not found or does not belong to tenant {$tenantId}");
        }

        if (empty($tenant->establishment_name) && empty($tenant->name)) {
            throw new \RuntimeException(
                "Statutory settings incomplete. Please configure establishment details in Settings before generating forms."
            );
        }

        if (empty($branch->unit_name) && empty($branch->branch_name)) {
            throw new \RuntimeException(
                "Branch details incomplete. Please configure branch/unit details in Settings before generating forms."
            );
        }

        if (empty($branch->address)) {
            throw new \RuntimeException(
                "Branch address missing. Please configure branch address in Settings before generating forms."
            );
        }
    }

    protected function formatPeriod(int $month, int $year): string
    {
        return \Carbon\Carbon::create($year, $month, 1)->format('F Y');
    }

    protected function calculateTotals(array $rows, array $fields): array
    {
        $totals = [];
        foreach ($fields as $field) {
            $totals[$field] = array_sum(array_column($rows, $field));
        }
        return $totals;
    }
    
    protected function validateTotals(array $data): void
    {
        if (isset($data['totals']) && isset($data['rows'])) {
            foreach ($data['totals'] as $field => $total) {
                $calculated = array_sum(array_column($data['rows'], $field));
                if (abs($calculated - $total) > 0.01) {
                    Log::error("Total mismatch for {$field} in {$this->formCode}", [
                        'expected' => $total,
                        'calculated' => $calculated
                    ]);
                }
            }
        }
    }
}
