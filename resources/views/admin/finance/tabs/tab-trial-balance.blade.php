{{-- TAB 4: NERACA SALDO (TRIAL BALANCE) --}}
<div x-show="activeTab === 'trial_balance'" class="space-y-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.neraca_saldo_trial_balance') }}</h2>
            <p class="text-[12.5px] text-black/50 dark:text-white/50">
                Pemeriksaan kepatuhan prinsip pembukuan berpasangan (Double-Entry). Total saldo akhir Debit wajib sama dengan Kredit.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold {{ $trialBalanceData['isBalanced'] ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]' }}">
                <i data-lucide="{{ $trialBalanceData['isBalanced'] ? 'check-circle-2' : 'alert-circle' }}" class="w-4 h-4"></i>
                <span>{{ $trialBalanceData['isBalanced'] ? 'Status: Seimbang (Balanced)' : 'Status: Tidak Seimbang' }}</span>
            </span>

            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.export'))
                <a href="{{ route('admin.finance.export', array_merge(request()->query(), ['tab' => 'trial_balance'])) }}"
                    class="h-9 px-3 rounded-[10px] text-[12px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>{{ __('admin.ekspor_neraca_saldo') }}</span>
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-[12.5px] text-left">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                    <tr>
                        <th class="px-5 py-3.5">Kode &amp; Akun Perkiraan</th>
                        <th class="px-4 py-3.5">Tipe &amp; Kategori</th>
                        <th class="px-4 py-3.5 text-center">Posisi Normal</th>
                        <th class="px-4 py-3.5 text-right">Mutasi Debit</th>
                        <th class="px-4 py-3.5 text-right">Mutasi Kredit</th>
                        <th class="px-4 py-3.5 text-right">Saldo Akhir Debit (IDR)</th>
                        <th class="px-5 py-3.5 text-right">Saldo Akhir Kredit (IDR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 font-mono">
                    @foreach($trialBalanceData['accounts'] as $row)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015]">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 font-sans">
                                    <span class="font-mono font-bold text-black dark:text-white">{{ $row['code'] }}</span>
                                    <span class="text-black/80 dark:text-white/80 font-medium">{{ $row['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-sans">
                                <span class="text-[11px] font-medium text-black/60 dark:text-white/60">
                                    {{ \App\Models\PlatformChartOfAccount::typeLabels()[$row['type']] ?? $row['type'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-sans">
                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold {{ $row['normal_balance'] === 'debit' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#AF52DE]/10 text-[#AF52DE]' }}">
                                    {{ strtoupper($row['normal_balance']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-black/50 dark:text-white/50 tabular-nums">
                                {{ $row['mutation_debit'] > 0 ? number_format($row['mutation_debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-black/50 dark:text-white/50 tabular-nums">
                                {{ $row['mutation_credit'] > 0 ? number_format($row['mutation_credit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-black dark:text-white tabular-nums">
                                {{ $row['ending_debit'] > 0 ? 'Rp ' . number_format($row['ending_debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-black dark:text-white tabular-nums">
                                {{ $row['ending_credit'] > 0 ? 'Rp ' . number_format($row['ending_credit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-black/[0.03] dark:bg-white/[0.04] font-mono text-[13px] font-bold border-t-2 border-black/10 dark:border-white/10">
                    <tr>
                        <td colspan="5" class="px-5 py-3 text-right font-sans uppercase text-[11px] text-black/60 dark:text-white/60">
                            Total Saldo Akhir:
                        </td>
                        <td class="px-4 py-3 text-right text-[#007AFF] tabular-nums">
                            Rp {{ number_format($trialBalanceData['totalDebit'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right text-[#007AFF] tabular-nums">
                            Rp {{ number_format($trialBalanceData['totalCredit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
