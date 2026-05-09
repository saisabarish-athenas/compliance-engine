@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 11 - Notice of Periods of Work</title>
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
        .factory-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border: 1px solid black;
        }
        .factory-details td {
            border: 1px solid black;
            padding: 3px 4px;
            font-size: 9px;
        }
        .factory-details td:first-child {
            font-weight: bold;
            width: 25%;
        }
        .work-periods-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 8px;
            margin-bottom: 8px;
        }
        .work-periods-table th,
        .work-periods-table td {
            border: 1px solid black;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .work-periods-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            line-height: 1.1;
            height: 18px;
        }
        .work-periods-table td {
            height: 16px;
        }
        .label-col {
            text-align: left;
            font-weight: bold;
            width: 12%;
        }
        .group-header {
            font-weight: bold;
            text-align: center;
        }
        .time-col {
            width: 6%;
        }
        .footer-section {
            margin-top: 10px;
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
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 11</div>
            <div>(Prescribed under Rule 79)</div>
            <div class="header-title">Notice of Periods of work for adult workers and children</div>
        </div>

        <!-- Factory Details -->
        <table class="factory-details">
            <tr>
                <td>Name of factory</td>
                <td>{{ $factory_name ?? 'NIL' }}</td>
            </tr>
            <tr>
                <td>Place</td>
                <td>{{ $place ?? 'NIL' }}</td>
            </tr>
            <tr>
                <td>District</td>
                <td>{{ $district ?? 'NIL' }}</td>
            </tr>
        </table>

        <!-- Work Periods Table -->
        <table class="work-periods-table">
            <thead>
                <tr>
                    <th class="label-col">Periods of work</th>
                    <th colspan="3" class="group-header">Men</th>
                    <th colspan="1" class="group-header">Total Men</th>
                    <th colspan="3" class="group-header">Women</th>
                    <th colspan="1" class="group-header">Total Women</th>
                    <th colspan="3" class="group-header">Children</th>
                    <th colspan="1" class="group-header">Total Children</th>
                </tr>
                <tr>
                    <th class="label-col"></th>
                    <th class="time-col">A</th>
                    <th class="time-col">B</th>
                    <th class="time-col">C</th>
                    <th class="time-col">Total</th>
                    <th class="time-col">D</th>
                    <th class="time-col">E</th>
                    <th class="time-col">F</th>
                    <th class="time-col">Total</th>
                    <th class="time-col">G</th>
                    <th class="time-col">H</th>
                    <th class="time-col">I</th>
                    <th class="time-col">Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- On working days -->
                <tr>
                    <td class="label-col" rowspan="2"><strong>On working days</strong></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                </tr>
                <tr>
                    <td>{{ $men_a_from ?? 'NIL' }}</td>
                    <td>{{ $men_a_to ?? 'NIL' }}</td>
                    <td>{{ $men_b_from ?? 'NIL' }}</td>
                    <td>{{ $men_total ?? 'NIL' }}</td>
                    <td>{{ $women_d_from ?? 'NIL' }}</td>
                    <td>{{ $women_d_to ?? 'NIL' }}</td>
                    <td>{{ $women_e_from ?? 'NIL' }}</td>
                    <td>{{ $women_total ?? 'NIL' }}</td>
                    <td>{{ $children_g_from ?? 'NIL' }}</td>
                    <td>{{ $children_g_to ?? 'NIL' }}</td>
                    <td>{{ $children_h_from ?? 'NIL' }}</td>
                    <td>{{ $children_total ?? 'NIL' }}</td>
                </tr>
                <!-- On partial working days -->
                <tr>
                    <td class="label-col" rowspan="2"><strong>On partial working days</strong></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">From</td>
                    <td colspan="1" style="text-align: left; font-weight: bold;">To</td>
                    <td colspan="1"></td>
                    <td colspan="1"></td>
                </tr>
                <tr>
                    <td>{{ $men_c_from ?? 'NIL' }}</td>
                    <td>{{ $men_c_to ?? 'NIL' }}</td>
                    <td>{{ $men_partial_total ?? 'NIL' }}</td>
                    <td></td>
                    <td>{{ $women_f_from ?? 'NIL' }}</td>
                    <td>{{ $women_f_to ?? 'NIL' }}</td>
                    <td>{{ $women_partial_total ?? 'NIL' }}</td>
                    <td></td>
                    <td>{{ $children_i_from ?? 'NIL' }}</td>
                    <td>{{ $children_i_to ?? 'NIL' }}</td>
                    <td>{{ $children_partial_total ?? 'NIL' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Description of Groups Table -->
        <table class="work-periods-table" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="width: 15%;">Description of groups</th>
                    <th style="width: 10%;">Group letter</th>
                    <th style="width: 75%;">Nature of work</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Group A</td>
                    <td>A</td>
                    <td style="text-align: left;">{{ $group_a_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group B</td>
                    <td>B</td>
                    <td style="text-align: left;">{{ $group_b_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group C</td>
                    <td>C</td>
                    <td style="text-align: left;">{{ $group_c_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group D</td>
                    <td>D</td>
                    <td style="text-align: left;">{{ $group_d_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group E</td>
                    <td>E</td>
                    <td style="text-align: left;">{{ $group_e_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group F</td>
                    <td>F</td>
                    <td style="text-align: left;">{{ $group_f_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group G</td>
                    <td>G</td>
                    <td style="text-align: left;">{{ $group_g_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group H</td>
                    <td>H</td>
                    <td style="text-align: left;">{{ $group_h_work ?? 'NIL' }}</td>
                </tr>
                <tr>
                    <td>Group I</td>
                    <td>I</td>
                    <td style="text-align: left;">{{ $group_i_work ?? 'NIL' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                <div>Date on which this notice was first exhibited: {{ $notice_date ?? 'NIL' }}</div>
                <div>Weekly holidays: {{ $weekly_holidays ?? 'NIL' }}</div>
            </div>
            <div class="footer-right">
                <div class="signature-line"></div>
                <div class="signature-label">(Signed) Manager</div>
            </div>
        </div>
    </div>
</body>
</html>
