{{-- TAB 5: LAPORAN KEUANGAN RESMI (NERACA & LABA RUGI KOMPREHENSIF) --}}
<div x-show="activeTab === 'balance_sheet'" class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-[16px] font-bold text-black dark:text-white">Laporan Keuangan Platform Resmi</h2>
            <p class="text-[12.5px] text-black/50 dark:text-white/50">
                Penyajian Laporan Posisi Keuangan (Neraca) dan Laporan Laba Rugi Komprehensif standar akuntansi korporat.
            </p>
        </div>
        <div class="text-[11.5px] font-mono text-black/45 dark:text-white/45 bg-black/[0.03] dark:bg-white/[0.05] px-3 py-1.5 rounded-[10px]">
            Posisi per: {{ \Carbon\Carbon::parse($balanceSheetData['asOfDate'])->format('d F Y') }}
        </div>
    </div>

    {{-- KARTU VERIFIKASI PERSAMAAN DASAR AKUNTANSI (ASET = KEWAJIBAN + EKUITAS) --}}
    <div class="rounded-[18px] p-4 bg-gradient-to-r {{ $balanceSheetData['isBalanced'] ? 'from-[#34C759]/12 via-[#34C759]/8 to-transparent border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158]' : 'from-[#FF3B30]/12 via-[#FF3B30]/8 to-transparent border border-[#FF3B30]/25 text-[#C41E17] dark:text-[#FF453A]' }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] {{ $balanceSheetData['isBalanced'] ? 'bg-[#34C759]/20' : 'bg-[#FF3B30]/20' }} flex items-center justify-center shrink-0">
                <i data-lucide="{{ $balanceSheetData['isBalanced'] ? 'scale' : 'alert-triangle' }}" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[13.5px] font-bold">
                    {{ $balanceSheetData['isBalanced'] ? 'Persamaan Neraca Terverifikasi Seimbang: Aset = Kewajiban + Ekuitas' : 'Peringatan: Terdapat Selisih pada Neraca' }}
                </div>
                <div class="text-[11.5px] opacity-80 mt-0.5 font-mono">
                    Total Aset: Rp {{ number_format($balanceSheetData['totalAssets'], 0, ',', '.') }} 
                    = Total Kewajiban &amp; Ekuitas: Rp {{ number_format($balanceSheetData['totalLiabilitiesAndEquity'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- DUA KOLOM BENTO: NERACA (KIRI: ASET, KANAN: KEWAJIBAN & EKUITAS) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- KOLOM KIRI: ASET / AKTIVA --}}
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                    <h3 class="text-[14px] font-bold text-black dark:text-white uppercase tracking-wider">Aset / Aktiva</h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/12 text-[#007AFF]">Debet</span>
                </div>

                <div class="divide-y divide-black/5 dark:divide-white/5 font-mono text-[12.5px] mt-2">
                    @forelse($balanceSheetData['assets'] as $asset)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="font-sans">
                                <span class="font-mono text-black/50 dark:text-white/50 text-[11px] mr-1.5">{{ $asset['code'] }}</span>
                                <span class="font-medium text-black dark:text-white">{{ $asset['name'] }}</span>
                            </div>
                            <span class="font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($asset['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-4 text-center text-black/40 dark:text-white/40 font-sans text-[12px]">Belum ada akun aset</div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t-2 border-black/10 dark:border-white/10 flex items-center justify-between font-mono text-[14px] font-bold mt-4">
                <span class="font-sans uppercase text-[12px] text-black/60 dark:text-white/60">Total Aset (Aktiva):</span>
                <span class="text-[#007AFF] tabular-nums">Rp {{ number_format($balanceSheetData['totalAssets'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- KOLOM KANAN: KEWAJIBAN & EKUITAS --}}
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
            <div class="space-y-4">
                {{-- 1. Kewajiban --}}
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                        <h3 class="text-[14px] font-bold text-black dark:text-white uppercase tracking-wider">Kewajiban / Liabilitas</h3>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">Kredit</span>
                    </div>

                    <div class="divide-y divide-black/5 dark:divide-white/5 font-mono text-[12.5px] mt-2">
                        @forelse($balanceSheetData['liabilities'] as $liab)
                            <div class="py-2 flex items-center justify-between">
                                <div class="font-sans">
                                    <span class="font-mono text-black/50 dark:text-white/50 text-[11px] mr-1.5">{{ $liab['code'] }}</span>
                                    <span class="font-medium text-black dark:text-white">{{ $liab['name'] }}</span>
                                </div>
                                <span class="font-bold text-black dark:text-white tabular-nums">
                                    Rp {{ number_format($liab['amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <div class="py-2 text-center text-black/40 dark:text-white/40 font-sans text-[12px]">Belum ada akun kewajiban</div>
                        @endforelse
                        <div class="py-2 flex items-center justify-between font-bold text-black/60 dark:text-white/60 text-[11.5px]">
                            <span>Subtotal Kewajiban:</span>
                            <span class="tabular-nums">Rp {{ number_format($balanceSheetData['totalLiabilities'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Ekuitas / Modal --}}
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                        <h3 class="text-[14px] font-bold text-black dark:text-white uppercase tracking-wider">Ekuitas / Modal</h3>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#AF52DE]/12 text-[#AF52DE]">Kredit</span>
                    </div>

                    <div class="divide-y divide-black/5 dark:divide-white/5 font-mono text-[12.5px] mt-2">
                        @foreach($balanceSheetData['equity'] as $eq)
                            <div class="py-2 flex items-center justify-between">
                                <div class="font-sans">
                                    <span class="font-mono text-black/50 dark:text-white/50 text-[11px] mr-1.5">{{ $eq['code'] }}</span>
                                    <span class="font-medium text-black dark:text-white">{{ $eq['name'] }}</span>
                                </div>
                                <span class="font-bold text-black dark:text-white tabular-nums">
                                    Rp {{ number_format($eq['amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                        {{-- Laba Berjalan --}}
                        <div class="py-2 flex items-center justify-between bg-black/[0.015] dark:bg-white/[0.02] px-2 rounded-[8px]">
                            <div class="font-sans font-semibold text-black dark:text-white">
                                Laba Bersih Periode Berjalan (P&amp;L)
                            </div>
                            <span class="font-bold {{ $balanceSheetData['currentPeriodNetIncome'] >= 0 ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-[#FF3B30]' }} tabular-nums">
                                Rp {{ number_format($balanceSheetData['currentPeriodNetIncome'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="py-2 flex items-center justify-between font-bold text-black/60 dark:text-white/60 text-[11.5px]">
                            <span>Subtotal Ekuitas:</span>
                            <span class="tabular-nums">Rp {{ number_format($balanceSheetData['totalEquity'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t-2 border-black/10 dark:border-white/10 flex items-center justify-between font-mono text-[14px] font-bold mt-4">
                <span class="font-sans uppercase text-[12px] text-black/60 dark:text-white/60">Total Kewajiban &amp; Ekuitas:</span>
                <span class="text-[#007AFF] tabular-nums">Rp {{ number_format($balanceSheetData['totalLiabilitiesAndEquity'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- KARTU LABA RUGI KOMPREHENSIF (PROFIT & LOSS) --}}
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-[14px] font-bold text-black dark:text-white uppercase tracking-wider">Laporan Laba Rugi Komprehensif (Income Statement)</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Periode: {{ \Carbon\Carbon::parse($pnlData['startDate'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($pnlData['endDate'])->format('d M Y') }}</p>
            </div>
            <span class="text-[13px] font-bold text-[#007AFF] tabular-nums">
                Margin: {{ $pnlData['profitMargin'] }}%
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            {{-- Pendapatan --}}
            <div>
                <div class="text-[12px] font-bold uppercase text-[#248A3D] dark:text-[#30D158] tracking-wider mb-2">1. Pendapatan Usaha Platform</div>
                <div class="divide-y divide-black/5 dark:divide-white/5 font-mono text-[12.5px]">
                    @forelse($pnlData['revenues'] as $rev)
                        <div class="py-2 flex items-center justify-between">
                            <span class="font-sans text-black/80 dark:text-white/80 font-medium">{{ $rev['name'] }}</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-black/40 dark:text-white/40 font-sans text-[12px]">Belum ada pendapatan</div>
                    @endforelse
                    <div class="py-2.5 flex items-center justify-between font-bold text-black dark:text-white border-t border-black/10 dark:border-white/10">
                        <span class="font-sans uppercase text-[11px]">Total Pendapatan:</span>
                        <span class="text-[#248A3D] dark:text-[#30D158] tabular-nums">Rp {{ number_format($pnlData['totalRevenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Beban --}}
            <div>
                <div class="text-[12px] font-bold uppercase text-[#C41E17] dark:text-[#FF453A] tracking-wider mb-2">2. Beban Pokok &amp; Operasional</div>
                <div class="divide-y divide-black/5 dark:divide-white/5 font-mono text-[12.5px]">
                    @forelse($pnlData['expenses'] as $exp)
                        <div class="py-2 flex items-center justify-between">
                            <span class="font-sans text-black/80 dark:text-white/80 font-medium">{{ $exp['name'] }}</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($exp['amount'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-black/40 dark:text-white/40 font-sans text-[12px]">Belum ada beban operasional</div>
                    @endforelse
                    <div class="py-2.5 flex items-center justify-between font-bold text-black dark:text-white border-t border-black/10 dark:border-white/10">
                        <span class="font-sans uppercase text-[11px]">Total Beban:</span>
                        <span class="text-[#C41E17] dark:text-[#FF453A] tabular-nums">Rp {{ number_format($pnlData['totalExpense'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Income Result Card --}}
        <div class="mt-6 pt-4 border-t border-black/10 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 bg-black/[0.02] dark:bg-white/[0.02] p-4 rounded-[14px]">
            <div>
                <div class="text-[14px] font-bold text-black dark:text-white">Laba Bersih Operasional (Net Income)</div>
                <div class="text-[11.5px] text-black/50 dark:text-white/50">Total Pendapatan dikurangi Seluruh Beban Operasional Platform</div>
            </div>
            <div class="text-[20px] font-bold {{ $pnlData['netProfit'] >= 0 ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-[#FF3B30]' }} font-mono tabular-nums">
                Rp {{ number_format($pnlData['netProfit'], 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
