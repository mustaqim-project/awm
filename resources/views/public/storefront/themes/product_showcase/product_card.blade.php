{{--
    Product Showcase Card (`product_showcase`)
    Boutique luxury card with refined portrait ratio, formula tag, and instant cart action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#140F12] border border-rose-200/70 dark:border-rose-950/60 shadow-sm hover:shadow-xl hover:border-rose-300 dark:hover:border-rose-800 transition-all duration-300">
    {{-- Thumbnail --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/5] overflow-hidden bg-rose-50/40 dark:bg-zinc-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-700" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-rose-300 dark:text-rose-900/60">
                <i data-lucide="gem" class="w-12 h-12 stroke-1 mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-widest">Pure Formula</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-zinc-950/95 text-rose-800 dark:text-rose-300 shadow-xs backdrop-blur-xs border border-rose-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-500 text-white shadow-xs">
            Signature
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base text-slate-900 dark:text-rose-50 group-hover:text-rose-700 dark:group-hover:text-rose-300 transition line-clamp-2 leading-snug mb-1.5">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-rose-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Price & Cart CTA --}}
        <div class="mt-auto pt-3 border-t border-rose-100 dark:border-rose-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-rose-400/60 block uppercase tracking-wider">Harga Resmi</span>
                <span class="font-extrabold text-base text-rose-800 dark:text-rose-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Konsultasi Harga' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2.5 rounded-2xl bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="{{ __('storefront.buy_this_product') }}">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Pesan</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-xs font-semibold hover:bg-rose-100 transition">{{ __('storefront.btn_view') }}</a>
            @endif
        </div>
    </div>
</div>
