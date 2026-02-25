@extends('compliance.layouts.statutory_base')

@section('form_title', 'FORM XIII - REGISTER OF CONTRACT LABOUR')
@section('act_reference', '[Under Section 29 of the Contract Labour (Regulation and Abolition) Act, 1970]')
@section('rule_reference', '[See Rule 75(1) of the Contract Labour (Regulation and Abolition) Central Rules, 1971]')
@section('signatory_title', 'Principal Employer')

@section('additional_styles')
<style>
    .col-sno { width: 4%; }
    .col-name { width: 18%; }
    .col-contractor { width: 18%; }
    .col-date { width: 10%; }
    .col-wage { width: 10%; }
    .col-order { width: 15%; }
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
                <th class="col-sno">S.No</th>
                <th class="col-name">Name and Address of Worker</th>
                <th class="col-contractor">Name of Contractor</th>
                <th class="col-date">Date of Commencement</th>
                <th class="col-wage">Rate of Wages</th>
                <th class="col-order">Work Order Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['worker_name'] ?? '-' }}</td>
                <td>{{ $row['contractor_name'] ?? '-' }}</td>
                <td class="text-center">{{ $row['deployment_start'] ?? '-' }}</td>
                <td class="text-right">{{ $row['wage_rate'] ?? '-' }}</td>
                <td>{{ $row['work_order'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
<div class="declaration-text">
    Certified that the particulars given above are true to the best of my knowledge and belief.
</div>
@endsection
