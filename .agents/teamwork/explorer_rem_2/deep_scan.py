import os
import re
import glob

BASE_DIR = r"c:\laragon\www\cooca_core\resources\views\app"

print("--- 1. SCANNING CRAMPED MODALS ---")
modal_pattern = re.compile(r'class="[^"]*\b(max-w-(?:xs|sm|md|lg))\b[^"]*"')
# also check if inside modal structure (e.g. fixed inset-0 or x-show or role="dialog" or modal)

all_blade_files = glob.glob(os.path.join(BASE_DIR, "**", "*.blade.php"), recursive=True)

cramped_modals = []
for file_path in all_blade_files:
    rel_path = os.path.relpath(file_path, BASE_DIR).replace("\\", "/")
    with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
    for idx, line in enumerate(lines, 1):
        # find instances of max-w-xs, max-w-sm, max-w-md, max-w-lg in context of modal
        m = modal_pattern.search(line)
        if m:
            # check context (is it a modal or dialog container?)
            # look around +/- 5 lines
            context_window = "".join(lines[max(0, idx-6):min(len(lines), idx+5)])
            if any(k in context_window.lower() for k in ["modal", "dialog", "x-show", "fixed inset-0", "backdrop", "z-[200]", "z-50", "rounded-"]):
                cramped_modals.append({
                    "file": rel_path,
                    "line": idx,
                    "class": m.group(1),
                    "line_content": line.strip()
                })

print(f"Total cramped modal candidate occurrences: {len(cramped_modals)}")

# Group by submodule
by_submod = {}
for item in cramped_modals:
    sub = item["file"].split("/")[0]
    by_submod[sub] = by_submod.get(sub, 0) + 1
for sub, cnt in sorted(by_submod.items(), key=lambda x: -x[1]):
    print(f"  {sub}: {cnt}")

print("\n--- 2. SCANNING TABLE ACTIONS ---")
table_clutter_files = [
    "sales-orders/index.blade.php",
    "invoices/show.blade.php",
    "sales/returns/index.blade.php",
    "materials/index.blade.php",
    "marketplace/products.blade.php",
    "quotations/index.blade.php",
    "invoices/index.blade.php",
    "products/index.blade.php",
    "purchasing/bills/index.blade.php",
    "purchase-orders/index.blade.php",
    "services/index.blade.php"
]
for tf in table_clutter_files:
    full_p = os.path.join(BASE_DIR, tf)
    if os.path.exists(full_p):
        print(f"File exists: {tf}")
    else:
        print(f"File NOT found: {tf}")

print("\n--- 3. SCANNING MISSING MOBILE BOTTOM PADDING (32 VIEWS) ---")
# From audit report, let's test the 32 views
reported_padding_views = [
    "ai/actions.blade.php", "ai/history.blade.php", "ai/index.blade.php", "ai/lobby.blade.php",
    "ai/office.blade.php", "ai/providers.blade.php", "ai/offices/executive.blade.php",
    "ai/offices/growth.blade.php", "ai/offices/operations.blade.php", "community/events.blade.php",
    "community/event_payment.blade.php", "community/index.blade.php", "crm/vouchers.blade.php",
    "feedback/create.blade.php", "feedback/index.blade.php", "feedback/show.blade.php",
    "finance/accounting/balance-sheet.blade.php", "finance/accounting/coa.blade.php",
    "finance/accounting/general-ledger.blade.php", "finance/accounting/journals.blade.php",
    "finance/accounting/reconciliations.blade.php", "finance/accounting/trial-balance.blade.php",
    "finance/cash-bank/index.blade.php", "finance/expenses/index.blade.php",
    "finance/external-reconciliation/index.blade.php", "landing_page/edit.blade.php",
    "landing_page/popup.blade.php", "mcp/index.blade.php", "mcp/server/index.blade.php",
    "portal/index.blade.php", "quotations/index.blade.php", "social_media/posts.blade.php"
]
for pv in reported_padding_views:
    fp = os.path.join(BASE_DIR, pv)
    exists = os.path.exists(fp)
    if exists:
        content = open(fp, "r", encoding="utf-8", errors="ignore").read()
        has_pb = bool(re.search(r'\bpb-(?:20|24|28|32|36)\b', content))
        print(f"  {pv}: exists=True, has_pb={has_pb}")
    else:
        print(f"  {pv}: exists=False (check actual path)")

print("\n--- 4. SCANNING UNRESPONSIVE GRIDS (23 VIEWS) ---")
unresponsive_grid_pattern = re.compile(r'class="[^"]*\bgrid\b[^"]*\b(grid-cols-[3-9]|grid-cols-[1-9][0-9])\b[^"]*"')
# check if it lacks grid-cols-1 or sm: or md: or lg:
grid_views = {}
for file_path in all_blade_files:
    rel_path = os.path.relpath(file_path, BASE_DIR).replace("\\", "/")
    with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
    for idx, line in enumerate(lines, 1):
        for m in re.finditer(r'class="([^"]*)"', line):
            cls = m.group(1)
            if "grid" in cls.split():
                # check if there is grid-cols-3 or 4 without grid-cols-1 or sm:
                cols = re.findall(r'(?:^|\s)(grid-cols-[2-9])(?:\s|$)', cls)
                if cols and not any(p in cls for p in ["grid-cols-1", "sm:grid-cols", "md:grid-cols", "lg:grid-cols"]):
                    if rel_path not in grid_views:
                        grid_views[rel_path] = []
                    grid_views[rel_path].append((idx, cols[0], cls))

print(f"Total views with unresponsive grids: {len(grid_views)}")
for gv, occurrences in grid_views.items():
    print(f"  {gv}: {len(occurrences)} occurrences (lines: {[x[0] for x in occurrences]})")

print("\n--- 5. SCANNING RAW UNICODE EMOJIS (13 VIEWS) ---")
# Emojis regex: cars, laundry, pills, tags, stars, smileys, etc.
emoji_pattern = re.compile(r'([\U0001F300-\U0001F9FF]|[\u2600-\u26FF]|[\u2700-\u27BF]|[\u2B50]|[\u2605]|[\u2022]|★|🚗|🧺|💊|🏷|⭐|●|😊|✨|🙏|📝)')
emoji_views = {}
for file_path in all_blade_files:
    rel_path = os.path.relpath(file_path, BASE_DIR).replace("\\", "/")
    with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
    for idx, line in enumerate(lines, 1):
        found = emoji_pattern.findall(line)
        # filter out standard dots or common punctuation if any, keep distinct emojis
        emojis_found = [e for e in found if e in ['🚗', '🧺', '💊', '🏷', '🏷️', '★', '⭐', '●', '😊', '✨', '🙏', '📝', '🛒', '📦', '💳', '💡', '⚠️']]
        if emojis_found:
            if rel_path not in emoji_views:
                emoji_views[rel_path] = []
            emoji_views[rel_path].append((idx, emojis_found, line.strip()))

print(f"Total views with raw emojis: {len(emoji_views)}")
for ev, occurrences in emoji_views.items():
    print(f"  {ev}: {len(occurrences)} occurrences (lines: {[x[0] for x in occurrences]})")
