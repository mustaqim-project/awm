{{--
    Food & Restaurant Hero Section (`food_restaurant`)
    Warm appetite-inducing visual storytelling, chef specialties, dine-in & takeaway reservations.
--}}
<section class="relative overflow-hidden bg-[#FFFDF5] dark:bg-[#18110D] border-b border-amber-100 dark:border-amber-950/60 transition-colors">
    {{-- Warm Hearth Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-red-500/10 dark:bg-red-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/10 dark:bg-amber-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Actions --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-100/70 dark:bg-red-950/60 border border-red-200/80 dark:border-red-900/60 text-xs font-semibold text-red-800 dark:text-red-300">
                    <i data-lucide="utensils-crossed" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                    <span class="tracking-wide uppercase text-[10px] font-bold">Cita Rasa Otentik &bull; Masak Segar Tiap Hari</span>
                </div>

                <h1 class="font-heading font-normal text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-amber-50 tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: 'Kelezatan Resep Asli, Dimasak Hangat Dari Dapur Kami' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-amber-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Nikmati hidangan istimewa racikan bumbu rempah pilihan, disajikan dengan kehangatan suasana santap bersama keluarga dan kerabat.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>100% Halal Certified</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60">
                        <i data-lucide="flame" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Bumbu Rempah Alami</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-300 border border-red-200/60">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-red-600"></i>
                        <span>Higienis &amp; Segar</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 rounded-2xl bg-red-700 hover:bg-red-800 text-white font-semibold text-sm shadow-md shadow-red-700/20 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <span>Buka Buku Menu</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 hover:bg-amber-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-amber-200 dark:border-amber-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                            <span>Reservasi Meja / Order</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Hero Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-amber-200/80 dark:border-amber-950/80 bg-white dark:bg-zinc-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-red-900 via-amber-950 to-neutral-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="utensils" class="w-8 h-8 text-amber-400"></i>
                                </div>
                                <h3 class="font-heading text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-amber-200/80 max-w-xs">Pengalaman kuliner penuh selera dengan cita rasa hangat keluarga.</p>
                            </div>
                        @endif

                        {{-- Floating Specialty Card --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-amber-200/60 dark:border-amber-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-950 flex items-center justify-center text-red-600">
                                    <i data-lucide="chef-hat" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Menu Spesial Hari Ini</span>
                                    <span class="text-[10px] text-slate-500 dark:text-zinc-400">Disediakan terbatas &amp; fresh</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">Pesan &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
