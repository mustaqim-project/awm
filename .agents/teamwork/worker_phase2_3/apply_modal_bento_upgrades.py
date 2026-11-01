import os, re

root = r'c:\laragon\www\cooca_core'

# 1. pos/terminal.blade.php
f_term = os.path.join(root, r'resources\views\app\pos\terminal.blade.php')
c_term = open(f_term, 'r', encoding='utf-8').read()

# Bento XXL 2-column Close Shift & Reconciliation Modal
old_close_shift = """            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showCloseShiftModal = false">
                <div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
                    <h3 class="font-semibold text-[17px] text-black dark:text-white">{{ __('pos.shift_close_modal_title') }}</h3>

            <div
                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('pos.opening_cash_colon') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.opening_cash)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('pos.cash_sales_colon') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.cash_sales)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('pos.non_cash_sales_colon') }}</span>
                    <span class="tabular-nums font-medium text-[#007AFF]"
                        x-text="formatRupiah(shiftSummary.non_cash_sales || 0)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('pos.cash_in_out_colon') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.cash_in - shiftSummary.cash_out)"></span>
                </div>
                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                    class="flex justify-between text-[#34C759] font-semibold border-t border-black/5 dark:border-white/5 pt-1.5 text-[13px]">
                    <span>{{ __('pos.expected_physical_cash_colon') }}</span>
                    <span class="tabular-nums font-bold" x-text="formatRupiah(shiftSummary.expected_cash)"></span>
                </div>
                <div x-show="shiftSummary.expected_cash === null || shiftSummary.expected_cash === undefined"
                    class="p-2.5 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[11px] text-[#007AFF] font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span>{{ __('pos.blind_count_mode_desc') }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.actual_cash_label') }}</label>
                    <input type="number" x-model.number="shiftActualCash"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                    class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex justify-between items-center text-xs">
                    <span class="text-black/60 dark:text-white/60">{{ __('pos.cash_difference_colon') }}</span>
                    <span
                        :class="(shiftActualCash - shiftSummary.expected_cash) === 0 ? 'text-[#34C759] font-bold' :
                            'text-[#FF3B30] font-bold'"
                        class="tabular-nums text-[13px]"
                        x-text="formatRupiah(shiftActualCash - shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showCloseShiftModal = false"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">{{ __('pos.cancel') }}</button>
                <button @click="submitCloseShift()"
                    class="h-9 px-5 rounded-[10px] bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] text-white font-semibold text-[13px] transition">{{ __('pos.close_reconcile_btn') }}</button>
                </div>
            </div>"""

new_close_shift = """            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCloseShiftModal = false">
                <div class="pos-modal-panel relative w-full max-w-full sm:max-w-[95vw] lg:max-w-4xl xl:max-w-5xl bg-white dark:bg-[#2C2C2E] rounded-t-[24px] sm:rounded-[20px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.3)] text-black dark:text-white">
                    <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="calculator" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-[18px] text-black dark:text-white">{{ __('pos.shift_close_modal_title') }}</h3>
                                <p class="text-xs text-black/50 dark:text-white/50">{{ __('pos.shift_close_modal_sub') ?? 'Rekonsiliasi Kas dan Penutupan Sesi Kasir' }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showCloseShiftModal = false" class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-full flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <!-- Left Bento Card: System Calculation & Expected Cash -->
                        <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-black/60 dark:text-white/60">{{ __('pos.shift_summary_title') ?? 'Ringkasan Kas Sistem' }}</h4>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('pos.opening_cash_colon') }}</span>
                                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.opening_cash)"></span>
                                </div>
                                <div class="flex justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('pos.cash_sales_colon') }}</span>
                                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.cash_sales)"></span>
                                </div>
                                <div class="flex justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('pos.non_cash_sales_colon') }}</span>
                                    <span class="tabular-nums font-medium text-[#007AFF]" x-text="formatRupiah(shiftSummary.non_cash_sales || 0)"></span>
                                </div>
                                <div class="flex justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('pos.cash_in_out_colon') }}</span>
                                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.cash_in - shiftSummary.cash_out)"></span>
                                </div>
                                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                                    class="flex justify-between text-[#34C759] font-semibold border-t border-black/5 dark:border-white/5 pt-2 text-[14px]">
                                    <span>{{ __('pos.expected_physical_cash_colon') }}</span>
                                    <span class="tabular-nums font-bold" x-text="formatRupiah(shiftSummary.expected_cash)"></span>
                                </div>
                                <div x-show="shiftSummary.expected_cash === null || shiftSummary.expected_cash === undefined"
                                    class="p-2.5 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[11px] text-[#007AFF] font-medium flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('pos.blind_count_mode_desc') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Bento Card: Actual Cash & Reconciliation Difference -->
                        <div class="p-4 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 space-y-4 flex flex-col justify-between">
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#007AFF]">{{ __('pos.physical_count_title') ?? 'Penghitungan Fisik Kasir' }}</h4>
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('pos.actual_cash_label') }}</label>
                                    <input type="number" x-model.number="shiftActualCash" placeholder="0"
                                        class="w-full h-12 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[18px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                </div>
                                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                                    class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 flex justify-between items-center text-xs">
                                    <span class="text-black/60 dark:text-white/60">{{ __('pos.cash_difference_colon') }}</span>
                                    <span
                                        :class="(shiftActualCash - shiftSummary.expected_cash) === 0 ? 'text-[#34C759] font-bold' : 'text-[#FF3B30] font-bold'"
                                        class="tabular-nums text-[14px]"
                                        x-text="formatRupiah(shiftActualCash - shiftSummary.expected_cash)"></span>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2.5 pt-3 border-t border-black/10 dark:border-white/10">
                                <button type="button" @click="showCloseShiftModal = false"
                                    class="min-h-[44px] h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition cursor-pointer">{{ __('pos.cancel') }}</button>
                                <button type="button" @click="submitCloseShift()"
                                    class="min-h-[44px] h-11 px-6 rounded-[12px] bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] text-white font-bold text-[13px] transition shadow-md shadow-[#FF3B30]/20 cursor-pointer">{{ __('pos.close_reconcile_btn') }}</button>
                            </div>
                        </div>
                    </div>
                </div>"""

