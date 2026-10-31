import os
import sys
import re
import json

sys.path.insert(0, os.path.abspath('.'))
from scratch.deep_audit_v2 import ID_KEYWORDS, is_indonesian_text

target_files = [
    'resources/views/app/inventory/stocks.blade.php',
    'resources/views/app/marketplace/products.blade.php',
    'resources/views/app/inventory/transfers.blade.php',
    'resources/views/app/pos/terminal.blade.php',
    'resources/views/app/products/index.blade.php',
    'resources/views/app/marketplace/index.blade.php',
    'resources/views/app/marketplace/orders.blade.php',
    'resources/views/app/pos/shifts.blade.php',
    'resources/views/app/products/modifiers.blade.php',
    'resources/views/app/pos/orders.blade.php',
    'resources/views/app/pos/printers/index.blade.php',
    'resources/views/app/pos/reports/tabs/payments.blade.php',
    'resources/views/app/warehouse/index.blade.php',
    'resources/views/app/storefront/orders/index.blade.php',
    'resources/views/app/storefront/shipping/index.blade.php',
    'resources/views/app/pos/tables.blade.php',
    'resources/views/app/inventory/opname.blade.php',
    'resources/views/app/marketplace/chat.blade.php',
    'resources/views/app/marketplace/shipping_label.blade.php',
    'resources/views/app/quotations/index.blade.php',
    'resources/views/app/suppliers/index.blade.php',
    'resources/views/app/customers/index.blade.php',
    'resources/views/app/sales/returns/index.blade.php',
    'resources/views/app/sales-orders/index.blade.php',
    'resources/views/app/sales/returns/edit.blade.php',
    'resources/views/app/quotations/edit.blade.php',
    'resources/views/app/sales-orders/edit.blade.php',
    'resources/views/app/services/index.blade.php',
    'resources/views/app/sales/returns/show.blade.php',
    'resources/views/app/sales-orders/show.blade.php',
    'resources/views/app/quotations/show.blade.php',
    'resources/views/app/purchasing/returns/show.blade.php',
    'resources/views/app/sales/returns/create.blade.php',
    'resources/views/app/purchase-orders/print.blade.php',
    'resources/views/app/crm/vouchers.blade.php',
    'resources/views/app/sales-orders/create.blade.php',
    'resources/views/app/quotations/create.blade.php',
    'resources/views/app/purchase-orders/create.blade.php',
    'resources/views/app/invoices/index.blade.php'
]

TAG_RE = re.compile(r'<[^>]+>')
BLADE_RE = re.compile(r'\{\{.*?\}\}|\{!!.*?!!\}|@[a-zA-Z_]+(?:\([^)]*\))?')

results = {}
for f in target_files:
    if not os.path.exists(f):
        continue
    with open(f, 'r', encoding='utf-8') as fh:
        lines = fh.readlines()
    for idx, line in enumerate(lines, 1):
        stripped = line.strip()
        if stripped.startswith('//') or stripped.startswith('/*') or stripped.startswith('*') or stripped.startswith('<!--'):
            continue
        no_blade = BLADE_RE.sub(' ', line)
        text_chunks = TAG_RE.sub(' ', no_blade).strip()
        text_chunks = re.sub(r'\s+', ' ', text_chunks).strip()
        if not text_chunks or len(text_chunks) < 3:
            continue
        # Skip common JS/CSS syntax
        if re.match(r'^[a-zA-Z0-9_\$]+\s*:\s*[a-zA-Z0-9_\$\'\"\(\)\[\]\{\}\s,\.\+\-]+$', text_chunks):
            # Could be a JS property assignment like `x: 10, y: 20`
            # Check if values contain Indonesian words
            pass
        is_id, words = is_indonesian_text(text_chunks)
        if is_id and len(words) >= 1:
            # Filter out lines that already have localization or are code
            if '__(' in line or '@lang' in line:
                # check if the text is just surrounding text
                pass
            results.setdefault(f, []).append({
                'line': idx,
                'text': text_chunks,
                'words': words,
                'raw': line.strip()
            })

print(f"Total files with potential issues: {len(results)}")
for f, items in results.items():
    print(f"\n--- {f} ({len(items)} items) ---")
    for item in items[:10]: # show first 10
        print(f"  L{item['line']}: {item['text'][:80]} [words: {', '.join(item['words'])}]")
    if len(items) > 10:
        print(f"  ... and {len(items) - 10} more")

with open('.agents/teamwork/worker_app_m3/potential_unlocalized.json', 'w', encoding='utf-8') as out:
    json.dump(results, out, indent=2)
