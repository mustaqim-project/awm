{{--
    Bengkel & Otomotif Category Chips (`service_workshop` / `apex_velocity`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">{{ __('storefront.kategori_servis_onderdil') }}</h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Oli mesin, ban, aki, pengereman, tune-up &amp; aksesoris motor/mobil
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-orange-600 dark:text-orange-400">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                $catIcons = [
                    'oli' => 'droplet',
                    'oil' => 'droplet',
                    'pelumas' => 'droplet',
                    'ban' => 'disc',
                    'tire' => 'disc',
                    'aki' => 'battery-charging',
                    'battery' => 'battery-charging',
                    'rem' => 'shield-alert',
                    'brake' => 'shield-alert',
                    'servis' => 'wrench',
                    'tune up' => 'gauge',
                    'busi' => 'zap',
                    'filter' => 'filter',
                    'aksesoris' => 'settings',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'wrench';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] bg-white dark:bg-[#1E293B] border border-orange-100 dark:border-white/10 hover:border-orange-500 text-neutral-800 dark:text-neutral-200 min-h-[46px]">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <i data-lucide="{{ $catIcon }}" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
