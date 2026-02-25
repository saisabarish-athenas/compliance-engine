@extends('compliance.layouts.statutory_base')

@section('form_title', 'FORM B - REGISTER OF WAGES')
@section('act_reference', '[Under Section 13 of the Factories Act, 1948]')
@section('rule_reference', '[See Rule 26 of the Factories Rules]')
@section('signatory_title', 'Manager / Occupier')

@section('additional_styles')
<style>
    .col-sno { width: 2.5%; }
    .col-name { width: 12%; }
    .col-desig { width: 8%; }
    .col-days { width: 4%; }
    .col-rate { width: 5.5%; }
    .col-amount { width: 5.5%; }
    .col-deduct { width: 8%; }
    .col-sign { width: 8%; }
    .col-initial { width: 5%; }
</style>
@endsection

@section('content')
@if($is_nil)
    <div class="nil-declaration">
        NIL – No records during this period
    </div>
@else
    <table>
        <thead>
            <tr>
                <th class="col-sno" rowspan="2">Sl.<br>No.</th>
                <th class="col-name" rowspan="2">Name of Worker</th>
                <th class="col-desig" rowspan="2">Designation</th>
                <th class="col-days" rowspan="2">No. of Days Worked</th>
                <th class="col-rate" rowspan="2">Daily Rate</th>
                <th colspan="6">Wages Earned</th>
                <th class="col-deduct" rowspan="2">Deductions<br>(Nature)</th>
                <th class="col-amount" rowspan="2">Net Amount Paid</th>
                <th class="col-sign" rowspan="2">Signature/<br>Thumb Impression</th>
                <th class="col-initial" rowspan="2">Initial of Employer</th>
            </tr>
            <tr>
                <th class="col-amount">Basic Wages</th>
                <th class="col-amount">Dearness Allowance</th>
                <th class="col-amount">Overtime</th>
                <th class="col-amount">Others</th>
                <th class="col-amount">Other Cash Payments</th>
                <th class="col-amount">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            @php
                $daysWorked = $row['total_days_worked'] ?? 0;
                $dailyRate = $daysWorked > 0 ? ($row['basic_earned'] ?? 0) / $daysWorked : 0;
                $others = ($row['hra_earned'] ?? 0);
                $otherCash = 0;
                $total = ($row['basic_earned'] ?? 0) + ($row['da_earned'] ?? 0) + ($row['overtime_wages'] ?? 0) + $others + $otherCash;
                $deductions = 'PF: ' . number_format($row['pf_employee'] ?? 0, 2) . ', ESI: ' . number_format($row['esi_employee'] ?? 0, 2);
                if (($row['advances'] ?? 0) > 0) $deductions .= ', Adv: ' . number_format($row['advances'], 2);
                if (($row['fines'] ?? 0) > 0) $deductions .= ', Fine: ' . number_format($row['fines'], 2);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_name'] ?? '-' }}</td>
                <td>{{ $row['designation'] ?? '-' }}</td>
                <td class="text-center">{{ $daysWorked }}</td>
                <td class="text-right">{{ number_format($dailyRate, 2) }}</td>
                <td class="text-right">{{ number_format($row['basic_earned'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['da_earned'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($others, 2) }}</td>
                <td class="text-right">{{ number_format($otherCash, 2) }}</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
                <td style="font-size: 7pt;">{{ $deductions }}</td>
                <td class="text-right">{{ number_format($row['net_salary'] ?? 0, 2) }}</td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="5" class="text-right"><strong>GRAND TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['basic_earned'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['da_earned'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['overtime_wages'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['hra_earned'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>0.00</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['gross_salary'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['total_deductions'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['net_salary'] ?? 0, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
<div class="declaration-text">
    Certified that the above register is maintained in accordance with the provisions of the Factories Act, 1948 and the rules made thereunder, and that the particulars entered therein are true to the best of my knowledge and belief.
</div>
@endsection
