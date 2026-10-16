@extends('layouts.app', [
    'title' => __('storefront.backoffice.order_detail.page_title', ['order' => $order->order_number]),
    'headerTitle' => __('storefront.backoffice.order_detail.header_title', ['order' => $order->order_number]),
    'headerSubtitle' => __('storefront.backoffice.order_detail.header_subtitle'),
])

@section('content')
    @php
        $statusStyles = [
            'pending_payment' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/25',
            'proof_submitted' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/25',
            'paid' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/25',
            'processing' => 'bg-[#5856D6]/10 text-[#5856D6] border-[#5856D6]/25',
            'ready' => 'bg-[#32ADE6]/10 text-[#32ADE6] border-[#32ADE6]/25',
            'shipped' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/25',
            'delivered' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/25',
            'fulfilled' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/25',
            'completed' =>
                'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 border-black/10 dark:border-white/15',
            'cancelled' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/25',
            'payment_rejected' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/25',
            'expired' => 'bg-[#8E8E93]/10 text-[#8E8E93] border-[#8E8E93]/25',
        ];

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $waMessage = rawurlencode(
            "Halo Kak {$order->customer_name}, terima kasih telah memesan di {$business->name}! Terkait pesanan #{$order->order_number}...",
        );
    @endphp

    <div class="space-y-6 pb-28 sm:pb-32 lg:pb-12 max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8"
        x-data="{
            rejectModalOpen: false,
            cancelModalOpen: false,
            imageModalOpen: false,
            verifyModalOpen: false,
            waybillModalOpen: false,
            activeImageUrl: ''
        }">

        {{-- FLASH NOTIFICATIONS --}}
        @if (session('success'))
            <div
                class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 text-[13.5px] font-medium shadow-xs">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] flex items-center gap-3 text-[13.5px] font-medium shadow-xs">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- UNIFIED 3-ROW PAGE HEADER (BENTO APPLE HIG STANDARD) --}}
        <div class="space-y-3">
            {{-- Baris 1: Overline Breadcrumbs --}}
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('storefront.orders.index') }}"
                    class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    {{ __('storefront.backoffice.order_detail.nav_storefront_orders') }}
                </a>
                <span class="text-black/20 dark:text-white/20 text-xs">&bull;</span>
                <a href="{{ route('storefront.orders.index') }}"
                    class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                    {{ __('storefront.backoffice.order_detail.nav_order_list') }}
                </a>
                <span class="text-black/20 dark:text-white/20 text-xs">&bull;</span>
                <span
                    class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/60 dark:text-white/60 font-mono">
                    #{{ $order->order_number }}
                </span>
            </div>

            {{-- Baris 2: H1 Title + Badge Status + Action Buttons --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('storefront.orders.index') }}"
                        class="w-10 h-10 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] flex items-center justify-center text-black/70 dark:text-white/70 transition shadow-xs shrink-0 active:scale-95"
                        title="{{ __('storefront.backoffice.order_detail.btn_back_tooltip') }}">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white">
                        {{ __('storefront.backoffice.order_detail.header_title', ['order' => $order->order_number]) }}
                    </h1>
                    <span
                        class="px-3 py-1 rounded-full text-[11.5px] font-bold border uppercase tracking-wider {{ $statusStyles[$order->status] ?? 'bg-black/5 text-black/60' }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if ($order->fulfillment_type !== 'pickup')
                        <a href="{{ route('storefront.orders.shipping_label', $order) }}" target="_blank"
                            class="h-10 px-4 rounded-xl border border-black/[0.08] dark:border-white/[0.1] bg-white dark:bg-[#1C1C1E] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-xs sm:text-sm font-semibold transition active:scale-[0.98] inline-flex items-center gap-2 shadow-xs cursor-pointer">
                            <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('storefront.backoffice.order_detail.btn_print_shipping_label') }}</span>
                        </a>
                    @endif

                    <a href="{{ url("/b/{$business->slug}/order/{$order->tracking_token}") }}" target="_blank"
                        class="h-10 px-4 rounded-xl border border-black/[0.08] dark:border-white/[0.1] bg-white dark:bg-[#1C1C1E] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-xs sm:text-sm font-semibold transition active:scale-[0.98] inline-flex items-center gap-2 shadow-xs cursor-pointer">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>{{ __('storefront.backoffice.order_detail.btn_customer_view') }}</span>
                    </a>

                    @if ($order->customer_phone)
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                            class="h-10 px-4 rounded-xl bg-[#25D366]/15 hover:bg-[#25D366]/25 text-[#128C7E] dark:text-[#25D366] border border-[#25D366]/30 text-xs sm:text-sm font-semibold transition active:scale-[0.98] inline-flex items-center gap-2 shadow-xs cursor-pointer">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>{{ __('storefront.backoffice.order_detail.btn_contact_wa') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Baris 3: Subtitle Ringkas Faktual --}}
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 max-w-4xl leading-relaxed">
                {{ __('storefront.backoffice.order_detail.subtitle_meta', [
                    'date' => $order->created_at->translatedFormat('d F Y, H:i'),
                    'channel' => strtoupper(str_replace('_', ' ', $order->order_type)),
                    'method' => $order->fulfillment_type === 'pickup' ? __('storefront.backoffice.order_detail.method_pickup') : ($order->shipping_courier_name ?: __('storefront.backoffice.order_detail.method_delivery_default'))
                ]) }}
            </p>
        </div>

        {{-- SCHEDULED ORDER ALERT / BADGE --}}
        @if ($order->scheduled_date)
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-[#007AFF]/[0.08] dark:bg-[#007AFF]/[0.12] border border-[#007AFF]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                            {{ __('storefront.backoffice.order_detail.scheduled_order_title', ['date' => $order->scheduled_date->translatedFormat('l, d F Y')]) }}
                        </h3>
                        <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 mt-0.5">
                            {{ __('storefront.backoffice.order_detail.scheduled_slot_label') }} <span
                                class="font-bold text-[#007AFF]">{{ $order->scheduled_time_slot ?? __('storefront.backoffice.order_detail.scheduled_flexible') }}</span>
                        </p>
                    </div>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#007AFF] text-white self-start sm:self-auto">
                    {{ __('storefront.backoffice.order_detail.badge_scheduled') }}
                </span>
            </div>
        @endif

        {{-- CUSTOMER PO / B2B BANNER --}}
        @if ($order->isCustomerPo())
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-[#5856D6]/[0.08] dark:bg-[#5856D6]/[0.12] border border-[#5856D6]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-[#5856D6] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                                {{ __('storefront.backoffice.order_detail.b2b_po_title', ['po' => $order->customer_po_number ?: 'PO Klien']) }}
                            </h3>
                            @if ($order->company_name)
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#5856D6]/15 text-[#5856D6]">
                                    {{ $order->company_name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-[12.5px] text-black/60 dark:text-white/60 mt-0.5">
                            {{ __('storefront.backoffice.order_detail.b2b_po_desc') }}
                        </p>
                    </div>
                </div>
                @if ($order->batches->isNotEmpty())
                    <div class="text-left sm:text-right shrink-0">
                        <span
                            class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('storefront.backoffice.order_detail.b2b_progress_label') }}</span>
                        <span class="text-sm sm:text-base font-bold text-[#5856D6]">
                            {{ __('storefront.backoffice.order_detail.b2b_progress_count', ['delivered' => $order->delivered_batches_count, 'total' => $order->total_batches_count]) }}
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- GROUP ORDER / PESAN BERSAMA BANNER & KITCHEN PACKAGING BREAKDOWN --}}
        @if ($order->groupOrder)
            <div
                class="p-5 sm:p-6 rounded-[20px] bg-[#AF52DE]/[0.08] dark:bg-[#AF52DE]/[0.12] border border-[#AF52DE]/25 space-y-4 shadow-xs">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#AF52DE]/20">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#AF52DE] text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                                    {{ __('storefront.backoffice.order_detail.group_order_title', ['title' => $order->groupOrder->title]) }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#AF52DE]/20 text-[#AF52DE]">
                                    {{ __('storefront.backoffice.orders.badge_group_order') }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-[12.5px] text-black/60 dark:text-white/60 mt-0.5">
                                {{ __('storefront.backoffice.order_detail.group_order_host_label') }} <strong
                                    class="text-black dark:text-white">{{ $order->groupOrder->host?->name ?? $order->customer_name }}</strong>
                                &bull; {{ __('storefront.backoffice.order_detail.group_order_members_count', ['count' => $order->groupOrder->items->groupBy('member_name')->count()]) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <span
                            class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('storefront.backoffice.order_detail.group_order_total_portions') }}</span>
                        <span class="text-base sm:text-lg font-extrabold text-[#AF52DE] tabular-nums">
                            {{ __('storefront.backoffice.order_detail.group_order_portions_count', ['count' => (int) $order->groupOrder->items->sum('quantity')]) }}
                        </span>
                    </div>
                </div>

                {{-- COLLEAGUE MEAL PACKAGING BREAKDOWN FOR KITCHEN --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#AF52DE] flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                            <span>{{ __('storefront.backoffice.order_detail.kitchen_guide_title') }}</span>
                        </span>
                        <span class="text-xs text-black/50 dark:text-white/50">{{ __('storefront.backoffice.order_detail.kitchen_guide_desc') }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($order->groupOrder->items->groupBy('member_name') as $memberName => $memberItems)
                            <div
                                class="p-4 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-2 shadow-xs">
                                <div
                                    class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-[#AF52DE]/15 text-[#AF52DE] font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($memberName, 0, 1)) }}
                                        </div>
                                        <span class="font-bold text-sm text-black dark:text-white">{{ $memberName }}</span>
                                    </div>
                                    <span class="text-xs font-mono text-black/45 dark:text-white/45">
                                        {{ __('storefront.backoffice.order_detail.member_items_count', ['count' => $memberItems->sum('quantity')]) }}
                                    </span>
                                </div>
                                <div class="space-y-1.5 text-xs sm:text-[13px]">
                                    @foreach ($memberItems as $mItem)
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span
                                                    class="font-semibold text-black dark:text-white">{{ (float) $mItem->quantity }}x
                                                    {{ $mItem->product?->name ?? 'Produk' }}</span>
                                                @if ($mItem->notes)
                                                    <p class="text-[11px] text-[#FF9500] font-medium italic">{{ __('storefront.backoffice.order_detail.item_notes_prefix', ['notes' => $mItem->notes]) }}</p>
                                                @endif
                                            </div>
                                            <span class="text-black/60 dark:text-white/60 tabular-nums text-xs">
                                                Rp {{ number_format($mItem->unit_price * $mItem->quantity, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- MAIN BENTO GRID: 2 COLUMNS (8 COLS / 4 COLS) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: ORDER ITEMS, FULFILLMENT & LOGISTICS (8 COLS) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- REQUEST ORDER QUOTATION FORM (When pending_review) --}}
                @if ($order->order_type === 'request_order' && $order->status === 'pending_review')
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border-2 border-[#007AFF]/30 p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div
                                class="w-9 h-9 rounded-xl bg-[#007AFF] text-white flex items-center justify-center shrink-0">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.quotation_title') }}</h2>
                                <p class="text-xs text-black/55 dark:text-white/55">{{ __('storefront.backoffice.order_detail.quotation_subtitle') }}</p>
                            </div>
                        </div>

                        <form action="{{ route('storefront.orders.quote', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-3">
                                @foreach ($order->items as $idx => $it)
                                    <div
                                        class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex-1">
                                            <input type="hidden" name="items[{{ $idx }}][id]"
                                                value="{{ $it->id }}">
                                            <h4 class="text-sm font-bold text-black dark:text-white">
                                                {{ $it->product_name }}</h4>
                                            @if ($it->notes)
                                                <p class="text-xs text-black/50 dark:text-white/50 italic mt-0.5">
                                                    "{{ $it->notes }}"</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2.5 shrink-0">
                                            <div>
                                                <label
                                                    class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1">{{ __('storefront.backoffice.order_detail.quotation_qty') }}</label>
                                                <input type="number" step="0.01" min="0.01"
                                                    name="items[{{ $idx }}][quantity]"
                                                    value="{{ (float) $it->quantity }}"
                                                    class="w-20 h-10 px-2.5 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm text-black dark:text-white font-bold text-center tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1">{{ __('storefront.backoffice.order_detail.quotation_unit_price') }}</label>
                                                <input type="number" step="100" min="0"
                                                    name="items[{{ $idx }}][unit_price]"
                                                    value="{{ (float) $it->unit_price }}" placeholder="0" required
                                                    class="w-36 h-10 px-3 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm text-black dark:text-white font-bold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('storefront.backoffice.order_detail.quotation_shipping_fee') }}</label>
                                    <input type="number" step="100" min="0" name="shipping_cost"
                                        value="{{ (float) $order->shipping_cost }}" placeholder="0"
                                        class="w-full h-10 px-3 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm text-black dark:text-white font-bold tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('storefront.backoffice.order_detail.quotation_payment_method') }}</label>
                                    <div
                                        class="w-full h-10 px-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-xs sm:text-sm font-semibold text-[#007AFF] flex items-center justify-between">
                                        <span class="flex items-center gap-2">
                                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.qris_national_standard') }}</span>
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10.5px] uppercase font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">Otomatis</span>
                                    </div>
                                    <input type="hidden" name="payment_method_id" value="">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('storefront.backoffice.order_detail.quotation_notes_label') }}</label>
                                <input type="text" name="notes"
                                    placeholder="{{ __('storefront.backoffice.order_detail.quotation_notes_placeholder') }}"
                                    value="{{ $order->notes }}"
                                    class="w-full h-10 px-3.5 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>

                            <button type="submit"
                                class="w-full h-11 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white font-bold text-sm transition active:scale-[0.98] flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>{{ __('storefront.backoffice.order_detail.quotation_submit') }}</span>
                            </button>
                        </form>
                    </div>
                @endif

                {{-- 1. ORDER ITEMS CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08] mb-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.items_card_title') }}</h2>
                        </div>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                            {{ __('storefront.backoffice.orders.items_count', ['count' => $order->items->count()]) }}
                        </span>
                    </div>

                    <div class="divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                        @foreach ($order->items as $item)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-sm sm:text-[15px] font-bold text-black dark:text-white tracking-tight">
                                        {{ $item->product_name }}</h3>
                                    <div
                                        class="flex items-center gap-2 mt-1 text-xs sm:text-[13px] text-black/55 dark:text-white/55 tabular-nums">
                                        <span>{{ number_format((float) $item->quantity, 0, ',', '.') }}
                                            {{ $item->unit_name ?? 'pcs' }}</span>
                                        <span>&times;</span>
                                        <span>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($item->notes)
                                        <p
                                            class="text-xs text-amber-700 dark:text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-lg mt-2 inline-block">
                                            {{ __('storefront.backoffice.order_detail.item_notes_prefix', ['notes' => $item->notes]) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-sm sm:text-[15px] font-bold text-black dark:text-white tracking-tight tabular-nums">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- FINANCIAL SUMMARY --}}
                    <div
                        class="mt-6 pt-4 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2.5 text-xs sm:text-sm">
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span>{{ __('storefront.backoffice.order_detail.subtotal_label') }}</span>
                            <span class="tabular-nums font-medium text-black dark:text-white">Rp
                                {{ number_format((float) $order->subtotal_amount, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $actualShipping = (float) ($order->shipping_cost ?? ($order->shipping_fee ?? 0));
                        @endphp
                        @if ($actualShipping > 0)
                            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                <span>{{ __('storefront.backoffice.order_detail.shipping_label') }}
                                    ({{ $order->shipping_courier_name ?: ($order->fulfillment_type === 'pickup' ? __('storefront.backoffice.order_detail.method_pickup') : __('storefront.backoffice.orders.delivery_store')) }})</span>
                                <span class="tabular-nums font-medium text-black dark:text-white">Rp
                                    {{ number_format($actualShipping, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ((float) ($order->biteship_service_fee ?? 0) > 0)
                            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                <span class="flex items-center gap-1.5">
                                    <span>{{ __('storefront.backoffice.order_detail.biteship_fee_sublabel') }}</span>
                                    <span
                                        class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF]">{{ __('storefront.backoffice.order_detail.biteship_platform_fee') }}</span>
                                </span>
                                <span class="tabular-nums font-semibold text-black dark:text-white">Rp
                                    {{ number_format((float) $order->biteship_service_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ((float) $order->discount_amount > 0)
                            <div class="flex items-center justify-between text-[#34C759]">
                                <span>{{ __('storefront.backoffice.order_detail.discount_label') }}</span>
                                <span class="tabular-nums font-semibold">-Rp
                                    {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div
                            class="flex items-center justify-between pt-3 border-t border-black/[0.06] dark:border-white/[0.08] text-base sm:text-lg font-bold text-black dark:text-white">
                            <span>{{ __('storefront.backoffice.order_detail.total_bill_label') }}</span>
                            <span class="text-[#007AFF] tabular-nums font-extrabold">Rp
                                {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- MULTI-DROP DELIVERY BATCHES (PO BATCHES) --}}
                @if ($order->batches->isNotEmpty())
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs space-y-4">
                        <div
                            class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-lg bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.multi_drop_title') }}</h2>
                                    <p class="text-xs text-black/55 dark:text-white/55">{{ __('storefront.backoffice.order_detail.multi_drop_subtitle') }}</p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#5856D6]/10 text-[#5856D6] border border-[#5856D6]/20">
                                {{ __('storefront.backoffice.order_detail.multi_drop_completed_badge', ['delivered' => $order->delivered_batches_count, 'total' => $order->total_batches_count]) }}
                            </span>
                        </div>

                        <div class="space-y-3 divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                            @foreach ($order->batches as $batch)
                                <div class="pt-3.5 first:pt-0 space-y-3" x-data="{ editing: false }">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] font-bold text-xs flex items-center justify-center shrink-0 text-black dark:text-white">
                                                #{{ $batch->batch_number }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span
                                                        class="font-bold text-sm text-black dark:text-white">{{ $batch->batch_code }}</span>
                                                    <span class="text-xs text-black/50 dark:text-white/50">&bull;
                                                        {{ $batch->scheduled_date->translatedFormat('d M Y') }}</span>
                                                    @if ($batch->scheduled_time_slot)
                                                        <span
                                                            class="text-[11px] px-2 py-0.5 rounded-md bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">{{ $batch->scheduled_time_slot }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-black/60 dark:text-white/60 mt-0.5">
                                                    {{ __('storefront.backoffice.order_detail.batch_volume_label') }} <strong
                                                        class="text-black dark:text-white">{{ __('storefront.backoffice.order_detail.batch_volume_unit', ['count' => number_format($batch->quantity, 0, ',', '.')]) }}</strong>
                                                    @if ($batch->tracking_number)
                                                        &bull; {{ __('storefront.backoffice.order_detail.batch_waybill_label') }} <code
                                                            class="font-mono text-[#007AFF] font-bold">{{ $batch->tracking_number }}</code>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 self-start sm:self-center">
                                            @if ($batch->status === 'delivered')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('storefront.backoffice.order_detail.batch_delivered_badge', ['date' => $batch->delivered_at?->format('d/m H:i')]) }}</span>
                                                </span>
                                            @elseif($batch->status === 'shipped')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-[#5856D6]/15 text-[#5856D6]">
                                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('storefront.backoffice.order_detail.batch_shipped_badge') }}</span>
                                                </span>
                                            @elseif($batch->status === 'in_preparation')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('storefront.backoffice.order_detail.batch_in_prep_badge') }}</span>
                                                </span>
                                            @elseif($batch->status === 'cancelled')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/15 text-red-600">
                                                    <span>{{ __('storefront.backoffice.order_detail.batch_cancelled_badge') }}</span>
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('storefront.backoffice.order_detail.batch_scheduled_badge') }}</span>
                                                </span>
                                            @endif

                                            <button type="button" @click="editing = !editing"
                                                class="p-2 rounded-xl hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition cursor-pointer"
                                                title="{{ __('storefront.backoffice.order_detail.batch_edit_tooltip') }}">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Inline Status Update Form --}}
                                    <div x-show="editing" x-transition
                                        class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                        <form action="{{ route('storefront.orders.batches.status', [$order, $batch]) }}"
                                            method="POST" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('storefront.backoffice.order_detail.batch_update_status') }}</label>
                                                    <select name="status"
                                                        class="w-full h-10 px-3 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                                        <option value="scheduled"
                                                            {{ $batch->status === 'scheduled' ? 'selected' : '' }}>
                                                            {{ __('storefront.backoffice.order_detail.batch_status_opt_scheduled') }}</option>
                                                        <option value="in_preparation"
                                                            {{ $batch->status === 'in_preparation' ? 'selected' : '' }}>
                                                            {{ __('storefront.backoffice.order_detail.batch_status_opt_in_prep') }}</option>
                                                        <option value="shipped"
                                                            {{ $batch->status === 'shipped' ? 'selected' : '' }}>{{ __('storefront.backoffice.order_detail.batch_status_opt_shipped') }}</option>
                                                        <option value="delivered"
                                                            {{ $batch->status === 'delivered' ? 'selected' : '' }}>{{ __('storefront.backoffice.order_detail.batch_status_opt_delivered') }}</option>
                                                        <option value="cancelled"
                                                            {{ $batch->status === 'cancelled' ? 'selected' : '' }}>
                                                            {{ __('storefront.backoffice.order_detail.batch_status_opt_cancelled') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">{{ __('storefront.backoffice.order_detail.batch_tracking_optional') }}</label>
                                                    <input type="text" name="tracking_number"
                                                        value="{{ $batch->tracking_number }}"
                                                        placeholder="{{ __('storefront.backoffice.order_detail.batch_waybill_input_placeholder') }}"
                                                        class="w-full h-10 px-3 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="button" @click="editing = false"
                                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 cursor-pointer">{{ __('storefront.backoffice.order_detail.btn_cancel') }}</button>
                                                <button type="submit"
                                                    class="px-4 py-1.5 rounded-xl bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition active:scale-[0.98] cursor-pointer">{{ __('storefront.backoffice.order_detail.save_status') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 2. CUSTOMER & FULFILLMENT CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08] mb-4">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">Informasi
                            {{ __('storefront.backoffice.order_detail.customer_info_title') }}</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div>
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.customer_name_label') }}</span>
                            <p class="font-bold text-black dark:text-white text-sm sm:text-base">{{ $order->customer_name }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.customer_phone_label') }}</span>
                            <p class="font-bold text-black dark:text-white font-mono">{{ $order->customer_phone }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.fulfillment_method_label') }}</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80">
                                @if ($order->fulfillment_type === 'pickup')
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i> {{ __('storefront.backoffice.order_detail.fulfillment_pickup_label') }}
                                @else
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> {{ __('storefront.backoffice.order_detail.fulfillment_biteship_label', ['courier' => $order->shipping_courier_name ?: ($order->shipping_courier_code ? strtoupper($order->shipping_courier_code) . ' - ' . strtoupper($order->shipping_courier_service) : 'Kurir')]) }}
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.customer_email_label') }}</span>
                            <p class="text-black/80 dark:text-white/80 font-medium">{{ $order->customer_email ?? '-' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.shipping_address_label') }}</span>
                            <p
                                class="text-black/80 dark:text-white/80 bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] p-3.5 rounded-xl leading-relaxed">
                                {{ $order->shipping_address ?? __('storefront.backoffice.order_detail.no_shipping_address') }}
                                @if ($order->destination_postal_code)
                                    <span class="block mt-1 font-mono text-xs font-bold text-[#007AFF]">{{ __('storefront.backoffice.order_detail.postal_code_inline', ['code' => $order->destination_postal_code]) }}</span>
                                @endif
                            </p>
                        </div>
                        @if ($order->notes)
                            <div class="sm:col-span-2">
                                <span class="text-xs font-semibold text-black/45 dark:text-white/45 block mb-1">{{ __('storefront.backoffice.order_detail.additional_notes_label') }}</span>
                                <p
                                    class="text-black/80 dark:text-white/80 italic bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] p-3.5 rounded-xl">
                                    "{{ $order->notes }}"
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 3. LOGISTIK & EKSPEDISI CARD (When not pickup) --}}
                @if ($order->fulfillment_type !== 'pickup')
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs space-y-4">
                        <div
                            class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="truck" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                                        {{ __('storefront.backoffice.order_detail.logistics_title') }}</h2>
                                    <p class="text-xs text-black/55 dark:text-white/55">{{ __('storefront.backoffice.order_detail.logistics_subtitle') }}</p>
                                </div>
                            </div>
                            @if ($order->shipping_waybill_id || $order->biteship_order_id)
                                @php
                                    $st = strtolower($order->shipping_status ?? 'allocated');
                                    $badgeClass = match ($st) {
                                        'delivered' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20',
                                        'in_transit',
                                        'picking_up',
                                        'picked',
                                        'dropping_off'
                                            => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                                        'cancelled', 'rejected' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                                        default => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20',
                                    };
                                @endphp
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $st) }}
                                </span>
                            @else
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50">
                                    {{ __('storefront.backoffice.order_detail.not_shipped_yet') }}
                                </span>
                            @endif
                        </div>

                        @if ($order->shipping_waybill_id || $order->biteship_order_id)
                            <div
                                class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5 text-xs sm:text-[13px]">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-xs block mb-1">{{ __('storefront.backoffice.order_detail.awb_number') }}</span>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-mono font-bold text-black dark:text-white text-base tracking-wide">
                                                {{ $order->shipping_waybill_id ?: __('storefront.backoffice.order_detail.waiting_courier_allocation') }}
                                            </span>
                                            @if ($order->shipping_waybill_id)
                                                <button type="button"
                                                    onclick="navigator.clipboard.writeText('{{ $order->shipping_waybill_id }}'); alert('{{ __('storefront.backoffice.order_detail.copy_waybill_alert') }}')"
                                                    class="px-2.5 py-1 rounded-lg bg-black/[0.05] hover:bg-black/[0.1] dark:bg-white/[0.1] text-xs text-[#007AFF] font-bold transition active:scale-95 cursor-pointer">
                                                    {{ __('storefront.backoffice.order_detail.copy_waybill_btn') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-black/50 dark:text-white/50 text-xs block mb-1">{{ __('storefront.backoffice.order_detail.courier_and_service_label') }}</span>
                                        <span class="font-bold text-black dark:text-white text-sm block">
                                            {{ $order->shipping_courier_name ?: strtoupper(($order->shipping_courier_code ?: 'KURIR') . ' ' . ($order->shipping_courier_service ?: 'REGULER')) }}
                                        </span>
                                    </div>
                                </div>

                                @if ($order->shipping_tracking_url)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-black/50 dark:text-white/50">{{ __('storefront.backoffice.order_detail.tracking_link_label') }}</span>
                                        <a href="{{ $order->shipping_tracking_url }}" target="_blank"
                                            class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                                            <span>{{ __('storefront.backoffice.order_detail.live_tracking_biteship') }}</span>
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                @endif

                                {{-- ACTION BUTTONS: CETAK RESI, EDIT RESI, SINKRONISASI --}}
                                <div class="pt-2 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('storefront.orders.shipping_label', $order) }}" target="_blank"
                                        class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold transition active:scale-[0.98] flex items-center gap-1.5 shadow-xs cursor-pointer">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('storefront.backoffice.order_detail.print_label_thermal') }}</span>
                                    </a>

                                    <button type="button" @click="waybillModalOpen = true"
                                        class="h-9 px-3.5 rounded-xl bg-black/[0.05] hover:bg-black/[0.1] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('storefront.backoffice.order_detail.edit_waybill') }}</span>
                                    </button>

                                    @if ($order->biteship_order_id)
                                        <form action="{{ route('storefront.orders.biteship.track', $order) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="h-9 px-3.5 rounded-xl bg-black/[0.05] hover:bg-black/[0.1] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.backoffice.order_detail.sync_status') }}</span>
                                            </button>
                                        </form>

                                        @if (in_array(strtolower($order->shipping_status ?? ''), ['confirmed', 'scheduled', 'allocated', 'picking_up'], true))
                                            <form action="{{ route('storefront.orders.biteship.cancel', $order) }}"
                                                method="POST"
                                                onsubmit="return confirm('{{ __('storefront.backoffice.order_detail.cancel_pickup_confirm') }}')">
                                                @csrf
                                                <button type="submit"
                                                    class="h-9 px-3.5 rounded-xl bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-xs font-semibold transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('storefront.backoffice.order_detail.cancel_pickup') }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- UNFULFILLED: PICKUP REQUEST & MANUAL WAYBILL BUTTONS --}}
                            <div
                                class="p-4 sm:p-5 rounded-xl bg-[#007AFF]/[0.04] border border-[#007AFF]/15 space-y-3.5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <span class="text-sm font-bold text-black dark:text-white block">
                                            {{ __('storefront.backoffice.order_detail.ready_to_ship_title') }}
                                        </span>
                                        <span class="text-xs text-black/55 dark:text-white/55 mt-0.5 block">
                                            {{ __('storefront.backoffice.order_detail.courier_choice_label') }}
                                            <strong>{{ $order->shipping_courier_name ?: strtoupper($order->shipping_courier_code ?: 'JNE') }}</strong>
                                            &bull; {{ __('storefront.backoffice.order_detail.shipping_fee_inline', ['amount' => number_format((float) $order->shipping_fee, 0, ',', '.')]) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('storefront.orders.shipping_label', $order) }}" target="_blank"
                                            class="h-9 px-3.5 rounded-xl bg-black/[0.05] hover:bg-black/[0.1] dark:bg-white/[0.08] text-black dark:text-white font-semibold text-xs transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.print_label_btn') }}</span>
                                        </a>

                                        <button type="button" @click="waybillModalOpen = true"
                                            class="h-9 px-3.5 rounded-xl bg-black/[0.05] hover:bg-black/[0.1] dark:bg-white/[0.08] text-black dark:text-white font-semibold text-xs transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.input_waybill_btn') }}</span>
                                        </button>

                                        <form action="{{ route('storefront.orders.biteship.create', $order) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ __('storefront.backoffice.order_detail.request_pickup_confirm', ['courier' => $order->shipping_courier_code ?: 'ekspedisi']) }}')">
                                            @csrf
                                            <button type="submit"
                                                class="h-9 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white font-bold text-xs transition active:scale-[0.98] flex items-center gap-1.5 shadow-xs cursor-pointer">
                                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.backoffice.order_detail.request_pickup_btn') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: PAYMENT PROOF & OPERATIONS (4 COLS - STICKY DESKTOP) --}}
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">

                {{-- 1. PEMBAYARAN QRIS CARD (APPLE BENTO HIG) --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08] mb-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-lg {{ $order->isPaid() ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#007AFF]/10 text-[#007AFF]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                                {{ __('storefront.backoffice.order_detail.qris_card_title') }}
                            </h2>
                        </div>
                        @if ($order->isPaid())
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">{{ __('storefront.backoffice.order_detail.qris_paid_badge') }}</span>
                        @else
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">{{ __('storefront.backoffice.order_detail.qris_awaiting_badge') }}</span>
                        @endif
                    </div>

                    {{-- QRIS TRANSACTION SUMMARY --}}
                    <div class="space-y-3.5">
                        <div
                            class="p-4 rounded-xl bg-[#007AFF]/[0.04] border border-[#007AFF]/15 space-y-2.5 text-xs sm:text-[13px]">
                            <div class="flex items-center justify-between">
                                <span class="text-black/60 dark:text-white/60">{{ __('storefront.backoffice.order_detail.method_channel_label') }}</span>
                                <span
                                    class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>{{ __('storefront.backoffice.order_detail.qris_national_standard') }}</span>
                                </span>
                            </div>
                            @if ($order->gateway_reference)
                                <div class="flex items-center justify-between">
                                    <span class="text-black/60 dark:text-white/60">{{ __('storefront.backoffice.order_detail.gateway_ref_label') }}</span>
                                    <span
                                        class="font-mono font-semibold text-[#007AFF]">{{ $order->gateway_reference }}</span>
                                </div>
                            @endif
                            @if ($order->gateway_pay_code)
                                <div class="flex items-center justify-between">
                                    <span class="text-black/60 dark:text-white/60">{{ __('storefront.backoffice.order_detail.pay_code_label') }}</span>
                                    <span
                                        class="font-mono font-bold text-black dark:text-white">{{ $order->gateway_pay_code }}</span>
                                </div>
                            @endif
                            <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-black/60 dark:text-white/60">{{ __('storefront.backoffice.order_detail.total_bill_inline') }}</span>
                                    <span class="font-bold text-black dark:text-white tabular-nums">Rp
                                        {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                                @if ((float) ($order->gateway_fee ?? 0) > 0)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-amber-700 dark:text-amber-400">{{ __('storefront.backoffice.order_detail.gateway_fee_label') }}</span>
                                        <span
                                            class="font-semibold text-amber-700 dark:text-amber-400 tabular-nums">-Rp
                                            {{ number_format($order->gateway_fee, 0, ',', '.') }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between font-bold text-sm pt-1 text-emerald-700 dark:text-emerald-400">
                                        <span>{{ __('storefront.backoffice.order_detail.net_revenue_label') }}</span>
                                        <span class="tabular-nums">Rp
                                            {{ number_format($order->net_revenue, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($order->isPaid())
                            <div
                                class="p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-xs flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                <span>{{ __('storefront.backoffice.order_detail.qris_paid_notice', ['date' => $order->paid_at?->translatedFormat('d M Y, H:i') ?? '-']) }}</span>
                            </div>
                        @else
                            <div
                                class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                <span>{{ __('storefront.backoffice.order_detail.qris_awaiting_notice') }}</span>
                            </div>

                            <div class="space-y-2 pt-1">
                                @if ($order->gateway_reference)
                                    <form action="{{ route('storefront.orders.sync_gateway', $order->id) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full h-10 rounded-xl bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-xs font-semibold flex items-center justify-center gap-1.5 transition active:scale-[0.98] cursor-pointer">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.sync_qris_btn') }}</span>
                                        </button>
                                    </form>
                                @endif

                                <button type="button" @click="verifyModalOpen = true"
                                    class="w-full h-11 rounded-xl bg-[#34C759] hover:bg-[#2EB04E] text-white text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 shadow-xs cursor-pointer active:scale-[0.98]">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    <span>{{ __('storefront.backoffice.order_detail.manual_confirm_qris_btn') }}</span>
                                </button>
                                <p class="text-[11px] text-black/45 dark:text-white/45 text-center">{{ __('storefront.backoffice.order_detail.manual_confirm_qris_hint') }}</p>
                            </div>
                        @endif

                        {{-- HISTORICAL ATTACHMENT / PROOF IF ANY --}}
                        @php
                            $latestProof = $order->latestProof;
                        @endphp
                        @if ($latestProof)
                            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                                <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                                    <span class="flex items-center gap-1.5 font-medium">
                                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('storefront.backoffice.order_detail.proof_attachment_label') }}</span>
                                    </span>
                                    <span>{{ $latestProof->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                                <button type="button"
                                    @click="activeImageUrl = '{{ route('storefront.proofs.stream', $latestProof->id) }}'; imageModalOpen = true"
                                    class="w-full py-2 px-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-xs font-semibold text-[#007AFF] flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('storefront.backoffice.order_detail.view_proof_image_btn') }}</span>
                                </button>

                                @if ($latestProof->isPending() || $order->status === 'proof_submitted')
                                    <div class="space-y-2 pt-1">
                                        <button type="button" @click="verifyModalOpen = true"
                                            class="w-full h-10 rounded-xl bg-[#34C759] hover:bg-[#2EB04E] text-white text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 shadow-xs cursor-pointer active:scale-[0.98]">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.verify_payment_action_btn') }}</span>
                                        </button>
                                        <button type="button" @click="rejectModalOpen = true"
                                            class="w-full h-9 rounded-xl bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('storefront.backoffice.order_detail.reject_payment_action_btn') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 2. OPERATIONAL STATUS CARD (COMPATIBLE ACROSS 20 BUSINESS SECTORS) --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-xs">
                    <div
                        class="flex items-center gap-2.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08] mb-4">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.operational_status_card_title') }}</h2>
                    </div>

                    <form action="{{ route('storefront.orders.update_status', $order->id) }}" method="POST"
                        class="space-y-4" x-data="{ currentStatus: '{{ $order->status }}' }">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-black/50 dark:text-white/50 mb-1.5">{{ __('storefront.backoffice.order_detail.update_order_stage_label') }}</label>
                            <select name="status" x-model="currentStatus"
                                class="w-full h-11 px-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="processing">{{ __('storefront.backoffice.order_detail.opt_processing') }}</option>
                                <option value="ready">{{ __('storefront.backoffice.order_detail.opt_ready') }}</option>
                                @if ($order->fulfillment_type !== 'pickup')
                                    <option value="shipped">{{ __('storefront.backoffice.order_detail.opt_shipped') }}</option>
                                @endif
                                <option value="completed">{{ __('storefront.backoffice.order_detail.opt_completed') }}</option>
                                <option value="cancelled">{{ __('storefront.backoffice.order_detail.opt_cancelled') }}</option>
                            </select>
                        </div>

                        {{-- Dynamic Cancellation Reason Input --}}
                        <div x-show="currentStatus === 'cancelled'" x-cloak class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#FF3B30] mb-1">{{ __('storefront.backoffice.order_detail.cancel_reason_required') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="text" name="cancel_reason" placeholder="{{ __('storefront.backoffice.order_detail.cancel_reason_placeholder') }}"
                                class="w-full h-10 px-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-[#FF3B30]/30 text-[16px] sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                        </div>

                        <button type="submit"
                            class="w-full h-10 rounded-xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs sm:text-sm font-semibold transition active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>{{ __('storefront.backoffice.order_detail.btn_save_status') }}</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

        {{-- MODAL TOLAK BUKTI BAYAR (APPLE HIG SHEET DIALOG FOR LEGACY PROOFS) --}}
        <template x-teleport="body">
            <div x-show="rejectModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-end sm:items-center justify-center p-0 sm:p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="rejectModalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="rejectModalOpen = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4 max-h-[92vh] overflow-y-auto"
                    @click.outside="rejectModalOpen = false">
                    {{-- Mobile Grab Bar --}}
                    <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-1 sm:hidden"></div>

                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2 text-[#FF3B30]">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            <h3 class="text-base font-bold text-black dark:text-white">{{ __('storefront.backoffice.order_detail.reject_modal_header') }}</h3>
                        </div>
                        <button type="button" @click="rejectModalOpen = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                        {{ __('storefront.backoffice.order_detail.reject_modal_prompt') }}
                    </p>

                    <form action="{{ route('storefront.orders.reject_payment', $order->id) }}" method="POST"
                        class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-black/60 dark:text-white/60 mb-1.5">{{ __('storefront.backoffice.order_detail.reject_reason_label') }} <span class="text-[#FF3B30]">*</span></label>
                            <textarea name="rejection_reason" rows="3" required
                                placeholder="{{ __('storefront.backoffice.order_detail.reject_reason_placeholder') }}"
                                class="w-full p-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30] resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="rejectModalOpen = false"
                                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white cursor-pointer transition">
                                {{ __('storefront.backoffice.order_detail.btn_cancel_dialog') }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#FF3B30] hover:bg-[#E0352B] text-white text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                {{ __('storefront.backoffice.order_detail.btn_confirm_reject') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- APPLE ALERT CONFIRMATION DIALOG (KONFIRMASI PEMBAYARAN QRIS) --}}
        <template x-teleport="body">
            <div x-show="verifyModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="verifyModalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="verifyModalOpen = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"
                    @click.outside="verifyModalOpen = false">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.verify_modal_header') }}</h3>
                            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                {!! __('storefront.backoffice.order_detail.verify_modal_prompt', [
                                    'total' => '<span class="tabular-nums">' . number_format($order->total_amount, 0, ',', '.') . '</span>',
                                    'customer' => e($order->customer_name)
                                ]) !!}
                            </p>
                        </div>
                    </div>

                    {{-- PENENANG JIWA & STOCK ALLOCATION NOTICE --}}
                    <div
                        class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex items-start gap-2.5 text-xs text-black/60 dark:text-white/60 leading-relaxed">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <span>{{ __('storefront.backoffice.order_detail.verify_modal_stock_notice') }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="verifyModalOpen = false"
                            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">
                            {{ __('storefront.backoffice.order_detail.btn_cancel_dialog') }}
                        </button>
                        <form action="{{ route('storefront.orders.verify_payment', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#34C759] hover:bg-[#2EB04E] text-white text-xs sm:text-sm font-bold transition active:scale-[0.98] shadow-xs cursor-pointer">
                                {{ __('storefront.backoffice.order_detail.btn_confirm_settled') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL ZOOM GAMBAR BUKTI (APPLE LIGHTBOX) --}}
        <template x-teleport="body">
            <div x-show="imageModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="imageModalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="imageModalOpen = false"
                    class="fixed inset-0 bg-black/85 backdrop-blur-md"></div>

                <div class="relative z-10 max-w-2xl max-h-[90vh] overflow-hidden rounded-[20px] shadow-2xl" @click.stop>
                    <button type="button" @click="imageModalOpen = false"
                        class="absolute top-3 right-3 w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/80 transition z-10 cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                    <img :src="activeImageUrl" alt="{{ __('storefront.backoffice.order_detail.preview_proof_alt') }}"
                        class="w-full h-auto max-h-[85vh] object-contain rounded-[20px]">
                </div>
            </div>
        </template>

        {{-- MODAL INPUT / TERBITKAN NOMOR RESI --}}
        <template x-teleport="body">
            <div x-show="waybillModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="waybillModalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="waybillModalOpen = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/[0.08] dark:border-white/[0.1] space-y-4"
                    @click.outside="waybillModalOpen = false">
                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="tag" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.order_detail.waybill_modal_header') }}</h3>
                                <p class="text-xs text-black/50 dark:text-white/50">{{ __('storefront.backoffice.order_detail.waybill_modal_sub', ['order' => $order->order_number, 'customer' => $order->customer_name]) }}</p>
                            </div>
                        </div>
                        <button type="button" @click="waybillModalOpen = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('storefront.orders.waybill.update', $order) }}" method="POST"
                        class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('storefront.backoffice.order_detail.waybill_input_label') }}
                            </label>
                            <div class="relative">
                                <input type="text" name="shipping_waybill_id" id="waybillInput"
                                    value="{{ $order->shipping_waybill_id }}"
                                    placeholder="{{ __('storefront.backoffice.order_detail.waybill_input_placeholder') }}"
                                    class="w-full h-11 px-3.5 pr-28 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-sm font-mono font-bold text-black dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <button type="button"
                                    onclick="document.getElementById('waybillInput').value = 'CC' + '{{ strtoupper(substr($order->shipping_courier_code ?: 'JNE', 0, 3)) }}' + '{{ now()->format('ymd') }}' + Math.random().toString(36).substring(2, 6).toUpperCase();"
                                    class="absolute right-1.5 top-1.5 h-8 px-2.5 rounded-lg bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] font-bold text-xs transition cursor-pointer">
                                    {{ __('storefront.backoffice.order_detail.btn_auto_waybill') }}
                                </button>
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                                {{ __('storefront.backoffice.order_detail.waybill_hint') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('storefront.backoffice.order_detail.courier_select_label') }}
                                </label>
                                <select name="shipping_courier_code"
                                    class="w-full h-10 px-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    @php
                                        $currentCourier = strtolower($order->shipping_courier_code ?: 'jne');
                                    @endphp
                                    <option value="jne" {{ $currentCourier === 'jne' ? 'selected' : '' }}>JNE Express</option>
                                    <option value="sicepat" {{ $currentCourier === 'sicepat' ? 'selected' : '' }}>SiCepat Ekspres</option>
                                    <option value="jnt" {{ $currentCourier === 'jnt' ? 'selected' : '' }}>J&amp;T Express</option>
                                    <option value="anteraja" {{ $currentCourier === 'anteraja' ? 'selected' : '' }}>AnterAja</option>
                                    <option value="gosend" {{ $currentCourier === 'gosend' ? 'selected' : '' }}>GoSend Instant</option>
                                    <option value="grab" {{ $currentCourier === 'grab' ? 'selected' : '' }}>GrabExpress</option>
                                    <option value="kurir_toko" {{ $currentCourier === 'kurir_toko' ? 'selected' : '' }}>{{ __('storefront.backoffice.order_detail.courier_opt_internal') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('storefront.backoffice.order_detail.service_select_label') }}
                                </label>
                                <input type="text" name="shipping_courier_service"
                                    value="{{ $order->shipping_courier_service ?: 'REGULER' }}"
                                    placeholder="{{ __('storefront.backoffice.order_detail.service_placeholder') }}"
                                    class="w-full h-10 px-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white font-medium uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="waybillModalOpen = false"
                                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white cursor-pointer transition">
                                {{ __('storefront.backoffice.order_detail.btn_cancel_dialog') }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                {{ __('storefront.backoffice.order_detail.btn_save_waybill') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
@endsection
