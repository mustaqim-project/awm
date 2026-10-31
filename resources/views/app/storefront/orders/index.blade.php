@extends('layouts.app', [
    'title' => __('storefront.backoffice.orders.page_title'),
    'headerTitle' => __('storefront.backoffice.orders.header_title'),
    'headerSubtitle' => __('storefront.backoffice.orders.header_subtitle'),
])

@section('content')
    <div class="space-y-6 pb-28 sm:pb-32 lg:pb-10 max-w-[1250px] mx-auto px-3 sm:px-6 lg:px-8"
        x-data="{
            selectedOrderIds: [],
            pageOrderIds: @js($orders->pluck('id')),
            toggleSelectAll() {
                if (this.selectedOrderIds.length === this.pageOrderIds.length) {
                    this.selectedOrderIds = [];
                } else {
                    this.selectedOrderIds = [...this.pageOrderIds];
                }
            },
            printMassLabels() {
                if (this.selectedOrderIds.length === 0) {
                    if (window.AppAlert) AppAlert.toast(@json(__('storefront.select_order_first')), 'warning');
                    return;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('storefront.orders.mass_shipping_label') }}';
                form.target = '_blank';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
                form.appendChild(csrf);

                this.selectedOrderIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }">

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
            <div
                class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 text-[13.5px] font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] flex items-center gap-3 text-[13.5px] font-medium">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => __('storefront.backoffice.orders.header_title')])

        {{-- OMNICHANNEL CHANNEL SWITCHER (SATUKAN SEMUA PESANAN DALAM 1 TEMPAT) --}}
        <div class="p-3 sm:p-4 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[13.5px] font-bold text-black dark:text-white leading-tight">{{ __('storefront.hub_pesanan_omnichannel') }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            {{ __('storefront.all_channels') }}
                        </span>
                    </div>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 truncate">
                        {{ __('storefront.omnichannel_control_desc') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 shrink-0">
                {{-- 1. Toko Online COOCA (Aktif) --}}
                <a href="{{ route('storefront.orders.index') }}"
                    class="h-9 px-3.5 rounded-[11px] text-[12.5px] font-bold transition-all flex items-center gap-1.5 shrink-0 bg-[#007AFF] text-white shadow-xs">
                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                    <span>Toko Online COOCA</span>
                    <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10.5px] bg-white/20 font-extrabold">{{ $orders->total() }}</span>
                </a>

                {{-- 2. Semua Marketplace Hub --}}
                <a href="{{ route('marketplace-hub.orders') }}"
                    class="h-9 px-3 rounded-[11px] text-[12.5px] font-semibold transition-all flex items-center gap-1.5 shrink-0 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/75 dark:text-white/75 hover:text-black dark:hover:text-white">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Marketplace Hub</span>
                </a>

                {{-- 3. Shopee --}}
                <a href="{{ route('marketplace-hub.orders', ['channel' => 'shopee']) }}"
                    class="h-9 px-3 rounded-[11px] text-[12px] font-bold transition-all flex items-center gap-1.5 shrink-0 bg-[#EE4D2D]/10 hover:bg-[#EE4D2D]/15 text-[#EE4D2D]">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12z"/></svg>
                    <span>Shopee</span>
                </a>

                {{-- 4. TikTok Shop --}}
                <a href="{{ route('marketplace-hub.orders', ['channel' => 'tiktok_shop']) }}"
                    class="h-9 px-3 rounded-[11px] text-[12px] font-bold transition-all flex items-center gap-1.5 shrink-0 bg-black/5 hover:bg-black/10 dark:bg-white/10 text-black dark:text-white">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.29 0 .58.04.86.12V9.42a6.34 6.34 0 0 0-.86-.06 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 10.79 4.48 6.31 6.31 0 0 0 1.9-4.48V8.9a8.18 8.18 0 0 0 4.76 1.52v-3.4a4.85 4.85 0 0 1-1-.33z"/></svg>
                    <span>TikTok Shop</span>
                </a>

                {{-- 5. Tokopedia --}}
                <a href="{{ route('marketplace-hub.orders', ['channel' => 'tokopedia']) }}"
                    class="h-9 px-3 rounded-[11px] text-[12px] font-bold transition-all flex items-center gap-1.5 shrink-0 bg-[#03AC0E]/10 hover:bg-[#03AC0E]/15 text-[#03AC0E]">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-6h2v6zm4 0h-2v-6h2v6zm-2-8c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
                    <span>Tokopedia</span>
                </a>
            </div>
        </div>

        {{-- BENTO METRIC CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">{{ __('storefront.backoffice.orders.kpi_total_orders') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-[#007AFF] shrink-0">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ $orders->total() }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    {{ __('storefront.backoffice.orders.kpi_total_orders_desc') }}
                </p>
            </div>

            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">{{ __('storefront.backoffice.orders.tab_needs_verification') }}</span>
                    <div
                        class="w-8 h-8 rounded-full {{ $needsVerificationCount > 0 ? 'bg-rose-50 dark:bg-rose-900/30 text-[#FF3B30]' : 'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40' }} flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div
                    class="text-2xl sm:text-[26px] font-bold tracking-tight {{ $needsVerificationCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums">
                    {{ $needsVerificationCount }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    {{ __('storefront.backoffice.orders.kpi_verify_count_desc') }}
                </p>
            </div>

            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">{{ __('storefront.backoffice.orders.kpi_order_mode') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-[#34C759] shrink-0">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-base sm:text-lg font-bold tracking-tight text-black dark:text-white truncate">
                    {{ __('storefront.backoffice.orders.kpi_order_mode_val') }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    {{ __('storefront.backoffice.orders.kpi_order_mode_desc') }}
                </p>
            </div>

            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">{{ __('storefront.backoffice.orders.kpi_current_page') }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-[#5856D6] shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ $orders->count() }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    {{ __('storefront.backoffice.orders.kpi_current_page_desc') }}
                </p>
            </div>
        </div>

        {{-- FILTER TABS & SEARCH BAR --}}
        <div class="space-y-3">
            <div class="w-full overflow-x-auto scrollbar-thin pb-1">
                <div
                    class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[14px] border border-black/[0.04] dark:border-white/[0.06] gap-1">
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'all', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('storefront.backoffice.orders.tab_all') }}
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'needs_verification', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="relative px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'needs_verification' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <span>{{ __('storefront.backoffice.orders.tab_needs_verification') }}</span>
                        @if ($needsVerificationCount > 0)
                            <span
                                class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30] text-white tabular-nums">{{ $needsVerificationCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'unpaid', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'unpaid' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('storefront.backoffice.orders.tab_unpaid') }}
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'processing', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'processing' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('storefront.backoffice.orders.tab_processing') }}
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'completed', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'completed' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('storefront.backoffice.orders.tab_completed') }}
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'cancelled', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'cancelled' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('storefront.backoffice.orders.tab_cancelled') }}
                    </a>
                </div>
            </div>

            {{-- ORDER TYPE FILTER PILLS --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-[12px]">
                <span class="text-black/40 dark:text-white/40 font-medium mr-1 shrink-0">{{ __('storefront.backoffice.orders.type_prefix') }}</span>
                @php
                    $typeTabs = [
                        'all' => __('storefront.backoffice.orders.type_all'),
                        'direct_checkout' => __('storefront.backoffice.orders.type_direct_checkout'),
                        'scheduled_order' => __('storefront.backoffice.orders.type_scheduled_order'),
                        'request_order' => __('storefront.backoffice.orders.type_request_order'),
                        'customer_po' => __('storefront.backoffice.orders.type_customer_po'),
                        'reservation' => __('storefront.backoffice.orders.type_reservation'),
                    ];
                    $currentType = $typeFilter ?? 'all';
                @endphp
                @foreach ($typeTabs as $tKey => $tLabel)
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => $statusTab !== 'all' ? $statusTab : null, 'type' => $tKey !== 'all' ? $tKey : null, 'search' => request('search')])) }}"
                        class="px-2.5 py-1 rounded-full border transition whitespace-nowrap {{ $currentType === $tKey ? 'bg-black dark:bg-white text-white dark:text-black font-semibold border-transparent shadow-2xs' : 'border-black/10 dark:border-white/10 text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20' }}">
                        {{ $tLabel }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('storefront.orders.index') }}" class="relative max-w-md w-full">
                    <input type="hidden" name="tab" value="{{ $statusTab }}">
                    @if (($typeFilter ?? 'all') !== 'all')
                        <input type="hidden" name="type" value="{{ $typeFilter }}">
                    @endif
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="search" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('storefront.backoffice.orders.search_input_placeholder') }}"
                        class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition-all shadow-sm">
                </form>

                <a href="{{ route('storefront.orders.export-dispatch', array_filter(['tab' => $statusTab !== 'all' ? $statusTab : null, 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                    class="h-11 px-4 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-xs transition active:scale-[0.98] shrink-0"
                    title="{{ __('storefront.backoffice.orders.export_manifest_tooltip') }}">
                    <i data-lucide="truck" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('storefront.backoffice.orders.export_manifest_btn') }}</span>
                </a>
            </div>
        </div>

        {{-- MOBILE CARD LIST VIEW (< md) --}}
        <div class="block md:hidden space-y-3">
            @forelse($orders as $order)
                <div
                    class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <input type="checkbox" :value="'{{ $order->id }}'" x-model="selectedOrderIds"
                                class="w-4 h-4 rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF] cursor-pointer shrink-0">
                            <span class="font-mono font-bold text-[14px] text-black dark:text-white">
                                #{{ $order->order_number }}
                            </span>
                            @if ($order->groupOrder)
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 inline-flex items-center gap-1">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    <span>{{ __('storefront.backoffice.orders.badge_group_order') }}</span>
                                </span>
                            @endif
                        </div>
                        @if ($order->status === 'proof_submitted')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                {{ __('storefront.backoffice.orders.status_proof_submitted') }}
                            </span>
                        @elseif($order->status === 'pending_payment')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                {{ __('storefront.backoffice.orders.status_pending_payment') }}
                            </span>
                        @elseif($order->status === 'paid' || $order->status === 'processing')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                {{ __('storefront.backoffice.orders.status_paid_processing') }}
                            </span>
                        @elseif($order->status === 'ready')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                {{ __('storefront.backoffice.orders.status_ready') }}
                            </span>
                        @elseif($order->status === 'completed')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                {{ __('storefront.backoffice.orders.status_completed') }}
                            </span>
                        @elseif($order->status === 'payment_rejected')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                {{ __('storefront.backoffice.orders.status_payment_rejected') }}
                            </span>
                        @else
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                                {{ $order->status }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-[13px]">
                        <div class="min-w-0 flex-1 pr-2">
                            <div class="font-semibold text-black dark:text-white truncate">{{ $order->customer_name }}
                            </div>
                            <div class="text-[12px] text-black/50 dark:text-white/50 truncate tabular-nums">
                                +{{ $order->customer_phone }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <span
                                class="inline-flex items-center gap-1 text-[12px] font-medium text-black/60 dark:text-white/60">
                                <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}"
                                    class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ $order->fulfillment_type === 'pickup' ? __('storefront.backoffice.orders.pickup_label') : __('storefront.backoffice.orders.delivery_label') }}</span>
                            </span>
                        </div>
                    </div>

                    <div
                        class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[13px]">
                        <div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('storefront.backoffice.orders.total_bill_label') }}</div>
                            <div class="text-[16px] font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('storefront.orders.shipping_label', $order) }}" target="_blank"
                                class="h-9 px-3 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF]/10 text-black/70 dark:text-white/70 hover:text-[#007AFF] transition active:scale-[0.98] flex items-center gap-1 text-[12px] font-semibold"
                                title="Cetak Label Resi Thermal">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Label</span>
                            </a>
                            <a href="{{ route('storefront.orders.show', $order) }}"
                                class="h-9 px-3.5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold transition flex items-center gap-1 active:scale-[0.98] shadow-sm">
                                <span>{{ __('storefront.backoffice.orders.btn_view') }}</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="p-8 text-center bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] text-black/40 dark:text-white/40">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="text-[14px] font-medium">{{ __('storefront.backoffice.orders.empty_orders') }}</p>
                </div>
            @endforelse

            @if ($orders->hasPages())
                <div class="pt-2">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- DESKTOP TABLE VIEW (>= md) --}}
        <div
            class="hidden md:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/5 text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="py-3.5 px-3 w-10 text-center">
                                <input type="checkbox" @click="toggleSelectAll()" :checked="selectedOrderIds.length === pageOrderIds.length && pageOrderIds.length > 0"
                                    class="w-4 h-4 rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF] cursor-pointer">
                            </th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_order_num') }}</th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_customer_label') }}</th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_method') }}</th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_items_bill') }}</th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_status_label') }}</th>
                            <th class="py-3.5 px-4 font-semibold">{{ __('storefront.backoffice.orders.col_time') }}</th>
                            <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('storefront.backoffice.orders.col_action_label') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($orders as $order)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                                <td class="py-3.5 px-3 text-center" @click.stop>
                                    <input type="checkbox" :value="'{{ $order->id }}'" x-model="selectedOrderIds"
                                        class="w-4 h-4 rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF] cursor-pointer">
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-black dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('storefront.orders.show', $order) }}"
                                            class="text-[#007AFF] hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        @if ($order->groupOrder)
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 inline-flex items-center gap-1"
                                                title="{{ __('storefront.backoffice.orders.badge_group_order_title', ['title' => $order->groupOrder->title]) }}">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                <span>{{ __('storefront.backoffice.orders.badge_group_order') }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-black dark:text-white leading-tight">
                                        {{ $order->customer_name }}
                                    </div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                                        +{{ $order->customer_phone }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/70 dark:text-white/70">
                                        <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}"
                                            class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ $order->fulfillment_type === 'pickup' ? __('storefront.backoffice.orders.pickup_store') : __('storefront.backoffice.orders.delivery_store') }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-black dark:text-white tabular-nums">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">
                                        {{ __('storefront.backoffice.orders.product_types_count', ['count' => $order->items->count()]) }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($order->status === 'proof_submitted')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                            {{ __('storefront.backoffice.orders.status_proof_submitted') }}
                                        </span>
                                    @elseif($order->status === 'pending_payment')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            {{ __('storefront.backoffice.orders.status_pending_payment') }}
                                        </span>
                                    @elseif($order->status === 'paid' || $order->status === 'processing')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            {{ __('storefront.backoffice.orders.status_paid_processing') }}
                                        </span>
                                    @elseif($order->status === 'ready')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            {{ __('storefront.backoffice.orders.status_ready') }}
                                        </span>
                                    @elseif($order->status === 'completed')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            {{ __('storefront.backoffice.orders.status_completed') }}
                                        </span>
                                    @elseif($order->status === 'payment_rejected')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                            {{ __('storefront.backoffice.orders.status_payment_rejected') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                                            {{ $order->status }}
                                        </span>
                                    @endif
                                </td>
                                <td
                                    class="py-3.5 px-4 text-[12px] text-black/55 dark:text-white/55 whitespace-nowrap tabular-nums">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('storefront.orders.shipping_label', $order) }}" target="_blank"
                                            class="h-8 px-2.5 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF]/10 text-black/70 dark:text-white/70 hover:text-[#007AFF] transition active:scale-[0.98] flex items-center gap-1 shrink-0 cursor-pointer text-[11.5px] font-semibold"
                                            title="Cetak Label Resi Thermal" aria-label="Cetak Label Resi">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span class="hidden xl:inline">Label</span>
                                        </a>
                                        <a href="{{ route('storefront.orders.show', $order) }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 transition active:scale-[0.98] flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('storefront.backoffice.orders.btn_view') }}"
                                            aria-label="{{ __('storefront.backoffice.orders.btn_view') }}">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                    <p class="text-[14px] font-medium">{{ __('storefront.backoffice.orders.empty_orders') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- FLOATING BATCH ACTION BAR (APPLE HIG DOCK PILL) --}}
        <div x-show="selectedOrderIds.length > 0" x-cloak
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="fixed bottom-6 inset-x-0 z-[150] flex justify-center px-4 pointer-events-none">
            <div class="pointer-events-auto bg-black/90 dark:bg-white/95 text-white dark:text-black backdrop-blur-xl px-5 py-3 rounded-[22px] shadow-2xl flex flex-wrap items-center gap-3 border border-white/10 dark:border-black/10">
                <div class="flex items-center gap-2 text-[13px] font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span><strong x-text="selectedOrderIds.length"></strong> {{ __('storefront.orders_selected') }}</span>
                </div>
                <div class="h-4 w-px bg-white/20 dark:bg-black/20"></div>
                <button type="button" @click="printMassLabels()"
                    class="h-9 px-4 rounded-[12px] text-[13px] font-bold bg-[#007AFF] hover:bg-[#0071E3] text-white flex items-center gap-1.5 cursor-pointer active:scale-95 shadow-md transition">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>{{ __('storefront.print_mass_labels') }}</span>
                </button>
                <button type="button" @click="selectedOrderIds = []"
                    class="h-9 px-3 rounded-[12px] text-[12px] font-medium text-white/70 dark:text-black/70 hover:text-white dark:hover:text-black hover:bg-white/10 dark:hover:bg-black/10 transition cursor-pointer">{{ __('common.cancel') }}</button>
            </div>
        </div>

    </div>
@endsection
