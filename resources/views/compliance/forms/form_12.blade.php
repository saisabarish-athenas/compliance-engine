@extends('compliance.layouts.statutory_reference_layout')

@section('form_title')
FORM 12 - REGISTER OF ADULT WORKERS
@endsection

@section('act_reference')
[Under Section XX of the Factories Act, 1948]
@endsection

@section('rule_reference')
[See Rule XX of the Factories Rules]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Factory:</td>
        <td>{{ $header['tenant']['name'] }}</td>
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
        NIL - No adult workers registered during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No.</th>
                <th style="width: 15%;">Employee Code</th>
                <th style="width: 30%;">Name</th>
                <th style="width: 20%;">Designation</th>
                <th style="width: 30%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_code'] ?? 'N/A' }}</td>
                <td>{{ $row['name'] ?? 'N/A' }}</td>
                <td>{{ $row['designation'] ?? 'N/A' }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
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
        </td>
        <td class="signature-right">
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Signature of Manager/Occupier</strong>
            </div>
        </td>
    </tr>
</table>
@endsection
