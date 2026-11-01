import os, re

updates = [
    ('resources/views/app/ai/actions.blade.php', r'<div class="space-y-6" x-data="actionCenterApp\(\)">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="actionCenterApp()">'),
    ('resources/views/app/ai/history.blade.php', r'<div class="space-y-6">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10">'),
    ('resources/views/app/ai/index.blade.php', r'<div class="space-y-8" x-data="aiPosApp\(\)">', r'<div class="space-y-8 pb-28 sm:pb-32 lg:pb-10" x-data="aiPosApp()">'),
    ('resources/views/app/ai/lobby.blade.php', r'<div class="space-y-6 w-full" x-data="aiOfficeBase\(\)">', r'<div class="space-y-6 w-full pb-28 sm:pb-32 lg:pb-10" x-data="aiOfficeBase()">'),
    ('resources/views/app/ai/office.blade.php', r'<div class="space-y-6" x-data="aiOfficeApp\(\)">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="aiOfficeApp()">'),
    ('resources/views/app/ai/providers.blade.php', r'<div class="space-y-6" x-data="aiProvidersApp\(\)">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="aiProvidersApp()">'),
    ('resources/views/app/ai/offices/executive.blade.php', r'<div class="space-y-6 w-full" x-data="aiOfficeBase\(\)">', r'<div class="space-y-6 w-full pb-28 sm:pb-32 lg:pb-10" x-data="aiOfficeBase()">'),
    ('resources/views/app/ai/offices/growth.blade.php', r'<div class="space-y-6 w-full" x-data="aiOfficeBase\(\)">', r'<div class="space-y-6 w-full pb-28 sm:pb-32 lg:pb-10" x-data="aiOfficeBase()">'),
    ('resources/views/app/ai/offices/operations.blade.php', r'<div class="space-y-6 w-full" x-data="aiOfficeBase\(\)">', r'<div class="space-y-6 w-full pb-28 sm:pb-32 lg:pb-10" x-data="aiOfficeBase()">'),
    ('resources/views/app/community/events.blade.php', r'<div class="space-y-6 pb-16"', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/community/event_payment.blade.php', r'<div class="max-w-5xl mx-auto pb-20 px-3 sm:px-6"', r'<div class="max-w-5xl mx-auto pb-28 sm:pb-32 lg:pb-10 px-3 sm:px-6"'),
    ('resources/views/app/community/index.blade.php', r'<div class="max-w-7xl mx-auto pb-16 px-2 sm:px-4 lg:px-6"', r'<div class="max-w-7xl mx-auto pb-28 sm:pb-32 lg:pb-10 px-2 sm:px-4 lg:px-6"'),
    ('resources/views/app/crm/vouchers.blade.php', r'<div class="space-y-6 pb-16"', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/feedback/create.blade.php', r'<div class="space-y-6 pb-16">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10">'),
    ('resources/views/app/feedback/index.blade.php', r'<div class="space-y-6 pb-16">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10">'),
    ('resources/views/app/feedback/show.blade.php', r'<div class="space-y-6 pb-16">', r'<div class="space-y-6 pb-28 sm:pb-32 lg:pb-10">'),
    ('resources/views/app/finance/accounting/balance-sheet.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-5 pb-28 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/finance/accounting/coa.blade.php', r'<div x-data="coaManager\(\)" class="max-w-\[1360px\] mx-auto space-y-5 pb-28 lg:pb-12"', r'<div x-data="coaManager()" class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/finance/accounting/general-ledger.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-5 pb-28 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/finance/accounting/trial-balance.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-5 pb-28 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/finance/cash-bank/index.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-5 pb-28 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/finance/external-reconciliation/index.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-28 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/landing_page/popup.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/portal/index.blade.php', r'<div class="max-w-\[1360px\] mx-auto px-4 sm:px-6 py-6 sm:py-8 pb-28 sm:pb-32 space-y-6"', r'<div class="max-w-[1360px] mx-auto px-4 sm:px-6 py-6 sm:py-8 pb-28 sm:pb-32 lg:pb-10 space-y-6"'),
    ('resources/views/app/quotations/index.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/social_media/posts.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-36 lg:pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/pos/kitchen.blade.php', r'<div class="max-w-\[1600px\] mx-auto space-y-5 pb-16"', r'<div class="max-w-[1600px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/pos/orders.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/pos/reports.blade.php', r'<div class="max-w-\[1440px\] mx-auto space-y-6 pb-16">', r'<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10">'),
    ('resources/views/app/pos/shifts.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"'),
    ('resources/views/app/sales-orders/index.blade.php', r'<div class="max-w-\[1360px\] mx-auto space-y-6 pb-12"', r'<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10"')
]

root = r'c:\laragon\www\cooca_core'
success_count = 0

for file_rel, pattern, repl in updates:
    filepath = os.path.join(root, file_rel.replace('/', os.sep))
    if not os.path.exists(filepath):
        print(f"File not found: {file_rel}")
        continue
    content = open(filepath, 'r', encoding='utf-8').read()
    new_content, count = re.subn(pattern, repl, content, count=1)
    if count > 0:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        success_count += 1
        print(f"[OK] Updated {file_rel}")
    else:
        print(f"[FAILED] Pattern not matched in {file_rel}")

print(f"Total updated: {success_count} / {len(updates)}")
