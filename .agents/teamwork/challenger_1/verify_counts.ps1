$appDir = Resolve-Path "resources\views\app"
$bladeFiles = Get-ChildItem -Path $appDir -Filter "*.blade.php" -Recurse -File
$allFiles = Get-ChildItem -Path $appDir -Recurse -File
$nonBlade = $allFiles | Where-Object { $_.Name -notlike "*.blade.php" }

Write-Host "Total Blade files: $($bladeFiles.Count)"
Write-Host "Total All files: $($allFiles.Count)"
Write-Host "Non-Blade files: $($nonBlade.Count)"

$totalLines = 0
foreach ($f in $bladeFiles) {
    $lines = (Get-Content $f.FullName | Measure-Object -Line).Lines
    $totalLines += $lines
}
Write-Host "Total Lines in Blade files: $totalLines"

# Subdirectories at root of resources\views\app
$rootSubdirs = Get-ChildItem -Path $appDir -Directory
Write-Host "Root subdirectories count: $($rootSubdirs.Count)"
foreach ($d in $rootSubdirs) {
    $count = (Get-ChildItem -Path $d.FullName -Filter "*.blade.php" -Recurse -File).Count
    Write-Host " - $($d.Name): $count blade files"
}
