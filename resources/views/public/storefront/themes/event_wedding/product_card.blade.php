{{--
    Event & Wedding Package Card (`event_wedding`)
    Romantic celebration package card with guest capacity badge, all-in inclusions, and booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#1A1216] border border-rose-200/70 dark:border-rose-950/70 shadow-2xs hover:shadow-xl hover:border-rose-400 dark:hover:border-rose-800 transition-all duration-300">
    {{-- Package Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-rose-50/50 dark:bg-zinc-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-rose-300 dark:text-zinc-600">
                <i data-lucide="sparkles" class="w-12 h-12 stroke-1 mb-1 text-amber-400"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Paket Perayaan</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-zinc-950/95 text-rose-800 dark:text-rose-300 shadow-xs backdrop-blur-xs border border-rose-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-slate-900 shadow-xs flex items-center gap-1">
            <i data-lucide="award" class="w-3 h-3"></i>
            <span>All-In Pack</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-rose-50 group-hover:text-rose-700 dark:group-hover:text-rose-300 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-rose-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Booking CTA --}}
        <div class="mt-auto pt-3 border-t border-rose-100 dark:border-rose-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-rose-400/60 block font-medium">Investasi Paket / Event</span>
                <span class="font-extrabold text-base text-rose-800 dark:text-rose-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Konsultasi WO' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-full bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pilih Paket">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Pilih</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-xs font-semibold hover:bg-rose-100 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
