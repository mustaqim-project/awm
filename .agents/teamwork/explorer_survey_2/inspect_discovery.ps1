$path = 'C:\laragon\www\cooca_core\resources\views\public\discovery\index.blade.php'
$lines = Get-Content $path
Write-Host "Total lines: $($lines.Count)"
Write-Host "--- Scripts ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<script') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
Write-Host "--- Images ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<img') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
Write-Host "--- Headings ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<h[1-3]') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
Write-Host "--- First 20 lines ---"
for ($i = 0; $i -lt [Math]::Min(20, $lines.Count); $i++) {
    Write-Host "$($lines[$i])"
}
