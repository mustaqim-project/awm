$path = 'C:\laragon\www\cooca_core\resources\views\public\business_landing.blade.php'
$lines = Get-Content $path

Write-Host "Total lines: $($lines.Count)"
Write-Host "--- Scripts ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<script') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Styles ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<style') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Images (first 10) ---"
$count = 0
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<img') {
        $count++
        if ($count -le 10) {
            Write-Host "Line $($i+1): $($lines[$i].Trim())"
        }
    }
}
Write-Host "Total images found: $count"

Write-Host "`n--- External JS / CDNs loaded ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'src="http|<script src=') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
