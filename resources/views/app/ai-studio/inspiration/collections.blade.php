@extends('layouts.app', [
    'title' => 'Koleksi Inspirasi - AI Studio Inspiration Library',
    'headerTitle' => 'Koleksi Gambar Inspirasi',
    'headerSubtitle' => 'Kumpulan ide visual bertema yang dikurasi oleh tim COOCA dan papan koleksi pribadi Anda',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{ createModalOpen: false }">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        title="Koleksi Inspirasi Visual"
        subtitle="Organisir gaya visual produk ke dalam papan koleksi tematik untuk panduan kampanye promosi"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => route('ai-studio.inspiration.index')],
            ['label' => 'Koleksi', 'url' => null]
        ]">

        <div class="flex items-center gap-2">
            <button type="button" @click="createModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-xs transition-all">
                <i data-lucide="folder-plus" class="w-3.5 h-3.5"></i>
                <span>Buat Koleksi Baru</span>
            </button>
        </div>
    </x-module-header>

    {{-- 2. TAB NAVIGATOR INSPIRATION --}}
    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3 overflow-x-auto scrollbar-none text-xs font-semibold">
        <a href="{{ route('ai-studio.inspiration.index') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Semua Inspirasi
        </a>
        <a href="{{ route('ai-studio.inspiration.categories') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Kategori & Sektor
        </a>
        <a href="{{ route('ai-studio.inspiration.collections') }}"
            class="px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] text-white shadow-xs">
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

    {{-- 3. KOLEKSI PRIBADI BISNIS --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Papan Koleksi Pribadi Anda</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Hanya dapat diakses oleh tim bisnis Anda (Terisolasi aman)</p>
            </div>
            <span class="text-xs font-semibold text-[#007AFF]">{{ $myCollections->count() }} Koleksi</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($myCollections as $coll)
                <a href="{{ route('ai-studio.inspiration.collections.show', $coll->id) }}"
                    class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 hover:border-[#007AFF] hover:shadow-md transition-all group flex flex-col justify-between h-44">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="bookmark" class="w-4 h-4"></i>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $coll->visibility === 'public' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                                {{ $coll->visibility === 'public' ? 'Publik' : 'Privat' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors truncate">
                            {{ $coll->name }}
                        </h4>
                        @if($coll->description)
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2">
                                {{ $coll->description }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] font-medium text-black/50 dark:text-white/50">
                        <span>{{ $coll->items_count }} Item Tersimpan</span>
                        <span class="text-[#007AFF] font-bold group-hover:underline">Buka &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full p-8 text-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/10 space-y-2">
                    <i data-lucide="folder" class="w-8 h-8 text-black/20 dark:text-white/20 mx-auto"></i>
                    <p class="text-xs text-black/60 dark:text-white/60">Anda belum membuat papan koleksi pribadi.</p>
                    <button type="button" @click="createModalOpen = true"
                        class="text-xs font-bold text-[#007AFF] hover:underline">
                        + Buat Koleksi Pertama Anda
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. KOLEKSI GLOBAL TERKURASI COOCA --}}
    <div class="space-y-3 pt-6 border-t border-black/5 dark:border-white/10">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Koleksi Terkurasi Resmi COOCA</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Direkomendasikan langsung oleh art director dan fotografer komersial</p>
            </div>
            <span class="text-xs font-semibold text-[#007AFF]">{{ $globalCollections->count() }} Koleksi Global</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($globalCollections as $gColl)
                <a href="{{ route('ai-studio.inspiration.collections.show', $gColl->id) }}"
                    class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 hover:border-[#007AFF] hover:shadow-md transition-all group flex flex-col justify-between h-44">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="w-8 h-8 rounded-[8px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </span>
                            @if($gColl->is_featured)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                    Unggulan
                                </span>
                            @endif
                        </div>
                        <h4 class="text-sm font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors truncate">
                            {{ $gColl->name }}
                        </h4>
                        @if($gColl->description)
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2">
                                {{ $gColl->description }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] font-medium text-black/50 dark:text-white/50">
                        <span>{{ $gColl->items_count }} Item Terkurasi</span>
                        <span class="text-[#007AFF] font-bold group-hover:underline">Buka &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full p-8 text-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/5 dark:border-white/10">
                    <p class="text-xs text-black/50 dark:text-white/50">Belum ada koleksi global yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL BUAT KOLEKSI BARU --}}
    <div x-show="createModalOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="createModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="folder-plus" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Buat Papan Koleksi Baru</span>
                </h3>
                <button type="button" @click="createModalOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('ai-studio.inspiration.collections.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Koleksi</label>
                    <input type="text" name="name" required placeholder="Contoh: Inspirasi Kemasan Kopi Ramadhan"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi Singkat (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Catatan tema pencahayaan, konsep warna, atau tujuan kampanye..."
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Visibilitas</label>
                    <select name="visibility"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="private">Privat (Hanya untuk bisnis Anda)</option>
                        <option value="public">Publik (Dapat dilihat di katalog umum)</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="createModalOpen = false"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs">
                        Simpan Koleksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
