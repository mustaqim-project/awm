<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Business;
use App\Support\Context;
use Illuminate\Pagination\LengthAwarePaginator;

// Setup in-memory mock tenant & user without DB insertion
$business = new Business();
$business->id = '99999999-9999-9999-9999-999999999999';
$business->name = 'Empirical Test Corp';
$business->slug = 'empirical-test';
$business->is_active = true;

$user = new User();
$user->id = '99999999-9999-9999-9999-999999999998';
$user->name = 'Empirical Challenger';
$user->email = 'challenger@cooca.test';
$user->business_id = $business->id;

Auth::login($user);
Context::setBusiness($business);

$renderResults = [];

// ==========================================
// TEST 1: analytics/index with 0 data
// ==========================================
try {
    $emptyAnalytics = [
        'period' => 'month',
        'period_label' => 'Bulan Ini',
        'from' => now()->startOfMonth()->toDateString(),
        'to' => now()->endOfMonth()->toDateString(),
        'current' => [
            'revenue' => 0,
            'orders_count' => 0,
            'avg_order_value' => 0,
            'gross_profit' => 0,
            'gross_margin_pct' => 0,
            'expenses' => 0,
            'net_profit' => 0,
        ],
        'prior' => [
            'revenue' => 0,
            'orders_count' => 0,
            'avg_order_value' => 0,
            'gross_profit' => 0,
            'gross_margin_pct' => 0,
            'expenses' => 0,
            'net_profit' => 0,
        ],
        'growth' => [
            'revenue' => null,
            'orders' => null,
            'profit' => null,
            'aov' => null,
        ],
        'trend' => [
            'labels' => [],
            'revenue' => [],
            'profit' => [],
        ],
        'hourly' => [],
        'payment_methods' => [],
        'top_products' => collect([]),
        'recent_transactions' => collect([]),
    ];

    $html = View::make('app.analytics.index', [
        'analytics' => $emptyAnalytics,
        'locations' => collect([]),
        'selectedLocationId' => null,
        'hasFinancialAccess' => true,
    ])->render();

    $renderResults['analytics_index'] = [
        'status' => 'PASS',
        'bytes' => strlen($html),
        'has_empty_indicator' => strpos($html, 'no_transaction_yet') !== false || strpos($html, 'Belum ada transaksi') !== false || strpos($html, '0') !== false
    ];
} catch (\Throwable $e) {
    $renderResults['analytics_index'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

// ==========================================
// TEST 2: approvals/inbox with 0 tickets
// ==========================================
try {
    $emptyPaginator = new LengthAwarePaginator([], 0, 10, 1, ['path' => url('/approvals')]);
    $emptyCounts = [
        'all' => 0,
        'purchase_order' => 0,
        'expense' => 0,
        'supplier_invoice' => 0,
    ];

    $html = View::make('app.approvals.inbox', [
        'pendingTickets' => $emptyPaginator,
        'counts' => $emptyCounts,
        'filterType' => null,
    ])->render();

    $renderResults['approvals_inbox'] = [
        'status' => 'PASS',
        'bytes' => strlen($html),
        'has_empty_state' => strpos($html, 'Semua Dokumen Sudah Rapi') !== false || strpos($html, 'empty_title') !== false || strpos($html, 'empty_inbox') !== false
    ];
} catch (\Throwable $e) {
    $renderResults['approvals_inbox'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

// ==========================================
// TEST 3: approvals/history with 0 history
// ==========================================
try {
    $emptyHistoryPaginator = new LengthAwarePaginator([], 0, 15, 1, ['path' => url('/approvals/history')]);
    $emptyHistoryCounts = [
        'all' => 0,
        'approved' => 0,
        'rejected' => 0,
    ];

    $html = View::make('app.approvals.history', [
        'historyTickets' => $emptyHistoryPaginator,
        'counts' => $emptyHistoryCounts,
        'filterStatus' => null,
        'filterType' => null,
        'dateFrom' => null,
        'dateTo' => null,
    ])->render();

    $renderResults['approvals_history'] = [
        'status' => 'PASS',
        'bytes' => strlen($html),
        'has_empty_state' => strpos($html, 'Belum Ada Arsip') !== false || strpos($html, 'empty_history') !== false || strpos($html, 'empty') !== false
    ];
} catch (\Throwable $e) {
    $renderResults['approvals_history'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

// ==========================================
// TEST 4: approvals/rules with 0 rules
// ==========================================
try {
    $html = View::make('app.approvals.rules', [
        'rules' => collect([]),
    ])->render();

    $renderResults['approvals_rules'] = [
        'status' => 'PASS',
        'bytes' => strlen($html),
        'has_empty_state' => strpos($html, 'Belum Ada Aturan') !== false || strpos($html, 'empty_rules') !== false || strpos($html, 'Buat Aturan Pertama') !== false
    ];
} catch (\Throwable $e) {
    $renderResults['approvals_rules'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

// ==========================================
// TEST 5: billing/limits with 0 data
// ==========================================
try {
    $emptyBillingHistory = new LengthAwarePaginator([], 0, 10, 1, ['path' => url('/billing/history')]);
    
    $emptyLimits = [
        'plan_name' => 'Free Solo',
        'plan_label' => 'Gratis Selamanya',
        'is_active' => true,
        'expires_at' => null,
        'days_remaining' => null,
        'usage' => [
            'transactions' => ['used' => 0, 'limit' => 100, 'pct' => 0],
            'products' => ['used' => 0, 'limit' => 50, 'pct' => 0],
            'outlets' => ['used' => 1, 'limit' => 1, 'pct' => 100],
            'users' => ['used' => 1, 'limit' => 2, 'pct' => 50],
            'storage_mb' => ['used' => 0, 'limit' => 100, 'pct' => 0],
            'ai_tokens' => ['used' => 0, 'limit' => 10000, 'pct' => 0],
        ],
    ];

    $emptyStorage = [
        'used_bytes' => 0,
        'allocated_bytes' => 104857600,
        'used_formatted' => '0 B',
        'allocated_formatted' => '100 MB',
        'used_pct' => 0,
        'breakdown' => [],
        'largest_files' => [],
    ];

    $html = View::make('app.billing.limits', [
        'subscription' => null,
        'limits' => $emptyLimits,
        'storageDetails' => $emptyStorage,
        'billingHistory' => $emptyBillingHistory,
        'plans' => [],
        'addons' => [],
    ])->render();

    $renderResults['billing_limits'] = [
        'status' => 'PASS',
        'bytes' => strlen($html)
    ];
} catch (\Throwable $e) {
    $renderResults['billing_limits'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

// ==========================================
// TEST 6: docs/index with all 3 partials
// ==========================================
try {
    $html = View::make('app.docs.index')->render();

    $renderResults['docs_index_and_partials'] = [
        'status' => 'PASS',
        'bytes' => strlen($html),
        'has_analytics_doc' => strpos($html, 'sec-analytics') !== false,
        'has_approvals_doc' => strpos($html, 'sec-approvals') !== false,
        'has_billing_doc' => strpos($html, 'sec-billing') !== false,
    ];
} catch (\Throwable $e) {
    $renderResults['docs_index_and_partials'] = [
        'status' => 'FAIL',
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ];
}

echo json_encode($renderResults, JSON_PRETTY_PRINT);
