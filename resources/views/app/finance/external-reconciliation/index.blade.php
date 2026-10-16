@extends('layouts.app', ['title' => __('finance.external_recon_page_title')])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12"
    x-data="externalReconApp()"
    x-init="init()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="{{ __('finance.where_the_money_lives_title') }}"
        subtitle="{{ __('finance.where_the_money_lives_subtitle') }}">
        <a href="{{ route('finance.settlements.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            <span>{{ __('finance.payout_hub') }}</span>
        </a>
        <a href="{{ route('finance.cash-bank.ledger') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>{{ __('finance.cash_book') }}</span>
        </a>
    </x-module-header>

    <x-module-tabs module="finance" />

    {{-- ========================================================== --}}
    {{-- PERIOD SELECTOR + LOCATION FILTER                          --}}
    {{-- ========================================================== --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('finance.period_label') }}</label>
            <input type="month" value="{{ $period }}"
                @change="window.location.href = '{{ route('finance.external-recon.index') }}?period=' + $event.target.value + (currentLocationId ? '&location_id=' + currentLocationId : '')"
                class="h-9 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
        </div>
        @if($locations->count() > 1)
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('finance.branch_label') }}</label>
                <select
                    @change="window.location.href = '{{ route('finance.external-recon.index') }}?period={{ $period }}&location_id=' + $event.target.value"
                    class="h-9 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    <option value="">{{ __('finance.all_branches') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION 1: WHERE THE MONEY LIVES — BENTO DASHBOARD         --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-gradient-to-r from-[#007AFF]/5 to-[#5856D6]/5 dark:from-[#007AFF]/10 dark:to-[#5856D6]/10">
            <h2 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                <span class="text-lg">🏦</span>
                {{ __('finance.where_the_money_lives_title') }}
            </h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.section_where_money_lives_desc', ['period' => \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y')]) }}</p>
        </div>

        <div class="p-5 sm:p-6 space-y-5">
            {{-- Grand Total Liquidity --}}
            <div class="p-5 rounded-[16px] bg-gradient-to-br from-[#007AFF]/5 via-white to-[#5856D6]/5 dark:from-[#007AFF]/10 dark:via-[#1C1C1E] dark:to-[#5856D6]/10 border border-[#007AFF]/10 dark:border-[#007AFF]/20 text-center">
                <span class="text-[11px] font-bold uppercase tracking-widest text-[#007AFF]">{{ __('finance.kpi_total_business_liquidity') }}</span>
                <div class="text-[32px] sm:text-[40px] font-black text-black dark:text-white tabular-nums tracking-tight mt-1">
                    {{ $dashboard['currency'] }} {{ number_format($dashboard['liquidity']['total'] ?? 0, 0, ',', '.') }}
                </div>
                @if(($dashboard['liquidity']['discrepancy_count'] ?? 0) > 0)
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] text-[11px] font-bold">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        {{ __('finance.discrepancies_detected', ['count' => $dashboard['liquidity']['discrepancy_count'], 'amount' => $dashboard['currency'] . ' ' . number_format(abs($dashboard['liquidity']['total_variance'] ?? 0), 0, ',', '.')]) }}
                    </div>
                @else
                    <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        {{ __('finance.all_balances_balanced') }}
                    </div>
                @endif
            </div>

            {{-- 3-Column Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Column 1: Internal Cash (COOCA Ledger) --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="landmark" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">{{ __('finance.internal_cash_bank') }}</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">COOCA</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['internal']['total'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5">
                        @foreach($dashboard['internal']['accounts'] ?? [] as $acc)
                            <div class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                        {{ $acc['type'] === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                        {{ $acc['type'] === 'bank' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                        {{ $acc['type'] === 'ewallet' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : '' }}
                                    ">{{ $acc['type'] }}</span>
                                    <span class="font-medium text-black/80 dark:text-white/80">{{ $acc['name'] }}</span>
                                </div>
                                <span class="font-bold tabular-nums text-black dark:text-white">{{ number_format($acc['balance'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        @if(empty($dashboard['internal']['accounts']))
                            <p class="text-[11px] text-black/40 dark:text-white/40 text-center py-3">{{ __('finance.empty_active_cash_accounts') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Column 2: Cooca Pay Escrow --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">{{ __('finance.escrow_cooca_pay') }}</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#FF9500]/10 text-[#D97706]">{{ __('finance.escrow_badge_holding') }}</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#FF9500] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['escrow']['net'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60">{{ __('finance.gross') }}</span>
                            <span class="font-bold tabular-nums">{{ number_format($dashboard['escrow']['gross'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-[#FF3B30]">{{ __('finance.mdr_fee') }}</span>
                            <span class="font-bold tabular-nums text-[#FF3B30]">-{{ number_format($dashboard['escrow']['fee'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60">{{ __('finance.pending_transactions_count') }}</span>
                            <span class="font-bold">{{ __('finance.trx_count', ['count' => $dashboard['escrow']['count'] ?? 0]) }}</span>
                        </div>
                    </div>

                    @if(($dashboard['escrow']['count'] ?? 0) > 0)
                        <a href="{{ route('finance.settlements.index') }}"
                            class="block text-center text-[11px] font-bold text-[#007AFF] hover:underline mt-1">
                            {{ __('finance.disburse_via_payout_hub') }}
                        </a>
                    @endif
                </div>

                {{-- Column 3: External Channels --}}
                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-bold text-black dark:text-white">{{ __('finance.external_accounts') }}</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#AF52DE]/10 text-[#AF52DE]">{{ __('finance.badge_manual') }}</span>
                    </div>

                    <div class="text-[20px] font-extrabold text-[#AF52DE] tabular-nums">
                        {{ $dashboard['currency'] }} {{ number_format($dashboard['external']['total'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="space-y-1.5">
                        @forelse($dashboard['external']['accounts'] ?? [] as $ext)
                            <div class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-[8px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-black/80 dark:text-white/80">{{ $ext['channel_label'] }}</span>
                                    @if($ext['has_discrepancy'])
                                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] animate-pulse"></span>
                                    @endif
                                </div>
                                <span class="font-bold tabular-nums {{ $ext['has_discrepancy'] ? 'text-[#FF3B30]' : 'text-black dark:text-white' }}">
                                    {{ number_format($ext['closing_balance'], 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-[11px] text-black/40 dark:text-white/40 text-center py-3">
                                {{ __('finance.no_external_data_prompt') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION 2: LEMBAR REKONSILIASI AKUN EKSTERNAL              --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.01] dark:bg-white/[0.01]">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('finance.external_recon_sheet_title') }}</h2>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.external_recon_sheet_subtitle') }}</p>
            </div>
            <button type="button" @click="showFormModal = true"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shrink-0 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>{{ __('finance.btn_add_update_sheet') }}</span>
            </button>
        </div>

        {{-- Existing Reconciliation Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-2.5 px-4">{{ __('finance.col_channel_account') }}</th>
                        @if($locations->count() > 1)
                            <th class="py-2.5 px-4">{{ __('finance.col_branch') }}</th>
                        @endif
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_opening_balance') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_inflow') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_disbursement') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_closing_balance_manual') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_system_expectation') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_variance') }}</th>
                        <th class="py-2.5 px-4 text-center">{{ __('common.status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @forelse($reconciliations as $recon)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-black dark:text-white">{{ $recon->channel_label }}</span>
                                <div class="text-[10px] text-black/40 dark:text-white/40 font-mono">{{ $recon->channel_type }}</div>
                            </td>
                            @if($locations->count() > 1)
                                <td class="py-3.5 px-4 text-black/60 dark:text-white/60">{{ $recon->location?->name ?? __('finance.all_branches_hq') }}</td>
                            @endif
                            <td class="py-3.5 px-4 text-right tabular-nums font-medium">{{ number_format($recon->opening_balance, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#34C759]">+{{ number_format($recon->total_inflow, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#FF3B30]">-{{ number_format($recon->total_disbursement, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold text-black dark:text-white">{{ number_format($recon->closing_balance, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-black/50 dark:text-white/50">{{ number_format($recon->expected_closing, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold {{ $recon->hasDiscrepancy() ? 'text-[#FF3B30]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                                {{ $recon->variance >= 0 ? '+' : '' }}{{ number_format($recon->variance, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($recon->status === 'discrepancy')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">{{ __('finance.badge_discrepancy') }}</span>
                                @elseif($recon->status === 'submitted')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">{{ __('finance.badge_balanced_checked') }}</span>
                                @elseif($recon->status === 'reviewed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF]">{{ __('finance.badge_reviewed') }}</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/10 text-black/60">{{ __('finance.badge_draft') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $locations->count() > 1 ? 9 : 8 }}" class="py-12 text-center text-black/40 dark:text-white/40">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-[13px] font-medium text-black/70 dark:text-white/70">{{ __('finance.no_recon_data_period') }}</span>
                                    <span class="text-[11px]">{{ __('finance.click_add_sheet_prompt') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL: INPUT REKONSILIASI BARU (BENTO APPLE HIG XXL SHEET) --}}
    {{-- ========================================================== --}}
    <template x-teleport="body">
        <div x-show="showFormModal"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="if (!isSubmitting) showFormModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-recon-title">

            {{-- Backdrop --}}
            <div x-show="showFormModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="if (!isSubmitting) showFormModal = false"></div>

            {{-- Dialog Container --}}
            <div class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="if (!isSubmitting) showFormModal = false">
                <div class="relative w-full sm:max-w-3xl lg:max-w-4xl rounded-t-[24px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden max-h-[92vh] flex flex-col"
                    @click.stop
                    x-show="showFormModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 text-[#007AFF] flex items-center justify-center border border-blue-500/20">
                                <i data-lucide="scale" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 id="modal-recon-title" class="text-base font-bold text-black dark:text-white tracking-tight">{{ __('finance.modal_input_recon_title') }}</h3>
                                <p class="text-xs text-black/50 dark:text-white/50">{{ __('finance.modal_input_recon_subtitle', ['period' => \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y')]) }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showFormModal = false" :disabled="isSubmitting"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition cursor-pointer"
                            aria-label="{{ __('common.close_modal') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form @submit.prevent="submitRecon()" class="flex-1 overflow-y-auto p-6 space-y-6">
                <input type="hidden" x-model="formData.period" value="{{ $period }}">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {{-- Left Column: Channel & Account Details (5 cols) --}}
                    <div class="lg:col-span-5 space-y-4">
                        <div class="rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-4">
                            <h4 class="text-xs font-bold text-black/70 dark:text-white/70 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                {{ __('finance.channel_and_identity') }}
                            </h4>

                            <div>
                                <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.field_channel_type') }} <span class="text-red-500">*</span></label>
                                <select x-model="formData.channel_type" @change="autoLabel()" required
                                    class="w-full h-11 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3.5 text-sm text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-sm">
                                    <option value="">{{ __('finance.select_channel_placeholder') }}</option>
                                    @foreach($channelOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.field_channel_label') }} <span class="text-red-500">*</span></label>
                                <input type="text" x-model="formData.channel_label" required placeholder="{{ __('finance.placeholder_channel_label') }}"
                                    class="w-full h-11 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3.5 text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-sm">
                            </div>

                            @if($locations->count() > 1)
                                <div>
                                    <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.field_outlet_branch') }}</label>
                                    <select x-model="formData.location_id"
                                        class="w-full h-11 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3.5 text-sm text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-sm">
                                        <option value="">{{ __('finance.headquarters_all_branches') }}</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.notes_optional') }}</label>
                                <textarea x-model="formData.notes" rows="2" placeholder="{{ __('finance.placeholder_recon_notes') }}"
                                    class="w-full rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 p-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-sm resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Balance Numbers & Variance Calculation (7 cols) --}}
                    <div class="lg:col-span-7 space-y-4">
                        <div class="rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 space-y-3">
                            <h4 class="text-xs font-bold text-black/70 dark:text-white/70 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                {{ __('finance.data_balance_mutations_month') }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="bg-white dark:bg-[#2C2C2E] rounded-xl p-3 border border-black/5 dark:border-white/5 shadow-sm">
                                    <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">{{ __('finance.field_opening_balance_month') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs text-black/40 dark:text-white/40 font-bold">Rp</span>
                                        <input type="number" x-model.number="formData.opening_balance" required min="0" step="1" placeholder="0"
                                            class="w-full h-9 pl-9 pr-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-[#2C2C2E] rounded-xl p-3 border border-black/5 dark:border-white/5 shadow-sm">
                                    <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">{{ __('finance.field_closing_balance_month') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs text-black/40 dark:text-white/40 font-bold">Rp</span>
                                        <input type="number" x-model.number="formData.closing_balance" required min="0" step="1" placeholder="0"
                                            class="w-full h-9 pl-9 pr-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-[#2C2C2E] rounded-xl p-3 border border-black/5 dark:border-white/5 shadow-sm">
                                    <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">{{ __('finance.field_total_inflow') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs text-[#34C759] font-bold">Rp</span>
                                        <input type="number" x-model.number="formData.total_inflow" required min="0" step="1" placeholder="0"
                                            class="w-full h-9 pl-9 pr-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs font-bold text-[#34C759] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-[#2C2C2E] rounded-xl p-3 border border-black/5 dark:border-white/5 shadow-sm">
                                    <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">{{ __('finance.field_total_disbursement') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs text-[#FF9500] font-bold">Rp</span>
                                        <input type="number" x-model.number="formData.total_disbursement" required min="0" step="1" placeholder="0"
                                            class="w-full h-9 pl-9 pr-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs font-bold text-[#FF9500] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bento Live Calculation Preview --}}
                        <div class="rounded-2xl p-4 transition-all duration-300 border"
                            :class="Math.abs(calcVariance()) > 100 ? 'bg-[#FF3B30]/5 border-[#FF3B30]/20 dark:bg-[#FF3B30]/10' : 'bg-[#34C759]/5 border-[#34C759]/20 dark:bg-[#34C759]/10'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5"
                                    :class="Math.abs(calcVariance()) > 100 ? 'text-[#FF3B30]' : 'text-[#34C759]'">
                                    <i :data-lucide="Math.abs(calcVariance()) > 100 ? 'alert-triangle' : 'check-circle-2'" class="w-4 h-4"></i>
                                    {{ __('finance.recon_status_label') }}
                                </span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md uppercase"
                                    :class="Math.abs(calcVariance()) > 100 ? 'bg-[#FF3B30]/15 text-[#FF3B30]' : 'bg-[#34C759]/15 text-[#34C759]'"
                                    x-text="Math.abs(calcVariance()) > 100 ? '{{ __('finance.discrepancy_detected_badge') }}' : '{{ __('finance.match_balanced_badge') }}'">
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between items-center text-black/60 dark:text-white/60">
                                    <span>{{ __('finance.expected_balance_formula') }}</span>
                                    <span class="font-bold tabular-nums text-black dark:text-white" x-text="formatRupiah(calcExpected())"></span>
                                </div>
                                <div class="flex justify-between items-center pt-1.5 border-t border-black/5 dark:border-white/5">
                                    <span class="font-semibold text-black dark:text-white">{{ __('finance.variance_value') }}</span>
                                    <span class="font-extrabold text-sm tabular-nums" :class="Math.abs(calcVariance()) > 100 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="formatRupiah(calcVariance())"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <button type="button" @click="showFormModal = false" :disabled="isSubmitting"
                        class="h-12 px-5 rounded-xl text-xs font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition disabled:opacity-40">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="h-12 px-7 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold transition active:scale-[0.98] flex items-center gap-2 shadow-lg shadow-blue-500/20 disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!isSubmitting">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <span x-text="isSubmitting ? '{{ __('finance.btn_saving_recon') }}' : '{{ __('finance.btn_save_recon') }}'"></span>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function externalReconApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        showFormModal: false,
        isSubmitting: false,
        currentLocationId: '{{ $locationId ?? '' }}',
        formData: {
            channel_type: '',
            channel_label: '',
            location_id: '',
            period: '{{ $period }}',
            opening_balance: 0,
            total_inflow: 0,
            total_disbursement: 0,
            closing_balance: 0,
            notes: '',
        },

        init() {},

        autoLabel() {
            const labels = @json($channelOptions);
            if (this.formData.channel_type && labels[this.formData.channel_type]) {
                this.formData.channel_label = labels[this.formData.channel_type];
            }
        },

        calcExpected() {
            return (this.formData.opening_balance || 0) + (this.formData.total_inflow || 0) - (this.formData.total_disbursement || 0);
        },

        calcVariance() {
            return (this.formData.closing_balance || 0) - this.calcExpected();
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        async submitRecon() {
            if (!this.formData.channel_type || !this.formData.channel_label) {
                alert(@json(__('finance.js_select_channel_and_label')));
                return;
            }

            this.isSubmitting = true;
            try {
                const res = await fetch("{{ route('finance.external-recon.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.formData),
                });
                const data = await res.json();
                if (data.success) {
                    this.isModalOpen = false;
                    if (window.AppAlert) {
                        AppAlert.success(data.message || @json(__('finance.js_recon_recorded_success')));
                    } else {
                        alert(data.message || @json(__('finance.js_recon_recorded_success')));
                    }
                    this.formData = {
                        channel_type: '',
                        channel_label: '',
                        period_start: '',
                        period_end: '',
                        statement_ending_balance: 0,
                        ledger_balance: 0,
                        notes: ''
                    };
                } else {
                    if (window.AppAlert) {
                        AppAlert.error(data.message || @json(__('finance.js_failed_save_recon')));
                    } else {
                        alert(data.message || @json(__('finance.js_failed_save_recon')));
                    }
                }
            } catch (e) {
                if (window.AppAlert) {
                    AppAlert.error(@json(__('finance.js_error_occurred')) + e.message);
                } else {
                    alert(@json(__('finance.js_error_occurred')) + e.message);
                }
            } finally {
                this.isSubmitting = false;
            }
        },
    };
}
</script>
@endsection
