import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

fp = r'c:\laragon\www\cooca_core\resources\views\app\settings\index.blade.php'
with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print(f"Total lines in settings/index.blade.php: {len(lines)}")

# Modals
modals = []
for i, l in enumerate(lines, 1):
    if 'fixed inset-0' in l:
        snippet = ""
        for j in range(max(0, i-5), min(len(lines), i+30)):
            if any(k in lines[j] for k in ['x-show=', '<h2', '<h3', 'aria-labelledby', 'role="dialog"']):
                snippet += f"      L{j+1}: {lines[j].strip()}\n"
        print(f"Modal at Line {i}:\n{snippet}")

# Tabs
tabs = []
for i, l in enumerate(lines, 1):
    m = re.search(r"@click=\"activeTab = '([^']+)'\"", l)
    if m:
        tabs.append((i, m.group(1), l.strip()[:80]))

print(f"\nFound {len(tabs)} Tabs in settings:")
for t in tabs:
    print(f"  Line {t[0]}: Tab '{t[1]}'")
