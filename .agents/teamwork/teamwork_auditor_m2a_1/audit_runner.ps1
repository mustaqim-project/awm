$themes = @(
    'universal_storefront',
    'modern_umkm',
    'online_store',
    'business_corporate',
    'coffee_beverage',
    'agriculture_farm',
    'retail_fashion',
    'service_workshop',
    'food_restaurant',
    'health_beauty'
)

$files = @(
    'hero.blade.php',
    'features_banner.blade.php',
    'category_chips.blade.php'
)

$forbiddenPatterns = @(
    'unsplash\.com',
    'Tune-up',
    'Petik Segar',
    'Jahitan Rapi',
    'Specialty Roastery',
    '80\+ Cupping',
    'Bongkar Pasang',
    '100% Produk Lokal',
    'Corporate Enterprise',
    '10k\+ Pasien',
    'BPOM'
)

Write-Host "=== FORENSIC AUDIT: 30 BLADE FILES IN THEMES 1-10 ===" -ForegroundColor Cyan

$allPass = $true
$fileCount = 0

foreach ($theme in $themes) {
    Write-Host "`n--- Checking Theme: $theme ---" -ForegroundColor Yellow
    foreach ($file in $files) {
        $fileCount++
        $filePath = "resources/views/public/storefront/themes/$theme/$file"
        if (-not (Test-Path $filePath)) {
            Write-Host "MISSING FILE: $filePath" -ForegroundColor Red
            $allPass = $false
            continue
        }

        $content = Get-Content -Raw $filePath

        # 1. Check PHP syntax with php -l
        $lintResult = & php -l $filePath 2>&1
        if ($lintResult -notmatch "No syntax errors detected") {
            Write-Host "SYNTAX ERROR in ${filePath}: $lintResult" -ForegroundColor Red
            $allPass = $false
        } else {
            Write-Host "  [OK] Syntax: $file" -ForegroundColor Green
        }

        # 2. Check forbidden patterns
        foreach ($pattern in $forbiddenPatterns) {
            if ($content -match $pattern) {
                Write-Host "  [VIOLATION] Found forbidden pattern '$pattern' in $filePath" -ForegroundColor Red
                $allPass = $false
            }
        }

        # 3. File specific checks
        if ($file -eq 'hero.blade.php') {
            # Monogram check
            if ($content -notmatch 'substr\(\$business->name') {
                Write-Host "  [VIOLATION] Missing dynamic monogram logic in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] Dynamic monogram present" -ForegroundColor Green
            }

            # Business name check
            if ($content -notmatch '\$business->name') {
                Write-Host "  [VIOLATION] Missing dynamic business name in $filePath" -ForegroundColor Red
                $allPass = $false
            }

            # Theme tokens check
            if ($content -notmatch 'var\(--theme-primary\)' -and $content -notmatch 'theme-btn-primary') {
                Write-Host "  [VIOLATION] Missing dynamic theme token in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] Theme tokens present" -ForegroundColor Green
            }

            # Universal values / badges
            if ($content -notmatch 'getUniversalValues') {
                Write-Host "  [VIOLATION] Missing getUniversalValues() call in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] Dynamic values/badges present" -ForegroundColor Green
            }
        }

        if ($file -eq 'features_banner.blade.php') {
            if ($content -notmatch 'getUniversalValues') {
                Write-Host "  [VIOLATION] Missing getUniversalValues() loop in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] Dynamic features loop present" -ForegroundColor Green
            }
        }

        if ($file -eq 'category_chips.blade.php') {
            if ($content -notmatch 'semua_produk') {
                Write-Host "  [VIOLATION] Missing 'semua_produk' fallback chip in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] 'semua_produk' chip present" -ForegroundColor Green
            }
            if ($content -notmatch '!request\(\)->filled\(''category''\)' -and $content -notmatch '!request\(''category''\)') {
                Write-Host "  [VIOLATION] Missing active indicator for default category in $filePath" -ForegroundColor Red
                $allPass = $false
            }
            if ($content -notmatch 'request\(''category''\)\s*==\s*\$cat->id') {
                Write-Host "  [VIOLATION] Missing active category check in $filePath" -ForegroundColor Red
                $allPass = $false
            } else {
                Write-Host "  [OK] Active category check present" -ForegroundColor Green
            }
        }
    }
}

Write-Host "`n=== SUMMARY ===" -ForegroundColor Cyan
Write-Host "Total files evaluated: $fileCount"
if ($allPass) {
    Write-Host "VERDICT: ALL CHECKS PASSED CLEANLY" -ForegroundColor Green
} else {
    Write-Host "VERDICT: INTEGRITY VIOLATION DETECTED" -ForegroundColor Red
}
