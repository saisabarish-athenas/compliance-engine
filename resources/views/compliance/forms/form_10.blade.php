@extends('compliance.layouts.statutory_reference_layout')

@section('form_title')
FORM 10 - OVERTIME REGISTER
@endsection

@section('act_reference')
[Under Section XX of the Factories Act, 1948]
@endsection

@section('rule_reference')
[See {{ config('tn_statutory_rules.FORM_10.rule') }} of the {{ config('tn_statutory_rules.FORM_10.act') }}]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Factory:</td>
        <td>{{ $header['tenant']['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Address:</td>
        <td>{{ $header['branch']['address'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Registration/License No:</td>
        <td>{{ $header['branch']['license'] ?: $header['tenant']['factory_license_no'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Period:</td>
        <td>{{ $header['period'] }}</td>
    </tr>
</table>
@endsection

@section('content')
@if($is_nil)
    <div class="nil-block">
        NIL - No overtime worked during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No.</th>
                <th style="width: 10%;">Employee Code</th>
                <th style="width: 20%;">Name of Worker</th>
                <th style="width: 15%;">Designation</th>
                <th style="width: 10%;">Overtime Hours</th>
                <th style="width: 15%;">Overtime Wages</th>
                <th style="width: 25%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['designation'] }}</td>
                <td class="text-right">{{ number_format($row['overtime_hours'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
        @if(!empty($totals))
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['overtime_hours'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['overtime_wages'] ?? 0, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
@endif
@endsection

@section('declaration')
I hereby certify that the above particulars are correct to the best of my knowledge and belief.
@endsection

@section('signature_block')
<table class="signature-table">
    <tr>
        <td class="signature-left">
            <div>Date: _______________</div>
            <div style="margin-top: 10px;">Place: _______________</div>
        </td>
        <td class="signature-right">
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Signature of Manager/Occupier</strong><br>
                Name: _______________<br>
                Designation: _______________
            </div>
            <div class="seal-placeholder">(Seal of Factory)</div>
        </td>
    </tr>
</table>
@endsection
