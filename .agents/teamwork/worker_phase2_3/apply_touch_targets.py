import os, re

root = r'c:\laragon\www\cooca_core'

# 1. products/index.blade.php
f_p = os.path.join(root, r'resources\views\app\products\index.blade.php')
c_p = open(f_p, 'r', encoding='utf-8').read()

# Replace all occurrences of h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]
# with min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:h-7 px-2.5 rounded-[8px] border text-[11px] font-medium flex items-center justify-center cursor-pointer
old_toggle = 'class="h-7 px-2.5 rounded-[6px] border text-[11px] font-medium min-w-[44px]"'
new_toggle = 'class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:h-7 px-2.5 rounded-[8px] border text-[11px] font-medium flex items-center justify-center cursor-pointer"'

c_p_new, count_p = re.subn(re.escape(old_toggle), new_toggle, c_p)
if count_p > 0:
    open(f_p, 'w', encoding='utf-8').write(c_p_new)
    print(f"[OK] products/index.blade.php ({count_p} toggles updated)")
else:
    print("[FAIL] products/index.blade.php toggle pattern not matched")

# 2. invoices/create.blade.php
f_ic = os.path.join(root, r'resources\views\app\invoices\create.blade.php')
c_ic = open(f_ic, 'r', encoding='utf-8').read()
c_ic = c_ic.replace(
    'class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center"',
    'class="min-h-[44px] h-11 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center"'
)
c_ic = c_ic.replace(
    'class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"',
    'class="min-h-[44px] h-11 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer"'
)
open(f_ic, 'w', encoding='utf-8').write(c_ic)
print("[OK] invoices/create.blade.php updated")

# 3. quotations/create.blade.php
f_qc = os.path.join(root, r'resources\views\app\quotations\create.blade.php')
c_qc = open(f_qc, 'r', encoding='utf-8').read()
c_qc = c_qc.replace(
    'class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center"',
    'class="min-h-[44px] h-11 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center"'
)
c_qc = c_qc.replace(
    'class="h-9 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"',
    'class="min-h-[44px] h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer"'
)
open(f_qc, 'w', encoding='utf-8').write(c_qc)
print("[OK] quotations/create.blade.php updated")
