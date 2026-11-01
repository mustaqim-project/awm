<!-- ===================================================== -->
    <!-- 8. MODAL: HELD ORDERS QUEUE (Apple Sheet)             -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showHeldOrdersModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showHeldOrdersModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showHeldOrdersModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showHeldOrdersModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showHeldOrdersModal = false">
                <div class="pos-modal-panel relative w-full max-w-2xl lg:max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
                    <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                        <h3 class="font-semibold text-[17px] text-black dark:text-white">{{ __('pos.held_orders_modal_title') }}</h3>
                        <button @click="showHeldOrdersModal = false"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-72 overflow-y-auto space-y-2">
                        <template x-for="ho in heldOrders" :key="ho.id">
                            <div
                                class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-[14px] text-black dark:text-white"
                                        x-text="ho.hold_label || 'Antrean'"></div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5"
                                        x-text="ho.order_number + ' • ' + (ho.items ? ho.items.length : 0) + ' item'"></div>
                                </div>
                                <button @click="resumeHeldOrder(ho.id)"
                                    class="h-8 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[12px] transition">
                                    {{ __('pos.resume_order_btn') }}
                                </button>
                            </div>
                        </template>
                        <div x-show="heldOrders.length === 0"
                            class="text-center py-8 text-xs text-black/40 dark:text-white/40">
                            {{ __('pos.no_held_orders_desc') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
