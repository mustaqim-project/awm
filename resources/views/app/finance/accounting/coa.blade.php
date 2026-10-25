@extends('layouts.app', ['title' => __('finance.coa_page_title')])

@section('content')
    <div x-data="coaManager()" class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="{{ __('finance.coa_header_title') }}"
            subtitle="{{ __('finance.coa_header_subtitle') }}">
            <a href="{{ route('finance.balance-sheet') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="scale" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('finance.balance_sheet') }}</span>
            </a>
            <a href="{{ route('finance.coa.export') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                title="{{ __('finance.tooltip_export_coa_excel') }}">
                <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ __('finance.btn_export_excel') }}</span>
            </a>
            @if(\App\Support\Context::hasPermission('accounting.manage'))
                <button type="button" @click="showImportModal = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                    title="{{ __('finance.tooltip_import_coa_excel') }}">
                    <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('finance.btn_import_excel') }}</span>
                </button>
                <button type="button" @click="createModalOpen = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.35)]">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>{{ __('finance.btn_add_sub_account') }}</span>
                </button>
            @endif
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="accounting" />

        {{-- ========================================================== --}}
        {{-- KPI STATS CARDS (BENTO)                                    --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.total_active_accounts') }}</p>
                    <p class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $stats['active'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.from_total_accounts_registered', ['total' => $stats['total']]) }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="book-marked" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.core_system_accounts') }}</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] mt-1">{{ $stats['system'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.locked_and_protected') }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.custom_sub_accounts') }}</p>
                    <p class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $stats['custom'] }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.created_by_company') }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="git-branch" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.journal_integrity') }}</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            {{ __('finance.sak_emkm_standard') }}
                        </span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.consistent_double_entry') }}</p>
                </div>
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="file-check-2" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- SEARCH & CATEGORY FILTER BAR                               --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 space-y-3 shadow-xs">
            <form method="GET" action="{{ route('finance.coa.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[220px]">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('finance.placeholder_search_coa') }}"
                        class="w-full h-11 sm:h-9 pl-8 pr-3 text-[16px] sm:text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>

                <div class="flex items-center gap-1 overflow-x-auto py-0.5 text-[12px]">
                    <a href="{{ route('finance.coa.index', request()->except('type')) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ !request('type') ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('common.all') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'asset'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'asset' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_asset') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'liability'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'liability' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_liability') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'equity'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'equity' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_equity') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'revenue'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'revenue' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_revenue') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'cogs'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'cogs' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_cogs') }}
                    </a>
                    <a href="{{ route('finance.coa.index', array_merge(request()->except('type'), ['type' => 'expense'])) }}"
                        class="px-3 py-1.5 rounded-[8px] font-medium transition-colors {{ request('type') === 'expense' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('finance.type_expense') }}
                    </a>
                </div>

                <button type="submit"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white text-[13px] font-semibold hover:bg-black/[0.1] transition-colors flex items-center justify-center">
                    {{ __('common.filter') }}
                </button>
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- ACCOUNTS TABLE                                             --}}
        {{-- ========================================================== --}}
        <div x-data="coocaTableSort()" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <x-table-sort-th :col="0" type="string" :title="__('finance.col_account_code')" />
                            <x-table-sort-th :col="1" type="string" :title="__('finance.col_account_name')" />
                            <x-table-sort-th :col="2" type="string" :title="__('finance.col_category_type')" />
                            <x-table-sort-th :col="3" type="string" :title="__('finance.col_normal_balance')" />
                            <x-table-sort-th :col="4" type="string" :title="__('common.status')" />
                            <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($accounts as $acc)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                                <td data-sort-value="{{ $acc->code }}" class="px-4 sm:px-5 py-3.5 tabular-nums text-[13.5px] sm:text-[14px] font-mono font-bold {{ $acc->is_system ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                    @if($acc->parent_id)
                                        <span class="text-black/35 dark:text-white/35 mr-1 font-mono">↳</span>
                                    @endif
                                    {{ $acc->code }}
                                </td>
                                <td data-sort-value="{{ strtolower($acc->name) }}" class="px-4 sm:px-5 py-3.5 text-[13.5px] sm:text-[14px] font-medium text-black dark:text-white">
                                    {{ $acc->name }}
                                    @if($acc->parent)
                                        <span class="block text-[12px] text-black/45 dark:text-white/45 font-normal mt-0.5">{{ __('finance.parent_prefix', ['code' => $acc->parent->code, 'name' => $acc->parent->name]) }}</span>
                                    @endif
                                </td>
                                <td data-sort-value="{{ strtolower($acc->getTypeLabel()) }}" class="px-4 sm:px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-[8px] text-[12px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                        {{ $acc->getTypeLabel() }}
                                    </span>
                                </td>
                                <td data-sort-value="{{ $acc->normal_balance }}" class="px-4 sm:px-5 py-3.5">
                                    <span class="inline-flex items-center uppercase px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider {{ $acc->normal_balance === 'debit' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/12 text-[#007AFF]' }}">
                                        {{ $acc->normal_balance }}
                                    </span>
                                </td>
                                <td data-sort-value="{{ $acc->is_system ? 'system' : 'custom' }}" class="px-4 sm:px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        @if($acc->is_system)
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/55 dark:text-white/55" title="{{ __('finance.tooltip_system_account_locked') }}">
                                                <i data-lucide="lock" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                                {{ __('finance.badge_system') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">
                                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                {{ __('finance.badge_custom') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-5 py-3.5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('finance.general-ledger', ['account_id' => $acc->id]) }}"
                                            title="{{ __('finance.tooltip_general_ledger_account') }}"
                                            aria-label="{{ __('finance.tooltip_general_ledger_account') }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#007AFF]/12 hover:text-[#007AFF] text-black/60 dark:text-white/60 flex items-center justify-center shrink-0 cursor-pointer transition-colors">
                                            <i data-lucide="book-open" class="w-4 h-4"></i>
                                        </a>
                                        @if(\App\Support\Context::hasPermission('accounting.manage'))
                                            <button type="button"
                                                @click="openEdit({
                                                    id: '{{ $acc->id }}',
                                                    code: '{{ $acc->code }}',
                                                    name: '{{ addslashes($acc->name) }}',
                                                    type: '{{ $acc->type }}',
                                                    normal_balance: '{{ $acc->normal_balance }}',
                                                    parent_id: '{{ $acc->parent_id ?? '' }}',
                                                    is_active: {{ $acc->is_active ? 'true' : 'false' }},
                                                    is_system: {{ $acc->is_system ? 'true' : 'false' }}
                                                })"
                                                title="{{ __('finance.tooltip_edit_account') }}"
                                                aria-label="{{ __('finance.tooltip_edit_account') }}"
                                                class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/60 dark:text-white/60 flex items-center justify-center shrink-0 cursor-pointer transition-colors">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                            @if($acc->canBeDeleted())
                                                <button type="button"
                                                    @click="confirmDelete({ id: '{{ $acc->id }}', code: '{{ $acc->code }}', name: '{{ addslashes($acc->name) }}' }, '{{ route('finance.coa.destroy', $acc) }}')"
                                                    title="{{ __('finance.tooltip_delete_sub_account') }}"
                                                    aria-label="{{ __('finance.tooltip_delete_sub_account') }}"
                                                    class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#FF3B30]/15 hover:text-[#FF3B30] text-[#FF3B30] flex items-center justify-center shrink-0 cursor-pointer transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                    {{ __('finance.empty_accounts_search') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL CREATE SUB-ACCOUNT (BENTO APPLE HIG XXL SHEET)       --}}
        {{-- ========================================================== --}}
        @if(\App\Support\Context::hasPermission('accounting.manage'))
        <template x-teleport="body">
            <div x-show="createModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="createModalOpen = false">
                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                    @click.outside="createModalOpen = false"
                    x-data="{ isSubmitting: false }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-[18px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.modal_create_sub_account_title') }}</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.modal_create_sub_account_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="w-9 h-9 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('finance.coa.store') }}" @submit="isSubmitting = true" class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_parent_account') }}</label>
                                <select name="parent_id" class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="">{{ __('finance.no_parent_main_account') }}</option>
                                    @foreach($parentCandidates as $p)
                                        <option value="{{ $p->id }}">{{ $p->code }} – {{ $p->name }} ({{ $p->getTypeLabel() }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_account_code_required') }}</label>
                                <input type="text" name="code" required placeholder="{{ __('finance.placeholder_account_code') }}"
                                    class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_account_type_required') }}</label>
                                <select name="type" required class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="asset">{{ __('finance.option_type_asset') }}</option>
                                    <option value="liability">{{ __('finance.option_type_liability') }}</option>
                                    <option value="equity">{{ __('finance.option_type_equity') }}</option>
                                    <option value="revenue">{{ __('finance.option_type_revenue') }}</option>
                                    <option value="cogs">{{ __('finance.option_type_cogs') }}</option>
                                    <option value="expense">{{ __('finance.option_type_expense') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_sub_account_name_required') }}</label>
                                <input type="text" name="name" required placeholder="{{ __('finance.placeholder_sub_account_name') }}"
                                    class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                            </div>

                            <div>
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_normal_balance_required') }}</label>
                                <select name="normal_balance" required class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="debit">{{ __('finance.option_debit') }}</option>
                                    <option value="credit">{{ __('finance.option_credit') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="createModalOpen = false" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-[0_4px_16px_rgba(0,122,255,0.3)] transition flex items-center justify-center gap-2">
                                <span x-text="isSubmitting ? '{{ __('finance.btn_saving') }}' : '{{ __('finance.btn_save_sub_account') }}'">{{ __('finance.btn_save_sub_account') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ========================================================== --}}
        {{-- MODAL EDIT ACCOUNT (BENTO APPLE HIG XXL SHEET)             --}}
        {{-- ========================================================== --}}
        <template x-teleport="body">
            <div x-show="editModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="editModalOpen = false">
                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                    @click.outside="editModalOpen = false"
                    x-data="{ isSubmitting: false }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/12 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="pencil" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-[18px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.modal_edit_account_title') }}</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.modal_edit_account_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="w-9 h-9 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" :action="'/finance/chart-of-accounts/' + editAccount.id" @submit="isSubmitting = true" class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-if="!editAccount.is_system">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.col_account_code') }}</label>
                                    <input type="text" name="code" x-model="editAccount.code" required
                                        class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                            </template>

                            <div :class="editAccount.is_system ? 'sm:col-span-2' : ''">
                                <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.col_account_name') }}</label>
                                <input type="text" name="name" x-model="editAccount.name" required
                                    class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 pt-1">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editAccount.is_active"
                                class="w-5 h-5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                            <label for="edit_is_active" class="text-[13px] font-semibold text-black/80 dark:text-white/80">{{ __('finance.checkbox_active_account_label') }}</label>
                        </div>

                        <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="editModalOpen = false" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-[0_4px_16px_rgba(0,122,255,0.3)] transition flex items-center justify-center gap-2">
                                <span x-text="isSubmitting ? '{{ __('finance.btn_saving') }}' : '{{ __('finance.btn_update_account') }}'">{{ __('finance.btn_update_account') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ========================================================== --}}
        {{-- MODAL DELETE CONFIRMATION (APPLE HIG BENTO MODAL SHEET)    --}}
        {{-- ========================================================== --}}
        <template x-teleport="body">
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/80 backdrop-blur-md"
                @keydown.escape.window="deleteModalOpen = false">
                <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[22px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-5"
                    @click.outside="deleteModalOpen = false">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-[14px] bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('finance.modal_delete_sub_account_title') }}</h2>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                {{ __('finance.modal_delete_sub_account_prompt', ['account' => '']) }}<span class="font-semibold text-black dark:text-white" x-text="deleteAccountData.code + ' – ' + deleteAccountData.name"></span>
                            </p>
                        </div>
                    </div>

                    <form method="POST" :action="deleteAccountData.url" class="flex items-center justify-end gap-2.5 pt-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-10 px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] transition-all shadow-[0_2px_8px_rgba(255,59,48,0.35)] flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>{{ __('finance.btn_confirm_delete_sub_account') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>
        {{-- ========================================================== --}}
        {{-- MODAL IMPORT COA PREVIEW (APPLE BENTO XXL MODAL)           --}}
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
                            <i data-lucide="book-open-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('finance.modal_import_coa_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.modal_import_coa_subtitle') }}</p>
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
                        <div class="p-4 rounded-[16px] bg-[#FF9500]/[0.08] dark:bg-[#FF9500]/[0.12] border border-[#FF9500]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-[#B26A00] dark:text-[#FF9F0A] font-bold text-[13px]">
                                    <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                    <span>{{ __('finance.step_1_download_coa_template_title') }}</span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('finance.step_1_download_coa_template_desc') }}</p>
                            </div>
                            <a href="{{ route('finance.coa.template') }}"
                               class="h-9 px-4 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>{{ __('finance.btn_download_template_xlsx') }}</span>
                            </a>
                        </div>

                        <!-- Langkah 2: Upload Dropzone -->
                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                                <span>{{ __('finance.step_2_upload_coa_title') }}</span>
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
                                    <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? '{{ __('finance.reading_analyzing_coa') }}' : '{{ __('finance.select_coa_file_or_drag') }}'"></div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.supports_official_coa_template') }}</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- State 2: Pre-Commit Preview -->
                    <div x-show="importPreview" class="space-y-5">
                        
                        <!-- KPI Summary Cards -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('finance.kpi_total_rows') }}</span>
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
                                <span class="text-black/50 dark:text-white/50 font-medium">{{ __('finance.if_account_exists_label') }}</span>
                                <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                    <input type="radio" value="skip" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                    <span>{{ __('finance.strategy_skip') }}</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                    <input type="radio" value="update" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                    <span>{{ __('finance.strategy_update') }}</span>
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
                                            <th class="py-2.5 px-3">{{ __('finance.col_account_code') }}</th>
                                            <th class="py-2.5 px-3">{{ __('finance.col_account_name') }}</th>
                                            <th class="py-2.5 px-3">{{ __('finance.col_classification') }}</th>
                                            <th class="py-2.5 px-3 text-center">{{ __('finance.col_normal_position') }}</th>
                                            <th class="py-2.5 px-3">{{ __('finance.col_parent_code') }}</th>
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
                                                <td class="py-2.5 px-3 font-mono font-bold text-black dark:text-white" x-text="row.code || '-'"></td>
                                                <td class="py-2.5 px-3 font-medium text-black dark:text-white" x-text="row.name || '-'"></td>
                                                <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                    <span class="inline-block uppercase text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 font-semibold" x-text="row.type || '-'"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono font-medium text-black/80 dark:text-white/80" x-text="row.normal_balance ? row.normal_balance.toUpperCase() : '-'"></td>
                                                <td class="py-2.5 px-3 font-mono text-black/60 dark:text-white/60" x-text="row.parent_code || '-'"></td>
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

                        <button type="button" x-show="importPreview" @click="executeCoaImport()"
                            class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="importExecuting || !importPreview || (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) === 0">
                            <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                            <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                            <span x-text="importExecuting ? '{{ __('finance.btn_processing') }}' : ('{{ __('finance.btn_commit_import_accounts', ['count' => '']) }}'.replace(':count', (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count))))"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </template>
    @endif

    </div>

<script>
function coaManager() {
    return {
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        deleteAccountData: { id: '', code: '', name: '', url: '' },
        editAccount: { id: '', code: '', name: '', type: 'asset', normal_balance: 'debit', parent_id: '', is_active: true, is_system: false },
        openEdit(account) {
            this.editAccount = { ...account };
            this.editModalOpen = true;
        },
        confirmDelete(account, deleteUrl) {
            this.deleteAccountData = { id: account.id, code: account.code, name: account.name, url: deleteUrl };
            this.deleteModalOpen = true;
        },

        // Import COA Excel State
        showImportModal: false,
        importFile: null,
        importFileName: '',
        importLoading: false,
        importPreview: null,
        importFilter: 'all',
        importDuplicateStrategy: 'skip',
        importExecuting: false,

        handleImportFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.importFile = file;
                this.importFileName = file.name;
                this.uploadAndPreviewCoa();
            }
        },

        uploadAndPreviewCoa() {
            if (!this.importFile) return;
            this.importLoading = true;
            const formData = new FormData();
            formData.append('file', this.importFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('finance.coa.import-preview') }}', {
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
                    alert(res.message || @json(__('finance.js_failed_analyze_coa')));
                }
            })
            .catch(err => {
                this.importLoading = false;
                alert(err.message || @json(__('finance.js_upload_error')));
            });
        },

        async executeCoaImport() {
            if (!this.importPreview || !this.importPreview.rows || this.importPreview.rows.length === 0) {
                return;
            }

            const totalToProcess = this.importDuplicateStrategy === 'skip' 
                ? this.importPreview.valid_count 
                : (this.importPreview.valid_count + this.importPreview.duplicate_count);

            if (totalToProcess <= 0) {
                alert(@json(__('finance.js_no_accounts_to_import')));
                return;
            }

            if (!confirm(@json(__('finance.js_confirm_import_accounts', ['count' => ''])).replace(':count', totalToProcess))) {
                return;
            }

            this.importExecuting = true;
            fetch('{{ route('finance.coa.import-execute') }}', {
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
                    alert(res.message || @json(__('finance.js_failed_execute_import')));
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
    };
}
</script>
@endsection
