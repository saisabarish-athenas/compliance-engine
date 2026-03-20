# Batch Process Endpoint - Complete Fix

## Problem
The `/compliance/batch/{id}/process` endpoint times out because it processes all forms synchronously.

## Root Cause
- `processBatch()` iterates through all forms (34+)
- Each form calls `orchestrator->execute()` which can take 5-30 seconds
- Total time: 3-15 minutes
- PHP default timeout: 30 seconds
- Result: Request times out before completion

## Solution: Async Processing with Queue

### Step 1: Create Job File
**File:** `app/Jobs/ProcessComplianceBatchJob.php`

```php
<?php
namespace App\Jobs;

use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessComplianceBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $batchId) {
        $this->onQueue('compliance');
    }

    public function handle(ComplianceExecutionService $service): void
    {
        try {
            Log::info("Processing batch {$this->batchId}");
            $batch = ComplianceExecutionBatch::findOrFail($this->batchId);
            $batch->update(['status' => 'processing']);
            
            $results = $service->processBatch($this->batchId);
            
            $batch->update([
                'status' => $results['failed'] === 0 ? 'processed' : 'partial',
                'updated_at' => now()
            ]);
            
            Log::info("Batch {$this->batchId} processed", ['results' => $results]);
        } catch (\Exception $e) {
            Log::error("Batch {$this->batchId} failed", ['error' => $e->getMessage()]);
            ComplianceExecutionBatch::find($this->batchId)?->update(['status' => 'failed']);
            throw $e;
        }
    }
}
```

### Step 2: Update Controller
**File:** `app/Http/Controllers/ComplianceExecutionController.php`

Replace the `processBatch` method:

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

        // Dispatch async job
        \App\Jobs\ProcessComplianceBatchJob::dispatch($batch);
        $batchModel->update(['status' => 'queued']);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch processing started. Forms are being generated in the background.',
            'batch_id' => $batchModel->id
        ]);
    } catch (\Exception $e) {
        Log::error('Batch process error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to start batch processing: ' . $e->getMessage()
        ], 500);
    }
}
```

### Step 3: Add Status Endpoint
**File:** `app/Http/Controllers/ComplianceExecutionController.php`

Add new method:

```php
public function getBatchStatus(int $batch)
{
    try {
        $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batch)
            ->firstOrFail();

        $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch)->get();
        $completed = $forms->where('status', 'success')->count();
        $failed = $forms->where('status', 'failed')->count();
        $total = $forms->count();

        return response()->json([
            'status' => 'success',
            'batch_status' => $batchModel->status,
            'progress' => [
                'total' => $total,
                'completed' => $completed,
                'failed' => $failed,
                'pending' => $total - $completed - $failed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### Step 4: Add Route
**File:** `routes/compliance.php`

Add route:

```php
Route::get('/batch/{batch}/status', [ComplianceExecutionController::class, 'getBatchStatus'])->name('compliance.batch.status');
```

### Step 5: Update Dashboard
**File:** `resources/views/compliance/dashboard.blade.php`

Replace proceed button handler:

```javascript
if (e.target.classList.contains('proceed-batch-btn')) {
    const btn = e.target;
    const batchId = btn.dataset.batch;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting...';

    fetch(`/compliance/batch/${batchId}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
        return r.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Batch processing started in background.');
            pollBatchStatus(batchId, btn);
        } else {
            alert('❌ Error: ' + (data.message || 'Processing failed'));
            btn.disabled = false;
            btn.innerHTML = '✅ Proceed to Generate';
        }
    })
    .catch(err => {
        alert('❌ Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '✅ Proceed to Generate';
    });
}

function pollBatchStatus(batchId, btn) {
    const pollInterval = setInterval(() => {
        fetch(`/compliance/batch/${batchId}/status`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const progress = data.progress;
                const percentage = progress.percentage;
                
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Processing ${percentage}%`;
                
                if (percentage === 100) {
                    clearInterval(pollInterval);
                    alert('✅ Batch processing complete!');
                    window.location.reload();
                }
            }
        })
        .catch(err => console.error('Status poll error:', err));
    }, 2000); // Poll every 2 seconds
}
```

### Step 6: Configure Queue
**File:** `.env`

```env
QUEUE_CONNECTION=database
```

Or use Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Step 7: Create Queue Table (if using database)

```bash
php artisan queue:table
php artisan migrate
```

### Step 8: Start Queue Worker

```bash
php artisan queue:work --queue=compliance --tries=3
```

Or for production (using supervisor):

```ini
[program:compliance-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=compliance --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue.log
```

## Testing

### 1. Test Batch Creation
```
1. Open Dashboard
2. Select Month and Year
3. Click "Create Batch"
4. Verify batch appears in review
```

### 2. Test Async Processing
```
1. Click "Proceed to Generate"
2. Should see "Batch processing started"
3. Progress bar should update every 2 seconds
4. After completion, page reloads
```

### 3. Check Queue
```bash
# View pending jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear queue
php artisan queue:flush
```

## Fallback: Synchronous with Increased Timeout

If queue is not available, increase PHP timeout:

**File:** `php.ini`
```ini
max_execution_time = 900  ; 15 minutes
```

Or in `.htaccess`:
```apache
php_value max_execution_time 900
```

Or in `nginx.conf`:
```nginx
fastcgi_read_timeout 900s;
```

## Monitoring

### Check Queue Status
```bash
php artisan queue:work --queue=compliance --verbose
```

### View Logs
```bash
tail -f storage/logs/laravel.log | grep "Processing batch"
```

### Database Query
```sql
SELECT * FROM jobs WHERE queue = 'compliance';
SELECT * FROM failed_jobs;
```

## Benefits

✅ No timeout errors
✅ Better UX with progress bar
✅ Forms generate in background
✅ User can navigate away
✅ Scalable to multiple workers
✅ Retry failed jobs automatically

## Status Codes

- `pending` - Batch created, waiting to process
- `queued` - Job dispatched to queue
- `processing` - Currently generating forms
- `processed` - All forms generated successfully
- `partial` - Some forms failed
- `failed` - Batch processing failed

## Troubleshooting

### Queue not processing
```bash
# Check if worker is running
ps aux | grep queue:work

# Start worker
php artisan queue:work --queue=compliance

# Check failed jobs
php artisan queue:failed
```

### Jobs stuck in queue
```bash
# Clear queue
php artisan queue:flush

# Retry failed jobs
php artisan queue:retry all
```

### High memory usage
```bash
# Limit batch size
php artisan queue:work --queue=compliance --max-jobs=10
```

## Production Deployment

1. Set `QUEUE_CONNECTION=redis` in `.env`
2. Configure Redis connection
3. Set up supervisor for queue workers
4. Monitor queue with `php artisan queue:monitor`
5. Set up alerts for failed jobs
6. Regular cleanup: `php artisan queue:prune-failed`
