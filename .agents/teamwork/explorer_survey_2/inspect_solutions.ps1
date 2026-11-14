$files = @(
  'resources/views/public/solutions/fnb.blade.php',
  'resources/views/public/solutions/laundry.blade.php',
  'resources/views/public/solutions/manufacturing.blade.php',
  'resources/views/public/solutions/retail.blade.php',
  'resources/views/public/solutions/services.blade.php',
  'resources/views/public/solutions/show.blade.php',
  'resources/views/public/solutions/workshop.blade.php'
)

foreach ($f in $files) {
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
    Write-Host "--- Images ---"
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match '<img') {
            Write-Host "  Line $($i+1): $($lines[$i].Trim())"
        }
    }
}
