<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Compliance Engine Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Compliance Engine Dashboard</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Create Compliance Batch</h5>
                
                <div class="mb-3">
                    <label for="sectionSelect" class="form-label">Select Section</label>
                    <select class="form-select" id="sectionSelect">
                        <option value="">-- Select Section --</option>
                    </select>
                </div>

                <div class="mb-3" id="formsContainer" style="display:none;">
                    <label class="form-label">Select Forms</label>
                    <div id="formsList"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="periodFrom" class="form-label">Period From</label>
                        <input type="date" class="form-control" id="periodFrom">
                    </div>
                    <div class="col-md-6">
                        <label for="periodTo" class="form-label">Period To</label>
                        <input type="date" class="form-control" id="periodTo">
                    </div>
                </div>

                <button class="btn btn-primary" id="createBatchBtn" onclick="createBatch()">
                    <span id="createSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    Create Batch
                </button>
            </div>
        </div>

        <div class="card mb-4 d-none" id="batchInfoCard">
            <div class="card-body">
                <h5 class="card-title">Batch Information</h5>
                <div id="batchInfo"></div>
                <div class="mt-3">
                    <button class="btn btn-success me-2" id="processBatchBtn" onclick="processBatch()">
                        <span id="processSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Process Batch
                    </button>
                    <button class="btn btn-info d-none" id="downloadBtn" onclick="downloadReport()">
                        Download Report
                    </button>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>
    </div>

    <script>
        let currentBatchId = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showAlert(message, type = 'danger') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.getElementById('alertContainer').appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000);
        }

        async function loadSections() {
            try {
                const response = await fetch('/compliance/sections');
                const sections = await response.json();
                const select = document.getElementById('sectionSelect');
                sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.section_name;
                    select.appendChild(option);
                });
            } catch (error) {
                showAlert('Failed to load sections: ' + error.message);
            }
        }

        async function loadForms(sectionId) {
            try {
                const response = await fetch(`/compliance/forms/${sectionId}`);
                const forms = await response.json();
                const formsList = document.getElementById('formsList');
                formsList.innerHTML = '';
                
                forms.forEach(form => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" value="${form.id}" id="form${form.id}">
                        <label class="form-check-label" for="form${form.id}">
                            ${form.form_code} - ${form.form_name}
                        </label>
                    `;
                    formsList.appendChild(div);
                });
                
                document.getElementById('formsContainer').style.display = 'block';
            } catch (error) {
                showAlert('Failed to load forms: ' + error.message);
            }
        }

        document.getElementById('sectionSelect').addEventListener('change', function() {
            if (this.value) {
                loadForms(this.value);
            } else {
                document.getElementById('formsContainer').style.display = 'none';
            }
        });

        async function createBatch() {
            const sectionId = document.getElementById('sectionSelect').value;
            const periodFrom = document.getElementById('periodFrom').value;
            const periodTo = document.getElementById('periodTo').value;
            const formCheckboxes = document.querySelectorAll('#formsList input[type="checkbox"]:checked');
            const formIds = Array.from(formCheckboxes).map(cb => parseInt(cb.value));

            if (!sectionId || !periodFrom || !periodTo || formIds.length === 0) {
                showAlert('Please fill all fields and select at least one form');
                return;
            }

            const btn = document.getElementById('createBatchBtn');
            const spinner = document.getElementById('createSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const response = await fetch('/compliance/batch/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        section_id: parseInt(sectionId),
                        period_from: periodFrom,
                        period_to: periodTo,
                        form_ids: formIds
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    currentBatchId = data.batch_id;
                    document.getElementById('batchInfo').innerHTML = `
                        <p><strong>Batch ID:</strong> ${data.batch_id}</p>
                        <p><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                    `;
                    document.getElementById('batchInfoCard').classList.remove('d-none');
                    showAlert('Batch created successfully!', 'success');
                } else {
                    showAlert('Error: ' + (data.message || 'Failed to create batch'));
                }
            } catch (error) {
                showAlert('Failed to create batch: ' + error.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        async function processBatch() {
            if (!currentBatchId) return;

            const btn = document.getElementById('processBatchBtn');
            const spinner = document.getElementById('processSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const response = await fetch(`/compliance/batch/process/${currentBatchId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    document.getElementById('batchInfo').innerHTML = `
                        <p><strong>Batch ID:</strong> ${currentBatchId}</p>
                        <p><strong>Status:</strong> <span class="badge bg-success">Completed</span></p>
                        <p><strong>Results:</strong> ${JSON.stringify(data.results, null, 2)}</p>
                    `;
                    document.getElementById('downloadBtn').classList.remove('d-none');
                    showAlert('Batch processed successfully!', 'success');
                } else {
                    showAlert('Error: ' + (data.message || 'Failed to process batch'));
                }
            } catch (error) {
                showAlert('Failed to process batch: ' + error.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        function downloadReport() {
            if (!currentBatchId) return;
            window.location = `/compliance/batch/${currentBatchId}/download`;
        }

        // Load sections on page load
        loadSections();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
