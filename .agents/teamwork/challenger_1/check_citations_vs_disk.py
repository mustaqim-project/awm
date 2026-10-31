import os
import re

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
report_path = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

# 1. Enumerate all blade files on disk
disk_files = []
for root, dirs, files in os.walk(app_dir):
    for f in files:
        full_path = os.path.join(root, f)
        rel_path = os.path.relpath(full_path, app_dir).replace("\\", "/")
        disk_files.append(rel_path)

disk_files.sort()
print(f"Total files on disk in resources/views/app: {len(disk_files)}")

# 2. Check for non-.blade.php files
non_blade = [f for f in disk_files if not f.endswith(".blade.php")]
print(f"Non-blade files on disk: {len(non_blade)}")
if non_blade:
    print("Non-blade files:", non_blade)

# 3. Read report and extract all file mentions
with open(report_path, "r", encoding="utf-8") as f:
    report_content = f.read()

# Pattern matching file paths like resources/views/app/xyz.blade.php or app/xyz.blade.php or xyz.blade.php
matches = re.findall(r"(?:resources/views/app/)?([a-zA-Z0-9_\-\./]+\.blade\.php)", report_content)
unique_report_files = sorted(list(set(matches)))

print(f"Total unique .blade.php filenames cited in report: {len(unique_report_files)}")

# Check if any cited app view does not exist on disk
missing_from_disk = []
cited_in_app = []
cited_outside_app = []

for rf in unique_report_files:
    if rf in disk_files:
        cited_in_app.append(rf)
    elif os.path.exists(os.path.join(r"c:\laragon\www\cooca_core\resources\views", rf)):
        cited_outside_app.append(rf)
    elif os.path.exists(os.path.join(r"c:\laragon\www\cooca_core\resources\views\app", rf)):
        cited_in_app.append(rf)
    else:
        # Check if it has a prefix like layouts/ or components/
        full_try = os.path.join(r"c:\laragon\www\cooca_core\resources\views", rf)
        if os.path.exists(full_try):
            cited_outside_app.append(rf)
        else:
            missing_from_disk.append(rf)

print(f"Cited files verified in resources/views/app: {len(cited_in_app)}")
print(f"Cited files verified in resources/views (e.g. layouts/components): {len(cited_outside_app)}")
print(f"Cited files missing from disk: {len(missing_from_disk)}")
if missing_from_disk:
    print("Missing files:", missing_from_disk)
