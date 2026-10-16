@extends('layouts.app', ['title' => __('finance.receivables_page_title')])

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
                return res.json().then(err => { throw new Error(err.message || @json(__('finance.js_err_import_process'))); });
            }
            return res.json();
        })
        .then(res => {
            this.importLoading = false;
            if (res.success) {
                this.importPreview = res.data;
            } else {
                alert(res.message || @json(__('finance.js_err_import_analyze')));
            }
        })
        .catch(err => {
            this.importLoading = false;
            alert(err.message || @json(__('finance.js_err_import_upload')));
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
            alert(@json(__('finance.js_err_import_no_rows')));
            return;
        }

        const confirmMsg = @json(__('finance.js_confirm_import_receivables')).replace(':count', totalToProcess);
        if (!confirm(confirmMsg)) {
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
                alert(res.message || @json(__('finance.js_err_import_execute')));
            }
        })
        .catch(err => {
            this.importExecuting = false;
            alert(err.message || @json(__('finance.js_err_import_system')));
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
        :title="__('finance.receivables_title')"
        :subtitle="__('finance.receivables_subtitle')">
        <a href="{{ route('finance.receivables.export') }}"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="{{ __('finance.receivables_export_tooltip') }}">
            <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ __('common.export_excel') }}</span>
        </a>

        <button type="button" @click="showImportModal = true"
           class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
           title="{{ __('finance.receivables_import_tooltip') }}">
            <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
            <span>{{ __('finance.btn_import_opening') }}</span>
        </button>

        @if(\App\Support\Context::hasPermission('invoices.create'))
            <a href="{{ route('invoices.create') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('finance.btn_create_new_invoice') }}</span>
            </a>
        @endif
        @if(\App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#34C759]"></i>
                <span>{{ __('finance.btn_invoice_list') }}</span>
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
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.kpi_total_receivables') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-black dark:text-white">Rp {{ number_format($totalBalance, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.kpi_open_invoices_count', ['count' => $totalOpenCount]) }}</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'not_due'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'not_due' ? 'border-[#34C759] shadow-[0_0_0_1.5px_#34C759]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#34C759]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.kpi_ar_not_due') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($notDue, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5">{{ __('finance.kpi_ar_not_due_hint') }}</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_1_30'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_1_30' ? 'border-[#FF9500] shadow-[0_0_0_1.5px_#FF9500]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF9500]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.kpi_ar_overdue_1_30') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($overdue1to30, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">{{ __('finance.kpi_ar_overdue_1_30_hint') }}</p>
        </a>

        <a href="{{ route('finance.receivables', array_merge(request()->except(['page']), ['bucket' => 'overdue_30_plus'])) }}"
           class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border {{ $currentBucket === 'overdue_30_plus' ? 'border-[#FF3B30] shadow-[0_0_0_1.5px_#FF3B30]' : 'border-black/5 dark:border-white/5' }} p-4 flex flex-col justify-between hover:border-[#FF3B30]/50 transition-all shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.kpi_ar_overdue_30_plus') }}</p>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[18px] sm:text-[22px] font-extrabold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($overdue30plus, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-[#FF3B30] dark:text-[#FF453A] mt-0.5">{{ __('finance.kpi_ar_overdue_30_plus_hint') }}</p>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- SEARCH & FILTER BAR                                        --}}
    {{-- ========================================================== --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-[12px]">
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'all'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'all' ? 'bg-[#007AFF] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.filter_all') }}
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'not_due'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'not_due' ? 'bg-[#34C759] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.filter_current') }}
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_1_30'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_1_30' ? 'bg-[#FF9500] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.kpi_ar_overdue_1_30') }}
            </a>
            <a href="{{ route('finance.receivables', array_merge(request()->except(['page', 'bucket']), ['bucket' => 'overdue_30_plus'])) }}"
               class="px-3 py-1.5 rounded-[8px] font-medium transition-colors whitespace-nowrap {{ $currentBucket === 'overdue_30_plus' ? 'bg-[#FF3B30] text-white font-semibold' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ __('finance.kpi_ar_overdue_30_plus') }}
            </a>
        </div>
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex items-center gap-2">
            <input type="hidden" name="bucket" value="{{ $currentBucket }}">
            <div class="relative flex-1 sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('finance.search_receivables_placeholder') }}"
                       class="w-full h-11 sm:h-9 pl-8 pr-3 text-[16px] sm:text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>
            @if(request('search') || request('bucket') !== 'all')
                <a href="{{ route('finance.receivables') }}" class="h-11 sm:h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 flex items-center">{{ __('common.reset') }}</a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- AR TABLE (DESKTOP)                                         --}}
    {{-- ========================================================== --}}
    <div x-data="coocaTableSort()" class="hidden sm:block rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('finance.customer_receivables_table_title') }}</h2>
            <span class="text-[12px] font-bold text-black/50 dark:text-white/50 tabular-nums px-2.5 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06]">{{ __('finance.invoices_count_badge', ['count' => $invoices->total()]) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <x-table-sort-th :col="0" type="string" :title="__('finance.col_invoice_no')" />
                        <x-table-sort-th :col="1" type="string" :title="__('finance.col_customer')" />
                        <x-table-sort-th :col="2" type="date" :title="__('finance.col_issue_date')" />
                        <x-table-sort-th :col="3" type="date" :title="__('finance.col_due_date')" />
                        <x-table-sort-th :col="4" type="number" :title="__('finance.col_total_invoice')" align="right" />
                        <x-table-sort-th :col="5" type="number" :title="__('finance.col_remaining_receivable')" align="right" />
                        <x-table-sort-th :col="6" type="number" :title="__('finance.col_receivable_age')" align="center" />
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 text-right">{{ __('finance.col_action') }}</th>
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
                        <td data-sort-value="{{ strtolower($invoice->invoice_number) }}" class="px-4 py-3.5">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                            @else
                            <span class="tabular-nums font-bold text-black dark:text-white">{{ $invoice->invoice_number }}</span>
                            @endif
                        </td>
                        <td data-sort-value="{{ strtolower($invoice->customer?->name ?? 'pelanggan umum') }}" class="px-4 py-3.5">
                            <div class="font-semibold text-black dark:text-white">{{ $invoice->customer?->name ?? __('finance.default_customer_name') }}</div>
                            @if($invoice->customer?->phone)
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">{{ $invoice->customer->phone }}</div>
                            @endif
                        </td>
                        <td data-sort-value="{{ $invoice->invoice_date?->format('Y-m-d') ?? '' }}" class="px-4 py-3.5 text-black/60 dark:text-white/60 tabular-nums">{{ $invoice->invoice_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td data-sort-value="{{ $invoice->due_date?->format('Y-m-d') ?? '' }}" class="px-4 py-3.5 tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                            {{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}
                        </td>
                        <td data-sort-value="{{ (float) $invoice->total_amount }}" class="px-4 py-3.5 text-right tabular-nums text-black/60 dark:text-white/60 font-medium">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td data-sort-value="{{ (float) $invoice->balance_due }}" class="px-4 py-3.5 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[14px]">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td data-sort-value="{{ (int) $daysDiff }}" class="px-4 py-3.5 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('finance.badge_overdue_days', ['days' => $daysOverdue]) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('finance.badge_remaining_days', ['days' => $daysDiff]) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            @if(\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.show', $invoice) }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>{{ __('common.detail') }}</span>
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
                                    <p class="text-[15px] font-bold text-black/70 dark:text-white/70">{{ __('finance.empty_receivables_title_full') }}</p>
                                    <p class="text-[13px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.empty_receivables_desc_full') }}</p>
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
            <span>{{ __('finance.invoices_count_badge', ['count' => $invoices->total()]) }}</span>
            <div class="flex items-center gap-2">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/30 dark:text-white/30 cursor-not-allowed flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> {{ __('common.previous') }}</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-1"></i> {{ __('common.previous') }}</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums">{{ $invoices->currentPage() }}</span>
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
    {{-- AR LIST CARDS (MOBILE ONLY)                                --}}
    {{-- ========================================================== --}}
    <div class="sm:hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('finance.mobile_receivables_list_title') }}</h2>
            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ __('finance.invoices_count_badge', ['count' => $invoices->total()]) }}</span>
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
                        <p class="text-[13px] font-semibold text-black dark:text-white mt-0.5">{{ $invoice->customer?->name ?? __('finance.default_customer_name') }}</p>
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
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('finance.badge_overdue_days', ['days' => $daysOverdue]) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('finance.badge_remaining_days', ['days' => $daysDiff]) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[12px] bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[12px]">
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('finance.col_issue_date') }}</span>
                        <span class="text-black/70 dark:text-white/70 tabular-nums">{{ $invoice->invoice_date?->translatedFormat('d M Y') ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('finance.col_due_date') }}</span>
                        <span class="tabular-nums {{ $isOverdue ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/70 dark:text-white/70' }}">
                            {{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('finance.col_total_invoice') }}</span>
                        <span class="tabular-nums text-black/60 dark:text-white/60 font-medium">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('finance.col_remaining_receivable') }}</span>
                        <span class="tabular-nums font-bold text-[#34C759] dark:text-[#30D158] text-[13px]">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(\App\Support\Context::hasPermission('invoices.view'))
                <div class="flex items-center justify-end pt-1">
                    <a href="{{ route('invoices.show', $invoice) }}" class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors inline-flex items-center gap-1.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>{{ __('finance.btn_view_invoice_and_pay') }}</span>
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
                        <p class="text-[14px] font-bold text-black/70 dark:text-white/70">{{ __('finance.empty_receivables_title_full') }}</p>
                        <p class="text-[12px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.empty_receivables_desc_full') }}</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        @if($invoices->hasPages())
        <div class="p-3 bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/5 flex items-center justify-between text-[12px]">
            <span class="text-black/50 dark:text-white/50">{{ __('finance.invoices_count_badge', ['count' => $invoices->total()]) }}</span>
            <div class="flex items-center gap-1.5">
                @if($invoices->onFirstPage())
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> {{ __('common.previous') }}</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]"><i data-lucide="chevron-left" class="w-3.5 h-3.5 mr-0.5"></i> {{ __('common.previous') }}</a>
                @endif
                <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-bold flex items-center tabular-nums text-[12px]">{{ $invoices->currentPage() }}</span>
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="h-8 px-2.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-medium flex items-center text-[12px]">{{ __('common.next') }} <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></a>
                @else
                    <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 cursor-not-allowed flex items-center text-[12px]">{{ __('common.next') }} <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL IMPORT PIUTANG PREVIEW (APPLE BENTO XXL MODAL)       --}}
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
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('finance.modal_import_ar_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.modal_import_ar_subtitle') }}</p>
                    </div>
                </div>

                <button type="button" @click="if (!importExecuting) showImportModal = false"
                    class="w-9 h-9 min-w-[44px] min-h-[44px] rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1">
                
                <!-- State 1: Drop Zone -->
                <div x-show="!importPreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('finance.step_1_ar_download_title') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('finance.step_1_ar_download_desc') }}</p>
                        </div>
                        <a href="{{ route('finance.receivables.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('finance.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('finance.step_2_ar_upload_title') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('finance.import_ar_max_size') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#0A84FF] rounded-[20px] p-8 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01] hover:bg-[#007AFF]/[0.02]">
                        <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleImportFileSelect($event)" :disabled="importLoading">
                        
                        <div class="space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!importLoading"></i>
                                <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="importLoading"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? '{{ __('finance.import_ar_analyzing_state') }}' : '{{ __('finance.import_ar_dropzone_title') }}'"></div>
                                <div class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.import_ar_dropzone_sub') }}</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importPreview" class="space-y-5">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('finance.kpi_total_invoices') }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importPreview ? importPreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('finance.kpi_ready_import_count') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importPreview ? importPreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('finance.kpi_duplicate_count') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importPreview ? importPreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('finance.kpi_error_count') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importPreview ? importPreview.error_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-[#007AFF]/[0.08] border border-[#007AFF]/20 space-y-1 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('finance.kpi_receivables_value') }}</span>
                            <div class="text-lg font-bold font-mono text-[#007AFF]" x-text="importPreview ? ('Rp ' + formatCurrency(importPreview.total_balance_due)) : 0"></div>
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
                                {{ __('finance.filter_tab_ready') }} (<span x-text="importPreview ? importPreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('finance.filter_tab_duplicate') }} (<span x-text="importPreview ? importPreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('finance.filter_tab_error') }} (<span x-text="importPreview ? importPreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('finance.duplicate_strategy_prompt') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('finance.duplicate_strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('finance.duplicate_strategy_update') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('finance.col_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_invoice_no') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_customer') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_issue_date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_due_date') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('finance.col_total_invoice') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('finance.col_remaining_receivable') }}</th>
                                        <th class="py-2.5 px-3">{{ __('finance.col_diagnostic') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importPreview ? importPreview.rows.filter(r => importFilter === 'all' || r.status === importFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('finance.badge_status_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('finance.badge_status_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('finance.badge_status_error') }}</span>
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
                        <span>{{ __('finance.btn_change_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importExecuting">
                        {{ __('common.cancel') }}
                    </button>

                    <button type="button" x-show="importPreview" @click="executeReceivablesImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importExecuting || !importPreview || (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                        <span x-text="importExecuting ? '{{ __('finance.import_executing_state') }}' : ('{{ __('finance.btn_commit_import_template') }}'.replace(':count', (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count))))"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

</div>
@endsection
