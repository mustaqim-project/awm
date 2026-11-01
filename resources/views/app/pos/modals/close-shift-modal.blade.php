<!-- ===================================================== -->
    <!-- 7. MODAL: CLOSE SHIFT & RECONCILIATION (Apple Sheet)  -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showCloseShiftModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showCloseShiftModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showCloseShiftModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showCloseShiftModal = false"></div>

            <!-- Centered Dialog Wrapper -->
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
                </div>
        </div>
    </template>
