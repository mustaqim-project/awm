import os, re

root = r'c:\laragon\www\cooca_core'

# 1. pos/reports/tabs/transactions.blade.php
f1 = os.path.join(root, r'resources\views\app\pos\reports\tabs\transactions.blade.php')
c1 = open(f1, 'r', encoding='utf-8').read()
c1 = c1.replace(
    '🚗 {{ $order->vehicle_license_plate }}',
    '<i data-lucide="car" class="w-3.5 h-3.5 text-blue-500 shrink-0 inline-block mr-1"></i>{{ $order->vehicle_license_plate }}'
)
c1 = c1.replace(
    '🧺 {{ $order->laundry_weight_kg }} kg',
    '<i data-lucide="shirt" class="w-3.5 h-3.5 shrink-0 inline-block mr-1"></i>{{ $order->laundry_weight_kg }} kg'
)
c1 = c1.replace(
    '💊 {{ __(\'pos.reports_meta_batch\') }}',
    '<i data-lucide="pill" class="w-3.5 h-3.5 shrink-0 inline-block mr-1"></i>{{ __(\'pos.reports_meta_batch\') }}'
)
c1 = re.sub(
    r'🏷️?\s*\{\{\s*__\(\x27pos\.reports_meta_imei\x27\)\s*\}\}',
    r'<i data-lucide="tag" class="w-3.5 h-3.5 shrink-0 inline-block mr-1"></i>{{ __(\'pos.reports_meta_imei\') }}',
    c1
)
open(f1, 'w', encoding='utf-8').write(c1)
print("[OK] transactions.blade.php")

# 2. pos/reports/partials/order_detail_modal.blade.php
f2 = os.path.join(root, r'resources\views\app\pos\reports\partials\order_detail_modal.blade.php')
c2 = open(f2, 'r', encoding='utf-8').read()
c2 = re.sub(
    r'<div class="text-\[10px\] text-\[#007AFF\] font-medium mt-0\.5" x-text="\'💊\s*\{\{\s*__\(\x27pos\.reports_modal_dosage_prefix\x27\)\s*\}\}:\s*\'\s*\+\s*item\.dosage_instructions"></div>',
    r'<div class="text-[10px] text-[#007AFF] font-medium mt-0.5 inline-flex items-center gap-1"><i data-lucide="pill" class="w-3 h-3 shrink-0"></i><span x-text="\'{{ __(\'pos.reports_modal_dosage_prefix\') }}: \' + item.dosage_instructions"></span></div>',
    c2
)
open(f2, 'w', encoding='utf-8').write(c2)
print("[OK] order_detail_modal.blade.php")

# 3. pos/reports/print_summary.blade.php
f3 = os.path.join(root, r'resources\views\app\pos\reports\print_summary.blade.php')
c3 = open(f3, 'r', encoding='utf-8').read()
c3 = c3.replace("'🚗 '", "'[NOPOL] '")
c3 = c3.replace("'🧺 '", "'[LAUNDRY] '")
c3 = c3.replace("'📝 '", "'[CATATAN] '")
open(f3, 'w', encoding='utf-8').write(c3)
print("[OK] print_summary.blade.php")

# 4. social_media/inbox.blade.php
f4 = os.path.join(root, r'resources\views\app\social_media\inbox.blade.php')
c4 = open(f4, 'r', encoding='utf-8').read()
c4 = c4.replace("replyText += ' 😊'", "replyText += ' :)'")
c4 = c4.replace("hari ini? 😊", "hari ini?")
c4 = c4.replace("langsung ya Kak! 🙏", "langsung ya Kak!")
c4 = c4.replace("di toko kami! ✨", "di toko kami!")
open(f4, 'w', encoding='utf-8').write(c4)
print("[OK] inbox.blade.php")

# 5. community/index.blade.php
f5 = os.path.join(root, r'resources\views\app\community\index.blade.php')
c5 = open(f5, 'r', encoding='utf-8').read()
c5 = c5.replace(
    'title="{{ __(\'community.active_customer_cooca\') }}">\n                                        ★\n                                    </span>',
    'title="{{ __(\'community.active_customer_cooca\') }}"><svg class="w-2.5 h-2.5 fill-current text-slate-900" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span>'
)
open(f5, 'w', encoding='utf-8').write(c5)
print("[OK] community/index.blade.php")

# 6. ai/partials/virtual_office_canvas.blade.php
f6 = os.path.join(root, r'resources\views\app\ai\partials\virtual_office_canvas.blade.php')
c6 = open(f6, 'r', encoding='utf-8').read()
c6 = c6.replace('<text x="67" y="46" fill="#ffffff" font-size="6.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">● Leading</text>',
                '<circle cx="53" cy="44" r="1.5" fill="#ffffff" /><text x="69" y="46" fill="#ffffff" font-size="6.5" font-family="system-ui, sans-serif" font-weight="bold" text-anchor="middle">Leading</text>')
c6 = c6.replace("{ icon: '⭐', label: 'Retensi Pelanggan'", "{ icon: 'star', label: 'Retensi Pelanggan'")
c6 = c6.replace("{ icon: '⭐', label: 'Upselling'", "{ icon: 'star', label: 'Upselling'")
c6 = c6.replace("{ icon: '⭐', label: 'Loyalty Program'", "{ icon: 'star', label: 'Loyalty Program'")
open(f6, 'w', encoding='utf-8').write(c6)
print("[OK] virtual_office_canvas.blade.php")

