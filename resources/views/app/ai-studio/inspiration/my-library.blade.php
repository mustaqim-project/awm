@extends('layouts.app', [
    'title' => 'Perpustakaan Pribadi - AI Studio Inspiration Library',
    'headerTitle' => 'Perpustakaan Pribadi Bisnis',
    'headerSubtitle' => 'Koleksi aset foto referensi yang Anda unggah dan hasil generasi AI Studio milik bisnis Anda',
])

@section('content')
<div class="w-full min-w-0 space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{ uploadModalOpen: false }">

    {{-- 1. BENTO APPLE HIG MODULE HEADER --}}
    <x-module-header
        title="Perpustakaan Visual Pribadi"
        subtitle="Aset visual eksklusif dan foto referensi internal yang aman terisolasi untuk bisnis Anda"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Inspiration Library', 'url' => route('ai-studio.inspiration.index')],
            ['label' => 'Perpustakaan Pribadi', 'url' => null]
        ]">

        <div class="flex items-center gap-2">
            <button type="button" @click="uploadModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-xs transition-all">
                <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                <span>Unggah Foto Referensi</span>
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
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Koleksi Terpilih
        </a>
        <a href="{{ route('ai-studio.inspiration.favorites') }}"
            class="px-3.5 py-1.5 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-colors">
            Favorit Saya
        </a>
        <a href="{{ route('ai-studio.inspiration.my-library') }}"
            class="px-3.5 py-1.5 rounded-[10px] bg-[#007AFF] text-white shadow-xs">
            Perpustakaan Pribadi
        </a>
    </div>

    {{-- 3. STATISTIK ASET PRIBADI --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 flex items-center gap-3">
            <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                <i data-lucide="layers" class="w-4.5 h-4.5"></i>
            </div>
            <div>
                <div class="text-[11px] text-black/50 dark:text-white/50">Total Aset Bisnis</div>
                <div class="text-base font-bold text-black dark:text-white">{{ $assets->total() }} Aset</div>
            </div>
        </div>

        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 flex items-center gap-3">
            <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                <i data-lucide="upload" class="w-4.5 h-4.5"></i>
            </div>
            <div>
                <div class="text-[11px] text-black/50 dark:text-white/50">Foto Diunggah</div>
                <div class="text-base font-bold text-black dark:text-white">{{ $totalUploaded }} Foto</div>
            </div>
        </div>

        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                <i data-lucide="sparkles" class="w-4.5 h-4.5"></i>
            </div>
            <div>
                <div class="text-[11px] text-black/50 dark:text-white/50">Dihasilkan AI Studio</div>
                <div class="text-base font-bold text-black dark:text-white">{{ $totalGenerated }} Kreasi</div>
            </div>
        </div>
    </div>

    {{-- 4. GRID ASET PRIBADI --}}
    @if($assets->isEmpty())
        <div class="w-full rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto">
                <i data-lucide="folder-open" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-black dark:text-white">Perpustakaan Pribadi Masih Kosong</h4>
                <p class="text-xs text-black/50 dark:text-white/50">Unggah foto produk atau referensi gaya Anda untuk digunakan berulang kali di AI Studio.</p>
            </div>
            <button type="button" @click="uploadModalOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[12px] bg-[#007AFF] text-xs font-semibold text-white shadow-xs">
                Unggah Foto Sekarang
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($assets as $asset)
                @php
                    $rawMyUrl = $asset->display_url ?? $asset->image_url ?? $asset->thumbnail_url ?? '';
                    $myImgUrl = !empty($rawMyUrl)
                        ? ((str_starts_with($rawMyUrl, 'http://') || str_starts_with($rawMyUrl, 'https://')) ? $rawMyUrl : asset($rawMyUrl))
                        : asset('images/ai-studio/placeholder.svg');
                @endphp
                <div class="group rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden flex flex-col justify-between transition-all hover:shadow-md hover:border-black/15">
                    <div class="relative aspect-[4/5] w-full bg-black/[0.04] dark:bg-white/[0.04] overflow-hidden">
                        <img src="{{ $myImgUrl }}" alt="{{ $asset->title }}" loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            onerror="this.onerror=null; this.src='{{ asset('images/ai-studio/placeholder.svg') }}';">

                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-md">
                            {{ $asset->source_type === 'ai_generated' ? 'AI Studio' : 'Unggahan Saya' }}
                        </span>

                        <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/90 text-white backdrop-blur-md">
                            Privat
                        </span>

                        {{-- Action Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end gap-2">
                            <form method="POST" action="{{ route('ai-studio.inspiration.use-as-reference') }}">
                                @csrf
                                <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                                <input type="hidden" name="image_url" value="{{ $myImgUrl }}">
                                <input type="hidden" name="title" value="{{ $asset->title }}">
                                <button type="submit"
                                    class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all">
                                    Gunakan Sebagai Referensi
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-3">
                        <h4 class="text-xs font-bold text-black dark:text-white truncate">{{ $asset->title }}</h4>
                        <div class="flex items-center justify-between pt-1 text-[10px] text-black/50 dark:text-white/50">
                            <span>{{ $asset->aspect_ratio ?: '1:1' }}</span>
                            <span>{{ $asset->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $assets->links() }}
        </div>
    @endif

    {{-- MODAL UNGGAH FOTO REFERENSI --}}
    <div x-show="uploadModalOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="uploadModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Unggah Foto Referensi Pribadi</span>
                </h3>
                <button type="button" @click="uploadModalOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('ai-studio.inspiration.my-library.upload') }}" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Pilih Berkas Foto (JPG, PNG, WEBP max 10MB)</label>
                    <input type="file" name="image" required accept="image/png,image/jpeg,image/webp"
                        class="w-full text-xs text-black/60 dark:text-white/60 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Judul / Catatan Gaya</label>
                    <input type="text" name="title" required placeholder="Contoh: Foto Produk Kemasan Depan"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Rasio Aspek Foto</label>
                    <select name="aspect_ratio"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="1:1">1:1 Square (Feed Instagram / Katalog POS)</option>
                        <option value="9:16">9:16 Vertical (Story, TikTok, Reels)</option>
                        <option value="16:9">16:9 Landscape (Banner Website & Marketplace)</option>
                        <option value="4:5">4:5 Portrait</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="uploadModalOpen = false"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs">
                        Unggah ke Koleksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

