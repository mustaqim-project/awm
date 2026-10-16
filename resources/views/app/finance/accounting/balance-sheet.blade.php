@extends('layouts.app', ['title' => 'Neraca Keuangan (Balance Sheet)'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12" x-data="{
    showOpeningModal: false,
    openingDate: '{{ date('Y-01-01') }}',
    openingFile: null,
    openingFileName: '',
    openingLoading: false,
    openingPreview: null,
    openingFilter: 'all',
    openingExecuting: false,

    formatCurrency(num) {
        return new Intl.NumberFormat(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID').format(num || 0);
    },

    handleOpeningFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.openingFile = file;
            this.openingFileName = file.name;
            this.uploadAndPreviewOpening();
        }
    },

    uploadAndPreviewOpening() {
        if (!this.openingFile) return;
        this.openingLoading = true;
        const formData = new FormData();
        formData.append('file', this.openingFile);
        formData.append('as_of_date', this.openingDate);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('finance.balance-sheet.opening-preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
            }
            return res.json();
        })
        .then(res => {
            this.openingLoading = false;
            if (res.success) {
                this.openingPreview = res.data;
            } else {
                alert(res.message || 'Gagal menganalisis file saldo awal.');
            }
        })
        .catch(err => {
            this.openingLoading = false;
            alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
        });
    },

    async executeOpeningImport() {
        if (!this.openingPreview || !this.openingPreview.rows || this.openingPreview.rows.length === 0) {
            return;
        }

        if (!this.openingPreview.is_balanced) {
            alert('Tidak dapat mengomitsaldo awal: Total Debet dan Kredit belum seimbang! Selisih: Rp ' + this.formatCurrency(Math.abs(this.openingPreview.difference)));
            return;
        }

        if (!confirm('Apakah Anda yakin ingin memproses dan membukukan Jurnal Saldo Awal Neraca ini?')) {
            return;
        }

        this.openingExecuting = true;
        fetch('{{ route('finance.balance-sheet.opening-execute') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                as_of_date: this.openingDate,
                rows: this.openingPreview.rows
            })
        })
        .then(res => res.json())
        .then(res => {
            this.openingExecuting = false;
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal membukukan saldo awal neraca.');
            }
        })
        .catch(err => {
            this.openingExecuting = false;
            alert(err.message || 'Terjadi kesalahan sistem saat membukukan saldo awal.');
        });
    },

    resetOpening() {
        this.openingFile = null;
        this.openingFileName = '';
        this.openingPreview = null;
        this.openingFilter = 'all';
        this.openingLoading = false;
        this.openingExecuting = false;
    }
}">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="Laporan Posisi Keuangan (Neraca)"
            subtitle="Laporan Posisi Keuangan resmi: Aktiva (Aset) = Pasiva (Kewajiban + Ekuitas)">
            <span class="sr-only">SAK EMKM</span>
            <form method="GET" action="{{ route('finance.balance-sheet') }}" class="flex items-center gap-2">
                <div class="relative">
                    <input type="date" name="as_of_date" value="{{ $asOfDate->toDateString() }}"
                        onchange="this.form.submit()"
                        class="h-11 sm:h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>
            </form>
            <button type="button" @click="showOpeningModal = true"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.35)] cursor-pointer"
                title="Import saldo awal neraca (debit = kredit balance) dari file Excel">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>Import Saldo Awal Neraca</span>
            </button>
            <button type="button" onclick="window.print()"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="accounting" />

        {{-- ========================================================== --}}
        {{-- BALANCE STATUS HIGHLIGHT CARD (APPLE HIG BENTO)            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Aktiva (Aset)</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF] mt-1">
                        Rp {{ number_format($sheet['summary']['total_assets'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Per {{ $sheet['formatted_date'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Total Pasiva (Kewajiban + Modal)</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] mt-1">
                        Rp {{ number_format($sheet['summary']['total_liabilities_and_equity'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Hutang &amp; Ekuitas pemilik</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="scale" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Status Keseimbangan</p>
                    <div class="mt-1.5 flex items-center gap-1.5">
                        @if($sheet['summary']['is_balanced'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                Seimbang (Valid 100%)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                Selisih Rp {{ number_format(abs($sheet['summary']['difference']), 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                        {{ $sheet['summary']['is_balanced'] ? 'Aktiva sama dengan pasiva' : 'Periksa penyesuaian jurnal' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-[12px] {{ $sheet['summary']['is_balanced'] ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $sheet['summary']['is_balanced'] ? 'shield-check' : 'alert-circle' }}" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- BALANCE SHEET DUAL-COLUMN BENTO                            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ------------------------------------------------------ --}}
            {{-- KOLOM KIRI: AKTIVA (ASET)                              --}}
            {{-- ------------------------------------------------------ --}}
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden flex flex-col justify-between shadow-xs">
                <div>
                    <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                            <h2 class="text-[15px] font-bold text-black dark:text-white">ASET / AKTIVA</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Sisi Kiri Neraca</span>
                    </div>

                    {{-- 1. Aset Lancar --}}
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Aset Lancar</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Kas, Bank, Piutang, Persediaan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['assets']['current'] as $item)
                                <div class="py-2.5 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/45 dark:text-white/45 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/85 dark:text-white/85 truncate font-medium">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Belum ada akun aset lancar dengan saldo.</div>
                            @endforelse
                        </div>

                        <div class="pt-2.5 flex items-center justify-between text-[13px] font-bold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/65 dark:text-white/65">Subtotal Aset Lancar</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['assets']['total_current'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. Aset Tidak Lancar / Tetap --}}
                    <div class="p-5 pt-0 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Aset Tidak Lancar (Aset Tetap)</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Peralatan, Mesin &amp; Kendaraan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['assets']['non_current'] as $item)
                                <div class="py-2.5 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/45 dark:text-white/45 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/85 dark:text-white/85 truncate font-medium">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Tidak ada aset tidak lancar tercatat.</div>
                            @endforelse
                        </div>

                        <div class="pt-2.5 flex items-center justify-between text-[13px] font-bold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/65 dark:text-white/65">Subtotal Aset Tetap</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['assets']['total_non_current'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Total Aktiva Footer --}}
                <div class="p-5 bg-black/[0.03] dark:bg-white/[0.03] border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[15px] font-bold text-black dark:text-white tracking-tight">TOTAL AKTIVA (ASET)</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                        Rp {{ number_format($sheet['summary']['total_assets'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- ------------------------------------------------------ --}}
            {{-- KOLOM KANAN: PASIVA (KEWAJIBAN & EKUITAS)              --}}
            {{-- ------------------------------------------------------ --}}
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden flex flex-col justify-between shadow-xs">
                <div>
                    <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#5856D6]"></span>
                            <h2 class="text-[15px] font-bold text-black dark:text-white">KEWAJIBAN &amp; EKUITAS (PASIVA)</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">Sisi Kanan Neraca</span>
                    </div>

                    {{-- 1. Kewajiban Jangka Pendek --}}
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Kewajiban / Hutang Lancar</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Hutang Usaha &amp; Pajak</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse($sheet['liabilities']['current'] as $item)
                                <div class="py-2.5 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/45 dark:text-white/45 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/85 dark:text-white/85 truncate font-medium">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-[12px] text-black/40 dark:text-white/40">Belum ada kewajiban lancar tercatat.</div>
                            @endforelse
                        </div>

                        <div class="pt-2.5 flex items-center justify-between text-[13px] font-bold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/65 dark:text-white/65">Subtotal Kewajiban</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['liabilities']['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. Ekuitas / Modal --}}
                    <div class="p-5 pt-0 space-y-3">
                        <div class="flex items-center justify-between pb-1 border-b border-black/[0.06] dark:border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-black/75 dark:text-white/75 uppercase tracking-wide">Ekuitas &amp; Modal Pemilik</h3>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Modal, Saldo Laba &amp; Laba Berjalan</span>
                        </div>

                        <div class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @foreach($sheet['equity']['accounts'] as $item)
                                <div class="py-2.5 flex items-center justify-between text-[13px]">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="tabular-nums font-mono text-[11px] text-black/45 dark:text-white/45 shrink-0">{{ $item['code'] }}</span>
                                        <span class="text-black/85 dark:text-white/85 truncate font-medium">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="tabular-nums font-semibold text-black dark:text-white shrink-0">
                                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Laba Bersih Periode Berjalan --}}
                            <div class="py-2.5 flex items-center justify-between text-[13px] bg-[#34C759]/5 px-3 rounded-[10px] my-1">
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                                    <span class="font-semibold text-[#248A3D] dark:text-[#30D158]">Laba Bersih Periode Berjalan (P&amp;L)</span>
                                </div>
                                <span class="tabular-nums font-bold text-[#248A3D] dark:text-[#30D158] shrink-0">
                                    Rp {{ number_format($sheet['equity']['current_earnings'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2.5 flex items-center justify-between text-[13px] font-bold border-t border-dashed border-black/10 dark:border-white/10">
                            <span class="text-black/65 dark:text-white/65">Subtotal Ekuitas</span>
                            <span class="tabular-nums text-black dark:text-white">
                                Rp {{ number_format($sheet['equity']['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Total Pasiva Footer --}}
                <div class="p-5 bg-black/[0.03] dark:bg-white/[0.03] border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[15px] font-bold text-black dark:text-white tracking-tight">TOTAL PASIVA (KEWAJIBAN + EKUITAS)</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">
                        Rp {{ number_format($sheet['summary']['total_liabilities_and_equity'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

        {{-- ========================================================== --}}
        {{-- MODAL IMPORT SALDO AWAL NERACA (BENTO XXL PRE-COMMIT)      --}}
        {{-- ========================================================== --}}
        <div x-show="showOpeningModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
                @click.away="if (!openingExecuting) showOpeningModal = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Header -->
                <div class="px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="scale" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Import Jurnal Saldo Awal Neraca</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Validasi double-entry ketat SAK EMKM: Total Debet wajib sama dengan Total Kredit</p>
                        </div>
                    </div>

                    <button type="button" @click="if (!openingExecuting) showOpeningModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto space-y-5 flex-1">

                    <!-- As Of Date Selector -->
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Tanggal Cut-Off Saldo Awal</label>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Tanggal efektif pembukuan jurnal saldo awal neraca bisnis</p>
                        </div>
                        <input type="date" x-model="openingDate" :disabled="openingPreview !== null || openingLoading"
                               class="h-10 px-3.5 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>

                    <!-- State 1: Drop Zone -->
                    <div x-show="!openingPreview" class="space-y-4">
                        <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#0A84FF] rounded-[20px] p-8 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01]">
                            <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleOpeningFileSelect($event)" :disabled="openingLoading">
                            
                            <div class="space-y-3">
                                <div class="w-14 h-14 mx-auto rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!openingLoading"></i>
                                    <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="openingLoading"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[15px] font-bold text-black dark:text-white" x-text="openingLoading ? 'Membaca dan Memvalidasi Keseimbangan Debet / Kredit...' : 'Pilih Berkas Excel Saldo Awal atau Tarik ke Sini'"></div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50">Mendukung format .XLSX template resmi neraca Cooca</div>
                                </div>
                                <div class="inline-flex items-center gap-2 pt-2">
                                    <a href="{{ route('finance.balance-sheet.opening-template') }}"
                                       @click.stop
                                       class="px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[12px] font-semibold flex items-center gap-1.5 transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        <span>Unduh Template Excel Saldo Awal Neraca</span>
                                    </a>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- State 2: Pre-Commit Preview -->
                    <div x-show="openingPreview" class="space-y-5">
                        
                        <!-- KPI Summary Cards -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Akun</span>
                                <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="openingPreview ? openingPreview.total_rows : 0"></div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#007AFF]/[0.08] border border-[#007AFF]/20 space-y-1">
                                <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">Total Debet</span>
                                <div class="text-xl font-bold font-mono text-[#007AFF]" x-text="openingPreview ? ('Rp ' + formatCurrency(openingPreview.total_debit)) : 0"></div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#5856D6]/[0.08] border border-[#5856D6]/20 space-y-1">
                                <span class="text-[11px] font-semibold text-[#5856D6] dark:text-[#5E5CE6] uppercase tracking-wider">Total Kredit</span>
                                <div class="text-xl font-bold font-mono text-[#5856D6] dark:text-[#5E5CE6]" x-text="openingPreview ? ('Rp ' + formatCurrency(openingPreview.total_credit)) : 0"></div>
                            </div>
                            <div class="p-3.5 rounded-[16px] space-y-1"
                                 :class="openingPreview && openingPreview.is_balanced ? 'bg-emerald-500/[0.08] border border-emerald-500/20' : 'bg-rose-500/[0.08] border border-rose-500/20'">
                                <span class="text-[11px] font-semibold uppercase tracking-wider"
                                      :class="openingPreview && openingPreview.is_balanced ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'">Keseimbangan</span>
                                <div class="text-xl font-bold font-mono"
                                     :class="openingPreview && openingPreview.is_balanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                     x-text="openingPreview && openingPreview.is_balanced ? 'Seimbang (Valid)' : ('Selisih Rp ' + formatCurrency(Math.abs(openingPreview ? openingPreview.difference : 0)))"></div>
                            </div>
                        </div>

                        <!-- Imbalanced Error Alert -->
                        <div x-show="openingPreview && !openingPreview.is_balanced"
                             class="p-4 rounded-[16px] bg-rose-500/10 border border-rose-500/25 text-rose-800 dark:text-rose-300 flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-600 mt-0.5"></i>
                            <div class="text-[12px] space-y-1">
                                <div class="font-bold text-[13px]">Buku Jurnal Saldo Awal Belum Seimbang (Out of Balance)</div>
                                <p>Prinsip double-entry akuntansi mewajibkan total Debet persis sama dengan total Kredit. Selisih saat ini adalah <strong class="font-mono">Rp <span x-text="formatCurrency(Math.abs(openingPreview ? openingPreview.difference : 0))"></span></strong>. Mohon perbaiki data pada Excel Anda sebelum melakukan komit.</p>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                            <div class="max-h-[380px] overflow-y-auto">
                                <table class="w-full text-left text-[12px] border-collapse">
                                    <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                        <tr>
                                            <th class="py-2.5 px-3 w-16 text-center">Status</th>
                                            <th class="py-2.5 px-3">Kode Akun</th>
                                            <th class="py-2.5 px-3">Nama Akun</th>
                                            <th class="py-2.5 px-3">Klasifikasi</th>
                                            <th class="py-2.5 px-3 text-right">Debet (Rp)</th>
                                            <th class="py-2.5 px-3 text-right">Kredit (Rp)</th>
                                            <th class="py-2.5 px-3">Keterangan / Diagnostik</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                        <template x-for="(row, idx) in (openingPreview ? openingPreview.rows : [])" :key="idx">
                                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                                <td class="py-2.5 px-3 text-center">
                                                    <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Siap</span>
                                                    <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">Error</span>
                                                </td>
                                                <td class="py-2.5 px-3 font-mono font-bold text-black dark:text-white" x-text="row.account_code || '-'"></td>
                                                <td class="py-2.5 px-3 font-medium text-black dark:text-white" x-text="row.account_name || '-'"></td>
                                                <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                    <span class="inline-block uppercase text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 font-semibold" x-text="row.type || '-'"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-black dark:text-white" x-text="row.debit > 0 ? formatCurrency(row.debit) : '-'"></td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-black dark:text-white" x-text="row.credit > 0 ? formatCurrency(row.credit) : '-'"></td>
                                                <td class="py-2.5 px-3">
                                                    <div class="space-y-0.5">
                                                        <template x-for="(msg, mi) in row.messages" :key="mi">
                                                            <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="msg"></div>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                    <div>
                        <button type="button" x-show="openingPreview" @click="resetOpening()"
                            class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                            :disabled="openingExecuting">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            <span>Ganti Berkas</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="if (!openingExecuting) showOpeningModal = false"
                            class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                            :disabled="openingExecuting">
                            Batal
                        </button>

                        <button type="button" x-show="openingPreview" @click="executeOpeningImport()"
                            class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="openingExecuting || !openingPreview || !openingPreview.is_balanced || openingPreview.valid_count === 0">
                            <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="openingExecuting"></i>
                            <i data-lucide="check" class="w-4 h-4" x-show="!openingExecuting"></i>
                            <span x-text="openingExecuting ? 'Membukukan...' : ('Komit Jurnal Saldo Awal (' + (openingPreview ? openingPreview.valid_count : 0) + ' Baris)')"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
