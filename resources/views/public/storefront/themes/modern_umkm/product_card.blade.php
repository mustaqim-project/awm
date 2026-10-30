{{--
    Modern UMKM Product Card (`modern_umkm`)
    Warm, community-friendly card with rounded aesthetics, home-grown trust badge, and easy order button.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-emerald-100 dark:border-slate-800 shadow-2xs hover:shadow-xl hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-emerald-50/40 dark:bg-slate-800 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-emerald-300 dark:text-slate-600">
                <i data-lucide="package" class="w-10 h-10 stroke-1 mb-1"></i>
                <span class="text-[10px] font-medium">{{ __('storefront.produk_umkm') }}</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 dark:bg-slate-900/90 text-emerald-800 dark:text-emerald-300 shadow-xs backdrop-blur-xs border border-emerald-200/50">
                {{ $item->category->name }}
            </span>
        @endif
    </a>

    {{-- Info --}}
    <div class="p-4 sm:p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        {{-- Bottom Action Row --}}
        <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
            <div>
                <span class="text-[10px] text-slate-400 block font-medium">Harga Bersahabat</span>
                <span class="font-extrabold text-sm sm:text-base text-emerald-700 dark:text-emerald-400 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Harga' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Beli Sekarang">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Pesan</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 text-slate-700 dark:text-slate-200 text-xs font-semibold transition">{{ __('storefront.btn_view') }}</a>
            @endif
        </div>
    </div>
</div>
