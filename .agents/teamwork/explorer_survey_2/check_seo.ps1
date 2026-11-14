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
    $raw = Get-Content (Join-Path 'C:\laragon\www\cooca_core' $f) -Raw
    $hasCanonical = $raw -match "@section\('canonical'" -or $raw -match '<link rel="canonical"' -or $raw -match "'url' =>"
    $hasJsonLd = $raw -match 'application/ld\+json'
    $hasTitle = $raw -match "@section\('title'" -or $raw -match '<title'
    $hasDesc = $raw -match "@section\('description'" -or $raw -match 'meta name="description"'
    Write-Host "$f"
    Write-Host "  Canonical: $hasCanonical | JSON-LD: $hasJsonLd | Title: $hasTitle | Desc: $hasDesc"
}
