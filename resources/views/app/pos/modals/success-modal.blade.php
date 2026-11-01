<!-- ===================================================== -->
    <!-- 5. MODAL: PAYMENT SUCCESS & RECEIPT (Bento UI Grid)  -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showSuccessModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="if(showSuccessModal) resetForNewOrder()">
            <!-- Standalone Backdrop -->
            <div x-show="showSuccessModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="resetForNewOrder()"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="resetForNewOrder()">
                <div class="pos-modal-panel relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden">

            <!-- Top Header Row -->
            <div
                class="flex items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-11 h-11 rounded-2xl bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/25 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-[17px] text-black dark:text-white tracking-tight leading-snug">
                            {{ __('pos.transaction_success_title') }}</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span
                                class="font-mono text-[11px] font-bold px-1.5 py-0.2 rounded bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70"
                                x-text="lastCompletedOrder ? ('#' + lastCompletedOrder.order_number) : ''"></span>
                            <span class="text-[11px] font-medium text-[#34C759]">{{ __('pos.paid_status_badge') }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="resetForNewOrder()"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition shrink-0"
                    title="{{ __('pos.close_esc_tooltip') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Financial Summary Bento Strip -->
            <div
                class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-3 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">
                <div class="px-2 py-1">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">{{ __('pos.total_shopping_label') }}</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.total_amount : 0)"></span>
                </div>
                <div class="px-2 py-1 border-x border-black/5 dark:border-white/5">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">{{ __('pos.total_paid_label') }}</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.paid_amount : 0)"></span>
                </div>
                <div class="px-2 py-1">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-[#34C759] block">{{ __('pos.change_label') }}</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-extrabold text-[#34C759] tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.change_amount : 0)"></span>
                </div>
            </div>

            <!-- Paperless Auto KDS Confirmation Badge -->
            <div x-show="lastOrderSentToKds" x-cloak
                class="py-2.5 px-3.5 rounded-xl bg-[#34C759]/12 border border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="truncate">{{ __('pos.kds_paperless_notice') }}</span>
                </div>
                <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] shrink-0 ml-2">{{ __('pos.kds_live_badge') }}</span>
            </div>

            <!-- WhatsApp Feedback Toast (If Triggered) -->
            <div x-show="waBotFeedback" x-cloak
                class="text-xs font-semibold py-2 px-3 rounded-xl border flex items-center justify-between gap-2"
                :class="waBotFeedbackSuccess ? 'bg-[#34C759]/12 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' :
                    'bg-[#FF3B30]/12 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A]'">
                <div class="flex items-center gap-2">
                    <svg x-show="waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg x-show="!waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span x-text="waBotFeedback"></span>
                </div>
                <button type="button" @click="waBotFeedback = ''"
                    class="w-6 h-6 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- BENTO ACTIONS GRID -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 px-0.5">
                    {{ __('pos.receipt_billing_actions_heading') }}
                </div>

                <!-- Primary Row: Thermal Hardware ESC/POS & WhatsApp Bot -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">

                    <!-- Bento Tile 1: Cetak Struk ESC/POS Hardware (Hero Green/Blue) -->
                    <button type="button" @click.stop="!directPrinting && directPrintReceipt(lastCompletedOrder.id)"
                        :disabled="directPrinting"
                        class="group rounded-2xl p-4 bg-gradient-to-br from-[#34C759] to-[#248A3D] hover:from-[#30D158] hover:to-[#227D37] active:scale-[0.98] text-white flex flex-col justify-between shadow-md shadow-[#34C759]/20 transition relative overflow-hidden min-h-[120px] text-left disabled:opacity-60">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                </svg>
                            </div>
                            <span
                                class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/20 text-white backdrop-blur-xs"
                                x-text="directPrinting ? '{{ __('pos.printing_status') }}' : '{{ __('pos.hardware_esc_pos_badge') }}'">
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] leading-tight">{{ __('pos.print_receipt_esc_pos_title') }}</div>
                            <div class="text-[11px] text-white/90 mt-0.5 flex items-center justify-between">
                                <span>{{ __('pos.send_to_thermal_printer_sub') }}</span>
                                <span class="font-bold group-hover:translate-x-0.5 transition" x-text="directPrinting ? '{{ __('pos.printing_status') }}' : '{{ __('pos.print_action_label') }}'"></span>
                            </div>
                        </div>
                    </button>

                    <!-- Bento Tile 2: WhatsApp Bot Otomatis (Hero Emerald) -->
                    <button type="button" @click.stop="!sendingWaBot && sendWhatsAppBotReceipt()"
                        :disabled="sendingWaBot"
                        class="group rounded-2xl p-4 bg-[#25D366]/10 hover:bg-[#25D366]/18 dark:bg-[#25D366]/15 dark:hover:bg-[#25D366]/22 border border-[#25D366]/30 dark:border-[#25D366]/40 active:scale-[0.98] text-black dark:text-white flex flex-col justify-between transition shadow-xs disabled:opacity-50 text-left min-h-[120px]"
                        :class="sendingWaBot ? 'pointer-events-none opacity-60' : ''">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="w-9 h-9 rounded-xl bg-[#25D366] text-white flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                :class="waBotSent ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : (sendingWaBot ?
                                    'bg-[#FF9500]/20 text-[#FF9500]' : 'bg-[#25D366]/20 text-[#25D366]')">
                                <span
                                    x-text="sendingWaBot ? '{{ __('pos.sending_status') }}' : (waBotSent ? '{{ __('pos.sent_status') }}' : '{{ __('pos.auto_bot_status') }}')"></span>
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] text-black dark:text-white leading-tight">{{ __('pos.send_whatsapp_title') }}</div>
                            <div
                                class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 flex items-center justify-between">
                                <span>{{ __('pos.send_e_receipt_customer_sub') }}</span>
                                <span class="font-bold text-[#25D366] group-hover:translate-x-0.5 transition">
                                    <span x-text="waBotSent ? '{{ __('pos.resend_action_label') }}' : '{{ __('pos.send_action_label') }}'"></span>
                                </span>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Secondary Row: Cetak Dapur, Pratinjau Browser, & Gambar Struk PNG -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <!-- Bento Tile 3: Cetak Tiket Dapur / Bar (KOT) -->
                    <button type="button" @click.stop="!kitchenPrinting && printKitchenTickets(lastCompletedOrder.id)"
                        :disabled="kitchenPrinting"
                        class="p-2.5 sm:p-3 rounded-xl bg-[#FF9500]/10 hover:bg-[#FF9500]/18 border border-[#FF9500]/30 active:scale-[0.98] transition flex items-center justify-between group text-left disabled:opacity-50">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate" x-text="kitchenPrinting ? '{{ __('pos.sending_status') }}' : '{{ __('pos.kitchen_ticket_title') }}'">{{ __('pos.kitchen_ticket_title') }}</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">{{ __('pos.kot_station_sub') }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-[#FF9500] group-hover:translate-x-0.5 transition shrink-0" x-text="kitchenPrinting ? '{{ __('pos.sending_status') }}' : '{{ __('pos.send_action_label') }}'"></span>
                    </button>

                    <!-- Bento Tile 4: Pratinjau & Cetak Browser (Fallback) -->
                    <a :href="lastReceiptUrl" target="_blank"
                        class="p-2.5 sm:p-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-between group">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate">{{ __('pos.browser_print_title') }}</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">{{ __('pos.browser_print_sub') }}</div>
                            </div>
                        </div>
                        <span
                            class="text-[10px] text-black/30 dark:text-white/30 group-hover:text-black dark:group-hover:text-white group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>

                    <!-- Bento Tile 5: Gambar Struk PNG HD -->
                    <a :href="lastReceiptImageUrl" target="_blank"
                        class="p-2.5 sm:p-3 rounded-xl bg-[#007AFF]/8 hover:bg-[#007AFF]/15 border border-[#007AFF]/25 active:scale-[0.98] transition flex items-center justify-between group shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-[#007AFF] truncate">{{ __('pos.image_receipt_title') }}</div>
                                <div class="text-[9px] text-[#007AFF]/70 truncate">{{ __('pos.image_receipt_sub') }}</div>
                            </div>
                        </div>
                        <span
                            class="text-[10px] font-bold text-[#007AFF] group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Action: Transaksi Baru -->
            <div class="pt-1">
                <button type="button" @click="resetForNewOrder()"
                    class="w-full h-11 sm:h-12 rounded-2xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] active:scale-[0.98] text-black dark:text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition border border-black/5 dark:border-white/10">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2.2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>{{ __('pos.finish_new_transaction_btn') }}</span>
                    <span
                        class="text-[10px] text-black/40 dark:text-white/40 font-normal px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 ml-1">Esc</span>
                </button>
            </div>
        </div>
    </div>
</template>
