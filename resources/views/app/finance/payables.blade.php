@extends('layouts.app', ['title' => __('finance.payables_page_title')])

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
            this.uploadAndPreviewPayables();
        }
    },

    uploadAndPreviewPayables() {
        if (!this.importFile) return;
        this.importLoading = true;
        const formData = new FormData();
        formData.append('file', this.importFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('finance.payables.import-preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || @json(__('finance.js_failed_process_excel'))); });
            }
            return res.json();
        })
        .then(res => {
            this.importLoading = false;
            if (res.success) {
                this.importPreview = res.data;
            } else {
                alert(res.message || @json(__('finance.js_failed_analyze_payables')));
            }
        })
        .catch(err => {
            this.importLoading = false;
            alert(err.message || @json(__('finance.js_upload_error')));
        });
    },

    async executePayablesImport() {
        if (!this.importPreview || !this.importPreview.rows || this.importPreview.rows.length === 0) {
            return;
        }

        const totalToProcess = this.importDuplicateStrategy === 'skip' 
            ? this.importPreview.valid_count 
            : (this.importPreview.valid_count + this.importPreview.duplicate_count);

        if (totalToProcess <= 0) {
            alert(@json(__('finance.js_no_payables_to_import')));
            return;
        }

        if (!confirm(@json(__('finance.js_confirm_import_payables', ['count' => ''])).replace(':count', totalToProcess))) {
            return;
        }

        this.importExecuting = true;
        fetch('{{ route('finance.payables.import-execute') }}', {
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
                alert(res.message || @json(__('finance.js_failed_execute_import_payables')));
            }
        })
        .catch(err => {
            this.importExecuting = false;
            alert(err.message || @json(__('finance.js_system_error_import')));
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
        title="{{ __('finance.payables_page_title') }}"
        subtitle="{{ __('finance.payables_subtitle') }}">
        <a href="{{ route('finance.payables.export') }}"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="{{ __('finance.tooltip_export_payables_excel') }}">
            <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ __('finance.btn_export_excel') }}</span>
        </a>

        <button type="button" @click="showImportModal = true"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="{{ __('finance.tooltip_import_payables_opening') }}">
            <i data-lucide="upload" class="w-4 h-4 text-[#FF9500]"></i>
            <span>{{ __('finance.btn_import_opening_balance_short') }}</span>
        </button>

        @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E8880A] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)]">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>{{ __('finance.btn_supplier_bills') }}</span>
            </a>
        @endif
        @if(\App\Support\Context::hasPermission('master_data.suppliers') || \App\Support\Context::hasPermission('purchasing.view'))
            <a href="{{ route('suppliers.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="truck" class="w-4 h-4 text-[#FF9500]"></i>
                <span>{{ __('finance.btn_suppliers_list') }}</span>
            </a>
        @endif
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- MODULE TABS (SSOT)                                         --}}
    {{-- ========================================================== --}}
    <x-module-tabs module="finance" />

    @php
        $totalPayable = $totalPayable ?? $invoices->sum('balance_due');
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
    {{-- AP AGING BENTO KPI TILES                                  --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'all'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'all' ? 'border-[#007AFF] shadow-[0_0_0_1px_#007AFF]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#007AFF]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.kpi_total_supplier_debt') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalPayable, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('finance.open_bills_count', ['count' => $totalOpenCount]) }}</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#34C759]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.not_due_yet') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-1">{{ __('finance.on_schedule_terms') }}</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.overdue_1_30_days') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-1">{{ __('finance.schedule_settlement') }}</p>
        </a>

        <a href="{{ route('finance.payables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4.5 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all shadow-xs group">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.overdue_30_plus_days') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-1">{{ __('finance.priority_settlement') }}</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                       --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('common.all') }}
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.filter_current_on_time') }}
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.filter_overdue_1_30') }}
            </a>
            <a href="{{ route('finance.payables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.filter_overdue_30_plus') }}
            </a>
        </div>
        <form method="GET" action="{{ route('finance.payables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('finance.placeholder_search_payables') }}"
                       class="w-full h-10 sm:h-8 pl-8 pr-3 text-[16px] sm:text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.payables') }}" class="min-h-[44px] sm:min-h-0 h-10 sm:h-8 px-3 rounded-[8px] text-[12px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition-colors">{{ __('common.reset') }}</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AP TABLE                                                  --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">{{ __('finance.table_supplier_bills_title') }}</h2>
            <span class="text-[12px] font-medium text-black/45 dark:text-white/45 tabular-nums">{{ __('finance.bills_count', ['count' => $invoices->total()]) }}</span>
        </div>
        <div x-data="coocaTableSort()" class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <x-table-sort-th :col="0" type="string" :title="__('finance.col_bill_number')" />
                        <x-table-sort-th :col="1" type="string" :title="__('finance.col_supplier_vendor')" />
                        <x-table-sort-th :col="2" type="date" :title="__('finance.col_bill_date')" />
                        <x-table-sort-th :col="3" type="date" :title="__('finance.col_due_date')" />
                        <x-table-sort-th :col="4" type="number" :title="__('finance.col_total_bill')" align="right" />
                        <x-table-sort-th :col="5" type="number" :title="__('finance.col_remaining_debt')" align="right" />
                        <x-table-sort-th :col="6" type="number" :title="__('finance.col_debt_age')" align="center" />
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $invoice)
                    @php
                        $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                        $isOverdue = $daysDiff < 0;
                        $daysOverdue = abs($daysDiff);
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                        <td data-sort-value="{{ strtolower($invoice->invoice_number ?? $invoice->bill_number ?? '') }}" class="px-4 py-3 tabular-nums font-mono font-semibold text-black dark:text-white">
                            {{ $invoice->invoice_number ?? $invoice->bill_number ?? '-' }}
                        </td>
                        <td data-sort-value="{{ strtolower($invoice->supplier?->name ?? __('finance.general_supplier')) }}" class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $invoice->supplier?->name ?? __('finance.general_supplier') }}</div>
                            @if($invoice->supplier?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->supplier->phone }}</div>
                            @endif
                        </td>
                        <td data-sort-value="{{ ($invoice->invoice_date ?? $invoice->created_at)?->format('Y-m-d') ?? '' }}" class="px-4 py-3 text-black/60 dark:text-white/60">{{ $invoice->invoice_date?->translatedFormat('d M Y') ?? $invoice->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td data-sort-value="{{ $invoice->due_date?->format('Y-m-d') ?? '' }}" class="px-4 py-3 {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}
                        </td>
                        <td data-sort-value="{{ (float) $invoice->total_amount }}" class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td data-sort-value="{{ (float) $invoice->balance_due }}" class="px-4 py-3 text-right tabular-nums font-semibold text-[#FF9500] dark:text-[#FF9F0A]">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td data-sort-value="{{ (int) $daysDiff }}" class="px-4 py-3 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('finance.days_overdue', ['days' => $daysOverdue]) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('finance.days_remaining', ['days' => $daysDiff]) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-end gap-1">
                                @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment'))
                                <a href="{{ route('purchasing.bills.index') }}" class="w-8 h-8 rounded-[8px] text-[#FF9500] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer" title="{{ __('finance.btn_pay') }}" aria-label="{{ __('finance.btn_pay') }}">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/25 dark:text-white/25">
                                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p class="text-[15px] font-semibold text-black/60 dark:text-white/60">{{ __('finance.no_open_supplier_debt') }}</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.all_bills_paid_off') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE GROUPED INSET LIST (Apple iOS HIG) --}}
        <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($invoices as $invoice)
            @php
                $daysDiff = $invoice->due_date ? (int) now()->startOfDay()->diffInDays($invoice->due_date, false) : 0;
                $isOverdue = $daysDiff < 0;
                $daysOverdue = abs($daysDiff);
            @endphp
            <div class="p-4 space-y-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-semibold text-[14px] text-black dark:text-white font-mono">
                            {{ $invoice->invoice_number ?? $invoice->bill_number ?? '-' }}
                        </div>
                        <div class="text-[13px] font-medium text-black/80 dark:text-white/80 mt-0.5">
                            {{ $invoice->supplier?->name ?? __('finance.general_supplier') }}
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                            {{ __('finance.date_prefix_short', ['date' => $invoice->invoice_date?->translatedFormat('d M Y') ?? $invoice->created_at?->translatedFormat('d M Y') ?? '-']) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('finance.remaining_debt_label') }}</div>
                        <div class="tabular-nums font-bold text-[15px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">
                            {{ __('finance.total_label', ['amount' => 'Rp ' . number_format($invoice->total_amount, 0, ',', '.')]) }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-black/[0.03] dark:border-white/[0.04] text-[12px]">
                    <div>
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('finance.days_overdue', ['days' => $daysOverdue]) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('finance.days_remaining', ['days' => $daysDiff]) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        @if(\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment'))
                        <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] h-10 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5 shadow-xs">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            <span>{{ __('finance.btn_pay') }}</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                {{ __('finance.no_open_supplier_debt') }}
            </div>
            @endforelse
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <span>{{ __('finance.total_bills_footer', ['count' => $invoices->total()]) }}</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> {{ __('common.previous') }}</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> {{ __('common.previous') }}</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">{{ __('common.next') }} <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></a>
                @else
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center">{{ __('common.next') }} <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-1"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL IMPORT HUTANG PREVIEW (APPLE BENTO XXL MODAL)        --}}
    {{-- ========================================================== --}}
    <template x-teleport="body">
        <div x-show="showImportModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="if (!importExecuting) showImportModal = false">

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
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('finance.modal_import_payables_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.modal_import_payables_subtitle') }}</p>
                    </div>
                </div>

                <button type="button" @click="if (!importExecuting) showImportModal = false"
                    class="w-9 h-9 min-w-[44px] min-h-[44px] rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition"
                    aria-label="{{ __('common.close_modal') }}">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1">
                
                <!-- State 1: Drop Zone -->
                <div x-show="!importPreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#FF9500]/[0.08] dark:bg-[#FF9500]/[0.12] border border-[#FF9500]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#B26A00] dark:text-[#FF9F0A] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('finance.step_1_download_payables_template_title') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('finance.step_1_download_payables_template_desc') }}</p>
                        </div>
                        <a href="{{ route('finance.payables.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('finance.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('finance.step_2_upload_payables_title') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('finance.max_file_size_xlsx_csv') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#FF9500] dark:hover:border-[#FF9F0A] rounded-[20px] p-8 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01] hover:bg-[#FF9500]/[0.02]">
                        <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleImportFileSelect($event)" :disabled="importLoading">
                        
                        <div class="space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!importLoading"></i>
                                <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="importLoading"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? '{{ __('finance.reading_analyzing_payables') }}' : '{{ __('finance.select_payables_file_or_drag') }}'"></div>
                                <div class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.supports_official_payables_template') }}</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importPreview" class="space-y-5">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('finance.total_label', ['amount' => '']) }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importPreview ? importPreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('finance.kpi_ready_import') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importPreview ? importPreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('finance.kpi_duplicate_data') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importPreview ? importPreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('finance.kpi_error_invalid') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importPreview ? importPreview.error_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-[#FF9500]/[0.08] border border-[#FF9500]/20 space-y-1 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-semibold text-[#FF9500] uppercase tracking-wider">{{ __('finance.kpi_total_debt_value') }}</span>
                            <div class="text-lg font-bold font-mono text-[#FF9500]" x-text="importPreview ? ('Rp ' + formatCurrency(importPreview.total_balance_due)) : 0"></div>
                        </div>
                    </div>

                    <!-- Filter & Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                {{ __('common.all') }} (<span x-text="importPreview ? importPreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('finance.badge_ready') }} (<span x-text="importPreview ? importPreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('finance.badge_duplicate') }} (<span x-text="importPreview ? importPreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('finance.badge_error') }} (<span x-text="importPreview ? importPreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('finance.if_bill_number_exists_label') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importDuplicateStrategy" class="text-[#FF9500] focus:ring-0">
                                <span>{{ __('finance.strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importDuplicateStrategy" class="text-[#FF9500] focus:ring-0">
                                <span>{{ __('finance.strategy_update_balance_due') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('common.status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_bill_number') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_supplier') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_bill_date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_due_date') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('finance.col_total_value') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('finance.col_remaining_debt') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_diagnostic_notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importPreview ? importPreview.rows.filter(r => importFilter === 'all' || r.status === importFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('finance.badge_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('finance.badge_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('finance.badge_error') }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-black dark:text-white" x-text="row.invoice_number || '-'"></td>
                                            <td class="py-2.5 px-3 font-medium text-black dark:text-white" x-text="row.supplier_name || '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/70 dark:text-white/70" x-text="row.invoice_date || '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/70 dark:text-white/70" x-text="row.due_date || '-'"></td>
                                            <td class="py-2.5 px-3 text-right font-mono text-black/70 dark:text-white/70" x-text="'Rp ' + formatCurrency(row.total_amount)"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]" x-text="'Rp ' + formatCurrency(row.balance_due)"></td>
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
                        <span>{{ __('finance.btn_change_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importExecuting">
                        {{ __('common.cancel') }}
                    </button>

                    <button type="button" x-show="importPreview" @click="executePayablesImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#FF9500] hover:bg-[#E8880A] text-white text-[13px] font-semibold shadow-sm shadow-[#FF9500]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importExecuting || !importPreview || (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                        <span x-text="importExecuting ? '{{ __('finance.btn_processing') }}' : ('{{ __('finance.btn_commit_import_bills', ['count' => '']) }}'.replace(':count', (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count))))"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

</div>
@endsection
