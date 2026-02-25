<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;

class ComplianceExecutionService
{
    public function __construct(
        private ComplianceEngine $engine,
        private ComplianceTimelineService $timelineService
    ) {}

    public function createBatch(int $tenantId, int $sectionId, string $periodFrom, string $periodTo, array $formIds, ?int $branchId = null): ComplianceExecutionBatch
    {
        return ComplianceExecutionBatch::create([
            'tenant_id' => $tenantId,
            'section_id' => $sectionId,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'form_ids' => $formIds,
            'branch_id' => $branchId,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        
        $tenant = \App\Models\Tenant::findOrFail($batch->tenant_id);
        if ($tenant->subscription_type === 'MINIMAL') {
            throw new \Exception("Automation is not allowed under MINIMAL subscription.");
        }
        
        $batch->update(['status' => 'processing']);

        $results = [];
        $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
        
        foreach ($batch->form_ids as $formId) {
            try {
                $form = ComplianceFormsMaster::findOrFail($formId);
                $generator = $factory::make($form->form_code);
                
                if (!$generator) {
                    $results[$formId] = [
                        'success' => false,
                        'form_code' => $form->form_code,
                        'error' => 'No generator available for this form'
                    ];
                    continue;
                }
                
                $filePath = $generator->generate(
                    $batch->tenant_id,
                    $batch->branch_id ?? 1,
                    $batch->period_month ?? date('n'),
                    $batch->period_year ?? date('Y'),
                    $batch->id
                );
                
                // Log to compliance_generation_logs
                $checksum = '';
                $fullPath = storage_path('app/' . $filePath);
                if (file_exists($fullPath)) {
                    $checksum = hash_file('sha256', $fullPath);
                }
                
                $logData = [
                    'tenant_id' => $batch->tenant_id,
                    'batch_id' => $batch->id,
                    'form_id' => $formId,
                    'compliance_status_id' => null,
                    'generated_by' => auth()->id() ?? 1,
                    'file_path' => $filePath,
                    'checksum_hash' => $checksum,
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'CLI',
                    'form_code' => $form->form_code,
                    'status' => 'success',
                    'generated_file_path' => $filePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add source only if column exists
                if (\Schema::hasColumn('compliance_generation_logs', 'source')) {
                    $logData['source'] = 'Automated';
                }
                
                \DB::table('compliance_generation_logs')->insert($logData);
                
                $results[$formId] = [
                    'success' => true,
                    'form_code' => $form->form_code,
                    'file_path' => $filePath,
                    'status' => 'Generated'
                ];

                // Mark timeline as Generated
                if ($batch->period_month && $batch->period_year) {
                    $this->timelineService->markAsGenerated(
                        $batch->tenant_id,
                        $formId,
                        $batch->period_month,
                        $batch->period_year
                    );
                }
            } catch (\Exception $e) {
                // Log error to compliance_generation_logs
                $errorData = [
                    'tenant_id' => $batch->tenant_id,
                    'batch_id' => $batch->id,
                    'form_id' => $formId,
                    'compliance_status_id' => null,
                    'generated_by' => auth()->id() ?? 1,
                    'file_path' => '',
                    'checksum_hash' => '',
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'CLI',
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'status' => 'failed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add source only if column exists
                if (\Schema::hasColumn('compliance_generation_logs', 'source')) {
                    $errorData['source'] = 'Automated';
                }
                
                // Add error_message only if column exists
                if (\Schema::hasColumn('compliance_generation_logs', 'error_message')) {
                    $errorData['error_message'] = $e->getMessage();
                }
                
                \DB::table('compliance_generation_logs')->insert($errorData);
                
                $results[$formId] = [
                    'success' => false,
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Determine final batch status
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);
        
        if ($successCount === $totalCount) {
            $finalStatus = 'completed';
        } elseif ($successCount > 0) {
            $finalStatus = 'partially_completed';
        } else {
            $finalStatus = 'failed';
        }

        $batch->update([
            'status' => $finalStatus,
            'processed_at' => now(),
            'results' => $results,
        ]);

        return $results;
    }
}
