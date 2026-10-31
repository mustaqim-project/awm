@extends('layouts.admin', [
    'title' => __('admin.sumber_inspirasi_lisensi_admin_console'),
    'headerTitle' => 'Sumber Inspirasi Visual & Kebijakan Lisensi',
    'headerSubtitle' => 'Kelola provider feed inspirasi, kredensial API terenkripsi, kuota rate limit, dan kepatuhan hak cipta komersial',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminAiStudioSources()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                </span>
                <span>Konektor Sumber Inspirasi Komersial</span>
            </h1>
            <p class="text-xs text-black/60 dark:text-white/60">
                Setiap item inspirasi wajib diverifikasi lisensi komersialnya sebelum dapat digunakan oleh merchant.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="syncAllSources()" :disabled="syncingAll"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] text-xs font-bold transition-all disabled:opacity-50">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': syncingAll }"></i>
                <span x-text="syncingAll ? 'Menyinkronkan...' : 'Sinkronkan Semua Sumber'"></span>
            </button>
            <a href="{{ route('admin.ai-studio.pricing.index') }}"
                class="px-4 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all">
                <i data-lucide="coins" class="w-3.5 h-3.5 inline mr-1 text-[#FF9500]"></i>
                <span>{{ __('admin.matriks_harga_ledger') }}</span>
            </a>
            <button type="button" @click="openCreateSourceModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Sumber Baru</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-xs font-semibold text-[#34C759] flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="p-4 rounded-[14px] bg-red-500/10 border border-red-500/20 text-xs text-red-600 dark:text-red-400 space-y-1">
            <div class="font-bold">{{ __('admin.terjadi_kesalahan_validasi') }}</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Sources Grid (Bento Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($sources as $source)
            @php
                $policy = $source->policy;
                $hasActiveCreds = $source->credentials->where('status', 'active')->isNotEmpty();
            @endphp
            <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 flex flex-col justify-between space-y-4 shadow-sm hover:shadow-md transition-shadow">
                <!-- Top Card Info -->
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-black dark:text-white">{{ $source->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    {{ $source->code }}
                                </span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[220px]" title="{{ $source->driver_class }}">
                                {{ class_basename($source->driver_class) }}
                            </p>
                        </div>

                        <!-- Status Toggle Form -->
                        <form action="{{ route('admin.ai-studio.sources.toggle', $source->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all {{ $source->is_active ? 'bg-[#34C759]/15 text-[#34C759] hover:bg-[#34C759]/25' : 'bg-black/5 text-black/40 hover:bg-black/10' }}"
                                title="{{ __('admin.klik_untuk_mengubah_status') }}">
                                {{ $source->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </div>

                    <!-- Metrics & Details -->
                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                        <div class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] space-y-0.5">
                            <span class="text-[10px] text-black/50 dark:text-white/50">Item Terindeks</span>
                            <p class="font-bold text-black dark:text-white">{{ number_format($source->items_count) }} foto</p>
                        </div>
                        <div class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] space-y-0.5">
                            <span class="text-[10px] text-black/50 dark:text-white/50">Rate Limit</span>
                            <p class="font-bold text-black dark:text-white">{{ $source->rate_limit_per_minute }}/menit</p>
                        </div>
                    </div>

                    <!-- Credential Status (AC-009) -->
                    <div class="p-2.5 rounded-[10px] border flex items-center justify-between text-xs {{ $hasActiveCreds ? 'bg-[#34C759]/5 border-[#34C759]/20 text-[#34C759]' : 'bg-amber-500/5 border-amber-500/20 text-amber-600' }}">
                        <span class="flex items-center gap-1.5 font-semibold text-[11px]">
                            <i data-lucide="{{ $hasActiveCreds ? 'key' : 'alert-circle' }}" class="w-3.5 h-3.5"></i>
                            <span>{{ $hasActiveCreds ? 'Kredensial Terenkripsi Aktif' : 'Kredensial Belum Dikonfigurasi' }}</span>
                        </span>
                        <button type="button" @click="openCredentialsModal($el.dataset.id, $el.dataset.name)"
                            data-id="{{ $source->id }}"
                            data-name="{{ $source->name }}"
                            class="text-[11px] font-bold underline hover:opacity-80">
                            {{ $hasActiveCreds ? 'Ubah' : 'Pasang' }}
                        </button>
                    </div>

                    <!-- Rights Policy Summary -->
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] space-y-1.5 text-[11px]">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-black/70 dark:text-white/70">Kebijakan Lisensi:</span>
                            <button type="button" @click="openPolicyModal($el.dataset.id, $el.dataset.name, $el.dataset.commercial === '1', $el.dataset.attrib === '1', parseInt($el.dataset.retention), $el.dataset.disclaimer)"
                                data-id="{{ $source->id }}"
                                data-name="{{ $source->name }}"
                                data-commercial="{{ $policy?->allow_commercial_use ? '1' : '0' }}"
                                data-attrib="{{ $policy?->require_attribution ? '1' : '0' }}"
                                data-retention="{{ $policy?->retention_days ?? 90 }}"
                                data-disclaimer="{{ $policy?->disclaimer_template ?? '' }}"
                                class="text-[#007AFF] font-semibold hover:underline">
                                Atur
                            </button>
                        </div>
                        <div class="space-y-1 text-black/60 dark:text-white/60">
                            <div class="flex items-center justify-between">
                                <span>Izin Komersial:</span>
                                <span class="font-bold {{ $policy?->allow_commercial_use ? 'text-[#34C759]' : 'text-red-500' }}">
                                    {{ $policy?->allow_commercial_use ? 'Diizinkan' : 'Dilarang' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Kewajiban Atribusi:</span>
                                <span>{{ $policy?->require_attribution ? 'Wajib' : 'Opsional' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Retensi Cache:</span>
                                <span>{{ $policy?->retention_days ?? 90 }} hari</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Card Action -->
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs gap-2">
                    <span class="text-[10px] text-black/40 dark:text-white/40 truncate" id="sync-time-{{ $source->id }}">
                        Sinkron: {{ $source->last_synced_at ? $source->last_synced_at->diffForHumans() : 'Belum pernah' }}
                    </span>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" @click="syncSingleSource($el.dataset.id, $el.dataset.name)"
                            data-id="{{ $source->id }}"
                            data-name="{{ $source->name }}"
                            :disabled="syncingSourceId === '{{ $source->id }}'"
                            class="px-2.5 py-1 rounded-[8px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[11px] font-bold text-[#34C759] transition-all disabled:opacity-50 flex items-center gap-1"
                            title="{{ __('admin.tarik_konten_visual_prompt_terbaru_dari') }}">
                            <i data-lucide="refresh-cw" class="w-3 h-3" :class="{ 'animate-spin': syncingSourceId === '{{ $source->id }}' }"></i>
                            <span :x-text="syncingSourceId === '{{ $source->id }}' ? @json(__('admin.menyinkronkan')) : @json(__('admin.sinkronkan'))"></span>
                        </button>
                        <button type="button" @click="openEditSourceModal($el.dataset.id, $el.dataset.name, $el.dataset.driver, parseInt($el.dataset.freq), parseInt($el.dataset.limit))"
                            data-id="{{ $source->id }}"
                            data-name="{{ $source->name }}"
                            data-driver="{{ $source->driver_class }}"
                            data-freq="{{ $source->fetch_frequency_minutes }}"
                            data-limit="{{ $source->rate_limit_per_minute }}"
                            class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11px] font-semibold text-black/70 dark:text-white/70 transition-all">
                            {{ __('common.edit') }}
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: KREDENSIAL API (AC-009 MASKED)    -->
    <!-- ========================================== -->
    <div x-show="credentialsModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="credentialsModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="key" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">{{ __('admin.kelola_kredensial_api') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50" x-text="targetSourceName"></p>
                    </div>
                </div>
                <button type="button" @click="credentialsModalOpen = false" class="p-1 text-black/40 hover:bg-black/5 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- AC-009 Notice -->
            <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/60 dark:text-white/60 space-y-1">
                <div class="font-bold text-black dark:text-white flex items-center gap-1">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-[#34C759]"></i>
                    <span>Protokol Keamanan AC-009</span>
                </div>
                <p>Kunci rahasia API akan dienkripsi secara simetris (AES-256-CBC) dan tidak akan pernah ditampilkan kembali dalam bentuk teks mentah.</p>
            </div>

            <form :action="credentialsActionUrl" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">API Key / Client ID <span class="text-red-500">*</span></label>
                    <input type="password" name="api_key" required autocomplete="new-password"
                        placeholder="••••••••••••••••••••••••••••••••"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">API Secret (Opsional)</label>
                    <input type="password" name="api_secret" autocomplete="new-password"
                        placeholder="••••••••••••••••••••••••••••••••"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5">
                    <button type="button" @click="credentialsModalOpen = false"
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-sm transition-all">{{ __('admin.simpan_kredensial_terenkripsi') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: KEBIJAKAN LISENSI (POLICY)       -->
    <!-- ========================================== -->
    <div x-show="policyModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="policyModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">Kebijakan Lisensi Komersial</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50" x-text="targetSourceName"></p>
                    </div>
                </div>
                <button type="button" @click="policyModalOpen = false" class="p-1 text-black/40 hover:bg-black/5 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="policyActionUrl" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-semibold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="allow_commercial_use" value="1" x-model="policyAllowCommercial"
                            class="rounded border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                        <span>Izinkan Penggunaan Komersial bagi Tenant (AC-002 Gate)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="require_attribution" value="1" x-model="policyRequireAttribution"
                            class="rounded border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                        <span>Wajibkan Penyertaan Lembar Atribusi</span>
                    </label>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Masa Retensi Cache (Hari)</label>
                    <input type="number" name="retention_days" x-model="policyRetentionDays" min="1" max="365"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Template Disclaimer</label>
                    <textarea name="disclaimer_template" x-model="policyDisclaimer" rows="2"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5">
                    <button type="button" @click="policyModalOpen = false"
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-sm transition-all">
                        Perbarui Kebijakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: TAMBAH SUMBER BARU               -->
    <!-- ========================================== -->
    <div x-show="createSourceModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="createSourceModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-black dark:text-white">Tambah Sumber Inspirasi Baru</h3>
                <button type="button" @click="createSourceModalOpen = false" class="p-1 text-black/40 hover:bg-black/5 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.ai-studio.sources.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Kode Unik Sumber <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="pexels / unsplash / custom_rss"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Nama Tampilan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Pexels Commercial Feed"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Driver Class FQCN <span class="text-red-500">*</span></label>
                    <input type="text" name="driver_class" required placeholder="App\Domain\Ai\Inspiration\Drivers\PexelsDriver"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-black dark:text-white">Frekuensi Sync (Mnt)</label>
                        <input type="number" name="fetch_frequency_minutes" value="60" min="5"
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-black dark:text-white">Rate Limit (/Mnt)</label>
                        <input type="number" name="rate_limit_per_minute" value="30" min="1"
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5">
                    <button type="button" @click="createSourceModalOpen = false"
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-sm transition-all">
                        Daftarkan Sumber
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adminAiStudioSources() {
    return {
        credentialsModalOpen: false,
        credentialsActionUrl: '',
        policyModalOpen: false,
        policyActionUrl: '',
        createSourceModalOpen: false,
        targetSourceName: '',
        syncingAll: false,
        syncingSourceId: null,

        // Policy form bindings
        policyAllowCommercial: true,
        policyRequireAttribution: false,
        policyRetentionDays: 90,
        policyDisclaimer: '',

        async syncSingleSource(sourceId, name) {
            this.syncingSourceId = sourceId;
            try {
                const response = await fetch('{{ url("admin/ai-studio/sources") }}/' + sourceId + '/sync', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ limit: 24 })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyinkronkan sumber.');
                }

                alert('✓ ' + data.message);
                window.location.reload();
            } catch (err) {
                alert('✗ ' + err.message);
            } finally {
                this.syncingSourceId = null;
            }
        },

        async syncAllSources() {
            if (!confirm('Mulai sinkronisasi seluruh katalog visual dari CopasPrompt Indonesia & Civitai Open AI?')) {
                return;
            }

            this.syncingAll = true;
            try {
                const response = await fetch('{{ route("admin.ai-studio.sources.sync-all") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ limit: 24 })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyinkronkan semua sumber.');
                }

                alert('✓ ' + data.message);
                window.location.reload();
            } catch (err) {
                alert('✗ ' + err.message);
            } finally {
                this.syncingAll = false;
            }
        },

        openCredentialsModal(sourceId, name) {
            this.targetSourceName = name;
            this.credentialsActionUrl = '{{ url("admin/ai-studio/sources") }}/' + sourceId + '/credentials';
            this.credentialsModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        openPolicyModal(sourceId, name, allowComm, reqAttr, retention, disclaimer) {
            this.targetSourceName = name;
            this.policyActionUrl = '{{ url("admin/ai-studio/sources") }}/' + sourceId + '/policy';
            this.policyAllowCommercial = allowComm;
            this.policyRequireAttribution = reqAttr;
            this.policyRetentionDays = retention;
            this.policyDisclaimer = disclaimer;
            this.policyModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        openCreateSourceModal() {
            this.createSourceModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        openEditSourceModal(sourceId, name, driver, freq, limit) {
            // For parameter edit
            alert('Mengedit sumber: ' + name);
        },

        init() {
            lucide.createIcons();
        }
    };
}
</script>
@endpush
@endsection
