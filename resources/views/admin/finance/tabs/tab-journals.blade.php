{{-- TAB 2: JURNAL UMUM (DOUBLE-ENTRY GENERAL JOURNAL) --}}
<div x-show="activeTab === 'journals'" class="space-y-4">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-[16px] font-bold text-black dark:text-white">Jurnal Umum Berpasangan (Double-Entry Ledger)</h2>
            <p class="text-[12.5px] text-black/50 dark:text-white/50">
                Pencatatan mutasi akuntansi debit dan kredit seimbang (Total Debit = Total Kredit) yang dihasilkan otomatis dari transaksi sistem maupun entri manual.
            </p>
        </div>
        @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
            <button type="button" @click="openCreateJournal()"
                class="h-9.5 px-4 rounded-[12px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Input Jurnal Manual</span>
            </button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($journalEntries as $entry)
            <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs transition-all hover:border-black/15 dark:hover:border-white/20">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 pb-3 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="font-mono text-[13px] font-bold text-black dark:text-white">
                            {{ $entry->entry_number }}
                        </span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                            {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $entry->is_balanced ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]' }}">
                            <i data-lucide="{{ $entry->is_balanced ? 'check' : 'alert-circle' }}" class="w-3 h-3"></i>
                            {{ $entry->is_balanced ? 'Balanced' : 'Unbalanced' }}
                        </span>
                        <span class="text-[11px] text-black/40 dark:text-white/40">
                            Ref: {{ ucfirst(str_replace('_', ' ', $entry->reference_type ?? 'manual')) }}
                        </span>
                    </div>

                    <div class="text-[11px] text-black/40 dark:text-white/40">
                        Oleh: {{ $entry->admin?->name ?? 'Sistem Otomatis' }}
                    </div>
                </div>

                <div class="mt-2.5 text-[13px] font-medium text-black/80 dark:text-white/85">
                    {{ $entry->description }}
                </div>

                {{-- Table Debit & Credit Lines --}}
                <div class="mt-3 overflow-x-auto rounded-[12px] border border-black/5 dark:border-white/5">
                    <table class="w-full text-[12px] text-left">
                        <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-[10.5px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40">
                            <tr>
                                <th class="px-3.5 py-2">Kode &amp; Akun Perkiraan</th>
                                <th class="px-3.5 py-2">Catatan / Baris</th>
                                <th class="px-3.5 py-2 text-right">Debit (IDR)</th>
                                <th class="px-3.5 py-2 text-right">Kredit (IDR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5 font-mono">
                            @foreach($entry->lines as $line)
                                <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                                    <td class="px-3.5 py-2">
                                        <div class="flex items-center gap-1.5 font-sans">
                                            @if($line->isCredit())
                                                <span class="text-black/30 dark:text-white/30 pl-4">↳</span>
                                            @endif
                                            <span class="font-mono font-bold text-black dark:text-white">{{ $line->account?->code ?? '-' }}</span>
                                            <span class="text-black/75 dark:text-white/75">{{ $line->account?->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-2 text-black/50 dark:text-white/50 font-sans">
                                        {{ $line->notes ?? '-' }}
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-bold text-black dark:text-white tabular-nums">
                                        {{ $line->isDebit() ? 'Rp ' . number_format((float) $line->amount, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-bold text-black dark:text-white tabular-nums">
                                        {{ $line->isCredit() ? 'Rp ' . number_format((float) $line->amount, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-black/[0.02] dark:bg-white/[0.02] font-mono text-[12px] font-bold border-t border-black/10 dark:border-white/10">
                            <tr>
                                <td colspan="2" class="px-3.5 py-2 text-right font-sans uppercase text-[10.5px] text-black/50 dark:text-white/50">Total Keseimbangan:</td>
                                <td class="px-3.5 py-2 text-right text-[#007AFF] tabular-nums">Rp {{ number_format((float) $entry->total_debit, 0, ',', '.') }}</td>
                                <td class="px-3.5 py-2 text-right text-[#007AFF] tabular-nums">Rp {{ number_format((float) $entry->total_credit, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-12 text-center text-black/40 dark:text-white/40 shadow-xs">
                <div class="w-12 h-12 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="book-open-check" class="w-6 h-6"></i>
                </div>
                <div class="text-[14px] font-bold text-black/70 dark:text-white/70">{{ __('admin.belum_ada_jurnal_umum_pada_periode') }}</div>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-1 max-w-sm mx-auto">
                    Setiap transaksi kas masuk dan keluar yang tercatat otomatis menghasilkan entri jurnal berpasangan di sini.
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($journalEntries->hasPages())
            <div class="rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3 shadow-xs">
                {{ $journalEntries->links() }}
            </div>
        @endif
    </div>
</div>
