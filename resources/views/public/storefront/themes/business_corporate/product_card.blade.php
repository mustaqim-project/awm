{{--
    Business Corporate Product & Spec Card (`business_corporate`)
    Sharp, specification-oriented card with SKU badge, tiered pricing, and direct RFQ/Cart button.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs hover:shadow-lg transition-all duration-300">
    {{-- Thumbnail with Corporate Spec Badge --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-slate-400">
                <i data-lucide="layers" class="w-10 h-10 stroke-1"></i>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-900/80 text-white backdrop-blur-xs">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-mono bg-blue-600 text-white shadow-xs">
            SKU #{{ substr(md5((string)$item->id), 0, 6) }}
        </span>
    </a>

    {{-- Content --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition line-clamp-2 leading-snug mb-1.5">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Spec / Price Row --}}
        <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
            <div class="flex flex-col">
                <span class="text-[10px] font-mono text-slate-400 uppercase">Tarif Pengadaan</span>
                <span class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi RFQ' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-700 dark:text-slate-200 transition active:scale-95 shadow-2xs flex items-center justify-center min-w-[40px] min-h-[40px]"
                    title="{{ __('storefront.add_to_procurement') }}">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition">
                    Rincian
                </a>
            @endif
        </div>
    </div>
</div>
