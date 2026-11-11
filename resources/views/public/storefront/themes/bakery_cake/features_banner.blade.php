{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE FEATURES BENTO (`bakery_cake`)                --}}
{{-- Asymmetric Culinary Craftsmanship Bento: Dynamic Universal Values from CMS--}}
{{-- ========================================================================= --}}
@php
    $pillars = $landingPage->getUniversalValues();
    $p1 = $pillars[0] ?? ['title' => 'Resep Fermentasi Alami', 'desc' => 'Dibuat dengan ragi alami dan fermentasi lambat untuk tekstur lembut dan aroma khas.', 'icon' => 'sparkles'];
    $p2 = $pillars[1] ?? ['title' => '100% Mentega Murni', 'desc' => 'Tanpa lemak trans atau pengawet buatan, hanya bahan premium pilihan.', 'icon' => 'shield-check'];
    $p3 = $pillars[2] ?? ['title' => 'Custom Celebration Cake', 'desc' => 'Desain kue ulang tahun dan perayaan custom sesuai impian Anda.', 'icon' => 'cake'];
    $p4 = $pillars[3] ?? ['title' => 'Kemasan Anti-Guncang', 'desc' => 'Kotak eksklusif menjaga bentuk kue tetap sempurna hingga ke meja Anda.', 'icon' => 'package'];
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="space-y-4">
        {{-- Section Eyebrow Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-amber-800 dark:text-amber-400 block mb-1">
                    Dedikasi &amp; Standar Rasa
                </span>
                <h2 class="font-heading text-2xl sm:text-3xl text-stone-900 dark:text-stone-100 font-normal">
                    Rahasia di Balik Kelezatan Setiap Gigitan
                </h2>
            </div>
            <span class="text-xs text-stone-500 dark:text-stone-400 hidden sm:block max-w-xs text-right">
                Komitmen mutu dari dapur pembuat roti dan kue untuk keluarga Anda.
            </span>
        </div>

        {{-- Asymmetric Bento Grid (4 Distinct Tiles) --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-2">

            {{-- Tile 1: Primary Featured Pillar (md:col-span-7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1D1714] border border-stone-200/80 dark:border-stone-800/80 shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-6 group hover:border-amber-700/30 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-700/10 text-amber-800 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-700/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ $p1['icon'] ?? 'sparkles' }}" class="w-6 h-6"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-900/10">
                        Filosofi Utama
                    </span>
                </div>
                <div class="space-y-2">
                    <h3 class="font-heading text-xl sm:text-2xl text-stone-900 dark:text-stone-100 font-normal leading-snug">
                        {{ $p1['title'] }}
                    </h3>
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300/80 leading-relaxed max-w-lg">
                        {{ $p1['desc'] }}
                    </p>
                </div>
            </div>

            {{-- Tile 2: Pure Ingredients Standard (md:col-span-5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1D1714] border border-stone-200/80 dark:border-stone-800/80 shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-6 group hover:border-amber-700/30 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 flex items-center justify-center shrink-0 border border-stone-200 dark:border-stone-700 group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ $p2['icon'] ?? 'shield-check' }}" class="w-6 h-6 text-amber-700 dark:text-amber-400"></i>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-stone-400 uppercase tracking-widest">
                        01 / Bahan
                    </span>
                </div>
                <div class="space-y-1.5">
                    <h3 class="font-heading text-lg sm:text-xl text-stone-900 dark:text-stone-100 font-normal leading-snug">
                        {{ $p2['title'] }}
                    </h3>
                    <p class="text-xs text-stone-600 dark:text-stone-300/80 leading-relaxed">
                        {{ $p2['desc'] }}
                    </p>
                </div>
            </div>

            {{-- Tile 3: Custom Cake & Celebration (md:col-span-5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1D1714] border border-stone-200/80 dark:border-stone-800/80 shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-6 group hover:border-amber-700/30 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 flex items-center justify-center shrink-0 border border-stone-200 dark:border-stone-700 group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ $p3['icon'] ?? 'cake' }}" class="w-6 h-6 text-rose-700 dark:text-rose-400"></i>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-stone-400 uppercase tracking-widest">
                        02 / Kreasi
                    </span>
                </div>
                <div class="space-y-1.5">
                    <h3 class="font-heading text-lg sm:text-xl text-stone-900 dark:text-stone-100 font-normal leading-snug">
                        {{ $p3['title'] }}
                    </h3>
                    <p class="text-xs text-stone-600 dark:text-stone-300/80 leading-relaxed">
                        {{ $p3['desc'] }}
                    </p>
                </div>
            </div>

            {{-- Tile 4: Secure Delivery Packaging (md:col-span-7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1D1714] border border-stone-200/80 dark:border-stone-800/80 shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-6 group hover:border-amber-700/30 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-700/10 text-amber-800 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-700/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ $p4['icon'] ?? 'package' }}" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-stone-400 uppercase tracking-widest">
                        03 / Antaran
                    </span>
                </div>
                <div class="space-y-1.5">
                    <h3 class="font-heading text-lg sm:text-xl text-stone-900 dark:text-stone-100 font-normal leading-snug">
                        {{ $p4['title'] }}
                    </h3>
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300/80 leading-relaxed max-w-lg">
                        {{ $p4['desc'] }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
