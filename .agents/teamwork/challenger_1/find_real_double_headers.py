import os
import re

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
double_headers = []

for root, dirs, files in os.walk(app_dir):
    for f in files:
        if f.endswith(".blade.php"):
            p = os.path.join(root, f)
            rel = os.path.relpath(p, app_dir).replace("\\", "/")
            with open(p, "r", encoding="utf-8", errors="ignore") as fp:
                c = fp.read()
            has_headertitle = bool(re.search(r"['\"]headerTitle['\"]\s*=>", c))
            has_module_header = bool(re.search(r"<x-module-header\b", c))
            if has_headertitle and has_module_header:
                double_headers.append(rel)

print(f"Total actual views with Double Header (headerTitle + <x-module-header>): {len(double_headers)}")
for d in sorted(double_headers):
    print(" ", d)
