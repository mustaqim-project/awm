{{--
    Fashion & Butik Product Card (`retail_fashion` / `vogue_minimalist`)
    Editorial clean layout, size pill indicators, silhouette focus.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-[#181E2A] border border-black/5 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-[3/4] overflow-hidden bg-neutral-100 dark:bg-neutral-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-700 ease-out" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-neutral-300">
                <i data-lucide="shirt" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        @if ($item->is_preorder)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-sm">
                Pre-Order
            </span>
        @endif

        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="px-2 py-0.5 rounded bg-white/90 dark:bg-black/90 backdrop-blur-sm text-[10px] font-mono text-neutral-800 dark:text-neutral-200">
                S • M • L • XL
            </span>
        </div>
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[10px] font-medium text-neutral-400 uppercase tracking-widest mb-1 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-medium text-sm sm:text-base text-neutral-900 dark:text-white group-hover:underline line-clamp-2 leading-snug mb-2">
            {{ $item->name }}
        </a>

        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-black/5 dark:border-white/5">
            <span class="font-medium text-sm sm:text-base text-neutral-900 dark:text-white tabular-nums">
                {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Butik' }}
            </span>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2 rounded-full border border-black/15 dark:border-white/15 hover:bg-neutral-900 hover:text-white dark:hover:bg-white dark:hover:text-neutral-900 transition active:scale-95 min-w-[36px] min-h-[36px] flex items-center justify-center text-neutral-700 dark:text-neutral-200"
                    title="Tambah ke Tas Belanja">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="text-xs font-medium text-neutral-500 hover:underline">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
