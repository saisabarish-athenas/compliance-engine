<?php

namespace App\Services\Compliance\Validation;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Support\Facades\DB;

class ComplianceCertificationService
{
    public function __construct(
        private StructuralFormatValidator $structuralValidator,
        private LegalRuleValidator $legalValidator,
        private CrossFormValidator $crossFormValidator,
        private ComputationValidator $computationValidator,
        private LayoutIntegrityValidator $layoutValidator,
        private \App\Compliance\ComplianceDataService $dataService
    ) {}

    public function certifyBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        
        $forms = ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'success')
            ->get();

        if ($forms->isEmpty()) {
            return [
                'certified' => false,
                'score' => 0,
                'message' => 'No forms generated for certification'
            ];
        }

        $allViolations = [];
        $allWarnings = [];
        $criticalErrors = [];
        $formScores = [];
        $allFormsData = [];

        // Step 1-5: Validate each form individually
        foreach ($forms as $form) {
            $preparedData = $this->getPreparedData($batch, $form->form_code);
            $allFormsData[$form->form_code] = $preparedData;

            // Run all validators
            $structuralViolations = $this->structuralValidator->validate($form->form_code, $preparedData);
            $legalViolations = $this->legalValidator->validate($form->form_code, $preparedData);
            $computationViolations = $this->computationValidator->validate($form->form_code, $preparedData);
            $layoutViolations = $this->layoutValidator->validate($form->form_code, $preparedData);

            $formViolations = array_merge(
                $structuralViolations,
                $legalViolations,
                $computationViolations,
                $layoutViolations
            );

            // Integration of Compliance Validation Layer
            $validator = app(\App\Services\Compliance\Validation\ComplianceFormValidator::class);
            $validationResult = $validator->validate($form->form_code, $preparedData);

            if (!$validationResult['valid']) {
                foreach ($validationResult['violations'] as $violation) {
                    $formViolations[] = array_merge($violation, ['severity' => 'critical']);
                }
            }
            foreach ($validationResult['warnings'] as $warning) {
                $formViolations[] = array_merge($warning, ['severity' => 'medium']);
            }

            // Blade Rendering Validation Check (Critical Failure if Crashes)
            try {
                $this->dataService->renderForm(
                    $form->form_code,
                    $batch->tenant_id,
                    $batch->branch_id ?? \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe($batch->tenant_id, $batch->branch_id),
                    $batch->period_month,
                    $batch->period_year
                );
            } catch (\Exception $e) {
                $formViolations[] = [
                    'type' => 'structural',
                    'field' => 'blade_render',
                    'message' => 'Template crashes on rendering: ' . $e->getMessage(),
                    'severity' => 'critical'
                ];
            }

            // Categorize violations
            foreach ($formViolations as $violation) {
                if (($violation['severity'] ?? 'medium') === 'critical') {
                    $criticalErrors[] = array_merge($violation, ['form_code' => $form->form_code]);
                } elseif (in_array($violation['type'], ['structural', 'legal', 'computation'])) {
                    $allViolations[] = array_merge($violation, ['form_code' => $form->form_code]);
                } else {
                    $allWarnings[] = array_merge($violation, ['form_code' => $form->form_code]);
                }
            }

            // Calculate form score
            $formScore = $this->calculateFormScore($formViolations);
            $formScores[$form->form_code] = $formScore;

            // Log individual form certification
            $this->logFormCertification($batchId, $form->form_code, $formScore, $formViolations);
        }

        // Cross-form validation
        $crossFormViolations = $this->crossFormValidator->validate($batchId, $allFormsData);
        foreach ($crossFormViolations as $violation) {
            $allViolations[] = $violation;
        }

        // Calculate final score
        $finalScore = $this->calculateFinalScore($formScores, $crossFormViolations);

        // Determine certification status based on exact tiering
        $certified = ($finalScore === 100 && empty($criticalErrors));
        
        if ($finalScore === 100) {
            $status = 'Inspection Ready';
        } elseif ($finalScore >= 70 && $finalScore <= 99) {
            $status = 'Minor Issues';
        } else {
            $status = 'Correction Required';
        }

        // Log batch certification
        $this->logBatchCertification($batchId, $finalScore, $certified, $allViolations, $allWarnings, $criticalErrors);

        return [
            'certified' => $certified,
            'score' => $finalScore,
            'status' => $status,
            'violations' => $allViolations,
            'warnings' => $allWarnings,
            'critical_errors' => $criticalErrors,
            'form_scores' => $formScores,
            'message' => $certified 
                ? 'Batch certified for TN statutory inspection' 
                : 'Batch not certified. Resolve violations first.'
        ];
    }

    private function getPreparedData(ComplianceExecutionBatch $batch, string $formCode): array
    {
        $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
        $generator = $factory->make($formCode);

        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batch->tenant_id,
            $batch->branch_id
        );

        // Get data based on subscription
        $subscription = \App\Models\Tenant::find($batch->tenant_id)->subscription_type;
        
        if ($subscription === 'MINIMAL') {
            $adapter = app(\App\Services\Compliance\ManualDataAdapter::class);
            $rawData = $adapter->adaptForFormGenerator(
                $formCode,
                $batch->tenant_id,
                $branchId,
                $batch->period_month,
                $batch->period_year
            );
        } else {
            $aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);
            $rawData = $aggregator->aggregate(
                $formCode,
                $batch->tenant_id,
                $branchId,
                $batch->period_month,
                $batch->period_year
            );
        }

        $reflection = new \ReflectionClass($generator);
        $method = $reflection->getMethod('prepareData');
        $method->setAccessible(true);
        
        return $method->invoke($generator, $rawData);
    }

    private function calculateFormScore(array $violations): int
    {
        $criticalCount = count(array_filter($violations, fn($v) => ($v['severity'] ?? 'medium') === 'critical'));
        $majorCount = count(array_filter($violations, fn($v) => in_array($v['type'], ['structural', 'legal', 'computation'])));
        $minorCount = count($violations) - $criticalCount - $majorCount;

        $score = 100;
        $score -= ($criticalCount * 50); // Critical = -50 each
        $score -= ($majorCount * 10);    // Major = -10 each
        $score -= ($minorCount * 2);     // Minor = -2 each

        return max(0, $score);
    }

    private function calculateFinalScore(array $formScores, array $crossFormViolations): int
    {
        if (empty($formScores)) {
            return 0;
        }

        $avgFormScore = array_sum($formScores) / count($formScores);
        $crossFormPenalty = count($crossFormViolations) * 5;

        return max(0, (int) round($avgFormScore - $crossFormPenalty));
    }

    private function logFormCertification(int $batchId, string $formCode, int $score, array $violations): void
    {
        DB::table('compliance_certification_logs')->updateOrInsert(
            [
                'batch_id' => $batchId,
                'form_code' => $formCode,
            ],
            [
                'certification_score' => $score,
                'certified' => $score === 100,
                'violations' => json_encode($violations),
                'certified_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function logBatchCertification(int $batchId, int $score, bool $certified, array $violations, array $warnings, array $criticalErrors): void
    {
        DB::table('compliance_certification_logs')->updateOrInsert(
            [
                'batch_id' => $batchId,
                'form_code' => 'BATCH_SUMMARY',
            ],
            [
                'certification_score' => $score,
                'certified' => $certified,
                'violations' => json_encode([
                    'violations' => $violations,
                    'warnings' => $warnings,
                    'critical_errors' => $criticalErrors,
                ]),
                'certified_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
