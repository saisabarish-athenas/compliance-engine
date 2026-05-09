@extends('compliance.layouts.antd_base')

@section('title', 'Upload Compliance Data')

@section('content')

{{-- Flash errors from server-side redirect (non-AJAX fallback) --}}
@if ($errors->any())
<div class="ant-alert ant-alert-error mb-3">
    <strong>Upload failed:</strong>
    <ul style="margin:8px 0 0 16px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="ant-card">
    <div class="ant-card-head">📂 Upload Compliance Datasets</div>
    <div class="ant-card-body">

        {{-- Result banner (shown after AJAX response) --}}
        <div id="uploadResult" class="ant-alert mb-3" style="display:none;"></div>

        <form id="csvUploadForm" enctype="multipart/form-data">
            @csrf

            {{-- Period --}}
            <div class="ant-row mb-3">
                <div class="ant-col ant-col-6">
                    <div class="ant-form-item">
                        <label class="ant-form-item-label">Period From <span style="color:#ff4d4f">*</span></label>
                        <input type="date" name="period_from" class="ant-input" required>
                    </div>
                </div>
                <div class="ant-col ant-col-6">
                    <div class="ant-form-item">
                        <label class="ant-form-item-label">Period To <span style="color:#ff4d4f">*</span></label>
                        <input type="date" name="period_to" class="ant-input" required>
                    </div>
                </div>
            </div>

            {{-- File inputs --}}
            <div class="ant-row mb-3">

                {{-- Employees --}}
                <div class="ant-col ant-col-4">
                    <div class="ant-card" style="border:2px dashed #d9d9d9;" id="card-employees">
                        <div class="ant-card-head secondary">👥 Employees CSV</div>
                        <div class="ant-card-body">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">
                                    employees.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="employees_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-employees" required
                                       style="width:100%;padding:6px 0;">
                            </div>
                            <div class="text-muted" style="font-size:12px;line-height:1.6;">
                                Required columns:<br>
                                <code>employee_code, name</code><br>
                                Optional:<br>
                                <code>designation, department, uan, esi,<br>basic_salary, date_of_joining</code>
                            </div>
                            <div class="file-status mt-3" id="status-employees"></div>
                        </div>
                    </div>
                </div>

                {{-- Payroll --}}
                <div class="ant-col ant-col-4">
                    <div class="ant-card" style="border:2px dashed #d9d9d9;" id="card-payroll">
                        <div class="ant-card-head secondary">💰 Payroll CSV</div>
                        <div class="ant-card-body">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">
                                    payroll.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="payroll_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-payroll" required
                                       style="width:100%;padding:6px 0;">
                            </div>
                            <div class="text-muted" style="font-size:12px;line-height:1.6;">
                                Required columns:<br>
                                <code>employee_code, gross_salary, net_salary</code><br>
                                Optional:<br>
                                <code>basic_salary, da, hra, pf_employee,<br>esi_employee, professional_tax,<br>working_days, payment_date</code>
                            </div>
                            <div class="file-status mt-3" id="status-payroll"></div>
                        </div>
                    </div>
                </div>

                {{-- Attendance --}}
                <div class="ant-col ant-col-4">
                    <div class="ant-card" style="border:2px dashed #d9d9d9;" id="card-attendance">
                        <div class="ant-card-head secondary">📅 Attendance CSV</div>
                        <div class="ant-card-body">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">
                                    attendance.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="attendance_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-attendance" required
                                       style="width:100%;padding:6px 0;">
                            </div>
                            <div class="text-muted" style="font-size:12px;line-height:1.6;">
                                Required columns:<br>
                                <code>employee_code, working_days</code><br>
                                Optional:<br>
                                <code>absent, attendance_date, status</code>
                            </div>
                            <div class="file-status mt-3" id="status-attendance"></div>
                        </div>
                    </div>
                </div>

            </div>{{-- /ant-row --}}

            {{-- CSV format reference --}}
            <div class="ant-card mb-3" style="background:#fafafa;">
                <div class="ant-card-head info">📋 Sample CSV Formats</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-4">
                            <strong style="font-size:13px;">employees.csv</strong>
                            <pre style="font-size:11px;margin-top:6px;background:#fff;padding:8px;border-radius:4px;border:1px solid #f0f0f0;">employee_code,name,designation,uan,esi
