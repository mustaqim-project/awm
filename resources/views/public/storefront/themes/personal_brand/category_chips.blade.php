{{--
    Personal Brand Category Chips (`personal_brand`)
    Advisory & creator category pills for private sessions, masterclasses, digital tools, and brand partnerships.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-zinc-900 dark:text-white">
                    Program, Sesi &amp; Karya Pilihan
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Pilih format kolaborasi, konsultasi personal, atau akses pustaka resource digital</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
                <span>Seluruh Program</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#121215] text-zinc-800 dark:text-zinc-200 hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 shadow-2xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
