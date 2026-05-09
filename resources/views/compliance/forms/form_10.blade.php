<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM 10 - Overtime Muster Roll</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        * {
            margin: 0;
            line-height: 1;
            padding: 1.5px 2px;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1;
            color: #000;
        }
        .form-container {
            border: 1px solid #000;
            padding: 0;
        }
        .form-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .form-header-title {
            font-size: 10px;
            font-weight: bold;
            margin: 2px 0;
        }
        .form-header-subtitle {
            font-size: 10px;
            margin: 1px 0;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #000;
        }
        .info-grid td {
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            font-size: 10px;
            vertical-align: top;
        }
        .info-grid td:last-child {
            border-right: none;
        }
        .info-label {
            font-weight: bold;
            width: 25%;
        }
        .info-value {
            width: 25%;
        }
        .month-row {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 2px 0;
            font-size: 10px;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .data-table th {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.2;
        }
        .data-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 10px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .nil-row {
            text-align: center;
            font-weight: bold;
            padding: 8px 3px;
        }
        .footer-section {
            border-top: 1px solid #000;
            padding: 20px 10px;
            text-align: right;
            font-size: 10px;
            min-height: 60px;
        }
        .footer-text {
            margin-bottom: 30px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="form-header-title">The Tamil Nadu Factories Rules</div>
            <div class="form-header-title">FORM 10</div>
            <div class="form-header-subtitle">(Prescribed under Rule 78)</div>
            <div class="form-header-subtitle">Overtime muster roll for exempted workers</div>
        </div>

        <table class="info-grid">
            <tr>
                <td class="info-label">Name of the Company:</td>
                <td class="info-value">{{ ($header['tenant'] ?? [])['name'] ?? 'N/A' }}</td>
                <td class="info-label">Name of the Contractor:</td>
                <td class="info-value">{{ $header['contractor_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Total number of workers employed:</td>
                <td class="info-value">{{ $header['total_workers'] ?? count($rows ?? []) }}</td>
                <td class="info-label">Work location:</td>
                <td class="info-value">{{ ($header['branch'] ?? [])['address'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Name of the Principal Employer:</td>
                <td class="info-value">{{ $header['principal_employer'] ?? 'N/A' }}</td>
                <td colspan="2"></td>
            </tr>
        </table>

        <div class="month-row">{{ $header['period'] ?? 'N/A' }}</div>

        @if($is_nil)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No. in register</th>
                        <th style="width: 8%;">Name</th>
                        <th style="width: 8%;">Department</th>
                        <th style="width: 10%;">Dates on which overtime has been worked</th>
                        <th style="width: 8%;">Extent of overtime on each occasion</th>
                        <th style="width: 10%;">Total overtime worked or production in case of piece-workers</th>
                        <th style="width: 6%;">Normal hours</th>
                        <th style="width: 8%;">Normal rate of pay</th>
                        <th style="width: 8%;">Overtime rate of pay</th>
                        <th style="width: 8%;">Normal earnings</th>
                        <th style="width: 8%;">Overtime earnings</th>
                        <th style="width: 10%;">Cash equivalent of advantages accruing through the concessional sale of food-grains and other articles</th>
                        <th style="width: 8%;">Total earnings</th>
                        <th style="width: 8%;">Dates on which overtime payments made</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="14" class="nil-row">NO BODY IN THE ORGANIZATION HAS WORKED OVER TIME FOR THE MONTH OF {{ strtoupper($header['period'] ?? '') }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No. in register</th>
                        <th style="width: 8%;">Name</th>
                        <th style="width: 8%;">Department</th>
                        <th style="width: 10%;">Dates on which overtime has been worked</th>
                        <th style="width: 8%;">Extent of overtime on each occasion</th>
                        <th style="width: 10%;">Total overtime worked or production in case of piece-workers</th>
                        <th style="width: 6%;">Normal hours</th>
                        <th style="width: 8%;">Normal rate of pay</th>
                        <th style="width: 8%;">Overtime rate of pay</th>
                        <th style="width: 8%;">Normal earnings</th>
                        <th style="width: 8%;">Overtime earnings</th>
                        <th style="width: 10%;">Cash equivalent of advantages accruing through the concessional sale of food-grains and other articles</th>
                        <th style="width: 8%;">Total earnings</th>
                        <th style="width: 8%;">Dates on which overtime payments made</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows ?? $entries ?? [] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['employee_name'] ?? '' }}</td>
                        <td>{{ $row['designation'] ?? '' }}</td>
                        <td class="text-center"></td>
                        <td class="text-right">{{ number_format($row['overtime_hours'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ $row['is_piece_worker'] ?? '' ? number_format($row['overtime_hours'] ?? 0, 2) : '' }}</td>
                        <td class="text-center">8</td>
                        <td class="text-right">{{ number_format($row['normal_rate'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($row['overtime_rate'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($row['normal_earnings'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($row['food_grain_benefit'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format(($row['normal_earnings'] ?? 0) + ($row['overtime_wages'] ?? 0) + ($row['food_grain_benefit'] ?? 0), 2) }}</td>
                        <td class="text-center"></td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="footer-section">
            <div class="footer-text">For {{ ($header['tenant'] ?? [])['name'] ?? 'Company Name' }}</div>
            <div class="signature-line"></div>
            <div style="margin-top: 2px;">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
