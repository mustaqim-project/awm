@extends('layouts.app', [
    'title' => __('landing_page.website_landing_page_bisnis_cooca'),
    'headerTitle' => 'Website & Landing Page Bisnis',
    'headerSubtitle' => 'Kelola profil digital, katalog produk & layanan, galeri, serta kontak online ' . $business->name,
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="landingPageEditor()">

        {{-- 0. STOREFRONT & WEBSITE HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => 'Website & Profil Bisnis'])

        <!-- ========================================== -->
        <!-- 1. UNIFIED ACTION COCKPIT & STATUS TOOLBAR -->
        <!-- ========================================== -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 sm:p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3.5">
            <!-- Left: Live Status & Meta Pills -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[12px] font-semibold border transition-all"
                     :class="isPublished ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 border-black/5 dark:border-white/10'">
                    <span class="w-2 h-2 rounded-full" :class="isPublished ? 'bg-emerald-500 animate-pulse' : 'bg-black/30 dark:bg-white/30'"></span>
                    <span x-text="isPublished ? 'Live Publik (Terbuka Umum)' : 'Draft Offline (Hanya Pemilik)'"></span>
                </div>

                <!-- Active Preset Pill -->
                <div class="inline-flex items-center gap-1.5 text-[12px] text-black/60 dark:text-white/60 bg-black/[0.03] dark:bg-white/[0.04] px-3 py-1.5 rounded-full border border-black/5 dark:border-white/5">
                    <span class="text-black/40 dark:text-white/40">Tema:</span>
                    <strong class="text-[#007AFF] font-semibold uppercase font-mono" x-text="form.theme_preset || form.industry_preset"></strong>
                </div>

                <!-- Quick Copy Link -->
                <button type="button" @click="copyLink('{{ $publicUrl }}')"
                    class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/70 dark:text-white/70 font-medium active:scale-95 transition inline-flex items-center gap-1.5 text-[11.5px]">
                    <i data-lucide="copy" class="w-3.5 h-3.5 opacity-70"></i>
                    <span x-text="copied ? 'Tersalin!' : 'Salin URL'">Salin URL</span>
                </button>

                <!-- Share WA -->
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Halo, kunjungi website kami di: ' . $publicUrl) }}"
                    target="_blank"
                    class="h-8 px-2.5 rounded-[9px] bg-emerald-500/10 hover:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-medium active:scale-95 transition inline-flex items-center gap-1.5 text-[11.5px]">
                    <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                    <span>Share WA</span>
                </a>
            </div>

            <!-- Right: Studio Actions -->
            <div class="flex items-center gap-2 flex-wrap">
                @if (\App\Support\Context::hasPermission('cms.manage'))
                    <!-- Preset Modal Button -->
                    <button type="button" @click="showPresetModal = true; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                        class="min-h-[38px] px-3.5 rounded-[11px] text-[12.5px] font-semibold text-[#B25E00] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/15 active:scale-95 transition-all inline-flex items-center gap-1.5 border border-[#FF9500]/20">
                        <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                        <span>Preset Industri</span>
                    </button>

                    <!-- Toggle Publish Button -->
                    <button type="button" @click="togglePublish()" :disabled="saving"
                        :class="isPublished
                            ? 'text-red-600 dark:text-red-400 bg-red-500/10 hover:bg-red-500/15 border border-red-500/20'
                            : 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/15 border border-emerald-500/20'"
                        class="min-h-[38px] px-3.5 rounded-[11px] text-[12.5px] font-semibold active:scale-95 transition-all inline-flex items-center gap-1.5 disabled:opacity-50">
                        <i :data-lucide="isPublished ? 'eye-off' : 'globe'" class="w-3.5 h-3.5"></i>
                        <span x-text="isPublished ? 'Arsipkan Publik' : 'Terbitkan Sekarang'"></span>
                    </button>
                @endif

                <!-- Live Preview Button -->
                <button type="button" @click="openPreview()" :disabled="saving"
                    class="min-h-[38px] px-3.5 rounded-[11px] text-[12.5px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] active:scale-95 transition-all inline-flex items-center gap-1.5 border border-black/5 dark:border-white/10">
                    <i data-lucide="play" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span x-text="saving ? 'Menyiapkan...' : 'Preview Live'">Preview Live</span>
                </button>

                <!-- Primary Save Button -->
                @if (\App\Support\Context::hasPermission('cms.manage'))
                    <button type="button" @click="saveAll()" :disabled="saving"
                        class="min-h-[38px] px-4 rounded-[11px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-95 transition-all inline-flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                        <i x-show="!saving" data-lucide="check" class="w-4 h-4"></i>
                        <svg x-show="saving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Menyimpan...' : '{{ __('common.save') }}'">{{ __('common.save') }}</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. COMPACT BENTO GLANCEABLE METRICS STRIP  -->
        <!-- ========================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="globe" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Status Website</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tracking-tight truncate"
                         :class="isPublished ? 'text-emerald-600 dark:text-emerald-400' : 'text-black/50 dark:text-white/50'"
                         x-text="isPublished ? 'Live Publik' : 'Draft Offline'">
                        {{ $landingPage->is_published ? 'Live Publik' : 'Draft Offline' }}
                    </div>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Layanan &amp; Menu</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tracking-tight truncate">
                        <span x-text="services.length">0</span> Layanan
                        <span class="text-[11.5px] font-normal text-black/45 dark:text-white/45">· {{ $landingPage->show_pos_products ? $posProducts->count() . ' POS' : 'POS Non-aktif' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Pilar &amp; Ulasan</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tracking-tight truncate">
                        <span x-text="values.length">4</span> Pilar
                        <span class="text-[11.5px] font-normal text-black/45 dark:text-white/45">· <span x-text="testimonials.length">0</span> Ulasan</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                    <i data-lucide="image" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Galeri Foto</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tracking-tight truncate">
                        <span x-text="galleryItems.length + newGalleryUploads.length">0</span> / 20 Foto
                        <span class="text-[11.5px] font-normal text-emerald-600 dark:text-emerald-400" x-show="sectionVisibility.gallery">· Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. MAIN FORM & STUDIO WORKSPACE            -->
        <!-- ========================================== -->
        <form action="{{ route('landing-page.update') }}" method="POST" id="landingPageForm"
            enctype="multipart/form-data" @submit.prevent="saveAll">
            @csrf
            @method('PUT')

            <!-- Hidden JSON & Preset inputs synced via Alpine -->
            <input type="hidden" name="values_json" :value="JSON.stringify(values)">
            <input type="hidden" name="custom_services_json" :value="JSON.stringify(services)">
            <input type="hidden" name="faqs_json" :value="JSON.stringify(faqs)">
            <input type="hidden" name="testimonials_json" :value="JSON.stringify(testimonials)">
            <input type="hidden" name="operational_hours_json" :value="JSON.stringify(operationalHours)">
            <input type="hidden" name="gallery_images_json" :value="JSON.stringify(galleryItems)">
            <input type="hidden" name="section_visibility_json" :value="JSON.stringify(sectionVisibility)">
            <input type="hidden" name="industry_preset" :value="form.industry_preset">
            <input type="hidden" name="theme_color" :value="form.theme_color">
            <input type="hidden" name="theme_preset" :value="form.theme_preset">
            <input type="hidden" name="font_family" :value="form.font_family">
            <input type="hidden" name="active_pages_json" :value="JSON.stringify(activePages)">
            <input type="hidden" name="custom_labels_json" :value="JSON.stringify(customLabels)">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 items-start">

                <!-- SIDEBAR TABS NAVIGATION (3 KOLOM DESKTOP - macOS Source List) -->
                <div class="lg:col-span-3 space-y-3">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3 lg:sticky lg:top-20 transition-colors shadow-xs">

                        <div
                            class="px-2.5 pb-2.5 mb-1.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                            <span
                                class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">Bagian Studio</span>
                            <span class="text-[11px] font-mono font-semibold text-[#007AFF]">11 Bagian</span>
                        </div>

                        <!-- Horizontal scroll on mobile / vertical list on desktop -->
                        <div class="flex lg:block gap-1 overflow-x-auto no-scrollbar pb-1.5 lg:pb-0 snap-x snap-mandatory">
                            <template x-for="(tab, index) in tabs" :key="tab.id">
                                <button type="button" @click="activeTab = tab.id; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                                    class="w-auto lg:w-full shrink-0 snap-start flex items-center justify-between gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all text-left whitespace-nowrap mb-0.5 cursor-pointer"
                                    :class="activeTab === tab.id ?
                                        'bg-[#007AFF] text-white font-semibold shadow-xs' :
                                        'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-5 h-5 rounded-[6px] flex items-center justify-center shrink-0 text-[10.5px] font-bold font-mono"
                                              :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60'"
                                              x-text="index + 1"></span>
                                        <span class="truncate" x-text="tab.label"></span>
                                    </div>
                                    <i x-show="activeTab === tab.id" data-lucide="chevron-right"
                                        class="w-3.5 h-3.5 text-white/70 hidden lg:inline"></i>
                                </button>
                            </template>
                        </div>

                        <!-- Quick Direct Save in Sidebar -->
                        @if (\App\Support\Context::hasPermission('cms.manage'))
                            <div class="pt-3 mt-2 border-t border-black/5 dark:border-white/5 space-y-2">
                                <button type="submit" :disabled="saving"
                                    class="w-full min-h-[38px] px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-xs disabled:opacity-50 cursor-pointer">
                                    <i x-show="!saving" data-lucide="check" class="w-4 h-4"></i>
                                    <svg x-show="saving" class="w-4 h-4 animate-spin text-white" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-text="saving ? 'Menyimpan...' : '{{ __('common.save') }}'">{{ __('common.save') }}</span>
                                </button>
                                <p class="text-[11px] text-center truncate"
                                    :class="saveError ? 'text-[#FF3B30] font-medium' : 'text-black/50 dark:text-white/50'"
                                    x-text="saveError || saveMessage || 'Perubahan tersimpan otomatis'"></p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- STUDIO CONTENT AREA (9 KOLOM DESKTOP) -->
                <div class="lg:col-span-9 space-y-5">

                    <!-- ============================================================ -->
                    <!-- TAB 1: GENERAL / IDENTITAS & WARNA TEMA                       -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'general'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        1. Identitas Visual &amp; Warna Tema
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tentukan warna dominan
                                        dan karakter visual yang merepresentasikan identitas brand bisnis Anda.</p>
                                </div>
                                <span
                                    class="text-[11px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] px-2.5 py-1 rounded-full">
                                    Preset: <strong class="text-[#007AFF] uppercase font-mono"
                                        x-text="form.industry_preset"></strong>
                                </span>
                            </div>

                            <!-- Theme Color Picker -->
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2 uppercase tracking-wider">
                                    Palet Warna Aksen Utama
                                </label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <template x-for="color in colorSwatches" :key="color.hex">
                                        <button type="button" @click="form.theme_color = color.hex"
                                            :style="`background-color: ${color.hex}`"
                                            :class="form.theme_color === color.hex ?
                                                'ring-2 ring-[#007AFF] ring-offset-2 ring-offset-white dark:ring-offset-[#1C1C1E] scale-110' :
                                                'opacity-80 hover:opacity-100 hover:scale-105'"
                                            class="w-8 h-8 rounded-full transition-all flex items-center justify-center text-white shadow-xs cursor-pointer"
                                            :title="color.label">
                                            <svg x-show="form.theme_color === color.hex" class="w-3.5 h-3.5"
                                                fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </button>
                                    </template>
                                    <div
                                        class="flex items-center gap-2 ml-2 px-3 py-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                                        <input type="color" x-model="form.theme_color"
                                            class="w-6 h-6 rounded-md cursor-pointer bg-transparent border-0 p-0"
                                            title="{{ __('landing_page.pilih_warna_kustom') }}">
                                        <code class="text-[12px] text-[#007AFF] font-mono font-semibold uppercase"
                                            x-text="form.theme_color"></code>
                                    </div>
                                </div>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-2">
                                    Warna aksen ini akan diterapkan pada tombol utama, badge produk, serta elemen sorotan
                                    pada website publik.
                                </p>
                            </div>

                            <!-- Font Family & Google Fonts Selector -->
                            <div class="pt-2">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2 uppercase tracking-wider">
                                    Tipografi &amp; Google Fonts Storefront
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <select x-model="form.font_family"
                                            class="w-full h-11 px-3.5 text-[13px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF] transition cursor-pointer">
                                            <option value="">Bawaan Tema Aktif (Otentik)</option>
                                            <template x-for="font in curatedFonts" :key="font">
                                                <option :value="font" x-text="font" :selected="form.font_family === font"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                                        <div class="truncate">
                                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-semibold">Pratinjau Huruf:</span>
                                            <span class="text-[13px] font-bold text-black dark:text-white truncate block"
                                                  :style="form.font_family ? `font-family: '${form.font_family}', sans-serif;` : ''"
                                                  x-text="form.font_family || (allThemesMap[form.theme_preset]?.font_heading || 'Plus Jakarta Sans') + ' (Bawaan)'"></span>
                                        </div>
                                        <button type="button" x-show="form.font_family" @click="form.font_family = ''"
                                            class="text-[11px] text-[#007AFF] hover:underline font-semibold shrink-0 cursor-pointer">
                                            Reset Default
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-1.5">
                                    Gunakan font kustom Google Fonts atau biarkan kosong untuk menerapkan perpaduan tipografi otentik bawaan dari tema yang dipilih.
                                </p>
                            </div>

                            <!-- Dark Mode Toggle for Landing Page -->
                            <div
                                class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-[14px] font-semibold text-black dark:text-white">Tampilan Elegan Gelap
                                        (Dark Mode Publik)</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Aktifkan untuk
                                        memberikan nuansa mewah bertema gelap pada landing page publik bisnis Anda.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="dark_mode" value="1" x-model="form.dark_mode"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>

                            <!-- ============================================================ -->
                            <!-- 21 TEMPLATE TEMA INDUSTRI OTENTIK & UNIVERSAL STOREFRONT        -->
                            <!-- ============================================================ -->
                            <!-- ============================================================ -->
                            <!-- 21 TEMPLATE TEMA INDUSTRI OTENTIK & UNIVERSAL STOREFRONT        -->
                            <!-- ============================================================ -->
                            @php
                                $themeCategoryMap = [
                                    // Kuliner & Minuman (8)
                                    'food_restaurant'           => 'fnb',
                                    'coffee_beverage'           => 'fnb',
                                    'bakery_cake'               => 'fnb',
                                    'fnb_bakery'                => 'fnb',
                                    'fnb_cafe'                  => 'fnb',
                                    'fnb_catering'              => 'fnb',
                                    'fnb_fastfood'              => 'fnb',
                                    'fnb_resto'                 => 'fnb',

                                    // Retail & Toko (11)
                                    'online_store'              => 'retail',
                                    'product_showcase'          => 'retail',
                                    'wholesale_distributor'     => 'retail',
                                    'modern_umkm'               => 'retail',
                                    'retail_bookstore'          => 'retail',
                                    'retail_building'           => 'retail',
                                    'retail_electronics'        => 'retail',
                                    'retail_fashion'            => 'retail',
                                    'retail_florist'            => 'retail',
                                    'retail_grocery'            => 'retail',
                                    'retail_pharmacy'           => 'retail',

                                    // Jasa & Layanan & Reservasi (10)
                                    'service_business'          => 'service',
                                    'booking_appointment'       => 'service',
                                    'event_wedding'             => 'service',
                                    'hospitality_accommodation' => 'service',
                                    'service_beauty'            => 'service',
                                    'service_carwash'           => 'service',
                                    'service_laundry'           => 'service',
                                    'service_petshop'           => 'service',
                                    'service_printing'          => 'service',
                                    'service_workshop'          => 'service',

                                    // Industri, Logistik, Properti & Agro (4)
                                    'manufacturing_factory'     => 'industry',
                                    'agriculture_farm'          => 'industry',
                                    'logistics_transportation'  => 'industry',
                                    'construction_property'     => 'industry',

                                    // Kesehatan, Kebugaran, Edukasi & Personal (4)
                                    'health_beauty'             => 'lifestyle',
                                    'education_courses'         => 'lifestyle',
                                    'personal_brand'            => 'lifestyle',
                                    'service_fitness'           => 'lifestyle',

                                    // Korporat, Agensi & Universal (5)
                                    'business_corporate'        => 'corporate',
                                    'professional_services'     => 'corporate',
                                    'portfolio_creative'        => 'corporate',
                                    'service_agency'            => 'corporate',
                                    'universal_storefront'      => 'corporate',
                                ];

                                $categoryCounts = [
                                    'all'       => count($themePresets),
                                    'fnb'       => 0,
                                    'retail'    => 0,
                                    'service'   => 0,
                                    'industry'  => 0,
                                    'lifestyle' => 0,
                                    'corporate' => 0,
                                ];

                                foreach ($themePresets as $k => $preset) {
                                    $cat = $themeCategoryMap[$k] ?? 'corporate';
                                    if (isset($categoryCounts[$cat])) {
                                        $categoryCounts[$cat]++;
                                    }
                                }
                            @endphp
                            <div class="pt-5 border-t border-black/5 dark:border-white/5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-[15px] font-semibold text-black dark:text-white">
                                                Koleksi {{ count($themePresets) }} Tema Industri Otentik &amp; Storefront
                                            </h3>
                                            <span class="text-[10px] font-mono font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">
                                                {{ count($themePresets) }} Tema Tersedia
                                            </span>
                                        </div>
                                        <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">
                                            Seluruh {{ count($themePresets) }} tema visual dirancang spesifik dengan font Google Fonts, palet warna otentik, dan arsitektur hero per sektor usaha.
                                        </p>
                                    </div>

                                    <!-- Quick Reset / Active Theme Pill -->
                                    <div class="flex items-center gap-2 text-[11px] bg-black/[0.03] dark:bg-white/[0.04] px-3 py-1.5 rounded-[10px] border border-black/5 dark:border-white/5 shrink-0">
                                        <span class="text-black/45 dark:text-white/45">Tema Aktif:</span>
                                        <strong class="text-[#007AFF] font-semibold uppercase font-mono" x-text="form.theme_preset"></strong>
                                    </div>
                                </div>

                                <!-- Active Theme Cockpit Card -->
                                <div class="p-4 rounded-[16px] bg-gradient-to-r from-[#007AFF]/10 via-[#007AFF]/5 to-black/[0.02] dark:to-white/[0.02] border border-[#007AFF]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-[14px] flex items-center justify-center text-white font-bold text-lg shadow-sm shrink-0"
                                             :style="`background-color: ${form.theme_color || '#007AFF'}`">
                                            <i data-lucide="layout-template" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF]"
                                                      x-text="allThemesMap[form.theme_preset]?.industry || 'UMKM & Bisnis'"></span>
                                                <span class="w-1 h-1 rounded-full bg-black/20 dark:bg-white/20"></span>
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">Tema Terpasang</span>
                                            </div>
                                            <h4 class="text-[16px] font-bold text-black dark:text-white mt-0.5"
                                                x-text="allThemesMap[form.theme_preset]?.name || form.theme_preset"></h4>
                                            <div class="flex items-center gap-3 text-[11px] text-black/55 dark:text-white/55 mt-0.5">
                                                <span>Font: <strong class="text-black dark:text-white font-mono" x-text="form.font_family || allThemesMap[form.theme_preset]?.font_heading || 'Plus Jakarta Sans'"></strong></span>
                                                <span>•</span>
                                                <span>Warna Aksen: <strong class="font-mono uppercase font-bold" :style="`color: ${form.theme_color}`" x-text="form.theme_color"></strong></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="openThemeModal(form.theme_preset)"
                                            class="h-9 px-3.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-black/20 text-[12px] font-semibold text-black dark:text-white shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            <span>{{ __('landing_page.detail_rekomendasi') }}</span>
                                        </button>
                                        <a :href="`{{ $publicUrl }}?preview=1&preview_theme=${form.theme_preset}`" target="_blank"
                                            class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] text-white hover:bg-[#0071E3] text-[12px] font-semibold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            <span>Test Drive Toko</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Filter Controls: Search & Category Tabs -->
                                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-1">
                                    <!-- Category Segmented Control -->
                                    <div class="flex items-center gap-1 overflow-x-auto no-scrollbar p-1 bg-black/[0.03] dark:bg-white/[0.04] rounded-[11px] border border-black/5 dark:border-white/5 text-[11px]">
                                        <button type="button" @click="themeFilterCategory = 'all'"
                                            :class="themeFilterCategory === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            {{ __('common.all') }} ({$categoryCounts['all'] })
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'fnb'"
                                            :class="themeFilterCategory === 'fnb' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Kuliner ({{ $categoryCounts['fnb'] }})
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'retail'"
                                            :class="themeFilterCategory === 'retail' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Retail &amp; Toko ({{ $categoryCounts['retail'] }})
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'service'"
                                            :class="themeFilterCategory === 'service' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Jasa &amp; Reservasi ({{ $categoryCounts['service'] }})
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'industry'"
                                            :class="themeFilterCategory === 'industry' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Industri &amp; Agro ({{ $categoryCounts['industry'] }})
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'lifestyle'"
                                            :class="themeFilterCategory === 'lifestyle' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Kesehatan &amp; Edu ({{ $categoryCounts['lifestyle'] }})
                                        </button>
                                        <button type="button" @click="themeFilterCategory = 'corporate'"
                                            :class="themeFilterCategory === 'corporate' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="px-2.5 py-1 rounded-[8px] transition whitespace-nowrap cursor-pointer">
                                            Korporat &amp; Kreatif ({{ $categoryCounts['corporate'] }})
                                        </button>
                                    </div>

                                    <!-- Search Input -->
                                    <div class="relative w-full md:w-64">
                                        <input type="text" x-model="themeSearch" placeholder="{{ __('landing_page.cari_tema_nama_atau_industri') }}"
                                            class="w-full h-8.5 pl-8.5 pr-7 text-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 rounded-[10px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:ring-1 focus:ring-[#007AFF] transition">
                                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-black/40 dark:text-white/40 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M16.65 11a5.65 5.65 0 11-11.3 0 5.65 5.65 0 0111.3 0z"/>
                                        </svg>
                                        <button type="button" x-show="themeSearch" @click="themeSearch = ''" class="absolute right-2 top-2 text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Grid of All 21 Themes -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 pt-1">
                                    @foreach ($themePresets as $tKey => $tPreset)
                                        @php
                                            $tCat = $themeCategoryMap[$tKey] ?? 'all';
                                            $searchIndex = strtolower($tPreset['name'] . ' ' . $tPreset['industry'] . ' ' . $tKey . ' ' . $tPreset['description']);
                                        @endphp
                                        <div x-show="(themeFilterCategory === 'all' || themeFilterCategory === '{{ $tCat }}') && (!themeSearch || '{{ addslashes($searchIndex) }}'.toLowerCase().includes(themeSearch.toLowerCase().trim()))"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             @click="applyTheme('{{ $tKey }}', false)"
                                             :class="form.theme_preset === '{{ $tKey }}' ? 'ring-2 ring-[#007AFF] border-[#007AFF] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 shadow-sm' : 'border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.03] hover:border-black/20 dark:hover:border-white/20'"
                                             class="p-3.5 rounded-[14px] border cursor-pointer transition flex flex-col justify-between gap-3 relative group active:scale-[0.99]">
                                            
                                            <div>
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <span class="text-[9.5px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                                          style="background-color: {{ $tPreset['primary_color'] }}20; color: {{ $tPreset['primary_color'] }};">
                                                        {{ $tPreset['industry'] }}
                                                    </span>
                                                    <div x-show="form.theme_preset === '{{ $tKey }}'" x-cloak class="w-4 h-4 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-[10px] shadow-xs">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <div class="font-bold text-[13.5px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                                                    {{ $tPreset['name'] }}
                                                </div>
                                                <div class="text-[11px] text-black/50 dark:text-white/50 line-clamp-2 mt-1 leading-snug">
                                                    {{ $tPreset['description'] }}
                                                </div>
                                            </div>

                                            <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[10.5px] text-black/45 dark:text-white/45">
                                                <div class="flex items-center gap-1.5" title="Palet Warna Tema">
                                                    <span class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-2xs" style="background-color: {{ $tPreset['primary_color'] }};"></span>
                                                    <span class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-2xs" style="background-color: {{ $tPreset['accent_color'] }};"></span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-medium truncate max-w-[90px] font-mono text-[10px]" title="Font Tipografi">{{ $tPreset['font_heading'] }}</span>
                                                    <button type="button" @click.stop="openThemeModal('{{ $tKey }}')"
                                                        class="px-2 py-0.5 rounded-[6px] bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-[10px] font-semibold text-[#007AFF] transition">{{ __('common.detail') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- ============================================================ -->
                            <!-- AUTO-HIDE DYNAMIC NAVIGATION PANEL (§PRD-07 §2.B)             -->
                            <!-- ============================================================ -->
                            <div class="pt-4 border-t border-black/5 dark:border-white/5 space-y-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('landing_page.pengaturan_halaman_navigasi_dinamis_auto_hide') }}</h3>
                                        <span class="text-[11px] font-mono text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded-full font-bold">
                                            Auto-Hide Enabled
                                        </span>
                                    </div>
                                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                        Aktifkan atau nonaktifkan halaman toko. Header navbar &amp; footer publik hanya akan menampilkan menu yang aktif. URL halaman yang dinonaktifkan akan secara cerdas dialihkan ke Beranda.
                                    </p>
                                </div>

                                <div class="space-y-2.5">
                                    {{-- Home --}}
                                    <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Halaman Beranda</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug} (Wajib Aktif)</span>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Selalu Aktif</span>
                                    </div>

                                    {{-- Catalog --}}
                                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="checkbox" x-model="activePages.catalog" class="sr-only peer">
                                                <div class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Katalog Toko &amp; Belanja</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug}/katalog &amp; PDP</span>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-60">
                                            <input type="text" x-model="customLabels.catalog" placeholder="{{ __('landing_page.label_menu_default_katalog_produk') }}"
                                                   class="w-full h-8 px-2.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800 text-[12px] text-black dark:text-white">
                                        </div>
                                    </div>

                                    {{-- About --}}
                                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="checkbox" x-model="activePages.about" class="sr-only peer">
                                                <div class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Halaman Tentang Kami</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug}/tentang-kami</span>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-60">
                                            <input type="text" x-model="customLabels.about" placeholder="{{ __('landing_page.label_menu_default_tentang_kami') }}"
                                                   class="w-full h-8 px-2.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800 text-[12px] text-black dark:text-white">
                                        </div>
                                    </div>

                                    {{-- Reservation --}}
                                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="checkbox" x-model="activePages.reservation" class="sr-only peer">
                                                <div class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Reservasi &amp; Booking Meja / Janji Temu</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug}/reservasi</span>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-60">
                                            <input type="text" x-model="customLabels.reservation" placeholder="Label Menu (Default: Reservasi)"
                                                   class="w-full h-8 px-2.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800 text-[12px] text-black dark:text-white">
                                        </div>
                                    </div>

                                    {{-- Contact --}}
                                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="checkbox" x-model="activePages.contact" class="sr-only peer">
                                                <div class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Kontak, Cabang &amp; Jam Buka</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug}/kontak</span>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-60">
                                            <input type="text" x-model="customLabels.contact" placeholder="{{ __('landing_page.label_menu_default_kontak_cabang') }}"
                                                   class="w-full h-8 px-2.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800 text-[12px] text-black dark:text-white">
                                        </div>
                                    </div>

                                    {{-- Blog --}}
                                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="checkbox" x-model="activePages.blog" class="sr-only peer">
                                                <div class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]"></div>
                                            </label>
                                            <div>
                                                <span class="text-[13px] font-semibold text-black dark:text-white">Artikel &amp; Edukasi Bisnis (SEO)</span>
                                                <span class="text-[11px] text-black/40 dark:text-white/40 block">URL: /{business-slug}/artikel</span>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-60">
                                            <input type="text" x-model="customLabels.blog" placeholder="Label Menu (Default: Artikel)"
                                                   class="w-full h-8 px-2.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800 text-[12px] text-black dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 2: HERO / BANNER UTAMA                                   -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'hero'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        2. Banner Utama (Hero Section)
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bagian teratas yang
                                        pertama kali dilihat calon pelanggan saat membuka website.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.hero"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Hero</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Badge Pengumuman -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Badge Pengumuman Singkat
                                    </label>
                                    <input type="text" name="announcement_badge" x-model="form.announcement_badge"
                                        placeholder="{{ __('landing_page.solusi_terpercaya_sejak_2018') }}"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Muncul di atas judul besar
                                        sebagai pemikat perhatian pertama.</p>
                                </div>

                                <!-- Upload Logo -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Upload Logo Bisnis
                                    </label>
                                    <div x-show="previews.logo || form.logo_url" class="mb-2 flex items-center gap-3">
                                        <img :src="previews.logo || form.logo_url" alt="Logo Preview"
                                            class="w-12 h-12 rounded-[10px] object-contain bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 p-1">
                                        <span class="text-[11px] text-[#007AFF] font-medium"
                                            x-text="previews.logo ? 'Pratinjau logo baru dipilih' : 'Logo tersimpan aktif'"></span>
                                    </div>
                                    <input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp"
                                        @change="handleImagePreview($event, 'logo')"
                                        class="w-full text-[16px] sm:text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                    <label
                                        class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                        <input type="checkbox" name="remove_logo_image" value="1"
                                            class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                        <span>Hapus logo tersimpan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Headline Utama -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Headline Utama (Judul Besar) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="headline" x-model="form.headline"
                                    placeholder="{{ __('landing_page.solusi_layanan_produk_terpercaya_untuk_kebutuhan') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[14px] font-semibold text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <!-- Subheadline -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Subheadline (Deskripsi Lengkap Hero)
                                </label>
                                <textarea name="subheadline" x-model="form.subheadline" rows="3"
                                    placeholder="{{ __('landing_page.kami_hadir_memberikan_solusi_terbaik_dengan') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- Call-to-Action Buttons -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Teks Tombol Primer (CTA)
                                    </label>
                                    <input type="text" name="cta_primary_text" x-model="form.cta_primary_text"
                                        placeholder="Pesan via WhatsApp"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        URL Tujuan Tombol Primer
                                    </label>
                                    <input type="text" name="cta_primary_url" x-model="form.cta_primary_url"
                                        placeholder="{{ __('landing_page.https_wa_me_atau_kontak') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-mono">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Teks Tombol Sekunder
                                    </label>
                                    <input type="text" name="cta_secondary_text" x-model="form.cta_secondary_text"
                                        placeholder="{{ __('landing_page.lihat_daftar_layanan_harga') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        URL Tujuan Tombol Sekunder
                                    </label>
                                    <input type="text" name="cta_secondary_url" x-model="form.cta_secondary_url"
                                        placeholder="{{ __('landing_page.layanan_atau_url_tujuan') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-mono">
                                </div>
                            </div>

                            <!-- Upload Hero Image -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Foto Banner Hero (Opsional)
                                </label>
                                <div x-show="previews.hero || form.hero_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.hero || form.hero_image_url" alt="Hero Preview"
                                        class="w-32 h-20 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.hero ? 'Pratinjau foto hero baru dipilih' : 'Foto hero aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'hero')"
                                    class="w-full text-[16px] sm:text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_hero_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>Hapus foto hero tersimpan</span>
                                </label>
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Maks. 4MB. Format JPG, PNG,
                                    atau WebP. Resolusi ideal 1200x800px.</p>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 3: ABOUT / PROFIL, KEUNGGULAN & JAM BUKA                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'about'" x-cloak class="space-y-5">

                        <!-- Profil Bisnis & Story Card -->
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.3_profil_cerita_kredibilitas_bisnis') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bangun kepercayaan
                                        pelanggan melalui transparansi sejarah, reputasi, dan standar kualitas.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.about"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Profil</span>
                                </label>
                            </div>

                            <!-- Gambar About -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Foto Profil / Tempat Usaha (Opsional)
                                </label>
                                <div x-show="previews.about || form.about_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.about || form.about_image_url" alt="About Preview"
                                        class="w-32 h-20 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.about ? 'Pratinjau foto profil baru dipilih' : 'Foto profil aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'about')"
                                    class="w-full text-[16px] sm:text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_about_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>Hapus foto profil tersimpan</span>
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Judul Bagian Profil / About
                                </label>
                                <input type="text" x-model="form.about_title"
                                    placeholder="{{ __('landing_page.dedikasi_kami_untuk_kualitas_kepuasan_pelanggan') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Cerita / Sejarah / Filosofi Usaha
                                </label>
                                <textarea x-model="form.about_story" rows="4"
                                    placeholder="{{ __('landing_page.ceritakan_bagaimana_usaha_anda_beroperasi_komitmen') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- 3 STATS KREDIBILITAS -->
                            <div class="border-t border-black/5 dark:border-white/5 pt-4">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-3">
                                    3 Angka Metrik Kredibilitas Usaha
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label
                                            class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik
                                            1 (Pelanggan)</label>
                                        <input type="text" name="stats[clients]"
                                            value="{{ data_get($landingPage->values, 'stats.clients', '5.000+') }}"
                                            placeholder="5.000+"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[16px] sm:text-[15px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] focus:outline-none text-center">
                                        <input type="text" name="stats[clients_label]" value="Pelanggan Puas"
                                            placeholder="{{ __('landing_page.label_pelanggan') }}"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label
                                            class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik
                                            2 (Pengalaman)</label>
                                        <input type="text" name="stats[experience]" value="8+ Tahun"
                                            placeholder="{{ __('landing_page.8_tahun') }}"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[16px] sm:text-[15px] font-bold tabular-nums text-[#007AFF] focus:outline-none text-center">
                                        <input type="text" name="stats[experience_label]"
                                            value="Pengalaman Profesional" placeholder="Label Pengalaman"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label
                                            class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik
                                            3 (Review / Rating)</label>
                                        <input type="text" name="stats[rating]" value="4.9 / 5.0"
                                            placeholder="4.9 / 5.0"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[16px] sm:text-[15px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none text-center">
                                        <input type="text" name="stats[rating_label]" value="Rating Kepuasan Ulasan"
                                            placeholder="Label Rating"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 4 VALUE PILLARS CARD -->
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.4_pilar_keunggulan_utama_bisnis') }}</h3>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Alasan kuat mengapa calon
                                    pelanggan harus memilih produk atau layanan Anda dibanding kompetitor.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="(val, index) in values" :key="index">
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-[11px] shrink-0 tabular-nums"
                                                x-text="index + 1"></div>
                                            <input type="text" x-model="val.title"
                                                placeholder="Judul Pilar Keunggulan"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <select x-model="val.icon"
                                                class="h-8 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] text-[16px] sm:text-[12px] text-[#007AFF] font-medium focus:outline-none">
                                                <option value="shield-check">Shield Check</option>
                                                <option value="zap">Kilat (Zap)</option>
                                                <option value="award">Penghargaan (Award)</option>
                                                <option value="star">⭐ Star</option>
                                                <option value="clock">Jam (Clock)</option>
                                                <option value="heart">Favorit (Heart)</option>
                                                <option value="truck">Pengiriman (Truck)</option>
                                                <option value="thumbs-up">Rekomendasi (Thumbs Up)</option>
                                                <option value="check-circle">Terverifikasi (Check)</option>
                                                <option value="gem">Premium (Gem)</option>
                                                <option value="badge-check">Lencana (Badge)</option>
                                                <option value="tag">Penawaran (Tag)</option>
                                                <option value="sparkles">Istimewa (Sparkles)</option>
                                            </select>
                                        </div>
                                        <textarea x-model="val.description" rows="2" placeholder="Uraian singkat keunggulan..."
                                            class="w-full bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- OPERATIONAL HOURS CARD -->
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.jadwal_jam_operasional_outlet') }}</h3>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Informasikan jam buka harian
                                    agar calon pelanggan tahu waktu terbaik untuk datang atau memesan.</p>
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="(day, dIdx) in operationalHours" :key="dIdx">
                                    <div
                                        class="flex items-center gap-3 p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                        <div class="w-20 sm:w-24 text-[13px] font-medium text-black dark:text-white shrink-0"
                                            x-text="day.day"></div>
                                        <input type="text" x-model="day.hours" :disabled="!day.is_open"
                                            placeholder="08:00 - 17:00 WIB"
                                            :class="day.is_open ?
                                                'text-black dark:text-white bg-white dark:bg-[#2C2C2E]' :
                                                'text-black/35 dark:text-white/35 bg-black/[0.04] dark:bg-white/[0.04] italic'"
                                            class="flex-1 h-8 px-2.5 border-none rounded-[6px] text-[16px] sm:text-[12px] tabular-nums focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                        <label
                                            class="flex items-center gap-1.5 cursor-pointer text-[12px] shrink-0 select-none">
                                            <input type="checkbox" x-model="day.is_open"
                                                class="rounded-[4px] border-black/20 dark:border-white/20 text-[#34C759] focus:ring-[#34C759]">
                                            <span
                                                :class="day.is_open ? 'text-[#34C759] dark:text-[#30D158] font-semibold' :
                                                    'text-black/40 dark:text-white/40'"
                                                x-text="day.is_open ? 'Buka' : 'Tutup'"></span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 4: SERVICES / KATALOG LAYANAN & MENU                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'services'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.4_katalog_layanan_menu_paket_produk') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan daftar
                                        layanan, menu unggulan, atau paket komersial dengan foto dan rincian harga
                                        transparan.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.services"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addService"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>{{ __('landing_page.tambah_layanan') }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Auto-sync Jasa & Layanan Banner -->
                            @if (isset($serviceProducts) && $serviceProducts->isNotEmpty())
                                <div
                                    class="rounded-[12px] bg-purple-500/10 border border-purple-500/20 p-4 flex flex-col sm:flex-row items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-[10px] bg-purple-500/15 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 3.097l-.102.102m0 0l-5.108.233" /></svg>
                                    </div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-[13.5px] text-purple-950 dark:text-purple-100">
                                                {{ $serviceProducts->count() }} Jasa &amp; Layanan Terhubung Otomatis dari
                                                Master Katalog
                                            </span>
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded-full bg-purple-600 text-white font-bold uppercase tracking-wider">
                                                Auto Sync Live
                                            </span>
                                        </div>
                                        <p class="text-purple-900/80 dark:text-purple-300/80 text-[12px] leading-relaxed">
                                            Semua jasa yang Anda tambahkan di menu <strong
                                                class="text-purple-950 dark:text-purple-100">Jasa &amp; Layanan</strong>
                                            otomatis tampil di website publik Anda lengkap dengan tombol reservasi/booking
                                            via WhatsApp. Tidak perlu input ulang.
                                        </p>
                                        <div class="pt-1 flex flex-wrap gap-1.5">
                                            @foreach ($serviceProducts as $sp)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-white dark:bg-[#2C2C2E] border border-purple-500/20 text-[11.5px] font-medium text-black dark:text-white shadow-xs">
                                                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 3.097l-.102.102m0 0l-5.108.233" /></svg><span>{{ $sp->name }}</span>
                                                    @if ($sp->selling_price > 0)
                                                        <span
                                                            class="text-purple-600 dark:text-purple-400 font-semibold">(Rp
                                                            {{ number_format($sp->selling_price, 0, ',', '.') }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <a href="{{ route('services.index') }}"
                                        class="shrink-0 h-8 px-3 rounded-[8px] bg-purple-600 hover:bg-purple-700 active:scale-[0.97] text-white text-[12px] font-semibold transition flex items-center gap-1 shadow-xs">
                                        <span>{{ __('services.manage_services') }}</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            @endif

                            <!-- Services Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul
                                        Section Layanan</label>
                                    <input type="text" name="services_title" x-model="form.services_title"
                                        placeholder="{{ __('landing_page.layanan_produk_pilihan') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Subjudul
                                        / Deskripsi Pengantar</label>
                                    <input type="text" name="services_subtitle" x-model="form.services_subtitle"
                                        placeholder="{{ __('landing_page.kualitas_terbaik_dan_pelayanan_prima_untuk') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                            </div>

                            <!-- Services Repeater List -->
                            <div class="space-y-3">
                                <template x-for="(service, sIdx) in services" :key="sIdx">
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center text-[11px] font-bold tabular-nums shrink-0"
                                                x-text="sIdx + 1"></span>
                                            <input type="text" x-model="service.title"
                                                placeholder="{{ __('landing_page.nama_layanan_menu_paket') }}"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <input type="text" x-model="service.price" placeholder="Rp 150.000"
                                                class="w-full sm:w-36 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-semibold tabular-nums text-[#34C759] dark:text-[#30D158] focus:outline-none text-right">
                                            <button type="button" @click="removeService(sIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="{{ __('landing_page.hapus_layanan') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <div class="md:col-span-2">
                                                <input type="text" x-model="service.description"
                                                    placeholder="Deskripsi ringkas, rincian termasuk, garansi..."
                                                    class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            </div>
                                            <div>
                                                <input type="text" x-model="service.badge"
                                                    placeholder="Badge: Terpopuler / Promo"
                                                    class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-medium text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                            <label
                                                class="block text-[10px] font-semibold text-black/50 dark:text-white/50 mb-1 uppercase tracking-wider">Foto
                                                Layanan</label>
                                            <div x-show="service.preview_url || service.image_url"
                                                class="mb-2 flex items-center gap-2">
                                                <img :src="service.preview_url || service.image_url" alt="{{ __('landing_page.preview_layanan') }}"
                                                    class="w-14 h-10 rounded-[6px] object-cover border border-black/5 dark:border-white/10">
                                                <span class="text-[10px] text-[#007AFF] font-medium"
                                                    x-text="service.preview_url ? 'Foto baru dipilih' : 'Foto tersimpan aktif'"></span>
                                            </div>
                                            <input type="file" :name="'service_images[' + sIdx + ']'"
                                                accept="image/jpeg,image/png,image/webp"
                                                @change="handleServiceImagePreview($event, sIdx)"
                                                class="w-full text-[11px] text-black/50 dark:text-white/50 file:mr-2 file:rounded-[6px] file:border-0 file:bg-[#007AFF]/10 file:px-2.5 file:py-1 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                            <label
                                                class="mt-1 flex items-center gap-1.5 text-[10px] text-black/50 dark:text-white/50">
                                                <input type="checkbox" :name="'remove_service_images[' + sIdx + ']'"
                                                    value="1" class="rounded-[4px] border-black/20 text-[#FF3B30]">
                                                <span>Hapus foto layanan</span>
                                            </label>
                                        </div>

                                    </div>
                                </template>

                                <!-- Empty State Layanan -->
                                <div x-show="services.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">{{ __('landing_page.belum_ada_item_layanan') }}
                                        di katalog</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Klik "Tambah Layanan" di atas
                                        atau pilih template industri untuk mengisi katalog secara otomatis.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 5: PRODUCTS / INTEGRASI PRODUK KASIR POS                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'products'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.5_sinkronisasi_produk_kasir_pos') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan katalog produk
                                        master POS langsung di landing page lengkap dengan tombol pesan instan via WhatsApp.
                                    </p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.products"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Produk</span>
                                </label>
                            </div>

                            <!-- Toggle Sinkron POS -->
                            <div
                                class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-[14px] font-semibold text-black dark:text-white">Tampilkan Produk
                                        Aktif dari Kasir POS</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Produk aktif di POS
                                        akan otomatis tampil dengan harga terkini dan tombol pesan via WhatsApp.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_pos_products" value="1"
                                        {{ $landingPage->show_pos_products ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>

                            <!-- POS Products List Preview -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                                    Pratinjau Produk POS Terhubung (Maks. 8 Item Pertama)
                                </label>
                                @if ($posProducts->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach ($posProducts as $prod)
                                            <div
                                                class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-1">
                                                <div
                                                    class="flex items-center gap-1.5 text-[13px] font-medium text-black dark:text-white truncate">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                                                    <span class="truncate">{{ $prod->name }}</span>
                                                </div>
                                                <div class="text-[13px] font-bold tabular-nums text-black dark:text-white">
                                                    Rp
                                                    {{ number_format((float) ($prod->selling_price ?? 0), 0, ',', '.') }}
                                                </div>
                                                <div
                                                    class="text-[11px] text-black/45 dark:text-white/45 tabular-nums truncate">
                                                    Stok: {{ (int) ($prod->current_stock ?? 0) }}
                                                    {{ $prod->unit ?? 'unit' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        class="p-6 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[10px] border border-dashed border-black/10 dark:border-white/10">
                                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('landing_page.belum_ada_produk_pos_yang') }}
                                            aktif. Kelola produk di menu <a href="{{ route('products.index') }}"
                                                class="text-[#007AFF] font-medium hover:underline">{{ __('common.master_data') }} &gt; {{ __('products.title') }}</a>.</p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 6: GALLERY / GALERI FOTO & SUASANA BISNIS                -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'gallery'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.6_galeri_suasana_tempat_usaha') }}</h2>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-semibold tabular-nums bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70"
                                            x-text="(galleryItems.length + newGalleryUploads.length) + ' / 20 Foto'"></span>
                                    </div>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan suasana
                                        tempat, hasil karya, foto produk, dan kegiatan bisnis dalam grid modern.</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <label
                                        class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.gallery"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="showAddUrlModal = true"
                                        class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 text-[12px] font-medium flex items-center gap-1.5 transition cursor-pointer">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Tambah URL</span>
                                    </button>
                                    <button type="button" @click="clearAllGallery()"
                                        x-show="galleryItems.length > 0 || newGalleryUploads.length > 0"
                                        class="h-8 px-3 rounded-[8px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[#FF3B30] text-[12px] font-semibold flex items-center gap-1.5 transition cursor-pointer">
                                        <span>Kosongkan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Gallery Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul
                                        Bagian Galeri</label>
                                    <input type="text" name="gallery_title" x-model="form.gallery_title"
                                        maxlength="120" placeholder="Galeri &amp; Suasana Toko Kami"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Deskripsi
                                        / Subtitle Galeri</label>
                                    <textarea name="gallery_subtitle" x-model="form.gallery_subtitle" maxlength="500" rows="2"
                                        placeholder="{{ __('landing_page.dokumentasi_aktivitas_produk_unggulan_dan_kehangatan') }}"
                                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 py-2 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                                </div>
                            </div>

                            <!-- Upload Dropzone -->
                            <div
                                class="p-6 border-2 border-dashed border-black/10 dark:border-white/10 hover:border-[#007AFF]/50 bg-black/[0.02] dark:bg-white/[0.02] rounded-[14px] text-center transition-all group relative">
                                <input type="file" multiple accept="image/jpeg,image/png,image/webp"
                                    @change="handleGalleryFiles($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                                    <div
                                        class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                        </svg>
                                    </div>
                                    <div class="text-[13px] font-semibold text-black dark:text-white">
                                        <span class="text-[#007AFF]">Klik untuk memilih foto</span> atau seret file ke area
                                        ini
                                    </div>
                                    <p class="text-[11px] text-black/45 dark:text-white/45 max-w-md">
                                        Format: JPG, PNG, WebP (maks. 4 MB per foto). Foto otomatis tampil dalam rasio
                                        persegi rapi di website publik.
                                    </p>
                                </div>
                            </div>

                            <!-- Gallery Photos Grid -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div
                                        class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                                        Foto Galeri Aktif &amp; Antrean Upload:
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div x-show="galleryItems.length === 0 && newGalleryUploads.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="text-[13px] font-semibold text-black dark:text-white">{{ __('landing_page.belum_ada_foto_di') }}
                                        galeri</div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 max-w-md mx-auto">Unggah foto
                                        dokumentasi suasana toko atau terapkan template industri.</p>
                                </div>

                                <!-- Grid of Photos -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
                                    x-show="galleryItems.length > 0 || newGalleryUploads.length > 0">

                                    <!-- Existing Saved Photos -->
                                    <template x-for="(item, idx) in galleryItems" :key="'saved-' + idx">
                                        <div
                                            class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-2 space-y-2 flex flex-col justify-between group transition-all">
                                            <div
                                                class="aspect-square rounded-[8px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.06] relative group/img flex items-center justify-center">
                                                <img :src="item.url" :alt="item.caption || 'Galeri Foto'"
                                                    class="w-full h-full object-cover">

                                                <!-- Top Badges & Controls -->
                                                <div
                                                    class="absolute top-1.5 inset-x-1.5 flex items-center justify-between pointer-events-none">
                                                    <span
                                                        class="px-1.5 py-0.5 rounded-[4px] bg-white/90 dark:bg-black/90 text-[10px] font-mono font-bold text-black dark:text-white shadow-xs"
                                                        x-text="'#' + (idx + 1)"></span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="moveGalleryItem(idx, -1)"
                                                            :disabled="idx === 0" title="{{ __('landing_page.geser_ke_kiri') }}"
                                                            class="w-6 h-6 rounded-[4px] bg-white/90 dark:bg-[#2C2C2E]/90 text-black/80 dark:text-white/80 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" @click="moveGalleryItem(idx, 1)"
                                                            :disabled="idx === galleryItems.length - 1"
                                                            title="{{ __('landing_page.geser_ke_kanan') }}"
                                                            class="w-6 h-6 rounded-[4px] bg-white/90 dark:bg-[#2C2C2E]/90 text-black/80 dark:text-white/80 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" @click="removeGalleryItem(idx)"
                                                            title="{{ __('landing_page.hapus_foto_ini') }}"
                                                            class="w-6 h-6 rounded-[4px] bg-[#FF3B30] text-white flex items-center justify-center shadow-xs transition">
                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Caption Input -->
                                            <div>
                                                <input type="text" x-model="item.caption"
                                                    placeholder="Caption foto..."
                                                    class="w-full h-7 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[6px] text-[16px] sm:text-[11px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Staged New Uploads -->
                                    <template x-for="(nItem, nIdx) in newGalleryUploads" :key="'new-' + nIdx">
                                        <div
                                            class="rounded-[12px] bg-[#007AFF]/5 border border-[#007AFF]/30 p-2 space-y-2 flex flex-col justify-between group transition-all">
                                            <div
                                                class="aspect-square rounded-[8px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.06] relative group/img flex items-center justify-center">
                                                <img :src="nItem.preview" :alt="nItem.caption || 'Foto Baru'"
                                                    class="w-full h-full object-cover">

                                                <div
                                                    class="absolute top-1.5 inset-x-1.5 flex items-center justify-between pointer-events-none">
                                                    <span
                                                        class="px-1.5 py-0.5 rounded-[4px] bg-[#007AFF] text-[10px] font-semibold text-white shadow-xs">Baru</span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="removeNewUpload(nIdx)"
                                                            title="{{ __('landing_page.batal_upload') }}"
                                                            class="w-6 h-6 rounded-[4px] bg-[#FF3B30] text-white flex items-center justify-center shadow transition cursor-pointer">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <input type="text" x-model="nItem.caption"
                                                    placeholder="Caption baru..."
                                                    class="w-full h-7 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[6px] text-[16px] sm:text-[11px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 7: TESTIMONIALS / ULASAN & TESTIMONI                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'testimonials'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.7_ulasan_testimoni_pelanggan') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bukti kepuasan nyata
                                        dari pelanggan yang meningkatkan konversi dan rasa percaya calon pembeli baru.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.testimonials"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addTestimonial"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Tambah Ulasan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Testimonials Repeater -->
                            <div class="space-y-3">
                                <template x-for="(testi, tIdx) in testimonials" :key="tIdx">
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="testi.name" placeholder="{{ __('landing_page.nama_pelanggan') }}"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <input type="text" x-model="testi.role" placeholder="{{ __('landing_page.kota_profesi') }}"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <span
                                                class="text-[#FF9500] text-[12px] font-bold font-mono whitespace-nowrap inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 fill-[#FF9500] text-[#FF9500]" viewBox="0 0 24 24" fill="currentColor">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                </svg>
                                                <span>5.0</span>
                                            </span>
                                            <button type="button" @click="removeTestimonial(tIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="{{ __('landing_page.hapus_ulasan') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <textarea x-model="testi.comment" rows="2" placeholder="{{ __('landing_page.ulasan_pengalaman_pelanggan') }}"
                                            class="w-full bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                    </div>
                                </template>

                                <div x-show="testimonials.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">{{ __('landing_page.belum_ada_testimoni') }}
                                        pelanggan</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Tambahkan testimoni positif
                                        dari pelanggan setia Anda untuk memperkuat reputasi toko.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 8: FAQ / TANYA JAWAB POPULER                             -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'faq'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        8. Tanya Jawab Populer (FAQ)
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Jawab pertanyaan yang
                                        paling sering ditanyakan untuk mempercepat keputusan pembelian.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.faq"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addFaq"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Tambah FAQ</span>
                                    </button>
                                </div>
                            </div>

                            <!-- FAQ Repeater -->
                            <div class="space-y-3">
                                <template x-for="(faq, fIdx) in faqs" :key="fIdx">
                                    <div
                                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-[#007AFF] font-mono shrink-0">Q:</span>
                                            <input type="text" x-model="faq.q"
                                                placeholder="{{ __('landing_page.pertanyaan_misal_apakah_melayani_pesan_antar') }}"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <button type="button" @click="removeFaq(fIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="{{ __('landing_page.hapus_faq') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span
                                                class="text-[12px] font-bold text-black/35 dark:text-white/35 font-mono shrink-0 mt-1.5">A:</span>
                                            <textarea x-model="faq.a" rows="2" placeholder="{{ __('landing_page.jawaban_yang_jelas_dan_ramah') }}"
                                                class="flex-1 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="faqs.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">{{ __('landing_page.belum_ada_pertanyaan') }}
                                        FAQ</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Tambahkan daftar tanya jawab
                                        untuk mempermudah calon pelanggan.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 9: CONTACT / KONTAK, WA, ALAMAT & SOSMED                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'contact'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.9_kontak_whatsapp_peta_lokasi') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Hubungkan pelanggan
                                        langsung ke nomor WhatsApp, telepon, email, dan rute navigasi Google Maps.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.contact"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Kontak</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- WhatsApp & Phone -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Nomor WhatsApp Bisnis <span class="text-[#007AFF] font-mono">(Format:
                                            628xxx)</span>
                                    </label>
                                    <input type="text" name="whatsapp_number" x-model="form.whatsapp_number"
                                        placeholder="6281234567890"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition mb-3">

                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Nomor Telepon Kantor / Toko (Opsional)
                                    </label>
                                    <input type="text" name="custom_phone" x-model="form.custom_phone"
                                        placeholder="{{ __('landing_page.021_1234567_atau_081234') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>

                                <!-- Email & Alamat -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Email Resmi Usaha (Opsional)
                                    </label>
                                    <input type="email" name="custom_email" x-model="form.custom_email"
                                        placeholder="{{ __('landing_page.kontak_bisnisanda_com') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition mb-3">

                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Google Maps Embed URL
                                    </label>
                                    <input type="text" name="google_maps_embed_url"
                                        x-model="form.google_maps_embed_url"
                                        placeholder="https://www.google.com/maps/embed?pb=..."
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Google Maps &gt; Bagikan
                                        &gt; Sematkan peta &gt; salin URL di <code class="text-[#007AFF]">src="..."</code>
                                    </p>
                                </div>
                            </div>

                            <!-- Pesan Sambutan WhatsApp -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Pesan Sambutan Otomatis WhatsApp
                                </label>
                                <textarea name="whatsapp_welcome_message" x-model="form.whatsapp_welcome_message" rows="2"
                                    placeholder="{{ __('landing_page.halo_saya_melihat_website_anda_dan') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- Alamat Fisik Lengkap -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Alamat Fisik Toko / Kantor / Workshop
                                </label>
                                <textarea name="custom_address" x-model="form.custom_address" rows="2"
                                    placeholder="{{ __('landing_page.jl_sudirman_no_123_kelurahan_kecamatan') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- SOCIAL MEDIA & MARKETPLACE LINKS -->
                            <div class="border-t border-black/5 dark:border-white/5 pt-4 space-y-3">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                                    Tautan Media Sosial &amp; Marketplace Toko
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach ([['key' => 'instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/...'], ['key' => 'tiktok', 'label' => 'TikTok', 'placeholder' => 'https://tiktok.com/@...'], ['key' => 'facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/...'], ['key' => 'youtube', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/...'], ['key' => 'tokopedia', 'label' => 'Tokopedia', 'placeholder' => 'https://tokopedia.com/...'], ['key' => 'shopee', 'label' => 'Shopee', 'placeholder' => 'https://shopee.co.id/...']] as $social)
                                        <div>
                                            <label
                                                class="text-[10px] font-semibold text-black/60 dark:text-white/60 block mb-1 uppercase tracking-wider">{{ $social['label'] }}</label>
                                            <input type="text" name="social_links[{{ $social['key'] }}]"
                                                value="{{ $landingPage->social_links[$social['key']] ?? '' }}"
                                                placeholder="{{ $social['placeholder'] }}"
                                                class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[16px] sm:text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 10: FOOTER / TATA LETAK & KONTEN FOOTER                  -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'footer'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">{{ __('landing_page.10_tata_letak_konten_footer') }}</h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Atur kolom navigasi,
                                        ringkasan brand, dan hak cipta di bagian bawah website.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.footer"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Footer</span>
                                </label>
                            </div>

                            <!-- Grid Columns Visibility -->
                            <div
                                class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                <div
                                    class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                    Pilihan Kolom Footer</div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_brand"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Brand &amp; Sosmed</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_navigation"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Navigasi</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_services"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Layanan</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_contact"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Kontak</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Deskripsi
                                    Singkat Footer</label>
                                <textarea name="footer_description" x-model="form.footer_description" maxlength="1000" rows="3"
                                    placeholder="{{ __('landing_page.deskripsi_ringkas_profil_usaha_untuk_ditampilkan') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul
                                        Kolom Navigasi</label>
                                    <input type="text" name="footer_navigation_title"
                                        x-model="form.footer_navigation_title" maxlength="80" placeholder="Navigasi"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul
                                        Kolom Layanan</label>
                                    <input type="text" name="footer_services_title"
                                        x-model="form.footer_services_title" maxlength="80" placeholder="{{ __('landing_page.layanan_kami') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul
                                        Kolom Kontak</label>
                                    <input type="text" name="footer_contact_title" x-model="form.footer_contact_title"
                                        maxlength="80" placeholder="Hubungi Kami"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Teks
                                        Tombol Aksi Footer</label>
                                    <input type="text" name="footer_cta_text" x-model="form.footer_cta_text"
                                        maxlength="100" placeholder="Hubungi via WhatsApp"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Teks
                                    Hak Cipta (Copyright)</label>
                                <input type="text" name="footer_copyright" x-model="form.footer_copyright"
                                    maxlength="255"
                                    placeholder="© {{ date('Y') }} {{ $business->name }}. All rights reserved."
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 11: SEO / OPTIMASI GOOGLE & META TAG                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'seo'" x-cloak class="space-y-5">
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                    11. Optimasi Google SEO &amp; Pratinjau Pencarian
                                </h2>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tingkatkan peringkat halaman
                                    bisnis Anda di Google Search agar calon pembeli di sekitar Anda mudah menemukan toko.
                                </p>
                            </div>

                            <!-- Meta Title -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Judul Halaman Google (Meta Title)
                                </label>
                                <input type="text" name="meta_title" x-model="form.meta_title"
                                    placeholder="{{ __('landing_page.business_name_layanan_produk_terpercaya') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <div class="flex justify-between text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    <span>Panjang ideal: 50–60 karakter</span>
                                    <span
                                        :class="(form.meta_title || '').length > 60 ? 'text-[#FF3B30] font-semibold' : ''"
                                        x-text="(form.meta_title || '').length + ' / 60'"></span>
                                </div>
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Deskripsi Google (Meta Description)
                                </label>
                                <textarea name="meta_description" x-model="form.meta_description" rows="3"
                                    placeholder="{{ __('landing_page.deskripsi_ringkas_bisnis_anda_yang_tampil') }}"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                                <div class="flex justify-between text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    <span>Panjang ideal: 120–160 karakter</span>
                                    <span
                                        :class="(form.meta_description || '').length > 160 ? 'text-[#FF3B30] font-semibold' : ''"
                                        x-text="(form.meta_description || '').length + ' / 160'"></span>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Kata Kunci Relevan (Meta Keywords)
                                </label>
                                <input type="text" name="meta_keywords" x-model="form.meta_keywords" maxlength="500"
                                    placeholder="kuliner jakarta, servis motor, katering enak, toko grosir"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Pisahkan tiap kata kunci
                                    dengan tanda koma.</p>
                            </div>

                            <!-- OG Image -->
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Gambar Pratinjau Sosial (Open Graph / WhatsApp Share)
                                </label>
                                <div x-show="previews.og || form.og_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.og || form.og_image_url" alt="OG Preview"
                                        class="w-32 h-16 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.og ? 'Pratinjau OG baru dipilih' : 'Gambar OG tersimpan aktif'"></span>
                                </div>
                                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'og')"
                                    class="w-full text-[16px] sm:text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_og_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30]">
                                    <span>Hapus gambar OG tersimpan</span>
                                </label>
                            </div>

                            <!-- LIVE GOOGLE SEARCH SERP PREVIEW -->
                            <div
                                class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1">
                                <div
                                    class="text-[10px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <span>Pratinjau Google Search</span>
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 font-mono truncate">
                                    {{ $publicUrl }}</div>
                                <div class="text-[14px] font-medium text-[#007AFF] hover:underline cursor-pointer truncate"
                                    x-text="form.meta_title || '{{ addslashes($business->name) }} - Layanan & Produk Terpercaya'">
                                </div>
                                <div class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    x-text="form.meta_description || 'Kunjungi halaman profil dan katalog layanan resmi kami. Hubungi langsung melalui WhatsApp.'">
                                </div>
                            </div>

                            <!-- Schema Markup Info Banner -->
                            <div
                                class="p-3.5 bg-[#34C759]/10 border border-[#34C759]/20 rounded-[10px] flex items-start gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0 mt-1.5"></span>
                                <div class="text-[12px] text-[#248A3D] dark:text-[#30D158] leading-relaxed">
                                    <strong class="font-semibold">Schema Markup Otomatis:</strong> Cooca secara otomatis
                                    menyematkan data terstruktur JSON-LD <code
                                        class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded">LocalBusiness</code>
                                    agar Google Maps dan pencarian lokal mengenali bisnis Anda secara akurat.
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- 4. STICKY ACTION BAR (macOS Sonoma Floating Toolbar) -->
            <!-- ========================================== -->
            <div
                class="fixed bottom-0 inset-x-0 lg:left-64 z-40 backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border-t border-black/5 dark:border-white/10 px-4 sm:px-8 py-3 flex flex-col sm:flex-row items-center justify-between gap-3 transition-colors">
                <div class="flex items-center gap-2 text-[12px]">
                    <span class="w-2 h-2 rounded-full"
                        :class="saving ? 'bg-[#FF9500] animate-ping' : 'bg-[#34C759]'"></span>
                    <span class="font-medium text-black/80 dark:text-white/80"
                        x-text="saving ? 'Menyimpan perubahan ke server...' : (saveMessage || 'Siap menyimpan perubahan.')"></span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" @click="openPreview()" :disabled="saving"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                        <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                        <span>Preview Live</span>
                    </button>

                    @if (\App\Support\Context::hasPermission('cms.manage'))
                        <button type="submit" :disabled="saving"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex-1 sm:flex-none disabled:opacity-50">
                            <svg x-show="saving" class="w-4 h-4 animate-spin text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="saving ? 'Menyimpan...' : '{{ __('common.save') }}'">{{ __('landing_page.simpan_semua_perubahan') }}</span>
                        </button>
                    @endif
                </div>
            </div>

        </form>

        <!-- Sticky Mobile Bottom Bar (Thumb-zone Quick Action) -->
        @if (\App\Support\Context::hasPermission('cms.manage'))
            <div class="fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border-t border-black/10 dark:border-white/10 p-3 block lg:hidden pb-[calc(12px+env(safe-area-inset-bottom))] shadow-lg">
                <div class="max-w-md mx-auto flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 block">Bagian Aktif:</span>
                        <span class="text-[12.5px] font-bold text-black dark:text-white truncate block"
                              x-text="(tabs.find(t => t.id === activeTab) || {}).label || 'Studio'"></span>
                    </div>
                    <button type="button" @click="saveAll()" :disabled="saving"
                        class="min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] text-white font-semibold text-[13px] hover:bg-[#0071E3] active:scale-95 transition-all inline-flex items-center gap-2 shadow-sm shrink-0 disabled:opacity-50">
                        <i x-show="!saving" data-lucide="check" class="w-4 h-4"></i>
                        <svg x-show="saving" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'">{{ __('common.save_changes') }}</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- ============================================================ -->
        <!-- MODAL: THEME INSPECTOR & 1-CLICK RECOMMENDATION (Apple HIG)  -->
        <!-- ============================================================ -->
        <template x-teleport="body">
            <div x-show="showThemeModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-3 sm:p-4 md:p-6 overflow-y-auto">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                    x-show="showThemeModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showThemeModal = false"
                    aria-hidden="true"></div>

                <!-- Dialog Box -->
                <div @click.away="showThemeModal = false"
                    @keydown.escape.window="showThemeModal = false"
                    role="dialog" aria-modal="true"
                    x-show="showThemeModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-3xl rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden my-auto flex flex-col max-h-[90vh]">

                    <!-- Modal Header -->
                    <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="w-4 h-4 rounded-full shadow-xs shrink-0"
                                  :style="`background-color: ${selectedThemeForModal?.primary_color || '#007AFF'}`"></span>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-[17px] font-bold text-black dark:text-white"
                                        x-text="selectedThemeForModal?.name || 'Detail Tema'"></h3>
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                          :style="`background-color: ${selectedThemeForModal?.primary_color}18; color: ${selectedThemeForModal?.primary_color};`"
                                          x-text="selectedThemeForModal?.industry"></span>
                                </div>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5"
                                   x-text="selectedThemeForModal?.description"></p>
                            </div>
                        </div>
                        <button type="button" @click="showThemeModal = false"
                            class="w-9 h-9 rounded-full text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition shrink-0 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-5 sm:p-6 space-y-5 overflow-y-auto">
                        <!-- Two Columns Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                            <!-- Left: Identity & Typography Preview -->
                            <div class="md:col-span-5 space-y-4">
                                <div class="p-4 rounded-[16px] border border-black/5 dark:border-white/10 flex flex-col justify-between gap-3 shadow-sm"
                                     :style="`background-color: ${selectedThemeForModal?.card_bg || '#FAFAFA'};`">
                                    <div>
                                        <span class="text-[9.5px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full mb-2 inline-block"
                                              :style="`background-color: ${selectedThemeForModal?.primary_color}20; color: ${selectedThemeForModal?.primary_color};`"
                                              x-text="selectedThemeForModal?.recommendation?.announcement_badge || 'Toko Resmi Terverifikasi'"></span>
                                        <h4 class="text-[18px] font-black text-neutral-900 leading-snug"
                                            :style="`font-family: '${selectedThemeForModal?.font_heading}', sans-serif;`"
                                            x-text="selectedThemeForModal?.recommendation?.headline || 'Headline Utama Industri'"></h4>
                                        <p class="text-[11.5px] text-neutral-600 mt-2 line-clamp-3 leading-relaxed"
                                           :style="`font-family: '${selectedThemeForModal?.font_body}', sans-serif;`"
                                           x-text="selectedThemeForModal?.recommendation?.subheadline"></p>
                                    </div>

                                    <div class="pt-3 border-t border-black/5 flex items-center justify-between">
                                        <span class="h-8 px-3 rounded-[8px] text-[11px] font-bold text-white flex items-center shadow-xs"
                                              :style="`background-color: ${selectedThemeForModal?.primary_color};`"
                                              x-text="selectedThemeForModal?.recommendation?.cta_primary_text || 'Lihat Produk'"></span>
                                        <span class="text-[10px] text-neutral-500 font-mono" x-text="selectedThemeForModal?.font_heading"></span>
                                    </div>
                                </div>

                                <!-- Specs List -->
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 text-[11.5px]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/50 dark:text-white/50">Font Heading:</span>
                                        <strong class="text-black dark:text-white font-mono" x-text="selectedThemeForModal?.font_heading"></strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/50 dark:text-white/50">Font Body:</span>
                                        <strong class="text-black dark:text-white font-mono" x-text="selectedThemeForModal?.font_body"></strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/50 dark:text-white/50">Warna Utama:</span>
                                        <div class="flex items-center gap-1.5 font-mono uppercase font-bold" :style="`color: ${selectedThemeForModal?.primary_color};`">
                                            <span class="w-3 h-3 rounded-full border border-black/10" :style="`background-color: ${selectedThemeForModal?.primary_color};`"></span>
                                            <span x-text="selectedThemeForModal?.primary_color"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/50 dark:text-white/50">Warna Aksen:</span>
                                        <div class="flex items-center gap-1.5 font-mono uppercase font-bold" :style="`color: ${selectedThemeForModal?.accent_color};`">
                                            <span class="w-3 h-3 rounded-full border border-black/10" :style="`background-color: ${selectedThemeForModal?.accent_color};`"></span>
                                            <span x-text="selectedThemeForModal?.accent_color"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Recommendation & Trust Pillars -->
                            <div class="md:col-span-7 space-y-4">
                                <div>
                                    <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block mb-2">
                                        Rekomendasi Copywriting Industri Otentik
                                    </span>
                                    <div class="space-y-2 text-[12px]">
                                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-semibold">Headline Rekomendasi:</span>
                                            <span class="font-medium text-black dark:text-white" x-text="selectedThemeForModal?.recommendation?.headline"></span>
                                        </div>
                                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-semibold">Subheadline Rekomendasi:</span>
                                            <span class="text-black/70 dark:text-white/70" x-text="selectedThemeForModal?.recommendation?.subheadline"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                                <span class="text-[10px] text-black/40 dark:text-white/40 block font-semibold">Badge Promosi:</span>
                                                <span class="font-medium text-black dark:text-white text-[11px]" x-text="selectedThemeForModal?.recommendation?.announcement_badge"></span>
                                            </div>
                                            <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                                <span class="text-[10px] text-black/40 dark:text-white/40 block font-semibold">Tombol Utama (CTA):</span>
                                                <span class="font-medium text-black dark:text-white text-[11px]" x-text="selectedThemeForModal?.recommendation?.cta_primary_text"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4 Sector Trust Pillars -->
                                <div>
                                    <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block mb-2">
                                        Pilar Kepercayaan Sektor (Trust Pillars)
                                    </span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="(pillar, pIdx) in (selectedThemeForModal?.recommendation?.trust_pillars || [])" :key="pIdx">
                                            <div class="p-2.5 rounded-[10px] bg-emerald-500/5 border border-emerald-500/20 text-[11px] text-emerald-800 dark:text-emerald-300 font-medium flex items-center gap-2">
                                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                                <span x-text="pillar"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-5 sm:px-6 py-3.5 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-2">
                            <a :href="`{{ $publicUrl }}?preview=1&preview_theme=${selectedThemeForModal?.id}`" target="_blank"
                               class="h-9 px-3.5 rounded-[10px] border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5 text-[12px] font-semibold text-black/80 dark:text-white/80 transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                <span>{{ __('landing_page.test_drive_di_tab_baru') }}</span>
                            </a>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" @click="showThemeModal = false"
                                class="h-9 px-3.5 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="button" @click="applyTheme(selectedThemeForModal?.id, false)"
                                class="h-9 px-3.5 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold transition cursor-pointer">
                                Terapkan Desain &amp; Palet Saja
                            </button>
                            <button type="button" @click="applyTheme(selectedThemeForModal?.id, true)"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                <span>Terapkan Desain + Copywriting</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <!-- ============================================================ -->
        <!-- MODAL: LIVE PREVIEW DRAWER (Apple Sheet Style)               -->
        <!-- ============================================================ -->
        <template x-teleport="body">
            <div x-show="showPreview" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 overflow-hidden">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    x-show="showPreview"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showPreview = false"
                    aria-hidden="true"></div>

                <div @click.away="showPreview = false"
                    @keydown.escape.window="showPreview = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-live-preview-title"
                    x-show="showPreview"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative z-10 h-full max-w-6xl w-full mx-auto bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[20px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-col border border-black/5 dark:border-white/10 my-auto">
                    <div
                        class="shrink-0 px-4 sm:px-6 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <span id="modal-live-preview-title" class="text-[14px] font-semibold text-black dark:text-white">Pratinjau Langsung (Live Preview)</span>
                            <span
                                class="text-[11px] text-black/45 dark:text-white/45 font-mono hidden sm:inline">{{ $publicUrl }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="previewUrl" target="_blank"
                                class="min-h-[44px] px-3 rounded-[10px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/10 flex items-center gap-1 transition-all">
                                <span>{{ __('landing_page.buka_tab_baru') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                            <button type="button" @click="showPreview = false"
                                class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition cursor-pointer"
                                aria-label="{{ __('landing_page.tutup_preview') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </div>
                    <iframe :src="previewUrl" title="{{ __('landing_page.preview_halaman_publik_bisnis') }}"
                        class="w-full h-full border-0 bg-white"></iframe>
                </div>
            </div>
        </template>

        <!-- ============================================================ -->
        <!-- MODAL: 25+ INDUSTRY PRESETS SELECTOR (Apple Sheet Style)     -->
        <!-- ============================================================ -->
        <template x-teleport="body">
            <div x-show="showPresetModal"
                x-cloak
                x-data="{ presetCategory: 'all' }"
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-4 overflow-y-auto">

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    x-show="showPresetModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showPresetModal = false"
                    aria-hidden="true"></div>

                <div @click.away="showPresetModal = false"
                    @keydown.escape.window="showPresetModal = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-presets-title"
                    x-show="showPresetModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 rounded-[24px] max-w-5xl w-full max-h-[90vh] flex flex-col shadow-[0_25px_60px_rgba(0,0,0,0.3)] overflow-hidden my-auto">

                    <!-- Modal Header -->
                    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-start justify-between gap-3">
                        <div>
                            <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-1">
                                Arsitektur Konten Multisektor
                            </div>
                            <h3 id="modal-presets-title" class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight">{{ __('landing_page.preset_25_sektor_industri_bisnis_indonesia') }}</h3>
                            <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                                Pilih sektor bisnis Anda untuk menyelaraskan headline, narasi profil, 4 pilar nilai, katalog layanan, FAQ, dan ulasan secara otomatis.
                            </p>
                        </div>
                        <button type="button" @click="showPresetModal = false"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition shrink-0 cursor-pointer"
                            aria-label="{{ __('landing_page.tutup_pemilih_preset') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Category Segmented Tabs -->
                    <div class="px-4 sm:px-5 py-2.5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 overflow-x-auto no-scrollbar">
                        <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[12px] gap-1 text-[12px] font-medium">
                            <button type="button" @click="presetCategory = 'all'"
                                :class="presetCategory === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">
                                {{ __('common.all') }} (25)
                            </button>
                            <button type="button" @click="presetCategory = 'fnb'"
                                :class="presetCategory === 'fnb' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">
                                Kuliner &amp; F&amp;B
                            </button>
                            <button type="button" @click="presetCategory = 'manufacturing'"
                                :class="presetCategory === 'manufacturing' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">
                                Manufaktur &amp; Bengkel
                            </button>
                            <button type="button" @click="presetCategory = 'retail'"
                                :class="presetCategory === 'retail' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">
                                Retail &amp; Apotek
                            </button>
                            <button type="button" @click="presetCategory = 'service'"
                                :class="presetCategory === 'service' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">{{ __('landing_page.jasa_layanan') }}</button>
                            <button type="button" @click="presetCategory = 'trading'"
                                :class="presetCategory === 'trading' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-[9px] transition-all">
                                Distribusi &amp; Agro
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body: 25 Industry Cards -->
                    <div class="p-4 sm:p-5 overflow-y-auto space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($industries as $ind)
                                <div x-show="presetCategory === 'all' || presetCategory === '{{ $ind['category'] ?? 'retail' }}'"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="p-4 rounded-[16px] border border-black/5 dark:border-white/5 bg-white/60 dark:bg-white/[0.02] hover:border-[#007AFF]/40 hover:shadow-sm transition-all flex flex-col justify-between group">
                                    <div>
                                        <div class="flex items-center justify-between mb-2.5">
                                            <div class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/70 dark:text-white/80 group-hover:text-[#007AFF] transition-colors">
                                                <i data-lucide="{{ $ind['icon'] ?? 'store' }}" class="w-4 h-4"></i>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-medium uppercase tracking-wider text-black/40 dark:text-white/40">
                                                    {{ match($ind['category'] ?? '') {
                                                        'fnb' => 'F&B',
                                                        'manufacturing' => 'MFG',
                                                        'retail' => 'Retail',
                                                        'service' => 'Jasa',
                                                        'trading' => 'Agro',
                                                        default => 'Bisnis'
                                                    } }}
                                                </span>
                                                <span class="w-3 h-3 rounded-full border border-black/10 dark:border-white/10"
                                                    style="background-color: {{ $ind['theme_color'] ?? '#007AFF' }}"></span>
                                            </div>
                                        </div>
                                        <h4 class="text-[13.5px] font-bold text-black dark:text-white leading-snug mb-1">
                                            {{ $ind['name'] }}
                                        </h4>
                                        <p class="text-[11.5px] text-black/55 dark:text-white/55 leading-relaxed line-clamp-2">
                                            {{ $ind['description'] }}
                                        </p>
                                    </div>
                                    <div class="mt-3.5 pt-2.5 border-t border-black/5 dark:border-white/5">
                                        <button type="button"
                                            @click="applyPreset('{{ $ind['id'] }}', $event.currentTarget)"
                                            class="w-full min-h-[44px] h-11 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF] text-[#007AFF] hover:text-white text-[12px] font-semibold active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Terapkan Template</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row justify-between items-center gap-2 text-[12px] text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.01]">
                        <p>Menerapkan template otomatis menyesuaikan headline, 4 pilar nilai, katalog layanan, FAQ, dan ulasan.</p>
                        <button type="button" @click="showPresetModal = false"
                            class="min-h-[44px] px-5 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-medium transition cursor-pointer">{{ __('common.close') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ============================================================ -->
        <!-- MODAL: ADD PHOTO BY URL (Apple Alert/Sheet Style)            -->
        <!-- ============================================================ -->
        <template x-teleport="body">
            <div x-show="showAddUrlModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    x-show="showAddUrlModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showAddUrlModal = false"
                    aria-hidden="true"></div>

                <div class="relative z-10 w-full max-w-md bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[20px] p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4 my-auto"
                    @click.away="showAddUrlModal = false"
                    @keydown.escape.window="showAddUrlModal = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-add-url-title"
                    x-show="showAddUrlModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">
                    <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                        <h3 id="modal-add-url-title" class="font-semibold text-[15px] text-black dark:text-white">
                            Tambah Foto Galeri via URL
                        </h3>
                        <button type="button" @click="showAddUrlModal = false"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition cursor-pointer"
                            aria-label="{{ __('landing_page.tutup_modal_tambah_url') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label
                                class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">
                                URL Gambar <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" x-model="newUrlInput"
                                placeholder="https://example.com/foto.jpg atau /storage/..."
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">
                                Caption Foto (Opsional)
                            </label>
                            <input type="text" x-model="newCaptionInput"
                                placeholder="{{ __('landing_page.contoh_suasana_ruang_tunggu_dan_kasir') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/5 dark:border-white/5">
                        <button type="button" @click="showAddUrlModal = false"
                            class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 transition cursor-pointer">{{ __('common.cancel') }}</button>
                        <button type="button" @click="addGalleryByUrl()" :disabled="!newUrlInput.trim()"
                            class="min-h-[44px] px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold disabled:opacity-50 transition cursor-pointer">Tambahkan</button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    @php
        $sectionVisibilityDefaults = array_merge(
            [
                'hero' => true,
                'about' => true,
                'products' => true,
                'services' => true,
                'gallery' => true,
                'testimonials' => true,
                'faq' => true,
                'contact' => true,
                'footer' => true,
                'footer_brand' => true,
                'footer_navigation' => true,
                'footer_services' => true,
                'footer_contact' => true,
            ],
            $landingPage->section_visibility ?? [],
        );
    @endphp

    <script>
        function landingPageEditor() {
            return {
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'general',
                showPresetModal: false,
                showPreview: false,
                previewUrl: @json($publicUrl),
                copied: false,
                saving: false,
                galleryFileCount: 0,
                saveMessage: '',
                saveError: '',
                isPublished: @json((bool) $landingPage->is_published),
                themeFilterCategory: 'all',
                themeSearch: '',

                tabs: [{
                        id: 'general',
                        label: 'Identitas & Tema'
                    },
                    {
                        id: 'hero',
                        label: 'Banner Hero'
                    },
                    {
                        id: 'about',
                        label: 'Profil & Nilai'
                    },
                    {
                        id: 'services',
                        label: 'Katalog Layanan'
                    },
                    {
                        id: 'products',
                        label: 'Produk POS'
                    },
                    {
                        id: 'gallery',
                        label: 'Galeri Suasana'
                    },
                    {
                        id: 'testimonials',
                        label: 'Ulasan Pelanggan'
                    },
                    {
                        id: 'faq',
                        label: 'Tanya Jawab (FAQ)'
                    },
                    {
                        id: 'contact',
                        label: 'Kontak & Lokasi'
                    },
                    {
                        id: 'footer',
                        label: 'Konten Footer'
                    },
                    {
                        id: 'seo',
                        label: 'Optimasi Google SEO'
                    },
                ],

                colorSwatches: [
                    {
                        hex: '#8B5A2B',
                        label: 'Artisan Warm Wood (Default)'
                    },
                    {
                        hex: '#007AFF',
                        label: 'System Blue'
                    },
                    {
                        hex: '#34C759',
                        label: 'System Green'
                    },
                    {
                        hex: '#0EA5E9',
                        label: 'Sky'
                    },
                    {
                        hex: '#5856D6',
                        label: 'System Indigo'
                    },
                    {
                        hex: '#AF52DE',
                        label: 'System Purple'
                    },
                    {
                        hex: '#FF2D55',
                        label: 'System Pink'
                    },
                    {
                        hex: '#FF9500',
                        label: 'System Orange'
                    },
                    {
                        hex: '#FF3B30',
                        label: 'System Red'
                    },
                    {
                        hex: '#30B0C7',
                        label: 'System Teal'
                    },
                    {
                        hex: '#8E8E93',
                        label: 'System Gray'
                    },
                ],

                sectionVisibility: @json($sectionVisibilityDefaults),

                activePages: @json($landingPage->getActivePages()),
                customLabels: @json($landingPage->custom_labels ?? []),
                allThemesMap: @json($themePresets),
                curatedFonts: @json($curatedFonts ?? []),
                selectedThemeForModal: null,
                showThemeModal: false,

                openThemeModal(key) {
                    if (!this.allThemesMap || !this.allThemesMap[key]) return;
                    this.selectedThemeForModal = this.allThemesMap[key];
                    this.showThemeModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                applyTheme(key, withCopy = false) {
                    const t = this.allThemesMap ? this.allThemesMap[key] : null;
                    if (!t) return;
                    this.form.theme_preset = key;
                    this.form.theme_color = t.primary_color;
                    this.form.font_family = t.font_heading;
                    if (withCopy && t.recommendation) {
                        if (t.recommendation.headline) this.form.headline = t.recommendation.headline;
                        if (t.recommendation.subheadline) this.form.subheadline = t.recommendation.subheadline;
                        if (t.recommendation.announcement_badge) this.form.announcement_badge = t.recommendation.announcement_badge;
                        if (t.recommendation.cta_primary_text) this.form.cta_primary_text = t.recommendation.cta_primary_text;
                    }
                    this.showThemeModal = false;
                    this.saveMessage = `Tema ${t.name} berhasil diterapkan!`;
                    setTimeout(() => { this.saveMessage = ''; }, 4000);
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                form: {
                    industry_preset: @json($landingPage->industry_preset ?? 'retail'),
                    theme_preset: @json($landingPage->getThemePreset()),
                    font_family: @json($landingPage->font_family ?? ''),
                    theme_color: @json($landingPage->theme_color ?? '#8B5A2B'),
                    dark_mode: @json((bool) ($landingPage->dark_mode ?? false)),
                    announcement_badge: @json($landingPage->announcement_badge ?? ''),
                    headline: @json($landingPage->headline ?? ''),
                    subheadline: @json($landingPage->subheadline ?? ''),
                    cta_primary_text: @json($landingPage->cta_primary_text ?? ''),
                    cta_primary_url: @json($landingPage->cta_primary_url ?? ''),
                    cta_secondary_text: @json($landingPage->cta_secondary_text ?? ''),
                    cta_secondary_url: @json($landingPage->cta_secondary_url ?? ''),
                    hero_image_url: @json($landingPage->hero_image_url ?? ''),
                    logo_url: @json($landingPage->logo_url ?? ''),
                    about_title: @json($landingPage->about_title ?? ''),
                    about_story: @json($landingPage->about_story ?? ''),
                    services_title: @json($landingPage->services_title ?? ''),
                    services_subtitle: @json($landingPage->services_subtitle ?? ''),
                    whatsapp_number: @json($landingPage->whatsapp_number ?? ''),
                    custom_phone: @json($landingPage->custom_phone ?? ''),
                    whatsapp_welcome_message: @json($landingPage->whatsapp_welcome_message ?? ''),
                    custom_email: @json($landingPage->custom_email ?? ''),
                    custom_address: @json($landingPage->custom_address ?? ''),
                    google_maps_embed_url: @json($landingPage->google_maps_embed_url ?? ''),
                    gallery_title: @json($landingPage->gallery_title ?? ''),
                    gallery_subtitle: @json($landingPage->gallery_subtitle ?? ''),
                    meta_title: @json($landingPage->meta_title ?? ''),
                    meta_description: @json($landingPage->meta_description ?? ''),
                    meta_keywords: @json($landingPage->meta_keywords ?? ''),
                    footer_description: @json($landingPage->footer_description ?? ''),
                    footer_navigation_title: @json($landingPage->footer_navigation_title ?? ''),
                    footer_services_title: @json($landingPage->footer_services_title ?? ''),
                    footer_contact_title: @json($landingPage->footer_contact_title ?? ''),
                    footer_cta_text: @json($landingPage->footer_cta_text ?? ''),
                    footer_copyright: @json($landingPage->footer_copyright ?? ''),
                },

                previews: {
                    logo: '',
                    hero: '',
                    about: '',
                    og: ''
                },

                @php
                    $initialGallery = collect($landingPage->gallery_images ?? [])
                        ->map(function ($img) {
                            if (is_array($img)) {
                                return [
                                    'url' => (string) ($img['url'] ?? ''),
                                    'caption' => (string) ($img['caption'] ?? ''),
                                ];
                            }
                            return [
                                'url' => (string) $img,
                                'caption' => '',
                            ];
                        })
                        ->filter(fn($item) => filled($item['url']))
                        ->values();
                @endphp
                galleryItems: {!! json_encode($initialGallery, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
                newGalleryUploads: [],
                showAddUrlModal: false,
                newUrlInput: '',
                newCaptionInput: '',

                values: @json($landingPage->values ?? []),
                services: @json($landingPage->custom_services ?? []),
                faqs: @json($landingPage->faqs ?? []),
                testimonials: @json($landingPage->testimonials ?? []),
                operationalHours: @json($landingPage->operational_hours ?? []),

                init() {
                    const urlTab = new URLSearchParams(window.location.search).get('tab');
                    if (urlTab && this.tabs.some(t => t.id === urlTab)) {
                        this.activeTab = urlTab;
                    }

                    this.$watch('activeTab', (val) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', val);
                        window.history.replaceState({}, '', url);
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    });

                    if (!this.values || this.values.length === 0) {
                        this.values = [{
                                title: 'Kualitas Terjamin',
                                description: 'Standar mutu dan pengerjaan terbaik dengan garansi kepuasan.',
                                icon: 'shield-check'
                            },
                            {
                                title: 'Pelayanan Cepat',
                                description: 'Responsif dan tepat waktu untuk setiap kebutuhan pelanggan.',
                                icon: 'zap'
                            },
                            {
                                title: 'Harga Transparan',
                                description: 'Biaya jelas tanpa ada pungutan tersembunyi.',
                                icon: 'award'
                            },
                            {
                                title: 'Konsultasi Ramah',
                                description: 'Dapatkan rekomendasi terbaik dari tim berpengalaman kami.',
                                icon: 'star'
                            }
                        ];
                    }
                    if (!this.operationalHours || this.operationalHours.length === 0) {
                        this.operationalHours = [{
                                day: 'Senin',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Selasa',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Rabu',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Kamis',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Jumat',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Sabtu',
                                hours: '08:00 - 15:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Minggu',
                                hours: 'Tutup',
                                is_open: false
                            },
                        ];
                    }
                },

                handleImagePreview(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Tidak Didukung',
                                text: 'Hanya format JPG, PNG, atau WebP yang diperbolehkan.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    if (file.size > 4 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ukuran Terlalu Besar',
                                text: 'Ukuran file gambar maksimal 4 MB.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.previews[type] = URL.createObjectURL(file);
                },

                handleServiceImagePreview(event, sIdx) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Tidak Didukung',
                                text: 'Hanya format JPG, PNG, atau WebP yang diperbolehkan.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    if (file.size > 4 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ukuran Terlalu Besar',
                                text: 'Ukuran file gambar maksimal 4 MB.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.services[sIdx].preview_url = URL.createObjectURL(file);
                },

                handleGalleryFiles(event) {
                    const files = Array.from(event.target.files || []);
                    if (!files.length) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    for (let file of files) {
                        if (!validTypes.includes(file.type)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Format Tidak Didukung',
                                    text: `File "${file.name}" bukan format JPG, PNG, atau WebP.`,
                                    confirmButtonColor: '#007AFF'
                                });
                            }
                            continue;
                        }
                        if (file.size > 4 * 1024 * 1024) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ukuran Terlalu Besar',
                                    text: `File "${file.name}" melebihi batas 4 MB.`,
                                    confirmButtonColor: '#007AFF'
                                });
                            }
                            continue;
                        }
                        this.newGalleryUploads.push({
                            file: file,
                            preview: URL.createObjectURL(file),
                            caption: ''
                        });
                    }
                    event.target.value = '';
                },

                addGalleryByUrl() {
                    const url = this.newUrlInput.trim();
                    if (!url) return;
                    this.galleryItems.push({
                        url: url,
                        caption: this.newCaptionInput.trim()
                    });
                    this.newUrlInput = '';
                    this.newCaptionInput = '';
                    this.showAddUrlModal = false;
                },

                removeGalleryItem(index) {
                    this.galleryItems.splice(index, 1);
                },

                removeNewUpload(index) {
                    this.newGalleryUploads.splice(index, 1);
                },

                moveGalleryItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.galleryItems.length) return;
                    const item = this.galleryItems.splice(index, 1)[0];
                    this.galleryItems.splice(targetIndex, 0, item);
                },

                clearAllGallery() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Kosongkan Semua Galeri?',
                            text: 'Semua foto aktif dan antrean foto baru akan dihapus dari galeri.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Kosongkan',
                            cancelButtonText: '{{ __('common.cancel') }}',
                            confirmButtonColor: '#FF3B30',
                            cancelButtonColor: '#007AFF'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.galleryItems = [];
                                this.newGalleryUploads = [];
                            }
                        });
                    } else {
                        this.galleryItems = [];
                        this.newGalleryUploads = [];
                    }
                },

                addService() {
                    this.services.push({
                        title: '',
                        price: '',
                        description: '',
                        badge: '',
                        preview_url: ''
                    });
                },

                removeService(index) {
                    this.services.splice(index, 1);
                },

                addTestimonial() {
                    this.testimonials.push({
                        name: '',
                        role: 'Pelanggan',
                        comment: '',
                        rating: 5
                    });
                },

                removeTestimonial(index) {
                    this.testimonials.splice(index, 1);
                },

                addFaq() {
                    this.faqs.push({
                        q: '',
                        a: ''
                    });
                },

                removeFaq(index) {
                    this.faqs.splice(index, 1);
                },

                copyLink(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.copied = true;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Link website publik berhasil disalin!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                        setTimeout(() => {
                            this.copied = false;
                        }, 2500);
                    });
                },

                applyPreset(presetKey, button) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Terapkan Template Industri?',
                            text: 'Konten draft Anda akan diselaraskan sesuai standar sektor industri yang dipilih.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Terapkan',
                            cancelButtonText: '{{ __('common.cancel') }}',
                            confirmButtonColor: '#007AFF',
                            cancelButtonColor: '#8E8E93'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.executeApplyPreset(presetKey, button);
                            }
                        });
                    } else {
                        this.executeApplyPreset(presetKey, button);
                    }
                },

                executeApplyPreset(presetKey, button) {
                    const btn = button;
                    if (btn) {
                        btn.textContent = 'Memuat...';
                        btn.disabled = true;
                    }

                    fetch('{{ route('landing-page.preset') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                preset: presetKey
                            })
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Gagal memuat template.');
                            return data;
                        })
                        .then(data => {
                            if (data.preset) {
                                const p = data.preset;
                                this.form.industry_preset = presetKey;
                                if (p.theme_preset) this.form.theme_preset = p.theme_preset;
                                if (p.theme_color) this.form.theme_color = p.theme_color;
                                if (p.headline) this.form.headline = p.headline;
                                if (p.subheadline) this.form.subheadline = p.subheadline;
                                if (p.announcement_badge) this.form.announcement_badge = p.announcement_badge;
                                if (p.cta_primary_text) this.form.cta_primary_text = p.cta_primary_text;
                                if (p.cta_secondary_text) this.form.cta_secondary_text = p.cta_secondary_text;
                                if (p.about_title) this.form.about_title = p.about_title;
                                if (p.about_story) this.form.about_story = p.about_story;
                                if (p.values) this.values = p.values;
                                if (p.services) this.services = p.services;
                                if (p.faqs) this.faqs = p.faqs;
                                if (p.testimonials) this.testimonials = p.testimonials;
                                this.showPresetModal = false;
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Template Berhasil Diterapkan!',
                                        text: 'Seluruh teks profil, layanan, FAQ, dan ulasan telah diperbarui.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: error.message || 'Gagal menerapkan template. Silakan coba kembali.',
                                    confirmButtonColor: '#FF3B30'
                                });
                            }
                        })
                        .finally(() => {
                            if (btn) {
                                btn.textContent = 'Terapkan';
                                btn.disabled = false;
                            }
                        });
                },

                async openPreview() {
                    const saved = await this.saveAll();
                    if (!saved) return;

                    this.previewUrl = @json($publicUrl) + '?preview=' + Date.now();
                    this.showPreview = true;
                },

                async saveAll() {
                    if (this.saving) return;
                    this.saving = true;
                    this.saveMessage = '';
                    this.saveError = '';

                    try {
                        const formEl = document.getElementById('landingPageForm');
                        const formData = new FormData(formEl);

                        // Selaraskan nilai dark_mode dan theme_color dari form state
                        formData.set('dark_mode', this.form.dark_mode ? '1' : '0');
                        formData.set('theme_color', this.form.theme_color);
                        formData.set('theme_preset', this.form.theme_preset);
                        formData.set('active_pages_json', JSON.stringify(this.activePages));
                        formData.set('custom_labels_json', JSON.stringify(this.customLabels));

                        // Sinkronkan galleryItems JSON dan unggahan foto baru beserta captionnya
                        formData.set('gallery_images_json', JSON.stringify(this.galleryItems));
                        formData.delete('gallery_images[]');
                        formData.delete('new_gallery_captions[]');
                        this.newGalleryUploads.forEach((item) => {
                            if (item.file) {
                                formData.append('gallery_images[]', item.file);
                                formData.append('new_gallery_captions[]', item.caption || '');
                            }
                        });

                        const response = await fetch('{{ route('landing-page.update') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            const messages = data.errors ? Object.values(data.errors).flat() : [];
                            const errorMsg = messages[0] || data.message || 'Perubahan gagal disimpan.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: '{{ __('landing_page.periksa_kembali_input_data') }}
                                    text: errorMsg,
                                    confirmButtonColor: '#FF9500'
                                });
                            }
                            throw new Error(errorMsg);
                        }
                        this.saveMessage = data.message || 'Perubahan berhasil disimpan.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: data.message || 'Website bisnis Anda berhasil diperbarui.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        if (data.landing_page) {
                            if (typeof data.landing_page.dark_mode !== 'undefined') this.form.dark_mode = Boolean(data.landing_page.dark_mode);
                            if (data.landing_page.theme_color) this.form.theme_color = data.landing_page.theme_color;
                            if (data.landing_page.hero_image_url) this.form.hero_image_url = data.landing_page
                                .hero_image_url;
                            if (data.landing_page.logo_url) this.form.logo_url = data.landing_page.logo_url;
                            if (data.landing_page.about_image_url) this.form.about_image_url = data.landing_page
                                .about_image_url;
                            if (data.landing_page.og_image_url) this.form.og_image_url = data.landing_page.og_image_url;
                            if (data.landing_page.gallery_images) {
                                this.galleryItems = (data.landing_page.gallery_images || []).map(img => {
                                    if (typeof img === 'object' && img !== null) {
                                        return {
                                            url: img.url || '',
                                            caption: img.caption || ''
                                        };
                                    }
                                    return {
                                        url: String(img),
                                        caption: ''
                                    };
                                }).filter(item => item.url);
                                this.newGalleryUploads = [];
                            }
                        }
                        return true;
                    } catch (error) {
                        this.saveError = error.message || 'Perubahan gagal disimpan.';
                        return false;
                    } finally {
                        this.saving = false;
                    }
                },

                async togglePublish() {
                    if (this.saving) return;
                    this.saving = true;
                    this.saveError = '';
                    try {
                        const response = await fetch('{{ route('landing-page.toggle-publish') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Status publikasi gagal diubah.');
                        this.isPublished = Boolean(data.is_published);
                        this.saveMessage = data.message || 'Status publikasi diperbarui.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Diperbarui!',
                                text: data.message || 'Status publikasi website berhasil diubah.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } catch (error) {
                        this.saveError = error.message || 'Status publikasi gagal diubah.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: error.message || 'Status publikasi gagal diubah.',
                                confirmButtonColor: '#FF3B30'
                            });
                        }
                    } finally {
                        this.saving = false;
                    }
                }
            };
        }
    </script>
@endsection
