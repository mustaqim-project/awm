{{--
    Fast Food Category Chips (`fnb_fastfood` / `neon_crunch`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    @php
        $catHeading = $landingPage->services_title ?: __('storefront.kategori_pilihan');
        $catSubtitle = $landingPage->services_subtitle ?: __('storefront.kategori_pilihan_sub');
    @endphp
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-black text-2xl text-neutral-900 dark:text-white uppercase tracking-tight">
                    {{ $catHeading }}
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 font-medium">
                    {{ $catSubtitle }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-bold flex items-center gap-1 hover:underline text-red-600 dark:text-red-400">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            {{-- Semua Produk Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-extrabold transition-all duration-200 hover:shadow-lg min-h-[46px] {{ !request()->filled('category') ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-[#181E2A] border-2 border-red-100 dark:border-white/10 text-neutral-900 dark:text-neutral-100 hover:border-red-500' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ !request()->filled('category') ? 'bg-white/20 text-white' : 'bg-red-100 dark:bg-red-950/60 text-red-600' }}">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-extrabold transition-all duration-200 hover:shadow-lg hover:scale-105 min-h-[46px] {{ $isActive ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-[#181E2A] border-2 border-red-100 dark:border-white/10 text-neutral-900 dark:text-neutral-100 hover:border-red-500' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-white/20 text-white' : 'bg-red-100 dark:bg-red-950/60 text-red-600' }}">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums {{ $isActive ? 'bg-white/20 text-white' : 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
