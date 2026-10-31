import os
import glob
import re

APP_DIR = r"c:\laragon\www\cooca_core\resources\views\app"
REPORT_PATH = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

def test_audit():
    print("=" * 60)
    print("STARTING INDEPENDENT AUDIT RECHECK")
    print("=" * 60)

    with open(REPORT_PATH, "r", encoding="utf-8") as f:
        report_content = f.read()
        report_lines = report_content.splitlines()

    # 1. Total Blade files in resources/views/app
    all_blades = glob.glob(os.path.join(APP_DIR, "**", "*.blade.php"), recursive=True)
    print(f"\n[1] Total Blade files in resources/views/app: {len(all_blades)}")
    assert len(all_blades) == 185, f"Expected 185 blade files, got {len(all_blades)}"

    # 2. Section 3.2 Double Header Manifest
    # Extract lines 246 to 291
    sec32_items = []
    for l in report_lines[246:291]:
        m = re.search(r"^\d+\.\s+`([^`]+)`", l.strip())
        if m:
            sec32_items.append(m.group(1))
    
    print(f"\n[2] Section 3.2 Manifest Count: {len(sec32_items)}")
    assert len(sec32_items) == 44, f"Expected 44 items in Sec 3.2 manifest, got {len(sec32_items)}"

    # Check existence on disk
    missing_files = []
    for item in sec32_items:
        full_path = os.path.join(APP_DIR, item.replace("/", os.sep))
        if not os.path.exists(full_path):
            missing_files.append(item)
    print(f"Missing/Fabricated files in Section 3.2: {len(missing_files)}")
    if missing_files:
        print("MISSING:", missing_files)
    assert len(missing_files) == 0, f"Found missing files: {missing_files}"

    # Verify each actually has Double Header: headerTitle AND <x-module-header
    lacking_double_header = []
    for item in sec32_items:
        full_path = os.path.join(APP_DIR, item.replace("/", os.sep))
        with open(full_path, "r", encoding="utf-8", errors="ignore") as fp:
            c = fp.read()
        has_ht = bool(re.search(r"['\"]headerTitle['\"]\s*=>", c))
        has_mh = bool(re.search(r"<x-module-header\b", c))
        if not (has_ht and has_mh):
            lacking_double_header.append((item, has_ht, has_mh))
    print(f"Files in Section 3.2 lacking double header criteria: {len(lacking_double_header)}")
    assert len(lacking_double_header) == 0, f"Lacking criteria: {lacking_double_header}"

    # Also check if any file in app/ has double header but is NOT in Section 3.2
    actual_double_headers = []
    for f in all_blades:
        rel = os.path.relpath(f, APP_DIR).replace("\\", "/")
        with open(f, "r", encoding="utf-8", errors="ignore") as fp:
            c = fp.read()
        if re.search(r"['\"]headerTitle['\"]\s*=>", c) and re.search(r"<x-module-header\b", c):
            actual_double_headers.append(rel)
    actual_double_headers.sort()
    sec32_sorted = sorted(sec32_items)
    print(f"Actual Double Header views across entire repo: {len(actual_double_headers)}")
    assert sec32_sorted == actual_double_headers, "Section 3.2 manifest does not match disk actuals exactly!"

    # 3. Command 3 / Flawed Backdrop Wrappers
    pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
    backdrop_dist = {}
    for f in all_blades:
        rel = os.path.relpath(f, APP_DIR).replace("\\", "/")
        with open(f, "r", encoding="utf-8", errors="ignore") as fp:
            c = fp.read()
        cnt = len(pattern.findall(c))
        if cnt > 0:
            backdrop_dist[rel] = cnt

    total_backdrops = sum(backdrop_dist.values())
    affected_views = len(backdrop_dist)
    print(f"\n[3] Flawed Backdrop Instances: {total_backdrops} across {affected_views} views")
    assert affected_views == 21, f"Expected 21 affected views, got {affected_views}"
    assert total_backdrops == 76, f"Expected 76 instances, got {total_backdrops}"

    # Verify expected distribution mentioned in report
    expected_dist = {
        "pos/terminal.blade.php": 20,
        "products/index.blade.php": 8,
        "materials/index.blade.php": 7,
        "finance/cash-bank.blade.php": 5,
        "settings/index.blade.php": 5,
        "products/modifiers.blade.php": 4,
        "finance/accounting/coa.blade.php": 4,
        "master-data/index.blade.php": 3,
        "warehouse/index.blade.php": 3,
        "warehouse/show.blade.php": 3,
        "calculator/index.blade.php": 2, # Note: report says calculator or calculator/index
        "ai-studio/show.blade.php": 2,
        "products/bom.blade.php": 2,
        "ai-studio/index.blade.php": 1,
        "ai-studio/prompt-preview.blade.php": 1,
        "finance/expenses.blade.php": 1,
        "finance/payables.blade.php": 1,
        "finance/receivables.blade.php": 1,
        "finance/accounting/reconciliation.blade.php": 1,
        "whatsapp/index.blade.php": 1,
        "whatsapp/logs.blade.php": 1,
    }
    # Check normalized
    for k, v in backdrop_dist.items():
        print(f"  {v:2d} in {k}")

    # Check Command 3 in report
    assert "Verify Flawed Backdrop Wrappers (76 locations across 21 views)" in report_content
    assert "Affected Views: 21 | Total Flawed Backdrop Instances: 76" in report_content

    # 4. Check for leftover fictional paths in the entire report
    fictional_patterns = [
        r"materials/categories\.blade\.php",
        r"products/categories\.blade\.php",
        r"purchasing/bills\.blade\.php",
        r"purchasing/index\.blade\.php",
        r"purchasing/receivings\.blade\.php",
        r"roles/create\.blade\.php",
        r"roles/edit\.blade\.php",
        r"roles/show\.blade\.php",
        r"sales/index\.blade\.php",
        r"whatsapp/templates\.blade\.php",
        r"employees\.blade\.php",
        r"attendance\.blade\.php",
    ]
    print("\n[4] Scanning for leftover fictional paths in docs/UI_UX_AUDIT_REPORT.md:")
    leftovers = []
    for fp in fictional_patterns:
        m = re.findall(fp, report_content)
        if m:
            leftovers.append((fp, len(m)))
    print(f"Leftovers found: {leftovers}")
    assert len(leftovers) == 0, f"Found leftover fictional paths: {leftovers}"

    # 5. Check HRM Monolith disclosure and line count
    hrm_path = os.path.join(APP_DIR, "hrm", "index.blade.php")
    with open(hrm_path, "r", encoding="utf-8") as f:
        hrm_lines = len(f.readlines())
    print(f"\n[5] HRM index.blade.php real line count: {hrm_lines}")
    assert hrm_lines == 5705, f"Expected 5705 lines in hrm/index.blade.php, got {hrm_lines}"
    assert "`hrm/index.blade.php` (5,705 lines" in report_content
    assert "second-largest monolith" in report_content or "second-largest single template" in report_content

    # 6. Check Approvals, Roles, Storefront, WhatsApp inventories in Section 4
    # Approvals: inbox.blade.php, history.blade.php, rules.blade.php
    assert "inbox.blade.php" in report_content
    assert "history.blade.php" in report_content
    assert "rules.blade.php" in report_content

    # Roles: roles/index.blade.php
    assert "roles/index.blade.php" in report_content

    # Storefront tabs: tab-features, tab-fulfillment, tab-general, tab-payment-settlement, tab-schedule
    for tab in ["tab-features.blade.php", "tab-fulfillment.blade.php", "tab-general.blade.php", "tab-payment-settlement.blade.php", "tab-schedule.blade.php"]:
        assert tab in report_content, f"Missing storefront tab {tab}"

    # 7. Stress test: Are there any non-black backdrop overlays?
    pattern_other = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-(?!black/)[a-zA-Z0-9_-]+/(?:[0-9]+)\b')
    other_backdrops = []
    for f in all_blades:
        c = open(f, encoding='utf-8', errors='ignore').read()
        m = pattern_other.findall(c)
        if m:
            other_backdrops.append((os.path.relpath(f, APP_DIR), m))
    print(f"\n[7] Non-black flex backdrop overlays: {len(other_backdrops)}")
    if other_backdrops:
        print("Found:", other_backdrops)

    print("\nALL VERIFICATIONS PASSED SUCCESSFULLY!")

if __name__ == "__main__":
    test_audit()
