<?php
/**
 * COOCA ERP - Comprehensive i18n Audit & Verification Tool
 *
 * Designed by Explorer 3 (Verification & Tooling Explorer)
 * Fulfills Requirements R1, R2, R3, R5, and Acceptance Criteria.
 */

declare(strict_types=1);

class I18nVerifier
{
    private string $workspace;
    private array $dictId = [];
    private array $dictEn = [];
    private array $dictFiles = [];
    private array $codebaseStaticKeys = [];
    private array $codebaseDynamicCalls = [];
    private array $branchKeys = [];
    private array $groupLoaders = [];

    public function __construct(string $workspace)
    {
        $this->workspace = rtrim(str_replace('\\', '/', $workspace), '/');
    }

    public function run(): array
    {
        $startTime = microtime(true);

        $this->loadDictionaries();
        $this->scanCodebase();
        $report = $this->analyze();

        $duration = microtime(true) - $startTime;
        $report['execution_time_seconds'] = round($duration, 3);

        return $report;
    }

    /**
     * Load all dictionary files and build flattened lookup tables + branch key indices.
     */
    private function loadDictionaries(): void
    {
        $idDir = $this->workspace . '/lang/id';
        $enDir = $this->workspace . '/lang/en';

        $idFiles = glob($idDir . '/*.php') ?: [];
        $enFiles = glob($enDir . '/*.php') ?: [];

        $allBaseNames = array_unique(array_merge(
            array_map('basename', $idFiles),
            array_map('basename', $enFiles)
        ));
        sort($allBaseNames);

        $this->dictFiles = $allBaseNames;

        foreach ($allBaseNames as $file) {
            $domain = basename($file, '.php');
            $idPath = $idDir . '/' . $file;
            $enPath = $enDir . '/' . $file;

            $idArr = file_exists($idPath) ? require $idPath : null;
            $enArr = file_exists($enPath) ? require $enPath : null;

            if (is_array($idArr)) {
                $this->dictId[$domain] = $idArr;
            }
            if (is_array($enArr)) {
                $this->dictEn[$domain] = $enArr;
            }
        }
    }

