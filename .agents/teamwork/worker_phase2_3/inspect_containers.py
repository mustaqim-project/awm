import os

files = [
    'resources/views/app/ai/actions.blade.php',
    'resources/views/app/ai/history.blade.php',
    'resources/views/app/ai/index.blade.php',
    'resources/views/app/ai/lobby.blade.php',
    'resources/views/app/ai/office.blade.php',
    'resources/views/app/ai/providers.blade.php',
    'resources/views/app/ai/offices/executive.blade.php',
    'resources/views/app/ai/offices/growth.blade.php',
    'resources/views/app/ai/offices/operations.blade.php',
    'resources/views/app/community/events.blade.php',
    'resources/views/app/community/event_payment.blade.php',
    'resources/views/app/community/index.blade.php',
    'resources/views/app/crm/vouchers.blade.php',
    'resources/views/app/feedback/create.blade.php',
    'resources/views/app/feedback/index.blade.php',
    'resources/views/app/feedback/show.blade.php',
    'resources/views/app/finance/accounting/balance-sheet.blade.php',
    'resources/views/app/finance/accounting/coa.blade.php',
    'resources/views/app/finance/accounting/general-ledger.blade.php',
    'resources/views/app/finance/accounting/trial-balance.blade.php',
    'resources/views/app/finance/cash-bank/index.blade.php',
    'resources/views/app/finance/external-reconciliation/index.blade.php',
    'resources/views/app/landing_page/edit.blade.php',
    'resources/views/app/landing_page/popup.blade.php',
    'resources/views/app/portal/index.blade.php',
    'resources/views/app/quotations/index.blade.php',
    'resources/views/app/social_media/posts.blade.php',
    'resources/views/app/pos/kitchen.blade.php',
    'resources/views/app/pos/orders.blade.php',
    'resources/views/app/pos/reports.blade.php',
    'resources/views/app/pos/shifts.blade.php',
    'resources/views/app/sales-orders/index.blade.php'
]

for f in files:
    full = os.path.join(r'c:\laragon\www\cooca_core', f.replace('/', os.sep))
    if os.path.exists(full):
        lines = open(full, encoding='utf-8', errors='ignore').readlines()[:30]
        content_idx = -1
        for i, l in enumerate(lines):
            if "@section('content')" in l or '@section("content")' in l:
                content_idx = i
                break
        print(f"FILE: {f}")
        start = content_idx if content_idx != -1 else 0
        for j in range(start, min(start + 5, len(lines))):
            print(f"  [{j+1}] {lines[j].rstrip()}")
    else:
        print(f"NOT FOUND: {f}")
