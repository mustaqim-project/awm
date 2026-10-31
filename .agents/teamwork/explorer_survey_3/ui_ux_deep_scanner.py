import os, re, json

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

target_modules = {
    'dashboards_analytics': [
        'dashboard.blade.php',
        'calculator.blade.php',
        'analytics/index.blade.php',
        'reports/index.blade.php',
        'profitability/index.blade.php',
        'simulator/index.blade.php',
    ],
    'finance_accounting': [
        'finance/expenses.blade.php',
        'finance/journals.blade.php',
        'finance/payables.blade.php',
        'finance/receivables.blade.php',
        'finance/accounting/balance-sheet.blade.php',
        'finance/accounting/coa.blade.php',
        'finance/accounting/general-ledger.blade.php',
        'finance/accounting/reconciliation.blade.php',
        'finance/accounting/trial-balance.blade.php',
        'finance/cash-bank/index.blade.php',
        'finance/cash-bank/ledger.blade.php',
        'finance/edc-terminals/index.blade.php',
        'finance/external-reconciliation/index.blade.php',
        'finance/payout-accounts/index.blade.php',
        'finance/settlements/index.blade.php',
        'finance/settlements/show.blade.php',
        'invoices/index.blade.php',
        'invoices/create.blade.php',
        'invoices/edit.blade.php',
        'invoices/show.blade.php',
        'invoices/print.blade.php',
        'tax/index.blade.php',
        'billing/checkout.blade.php',
        'billing/history.blade.php',
        'billing/invoice.blade.php',
        'billing/limits.blade.php',
        'billing/payment.blade.php',
    ],
    'crm_customers': [
        'customers/index.blade.php',
        'crm/members.blade.php',
        'crm/vouchers.blade.php',
        'feedback/index.blade.php',
        'feedback/create.blade.php',
        'feedback/show.blade.php',
        'community/index.blade.php',
        'community/events.blade.php',
        'community/event_payment.blade.php',
    ],
    'hrm_workforce': [
        'hrm/index.blade.php',
        'hrm/payroll/create.blade.php',
        'hrm/payroll/payslip.blade.php',
        'hrm/payroll/show.blade.php',
        'labor-machines/index.blade.php',
    ],
    'settings_governance': [
        'settings/index.blade.php',
        'settings/roles.blade.php',
        'settings/integrations/mcp.blade.php',
        'settings/integrations/social_media.blade.php',
        'settings/integrations/whatsapp.blade.php',
        'profile/edit.blade.php',
        'profile/verify-contact.blade.php',
        'roles/index.blade.php',
        'security/audit-logs/index.blade.php',
        'security/audit-logs/show.blade.php',
        'approvals/history.blade.php',
        'approvals/inbox.blade.php',
        'approvals/rules.blade.php',
        'master-data/index.blade.php',
    ]
}

# Regex patterns for UI/UX audit
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]|[\u2600-\u27bf]|[\u2300-\u23ff]|[\u2b50-\u2b55]|[\u203c-\u2049]')
# Common emojis
specific_emojis = re.compile(r'[🚀✨💡👥🏆🎟️📦⚡🔥🟢📈💬🏢📊💰🛍️📦👥⚙️🔍❌✅⚠️📋📥🖨️🎯🏷️💳🛒🧾🔒🔑]')

results = {}

for cat_name, rel_paths in target_modules.items():
    cat_results = []
    for rp in rel_paths:
        full_path = os.path.join(views_dir, rp.replace('/', os.sep))
        with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            lines = content.splitlines()

        file_stat = {
            'file': rp,
            'lines': len(lines),
            'emojis': [],
            'bento_styling': {
                'rounded_bento': len(re.findall(r'rounded-\[(?:20|24|28)px\]', content)),
                'legacy_rounded': len(re.findall(r'\brounded-(?:lg|xl|2xl|3xl)\b', content)),
                'hairline_border': len(re.findall(r'border-black/\[0\.06\]|border-white/\[0\.08\]', content)),
                'legacy_border': len(re.findall(r'\bborder-(?:gray|slate|zinc)-(?:200|300)\b', content)),
                'bento_canvas': len(re.findall(r'bg-\[#F2F2F7\]|bg-\[#1C1C1E\]|bg-\[#000000\]', content)),
                'primary_apple_blue': len(re.findall(r'bg-\[#007AFF\]|text-\[#007AFF\]', content)),
                'tailwind_blue_indigo': len(re.findall(r'\b(?:bg|text)-(?:blue|indigo)-(?:600|700)\b', content)),
            },
            'metric_cards': {
                'tabular_nums': len(re.findall(r'\btabular-nums\b', content)),
                'pulse_dots': len(re.findall(r'\banimate-pulse\b', content)),
                'kpi_headers': len(re.findall(r'\btext-3xl\b|\btext-2xl\b', content)),
            },
            'modals': {
                'has_modal': bool(re.search(r'x-modal|x-show="[^"]*modal|role="dialog"|fixed inset-0', content, re.IGNORECASE)),
                'backdrop_blur': len(re.findall(r'backdrop-blur', content)),
                'modal_count': len(re.findall(r'role="dialog"|x-show="[^"]*modal', content, re.IGNORECASE)),
            },
            'empty_states': {
                'has_empty_directive': bool(re.search(r'@empty', content)),
                'empty_state_keywords': len(re.findall(r'belum ada|tidak ada|no data|kosong|empty state', content, re.IGNORECASE)),
            },
            'error_handling': {
                'has_errors_any': bool(re.search(r'\$errors->any\(\)', content)),
                'has_error_directive': bool(re.search(r'@error\(', content)),
                'has_flash_alerts': bool(re.search(r"session\('(?:success|error|status|warning)'\)", content)),
            },
            'multi_industry': {
                'has_industry_gating': bool(re.search(r'isWorkshop|isLaundry|isPharmacy|isFoodIndustry|hasDineInFeature|isModuleEnabled', content)),
                'gating_matches': re.findall(r'(?:isWorkshop|isLaundry|isPharmacy|isFoodIndustry|hasDineInFeature|isModuleEnabled)\b', content),
            },
            'responsive_tables': {
                'table_count': len(re.findall(r'<table\b', content)),
                'overflow_x_auto': len(re.findall(r'overflow-x-auto', content)),
            }
        }

        # Check line by line for specific issues
        for idx, line in enumerate(lines, 1):
            # Emojis in UI text or buttons
            emoji_matches = specific_emojis.findall(line)
            if emoji_matches:
                file_stat['emojis'].append({'line': idx, 'emojis': list(set(emoji_matches)), 'snippet': line.strip()[:100]})

        cat_results.append(file_stat)
    results[cat_name] = cat_results

with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\ui_ux_scan_raw.json', 'w', encoding='utf-8') as f:
    json.dump(results, f, indent=2)

print('Scan completed and saved to ui_ux_scan_raw.json')
