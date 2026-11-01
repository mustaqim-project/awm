<!-- ======================================================== -->
    <!-- MODAL 9: REVIEW PERSUTUJUAN TIKET KOREKSI (VISUAL DIFF)  -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showReviewCorrectionModal" 
            x-cloak
            @keydown.escape.window="showReviewCorrectionModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showReviewCorrectionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showReviewCorrectionModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showReviewCorrectionModal = false">
                <div x-show="showReviewCorrectionModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_review_correction_title') }}</span>
                                <span class="font-mono text-[14px] font-bold text-[#007AFF] px-2.5 py-0.5 rounded-[7px] bg-[#007AFF]/10" x-text="selectedCorrection?.correction_number"></span>
                                <template x-if="selectedCorrection?.status === 'pending'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">{{ __('hrm.badge_pending_review') }}</span>
                                </template>
                                <template x-if="selectedCorrection?.status === 'approved'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">{{ __('hrm.badge_approved') }}</span>
                                </template>
                                <template x-if="selectedCorrection?.status === 'rejected'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">{{ __('hrm.badge_rejected') }}</span>
                                </template>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 font-medium">
                                {{ __('hrm.submitter_prefix') }} <strong class="text-black dark:text-white" x-text="selectedCorrection?.user?.name || '{{ __('hrm.default_employee_name') }}'"></strong>
                                &bull; {{ __('hrm.target_prefix') }} <span class="tabular-nums font-semibold" x-text="selectedCorrection?.target_date ? selectedCorrection.target_date.substring(0, 10) : '-'"></span>
                            </p>
                        </div>
                        <button type="button" @click="showReviewCorrectionModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <div class="p-5 overflow-y-auto space-y-5 text-[13px]">
                <!-- Visual Diff Comparison -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2.5">
                        <div class="flex items-center gap-2 text-black/70 dark:text-white/70 font-bold text-[12.5px]">
                            <i data-lucide="database" class="w-4 h-4"></i>
                            <span>{{ __('hrm.sensor_original_record') }}</span>
                        </div>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.clock_in_label') }}</span>
                                <span class="font-bold tabular-nums text-black dark:text-white" x-text="selectedCorrection?.attendance?.clock_in_at ? selectedCorrection.attendance.clock_in_at.substring(11, 16) + ' WIB' : '{{ __('hrm.not_recorded') }}'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.clock_out_label') }}</span>
                                <span class="font-bold tabular-nums text-black dark:text-white" x-text="selectedCorrection?.attendance?.clock_out_at ? selectedCorrection.attendance.clock_out_at.substring(11, 16) + ' WIB' : '{{ __('hrm.not_recorded') }}'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.06] border border-[#007AFF]/20 space-y-2.5">
                        <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[12.5px]">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                            <span>{{ __('hrm.proposed_staff_fix') }}</span>
                        </div>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.proposed_in') }}</span>
                                <span class="font-bold tabular-nums text-[#007AFF]" x-text="selectedCorrection?.proposed_clock_in ? selectedCorrection.proposed_clock_in.substring(0, 5) + ' WIB' : '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.proposed_out') }}</span>
                                <span class="font-bold tabular-nums text-[#007AFF]" x-text="selectedCorrection?.proposed_clock_out ? selectedCorrection.proposed_clock_out.substring(0, 5) + ' WIB' : '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alasan Staf -->
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 block mb-1">{{ __('hrm.staff_reason_label') }}</span>
                    <p class="text-[13px] text-black dark:text-white leading-relaxed italic font-medium" x-text="selectedCorrection?.reason"></p>
                </div>

                <!-- Lampiran Bukti File -->
                <template x-if="selectedCorrection?.attachment_path">
                    <div class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i data-lucide="paperclip" class="w-4.5 h-4.5 text-[#007AFF] shrink-0"></i>
                            <span class="text-[12.5px] font-bold text-black/80 dark:text-white/80 truncate">{{ __('hrm.attachment_evidence_attached') }}</span>
                        </div>
                        <a :href="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection.id + '/attachment'" target="_blank"
                            class="h-8 px-3.5 rounded-[9px] text-[11.5px] font-bold bg-[#007AFF] text-white hover:bg-[#0071E3] transition flex items-center gap-1.5 shrink-0">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_open_file') }}</span>
                        </a>
                    </div>
                </template>

                <!-- Review Form Controls jika Pending -->
                <template x-if="selectedCorrection?.status === 'pending'">
                    <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/approve'"
                                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                                class="space-y-2">
                                @csrf
                                <input type="text" name="review_notes" placeholder="{{ __('hrm.approval_notes_ph') }}"
                                    class="w-full h-11 sm:h-9 px-3.5 sm:px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12px] font-medium text-black dark:text-white focus:ring-1 focus:ring-[#34C759]">
                                <button type="submit" :disabled="isSubmittingForm"
                                    :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                                    class="w-full h-11 sm:h-10 rounded-[11px] bg-[#34C759] hover:bg-[#28A745] text-white text-[14px] sm:text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                                    <template x-if="!isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>{{ __('hrm.btn_approve_and_sync') }}</span>
                                        </div>
                                    </template>
                                    <template x-if="isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>{{ __('hrm.processing') }}</span>
                                        </div>
                                    </template>
                                </button>
                            </form>

                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/reject'"
                                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                                class="space-y-2">
                                @csrf
                                <input type="text" name="reason" required minlength="3" placeholder="{{ __('hrm.rejection_reason_ph') }}"
                                    class="w-full h-11 sm:h-9 px-3.5 sm:px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12px] font-medium text-black dark:text-white focus:ring-1 focus:ring-[#FF3B30]">
                                <button type="submit" :disabled="isSubmittingForm"
                                    :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                                    class="w-full h-11 sm:h-10 rounded-[11px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-[14px] sm:text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,59,48,0.3)] transition active:scale-[0.98] cursor-pointer">
                                    <template x-if="!isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                            <span>{{ __('hrm.btn_reject_request') }}</span>
                                        </div>
                                    </template>
                                    <template x-if="isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>{{ __('hrm.processing') }}</span>
                                        </div>
                                    </template>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
                </div>
            </div>
        </div>
    </template>
