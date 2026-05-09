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

        $viewData = \App\Services\Compliance\FormDataUnpacker::unpack($data);
        $viewData['form_title'] = $formMaster->form_name;
        $viewData['form_code'] = $form;
        $viewData['period_month'] = $batchModel->period_month;
        $viewData['period_year'] = $batchModel->period_year;

        return view($viewPath, $viewData);
    }
