<!-- ===================================================== -->
    <!-- 9. MODAL: HOLD CART PROMPT (Apple Sheet - No prompt)  -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showHoldPromptModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showHoldPromptModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showHoldPromptModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showHoldPromptModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showHoldPromptModal = false">
                <div class="pos-modal-panel relative w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
                    <div>
                        <h3 class="font-semibold text-[17px] text-black dark:text-white">{{ __('pos.hold_save_modal_title') }}</h3>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">{{ __('pos.hold_save_modal_desc') }}</p>
                    </div>
                    <form @submit.prevent="confirmHoldCart()">
                        <div>
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.hold_label_required') }}</label>
                            <input x-ref="holdLabelInput" type="text" x-model="holdOrderLabel" required
                                maxlength="100" placeholder="{{ __('pos.hold_reference_placeholder') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div class="flex justify-end gap-2 pt-3 mt-4 border-t border-black/10 dark:border-white/10">
                            <button type="button" @click="showHoldPromptModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">{{ __('pos.cancel') }}</button>
                            <button type="submit"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">{{ __('pos.hold_save_btn') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
