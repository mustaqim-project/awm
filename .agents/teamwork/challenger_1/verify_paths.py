import os
import re

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
views_dir = r"c:\laragon\www\cooca_core\resources\views"
report_path = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

with open(report_path, "r", encoding="utf-8") as f:
    text = f.read()

# Let's find all mentions of resources/views/...
full_refs = re.findall(r"resources/views/([a-zA-Z0-9_\-\./]+\.blade\.php)", text)
print(f"Total 'resources/views/...' references: {len(full_refs)}")

verified_full = []
unverified_full = []

for r in set(full_refs):
    p = os.path.join(views_dir, r.replace("/", os.sep))
    if os.path.exists(p):
        verified_full.append(r)
    else:
        unverified_full.append(r)

print(f"Verified full path references: {len(verified_full)}")
print(f"Unverified full path references: {len(unverified_full)}")
if unverified_full:
    print("Unverified full paths:")
    for u in sorted(unverified_full):
        print(" ", u)
