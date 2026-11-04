@extends('layouts.admin', [
    'title' => 'Provider API & Kredensial Inspirasi - Admin Console',
    'headerTitle' => 'Provider API & Kredensial Terenkripsi',
    'headerSubtitle' => 'Kelola konektivitas API resmi (Pexels, Pixabay, Unsplash, Pinterest, OpenAI & Grok), batas rate limit, dan kepatuhan lisensi',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminInspirationProviders()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="plug-2" class="w-4 h-4"></i>
                    </span>
                    <span>Status Adapter &amp; Kredensial Resmi</span>
                </h1>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Kunci API disimpan terenkripsi di server (AES-256). Kepatuhan API terms diawasi ketat tanpa scraping.
                </p>
            </div>
            <a href="{{ route('admin.ai-studio.inspiration.index') }}"
                class="px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Katalog</span>
            </a>
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
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Katalog &amp; Moderasi</span>
            </a>
            <a href="{{ route('admin.ai-studio.inspiration.providers') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-bold transition-all bg-[#007AFF] text-white shadow-xs">
                <i data-lucide="plug" class="w-3.5 h-3.5"></i>
                <span>Provider API &amp; Kredensial</span>
            </a>
        </div>
    </div>

    <!-- Provider Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($providers as $key => $p)
            <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 flex flex-col justify-between shadow-xs">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center font-bold text-xs uppercase text-[#007AFF]">
                                {{ substr($key, 0, 2) }}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-black dark:text-white">{{ $p['name'] }}</h3>
                                <div class="text-[10px] text-black/50 dark:text-white/50">Provider ID: {{ $key }}</div>
                            </div>
                        </div>

                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p['configured'] ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-amber-500/10 text-amber-500' }}">
                            {{ $p['configured'] ? 'Terkonfigurasi' : 'Belum Dikonfigurasi' }}
                        </span>
                    </div>

                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] space-y-1.5 text-xs text-black/70 dark:text-white/70">
                        <div class="flex items-center justify-between">
                            <span class="text-black/40 dark:text-white/40">Status Lisensi:</span>
                            <span class="font-semibold text-black dark:text-white">{{ $p['license_policy'] ?? 'Komersial Resmi' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/40 dark:text-white/40">Kewajiban Atribusi:</span>
                            <span class="font-semibold text-black dark:text-white">{{ $p['attribution_required'] ? 'Wajib Diberikan' : 'Opsional' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/40 dark:text-white/40">Kebijakan Cache:</span>
                            <span class="font-semibold text-black dark:text-white">{{ $p['cache_policy'] ?? 'Default' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/40 dark:text-white/40">Rate Limit:</span>
                            <span class="font-semibold text-black dark:text-white">{{ $p['rate_limit'] ?? '60 req/min' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                    <button type="button" @click="testProvider('{{ $key }}')"
                        class="px-3 py-1.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                        <i data-lucide="activity" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Uji Koneksi</span>
                    </button>

                    <button type="button" @click="openConfigModal('{{ $key }}', '{{ $p['name'] }}')"
                        class="px-3.5 py-1.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all flex items-center gap-1">
                        <i data-lucide="key" class="w-3 h-3"></i>
                        <span>Atur Kunci</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Log Riwayat Sinkronisasi Terkini -->
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-3">
        <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
            <span>Log Riwayat Sinkronisasi Provider Eksternal</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-2.5 px-3">Provider</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Item Diimpor</th>
                        <th class="py-2.5 px-3">Durasi</th>
                        <th class="py-2.5 px-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($recentSyncs ?? [] as $log)
                        <tr>
                            <td class="py-2.5 px-3 font-bold uppercase">{{ $log->provider }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'success' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-red-500/10 text-red-500' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">{{ $log->items_imported ?? 0 }} item</td>
                            <td class="py-2.5 px-3">{{ $log->duration_ms ?? 0 }} ms</td>
                            <td class="py-2.5 px-3 text-black/50 dark:text-white/50">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-black/40 dark:text-white/40">Belum ada aktivitas sinkronisasi tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL KONFIGURASI KUNCI API -->
    <div x-show="configModalOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="configModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Atur Kredensial: <span x-text="targetProviderName"></span></span>
                </h3>
                <button type="button" @click="configModalOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="`{{ url('admin/ai-studio/inspiration/providers') }}/${targetProviderKey}/credentials`" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">API Key / Client ID / Token</label>
                    <input type="password" name="api_key" placeholder="Masukkan API Key resmi..." required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">Disimpan aman terenkripsi di database menggunakan App Key AES-256.</p>
                </div>

                <div x-show="targetProviderKey === 'pinterest'">
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Pinterest App Secret (Opsional)</label>
                    <input type="password" name="api_secret" placeholder="App Secret jika menggunakan OAuth..."
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="configModalOpen = false"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs">
                        Simpan Kredensial
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adminInspirationProviders() {
    return {
        configModalOpen: false,
        targetProviderKey: '',
        targetProviderName: '',

        openConfigModal(key, name) {
            this.targetProviderKey = key;
            this.targetProviderName = name;
            this.configModalOpen = true;
        },

        async testProvider(providerKey) {
            try {
                const res = await fetch(`{{ url('admin/ai-studio/inspiration/providers') }}/${providerKey}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(`✅ Koneksi Berhasil!\n\nStatus: Terhubung\nResponse: ${data.message || 'OK'}`);
                } else {
                    alert(`⚠️ Koneksi Gagal!\n\nAlasan: ${data.message || 'Error tidak diketahui'}`);
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menghubungi server');
            }
        }
    };
}
</script>
@endpush
@endsection

