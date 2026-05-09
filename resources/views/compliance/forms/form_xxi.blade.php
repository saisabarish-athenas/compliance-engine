<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XXI - Register of Fines</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 landscape; margin: 8mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
            background: #fff;
        }

        /* ── OUTER WRAPPER ── */
        .form-wrapper {
            width: 820px;
            max-width: 820px;
            margin: 40px auto 20px;
            border: 1.5px solid #000;
            padding: 0;
        }

        /* ── TITLE BLOCK ── */
        .title-block {
            text-align: center;
            padding: 4px 0 3px;
            border-bottom: 1px solid #000;
            line-height: 1.5;
        }
        .title-block .t1 { font-size: 9px; font-weight: bold; }
        .title-block .t2 { font-size: 8px; font-weight: normal; }
        .title-block .t3 { font-size: 9px; font-weight: bold; }

        /* ── HEADER TABLE ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .header-table tr td {
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            font-size: 8px;
            vertical-align: middle;
            line-height: 1.3;
        }
        .header-table tr td:first-child {
            width: 28%;
            font-weight: bold;
            border-right: none;
        }
        .header-table tr td:last-child {
            width: 72%;
        }

        /* ── MAIN REGISTER TABLE ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* column-number row */
        .main-table thead tr.col-num th {
            border: 1px solid #000;
            font-size: 7px;
            font-weight: normal;
            text-align: center;
            padding: 1px 0;
            height: 12px;
            background: #fff;
        }

        /* column header row */
        .main-table thead tr.col-hdr th {
            border: 1px solid #000;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            word-break: normal;
            overflow-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
            padding: 2px 2px;
            height: 46px;
            background: #fff;
        }
        .main-table thead tr.col-hdr th:nth-child(2),
        .main-table thead tr.col-hdr th:nth-child(3),
        .main-table thead tr.col-hdr th:nth-child(4) {
            text-align: left;
            padding-left: 3px;
        }

        /* data rows */
        .main-table tbody td {
            border: 1px solid #000;
            font-size: 7.5px;
            padding: 1px 3px;
            text-align: center;
            vertical-align: middle;
            height: 15px;
            line-height: 1.2;
        }
        .main-table tbody td:nth-child(2),
        .main-table tbody td:nth-child(3),
        .main-table tbody td:nth-child(4) {
            text-align: left;
        }

        /* footer split row */
        .main-table tbody tr.footer-row td {
            border: 1px solid #000;
            font-size: 7.5px;
            padding: 2px 5px;
            vertical-align: middle;
            height: auto;
        }
        .fn-note { text-align: left; font-style: italic; }
        .fn-nil  { text-align: center; font-weight: bold; }

        /* ── SIGNATURE BLOCK ── */
        .sig-area {
            padding: 6px 8px 10px 0;
            margin-top: 30px;
            text-align: right;
            font-size: 8px;
        }
        .sig-content {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .sig-content .seal-img {
            width: 60px;
            height: auto;
            display: block;
        }
        .sig-content .sign-img {
            width: 100px;
            height: auto;
            display: block;
        }
        .sig-label {
            margin-top: 4px;
            font-size: 8px;
            text-align: right;
        }

        @media print {
            body  { margin: 0; }
            .form-wrapper { width: 100%; max-width: 100%; margin: 0; border: 1.5px solid #000; }
        }
    </style>
</head>
<body>
<div class="form-wrapper">

    {{-- ── TITLE ── --}}
    <div class="title-block">
        <div class="t1">FORM XXI</div>
        <div class="t2">[See Rule 78(2)(d)]</div>
        <div class="t3">Register of Fines</div>
    </div>

    {{-- ── HEADER TABLE (bordered rows, 2-col) ── --}}
    <table class="header-table">
        <tr>
            <td>NAME AND ADDRESS OF CONTRACTOR :</td>
            <td>{{ $header['contractor_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NATURE AND LOCATION OF WORK :</td>
            <td>{{ $header['work_nature'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</td>
            <td>{{ $header['establishment_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</td>
            <td>{{ $header['principal_employer'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Month &amp; Year:</td>
            <td>{{ $header['month_year'] ?? '' }}</td>
        </tr>
    </table>

    {{-- ── MAIN REGISTER TABLE ── --}}
    <table class="main-table">
        <colgroup>
            <col style="width:4%">
            <col style="width:13%">
            <col style="width:13%">
            <col style="width:13%">
            <col style="width:9%">
            <col style="width:7%">
            <col style="width:9%">
            <col style="width:13%">
            <col style="width:8%">
            <col style="width:7%">
            <col style="width:7%">
            <col style="width:5%">
        </colgroup>

        <thead>
            <tr class="col-num">
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>
                <th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th>
            </tr>
            <tr class="col-hdr">
                <th>SL<br>NO</th>
                <th>Name of workmen</th>
                <th>Father's/Husband's<br>name</th>
                <th>Designation/Nature<br>of employment</th>
                <th>Act/Omission<br>for which fine<br>imposed</th>
                <th>Date of<br>offence</th>
                <th>Whether workmen<br>showed against<br>fine</th>
                <th>Name of person in whose<br>presence employer's<br>explanation was heard</th>
                <th>Wage period<br>and wages<br>payable</th>
                <th>Amount of<br>fine imposed</th>
                <th>Date on which<br>fine realised</th>
                <th>Re-<br>marks</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows ?? [] as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['name'] ?? '' }}</td>
                <td>{{ $row['father_name'] ?? '' }}</td>
                <td>{{ $row['designation'] ?? '' }}</td>
                <td>{{ $row['act_or_omission'] ?? 'NIL' }}</td>
                <td>{{ $row['date_of_offence'] ?? 'NIL' }}</td>
                <td>{{ $row['showed_cause'] ?? 'NIL' }}</td>
                <td>{{ $row['heard_by'] ?? 'NIL' }}</td>
                <td>{{ $row['wage_period'] ?? 'NIL' }}</td>
                <td>{{ $row['fine_amount'] ?? 'NIL' }}</td>
                <td>{{ $row['fine_realised'] ?? 'NIL' }}</td>
                <td>{{ $row['remarks'] ?? '-' }}</td>
            </tr>
            @empty
            @endforelse

            {{-- footer: note left | nil right ── --}}
            <tr class="footer-row">
                <td colspan="4" class="fn-note">*Applicable only in case of damage/loss/fine</td>
                <td colspan="8" class="fn-nil">Nil for the month of &nbsp;{{ $header['month_year'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── SIGNATURE ── --}}
    <div class="sig-area">
        <div class="sig-content">
            @if(!empty($seal_path))
                <img src="{{ $seal_path }}" class="seal-img" alt="seal">
            @endif
            @if(!empty($signature_path))
                <img src="{{ $signature_path }}" class="sign-img" alt="signature">
            @endif
        </div>
        <div class="sig-label">Seal Signature of The Contractor</div>
    </div>

</div>
</body>
</html>
