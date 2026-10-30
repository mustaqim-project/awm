{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE CATEGORY CHIPS (`bakery_cake`)                --}}
{{-- Patisserie display case category pills with warm amber accents             --}}
{{-- ========================================================================= --}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-4 sm:mb-5">
            <div>
                <div class="inline-flex items-center gap-1.5 text-amber-800 dark:text-amber-400 font-bold text-[10px] uppercase tracking-widest mb-1">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    <span>Etalase Roti &amp; Patisserie</span>
                </div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-neutral-900 dark:text-amber-50 leading-tight">
                    Pilihan Roti Artisan, Pastry &amp; Celebration Cake
                </h2>
                <p class="text-xs text-neutral-600 dark:text-amber-200/70 mt-1">
                    Jelajahi sourdough renyah, croissant mentega, kue tart ulang tahun, dan dessert lembut favorit Anda
                </p>
            </div>

            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200 transition shrink-0 group">
                <span>Seluruh Etalase Toko</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        {{-- Scrollable Category Pills with Ergonomic Tap Targets (min 44px) --}}
        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            {{-- All / Semua Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition border min-h-[44px] {{ !request()->filled('category') ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white dark:bg-[#221810] text-neutral-700 dark:text-amber-100 border-amber-900/10 dark:border-amber-500/15 hover:border-amber-500' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>{{ __('storefront.semua_etalase') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold transition border min-h-[44px] {{ $isActive ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white dark:bg-[#221810] text-neutral-800 dark:text-amber-100 border-amber-900/10 dark:border-amber-500/15 hover:border-amber-500 hover:text-amber-700 dark:hover:text-amber-300' }}">
                    <i data-lucide="cake-slice" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums {{ $isActive ? 'bg-white/20 text-white' : 'bg-amber-900/5 dark:bg-amber-400/10 text-amber-800 dark:text-amber-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
