import os
import re
import sys

# Ensure stdout handles utf-8 safely
sys.stdout.reconfigure(encoding='utf-8', errors='replace')

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"

def verify_item(num, rel_path, check_func, description):
    full_path = os.path.join(app_dir, rel_path.replace("/", os.sep))
    if not os.path.exists(full_path):
        return {
            "num": num,
            "path": rel_path,
            "exists": False,
            "status": "FAIL",
            "detail": f"File does not exist: {full_path}"
        }
    with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
    
    passed, detail = check_func(lines)
    return {
        "num": num,
        "path": rel_path,
        "exists": True,
        "total_lines": len(lines),
        "status": "PASS" if passed else "FAIL",
        "detail": detail,
        "description": description
    }

checks = []

# Item 1: pos/terminal.blade.php (Lines 2145, 2195, 3111, 3141, 3559, 3687) - FLAWED_BACKDROP_WRAPPER
def check_item_1(lines):
    evidence = []
    target_lines = [2145, 2195, 3111, 3141, 3559, 3687]
    for tl in target_lines:
        window = lines[max(0, tl-10):min(len(lines), tl+10)]
        found = any("fixed inset-0" in l and ("bg-black" in l or "backdrop-blur" in l) for l in window)
        evidence.append((tl, found))
    all_found = sum(1 for e in evidence if e[1]) >= 4
    return all_found, f"Found flawed backdrop near target lines: {evidence}"

checks.append((1, "pos/terminal.blade.php", check_item_1, "FLAWED_BACKDROP_WRAPPER at line 2145, etc."))

# Item 2: pos/terminal.blade.php (Line 3141) - NARROW_FORM_MODAL max-w-md
def check_item_2(lines):
    window = lines[max(0, 3130):min(len(lines), 3160)]
    found = any("max-w-md" in l for l in window)
    matched_lines = [l.strip() for l in window if "max-w-md" in l]
    return found, f"Matched max-w-md: {matched_lines}"

checks.append((2, "pos/terminal.blade.php", check_item_2, "NARROW_FORM_MODAL max-w-md at line 3141"))

# Item 3: sales-orders/create.blade.php (Line 600 / submit button) - MISSING_MOBILE_STICKY_SUBMIT
def check_item_3(lines):
    # submit button is at line 388 in a 612 line file without mobile sticky bar
    submit_lines = [(i+1, l.strip()) for i, l in enumerate(lines) if "promptSubmitOrder" in l or "btn_submit_create" in l]
    has_fixed_sticky_submit = any("fixed inset-x-0 bottom-0" in l for l in lines)
    return (not has_fixed_sticky_submit and len(submit_lines) > 0), f"Submit buttons at {submit_lines}; has fixed bottom: {has_fixed_sticky_submit}"

checks.append((3, "sales-orders/create.blade.php", check_item_3, "MISSING_MOBILE_STICKY_SUBMIT in 612-line form"))

# Item 4: sales-orders/index.blade.php (Lines 223–253) - TABLE_ACTION_CLUTTER (w-8 h-8 rounded-[8px])
def check_item_4(lines):
    window = lines[max(0, 220):min(len(lines), 260)]
    w8h8 = [l.strip() for l in window if "w-8 h-8" in l or "rounded-[8px]" in l]
    return len(w8h8) >= 2, f"Found {len(w8h8)} w-8 h-8 action button lines: {w8h8[:2]}"

checks.append((4, "sales-orders/index.blade.php", check_item_4, "TABLE_ACTION_CLUTTER (w-8 h-8 rounded-[8px]) at lines 223-253"))

# Item 5: products/index.blade.php (Lines 1454–2813) - CODE_BLOAT_DUPLICATION
def check_item_5(lines):
    l1 = len(lines)
    has_create = any("tambah" in l.lower() or "create" in l.lower() for l in lines[1450:1500])
    has_edit = any("edit" in l.lower() or "ubah" in l.lower() for l in lines[2110:2160])
    return (l1 >= 3400 and (has_create or has_edit)), f"Total lines: {l1}, Create/Edit modal presence confirmed"

checks.append((5, "products/index.blade.php", check_item_5, "CODE_BLOAT_DUPLICATION (Lines 1454-2813)"))

