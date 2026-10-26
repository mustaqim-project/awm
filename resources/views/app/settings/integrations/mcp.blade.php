@extends('layouts.app')

@section('title', __('mcp.page_title'))

@section('content')
<script>
function mcpSettingsHub() {
    const sseUrl = @json($sseEndpoint);
    const apiBase = @json($apiBaseUrl);
    const openApiUrl = @json($openApiEndpoint);
    const topLevelMcpUrl = @json($topLevelMcpUrl ?? url('/mcp'));
    const oauthAuthorizeUrl = @json($oauthAuthorizeUrl ?? url('/mcp/oauth/authorize'));
    const oauthTokenUrl = @json($oauthTokenUrl ?? url('/mcp/oauth/token'));
    const oauthRegisterUrl = @json($oauthRegisterUrl ?? url('/mcp/oauth/register'));
    const oauthUserInfoUrl = @json($oauthUserInfoUrl ?? url('/mcp/oauth/userinfo'));
    const oauthIssuerUrl = @json($oauthIssuerUrl ?? url('/'));
    const oidcConfigUrl = @json($oidcConfigUrl ?? url('/.well-known/openid-configuration'));
    const oauthMetadataUrl = @json($oauthMetadataUrl ?? url('/.well-known/oauth-authorization-server'));

    return {
        sseUrl,
        apiBase,
        openApiUrl,
        topLevelMcpUrl,
        oauthAuthorizeUrl,
        oauthTokenUrl,
        oauthRegisterUrl,
        oauthUserInfoUrl,
        oauthIssuerUrl,
        oidcConfigUrl,
        oauthMetadataUrl,
        openCreateModal: false,
        openViewModal: false,
        openEditModal: false,
        activeTokenDetail: null,
        editTokenForm: {
            id: '',
            name: '',
            provider_hint: 'all',
            is_active: true,
            expires_in: 'keep',
            abilities: [],
            update_url: '',
        },

        openView(token) {
            this.activeTokenDetail = token;
            this.openViewModal = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
            });
        },

        openEdit(token) {
            this.activeTokenDetail = token;
            this.editTokenForm = {
                id: token.id,
                name: token.name,
                provider_hint: token.provider_hint,
                is_active: token.is_active,
                expires_in: 'keep',
                abilities: Array.isArray(token.abilities) ? [...token.abilities] : [],
                update_url: token.update_url,
            };
            this.openViewModal = false;
            this.openEditModal = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
            });
        },

        toggleEditAbility(val) {
            if (val === '*') {
                if (this.editTokenForm.abilities.includes('*')) {
                    this.editTokenForm.abilities = [];
                } else {
                    this.editTokenForm.abilities = ['*'];
                }
                return;
            }
            if (this.editTokenForm.abilities.includes('*')) {
                this.editTokenForm.abilities = this.editTokenForm.abilities.filter(a => a !== '*');
            }
            if (this.editTokenForm.abilities.includes(val)) {
                this.editTokenForm.abilities = this.editTokenForm.abilities.filter(a => a !== val);
            } else {
                this.editTokenForm.abilities.push(val);
            }
        },

        hasEditAbility(val) {
            if (this.editTokenForm.abilities.includes('*')) return true;
            return this.editTokenForm.abilities.includes(val);
        },
        activeProviderTab: 'claude',
        claudeMode: 'web', // 'web' (Claude.ai Web & Mobile - Streamable HTTP), 'desktop_http' (Claude Desktop Remote HTTP), 'desktop_stdio' (Local CLI)
        chatgptMode: 'connector', // 'connector' (ChatGPT Apps SDK / MCP Connector), 'actions' (Custom GPT Actions)
        chatgptAuthMethod: 'oauth', // 'oauth' (Recommended by ChatGPT Apps SDK), 'none' (Direct Token URL)
        chatgptClientRegMethod: 'user_defined', // 'user_defined' (Klien OAuth yang Ditentukan Pengguna), 'dcr' (Dynamic Client Registration)
        chatgptTokenEndpointAuth: 'client_secret_post', // 'client_secret_post', 'none', 'client_secret_basic'
        customToken: @json(session('plain_token') ?? ''),
        activeOs: 'windows',
        toastVisible: false,
        toastMessage: '',
        toastTimeout: null,

        init() {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
            this.$watch('activeProviderTab', () => {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined' && lucide.createIcons) {
                        lucide.createIcons();
                    }
                });
            });
        },

        getToken() {
            return (this.customToken && this.customToken.trim().length > 0)
                ? this.customToken.trim()
                : 'cooca_mcp_YOUR_TOKEN';
        },

        showToast(message) {
            this.toastMessage = message || @json(__('mcp.copied'));
            this.toastVisible = true;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                this.toastVisible = false;
            }, 2500);
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
        },

        copyText(text, message) {
            if (!text) return;
            const self = this;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    self.showToast(message);
                }).catch(() => {
                    self.fallbackCopy(text, message);
                });
            } else {
                self.fallbackCopy(text, message);
            }
        },

        fallbackCopy(text, message) {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                this.showToast(message);
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(el);
        },

        getClaudePath() {
            if (this.activeOs === 'windows') {
                return '%APPDATA%\\Claude\\claude_desktop_config.json';
            }
            if (this.activeOs === 'macos') {
                return '~/Library/Application Support/Claude/claude_desktop_config.json';
            }
            return '~/.config/Claude/claude_desktop_config.json';
        },

        getClaudeWebUrl() {
            return `${topLevelMcpUrl}?token=${this.getToken()}`;
        },

        getClaudeHttpBaseUrl() {
            return topLevelMcpUrl;
        },

        getClaudeDesktopHttpConfig() {
            const config = {
                "mcpServers": {
                    "cooca-erp": {
                        "url": topLevelMcpUrl,
                        "headers": {
                            "Authorization": "Bearer " + this.getToken()
                        }
                    }
                }
            };
            return JSON.stringify(config, null, 2);
        },

        getClaudeDesktopSseConfig() {
            return this.getClaudeDesktopHttpConfig();
        },

        getClaudeDesktopStdioConfig() {
            const config = {
                "mcpServers": {
                    "cooca-erp": {
                        "command": "php",
                        "args": ["artisan", "mcp:serve", "--token=" + this.getToken()],
                        "cwd": "/path/ke/proyek/cooca_core"
                    }
                }
            };
            return JSON.stringify(config, null, 2);
        },

        getClaudeCodeCmd() {
            return `claude mcp add cooca-erp -- php artisan mcp:serve --token=${this.getToken()}`;
        },

        getChatGptMcpUrl() {
            if (this.chatgptAuthMethod === 'none') {
                return `${topLevelMcpUrl}?token=${this.getToken()}`;
            }
            return topLevelMcpUrl;
        },

        getChatGptConnectorUrl() {
            return this.getChatGptMcpUrl();
        },

        getChatGptSseUrl() {
            if (this.chatgptAuthMethod === 'none') {
                return `${sseUrl}?token=${this.getToken()}`;
            }
            return sseUrl;
        },

        getChatGptClientId() {
            return this.getToken();
        },

        getChatGptClientSecret() {
            return (this.customToken && this.customToken.trim().length > 0) ? this.customToken.trim() : this.getToken();
        },

        getChatGptOpenApiUrl() {
            return `${openApiUrl}?token=${this.getToken()}`;
        },

        getGeminiPythonCode() {
            const token = this.getToken();
            return `import requests

# Konfigurasi Akses COOCA MCP Server via REST Bridge
COOCA_TOKEN = "${token}"
COOCA_API_BASE = "${apiBase}"

# 1. Panggil Tool: Laporan Ringkasan Laba Rugi Bisnis
response = requests.post(
    f"{COOCA_API_BASE}/tools/report_get_profit_loss/execute",
    headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
    json={"period": "this_month"}
)
print("{{ addslashes(__('mcp.py_sample_print_pnl')) }}", response.json())

{{ addslashes(__('mcp.py_sample_comment_bank')) }}
bank_resp = requests.post(
    f"{COOCA_API_BASE}/tools/finance_get_cash_and_bank_balances/execute",
    headers={"Authorization": f"Bearer {COOCA_TOKEN}"},
    json={}
)
print("{{ addslashes(__('mcp.py_sample_print_bank')) }}", bank_resp.json())`;
        },

        getGeminiCurl() {
            const token = this.getToken();
            return `curl -X POST "${apiBase}/tools/finance_get_cash_and_bank_balances/execute" \\
  -H "Authorization: Bearer ${token}" \\
  -H "Content-Type: application/json"`;
        }
    };
}

// 1. Assign to window so Alpine evaluates x-data="mcpSettingsHub()" directly
window.mcpSettingsHub = mcpSettingsHub;

// 2. Register with Alpine.data if Alpine is already loaded or on alpine:init
if (typeof Alpine !== 'undefined' && Alpine.data) {
    Alpine.data('mcpSettingsHub', mcpSettingsHub);
}
document.addEventListener('alpine:init', () => {
    if (typeof Alpine !== 'undefined' && Alpine.data) {
        Alpine.data('mcpSettingsHub', mcpSettingsHub);
    }
});
</script>

