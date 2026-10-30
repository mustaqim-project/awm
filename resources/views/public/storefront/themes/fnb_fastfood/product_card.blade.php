{{--
    Fast Food Product Card (`fnb_fastfood` / `neon_crunch`)
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#181E2A] border-2 border-red-100 dark:border-white/10 shadow-sm hover:shadow-2xl transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-red-50/50 dark:bg-red-950/20 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-red-400">
                <i data-lucide="sandwich" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider uppercase shadow-md bg-red-600 text-white flex items-center gap-1">
            <i data-lucide="flame" class="w-3 h-3"></i>
            Hot &amp; Crispy
        </span>

        @if ($item->is_preorder)
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded-lg text-[10px] font-black bg-amber-500 text-white shadow-sm">
                PO
            </span>
        @endif
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[11px] font-black text-red-600 uppercase tracking-wider mb-1 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-base text-neutral-900 dark:text-white group-hover:text-red-600 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t-2 border-red-50 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-bold uppercase">Harga</span>
                <span class="font-black text-base text-red-600 dark:text-red-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Toko' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2.5 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs transition active:scale-95 shadow-md flex items-center gap-1.5"
                    title="Tambah Menu">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Beli</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ __('storefront.btn_detail') }}</a>
            @endif
        </div>
    </div>
</div>
