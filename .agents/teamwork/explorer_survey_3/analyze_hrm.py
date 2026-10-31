import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

fp = r'c:\laragon\www\cooca_core\resources\views\app\hrm\index.blade.php'
with open(fp, 'r', encoding='utf-8') as f:
    lines = f.readlines()

print(f"Total lines in hrm/index.blade.php: {len(lines)}")

# Modals
modals = []
for i, l in enumerate(lines, 1):
    if 'fixed inset-0' in l:
        modals.append((i, l.strip()[:100]))

print(f"Found {len(modals)} 'fixed inset-0' modals/dialogs:")
for m in modals:
    print(f"  Line {m[0]}: {m[1]}")

# Tables
tables = []
for i, l in enumerate(lines, 1):
    if '<table' in l:
        tables.append((i, l.strip()[:100]))
print(f"\nFound {len(tables)} tables:")
for t in tables:
    print(f"  Line {t[0]}: {t[1]}")
