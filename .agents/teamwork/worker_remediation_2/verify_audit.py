import os
import glob
import re

base = r'resources/views/app'

# 1. Check all blade files and real double header views
fl = glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True)
real_dh = [os.path.relpath(f, base).replace('\\', '/') for f in fl if 'headerTitle' in open(f, encoding='utf-8', errors='ignore').read() and '<x-module-header' in open(f, encoding='utf-8', errors='ignore').read()]
real_dh.sort()
print(f"Total actual Double Header views on disk: {len(real_dh)}")

# 2. Check current manifest in UI_UX_AUDIT_REPORT.md
report_content = open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8').read()
section = report_content.split('#### Complete Manifest of the 44 Affected Views')[1].split('#### Impact Assessment')[0]
manifest_paths = re.findall(r'`([^`]+)`', section)
print(f"Total manifest paths: {len(manifest_paths)}")

missing_on_disk = [p for p in manifest_paths if not os.path.exists(os.path.join(base, p))]
print(f"Missing on disk ({len(missing_on_disk)}): {missing_on_disk}")

not_in_real_dh = [p for p in manifest_paths if p not in real_dh]
print(f"In manifest but NOT in real double header list ({len(not_in_real_dh)}): {not_in_real_dh}")

in_real_dh_not_manifest = [p for p in real_dh if p not in manifest_paths]
print(f"In real double header list but NOT in manifest ({len(in_real_dh_not_manifest)}): {in_real_dh_not_manifest}")

# 3. Check flawed backdrops across resources/views/app
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
matches = [f for f in fl if pattern.search(open(f, encoding='utf-8', errors='ignore').read())]
total_instances = sum(len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read())) for f in matches)
print(f"\nFlawed Backdrop Views ({len(matches)}): Total Instances: {total_instances}")
for f in sorted(matches):
    rel = os.path.relpath(f, base).replace('\\', '/')
    cnt = len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read()))
    print(f"  - {rel}: {cnt}")
