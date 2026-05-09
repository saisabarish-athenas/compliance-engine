<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM D - Register of Attendance</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 5mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            width: 100%;
            overflow: hidden;
        }
        .page-border {
            border: 1px solid #000;
            padding: 3px;
            width: 410mm;
            max-width: 410mm;
        }
        .form-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 2px 0;
            margin-bottom: 2px;
        }
        .form-header h1 {
            font-size: 11px;
            font-weight: bold;
            margin: 0;
        }
        .form-header h2 {
            font-size: 9px;
            font-weight: bold;
            margin: 0;
        }
        .form-header p {
            font-size: 7px;
            margin: 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 7px;
        }
        .info-label {
            font-weight: bold;
            width: 130px;
            background-color: #f5f5f5;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .attendance-table thead th {
            background-color: #d0e8f7;
            font-weight: bold;
            font-size: 7px;
            line-height: 1.2;
        }
        .col-sno { width: 30px; max-width: 30px; }
        .col-name { width: 180px; max-width: 180px; min-width: 180px; text-align: left !important; padding: 3px 5px !important; font-size: 7px; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
        .col-designation { width: 120px; max-width: 120px; min-width: 120px; text-align: left !important; padding: 3px 5px !important; font-size: 7px; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
        .col-date { width: 18px; max-width: 18px; font-size: 6.5px; padding: 2px 1px !important; }
        .col-summary { width: 35px; max-width: 35px; font-size: 6.5px; padding: 2px 1px !important; }
        .col-remarks { width: 35px; max-width: 35px; font-size: 6.5px; padding: 2px 1px !important; }
        .date-header {
            background-color: #b8d9f0 !important;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .signature-area {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="page-border">
        <div class="form-header">
            <h1>FORM D</h1>
            <h2>REGISTER OF ATTENDANCE</h2>
            <p>[See rule 2(1)]</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Name of the Establishment</td>
                <td>{{ $establishment_name ?? 'M/S. MAK47' }}</td>
            </tr>
            <tr>
                <td class="info-label">Name of Owner</td>
                <td>{{ $owner_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">For the Period From</td>
                <td>{{ $month_name ?? 'December' }} {{ $year ?? '' }}</td>
            </tr>
        </table>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-sno">S.NO</th>
                    <th rowspan="2" class="col-name">NAME</th>
                    <th rowspan="2" class="col-designation">DESIGNATION</th>
                    <th colspan="31" class="date-header">DATE</th>
                    <th rowspan="2" class="col-summary">Total<br>Days</th>
                    <th rowspan="2" class="col-summary">Paid<br>Holidays</th>
                    <th rowspan="2" class="col-summary">Paid<br>Leave</th>
                    <th rowspan="2" class="col-summary">Weekly<br>Off</th>
                    <th rowspan="2" class="col-summary">ABSENT<br>Days</th>
                    <th rowspan="2" class="col-summary">Total<br>Days</th>
                    <th rowspan="2" class="col-remarks">Remarks</th>
                </tr>
                <tr>
                    @for($day = 1; $day <= 31; $day++)
                        <th class="col-date date-header">{{ $day }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? $entries ?? [] as $index => $row)
                    <tr>
                        <td class="col-sno">{{ $index + 1 }}</td>
                        <td class="col-name">{{ $row['employee_name'] ?? '' }}</td>
                        <td class="col-designation">{{ $row['designation'] ?? '' }}</td>
                        @for($day = 1; $day <= 31; $day++)
                            <td class="col-date">{{ $row['day_' . $day] ?? '' }}</td>
                        @endfor
                        <td class="col-summary">{{ $row['total_present'] ?? '0' }}</td>
                        <td class="col-summary">{{ $row['paid_holidays'] ?? '0' }}</td>
                        <td class="col-summary">{{ $row['paid_leave'] ?? '0' }}</td>
                        <td class="col-summary">{{ $row['weekly_off'] ?? '0' }}</td>
                        <td class="col-summary">{{ $row['absent_days'] ?? '0' }}</td>
                        <td class="col-summary">{{ $row['total_days'] ?? '0' }}</td>
                        <td class="col-remarks">{{ $row['remarks'] ?? '-' }}</td>
                    </tr>
                @empty
                    @for($i = 1; $i <= 9; $i++)
                        <tr>
                            <td class="col-sno">{{ $i }}</td>
                            <td class="col-name"></td>
                            <td class="col-designation"></td>
                            @for($day = 1; $day <= 31; $day++)
                                <td class="col-date"></td>
                            @endfor
                            <td class="col-summary">0</td>
                            <td class="col-summary">0</td>
                            <td class="col-summary">0</td>
                            <td class="col-summary">0</td>
                            <td class="col-summary">0</td>
                            <td class="col-summary">0</td>
                            <td class="col-remarks">-</td>
                        </tr>
                    @endfor
                @endforelse
                <tr class="total-row">
                    <td colspan="3" style="text-align: center;">TOTAL</td>
                    @for($day = 1; $day <= 31; $day++)
                        <td class="col-date"></td>
                    @endfor
                    <td class="col-summary">{{ $totals['total_present'] ?? '0' }}</td>
                    <td class="col-summary">{{ $totals['paid_holidays'] ?? '0' }}</td>
                    <td class="col-summary">{{ $totals['paid_leave'] ?? '0' }}</td>
                    <td class="col-summary">{{ $totals['weekly_off'] ?? '0' }}</td>
                    <td class="col-summary">{{ $totals['absent_days'] ?? '0' }}</td>
                    <td class="col-summary">{{ $totals['total_days'] ?? '0' }}</td>
                    <td class="col-remarks">-</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-area">
            <div style="margin-top: 30px;">_______________________</div>
            <div>Signature</div>
        </div>
    </div>
</body>
</html>
