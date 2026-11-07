<?php

$viewsDir = __DIR__ . '/../../../resources/views/app';
$views = [
    'analytics/index.blade.php',
    'approvals/inbox.blade.php',
    'approvals/history.blade.php',
    'approvals/rules.blade.php',
    'billing/limits.blade.php',
    'docs/index.blade.php',
    'docs/partials/sec_analytics.blade.php',
    'docs/partials/sec_approvals.blade.php',
    'docs/partials/sec_billing.blade.php',
];

$results = [];

// 1. Check analytics KPI container
$analyticsContent = file_get_contents("$viewsDir/analytics/index.blade.php");
$kpiMatches = [];
if (preg_match('/<div[^>]*class="[^"]*(flex[^"]*overflow-x-auto[^"]*snap-x[^"]*no-scrollbar[^"]*)"/i', $analyticsContent, $kpiMatches)) {
    $results['analytics_kpi_container'] = [
        'status' => 'PASS',
        'classes' => $kpiMatches[1]
    ];
} else {
    $results['analytics_kpi_container'] = [
        'status' => 'FAIL',
        'reason' => 'KPI card container does not match flex overflow-x-auto snap-x no-scrollbar pattern'
    ];
}

// 2. Check approvals inbox action buttons
$approvalsContent = file_get_contents("$viewsDir/approvals/inbox.blade.php");
$buttonGridMatches = [];
if (preg_match('/<div[^>]*class="[^"]*(grid\s+grid-cols-2[^"]*)"/i', $approvalsContent, $buttonGridMatches)) {
    $results['approvals_button_grid'] = [
        'status' => 'PASS',
        'classes' => $buttonGridMatches[1]
    ];
} else {
    $results['approvals_button_grid'] = [
        'status' => 'FAIL',
        'reason' => 'grid grid-cols-2 not found for action buttons'
    ];
}

// Accurate check for openRejectModal and openApproveModal min-h-[44px]
$hasRejectMinH = false;
$hasApproveMinH = false;
if (preg_match('/openRejectModal[\s\S]*?class="([^"]*)"/i', $approvalsContent, $mReject)) {
    $hasRejectMinH = strpos($mReject[1], 'min-h-[44px]') !== false;
}
if (preg_match('/openApproveModal[\s\S]*?class="([^"]*)"/i', $approvalsContent, $mApprove)) {
    $hasApproveMinH = strpos($mApprove[1], 'min-h-[44px]') !== false;
}

$results['approvals_button_tap_targets'] = [
    'reject_min_44px' => $hasRejectMinH ? 'PASS' : 'FAIL',
    'approve_min_44px' => $hasApproveMinH ? 'PASS' : 'FAIL',
    'reject_classes' => $mReject[1] ?? '',
    'approve_classes' => $mApprove[1] ?? ''
];

// 3. Scan for hardcoded fixed widths > 350px (e.g. w-[1200px], w-[800px], w-[500px]) on mobile (< md)
$hardcodedWidths = [];
foreach ($views as $v) {
    $path = "$viewsDir/$v";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (preg_match_all('/\b(w|min-w)-\[(\d+)px\]/i', $content, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[2] as $idx => $match) {
                $val = (int)$match[0];
                $offset = $m[0][$idx][1];
                $preceding = substr($content, max(0, $offset - 4), 4);
                if ($val > 350 && $preceding !== 'max-') {
                    // Check enclosing element classes
                    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                    $lineStr = explode("\n", $content)[$line - 1];
                    $hardcodedWidths[] = [
                        'view' => $v,
                        'token' => $m[0][$idx][0],
                        'val' => $val,
                        'line' => $line,
                        'line_snippet' => trim($lineStr)
                    ];
                }
            }
        }
    }
}
$results['hardcoded_large_widths'] = $hardcodedWidths;

echo json_encode($results, JSON_PRETTY_PRINT);
