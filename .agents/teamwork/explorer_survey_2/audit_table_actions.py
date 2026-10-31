import os
import re

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"

target_files = [
    'sales-orders/index.blade.php',
    'sales/returns/index.blade.php',
    'invoices/index.blade.php',
    'quotations/index.blade.php',
    'purchase-orders/index.blade.php',
    'purchasing/bills/index.blade.php',
    'purchasing/returns/index.blade.php',
    'inventory/stocks.blade.php',
    'inventory/opname.blade.php',
    'inventory/transfers.blade.php',
    'inventory/movements.blade.php',
    'products/index.blade.php',
    'materials/index.blade.php',
    'suppliers/index.blade.php',
    'warehouse/index.blade.php',
    'storefront/orders/index.blade.php',
    'marketplace/orders.blade.php'
]

print("=== TABLE ROW ACTION BUTTON AUDIT ===")
for rel_path in target_files:
    full_path = os.path.join(base_dir, rel_path.replace('/', os.sep))
    if not os.path.exists(full_path):
        continue
    
    with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    # Find all <td> containing actions
    # look for patterns like <div class="flex items-center justify-end gap-
    matches = re.findall(r'<div[^>]*class="[^"]*flex[^"]*items-center[^"]*justify-end[^"]*gap-[^"]*"[^>]*>(.*?)</div>', content, re.DOTALL)
    for m in matches:
        btns = re.findall(r'<(?:a|button)\b[^>]*class="([^"]*)"[^>]*>', m)
        if len(btns) >= 3:
            sizes = [re.findall(r'\b(w-\d+|h-\d+|p-\d+|py-[\d\.]+|px-[\d\.]+)\b', b) for b in btns]
            print(f"\n{rel_path}: {len(btns)} inline buttons!")
            for idx, b in enumerate(btns[:5]):
                sz = " ".join([x for sub in sizes[idx] for x in sub])
                print(f"   Button {idx+1}: {sz}")
