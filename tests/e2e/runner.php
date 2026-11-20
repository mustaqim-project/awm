<?php

declare(strict_types=1);

/**
 * COOCA E2E Standalone Test Suite Runner
 *
 * Requirements:
 * - Bootstraps Laravel application and test environment.
 * - Supports CLI arguments: --tier=1..4, --feature=F01..F21, --filter=regex, --verbose, --help.
 * - Discovers and executes tests extending BaseTestCase / TestCase.
 * - Outputs ANSI-colored test results, progress indicators, and summary table.
 * - Returns deterministic exit codes: 0 for all pass / clean, 1 for any failure.
 */

// 1. Check for Help Option Early
$rawArgs = $_SERVER['argv'] ?? [];
if (in_array('--help', $rawArgs, true) || in_array('-h', $rawArgs, true)) {
    echo "\n\033[1;36mCOOCA E2E Test Suite Runner\033[0m\n";
    echo "Usage:\n";
    echo "  php tests/e2e/runner.php [options]\n\n";
    echo "Options:\n";
    echo "  --tier=<1|2|3|4>      Filter tests by tier (Tier 1..4)\n";
    echo "  --feature=<F01..F21>  Filter tests by feature code (e.g. F07, F12)\n";
    echo "  --filter=<pattern>    Filter test class or method by substring or regex\n";
    echo "  --verbose, -v         Display verbose output and failure stack traces\n";
    echo "  --no-color            Disable ANSI color output\n";
    echo "  --help, -h            Show this help screen\n\n";
    echo "Examples:\n";
    echo "  php tests/e2e/runner.php\n";
    echo "  php tests/e2e/runner.php --tier=1\n";
    echo "  php tests/e2e/runner.php --feature=F07\n";
    echo "  php tests/e2e/runner.php --filter=retail\n";
    echo "  php tests/e2e/runner.php --tier=2 --verbose\n\n";
    exit(0);
}

// 2. ANSI Styling Helper
final class CliStyle
{
    public static bool $enabled = true;

    public static function color(string $text, string $code): string
    {
        if (! self::$enabled) {
            return $text;
        }

        return "\033[{$code}m{$text}\033[0m";
    }

    public static function green(string $text): string
    {
        return self::color($text, '32');
    }

    public static function boldGreen(string $text): string
    {
        return self::color($text, '1;32');
    }

    public static function red(string $text): string
    {
        return self::color($text, '31');
    }

    public static function boldRed(string $text): string
    {
        return self::color($text, '1;31');
    }

    public static function yellow(string $text): string
    {
        return self::color($text, '33');
    }

    public static function boldYellow(string $text): string
    {
        return self::color($text, '1;33');
    }

    public static function cyan(string $text): string
    {
        return self::color($text, '36');
    }

    public static function boldCyan(string $text): string
    {
        return self::color($text, '1;36');
    }

    public static function gray(string $text): string
    {
        return self::color($text, '90');
    }

    public static function bold(string $text): string
    {
        return self::color($text, '1');
    }
}

// Check --no-color flag
if (in_array('--no-color', $rawArgs, true)) {
    CliStyle::$enabled = false;
} elseif (DIRECTORY_SEPARATOR === '\\' && ! getenv('ANSICON') && ! getenv('ConEmuANSI') && ! getenv('WT_SESSION')) {
    // Attempt to enable virtual terminal processing on Windows if supported
    if (function_exists('sapi_windows_vt100_support') && defined('STDOUT')) {
        @sapi_windows_vt100_support(STDOUT, true);
    }
}

// 3. Parse CLI Options
$options = [
    'tier' => null,
    'feature' => null,
    'filter' => null,
    'verbose' => false,
];

