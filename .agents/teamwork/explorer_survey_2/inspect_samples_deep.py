import os
import re

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"

files_to_sample = [
    'sales-orders/create.blade.php',
    'sales-orders/index.blade.php',
    'invoices/create.blade.php',
    'invoices/show.blade.php',
    'quotations/create.blade.php',
    'purchase-orders/create.blade.php',
    'inventory/opname.blade.php',
    'warehouse/index.blade.php',
    'materials/index.blade.php',
    'suppliers/index.blade.php',
    'storefront/orders/show.blade.php',
    'marketplace/orders.blade.php'
]

print("=== DEEP SAMPLE INSPECTION ===")
for rel_path in files_to_sample:
    full_path = os.path.join(base_dir, rel_path.replace('/', os.sep))
    if not os.path.exists(full_path):
        print(f"NOT FOUND: {rel_path}")
        continue
    
    with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        lines = content.splitlines()
    
    # 1. Check layout structure & containers
    max_w = re.findall(r'max-w-\[?[0-9a-zA-Z]+\]?', content)
    tables = len(re.findall(r'<table\b', content))
    forms = len(re.findall(r'<form\b', content))
    sticky_bars = len(re.findall(r'\b(sticky|fixed)\s+(?:bottom-0|inset-x-0\s+bottom-0)', content))
    pb_bottom = re.findall(r'\bpb-(?:20|24|28|32|36|40|\[calc[^\]]+\])\b', content)
    
    # Check buttons
    primary_btns = len(re.findall(r'bg-\[#007AFF\]', content))
    destructive_btns = len(re.findall(r'bg-\[#FF3B30\]|text-\[#FF3B30\]', content))
    
    # Check Alpine
    has_alpine = 'x-data' in content
    
    # Check header
    header_title = ""
    m_h = re.search(r"'(?:title|headerTitle)'\s*=>\s*(?:__\([^)]+\)|'([^']+)'|\"([^\"]+)\")", content)
    if m_h:
        header_title = m_h.group(1) or m_h.group(2) or "localized"
    
    print(f"\n--- {rel_path} ({len(lines)} lines) ---")
    print(f"  Header: {header_title}")
    print(f"  Forms: {forms}, Tables: {tables}, Alpine: {has_alpine}")
    print(f"  Max Width Containers: {list(set(max_w))[:5]}")
    print(f"  Sticky/Fixed Bottom: {sticky_bars}, Safe Padding Bottom: {list(set(pb_bottom))}")
    print(f"  Buttons -> Primary (#007AFF): {primary_btns}, Destructive (#FF3B30): {destructive_btns}")
    
    # Look for button clustering in table rows
    row_actions = re.findall(r'<td[^>]*class="[^"]*whitespace-nowrap[^"]*"[^>]*>(.*?)</td>', content, re.DOTALL)
    if row_actions:
        btn_counts = [len(re.findall(r'<(?:button|a)\b', ra)) for ra in row_actions]
        if btn_counts and max(btn_counts) >= 3:
            print(f"  WARNING: Table row has {max(btn_counts)} inline action buttons without overflow menu!")
