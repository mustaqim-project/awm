{{--
    Fashion, Apparel & Butik Hero Section (`retail_fashion` / `vogue_minimalist`)
    Editorial clean lookbook, Cormorant Garamond serif, monochrome elegance, premium fabrics.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-medium tracking-widest uppercase bg-black/5 dark:bg-white/10 text-neutral-900 dark:text-neutral-100 border border-black/10 dark:border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Editorial Fashion & Haute Butik' }}</span>
                </div>

                <h1 class="font-heading font-light text-4xl sm:text-6xl lg:text-7xl text-neutral-900 dark:text-white tracking-tight leading-[1.08]">
                    {{ $landingPage->headline ?: 'Elegansi Abadi Siluet Mode Kontemporer' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Koleksi busana pilihan dengan potongan presisi, kain serat alami berstandar kurasi tinggi, dan jahitan rapi yang dirancang untuk kenyamanan sepanjang hari.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Koleksi Eksklusif Terbatas</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="scissors" class="w-3.5 h-3.5"></i>
                        <span>Tailoring &amp; Jahitan Rapi</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Garansi Tukar Ukuran</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-8 py-3.5 text-sm sm:text-base font-medium rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-md hover:opacity-90 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5">
                            <span>{{ $landingPage->cta_primary_text ?: 'Jelajahi Lookbook' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-7 py-3.5 text-sm sm:text-base font-medium rounded-full border border-black/20 dark:border-white/20 transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white hover:bg-neutral-50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Tanya Fitting &amp; Size</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-black/5 dark:border-white/10 aspect-[3/4] bg-neutral-100 dark:bg-neutral-800">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-md flex items-center justify-between">
                            <div>
                                <span class="font-bold text-neutral-900 dark:text-white block">New Season Drop</span>
                                <span class="text-[11px] text-neutral-500">Koleksi Terkini</span>
                            </div>
                            <span class="text-neutral-900 dark:text-white font-mono font-bold">Limited Run</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
