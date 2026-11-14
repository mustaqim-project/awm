@extends('layouts.app', [
    'title' => 'AI Inspiration Library - Inspirasi Visual & Referensi Komersial',
    'headerTitle' => 'AI Inspiration Library',
    'headerSubtitle' => 'Eksplorasi ribuan tata letak fotografi, komposisi visual, dan acuan lighting komersial lintas 4 pilar sumber',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="inspirationExplorer()">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        title="AI Inspiration Library"
        subtitle="Pusat inspirasi gaya visual komersial resmi COOCA  Kurasi Internal, Pexels, Pixabay, Unsplash & Koleksi Pribadi"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => null]
        ]">

        <div class="flex items-center gap-2">
            <!-- Back to Projects -->
            <a href="{{ route('ai-studio.index') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Studio Proyek</span>
            </a>

            <!-- Create Direct CTA -->
            <a href="{{ route('ai-studio.create') }}"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-xs transition-all">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Generator Visual</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. TAB NAVIGATOR INSPIRATION --}}
    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3 overflow-x-auto scrollbar-none text-xs font-semibold">
        <a href="{{ route('ai-studio.inspiration.index') }}"
            class="px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] text-white shadow-xs">
            Semua Inspirasi
        </a>
        <a href="{{ route('ai-studio.inspiration.categories') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Kategori & Sektor
        </a>
        <a href="{{ route('ai-studio.inspiration.collections') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors flex items-center gap-1.5">
            <span>Koleksi Terpilih</span>
            @if(($myCollectionsCount ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/[0.06] dark:bg-white/[0.08]">{{ $myCollectionsCount }}</span>
            @endif
        </a>
        <a href="{{ route('ai-studio.inspiration.favorites') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors flex items-center gap-1.5">
            <span>Favorit Saya</span>
            @if(($favoritesCount ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-red-500/10 text-red-500 font-bold">{{ $favoritesCount }}</span>
            @endif
        </a>
        <a href="{{ route('ai-studio.inspiration.my-library') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Perpustakaan Pribadi
        </a>
    </div>

    {{-- 3. PILLAR & PROVIDER FILTER PILLS --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs">
        @php
            $currentSource = request('source', $sourceFilter ?? 'all');
            $providersList = [
                'all' => ['label' => 'Semua Sumber (Hybrid)', 'icon' => 'globe'],
                'curated' => ['label' => 'Kurasi COOCA (460+)', 'icon' => 'sparkles'],
                'pexels' => ['label' => 'Pexels Commercial', 'icon' => 'camera'],
                'pixabay' => ['label' => 'Pixabay Library', 'icon' => 'image'],
                'unsplash' => ['label' => 'Unsplash Curated', 'icon' => 'aperture'],
                'pinterest' => ['label' => 'Pinterest Inspiration', 'icon' => 'pin'],
                'personal' => ['label' => 'Aset Pribadi Bisnis', 'icon' => 'lock'],
            ];
        @endphp
        @foreach($providersList as $srcKey => $srcData)
            @php $isActiveSrc = ($currentSource === $srcKey); @endphp
            <a href="{{ route('ai-studio.inspiration.index', array_merge(request()->except('page'), ['source' => $srcKey])) }}"
                class="px-3 py-1.5 rounded-full font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $isActiveSrc ? 'bg-black text-white dark:bg-white dark:text-black shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5' }}">
                <i data-lucide="{{ $srcData['icon'] }}" class="w-3.5 h-3.5"></i>
                <span>{{ $srcData['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- 4. SEARCH & ADVANCED FILTER BAR --}}
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 space-y-3">
        @if(!empty($recommendedIndustry) && $recommendedIndustry !== 'general' && request('industry') !== $recommendedIndustry)
            <!-- Smart Tenant Industry Recommendation Banner -->
            <div class="p-2.5 sm:p-3 rounded-[12px] bg-[#007AFF]/[0.08] dark:bg-[#007AFF]/[0.12] border border-[#007AFF]/20 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF] animate-pulse shrink-0"></span>
                    <span class="text-black/80 dark:text-white/90 truncate">
                        Rekomendasi untuk usaha Anda: <strong class="text-[#007AFF] font-bold">{{ $recommendedLabel }}</strong>
                    </span>
                </div>
                <a href="{{ route('ai-studio.inspiration.index', array_merge(request()->except('page'), ['industry' => $recommendedIndustry])) }}"
                    class="px-3 py-1 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold shrink-0 transition-all inline-flex items-center gap-1">
                    <span>Lihat Sektor Saya</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        @endif

        <form method="GET" action="{{ route('ai-studio.inspiration.index') }}" class="flex flex-col md:flex-row gap-3">
            @if(request('source'))
                <input type="hidden" name="source" value="{{ request('source') }}">
            @endif

            <!-- Search Input -->
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search', $query ?? '') }}"
                    placeholder="Cari gaya visual, mood pencahayaan, atau kata kunci produk..."
                    class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <!-- Category Selector -->
            <div class="w-full md:w-48">
                <select name="category" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ (request('category') === $cat->slug) ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Target Industry Selector (Multi-Industry 20+ Sectors) -->
            <div class="w-full md:w-52">
                <select name="industry" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Sektor Industri</option>
                    @foreach($industrySectors ?? [] as $secCode => $secData)
                        <option value="{{ $secCode }}" {{ request('industry') === $secCode ? 'selected' : '' }}>
                            {{ $secData['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Aspect Ratio Selector -->
            <div class="w-full md:w-36">
                <select name="aspect_ratio" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="all">Semua Rasio</option>
                    <option value="1:1" {{ request('aspect_ratio') === '1:1' ? 'selected' : '' }}>1:1 Square</option>
                    <option value="9:16" {{ request('aspect_ratio') === '9:16' ? 'selected' : '' }}>9:16 Story</option>
                    <option value="16:9" {{ request('aspect_ratio') === '16:9' ? 'selected' : '' }}>16:9 Banner</option>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="px-4 py-2 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition-all">
                    Terapkan
                </button>
                @if(request()->hasAny(['search', 'industry', 'aspect_ratio', 'category', 'source']))
                    <a href="{{ route('ai-studio.inspiration.index') }}"
                        class="px-3 py-2 rounded-[10px] text-xs font-medium text-black/50 dark:text-white/50 hover:text-red-500 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 5. MASONRY / INSPIRATION GRID --}}
    @php
        $rawItems = $searchResults['items'] ?? [];
        $hasItems = !empty($rawItems) || (isset($items) && $items->isNotEmpty());
    @endphp

    @if(!$hasItems)
        <div class="w-full rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-12 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                <i data-lucide="image-off" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1.5 max-w-md mx-auto">
                <h3 class="text-base font-bold text-black dark:text-white">Tidak Ada Gambar Inspirasi Ditemukan</h3>
                <p class="text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Coba ganti kata kunci pencarian, pilih sektor industri lain, atau alihkan ke pilar sumber lain (Kurasi COOCA, Pexels, Pixabay).
                </p>
            </div>
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-xs font-semibold text-white shadow-sm">
                Lihat Semua Koleksi
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($rawItems as $item)
                @php
                    $itemId = $item['id'] ?? null;
                    $externalId = $item['external_id'] ?? null;
                    $title = $item['title'] ?? 'Inspirasi Visual';
                    $rawUrl = $item['display_url'] ?? $item['image_url'] ?? $item['preview_url'] ?? $item['thumbnail_url'] ?? '';
                    $imgUrl = !empty($rawUrl)
                        ? (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://') ? $rawUrl : asset($rawUrl))
                        : asset('images/ai-studio/placeholder.svg');
                    $provider = strtolower($item['source_provider'] ?? $item['source_code'] ?? 'cooca');
                    $categoryName = $item['category_name'] ?? 'Katalog Visual';
                    $aspectRatio = $item['aspect_ratio'] ?? '1:1';
                    $stylePrompt = $item['style_prompt'] ?? $item['prompt_hint'] ?? $item['description'] ?? '';

                    $isFav = in_array($itemId, $favoritedAssetIds ?? [], true) || in_array($externalId, $favoritedExternalIds ?? [], true);
                @endphp
                <div class="group rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-[0_12px_32px_rgba(0,0,0,0.08)] hover:border-black/15 dark:hover:border-white/20">
                    <!-- Image Box with Badges & Hover Overlay -->
                    <div class="relative aspect-[4/5] w-full bg-black/[0.04] dark:bg-white/[0.04] overflow-hidden">
                        <img src="{{ $imgUrl }}" alt="{{ $title }}" loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.onerror=null; this.src='{{ asset('images/ai-studio/placeholder.svg') }}';">

                        <!-- Source Badge & Category Pill -->
                        <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 pointer-events-none">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-black/70 text-white backdrop-blur-md">
                                {{ $provider }}
                            </span>
                            @if($categoryName)
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-white/85 dark:bg-black/75 text-black dark:text-white backdrop-blur-md truncate max-w-[100px]">
                                    {{ $categoryName }}
                                </span>
                            @endif
                        </div>

                        <!-- Top Right: Favorite Button & Save to Collection (Touch Ergonomics >= 36px) -->
                        <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                            <button type="button"
                                @click="toggleFavoriteItem(@js($item))"
                                :title="isFavorited(@js($item)) ? 'Hapus dari Favorit' : 'Simpan ke Favorit'"
                                class="w-9 h-9 sm:w-8 sm:h-8 rounded-full bg-white/95 dark:bg-black/90 flex items-center justify-center shadow-xs transition-transform active:scale-90 hover:scale-110 cursor-pointer">
                                <i data-lucide="heart" class="w-4 h-4 transition-colors"
                                    :class="isFavorited(@js($item)) ? 'text-red-500 fill-red-500' : 'text-black/60 dark:text-white/60'"></i>
                            </button>

                            <button type="button"
                                @click="openAddToCollectionModal(@js($item))"
                                title="Tambahkan ke Papan Koleksi"
                                class="w-9 h-9 sm:w-8 sm:h-8 rounded-full bg-white/95 dark:bg-black/90 text-black/75 dark:text-white/75 flex items-center justify-center shadow-xs transition-transform active:scale-90 hover:scale-110 cursor-pointer">
                                <i data-lucide="bookmark-plus" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Hover Overlay Actions -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 p-4 flex flex-col justify-end gap-2">
                            @if(!empty($item['attribution']['photographer_name']))
                                <div class="text-[10px] text-white/80 truncate">
                                    Foto oleh: <strong>{{ $item['attribution']['photographer_name'] }}</strong>
                                </div>
                            @endif

                            <div class="flex items-center gap-2 pt-1">
                                <form method="POST" action="{{ route('ai-studio.inspiration.use-as-reference') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="image_url" value="{{ $imgUrl }}">
                                    <input type="hidden" name="title" value="{{ $title }}">
                                    <input type="hidden" name="source" value="{{ $provider }}">
                                    <input type="hidden" name="external_id" value="{{ $externalId }}">
                                    <input type="hidden" name="asset_id" value="{{ $itemId }}">
                                    <input type="hidden" name="style_prompt" value="{{ $stylePrompt }}">
                                    <input type="hidden" name="aspect_ratio" value="{{ $aspectRatio }}">
                                    <button type="submit"
                                        class="w-full text-center py-2 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md transition-all active:scale-95">
                                        Gunakan Referensi
                                    </button>
                                </form>

                                @if($itemId)
                                    <a href="{{ route('ai-studio.inspiration.show', $itemId) }}"
                                        class="p-2 rounded-[10px] bg-white/20 hover:bg-white/30 text-white text-xs font-semibold backdrop-blur-sm transition-all"
                                        title="Lihat Detail & Atribusi Hak Cipta">
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Item Details -->
                    <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 mb-1">
                                <span class="truncate">{{ $categoryName }}</span>
                                <span class="shrink-0 text-[10px] font-bold bg-black/[0.04] dark:bg-white/[0.06] px-1.5 py-0.2 rounded">{{ $aspectRatio }}</span>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white line-clamp-1 group-hover:text-[#007AFF] transition-colors" title="{{ $title }}">
                                {{ $title }}
                            </h4>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px]">
                            <span class="text-black/50 dark:text-white/50 truncate max-w-[140px] flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3 h-3 text-[#34C759]"></i>
                                <span>{{ $item['license']['policy'] ?? 'Komersial Sah' }}</span>
                            </span>
                            <form method="POST" action="{{ route('ai-studio.inspiration.use-as-reference') }}">
                                @csrf
                                <input type="hidden" name="image_url" value="{{ $imgUrl }}">
                                <input type="hidden" name="title" value="{{ $title }}">
                                <input type="hidden" name="source" value="{{ $provider }}">
                                <input type="hidden" name="style_prompt" value="{{ $stylePrompt }}">
                                <button type="submit"
                                    class="inline-flex items-center gap-1 font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                    <span>Pakai</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL TAMBAHKAN KE KOLEKSI --}}
    <template x-teleport="body">
    <div x-show="collectionModalOpen" style="display: none;"
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="collectionModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="bookmark-plus" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Simpan ke Papan Koleksi</span>
                </h3>
                <button type="button" @click="collectionModalOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-3">
                <template x-if="targetItem">
                    <div class="flex items-center gap-3 p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04]">
                        <img :src="targetItem.display_url || targetItem.preview_url" class="w-12 h-12 rounded-[8px] object-cover shrink-0">
                        <div class="min-w-0 flex-1">
                            <h5 class="text-xs font-bold text-black dark:text-white truncate" x-text="targetItem.title"></h5>
                            <span class="text-[10px] text-black/50 dark:text-white/50 uppercase" x-text="targetItem.source_provider"></span>
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Pilih Papan Koleksi Tujuan</label>
                    <select x-model="selectedCollectionId"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">-- Pilih Koleksi --</option>
                        @foreach($featuredCollections ?? [] as $col)
                            <option value="{{ $col->id }}">{{ $col->name }} ({{ $col->items_count }} item)</option>
                        @endforeach
                    </select>
                </div>

                <div class="text-[11px] text-black/50 dark:text-white/50">
                    Atau <a href="{{ route('ai-studio.inspiration.collections') }}" class="text-[#007AFF] font-bold hover:underline">buat papan koleksi baru</a> terlebih dahulu.
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/10">
                <button type="button" @click="collectionModalOpen = false"
                    class="px-3.5 py-1.5 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">
                    Batal
                </button>
                <button type="button" @click="saveItemToCollection()"
                    :disabled="!selectedCollectionId || isSavingToCollection"
                    class="px-4 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                    <span x-text="isSavingToCollection ? 'Menyimpan...' : 'Simpan Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>
    </template>

</div>

@push('scripts')
<script>
function inspirationExplorer() {
    return {
        favoritedAssetIds: @js($favoritedAssetIds ?? []),
        favoritedExternalIds: @js($favoritedExternalIds ?? []),
        collectionModalOpen: false,
        targetItem: null,
        selectedCollectionId: '',
        isSavingToCollection: false,

        init() {
            lucide.createIcons();
        },

        isFavorited(item) {
            if (item.id && this.favoritedAssetIds.includes(item.id)) return true;
            if (item.external_id && this.favoritedExternalIds.includes(String(item.external_id))) return true;
            return false;
        },

        async toggleFavoriteItem(item) {
            const alreadyFav = this.isFavorited(item);
            // Optimistic update
            if (alreadyFav) {
                if (item.id) this.favoritedAssetIds = this.favoritedAssetIds.filter(id => id !== item.id);
                if (item.external_id) this.favoritedExternalIds = this.favoritedExternalIds.filter(id => id !== String(item.external_id));
            } else {
                if (item.id) this.favoritedAssetIds.push(item.id);
                if (item.external_id) this.favoritedExternalIds.push(String(item.external_id));
            }

            try {
                const res = await fetch('{{ route('ai-studio.inspiration.favorites.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        asset_id: item.id || null,
                        external_id: item.external_id || null,
                        source_provider: item.source_provider || 'cooca',
                        title: item.title || '',
                        preview_url: item.display_url || item.preview_url || '',
                    })
                });
                const data = await res.json();
                if (!data.success) {
                    // Revert if failed
                    if (alreadyFav) {
                        if (item.id) this.favoritedAssetIds.push(item.id);
                        if (item.external_id) this.favoritedExternalIds.push(String(item.external_id));
                    } else {
                        if (item.id) this.favoritedAssetIds = this.favoritedAssetIds.filter(id => id !== item.id);
                        if (item.external_id) this.favoritedExternalIds = this.favoritedExternalIds.filter(id => id !== String(item.external_id));
                    }
                }
            } catch (err) {
                console.error(err);
            }
        },

        openAddToCollectionModal(item) {
            this.targetItem = item;
            this.selectedCollectionId = '';
            this.collectionModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async saveItemToCollection() {
            if (!this.selectedCollectionId || !this.targetItem) return;
            this.isSavingToCollection = true;

            try {
                const url = `{{ url('ai-studio/inspiration/collections') }}/${this.selectedCollectionId}/items`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        asset_id: this.targetItem.id || null,
                        external_id: this.targetItem.external_id || null,
                        source_provider: this.targetItem.source_provider || 'cooca',
                        display_url: this.targetItem.display_url || this.targetItem.preview_url || '',
                        title: this.targetItem.title || '',
                        aspect_ratio: this.targetItem.aspect_ratio || '1:1',
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.collectionModalOpen = false;
                    alert('Berhasil disimpan ke papan koleksi!');
                } else {
                    alert(data.message || 'Gagal menyimpan ke koleksi');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
            } finally {
                this.isSavingToCollection = false;
            }
        }
    };
}
</script>
@endpush
@endsection
