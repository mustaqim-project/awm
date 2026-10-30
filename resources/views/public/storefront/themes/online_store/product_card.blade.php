{{--
    Online Store Product Card (`online_store`)
    Modern retail commerce card with ready stock badge, sharp pricing, and instant add-to-cart.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xl hover:border-indigo-200 dark:hover:border-indigo-900/50 transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-800/80 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                <i data-lucide="shopping-bag" class="w-10 h-10 stroke-1 mb-1"></i>
                <span class="text-[10px] font-medium">Ready Stock</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white/95 dark:bg-zinc-900/95 text-zinc-900 dark:text-zinc-100 shadow-xs backdrop-blur-xs border border-zinc-200/60 dark:border-zinc-700/60">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-600 text-white shadow-xs">
            Ori
        </span>
    </a>

    {{-- Content --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Cart CTA --}}
        <div class="mt-auto pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
            <div>
                <span class="text-[10px] text-zinc-400 block font-medium">Harga Resmi</span>
                <span class="font-extrabold text-sm sm:text-base text-zinc-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Toko' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-xl bg-zinc-900 hover:bg-indigo-600 dark:bg-zinc-100 dark:hover:bg-indigo-500 text-white dark:text-zinc-950 dark:hover:text-white transition active:scale-95 shadow-xs flex items-center justify-center min-w-[40px] min-h-[40px]"
                    title="{{ __('storefront.add_to_cart') }}">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">{{ __('storefront.btn_view') }}</a>
            @endif
        </div>
    </div>
</div>
