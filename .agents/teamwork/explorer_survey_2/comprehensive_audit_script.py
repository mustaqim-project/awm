import os
import re
import json

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"

target_modules = {
    'pos': [
        'pos/terminal.blade.php', 'pos/orders.blade.php', 'pos/kitchen.blade.php',
        'pos/tables.blade.php', 'pos/shifts.blade.php', 'pos/printers/index.blade.php',
        'pos/receipt.blade.php', 'pos/prep_sheet.blade.php', 'pos/qr-card.blade.php',
        'pos/reports.blade.php', 'pos/reports/partials/order_detail_modal.blade.php',
        'pos/reports/partials/filter_bar.blade.php', 'pos/reports/partials/kpi_cards.blade.php',
        'pos/reports/print_summary.blade.php'
    ],
    'sales_orders': [
        'sales-orders/create.blade.php', 'sales-orders/edit.blade.php',
        'sales-orders/index.blade.php', 'sales-orders/show.blade.php'
    ],
    'sales_returns': [
        'sales/returns/create.blade.php', 'sales/returns/edit.blade.php',
        'sales/returns/index.blade.php', 'sales/returns/print.blade.php',
        'sales/returns/show.blade.php'
    ],
    'invoices': [
        'invoices/create.blade.php', 'invoices/edit.blade.php',
        'invoices/index.blade.php', 'invoices/print.blade.php',
        'invoices/show.blade.php'
    ],
    'quotations': [
        'quotations/create.blade.php', 'quotations/edit.blade.php',
        'quotations/index.blade.php', 'quotations/show.blade.php'
    ],
    'products': [
        'products/index.blade.php', 'products/modifiers.blade.php', 'products/bom.blade.php'
    ],
    'inventory': [
        'inventory/stocks.blade.php', 'inventory/opname.blade.php',
        'inventory/movements.blade.php', 'inventory/transfers.blade.php'
    ],
    'purchasing': [
        'purchasing/bills/index.blade.php', 'purchasing/bills/show.blade.php',
        'purchasing/receipts/create.blade.php', 'purchasing/returns/create.blade.php',
        'purchasing/returns/index.blade.php', 'purchasing/returns/show.blade.php'
    ],
    'purchase_orders': [
        'purchase-orders/create.blade.php', 'purchase-orders/index.blade.php',
        'purchase-orders/print.blade.php', 'purchase-orders/show.blade.php'
    ],
    'warehouse_materials_suppliers': [
        'warehouse/index.blade.php', 'warehouse/show.blade.php',
        'materials/index.blade.php', 'suppliers/index.blade.php'
    ],
    'storefront': [
        'storefront/orders/index.blade.php', 'storefront/orders/show.blade.php',
        'storefront/orders/shipping_label.blade.php', 'storefront/reservations/index.blade.php',
        'storefront/settings.blade.php', 'storefront/shipping/index.blade.php',
        'storefront/partials/navigation.blade.php'
    ],
    'marketplace': [
        'marketplace/index.blade.php', 'marketplace/orders.blade.php',
        'marketplace/products.blade.php', 'marketplace/shipping_label.blade.php',
        'marketplace/chat.blade.php', 'marketplace/logs.blade.php'
    ],
    'customers': [
        'customers/index.blade.php'
    ]
}

findings = []

