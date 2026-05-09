    public function previewForm(int $batch, string $form)
    {
        $batchModel = ComplianceExecutionBatch::findOrFail($batch);
        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batchModel->tenant_id,
            $batchModel->branch_id
        );

        $executionService = app(\App\Services\Compliance\ComplianceExecutionService::class);

        $data = $executionService->getFormDataViaAPI(
            $form,
            $batchModel->tenant_id,
            $branchId,
            $batchModel->period_month,
            $batchModel->period_year
        );

        $formMaster = ComplianceFormsMaster::where('form_code', $form)->firstOrFail();

        $viewPath = "compliance.forms." . strtolower($form);

        return view($viewPath, [
            'form_title' => $formMaster->form_name,
            'form_code' => $form,
            'header' => $data['header'] ?? [],
            'rows' => $data['rows'] ?? [],
            'totals' => $data['totals'] ?? [],
            'is_nil' => $data['is_nil'] ?? empty($data['rows'] ?? []),
            'period_month' => $batchModel->period_month,
            'period_year' => $batchModel->period_year
        ]);
    }
