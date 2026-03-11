@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ESI FORM 12 - Accident Report</title>
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
            page-break-after: always;
        }
        .form-header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid black;
        }
        .header-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px;
            border-right: 1px solid black;
            font-weight: bold;
            font-size: 10px;
        }
        .header-cell:last-child {
            border-right: none;
        }
        .section-title {
            font-weight: bold;
            font-size: 10px;
            margin-top: 8px;
            margin-bottom: 4px;
            border-bottom: 0.8px solid black;
            padding: 2px;
        }
        .field-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            border: 1px solid black;
        }
        .field-label {
            display: table-cell;
            width: 35%;
            padding: 3px 4px;
            font-weight: bold;
            border-right: 1px solid black;
            vertical-align: top;
        }
        .field-value {
            display: table-cell;
            width: 65%;
            padding: 3px 4px;
            vertical-align: top;
            min-height: 18px;
        }
        .two-column-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        .two-column-cell {
            display: table-cell;
            width: 50%;
            border: 1px solid black;
            padding: 3px 4px;
        }
        .two-column-cell:first-child {
            border-right: 1px solid black;
        }
        .checkbox-group {
            display: inline-block;
            margin-right: 10px;
        }
        .checkbox {
            border: 1px solid black;
            width: 12px;
            height: 12px;
            display: inline-block;
            margin-right: 3px;
            vertical-align: middle;
        }
        .checkbox.checked {
            text-align: center;
            line-height: 12px;
            font-size: 10px;
        }
        .witness-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            border: 1px solid black;
        }
        .witness-label {
            display: table-cell;
            width: 5%;
            padding: 3px 4px;
            border-right: 1px solid black;
            font-weight: bold;
            vertical-align: top;
        }
        .witness-value {
            display: table-cell;
            width: 95%;
            padding: 3px 4px;
            vertical-align: top;
            min-height: 30px;
        }
        .declaration-section {
            border: 1px solid black;
            padding: 6px;
            margin-bottom: 4px;
            font-size: 9px;
            line-height: 1.4;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            border: 1px solid black;
        }
        .footer-cell {
            display: table-cell;
            width: 50%;
            padding: 3px 4px;
            border-right: 1px solid black;
            vertical-align: top;
            min-height: 40px;
        }
        .footer-cell:last-child {
            border-right: none;
        }
        .footer-label {
            font-weight: bold;
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $data = ($rows ?? [])[0] ?? [
            'employer_name' => 'NIL',
            'code_no' => 'NIL',
            'branch_office' => 'NIL',
            'industry_nature' => 'NIL',
            'insured_name' => 'NIL',
            'insurance_no' => 'NIL',
            'sex' => 'NIL',
            'age' => 'NIL',
            'occupation' => 'NIL',
            'accident_address' => 'NIL',
            'department' => 'NIL',
            'shift_hour' => 'NIL',
            'exact_place' => 'NIL',
            'injury_nature' => 'NIL',
            'injury_location' => 'NIL',
            'hospital_info' => 'NIL',
            'accident_description' => 'NIL',
            'death' => 'no',
            'death_date' => 'NIL',
            'wages_payable' => 'no',
            'contravention' => 'no',
            'witness_1' => 'NIL',
            'witness_2' => 'NIL',
            'machine_involved' => 'NIL',
            'machinery_fenced' => 'no',
            'person_doing' => 'NIL',
            'employer_vehicle' => 'no',
            'employer_permission' => 'no',
            'transport_operated' => 'no',
            'despatch_date' => 'NIL',
            'designation' => 'NIL',
            'diary_no' => 'NIL',
            'branch_manager' => 'NIL'
        ];
        $data = array_merge([
            'death' => 'no',
            'wages_payable' => 'no',
            'contravention' => 'no',
            'machinery_fenced' => 'no',
            'employer_vehicle' => 'no',
            'employer_permission' => 'no',
            'transport_operated' => 'no'
        ], $data);
    @endphp
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div class="header-cell">FORM 12<br>(REGULATION 68)</div>
            <div class="header-cell">E.S.I CORPORATION</div>
            <div class="header-cell">ACCIDENT REPORT</div>
        </div>

        <!-- Employee Identification -->
        <div class="section-title">EMPLOYEE IDENTIFICATION</div>
        <div class="field-row">
            <div class="field-label">Name of Employer</div>
            <div class="field-value">{{ $data['employer_name'] ?? 'NIL' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Code No.</strong><br>{{ $data['code_no'] ?? 'NIL' }}
            </div>
            <div class="two-column-cell">
                <strong>Branch Office</strong><br>{{ $data['branch_office'] ?? 'NIL' }}
            </div>
        </div>

        <!-- Main Details -->
        <div class="section-title">MAIN DETAILS</div>
        <div class="field-row">
            <div class="field-label">Nature of Industry / Business</div>
            <div class="field-value">{{ $data['industry_nature'] ?? 'NIL' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Name & Address of Insured Person</div>
            <div class="field-value">{{ $data['insured_name'] ?? 'NIL' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Insurance No</strong><br>{{ $data['insurance_no'] ?? 'NIL' }}
            </div>
            <div class="two-column-cell">
                <strong>Sex</strong><br>{{ $data['sex'] ?? 'NIL' }}
            </div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Age (Last Birthday)</strong><br>{{ $data['age'] ?? 'NIL' }}
            </div>
            <div class="two-column-cell">
                <strong>Occupation</strong><br>{{ $data['occupation'] ?? 'NIL' }}
            </div>
        </div>

        <!-- Accident Location -->
        <div class="section-title">ACCIDENT LOCATION DETAILS</div>
        <div class="field-row">
            <div class="field-label">Address of premises where accident happened</div>
            <div class="field-value">{{ $data['accident_address'] ?? 'NIL' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Department</strong><br>{{ $data['department'] ?? 'NIL' }}
            </div>
            <div class="two-column-cell">
                <strong>Shift Hour</strong><br>{{ $data['shift_hour'] ?? 'NIL' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Exact place of accident</div>
            <div class="field-value">{{ $data['exact_place'] ?? 'NIL' }}</div>
        </div>

        <!-- Injury Information -->
        <div class="section-title">INJURY INFORMATION</div>
        <div class="field-row">
            <div class="field-label">Nature and extent of injury</div>
            <div class="field-value">{{ $data['injury_nature'] ?? 'NIL' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Location of injury</div>
            <div class="field-value">{{ $data['injury_location'] ?? 'NIL' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Dispensary / ESI Hospital / Insurance No</div>
            <div class="field-value">{{ $data['hospital_info'] ?? 'NIL' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Brief description of accident</div>
            <div class="field-value" style="min-height: 40px;">{{ $data['accident_description'] ?? 'NIL' }}</div>
        </div>

        <!-- Accident Consequence -->
        <div class="section-title">ACCIDENT CONSEQUENCE</div>
        <div class="field-row">
            <div class="field-label">Did accident cause death</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ ($data['death'] ?? 'no') == 'yes' ? 'checked' : '' }}">{{ ($data['death'] ?? 'no') == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['death'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['death'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Date of Death</div>
            <div class="field-value">{{ $data['death_date'] ?? 'NIL' }}</div>
        </div>

        <!-- Disability / Benefit -->
        <div class="section-title">DISABILITY / BENEFIT</div>
        <div class="field-row">
            <div class="field-label">Whether wages in full or part payable</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['wages_payable'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['wages_payable'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['wages_payable'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['wages_payable'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Whether injured person was acting in contravention of rules</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['contravention'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['contravention'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['contravention'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['contravention'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>

        <!-- Witness Section -->
        <div class="section-title">WITNESS INFORMATION</div>
        <div class="witness-row">
            <div class="witness-label">1.</div>
            <div class="witness-value">
                <strong>Name and address of witness</strong><br>{{ $data['witness_1'] ?? 'NIL' }}
            </div>
        </div>
        <div class="witness-row">
            <div class="witness-label">2.</div>
            <div class="witness-value">
                <strong>Name and address of witness</strong><br>{{ $data['witness_2'] ?? 'NIL' }}
            </div>
        </div>

        <!-- Cause of Accident -->
        <div class="section-title">CAUSE OF ACCIDENT</div>
        <div class="field-row">
            <div class="field-label">Machine involved</div>
            <div class="field-value">{{ $data['machine_involved'] ?? 'NIL' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">State whether machinery fenced</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['machinery_fenced'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['machinery_fenced'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['machinery_fenced'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['machinery_fenced'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">State what injured person was doing</div>
            <div class="field-value" style="min-height: 30px;">{{ $data['person_doing'] ?? 'NIL' }}</div>
        </div>

        <!-- Transport Accident -->
        <div class="section-title">TRANSPORT ACCIDENT DETAILS</div>
        <div class="field-row">
            <div class="field-label">Was employee travelling in employer vehicle</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ ($data['employer_vehicle'] ?? 'no') == 'yes' ? 'checked' : '' }}">{{ ($data['employer_vehicle'] ?? 'no') == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ ($data['employer_vehicle'] ?? 'no') == 'no' ? 'checked' : '' }}">{{ ($data['employer_vehicle'] ?? 'no') == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">With employer permission</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_permission'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['employer_permission'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_permission'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['employer_permission'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Transport operated by employer</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['transport_operated'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['transport_operated'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['transport_operated'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['transport_operated'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>

        <!-- Declaration -->
        <div class="declaration-section">
            <strong>DECLARATION</strong><br><br>
            I certify that to the best of my knowledge and belief the above particulars are correct.
        </div>

        <div class="field-row">
            <div class="field-label">Date of despatch of accident report</div>
            <div class="field-value">{{ $data['despatch_date'] ?? 'NIL' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Signature</strong><br><br><br>
            </div>
            <div class="two-column-cell">
                <strong>Designation</strong><br>{{ $data['designation'] ?? 'NIL' }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-row">
            <div class="footer-cell">
                <div class="footer-label">Diary No & Date</div>
                <div style="margin-top: 20px;">{{ $data['diary_no'] ?? 'NIL' }}</div>
            </div>
            <div class="footer-cell">
                <div class="footer-label">Branch Office Manager</div>
                <div style="margin-top: 20px;">{{ $data['branch_manager'] ?? 'NIL' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
