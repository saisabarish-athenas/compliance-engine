    public function createBatch(Request $request)
    {
        Log::info('=== CREATE BATCH START ===');
        Log::info('Request data: ' . json_encode($request->all()));
        
        try {
            $tenantId = Auth::user()->tenant_id;
            Log::info('Tenant ID: ' . $tenantId);

            $validated = $request->validate([
                'period_month' => 'required|integer|min:1|max:12',
                'period_year' => 'required|integer|min:2020|max:2030',
            ]);
            Log::info('Validation passed: ' . json_encode($validated));

            $batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
            Log::info('BatchOrchestrator instantiated');
            
            $batch = $batchOrchestrator->createBatch(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );
            Log::info('Batch created: ' . $batch->id);

            $this->timelineService->createTimelineOnBatchCreation(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );
            Log::info('Timeline created');

            $response = [
                'status' => 'success',
                'batch_id' => $batch->id,
                'period' => Carbon::create($validated['period_year'], $validated['period_month'], 1)->format('F Y'),
                'forms' => [],
                'data_availability' => [],
            ];
            
            Log::info('Response: ' . json_encode($response));
            Log::info('=== CREATE BATCH END ===');
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('=== CREATE BATCH ERROR ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
