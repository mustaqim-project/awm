import os
import glob
import re

base = r'c:\laragon\www\cooca_core\resources\views\app'
target_modules = [
    'pos', 'products', 'marketplace', 'storefront', 'warehouse',
    'invoices', 'sales', 'inventory', 'purchase-orders', 'sales-orders',
    'customers', 'suppliers', 'quotations', 'purchasing', 'materials'
]

counts = {}
details = {}
for mod in target_modules:
    files = glob.glob(os.path.join(base, mod, '**', '*.blade.php'), recursive=True)
    counts[mod] = 0
    details[mod] = []
    for f in files:
        rel = os.path.relpath(f, base).replace('\\', '/')
        with open(f, encoding='utf-8', errors='ignore') as fp:
            for idx, line in enumerate(fp, 1):
                m = re.search(r'class="[^"]*\b(max-w-(?:xs|sm|md|lg|xl|2xl|3xl))\b[^"]*"', line)
                if m:
                    if not re.search(r'\b(?:lg|xl|2xl):max-w-(?:4xl|5xl|6xl|7xl)\b', line):
                        counts[mod] += 1
                        details[mod].append((rel, idx, m.group(1), line.strip()[:80]))

print(counts)
print('Total across 15 submodules:', sum(counts.values()))
