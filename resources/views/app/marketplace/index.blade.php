@extends('layouts.app', [
    'title' => __('marketplace.page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.page_title'),
    'headerSubtitle' => __('marketplace.page_subtitle'),
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    disconnectModal: false,
    submitting: false,
    selectedChannel: '',
    selectedChannelName: '',
    openDisconnect(channel, name) {
        this.selectedChannel = channel;
        this.selectedChannelName = name;
        this.submitting = false;
        this.disconnectModal = true;
    }
}">

    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.hub_title')"
        :subtitle="__('marketplace.hub_subtitle')">
        <x-slot:actions>
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
                {{ $accounts->where('status', 'connected')->count() }} <span class="text-[14px] font-medium text-black/40 dark:text-white/40">{{ __('marketplace.metric_channels_unit') }}</span>
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
                {{ number_format($totalMappings) }}
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
                {{ number_format($totalOrders) }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('marketplace.metric_orders_sub') }}</p>
        </div>

        <!-- Metric 4: Sync Error Status -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">{{ __('marketplace.metric_sync_status') }}</span>
                <i data-lucide="{{ $totalErrors > 0 ? 'alert-triangle' : 'check-circle-2' }}" class="w-4 h-4 {{ $totalErrors > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold {{ $totalErrors > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }} tracking-tight">
                {{ $totalErrors > 0 ? __('marketplace.metric_sync_errors_count', ['count' => $totalErrors]) : __('marketplace.metric_sync_normal') }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">
                {{ $totalErrors > 0 ? __('marketplace.metric_sync_err_sub') : __('marketplace.metric_sync_ok_sub') }}
            </p>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO CHANNEL CARDS (SHOPEE, TIKTOK, TOKOPEDIA)     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- =================== CHANNEL 1: SHOPEE =================== -->
        @php
            $shopeeAccount = $channels['shopee']['account'] ?? null;
            $shopeeConnected = $shopeeAccount && $shopeeAccount->status === 'connected';
        @endphp
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 flex flex-col justify-between space-y-5 shadow-xs transition-all hover:shadow-md">
            <div class="space-y-4">
                <!-- Channel Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-[16px] bg-[#EE4D2D]/15 text-[#EE4D2D] flex items-center justify-center font-black text-base shadow-xs">
                            SP
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('marketplace.channel_shopee_title') }}</h2>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.channel_shopee_sub') }}</p>
                        </div>
                    </div>
                    @if($shopeeConnected)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>{{ __('marketplace.status_connected') }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                            <span>{{ __('marketplace.status_disconnected') }}</span>
                        </span>
                    @endif
                </div>

                <!-- Channel Body Info -->
                @if($shopeeConnected)
                    <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">{{ __('marketplace.shop_name') }}</span>
                            <span class="font-bold text-black dark:text-white truncate max-w-[170px]">{{ $shopeeAccount->shop_name ?? 'Shopee Store' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">{{ __('marketplace.shop_id') }}</span>
                            <span class="font-mono text-black/80 dark:text-white/80 text-[11px]">{{ $shopeeAccount->shop_id }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">{{ __('marketplace.last_synced') }}</span>
                            <span class="text-black/70 dark:text-white/70 text-[11.5px]">{{ $shopeeAccount->last_synced_at?->diffForHumans() ?? __('marketplace.never_synced') }}</span>
                        </div>
                    </div>

                    <!-- Active Toggle Form -->
                    <form method="POST" action="{{ route('marketplace-hub.toggle', 'shopee') }}" class="flex items-center justify-between pt-1">
                        @csrf
                        <div class="space-y-0.5">
                            <span class="text-[12.5px] font-bold text-black dark:text-white">{{ __('marketplace.sales_sync_active') }}</span>
                            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('marketplace.sales_sync_desc_shopee') }}</p>
                        </div>
                        <button type="submit"
                            title="{{ ($shopeeAccount->is_active ?? true) ? __('marketplace.click_to_deactivate') : __('marketplace.click_to_activate') }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($shopeeAccount->is_active ?? true) ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($shopeeAccount->is_active ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                @else
                    <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed py-2">
                        {{ __('marketplace.connect_shopee_desc') }}
                    </p>
                @endif
            </div>

            <!-- Actions -->
            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                @if($shopeeConnected)
                    <a href="{{ route('marketplace-hub.products', ['channel' => 'shopee']) }}"
                        class="flex-1 h-9 px-3 rounded-[12px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                        <span>{{ __('marketplace.action_manage_products') }}</span>
                    </a>
                    <button type="button" @click="openDisconnect('shopee', 'Shopee Indonesia')"
                        class="h-9 px-3 rounded-[12px] text-[12px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-all flex items-center justify-center gap-1 cursor-pointer"
                        title="{{ __('marketplace.action_disconnect') }}">
                        <i data-lucide="unlink" class="w-3.5 h-3.5"></i>
                    </button>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'shopee') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#EE4D2D] hover:bg-[#E03A1A] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <i data-lucide="plug" class="w-4 h-4"></i>
                            <span>{{ __('marketplace.btn_connect_shopee') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- =================== CHANNEL 2: TIKTOK SHOP + TOKOPEDIA =================== -->
        @php
            $tiktokAccount = $channels['tiktok_shop']['account'] ?? $channels['tokopedia']['account'] ?? null;
            $tiktokConnected = $tiktokAccount && $tiktokAccount->status === 'connected';
        @endphp
        <div class="col-span-1 md:col-span-2 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 flex flex-col justify-between space-y-5 shadow-xs transition-all hover:shadow-md">
            <div class="space-y-4">
                <!-- Channel Header -->
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-gradient-to-br from-black via-[#1C1C1E] to-[#00AA5B] text-white flex items-center justify-center font-black text-sm shadow-xs tracking-tight">
                            TT+TP
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-[17px] font-bold text-black dark:text-white">{{ __('marketplace.channel_tiktok_tokped_title') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/20">{{ __('marketplace.channel_unified_badge') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                    {{ __('marketplace.channel_sdk_verified') }}
                                </span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('marketplace.channel_tiktok_tokped_sub') }}</p>
                        </div>
                    </div>
                    @if($tiktokConnected)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>{{ __('marketplace.status_connected_active') }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                            <span>{{ __('marketplace.status_disconnected') }}</span>
                        </span>
                    @endif
                </div>

                <!-- Channel Body Info -->
                @if($tiktokConnected)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">{{ __('marketplace.shop_name_official') }}</span>
                            <span class="font-bold text-black dark:text-white text-[13px] truncate block">{{ $tiktokAccount->shop_name ?? 'TikTok Shop + Tokopedia Store' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">{{ __('marketplace.shop_cipher_seller_id') }}</span>
                            <span class="font-mono text-black/80 dark:text-white/80 text-[12px] block">{{ $tiktokAccount->shop_id }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">{{ __('marketplace.last_synced') }}</span>
                            <span class="text-black/80 dark:text-white/80 text-[12px] font-medium block">{{ $tiktokAccount->last_synced_at?->diffForHumans() ?? __('marketplace.never_synced') }}</span>
                        </div>
                    </div>

                    <!-- Achieved Capabilities Matrix (7 Pillars) -->
                    <div class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] font-bold text-black/80 dark:text-white/80 flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                <span>{{ __('marketplace.integrated_capabilities') }}</span>
                            </span>
                            <span class="text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">
                                {{ __('marketplace.active_modules_count') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                <span class="truncate" title="seller.product.basic, write, delete, global">{{ __('marketplace.scope_products_stock') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                <span class="truncate" title="seller.order.info, fulfillment.basic, package.write">{{ __('marketplace.scope_orders_resi') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="truck" class="w-3.5 h-3.5 text-[#FF9500] shrink-0"></i>
                                <span class="truncate" title="seller.logistics">{{ __('marketplace.scope_warehouse_logistics') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="wallet" class="w-3.5 h-3.5 text-[#AF52DE] shrink-0"></i>
                                <span class="truncate" title="seller.finance.info">{{ __('marketplace.scope_finance_settlement') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-[#FF2D55] shrink-0"></i>
                                <span class="truncate" title="seller.promotion.info, write">{{ __('marketplace.scope_promo_discount') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-[#5856D6] shrink-0"></i>
                                <span class="truncate" title="seller.return_refund.basic">{{ __('marketplace.scope_return_refund') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-[#00AA5B] shrink-0"></i>
                                <span class="truncate" title="seller.shop.info, authorization.info">{{ __('marketplace.scope_shop_auth') }}</span>
                            </div>
                            <div class="p-2 rounded-[12px] bg-white/60 dark:bg-[#2C2C2E]/60 border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                <span class="truncate" title="Image Translation & Product Optimization">{{ __('marketplace.scope_product_optimization') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active Toggle Form -->
                    <form method="POST" action="{{ route('marketplace-hub.toggle', 'tiktok_shop') }}" class="flex items-center justify-between pt-1">
                        @csrf
                        <div class="space-y-0.5">
                            <span class="text-[13px] font-bold text-black dark:text-white">{{ __('marketplace.sales_sync_title_unified') }}</span>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.sales_sync_desc_unified') }}</p>
                        </div>
                        <button type="submit"
                            title="{{ ($tiktokAccount->is_active ?? true) ? __('marketplace.click_to_deactivate') : __('marketplace.click_to_activate') }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($tiktokAccount->is_active ?? true) ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($tiktokAccount->is_active ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-[18px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 space-y-3">
                        <p class="text-[13px] text-black/75 dark:text-white/75 leading-relaxed">
                            {!! __('marketplace.connect_unified_desc') !!}
                        </p>
                        <div class="flex items-center gap-2 text-[11px] text-black/60 dark:text-white/60 flex-wrap">
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_products_stock') }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_fulfillment') }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_logistics') }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_finance') }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_promotions') }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-md">
                                <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i> {{ __('marketplace.scope_return_refund') }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                @if($tiktokConnected)
                    <div class="flex items-center gap-2 flex-1 flex-wrap">
                        <a href="{{ route('marketplace-hub.products', ['channel' => 'tiktok_shop']) }}"
                            class="h-10 px-4 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>{{ __('marketplace.action_pricing_stock_tiktok') }}</span>
                        </a>
                        <a href="{{ route('marketplace-hub.products', ['channel' => 'tokopedia']) }}"
                            class="h-10 px-4 rounded-[12px] text-[12.5px] font-bold text-[#00AA5B] bg-[#00AA5B]/10 hover:bg-[#00AA5B]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>{{ __('marketplace.action_pricing_stock_tokopedia') }}</span>
                        </a>
                        <a href="{{ route('marketplace-hub.orders', ['channel' => 'tiktok_shop']) }}"
                            class="h-10 px-3.5 rounded-[12px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                            title="{{ __('marketplace.action_view_orders_title') }}">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                            <span>{{ __('marketplace.action_orders_short') }}</span>
                        </a>
                    </div>
                    <button type="button" @click="openDisconnect('tiktok_shop', 'TikTok Shop + Tokopedia')"
                        class="h-10 px-3.5 rounded-[12px] text-[12px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        title="{{ __('marketplace.action_disconnect') }}">
                        <i data-lucide="unlink" class="w-4 h-4"></i>
                        <span class="text-[12px]">{{ __('marketplace.action_disconnect_short') }}</span>
                    </button>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'tiktok-tokopedia') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full h-11 px-5 rounded-[14px] text-[13.5px] font-bold text-white bg-gradient-to-r from-black via-[#1C1C1E] to-[#00AA5B] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 shadow-md shadow-black/10 cursor-pointer">
                            <i data-lucide="plug" class="w-4 h-4"></i>
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
