import re

file_path = r"c:\laragon\www\cooca_core\resources\views\app\pos\terminal.blade.php"
with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print(f"Total lines: {len(lines)}")

# Look around line 500-750 for main layout
for i in range(500, 750):
    l = lines[i].rstrip()
    if any(k in l for k in ['<main', 'pos-main', 'pos-body', 'class="flex-1', 'grid', 'col-span', 'cart', 'drawer', 'sidebar']):
        print(f"{i+1:4}: {l[:110]}")
