@extends('layouts.app')

@section('title', 'Theme Code Editor - ' . ($themeDetails['name'] ?? $currentPreset))

@push('styles')
{{-- Monaco Editor CDN Loader --}}
<style>
    .editor-container {
        height: 620px;
        border-radius: 1rem;
        overflow: hidden;
    }
    .file-tab-active {
        background: #007AFF15;
        color: #007AFF;
        border-color: #007AFF;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
     x-data="themeEditorApp({
         currentPreset: @json($currentPreset),
         activeFileKey: @json($activeFileKey),
         files: @json($files),
         csrfToken: '{{ csrf_token() }}',
         saveDraftUrl: '{{ route('landing-page.theme-editor.draft') }}',
         submitReviewUrl: '{{ route('landing-page.theme-editor.submit-review') }}',
         resetDefaultUrl: '{{ route('landing-page.theme-editor.reset-default') }}',
         previewBaseUrl: '{{ $previewUrl }}'
     })">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-black/5 dark:border-white/5">
        <div>
            <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-1">
                <a href="{{ route('landing-page.edit') }}" class="hover:text-neutral-900 dark:hover:text-white transition">Landing Page CMS</a>
                <span>/</span>
                <span class="font-medium text-neutral-900 dark:text-white">Theme Code Editor</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <i data-lucide="code-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-heading text-neutral-900 dark:text-white flex items-center gap-2">
                        <span>Editor Kode Tema:</span>
                        <span class="text-[#007AFF] font-mono">{{ $themeDetails['name'] ?? $currentPreset }}</span>
                    </h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Kustomisasi codingan tema toko Anda. Preview instan di sandbox; wajib ACC admin sebelum live ke production.
                    </p>
                </div>
            </div>
        </div>

        {{-- Top Right Actions --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Preview Sandbox Button --}}
            <a :href="previewBaseUrl" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-[#1C1C1E] text-neutral-800 dark:text-neutral-200 border border-black/10 dark:border-white/10 text-xs font-semibold hover:border-black/20 dark:hover:border-white/20 shadow-2xs transition">
                <i data-lucide="external-link" class="w-4 h-4 text-emerald-500"></i>
                <span>Buka Live Preview</span>
            </a>

            {{-- Reset Default Button --}}
            <button type="button" 
                    @click="resetActiveFileToDefault()"
                    :disabled="isSaving"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 text-xs font-semibold transition cursor-pointer disabled:opacity-50">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset ke Bawaan</span>
            </button>

            {{-- Save Draft Button --}}
            <button type="button" 
                    @click="saveCurrentDraft()"
                    :disabled="isSaving"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-neutral-900 hover:bg-black dark:bg-white dark:hover:bg-neutral-100 text-white dark:text-neutral-900 text-xs font-semibold shadow-xs transition cursor-pointer disabled:opacity-50">
                <i data-lucide="save" class="w-4 h-4" x-show="!isSaving"></i>
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSaving" x-cloak></i>
                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Draft Preview'"></span>
            </button>

            {{-- Submit for Admin Review Button --}}
            <button type="button" 
                    @click="submitForReview()"
                    :disabled="isSubmitting"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-semibold shadow-[0_4px_16px_rgba(0,122,255,0.25)] transition cursor-pointer disabled:opacity-50">
                <i data-lucide="send" class="w-4 h-4" x-show="!isSubmitting"></i>
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSubmitting" x-cloak></i>
                <span x-text="isSubmitting ? 'Mengirim...' : 'Ajukan Review ke Admin'"></span>
            </button>
        </div>
    </div>

    {{-- Notification / Notice Banner --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-900 dark:text-blue-200 text-xs flex items-start gap-3">
            <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-semibold block mb-0.5">Pilih Bawaan = Langsung Live</strong>
                Jika Anda hanya memilih tema resmi tanpa mengubah kode, tema langsung aktif untuk publik tanpa butuh acc admin.
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-3">
            <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-semibold block mb-0.5">Ubah Codingan = Wajib Review Admin</strong>
                Setiap custom kode pada Blade/HTML/CSS harus disetujui admin sebelum tayang di production demi keamanan & kestabilan.
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 text-xs flex items-start gap-3">
            <i data-lucide="sparkles" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-semibold block mb-0.5">Live Sandbox Preview Bebas Uji</strong>
                Anda dapat menguji draft codingan kapan pun di tautan preview khusus tanpa mengganggu pelanggan toko yang sedang aktif.
            </div>
        </div>
    </div>

    {{-- Main Workspace: Sidebar File List & Editor / Split Preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: File Selector & Status List (3 cols) --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="p-4 rounded-3xl bg-white dark:bg-[#18181B] border border-black/5 dark:border-white/5 shadow-2xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <span class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider">Berkas Komponen Tema</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 font-mono">
                        {{ count(App\Models\BusinessStorefrontCustomTheme::SUPPORTED_FILES) }} Files
                    </span>
                </div>

                <div class="space-y-1.5">
                    @foreach (App\Models\BusinessStorefrontCustomTheme::SUPPORTED_FILES as $fKey => $fMeta)
                        @php
                            $rec = $files[$fKey] ?? null;
                            $st = $rec?->status ?? 'default';
                        @endphp
                        <button type="button" 
                                @click="switchFile('{{ $fKey }}')"
                                :class="activeFileKey === '{{ $fKey }}' ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF] shadow-2xs' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-neutral-700 dark:text-neutral-300 border-transparent'"
                                class="w-full text-left p-3 rounded-2xl border transition-all flex items-center justify-between gap-2.5 cursor-pointer">
                            <div class="min-w-0 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                     :class="activeFileKey === '{{ $fKey }}' ? 'bg-[#007AFF] text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-500'">
                                    <i data-lucide="{{ $fKey === 'custom_css' ? 'file-code' : 'layout' }}" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold truncate">{{ $fMeta['label'] }}</div>
                                    <div class="text-[10.5px] font-mono text-neutral-400 truncate">{{ $fMeta['name'] }}</div>
                                </div>
                            </div>

                            {{-- Status Badge --}}
                            <div class="shrink-0">
                                <template x-if="getFileStatus('{{ $fKey }}') === 'published'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Live
                                    </span>
                                </template>
                                <template x-if="getFileStatus('{{ $fKey }}') === 'under_review'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                        Review
                                    </span>
                                </template>
                                <template x-if="getFileStatus('{{ $fKey }}') === 'draft'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        Draft
                                    </span>
                                </template>
                                <template x-if="getFileStatus('{{ $fKey }}') === 'rejected'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Revisi
                                    </span>
                                </template>
                                <template x-if="getFileStatus('{{ $fKey }}') === 'default'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-500">
                                        Official
                                    </span>
                                </template>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- File Details Card --}}
            <div class="p-4 rounded-3xl bg-white dark:bg-[#18181B] border border-black/5 dark:border-white/5 shadow-2xs space-y-3 text-xs">
                <div class="font-bold text-neutral-900 dark:text-white flex items-center justify-between">
                    <span>Detail Berkas Aktif</span>
                    <span class="font-mono text-[#007AFF]" x-text="activeMeta.name"></span>
                </div>
                <p class="text-neutral-500 dark:text-neutral-400 leading-relaxed" x-text="activeMeta.desc"></p>

                {{-- Status Card Detail --}}
                <div class="p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-black/5 dark:border-white/5 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-500">Status Saat Ini:</span>
                        <strong class="font-semibold uppercase tracking-wider text-[11px]"
                                :class="{
                                    'text-emerald-600': activeStatus === 'published',
                                    'text-amber-600': activeStatus === 'under_review',
                                    'text-blue-600': activeStatus === 'draft',
                                    'text-rose-600': activeStatus === 'rejected',
                                    'text-neutral-500': activeStatus === 'default'
                                }"
                                x-text="statusLabel(activeStatus)"></strong>
                    </div>
                    <template x-if="activeAdminNotes">
                        <div class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 text-[11px] text-rose-600 dark:text-rose-400">
                            <strong>Catatan Admin:</strong> <span x-text="activeAdminNotes"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Right: Monaco Code Editor & Live Preview Panel (9 cols) --}}
        <div class="lg:col-span-9 space-y-4">
            
            {{-- Editor Header Bar --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#18181B] border border-black/5 dark:border-white/5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="ml-2 font-mono text-xs font-semibold text-neutral-800 dark:text-neutral-200" x-text="activeMeta.name"></span>
                    <span class="text-[11px] text-neutral-400" x-text="'(' + (activeMeta.language || 'html').toUpperCase() + ')'"></span>
                </div>

                {{-- View Mode Tabs --}}
                <div class="flex items-center gap-1 p-1 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs">
                    <button type="button" 
                            @click="viewMode = 'editor'"
                            :class="viewMode === 'editor' ? 'bg-white dark:bg-[#1C1C1E] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                        Code Editor Saja
                    </button>
                    <button type="button" 
                            @click="viewMode = 'split'"
                            :class="viewMode === 'split' ? 'bg-white dark:bg-[#1C1C1E] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="columns-2" class="w-3.5 h-3.5"></i>
                        <span>Split Editor + Preview</span>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'preview'"
                            :class="viewMode === 'preview' ? 'bg-white dark:bg-[#1C1C1E] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Preview Saja</span>
                    </button>
                </div>
            </div>

            {{-- Workspace Body --}}
            <div class="grid gap-4" :class="viewMode === 'split' ? 'grid-cols-1 xl:grid-cols-2' : 'grid-cols-1'">
                
                {{-- Monaco Code Editor Container --}}
                <div x-show="viewMode === 'editor' || viewMode === 'split'" 
                     class="editor-container shadow-sm border border-black/10 dark:border-white/10 bg-[#1E1E1E]">
                    <div id="monaco-code-editor" class="w-full h-full"></div>
                </div>

                {{-- Live Iframe Sandbox Preview --}}
                <div x-show="viewMode === 'preview' || viewMode === 'split'" 
                     class="editor-container shadow-sm border border-black/10 dark:border-white/10 bg-white dark:bg-[#14100E] flex flex-col">
                    <div class="p-2.5 bg-neutral-100 dark:bg-neutral-800/80 border-b border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                            <i data-lucide="monitor" class="w-4 h-4"></i>
                            <span class="font-medium">Sandbox Live Preview</span>
                        </div>
                        <button type="button" 
                                @click="refreshPreviewFrame()"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 transition cursor-pointer text-[11px]">
                            <i data-lucide="refresh-cw" class="w-3 h-3" :class="isFrameRefreshing ? 'animate-spin' : ''"></i>
                            <span>Reload</span>
                        </button>
                    </div>
                    <iframe id="preview-sandbox-frame" 
                            :src="previewBaseUrl" 
                            class="w-full flex-1 border-0"
                            title="Theme Preview Sandbox"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Monaco Editor CDN Loader Script --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('themeEditorApp', (config) => ({
        currentPreset: config.currentPreset,
        activeFileKey: config.activeFileKey,
        files: config.files,
        saveDraftUrl: config.saveDraftUrl,
        submitReviewUrl: config.submitReviewUrl,
        resetDefaultUrl: config.resetDefaultUrl,
        previewBaseUrl: config.previewBaseUrl,
        csrfToken: config.csrfToken,

        viewMode: 'split', // 'editor', 'split', 'preview'
        editor: null,
        isSaving: false,
        isSubmitting: false,
        isFrameRefreshing: false,

        supportedMeta: @json(App\Models\BusinessStorefrontCustomTheme::SUPPORTED_FILES),

        get activeMeta() {
            return this.supportedMeta[this.activeFileKey] || { name: 'file', label: 'File', desc: '' };
        },

        get activeFileRecord() {
            return this.files[this.activeFileKey] || {};
        },

        get activeStatus() {
            return this.activeFileRecord.status || 'default';
        },

        get activeAdminNotes() {
            return this.activeFileRecord.admin_review_notes || '';
        },

        init() {
            this.initMonaco();
        },

        initMonaco() {
            require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } });
            
            const self = this;
            require(['vs/editor/editor.main'], function () {
                const container = document.getElementById('monaco-code-editor');
                if (!container) return;

                const initialContent = self.activeFileRecord.draft_content || self.activeFileRecord.baseline_content || '';
                const lang = self.activeFileKey === 'custom_css' ? 'css' : 'html';

                self.editor = monaco.editor.create(container, {
                    value: initialContent,
                    language: lang,
                    theme: 'vs-dark',
                    automaticLayout: true,
                    fontSize: 13,
                    tabSize: 4,
                    lineNumbers: 'on',
                    minimap: { enabled: false },
                    scrollBeyondLastLine: false,
                    wordWrap: 'on',
                });

                // Auto save shortcut Ctrl+S / Cmd+S
                self.editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function () {
                    self.saveCurrentDraft();
                });
            });
        },

        switchFile(fileKey) {
            if (this.activeFileKey === fileKey) return;

            // Prompt save if modified
            const currentContent = this.editor ? this.editor.getValue() : '';
            this.activeFileKey = fileKey;

            const newRecord = this.files[fileKey] || {};
            const content = newRecord.draft_content || newRecord.baseline_content || '';
            const lang = fileKey === 'custom_css' ? 'css' : 'html';

            if (this.editor) {
                const model = this.editor.getModel();
                if (model) {
                    monaco.editor.setModelLanguage(model, lang);
                    this.editor.setValue(content);
                }
            }
        },

        getFileStatus(fileKey) {
            return this.files[fileKey]?.status || 'default';
        },

        statusLabel(status) {
            switch(status) {
                case 'published': return 'Live Production';
                case 'under_review': return 'Menunggu Review Admin';
                case 'draft': return 'Draft Preview';
                case 'rejected': return 'Revisi Diperlukan';
                default: return 'Tema Resmi Bawaan';
            }
        },

        async saveCurrentDraft() {
            if (!this.editor || this.isSaving) return;
            this.isSaving = true;

            const content = this.editor.getValue();

            try {
                const res = await fetch(this.saveDraftUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        file_key: this.activeFileKey,
                        content: content
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (!this.files[this.activeFileKey]) this.files[this.activeFileKey] = {};
                    this.files[this.activeFileKey].draft_content = content;
                    this.files[this.activeFileKey].status = data.status;
                    this.files[this.activeFileKey].has_custom_modifications = data.has_modifications;

                    this.refreshPreviewFrame();

                    Swal.fire({
                        icon: 'success',
                        title: 'Draft Tersimpan',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    throw new Error(data.message || 'Gagal menyimpan draft');
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message || 'Terjadi kesalahan sistem.'
                });
            } finally {
                this.isSaving = false;
            }
        },

        async submitForReview() {
            if (this.isSubmitting) return;

            const confirm = await Swal.fire({
                title: 'Ajukan Review ke Admin?',
                text: 'Codingan custom tema yang Anda ubah akan ditinjau oleh Admin Superadmin. Setelah disetujui, kode akan langsung tayang di live production.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007AFF',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Ajukan Review',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.isSubmitting = true;

            try {
                // Ensure current draft is saved first
                if (this.editor) {
                    await this.saveCurrentDraft();
                }

                const res = await fetch(this.submitReviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    // Update statuses
                    Object.keys(this.files).forEach(k => {
                        if (this.files[k].has_custom_modifications && this.files[k].status !== 'published') {
                            this.files[k].status = 'under_review';
                        }
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diajukan',
                        text: data.message,
                        confirmButtonColor: '#007AFF'
                    });
                } else {
                    throw new Error(data.message || 'Gagal mengajukan review.');
                }
            } catch (err) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: err.message || 'Gagal mengajukan review.'
                });
            } finally {
                this.isSubmitting = false;
            }
        },

        async resetActiveFileToDefault() {
            const confirm = await Swal.fire({
                title: 'Reset ke Bawaan Resmi?',
                text: 'Codingan kustom pada file ini akan dikembalikan persis seperti template resmi asli.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Reset Sekarang',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(this.resetDefaultUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ file_key: this.activeFileKey })
                });

                const data = await res.json();
                if (data.success) {
                    if (this.editor && data.baseline_content) {
                        this.editor.setValue(data.baseline_content);
                    }
                    if (this.files[this.activeFileKey]) {
                        this.files[this.activeFileKey].status = 'default';
                        this.files[this.activeFileKey].has_custom_modifications = false;
                        this.files[this.activeFileKey].draft_content = data.baseline_content;
                    }

                    this.refreshPreviewFrame();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Direset',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message || 'Gagal mereset berkas.'
                });
            }
        },

        refreshPreviewFrame() {
            const frame = document.getElementById('preview-sandbox-frame');
            if (frame) {
                this.isFrameRefreshing = true;
                frame.src = this.previewBaseUrl + '&t=' + Date.now();
                setTimeout(() => { this.isFrameRefreshing = false; }, 800);
            }
        }
    }));
});
</script>
@endpush

