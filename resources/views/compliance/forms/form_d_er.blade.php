<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM 'D' - Equal Remuneration Register</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 14mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1;
            color: #000;
        }
        .form-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .form-header-title {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
            padding: 2px 0;
        }
        .form-header-rule {
            font-size: 10px;
            margin: 0;
            padding: 1px 0;
        }
        .form-header-subtitle {
            font-size: 11px;
            margin: 0;
            padding: 2px 0;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .info-grid tr {
            border-bottom: 1px solid #000;
        }
        .info-grid td {
            padding: 2px 4px;
            text-align: left;
            line-height: 1.2;
            font-size: 11px;
            vertical-align: middle;
        }
        .info-grid td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .info-grid td:nth-child(2) {
            width: 60%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
            margin-bottom: 0;
        }
        .data-table th {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            line-height: 1.2;
            vertical-align: middle;
        }
        .data-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .column-number-row {
            font-weight: bold;
            background-color: #fff;
        }
        .nil-message {
            text-align: center;
            padding: 20px;
            font-weight: bold;
            font-size: 11px;
        }
        .signature-space {
            margin-top: 100px;
            height: 80px;
            text-align: right
        }
        tr {
            height: 16px;
        }
    </style>
</head>
<body>
    <div class="form-header">
        <div class="form-header-title">FORM 'D'</div>
        <div class="form-header-rule">(See rule 6)</div>
        <div class="form-header-subtitle">Register to be maintained by the employer under Rule 6 of the Equal Remuneration Rules, 1976.</div>
    </div>

    <table class="info-grid">
        <tr>
            <td>Name of the Company</td>
            <td class="text-left">{{ $company_name ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Contractor</td>
            <td class="text-left">{{ $contractor_name ?? '' }}</td>
        </tr>
        <tr>
            <td>Total number of workers employed</td>
            <td class="text-left">{{ $total_workers ?? '' }}</td>
        </tr>
        <tr>
            <td>Total number of men workers employed</td>
            <td class="text-left">{{ $total_men ?? '' }}</td>
        </tr>
        <tr>
            <td>Total number of women workers employed</td>
            <td class="text-left">{{ $total_women ?? '' }}</td>
        </tr>
        <tr>
            <td>Work location</td>
            <td class="text-left">{{ $work_location ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Principal Employer</td>
            <td class="text-left">{{ $principal_employer ?? '' }}</td>
        </tr>
        <tr>
            <td>Month</td>
            <td class="text-left">{{ $month ?? '' }} {{ $year ?? '' }}</td>
        </tr>
    </table>

    @if(($total_women ?? 0) == 0)
    <table class="data-table">
        <colgroup>
            <col style="width: 10%;">
            <col style="width: 12%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 12%;">
        </colgroup>
        <thead>
            <tr>
                <th>Category of workers</th>
                <th>Brief description of work</th>
                <th>No. of men employed</th>
                <th>No. of women employed</th>
                <th>Rate of remuneration paid</th>
                <th>Basic wage or salary</th>
                <th>Dearness allowance</th>
                <th>House rent allowance</th>
                <th>Other allowance</th>
                <th>Cash value of concessional supply of essential commodities</th>
            </tr>
            <tr class="column-number-row">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-left">{{ $row['category'] ?? '' }}</td>
                <td class="text-left">{{ $row['description'] ?? '' }}</td>
                <td class="text-center">{{ $row['men_count'] ?? 0 }}</td>
                <td class="text-center">{{ $row['women_count'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($row['rate_remuneration'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['basic_wage'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['da'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['hra'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['other_allowance'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['cash_value'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <table class="data-table">
        <colgroup>
            <col style="width: 10%;">
            <col style="width: 12%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 12%;">
        </colgroup>
        <thead>
            <tr class="column-number-row">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
            </tr>
            <tr>
                <th>Category of workers</th>
                <th>Brief description of work</th>
                <th>No. of men employed</th>
                <th>No. of women employed</th>
                <th>Rate of remuneration paid</th>
                <th>Basic wage or salary</th>
                <th>Dearness allowance</th>
                <th>House rent allowance</th>
                <th>Other allowance</th>
                <th>Cash value of concessional supply of essential commodities</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="10" class="nil-message">No Women Workers Employed on {{ $month ?? '' }} {{ $year ?? '' }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="signature-space">
        <div>Authorized Signature</div>
    </div>
</body>
</html>
