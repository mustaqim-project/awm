import os, glob, re

root = r'c:\laragon\www\cooca_core\resources\views\app'

submodules = [
    'pos', 'products', 'storefront', 'warehouse', 'suppliers',
    'materials', 'invoices', 'inventory', 'marketplace', 'purchase-orders',
    'sales', 'sales-orders', 'customers', 'quotations', 'purchasing'
]

results = []

for sm in submodules:
    sm_dir = os.path.join(root, sm)
    if not os.path.exists(sm_dir):
        continue
    for f in glob.glob(os.path.join(sm_dir, '**', '*.blade.php'), recursive=True):
        lines = open(f, encoding='utf-8', errors='ignore').readlines()
        rel = os.path.relpath(f, root)
        for idx, line in enumerate(lines):
            # Find elements with max-w-xs, max-w-sm, max-w-md, max-w-lg in files
            m = re.search(r'\b(max-w-(?:xs|sm|md|lg))\b', line)
            if m:
                # Check if it has modal panel characteristics (rounded, bg-, shadow, fixed, etc.)
                # or is preceded by teleport/x-show/dialog
                ctx = "".join(lines[max(0, idx-8):min(len(lines), idx+8)])
                if any(k in ctx.lower() for k in ['teleport', 'modal', 'dialog', 'backdrop', 'x-show', 'fixed inset-0']):
                    results.append((rel, idx + 1, m.group(1), line.strip()))

print(f"Total matching modal panels: {len(results)}")
by_file = {}
for rel, lno, mw, line in results:
    by_file.setdefault(rel, []).append((lno, mw, line))

for rel, items in sorted(by_file.items()):
    print(f"\n{rel} ({len(items)} modals):")
    for lno, mw, line in items[:5]:
        print(f"  [{lno}] {mw} -> {line[:90]}")
    if len(items) > 5:
        print(f"  ... and {len(items)-5} more")
