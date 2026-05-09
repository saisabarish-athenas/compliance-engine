@extends('compliance.layouts.preview')

﻿<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM C - Register of Fines and Unpaid Accumulations</title>
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
        .establishment-section {
            margin-bottom: 8px;
            font-size: 9px;
        }
        .establishment-row {
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }
        .establishment-label {
            font-weight: bold;
            width: 8%;
            margin-right: 5px;
        }
        .establishment-line {
            flex: 1;
            border-bottom: 1px solid black;
            height: 10px;
            padding-left: 3px;
        }
        .address-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .address-lines {
            margin-bottom: 8px;
        }
        .address-line {
            border-bottom: 1px solid black;
            height: 10px;
            margin-bottom: 2px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
        }
        .register-table td {
            text-align: left;
        }
        .col-details {
            width: 30%;
        }
        .col-quarter {
            width: 17.5%;
        }
        .text-center {
            text-align: center;
        }
        .sub-row {
            padding-left: 20px;
        }
        .footnote-section {
            margin-top: 10px;
            font-size: 8px;
        }
        .footnote-text {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div class="header-title">FORM – C</div>
            <div>(See Rule 29 of the Tamil Nadu Labour Welfare Fund Rules, 1973)</div>
            <div class="header-title">Register of Fines and Unpaid Accumulations for the Year ______</div>
        </div>

        <!-- Establishment Details -->
        <div class="establishment-section">
            <div class="establishment-row">
                <div class="establishment-label">Name of the Establishment :</div>
                <div class="establishment-line"></div>
            </div>

            <div class="address-label">Address :</div>
            <div class="address-lines">
                <div class="address-line"></div>
                <div class="address-line"></div>
                <div class="address-line"></div>
                <div class="address-line"></div>
            </div>
        </div>

        <!-- Fines and Unpaid Accumulations Register Table -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-details">Details of Fines and Unpaid Accumulations</th>
                    <th class="col-quarter">Quarter ending 31st March</th>
                    <th class="col-quarter">Quarter ending 30th June</th>
                    <th class="col-quarter">Quarter ending 30th September</th>
                    <th class="col-quarter">Quarter ending 31st December</th>
                </tr>
                <tr>
                    <th class="col-details text-center">(1)</th>
                    <th class="col-quarter text-center">(2)</th>
                    <th class="col-quarter text-center">(3)</th>
                    <th class="col-quarter text-center">(4)</th>
                    <th class="col-quarter text-center">(5)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-details">1.Total Realisation under Fines</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details">2.Total amount being unpaid accumulations* of</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(i) Basic wages</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(ii) Overtime</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(iii) Dearness allowances and other allowances</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(iv) Bonus</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(v) Gratuity</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details sub-row">(vi) Any other item of unpaid accumulation</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details">3.Deductions under Standing Orders</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
                <tr>
                    <td class="col-details">4.Deductions under Payment of Wages Act</td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                    <td class="col-quarter"></td>
                </tr>
            </tbody>
        </table>

        <!-- Footnote Section -->
        <div class="footnote-section">
            <div class="footnote-text">* See definition of "Unpaid Accumulations" under Section 2(1) of the Tamil Nadu Labour Welfare Fund Act 1972.</div>
        </div>
    </div>
</body>
</html>
