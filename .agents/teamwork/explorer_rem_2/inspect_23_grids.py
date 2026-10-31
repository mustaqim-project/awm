import os
import re

base = r"c:\laragon\www\cooca_core\resources\views\app"
pattern_grid = re.compile(r'(?<![a-zA-Z0-9:])grid-cols-(?:[3-9]|1[0-2])\b')

grid_files = [
    "ai/index.blade.php",
    "ai/lobby.blade.php",
    "ai/office.blade.php",
    "ai/partials/virtual_office_canvas.blade.php",
    "ai-studio/create.blade.php",
    "analytics/index.blade.php",
    "billing/invoice.blade.php",
    "billing/limits.blade.php",
    "crm/members.blade.php",
    "labor-machines/index.blade.php",
    "marketplace/orders.blade.php",
    "marketplace/products.blade.php",
    "pos/terminal.blade.php",
    "pos/reports/partials/order_detail_modal.blade.php",
    "products/index.blade.php",
    "sales/returns/print.blade.php",
    "sales/returns/show.blade.php",
    "sales-orders/show.blade.php",
    "social_media/calendar.blade.php",
    "social_media/insights.blade.php",
    "social_media/posts.blade.php",
    "storefront/orders/shipping_label.blade.php",
    "warehouse/index.blade.php"
]

print(f"Total target grid files: {len(grid_files)}")

for gf in grid_files:
    fp = os.path.join(base, gf.replace('/', os.sep))
    if not os.path.exists(fp):
        print(f"FILE NOT FOUND: {gf}")
        continue
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    print(f"\n--- {gf} ---")
    for idx, l in enumerate(lines, 1):
        if pattern_grid.search(l):
            print(f"  Line {idx:4d}: {l.strip()}")
