$files = @(
  'public_home.php',
  'public_commercial.php',
  'public_discovery.php',
  'public_solutions.php',
  'public_marketing.php',
  'storefront.php'
)

foreach ($f in $files) {
    $idPath = "C:\laragon\www\cooca_core\lang\id\$f"
    $enPath = "C:\laragon\www\cooca_core\lang\en\$f"
    
    if (!(Test-Path $idPath) -or !(Test-Path $enPath)) {
        Write-Host "File missing for $f"
        continue
    }

    $idKeys = @()
    $enKeys = @()
    (Get-Content $idPath) | ForEach-Object {
        if ($_ -match "^\s*['""]([^'""]+)['""]\s*=>") {
            $idKeys += $matches[1]
        }
    }
    (Get-Content $enPath) | ForEach-Object {
        if ($_ -match "^\s*['""]([^'""]+)['""]\s*=>") {
            $enKeys += $matches[1]
        }
    }

    $missingInEn = $idKeys | Where-Object { $enKeys -notcontains $_ }
    $missingInId = $enKeys | Where-Object { $idKeys -notcontains $_ }

    Write-Host "FILE: $f"
    Write-Host "  ID keys: $($idKeys.Count) | EN keys: $($enKeys.Count)"
    Write-Host "  Missing in EN: $($missingInEn.Count)"
    if ($missingInEn.Count -gt 0) {
        Write-Host "    Sample missing in EN: $(($missingInEn | Select-Object -First 5) -join ', ')"
    }
    Write-Host "  Missing in ID: $($missingInId.Count)"
    Write-Host "----------------------------------"
}
