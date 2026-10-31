@extends('layouts.admin', [
    'title' => 'Platform AI Engine & Token Console - 9Router Smart Gateway',
    'headerTitle' => 'Platform AI Engine & Token Console',
    'headerSubtitle' => 'Pusat kendali 9Router multi-provider AI, multi-key pool load balancing & failover, dan monitoring token ekosistem',
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

    <!-- Top Navigation Tabs (Apple HIG Segmented Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 self-start">
            <button type="button" 
                @click="switchTab('engine')" 
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2"
                :class="activeTab === 'engine' 
                    ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' 
                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <i data-lucide="router" class="w-4 h-4 text-[#007AFF]" stroke-width="1.75"></i>
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
                <div class="flex flex-wrap items-center gap-2">
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
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]" :class="isTesting{{ __('admin.testing') ? '' : '' }} ? 'animate-spin' : ''" stroke-width="1.75"></i>
                        <span x-text="isTesting{{ __('admin.testing') ? '' : '' }} ? {{ Js::from(__('admin.testing_gateway')) }} : {{ Js::from(__('admin.live_ping_gateway')) }}"></span>
                    </button>
                    <button type="button" 
                        @click="openAddKeyModal()"
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                        <span>{{ __('admin.hubungkan_kunci_api') }}</span>
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

    <!-- TAB 1: 9ROUTER AI SMART GATEWAY & PROVIDER POOL -->
    <div x-show="activeTab === 'engine'" x-cloak class="space-y-6">

        <!-- 1. 9ROUTER ARCHITECTURE & VISUAL TOPOLOGY HEADER -->
        <div class="relative overflow-hidden rounded-[20px] bg-gradient-to-br from-[#0B0F19] via-[#111827] to-[#1E1B4B] text-white p-6 sm:p-7 shadow-lg border border-white/10">
            <!-- Background Decorative Glow -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#AF52DE]/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-6">
                <!-- Header Badge & Status -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[14px] bg-white/10 border border-white/15 flex items-center justify-center text-[#38BDF8] shadow-inner shrink-0">
                            <i data-lucide="router" class="w-6 h-6" stroke-width="2"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h1 class="text-[20px] font-bold tracking-tight text-white flex items-center gap-2">
                                    <span>9Router Smart Gateway</span>
                                    <span class="text-[12px] font-mono px-2 py-0.5 rounded-full bg-[#007AFF]/25 text-[#38BDF8] border border-[#007AFF]/40">v2.5 Hybrid Router</span>
                                </h1>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider"
                                    :class="engineStatus === 'connected' 
                                        ? 'bg-[#34C759]/20 text-[#4ADE80] border border-[#34C759]/40' 
                                        : 'bg-[#FF9500]/20 text-[#FBBF24] border border-[#FF9500]/40'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="engineStatus === 'connected' ? 'bg-[#4ADE80] animate-pulse' : 'bg-[#FBBF24]'"></span>
                                    <span x-text="engineStatus === 'connected' ? 'Gateway Online & Terverifikasi' : 'Menunggu Verifikasi'"></span>
                                </span>
                            </div>
                            <p class="text-[13px] text-white/70 mt-1 leading-relaxed">
                                Smart routing layer anti *rate-limit* dengan auto-fallback 3 tier. Menggabungkan banyak provider AI dalam satu endpoint cerdas.
                            </p>
                        </div>
                    </div>

                    <!-- Action Live Test Button in Banner -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button" @click="testConnection()" :disabled="isTesting"
                            class="px-4 py-2 rounded-[10px] text-[12.5px] font-semibold text-white bg-white/10 hover:bg-white/15 active:scale-[0.98] transition border border-white/15 flex items-center gap-2 disabled:opacity-50">
                            <i data-lucide="activity" class="w-3.5 h-3.5 text-[#38BDF8]" :class="isTesting{{ __('admin.testing') ? '' : '' }} ? 'animate-spin' : ''"></i>
                            <span x-text="isTesting{{ __('admin.testing') ? '' : '' }} ? {{ Js::from(__('admin.testing_gateway')) }} : {{ Js::from(__('admin.live_ping_all_providers')) }}"></span>
                        </button>
                        <button type="button" @click="openAddKeyModal()"
                            class="px-4 py-2 rounded-[10px] text-[12.5px] font-semibold text-black bg-[#38BDF8] hover:bg-[#0EA5E9] active:scale-[0.98] transition flex items-center gap-1.5 shadow-md shadow-[#38BDF8]/20">
                            <i data-lucide="plus" class="w-4 h-4" stroke-width="2.25"></i>
                            <span>{{ __('admin.hubungkan_kunci_api_2') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Visual 9Router Routing Pipeline Flow -->
                <div class="p-4 rounded-[14px] bg-black/30 border border-white/10 backdrop-blur-sm">
                    <div class="flex items-center justify-between text-[11px] font-medium text-white/50 mb-3 px-1">
                        <span>TOPOLOGI ALUR TRAFIK 9ROUTER (REQUEST PIPELINE)</span>
                        <span class="text-[#38BDF8] font-mono">Auto-Failover Active</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2.5 text-center items-center">
                        <!-- Step 1: Apps -->
                        <div class="p-3 rounded-[12px] bg-white/[0.06] border border-white/10">
                            <div class="w-7 h-7 rounded-full bg-[#AF52DE]/20 text-[#C084FC] mx-auto flex items-center justify-center mb-1.5">
                                <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="text-[12px] font-bold text-white">1. Client Apps</div>
                            <div class="text-[10px] text-white/60">POS, Inbox, CRM, Analytics</div>
                        </div>

                        <!-- Connector 1 -->
                        <div class="hidden md:flex items-center justify-center text-white/30">
                            <i data-lucide="arrow-right" class="w-4 h-4 animate-pulse text-[#38BDF8]"></i>
                        </div>

                        <!-- Step 2: 9Router Smart Layer -->
                        <div class="p-3 rounded-[12px] bg-[#007AFF]/20 border border-[#007AFF]/40 shadow-sm shadow-[#007AFF]/20">
                            <div class="w-7 h-7 rounded-full bg-[#007AFF]/30 text-[#38BDF8] mx-auto flex items-center justify-center mb-1.5">
                                <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="text-[12px] font-bold text-[#38BDF8]">2. 9Router Gateway</div>
                            <div class="text-[10px] text-white/80" x-text="rotationStrategy === 'failover' ? '3-Tier Auto-Fallback' : 'Round-Robin Balancing'"></div>
                        </div>

                        <!-- Connector 2 -->
                        <div class="hidden md:flex items-center justify-center text-white/30">
                            <i data-lucide="arrow-right" class="w-4 h-4 animate-pulse text-[#38BDF8]"></i>
                        </div>

                        <!-- Step 3: Multi-Key Channels -->
                        <div class="p-3 rounded-[12px] bg-white/[0.06] border border-white/10">
                            <div class="w-7 h-7 rounded-full bg-[#34C759]/20 text-[#4ADE80] mx-auto flex items-center justify-center mb-1.5">
                                <i data-lucide="key" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="text-[12px] font-bold text-white">3. Multi-Key Channels</div>
                            <div class="text-[10px] text-white/60" x-text="activeKeysCount + ' Kunci Aktif Terhubung'"></div>
                        </div>
                    </div>
                </div>

                <!-- 4 KPI Metrics Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-white">
                    <div class="p-3.5 rounded-[12px] bg-white/[0.05] border border-white/10">
                        <div class="text-[11px] text-white/60 font-medium">Provider Terhubung</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-[20px] font-bold tracking-tight text-white font-mono" x-text="connectedProvidersCount"></span>
                            <span class="text-[12px] text-white/50">/ 5 Provider</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[12px] bg-white/[0.05] border border-white/10">
                        <div class="text-[11px] text-white/60 font-medium">Total Kunci API di Pool</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-[20px] font-bold tracking-tight text-[#4ADE80] font-mono" x-text="activeKeysCount"></span>
                            <span class="text-[12px] text-white/50" x-text="'/ ' + totalKeysCount + ' Total Key'"></span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[12px] bg-white/[0.05] border border-white/10">
                        <div class="text-[11px] text-white/60 font-medium">Rata-rata Latensi Global</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-[20px] font-bold tracking-tight text-[#38BDF8] font-mono" x-text="latencyMs > 0 ? latencyMs + ' ms' : 'Optimal'"></span>
                            <span class="text-[12px] text-white/50">live ping</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[12px] bg-white/[0.05] border border-white/10">
                        <div class="text-[11px] text-white/60 font-medium">Trafik Kueri Ter-Rute</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-[20px] font-bold tracking-tight text-[#C084FC] font-mono" x-text="totalRoutedRequests"></span>
                            <span class="text-[12px] text-white/50">eksekusi sukses</span>
                        </div>
                    </div>
                </div>

                <!-- Test Feedback Alert -->
                <div x-show="testFeedback.show" x-transition class="p-3.5 rounded-[12px] text-[12.5px] flex items-start gap-3"
                    :class="testFeedback.success 
                        ? 'bg-[#34C759]/20 text-[#4ADE80] border border-[#34C759]/40' 
                        : 'bg-[#FF3B30]/20 text-[#F87171] border border-[#FF3B30]/40'">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                        <span class="font-bold" x-text="testFeedback.success ? {{ Js::from(__('admin.gateway_test_results')) }} + ':' : {{ Js::from(__('admin.failed_contacting_gateway')) }} + ':'"></span>
                        <span class="ml-1" x-text="testFeedback.message"></span>
                    </div>
                    <button type="button" @click="testFeedback.show = false" class="opacity-70 hover:opacity-100">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. CONNECTED PROVIDERS MATRIX (VISUALISASI API AI YANG TERHUBUNG) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="cloud-lightning" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Visualisasi Provider AI Terhubung (Connected Channels)</span>
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Status konektivitas, ketersediaan kunci, endpoint resmi, dan penggunaan kueri per provider AI.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5">
                <template x-for="(pStat, pId) in providersStats" :key="pId">
                    <div class="rounded-[16px] p-4 bg-white dark:bg-[#1C1C1E] border transition-all duration-150 flex flex-col justify-between shadow-sm relative group"
                        :class="pStat.is_connected 
                            ? 'border-[#007AFF]/30 dark:border-[#007AFF]/30 hover:border-[#007AFF]' 
                            : 'border-black/5 dark:border-white/5 opacity-80 hover:opacity-100'">
                        
                        <div>
                            <!-- Provider Header & Badge -->
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center text-white shrink-0 font-bold text-[12px]"
                                        :class="{
                                            'bg-[#007AFF]': pId === 'openai_compatible',
                                            'bg-[#D97706]': pId === 'anthropic',
                                            'bg-[#8B5CF6]': pId === 'gemini',
                                            'bg-[#10B981]': pId === 'openai',
                                            'bg-[#06B6D4]': pId === 'openrouter'
                                        }">
                                        <template x-if="pId === 'openai_compatible'"><i data-lucide="server" class="w-4 h-4"></i></template>
                                        <template x-if="pId === 'anthropic'"><i data-lucide="sparkles" class="w-4 h-4"></i></template>
                                        <template x-if="pId === 'gemini'"><i data-lucide="zap" class="w-4 h-4"></i></template>
                                        <template x-if="pId === 'openai'"><i data-lucide="cpu" class="w-4 h-4"></i></template>
                                        <template x-if="pId === 'openrouter'"><i data-lucide="share-2" class="w-4 h-4"></i></template>
                                    </div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-black dark:text-white leading-tight" x-text="pStat.short_name"></h4>
                                        <span class="text-[10.5px] text-black/50 dark:text-white/50" x-text="pStat.badge"></span>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider shrink-0"
                                    :class="pStat.is_connected 
                                        ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' 
                                        : 'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40'">
                                    <span x-text="pStat.is_connected ? (pStat.active_keys + ' Key') : 'Belum Ada'"></span>
                                </span>
                            </div>

                            <!-- Provider Description -->
                            <p class="text-[11px] text-black/60 dark:text-white/60 line-clamp-2 mb-3 leading-relaxed" x-text="pStat.description"></p>

                            <!-- Endpoint & Model Info -->
                            <div class="space-y-1.5 text-[10.5px] font-mono text-black/50 dark:text-white/50 p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 mb-3">
                                <div class="truncate">
                                    <span class="text-black/40 dark:text-white/40 font-sans">Model:</span>
                                    <span class="text-black/80 dark:text-white/80 font-semibold" x-text="pStat.default_model"></span>
                                </div>
                                <div class="truncate">
                                    <span class="text-black/40 dark:text-white/40 font-sans">Endpoint:</span>
                                    <span class="text-[#007AFF] select-all" x-text="pStat.default_endpoint"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                            <span class="text-[11px] text-black/60 dark:text-white/60 tabular-nums">
                                <strong class="text-black dark:text-white font-semibold" x-text="pStat.total_requests"></strong> kueri
                            </span>

                            <button type="button" @click="openAddKeyModal(pId)"
                                class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Kunci</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. KELOLA MASING-MASING API KEY TERHUBUNG & PROVIDERNYA (GATEWAY CHANNELS) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-sm space-y-5">
            <!-- Header & Filter Bar -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="key" class="w-5 h-5 text-[#FF9500]"></i>
                        <span>Multi-Key Pool &amp; Gateway Channels (Kelola Kunci API &amp; Provider)</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF]"
                            x-text="maskedKeys.length + ' Kunci Terdaftar'"></span>
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Setiap kunci API dapat diatur providernya, endpoint URL kustom, model yang dilayani, status, tier failover, dan diuji ping latensinya secara individual.
                    </p>
                </div>

                <!-- Add Key Trigger Button -->
                <button type="button" @click="openAddKeyModal()"
                    class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25 self-start lg:self-auto">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>+ Tambah Kunci API Baru</span>
                </button>
            </div>

            <!-- Filter Tabs & Search Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Provider & Purpose Filter Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-[12px]">
                    <!-- Purpose Quick Switcher -->
                    <div class="flex items-center gap-1 p-0.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] mr-1.5 shrink-0">
                        <button type="button" @click="purposeFilter = 'all'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold transition-all"
                            :class="purposeFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60'">{{ __('common.all') }}</button>
                        <button type="button" @click="purposeFilter = 'chat'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold transition-all flex items-center gap-1"
                            :class="purposeFilter === 'chat' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60'">
                            <span>💬 Chatbot</span>
                        </button>
                        <button type="button" @click="purposeFilter = 'image'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold transition-all flex items-center gap-1"
                            :class="purposeFilter === 'image' ? 'bg-[#AF52DE] text-white shadow-xs' : 'text-black/60 dark:text-white/60'">
                            <span>🎨 AI Studio</span>
                        </button>
                    </div>

                    <button type="button" @click="keyFilter = 'all'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0"
                        :class="keyFilter === 'all' 
                            ? 'bg-black text-white dark:bg-white dark:text-black shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        {{ __('admin.all_providers') }} (<span x-text="maskedKeys.length"></span>)
                    </button>
                    <button type="button" @click="keyFilter = 'openai_compatible'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0 flex items-center gap-1"
                        :class="keyFilter === 'openai_compatible' 
                            ? 'bg-[#007AFF] text-white shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        <span>Tunnel / vLLM</span>
                    </button>
                    <button type="button" @click="keyFilter = 'anthropic'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0 flex items-center gap-1"
                        :class="keyFilter === 'anthropic' 
                            ? 'bg-[#D97706] text-white shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        <span>Anthropic Claude</span>
                    </button>
                    <button type="button" @click="keyFilter = 'gemini'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0 flex items-center gap-1"
                        :class="keyFilter === 'gemini' 
                            ? 'bg-[#8B5CF6] text-white shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        <span>Google Gemini</span>
                    </button>
                    <button type="button" @click="keyFilter = 'openai'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0 flex items-center gap-1"
                        :class="keyFilter === 'openai' 
                            ? 'bg-[#10B981] text-white shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        <span>OpenAI</span>
                    </button>
                    <button type="button" @click="keyFilter = 'openrouter'"
                        class="px-3 py-1.5 rounded-[8px] font-semibold transition-all shrink-0 flex items-center gap-1"
                        :class="keyFilter === 'openrouter' 
                            ? 'bg-[#06B6D4] text-white shadow-xs' 
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                        <span>OpenRouter</span>
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="keySearch" placeholder="{{ __('admin.cari_nama_model_endpoint') }}"
                        class="w-full h-8 pl-8 pr-3 text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 rounded-[8px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <div class="absolute left-2.5 top-2 text-black/40 dark:text-white/40">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            <!-- Keys List Grid -->
            <div class="space-y-3">
                <template x-for="k in filteredKeys()" :key="k.id">
                    <div class="rounded-[16px] p-4 bg-black/[0.02] dark:bg-white/[0.03] border transition-all duration-150 space-y-3.5"
                        :class="{
                            'border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/15': k.is_active,
                            'border-black/5 dark:border-white/5 opacity-60 bg-black/[0.01] dark:bg-white/[0.01]': !k.is_active
                        }">
                        
                        <!-- Row 1: Provider, Name, Priority Tier, Status, Actions -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <!-- Provider Badge -->
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white flex items-center gap-1 shadow-xs"
                                    :class="{
                                        'bg-[#007AFF]': k.provider === 'openai_compatible',
                                        'bg-[#D97706]': k.provider === 'anthropic',
                                        'bg-[#8B5CF6]': k.provider === 'gemini',
                                        'bg-[#10B981]': k.provider === 'openai',
                                        'bg-[#06B6D4]': k.provider === 'openrouter'
                                    }">
                                    <template x-if="k.provider === 'openai_compatible'"><i data-lucide="server" class="w-3 h-3"></i></template>
                                    <template x-if="k.provider === 'anthropic'"><i data-lucide="sparkles" class="w-3 h-3"></i></template>
                                    <template x-if="k.provider === 'gemini'"><i data-lucide="zap" class="w-3 h-3"></i></template>
                                    <template x-if="k.provider === 'openai'"><i data-lucide="cpu" class="w-3 h-3"></i></template>
                                    <template x-if="k.provider === 'openrouter'"><i data-lucide="share-2" class="w-3 h-3"></i></template>
                                    <span x-text="k.provider_meta?.short_name || k.provider"></span>
                                </span>

                                <!-- Key Name -->
                                <h4 class="text-[14px] font-bold text-black dark:text-white" x-text="k.name"></h4>

                                <!-- Priority Tier Badge (9Router 3-Tier Fallback) -->
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-semibold border"
                                    :class="{
                                        'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20': k.priority === 1,
                                        'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20': k.priority === 2,
                                        'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 border-black/10 dark:border-white/10': k.priority === 3
                                    }">
                                    <span x-text="k.priority === 1 ? 'Tier 1: Utama' : (k.priority === 2 ? 'Tier 2: Cadangan' : 'Tier 3: Fallback')"></span>
                                </span>

                                <!-- Purpose / Peruntukan Badge -->
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold border flex items-center gap-1"
                                    :class="{
                                        'bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] border-[#AF52DE]/20': k.purpose === 'image',
                                        'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border-[#007AFF]/20': k.purpose === 'chat' || !k.purpose,
                                        'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20': k.purpose === 'all'
                                    }">
                                    <span x-text="k.purpose === 'image' ? '🎨 Khusus Gambar' : (k.purpose === 'all' ? '🌐 Universal' : '💬 Khusus Chatbot')"></span>
                                </span>

                                <!-- Health / Status Indicator -->
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold"
                                    :class="{
                                        'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]': k.is_active && k.status === 'healthy',
                                        'bg-[#FF9500]/15 text-[#C97700] dark:text-[#FF9F0A]': k.is_active && k.status === 'degraded',
                                        'bg-[#FF3B30]/15 text-[#D70015] dark:text-[#FF453A]': k.is_active && k.status === 'error',
                                        'bg-black/10 dark:bg-white/10 text-black/45 dark:text-white/45': !k.is_active
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-[#34C759] animate-pulse': k.is_active && k.status === 'healthy',
                                            'bg-[#FF9500]': k.is_active && k.status === 'degraded',
                                            'bg-[#FF3B30]': k.is_active && k.status === 'error',
                                            'bg-black/40 dark:bg-white/40': !k.is_active
                                        }"></span>
                                    <span x-text="k.is_active ? (k.status === 'healthy' ? 'Online' : (k.status === 'degraded' ? 'Degraded' : 'Error')) : 'Nonaktif'"></span>
                                </span>

                                <!-- Live Ping Latency Badge if Available -->
                                <template x-if="k.latency_ms > 0">
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                        <span x-text="k.latency_ms + ' ms'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Test Single Key Live Ping -->
                                <button type="button" @click="testSingleKey(k.id)" :disabled="testingKeyId === k.id"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/75 dark:text-white/80 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.98] transition flex items-center gap-1 shadow-xs disabled:opacity-50"
                                    title="{{ __('admin.uji_koneksi_khusus_untuk_key_ini') }}">
                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FF9500]" :class="testingKeyId{{ __('admin.testing') ? '' : '' }} === k.id ? 'animate-spin' : ''"></i>
                                    <span x-text="testingKeyId{{ __('admin.testing') ? '' : '' }} === k.id ? {{ Js::from(__('admin.testing')) }} : {{ Js::from(__('admin.ping_test')) }}"></span>
                                </button>

                                <!-- Edit Key Modal Trigger -->
                                <button type="button" @click="openEditKeyModal(k)"
                                    class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center gap-1"
                                    title="{{ __('admin.edit_konfigurasi_key_ini') }}">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('common.edit') }}</span>
                                </button>

                                <!-- Toggle Active Form -->
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id + '/toggle'" method="POST">
                                    @csrf
                                    <button type="submit" 
                                        class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold transition"
                                        :class="k.is_active 
                                            ? 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 hover:bg-black/10' 
                                            : 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25'">
                                        <span x-text="k.is_active ? 'Jeda' : 'Aktifkan'"></span>
                                    </button>
                                </form>

                                <!-- Delete Key Form -->
                                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + k.id" method="POST"
                                    onsubmit="return confirm('Hapus API Key ini dari pool platform?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center" title="{{ __('admin.hapus_kunci') }}">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Row 2: Masked Token with Copy & Reveal, Endpoint URL, Assigned Model, Usage Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-2 border-t border-black/5 dark:border-white/5 text-[11.5px]">
                            <!-- Token & Copy (4 Cols) -->
                            <div class="md:col-span-4 flex items-center gap-2">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Token:</span>
                                <div class="px-2.5 py-1 rounded-[6px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 font-mono text-[11px] text-black/80 dark:text-white/80 flex items-center justify-between gap-2 flex-1 min-w-0">
                                    <span class="truncate select-all" x-text="revealedTokens[k.id] ? k.key : k.masked_key"></span>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="copyText(k.masked_key, k.id)" class="text-black/40 hover:text-[#007AFF] transition" title="Salin Token">
                                            <i data-lucide="copy" class="w-3 h-3" x-show="copiedTokenId !== k.id"></i>
                                            <i data-lucide="check" class="w-3 h-3 text-[#34C759]" x-show="copiedTokenId === k.id"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Endpoint URL (4 Cols) -->
                            <div class="md:col-span-4 flex items-center gap-2 min-w-0">
                                <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Endpoint:</span>
                                <span class="font-mono text-[#007AFF] text-[11px] truncate select-all" x-text="k.endpoint"></span>
                            </div>

                            <!-- Assigned Model (4 Cols) -->
                            <div class="md:col-span-4 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-black/45 dark:text-white/45 shrink-0 font-medium">Model:</span>
                                    <span class="font-mono font-semibold text-black dark:text-white text-[11px] truncate" x-text="k.model || 'Default Platform'"></span>
                                </div>
                                <div class="text-right shrink-0 tabular-nums text-black/60 dark:text-white/60">
                                    <strong class="text-black dark:text-white" x-text="k.total_requests"></strong> kueri
                                    <template x-if="k.total_errors > 0">
                                        <span class="text-[#FF3B30] font-semibold" x-text="'(' + k.total_errors + ' err)'"></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Live Ping Feedback Banner for This Key -->
                        <div x-show="pingFeedback[k.id]?.show" x-transition class="p-2.5 rounded-[8px] text-[11.5px] flex items-center justify-between gap-2"
                            :class="pingFeedback[k.id]?.success 
                                ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' 
                                : 'bg-[#FF3B30]/10 text-[#D70015] dark:text-[#FF453A] border border-[#FF3B30]/20'">
                            <div class="flex items-center gap-2">
                                <i data-lucide="zap" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="font-bold" x-text="pingFeedback[k.id]?.success ? 'Ping Berhasil (' + pingFeedback[k.id]?.latency_ms + 'ms):' : 'Ping Gagal:'"></span>
                                <span x-text="pingFeedback[k.id]?.message"></span>
                            </div>
                            <button type="button" @click="pingFeedback[k.id].show = false" class="opacity-60 hover:opacity-100">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="filteredKeys().length === 0">
                    <div class="p-8 text-center rounded-[16px] border border-dashed border-black/10 dark:border-white/10 space-y-2">
                        <i data-lucide="key" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] font-semibold text-black dark:text-white">Tidak ada kunci API yang cocok dengan filter</p>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50">Coba ubah kata kunci pencarian atau hubungkan kunci baru.</p>
                        <button type="button" @click="openAddKeyModal()"
                            class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Kunci API</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- 4. PENGATURAN ROUTER GATEWAY & STRATEGI FAILOVER (ENGINE CONFIG FORM) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-sm space-y-5">
            <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Pengaturan Global Smart Router &amp; Fallback Strategy</span>
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Tentukan strategi rotasi kuota, model default platform, dan endpoint default jika tidak dispesifikasikan oleh key.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.ai-tokens.engine.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Key Rotation & Fallback Strategy (9Router Style) -->
                <div>
                    <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Strategi Rotasi &amp; Auto-Fallback 9Router
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3.5 rounded-[12px] border cursor-pointer transition-all flex items-start gap-3"
                            :class="rotationStrategy === 'failover' 
                                ? 'bg-[#007AFF]/5 border-[#007AFF] text-black dark:text-white ring-1 ring-[#007AFF]' 
                                : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                            <input type="radio" name="rotation_strategy" value="failover" x-model="rotationStrategy" class="mt-0.5 text-[#007AFF]">
                            <div>
                                <div class="text-[13px] font-bold flex items-center gap-1.5">
                                    <span>3-Tier Priority Auto-Fallback (9Router Style)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Rekomendasi</span>
                                </div>
                                <div class="text-[11.5px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Prioritaskan Tier 1 (Utama). Jika terkena limit kuota (429) atau error, gateway otomatis mengalihkan request ke Tier 2 (Cadangan) lalu Tier 3 (Fallback) tanpa membuat coding tool/tenant terputus!
                                </div>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-[12px] border cursor-pointer transition-all flex items-start gap-3"
                            :class="rotationStrategy === 'round_robin' 
                                ? 'bg-[#007AFF]/5 border-[#007AFF] text-black dark:text-white ring-1 ring-[#007AFF]' 
                                : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                            <input type="radio" name="rotation_strategy" value="round_robin" x-model="rotationStrategy" class="mt-0.5 text-[#007AFF]">
                            <div>
                                <div class="text-[13px] font-bold">Round-Robin Load Balancing</div>
                                <div class="text-[11.5px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Bagi rata setiap panggilan API secara bergiliran ke seluruh kunci API yang aktif di dalam pool untuk menyeimbangkan beban trafik.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Default Platform Model -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[12.5px] font-medium text-black/70 dark:text-white/70">
                                Model Acuan Default Platform
                            </label>
                            <button type="button" @click="manageModelsOpen = true"
                                class="text-[11.5px] font-semibold text-[#AF52DE] hover:underline flex items-center gap-1">
                                <i data-lucide="layers" class="w-3 h-3"></i>
                                <span>{{ __('admin.manage_models') }} (<span x-text="modelsList.length"></span>)</span>
                            </button>
                        </div>
                        <select name="model" x-model="selectedModel"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 font-mono">
                            <template x-for="m in modelsList" :key="m.id">
                                <option :value="m.id" :selected="m.id === selectedModel" x-text="m.name + ' (' + m.id + ')'"></option>
                            </template>
                            <option value="custom">-- Gunakan Model Kustom Lainnya --</option>
                        </select>
                        <div x-show="selectedModel === 'custom'" class="mt-2">
                            <input type="text" name="custom_model" x-model="customModelName" placeholder="Masukkan ID model (misal: cx/gpt-6-astra)"
                                class="w-full h-9 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12.5px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>

                    <!-- Default Provider Engine (Fallback) -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Default Driver Provider (Fallback Global)
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
                </div>

                <!-- Default Endpoint URL -->
                <div>
                    <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                        <span>Default Base URL Endpoint API (Fallback)</span>
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
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition flex items-center gap-2 shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="check" class="w-4 h-4" stroke-width="2"></i>
                        <span>{{ __('admin.simpan_konfigurasi_router_gateway') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 5. PANDUAN FORMAT & PRESET PROVIDER 9ROUTER -->
        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-[13px] font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Panduan Preset Format Token &amp; Endpoint Provider 9Router</span>
                </h4>
                <span class="text-[11px] text-black/50 dark:text-white/50">Klik baris untuk mengisi konfigurasi otomatis</span>
            </div>

            <div class="overflow-x-auto rounded-[12px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#1C1C1E]">
                <table class="w-full text-left text-[11.5px]">
                    <thead class="bg-black/[0.03] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold border-b border-black/5 dark:border-white/5">
                        <tr>
                            <th class="py-2.5 px-3">Provider AI</th>
                            <th class="py-2.5 px-3">Base URL Endpoint API</th>
                            <th class="py-2.5 px-3">Format API Key &amp; Model Unggulan</th>
                            <th class="py-2.5 px-3 text-right">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                <span>OpenAI-Compatible Tunnel / vLLM / Proxy</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://r4g77gv.abc-tunnel.us/v1</td>
                            <td class="py-2.5 px-3 text-[11px]">Format <code class="font-mono text-[#007AFF]">sk-...</code>. Cocok untuk server private AI, LiteLLM, vLLM, Ollama, proxy Groq.</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('openai_compatible')" class="text-[#007AFF] font-semibold hover:underline">{{ __('admin.hubungkan') }}</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#D97706]"></span>
                                <span>Anthropic Claude Official</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://api.anthropic.com/v1</td>
                            <td class="py-2.5 px-3 text-[11px]">Format <code class="font-mono text-[#007AFF]">sk-ant-api03-...</code> dari console.anthropic.com. Mendukung Claude 3.7 Sonnet, Claude 3.5 Haiku.</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('anthropic')" class="text-[#D97706] font-semibold hover:underline">{{ __('admin.hubungkan') }}</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                                <span>Google Gemini Official</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://generativelanguage.googleapis.com/v1beta</td>
                            <td class="py-2.5 px-3 text-[11px]">Format <code class="font-mono text-[#007AFF]">AIzaSy...</code> dari Google AI Studio. Mendukung Gemini 2.0 Flash, Gemini 1.5 Pro.</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('gemini')" class="text-[#8B5CF6] font-semibold hover:underline">{{ __('admin.hubungkan') }}</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                <span>OpenAI Official Direct</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://api.openai.com/v1</td>
                            <td class="py-2.5 px-3 text-[11px]">Format <code class="font-mono text-[#007AFF]">sk-proj-...</code> dari platform.openai.com. Mendukung GPT-4o, GPT-4o-mini, o1, o3-mini.</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('openai')" class="text-[#10B981] font-semibold hover:underline">{{ __('admin.hubungkan') }}</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white whitespace-nowrap flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#06B6D4]"></span>
                                <span>OpenRouter AI Gateway</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[#007AFF] text-[11px] select-all">https://openrouter.ai/api/v1</td>
                            <td class="py-2.5 px-3 text-[11px]">Format <code class="font-mono text-[#007AFF]">sk-or-v1-...</code> dari openrouter.ai. Akses ke DeepSeek-R1, Llama 3.3, Qwen, Mistral.</td>
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" @click="openAddKeyModal('openrouter')" class="text-[#06B6D4] font-semibold hover:underline">{{ __('admin.hubungkan') }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 2: MONITORING KONSUMSI TOKEN TENANT (PRESERVED) -->
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
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Bisnis Aktif Menggunakan AI</span>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ number_format($activeAiBusinessesCount, 0, ',', '.') }}</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-2">Tenant aktif bulan ini</div>
            </div>
        </div>

        <!-- 2 Columns Grid: Usage by Intent & Top Consuming Businesses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Usage by Intent Breakdown -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-[#007AFF]" stroke-width="1.5"></i>
                        <span>{{ __('admin.distribusi_konsumsi_token_per_fitur_intent') }}</span>
                    </h3>
                </div>
                @if ($intentBreakdown->isEmpty())
                    <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">{{ __('admin.belum_ada_riwayat_konsumsi_token_tercatat') }}</div>
                @else
                    <div class="space-y-3">
                        @foreach ($intentBreakdown as $intent)
                            @php
                                $percent = $totalTokensAllTime > 0 ? round(($intent->total_tokens / $totalTokensAllTime) * 100, 1) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="font-medium text-black dark:text-white capitalize flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[#AF52DE]"></span>
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

            <!-- Top 10 Consuming Businesses -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-4 h-4 text-[#FF9500]" stroke-width="1.5"></i>
                        <span>Top 10 Tenant Konsumsi Token Tertinggi</span>
                    </h3>
                </div>
                @if ($topBusinesses->isEmpty())
                    <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">{{ __('admin.belum_ada_data_tenant_yang_menggunakan') }}</div>
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
                                    <p class="text-[15px] font-semibold text-black dark:text-white">{{ __('admin.belum_ada_aktivitas_ai') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">{{ __('admin.belum_ada_riwayat_aktivitas_kueri_ai') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: TAMBAH API KEY BARU KE POOL (9ROUTER STYLE) -->
    <template x-teleport="body">
        <div x-show="addKeyModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="addKeyModalOpen = false" @keydown.escape.window="addKeyModalOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-lg p-6 space-y-5 shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="key" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Hubungkan Kunci API Baru ke Gateway 9Router</span>
                    </div>
                    <button type="button" @click="addKeyModalOpen = false"
                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.ai-tokens.engine.keys.add') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Provider Selector -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Pilih AI Provider Engine
                        </label>
                        <select name="provider" x-model="newKeyForm.provider" @change="onAddKeyProviderChange()" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM / Proxy / AI Studio)</option>
                            <option value="openai">OpenAI Official Direct (DALL-E 3 &amp; GPT - api.openai.com)</option>
                            <option value="anthropic">Anthropic Claude Official (api.anthropic.com)</option>
                            <option value="gemini">Google Gemini Official (Google AI Studio)</option>
                            <option value="openrouter">OpenRouter AI Gateway (openrouter.ai)</option>
                            <option value="ideogram">Ideogram AI Official (api.ideogram.ai - Image Studio)</option>
                            <option value="stability">Stability AI / Flux Pro (api.stability.ai - Image Studio)</option>
                        </select>
                    </div>

                    <!-- Key Name & Priority Tier in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Label Nama Kunci
                            </label>
                            <input type="text" name="key_name" x-model="newKeyForm.name" required placeholder="Misal: Claude 3.7 Primary / Tunnel 1"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Tier Prioritas Failover
                            </label>
                            <select name="priority" x-model.number="newKeyForm.priority"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="1">Tier 1: Utama (Primary Traffic)</option>
                                <option value="2">Tier 2: Cadangan (Standby Backup)</option>
                                <option value="3">Tier 3: Darurat (Emergency Fallback)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Peruntukan Kunci (Isolasi Chatbot vs Image Generator) -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                            <span>Peruntukan Kunci AI (Pemisahan Kuota)</span>
                            <span class="text-[11px] font-bold text-[#FF9500]">Isolasi Ketat</span>
                        </label>
                        <select name="purpose" x-model="newKeyForm.purpose" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="chat">💬 Khusus Chatbot &amp; Analisis Teks (WhatsApp CS, Live Chat, Prompts)</option>
                            <option value="image">🎨 Khusus AI Studio &amp; Generasi Gambar (DALL-E 3, Flux, Visual Studio)</option>
                            <option value="all">🌐 Universal (Bisa Chatbot &amp; Generasi Gambar)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                            Kunci khusus gambar dipisahkan ketat agar render visual resolusi tinggi tidak mengganggu kuota / rate limit chatbot.
                        </p>
                    </div>

                    <!-- Token API Key -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Token API Key
                        </label>
                        <input type="password" name="api_key" x-model="newKeyForm.api_key" required placeholder="sk-..."
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Disimpan terenkripsi di database aman (Zero Plaintext Exposure).</p>
                    </div>

                    <!-- Base URL Endpoint & Model in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Base URL Endpoint
                            </label>
                            <input type="url" name="endpoint" x-model="newKeyForm.endpoint" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Model Terkait
                            </label>
                            <input type="text" name="model" x-model="newKeyForm.model" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="addKeyModalOpen = false"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>{{ __('admin.simpan_kunci_ke_gateway') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT API KEY POOL (9ROUTER STYLE) -->
    <template x-teleport="body">
        <div x-show="editKeyModalOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="editKeyModalOpen = false" @keydown.escape.window="editKeyModalOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-lg p-6 space-y-5 shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="edit-3" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>Edit Konfigurasi Kunci API</span>
                    </div>
                    <button type="button" @click="editKeyModalOpen = false"
                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/ai-tokens/engine/keys') }}/' + editingKey.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <!-- Provider Selector -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Pilih AI Provider Engine
                        </label>
                        <select name="provider" x-model="editingKey.provider" @change="onEditKeyProviderChange()" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="openai_compatible">OpenAI Compatible (Custom Tunnel / vLLM / Proxy / AI Studio)</option>
                            <option value="openai">OpenAI Official Direct (DALL-E 3 &amp; GPT - api.openai.com)</option>
                            <option value="anthropic">Anthropic Claude Official (api.anthropic.com)</option>
                            <option value="gemini">Google Gemini Official (Google AI Studio)</option>
                            <option value="openrouter">OpenRouter AI Gateway (openrouter.ai)</option>
                            <option value="ideogram">Ideogram AI Official (api.ideogram.ai - Image Studio)</option>
                            <option value="stability">Stability AI / Flux Pro (api.stability.ai - Image Studio)</option>
                        </select>
                    </div>

                    <!-- Key Name & Priority Tier in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Label Nama Kunci
                            </label>
                            <input type="text" name="key_name" x-model="editingKey.name" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Tier Prioritas Failover
                            </label>
                            <select name="priority" x-model.number="editingKey.priority"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                <option value="1">Tier 1: Utama (Primary Traffic)</option>
                                <option value="2">Tier 2: Cadangan (Standby Backup)</option>
                                <option value="3">Tier 3: Darurat (Emergency Fallback)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Peruntukan Kunci (Isolasi Chatbot vs Image Generator) -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5 flex items-center justify-between">
                            <span>Peruntukan Kunci AI (Pemisahan Kuota)</span>
                            <span class="text-[11px] font-bold text-[#FF9500]">Isolasi Ketat</span>
                        </label>
                        <select name="purpose" x-model="editingKey.purpose" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="chat">💬 Khusus Chatbot &amp; Analisis Teks (WhatsApp CS, Live Chat, Prompts)</option>
                            <option value="image">🎨 Khusus AI Studio &amp; Generasi Gambar (DALL-E 3, Flux, Visual Studio)</option>
                            <option value="all">🌐 Universal (Bisa Chatbot &amp; Generasi Gambar)</option>
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                            Kunci khusus gambar dipisahkan ketat agar render visual resolusi tinggi tidak mengganggu kuota / rate limit chatbot.
                        </p>
                    </div>

                    <!-- Token API Key (Optional Update) -->
                    <div>
                        <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Perbarui Token API (Biarkan kosong jika tidak ingin mengubah)
                        </label>
                        <input type="password" name="api_key" x-model="editingKey.api_key" placeholder="•••••••• (Tetap gunakan token lama)"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>

                    <!-- Base URL Endpoint & Model in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Base URL Endpoint
                            </label>
                            <input type="url" name="endpoint" x-model="editingKey.endpoint" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>

                        <div>
                            <label class="block text-[12.5px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                Model Terkait
                            </label>
                            <input type="text" name="model" x-model="editingKey.model" required
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="editKeyModalOpen = false"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('common.save_changes') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: BONGKAR PASANG MODEL DYNAMIC SHEET (PRESERVED) -->
    <template x-teleport="body">
        <div x-show="manageModelsOpen" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-all"
            role="dialog" aria-modal="true" @click.self="manageModelsOpen = false" @keydown.escape.window="manageModelsOpen = false">
            <div @click.stop
                class="relative z-10 sheet-material rounded-[20px] w-full max-w-xl p-6 space-y-5 shadow-2xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] max-h-[90vh] flex flex-col">
                
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3 shrink-0">
                    <div class="flex items-center gap-2 text-[15px] font-semibold text-black dark:text-white">
                        <i data-lucide="layers" class="w-5 h-5 text-[#AF52DE]"></i>
                        <span>Bongkar Pasang Model AI Platform</span>
                    </div>
                    <button type="button" @click="manageModelsOpen = false"
                        class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Model Feedback -->
                <div x-show="modelFeedback.show" x-transition class="p-3 rounded-[10px] text-[12px] flex items-center justify-between"
                    :class="modelFeedback.success ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 text-[#D70015] dark:text-[#FF453A]'">
                    <span x-text="modelFeedback.message"></span>
                    <button type="button" @click="modelFeedback.show = false"><i data-lucide="x" class="w-3 h-3"></i></button>
                </div>

                <!-- Add New Model Form Inline -->
                <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 shrink-0">
                    <div class="text-[12.5px] font-semibold text-black dark:text-white">Pasang / Daftarkan Model Baru</div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <div class="sm:col-span-6">
                            <input type="text" x-model="newModel.id" placeholder="Model ID (misal: ag/claude-3.7-sonnet)"
                                class="w-full h-8 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#AF52DE]">
                        </div>
                        <div class="sm:col-span-6">
                            <input type="text" x-model="newModel.name" placeholder="{{ __('admin.nama_tampilan_opsional') }}"
                                class="w-full h-8 px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#AF52DE]">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50">Tier:</span>
                            <select x-model="newModel.tier" class="h-7 px-2 text-[11.5px] rounded-[6px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                                <option value="balanced">Balanced</option>
                                <option value="reasoning">Reasoning / Heavy</option>
                                <option value="fast">Fast / Lightweight</option>
                            </select>
                        </div>
                        <button type="button" @click="addModel()" :disabled="isAddingModel || !newModel.id.trim()"
                            class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] transition flex items-center gap-1 disabled:opacity-50">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Pasang Model</span>
                        </button>
                    </div>
                </div>

                <!-- Models List Scrollable -->
                <div class="overflow-y-auto flex-1 divide-y divide-black/5 dark:divide-white/5 border border-black/5 dark:border-white/5 rounded-[12px]">
                    <template x-for="m in modelsList" :key="m.id">
                        <div class="p-3 flex items-center justify-between hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <div class="min-w-0 pr-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[13px] text-black dark:text-white truncate" x-text="m.name || m.id"></span>
                                    <template x-if="m.id === selectedModel">
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Aktif</span>
                                    </template>
                                </div>
                                <div class="text-[11px] font-mono text-black/50 dark:text-white/50 truncate" x-text="m.id"></div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="selectActiveModel(m.id)"
                                    class="text-[11px] font-semibold px-2 py-1 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white transition">{{ __('admin.pilih') }}</button>
                                <button type="button" @click="deleteModel(m.id)"
                                    class="p-1 rounded text-black/40 hover:text-[#FF3B30] transition" title="{{ __('admin.bongkar_hapus_model') }}">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer & Reset Seed Button -->
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                    <button type="button" @click="resetModels()" :disabled="isResettingModels"
                        class="text-[11.5px] text-[#FF3B30] hover:underline flex items-center gap-1 disabled:opacity-50">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                        <span>{{ __('admin.reset_ke_13_model_bawaan') }}</span>
                    </button>
                    <button type="button" @click="manageModelsOpen = false"
                        class="h-8 px-4 rounded-[8px] text-[12px] font-semibold text-white bg-black dark:bg-white dark:text-black hover:opacity-90 transition">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL 4: GRANT TOKEN MANUAL SHEET (PRESERVED) -->
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
                        <textarea name="reason" x-model="reasonText" required rows="2" placeholder="{{ __('admin.contoh_bonus_kompensasi_migrasi_akun_testing') }}"
                            class="w-full px-3 py-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="grantModalOpen = false"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9A45C6] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm shadow-[#AF52DE]/30">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('admin.kirim_token_sekarang') }}</span>
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
    return {
        activeTab: config.currentTab || 'engine',
        engineStatus: config.engineConfig.status || 'connected',
        latencyMs: config.engineConfig.latency_ms || 0,
        selectedProvider: config.engineConfig.provider || 'openai_compatible',
        endpointUrl: config.engineConfig.endpoint || 'https://r4g77gv.abc-tunnel.us/v1',
        selectedModel: config.engineConfig.model || 'ag/gemini-3.8-flash-high',
        customModelName: '',
        rotationStrategy: config.engineConfig.rotation_strategy || 'failover',
        modelsList: config.modelsList || [],
        maskedKeys: config.engineConfig.masked_keys || [],
        providersMeta: config.engineConfig.providers_meta || {},
        providersStats: config.engineConfig.providers_stats || {},
        totalKeysCount: config.engineConfig.total_keys_count || 0,
        activeKeysCount: config.engineConfig.active_keys_count || 0,
        connectedProvidersCount: config.engineConfig.connected_providers_count || 0,
        totalRoutedRequests: config.engineConfig.total_routed_requests || 0,
        totalRoutedErrors: config.engineConfig.total_routed_errors || 0,

        // Key Filters & Search
        keyFilter: 'all',
        purposeFilter: 'all',
        keySearch: '',

        // Modals
        addKeyModalOpen: false,
        editKeyModalOpen: false,
        grantModalOpen: false,
        manageModelsOpen: false,

        // Add Key Form
        newKeyForm: {
            name: '',
            provider: 'openai_compatible',
            purpose: 'chat',
            api_key: '',
            endpoint: 'https://r4g77gv.abc-tunnel.us/v1',
            model: 'ag/gemini-3.8-flash-high',
            priority: 1,
            weight: 1
        },

        // Edit Key Form
        editingKey: {
            id: '',
            name: '',
            provider: 'openai_compatible',
            purpose: 'chat',
            api_key: '',
            endpoint: '',
            model: '',
            priority: 1,
            weight: 1,
            is_active: true
        },

        // Testing States
        isTesting{{ __('admin.testing') ? '' : '' }}: false,
        isDetecting: false,
        testingKeyId{{ __('admin.testing') ? '' : '' }}: null,
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

        filteredKeys() {
            return this.maskedKeys.filter(k => {
                if (this.purposeFilter !== 'all') {
                    const p = k.purpose || 'chat';
                    if (this.purposeFilter === 'chat' && p === 'image') return false;
                    if (this.purposeFilter === 'image' && p === 'chat') return false;
                }
                if (this.keyFilter !== 'all' && k.provider !== this.keyFilter) {
                    return false;
                }
                if (this.keySearch.trim()) {
                    const q = this.keySearch.toLowerCase();
                    return (k.name && k.name.toLowerCase().includes(q)) ||
                           (k.provider && k.provider.toLowerCase().includes(q)) ||
                           (k.model && k.model.toLowerCase().includes(q)) ||
                           (k.purpose && k.purpose.toLowerCase().includes(q)) ||
                           (k.endpoint && k.endpoint.toLowerCase().includes(q));
                }
                return true;
            });
        },

        openAddKeyModal(preferredProvider = null) {
            this.newKeyForm.provider = preferredProvider || this.selectedProvider || 'openai_compatible';
            this.onAddKeyProviderChange();
            this.newKeyForm.name = '';
            this.newKeyForm.api_key = '';
            this.newKeyForm.priority = 1;
            this.newKeyForm.weight = 1;
            if (['ideogram', 'stability'].includes(this.newKeyForm.provider)) {
                this.newKeyForm.purpose = 'image';
            } else {
                this.newKeyForm.purpose = 'chat';
            }
            this.addKeyModalOpen = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        onAddKeyProviderChange() {
            const meta = this.providersMeta[this.newKeyForm.provider];
            if (meta) {
                this.newKeyForm.endpoint = meta.default_endpoint || '';
                this.newKeyForm.model = meta.default_model || '';
            }
            if (['ideogram', 'stability'].includes(this.newKeyForm.provider)) {
                this.newKeyForm.purpose = 'image';
            }
        },

        openEditKeyModal(k) {
            this.editingKey = {
                id: k.id,
                name: k.name,
                provider: k.provider || 'openai_compatible',
                purpose: k.purpose || 'chat',
                endpoint: k.endpoint || '',
                model: k.model || '',
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
                this.editingKey.model = meta.default_model || '';
            }
        },

        toggleRevealToken(keyId) {
            this.revealedTokens[keyId] = !this.revealedTokens[keyId];
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
                
                // Update key in maskedKeys array
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
            this.modelFeedback.message = `Model aktif dipilih: ${modelId}. Klik 'Simpan Konfigurasi Router Gateway' untuk menerapkan.`;
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
                this.selectedModel = 'claude-3-7-sonnet-20250219';
            } else if (p === 'openrouter') {
                this.endpointUrl = 'https://openrouter.ai/api/v1';
                this.selectedModel = 'deepseek/deepseek-r1';
            } else if (p === 'openai_compatible') {
                this.endpointUrl = 'https://r4g77gv.abc-tunnel.us/v1';
                this.selectedModel = 'ag/gemini-3.8-flash-high';
            }
            this.testFeedback.show = true;
            this.testFeedback.success = true;
            this.testFeedback.message = `Preset endpoint untuk provider '${p}' berhasil disematkan. Klik 'Simpan Konfigurasi Router Gateway' untuk menerapkan.`;
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
                this.modelFeedback.message = '{{ __('admin.terjadi_kesalahan_err_message') }}
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
                    this.testFeedback.message = data.message || `Berhasil mendeteksi ${data.models.length} model aktif dari endpoint.`;
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
