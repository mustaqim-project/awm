@extends('layouts.admin', [
    'title' => 'Buku Kas & Akuntansi Platform - Admin Console',
    'headerTitle' => 'Finance & Accounting Platform',
    'headerSubtitle' => 'Buku kas arus kas, laba rugi, dan rekonsiliasi otomatis terintegrasi dengan Billing Subscriptions dan Merchant Settlements',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeTab: '{{ $activeTab }}',
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showImageModal: false,
    modalImageUrl: '',
    modalImageTitle: '',

    // Edit form state
    editTrx: null,
    editActionUrl: '',

    // Delete form state
    deleteTrx: null,
    deleteActionUrl: '',

    // Create form preview
    createImagePreview: null,
    editImagePreview: null,

    previewImage(url, title) {
        this.modalImageUrl = url;
        this.modalImageTitle = title || 'Lampiran Bukti Transaksi';
        this.showImageModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreate() {
        this.createImagePreview = null;
        this.showCreateModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openEdit(trx) {
        this.editTrx = trx;
        this.editActionUrl = '/admin/finance/' + trx.id;
        this.editImagePreview = trx.attachment_url || null;
        this.showEditModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openDelete(trx) {
        this.deleteTrx = trx;
        this.deleteActionUrl = '/admin/finance/' + trx.id;
        this.showDeleteModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    handleFileSelect(event, target) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                if (target === 'create') this.createImagePreview = e.target.result;
                if (target === 'edit') this.editImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">

    {{-- 1. TOP HEADER & ACTION TOOLBAR --}}
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/45 dark:text-white/45 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#007AFF] transition-colors">Admin Console</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/60 dark:text-white/60 font-medium">Finansial &amp; Paket</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Buku Kas &amp; Akuntansi</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Buku Kas &amp; Akuntansi Platform</h1>
            <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5">
                Pusat pencatatan arus kas, laba rugi, dan rekonsiliasi otomatis terintegrasi dengan 
                <a href="{{ route('admin.subscriptions.index') }}" class="text-[#007AFF] hover:underline font-semibold">Billing Subscriptions</a> &amp; 
                <a href="{{ route('admin.settlements.index') }}" class="text-[#007AFF] hover:underline font-semibold">Settlement Merchant</a>.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 flex-wrap">
            {{-- Tombol Sinkronisasi Billing & Settlement --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.sync'))
                <form action="{{ route('admin.finance.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                        title="Tarik transaksi baru dari Billing Subscriptions dan Settlement Merchant">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Sinkronisasi Otomatis</span>
                    </button>
                </form>
            @endif

            {{-- Tombol Ekspor CSV --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.export'))
                <a href="{{ route('admin.finance.export', request()->query()) }}"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor CSV</span>
                </a>
            @endif

            {{-- Tombol Catat Transaksi Baru --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                <button type="button" @click="openCreate()"
                    class="h-10 px-4.5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Transaksi</span>
                </button>
            @endif
        </div>
    </header>

    {{-- ALERT FLASH MESSAGES --}}
    @if(session('success'))
        <div class="rounded-[16px] bg-[#34C759]/12 border border-[#34C759]/25 p-4 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1 font-semibold">{{ session('success') }}</div>
        </div>
    @endif

    @if(!empty($errors) && (is_array($errors) ? count($errors) > 0 : $errors->any()))
        <div class="rounded-[16px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-start gap-3 shadow-xs">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold space-y-1">
                @foreach(is_array($errors) ? $errors : $errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PENDING SYNC BANNER CALLOUT --}}
    @if($unsyncedSubscriptionsCount > 0 || $unsyncedSettlementsCount > 0)
        <div class="rounded-[18px] p-4 bg-gradient-to-r from-[#FF9500]/15 via-[#FF9500]/10 to-transparent border border-[#FF9500]/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[13.5px] font-bold text-black dark:text-white">
                        Terdapat Data Transaksi Baru Belum Dibukukan
                    </div>
                    <div class="text-[12px] text-black/60 dark:text-white/60">
                        {{ $unsyncedSubscriptionsCount }} langganan disetujui &amp; {{ $unsyncedSettlementsCount }} pencairan saldo selesai siap disinkronkan ke buku kas.
                    </div>
                </div>
            </div>
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.sync'))
                <form action="{{ route('admin.finance.sync') }}" method="POST" class="shrink-0 w-full sm:w-auto">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto h-9 px-4 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12.5px] font-bold transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Sinkronkan Sekarang</span>
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- 2. BENTO STATS / KPI FINANCIAL PULSE --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Inflow (Kas Masuk) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Penerimaan Kas (Inflow)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                    Rp {{ number_format($totalInflow, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Periode Terpilih
                </div>
            </div>
        </div>

        {{-- Total Outflow (Kas Keluar) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Pengeluaran Kas (Outflow)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#FF3B30] dark:text-[#FF453A] tabular-nums tracking-tight">
                    Rp {{ number_format($totalOutflow, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Settlement &amp; Beban Operasional
                </div>
            </div>
        </div>

        {{-- Net Profit / Surplus --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Laba Bersih (Net Profit)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[22px] sm:text-[24px] font-bold {{ $netProfit >= 0 ? 'text-black dark:text-white' : 'text-[#FF3B30]' }} tabular-nums tracking-tight">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <div class="text-[11px] font-semibold text-[#007AFF] mt-0.5 flex items-center gap-1">
                    <span>Margin: {{ $profitMargin }}%</span>
                </div>
            </div>
        </div>

        {{-- Total Transaksi --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Total Transaksi</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[22px] sm:text-[24px] font-bold text-[#5856D6] dark:text-[#5E5CE6] tabular-nums tracking-tight">
                    {{ number_format($totalTransactionsCount, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Jurnal Buku Kas
                </div>
            </div>
        </div>
    </div>

    {{-- 3. FILTER BAR & DATE PICKER --}}
    <form method="GET" action="{{ route('admin.finance.index') }}"
        class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 shadow-xs">
        
        <input type="hidden" name="tab" :value="activeTab">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 flex-1">
            {{-- Rentang Tanggal Awal & Akhir --}}
            <div class="flex items-center gap-1.5">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <span class="text-black/40 dark:text-white/40 text-[12px]">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            {{-- Filter Tipe --}}
            <select name="type"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Arus Kas</option>
                <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Kas Masuk (Inflow)</option>
                <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Kas Keluar (Outflow)</option>
            </select>

            {{-- Filter Kategori --}}
            <select name="category"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Kategori Akun</option>
                @foreach($categoryLabels as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Filter Sumber --}}
            <select name="source"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Sumber</option>
                <option value="subscription_payment" {{ $source === 'subscription_payment' ? 'selected' : '' }}>Billing Subscriptions</option>
                <option value="payment_settlement" {{ $source === 'payment_settlement' ? 'selected' : '' }}>Settlement Merchant</option>
                <option value="manual" {{ $source === 'manual' ? 'selected' : '' }}>Manual Entry</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative flex-1 sm:w-56">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="No. TRX, judul, catatan..."
                    class="w-full h-9.5 pl-8 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            <button type="submit"
                class="h-9.5 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold shadow-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.finance.index') }}"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/70 dark:text-white/70 text-[12.5px] font-medium flex items-center justify-center"
                title="Reset Filter">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </form>

    {{-- 4. SEGMENTED NAVIGATION TABS --}}
    <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-2">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium">
            <button type="button" @click="activeTab = 'journal'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'journal' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Jurnal Buku Kas</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $transactions->total() }}</span>
            </button>

            <button type="button" @click="activeTab = 'pnl'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'pnl' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="pie-chart" class="w-4 h-4 text-[#34C759]"></i>
                <span>Laporan Laba Rugi (P&amp;L)</span>
            </button>

            <button type="button" @click="activeTab = 'reconciliation'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'reconciliation' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>Integrasi Billing &amp; Settlement</span>
            </button>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 1: JURNAL BUKU KAS TRANSAKSI (CASHFLOW LEDGER)    --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'journal'" class="space-y-4">
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px] text-left">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                        <tr>
                            <th class="px-5 py-3.5">No. Transaksi &amp; Tanggal</th>
                            <th class="px-4 py-3.5">Tipe &amp; Kategori</th>
                            <th class="px-4 py-3.5">Judul &amp; Referensi</th>
                            <th class="px-4 py-3.5">Metode / Rekening</th>
                            <th class="px-4 py-3.5 text-right">Nominal (IDR)</th>
                            <th class="px-4 py-3.5 text-center">Lampiran Bukti</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                                {{-- 1. No TRX & Tanggal --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">{{ $trx->transaction_number }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $trx->transaction_date->format('d M Y') }}</div>
                                </td>

                                {{-- 2. Tipe & Kategori --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($trx->isInflow())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                                <span>Masuk</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                                <span>Keluar</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-medium text-black/60 dark:text-white/60 mt-1">
                                        {{ $categoryLabels[$trx->category] ?? $trx->category }}
                                    </div>
                                </td>

                                {{-- 3. Judul & Referensi Sumber --}}
                                <td class="px-4 py-3.5 max-w-xs">
                                    <div class="font-bold text-black dark:text-white truncate" title="{{ $trx->title }}">
                                        {{ $trx->title }}
                                    </div>
                                    
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-black/45 dark:text-white/45">
                                        @if($trx->source_type === 'subscription_payment' && $trx->source_id)
                                            <a href="{{ route('admin.subscriptions.show', $trx->source_id) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[#007AFF] hover:underline font-semibold"
                                                title="Buka rincian tagihan langganan">
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                                <span>Billing Ref</span>
                                            </a>
                                        @elseif($trx->source_type === 'payment_settlement' && $trx->source_id)
                                            <a href="{{ route('admin.settlements.show', $trx->source_id) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[#FF9500] hover:underline font-semibold"
                                                title="Buka rincian pencairan settlement">
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                                <span>Settlement Ref</span>
                                            </a>
                                        @else
                                            <span class="text-black/40 dark:text-white/40">Manual Entry ({{ $trx->admin?->name ?? 'Admin' }})</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- 4. Rekening / Metode Pembayaran --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-black/80 dark:text-white/80 text-[12px] flex items-center gap-1.5">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5 opacity-40"></i>
                                        <span>{{ $trx->payment_method ?: 'Kas Tunai / Bank' }}</span>
                                    </div>
                                    @if($trx->notes)
                                        <div class="text-[10.5px] text-black/45 dark:text-white/45 truncate max-w-[180px]" title="{{ $trx->notes }}">
                                            {{ $trx->notes }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 5. Nominal (IDR) --}}
                                <td class="px-4 py-3.5 text-right whitespace-nowrap font-mono">
                                    <div class="text-[13.5px] font-bold {{ $trx->isInflow() ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                                        {{ $trx->isInflow() ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- 6. Lampiran Bukti (Optional Image Attachment Thumbnail / Zoom) --}}
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($trx->attachment_url)
                                        <button type="button"
                                            @click="previewImage('{{ $trx->attachment_url }}', 'Bukti {{ $trx->transaction_number }} - {{ addslashes($trx->title) }}')"
                                            class="inline-flex items-center gap-1.5 p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 transition-all cursor-pointer group"
                                            title="Klik untuk melihat bukti gambar layar penuh">
                                            <img src="{{ $trx->attachment_url }}" alt="Bukti" class="w-8 h-8 rounded-[8px] object-cover shrink-0">
                                            <span class="text-[11px] font-semibold text-[#007AFF] pr-1 group-hover:underline">Lihat</span>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-black/30 dark:text-white/30 italic">Tanpa Lampiran</span>
                                    @endif
                                </td>

                                {{-- 7. Aksi --}}
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                                            <button type="button" @click="openEdit(@js($trx))"
                                                class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center transition-all cursor-pointer"
                                                title="Ubah Transaksi & Lampiran">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>

                                            <button type="button" @click="openDelete(@js($trx))"
                                                class="w-7 h-7 rounded-[8px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition-all cursor-pointer"
                                                title="Hapus Transaksi">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/30 dark:text-white/30 mb-2.5">
                                        <i data-lucide="receipt" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-[14px] font-bold text-black dark:text-white">Belum ada transaksi pada periode ini</p>
                                    <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Catat transaksi manual atau klik "Sinkronisasi Otomatis" untuk menarik tagihan dan pencairan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/5">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 2: LAPORAN LABA RUGI (PROFIT & LOSS BREAKDOWN)    --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'pnl'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Inflow Breakdown (Pendapatan) --}}
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Rincian Penerimaan (Inflow)</h3>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Sumber pemasukan kas platform</p>
                        </div>
                    </div>
                    <div class="text-[15px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">
                        Rp {{ number_format($totalInflow, 0, ',', '.') }}
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($inflowCategories as $catKey => $catAmount)
                        @php
                            $pct = $totalInflow > 0 ? round(($catAmount / $totalInflow) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="font-semibold text-black dark:text-white">{{ $categoryLabels[$catKey] ?? $catKey }}</span>
                                <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($catAmount, 0, ',', '.') }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden">
                                <div class="h-full rounded-full bg-[#34C759]" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-black/40 dark:text-white/40 text-[12px]">Belum ada pemasukan pada rentang tanggal ini.</div>
                    @endforelse
                </div>
            </div>

            {{-- Outflow Breakdown (Beban & Pengeluaran) --}}
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                            <i data-lucide="trending-down" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Rincian Pengeluaran (Outflow)</h3>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Settlement merchant &amp; beban operasional</p>
                        </div>
                    </div>
                    <div class="text-[15px] font-bold text-[#FF3B30] dark:text-[#FF453A] tabular-nums">
                        Rp {{ number_format($totalOutflow, 0, ',', '.') }}
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($outflowCategories as $catKey => $catAmount)
                        @php
                            $pct = $totalOutflow > 0 ? round(($catAmount / $totalOutflow) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="font-semibold text-black dark:text-white">{{ $categoryLabels[$catKey] ?? $catKey }}</span>
                                <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($catAmount, 0, ',', '.') }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden">
                                <div class="h-full rounded-full bg-[#FF3B30]" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-black/40 dark:text-white/40 text-[12px]">Belum ada pengeluaran pada rentang tanggal ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Net Income Statement Banner --}}
        <div class="rounded-[20px] bg-gradient-to-r {{ $netProfit >= 0 ? 'from-[#34C759]/10 via-[#007AFF]/10 to-transparent border border-[#34C759]/20' : 'from-[#FF3B30]/10 via-[#FF9500]/10 to-transparent border border-[#FF3B30]/20' }} p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
            <div>
                <div class="text-[12px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Laba Bersih Operasional Platform (Net Income)</div>
                <div class="text-[26px] sm:text-[30px] font-bold {{ $netProfit >= 0 ? 'text-black dark:text-white' : 'text-[#FF3B30]' }} tabular-nums mt-0.5">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 mt-1">
                    Hasil kalkulasi seluruh kas masuk dikurangi total kas keluar settlement merchant &amp; operasional platform.
                </p>
            </div>
            <div class="px-4 py-2 rounded-[14px] bg-black/5 dark:bg-white/10 text-center shrink-0">
                <div class="text-[11px] text-black/50 dark:text-white/50 font-semibold">Net Profit Margin</div>
                <div class="text-[18px] font-bold text-[#007AFF] tabular-nums">{{ $profitMargin }}%</div>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 3: INTEGRASI BILLING & SETTLEMENT HUB             --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'reconciliation'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Integration Card 1: Billing Subscriptions --}}
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Billing Subscriptions</h3>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Sistem penerimaan pembayaran tenant Core</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">Aktif &amp; Terhubung</span>
                </div>

                <div class="space-y-2 text-[12.5px]">
                    <div class="flex items-center justify-between py-1 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/60 dark:text-white/60">URL Sistem Billing:</span>
                        <a href="{{ route('admin.subscriptions.index') }}" class="text-[#007AFF] hover:underline font-semibold font-mono text-[11.5px]">/admin/subscriptions</a>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/60 dark:text-white/60">Data Belum Tersinkron:</span>
                        <span class="font-bold text-black dark:text-white tabular-nums">{{ $unsyncedSubscriptionsCount }} Transaksi</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-black/60 dark:text-white/60">Integrasi Bukti Bayar:</span>
                        <span class="font-semibold text-[#34C759] flex items-center gap-1">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            <span>Otomatis Lampirkan</span>
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="flex-1 h-9.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/80 dark:text-white/80 font-bold text-[12.5px] flex items-center justify-center gap-1.5 transition-all">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Antrean Billing</span>
                    </a>
                </div>
            </div>

            {{-- Integration Card 2: Merchant Settlement --}}
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                            <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Merchant Settlement</h3>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Sistem pencairan saldo merchant &amp; fee</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">Aktif &amp; Terhubung</span>
                </div>

                <div class="space-y-2 text-[12.5px]">
                    <div class="flex items-center justify-between py-1 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/60 dark:text-white/60">URL Sistem Settlement:</span>
                        <a href="{{ route('admin.settlements.index') }}" class="text-[#FF9500] hover:underline font-semibold font-mono text-[11.5px]">/admin/settlements</a>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/60 dark:text-white/60">Data Belum Tersinkron:</span>
                        <span class="font-bold text-black dark:text-white tabular-nums">{{ $unsyncedSettlementsCount }} Transaksi</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-black/60 dark:text-white/60">Integrasi Bukti Transfer:</span>
                        <span class="font-semibold text-[#34C759] flex items-center gap-1">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            <span>Otomatis Lampirkan</span>
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <a href="{{ route('admin.settlements.index') }}"
                        class="flex-1 h-9.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/80 dark:text-white/80 font-bold text-[12.5px] flex items-center justify-center gap-1.5 transition-all">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Hub Settlement</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Platform Accounts List --}}
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Rekening Penampung Kas Platform</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Rekening resmi penampung pembayaran langganan SaaS</p>
                </div>
                <a href="{{ route('admin.payment-accounts.index') }}" class="text-[12px] font-bold text-[#007AFF] hover:underline">Kelola Rekening &rarr;</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($platformAccounts as $acc)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[13px] text-black dark:text-white">{{ $acc->bank_name }}</span>
                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        </div>
                        <div class="font-mono text-[13px] font-bold text-[#007AFF] tabular-nums">{{ $acc->account_number }}</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">a.n. {{ $acc->account_name }}</div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-6 text-black/40 dark:text-white/40 text-[12px]">Belum ada rekening platform yang terdaftar.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: CATAT TRANSAKSI KAS BARU (WITH IMAGE ATTACH)   --}}
    {{-- ===================================================== --}}
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showCreateModal = false">
        
        <div class="w-full max-w-2xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
            @click.outside="showCreateModal = false">
            
            <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white">Catat Transaksi Kas Baru</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Catat pemasukan atau pengeluaran platform dengan bukti lampiran gambar.</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-black/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.finance.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    {{-- Tipe & Nominal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Jenis Arus Kas *</label>
                            <select name="type" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="in">Kas Masuk (Inflow / Penerimaan)</option>
                                <option value="out">Kas Keluar (Outflow / Pengeluaran)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Nominal Transaksi (Rp) *</label>
                            <input type="number" name="amount" min="1" step="any" required placeholder="Contoh: 1500000"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Kategori & Tanggal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Kategori Akun *</label>
                            <select name="category" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <optgroup label="-- Pemasukan (Inflows) --">
                                    <option value="subscription_revenue">Pendapatan Langganan SaaS</option>
                                    <option value="ai_token_revenue">Penjualan Kuota Token AI</option>
                                    <option value="setup_fee">Biaya Onboarding &amp; Setup</option>
                                    <option value="other_income">Penerimaan Kas Lainnya</option>
                                </optgroup>
                                <optgroup label="-- Pengeluaran (Outflows) --">
                                    <option value="merchant_settlement">Pencairan Saldo Merchant</option>
                                    <option value="infrastructure">Server, VPS &amp; Cloud Hosting</option>
                                    <option value="ai_api_cost">Biaya API LLM (Gemini / Claude)</option>
                                    <option value="whatsapp_gateway">Meta WhatsApp Cloud API</option>
                                    <option value="marketing">Pemasaran &amp; Kampanye Iklan</option>
                                    <option value="salary">Gaji &amp; Insentif Tim</option>
                                    <option value="operational">Operasional &amp; Legalitas Platform</option>
                                    <option value="other_expense">Pengeluaran Kas Lainnya</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Tanggal Transaksi *</label>
                            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Judul Transaksi & Metode --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Judul / Keterangan Transaksi *</label>
                            <input type="text" name="title" required placeholder="Contoh: Pembayaran Server VPS Hetzner Oktober"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Metode / Bank Penampung</label>
                            <input type="text" name="payment_method" placeholder="Contoh: BCA 81290312, Mandiri VA, Kas Tunai"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-bold text-black dark:text-white">Catatan Tambahan / Memo</label>
                        <textarea name="notes" rows="2" placeholder="Tuliskan nomor referensi, faktur pajak, atau rincian transaksi..."
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>

                    {{-- Upload Lampiran Gambar (Optional Attachment Image) --}}
                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <label class="block text-[12px] font-bold text-black dark:text-white">
                            Lampiran Bukti Transaksi (Opsional / Gambar Bukti)
                        </label>
                        
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
                                    @change="handleFileSelect($event, 'create')"
                                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Mendukung format JPG, PNG, WEBP, atau PDF (maksimal 5MB).</p>
                            </div>

                            <template x-if="createImagePreview">
                                <div class="relative w-14 h-14 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/15 shrink-0 shadow-xs">
                                    <img :src="createImagePreview" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-3 shrink-0 bg-black/[0.01] dark:bg-white/[0.01]">
                    <button type="button" @click="showCreateModal = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 transition-colors">
                        Batal
                    </button>
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                        <button type="submit"
                            class="h-10 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Transaksi</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: UBAH TRANSAKSI KAS & LAMPIRAN GAMBAR           --}}
    {{-- ===================================================== --}}
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showEditModal = false">
        
        <div class="w-full max-w-2xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
            @click.outside="showEditModal = false">
            
            <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white">Ubah Data Transaksi</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="editTrx ? editTrx.transaction_number : ''"></p>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-black/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="editActionUrl" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Jenis Arus Kas *</label>
                            <select name="type" x-model="editTrx.type" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="in">Kas Masuk (Inflow / Penerimaan)</option>
                                <option value="out">Kas Keluar (Outflow / Pengeluaran)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Nominal Transaksi (Rp) *</label>
                            <input type="number" name="amount" min="1" step="any" x-model="editTrx.amount" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Kategori Akun *</label>
                            <select name="category" x-model="editTrx.category" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                @foreach($categoryLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Tanggal Transaksi *</label>
                            <input type="date" name="transaction_date" :value="editTrx ? editTrx.transaction_date.substring(0, 10) : ''" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Judul Transaksi *</label>
                            <input type="text" name="title" x-model="editTrx.title" required
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Metode / Bank</label>
                            <input type="text" name="payment_method" x-model="editTrx.payment_method"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[12px] font-bold text-black dark:text-white">Catatan / Memo</label>
                        <textarea name="notes" rows="2" x-model="editTrx.notes"
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>

                    {{-- Ganti Lampiran Gambar --}}
                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <label class="block text-[12px] font-bold text-black dark:text-white">Perbarui Lampiran Bukti (Opsional)</label>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
                                    @change="handleFileSelect($event, 'edit')"
                                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
                            </div>

                            <template x-if="editImagePreview">
                                <div class="relative w-14 h-14 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/15 shrink-0 shadow-xs">
                                    <img :src="editImagePreview" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-3 shrink-0 bg-black/[0.01] dark:bg-white/[0.01]">
                    <button type="button" @click="showEditModal = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 transition-colors">
                        Batal
                    </button>
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                        <button type="submit"
                            class="h-10 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Perbarui Transaksi</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: LIGHTBOX ZOOM PREVIEW GAMBAR BUKTI             --}}
    {{-- ===================================================== --}}
    <div x-show="showImageModal" x-cloak
        class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
        @keydown.escape.window="showImageModal = false">
        
        <div class="relative max-w-3xl w-full rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/15 dark:border-white/15 overflow-hidden shadow-2xl flex flex-col"
            @click.outside="showImageModal = false">
            
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <i data-lucide="image" class="w-4 h-4 text-[#007AFF]"></i>
                    <span class="text-[13px] font-bold text-black dark:text-white truncate" x-text="modalImageTitle"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="modalImageUrl" target="_blank" download
                        class="h-8 px-3 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh</span>
                    </a>
                    <button type="button" @click="showImageModal = false" class="p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-black/50">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="p-4 flex items-center justify-center bg-black/[0.03] dark:bg-black/40 max-h-[75vh] overflow-auto">
                <img :src="modalImageUrl" alt="Bukti Lampiran" class="max-h-[70vh] w-auto rounded-[12px] object-contain shadow-lg">
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: KONFIRMASI HAPUS TRANSAKSI                     --}}
    {{-- ===================================================== --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showDeleteModal = false">
        
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
            @click.outside="showDeleteModal = false">
            
            <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                <i data-lucide="trash-2" class="w-6 h-6"></i>
            </div>

            <div>
                <h3 class="text-[17px] font-bold text-black dark:text-white">Hapus Transaksi Buku Kas?</h3>
                <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus transaksi <strong class="text-black dark:text-white" x-text="deleteTrx?.transaction_number"></strong>?
                    Data akan dihapus dari buku kas operasional platform.
                </p>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2.5">
                <button type="button" @click="showDeleteModal = false"
                    class="h-9.5 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5">
                    Batal
                </button>
                @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="h-9.5 px-5 rounded-[10px] bg-[#FF3B30] hover:bg-[#E02D23] text-white font-bold text-[13px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Ya, Hapus Transaksi</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
