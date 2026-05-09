<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM 'D' - Equal Remuneration Register</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #ccc; }

        .page-wrapper { display: flex; justify-content: center; }
        .page-container { width: 90%; margin: 20px auto; background: #fff; border: 2px solid #000; padding: 8px 0 0 0; }

        .title-block { text-align: center; margin-bottom: 8px; }
        .title-block .title    { font-weight: bold; font-size: 14px; }
        .title-block .rule     { font-size: 10px; }
        .title-block .subtitle { font-size: 11px; font-weight: bold; }

        .form-d {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: none;
            font-family: serif;
        }
        .form-d td,
        .form-d th {
            border-top: 0.8px solid #000;
            border-bottom: 0.8px solid #000;
            border-left: 0.8px solid #000;
            border-right: 0.8px solid #000;
            padding: 4px;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }
        /* All 4 outer edges connect to page-container box — no table border of its own */
        .form-d tr td:first-child,
        .form-d tr th:first-child { border-left: none; }
        .form-d tr td:last-child,
        .form-d tr th:last-child  { border-right: none; }
        .form-d tr:first-child td,
        .form-d tr:first-child th { border-top: none; }
        .form-d tr:last-child td,
        .form-d tr:last-child th  { border-bottom: none; }

        /* Meta rows — full-width ruled lines, text indented via span */
        .form-d tr.meta-row td {
            border-left: none !important;
            border-right: none !important;
            border-top: 0.8px solid #000;
            border-bottom: 0.8px solid #000;
            text-align: left;
            padding: 0;
        }
        .meta-text { display: inline-block; padding: 4px 6px; }

        /* Month row */
        .form-d tr.month td {
            border-left: none;
            border-right: none;
            text-align: center;
            font-size: 12px;
            padding: 4px 0;
        }

        /* Column header row */
        .form-d tr.header th {
            font-size: 10px;
            font-weight: 600;
            background: transparent;
            white-space: normal;
            word-break: break-word;
            line-height: 1.2;
            padding: 5px 4px;
            height: 65px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
        }
        .form-d tr.header th:nth-child(1),
        .form-d tr.header th:nth-child(2) { text-align: left; padding-left: 6px; }

        /* Column numbers row */
        .form-d tr.numbers td {
            font-size: 10px;
            padding: 3px 0;
            height: 20px;
            font-weight: bold;
            background: transparent;
            text-align: center;
            border-top: none;
            border-bottom: 1px solid #000;
        }

        /* Data rows */
        .form-d tr.data td       { font-size: 9px; height: 28px; white-space: nowrap; text-overflow: ellipsis; text-align: center; }
        .form-d tr.data td.left  { text-align: left; padding-left: 6px; }
        .form-d tr.data td.right { text-align: right; }

        /* No-data row */
        .form-d tr.no-data td {
            border-left: none;
            border-right: none;
            border-top: 0.8px solid #000;
            border-bottom: 0.8px solid #000;
            height: 60px;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
            padding-left: 0;
            padding-right: 0;
        }

        .signature-block {
            text-align: right;
            margin-top: 30px;
            padding: 0 20px 10px 0;
        }
        .signature-block .for-text  { font-size: 12px; margin-bottom: 10px; }
        .signature-block .sig-image img { width: 120px; height: auto; margin: 5px 0; }
        .signature-block .designation  { font-size: 11px; margin-top: 8px; }

        @media print {
            body { background: #fff; margin: 0; }
            .page-wrapper { display: block; }
            .page-container { width: 100%; margin: 0; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
<div class="page-container">

    <div class="title-block">
        <div class="title">FORM 'D'</div>
        <div class="rule">(See rule 6)</div>
        <div class="subtitle">Register to be maintained by the employer under Rule 6 of the Equal Remuneration Rules, 1976.</div>
    </div>

    <table class="form-d">
        <colgroup>
            <col style="width:14%">
            <col style="width:14%">
            <col style="width:9%">
            <col style="width:9%">
            <col style="width:11%">
            <col style="width:11%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:8%">
        </colgroup>
        <tbody>
            {{-- Meta rows: single full-width ruled line per field --}}
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Name of the Company:</strong> {{ $company_name ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Name of the Contractor:</strong> {{ $contractor_name ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Total number of workers employed:</strong> {{ $total_workers ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Total number of men workers employed:</strong> {{ $total_men ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Total number of women workers employed:</strong> {{ $total_women ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Work location:</strong> {{ $work_location ?? '' }}</span></td></tr>
            <tr class="meta-row"><td colspan="10"><span class="meta-text"><strong>Name of the Principal Employer:</strong> {{ $principal_employer ?? '' }}</span></td></tr>

            {{-- Month row --}}
            <tr class="month"><td colspan="10">{{ $month ?? '' }} {{ $year ?? '' }}</td></tr>

            {{-- Column headers --}}
            <tr class="header">
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

            {{-- Column numbers --}}
            <tr class="numbers">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td>
                <td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
            </tr>

            {{-- Data rows --}}
            @if(($total_women ?? 0) == 0)
            <tr class="no-data"><td colspan="10">No Women Workers Employed on {{ $month ?? '' }} {{ $year ?? '' }}</td></tr>
            @else
                @forelse($rows ?? [] as $row)
                <tr class="data">
                    <td class="left">{{ $row['category'] ?? '' }}</td>
                    <td class="left">{{ $row['description'] ?? '' }}</td>
                    <td>{{ $row['men_count'] ?? 0 }}</td>
                    <td>{{ $row['women_count'] ?? 0 }}</td>
                    <td class="right">{{ number_format($row['rate_remuneration'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['basic_wage'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['da'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['hra'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['other_allowance'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['cash_value'] ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr class="no-data"><td colspan="10">No records found.</td></tr>
                @endforelse
            @endif
        </tbody>
    </table>

    <div class="signature-block">
        <div class="for-text">For {{ $company_name ?? '' }}</div>
        @if(!empty($signature ?? ''))
        <div class="sig-image"><img src="{{ $signature }}" alt="signature"></div>
        @else
        <div style="height:40px;"></div>
        @endif
        <div class="designation">Authorized Signatory</div>
    </div>

</div>{{-- /page-container --}}
</div>{{-- /page-wrapper --}}
</body>
</html>
