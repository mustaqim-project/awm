{{--
    Hospitality & Accommodation Room/Villa Card (`hospitality_accommodation`)
    Bespoke resort accommodation card with guest capacity indicator, scenic amenities, and direct booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#0C1D24] border border-sky-100 dark:border-sky-950/70 shadow-2xs hover:shadow-xl hover:border-sky-400 dark:hover:border-sky-800 transition-all duration-300">
    {{-- Room / Villa Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-sky-50/50 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-sky-300 dark:text-slate-600">
                <i data-lucide="bed-double" class="w-12 h-12 stroke-1 mb-1 text-sky-400"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Akomodasi</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-slate-950/95 text-sky-800 dark:text-sky-300 shadow-xs backdrop-blur-xs border border-sky-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-600 text-white shadow-xs flex items-center gap-1">
            <i data-lucide="users" class="w-3 h-3"></i>
            <span>Max 2-4 Tamu</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-sky-50 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-sky-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Booking CTA --}}
        <div class="mt-auto pt-3 border-t border-sky-100 dark:border-sky-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-sky-400/60 block font-medium">Tarif / Malam</span>
                <span class="font-extrabold text-base text-sky-800 dark:text-sky-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Reservasi Langsung' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-full bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Reservasi Kamar">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Booking</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-full bg-sky-50 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 text-xs font-semibold hover:bg-sky-100 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
