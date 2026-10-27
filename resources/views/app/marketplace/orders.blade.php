@extends('layouts.app', [
    'title' => __('marketplace.orders_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.orders_page_title'),
    'headerSubtitle' => __('marketplace.orders_page_subtitle'),
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    pullModal: false,
    detailModal: false,
    selectedOrder: null,
    submitting: false,
    refreshing: false,
    selectedPullChannel: 'shopee',
    pollTimer: null,
    openDetail(order) {
        this.selectedOrder = order;
        this.detailModal = true;
        this.$nextTick(() => {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });
    },
    copyText(text, msg) {
        if (!text) return;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
            if (window.AppAlert) {
                AppAlert.toast(msg || 'Tersalin ke clipboard!', 'success');
            }
        }
    },
    async pullOrdersAjax() {
        this.submitting = true;
        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const res = await fetch('{{ route('marketplace-hub.orders.pull') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({ channel: this.selectedPullChannel })
            });
            const data = await res.json();
            if (data.success) {
                if (window.AppAlert) {
                    AppAlert.toast(data.message || 'Pesanan berhasil ditarik!', 'success');
                }
                this.pullModal = false;
                await this.refreshOrdersTable();
            } else {
                if (window.AppAlert) {
                    AppAlert.toast(data.message || 'Gagal menarik pesanan.', 'error');
                }
            }
        } catch (e) {
            if (window.AppAlert) {
                AppAlert.toast('Kesalahan jaringan saat menarik pesanan.', 'error');
            }
        } finally {
            this.submitting = false;
        }
    },
    async refreshOrdersTable() {
        if (this.refreshing) return;
        this.refreshing = true;
        try {
            const url = new URL(window.location.href);
            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.querySelector('#marketplace-orders-tbody');
            const curTbody = document.querySelector('#marketplace-orders-tbody');
            if (newTbody && curTbody) {
                curTbody.innerHTML = newTbody.innerHTML;
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            }
            const newCount = doc.querySelector('#marketplace-orders-total-count');
            const curCount = document.querySelector('#marketplace-orders-total-count');
            if (newCount && curCount) {
                curCount.innerHTML = newCount.innerHTML;
            }
        } catch (e) {
            // silent catch
        } finally {
            this.refreshing = false;
        }
    },
    init() {
        this.pollTimer = setInterval(() => this.refreshOrdersTable(), 10000);
    }
}">

    <!-- ========================================== -->
    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.orders_page_title')"
        :subtitle="__('marketplace.orders_module_subtitle')">
        <x-slot:actions>
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]" :class="refreshing ? 'animate-spin' : 'animate-pulse'"></span>
                <span>Live Feed • Auto-sync</span>
            </div>
            <a href="{{ route('marketplace-hub.orders.export', request()->only(['channel', 'status', 'start_date', 'end_date'])) }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                title="{{ __('marketplace.btn_export_excel_title') }}">
                <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ __('marketplace.btn_export_excel') }}</span>
            </a>
            <button type="button" @click="pullModal = true"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span>{{ __('marketplace.btn_pull_latest_orders') }}</span>
            </button>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('marketplace.back_to_hub') }}</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- ===================================================== -->
    <!-- 2. CHANNEL & STATUS FILTER BAR                        -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <form method="GET" action="{{ route('marketplace-hub.orders') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Channel Filter -->
            <select name="channel" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">{{ __('marketplace.all_channels_select') }}</option>
                <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee</option>
                <option value="tiktok_shop" {{ request('channel') === 'tiktok_shop' ? 'selected' : '' }}>TikTok Shop</option>
                <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">{{ __('marketplace.all_order_statuses_select') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('marketplace.status_pending_payment') }}</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('marketplace.status_paid_ready') }}</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>{{ __('marketplace.status_in_transit') }}</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('marketplace.status_completed') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('marketplace.status_cancelled') }}</option>
            </select>

            @if(request()->filled('channel') || request()->filled('status'))
                <a href="{{ route('marketplace-hub.orders') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                    {{ __('marketplace.reset_filter') }}
                </a>
            @endif
        </form>

        <div class="text-[12px] text-black/60 dark:text-white/60">
            <span id="marketplace-orders-total-count">{!! __('marketplace.total_orders_count', ['count' => $orders->total()]) !!}</span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO ORDERS TABLE                                 -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11.5px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">{{ __('marketplace.th_order_sn_channel') }}</th>
                        <th class="py-3.5 px-4">{{ __('marketplace.th_customer') }}</th>
                        <th class="py-3.5 px-4">{{ __('marketplace.th_items_products') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('marketplace.th_transaction_total') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('marketplace.th_order_status') }}</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">{{ __('marketplace.th_order_time') }}</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="marketplace-orders-tbody" class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($orders as $order)
                        @php
                            $recipientAddress = $order->raw_payload['recipient_address']['full_address'] 
                                ?? ($order->raw_payload['recipient_address']['address'] ?? null);
                            if (empty($recipientAddress) && !empty($order->raw_payload['recipient_address']) && is_array($order->raw_payload['recipient_address'])) {
                                $addrParts = array_filter([
                                    $order->raw_payload['recipient_address']['full_address'] ?? null,
                                    $order->raw_payload['recipient_address']['city'] ?? null,
                                    $order->raw_payload['recipient_address']['district'] ?? null,
                                    $order->raw_payload['recipient_address']['state'] ?? null,
                                    $order->raw_payload['recipient_address']['zipcode'] ?? null,
                                ]);
                                $recipientAddress = implode(', ', $addrParts);
                            }
                            $orderItems = is_array($order->items_summary) ? $order->items_summary : json_decode($order->items_summary ?? '[]', true) ?: [];
                            $orderModalData = [
                                'id' => $order->id,
                                'channel' => $order->channel,
                                'channel_label' => $order->getChannelLabel(),
                                'external_order_sn' => $order->external_order_sn ?? $order->external_order_id,
                                'buyer_name' => $order->buyer_name ?? __('marketplace.default_marketplace_buyer'),
                                'buyer_phone' => $order->buyer_phone ?? '-',
                                'shipping_provider' => $order->shipping_provider ?? 'Kurir Marketplace',
                                'tracking_number' => $order->tracking_number ?? '',
                                'recipient_address' => $recipientAddress ?: 'Sesuai pesanan di sistem marketplace',
                                'total_amount' => (float) $order->total_amount,
                                'channel_fee' => (float) $order->channel_fee,
                                'order_status' => strtoupper((string) $order->order_status),
                                'placed_at' => $order->placed_at ? $order->placed_at->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i'),
                                'items' => $orderItems,
                            ];
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <!-- Col 1: Order ID & Channel Badge -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    @if($order->channel === 'shopee')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#EE4D2D]/15 text-[#EE4D2D]">SHOPEE</span>
                                    @elseif($order->channel === 'tiktok_shop')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black text-white dark:bg-white dark:text-black">TIKTOK</span>
                                    @elseif($order->channel === 'tokopedia')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#00AA5B]/15 text-[#00AA5B]">TOKPED</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black/10">{{ strtoupper($order->channel) }}</span>
                                    @endif
                                    <span class="font-mono font-bold text-black dark:text-white text-[13px]">{{ $order->external_order_sn ?? $order->external_order_id }}</span>
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 font-mono">
                                    Ref ID: #{{ substr($order->id, 0, 8) }}
                                </div>
                            </td>

                            <!-- Col 2: Customer -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-black dark:text-white text-[13px]">
                                    {{ $order->buyer_name ?? __('marketplace.default_marketplace_buyer') }}
                                </div>
                                @if($order->buyer_phone)
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-mono">
                                        {{ $order->buyer_phone }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 3: Items Summary -->
                            <td class="py-4 px-4">
                                @php
                                    $items = $orderItems;
                                    $itemCount = is_countable($items) ? count($items) : 0;
                                @endphp
                                <div class="font-medium text-black dark:text-white">
                                    {{ __('marketplace.items_count', ['count' => $itemCount]) }}
                                </div>
                                @if(!empty($items[0]['item_name'] ?? ($items[0]['name'] ?? null)))
                                    <div class="text-[11px] text-black/60 dark:text-white/60 truncate max-w-[200px]">
                                        {{ $items[0]['item_name'] ?? $items[0]['name'] }}
                                        @if($itemCount > 1)
                                            <span class="text-black/40 dark:text-white/40">(+{{ $itemCount - 1 }})</span>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($order->shipping_provider))
                                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="truck" class="w-3 h-3"></i>
                                        <span>{{ $order->shipping_provider }}</span>
                                        @if(!empty($order->tracking_number))
                                            <span class="font-mono">({{ $order->tracking_number }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Col 4: Total Amount -->
                            <td class="py-4 px-4 text-right">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">
                                    Rp {{ number_format((float)$order->total_amount, 0, ',', '.') }}
                                </div>
                                @if($order->channel_fee > 0)
                                    <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                        {{ __('marketplace.fee_label', ['amount' => 'Rp ' . number_format((float)$order->channel_fee, 0, ',', '.')]) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 5: Status Badge -->
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusClass = match(strtolower($order->order_status)) {
                                        'completed', 'delivered' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]',
                                        'shipped', 'in_transit' => 'bg-[#007AFF]/15 text-[#007AFF]',
                                        'paid', 'ready_to_ship' => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]',
                                        'cancelled' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                                        default => 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusClass }}">
                                    {{ strtoupper($order->order_status) }}
                                </span>
                            </td>

                            <!-- Col 6: Time -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="text-[12px] font-medium text-black dark:text-white">
                                    {{ $order->placed_at ? $order->placed_at->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i') }}
                                </div>
                                <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                    {{ ($order->placed_at ?? $order->created_at)->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Col 7: Actions -->
                            <td class="py-4 px-4 text-center">
                                <button type="button" @click="openDetail(@js($orderModalData))"
                                    class="h-8 px-3 rounded-[9px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all inline-flex items-center gap-1.5 cursor-pointer active:scale-95 shadow-2xs"
                                    title="Lihat Rincian Barang & Pengiriman">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="shopping-bag" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">{{ __('marketplace.empty_orders_title') }}</div>
                                <p class="text-[12px] mt-0.5">{{ __('marketplace.empty_orders_desc') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL TARIK PESANAN MANUAL (BENTO HIG)             -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="pullModal" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto flex items-center justify-center p-4"
            @keydown.escape.window="pullModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="pull-orders-modal-title">

            <!-- Backdrop -->
            <div x-show="pullModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="pullModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div x-show="pullModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"
                @click.outside="pullModal = false">
                
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="download-cloud" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="pull-orders-modal-title" class="text-[16px] font-bold text-black dark:text-white">{{ __('marketplace.pull_orders_modal_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('marketplace.pull_orders_modal_sub') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="pullModal = false" class="min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer active:scale-95" aria-label="{{ __('marketplace.close') }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('marketplace-hub.orders.pull') }}" @submit.prevent="pullOrdersAjax()" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">{{ __('marketplace.pull_orders_select_channel') }}</label>
                        <div class="grid grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">
                            <button type="button" @click="selectedPullChannel = 'shopee'"
                                :class="selectedPullChannel === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#EE4D2D]' : 'text-black/60 dark:text-white/60 font-medium'"
                                class="min-h-[44px] py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                <span>Shopee</span>
                            </button>
                            <button type="button" @click="selectedPullChannel = 'tiktok_shop'"
                                :class="selectedPullChannel === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-black dark:text-white' : 'text-black/60 dark:text-white/60 font-medium'"
                                class="min-h-[44px] py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                <span>TikTok Shop</span>
                            </button>
                            <button type="button" @click="selectedPullChannel = 'tokopedia'"
                                :class="selectedPullChannel === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#00AA5B]' : 'text-black/60 dark:text-white/60 font-medium'"
                                class="min-h-[44px] py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                <span>Tokopedia</span>
                            </button>
                        </div>
                        <input type="hidden" name="channel" :value="selectedPullChannel">
                    </div>

                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] text-[12px] text-black/60 dark:text-white/60 flex items-center gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                        <span>{{ __('marketplace.pull_orders_auto_deduct_info') }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="pullModal = false" :disabled="submitting"
                            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer active:scale-95">
                            {{ __('marketplace.cancel') }}
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? '{{ __('marketplace.btn_pulling_orders') }}' : '{{ __('marketplace.btn_start_pull_orders') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ===================================================== -->
    <!-- 5. MODAL DETAIL PESANAN MARKETPLACE (BENTO APPLE HIG) -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="detailModal" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto flex items-center justify-center p-3 sm:p-4"
            @keydown.escape.window="detailModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="order-detail-modal-title">

            <!-- Backdrop -->
            <div x-show="detailModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="detailModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div x-show="detailModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full max-w-2xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-7 space-y-5 shadow-2xl z-10 max-h-[92vh] overflow-y-auto"
                @click.outside="detailModal = false">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-[6px] text-[11px] font-bold"
                                :class="{
                                    'bg-[#EE4D2D]/15 text-[#EE4D2D]': selectedOrder?.channel === 'shopee',
                                    'bg-black text-white dark:bg-white dark:text-black': selectedOrder?.channel === 'tiktok_shop',
                                    'bg-[#00AA5B]/15 text-[#00AA5B]': selectedOrder?.channel === 'tokopedia',
                                }"
                                x-text="selectedOrder?.channel_label || selectedOrder?.channel?.toUpperCase()">
                            </span>
                            <h3 id="order-detail-modal-title" class="text-[17px] font-bold text-black dark:text-white font-mono tracking-tight" x-text="'#' + (selectedOrder?.external_order_sn || '')"></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]" x-text="selectedOrder?.order_status"></span>
                        </div>
                        <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'Diterima pada ' + (selectedOrder?.placed_at || '-')"></p>
                    </div>
                    <button type="button" @click="detailModal = false" class="min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer active:scale-95" aria-label="{{ __('marketplace.close') }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Section: Item Produk Yang Dipesan -->
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-1.5">
                                <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Rincian Produk Dipesan</span>
                            </span>
                            <span class="text-[12px] font-semibold text-black/50 dark:text-white/50" x-text="(selectedOrder?.items?.length || 0) + ' Item'"></span>
                        </div>

                        <div class="space-y-2.5 divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            <template x-for="(item, idx) in (selectedOrder?.items || [])" :key="idx">
                                <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <template x-if="item.image_url">
                                            <img :src="item.image_url" alt="" class="w-10 h-10 rounded-lg object-cover bg-black/5 shrink-0 border border-black/5">
                                        </template>
                                        <template x-if="!item.image_url">
                                            <div class="w-10 h-10 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <div class="font-bold text-[13px] text-black dark:text-white truncate" x-text="item.item_name || item.name || 'Produk Marketplace'"></div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-black/50 dark:text-white/50">
                                                <span x-show="item.model_name" class="px-1.5 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.08] font-medium" x-text="item.model_name"></span>
                                                <span x-show="item.sku" class="font-mono text-black/40 dark:text-white/40" x-text="'SKU: ' + item.sku"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-[13px] text-black dark:text-white" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(item.subtotal || (item.price * item.quantity) || 0))"></div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45" x-text="(item.quantity || 1) + 'x @ Rp ' + (new Intl.NumberFormat('id-ID').format(item.price || 0))"></div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="!selectedOrder?.items || selectedOrder?.items?.length === 0" class="py-4 text-center text-[12px] text-black/45 dark:text-white/45">
                                Tidak ada rincian item atau belum disinkronkan dari marketplace.
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pengiriman & Logistik (Shipping) -->
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-1.5 pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>Pengiriman & Logistik</span>
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-[12.5px]">
                            <div>
                                <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 block mb-0.5">Ekspedisi / Kurir:</span>
                                <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="truck" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                    <span x-text="selectedOrder?.shipping_provider || 'Kurir Standar Marketplace'"></span>
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 block mb-0.5">Nomor Resi / AWB:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-black dark:text-white" x-text="selectedOrder?.tracking_number || 'Belum Terbit / Dalam Proses'"></span>
                                    <template x-if="selectedOrder?.tracking_number">
                                        <button type="button" @click="copyText(selectedOrder.tracking_number, 'No. Resi disalin!')"
                                            class="px-2 py-0.5 rounded-[6px] text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition cursor-pointer">
                                            Salin
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="sm:col-span-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 block mb-0.5">Penerima & Alamat Tujuan:</span>
                                <div class="font-bold text-black dark:text-white" x-text="selectedOrder?.buyer_name + ' (' + selectedOrder?.buyer_phone + ')'"></div>
                                <p class="text-[12px] text-black/70 dark:text-white/70 mt-1 leading-relaxed bg-white/70 dark:bg-black/30 p-2.5 rounded-[10px] border border-black/5 dark:border-white/5" x-text="selectedOrder?.recipient_address"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Rincian Pembayaran -->
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[12.5px]">
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span>Total Belanja Pembeli:</span>
                            <span class="font-bold text-black dark:text-white" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(selectedOrder?.total_amount || 0))"></span>
                        </div>
                        <template x-if="selectedOrder?.channel_fee > 0">
                            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11.5px]">
                                <span>Potongan Admin / Layanan Channel:</span>
                                <span class="text-[#FF3B30] font-semibold" x-text="'-Rp ' + (new Intl.NumberFormat('id-ID').format(selectedOrder?.channel_fee || 0))"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.08] text-[14px] font-bold text-black dark:text-white">
                            <span>Pendapatan Bersih:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(Math.max(0, (selectedOrder?.total_amount || 0) - (selectedOrder?.channel_fee || 0))))"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="detailModal = false"
                        class="min-h-[42px] px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer active:scale-95">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
