# PowerShell script to fix all blade templates for safe variable references
$templateDir = "e:\compliance-engine\resources\views\compliance\forms"
$files = Get-ChildItem -Path $templateDir -Filter "*.blade.php" -Exclude "*reference*"

foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    $modified = $false
    
    # Pattern 1: @foreach($rows as ...) -> @forelse($rows ?? $entries ?? [] as ...)
    if ($content -match '@foreach\(\$rows\s+as\s+') {
        $content = $content -replace '@foreach\(\$rows\s+as\s+', '@forelse($rows ?? $entries ?? [] as '
        $content = $content -replace '@endforeach', '@empty@endforelse'
        $content = $content -replace '@empty@endforelse', "@empty`n                @endforelse"
        $modified = $true
    }
    
    # Pattern 2: @foreach($entries as ...) -> @forelse($entries ?? $rows ?? [] as ...)
    if ($content -match '@foreach\(\$entries\s+as\s+') {
        $content = $content -replace '@foreach\(\$entries\s+as\s+', '@forelse($entries ?? $rows ?? [] as '
        $content = $content -replace '@endforeach', '@empty@endforelse'
        $content = $content -replace '@empty@endforelse', "@empty`n                @endforelse"
        $modified = $true
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content
        Write-Host "Updated: $($file.Name)"
    }
}

Write-Host "Blade template fixes completed!"
