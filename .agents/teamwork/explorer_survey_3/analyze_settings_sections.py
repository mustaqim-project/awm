import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

fp = r'c:\laragon\www\cooca_core\resources\views\app\settings\index.blade.php'
with open(fp, 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(1080, len(lines)):
    l = lines[i]
    if 'x-show="activeTab ===' in l or '<!-- ===' in l or '<section' in l or '<form' in l:
        print(f"{i+1}: {l.strip()[:100]}")
