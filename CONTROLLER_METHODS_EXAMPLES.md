# Additional Controller Methods - Code Examples

## Add These Methods to ComplianceExecutionController

### 1. Get Violations for a Batch

```php
public function getViolations(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $violations = ComplianceAuditLog::where('batch_id', $batchId)
            ->where('status', 'failed')
            ->get(['form_code', 'violations']);

        return response()->json([
            'status' => 'success',
            'forms' => $violations->map(fn($v) => [
                'form_code' => $v->form_code,
                'violations' => is_array($v->violations) ? $v->violations : json_decode($v->violations, true)
            ])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 2. Get Preview Data for a Batch

```php
public function getPreview(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $forms = ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'success')
            ->get(['form_code', 'file_path']);

        $auditLogs = ComplianceAuditLog::where('batch_id', $batchId)
            ->get(['form_code', 'audit_score', 'status']);

        $certLog = DB::table('compliance_certification_logs')
            ->where('batch_id', $batchId)
            ->where('form_code', 'BATCH_SUMMARY')
            ->first();

        return view('compliance.preview', [
            'batch' => $batch,
            'forms' => $forms,
            'auditLogs' => $auditLogs,
            'certLog' => $certLog
        ]);
    } catch (\Exception $e) {
        return redirect()->route('compliance.dashboard')
            ->with('error', 'Failed to load preview: ' . $e->getMessage());
    }
}
```

### 3. Get Batch Audit Details

```php
public function getBatchAuditDetails(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $auditLogs = ComplianceAuditLog::where('batch_id', $batchId)
            ->get();

        $avgScore = $auditLogs->isNotEmpty() ? round($auditLogs->avg('audit_score')) : 0;
        $passedCount = $auditLogs->where('status', 'passed')->count();
        $totalCount = $auditLogs->count();

        return response()->json([
            'status' => 'success',
            'batch_id' => $batchId,
            'average_score' => $avgScore,
            'passed_forms' => $passedCount,
            'total_forms' => $totalCount,
            'audit_status' => $passedCount === $totalCount ? 'passed' : ($passedCount === 0 ? 'failed' : 'partial'),
            'forms' => $auditLogs->map(fn($log) => [
                'form_code' => $log->form_code,
                'score' => $log->audit_score,
                'status' => $log->status,
                'violations_count' => count(is_array($log->violations) ? $log->violations : json_decode($log->violations, true) ?? [])
            ])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 4. Get Batch Certification Details

```php
public function getBatchCertificationDetails(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $certLog = DB::table('compliance_certification_logs')
            ->where('batch_id', $batchId)
            ->where('form_code', 'BATCH_SUMMARY')
            ->first();

        if (!$certLog) {
            return response()->json([
                'status' => 'not_certified',
                'message' => 'Batch not yet certified'
            ]);
        }

        $violations = json_decode($certLog->violations, true);

        return response()->json([
            'status' => 'success',
            'batch_id' => $batchId,
            'certified' => $certLog->certified,
            'score' => $certLog->certification_score,
            'certification_status' => $certLog->certified ? 'Inspection Ready' : 'Review Required',
            'violations' => $violations['violations'] ?? [],
            'warnings' => $violations['warnings'] ?? [],
            'critical_errors' => $violations['critical_errors'] ?? [],
            'certified_at' => $certLog->certified_at
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 5. Re-run Batch Audit

```php
public function reAuditBatch(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $result = $this->auditService->auditBatch($batchId);

        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'batch_score' => $result['batch_score'],
                'batch_status' => $result['batch_status'],
                'passed_forms' => $result['passed_forms'],
                'total_forms' => $result['total_forms'],
                'message' => "Batch audit completed. Score: {$result['batch_score']}%"
            ]);
        }

        return response()->json($result, 400);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 6. Re-run Batch Certification

```php
public function recertifyBatch(int $batchId)
{
    try {
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->firstOrFail();

        $certificationService = app(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
        $result = $certificationService->certifyBatch($batchId);

        return response()->json([
            'status' => 'success',
            'certified' => $result['certified'],
            'score' => $result['score'],
            'certification_status' => $result['status'],
            'violations' => $result['violations'],
            'warnings' => $result['warnings'],
            'critical_errors' => $result['critical_errors'],
            'message' => $result['message']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

## Routes to Add

Add these routes to `routes/compliance.php`:

```php
// Batch Details
Route::get('/batches/{batch}/audit-details', 'ComplianceExecutionController@getBatchAuditDetails');
Route::get('/batches/{batch}/certification-details', 'ComplianceExecutionController@getBatchCertificationDetails');
Route::get('/batches/{batch}/violations', 'ComplianceExecutionController@getViolations');
Route::get('/batches/{batch}/preview', 'ComplianceExecutionController@getPreview');

// Batch Actions
Route::post('/batches/{batch}/re-audit', 'ComplianceExecutionController@reAuditBatch');
Route::post('/batches/{batch}/recertify', 'ComplianceExecutionController@recertifyBatch');

// Fix Violations (already exists)
Route::post('/batches/{batch}/forms/{form}/fix', 'ComplianceExecutionController@fixViolations');
Route::post('/batches/{batch}/forms/{form}/fix-submit', 'ComplianceExecutionController@submitFix');

// Inspection Pack (already exists)
Route::get('/batches/{batch}/inspection-pack', 'ComplianceExecutionController@downloadInspectionPack')->name('compliance.inspection-pack');
```

## Dashboard Controller Enhancement

Update the `dashboard()` method to enrich batches with audit and certification data:

```php
public function dashboard()
{
    try {
        $user = Auth::user();

        if (!$user || !$user->tenant) {
            abort(500, 'User not authenticated or tenant not assigned');
        }

        $subscription = $this->subscription();
        $tenantId = $user->tenant_id;

        $branch = \App\Models\Branch::where('tenant_id', $tenantId)->first();
        $sections = ComplianceSection::where('is_active', true)->get();
        
        $statutorySections = config('statutory_form_grouping.sections');
        $formCodeToId = ComplianceFormsMaster::pluck('id', 'form_code')->toArray();
        
        $batches = ComplianceExecutionBatch::with('section')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Enrich each batch with audit and certification data
        foreach ($batches as $batch) {
            // Calculate display status from generation logs
            $logs = DB::table('compliance_generation_logs')
                ->where('batch_id', $batch->id)
                ->pluck('status');

            $batch->display_status = $logs->isEmpty() ? 'Pending' : 
                ($logs->contains('processing') ? 'Processing' : 
                ($logs->every(fn($s) => in_array($s, ['success', 'completed'])) ? 'Completed' : 
                ($logs->every(fn($s) => $s === 'failed') ? 'Failed' : 'Partially Completed')));

            // Get audit data
            $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch->id)->get();
            if ($auditLogs->isNotEmpty()) {
                $batch->audit_score = round($auditLogs->avg('audit_score'));
                $passedCount = $auditLogs->where('status', 'passed')->count();
                $totalCount = $auditLogs->count();
                $batch->audit_status = $passedCount === $totalCount ? 'Passed' : 
                    ($passedCount === 0 ? 'Failed' : 'Partial');
                $batch->audit_logs = $auditLogs;
                $batch->has_violations = $auditLogs->where('status', 'failed')->count() > 0;
            } else {
                $batch->audit_score = null;
                $batch->audit_status = 'Not Audited';
                $batch->audit_logs = collect();
                $batch->has_violations = false;
            }

            // Get certification data
            $certLog = DB::table('compliance_certification_logs')
                ->where('batch_id', $batch->id)
                ->where('form_code', 'BATCH_SUMMARY')
                ->first();
            
            if ($certLog) {
                $batch->certification_status = $certLog->certified ? 'Inspection Ready' : 'Review Required';
                $batch->certification_score = $certLog->certification_score;
            } else {
                $batch->certification_status = 'Not Certified';
                $batch->certification_score = null;
            }
        }

        $healthService = app(\App\Services\Compliance\ComplianceHealthService::class);
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $healthScore = $healthService->calculateScore($tenantId, $currentMonth, $currentYear);

        $timelineMetrics = $this->timelineService->getTimelineMetrics($tenantId, $currentMonth, $currentYear);

        return view('compliance.dashboard', compact(
            'sections', 'batches', 'subscription', 'branch', 'user', 
            'healthScore', 'timelineMetrics', 'statutorySections', 'formCodeToId'
        ));
    } catch (\Exception $e) {
        logger()->error('Dashboard Error', ['error' => $e->getMessage()]);
        $statutorySections = config('statutory_form_grouping.sections');
        $formCodeToId = ComplianceFormsMaster::pluck('id', 'form_code')->toArray();
        
        return view('compliance.dashboard', [
            'sections' => [],
            'batches' => [],
            'subscription' => 'MINIMAL',
            'branch' => null,
            'user' => Auth::user(),
            'healthScore' => null,
            'timelineMetrics' => null,
            'statutorySections' => $statutorySections,
            'formCodeToId' => $formCodeToId,
            'error' => 'Failed to load dashboard: ' . $e->getMessage()
        ]);
    }
}
```

## Service Provider Registration

Ensure these services are registered in `app/Providers/ComplianceServiceProvider.php`:

```php
public function register()
{
    $this->app->singleton(\App\Services\Compliance\InspectionPackService::class);
    $this->app->singleton(\App\Services\Compliance\Audit\ComplianceAuditService::class);
    $this->app->singleton(\App\Services\Compliance\Audit\ComplianceCorrectionService::class);
    $this->app->singleton(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
}
```

## Testing the Implementation

### Test 1: Verify Automatic Audit Trigger
```php
// Create batch and process
$batch = $this->executionService->processBatch($batchId);

// Verify audit logs created
$auditLogs = ComplianceAuditLog::where('batch_id', $batchId)->get();
$this->assertNotEmpty($auditLogs);
```

### Test 2: Verify Automatic Certification Trigger
```php
// Process batch
$this->executionService->processBatch($batchId);

// Verify certification logs created
$certLog = DB::table('compliance_certification_logs')
    ->where('batch_id', $batchId)
    ->where('form_code', 'BATCH_SUMMARY')
    ->first();
$this->assertNotNull($certLog);
```

### Test 3: Verify Fix Violations Workflow
```php
// Get violations
$result = $this->correctionService->fixFormViolations($batchId, 'FORM_B');

// If requires input
if ($result['status'] === 'requires_input') {
    // Submit user input
    $fixResult = $this->correctionService->fixWithUserInput(
        $batchId, 
        'FORM_B', 
        ['field' => 'value']
    );
    $this->assertEquals('success', $fixResult['status']);
}
```

### Test 4: Verify Inspection Pack Generation
```php
// Generate inspection pack
$zipPath = $this->inspectionPackService->generateInspectionPack($batchId);

// Verify ZIP exists
$this->assertTrue(file_exists($zipPath));

// Verify ZIP contains forms
$zip = new \ZipArchive;
$zip->open($zipPath);
$this->assertGreaterThan(0, $zip->numFiles);
$zip->close();
```
