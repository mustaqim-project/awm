{{--
    Logistics & Transportation Fleet Card (`logistics_transportation`)
    Industrial cargo & fleet vehicle card with tonnage capacity badge, GPS badge, tariff, and booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-[#131B26] border border-amber-200/80 dark:border-amber-950/70 shadow-2xs hover:shadow-xl hover:border-amber-400 dark:hover:border-amber-700 transition-all duration-300">
    {{-- Fleet Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-video overflow-hidden bg-amber-500/10 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-amber-500 dark:text-slate-600">
                <i data-lucide="truck" class="w-12 h-12 stroke-1 mb-1 text-amber-500"></i>
                <span class="text-[10px] uppercase font-black tracking-wider">Unit Armada Truk</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-white/95 dark:bg-slate-950/95 text-slate-950 dark:text-amber-300 shadow-xs backdrop-blur-xs border border-amber-300/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase bg-amber-500 text-slate-950 shadow-xs flex items-center gap-1">
            <i data-lucide="scan-line" class="w-3 h-3"></i>
            <span>GPS Aktif</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-base sm:text-lg text-slate-900 dark:text-white uppercase group-hover:text-amber-600 dark:group-hover:text-amber-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Tariff & Booking CTA --}}
        <div class="mt-auto pt-3 border-t border-amber-200/80 dark:border-amber-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-amber-400/60 block font-bold uppercase">Estimasi Tarif / Trip</span>
                <span class="font-heading font-black text-base text-amber-700 dark:text-amber-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Dispatcher' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black uppercase tracking-wider shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pesan Sewa Armada">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Sewa</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 text-xs font-bold uppercase hover:bg-amber-100 transition">
                    Spek
                </a>
            @endif
        </div>
    </div>
</div>
