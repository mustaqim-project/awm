@extends('layouts.admin', [
    'title' => 'Katalog & Moderasi Inspirasi Visual - Admin Console',
    'headerTitle' => 'Katalog & Moderasi AI Inspiration Library',
    'headerSubtitle' => 'Moderasi hak cipta, audit kepatuhan lisensi, dan tata kelola katalog gambar inspirasi global',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminInspirationModeration()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </span>
                    <span>Moderasi & Tata Kelola Aset Inspirasi</span>
                </h1>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Audit hak cipta komersial, setujui atau tolak aset dari penyedia eksternal dan kurasi COOCA.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.ai-studio.inspiration.providers') }}"
                    class="px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="plug" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Provider API & Status</span>
                </a>
                <a href="{{ route('admin.ai-studio.inspiration.categories.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="tag" class="w-3.5 h-3.5 text-[#34C759]"></i>
                    <span>Kelola Kategori</span>
                </a>
                <a href="{{ route('admin.ai-studio.inspiration.collections.index') }}"
                    class="px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="bookmark" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>Koleksi Global</span>
                </a>
            </div>
        </div>

        <!-- Submodule Navigation Tabs -->
        <div class="w-full flex items-center gap-1.5 border-t border-black/5 dark:border-white/10 pt-4 overflow-x-auto no-scrollbar">
            <a href="{{ route('admin.ai-studio.visual.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                <span>Studio Visual &amp; Medsos</span>
            </a>
            <a href="{{ route('admin.ai-studio.sources.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                <span>Sumber Inspirasi &amp; Tren</span>
            </a>
            <a href="{{ route('admin.ai-studio.pricing.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                <span>Model Registry &amp; Tarif</span>
            </a>
            <a href="{{ route('admin.ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-bold transition-all bg-[#007AFF] text-white shadow-xs">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Katalog &amp; Moderasi</span>
            </a>
        </div>
    </div>

    <!-- Metrik Moderasi -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1">
            <div class="text-[11px] text-black/50 dark:text-white/50">Total Aset Terdaftar</div>
            <div class="text-lg font-bold text-black dark:text-white">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1">
            <div class="text-[11px] text-[#34C759]">Disetujui (Approved)</div>
            <div class="text-lg font-bold text-[#34C759]">{{ number_format($stats['approved'] ?? 0) }}</div>
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1">
            <div class="text-[11px] text-amber-500">Menunggu Review</div>
            <div class="text-lg font-bold text-amber-500">{{ number_format($stats['pending'] ?? 0) }}</div>
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1">
            <div class="text-[11px] text-red-500">Ditolak (Rejected)</div>
            <div class="text-lg font-bold text-red-500">{{ number_format($stats['rejected'] ?? 0) }}</div>
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1 col-span-2 sm:col-span-1">
            <div class="text-[11px] text-purple-500">Ditandai (Flagged)</div>
            <div class="text-lg font-bold text-purple-500">{{ number_format($stats['flagged'] ?? 0) }}</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
        <form method="GET" action="{{ route('admin.ai-studio.inspiration.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ request('q', $query ?? '') }}" placeholder="Cari judul, tag, atau external ID..."
                    class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div class="w-full sm:w-44">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="all">Semua Status</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    <option value="flagged" {{ request('status') === 'flagged' ? 'selected' : '' }}>Ditandai (Flagged)</option>
                </select>
            </div>

            <div class="w-full sm:w-44">
                <select name="source" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="all">Semua Sumber</option>
                    <option value="cooca_curated" {{ request('source') === 'cooca_curated' ? 'selected' : '' }}>Kurasi COOCA</option>
                    <option value="pexels" {{ request('source') === 'pexels' ? 'selected' : '' }}>Pexels</option>
                    <option value="pixabay" {{ request('source') === 'pixabay' ? 'selected' : '' }}>Pixabay</option>
                    <option value="unsplash" {{ request('source') === 'unsplash' ? 'selected' : '' }}>Unsplash</option>
                    <option value="pinterest" {{ request('source') === 'pinterest' ? 'selected' : '' }}>Pinterest</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-xs font-semibold text-black dark:text-white">
                Filter
            </button>
        </form>
    </div>

    <!-- Moderation Table -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Gambar</th>
                        <th class="py-3 px-4">Judul & Atribusi</th>
                        <th class="py-3 px-4">Sumber</th>
                        <th class="py-3 px-4">Lisensi</th>
                        <th class="py-3 px-4">Status Moderasi</th>
                        <th class="py-3 px-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                            <td class="py-3 px-4 w-20">
                                @php
                                    $imgSrc = $asset->display_url ?? $asset->image_url ?? $asset->thumbnail_url ?? '';
                                    if (!empty($imgSrc) && !str_starts_with($imgSrc, 'http://') && !str_starts_with($imgSrc, 'https://')) {
                                        $imgSrc = asset($imgSrc);
                                    }
                                @endphp
                                <img src="{{ $imgSrc ?: asset('images/ai-studio/placeholder.svg') }}" alt="{{ $asset->title }}" class="w-14 h-14 rounded-[8px] object-cover bg-black/5 border border-black/10" onerror="this.onerror=null; this.src='{{ asset('images/ai-studio/placeholder.svg') }}';">
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-black dark:text-white line-clamp-1">{{ $asset->title }}</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 line-clamp-1">
                                    {{ $asset->photographer_name ? 'Oleh: ' . $asset->photographer_name : 'Koleksi Platform' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-black/[0.05] dark:bg-white/[0.08]">
                                    {{ $asset->source_provider }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-[11px] font-semibold {{ $asset->commercial_use_allowed ? 'text-[#34C759]' : 'text-red-500' }}">
                                    {{ $asset->license_type ?: 'Commercial' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'approved' => 'bg-[#34C759]/10 text-[#34C759]',
                                        'pending_review' => 'bg-amber-500/10 text-amber-500',
                                        'rejected' => 'bg-red-500/10 text-red-500',
                                        'flagged' => 'bg-purple-500/10 text-purple-500',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusClasses[$asset->moderation_status] ?? 'bg-black/5 text-black/60' }}">
                                    {{ ucfirst(str_replace('_', ' ', $asset->moderation_status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($asset->moderation_status !== 'approved')
                                        <button type="button" @click="moderate('{{ $asset->id }}', 'approved')"
                                            class="px-2 py-1 rounded-[6px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] font-bold text-[11px]">
                                            Setujui
                                        </button>
                                    @endif
                                    @if($asset->moderation_status !== 'rejected')
                                        <button type="button" @click="moderate('{{ $asset->id }}', 'rejected')"
                                            class="px-2 py-1 rounded-[6px] bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold text-[11px]">
                                            Tolak
                                        </button>
                                    @endif
                                    @if($asset->moderation_status !== 'flagged')
                                        <button type="button" @click="moderate('{{ $asset->id }}', 'flagged')"
                                            class="px-2 py-1 rounded-[6px] bg-purple-500/10 hover:bg-purple-500/20 text-purple-500 font-bold text-[11px]">
                                            Tandai
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-black/50 dark:text-white/50">
                                Tidak ada data aset inspirasi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/5">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function adminInspirationModeration() {
    return {
        async moderate(assetId, status) {
            try {
                const res = await fetch(`{{ url('admin/ai-studio/inspiration') }}/${assetId}/moderate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal memoderasi aset');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
            }
        }
    };
}
</script>
@endpush
@endsection

