@extends('layouts.app', [
    'title' => __('marketplace.page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.page_title'),
    'headerSubtitle' => __('marketplace.page_subtitle'),
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    disconnectModal: false,
    submitting: false,
    toggling: false,
    selectedChannel: '',
    selectedChannelName: '',
    shopeeActive: {{ ($channels['shopee']['account'] && ($channels['shopee']['account']->is_active ?? true)) ? 'true' : 'false' }},
    tiktokActive: {{ (($channels['tiktok_shop']['account'] ?? $channels['tokopedia']['account'] ?? null) && (($channels['tiktok_shop']['account'] ?? $channels['tokopedia']['account'])->is_active ?? true)) ? 'true' : 'false' }},
    metrics: {
        channels: {{ $accounts->where('status', 'connected')->count() }},
        mappings: {{ (int) $totalMappings }},
        orders: {{ (int) $totalOrders }},
        errors: {{ (int) $totalErrors }}
    },
    pollTimer: null,
    openDisconnect(channel, name) {
        this.selectedChannel = channel;
        this.selectedChannelName = name;
        this.submitting = false;
        this.disconnectModal = true;
    },
    async toggleChannel(channel) {
        if (this.toggling) return;
        this.toggling = true;
        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const res = await fetch(`/marketplace-hub/toggle/${channel}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                }
            });
            const data = await res.json();
            if (data.success) {
                if (channel === 'shopee') this.shopeeActive = Boolean(data.is_active);
                if (channel === 'tiktok_shop' || channel === 'tokopedia') this.tiktokActive = Boolean(data.is_active);
                if (window.AppAlert) {
                    AppAlert.toast(data.message, 'success');
                }
            } else {
                if (window.AppAlert) {
                    AppAlert.toast(data.message || 'Gagal mengubah status', 'error');
                }
            }
        } catch (e) {
            if (window.AppAlert) {
                AppAlert.toast('Kesalahan jaringan saat mengubah status', 'error');
            }
        } finally {
            this.toggling = false;
        }
    },
    async pollMetrics() {
        try {
            const res = await fetch('{{ route('marketplace-hub.index') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success && data.metrics) {
                this.metrics.channels = data.metrics.connected_channels;
                this.metrics.mappings = data.metrics.total_mappings;
                this.metrics.orders   = data.metrics.total_orders;
                this.metrics.errors   = data.metrics.total_errors;
                if (data.channels?.shopee) {
                    this.shopeeActive = data.channels.shopee.is_active;
                }
                if (data.channels?.tiktok_shop) {
                    this.tiktokActive = data.channels.tiktok_shop.is_active;
                }
            }
        } catch (e) {
            // silent background poll
        }
    },
    init() {
        this.pollTimer = setInterval(() => this.pollMetrics(), 10000);
    }
}">

    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.hub_title')"
        :subtitle="__('marketplace.hub_subtitle')">
        <x-slot:actions>
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                <span>Auto-sync Aktif</span>
            </div>
            <a href="{{ route('marketplace-hub.products') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span>{{ __('marketplace.action_set_pricing_stock') }}</span>
            </a>
            <a href="{{ route('marketplace-hub.orders') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>{{ __('marketplace.action_incoming_orders') }}</span>
            </a>
            <a href="{{ route('marketplace-hub.logs') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] flex items-center justify-center gap-1.5"
                title="{{ __('marketplace.action_sync_logs') }}">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Log</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- ===================================================== -->
    <!-- 2. BENTO STATS SUMMARY (4 METRICS)                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Connected Channels -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_connected_channels') }}</span>
                <i data-lucide="plug-2" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                <span x-text="metrics.channels">{{ $accounts->where('status', 'connected')->count() }}</span> <span class="text-[14px] font-medium text-black/40 dark:text-white/40">{{ __('marketplace.metric_channels_unit') }}</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('marketplace.metric_channels_sub') }}</p>
        </div>

        <!-- Metric 2: Mapped Products -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_mapped_products') }}</span>
                <i data-lucide="layers" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                <span x-text="Number(metrics.mappings).toLocaleString('id-ID')">{{ number_format($totalMappings) }}</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('marketplace.metric_mapped_sub') }}</p>
        </div>

        <!-- Metric 3: Synced Orders -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_marketplace_orders') }}</span>
                <i data-lucide="package-check" class="w-4 h-4 text-[#FF9500]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                <span x-text="Number(metrics.orders).toLocaleString('id-ID')">{{ number_format($totalOrders) }}</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('marketplace.metric_orders_sub') }}</p>
        </div>

        <!-- Metric 4: Sync Error Status -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_sync_status') }}</span>
                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold tracking-tight" :class="metrics.errors > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]'">
                <span x-text="metrics.errors > 0 ? metrics.errors + ' Error' : '{{ __('marketplace.metric_sync_normal') }}'">{{ $totalErrors > 0 ? __('marketplace.metric_sync_errors_count', ['count' => $totalErrors]) : __('marketplace.metric_sync_normal') }}</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">
                <span x-text="metrics.errors > 0 ? '{{ __('marketplace.metric_sync_err_sub') }}' : '{{ __('marketplace.metric_sync_ok_sub') }}'">{{ $totalErrors > 0 ? __('marketplace.metric_sync_err_sub') : __('marketplace.metric_sync_ok_sub') }}</span>
            </p>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO CHANNEL CARDS (SHOPEE & TIKTOK/TOKOPEDIA)     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- =================== CHANNEL 1: SHOPEE =================== -->
        @php
            $shopeeAccounts = $accounts->where('channel', 'shopee')->where('status', 'connected');
            $shopeeConnected = $shopeeAccounts->isNotEmpty();
        @endphp
        <div class="col-span-1 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-[13px] bg-[#EE4D2D] text-white flex items-center justify-center shrink-0 shadow-sm font-black text-sm tracking-tight">
                        SP
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[15px] font-bold text-black dark:text-white">1. Shopee Indonesia</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EE4D2D]/15 text-[#EE4D2D] border border-[#EE4D2D]/20">
                                {{ __('marketplace.shopee_erp_badge') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                API V2 Verified
                            </span>
                        </div>
                        <p class="text-[12px] text-black/50 dark:text-white/50 truncate">{{ __('marketplace.channel_shopee_sub') }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $shopeeConnected ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $shopeeConnected ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                    <span>{{ $shopeeConnected ? 'Connected' : 'Not Connected' }}</span>
                </span>
            </div>

            {{-- Akun Terkoneksi Shopee (ACTIVE CONNECTED ASSETS) --}}
            <div class="space-y-2.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">ACTIVE CONNECTED ASSETS:</span>
                @if($shopeeConnected)
                    <div class="space-y-2">
                        @foreach($shopeeAccounts as $acc)
                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#EE4D2D]/12 text-[#EE4D2D] flex items-center justify-center shrink-0 font-bold text-xs">
                                        SP
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->shop_name ?? ('Shopee Store #' . $acc->shop_id) }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5 flex-wrap">
                                            <span class="capitalize font-semibold text-[#EE4D2D]">Shopee</span>
                                            <span>•</span>
                                            <span class="font-mono text-[#007AFF]">ID: {{ $acc->shop_id }}</span>
                                            <span>•</span>
                                            <span class="text-[10px] text-[#34C759] font-medium flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                Auto-Refresh Active
                                            </span>
                                            @if($acc->last_synced_at)
                                                <span>•</span>
                                                <span>Synced {{ $acc->last_synced_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="openDisconnect('{{ $acc->channel }}', '{{ $acc->shop_name ?? ('Shopee Store #' . $acc->shop_id) }}')"
                                    class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                    Disconnect
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun Shopee yang terhubung.</p>
                @endif
            </div>

            @if($shopeeConnected)
                {{-- Quick Nav Actions when Connected --}}
                <div class="flex items-center gap-2 flex-wrap pt-1">
                    <a href="{{ route('marketplace-hub.products', ['channel' => 'shopee']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        <span>Atur Produk & Stok</span>
                    </a>
                    <a href="{{ route('marketplace-hub.orders', ['channel' => 'shopee']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-black/75 dark:text-white/75 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#EE4D2D]"></i>
                        <span>Pesanan Masuk</span>
                    </a>
                    <a href="{{ route('marketplace-hub.chat', ['channel' => 'shopee']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-black/75 dark:text-white/75 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 text-[#EE4D2D]"></i>
                        <span>Live Chat CS</span>
                    </a>
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-[11px] text-black/50 dark:text-white/50 hidden sm:inline">Sync Otomatis:</span>
                        <button type="button" @click="toggleChannel('shopee')" :disabled="toggling"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="shopeeActive ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                            <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="shopeeActive ? 'translate-x-4' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>
            @else
                {{-- Module Capabilities Box when Disconnected --}}
                <div class="p-4 rounded-[16px] bg-[#EE4D2D]/5 dark:bg-[#EE4D2D]/10 border border-[#EE4D2D]/15 space-y-3">
                    <p class="text-[12.5px] text-black/75 dark:text-white/75 leading-relaxed">
                        Hubungkan akun <strong>Shopee Open Platform</strong> Anda untuk mengelola seluruh operasional toko online langsung dari COOCA ERP. Sistem mendukung 9 modul resmi: Manajemen Toko, Katalog & Multi-Harga, Media Library, Diskon & Promosi, Sinkronisasi Pesanan, Logistik & Cetak Resi, Kategori Toko, Retur/Refund, dan Customer Service Live Chat.
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px] text-black/60 dark:text-white/60">
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_shop') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_product') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_media') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_marketing') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_order') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_logistics') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_category') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_returns') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.shopee_mod_chat') }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Compact Action Button (Footer Row) --}}
            <div class="pt-2 flex items-center justify-between gap-3 flex-wrap">
                <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">
                    {{ $shopeeConnected ? 'Otorisasikan toko resmi Shopee untuk mempublikasikan produk dan sinkronisasi pesanan langsung dari COOCA.' : 'Hubungkan akun Shopee Seller untuk otomatisasi sinkronisasi harga, stok, dan pesanan terpadu.' }}
                </p>
                @if($shopeeConnected)
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'shopee') }}" class="ml-auto">
                        @csrf
                        <button type="submit"
                            class="min-h-[38px] px-4 rounded-[11px] bg-black dark:bg-white text-white dark:text-black hover:opacity-90 font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Update Shopee Account</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'shopee') }}" class="w-full sm:w-auto ml-auto">
                        @csrf
                        <button type="submit"
                            class="min-h-[38px] px-5 rounded-[11px] bg-[#EE4D2D] hover:bg-[#E03A1A] text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all cursor-pointer w-full sm:w-auto">
                            <i data-lucide="plug" class="w-3.5 h-3.5"></i>
                            <span>Connect Shopee</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- =================== CHANNEL 2: TIKTOK SHOP + TOKOPEDIA =================== -->
        @php
            $tiktokAccounts = $accounts->whereIn('channel', ['tiktok_shop', 'tokopedia'])->where('status', 'connected');
            $tiktokConnected = $tiktokAccounts->isNotEmpty();
        @endphp
        <div class="col-span-1 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-[13px] bg-gradient-to-br from-black via-[#1C1C1E] to-[#00AA5B] text-white flex items-center justify-center shrink-0 shadow-sm font-black text-xs tracking-tight">
                        TT+TP
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[15px] font-bold text-black dark:text-white">2. {{ __('marketplace.channel_tiktok_tokped_title') }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/20">
                                {{ __('marketplace.channel_unified_badge') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                {{ __('marketplace.channel_sdk_verified') }}
                            </span>
                        </div>
                        <p class="text-[12px] text-black/50 dark:text-white/50 truncate">{{ __('marketplace.channel_tiktok_tokped_sub') }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $tiktokConnected ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $tiktokConnected ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                    <span>{{ $tiktokConnected ? 'Connected' : 'Not Connected' }}</span>
                </span>
            </div>

            {{-- Akun Terkoneksi TikTok Shop + Tokopedia (ACTIVE CONNECTED ASSETS) --}}
            <div class="space-y-2.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">ACTIVE CONNECTED ASSETS:</span>
                @if($tiktokConnected)
                    <div class="space-y-2">
                        @foreach($tiktokAccounts as $acc)
                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] {{ $acc->channel === 'tokopedia' ? 'bg-[#00AA5B]/15 text-[#00AA5B]' : 'bg-black/10 dark:bg-white/10 text-black dark:text-white' }} flex items-center justify-center shrink-0 font-bold text-xs">
                                        {{ $acc->channel === 'tokopedia' ? 'TP' : 'TT' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->shop_name ?? ($acc->channel === 'tokopedia' ? 'Tokopedia Store' : 'TikTok Shop') }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5 flex-wrap">
                                            <span class="capitalize font-semibold {{ $acc->channel === 'tokopedia' ? 'text-[#00AA5B]' : 'text-black dark:text-white' }}">
                                                {{ $acc->channel === 'tokopedia' ? 'Tokopedia' : 'TikTok Shop' }}
                                            </span>
                                            <span>•</span>
                                            <span class="font-mono text-[#007AFF]">ID: {{ $acc->shop_id }}</span>
                                            <span>•</span>
                                            <span class="text-[10px] text-[#34C759] font-medium flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                Auto-Refresh Active
                                            </span>
                                            @if($acc->last_synced_at)
                                                <span>•</span>
                                                <span>Synced {{ $acc->last_synced_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="openDisconnect('{{ $acc->channel }}', '{{ $acc->shop_name ?? ($acc->channel === 'tokopedia' ? 'Tokopedia Store' : 'TikTok Shop') }}')"
                                    class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                    Disconnect
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun TikTok Shop atau Tokopedia yang terhubung.</p>
                @endif
            </div>

            @if($tiktokConnected)
                {{-- Quick Nav Actions when Connected --}}
                <div class="flex items-center gap-2 flex-wrap pt-1">
                    <a href="{{ route('marketplace-hub.products', ['channel' => 'tiktok_shop']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        <span>Produk TikTok</span>
                    </a>
                    <a href="{{ route('marketplace-hub.products', ['channel' => 'tokopedia']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-[#00AA5B] bg-[#00AA5B]/10 hover:bg-[#00AA5B]/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        <span>Produk Tokopedia</span>
                    </a>
                    <a href="{{ route('marketplace-hub.orders', ['channel' => 'tiktok_shop']) }}"
                        class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-semibold text-black/75 dark:text-white/75 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#00AA5B]"></i>
                        <span>Pesanan Masuk</span>
                    </a>
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-[11px] text-black/50 dark:text-white/50 hidden sm:inline">Sync Otomatis:</span>
                        <button type="button" @click="toggleChannel('tiktok_shop')" :disabled="toggling"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="tiktokActive ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                            <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="tiktokActive ? 'translate-x-4' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>
            @else
                {{-- Module Capabilities Box when Disconnected --}}
                <div class="p-4 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 space-y-3">
                    <p class="text-[12.5px] text-black/75 dark:text-white/75 leading-relaxed">
                        {!! __('marketplace.connect_unified_desc') !!}
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px] text-black/60 dark:text-white/60">
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_products_stock') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_fulfillment') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_logistics') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_finance') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_promotions') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md truncate">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i> {{ __('marketplace.scope_return_refund') }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Compact Action Button (Footer Row) --}}
            <div class="pt-2 flex items-center justify-between gap-3 flex-wrap">
                <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">
                    {{ $tiktokConnected ? 'Otorisasi TikTok Shop Partner & Tokopedia aktif untuk sinkronisasi inventori dan pesanan multi-toko.' : 'Hubungkan akun TikTok Shop Partner untuk sinkronisasi pesanan TikTok Shop dan Tokopedia sekaligus.' }}
                </p>
                @if($tiktokConnected)
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'tiktok-tokopedia') }}" class="ml-auto">
                        @csrf
                        <button type="submit"
                            class="min-h-[38px] px-4 rounded-[11px] bg-black dark:bg-white text-white dark:text-black hover:opacity-90 font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Update TikTok Account</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'tiktok-tokopedia') }}" class="w-full sm:w-auto ml-auto">
                        @csrf
                        <button type="submit"
                            class="min-h-[38px] px-5 rounded-[11px] bg-gradient-to-r from-black via-[#1C1C1E] to-[#00AA5B] hover:opacity-95 text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all cursor-pointer w-full sm:w-auto">
                            <i data-lucide="plug" class="w-3.5 h-3.5"></i>
                            <span>{{ __('marketplace.btn_connect_unified') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL KONFIRMASI PUTUS KONEKSI (BENTO HIG)         -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="disconnectModal" x-cloak
            class="fixed inset-0 z-[220] overflow-y-auto flex items-center justify-center p-4"
            @keydown.escape.window="disconnectModal = false"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="disconnect-modal-title">

            <!-- Backdrop -->
            <div x-show="disconnectModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="disconnectModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div x-show="disconnectModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl z-10"
                @click.outside="disconnectModal = false">
                
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="unlink" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="disconnect-modal-title" class="text-[16px] font-bold text-black dark:text-white">{{ __('marketplace.disconnect_modal_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'{{ __('marketplace.disconnect_modal_sub', ['channel' => '']) }}' + selectedChannelName"></p>
                        </div>
                    </div>
                    <button type="button" @click="disconnectModal = false" class="min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer active:scale-95" aria-label="{{ __('marketplace.close') }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 space-y-2">
                    <div class="flex items-center gap-2 text-[#FF3B30] text-[12.5px] font-bold">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>{{ __('marketplace.disconnect_impact_title') }}</span>
                    </div>
                    <ul class="text-[12px] text-black/70 dark:text-white/70 space-y-1 list-disc list-inside">
                        <li>{{ __('marketplace.disconnect_impact_1') }}</li>
                        <li>{{ __('marketplace.disconnect_impact_2') }}</li>
                        <li>{{ __('marketplace.disconnect_impact_3') }}</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="disconnectModal = false" :disabled="submitting"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer active:scale-95">
                        {{ __('marketplace.cancel') }}
                    </button>
                    <form :action="'{{ url('marketplace-hub/disconnect') }}/' + selectedChannel" method="POST" @submit="submitting = true">
                        @csrf
                        <button type="submit" :disabled="submitting"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? '{{ __('marketplace.processing') }}' : '{{ __('marketplace.btn_confirm_disconnect') }}'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
