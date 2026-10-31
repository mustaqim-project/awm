import os
import re
import glob

base = r'resources/views/app'
report_path = r'docs/UI_UX_AUDIT_REPORT.md'

print("=== 1. VERIFY SECTION 3.2 MANIFEST ===")
report = open(report_path, encoding='utf-8').read()
sec32_match = re.search(r'#### Complete Manifest of the 44 Affected Views\s*\n(.*?)(?=\n#### Impact Assessment)', report, re.DOTALL)
if not sec32_match:
    print("FAILED to find Section 3.2 manifest!")
else:
    manifest_lines = [l.strip() for l in sec32_match.group(1).splitlines() if l.strip()]
    manifest_paths = [re.search(r'`([^`]+)`', l).group(1) for l in manifest_lines if '`' in l]
    print(f"Total manifest entries: {len(manifest_paths)}")
    missing = [p for p in manifest_paths if not os.path.exists(os.path.join(base, p))]
    print(f"Missing from disk: {len(missing)} -> {missing}")
    
    # Check if each has headerTitle and <x-module-header
    not_dh = []
    for p in manifest_paths:
        c = open(os.path.join(base, p), encoding='utf-8', errors='ignore').read()
        if not ('headerTitle' in c and '<x-module-header' in c):
            not_dh.append(p)
    print(f"Entries not having double header: {len(not_dh)} -> {not_dh}")

print("\n=== 2. VERIFY COMMAND 1, 2, 3 EXECUTION ===")
# Command 1:
fl = glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True)
submods = set(os.path.relpath(f, base).split(os.sep)[0] for f in fl if os.sep in os.path.relpath(f, base))
print(f"Command 1: Total Blade Views: {len(fl)} | Total Submodules: {len(submods) + 1}")

# Command 2:
dh = [f for f in fl if 'headerTitle' in open(f, encoding='utf-8', errors='ignore').read() and '<x-module-header' in open(f, encoding='utf-8', errors='ignore').read()]
print(f"Command 2: Double Header Views: {len(dh)}")

# Command 3:
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
matches = [f for f in fl if pattern.search(open(f, encoding='utf-8', errors='ignore').read())]
total_instances = sum(len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read())) for f in matches)
print(f"Command 3: Affected Views: {len(matches)} | Total Flawed Backdrop Instances: {total_instances}")

print("\n=== 3. CHECK SECTION 4 CRITICAL DISCLOSURES ===")
hrm_monolith_found = "5,705 lines" in report and "hrm/index.blade.php" in report
approvals_inbox_found = "inbox.blade.php (381 lines)" in report
roles_index_found = "roles/index.blade.php (1,048 lines)" in report
storefront_tabs_found = "tab-features.blade.php" in report and "tab-schedule.blade.php" in report
whatsapp_detail_found = "broadcast_detail.blade.php (434 lines)" in report
print(f"HRM monolith disclosed: {hrm_monolith_found}")
print(f"Approvals inbox/history/rules disclosed: {approvals_inbox_found}")
print(f"Roles index disclosed: {roles_index_found}")
print(f"Storefront tabs disclosed: {storefront_tabs_found}")
print(f"WhatsApp broadcast_detail disclosed: {whatsapp_detail_found}")

print("\n=== 4. CHECK FOR LEFTOVER FABRICATED PATHS ===")
bad_strings = [
    'materials/categories', 'products/categories', 'purchasing/bills.blade.php',
    'purchasing/index.blade.php', 'purchasing/receivings', 'purchasing/returns.blade.php',
    'roles/create', 'roles/edit', 'roles/show', 'sales/index.blade.php', 'sales/returns.blade.php',
    'employees.blade.php', 'attendance.blade.php', 'templates.blade.php'
]
leftovers = [s for s in bad_strings if s in report]
print(f"Leftovers found: {len(leftovers)} -> {leftovers}")
