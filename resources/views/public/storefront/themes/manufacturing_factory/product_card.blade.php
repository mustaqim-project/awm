{{--
    Manufacturing & Factory Product Card (`manufacturing_factory`)
    Industrial spec sheet card with SKU, MOQ indicator, factory bulk pricing, and RFQ action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs hover:shadow-xl hover:border-orange-500 transition-all duration-300">
    {{-- Technical Spec Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                <i data-lucide="package" class="w-10 h-10 stroke-1 mb-1"></i>
                <span class="text-[10px] font-mono uppercase tracking-wider">Industrial Spec</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-mono bg-orange-600 text-white shadow-xs">
            MOQ 50+ Pcs
        </span>
    </a>

    {{-- Content --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-sm sm:text-base text-slate-950 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition line-clamp-2 leading-snug mb-1 uppercase">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Factory Price & RFQ --}}
        <div class="mt-auto pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono text-slate-400 uppercase block">Harga Pabrik / Unit</span>
                <span class="font-extrabold text-sm sm:text-base text-slate-950 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi RFQ' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-3.5 py-2 rounded bg-slate-900 dark:bg-orange-600 hover:bg-orange-600 dark:hover:bg-orange-700 text-white text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5 active:scale-95"
                    title="{{ __('storefront.add_to_rfq') }}">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>RFQ</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded bg-orange-600 text-white text-xs font-bold uppercase hover:bg-orange-700 transition">
                    Rincian
                </a>
            @endif
        </div>
    </div>
</div>
