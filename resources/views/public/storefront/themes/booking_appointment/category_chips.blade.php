{{--
    Booking & Appointment Category Chips (`booking_appointment`)
    Vibrant capsule filters for booking treatments, sessions, and appointment services.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-extrabold text-lg sm:text-xl text-slate-900 dark:text-white">
                    Pilihan Kategori Sesi &amp; Layanan
                </h2>
                <p class="text-xs text-slate-500 dark:text-purple-300/70">Pilih jenis perawatan atau sesi konsultasi yang ingin Anda reservasi</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold text-purple-700 dark:text-purple-400 hover:underline flex items-center gap-1">
                <span>Seluruh Layanan</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-bold transition border border-purple-200 dark:border-purple-900/60 bg-white dark:bg-[#161224] text-slate-800 dark:text-purple-100 hover:border-purple-500 hover:text-purple-700 dark:hover:text-purple-300 shadow-2xs">
                    <i data-lucide="sparkle" class="w-3.5 h-3.5 text-purple-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
