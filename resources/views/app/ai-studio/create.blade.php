@extends('layouts.app', [
    'title' => 'Inisiasi Proyek Visual AI - AI Studio',
    'headerTitle' => 'Buat Proyek Generasi Visual Baru',
    'headerSubtitle' => 'Konfigurasikan brief kreatif, kaitkan produk katalog, dan tetapkan rasio aspek multi-channel',
])

@section('content')
<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="aiStudioCreateProject()">

    <!-- ========================================== -->
    <!-- 1. BENTO HEADER & BREADCRUMBS              -->
    <!-- ========================================== -->
    <div class="relative z-10 rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('ai-studio.index') }}" class="hover:text-[#007AFF] transition-colors">AI Studio</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-black/80 dark:text-white/80 font-semibold">Proyek Baru</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white shadow-sm shadow-[#007AFF]/25">
                    <i data-lucide="wand-2" class="w-4 h-4"></i>
                </span>
                <span>Inisiasi Brief Kreatif &amp; Visual Produk</span>
            </h1>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                Pilih produk dari katalog bisnis Anda, tentukan channel pemasaran sasaran, dan pratinjau rancangan prompt sebelum eksekusi render.
            </p>
        </div>

        <!-- Credits Balance Badge & Top Up Action -->
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="px-3.5 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-xs font-semibold text-black/80 dark:text-white/90 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="coins" class="w-3 h-3"></i>
                </span>
                <span>Saldo Anda:</span>
                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] text-sm" x-text="userCredits">{{ number_format($creditsBalance) }}</span>
                <span class="text-[10px] text-black/40 dark:text-white/40">kredit</span>
            </div>

            <!-- Top Up Modal Trigger -->
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[12px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-sm shadow-orange-500/20 active:scale-[0.98] transition-all">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                <span>Top Up</span>
            </button>

            <a href="{{ route('ai-studio.index') }}"
                class="px-3.5 py-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold text-black/70 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                Batal
            </a>
        </div>
    </div>

    <!-- Alert Banner when Monthly Credits are Expended (0 Balance) -->
    @if ($creditsBalance <= 0)
        <div class="p-4 rounded-[16px] bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10 border border-orange-500/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Kredit Bulanan AI Studio Anda Telah Habis (0 Kredit)</h4>
                    <p class="text-[11px] sm:text-xs text-black/60 dark:text-white/60">
                        Top up kredit sekarang via QRIS / Virtual Account untuk mulai membuat variasi visual katalog baru.
                    </p>
                </div>
            </div>
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-sm shadow-orange-500/20 transition-all shrink-0">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                <span>Top Up Sekarang</span>
            </button>
        </div>
    @endif

    <!-- Error Alerts if any -->
    @if ($errors->any())
        <div class="p-4 rounded-[14px] bg-red-500/10 border border-red-500/20 text-xs text-red-600 dark:text-red-400 space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Terdapat kesalahan pada isian form:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- 2. MAIN 2-COLUMN BENTO FORM                -->
    <!-- ========================================== -->
    <form id="projectForm" method="POST" action="{{ route('ai-studio.projects.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: Creative Brief & Model Specs (7 Cols) -->
            <div class="lg:col-span-7 space-y-5">

                <!-- Bento Card 1: Project Basic Info -->
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>1. Identitas &amp; Brief Proyek</span>
                    </h3>

                    <!-- Project Name -->
                    <div class="space-y-1.5">
                        <label for="projectName" class="text-xs font-semibold text-black dark:text-white">
                            Nama Proyek Kreatif <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="projectName" name="name" x-model="projectName" required maxlength="150"
                            placeholder="Contoh: Katalog Kopi Gayo Robusta - Instagram Square"
                            class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <!-- Target Channel Selector -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-black dark:text-white">
                            Target Channel Pemasaran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'instagram' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="instagram" x-model="targetChannel" @change="onChannelChange('instagram')" class="sr-only">
                                <span class="text-xs font-bold">Instagram</span>
                                <span class="text-[10px] opacity-70">Feed &amp; Carousel (1:1)</span>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'tiktok' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="tiktok" x-model="targetChannel" @change="onChannelChange('tiktok')" class="sr-only">
                                <span class="text-xs font-bold">TikTok</span>
                                <span class="text-[10px] opacity-70">Vertical Video Cover (9:16)</span>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'shopee' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="shopee" x-model="targetChannel" @change="onChannelChange('shopee')" class="sr-only">
                                <span class="text-xs font-bold">Shopee</span>
                                <span class="text-[10px] opacity-70">Katalog Produk (1:1)</span>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'tokopedia' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="tokopedia" x-model="targetChannel" @change="onChannelChange('tokopedia')" class="sr-only">
                                <span class="text-xs font-bold">Tokopedia</span>
                                <span class="text-[10px] opacity-70">Etalase Toko (1:1)</span>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'banner' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="banner" x-model="targetChannel" @change="onChannelChange('banner')" class="sr-only">
                                <span class="text-xs font-bold">Banner Web</span>
                                <span class="text-[10px] opacity-70">Landscape Hero (16:9)</span>
                            </label>

                            <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all"
                                :class="targetChannel === 'facebook' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5'">
                                <input type="radio" name="target_channel" value="facebook" x-model="targetChannel" @change="onChannelChange('facebook')" class="sr-only">
                                <span class="text-xs font-bold">Facebook</span>
                                <span class="text-[10px] opacity-70">Ads &amp; Post (4:5)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Aspect Ratio Selector -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-black dark:text-white">
                            Rasio Dimensi Kanvas <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <label class="p-2.5 rounded-[10px] border text-center cursor-pointer transition-all text-xs font-bold"
                                :class="aspectRatio === '1:1' ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="aspect_ratio" value="1:1" x-model="aspectRatio" class="sr-only">
                                <span>1:1</span>
                                <span class="block text-[10px] font-normal opacity-75">Kotak</span>
                            </label>
                            <label class="p-2.5 rounded-[10px] border text-center cursor-pointer transition-all text-xs font-bold"
                                :class="aspectRatio === '4:5' ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="aspect_ratio" value="4:5" x-model="aspectRatio" class="sr-only">
                                <span>4:5</span>
                                <span class="block text-[10px] font-normal opacity-75">Potret</span>
                            </label>
                            <label class="p-2.5 rounded-[10px] border text-center cursor-pointer transition-all text-xs font-bold"
                                :class="aspectRatio === '9:16' ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="aspect_ratio" value="9:16" x-model="aspectRatio" class="sr-only">
                                <span>9:16</span>
                                <span class="block text-[10px] font-normal opacity-75">Story/Reels</span>
                            </label>
                            <label class="p-2.5 rounded-[10px] border text-center cursor-pointer transition-all text-xs font-bold"
                                :class="aspectRatio === '16:9' ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="aspect_ratio" value="16:9" x-model="aspectRatio" class="sr-only">
                                <span>16:9</span>
                                <span class="block text-[10px] font-normal opacity-75">Lanskap</span>
                            </label>
                        </div>
                    </div>

                    <!-- Style Preset Selector -->
                    <div class="space-y-1.5">
                        <label for="stylePreset" class="text-xs font-semibold text-black dark:text-white">
                            Preset Gaya Estetika Visual
                        </label>
                        <select id="stylePreset" name="style_preset" x-model="stylePreset"
                            class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="clean_commercial">Clean Commercial (Pencahayaan Terang, Studio Bersih)</option>
                            <option value="studio_minimalist">Studio Minimalist (Latar Netral Pastel, Bayangan Halus)</option>
                            <option value="editorial_vibrant">Editorial Vibrant (Warna Berani &amp; Kontras Modern)</option>
                            <option value="lifestyle_warm">Lifestyle Warm (Nuansa Alami, Hangat &amp; Human Touch)</option>
                            <option value="flatlay_overhead">Flatlay Overhead (Tampak Atas Rapi Bersih)</option>
                            <option value="cinematic_dark">Cinematic Dark Mood (Dramatis, Eksklusif &amp; Elegan)</option>
                        </select>
                    </div>

                    <!-- Custom Notes / Mood Brief -->
                    <div class="space-y-1.5">
                        <label for="customNotes" class="text-xs font-semibold text-black dark:text-white">
                            Catatan Khusus / Deskripsi Tambahan
                        </label>
                        <textarea id="customNotes" name="custom_notes" x-model="customNotes" rows="3" maxlength="1000"
                            placeholder="Contoh: Tambahkan pencahayaan sore keemasan dari jendela kayu, meja marmer putih dengan taburan biji kopi segar di sekitarnya."
                            class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                    </div>
                </div>

                <!-- Bento Card 2: AI Engine Model Selector -->
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="cpu" class="w-4 h-4 text-[#5856D6]"></i>
                        <span>2. Pilihan Mesin Generative AI</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Ideogram v2 -->
                        <label class="p-3.5 rounded-[14px] border cursor-pointer transition-all flex flex-col justify-between space-y-2"
                            :class="selectedModel === 'ideogram-v2' ? 'bg-[#007AFF]/10 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 hover:bg-black/5'">
                            <input type="radio" name="ai_model" value="ideogram-v2" x-model="selectedModel" @change="onModelChange('ideogram', 'ideogram-v2', 15)" class="sr-only">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-black dark:text-white">Ideogram v2</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#007AFF]/20 text-[#007AFF]">Rekomendasi</span>
                                </div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-1">Unggul dalam tipografi tajam, rendering logo, dan label kemasan.</p>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs font-bold text-[#007AFF]">
                                <span>15 Kredit</span>
                                <i data-lucide="check" class="w-3.5 h-3.5" x-show="selectedModel === 'ideogram-v2'"></i>
                            </div>
                        </label>

                        <!-- DALL-E 3 -->
                        <label class="p-3.5 rounded-[14px] border cursor-pointer transition-all flex flex-col justify-between space-y-2"
                            :class="selectedModel === 'dall-e-3' ? 'bg-[#007AFF]/10 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 hover:bg-black/5'">
                            <input type="radio" name="ai_model" value="dall-e-3" x-model="selectedModel" @change="onModelChange('openai', 'dall-e-3', 20)" class="sr-only">
                            <div>
                                <span class="text-xs font-bold text-black dark:text-white">DALL-E 3</span>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-1">Fotografi realistik tinggi dengan interpretasi prompt kaya konteks.</p>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs font-bold text-[#007AFF]">
                                <span>20 Kredit</span>
                                <i data-lucide="check" class="w-3.5 h-3.5" x-show="selectedModel === 'dall-e-3'"></i>
                            </div>
                        </label>

                        <!-- SDXL -->
                        <label class="p-3.5 rounded-[14px] border cursor-pointer transition-all flex flex-col justify-between space-y-2"
                            :class="selectedModel === 'sdxl' ? 'bg-[#007AFF]/10 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 hover:bg-black/5'">
                            <input type="radio" name="ai_model" value="sdxl" x-model="selectedModel" @change="onModelChange('stability', 'sdxl', 10)" class="sr-only">
                            <div>
                                <span class="text-xs font-bold text-black dark:text-white">SDXL Fast</span>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-1">Generasi super cepat &amp; hemat untuk eksplorasi variasi ide awal.</p>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs font-bold text-[#007AFF]">
                                <span>10 Kredit</span>
                                <i data-lucide="check" class="w-3.5 h-3.5" x-show="selectedModel === 'sdxl'"></i>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Source Assets & Reference (5 Cols) -->
            <div class="lg:col-span-5 space-y-5">

                <!-- Bento Card 3: Objek Produk / Jasa (Katalog atau Upload Mandiri) -->
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                            <i data-lucide="package" class="w-4 h-4 text-[#34C759]"></i>
                            <span>3. Objek Produk / Layanan</span>
                        </h3>

                        <!-- Tab Switcher: Katalog vs Upload Sendiri -->
                        <div class="flex p-0.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-semibold">
                            <button type="button" @click="productTab = 'catalog'"
                                class="px-2.5 py-1 rounded-[8px] transition-all"
                                :class="productTab === 'catalog' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black'">
                                Katalog Bisnis
                            </button>
                            <button type="button" @click="productTab = 'upload'"
                                class="px-2.5 py-1 rounded-[8px] transition-all"
                                :class="productTab === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black'">
                                Upload Foto Baru
                            </button>
                        </div>
                    </div>

                    <!-- TAB A: Pilih dari Katalog Produk & Jasa -->
                    <div x-show="productTab === 'catalog'" class="space-y-3">
                        <div class="space-y-1.5">
                            <label for="productId" class="text-xs font-semibold text-black dark:text-white">Pilih dari Daftar Produk / Jasa</label>
                            <select id="productId" name="product_id" x-model="selectedProductId" @change="onProductSelect($event)"
                                class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="">-- Tanpa Produk (Materi Grafis Umum / Promo) --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-type="{{ $product->type }}" data-img="{{ $product->image_path }}">
                                        [{{ $product->type === 'service' ? 'JASA' : 'BARANG' }}] {{ $product->name }} ({{ $product->code ?: 'SKU' }}) - Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                Produk terpilih akan menjadi fokus utama komposisi foto dan atributnya diintegrasikan ke prompt AI.
                            </p>
                        </div>
                    </div>

                    <!-- TAB B: Upload Foto Objek Sendiri Langsung (Bakso, Makanan, Jasa, dsb) -->
                    <div x-show="productTab === 'upload'" class="space-y-3" style="display: none;">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-black dark:text-white">Nama Produk / Objek / Jasa</label>
                            <input type="text" name="custom_product_name" x-model="customProductName"
                                placeholder="Contoh: Bakso Urat Kuah Pedas / Jasa Cuci Sepatu Deep Clean"
                                class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-black dark:text-white">Foto Objek / Produk Asli</label>
                            
                            <!-- Dropzone Upload Area -->
                            <div x-show="!customProductPreview"
                                class="relative border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#34C759] rounded-[14px] p-4 text-center cursor-pointer transition-colors bg-black/[0.01] hover:bg-[#34C759]/5"
                                @click="document.getElementById('customProductFileInput').click()">
                                <input type="file" id="customProductFileInput" name="custom_product_image" accept="image/*" @change="onCustomProductChange($event)" class="sr-only">
                                <div class="w-10 h-10 mx-auto rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center mb-2">
                                    <i data-lucide="camera" class="w-5 h-5"></i>
                                </div>
                                <p class="text-xs font-bold text-black dark:text-white">Klik untuk upload foto objek</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">JPG, PNG, atau WebP (Maks. 5MB). Misal foto mangkok bakso asli dari HP.</p>
                            </div>

                            <!-- Preview Box -->
                            <div x-show="customProductPreview" class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center gap-3" style="display: none;">
                                <img :src="customProductPreview" alt="Preview Objek" class="w-14 h-14 rounded-[10px] object-cover shrink-0 border border-black/10">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-black dark:text-white truncate" x-text="customProductName || 'Foto Objek Produk'">Foto Objek Produk</h5>
                                    <p class="text-[10px] text-[#34C759] font-semibold flex items-center gap-1 mt-0.5">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>Siap diintegrasikan ke komposisi AI</span>
                                    </p>
                                </div>
                                <button type="button" @click="clearCustomProduct()" class="p-1.5 rounded-full text-black/40 hover:text-red-500 hover:bg-black/5" title="Hapus Gambar">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Role of Product in Asset -->
                    <div class="space-y-1.5 pt-1 border-t border-black/5 dark:border-white/5">
                        <label class="text-xs font-semibold text-black dark:text-white">Peran Objek dalam Komposisi</label>
                        <select name="role" x-model="productRole"
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="hero_product">Produk Utama (Hero Product - Di Tengah &amp; Fokus Tajam)</option>
                            <option value="packaging">Sajian Mangkok / Piring / Kemasan (Fokus Presentasi Makanan)</option>
                            <option value="logo">Aplikasi Logo &amp; Merk Dagang</option>
                            <option value="supporting">Elemen Pendukung Komposisi</option>
                        </select>
                    </div>
                </div>

                <!-- Bento Card 4: Referensi Inspirasi Visual (Galeri atau Upload Sendiri) -->
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                            <i data-lucide="compass" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>4. Referensi Gaya Visual</span>
                        </h3>

                        <!-- Tab Switcher: Galeri vs Upload Referensi Sendiri -->
                        <div class="flex p-0.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-semibold">
                            <button type="button" @click="referenceTab = 'gallery'"
                                class="px-2.5 py-1 rounded-[8px] transition-all"
                                :class="referenceTab === 'gallery' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black'">
                                Galeri COOCA
                            </button>
                            <button type="button" @click="referenceTab = 'upload'"
                                class="px-2.5 py-1 rounded-[8px] transition-all"
                                :class="referenceTab === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black'">
                                Upload Referensi
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="inspiration_item_id" :value="selectedInspirationId">

                    <!-- TAB A: Galeri Template COOCA -->
                    <div x-show="referenceTab === 'gallery'" class="space-y-3">
                        @if ($selectedInspiration)
                            <div x-show="selectedInspirationId" class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center gap-3">
                                <img src="{{ $selectedInspiration->thumbnail_url ?: $selectedInspiration->canonical_url }}" alt="{{ $selectedInspiration->title }}"
                                    class="w-14 h-14 rounded-[10px] object-cover shrink-0 border border-black/10">
                                <div class="flex-1 min-w-0 space-y-0.5">
                                    <h4 class="text-xs font-bold text-black dark:text-white truncate">{{ $selectedInspiration->title }}</h4>
                                    <div class="flex items-center gap-1.5 text-[10px] text-black/50 dark:text-white/50">
                                        <span class="inline-flex items-center gap-0.5 text-[#34C759] font-semibold">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                                            <span>Lisensi Komersial Sah</span>
                                        </span>
                                    </div>
                                </div>
                                <button type="button" @click="clearInspiration()"
                                    class="p-1.5 rounded-full text-black/40 dark:text-white/40 hover:text-red-500 hover:bg-black/5 transition-colors"
                                    title="Hapus Referensi">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        @else
                            <div x-show="!selectedInspirationId" class="p-4 rounded-[14px] border border-dashed border-black/15 dark:border-white/15 text-center space-y-2">
                                <p class="text-xs text-black/60 dark:text-white/60">
                                    Pilih template foto produk/makanan dari 197 inspirasi viral terkurasi COOCA.
                                </p>
                                <a href="{{ route('ai-studio.inspiration.index') }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] text-xs font-semibold text-black/80 dark:text-white transition-all">
                                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                    <span>Pilih di Galeri Inspirasi (197 Template)</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- TAB B: Upload Referensi Sendiri (Dari HP / Pinterest) -->
                    <div x-show="referenceTab === 'upload'" class="space-y-3" style="display: none;">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-black dark:text-white">Foto Gaya / Poster Referensi Anda</label>
                            
                            <div x-show="!customReferencePreview"
                                class="relative border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#FF9500] rounded-[14px] p-4 text-center cursor-pointer transition-colors bg-black/[0.01] hover:bg-[#FF9500]/5"
                                @click="document.getElementById('customRefFileInput').click()">
                                <input type="file" id="customRefFileInput" name="custom_reference_image" accept="image/*" @change="onCustomReferenceChange($event)" class="sr-only">
                                <div class="w-10 h-10 mx-auto rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mb-2">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                                <p class="text-xs font-bold text-black dark:text-white">Upload contoh gaya / pencahayaan</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">AI akan meniru komposisi, tone warna, dan pencahayaan foto ini.</p>
                            </div>

                            <!-- Preview Box -->
                            <div x-show="customReferencePreview" class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center gap-3" style="display: none;">
                                <img :src="customReferencePreview" alt="Preview Referensi" class="w-14 h-14 rounded-[10px] object-cover shrink-0 border border-black/10">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-black dark:text-white truncate">Referensi Visual Mandiri</h5>
                                    <p class="text-[10px] text-[#FF9500] font-semibold flex items-center gap-1 mt-0.5">
                                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                                        <span>Gaya &amp; lighting akan diaplikasikan ke produk Anda</span>
                                    </p>
                                </div>
                                <button type="button" @click="clearCustomReference()" class="p-1.5 rounded-full text-black/40 hover:text-red-500 hover:bg-black/5" title="Hapus Gambar">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bento Card 5: Actions & Live Quote Summary -->
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-black/60 dark:text-white/60">Estimasi Kredit:</span>
                        <span class="text-lg font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="estimatedCost + ' Kredit'">15 Kredit</span>
                    </div>

                    <div class="space-y-2">
                        <!-- Primary Button: Tinjau & Kompilasi Prompt (TASK-26 Modal Trigger) -->
                        <button type="button" @click="compileAndPreviewPrompt()" :disabled="compilingPrompt"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0062cc] disabled:opacity-50 text-white text-xs sm:text-sm font-bold shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                            <i data-lucide="sparkles" class="w-4 h-4" x-show="!compilingPrompt"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="compilingPrompt" style="display: none;"></i>
                            <span x-text="compilingPrompt ? 'Mengompilasi Visi & Prompt...' : 'Tinjau & Kompilasi Prompt AI'">Tinjau &amp; Kompilasi Prompt AI</span>
                        </button>

                        <!-- Secondary Button: Simpan Draf Saja -->
                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                            Simpan Sebagai Draf Proyek
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>

    <!-- Include Modal Sheet Transparansi Prompt (TASK-26) -->
    @include('app.ai-studio.prompt-preview')

    <!-- Include Modal Top Up Kredit AI Studio -->
    @include('app.ai-studio.partials.topup-modal')

