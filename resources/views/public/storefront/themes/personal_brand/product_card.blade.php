{{--
    Personal Brand Program / Digital Product Card (`personal_brand`)
    Editorial signature advisory card with format tag, actionable description, investment fee, and direct booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#121215] border border-zinc-200 dark:border-zinc-800/80 shadow-2xs hover:shadow-xl hover:border-cyan-500/80 dark:hover:border-cyan-500/60 transition-all duration-300">
    {{-- Program / Toolkit Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-600">
                <i data-lucide="sparkles" class="w-12 h-12 stroke-1 mb-1 text-cyan-400"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Program &amp; Sesi</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-zinc-950/95 text-zinc-900 dark:text-cyan-400 shadow-xs backdrop-blur-xs border border-zinc-200 dark:border-zinc-800">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-900 text-cyan-400 dark:bg-cyan-500 dark:text-zinc-950 shadow-xs flex items-center gap-1">
            <i data-lucide="check" class="w-3 h-3"></i>
            <span>Eksklusif</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-extrabold text-base sm:text-lg text-zinc-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Investment & Cart CTA --}}
        <div class="mt-auto pt-3 border-t border-zinc-100 dark:border-zinc-800/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 block font-medium">Investasi / Sesi</span>
                <span class="font-heading font-extrabold text-base text-zinc-900 dark:text-cyan-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Manajemen' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-full bg-zinc-900 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-900 text-xs font-bold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Pilih Program">
                    <i data-lucide="plus" class="w-3.5 h-3.5 text-cyan-400 dark:text-cyan-600"></i>
                    <span>Pilih</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-semibold hover:bg-zinc-200 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
