<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/*
 | Table name map (wrong → correct)
 |   employees  → workforce_employee
 |   payroll    → workforce_payroll_entry   (requires payroll_cycle_id)
 |   attendance → workforce_attendance
 */

class DataInputController extends Controller
{
    public function saveManualData(Request $request, int $batchId)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'employees_data' => 'nullable|string',
                'payroll_data' => 'nullable|string',
                'attendance_data' => 'nullable|string'
            ]);

            // Parse and save employees data
            if ($validated['employees_data']) {
                $lines = array_filter(array_map('trim', explode("\n", $validated['employees_data'])));
                foreach ($lines as $line) {
                    $parts = array_map('trim', explode(',', $line));
                    if (count($parts) >= 2) {
                        DB::table('workforce_employee')->insertOrIgnore([
                            'tenant_id'      => $batch->tenant_id,
                            'branch_id'      => $batch->branch_id,
                            'employee_code'  => 'EMP-' . uniqid(),
                            'name'           => $parts[0] ?? null,
                            'designation'    => $parts[1] ?? null,
                            'basic_salary'   => isset($parts[2]) ? (float) $parts[2] : 0,
                            'date_of_joining'=> now()->toDateString(),
                            'status'         => 'active',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            // Parse and save attendance data
            if ($validated['attendance_data']) {
                $lines = array_filter(array_map('trim', explode("\n", $validated['attendance_data'])));
                foreach ($lines as $line) {
                    $parts = array_map('trim', explode(',', $line));
                    if (count($parts) >= 2) {
                        DB::table('workforce_attendance')->insertOrIgnore([
                            'tenant_id'       => $batch->tenant_id,
                            'branch_id'       => $batch->branch_id,
                            'employee_id'     => $parts[0] ?? null,
                            'attendance_date' => $parts[1] ?? now()->toDateString(),
                            'status'          => $parts[2] ?? 'present',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }

            // Payroll text-paste is intentionally not supported here;
            // use the CSV upload endpoint with dataset_type=payroll instead.

            Log::info("Manual data saved for batch {$batchId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Manual data save error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPdfForm(Request $request, int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240'
            ]);

            // Store PDF
            if (!Storage::disk('local')->exists('compliance/manual_uploads')) {
                Storage::disk('local')->makeDirectory('compliance/manual_uploads');
            }

            $file = $request->file('file');
            $fileName = "batch_{$batchId}_{$formCode}_" . time() . ".pdf";
            $filePath = $file->storeAs('compliance/manual_uploads', $fileName, 'local');

            // Update batch form with file path
            DB::table('compliance_batch_forms')
                ->where('batch_id', $batchId)
                ->where('form_code', $formCode)
                ->update([
                    'file_path' => $filePath,
                    'status' => 'generated',
                    'updated_at' => now()
                ]);

            Log::info("PDF uploaded for batch {$batchId}, form {$formCode}");

            return response()->json([
                'status' => 'success',
                'message' => 'PDF uploaded successfully',
                'file_path' => $filePath
            ]);
        } catch (\Exception $e) {
            Log::error('PDF upload error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Throw a clear error if required CSV headers are missing.
     */
    // ── Post-upload: trigger generation when all data is ready ─────────────

    private function maybeGenerateForms(\App\Models\ComplianceExecutionBatch $batch): array
    {
        // Only auto-generate for FULL subscription batches in pending state
        $tenant = DB::table('tenants')->where('id', $batch->tenant_id)->first();
        if (! $tenant || strtoupper($tenant->subscription_type) !== 'FULL') {
            return ['triggered' => false, 'reason' => 'MINIMAL subscription — click Proceed to Generate'];
        }

        if ($batch->status !== 'pending') {
            return ['triggered' => false, 'reason' => "Batch already in status: {$batch->status}"];
        }

        // Check all 3 datasets exist for this tenant/branch
        $dataEngine = app(\App\Services\Compliance\DataAvailabilityEngine::class);
        $availability = $dataEngine->checkDataAvailability(
            $batch->tenant_id,
            $batch->branch_id,
            $batch->period_month,
            $batch->period_year
        );

        // Core 3 datasets required — ignore optional ones (incidents, hazard, etc.)
        $coreReady = ! array_intersect(
            ['employees', 'attendance', 'payroll'],
            $availability['missing_data'] ?? []
        );

        if (! $coreReady) {
            $stillMissing = array_intersect(
                ['employees', 'attendance', 'payroll'],
                $availability['missing_data'] ?? []
            );
            return [
                'triggered'     => false,
                'reason'        => 'Waiting for: ' . implode(', ', $stillMissing),
                'data_summary'  => $availability['data_summary'],
            ];
        }

        // All core data present — mark processing and run generation
        Log::info('Auto-triggering form generation after CSV upload', [
            'batch_id'  => $batch->id,
            'tenant_id' => $batch->tenant_id,
        ]);

        $batch->update(['status' => 'processing']);

        try {
            $service = app(\App\Services\Compliance\RealtimeComplianceExecutionService::class);
            $results = $service->processBatchRealtime($batch->id, fn() => null);

            Log::info('Auto-generation complete', [
                'batch_id'   => $batch->id,
                'successful' => $results['successful'],
                'failed'     => $results['failed'],
            ]);

            return [
                'triggered'       => true,
                'generated_forms' => $results['successful'],
                'failed_forms'    => $results['failed'],
                'batch_status'    => $results['failed'] === 0 ? 'completed' : 'partial',
            ];

        } catch (\Throwable $e) {
            Log::error('Auto-generation failed', [
                'batch_id' => $batch->id,
                'error'    => $e->getMessage(),
            ]);

            $batch->update(['status' => 'pending']); // revert so user can retry

            return [
                'triggered' => false,
                'reason'    => 'Generation error: ' . $e->getMessage(),
            ];
        }
    }

    private function validateCsvHeaders(array $headers, array $required, string $type): void
    {
        $missing = array_diff($required, $headers);
        if (! empty($missing)) {
            throw new \InvalidArgumentException(
                "CSV ({$type}): missing required columns: " . implode(', ', $missing)
            );
        }
    }

    public function uploadCsvData(Request $request, int $batchId)
    {
        // ── 1. Table existence guard — never let a missing table produce a 500 ──
        $requiredTables = [
            'workforce_employee',
            'workforce_payroll_entry',
            'workforce_attendance',
            'workforce_payroll_cycle',
        ];
        $missingTables = array_filter(
            $requiredTables,
            fn($t) => ! \Illuminate\Support\Facades\Schema::hasTable($t)
        );
        if (! empty($missingTables)) {
            Log::error('CSV upload: required tables missing', ['tables' => $missingTables]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Required database tables missing: ' . implode(', ', $missingTables),
            ], 500);
        }

        // ── 2. Request validation ─────────────────────────────────────────────
        try {
            $validated = $request->validate([
                'file'         => 'required|file|max:10240',
                'dataset_type' => 'required|string|in:employees,payroll,attendance',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed: ' . implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        }

        // ── 3. Batch ownership check ──────────────────────────────────────────
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->first();

        if (! $batch) {
            return response()->json([
                'status'  => 'error',
                'message' => "Batch #{$batchId} not found or access denied.",
            ], 404);
        }

        $file        = $request->file('file');
        $datasetType = $validated['dataset_type'];

        // ── 4. Parse CSV — BOM strip + delimiter detection ──────────────────
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not open uploaded file.',
            ], 500);
        }

        // Read first raw line to detect delimiter and strip UTF-8 BOM
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): file is empty or unreadable.",
            ], 422);
        }
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rawHeaders = fgetcsv($handle, 4096, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): file is empty or unreadable.",
            ], 422);
        }

        $headers = array_map(function (string $h): string {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM on first cell
            return strtolower(trim(preg_replace('/\s+/', '_', $h)));
        }, $rawHeaders);

        // ── 5. Header validation (before any DB work) ─────────────────────────
        $requiredHeaders = [
            'employees'  => ['employee_code', 'name'],
            'payroll'    => ['employee_code', 'gross_salary', 'net_salary'],
            'attendance' => ['employee_code', 'working_days'],
        ];

        $missing = array_diff($requiredHeaders[$datasetType], $headers);
        if (! empty($missing)) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): missing required columns: " . implode(', ', $missing),
                'hint'    => 'Required: ' . implode(', ', $requiredHeaders[$datasetType]),
            ], 422);
        }

        // ── 6. Parse all rows into memory (before transaction) ────────────────
        $rows   = [];
        $colCount = count($headers);
        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            // Skip completely empty lines
            if ($data === [null] || implode('', $data) === '') {
                continue;
            }
            // Pad short rows / trim long rows (handles trailing commas from Excel)
            if (count($data) < $colCount) {
                $data = array_pad($data, $colCount, '');
            } elseif (count($data) > $colCount) {
                $data = array_slice($data, 0, $colCount);
            }
            $row = array_combine($headers, array_map('trim', $data));
            if (empty($row['employee_code'])) {
                continue; // skip blank-code rows (footer lines, etc.)
            }
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): no valid data rows found after the header.",
            ], 422);
        }

        Log::info('CSV upload started', [
            'batch_id'     => $batchId,
            'dataset_type' => $datasetType,
            'filename'     => $file->getClientOriginalName(),
            'row_count'    => count($rows),
            'tenant_id'    => $batch->tenant_id,
        ]);

        // ── 7. Transaction — all inserts or nothing ───────────────────────────
        try {
            $recordsInserted = DB::transaction(function () use (
                $batch, $datasetType, $rows
            ) {
                $inserted = 0;

                if ($datasetType === 'employees') {
                    foreach ($rows as $row) {
                        $exists = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $row['employee_code'])
                            ->exists();

                        if ($exists) {
                            // Update existing record so re-uploads refresh data
                            DB::table('workforce_employee')
                                ->where('tenant_id', $batch->tenant_id)
                                ->where('employee_code', $row['employee_code'])
                                ->update([
                                    'name'            => $row['name'],
                                    'designation'     => $row['designation'] ?? null,
                                    'department'      => $row['department']  ?? null,
                                    'pf_number'       => $row['uan']         ?? $row['pf_number']  ?? null,
                                    'esi_number'      => $row['esi']         ?? $row['esi_number'] ?? null,
                                    'basic_salary'    => (float) ($row['basic_salary'] ?? $row['salary'] ?? 0),
                                    'updated_at'      => now(),
                                ]);
                        } else {
                            DB::table('workforce_employee')->insert([
                                'tenant_id'        => $batch->tenant_id,
                                'branch_id'        => $batch->branch_id,
                                'employee_code'    => $row['employee_code'],
                                'name'             => $row['name'],
                                'designation'      => $row['designation']  ?? null,
                                'department'       => $row['department']   ?? null,
                                'pf_number'        => $row['uan']          ?? $row['pf_number']  ?? null,
                                'esi_number'       => $row['esi']          ?? $row['esi_number'] ?? null,
                                'basic_salary'     => (float) ($row['basic_salary'] ?? $row['salary'] ?? 0),
                                'date_of_joining'  => $row['date_of_joining'] ?? $row['doj'] ?? now()->toDateString(),
                                'status'           => 'active',
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                        $inserted++;
                    }

                } elseif ($datasetType === 'payroll') {
                    // Resolve or create payroll cycle once for the whole file
                    $cycleId = DB::table('workforce_payroll_cycle')
                        ->where('tenant_id', $batch->tenant_id)
                        ->whereDate('period_from', $batch->period_from->toDateString())
                        ->whereDate('period_to',   $batch->period_to->toDateString())
                        ->value('id');

                    if (! $cycleId) {
                        $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                            'tenant_id'    => $batch->tenant_id,
                            'cycle_name'   => 'CSV Import ' . $batch->period_from->format('M Y'),
                            'period_from'  => $batch->period_from->toDateString(),
                            'period_to'    => $batch->period_to->toDateString(),
                            'status'       => 'processed',
                            'processed_at' => now(),
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }

                    foreach ($rows as $row) {
                        $empCode    = $row['employee_code'];
                        $employeeId = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $empCode)
                            ->value('id');

                        if (! $employeeId) {
                            throw new \RuntimeException(
                                "Payroll row skipped — employee not found: {$empCode}. "
                                . 'Upload employees.csv first.'
                            );
                        }

                        $gross       = (float) ($row['gross_salary'] ?? $row['gross'] ?? 0);
                        $net         = (float) ($row['net_salary']   ?? $row['net']   ?? 0);
                        $pf          = (float) ($row['pf_employee']  ?? $row['pf']    ?? 0);
                        $esi         = (float) ($row['esi_employee'] ?? $row['esi']   ?? 0);
                        $pt          = (float) ($row['professional_tax'] ?? $row['pt'] ?? 0);

                        if ($gross <= 0) {
                            throw new \InvalidArgumentException(
                                "Invalid gross_salary (must be > 0) for employee: {$empCode}"
                            );
                        }
                        if ($net > $gross) {
                            throw new \InvalidArgumentException(
                                "net_salary ({$net}) exceeds gross_salary ({$gross}) for employee: {$empCode}"
                            );
                        }

                        DB::table('workforce_payroll_entry')->insertOrIgnore([
                            'tenant_id'         => $batch->tenant_id,
                            'branch_id'         => $batch->branch_id,
                            'payroll_cycle_id'  => $cycleId,
                            'employee_id'       => $employeeId,
                            'total_days_worked' => (int) ($row['working_days'] ?? $row['days_worked'] ?? 26),
                            'paid_leave_days'   => 0,
                            'unpaid_leave_days' => (int) ($row['absent'] ?? 0),
                            'overtime_hours'    => 0,
                            'basic_earned'      => (float) ($row['basic_salary'] ?? $row['basic'] ?? 0),
                            'da_earned'         => (float) ($row['da']  ?? 0),
                            'hra_earned'        => (float) ($row['hra'] ?? 0),
                            'other_allowances'  => (float) ($row['other_allowances'] ?? 0),
                            'overtime_wages'    => 0,
                            'gross_salary'      => $gross,
                            'pf_employee'       => $pf,
                            'esi_employee'      => $esi,
                            'professional_tax'  => $pt,
                            'fines'             => 0,
                            'advances'          => 0,
                            'other_deductions'  => 0,
                            'total_deductions'  => $gross - $net,
                            'net_salary'        => $net,
                            'payment_date'      => $row['payment_date'] ?? null,
                            'payment_mode'      => $row['payment_mode'] ?? 'Bank Transfer',
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                        $inserted++;
                    }

                } elseif ($datasetType === 'attendance') {
                    // Derive the period start date safely — period_from may be null
                    // on older batches that pre-date the period_month/year columns.
                    $periodStart = null;
                    if (! empty($batch->period_from)) {
                        $periodStart = \Carbon\Carbon::parse($batch->period_from)->toDateString();
                    } elseif (! empty($batch->period_month) && ! empty($batch->period_year)) {
                        $periodStart = \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->toDateString();
                    } else {
                        $periodStart = now()->startOfMonth()->toDateString();
                    }

                    foreach ($rows as $row) {
                        $empCode = trim($row['employee_code'] ?? '');
                        if ($empCode === '') continue;

                        $employeeId = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $empCode)
                            ->whereNull('deleted_at')
                            ->value('id');

                        if (! $employeeId) {
                            throw new \RuntimeException(
                                "Attendance: employee not found — {$empCode}. Upload employees.csv first."
                            );
                        }

                        $workingDays = trim($row['working_days'] ?? '');
                        if (! is_numeric($workingDays)) {
                            throw new \InvalidArgumentException(
                                "Attendance: invalid working_days value '{$workingDays}' for employee {$empCode}."
                            );
                        }

                        $workingDays = (int) $workingDays;
                        $absent      = (int) ($row['absent'] ?? 0);
                        $presentDays = max(0, $workingDays - $absent);

                        // Use explicit date from CSV, fall back to period start
                        $attendDate = trim($row['attendance_date'] ?? $row['date'] ?? '');
                        if ($attendDate === '' || ! strtotime($attendDate)) {
                            $attendDate = $periodStart;
                        }

                        // Use updateOrInsert to handle re-uploads safely.
                        // insertOrIgnore silently skips on duplicate key but can
                        // still trigger a MySQL warning that corrupts JSON output.
                        DB::table('workforce_attendance')->updateOrInsert(
                            [
                                'tenant_id'       => $batch->tenant_id,
                                'employee_id'     => $employeeId,
                                'attendance_date' => $attendDate,
                            ],
                            [
                                'branch_id'  => $batch->branch_id,
                                'status'     => $presentDays > 0 ? 'present' : 'absent',
                                'remarks'    => "CSV import: {$presentDays}/{$workingDays} days present",
                                'deleted_at' => null,   // restore if previously soft-deleted
                                'updated_at' => now(),
                                'created_at' => now(),  // ignored on update by updateOrInsert
                            ]
                        );
                        $inserted++;
                    }
                }

                return $inserted;
            });

            Log::info('CSV upload completed', [
                'batch_id'         => $batchId,
                'dataset_type'     => $datasetType,
                'records_inserted' => $recordsInserted,
            ]);

            // Auto-trigger form generation — wrapped so a generation failure
            // never rolls back the already-committed CSV data
            try {
                $generationResult = $this->maybeGenerateForms($batch);
            } catch (\Throwable $genEx) {
                Log::error('maybeGenerateForms threw unexpectedly', [
                    'batch_id' => $batchId,
                    'error'    => $genEx->getMessage(),
                ]);
                $generationResult = ['triggered' => false, 'reason' => $genEx->getMessage()];
            }

            return response()->json([
                'status'           => 'success',
                'message'          => "Successfully imported {$recordsInserted} {$datasetType} records",
                'records_inserted' => $recordsInserted,
                'dataset_type'     => $datasetType,
                'generation'       => $generationResult,
            ]);

        } catch (\Throwable $e) {
            Log::error('CSV upload failed', [
                'batch_id'     => $batchId,
                'dataset_type' => $datasetType,
                'error'        => $e->getMessage(),
                'file'         => $e->getFile() . ':' . $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
