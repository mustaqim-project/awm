import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

fp = r'c:\laragon\www\cooca_core\resources\views\app\hrm\index.blade.php'
with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i, l in enumerate(lines, 1):
    if 'class="fixed inset-0 z-[200] overflow-y-auto"' in l:
        # scan next 30 lines for title or h3 or x-show
        snippet = ""
        for j in range(max(0, i-5), min(len(lines), i+30)):
            if any(k in lines[j] for k in ['x-show=', '<h2', '<h3', 'aria-labelledby', 'role="dialog"']):
                snippet += f"      L{j+1}: {lines[j].strip()}\n"
        print(f"Modal at Line {i}:\n{snippet}")
