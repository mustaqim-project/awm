{{--
    COOCA In-App Help Chatbot Widget (Bento Apple HIG Aesthetic)
    Asisten Panduan Penggunaan Fitur & Bantuan Operasional Panel Bisnis
    - Tanpa form pendaftaran pengunjung (langsung pakai untuk semua pengguna terotentikasi)
    - Terisolasi 100% per tenant (business_id)
    - Menegakkan kontrol wewenang RBAC (Role-Based Access Control)
--}}

<div id="cooca-help-chatbot-root"
     x-data="coocaHelpChatbot()"
     x-init="init()"
     x-cloak
     class="print:hidden">

    {{-- 1. FLOATING ACTION BUTTON (FAB) & GREETING PILL --}}
    <div class="fixed right-4 sm:right-6 bottom-20 sm:bottom-24 lg:bottom-6 z-40 flex flex-col items-end gap-2.5 pointer-events-none select-none">

        {{-- Greeting Tooltip Pill (Desktop & Tablet only, auto-hide on dismiss) --}}
        <div x-show="!isOpen && showGreeting"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="hidden sm:block pointer-events-auto bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 shadow-[0_8px_25px_rgba(0,0,0,0.12)] rounded-2xl p-3 max-w-[270px] text-left relative cursor-pointer hover:shadow-xl transition-all"
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
                Bingung cara pakai fitur? Tanyakan cara kerja Kasir, HPP, Stok, atau Toko Online di sini! 👋
            </p>
        </div>

        {{-- Floating Trigger Button --}}
        <div class="pointer-events-auto relative">
            <button type="button"
                    @click="toggleChat()"
                    :aria-expanded="isOpen"
                    aria-label="Buka Asisten Panduan Cooca"
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

    {{-- 2. EXPANDABLE CHATBOT CONTAINER WINDOW --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed right-3 sm:right-6 bottom-20 sm:bottom-24 lg:bottom-22 z-50 w-[calc(100vw-1.5rem)] sm:w-[410px] h-[550px] sm:h-[600px] max-h-[calc(100vh-6.5rem)] bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_20px_60px_rgba(0,0,0,0.25)] border border-black/10 dark:border-white/10 flex flex-col overflow-hidden backdrop-blur-2xl">

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
                        <h3 class="text-[14px] font-bold text-white tracking-tight truncate">Asisten Panduan Cooca</h3>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-white/20 text-white border border-white/30 uppercase">24/7 AI</span>
                    </div>
                    <p class="text-[11px] text-white/80 truncate">
                        {{ \App\Support\Context::business()->name ?? 'Bisnis Aktif' }} &bull; Peran: {{ \App\Support\Context::role() ?? 'Staff' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
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

        {{-- MESSAGE STREAM BODY --}}
        <div id="cooca-chatbot-messages"
             class="flex-1 p-3.5 sm:p-4 overflow-y-auto space-y-3.5 text-xs text-black dark:text-white scrollbar-thin scrollbar-thumb-black/10 dark:scrollbar-thumb-white/10">

            {{-- 1. Initial Greeting Card (Always present) --}}
            <div class="p-3.5 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2 text-black/75 dark:text-white/85 font-bold text-xs">
                    <span class="w-5 h-5 rounded-lg bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center text-[10px]">
                        👋
                    </span>
                    <span>Halo Kak {{ Auth::user()->name ?? 'Pengguna' }}!</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saya asisten panduan pintar Cooca. Tanyakan cara penggunaan modul apa saja (Kasir POS, kalkulasi HPP resep, stok gudang, penjualan B2B, atau toko online).
                </p>

                {{-- Quick Prompt Chips --}}
                <div class="pt-1.5 space-y-1.5">
                    <span class="text-[10px] font-bold text-black/40 dark:text-white/40 uppercase tracking-wider block">Pertanyaan Populer:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button"
                                @click="askQuestion('Bagaimana cara buka dan tutup shift kasir POS?')"
                                class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/80 dark:text-white/80 hover:border-[#007AFF] hover:text-[#007AFF] transition active:scale-95 text-left cursor-pointer">
                            🧾 Buka &amp; Tutup Shift Kasir
                        </button>
                        <button type="button"
                                @click="askQuestion('Bagaimana cara menghitung HPP resep dan margin jual?')"
                                class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/80 dark:text-white/80 hover:border-[#007AFF] hover:text-[#007AFF] transition active:scale-95 text-left cursor-pointer">
                            ⚖️ Hitung HPP Resep BOM
                        </button>
                        <button type="button"
                                @click="askQuestion('Bagaimana cara transfer stok antar gudang dan cabang?')"
                                class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/80 dark:text-white/80 hover:border-[#007AFF] hover:text-[#007AFF] transition active:scale-95 text-left cursor-pointer">
                            📦 Transfer Stok Gudang
                        </button>
                        <button type="button"
                                @click="askQuestion('Bagaimana cara memilih tema toko online dari 42 tema?')"
                                class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/80 dark:text-white/80 hover:border-[#007AFF] hover:text-[#007AFF] transition active:scale-95 text-left cursor-pointer">
                            🌐 42 Tema Toko Online
                        </button>
                        <button type="button"
                                @click="askQuestion('Berapa omzet kasir penjualan hari ini?')"
                                class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/80 dark:text-white/80 hover:border-[#007AFF] hover:text-[#007AFF] transition active:scale-95 text-left cursor-pointer">
                            📊 Cek Omzet (Izin Khusus)
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Message History Loop --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.sender === 'user'
                            ? 'bg-[#007AFF] text-white rounded-2xl rounded-tr-xs max-w-[85%] px-3.5 py-2.5 shadow-xs'
                            : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white rounded-2xl rounded-tl-xs max-w-[92%] px-3.5 py-3 border border-black/5 dark:border-white/10 shadow-xs space-y-2'">

                        {{-- Assistant Header Identifier --}}
                        <template x-if="msg.sender !== 'user'">
                            <div class="flex items-center gap-1.5 pb-1 border-b border-black/5 dark:border-white/10 text-[10.5px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="8" width="18" height="12" rx="4"></rect>
                                    <circle cx="8" cy="14" r="1.5" fill="currentColor"></circle>
                                    <circle cx="16" cy="14" r="1.5" fill="currentColor"></circle>
                                </svg>
                                <span>Cooca AI</span>
                            </div>
                        </template>

                        {{-- Message Content Text (Supports HTML/Markdown formatting) --}}
                        <div class="text-[12px] leading-relaxed break-words chatbot-markdown"
                             x-html="formatMessage(msg.text)"></div>

                        {{-- Quick Navigation Action Links (if any) --}}
                        <template x-if="msg.quick_links && msg.quick_links.length > 0">
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 flex flex-wrap gap-1.5">
                                <template x-for="(link, lIdx) in msg.quick_links" :key="lIdx">
                                    <a :href="link.url"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#007AFF]/10 dark:bg-[#0A84FF]/20 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF] hover:text-white text-[11px] font-bold transition-all active:scale-95 shadow-2xs">
                                        <span x-text="link.label"></span>
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M5 12h14"></path>
                                            <path d="m12 5 7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </template>
                            </div>
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
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50 ml-1">Menyiapkan jawaban...</span>
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
                       placeholder="Tanya cara pakai fitur Cooca..."
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
                <span>Isolasi Multi-Tenant Aktif</span>
            </div>
        </div>

    </div>

</div>

<script>
function coocaHelpChatbot() {
    return {
        isOpen: false,
        showGreeting: true,
        isLoading: false,
        inputQuery: '',
        messages: [],

        init() {
            // Load saved conversation state from session storage
            try {
                const saved = sessionStorage.getItem('cooca_help_chat_history');
                if (saved) {
                    this.messages = JSON.parse(saved);
                }
            } catch (e) {}

            // Dismiss greeting if closed previously
            if (sessionStorage.getItem('cooca_help_greeting_dismissed')) {
                this.showGreeting = false;
            }
        },

        toggleChat() {
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
                sessionStorage.setItem('cooca_help_greeting_dismissed', '1');
            } catch (e) {}
        },

        clearChat() {
            this.messages = [];
            try {
                sessionStorage.removeItem('cooca_help_chat_history');
            } catch (e) {}
            this.inputQuery = '';
        },

        askQuestion(query) {
            this.inputQuery = query;
            this.sendMessage();
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

                const res = await fetch('{{ route('help-chatbot.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        message: query,
                        history: historyPayload
                    })
                });

                if (res.ok) {
                    const data = await res.json();
                    this.messages.push({
                        sender: 'assistant',
                        text: data.reply || 'Maaf, saya tidak dapat memahami pertanyaan tersebut.',
                        quick_links: data.quick_links || [],
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    const errData = await res.json().catch(() => ({}));
                    this.messages.push({
                        sender: 'assistant',
                        text: errData.message || 'Terjadi gangguan saat memproses jawaban. Silakan coba sesaat lagi.',
                        quick_links: [],
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

        saveHistory() {
            try {
                sessionStorage.setItem('cooca_help_chat_history', JSON.stringify(this.messages.slice(-15)));
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

