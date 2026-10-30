{{--
    Restoran & Rumah Makan Hero Section (`fnb_resto` / `nusantara_feast`)
    Terracotta warm spice ambiance, DM Serif Display, dine-in & table reservation.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Text & Actions --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#991B1B15' }}; color: {{ $activeTheme['badge_text'] ?? '#991B1B' }};">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Restoran & Kuliner Nusantara' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Cita Rasa Otentik Rempah Warisan Tradisi' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Nikmati hidangan istimewa racikan bumbu segar pilihan, dimasak langsung saat dipesan dengan kehangatan suasana santap bersama keluarga.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        <span>Resep Otentik Turun-Temurun</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200/60 dark:border-red-800/40">
                        <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                        <span>Bumbu Rempah Segar Asli</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span>100% Halal Certified</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, #991B1B, #B45309);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Buka Menu Santap' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($landingPage->isPageActive('reservation'))
                        <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-amber-300 dark:border-amber-800/50 hover:bg-amber-50/50">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                            <span>{{ __('storefront.reservasi_meja') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Showcase --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-amber-100 dark:border-white/10 aspect-square bg-amber-50 dark:bg-amber-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-semibold text-neutral-900 dark:text-white">Dine-in &amp; Takeaway Buka</span>
                            </div>
                            <span class="text-amber-700 dark:text-amber-400 font-bold font-mono">Meja Tersedia</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
