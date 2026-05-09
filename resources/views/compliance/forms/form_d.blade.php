<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Form D - Register of Attendance</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10px;
    color: #000;
}

.form-wrapper {
    border: 2px solid #000;
    margin-top: 25px;
    padding: 10px 12px;
    width: 95%;
    margin-left: auto;
    margin-right: auto;
}

@media print {
    .form-wrapper { margin-top: 20px; }
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-family: 'Times New Roman', Times, serif;
    font-size: 10px;
}

.attendance-table th,
.attendance-table td {
    border: 1px solid #000;
    padding: 2px 3px;
    text-align: center;
    vertical-align: middle;
    overflow: hidden;
    white-space: nowrap;
    line-height: 1.2;
    box-sizing: border-box;
}

.attendance-table tr { height: 20px; }

/* Title rows */
.row-title td {
    border: none;
    text-align: center;
    padding: 2px 0;
    font-family: 'Times New Roman', Times, serif;
}

/* Header info rows */
.header-row td {
    border: none;
    vertical-align: middle;
    line-height: 1.4;
    height: 22px;
}

.label-cell {
    text-align: left;
    font-weight: 600;
    padding: 4px 6px;
    white-space: nowrap;
    font-size: 11px;
}

.value-cell {
    text-align: left;
    padding: 4px 6px 4px 10px;
    font-size: 11px;
}

/* Main header row */
.main-header-row th {
    background-color: #d9e7f5;
    font-weight: bold;
    font-size: 8px;
    text-align: center;
    vertical-align: middle;
    height: 32px;
    white-space: normal;
    line-height: 1.3;
    padding: 2px 1px;
}

/* Day numbers row */
.day-row th {
    background-color: #e8f4f8;
    font-weight: bold;
    font-size: 8px;
    text-align: center;
    vertical-align: middle;
    height: 16px;
    padding: 1px 0;
}

/* Data rows */
.attendance-table tbody td { height: 18px; padding: 1px 2px; font-size: 10px; }
.attendance-table tbody td.day-cell { padding: 0; font-size: 9px; }

.td-name,  .th-name  { text-align: left !important; padding-left: 4px !important; font-size: 10px; }
.td-desig, .th-desig { text-align: left !important; padding-left: 4px !important; font-size: 10px; }

/* Weekly off highlight */
.wo { background-color: #f4a742; font-weight: bold; }

/* Total row */
.tr-total td {
    font-weight: bold;
    background-color: #f0f0f0;
    border-top: 2px solid #000;
    text-align: center;
    padding: 2px 3px;
}

.tr-total td:first-child { text-align: left; padding-left: 6px; }

/* Signature block */
.signature-block {
    margin-top: 40px;
    height: 100px;
    text-align: center;
    position: relative;
}

.signature-text {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    font-size: 11px;
}

@media print {
    .signature-block { margin-top: 30px; height: 90px; }
}
</style>
</head>
<body>

<div class="form-wrapper">
<table class="attendance-table">
    <colgroup>
        <col style="width:3%">
        <col style="width:13%">
        <col style="width:9%">
        <col span="31" style="width:1.45%">
        <col style="width:5%">
        <col style="width:5%">
        <col style="width:5%">
        <col style="width:5%">
        <col style="width:5%">
        <col style="width:5%">
        <col style="width:4%">
    </colgroup>

    <thead>
        {{-- Title --}}
        <tr class="row-title">
            <td colspan="41" style="font-size:13px; font-weight:bold; letter-spacing:1px; padding:4px 0 2px;">FORM D</td>
        </tr>
        <tr class="row-title">
            <td colspan="41" style="font-size:11px; font-weight:bold; padding-bottom:2px;">REGISTER OF ATTENDANCE</td>
        </tr>
        <tr class="row-title">
            <td colspan="41" style="font-size:9px; padding-bottom:4px;">[See Rule 2(1) of the Tamil Nadu Shops and Establishments Rules]</td>
        </tr>

        {{-- Establishment info --}}
        <tr class="header-row">
            <td colspan="8" class="label-cell">Name of the Establishment</td>
            <td colspan="33" class="value-cell">{{ $header['establishment_name'] ?? '' }}</td>
        </tr>
        <tr class="header-row">
            <td colspan="8" class="label-cell">Name of Owner / Manager</td>
            <td colspan="33" class="value-cell">{{ $header['owner_name'] ?? '' }}</td>
        </tr>
        <tr class="header-row">
            <td colspan="8" class="label-cell">For the Period</td>
            <td colspan="33" class="value-cell">{{ $header['month_name'] ?? '' }} {{ $header['year'] ?? '' }}</td>
        </tr>

        {{-- Spacer --}}
        <tr><td colspan="41" style="border:none; height:6px;"></td></tr>

        {{-- Grid header: rowspan for left+right, colspan for DATE --}}
        <tr class="main-header-row">
            <th rowspan="2">S.No</th>
            <th rowspan="2" class="th-name">Name of Employee</th>
            <th rowspan="2" class="th-desig">Designation</th>
            <th colspan="31">DATE</th>
            <th rowspan="2">Total<br>Present<br>Days</th>
            <th rowspan="2">Paid<br>Holi-<br>days</th>
            <th rowspan="2">Paid<br>Leave</th>
            <th rowspan="2">Weekly<br>Off</th>
            <th rowspan="2">Absent<br>Days</th>
            <th rowspan="2">Total<br>Days</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr class="day-row">
            @for ($d = 1; $d <= 31; $d++)
                <th>{{ $d }}</th>
            @endfor
        </tr>
    </thead>

    <tbody>
        @forelse ($rows ?? [] as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="td-name">{{ $row['employee_name'] ?? '' }}</td>
            <td class="td-desig">{{ $row['designation'] ?? '' }}</td>
            @for ($d = 1; $d <= 31; $d++)
                @php $m = $row['day_' . $d] ?? ''; @endphp
                @if (in_array($m, ['W/O', 'WO']))
                    <td class="wo day-cell">W/O</td>
                @else
                    <td class="day-cell">{{ $m }}</td>
                @endif
            @endfor
            <td>{{ $row['total_present'] ?? 0 }}</td>
            <td>{{ $row['paid_holidays'] ?? 0 }}</td>
            <td>{{ $row['paid_leave']    ?? 0 }}</td>
            <td>{{ $row['weekly_off']   ?? 0 }}</td>
            <td>{{ $row['absent_days']  ?? 0 }}</td>
            <td>{{ $row['total_days']   ?? 0 }}</td>
            <td>{{ $row['remarks']      ?? '' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="41" style="text-align:center; padding:4pt;">No records found for this period.</td>
        </tr>
        @endforelse

        {{-- Totals --}}
        <tr class="tr-total">
            <td colspan="34">TOTAL</td>
            <td>{{ $totals['total_present'] ?? 0 }}</td>
            <td>{{ $totals['paid_holidays'] ?? 0 }}</td>
            <td>{{ $totals['paid_leave']    ?? 0 }}</td>
            <td>{{ $totals['weekly_off']   ?? 0 }}</td>
            <td>{{ $totals['absent_days']  ?? 0 }}</td>
            <td>{{ $totals['total_days']   ?? 0 }}</td>
            <td>—</td>
        </tr>

    </tbody>
</table>

<div class="signature-block"></div>

</div>
</body>
</html>
