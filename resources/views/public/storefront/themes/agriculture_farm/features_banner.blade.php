{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM FEATURES BANNER (`agriculture_farm`)                  --}}
{{-- 4 Pillars of Agrarian Excellence: Fresh Harvest, Clean, Fair, & Fast     --}}
{{-- ========================================================================= --}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-[#0D1F12]/80 backdrop-blur-md border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs transition-colors">

        {{-- Pillar 1: Petik Segar Tiap Hari --}}
        <div class="group flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-emerald-50 dark:hover:bg-emerald-950/70 hover:border-emerald-600/20 transition-all duration-200">
            <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                <i data-lucide="sprout" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-100 leading-snug">
                    Petik Segar Subuh
                </h4>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1 leading-relaxed">
                    Dipanen tiap subuh saat kadar nutrisi tanaman berada di titik puncak kealamian.
                </p>
            </div>
        </div>

        {{-- Pillar 2: 100% Organik & Non-Pestisida --}}
        <div class="group flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-emerald-50 dark:hover:bg-emerald-950/70 hover:border-emerald-600/20 transition-all duration-200">
            <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                <i data-lucide="leaf" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-100 leading-snug">
                    Bebas Residu Kimia
                </h4>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1 leading-relaxed">
                    Sistem tanam alami dengan pupuk hayati ramah tanah, aman dikonsumsi seluruh keluarga.
                </p>
            </div>
        </div>

        {{-- Pillar 3: Kemitraan Tani Adil --}}
        <div class="group flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-emerald-50 dark:hover:bg-emerald-950/70 hover:border-emerald-600/20 transition-all duration-200">
            <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                <i data-lucide="heart-handshake" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-100 leading-snug">
                    Kemitraan Petani Adil
                </h4>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1 leading-relaxed">
                    Membeli langsung dari poktan binaan guna memberdayakan ekonomi desa dan pangan mandiri.
                </p>
            </div>
        </div>

        {{-- Pillar 4: Logistik & Proteksi Pengiriman --}}
        <div class="group flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-emerald-50 dark:hover:bg-emerald-950/70 hover:border-emerald-600/20 transition-all duration-200">
            <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                <i data-lucide="truck" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-100 leading-snug">{{ __('storefront.pengiriman_terproteksi') }}</h4>
                <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1 leading-relaxed">
                    Kemasan sirkulasi udara khusus menjaga sayur &amp; buah tetap renyah hingga sampai di rumah.
                </p>
            </div>
        </div>

    </div>
</section>
