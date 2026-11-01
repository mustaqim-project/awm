import os, glob, re

submodules = [
    'pos', 'products', 'storefront', 'warehouse', 'suppliers',
    'materials', 'invoices', 'inventory', 'marketplace', 'purchase-orders',
    'sales', 'sales-orders', 'customers', 'quotations', 'purchasing'
]

pattern = re.compile(r'\b(max-w-(?:xs|sm|md|lg))\b')

root = r'c:\laragon\www\cooca_core\resources\views\app'
by_submodule = {sm: [] for sm in submodules}

for sm in submodules:
    sm_dir = os.path.join(root, sm)
    if not os.path.exists(sm_dir):
        continue
    for f in glob.glob(os.path.join(sm_dir, '**', '*.blade.php'), recursive=True):
        lines = open(f, encoding='utf-8', errors='ignore').readlines()
        for idx, line in enumerate(lines):
            # check if line contains max-w-sm/md/lg and has modal-like classes (rounded, bg-white, fixed, relative, etc)
            if pattern.search(line) and ('rounded' in line or 'bg-' in line or 'w-full' in line or 'panel' in line):
                # check context lines (+- 5) for teleport or modal or dialog or x-show
                ctx = "".join(lines[max(0, idx-10):min(len(lines), idx+10)]).lower()
                if 'modal' in ctx or 'teleport' in ctx or 'dialog' in ctx or 'backdrop' in ctx or 'x-show' in ctx:
                    by_submodule[sm].append((f, idx + 1, line.strip()))

total = sum(len(v) for v in by_submodule.values())
print(f"Total modal panel containers found across 15 submodules: {total}")
for sm, lst in by_submodule.items():
    print(f"  {sm}: {len(lst)}")
