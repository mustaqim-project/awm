import os
import re

for fname in ['materials/index.blade.php', 'suppliers/index.blade.php']:
    p = os.path.join(r'c:\laragon\www\cooca_core\resources\views\app', fname.replace('/', os.sep))
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    print(f"\n=== {fname} ({len(lines)} lines) ===")
    for i, l in enumerate(lines):
        if any(k in l for k in ['x-show="', 'max-w-']) and any(k in l.lower() for k in ['modal', 'dialog', 'form', 'rounded']):
            print(f"  Line {i+1}: {l.strip()[:100]}")
