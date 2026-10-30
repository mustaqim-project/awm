{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM HARVEST PRODUCT CARD (`agriculture_farm`)             --}}
{{-- Fresh farm-to-table card with commodity grades, unit weights & quick buy --}}
{{-- ========================================================================= --}}
@props([
    'product' => null,
    'item' => null,
    'business' => null,
    'activeTheme' => null,
])

@php
    $prod = $product ?? $item;
    if (!$prod) return;

    $productSlug = $prod->slug ?: $prod->id;
    $pdpUrl = url('/' . $business->slug . '/produk/' . $productSlug);
    $price = (float) ($prod->price ?? $prod->selling_price ?? 0);
    $slashPrice = (float) ($prod->slash_price ?? 0);
    $hasDiscount = $slashPrice > $price;
    $discountPercent = $hasDiscount && $slashPrice > 0 ? round((($slashPrice - $price) / $slashPrice) * 100) : 0;
    $hasPrice = ($prod->show_price_on_web ?? true) && $price > 0;
    $trackStock = (bool) ($prod->track_stock ?? false);
    $currentStock = (int) ($prod->stock ?? 0);
    $isOutOfStock = $trackStock && $currentStock <= 0;

    $productPayload = [
        'id' => $prod->id,
        'name' => $prod->name,
        'price' => $price,
        'image_url' => $prod->image_url,
        'quantity' => 1,
        'notes' => '',
    ];
@endphp

<div class="group flex flex-col rounded-2xl sm:rounded-3xl overflow-hidden bg-white dark:bg-[#0D1F12] border border-emerald-900/10 dark:border-emerald-500/15 shadow-[0_4px_16px_rgba(21,128,61,0.04)] hover:shadow-[0_16px_36px_rgba(21,128,61,0.12)] hover:-translate-y-1 transition-all duration-300">
    
    {{-- 1. Media Visual & Freshness Badges --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-emerald-50/50 dark:bg-emerald-950/40 block focus:outline-none">
        @if ($prod->image_url)
            <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-emerald-400 dark:text-emerald-600 p-4">
                <i data-lucide="sprout" class="w-12 h-12 stroke-[1.25] mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-800 dark:text-emerald-400">Komoditas Segar</span>
            </div>
        @endif

        {{-- Floating Badges --}}
        <div class="absolute top-2.5 inset-x-2.5 flex items-start justify-between gap-1.5 pointer-events-none">
            @if ($prod->category)
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold tracking-wide uppercase bg-white/92 dark:bg-[#07130A]/92 text-emerald-900 dark:text-emerald-200 backdrop-blur-md shadow-2xs border border-emerald-900/10">
                    {{ $prod->category->name }}
                </span>
            @else
                <span></span>
            @endif

            @if ($hasDiscount)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-rose-600 text-white shadow-2xs animate-pulse font-mono tabular-nums">
                    -{{ $discountPercent }}%
                </span>
            @elseif ($isOutOfStock)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-neutral-900 text-white shadow-2xs">
                    Musim Lewat
                </span>
            @else
                <span class="px-2.5 py-0.5 rounded-xl text-[10px] font-bold bg-amber-400 text-emerald-950 shadow-2xs flex items-center gap-1">
                    <i data-lucide="sun" class="w-2.5 h-2.5"></i>
                    <span>Grade A</span>
                </span>
            @endif
        </div>
    </a>

    {{-- 2. Content & Description --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between gap-3">
        <div class="space-y-1.5">
            <a href="{{ $pdpUrl }}" class="focus:outline-none block">
                <h3 class="font-heading font-normal text-base sm:text-lg text-neutral-900 dark:text-emerald-50 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors line-clamp-2 leading-snug">
                    {{ $prod->name }}
                </h3>
            </a>

            @if ($prod->short_description || $prod->description)
                <p class="text-xs text-neutral-500 dark:text-emerald-200/60 line-clamp-2 leading-relaxed">
                    {{ Str::limit(strip_tags($prod->short_description ?: $prod->description), 80) }}
                </p>
            @endif
        </div>

        {{-- 3. Price & Action Stepper --}}
        <div class="pt-3 border-t border-emerald-900/8 dark:border-emerald-500/12 flex items-center justify-between gap-2">
            <div class="flex flex-col min-w-0">
                <span class="text-[10px] text-neutral-400 dark:text-emerald-400/60 font-medium">Harga Panen</span>
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="font-bold text-sm sm:text-base text-emerald-800 dark:text-emerald-300 font-mono tracking-tight tabular-nums">
                        {{ $hasPrice ? 'Rp ' . number_format($price, 0, ',', '.') : 'Hubungi Kebun' }}
                    </span>
                    @if ($hasDiscount)
                        <span class="text-[10px] sm:text-xs text-neutral-400 line-through font-mono tabular-nums">
                            Rp {{ number_format($slashPrice, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                @if ($prod->unit)
                    <span class="text-[10px] text-neutral-500 dark:text-emerald-300/70 font-medium">/ {{ $prod->unit }}</span>
                @endif
            </div>

            {{-- Buy / Add to Cart Action --}}
            @if ($isOutOfStock)
                <button type="button" disabled
                    class="px-3 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 text-xs font-semibold cursor-not-allowed min-h-[40px]">
                    Habis
                </button>
            @elseif ($hasPrice)
                <button type="button"
                    @click="$store.cart.add(@js($productPayload), 1); $dispatch('product-added-notification', { name: @js($prod->name) })"
                    class="px-3.5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 shrink-0 min-h-[40px]"
                    title="{{ __('storefront.add_harvest_to_cart') }}">
                    <i data-lucide="shopping-basket" class="w-3.5 h-3.5"></i>
                    <span>Pesan</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-2 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-200 transition min-h-[40px] flex items-center justify-center">{{ __('storefront.btn_detail') }}</a>
            @endif
        </div>
    </div>
</div>
