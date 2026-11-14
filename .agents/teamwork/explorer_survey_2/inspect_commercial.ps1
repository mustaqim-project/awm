$files = @(
  'resources/views/public/landing_commercial/kasir_android.blade.php',
  'resources/views/public/landing_commercial/kasir_gratis.blade.php',
  'resources/views/public/landing_commercial/kasir_offline.blade.php'
)

foreach ($f in $files) {
    $path = Join-Path 'C:\laragon\www\cooca_core' $f
    $lines = Get-Content $path
    Write-Host "=============================="
    Write-Host "FILE: $f (Total lines: $($lines.Count))"
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
    Write-Host "--- First 15 lines of content ---"
    for ($i = 0; $i -lt [Math]::Min(15, $lines.Count); $i++) {
        Write-Host "  $($lines[$i])"
    }
}
