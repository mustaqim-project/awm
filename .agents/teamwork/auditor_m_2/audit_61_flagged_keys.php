<?php
/**
 * Specific audit of the 61 previously missing keys flagged by Sentinel
 */
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Lang;

$flaggedKeys = [
    // Purchasing
    'purchasing.orders.order_date',
    'purchasing.statuses.draft',
    'purchasing.statuses.pending',
    'purchasing.statuses.approved',
    'purchasing.statuses.rejected',
    'purchasing.statuses.ordered',
    'purchasing.statuses.partially_received',
    'purchasing.statuses.received',
    'purchasing.statuses.partially_invoiced',
    'purchasing.statuses.fully_invoiced',
    'purchasing.statuses.completed',
    'purchasing.statuses.cancelled',
    'purchasing.statuses.',
    'purchasing.po_print_created_by',
    'purchasing.po_print_purchasing_dept',
    'purchasing.po_print_sign_stamp',
    'purchasing.po_print_generating_pdf',

    // Storefront reservations
    'storefront.open_store',
    'storefront.reservations.status_confirmed',
    'storefront.reservations.status_seated',
    'storefront.reservations.status_completed',
    'storefront.reservations.status_cancelled',
    'storefront.reservations.status_no_show',
    'storefront.reservations.status_pending',
    'storefront.reservations.guest_count_mobile',
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

    // Common
    'common.rejected',
    'common.settings',
    'common.back_to_list',
    'common.business',
    'common.completed',
    'common.customer',
    'common.master_data',
    'common.no_notifications',
    'common.optional',
    'common.other',
    'common.transactions',

    // Billing
    'billing.cooca_security_guarantee_desc',
    'billing.step_choose_package_subtitle',
    'billing.step_choose_package',

    // Sales
    'sales.invoices.confirm_delete_draft_desc',
    'sales.returns.comp_store_credit',

    // Finance
    'finance.col_account_code',
    'finance.col_account_name',
    'finance.petty_cash_label',
    'finance.kpi_ready_import_count',
    'finance.kpi_duplicate_count',
    'finance.kpi_error_count',
    'finance.categories.',
    'finance.inflow_categories.',

    // Products
    'products.unit',
    'products.category',
    'products.title',

    // Auth
    'auth.resend_otp',

    // POS
    'pos.terminal',
    'pos.touchscreen_cashier',

    // HRM
    'hrm.label_location',
    'hrm.tooltip_delete_shift',
    'hrm.tooltip_delete_schedule',

    // Services
    'services.manage_services',

    // Feedback
    'feedback.status_pending',
    'feedback.status_reviewed',
    'feedback.status_',

    // Inventory
    'inventory.movement_types.',

    // Social Media
    'social_media.format_story',
    'social_media.format_reel',
    'social_media.format_',
];

$results = [];
foreach ($flaggedKeys as $key) {
    $hasId = Lang::has($key, 'id');
    $hasEn = Lang::has($key, 'en');
    $valId = Lang::get($key, [], 'id');
    $valEn = Lang::get($key, [], 'en');

    $results[$key] = [
        'has_id' => $hasId,
        'has_en' => $hasEn,
        'val_id' => $valId,
        'val_en' => $valEn,
        'clean' => $hasId && $hasEn && !empty($valId) && !empty($valEn) && $valId !== $key && $valEn !== $key
    ];
}

$allClean = true;
$failed = [];
foreach ($results as $k => $info) {
    if (!$info['clean']) {
        $allClean = false;
        $failed[$k] = $info;
    }
}

echo json_encode([
    'total_checked' => count($flaggedKeys),
    'all_clean' => $allClean,
    'failed_count' => count($failed),
    'failed' => $failed,
    'sample_values' => array_slice($results, 0, 15, true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
