import os, glob, re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8') as f:
    text = f.read()

base_dir = 'resources/views/app'

print("=== CHECKING SECTION 4 SUBMODULE VIEW INVENTORIES ===")

submodules_to_check = [
    ("4.42 HRM", "hrm"),
    ("4.43 Approvals", "approvals"),
    ("4.25 Roles", "roles"),
    ("4.5 Storefront", "storefront"),
    ("4.35 WhatsApp", "whatsapp"),
    ("4.7 Sales", "sales"),
    ("4.11 Purchasing", "purchasing"),
    ("4.2 Products", "products"),
]

for section_name, folder_name in submodules_to_check:
    print(f"\n--- Checking {section_name} (folder: {folder_name}) ---")
    # find section in text
    pattern = rf'###\s+{re.escape(section_name)}.*?(?=###\s+4\.|\Z)'
    m = re.search(pattern, text, re.DOTALL)
    if not m:
        # try without the number in case heading format differs
        pattern = rf'###\s+4\.\d+\s+{re.escape(folder_name.capitalize())}.*?(?=###\s+4\.|\Z)'
        m = re.search(pattern, text, re.DOTALL | re.I)
    
    if not m:
        print(f"FAILED to find section for {section_name}")
        continue
    
    section_content = m.group(0)
    
    # Extract all .blade.php files mentioned
    blade_mentions = re.findall(r'`([^`]+\.blade\.php)`', section_content)
    print(f"Found {len(blade_mentions)} blade mentions: {blade_mentions}")
    
    # Verify each on disk
    for bm in blade_mentions:
        # if relative to folder or relative to app
        path1 = os.path.join(base_dir, bm)
        path2 = os.path.join(base_dir, folder_name, bm)
        if os.path.isfile(path1):
            line_count = len(open(path1, encoding='utf-8', errors='ignore').readlines())
            print(f"  OK: {bm} exists at {path1} (LOC: {line_count})")
        elif os.path.isfile(path2):
            line_count = len(open(path2, encoding='utf-8', errors='ignore').readlines())
            print(f"  OK: {bm} exists at {path2} (LOC: {line_count})")
        else:
            print(f"  ERROR: {bm} NOT FOUND on disk!")
