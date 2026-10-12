{{--
    Portfolio & Creative Hero Section (`portfolio_creative`)
    Editorial modern studio aesthetic for agencies, designers, photographers & creative studios.
--}}
<section class="relative overflow-hidden bg-[#F8FAFC] dark:bg-[#09090B] border-b border-slate-200/80 dark:border-zinc-800 transition-colors">
    {{-- Electric Violet Ambient Light --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-violet-600/10 dark:bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-fuchsia-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Creative Manifesto --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-violet-400 dark:bg-violet-600 animate-ping"></span>
                    <span class="tracking-widest uppercase text-[10px] font-extrabold">Creative Studio &amp; Agency</span>
                </div>

                <h1 class="font-heading font-black text-3xl sm:text-5xl lg:text-6xl text-zinc-950 dark:text-white tracking-tight leading-[1.08]">
                    {{ $landingPage->headline ?: 'Menciptakan Karya Visual & Brand yang Memikat' }}
                </h1>

                <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Kami memadukan strategi konseptual, tipografi berani, dan produksi visual berstandar tinggi untuk membangun identitas brand yang tak terlupakan.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-violet-500"></i>
                        <span>100% Hak Cipta &amp; Source Files</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-violet-500"></i>
                        <span>Award-Winning Design Standard</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>SLA Timeline Terikat</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-bold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="grid" class="w-4 h-4"></i>
                            <span>Eksplorasi Karya &amp; Paket</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Diskusikan Brief Proyek</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Case Study Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-zinc-950 via-zinc-900 to-violet-950 text-white">
                                <div class="w-16 h-16 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="palette" class="w-8 h-8 text-violet-400"></i>
                                </div>
                                <h3 class="font-heading font-black text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-zinc-400 max-w-xs">Portfolio kreasi visual, branding identitas, dan solusi digital kreatif.</p>
                            </div>
                        @endif

                        {{-- Floating Case Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-zinc-950/90 text-white backdrop-blur-md border border-zinc-800 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-violet-600/30 flex items-center justify-center text-violet-400">
                                    <i data-lucide="award" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs block">Studio Reel {{ date('Y') }}</span>
                                    <span class="text-[10px] text-zinc-400">Branding, UI &amp; Visual Direction</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-violet-400">Lihat &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
