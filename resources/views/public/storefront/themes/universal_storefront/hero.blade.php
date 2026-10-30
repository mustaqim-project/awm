{{--
    Universal Storefront Hero Section (`universal_storefront` / `universal_clean`)
    Apple HIG Bento Modern Architecture, adaptive to any business sector.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Hero Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold theme-badge shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Toko Resmi & Terverifikasi' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: $business->name }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Temukan produk & layanan terbaik kami dengan kualitas terjamin, pemesanan mudah, dan transaksi aman langsung dari outlet resmi.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold theme-btn-primary shadow-md flex items-center gap-2 min-h-[48px]">
                            <span>{{ $landingPage->cta_primary_text ?: 'Jelajahi Katalog' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-white border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 shadow-sm transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Tanya CS WhatsApp</span>
                        </a>
                    @endif

                    <button type="button" @click="$dispatch('open-order-track-modal')"
                        class="px-4 py-3.5 text-xs sm:text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:text-theme-primary transition flex items-center gap-1.5 min-h-[48px]">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>{{ __('storefront.order_tracking') }}</span>
                    </button>
                </div>
            </div>

            {{-- Hero Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-black/5 dark:border-white/10 aspect-[4/3] sm:aspect-square bg-neutral-100 dark:bg-neutral-800">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        {{-- Floating Status Pill --}}
                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 dark:border-black/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="font-semibold text-neutral-900 dark:text-white">Outlet Resmi &amp; Siap Melayani</span>
                            </div>
                            <span class="text-neutral-500 font-mono text-[11px]">100% Terverifikasi</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
