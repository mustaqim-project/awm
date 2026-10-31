import os
import re

for fname in ['storefront/orders/show.blade.php', 'storefront/orders/index.blade.php', 'marketplace/orders.blade.php', 'marketplace/products.blade.php']:
    p = os.path.join(r'c:\laragon\www\cooca_core\resources\views\app', fname.replace('/', os.sep))
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
        content = "".join(lines)
    
    print(f"\n=================== {fname} ({len(lines)} lines) ===================")
    
    # Check grids / layout
    grids = [l.strip() for l in lines if 'grid grid-cols-' in l or 'lg:grid-cols-' in l]
    print(f"Grids ({len(grids)}): {grids[:3]}")
    
    # Check forms and actions
    forms = [l.strip() for l in lines if '<form' in l]
    print(f"Forms ({len(forms)}): {forms[:3]}")
    
    # Check modals
    modals = [l.strip() for l in lines if 'x-show="' in l and any(k in l.lower() for k in ['modal', 'dialog'])]
    print(f"Modals ({len(modals)}): {modals[:3]}")
    
    # Check buttons
    primary_btns = len(re.findall(r'bg-\[#007AFF\]', content))
    destructive_btns = len(re.findall(r'bg-\[#FF3B30\]|text-\[#FF3B30\]', content))
    print(f"Buttons -> Primary: {primary_btns}, Destructive: {destructive_btns}")