    /**
     * Recursively flatten array into dot-notation paths.
     */
    private function flatten(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string)$key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result += $this->flatten($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Recursively collect all branch node keys (intermediate arrays).
     */
    private function collectBranches(array $array, string $prefix = ''): array
    {
        $branches = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string)$key : $prefix . '.' . $key;
            if (is_array($value)) {
                $branches[] = $fullKey;
                $branches = array_merge($branches, $this->collectBranches($value, $fullKey));
            }
        }
        return $branches;
    }

    /**
     * Scan Blade, PHP, and JS files for translation calls.
     */
    private function scanCodebase(): void
    {
        $targets = [
            $this->workspace . '/resources/views/app',
            $this->workspace . '/resources/views/components',
            $this->workspace . '/resources/views/layouts',
            $this->workspace . '/resources/views/calculator.blade.php',
            $this->workspace . '/resources/views/dashboard.blade.php',
            $this->workspace . '/resources/js',
            $this->workspace . '/app',
            $this->workspace . '/routes',
        ];

        $files = [];
        foreach ($targets as $target) {
            if (is_file($target)) {
                $files[] = $target;
            } elseif (is_dir($target)) {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($it as $f) {
                    $ext = $f->getExtension();
                    $p = $f->getPathname();
                    if ($ext === 'php' || $ext === 'js' || str_ends_with($p, '.blade.php')) {
                        $files[] = $p;
                    }
                }
            }
        }

        // Translation patterns:
        // 1. Static call: __('key', trans('key', @lang('key', Lang::get('key', Lang::has('key', trans_choice('key', window.__('key'
        // 2. Dynamic concatenation: __('prefix.' . $var) or __("prefix.{$var}")
        $staticRegex = '/(?:\b(?:trans|__)|Lang::(?:get|has)|@lang|trans_choice|window\.__)\s*\(\s*([\'"])(.*?)\1/s';
        $dynamicConcatRegex = '/(?:\b(?:trans|__)|Lang::(?:get|has)|@lang|trans_choice|window\.__)\s*\(\s*([\'"][^\'"]*[\'"]\s*\.\s*[^,\)]+)/s';

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            $relPath = str_replace($this->workspace . '/', '', str_replace('\\', '/', $filePath));

            // Static matches
            if (preg_match_all($staticRegex, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[2] as $match) {
                    $rawKey = $match[0];
                    $offset = $match[1];

                    // Check for variable interpolation inside quotes
                    if (str_contains($rawKey, '$') || str_contains($rawKey, '{')) {
                        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                        $this->codebaseDynamicCalls[] = [
                            'file' => $relPath,
                            'line' => $line,
                            'type' => 'interpolation',
                            'snippet' => substr($content, max(0, $offset - 10), 80),
                            'expression' => $rawKey,
                        ];
                        continue;
                    }

                    // Check if followed by concatenation operator '.'
                    $afterOffset = $offset + strlen($rawKey) + 1; // after closing quote
                    $restOfLine = substr($content, $afterOffset, 30);
                    if (preg_match('/^\s*\./', $restOfLine)) {
                        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                        $this->codebaseDynamicCalls[] = [
                            'file' => $relPath,
                            'line' => $line,
                            'type' => 'concatenation_prefix',
                            'snippet' => substr($content, max(0, $offset - 10), 80),
                            'expression' => $rawKey,
                        ];
                        continue;
                    }

                    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                    if (!isset($this->codebaseStaticKeys[$rawKey])) {
                        $this->codebaseStaticKeys[$rawKey] = [];
                    }
                    $this->codebaseStaticKeys[$rawKey][] = [
                        'file' => $relPath,
                        'line' => $line,
                    ];
                }
            }

            // Dynamic concatenation matches
            if (preg_match_all($dynamicConcatRegex, $content, $dynMatches, PREG_OFFSET_CAPTURE)) {
                foreach ($dynMatches[1] as $dm) {
                    $offset = $dm[1];
                    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                    $this->codebaseDynamicCalls[] = [
                        'file' => $relPath,
                        'line' => $line,
                        'type' => 'concatenation_expression',
                        'snippet' => trim($dm[0]),
                        'expression' => trim($dm[0]),
                    ];
                }
            }
        }
    }

    /**
     * Cross-check codebase usage against dictionary tables and check internal parity.
     */
    private function analyze(): array
    {
        // 1. Flatten all ID and EN dictionaries
        $idFlat = [];
        $enFlat = [];
        $branchesId = [];
        $branchesEn = [];

        foreach ($this->dictId as $domain => $arr) {
            $flat = $this->flatten($arr);
            foreach ($flat as $k => $v) {
                $idFlat[$domain . '.' . $k] = $v;
            }
            foreach ($this->collectBranches($arr) as $b) {
                $branchesId[$domain . '.' . $b] = true;
            }
        }

        foreach ($this->dictEn as $domain => $arr) {
            $flat = $this->flatten($arr);
            foreach ($flat as $k => $v) {
                $enFlat[$domain . '.' . $k] = $v;
            }
            foreach ($this->collectBranches($arr) as $b) {
                $branchesEn[$domain . '.' . $b] = true;
            }
        }

        // 2. Dictionary Bilateral Parity Audit
        $idAllKeys = array_keys($idFlat);
        $enAllKeys = array_keys($enFlat);

        $dictMissingInEn = array_values(array_diff($idAllKeys, $enAllKeys));
        $dictMissingInId = array_values(array_diff($enAllKeys, $idAllKeys));

        $emptyInId = [];
        foreach ($idFlat as $k => $v) {
            if (!is_string($v) || trim($v) === '') {
                $emptyInId[] = $k;
            }
        }

        $emptyInEn = [];
        foreach ($enFlat as $k => $v) {
            if (!is_string($v) || trim($v) === '') {
                $emptyInEn[] = $k;
            }
        }

        // 3. Placeholder parameter audit (:param)
        $placeholderMismatches = [];
        $phRegex = '/:([a-zA-Z_][a-zA-Z0-9_]*)/';
        foreach ($idFlat as $k => $valId) {
            if (!isset($enFlat[$k]) || !is_string($valId) || !is_string($enFlat[$k])) continue;
            preg_match_all($phRegex, $valId, $mId);
            preg_match_all($phRegex, $enFlat[$k], $mEn);
            $pId = array_unique($mId[1]);
            $pEn = array_unique($mEn[1]);
            sort($pId);
            sort($pEn);
            if ($pId !== $pEn) {
                $placeholderMismatches[$k] = [
                    'id_placeholders' => $pId,
                    'en_placeholders' => $pEn,
                ];
            }
        }

        // 4. Codebase Static Keys Audit against Dictionary
        $registeredInBoth = [];
        $groupLoaders = [];
        $branchAccess = [];
        $missingInBoth = [];
        $missingInIdOnly = [];
        $missingInEnOnly = [];

        foreach ($this->codebaseStaticKeys as $key => $usages) {
            // Check if key is a dictionary group loader e.g. __('common') or __('pos')
            if (isset($this->dictId[$key]) && isset($this->dictEn[$key])) {
                $groupLoaders[$key] = $usages;
                continue;
            }

            // Check if key is a branch node (e.g. __('finance.categories'))
            if (isset($branchesId[$key]) && isset($branchesEn[$key])) {
                $branchAccess[$key] = $usages;
                continue;
            }

            $inId = array_key_exists($key, $idFlat);
            $inEn = array_key_exists($key, $enFlat);

            if ($inId && $inEn) {
                $registeredInBoth[$key] = $usages;
            } elseif ($inId && !$inEn) {
                $missingInEnOnly[$key] = $usages;
            } elseif (!$inId && $inEn) {
                $missingInIdOnly[$key] = $usages;
            } else {
                $missingInBoth[$key] = $usages;
            }
        }

        // 5. Dynamic Calls Deduplication
        $uniqueDynamicExpressions = [];
        foreach ($this->codebaseDynamicCalls as $dc) {
            $expr = $dc['expression'];
            if (!isset($uniqueDynamicExpressions[$expr])) {
                $uniqueDynamicExpressions[$expr] = [];
            }
            $uniqueDynamicExpressions[$expr][] = [
                'file' => $dc['file'],
                'line' => $dc['line'],
                'type' => $dc['type'],
            ];
        }

        return [
            'summary' => [
                'total_dictionary_files' => count($this->dictFiles),
                'total_registered_keys_id' => count($idFlat),
                'total_registered_keys_en' => count($enFlat),
                'dictionary_missing_in_en' => count($dictMissingInEn),
                'dictionary_missing_in_id' => count($dictMissingInId),
                'empty_translations_id' => count($emptyInId),
                'empty_translations_en' => count($emptyInEn),
                'placeholder_mismatches' => count($placeholderMismatches),
                'total_unique_codebase_static_keys' => count($this->codebaseStaticKeys),
                'codebase_registered_in_both' => count($registeredInBoth),
                'codebase_group_loaders' => count($groupLoaders),
                'codebase_branch_access' => count($branchAccess),
                'codebase_missing_in_both' => count($missingInBoth),
                'codebase_missing_in_en_only' => count($missingInEnOnly),
                'codebase_missing_in_id_only' => count($missingInIdOnly),
                'total_dynamic_calls' => count($this->codebaseDynamicCalls),
                'unique_dynamic_expressions' => count($uniqueDynamicExpressions),
            ],
            'issues' => [
                'codebase_missing_in_both' => $missingInBoth,
                'codebase_missing_in_en_only' => $missingInEnOnly,
                'codebase_missing_in_id_only' => $missingInIdOnly,
                'dictionary_missing_in_en' => $dictMissingInEn,
                'dictionary_missing_in_id' => $dictMissingInId,
                'empty_translations_id' => $emptyInId,
                'empty_translations_en' => $emptyInEn,
                'placeholder_mismatches' => $placeholderMismatches,
            ],
            'dynamic_expressions' => $uniqueDynamicExpressions,
            'group_loaders' => array_keys($groupLoaders),
            'branch_access' => array_keys($branchAccess),
        ];
    }
}

