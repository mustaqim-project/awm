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
  $content = Get-Content (Join-Path 'C:\laragon\www\cooca_core' $f) -Raw
  $extends = [regex]::Matches($content, '@extends\([^\)]+\)') | ForEach-Object { $_.Value }
  $includes = [regex]::Matches($content, '@include\([^\)]+\)') | ForEach-Object { $_.Value }
  $hasHtml = $content -match '<!DOCTYPE|<html'
  $hasScript = [regex]::Matches($content, '<script[\s\S]*?<\/script>').Count
  $hasStyle = [regex]::Matches($content, '<style[\s\S]*?<\/style>').Count
  Write-Host "FILE: $f"
  Write-Host "  Extends: $(($extends -join ', '))"
  Write-Host "  Has <!DOCTYPE/<html>: $hasHtml"
  Write-Host "  Includes: $(($includes -join ', '))"
  Write-Host "  Script tags: $hasScript | Style tags: $hasStyle"
  Write-Host "---------------------------------------"
}
