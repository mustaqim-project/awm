{{--
    Fashion & Butik Category Chips (`retail_fashion` / `vogue_minimalist`)
    Editorial clean category navigation for any business sector.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-light text-2xl text-neutral-900 dark:text-white tracking-wide">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-medium flex items-center gap-1 hover:underline text-theme-primary">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-none">
            {{-- Semua Produk Pill --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-medium transition-all duration-200 min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:border-theme-primary' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php $isActive = request('category') == $cat->id; @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-medium transition-all duration-200 min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:border-theme-primary' }}">
                    <i data-lucide="tag" class="w-3.5 h-3.5 text-neutral-400"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
