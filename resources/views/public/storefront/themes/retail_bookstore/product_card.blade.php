{{--
    Toko Buku & Stationery Product Card (`retail_bookstore` / `bibliotheca`)
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-[#181E2A] border border-slate-200 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-[3/4] overflow-hidden bg-[#FDFBF7] dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-slate-400">
                <i data-lucide="book-open" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        <span class="absolute top-3 left-3 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900/90 text-white shadow-sm">
            Buku Ori
        </span>

        @if ($item->is_preorder)
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                Pre-Order
            </span>
        @endif
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-neutral-900 dark:text-white group-hover:text-amber-700 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-medium">Harga Buku</span>
                <span class="font-bold text-sm sm:text-base text-neutral-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Toko' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition active:scale-95 shadow-sm min-w-[40px] min-h-[40px] flex items-center justify-center"
                    title="Beli Buku">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">{{ __('storefront.btn_detail') }}</a>
            @endif
        </div>
    </div>
</div>
