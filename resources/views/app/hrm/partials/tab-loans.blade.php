<!-- ======================================================== -->
    <!-- TAB 6: KASBON & PINJAMAN KARYAWAN                        -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'loans'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_loan_number') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_employee_name') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.principal_ceiling') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_tenor') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.installment_per_month') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.remaining_balance_label') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white tabular-nums">
                                    {{ $loan->loan_number }}
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-black dark:text-white">
                                    {{ $loan->user?->name ?? __('hrm.default_employee_name') }}
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 tabular-nums font-medium">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    Rp {{ number_format((float)$loan->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                    {{ $loan->tenor_months }} {{ __('hrm.unit_months') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF9500] font-bold">
                                    Rp {{ number_format((float)$loan->monthly_installment, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-extrabold {{ $loan->remaining_balance > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}">
                                    Rp {{ number_format((float)$loan->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($loan->status === 'completed' || $loan->remaining_balance <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_paid_off') }}
                                        </span>
                                    @elseif($loan->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            {{ __('hrm.status_cancelled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.status_active_loan') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end">
                                        @if($loan->status === 'active' && $loan->remaining_balance > 0)
                                            <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_cancel_loan')) }}')">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.btn_cancel') }}"
                                                    aria-label="{{ __('hrm.btn_cancel') }}">
                                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-black/40 dark:text-white/40">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_loans_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($loans as $loan)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="font-bold text-black dark:text-white text-[13px] tabular-nums block truncate">
                                    {{ $loan->loan_number }}
                                </span>
                                <span class="text-[11px] text-black/50 dark:text-white/50">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            @if($loan->status === 'completed' || $loan->remaining_balance <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.status_paid_off') }}
                                </span>
                            @elseif($loan->status === 'cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60 shrink-0">
                                    {{ __('hrm.status_cancelled') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] shrink-0">
                                    {{ __('hrm.status_active_loan') }}
                                </span>
                            @endif
                        </div>

                        <div class="font-bold text-[14px] text-black dark:text-white">
                            {{ $loan->user?->name ?? __('hrm.default_employee_name') }}
                        </div>

                        <!-- Loan Metrics Grid -->
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.principal_ceiling') }}</span>
                                    <span class="font-bold text-black dark:text-white tabular-nums">
                                        Rp {{ number_format((float)$loan->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_tenor') }}</span>
                                    <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $loan->tenor_months }} {{ __('hrm.unit_months') }}
                                    </span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.installment_per_month') }}</span>
                                    <span class="font-bold text-[#FF9500] tabular-nums text-[13px]">
                                        Rp {{ number_format((float)$loan->monthly_installment, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.remaining_balance_label') }}</span>
                                    <span class="font-extrabold tabular-nums text-[13px] {{ $loan->remaining_balance > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}">
                                        Rp {{ number_format((float)$loan->remaining_balance, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($loan->status === 'active' && $loan->remaining_balance > 0)
                            <div class="pt-1">
                                <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_cancel_loan')) }}')">
                                    @csrf
                                    <button type="submit"
                                        class="w-full h-11 px-4 rounded-[12px] text-[13px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                        <span>{{ __('hrm.cancel_remaining_loan') }}</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="credit-card" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_loans_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($loans->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
