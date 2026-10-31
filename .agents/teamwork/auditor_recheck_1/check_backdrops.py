import sys, re
sys.stdout.reconfigure(encoding='utf-8')

with open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8') as f:
    lines = f.readlines()

print("=== BACKDROP & COUNT CONSISTENCY CHECK ===")
for i, line in enumerate(lines):
    if re.search(r'\b(?:backdrop|flawed|47\s+locations|76\s+locations|47\s+instances)\b', line, re.I):
        print(f"Line {i+1}: {line.strip()}")
