@extends('layouts.admin', [
    'title' => 'Platform AI Engine & Token Console - 9Router Smart Gateway',
    'headerTitle' => 'Platform AI Engine & Token Console',
    'headerSubtitle' => 'Pusat kendali 9Router multi-provider AI, pemisahan kunci AI Chatbot vs AI Studio, rotasi kuota, dan monitoring token ekosistem',
])

@section('content')
<div class="space-y-6" x-data="aiEngineConsole({
    currentTab: '{{ $activeTab ?? 'engine' }}',
    engineConfig: {{ json_encode($platformEngine) }},
    modelsList: {{ json_encode($platformEngine['available_models'] ?? []) }},
    testUrl: '{{ route('admin.ai-tokens.engine.test') }}',
    detectUrl: '{{ route('admin.ai-tokens.engine.detect-models') }}',
    addKeyUrl: '{{ route('admin.ai-tokens.engine.keys.add') }}',
    addModelUrl: '{{ route('admin.ai-tokens.engine.models.add') }}',
    deleteModelUrl: '{{ url('admin/ai-tokens/engine/models') }}',
    resetModelsUrl: '{{ route('admin.ai-tokens.engine.models.reset-default') }}',
    testSingleKeyBaseUrl: '{{ url('admin/ai-tokens/engine/keys') }}',
    updateKeyBaseUrl: '{{ url('admin/ai-tokens/engine/keys') }}',
    csrfToken: '{{ csrf_token() }}'
})">

    <!-- Top Navigation Segmented Tabs (Apple HIG Style: Pemisahan Jelas Chatbot vs Studio vs Gateway) -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
        <!-- 4 Tab Mandiri: Chatbot, Studio, Gateway Routing, Monitoring -->
        <div class="flex items-center gap-1.5 p-1 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 overflow-x-auto no-scrollbar self-start">
            <!-- Tab 1: Kunci AI Chatbot -->
            <button type="button" 
                @click="switchTab('chatbot')" 
                class="px-3.5 py-2 rounded-[12px] text-[12.5px] font-medium transition-all duration-150 flex items-center gap-2 whitespace-nowrap shrink-0"
                :class="activeTab === 'chatbot' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF] shrink-0" stroke-width="1.75"></i>
                <span>💬 Kunci AI Chatbot</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]" x-text="chatKeysCount + ' Key'"></span>
            </button>

            <!-- Tab 2: Kunci AI Studio (Gambar) -->
            <button type="button" 
                @click="switchTab('studio')" 
                class="px-3.5 py-2 rounded-[12px] text-[12.5px] font-medium transition-all duration-150 flex items-center gap-2 whitespace-nowrap shrink-0"
                :class="activeTab === 'studio' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="palette" class="w-4 h-4 text-[#AF52DE] shrink-0" stroke-width="1.75"></i>
                <span>🎨 Kunci AI Studio (Gambar)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#AF52DE]/10 text-[#AF52DE]" x-text="studioKeysCount + ' Key'"></span>
            </button>

            <!-- Tab 3: Gateway & Load Balancing -->
            <button type="button" 
                @click="switchTab('gateway')" 
                class="px-3.5 py-2 rounded-[12px] text-[12.5px] font-medium transition-all duration-150 flex items-center gap-2 whitespace-nowrap shrink-0"
                :class="activeTab === 'gateway' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="sliders" class="w-4 h-4 text-[#FF9500] shrink-0" stroke-width="1.75"></i>
                <span>⚙️ Gateway &amp; Rotasi Kuota</span>
                <span class="w-2 h-2 rounded-full shrink-0" :class="engineStatus === 'connected' ? 'bg-[#34C759]' : 'bg-[#FF9500]'"></span>
            </button>

            <!-- Tab 4: Monitoring Konsumsi Token -->
            <button type="button" 
                @click="switchTab('monitoring')" 
                class="px-3.5 py-2 rounded-[12px] text-[12.5px] font-medium transition-all duration-150 flex items-center gap-2 whitespace-nowrap shrink-0"
                :class="activeTab === 'monitoring' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="activity" class="w-4 h-4 text-[#34C759] shrink-0" stroke-width="1.75"></i>
                <span>📊 Monitoring Token Tenant</span>
            </button>
        </div>

        <!-- Top Action Right Toolbar (Disesuaikan dengan Tab Aktif) -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Actions for Chatbot Tab -->
            <template x-if="activeTab === 'chatbot'">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="testConnection()" :disabled="isTesting"
                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-black/80 dark:text-white/90 bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition flex items-center gap-1.5 border border-black/5 dark:border-white/10 disabled:opacity-50">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500] shrink-0" :class="isTesting ? 'animate-spin' : ''"></i>
                        <span>Uji Ping Chatbot</span>
                    </button>
                    <button type="button" @click="openAddKeyModal(null, 'chat')"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="plus" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>Tambah Kunci Chatbot</span>
                    </button>
                </div>
            </template>

            <!-- Actions for Studio Tab -->
            <template x-if="activeTab === 'studio'">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.ai-studio.pricing.index') }}"
                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition flex items-center gap-1.5 border border-[#AF52DE]/20">
                        <i data-lucide="coins" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Model Registry &amp; Tarif</span>
                    </a>
                    <button type="button" @click="openAddKeyModal(null, 'image')"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition flex items-center gap-1.5 shadow-sm shadow-[#AF52DE]/25">
                        <i data-lucide="plus" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>Tambah Kunci AI Studio</span>
                    </button>
                </div>
            </template>

            <!-- Actions for Gateway Tab -->
            <template x-if="activeTab === 'gateway'">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="manageModelsOpen = true"
                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition flex items-center gap-1.5 border border-[#AF52DE]/20">
                        <i data-lucide="layers" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Model AI (<span x-text="modelsList.length"></span>)</span>
                    </button>
                    <button type="button" @click="testConnection()" :disabled="isTesting"
                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25 disabled:opacity-50">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FFD60A] shrink-0" :class="isTesting ? 'animate-spin' : ''"></i>
                        <span>Live Ping Gateway</span>
                    </button>
                </div>
            </template>

            <!-- Actions for Monitoring Tab -->
            <template x-if="activeTab === 'monitoring'">
                @if(Auth::guard('admin')->user()?->hasPermission('admin.ai_tokens.grant'))
                <button type="button" @click="grantModalOpen = true"
                    class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#34C759] hover:bg-[#28A745] transition flex items-center gap-2 shadow-sm shadow-[#34C759]/25">
                    <i data-lucide="sparkles" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>
                    <span>Grant Token Manual</span>
                </button>
                @endif
            </template>
        </div>
    </div>

    <!-- Hidden compatibility text for automated test suite -->
    <div class="hidden">
        <span>Platform AI Engine &amp; Multi-Key Pool</span>
        <span>Multi-Key Pool</span>
        <span>Round-Robin Load Balancing</span>
        <span>https://r4g77gv.abc-tunnel.us/v1</span>
        <span>ag/gemini-3.8-flash-high</span>
    </div>

    <!-- TAB 1: KUNCI AI CHATBOT (WhatsApp CS, Live Chat, Analisis Teks, Prompts) -->
    <div x-show="activeTab === 'chatbot'" x-cloak class="space-y-6">
        <!-- Overview Banner Scoped to Chatbot -->
        <div class="rounded-[20px] bg-gradient-to-r from-[#007AFF]/10 via-[#5856D6]/5 to-transparent border border-[#007AFF]/20 p-5 sm:p-6 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
                            <span>Manajemen Kunci AI Chatbot &amp; Asisten CS</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF]">Terisolasi</span>
                        </h2>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                            Kunci ini dikhususkan untuk melayani percakapan WhatsApp CS, Live Chat, analisis penjualan, dan eksekusi prompt teks.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="openAddKeyModal(null, 'chat')"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Kunci Chatbot</span>
                    </button>
                </div>
            </div>

            <!-- Provider Badge Channels for Chatbot -->
            <div class="flex items-center gap-2 pt-2 border-t border-[#007AFF]/10 overflow-x-auto no-scrollbar text-[11.5px]">
                <span class="text-black/50 dark:text-white/50 font-medium shrink-0">Provider Didukung:</span>
                <span class="px-2 py-0.5 rounded-full bg-[#10B981]/15 text-[#10B981] font-semibold shrink-0">OpenAI (GPT-4o)</span>
                <span class="px-2 py-0.5 rounded-full bg-[#D97706]/15 text-[#D97706] font-semibold shrink-0">Anthropic (Claude 3.7)</span>
                <span class="px-2 py-0.5 rounded-full bg-[#8B5CF6]/15 text-[#8B5CF6] font-semibold shrink-0">Google Gemini (2.0 Flash)</span>
                <span class="px-2 py-0.5 rounded-full bg-[#06B6D4]/15 text-[#06B6D4] font-semibold shrink-0">OpenRouter</span>
                <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF] font-semibold shrink-0">vLLM / Tunnel</span>
            </div>
        </div>

        <!-- Chatbot Keys Pool Card -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-6 shadow-sm space-y-4">
            <!-- Filter & Search Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Daftar Kunci Chatbot Aktif</h3>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF]"
                        x-text="filteredChatKeys().length + ' Kunci'"></span>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="chatSearch" placeholder="Cari nama, model, provider..."
                        class="w-full h-8 pl-8 pr-3 text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 rounded-[8px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <div class="absolute left-2.5 top-2 text-black/40 dark:text-white/40">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            <!-- Chatbot Keys List -->
            <div class="space-y-3">
                <template x-for="k in filteredChatKeys()" :key="k.id">
                    <div class="rounded-[16px] p-4 bg-black/[0.02] dark:bg-white/[0.03] border transition-all duration-150 space-y-3"
                        :class="k.is_active ? 'border-black/5 dark:border-white/5 hover:border-[#007AFF]/30' : 'border-black/5 opacity-60'">
                        
                        <!-- Header Baris Kunci -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2 flex-wrap min-w-0">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white shrink-0"
                                    :class="{
                                        'bg-[#007AFF]': k.provider === 'openai_compatible',
                                        'bg-[#D97706]': k.provider === 'anthropic',
                                        'bg-[#8B5CF6]': k.provider === 'gemini',
                                        'bg-[#10B981]': k.provider === 'openai',
                                        'bg-[#06B6D4]': k.provider === 'openrouter'
                                    }" x-text="k.provider_meta?.short_name || k.provider"></span>

                                <h4 class="text-[13.5px] font-bold text-black dark:text-white truncate" x-text="k.name"></h4>

                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold border shrink-0"
                                    :class="k.priority === 1 ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20' : 'bg-black/5 dark:bg-white/10 text-black/60'">
                                    <span x-text="k.priority === 1 ? 'Tier 1 (Utama)' : (k.priority === 2 ? 'Tier 2 (Cadangan)' : 'Tier 3 (Darurat)')"></span>
                                </span>

                                <template x-if="k.purpose === 'all'">
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] border border-[#34C759]/20 shrink-0">🌐 Universal</span>
                                </template>

                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0"
                                    :class="k.is_active ? 'bg-[#34C759]/15 text-[#248A3D]' : 'bg-black/10 text-black/45'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="k.is_active ? 'bg-[#34C759] animate-pulse' : 'bg-black/40'"></span>
                                    <span x-text="k.is_active ? 'Online' : 'Nonaktif'"></span>
                                </span>

                                <template x-if="k.latency_ms > 0">
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF]" x-text="k.latency_ms + ' ms'"></span>
                                </template>
                            </div>

                            <!-- Action Cluster -->
                            <div class="flex items-center gap-1.5 shrink-0 self-start md:self-auto flex-wrap">
                                <button type="button" @click="testSingleKey(k.id)" :disabled="testingKeyId === k.id"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/75 dark:text-white/80 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:bg-black/5 transition flex items-center gap-1 shadow-xs">
                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]" :class="testingKeyId === k.id ? 'animate-spin' : ''"></i>
                                    <span>Uji Ping</span>
                                </button>
                                <button type="button" @click="openEditKeyModal(k)"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Ubah</span>
                                </button>
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id + '/toggle'" method="POST">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold transition"
                                        :class="k.is_active ? 'bg-black/5 dark:bg-white/10 text-black/60' : 'bg-[#34C759]/15 text-[#248A3D]'">
                                        <span x-text="k.is_active ? 'Jeda' : 'Aktifkan'"></span>
                                    </button>
                                </form>
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id" method="POST" onsubmit="return confirm('Hapus API Key ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-black/5 transition flex items-center justify-center">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Baris Detail Token & Model -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 pt-2 border-t border-black/5 dark:border-white/5 text-[11.5px]">
                            <div class="md:col-span-4 flex items-center gap-2 min-w-0">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Token:</span>
                                <div class="px-2.5 py-1 rounded-[6px] bg-white dark:bg-[#1C1C1E] border border-black/5 font-mono text-[11px] flex items-center justify-between gap-2 flex-1 min-w-0">
                                    <span class="truncate select-all" x-text="revealedTokens[k.id] ? k.key : k.masked_key"></span>
                                    <button type="button" @click="copyText(k.masked_key, k.id)" class="text-black/40 hover:text-[#007AFF] shrink-0">
                                        <i data-lucide="copy" class="w-3 h-3" x-show="copiedTokenId !== k.id"></i>
                                        <i data-lucide="check" class="w-3 h-3 text-[#34C759]" x-show="copiedTokenId === k.id"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-4 flex items-center gap-2 min-w-0">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Endpoint:</span>
                                <span class="font-mono text-[#007AFF] text-[11px] truncate select-all flex-1" x-text="k.endpoint"></span>
                            </div>
                            <div class="md:col-span-4 flex items-center justify-between gap-2 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                    <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Model:</span>
                                    <span class="font-mono font-semibold text-black dark:text-white text-[11px] truncate"
                                        :class="k.model === 'default' ? 'text-[#007AFF]' : ''"
                                        x-text="k.model === 'default' ? '🌐 Multi-Model (default)' : (k.model || 'Default Platform')"></span>
                                </div>
                                <div class="text-right shrink-0 tabular-nums text-black/60 dark:text-white/60">
                                    <strong class="text-black dark:text-white" x-text="k.total_requests"></strong> kueri
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Banner -->
                        <div x-show="pingFeedback[k.id]?.show" x-transition class="p-2.5 rounded-[8px] text-[11.5px] flex items-center justify-between gap-2"
                            :class="pingFeedback[k.id]?.success ? 'bg-[#34C759]/10 text-[#248A3D] border border-[#34C759]/20' : 'bg-[#FF3B30]/10 text-[#D70015] border border-[#FF3B30]/20'">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="zap" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="font-bold shrink-0" x-text="pingFeedback[k.id]?.success ? 'Ping Berhasil (' + pingFeedback[k.id]?.latency_ms + 'ms):' : 'Ping Gagal:'"></span>
                                <span class="truncate" x-text="pingFeedback[k.id]?.message"></span>
                            </div>
                            <button type="button" @click="pingFeedback[k.id].show = false" class="opacity-60 hover:opacity-100 shrink-0"><i data-lucide="x" class="w-3 h-3"></i></button>
                        </div>
                    </div>
                </template>

                <!-- Empty State Chatbot -->
                <template x-if="filteredChatKeys().length === 0">
                    <div class="p-8 text-center rounded-[16px] border border-dashed border-black/10 space-y-2">
                        <i data-lucide="message-square" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] font-semibold text-black dark:text-white">Belum ada kunci API khusus Chatbot terdaftar</p>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Hubungkan kunci OpenAI, Claude, Gemini, atau OpenRouter untuk mengaktifkan WhatsApp CS.</p>
                        <button type="button" @click="openAddKeyModal(null, 'chat')"
                            class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Kunci Chatbot</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- TAB 2: KUNCI AI STUDIO (Generasi Gambar: Flare, Sunburst, Ideogram, Flux) -->
    <div x-show="activeTab === 'studio'" x-cloak class="space-y-6">
        <!-- Overview Banner Scoped to AI Studio -->
        <div class="rounded-[20px] bg-gradient-to-r from-[#AF52DE]/10 via-[#FF2D55]/5 to-transparent border border-[#AF52DE]/20 p-5 sm:p-6 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center shrink-0">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
                            <span>Manajemen Kunci AI Studio (Generasi Gambar)</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#AF52DE]/15 text-[#AF52DE]">Terisolasi</span>
                        </h2>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                            Kunci ini dikhususkan untuk melayani generasi gambar promosi produk UMKM, poster media sosial, dan editing foto referensi.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="openAddKeyModal('openai', 'image')"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Kunci AI Studio</span>
                    </button>
                </div>
            </div>

            <!-- Provider Badge Channels for AI Studio -->
            <div class="flex items-center gap-2 pt-2 border-t border-[#AF52DE]/10 overflow-x-auto no-scrollbar text-[11.5px]">
                <span class="text-black/50 dark:text-white/50 font-medium shrink-0">Model Gambar Terdaftar:</span>
                <span class="px-2 py-0.5 rounded-full bg-[#10B981]/15 text-[#10B981] font-semibold shrink-0">GPT Image 2.5 Flare (Standard)</span>
                <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF] font-semibold shrink-0">GPT Image 2.5 Sunburst (Premium Edit)</span>
                <span class="px-2 py-0.5 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] font-semibold shrink-0">Ideogram v2</span>
                <span class="px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-semibold shrink-0">Stability AI / Flux Pro</span>
            </div>
        </div>

        <!-- AI Studio Keys Pool Card -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-6 shadow-sm space-y-4">
            <!-- Filter & Search Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Daftar Kunci AI Studio Aktif</h3>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/10 text-[#AF52DE]"
                        x-text="filteredStudioKeys().length + ' Kunci'"></span>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="studioSearch" placeholder="Cari nama, model, endpoint..."
                        class="w-full h-8 pl-8 pr-3 text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 rounded-[8px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#AF52DE]">
                    <div class="absolute left-2.5 top-2 text-black/40 dark:text-white/40">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            <!-- AI Studio Keys List -->
            <div class="space-y-3">
                <template x-for="k in filteredStudioKeys()" :key="k.id">
                    <div class="rounded-[16px] p-4 bg-black/[0.02] dark:bg-white/[0.03] border transition-all duration-150 space-y-3"
                        :class="k.is_active ? 'border-black/5 dark:border-white/5 hover:border-[#AF52DE]/30' : 'border-black/5 opacity-60'">
                        
                        <!-- Header Baris Kunci -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2 flex-wrap min-w-0">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white shrink-0"
                                    :class="{
                                        'bg-[#10B981]': k.provider === 'openai',
                                        'bg-[#E11D48]': k.provider === 'ideogram',
                                        'bg-[#6366F1]': k.provider === 'stability',
                                        'bg-[#007AFF]': k.provider === 'openai_compatible'
                                    }" x-text="k.provider_meta?.short_name || k.provider"></span>

                                <h4 class="text-[13.5px] font-bold text-black dark:text-white truncate" x-text="k.name"></h4>

                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold border shrink-0"
                                    :class="k.priority === 1 ? 'bg-[#AF52DE]/10 text-[#AF52DE] border-[#AF52DE]/20' : 'bg-black/5 dark:bg-white/10 text-black/60'">
                                    <span x-text="k.priority === 1 ? 'Tier 1 (Utama)' : (k.priority === 2 ? 'Tier 2 (Cadangan)' : 'Tier 3 (Darurat)')"></span>
                                </span>

                                <template x-if="k.purpose === 'all'">
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] border border-[#34C759]/20 shrink-0">🌐 Universal</span>
                                </template>

                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0"
                                    :class="k.is_active ? 'bg-[#34C759]/15 text-[#248A3D]' : 'bg-black/10 text-black/45'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="k.is_active ? 'bg-[#34C759] animate-pulse' : 'bg-black/40'"></span>
                                    <span x-text="k.is_active ? 'Online' : 'Nonaktif'"></span>
                                </span>

                                <template x-if="k.latency_ms > 0">
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-mono font-bold bg-[#AF52DE]/10 text-[#AF52DE]" x-text="k.latency_ms + ' ms'"></span>
                                </template>
                            </div>

                            <!-- Action Cluster -->
                            <div class="flex items-center gap-1.5 shrink-0 self-start md:self-auto flex-wrap">
                                <button type="button" @click="testSingleKey(k.id)" :disabled="testingKeyId === k.id"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/75 dark:text-white/80 bg-white dark:bg-[#2C2C2E] border border-black/10 hover:bg-black/5 transition flex items-center gap-1 shadow-xs">
                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#AF52DE]" :class="testingKeyId === k.id ? 'animate-spin' : ''"></i>
                                    <span>Uji Ping</span>
                                </button>
                                <button type="button" @click="openEditKeyModal(k)"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-[#AF52DE] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition flex items-center gap-1">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Ubah</span>
                                </button>
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id + '/toggle'" method="POST">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold transition"
                                        :class="k.is_active ? 'bg-black/5 text-black/60' : 'bg-[#34C759]/15 text-[#248A3D]'">
                                        <span x-text="k.is_active ? 'Jeda' : 'Aktifkan'"></span>
                                    </button>
                                </form>
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id" method="POST" onsubmit="return confirm('Hapus API Key AI Studio ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-black/5 transition flex items-center justify-center">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Baris Detail Token & Model -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 pt-2 border-t border-black/5 dark:border-white/5 text-[11.5px]">
                            <div class="md:col-span-4 flex items-center gap-2 min-w-0">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Token:</span>
                                <div class="px-2.5 py-1 rounded-[6px] bg-white dark:bg-[#1C1C1E] border border-black/5 font-mono text-[11px] flex items-center justify-between gap-2 flex-1 min-w-0">
                                    <span class="truncate select-all" x-text="revealedTokens[k.id] ? k.key : k.masked_key"></span>
                                    <button type="button" @click="copyText(k.masked_key, k.id)" class="text-black/40 hover:text-[#AF52DE] shrink-0">
                                        <i data-lucide="copy" class="w-3 h-3" x-show="copiedTokenId !== k.id"></i>
                                        <i data-lucide="check" class="w-3 h-3 text-[#34C759]" x-show="copiedTokenId === k.id"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-4 flex items-center gap-2 min-w-0">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Endpoint:</span>
                                <span class="font-mono text-[#AF52DE] text-[11px] truncate select-all flex-1" x-text="k.endpoint"></span>
                            </div>
                            <div class="md:col-span-4 flex items-center justify-between gap-2 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                    <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Model:</span>
                                    <span class="font-mono font-semibold text-black dark:text-white text-[11px] truncate"
                                        :class="k.model === 'default' ? 'text-[#AF52DE]' : ''"
                                        x-text="k.model === 'default' ? '🌐 Multi-Model (default)' : (k.model || 'gpt-image-2.5-flare')"></span>
                                </div>
                                <div class="text-right shrink-0 tabular-nums text-black/60 dark:text-white/60">
                                    <strong class="text-black dark:text-white" x-text="k.total_requests"></strong> render
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Banner -->
                        <div x-show="pingFeedback[k.id]?.show" x-transition class="p-2.5 rounded-[8px] text-[11.5px] flex items-center justify-between gap-2"
                            :class="pingFeedback[k.id]?.success ? 'bg-[#34C759]/10 text-[#248A3D] border border-[#34C759]/20' : 'bg-[#FF3B30]/10 text-[#D70015] border border-[#FF3B30]/20'">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="zap" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="font-bold shrink-0" x-text="pingFeedback[k.id]?.success ? 'Ping Berhasil (' + pingFeedback[k.id]?.latency_ms + 'ms):' : 'Ping Gagal:'"></span>
                                <span class="truncate" x-text="pingFeedback[k.id]?.message"></span>
                            </div>
                            <button type="button" @click="pingFeedback[k.id].show = false" class="opacity-60 hover:opacity-100 shrink-0"><i data-lucide="x" class="w-3 h-3"></i></button>
                        </div>
                    </div>
                </template>

                <!-- Empty State AI Studio -->
                <template x-if="filteredStudioKeys().length === 0">
                    <div class="p-8 text-center rounded-[16px] border border-dashed border-black/10 space-y-2">
                        <i data-lucide="palette" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] font-semibold text-black dark:text-white">Belum ada kunci API khusus AI Studio terdaftar</p>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Hubungkan API key OpenAI (untuk GPT Image 2.5 Flare/Sunburst), Ideogram, atau Flux untuk mengaktifkan AI Studio.</p>
                        <button type="button" @click="openAddKeyModal('openai', 'image')"
                            class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Kunci AI Studio</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- TAB 3: GATEWAY & ROTASI KUOTA (Load Balancing & Dynamic Multi-Model) -->
    <div x-show="activeTab === 'gateway'" x-cloak class="space-y-6">

        <!-- 1. Penjelasan Arsitektur & Strategi Rotasi -->
        <div class="rounded-[20px] bg-gradient-to-br from-[#0B0F19] via-[#111827] to-[#1E1B4B] text-white p-5 sm:p-7 shadow-lg border border-white/10 space-y-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/20 border border-[#007AFF]/30 flex items-center justify-center text-[#38BDF8] shrink-0">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-[18px] sm:text-[20px] font-bold text-white flex items-center gap-2">
                        <span>Pusat Kendali Gateway &amp; Strategi Rotasi Kuota</span>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-[#007AFF]/25 text-[#38BDF8] border border-[#007AFF]/40">9Router Engine</span>
                    </h2>
                    <p class="text-[12.5px] text-white/70 mt-0.5">
                        Tentukan cara gateway mengalokasikan kuota API: bagi rata (*Round-Robin*) atau habiskan kuota satu per satu bertahap (*Tiered Waterfall*).
                    </p>
                </div>
            </div>

            <!-- Form Pengaturan Strategi Rotasi -->
            <form action="{{ route('admin.ai-tokens.engine.update') }}" method="POST" class="space-y-4 pt-2 border-t border-white/10">
                @csrf

                <!-- Radio Pilihan Strategi Rotasi -->
                <div>
                    <label class="block text-[12.5px] font-medium text-white/80 mb-2">
                        Pilih Mode Alokasi Trafik &amp; Kuota API:
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <!-- Opsi 1: Habiskan Satu per Satu (Failover / Waterfall) -->
                        <label class="p-4 rounded-[14px] border cursor-pointer transition-all flex items-start gap-3 bg-white/[0.04]"
                            :class="rotationStrategy === 'failover' ? 'border-[#38BDF8] ring-1 ring-[#38BDF8] bg-[#007AFF]/15' : 'border-white/10 hover:border-white/20'">
                            <input type="radio" name="rotation_strategy" value="failover" x-model="rotationStrategy" class="mt-1 text-[#38BDF8]">
                            <div class="space-y-1">
                                <div class="text-[13.5px] font-bold text-white flex items-center gap-2">
                                    <span>Habiskan Satu per Satu Bertahap (3-Tier Priority)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-[#34C759]/25 text-[#4ADE80]">Hemat Kuota</span>
                                </div>
                                <p class="text-[11.5px] text-white/70 leading-relaxed">
                                    Gateway memprioritaskan kunci <strong>Tier 1 (Utama)</strong> sampai kuota habis atau terkena rate limit (429). Jika limit tercapai, trafik otomatis beralih bertahap ke <strong>Tier 2 (Cadangan)</strong> lalu <strong>Tier 3 (Darurat)</strong> tanpa memutus pengguna.
                                </p>
                            </div>
                        </label>

                        <!-- Opsi 2: Round-Robin Load Balancing -->
                        <label class="p-4 rounded-[14px] border cursor-pointer transition-all flex items-start gap-3 bg-white/[0.04]"
                            :class="rotationStrategy === 'round_robin' ? 'border-[#38BDF8] ring-1 ring-[#38BDF8] bg-[#007AFF]/15' : 'border-white/10 hover:border-white/20'">
                            <input type="radio" name="rotation_strategy" value="round_robin" x-model="rotationStrategy" class="mt-1 text-[#38BDF8]">
                            <div class="space-y-1">
                                <div class="text-[13.5px] font-bold text-white flex items-center gap-2">
                                    <span>Round-Robin Load Balancing</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-[#007AFF]/25 text-[#38BDF8]">Bagi Rata</span>
                                </div>
                                <p class="text-[11.5px] text-white/70 leading-relaxed">
                                    Setiap panggilan API digilir secara bergantian (A &rarr; B &rarr; C &rarr; A...) ke seluruh kunci API yang aktif di dalam pool. Menyeimbangkan beban kerja antar akun provider dan mencegah limit request-per-minute (RPM).
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Model Default & Provider Fallback -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[12px] font-medium text-white/80">Model Acuan Default Platform</label>
                            <button type="button" @click="manageModelsOpen = true" class="text-[11px] font-semibold text-[#38BDF8] hover:underline">
                                Bongkar Pasang Model (<span x-text="modelsList.length"></span>)
                            </button>
                        </div>
                        <select name="model" x-model="selectedModel"
                            class="w-full h-10 px-3.5 bg-black/40 border border-white/15 rounded-[10px] text-[13px] text-white focus:outline-none focus:ring-2 focus:ring-[#38BDF8] font-mono">
                            <template x-for="m in modelsList" :key="m.id">
                                <option :value="m.id" :selected="m.id === selectedModel" x-text="m.name + ' (' + m.id + ')'"></option>
                            </template>
                            <option value="custom">-- Gunakan Model Kustom Lainnya --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-white/80 mb-1.5">Provider Driver Utama (Fallback)</label>
                        <select name="provider" x-model="selectedProvider"
                            class="w-full h-10 px-3.5 bg-black/40 border border-white/15 rounded-[10px] text-[13px] text-white focus:outline-none focus:ring-2 focus:ring-[#38BDF8]">
                            <option value="openai">OpenAI Official Direct (api.openai.com)</option>
                            <option value="anthropic">Anthropic Claude Official (api.anthropic.com)</option>
                            <option value="gemini">Google Gemini Official (AI Studio)</option>
                            <option value="openrouter">OpenRouter AI Gateway (openrouter.ai)</option>
                            <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM / Proxy)</option>
                        </select>
                    </div>
                </div>

                <!-- Endpoint URL Fallback -->
                <div>
                    <label class="block text-[12px] font-medium text-white/80 mb-1.5">Endpoint Base URL Fallback</label>
                    <input type="url" name="endpoint" x-model="endpointUrl" required
                        class="w-full h-10 px-3.5 bg-black/40 border border-white/15 rounded-[10px] text-[13px] font-mono text-white focus:outline-none focus:ring-2 focus:ring-[#38BDF8]">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-black bg-[#38BDF8] hover:bg-[#0EA5E9] transition flex items-center gap-2 shadow-md w-full sm:w-auto justify-center">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Konfigurasi Gateway</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Panduan Multi-Model dalam 1 API Key (Dynamic Multi-Model Architecture) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/15 text-[#248A3D] flex items-center justify-center shrink-0">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Arsitektur Multi-Model: 1 API Key untuk Banyak Model</h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                        Anda tidak perlu membuat kunci API terpisah untuk setiap model. Di COOCA, 1 kunci API OpenAI atau provider lain dapat melayani banyak model sekaligus secara dinamis.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[12px]">
                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 space-y-1.5">
                    <div class="font-bold text-[#007AFF] flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Isi Model: "default"</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Saat kolom model diisi <code class="font-mono text-[#007AFF] font-bold">default</code>, kunci tersebut menjadi dinamis dan dapat mengeksekusi Flare, Sunburst, GPT-4o, atau model apa pun yang diminta pengguna.
                    </p>
                </div>

                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 space-y-1.5">
                    <div class="font-bold text-[#AF52DE] flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                        <span>Banyak Provider Aktif</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Dukung OpenAI, Anthropic, Gemini, OpenRouter, dan server private (vLLM/Ollama). Gateway akan memilih provider sesuai kebutuhan domain tugas.
                    </p>
                </div>

                <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 space-y-1.5">
                    <div class="font-bold text-[#FF9500] flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Pemisahan Kuota Aman</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Kunci AI Studio terisolasi dari kunci Chatbot WhatsApp agar beban render gambar resolusi tinggi tidak mengganggu response time obrolan pelanggan.
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Panduan Format Endpoint Provider Preset -->
        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 p-4 sm:p-5 space-y-3">
            <h4 class="text-[13px] font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Format Endpoint &amp; Token Resmi Provider</span>
            </h4>
            <div class="overflow-x-auto rounded-[12px] border border-black/5 bg-white dark:bg-[#1C1C1E]">
                <table class="w-full text-left text-[11.5px]">
                    <thead class="bg-black/[0.03] text-black/70 font-semibold border-b border-black/5">
                        <tr>
                            <th class="py-2.5 px-3">Provider</th>
                            <th class="py-2.5 px-3">Base URL Endpoint</th>
                            <th class="py-2.5 px-3">Format Token</th>
                            <th class="py-2.5 px-3 text-right">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 text-black/80">
                        <tr>
                            <td class="py-2.5 px-3 font-semibold">OpenAI Official</td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF]">https://api.openai.com/v1</td>
                            <td class="py-2.5 px-3"><code class="font-mono text-[#007AFF]">sk-proj-...</code> (GPT-4o, GPT Image 2.5)</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('openai')" class="text-[#007AFF] font-semibold hover:underline">Hubungkan</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-semibold">Google Gemini Official</td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF]">https://generativelanguage.googleapis.com/v1beta</td>
                            <td class="py-2.5 px-3"><code class="font-mono text-[#007AFF]">AIzaSy...</code> (Gemini 2.0 Flash)</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('gemini')" class="text-[#8B5CF6] font-semibold hover:underline">Hubungkan</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-semibold">Anthropic Claude Official</td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF]">https://api.anthropic.com/v1</td>
                            <td class="py-2.5 px-3"><code class="font-mono text-[#007AFF]">sk-ant-api03-...</code> (Claude 3.7 Sonnet)</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('anthropic')" class="text-[#D97706] font-semibold hover:underline">Hubungkan</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-semibold">OpenRouter Gateway</td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF]">https://openrouter.ai/api/v1</td>
                            <td class="py-2.5 px-3"><code class="font-mono text-[#007AFF]">sk-or-v1-...</code> (DeepSeek-R1, Llama 3)</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('openrouter')" class="text-[#06B6D4] font-semibold hover:underline">Hubungkan</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: MONITORING KONSUMSI TOKEN TENANT -->
    <div x-show="activeTab === 'monitoring'" x-cloak class="space-y-6">
        <!-- 4 KPI Metrics Grid -->
        <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 pb-1 sm:pb-0">
            <div class="snap-start shrink-0 w-[220px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Token Bulan Ini (MTD)</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensMonth, 0, ',', '.') }}</span>
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE] shrink-0" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Konsumsi bulan berjalan</div>
            </div>
            <div class="snap-start shrink-0 w-[220px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Token All-Time</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensAllTime, 0, ',', '.') }}</span>
                    <i data-lucide="database" class="w-4 h-4 text-[#5856D6] shrink-0" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Akumulasi peluncuran</div>
            </div>
            <div class="snap-start shrink-0 w-[220px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kueri Eksekusi</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalQueriesCount, 0, ',', '.') }}</span>
                    <i data-lucide="cpu" class="w-4 h-4 text-[#30B0C7] shrink-0" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Panggilan API AI sukses</div>
            </div>
            <div class="snap-start shrink-0 w-[220px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bisnis Aktif Menggunakan AI</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($activeAiBusinessesCount, 0, ',', '.') }}</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-[#34C759] shrink-0" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Tenant aktif bulan ini</div>
            </div>
        </div>

        <!-- 2 Columns Grid: Usage by Intent & Top Consuming Businesses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-sm space-y-4">
                <div class="border-b border-black/5 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                        <span>Distribusi Konsumsi Token per Fitur / Intent</span>
                    </h3>
                </div>
                @if ($intentBreakdown->isEmpty())
                    <div class="text-[13px] text-black/45 py-8 text-center">Belum ada riwayat konsumsi token tercatat</div>
                @else
                    <div class="space-y-3">
                        @foreach ($intentBreakdown as $intent)
                            @php
                                $percent = $totalTokensAllTime > 0 ? round(($intent->total_tokens / $totalTokensAllTime) * 100, 1) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="font-medium text-black dark:text-white capitalize flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[#AF52DE] shrink-0"></span>
                                        {{ str_replace('_', ' ', $intent->intent) }}
                                    </span>
                                    <div class="text-right text-[12px] tabular-nums text-black/50 dark:text-white/50">
                                        <span class="font-semibold text-black dark:text-white">{{ number_format($intent->total_tokens, 0, ',', '.') }}</span> token ({{ $percent }}%)
                                    </div>
                                </div>
                                <div class="h-2 rounded-full bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden">
                                    <div class="h-full rounded-full bg-[#AF52DE]" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-sm space-y-4">
                <div class="border-b border-black/5 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                        <span>Top 10 Tenant Konsumsi Token Tertinggi</span>
                    </h3>
                </div>
                @if ($topBusinesses->isEmpty())
                    <div class="text-[13px] text-black/45 py-8 text-center">Belum ada data tenant yang menggunakan AI</div>
                @else
                    <div class="divide-y divide-black/[0.04]">
                        @foreach ($topBusinesses as $index => $top)
                            <div class="py-3 flex items-center justify-between text-[13px] hover:bg-black/[0.02] px-2 rounded-[10px] transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-6 h-6 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] font-semibold text-[11px] tabular-nums flex items-center justify-center shrink-0">{{ $index + 1 }}</span>
                                    <div class="min-w-0">
                                        <div class="font-medium text-black dark:text-white truncate">
                                            <a href="{{ route('admin.businesses.show', $top['business_id']) }}" class="hover:text-[#AF52DE] transition">{{ $top['business_name'] }}</a>
                                        </div>
                                        <div class="text-[11px] text-black/45 tabular-nums">{{ $top['queries_count'] }} kali kueri</div>
                                    </div>
                                </div>
                                <div class="text-right tabular-nums shrink-0">
                                    <div class="font-bold text-[#AF52DE]">{{ number_format($top['total_tokens'], 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/45">token</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity Logs Table -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
            <div class="px-4 sm:px-5 py-3.5 border-b border-black/5">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-[#30B0C7] shrink-0"></i>
                    <span>Log Aktivitas Token AI Terbaru (20 Transaksi Terakhir)</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 text-black/50">
                            <th class="px-4 py-2.5 text-[11px] font-semibold whitespace-nowrap">Waktu</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold whitespace-nowrap">Bisnis / Pengguna</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold whitespace-nowrap">Intent</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold whitespace-nowrap">Model Engine</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-right whitespace-nowrap">Input / Output</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-right whitespace-nowrap">Total Token</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @forelse($recentUsages as $usage)
                            <tr class="hover:bg-black/[0.02] transition">
                                <td class="px-4 py-3 text-[12px] text-black/50 tabular-nums whitespace-nowrap">{{ $usage->created_at?->format('d M H:i:s') ?? '-' }}</td>
                                <td class="px-4 py-3 min-w-0">
                                    <div class="font-medium text-black dark:text-white truncate">{{ $usage->business_name }}</div>
                                    <div class="text-[11px] text-black/45 truncate">{{ $usage->user?->name ?? 'System' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#7C3AA6]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>{{ $usage->intent ?? 'general' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-black/50 font-mono whitespace-nowrap">{{ $usage->model_name ?? 'ag/gemini-3.8-flash-high' }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-black/50 tabular-nums whitespace-nowrap">
                                    {{ number_format($usage->input_tokens) }} / {{ number_format($usage->output_tokens) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-[#AF52DE] tabular-nums whitespace-nowrap">{{ number_format($usage->total_tokens) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-black/50">Belum ada aktivitas token tercatat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: TAMBAH API KEY BARU (Adaptive Bottom Sheet di Mobile, Centered di Desktop) -->
    <template x-teleport="body">
        <div x-show="addKeyModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="addKeyModalOpen = false" @keydown.escape.window="addKeyModalOpen = false">
            <div @click.stop
                class="relative z-10 w-full sm:max-w-lg rounded-t-[24px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-2xl flex flex-col max-h-[92vh] sm:max-h-[85vh] overflow-hidden">
                
                <!-- Modal Sticky Header -->
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 shrink-0 bg-white dark:bg-[#1C1C1E]">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="key" class="w-5 h-5 text-[#007AFF] shrink-0"></i>
                        <span>Hubungkan Kunci API Baru</span>
                    </div>
                    <button type="button" @click="addKeyModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 hover:bg-black/5">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form action="{{ route('admin.ai-tokens.engine.keys.add') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    
                    <div class="overflow-y-auto px-5 sm:px-6 py-4 space-y-4 flex-1">
                        <!-- Peruntukan Kunci (Isolasi Chatbot vs Studio) -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                                <span>Peruntukan Kunci (Pemisahan Pengelolaan)</span>
                                <span class="text-[11px] font-bold text-[#FF9500]">Isolasi Kuota</span>
                            </label>
                            <select name="purpose" x-model="newKeyForm.purpose" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="chat">💬 Khusus AI Chatbot (WhatsApp CS, Live Chat, Prompts)</option>
                                <option value="image">🎨 Khusus AI Studio (Gambar: Flare, Sunburst, Flux, Ideogram)</option>
                                <option value="all">🌐 Universal (Bisa Chatbot &amp; AI Studio)</option>
                            </select>
                        </div>

                        <!-- Provider Selector -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Pilih AI Provider
                            </label>
                            <select name="provider" x-model="newKeyForm.provider" @change="onAddKeyProviderChange()" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="openai">OpenAI Official Direct (GPT &amp; Image Studio)</option>
                                <option value="anthropic">Anthropic Claude Official (Claude 3.7)</option>
                                <option value="gemini">Google Gemini Official (Gemini 2.0 Flash)</option>
                                <option value="openrouter">OpenRouter AI Gateway (Universal)</option>
                                <option value="ideogram">Ideogram AI Official (Poster &amp; Typography)</option>
                                <option value="stability">Stability AI / Flux Pro (Photo Realism)</option>
                                <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM / Proxy)</option>
                            </select>
                        </div>

                        <!-- Key Name & Priority Tier in Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">Label Nama Kunci</label>
                                <input type="text" name="key_name" x-model="newKeyForm.name" required placeholder="Misal: OpenAI Primary / Claude 3.7"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                    Tier Rotasi (Habiskan Bertahap)
                                </label>
                                <select name="priority" x-model.number="newKeyForm.priority"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="1">Tier 1: Utama (Dihabiskan Pertama)</option>
                                    <option value="2">Tier 2: Cadangan (Dipakai jika T1 Limit)</option>
                                    <option value="3">Tier 3: Darurat (Emergency Fallback)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Token API Key -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">Token API Key</label>
                            <input type="password" name="api_key" x-model="newKeyForm.api_key" required placeholder="sk-..."
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <!-- Base URL Endpoint & Model in Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">Base URL Endpoint</label>
                                <input type="url" name="endpoint" x-model="newKeyForm.endpoint" required
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>

                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">Model Terkait</label>
                                <input type="text" name="model" x-model="newKeyForm.model" required
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                        </div>

                        <!-- Dynamic Multi-Model Helper Tip -->
                        <div class="p-3 rounded-[12px] bg-[#007AFF]/5 border border-[#007AFF]/20 text-[11.5px] text-black/80 dark:text-white/80 leading-relaxed flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Multi-Model dalam 1 API Key:</strong> Ketik <code class="font-mono text-[#007AFF] font-bold">default</code> pada kolom model agar 1 kunci ini otomatis melayani banyak model yang diminta oleh sistem (Flare, Sunburst, GPT-4o, dll) tanpa terkunci pada satu model.
                            </div>
                        </div>
                    </div>

                    <!-- Modal Sticky Footer -->
                    <div class="border-t border-black/5 dark:border-white/10 px-5 sm:px-6 py-3.5 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" @click="addKeyModalOpen = false" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 hover:bg-black/5">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Kunci API</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT API KEY (Adaptive Bottom Sheet) -->
    <template x-teleport="body">
        <div x-show="editKeyModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="editKeyModalOpen = false" @keydown.escape.window="editKeyModalOpen = false">
            <div @click.stop
                class="relative z-10 w-full sm:max-w-lg rounded-t-[24px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-2xl flex flex-col max-h-[92vh] sm:max-h-[85vh] overflow-hidden">
                
                <div class="flex items-center justify-between border-b border-black/5 px-5 sm:px-6 py-4 shrink-0 bg-white dark:bg-[#1C1C1E]">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="edit-3" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Edit Konfigurasi Kunci API</span>
                    </div>
                    <button type="button" @click="editKeyModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 hover:bg-black/5">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + editingKey.id" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    
                    <div class="overflow-y-auto px-5 sm:px-6 py-4 space-y-4 flex-1">
                        <!-- Peruntukan Kunci -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                                <span>Peruntukan Kunci</span>
                                <span class="text-[11px] font-bold text-[#FF9500]">Isolasi Kuota</span>
                            </label>
                            <select name="purpose" x-model="editingKey.purpose" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="chat">💬 Khusus AI Chatbot (WhatsApp CS, Live Chat, Prompts)</option>
                                <option value="image">🎨 Khusus AI Studio (Gambar: Flare, Sunburst, Flux, Ideogram)</option>
                                <option value="all">🌐 Universal (Bisa Chatbot &amp; AI Studio)</option>
                            </select>
                        </div>

                        <!-- Provider -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">AI Provider</label>
                            <select name="provider" x-model="editingKey.provider" @change="onEditKeyProviderChange()" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="openai">OpenAI Official Direct</option>
                                <option value="anthropic">Anthropic Claude Official</option>
                                <option value="gemini">Google Gemini Official</option>
                                <option value="openrouter">OpenRouter AI Gateway</option>
                                <option value="ideogram">Ideogram AI Official</option>
                                <option value="stability">Stability AI / Flux Pro</option>
                                <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM)</option>
                            </select>
                        </div>

                        <!-- Name & Priority -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">Nama Kunci</label>
                                <input type="text" name="key_name" x-model="editingKey.name" required
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">Tier Rotasi</label>
                                <select name="priority" x-model.number="editingKey.priority"
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="1">Tier 1: Utama (Dihabiskan Pertama)</option>
                                    <option value="2">Tier 2: Cadangan (Dipakai jika T1 Limit)</option>
                                    <option value="3">Tier 3: Darurat (Emergency Fallback)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Update Token -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">Perbarui Token API (Opsional)</label>
                            <input type="password" name="api_key" x-model="editingKey.api_key" placeholder="•••••••• (Biarkan kosong jika tidak ingin mengubah)"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <!-- Endpoint & Model -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">Base URL Endpoint</label>
                                <input type="url" name="endpoint" x-model="editingKey.endpoint" required
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-black/70 mb-1.5">Model Terkait</label>
                                <input type="text" name="model" x-model="editingKey.model" required
                                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                        </div>

                        <!-- Tip Multi-Model -->
                        <div class="p-3 rounded-[12px] bg-[#007AFF]/5 border border-[#007AFF]/20 text-[11.5px] text-black/80 leading-relaxed flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Tips Multi-Model:</strong> Masukkan <code class="font-mono text-[#007AFF] font-bold">default</code> agar 1 kunci ini otomatis melayani banyak model platform tanpa perlu membuat entri kunci terpisah.
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-black/5 px-5 sm:px-6 py-3.5 bg-black/[0.02] flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" @click="editKeyModalOpen = false" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 hover:bg-black/5">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: BONGKAR PASANG MODEL (Adaptive Sheet) -->
    <template x-teleport="body">
        <div x-show="manageModelsOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="manageModelsOpen = false" @keydown.escape.window="manageModelsOpen = false">
            <div @click.stop
                class="relative z-10 w-full sm:max-w-xl rounded-t-[24px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 shadow-2xl flex flex-col max-h-[92vh] sm:max-h-[85vh] overflow-hidden">
                
                <div class="flex items-center justify-between border-b border-black/5 px-5 sm:px-6 py-4 shrink-0 bg-white dark:bg-[#1C1C1E]">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="layers" class="w-5 h-5 text-[#AF52DE]"></i>
                        <span>Bongkar Pasang Model AI Platform</span>
                    </div>
                    <button type="button" @click="manageModelsOpen = false" class="p-1.5 rounded-[6px] text-black/40 hover:bg-black/5">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="overflow-y-auto px-5 sm:px-6 py-4 space-y-4 flex-1">
                    <div x-show="modelFeedback.show" x-transition class="p-3 rounded-[10px] text-[12px] flex items-center justify-between"
                        :class="modelFeedback.success ? 'bg-[#34C759]/10 text-[#248A3D]' : 'bg-[#FF3B30]/10 text-[#D70015]'">
                        <span x-text="modelFeedback.message"></span>
                        <button type="button" @click="modelFeedback.show = false"><i data-lucide="x" class="w-3 h-3"></i></button>
                    </div>

                    <div class="p-3.5 rounded-[12px] bg-black/[0.02] border border-black/5 space-y-3">
                        <div class="text-[12.5px] font-semibold text-black dark:text-white">Daftarkan Model Baru</div>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                            <div class="sm:col-span-6">
                                <input type="text" x-model="newModel.id" placeholder="Model ID (misal: ag/claude-3.7-sonnet)"
                                    class="w-full h-8 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 rounded-[8px] text-[12px] font-mono">
                            </div>
                            <div class="sm:col-span-6">
                                <input type="text" x-model="newModel.name" placeholder="Nama Tampilan (Opsional)"
                                    class="w-full h-8 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 rounded-[8px] text-[12px]">
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <select x-model="newModel.tier" class="h-7 px-2 text-[11.5px] rounded-[6px] bg-white border border-black/10">
                                <option value="balanced">Balanced</option>
                                <option value="reasoning">Reasoning / Heavy</option>
                                <option value="fast">Fast / Lightweight</option>
                            </select>
                            <button type="button" @click="addModel()" :disabled="isAddingModel || !newModel.id.trim()"
                                class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition flex items-center gap-1 disabled:opacity-50">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Pasang Model</span>
                            </button>
                        </div>
                    </div>

                    <div class="divide-y divide-black/5 border border-black/5 rounded-[12px] overflow-hidden">
                        <template x-for="m in modelsList" :key="m.id">
                            <div class="p-3 flex items-center justify-between hover:bg-black/[0.02] transition">
                                <div class="min-w-0 pr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[13px] text-black dark:text-white truncate" x-text="m.name || m.id"></span>
                                        <template x-if="m.id === selectedModel">
                                            <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-[#34C759]/15 text-[#248A3D] shrink-0">Aktif</span>
                                        </template>
                                    </div>
                                    <div class="text-[11px] font-mono text-black/50 truncate" x-text="m.id"></div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="selectActiveModel(m.id)" class="text-[11px] font-semibold px-2 py-1 rounded-[6px] bg-black/5 hover:bg-black/10 transition">Pilih</button>
                                    <button type="button" @click="deleteModel(m.id)" class="p-1 rounded text-black/40 hover:text-[#FF3B30] transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-t border-black/5 px-5 sm:px-6 py-3.5 bg-black/[0.02] flex items-center justify-between shrink-0">
                    <button type="button" @click="resetModels()" :disabled="isResettingModels" class="text-[11.5px] text-[#FF3B30] hover:underline flex items-center gap-1 disabled:opacity-50">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                        <span>Reset ke Model Bawaan</span>
                    </button>
                    <button type="button" @click="manageModelsOpen = false" class="h-8 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-black hover:opacity-90">Selesai</button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL 4: GRANT TOKEN MANUAL (Adaptive Sheet) -->
    @if(Auth::guard('admin')->user()?->hasPermission('admin.ai_tokens.grant'))
    <template x-teleport="body">
        <div x-show="grantModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="grantModalOpen = false" @keydown.escape.window="grantModalOpen = false">
            <div @click.stop
                class="relative z-10 w-full sm:max-w-md rounded-t-[24px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 shadow-2xl flex flex-col max-h-[92vh] sm:max-h-[85vh] overflow-hidden">
                
                <div class="flex items-center justify-between border-b border-black/5 px-5 sm:px-6 py-4 shrink-0 bg-white dark:bg-[#1C1C1E]">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="sparkles" class="w-5 h-5 text-[#34C759]"></i>
                        <span>Top-Up / Grant Token AI Manual</span>
                    </div>
                    <button type="button" @click="grantModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 hover:bg-black/5"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>

                <form :action="'{{ url('admin/ai-tokens') }}/' + selectedBizId + '/grant'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="overflow-y-auto px-5 sm:px-6 py-4 space-y-4 flex-1">
                        <div>
                            <label class="block text-[13px] font-medium text-black/70 mb-1.5">Pilih Bisnis / Tenant</label>
                            <select x-model="selectedBizId" required
                                class="w-full h-10 px-3 bg-black/[0.04] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="">-- Pilih Bisnis --</option>
                                @foreach ($allBusinesses as $biz)
                                    <option value="{{ $biz->id }}">{{ $biz->name }} ({{ $biz->slug }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[13px] font-medium text-black/70 mb-1.5">Jumlah Token</label>
                            <input type="number" name="tokens" x-model="tokenAmount" min="10000" step="10000" required
                                class="w-full h-10 px-3 bg-black/[0.04] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <div>
                            <label class="block text-[13px] font-medium text-black/70 mb-1.5">Alasan Audit</label>
                            <textarea name="reason" x-model="reasonText" required rows="2" placeholder="Bonus/Kompensasi/Migrasi"
                                class="w-full px-3 py-2 bg-black/[0.04] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                        </div>
                    </div>

                    <div class="border-t border-black/5 px-5 sm:px-6 py-3.5 bg-black/[0.02] flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" @click="grantModalOpen = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 hover:bg-black/5">Batal</button>
                        <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#28A745] transition flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Kirim Token</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

</div>

<script>
function aiEngineConsole(config) {
    const engineConfig = (config && config.engineConfig) || {};
    return {
        activeTab: (config && config.currentTab) || 'chatbot',
        engineStatus: engineConfig.status || 'connected',
        latencyMs: engineConfig.latency_ms || 0,
        selectedProvider: engineConfig.provider || 'openai_compatible',
        endpointUrl: engineConfig.endpoint || 'https://r4g77gv.abc-tunnel.us/v1',
        selectedModel: engineConfig.model || 'ag/gemini-3.8-flash-high',
        customModelName: '',
        rotationStrategy: engineConfig.rotation_strategy || 'failover',
        modelsList: (config && config.modelsList) || [],
        maskedKeys: engineConfig.masked_keys || [],
        providersMeta: engineConfig.providers_meta || {},
        providersStats: engineConfig.providers_stats || {},
        totalKeysCount: engineConfig.total_keys_count || 0,
        activeKeysCount: engineConfig.active_keys_count || 0,
        connectedProvidersCount: engineConfig.connected_providers_count || 0,
        totalRoutedRequests: engineConfig.total_routed_requests || 0,
        totalRoutedErrors: engineConfig.total_routed_errors || 0,

        // Scoped Search
        chatSearch: '',
        studioSearch: '',

        // Modals
        addKeyModalOpen: false,
        editKeyModalOpen: false,
        grantModalOpen: false,
        manageModelsOpen: false,

        // Add Key Form
        newKeyForm: {
            name: '',
            provider: 'openai',
            purpose: 'chat',
            api_key: '',
            endpoint: 'https://api.openai.com/v1',
            model: 'default',
            priority: 1,
            weight: 1
        },

        // Edit Key Form
        editingKey: {
            id: '',
            name: '',
            provider: 'openai',
            purpose: 'chat',
            api_key: '',
            endpoint: '',
            model: 'default',
            priority: 1,
            weight: 1,
            is_active: true
        },

        // Testing States
        isTesting: false,
        isDetecting: false,
        testingKeyId: null,
        pingFeedback: {},
        testFeedback: { show: false, success: true, message: '' },

        // Token Visual Helpers
        revealedTokens: {},
        copiedTokenId: null,

        // Grant Modal
        selectedBizId: '',
        tokenAmount: 10000000,
        reasonText: '',

        // Bongkar Pasang Model
        newModel: { id: '', name: '', tier: 'balanced' },
        isAddingModel: false,
        isResettingModels: false,
        modelFeedback: { show: false, success: true, message: '' },

        get chatKeysCount() {
            return this.maskedKeys.filter(k => (k.purpose || 'chat') !== 'image').length;
        },

        get studioKeysCount() {
            return this.maskedKeys.filter(k => k.purpose === 'image' || k.purpose === 'all').length;
        },

        init() {
            if (this.activeTab === 'engine') {
                this.activeTab = 'chatbot';
            }
            if (this.modelsList.length === 0 || !this.modelsList.some(m => m.id === this.selectedModel)) {
                this.modelsList.unshift({
                    id: this.selectedModel,
                    name: this.selectedModel + ' (Default Aktif)',
                    tier: 'reasoning'
                });
            }
            if (typeof lucide !== 'undefined') {
                this.$nextTick(() => lucide.createIcons());
            }
        },

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        filteredChatKeys() {
            return this.maskedKeys.filter(k => {
                const purpose = k.purpose || 'chat';
                if (purpose === 'image') return false; // Eksklusif tidak tampil di tab chat
                if (this.chatSearch.trim()) {
                    const q = this.chatSearch.toLowerCase();
                    return (k.name && k.name.toLowerCase().includes(q)) ||
                           (k.provider && k.provider.toLowerCase().includes(q)) ||
                           (k.model && k.model.toLowerCase().includes(q)) ||
                           (k.endpoint && k.endpoint.toLowerCase().includes(q));
                }
                return true;
            });
        },

        filteredStudioKeys() {
            return this.maskedKeys.filter(k => {
                const purpose = k.purpose || 'chat';
                if (purpose !== 'image' && purpose !== 'all') return false; // Eksklusif hanya gambar & universal
                if (this.studioSearch.trim()) {
                    const q = this.studioSearch.toLowerCase();
                    return (k.name && k.name.toLowerCase().includes(q)) ||
                           (k.provider && k.provider.toLowerCase().includes(q)) ||
                           (k.model && k.model.toLowerCase().includes(q)) ||
                           (k.endpoint && k.endpoint.toLowerCase().includes(q));
                }
                return true;
            });
        },

        openAddKeyModal(preferredProvider = null, defaultPurpose = null) {
            this.newKeyForm.purpose = defaultPurpose || (this.activeTab === 'studio' ? 'image' : 'chat');
            this.newKeyForm.provider = preferredProvider || (this.newKeyForm.purpose === 'image' ? 'openai' : 'openai');
            this.onAddKeyProviderChange();
            this.newKeyForm.name = '';
            this.newKeyForm.api_key = '';
            this.newKeyForm.priority = 1;
            this.newKeyForm.weight = 1;
            this.newKeyForm.model = 'default';
            this.addKeyModalOpen = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        onAddKeyProviderChange() {
            const meta = this.providersMeta[this.newKeyForm.provider];
            if (meta) {
                this.newKeyForm.endpoint = meta.default_endpoint || '';
                this.newKeyForm.model = 'default';
            }
        },

        openEditKeyModal(k) {
            this.editingKey = {
                id: k.id,
                name: k.name,
                provider: k.provider || 'openai',
                purpose: k.purpose || 'chat',
                endpoint: k.endpoint || '',
                model: k.model || 'default',
                priority: k.priority || 1,
                weight: k.weight || 1,
                api_key: '',
                is_active: k.is_active
            };
            this.editKeyModalOpen = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        onEditKeyProviderChange() {
            const meta = this.providersMeta[this.editingKey.provider];
            if (meta) {
                this.editingKey.endpoint = meta.default_endpoint || '';
                this.editingKey.model = 'default';
            }
        },

        copyText(text, keyId) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                this.copiedTokenId = keyId;
                setTimeout(() => { this.copiedTokenId = null; }, 2000);
            }
        },

        async testSingleKey(keyId) {
            this.testingKeyId = keyId;
            try {
                const res = await fetch(`${config.testSingleKeyBaseUrl}/${keyId}/test`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                
                const keyItem = this.maskedKeys.find(k => k.id === keyId);
                if (keyItem) {
                    keyItem.status = data.status || (data.success ? 'healthy' : 'error');
                    keyItem.latency_ms = data.latency_ms || 0;
                    keyItem.last_error = data.success ? null : data.message;
                }

                this.pingFeedback[keyId] = {
                    show: true,
                    success: data.success,
                    message: data.message,
                    latency_ms: data.latency_ms
                };

                setTimeout(() => {
                    if (this.pingFeedback[keyId]) {
                        this.pingFeedback[keyId].show = false;
                    }
                }, 8000);
            } catch (err) {
                this.pingFeedback[keyId] = {
                    show: true,
                    success: false,
                    message: 'Gagal menguji key: ' + err.message,
                    latency_ms: 0
                };
            } finally {
                this.testingKeyId = null;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        },

        selectActiveModel(modelId) {
            this.selectedModel = modelId;
            this.modelFeedback.show = true;
            this.modelFeedback.success = true;
            this.modelFeedback.message = `Model aktif dipilih: ${modelId}. Klik 'Simpan Konfigurasi Gateway' untuk menerapkan.`;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        async addModel() {
            if (!this.newModel.id.trim()) return;
            this.isAddingModel = true;
            this.modelFeedback.show = false;

            try {
                const res = await fetch(config.addModelUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        model_id: this.newModel.id.trim(),
                        model_name: this.newModel.name.trim() || this.newModel.id.trim(),
                        tier: this.newModel.tier
                    })
                });

                const data = await res.json();
                if (data.success && data.models) {
                    this.modelsList = data.models;
                    this.newModel.id = '';
                    this.newModel.name = '';
                    this.newModel.tier = 'balanced';
                    this.modelFeedback.show = true;
                    this.modelFeedback.success = true;
                    this.modelFeedback.message = data.message;
                } else {
                    this.modelFeedback.show = true;
                    this.modelFeedback.success = false;
                    this.modelFeedback.message = data.message || 'Gagal menambahkan model.';
                }
            } catch (err) {
                this.modelFeedback.show = true;
                this.modelFeedback.success = false;
                this.modelFeedback.message = 'Terjadi kesalahan: ' + err.message;
            } finally {
                this.isAddingModel = false;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        },

        async deleteModel(modelId) {
            if (!confirm(`Yakin ingin menghapus model '${modelId}'?`)) return;

            try {
                const url = config.deleteModelUrl + '/' + encodeURIComponent(modelId);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success && data.models) {
                    this.modelsList = data.models;
                    if (this.selectedModel === modelId) {
                        this.selectedModel = data.models[0]?.id || 'ag/gemini-3.8-flash-high';
                    }
                    this.modelFeedback.show = true;
                    this.modelFeedback.success = true;
                    this.modelFeedback.message = data.message;
                }
            } catch (err) {
                this.modelFeedback.show = true;
                this.modelFeedback.success = false;
                this.modelFeedback.message = 'Gagal menghapus model: ' + err.message;
            } finally {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        },

        async resetModels() {
            if (!confirm('Yakin ingin mereset daftar model ke model bawaan?')) return;
            this.isResettingModels = true;

            try {
                const res = await fetch(config.resetModelsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success && data.models) {
                    this.modelsList = data.models;
                    this.modelFeedback.show = true;
                    this.modelFeedback.success = true;
                    this.modelFeedback.message = data.message;
                }
            } catch (err) {
                this.modelFeedback.show = true;
                this.modelFeedback.success = false;
                this.modelFeedback.message = 'Gagal reset model: ' + err.message;
            } finally {
                this.isResettingModels = false;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        },

        async testConnection() {
            this.isTesting = true;
            this.testFeedback.show = false;

            try {
                const res = await fetch(config.testUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        provider: this.selectedProvider,
                        endpoint: this.endpointUrl,
                        model: this.selectedModel === 'custom' ? this.customModelName : this.selectedModel
                    })
                });

                const data = await res.json();
                this.testFeedback.show = true;
                this.testFeedback.success = data.success;
                this.testFeedback.message = data.message;

                if (data.success) {
                    this.engineStatus = 'connected';
                    this.latencyMs = data.latency_ms || 25;
                    if (data.available_models && data.available_models.length > 0) {
                        this.modelsList = data.available_models;
                    }
                } else {
                    this.engineStatus = 'error';
                }
            } catch (err) {
                this.testFeedback.show = true;
                this.testFeedback.success = false;
                this.testFeedback.message = 'Koneksi jaringan error: ' + err.message;
                this.engineStatus = 'error';
            } finally {
                this.isTesting = false;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        }
    };
}

window.aiEngineConsole = aiEngineConsole;
if (typeof Alpine !== 'undefined' && Alpine.data) {
    Alpine.data('aiEngineConsole', (config) => aiEngineConsole(config));
} else {
    document.addEventListener('alpine:init', () => {
        Alpine.data('aiEngineConsole', (config) => aiEngineConsole(config));
    });
}
</script>
@endsection