foreach ($rawArgs as $idx => $arg) {
    if ($arg === '--verbose' || $arg === '-v') {
        $options['verbose'] = true;
    } elseif (str_starts_with($arg, '--tier=')) {
        $options['tier'] = trim(substr($arg, 7));
    } elseif ($arg === '--tier' || $arg === '-t') {
        $options['tier'] = trim($rawArgs[$idx + 1] ?? '');
    } elseif (str_starts_with($arg, '--feature=')) {
        $options['feature'] = strtoupper(trim(substr($arg, 10)));
    } elseif ($arg === '--feature' || $arg === '-f') {
        $options['feature'] = strtoupper(trim($rawArgs[$idx + 1] ?? ''));
    } elseif (str_starts_with($arg, '--filter=')) {
        $options['filter'] = trim(substr($arg, 9));
    } elseif ($arg === '--filter') {
        $options['filter'] = trim($rawArgs[$idx + 1] ?? '');
    }
}

// Normalize feature string (e.g., "F7" -> "F07")
if ($options['feature'] !== null) {
    if (preg_match('/^F([1-9])$/', $options['feature'], $m)) {
        $options['feature'] = 'F0' . $m[1];
    }
}

echo "\n" . CliStyle::boldCyan('╔════════════════════════════════════════════════════════════════════════════════╗') . "\n";
echo CliStyle::boldCyan('║') . ' ' . CliStyle::bold('COOCA ERP Dual-Platform E2E Test Suite Runner') . str_repeat(' ', 32) . CliStyle::boldCyan('║') . "\n";
echo CliStyle::boldCyan('║') . ' ' . CliStyle::gray('Environment: ' . php_uname('s') . ' | PHP ' . PHP_VERSION . ' | Laravel 13') . str_repeat(' ', 26) . CliStyle::boldCyan('║') . "\n";
echo CliStyle::boldCyan('╚════════════════════════════════════════════════════════════════════════════════╝') . "\n\n";

// 4. Bootstrap Laravel Application
$startTime = microtime(true);
echo CliStyle::cyan('⚡ Bootstrapping Laravel application & test environment...') . ' ';

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (! file_exists($autoloadPath)) {
    echo CliStyle::boldRed("FAILED!\nComposer autoload file not found at: {$autoloadPath}\n");
    exit(1);
}
require_once $autoloadPath;

// Initialize PHPUnit Configuration Registry for PHPUnit 12 compatibility
if (class_exists(\PHPUnit\TextUI\Configuration\Registry::class)) {
    try {
        \PHPUnit\TextUI\Configuration\Registry::get();
    } catch (\Throwable) {
        try {
            (new \PHPUnit\TextUI\Configuration\Builder)->build([]);
        } catch (\Throwable) {
            // Ignore config builder exception
        }
    }
}

$appBootstrapPath = __DIR__ . '/../../bootstrap/app.php';
if (! file_exists($appBootstrapPath)) {
    echo CliStyle::boldRed("FAILED!\nLaravel bootstrap/app.php not found at: {$appBootstrapPath}\n");
    exit(1);
}
$app = require_once $appBootstrapPath;
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo CliStyle::boldGreen("DONE\n");

// 5. Test Discovery Helper Functions
function parseClassFromFile(string $filePath): ?string
{
    $content = (string) @file_get_contents($filePath);
    if ($content === '') {
        return null;
    }

    $tokens = token_get_all($content);
    $namespace = '';
    $class = '';
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        if ($tokens[$i][0] === T_NAMESPACE) {
            $ns = '';
            for ($j = $i + 1; $j < $count; $j++) {
                if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                    break;
                }
                $ns .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
            }
            $namespace = trim($ns);
        }

        if ($tokens[$i][0] === T_CLASS) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($tokens[$j][0] === T_STRING) {
                    $class = $tokens[$j][1];
                    break;
                }
            }
            if ($class !== '') {
                break;
            }
        }
    }

    if ($class === '') {
        return null;
    }

    return $namespace !== '' ? "{$namespace}\\{$class}" : $class;
}

// 6. Locate Target Test Files
$searchDirs = [
    'Tier1_FeatureCoverage' => __DIR__ . '/Tier1_FeatureCoverage',
    'Tier2_BoundaryCorner' => __DIR__ . '/Tier2_BoundaryCorner',
    'Tier3_CrossFeature' => __DIR__ . '/Tier3_CrossFeature',
    'Tier4_RealWorldScenarios' => __DIR__ . '/Tier4_RealWorldScenarios',
];

