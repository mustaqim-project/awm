import os
import re

base = r"c:\laragon\www\cooca_core\resources\views\app"

target_32 = [
    "ai/actions.blade.php",
    "ai/history.blade.php",
    "ai/index.blade.php",
    "ai/lobby.blade.php",
    "ai/office.blade.php",
    "ai/providers.blade.php",
    "ai/offices/executive.blade.php",
    "ai/offices/growth.blade.php",
    "ai/offices/operations.blade.php",
    "community/events.blade.php",
    "community/event_payment.blade.php",
    "community/index.blade.php",
    "crm/vouchers.blade.php",
    "feedback/create.blade.php",
    "feedback/index.blade.php",
    "feedback/show.blade.php",
    "finance/accounting/balance-sheet.blade.php",
    "finance/accounting/coa.blade.php",
    "finance/accounting/general-ledger.blade.php",
    "finance/accounting/trial-balance.blade.php",
    "finance/cash-bank/index.blade.php",
    "finance/external-reconciliation/index.blade.php",
    "landing_page/edit.blade.php",
    "landing_page/popup.blade.php",
    "portal/index.blade.php",
    "quotations/index.blade.php",
    "social_media/posts.blade.php",
    "pos/kitchen.blade.php",
    "pos/orders.blade.php",
    "pos/reports.blade.php",
    "pos/shifts.blade.php",
    "sales-orders/index.blade.php"
]

print("Index | File | Exists | Line | Current Root Class | Recommended Modification")
for i, rel in enumerate(target_32, 1):
    p = os.path.join(base, rel.replace('/', os.sep))
    if not os.path.exists(p):
        print(f"{i:2d} | {rel} | NOT FOUND")
        continue
    content = open(p, 'r', encoding='utf-8', errors='ignore').readlines()
    # find outermost container inside content
    in_content = False
    found = False
    for idx, line in enumerate(content, 1):
        if "@section('content')" in line:
            in_content = True
            continue
        if in_content:
            m = re.search(r'<div\s+([^>]*class="([^"]*)")', line)
            if m:
                cls = m.group(2)
                # check pb
                pb_m = re.search(r'\bpb-\d+\b', cls)
                cur_pb = pb_m.group(0) if pb_m else "None"
                print(f"{i:2d} | {rel} | Line {idx} | current pb: {cur_pb} | class: {cls[:80]}")
                found = True
                break
    if not found:
        print(f"{i:2d} | {rel} | Could not find root div after @section('content')")
