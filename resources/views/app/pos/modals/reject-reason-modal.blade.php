<!-- ===================================================== -->
    <!-- MODAL: REJECT REASON PROMPT                           -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showRejectReasonModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showRejectReasonModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showRejectReasonModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showRejectReasonModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showRejectReasonModal = false">
                <div class="pos-modal-panel relative w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
                    <div>
                        <h3 class="font-bold text-base text-[#FF3B30]">{{ __('pos.reject_table_order') }}</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1">{{ __('pos.reject_table_order_desc') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('pos.rejection_reason_label') }}</label>
                        <textarea x-model="rejectionReason" rows="3" placeholder="{{ __('pos.rejection_reason_placeholder') }}"
                            class="w-full rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showRejectReasonModal = false"
                            class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                        <button type="button" @click="submitRejectOrder()"
                            class="h-9 px-4 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-xs font-bold transition">
                            {{ __('pos.confirm_reject') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
