{{--
    COOCA In-App Help Chatbot & AI Business Assistant Widget (Apple HIG Aesthetic)
    - Mode Switcher: Help Center (Panduan Fitur) vs Business Assistant (Analisis Data Bisnis)
    - High Traffic Queue Management: Real-time antrean, background polling, retry indicator
    - Isolasi Data Multi-Tenant 100% aman (Zero Operational Data Leakage pada Help Center)
    - Kontrol Otorisasi RBAC granular (Permission-gated untuk Business Assistant)
    - Tanpa form pendaftaran (langsung pakai untuk seluruh pengguna terotentikasi)
--}}

<script>
function safeSessionGet(key) {
    try { return window.sessionStorage ? window.sessionStorage.getItem(key) : null; } catch(e) { return null; }
}
function safeSessionSet(key, val) {
    try { if (window.sessionStorage) window.sessionStorage.setItem(key, val); } catch(e) {}
}
function safeSessionRemove(key) {
    try { if (window.sessionStorage) window.sessionStorage.removeItem(key); } catch(e) {}
}

window.coocaHelpChatbot = coocaHelpChatbot;

function coocaHelpChatbot() {
    return {
        isOpen: false,
        isMounted: false,
        isHydrated: false,
        showGreeting: true,
        isLoading: false,
        showHistoryView: false,
        historyLoading: false,
        conversationList: [],
        activeMode: 'help_center', // 'help_center' | 'business_assistant'
        activeConversationId: null,
        inputQuery: '',
        messages: [],
        pollingTimers: {},

        ensureHydrated() {
            if (this.isHydrated) return;
            this.isHydrated = true;
            this.isMounted = true;

            // Load saved conversation state from session storage
            try {
                const savedMode = safeSessionGet('cooca_chat_active_mode');
                if (savedMode && (savedMode === 'help_center' || savedMode === 'business_assistant')) {
                    this.activeMode = savedMode;
                }

                const savedConv = safeSessionGet('cooca_chat_conversation_id');
                if (savedConv) {
                    this.activeConversationId = savedConv;
                }

                const saved = safeSessionGet('cooca_help_chat_history_' + this.activeMode);
                if (saved) {
                    this.messages = JSON.parse(saved);
                }
            } catch (e) {}

            // Dismiss greeting if closed previously
            if (safeSessionGet('cooca_help_greeting_dismissed')) {
                this.showGreeting = false;
            }
        },

        init() {
            // Dismiss greeting lightweight check
            if (safeSessionGet('cooca_help_greeting_dismissed')) {
                this.showGreeting = false;
            }

            // Listen for custom event from other pages/components
            window.addEventListener('cooca-open-chatbot', (e) => {
                this.ensureHydrated();
                const detail = e.detail || {};
                this.isOpen = true;
                this.dismissGreeting();
                if (detail.newSession) {
                    this.showHistoryView = false;
                    this.startNewConversation();
                } else if (detail.conversationId) {
                    this.loadConversationFromHistory(detail.conversationId, detail.mode);
                }
            });

            // Defer URL parameter check and full hydration to idle time or after load
            const checkIdleAndDeepLink = () => {
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('open_chatbot')) {
                        this.ensureHydrated();
                        this.isOpen = true;
                        this.dismissGreeting();
                        const convId = urlParams.get('open_chatbot');
                        if (convId && convId !== '1' && convId !== 'new') {
                            this.loadConversationFromHistory(convId, urlParams.get('mode'));
                        } else if (convId === 'new') {
                            this.startNewConversation();
                        }
                        return;
                    }
                } catch(e) {}

                // Non-blocking idle hydration
                if ('requestIdleCallback' in window) {
                    window.requestIdleCallback(() => {
                        this.ensureHydrated();
                    }, { timeout: 4000 });
                }
            };

            if (document.readyState === 'complete') {
                checkIdleAndDeepLink();
            } else {
                window.addEventListener('load', checkIdleAndDeepLink, { once: true });
            }
        },

        async toggleHistoryView() {
            this.showHistoryView = !this.showHistoryView;
            if (this.showHistoryView) {
                await this.fetchConversationList();
            }
        },

        async fetchConversationList() {
            this.historyLoading = true;
            try {
                const res = await fetch('{{ route('help-chatbot.conversations') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.conversationList = data.conversations || [];
                }
            } catch (e) {
                this.conversationList = [];
            } finally {
                this.historyLoading = false;
            }
        },

        async loadConversationFromHistory(conversationId, type) {
            this.isLoading = true;
            try {
                const res = await fetch(`/help-chatbot/history/${conversationId}`);
                if (res.ok) {
                    const data = await res.json();
                    this.activeConversationId = conversationId;
                    if (type) {
                        this.activeMode = type;
                        try { safeSessionSet('cooca_chat_active_mode', type); } catch(e){}
                    }
                    try { safeSessionSet('cooca_chat_conversation_id', conversationId); } catch(e){}

                    this.messages = (data.messages || []).map(m => {
                        const meta = m.metadata || {};
                        return {
                            sender: m.sender,
                            text: m.content,
                            status: m.status || 'completed',
                            is_queued: (m.status === 'pending' || m.status === 'processing'),
                            message_id: m.id,
                            is_data_query: !!m.is_data_query,
                            quick_links: meta.quick_links || [],
                            ai_model: meta.ai_model || null,
                            time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                        };
                    });

                    this.saveHistory();
                    this.showHistoryView = false;
                    this.scrollToBottom();
                }
            } catch (e) {
            } finally {
                this.isLoading = false;
            }
        },

        async deleteConversationFromHistory(conversationId) {
            if (!confirm('Hapus sesi percakapan ini?')) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/help-chatbot/conversations/${conversationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });
                if (res.ok) {
                    this.conversationList = this.conversationList.filter(c => c.id !== conversationId);
                    if (this.activeConversationId === conversationId) {
                        this.clearChat();
                    }
                }
            } catch (e) {}
        },

        switchMode(mode) {
            if (this.activeMode === mode) return;
            this.activeMode = mode;
            try {
                safeSessionSet('cooca_chat_active_mode', mode);
            } catch (e) {}

            // Load mode-specific conversation history
            try {
                const saved = safeSessionGet('cooca_help_chat_history_' + mode);
                this.messages = saved ? JSON.parse(saved) : [];
            } catch (e) {
                this.messages = [];
            }

            this.scrollToBottom();
            if (this.$refs.chatInput) {
                this.$refs.chatInput.focus();
            }
        },

        toggleChat() {
            this.ensureHydrated();
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.dismissGreeting();
                this.$nextTick(() => {
                    this.scrollToBottom();
                    if (this.$refs.chatInput) {
                        this.$refs.chatInput.focus();
                    }
                });
            }
        },

        dismissGreeting() {
            this.showGreeting = false;
            try {
                safeSessionSet('cooca_help_greeting_dismissed', '1');
            } catch (e) {}
        },

        clearChat() {
            this.messages = [];
            try {
                safeSessionRemove('cooca_help_chat_history_' + this.activeMode);
                safeSessionRemove('cooca_chat_conversation_id');
            } catch (e) {}
            this.activeConversationId = null;
            this.inputQuery = '';
        },

        async startNewConversation() {
            this.showHistoryView = false;
            this.clearChat();
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route('help-chatbot.conversations.new') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ type: this.activeMode })
                });

                if (res.ok) {
                    const data = await res.json();
                    this.activeConversationId = data.conversation_id;
                    try {
                        safeSessionSet('cooca_chat_conversation_id', data.conversation_id);
                    } catch (e) {}
                }
            } catch (e) {}

            this.scrollToBottom();
            if (this.$refs.chatInput) {
                this.$refs.chatInput.focus();
            }
        },

        async sendMessage() {
            const query = this.inputQuery.trim();
            if (!query || this.isLoading) return;

            // Push User Message
            this.messages.push({
                sender: 'user',
                text: query,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            this.inputQuery = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const historyPayload = this.messages.slice(-8).map(m => ({
                    sender: m.sender,
                    text: m.text
                }));

                const payload = {
                    message: query,
                    type: this.activeMode,
                    history: historyPayload
                };

                if (this.activeConversationId) {
                    payload.conversation_id = this.activeConversationId;
                }

                const res = await fetch('{{ route('help-chatbot.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.conversation_id) {
                        this.activeConversationId = data.conversation_id;
                        try {
                            safeSessionSet('cooca_chat_conversation_id', data.conversation_id);
                        } catch (e) {}
                    }

                    const isQueued = (data.status === 'queued' || data.status === 'pending');
                    const msgIndex = this.messages.length;

                    this.messages.push({
                        sender: 'assistant',
                        text: data.reply || 'Maaf, saya tidak dapat memahami pertanyaan tersebut.',
                        status: data.status || 'completed',
                        is_queued: isQueued,
                        message_id: data.message_id || null,
                        is_data_query: !!data.is_data_query,
                        quick_links: data.quick_links || [],
                        ai_model: data.ai_model || null,
                        provider: data.provider || null,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });

                    // Jika masuk antrean, mulai polling background status
                    if (isQueued && data.message_id) {
                        this.startPollingStatus(msgIndex, data.message_id);
                    }
                } else {
                    const errData = await res.json().catch(() => ({}));
                    this.messages.push({
                        sender: 'assistant',
                        text: errData.message || 'Sistem Sedang Sibuk. Silakan coba beberapa saat lagi atau hubungi tim Support COOCA melalui WhatsApp atau email untuk mendapatkan bantuan.',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                }
            } catch (err) {
                this.messages.push({
                    sender: 'assistant',
                    text: 'Koneksi terputus. Pastikan perangkat Anda terhubung ke internet.',
                    quick_links: [],
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.isLoading = false;
                this.saveHistory();
                this.scrollToBottom();
            }
        },

        startPollingStatus(msgIndex, messageId) {
            let attempts = 0;
            const maxAttempts = 30; // Max 60 seconds of polling

            const check = async () => {
                attempts++;
                if (attempts > maxAttempts) {
                    if (this.messages[msgIndex]) {
                        this.messages[msgIndex].is_queued = false;
                        this.messages[msgIndex].text = 'Sistem Sedang Sibuk. Silakan coba beberapa saat lagi atau hubungi tim Support COOCA melalui WhatsApp atau email untuk mendapatkan bantuan.';
                    }
                    this.saveHistory();
                    return;
                }

                try {
                    const res = await fetch(`/help-chatbot/status/${messageId}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (this.messages[msgIndex]) {
                            this.messages[msgIndex].status = data.status;
                            if (data.is_completed) {
                                this.messages[msgIndex].is_queued = false;
                                this.messages[msgIndex].text = data.reply;
                                this.messages[msgIndex].quick_links = data.quick_links || [];
                                this.messages[msgIndex].ai_model = data.ai_model || null;
                                this.saveHistory();
                                this.scrollToBottom();
                                return; // Stop polling
                            } else if (data.is_failed) {
                                this.messages[msgIndex].is_queued = false;
                                this.messages[msgIndex].text = data.reply;
                                this.messages[msgIndex].quick_links = data.quick_links || [];
                                this.saveHistory();
                                this.scrollToBottom();
                                return; // Stop polling
                            }
                        }
                    }
                } catch (e) {}

                // Next poll in 2 seconds
                setTimeout(check, 2000);
            };

            setTimeout(check, 2000);
        },

        saveHistory() {
            try {
                safeSessionSet('cooca_help_chat_history_' + this.activeMode, JSON.stringify(this.messages.slice(-15)));
            } catch (e) {}
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('cooca-chatbot-messages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        formatMessage(text) {
            if (!text) return '';

            // Simple safe Markdown parsing (Bold, Headings, Bullet Lists, Code, Links)
            let parsed = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Headings ###
            parsed = parsed.replace(/^### (.*$)/gim, '<h4 class="font-bold text-[13px] text-[#007AFF] dark:text-[#0A84FF] mt-1 mb-1">$1</h4>');

            // Bold **text**
            parsed = parsed.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-black dark:text-white">$1</strong>');

            // Italic *text*
            parsed = parsed.replace(/\*(.*?)\*/g, '<em class="italic opacity-90">$1</em>');

            // Inline Code `text`
            parsed = parsed.replace(/`(.*?)`/g, '<code class="px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[10.5px] text-[#007AFF] dark:text-[#0A84FF]">$1</code>');

            // Markdown Links [Label](url)
            parsed = parsed.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="text-[#007AFF] dark:text-[#0A84FF] underline font-semibold hover:opacity-80" target="_self">$1 &rarr;</a>');

            // Bullet Lists
            parsed = parsed.replace(/^\s*-\s+(.*$)/gim, '<div class="flex items-start gap-1.5 my-0.5"><span class="text-[#007AFF] shrink-0">&bull;</span><span>$1</span></div>');

            // Numbered Lists
            parsed = parsed.replace(/^\s*(\d+)\.\s+(.*$)/gim, '<div class="flex items-start gap-1.5 my-0.5"><span class="font-bold text-[#007AFF] shrink-0">$1.</span><span>$2</span></div>');

            // Newlines to <br>
            parsed = parsed.replace(/\n\n/g, '<div class="my-1.5"></div>').replace(/\n/g, '<br>');

            return parsed;
        }
    };
}
</script>

<div id="cooca-help-chatbot-root"
     x-data="coocaHelpChatbot()"
     x-init="init()"
     x-cloak
     class="print:hidden">

    {{-- 1. FLOATING ACTION BUTTON (FAB) & GREETING TOOLTIP PILL --}}
    <div class="fixed right-4 sm:right-6 bottom-20 sm:bottom-24 lg:bottom-6 z-40 flex flex-col items-end gap-2.5 pointer-events-none select-none">

        {{-- Greeting Tooltip Pill (Desktop & Tablet only, auto-hide on dismiss) --}}
        <div x-show="!isOpen && showGreeting"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="hidden sm:block pointer-events-auto bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 shadow-[0_8px_25px_rgba(0,0,0,0.12)] rounded-2xl p-3 max-w-[280px] text-left relative cursor-pointer hover:shadow-xl transition-all"
             @click="toggleChat()">
            <button type="button"
                    @click.stop="dismissGreeting()"
                    class="absolute -top-1.5 -left-1.5 w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 text-black/50 dark:text-white/60 flex items-center justify-center text-[10px] shadow-xs cursor-pointer"
                    aria-label="Tutup sapaan">
                &times;
            </button>
            <div class="flex items-center gap-1.5 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Asisten Panduan Cooca</span>
            </div>
            <p class="text-[12px] font-medium text-black/80 dark:text-white/90 leading-snug">
                Bingung cara pakai fitur atau butuh analisis bisnis? Tanyakan langsung di sini! 👋
            </p>
        </div>

        {{-- Floating Trigger Button --}}
        <div class="pointer-events-auto relative">
            <button type="button"
                    @click="toggleChat()"
                    :aria-expanded="isOpen"
                    aria-label="Buka Asisten AI Cooca"
                    class="group relative w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-[#007AFF] via-[#5856D6] to-[#AF52DE] text-white flex items-center justify-center shadow-[0_8px_30px_rgba(0,122,255,0.4)] hover:shadow-[0_12px_40px_rgba(0,122,255,0.6)] hover:scale-105 active:scale-95 transition-all cursor-pointer">

                {{-- Ambient Pulse Glow when closed --}}
                <span x-show="!isOpen" class="absolute -inset-1 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#AF52DE] opacity-40 blur-xs group-hover:opacity-60 animate-pulse"></span>

                {{-- Online Active Dot --}}
                <span class="absolute top-0.5 right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-[#1C1C1E] z-10"></span>

                {{-- Icon: Sparkles & Bot (when closed) --}}
                <div x-show="!isOpen" class="relative z-10 flex items-center justify-center transition-transform duration-200 group-hover:rotate-6">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"></path>
                        <rect x="3" y="8" width="18" height="12" rx="4"></rect>
                        <circle cx="8" cy="14" r="1.5" fill="currentColor"></circle>
                        <circle cx="16" cy="14" r="1.5" fill="currentColor"></circle>
                        <path d="M10 17h4"></path>
                    </svg>
                </div>

                {{-- Icon: Close (when open) --}}
                <div x-show="isOpen" class="relative z-10 flex items-center justify-center transition-transform duration-200 rotate-90">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
            </button>
        </div>
    </div>

    {{-- 2. EXPANDABLE CHATBOT CONTAINER WINDOW (Deferred DOM mounting) --}}
    <template x-if="isMounted || isOpen">
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed right-3 sm:right-6 bottom-2 sm:bottom-24 lg:bottom-22 z-50 w-[calc(100vw-1.5rem)] sm:w-[420px] h-[580px] sm:h-[630px] max-h-[85vh] sm:max-h-[calc(100vh-6.5rem)] bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_20px_60px_rgba(0,0,0,0.25)] border border-black/10 dark:border-white/10 flex flex-col overflow-hidden backdrop-blur-2xl">

        {{-- WINDOW HEADER BAR --}}
        <div class="px-4 py-3 bg-gradient-to-r from-[#007AFF] via-[#5856D6] to-[#AF52DE] text-white flex items-center justify-between shadow-xs shrink-0 select-none">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="relative w-9 h-9 rounded-xl bg-white/20 p-1 flex items-center justify-center shrink-0 backdrop-blur-md ring-1 ring-white/30">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"></path>
                        <rect x="3" y="8" width="18" height="12" rx="4"></rect>
                        <circle cx="8" cy="14" r="1.5" fill="currentColor"></circle>
                        <circle cx="16" cy="14" r="1.5" fill="currentColor"></circle>
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#007AFF]"></span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 class="text-[14px] font-bold text-white tracking-tight truncate"
                            x-text="activeMode === 'business_assistant' ? 'COOCA Business Assistant' : 'Asisten Panduan Cooca'"></h3>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-white/20 text-white border border-white/30 uppercase"
                              x-text="activeMode === 'business_assistant' ? 'BI & DATA' : '24/7 AI'"></span>
                    </div>
                    <p class="text-[11px] text-white/80 truncate">
                        {{ \App\Support\Context::business()->name ?? 'Bisnis Aktif' }} &bull; Peran: {{ \App\Support\Context::role() ?? 'Staff' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                {{-- Direct WhatsApp Support Link (From live_chat_widget concept) --}}
                @php
                    $waHelpUrl = \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176');
                    $cleanWaHelp = preg_replace('/[^0-9]/', '', $waHelpUrl);
                    $fullWaHelpUrl = 'https://wa.me/' . ($cleanWaHelp ?: '6285287864176') . '?text=' . rawurlencode('Halo Tim Support COOCA, saya butuh bantuan terkait penggunaan sistem');
                @endphp
                <a href="{{ $fullWaHelpUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="Bantuan WhatsApp Support (+62 852-8786-4176)"
                   class="w-7 h-7 rounded-lg hover:bg-white/15 text-emerald-300 hover:text-emerald-200 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.825 2.787.826h.004c3.182 0 5.768-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.503-4.477 9.98-9.969 9.98-1.747 0-3.385-.453-4.819-1.246l-5.212 1.366 1.393-5.086c-.888-1.488-1.393-3.235-1.393-5.014 0-5.503 4.477-9.98 9.969-9.98 5.492 0 10.031 4.477 10.031 9.98z"/>
                    </svg>
                </a>

                {{-- Conversation History Button --}}
                <button type="button"
                        @click="toggleHistoryView()"
                        :title="showHistoryView ? 'Kembali ke Chat' : 'Daftar Riwayat Percakapan'"
                        :class="showHistoryView ? 'bg-white/25 text-white' : 'text-white/80 hover:text-white hover:bg-white/15'"
                        class="w-7 h-7 rounded-lg active:scale-95 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </button>

                {{-- New Conversation (+) Button --}}
                <button type="button"
                        @click="startNewConversation()"
                        title="Percakapan Baru"
                        class="w-7 h-7 rounded-lg hover:bg-white/15 active:scale-95 text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>

                {{-- Reset / Clear Conversation --}}
                <button type="button"
                        @click="clearChat()"
                        title="Bersihkan percakapan"
                        class="w-7 h-7 rounded-lg hover:bg-white/15 active:scale-95 text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"></path>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                    </svg>
                </button>

                {{-- Close Window Button --}}
                <button type="button"
                        @click="toggleChat()"
                        title="Tutup jendela chat"
                        class="w-7 h-7 rounded-lg hover:bg-white/15 active:scale-95 text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        {{-- IN-WIDGET CONVERSATION HISTORY DRAWER / VIEW --}}
        <div x-show="showHistoryView"
             x-cloak
             class="flex-1 flex flex-col min-h-0 overflow-hidden bg-black/[0.01] dark:bg-white/[0.01]">
            <div class="px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Riwayat Sesi Percakapan</span>
                </span>
                <button type="button"
                        @click="toggleHistoryView()"
                        class="text-[11px] font-semibold text-[#007AFF] hover:underline cursor-pointer">
                    &larr; Chat Aktif
                </button>
            </div>

            <div class="flex-1 p-3 overflow-y-auto space-y-2 text-xs scrollbar-thin">
                <template x-if="historyLoading">
                    <div class="py-12 text-center text-black/50 dark:text-white/50 space-y-2">
                        <div class="w-5 h-5 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-[11px]">Memuat riwayat percakapan...</p>
                    </div>
                </template>

                <template x-if="!historyLoading && conversationList.length === 0">
                    <div class="py-12 text-center text-black/40 dark:text-white/40 space-y-2">
                        <svg class="w-8 h-8 mx-auto opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p class="text-[11px]">Belum ada riwayat tersimpan.</p>
                    </div>
                </template>

                <template x-for="item in conversationList" :key="item.id">
                    <div class="p-2.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 hover:border-[#007AFF]/30 transition flex items-center justify-between gap-2 group">
                        <div class="min-w-0 flex-1 cursor-pointer" @click="loadConversationFromHistory(item.id, item.type)">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="item.type === 'business_assistant' ? 'bg-purple-500' : 'bg-[#007AFF]'"></span>
                                <span class="font-bold text-[11.5px] text-black dark:text-white truncate" x-text="item.title || 'Percakapan'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-black/40 dark:text-white/40">
                                <span x-text="item.type === 'business_assistant' ? 'Business Assistant' : 'Panduan Fitur'"></span>
                                <span x-show="item.messages_count" x-text="'&bull; ' + item.messages_count + ' pesan'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button"
                                    @click="loadConversationFromHistory(item.id, item.type)"
                                    title="Buka percakapan"
                                    class="p-1 rounded-lg bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] transition active:scale-95 cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                            <button type="button"
                                    @click.stop="deleteConversationFromHistory(item.id)"
                                    title="Hapus sesi"
                                    class="p-1 rounded-lg text-rose-500/60 hover:text-rose-500 hover:bg-rose-500/10 transition active:scale-95 cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-2.5 bg-black/[0.02] dark:bg-white/[0.03] border-t border-black/5 dark:border-white/10 text-center">
                <a href="{{ route('help-chatbot.conversations') }}"
                   class="text-[11px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                    <span>Lihat Halaman Lengkap Riwayat</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        {{-- MAIN ACTIVE CHAT VIEW WRAPPER --}}
        <div x-show="!showHistoryView" class="flex-1 flex flex-col min-h-0 overflow-hidden">

        {{-- APPLE HIG SEGMENTED CONTROL MODE SWITCHER (BAGIAN 8 & 9) --}}
        <div class="px-3 py-2 bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 flex items-center gap-1.5 select-none shrink-0">
            <button type="button"
                    @click="switchMode('help_center')"
                    :class="activeMode === 'help_center'
                        ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold border-black/5 dark:border-white/10'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium border-transparent'"
                    class="flex-1 py-1.5 px-2 rounded-xl text-[11px] border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Help Center (Panduan)</span>
            </button>

            <button type="button"
                    @click="switchMode('business_assistant')"
                    :class="activeMode === 'business_assistant'
                        ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold border-black/5 dark:border-white/10'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium border-transparent'"
                    class="flex-1 py-1.5 px-2 rounded-xl text-[11px] border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>Business Assistant (Data)</span>
            </button>
        </div>

        {{-- MESSAGE STREAM BODY --}}
        <div id="cooca-chatbot-messages"
             class="flex-1 p-3.5 sm:p-4 overflow-y-auto space-y-3.5 text-xs text-black dark:text-white scrollbar-thin scrollbar-thumb-black/10 dark:scrollbar-thumb-white/10">

            {{-- 1. Mode-Aware Welcome Card --}}
            <div class="p-3.5 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2 text-black/75 dark:text-white/85 font-bold text-xs">
                    <span class="w-5 h-5 rounded-lg bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center text-[10px]">
                        👋
                    </span>
                    <span>Halo Kak {{ Auth::user()->name ?? 'Pengguna' }}!</span>
                </div>

                {{-- Help Center Mode Greeting --}}
                <template x-if="activeMode === 'help_center'">
                    <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                        Saya asisten panduan pintar COOCA. Silakan tanyakan langsung cara penggunaan fitur atau modul apa saja (Kasir POS, kalkulasi HPP resep, stok gudang, penjualan B2B, atau toko online).
                    </p>
                </template>

                {{-- Business Assistant Mode Greeting --}}
                <template x-if="activeMode === 'business_assistant'">
                    <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                        Saya COOCA AI Business Assistant. Silakan tanyakan langsung analisis data bisnis Anda seperti performa penjualan, omzet harian/bulanan, stok menipis, atau ringkasan operasional.
                    </p>
                </template>
            </div>

            {{-- 2. Message History Stream Loop --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.sender === 'user'
                            ? 'bg-[#007AFF] text-white rounded-2xl rounded-tr-xs max-w-[85%] px-3.5 py-2.5 shadow-xs'
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white rounded-2xl rounded-tl-xs max-w-[92%] px-3.5 py-3 border border-black/5 dark:border-white/10 shadow-xs space-y-2'">

                        {{-- Assistant Header Identifier --}}
                        <template x-if="msg.sender !== 'user'">
                            <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/10 text-[10.5px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="8" width="18" height="12" rx="4"></rect>
                                        <circle cx="8" cy="14" r="1.5" fill="currentColor"></circle>
                                        <circle cx="16" cy="14" r="1.5" fill="currentColor"></circle>
                                    </svg>
                                    <span x-text="msg.is_data_query ? 'COOCA Business Assistant' : 'Cooca AI Assistant'"></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span x-show="msg.is_queued" class="text-[9px] font-semibold px-1.5 py-0.2 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400">Antrean Aktif</span>
                                    <span x-show="msg.ai_model" x-text="msg.ai_model" class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]"></span>
                                </div>
                            </div>
                        </template>

                        {{-- HIGH TRAFFIC QUEUE CARD INDICATOR (BAGIAN 4) --}}
                        <template x-if="msg.is_queued">
                            <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 space-y-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="2" x2="12" y2="6"></line>
                                        <line x1="12" y1="18" x2="12" y2="22"></line>
                                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                    </svg>
                                    <span class="text-[11px] font-bold" x-text="msg.status === 'processing' ? 'Sedang Diproses Sistem...' : 'Menunggu Antrean Sistem...'"></span>
                                </div>
                                <p class="text-[11px] leading-relaxed opacity-90" x-text="msg.text"></p>
                            </div>
                        </template>

                        {{-- Regular Message Content (Markdown Rendered) --}}
                        <template x-if="!msg.is_queued">
                            <div class="text-[12px] leading-relaxed break-words chatbot-markdown"
                                 x-html="formatMessage(msg.text)"></div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- 3. Typing Indicator --}}
            <div x-show="isLoading" class="flex justify-start">
                <div class="bg-black/[0.04] dark:bg-white/[0.06] rounded-2xl rounded-tl-xs px-3.5 py-2.5 border border-black/5 dark:border-white/10 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-bounce"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#5856D6] animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE] animate-bounce [animation-delay:0.4s]"></span>
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50 ml-1"
                          x-text="activeMode === 'business_assistant' ? 'Menganalisis data bisnis...' : 'Menyiapkan panduan...'"></span>
                </div>
            </div>
        </div>

        {{-- FOOTER INPUT BAR --}}
        <div class="p-3 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/10 shrink-0 space-y-1.5">
            <form @submit.prevent="sendMessage()" class="relative flex items-center gap-2">
                <input type="text"
                       x-ref="chatInput"
                       x-model="inputQuery"
                       :disabled="isLoading"
                       :placeholder="activeMode === 'business_assistant' ? 'Tanya omzet, stok menipis, atau absensi...' : 'Tanya cara pakai fitur Cooca...'"
                       class="w-full h-10 pl-3.5 pr-11 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-[#007AFF] transition disabled:opacity-50">

                <button type="submit"
                        :disabled="isLoading || !inputQuery.trim()"
                        class="absolute right-1.5 w-7 h-7 rounded-lg bg-[#007AFF] hover:bg-[#0062CC] text-white flex items-center justify-center transition active:scale-95 disabled:opacity-30 disabled:pointer-events-none cursor-pointer shadow-xs"
                        aria-label="Kirim pertanyaan">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>

            <div class="flex items-center justify-between text-[10px] text-black/40 dark:text-white/40 px-1 select-none">
                <span>Tekan <kbd class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.2 rounded text-[9px]">Enter</kbd> untuk mengirim</span>
                <span class="flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Isolasi Tenant &amp; RBAC Aktif
                </span>
            </div>
        </div>
        </div> {{-- End of MAIN ACTIVE CHAT VIEW WRAPPER --}}

    </div>
    </template>

</div>
