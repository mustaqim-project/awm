import os
import re

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
views_dir = r"c:\laragon\www\cooca_core\resources\views"
report_path = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

with open(report_path, "r", encoding="utf-8") as f:
    text = f.read()

# Match code blocks with .blade.php like `pos/terminal.blade.php` or `products/index.blade.php`
code_mentions = re.findall(r"`([a-zA-Z0-9_\-\./]+\.blade\.php)`", text)
print(f"Total `*.blade.php` mentions in backticks: {len(code_mentions)}")

unique_code_mentions = sorted(list(set(code_mentions)))
print(f"Unique mentions in backticks: {len(unique_code_mentions)}")

existing_in_app = []
existing_in_views = []
recommended_or_missing = []

for m in unique_code_mentions:
    clean_m = m.replace("resources/views/", "").replace("app/", "")
    p_app = os.path.join(app_dir, clean_m.replace("/", os.sep))
    p_views = os.path.join(views_dir, m.replace("resources/views/", "").replace("/", os.sep))
    
    if os.path.exists(p_app):
        existing_in_app.append((m, clean_m, p_app))
    elif os.path.exists(p_views):
        existing_in_views.append((m, p_views))
    else:
        recommended_or_missing.append(m)

print(f"\nExisting views in app/: {len(existing_in_app)}")
print(f"Existing views in layouts/components: {len(existing_in_views)}")
print(f"Missing or proposed new files: {len(recommended_or_missing)}")

print("\n--- Missing or Proposed files ---")
for r in recommended_or_missing:
    print(" ", r)

print("\n--- Existing views in layouts/components ---")
for r in existing_in_views:
    print(" ", r[0])
