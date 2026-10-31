@extends('layouts.app', [
    'title' => 'AI Studio - Studio Kreatif & Generator Visual',
    'headerTitle' => 'AI Studio Visual & Inspirasi Viral',
    'headerSubtitle' => 'Buat visual produk premium, fotografi katalog, dan materi promosi omnichannel bertenaga Generative AI',
])

@section('content')
<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="aiStudioDashboard()">

    <!-- ========================================== -->
    <!-- 1. BENTO HEADER & ACTION COCKPIT           -->
    <!-- ========================================== -->
    <div class="relative z-10 rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-black/80 dark:text-white/80 font-semibold">AI Studio Visual</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white shadow-sm shadow-[#007AFF]/25">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
                <span>{{ __('ai.studio_kreatif_visual_produk') }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                Rancang materi pemasaran omnichannel berbasis referensi visual legal, analisis visi otomatis, dan perakitan prompt dinamis.
            </p>
        </div>

        <!-- Cockpit Quick Actions & Credits Pill -->
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto justify-start lg:justify-end">
            <!-- Credit Balance Badge -->
            <a href="{{ route('ai-studio.credit-history') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 transition-all text-xs font-semibold text-black/80 dark:text-white/90">
                <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="coins" class="w-3 h-3"></i>
                </span>
                <span>{{ __('ai.saldo_kredit') }}</span>
                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] text-sm" x-text="creditsBalance">{{ number_format($stats['credits_balance']) }}</span>
            </a>

            <!-- Top Up Kredit Trigger -->
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-sm shadow-orange-500/20 active:scale-[0.98] transition-all">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                <span>Top Up Kredit</span>
            </button>

            <!-- Browse Inspiration Button -->
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] hover:bg-black/[0.03] dark:hover:bg-white/[0.06] border border-black/10 dark:border-white/15 text-xs font-semibold text-black/80 dark:text-white transition-all active:scale-[0.98] shadow-sm">
                <i data-lucide="compass" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]"></i>
                <span>Jelajahi Inspirasi</span>
            </a>

            <!-- Create Project Primary CTA -->
            <a href="{{ route('ai-studio.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-semibold shadow-sm shadow-[#007AFF]/30 transition-all active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Proyek Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Banner when Monthly Credits are Expended (0 Balance) -->
    @if ($stats['credits_balance'] <= 0)
        <div class="p-4 rounded-[16px] bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10 border border-orange-500/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">{{ __('ai.kredit_bulanan_ai_studio_anda_telah') }}</h4>
                    <p class="text-[11px] sm:text-xs text-black/60 dark:text-white/60">
                        Top up kredit sekarang via QRIS / Virtual Account untuk melanjutkan render visual katalog produk tanpa hambatan.
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

    <!-- ========================================== -->
    <!-- 2. BENTO STATS & METRICS GRID             -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Proyek -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Proyek</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_projects'] }}</span>
                <span class="text-xs text-black/50 dark:text-white/50">proyek dibuat</span>
            </div>
        </div>

        <!-- Metric 2: Total Generasi Render -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Generasi</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 text-[#5856D6] dark:bg-[#5856D6]/20 dark:text-[#5E5CE6] flex items-center justify-center">
                    <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['total_generations'] }}</span>
                <span class="text-xs text-black/50 dark:text-white/50">kali eksekusi</span>
            </div>
        </div>

        <!-- Metric 3: Sukses & Tersedia -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Generasi Berhasil</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] dark:bg-[#34C759]/20 dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ $stats['completed_generations'] }}</span>
                <span class="text-xs text-[#34C759] font-medium">siap digunakan</span>
            </div>
        </div>

        <!-- Metric 4: Saldo Kredit -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Kredit AI Tersedia</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9500]/20 dark:text-[#FF9F0A] flex items-center justify-center">
                    <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ number_format($stats['credits_balance']) }}</span>
                <a href="{{ route('ai-studio.credit-history') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">{{ __('ai.riwayat_rarr') }}</a>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. INSPIRATION BANNER BENTO CARD          -->
    <!-- ========================================== -->
    <div class="rounded-[16px] p-5 sm:p-6 bg-gradient-to-r from-[#007AFF]/10 via-[#5856D6]/10 to-transparent border border-[#007AFF]/20 dark:border-[#0A84FF]/25 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF]">
                <i data-lucide="sparkles" class="w-3 h-3"></i>
                <span>Katalog Inspirasi Bebas Risiko Hak Cipta</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                Bingung mulai dari mana? Temukan gaya visual viral terverifikasi komersial
            </h3>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60">
                Pilih ratusan tata letak pencahayaan studio, fotografi produk flatlay, dan poster media sosial berlisensi legal CC0 &amp; Pexels.
            </p>
        </div>
        <a href="{{ route('ai-studio.inspiration.index') }}"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-[12px] bg-black/80 dark:bg-white text-white dark:text-black hover:bg-black dark:hover:bg-white/90 text-xs font-semibold transition-all shadow-sm">
            <span>{{ __('ai.buka_katalog_inspirasi') }}</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- 4. FILTER & SEARCH BAR                    -->
    <!-- ========================================== -->
    <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 space-y-3">
        <form method="GET" action="{{ route('ai-studio.index') }}" class="flex flex-col md:flex-row gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('ai.cari_nama_proyek_kreatif') }}"
                    class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <!-- Target Channel Selector -->
            <div class="w-full md:w-48">
                <select name="channel" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Channel</option>
                    <option value="instagram" {{ request('channel') === 'instagram' ? 'selected' : '' }}>Instagram (Feed &amp; Story)</option>
                    <option value="tiktok" {{ request('channel') === 'tiktok' ? 'selected' : '' }}>TikTok (Vertical 9:16)</option>
                    <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee (Square 1:1)</option>
                    <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia (Square 1:1)</option>
                    <option value="banner" {{ request('channel') === 'banner' ? 'selected' : '' }}>Banner Website (16:9)</option>
                </select>
            </div>

            <!-- Status Selector -->
            <div class="w-full md:w-44">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="generating" {{ request('status') === 'generating' ? 'selected' : '' }}>Sedang Dibuat</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <!-- Filter & Reset Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="px-3.5 py-2 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition-all">
                    Terapkan
                </button>
                @if(request()->hasAny(['search', 'channel', 'status']))
                    <a href="{{ route('ai-studio.index') }}"
                        class="px-3 py-2 rounded-[10px] text-xs font-medium text-black/50 dark:text-white/50 hover:text-red-500 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- 5. PROJECTS BENTO GRID                     -->
    <!-- ========================================== -->
    @if ($projects->isEmpty())
        <!-- Empty State Bento Card -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-12 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                <i data-lucide="wand-2" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1.5 max-w-md mx-auto">
                <h3 class="text-base font-bold text-black dark:text-white">{{ __('ai.belum_ada_proyek_visual') }}</h3>
                <p class="text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Mulai proyek visual pertama Anda dengan memilih produk dari katalog COOCA dan menerapkan gaya visual terpopuler.
                </p>
            </div>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('ai-studio.inspiration.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-xs font-semibold text-black dark:text-white hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all">
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.cari_referensi_visual') }}</span>
                </a>
                <a href="{{ route('ai-studio.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-xs font-semibold text-white shadow-sm transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Proyek Sekarang</span>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($projects as $project)
                @php
                    $latestGen = $project->latestGeneration;
                    $primaryAsset = $latestGen?->assets?->first();
                    $attachedProduct = $project->assets->firstWhere('asset_type', 'product_source')?->product;
                    $referenceItem = $project->assets->firstWhere('asset_type', 'reference_style')?->inspirationItem;
                @endphp
                <div class="group rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)] hover:border-black/10 dark:hover:border-white/20">
                    <!-- Project Preview Image Area -->
                    <div class="relative aspect-square w-full bg-black/[0.03] dark:bg-white/[0.03] overflow-hidden">
                        @if ($primaryAsset && $primaryAsset->output_image_url)
                            <img src="{{ $primaryAsset->output_image_url }}" alt="{{ $project->name }}"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @elseif ($referenceItem && ($referenceItem->thumbnail_url || $referenceItem->canonical_url))
                            <img src="{{ $referenceItem->thumbnail_url ?? $referenceItem->canonical_url }}" alt="{{ $project->name }}"
                                class="w-full h-full object-cover opacity-80 filter blur-[0.5px] transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/60 text-white backdrop-blur-sm">
                                    Gambar Referensi
                                </span>
                            </div>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-black/30 dark:text-white/30 gap-2">
                                <i data-lucide="image" class="w-8 h-8"></i>
                                <span class="text-[11px] font-medium">{{ __('ai.belum_ada_render') }}</span>
                            </div>
                        @endif

                        <!-- Channel & Aspect Badge -->
                        <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-md">
                                {{ $project->target_platform ?? $project->target_channel ?? 'Visual' }}
                            </span>
                        </div>

                        <!-- Status Pill -->
                        <div class="absolute top-2.5 right-2.5">
                            @if ($project->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/90 text-white backdrop-blur-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    <span>Selesai</span>
                                </span>
                            @elseif ($project->status === 'generating')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/90 text-white backdrop-blur-md animate-pulse">
                                    <i data-lucide="loader-2" class="w-2.5 h-2.5 animate-spin"></i>
                                    <span>Merender</span>
                                </span>
                            @elseif ($project->status === 'failed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/90 text-white backdrop-blur-md">
                                    <span>Gagal</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/50 text-white/90 backdrop-blur-md">
                                    <span>Draf</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-black dark:text-white truncate group-hover:text-[#007AFF] transition-colors" title="{{ $project->name }}">
                                {{ $project->name }}
                            </h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50 line-clamp-2">
                                {{ $project->description ?: 'Tanpa catatan kustom.' }}
                            </p>
                        </div>

                        <!-- Product Link Indicator if attached -->
                        @if ($attachedProduct)
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-black/60 dark:text-white/60 bg-black/[0.03] dark:bg-white/[0.04] px-2 py-1 rounded-[6px] truncate">
                                <i data-lucide="package" class="w-3 h-3 text-[#007AFF] shrink-0"></i>
                                <span class="truncate">{{ $attachedProduct->name }}</span>
                            </div>
                        @endif

                        <!-- Card Footer & Actions -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="text-[10px] text-black/40 dark:text-white/40">
                                {{ $project->updated_at?->diffForHumans() ?? $project->created_at->diffForHumans() }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                @if(\App\Support\Context::hasPermission('ai_studio.manage'))
                                <button type="button" @click="confirmDelete('{{ $project->id }}', '{{ addslashes($project->name) }}')"
                                    class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-red-500 hover:bg-red-500/10 transition-colors"
                                    title="{{ __('ai.hapus_proyek') }}">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                                @endif
                                <a href="{{ route('ai-studio.show', $project->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[8px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF] hover:text-white text-xs font-semibold transition-all">
                                    <span>{{ __('ai.buka_canvas') }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $projects->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal (Apple Sheet Style) -->
    <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display: none;">
        <div @click.away="deleteModalOpen = false" class="w-full max-w-sm rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 shadow-2xl space-y-4">
            <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.hapus_proyek_ai_studio') }}</h3>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Proyek <span class="font-semibold text-black dark:text-white" x-text="deleteProjectName"></span> beserta riwayat prompt akan dihapus. Aset render yang telah disimpan ke katalog produk tidak akan terpengaruh.
                </p>
            </div>
            <form :action="deleteActionUrl" method="POST" class="flex items-center justify-end gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                    class="px-3 py-2 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="px-4 py-2 rounded-[10px] bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-sm transition-all">{{ __('admin.ya_hapus') }}</button>
            </form>
        </div>
    </div>

    <!-- Top Up Modal Component -->
    @include('app.ai-studio.partials.topup-modal')

</div>

@push('scripts')
<script>
function aiStudioDashboard() {
    return {
        creditsBalance: {{ (int) ($stats['credits_balance'] ?? 0) }},
        deleteModalOpen: false,
        deleteProjectId: null,
        deleteProjectName: '',
        deleteActionUrl: '',

        confirmDelete(id, name) {
            this.deleteProjectId = id;
            this.deleteProjectName = name;
            this.deleteActionUrl = '{{ url("ai-studio/projects") }}/' + id;
            this.deleteModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        init() {
            lucide.createIcons();
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
