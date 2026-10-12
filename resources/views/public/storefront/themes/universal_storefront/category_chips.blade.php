{{--
    Universal Storefront Category Navigation (`universal_storefront` / `universal_clean`)
    Adaptive category chips for any industry.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                    Kategori Pilihan
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Pilih kategori untuk memfilter etalase belanja
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-theme-primary">
                <span>Lihat Semua</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @foreach ($productCategories->take(8) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-medium transition-all duration-200 hover:shadow-md hover:scale-[1.02] bg-white dark:bg-neutral-800/90 border border-black/5 dark:border-white/10 hover:border-theme-primary/40 text-neutral-800 dark:text-neutral-200 min-h-[44px]">
                    <div class="w-7 h-7 rounded-lg theme-badge flex items-center justify-center shrink-0">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
