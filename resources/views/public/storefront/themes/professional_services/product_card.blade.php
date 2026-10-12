{{--
    Professional Services Package Card (`professional_services`)
    Formal advisory package card with engagement scope, certified practice badge, and proposal CTA.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs hover:shadow-lg hover:border-amber-400 dark:hover:border-amber-600 transition-all duration-300">
    {{-- Thumbnail / Visual Badge --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                <i data-lucide="scale" class="w-10 h-10 stroke-1 mb-1"></i>
                <span class="text-[10px] font-mono uppercase tracking-wider">Advisory Practice</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-900/90 text-white backdrop-blur-xs">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-mono bg-amber-600 text-white shadow-xs">
            Resmi
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition line-clamp-1 leading-snug mb-1.5">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Retainer / Fee Row --}}
        <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono text-slate-400 uppercase block">Tarif Retainer / Sesi</span>
                <span class="font-extrabold text-base text-slate-900 dark:text-white tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Hubungi RFQ' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-amber-600 text-white hover:bg-slate-800 dark:hover:bg-amber-700 text-xs font-semibold transition flex items-center gap-1.5 active:scale-95"
                    title="Pilih Penugasan">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Pilih</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-amber-50 transition">
                    Rincian
                </a>
            @endif
        </div>
    </div>
</div>