if old_close_shift in c_term:
    c_term = c_term.replace(old_close_shift, new_close_shift)
    print("[OK] pos/terminal.blade.php Close Shift Bento XXL upgrade applied")
else:
    print("[FAIL] pos/terminal.blade.php Close Shift pattern not matched")

# Upgrade other cramped modal panels in pos/terminal.blade.php
c_term = c_term.replace(
    '<div class="pos-modal-panel relative w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">',
    '<div class="pos-modal-panel relative w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">'
)
c_term = c_term.replace(
    '<div class="pos-modal-panel relative w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl text-black dark:text-white">',
    '<div class="pos-modal-panel relative w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl text-black dark:text-white">'
)
c_term = c_term.replace(
    '<div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">',
    '<div class="pos-modal-panel relative w-full max-w-lg lg:max-w-xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">'
)
c_term = c_term.replace(
    '<div class="pos-modal-panel relative w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">',
    '<div class="pos-modal-panel relative w-full max-w-2xl lg:max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">'
)
c_term = c_term.replace(
    '<div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl text-black dark:text-white">',
    '<div class="pos-modal-panel relative w-full max-w-2xl lg:max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl text-black dark:text-white">'
)
open(f_term, 'w', encoding='utf-8').write(c_term)
print("[OK] pos/terminal.blade.php updated")

# 2. materials/index.blade.php
f_mat = os.path.join(root, r'resources\views\app\materials\index.blade.php')
c_mat = open(f_mat, 'r', encoding='utf-8').read()
c_mat = c_mat.replace(
    '<div class="relative w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl">',
    '<div class="relative w-full max-w-lg lg:max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl">'
)
c_mat = c_mat.replace(
    'class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50">',
    'class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50 cursor-pointer" aria-label="{{ __(\'common.close\') }}">'
)
open(f_mat, 'w', encoding='utf-8').write(c_mat)
print("[OK] materials/index.blade.php updated")

# 3. storefront/orders/show.blade.php
f_sto = os.path.join(root, r'resources\views\app\storefront\orders\show.blade.php')
c_sto = open(f_sto, 'r', encoding='utf-8').read()
c_sto = c_sto.replace(
    '<div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4 max-h-[92vh] overflow-y-auto"',
    '<div class="relative z-10 w-full max-w-lg lg:max-w-xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4 max-h-[92vh] overflow-y-auto"'
)
c_sto = c_sto.replace(
    '<div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"',
    '<div class="relative z-10 w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"'
)
c_sto = c_sto.replace(
    '<div class="relative z-10 w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"',
    '<div class="relative z-10 w-full max-w-xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"'
)
open(f_sto, 'w', encoding='utf-8').write(c_sto)
print("[OK] storefront/orders/show.blade.php updated")

# 4. storefront/settings.blade.php
f_sts = os.path.join(root, r'resources\views\app\storefront\settings.blade.php')
c_sts = open(f_sts, 'r', encoding='utf-8').read()
c_sts = c_sts.replace(
    '<div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"',
    '<div class="relative z-10 w-full max-w-lg lg:max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"'
)
c_sts = c_sts.replace(
    '<div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4 max-h-[92vh] overflow-y-auto"',
    '<div class="relative z-10 w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4 max-h-[92vh] overflow-y-auto"'
)
open(f_sts, 'w', encoding='utf-8').write(c_sts)
print("[OK] storefront/settings.blade.php updated")

# 5. storefront/reservations/index.blade.php
f_str = os.path.join(root, r'resources\views\app\storefront\reservations\index.blade.php')
c_str = open(f_str, 'r', encoding='utf-8').read()
c_str = c_str.replace(
    '<div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"',
    '<div class="relative z-10 w-full max-w-lg lg:max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"'
)
open(f_str, 'w', encoding='utf-8').write(c_str)
print("[OK] storefront/reservations/index.blade.php updated")

