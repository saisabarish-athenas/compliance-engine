# Dashboard View Updates - Quick Reference

## Blade Template Changes Required

### File: `resources/views/compliance/dashboard.blade.php`

## Batch Table Structure

Replace the existing batch table with this enhanced structure:

```blade
<table class="table table-striped">
    <thead>
        <tr>
            <th>Batch ID</th>
            <th>Section</th>
            <th>Period</th>
            <th>Status</th>
            <th>Audit Score</th>
            <th>Audit Status</th>
            <th>Certification</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($batches as $batch)
            <tr>
                <td>{{ $batch->id }}</td>
                <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                <td>{{ $batch->period_month }}/{{ $batch->period_year }}</td>
                <td>
                    <span class="badge badge-{{ $batch->display_status === 'Completed' ? 'success' : ($batch->display_status === 'Failed' ? 'danger' : 'warning') }}">
                        {{ $batch->display_status }}
                    </span>
                </td>
                <td>
                    @if($batch->audit_score !== null)
                        <span class="badge badge-{{ $batch->audit_score >= 90 ? 'success' : ($batch->audit_score >= 70 ? 'warning' : 'danger') }}">
                            {{ $batch->audit_score }}%
                        </span>
                    @else
                        <span class="badge badge-secondary">Not Audited</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $batch->audit_status === 'Passed' ? 'success' : ($batch->audit_status === 'Partial' ? 'warning' : 'danger') }}">
                        {{ $batch->audit_status ?? 'N/A' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $batch->certification_status === 'Inspection Ready' ? 'success' : ($batch->certification_status === 'Review Required' ? 'warning' : 'secondary') }}">
                        {{ $batch->certification_status ?? 'Not Certified' }}
                    </span>
                </td>
                <td>{{ $batch->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="btn-group" role="group">
                        <!-- Preview Button -->
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#previewModal" onclick="loadPreview({{ $batch->id }})">
                            Preview
                        </button>

                        <!-- Fix Issues Button (only if violations exist) -->
                        @if($batch->has_violations)
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#fixModal" onclick="loadFixIssues({{ $batch->id }})">
                                Fix Issues
                            </button>
                        @endif

                        <!-- Inspection Pack Button (only if certification score >= 70) -->
                        @if($batch->certification_score !== null && $batch->certification_score >= 70)
                            <a href="{{ route('compliance.inspection-pack', $batch->id) }}" class="btn btn-sm btn-success">
                                Inspection Pack
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-success" disabled title="Certification score must be >= 70">
                                Inspection Pack
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No batches found</td>
            </tr>
        @endforelse
    </tbody>
</table>
```

## Fix Issues Modal

Add this modal for fixing violations:

```blade
<div class="modal fade" id="fixModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fix Violations</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fixContent">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitFixBtn" onclick="submitFix()">Submit Fix</button>
            </div>
        </div>
    </div>
</div>
```

## JavaScript Functions

Add these functions to handle fix issues workflow:

