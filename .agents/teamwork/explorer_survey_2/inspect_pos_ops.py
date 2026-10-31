import os
import re

for fname in ['pos/kitchen.blade.php', 'pos/tables.blade.php', 'pos/shifts.blade.php', 'pos/orders.blade.php', 'pos/printers/index.blade.php']:
    p = os.path.join(r'c:\laragon\www\cooca_core\resources\views\app', fname.replace('/', os.sep))
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
        content = "".join(lines)
    
    print(f"\n=================== {fname} ({len(lines)} lines) ===================")
    
    # Check layout
    grids = [l.strip() for l in lines if 'grid grid-cols-' in l]
    print(f"Grid Layouts ({len(grids)}): {grids[:3]}")
    
    # Check touch target buttons
    buttons = re.findall(r'<button\b[^>]*class="([^"]*)"', content)
    small_btns = [b for b in buttons if any(sh in b for sh in ['h-6', 'h-7', 'h-8']) and 'min-h-[44px]' not in b]
    large_btns = [b for b in buttons if any(lh in b for lh in ['min-h-[44px]', 'min-h-[48px]', 'h-11', 'h-12', 'h-14', 'h-16'])]
    print(f"Buttons total: {len(buttons)}, Small (<44px): {len(small_btns)}, Touch-compliant (>=44px): {len(large_btns)}")
    
    # Check modals in view
    modals = re.findall(r'x-show="[^"]*modal[^"]*"|id="[^"]*modal[^"]*"', content, re.IGNORECASE)
    print(f"Modals ({len(modals)}): {modals[:3]}")
    
    # Check audio / real-time / timers
    has_audio = 'Audio(' in content or '<audio' in content or 'playBeep' in content
    has_timer = 'setInterval' in content or 'setTimeout' in content
    print(f"Real-time audio: {has_audio}, Real-time timers: {has_timer}")
