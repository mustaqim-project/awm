$files = @(
  'resources/views/public/about.blade.php',
  'resources/views/public/business_landing.blade.php',
  'resources/views/public/demo.blade.php',
  'resources/views/public/pricing.blade.php',
  'resources/views/public/privacy.blade.php',
  'resources/views/public/security.blade.php',
  'resources/views/public/sitemap.blade.php',
  'resources/views/public/support.blade.php',
  'resources/views/public/terms.blade.php',
  'resources/views/landing.blade.php',
  'resources/views/public/home/index.blade.php',
  'resources/views/public/landing_commercial/kasir_android.blade.php',
  'resources/views/public/landing_commercial/kasir_gratis.blade.php',
  'resources/views/public/landing_commercial/kasir_offline.blade.php',
  'resources/views/public/discovery/index.blade.php',
  'resources/views/public/solutions/fnb.blade.php',
  'resources/views/public/solutions/laundry.blade.php',
  'resources/views/public/solutions/manufacturing.blade.php',
  'resources/views/public/solutions/retail.blade.php',
  'resources/views/public/solutions/services.blade.php',
  'resources/views/public/solutions/show.blade.php',
  'resources/views/public/solutions/workshop.blade.php'
)

$results = @()

foreach ($relPath in $files) {
    $fullPath = Join-Path 'C:\laragon\www\cooca_core' $relPath
    if (!(Test-Path $fullPath)) {
        Write-Host "File not found: $relPath"
        continue
    }
    $raw = Get-Content $fullPath -Raw
    $lines = Get-Content $fullPath

    # 1. Headings
    $h1 = [regex]::Matches($raw, '<h1[\s\S]*?<\/h1>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $h2 = [regex]::Matches($raw, '<h2[\s\S]*?<\/h2>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $h3 = [regex]::Matches($raw, '<h3[\s\S]*?<\/h3>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    # 2. Images
    $imgs = [regex]::Matches($raw, '<img[\s\S]*?>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $imgWithoutLazy = 0
    $imgWithoutDimensions = 0
    $imgWithoutAlt = 0
    foreach ($img in $imgs) {
        $val = $img.Value
        if ($val -notmatch 'loading=') { $imgWithoutLazy++ }
        if ($val -notmatch 'width=' -or $val -notmatch 'height=') { $imgWithoutDimensions++ }
        if ($val -notmatch 'alt=') { $imgWithoutAlt++ }
    }

    # 3. Scripts & Inline Handlers
    $scripts = [regex]::Matches($raw, '<script[\s\S]*?<\/script>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $inlineHandlers = [regex]::Matches($raw, '\son[a-z]+="[^"]*"', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    # 4. Styles & Inline Styles
    $styles = [regex]::Matches($raw, '<style[\s\S]*?<\/style>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $inlineStyles = [regex]::Matches($raw, '\sstyle="[^"]*"', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    # 5. Fixed Widths (e.g. w-[...px])
    $fixedWidths = [regex]::Matches($raw, 'w-\[\d+px\]|min-w-\[\d+px\]|max-w-\[\d+px\]', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    # 6. Localization: count of __()
    $transCalls = [regex]::Matches($raw, '__\([^\)]+\)')
    
    # 7. Dark theme missing check: bg-white without dark:bg-, text-gray-900 without dark:text-
    $bgWhiteNoDark = 0
    $textGray900NoDark = 0
    foreach ($m in [regex]::Matches($raw, 'class="([^"]*)"')) {
        $c = $m.Groups[1].Value
        if ($c -match '\bbg-white\b' -and $c -notmatch 'dark:bg-') { $bgWhiteNoDark++ }
        if ($c -match '\btext-gray-900\b' -and $c -notmatch 'dark:text-') { $textGray900NoDark++ }
        if ($c -match '\bborder-gray-200\b' -and $c -notmatch 'dark:border-') { }
    }

    $results += [PSCustomObject]@{
        File = $relPath
        Lines = $lines.Count
        H1_Count = $h1.Count
        H2_Count = $h2.Count
        H3_Count = $h3.Count
        Img_Total = $imgs.Count
        Img_NoLazy = $imgWithoutLazy
        Img_NoDims = $imgWithoutDimensions
        Img_NoAlt = $imgWithoutAlt
        Script_Tags = $scripts.Count
        Inline_Handlers = $inlineHandlers.Count
        Style_Tags = $styles.Count
        Inline_Styles = $inlineStyles.Count
        Fixed_Widths = $fixedWidths.Count
        Trans_Calls = $transCalls.Count
        BgWhite_NoDark = $bgWhiteNoDark
        TextGray900_NoDark = $textGray900NoDark
    }
}

$results | Format-Table File, Lines, H1_Count, Img_Total, Img_NoDims, Script_Tags, Inline_Styles, Fixed_Widths, Trans_Calls, BgWhite_NoDark -AutoSize
