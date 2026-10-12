{{--
    Laundry Kiloan & Satuan Product Card (`service_laundry` / `aqua_bubble`)
    Turnaround speed badge, per-kg/pcs indicator, and quick add to cart.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
    $unit = $item->unit ?: (str_contains(strtolower($item->name), 'kilo') ? 'kg' : 'pcs');
    $isExpress = str_contains(strtolower($item->name), 'express') || str_contains(strtolower($item->name), 'kilat');
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#181E2A] border border-sky-100 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    {{-- Thumbnail with Turnaround & Hygiene Badge --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-sky-50 dark:bg-sky-950/20 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-sky-400">
                <i data-lucide="washing-machine" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        {{-- Speed Badge --}}
        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider shadow-sm backdrop-blur-md {{ $isExpress ? 'bg-amber-500 text-white' : 'bg-sky-600/90 text-white' }}">
            <i data-lucide="{{ $isExpress ? 'zap' : 'clock' }}" class="w-3 h-3 inline-block mr-0.5"></i>
            {{ $isExpress ? 'Express 3 Jam' : 'Reguler 1-2 Hari' }}
        </span>

        @if ($item->category)
            <span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg text-[10px] font-medium bg-black/60 text-white backdrop-blur-sm">
                {{ $item->category->name }}
            </span>
        @endif
    </a>

    {{-- Details --}}
    <div class="p-4 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-neutral-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
            {{ $item->description ?: 'Pencucian higienis dengan deterjen premium, pewangi tahan lama, dan setrika uap rapi.' }}
        </p>

        {{-- Pricing & Action --}}
        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-sky-100 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-medium">Tarif Cuci</span>
                <span class="font-bold text-sm sm:text-base text-sky-700 dark:text-sky-300 font-mono">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya CS' }}
                    <span class="text-[11px] font-normal text-neutral-400">/{{ $unit }}</span>
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white transition active:scale-95 shadow-sm min-w-[42px] min-h-[42px] flex items-center justify-center"
                    title="Pesan Layanan">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 text-xs font-semibold">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
