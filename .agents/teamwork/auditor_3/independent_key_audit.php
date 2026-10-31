<?php
/**
 * Independent Victory Key & Parity Forensic Auditor
 * Authored by teamwork_victory_auditor (auditor_3)
 */
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Lang;

$viewDirs = [
    resource_path('views/app'),
    resource_path('views/admin'),
];

$pattern = '/(?:__|@lang|trans|trans_choice)\s*\(\s*([\'"])([^\'"\$]+?)\1/s';
$allKeys = [];
$totalCalls = 0;

foreach ($viewDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
            $path = $f->getPathname();
            $content = file_get_contents($path);
            $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
            if (preg_match_all($pattern, $clean, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $m) {
                    $totalCalls++;
                    $key = trim($m[2][0]);
                    $offset = $m[0][1];
                    $lineNo = substr_count(substr($clean, 0, $offset), "\n") + 1;
                    
                    // Filter dynamic calls
                    $after = ltrim(substr($clean, $offset + strlen($m[0][0]), 20));
                    $isDynamic = str_starts_with($after, '.') || str_ends_with($key, '.') || str_ends_with($key, '_');
                    
                    if ($isDynamic || empty($key) || str_contains($key, '$') || str_contains($key, '{') || str_contains($key, ' ')) {
                        continue;
                    }
                    
                    if (!isset($allKeys[$key])) {
                        $allKeys[$key] = [];
                    }
                    $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                    $allKeys[$key][] = "$rel:$lineNo";
                }
            }
        }
    }
}

$missingId = [];
$missingEn = [];
$dummyId = [];
$dummyEn = [];
$resolvedCount = 0;

foreach ($allKeys as $key => $locs) {
    if (!str_contains($key, '.')) {
        $valId = Lang::get($key, [], 'id');
        $valEn = Lang::get($key, [], 'en');
        if (!is_array($valId)) $missingId[$key] = $locs;
        if (!is_array($valEn)) $missingEn[$key] = $locs;
        continue;
    }

    $hasId = Lang::has($key, 'id');
    $hasEn = Lang::has($key, 'en');

    if (!$hasId) {
        $missingId[$key] = $locs;
    } else {
        $transId = Lang::get($key, [], 'id');
        if (is_string($transId)) {
            $t = trim($transId);
            if ($t === '' || $t === $key || in_array(strtolower($t), ['todo', 'dummy', 'test', 'xxx', 'placeholder'])) {
                $dummyId[$key] = $transId;
            }
        }
    }

    if (!$hasEn) {
        $missingEn[$key] = $locs;
    } else {
        $transEn = Lang::get($key, [], 'en');
        if (is_string($transEn)) {
            $t = trim($transEn);
            if ($t === '' || $t === $key || in_array(strtolower($t), ['todo', 'dummy', 'test', 'xxx', 'placeholder'])) {
                $dummyEn[$key] = $transEn;
            }
        }
    }

    if ($hasId && $hasEn) {
        $resolvedCount++;
    }
}

echo "======================================================================\n";
echo "AUDITOR_3 INDEPENDENT TRANSLATION KEY & FORENSIC AUDIT\n";
echo "======================================================================\n";
echo "Total translation calls found in Blade views: $totalCalls\n";
echo "Total distinct static keys checked: " . count($allKeys) . "\n";
echo "Keys fully resolved in BOTH ID & EN: $resolvedCount\n";
echo "Missing in ID count: " . count($missingId) . "\n";
echo "Missing in EN count: " . count($missingEn) . "\n";
echo "Suspicious/dummy translations in ID: " . count($dummyId) . "\n";
echo "Suspicious/dummy translations in EN: " . count($dummyEn) . "\n";

if (count($missingId) > 0) {
    echo "\n--- MISSING IN ID SAMPLE (up to 10) ---\n";
    $i = 0;
    foreach ($missingId as $k => $locs) {
        echo "  - $k (e.g. {$locs[0]})\n";
        if (++$i >= 10) break;
    }
}

if (count($missingEn) > 0) {
    echo "\n--- MISSING IN EN SAMPLE (up to 10) ---\n";
    $i = 0;
    foreach ($missingEn as $k => $locs) {
        echo "  - $k (e.g. {$locs[0]})\n";
        if (++$i >= 10) break;
    }
}

