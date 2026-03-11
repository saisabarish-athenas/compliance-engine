<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XXI - Register of Fines</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 20px;
        }
        .form-container {
            border: 2px solid black;
            padding: 15px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .form-header div {
            margin: 3px 0;
        }
        .header-title {
            font-weight: bold;
        }
        .establishment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid black;
        }
        .establishment-table td {
            border: 1px solid black;
            padding: 6px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        .establishment-table td:first-child {
            font-weight: bold;
            width: 45%;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 11px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            font-size: 10px;
        }
        .register-table td {
            height: 20px;
        }
        .col-sl {
            width: 4%;
            text-align: center;
        }
        .col-name {
            width: 8%;
        }
        .col-father {
            width: 8%;
        }
        .col-designation {
            width: 9%;
        }
        .col-act {
            width: 9%;
        }
        .col-date-offence {
            width: 7%;
        }
        .col-showed-cause {
            width: 9%;
        }
        .col-heard-by {
            width: 10%;
        }
        .col-wage {
            width: 8%;
        }
        .col-amount {
            width: 8%;
        }
        .col-date-realised {
            width: 8%;
        }
        .col-remarks {
            width: 8%;
        }
        .footer-section {
            margin-top: 10px;
            font-size: 11px;
        }
        .footer-note {
            margin-bottom: 8px;
        }
        .nil-footer {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            font-size: 11px;
        }
        .signature-section {
            text-align: right;
            margin-top: 30px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XXI</div>
            <div>[See Rule 78(2)(d)]</div>
            <div class="header-title">Register of Fines</div>
        </div>

        <table class="establishment-table">
            <tr>
                <td>NAME AND ADDRESS OF CONTRACTOR :</td>
                <td>{{ $contractor_name ?? '' }}</td>
            </tr>
            <tr>
                <td>NATURE AND LOCATION OF WORK :</td>
                <td>{{ $work_nature ?? '' }}</td>
            </tr>
            <tr>
                <td>NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</td>
                <td>{{ $establishment_name ?? '' }}</td>
            </tr>
            <tr>
                <td>NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</td>
                <td>{{ $principal_employer ?? '' }}</td>
            </tr>
            <tr>
                <td>Month & Year:</td>
                <td>{{ $month_year ?? '' }}</td>
            </tr>
        </table>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sl">SL NO</th>
                    <th class="col-name">Name of workmen</th>
                    <th class="col-father">Father's/Husband's name</th>
                    <th class="col-designation">Designation/Nature of employment</th>
                    <th class="col-act">Act/Omission for which fine imposed</th>
                    <th class="col-date-offence">Date of offence</th>
                    <th class="col-showed-cause">Whether workmen showed against fine</th>
                    <th class="col-heard-by">Name of person in whose presence employee's explanation was heard</th>
                    <th class="col-wage">Wage period and wages payable</th>
                    <th class="col-amount">Amount of fine imposed</th>
                    <th class="col-date-realised">Date on which fine realised</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($rows) && count($rows) > 0)
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="col-sl">{{ $index + 1 }}</td>
                            <td class="col-name">{{ $row['name'] ?? 'NIL' }}</td>
                            <td class="col-father">{{ $row['father_name'] ?? 'NIL' }}</td>
                            <td class="col-designation">{{ $row['designation'] ?? 'NIL' }}</td>
                            <td class="col-act">{{ $row['act_or_omission'] ?? 'NIL' }}</td>
                            <td class="col-date-offence">{{ $row['date_of_offence'] ?? 'NIL' }}</td>
                            <td class="col-showed-cause">{{ $row['showed_cause'] ?? 'NIL' }}</td>
                            <td class="col-heard-by">{{ $row['heard_by'] ?? 'NIL' }}</td>
                            <td class="col-wage">{{ $row['wage_period'] ?? 'NIL' }}</td>
                            <td class="col-amount">{{ $row['fine_amount'] ?? 'NIL' }}</td>
                            <td class="col-date-realised">{{ $row['fine_realised'] ?? 'NIL' }}</td>
                            <td class="col-remarks">{{ $row['remarks'] ?? 'NIL' }}</td>
                        </tr>
                    @endforeach
                @else
                    @for($i = 0; $i < 9; $i++)
                        <tr>
                            <td class="col-sl">{{ $i + 1 }}</td>
                            <td class="col-name">NIL</td>
                            <td class="col-father">NIL</td>
                            <td class="col-designation">NIL</td>
                            <td class="col-act">NIL</td>
                            <td class="col-date-offence">NIL</td>
                            <td class="col-showed-cause">NIL</td>
                            <td class="col-heard-by">NIL</td>
                            <td class="col-wage">NIL</td>
                            <td class="col-amount">NIL</td>
                            <td class="col-date-realised">NIL</td>
                            <td class="col-remarks">NIL</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-note">*Applicable only in case of damage/loss/fine</div>
            <div class="nil-footer">Nil for the month of {{ $month_year ?? '' }}</div>
        </div>

        <div class="signature-section">
            <div>Seal Signature of The Contractor</div>
        </div>
    </div>
</body>
</html>
