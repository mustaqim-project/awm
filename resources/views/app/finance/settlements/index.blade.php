@extends('layouts.app', ['title' => __('finance.payout_hub_page_title')])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="payoutHubApp()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="{{ __('finance.payout_hub_page_title') }}"
        subtitle="{{ __('finance.payout_hub_subtitle') }}">
        <a href="{{ route('finance.cash-bank.ledger') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>{{ __('finance.cash_ledger') }}</span>
        </a>
        <a href="{{ route('finance.journals.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="split" class="w-4 h-4"></i>
            <span>{{ __('finance.auto_journals') }}</span>
        </a>
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- MODULE TABS (SSOT)                                         --}}
    {{-- ========================================================== --}}
    <x-module-tabs module="finance" />

    {{-- ========================================================== --}}
    {{-- BENTO KPI CARDS: COOCA PAY CLEARING STATS                  --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Unsettled Gross --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">{{ __('finance.kpi_escrow_funds') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#D97706] dark:text-[#FBBF24]">{{ __('finance.cooca_pay_escrow') }}</span>
            </div>
            <div class="text-[22px] font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_gross'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center justify-between">
                <span>{{ __('finance.escrow_ready_to_disburse', ['count' => $unsettledData['summary']['count'] ?? 0]) }}</span>
                <span class="font-mono text-[10px]">{{ __('finance.qris_and_online') }}</span>
            </div>
        </div>

        {{-- Card 2: Estimated Gateway Fee --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">{{ __('finance.kpi_total_mdr_fee') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">{{ __('finance.deduction_badge') }}</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_fee'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                {{ __('finance.mdr_rate_description') }}
            </div>
        </div>

        {{-- Card 3: Net Payout Estimate --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">{{ __('finance.kpi_net_payout_estimate') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">{{ __('finance.your_bank_badge') }}</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                {{ __('finance.net_after_mdr') }}
            </div>
        </div>

        {{-- Card 4: Historical Completed Payouts --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium text-[11px] uppercase tracking-wider">{{ __('finance.kpi_completed_payouts') }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">{{ __('finance.transferred_badge') }}</span>
            </div>
            <div class="text-[22px] font-extrabold text-[#007AFF] tabular-nums tracking-tight">
                {{ $business->currency_symbol }} {{ number_format($settlements->sum('net_amount'), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                {{ __('finance.payout_history_count', ['count' => $settlements->total()]) }}
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: REKENING PENARIKAN TERVERIFIKASI                  --}}
    {{-- ========================================================== --}}
    @if($payoutBankAccounts->isEmpty())
        <div class="rounded-[16px] bg-[#FF9500]/5 dark:bg-[#FF9500]/10 border border-[#FF9500]/20 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-[#FF9500]/15 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-[#FF9500]"></i>
                </div>
                <div>
                    <h3 class="text-[14px] font-bold text-[#D97706] dark:text-[#FBBF24]">{{ __('finance.no_verified_payout_accounts_title') }}</h3>
                    <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">{{ __('finance.no_verified_payout_accounts_desc') }}</p>
                </div>
            </div>
            <a href="{{ route('finance.payout-accounts.index') }}"
                class="h-10 px-5 rounded-[10px] bg-[#FF9500] hover:bg-[#E68900] text-white text-xs font-bold flex items-center justify-center gap-1.5 shrink-0 transition active:scale-[0.97]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                {{ __('finance.btn_register_account') }}
            </a>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- SECTION: DAFTAR TRANSAKSI SIAP PENCAIRAN (ACTION)          --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.01] dark:bg-white/[0.01]">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('finance.payout_disbursement_section_title') }}</h2>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.payout_disbursement_section_desc') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="toggleSelectAll()"
                    class="h-8 px-3 rounded-[8px] text-xs font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition">
                    <span x-text="selectedItems.length === unsettledItems.length && unsettledItems.length > 0 ? '{{ __('finance.btn_deselect_all') }}' : '{{ __('finance.btn_select_all') }}'"></span>
                </button>
            </div>
        </div>

        {{-- Table of Unsettled Orders --}}
        <div class="overflow-x-auto max-h-[380px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="sticky top-0 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-sm border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold z-10">
                    <tr>
                        <th class="py-2.5 px-4 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll()" :checked="selectedItems.length === unsettledItems.length && unsettledItems.length > 0"
                                class="rounded border-black/20 text-[#007AFF] focus:ring-0 focus:ring-offset-0">
                        </th>
                        <th class="py-2.5 px-4">{{ __('finance.col_order_ref') }}</th>
                        <th class="py-2.5 px-4">{{ __('finance.col_channel_source') }}</th>
                        <th class="py-2.5 px-4">{{ __('common.customer') }}</th>
                        <th class="py-2.5 px-4">{{ __('finance.col_payment_date') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_gross_nominal') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_mdr_fee') }}</th>
                        <th class="py-2.5 px-4 text-right">{{ __('finance.col_net_nominal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    <template x-for="item in unsettledItems" :key="item.payment_type + '-' + item.payment_id">
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer"
                            @click="toggleItem(item)">
                            <td class="py-3 px-4 text-center" @click.stop>
                                <input type="checkbox" :value="item.payment_type + '-' + item.payment_id"
                                    :checked="isSelected(item)"
                                    @change="toggleItem(item)"
                                    class="rounded border-black/20 text-[#007AFF] focus:ring-0 focus:ring-offset-0">
                            </td>
                            <td class="py-3 px-4 font-mono font-medium">
                                <span class="text-black dark:text-white" x-text="'#' + item.order_number"></span>
                                <div class="text-[10px] text-black/45 dark:text-white/45 font-sans" x-text="item.reference"></div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md font-semibold text-[10px]"
                                    :class="item.payment_type === 'commerce_order' ? 'bg-[#AF52DE]/10 text-[#AF52DE]' : 'bg-[#007AFF]/10 text-[#007AFF]'"
                                    x-text="item.source + ' (' + item.channel + ')'"></span>
                            </td>
                            <td class="py-3 px-4 font-medium" x-text="item.customer"></td>
                            <td class="py-3 px-4 text-black/60 dark:text-white/60" x-text="item.date"></td>
                            <td class="py-3 px-4 text-right font-medium tabular-nums" x-text="formatRupiah(item.gross_amount)"></td>
                            <td class="py-3 px-4 text-right text-[#FF3B30] tabular-nums" x-text="formatRupiah(item.fee_amount)"></td>
                            <td class="py-3 px-4 text-right font-bold text-[#34C759] dark:text-[#30D158] tabular-nums" x-text="formatRupiah(item.net_amount)"></td>
                        </tr>
                    </template>
                    <tr x-show="unsettledItems.length === 0">
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                                <span class="text-[13px] font-medium text-black/70 dark:text-white/70">{{ __('finance.all_payments_settled_title') }}</span>
                                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('finance.no_pending_escrow_subtitle') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Payout Action Bar --}}
        <div x-show="unsettledItems.length > 0" class="p-5 sm:p-6 border-t border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.02]">
            <form @submit.prevent="submitPayout()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Rekening Penarikan Terverifikasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.verified_payout_account_label') }}</label>
                        <select x-model="payoutBankAccountId" required
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">{{ __('finance.select_verified_account_placeholder') }}</option>
                            @foreach($payoutBankAccounts as $pba)
                                <option value="{{ $pba->id }}" {{ $pba->is_primary ? 'selected' : '' }}>
                                    {{ $pba->bank_name }} - {{ $pba->account_number }} ({{ __('finance.account_holder_name_prefix', ['name' => $pba->account_holder_name]) }}){{ $pba->is_primary ? ' ' . __('finance.star_primary') : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">
                            {{ __('finance.account_owner_must_match', ['owner' => $ownerName]) }}
                        </p>
                    </div>

                    {{-- Mode Pencairan --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.payout_mode_label') }}</label>
                        <select x-model="payoutMode"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="manual">{{ __('finance.payout_mode_manual') }}</option>
                            <option value="auto_h1">{{ __('finance.payout_mode_auto_h1') }}</option>
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">{{ __('finance.notes_optional') }}</label>
                        <input type="text" x-model="settlementNotes" placeholder="{{ __('finance.settlement_notes_placeholder') }}"
                            class="w-full h-11 sm:h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                {{-- Auto-Payout H+1 Info Banner --}}
                <div x-show="payoutMode === 'auto_h1'" x-cloak
                    class="rounded-[12px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-3.5 flex items-start gap-3">
                    <i data-lucide="clock" class="w-5 h-5 text-[#007AFF] mt-0.5 shrink-0"></i>
                    <div class="text-xs text-black/70 dark:text-white/70">
                        <strong class="text-[#007AFF]">{{ __('finance.auto_payout_h1_title') }}</strong>
                        {{ __('finance.auto_payout_h1_desc') }}
                    </div>
                </div>

                {{-- Summary & Submit --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-black/5 dark:border-white/10">
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div>
                            <span class="text-black/50 dark:text-white/50">{{ __('finance.selected_label') }}:</span>
                            <span class="font-bold text-black dark:text-white" x-text="selectedItems.length + ' {{ strtolower(__('common.transactions')) }}'"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">{{ __('finance.total_gross_label') }}:</span>
                            <span class="font-bold tabular-nums text-black dark:text-white" x-text="formatRupiah(calculateGross())"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">{{ __('finance.total_fee_label') }}:</span>
                            <span class="font-bold tabular-nums text-[#FF3B30]" x-text="formatRupiah(calculateFee())"></span>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50">{{ __('finance.net_to_bank_label') }}:</span>
                            <span class="font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158] text-sm" x-text="formatRupiah(calculateNet())"></span>
                        </div>
                    </div>

                    <button type="submit"
                        :disabled="selectedItems.length === 0 || !payoutBankAccountId || isProcessing"
                        class="min-h-[44px] h-11 sm:h-10 px-6 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40 flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span x-text="isProcessing ? '{{ __('finance.submitting_request') }}' : (payoutMode === 'auto_h1' ? '{{ __('finance.schedule_auto_payout_h1') }}' : '{{ __('finance.submit_payout_to_cooca') }}')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SECTION: RIWAYAT SETTLEMENT & PENCAIRAN SELESAI            --}}
    {{-- ========================================================== --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('finance.payout_history_section_title') }}</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.payout_history_section_desc') }}</p>
        </div>

        <div x-data="coocaTableSort()" class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <x-table-sort-th :col="0" type="string" :title="__('finance.col_settlement_number')" />
                        <x-table-sort-th :col="1" type="date" :title="__('common.date')" />
                        <x-table-sort-th :col="2" type="string" :title="__('finance.col_destination_account')" />
                        <x-table-sort-th :col="3" type="string" :title="__('finance.col_mode')" />
                        <x-table-sort-th :col="4" type="number" :title="__('finance.col_gross')" align="right" />
                        <x-table-sort-th :col="5" type="number" :title="__('finance.col_fee_mdr')" align="right" />
                        <x-table-sort-th :col="6" type="number" :title="__('finance.col_net_bank')" align="right" />
                        <x-table-sort-th :col="7" type="string" :title="__('common.status')" align="center" />
                        <th class="py-3 px-5 text-right">{{ __('finance.col_proof_and_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @forelse($settlements as $settlement)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td data-sort-value="{{ strtolower($settlement->settlement_number) }}" class="py-3.5 px-5 font-mono font-bold text-black dark:text-white">
                                {{ $settlement->settlement_number }}
                            </td>
                            <td data-sort-value="{{ \Carbon\Carbon::parse($settlement->settlement_date)->timestamp }}" class="py-3.5 px-5 text-black/60 dark:text-white/60">
                                {{ \Carbon\Carbon::parse($settlement->settlement_date)->translatedFormat('d M Y') }}
                            </td>
                            <td data-sort-value="{{ strtolower($settlement->destination_bank ?? __('finance.main_cash_account')) }}" class="py-3.5 px-5 font-medium">
                                {{ $settlement->destination_bank ?? __('finance.main_cash_account') }}
                            </td>
                            <td data-sort-value="{{ $settlement->payout_mode }}" class="py-3.5 px-5">
                                @if($settlement->payout_mode === 'auto_h1')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Auto H+1</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">Manual</span>
                                @endif
                            </td>
                            <td data-sort-value="{{ (float) $settlement->gross_amount }}" class="py-3.5 px-5 text-right font-medium tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->gross_amount, 0, ',', '.') }}
                            </td>
                            <td data-sort-value="{{ (float) $settlement->fee_amount }}" class="py-3.5 px-5 text-right text-[#FF3B30] tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->fee_amount, 0, ',', '.') }}
                            </td>
                            <td data-sort-value="{{ (float) $settlement->net_amount }}" class="py-3.5 px-5 text-right font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
                            </td>
                            <td data-sort-value="{{ $settlement->status }}" class="py-3.5 px-5 text-center">
                                @if($settlement->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        {{ __('finance.status_transferred_checked') }}
                                    </span>
                                @elseif($settlement->status === 'pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24]">
                                        {{ __('finance.status_waiting_transfer') }}
                                    </span>
                                @elseif($settlement->status === 'rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        {{ __('finance.status_rejected') }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-black/10 text-black/70">
                                        {{ ucfirst($settlement->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($settlement->proof_image_path)
                                        <button type="button"
                                            @click="openProofModal('{{ $settlement->proof_image_url }}', '{{ $settlement->settlement_number }}', '{{ number_format($settlement->net_amount, 0, ',', '.') }}', '{{ $settlement->destination_bank }}', '{{ $settlement->transferred_at ? $settlement->transferred_at->translatedFormat('d M Y H:i') : '-' }}', '{{ addslashes($settlement->admin_notes ?? '') }}')"
                                            class="h-8 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition flex items-center gap-1 shadow-xs">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('finance.btn_payment_proof') }}</span>
                                        </button>
                                    @elseif($settlement->status === 'pending')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium text-[#FF9500] bg-[#FF9500]/10">
                                            {{ __('finance.status_waiting_proof') }}
                                        </span>
                                    @endif

                                    <a href="{{ route('finance.settlements.show', $settlement) }}"
                                        class="h-8 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1">
                                        <span>{{ __('common.detail') }}</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40 text-xs">
                                {{ __('finance.empty_payout_history') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/10">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL PREVIEW BUKTI TRANSFER (APPLE HIG)                  --}}
    {{-- ========================================================== --}}
    <template x-teleport="body">
        <div x-show="showProofModal"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showProofModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-proof-title">

            {{-- Backdrop --}}
            <div x-show="showProofModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showProofModal = false"></div>

            {{-- Dialog Container --}}
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showProofModal = false">
                <div class="relative w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
                    @click.stop
                    x-show="showProofModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <div>
                            <h3 id="modal-proof-title" class="text-[15px] font-bold text-black dark:text-white">{{ __('finance.proof_payment_transfer_title') }}</h3>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Settlement #' + proofSettlementNumber"></p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a :href="proofImageUrl" target="_blank" download title="{{ __('finance.download_transfer_proof') }}"
                                class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 transition cursor-pointer"
                                aria-label="{{ __('finance.download_transfer_proof') }}">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </a>
                            <button type="button" @click="showProofModal = false"
                                class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition cursor-pointer"
                                aria-label="{{ __('common.close_modal') }}">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

            <div class="p-6 space-y-4">
                <div class="rounded-[18px] bg-black/[0.03] dark:bg-black/40 border border-black/[0.05] dark:border-white/5 p-2 flex items-center justify-center overflow-hidden">
                    <img :src="proofImageUrl" alt="{{ __('finance.bank_transfer_proof_alt') }}" class="max-h-[360px] w-auto rounded-[12px] object-contain shadow-xs">
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('finance.net_nominal_to_bank') }}:</span>
                        <span class="font-extrabold text-[#34C759] text-[13px] tabular-nums" x-text="'Rp ' + proofNetAmount"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('finance.destination_account_label') }}:</span>
                        <span class="font-semibold text-black dark:text-white" x-text="proofDestinationBank"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('finance.admin_transfer_time_label') }}:</span>
                        <span class="font-medium text-black dark:text-white" x-text="proofTransferredAt"></span>
                    </div>
                    <div x-show="proofAdminNotes">
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('finance.admin_notes_label') }}:</span>
                        <span class="font-medium text-black dark:text-white" x-text="proofAdminNotes"></span>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </template>

</div>

{{-- ========================================================== --}}
{{-- ALPINE.JS COOCA PAY PAYOUT HUB ENGINE                     --}}
{{-- ========================================================== --}}
<script>
function payoutHubApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        unsettledItems: @json($unsettledData['items'] ?? []),
        selectedItems: [],
        payoutBankAccountId: '{{ $payoutBankAccounts->firstWhere("is_primary", true)?->id ?? $payoutBankAccounts->first()?->id ?? "" }}',
        payoutMode: 'manual',
        settlementNotes: '',
        isProcessing: false,

        showProofModal: false,
        proofImageUrl: '',
        proofSettlementNumber: '',
        proofNetAmount: '',
        proofDestinationBank: '',
        proofTransferredAt: '',
        proofAdminNotes: '',

        init() {
            this.selectedItems = this.unsettledItems.map(i => i.payment_type + '-' + i.payment_id);
        },

        openProofModal(url, number, net, bank, time, notes) {
            this.proofImageUrl = url;
            this.proofSettlementNumber = number;
            this.proofNetAmount = net;
            this.proofDestinationBank = bank;
            this.proofTransferredAt = time;
            this.proofAdminNotes = notes;
            this.showProofModal = true;
        },

        isSelected(item) {
            const key = item.payment_type + '-' + item.payment_id;
            return this.selectedItems.includes(key);
        },

        toggleItem(item) {
            const key = item.payment_type + '-' + item.payment_id;
            const idx = this.selectedItems.indexOf(key);
            if (idx > -1) {
                this.selectedItems.splice(idx, 1);
            } else {
                this.selectedItems.push(key);
            }
        },

        toggleSelectAll() {
            if (this.selectedItems.length === this.unsettledItems.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.unsettledItems.map(i => i.payment_type + '-' + i.payment_id);
            }
        },

        getSelectedObjects() {
            return this.unsettledItems.filter(i => this.selectedItems.includes(i.payment_type + '-' + i.payment_id));
        },

        calculateGross() {
            return this.getSelectedObjects().reduce((acc, i) => acc + Number(i.gross_amount || 0), 0);
        },

        calculateFee() {
            return this.getSelectedObjects().reduce((acc, i) => acc + Number(i.fee_amount || 0), 0);
        },

        calculateNet() {
            return Math.max(0, this.calculateGross() - this.calculateFee());
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        async submitPayout() {
            if (this.selectedItems.length === 0) {
                alert(@json(__('finance.js_select_min_one_transaction')));
                return;
            }
            if (!this.payoutBankAccountId) {
                alert(@json(__('finance.js_select_payout_account')));
                return;
            }

            const allocations = this.getSelectedObjects().map(i => ({
                payment_type: i.payment_type,
                payment_id: i.payment_id,
                amount: i.gross_amount
            }));

            this.isProcessing = true;
            try {
                const res = await fetch("{{ route('finance.settlements.reconcile') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        payout_bank_account_id: this.payoutBankAccountId,
                        payout_mode: this.payoutMode,
                        notes: this.settlementNotes,
                        allocations: allocations,
                        fee_amount: this.calculateFee()
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.showPayoutModal = false;
                    const selectedKeys = [...this.selectedItems];
                    this.unsettledItems = this.unsettledItems.filter(i => !selectedKeys.includes(i.payment_type + '-' + i.payment_id));
                    this.selectedItems = [];
                    if (window.AppAlert) {
                        AppAlert.success(data.message || @json(__('finance.js_payout_processed_success')));
                    } else {
                        alert(data.message || @json(__('finance.js_payout_processed_success')));
                    }
                } else {
                    if (window.AppAlert) {
                        AppAlert.error(data.message || @json(__('finance.js_payout_process_failed')));
                    } else {
                        alert(data.message || @json(__('finance.js_payout_process_failed')));
                    }
                }
            } catch (e) {
                if (window.AppAlert) {
                    AppAlert.error(@json(__('finance.js_error_occurred')) + e.message);
                } else {
                    alert(@json(__('finance.js_error_occurred')) + e.message);
                }
            } finally {
                this.isProcessing = false;
            }
        }
    };
}
</script>
@endsection
