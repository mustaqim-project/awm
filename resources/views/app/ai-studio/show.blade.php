@extends('layouts.app', [
    'title' => $project->name . ' - Canvas Studio AI',
    'headerTitle' => 'Canvas Studio & Output Visual AI',
    'headerSubtitle' => 'Pantau proses render, sesuaikan variasi prompt, dan simpan hasil visual ke katalog produk atau media sosial',
])

@section('content')
@php
    $latestGeneration = $project->generations->first();
    $primaryAsset = $latestGeneration?->assets?->first();
    $attachedProduct = $project->product;
    $customProductAsset = $project->assets->firstWhere('asset_type', 'original_product');
    $referenceItem = $project->inspirationItem;
    $customReferenceAsset = $project->assets->firstWhere('asset_type', 'reference_style');
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
    isFavorite: {{ $primaryAsset?->is_selected_favorite ? 'true' : 'false' }},
    defaultCaption: '{{ addslashes($project->name) }} - Visual resmi dari studio produk kami. Dapatkan penawaran terbaik hari ini! #{{ Str::slug($project->name, "") }} #cooca #bisnisindonesia'
})">

    <!-- ========================================== -->
    <!-- 1. BENTO APPLE HIG MODULE HEADER          -->
    <!-- ========================================== -->
    <x-module-header
        :title="$project->name"
        subtitle="Pantau proses render, sesuaikan variasi prompt, dan simpan hasil visual ke katalog produk atau media sosial"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => $project->name, 'url' => null]
        ]">

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <!-- Platform & Aspect Pill -->
            <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold uppercase tracking-wider bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                {{ $project->target_platform ?? 'Instagram' }} ({{ $latestPrompt?->aspect_ratio ?? '1:1' }})
            </span>

            <!-- Status Pill -->
            <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold"
                :class="{
                    'bg-[#34C759]/15 text-[#34C759]': currentStatus === 'completed' || currentStatus === 'succeeded',
                    'bg-[#007AFF]/15 text-[#007AFF] animate-pulse': currentStatus === 'generating' || isRendering,
                    'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60': currentStatus === 'draft',
                    'bg-red-500/15 text-red-500': currentStatus === 'failed'
                }"
                x-text="statusLabel">
            </span>

            <!-- Saldo Pill -->
            <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-xs font-semibold text-black/80 dark:text-white/90 flex items-center gap-1.5">
                <i data-lucide="coins" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="creditsBalance">{{ number_format($creditsBalance) }}</span>
                <span class="text-[10px] text-black/50 dark:text-white/50">Kredit</span>
            </div>

            <!-- Top Up Button (Secondary Tonal Pill) -->
            @if(\App\Support\Context::hasPermission('ai_studio.manage') || \App\Support\Context::isAdminOrOwner())
                <button type="button" @click="$dispatch('open-ai-topup-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 text-xs font-semibold shadow-2xs transition-all active:scale-[0.98]">
                    <i data-lucide="zap" class="w-3.5 h-3.5 fill-amber-500/20"></i>
                    <span>Top Up</span>
                </button>
            @endif

            <!-- New Generation Button (Primary CTA) -->
            @if(\App\Support\Context::hasPermission('ai_studio.generate') || \App\Support\Context::isOwner())
                <button type="button" @click="generationDrawerOpen = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-xs transition-all active:scale-[0.98]">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Render Variasi Baru</span>
                </button>
            @endif

            <!-- Back Button -->
            <a href="{{ route('ai-studio.index') }}"
                class="px-3 py-1.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-xs font-medium text-black/80 dark:text-white/80 transition-all">
                Kembali
            </a>
        </div>
    </x-module-header>

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
            <div class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-sm">

                <!-- STATE A: Active Rendering In Progress (Shimmer Skeleton & Alpine Poller) -->
                <div x-show="isRendering" class="space-y-4 py-12 text-center" style="display: none;">
                    <div class="relative w-48 h-48 mx-auto rounded-[20px] bg-gradient-to-tr from-[#007AFF]/15 via-[#5856D6]/15 to-[#FF2D55]/15 border border-[#007AFF]/20 flex flex-col items-center justify-center overflow-hidden animate-pulse">
                        <div class="w-12 h-12 rounded-full bg-white/80 dark:bg-[#1C1C1E]/80 shadow-md flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i>
                        </div>
                    </div>
                    <div class="space-y-1.5 max-w-sm mx-auto">
                        <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.sedang_merender_visual_ai') }}</h3>
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

                    <!-- 1-Click Action Cockpit (Katalog Produk & Media Sosial) -->
                    <div class="pt-2 flex flex-wrap items-center justify-between gap-3 border-t border-black/5 dark:border-white/10">
                        <div class="flex flex-wrap items-center gap-2">
                            @if(\App\Support\Context::hasPermission('ai_studio.manage') || \App\Support\Context::isOwner())
                                <!-- Primary Action: Simpan ke Katalog Produk -->
                                <button type="button" @click="openExportModal()"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all active:scale-[0.98]">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                    <span>Simpan ke Katalog</span>
                                </button>

                                <!-- Secondary Action: Posting ke Media Sosial -->
                                <button type="button" @click="openSocialModal()"
                                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] border border-black/5 dark:border-white/10 text-black/80 dark:text-white text-xs font-semibold transition-all active:scale-[0.98]">
                                    <i data-lucide="share-2" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Posting ke Media Sosial</span>
                                </button>
                            @endif

                            <!-- Direct Download -->
                            <a :href="activeImageUrl" download="ai-studio-render.png" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>{{ __('admin.unduh') }}</span>
                            </a>

                            @if(\App\Support\Context::hasPermission('ai_studio.manage') || \App\Support\Context::isOwner())
                                <!-- Delete Asset Button (Quiet Destructive) -->
                                <button type="button" @click="deleteActiveAsset()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-[12px] bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 text-xs font-semibold transition-all cursor-pointer ml-auto sm:ml-0"
                                    title="Hapus aset gambar ini jika tidak sesuai">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus</span>
                                </button>
                            @endif
                        </div>

                        <div class="text-[11px] text-black/40 dark:text-white/40">
                            Render ID: <span class="font-mono" x-text="activeGenerationId ? activeGenerationId.substring(0, 8) : '-'"></span>
                        </div>
                    </div>
                </div>

                <!-- STATE C: Generation Failed -->
                <div x-show="!isRendering && currentStatus === 'failed'" class="p-6 rounded-[16px] bg-red-500/5 border border-red-500/20 text-center space-y-3" style="display: none;">
                    <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center mx-auto">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-red-600 dark:text-red-400">{{ __('ai.proses_render_gagal_dijalankan') }}</h4>
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
                        <span>{{ __('ai.render_history_this_project') }} ({{ $project->generations->count() }})</span>
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

            <!-- Bento Card: Associated Catalog Product / Custom Upload -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span>{{ __('ai.objek_produk_layanan_terkait') }}</span>
                    </h4>
                    @if ($attachedProduct)
                        <span class="text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">Katalog Aktif</span>
                    @elseif ($customProductAsset)
                        <span class="text-[10px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">Upload Mandiri</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">SKU: {{ $attachedProduct->code ?: '-' }} &bull; {{ $attachedProduct->type === 'service' ? 'Layanan Jasa' : 'Barang' }}</p>
                            <p class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp {{ number_format($attachedProduct->selling_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @elseif ($customProductAsset)
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $customProductAsset->file_path) }}" alt="{{ __('ai.foto_produk') }}"
                            class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/10">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate">{{ $customProductAsset->metadata['product_name'] ?? $project->name }}</h5>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Foto Objek Langsung dari Merchant</p>
                            <span class="inline-block mt-0.5 text-[10px] text-[#007AFF] font-medium bg-[#007AFF]/10 px-1.5 py-0.5 rounded">Fokus Utama Komposisi</span>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] text-xs text-black/50 dark:text-white/50">
                        Tidak ada produk spesifik yang dikaitkan (generasi materi umum/branding).
                    </div>
                @endif
            </div>

            <!-- Bento Card: Reference Style Inspiration / Custom Reference -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="compass" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Referensi Visual Komersial</span>
                    </h4>
                    @if ($referenceItem)
                        <span class="text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">Legal Sah</span>
                    @elseif ($customReferenceAsset)
                        <span class="text-[10px] font-bold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded-full">Referensi Mandiri</span>
                    @endif
                </div>

                @if ($referenceItem)
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-3">
                        <img src="{{ $referenceItem->display_image_url }}" alt="{{ $referenceItem->title }}"
                            class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/10">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate">{{ $referenceItem->title }}</h5>
                            <p class="text-[11px] text-black/50 dark:text-white/50 truncate">Konsep: {{ $referenceItem->category?->name ?? 'Visual Estetik' }}</p>
                            <a href="{{ route('ai-studio.inspiration.show', $referenceItem->id) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                                Lihat Detail Visual &rarr;
                            </a>
                        </div>
                    </div>
                @elseif ($customReferenceAsset)
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $customReferenceAsset->file_path) }}" alt="Foto Referensi"
                            class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/10">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate">Gaya Visual Pilihan Sendiri</h5>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Digunakan sebagai acuan lighting &amp; komposisi</p>
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
                        <span>{{ __('ai.active_prompt') }} ({{ __('common.version') }} {{ $latestPrompt?->version_number ?? 1 }})</span>
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
    <template x-teleport="body">
        <div x-show="exportModalOpen" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            role="dialog" aria-modal="true"
            @keydown.escape.window="exportModalOpen = false"
            style="display: none;">
            <!-- Standalone Backdrop -->
            <div x-show="exportModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="exportModalOpen = false"></div>

            <!-- Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="exportModalOpen = false">
                <div @click.away="exportModalOpen = false"
                    class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="package-plus" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.simpan_ke_katalog_produk') }}</h3>
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
                            :class="exportMode === 'update' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60'">{{ __('ai.perbarui_produk_terkait') }}</button>
                        <button type="button" @click="exportMode = 'new'"
                            class="flex-1 py-1.5 rounded-[8px] transition-all"
                            :class="exportMode === 'new' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60'">{{ __('ai.buat_produk_baru') }}</button>
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
                            class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5">{{ __('common.cancel') }}</button>
                        <button type="button" @click="submitExportToCatalog()" :disabled="exporting"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] bg-[#34C759] hover:bg-[#2db24f] disabled:opacity-50 text-white text-xs font-bold shadow-sm transition-all">
                            <i data-lucide="check" class="w-3.5 h-3.5" x-show="!exporting"></i>
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="exporting" style="display: none;"></i>
                            <span x-text="exporting ? 'Menyimpan...' : 'Simpan Sekarang'">{{ __('ai.simpan_sekarang') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ========================================== -->
    <!-- 4. MODAL: POSTING KE MEDIA SOSIAL          -->
    <!-- ========================================== -->
    <template x-teleport="body">
        <div x-show="socialModalOpen" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            role="dialog" aria-modal="true"
            @keydown.escape.window="socialModalOpen = false"
            style="display: none;">
            <!-- Standalone Backdrop -->
            <div x-show="socialModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="socialModalOpen = false"></div>

            <!-- Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="socialModalOpen = false">
                <div @click.away="socialModalOpen = false"
                    class="w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-black dark:text-white">Posting ke Media Sosial</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Publikasikan visual render AI langsung atau jadwalkan via kalender otomatis</p>
                            </div>
                        </div>
                        <button type="button" @click="socialModalOpen = false" class="p-1 rounded-full text-black/40 hover:bg-black/5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Platform Selection -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-black dark:text-white">Saluran Media Sosial</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="socialPlatform = 'instagram'"
                                class="p-2 rounded-[10px] border text-center transition-all flex flex-col items-center gap-1"
                                :class="socialPlatform === 'instagram' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <i data-lucide="instagram" class="w-4 h-4"></i>
                                <span class="text-[10px] font-bold">Instagram</span>
                            </button>
                            <button type="button" @click="socialPlatform = 'facebook'"
                                class="p-2 rounded-[10px] border text-center transition-all flex flex-col items-center gap-1"
                                :class="socialPlatform === 'facebook' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <i data-lucide="facebook" class="w-4 h-4"></i>
                                <span class="text-[10px] font-bold">Facebook</span>
                            </button>
                            <button type="button" @click="socialPlatform = 'tiktok'"
                                class="p-2 rounded-[10px] border text-center transition-all flex flex-col items-center gap-1"
                                :class="socialPlatform === 'tiktok' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <i data-lucide="video" class="w-4 h-4"></i>
                                <span class="text-[10px] font-bold">TikTok</span>
                            </button>
                            <button type="button" @click="socialPlatform = 'threads'"
                                class="p-2 rounded-[10px] border text-center transition-all flex flex-col items-center gap-1"
                                :class="socialPlatform === 'threads' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF]' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <i data-lucide="at-sign" class="w-4 h-4"></i>
                                <span class="text-[10px] font-bold">Threads</span>
                            </button>
                        </div>
                    </div>

                    <!-- Connected Account Selector -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-black dark:text-white">Pilih Akun Terhubung</label>
                        <select x-model="socialAccountId"
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Mode Umum / Semua Akun Tertaut --</option>
                            @if(isset($socialAccounts) && $socialAccounts->isNotEmpty())
                                @foreach($socialAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ ucfirst($account->platform) }}: {{ $account->account_name ?: ($account->username ?: 'Akun ' . $account->id) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if(!isset($socialAccounts) || $socialAccounts->isEmpty())
                            <p class="text-[11px] text-amber-600 dark:text-amber-400">
                                Belum ada akun media sosial yang terhubung. Postingan akan disimpan sebagai draf atau antrean posting.
                            </p>
                        @endif
                    </div>

                    <!-- Caption Content -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-black dark:text-white">Teks Caption Postingan</label>
                            <span class="text-[10px] text-black/40 dark:text-white/40" x-text="(socialContent?.length || 0) + '/5000'"></span>
                        </div>
                        <textarea x-model="socialContent" rows="4"
                            class="w-full p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white leading-relaxed"></textarea>
                    </div>

                    <!-- Publish Mode Switcher -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-black dark:text-white">Waktu &amp; Metode Publikasi</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="socialPublishMode = 'now'"
                                class="p-2 rounded-[10px] border text-center transition-all"
                                :class="socialPublishMode === 'now' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-bold' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 text-xs'">
                                Sekarang
                            </button>
                            <button type="button" @click="socialPublishMode = 'scheduled'"
                                class="p-2 rounded-[10px] border text-center transition-all"
                                :class="socialPublishMode === 'scheduled' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-bold' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 text-xs'">
                                Jadwalkan
                            </button>
                            <button type="button" @click="socialPublishMode = 'draft'"
                                class="p-2 rounded-[10px] border text-center transition-all"
                                :class="socialPublishMode === 'draft' ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-bold' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 text-xs'">
                                Simpan Draf
                            </button>
                        </div>

                        <!-- Date Time Picker if Scheduled -->
                        <div x-show="socialPublishMode === 'scheduled'" class="pt-2 space-y-1" style="display: none;">
                            <label class="text-[11px] font-medium text-black/60 dark:text-white/60">Pilih Tanggal &amp; Waktu Tayang</label>
                            <input type="datetime-local" x-model="socialScheduledAt"
                                class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                            <p class="text-[10px] text-black/50 dark:text-white/50">
                                Sistem scheduler cron otomatis (per menit) akan mengeksekusi publikasi postingan tepat pada waktu yang ditentukan.
                            </p>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <button type="button" @click="socialModalOpen = false"
                            class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5">{{ __('common.cancel') }}</button>
                        <button type="button" @click="submitExportToSocialMedia()" :disabled="socialPosting"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] disabled:opacity-50 text-white text-xs font-bold shadow-sm transition-all">
                            <i data-lucide="share-2" class="w-3.5 h-3.5" x-show="!socialPosting"></i>
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="socialPosting" style="display: none;"></i>
                            <span x-text="socialPosting ? 'Mengirim...' : (socialPublishMode === 'now' ? 'Publikasikan Sekarang' : (socialPublishMode === 'scheduled' ? 'Jadwalkan Posting' : 'Simpan Draf'))"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ========================================== -->
    <!-- 5. DRAWER / MODAL: DISPATCH NEW GENERATION -->
    <!-- ========================================== -->
    <template x-teleport="body">
        <div x-show="generationDrawerOpen" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            role="dialog" aria-modal="true"
            @keydown.escape.window="generationDrawerOpen = false"
            style="display: none;">
            <!-- Standalone Backdrop -->
            <div x-show="generationDrawerOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="generationDrawerOpen = false"></div>

            <!-- Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="generationDrawerOpen = false">
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
                            <option value="gpt-image-2.5-flare">Fast Generation (gpt-image-2.5-flare - 15 Kredit / Generate Harian)</option>
                            <option value="gpt-image-2.5-sunburst">Premium Generation (gpt-image-2.5-sunburst - 25 Kredit / Presisi &amp; Edit)</option>
                            <option value="ideogram-v2">Ideogram v2 (15 Kredit - Unggul Tipografi &amp; Label)</option>
                            <option value="dall-e-3">OpenAI DALL-E 3 (20 Kredit - Fotografi Realistik Tinggi)</option>
                            <option value="sdxl">Stable Diffusion XL (10 Kredit - Cepat &amp; Artistik)</option>
                        </select>
                    </div>

                    <!-- Custom Prompt Override if desired -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-black dark:text-white">Teks Prompt Kustom (Opsional)</label>
                        <textarea x-model="dispatchCustomPrompt" rows="3"
                            placeholder="{{ __('ai.biarkan_kosong_untuk_menggunakan_prompt_aktif') }}"
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
                            class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                        <button type="button" @click="dispatchNewGeneration()" :disabled="dispatching"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] disabled:opacity-50 text-white text-xs font-bold shadow-sm transition-all">
                            <i data-lucide="wand-2" class="w-3.5 h-3.5" x-show="!dispatching"></i>
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="dispatching" style="display: none;"></i>
                            <span x-text="dispatching ? 'Memproses...' : 'Mulai Render Visual'">Mulai Render Visual</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

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

        // Export to Catalog Modal State (AC-010)
        exportModalOpen: false,
        exportMode: config.hasAttachedProduct ? 'update' : 'new',
        exportTargetProductId: config.attachedProductId,
        newProductName: @json($project->name),
        newProductPrice: '',
        exporting: false,

        // Social Media Modal State
        socialModalOpen: false,
        socialPlatform: '{{ in_array($project->target_platform ?? "", ["instagram", "facebook", "tiktok", "threads"]) ? $project->target_platform : "instagram" }}',
        socialAccountId: '',
        socialContent: config.defaultCaption || '',
        socialPublishMode: 'now',
        socialScheduledAt: '',
        socialPosting: false,

        // Dispatch Drawer State
        generationDrawerOpen: false,
        dispatchModel: 'gpt-image-2.5-flare',
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
            if (this.dispatchModel === 'gpt-image-2.5-flare') this.dispatchCost = 15;
            else if (this.dispatchModel === 'gpt-image-2.5-sunburst') this.dispatchCost = 25;
            else if (this.dispatchModel === 'dall-e-3') this.dispatchCost = 20;
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
            const promptText = @json($latestPrompt?->positive_prompt ?? '');
            navigator.clipboard.writeText(promptText).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        openExportModal() {
            this.exportModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openSocialModal() {
            this.socialModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
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

        async submitExportToSocialMedia() {
            if (!this.activeAssetId) {
                alert('Tidak ada aset render aktif yang dapat diposting.');
                return;
            }

            if (!this.socialContent || !this.socialContent.trim()) {
                alert('Silakan tuliskan teks caption terlebih dahulu.');
                return;
            }

            this.socialPosting = true;

            try {
                const payload = {
                    platform: this.socialPlatform,
                    social_media_account_id: this.socialAccountId || null,
                    content: this.socialContent,
                    publish_mode: this.socialPublishMode,
                    scheduled_at: this.socialPublishMode === 'scheduled' ? this.socialScheduledAt : null
                };

                const res = await fetch('{{ url("ai-studio/assets") }}/' + this.activeAssetId + '/export-to-social-media', {
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
                    throw new Error(data.message || 'Gagal memproses postingan ke media sosial.');
                }

                this.socialModalOpen = false;
                this.flashMessage = data.message || 'Aset visual berhasil dikirim ke antrean Media Sosial!';
            } catch (e) {
                alert('Gagal: ' + e.message);
            } finally {
                this.socialPosting = false;
            }
        },

        async deleteActiveAsset() {
            if (!this.activeAssetId) {
                alert('Tidak ada aset gambar yang aktif dipilih.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin menghapus aset gambar ini dari katalog bisnis Anda?')) {
                return;
            }

            try {
                const res = await fetch('{{ url("ai-studio/assets") }}/' + this.activeAssetId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menghapus aset gambar.');
                }

                alert(data.message || 'Aset gambar berhasil dihapus.');
                window.location.reload();
            } catch (e) {
                alert('Gagal: ' + e.message);
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
        },

        async dispatchNewGeneration() {
            this.dispatching = true;
            try {
                const payload = {
                    project_id: '{{ $project->id }}',
                    model_name: this.dispatchModel,
                    custom_prompt: this.dispatchCustomPrompt || undefined
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

                if (res.status === 402) {
                    this.generationDrawerOpen = false;
                    this.$dispatch('open-ai-topup-modal', {
                        required: this.dispatchCost,
                        available: this.creditsBalance,
                        message: data.message || 'Saldo kredit AI Anda tidak mencukupi untuk memproses render variasi ini.'
                    });
                    return;
                }

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal memicu render AI baru.');
                }

                this.generationDrawerOpen = false;
                this.activeGenerationId = data.generation.id;
                this.isRendering = true;
                this.currentStatus = 'generating';
                this.creditsBalance = data.remaining_credits;
                this.flashMessage = 'Render variasi baru sedang diproses di latar belakang.';

                this.pollGenerationStatus(data.generation.id);
            } catch (e) {
                alert('Gagal: ' + e.message);
            } finally {
                this.dispatching = false;
            }
        },

        pollGenerationStatus(genId) {
            let attempts = 0;
            const maxAttempts = 60; // 3 minutes timeout limit
            const interval = setInterval(async () => {
                attempts++;
                if (attempts > maxAttempts) {
                    clearInterval(interval);
                    this.isRendering = false;
                    this.errorMessage = 'Waktu render melebihi batas waktu (timeout). Silakan periksa kembali nanti.';
                    return;
                }
                try {
                    const res = await fetch('{{ url("ai-studio/projects") }}/{{ $project->id }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (!data.success) return;

                    const gen = (data.project.generations || []).find(g => g.id === genId);
                    if (gen) {
                        this.currentStatus = gen.status;
                        if (gen.status === 'completed' || gen.status === 'succeeded') {
                            clearInterval(interval);
                            this.isRendering = false;
                            const asset = (gen.assets || [])[0];
                            if (asset) {
                                this.activeImageUrl = asset.output_image_url || asset.asset_url;
                                this.activeAssetId = asset.id;
                                this.activeModelName = gen.ai_model_name;
                                this.activeAspectRatio = asset.aspect_ratio;
                                this.isFavorite = !!asset.is_selected_favorite;
                            }
                            this.flashMessage = 'Visual render AI selesai dengan sempurna!';
                            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                        } else if (gen.status === 'failed') {
                            clearInterval(interval);
                            this.isRendering = false;
                            this.errorMessage = gen.error_message || 'Terjadi kesalahan teknis pada provider AI.';
                            this.creditsBalance = data.credits_balance;
                        }
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }, 3000);
        }
    };
}
window.aiStudioCanvas = aiStudioCanvas;
</script>
@endpush
@endsection
