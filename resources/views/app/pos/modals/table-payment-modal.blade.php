<!-- ===================================================== -->
    <!-- MODAL: PAY TABLE QR ORDER (ATOMIC MATERIAL DEDUCTION)  -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showTablePaymentModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showTablePaymentModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showTablePaymentModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showTablePaymentModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showTablePaymentModal = false">
                <div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
            <template x-if="activeTableOrder">
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div>
                            <h3 class="font-bold text-base text-black dark:text-white">{{ __('pos.table_order_payment_title') }}</h3>
                            <div class="text-xs text-black/50 dark:text-white/50"
                                x-text="'{{ __('pos.order_prefix') }} #' + activeTableOrder.order_number"></div>
                        </div>
                        <button type="button" @click="showTablePaymentModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Order Items Summary -->
                    <div
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5 max-h-40 overflow-y-auto">
                        <template x-for="it in activeTableOrder.items" :key="it.id">
                            <div class="flex justify-between text-xs">
                                <div>
                                    <span class="font-bold" x-text="it.quantity + 'x '"></span>
                                    <span x-text="it.product_name"></span>
                                    <div x-show="it.modifiers && it.modifiers.length > 0"
                                        class="text-[10px] text-[#007AFF]">
                                        <template x-for="m in it.modifiers" :key="m.id">
                                            <span class="mr-1" x-text="'+ ' + m.modifier_option_name"></span>
                                        </template>
                                    </div>
                                </div>
                                <span class="font-semibold tabular-nums"
                                    x-text="formatRupiah(it.total_price)"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Amount & Payment Status Display -->
                    <template x-if="activeTableOrder.is_paid">
                        <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 space-y-2.5">
                            <div class="flex items-center gap-2 text-[#248A3D] dark:text-[#30D158]">
                                <div class="w-6 h-6 rounded-full bg-[#34C759]/20 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider">{{ __('pos.paid_via_qris_badge') }}</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-1">
                                <span class="text-xs text-black/60 dark:text-white/60 font-medium">{{ __('pos.total_paid_label_colon') }}</span>
                                <span class="text-xl font-black text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="formatRupiah(activeTableOrder.total_amount)"></span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 border-t border-[#34C759]/15 pt-2 flex items-center justify-between">
                                <span>{{ __('pos.payment_channel_colon') }} <b class="font-semibold text-black/80 dark:text-white/80" x-text="activeTableOrder.payment_channel || '{{ __('pos.method_qris') }}'"></b></span>
                                <span x-show="activeTableOrder.gateway_reference" class="font-mono text-[10px]" x-text="'Ref: ' + activeTableOrder.gateway_reference"></span>
                            </div>
                            <div class="p-2 rounded-[8px] bg-[#34C759]/15 text-[11px] text-[#248A3D] dark:text-[#30D158] font-medium leading-relaxed">
                                {{ __('pos.guest_paid_self_notice') }}
                            </div>
                        </div>
                    </template>

                    <template x-if="!activeTableOrder.is_paid">
                        <div>
                            <!-- Total Amount Display -->
                            <div class="text-center py-2 bg-[#007AFF]/10 rounded-[12px] border border-[#007AFF]/20">
                                <span class="text-xs text-[#007AFF] font-medium block">{{ __('pos.total_to_be_paid_block') }}</span>
                                <span class="text-2xl font-black text-[#007AFF] tabular-nums"
                                    x-text="formatRupiah(activeTableOrder.total_amount)"></span>
                            </div>

                            <!-- Payment Method Selector -->
                            <div class="space-y-1.5 mt-3">
                                <label class="block text-xs font-semibold text-black dark:text-white">{{ __('pos.payment_method') }}</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'cash'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'cash' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">{{ __('pos.method_cash') }}</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'qris'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'qris' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">{{ __('pos.qris_label') }}</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'transfer'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'transfer' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">{{ __('pos.method_transfer') }}</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'debit'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'debit' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">{{ __('pos.method_debit') }}</button>
                                </div>
                            </div>

                            <!-- Tender Amount (for Cash) -->
                            <div x-show="selectedTablePayMethod === 'cash'" class="space-y-1.5 mt-3">
                                <label class="block text-xs font-semibold text-black dark:text-white">{{ __('pos.nominal_received_label') }}</label>
                                <input type="number" x-model.number="tableTenderAmount"
                                    class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 font-bold text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <div x-show="tableTenderAmount > activeTableOrder.total_amount"
                                    class="text-xs text-[#34C759] font-bold">
                                    {{ __('pos.change_amount_colon') }} <span
                                        x-text="formatRupiah(tableTenderAmount - activeTableOrder.total_amount)"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showTablePaymentModal = false"
                            class="h-10 px-4 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                        <template x-if="activeTableOrder.is_paid">
                            <button type="button" @click="submitPayTableOrder()"
                                :disabled="isProcessing"
                                class="h-10 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>{{ __('pos.settle_clear_table') }}</span>
                            </button>
                        </template>
                        <template x-if="!activeTableOrder.is_paid">
                            <button type="button" @click="submitPayTableOrder()"
                                :disabled="tableTenderAmount < activeTableOrder.total_amount || isProcessing"
                                class="h-10 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40">
                                {{ __('pos.confirm_paid_settle') }}
                            </button>
                        </template>
                    </div>
                </div>
            </template>
                </div>
            </div>
        </div>
    </template>
