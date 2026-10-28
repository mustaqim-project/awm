@extends('layouts.app', [
    'title' => $project->name . ' - Canvas Studio AI',
    'headerTitle' => 'Canvas Studio & Output Visual AI',
    'headerSubtitle' => 'Pantau proses render, sesuaikan variasi prompt, dan simpan hasil visual ke katalog produk',
])

@section('content')
@php
    $latestGeneration = $project->generations->first();
    $primaryAsset = $latestGeneration?->assets?->first();
    $attachedProduct = $project->assets->firstWhere('asset_type', 'original_product')?->product;
    $referenceItem = $project->assets->firstWhere('asset_type', 'reference_style')?->inspirationItem;
    $latestPrompt = $project->promptVersions->first();
    $isCurrentlyRendering = $latestGeneration && in_array($latestGeneration->status, ['queued', 'generating', 'processing']);
@endphp

<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="aiStudioCanvas({
    projectId: '{{ $project->id }}',
    activeGenerationId: '{{ $latestGeneration?->id ?? "" }}',
    isRendering: {{ $isCurrentlyRendering ? 'true' : 'false' }},
    creditsBalance: {{ $creditsBalance }},
    hasAttachedProduct: {{ $attachedProduct ? 'true' : 'false' }},
    attachedProductId: '{{ $attachedProduct?->id ?? "" }}',
    activeAssetId: '{{ $primaryAsset?->id ?? "" }}',
    isFavorite: {{ $primaryAsset?->is_selected_favorite ? 'true' : 'false' }}
})">

    <!-- ========================================== -->
    <!-- 1. BENTO HEADER & STATUS COCKPIT           -->
    <!-- ========================================== -->
    <div class="relative z-10 rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('ai-studio.index') }}" class="hover:text-[#007AFF] transition-colors">AI Studio</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-black/80 dark:text-white/80 font-semibold truncate max-w-[200px]">{{ $project->name }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white">
                    {{ $project->name }}
                </h1>
                <!-- Platform & Aspect Pill -->
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                    {{ $project->target_platform ?? 'Instagram' }} ({{ $latestPrompt?->aspect_ratio ?? '1:1' }})
                </span>
                <!-- Project Status Pill -->
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                    :class="{
                        'bg-[#34C759]/15 text-[#34C759]': currentStatus === 'completed' || currentStatus === 'succeeded',
                        'bg-[#007AFF]/15 text-[#007AFF] animate-pulse': currentStatus === 'generating' || isRendering,
                        'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60': currentStatus === 'draft',
                        'bg-red-500/15 text-red-500': currentStatus === 'failed'
                    }"
                    x-text="statusLabel">
                    {{ ucfirst($project->status) }}
                </span>
            </div>

            <p class="text-xs text-black/60 dark:text-white/60">
                {{ $project->description ?: 'Proyek materi visual pemasaran multi-channel.' }}
            </p>
        </div>

        <!-- Cockpit Actions & Credits Balance -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <!-- Saldo Pill -->
            <div class="px-3.5 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-xs font-semibold text-black/80 dark:text-white/90 flex items-center gap-2">
                <i data-lucide="coins" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                <span>Saldo:</span>
                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="creditsBalance">{{ number_format($creditsBalance) }}</span>
            </div>

            <!-- Top Up Button -->
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[12px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-sm shadow-orange-500/20 active:scale-[0.98] transition-all">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                <span>Top Up</span>
            </button>

            <!-- New Generation Button -->
            <button type="button" @click="generationDrawerOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-sm shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>+ Render Variasi Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <div x-show="flashMessage" x-transition.opacity
        class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-xs font-semibold text-[#34C759] flex items-center justify-between"
        style="display: none;">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            <span x-text="flashMessage"></span>
        </div>
        <button type="button" @click="flashMessage = ''" class="text-black/40 hover:text-black">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- 2. CANVAS MAIN WORKSPACE (2 Columns)      -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: Primary Canvas & Output View (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">

            <!-- Active Canvas Showcase Card -->
            <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">

                <!-- STATE A: Active Rendering In Progress (Shimmer Skeleton & Alpine Poller) -->
                <div x-show="isRendering" class="space-y-4 py-8 text-center" style="display: none;">
                    <div class="relative w-48 h-48 mx-auto rounded-[20px] bg-gradient-to-tr from-[#007AFF]/15 via-[#5856D6]/15 to-[#FF2D55]/15 border border-[#007AFF]/20 flex flex-col items-center justify-center overflow-hidden animate-pulse">
                        <div class="w-12 h-12 rounded-full bg-white/80 dark:bg-[#1C1C1E]/80 shadow-md flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i>
                        </div>
                    </div>
                    <div class="space-y-1.5 max-w-sm mx-auto">
                        <h3 class="text-sm font-bold text-black dark:text-white">Sedang Merender Visual AI...</h3>
                        <p class="text-xs text-black/60 dark:text-white/60">
                            Mesin AI sedang mengomposisikan objek, pencahayaan, dan detail foto produk. Proses ini biasanya memerlukan waktu 10 hingga 25 detik.
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                        <span>Sinkronisasi Status Otomatis Aktif</span>
                    </div>
                </div>

                <!-- STATE B: Completed Generation Output (High-Res Canvas) -->
                <div x-show="!isRendering && activeImageUrl" class="space-y-4">
                    <!-- Image Frame -->
                    <div class="relative rounded-[16px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 group">
                        <img :src="activeImageUrl" alt="Visual Render"
                            class="w-full h-auto object-contain max-h-[600px] mx-auto transition-transform duration-300">

                        <!-- Floating Badges Top Left -->
                        <div class="absolute top-3 left-3 flex flex-wrap items-center gap-1.5 pointer-events-none">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/70 text-white backdrop-blur-md">
                                <span x-text="activeModelName || 'AI Studio Engine'"></span>
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/70 text-white backdrop-blur-md"
                                x-text="activeAspectRatio || '1:1'">
                            </span>
                        </div>

                        <!-- Favorite Toggle Button Top Right -->
                        <div class="absolute top-3 right-3">
                            <button type="button" @click="toggleFavorite()"
                                class="p-2.5 rounded-full backdrop-blur-md transition-all shadow-md"
                                :class="isFavorite ? 'bg-amber-500 text-white' : 'bg-black/50 text-white/80 hover:bg-black/70'">
                                <i data-lucide="star" class="w-4 h-4" :class="{ 'fill-current': isFavorite }"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 1-Click Action Cockpit (AC-010: Simpan ke Katalog Produk) -->
                    <div class="pt-2 flex flex-wrap items-center justify-between gap-3 border-t border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <!-- AC-010 Button -->
                            <button type="button" @click="openExportModal()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[12px] bg-[#34C759] hover:bg-[#2db24f] text-white text-xs font-bold shadow-sm shadow-[#34C759]/25 transition-all active:scale-[0.98]">
                                <i data-lucide="package-plus" class="w-4 h-4"></i>
                                <span>Simpan ke Katalog Produk</span>
                            </button>

                            <!-- Direct Download -->
                            <a :href="activeImageUrl" download="ai-studio-render.png" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Unduh</span>
                            </a>
                        </div>

                        <div class="text-[11px] text-black/40 dark:text-white/40">
                            Render ID: <span class="font-mono" x-text="activeGenerationId.substring(0, 8)"></span>
                        </div>
                    </div>
                </div>

                <!-- STATE C: Generation Failed -->
                <div x-show="!isRendering && currentStatus === 'failed'" class="p-6 rounded-[16px] bg-red-500/5 border border-red-500/20 text-center space-y-3" style="display: none;">
                    <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center mx-auto">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-red-600 dark:text-red-400">Proses Render Gagal Dijalankan</h4>
                        <p class="text-xs text-black/60 dark:text-white/60" x-text="errorMessage || 'Provider AI tidak dapat memproses permintaan.'"></p>
                    </div>
                    <div class="p-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-xs text-black/70 dark:text-white/70 max-w-sm mx-auto">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#34C759] inline mr-1"></i>
                        <span>Sistem Atomic Ledger telah mengembalikan saldo kredit Anda sebesar 100%.</span>
                    </div>
                    <button type="button" @click="generationDrawerOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-white text-xs font-bold shadow-sm">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Coba Render Ulang</span>
                    </button>
                </div>

                <!-- STATE D: Draft Empty Canvas (No generations yet) -->
                <div x-show="!isRendering && !activeImageUrl && currentStatus !== 'failed'" class="py-12 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="image" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-sm font-bold text-black dark:text-white">Kanvas Proyek Siap Dirender</h4>
                        <p class="text-xs text-black/60 dark:text-white/60">
                            Proyek telah diinisiasi. Tekan tombol di bawah untuk mulai memproses visual menggunakan mesin AI pilihan Anda.
                        </p>
                    </div>
                    <button type="button" @click="generationDrawerOpen = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all">
                        <i data-lucide="wand-2" class="w-4 h-4"></i>
                        <span>Mulai Eksekusi Render</span>
                    </button>
                </div>

            </div>

            <!-- Past Generations History Strip (if multiple) -->
            @if ($project->generations->count() > 1)
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="history" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Riwayat Render Proyek Ini ({{ $project->generations->count() }})</span>
                    </h4>
                    <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                        @foreach ($project->generations as $gen)
                            @php $asset = $gen->assets->first(); @endphp
                            @if ($asset && ($asset->output_image_url || $asset->asset_url))
                                <button type="button" @click="selectPastGeneration('{{ $gen->id }}', '{{ $asset->output_image_url ?? $asset->asset_url }}', '{{ $asset->id }}', '{{ $gen->ai_model_name }}', '{{ $asset->aspect_ratio }}', {{ $asset->is_selected_favorite ? 'true' : 'false' }})"
                                    class="shrink-0 w-20 h-20 rounded-[12px] overflow-hidden border transition-all"
                                    :class="activeGenerationId === '{{ $gen->id }}' ? 'border-[#007AFF] ring-2 ring-[#007AFF]/30' : 'border-black/10 dark:border-white/10 opacity-70 hover:opacity-100'">
                                    <img src="{{ $asset->thumbnail_url ?: ($asset->output_image_url ?? $asset->asset_url) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- RIGHT COLUMN: Project Metadata & Asset Context (5 Cols) -->
        <div class="lg:col-span-5 space-y-5">

            <!-- Bento Card: Associated Catalog Product -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span>Produk Katalog Terkait</span>
                    </h4>
                    @if ($attachedProduct)
                        <span class="text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">Aktif</span>
                    @endif
                </div>

                @if ($attachedProduct)
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-3">
                        @if ($attachedProduct->image_path)
                            <img src="{{ asset('storage/' . $attachedProduct->image_path) }}" alt="{{ $attachedProduct->name }}"
                                class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/10">
                        @else
                            <div class="w-12 h-12 rounded-[10px] bg-black/[0.05] flex items-center justify-center text-black/40 shrink-0">
                                <i data-lucide="package" class="w-6 h-6"></i>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate">{{ $attachedProduct->name }}</h5>
                            <p class="text-[11px] text-black/50 dark:text-white/50">SKU: {{ $attachedProduct->code ?: '-' }}</p>
                            <p class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp {{ number_format($attachedProduct->selling_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] text-xs text-black/50 dark:text-white/50">
                        Tidak ada produk spesifik yang dikaitkan (generasi materi umum/branding).
                    </div>
                @endif
            </div>

            <!-- Bento Card: Reference Style Inspiration -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="compass" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Referensi Visual Komersial</span>
                    </h4>
                    @if ($referenceItem)
                        <span class="text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">Legal Sah</span>
                    @endif
                </div>

                @if ($referenceItem)
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-3">
                        <img src="{{ $referenceItem->thumbnail_url ?: $referenceItem->canonical_url }}" alt="{{ $referenceItem->title }}"
                            class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/10">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate">{{ $referenceItem->title }}</h5>
                            <p class="text-[11px] text-black/50 dark:text-white/50 truncate">Sumber: {{ $referenceItem->source?->name ?? 'Pexels' }}</p>
                            <a href="{{ route('ai-studio.inspiration.show', $referenceItem->id) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                                Lihat Lembar Atribusi &rarr;
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] text-xs text-black/50 dark:text-white/50">
                        Tidak ada gambar referensi (menggunakan style preset murni).
                    </div>
                @endif
            </div>

            <!-- Bento Card: Active Prompt & Version Archive -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="terminal" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                        <span>Prompt Aktif (Versi {{ $latestPrompt?->version_number ?? 1 }})</span>
                    </h4>
                    <button type="button" @click="copyPrompt()"
                        class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>

                <div class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-xs font-mono text-black/80 dark:text-white/80 leading-relaxed max-h-48 overflow-y-auto">
                    {{ $latestPrompt?->positive_prompt ?: 'Studio visual commercial photography shot.' }}
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================== -->
    <!-- 3. MODAL: EXPORT TO PRODUCT CATALOG (AC-010) -->
    <!-- ========================================== -->
    <div x-show="exportModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="exportModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="package-plus" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">Simpan ke Katalog Produk</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Jadikan hasil render foto resmi produk Anda</p>
                    </div>
                </div>
                <button type="button" @click="exportModalOpen = false" class="p-1 rounded-full text-black/40 hover:bg-black/5">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Tab Switcher (Update existing vs Create new) -->
            <div class="flex p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-xs font-semibold">
                <button type="button" @click="exportMode = 'update'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all"
                    :class="exportMode === 'update' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60'">
                    Perbarui Produk Terkait
                </button>
                <button type="button" @click="exportMode = 'new'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all"
                    :class="exportMode === 'new' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60'">
                    Buat Produk Baru
                </button>
            </div>

            <!-- Form: Update existing product -->
            <div x-show="exportMode === 'update'" class="space-y-3">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-black dark:text-white">Pilih Produk Sasaran</label>
                    <select x-model="exportTargetProductId"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#34C759]">
                        @if ($attachedProduct)
                            <option value="{{ $attachedProduct->id }}">Produk Proyek Ini: {{ $attachedProduct->name }}</option>
                        @endif
                        <option value="">-- Pilih Produk Lain --</option>
                    </select>
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    Foto produk di database katalog akan digantikan dengan gambar render AI Studio ini.
                </p>
            </div>

            <!-- Form: Create brand new product -->
            <div x-show="exportMode === 'new'" class="space-y-3" style="display: none;">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Nama Produk Baru <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newProductName" placeholder="Contoh: Kopi Robusta Premium 250g"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#34C759]">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="newProductPrice" placeholder="50000" min="0"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#34C759]">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/5">
                <button type="button" @click="exportModalOpen = false"
                    class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5">
                    Batal
                </button>
                <button type="button" @click="submitExportToCatalog()" :disabled="exporting"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] bg-[#34C759] hover:bg-[#2db24f] disabled:opacity-50 text-white text-xs font-bold shadow-sm transition-all">
                    <i data-lucide="check" class="w-3.5 h-3.5" x-show="!exporting"></i>
                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="exporting" style="display: none;"></i>
                    <span x-text="exporting ? 'Menyimpan...' : 'Simpan Sekarang'">Simpan Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 4. DRAWER / MODAL: DISPATCH NEW GENERATION -->
    <!-- ========================================== -->
    <div x-show="generationDrawerOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="generationDrawerOpen = false"
            class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-bold text-black dark:text-white">Pesan Render Generasi Baru</h3>
                </div>
                <button type="button" @click="generationDrawerOpen = false" class="p-1 rounded-full text-black/40 hover:bg-black/5">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Model Selection -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-black dark:text-white">Pilihan Engine Model AI</label>
                <select x-model="dispatchModel" @change="onDrawerModelChange()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                    <option value="ideogram-v2">Ideogram v2 (15 Kredit - Unggul Tipografi &amp; Label)</option>
                    <option value="dall-e-3">OpenAI DALL-E 3 (20 Kredit - Fotografi Realistik Tinggi)</option>
                    <option value="sdxl">Stable Diffusion XL (10 Kredit - Cepat &amp; Artistik)</option>
                </select>
            </div>

            <!-- Custom Prompt Override if desired -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-black dark:text-white">Teks Prompt Kustom (Opsional)</label>
                <textarea x-model="dispatchCustomPrompt" rows="3"
                    placeholder="Biarkan kosong untuk menggunakan prompt aktif terakhir..."
                    class="w-full p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white"></textarea>
            </div>

            <!-- Hold Notice & Cost -->
            <div class="p-3.5 rounded-[12px] bg-[#007AFF]/5 border border-[#007AFF]/20 flex items-center justify-between text-xs">
                <span class="text-black/70 dark:text-white/70">Biaya Reservasi:</span>
                <span class="font-bold text-[#007AFF] text-sm" x-text="dispatchCost + ' Kredit'">15 Kredit</span>
            </div>

            <!-- Insufficient Balance Warning inside Drawer -->
            <div x-show="creditsBalance < dispatchCost" class="p-3 rounded-[12px] bg-amber-500/10 border border-amber-500/20 flex items-center justify-between text-xs text-amber-700 dark:text-amber-300">
                <span class="text-[11px]">Saldo kredit Anda (<strong x-text="creditsBalance"></strong>) tidak mencukupi.</span>
                <button type="button" @click="$dispatch('open-ai-topup-modal', { required: dispatchCost, available: creditsBalance, message: 'Saldo kredit Anda tidak mencukupi untuk render variasi ini.' })"
                    class="px-2.5 py-1 rounded-[8px] bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-[10px] shadow-xs hover:from-amber-600 hover:to-orange-600">
                    Top Up Sekarang
                </button>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5">
                <button type="button" @click="generationDrawerOpen = false"
                    class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">
                    Batal
                </button>
                <button type="button" @click="dispatchNewGeneration()" :disabled="dispatching"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] disabled:opacity-50 text-white text-xs font-bold shadow-sm transition-all">
                    <i data-lucide="wand-2" class="w-3.5 h-3.5" x-show="!dispatching"></i>
                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="dispatching" style="display: none;"></i>
                    <span x-text="dispatching ? 'Memproses...' : 'Mulai Render Visual'">Mulai Render Visual</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Top Up Modal Component -->
    @include('app.ai-studio.partials.topup-modal')

</div>

@push('scripts')
<script>
function aiStudioCanvas(config) {
    return {
        projectId: config.projectId,
        activeGenerationId: config.activeGenerationId,
        isRendering: config.isRendering,
        creditsBalance: config.creditsBalance,
        currentStatus: '{{ $latestGeneration?->status ?? $project->status }}',
        activeImageUrl: '{{ $primaryAsset?->output_image_url ?? $primaryAsset?->asset_url ?? "" }}',
        activeAssetId: config.activeAssetId,
        activeModelName: '{{ $latestGeneration?->ai_model_name ?? "" }}',
        activeAspectRatio: '{{ $latestPrompt?->aspect_ratio ?? "1:1" }}',
        errorMessage: '{{ $latestGeneration?->error_message ?? "" }}',
        isFavorite: config.isFavorite,
        copied: false,
        flashMessage: '',

        // Export Modal State (AC-010)
        exportModalOpen: false,
        exportMode: config.hasAttachedProduct ? 'update' : 'new',
        exportTargetProductId: config.attachedProductId,
        newProductName: '{{ addslashes($project->name) }}',
        newProductPrice: '',
        exporting: false,

        // Dispatch Drawer State
        generationDrawerOpen: false,
        dispatchModel: 'ideogram-v2',
        dispatchCost: 15,
        dispatchCustomPrompt: '',
        dispatching: false,

        get statusLabel() {
            if (this.isRendering) return 'Merender Visual...';
            if (this.currentStatus === 'completed' || this.currentStatus === 'succeeded') return 'Selesai';
            if (this.currentStatus === 'failed') return 'Gagal';
            return 'Draf';
        },

        onDrawerModelChange() {
            if (this.dispatchModel === 'dall-e-3') this.dispatchCost = 20;
            else if (this.dispatchModel === 'sdxl') this.dispatchCost = 10;
            else this.dispatchCost = 15;
        },

        async toggleFavorite() {
            if (!this.activeAssetId) return;

            try {
                const res = await fetch('{{ url("ai-studio/assets") }}/' + this.activeAssetId + '/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.isFavorite = data.is_favorite;
                }
            } catch (e) {
                console.error(e);
            }
        },

        copyPrompt() {
            const promptText = `{{ addslashes($latestPrompt?->positive_prompt ?? '') }}`;
            navigator.clipboard.writeText(promptText).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        openExportModal() {
            this.exportModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async submitExportToCatalog() {
            if (!this.activeAssetId) {
                alert('Tidak ada aset render aktif yang dapat disimpan.');
                return;
            }

            this.exporting = true;

            try {
                const payload = {};
                if (this.exportMode === 'update') {
                    payload.product_id = this.exportTargetProductId;
                } else {
                    payload.name = this.newProductName;
                    payload.selling_price = parseFloat(this.newProductPrice) || 0;
                }

                const res = await fetch('{{ url("ai-studio/assets") }}/' + this.activeAssetId + '/export-to-catalog', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyimpan ke katalog.');
                }

                this.exportModalOpen = false;
                this.flashMessage = 'Aset visual berhasil disimpan ke katalog produk COOCA!';
            } catch (e) {
                alert('Gagal: ' + e.message);
            } finally {
                this.exporting = false;
            }
        },

        selectPastGeneration(genId, url, assetId, model, aspect, favorite) {
            this.activeGenerationId = genId;
            this.activeImageUrl = url;
            this.activeAssetId = assetId;
            this.activeModelName = model;
            this.activeAspectRatio = aspect;
            this.isFavorite = favorite;
            this.currentStatus = 'completed';
            this.isRendering = false;
            this.$nextTick(() => lucide.createIcons());
        },

        async dispatchNewGeneration() {
            this.dispatching = true;

            try {
                const provider = this.dispatchModel === 'dall-e-3' ? 'openai' : (this.dispatchModel === 'sdxl' ? 'stability' : 'ideogram');

                const payload = {
                    project_id: this.projectId,
                    provider: provider,
                    model_name: this.dispatchModel,
                    custom_prompt: this.dispatchCustomPrompt || null,
                    aspect_ratio: this.activeAspectRatio,
                    batch_count: 1,
                };

                const res = await fetch('{{ route("ai-studio.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    if (res.status === 402 || data.error_code === 'INSUFFICIENT_CREDITS') {
                        this.generationDrawerOpen = false;
                        window.dispatchEvent(new CustomEvent('open-ai-topup-modal', {
                            detail: {
                                required: data.required || this.dispatchCost,
                                available: data.available ?? this.creditsBalance,
                                message: data.message || 'Kredit AI Studio Anda tidak mencukupi untuk memproses render ini.'
                            }
                        }));
                        return;
                    }
                    throw new Error(data.message || 'Gagal memproses pemesanan generasi.');
                }

                this.generationDrawerOpen = false;
                this.activeGenerationId = data.generation?.id;
                this.isRendering = true;
                this.currentStatus = 'generating';
                this.creditsBalance -= this.dispatchCost;

                this.startPolling();
            } catch (e) {
                alert('Gagal memulai generasi: ' + e.message);
            } finally {
                this.dispatching = false;
            }
        },

        startPolling() {
            if (!this.activeGenerationId) return;

            const pollInterval = setInterval(async () => {
                try {
                    const statusUrl = '{{ url("ai-studio/projects") }}/' + this.projectId + '/generations/' + this.activeGenerationId + '/status';
                    const res = await fetch(statusUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();

                    if (data.status === 'succeeded') {
                        clearInterval(pollInterval);
                        this.isRendering = false;
                        this.currentStatus = 'succeeded';
                        const firstAsset = data.assets?.[0];
                        if (firstAsset) {
                            this.activeImageUrl = firstAsset.url;
                            this.activeAssetId = firstAsset.id;
                        }
                        this.flashMessage = 'Visual render AI telah selesai diproses!';
                        this.$nextTick(() => lucide.createIcons());
                    } else if (data.status === 'failed') {
                        clearInterval(pollInterval);
                        this.isRendering = false;
                        this.currentStatus = 'failed';
                        this.errorMessage = data.error_message || 'Proses render gagal.';
                        this.$nextTick(() => lucide.createIcons());
                    }
                } catch (e) {
                    console.error('Polling error', e);
                }
            }, 3000);
        },

        init() {
            lucide.createIcons();
            if (this.isRendering) {
                this.startPolling();
            }
            window.addEventListener('ai-credits-updated', (e) => {
                if (e.detail && typeof e.detail.credits !== 'undefined') {
                    this.creditsBalance = e.detail.credits;
                }
            });
        }
    };
}
</script>
@endpush
@endsection