MAK001,ARUMUGAM S,Supervisor,UAN001,ESI001
MAK002,BALAMURUGAN K,Technician,UAN002,ESI002</pre>
                        </div>
                        <div class="ant-col ant-col-4">
                            <strong style="font-size:13px;">payroll.csv</strong>
                            <pre style="font-size:11px;margin-top:6px;background:#fff;padding:8px;border-radius:4px;border:1px solid #f0f0f0;">employee_code,gross_salary,net_salary,pf_employee,esi_employee,professional_tax
MAK001,10308,9371,660,96,181
MAK002,10308,9371,660,96,181</pre>
                        </div>
                        <div class="ant-col ant-col-4">
                            <strong style="font-size:13px;">attendance.csv</strong>
                            <pre style="font-size:11px;margin-top:6px;background:#fff;padding:8px;border-radius:4px;border:1px solid #f0f0f0;">employee_code,working_days,absent
MAK001,27,0
MAK002,27,0</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <button type="submit" class="ant-btn ant-btn-primary" id="submitBtn">
                    ⬆️ Upload All Datasets
                </button>
                <span id="uploadSpinner" style="display:none;">
                    <span class="spinner"></span>&nbsp;Processing…
                </span>
                <a href="{{ route('compliance.dashboard') }}" class="ant-btn">← Back to Dashboard</a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── File picker feedback ──────────────────────────────────────────────────────
document.querySelectorAll('.csv-input').forEach(function (input) {
    input.addEventListener('change', function () {
        const cardId  = this.dataset.card;
        const type    = cardId.replace('card-', '');
        const statusEl = document.getElementById('status-' + type);
        const card    = document.getElementById(cardId);

        if (this.files.length) {
            const file = this.files[0];
            const kb   = (file.size / 1024).toFixed(1);
            statusEl.innerHTML = `<span class="ant-tag ant-tag-success">✓ ${file.name} (${kb} KB)</span>`;
            card.style.borderColor = '#52c41a';
        } else {
            statusEl.innerHTML = '';
            card.style.borderColor = '#d9d9d9';
        }
    });
});

// ── Form submission ───────────────────────────────────────────────────────────
document.getElementById('csvUploadForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn     = document.getElementById('submitBtn');
    const spinner = document.getElementById('uploadSpinner');
    const result  = document.getElementById('uploadResult');

    btn.disabled    = true;
    spinner.style.display = 'inline-flex';
    result.style.display  = 'none';

    try {
        const resp = await fetch('{{ route("data.upload-multi") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new FormData(this),
        });

        let json;
        const rawText = await resp.text();
        try {
            json = JSON.parse(rawText);
        } catch (_) {
            // Server returned HTML (e.g. session expired or unexpected error)
            result.className = 'ant-alert ant-alert-error mb-3';
            result.innerHTML = `<strong>❌ Server error (${resp.status}):</strong> Unexpected response. Check server logs.`;
            return;
        }

        if (json.status === 'success') {
            const c = json.counts;
            result.className = 'ant-alert ant-alert-success mb-3';
            result.innerHTML = `
                <strong>✅ ${json.message}</strong><br>
                <small>
                    Employees: ${c.employees} &nbsp;|&nbsp;
                    Payroll: ${c.payroll} &nbsp;|&nbsp;
                    Attendance: ${c.attendance}
                </small>`;
            this.reset();
            document.querySelectorAll('.file-status').forEach(el => el.innerHTML = '');
            document.querySelectorAll('[id^="card-"]').forEach(el => el.style.borderColor = '#d9d9d9');
        } else {
            // Surface Laravel validation errors (errors object) or plain message
            let msg = json.message ?? 'Upload failed.';
            if (json.errors) {
                const lines = Object.values(json.errors).flat();
                msg = lines.join('<br>');
            }
            result.className = 'ant-alert ant-alert-error mb-3';
            result.innerHTML = `<strong>❌ Upload failed:</strong><br>${msg}`;
        }

    } catch (err) {
        result.className = 'ant-alert ant-alert-error mb-3';
        result.innerHTML = `<strong>❌ Network error:</strong> ${err.message}`;
    } finally {
        result.style.display  = 'block';
        btn.disabled          = false;
        spinner.style.display = 'none';
        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>
@endpush
