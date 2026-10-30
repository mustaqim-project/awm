{{--
    Professional Services Category Chips (`professional_services`)
    Formal practice area tabs for legal, tax, accounting, and advisory solutions.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                    Bidang Praktik &amp; Spesialisasi
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih bidang penugasan advisory yang dibutuhkan perusahaan Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                <span>{{ __('storefront.seluruh_layanan') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 shadow-2xs">
                    <i data-lucide="scale" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
