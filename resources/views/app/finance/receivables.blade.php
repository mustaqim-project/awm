@extends('layouts.app', ['title' => 'Piutang Usaha (AR Aging)'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12" x-data="{
    showImportModal: false,
    importFile: null,
    importFileName: '',
    importLoading: false,
    importPreview: null,
    importFilter: 'all',
    importDuplicateStrategy: 'skip',
    importExecuting: false,

    formatCurrency(num) {
        return new Intl.NumberFormat(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID').format(num || 0);
    },

    handleImportFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.importFile = file;
            this.importFileName = file.name;
            this.uploadAndPreviewReceivables();
        }
    },

    uploadAndPreviewReceivables() {
        if (!this.importFile) return;
        this.importLoading = true;
        const formData = new FormData();
        formData.append('file', this.importFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('finance.receivables.import-preview') }}', {
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
            this.importLoading = false;
            if (res.success) {
                this.importPreview = res.data;
            } else {
                alert(res.message || 'Gagal menganalisis file piutang.');
            }
        })
        .catch(err => {
            this.importLoading = false;
            alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
        });
    },

    async executeReceivablesImport() {
        if (!this.importPreview || !this.importPreview.rows || this.importPreview.rows.length === 0) {
            return;
        }

        const totalToProcess = this.importDuplicateStrategy === 'skip' 
            ? this.importPreview.valid_count 
            : (this.importPreview.valid_count + this.importPreview.duplicate_count);

        if (totalToProcess <= 0) {
            alert('Tidak ada baris faktur piutang yang siap diimport.');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin memproses ' + totalToProcess + ' faktur piutang ke sistem?')) {
            return;
        }

        this.importExecuting = true;
        fetch('{{ route('finance.receivables.import-execute') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rows: this.importPreview.rows,
                duplicate_strategy: this.importDuplicateStrategy
            })
        })
        .then(res => res.json())
        .then(res => {
            this.importExecuting = false;
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal mengeksekusi import piutang.');
            }
        })
        .catch(err => {
            this.importExecuting = false;
            alert(err.message || 'Terjadi kesalahan sistem saat mengeksekusi import.');
        });
    },

    resetImport() {
        this.importFile = null;
        this.importFileName = '';
        this.importPreview = null;
        this.importFilter = 'all';
        this.importDuplicateStrategy = 'skip';
        this.importLoading = false;
        this.importExecuting = false;
    }
}">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="Piutang Usaha (AR Aging)"
        subtitle="Analisis umur piutang pelanggan dan jadwal penagihan faktur tempo untuk kelancaran arus kas">
        <a href="{{ route('finance.receivables.export') }}"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="Export seluruh daftar piutang usaha ke file Excel (.xlsx)">
            <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>Export Excel</span>
        </a>

        <button type="button" @click="showImportModal = true"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="Import saldo piutang saldo awal / faktur belum lunas dari berkas Excel">
            <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
            <span>Import Saldo Awal</span>
        </button>

        @if(\App\Support\Context::hasPermission('invoices.create'))
            <a href="{{ route('invoices.create') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Faktur Baru</span>
            </a>
        @endif
        @if(\App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#34C759]"></i>
                <span>Daftar Faktur</span>
            </a>
        @endif
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- MODULE TABS (SSOT)                                         --}}
    {{-- ========================================================== --}}
    <x-module-tabs module="finance" />

    @php
        $totalBalance = $totalReceivable ?? $invoices->sum('balance_due');
        $notDue = $notDue ?? $invoices->filter(fn($i) => $i->due_date && $i->due_date->isFuture())->sum('balance_due');
        $overdue1to30 = $overdue1to30 ?? $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            $days = now()->diffInDays($i->due_date);
            return $days >= 0 && $days <= 30;
        })->sum('balance_due');
        $overdue30plus = $overdue30plus ?? $invoices->filter(function($i) {
            if (!$i->due_date || $i->due_date->isFuture()) return false;
            return now()->diffInDays($i->due_date) > 30;
        })->sum('balance_due');
        $totalOpenCount = $totalOpenCount ?? $invoices->total();
        $currentBucket = request('bucket', 'all');
    @endphp

    {{-- ========================================================== --}}
    {{-- AR AGING BENTO TILES                                       --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1.5px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Total Piutang</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-black dark:text-white">Rp {{ number_format($totalBalance, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $totalOpenCount }} Faktur Terbuka</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1.5px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#34C759]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lancar (Belum Tempo)</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5">Sesuai termin penagihan</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1.5px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat 1–30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">Perlu follow-up penagihan</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1.5px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Lewat &gt; 30 Hari</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-0.5">Prioritas penagihan intensif</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                        --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-[12px]">
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Semua
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lancar
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat 1–30 Hari
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                Lewat &gt; 30 Hari
            </a>
        </div>
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. faktur / pelanggan..."
                       class="w-full h-11 sm:h-9 pl-8 pr-3 text-[16px] sm:text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.receivables') }}" class="h-11 sm:h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 flex items-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AR TABLE (DESKTOP)                                         --}}
    {{-- ========================================================== --}}
    <div class="hidden sm:block rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-black dark:text-white">Daftar Piutang Faktur Pelanggan</h2>
            <span class="text-[12px] font-bold text-black/50 dark:text-white/50 tabular-nums px-2.5 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06]">{{ $invoices->total() }} Faktur</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">No. Faktur</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Pelanggan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Tgl. Terbit</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Total Faktur</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Sisa Piutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-center">Umur Piutang</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $invoice)
                    @php
                        $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                        $isOverdue = $daysDiff < 0;
                        $daysOverdue = abs($daysDiff);
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3.5">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                            @else
                            <span class="tabular-nums font-bold text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-black dark:text-white">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</div>
                            @if($invoice->customer?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 tabular-nums">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3.5 tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums text-black/60 dark:text-white/60 font-medium">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[14px]">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-black/70 dark:text-white/70">Semua Piutang Pelanggan Telah Lunas!</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">Tidak ada saldo piutang yang menunggu pembayaran</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <span>{{ $invoices->total() }} faktur</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> Sebelumnya</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- AR LIST CARDS (MOBILE ONLY)                                --}}
    {{-- ========================================================== --}}
    <div class="sm:hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-[14px] font-bold text-black dark:text-white">Daftar Piutang Faktur</h2>
            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ $invoices->total() }} Faktur</span>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-sm">
            @forelse($invoices as $invoice)
            @php
                $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                $isOverdue = $daysDiff < 0;
                $daysOverdue = abs($daysDiff);
            @endphp
            <div class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        @if(\App\Support\Context::hasPermission('invoices.view'))
                        <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-bold text-[14px] text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                            {{ $invoice->invoice_number }}
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                        </a>
                        @else
                        <span class="tabular-nums font-bold text-[14px] text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                        @endif
                        <p class="text-[13px] font-semibold text-black dark:text-white mt-0.5">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</p>
                        @if($invoice->customer?->phone)
                        <p class="text-[11px] text-black/45 dark:text-white/45 tabular-nums flex items-center gap-1 mt-0.5">
                            <i data-lucide="phone" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                            {{ $invoice->customer->phone }}
                        </p>
                        @endif
                    </div>
                    <div>
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Lewat {{ $daysOverdue }} hr
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Sisa {{ $daysDiff }} hr
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[12px] bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[12px]">
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Tgl. Terbit</span>
                        <span class="text-black/70 dark:text-white/70 tabular-nums">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Jatuh Tempo</span>
                        <span class="tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/70 dark:text-white/70' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Total Faktur</span>
                        <span class="tabular-nums text-black/60 dark:text-white/60 font-medium">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">Sisa Piutang</span>
                        <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[13px]">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(\App\Support\Context::hasPermission('invoices.view'))
                <div class="flex items-center justify-end pt-1">
                    <a href="{{ route('invoices.show', $invoice) }}" class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>Lihat Faktur &amp; Catat Bayar</span>
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="py-12 px-4 text-center">
                <div class="flex flex-col items-center gap-2.5">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[14px] font-bold text-black/70 dark:text-white/70">Semua Piutang Telah Lunas!</p>
                        <p class="text-[12px] text-black/40 dark:text-white/40 mt-0.5">Tidak ada saldo piutang yang menunggu pembayaran</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        @if($invoices->hasPages())
        <div class="p-3 bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/5 flex items-center justify-between text-[12px]">
            <span class="text-black/50 dark:text-white/50">{{ $invoices->total() }} faktur</span>
            <div class="flex items-center gap-1.5">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> Prev</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> Prev</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums text-[12px]">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]">Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></a>
                @else
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]">Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL IMPORT PIUTANG PREVIEW (APPLE BENTO XXL MODAL)       --}}
    {{-- ========================================================== --}}
    <div x-show="showImportModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/40 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
            @click.away="if (!importExecuting) showImportModal = false"
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
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">Import Saldo Awal Piutang Usaha (AR)</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Unggah berkas Excel faktur penjualan terbuka / belum lunas untuk cut-off saldo awal</p>
                    </div>
                </div>

                <button type="button" @click="if (!importExecuting) showImportModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1">
                
                <!-- State 1: Drop Zone -->
                <div x-show="!importPreview" class="space-y-4">
                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#0A84FF] rounded-[20px] p-8 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleImportFileSelect($event)" :disabled="importLoading">
                        
                        <div class="space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!importLoading"></i>
                                <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="importLoading"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? 'Membaca dan Menganalisis Faktur Piutang...' : 'Pilih Berkas Excel Piutang atau Tarik ke Sini'"></div>
                                <div class="text-[12px] text-black/50 dark:text-white/50">Mendukung format .XLSX template resmi piutang Cooca</div>
                            </div>
                            <div class="inline-flex items-center gap-2 pt-2">
                                <a href="{{ route('finance.receivables.template') }}"
                                   @click.stop
                                   class="px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[12px] font-semibold flex items-center gap-1.5 transition">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>Unduh Template Excel Piutang Usaha</span>
                                </a>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importPreview" class="space-y-5">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Faktur</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importPreview ? importPreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Siap Import</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importPreview ? importPreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Duplikat</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importPreview ? importPreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Error / Tidak Valid</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importPreview ? importPreview.error_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-[#007AFF]/[0.08] border border-[#007AFF]/20 space-y-1 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">Nilai Piutang</span>
                            <div class="text-lg font-bold font-mono text-[#007AFF]" x-text="importPreview ? ('Rp ' + formatCurrency(importPreview.total_balance_due)) : 0"></div>
                        </div>
                    </div>

                    <!-- Filter & Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                Semua (<span x-text="importPreview ? importPreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Siap (<span x-text="importPreview ? importPreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Duplikat (<span x-text="importPreview ? importPreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Error (<span x-text="importPreview ? importPreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">Jika nomor faktur sudah ada:</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>Lewati</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>Perbarui Sisa Saldo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">Status</th>
                                        <th class="py-2.5 px-3">No. Faktur</th>
                                        <th class="py-2.5 px-3">Pelanggan</th>
                                        <th class="py-2.5 px-3">Tgl Faktur</th>
                                        <th class="py-2.5 px-3">Jatuh Tempo</th>
                                        <th class="py-2.5 px-3 text-right">Total Nilai</th>
                                        <th class="py-2.5 px-3 text-right">Sisa Piutang</th>
                                        <th class="py-2.5 px-3">Keterangan / Diagnostik</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importPreview ? importPreview.rows.filter(r => importFilter === 'all' || r.status === importFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Siap</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">Duplikat</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">Error</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-black dark:text-white" x-text="row.invoice_number || '-'"></td>
                                            <td class="py-2.5 px-3 font-medium text-black dark:text-white" x-text="row.customer_name || '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/70 dark:text-white/70" x-text="row.invoice_date || '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/70 dark:text-white/70" x-text="row.due_date || '-'"></td>
                                            <td class="py-2.5 px-3 text-right font-mono text-black/70 dark:text-white/70" x-text="'Rp ' + formatCurrency(row.total_amount)"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + formatCurrency(row.balance_due)"></td>
                                            <td class="py-2.5 px-3">
                                                <div class="space-y-0.5">
                                                    <template x-for="(msg, mi) in row.messages" :key="mi">
                                                        <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : (row.status === 'duplicate' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="msg"></div>
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
                    <button type="button" x-show="importPreview" @click="resetImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Ganti Berkas</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importExecuting">
                        Batal
                    </button>

                    <button type="button" x-show="importPreview" @click="executeReceivablesImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importExecuting || !importPreview || (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                        <span x-text="importExecuting ? 'Sedang Memproses...' : ('Komit Import (' + (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) + ' Faktur)')"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

