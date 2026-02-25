@extends('compliance.layouts.statutory_base')

@section('form_title', 'INSPECTION REGISTER')
@section('act_reference', '[Under Section 17 of the Employees Provident Funds and Miscellaneous Provisions Act, 1952]')
@section('rule_reference', '[See Rule 44 of the Employees Provident Funds Scheme, 1952]')
@section('signatory_title', 'Employer / Manager')

@section('additional_styles')
<style>
    .col-sno { width: 5%; }
    .col-date { width: 12%; }
    .col-authority { width: 20%; }
    .col-ref { width: 15%; }
    .col-remarks { width: 35%; }
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
                <th class="col-date">Date of Inspection</th>
                <th class="col-authority">Name and Designation of Inspecting Authority</th>
                <th class="col-ref">Reference Number</th>
                <th class="col-remarks">Remarks / Observations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row['inspection_date'] ?? '-' }}</td>
                <td>{{ $row['authority'] ?? '-' }}</td>
                <td>{{ $row['reference'] ?? '-' }}</td>
                <td>{{ $row['remarks'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
<div class="declaration-text">
    Certified that the above inspection records are maintained as per statutory requirements.
</div>
@endsection
