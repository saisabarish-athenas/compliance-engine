@extends('compliance.layouts.statutory_base')

@section('form_title', 'FORM 12 - ACCIDENT REGISTER')
@section('act_reference', '[Under Section 55 of the Employees State Insurance Act, 1948]')
@section('rule_reference', '[See Rule 67 of the Employees State Insurance (General) Regulations, 1950]')
@section('signatory_title', 'Employer / Manager')

@section('additional_styles')
<style>
    .col-sno { width: 4%; }
    .col-name { width: 15%; }
    .col-date { width: 10%; }
    .col-type { width: 12%; }
    .col-location { width: 15%; }
    .col-desc { width: 30%; }
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
                <th class="col-name">Name of Injured Person</th>
                <th class="col-date">Date and Time of Accident</th>
                <th class="col-type">Nature of Injury</th>
                <th class="col-location">Place of Accident</th>
                <th class="col-desc">Circumstances of Accident</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_name'] ?? '-' }}</td>
                <td class="text-center">{{ $row['incident_date'] ?? '-' }}</td>
                <td>{{ $row['incident_type'] ?? '-' }}</td>
                <td>{{ $row['location'] ?? '-' }}</td>
                <td>{{ $row['description'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
<div class="declaration-text">
    I hereby certify that the above entries are correct and complete as per the records maintained.
</div>
@endsection
