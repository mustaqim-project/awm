@extends('layouts.admin')

@section('title', 'Review Kustomisasi Kode Tema Toko')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-neutral-900 transition">Admin</a>
                <span>/</span>
                <span class="font-medium text-neutral-900">Theme Code Reviews</span>
            </div>
            <h1 class="text-2xl font-bold font-heading text-neutral-900 dark:text-white flex items-center gap-2.5">
                <span>Persetujuan Kustomisasi Tema Storefront</span>
                @if (($counts['under_review'] ?? 0) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white animate-pulse">
                        {{ $counts['under_review'] }} Menunggu
                    </span>
                @endif
            </h1>
            <p class="text-xs text-neutral-500 mt-1">
                Tinjau perubahan kode (Blade, HTML, CSS) yang diajukan oleh merchant sebelum di-ACC untuk tayang di live production.
            </p>
        </div>
    </div>

    {{-- Filter Status Tabs --}}
    <div class="flex items-center gap-2 border-b border-neutral-200 dark:border-neutral-800 pb-3 overflow-x-auto text-xs">
        <a href="{{ route('admin.theme-reviews.index', ['status' => 'under_review']) }}"
           class="px-4 py-2 rounded-xl font-semibold transition flex items-center gap-2 {{ $status === 'under_review' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
            <span>Menunggu Review</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20">{{ $counts['under_review'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.theme-reviews.index', ['status' => 'published']) }}"
           class="px-4 py-2 rounded-xl font-semibold transition flex items-center gap-2 {{ $status === 'published' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
            <span>Telah Disetujui (Live)</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20">{{ $counts['published'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.theme-reviews.index', ['status' => 'rejected']) }}"
           class="px-4 py-2 rounded-xl font-semibold transition flex items-center gap-2 {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
            <span>Ditolak / Revisi</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20">{{ $counts['rejected'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.theme-reviews.index', ['status' => 'all']) }}"
           class="px-4 py-2 rounded-xl font-semibold transition flex items-center gap-2 {{ $status === 'all' ? 'bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 shadow-xs' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
            <span>Semua Riwayat</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20">{{ $counts['all'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Submissions Table Card --}}
    <div class="rounded-3xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-50 dark:bg-neutral-800/60 border-b border-black/5 dark:border-white/5 text-neutral-500 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="px-5 py-3.5">Nama Usaha / Merchant</th>
                        <th class="px-5 py-3.5">Tema Toko</th>
                        <th class="px-5 py-3.5">Berkas Komponen</th>
                        <th class="px-5 py-3.5">Waktu Pengajuan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($items as $item)
                        <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/40 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-neutral-900 dark:text-white">
                                    {{ $item->business->name ?? 'Bisnis Unknown' }}
                                </div>
                                <div class="text-[11px] text-neutral-400 font-mono">
                                    /{{ $item->business->slug ?? $item->business_id }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">
                                    {{ $item->theme_preset }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-neutral-900 dark:text-white">
                                    {{ App\Models\BusinessStorefrontCustomTheme::SUPPORTED_FILES[$item->file_key]['label'] ?? $item->file_key }}
                                </div>
                                <div class="text-[10.5px] font-mono text-neutral-400">
                                    {{ $item->file_name }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-neutral-500">
                                @if ($item->submitted_at)
                                    <div>{{ $item->submitted_at->diffForHumans() }}</div>
                                    <div class="text-[10.5px] text-neutral-400">{{ $item->submitted_at->format('d M Y H:i') }}</div>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($item->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                        Published (Live)
                                    </span>
                                @elseif ($item->status === 'under_review')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20 animate-pulse">
                                        Menunggu Review
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-neutral-100 text-neutral-500">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.theme-reviews.show', $item) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white font-semibold text-xs transition shadow-2xs">
                                    <span>Tinjau Kode & Diff</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-neutral-400">
                                <i data-lucide="check-circle-2" class="w-8 h-8 text-neutral-300 dark:text-neutral-600 mx-auto mb-2"></i>
                                <p class="font-medium">Tidak ada pengajuan kustomisasi kode tema pada kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/5">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