// Check dictionary file-level parity
$idFiles = glob(base_path('lang/id/*.php'));
$enFiles = glob(base_path('lang/en/*.php'));
$idFileNames = array_map('basename', $idFiles);
$enFileNames = array_map('basename', $enFiles);

$missingLangEnFiles = array_diff($idFileNames, $enFileNames);
$missingLangIdFiles = array_diff($enFileNames, $idFileNames);

echo "\n--- LANGUAGE DICTIONARY FILE PARITY ---\n";
echo "lang/id file count: " . count($idFileNames) . "\n";
echo "lang/en file count: " . count($enFileNames) . "\n";
echo "Files in ID missing in EN: " . count($missingLangEnFiles) . "\n";
echo "Files in EN missing in ID: " . count($missingLangIdFiles) . "\n";

// Audit the 61 auditor_2 previously missing keys specifically
$prevAudit61 = [
    'purchasing.orders.order_date',
    'storefront.reservations.status_confirmed',
    'storefront.reservations.status_seated',
    'storefront.reservations.status_completed',
    'storefront.reservations.status_cancelled',
    'storefront.reservations.status_no_show',
    'storefront.reservations.status_pending',
    'storefront.reservations.table_label_short',
    'storefront.reservations.btn_select_table',
    'storefront.reservations.action_confirm',
    'storefront.reservations.action_seat',
    'storefront.reservations.action_complete',
    'storefront.reservations.btn_open_pos',
    'storefront.reservations.btn_change_table',
    'storefront.reservations.btn_cancel_rsv',
    'storefront.reservations.modal_assign_title',
    'storefront.reservations.modal_assign_rsv_label',
    'storefront.reservations.modal_assign_select_table',
    'storefront.reservations.modal_assign_choose_placeholder',
    'storefront.reservations.modal_assign_table_option',
    'storefront.reservations.modal_assign_btn_cancel',
    'storefront.reservations.modal_assign_btn_save',
    'storefront.reservations.modal_cancel_title',
    'storefront.reservations.modal_cancel_desc',
    'storefront.reservations.modal_cancel_peace_of_mind',
    'storefront.reservations.modal_cancel_btn_close',
    'storefront.reservations.modal_cancel_btn_confirm',
    'common.rejected',
    'common.settings',
    'common.back_to_list',
    'billing.cooca_security_guarantee_desc',
    'billing.step_choose_package_subtitle',
    'billing.step_choose_package',
    'sales.invoices.confirm_delete_draft_desc',
    'sales.returns.comp_store_credit',
    'finance.col_account_code',
    'finance.col_account_name',
    'products.unit',
    'products.category',
    'auth.resend_otp',
    'pos.terminal',
    'pos.touchscreen_cashier',
    'services.manage_services',
    'hrm.tooltip_delete_shift',
    'hrm.label_location',
    'hrm.tooltip_delete_schedule',
    'finance.petty_cash_label',
    'finance.kpi_ready_import_count',
    'finance.kpi_duplicate_count',
    'finance.kpi_error_count',
    'common.no_notifications',
    'common.optional',
    'common.customer',
    'common.transactions',
    'common.completed',
    'common.master_data',
    'common.business',
    'common.other',
    'products.title',
    'storefront.open_store',
    'storefront.reservations.guest_count_mobile'
];

echo "\n--- SPOT CHECK OF AUDITOR_2'S 61 PREVIOUSLY MISSING KEYS ---\n";
$audit61Passed = 0;
$audit61Failed = 0;
foreach ($prevAudit61 as $k) {
    $hasId = Lang::has($k, 'id');
    $hasEn = Lang::has($k, 'en');
    if ($hasId && $hasEn) {
        $audit61Passed++;
    } else {
        $audit61Failed++;
        echo "  [FAIL] $k: hasId=" . ($hasId ? 'true' : 'false') . ", hasEn=" . ($hasEn ? 'true' : 'false') . "\n";
    }
}
echo "Spot check result: $audit61Passed / " . count($prevAudit61) . " PASSED, $audit61Failed FAILED\n";

if (count($missingId) === 0 && count($missingEn) === 0 && $audit61Failed === 0) {
    echo "\n>>> OVERALL KEY AUDIT VERDICT: PASS <<<\n";
} else {
    echo "\n>>> OVERALL KEY AUDIT VERDICT: FAIL <<<\n";
}
