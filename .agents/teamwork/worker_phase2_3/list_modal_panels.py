import os, glob, re

root = r'c:\laragon\www\cooca_core\resources\views\app'
submodules = [
    'pos', 'products', 'storefront', 'warehouse', 'suppliers',
    'materials', 'invoices', 'inventory', 'marketplace', 'purchase-orders',
    'sales', 'sales-orders', 'customers', 'quotations', 'purchasing'
]

modal_panels = []

for sm in submodules:
    sm_dir = os.path.join(root, sm)
    if not os.path.exists(sm_dir):
        continue
    for f in glob.glob(os.path.join(sm_dir, '**', '*.blade.php'), recursive=True):
        lines = open(f, encoding='utf-8', errors='ignore').readlines()
        rel = os.path.relpath(f, root)
        for idx, line in enumerate(lines):
            # Find classes that set narrow max-w on div containers
            if re.search(r'<div[^>]*\b(max-w-(?:xs|sm|md|lg))\b', line):
                ctx = "".join(lines[max(0, idx-12):min(len(lines), idx+12)])
                # check if this is an actual modal or dialog container (has x-show, teleport, role="dialog", backdrop, or is inside a modal wrapper)
                is_modal = any(k in ctx.lower() for k in [
                    'role="dialog"', 'role=\'dialog\'', 'role="alertdialog"',
                    'x-teleport', 'backdrop', 'pos-modal-panel', 'fixed inset-0',
                    '@click.away', '@click.self', 'modal'
                ])
                if is_modal:
                    modal_panels.append((rel, idx + 1, line.strip()))

print(f"Found {len(modal_panels)} form modal panels across the 15 submodules:")
by_sm = {}
for rel, lno, line in modal_panels:
    sm = rel.split(os.sep)[0]
    by_sm.setdefault(sm, []).append((rel, lno, line))

for sm, items in sorted(by_sm.items()):
    print(f"Submodule: {sm} ({len(items)} modal panels)")
    for r, l, line in items:
        print(f"  {r}:{l} -> {line[:80]}")
