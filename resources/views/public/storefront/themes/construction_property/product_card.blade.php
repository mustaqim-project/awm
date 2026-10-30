{{--
    Construction & Property Project Card (`construction_property`)
    Architectural project & property unit card with building specs, price per m²/unit, and consultation action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-xl overflow-hidden bg-white dark:bg-[#181613] border border-amber-200/80 dark:border-amber-950/70 shadow-2xs hover:shadow-xl hover:border-amber-400 dark:hover:border-amber-700 transition-all duration-300">
    {{-- Project Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-amber-50/40 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-amber-300 dark:text-zinc-600">
                <i data-lucide="building-2" class="w-12 h-12 stroke-1 mb-1"></i>
                <span class="text-[10px] font-mono uppercase tracking-wider">Proyek Terbangun</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-900/90 text-white backdrop-blur-xs">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-mono bg-amber-600 text-white shadow-xs">
            Spek SNI
        </span>
    </a>

    {{-- Content --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-base text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition line-clamp-1 leading-snug mb-1 uppercase">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price / RAB & Action --}}
        <div class="mt-auto pt-3 border-t border-amber-100 dark:border-amber-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono text-slate-400 uppercase block">Estimasi / m² / Unit</span>
                <span class="font-extrabold text-base text-amber-700 dark:text-amber-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Konsultasi RAB' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5 active:scale-95"
                    title="{{ __('storefront.select_budget_package') }}">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>RAB</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-bold uppercase hover:bg-amber-100 transition">
                    Rincian
                </a>
            @endif
        </div>
    </div>
</div>
