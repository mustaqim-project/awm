import os
import re
import json

missing_keys = [
    'auth.resend_otp',
    'billing.cooca_security_guarantee_desc',
    'billing.step_choose_package',
    'billing.step_choose_package_subtitle',
    'common.back_to_list',
    'common.business',
    'common.completed',
    'common.customer',
    'common.master_data',
    'common.no_notifications',
    'common.optional',
    'common.other',
    'common.rejected',
    'common.settings',
    'common.transactions',
    'feedback.status_',
    'finance.categories.',
    'finance.col_account_code',
    'finance.col_account_name',
    'finance.inflow_categories.',
    'finance.kpi_duplicate_count',
    'finance.kpi_error_count',
    'finance.kpi_ready_import_count',
    'finance.petty_cash_label',
    'hrm.label_location',
    'hrm.tooltip_delete_schedule',
    'hrm.tooltip_delete_shift',
    'inventory.movement_types.',
    'pos.terminal',
    'pos.touchscreen_cashier',
    'products.category',
    'products.title',
    'products.unit',
    'purchasing.orders.order_date',
    'purchasing.statuses.',
    'sales.invoices.confirm_delete_draft_desc',
    'sales.returns.comp_store_credit',
    'services.manage_services',
    'social_media.format_',
    'storefront.open_store',
    'storefront.reservations.action_complete',
    'storefront.reservations.action_confirm',
    'storefront.reservations.action_seat',
    'storefront.reservations.btn_cancel_rsv',
    'storefront.reservations.btn_change_table',
    'storefront.reservations.btn_open_pos',
    'storefront.reservations.btn_select_table',
    'storefront.reservations.guest_count_mobile',
    'storefront.reservations.modal_assign_btn_cancel',
    'storefront.reservations.modal_assign_btn_save',
    'storefront.reservations.modal_assign_choose_placeholder',
    'storefront.reservations.modal_assign_rsv_label',
    'storefront.reservations.modal_assign_select_table',
    'storefront.reservations.modal_assign_table_option',
    'storefront.reservations.modal_assign_title',
    'storefront.reservations.modal_cancel_btn_close',
    'storefront.reservations.modal_cancel_btn_confirm',
    'storefront.reservations.modal_cancel_desc',
    'storefront.reservations.modal_cancel_peace_of_mind',
    'storefront.reservations.modal_cancel_title',
    'storefront.reservations.status_cancelled',
    'storefront.reservations.status_completed',
    'storefront.reservations.status_confirmed',
    'storefront.reservations.status_no_show',
    'storefront.reservations.status_pending',
    'storefront.reservations.status_seated',
    'storefront.reservations.table_label_short'
]

# Check each in its file to see the exact code line
pattern = re.compile(r"""(?:__\s*\(\s*['"]([^'"]+)['"]|@lang\s*\(\s*['"]([^'"]+)['"])""")
views = ['resources/views/app', 'resources/views/admin']

results = []

for k in missing_keys:
    is_prefix = k.endswith('.') or k.endswith('_')
    found_lines = []
    for d in views:
        for root, _, files in os.walk(d):
            for f in files:
                if f.endswith('.blade.php'):
                    fp = os.path.join(root, f)
                    with open(fp, 'r', encoding='utf-8', errors='ignore') as s:
                        lines = s.readlines()
                    for idx, line in enumerate(lines):
                        if f"'{k}'" in line or f'"{k}"' in line:
                            found_lines.append({
                                'file': fp,
                                'line_no': idx + 1,
                                'line_text': line.strip()
                            })
    results.append({
        'key': k,
        'is_prefix': is_prefix,
        'occurrences': found_lines
    })

print(f"Total keys analyzed: {len(results)}")
static_keys = [r for r in results if not r['is_prefix']]
prefix_keys = [r for r in results if r['is_prefix']]

print(f"Definite Static Literal Missing Keys: {len(static_keys)}")
print(f"Dynamic Prefix Concatenations: {len(prefix_keys)}")

print("\n--- SAMPLE STATIC MISSING KEYS ---")
for s in static_keys[:25]:
    occ = s['occurrences'][0] if s['occurrences'] else {'file': 'none', 'line_no': 0, 'line_text': ''}
    print(f"Key: {s['key']}")
    print(f"   At: {occ['file']}:{occ['line_no']}")
    print(f"   Snippet: {occ['line_text'][:100]}")
