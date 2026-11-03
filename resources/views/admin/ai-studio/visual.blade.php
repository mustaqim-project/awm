@extends('layouts.admin', [
    'title' => 'AI Studio Visual Creator - Admin Console',
    'headerTitle' => 'Studio Visual & Materi Media Sosial AI',
    'headerSubtitle' => 'Rancang konten visual promosi platform, jelajahi 460+ gambar referensi gaya, dan publikasikan langsung ke media sosial',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-12" x-data="adminAiStudioVisual()">

    <!-- ========================================== -->
    <!-- 1. HEADER COCKPIT & TAB NAVIGATION        -->
    <!-- ========================================== -->
    <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-[12px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </span>
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight text-black dark:text-white">
                        AI Studio Visual Creator
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF]">
                        Platform Marketing Pro
                    </span>
                </div>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Hasilkan materi visual promosi resmi COOCA, pilih 460+ referensi gambar dari Pinterest, Civitai, atau CopasPrompt, dan jadwalkan postingan ke media sosial.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Status Kunci Engine AI Platform -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] text-xs font-semibold {{ $isEngineReady ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20' }}">
                    <span class="w-2 h-2 rounded-full {{ $isEngineReady ? 'bg-[#34C759] animate-pulse' : 'bg-[#FF9500]' }}"></span>
                    <span>{{ $isEngineReady ? 'AI Engine Siap' : 'Mode Simulasi' }}</span>
                </div>

                <!-- Shortcut ke Social Media Center -->
                <a href="{{ route('admin.social-media.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white/90 border border-black/5 dark:border-white/10 transition-all">
                    <i data-lucide="share-2" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Social Media Center</span>
                </a>
            </div>
        </div>

        <!-- Tab Navigasi Submodul AI Studio Admin -->
        <div class="flex items-center gap-1.5 border-t border-black/5 dark:border-white/10 pt-4 overflow-x-auto no-scrollbar">
            <a href="{{ route('admin.ai-studio.visual.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-bold transition-all bg-[#007AFF] text-white shadow-xs">
                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                <span>Studio Visual &amp; Medsos</span>
            </a>
            <a href="{{ route('admin.ai-studio.sources.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                <span>Sumber Inspirasi &amp; Tren ({{ $totalInspirationsCount ?? 463 }} Foto)</span>
            </a>
            <a href="{{ route('admin.ai-studio.pricing.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                <span>Model Registry &amp; Tarif</span>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. BENTO METRICS GRID                      -->
    <!-- ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Metric 1: Total Aset Visual Admin -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Hasil Render Admin</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_assets'] }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">materi tersimpan</span>
            </div>
        </div>

        <!-- Metric 2: Katalog Inspirasi Siap Pakai -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Katalog Gambar Referensi</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9500]/20 dark:text-[#FF9F0A] flex items-center justify-center">
                    <i data-lucide="compass" class="w-3.5 h-3.5 fill-[#FF9500]/20"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-[#FF9500] dark:text-[#FF9F0A]">{{ $totalInspirationsCount ?? 463 }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">desain siap pakai</span>
            </div>
        </div>

        <!-- Metric 3: Rasio Feed 1:1 -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Feed Square (1:1)</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] dark:bg-[#34C759]/20 dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="square" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_1_1'] }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">konten feed</span>
            </div>
        </div>

        <!-- Metric 4: Rasio Story/Reels 9:16 -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Story &amp; Reels (9:16)</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#AF52DE]/10 text-[#AF52DE] dark:bg-[#AF52DE]/20 dark:text-[#BF5AF2] flex items-center justify-center">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_9_16'] }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">konten vertikal</span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. MAIN WORKSPACE (2 BENTO COLUMNS)        -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ========================================== -->
        <!-- KOLOM KIRI: GENERATOR CONTROLS (5 COLS)   -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 space-y-5">
            <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">

                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="wand-2" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>1. Konfigurasi Generator Visual</span>
                    </h2>
                    <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">Multi-Konten</span>
                </div>

                <!-- Bagian A: Pilihan Kategori / Jenis Konten -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-black dark:text-white flex items-center justify-between">
                        <span>Jenis Konten Platform</span>
                        <span class="text-[10px] text-black/40 dark:text-white/40">8 Tipe Komunikasi</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                        @foreach ($contentTypes as $key => $type)
                            <button type="button"
                                @click="contentType = '{{ $key }}'"
                                :class="contentType === '{{ $key }}' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 text-black/70 dark:text-white/70 bg-black/[0.02] dark:bg-white/[0.02]'"
                                class="p-2 rounded-[10px] border text-left transition-all flex flex-col justify-between h-[58px]">
                                <span class="text-[11px] font-bold truncate">{{ $type['label'] ?? $type['name'] ?? '' }}</span>
                                <span class="text-[9px] opacity-60 truncate">{{ $type['desc'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Bagian B: Preset Kampanye Cepat (Chips) -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-black dark:text-white">
                        Preset Kampanye Siap Pakai
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($campaignPresets as $preset)
                            <button type="button"
                                @click="applyPreset(@js($preset))"
                                :class="selectedPresetId === '{{ $preset['id'] }}' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/80 hover:bg-black/[0.08]'"
                                class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                <span>{{ $preset['title'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- Bagian C: Pemilih Gambar Referensi Visual (VISUAL PICKER)  -->
                <!-- ========================================================= -->
                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                            <i data-lucide="image-plus" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Gambar Referensi Gaya Visual</span>
                        </label>
                        <template x-if="hasReference">
                            <button type="button" @click="clearReference()"
                                class="text-[11px] font-semibold text-red-500 hover:text-red-600 flex items-center gap-1">
                                <i data-lucide="x" class="w-3 h-3"></i>
                                <span>Hapus Referensi</span>
                            </button>
                        </template>
                    </div>

                    <!-- Pilihan Sumber Referensi (Tab: Dari Inspirasi Lokal vs Upload Manual) -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-black/[0.04] dark:bg-white/[0.05] rounded-[10px]">
                        <button type="button" @click="refMode = 'catalog'"
                            :class="refMode === 'catalog' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-1.5 px-2 rounded-[8px] text-xs transition-all flex items-center justify-center gap-1.5">
                            <i data-lucide="compass" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Katalog COOCA ({{ $totalInspirationsCount ?? 463 }}+)</span>
                        </button>
                        <button type="button" @click="refMode = 'upload'"
                            :class="refMode === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-1.5 px-2 rounded-[8px] text-xs transition-all flex items-center justify-center gap-1.5">
                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>Unggah Foto Manual</span>
                        </button>
                    </div>

                    <!-- Panel Mode A: Pilih dari Katalog Inspirasi -->
                    <div x-show="refMode === 'catalog'" class="space-y-2.5">

                        <!-- KONDISI 1: Belum Ada Referensi Dipilih -> Tampilkan Quick Pick Thumbnails Langsung! -->
                        <template x-if="!selectedInspiration">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                                    <span class="font-semibold text-black dark:text-white">Pilihan Cepat Gambar Referensi:</span>
                                    <span>Klik untuk pasang</span>
                                </div>

                                <!-- Strip Thumbnail Gambar Referensi Populer (Langsung Terlihat di Layar!) -->
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach ($quickReferences as $qRef)
                                        <div @click="selectInspirationFromModal(@js($qRef))"
                                            title="{{ $qRef['title'] }} ({{ $qRef['source_name'] }})"
                                            class="group relative rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 hover:border-[#007AFF] aspect-square cursor-pointer transition-all bg-black/[0.04] shadow-2xs">
                                            <img src="{{ $qRef['display_image_url'] }}" alt="{{ $qRef['title'] }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                            <span class="absolute top-1 left-1 px-1 py-0.2 rounded text-[8px] font-bold uppercase bg-black/70 text-white backdrop-blur-xs">
                                                {{ $qRef['source_code'] }}
                                            </span>
                                            <!-- Hover Overlay -->
                                            <div class="absolute inset-0 bg-black/65 opacity-0 group-hover:opacity-100 transition-opacity p-1 flex flex-col justify-end text-white text-[9px] font-bold">
                                                <span class="truncate">{{ $qRef['title'] }}</span>
                                                <span class="text-[#0A84FF]">Pilih &rarr;</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Tombol Navigasi ke Galeri Lengkap di Bawah -->
                                <button type="button" @click="scrollOrOpenGallery()"
                                    class="w-full py-2 px-3 rounded-[10px] border border-dashed border-[#007AFF]/40 hover:border-[#007AFF] bg-[#007AFF]/5 hover:bg-[#007AFF]/10 text-[#007AFF] text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                                    <span>Lihat &amp; Cari di 460+ Koleksi Gambar Lengkap &darr;</span>
                                </button>
                            </div>
                        </template>

                        <!-- KONDISI 2: Referensi Sudah Dipilih -> Tampilkan Kartu Referensi Aktif -->
                        <template x-if="selectedInspiration">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-[#007AFF]/30 shadow-xs space-y-2">
                                <div class="flex items-center gap-3">
                                    <img :src="selectedInspiration.display_image_url || selectedInspiration.preview_url || selectedInspiration.image_url" :alt="selectedInspiration.title"
                                        class="w-16 h-16 rounded-[10px] object-cover border border-black/10 shrink-0 shadow-sm">
                                    <div class="flex-1 min-w-0 space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-[#007AFF]/10 text-[#007AFF]" x-text="selectedInspiration.source_code || selectedInspiration.source_name || 'COOCA'"></span>
                                            <span class="text-[10px] text-[#34C759] font-bold flex items-center gap-0.5">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                <span>Referensi Aktif</span>
                                            </span>
                                        </div>
                                        <h5 class="text-xs font-bold text-black dark:text-white truncate" x-text="selectedInspiration.title"></h5>
                                        <p class="text-[10px] text-black/50 dark:text-white/50 truncate" x-text="selectedInspiration.category_name || 'Katalog Visual'"></p>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <button type="button" @click="scrollOrOpenGallery()"
                                            class="px-2.5 py-1 rounded-[7px] text-[10px] font-bold bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] text-black/80 dark:text-white/80 transition-all">
                                            Ganti
                                        </button>
                                        <button type="button" @click="clearReference()"
                                            class="px-2.5 py-1 rounded-[7px] text-[10px] font-bold bg-red-500/10 text-red-500 hover:bg-red-500/20 transition-all">
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <!-- Tombol Salin Prompt Inspirasi jika ada -->
                                <template x-if="selectedInspiration.prompt_hint">
                                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                        <span class="text-[10px] text-black/50 dark:text-white/50">Tersedia prompt gaya visual</span>
                                        <button type="button" @click="applyInspirationPrompt()"
                                            class="text-[10px] font-bold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                                            <span>Salin ke Prompt Generator</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                    </div>

                    <!-- Panel Mode B: Unggah Foto Manual -->
                    <div x-show="refMode === 'upload'" class="space-y-2">
                        <template x-if="!uploadedReferenceUrl">
                            <label class="w-full py-4 px-3 rounded-[10px] border border-dashed border-black/20 dark:border-white/20 hover:border-[#34C759] bg-black/[0.01] dark:bg-white/[0.02] hover:bg-[#34C759]/5 text-center flex flex-col items-center justify-center gap-1.5 cursor-pointer transition-all">
                                <i data-lucide="upload" class="w-5 h-5 text-black/40 dark:text-white/40"></i>
                                <span class="text-xs font-semibold text-black dark:text-white">Klik atau Tarik File Foto ke Sini</span>
                                <span class="text-[10px] text-black/45 dark:text-white/45">Mendukung format JPG, PNG, WEBP (Maks 10MB)</span>
                                <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                            </label>
                        </template>

                        <template x-if="uploadedReferenceUrl">
                            <div class="flex items-center gap-3 p-2 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 shadow-xs">
                                <img :src="uploadedReferenceUrl" alt="Uploaded Reference"
                                    class="w-14 h-14 rounded-[8px] object-cover border border-black/5">
                                <div class="flex-1 min-w-0 space-y-0.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-[#34C759]/10 text-[#34C759]">Berkas Manual</span>
                                    <h5 class="text-xs font-bold text-black dark:text-white truncate" x-text="uploadedFileName || 'Gambar Manual'"></h5>
                                    <p class="text-[10px] text-black/50 dark:text-white/50">Gambar akan dijadikan acuan gaya &amp; layout oleh engine AI</p>
                                </div>
                                <label class="px-2.5 py-1.5 rounded-[8px] text-[10px] font-bold bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] text-black/80 dark:text-white/80 cursor-pointer transition-all">
                                    <span>Ganti File</span>
                                    <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                                </label>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- Bagian D: Directives & Modifiers (Ketentuan Khusus) -->
                <!-- ========================================== -->
                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Arahan Gaya &amp; Ketentuan Khusus</span>
                        </label>
                        <button type="button" @click="autoRecompilePrompt()"
                            class="text-[10px] font-bold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                            <span>Susun Ulang Prompt Otomatis</span>
                        </button>
                    </div>

                    <!-- Dropdown Gaya Visual, Lighting, dan Palet -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- Gaya Visual -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">
                                Gaya Visual
                            </label>
                            <select x-model="visualStyle"
                                class="w-full h-8 px-2 rounded-[8px] text-[11px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                @foreach ($visualStyles as $key => $vStyle)
                                    <option value="{{ $key }}">{{ $vStyle['label'] ?? $vStyle['name'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pencahayaan (Lighting) -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">
                                Pencahayaan
                            </label>
                            <select x-model="lightingStyle"
                                class="w-full h-8 px-2 rounded-[8px] text-[11px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                @foreach ($lightingStyles as $key => $lStyle)
                                    <option value="{{ $key }}">{{ $lStyle['label'] ?? $lStyle['name'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Palet Warna -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">
                                Palet Warna
                            </label>
                            <select x-model="colorPalette"
                                class="w-full h-8 px-2 rounded-[8px] text-[11px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                @foreach ($colorPalettes as $key => $palette)
                                    <option value="{{ $key }}">{{ $palette['label'] ?? $palette['name'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Checklist Ketentuan Khusus / Directives -->
                    <div class="space-y-1.5 pt-1">
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">
                            Ketentuan Komposisi Khusus (Opsional)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2 rounded-[8px] border border-black/10 dark:border-white/10 cursor-pointer bg-white dark:bg-[#2C2C2E]"
                                :class="customDirectives.leave_space_for_text ? 'border-[#007AFF] bg-[#007AFF]/5' : ''">
                                <input type="checkbox" x-model="customDirectives.leave_space_for_text" class="rounded text-[#007AFF]">
                                <span class="text-[11px] font-medium text-black dark:text-white">Ruang Kosong untuk Teks</span>
                            </label>

                            <label class="flex items-center gap-2 p-2 rounded-[8px] border border-black/10 dark:border-white/10 cursor-pointer bg-white dark:bg-[#2C2C2E]"
                                :class="customDirectives.include_ui_mockup ? 'border-[#007AFF] bg-[#007AFF]/5' : ''">
                                <input type="checkbox" x-model="customDirectives.include_ui_mockup" class="rounded text-[#007AFF]">
                                <span class="text-[11px] font-medium text-black dark:text-white">Mockup UI Aplikasi COOCA</span>
                            </label>

                            <label class="flex items-center gap-2 p-2 rounded-[8px] border border-black/10 dark:border-white/10 cursor-pointer bg-white dark:bg-[#2C2C2E]"
                                :class="customDirectives.floating_3d_badge ? 'border-[#007AFF] bg-[#007AFF]/5' : ''">
                                <input type="checkbox" x-model="customDirectives.floating_3d_badge" class="rounded text-[#007AFF]">
                                <span class="text-[11px] font-medium text-black dark:text-white">Badge 3D Penawaran Diskon</span>
                            </label>

                            <label class="flex items-center gap-2 p-2 rounded-[8px] border border-black/10 dark:border-white/10 cursor-pointer bg-white dark:bg-[#2C2C2E]"
                                :class="customDirectives.indonesian_msme_context ? 'border-[#007AFF] bg-[#007AFF]/5' : ''">
                                <input type="checkbox" x-model="customDirectives.indonesian_msme_context" class="rounded text-[#007AFF]">
                                <span class="text-[11px] font-medium text-black dark:text-white">Nuansa Lokal UMKM Indonesia</span>
                            </label>
                        </div>
                    </div>

                    <!-- Teks Headline Banner (Opsional) -->
                    <div class="space-y-1 pt-1">
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">
                            Teks Utama / Headline Banner (Opsional)
                        </label>
                        <input type="text" x-model="bannerHeadline" placeholder="Contoh: Otomasi Bisnis Ritel &amp; F&amp;B Tanpa Ribet"
                            class="w-full h-8 px-3 rounded-[8px] text-xs bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </div>

                <!-- Bagian E: Pilihan Rasio Media Sosial -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-black dark:text-white">
                        Rasio Media Sosial
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" @click="aspectRatio = '1:1'"
                            :class="aspectRatio === '1:1' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 text-black/70 dark:text-white/70'"
                            class="p-2 rounded-[12px] border text-center transition-all flex flex-col items-center gap-1">
                            <span class="w-5 h-5 rounded-[4px] border-2 border-current flex items-center justify-center text-[9px] font-bold">1:1</span>
                            <span class="text-[10px] font-bold">Square</span>
                            <span class="text-[9px] opacity-60">Feed IG/FB</span>
                        </button>
                        <button type="button" @click="aspectRatio = '9:16'"
                            :class="aspectRatio === '9:16' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 text-black/70 dark:text-white/70'"
                            class="p-2 rounded-[12px] border text-center transition-all flex flex-col items-center gap-1">
                            <span class="w-3.5 h-6 rounded-[4px] border-2 border-current flex items-center justify-center text-[8px] font-bold">9:16</span>
                            <span class="text-[10px] font-bold">Reels</span>
                            <span class="text-[9px] opacity-60">Story/TikTok</span>
                        </button>
                        <button type="button" @click="aspectRatio = '16:9'"
                            :class="aspectRatio === '16:9' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 text-black/70 dark:text-white/70'"
                            class="p-2 rounded-[12px] border text-center transition-all flex flex-col items-center gap-1">
                            <span class="w-6 h-3.5 rounded-[4px] border-2 border-current flex items-center justify-center text-[8px] font-bold">16:9</span>
                            <span class="text-[10px] font-bold">Banner</span>
                            <span class="text-[9px] opacity-60">Web/LinkedIn</span>
                        </button>
                        <button type="button" @click="aspectRatio = '4:5'"
                            :class="aspectRatio === '4:5' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 text-black/70 dark:text-white/70'"
                            class="p-2 rounded-[12px] border text-center transition-all flex flex-col items-center gap-1">
                            <span class="w-4 h-5 rounded-[4px] border-2 border-current flex items-center justify-center text-[8px] font-bold">4:5</span>
                            <span class="text-[10px] font-bold">Portrait</span>
                            <span class="text-[9px] opacity-60">Feed Vertikal</span>
                        </button>
                    </div>
                </div>

                <!-- Bagian F: Pilihan Model & Kualitas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-black dark:text-white flex items-center justify-between">
                            <span>Model AI</span>
                            <template x-if="hasReference && modelName === 'gpt-image-2.5-flare'">
                                <span class="text-[10px] text-[#007AFF] font-bold">💡 Sunburst disarankan untuk referensi</span>
                            </template>
                        </label>
                        <select x-model="modelName"
                            class="w-full h-10 px-3 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="gpt-image-2.5-flare">GPT Image 2.5 Flare (Cepat &amp; Harian)</option>
                            <option value="gpt-image-2.5-sunburst">GPT Image 2.5 Sunburst (Ultra Detail + Edit Referensi)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-black dark:text-white">
                            Kualitas Render
                        </label>
                        <select x-model="quality"
                            class="w-full h-10 px-3 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="standard">Standard Quality</option>
                            <option value="high">High Definition</option>
                            <option value="xhigh">X-High Commercial</option>
                        </select>
                    </div>
                </div>

                <!-- Bagian G: Judul Materi Promosi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-black dark:text-white">
                        Judul Materi Visual (Label Arsip)
                    </label>
                    <input type="text" x-model="title" placeholder="Contoh: Banner Promo Langganan Tahunan Q4"
                        class="w-full h-10 px-3.5 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <!-- Bagian H: Input Prompt Positif -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-black dark:text-white">
                            Prompt Generasi Visual <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-black/40 dark:text-white/40" x-text="prompt.length + '/4000'"></span>
                    </div>
                    <textarea x-model="prompt" rows="5" maxlength="4000"
                        placeholder="Deskripsikan konsep visual promosi secara spesifik (objek, komposisi, lighting, palet warna, suasana)..."
                        class="w-full p-3.5 rounded-[14px] text-xs leading-relaxed bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-y"></textarea>
                </div>

                <!-- Bagian I: Negative Prompt (Accordion) -->
                <div x-data="{ open: false }" class="border border-black/5 dark:border-white/10 rounded-[12px] p-3 bg-black/[0.01] dark:bg-white/[0.02]">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between text-xs font-semibold text-black/70 dark:text-white/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                            <span>Negative Prompt (Elemen yang Dihindari)</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-cloak class="mt-2.5 pt-2 border-t border-black/5 dark:border-white/10">
                        <textarea x-model="negativePrompt" rows="2" maxlength="1000"
                            placeholder="blur, text watermark, ugly hands, low resolution, bad lighting, distorted faces..."
                            class="w-full p-2.5 rounded-[10px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>
                </div>

                <!-- Bagian J: Tombol Action Render Utama -->
                <div>
                    <button type="button" @click="dispatchGenerate()"
                        :disabled="isGenerating || !prompt.trim()"
                        class="w-full h-11 rounded-[12px] bg-gradient-to-r from-[#007AFF] to-[#5856D6] hover:from-[#0062cc] hover:to-[#4a47b8] text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99]">
                        <template x-if="isGenerating">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span>Sedang Menghasilkan Visual AI... (<span x-text="generationTimer"></span>s)</span>
                            </div>
                        </template>
                        <template x-if="!isGenerating">
                            <div class="flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Generate Visual AI Sekarang</span>
                            </div>
                        </template>
                    </button>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- KOLOM KANAN: CANVAS & ASSETS GALLERY (7 COLS)-->
        <!-- ========================================== -->
        <div class="lg:col-span-7 space-y-5">

            <!-- Bento Card 2: Interactive Active Canvas -->
            <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="monitor" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>2. Canvas Pratinjau Visual</span>
                    </h2>

                    <!-- Keterangan Status Canvas -->
                    <div>
                        <template x-if="activeAsset">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]" x-text="activeAsset.aspect_ratio"></span>
                                <span class="text-[11px] text-black/40 dark:text-white/40" x-text="activeAsset.width + ' × ' + activeAsset.height + ' px'"></span>
                            </div>
                        </template>
                        <template x-if="!activeAsset && selectedInspiration">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                Pratinjau Referensi Terpasang
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Frame Gambar Hasil Render / Referensi Terpasang -->
                <div class="relative w-full rounded-[16px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-center min-h-[380px] max-h-[520px]">

                    <!-- Loading State saat Render -->
                    <template x-if="isGenerating">
                        <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
                            <div class="w-14 h-14 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center animate-pulse">
                                <i data-lucide="sparkles" class="w-7 h-7"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-black dark:text-white">Merender Visual Promosi Platform</h4>
                                <p class="text-xs text-black/50 dark:text-white/50 max-w-xs">
                                    Model OpenAI sedang menyusun komposisi pencahayaan studio, gaya visual, dan referensi terpilih...
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Active Image Display (Aset Hasil Render) -->
                    <template x-if="!isGenerating && activeAsset">
                        <div class="relative w-full h-full flex items-center justify-center p-2 group">
                            <img :src="activeAsset.image_url" :alt="activeAsset.title"
                                class="max-h-[500px] w-auto max-w-full rounded-[12px] object-contain shadow-lg">

                            <!-- Hover Overlay with Title -->
                            <div class="absolute bottom-4 left-4 right-4 p-3 rounded-[12px] backdrop-blur-md bg-black/60 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-between">
                                <span class="font-semibold truncate mr-2" x-text="activeAsset.title"></span>
                                <span class="text-[10px] text-white/70" x-text="activeAsset.model_name"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Tampilan Pratinjau Gambar Referensi Aktif (Jika belum ada hasil render) -->
                    <template x-if="!isGenerating && !activeAsset && selectedInspiration">
                        <div class="relative w-full h-full flex flex-col items-center justify-center p-4 group">
                            <img :src="selectedInspiration.display_image_url || selectedInspiration.preview_url || selectedInspiration.image_url" :alt="selectedInspiration.title"
                                class="max-h-[400px] w-auto max-w-full rounded-[14px] object-contain shadow-lg border border-black/10">

                            <!-- Overlay Keterangan Referensi -->
                            <div class="mt-3 p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-sm flex items-center justify-between w-full max-w-md">
                                <div class="min-w-0 pr-2">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-[#007AFF]" x-text="'Referensi: ' + (selectedInspiration.source_name || selectedInspiration.source_code)"></span>
                                    <h4 class="text-xs font-bold text-black dark:text-white truncate" x-text="selectedInspiration.title"></h4>
                                </div>
                                <button type="button" @click="dispatchGenerate()"
                                    class="px-3 py-1.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-[11px] font-bold shadow-xs transition-all shrink-0">
                                    Render Sekarang &rarr;
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Canvas Placeholder -->
                    <template x-if="!isGenerating && !activeAsset && !selectedInspiration">
                        <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
                            <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="image" class="w-6 h-6"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-black dark:text-white">Pilih Gambar Referensi di Bawah untuk Memulai</h4>
                                <p class="text-[11px] text-black/50 dark:text-white/50 max-w-xs">
                                    Klik salah satu dari 460+ inspirasi di galeri bawah atau pilih preset kampanye untuk merancang visual promosi.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Integrated Action Toolbar (1-Click Social Media Post) -->
                <template x-if="activeAsset">
                    <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <!-- 🚀 Primary CTA: 1-Click Kirim ke Social Media -->
                            <button type="button" @click="openSocialModal()"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98] cursor-pointer">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                                <span>Kirim ke Social Media</span>
                            </button>

                            <!-- Unduh HD -->
                            <a :href="activeAsset.image_url" download="cooca-ai-visual.png" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/90 transition-all">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">Unduh HD</span>
                            </a>

                            <!-- Salin URL -->
                            <button type="button" @click="copyImageUrl(activeAsset.image_url)"
                                class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/90 transition-all">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">Salin URL</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <!-- Toggle Favorite -->
                            <button type="button" @click="toggleFavorite(activeAsset.id)"
                                :class="activeAsset.is_favorite ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50'"
                                class="p-2.5 rounded-[12px] hover:scale-105 transition-all cursor-pointer">
                                <i data-lucide="star" class="w-4 h-4" :class="activeAsset.is_favorite ? 'fill-[#FF9500]' : ''"></i>
                            </button>

                            <!-- Hapus Aset -->
                            <button type="button" @click="deleteAsset(activeAsset.id)"
                                class="p-2.5 rounded-[12px] bg-red-500/10 text-red-600 hover:bg-red-500/20 transition-all cursor-pointer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ======================================================== -->
            <!-- Bento Card 3: DUAL-TAB BENTO GALLERY (INSPIRASI vs HASIL) -->
            <!-- ======================================================== -->
            <div id="bentoGallerySection" class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                
                <!-- Tab Switcher: Galeri Inspirasi (Default) vs Aset Admin -->
                <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-1.5 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <button type="button" @click="activeBottomTab = 'inspirations'"
                            :class="activeBottomTab === 'inspirations' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04]'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[9px] text-xs font-bold transition-all cursor-pointer">
                            <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                            <span>Koleksi 460+ Gambar Inspirasi</span>
                        </button>
                        <button type="button" @click="activeBottomTab = 'my_assets'"
                            :class="activeBottomTab === 'my_assets' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04]'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[9px] text-xs font-bold transition-all cursor-pointer">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            <span>Materi Visual Tersimpan ({{ $stats['total_assets'] }})</span>
                        </button>
                    </div>

                    <span class="text-[11px] text-black/50 dark:text-white/50">
                        <span x-show="activeBottomTab === 'inspirations'">Pinterest, Civitai, CopasPrompt</span>
                        <span x-show="activeBottomTab === 'my_assets'">Hasil Generate Platform</span>
                    </span>
                </div>

                <!-- ========================================================= -->
                <!-- TAB 1: KOLEKSI GAMBAR INSPIRASI REFERENSI LOKAL (DEFAULT!) -->
                <!-- ========================================================= -->
                <div x-show="activeBottomTab === 'inspirations'" class="space-y-4">

                    <!-- Filter Sumber Feed & Pencarian -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <!-- Source Filter Chips -->
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <a href="{{ route('admin.ai-studio.visual.index', ['insp_source' => 'all', 'insp_category' => $inspCategory]) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold shrink-0 {{ $inspSource === 'all' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70' }}">
                                Semua ({{ $totalInspirationsCount ?? 463 }})
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['insp_source' => 'pinterest', 'insp_category' => $inspCategory]) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold shrink-0 {{ $inspSource === 'pinterest' ? 'bg-[#E60023] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70' }}">
                                Pinterest (34)
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['insp_source' => 'civitai', 'insp_category' => $inspCategory]) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold shrink-0 {{ $inspSource === 'civitai' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70' }}">
                                Civitai (262)
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['insp_source' => 'copasprompt', 'insp_category' => $inspCategory]) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold shrink-0 {{ $inspSource === 'copasprompt' ? 'bg-[#34C759] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70' }}">
                                CopasPrompt (133)
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['insp_source' => 'cooca_curated', 'insp_category' => $inspCategory]) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold shrink-0 {{ $inspSource === 'cooca_curated' ? 'bg-[#AF52DE] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70' }}">
                                Cooca Curated (26)
                            </a>
                        </div>

                        <!-- Tombol Buka Modal Pencarian Lengkap -->
                        <button type="button" @click="openInspirationPickerModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-bold text-black/80 dark:text-white/80 shrink-0 cursor-pointer">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span>Pencarian Canggih</span>
                        </button>
                    </div>

                    <!-- GRID UTAMA GAMBAR INSPIRASI (GAMBAR ASLI DITAMPILKAN DI SINI!) -->
                    @if ($inspirationReferences->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach ($inspirationReferences as $item)
                                @php
                                    $itemData = [
                                        'id' => $item->id,
                                        'title' => $item->title,
                                        'caption' => $item->caption,
                                        'display_image_url' => $item->display_image_url,
                                        'thumbnail_url' => $item->display_image_url,
                                        'source_name' => $item->source?->name ?? 'Katalog Visual',
                                        'source_code' => $item->source?->code ?? 'cooca',
                                        'category_name' => $item->category?->name ?? 'Umum',
                                        'prompt_hint' => $item->visualAnalysis?->extracted_style_prompt ?? $item->caption,
                                    ];
                                @endphp
                                <div class="group relative rounded-[14px] overflow-hidden bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-all aspect-square flex flex-col shadow-2xs">
                                    <img src="{{ $item->display_image_url }}" alt="{{ $item->title }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                                    <!-- Source Badge -->
                                    <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-black/70 text-white backdrop-blur-xs">
                                        {{ $item->source?->code ?? 'cooca' }}
                                    </span>

                                    <!-- Hover Action Overlay -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-2.5 flex flex-col justify-end text-white opacity-0 group-hover:opacity-100 transition-opacity space-y-1.5">
                                        <p class="text-[11px] font-bold truncate">{{ $item->title }}</p>
                                        <p class="text-[9px] text-white/70 truncate">{{ $item->category?->name ?? 'Katalog Visual' }}</p>

                                        <div class="grid grid-cols-2 gap-1 pt-1">
                                            <button type="button" @click="selectInspirationFromModal(@js($itemData))"
                                                class="py-1 px-1.5 rounded-[6px] text-[10px] font-bold bg-[#007AFF] hover:bg-[#0062cc] text-white text-center cursor-pointer shadow-xs">
                                                🎯 Referensi
                                            </button>
                                            <button type="button" @click="selectInspirationFromModal(@js($itemData)); applyInspirationPrompt();"
                                                class="py-1 px-1.5 rounded-[6px] text-[10px] font-bold bg-white/20 hover:bg-white/30 text-white text-center cursor-pointer backdrop-blur-xs">
                                                ✨ + Prompt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination Gambar Inspirasi -->
                        <div class="pt-3 border-t border-black/5 dark:border-white/10">
                            {{ $inspirationReferences->links() }}
                        </div>
                    @else
                        <div class="p-8 text-center space-y-2">
                            <i data-lucide="image-off" class="w-8 h-8 mx-auto text-black/30 dark:text-white/30"></i>
                            <h4 class="text-xs font-bold text-black dark:text-white">Tidak Ada Gambar Inspirasi Ditemukan</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Coba pilih sumber feed lain atau reset filter pencarian.</p>
                        </div>
                    @endif
                </div>

                <!-- ========================================================= -->
                <!-- TAB 2: MATERI VISUAL HASIL RENDER ADMIN                   -->
                <!-- ========================================================= -->
                <div x-show="activeBottomTab === 'my_assets'" class="space-y-4" style="display: none;">

                    <!-- Filter Chips Rasio -->
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.ai-studio.visual.index', ['ratio' => 'all']) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[10px] font-bold {{ $aspectRatioFilter === 'all' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                                Semua
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['ratio' => '1:1']) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[10px] font-bold {{ $aspectRatioFilter === '1:1' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                                1:1
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['ratio' => '9:16']) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[10px] font-bold {{ $aspectRatioFilter === '9:16' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                                9:16
                            </a>
                            <a href="{{ route('admin.ai-studio.visual.index', ['ratio' => '16:9']) }}"
                                class="px-2.5 py-1 rounded-[8px] text-[10px] font-bold {{ $aspectRatioFilter === '16:9' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                                16:9
                            </a>
                        </div>

                        <span class="text-[11px] text-black/50 dark:text-white/50">
                            {{ $assets->total() }} berkas tersimpan
                        </span>
                    </div>

                    <!-- Asset Grid -->
                    @if ($assets->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach ($assets as $assetItem)
                                <div @click="selectAsset(@js($assetItem))"
                                    :class="activeAsset && activeAsset.id === '{{ $assetItem->id }}' ? 'ring-2 ring-[#007AFF] shadow-md' : 'hover:border-black/20 dark:hover:border-white/20'"
                                    class="group relative rounded-[14px] overflow-hidden bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 cursor-pointer transition-all aspect-square">
                                    <img src="{{ $assetItem->image_url }}" alt="{{ $assetItem->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">

                                    <!-- Badge Aspek Rasio -->
                                    <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded-[6px] text-[9px] font-bold bg-black/60 text-white backdrop-blur-xs">
                                        {{ $assetItem->aspect_ratio }}
                                    </span>

                                    @if ($assetItem->is_favorite)
                                        <span class="absolute top-2 right-2 text-[#FF9500]">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-[#FF9500]"></i>
                                        </span>
                                    @endif

                                    <!-- Hover Quick Details -->
                                    <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/80 via-black/40 to-transparent text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-[10px] font-bold truncate">{{ $assetItem->title }}</p>
                                        <p class="text-[9px] text-white/70">{{ $assetItem->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="pt-3 border-t border-black/5 dark:border-white/10">
                            {{ $assets->links() }}
                        </div>
                    @else
                        <div class="p-8 text-center space-y-2">
                            <div class="w-10 h-10 mx-auto rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center">
                                <i data-lucide="image" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-black dark:text-white">Belum Ada Materi Visual Buatan Admin</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Gunakan generator di atas untuk menciptakan aset promosi resmi platform.</p>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: PEMILIH INSPIRASI DARI KATALOG COOCA (460+ FOTO)-->
    <!-- ======================================================== -->
    <template x-if="inspirationPickerOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="inspirationPickerOpen = false">
            <div class="w-full max-w-4xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">

                <!-- Header Modal -->
                <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="compass" class="w-4 h-4"></i>
                            </span>
                            <h3 class="text-sm font-bold text-black dark:text-white">Pilih Gambar Referensi Gaya Visual COOCA</h3>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60">
                            Pilih gambar sebagai referensi gaya, layout, atau pencahayaan untuk generator AI Anda.
                        </p>
                    </div>

                    <button type="button" @click="inspirationPickerOpen = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.05] hover:bg-black/[0.1] text-black/70 dark:text-white/70 flex items-center justify-center transition-all cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Bar Filter & Pencarian -->
                <div class="p-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" x-model="modalSearch" @input.debounce.300ms="fetchModalInspirations()"
                            placeholder="Cari kata kunci: saas, glass, minimal, dashboard, coffee, retail..."
                            class="w-full h-9 pl-9 pr-3 rounded-[10px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select x-model="modalCategory" @change="fetchModalInspirations()"
                            class="h-9 px-3 rounded-[10px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            <option value="all">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Grid Gambar Inspirasi Modal -->
                <div class="p-5 flex-1 overflow-y-auto min-h-[300px]">
                    <template x-if="isLoadingInspirations">
                        <div class="h-64 flex flex-col items-center justify-center space-y-2">
                            <div class="w-8 h-8 rounded-full border-2 border-[#007AFF] border-t-transparent animate-spin"></div>
                            <span class="text-xs text-black/50 dark:text-white/50">Memuat koleksi gambar inspirasi...</span>
                        </div>
                    </template>

                    <template x-if="!isLoadingInspirations && modalInspirations.length > 0">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            <template x-for="item in modalInspirations" :key="item.id">
                                <div @click="selectInspirationFromModal(item)"
                                    class="group relative rounded-[12px] overflow-hidden bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 hover:border-[#007AFF] cursor-pointer transition-all aspect-square flex flex-col">
                                    <img :src="item.display_image_url || item.preview_url || item.image_url" :alt="item.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-black/70 text-white backdrop-blur-xs" x-text="item.source_code || item.source_name"></span>

                                    <!-- Hover Select Overlay -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent p-2.5 flex flex-col justify-end text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-[11px] font-bold truncate" x-text="item.title"></p>
                                        <p class="text-[9px] text-white/70 truncate" x-text="item.category_name"></p>
                                        <span class="mt-1.5 py-1 px-2 rounded-[6px] text-[10px] font-bold bg-[#007AFF] text-white text-center">
                                            Pilih Sebagai Referensi
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!isLoadingInspirations && modalInspirations.length === 0">
                        <div class="h-64 flex flex-col items-center justify-center text-center space-y-2">
                            <i data-lucide="image-off" class="w-8 h-8 text-black/30 dark:text-white/30"></i>
                            <h4 class="text-xs font-bold text-black dark:text-white">Tidak Ditemukan Gambar Inspirasi</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
                        </div>
                    </template>
                </div>

                <!-- Footer Modal -->
                <div class="p-4 border-t border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] flex items-center justify-between">
                    <span class="text-xs text-black/50 dark:text-white/50">
                        Total <span x-text="modalInspirations.length"></span> inspirasi ditemukan
                    </span>
                    <button type="button" @click="inspirationPickerOpen = false"
                        class="px-4 py-2 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/70 dark:text-white/70 cursor-pointer">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 2: 1-CLICK SOCIAL MEDIA POST                       -->
    <!-- ======================================================== -->
    <template x-if="socialModalOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="socialModalOpen = false">
            <div class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">

                <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </span>
                            <h3 class="text-sm font-bold text-black dark:text-white">Publikasikan ke Social Media</h3>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60">
                            Kirim materi visual yang sudah dirender langsung ke saluran resmi COOCA.
                        </p>
                    </div>

                    <button type="button" @click="socialModalOpen = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.05] hover:bg-black/[0.1] text-black/70 dark:text-white/70 flex items-center justify-center transition-all cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">

                    <!-- Pratinjau Mini Gambar -->
                    <template x-if="activeAsset">
                        <div class="flex items-center gap-3 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10">
                            <img :src="activeAsset.image_url" :alt="activeAsset.title"
                                class="w-16 h-16 rounded-[8px] object-cover">
                            <div class="space-y-0.5 min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-black dark:text-white truncate" x-text="activeAsset.title"></h4>
                                <p class="text-[11px] text-black/50 dark:text-white/50" x-text="activeAsset.aspect_ratio + ' • ' + activeAsset.model_name"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Pilihan Platform -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-black dark:text-white">
                            Pilih Saluran Media Sosial <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <!-- Instagram -->
                            <label class="flex items-center gap-2 p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                                :class="selectedPlatforms.includes('instagram') ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                                <input type="checkbox" value="instagram" x-model="selectedPlatforms" class="rounded text-[#007AFF]">
                                <span class="text-xs font-semibold text-black dark:text-white">Instagram</span>
                            </label>

                            <!-- Facebook -->
                            <label class="flex items-center gap-2 p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                                :class="selectedPlatforms.includes('facebook') ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                                <input type="checkbox" value="facebook" x-model="selectedPlatforms" class="rounded text-[#007AFF]">
                                <span class="text-xs font-semibold text-black dark:text-white">Facebook</span>
                            </label>

                            <!-- Threads -->
                            <label class="flex items-center gap-2 p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                                :class="selectedPlatforms.includes('threads') ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                                <input type="checkbox" value="threads" x-model="selectedPlatforms" class="rounded text-[#007AFF]">
                                <span class="text-xs font-semibold text-black dark:text-white">Threads</span>
                            </label>

                            <!-- TikTok -->
                            <label class="flex items-center gap-2 p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                                :class="selectedPlatforms.includes('tiktok') ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                                <input type="checkbox" value="tiktok" x-model="selectedPlatforms" class="rounded text-[#007AFF]">
                                <span class="text-xs font-semibold text-black dark:text-white">TikTok Photo</span>
                            </label>

                            <!-- LinkedIn -->
                            <label class="flex items-center gap-2 p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                                :class="selectedPlatforms.includes('linkedin') ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                                <input type="checkbox" value="linkedin" x-model="selectedPlatforms" class="rounded text-[#007AFF]">
                                <span class="text-xs font-semibold text-black dark:text-white">LinkedIn</span>
                            </label>
                        </div>
                    </div>

                    <!-- Input Caption & Template Preset -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-black dark:text-white">
                                Teks Caption Postingan <span class="text-red-500">*</span>
                            </label>
                            <button type="button" @click="usePresetCaption()"
                                class="text-[11px] font-bold text-[#007AFF] hover:underline cursor-pointer">
                                🪄 Gunakan Template Caption
                            </button>
                        </div>
                        <textarea x-model="socialContent" rows="4" maxlength="5000"
                            placeholder="Tuliskan copywriting menarik, hook promosi, dan tagar #COOCA #BisnisUMKM..."
                            class="w-full p-3 rounded-[12px] text-xs leading-relaxed bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>

                    <!-- Mode Waktu Publikasi -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer flex items-center gap-2.5"
                            :class="socialTimingMode === 'now' ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                            <input type="radio" value="now" x-model="socialTimingMode" class="text-[#007AFF]">
                            <div>
                                <p class="text-xs font-bold text-black dark:text-white">Kirim Sekarang</p>
                                <p class="text-[10px] text-black/50 dark:text-white/50">Langsung dipublikasikan</p>
                            </div>
                        </label>
                        <label class="p-3 rounded-[12px] border border-black/10 dark:border-white/10 cursor-pointer flex items-center gap-2.5"
                            :class="socialTimingMode === 'schedule' ? 'bg-[#007AFF]/10 border-[#007AFF]' : ''">
                            <input type="radio" value="schedule" x-model="socialTimingMode" class="text-[#007AFF]">
                            <div>
                                <p class="text-xs font-bold text-black dark:text-white">Jadwalkan Tayang</p>
                                <p class="text-[10px] text-black/50 dark:text-white/50">Atur tanggal &amp; jam</p>
                            </div>
                        </label>
                    </div>

                    <!-- Input Jadwal jika Schedule -->
                    <template x-if="socialTimingMode === 'schedule'">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-black dark:text-white">
                                Tanggal &amp; Waktu Penayangan
                            </label>
                            <input type="datetime-local" x-model="socialScheduledAt"
                                class="w-full h-10 px-3 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </template>

                    <!-- Tombol Eksekusi Kirim -->
                    <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/10 gap-2">
                        <a :href="'{{ route('admin.social-media.index') }}?tab=posts&draft_image=' + encodeURIComponent(activeAsset ? activeAsset.image_url : '')"
                            class="text-[11px] font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white">
                            Buka di Form Lengkap Medsos &rarr;
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="socialModalOpen = false"
                                class="px-4 py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/70 dark:text-white/70 cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="submitSocialPost()"
                                :disabled="isPostingSocial || selectedPlatforms.length === 0 || !socialContent.trim()"
                                class="px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="isPostingSocial">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span>Memproses...</span>
                                    </div>
                                </template>
                                <template x-if="!isPostingSocial">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                        <span>Terbitkan Sekarang</span>
                                    </div>
                                </template>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </template>

</div>

<script>
function adminAiStudioVisual() {
    return {
        // Tab Section Bawah
        activeBottomTab: 'inspirations', // 'inspirations' (default) or 'my_assets'

        // Form Generator State
        title: '',
        prompt: @js($campaignPresets[0]['prompt'] ?? ''),
        negativePrompt: @js($campaignPresets[0]['negative_prompt'] ?? ''),
        aspectRatio: '1:1',
        modelName: 'gpt-image-2.5-flare',
        quality: 'high',
        selectedPresetId: 'promo_saas',

        // Enhanced Controls
        contentType: 'saas_promo',
        visualStyle: '3d_apple_glass',
        lightingStyle: 'softbox_studio',
        colorPalette: 'cooca_signature',
        bannerHeadline: '',
        customDirectives: {
            leave_space_for_text: true,
            include_ui_mockup: true,
            floating_3d_badge: false,
            indonesian_msme_context: true
        },

        // Reference Image State
        refMode: 'catalog', // 'catalog' or 'upload'
        selectedInspiration: null, // item object
        uploadedFile: null,
        uploadedReferenceUrl: null,
        uploadedFileName: '',

        // Inspiration Modal State
        inspirationPickerOpen: false,
        modalSearch: '',
        modalCategory: 'all',
        isLoadingInspirations: false,
        modalInspirations: @js($quickReferences ?? []),

        // Generation Process State
        isGenerating: false,
        generationTimer: 0,
        timerInterval: null,

        // Active Canvas Asset
        activeAsset: @js($assets->first() ? [
            'id' => $assets->first()->id,
            'title' => $assets->first()->title,
            'image_url' => $assets->first()->image_url,
            'aspect_ratio' => $assets->first()->aspect_ratio,
            'width' => $assets->first()->width,
            'height' => $assets->first()->height,
            'model_name' => $assets->first()->model_name,
            'is_favorite' => $assets->first()->is_favorite,
            'created_at' => $assets->first()->created_at?->translatedFormat('d M Y, H:i'),
        ] : null),

        // Social Media Modal State
        socialModalOpen: false,
        selectedPlatforms: ['instagram', 'facebook'],
        socialContent: @js($campaignPresets[0]['caption_suggestion'] ?? ''),
        socialTimingMode: 'now',
        socialScheduledAt: '',
        isPostingSocial: false,

        init() {
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        get hasReference() {
            return Boolean(this.selectedInspiration || this.uploadedFile);
        },

        clearReference() {
            this.selectedInspiration = null;
            this.uploadedFile = null;
            this.uploadedReferenceUrl = null;
            this.uploadedFileName = '';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        scrollOrOpenGallery() {
            this.activeBottomTab = 'inspirations';
            const galleryEl = document.getElementById('bentoGallerySection');
            if (galleryEl) {
                galleryEl.scrollIntoView({ behavior: 'smooth' });
            } else {
                this.openInspirationPickerModal();
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file maksimal adalah 10 MB.');
                return;
            }

            this.uploadedFile = file;
            this.uploadedFileName = file.name;
            this.uploadedReferenceUrl = URL.createObjectURL(file);
            this.selectedInspiration = null;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openInspirationPickerModal() {
            this.inspirationPickerOpen = true;
            if (this.modalInspirations.length === 0) {
                this.fetchModalInspirations();
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async fetchModalInspirations() {
            this.isLoadingInspirations = true;
            try {
                const params = new URLSearchParams();
                if (this.modalSearch.trim()) params.append('q', this.modalSearch.trim());
                if (this.modalCategory && this.modalCategory !== 'all') params.append('category', this.modalCategory);

                const response = await fetch('{{ route("admin.ai-studio.visual.inspirations") }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    this.modalInspirations = data.items || [];
                }
            } catch (error) {
                console.error('Gagal mengambil data inspirasi:', error);
            } finally {
                this.isLoadingInspirations = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        selectInspirationFromModal(item) {
            this.selectedInspiration = item;
            this.uploadedFile = null;
            this.uploadedReferenceUrl = null;
            this.inspirationPickerOpen = false;

            // Jika canvas sedang kosong (belum ada gambar render), pastikan activeAsset null agar referensi tampil di canvas
            if (!this.activeAsset) {
                // Biarkan activeAsset null, canvas akan menampilkan gambar referensi terpilih
            }

            // Scroll ke generator di mobile jika perlu
            window.scrollTo({ top: 0, behavior: 'smooth' });

            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        applyInspirationPrompt() {
            if (!this.selectedInspiration) return;
            const hint = this.selectedInspiration.prompt_hint || this.selectedInspiration.caption;
            if (hint) {
                this.prompt = hint;
            }
            if (this.selectedInspiration.title && !this.title) {
                this.title = 'Promosi: ' + this.selectedInspiration.title;
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        applyPreset(preset) {
            this.selectedPresetId = preset.id;
            this.title = preset.title;
            this.prompt = preset.prompt;
            this.negativePrompt = preset.negative_prompt || '';
            this.aspectRatio = preset.ratio || '1:1';
            this.modelName = preset.model || 'gpt-image-2.5-flare';
            this.quality = preset.quality || 'high';
            this.socialContent = preset.caption_suggestion || '';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        autoRecompilePrompt() {
            const stylesMap = @js($visualStyles);
            const lightMap = @js($lightingStyles);
            const palMap = @js($colorPalettes);

            let parts = [];

            if (this.prompt.trim()) {
                parts.push(this.prompt.trim());
            } else {
                parts.push('High-end promotional visual banner for COOCA Business Operating System platform');
            }

            if (stylesMap[this.visualStyle] && stylesMap[this.visualStyle].prompt_fragment) {
                parts.push('Art direction: ' + stylesMap[this.visualStyle].prompt_fragment);
            }

            if (lightMap[this.lightingStyle] && lightMap[this.lightingStyle].prompt_fragment) {
                parts.push('Lighting: ' + lightMap[this.lightingStyle].prompt_fragment);
            }

            if (palMap[this.colorPalette] && palMap[this.colorPalette].prompt_fragment) {
                parts.push('Color scheme: ' + palMap[this.colorPalette].prompt_fragment);
            }

            let directives = [];
            if (this.customDirectives.leave_space_for_text) {
                directives.push('ample negative space for typography and headline banner overlay');
            }
            if (this.customDirectives.include_ui_mockup) {
                directives.push('clean modern 3D glass laptop or tablet displaying COOCA SaaS dashboard interface');
            }
            if (this.customDirectives.floating_3d_badge) {
                directives.push('subtle floating 3D discount badge and promotional ribbon icons');
            }
            if (this.customDirectives.indonesian_msme_context) {
                directives.push('tailored for Indonesian modern MSME entrepreneurship context');
            }
            if (this.bannerHeadline.trim()) {
                directives.push('banner concept themed for: "' + this.bannerHeadline.trim() + '"');
            }

            if (directives.length > 0) {
                parts.push('Requirements: ' + directives.join(', '));
            }

            this.prompt = parts.join('. ') + '.';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        selectAsset(asset) {
            this.activeAsset = {
                id: asset.id,
                title: asset.title,
                image_url: asset.image_url,
                aspect_ratio: asset.aspect_ratio,
                width: asset.width,
                height: asset.height,
                model_name: asset.model_name,
                is_favorite: Boolean(asset.is_favorite),
                created_at: asset.created_at,
            };
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async dispatchGenerate() {
            if (!this.prompt.trim()) return;

            this.isGenerating = true;
            this.generationTimer = 0;
            this.timerInterval = setInterval(() => {
                this.generationTimer++;
            }, 1000);

            try {
                const formData = new FormData();
                formData.append('title', this.title);
                formData.append('prompt', this.prompt);
                formData.append('negative_prompt', this.negativePrompt);
                formData.append('aspect_ratio', this.aspectRatio);
                formData.append('model_name', this.modelName);
                formData.append('quality', this.quality);
                formData.append('content_type', this.contentType);
                formData.append('visual_style', this.visualStyle);
                formData.append('lighting_style', this.lightingStyle);
                formData.append('color_palette', this.colorPalette);
                formData.append('banner_headline', this.bannerHeadline);

                if (this.customDirectives.leave_space_for_text) formData.append('leave_space_for_text', '1');
                if (this.customDirectives.include_ui_mockup) formData.append('include_ui_mockup', '1');
                if (this.customDirectives.floating_3d_badge) formData.append('floating_3d_badge', '1');
                if (this.customDirectives.indonesian_msme_context) formData.append('indonesian_msme_context', '1');

                if (this.selectedInspiration) {
                    formData.append('reference_id', this.selectedInspiration.id);
                    formData.append('reference_inspiration_id', this.selectedInspiration.id);
                } else if (this.uploadedFile) {
                    formData.append('reference_image', this.uploadedFile);
                }

                const response = await fetch('{{ route("admin.ai-studio.visual.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal merender gambar.');
                }

                this.activeAsset = data.asset;
                this.activeBottomTab = 'my_assets';
                alert('Berhasil! Visual promosi platform selesai dirender.');
                window.location.reload();
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat memproses generasi gambar.');
            } finally {
                clearInterval(this.timerInterval);
                this.isGenerating = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        openSocialModal() {
            if (!this.activeAsset) return;
            this.socialModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        usePresetCaption() {
            const currentPreset = @js($campaignPresets).find(p => p.id === this.selectedPresetId);
            if (currentPreset && currentPreset.caption_suggestion) {
                this.socialContent = currentPreset.caption_suggestion;
            } else {
                this.socialContent = '🚀 ' + (this.activeAsset?.title || 'COOCA Business OS') + ' — Permudah operasional bisnis UMKM Indonesia bersama sistem cerdas terintegrasi. Cek selengkapnya di cooca.id! #COOCA #BisnisUMKM';
            }
        },

        async submitSocialPost() {
            if (!this.activeAsset || this.selectedPlatforms.length === 0 || !this.socialContent.trim()) {
                return;
            }

            this.isPostingSocial = true;

            try {
                const url = '{{ url("admin/ai-studio/visual") }}/' + this.activeAsset.id + '/send-to-social-media';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        platforms: this.selectedPlatforms,
                        content: this.socialContent,
                        timing_mode: this.socialTimingMode,
                        scheduled_at: this.socialTimingMode === 'schedule' ? this.socialScheduledAt : null
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mengirim postingan.');
                }

                this.socialModalOpen = false;
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan sistem.');
            } finally {
                this.isPostingSocial = false;
            }
        },

        async toggleFavorite(assetId) {
            try {
                const response = await fetch('{{ url("admin/ai-studio/visual") }}/' + assetId + '/favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (data.success && this.activeAsset && this.activeAsset.id === assetId) {
                    this.activeAsset.is_favorite = data.is_favorite;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async deleteAsset(assetId) {
            if (!confirm('Yakin ingin menghapus berkas materi visual ini?')) return;

            try {
                const response = await fetch('{{ url("admin/ai-studio/visual") }}/' + assetId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus aset.');
                }
            } catch (e) {
                alert('Gagal menghapus aset.');
            }
        },

        copyImageUrl(url) {
            if (!url) return;
            navigator.clipboard.writeText(url).then(() => {
                alert('URL berkas gambar berhasil disalin ke clipboard!');
            }).catch(() => {
                prompt('Salin URL gambar manual:', url);
            });
        }
    };
}
</script>
@endsection
