@extends('layouts.app', [
    'title' => 'Katalog Inspirasi Visual & Referensi Viral - AI Studio',
    'headerTitle' => 'Katalog Inspirasi Visual & Referensi Legal',
    'headerSubtitle' => 'Eksplorasi ribuan tata letak fotografi komersial terverifikasi untuk inspirasi konten produk UMKM',
])

@section('content')
<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="inspirationGallery()">

    <!-- ========================================== -->
    <!-- 1. BENTO HEADER & NAVIGATION COCKPIT      -->
    <!-- ========================================== -->
    <div class="relative z-10 rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('ai-studio.index') }}" class="hover:text-[#007AFF] transition-colors">AI Studio</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-black/80 dark:text-white/80 font-semibold">Katalog Inspirasi</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-gradient-to-tr from-[#FF2D55] to-[#FF9500] flex items-center justify-center text-white shadow-sm shadow-[#FF2D55]/25">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                </span>
                <span>Katalog Inspirasi Viral &amp; Referensi Visual</span>
            </h1>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                Temukan gaya visual terbaik untuk produk Anda. Setiap gambar telah divalidasi kepatuhan hak cipta komersial dan dianalisis parameter pencahayaan serta komposisinya.
            </p>
        </div>

        <!-- Navigation Actions -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('ai-studio.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 transition-all text-xs font-semibold text-black/80 dark:text-white">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Studio</span>
            </a>
            <a href="{{ route('ai-studio.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-semibold shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                <span>Buat Proyek Tanpa Referensi</span>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. CATEGORY PILLS BAR                      -->
    <!-- ========================================== -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('ai-studio.inspiration.index', array_merge(request()->except('category', 'page'))) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ !request('category') ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25' : 'bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5' }}">
            Semua Kategori
        </a>
        @foreach ($categories as $cat)
            @php
                $isActiveCat = request('category') == $cat->slug || request('category') == $cat->id;
            @endphp
            <a href="{{ route('ai-studio.inspiration.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $isActiveCat ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25' : 'bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- 3. SEARCH & ADVANCED FILTER BAR            -->
    <!-- ========================================== -->
    <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4">
        <form method="GET" action="{{ route('ai-studio.inspiration.index') }}" class="flex flex-col md:flex-row gap-3">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif

            <!-- Search Input -->
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari gaya visual, mood, pencahayaan, atau kata kunci..."
                    class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <!-- Target Industry Selector -->
            <div class="w-full md:w-48">
                <select name="industry" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Sektor Industri</option>
                    <option value="fnb" {{ request('industry') === 'fnb' ? 'selected' : '' }}>Kuliner &amp; F&amp;B</option>
                    <option value="fashion" {{ request('industry') === 'fashion' ? 'selected' : '' }}>Fashion &amp; Tekstil</option>
                    <option value="beauty" {{ request('industry') === 'beauty' ? 'selected' : '' }}>Skincare &amp; Kosmetik</option>
                    <option value="electronics" {{ request('industry') === 'electronics' ? 'selected' : '' }}>Elektronik &amp; Gadget</option>
                    <option value="craft" {{ request('industry') === 'craft' ? 'selected' : '' }}>Kerajinan &amp; Craft</option>
                    <option value="health" {{ request('industry') === 'health' ? 'selected' : '' }}>Kesehatan &amp; Herbal</option>
                    <option value="general" {{ request('industry') === 'general' ? 'selected' : '' }}>Umum &amp; Retail</option>
                </select>
            </div>

            <!-- Sorting Selector -->
            <div class="w-full md:w-48">
                <select name="sort" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="trending" {{ request('sort', 'trending') === 'trending' ? 'selected' : '' }}>Paling Viral &amp; Trending</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="px-4 py-2 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition-all">
                    Terapkan
                </button>
                @if(request()->hasAny(['search', 'industry', 'sort', 'category']))
                    <a href="{{ route('ai-studio.inspiration.index') }}"
                        class="px-3 py-2 rounded-[10px] text-xs font-medium text-black/50 dark:text-white/50 hover:text-red-500 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- 4. MASONRY / INSPIRATION GRID             -->
    <!-- ========================================== -->
    @if ($items->isEmpty())
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-12 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                <i data-lucide="image-off" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1.5 max-w-md mx-auto">
                <h3 class="text-base font-bold text-black dark:text-white">Tidak Ada Inspirasi Ditemukan</h3>
                <p class="text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Coba ubah kata kunci pencarian atau pilih kategori lain untuk melihat koleksi inspirasi komersial.
                </p>
            </div>
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-xs font-semibold text-white shadow-sm">
                Lihat Semua Inspirasi
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($items as $item)
                @php
                    $analysis = $item->visualAnalysis;
                    $score = $item->trendScore?->trend_score ?? 85;
                    $source = $item->source;
                    $imgUrl = $item->thumbnail_url ?: $item->canonical_url;
                @endphp
                <div class="group rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-[0_12px_32px_rgba(0,0,0,0.08)] hover:border-black/15 dark:hover:border-white/20">
                    <!-- Image Box with Badges & Hover Overlay -->
                    <div class="relative aspect-[4/5] w-full bg-black/[0.04] dark:bg-white/[0.04] overflow-hidden">
                        <img src="{{ $imgUrl }}" alt="{{ $item->title }}" loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

                        <!-- Trend Score & Source Badge -->
                        <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 pointer-events-none">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/65 text-white backdrop-blur-md">
                                <i data-lucide="trending-up" class="w-3 h-3 text-[#34C759]"></i>
                                <span>{{ round($score) }}%</span>
                            </span>
                            @if ($source)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/80 dark:bg-black/70 text-black dark:text-white backdrop-blur-md">
                                    {{ $source->name }}
                                </span>
                            @endif
                        </div>

                        <!-- License Verified Pill -->
                        <div class="absolute top-2.5 right-2.5 pointer-events-none">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/90 text-white backdrop-blur-md shadow-sm">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Komersial Bebas</span>
                            </span>
                        </div>

                        <!-- Hover Overlay Actions -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 p-4 flex flex-col justify-end gap-2">
                            <!-- Visual specs tags -->
                            @if ($analysis)
                                <div class="flex flex-wrap gap-1 text-[10px] text-white/90">
                                    @if ($analysis->lighting_style)
                                        <span class="px-1.5 py-0.5 rounded bg-white/20 backdrop-blur-sm">{{ $analysis->lighting_style }}</span>
                                    @endif
                                    @if ($analysis->composition_layout)
                                        <span class="px-1.5 py-0.5 rounded bg-white/20 backdrop-blur-sm">{{ $analysis->composition_layout }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center gap-2 pt-1">
                                <a href="{{ route('ai-studio.create', ['inspiration_id' => $item->id]) }}"
                                    class="flex-1 text-center py-2 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md transition-all active:scale-95">
                                    Gunakan Inspirasi
                                </a>
                                <a href="{{ route('ai-studio.inspiration.show', $item->id) }}"
                                    class="p-2 rounded-[10px] bg-white/20 hover:bg-white/30 text-white text-xs font-semibold backdrop-blur-sm transition-all"
                                    title="Lihat Detail & Atribusi Hak Cipta">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Item Details -->
                    <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 mb-1">
                                <span>{{ $item->category?->name ?? 'Gaya Visual' }}</span>
                                <span class="capitalize">{{ $item->industry_target ?? 'Multi-sektor' }}</span>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white line-clamp-1 group-hover:text-[#007AFF] transition-colors" title="{{ $item->title }}">
                                {{ $item->title }}
                            </h4>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px]">
                            <span class="text-black/40 dark:text-white/40 truncate max-w-[130px]">
                                Oleh {{ $item->author_name ?: 'Kontributor Legal' }}
                            </span>
                            <a href="{{ route('ai-studio.create', ['inspiration_id' => $item->id]) }}"
                                class="inline-flex items-center gap-1 font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                                <span>Pakai</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $items->links() }}
        </div>
    @endif

</div>

@push('scripts')
<script>
function inspirationGallery() {
    return {
        init() {
            lucide.createIcons();
        }
    };
}
</script>
@endpush
@endsection
