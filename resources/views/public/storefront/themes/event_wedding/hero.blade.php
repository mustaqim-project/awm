{{--
    Event & Wedding Hero Section (`event_wedding`)
    Romantic luxury celebration aesthetic for wedding organizers, event planners, decoration & rentals.
--}}
<section class="relative overflow-hidden bg-[#FFFDF7] dark:bg-[#161013] border-b border-rose-200/60 dark:border-rose-950/60 transition-colors">
    {{-- Champagne Gold & Rose Ambient --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-rose-400/10 dark:bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Wedding Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-900/60 text-xs font-semibold text-rose-800 dark:text-rose-300">
                    <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Wedding Organizer &bull; Event Planner &bull; Dekorasi Elegan</span>
                </div>

                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-rose-50 tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Wujudkan Momen Bahagia & Perayaan Paling Berkesan' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-rose-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Pendampingan menyeluruh dari konsep tema, dekorasi venue megah, katering istimewa hingga koordinasi hari perayaan tanpa rasa khawatir.' }}
                </p>

                {{-- Key Trust Metrics --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Paket All-In Wedding &amp; Event</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Tim Kru Berpengalaman</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Cek Tanggal Baik Hari Bahagia</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-full bg-rose-700 hover:bg-rose-800 text-white font-semibold text-sm shadow-md shadow-rose-700/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="sparkles" class="w-4 h-4 text-amber-300"></i>
                            <span>{{ __('storefront.pilihan_paket_pernikahan') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-full bg-white dark:bg-zinc-900 hover:bg-rose-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-rose-200 dark:border-rose-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Konsultasi Wedding Planner</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Venue Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-rose-200/80 dark:border-rose-950/80 bg-white dark:bg-zinc-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-rose-950 via-zinc-950 to-amber-950 text-white">
                                <div class="w-16 h-16 rounded-full bg-rose-600/20 border border-rose-500/30 flex items-center justify-center mb-3 text-rose-300">
                                    <i data-lucide="heart" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading text-3xl text-rose-100 mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-rose-200/80 max-w-xs">Pernikahan impian, dekorasi anggun, dan perayaan penuh kenangan indah.</p>
                            </div>
                        @endif

                        {{-- Floating Celebration Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md border border-rose-100 dark:border-rose-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-700">
                                    <i data-lucide="award" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Hari Bahagia Tanpa Khawatir</span>
                                    <span class="text-[10px] text-slate-500">Rundown rapi &amp; koordinasi tuntas</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-rose-700 dark:text-rose-400">Rincian &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
