@extends('layouts.app', [
    'title' => __('marketplace.finances_title') . ' - ' . $business->name,
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    activeTab: 'orders',
    syncModal: false,
    detailModal: false,
    reconcileModal: false,
    selectedOrder: null,
    selectedSettlement: null,
    targetCashAccountId: '{{ $cashAccounts->first()?->id }}',
    submitting: false,
    syncing: false,
    async triggerSync() {
        if (this.syncing) return;
        this.syncing = true;
        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const res = await fetch('{{ route('marketplace-hub.finances.sync') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({
                    channel: '{{ request('channel') }}',
                    account_id: '{{ request('account_id') }}'
                })
            });
            const data = await res.json();
            if (data.success) {
                if (window.AppAlert) AppAlert.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                if (window.AppAlert) AppAlert.toast(data.message || 'Gagal sinkronisasi settlement', 'error');
            }
        } catch (e) {
            if (window.AppAlert) AppAlert.toast('Terjadi kesalahan koneksi', 'error');
        } finally {
            this.syncing = false;
            this.syncModal = false;
        }
    },
    openDetail(order) {
        this.selectedOrder = order;
        this.detailModal = true;
    },
    openReconcile(settlement) {
        this.selectedSettlement = settlement;
        this.reconcileModal = true;
    },
    async submitReconcile() {
        if (!this.selectedSettlement || this.submitting) return;
        this.submitting = true;
        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const res = await fetch(`/marketplace-hub/finances/settlements/${this.selectedSettlement.id}/reconcile`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({
                    cash_account_id: this.targetCashAccountId
                })
            });
            const data = await res.json();
            if (data.success) {
                if (window.AppAlert) AppAlert.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                if (window.AppAlert) AppAlert.toast(data.message || 'Gagal rekonsiliasi', 'error');
            }
        } catch (e) {
            if (window.AppAlert) AppAlert.toast('Terjadi kesalahan saat rekonsiliasi bank', 'error');
        } finally {
            this.submitting = false;
            this.reconcileModal = false;
        }
    }
}">

    {{-- 1. MODULE HEADER & TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.finances_title')"
        :subtitle="__('marketplace.finances_subtitle')">
        <x-slot:actions>
            <button @click="syncModal = true"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': syncing}"></i>
                <span>{{ __('marketplace.action_sync_finances') }}</span>
            </button>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center gap-1.5">
                <i data-lucide="store" class="w-4 h-4"></i>
                <span class="hidden sm:inline">{{ __('marketplace.action_view_channels') }}</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    {{-- 2. BENTO FINANCIAL SUMMARY KPIS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Metric 1: Total Gross Sales -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_gross_sales') }}</span>
                <i data-lucide="trending-up" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="text-[20px] sm:text-[24px] font-extrabold text-black dark:text-white tracking-tight">
                Rp {{ number_format($summary['gross_sales'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">{{ $summary['total_orders'] }} pesanan • {{ number_format($summary['total_items_sold']) }} item</p>
        </div>

        <!-- Metric 2: Total Marketplace Deductions -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_total_fees') }}</span>
                <i data-lucide="percent" class="w-4 h-4 text-[#FF9500]"></i>
            </div>
            <div class="text-[20px] sm:text-[24px] font-extrabold text-[#FF9500] tracking-tight">
                -Rp {{ number_format($summary['total_marketplace_fees'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Komisi: Rp {{ number_format($summary['commission_fees'], 0, ',', '.') }}</p>
        </div>

        <!-- Metric 3: Net Settlement -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_net_settlement') }}</span>
                <i data-lucide="badge-check" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="text-[20px] sm:text-[24px] font-extrabold text-[#34C759] tracking-tight">
                Rp {{ number_format($summary['net_settlement'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Pendapatan bersih hak toko</p>
        </div>

        <!-- Metric 4: Escrow Holding -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_escrow_holding') }}</span>
                <i data-lucide="shield-alert" class="w-4 h-4 text-[#5856D6]"></i>
            </div>
            <div class="text-[20px] sm:text-[24px] font-extrabold text-[#5856D6] tracking-tight">
                Rp {{ number_format($summary['escrow_holding'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Tertahan di saldo marketplace</p>
        </div>

        <!-- Metric 5: Disbursed to Bank -->
        <div class="col-span-2 lg:col-span-1 rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_disbursed_bank') }}</span>
                <i data-lucide="landmark" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="text-[20px] sm:text-[24px] font-extrabold text-black dark:text-white tracking-tight">
                Rp {{ number_format($summary['disbursed_amount'], 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-black/50 dark:text-white/50">
                <span>Diff: Rp {{ number_format($summary['reconciliation_diff'], 0, ',', '.') }}</span>
                @if($summary['reconciliation_diff'] == 0)
                    <span class="text-[#34C759] font-bold">✓ Klir</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. PER-CHANNEL FINANCIAL BREAKDOWN CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach(['shopee' => ['name' => 'Shopee', 'color' => '#EE4D2D', 'icon' => 'shopping-bag'], 'tiktok_shop' => ['name' => 'TikTok Shop', 'color' => '#000000', 'icon' => 'video'], 'tokopedia' => ['name' => 'Tokopedia', 'color' => '#03AC0E', 'icon' => 'store']] as $chanKey => $chanMeta)
            @php $chanStat = $breakdown[$chanKey] ?? null; @endphp
            <div class="rounded-[16px] p-4 bg-white/70 dark:bg-[#1C1C1E]/70 border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white" style="background-color: {{ $chanMeta['color'] }}">
                        <i data-lucide="{{ $chanMeta['icon'] }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-[13px] font-bold text-black dark:text-white">{{ $chanMeta['name'] }}</div>
                        <div class="text-[11px] text-black/40 dark:text-white/40">{{ $chanStat['total_orders'] ?? 0 }} pesanan</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[14px] font-extrabold text-[#34C759]">Rp {{ number_format($chanStat['net_settlement'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-black/50 dark:text-white/50">Potongan: Rp {{ number_format($chanStat['total_marketplace_fees'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 4. FILTER TOOLBAR --}}
    <form method="GET" action="{{ route('marketplace-hub.finances') }}" class="rounded-[18px] p-4 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 flex flex-wrap items-center gap-3">
        <!-- Channel Filter -->
        <select name="channel" onchange="this.form.submit()" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3 rounded-[10px] text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
            <option value="">{{ __('marketplace.filter_all_channels') }}</option>
            <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee</option>
            <option value="tiktok_shop" {{ request('channel') === 'tiktok_shop' ? 'selected' : '' }}>TikTok Shop</option>
            <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
        </select>

        <!-- Account Filter -->
        <select name="account_id" onchange="this.form.submit()" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3 rounded-[10px] text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
            <option value="">{{ __('marketplace.filter_all_accounts') }}</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ request('account_id') === $acc->id ? 'selected' : '' }}>
                    {{ $acc->getChannelLabel() }} — {{ $acc->shop_name }}
                </option>
            @endforeach
        </select>

        <!-- Settlement Status Filter -->
        <select name="settlement_status" onchange="this.form.submit()" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3 rounded-[10px] text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
            <option value="">{{ __('marketplace.filter_all_statuses') }}</option>
            <option value="pending" {{ request('settlement_status') === 'pending' ? 'selected' : '' }}>{{ __('marketplace.status_pending') }}</option>
            <option value="escrow_holding" {{ request('settlement_status') === 'escrow_holding' ? 'selected' : '' }}>{{ __('marketplace.status_escrow_holding') }}</option>
            <option value="settled" {{ request('settlement_status') === 'settled' ? 'selected' : '' }}>{{ __('marketplace.status_settled') }}</option>
            <option value="payout_completed" {{ request('settlement_status') === 'payout_completed' ? 'selected' : '' }}>{{ __('marketplace.status_payout_completed') }}</option>
            <option value="refunded" {{ request('settlement_status') === 'refunded' ? 'selected' : '' }}>{{ __('marketplace.status_refunded') }}</option>
        </select>

        <!-- Date Range -->
        <div class="flex items-center gap-1.5 ml-auto">
            <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" class="h-9 px-2.5 rounded-[10px] text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white">
            <span class="text-black/30 dark:text-white/30 text-xs">s/d</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" class="h-9 px-2.5 rounded-[10px] text-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white">
            @if(request()->hasAny(['channel', 'account_id', 'settlement_status', 'start_date', 'end_date']))
                <a href="{{ route('marketplace-hub.finances') }}" class="h-9 px-2.5 rounded-[10px] text-[12px] text-red-500 hover:bg-red-500/10 flex items-center justify-center">Reset</a>
            @endif
        </div>
    </form>

    {{-- 5. TAB SWITCHER (ORDERS VS SETTLEMENT BATCHES) --}}
    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-2">
        <button @click="activeTab = 'orders'"
            :class="activeTab === 'orders' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5'"
            class="px-4 py-2 rounded-[10px] text-[13px] font-semibold transition-all flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            <span>{{ __('marketplace.tab_orders_financial') }}</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/10 dark:bg-white/20">{{ $orders->total() }}</span>
        </button>

        <button @click="activeTab = 'settlements'"
            :class="activeTab === 'settlements' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5'"
            class="px-4 py-2 rounded-[10px] text-[13px] font-semibold transition-all flex items-center gap-2">
            <i data-lucide="landmark" class="w-4 h-4"></i>
            <span>{{ __('marketplace.tab_settlement_batches') }}</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/10 dark:bg-white/20">{{ $settlements->total() }}</span>
        </button>
    </div>

    {{-- TAB 1: ORDER FINANCIAL EXPLORER --}}
    <div x-show="activeTab === 'orders'" class="space-y-4">
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">{{ __('marketplace.col_order_ref') }}</th>
                            <th class="py-3 px-4">{{ __('marketplace.col_channel_account') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('marketplace.col_gross_amount') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('marketplace.col_marketplace_fees') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('marketplace.col_net_settlement') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('marketplace.col_settlement_status') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('marketplace.col_journal_ref') }}</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        @forelse($orders as $order)
                            <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-black dark:text-white">
                                    <div class="font-mono text-[12px]">{{ $order->external_order_sn ?: $order->external_order_id }}</div>
                                    <div class="text-[11px] text-black/40 dark:text-white/40">{{ $order->buyer_name }} • {{ $order->placed_at ? $order->placed_at->format('d M Y H:i') : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                        style="background-color: {{ $order->channel === 'shopee' ? 'rgba(238, 77, 45, 0.1)' : ($order->channel === 'tiktok_shop' ? 'rgba(0, 0, 0, 0.08)' : 'rgba(3, 172, 14, 0.1)') }}; color: {{ $order->channel === 'shopee' ? '#EE4D2D' : ($order->channel === 'tiktok_shop' ? '#111' : '#03AC0E') }}">
                                        <span>{{ $order->getChannelLabel() }}</span>
                                    </div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[140px]">{{ $order->account?->shop_name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-black dark:text-white">
                                    Rp {{ number_format($order->gross_amount > 0 ? $order->gross_amount : $order->total_amount, 0, ',', '.') }}
                                    @if($order->seller_discount > 0)
                                        <div class="text-[10px] text-red-500 font-normal">Disc: -Rp {{ number_format($order->seller_discount, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right text-red-600 dark:text-red-400 font-medium">
                                    -Rp {{ number_format($order->getTotalFees(), 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-[#34C759]">
                                    Rp {{ number_format($order->getCalculatedNetSettlement(), 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        @if($order->settlement_status === 'settled' || $order->settlement_status === 'payout_completed') bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]
                                        @elseif($order->settlement_status === 'escrow_holding') bg-[#5856D6]/10 text-[#5856D6]
                                        @elseif($order->settlement_status === 'refunded') bg-red-500/10 text-red-600
                                        @else bg-black/5 text-black/60 dark:text-white/60 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->settlement_status)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-[11px]">
                                    @if($order->journalEntry)
                                        <span class="text-[#007AFF] font-medium" title="Jurnal Penjualan: {{ $order->journalEntry->entry_number }}">
                                            {{ $order->journalEntry->entry_number }}
                                        </span>
                                    @elseif($order->settlementJournalEntry)
                                        <span class="text-[#34C759] font-medium" title="Jurnal Escrow: {{ $order->settlementJournalEntry->entry_number }}">
                                            {{ $order->settlementJournalEntry->entry_number }}
                                        </span>
                                    @else
                                        <span class="text-black/30 dark:text-white/30">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button @click="openDetail({{ json_encode($order) }})"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black dark:text-white transition-all">
                                        Rincian
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Tidak ada data transaksi marketplace untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/10">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 2: SETTLEMENT BATCH EXPLORER --}}
    <div x-show="activeTab === 'settlements'" class="space-y-4">
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">{{ __('marketplace.col_settlement_sn') }}</th>
                            <th class="py-3 px-4">{{ __('marketplace.col_channel_account') }}</th>
                            <th class="py-3 px-4">{{ __('marketplace.col_payout_date') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('marketplace.col_payout_amount') }}</th>
                            <th class="py-3 px-4">{{ __('marketplace.col_destination_bank') }}</th>
                            <th class="py-3 px-4 text-center">Status Rekonsiliasi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        @forelse($settlements as $settle)
                            <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                <td class="py-3.5 px-4 font-mono font-semibold text-black dark:text-white">
                                    {{ $settle->settlement_sn }}
                                    <div class="text-[11px] font-normal text-black/40 dark:text-white/40">{{ $settle->total_orders_count }} pesanan tercakup</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-black dark:text-white">{{ $settle->getChannelLabel() }}</span>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ $settle->account?->shop_name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-black/70 dark:text-white/70">
                                    {{ $settle->payout_date ? $settle->payout_date->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-[#34C759]">
                                    Rp {{ number_format($settle->net_payout_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($settle->cashAccount)
                                        <div class="font-medium text-black dark:text-white">{{ $settle->cashAccount->name }}</div>
                                    @else
                                        <div class="text-black/60 dark:text-white/60">{{ $settle->bank_name ? $settle->bank_name . ' (' . $settle->bank_account_number . ')' : 'Bank Utama Toko' }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($settle->journalEntry)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Direkonsiliasi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">
                                            <span>Belum Terhubung</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if(! $settle->journalEntry)
                                        <button @click="openReconcile({{ json_encode($settle) }})"
                                            class="h-8 px-3 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-xs transition-all">
                                            {{ __('marketplace.action_reconcile') }}
                                        </button>
                                    @else
                                        <span class="font-mono text-[11px] text-[#007AFF]">{{ $settle->journalEntry->entry_number }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                                    Belum ada data pencairan batch settlement. Klik "Tarik Data Settlement & Escrow" di atas.
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
    </div>

    {{-- MODAL 1: ORDER FINANCIAL DETAIL RECEIPT --}}
    <div x-show="detailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;" x-cloak>
        <div @click.away="detailModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-[24px] max-w-lg w-full p-6 shadow-2xl border border-black/10 dark:border-white/15 space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-[#007AFF]"></i>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Rincian Perhitungan Keuangan</h3>
                </div>
                <button @click="detailModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">✕</button>
            </div>

            <template x-if="selectedOrder">
                <div class="space-y-3 text-[13px]">
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] space-y-1">
                        <div class="flex justify-between">
                            <span class="text-black/50 dark:text-white/50">No. Pesanan:</span>
                            <span class="font-mono font-bold text-black dark:text-white" x-text="selectedOrder.external_order_sn || selectedOrder.external_order_id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black/50 dark:text-white/50">Platform:</span>
                            <span class="font-semibold text-black dark:text-white" x-text="selectedOrder.channel"></span>
                        </div>
                    </div>

                    {{-- Formula Calculation Breakdown --}}
                    <div class="space-y-2 border-t border-black/5 dark:border-white/10 pt-2">
                        <div class="flex justify-between">
                            <span class="text-black/70 dark:text-white/70">Nilai Pesanan Kotor (Gross):</span>
                            <span class="font-bold text-black dark:text-white" x-text="'Rp ' + Number(selectedOrder.gross_amount || selectedOrder.total_amount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-red-500" x-show="selectedOrder.seller_discount > 0">
                            <span>Diskon Ditanggung Toko:</span>
                            <span x-text="'-Rp ' + Number(selectedOrder.seller_discount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-black/50 dark:text-white/50" x-show="selectedOrder.platform_discount > 0">
                            <span>Subsidi Diskon Platform:</span>
                            <span x-text="'Rp ' + Number(selectedOrder.platform_discount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    {{-- Platform Fees --}}
                    <div class="space-y-1.5 border-t border-black/5 dark:border-white/10 pt-2 text-red-600 dark:text-red-400">
                        <div class="flex justify-between text-[12px]">
                            <span>Biaya Komisi Platform:</span>
                            <span x-text="'-Rp ' + Number(selectedOrder.commission_fee || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-[12px]">
                            <span>Biaya Layanan:</span>
                            <span x-text="'-Rp ' + Number(selectedOrder.service_fee || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-[12px]">
                            <span>Biaya Transaksi / MDR:</span>
                            <span x-text="'-Rp ' + Number(selectedOrder.transaction_fee || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-[12px]" x-show="selectedOrder.shipping_fee_seller > 0">
                            <span>Selisih Ongkir Penjual:</span>
                            <span x-text="'-Rp ' + Number(selectedOrder.shipping_fee_seller).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    {{-- Net Settlement Bottom Line --}}
                    <div class="p-3 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 flex justify-between items-center text-[15px] font-extrabold text-[#248A3D] dark:text-[#30D158]">
                        <span>Net Settlement Hak Toko:</span>
                        <span x-text="'Rp ' + Number(selectedOrder.net_settlement_amount || selectedOrder.total_amount).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </template>

            <div class="pt-2">
                <button @click="detailModal = false" class="w-full h-10 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white font-semibold text-[13px]">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL 2: BANK RECONCILIATION MODAL --}}
    <div x-show="reconcileModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;" x-cloak>
        <div @click.away="reconcileModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-[24px] max-w-md w-full p-6 shadow-2xl border border-black/10 dark:border-white/15 space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="landmark" class="w-5 h-5 text-[#007AFF]"></i>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Rekonsiliasi Bank</h3>
                </div>
                <button @click="reconcileModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">✕</button>
            </div>

            <template x-if="selectedSettlement">
                <div class="space-y-4 text-[13px]">
                    <p class="text-black/60 dark:text-white/60">
                        Cocokkan pencairan batch settlement <b class="text-black dark:text-white" x-text="selectedSettlement.settlement_sn"></b> sebesar <b class="text-[#34C759]" x-text="'Rp ' + Number(selectedSettlement.net_payout_amount).toLocaleString('id-ID')"></b> ke rekening bank usaha Anda.
                    </p>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Pilih Rekening Bank Tujuan:</label>
                        <select x-model="targetCashAccountId" class="w-full h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border-0 text-black dark:text-white font-medium focus:ring-1 focus:ring-[#007AFF]">
                            @foreach($cashAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->account_number ?? 'Utama' }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Sistem akan otomatis membukukan jurnal debet Kas/Bank dan kredit Piutang Marketplace.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button @click="reconcileModal = false" class="flex-1 h-10 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white font-medium text-[13px]">Batal</button>
                        <button @click="submitReconcile()" :disabled="submitting" class="flex-1 h-10 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-sm flex items-center justify-center gap-1.5">
                            <span x-show="!submitting">Konfirmasi Masuk</span>
                            <span x-show="submitting">Memproses...</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- MODAL 3: SYNC MODAL --}}
    <div x-show="syncModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;" x-cloak>
        <div @click.away="syncModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-[24px] max-w-sm w-full p-6 shadow-2xl border border-black/10 dark:border-white/15 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="refresh-cw" class="w-5 h-5" :class="{'animate-spin': syncing}"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Tarik Data Settlement</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Sinkronkan saldo escrow & payout dari server Shopee / TikTok Shop.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button @click="syncModal = false" class="flex-1 h-10 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white font-medium text-[13px]">Batal</button>
                <button @click="triggerSync()" :disabled="syncing" class="flex-1 h-10 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-sm flex items-center justify-center gap-1.5">
                    <span x-show="!syncing">Mulai Tarik</span>
                    <span x-show="syncing">Menghubungi API...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
