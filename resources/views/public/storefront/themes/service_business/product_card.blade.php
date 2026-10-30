{{--
    Service Business Card (`service_business`)
    Clean service card with turnaround speed badge, unit/kg pricing, and instant booking action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#0D1C2A] border border-sky-100 dark:border-sky-950/70 shadow-2xs hover:shadow-xl hover:border-sky-300 dark:hover:border-sky-800 transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-sky-50/50 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-sky-300 dark:text-sky-800">
                <i data-lucide="sparkles" class="w-12 h-12 stroke-1 mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">{{ __('storefront.layanan_bersih') }}</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-slate-900/95 text-sky-800 dark:text-sky-300 shadow-xs backdrop-blur-xs border border-sky-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-600 text-white shadow-xs flex items-center gap-1">
            <i data-lucide="shield-check" class="w-3 h-3"></i>
            <span>Higienis</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-1 leading-snug mb-1">
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
                <span class="text-[10px] text-slate-400 dark:text-sky-400/60 block font-medium">Tarif / Kg / Pcs</span>
                <span class="font-extrabold text-base text-sky-700 dark:text-sky-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi Kami' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="{{ __('storefront.order_service') }}">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Order</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 text-xs font-semibold hover:bg-sky-100 transition">{{ __('storefront.btn_view') }}</a>
            @endif
        </div>
    </div>
</div>
