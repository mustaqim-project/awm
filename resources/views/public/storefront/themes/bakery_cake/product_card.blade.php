{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE PRODUCT CARD (`bakery_cake`)                  --}}
{{-- Patisserie Vitrine Showcase: Editorial Aspect Ratio, Pure Elegance & Tap  --}}
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

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#1D1714] border border-stone-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_16px_36px_rgba(45,27,16,0.08)] hover:-translate-y-1 transition-all duration-300">
    
    {{-- 1. Vitrine Image Container (1:1 Aspect Ratio for Culinary Perfection) --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-stone-100 dark:bg-stone-900/60 block focus:outline-none">
        @if ($prod->image_url)
            <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" loading="lazy">
        @else
            {{-- Refined Pastry Atelier Placeholder (No Ugly Yellow Box) --}}
            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-stone-50 to-stone-100 dark:from-[#1D1714] dark:to-[#150F0C] relative">
                <div class="w-16 h-16 rounded-full bg-amber-900/[0.04] dark:bg-amber-400/[0.06] border border-amber-900/[0.08] dark:border-amber-400/[0.12] flex items-center justify-center text-amber-800/60 dark:text-amber-300/60 mb-2">
                    <i data-lucide="cake-slice" class="w-8 h-8 stroke-[1.2]"></i>
                </div>
                <span class="text-[10px] font-mono font-semibold uppercase tracking-widest text-stone-400 dark:text-stone-500">
                    Koleksi Dapur
                </span>
            </div>
        @endif

        {{-- Floating Badges (Subtle & High-Contrast) --}}
        <div class="absolute top-3 inset-x-3 flex items-start justify-between gap-1.5 pointer-events-none">
            @if ($prod->category)
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold tracking-wider uppercase bg-white/95 dark:bg-[#14100E]/95 text-stone-800 dark:text-stone-200 backdrop-blur-md shadow-2xs border border-stone-200/60 dark:border-stone-800">
                    {{ $prod->category->name }}
                </span>
            @else
                <span></span>
            @endif

            @if ($hasDiscount)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-rose-600 text-white shadow-2xs font-mono tabular-nums">
                    -{{ $discountPercent }}%
                </span>
            @elseif ($isOutOfStock)
                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-stone-900 text-white shadow-2xs">
                    Habis
                </span>
            @endif
        </div>
    </a>

    {{-- 2. Card Content & Details --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between gap-3">
        <div class="space-y-1.5">
            <a href="{{ $pdpUrl }}" class="focus:outline-none block">
                <h3 class="font-heading font-normal text-lg sm:text-xl text-stone-900 dark:text-stone-100 group-hover:text-amber-800 dark:group-hover:text-amber-300 transition-colors line-clamp-2 leading-snug">
                    {{ $prod->name }}
                </h3>
            </a>

            @if ($prod->short_description || $prod->description)
                <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2 leading-relaxed">
                    {{ Str::limit(strip_tags($prod->short_description ?: $prod->description), 75) }}
                </p>
            @endif
        </div>

        {{-- 3. Price & Action Stepper --}}
        <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between gap-3">
            <div class="flex flex-col min-w-0">
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="font-bold text-base sm:text-lg text-amber-900 dark:text-amber-400 font-mono tracking-tight tabular-nums">
                        {{ $hasPrice ? 'Rp ' . number_format($price, 0, ',', '.') : 'Hubungi Toko' }}
                    </span>
                    @if ($hasDiscount)
                        <span class="text-[11px] text-stone-400 line-through font-mono tabular-nums">
                            Rp {{ number_format($slashPrice, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                @if ($prod->unit)
                    <span class="text-[10px] text-stone-400 font-medium">per {{ $prod->unit }}</span>
                @endif
            </div>

            {{-- Buy / Add to Cart Action with Micro-Interactions --}}
            @if ($isOutOfStock)
                <button type="button" disabled
                    class="px-3.5 py-2 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-400 text-xs font-semibold cursor-not-allowed min-h-[44px]">
                    Habis
                </button>
            @elseif ($hasPrice)
                <button type="button"
                    x-data="{ added: false }"
                    @click="$store.cart.add(@js($productPayload), 1); $dispatch('product-added-notification', { name: @js($prod->name) }); added = true; setTimeout(() => added = false, 1200)"
                    :class="added ? 'bg-emerald-600 hover:bg-emerald-700 text-white scale-95' : 'bg-amber-700 hover:bg-amber-800 text-white'"
                    class="px-4 py-2 rounded-xl active:scale-95 text-xs font-bold shadow-xs transition-all duration-200 flex items-center gap-1.5 shrink-0 min-h-[44px]"
                    title="Tambahkan ke pesanan">
                    <template x-if="!added">
                        <span class="inline-flex items-center gap-1.5">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>Pesan</span>
                        </span>
                    </template>
                    <template x-if="added">
                        <span class="inline-flex items-center gap-1.5 animate-fadeIn">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Masuk</span>
                        </span>
                    </template>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 text-xs font-semibold hover:bg-stone-200 transition min-h-[44px] flex items-center justify-center">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
