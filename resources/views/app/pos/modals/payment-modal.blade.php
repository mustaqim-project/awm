<!-- ===================================================== -->
    <!-- 4. MODAL: PAYMENT MODAL (Apple Sheet Presentation)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showPaymentModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="if(!isProcessing) showPaymentModal = false"
            @keydown.enter.prevent="if(!isProcessing && (isSplitPayment ? splitRemainingAmount === 0 : currentTenderAmount >= grandTotal)) submitCheckout()">
            <!-- Standalone Backdrop -->
            <div x-show="showPaymentModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="if(!isProcessing) showPaymentModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4" @click.self="if(!isProcessing) showPaymentModal = false">
                <div class="pos-modal-panel relative w-full max-w-3xl lg:max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col max-h-[92vh] overflow-y-auto space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white">
            
            {{-- Modal Header & Mode Switcher --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-[17px] text-black dark:text-white leading-tight">{{ __('pos.pos_checkout_payment_title') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('pos.pos_checkout_payment_subtitle') }}</p>
                    </div>
                </div>

                {{-- Mode Switcher (Apple Segmented Tab) --}}
                <div class="flex items-center p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/10 text-xs font-medium self-start sm:self-auto">
                    <button type="button" @click="isSplitPayment = false"
                        :class="!isSplitPayment ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        <span>{{ __('pos.single_payment_tab') }}</span>
                    </button>
                    <button type="button" @click="initSplitPayment()"
                        :class="isSplitPayment ? 'bg-[#007AFF] text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>{{ __('pos.split_payment_tab') }}</span>
                    </button>
                    <button type="button" @click="showPaymentModal = false"
                        class="ml-2 w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/50 dark:text-white/50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- 1. MODE STANDAR (SINGLE PAYMENT) --}}
            <div x-show="!isSplitPayment" class="space-y-4">
                <!-- Tagihan Summary Box -->
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.total_bill_label') }}</div>
                        <div class="text-[24px] sm:text-[28px] font-extrabold text-[#007AFF] tabular-nums tracking-tight"
                            x-text="formatRupiah(grandTotal)"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.received_amount_label') }}</div>
                        <div class="text-[20px] font-bold text-black dark:text-white tabular-nums"
                            x-text="formatRupiah(currentTenderAmount)"></div>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-2">{{ __('pos.select_payment_channel_label') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                        <button type="button" @click="selectedPayMethod = 'cash'"
                            :class="selectedPayMethod === 'cash' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                            <span>{{ __('pos.method_cash_label') }}</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'qris'; currentTenderAmount = grandTotal;"
                            :class="selectedPayMethod === 'qris' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                            <span>{{ __('pos.method_qris_cooca_pay') }}</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'edc_debit'"
                            :class="selectedPayMethod === 'edc_debit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            <span>{{ __('pos.method_edc_debit') }}</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'edc_credit'"
                            :class="selectedPayMethod === 'edc_credit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            <span>{{ __('pos.method_edc_credit') }}</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'transfer'"
                            :class="selectedPayMethod === 'transfer' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            <span>{{ __('pos.method_transfer_bank') }}</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'customer_credit'"
                            :class="selectedPayMethod === 'customer_credit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ __('pos.method_customer_credit') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Opsi Mesin EDC & Nomor Approval jika metode EDC dipilih --}}
                <div x-show="selectedPayMethod === 'edc_debit' || selectedPayMethod === 'edc_credit'" class="p-3.5 rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('pos.select_edc_machine_label') }}</label>
                        <select x-model="selectedEdcTerminalId"
                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">{{ __('pos.select_edc_placeholder') }}</option>
                            <template x-for="t in storeEdcTerminals" :key="t.id">
                                <option :value="t.id" x-text="t.terminal_name + ' (' + t.bank_name + ' - TID: ' + t.terminal_id_tid + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('pos.edc_approval_ref_optional') }}</label>
                        <input type="text" x-model="paymentRefNumber" placeholder="{{ __('pos.example_edc_appr_placeholder') }}"
                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <!-- Input Nominal Tender -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70">{{ __('pos.payment_amount_input_label') }}</label>
                        <span x-show="selectedPayMethod === 'qris'" class="text-[10px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full border border-[#007AFF]/20">
                            {{ __('pos.qris_tripay_auto_exact') }}
                        </span>
                    </div>
                    <input type="number" x-model.number="currentTenderAmount"
                        class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[20px] font-extrabold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">

                    <!-- QRIS Dynamic Info Alert -->
                    <div x-show="selectedPayMethod === 'qris'" class="mt-2 p-2.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 flex items-center gap-2 text-xs text-[#007AFF]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                        <span>{{ __('pos.qris_dynamic_generation_notice') }}</span>
                    </div>

                    <!-- Quick Cash Presets (only for Cash) -->
                    <div x-show="selectedPayMethod === 'cash'" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                        <button type="button" @click="currentTenderAmount = grandTotal"
                            class="h-9 rounded-[10px] bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-xs font-bold text-[#007AFF] tabular-nums transition active:scale-[0.97]">{{ __('pos.exact_amount_btn') }}</button>
                        <button type="button" @click="currentTenderAmount = 50000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">50.000</button>
                        <button type="button" @click="currentTenderAmount = 100000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">100.000</button>
                        <button type="button" @click="currentTenderAmount = 200000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">200.000</button>
                    </div>
                </div>

                <!-- Kembalian Live Indicator -->
                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-xs font-medium text-black/60 dark:text-white/60">{{ __('pos.change_amount_colon') }}</span>
                    <span class="text-[20px] font-extrabold text-[#34C759] tabular-nums"
                        x-text="formatRupiah(Math.max(0, currentTenderAmount - grandTotal))"></span>
                </div>
            </div>

            {{-- 2. MODE SPLIT PAYMENT (MULTI-METODE) --}}
            <div x-show="isSplitPayment" class="space-y-4">
                {{-- Financial Balance Status Bar --}}
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 block">{{ __('pos.total_bill') }}</span>
                        <span class="text-base font-extrabold text-black dark:text-white tabular-nums block mt-0.5" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-[#007AFF] block">{{ __('pos.total_allocated_label') }}</span>
                        <span class="text-base font-extrabold text-[#007AFF] tabular-nums block mt-0.5" x-text="formatRupiah(totalSplitPaid)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 block">{{ __('pos.remaining_underpaid_label') }}</span>
                        <span class="text-base font-extrabold tabular-nums block mt-0.5" :class="splitRemainingAmount > 0 ? 'text-[#FF3B30]' : 'text-black/40 dark:text-white/40'" x-text="formatRupiah(splitRemainingAmount)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-[#34C759] block">{{ __('pos.change_label') }}</span>
                        <span class="text-base font-extrabold text-[#34C759] tabular-nums block mt-0.5" x-text="formatRupiah(splitChangeAmount)"></span>
                    </div>
                </div>

                {{-- Quick Bagi Rata (Split Evenly) Presets --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <span class="text-[11px] font-bold text-black/50 dark:text-white/50 shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16" /></svg>
                        <span>{{ __('pos.split_evenly_label') }}</span>
                    </span>
                    <button type="button" @click="splitEvenly(2)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">{{ __('pos.people_count_preset', ['count' => 2]) }}</button>
                    <button type="button" @click="splitEvenly(3)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">{{ __('pos.people_count_preset', ['count' => 3]) }}</button>
                    <button type="button" @click="splitEvenly(4)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">{{ __('pos.people_count_preset', ['count' => 4]) }}</button>
                    <button type="button" @click="splitEvenly(5)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">{{ __('pos.people_count_preset', ['count' => 5]) }}</button>
                </div>

                {{-- Dynamic Split Rows Container --}}
                <div class="space-y-2.5 max-h-[42vh] overflow-y-auto pr-1">
                    <template x-for="(row, idx) in splitPaymentRows" :key="idx">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                    <span>{{ __('pos.payment_method_index_prefix') }}<span x-text="idx + 1"></span></span>
                                </span>
                                <button type="button" @click="removeSplitRow(idx)" x-show="splitPaymentRows.length > 1"
                                    class="w-6 h-6 rounded-full hover:bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                {{-- Dropdown Metode --}}
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-medium text-black/50 dark:text-white/50 mb-1">{{ __('pos.pay_channel_required_label') }}</label>
                                    <select x-model="row.payment_method"
                                        class="w-full h-10 px-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="cash">{{ __('pos.method_cash') }}</option>
                                        <option value="qris">{{ __('pos.method_qris') }}</option>
                                        <option value="edc_debit">{{ __('pos.method_edc_debit') }}</option>
                                        <option value="edc_credit">{{ __('pos.method_edc_credit') }}</option>
                                        <option value="transfer">{{ __('pos.method_transfer') }}</option>
                                        <option value="customer_credit">{{ __('pos.method_customer_credit') }}</option>
                                    </select>
                                </div>

                                {{-- Input Nominal & Auto Fill Sisa --}}
                                <div class="sm:col-span-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[10px] font-medium text-black/50 dark:text-white/50">{{ __('pos.nominal_rp_required_label') }}</label>
                                        <button type="button" @click="fillRemainingSplit(idx)" x-show="splitRemainingAmount > 0"
                                            class="text-[10px] font-bold text-[#007AFF] hover:underline">
                                            {{ __('pos.plus_remaining_prefix') }} (<span x-text="formatRupiah(splitRemainingAmount)"></span>)
                                        </button>
                                    </div>
                                    <input type="number" min="0" x-model.number="row.amount" @input="sanitizeSplitAmount(idx)"
                                        class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-bold tabular-nums text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                </div>

                                {{-- Detail EDC / Ref --}}
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-medium text-black/50 dark:text-white/50 mb-1">
                                        <span x-text="row.payment_method.startsWith('edc') ? '{{ __('pos.edc_machine_or_approval_label') }}' : '{{ __('pos.ref_or_notes_label') }}'"></span>
                                    </label>
                                    <template x-if="row.payment_method.startsWith('edc') && storeEdcTerminals.length > 0">
                                        <select x-model="row.store_edc_terminal_id"
                                            class="w-full h-10 px-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                            <option value="">{{ __('pos.select_edc_short_placeholder') }}</option>
                                            <template x-for="t in storeEdcTerminals" :key="t.id">
                                                <option :value="t.id" x-text="t.terminal_name + ' (' + t.terminal_id_tid + ')'"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!row.payment_method.startsWith('edc') || storeEdcTerminals.length === 0">
                                        <input type="text" x-model="row.reference_number" placeholder="{{ __('pos.ref_or_approval_placeholder') }}"
                                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Button Tambah Baris Split --}}
                <button type="button" @click="addSplitRow()" :disabled="splitPaymentRows.length >= 5"
                    class="w-full h-10 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('pos.add_another_payment_method') }}</span>
                </button>
            </div>

            {{-- Modal Actions Footer --}}
            <div class="pt-3 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-3">
                <div class="text-xs">
                    <template x-if="isSplitPayment && splitRemainingAmount > 0">
                        <span class="text-[#FF3B30] font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            <span>{{ __('pos.underpaid_allocation_notice') }} <strong x-text="formatRupiah(splitRemainingAmount)"></strong></span>
                        </span>
                    </template>
                    <template x-if="isSplitPayment && splitRemainingAmount === 0">
                        <span class="text-[#34C759] font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>{{ __('pos.exact_split_payment_notice') }}</span>
                        </span>
                    </template>
                    <template x-if="!isSplitPayment && currentTenderAmount >= grandTotal">
                        <span class="text-[#34C759] font-bold">{{ __('pos.ready_to_process_badge') }}</span>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showPaymentModal = false" :disabled="isProcessing"
                        class="h-10 px-4 rounded-[12px] text-xs font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition disabled:opacity-40">
                        {{ __('pos.cancel') }}
                    </button>
                    <button type="button" @click="submitCheckout()"
                        :disabled="isProcessing || (isSplitPayment ? splitRemainingAmount > 0 : currentTenderAmount < grandTotal)"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-bold text-xs shadow-sm disabled:opacity-35 transition flex items-center gap-2">
                        <span x-show="!isProcessing" x-text="isSplitPayment ? (window.COOCA_I18N?.finish_split_payment || '{{ __('pos.finish_split_payment') }}') : (selectedPayMethod === 'qris' ? '{{ __('pos.generate_dynamic_qris_btn') }}' : (window.COOCA_I18N?.finish_transaction || '{{ __('pos.finish_transaction') }}'))"></span>
                        <span x-show="isProcessing" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('pos.processing_status') }}</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
