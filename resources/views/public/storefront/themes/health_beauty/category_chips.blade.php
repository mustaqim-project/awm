{{--
    Health & Beauty Category Chips (`health_beauty`)
    Aesthetic & dermatological category filter pills for serums, sunscreens, clinical treatments, and wellness kits.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
                    Koleksi Skincare &amp; Menu Treatment
                </h2>
                <p class="text-xs text-slate-500 dark:text-teal-200/70">Pilih formulasi aktif atau rangkaian treatment sesuai kebutuhan kondisi kulit Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-teal-700 dark:text-teal-300 hover:underline flex items-center gap-1">
                <span>Lihat Semua</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition border border-teal-200 dark:border-teal-900/60 bg-white dark:bg-[#111C20] text-slate-800 dark:text-teal-100 hover:border-teal-500 hover:text-teal-700 dark:hover:text-teal-300 shadow-2xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-teal-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
