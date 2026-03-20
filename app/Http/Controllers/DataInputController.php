<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                        DB::table('employees')->insertOrIgnore([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'name' => $parts[0] ?? null,
                            'designation' => $parts[1] ?? null,
                            'salary' => isset($parts[2]) ? (float)$parts[2] : 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Parse and save payroll data
            if ($validated['payroll_data']) {
                $lines = array_filter(array_map('trim', explode("\n", $validated['payroll_data'])));
                foreach ($lines as $line) {
                    $parts = array_map('trim', explode(',', $line));
                    if (count($parts) >= 2) {
                        DB::table('payroll')->insertOrIgnore([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'employee_id' => $parts[0] ?? null,
                            'amount' => isset($parts[1]) ? (float)$parts[1] : 0,
                            'date' => isset($parts[2]) ? $parts[2] : now()->toDateString(),
                            'created_at' => now(),
                            'updated_at' => now()
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
                        DB::table('attendance')->insertOrIgnore([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'employee_id' => $parts[0] ?? null,
                            'date' => $parts[1] ?? now()->toDateString(),
                            'status' => $parts[2] ?? 'present',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

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

    public function uploadCsvData(Request $request, int $batchId)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
                'dataset_type' => 'required|string|in:employees,payroll,attendance'
            ]);

            $file = $request->file('file');
            $datasetType = $validated['dataset_type'];
            $handle = fopen($file->getRealPath(), 'r');
            $headers = fgetcsv($handle, 1000, ',');
            $headers = array_map('strtolower', array_map('trim', $headers));
            $recordsInserted = 0;

            DB::beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($headers) === count($data)) {
                        $row = array_combine($headers, $data);

                        if ($datasetType === 'employees') {
                            DB::table('employees')->insertOrIgnore([
                                'tenant_id' => $batch->tenant_id,
                                'branch_id' => $batch->branch_id,
                                'name' => $row['name'] ?? $row['employee_name'] ?? null,
                                'designation' => $row['designation'] ?? $row['position'] ?? null,
                                'salary' => isset($row['salary']) ? (float)$row['salary'] : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } elseif ($datasetType === 'payroll') {
                            DB::table('payroll')->insertOrIgnore([
                                'tenant_id' => $batch->tenant_id,
                                'branch_id' => $batch->branch_id,
                                'employee_id' => $row['employee_id'] ?? $row['emp_id'] ?? null,
                                'amount' => isset($row['amount']) ? (float)$row['amount'] : 0,
                                'date' => $row['date'] ?? $row['payment_date'] ?? now()->toDateString(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } elseif ($datasetType === 'attendance') {
                            DB::table('attendance')->insertOrIgnore([
                                'tenant_id' => $batch->tenant_id,
                                'branch_id' => $batch->branch_id,
                                'employee_id' => $row['employee_id'] ?? $row['emp_id'] ?? null,
                                'date' => $row['date'] ?? $row['attendance_date'] ?? now()->toDateString(),
                                'status' => $row['status'] ?? $row['attendance_status'] ?? 'present',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        $recordsInserted++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                throw $e;
            }

            fclose($handle);

            Log::info("CSV data uploaded for batch {$batchId}", [
                'dataset_type' => $datasetType,
                'records_inserted' => $recordsInserted
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$recordsInserted} records",
                'records_inserted' => $recordsInserted
            ]);
        } catch (\Exception $e) {
            Log::error('CSV upload error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
