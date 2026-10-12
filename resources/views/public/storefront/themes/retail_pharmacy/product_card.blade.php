{{--
    Apotek, Farmasi & Alkes Product Card (`retail_pharmacy` / `clinical_pure`)
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
    $isPrescription = str_contains(strtolower($item->name . ' ' . $item->description), 'resep') || str_contains(strtolower($item->name), 'antibiotik');
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#181E2A] border border-teal-100 dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-teal-50/50 dark:bg-teal-950/20 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-teal-400">
                <i data-lucide="pill" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        @if ($isPrescription)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider shadow-sm backdrop-blur-md bg-red-600/90 text-white flex items-center gap-1">
                <i data-lucide="file-text" class="w-3 h-3"></i>
                Resep Dokter
            </span>
        @else
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider shadow-sm backdrop-blur-md bg-teal-700/90 text-white flex items-center gap-1">
                <i data-lucide="check" class="w-3 h-3"></i>
                Bebas Terbatas
            </span>
        @endif
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[11px] font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider mb-1 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-neutral-900 dark:text-white group-hover:text-teal-700 dark:group-hover:text-teal-400 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-teal-100 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-medium">Harga Resmi HET</span>
                <span class="font-bold text-sm sm:text-base text-teal-800 dark:text-teal-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Apotek' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition active:scale-95 shadow-sm min-w-[42px] min-h-[42px] flex items-center justify-center"
                    title="Beli Produk">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
