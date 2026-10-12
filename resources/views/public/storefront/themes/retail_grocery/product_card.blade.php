{{--
    Minimarket & Sembako Product Card (`retail_grocery` / `fresh_mart`)
    Fast add-to-cart, unit badge (kg/ikat/dus/pcs), fresh guarantee.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
    $unit = $item->unit ?: 'pcs';
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#181E2A] border border-green-100 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-green-50/50 dark:bg-green-950/20 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-green-400">
                <i data-lucide="shopping-bag" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-600/90 text-white shadow-sm">
            Tersedia
        </span>

        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/90 dark:bg-black/90 text-neutral-800 dark:text-neutral-200">
            Per {{ $unit }}
        </span>
    </a>

    <div class="p-3.5 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[10px] font-semibold text-green-700 dark:text-green-400 uppercase tracking-wider mb-0.5 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm text-neutral-900 dark:text-white group-hover:text-green-700 dark:group-hover:text-green-400 transition line-clamp-2 leading-snug mb-2">
            {{ $item->name }}
        </a>

        <div class="mt-auto pt-2.5 flex items-center justify-between gap-2 border-t border-green-100 dark:border-white/5">
            <span class="font-extrabold text-sm sm:text-base text-green-800 dark:text-green-300 tabular-nums">
                {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Toko' }}
            </span>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2 rounded-xl bg-green-600 hover:bg-green-700 text-white transition active:scale-95 shadow-sm min-w-[38px] min-h-[38px] flex items-center justify-center"
                    title="Tambah ke Keranjang">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-xs font-semibold">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
