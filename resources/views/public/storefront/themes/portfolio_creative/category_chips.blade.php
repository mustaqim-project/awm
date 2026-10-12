{{--
    Portfolio & Creative Category Chips (`portfolio_creative`)
    Editorial discipline filters for creative studio case studies and service packages.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-black text-xl sm:text-2xl text-zinc-950 dark:text-white">
                    Disiplin Layanan &amp; Karya
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Pilih rumpun proyek kreatif yang ingin Anda telusuri</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-1">
                <span>Seluruh Portofolio</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 hover:border-violet-500 hover:text-violet-600 dark:hover:text-violet-400 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
