{{--
    Percetakan, Sablon & Desain Hero Section (`service_printing` / `pixel_print`)
    Creative studio purple & hot magenta, Syne typography, artwork upload & express finishing.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#7C3AED15' }}; color: {{ $activeTheme['badge_text'] ?? '#7C3AED' }};">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Percetakan Digital & Sablon Kreatif' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Wujudkan Ide Kreatif Jadi Cetakan Berkualitas Tinggi' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Cetak banner spanduk kilat, stiker label kemasan cutting presisi, brosur promosi, sablon kaos DTF, hingga merchandise kustom dengan warna tajam anti luntur.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/40">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Cetak Kilat 1 Hari Selesai</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-pink-50 dark:bg-pink-950/40 text-pink-800 dark:text-pink-300 border border-pink-200/60 dark:border-pink-800/40">
                        <i data-lucide="palette" class="w-3.5 h-3.5"></i>
                        <span>Resolusi Tinggi CMYK Akurat</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                        <span>Kirim File Siap Cetak</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-2xl text-white shadow-lg shadow-purple-500/20 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #7C3AED, #6D28D9);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Produk Cetak' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-purple-300 dark:border-purple-800/50 hover:bg-purple-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ __('storefront.kirim_file_cek_format') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1562654501-a0ccc0fc3fb1?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-purple-200 dark:border-white/10 aspect-square bg-purple-50 dark:bg-purple-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600 animate-pulse"></span>
                                <span class="font-bold text-neutral-900 dark:text-white">Mesin Cetak Aktif Beroperasi</span>
                            </div>
                            <span class="text-purple-700 dark:text-purple-400 font-bold font-mono">High Res CMYK</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
