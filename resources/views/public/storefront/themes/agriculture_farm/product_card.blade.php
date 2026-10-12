{{--
    Agriculture & Farm Harvest Card (`agriculture_farm`)
    Fresh farm-to-table product card with harvest grade, quantity indicator, and direct order action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-[#112117] border border-emerald-100 dark:border-emerald-950/70 shadow-2xs hover:shadow-xl hover:border-emerald-500 dark:hover:border-emerald-700 transition-all duration-300">
    {{-- Produce Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-emerald-50/40 dark:bg-stone-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-emerald-300 dark:text-stone-600">
                <i data-lucide="sprout" class="w-12 h-12 stroke-1 mb-1 text-emerald-500"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Hasil Panen</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-stone-950/95 text-emerald-800 dark:text-emerald-300 shadow-xs backdrop-blur-xs border border-emerald-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-700 text-white shadow-xs flex items-center gap-1">
            <i data-lucide="leaf" class="w-3 h-3 text-emerald-200"></i>
            <span>Segar Alami</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-emerald-50 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-emerald-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Cart CTA --}}
        <div class="mt-auto pt-3 border-t border-emerald-100 dark:border-emerald-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-emerald-400/60 block font-medium">Harga / Satuan</span>
                <span class="font-extrabold text-base text-emerald-800 dark:text-emerald-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Kebun' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pesan Hasil Panen">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    <span>Beli</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-semibold hover:bg-emerald-100 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