</div>

@push('scripts')
<script>
function aiStudioCreateProject() {
    return {
        projectName: @json($selectedInspiration ? 'Replikasi - ' . $selectedInspiration->title : ''),
        targetChannel: '{{ ($selectedInspiration?->visualAnalysis?->suggested_aspect_ratios[0] ?? "") === "9:16" ? "tiktok" : (($selectedInspiration?->visualAnalysis?->suggested_aspect_ratios[0] ?? "") === "16:9" ? "banner" : (($selectedInspiration?->visualAnalysis?->suggested_aspect_ratios[0] ?? "") === "4:5" ? "facebook" : "instagram")) }}',
        aspectRatio: '{{ $selectedInspiration?->visualAnalysis?->suggested_aspect_ratios[0] ?? "1:1" }}',
        stylePreset: 'clean_commercial',
        customNotes: @json($selectedInspiration?->visualAnalysis?->extracted_style_prompt ?? ''),
        selectedProductId: '',
        productRole: 'hero_product',
        selectedInspirationId: '{{ $selectedInspiration?->id ?? "" }}',
        selectedProvider: 'ideogram',
        selectedModel: 'ideogram-v2',
        estimatedCost: 15,
        userCredits: {{ $creditsBalance }},
        canAfford: {{ $creditsBalance >= 15 ? 'true' : 'false' }},

        // Objek Produk & Upload State
        productTab: 'catalog',
        customProductName: '',
        customProductPreview: null,

        // Referensi Visual & Upload State
        referenceTab: 'gallery',
        customReferencePreview: null,

        // Prompt Preview Modal State
        promptModalOpen: false,
        compilingPrompt: false,
        compiledPositivePrompt: '',
        compiledNegativePrompt: '',
        visionAttributes: [],
        quoteCost: 15,

        onCustomProductChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.customProductPreview = URL.createObjectURL(file);
                if (!this.projectName && this.customProductName) {
                    this.projectName = 'Fotografi ' + this.customProductName;
                }
                this.$nextTick(() => lucide.createIcons());
            }
        },

        clearCustomProduct() {
            this.customProductPreview = null;
            const input = document.getElementById('customProductFileInput');
            if (input) input.value = '';
        },

        onCustomReferenceChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.customReferencePreview = URL.createObjectURL(file);
                this.$nextTick(() => lucide.createIcons());
            }
        },

        clearCustomReference() {
            this.customReferencePreview = null;
            const input = document.getElementById('customRefFileInput');
            if (input) input.value = '';
        },

        onChannelChange(channel) {
            if (channel === 'tiktok') {
                this.aspectRatio = '9:16';
            } else if (channel === 'banner') {
                this.aspectRatio = '16:9';
            } else if (channel === 'facebook') {
                this.aspectRatio = '4:5';
            } else {
                this.aspectRatio = '1:1';
            }
        },

        onModelChange(provider, model, cost) {
            this.selectedProvider = provider;
            this.selectedModel = model;
            this.estimatedCost = cost;
            this.canAfford = this.userCredits >= cost;
        },

        onProductSelect(event) {
            const select = event.target;
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value && !this.projectName) {
                const prodName = selectedOpt.getAttribute('data-name');
                this.projectName = 'Fotografi ' + prodName;
            }
        },

        clearInspiration() {
            this.selectedInspirationId = '';
        },

        async compileAndPreviewPrompt() {
            if (!this.projectName && this.customProductName) {
                this.projectName = 'Fotografi ' + this.customProductName;
            }

            if (!this.projectName) {
                alert('Silakan masukkan nama proyek atau nama produk terlebih dahulu.');
                document.getElementById('projectName')?.focus();
                return;
            }

            this.compilingPrompt = true;

            try {
                const payload = {
                    name: this.projectName,
                    target_channel: this.targetChannel,
                    aspect_ratio: this.aspectRatio,
                    style_preset: this.stylePreset,
                    custom_notes: this.customNotes,
                    product_id: this.productTab === 'catalog' ? (this.selectedProductId || null) : null,
                    custom_product_name: this.productTab === 'upload' ? (this.customProductName || null) : null,
                    inspiration_item_id: this.referenceTab === 'gallery' ? (this.selectedInspirationId || null) : null,
                    provider: this.selectedProvider,
                    model_name: this.selectedModel,
                };

                const response = await fetch('{{ route("ai-studio.prompt.compile") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mengompilasi prompt.');
                }

                this.compiledPositivePrompt = data.compiled_prompt?.positive_prompt || data.compiled_prompt?.prompt_text || '';
                this.compiledNegativePrompt = data.compiled_prompt?.negative_prompt || '';
                this.visionAttributes = data.compiled_prompt?.vision_attributes || [];
                this.quoteCost = data.quote?.credit_cost || this.estimatedCost;
                this.canAfford = data.can_afford ?? (this.userCredits >= this.quoteCost);

                this.promptModalOpen = true;
                this.$nextTick(() => lucide.createIcons());
            } catch (err) {
                alert('Terjadi kesalahan: ' + err.message);
            } finally {
                this.compilingPrompt = false;
            }
        },

        confirmAndSubmitProject() {
            // Submit form to store project
            document.getElementById('projectForm').submit();
        },

        init() {
            lucide.createIcons();
            window.addEventListener('ai-credits-updated', (e) => {
                if (e.detail && typeof e.detail.credits !== 'undefined') {
                    this.userCredits = e.detail.credits;
                    this.canAfford = this.userCredits >= this.estimatedCost;
                }
            });
        }
    };
}
</script>
@endpush
@endsection
