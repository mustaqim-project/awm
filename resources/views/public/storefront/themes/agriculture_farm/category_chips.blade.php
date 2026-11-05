{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM CATEGORY CHIPS (`agriculture_farm`)                   --}}
{{-- Category navigation: dynamic universal categories with count pills        --}}
{{-- ========================================================================= --}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-4 sm:mb-5">
            <div>
                <div class="inline-flex items-center gap-1.5 text-emerald-800 dark:text-emerald-400 font-bold text-[10px] uppercase tracking-widest mb-1">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>{{ $activeTheme['industry'] ?? __('storefront.kategori_pilihan') }}</span>
                </div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-neutral-900 dark:text-emerald-50 leading-tight">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>

            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-theme-primary hover:underline transition shrink-0 group">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        {{-- Scrollable Category Pills with Ergonomic Tap Targets (min 44px) --}}
        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            {{-- All / Semua Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition border min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-[#07130A] text-neutral-700 dark:text-emerald-100 border-emerald-900/10 dark:border-emerald-500/15 hover:border-theme-primary' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold transition border min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-[#07130A] text-neutral-800 dark:text-emerald-100 border-emerald-900/10 dark:border-emerald-500/15 hover:border-theme-primary' }}">
                    <i data-lucide="tag" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums {{ $isActive ? 'bg-white/20 text-white' : 'bg-emerald-900/5 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
