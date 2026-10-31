import os
import glob
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

base = r"c:\laragon\www\cooca_core\resources\views\app"

print("==================================================")
print("1. HARDCODED UNRESPONSIVE MULTI-COLUMN GRIDS (23 VIEWS)")
print("==================================================")
# The audit report pattern:
pattern_grid = re.compile(r'(?<![a-zA-Z0-9:])grid-cols-(?:[3-9]|1[0-2])\b')

grid_files = []
for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True):
    rel = os.path.relpath(f, base).replace('\\', '/')
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        lines = fp.readlines()
    matches = []
    for idx, l in enumerate(lines, 1):
        if pattern_grid.search(l):
            # check if it is part of a grid class and lacks responsive prefixes
            for m in pattern_grid.finditer(l):
                matches.append((idx, m.group(0), l.strip()))
    if matches:
        grid_files.append((rel, matches))

print(f"Total views matching grid pattern: {len(grid_files)}")
for rel, matches in grid_files:
    print(f"\nView: {rel} ({len(matches)} occurrences)")
    for line_num, match_str, text in matches:
        print(f"  Line {line_num}: [{match_str}] {text[:100]}")

print("\n==================================================")
print("2. RAW UNICODE EMOJIS (13 VIEWS)")
print("==================================================")
pattern_emoji = re.compile(r'[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]')

emoji_files = []
for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True):
    rel = os.path.relpath(f, base).replace('\\', '/')
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        lines = fp.readlines()
    matches = []
    for idx, l in enumerate(lines, 1):
        found = pattern_emoji.findall(l)
        if found:
            matches.append((idx, found, l.strip()))
    if matches:
        emoji_files.append((rel, matches))

print(f"Total views matching emoji pattern: {len(emoji_files)}")
for rel, matches in emoji_files:
    print(f"\nView: {rel} ({len(matches)} occurrences)")
    for line_num, found, text in matches:
        print(f"  Line {line_num}: {found} -> {text[:100]}")

print("\n==================================================")
print("3. MISSING MOBILE BOTTOM PADDING (32 VIEWS)")
print("==================================================")
# Let's inspect all 185 views to check which ones lack pb-20, pb-24, pb-28, pb-32, pb-36 or similar padding
views_no_pb = []
for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True):
    rel = os.path.relpath(f, base).replace('\\', '/')
    content = open(f, 'r', encoding='utf-8', errors='ignore').read()
    # If it's a print or thermal view, skip
    if any(k in rel for k in ['print', 'receipt', 'shipping_label', 'payslip', 'qr-card']):
        continue
    # check if extends layouts.app or is a full page
    if '@extends' in content:
        # Check outermost container padding
        # Does it have pb-28 or pb-32 or pb-20 or pb-24 or pb-36?
        has_pb = bool(re.search(r'\bpb-(?:20|24|28|32|36|40)\b', content))
        if not has_pb:
            views_no_pb.append(rel)

print(f"Total backoffice views lacking pb-[20-40]: {len(views_no_pb)}")
for v in views_no_pb:
    print(f"  {v}")

print("\n==================================================")
print("4. CRAMPED FORM MODALS IN 15 TRANSACTIONAL MODULES (127 LOCATIONS)")
print("==================================================")
target_submods = [
    'pos', 'products', 'warehouse', 'materials', 'storefront',
    'marketplace', 'sales-orders', 'invoices', 'quotations', 'inventory',
    'purchasing', 'purchase-orders', 'suppliers', 'sales', 'customers'
]
cramped_list = []
for sub in target_submods:
    sub_path = os.path.join(base, sub)
    if not os.path.exists(sub_path):
        continue
    for f in glob.glob(os.path.join(sub_path, '**', '*.blade.php'), recursive=True):
        rel = os.path.relpath(f, base).replace('\\', '/')
        with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
            lines = fp.readlines()
        for idx, l in enumerate(lines, 1):
            m = re.search(r'class="[^"]*\b(max-w-(?:xs|sm|md|lg|xl|2xl|3xl))\b[^"]*"', l)
            if m:
                # check if it lacks lg:max-w-4xl / 5xl / 6xl / 7xl
                cls = m.group(0)
                if not re.search(r'\b(?:lg|xl|2xl):max-w-(?:4xl|5xl|6xl|7xl)\b', cls):
                    # Check context: is it in a modal dialog / popup?
                    ctx = "".join(lines[max(0, idx-5):min(len(lines), idx+5)])
                    if any(w in ctx.lower() for w in ['modal', 'dialog', 'fixed inset-0', 'x-show', 'z-50', 'z-[200]', 'role="dialog"']):
                        cramped_list.append((rel, idx, m.group(1), cls))

print(f"Total cramped modal candidates in 15 transactional modules: {len(cramped_list)}")
by_sub = {}
for rel, idx, mw, cls in cramped_list:
    s = rel.split('/')[0]
    by_sub[s] = by_sub.get(s, 0) + 1
for s, c in sorted(by_sub.items(), key=lambda x: -x[1]):
    print(f"  {s}: {c}")
