import os
import re

for fname in ['sales-orders/create.blade.php', 'invoices/create.blade.php', 'quotations/create.blade.php', 'purchase-orders/create.blade.php']:
    p = os.path.join(r'c:\laragon\www\cooca_core\resources\views\app', fname.replace('/', os.sep))
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    
    print(f"\n=================== {fname} ({len(lines)} lines) ===================")
    
    # Check form layout (grid columns, cards)
    grids = [l.strip() for l in lines if 'grid grid-cols-' in l]
    print(f"Grid Layouts ({len(grids)}): {grids[:3]}")
    
    # Check bottom submission bar
    submit_bars = [l.strip() for l in lines if any(k in l for k in ['type="submit"', 'Simpan', 'Kirim', 'Submit']) and ('<button' in l or '<input' in l)]
    print(f"Submit Buttons ({len(submit_bars)}): {submit_bars[:3]}")
    
    # Check if there is sticky bottom bar on mobile
    sticky_bottom = [l.strip() for l in lines if 'bottom-0' in l or 'fixed' in l and 'bottom' in l]
    print(f"Sticky Bottom Bars ({len(sticky_bottom)}): {sticky_bottom}")
    
    # Check line item table in form
    table_lines = [l.strip() for l in lines if '<table' in l]
    print(f"Tables in Form ({len(table_lines)}): {table_lines}")
