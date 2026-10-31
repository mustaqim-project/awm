with open(r'c:\laragon\www\cooca_core\resources\views\app\pos\terminal.blade.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print("=== POS BREAKPOINT & CART INSPECTION ===")
for i, l in enumerate(lines):
    if any(k in l for k in ['pos-cart', 'cartOpen', 'cartDrawer', 'showMobileCart', 'bottom-bar', 'floating-cart', 'lg:w-', 'md:w-']):
        print(f"Line {i+1}: {l.strip()[:100]}")