$testFiles = [];

foreach ($searchDirs as $tierKey => $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
            $testFiles[] = [
                'tier' => $tierKey,
                'path' => $file->getPathname(),
                'filename' => $file->getFilename(),
            ];
        }
    }
}

// Sort test files deterministically by tier and filename
usort($testFiles, function ($a, $b) {
    if ($a['tier'] !== $b['tier']) {
        return strcmp($a['tier'], $b['tier']);
    }

    return strcmp($a['filename'], $b['filename']);
});

// 7. Filter Test Files Based on Options
$targetFiles = [];
foreach ($testFiles as $tf) {
    // Tier filter
    if ($options['tier'] !== null) {
        $tierNeedle = 'Tier' . $options['tier'];
        if (! str_starts_with($tf['tier'], $tierNeedle)) {
            continue;
        }
    }

    // Feature filter
    if ($options['feature'] !== null) {
        if (! str_contains(strtoupper($tf['filename']), $options['feature'])) {
            continue;
        }
    }

    $targetFiles[] = $tf;
}

$filterDescription = [];
if ($options['tier']) {
    $filterDescription[] = "Tier: {$options['tier']}";
}
if ($options['feature']) {
    $filterDescription[] = "Feature: {$options['feature']}";
}
if ($options['filter']) {
    $filterDescription[] = "Filter: {$options['filter']}";
}

if (! empty($filterDescription)) {
    echo CliStyle::yellow('Applied Filters: ' . implode(' | ', $filterDescription)) . "\n";
}

echo CliStyle::gray('Discovered ' . count($targetFiles) . ' test suite file(s).') . "\n\n";

if (empty($targetFiles)) {
    echo CliStyle::yellow("No test suites matched the specified criteria.\n");
    echo CliStyle::boldGreen("Exit code 0 (Clean).\n\n");
    exit(0);
}

// 8. Discover Test Methods and Plan Runs
$testPlan = [];
$totalTestMethods = 0;

foreach ($targetFiles as $tf) {
    require_once $tf['path'];
    $className = parseClassFromFile($tf['path']);

    if ($className === null || ! class_exists($className)) {
        continue;
    }

    $refClass = new ReflectionClass($className);
    if ($refClass->isAbstract() || ! $refClass->isSubclassOf(\PHPUnit\Framework\TestCase::class)) {
        continue;
    }

    $methods = [];
    foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isStatic() || $method->isConstructor() || $method->isDestructor()) {
            continue;
        }

        $mName = $method->getName();
        $doc = (string) $method->getDocComment();

        $isTest = str_starts_with($mName, 'test') || str_contains($doc, '@test');
        if (! $isTest) {
            continue;
        }

        // Apply method-level filter
        if ($options['filter'] !== null) {
            $fullName = "{$className}::{$mName}";
            $matched = str_contains(strtolower($fullName), strtolower($options['filter']))
                || @preg_match('/' . $options['filter'] . '/i', $fullName);

            if (! $matched) {
                continue;
            }
        }

        $methods[] = $mName;
        $totalTestMethods++;
    }

    if (! empty($methods)) {
        $testPlan[] = [
            'tier' => $tf['tier'],
            'filename' => $tf['filename'],
            'class' => $className,
            'methods' => $methods,
        ];
    }
}

if ($totalTestMethods === 0) {
    echo CliStyle::yellow("No test methods matched the specified filter criteria.\n");
    echo CliStyle::boldGreen("Exit code 0 (Clean).\n\n");
    exit(0);
}

// 9. Execute Test Suite
$suiteResults = [];
$failures = [];
$totalPass = 0;
$totalFail = 0;
$totalSkip = 0;
$totalAssertions = 0;

echo CliStyle::bold("Running {$totalTestMethods} test(s) across " . count($testPlan) . " suite(s):\n");

