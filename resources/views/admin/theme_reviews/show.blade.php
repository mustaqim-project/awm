@extends('layouts.admin')

@section('title', 'Tinjau Kode Tema - ' . ($theme->business->name ?? 'Bisnis'))

@push('styles')
<style>
    .diff-container {
        height: 520px;
        border-radius: 1rem;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    previewMode: false,
    rejectNotes: ''
}">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-black/5 dark:border-white/5">
        <div>
            <div class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
                <a href="{{ route('admin.theme-reviews.index') }}" class="hover:text-neutral-900 transition">Theme Reviews</a>
                <span>/</span>
                <span class="font-medium text-neutral-900">Tinjau Kode</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center font-bold">
                    <i data-lucide="git-pull-request" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-heading text-neutral-900 dark:text-white flex items-center gap-2">
                        <span>{{ $theme->business->name }}</span>
                        <span class="text-neutral-400 font-normal">/</span>
                        <span class="text-[#007AFF] font-mono text-lg">{{ $theme->file_name }}</span>
                    </h1>
                    <p class="text-xs text-neutral-500 mt-0.5">
                        Tema: <strong class="font-mono text-neutral-700 dark:text-neutral-300">{{ $theme->theme_preset }}</strong> &bull; 
                        Komponen: <strong>{{ App\Models\BusinessStorefrontCustomTheme::SUPPORTED_FILES[$theme->file_key]['label'] ?? $theme->file_key }}</strong> &bull;
                        Diajukan: {{ $theme->submitted_at ? $theme->submitted_at->format('d M Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Preview Sandbox --}}
            <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-[#1C1C1E] text-neutral-800 dark:text-neutral-200 border border-black/10 dark:border-white/10 text-xs font-semibold hover:border-black/20 shadow-2xs transition">
                <i data-lucide="external-link" class="w-4 h-4 text-emerald-500"></i>
                <span>Buka Preview Toko</span>
            </a>

            {{-- Reject Button --}}
            <button type="button" 
                    @click="rejectModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40 text-xs font-semibold transition cursor-pointer">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                <span>Tolak dengan Catatan</span>
            </button>

            {{-- Approve Button --}}
            <form action="{{ route('admin.theme-reviews.approve', $theme) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Apakah Anda yakin ingin menyetujui kode ini? Perubahan akan langsung aktif untuk seluruh pelanggan di live production.');"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-[0_4px_16px_rgba(16,185,129,0.3)] transition cursor-pointer">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Setujui & Publikasikan ke Live</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Meta Details Card --}}
    <div class="p-4 rounded-3xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-neutral-400 block mb-0.5">Pemilik Usaha</span>
            <strong class="text-neutral-800 dark:text-neutral-200 font-semibold">{{ $theme->business->name }}</strong>
            <div class="text-[11px] text-neutral-500 font-mono">ID: {{ $theme->business_id }}</div>
        </div>
        <div>
            <span class="text-neutral-400 block mb-0.5">Preset Tema Toko</span>
            <strong class="text-neutral-800 dark:text-neutral-200 font-mono font-semibold">{{ $theme->theme_preset }}</strong>
        </div>
        <div>
            <span class="text-neutral-400 block mb-0.5">Status Pengajuan</span>
            @if ($theme->status === 'published')
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                    Live Production
                </span>
            @elseif ($theme->status === 'under_review')
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">
                    Menunggu Review Admin
                </span>
            @elseif ($theme->status === 'rejected')
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                    Ditolak (Perlu Revisi)
                </span>
            @endif
        </div>
        <div>
            <span class="text-neutral-400 block mb-0.5">Riwayat Evaluasi</span>
            <span class="text-neutral-600 dark:text-neutral-400">
                {{ $theme->reviewed_at ? $theme->reviewed_at->format('d M Y H:i') . ' oleh ' . ($theme->reviewer->name ?? 'Admin') : 'Belum di-review' }}
            </span>
        </div>
    </div>

    {{-- Side-by-Side Diff Viewer (Monaco Diff Editor via CDN) --}}
    <div class="rounded-3xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-2xs overflow-hidden">
        <div class="p-4 bg-neutral-50 dark:bg-neutral-800/60 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1.5 font-semibold text-neutral-600 dark:text-neutral-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span>Kiri: Kode Asli Resmi (Baseline)</span>
                </div>
                <span class="text-neutral-300 dark:text-neutral-600">vs</span>
                <div class="flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Kanan: Usulan Custom Merchant (Draft)</span>
                </div>
            </div>
            <div class="font-mono text-xs text-neutral-500">
                {{ $theme->file_name }}
            </div>
        </div>

        <div class="diff-container bg-[#1E1E1E]">
            <div id="monaco-diff-editor" class="w-full h-full"></div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @keydown.escape.window="rejectModalOpen = false">
        <div class="w-full max-w-md p-6 rounded-3xl bg-white dark:bg-[#1C1C1E] shadow-2xl border border-black/10 dark:border-white/10 space-y-4"
             @click.outside="rejectModalOpen = false">
            <div class="flex items-center gap-3 text-rose-600">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                <h3 class="font-bold text-base text-neutral-900 dark:text-white">Tolak Kustomisasi Tema</h3>
            </div>
            <p class="text-xs text-neutral-500">
                Berikan catatan revisi atau alasan penolakan agar merchant dapat memperbaiki codingan tema mereka.
            </p>
            <form action="{{ route('admin.theme-reviews.reject', $theme) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Catatan Penolakan / Revisi</label>
                    <textarea name="notes" rows="4" required
                              class="w-full p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs focus:ring-2 focus:ring-rose-500 outline-none"
                              placeholder="Misal: Terdapat tag tag HTML yang tidak tertutup, script berbahaya, atau layout pecah di tampilan mobile."></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 shadow-sm transition cursor-pointer">
                        Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Monaco Diff Editor CDN Script --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } });

    const originalContent = @json($theme->baseline_content ?? '');
    const modifiedContent = @json($theme->draft_content ?? '');
    const lang = '{{ $theme->file_key === "custom_css" ? "css" : "html" }}';

    require(['vs/editor/editor.main'], function () {
        const container = document.getElementById('monaco-diff-editor');
        if (!container) return;

        const originalModel = monaco.editor.createModel(originalContent, lang);
        const modifiedModel = monaco.editor.createModel(modifiedContent, lang);

        const diffEditor = monaco.editor.createDiffEditor(container, {
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 13,
            readOnly: true,
            lineNumbers: 'on',
            renderSideBySide: true,
            wordWrap: 'on'
        });

        diffEditor.setModel({
            original: originalModel,
            modified: modifiedModel
        });
    });
});
</script>
@endpush

