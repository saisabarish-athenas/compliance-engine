# Implementation Guide - Batch Process Async Fix

## Overview
This guide provides step-by-step instructions to implement async batch processing to fix the timeout issue.

## Prerequisites
- Laravel 12 installed
- Database configured
- Redis or database queue configured

## Step-by-Step Implementation

### Step 1: Create the Job File

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

    public function __construct(private int $batchId)
    {
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
            
            Log::info("Batch {$this->batchId} processed successfully", ['results' => $results]);
        } catch (\Exception $e) {
            Log::error("Failed to process batch {$this->batchId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $batch = ComplianceExecutionBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }
}
```

### Step 2: Update Controller Method

**File:** `app/Http/Controllers/ComplianceExecutionController.php`

Find the `processBatch` method and replace it:

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

        // Dispatch async job to prevent timeout
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

Add this new method to the controller:

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

Add this route after the process route:

```php
Route::get('/batch/{batch}/status', [ComplianceExecutionController::class, 'getBatchStatus'])->name('compliance.batch.status');
```

### Step 5: Update Dashboard

**File:** `resources/views/compliance/dashboard.blade.php`

Find the proceed button handler and replace it with:

```javascript
// Batch review actions
document.addEventListener('click', function(e) {
    // Proceed button
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
});

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

Set queue connection:

```env
QUEUE_CONNECTION=database
```

Or for Redis (recommended for production):

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

### Step 8: Test Locally

```bash
# Terminal 1: Start queue worker
php artisan queue:work --queue=compliance --verbose

# Terminal 2: Open browser and test
# 1. Create batch
# 2. Click "Proceed to Generate"
# 3. Watch progress bar
# 4. Verify forms generate
```

### Step 9: Production Deployment

#### Option A: Using Redis (Recommended)

1. Install Redis:
```bash
sudo apt-get install redis-server
sudo systemctl start redis-server
```

2. Configure `.env`:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

3. Create supervisor config `/etc/supervisor/conf.d/compliance-queue.conf`:
```ini
[program:compliance-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=compliance --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue.log
stopwaitsecs=3600
```

4. Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start compliance-queue:*
```

#### Option B: Using Database Queue

1. Configure `.env`:
```env
QUEUE_CONNECTION=database
```

2. Create supervisor config (same as above)

3. Start supervisor

### Step 10: Verify Deployment

```bash
# Check queue status
php artisan queue:work --verbose

# View failed jobs
php artisan queue:failed

# Monitor queue
php artisan queue:monitor

# Retry failed jobs
php artisan queue:retry all
```

## Verification Checklist

- [ ] Job file created at `app/Jobs/ProcessComplianceBatchJob.php`
- [ ] Controller method updated with async dispatch
- [ ] Status endpoint added to controller
- [ ] Route added to `routes/compliance.php`
- [ ] Dashboard updated with polling logic
- [ ] Queue configured in `.env`
- [ ] Queue table migrated (if using database)
- [ ] Queue worker started
- [ ] Batch creation works
- [ ] Batch processing starts without timeout
- [ ] Progress bar updates
- [ ] Forms generate in background
- [ ] Page reloads when complete

## Troubleshooting

### Queue not processing jobs

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

### Redis connection error

```bash
# Check Redis is running
redis-cli ping

# Should return: PONG
```

## Performance Tuning

### Increase number of workers
```ini
numprocs=8  ; Instead of 4
```

### Increase timeout
```ini
stopwaitsecs=7200  ; 2 hours
```

### Monitor performance
```bash
php artisan queue:monitor --max=1000
```

## Rollback (if needed)

If you need to revert to synchronous processing:

1. Remove the job dispatch from controller
2. Restore original `processBatch` method
3. Restart application

## Support

For issues, check:
- `storage/logs/laravel.log` - Application logs
- `storage/logs/queue.log` - Queue worker logs
- `php artisan queue:failed` - Failed jobs

---

**Status:** Ready for implementation
**Estimated Time:** 30 minutes
**Difficulty:** Medium
**Risk:** Low