foreach ($testPlan as $suite) {
    $suiteClass = $suite['class'];
    $suiteTier = $suite['tier'];
    $suiteFile = $suite['filename'];
    $methods = $suite['methods'];

    $suitePass = 0;
    $suiteFail = 0;
    $suiteSkip = 0;
    $suiteStart = microtime(true);

    if ($options['verbose']) {
        echo "\n" . CliStyle::cyan("• [{$suiteTier}] {$suiteFile}") . "\n";
    }

    foreach ($methods as $methodName) {
        $testStart = microtime(true);
        $assertionsBefore = \PHPUnit\Framework\Assert::getCount();

        try {
            $testInstance = new $suiteClass($methodName);

            // Invoke setUp
            $refInstance = new ReflectionClass($testInstance);
            if ($refInstance->hasMethod('setUp')) {
                $setUp = $refInstance->getMethod('setUp');
                $setUp->setAccessible(true);
                $setUp->invoke($testInstance);
            }

            // Execute test method
            $testInstance->$methodName();

            // Invoke tearDown
            if ($refInstance->hasMethod('tearDown')) {
                $tearDown = $refInstance->getMethod('tearDown');
                $tearDown->setAccessible(true);
                $tearDown->invoke($testInstance);
            }

            $assertionsAfter = \PHPUnit\Framework\Assert::getCount();
            $totalAssertions += max(1, $assertionsAfter - $assertionsBefore);

            $suitePass++;
            $totalPass++;

            if ($options['verbose']) {
                $timeTaken = sprintf('%.3fs', microtime(true) - $testStart);
                echo '  ' . CliStyle::green('✓') . " {$methodName} " . CliStyle::gray("({$timeTaken})") . "\n";
            } else {
                echo CliStyle::green('.');
            }
        } catch (\PHPUnit\Framework\SkippedTestError $e) {
            $suiteSkip++;
            $totalSkip++;

            if ($options['verbose']) {
                echo '  ' . CliStyle::yellow('S') . " {$methodName} " . CliStyle::yellow('(Skipped: ' . $e->getMessage() . ')') . "\n";
            } else {
                echo CliStyle::yellow('S');
            }
        } catch (\Throwable $e) {
            $suiteFail++;
            $totalFail++;

            $failures[] = [
                'suite' => $suiteClass,
                'method' => $methodName,
                'file' => $suiteFile,
                'exception' => $e,
            ];

            if ($options['verbose']) {
                echo '  ' . CliStyle::red('✗') . " {$methodName} " . CliStyle::boldRed('(FAILED: ' . $e->getMessage() . ')') . "\n";
            } else {
                echo CliStyle::red('F');
            }
        } finally {
            // Guarantee context flush and rollback between tests
            try {
                \Tests\E2E\Support\Helpers\TransactionHelper::rollbackAll();
            } catch (\Throwable) {
            }
            \App\Support\Context::flush();
        }
    }

    $suiteDuration = microtime(true) - $suiteStart;
    $suiteResults[] = [
        'tier' => $suiteTier,
        'suite' => pathinfo($suiteFile, PATHINFO_FILENAME),
        'total' => count($methods),
        'pass' => $suitePass,
        'fail' => $suiteFail,
        'skip' => $suiteSkip,
        'duration' => $suiteDuration,
    ];
}

echo "\n\n";

// 10. Output Summary Table
$tierLabels = [
    'Tier1_FeatureCoverage' => 'Tier 1 (Coverage)',
    'Tier2_BoundaryCorner' => 'Tier 2 (Boundary)',
    'Tier3_CrossFeature' => 'Tier 3 (Cross)',
    'Tier4_RealWorldScenarios' => 'Tier 4 (Scenarios)',
];

echo CliStyle::boldCyan('┌────────────────────────┬──────────────────────────────────────────┬───────┬───────┬───────┬──────────┐') . "\n";
echo CliStyle::boldCyan('│') . ' ' . str_pad('Tier', 22) . ' '
    . CliStyle::boldCyan('│') . ' ' . str_pad('Feature / Test Suite', 40) . ' '
    . CliStyle::boldCyan('│') . ' ' . str_pad('Total', 5, ' ', STR_PAD_LEFT) . ' '
    . CliStyle::boldCyan('│') . ' ' . str_pad('Pass', 5, ' ', STR_PAD_LEFT) . ' '
    . CliStyle::boldCyan('│') . ' ' . str_pad('Fail', 5, ' ', STR_PAD_LEFT) . ' '
    . CliStyle::boldCyan('│') . ' ' . str_pad('Duration', 8, ' ', STR_PAD_LEFT) . ' '
    . CliStyle::boldCyan('│') . "\n";
