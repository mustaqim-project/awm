@extends('layouts.admin', [
    'title' => __('admin.buku_kas_akuntansi_platform_admin_console'),
    'headerTitle' => 'Finance & Corporate Accounting Platform',
    'headerSubtitle' => 'Buku Kas, Jurnal Umum Berpasangan, Buku Besar, Neraca Saldo, Laporan Neraca & Laba Rugi terintegrasi dengan Billing dan Settlement',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeTab: '{{ $activeTab }}',
    showCreateModal: false,
    showCreateJournalModal: false,
    showCreateAccountModal: false,
    showEditAccountModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showImageModal: false,
    modalImageUrl: '',
    modalImageTitle: '',

    // AR & AP modal state
    showCreateArModal: false,
    showPayArModal: false,
    selectedAr: null,
    payArActionUrl: '',

    showCreateApModal: false,
    showPayApModal: false,
    selectedAp: null,
    payApActionUrl: '',

    // Edit transaction form state
    editTrx: null,
    editActionUrl: '',

    // Delete transaction form state
    deleteTrx: null,
    deleteActionUrl: '',

    // Edit account state
    editAccount: null,

    // Create form preview
    createImagePreview: null,
    editImagePreview: null,

    previewImage(url, title) {
        this.modalImageUrl = url;
        this.modalImageTitle = title || 'Lampiran Bukti Transaksi';
        this.showImageModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreateAr() {
        this.showCreateArModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openPayAr(ar) {
        this.selectedAr = ar;
        this.payArActionUrl = '/admin/finance/receivables/' + ar.id + '/payments';
        this.showPayArModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreateAp() {
        this.showCreateApModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openPayAp(ap) {
        this.selectedAp = ap;
        this.payApActionUrl = '/admin/finance/payables/' + ap.id + '/payments';
        this.showPayApModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreate() {
        this.createImagePreview = null;
        this.showCreateModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreateJournal() {
        this.showCreateJournalModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openCreateAccount() {
        this.showCreateAccountModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openEditAccount(account) {
        this.editAccount = account;
        this.showEditAccountModal = true;
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
                Pusat pembukuan keuangan berpasangan (Double-Entry), buku besar, neraca saldo, neraca, serta sinkronisasi otomatis dengan 
                <a href="{{ route('admin.subscriptions.index') }}" class="text-[#007AFF] hover:underline font-semibold">Billing</a> &amp; 
                <a href="{{ route('admin.settlements.index') }}" class="text-[#007AFF] hover:underline font-semibold">Settlement</a>.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 flex-wrap">
            {{-- Tombol Sinkronisasi Billing & Settlement --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.sync'))
                <form action="{{ route('admin.finance.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                        title="{{ __('admin.tarik_transaksi_baru_dari_billing_subscriptions') }}">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>{{ __('admin.sinkronisasi_otomatis') }}</span>
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
                    <span>{{ __('admin.catat_transaksi') }}</span>
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

    @if(session('error'))
        <div class="rounded-[16px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3 shadow-xs">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1 font-semibold">{{ session('error') }}</div>
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
                    <div class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                        <span class="font-bold text-[#FF9500]">{{ $unsyncedSubscriptionsCount }}</span> pembayaran billing disetujui &amp; 
                        <span class="font-bold text-[#AF52DE]">{{ $unsyncedSettlementsCount }}</span> pencairan merchant selesai menunggu sinkronisasi.
                    </div>
                </div>
            </div>

            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.sync'))
                <form action="{{ route('admin.finance.sync') }}" method="POST" class="shrink-0 w-full sm:w-auto">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto h-9 px-4 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12.5px] font-bold transition-all shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Sinkronkan Sekarang</span>
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- 2. KPI STATS SUMMARY (BENTO GRID) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Inflow --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Total Kas Masuk (Inflow)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums tracking-tight">
                    Rp {{ number_format($totalInflow, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Langganan SaaS &amp; Token AI
                </div>
            </div>
        </div>

        {{-- Total Outflow --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Total Kas Keluar (Outflow)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A] flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-[#C41E17] dark:text-[#FF453A] tabular-nums tracking-tight">
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
                <div class="text-[20px] sm:text-[24px] font-bold {{ $netProfit >= 0 ? 'text-black dark:text-white' : 'text-[#FF3B30]' }} tabular-nums tracking-tight">
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
                <span>Total Transaksi Mutasi</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-[#5856D6] dark:text-[#5E5CE6] tabular-nums tracking-tight">
                    {{ number_format($totalTransactionsCount, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Buku Kas &amp; Jurnal Umum
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

            {{-- Filter Jenis Mutasi --}}
            <select name="type"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Tipe Kas</option>
                <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Kas Masuk (+)</option>
                <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Kas Keluar (-)</option>
            </select>

            {{-- Filter Kategori --}}
            <select name="category"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Kategori</option>
                @foreach($categoryLabels as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Filter Sumber Mutasi --}}
            <select name="source"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Sumber</option>
                <option value="subscription_payment" {{ $source === 'subscription_payment' ? 'selected' : '' }}>Billing Subscriptions</option>
                <option value="payment_settlement" {{ $source === 'payment_settlement' ? 'selected' : '' }}>Pencairan Merchant</option>
                <option value="manual" {{ $source === 'manual' ? 'selected' : '' }}>Pencatatan Manual</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative flex-1 sm:w-56">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('admin.cari_nomor_judul') }}"
                    class="w-full h-9.5 pl-9 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            <button type="submit"
                class="h-9.5 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold transition-all shadow-xs cursor-pointer">
                Terapkan
            </button>

            <a href="{{ route('admin.finance.index') }}"
                class="h-9.5 w-9.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] text-black/60 dark:text-white/60 flex items-center justify-center transition-all"
                title="{{ __('common.reset') }}">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </form>

    {{-- 4. SEGMENTED NAVIGATION TABS (7 TABS CORPORATE ACCOUNTING) --}}
    <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-2 overflow-x-auto">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[12.5px] font-medium shrink-0">
            {{-- Tab 1: Cashflow --}}
            <button type="button" @click="activeTab = 'cashflow'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'cashflow' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="wallet" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Buku Kas &amp; Mutasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $transactions->total() }}</span>
            </button>

            {{-- Tab 2: General Journal --}}
            <button type="button" @click="activeTab = 'journals'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'journals' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="book-open" class="w-4 h-4 text-[#34C759]"></i>
                <span>Jurnal Umum</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $journalEntries->total() }}</span>
            </button>

            {{-- Tab 3: General Ledger --}}
            <button type="button" @click="activeTab = 'ledger'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'ledger' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="layers" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>Buku Besar</span>
            </button>

            {{-- Tab 4: Trial Balance --}}
            <button type="button" @click="activeTab = 'trial_balance'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'trial_balance' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="check-square" class="w-4 h-4 text-[#FF9500]"></i>
                <span>{{ __('admin.neraca_saldo') }}</span>
            </button>

            {{-- Tab 5: Balance Sheet & PnL --}}
            <button type="button" @click="activeTab = 'balance_sheet'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'balance_sheet' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="scale" class="w-4 h-4 text-[#5856D6]"></i>
                <span>Neraca &amp; Laba Rugi</span>
            </button>

            {{-- Tab 6: Chart of Accounts --}}
            <button type="button" @click="activeTab = 'coa'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'coa' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="list-tree" class="w-4 h-4 text-[#FF2D55]"></i>
                <span>{{ __('admin.bagan_akun_coa') }}</span>
            </button>

            {{-- Tab 7: Billing & Settlement Sync --}}
            <button type="button" @click="activeTab = 'sync'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'sync' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Integrasi Billing</span>
            </button>

            {{-- Tab 8: Accounts Receivable (AR / Piutang) --}}
            <button type="button" @click="activeTab = 'ar'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'ar' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="receipt" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Piutang (AR)</span>
                @if(($arAging['counts']['unpaid'] + $arAging['counts']['partial']) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $arAging['counts']['unpaid'] + $arAging['counts']['partial'] }}</span>
                @endif
            </button>

            {{-- Tab 9: Accounts Payable (AP / Hutang) --}}
            <button type="button" @click="activeTab = 'ap'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'ap' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="file-check" class="w-4 h-4 text-[#FF3B30]"></i>
                <span>Hutang (AP)</span>
                @if(($apAging['counts']['unpaid'] + $apAging['counts']['partial']) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums">{{ $apAging['counts']['unpaid'] + $apAging['counts']['partial'] }}</span>
                @endif
            </button>
        </div>
    </div>

    {{-- TAB CONTENTS --}}
    @include('admin.finance.tabs.tab-cashflow')
    @include('admin.finance.tabs.tab-journals')
    @include('admin.finance.tabs.tab-ledger')
    @include('admin.finance.tabs.tab-trial-balance')
    @include('admin.finance.tabs.tab-balance-sheet')
    @include('admin.finance.tabs.tab-coa')
    @include('admin.finance.tabs.tab-sync')
    @include('admin.finance.tabs.tab-ar')
    @include('admin.finance.tabs.tab-ap')

    {{-- MODALS --}}
    @include('admin.finance.modals.modal-create-transaction')
    @include('admin.finance.modals.modal-create-journal')
    @include('admin.finance.modals.modal-coa')
    @include('admin.finance.modals.modal-lightbox')
    @include('admin.finance.modals.modal-create-ar')
    @include('admin.finance.modals.modal-pay-ar')
    @include('admin.finance.modals.modal-create-ap')
    @include('admin.finance.modals.modal-pay-ap')

    {{-- MODAL EDIT TRANSAKSI KAS --}}
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false"
            class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
            
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.ubah_transaksi_kas') }}</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 font-mono" x-text="editTrx ? editTrx.transaction_number : ''"></p>
                </div>
                <button type="button" @click="showEditModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="editActionUrl" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                {{-- Tipe Mutasi --}}
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1.5">
                        Jenis Mutasi Kas <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <label class="flex items-center justify-center gap-2 py-2 rounded-[9px] text-[13px] font-bold cursor-pointer transition-all has-[:checked]:bg-[#34C759] has-[:checked]:text-white text-black/70 dark:text-white/70">
                            <input type="radio" name="type" value="in" class="sr-only" :checked="editTrx && editTrx.type === 'in'">
                            <span>Kas Masuk (+)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 py-2 rounded-[9px] text-[13px] font-bold cursor-pointer transition-all has-[:checked]:bg-[#FF3B30] has-[:checked]:text-white text-black/70 dark:text-white/70">
                            <input type="radio" name="type" value="out" class="sr-only" :checked="editTrx && editTrx.type === 'out'">
                            <span>Kas Keluar (-)</span>
                        </label>
                    </div>
                </div>

                {{-- Kategori & Tanggal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Kategori *</label>
                        <select name="category" :value="editTrx ? editTrx.category : ''" required
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            @foreach($categoryLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Tanggal *</label>
                        <input type="date" name="transaction_date" :value="editTrx ? editTrx.transaction_date.substring(0, 10) : ''" required
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </div>

                {{-- Judul Transaksi --}}
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Judul / Uraian Transaksi *</label>
                    <input type="text" name="title" :value="editTrx ? editTrx.title : ''" required
                        class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>

                {{-- Nominal & Metode --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Jumlah (IDR) *</label>
                        <input type="number" name="amount" min="1" step="any" :value="editTrx ? editTrx.amount : ''" required
                            class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-mono font-bold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Metode</label>
                        <input type="text" name="payment_method" :value="editTrx ? editTrx.payment_method : ''"
                            class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" :value="editTrx ? editTrx.notes : ''"
                        class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                </div>

                {{-- Ganti Lampiran Bukti --}}
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">Ganti Berkas Lampiran Bukti (Opsional)</label>
                    <input type="file" name="attachment" accept="image/*,.pdf" class="text-[12px] text-black/70 dark:text-white/70" @change="handleFileSelect($event, 'edit')">
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showEditModal = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] cursor-pointer">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 cursor-pointer">{{ __('admin.perbarui_transaksi') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS TRANSAKSI --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showDeleteModal = false"
            class="w-full max-w-sm rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-center">
            
            <div class="w-12 h-12 rounded-full bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>

            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.hapus_transaksi_kas') }}</h3>
            <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-1.5 leading-relaxed">
                Transaksi <span class="font-mono font-bold text-black dark:text-white" x-text="deleteTrx ? deleteTrx.transaction_number : ''"></span> beserta entri jurnal terkait akan dihapus secara soft-delete.
            </p>

            <form :action="deleteActionUrl" method="POST" class="mt-5 flex items-center justify-center gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] shadow-sm shadow-[#FF3B30]/25 cursor-pointer">{{ __('admin.ya_hapus') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
