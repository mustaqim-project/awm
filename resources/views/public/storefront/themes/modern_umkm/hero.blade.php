{{--
    Modern UMKM Hero Section (`modern_umkm`)
    Friendly, approachable local merchant hero, authentic handmade/local pride, direct WhatsApp ordering.
--}}
<section class="relative overflow-hidden pt-8 pb-14 sm:pt-14 sm:pb-20 bg-emerald-50/50 dark:bg-emerald-950/20 border-b border-emerald-900/10">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Copy --}}
            <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Produk & Usaha UMKM Pilihan' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                    {{ $landingPage->headline ?: 'Kualitas Terbaik dari ' . $business->name . ' untuk Kebutuhan Anda' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Dibuat dengan sepenuh hati, harga ramah di kantong, dan siap dikirim langsung dari outlet kami ke tempat Anda.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 text-sm sm:text-base font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition flex items-center gap-2 min-h-[48px]">
                            <span>{{ $landingPage->cta_primary_text ?: 'Belanja Sekarang' }}</span>
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-bold rounded-xl bg-white dark:bg-neutral-800 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-50 dark:hover:bg-neutral-700 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Chat Pemilik Toko</span>
                        </a>
                    @endif
                </div>

                {{-- UMKM Highlights --}}
                <div class="pt-4 flex items-center justify-center lg:justify-start gap-6 text-xs text-neutral-600 dark:text-neutral-400 font-medium">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="heart" class="w-4 h-4 text-rose-500 fill-rose-500"></i>
                        <span>100% Produk Lokal</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="badge-check" class="w-4 h-4 text-emerald-500"></i>
                        <span>Terverifikasi COOCA</span>
                    </div>
                </div>
            </div>

            {{-- Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-2xl overflow-hidden shadow-xl border border-emerald-900/10 aspect-square bg-emerald-100 dark:bg-neutral-800">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-md flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-neutral-900 dark:text-white">Outlet Resmi Buka Hari Ini</span>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">Siap Kirim</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
