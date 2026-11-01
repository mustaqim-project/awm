import os, re

grid_updates = [
    # 1. ai/index.blade.php
    ('resources/views/app/ai/index.blade.php', 
     r'<div class="grid grid-cols-3 gap-2 text-\[11px\] bg-slate-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-slate-100 dark:border-white/5">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] bg-slate-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-slate-100 dark:border-white/5">'),
    ('resources/views/app/ai/index.blade.php',
     r'<div class="grid grid-cols-3 gap-2 text-center text-xs">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center text-xs">'),

    # 2. ai/lobby.blade.php
    ('resources/views/app/ai/lobby.blade.php',
     r'<div class="grid grid-cols-3 gap-2 pt-1 border-t border-black/5 dark:border-white/5 text-center">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 border-t border-black/5 dark:border-white/5 text-center">'),

    # 3. ai/office.blade.php
    ('resources/views/app/ai/office.blade.php',
     r'<div class="grid grid-cols-3 gap-2 border-t border-black/5 dark:border-white/5 pt-3">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 border-t border-black/5 dark:border-white/5 pt-3">'),

    # 4. ai/partials/virtual_office_canvas.blade.php
    ('resources/views/app/ai/partials/virtual_office_canvas.blade.php',
     r'<div class="grid grid-cols-4 gap-2 text-center">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">'),

    # 5. ai-studio/create.blade.php
    ('resources/views/app/ai-studio/create.blade.php',
     r'<div class="grid grid-cols-4 gap-2">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 gap-2">'),

    # 6. analytics/index.blade.php
    ('resources/views/app/analytics/index.blade.php',
     r'<div class="grid grid-cols-3 gap-2 text-\[11px\] pt-1 border-t border-black/5 dark:border-white/5">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] pt-1 border-t border-black/5 dark:border-white/5">'),

    # 7. billing/invoice.blade.php
    ('resources/views/app/billing/invoice.blade.php',
     r'<div class="grid grid-cols-3 sm:flex sm:items-center gap-2 sm:gap-2\.5 w-full sm:w-auto shrink-0">',
     r'<div class="grid grid-cols-1 sm:flex sm:items-center gap-2 sm:gap-2.5 w-full sm:w-auto shrink-0">'),

    # 8. billing/limits.blade.php
    ('resources/views/app/billing/limits.blade.php',
     r'<div class="grid grid-cols-3 gap-1 py-2 px-2\.5 rounded-\[12px\] bg-slate-50',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-slate-50'),
    ('resources/views/app/billing/limits.blade.php',
     r'<div class="grid grid-cols-3 gap-1 py-2 px-2\.5 rounded-\[12px\] bg-emerald-50',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-emerald-50'),
    ('resources/views/app/billing/limits.blade.php',
     r'<div class="grid grid-cols-3 gap-1 py-2 px-2\.5 rounded-\[12px\] bg-indigo-50',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-indigo-50'),
    ('resources/views/app/billing/limits.blade.php',
     r'<div class="grid grid-cols-3 gap-1 py-2 px-2\.5 rounded-\[12px\] bg-purple-50',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-purple-50'),

    # 9. crm/members.blade.php
    ('resources/views/app/crm/members.blade.php',
     r'<div class="grid grid-cols-3 gap-2 text-center">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">'),
    ('resources/views/app/crm/members.blade.php',
     r'<div class="grid grid-cols-3 gap-2\.5">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">'),

    # 10. labor-machines/index.blade.php (all 4 occurrences of <div class="grid grid-cols-3 gap-2">)
    ('resources/views/app/labor-machines/index.blade.php',
     r'<div class="grid grid-cols-3 gap-2">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2">'),

    # 11. marketplace/orders.blade.php
    ('resources/views/app/marketplace/orders.blade.php',
     r'<div class="grid grid-cols-3 gap-2 p-1\.5 rounded-\[16px\] bg-black/\[0\.04\] dark:bg-white/\[0\.06\]">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">'),

    # 12. marketplace/products.blade.php
    ('resources/views/app/marketplace/products.blade.php',
     r'<div class="grid grid-cols-3 gap-2 p-1\.5 rounded-\[16px\] bg-black/\[0\.04\] dark:bg-white/\[0\.06\]">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">'),

    # 13. pos/terminal.blade.php
    ('resources/views/app/pos/terminal.blade.php',
     r'<div class="grid grid-cols-3 gap-2 pt-1\.5">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1.5">'),
    ('resources/views/app/pos/terminal.blade.php',
     r'class="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-black/\[0\.03\] dark:bg-white/\[0\.04\] border border-black/5 dark:border-white/5 text-center">',
     r'class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-3 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">'),
    ('resources/views/app/pos/terminal.blade.php',
     r'<div class="grid grid-cols-4 gap-2">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 gap-2">'),

    # 14. pos/reports/partials/order_detail_modal.blade.php
    ('resources/views/app/pos/reports/partials/order_detail_modal.blade.php',
     r'<div class="grid grid-cols-3 gap-3">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">'),

    # 15. products/index.blade.php
    ('resources/views/app/products/index.blade.php',
     r'<div class="grid grid-cols-4 sm:grid-cols-5 gap-2">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-2">'),

    # 16. sales/returns/print.blade.php
    ('resources/views/app/sales/returns/print.blade.php',
     r'<div class="grid grid-cols-3 gap-6 text-center text-xs">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 print:grid-cols-3 gap-6 text-center text-xs">'),

    # 17. sales/returns/show.blade.php
    ('resources/views/app/sales/returns/show.blade.php',
     r'<div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">',
     r'<div class="hidden print:grid print:grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">'),

    # 18. sales-orders/show.blade.php
    ('resources/views/app/sales-orders/show.blade.php',
     r'<div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">',
     r'<div class="hidden print:grid print:grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">'),

    # 19. social_media/calendar.blade.php
    ('resources/views/app/social_media/calendar.blade.php',
     r'<div class="grid grid-cols-7 border-b border-black/5 dark:border-white/10 bg-black/\[0\.02\] dark:bg-white/\[0\.02\] text-center text-\[12px\] font-semibold text-black/60 dark:text-white/60">',
     r'<div class="grid sm:grid-cols-7 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-center text-[12px] font-semibold text-black/60 dark:text-white/60">'),
    ('resources/views/app/social_media/calendar.blade.php',
     r'<div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-black/5 dark:border-white/10">',
     r'<div class="grid sm:grid-cols-7 auto-rows-fr divide-x divide-y divide-black/5 dark:border-white/10">'),

    # 20. social_media/insights.blade.php
    ('resources/views/app/social_media/insights.blade.php',
     r'<div class="pt-2 border-t border-black/\[0\.04\] dark:border-white/\[0\.06\] grid grid-cols-4 gap-1 text-center text-\[10px\]">',
     r'<div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] grid grid-cols-2 sm:grid-cols-4 gap-1 text-center text-[10px]">'),
    ('resources/views/app/social_media/insights.blade.php',
     r'<div class="grid grid-cols-4 gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-center text-\[10px\]">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-center text-[10px]">'),

    # 21. social_media/posts.blade.php
    ('resources/views/app/social_media/posts.blade.php',
     r'<div class="grid grid-cols-4 gap-1 p-1 rounded-\[12px\] bg-black/\[0\.04\] dark:bg-white/\[0\.06\] text-\[11px\] font-bold">',
     r'<div class="grid grid-cols-2 sm:grid-cols-4 gap-1 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-bold">'),

    # 22. storefront/orders/shipping_label.blade.php
    ('resources/views/app/storefront/orders/shipping_label.blade.php',
     r'<div class="grid grid-cols-3 border-b-2 border-black py-1\.5 px-2 text-\[10\.5px\] font-bold items-center">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 print:grid-cols-3 border-b-2 border-black py-1.5 px-2 text-[10.5px] font-bold items-center">'),

    # 23. warehouse/index.blade.php
    ('resources/views/app/warehouse/index.blade.php',
     r'<div class="grid grid-cols-3 gap-2 text-center">',
     r'<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">')
]

root = r'c:\laragon\www\cooca_core'
for rel, pat, repl in grid_updates:
    full = os.path.join(root, rel.replace('/', os.sep))
    content = open(full, 'r', encoding='utf-8').read()
    # Note: for labor-machines there are 4 identical tags, so replace all or count=0
    count = 0 if rel == 'resources/views/app/labor-machines/index.blade.php' or rel == 'resources/views/app/ai/lobby.blade.php' else 1
    new_content, n = re.subn(pat, repl, content, count=count)
    if n > 0:
        with open(full, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"[OK] {rel} ({n} replacements)")
    else:
        print(f"[FAILED] {rel}: pattern {pat} not matched")
