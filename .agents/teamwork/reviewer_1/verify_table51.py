import os, sys

sys.stdout.reconfigure(encoding='utf-8')

def check_file_line(rel_path, target_line, search_str=None):
    full_path = os.path.join('resources', 'views', 'app', rel_path.replace('/', os.sep))
    if not os.path.exists(full_path):
        print(f"[MISSING FILE] {rel_path}")
        return
    with open(full_path, encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    if target_line <= len(lines):
        line_content = lines[target_line - 1].strip()
        print(f"[{rel_path}:{target_line}] {line_content[:100]}")
        if search_str:
            found = any(search_str in l for l in lines[max(0, target_line-5):min(len(lines), target_line+5)])
            print(f"   Nearby match for '{search_str}': {found}")
    else:
        print(f"[LINE OUT OF RANGE] {rel_path} has {len(lines)} lines, requested {target_line}")

print("Checking Table 5.1 citations:")
check_file_line('pos/reports/tabs/transactions.blade.php', 110)
check_file_line('finance/accounting/balance-sheet.blade.php', 45)
check_file_line('ai/actions.blade.php', 1)
check_file_line('invoices/create.blade.php', 480)
check_file_line('import/index.blade.php', 1)
check_file_line('materials/index.blade.php', 443)
check_file_line('storefront/orders/show.blade.php', 1122)
check_file_line('pos/kitchen.blade.php', 45)
check_file_line('marketplace/products.blade.php', 450)
check_file_line('landing_page/edit.blade.php', 1)