echo CliStyle::boldCyan('├────────────────────────┼──────────────────────────────────────────┼───────┼───────┼───────┼──────────┤') . "\n";

foreach ($suiteResults as $sr) {
    $tierDisplay = $tierLabels[$sr['tier']] ?? $sr['tier'];
    $suiteDisplay = mb_strimwidth($sr['suite'], 0, 40, '...');
    $passFormatted = $sr['pass'] > 0 ? CliStyle::green(str_pad((string) $sr['pass'], 5, ' ', STR_PAD_LEFT)) : str_pad('0', 5, ' ', STR_PAD_LEFT);
    $failFormatted = $sr['fail'] > 0 ? CliStyle::boldRed(str_pad((string) $sr['fail'], 5, ' ', STR_PAD_LEFT)) : str_pad('0', 5, ' ', STR_PAD_LEFT);
    $durationStr = sprintf('%.2fs', $sr['duration']);

    echo CliStyle::boldCyan('│') . ' ' . str_pad($tierDisplay, 22) . ' '
        . CliStyle::boldCyan('│') . ' ' . str_pad($suiteDisplay, 40) . ' '
        . CliStyle::boldCyan('│') . ' ' . str_pad((string) $sr['total'], 5, ' ', STR_PAD_LEFT) . ' '
        . CliStyle::boldCyan('│') . ' ' . $passFormatted . ' '
        . CliStyle::boldCyan('│') . ' ' . $failFormatted . ' '
        . CliStyle::boldCyan('│') . ' ' . str_pad($durationStr, 8, ' ', STR_PAD_LEFT) . ' '
        . CliStyle::boldCyan('│') . "\n";
}

echo CliStyle::boldCyan('└────────────────────────┴──────────────────────────────────────────┴───────┴───────┴───────┴──────────┘') . "\n\n";

// 11. Detailed Failure Reports
if (! empty($failures)) {
    echo CliStyle::boldRed("FAILURES AND ERRORS (" . count($failures) . "):\n\n");

    foreach ($failures as $idx => $fail) {
        $num = $idx + 1;
        $class = $fail['suite'];
        $method = $fail['method'];
        $e = $fail['exception'];

        echo CliStyle::boldRed("{$num}) {$class}::{$method}\n");
        echo CliStyle::yellow("    " . get_class($e) . ": " . $e->getMessage()) . "\n";
        echo CliStyle::gray("    at " . $e->getFile() . ':' . $e->getLine()) . "\n";

        if ($options['verbose']) {
            echo CliStyle::gray("\n    Stack Trace:\n");
            $traceLines = explode("\n", $e->getTraceAsString());
            foreach (array_slice($traceLines, 0, 10) as $tLine) {
                echo CliStyle::gray("      {$tLine}\n");
            }
        }
        echo "\n";
    }
}

// 12. Final Overall Summary
$totalTime = microtime(true) - $startTime;
$timeFormatted = sprintf('%.2fs', $totalTime);

if ($totalFail === 0) {
    echo CliStyle::boldGreen("════════════════════════════════════════════════════════════════════════════════\n");
    echo CliStyle::boldGreen(" ✓ PASS: All {$totalPass} tests passed ({$totalAssertions} assertions) in {$timeFormatted}\n");
    echo CliStyle::boldGreen("════════════════════════════════════════════════════════════════════════════════\n\n");
    exit(0);
} else {
    echo CliStyle::boldRed("════════════════════════════════════════════════════════════════════════════════\n");
    echo CliStyle::boldRed(" ✗ FAIL: {$totalFail} failed, {$totalPass} passed ({$totalTestMethods} total, {$totalAssertions} assertions) in {$timeFormatted}\n");
    echo CliStyle::boldRed("════════════════════════════════════════════════════════════════════════════════\n\n");
    exit(1);
}
