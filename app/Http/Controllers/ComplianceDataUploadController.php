<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplianceDataUploadController extends Controller
{
    public function showForm()
    {
        return view('compliance.csv_upload');
    }

    public function upload(Request $request)
    {
        // Validate file presence and period — always returns JSON on failure
        // because the JS fetch sends Accept: application/json.
        $request->validate([
            'employees_file'  => 'required|file|max:5120',
            'payroll_file'    => 'required|file|max:5120',
            'attendance_file' => 'required|file|max:5120',
            'period_from'     => 'required|date',
            'period_to'       => 'required|date|after_or_equal:period_from',
        ]);

        $user     = Auth::user();
        $tenantId = $user->tenant_id;
        $branchId = $user->branch_id;

        // Wrap everything — including CSV parsing — in one try/catch so that
        // any InvalidArgumentException from parseCsv returns JSON, not a 500.
        try {
            $employees   = $this->parseCsv($request->file('employees_file'),  'employees');
            $payrollRows = $this->parseCsv($request->file('payroll_file'),    'payroll');
            $attendRows  = $this->parseCsv($request->file('attendance_file'), 'attendance');

            Log::info('CSV parsed', [
                'tenant_id'  => $tenantId,
                'employees'  => count($employees),
                'payroll'    => count($payrollRows),
                'attendance' => count($attendRows),
            ]);

            $this->validateConsistency($employees, $payrollRows, $attendRows);

            $counts = DB::transaction(function () use (
                $tenantId, $branchId, $employees, $payrollRows, $attendRows, $request
            ) {
                $empCodeToId  = $this->insertEmployees($employees, $tenantId, $branchId);
                $cycleId      = $this->resolvePayrollCycle(
                    $tenantId,
                    $request->input('period_from'),
                    $request->input('period_to')
                );
                $payrollCount = $this->insertPayroll($payrollRows, $empCodeToId, $tenantId, $branchId, $cycleId);
                $attendCount  = $this->insertAttendance($attendRows, $empCodeToId, $tenantId, $branchId);

                return [
                    'employees'  => count($empCodeToId),
                    'payroll'    => $payrollCount,
                    'attendance' => $attendCount,
                ];
            });

            Log::info('Multi-CSV upload success', ['tenant_id' => $tenantId, 'counts' => $counts]);

            return response()->json([
                'status'  => 'success',
                'message' => 'All datasets uploaded successfully',
                'counts'  => $counts,
            ]);

        } catch (\Throwable $e) {
            Log::error('Multi-CSV upload failed', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ── CSV Parsing ───────────────────────────────────────────────────────────

    /**
     * Parse a CSV file into an array of associative rows (lowercase keys).
     * Throws on empty file or missing required headers.
     */
    private function parseCsv(\Illuminate\Http\UploadedFile $file, string $type): array
    {
        $required = [
            'employees'  => ['employee_code', 'name'],
            'payroll'    => ['employee_code', 'gross_salary', 'net_salary'],
            'attendance' => ['employee_code', 'working_days'],
        ];

        $path   = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException("CSV ({$type}): cannot open uploaded file.");
        }

        // ── Detect delimiter from first line (comma vs semicolon) ─────────────
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): file is empty.");
        }

        // Strip UTF-8 BOM (EF BB BF) that Excel adds — causes invisible garbage
        // in the first header name, breaking array_diff required-column checks.
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        // Rewind and re-read with correct delimiter
        rewind($handle);

        // ── Header row ───────────────────────────────────────────────────────
        $rawHeaders = fgetcsv($handle, 4096, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): could not read header row.");
        }

        // Normalise: strip BOM from first cell, lowercase, spaces→underscore
        $headers = array_map(function (string $h): string {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM on first cell
            return strtolower(trim(preg_replace('/\s+/', '_', $h)));
        }, $rawHeaders);

        // ── Required-column check ─────────────────────────────────────────────
        $missing = array_diff($required[$type], $headers);
        if (! empty($missing)) {
            fclose($handle);
            throw new \InvalidArgumentException(
                "CSV ({$type}): missing required columns: " . implode(', ', $missing) .
                ". Found: " . implode(', ', $headers)
            );
        }

        // ── Data rows ─────────────────────────────────────────────────────────
        $rows    = [];
        $lineNum = 1;

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $lineNum++;

            // Skip completely empty lines
            if ($data === [null] || implode('', $data) === '') {
                continue;
            }

            // Pad or trim to match header count (handles trailing commas)
            $colCount = count($headers);
            if (count($data) < $colCount) {
                $data = array_pad($data, $colCount, '');
            } elseif (count($data) > $colCount) {
                $data = array_slice($data, 0, $colCount);
            }

            $row = array_combine($headers, array_map('trim', $data));

            if (empty($row['employee_code'])) {
                continue; // skip blank-code rows (e.g. footer lines)
            }

            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \InvalidArgumentException("CSV ({$type}): no valid data rows found.");
        }

        Log::debug("CSV ({$type}) parsed", ['rows' => count($rows), 'delimiter' => $delimiter, 'headers' => $headers]);

        return $rows;
    }

    // ── Cross-file Consistency ────────────────────────────────────────────────

    private function validateConsistency(array $employees, array $payrollRows, array $attendRows): void
    {
        $empCodes     = array_column($employees,   'employee_code');
        $payrollCodes = array_column($payrollRows, 'employee_code');
        $attendCodes  = array_column($attendRows,  'employee_code');

        // Duplicate employee codes
        $dupes = array_keys(array_filter(array_count_values($empCodes), fn($c) => $c > 1));
        if (! empty($dupes)) {
            throw new \InvalidArgumentException(
                'Duplicate employee codes in employees.csv: ' . implode(', ', $dupes)
            );
        }

        // Payroll codes not in employees
        $orphanPayroll = array_diff($payrollCodes, $empCodes);
        if (! empty($orphanPayroll)) {
            throw new \InvalidArgumentException(
                'Payroll data for unknown employee codes: ' . implode(', ', $orphanPayroll)
            );
        }

        // Attendance codes not in employees
        $orphanAttend = array_diff($attendCodes, $empCodes);
        if (! empty($orphanAttend)) {
            throw new \InvalidArgumentException(
                'Attendance data for unknown employee codes: ' . implode(', ', $orphanAttend)
            );
        }
    }

    // ── Insert Employees ──────────────────────────────────────────────────────

    /**
     * Upsert employees and return [employee_code => id] map.
     */
    private function insertEmployees(array $rows, int $tenantId, int $branchId): array
    {
        $map = [];

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            // Check if already exists for this tenant
            $existing = DB::table('workforce_employee')
                ->where('tenant_id', $tenantId)
                ->where('employee_code', $code)
                ->value('id');

            if ($existing) {
                $map[$code] = $existing;
                continue;
            }

            $id = DB::table('workforce_employee')->insertGetId([
                'tenant_id'          => $tenantId,
                'branch_id'          => $branchId,
                'employee_code'      => $code,
                'name'               => $row['name'],
                'designation'        => $row['designation']      ?? null,
                'department'         => $row['department']       ?? null,
                'pf_number'          => $row['uan']              ?? $row['pf_number']    ?? null,
                'esi_number'         => $row['esi']              ?? $row['esi_number']   ?? null,
                'basic_salary'       => isset($row['basic_salary']) ? (float) $row['basic_salary'] : 0,
                'date_of_joining'    => $row['date_of_joining']  ?? $row['doj']          ?? now()->toDateString(),
                'date_of_birth'      => $row['date_of_birth']    ?? $row['dob']          ?? null,
                'gender'             => $row['gender']           ?? $row['sex']          ?? null,
                'permanent_address'  => $row['permanent_address'] ?? $row['address']     ?? null,
                'status'             => 'active',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $map[$code] = $id;
        }

        return $map;
    }

    // ── Payroll Cycle ─────────────────────────────────────────────────────────

    private function resolvePayrollCycle(int $tenantId, string $from, string $to): int
    {
        $existing = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereDate('period_from', $from)
            ->whereDate('period_to', $to)
            ->value('id');

        if ($existing) {
            return $existing;
        }

        return DB::table('workforce_payroll_cycle')->insertGetId([
            'tenant_id'    => $tenantId,
            'cycle_name'   => 'CSV Import ' . \Carbon\Carbon::parse($from)->format('M Y'),
            'period_from'  => $from,
            'period_to'    => $to,
            'status'       => 'processed',
            'processed_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    // ── Insert Payroll ────────────────────────────────────────────────────────

    private function insertPayroll(array $rows, array $empMap, int $tenantId, int $branchId, int $cycleId): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            if (! isset($empMap[$code])) {
                throw new \RuntimeException("Payroll data mismatch for employee {$code}");
            }

            $gross       = (float) ($row['gross_salary']     ?? $row['gross']          ?? 0);
            $net         = (float) ($row['net_salary']        ?? $row['net']            ?? 0);
            $basic       = (float) ($row['basic_salary']      ?? $row['basic']          ?? 0);
            $pf          = (float) ($row['pf_employee']       ?? $row['pf']             ?? 0);
            $esi         = (float) ($row['esi_employee']      ?? $row['esi']            ?? 0);
            $pt          = (float) ($row['professional_tax']  ?? $row['pt']             ?? 0);
            $otHours     = (float) ($row['overtime_hours']    ?? $row['ot_hours']       ?? 0);
            $otWages     = (float) ($row['overtime_wages']    ?? $row['ot_wages']       ?? 0);
            $workingDays = (int)   ($row['working_days']      ?? $row['days_worked']    ?? 26);
            $absent      = (int)   ($row['absent']            ?? $row['absent_days']    ?? 0);
            $totalDeduct = $gross - $net;

            // Numeric sanity
            if ($gross <= 0) {
                throw new \InvalidArgumentException("Invalid gross_salary for employee {$code}");
            }
            if ($net > $gross) {
                throw new \InvalidArgumentException("net_salary exceeds gross_salary for employee {$code}");
            }

            DB::table('workforce_payroll_entry')->insertOrIgnore([
                'tenant_id'         => $tenantId,
                'branch_id'         => $branchId,
                'payroll_cycle_id'  => $cycleId,
                'employee_id'       => $empMap[$code],
                'total_days_worked' => $workingDays,
                'paid_leave_days'   => 0,
                'unpaid_leave_days' => $absent,
                'overtime_hours'    => $otHours,
                'basic_earned'      => $basic,
                'da_earned'         => (float) ($row['da']               ?? 0),
                'hra_earned'        => (float) ($row['hra']              ?? 0),
                'other_allowances'  => (float) ($row['other_allowances'] ?? 0),
                'overtime_wages'    => $otWages,
                'gross_salary'      => $gross,
                'pf_employee'       => $pf,
                'esi_employee'      => $esi,
                'professional_tax'  => $pt,
                'fines'             => 0,
                'advances'          => 0,
                'other_deductions'  => 0,
                'total_deductions'  => $totalDeduct,
                'net_salary'        => $net,
                'payment_date'      => $row['payment_date'] ?? null,
                'payment_mode'      => $row['payment_mode'] ?? 'Bank Transfer',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $count++;
        }

        return $count;
    }

    // ── Insert Attendance ─────────────────────────────────────────────────────

    private function insertAttendance(array $rows, array $empMap, int $tenantId, int $branchId): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            if (! isset($empMap[$code])) {
                throw new \RuntimeException("Attendance data mismatch for employee {$code}");
            }

            $workingDays = (int) ($row['working_days'] ?? $row['total_days'] ?? 26);
            $absent      = (int) ($row['absent'] ?? 0);
            $presentDays = $workingDays - $absent;

            // Generate one attendance row per present day using the period
            // stored on the payroll cycle; fall back to current month if absent.
            $date = $row['attendance_date'] ?? $row['date'] ?? null;

            if ($date) {
                // Single-row mode: one row with a specific date
                DB::table('workforce_attendance')->insertOrIgnore([
                    'tenant_id'       => $tenantId,
                    'branch_id'       => $branchId,
                    'employee_id'     => $empMap[$code],
                    'attendance_date' => $date,
                    'status'          => $row['status'] ?? 'present',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $count++;
            } else {
                // Summary mode: CSV has working_days + absent counts.
                // Insert a summary record using today as a placeholder date.
                // Real per-day records should come from a date-based CSV.
                DB::table('workforce_attendance')->insertOrIgnore([
                    'tenant_id'       => $tenantId,
                    'branch_id'       => $branchId,
                    'employee_id'     => $empMap[$code],
                    'attendance_date' => now()->toDateString(),
                    'status'          => $presentDays > 0 ? 'present' : 'absent',
                    'remarks'         => "CSV import: {$presentDays}/{$workingDays} days present",
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }
}
