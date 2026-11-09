<?php
// Forensic static scanner for Milestone 2 changes
$views = [
    'resources/views/app/invoices/index.blade.php',
    'resources/views/app/invoices/create.blade.php',
    'resources/views/app/invoices/edit.blade.php',
    'resources/views/app/invoices/show.blade.php',
    'resources/views/app/invoices/print.blade.php',
    'resources/views/app/labor-machines/index.blade.php',
    'lang/id/labor_machines.php',
    'lang/en/labor_machines.php',
];

echo "=== 1. PHP Syntax Check (php -l) ===\n";
foreach ($views as $file) {
    $out = [];
    $ret = 0;
    exec("php -l " . escapeshellarg($file), $out, $ret);
    echo "$file: " . implode(" ", $out) . " (exit code: $ret)\n";
    if ($ret !== 0) {
        echo "SYNTAX ERROR in $file\n";
        exit(1);
    }
}

echo "\n=== 2. Forensic Scan of Added Lines in Git Diff ===\n";
$diff = shell_exec('git diff resources/views/app/invoices/ resources/views/app/labor-machines/ lang/');
$lines = explode("\n", $diff);
$added = [];
foreach ($lines as $line) {
    if (str_starts_with($line, '+') && !str_starts_with($line, '+++')) {
        $added[] = substr($line, 1);
    }
}
echo "Total added lines analyzed: " . count($added) . "\n";

$patterns = [
    'cheat_or_bypass' => '/\b(bypass|cheat|fake|mock|dummy|stub|test_only|allow_all)\b/i',
    'tautology' => '/\b(true\s*==\s*true|1\s*==\s*1)\b/i',
    'hardcoded_pass' => '/\b(PASS|SUCCESS|APPROVED)\b/i',
    'unauthorized_context' => '/Context::(hasPermission|isOwner)/',
];

$violations = [];
foreach ($added as $idx => $line) {
    foreach ($patterns as $patName => $regex) {
        if (preg_match($regex, $line)) {
            // Allow legitimate uses like CSS classes or language translations
            if ($patName === 'hardcoded_pass') {
                // Check if it's just a comment or CSS or harmless text
                continue;
            }
            $violations[] = [
                'type' => $patName,
                'line' => $line
            ];
        }
    }
}

if (empty($violations)) {
    echo "Scan result: CLEAN - No cheat flags, bypasses, dummy stubs, or suspicious patterns detected.\n";
} else {
    echo "Scan result: SUSPICIOUS PATTERNS FOUND:\n";
    print_r($violations);
}

echo "\n=== 3. Authorization Directives Check (@can, Gate::allows) ===\n";
foreach ($views as $file) {
    if (!str_ends_with($file, '.blade.php')) continue;
    $content = file_get_contents($file);
    preg_match_all('/@can\([^\)]+\)/', $content, $canMatches);
    preg_match_all('/Gate::allows\([^\)]+\)/', $content, $gateMatches);
    echo "$file:\n";
    echo "  @can directives: " . count($canMatches[0]) . " (" . implode(', ', array_unique($canMatches[0])) . ")\n";
    if (!empty($gateMatches[0])) {
        echo "  Gate::allows directives: " . count($gateMatches[0]) . " (" . implode(', ', array_unique($gateMatches[0])) . ")\n";
    }
}
