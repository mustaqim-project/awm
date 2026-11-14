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

foreach ($f in $files) {
    $path = Join-Path 'C:\laragon\www\cooca_core' $f
    $lines = Get-Content $path
    $issues = @()
    for ($i = 0; $i -lt $lines.Count; $i++) {
        $l = $lines[$i]
        # check class string
        if ($l -match 'class="([^"]*)"') {
            $class = $matches[1]
            # 1. bg-white without dark:bg (excluding bg-white/opacity)
            if ($class -match '\bbg-white\b' -and $class -notmatch 'dark:bg-' -and $class -notmatch 'bg-white/') {
                $issues += "Line $($i+1) [bg-white without dark:bg]: $($l.Trim())"
            }
            # 2. bg-gray-50/100 or bg-slate-50/100 without dark:bg
            if (($class -match '\bbg-(slate|gray)-[51]00?\b') -and $class -notmatch 'dark:bg-' -and $class -notmatch 'bg-(slate|gray)-[51]00?/') {
                $issues += "Line $($i+1) [light bg without dark:bg]: $($l.Trim())"
            }
            # 3. text-slate-900 / text-gray-900 without dark:text
            if (($class -match '\btext-(slate|gray)-900\b') -and $class -notmatch 'dark:text-') {
                $issues += "Line $($i+1) [dark text without dark:text]: $($l.Trim())"
            }
            # 4. text-white on light bg or standalone without dark:
            # (only if inside a card or container that isn't explicitly dark)
        }
    }
    if ($issues.Count -gt 0) {
        Write-Host "=============================="
        Write-Host "FILE: $f ($($issues.Count) theme issues)"
        $issues | Select-Object -First 6 | ForEach-Object { Write-Host "  $_" }
    }
}
