@extends('layouts.admin', [
    'title' => 'Platform AI Engine & Token Console - Admin Console',
    'headerTitle' => 'Platform AI Engine & Token Console',
    'headerSubtitle' => 'Pusat kendali engine multi-provider AI, multi-key pool load balancing, dan monitoring token ekosistem',
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
    csrfToken: '{{ csrf_token() }}'
})">

    <!-- Top Navigation Tabs (Apple HIG Segmented Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 self-start">
            <button type="button" 
                @click="switchTab('engine')" 
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2"
                :class="activeTab === 'engine' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="cpu" class="w-4 h-4 text-[#007AFF]" stroke-width="1.75"></i>
                <span>Platform AI Engine &amp; Multi-Key Pool</span>
                <span class="w-2 h-2 rounded-full" :class="engineStatus === 'connected' ? 'bg-[#34C759]' : 'bg-[#FF9500]'"></span>
            </button>
            <button type="button" 
                @click="switchTab('monitoring')" 
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2"
                :class="activeTab === 'monitoring' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="activity" class="w-4 h-4 text-[#AF52DE]" stroke-width="1.75"></i>
                <span>Monitoring Konsumsi Token Tenant</span>
            </button>
        </div>

        <!-- Top Action Right -->
        <div class="flex items-center gap-2.5">
            <template x-if="activeTab === 'engine'">
                <div class="flex items-center gap-2">
                    <button type="button" 
                        @click="manageModelsOpen = true"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 active:scale-[0.98] transition-all flex items-center gap-1.5 border border-[#AF52DE]/20">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Bongkar Pasang Model (<span x-text="modelsList.length"></span>)</span>
                    </button>
                    <button type="button" 
                        @click="testConnection()"
                        :disabled="isTesting"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-black/80 dark:text-white/90 bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center gap-2 border border-black/5 dark:border-white/10 disabled:opacity-50">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]" :class="isTesting ? 'animate-spin' : ''" stroke-width="1.75"></i>
                        <span x-text="isTesting ? 'Menguji Latency...' : 'Uji Koneksi Engine (Live Ping)'"></span>
                    </button>
                    <button type="button" 
                        @click="addKeyModalOpen = true"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                        <span>Tambah API Key Pool</span>
                    </button>
                </div>
            </template>
            <template x-if="activeTab === 'monitoring'">
                @if(Auth::guard('admin')->user()?->hasPermission('admin.ai_tokens.grant'))
                <button type="button" @click="grantModalOpen = true"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] active:scale-[0.97] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(175,82,222,0.3)]">
                    <i data-lucide="plus-circle" class="w-4 h-4" stroke-width="1.5"></i>
                    <span>Top-Up / Grant Token Manual</span>
                </button>
                @endif
            </template>
        </div>
    </div>

    <!-- TAB 1: PLATFORM AI ENGINE & PROVIDER SETTINGS -->
    <div x-show="activeTab === 'engine'" x-cloak class="space-y-6">

        <!-- Live Engine Status & Latency Banner -->
        <div class="rounded-[16px] p-5 bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-[12px] flex items-center justify-center shrink-0"
                        :class="engineStatus === 'connected' ? 'bg-[#34C759]/12 text-[#34C759]' : 'bg-[#FF9500]/12 text-[#FF9500]'">
                        <i data-lucide="server" class="w-5 h-5" stroke-width="1.75"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Status Engine Platform:</h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider"
                                :class="engineStatus === 'connected' 
                                    ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' 
                                    : 'bg-[#FF9500]/15 text-[#C97700] dark:text-[#FF9F0A]'">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :class="engineStatus === 'connected' ? 'bg-[#34C759] animate-pulse' : 'bg-[#FF9500]'"></span>
                                <span x-text="engineStatus === 'connected' ? 'Aktif & Terhubung' : 'Belum Teruji / Menunggu'"></span>
                            </span>
                        </div>
                        <p class="text-[12.5px] text-black/50 dark:text-white/50 mt-1">
                            Engine ini menenagai seluruh fitur AI bawaan platform COOCA (POS Assistant, Chatbot, Smart Analytics, &amp; Otomasi) untuk tenant bisnis.
                        </p>
                    </div>
                </div>

                <!-- Live Metrics Pills -->
                <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto text-[12px]">
                    <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 flex items-center gap-2">
                        <span class="text-black/45 dark:text-white/45">Active Model:</span>
                        <span class="font-semibold text-black dark:text-white font-mono" x-text="selectedModel"></span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 flex items-center gap-2">
                        <span class="text-black/45 dark:text-white/45">Latency:</span>
                        <span class="font-bold text-[#007AFF] tabular-nums" x-text="latencyMs > 0 ? latencyMs + ' ms' : 'Optimal'"></span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 flex items-center gap-2">
                        <span class="text-black/45 dark:text-white/45">Rotasi Key:</span>
                        <span class="font-semibold text-[#AF52DE]" x-text="rotationStrategy === 'round_robin' ? 'Round-Robin' : 'Failover'"></span>
                    </div>
                    <div class="px-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 flex items-center gap-2">
                        <span class="text-black/45 dark:text-white/45">Pool:</span>
                        <span class="font-bold text-[#34C759] tabular-nums" x-text="activeKeysCount + ' / ' + totalKeysCount + ' Key Aktif'"></span>
                    </div>
                </div>
            </div>

            <!-- Test Result Banner Notification -->
            <div x-show="testFeedback.show" x-transition class="mt-4 p-3.5 rounded-[12px] text-[12.5px] flex items-start gap-3"
                :class="testFeedback.success 
                    ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' 
                    : 'bg-[#FF3B30]/10 text-[#D70015] dark:text-[#FF453A] border border-[#FF3B30]/20'">
                <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5" stroke-width="2"></i>
                <div class="flex-1">
                    <span class="font-semibold" x-text="testFeedback.success ? 'Koneksi Berhasil:' : 'Gagal Menghubungi Engine:'"></span>
                    <span class="ml-1" x-text="testFeedback.message"></span>
                </div>
                <button type="button" @click="testFeedback.show = false" class="opacity-60 hover:opacity-100">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- 2 Columns Grid: Engine Config Form & Multi-Key Pool -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Form: Engine & Endpoint Configuration (7 Cols) -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-sm lg:col-span-7 space-y-5">
                <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]" stroke-width="1.75"></i>
                            <span>Konfigurasi Provider &amp; Endpoint AI</span>
                        </h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Atur driver provider, custom endpoint tunnel/proxy, dan model aktif.</p>
                    </div>
                </div>

                <form action="{{ route('admin.ai-tokens.engine.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Provider Selector -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            AI Provider Engine
                        </label>
                        <select name="provider" x-model="selectedProvider"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM / Proxy)</option>
                            <option value="openai">OpenAI Official Direct (api.openai.com)</option>
                            <option value="gemini">Google Gemini Official</option>
                            <option value="anthropic">Anthropic Claude</option>
                            <option value="openrouter">OpenRouter AI Gateway</option>
                        </select>
                    </div>

                    <!-- Custom Endpoint URL (Always visible & highlighted for openai_compatible) -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                            <span>Base URL Endpoint API</span>
                            <span class="text-[11px] text-[#007AFF] font-normal" x-show="selectedProvider === 'openai_compatible'">Mendukung OpenAI v1 compliant path</span>
                        </label>
                        <div class="relative">
                            <input type="url" name="endpoint" x-model="endpointUrl" required
                                placeholder="https://r4g77gv.abc-tunnel.us/v1"
                                class="w-full h-10 pl-3.5 pr-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <div class="absolute right-3 top-2.5 text-black/40 dark:text-white/40">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1.5">
                            Contoh endpoint tunnel aktif: <code class="text-[#007AFF] font-mono select-all">https://r4g77gv.abc-tunnel.us/v1</code>
                        </p>
                    </div>

                    <!-- Model Selection & Dynamic Model Discovery -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[12.5px] font-medium text-black/70 dark:text-white/70">
                                Model AI Utama Platform
                            </label>
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                    @click="manageModelsOpen = true"
                                    class="text-[11.5px] font-semibold text-[#AF52DE] hover:underline flex items-center gap-1">
                                    <i data-lucide="layers" class="w-3 h-3"></i>
                                    <span>Bongkar Pasang (<span x-text="modelsList.length"></span>)</span>
                                </button>
                                <button type="button" 
                                    @click="detectModels()"
                                    :disabled="isDetecting"
                                    class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 disabled:opacity-50">
                                    <i data-lucide="refresh-cw" class="w-3 h-3" :class="isDetecting ? 'animate-spin' : ''"></i>
                                    <span x-text="isDetecting ? 'Mendeteksi...' : 'Deteksi Model'"></span>
                                </button>
                            </div>
                        </div>

                        <select name="model" x-model="selectedModel"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 font-mono">
                            <template x-for="m in modelsList" :key="m.id">
                                <option :value="m.id" :selected="m.id === selectedModel" x-text="m.name + ' (' + m.id + ')'"></option>
                            </template>
                            <option value="custom">-- Gunakan Model Kustom Lainnya --</option>
                        </select>

                        <!-- Custom Model input if selected 'custom' -->
                        <div x-show="selectedModel === 'custom'" class="mt-2">
                            <input type="text" name="custom_model" x-model="customModelName" placeholder="Masukkan ID model (misal: cx/gpt-6-astra)"
                                class="w-full h-9 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12.5px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>

                    <!-- Key Rotation Strategy -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Strategi Rotasi Multi-Key Pool
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <label class="p-3 rounded-[10px] border cursor-pointer transition-all flex items-start gap-2.5"
                                :class="rotationStrategy === 'round_robin' 
                                    ? 'bg-[#007AFF]/5 border-[#007AFF] text-black dark:text-white' 
                                    : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="rotation_strategy" value="round_robin" x-model="rotationStrategy" class="mt-0.5 text-[#007AFF]">
                                <div>
                                    <div class="text-[12.5px] font-semibold">Round-Robin Load Balancing</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Bagi rata request secara berimbang ke seluruh API key aktif bergantian.</div>
                                </div>
                            </label>

                            <label class="p-3 rounded-[10px] border cursor-pointer transition-all flex items-start gap-2.5"
                                :class="rotationStrategy === 'failover' 
                                    ? 'bg-[#007AFF]/5 border-[#007AFF] text-black dark:text-white' 
                                    : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                <input type="radio" name="rotation_strategy" value="failover" x-model="rotationStrategy" class="mt-0.5 text-[#007AFF]">
                                <div>
                                    <div class="text-[12.5px] font-semibold">Active-Standby Failover</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Prioritaskan key utama, otomatis alihkan ke key backup jika terkena limit 429.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Panduan Format Provider & Kunci API Langsung di Form -->
                    <div class="rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-[12.5px] font-bold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Panduan Format Provider &amp; Kunci API</span>
                            </h4>
                            <span class="text-[11px] text-black/50 dark:text-white/50">Klik baris untuk auto-fill endpoint</span>
                        </div>

                        <div class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#1C1C1E]">
                            <table class="w-full text-left text-[11.5px]">
                                <thead class="bg-black/[0.03] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold border-b border-black/5 dark:border-white/5">
                                    <tr>
                                        <th class="py-2.5 px-3">Provider AI</th>
                                        <th class="py-2.5 px-3">Pilihan Dropdown</th>
                                        <th class="py-2.5 px-3">Base URL Endpoint API</th>
                                        <th class="py-2.5 px-3">Format API Key &amp; Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                                    <tr @click="applyProviderPreset('openai')" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer transition">
                                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap">OpenAI Official Direct</td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]">OpenAI Official Direct</td>
                                        <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://api.openai.com/v1</td>
                                        <td class="py-2.5 px-3 text-[11px] leading-relaxed">
                                            Format <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded text-[#007AFF]">sk-proj-...</code> dari platform.openai.com. Mendukung GPT-4o, GPT-4o-mini, o1, o3-mini.
                                        </td>
                                    </tr>
                                    <tr @click="applyProviderPreset('gemini')" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer transition">
                                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap">Google Gemini Official</td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]">Google Gemini Official</td>
                                        <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">Otomatis via Google Generative AI REST</td>
                                        <td class="py-2.5 px-3 text-[11px] leading-relaxed">
                                            Format <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded text-[#007AFF]">AIzaSy...</code> dari Google AI Studio. Mendukung Gemini 2.0 Flash, Gemini 1.5 Pro.
                                        </td>
                                    </tr>
                                    <tr @click="applyProviderPreset('anthropic')" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer transition">
                                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap">Anthropic Claude</td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]">Anthropic Claude</td>
                                        <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://api.anthropic.com/v1</td>
                                        <td class="py-2.5 px-3 text-[11px] leading-relaxed">
                                            Format <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded text-[#007AFF]">sk-ant-api03-...</code> dari console.anthropic.com. Mendukung Claude 3.7 Sonnet, Claude 3.5 Haiku.
                                        </td>
                                    </tr>
                                    <tr @click="applyProviderPreset('openrouter')" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer transition">
                                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap">OpenRouter Gateway</td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]">OpenRouter AI Gateway</td>
                                        <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://openrouter.ai/api/v1</td>
                                        <td class="py-2.5 px-3 text-[11px] leading-relaxed">
                                            Format <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded text-[#007AFF]">sk-or-v1-...</code> dari openrouter.ai. Akses ke ratusan model (DeepSeek-R1, Llama 3.3, Qwen, Mistral).
                                        </td>
                                    </tr>
                                    <tr @click="applyProviderPreset('openai_compatible')" class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer transition">
                                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap">OpenAI-Compatible Tunnel / Proxy / vLLM / Ollama</td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]">OpenAI Compatible</td>
                                        <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://r4g77gv.abc-tunnel.us/v1</td>
                                        <td class="py-2.5 px-3 text-[11px] leading-relaxed">
                                            Format <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded text-[#007AFF]">sk-...</code>. Cocok untuk server private AI, vLLM, LiteLLM, Groq-compatible proxy, atau tunnel Cloudflare/Ngrok.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25">
                            <i data-lucide="check" class="w-4 h-4" stroke-width="2"></i>
                            <span>Simpan Konfigurasi Engine</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Multi-Key Pool Management (5 Cols) -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-sm lg:col-span-5 space-y-4">
                <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="key" class="w-4 h-4 text-[#FF9500]" stroke-width="1.75"></i>
                            <span>Multi-Key Pool</span>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]"
                                x-text="activeKeysCount + ' Key'"></span>
                        </h3>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">Banyak API key terhubung untuk 1 provider.</p>
                    </div>
                    <button type="button" 
                        @click="addKeyModalOpen = true"
                        class="p-1.5 rounded-[8px] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors" title="Tambah API Key">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </button>
                </div>

                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem mendistribusikan kueri secara berurutan (*Round-Robin*) ke seluruh key aktif. Jika salah satu key mencapai limit kuota, sistem otomatis beralih ke key berikutnya.
                </p>

                <!-- Keys List -->
                <div class="space-y-3 mt-2">
                    <template x-for="k in maskedKeys" :key="k.id">
                        <div class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2 h-2 rounded-full shrink-0"
                                        :class="k.is_active ? 'bg-[#34C759]' : 'bg-black/30 dark:text-white/30'"></span>
                                    <span class="font-semibold text-[13px] text-black dark:text-white truncate" x-text="k.name"></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Toggle Active Form -->
                                    <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id + '/toggle'" method="POST">
                                        @csrf
                                        <button type="submit" 
                                            class="text-[11px] font-semibold px-2 py-0.5 rounded-[6px] transition-colors"
                                            :class="k.is_active 
                                                ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' 
                                                : 'bg-black/10 dark:bg-white/10 text-black/45 dark:text-white/45 hover:bg-black/15'">
                                            <span x-text="k.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                        </button>
                                    </form>

                                    <!-- Delete Key Form -->
                                    <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id" method="POST"
                                        onsubmit="return confirm('Hapus API Key ini dari pool platform?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-black/40 hover:text-[#FF3B30] transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] font-mono text-black/50 dark:text-white/50 pt-1">
                                <span class="tracking-wider select-all" x-text="k.masked_key"></span>
                                <span class="tabular-nums" x-text="k.total_requests + ' kueri'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Add Key Trigger Button -->
                <button type="button" 
                    @click="addKeyModalOpen = true"
                    class="w-full py-2.5 rounded-[10px] text-[12.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Kunci API Tambahan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 2: MONITORING KONSUMSI TOKEN TENANT (EXISTING MONITORING EXPANDED) -->
    <div x-show="activeTab === 'monitoring'" x-cloak class="space-y-6">

        <!-- KPI Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Token Bulan Ini (MTD)</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensMonth, 0, ',', '.') }}</span>
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Konsumsi bulan berjalan</div>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Token All-Time</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalTokensAllTime, 0, ',', '.') }}</span>
                    <i data-lucide="database" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Akumulasi sejak awal peluncuran</div>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kueri Eksekusi</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($totalQueriesCount, 0, ',', '.') }}</span>
                    <i data-lucide="cpu" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Panggilan API AI berhasil</div>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-sm">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bisnis Aktif Pakai AI</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">{{ number_format($activeAiBusinessesCount, 0, ',', '.') }}</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Workspace memanfaatkan AI bulan ini</div>
            </div>
        </div>

        <!-- 2 Columns: Intent Breakdown & Top Businesses -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 lg:col-span-6 shadow-sm">
                <div class="border-b border-black/5 dark:border-white/10 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
                        <span>Penggunaan Token Berdasarkan Fitur / Intent</span>
                    </h3>
                </div>
                @if ($intentBreakdown->isEmpty())
                    <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">Belum ada data eksekusi AI token tercatat.</div>
                @else
                    <div class="space-y-3 mt-4">
                        @foreach ($intentBreakdown as $intent)
                            @php
                                $pct = $totalTokensAllTime > 0 ? round(($intent->total_tokens / $totalTokensAllTime) * 100, 1) : 0;
                            @endphp
                            <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] text-[13px]">
                                <div class="flex items-center justify-between font-medium">
                                    <span class="text-black dark:text-white capitalize">{{ str_replace('_', ' ', $intent->intent ?? 'general') }}</span>
                                    <span class="text-[#AF52DE] dark:text-[#BF5AF2] font-semibold tabular-nums">{{ number_format((float) $intent->total_tokens, 0, ',', '.') }} Token ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full h-1.5 mt-2 bg-black/[0.08] dark:bg-white/[0.1] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#AF52DE] rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 text-right tabular-nums mt-1">
                                    {{ number_format($intent->queries_count) }} kali eksekusi</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 lg:col-span-6 shadow-sm">
                <div class="border-b border-black/5 dark:border-white/10 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="flame" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                        <span>Top 10 Bisnis Konsumen AI Terbesar</span>
                    </h3>
                </div>
                @if ($topBusinesses->isEmpty())
                    <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">Belum ada data tenant yang menggunakan AI.</div>
                @else
                    <div class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach ($topBusinesses as $index => $top)
                            <div class="py-3 flex items-center justify-between text-[13px] hover:bg-black/[0.02] dark:hover:bg-white/[0.03] px-2 rounded-[10px] transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] font-semibold text-[11px] tabular-nums flex items-center justify-center">{{ $index + 1 }}</span>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">
                                            <a href="{{ route('admin.businesses.show', $top['business_id']) }}"
                                                class="hover:text-[#AF52DE] dark:hover:text-[#BF5AF2] transition-colors">{{ $top['business_name'] }}</a>
                                        </div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                            {{ $top['queries_count'] }} kali kueri</div>
                                    </div>
                                </div>
                                <div class="text-right tabular-nums">
                                    <div class="font-bold text-[#AF52DE] dark:text-[#BF5AF2]">
                                        {{ number_format($top['total_tokens'], 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">token</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity Logs Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
            <div class="px-4 sm:px-5 py-3.5 border-b border-black/5 dark:border-white/10">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                    <span>Log Aktivitas Token AI Terbaru (20 Transaksi Terakhir)</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Waktu</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bisnis / Pengguna</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Intent / Fitur</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Model Engine</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Input / Output</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Total Token</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($recentUsages as $usage)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                                    {{ $usage->created_at?->format('d M H:i:s') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-black dark:text-white">{{ $usage->business_name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $usage->user?->name ?? 'System' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>{{ $usage->intent ?? 'general' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 font-mono">
                                    {{ $usage->model_name ?? 'ag/gemini-3.8-flash-high' }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                                    {{ number_format($usage->input_tokens) }} / {{ number_format($usage->output_tokens) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">
                                    {{ number_format($usage->total_tokens) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center">
                                    <p class="text-[15px] font-semibold text-black dark:text-white">Belum ada aktivitas AI</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada riwayat aktivitas kueri AI tercatat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: TAMBAH API KEY BARU KE POOL -->
    <template x-teleport="body">
        <div x-show="addKeyModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="addKeyModalOpen = false" @keydown.escape.window="addKeyModalOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-md p-6 space-y-5 shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="key" class="w-5 h-5 text-[#007AFF]" stroke-width="1.75"></i>
                        <span>Tambah API Key ke Pool</span>
                    </div>
                    <button type="button" @click="addKeyModalOpen = false"
                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.ai-tokens.engine.keys.add') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Label / Nama Kunci
                        </label>
                        <input type="text" name="key_name" required placeholder="Misal: Tunnel Primary 2 / Backup Key"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Token API Key
                        </label>
                        <input type="password" name="api_key" required placeholder="sk-..."
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Kunci akan disimpan terenkripsi di database (Zero Plaintext Exposure).</p>
                    </div>

                    <!-- Format Guide Box in Modal -->
                    <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 space-y-1.5 text-[11px]">
                        <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Format Token Resmi per Provider:</span>
                        </span>
                        <ul class="space-y-1 text-black/70 dark:text-white/70 font-mono text-[10.5px]">
                            <li>&bull; <strong class="text-black dark:text-white font-sans">OpenAI:</strong> <code class="text-[#007AFF]">sk-proj-...</code> (platform.openai.com)</li>
                            <li>&bull; <strong class="text-black dark:text-white font-sans">Google Gemini:</strong> <code class="text-[#007AFF]">AIzaSy...</code> (aistudio.google.com)</li>
                            <li>&bull; <strong class="text-black dark:text-white font-sans">Anthropic:</strong> <code class="text-[#007AFF]">sk-ant-api03-...</code> (console.anthropic.com)</li>
                            <li>&bull; <strong class="text-black dark:text-white font-sans">OpenRouter:</strong> <code class="text-[#007AFF]">sk-or-v1-...</code> (openrouter.ai)</li>
                            <li>&bull; <strong class="text-black dark:text-white font-sans">Tunnel / Proxy / Local:</strong> <code class="text-[#007AFF]">sk-...</code> (vLLM / Ollama)</li>
                        </ul>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="addKeyModalOpen = false"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Simpan Kunci ke Pool</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: GRANT TOKEN MANUAL SHEET MODAL -->
    @if(Auth::guard('admin')->user()?->hasPermission('admin.ai_tokens.grant'))
    <template x-teleport="body">
        <div x-show="grantModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="grantModalOpen = false" @keydown.escape.window="grantModalOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-md p-6 space-y-5 shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="sparkles" class="w-5 h-5 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
                        <span>Top-Up / Grant Token AI</span>
                    </div>
                    <button type="button" @click="grantModalOpen = false"
                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/ai-tokens') }}/' + selectedBizId + '/grant'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Pilih Bisnis / Tenant</label>
                        <select x-model="selectedBizId" required
                            class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">-- Pilih Bisnis --</option>
                            @foreach ($allBusinesses as $biz)
                                <option value="{{ $biz->id }}">{{ $biz->name }} ({{ $biz->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Jumlah Token</label>
                        <input type="number" name="tokens" x-model="tokenAmount" min="10000" step="10000" required
                            class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Default allowance Core: 10.000.000 token</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alasan / Jejak Audit</label>
                        <textarea name="reason" x-model="reasonText" required rows="2" placeholder="Contoh: Bonus kompensasi migrasi akun / testing"
                            class="w-full px-3 py-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="grantModalOpen = false"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">Batal</button>
                        <button type="submit" :disabled="!selectedBizId || !tokenAmount"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition flex items-center gap-1.5 disabled:opacity-50 shadow-sm shadow-[#AF52DE]/25">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Kirim Token Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
    </template>
    @endif

    <!-- MODAL 3: BONGKAR PASANG MODEL AI (OPENAI COMPATIBLE) -->
    <template x-teleport="body">
        <div x-show="manageModelsOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="manageModelsOpen = false" @keydown.escape.window="manageModelsOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] overflow-hidden">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 p-5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[15px] font-semibold text-black dark:text-white">Bongkar Pasang Model AI</h3>
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]" x-text="modelsList.length + ' Model Terdaftar'"></span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Daftarkan model baru, hapus model tidak terpakai, atau ganti model aktif tanpa hardcode di script.</p>
                        </div>
                    </div>
                    <button type="button" @click="manageModelsOpen = false"
                        class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Modal Action Feedback Banner -->
                <div x-show="modelFeedback.show" x-transition class="mx-5 mt-4 p-3 rounded-[10px] text-[12px] flex items-center justify-between"
                    :class="modelFeedback.success ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 text-[#D70015] dark:text-[#FF453A]'">
                    <span x-text="modelFeedback.message"></span>
                    <button type="button" @click="modelFeedback.show = false" class="opacity-60 hover:opacity-100"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-5 overflow-y-auto space-y-5 flex-1">
                    
                    <!-- Quick Add Model Card (Bongkar Pasang Inline Form) -->
                    <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Pasang Model AI Baru ke Daftar</span>
                            </span>
                            <span class="text-[11px] text-black/45 dark:text-white/45">Bebas ID apapun (OpenAI / Tunnel / Local)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-5">
                                <input type="text" x-model="newModel.id" placeholder="ID Model (misal: cx/gpt-6-astra-xhigh)"
                                    class="w-full h-9 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div class="sm:col-span-4">
                                <input type="text" x-model="newModel.name" placeholder="Nama Label (Opsional)"
                                    class="w-full h-9 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                            <div class="sm:col-span-3">
                                <select x-model="newModel.tier"
                                    class="w-full h-9 px-2 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    <option value="reasoning">Reasoning</option>
                                    <option value="balanced">Balanced</option>
                                    <option value="fast">Fast</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="addModel()" :disabled="isAddingModel || !newModel.id.trim()"
                                class="h-8 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition flex items-center gap-1.5 disabled:opacity-50">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span x-text="isAddingModel ? 'Memasang...' : 'Pasang Model'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Preset Actions: Sync & Reset -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-black/5 dark:border-white/5">
                        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">Daftar Model Terpasang:</div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="detectModels()" :disabled="isDetecting"
                                class="h-7 px-2.5 rounded-[6px] text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1">
                                <i data-lucide="refresh-cw" class="w-3 h-3" :class="isDetecting ? 'animate-spin' : ''"></i>
                                <span>Deteksi Endpoint</span>
                            </button>
                            <button type="button" @click="resetModels()" :disabled="isResettingModels"
                                class="h-7 px-2.5 rounded-[6px] text-[11px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition flex items-center gap-1">
                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                <span>Reset 13 Model Bawaan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Models List -->
                    <div class="divide-y divide-black/[0.04] dark:divide-white/[0.06] border border-black/5 dark:border-white/5 rounded-[12px] overflow-hidden">
                        <template x-for="m in modelsList" :key="m.id">
                            <div class="p-3 bg-white dark:bg-[#1C1C1E] hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors flex items-center justify-between gap-3 text-[12.5px]">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full shrink-0"
                                        :class="m.id === selectedModel ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'"></span>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono text-[12px] font-semibold text-black dark:text-white select-all" x-text="m.id"></span>
                                            <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold uppercase tracking-wider"
                                                :class="{
                                                    'bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2]': m.tier === 'reasoning',
                                                    'bg-[#007AFF]/15 text-[#007AFF]': m.tier === 'balanced',
                                                    'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]': m.tier === 'fast'
                                                }"
                                                x-text="m.tier"></span>
                                            <template x-if="m.id === selectedModel">
                                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Aktif Utama</span>
                                            </template>
                                        </div>
                                        <div class="text-[11.5px] text-black/50 dark:text-white/50 truncate" x-text="m.name || m.id"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" @click="selectActiveModel(m.id)"
                                        class="h-7 px-2.5 rounded-[6px] text-[11px] font-medium transition"
                                        :class="m.id === selectedModel 
                                            ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] cursor-default font-semibold' 
                                            : 'text-[#007AFF] hover:bg-[#007AFF]/10'">
                                        <span x-text="m.id === selectedModel ? 'Aktif' : 'Pilih Model'"></span>
                                    </button>
                                    <button type="button" @click="deleteModel(m.id)"
                                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition"
                                        title="Bongkar / Hapus Model">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01] shrink-0">
                    <span class="text-[11.5px] text-black/45 dark:text-white/45">Model yang dipilih akan tersimpan di konfigurasi platform.</span>
                    <button type="button" @click="manageModelsOpen = false"
                        class="h-8 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-black dark:bg-white dark:text-black hover:opacity-90 transition">
                        Selesai
                    </button>
                </div>

            </div>
        </div>
    </template>
</div>

<script>
function aiEngineConsole(config) {
    return {
        activeTab: config.currentTab || 'engine',
        engineStatus: config.engineConfig.status || 'connected',
        latencyMs: config.engineConfig.latency_ms || 0,
        selectedProvider: config.engineConfig.provider || 'openai_compatible',
        endpointUrl: config.engineConfig.endpoint || 'https://r4g77gv.abc-tunnel.us/v1',
        selectedModel: config.engineConfig.model || 'ag/gemini-3.8-flash-high',
        customModelName: '',
        rotationStrategy: config.engineConfig.rotation_strategy || 'round_robin',
        modelsList: config.modelsList || [],
        maskedKeys: config.engineConfig.masked_keys || [],
        totalKeysCount: config.engineConfig.total_keys_count || 0,
        activeKeysCount: config.engineConfig.active_keys_count || 0,

        isTesting: false,
        isDetecting: false,
        testFeedback: { show: false, success: true, message: '' },

        addKeyModalOpen: false,
        grantModalOpen: false,
        selectedBizId: '',
        tokenAmount: 10000000,
        reasonText: '',

        // Bongkar Pasang Model State
        manageModelsOpen: false,
        newModel: { id: '', name: '', tier: 'balanced' },
        isAddingModel: false,
        isResettingModels: false,
        modelFeedback: { show: false, success: true, message: '' },

        init() {
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

        selectActiveModel(modelId) {
            this.selectedModel = modelId;
            this.modelFeedback.show = true;
            this.modelFeedback.success = true;
            this.modelFeedback.message = `Model aktif dipilih: ${modelId}. Klik 'Simpan Konfigurasi Engine' di form untuk menyimpan secara permanen.`;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        applyProviderPreset(p) {
            this.selectedProvider = p;
            if (p === 'openai') {
                this.endpointUrl = 'https://api.openai.com/v1';
                this.selectedModel = 'gpt-4o-mini';
            } else if (p === 'gemini') {
                this.endpointUrl = 'https://generativelanguage.googleapis.com/v1beta';
                this.selectedModel = 'gemini-2.0-flash';
            } else if (p === 'anthropic') {
                this.endpointUrl = 'https://api.anthropic.com/v1';
                this.selectedModel = 'claude-3-5-haiku-latest';
            } else if (p === 'openrouter') {
                this.endpointUrl = 'https://openrouter.ai/api/v1';
                this.selectedModel = 'deepseek/deepseek-r1';
            } else if (p === 'openai_compatible') {
                this.endpointUrl = 'https://r4g77gv.abc-tunnel.us/v1';
                this.selectedModel = 'ag/gemini-3.8-flash-high';
            }
            this.testFeedback.show = true;
            this.testFeedback.success = true;
            this.testFeedback.message = `Preset endpoint untuk provider '${p}' berhasil disematkan. Klik 'Simpan Konfigurasi Engine' untuk menerapkan.`;
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
            if (!confirm(`Yakin ingin membongkar/menghapus model '${modelId}' dari daftar engine?`)) return;

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
            if (!confirm('Yakin ingin mereset daftar model ke 13 model bawaan tunnel standar?')) return;
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
        },

        async detectModels() {
            this.isDetecting = true;
            try {
                const res = await fetch(config.detectUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        provider: this.selectedProvider,
                        endpoint: this.endpointUrl
                    })
                });

                const data = await res.json();
                if (data.success && data.models && data.models.length > 0) {
                    this.modelsList = data.models;
                    this.testFeedback.show = true;
                    this.testFeedback.success = true;
                    this.testFeedback.message = data.message || `Berhasil mendeteksi ${data.models.length} model aktif dari endpoint tunnel.`;
                }
            } catch (err) {
                this.testFeedback.show = true;
                this.testFeedback.success = false;
                this.testFeedback.message = 'Gagal mendeteksi model: ' + err.message;
            } finally {
                this.isDetecting = false;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        }
    };
}
</script>
@endsection
