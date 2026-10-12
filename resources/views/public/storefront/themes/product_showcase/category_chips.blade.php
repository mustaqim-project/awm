{{--
    Product Showcase Category Chips (`product_showcase`)
    Elegant capsule navigation for boutique series and formulations.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-rose-50">
                    Rangkaian Seri Pilihan
                </h2>
                <p class="text-xs text-slate-500 dark:text-rose-300/70">Pilih kategori koleksi untuk menemukan solusi terbaik</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-rose-700 dark:text-rose-300 hover:underline flex items-center gap-1">
                <span>Seluruh Seri</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold transition border border-rose-200/70 dark:border-rose-900/60 bg-white dark:bg-[#140F12] text-slate-700 dark:text-rose-200 hover:border-rose-400 hover:text-rose-700 dark:hover:text-rose-300 shadow-2xs">
                    <i data-lucide="sparkle" class="w-3 h-3 text-amber-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
