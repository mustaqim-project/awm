import os
import glob
import re

print("=== FORENSIC VERIFICATION SUITE ===")

# 1. Double Header Verification
all_views = glob.glob('resources/views/app/**/*.blade.php', recursive=True)
all_views_rel = [os.path.relpath(f, 'resources/views/app').replace('\\', '/') for f in all_views]

dh_views_on_disk = []
for f, rel in zip(all_views, all_views_rel):
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        content = fp.read()
        if 'headerTitle' in content and '<x-module-header' in content:
            dh_views_on_disk.append(rel)

dh_views_on_disk.sort()
print(f"[Check 1] Total Double Header views found on disk: {len(dh_views_on_disk)}")

with open('docs/UI_UX_AUDIT_REPORT.md', 'r', encoding='utf-8') as f:
    report_text = f.read()

# Extract Section 3.2 manifest items
manifest_pattern = re.compile(r'^\d+\.\s+`([^`]+)`', re.MULTILINE)
manifest_matches = manifest_pattern.findall(report_text)
print(f"[Check 2] Total numbered backtick items in report: {len(manifest_matches)}")

# Filter to only the Section 3.2 items (which are 44 items under Section 3.2)
sec3_2_start = report_text.find("#### Complete Manifest of the 44 Affected Views")
sec3_2_end = report_text.find("#### Impact Assessment", sec3_2_start)
sec3_2_block = report_text[sec3_2_start:sec3_2_end]
sec3_2_items = manifest_pattern.findall(sec3_2_block)
print(f"[Check 3] Section 3.2 manifest items count: {len(sec3_2_items)}")

missing_from_disk = [m for m in sec3_2_items if m not in all_views_rel]
print(f"[Check 4] Manifest items missing from disk: {missing_from_disk} (Count: {len(missing_from_disk)})")

not_dh = [m for m in sec3_2_items if m not in dh_views_on_disk]
print(f"[Check 5] Manifest items lacking Double Header criteria: {not_dh} (Count: {len(not_dh)})")

on_disk_not_in_manifest = [d for d in dh_views_on_disk if d not in sec3_2_items]
print(f"[Check 6] Real Double Header views on disk omitted from manifest: {on_disk_not_in_manifest} (Count: {len(on_disk_not_in_manifest)})")

is_sec3_2_clean = (len(missing_from_disk) == 0 and len(not_dh) == 0 and len(on_disk_not_in_manifest) == 0 and len(sec3_2_items) == 44)
print(f"[VERDICT Section 3.2]: {'PASS (CLEAN)' if is_sec3_2_clean else 'FAIL (INTEGRITY VIOLATION)'}")
