<!-- ===================================================== -->
    <!-- 3. MODAL: QUICK CUSTOMER (Apple Sheet Presentation)   -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showCustomerModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showCustomerModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showCustomerModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showCustomerModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCustomerModal = false">
                <div class="pos-modal-panel relative w-full max-w-lg lg:max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
                    <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                        <div>
                            <h3 class="font-semibold text-[16px] text-black dark:text-white">{{ __('pos.quick_customer_title') }}</h3>
                            <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">{{ __('pos.quick_customer_desc') }}</p>
                        </div>
                        <button type="button" @click="showCustomerModal = false"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="createQuickCustomer" class="space-y-3">
                        <div>
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.customer_name') }} *</label>
                            <input x-ref="quickCustomerName" type="text" x-model="newCustomer.name" required
                                maxlength="255" placeholder="{{ __('pos.customer_name_placeholder') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.customer_phone') }} *</label>
                            <input type="tel" x-model="newCustomer.phone" required maxlength="50" inputmode="tel"
                                placeholder="{{ __('pos.customer_phone_placeholder') }}"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <p x-show="customerFormError" x-text="customerFormError" class="text-xs text-[#FF3B30]"></p>
                        <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                            <button type="button" @click="showCustomerModal = false"
                                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">{{ __('pos.cancel') }}</button>
                            <button type="submit" :disabled="isCreatingCustomer"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] disabled:opacity-50 transition active:scale-[0.97]">
                                <span x-text="isCreatingCustomer ? (window.COOCA_I18N?.saving || '{{ __('pos.saving') }}') : (window.COOCA_I18N?.save_customer || '{{ __('pos.save_customer') }}')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
