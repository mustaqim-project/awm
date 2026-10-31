import os

manifest = [
    "billing/checkout.blade.php",
    "billing/history.blade.php",
    "billing/limits.blade.php",
    "billing/payment.blade.php",
    "crm/members.blade.php",
    "crm/vouchers.blade.php",
    "customers/index.blade.php",
    "feedback/create.blade.php",
    "feedback/index.blade.php",
    "feedback/show.blade.php",
    "invoices/index.blade.php",
    "invoices/show.blade.php",
    "marketplace/chat.blade.php",
    "marketplace/index.blade.php",
    "marketplace/logs.blade.php",
    "marketplace/orders.blade.php",
    "marketplace/products.blade.php",
    "materials/categories.blade.php",
    "portal/index.blade.php",
    "products/index.blade.php",
    "products/categories.blade.php",
    "profitability/index.blade.php",
    "purchase-orders/create.blade.php",
    "purchase-orders/index.blade.php",
    "purchase-orders/show.blade.php",
    "purchasing/bills.blade.php",
    "purchasing/index.blade.php",
    "purchasing/receivings.blade.php",
    "purchasing/returns.blade.php",
    "reports/index.blade.php",
    "roles/create.blade.php",
    "roles/edit.blade.php",
    "roles/index.blade.php",
    "roles/show.blade.php",
    "sales/index.blade.php",
    "sales/returns.blade.php",
    "sales-orders/create.blade.php",
    "sales-orders/index.blade.php",
    "sales-orders/show.blade.php",
    "security/audit-logs/index.blade.php",
    "services/index.blade.php",
    "settings/index.blade.php",
    "suppliers/index.blade.php",
    "warehouse/index.blade.php"
]

base = r"resources/views/app"
existing = []
non_existing = []

for item in manifest:
    p = os.path.join(base, item.replace('/', os.sep))
    if os.path.exists(p):
        existing.append(item)
    else:
        non_existing.append(item)

print(f"Total in manifest: {len(manifest)}")
print(f"Existing files: {len(existing)}")
print(f"Non-existing files: {len(non_existing)}")
for ne in non_existing:
    print(f"  - {ne}")
