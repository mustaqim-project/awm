{{--
    Portfolio & Creative Project Card (`portfolio_creative`)
    Editorial project & package card with 16:10 visual ratio, deliverable tag, and booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-2xs hover:shadow-xl hover:border-violet-300 dark:hover:border-violet-800 transition-all duration-300">
    {{-- Project Showcase Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800/80 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                <i data-lucide="palette" class="w-10 h-10 stroke-1 mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Studio Work</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-zinc-950/90 text-white shadow-xs backdrop-blur-xs">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded text-[10px] font-mono bg-violet-600 text-white shadow-xs">
            Studio Pack
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-base text-zinc-950 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Retainer / Price & CTA --}}
        <div class="mt-auto pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono text-zinc-400 uppercase block">Tarif Layanan / Paket</span>
                <span class="font-extrabold text-base text-zinc-950 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Studio' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 hover:bg-violet-600 dark:hover:bg-violet-600 dark:hover:text-white text-xs font-bold transition flex items-center gap-1.5 active:scale-95"
                    title="Pesan Paket">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Order</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-xl bg-violet-600 text-white text-xs font-bold hover:bg-violet-700 transition">
                    Lihat
                </a>
            @endif
        </div>
    </div>
</div>
