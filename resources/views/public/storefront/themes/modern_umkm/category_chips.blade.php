{{--
    Modern UMKM Category Chips (`modern_umkm`)
    Friendly, accessible category pill navigation for local business offerings.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">{{ __('storefront.pilihan_produk_layanan') }}</h2>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 flex items-center gap-1 group">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-medium transition-all duration-200 border border-emerald-100 dark:border-emerald-950/60 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
