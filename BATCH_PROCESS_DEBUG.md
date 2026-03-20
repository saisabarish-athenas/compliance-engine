# Batch Process Endpoint - Debug Guide

## Issue
The `/compliance/batch/{id}/process` endpoint is being called but may be timing out or returning errors silently.

## Root Causes to Check

### 1. Long Processing Time
The `processBatch` method iterates through all forms and calls `orchestrator->execute()` for each one.
- Each form generation can take 5-30 seconds
- With 34 forms, total time could be 3-15 minutes
- Default PHP timeout is 30 seconds

### 2. Missing Orchestrator Implementation
The `ComplianceOrchestrator::execute()` method may not be fully implemented.

### 3. Database Locks
Multiple forms being processed simultaneously may cause database locks.

## Solution: Async Processing

Instead of waiting for all forms to generate, use a queue-based approach:

### Step 1: Create a Job
```php
// app/Jobs/ProcessComplianceBatch.php
namespace App\Jobs;

use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessComplianceBatch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $batchId
    ) {}

    public function handle(ComplianceExecutionService $service)
    {
        $service->processBatch($this->batchId);
    }
}
```

### Step 2: Update Controller
```php
public function processBatch(int $batch)
{
    try {
        $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batch)
            ->firstOrFail();

        if ($batchModel->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => "Batch cannot be processed. Current status: {$batchModel->status}"
            ], 422);
        }

        // Dispatch job instead of processing synchronously
        ProcessComplianceBatch::dispatch($batch);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch processing started. You will be notified when complete.',
            'batch_id' => $batch
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to start batch processing: ' . $e->getMessage()
        ], 500);
    }
}
```

### Step 3: Update Dashboard
```javascript
if (data.status === 'success') {
    alert('✅ Batch processing started in background. You will be notified when complete.');
    // Poll for status instead of reloading
    pollBatchStatus(batchId);
}
```

## Temporary Fix: Increase Timeout

Add to dashboard.blade.php:

```javascript
const controller = new AbortController();
const timeoutId = setTimeout(() => controller.abort(), 600000); // 10 minutes

fetch(`/compliance/batch/${batchId}/process`, {
    method: 'POST',
    headers: { ... },
    signal: controller.signal
})
.then(r => {
    clearTimeout(timeoutId);
    if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
    return r.json();
})
.catch(err => {
    clearTimeout(timeoutId);
    if (err.name === 'AbortError') {
        alert('Request timeout. Processing may still be running.');
    } else {
        alert('Error: ' + err.message);
    }
});
```

## Debugging Steps

### 1. Check Server Logs
```bash
tail -f storage/logs/laravel.log
```

### 2. Add Logging to processBatch
```php
public function processBatch(int $batch)
{
    try {
        Log::info("Starting batch process", ['batch_id' => $batch]);
        
        $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batch)
            ->firstOrFail();

        Log::info("Batch found", ['status' => $batchModel->status]);

        if ($batchModel->status !== 'pending') {
            Log::warning("Batch not pending", ['status' => $batchModel->status]);
            return response()->json([...], 422);
        }

        Log::info("Calling execution service");
        $results = $this->executionService->processBatch($batchModel->id);
        Log::info("Batch processed", ['results' => $results]);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch processed successfully!',
            'batch_id' => $batchModel->id,
            'results' => $results
        ]);
    } catch (\Exception $e) {
        Log::error("Batch process error", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to process batch: ' . $e->getMessage()
        ], 500);
    }
}
```

### 3. Test Endpoint Directly
```bash
curl -X POST http://127.0.0.1:8000/compliance/batch/43/process \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -H "Cookie: LARAVEL_SESSION=YOUR_SESSION"
```

### 4. Check Database
```sql
SELECT * FROM compliance_execution_batches WHERE id = 43;
SELECT * FROM compliance_batch_forms WHERE batch_id = 43;
SELECT * FROM compliance_generation_logs WHERE batch_id = 43;
```

## Quick Fix: Synchronous Processing with Timeout

Update `php.ini`:
```ini
max_execution_time = 600  ; 10 minutes
```

Or in `.htaccess`:
```apache
php_value max_execution_time 600
```

## Recommended: Queue-Based Processing

1. Set up Laravel queue (Redis or database)
2. Create ProcessComplianceBatch job
3. Dispatch job from controller
4. Poll for status from frontend
5. Show progress bar while processing

This prevents timeout and provides better UX.

## Testing Checklist

- [ ] Check server logs for errors
- [ ] Verify batch status in database
- [ ] Test with single form first
- [ ] Increase timeout to 10 minutes
- [ ] Implement queue-based processing
- [ ] Add progress polling to frontend
