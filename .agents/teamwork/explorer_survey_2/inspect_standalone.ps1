$standalone = @(
  'resources/views/public/about.blade.php',
  'resources/views/public/demo.blade.php',
  'resources/views/public/pricing.blade.php',
  'resources/views/public/privacy.blade.php',
  'resources/views/public/security.blade.php',
  'resources/views/public/sitemap.blade.php',
  'resources/views/public/support.blade.php',
  'resources/views/public/terms.blade.php'
)

foreach ($f in $standalone) {
    $path = Join-Path 'C:\laragon\www\cooca_core' $f
    $lines = Get-Content $path
    Write-Host "=============================="
    Write-Host "FILE: $f (Lines: $($lines.Count))"
    Write-Host "--- Scripts ---"
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match '<script') {
            Write-Host "  Line $($i+1): $($lines[$i].Trim())"
        }
    }
    Write-Host "--- Headings ---"
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match '<h[1-3]') {
            Write-Host "  Line $($i+1): $($lines[$i].Trim())"
        }
    }
    Write-Host "--- Hardcoded text samples outside blade ---"
    $raw = Get-Content $path -Raw
    $trans = [regex]::Matches($raw, '__\([^\)]+\)').Count
    Write-Host "  Translations count: $trans"
}
