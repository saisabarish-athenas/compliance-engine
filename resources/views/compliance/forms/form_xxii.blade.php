<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XXII - Register of Advances</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6px;
            padding: 0;
            margin: 0;
            background: #fff;
            color: #000;
        }
        .form-container {
            border: 1.5px solid #000;
            width: 110%;
            transform: scale(0.55);
            transform-origin: top center;
            margin: 40px auto 0;
        }

        /* Title */
        .form-title {
            text-align: center;
            padding: 2px 1px;
            border-bottom: 1px solid #000;
            line-height: 1.4;
        }
        .form-title .t1 { font-size: 8px; font-weight: bold; }
        .form-title .t2 { font-size: 6px; }
        .form-title .t3 { font-size: 7px; font-weight: bold; }

        /* All tables */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th {
            border: 1px solid #000;
            padding: 1px 2px;
            font-size: 6px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
        }

        /* Info rows 1 & 2 — two-cell */
        .info-table tr.two-cell td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 1px 2px; white-space: normal; vertical-align: middle;
        }
        .info-table tr.two-cell td.lbl {
            font-weight: bold; font-size: 6px; width: 32%;
            border-right: 1px solid #000;
        }
        .info-table tr.two-cell td.val { font-size: 6px; width: 68%; }

        /* Info rows 3 & 4 — full-width single cell */
        .info-table tr.one-cell td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 1px 2px; white-space: normal; font-size: 6px;
        }
        .info-table tr.one-cell td span.lbl { font-weight: bold; }

        /* Month & Year */
        .month-table td {
            border-top: none; border-left: none; border-right: none;
            border-bottom: 1px solid #000;
            padding: 1px 2px; font-size: 6px; vertical-align: middle;
        }
        .month-table td.lbl { font-weight: bold; width: 18%; white-space: nowrap; }
        .month-table td.val { width: 82%; }

        /* Register table */
        .reg-table { border-top: none; }
        .reg-table td, .reg-table th {
            border: 1px solid #000; padding: 1px 2px;
            font-size: 6px; vertical-align: middle;
            overflow: hidden; word-wrap: break-word;
        }

        /* Column number row */
        .reg-table .num-row td {
            text-align: center; font-weight: bold; font-size: 6px;
            padding: 1px; height: 10px; color: #000; background: #fff;
        }

        /* Column heading row */
        .reg-table .hdr-row th {
            text-align: center; font-weight: bold; font-size: 5.5px;
            white-space: normal; line-height: 1.2; padding: 1px;
            color: #000; background: #fff; vertical-align: middle;
        }

        /* Data rows */
        .reg-table tbody td {
            font-size: 6px; height: 11px;
            white-space: normal; overflow: hidden;
            word-wrap: break-word;
        }

        /* Cell alignment */
        .td-sl   { text-align: center; font-weight: bold; color: #000; }
        .td-name { text-align: left; font-weight: bold; color: #000; white-space: normal; }
        .td-left { text-align: left; color: #000; white-space: normal; }
        .td-ctr  { text-align: center; color: #000; font-weight: bold; white-space: normal; }

        /* Empty state */
        .nil-row td { text-align: center; font-weight: bold; font-size: 6px; height: 11px; color: #000; }

        /* Column widths — total 100% */
        .c1  { width: 1%;  max-width: 1%; }
        .c2  { width: 30%; }
        .c3  { width: 11%; }
        .c4  { width: 11%; }
        .c5  { width: 8%;  }
        .c6  { width: 8%;  }
        .c7  { width: 8%;  }
        .c8  { width: 8%;  }
        .c9  { width: 8%;  }
        .c10 { width: 5%;  }
        .c11 { width: 2%;  }

        /* Footer rows inside reg-table tfoot */
        .tfoot-note { text-align: left; font-style: italic; padding: 3px 6px; border: 1px solid #000; font-size: 6px; vertical-align: middle; }
        .tfoot-nil  { text-align: center; font-weight: bold; padding: 3px 6px; border: 1px solid #000; font-size: 6px; vertical-align: middle; }
        .tfoot-sig  { text-align: right; padding: 3px 6px; border: 1px solid #000; font-size: 6px; vertical-align: bottom; height: 70px; }
        .sig-label  { font-size: 6px; font-weight: bold; color: #000; text-align: right; display: block; }
    </style>
</head>
<body>
<div class="form-container">

    {{-- TITLE --}}
    <div class="form-title">
        <div class="t1">FORM XXII</div>
        <div class="t2">[See Rule 78(2)(d)]</div>
        <div class="t3">Register of Advances</div>
    </div>

    {{-- INFO ROWS --}}
    <table class="info-table">
        <tbody>
            <tr class="two-cell">
                <td class="lbl">NAME AND ADDRESS OF CONTRACTOR :</td>
                <td class="val">{{ $header['contractor_name'] ?? '' }}</td>
            </tr>
            <tr class="two-cell">
                <td class="lbl">NATURE AND LOCATION OF WORK :</td>
                <td class="val">{{ $header['work_nature'] ?? '' }}</td>
            </tr>
            <tr class="one-cell">
                <td colspan="2">
                    <span class="lbl">NAME AND ADDRESS OF ESTABLISHMENT IN/UNDER WHICH CONTRACT IS CARRIED ON :</span>
                    {{ $header['establishment_name'] ?? '' }}
                </td>
            </tr>
            <tr class="one-cell">
                <td colspan="2">
                    <span class="lbl">NAME AND ADDRESS OF PRINCIPAL EMPLOYER :</span>
                    {{ $header['principal_employer'] ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- MONTH & YEAR --}}
    <table class="month-table">
        <tbody>
            <tr>
                <td class="lbl">Month &amp; Year:</td>
                <td class="val">{{ $header['month_year'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- REGISTER TABLE --}}
    <table class="reg-table">
        <colgroup>
            <col class="c1"><col class="c2"><col class="c3"><col class="c4">
            <col class="c5"><col class="c6"><col class="c7"><col class="c8">
            <col class="c9"><col class="c10"><col class="c11">
        </colgroup>
        <thead>
            <tr class="num-row">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td>
                <td>7</td><td>8</td><td>9</td><td>10</td><td>11</td>
            </tr>
            <tr class="hdr-row">
                <th>SL NO</th>
                <th>Name of workman</th>
                <th>Father's/Husban d's name</th>
                <th>Designation/<br>Nature of<br>employment</th>
                <th>Date and<br>Amount of<br>Advance Given</th>
                <th>Date and<br>Amount of<br>Advance Given</th>
                <th>Purpose (S) for<br>Which Advance<br>Made</th>
                <th>No. Of<br>Instalments by<br>Which Advance to<br>Be Repaid</th>
                <th>Date and<br>Amount of Each<br>Instalment<br>Repaid</th>
                <th>Date on Which Last<br>Instalment Was<br>Repaid</th>
                <th>Signature of<br>Thumb Impresion<br>of Workman</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($rows) && count($rows) > 0)
                @foreach($rows as $i => $row)
                <tr>
                    <td class="td-sl">{{ $i + 1 }}</td>
                    <td class="td-name">{{ strtoupper($row['name'] ?? 'NIL') }}</td>
                    <td class="td-left">{{ strtoupper($row['father_name'] ?? 'NIL') }}</td>
                    <td class="td-left">{{ strtoupper($row['designation'] ?? 'NIL') }}</td>
                    <td class="td-ctr">{{ $row['advance_date_amount_1'] ?? 'NIL' }}</td>
                    <td class="td-ctr">{{ $row['advance_date_amount_2'] ?? 'NIL' }}</td>
                    <td class="td-ctr">{{ $row['purpose'] ?? 'NIL' }}</td>
                    <td class="td-ctr">{{ $row['installments'] ?? 'NIL' }}</td>
                    <td class="td-ctr">{{ $row['installment_repaid'] ?? 'NIL' }}</td>
                    <td class="td-ctr">{{ $row['last_installment_date'] ?? 'NIL' }}</td>
                    <td class="td-ctr"></td>
                </tr>
                @endforeach
            @else
                <tr class="nil-row">
                    <td colspan="11">NIL</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="tfoot-note">*Applicable only in case of damage/loss/fine</td>
                <td colspan="6" class="tfoot-nil">
                    @if((isset($is_nil) && $is_nil) || (isset($all_nil_advances) && $all_nil_advances))
                        Nil for the month of {{ $header['month_year'] ?? '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="11" class="tfoot-sig">
                    @if(isset($company_signature) && $company_signature)
                        <img src="{{ storage_path('app/' . $company_signature) }}" style="width:45px;height:45px;object-fit:contain;" alt="Seal">
                    @endif
                    <span class="sig-label">Seal Signature of The Contractor</span>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- FOOTER (inside reg-table as tfoot) --}}

</div>
</body>
</html>
