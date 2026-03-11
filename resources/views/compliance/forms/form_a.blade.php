<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM A - Employee Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 15px;
            font-size: 10px;
        }
        .form-container {
            border: 2px solid black;
            padding: 15px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .form-header div {
            margin: 3px 0;
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
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .establishment-table td:first-child {
            font-weight: bold;
            width: 25%;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 9px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 4px 3px;
            text-align: left;
            vertical-align: top;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            line-height: 1.1;
            font-size: 8px;
            height: 40px;
        }
        .register-table td {
            height: 60px;
            word-wrap: break-word;
        }
        .col-1 { width: 3%; text-align: center; }
        .col-2 { width: 4%; }
        .col-3 { width: 5%; }
        .col-4 { width: 5%; }
        .col-5 { width: 3%; }
        .col-6 { width: 6%; }
        .col-7 { width: 4%; }
        .col-8 { width: 4%; }
        .col-9 { width: 4%; }
        .col-10 { width: 5%; }
        .col-11 { width: 4%; }
        .col-12 { width: 4%; }
        .col-13 { width: 4%; }
        .col-14 { width: 5%; }
        .col-15 { width: 5%; }
        .col-16 { width: 4%; }
        .col-17 { width: 4%; }
        .col-18 { width: 4%; }
        .col-19 { width: 4%; }
        .col-20 { width: 4%; }
        .col-21 { width: 4%; }
        .col-22 { width: 5%; }
        .col-23 { width: 5%; }
        .col-24 { width: 5%; }
        .col-25 { width: 5%; }
        .footer-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        .footer-left {
            flex: 1;
            text-align: left;
        }
        .footer-center {
            flex: 1;
            text-align: center;
        }
        .footer-right {
            flex: 1;
            text-align: right;
        }
        .signature-space {
            margin-top: 40px;
            height: 50px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div>FORM A</div>
            <div>EMPLOYEE REGISTER</div>
            <div>[Part A : For all Establishments]</div>
        </div>

        <table class="establishment-table">
            <tr>
                <td>Name of Establishment</td>
                <td>{{ $establishment_name ?? '' }}</td>
            </tr>
            <tr>
                <td>Nature of Business</td>
                <td>{{ $nature_of_business ?? '' }}</td>
            </tr>
            <tr>
                <td>LIN</td>
                <td>{{ $lin ?? '' }}</td>
            </tr>
        </table>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-1">Sl. No</th>
                    <th class="col-2">Employee Code</th>
                    <th class="col-3">Name of Employee</th>
                    <th class="col-4">Father/Husband Name</th>
                    <th class="col-5">Gender</th>
                    <th class="col-6">Permanent Address</th>
                    <th class="col-7">Nationality</th>
                    <th class="col-8">Date of Birth</th>
                    <th class="col-9">Education Level</th>
                    <th class="col-10">Aadhaar Number</th>
                    <th class="col-11">Date of Joining</th>
                    <th class="col-12">Designation</th>
                    <th class="col-13">Type of Employment</th>
                    <th class="col-14">Mobile Number</th>
                    <th class="col-15">Bank Account Number</th>
                    <th class="col-16">IFSC Code</th>
                    <th class="col-17">UAN</th>
                    <th class="col-18">ESIC Number</th>
                    <th class="col-19">Aadhaar Linked Status</th>
                    <th class="col-20">PAN Number</th>
                    <th class="col-21">Category</th>
                    <th class="col-22">Present Address</th>
                    <th class="col-23">Mark of Identification</th>
                    <th class="col-24">Photograph</th>
                    <th class="col-25">Signature / Thumb Impression</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? $entries ?? [] as $index => $row)
                    <tr>
                        <td class="col-1">{{ $index + 1 }}</td>
                        <td class="col-2">{{ $row['employee_code'] ?? 'NIL' }}</td>
                        <td class="col-3">{{ $row['employee_name'] ?? 'NIL' }}</td>
                        <td class="col-4">{{ $row['father_name'] ?? 'NIL' }}</td>
                        <td class="col-5">{{ $row['gender'] ?? 'NIL' }}</td>
                        <td class="col-6">{{ $row['permanent_address'] ?? 'NIL' }}</td>
                        <td class="col-7">{{ $row['nationality'] ?? 'NIL' }}</td>
                        <td class="col-8">{{ $row['dob'] ?? 'NIL' }}</td>
                        <td class="col-9">{{ $row['education_level'] ?? 'NIL' }}</td>
                        <td class="col-10">{{ $row['aadhaar'] ?? 'NIL' }}</td>
                        <td class="col-11">{{ $row['date_of_joining'] ?? 'NIL' }}</td>
                        <td class="col-12">{{ $row['designation'] ?? 'NIL' }}</td>
                        <td class="col-13">{{ $row['employment_type'] ?? 'NIL' }}</td>
                        <td class="col-14">{{ $row['mobile'] ?? 'NIL' }}</td>
                        <td class="col-15">{{ $row['bank_account'] ?? 'NIL' }}</td>
                        <td class="col-16">{{ $row['ifsc_code'] ?? 'NIL' }}</td>
                        <td class="col-17">{{ $row['uan'] ?? 'NIL' }}</td>
                        <td class="col-18">{{ $row['esic_number'] ?? 'NIL' }}</td>
                        <td class="col-19">{{ $row['aadhaar_linked'] ?? 'NIL' }}</td>
                        <td class="col-20">{{ $row['pan'] ?? 'NIL' }}</td>
                        <td class="col-21">{{ $row['category'] ?? 'NIL' }}</td>
                        <td class="col-22">{{ $row['present_address'] ?? 'NIL' }}</td>
                        <td class="col-23">{{ $row['identification_mark'] ?? 'NIL' }}</td>
                        <td class="col-24"></td>
                        <td class="col-25"></td>
                    </tr>
                @empty
                    @for($i = 0; $i < 9; $i++)
                        <tr>
                            <td class="col-1">{{ $i + 1 }}</td>
                            <td class="col-2">NIL</td>
                            <td class="col-3">NIL</td>
                            <td class="col-4">NIL</td>
                            <td class="col-5">NIL</td>
                            <td class="col-6">NIL</td>
                            <td class="col-7">NIL</td>
                            <td class="col-8">NIL</td>
                            <td class="col-9">NIL</td>
                            <td class="col-10">NIL</td>
                            <td class="col-11">NIL</td>
                            <td class="col-12">NIL</td>
                            <td class="col-13">NIL</td>
                            <td class="col-14">NIL</td>
                            <td class="col-15">NIL</td>
                            <td class="col-16">NIL</td>
                            <td class="col-17">NIL</td>
                            <td class="col-18">NIL</td>
                            <td class="col-19">NIL</td>
                            <td class="col-20">NIL</td>
                            <td class="col-21">NIL</td>
                            <td class="col-22">NIL</td>
                            <td class="col-23">NIL</td>
                            <td class="col-24"></td>
                            <td class="col-25"></td>
                        </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-left">Signature of Principal Employer</div>
            <div class="footer-center"></div>
            <div class="footer-right">Seal / Signature of Contractor</div>
        </div>

        <div class="signature-space"></div>
    </div>
</body>
</html>
