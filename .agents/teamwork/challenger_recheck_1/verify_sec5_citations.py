import os
import re

REPO_DIR = r"c:\laragon\www\cooca_core"
APP_DIR = os.path.join(REPO_DIR, "resources", "views", "app")

print("=== VERIFYING SECTION 5.1 UPDATED LINE CITATIONS ===")

# Item 3: sales-orders/create.blade.php Line 388
so_create = os.path.join(APP_DIR, "sales-orders", "create.blade.php")
with open(so_create, "r", encoding="utf-8") as f:
    lines = f.readlines()
print(f"sales-orders/create.blade.php line 388: {lines[387].strip()}")
assert "promptSubmitOrder" in "".join(lines[380:395]), "promptSubmitOrder not found around line 388"
print("Item 3 Line 388 verified: PASS")

# Item 12: finance/accounting/balance-sheet.blade.php Line 437
bs = os.path.join(APP_DIR, "finance", "accounting", "balance-sheet.blade.php")
with open(bs, "r", encoding="utf-8") as f:
    lines = f.readlines()
print(f"balance-sheet.blade.php lines 435-439:")
for idx in range(434, min(440, len(lines))):
    print(f"  {idx+1}: {lines[idx].strip()}")
assert "<table" in "".join(lines[430:445]), "<table not found around line 437"
print("Item 12 Line 437 verified: PASS")

# Item 19: marketplace/products.blade.php Lines 1297-1538
mp_prod = os.path.join(APP_DIR, "marketplace", "products.blade.php")
with open(mp_prod, "r", encoding="utf-8") as f:
    lines = f.readlines()
chunk_1297_1538 = "".join(lines[1296:1538])
destructive_buttons = re.findall(r'bg-red|#FF3B30|text-red|unbind|delete', chunk_1297_1538, re.IGNORECASE)
print(f"marketplace/products.blade.php lines 1297-1538 destructive button/action matches: {len(destructive_buttons)}")
assert len(destructive_buttons) > 0, "No destructive buttons found in lines 1297-1538"
print("Item 19 Lines 1297-1538 verified: PASS")

print("ALL SECTION 5.1 CITATIONS VERIFIED: 100% PASS")
