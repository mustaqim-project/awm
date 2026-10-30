{{--
    Event & Wedding Category Chips (`event_wedding`)
    Romantic capsule filters for wedding packages, venue decoration, and event rentals.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-slate-900 dark:text-rose-50">{{ __('storefront.pilihan_paket_dekorasi') }}</h2>
                <p class="text-xs text-slate-500 dark:text-rose-200/70">Pilih tema perayaan, paket pernikahan lengkap, atau kebutuhan dekorasi</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-rose-700 dark:text-rose-300 hover:underline flex items-center gap-1">
                <span>{{ __('storefront.seluruh_paket') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition border border-rose-200 dark:border-rose-900/60 bg-white dark:bg-[#1A1216] text-slate-800 dark:text-rose-100 hover:border-rose-400 hover:text-rose-700 dark:hover:text-rose-300 shadow-2xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
