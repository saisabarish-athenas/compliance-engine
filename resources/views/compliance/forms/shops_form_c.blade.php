@extends('compliance.layouts.preview')

﻿<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM C - Bonus Register</title>
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
        .details-section {
            margin-bottom: 8px;
            font-size: 9px;
        }
        .detail-row {
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }
        .detail-label {
            font-weight: bold;
            width: 9%;
            margin-right: 5px;
        }
        .detail-line {
            flex: 1;
            border-bottom: 1px solid black;
            height: 10px;
            padding-left: 3px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            height: 16px;
            word-wrap: break-word;
            word-break: break-word;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            line-height: 1;
        }
        .register-table td {
            text-align: left;
        }
        .col-sl {
            width: 4%;
        }
        .col-name {
            width: 12%;
        }
        .col-father {
            width: 10%;
        }
        .col-age {
            width: 10%;
        }
        .col-designation {
            width: 8%;
        }
        .col-days {
            width: 7%;
        }
        .col-wages {
            width: 9%;
        }
        .col-bonus {
            width: 9%;
        }
        .col-puja {
            width: 8%;
        }
        .col-interim {
            width: 8%;
        }
        .col-tax {
            width: 7%;
        }
        .col-deduction {
            width: 10%;
        }
        .col-total {
            width: 8%;
        }
        .col-net {
            width: 8%;
        }
        .col-paid {
            width: 7%;
        }
        .col-date {
            width: 7%;
        }
        .col-sign {
            width: 8%;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div class="header-title">FORM C</div>
            <div>See Rule 4(c)</div>
            <div class="header-title">BONUS PAID TO EMPLOYEES FOR THE ACCOUNTING YEAR ENDING ON __________</div>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <div class="detail-row">
                <div class="detail-label">Name of the establishment</div>
                <div class="detail-line"></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">No. of working days in the year</div>
                <div class="detail-line"></div>
            </div>
        </div>

        <!-- Bonus Register Table -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sl">Sr. No.</th>
                    <th class="col-name">Name of the employee</th>
                    <th class="col-father">Father's Name</th>
                    <th class="col-age">Whether he has completed 15 years of age at the beginning of the accounting year</th>
                    <th class="col-designation">Designation</th>
                    <th class="col-days">No. of days worked in the year</th>
                    <th class="col-wages">Total salary or wage in respect of the accounting year</th>
                    <th class="col-bonus">Amount of bonus payable under section 10 or section 11 as the case may be</th>
                    <th class="col-puja">Puja bonus or other customary bonus paid during the accounting year</th>
                    <th class="col-interim">Interim bonus or bonus paid in advance</th>
                    <th class="col-tax">Amount of Income-tax deducted</th>
                    <th class="col-deduction">Deduction on account of financial loss, if any, caused by misconduct of the employees</th>
                    <th class="col-total">Total sum deducted under Columns 9, 10, 10A and 11</th>
                    <th class="col-net">Net amount payable (Column 8 minus Column 12)</th>
                    <th class="col-paid">Amount actually paid</th>
                    <th class="col-date">Date on which paid</th>
                    <th class="col-sign">Signature / Thumb impression of the employees</th>
                </tr>
                <tr>
                    <th class="col-sl text-center">(1)</th>
                    <th class="col-name text-center">(2)</th>
                    <th class="col-father text-center">(3)</th>
                    <th class="col-age text-center">(4)</th>
                    <th class="col-designation text-center">(5)</th>
                    <th class="col-days text-center">(6)</th>
                    <th class="col-wages text-center">(7)</th>
                    <th class="col-bonus text-center">(8)</th>
                    <th class="col-puja text-center">(9)</th>
                    <th class="col-interim text-center">(10)</th>
                    <th class="col-tax text-center">(10A)</th>
                    <th class="col-deduction text-center">(11)</th>
                    <th class="col-total text-center">(12)</th>
                    <th class="col-net text-center">(13)</th>
                    <th class="col-paid text-center">(14)</th>
                    <th class="col-date text-center">(15)</th>
                    <th class="col-sign text-center">(16)</th>
                </tr>
            </thead>
            <tbody>
                @if($is_nil)
                <tr>
                    <td colspan="17" class="text-center">Nil</td>
                </tr>
                @else
                @foreach($rows as $index => $row)
                <tr>
                    <td class="col-sl text-center">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $row['employee_name'] }}</td>
                    <td class="col-father">{{ $row['father_name'] }}</td>
                    <td class="col-age text-center">{{ $row['age_eligible'] }}</td>
                    <td class="col-designation">{{ $row['designation'] }}</td>
                    <td class="col-days text-center">{{ $row['days_worked'] }}</td>
                    <td class="col-wages text-center">{{ number_format($row['total_wages'], 2) }}</td>
                    <td class="col-bonus text-center">{{ number_format($row['bonus_payable'], 2) }}</td>
                    <td class="col-puja text-center">{{ number_format($row['puja_bonus'], 2) }}</td>
                    <td class="col-interim text-center">{{ number_format($row['interim_bonus'], 2) }}</td>
                    <td class="col-tax text-center">{{ number_format($row['tax_deducted'], 2) }}</td>
                    <td class="col-deduction text-center">{{ number_format($row['loss_deduction'], 2) }}</td>
                    <td class="col-total text-center">{{ number_format($row['total_deduction'], 2) }}</td>
                    <td class="col-net text-center">{{ number_format($row['net_payable'], 2) }}</td>
                    <td class="col-paid text-center">{{ number_format($row['amount_paid'], 2) }}</td>
                    <td class="col-date text-center">{{ $row['payment_date'] }}</td>
                    <td class="col-sign"></td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>
