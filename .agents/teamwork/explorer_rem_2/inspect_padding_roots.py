import os
import re

base = r"c:\laragon\www\cooca_core\resources\views\app"

views_list = [
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

results = []
for rel in views_list:
    p = os.path.join(base, rel.replace('/', os.sep))
    if not os.path.exists(p):
        results.append((rel, "FILE_NOT_FOUND", ""))
        continue
    with open(p, 'r', encoding='utf-8', errors='ignore') as fp:
        lines = fp.readlines()
    
    # find @section('content') or first root div
    root_line = None
    root_class = ""
    for idx, l in enumerate(lines, 1):
        if "@section('content')" in l:
            # find first <div following
            for j in range(idx, min(len(lines), idx+10)):
                m = re.search(r'<div\s+([^>]*class="([^"]*)")', lines[j])
                if m:
                    root_line = j + 1
                    root_class = m.group(2)
                    break
            break
        elif root_line is None:
            m = re.search(r'<div\s+([^>]*class="([^"]*)")', l)
            if m and not root_class:
                root_line = idx
                root_class = m.group(2)
    
    results.append((rel, root_line, root_class))

for rel, rline, rcls in results:
    print(f"{rel} | Line {rline} | Current class: {rcls}")
