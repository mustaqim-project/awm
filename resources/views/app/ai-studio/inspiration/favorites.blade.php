@extends('layouts.app', [
    'title' => 'Gambar Favorit - AI Studio Inspiration Library',
    'headerTitle' => 'Favorit Saya',
    'headerSubtitle' => 'Koleksi gambar inspirasi yang telah Anda tandai sebagai favorit untuk akses cepat',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    async toggleFavorite(item, cardEl) {
        try {
            const res = await fetch('{{ route('ai-studio.inspiration.favorites.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(item)
            });
            const data = await res.json();
            if (data.success && !data.is_favorited) {
                cardEl.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
        }
    }
}">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        title="Gambar Favorit Tersimpan"
        subtitle="Akses cepat seluruh aset dan foto referensi inspirasi yang pernah Anda sukai"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => route('ai-studio.inspiration.index')],
            ['label' => 'Favorit', 'url' => null]
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
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Kategori & Sektor
        </a>
        <a href="{{ route('ai-studio.inspiration.collections') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Koleksi Terpilih
        </a>
        <a href="{{ route('ai-studio.inspiration.favorites') }}"
            class="px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] text-white shadow-xs">
            Favorit Saya
        </a>
        <a href="{{ route('ai-studio.inspiration.my-library') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Perpustakaan Pribadi
        </a>
    </div>

    {{-- 3. GRID FAVORIT --}}
    @if($favorites->isEmpty())
        <div class="w-full rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center mx-auto">
                <i data-lucide="heart" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-black dark:text-white">Belum Ada Gambar Favorit</h4>
                <p class="text-xs text-black/50 dark:text-white/50">Klik ikon hati pada gambar di katalog inspirasi untuk menyimpannya di sini.</p>
            </div>
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-xs font-semibold text-white shadow-xs">
                Jelajahi Inspirasi Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($favorites as $fav)
                @php
                    $displayImg = $fav->asset?->display_url ?: $fav->item?->display_image_url ?: $fav->preview_url;
                    $title = $fav->title ?: $fav->asset?->title ?: $fav->item?->title ?: 'Inspirasi Favorit';
                    $sourceProvider = $fav->source_provider ?: $fav->asset?->source_provider ?: $fav->item?->source?->code ?: 'cooca';
                @endphp
                <div x-ref="card{{ $fav->id }}"
                    class="group rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-md hover:border-black/15">
                    <div class="relative aspect-[4/5] w-full bg-black/[0.04] dark:bg-white/[0.04] overflow-hidden">
                        <img src="{{ $displayImg }}" alt="{{ $title }}" loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            onerror="this.onerror=null; this.src='{{ asset('images/ai-studio/placeholder.svg') }}';">

                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-md">
                            {{ $sourceProvider }}
                        </span>

                        {{-- Tombol Unfavorite Cepat --}}
                        <button type="button"
                            @click="toggleFavorite({
                                asset_id: '{{ $fav->asset_id }}',
                                item_id: '{{ $fav->item_id }}',
                                external_id: '{{ $fav->external_id }}',
                                source_provider: '{{ $sourceProvider }}'
                            }, $refs.card{{ $fav->id }})"
                            title="Hapus dari Favorit"
                            class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/90 dark:bg-black/90 text-red-500 flex items-center justify-center shadow-xs hover:scale-110 transition-transform">
                            <i data-lucide="heart" class="w-4 h-4 fill-red-500"></i>
                        </button>

                        {{-- Action Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end gap-2">
                            <form method="POST" action="{{ route('ai-studio.inspiration.use-as-reference') }}">
                                @csrf
                                <input type="hidden" name="image_url" value="{{ $displayImg }}">
                                <input type="hidden" name="title" value="{{ $title }}">
                                <input type="hidden" name="source" value="{{ $sourceProvider }}">
                                <input type="hidden" name="external_id" value="{{ $fav->external_id }}">
                                <button type="submit"
                                    class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all">
                                    Gunakan Sebagai Referensi
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-3">
                        <h4 class="text-xs font-bold text-black dark:text-white truncate">{{ $title }}</h4>
                        <div class="flex items-center justify-between pt-1 text-[10px] text-black/50 dark:text-white/50">
                            <span>Disimpan {{ $fav->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $favorites->links() }}
        </div>
    @endif

</div>
@endsection

