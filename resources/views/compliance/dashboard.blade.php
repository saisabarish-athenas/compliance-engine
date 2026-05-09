@extends('compliance.layouts.antd_base')

@section('title', 'Dashboard - Compliance Engine')

@section('content')
    @if (isset($subscription))
        <div class="ant-card">
            <div class="ant-card-head">🏢 Organization Information</div>
            <div class="ant-card-body">
                <div class="ant-row">
                    <div class="ant-col ant-col-4">
                        <p class="mb-2"><strong>Organization:</strong><br>{{ Auth::user()->tenant->name ?? 'N/A' }}</p>
                        <p class="mb-2">
                            <strong>Subscription:</strong><br>
                            <span class="ant-tag {{ $subscription === 'FULL' ? 'ant-tag-success' : 'ant-tag-default' }}">
                                {{ $subscription }}
                            </span>
                        </p>
                    </div>
                    <div class="ant-col ant-col-4">
                        @if (isset($branch))
                            <p class="mb-2"><strong>Branch:</strong><br>{{ $branch->branch_name ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>License No:</strong><br>{{ $branch->factory_license_number ?? '-' }}
                            </p>
                        @else
                            <p class="mb-2 text-muted">No branch assigned</p>
                        @endif
                    </div>
                    <div class="ant-col ant-col-4">
                        @if (isset($branch))
                            <p class="mb-2"><strong>PF Code:</strong><br>{{ $branch->pf_code ?? '-' }}</p>
                            <p class="mb-2"><strong>ESI Code:</strong><br>{{ $branch->esi_code ?? '-' }}</p>
                        @endif
                        <p class="mb-2"><strong>Logged in as:</strong><br>{{ $user->name ?? Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($healthScore))
        <div class="ant-card">
            <div
                class="ant-card-head {{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">
                💚 Compliance Health Score</div>
            <div class="ant-card-body">
                <div class="ant-row align-items-center">
                    <div class="ant-col ant-col-4 text-center">
                        <h1
                            style="font-size: 48px; margin: 0; color: {{ $healthScore['status'] === 'Excellent' ? '#52c41a' : ($healthScore['status'] === 'Good' ? '#faad14' : '#ff4d4f') }};">
                            {{ $healthScore['percentage'] }}%</h1>
                        <span
                            class="ant-tag {{ $healthScore['status'] === 'Excellent' ? 'ant-tag-success' : ($healthScore['status'] === 'Good' ? 'ant-tag-warning' : 'ant-tag-error') }}"
                            style="font-size: 16px; padding: 6px 16px;">{{ $healthScore['status'] }}</span>
                    </div>
                    <div class="ant-col ant-col-8">
                        <p class="mb-2"><strong>Score Breakdown:</strong></p>
                        <ul style="list-style: none; padding: 0;">
                            @foreach ($healthScore['breakdown'] as $metric => $score)
                                <li class="mb-2">
                                    <span
                                        class="ant-tag {{ $score >= 18 ? 'ant-tag-success' : ($score >= 10 ? 'ant-tag-warning' : 'ant-tag-error') }}"
                                        style="margin-right: 8px;">{{ $score }}%</span>
                                    {{ $metric }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($timelineMetrics))
        <div class="ant-card">
            <div class="ant-card-head info">📅 Compliance Timeline Status</div>
            <div class="ant-card-body">
                <div class="ant-row text-center">
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #8c8c8c; margin: 0;">{{ $timelineMetrics['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #faad14; margin: 0;">{{ $timelineMetrics['pending'] }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #1890ff; margin: 0;">{{ $timelineMetrics['generated'] }}</h4>
                        <small class="text-muted">Generated</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #52c41a; margin: 0;">{{ $timelineMetrics['filed'] }}</h4>
                        <small class="text-muted">Filed</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #ff4d4f; margin: 0;">{{ $timelineMetrics['overdue'] }}</h4>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($subscription) && $subscription === 'MINIMAL')
        <div class="ant-alert ant-alert-warning">
            <strong>⚠️ MINIMAL Subscription:</strong> Enter statutory data manually to auto-generate forms. Upgrade to FULL
            for complete automation.
        </div>
    @endif

    @if (session('success'))
        <div class="ant-alert ant-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ant-alert ant-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="ant-alert ant-alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ant-row">
        <div class="ant-col ant-col-6">
            <div class="ant-card">
                <div class="ant-card-head">📋 Create Compliance Batch</div>
                <div class="ant-card-body">
                    <form method="POST" action="{{ route('compliance.batch.create') }}" id="batchForm">
                        @csrf
                        <div class="ant-form-item">
                            <label for="statutory_section" class="ant-form-item-label">Select Statutory Section</label>
                            <select class="ant-select" id="statutory_section" name="statutory_section" required>
                                <option value="">-- Select Section --</option>
                                @foreach ($statutorySections as $key => $section)
                                    <option value="{{ $key }}">{{ $section['icon'] }} {{ $section['title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ant-form-item" id="formsContainer" style="display:none;">
                            <label class="ant-form-item-label">Select Forms</label>
                            <div class="mb-2">
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="selectAllForms" class="ant-checkbox">
                                    <strong>Select All Forms</strong>
                                </label>
                            </div>
                            <div id="formsList"
                                style="border: 1px solid #d9d9d9; border-radius: 6px; padding: 16px; background: #fafafa; max-height: 300px; overflow-y: auto;">
                                <p class="text-muted" style="margin: 0;">Loading forms...</p>
                            </div>
                        </div>
                        <div class="ant-row">
                            <div class="ant-col ant-col-6">
                                <div class="ant-form-item">
                                    <label for="period_month" class="ant-form-item-label">Month</label>
                                    <select class="ant-select" id="period_month" name="period_month" required>
                                        <option value="">-- Select Month --</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ant-col ant-col-6">
                                <div class="ant-form-item">
                                    <label for="period_year" class="ant-form-item-label">Year</label>
                                    <select class="ant-select" id="period_year" name="period_year" required>
                                        <option value="">-- Select Year --</option>
                                        @for ($year = date('Y') - 2; $year <= date('Y') + 3; $year++)
                                            <option value="{{ $year }}"
                                                {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="ant-btn ant-btn-primary w-100" id="createBatchBtn">
                            <span id="submitSpinner" class="spinner d-none"></span>
                            Create Batch
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="ant-col ant-col-6">
            @if (session('batch_id'))
                <div class="ant-card" style="border: 2px solid #52c41a;">
                    <div class="ant-card-head success">✅ Batch Created</div>
                    <div class="ant-card-body">
                        <p><strong>Batch ID:</strong> {{ session('batch_id') }}</p>
                        @if (session('results'))
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-success">Completed</span></p>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('compliance.batch.download', session('batch_id')) }}"
                                    class="ant-btn ant-btn-info">📥 Download Report</a>
                                <a href="{{ route('compliance.batch.inspectionPack', session('batch_id')) }}"
                                    class="ant-btn ant-btn-primary">📦 Consolidated Reports</a>
                            </div>
                        @elseif(isset($subscription) && $subscription === 'MINIMAL')
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-warning" id="batchStatus">Awaiting
                                    Data Entry</span></p>
                            <div class="mt-3">
                                <a href="{{ route('compliance.manual-data.show', ['month' => session('period_month', date('n')), 'year' => session('period_year', date('Y'))]) }}"
                                    class="ant-btn ant-btn-primary w-100" target="_blank">
                                    📝 Step 1: Enter Statutory Data
                                </a>
                            </div>
                            <div id="previewFormsSection" class="mt-3 mb-3">
                                <p class="text-muted">Step 2: Preview forms with entered data:</p>
                                <div id="previewFormsList"></div>
                            </div>
                            <div class="mt-3">
                                <form method="POST"
                                    action="{{ route('compliance.batch.process', session('batch_id')) }}">
                                    @csrf
                                    <button type="submit" class="ant-btn ant-btn-success w-100">⚙️ Step 3: Generate
                                        Forms</button>
                                </form>
                            </div>
                        @else
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-warning">Pending</span></p>
                            @if (isset($subscription) && $subscription === 'FULL')
                                <div id="previewFormsSection" class="mt-3 mb-3">
                                    <p class="text-muted">Preview forms before processing:</p>
                                    <div id="previewFormsList"></div>
                                </div>
                                <form method="POST"
                                    action="{{ route('compliance.batch.process', session('batch_id')) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="ant-btn ant-btn-success w-100">⚙️ Process Batch</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="ant-card">
                <div class="ant-card-head info">📊 Quick Stats</div>
                <div class="ant-card-body">
                    <div class="ant-row text-center">
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #1890ff; margin: 0;">{{ count($sections) }}</h3>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #52c41a; margin: 0;">{{ count($batches) }}</h3>
                            <small class="text-muted">Batches</small>
                        </div>
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #13c2c2; margin: 0;">
                                {{ collect($batches)->filter(fn($b) => in_array($b->display_status ?? $b->status, ['Completed', 'completed']))->count() }}
                            </h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ant-card mt-4">
        <div class="ant-card-head secondary">📜 Recent Batches</div>
        <div class="ant-card-body">
            @if (count($batches) > 0)
                <div style="overflow-x: auto;">
                    <table class="ant-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Section</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Audit Score</th>
                                <th>Certification</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batches as $batch)
                                <tr data-batch-id="{{ $batch->id }}">
                                    <td><strong>#{{ $batch->id }}</strong></td>
                                    <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($batch->period_month && $batch->period_year)
                                            <small>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</small>
                                        @else
                                            <small>{{ \Carbon\Carbon::parse($batch->period_from)->format('M d') }} -
                                                {{ \Carbon\Carbon::parse($batch->period_to)->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mb-1"><span class="ant-tag ant-tag-success">Completed</span></div>
                                        @if(isset($batch->audit_status))
                                            <div><span class="ant-tag {{ $batch->audit_status === 'Passed' ? 'ant-tag-success' : ($batch->audit_status === 'Partial' ? 'ant-tag-warning' : ($batch->audit_status === 'Not Audited' ? 'ant-tag-default' : 'ant-tag-error')) }}">Audit: {{ $batch->audit_status }}</span></div>
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($batch->audit_score))
                                            <span
                                                class="ant-tag batch-score-badge {{ $batch->audit_score >= 90 ? 'ant-tag-success' : ($batch->audit_score >= 70 ? 'ant-tag-warning' : 'ant-tag-error') }}"
                                                style="font-weight: bold;">
                                                {{ $batch->audit_score }}/100
                                            </span>
                                            <br>
                                            <button class="ant-btn ant-btn-sm ant-btn-default mt-1" data-bs-toggle="modal"
                                                data-bs-target="#auditModal{{ $batch->id }}">
                                                👁️ View
                                            </button>
                                        @else
                                            <span class="ant-tag ant-tag-default">Not Audited</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($batch->certification_status)
                                            <span class="ant-tag {{ $batch->certification_status === 'Certified' ? 'ant-tag-success' : 'ant-tag-error' }}">
                                                {{ $batch->certification_status }}
                                            </span>
                                        @else
                                            <button class="ant-btn ant-btn-sm ant-btn-primary certify-btn" data-batch="{{ $batch->id }}">
                                                Certify
                                            </button>
                                        @endif
                                    </td>
                                    <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <div class="d-flex gap-2">

                                            {{-- Download Report --}}
                                            <a href="{{ route('compliance.batch.download', $batch->id) }}"
                                                class="ant-btn ant-btn-sm ant-btn-info">
                                                📥 Download
                                            </a>

                                            {{-- Inspection Pack (FULL only) --}}
                                            @if (($batch->audit_score ?? 0) >= 70 || $batch->certification_status === 'Certified')
                                            <a href="{{ route('compliance.batch.inspectionPack', $batch->id) }}"
                                                class="ant-btn ant-btn-sm ant-btn-primary">
                                                📦 CONSOLIDATED REPORTS
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center" style="padding: 32px 0;">No batches created yet. Create your first batch
                    above!</p>
            @endif
        </div>
    </div>

    {{-- Audit Details Modals --}}
    @foreach ($batches as $batch)
        @if (isset($batch->audit_score))
            <div class="modal fade" id="auditModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #f0f2f5; border-bottom: 2px solid #d9d9d9;">
                            <h5 class="modal-title"><strong>🔍 Audit Details - Batch #{{ $batch->id }}</strong></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4">
                                <h4 style="margin: 0 0 8px 0;">Audit Score: <strong>{{ $batch->audit_score }}/100</strong>
                                </h4>

                                @php
                                    $confidenceLabel =
                                        $batch->audit_score >= 90
                                            ? 'Inspection Ready'
                                            : ($batch->audit_score >= 70
                                                ? 'Moderate Risk – Review Recommended'
                                                : 'High Risk – Immediate Correction Required');
                                    $confidenceClass =
                                        $batch->audit_score >= 90
                                            ? 'success'
                                            : ($batch->audit_score >= 70
                                                ? 'warning'
                                                : 'danger');
                                @endphp

                                <span class="badge bg-{{ $confidenceClass }}"
                                    style="font-size: 14px; padding: 8px 12px; margin-bottom: 12px;">
                                    {{ $confidenceLabel }}
                                </span>

                                <div class="progress" style="height: 30px; margin-top: 12px;">
                                    <div class="progress-bar bg-{{ $confidenceClass }}" role="progressbar"
                                        style="width: {{ $batch->audit_score }}%; font-weight: bold; font-size: 16px;"
                                        aria-valuenow="{{ $batch->audit_score }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $batch->audit_score }}%
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3"><strong>📋 Form-wise Audit Breakdown</strong></h5>

                            @if ($batch->audit_logs->isNotEmpty())
                                <div class="list-group">
                                    @foreach ($batch->audit_logs as $log)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong>{{ $log->form_code }}</strong>
                                                    <span
                                                        class="badge bg-{{ $log->status === 'passed' ? 'success' : 'danger' }} ms-2">
                                                        {{ ucfirst($log->status) }}
                                                    </span>
                                                </div>
                                                <span class="badge bg-secondary">Score: {{ $log->audit_score }}/100</span>
                                            </div>

                                            @if ($log->violations && count($log->violations) > 0)
                                                <div class="mt-2">
                                                    <strong class="text-danger">⚠️ Violations:</strong>
                                                    <ul class="mb-2" style="margin-top: 8px;">
                                                        @foreach ($log->violations as $violation)
                                                            <li>
                                                                <small>
                                                                    <strong>{{ $violation['field'] ?? 'Unknown' }}</strong>
                                                                    ({{ $violation['type'] ?? 'general' }})
                                                                    :
                                                                    {{ $violation['message'] ?? 'No details' }}
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    @if ($log->status === 'failed')
                                                        <button class="btn btn-sm btn-danger re-audit-btn"
                                                            data-batch="{{ $batch->id }}"
                                                            data-form="{{ $log->form_code }}">
                                                            🔧 Fix & Re-Audit
                                                        </button>
                                                        <a href="{{ route('compliance.batch.preview', ['batch' => $batch->id, 'form' => $log->form_code]) }}"
                                                            class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
                                                            👁️ Preview
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <small class="text-success">✅ No violations detected</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No audit logs available for this batch.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('scripts')
    <script>
        const statutorySections = @json($statutorySections);
        const formCodeToId = @json($formCodeToId);

        document.getElementById('statutory_section').addEventListener('change', function() {
            const sectionKey = this.value;
            const formsContainer = document.getElementById('formsContainer');
            const formsList = document.getElementById('formsList');

            if (!sectionKey) {
                formsContainer.style.display = 'none';
                return;
            }

            formsContainer.style.display = 'block';
            const section = statutorySections[sectionKey];
            const forms = section.forms;

            formsList.innerHTML = '';
            Object.entries(forms).forEach(([formCode, formName]) => {
                const formId = formCodeToId[formCode];
                if (formId) {
                    const div = document.createElement('div');
                    div.className = 'mb-2';
                    div.innerHTML = `
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input class="ant-checkbox form-checkbox" type="checkbox" name="form_ids[]" value="${formId}" id="form${formId}">
                            <span>
                                <strong>${formCode}</strong> - ${formName}
                            </span>
                        </label>
                    `;
                    formsList.appendChild(div);
                }
            });
        });

        document.getElementById('batchForm').addEventListener('submit', function() {
            document.getElementById('submitSpinner').classList.remove('d-none');
        });

        document.getElementById('selectAllForms').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.form-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        @if (session('batch_id') && isset($subscription) && in_array($subscription, ['MINIMAL', 'FULL']) && !session('results'))

            const previewBatchId = {{ session('batch_id') }};
            const previewFormIds = @json(session('form_ids', []));
            const previewList = document.getElementById('previewFormsList');

            if (previewFormIds.length > 0) {

                previewFormIds.forEach(function(formId) {

                    const formCode = Object.keys(formCodeToId).find(
                        key => formCodeToId[key] == formId
                    );

                    if (formCode) {

                        const div = document.createElement('div');
                        div.className = 'mb-2';

                        div.innerHTML = `
                <a href="/compliance/batch/${previewBatchId}/preview/${formCode}"
                   class="ant-btn ant-btn-primary w-100"
                   target="_blank"
                   style="display:block;text-align:center;">
                   👁️ Preview ${formCode}
                </a>
            `;

                        previewList.appendChild(div);

                    }

                });

            }
        @endif

        // Fix violations functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('re-audit-btn') || e.target.closest('.re-audit-btn')) {
                const btn = e.target.classList.contains('re-audit-btn') ? e.target : e.target.closest(
                    '.re-audit-btn');
                const batchId = btn.dataset.batch;
                const formCode = btn.dataset.form;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fixing...';

                fetch(`/compliance/batch/${batchId}/fix-violations/${formCode}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'requires_input') {
                            // Show modal for missing fields
                            showFixModal(batchId, formCode, data.missing_fields, btn);
                        } else if (data.status === 'success') {
                            updateAuditUI(batchId, formCode, data, btn);
                            alert('✅ Form corrected successfully!');
                        } else if (data.status === 'no_violations') {
                            alert('ℹ️ No violations found for this form.');
                            btn.disabled = false;
                            btn.innerHTML = '🔧 Fix & Re-Audit';
                        } else {
                            alert('❌ Fix failed: ' + (data.message || 'Unknown error'));
                            btn.disabled = false;
                            btn.innerHTML = '🔧 Fix & Re-Audit';
                        }
                    })
                    .catch(err => {
                        alert('❌ Error: ' + err.message);
                        btn.disabled = false;
                        btn.innerHTML = '🔧 Fix & Re-Audit';
                    });
            }
        });

        function showFixModal(batchId, formCode, missingFields, originalBtn) {
            // Create modal dynamically
            const modalId = `fixModal_${batchId}_${formCode}`;
            let modal = document.getElementById(modalId);

            if (!modal) {
                modal = document.createElement('div');
                modal.id = modalId;
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">🔧 Fix Violations - ${formCode}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted">Please provide the following missing information:</p>
                                <form id="fixForm_${batchId}_${formCode}">
                                    ${missingFields.map((field, idx) => `
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <strong>${field.field}</strong>
                                                    <small class="text-muted d-block">${field.message}</small>
                                                </label>
                                                <input type="text" class="form-control" name="${field.field}" required>
                                            </div>
                                        `).join('')}
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitFix_${batchId}_${formCode}">Submit & Regenerate</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Handle form submission
            document.getElementById(`submitFix_${batchId}_${formCode}`).onclick = function() {
                const form = document.getElementById(`fixForm_${batchId}_${formCode}`);
                const formData = new FormData(form);
                const corrections = {};

                for (let [key, value] of formData.entries()) {
                    corrections[key] = value;
                }

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

                fetch(`/compliance/batch/${batchId}/submit-fix/${formCode}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            corrections
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            bsModal.hide();
                            updateAuditUI(batchId, formCode, data, originalBtn);
                            alert('✅ Form corrected and regenerated successfully!');
                        } else {
                            alert('❌ Fix failed: ' + (data.message || 'Unknown error'));
                            this.disabled = false;
                            this.innerHTML = 'Submit & Regenerate';
                        }
                    })
                    .catch(err => {
                        alert('❌ Error: ' + err.message);
                        this.disabled = false;
                        this.innerHTML = 'Submit & Regenerate';
                    });
            };

            originalBtn.disabled = false;
            originalBtn.innerHTML = '🔧 Fix & Re-Audit';
        }

        function updateAuditUI(batchId, formCode, data, btn) {
            const modal = btn.closest('.modal-body');
            const listItem = btn.closest('.list-group-item');

            // 1. Update batch average score in modal header
            const modalHeader = modal.querySelector('h4');
            if (modalHeader) {
                modalHeader.innerHTML = `Audit Score: <strong>${data.batch_average_score}/100</strong>`;
            }

            // 2. Update progress bar
            const progressBar = modal.querySelector('.progress-bar');
            if (progressBar) {
                const barClass = data.batch_average_score >= 90 ? 'bg-success' : (data.batch_average_score >= 70 ?
                    'bg-warning' : 'bg-danger');
                progressBar.className = `progress-bar ${barClass}`;
                progressBar.style.width = `${data.batch_average_score}%`;
                progressBar.textContent = `${data.batch_average_score}%`;
                progressBar.setAttribute('aria-valuenow', data.batch_average_score);
            }

            // 3. Update legal confidence badge
            const confidenceBadge = modal.querySelector('.badge');
            if (confidenceBadge) {
                const badgeClass = data.batch_average_score >= 90 ? 'bg-success' : (data.batch_average_score >= 70 ?
                    'bg-warning' : 'bg-danger');
                confidenceBadge.className = `badge ${badgeClass}`;
                confidenceBadge.style.fontSize = '14px';
                confidenceBadge.style.padding = '8px 12px';
                confidenceBadge.textContent = data.confidence_label;
            }

            // 4. Update form status badge
            const statusBadge = listItem.querySelector('.badge');
            if (statusBadge) {
                statusBadge.className = `badge ${data.audit_status === 'passed' ? 'bg-success' : 'bg-danger'} ms-2`;
                statusBadge.textContent = data.audit_status.charAt(0).toUpperCase() + data.audit_status.slice(1);
            }

            // 5. Update form score
            const scoreBadge = listItem.querySelector('.badge.bg-secondary');
            if (scoreBadge) {
                scoreBadge.textContent = `Score: ${data.form_score}/100`;
            }

            // 6. Update violations section
            const violationsDiv = listItem.querySelector('.mt-2');
            if (data.violations.length === 0) {
                if (violationsDiv) {
                    violationsDiv.innerHTML = '<small class="text-success">✅ No violations detected</small>';
                }
            } else {
                const violationsList = listItem.querySelector('ul');
                if (violationsList) {
                    violationsList.innerHTML = data.violations.map(v => `
                        <li>
                            <small>
                                <strong>${v.field || 'Unknown'}</strong>
                                (${v.type || 'general'}):
                                ${v.message || 'No details'}
                            </small>
                        </li>
                    `).join('');
                }
                btn.disabled = false;
                btn.innerHTML = '🔧 Fix & Re-Audit';
            }

            // 7. Update table score badge
            const tableRow = document.querySelector(`tr[data-batch-id="${batchId}"]`);
            if (tableRow) {
                const scoreCell = tableRow.querySelector('.batch-score-badge');
                if (scoreCell) {
                    const scoreClass = data.batch_average_score >= 90 ? 'ant-tag-success' : (data.batch_average_score >=
                        70 ? 'ant-tag-warning' : 'ant-tag-error');
                    scoreCell.className = `ant-tag batch-score-badge ${scoreClass}`;
                    scoreCell.style.fontWeight = 'bold';
                    scoreCell.textContent = `${data.batch_average_score}/100`;
                }
            }
        }

        // Certification logic
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('certify-btn') || e.target.closest('.certify-btn')) {
                const btn = e.target.classList.contains('certify-btn') ? e.target : e.target.closest('.certify-btn');
                const batchId = btn.dataset.batch;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Certifying...';

                fetch(`/compliance/batch/${batchId}/certify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.certified) {
                            alert('✅ Certification Successful! Score: ' + data.score + '/100');
                        } else {
                            alert('❌ Certification Failed. Score: ' + data.score + '/100. Violations found.');
                        }
                        window.location.reload();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Validation Failed'));
                        btn.disabled = false;
                        btn.innerHTML = 'Certify';
                    }
                })
                .catch(err => {
                    alert('❌ Error: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Certify';
                });
            }
        });
    </script>
@endpush
