<!-- ======================================================== -->
    <!-- TAB 3: PERSETUJUAN TIKET KOREKSI ABSENSI                 -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'corrections'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.tickets_approval_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.tickets_approval_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showAddCorrectionModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_apply_correction') }}</span>
                </button>
            </div>

            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_ticket_number') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_requester_staff') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_type_proposed') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_proposed_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_reason_evidence') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($corrections as $cor)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white tabular-nums">
                                    {{ $cor->correction_number }}
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-black dark:text-white">
                                    {{ $cor->user?->name ?? 'Staf' }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/70 dark:text-white/70 font-medium">
                                    {{ $cor->target_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 text-black/80 dark:text-white/80 font-medium">
                                    {{ $cor->type_label }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[12.5px]">
                                    @if($cor->proposed_clock_in)
                                        <div>{{ __('hrm.work_in_label') }} <strong>{{ substr((string)$cor->proposed_clock_in, 0, 5) }}</strong></div>
                                    @endif
                                    @if($cor->proposed_clock_out)
                                        <div>{{ __('hrm.work_out_label') }} <strong>{{ substr((string)$cor->proposed_clock_out, 0, 5) }}</strong></div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 max-w-xs truncate text-[12.5px] text-black/70 dark:text-white/70 font-medium" title="{{ $cor->reason }}">
                                    {{ $cor->reason }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($cor->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.badge_approved') }}
                                        </span>
                                    @elseif($cor->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            {{ __('hrm.badge_rejected') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.badge_pending_review') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end">
                                        <button type="button" @click="openReviewCorrection({{ json_encode($cor) }})"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}"
                                            aria-label="{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}">
                                            <i data-lucide="{{ $cor->status === 'pending' ? 'clipboard-check' : 'eye' }}" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_tickets_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($corrections as $cor)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-black dark:text-white text-[13px] tabular-nums truncate">
                                    {{ $cor->correction_number }}
                                </span>
                                <span class="text-[11px] text-black/40 dark:text-white/40">·</span>
                                <span class="text-[11.5px] text-black/60 dark:text-white/60 font-medium shrink-0">
                                    {{ $cor->target_date->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            @if($cor->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.badge_approved') }}
                                </span>
                            @elseif($cor->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] shrink-0">
                                    {{ __('hrm.badge_rejected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] shrink-0">
                                    {{ __('hrm.badge_pending_review') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="font-bold text-[14px] text-black dark:text-white truncate">
                                {{ $cor->user?->name ?? 'Staf' }}
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 shrink-0">
                                {{ $cor->type_label }}
                            </span>
                        </div>

                        <!-- Proposed Hours Bento -->
                        <div class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.proposed_in') }}</span>
                                <span class="font-bold text-black dark:text-white font-mono">
                                    {{ $cor->proposed_clock_in ? substr((string)$cor->proposed_clock_in, 0, 5) : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.proposed_out') }}</span>
                                <span class="font-bold text-black dark:text-white font-mono">
                                    {{ $cor->proposed_clock_out ? substr((string)$cor->proposed_clock_out, 0, 5) : '-' }}
                                </span>
                            </div>
                        </div>

                        @if($cor->reason)
                            <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] text-[12px] text-black/70 dark:text-white/70">
                                <span class="text-[10.5px] font-semibold text-black/40 dark:text-white/40 block mb-0.5">{{ __('hrm.col_reason_evidence') }}:</span>
                                {{ $cor->reason }}
                            </div>
                        @endif

                        <div class="pt-1">
                            <button type="button" @click="openReviewCorrection({{ json_encode($cor) }})"
                                class="w-full h-11 px-4 rounded-[12px] text-[13px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="file-check" class="w-4 h-4"></i>
                                <span>{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_tickets_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($corrections->total() > 0)
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $corrections->links() }}
                </div>
            @endif
        </div>
    </div>
