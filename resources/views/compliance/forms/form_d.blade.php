<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM D - Register of Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 10px;
            font-size: 10px;
        }
        .form-container {
            border: 2px solid black;
            padding: 10px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
        }
        .form-header div {
            margin: 2px 0;
            font-weight: bold;
        }
        .establishment-info {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 20%;
        }
        .info-value {
            width: 80%;
            border-bottom: 1px solid black;
            padding: 2px 5px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 9px;
            margin-bottom: 15px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 3px 2px;
            text-align: left;
            vertical-align: center;
        }
        .register-table th {
            font-weight: bold;
            background-color: #e8f4f8;
            font-size: 8px;
            height: 25px;
        }
        .register-table td {
            height: 20px;
        }
        .col-sno { width: 3%; text-align: center; }
        .col-name { width: 10%; }
        .col-designation { width: 10%; }
        .col-day { width: 2.5%; text-align: center; }
        .col-summary { width: 4%; text-align: center; }
        .col-remarks { width: 5%; }
        .day-header {
            background-color: #c0e0e0;
            text-align: center;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .signature-space {
            margin-top: 40px;
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div>FORM D</div>
            <div>REGISTER OF ATTENDANCE</div>
            <div>[See rule 2(1)]</div>
        </div>

        <div class="establishment-info">
            <div class="info-row">
                <div class="info-label">Name of the Establishment</div>
                <div class="info-value">{{ $header['establishment_name'] ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Name of Owner</div>
                <div class="info-value">{{ $header['owner_name'] ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">For the Period From</div>
                <div class="info-value">{{ $header['month_name'] ?? '' }} {{ $header['year'] ?? '' }}</div>
            </div>
        </div>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sno">S.NO</th>
                    <th class="col-name">NAME</th>
                    <th class="col-designation">DESIGNATION</th>
                    @for($day = 1; $day <= 31; $day++)
                        <th class="col-day day-header">{{ $day ?? '' }}</th>
                    @endfor
                    <th class="col-summary">Total Present Days</th>
                    <th class="col-summary">Paid Holidays</th>
                    <th class="col-summary">Paid Leave</th>
                    <th class="col-summary">Weekly Off</th>
                    <th class="col-summary">Absent Days</th>
                    <th class="col-summary">Total Days</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="col-sno">{{ $index + 1 }}</td>
                            <td class="col-name">{{ $row['employee_name'] ?? '' }}</td>
                            <td class="col-designation">{{ $row['designation'] ?? '' }}</td>
                            @for($day = 1; $day <= 31; $day++)
                                <td class="col-day">{{ $row['day_' . $day] ?? '' }}</td>
                            @endfor
                            <td class="col-summary">{{ $row['total_present'] ?? '0' }}</td>
                            <td class="col-summary">{{ $row['paid_holidays'] ?? '0' }}</td>
                            <td class="col-summary">{{ $row['paid_leave'] ?? '0' }}</td>
                            <td class="col-summary">{{ $row['weekly_off'] ?? '0' }}</td>
                            <td class="col-summary">{{ $row['absent_days'] ?? '0' }}</td>
                            <td class="col-summary">{{ $row['total_days'] ?? '0' }}</td>
                            <td class="col-remarks">{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="42" style="text-align:center;">No records found</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3">TOTAL</td>
                    @for($day = 1; $day <= 31; $day++)
                        <td class="col-day"></td>
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

        <div class="signature-space"></div>
    </div>
</body>
</html>
