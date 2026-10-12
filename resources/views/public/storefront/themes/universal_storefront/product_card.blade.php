{{--
    Universal Storefront Product Card (`universal_storefront` / `universal_clean`)
    Apple HIG Bento Card, clean typography, dynamic accent button, and instant cart integration.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-neutral-100 dark:bg-neutral-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-neutral-400">
                <i data-lucide="package" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        @if ($item->is_preorder)
            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                Pre-Order
            </span>
        @endif

        @if ($item->category)
            <span class="absolute bottom-2.5 left-2.5 px-2 py-0.5 rounded-lg text-[10px] font-medium bg-black/60 text-white backdrop-blur-sm">
                {{ $item->category->name }}
            </span>
        @endif
    </a>

    {{-- Info --}}
    <div class="p-4 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-neutral-900 dark:text-white group-hover:text-theme-primary transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price and Action Row --}}
        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-black/5 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-medium">Harga</span>
                <span class="font-bold text-sm sm:text-base text-neutral-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Toko' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-xl bg-neutral-100 dark:bg-neutral-700 hover:bg-theme-primary hover:text-white text-neutral-700 dark:text-neutral-200 transition active:scale-95 shadow-sm min-w-[42px] min-h-[42px] flex items-center justify-center"
                    title="Tambah ke Keranjang">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-700 text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
