{{-- TAB 3: BUKU BESAR (GENERAL LEDGER PER AKUN) --}}
<div x-show="activeTab === 'ledger'" class="space-y-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-[16px] font-bold text-black dark:text-white">Buku Besar Akun (General Ledger)</h2>
            <p class="text-[12.5px] text-black/50 dark:text-white/50">
                Riwayat mutasi kronologis dan kalkulasi saldo berjalan (running balance) per akun perkiraan.
            </p>
        </div>

        {{-- Switcher Akun --}}
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <input type="hidden" name="tab" value="ledger">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            
            <label for="account_id" class="text-[12px] font-medium text-black/60 dark:text-white/60 shrink-0">Pilih Akun:</label>
            <select name="account_id" id="account_id" onchange="this.form.submit()"
                class="h-9.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] shadow-xs">
                @foreach($allAccounts as $acc)
                    <option value="{{ $acc->id }}" {{ (isset($ledgerData['account']) && $ledgerData['account']->id === $acc->id) ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->normal_balance) }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($ledgerData)
        {{-- Card Info Akun Terpilih --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wider">Akun Terpilih</div>
                <div class="text-[15px] font-bold text-black dark:text-white mt-1">
                    {{ $ledgerData['account']->code }} - {{ $ledgerData['account']->name }}
                </div>
                <div class="text-[11px] font-semibold text-[#007AFF] mt-0.5">
                    Tipe: {{ \App\Models\PlatformChartOfAccount::typeLabels()[$ledgerData['account']->type] ?? $ledgerData['account']->type }}
                </div>
            </div>

            <div class="rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wider">Total Mutasi Debit</div>
                <div class="text-[18px] font-bold text-black dark:text-white mt-1 tabular-nums">
                    Rp {{ number_format((float) $ledgerData['totalDebit'], 0, ',', '.') }}
                </div>
            </div>

            <div class="rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wider">Total Mutasi Kredit</div>
                <div class="text-[18px] font-bold text-black dark:text-white mt-1 tabular-nums">
                    Rp {{ number_format((float) $ledgerData['totalCredit'], 0, ',', '.') }}
                </div>
            </div>

            <div class="rounded-[16px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-medium text-black/45 dark:text-white/45 uppercase tracking-wider">Saldo Akhir Periode</div>
                <div class="text-[18px] font-bold {{ $ledgerData['endingBalance'] >= 0 ? 'text-[#007AFF]' : 'text-[#FF3B30]' }} mt-1 tabular-nums">
                    Rp {{ number_format((float) $ledgerData['endingBalance'], 0, ',', '.') }}
                </div>
                <div class="text-[10.5px] text-black/40 dark:text-white/40 mt-0.5">
                    Posisi Normal: {{ strtoupper($ledgerData['account']->normal_balance) }}
                </div>
            </div>
        </div>

        {{-- Tabel Buku Besar --}}
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-[12.5px] text-left">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">No. Jurnal</th>
                            <th class="px-4 py-3.5">Keterangan</th>
                            <th class="px-4 py-3.5">Catatan Garis</th>
                            <th class="px-4 py-3.5 text-right">Debit (IDR)</th>
                            <th class="px-4 py-3.5 text-right">Kredit (IDR)</th>
                            <th class="px-5 py-3.5 text-right">Saldo Berjalan (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5 font-mono">
                        @forelse($ledgerData['rows'] as $row)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015]">
                                <td class="px-5 py-3.5 whitespace-nowrap text-black/60 dark:text-white/60 font-sans">
                                    {{ $row['entry_date'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-[#007AFF]">
                                    {{ $row['entry_number'] }}
                                </td>
                                <td class="px-4 py-3.5 font-sans font-medium text-black dark:text-white min-w-[200px]">
                                    {{ $row['description'] }}
                                </td>
                                <td class="px-4 py-3.5 font-sans text-black/50 dark:text-white/50">
                                    {{ $row['notes'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-black dark:text-white tabular-nums">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-black dark:text-white tabular-nums">
                                    {{ $row['credit'] > 0 ? number_format($row['credit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold {{ $row['running_balance'] >= 0 ? 'text-black dark:text-white' : 'text-[#FF3B30]' }} tabular-nums">
                                    Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center font-sans text-black/40 dark:text-white/40">
                                    Belum ada mutasi untuk akun ini pada periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
