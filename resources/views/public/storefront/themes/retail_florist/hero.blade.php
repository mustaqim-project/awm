{{--
    Florist & Toko Bunga Hero Section (`retail_florist` / `flora_romance`)
    Botanical elegance, Bodoni Moda typography, fresh blooms, greeting cards & bouquet deliveries.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#4D7C0F15' }}; color: {{ $activeTheme['badge_text'] ?? '#4D7C0F' }};">
                    <i data-lucide="flower-2" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Artisan Florist & Bunga Segar' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Rangkai Kasih & Ungkapkan Rasa Lewat Bunga Segar' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Buket bunga segar impor & lokal premium, standing flower pernikahan, ucapan duka cita, dan hampers elegan dengan kartu ucapan custom gratis.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-lime-50 dark:bg-lime-950/40 text-lime-800 dark:text-lime-300 border border-lime-200/60 dark:border-lime-800/40">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Bunga Segar Garansi Mekar</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300 border border-pink-200/60 dark:border-pink-800/40">
                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                        <span>Gratis Kartu Ucapan Custom</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                        <span>Same-Day Delivery Siap Kirim</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, #4D7C0F, #3F6212);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Koleksi Buket' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-lime-300 dark:border-lime-800/50 hover:bg-lime-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Konsultasi Buket Custom</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-lime-100 dark:border-white/10 aspect-square bg-lime-50 dark:bg-lime-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-semibold text-neutral-900 dark:text-white">Bunga Dipetik Hari Ini</span>
                            </div>
                            <span class="text-lime-700 dark:text-lime-400 font-bold font-mono">100% Bloom</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