# 6. pos/orders.blade.php
f_po = os.path.join(root, r'resources\views\app\pos\orders.blade.php')
c_po = open(f_po, 'r', encoding='utf-8').read()
c_po = c_po.replace(
    '<div class="relative w-full sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-2xl"',
    '<div class="relative w-full sm:max-w-lg lg:max-w-xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-2xl"'
)
open(f_po, 'w', encoding='utf-8').write(c_po)
print("[OK] pos/orders.blade.php updated")

# 7. invoices/show.blade.php
f_inv = os.path.join(root, r'resources\views\app\invoices\show.blade.php')
c_inv = open(f_inv, 'r', encoding='utf-8').read()
c_inv = c_inv.replace(
    '<div class="relative w-full sm:max-w-md rounded-t-[20px] sm:rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"',
    '<div class="relative w-full max-w-full sm:max-w-[95vw] lg:max-w-3xl rounded-t-[24px] sm:rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"'
)
c_inv = c_inv.replace(
    '<div class="relative w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-6 space-y-4">',
    '<div class="relative w-full max-w-xl lg:max-w-3xl rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-6 space-y-4">'
)
open(f_inv, 'w', encoding='utf-8').write(c_inv)
print("[OK] invoices/show.blade.php updated")

# 8. inventory/stocks.blade.php
f_stk = os.path.join(root, r'resources\views\app\inventory\stocks.blade.php')
c_stk = open(f_stk, 'r', encoding='utf-8').read()
c_stk = c_stk.replace(
    '<div class="relative z-10 w-full sm:max-w-lg rounded-t-[26px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto"',
    '<div class="relative z-10 w-full sm:max-w-xl lg:max-w-2xl rounded-t-[26px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto"'
)
open(f_stk, 'w', encoding='utf-8').write(c_stk)
print("[OK] inventory/stocks.blade.php updated")

# 9. purchase-orders/index.blade.php
f_poi = os.path.join(root, r'resources\views\app\purchase-orders\index.blade.php')
c_poi = open(f_poi, 'r', encoding='utf-8').read()
c_poi = c_poi.replace(
    '<div class="relative w-full max-w-lg rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"',
    '<div class="relative w-full max-w-lg lg:max-w-2xl rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"'
)
open(f_poi, 'w', encoding='utf-8').write(c_poi)
print("[OK] purchase-orders/index.blade.php updated")

# 10. purchase-orders/show.blade.php
f_pos = os.path.join(root, r'resources\views\app\purchase-orders\show.blade.php')
c_pos = open(f_pos, 'r', encoding='utf-8').read()
c_pos = c_pos.replace(
    '<div class="relative w-full max-w-md rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"',
    '<div class="relative w-full max-w-lg lg:max-w-xl rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"'
)
open(f_pos, 'w', encoding='utf-8').write(c_pos)
print("[OK] purchase-orders/show.blade.php updated")

# 11. marketplace/index.blade.php & marketplace/orders.blade.php
f_mpi = os.path.join(root, r'resources\views\app\marketplace\index.blade.php')
c_mpi = open(f_mpi, 'r', encoding='utf-8').read()
c_mpi = c_mpi.replace(
    'class="relative w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"',
    'class="relative w-full max-w-lg lg:max-w-2xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"'
)
open(f_mpi, 'w', encoding='utf-8').write(c_mpi)
print("[OK] marketplace/index.blade.php updated")

f_mpo = os.path.join(root, r'resources\views\app\marketplace\orders.blade.php')
c_mpo = open(f_mpo, 'r', encoding='utf-8').read()
c_mpo = c_mpo.replace(
    'class="relative w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"',
    'class="relative w-full max-w-lg lg:max-w-2xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"'
)
open(f_mpo, 'w', encoding='utf-8').write(c_mpo)
print("[OK] marketplace/orders.blade.php updated")

# 12. products/index.blade.php
f_prd = os.path.join(root, r'resources\views\app\products\index.blade.php')
c_prd = open(f_prd, 'r', encoding='utf-8').read()
c_prd = c_prd.replace(
    '<div class="relative w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">',
    '<div class="relative w-full max-w-lg lg:max-w-xl rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">'
)
c_prd = c_prd.replace(
    '<div class="relative w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">',
    '<div class="relative w-full max-w-lg lg:max-w-2xl rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">'
)
c_prd = c_prd.replace(
    'class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">',
    'class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center cursor-pointer" aria-label="{{ __(\'common.close\') }}">'
)
open(f_prd, 'w', encoding='utf-8').write(c_prd)
print("[OK] products/index.blade.php updated")

# 13. warehouse/show.blade.php
f_whs = os.path.join(root, r'resources\views\app\warehouse\show.blade.php')
c_whs = open(f_whs, 'r', encoding='utf-8').read()
c_whs = c_whs.replace(
    '<div class="relative w-full max-w-md rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 space-y-4 overflow-hidden text-center"',
    '<div class="relative w-full max-w-lg lg:max-w-xl rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 space-y-4 overflow-hidden text-center"'
)
open(f_whs, 'w', encoding='utf-8').write(c_whs)
print("[OK] warehouse/show.blade.php updated")
