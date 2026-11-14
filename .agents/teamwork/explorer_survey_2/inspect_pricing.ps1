$path = 'C:\laragon\www\cooca_core\resources\views\public\pricing.blade.php'
$lines = Get-Content $path

Write-Host "--- bg-white without dark: ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'class="[^"]*\bbg-white\b' -and $lines[$i] -notmatch 'dark:bg-') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Tables and Overflows ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<table|overflow-x|fixed|w-\[') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}

Write-Host "`n--- Buttons / Links with touch target < 44px ---"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '<a |<button ' -and $lines[$i] -match 'h-[6-9]|py-1|py-0\.5|px-2' -and $lines[$i] -notmatch 'min-h-\[4|min-h-\[44|h-10|h-11|h-12|h-14') {
        Write-Host "Line $($i+1): $($lines[$i].Trim())"
    }
}
