{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM CATEGORY CHIPS (`agriculture_farm`)                   --}}
{{-- Agrarian commodity selectors: fresh crops, certified seeds, soil nutrients--}}
{{-- ========================================================================= --}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-4 sm:mb-5">
            <div>
                <div class="inline-flex items-center gap-1.5 text-emerald-800 dark:text-emerald-400 font-bold text-[10px] uppercase tracking-widest mb-1">
                    <i data-lucide="tag" class="w-3 h-3"></i>
                    <span>Klasifikasi Komoditas</span>
                </div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-neutral-900 dark:text-emerald-50 leading-tight">
                    Pilihan Hasil Panen &amp; Sarana Tani
                </h2>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1">
                    Temukan hasil kebun segar, bibit varietas unggul, dan nutrisi tanaman terbaik
                </p>
            </div>

            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 dark:hover:text-emerald-200 transition shrink-0 group">
                <span>Lihat Semua Komoditas</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        {{-- Scrollable Category Pills with Ergonomic Tap Targets (min 44px) --}}
        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            {{-- All / Semua Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition border min-h-[44px] {{ !request()->filled('category') ? 'bg-emerald-700 text-white border-emerald-700 shadow-sm' : 'bg-white dark:bg-[#112117] text-neutral-700 dark:text-emerald-100 border-emerald-900/10 dark:border-emerald-500/15 hover:border-emerald-500' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>Semua Panen</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold transition border min-h-[44px] {{ $isActive ? 'bg-emerald-700 text-white border-emerald-700 shadow-sm' : 'bg-white dark:bg-[#112117] text-neutral-800 dark:text-emerald-100 border-emerald-900/10 dark:border-emerald-500/15 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300' }}">
                    <i data-lucide="sprout" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-emerald-900/5 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
