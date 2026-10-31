@extends('layouts.admin', [
    'title' => __('admin.penjualan_b2b_faktur_admin_console'),
    'headerTitle' => 'Penjualan B2B & Faktur Platform',
    'headerSubtitle' => 'Pusat operasional komersial enterprise: Surat Penawaran, Pesanan Penjualan (SO), Faktur Komersial & Retur Penjualan resmi',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeTab: '{{ $activeTab }}',
    showPaymentModal: false,
    selectedInvoice: null,
    paymentActionUrl: '',

    showCreateQuotationModal: false,
    showCreateOrderModal: false,
    showCreateInvoiceModal: false,
    showCreateReturnModal: false,

    showEditModal: false,
    editDocType: '',
    editDocUrl: '',
    editDoc: null,

    openEdit(type, doc, url) {
        this.editDocType = type;
        this.editDoc = doc;
        this.editDocUrl = url;
        this.showEditModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openPayment(inv) {
        this.selectedInvoice = inv;
        this.paymentActionUrl = '/admin/b2b/invoices/' + inv.id + '/payments';
        this.showPaymentModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    }
}">

    {{-- 1. TOP HEADER & ACTION TOOLBAR --}}
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/45 dark:text-white/45 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#007AFF] transition-colors">Admin Console</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/60 dark:text-white/60 font-medium">Monetisasi &amp; Finansial</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Penjualan B2B &amp; Faktur</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Penjualan B2B &amp; Faktur Komersial</h1>
            <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5">
                Monitoring komersial korporat, konfirmasi pesanan (SO), jadwal penagihan faktur piutang, nota retur, dan pencetakan dokumen PDF legal berstandar Indonesia.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 flex-wrap">
            {{-- Tombol Buat Dokumen Baru --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.create'))
                <template x-if="activeTab === 'quotations'">
                    <button type="button" @click="showCreateQuotationModal = true"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#6366f1] hover:bg-[#4f46e5] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>+ Terbitkan Penawaran</span>
                    </button>
                </template>
                <template x-if="activeTab === 'orders'">
                    <button type="button" @click="showCreateOrderModal = true"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('admin.buat_pesanan_so') }}</span>
                    </button>
                </template>
                <template x-if="activeTab === 'invoices'">
                    <button type="button" @click="showCreateInvoiceModal = true"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#0284c7] hover:bg-[#0369a1] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('admin.terbitkan_faktur_langsung') }}</span>
                    </button>
                </template>
                <template x-if="activeTab === 'returns'">
                    <button type="button" @click="showCreateReturnModal = true"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#d97706] hover:bg-[#b45309] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>+ Buat Pengajuan Retur</span>
                    </button>
                </template>
            @endif

            {{-- Tombol Ekspor CSV --}}
            @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.export'))
                <a href="{{ route('admin.b2b.export', array_merge(request()->query(), ['tab' => $activeTab])) }}"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor Data CSV</span>
                </a>
            @endif
        </div>
    </header>

    {{-- ALERT FLASH MESSAGES --}}
    @if(session('status'))
        <div class="rounded-[16px] bg-[#34C759]/12 border border-[#34C759]/25 p-4 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1 font-semibold">{{ session('status') }}</div>
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

    {{-- 2. BENTO GRID KPI METRICS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Surat Penawaran --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Nilai Penawaran (Quotation)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    Rp {{ number_format($kpi['totalQuotationAmount'], 0, ',', '.') }}
                </div>
                <div class="text-[11.5px] text-black/50 dark:text-white/50 mt-1 flex items-center justify-between">
                    <span>{{ $kpi['totalQuotationCount'] }} Dokumen</span>
                    <span class="font-bold text-[#34C759]">{{ $kpi['acceptedQuotationCount'] }} Diterima</span>
                </div>
            </div>
        </div>

        {{-- Total Pesanan Penjualan (SO) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Nilai Kontrak Pesanan (SO)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[22px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums tracking-tight">
                    Rp {{ number_format($kpi['totalOrderAmount'], 0, ',', '.') }}
                </div>
                <div class="text-[11.5px] text-black/50 dark:text-white/50 mt-1 flex items-center justify-between">
                    <span>{{ $kpi['totalOrderCount'] }} Pesanan SO</span>
                    <span class="font-bold text-[#007AFF]">{{ $kpi['fulfilledOrderCount'] }} Terpenuhi</span>
                </div>
            </div>
        </div>

        {{-- Total Faktur & Sisa Piutang --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Faktur Komersial (Invoiced)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#0284c7]/15 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    Rp {{ number_format($kpi['totalInvoiceAmount'], 0, ',', '.') }}
                </div>
                <div class="text-[11.5px] text-[#b91c1c] font-semibold mt-1 flex items-center justify-between">
                    <span>Sisa Piutang: Rp {{ number_format($kpi['totalInvoiceBalance'], 0, ',', '.') }}</span>
                    @if($kpi['overdueInvoiceCount'] > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-[#FF3B30]/15 text-[#C41E17] font-bold">{{ $kpi['overdueInvoiceCount'] }} Overdue</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Retur Penjualan --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Retur &amp; Kredit Kompensasi</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#e11d48]/15 text-[#e11d48] flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[22px] font-bold text-[#e11d48] tabular-nums tracking-tight">
                    Rp {{ number_format($kpi['totalReturnAmount'], 0, ',', '.') }}
                </div>
                <div class="text-[11.5px] text-black/50 dark:text-white/50 mt-1 flex items-center justify-between">
                    <span>{{ $kpi['totalReturnCount'] }} Dokumen</span>
                    <span class="font-bold text-[#e11d48]">{{ $kpi['completedReturnCount'] }} Selesai</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. FILTER BAR --}}
    <form method="GET" action="{{ route('admin.b2b.index') }}"
        class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 shadow-xs">
        
        <input type="hidden" name="tab" :value="activeTab">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 flex-1">
            {{-- Filter Tenant / Bisnis --}}
            <select name="business_id"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Tenant / Bisnis</option>
                @foreach($businesses as $b)
                    <option value="{{ $b->id }}" {{ $businessId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>

            {{-- Rentang Tanggal --}}
            <div class="flex items-center gap-1.5">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full h-9.5 px-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <span class="text-black/40 dark:text-white/40 text-[11px]">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full h-9.5 px-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            {{-- Filter Status --}}
            <select name="status"
                class="h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                <option value="">Semua Status</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent / Terkirim</option>
                <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>Accepted / Diterima</option>
                <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed (SO)</option>
                <option value="fulfilled" {{ $status === 'fulfilled' ? 'selected' : '' }}>Fulfilled (SO Selesai)</option>
                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid (Belum Lunas)</option>
                <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue (Jatuh Tempo)</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved (Retur)</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed (Retur)</option>
            </select>

            {{-- Pencarian --}}
            <div class="relative">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-3 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('admin.no_dokumen_nama_klien') }}"
                    class="w-full h-9.5 pl-8.5 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit"
                class="h-9.5 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold transition-all shadow-xs cursor-pointer">
                Terapkan
            </button>

            <a href="{{ route('admin.b2b.index') }}"
                class="h-9.5 w-9.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] text-black/60 dark:text-white/60 flex items-center justify-center transition-all"
                title="{{ __('common.reset') }}">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </form>

    {{-- 4. SEGMENTED NAVIGATION TABS --}}
    <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-2 overflow-x-auto">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[12.5px] font-medium shrink-0">
            {{-- Tab 1: Surat Penawaran --}}
            <a href="{{ route('admin.b2b.index', array_merge(request()->query(), ['tab' => 'quotations'])) }}"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer {{ $activeTab === 'quotations' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Surat Penawaran</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $kpi['totalQuotationCount'] }}</span>
            </a>

            {{-- Tab 2: Pesanan Penjualan (SO) --}}
            <a href="{{ route('admin.b2b.index', array_merge(request()->query(), ['tab' => 'orders'])) }}"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer {{ $activeTab === 'orders' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="check-check" class="w-4 h-4 text-[#34C759]"></i>
                <span>{{ __('admin.pesanan_penjualan_so') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $kpi['totalOrderCount'] }}</span>
            </a>

            {{-- Tab 3: Faktur Penjualan --}}
            <a href="{{ route('admin.b2b.index', array_merge(request()->query(), ['tab' => 'invoices'])) }}"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer {{ $activeTab === 'invoices' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="receipt" class="w-4 h-4 text-[#0284c7]"></i>
                <span>Faktur Penjualan (Invoice)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#0284c7]/12 text-[#0284c7] tabular-nums">{{ $invoices ? $invoices->total() : \App\Models\Invoice::count() }}</span>
            </a>

            {{-- Tab 4: Retur Penjualan --}}
            <a href="{{ route('admin.b2b.index', array_merge(request()->query(), ['tab' => 'returns'])) }}"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-1.5 cursor-pointer {{ $activeTab === 'returns' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="rotate-ccw" class="w-4 h-4 text-[#e11d48]"></i>
                <span>Retur Penjualan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-bold bg-[#e11d48]/12 text-[#e11d48] tabular-nums">{{ $kpi['totalReturnCount'] }}</span>
            </a>
        </div>
    </div>

    {{-- TAB CONTENTS --}}
    @if($activeTab === 'quotations')
        @include('admin.b2b.tabs.tab-quotations')
    @elseif($activeTab === 'orders')
        @include('admin.b2b.tabs.tab-orders')
    @elseif($activeTab === 'invoices')
        @include('admin.b2b.tabs.tab-invoices')
    @elseif($activeTab === 'returns')
        @include('admin.b2b.tabs.tab-returns')
    @endif

    {{-- MODALS --}}
    @include('admin.b2b.modals.modal-record-payment')
    @include('admin.b2b.modals.modal-create-quotation')
    @include('admin.b2b.modals.modal-create-order')
    @include('admin.b2b.modals.modal-create-invoice')
    @include('admin.b2b.modals.modal-create-return')
    @include('admin.b2b.modals.modal-edit-document')

</div>
@endsection
