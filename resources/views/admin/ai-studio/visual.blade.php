@extends('layouts.admin', [
    'title' => 'AI Studio Visual Creator - Admin Console',
    'headerTitle' => 'Studio Visual & Materi Media Sosial AI',
    'headerSubtitle' => 'Rancang dan render konten promosi platform, banner kampanye, dan publikasikan langsung ke media sosial',
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
                        Platform Marketing
                    </span>
                </div>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Hasilkan materi visual promosi resmi COOCA, banner SaaS, infografis UMKM, dan jadwalkan postingan ke kanal Instagram, Facebook, Threads, LinkedIn, atau TikTok.
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
                <span>Sumber Inspirasi &amp; Tren</span>
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
        <!-- Metric 1: Total Aset Visual -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Materi Visual</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_assets'] }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">aset tersimpan</span>
            </div>
        </div>

        <!-- Metric 2: Aset Favorit -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Aset Favorit</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9500]/20 dark:text-[#FF9F0A] flex items-center justify-center">
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-[#FF9500]"></i>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_favorites'] }}</span>
                <span class="text-[11px] text-black/50 dark:text-white/50">unggulan</span>
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
                    <span class="text-[11px] text-black/40 dark:text-white/40">OpenAI GPT Image 2.5</span>
                </div>

                <!-- Template Kampanye Cepat (Preset Chips) -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-black dark:text-white">
                        Preset Kampanye Resmi COOCA
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($campaignPresets as $preset)
                            <button type="button"
                                @click="applyPreset(@js($preset))"
                                :class="selectedPresetId === '{{ $preset['id'] }}' ? 'bg-[#007AFF] text-white' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/80 hover:bg-black/[0.08]'"
                                class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                <span>{{ $preset['title'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Pilihan Rasio Media Sosial -->
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

                <!-- Pilihan Model & Kualitas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-black dark:text-white">
                            Model AI
                        </label>
                        <select x-model="modelName"
                            class="w-full h-10 px-3 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="gpt-image-2.5-flare">GPT Image 2.5 Flare (Cepat &amp; Harian)</option>
                            <option value="gpt-image-2.5-sunburst">GPT Image 2.5 Sunburst (Ultra Detail)</option>
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

                <!-- Judul Materi Promosi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-black dark:text-white">
                        Judul Materi Visual (Label Internal)
                    </label>
                    <input type="text" x-model="title" placeholder="Contoh: Banner Promo Langganan Tahunan Q4"
                        class="w-full h-10 px-3.5 rounded-[12px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <!-- Input Prompt Positif -->
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

                <!-- Negative Prompt (Accordion) -->
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
                            placeholder="blur, text watermark, ugly hands, low resolution, bad lighting..."
                            class="w-full p-2.5 rounded-[10px] text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>
                </div>

                <!-- Tombol Action Render Utama -->
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
                    <template x-if="activeAsset">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]" x-text="activeAsset.aspect_ratio"></span>
                            <span class="text-[11px] text-black/40 dark:text-white/40" x-text="activeAsset.width + ' × ' + activeAsset.height + ' px'"></span>
                        </div>
                    </template>
                </div>

                <!-- Frame Gambar Hasil Render -->
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
                                    Model OpenAI sedang menyusun komposisi pencahayaan studio dan rasio aspek yang dipilih...
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Active Image Display -->
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

                    <!-- Empty Canvas Placeholder -->
                    <template x-if="!isGenerating && !activeAsset">
                        <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
                            <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center">
                                <i data-lucide="image" class="w-6 h-6"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-black dark:text-white">Belum Ada Visual yang Dipilih</h4>
                                <p class="text-[11px] text-black/50 dark:text-white/50 max-w-xs">
                                    Pilih preset kampanye di samping atau klik tombol Generate untuk menciptakan visual promosi baru.
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

            <!-- Bento Card 3: Galeri Riwayat Aset Admin -->
            <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="grid" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>3. Galeri Materi Visual Tersimpan</span>
                    </h3>

                    <!-- Filter Chips Rasio -->
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
                    <div class="p-8 text-center rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="folder-open" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto mb-2"></i>
                        <p class="text-xs font-semibold text-black/60 dark:text-white/60">Belum ada berkas visual yang tersimpan untuk filter ini.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- 4. MODAL: 1-CLICK KIRIM KE SOCIAL MEDIA    -->
    <!-- ========================================== -->
    <template x-teleport="body">
        <div x-show="socialModalOpen" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            role="dialog" aria-modal="true"
            @keydown.escape.window="socialModalOpen = false"
            style="display: none;">

            <!-- Backdrop -->
            <div x-show="socialModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="socialModalOpen = false"></div>

            <!-- Modal Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="socialModalOpen = false">
                <div @click.away="socialModalOpen = false"
                    class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 shadow-2xl space-y-5">

                    <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">
                                    Kirim ke Social Media Platform
                                </h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">
                                    Publikasikan aset visual AI ke kanal resmi COOCA secara simultan
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="socialModalOpen = false"
                            class="p-1.5 rounded-full text-black/40 hover:bg-black/5 dark:text-white/40 dark:hover:bg-white/5 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Pratinjau Gambar Terpilih -->
                    <template x-if="activeAsset">
                        <div class="flex items-center gap-3.5 p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10">
                            <img :src="activeAsset.image_url" :alt="activeAsset.title"
                                class="w-14 h-14 rounded-[10px] object-cover shrink-0 border border-black/10 dark:border-white/10">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-black dark:text-white truncate" x-text="activeAsset.title"></h4>
                                <p class="text-[10px] text-black/50 dark:text-white/50" x-text="'Rasio ' + activeAsset.aspect_ratio + ' • ' + activeAsset.width + 'x' + activeAsset.height + ' px'"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Pilihan Multi-Kanal Social Media -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-black dark:text-white">
                            Pilih Saluran Publikasi <span class="text-red-500">*</span>
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
                                class="px-4 py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/70 dark:text-white/70">
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
        // Form Generator State
        title: '',
        prompt: @js($campaignPresets[0]['prompt'] ?? ''),
        negativePrompt: @js($campaignPresets[0]['negative_prompt'] ?? ''),
        aspectRatio: '1:1',
        modelName: 'gpt-image-2.5-flare',
        quality: 'high',
        selectedPresetId: 'promo_saas',

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
                const response = await fetch('{{ route("admin.ai-studio.visual.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        title: this.title,
                        prompt: this.prompt,
                        negative_prompt: this.negativePrompt,
                        aspect_ratio: this.aspectRatio,
                        model_name: this.modelName,
                        quality: this.quality
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal merender gambar.');
                }

                this.activeAsset = data.asset;
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

