import re

with open(r'c:\laragon\www\cooca_core\resources\views\app\pos\terminal.blade.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

current_modal = "Top"
modal_widths = []

for i, line in enumerate(lines):
    if "MODAL:" in line:
        current_modal = line.strip()
    if "max-w-" in line and ("rounded" in line or "dialog" in line or "relative" in line or "w-full" in line):
        classes = re.findall(r'max-w-[a-zA-Z0-9\[\]-]+', line)
        if classes:
            modal_widths.append((i+1, current_modal, classes, line.strip()[:100]))

for line_no, modal, classes, snippet in modal_widths[:30]:
    print(f"Line {line_no:4}: {modal[:45]:45} | Classes: {classes}")