# 7. ai/partials/agent_inspector_hud.blade.php
f7 = os.path.join(root, r'resources\views\app\ai\partials\agent_inspector_hud.blade.php')
c7 = open(f7, 'r', encoding='utf-8').read()
c7 = c7.replace("x-text=\"'● ' + selectedAgentStatus\"></span>",
                "class=\"inline-flex items-center gap-1\"><span class=\"w-1.5 h-1.5 rounded-full shrink-0\" :class=\"selectedAgentStatus === 'WORKING' ? 'bg-emerald-500' : (selectedAgentStatus === 'WAITING_APPROVAL' ? 'bg-amber-500' : 'bg-blue-500')\"></span><span x-text=\"selectedAgentStatus\"></span></span>")
open(f7, 'w', encoding='utf-8').write(c7)
print("[OK] agent_inspector_hud.blade.php")

# 8. landing_page/edit.blade.php
f8 = os.path.join(root, r'resources\views\app\landing_page\edit.blade.php')
c8 = open(f8, 'r', encoding='utf-8').read()
c8 = c8.replace('<option value="star">⭐ Star</option>', '<option value="star">Bintang (Star)</option>')
open(f8, 'w', encoding='utf-8').write(c8)
print("[OK] landing_page/edit.blade.php")

# 9. master-data/index.blade.php
f9 = os.path.join(root, r'resources\views\app\master-data\index.blade.php')
c9 = open(f9, 'r', encoding='utf-8').read()
c9 = c9.replace('class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">✕</button>',
                'class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition cursor-pointer" aria-label="{{ __(\'common.close\') }}"><i data-lucide="x" class="w-4 h-4"></i></button>')
c9 = c9.replace('class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition cursor-pointer">✕</button>',
                'class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition cursor-pointer" aria-label="{{ __(\'common.close\') }}"><i data-lucide="x" class="w-4 h-4"></i></button>')
c9 = re.sub(r'class="w-5 h-5 rounded-full text-black/40 hover:text-\[#FF3B30\] hover:bg-black/5 flex items-center justify-center text-\[11px\] cursor-pointer">\s*✕\s*</button>',
            r'class="w-5 h-5 rounded-full text-black/40 hover:text-[#FF3B30] hover:bg-black/5 flex items-center justify-center text-[11px] cursor-pointer" aria-label="{{ __(\'common.delete\') }}"><i data-lucide="x" class="w-3 h-3"></i></button>',
            c9)
open(f9, 'w', encoding='utf-8').write(c9)
print("[OK] master-data/index.blade.php")

# 10. billing/invoice.blade.php
f10 = os.path.join(root, r'resources\views\app\billing\invoice.blade.php')
c10 = open(f10, 'r', encoding='utf-8').read()
c10 = c10.replace(
    '✓ {{ __(\'billing.stamp_paid_verified\') }}',
    '<i data-lucide="check" class="w-3.5 h-3.5 inline-block mr-1"></i>{{ __(\'billing.stamp_paid_verified\') }}'
)
open(f10, 'w', encoding='utf-8').write(c10)
print("[OK] invoice.blade.php")

# 11. sales/returns/show.blade.php
f11 = os.path.join(root, r'resources\views\app\sales\returns\show.blade.php')
c11 = open(f11, 'r', encoding='utf-8').read()
c11 = re.sub(
    r'@if \(in_array\(\$return->status, \[\x27approved\x27, \x27completed\x27\]\)\)\s*✓\s*@else',
    r'@if (in_array($return->status, [\'approved\', \'completed\']))<i data-lucide="check" class="w-3.5 h-3.5"></i>@else',
    c11
)
c11 = re.sub(
    r'@if \(\$return->status === \x27completed\x27\)\s*✓\s*@else\s*2\s*@endif',
    r'@if ($return->status === \'completed\')<i data-lucide="check" class="w-3.5 h-3.5"></i>@else 2 @endif',
    c11
)
c11 = re.sub(
    r'@if \(\$return->status === \x27completed\x27\)\s*✓\s*@else\s*3\s*@endif',
    r'@if ($return->status === \'completed\')<i data-lucide="check" class="w-3.5 h-3.5"></i>@else 3 @endif',
    c11
)
c11 = c11.replace('<span>✓</span> {{ __(\'sales.returns.status_restocked_success\') }}',
                  '<i data-lucide="check" class="w-3.5 h-3.5 inline-block mr-1"></i>{{ __(\'sales.returns.status_restocked_success\') }}')
open(f11, 'w', encoding='utf-8').write(c11)
print("[OK] sales/returns/show.blade.php")

# 12. mcp/oauth/consent.blade.php
f12 = os.path.join(root, r'resources\views\app\mcp\oauth\consent.blade.php')
c12 = open(f12, 'r', encoding='utf-8').read()
c12 = re.sub(
    r'(<span class="w-5 h-5 rounded-full [^"]+ flex items-center justify-center shrink-0 mt-0\.5">)✓(</span>)',
    r'\1<i data-lucide="check" class="w-3.5 h-3.5"></i>\2',
    c12
)
open(f12, 'w', encoding='utf-8').write(c12)
print("[OK] consent.blade.php")

# 13. social_media/posts.blade.php
f13 = os.path.join(root, r'resources\views\app\social_media\posts.blade.php')
c13 = open(f13, 'r', encoding='utf-8').read()
c13 = c13.replace('// ── Post Preview & Manager Modal ──', '// -- Post Preview & Manager Modal --')
open(f13, 'w', encoding='utf-8').write(c13)
print("[OK] posts.blade.php")
