import os
import re

for fname in ['inventory/stocks.blade.php', 'inventory/opname.blade.php', 'inventory/transfers.blade.php', 'inventory/movements.blade.php']:
    p = os.path.join(r'c:\laragon\www\cooca_core\resources\views\app', fname.replace('/', os.sep))
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
        content = "".join(lines)
    
    print(f"\n=================== {fname} ({len(lines)} lines) ===================")
    
    # Check tables
    tables = re.findall(r'<table[^>]*class="([^"]*)"', content)
    for t in tables:
        print(f"  Table class: {t}")
    
    # Check mobile cards
    has_mobile_view = 'sm:hidden' in content or 'md:hidden' in content or 'block sm:table' in content
    print(f"  Has dedicated mobile card view: {has_mobile_view}")
    
    # Check modals
    modals = re.findall(r'x-show="[^"]*modal[^"]*"', content, re.IGNORECASE)
    print(f"  Modals found ({len(modals)}): {modals}")
    
    # Check KPI cards
    kpi_grids = [l.strip() for l in lines if 'grid grid-cols-' in l]
    print(f"  KPI/Grid layouts: {kpi_grids[:2]}")
