{{--
    Health & Beauty Product / Treatment Card (`health_beauty`)
    Clean dermatological wellness card with BPOM trust tag, skin benefit preview, price, and add-to-bag action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#111C20] border border-teal-100 dark:border-teal-950/70 shadow-2xs hover:shadow-xl hover:border-teal-400 dark:hover:border-teal-700 transition-all duration-300">
    {{-- Product / Treatment Visual --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-teal-50/40 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-teal-300 dark:text-slate-600">
                <i data-lucide="sparkles" class="w-12 h-12 stroke-1 mb-1 text-teal-400"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">{{ __('storefront.perawatan_kulit') }}</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-slate-950/95 text-teal-800 dark:text-teal-300 shadow-xs backdrop-blur-xs border border-teal-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-600 text-white shadow-xs flex items-center gap-1">
            <i data-lucide="shield-check" class="w-3 h-3 text-teal-200"></i>
            <span>BPOM Resmi</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base sm:text-lg text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-teal-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Cart CTA --}}
        <div class="mt-auto pt-3 border-t border-teal-100 dark:border-teal-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-teal-400/60 block font-medium">Harga / Treatment</span>
                <span class="font-extrabold text-base text-teal-800 dark:text-teal-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Konsultasi Dokter' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-full bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="{{ __('storefront.buy_or_booking') }}">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Beli</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-full bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 text-xs font-semibold hover:bg-teal-100 transition">{{ __('storefront.btn_detail') }}</a>
            @endif
        </div>
    </div>
</div>
