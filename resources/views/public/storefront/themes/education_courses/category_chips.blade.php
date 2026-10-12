{{--
    Education & Courses Category Chips (`education_courses`)
    Academic category filters for bootcamp, regular class, certifications, and masterclasses.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
                    Pilihan Bidang &amp; Kategori Kelas
                </h2>
                <p class="text-xs text-slate-500 dark:text-indigo-200/70">Pilih peminatan keahlian sesuai tujuan belajar atau peningkatan karir Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:underline flex items-center gap-1">
                <span>Semua Program</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition border border-indigo-200 dark:border-indigo-900/60 bg-white dark:bg-[#131D31] text-slate-800 dark:text-indigo-100 hover:border-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 shadow-2xs">
                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
