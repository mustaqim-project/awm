@extends('layouts.app', [
    'title' => 'Kategori & Taksonomi Sektor Industri - AI Studio Inspiration',
    'headerTitle' => 'Kategori & Taksonomi Industri UMKM',
    'headerSubtitle' => 'Eksplorasi gaya visual, tata letak fotografi, dan acuan komposisi berdasarkan 20+ sektor bisnis UMKM',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        title="Taksonomi Sektor & Kategori Inspirasi"
        subtitle="Jelajahi referensi gaya visual berdasarkan industri spesifik untuk akurasi promosi bisnis Anda"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => route('ai-studio.inspiration.index')],
            ['label' => 'Kategori & Sektor', 'url' => null]
        ]">

        <div class="flex items-center gap-2">
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Galeri</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. TAB NAVIGATOR INSPIRATION --}}
    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3 overflow-x-auto scrollbar-none text-xs font-semibold">
        <a href="{{ route('ai-studio.inspiration.index') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Semua Inspirasi
        </a>
        <a href="{{ route('ai-studio.inspiration.categories') }}"
            class="px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] text-white shadow-xs">
            Kategori & Sektor
        </a>
        <a href="{{ route('ai-studio.inspiration.collections') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Koleksi Terpilih
        </a>
        <a href="{{ route('ai-studio.inspiration.favorites') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Favorit Saya
        </a>
        <a href="{{ route('ai-studio.inspiration.my-library') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Perpustakaan Pribadi
        </a>
    </div>

    {{-- 3. SEKTOR MULTI-INDUSTRI UMKM INDONESIA --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Sektor Industri UMKM Terkurasi</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Filter cepat gaya visual yang disesuaikan dengan karakteristik bidang usaha Anda</p>
            </div>
            <span class="text-xs font-semibold text-[#007AFF]">{{ count($industrySectors ?? []) }} Sektor</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($industrySectors ?? [] as $code => $sec)
                <a href="{{ route('ai-studio.inspiration.index', ['industry' => $code]) }}"
                    class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 hover:border-[#007AFF] hover:shadow-sm transition-all group flex flex-col justify-between h-24">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center transition-transform group-hover:scale-105">
                        <i data-lucide="{{ $sec['icon'] ?? 'tag' }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors truncate">
                            {{ $sec['label'] }}
                        </div>
                        <div class="text-[10px] text-black/40 dark:text-white/40">Eksplorasi &rarr;</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 4. KATEGORI GAYA VISUAL TERDAFTAR --}}
    <div class="space-y-3 pt-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Kategori Komposisi & Tipe Konten</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Klasifikasi visual berdasarkan sudut pandang fotografi dan mood pencahayaan</p>
            </div>
            <span class="text-xs font-semibold text-[#007AFF]">{{ $categories->count() }} Kategori</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($categories as $category)
                <a href="{{ route('ai-studio.inspiration.index', ['category' => $category->slug]) }}"
                    class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 hover:border-[#007AFF] hover:shadow-md transition-all group flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[#007AFF] flex items-center justify-center font-bold text-sm">
                                <i data-lucide="{{ $category->icon ?: 'image' }}" class="w-4.5 h-4.5"></i>
                            </span>
                            @if($category->industry_id)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                    {{ $category->industry_id }}
                                </span>
                            @endif
                        </div>
                        <h4 class="text-sm font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                            {{ $category->name }}
                        </h4>
                        @if($category->description)
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2 leading-relaxed">
                                {{ $category->description }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-3 mt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] font-semibold text-[#007AFF]">
                        <span>Buka Galeri</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i>
                    </div>
                </a>
            @empty
                <div class="col-span-full p-8 text-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/10">
                    <p class="text-xs text-black/50 dark:text-white/50">Belum ada kategori yang dikonfigurasi.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

