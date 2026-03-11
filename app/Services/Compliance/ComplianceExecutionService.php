<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceAuditLog;
use App\Services\Compliance\Audit\ComplianceAuditService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComplianceExecutionService
{
    public function __construct(
        private ComplianceEngine $engine,
        private ComplianceTimelineService $timelineService,
        private ComplianceAuditService $auditService,
        private \App\Compliance\ComplianceDataService $dataService,
        private ComplianceOrchestrator $orchestrator
    ) {}

    public function getFormDataViaAPI(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $serviceMap = [
            'FORM_10' => \App\Services\Compliance\Forms\Form10Service::class,
            'FORM_12' => \App\Services\Compliance\Forms\Form12Service::class,
            'FORM_17' => \App\Services\Compliance\Forms\Form17Service::class,
            'FORM_25' => \App\Services\Compliance\Forms\Form25Service::class,
            'FORM_B' => \App\Services\Compliance\Forms\FormBService::class,
            'FORM_26' => \App\Services\Compliance\Forms\Form26Service::class,
            'FORM_26A' => \App\Services\Compliance\Forms\Form26AService::class,
            'FORM_XII' => \App\Services\Compliance\Forms\FormXIIService::class,
            'FORM_XIII' => \App\Services\Compliance\Forms\FormXIIIService::class,
            'HAZARD_REGISTER' => \App\Services\Compliance\Forms\HazardRegisterService::class,
        ];

        $serviceClass = $serviceMap[$formCode] ?? null;
        if (!$serviceClass) {
            return ['status' => 'NIL', 'error' => 'Form service not found'];
        }

        $service = new $serviceClass();
        return $service->generate($tenantId, $branchId, $month, $year);
    }

    public function createBatch(int $tenantId, int $sectionId, string $periodFrom, string $periodTo, array $formIds, ?int $branchId = null): ComplianceExecutionBatch
    {
        $user = Auth::user();

        return ComplianceExecutionBatch::create([
            'tenant_id' => $tenantId,
            'section_id' => $sectionId,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'form_ids' => $formIds,
            'branch_id' => $branchId,
            'status' => 'pending',
            'created_by' => $user?->id ?? 1,
        ]);
    }

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::with('section')->findOrFail($batchId);
        $tenantId = $batch->tenant_id;
        $tenant = \App\Models\Tenant::findOrFail($tenantId);
        $subscription = strtoupper(trim($tenant->subscription_type ?? ''));
        $isFull = $subscription === 'FULL';
        $isMinimal = $subscription === 'MINIMAL';

        $authUser = Auth::user();
        $generatedBy = $authUser ? $authUser->id : ($batch->created_by ?? 1);

        logger('=== BATCH PROCESSING START ===', ['batch_id' => $batchId, 'tenant_id' => $tenantId, 'subscription' => $subscription]);

        $branchId = $batch->branch_id ?? 1;
        $formIds = $batch->form_ids;

        if (!is_array($formIds) || empty($formIds)) {
            logger('Invalid form_ids', ['batch_id' => $batchId]);
            $batch->update(['status' => 'failed', 'processed_at' => now()]);
            return [];
        }

        $month = \Carbon\Carbon::parse($batch->period_from)->month;
        $year = \Carbon\Carbon::parse($batch->period_from)->year;

        if (!$month || !$year) {
            logger('Missing period_month or period_year', ['batch_id' => $batchId]);
            $batch->update(['status' => 'failed', 'processed_at' => now()]);
            return [];
        }

        // FULL subscription: Validate payroll exists
        if ($isFull) {
            $payrollExists = \App\Models\WorkforcePayrollCycle::query()
                ->whereDate('period_from', $batch->period_from)
                ->whereDate('period_to', $batch->period_to)
                ->where('status', 'processed')
                ->exists();

            if (!$payrollExists) {
                logger()->error('Payroll not found for FULL subscription', [
                    'period_from' => $batch->period_from,
                    'period_to' => $batch->period_to,
                ]);
                $batch->update(['status' => 'failed', 'processed_at' => now()]);
                throw new \Exception("Payroll not processed for period {$batch->period_from} to {$batch->period_to}.");
            }
            logger('Payroll validated for FULL subscription');
        } else {
            logger('Skipping payroll validation for MINIMAL subscription');
        }

        $results = [];

        foreach ($formIds as $formId) {
            try {
                $form = ComplianceFormsMaster::findOrFail($formId);

                logger("Generating {$form->form_code}");

                // Use orchestrator for consistent pipeline
                $result = $this->orchestrator->execute(
                    $tenantId,
                    $branchId,
                    $month,
                    $year,
                    $form->form_code,
                    'batch',
                    $batchId
                );

                if ($result['status'] === 'failed') {
                    logger()->error("Form generation failed: {$form->form_code}", ['error' => $result['error']]);
                    $results[$formId] = [
                        'success' => false,
                        'form_code' => $form->form_code,
                        'error' => $result['error']
                    ];
                    continue;
                }

                $filePath = $result['result']['file_path'];
                logger("File written: {$filePath}");

                // Create batch form record
                \App\Models\ComplianceBatchForm::create([
                    'tenant_id' => $tenantId,
                    'batch_id' => $batchId,
                    'form_code' => $form->form_code,
                    'section' => $form->section->section_name ?? 'General',
                    'file_path' => $filePath,
                    'status' => 'success',
                    'created_at' => now(),
                ]);

                // Log generation
                $checksum = '';
                $fullPath = storage_path('app/' . $filePath);
                if (file_exists($fullPath)) {
                    $checksum = hash_file('sha256', $fullPath);
                }

                $logData = [
                    'tenant_id' => $tenantId,
                    'batch_id' => $batchId,
                    'form_id' => $formId,
                    'compliance_status_id' => null,
                    'generated_by' => $generatedBy,
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

                if (Schema::hasColumn('compliance_generation_logs', 'source')) {
                    $logData['source'] = 'Automated';
                }

                DB::table('compliance_generation_logs')->insert($logData);

                $results[$formId] = [
                    'success' => true,
                    'form_code' => $form->form_code,
                    'file_path' => $filePath,
                    'status' => 'Generated'
                ];

                if ($month && $year) {
                    $this->timelineService->markAsGenerated($tenantId, $formId, $month, $year);
                }
            } catch (\Exception $e) {
                logger()->error("Exception in form generation", [
                    'form_id' => $formId,
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage(),
                ]);

                $errorData = [
                    'tenant_id' => $tenantId,
                    'batch_id' => $batchId,
                    'form_id' => $formId,
                    'compliance_status_id' => null,
                    'generated_by' => $generatedBy,
                    'file_path' => '',
                    'checksum_hash' => '',
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'CLI',
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'status' => 'failed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('compliance_generation_logs', 'source')) {
                    $errorData['source'] = 'Automated';
                }
                if (Schema::hasColumn('compliance_generation_logs', 'error_message')) {
                    $errorData['error_message'] = $e->getMessage();
                }

                DB::table('compliance_generation_logs')->insert($errorData);

                $results[$formId] = [
                    'success' => false,
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage()
                ];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        $finalStatus = $successCount === $totalCount ? 'completed' : ($successCount > 0 ? 'partially_completed' : 'failed');

        // CRITICAL: Run audit automatically after generation
        try {
            logger('Running batch audit...');
            $this->auditService->auditBatch($batchId);
            logger('Batch audit completed');
        } catch (\Exception $e) {
            logger()->error('Batch audit failed', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
        }

        // CRITICAL: Run certification automatically after audit
        try {
            logger('Running batch certification...');
            $certService = app(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
            $certResult = $certService->certifyBatch($batchId);
            logger('Batch certification completed', ['batch_id' => $batchId, 'certified' => $certResult['certified'], 'score' => $certResult['score']]);
        } catch (\Exception $e) {
            logger()->error('Batch certification failed', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
        }

        $batch->update([
            'status' => $finalStatus,
            'processed_at' => now(),
            'results' => $results,
        ]);

        logger('=== BATCH PROCESSING END ===', [
            'batch_id' => $batchId,
            'status' => $finalStatus,
            'success_count' => $successCount,
            'total_count' => $totalCount
        ]);

        return $results;
    }
}
