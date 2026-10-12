{{--
    Fitness, Gym & Studio Olahraga Hero Section (`service_fitness` / `titan_kinetic`)
    Athletic power aesthetic, Bebas Neue condensed typography, acid lime accents, memberships & trainer booking.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24 bg-[#171717] text-white">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black tracking-widest uppercase bg-lime-950/70 text-lime-400 border border-lime-500/30">
                    <i data-lucide="dumbbell" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Fitness Center, Gym & Personal Trainer' }}</span>
                </div>

                <h1 class="font-heading font-black text-4xl sm:text-6xl lg:text-7xl text-white tracking-tight leading-[1.02] uppercase">
                    {{ $landingPage->headline ?: 'Bakar Lemak, Bentuk Otot & Capai Versi Terbaikmu' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Alat gym modern terlengkap, kelas yoga, zumba, HIIT & pilates harian, program diet terstruktur serta pendampingan personal trainer profesional berlisensi.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-lime-950/80 text-lime-300 border border-lime-800/60">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Akses Gym 06:00 - 22:00</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-neutral-800 text-neutral-200 border border-neutral-700">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Certified Personal Trainer</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-800/60">
                        <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                        <span>Free 1-Day Trial Pass</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-8 py-3.5 text-base font-black rounded-2xl text-black shadow-lg shadow-lime-500/20 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:scale-105"
                            style="background: linear-gradient(135deg, #84CC16, #A3E635);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Paket Membership' }}</span>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-neutral-900 text-white border-lime-500/30 hover:bg-neutral-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                            <span>Klaim Free Trial Gym</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-lime-500/30 aspect-square bg-[#262626]">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#171717] via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-neutral-900/95 backdrop-blur-md border border-lime-500/30 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-lime-500 animate-ping"></span>
                                <span class="font-bold text-white">Studio Aktif &amp; Alat Lengkap</span>
                            </div>
                            <span class="text-lime-400 font-mono font-bold">Titan Power</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
