import os
import glob
import re
import json

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"
report_path = r"c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md"

print("=" * 60)
print("INDEPENDENT FORENSIC VERIFICATION SCRIPT")
print("=" * 60)

# Check 1: File count and LOC in resources/views/app
blade_files = glob.glob(os.path.join(base_dir, "**", "*.blade.php"), recursive=True)
all_files = []
for r, d, files in os.walk(base_dir):
    for f in files:
        all_files.append(os.path.join(r, f))

non_blades = [f for f in all_files if not f.endswith(".blade.php")]
submodules = set(os.path.relpath(f, base_dir).split(os.sep)[0] for f in blade_files if os.sep in os.path.relpath(f, base_dir))
root_files = [os.path.relpath(f, base_dir) for f in blade_files if os.sep not in os.path.relpath(f, base_dir)]
loc_total = sum(sum(1 for _ in open(f, encoding="utf-8", errors="ignore")) for f in blade_files)

print(f"[Check 1] Total Blade Views: {len(blade_files)}")
print(f"          Total Files in Directory: {len(all_files)} (Non-blade: {len(non_blades)})")
print(f"          Submodules count: {len(submodules)} distinct subdirs + root files ({len(root_files)}) = {len(submodules) + (1 if root_files else 0)}")
print(f"          Total LOC: {loc_total}")

# Check 2: Layout Architecture (layouts.app vs Standalone/Partials)
extends_app = []
standalone_or_partials = []
for f in blade_files:
    rel = os.path.relpath(f, base_dir).replace(os.sep, "/")
    content = open(f, encoding="utf-8", errors="ignore").read()
    if "@extends('layouts.app'" in content or '@extends("layouts.app"' in content:
        extends_app.append(rel)
    else:
        standalone_or_partials.append(rel)

print(f"[Check 2] Layout Architecture:")
print(f"          Extends layouts.app: {len(extends_app)} views (Claimed: 134)")
print(f"          Standalone/Partials: {len(standalone_or_partials)} views (Claimed: 51)")

# Check 3: Flawed Modal Backdrop Pattern (Command 3)
pattern_backdrop = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
flawed_backdrops = {}
for f in blade_files:
    rel = os.path.relpath(f, base_dir).replace(os.sep, "/")
    content = open(f, encoding="utf-8", errors="ignore").read()
    m = pattern_backdrop.findall(content)
    if m:
        flawed_backdrops[rel] = len(m)

total_backdrop_instances = sum(flawed_backdrops.values())
print(f"[Check 3] Flawed Backdrop Wrappers:")
print(f"          Affected Views: {len(flawed_backdrops)} (Claimed: 21)")
print(f"          Total Instances: {total_backdrop_instances} (Claimed: 76)")

# Check 4: Double Header Redundancy (Command 2 / Section 3.2 Manifest)
manifest_44 = [
    'billing/checkout.blade.php', 'billing/history.blade.php', 'billing/limits.blade.php', 'billing/payment.blade.php',
    'crm/members.blade.php', 'crm/vouchers.blade.php', 'customers/index.blade.php',
    'feedback/create.blade.php', 'feedback/index.blade.php', 'feedback/show.blade.php',
    'invoices/index.blade.php', 'invoices/show.blade.php',
    'marketplace/chat.blade.php', 'marketplace/index.blade.php', 'marketplace/logs.blade.php',
    'marketplace/orders.blade.php', 'marketplace/products.blade.php',
    'master-data/index.blade.php', 'materials/index.blade.php',
    'products/bom.blade.php', 'products/index.blade.php', 'products/modifiers.blade.php',
    'purchase-orders/index.blade.php',
    'purchasing/bills/index.blade.php', 'purchasing/bills/show.blade.php',
    'purchasing/receipts/create.blade.php', 'purchasing/returns/create.blade.php',
    'purchasing/returns/index.blade.php', 'purchasing/returns/show.blade.php',
    'quotations/index.blade.php', 'sales-orders/index.blade.php', 'sales/returns/index.blade.php',
    'services/index.blade.php',
    'social_media/calendar.blade.php', 'social_media/inbox.blade.php', 'social_media/index.blade.php',
    'social_media/insights.blade.php', 'social_media/posts.blade.php',
    'suppliers/index.blade.php', 'warehouse/index.blade.php', 'warehouse/show.blade.php',
    'whatsapp/broadcast.blade.php', 'whatsapp/index.blade.php', 'whatsapp/logs.blade.php'
]
on_disk_dh = []
for f in blade_files:
    rel = os.path.relpath(f, base_dir).replace(os.sep, "/")
    content = open(f, encoding="utf-8", errors="ignore").read()
    if 'headerTitle' in content and '<x-module-header' in content:
        on_disk_dh.append(rel)

print(f"[Check 4] Double Header Redundancy:")
print(f"          Section 3.2 Manifest items: {len(manifest_44)}")
print(f"          On-disk double header views: {len(on_disk_dh)} (Claimed: 44)")
print(f"          Manifest matches on-disk: {set(manifest_44) == set(on_disk_dh)}")

# Check 5: Monolith line counts
monoliths = {
    'pos/terminal.blade.php': 7158,
    'hrm/index.blade.php': 5705,
    'ai/partials/virtual_office_canvas.blade.php': 3875,
    'products/index.blade.php': 3443,
    'landing_page/edit.blade.php': 3302  # or 3334
}
print(f"[Check 5] Monolithic Files Line Counts:")
for rel, claimed_loc in monoliths.items():
    p = os.path.join(base_dir, rel.replace('/', os.sep))
    if os.path.exists(p):
        actual_loc = sum(1 for _ in open(p, encoding='utf-8', errors='ignore'))
        print(f"          {rel}: actual {actual_loc} vs claimed ~{claimed_loc}")
    else:
        print(f"          {rel}: NOT FOUND ON DISK!")

# Check 6: Hardcoded grid-cols-3 or grid-cols-4 without breakpoint prefixes
pattern_grid = re.compile(r'class="[^"]*(?<![a-zA-Z0-9:])grid-cols-[34]\b')
grid_views = set()
for f in blade_files:
    rel = os.path.relpath(f, base_dir).replace(os.sep, "/")
    content = open(f, encoding="utf-8", errors="ignore").read()
    if pattern_grid.search(content):
        grid_views.add(rel)

print(f"[Check 6] Hardcoded Unresponsive Grids: {len(grid_views)} views (Claimed: 23)")

# Check 7: Missing mobile safe-area padding pb-28 / pb-36
missing_pb = []
for rel in extends_app:
    p = os.path.join(base_dir, rel.replace('/', os.sep))
    content = open(p, encoding="utf-8", errors="ignore").read()
    if 'pb-28' not in content and 'pb-32' not in content and 'pb-36' not in content:
        missing_pb.append(rel)

print(f"[Check 7] Views extending layouts.app missing pb-28/32/36: {len(missing_pb)} (Claimed: 32)")

# Check 8: Raw Unicode emojis in transaction tables/badges
emoji_views = []
emoji_pattern = re.compile(r'[\U0001F300-\U0001F9FF]')
for f in blade_files:
    rel = os.path.relpath(f, base_dir).replace(os.sep, "/")
    content = open(f, encoding="utf-8", errors="ignore").read()
    if emoji_pattern.search(content):
        emoji_views.append(rel)

print(f"[Check 8] Views with raw Unicode emojis: {len(emoji_views)} (Claimed: 13)")
