@extends('layouts.app', [
    'title' => $item->title . ' - Detail Inspirasi Visual AI Studio',
    'headerTitle' => 'Lembar Spesifikasi & Atribusi Inspirasi',
    'headerSubtitle' => 'Detail parameter fotografi produk, analisis visi komputer, dan validasi lisensi komersial',
])

@section('content')
<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{ copyFeedback: false }">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('ai-studio.index') }}" class="hover:text-[#007AFF] transition-colors">AI Studio</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('ai-studio.inspiration.index') }}" class="hover:text-[#007AFF] transition-colors">Katalog Inspirasi</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-black/80 dark:text-white/80 font-semibold truncate max-w-[200px]">{{ $item->title }}</span>
        </div>

        <a href="{{ route('ai-studio.inspiration.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>{{ __('ai.kembali_ke_galeri') }}</span>
        </a>
    </div>

    <!-- Main Bento Grid (2 Columns: Visual Showcase & Specifications) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Column Left: Visual Showcase (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="rounded-[20px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 shadow-lg relative group">
                <img src="{{ $item->canonical_url ?: $item->thumbnail_url }}" alt="{{ $item->title }}"
                    class="w-full h-auto object-cover max-h-[580px]">

                <!-- Verified License Floating Pill -->
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-[#34C759]/90 text-white backdrop-blur-md shadow-md">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Lisensi Komersial Sah</span>
                    </span>
                </div>
            </div>

            <!-- Primary Action Button -->
            <a href="{{ route('ai-studio.create', ['inspiration_id' => $item->id]) }}"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-sm font-bold shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                <i data-lucide="wand-2" class="w-4 h-4"></i>
                <span>{{ __('ai.gunakan_gaya_visual_ini_untuk_produk') }}</span>
            </a>
        </div>

        <!-- Column Right: Information, Specs & Attribution (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">

            <!-- Title & Metadata Bento Card -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF]">
                        {{ $item->category?->name ?? 'Gaya Visual' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 capitalize">
                        Target Industri: {{ $item->industry_target ?? 'Multi-sektor' }}
                    </span>
                    @if ($item->trendScore)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                            <i data-lucide="trending-up" class="w-3 h-3"></i>
                            <span>Viral Index: {{ round($item->trendScore->trend_score) }}/100</span>
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white">
                    {{ $item->title }}
                </h1>

                @if ($item->caption)
                    <p class="text-xs sm:text-sm text-black/65 dark:text-white/65 leading-relaxed">
                        {{ $item->caption }}
                    </p>
                @endif
            </div>

            <!-- Template Prompt Replikasi Bento Card -->
            @php
                $promptText = $item->visualAnalysis?->extracted_style_prompt ?: $item->caption ?: '';
            @endphp
            @if ($promptText)
                <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE]"></i>
                            <span>Template Prompt Replikasi Poster Viral</span>
                        </h3>
                        <button type="button"
                            @click="navigator.clipboard.writeText(@js($promptText)); copyFeedback = true; setTimeout(() => copyFeedback = false, 2000)"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all">
                            <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copyFeedback"></i>
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copyFeedback"></i>
                            <span x-text="copyFeedback ? 'Tersalin!' : 'Salin Prompt'"></span>
                        </button>
                    </div>

                    <div class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 font-mono text-xs leading-relaxed text-black/80 dark:text-white/90 select-all whitespace-pre-wrap">{{ $promptText }}</div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-black/50 dark:text-white/50">
                        <span class="flex items-center gap-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Ganti teks placeholder seperti <strong class="text-black dark:text-white font-mono">[Nama Produk]</strong> dengan produk Anda.</span>
                        </span>
                        <a href="{{ route('ai-studio.create', ['inspiration_id' => $item->id]) }}"
                            class="font-bold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                            <span>{{ __('ai.buka_di_kanvas_ai_studio') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Visual Specs & Vision Breakdown Bento Card -->
            @php $analysis = $item->visualAnalysis; @endphp
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                    <i data-lucide="scan-eye" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Analisis Parameter Fotografi AI</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Lighting -->
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <span class="text-black/50 dark:text-white/50 font-medium">Gaya Pencahayaan</span>
                        <p class="font-bold text-black dark:text-white text-sm">
                            {{ $analysis?->lighting_style ?: 'Natural Studio Daylight' }}
                        </p>
                    </div>

                    <!-- Composition -->
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <span class="text-black/50 dark:text-white/50 font-medium">Tata Letak Komposisi</span>
                        <p class="font-bold text-black dark:text-white text-sm">
                            {{ $analysis?->composition_layout ?: 'Clean Centered Product Shot' }}
                        </p>
                    </div>

                    <!-- Camera & Lens -->
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <span class="text-black/50 dark:text-white/50 font-medium">Lensa &amp; Sudut Pandang</span>
                        <p class="font-bold text-black dark:text-white text-sm">
                            {{ $analysis?->camera_angle ?: 'Eye-Level Medium Macro (85mm f/2.8)' }}
                        </p>
                    </div>

                    <!-- Mood & Texture -->
                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <span class="text-black/50 dark:text-white/50 font-medium">Suasana &amp; Tekstur Latar</span>
                        <p class="font-bold text-black dark:text-white text-sm">
                            {{ $analysis?->color_temperature ?: 'Warm & Minimalist Neutral' }}
                        </p>
                    </div>
                </div>

                <!-- Palette Colors if available -->
                @if ($analysis && !empty($analysis->detected_palette))
                    <div class="space-y-2 pt-2">
                        <span class="text-xs text-black/50 dark:text-white/50 font-medium">Palet Warna Terdeteksi:</span>
                        <div class="flex items-center gap-2">
                            @foreach ((array) $analysis->detected_palette as $color)
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-[11px] font-mono font-medium text-black/70 dark:text-white/70">
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/10 shrink-0" style="background-color: {{ $color }};"></span>
                                    <span>{{ $color }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Legal & Copyright Attribution Bento Card (AC-001) -->
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 flex items-center gap-2">
                        <i data-lucide="shield" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Lembar Atribusi &amp; Hak Cipta Sah</span>
                    </h3>
                    <span class="text-[11px] font-bold text-[#34C759]">Bebas Royalti</span>
                </div>

                <div class="p-4 rounded-[12px] bg-[#34C759]/5 border border-[#34C759]/20 space-y-2 text-xs">
                    <div class="flex items-start gap-2 text-black/80 dark:text-white/90">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <span>Gambar ini bersumber dari katalog publik resmi <strong>{{ $item->source?->name ?? 'Pexels / CC0 Provider' }}</strong> dengan izin penggunaan komersial tanpa batasan.</span>
                    </div>
                    <div class="flex items-start gap-2 text-black/80 dark:text-white/90">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <span>AI Studio COOCA hanya mengabstraksikan elemen tata letak &amp; pencahayaan untuk menghasilkan visual produk asli milik Anda.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                    <div>
                        <span class="text-black/50 dark:text-white/50">Pembuat / Fotografer:</span>
                        <p class="font-bold text-black dark:text-white">{{ $item->author_name ?: 'Kontributor Legal Komersial' }}</p>
                    </div>
                    <div>
                        <span class="text-black/50 dark:text-white/50">Tautan Sumber Sah:</span>
                        <p class="truncate">
                            <a href="{{ $item->canonical_url ?: '#' }}" target="_blank" rel="noopener noreferrer"
                                class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                <span>{{ __('ai.kunjungi_halaman_asli') }}</span>
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();
});
</script>
@endpush
@endsection