// CLI Execution Harness
$workspace = dirname(__DIR__);
$verifier = new I18nVerifier($workspace);
$results = $verifier->run();

$jsonOutput = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents(
    $workspace . '/tools/verifier_output.json',
    $jsonOutput
);

echo "=================================================================\n";
echo "           COOCA ERP - i18n AUDIT & VERIFICATION REPORT           \n";
echo "=================================================================\n";
$s = $results['summary'];
printf("Dictionary Files Scanned        : %d files\n", $s['total_dictionary_files']);
printf("Total Registered ID Keys        : %d\n", $s['total_registered_keys_id']);
printf("Total Registered EN Keys        : %d\n", $s['total_registered_keys_en']);
printf("Dictionary Parity Gaps (ID->EN) : %d\n", $s['dictionary_missing_in_en']);
printf("Dictionary Parity Gaps (EN->ID) : %d\n", $s['dictionary_missing_in_id']);
printf("Empty Values in ID              : %d\n", $s['empty_translations_id']);
printf("Empty Values in EN              : %d\n", $s['empty_translations_en']);
printf("Placeholder Mismatches (:param) : %d\n", $s['placeholder_mismatches']);
echo "-----------------------------------------------------------------\n";
printf("Codebase Unique Static Keys     : %d\n", $s['total_unique_codebase_static_keys']);
printf("Registered Leaf Keys in Code    : %d\n", $s['codebase_registered_in_both']);
printf("Dictionary Group Loaders (@json): %d\n", $s['codebase_group_loaders']);
printf("Branch Node Access (Arrays)     : %d\n", $s['codebase_branch_access']);
printf("Missing in BOTH ID and EN       : %d\n", $s['codebase_missing_in_both']);
printf("Missing in EN Only              : %d\n", $s['codebase_missing_in_en_only']);
printf("Missing in ID Only              : %d\n", $s['codebase_missing_in_id_only']);
echo "-----------------------------------------------------------------\n";
printf("Dynamic Calls in Code           : %d (across %d unique patterns)\n", $s['total_dynamic_calls'], $s['unique_dynamic_expressions']);
printf("Execution Time                  : %.3f seconds\n", $results['execution_time_seconds']);
echo "=================================================================\n\n";

if ($s['codebase_missing_in_both'] > 0) {
    echo "CRITICAL: The following keys are used in code but MISSING in both ID & EN:\n";
    $i = 1;
    foreach ($results['issues']['codebase_missing_in_both'] as $k => $usages) {
        printf("%2d. %-45s | %2d usages | %s:%d\n", $i++, $k, count($usages), $usages[0]['file'], $usages[0]['line']);
    }
} else {
    echo "STATUS: ZERO MISSING KEYS! 100% COVERAGE!\n";
}

if ($s['dictionary_missing_in_en'] > 0) {
    echo "\nWARNING: Keys present in ID but missing in EN:\n";
    foreach (array_slice($results['issues']['dictionary_missing_in_en'], 0, 10) as $k) {
        echo " - $k\n";
    }
}

if ($s['dictionary_missing_in_id'] > 0) {
    echo "\nWARNING: Keys present in EN but missing in ID:\n";
    foreach (array_slice($results['issues']['dictionary_missing_in_id'], 0, 10) as $k) {
        echo " - $k\n";
    }
}
