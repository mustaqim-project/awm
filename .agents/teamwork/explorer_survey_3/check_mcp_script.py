import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

fp = r'c:\laragon\www\cooca_core\resources\views\app\settings\integrations\mcp.blade.php'
with open(fp, 'r', encoding='utf-8') as f:
    lines = f.readlines()

print(f"Total lines in mcp.blade.php: {len(lines)}")

script_start = -1
for i, l in enumerate(lines, 1):
    if '<script' in l:
        script_start = i
        print(f"Found <script> at Line {i}: {l.strip()[:80]}")

if script_start != -1:
    print(f"Script lines: {len(lines) - script_start} lines of JS at bottom!")
