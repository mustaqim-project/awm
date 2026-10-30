{{--
    Fitness & Gym Product Card (`service_fitness` / `titan_kinetic`)
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
    $isMembership = str_contains(strtolower($item->name), 'member') || str_contains(strtolower($item->name), 'bulan') || str_contains(strtolower($item->name), 'tahun');
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#262626] border border-lime-200 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-lime-500/50 transition-all duration-300">
    <a href="{{ $pdpUrl }}" class="relative aspect-square overflow-hidden bg-neutral-100 dark:bg-neutral-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-lime-500">
                <i data-lucide="dumbbell" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm backdrop-blur-md bg-lime-500 text-black flex items-center gap-1">
            <i data-lucide="{{ $isMembership ? 'credit-card' : 'zap' }}" class="w-3 h-3"></i>
            {{ $isMembership ? 'Gym Pass' : 'Suplemen & Sesi' }}
        </span>

        @if ($item->is_preorder)
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                Booking
            </span>
        @endif
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if ($item->category)
            <span class="text-[11px] font-black text-lime-600 dark:text-lime-400 uppercase tracking-wider mb-1 line-clamp-1">
                {{ $item->category->name }}
            </span>
        @endif

        <a href="{{ $pdpUrl }}"
            class="font-heading font-black text-lg text-neutral-900 dark:text-white group-hover:text-lime-600 dark:group-hover:text-lime-400 transition line-clamp-2 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @endif

        <div class="mt-auto pt-3 flex items-center justify-between gap-2 border-t border-black/5 dark:border-white/5">
            <div class="flex flex-col">
                <span class="text-[10px] text-neutral-400 font-bold uppercase">Biaya / Harga</span>
                <span class="font-black text-base text-neutral-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Gym' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-2xl bg-lime-500 hover:bg-lime-400 text-black font-black transition active:scale-95 shadow-sm min-w-[42px] min-h-[42px] flex items-center justify-center"
                    title="{{ __('storefront.register_or_buy') }}">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs font-bold text-neutral-700 dark:text-neutral-300">
                    Info
                </a>
            @endif
        </div>
    </div>
</div>