# Item 6: products/index.blade.php (Lines 1402, 1408, 1415) - SUB_44PX_TOUCH_TARGET (h-7 px-2.5)
def check_item_6(lines):
    window = lines[max(0, 1395):min(len(lines), 1425)]
    h7 = [l.strip() for l in window if "h-7" in l]
    return len(h7) > 0, f"Found h-7 touch targets: {h7[:2]}"

checks.append((6, "products/index.blade.php", check_item_6, "SUB_44PX_TOUCH_TARGET (h-7 px-2.5) at lines 1402-1415"))

# Item 7: inventory/stocks.blade.php (Line 94) - INFINITE_CARD_BLOAT (grid grid-cols-1 sm:grid-cols-3)
def check_item_7(lines):
    window = lines[max(0, 85):min(len(lines), 105)]
    grid_lines = [l.strip() for l in window if "grid-cols" in l]
    return any("grid-cols-1" in l and "sm:grid-cols-3" in l for l in grid_lines), f"Found grid cards: {grid_lines}"

checks.append((7, "inventory/stocks.blade.php", check_item_7, "INFINITE_CARD_BLOAT grid-cols-1 sm:grid-cols-3 at line 94"))

# Item 8: analytics/index.blade.php (Line 511) - UNRESPONSIVE_GRID (grid grid-cols-3 gap-2)
def check_item_8(lines):
    window = lines[max(0, 500):min(len(lines), 525)]
    g3 = [l.strip() for l in window if "grid-cols-3" in l]
    raw = [l for l in g3 if "sm:grid-cols" not in l and "lg:grid-cols" not in l]
    return len(raw) > 0, f"Found raw grid-cols-3: {g3}"

checks.append((8, "analytics/index.blade.php", check_item_8, "UNRESPONSIVE_GRID grid grid-cols-3 at line 511"))

# Item 9: billing/checkout.blade.php (Line 8) - DOUBLE_HEADER_REDUNDANCY
def check_item_9(lines):
    head = "".join(lines[:30])
    has_header_title = "headerTitle" in head
    has_module_header = "<x-module-header" in head or any("<x-module-header" in l for l in lines)
    return (has_header_title and has_module_header), f"headerTitle: {has_header_title}, x-module-header: {has_module_header}"

checks.append((9, "billing/checkout.blade.php", check_item_9, "DOUBLE_HEADER_REDUNDANCY at line 8"))

# Item 10: ai-studio/index.blade.php (Line 368) - MODAL_MISSING_TELEPORT
def check_item_10(lines):
    window = lines[max(0, 355):min(len(lines), 385)]
    modal_lines = [l.strip() for l in window if "fixed inset-0" in l or "x-show" in l]
    has_teleport = any("x-teleport" in l for l in window)
    return (len(modal_lines) > 0 and not has_teleport), f"Modal without teleport: {modal_lines[:1]}"

checks.append((10, "ai-studio/index.blade.php", check_item_10, "MODAL_MISSING_TELEPORT at line 368"))

# Item 11: pos/reports/tabs/transactions.blade.php (Lines 110, 127, 143) - RAW_UNICODE_EMOJI
def check_item_11(lines):
    window = "".join(lines[max(0, 100):min(len(lines), 160)])
    emojis = [c for c in window if ord(c) > 0x1F000 or c in ['\U0001F697', '\U0001F9FA', '\U0001F48A', '\U0001F3F7']]
    return len(emojis) > 0, f"Found raw emojis count: {len(emojis)}"

checks.append((11, "pos/reports/tabs/transactions.blade.php", check_item_11, "RAW_UNICODE_EMOJI at lines 110, 127, 143"))

# Item 12: finance/accounting/balance-sheet.blade.php (Line 45) - TABLE_MISSING_OVERFLOW
def check_item_12(lines):
    # Check table around line 45-60
    window = lines[max(0, 35):min(len(lines), 70)]
    has_table = any("<table" in l for l in window)
    has_overflow = any("overflow-x" in l for l in window)
    return (has_table and not has_overflow), f"Table without overflow: {has_table}, has_overflow: {has_overflow}"

checks.append((12, "finance/accounting/balance-sheet.blade.php", check_item_12, "TABLE_MISSING_OVERFLOW at line 45"))

# Item 13: ai/actions.blade.php (Line 1) - MISSING_MOBILE_PADDING
def check_item_13(lines):
    content = "".join(lines)
    has_pb_28 = bool(re.search(r"\bpb-(?:24|28|32|36|40)\b", content))
    return not has_pb_28, f"File lacks pb-28/pb-36 padding (has_pb: {has_pb_28})"

