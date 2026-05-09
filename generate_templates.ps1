$templates = @{
    "form_17" = @{
        title = "FORM 17 - REGISTER OF YOUNG PERSONS"
        act = "Factories Act, 1948"
        nil = "No young persons registered"
    }
    "form_2" = @{
        title = "FORM 2 - REGISTER OF LEAVE"
        act = "Factories Act, 1948"
        nil = "No leave records"
    }
    "form_7" = @{
        title = "FORM 7 - NOTICE OF PERIODS FOR ADULT WORKERS"
        act = "Factories Act, 1948"
        nil = "No notices issued"
    }
    "form_8" = @{
        title = "FORM 8 - REGISTER OF ACCIDENTS"
        act = "Factories Act, 1948"
        nil = "No accidents reported"
    }
    "form_11" = @{
        title = "FORM 11 - NOTICE OF DANGEROUS OCCURRENCES"
        act = "Factories Act, 1948"
        nil = "No dangerous occurrences"
    }
    "form_18" = @{
        title = "FORM 18 - REGISTER OF CHILD WORKERS"
        act = "Factories Act, 1948"
        nil = "No child workers"
    }
    "form_26" = @{
        title = "FORM 26 - NOTICE OF ACCIDENT"
        act = "Factories Act, 1948"
        nil = "No accidents"
    }
    "form_26a" = @{
        title = "FORM 26A - NOTICE OF DANGEROUS OCCURRENCE"
        act = "Factories Act, 1948"
        nil = "No dangerous occurrences"
    }
    "hazard_reg" = @{
        title = "HAZARDOUS PROCESS REGISTER"
        act = "Factories Act, 1948"
        nil = "No hazardous processes"
    }
    "form_xii" = @{
        title = "FORM XII - REGISTER OF CONTRACTORS"
        act = "Contract Labour Act, 1970"
        nil = "No contractors registered"
    }
    "clra_license" = @{
        title = "LICENSE REGISTER"
        act = "Contract Labour Act, 1970"
        nil = "No licenses issued"
    }
    "form_xiv" = @{
        title = "FORM XIV - REGISTER OF WORKMEN"
        act = "Contract Labour Act, 1970"
        nil = "No workmen registered"
    }
    "form_xvi" = @{
        title = "FORM XVI - REGISTER OF WAGES (CLRA)"
        act = "Contract Labour Act, 1970"
        nil = "No wages paid"
    }
    "form_xvii" = @{
        title = "FORM XVII - REGISTER OF DEDUCTIONS"
        act = "Contract Labour Act, 1970"
        nil = "No deductions made"
    }
    "form_xix" = @{
        title = "FORM XIX - MUSTER ROLL (CLRA)"
        act = "Contract Labour Act, 1970"
        nil = "No attendance records"
    }
    "form_xx" = @{
        title = "FORM XX - REGISTER OF ADVANCES"
        act = "Contract Labour Act, 1970"
        nil = "No advances given"
    }
    "form_xxi" = @{
        title = "FORM XXI - REGISTER OF FINES"
        act = "Contract Labour Act, 1970"
        nil = "No fines imposed"
    }
    "form_xxii" = @{
        title = "FORM XXII - REGISTER OF DAMAGE OR LOSS"
        act = "Contract Labour Act, 1970"
        nil = "No damage or loss"
    }
    "form_xxiii" = @{
        title = "FORM XXIII - REGISTER OF OVERTIME"
        act = "Contract Labour Act, 1970"
        nil = "No overtime worked"
    }
    "form_xxiv" = @{
        title = "FORM XXIV - ANNUAL RETURN"
        act = "Contract Labour Act, 1970"
        nil = "No data for annual return"
    }
    "form_xxv" = @{
        title = "FORM XXV - HALF-YEARLY RETURN"
        act = "Contract Labour Act, 1970"
        nil = "No data for half-yearly return"
    }
    "shops_form_12" = @{
        title = "SHOPS FORM 12 - REGISTER OF WAGES"
        act = "Shops & Establishments Act"
        nil = "No wages paid"
    }
    "shops_form_13" = @{
        title = "SHOPS FORM 13 - ATTENDANCE REGISTER"
        act = "Shops & Establishments Act"
        nil = "No attendance records"
    }
    "shops_form_1" = @{
        title = "SHOPS FORM 1 - REGISTER OF EMPLOYMENT"
        act = "Shops & Establishments Act"
        nil = "No employment records"
    }
    "shops_fines" = @{
        title = "REGISTER OF FINES"
        act = "Shops & Establishments Act"
        nil = "No fines imposed"
    }
    "shops_form_c" = @{
        title = "SHOPS FORM C - BONUS REGISTER"
        act = "Shops & Establishments Act"
        nil = "No bonus paid"
    }
    "shops_unpaid" = @{
        title = "UNPAID WAGES REGISTER"
        act = "Shops & Establishments Act"
        nil = "No unpaid wages"
    }
    "shops_form_vi" = @{
        title = "SHOPS FORM VI - LEAVE REGISTER"
        act = "Shops & Establishments Act"
        nil = "No leave records"
    }
}

$basePath = "e:\compliance-engine\resources\views\compliance\forms"

foreach ($key in $templates.Keys) {
    $info = $templates[$key]
    $filePath = Join-Path $basePath "$key.blade.php"
    
    if (Test-Path $filePath) {
        Write-Host "Skipping $key (already exists)" -ForegroundColor Yellow
        continue
    }
    
    $content = @"
@extends('compliance.layouts.statutory_reference_layout')

@section('form_title')
$($info.title)
@endsection

@section('act_reference')
[Under $($info.act)]
@endsection

@section('rule_reference')
[See Rule XX]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Establishment:</td>
        <td>{{ `$header['tenant']['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Period:</td>
        <td>{{ `$header['period'] }}</td>
    </tr>
</table>
@endsection

@section('content')
@if(`$is_nil)
    <div class="nil-block">
        NIL - $($info.nil) during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No.</th>
                @foreach(array_keys(`$rows[0] ?? []) as `$column)
                <th>{{ ucwords(str_replace('_', ' ', `$column)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(`$rows as `$index => `$row)
            <tr>
                <td class="text-center">{{ `$index + 1 }}</td>
                @foreach(`$row as `$value)
                <td>{{ is_numeric(`$value) ? number_format(`$value, 2) : (`$value ?? 'N/A') }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
        @if(!empty(`$totals))
        <tfoot>
            <tr class="totals-row">
                <td colspan="{{ count(`$rows[0] ?? []) }}" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format(array_sum(`$totals), 2) }}</strong></td>
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
        </td>
        <td class="signature-right">
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Signature of Manager/Authorized Person</strong>
            </div>
        </td>
    </tr>
</table>
@endsection
"@
    
    Set-Content -Path $filePath -Value $content -Encoding UTF8
    Write-Host "Created $key.blade.php" -ForegroundColor Green
}

Write-Host "`nAll templates generated successfully!" -ForegroundColor Cyan
