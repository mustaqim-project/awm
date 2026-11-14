$path = 'C:\laragon\www\cooca_core\resources\views\landing.blade.php'
$lines = Get-Content $path

Write-Host "Total lines: $($lines.Count)"
Write-Host "--- Sections and Includes ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '@section|@push|@include') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Scripts ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<script') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Images ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<img') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- bg-white without dark: ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'class="[^"]*\bbg-white\b' -and $lines[$i] -notmatch 'dark:bg-') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
