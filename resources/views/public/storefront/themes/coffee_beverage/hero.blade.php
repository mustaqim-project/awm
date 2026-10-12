{{--
    Coffee & Beverage Hero Section (`coffee_beverage`)
    Artisan specialty coffee & cafe roastery with tasting notes, roast level badges, and barista recommendations.
--}}
<section class="relative overflow-hidden bg-[#FAF7F2] dark:bg-[#15110E] border-b border-[#E8DEC8] dark:border-[#2C211A] transition-colors">
    {{-- Warm Aroma Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-[#8B5A2B]/10 dark:bg-[#C88A58]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-[#C88A58]/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Roastery Details --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#8B5A2B]/10 dark:bg-[#8B5A2B]/20 border border-[#8B5A2B]/20 text-xs font-semibold text-[#8B5A2B] dark:text-[#C88A58]">
                    <i data-lucide="coffee" class="w-3.5 h-3.5"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Specialty Roastery &bull; Fresh Roasted Weekly</span>
                </div>

                <h1 class="font-heading font-bold text-3xl sm:text-5xl lg:text-6xl text-[#3D2B1F] dark:text-[#FAF7F2] tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Ritual Kopi Spesialti, Sangrai Presisi Setiap Cangkir' }}
                </h1>

                <p class="text-sm sm:text-base text-[#6B5744] dark:text-[#CBBDB1] max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Dari biji kopi pilihan petani nusantara, disangrai dengan profil optimal dan diseduh oleh barista penuh dedikasi untuk rasa terbaik.' }}
                </p>

                {{-- Tasting Notes Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i>
                        <span>Caramel &amp; Dark Chocolate</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="flame" class="w-3 h-3 text-rose-500"></i>
                        <span>Medium Roast Balanced</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                        <span>100% Arabica &amp; Robusta Origin</span>
                    </span>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 rounded-full bg-[#8B5A2B] hover:bg-[#724822] text-white font-semibold text-sm shadow-md shadow-[#8B5A2B]/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="coffee" class="w-4 h-4"></i>
                            <span>Pilih Menu Kopi</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-full bg-white dark:bg-[#201914] hover:bg-[#F2ECE3] dark:hover:bg-[#2C231C] text-[#3D2B1F] dark:text-[#FAF7F2] border border-[#E8DEC8] dark:border-[#382B21] font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Pesan Meja / Beans</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Hero Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-[#E8DEC8] dark:border-[#2C211A] bg-white dark:bg-[#201914]">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-[#3D2B1F] via-[#2A1D15] to-[#15110E] text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="coffee" class="w-8 h-8 text-[#C88A58]"></i>
                                </div>
                                <h3 class="font-heading font-bold text-2xl text-[#FAF7F2] mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-[#CBBDB1] max-w-xs">Pengalaman minum kopi spesialti dan nongkrong nyaman bersama kawan.</p>
                            </div>
                        @endif

                        {{-- Floating Roast Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-[#15110E]/95 backdrop-blur-md border border-[#E8DEC8] dark:border-[#2C211A] shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#8B5A2B]/15 flex items-center justify-center text-[#8B5A2B] dark:text-[#C88A58]">
                                    <i data-lucide="cup-soda" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-[#3D2B1F] dark:text-white block">Fresh Brewed Today</span>
                                    <span class="text-[10px] text-stone-500">Espresso base, filter &amp; artisan tea</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-[#8B5A2B] dark:text-[#C88A58]">Lihat Menu &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
