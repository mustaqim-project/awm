import os
import re

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"
target_modules = [
    'pos', 'sales', 'sales-orders', 'products', 'inventory', 
    'purchasing', 'purchase-orders', 'suppliers', 'invoices', 
    'quotations', 'warehouse', 'materials', 'storefront', 'marketplace', 'customers'
]

findings = []

for mod in target_modules:
    mod_path = os.path.join(base_dir, mod)
    if not os.path.exists(mod_path):
        continue
    for root, dirs, files in os.walk(mod_path):
        for f in files:
            if not f.endswith('.blade.php'):
                continue
            path = os.path.join(root, f)
            rel = os.path.relpath(path, base_dir)
            with open(path, 'r', encoding='utf-8', errors='ignore') as fp:
                lines = fp.readlines()
            
            for i, line in enumerate(lines):
                # Check for wrapper with flex and bg-black combined (flawed backdrop)
                if re.search(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b', line):
                    findings.append({
                        'file': rel,
                        'line': i + 1,
                        'type': 'FLAWED_BACKDROP_WRAPPER',
                        'snippet': line.strip()[:120]
                    })
                # Check for narrow modal
                m = re.search(r'class="[^"]*\b(max-w-(?:xs|sm|md|lg|xl|2xl|3xl))\b[^"]*"', line)
                if m and any(k in line for k in ['modal', 'rounded', 'dialog', 'w-full', 'bg-white', 'relative']):
                    # Check if it has larger responsive override like lg:max-w-5xl
                    has_large_override = bool(re.search(r'\b(?:lg|xl|2xl):max-w-(?:5xl|6xl|7xl|\[1350px\])\b', line))
                    if not has_large_override:
                        findings.append({
                            'file': rel,
                            'line': i + 1,
                            'type': 'NARROW_MODAL_WITHOUT_BENTO_OVERRIDE',
                            'class': m.group(1),
                            'snippet': line.strip()[:120]
                        })

print(f"Total findings: {len(findings)}")
by_file = {}
for f in findings:
    by_file.setdefault(f['file'], []).append(f)

for file, items in sorted(by_file.items(), key=lambda x: -len(x[1]))[:25]:
    types = {}
    for it in items:
        types[it['type']] = types.get(it['type'], 0) + 1
    print(f"{file:45}: {len(items)} issues {types}")
