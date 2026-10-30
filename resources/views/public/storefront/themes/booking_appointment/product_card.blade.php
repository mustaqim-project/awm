{{-- ========================================================================= --}}
{{-- PRODUCT / SERVICE CARD: Booking & Appointment                             --}}
{{-- ========================================================================= --}}
@props([
    'product' => null,
    'item' => null,
    'business' => null,
    'theme' => null,
])

@php
    $item = $product ?? $item;
    if (!$item) return;

    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $price = (float) ($item->price ?? $item->selling_price ?? 0);
    $hasPrice = ($item->show_price_on_web ?? true) && $price > 0;
    
    // Auto-detect estimated duration from description or name if mentioned
    $descLower = strtolower($item->description ?? '');
    $duration = '45 - 60 Menit';
    if (preg_match('/(\d+)\s*(menit|jam|hours|mins)/i', $item->name . ' ' . $descLower, $matches)) {
        $duration = $matches[1] . ' ' . ucfirst($matches[2]);
    }
@endphp

<div class="group flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-[#161224] border border-purple-200/80 dark:border-purple-900/60 shadow-xs hover:shadow-xl hover:border-purple-400 dark:hover:border-purple-700 transition-all duration-300">
    
    {{-- Service Visual Header --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden bg-purple-100/50 dark:bg-purple-950/40 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-purple-400 dark:text-purple-600 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-[#161224] dark:to-[#1D172E]">
                <i data-lucide="calendar" class="w-12 h-12 stroke-[1.2] mb-1"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Sesi Reservasi</span>
            </div>
        @endif

        {{-- Category Pill --}}
        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-bold bg-white/95 dark:bg-[#0E0B17]/95 text-purple-900 dark:text-purple-200 shadow-xs backdrop-blur-xs border border-purple-200/70 dark:border-purple-900/60">
                {{ $item->category->name }}
            </span>
        @endif

        {{-- Duration Pill --}}
        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-purple-700/90 text-white shadow-xs backdrop-blur-xs flex items-center gap-1">
            <i data-lucide="clock" class="w-3 h-3"></i>
            <span>{{ $duration }}</span>
        </span>
    </a>

    {{-- Card Content --}}
    <div class="p-5 flex flex-col flex-1">
        
        {{-- Title --}}
        <a href="{{ $pdpUrl }}"
            class="font-heading font-extrabold text-base text-slate-900 dark:text-purple-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        {{-- Description --}}
        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-purple-300/70 line-clamp-2 leading-relaxed mb-3">
                {{ $item->description }}
            </p>
        @else
            <p class="text-xs text-slate-400 dark:text-purple-300/50 mb-3 italic">
                Sesi layanan terencana dengan jadwal on-time dan praktisi ahli.
            </p>
        @endif

        {{-- Service Features Pill --}}
        <div class="flex items-center gap-2 mb-4 text-[10.5px] text-purple-700 dark:text-purple-300 font-medium">
            <span class="inline-flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                <span>Tanpa Antrean</span>
            </span>
            <span>&bull;</span>
            <span class="inline-flex items-center gap-1">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-500"></i>
                <span>Alat Steril</span>
            </span>
        </div>

        {{-- Price & Booking CTA Action --}}
        <div class="mt-auto pt-3.5 border-t border-purple-100 dark:border-purple-900/50 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-purple-300/60 block font-medium">
                    Tarif / Sesi Kunjungan
                </span>
                <span class="font-extrabold text-base text-purple-800 dark:text-purple-300 tabular-nums font-mono">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Jadwal' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}', duration: '{{ $duration }}' }, 1)"
                    class="min-h-[40px] px-4 py-2 rounded-2xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer"
                    title="Reservasi Jadwal">
                    <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                    <span>Booking</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="min-h-[40px] px-3.5 py-2 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-800 dark:text-purple-300 text-xs font-bold hover:bg-purple-200 dark:hover:bg-purple-900 transition flex items-center gap-1">
                    <span>{{ __('storefront.lihat_slot') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>
    </div>
</div>
