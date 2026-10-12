{{--
    Coffee & Beverage Product Card (`coffee_beverage`)
    Artisan coffee item card with roast note tag, cup price, and instant order action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#1D1713] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs hover:shadow-xl hover:border-[#C88A58] dark:hover:border-[#8B5A2B] transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-[#FAF7F2] dark:bg-[#15110E] block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-[#C88A58] dark:text-[#8B5A2B]/60">
                <i data-lucide="coffee" class="w-12 h-12 stroke-1 mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Artisan Brew</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-[#15110E]/95 text-[#8B5A2B] dark:text-[#C88A58] shadow-xs backdrop-blur-xs border border-[#E8DEC8]">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-[#8B5A2B] text-white shadow-xs">
            Roastery
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base sm:text-lg text-[#3D2B1F] dark:text-[#FAF7F2] group-hover:text-[#8B5A2B] dark:group-hover:text-[#C88A58] transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-[#6B5744] dark:text-[#CBBDB1] line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Cart Row --}}
        <div class="mt-auto pt-3 border-t border-[#F2ECE3] dark:border-[#2C211A] flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-stone-400 block font-medium">Per Cup / Pack</span>
                <span class="font-extrabold text-base text-[#8B5A2B] dark:text-[#C88A58] tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Barista' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-full bg-[#8B5A2B] hover:bg-[#724822] text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pesan Sekarang">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Pesan</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-full bg-[#FAF7F2] dark:bg-[#15110E] text-[#8B5A2B] dark:text-[#C88A58] text-xs font-semibold hover:bg-[#F2ECE3] transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
