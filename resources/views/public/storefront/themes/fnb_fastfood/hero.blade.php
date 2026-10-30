{{--
    Fast Food, Burger & Snack Hero Section (`fnb_fastfood` / `neon_crunch`)
    High-energy red & yellow vibrant aesthetic, Outfit typography, combo deals & fast delivery.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black tracking-wider uppercase"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#DC262618' }}; color: {{ $activeTheme['badge_text'] ?? '#DC2626' }};">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Fast Food & Burger Spesial' }}</span>
                </div>

                <h1 class="font-heading font-black text-4xl sm:text-6xl lg:text-7xl text-neutral-900 dark:text-white tracking-tight leading-[1.05]">
                    {{ $landingPage->headline ?: 'Renyah, Juicy & Puas di Setiap Gigitan!' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-medium">
                    {{ $landingPage->subheadline ?: 'Burger daging sapi asli, ayam goreng ekstra krispi, kentang goreng renyah bumbu gurih, dan paket combo hemat siap antar panas-panas.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-300">
                        <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                        <span>Ekstra Krispi & Panas</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                        <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                        <span>Saji Kilat 15 Menit</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        <span>Promo Combo Hemat</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-8 py-4 text-base font-extrabold rounded-2xl text-white shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[50px] hover:scale-105 active:scale-95"
                            style="background: linear-gradient(135deg, #DC2626, #EA580C);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pesan Sekarang!' }}</span>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-4 text-base font-bold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[50px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-red-300 dark:border-red-800/50 hover:bg-red-50/50">
                            <i data-lucide="message-circle" class="w-5 h-5 text-emerald-500"></i>
                            <span>{{ __('storefront.order_cepat_wa') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-2 border-red-200 dark:border-white/10 aspect-square bg-red-50 dark:bg-red-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-600 animate-ping"></span>
                                <span class="font-black text-neutral-900 dark:text-white uppercase tracking-wider">Hot &amp; Freshly Made</span>
                            </div>
                            <span class="text-red-600 font-black text-sm">Diskon Spesial</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
