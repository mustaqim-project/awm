<!-- ======================================================== -->
    <!-- MODAL 7: CATAT KASBON BARU                               -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddLoanModal" 
            x-cloak
            @keydown.escape.window="showAddLoanModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddLoanModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddLoanModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddLoanModal = false">
                <div x-show="showAddLoanModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_loan_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_loan_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddLoanModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.loans.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.select_employee_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <select name="user_id" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">{{ __('hrm.select_employee_ph') }}</option>
                        @foreach($memberships as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user?->name ?? 'User' }} ({{ $m->job_title ?: ucfirst($m->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_amount_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="amount" required step="10000" min="10000" placeholder="{{ __('hrm.loan_amount_ph') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.tenor_months_label') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="tenor_months" required min="1" max="60" value="1"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white tabular-nums">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_date_label') }}</label>
                        <input type="date" name="loan_date" required value="{{ date('Y-m-d') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_purpose_label') }}</label>
                    <input type="text" name="purpose" placeholder="{{ __('hrm.loan_purpose_ph') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddLoanModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(255,149,0,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_save_loan') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>
