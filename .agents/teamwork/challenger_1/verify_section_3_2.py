import os
import re

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
report_path = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

with open(report_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

# Extract lines 240 to 300 (Section 3.2 Double Header list)
sec32_lines = lines[240:300]
items = []
for l in sec32_lines:
    m = re.search(r"^\d+\.\s+`([^`]+)`", l.strip())
    if m:
        path = m.group(1).replace("resources/views/app/", "")
        full = os.path.join(app_dir, path.replace("/", os.sep))
        exists = os.path.exists(full)
        items.append((path, exists))

print("=== Section 3.2 Double Header Manifest Verification ===")
fabricated_count = 0
for idx, (p, exists) in enumerate(items, 1):
    status = "EXISTS" if exists else "FABRICATED"
    if not exists:
        fabricated_count += 1
        print(f"[{status}] #{idx}: {p}")

print(f"\nTotal Section 3.2 items: {len(items)}")
print(f"Fabricated items: {fabricated_count}")
