{{--
    Apotek, Farmasi & Alkes Hero Section (`retail_pharmacy` / `clinical_pure`)
    Pristine teal trust, BPOM & SIA verification, doctor prescription upload & pharmacist chat.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#0D948815' }}; color: {{ $activeTheme['badge_text'] ?? '#0D9488' }};">
                    <i data-lucide="cross" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Apotek Resmi & Alat Kesehatan Berizin' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Obat Asli, Vitamin Lengkap & Layanan Apoteker Siaga' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Tebus obat resep dokter, beli vitamin daya tahan tubuh, suplemen kesehatan, dan alat medis bersertifikat Kemenkes dengan jaminan 100% original dan kedaluwarsa panjang.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-teal-50 dark:bg-teal-950/40 text-teal-800 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/40">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>100% Terdaftar BPOM</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        <span>Tebus Resep Dokter</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Apoteker Resmi Terverifikasi</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-2xl text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #0D9488, #0F766E);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Cari Obat & Suplemen' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-teal-300 dark:border-teal-800/50 hover:bg-teal-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Kirim Foto Resep Dokter</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-teal-200 dark:border-white/10 aspect-square bg-teal-50 dark:bg-teal-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></span>
                                <span class="font-bold text-neutral-900 dark:text-white">Apotik Buka &amp; Layanan Siaga</span>
                            </div>
                            <span class="text-teal-700 dark:text-teal-400 font-bold font-mono">100% Asli</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
