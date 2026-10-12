{{--
    Food & Restaurant Dish Card (`food_restaurant`)
    Warm appetite-inviting dish card with halal badge, chef recommendation, and instant dine/order button.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#1D140F] border border-amber-200/70 dark:border-amber-950/70 shadow-2xs hover:shadow-xl hover:border-red-300 dark:hover:border-red-900/60 transition-all duration-300">
    {{-- Dish Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-amber-50/50 dark:bg-zinc-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-amber-300 dark:text-zinc-600">
                <i data-lucide="soup" class="w-12 h-12 stroke-1 mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Sajian Lezat</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-zinc-950/95 text-red-900 dark:text-amber-200 shadow-xs backdrop-blur-xs border border-amber-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-slate-900 shadow-xs flex items-center gap-1">
            <i data-lucide="flame" class="w-3 h-3"></i>
            <span>Fresh</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-normal text-lg sm:text-xl text-slate-900 dark:text-amber-50 group-hover:text-red-700 dark:group-hover:text-amber-300 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-amber-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Order Action --}}
        <div class="mt-auto pt-3 border-t border-amber-100 dark:border-amber-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-amber-300/50 block font-medium">Porsi / Menu</span>
                <span class="font-extrabold text-base text-red-800 dark:text-amber-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Harga' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-2xl bg-red-700 hover:bg-red-800 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pesan Menu">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Pesan</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-red-800 dark:text-amber-200 text-xs font-semibold hover:bg-amber-100 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
