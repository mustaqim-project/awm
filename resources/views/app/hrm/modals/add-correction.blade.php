<!-- ======================================================== -->
    <!-- MODAL 8: AJUKAN TIKET PERBAIKAN ABSENSI                  -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddCorrectionModal" 
            x-cloak
            @keydown.escape.window="showAddCorrectionModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddCorrectionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddCorrectionModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddCorrectionModal = false">
                <div x-show="showAddCorrectionModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="file-check-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_correction_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_correction_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddCorrectionModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.attendance.corrections.store') }}" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_target_date') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="date" name="target_date" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_correction_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="correction_type" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                            <option value="clock_in_only">{{ __('hrm.corr_type_clock_in') }}</option>
                            <option value="clock_out_only">{{ __('hrm.corr_type_clock_out') }}</option>
                            <option value="full_day">{{ __('hrm.corr_type_full_day') }}</option>
                            <option value="status_only">{{ __('hrm.corr_type_status_only') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_clock_in_label') }}</label>
                        <input type="time" name="proposed_clock_in" value="08:30"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_clock_out_label') }}</label>
                        <input type="time" name="proposed_clock_out" value="17:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_status_label') }}</label>
                        <select name="proposed_status" class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                            <option value="present">{{ __('portal.status_present') }}</option>
                            <option value="late">{{ __('portal.status_late') }}</option>
                            <option value="half_day">{{ __('portal.status_half_day') }}</option>
                            <option value="sick">{{ __('portal.status_sick') }}</option>
                            <option value="leave">{{ __('portal.status_leave') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.corr_reason_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <textarea name="reason" required minlength="5" maxlength="1000" rows="3"
                        placeholder="{{ __('hrm.corr_reason_ph') }}"
                        class="w-full p-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.attachment_label') }}</label>
                    <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf"
                        class="w-full text-[16px] sm:text-[12.5px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 sm:file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-[13px] sm:file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF]">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddCorrectionModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[14px] sm:text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[14px] sm:text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(255,149,0,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_submit_correction') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.submitting') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>