<div x-data="mcpSettingsHub()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-32 space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-1">
                <a href="{{ route('settings.index') }}" class="hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors">{{ __('mcp.breadcrumb_settings') }}</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-neutral-900 dark:text-neutral-100 font-medium">{{ __('mcp.page_title') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-neutral-900 dark:text-white">
                {{ __('mcp.page_title') }}
            </h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-3xl">
                {{ __('mcp.page_subtitle') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="openCreateModal = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-[14px] bg-neutral-900 hover:bg-black text-white dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100 font-medium text-sm shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('mcp.btn_create_token') }}</span>
            </button>
        </div>
    </div>

    {{-- Alert: Token Baru Saja Dibuat (One-Time View) --}}
    @if(session('plain_token'))
    <div class="rounded-[20px] bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-500/20 p-5 backdrop-blur-md">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="key" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                    {{ __('mcp.token_created_success') }} ({{ session('token_name') }})
                </h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                    {{ __('mcp.alert_new_token') }}
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ session('plain_token') }}"
                        id="newGeneratedToken"
                        class="w-full max-w-xl font-mono text-xs bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2 rounded-[10px] border border-emerald-500/30 focus:outline-none select-all" />
                    <button
                        type="button"
                        @click="copyText('{{ session('plain_token') }}')"
                        class="px-3 py-2 rounded-[10px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span>{{ __('mcp.btn_copy_token') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BENTO GRID UTAMA (4 KARTU) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KARTU 1: Master Status & Metrics Cockpit (4-Kolom) --}}
        <div class="lg:col-span-4 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800/80">
                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                        {{ __('mcp.lbl_master_status') }}
                    </span>
                    @if($isMcpActive)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ __('mcp.status_active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-500/10 text-neutral-600 dark:text-neutral-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-400"></span>
                            {{ __('mcp.status_inactive') }}
                        </span>
                    @endif
                </div>

                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.active_tokens') }}</div>
                            <div class="text-2xl font-semibold text-neutral-900 dark:text-white tabular-nums tracking-tight mt-0.5">
                                {{ $activeTokensCount }}
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-[14px] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-300">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-neutral-800/60">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.monthly_calls') }}</div>
                            <div class="text-2xl font-semibold text-neutral-900 dark:text-white tabular-nums tracking-tight mt-0.5">
                                {{ number_format($totalCallsThisMonth, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-[14px] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-600 dark:text-neutral-300">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('mcp.lbl_mcp_master_switch') }}
                </span>
                <form action="{{ route('settings.integrations.mcp.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $isMcpActive ? '0' : '1' }}">
                    <button
                        type="submit"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $isMcpActive ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $isMcpActive ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- KARTU 2: Kunci Akses & Token Registry (8-Kolom) --}}
        <div class="lg:col-span-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.tokens_title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.tokens_desc') }}
                    </p>
                </div>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
                    {{ __('mcp.lbl_keys_registered', ['count' => count($tokens)]) }}
                </span>
            </div>

            <div class="mt-4 divide-y divide-neutral-100 dark:divide-neutral-800/80">
                @forelse($tokens as $token)
                @php
                    $tokenJson = json_encode([
                        'id' => $token->id,
                        'name' => $token->name,
                        'provider_hint' => $token->provider_hint,
                        'token_hash' => substr($token->token_hash, -6),
                        'abilities' => $token->abilities ?? [],
                        'is_active' => (bool) $token->is_active,
                        'is_expired' => $token->isExpired(),
                        'last_used_at' => $token->last_used_at ? $token->last_used_at->translatedFormat('d M Y H:i') : null,
                        'last_used_diff' => $token->last_used_at ? $token->last_used_at->diffForHumans() : __('mcp.lbl_never_used'),
                        'expires_at' => $token->expires_at ? $token->expires_at->translatedFormat('d M Y H:i') : __('mcp.lbl_never_expires'),
                        'created_at' => $token->created_at ? $token->created_at->translatedFormat('d M Y H:i') : null,
                        'user_name' => $token->user?->name ?? 'System',
                        'update_url' => route('settings.integrations.mcp.tokens.update', $token),
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                @endphp
                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20 px-2 -mx-2 rounded-[14px] transition-colors">
                    <div class="min-w-0 flex-1 cursor-pointer" @click="openView({{ $tokenJson }})">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-neutral-900 dark:text-white truncate hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $token->name }}
                            </span>
                            <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                                {{ strtoupper($token->provider_hint) }}
                            </span>
                            @if(! $token->is_active)
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-rose-500/10 text-rose-500 font-medium">
                                    {{ __('mcp.status_inactive') }}
                                </span>
                            @elseif($token->isExpired())
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-amber-500/10 text-amber-600 font-medium">
                                    {{ __('mcp.status_expired') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium">
                                    {{ __('mcp.status_active') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            <span class="font-mono text-[11px] text-neutral-400 dark:text-neutral-500">
                                cooca_mcp_live_••••••{{ substr($token->token_hash, -6) }}
                            </span>
                            <span>•</span>
                            @if(in_array('*', $token->abilities ?? []))
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ __('mcp.lbl_all_access_granted') }}</span>
                            @else
                                <span>{{ __('mcp.lbl_permissions_count', ['count' => count($token->abilities ?? [])]) }}</span>
                            @endif
                            <span>•</span>
                            <span>{{ __('mcp.lbl_used_prefix') }}: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : __('mcp.lbl_never_used') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                        {{-- Tombol Lihat Detail Autorisasi --}}
                        <button
                            type="button"
                            @click="openView({{ $tokenJson }})"
                            class="min-h-[36px] px-2.5 py-1.5 rounded-[10px] bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 text-xs font-medium inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                            title="{{ __('mcp.btn_view_auth') }}"
                            aria-label="{{ __('mcp.btn_view_auth') }}">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                            <span class="hidden sm:inline">{{ __('mcp.btn_view_auth') }}</span>
                        </button>

                        {{-- Tombol Edit Autorisasi --}}
                        <button
                            type="button"
                            @click="openEdit({{ $tokenJson }})"
                            class="min-h-[36px] px-2.5 py-1.5 rounded-[10px] bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 text-xs font-medium inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                            title="{{ __('mcp.btn_edit_auth') }}"
                            aria-label="{{ __('mcp.btn_edit_auth') }}">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                            <span class="hidden sm:inline">{{ __('mcp.btn_edit_auth') }}</span>
                        </button>

                        {{-- Tombol Hapus/Cabut --}}
                        <form action="{{ route('settings.integrations.mcp.tokens.revoke', $token) }}" method="POST" onsubmit="return confirm('{{ addslashes(__('mcp.confirm_revoke_token')) }}')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="w-9 h-9 rounded-[10px] text-neutral-400 hover:text-rose-600 hover:bg-rose-500/10 transition-colors cursor-pointer flex items-center justify-center shrink-0"
                                title="{{ __('mcp.title_revoke_token') }}"
                                aria-label="{{ __('mcp.title_revoke_token') }}">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('mcp.empty_tokens') }}
                </div>
                @endforelse
            </div>
        </div>

        {{-- KARTU 3: Universal Multi-Provider Setup & Step-by-Step Tutorial Hub (12-Kolom) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-[8px] bg-blue-500/10 text-blue-600 dark:text-blue-400 font-mono text-[11px] font-semibold uppercase tracking-wider">
                            {{ __('mcp.lbl_interactive_tutorial_hub') }}
                        </span>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('mcp.setup_hub_title') }}
                        </h3>
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 max-w-2xl">
                        {{ __('mcp.setup_hub_desc') }}
                    </p>
                </div>

                {{-- Live Token Auto-Fill Selector & OS Switcher --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-neutral-50 dark:bg-neutral-900/90 p-3 rounded-[18px] border border-neutral-200/80 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                            {{ __('mcp.lbl_active_token_colon') }}
                        </span>
                        <input
                            type="text"
                            x-model="customToken"
                            placeholder="cooca_mcp_live_••••••"
                            class="text-xs font-mono px-3 py-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-neutral-200 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-1 focus:ring-blue-500 w-48 sm:w-56" />
                        @if(session('plain_token'))
                            <button
                                type="button"
                                @click="customToken = '{{ session('plain_token') }}'"
                                class="px-2 py-1.5 rounded-[8px] bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[11px] font-medium whitespace-nowrap transition-colors"
                                title="{{ addslashes(__('mcp.btn_use_new_token_title')) }}">
                                {{ __('mcp.btn_use_new_token') }}
                            </button>
                        @endif
                    </div>

                    <div class="hidden sm:block h-4 w-px bg-neutral-200 dark:bg-neutral-800"></div>

                    <div class="flex items-center gap-1">
                        <span class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 mr-1">{{ __('mcp.lbl_os') }}</span>
                        <button
                            type="button"
                            @click="activeOs = 'windows'"
                            :class="activeOs === 'windows' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            Windows
                        </button>
                        <button
                            type="button"
                            @click="activeOs = 'macos'"
                            :class="activeOs === 'macos' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            macOS
                        </button>
                        <button
                            type="button"
                            @click="activeOs = 'linux'"
                            :class="activeOs === 'linux' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-[8px] text-[11px] transition-all">
                            Linux
                        </button>
                    </div>
                </div>
            </div>

            {{-- Segmented Control Provider Tabs (Claude, ChatGPT, Gemini Ecosystems) --}}
            <div class="flex items-center gap-1.5 p-1.5 rounded-[16px] bg-neutral-100 dark:bg-neutral-800/80 text-xs font-medium overflow-x-auto no-scrollbar">
                <button
                    type="button"
                    @click="activeProviderTab = 'claude'"
                    :class="activeProviderTab === 'claude' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 min-h-[36px] rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i data-lucide="bot" class="w-4 h-4 text-amber-500"></i>
                    <span>{{ __('mcp.tab_claude') }}</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'chatgpt'"
                    :class="activeProviderTab === 'chatgpt' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 min-h-[36px] rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i data-lucide="sparkles" class="w-4 h-4 text-emerald-500"></i>
                    <span>{{ __('mcp.tab_chatgpt') }}</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'claude_code'"
                    :class="activeProviderTab === 'claude_code' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 min-h-[36px] rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i data-lucide="terminal" class="w-4 h-4 text-neutral-700 dark:text-neutral-300"></i>
                    <span>{{ __('mcp.tab_claude_code') }}</span>
                </button>
                <button
                    type="button"
                    @click="activeProviderTab = 'gemini'"
                    :class="activeProviderTab === 'gemini' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                    class="px-3.5 py-2 min-h-[36px] rounded-[12px] transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <i data-lucide="cpu" class="w-4 h-4 text-purple-500"></i>
                    <span>{{ __('mcp.tab_gemini') }}</span>
                </button>
            </div>

            {{-- Tab Contents Detail per Provider --}}
            <div class="mt-4">

                {{-- 1. Claude Ecosystem (Web, Mobile Apps & Desktop) --}}
                <div x-show="activeProviderTab === 'claude'" class="space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-semibold">
                                {{ __('mcp.claude_ecosystem_badge') }}
                            </span>
                        </div>

                        {{-- Sub-toggle: Web/Apps (HTTP) vs Desktop Remote (HTTP) vs Stdio --}}
                        <div class="inline-flex p-1 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 text-[11px] font-medium overflow-x-auto">
                            <button
                                type="button"
                                @click="claudeMode = 'web'"
                                :class="claudeMode === 'web' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-3 py-1 rounded-[8px] transition-all whitespace-nowrap">
                                {{ __('mcp.claude_mode_web') }}
                            </button>
                            <button
                                type="button"
                                @click="claudeMode = 'desktop_http'"
                                :class="(claudeMode === 'desktop_http' || claudeMode === 'desktop_sse') ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-3 py-1 rounded-[8px] transition-all whitespace-nowrap">
                                {{ __('mcp.claude_mode_desktop_http') }}
                            </button>
                            <button
                                type="button"
                                @click="claudeMode = 'desktop_stdio'"
                                :class="claudeMode === 'desktop_stdio' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-3 py-1 rounded-[8px] transition-all whitespace-nowrap">
                                {{ __('mcp.claude_mode_desktop_stdio') }}
                            </button>
                        </div>
                    </div>

                    {{-- Mode 1A: Claude.ai Web & Mobile Apps Custom Connector (Streamable HTTP) --}}
                    <div x-show="claudeMode === 'web'" class="space-y-4">
                        {{-- Warning Alert: Anthropic SSE Deprecation Notice & Streamable HTTP Solution --}}
                        <div class="p-4 rounded-[16px] bg-amber-50/80 dark:bg-amber-950/30 border border-amber-500/25 text-xs text-amber-900 dark:text-amber-200 leading-relaxed flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                            <div class="space-y-1">
                                <strong class="font-semibold text-amber-950 dark:text-amber-100 flex items-center gap-1.5">
                                    <span>{{ __('mcp.claude_advanced_warning_title') }}</span>
                                </strong>
                                <p class="text-[11.5px] text-amber-800 dark:text-amber-300 leading-relaxed">
                                    {!! __('mcp.claude_advanced_warning_desc') !!}
                                </p>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-blue-50/70 dark:bg-blue-950/30 border border-blue-500/20 text-xs text-blue-800 dark:text-blue-300 leading-relaxed flex items-start gap-3">
                            <i data-lucide="smartphone" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="font-semibold text-blue-900 dark:text-blue-200">{{ __('mcp.claude_sync_notice_title') }}</strong>
                                <p class="mt-0.5 text-[11px] text-blue-700 dark:text-blue-300">
                                    {{ __('mcp.claude_sync_notice_desc') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">1</span>
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_step1_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ __('mcp.claude_step1_desc') }}
                                </p>
                                <a href="https://claude.ai/new?modal=add-custom-connector#customize/connectors" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    <span>{{ __('mcp.claude_step1_link') }}</span>
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>

                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">2</span>
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_step2_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ __('mcp.claude_step2_desc') }}
                                </p>
                                <div class="p-2 rounded-[8px] bg-neutral-200/60 dark:bg-neutral-800 font-mono text-xs text-neutral-900 dark:text-white font-medium select-all">
                                    COOCA ID
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">3</span>
                                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_step3_title') }}</h4>
                                </div>
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">{{ __('mcp.claude_step3_badge') }}</span>
                            </div>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                {!! __('mcp.claude_step3_desc') !!}
                            </p>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    :value="getClaudeWebUrl()"
                                    class="flex-1 font-mono text-xs bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white px-3.5 py-2 rounded-[10px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                <button
                                    type="button"
                                    @click="copyText(getClaudeWebUrl(), '{{ addslashes(__('mcp.toast_claude_web_copied')) }}')"
                                    class="px-4 py-2 rounded-[10px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('mcp.btn_copy_url') }}</span>
                                </button>
                            </div>

                            {{-- Petunjuk Bagian Lanjutan (Advanced) di Claude.ai --}}
                            <div class="p-3.5 rounded-[14px] bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200/90 dark:border-neutral-700 space-y-2 mt-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-neutral-900 dark:text-white flex items-center gap-1.5">
                                        <i data-lucide="sliders" class="w-3.5 h-3.5 text-blue-500"></i>
                                        <span>{{ __('mcp.claude_advanced_transport_title') }}</span>
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 font-semibold">{{ __('mcp.claude_advanced_transport_badge') }}</span>
                                </div>
                                <ul class="space-y-1.5 text-[11.5px] text-neutral-600 dark:text-neutral-300">
                                    <li class="flex items-start gap-2">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                        <span>{!! __('mcp.claude_advanced_transport_step1') !!}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                        <span>{!! __('mcp.claude_advanced_transport_step2') !!}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="p-4 rounded-[16px] bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-500/20 space-y-1">
                            <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200 font-semibold text-xs">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-bold">4</span>
                                <span>{{ __('mcp.claude_step4_title') }}</span>
                            </div>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-300 leading-relaxed">
                                {!! __('mcp.claude_step4_desc') !!}
                            </p>
                        </div>
                    </div>

                    {{-- Mode 1B: Claude Desktop Remote (Streamable HTTP) --}}
                    <div x-show="claudeMode === 'desktop_http' || claudeMode === 'desktop_sse'" class="space-y-4" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">1</span>
                                    <button
                                        type="button"
                                        @click="copyText(getClaudePath(), '{{ addslashes(__('mcp.toast_file_path_copied')) }}')"
                                        class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                        <i data-lucide="copy" class="w-3 h-3"></i>
                                        <span>{{ __('mcp.btn_copy_path') }}</span>
                                    </button>
                                </div>
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_desktop_step1_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {!! __('mcp.claude_desktop_step1_desc') !!}
                                </p>
                                <div class="p-2 rounded-[8px] bg-neutral-200/60 dark:bg-neutral-800 font-mono text-[11px] text-neutral-800 dark:text-neutral-200 break-all select-all">
                                    <span x-text="getClaudePath()"></span>
                                </div>
                            </div>

                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center">2</span>
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_desktop_step2_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ __('mcp.claude_desktop_http_note') }}
                                </p>
                            </div>
                        </div>

                        {{-- Live Code Box Claude Desktop HTTP --}}
                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>claude_desktop_config.json</span>
                                <button
                                    type="button"
                                    @click="copyText(getClaudeDesktopHttpConfig(), '{{ addslashes(__('mcp.toast_claude_desktop_http_copied')) }}')"
                                    class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('mcp.btn_copy_config') }}</span>
                                </button>
                            </div>
                            <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getClaudeDesktopHttpConfig()"></code></pre>
                        </div>
                    </div>

                    {{-- Mode 1C: Claude Desktop Local CLI (Stdio) --}}
                    <div x-show="claudeMode === 'desktop_stdio'" class="space-y-4" style="display: none;">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_desktop_stdio_title') }}</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                {!! __('mcp.claude_desktop_stdio_note') !!}
                            </p>
                        </div>

                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>claude_desktop_config.json (Local Stdio)</span>
                                <button
                                    type="button"
                                    @click="copyText(getClaudeDesktopStdioConfig(), '{{ addslashes(__('mcp.toast_stdio_config_copied')) }}')"
                                    class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('mcp.btn_copy_config') }}</span>
                                </button>
                            </div>
                            <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getClaudeDesktopStdioConfig()"></code></pre>
                        </div>
                    </div>
                </div>

                {{-- 2. ChatGPT Ecosystem (Apps SDK, Web & Custom GPT Actions) --}}
                <div x-show="activeProviderTab === 'chatgpt'" class="space-y-6" style="display: none;">
                    
                    {{-- Header & Sub-toggle --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                                {{ __('mcp.chatgpt_ecosystem_badge') }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-neutral-100 dark:bg-neutral-800 text-[10px] text-neutral-600 dark:text-neutral-400 font-medium">
                                <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                                <span>RFC 8414 & OIDC Ready</span>
                            </span>
                        </div>

                        {{-- Sub-toggle: Apps SDK vs Actions --}}
                        <div class="inline-flex p-1 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 text-[11px] font-medium">
                            <button
                                type="button"
                                @click="chatgptMode = 'connector'"
                                :class="chatgptMode === 'connector' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-3 py-1 rounded-[8px] transition-all flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>{{ __('mcp.chatgpt_mode_connector') }}</span>
                            </button>
                            <button
                                type="button"
                                @click="chatgptMode = 'actions'"
                                :class="chatgptMode === 'actions' ? 'bg-white dark:bg-[#2C2C2E] text-neutral-900 dark:text-white shadow-xs font-semibold' : 'text-neutral-500'"
                                class="px-3 py-1 rounded-[8px] transition-all flex items-center gap-1.5">
                                <i data-lucide="wrench" class="w-3.5 h-3.5 text-neutral-500"></i>
                                <span>{{ __('mcp.chatgpt_mode_actions') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Mode 2A: ChatGPT Apps SDK / MCP Connector --}}
                    <div x-show="chatgptMode === 'connector'" class="space-y-5">
                        
                        {{-- Panduan Resmi OpenAI Help Center Header Banner --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/70 border border-neutral-200/80 dark:border-neutral-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="book-open" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                    <span>{{ __('mcp.chatgpt_setup_guide_title') }}</span>
                                </h3>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                    {{ __('mcp.chatgpt_setup_guide_sub') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="https://help.openai.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors">
                                    <span>{{ __('mcp.chatgpt_help_center_ref') }}</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>

                        {{-- 4 Steps Layout --}}
                        <div class="space-y-4">

                            {{-- LANGKAH 1 — Buka menu Plugins atau Apps --}}
                            <div class="p-4 rounded-[16px] bg-white dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center shrink-0">1</span>
                                        <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_step1_heading') }}</h4>
                                    </div>
                                    <a href="https://chatgpt.com/?utm_source=chatgpt.com" target="_blank" rel="noopener noreferrer" class="px-3 py-1 rounded-[8px] bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                        <span>Buka chatgpt.com</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                </div>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                    {!! __('mcp.chatgpt_step1_desc') !!}
                                </p>
                            </div>

                            {{-- LANGKAH 2 — Tambahkan MCP Server --}}
                            <div class="p-5 rounded-[16px] bg-white dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center shrink-0">2</span>
                                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_step2_heading') }}</h4>
                                </div>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ __('mcp.chatgpt_step2_desc') }}
                                </p>

                                {{-- Form Inputs Grid --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    {{-- Nama --}}
                                    <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/90 border border-neutral-200/70 dark:border-neutral-800 space-y-1.5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_form_name_label') }}</span>
                                            <button
                                                type="button"
                                                @click="copyText('COOCA MCP', 'Nama server tersalin!')"
                                                class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] flex items-center gap-1">
                                                <i data-lucide="copy" class="w-3 h-3"></i>
                                                <span>{{ __('mcp.btn_copy_short') }}</span>
                                            </button>
                                        </div>
                                        <div class="font-mono text-xs bg-white dark:bg-[#1E1E1E] text-neutral-900 dark:text-white px-3 py-2 rounded-[8px] border border-neutral-200 dark:border-neutral-700">
                                            COOCA MCP
                                        </div>
                                    </div>

                                    {{-- Deskripsi (opsional) --}}
                                    <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/90 border border-neutral-200/70 dark:border-neutral-800 space-y-1.5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_form_desc_label') }}</span>
                                            <button
                                                type="button"
                                                @click="copyText('{{ addslashes(__('mcp.chatgpt_form_desc_val')) }}', 'Deskripsi tersalin!')"
                                                class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] flex items-center gap-1">
                                                <i data-lucide="copy" class="w-3 h-3"></i>
                                                <span>{{ __('mcp.btn_copy_short') }}</span>
                                            </button>
                                        </div>
                                        <div class="text-xs text-neutral-700 dark:text-neutral-300 bg-white dark:bg-[#1E1E1E] px-3 py-2 rounded-[8px] border border-neutral-200 dark:border-neutral-700 truncate" title="{{ __('mcp.chatgpt_form_desc_val') }}">
                                            {{ __('mcp.chatgpt_form_desc_val') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Koneksi (URL Endpoint HTTPS) --}}
                                <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/90 border border-neutral-200/70 dark:border-neutral-800 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_form_conn_label') }}</span>
                                            <span class="px-2 py-0.5 rounded-[6px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-semibold">HTTPS</span>
                                        </div>
                                        <span class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="chatgptAuthMethod === 'oauth' ? 'Clean Endpoint (Auto-Discovery)' : 'Query Token Parameter'"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            readonly
                                            :value="getChatGptMcpUrl()"
                                            class="flex-1 font-mono text-xs bg-white dark:bg-[#1E1E1E] text-neutral-900 dark:text-white px-3.5 py-2 rounded-[8px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                        <button
                                            type="button"
                                            @click="copyText(getChatGptMcpUrl(), 'URL endpoint MCP tersalin!')"
                                            class="px-3.5 py-2 rounded-[8px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors shrink-0">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('mcp.btn_copy_url') }}</span>
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                                        <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>{{ __('mcp.chatgpt_tunnel_tip') }}</span>
                                    </p>
                                </div>

                                {{-- Autentikasi Switcher --}}
                                <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/90 border border-neutral-200/70 dark:border-neutral-800 space-y-2.5">
                                    <div class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_form_auth_label') }}</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <button
                                            type="button"
                                            @click="chatgptAuthMethod = 'oauth'"
                                            :class="chatgptAuthMethod === 'oauth' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-neutral-900 dark:text-white ring-1 ring-emerald-500/50' : 'border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1E1E1E] text-neutral-600 dark:text-neutral-400 hover:border-neutral-300'"
                                            class="p-3 rounded-[10px] border text-left transition-all">
                                            <div class="flex items-center justify-between font-semibold">
                                                <span class="flex items-center gap-1.5">
                                                    <i data-lucide="shield" class="w-3.5 h-3.5 text-emerald-500"></i>
                                                    <span>OAuth</span>
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">Rekomendasi</span>
                                            </div>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
                                                Apps SDK resmi dengan RFC 8414 Metadata Discovery, PKCE, dan OIDC.
                                            </p>
                                        </button>

                                        <button
                                            type="button"
                                            @click="chatgptAuthMethod = 'none'"
                                            :class="chatgptAuthMethod === 'none' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-neutral-900 dark:text-white ring-1 ring-emerald-500/50' : 'border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1E1E1E] text-neutral-600 dark:text-neutral-400 hover:border-neutral-300'"
                                            class="p-3 rounded-[10px] border text-left transition-all">
                                            <div class="flex items-center justify-between font-semibold">
                                                <span class="flex items-center gap-1.5">
                                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                                                    <span>None (Tanpa autentikasi)</span>
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">Cepat</span>
                                            </div>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
                                                Token keamanan langsung disematkan pada parameter URL.
                                            </p>
                                        </button>
                                    </div>
                                </div>

                                {{-- Pengaturan tingkat lanjut Oauth (Tampil saat OAuth) --}}
                                <div x-show="chatgptAuthMethod === 'oauth'" class="p-4 sm:p-5 rounded-[14px] bg-neutral-100/70 dark:bg-[#1E1E1E]/70 border border-neutral-200 dark:border-neutral-800 space-y-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="sliders" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                                <h5 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider">{{ __('mcp.chatgpt_oauth_advanced_title') }}</h5>
                                            </div>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                                {{ __('mcp.chatgpt_oauth_advanced_sub') }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Discovery Status Box --}}
                                    <div class="p-3 rounded-[10px] bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-500/20 text-xs space-y-1">
                                        <div class="font-semibold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                            <span>{{ __('mcp.chatgpt_oauth_discovery_active') }}</span>
                                        </div>
                                        <p class="text-[11px] text-emerald-800 dark:text-emerald-300 leading-relaxed">
                                            {!! __('mcp.chatgpt_oauth_discovery_active_desc') !!}
                                        </p>
                                    </div>

                                    <div class="text-[11px] text-neutral-600 dark:text-neutral-400 italic">
                                        {!! __('mcp.chatgpt_oauth_manual_note') !!}
                                    </div>

                                    {{-- Sub-section 1: Pendaftaran Klien --}}
                                    <div class="p-4 rounded-[12px] bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                                        <div>
                                            <h6 class="text-xs font-bold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_client_reg_title') }}</h6>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                {!! __('mcp.chatgpt_client_reg_desc') !!}
                                            </p>
                                        </div>

                                        {{-- Metode Pendaftaran --}}
                                        <div class="space-y-1.5 text-xs">
                                            <div class="font-semibold text-neutral-700 dark:text-neutral-300">{{ __('mcp.chatgpt_reg_method_label') }}</div>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <div class="p-2.5 rounded-[8px] border border-emerald-500/40 bg-emerald-50/30 dark:bg-emerald-950/20 text-neutral-900 dark:text-white flex items-center gap-2">
                                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                                    <span class="font-medium text-[11px]">{{ __('mcp.chatgpt_reg_method_user') }}</span>
                                                </div>
                                                <div class="p-2.5 rounded-[8px] border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 text-neutral-500 flex items-center justify-between">
                                                    <span class="text-[11px]">{{ __('mcp.chatgpt_reg_method_dcr') }}</span>
                                                    <span class="text-[10px] px-1 rounded bg-neutral-200 dark:bg-neutral-800">RFC 7591</span>
                                                </div>
                                                <div class="p-2.5 rounded-[8px] border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 text-neutral-400">
                                                    <span class="text-[11px]">{{ __('mcp.chatgpt_reg_method_cimd') }}</span>
                                                </div>
                                            </div>
                                            <p class="text-[11px] text-neutral-500 mt-1">{{ __('mcp.chatgpt_reg_method_user_help') }}</p>
                                        </div>

                                        {{-- Client Credentials Inputs --}}
                                        <div class="space-y-2.5 pt-1">
                                            {{-- URL Callback --}}
                                            <div class="space-y-1 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ __('mcp.chatgpt_lbl_callback_url') }}</span>
                                                    <span class="text-[10px] text-neutral-400">{{ __('mcp.chatgpt_val_callback_url_help') }}</span>
                                                </div>
                                                <input
                                                    type="text"
                                                    readonly
                                                    value="https://chatgpt.com/aip/.../oauth/callback (Otomatis dari ChatGPT)"
                                                    class="w-full font-mono text-[11px] bg-neutral-100 dark:bg-[#1E1E1E] text-neutral-600 dark:text-neutral-400 px-3 py-1.5 rounded-[8px] border border-neutral-200 dark:border-neutral-700 italic" />
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                {{-- ID Klien --}}
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ __('mcp.chatgpt_lbl_client_id') }}</span>
                                                        <button
                                                            type="button"
                                                            @click="copyText(getChatGptClientId(), 'Client ID tersalin!')"
                                                            class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] flex items-center gap-1">
                                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                                            <span>{{ __('mcp.btn_copy_short') }}</span>
                                                        </button>
                                                    </div>
                                                    <input
                                                        type="text"
                                                        readonly
                                                        :value="getChatGptClientId()"
                                                        class="w-full font-mono text-xs bg-white dark:bg-[#1E1E1E] text-neutral-900 dark:text-white px-3 py-1.5 rounded-[8px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                                </div>

                                                {{-- Rahasia Klien --}}
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ __('mcp.chatgpt_lbl_client_secret') }} <span class="text-neutral-400 font-normal">{{ __('mcp.chatgpt_client_secret_optional') }}</span></span>
                                                        <button
                                                            type="button"
                                                            @click="copyText(getChatGptClientSecret(), 'Client Secret tersalin!')"
                                                            class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] flex items-center gap-1">
                                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                                            <span>{{ __('mcp.btn_copy_short') }}</span>
                                                        </button>
                                                    </div>
                                                    <input
                                                        type="text"
                                                        readonly
                                                        :value="getChatGptClientSecret()"
                                                        class="w-full font-mono text-xs bg-white dark:bg-[#1E1E1E] text-neutral-900 dark:text-white px-3 py-1.5 rounded-[8px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                                </div>
                                            </div>

                                            {{-- Metode autentikasi endpoint token --}}
                                            <div class="space-y-1 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ __('mcp.chatgpt_lbl_token_auth_method') }}</span>
                                                    <button
                                                        type="button"
                                                        @click="copyText('client_secret_post', 'Metode token auth tersalin!')"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] flex items-center gap-1">
                                                        <i data-lucide="copy" class="w-3 h-3"></i>
                                                        <span>{{ __('mcp.btn_copy_short') }}</span>
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2.5 py-1 rounded-[6px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-mono text-xs font-semibold">client_secret_post</span>
                                                    <span class="text-[11px] text-neutral-400">(didukung juga: <code class="font-mono">client_secret_basic</code>, <code class="font-mono">none</code>)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sub-section 2: Cakupan (Scopes) --}}
                                    <div class="p-4 rounded-[12px] bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                                        <div>
                                            <h6 class="text-xs font-bold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_scopes_title') }}</h6>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                {!! __('mcp.chatgpt_scopes_desc') !!}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            {{-- Cakupan default --}}
                                            <div class="p-2.5 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-700/80 space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_lbl_default_scope') }}</span>
                                                    <button
                                                        type="button"
                                                        @click="copyText('mcp:tools', 'Cakupan default tersalin!')"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline text-[11px]">
                                                        {{ __('mcp.btn_copy_short') }}
                                                    </button>
                                                </div>
                                                <div class="font-mono text-xs text-neutral-900 dark:text-white font-semibold">mcp:tools</div>
                                                <p class="text-[10px] text-neutral-500">{{ __('mcp.chatgpt_default_scope_help') }}</p>
                                            </div>

                                            {{-- Cakupan dasar --}}
                                            <div class="p-2.5 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200 dark:border-neutral-700/80 space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ __('mcp.chatgpt_lbl_base_scope') }}</span>
                                                    <button
                                                        type="button"
                                                        @click="copyText('mcp:tools', 'Cakupan dasar tersalin!')"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline text-[11px]">
                                                        {{ __('mcp.btn_copy_short') }}
                                                    </button>
                                                </div>
                                                <div class="font-mono text-xs text-neutral-900 dark:text-white font-semibold">mcp:tools</div>
                                                <p class="text-[10px] text-neutral-500">{{ __('mcp.chatgpt_base_scope_help') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sub-section 3: Endpoint Oauth --}}
                                    <div class="p-4 rounded-[12px] bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                                        <div>
                                            <h6 class="text-xs font-bold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_endpoints_title') }}</h6>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                {!! __('mcp.chatgpt_endpoints_desc') !!}
                                            </p>
                                        </div>

                                        <div class="space-y-2 text-xs">
                                            {{-- URL otorisasi --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_auth_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oauthAuthorizeUrl }}">
                                                    {{ $oauthAuthorizeUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oauthAuthorizeUrl }}', 'URL otorisasi tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- URL token --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_token_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oauthTokenUrl }}">
                                                    {{ $oauthTokenUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oauthTokenUrl }}', 'URL token tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- URL pendaftaran --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_reg_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oauthRegisterUrl }}">
                                                    {{ $oauthRegisterUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oauthRegisterUrl }}', 'URL pendaftaran tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- URL dasar server otorisasi --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_issuer_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oauthIssuerUrl }}">
                                                    {{ $oauthIssuerUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oauthIssuerUrl }}', 'URL dasar server tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- Sumber daya --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_resource') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $topLevelMcpUrl }}">
                                                    {{ $topLevelMcpUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $topLevelMcpUrl }}', 'Sumber daya tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- PKCE --}}
                                            <div class="flex items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_pkce') }}</span>
                                                <div class="flex-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('mcp.chatgpt_val_pkce') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sub-section 4: Dukungan OpenID (OIDC) --}}
                                    <div class="p-4 rounded-[12px] bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                                        <div>
                                            <h6 class="text-xs font-bold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_oidc_title') }}</h6>
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                {{ __('mcp.chatgpt_oidc_desc') }}
                                            </p>
                                        </div>

                                        <div class="space-y-2 text-xs">
                                            {{-- OIDC Diaktifkan --}}
                                            <div class="flex items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_oidc_enabled') }}</span>
                                                <div class="flex-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('mcp.chatgpt_val_oidc_enabled') }}</span>
                                                </div>
                                            </div>

                                            {{-- URL konfigurasi OIDC --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_oidc_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oidcConfigUrl }}">
                                                    {{ $oidcConfigUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oidcConfigUrl }}', 'URL konfigurasi OIDC tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- Endpoint userinfo OIDC --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_userinfo_url') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white truncate" title="{{ $oauthUserInfoUrl }}">
                                                    {{ $oauthUserInfoUrl }}
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('{{ $oauthUserInfoUrl }}', 'Endpoint UserInfo tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>

                                            {{-- Cakupan OIDC yang didukung --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-[8px] bg-neutral-50 dark:bg-neutral-800/50 gap-2">
                                                <span class="font-semibold text-neutral-700 dark:text-neutral-300 w-44 shrink-0">{{ __('mcp.chatgpt_lbl_oidc_scopes') }}</span>
                                                <div class="flex-1 font-mono text-[11px] text-neutral-900 dark:text-white">
                                                    openid profile email
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="copyText('openid profile email', 'Cakupan OIDC tersalin!')"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] shrink-0">
                                                    {{ __('mcp.btn_copy_short') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- LANGKAH 3 — Buat dan periksa tools --}}
                            <div class="p-5 rounded-[16px] bg-white dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center shrink-0">3</span>
                                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_step3_heading') }}</h4>
                                </div>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                    {!! __('mcp.chatgpt_step3_desc', ['count' => count($tools)]) !!}
                                </p>
                                <div class="p-3.5 rounded-[12px] bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-500/20 flex flex-wrap items-center gap-2 text-xs">
                                    <div class="flex items-center gap-1.5 font-semibold text-emerald-800 dark:text-emerald-200">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>{{ count($tools) }} Tools Bisnis Aktif Terverifikasi:</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-[6px] bg-white dark:bg-neutral-900 text-[11px] text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800">Laporan Laba Rugi</span>
                                    <span class="px-2 py-0.5 rounded-[6px] bg-white dark:bg-neutral-900 text-[11px] text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800">Saldo Kas & Bank</span>
                                    <span class="px-2 py-0.5 rounded-[6px] bg-white dark:bg-neutral-900 text-[11px] text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800">Stok & Mutasi</span>
                                    <span class="px-2 py-0.5 rounded-[6px] bg-white dark:bg-neutral-900 text-[11px] text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800">POS & Penjualan</span>
                                    <span class="px-2 py-0.5 rounded-[6px] bg-white dark:bg-neutral-900 text-[11px] text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-800">CRM Pelanggan</span>
                                </div>
                            </div>

                            {{-- LANGKAH 4 — Uji di percakapan --}}
                            <div class="p-5 rounded-[16px] bg-white dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold flex items-center justify-center shrink-0">4</span>
                                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_step4_heading') }}</h4>
                                </div>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {{ __('mcp.chatgpt_step4_desc') }}
                                </p>

                                {{-- Prompt Cards --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    {{-- Prompt 1 --}}
                                    <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/70 dark:border-neutral-800 flex flex-col justify-between gap-2.5">
                                        <div class="space-y-1">
                                            <div class="text-[10px] text-neutral-400 uppercase font-semibold">Kas & Likuiditas</div>
                                            <p class="text-xs text-neutral-800 dark:text-neutral-200 italic leading-relaxed">
                                                "{{ __('mcp.chatgpt_test_prompt_1') }}"
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="copyText('{{ addslashes(__('mcp.chatgpt_test_prompt_1')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                                            class="w-full py-1.5 px-2.5 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white text-[11px] font-medium flex items-center justify-center gap-1.5 transition-colors">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                            <span>{{ __('mcp.chatgpt_btn_copy_prompt') }}</span>
                                        </button>
                                    </div>

                                    {{-- Prompt 2 --}}
                                    <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/70 dark:border-neutral-800 flex flex-col justify-between gap-2.5">
                                        <div class="space-y-1">
                                            <div class="text-[10px] text-neutral-400 uppercase font-semibold">Laporan Laba Rugi</div>
                                            <p class="text-xs text-neutral-800 dark:text-neutral-200 italic leading-relaxed">
                                                "{{ __('mcp.chatgpt_test_prompt_2') }}"
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="copyText('{{ addslashes(__('mcp.chatgpt_test_prompt_2')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                                            class="w-full py-1.5 px-2.5 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white text-[11px] font-medium flex items-center justify-center gap-1.5 transition-colors">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                            <span>{{ __('mcp.chatgpt_btn_copy_prompt') }}</span>
                                        </button>
                                    </div>

                                    {{-- Prompt 3 --}}
                                    <div class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/70 dark:border-neutral-800 flex flex-col justify-between gap-2.5">
                                        <div class="space-y-1">
                                            <div class="text-[10px] text-neutral-400 uppercase font-semibold">Stok & Inventori</div>
                                            <p class="text-xs text-neutral-800 dark:text-neutral-200 italic leading-relaxed">
                                                "{{ __('mcp.chatgpt_test_prompt_3') }}"
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="copyText('{{ addslashes(__('mcp.chatgpt_test_prompt_3')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                                            class="w-full py-1.5 px-2.5 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white text-[11px] font-medium flex items-center justify-center gap-1.5 transition-colors">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                            <span>{{ __('mcp.chatgpt_btn_copy_prompt') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mode 2B: Custom GPTs Actions (OpenAPI 3.1) --}}
                    <div x-show="chatgptMode === 'actions'" class="space-y-4" style="display: none;">
                        <div class="space-y-3">
                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_actions_step1_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {!! __('mcp.chatgpt_actions_step1_desc') !!}
                                </p>
                            </div>

                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_actions_step2_title') }}</h4>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    {!! __('mcp.chatgpt_actions_step2_desc', ['count' => count($tools)]) !!}
                                </p>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        readonly
                                        :value="getChatGptOpenApiUrl()"
                                        class="flex-1 font-mono text-xs bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white px-3.5 py-2 rounded-[10px] border border-neutral-200 dark:border-neutral-700 select-all" />
                                    <button
                                        type="button"
                                        @click="copyText(getChatGptOpenApiUrl(), '{{ addslashes(__('mcp.toast_openapi_url_copied')) }}')"
                                        class="px-4 py-2 rounded-[10px] bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-medium flex items-center gap-1.5 transition-colors">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('mcp.btn_copy_url') }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                                <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.chatgpt_actions_step3_title') }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800">
                                        <div class="text-[10px] text-neutral-400 uppercase">Authentication Type</div>
                                        <div class="font-semibold text-neutral-900 dark:text-white mt-0.5">API Key</div>
                                    </div>
                                    <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800">
                                        <div class="text-[10px] text-neutral-400 uppercase">Auth Type</div>
                                        <div class="font-semibold text-neutral-900 dark:text-white mt-0.5">Bearer</div>
                                    </div>
                                    <div class="p-2.5 rounded-[10px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                                        <div>
                                            <div class="text-[10px] text-neutral-400 uppercase">API Key Value</div>
                                            <div class="font-mono text-[11px] text-neutral-900 dark:text-white mt-0.5">{{ __('mcp.chatgpt_your_mcp_token') }}</div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="copyText(getToken(), '{{ addslashes(__('mcp.toast_token_copied')) }}')"
                                            class="text-blue-600 dark:text-blue-400 hover:underline text-[11px]">
                                            {{ __('mcp.btn_copy_short') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Claude Code CLI --}}
                <div x-show="activeProviderTab === 'claude_code'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-neutral-500/10 text-neutral-800 dark:text-neutral-200 text-xs font-semibold">
                            {{ __('mcp.claude_code_badge') }}
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.claude_code_subtitle') }}</span>
                    </div>

                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                        <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.claude_code_register_title') }}</h4>
                        <div class="relative">
                            <pre class="p-3.5 rounded-[12px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getClaudeCodeCmd()"></code></pre>
                            <button
                                type="button"
                                @click="copyText(getClaudeCodeCmd(), '{{ addslashes(__('mcp.toast_cli_cmd_copied')) }}')"
                                class="absolute top-2.5 right-2.5 px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>{{ __('mcp.btn_copy_cmd') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-emerald-500"></i>
                                <span>{{ __('mcp.claude_code_verify_title') }}</span>
                            </div>
                            <pre class="p-2.5 rounded-[8px] bg-neutral-100 dark:bg-neutral-900 font-mono text-[11px] text-neutral-800 dark:text-neutral-200">claude mcp list</pre>
                            <p class="text-[11px] text-neutral-500">{!! __('mcp.claude_code_verify_desc', ['count' => count($tools)]) !!}</p>
                        </div>

                        <div class="p-4 rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <div class="font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="message-square" class="w-4 h-4 text-blue-500"></i>
                                <span>{{ __('mcp.claude_code_example_title') }}</span>
                            </div>
                            <pre class="p-2.5 rounded-[8px] bg-neutral-100 dark:bg-neutral-900 font-mono text-[11px] text-neutral-800 dark:text-neutral-200">claude "{{ __('mcp.claude_code_example_cmd') }}"</pre>
                            <p class="text-[11px] text-neutral-500">{{ __('mcp.claude_code_example_desc') }}</p>
                        </div>
                    </div>
                </div>

                {{-- 3. Google Gemini --}}
                <div x-show="activeProviderTab === 'gemini'" class="space-y-5" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-[8px] bg-purple-500/10 text-purple-600 dark:text-purple-400 text-xs font-semibold">
                            {{ __('mcp.gemini_badge') }}
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('mcp.gemini_compat_subtitle') }}</span>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white">{{ __('mcp.gemini_how_it_works_title') }}</h4>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                {!! __('mcp.gemini_how_it_works_desc') !!}
                            </p>
                        </div>

                        {{-- Python SDK Code Box --}}
                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-t-[16px] bg-neutral-800 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>gemini_cooca_bridge.py</span>
                                <button
                                    type="button"
                                    @click="copyText(getGeminiPythonCode(), '{{ addslashes(__('mcp.toast_python_script_copied')) }}')"
                                    class="px-3 py-1 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-xs font-medium flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('mcp.btn_copy_python') }}</span>
                                </button>
                            </div>
                            <pre class="p-4 rounded-b-[16px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto leading-relaxed select-all"><code x-text="getGeminiPythonCode()"></code></pre>
                        </div>

                        {{-- cURL Quick Test Box --}}
                        <div class="relative">
                            <div class="flex items-center justify-between px-4 py-2 rounded-t-[14px] bg-neutral-800/80 text-neutral-300 text-xs font-mono border-b border-neutral-700">
                                <span>{{ __('mcp.gemini_curl_test_title') }}</span>
                                <button
                                    type="button"
                                    @click="copyText(getGeminiCurl(), '{{ addslashes(__('mcp.toast_curl_cmd_copied')) }}')"
                                    class="px-2.5 py-0.5 rounded-[6px] bg-white/10 hover:bg-white/20 text-white text-[11px] flex items-center gap-1">
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>{{ __('mcp.btn_copy_curl') }}</span>
                                </button>
                            </div>
                            <pre class="p-3.5 rounded-b-[14px] bg-neutral-900 text-neutral-100 font-mono text-xs overflow-x-auto select-all"><code x-text="getGeminiCurl()"></code></pre>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- KARTU 3B: Katalog 10 Domain Tools & Rekomendasi Prompt AI --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="wrench" class="w-4 h-4 text-blue-500"></i>
                        <span>{{ __('mcp.tools_catalog_title') }}</span>
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.tools_catalog_desc') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-mono font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                    {{ count($tools) }} {{ __('mcp.tool_verified_badge') }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Tool 1 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">finance_record_expense</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">mcp:expenses:write</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_1_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_1_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_1_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 2 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">finance_get_cash_and_bank_balances</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">mcp:finance:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_2_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_2_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_2_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 3 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">inventory_create_product</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">mcp:products:write</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_3_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_3_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_3_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 4 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400">inventory_check_stock</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">mcp:products:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_4_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_4_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_4_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 5 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400">social_schedule_post</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">mcp:social:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_5_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_5_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_5_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 6 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-purple-600 dark:text-purple-400">report_get_profit_loss</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">mcp:reports:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_6_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_6_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_6_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 7 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-teal-600 dark:text-teal-400">analytics_get_sales_forecast</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-500/10 text-teal-600 dark:text-teal-400">mcp:reports:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_7_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_7_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_7_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 8 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">crm_search_customer</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400">mcp:crm:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_8_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_8_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_8_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 9 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">whatsapp_send_notification</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">mcp:whatsapp:send</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_9_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_9_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_9_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 10 --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-pink-600 dark:text-pink-400">social_get_insights</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-pink-500/10 text-pink-600 dark:text-pink-400">mcp:social:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_10_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_10_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_10_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 11: Modifier Manage --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-purple-600 dark:text-purple-400">modifier_manage</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">mcp:products:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_11_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_11_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_11_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 12: Material Manage --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400">inventory_manage_material</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">mcp:inventory:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_12_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_12_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_12_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 13: BOM / Recipe Manage --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">inventory_manage_bom</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">mcp:products:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_13_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_13_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_13_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 14: Stock Opname & Stock Transfer --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-cyan-600 dark:text-cyan-400">inventory_stock_opname</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">mcp:inventory:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_14_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_14_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_14_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 15: Purchasing Manage Supplier --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">purchasing_manage_supplier</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400">mcp:purchasing:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_15_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_15_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_15_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 16: Purchasing Manage Order (PO) --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">purchasing_manage_order</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">mcp:purchasing:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_16_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_16_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_16_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 17: Sales Manage Quotation --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-violet-600 dark:text-violet-400">sales_manage_quotation</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-violet-500/10 text-violet-600 dark:text-violet-400">mcp:sales:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_17_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_17_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_17_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 18: Sales Manage Order --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">sales_manage_order</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">mcp:sales:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_18_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_18_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_18_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 19: Finance Manage Invoice --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-teal-600 dark:text-teal-400">finance_manage_invoice</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-500/10 text-teal-600 dark:text-teal-400">mcp:finance:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_19_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_19_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_19_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 20: Services Manage Service --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">services_manage_service</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">mcp:services:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {!! __('mcp.tool_20_desc', ['link' => route('services.index')]) !!}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_20_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_20_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 21: Business Get Logo & Identity --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">business_get_logo</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">mcp:business:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_21_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_21_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_21_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 22: Social Upload Media --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-purple-600 dark:text-purple-400">social_upload_media</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">mcp:social:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_22_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_22_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_22_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 23: Social Manage Post --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-rose-600 dark:text-rose-400">social_manage_post</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">mcp:social:manage</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_23_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_23_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_23_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>

                {{-- Tool 24: Social Manage Channels --}}
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">social_manage_channels</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-sky-500/10 text-sky-600 dark:text-sky-400">mcp:social:read</span>
                        </div>
                        <p class="text-xs text-neutral-700 dark:text-neutral-300 mt-1.5 leading-relaxed">
                            {{ __('mcp.tool_24_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-neutral-500 truncate italic">"{{ __('mcp.tool_24_prompt') }}"</span>
                        <button
                            type="button"
                            @click="copyText('{{ addslashes(__('mcp.tool_24_prompt')) }}', '{{ addslashes(__('mcp.toast_prompt_copied')) }}')"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 transition-colors shrink-0">
                            {{ __('mcp.btn_copy_short') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3C: Pusat Solusi & Pemecahan Kendala (Troubleshooting) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-500"></i>
                <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                    {{ __('mcp.troubleshooting_title') }}
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>{{ __('mcp.faq_chatgpt_unauth_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {!! __('mcp.faq_chatgpt_unauth_a') !!}
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <span>{{ __('mcp.faq_mobile_access_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {{ __('mcp.faq_mobile_access_a') }}
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ __('mcp.faq_local_testing_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {!! __('mcp.faq_local_testing_a') !!}
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>{{ __('mcp.faq_error_32000_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {{ __('mcp.faq_error_32000_a') }}
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        <span>{{ __('mcp.faq_error_32001_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {!! __('mcp.faq_error_32001_a') !!}
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-neutral-50/70 dark:bg-neutral-900/50 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                    <h5 class="font-semibold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>{{ __('mcp.faq_data_security_q') }}</span>
                    </h5>
                    <p class="text-[11px] text-neutral-500 leading-relaxed">
                        {{ __('mcp.faq_data_security_a') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- KARTU 4: Live Activity & Audit Log Feed (12-Kolom) --}}
        <div class="lg:col-span-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.logs_title') }}
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('mcp.logs_desc') }}
                    </p>
                </div>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('mcp.lbl_showing_last_15_logs') }}
                </span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-neutral-400 dark:text-neutral-500 border-b border-neutral-100 dark:border-neutral-800/80">
                            <th class="py-2.5 font-medium">{{ __('mcp.col_time') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_tool') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_client') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_duration') }}</th>
                            <th class="py-2.5 font-medium">{{ __('mcp.col_status') }}</th>
                            <th class="py-2.5 font-medium text-right">{{ __('mcp.col_ip') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                        @forelse($logs as $log)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="py-3 text-neutral-500 dark:text-neutral-400 tabular-nums">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3 font-mono font-medium text-neutral-900 dark:text-neutral-200">
                                {{ $log->tool_name }}
                            </td>
                            <td class="py-3 text-neutral-600 dark:text-neutral-300">
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] bg-neutral-100 dark:bg-neutral-800">
                                    {{ ucfirst($log->client_provider) }}
                                </span>
                            </td>
                            <td class="py-3 text-neutral-500 dark:text-neutral-400 tabular-nums">
                                {{ $log->execution_time_ms }} ms
                            </td>
                            <td class="py-3">
                                @if($log->response_status === 'success')
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium text-[11px]">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        {{ __('mcp.status_success') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-500 font-medium text-[11px]" title="{{ $log->error_message }}">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ __('mcp.status_error') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-right text-neutral-400 dark:text-neutral-500 font-mono text-[11px]">
                                {{ $log->ip_address ?: '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                {{ __('mcp.empty_logs') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>

    </div>

    {{-- MODAL SHEET: Buat Token Baru (Bento Apple HIG) --}}
    <template x-teleport="body">
        <div
            x-show="openCreateModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="openCreateModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="openCreateModal = false"
                aria-hidden="true"></div>

            <div
                @click.away="openCreateModal = false"
                @keydown.escape.window="openCreateModal = false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-create-token-title"
                x-show="openCreateModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto my-auto">

                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
                    <h3 id="modal-create-token-title" class="text-base font-semibold text-neutral-900 dark:text-white">
                        {{ __('mcp.modal_create_title') }}
                    </h3>
                    <button
                        type="button"
                        @click="openCreateModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="{{ __('mcp.aria_close_modal') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <form action="{{ route('settings.integrations.mcp.tokens.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        {{ __('mcp.input_token_name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="{{ __('mcp.input_token_name_ph') }}"
                        class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3.5 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            {{ __('mcp.input_provider') }}
                        </label>
                        <select
                            name="provider_hint"
                            class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                            <option value="all">{{ __('mcp.lbl_all_providers_universal') }}</option>
                            <option value="claude">Claude (Desktop / Code / Web)</option>
                            <option value="openai">ChatGPT / OpenAI</option>
                            <option value="gemini">Google Gemini</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            {{ __('mcp.input_expires') }}
                        </label>
                        <select
                            name="expires_in"
                            class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                            <option value="never">{{ __('mcp.expires_never') }}</option>
                            <option value="30_days">{{ __('mcp.expires_30_days') }}</option>
                            <option value="90_days">{{ __('mcp.expires_90_days') }}</option>
                            <option value="1_year">{{ __('mcp.expires_1_year') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('mcp.permissions_title') }}
                    </label>

                    <div class="space-y-2 p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-h-56 overflow-y-auto">
                        <label class="flex items-center gap-2.5 text-xs text-neutral-900 dark:text-white font-medium pb-2 border-b border-neutral-200 dark:border-neutral-800 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="*" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_all') }}</span>
                        </label>

                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:expenses:write" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_finance_write') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:finance:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_finance_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:finance:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_finance_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:products:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_products_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:products:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_products_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:inventory:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_inventory_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:purchasing:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_purchasing_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:sales:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_sales_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:social:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_social_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:social:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_social_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:reports:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_reports_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:analytics:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_analytics_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:crm:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_crm_read') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:whatsapp:send" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_whatsapp_send') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:services:manage" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_services_manage') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                            <input type="checkbox" name="abilities[]" value="mcp:business:read" checked class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                            <span>{{ __('mcp.perm_business_read') }}</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="openCreateModal = false"
                        class="px-4 py-2.5 min-h-[44px] rounded-[12px] text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        {{ __('mcp.btn_cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 min-h-[44px] rounded-[12px] bg-neutral-900 hover:bg-black dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100 text-white text-xs font-semibold shadow-sm transition-all">
                        {{ __('mcp.btn_submit_create_token') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

    {{-- MODAL DETAIL AUTORISASI & HAK AKSES MCP --}}
    <template x-teleport="body">
        <div
            x-show="openViewModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="openViewModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="openViewModal = false"
                aria-hidden="true"></div>

            <div
                @click.away="openViewModal = false"
                @keydown.escape.window="openViewModal = false"
                role="dialog"
                aria-modal="true"
                x-show="openViewModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto my-auto">

                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                            {{ __('mcp.modal_view_auth_title') }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="openViewModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="{{ __('mcp.btn_close') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <template x-if="activeTokenDetail">
                    <div class="space-y-4">
                        {{-- Ringkasan Token Card --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-bold text-neutral-900 dark:text-white" x-text="activeTokenDetail.name"></span>
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-mono bg-neutral-200/70 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-semibold" x-text="(activeTokenDetail.provider_hint || 'all').toUpperCase()"></span>
                                </div>
                                <div>
                                    <template x-if="!activeTokenDetail.is_active">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-rose-500/10 text-rose-500">
                                            {{ __('mcp.status_inactive') }}
                                        </span>
                                    </template>
                                    <template x-if="activeTokenDetail.is_active && activeTokenDetail.is_expired">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-600">
                                            {{ __('mcp.status_expired') }}
                                        </span>
                                    </template>
                                    <template x-if="activeTokenDetail.is_active && !activeTokenDetail.is_expired">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                            {{ __('mcp.status_active') }}
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-neutral-200/60 dark:border-neutral-800 text-xs">
                                <div>
                                    <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ __('mcp.lbl_token_id') }}:</span>
                                    <div class="font-mono text-[11px] text-neutral-800 dark:text-neutral-200 select-all" x-text="'cooca_mcp_live_••••••' + activeTokenDetail.token_hash"></div>
                                </div>
                                <div>
                                    <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ __('mcp.lbl_created_by') }}:</span>
                                    <div class="text-[11px] font-medium text-neutral-800 dark:text-neutral-200" x-text="activeTokenDetail.user_name + ' (' + (activeTokenDetail.created_at || '-') + ')'"></div>
                                </div>
                                <div>
                                    <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ __('mcp.lbl_last_used') }}:</span>
                                    <div class="text-[11px] font-medium text-neutral-800 dark:text-neutral-200" x-text="activeTokenDetail.last_used_at ? activeTokenDetail.last_used_at + ' (' + activeTokenDetail.last_used_diff + ')' : '{{ __('mcp.lbl_never_used') }}'"></div>
                                </div>
                                <div>
                                    <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ __('mcp.lbl_expires_at') }}:</span>
                                    <div class="text-[11px] font-medium text-neutral-800 dark:text-neutral-200" x-text="activeTokenDetail.expires_at || '{{ __('mcp.lbl_never_expires') }}'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Hak Akses & Abilities --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-neutral-900 dark:text-white uppercase tracking-wider">
                                {{ __('mcp.lbl_abilities_granted') }}
                            </h4>

                            <template x-if="activeTokenDetail.abilities.includes('*')">
                                <div class="p-4 rounded-[16px] bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="check-check" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200">
                                            {{ __('mcp.lbl_all_access_granted') }}
                                        </div>
                                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-0.5 leading-relaxed">
                                            {{ __('mcp.lbl_all_access_granted_desc') }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!activeTokenDetail.abilities.includes('*')">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-1">
                                    <template x-for="ability in activeTokenDetail.abilities" :key="ability">
                                        <div class="p-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                            <span class="font-mono text-[11px] text-neutral-800 dark:text-neutral-200 font-medium" x-text="ability"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Tombol Tindakan Modal View --}}
                        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between gap-3">
                            <button
                                type="button"
                                @click="openViewModal = false"
                                class="px-4 py-2.5 min-h-[44px] rounded-[12px] text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                                {{ __('mcp.btn_close') }}
                            </button>
                            <button
                                type="button"
                                @click="openEdit(activeTokenDetail)"
                                class="px-4 py-2.5 min-h-[44px] rounded-[12px] bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>{{ __('mcp.btn_edit_auth') }}</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- MODAL EDIT AUTORISASI & HAK AKSES MCP --}}
    <template x-teleport="body">
        <div
            x-show="openEditModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="openEditModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="openEditModal = false"
                aria-hidden="true"></div>

            <div
                @click.away="openEditModal = false"
                @keydown.escape.window="openEditModal = false"
                role="dialog"
                aria-modal="true"
                x-show="openEditModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200 dark:border-neutral-800 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto my-auto">

                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                            {{ __('mcp.modal_edit_auth_title') }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="openEditModal = false"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="{{ __('mcp.btn_cancel') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="editTokenForm.update_url" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            {{ __('mcp.input_token_name') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            required
                            x-model="editTokenForm.name"
                            class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3.5 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                {{ __('mcp.input_provider') }}
                            </label>
                            <select
                                name="provider_hint"
                                x-model="editTokenForm.provider_hint"
                                class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                                <option value="all">{{ __('mcp.lbl_all_providers_universal') }}</option>
                                <option value="claude">Claude (Desktop / Code / Web)</option>
                                <option value="openai">ChatGPT / OpenAI</option>
                                <option value="gemini">Google Gemini</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                {{ __('mcp.lbl_expires_at') }}
                            </label>
                            <select
                                name="expires_in"
                                x-model="editTokenForm.expires_in"
                                class="w-full min-h-[44px] text-[16px] sm:text-[15px] px-3 py-2.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-neutral-900 dark:text-white focus:outline-none">
                                <option value="keep">{{ __('mcp.expires_keep') }}</option>
                                <option value="never">{{ __('mcp.expires_never') }}</option>
                                <option value="30_days">{{ __('mcp.expires_30_days') }}</option>
                                <option value="90_days">{{ __('mcp.expires_90_days') }}</option>
                                <option value="1_year">{{ __('mcp.expires_1_year') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Toggle Status Aktif --}}
                    <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-neutral-900 dark:text-white">
                                {{ __('mcp.input_is_active') }}
                            </div>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                {{ __('mcp.input_is_active_help') }}
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                :checked="editTokenForm.is_active"
                                @change="editTokenForm.is_active = $event.target.checked"
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-300 peer-focus:outline-none rounded-full peer dark:bg-neutral-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-neutral-600 peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    {{-- Checkbox Hak Akses (Abilities) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                {{ __('mcp.permissions_title') }} <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-neutral-400" x-text="editTokenForm.abilities.includes('*') ? 'Semua Akses Aktif' : editTokenForm.abilities.length + ' dipilih'"></span>
                        </div>

                        <div class="space-y-2 p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-h-56 overflow-y-auto">
                            <label class="flex items-center gap-2.5 text-xs text-neutral-900 dark:text-white font-semibold pb-2 border-b border-neutral-200 dark:border-neutral-800 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="*"
                                    :checked="editTokenForm.abilities.includes('*')"
                                    @click="toggleEditAbility('*')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_all') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:social:manage"
                                    :checked="hasEditAbility('mcp:social:manage')"
                                    @click="toggleEditAbility('mcp:social:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_social_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:social:read"
                                    :checked="hasEditAbility('mcp:social:read')"
                                    @click="toggleEditAbility('mcp:social:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_social_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:expenses:write"
                                    :checked="hasEditAbility('mcp:expenses:write')"
                                    @click="toggleEditAbility('mcp:expenses:write')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_finance_write') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:finance:read"
                                    :checked="hasEditAbility('mcp:finance:read')"
                                    @click="toggleEditAbility('mcp:finance:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_finance_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:finance:manage"
                                    :checked="hasEditAbility('mcp:finance:manage')"
                                    @click="toggleEditAbility('mcp:finance:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_finance_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:products:manage"
                                    :checked="hasEditAbility('mcp:products:manage')"
                                    @click="toggleEditAbility('mcp:products:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_products_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:products:read"
                                    :checked="hasEditAbility('mcp:products:read')"
                                    @click="toggleEditAbility('mcp:products:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_products_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:inventory:manage"
                                    :checked="hasEditAbility('mcp:inventory:manage')"
                                    @click="toggleEditAbility('mcp:inventory:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_inventory_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:purchasing:manage"
                                    :checked="hasEditAbility('mcp:purchasing:manage')"
                                    @click="toggleEditAbility('mcp:purchasing:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_purchasing_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:sales:manage"
                                    :checked="hasEditAbility('mcp:sales:manage')"
                                    @click="toggleEditAbility('mcp:sales:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_sales_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:services:manage"
                                    :checked="hasEditAbility('mcp:services:manage')"
                                    @click="toggleEditAbility('mcp:services:manage')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_services_manage') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:business:read"
                                    :checked="hasEditAbility('mcp:business:read')"
                                    @click="toggleEditAbility('mcp:business:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_business_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:reports:read"
                                    :checked="hasEditAbility('mcp:reports:read')"
                                    @click="toggleEditAbility('mcp:reports:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_reports_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:analytics:read"
                                    :checked="hasEditAbility('mcp:analytics:read')"
                                    @click="toggleEditAbility('mcp:analytics:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_analytics_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:crm:read"
                                    :checked="hasEditAbility('mcp:crm:read')"
                                    @click="toggleEditAbility('mcp:crm:read')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_crm_read') }}</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="abilities[]"
                                    value="mcp:whatsapp:send"
                                    :checked="hasEditAbility('mcp:whatsapp:send')"
                                    @click="toggleEditAbility('mcp:whatsapp:send')"
                                    class="rounded border-neutral-300 text-neutral-900 dark:bg-neutral-800" />
                                <span>{{ __('mcp.perm_whatsapp_send') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="openEditModal = false"
                            class="px-4 py-2.5 min-h-[44px] rounded-[12px] text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                            {{ __('mcp.btn_cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 min-h-[44px] rounded-[12px] bg-neutral-900 hover:bg-black dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer">
                            {{ __('mcp.btn_save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Toast Notification (Bento Apple HIG Floating Pill) --}}
    <div
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-neutral-900/95 dark:bg-white/95 text-white dark:text-neutral-900 shadow-2xl backdrop-blur-md text-xs font-medium border border-white/10 dark:border-black/10 pointer-events-none"
        style="display: none;">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 dark:text-emerald-600"></i>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection

