{{--
    Business Corporate Hero Section (`business_corporate`)
    Formal enterprise split layout, B2B credibility metrics, direct RFQ consultation.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-14 sm:pb-20 border-b border-black/[0.06] dark:border-white/[0.06] bg-slate-50/60 dark:bg-slate-950/40">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Col 1: Corporate Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-semibold tracking-wide uppercase bg-blue-600/10 text-blue-700 dark:text-blue-400 border border-blue-600/20">
                    <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Corporate Enterprise & B2B Solutions' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: $business->name . ' - Mitra Solusi & Suplai Bisnis Terpercaya' }}
                </h1>

                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Menyediakan produk dan layanan berstandar industri dengan jaminan mutu, kontrak kerja transparan, dan keandalan operasional jangka panjang.' }}
                </p>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition flex items-center gap-2 min-h-[48px]">
                            <span>{{ $landingPage->cta_primary_text ?: 'Katalog Solusi & Produk' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="mail" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                            <span>Hubungi Account Representative</span>
                        </a>
                    @endif
                </div>

                {{-- B2B Trust Metrics Strip --}}
                <div class="pt-6 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="font-heading font-extrabold text-xl sm:text-2xl text-slate-900 dark:text-white tabular-nums">100%</div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Entitas Legal &amp; Resmi</div>
                    </div>
                    <div>
                        <div class="font-heading font-extrabold text-xl sm:text-2xl text-blue-600 dark:text-blue-400 tabular-nums">SLA 99.9%</div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Komitmen Layanan</div>
                    </div>
                    <div>
                        <div class="font-heading font-extrabold text-xl sm:text-2xl text-slate-900 dark:text-white tabular-nums">B2B &amp; Retail</div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Faktur Pajak Tersedia</div>
                    </div>
                </div>
            </div>

            {{-- Col 2: Corporate Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-800 aspect-[4/3] sm:aspect-square bg-slate-100 dark:bg-slate-900">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>

                        {{-- Corporate Verification Badge on Image --}}
                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 text-xs shadow-md flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></div>
                                <span class="font-semibold text-slate-900 dark:text-white">Operasional Aktif &amp; Terverifikasi</span>
                            </div>
                            <span class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">COOCA Verified</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
