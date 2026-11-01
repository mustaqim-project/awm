import os, re

files = [
    'resources/views/app/ai/index.blade.php',
    'resources/views/app/ai/lobby.blade.php',
    'resources/views/app/ai/office.blade.php',
    'resources/views/app/ai/partials/virtual_office_canvas.blade.php',
    'resources/views/app/ai-studio/create.blade.php',
    'resources/views/app/analytics/index.blade.php',
    'resources/views/app/billing/invoice.blade.php',
    'resources/views/app/billing/limits.blade.php',
    'resources/views/app/crm/members.blade.php',
    'resources/views/app/labor-machines/index.blade.php',
    'resources/views/app/marketplace/orders.blade.php',
    'resources/views/app/marketplace/products.blade.php',
    'resources/views/app/pos/terminal.blade.php',
    'resources/views/app/pos/reports/partials/order_detail_modal.blade.php',
    'resources/views/app/products/index.blade.php',
    'resources/views/app/sales/returns/print.blade.php',
    'resources/views/app/sales/returns/show.blade.php',
    'resources/views/app/sales-orders/show.blade.php',
    'resources/views/app/social_media/calendar.blade.php',
    'resources/views/app/social_media/insights.blade.php',
    'resources/views/app/social_media/posts.blade.php',
    'resources/views/app/storefront/orders/shipping_label.blade.php',
    'resources/views/app/warehouse/index.blade.php'
]

root = r'c:\laragon\www\cooca_core'
pattern = re.compile(r'(?<![a-zA-Z0-9:])grid-cols-(?:[3-9]|1[0-2])\b')

for rel in files:
    full = os.path.join(root, rel.replace('/', os.sep))
    if not os.path.exists(full):
        print(f"NOT FOUND: {rel}")
        continue
    lines = open(full, encoding='utf-8', errors='ignore').readlines()
    matches = []
    for idx, line in enumerate(lines):
        if pattern.search(line):
            matches.append((idx + 1, line.strip()))
    print(f"FILE: {rel} ({len(matches)} matches)")
    for lno, text in matches:
        print(f"  [{lno}] {text}")
