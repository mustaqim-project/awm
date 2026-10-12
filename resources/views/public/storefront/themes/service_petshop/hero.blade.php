{{--
    Pet Shop, Klinik & Grooming Hewan Hero Section (`service_petshop` / `playful_paws`)
    Warm honey amber & mint green aesthetic, Quicksand rounded typography, grooming booking & pet food.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#D9770615' }}; color: {{ $activeTheme['badge_text'] ?? '#D97706' }};">
                    <i data-lucide="paw-print" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Pet Care, Grooming & Makanan Anabul' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Kasih Sayang Terbaik untuk Sahabat Berbulu Anda' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Makanan kucing & anjing premium bernutrisi lengkap, spa grooming wangi anti kutu, pasir gumpal higienis, vitamin serta mainan seru untuk anabul kesayangan.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                        <span>Grooming Bebas Stres</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>100% Pet Food Asli &amp; Segar</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                        <span>Antar Pasir &amp; Pakan ke Rumah</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-full text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #D97706, #B45309);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Kebutuhan Anabul' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-bold rounded-full border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-amber-300 dark:border-amber-800/50 hover:bg-amber-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Booking Jadwal Grooming</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-amber-200 dark:border-white/10 aspect-square bg-amber-50 dark:bg-amber-950/20">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-bold text-neutral-900 dark:text-white">Groomer Sayang Binatang</span>
                            </div>
                            <span class="text-amber-700 dark:text-amber-400 font-bold font-mono">100% Care</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
