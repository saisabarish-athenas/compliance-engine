<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 12 - Register of Adult Workers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 12px;
            font-size: 9px;
        }
        .form-container {
            border: 1px solid black;
            padding: 10px;
            margin: 0 auto;
            width: 99%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .form-header div {
            margin: 2px 0;
        }
        .header-title {
            font-weight: bold;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 8px;
            margin-bottom: 8px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            line-height: 1.1;
            height: 16px;
        }
        .register-table td {
            height: 20px;
        }
        .col-sl { width: 4%; }
        .col-name { width: 28%; }
        .col-father { width: 12%; }
        .col-nature { width: 12%; }
        .col-group { width: 6%; }
        .col-relay { width: 6%; }
        .col-cert { width: 10%; }
        .col-token { width: 10%; }
        .col-remarks { width: 12%; }
        .footer-section {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        .footer-left {
            flex: 1;
        }
        .footer-right {
            flex: 1;
            text-align: right;
        }
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #000;
            width: 150px;
            margin-left: auto;
        }
        .signature-label {
            margin-top: 2px;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 12</div>
            <div>(Prescribed under Rule 80)</div>
            <div class="header-title">Register of adult workers</div>
        </div>

        <!-- Register Table -->
        <table class="register-table">
            <thead>
                <!-- Row 1: Main headers -->
                <tr>
                    <th class="col-sl" rowspan="2">Sl. No.</th>
                    <th class="col-name" rowspan="2">Name & residential address of the worker</th>
                    <th class="col-father" rowspan="2">Father's name</th>
                    <th class="col-nature" rowspan="2">Nature of work</th>
                    <th class="col-group" rowspan="2">Letter of group as in Form No. 11</th>
                    <th class="col-relay" rowspan="2">No. of relay, if working in shifts</th>
                    <th colspan="2" style="text-align: center;">No. & date of certificate if an adolescent</th>
                    <th class="col-remarks" rowspan="2">Remarks</th>
                </tr>
                <!-- Row 2: Certificate sub-headers -->
                <tr>
                    <th class="col-cert">No. of certificate & date</th>
                    <th class="col-token">Token No. giving reference to the certificates</th>
                </tr>
                <!-- Row 3: Column numbers -->
                <tr>
                    <th class="col-sl">(1)</th>
                    <th class="col-name">(2)</th>
                    <th class="col-father">(3)</th>
                    <th class="col-nature">(4)</th>
                    <th class="col-group">(5)</th>
                    <th class="col-relay">(6)</th>
                    <th class="col-cert">(7)</th>
                    <th class="col-token">(8)</th>
                    <th class="col-remarks">(9)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? $entries ?? [] as $index => $row)
                <tr>
                    <td class="col-sl">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $row['employee_name'] ?? '' }}</td>
                    <td class="col-father">{{ $row['father_name'] ?? '' }}</td>
                    <td class="col-nature">{{ $row['designation'] ?? '' }}</td>
                    <td class="col-group">{{ $row['group'] ?? '' }}</td>
                    <td class="col-relay">{{ $row['relay'] ?? '' }}</td>
                    <td class="col-cert">{{ $row['certificate_no'] ?? '' }}</td>
                    <td class="col-token">{{ $row['token_no'] ?? '' }}</td>
                    <td class="col-remarks">{{ $row['remarks'] ?? '' }}</td>
                </tr>
                @empty
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                <tr><td class="col-sl"></td><td class="col-name"></td><td class="col-father"></td><td class="col-nature"></td><td class="col-group"></td><td class="col-relay"></td><td class="col-cert"></td><td class="col-token"></td><td class="col-remarks"></td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                <div>Date: ___________</div>
            </div>
            <div class="footer-right">
                <div class="signature-line"></div>
                <div class="signature-label">(Signed) Manager/Occupier</div>
            </div>
        </div>
    </div>
</body>
</html>
