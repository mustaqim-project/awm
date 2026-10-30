{{-- ========================================================================= --}}
{{-- CATEGORY CHIPS: Booking & Appointment (Kategori Sesi & Layanan Reservasi) --}}
{{-- ========================================================================= --}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    @php
        $selectedCatId = request()->query('category');
    @endphp
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-extrabold text-lg sm:text-xl text-slate-900 dark:text-purple-100 leading-tight">{{ __('storefront.pilihan_kategori_sesi_layanan') }}</h2>
                <p class="text-xs text-slate-500 dark:text-purple-300/70 mt-0.5">
                    Pilih jenis perawatan atau sesi konsultasi yang ingin Anda reservasi
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold text-purple-700 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 flex items-center gap-1 shrink-0 transition">
                <span>{{ __('storefront.seluruh_layanan') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Scrollable Capsule Bar --}}
        <div class="flex items-center gap-2.5 overflow-x-auto pb-2 scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0">
            {{-- "Semua Sesi" Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2 min-h-[44px] px-4.5 py-2.5 rounded-full text-xs font-bold transition-all duration-200 border {{ empty($selectedCatId) ? 'bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-600/25' : 'bg-white/80 dark:bg-[#161224] text-slate-800 dark:text-purple-200 border-purple-200/80 dark:border-purple-900/60 hover:border-purple-400 hover:text-purple-700 dark:hover:text-purple-300' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>{{ __('storefront.semua_sesi') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = (string) $selectedCatId === (string) $cat->id;
                    $catName = strtolower($cat->name);
                    $icon = match(true) {
                        str_contains($catName, 'klinik') || str_contains($catName, 'sehat') || str_contains($catName, 'medis') || str_contains($catName, 'gigi') => 'activity',
                        str_contains($catName, 'salon') || str_contains($catName, 'rambut') || str_contains($catName, 'barber') || str_contains($catName, 'spa') => 'scissors',
                        str_contains($catName, 'bengkel') || str_contains($catName, 'servis') || str_contains($catName, 'mesin') => 'wrench',
                        str_contains($catName, 'studio') || str_contains($catName, 'foto') => 'camera',
                        str_contains($catName, 'konsultasi') || str_contains($catName, 'ahli') || str_contains($catName, 'terapi') => 'user-check',
                        default => 'sparkles'
                    };
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 min-h-[44px] px-4.5 py-2.5 rounded-full text-xs font-bold transition-all duration-200 border {{ $isActive ? 'bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-600/25' : 'bg-white/80 dark:bg-[#161224] text-slate-800 dark:text-purple-200 border-purple-200/80 dark:border-purple-900/60 hover:border-purple-400 hover:text-purple-700 dark:hover:text-purple-300' }}">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-purple-600 dark:text-purple-400' }}"></i>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count) && $cat->products_count > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] tabular-nums {{ $isActive ? 'bg-white/20 text-white' : 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