for mod_name, files in target_modules.items():
    for rel_f in files:
        full_path = os.path.join(base_dir, rel_f.replace('/', os.sep))
        if not os.path.exists(full_path):
            continue
        
        with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
            lines = f.readlines()
            content = "".join(lines)
        
        # 1. Check for small tap targets in buttons / inputs
        for i, l in enumerate(lines):
            # Button with small height
            if '<button' in l or '<a ' in l:
                small_h = re.findall(r'\b(h-6|h-7|h-8|py-0\.5|py-1)\b(?![^>]*sm:min-h-)', l)
                # check if there is min-h-[44px]
                if small_h and not re.search(r'\bmin-h-\[(?:44|48)px\]\b', l):
                    findings.append({
                        'module': mod_name,
                        'file': rel_f,
                        'line': i + 1,
                        'category': 'TOUCH_TARGET_TOO_SMALL',
                        'severity': 'MEDIUM',
                        'snippet': l.strip()[:140],
                        'issue': f"Button/link with small height class ({', '.join(small_h)}) below the 44px Apple HIG touch target recommendation."
                    })
            
            # Input with text-xs
            if '<input' in l or '<select' in l:
                if re.search(r'\btext-xs\b', l) and not re.search(r'\b(?:sm:text-sm|text-\[16px\]|sm:text-\[13px\])\b', l):
                    findings.append({
                        'module': mod_name,
                        'file': rel_f,
                        'line': i + 1,
                        'category': 'INPUT_SMALL_FONT_IOS_ZOOM',
                        'severity': 'HIGH',
                        'snippet': l.strip()[:140],
                        'issue': "Form input uses 'text-xs' without 16px mobile base, causing iOS Safari to automatically zoom in on focus and break viewport layout."
                    })
            
            # Modal width check
            if any(k in l for k in ['x-show="show', 'id="modal', 'class="fixed inset-0']):
                # Check next 5 lines for max-w
                snippet = "".join(lines[i:min(len(lines), i+6)])
                mw = re.findall(r'\b(max-w-(?:xs|sm|md|lg|xl|2xl|3xl))\b', snippet)
                if mw and not re.search(r'\b(?:lg|xl|2xl):max-w-(?:5xl|6xl|7xl|\[1350px\])\b', snippet):
                    findings.append({
                        'module': mod_name,
                        'file': rel_f,
                        'line': i + 1,
                        'category': 'NARROW_FORM_MODAL',
                        'severity': 'HIGH',
                        'snippet': l.strip()[:140],
                        'issue': f"Modal form container restricted to narrow canvas '{mw[0]}' instead of Apple HIG Bento XXL canvas (lg:max-w-5xl/6xl) or adaptive mobile bottom sheet."
                    })
            
            # Flawed backdrop wrapper
            if re.search(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b', l):
                findings.append({
                    'module': mod_name,
                    'file': rel_f,
                    'line': i + 1,
                    'category': 'FLAWED_BACKDROP_WRAPPER',
                    'severity': 'HIGH',
                    'snippet': l.strip()[:140],
                    'issue': "Backdrop dark background placed directly on flex wrapper, leaking topbar and causing header clipping ($y=0$ gap)."
                })
            
            # Bloated desktop full-width button
            if re.search(r'\bbtn\b[^"]*\bw-full\b', l) or re.search(r'<button[^>]*class="[^"]*\bw-full\b(?![^"]*\bsm:w-auto\b)[^"]*"', l):
                if any(k in l for k in ['Simpan', 'Tambah', 'Kirim', 'Submit', 'Bayar', 'Proses', 'Buat']) and 'sm:w-auto' not in l:
                    findings.append({
                        'module': mod_name,
                        'file': rel_f,
                        'line': i + 1,
                        'category': 'BLOATED_FULL_WIDTH_BUTTON',
                        'severity': 'MEDIUM',
                        'snippet': l.strip()[:140],
                        'issue': "Primary action button uses unconditional 'w-full' stretching across the entire desktop screen width instead of 'w-full sm:w-auto'."
                    })

print(f"Total findings recorded: {len(findings)}")

with open(r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\detailed_findings.json", 'w', encoding='utf-8') as f:
    json.dump(findings, f, indent=2)

# Summarize by module and category
summary = {}
for item in findings:
    m = item['module']
    cat = item['category']
    summary.setdefault(m, {}).setdefault(cat, 0)
    summary[m][cat] += 1

print("\n=== SUMMARY BY MODULE ===")
for m, cats in sorted(summary.items()):
    total_m = sum(cats.values())
    print(f"\n[{m.upper()}] - {total_m} issues:")
    for c, cnt in sorted(cats.items(), key=lambda x: -x[1]):
        print(f"  - {c}: {cnt}")