```javascript
let currentBatchId = null;
let currentFormCode = null;

function loadFixIssues(batchId) {
    currentBatchId = batchId;
    
    // Get first form with violations
    fetch(`/compliance/batches/${batchId}/violations`)
        .then(response => response.json())
        .then(data => {
            if (data.forms && data.forms.length > 0) {
                currentFormCode = data.forms[0].form_code;
                
                // Attempt automatic fix
                fetch(`/compliance/batches/${batchId}/forms/${currentFormCode}/fix`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        showFixSuccess(result);
                    } else if (result.status === 'requires_input') {
                        showFixForm(result);
                    } else {
                        showFixError(result.message);
                    }
                });
            }
        });
}

function showFixForm(data) {
    let html = `
        <div class="alert alert-info">
            <strong>Auto-Fixed Fields:</strong>
            <ul>
    `;
    
    for (const [field, value] of Object.entries(data.auto_fixed || {})) {
        html += `<li>${field}: ${value}</li>`;
    }
    
    html += `</ul></div>
        <div class="alert alert-warning">
            <strong>Missing Fields - Please Provide:</strong>
        </div>
        <form id="fixForm">
    `;
    
    data.missing_fields.forEach(field => {
        html += `
            <div class="form-group">
                <label for="${field.field}">${field.field}</label>
                <input type="text" class="form-control" id="${field.field}" name="${field.field}" placeholder="${field.message}">
            </div>
        `;
    });
    
    html += `</form>`;
    
    document.getElementById('fixContent').innerHTML = html;
}

function showFixSuccess(result) {
    let html = `
        <div class="alert alert-success">
            <h5>Fix Successful!</h5>
            <p><strong>Form:</strong> ${result.form_code}</p>
            <p><strong>New Score:</strong> ${result.form_score}%</p>
            <p><strong>Batch Average:</strong> ${result.batch_average_score}%</p>
            <p><strong>Status:</strong> ${result.audit_status}</p>
        </div>
    `;
    
    if (result.violations && result.violations.length > 0) {
        html += `<div class="alert alert-warning"><strong>Remaining Violations:</strong><ul>`;
        result.violations.forEach(v => {
            html += `<li>${v.message}</li>`;
        });
        html += `</ul></div>`;
    }
    
    document.getElementById('fixContent').innerHTML = html;
    document.getElementById('submitFixBtn').style.display = 'none';
}

function showFixError(message) {
    document.getElementById('fixContent').innerHTML = `
        <div class="alert alert-danger">
            <strong>Error:</strong> ${message}
        </div>
    `;
}

function submitFix() {
    const formData = new FormData(document.getElementById('fixForm'));
    const corrections = Object.fromEntries(formData);
    
    fetch(`/compliance/batches/${currentBatchId}/forms/${currentFormCode}/fix-submit`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ corrections })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showFixSuccess(result);
            // Refresh dashboard after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showFixError(result.message);
        }
    });
}

function loadPreview(batchId) {
    // Load preview modal content
    fetch(`/compliance/batches/${batchId}/preview`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('previewContent').innerHTML = html;
        });
}
```

## Status Badge Colors

```blade
<!-- Batch Status -->
Pending → badge-secondary
Processing → badge-info
Completed → badge-success
Failed → badge-danger
Partially Completed → badge-warning

<!-- Audit Score -->
>= 90 → badge-success
>= 70 → badge-warning
< 70 → badge-danger

<!-- Audit Status -->
Passed → badge-success
Partial → badge-warning
Failed → badge-danger

<!-- Certification Status -->
Inspection Ready → badge-success
Review Required → badge-warning
Not Certified → badge-secondary
```

## Controller Methods to Add

Add these routes to `routes/compliance.php`:

```php
Route::post('/batches/{batch}/forms/{form}/fix', 'ComplianceExecutionController@fixViolations');
Route::post('/batches/{batch}/forms/{form}/fix-submit', 'ComplianceExecutionController@submitFix');
Route::get('/batches/{batch}/inspection-pack', 'ComplianceExecutionController@downloadInspectionPack')->name('compliance.inspection-pack');
Route::get('/batches/{batch}/violations', 'ComplianceExecutionController@getViolations');
Route::get('/batches/{batch}/preview', 'ComplianceExecutionController@getPreview');
```

## New Controller Methods

Add to `ComplianceExecutionController`:

```php
public function getViolations(int $batchId)
{
    $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
        ->where('id', $batchId)
        ->firstOrFail();

    $violations = ComplianceAuditLog::where('batch_id', $batchId)
        ->where('status', 'failed')
        ->get(['form_code', 'violations']);

    return response()->json([
        'forms' => $violations->map(fn($v) => [
            'form_code' => $v->form_code,
            'violations' => $v->violations
        ])
    ]);
}

public function getPreview(int $batchId)
{
    $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
        ->where('id', $batchId)
        ->firstOrFail();

    $forms = ComplianceBatchForm::where('batch_id', $batchId)
        ->where('status', 'success')
        ->get();

    return view('compliance.preview', compact('batch', 'forms'));
}
```

## Summary

The dashboard now displays:
- ✅ Batch ID, Section, Period, Status
- ✅ Audit Score with color coding
- ✅ Audit Status (Passed/Partial/Failed)
- ✅ Certification Status (Inspection Ready/Review Required/Not Certified)
- ✅ Preview button
- ✅ Fix Issues button (conditional on violations)
- ✅ Inspection Pack button (conditional on certification score >= 70)
- ✅ Created date

All actions trigger the appropriate backend workflows automatically.
