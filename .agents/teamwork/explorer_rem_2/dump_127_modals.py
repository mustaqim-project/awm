import os
import re
import json

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"
target_modules = [
    'pos', 'products', 'storefront', 'warehouse', 'suppliers',
    'materials', 'invoices', 'inventory', 'marketplace', 'purchase-orders',
    'sales', 'sales-orders', 'customers', 'quotations', 'purchasing'
]

modals = []

for mod in target_modules:
    mod_path = os.path.join(base_dir, mod)
    if not os.path.exists(mod_path):
        continue
    for root, dirs, files in os.walk(mod_path):
        for f in files:
            if not f.endswith('.blade.php'):
                continue
            path = os.path.join(root, f)
            rel = os.path.relpath(path, base_dir).replace('\\', '/')
            with open(path, 'r', encoding='utf-8', errors='ignore') as fp:
                lines = fp.readlines()
            
            for i, line in enumerate(lines):
                # Search for max-w-(xs|sm|md|lg|xl|2xl|3xl)
                m = re.search(r'class="[^"]*\b(max-w-(?:xs|sm|md|lg|xl|2xl|3xl))\b[^"]*"', line)
                if m and any(k in line for k in ['modal', 'rounded', 'dialog', 'w-full', 'bg-white', 'relative', 'pos-modal-panel']):
                    # Ensure it does not already have an XXL override
                    if not re.search(r'\b(?:lg|xl|2xl):max-w-(?:4xl|5xl|6xl|7xl|\[1350px\])\b', line):
                        # check if it's within a modal dialog context
                        ctx = "".join(lines[max(0, i-6):min(len(lines), i+6)])
                        if any(w in ctx.lower() for w in ['modal', 'dialog', 'fixed inset-0', 'x-show', 'z-50', 'z-[200]', 'role="dialog"']):
                            modals.append({
                                'module': mod,
                                'file': rel,
                                'line': i + 1,
                                'current_width': m.group(1),
                                'class_snippet': m.group(0),
                                'line_content': line.strip()[:140]
                            })

print(f"Total extracted cramped modals: {len(modals)}")
with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_rem_2\cramped_modals_127.json', 'w', encoding='utf-8') as out:
    json.dump(modals, out, indent=2)

by_mod = {}
for m in modals:
    by_mod[m['module']] = by_mod.get(m['module'], 0) + 1
for k, v in sorted(by_mod.items(), key=lambda x: -x[1]):
    print(f"  {k:15}: {v}")
