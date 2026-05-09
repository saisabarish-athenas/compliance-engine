$apiPath = "e:\compliance-engine\app\Services\Compliance\FormApis"
$files = Get-ChildItem -Path $apiPath -Filter "*ApiService.php" | Where-Object { $_.Name -notmatch "^(Base|FormApiServices)" }

$oldPattern = @"
        return \[
            'tenant_id' => \$tenantId,
            'branch_id' => \$branchId,
            'month' => \$month,
            'year' => \$year,
            'period' => \$this->formatPeriod\(\),
            'tenant' => \$this->getTenantDetails\(\$tenantId\),
            'branch' => \$this->getBranchDetails\(\$branchId, \$tenantId\),
            'rows' => \$rows,
            'record_count' => count\(\$rows\),
        \];
"@

$newPattern = @"
        return [
            'records' => `$rows,
            'meta' => [
                'tenant_id' => `$tenantId,
                'branch_id' => `$branchId,
                'month' => `$month,
                'year' => `$year,
            ],
            'tenant' => `$this->getTenantDetails(`$tenantId),
            'branch' => `$this->getBranchDetails(`$branchId, `$tenantId),
            'period' => `$this->formatPeriod(),
        ];
"@

$updated = 0
foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    
    if ($content -match "'records' =>" -and $content -match "'meta' =>") {
        Write-Host "✓ Already updated: $($file.Name)"
        continue
    }
    
    $newContent = $content -replace [regex]::Escape($oldPattern), $newPattern
    
    if ($newContent -ne $content) {
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "✓ Updated: $($file.Name)"
        $updated++
    }
}

Write-Host "`n✅ Standardized $updated API services"
