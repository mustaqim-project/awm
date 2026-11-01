<!-- ======================================================== -->
    <!-- TAB 5: PENGGAJIAN BULANAN (PAYROLL RUNS)                -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'payrolls'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_payroll_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_employee_count') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_total_gross') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_total_deductions') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_take_home_pay') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_company_burden') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($payrolls as $p)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <a href="{{ route('hrm.payrolls.show', $p->id) }}" class="hover:text-[#007AFF] transition">
                                        {{ $p->title }}
                                    </a>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50 font-normal">
                                        {{ __('hrm.created_at_prefix') }} {{ $p->created_at->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black dark:text-white">
                                    {{ $p->total_employees_count }} {{ __('hrm.people_unit') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black dark:text-white font-medium">
                                    Rp {{ number_format((float)$p->total_gross_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF3B30] font-semibold">
                                    -Rp {{ number_format((float)$p->total_deductions, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-extrabold text-[#34C759]">
                                    Rp {{ number_format((float)$p->total_take_home_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    Rp {{ number_format((float)$p->total_company_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($p->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_paid_badge') }}</span>
                                        </span>
                                    @elseif($p->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_approved_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_draft_badge') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('hrm.payrolls.show', $p->id) }}"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.detail_and_slip') }}"
                                            aria-label="{{ __('hrm.detail_and_slip') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>

                                        @if($p->status === 'draft')
                                            <form method="POST" action="{{ route('hrm.payrolls.destroy', $p->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_payroll')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_draft') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_draft') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_payrolls_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($payrolls as $p)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('hrm.payrolls.show', $p->id) }}" class="font-bold text-[14px] text-black dark:text-white hover:text-[#007AFF] transition block truncate">
                                    {{ $p->title }}
                                </a>
                                <div class="text-[11px] text-black/50 dark:text-white/50">
                                    {{ __('hrm.created_at_prefix') }} {{ $p->created_at->translatedFormat('d M Y') }} · <strong class="text-black/70 dark:text-white/70">{{ $p->total_employees_count }} {{ __('hrm.people_unit') }}</strong>
                                </div>
                            </div>
                            @if($p->status === 'paid')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30 shrink-0">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_paid_badge') }}</span>
                                </span>
                            @elseif($p->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30 shrink-0">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_approved_badge') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30 shrink-0">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_draft_badge') }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Financial Bento Grid -->
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/60 dark:text-white/60">{{ __('hrm.col_total_gross') }}</span>
                                <span class="font-semibold text-black dark:text-white tabular-nums">
                                    Rp {{ number_format((float)$p->total_gross_pay, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/60 dark:text-white/60">{{ __('hrm.col_total_deductions') }}</span>
                                <span class="font-semibold text-[#FF3B30] tabular-nums">
                                    -Rp {{ number_format((float)$p->total_deductions, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">{{ __('hrm.col_take_home_pay') }}</span>
                                    <span class="text-[15px] font-extrabold text-[#34C759] tabular-nums">
                                        Rp {{ number_format((float)$p->total_take_home_pay, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-black/40 dark:text-white/40 block">{{ __('hrm.col_company_burden') }}</span>
                                    <span class="text-[12px] font-bold text-black/80 dark:text-white/80 tabular-nums">
                                        Rp {{ number_format((float)$p->total_company_cost, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-1 flex items-center gap-2">
                            <a href="{{ route('hrm.payrolls.show', $p->id) }}"
                                class="flex-1 h-11 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                                <span>{{ __('hrm.detail_and_slip') }}</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>

                            @if($p->status === 'draft')
                                <form method="POST" action="{{ route('hrm.payrolls.destroy', $p->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_payroll')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.tooltip_delete_draft') }}"
                                        aria-label="{{ __('hrm.tooltip_delete_draft') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="receipt-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_payrolls_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($payrolls->total() > 0)
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>
