    public function createBatch(Request $request)
    {
        try {
            $tenantId = Auth::user()->tenant_id;

            $validated = $request->validate([
                'period_month' => 'required|integer|min:1|max:12',
                'period_year' => 'required|integer|min:2020|max:2030',
            ]);

            $batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
            $batch = $batchOrchestrator->createBatch(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $this->timelineService->createTimelineOnBatchCreation(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $reviewService = app(\App\Services\Compliance\BatchReviewService::class);
            $reviewData = $reviewService->prepareReviewData($batch->id);

            return response()->json([
                'status' => 'success',
                'batch_id' => $batch->id,
                'period' => \Carbon\Carbon::create($validated['period_year'], $validated['period_month'], 1)->format('F Y'),
                'forms' => $reviewData['forms'] ?? [],
                'data_availability' => $reviewData['data_availability'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Batch Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