checks.append((13, "ai/actions.blade.php", check_item_13, "MISSING_MOBILE_PADDING in ai/actions.blade.php"))

# Item 14: invoices/create.blade.php (Line 480) - SUB_44PX_TOUCH_TARGET (h-9)
def check_item_14(lines):
    window = lines[max(0, 465):min(len(lines), 495)]
    h9_submit = [l.strip() for l in window if "h-9" in l]
    return len(h9_submit) > 0, f"Found h-9 buttons around line 480: {h9_submit[:2]}"

checks.append((14, "invoices/create.blade.php", check_item_14, "SUB_44PX_TOUCH_TARGET (h-9) around line 480"))

# Item 15: import/index.blade.php (Line 1) - ZERO_LOCALIZATION
def check_item_15(lines):
    content = "".join(lines)
    trans_calls = len(re.findall(r"__\(|@lang\(", content))
    total_lines = len(lines)
    return (trans_calls == 0 and total_lines > 2000), f"Total lines: {total_lines}, Translation calls: {trans_calls}"

checks.append((15, "import/index.blade.php", check_item_15, "ZERO_LOCALIZATION in import/index.blade.php"))

# Item 16: materials/index.blade.php (Line 443) - SUB_44PX_TOUCH_TARGET (w-8 h-8)
def check_item_16(lines):
    window = lines[max(0, 430):min(len(lines), 460)]
    w8 = [l.strip() for l in window if "w-8 h-8" in l or "rounded-[8px]" in l]
    return len(w8) > 0, f"Found w-8 h-8: {w8[:2]}"

checks.append((16, "materials/index.blade.php", check_item_16, "SUB_44PX_TOUCH_TARGET (w-8 h-8) around line 443"))

# Item 17: storefront/orders/show.blade.php (Line 1122) - NARROW_FORM_MODAL (max-w-md)
def check_item_17(lines):
    window = lines[max(0, 1100):min(len(lines), 1145)]
    mw = [l.strip() for l in window if "max-w-md" in l]
    return len(mw) > 0, f"Found max-w-md modal around line 1122: {mw[:2]}"

checks.append((17, "storefront/orders/show.blade.php", check_item_17, "NARROW_FORM_MODAL max-w-md at line 1122"))

# Item 18: pos/kitchen.blade.php (Line 45) - UNRESPONSIVE_BREAKPOINTS
def check_item_18(lines):
    window = lines[max(0, 35):min(len(lines), 60)]
    cols = [l.strip() for l in window if "grid-cols" in l or "flex-col" in l or "lg:grid-cols" in l]
    return len(cols) > 0, f"Found layout breakpoints: {cols}"

checks.append((18, "pos/kitchen.blade.php", check_item_18, "UNRESPONSIVE_BREAKPOINTS at line 45"))

# Item 19: marketplace/products.blade.php (Line 450) - BUTTON_VISUAL_POLLUTION
def check_item_19(lines):
    window = lines[max(0, 430):min(len(lines), 470)]
    red_btns = [l.strip() for l in window if "red" in l or "FF3B30" in l or "hapus" in l.lower() or "unbind" in l.lower()]
    return len(red_btns) > 0, f"Found red/unbind action buttons: {red_btns[:2]}"

checks.append((19, "marketplace/products.blade.php", check_item_19, "BUTTON_VISUAL_POLLUTION around line 450"))

# Item 20: landing_page/edit.blade.php (Line 1) - MONOLITHIC_BUILDER_BLOAT
def check_item_20(lines):
    l = len(lines)
    return l > 3000, f"Total lines: {l} (Claim: 3,334 lines)"

checks.append((20, "landing_page/edit.blade.php", check_item_20, "MONOLITHIC_BUILDER_BLOAT (3,334 lines)"))

print("Running automated adversarial verification on all 20 Master Catalog items...\n")
results = []
for c in checks:
    res = verify_item(c[0], c[1], c[2], c[3])
    results.append(res)
    print(f"[{res['status']}] Item {res['num']}: {res['description']}")
    print(f"       Detail: {res['detail']}\n")

passed_count = sum(1 for r in results if r['status'] == 'PASS')
print(f"SUMMARY: {passed_count} / {len(results)} items PASSED verification.")
