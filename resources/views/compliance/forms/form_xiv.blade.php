@extends('compliance.layouts.preview')

﻿<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XIV - Employment Card</title>
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
            padding: 20px;
            margin: 0 auto;
            width: 90%;
            max-width: 800px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .form-header div {
            margin: 3px 0;
        }
        .header-title {
            font-weight: bold;
            font-size: 13px;
        }
        .header-rule {
            font-size: 10px;
        }
        .two-column-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .column {
            flex: 1;
        }
        .field-row {
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .field-label {
            font-weight: bold;
            display: inline;
        }
        .dot-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 65%;
            height: 12px;
            margin-left: 5px;
            vertical-align: bottom;
        }
        .detail-section {
            margin-top: 20px;
            font-size: 11px;
        }
        .detail-row {
            margin-bottom: 14px;
            line-height: 1.4;
        }
        .detail-number {
            font-weight: bold;
            display: inline;
            width: 3%;
        }
        .detail-label {
            display: inline;
        }
        .detail-dot-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 60%;
            height: 12px;
            margin-left: 5px;
            vertical-align: bottom;
        }
        .signature-section {
            margin-top: 40px;
            text-align: right;
            font-size: 11px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
        }
        .signature-label {
            margin-top: 3px;
            font-weight: bold;
        }
        .signature{
            text-align:right;
            margin-top:40px;
        }

        .sign-line{
            width:200px;
            border-bottom:1px solid black;
            margin-left:auto;
            margin-bottom:5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XIV</div>
            <div class="header-rule">[See Rule 76]</div>
            <div class="header-title">Employment Card</div>
        </div>

        <div class="two-column-section">
            <div class="column">
                <div class="field-row">
                    <span class="field-label">Name and address of Contractor</span>
                    <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Nature of work and location of work</span>
                    <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
                </div>
            </div>
            <div class="column">
                <div class="field-row">
                    <span class="field-label">Name and address of Establishment in under which contract is carried on</span>
                    <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Name and address of Principal Employer</span>
                    <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-row">
                <span class="detail-number">1.</span>
                <span class="detail-label">Name of Workmen:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">2.</span>
                <span class="detail-label">Sl. No. in the register of workmen employed:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">3.</span>
                <span class="detail-label">Nature of employment / Designation:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">4.</span>
                <span class="detail-label">Wages rate (with particular of unit, in case of piece-work):</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">5.</span>
                <span class="detail-label">Wage period:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">6.</span>
                <span class="detail-label">Tenure of employment:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-number">7.</span>
                <span class="detail-label">Remarks:</span>
                <span class="dot-line">{{ $contractor_name ?? 'NIL' }}</span>
            </div>
        </div>
        <div class="signature">
            <div class="sign-line"></div>
            <div>Signature of Contractor</div>
        </div>
    </div>
</body>
</html>
