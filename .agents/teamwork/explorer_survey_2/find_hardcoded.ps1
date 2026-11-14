$files = @(
  'resources/views/public/solutions/fnb.blade.php',
  'resources/views/public/solutions/laundry.blade.php',
  'resources/views/public/solutions/manufacturing.blade.php',
  'resources/views/public/solutions/retail.blade.php',
  'resources/views/public/solutions/services.blade.php',
  'resources/views/public/solutions/workshop.blade.php'
)

foreach ($f in $files) {
    $path = Join-Path 'C:\laragon\www\cooca_core' $f
    $lines = Get-Content $path
    $hardcoded = @()
    for ($i = 0; $i -lt $lines.Count; $i++) {
        $l = $lines[$i].Trim()
        # look for <span>...</span> or <p>...</p> or <h3>...</h3> that has Indonesian text without __()
        if ($l -match '<(h[1-4]|span|p|a)[^>]*>([^<@\{]+)<\/\1>' -and $l -notmatch '__\(') {
            $txt = $matches[2].Trim()
            if ($txt.Length -gt 15 -and $txt -notmatch '^[0-9\s\.\,\:\-\/]+$') {
                $hardcoded += "Line $($i+1): $txt"
            }
        }
    }
    Write-Host "=============================="
    Write-Host "FILE: $f (Found $($hardcoded.Count) hardcoded text lines)"
    $hardcoded | Select-Object -First 5 | ForEach-Object { Write-Host "  $_" }
}
