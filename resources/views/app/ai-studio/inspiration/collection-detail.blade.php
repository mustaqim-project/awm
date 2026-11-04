@extends('layouts.app', [
    'title' => $collection->name . ' - Detail Koleksi AI Studio',
    'headerTitle' => 'Papan Koleksi Inspirasi',
    'headerSubtitle' => 'Koleksi tematik gaya visual untuk eksplorasi dan referensi generator AI Studio',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        :title="$collection->name"
        :subtitle="$collection->description ?: 'Papan koleksi tematik untuk referensi visual dan komposisi pencahayaan'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => route('ai-studio.inspiration.index')],
            ['label' => 'Koleksi', 'url' => route('ai-studio.inspiration.collections')],
            ['label' => $collection->name, 'url' => null]
        ]">

        <div class="flex items-center gap-2">
            <a href="{{ route('ai-studio.inspiration.collections') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white transition-all">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Semua Koleksi</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. COLLECTION METADATA BANNER --}}
    <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                <i data-lucide="bookmark" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-black dark:text-white">{{ $collection->name }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $collection->visibility === 'public' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60' }}">
                        {{ $collection->visibility === 'public' ? 'Koleksi Publik' : 'Koleksi Privat' }}
                    </span>
                    @if($collection->owner_type === 'global')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            Kurasi Resmi COOCA
                        </span>
                    @endif
                </div>
                <p class="text-xs text-black/50 dark:text-white/50">{{ $collection->items->count() }} Item Visual Tersimpan</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all inline-flex items-center gap-1.5">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Tambah Dari Galeri</span>
            </a>
        </div>
    </div>

    {{-- 3. ITEMS GRID --}}
    @if($collection->items->isEmpty())
        <div class="w-full rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto">
                <i data-lucide="image" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-black dark:text-white">Belum Ada Gambar di Koleksi Ini</h4>
                <p class="text-xs text-black/50 dark:text-white/50">Jelajahi katalog inspirasi dan klik "Simpan ke Koleksi" untuk menambahkan referensi favorit.</p>
            </div>
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-xs font-semibold text-white shadow-xs">
                Jelajahi Inspirasi Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($collection->items as $itemRecord)
                @php
                    $rawColl = $itemRecord->display_url ?: $itemRecord->image_url;
                    $displayImg = !empty($rawColl) ? ((str_starts_with($rawColl, 'http://') || str_starts_with($rawColl, 'https://')) ? $rawColl : asset($rawColl)) : asset('images/ai-studio/placeholder.svg');
                    $title = $itemRecord->title ?: 'Inspirasi Visual';
                    $sourceProvider = $itemRecord->source_provider ?: 'cooca';
                @endphp
                <div class="group rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-md hover:border-black/15">
                    <div class="relative aspect-[4/5] w-full bg-black/[0.04] dark:bg-white/[0.04] overflow-hidden">
                        <img src="{{ $displayImg }}" alt="{{ $title }}" loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            onerror="this.onerror=null; this.src='{{ asset('images/ai-studio/placeholder.svg') }}';">

                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-md">
                            {{ $sourceProvider }}
                        </span>

                        {{-- Action Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end gap-2">
                            <form method="POST" action="{{ route('ai-studio.inspiration.use-as-reference') }}">
                                @csrf
                                <input type="hidden" name="image_url" value="{{ $displayImg }}">
                                <input type="hidden" name="title" value="{{ $title }}">
                                <input type="hidden" name="source" value="{{ $sourceProvider }}">
                                <input type="hidden" name="external_id" value="{{ $itemRecord->external_id }}">
                                <button type="submit"
                                    class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all">
                                    Gunakan Sebagai Referensi
                                </button>
                            </form>

                            @if($isOwner)
                                <form method="POST" action="{{ route('ai-studio.inspiration.collections.items.destroy', ['collection' => $collection->id, 'item' => $itemRecord->id]) }}"
                                    onsubmit="return confirm('Hapus gambar ini dari papan koleksi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full py-1.5 px-3 rounded-[10px] bg-red-500/20 hover:bg-red-500/30 text-white text-[11px] font-semibold transition-all">
                                        Hapus dari Koleksi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="p-3">
                        <h4 class="text-xs font-bold text-black dark:text-white truncate">{{ $title }}</h4>
                        <div class="flex items-center justify-between pt-1 text-[10px] text-black/50 dark:text-white/50">
                            <span>Ditambahkan {{ $itemRecord->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

