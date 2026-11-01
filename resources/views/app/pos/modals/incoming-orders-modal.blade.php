<!-- ===================================================== -->
    <!-- MODAL: INCOMING QR TABLE ORDERS DRAWER                -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showIncomingOrdersModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showIncomingOrdersModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showIncomingOrdersModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showIncomingOrdersModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showIncomingOrdersModal = false">
                <div class="pos-modal-panel relative w-full max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">{{ __('pos.qr_incoming_orders_title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('pos.qr_incoming_orders_desc') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="fetchIncomingOrders()"
                        class="p-2 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white"
                        title="{{ __('pos.refresh') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                    <button type="button" @click="showIncomingOrdersModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                <template x-for="order in incomingQrOrders" :key="order.id">
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/10 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#007AFF]/15 text-[#007AFF]"
                                        x-text="'Meja ' + (order.pos_table ? order.pos_table.table_number : '-')"></span>
                                    <span class="font-mono text-xs text-black/50 dark:text-white/50"
                                        x-text="'#' + (order.order_number || order.id)"></span>
                                    <template x-if="order.is_paid">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            <span x-text="'{{ __('pos.paid_status_badge') }} (' + (order.payment_channel || 'QRIS') + ')'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!order.is_paid">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>{{ __('pos.pay_at_cashier') }}</span>
                                        </span>
                                    </template>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1">
                                    <span x-text="order.customer_name_guest || '{{ __('pos.customer') }}'"></span>
                                    <span class="text-black/45 dark:text-white/45"
                                        x-text="' • ' + (order.customer_phone_guest || '-')"></span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-black/50 dark:text-white/50"
                                x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes"
                            class="p-2 rounded-[8px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-[#FF9500] font-medium flex items-center gap-1.5">
                            <span class="font-bold">{{ __('pos.table_notes_colon') }}</span>
                            <span x-text="order.notes"></span>
                        </div>

                        <!-- Items Breakdown -->
                        <div class="space-y-1.5 border-t border-b border-black/[0.05] dark:border-white/5 py-2">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="text-xs">
                                    <div class="flex items-baseline justify-between">
                                        <div
                                            class="flex items-baseline gap-1.5 font-medium text-black dark:text-white">
                                            <span class="font-bold text-[#007AFF]"
                                                x-text="item.quantity + 'x'"></span>
                                            <span x-text="item.product_name"></span>
                                        </div>
                                        <span class="tabular-nums font-semibold"
                                            x-text="formatRupiah(item.total_price)"></span>
                                    </div>
                                    <div x-show="item.modifiers && item.modifiers.length > 0"
                                        class="pl-4 text-[11px] text-[#007AFF]">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <span class="mr-2"
                                                x-text="'• ' + mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                        </template>
                                    </div>
                                    <div x-show="item.notes" class="pl-4 text-[10px] text-[#FF9500] font-medium"
                                        x-text="'Catatan: ' + item.notes"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Total & Action Buttons -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <div>
                                <span class="text-[10px] text-black/50 dark:text-white/50 block">{{ __('pos.total_bill_label') }}</span>
                                <span class="text-base font-extrabold text-black dark:text-white tabular-nums"
                                    x-text="formatRupiah(order.total_amount)"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="!order.is_paid">
                                    <button type="button" @click="promptRejectOrder(order)"
                                        class="h-9 px-3 rounded-[10px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-xs font-bold transition">
                                        {{ __('pos.reject') }}
                                    </button>
                                </template>
                                <template x-if="order.is_paid">
                                    <span class="text-[11px] text-[#34C759] font-medium px-2 py-1 bg-[#34C759]/10 rounded-md">
                                        {{ __('pos.already_paid') }}
                                    </span>
                                </template>
                                <button type="button" @click="acceptIncomingOrder(order.id)"
                                    class="h-9 px-3.5 rounded-[10px] bg-[#34C759]/15 hover:bg-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-xs font-bold transition flex items-center gap-1.5"
                                    title="{{ __('pos.accept_and_forward_kds_tooltip') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>{{ __('pos.accept_only') }}</span>
                                </button>
                                <button type="button" @click="acceptAndLoadToCart(order.id)"
                                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
                                    title="{{ __('pos.accept_and_load_cart_tooltip') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                    </svg>
                                    <span>{{ __('pos.open_cashier') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="incomingQrOrders.length === 0"
                    class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('pos.no_incoming_qr_orders') }}</span>
                </div>
            </div>
                </div>
            </div>
        </div>
    </template>
