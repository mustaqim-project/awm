<!-- ===================================================== -->
    <!-- 4.1. MODAL: DYNAMIC QRIS COOCA PAY (TriPay Gateway)   -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showQrisModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showQrisModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showQrisModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4">
                <div class="pos-modal-panel relative w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[26px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden transition-all">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-[15px] sm:text-base text-black dark:text-white tracking-tight leading-tight">{{ __('pos.qris_cashier_dynamic_title') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 truncate" x-text="activeQrisOrder ? ('{{ __('pos.order') }} #' + activeQrisOrder.order_number) : '{{ __('pos.loading_order_data') }}'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1">
                        <svg class="w-3 h-3 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span>{{ __('pos.locked_badge') }}</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                        TriPay QRIS
                    </span>
                </div>
            </div>

            <!-- Amount Card -->
            <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-black/40 dark:text-white/40 block">{{ __('pos.total_bill') }}</span>
                    <span class="text-xs font-semibold text-black/70 dark:text-white/70">{{ __('pos.qris_scan_instruction_subtitle') }}</span>
                </div>
                <span class="font-black text-[20px] sm:text-[22px] text-[#007AFF] tabular-nums"
                    x-text="activeQrisOrder ? formatRupiah(activeQrisOrder.total_amount) : ''"></span>
            </div>

            <!-- QR Code Card -->
            <div class="p-4 rounded-[20px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative">
                <template x-if="activeQrisPayment && activeQrisPayment.qr_url">
                    <img :src="activeQrisPayment.qr_url" alt="{{ __('admin.qris_code') }}" class="w-56 h-56 sm:w-60 sm:h-60 object-contain rounded-xl select-none">
                </template>
                <template x-if="!activeQrisPayment || !activeQrisPayment.qr_url">
                    <div class="w-56 h-56 flex flex-col items-center justify-center text-gray-400 gap-2">
                        <svg class="animate-spin h-7 w-7 text-[#007AFF]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-500">{{ __('pos.preparing_qr_code') }}</span>
                    </div>
                </template>
                <div class="mt-2.5 text-[10.5px] font-bold text-gray-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span>{{ __('pos.waiting_customer_payment') }}</span>
                </div>
            </div>

            <!-- Countdown Timer & Auto Polling Notice -->
            <div class="space-y-1 text-center text-xs">
                <div class="flex items-center justify-center gap-1.5 text-black/70 dark:text-white/70 font-semibold">
                    <span>{{ __('pos.qr_validity_period_label') }}</span>
                    <span class="font-mono text-[#FF9500] font-extrabold tabular-nums text-sm" x-text="qrisCountdownFormatted">15:00</span>
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45">
                    {{ __('pos.qris_auto_detection_notice') }}
                </p>
            </div>

            <!-- Actions Grid -->
            <div class="pt-2 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="checkQrisStatusManual()" :disabled="isCheckingQrisStatus"
                        class="h-10 px-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                        <svg x-show="!isCheckingQrisStatus" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <svg x-show="isCheckingQrisStatus" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isCheckingQrisStatus ? '{{ __('pos.checking_status') }}' : '{{ __('pos.check_status_now_btn') }}'"></span>
                    </button>
                    <button type="button" @click="printQrisSlip()"
                        class="h-10 px-3 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] text-black dark:text-white font-bold text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.13-1.27-3.118m0 0A10.978 10.978 0 0112 7.5c2.47 0 4.757.818 6.55 2.211m-6.55 7.789a10.978 10.978 0 01-6.55-2.211M12 21a9 9 0 100-18 9 9 0 000 18z" /></svg>
                        <span>{{ __('pos.print_qr_slip_btn') }}</span>
                    </button>
                </div>

                @if(!config('tripay.is_production', false))
                <button type="button" @click="simulateQrisPayment()" :disabled="isSimulatingQris"
                    class="w-full h-9 rounded-[10px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] font-bold text-xs transition flex items-center justify-center gap-1.5 disabled:opacity-40 border border-[#34C759]/25">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span x-text="isSimulatingQris ? '{{ __('pos.processing_simulation') }}' : '{{ __('pos.qris_sandbox_simulation_btn') }}'"></span>
                </button>
                @endif

                <button type="button" @click="cancelQrisPayment()" :disabled="isCancellingQris"
                    class="w-full h-10 rounded-[12px] text-xs font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition disabled:opacity-40 flex items-center justify-center gap-1.5 border border-[#FF3B30]/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                    <span x-text="isCancellingQris ? '{{ __('pos.cancelling_qris_session') }}' : '{{ __('pos.change_payment_method_btn') }}'"></span>
                </button>
            </div>
        </div>
    </div>
</template>
