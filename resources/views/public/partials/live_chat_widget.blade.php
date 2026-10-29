{{--
    COOCA Official Website Live Chat Widget
    Powered by Laravel Broadcasting with Reverb
    Mandatory Visitor Input: Nama, Nomor HP, Email, Pertanyaan
--}}

<div id="cooca-live-chat-root" x-data="coocaLiveChatWidget()" x-init="init()" x-cloak class="print:hidden">

    {{-- FLOATING TRIGGER BUTTON & GREETING POPUP --}}
    <div class="fixed floating-live-chat-fab right-4 sm:right-6 lg:right-6 z-50 flex flex-col items-end gap-2 pointer-events-none">
        
        {{-- Floating Greeting Pill / Prompt (Desktop/Tablet only, hidden on mobile to protect hero & bottom bar) --}}
        <div x-show="!isOpen && showGreeting && !sessionToken" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="hidden sm:block pointer-events-auto bg-white dark:bg-[#1E1B4B] border border-violet-100 dark:border-violet-900/50 shadow-xl rounded-2xl p-3 max-w-[260px] text-left relative cursor-pointer hover:shadow-2xl transition-shadow"
             @click="toggleChat()">
            <button type="button" @click.stop="dismissGreeting()" class="absolute -top-1.5 -left-1.5 w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-700 flex items-center justify-center text-[10px] shadow-xs" aria-label="Tutup sapaan live chat">
                ✕
            </button>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-violet-700 dark:text-violet-300">Live Support Online</span>
            </div>
            <p class="text-[12.5px] font-medium text-gray-800 dark:text-gray-100 leading-snug">
                Ada yang bisa kami bantu seputar sistem <strong class="text-violet-600 dark:text-violet-400">COOCA</strong>? Chat langsung sekarang! 👋
            </p>
        </div>

        {{-- Floating Action Button (FAB) --}}
        <div class="pointer-events-auto relative">
            {{-- Unread Message Counter Badge --}}
            <span x-show="unreadCount > 0 && !isOpen" x-text="unreadCount" 
                  class="absolute -top-1 -right-1 w-5.5 h-5.5 rounded-full bg-rose-600 text-white font-bold text-[11px] flex items-center justify-center shadow-md animate-bounce z-10">
            </span>

            <button type="button" 
                    @click="toggleChat()" 
                    :aria-expanded="isOpen"
                    aria-label="Buka Live Chat COOCA"
                    class="group relative w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-tr from-[#6D28D9] via-[#7C3AED] to-[#4F46E5] text-white flex items-center justify-center shadow-[0_10px_35px_rgba(124,58,237,0.5)] hover:shadow-[0_14px_45px_rgba(124,58,237,0.7)] hover:scale-105 active:scale-95 transition-all">
                
                {{-- Glowing pulse ring when closed --}}
                <span x-show="!isOpen" class="absolute -inset-1 rounded-full bg-gradient-to-tr from-[#7C3AED] to-[#4F46E5] opacity-40 blur-xs group-hover:opacity-60 animate-pulse"></span>

                {{-- Online green indicator dot --}}
                <span class="absolute top-0.5 right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-gray-900 z-10"></span>

                {{-- Icon: Chat Bubble (when closed) --}}
                <svg x-show="!isOpen" class="w-7 h-7 sm:w-8 sm:h-8 relative z-10 transition-transform duration-200 group-hover:rotate-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    <path d="M8 10h.01"></path>
                    <path d="M12 10h.01"></path>
                    <path d="M16 10h.01"></path>
                </svg>

                {{-- Icon: Close (when open) --}}
                <svg x-show="isOpen" class="w-6 h-6 sm:w-7 sm:h-7 relative z-10 transition-transform duration-200 rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    {{-- EXPANDABLE LIVE CHAT CONTAINER (MODAL / FLOATING POPUP) --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed floating-live-chat-window right-2 sm:right-6 lg:right-6 z-50 w-[calc(100vw-1rem)] sm:w-[410px] h-[580px] sm:h-[620px] max-h-[calc(100vh-6rem)] bg-white dark:bg-[#18181B] rounded-[24px] shadow-[0_20px_60px_rgba(0,0,0,0.3)] border border-violet-100 dark:border-white/10 flex flex-col overflow-hidden">
        
        {{-- HEADER BAR --}}
        <div class="px-4 py-3.5 bg-gradient-to-r from-[#6D28D9] via-[#7C3AED] to-[#4F46E5] text-white flex items-center justify-between shadow-xs shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative w-10 h-10 rounded-full bg-white/20 p-0.5 backdrop-blur-xs flex items-center justify-center shrink-0 ring-1 ring-white/30">
                    <img src="{{ asset('assets/image/cooca-icon.png') }}" class="w-8 h-8 rounded-full object-cover" alt="Cooca Support" onerror="this.src='{{ asset('assets/image/cooca-logo-square.png') }}'">
                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-violet-700"></span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 class="text-[14.5px] font-bold text-white tracking-tight truncate">Live Chat COOCA</h3>
                        <span class="px-1.5 py-0.2 rounded-full text-[9.5px] font-semibold bg-emerald-400/25 text-emerald-200 border border-emerald-400/30">Online</span>
                    </div>
                    <p class="text-[11.5px] text-violet-100/90 truncate flex items-center gap-1">
                        <span x-show="sessionToken" class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                        <span x-text="sessionToken ? (isReverbConnected ? 'Terhubung (Realtime)' : 'Aktif') : 'Tim kami siap membantu Anda'"></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                {{-- Direct WhatsApp Support Link --}}
                <a href="https://wa.me/6285287864176?text=Halo%20COOCA%2C%20saya%20butuh%20bantuan%20seputar%20sistem%20kasir" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   title="Chat via WhatsApp (+62 852-8786-4176)"
                   class="w-8 h-8 rounded-full hover:bg-white/20 text-emerald-300 hover:text-emerald-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.825 2.787.826h.004c3.182 0 5.768-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.766c0 5.503-4.477 9.98-9.969 9.98-1.747 0-3.385-.453-4.819-1.246l-5.212 1.366 1.393-5.086c-.888-1.488-1.393-3.235-1.393-5.014 0-5.503 4.477-9.98 9.969-9.98 5.492 0 10.031 4.477 10.031 9.98z"/>
                    </svg>
                </a>

                {{-- Reset / New Chat Option if in session --}}
                <button type="button" x-show="sessionToken" @click="confirmResetSession()" 
                        title="Mulai sesi chat baru"
                        class="w-8 h-8 rounded-full hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                        <path d="M8 16H3v5"></path>
                    </svg>
                </button>

                {{-- Close Button --}}
                <button type="button" @click="isOpen = false" 
                        title="Tutup Chat"
                        class="w-8 h-8 rounded-full hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- VIEW 1: PRE-CHAT MANDATORY FORM (NAMA, NO HP, EMAIL, PERTANYAAN)          --}}
        {{-- ========================================================================= --}}
        <div x-show="!sessionToken" class="flex-1 flex flex-col p-5 overflow-y-auto min-h-0 bg-slate-50/50 dark:bg-[#18181B]">
            
            <div class="mb-4 text-center">
                <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 mx-auto flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <h4 class="text-[15px] font-bold text-gray-900 dark:text-white">Selamat Datang di Support COOCA</h4>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-1 max-w-[320px] mx-auto leading-relaxed">
                    Mohon isi data diri Anda di bawah ini agar tim kami dapat memberikan solusi terbaik untuk bisnis Anda.
                </p>
            </div>

            {{-- Form Error Alert --}}
            <div x-show="errorMessage" x-cloak class="mb-3.5 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 text-[12px] flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="startChatSession()" class="space-y-3 flex-1 flex flex-col justify-between">
                <div class="space-y-3">
                    {{-- 1. NAMA LENGKAP --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-700 dark:text-gray-200 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </span>
                            <input type="text" x-model="formData.name" required
                                   placeholder="Contoh: Budi Santoso"
                                   class="w-full h-10 pl-9 pr-3 rounded-xl bg-white dark:bg-[#27272A] border border-gray-200 dark:border-white/10 text-[13px] text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                        </div>
                    </div>

                    {{-- 2. NOMOR HP / WHATSAPP --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-700 dark:text-gray-200 mb-1">
                            Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            </span>
                            <input type="tel" x-model="formData.phone" required
                                   placeholder="Contoh: 081234567890"
                                   class="w-full h-10 pl-9 pr-3 rounded-xl bg-white dark:bg-[#27272A] border border-gray-200 dark:border-white/10 text-[13px] text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                        </div>
                    </div>

                    {{-- 3. EMAIL --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-700 dark:text-gray-200 mb-1">
                            Email Aktif <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </span>
                            <input type="email" x-model="formData.email" required
                                   placeholder="Contoh: budi@bisnisanda.com"
                                   class="w-full h-10 pl-9 pr-3 rounded-xl bg-white dark:bg-[#27272A] border border-gray-200 dark:border-white/10 text-[13px] text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                        </div>
                    </div>

                    {{-- 4. PERTANYAAN / PESAN AWAL --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-700 dark:text-gray-200 mb-1">
                            Pertanyaan / Kebutuhan Bisnis <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="formData.message" required rows="3"
                                  placeholder="Ceritakan kendala operasional atau modul apa yang ingin Anda tanyakan..."
                                  class="w-full p-3 rounded-xl bg-white dark:bg-[#27272A] border border-gray-200 dark:border-white/10 text-[13px] text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="isSubmitting"
                            class="w-full h-11 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-semibold text-[13.5px] shadow-lg shadow-violet-500/25 disabled:opacity-50 flex items-center justify-center gap-2 transition-all">
                        <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity=".25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity=".75"></path></svg>
                        <span x-text="isSubmitting ? 'Menghubungkan...' : 'Mulai Chat Sekarang'"></span>
                        <svg x-show="!isSubmitting" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                    <p class="text-[11px] text-gray-400 text-center mt-2 flex items-center justify-center gap-1">
                        <svg class="w-3 h-3 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Data aman dan terenkripsi.
                    </p>
                </div>
            </form>
        </div>

        {{-- ========================================================================= --}}
        {{-- VIEW 2: ACTIVE REALTIME CHAT CONVERSATION (REVERB BROADCASTING)           --}}
        {{-- ========================================================================= --}}
        <div x-show="sessionToken" class="flex-1 flex flex-col min-h-0 bg-slate-50/70 dark:bg-[#18181B]">
            
            {{-- Notification Banner / Lead Summary --}}
            <div class="px-4 py-2 bg-violet-50/80 dark:bg-violet-950/30 border-b border-violet-100 dark:border-violet-900/40 flex items-center justify-between text-[11.5px] text-violet-800 dark:text-violet-300 shrink-0">
                <div class="flex items-center gap-1.5 truncate">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Chat dengan: <strong x-text="visitorData.name || 'Anda'"></strong></span>
                </div>
                <div class="flex items-center gap-1 text-[10.5px] text-violet-600 dark:text-violet-400">
                    <span x-text="isReverbConnected ? '🟢 Reverb Live' : '🟠 Polling Mode'"></span>
                </div>
            </div>

            {{-- Messages Stream Container --}}
            <div id="coocaLiveChatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 min-h-0 scroll-smooth">
                
                {{-- System Welcome Message --}}
                <div class="p-3 rounded-2xl bg-white dark:bg-[#27272A] border border-gray-100 dark:border-white/5 shadow-xs text-[12px] text-gray-600 dark:text-gray-300 leading-relaxed">
                    👋 <strong>Halo <span x-text="visitorData.name || 'Bapak/Ibu'"></span>!</strong> Terima kasih telah menghubungi COOCA. Pertanyaan Anda sudah diteruskan langsung ke tim admin kami melalui dashboard omnichannel. Kami akan segera membalasnya di sini.
                </div>

                {{-- Messages Bubbles --}}
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.is_cooca || msg.is_from_page ? 'items-start' : 'items-end'">
                        
                        {{-- Sender Name & Time --}}
                        <div class="flex items-center gap-1.5 mb-1 px-1 text-[10.5px] text-gray-400">
                            <span class="font-semibold" :class="msg.is_cooca || msg.is_from_page ? 'text-violet-600 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300'" 
                                  x-text="msg.is_cooca || msg.is_from_page ? 'Cooca Support' : 'Anda'"></span>
                            <span>•</span>
                            <span x-text="msg.time"></span>
                        </div>

                        {{-- Bubble content --}}
                        <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-[13px] leading-relaxed shadow-xs"
                             :class="msg.is_cooca || msg.is_from_page 
                                     ? 'bg-white dark:bg-[#27272A] text-gray-800 dark:text-gray-100 border border-gray-200/80 dark:border-white/10 rounded-tl-xs' 
                                     : 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-tr-xs'">
                            
                            {{-- Attached Media Image if present --}}
                            <template x-if="msg.media_url">
                                <div class="mb-2 rounded-xl overflow-hidden border border-white/20">
                                    <img :src="msg.media_url" class="max-w-full max-h-48 object-cover cursor-pointer" @click="window.open(msg.media_url, '_blank')">
                                </div>
                            </template>

                            <div class="whitespace-pre-wrap break-words leading-relaxed" x-html="formatMessage(msg.message, msg.is_cooca || msg.is_from_page)"></div>
                        </div>
                    </div>
                </template>

                {{-- Typing Indicator Bubble (WhatsApp / Apple / Intercom Style) --}}
                <div x-show="isWaitingReply" x-transition.opacity.duration.150ms class="flex flex-col items-start gap-1">
                    <div class="flex items-center gap-1.5 px-1 text-[10.5px] text-gray-400">
                        <span class="font-semibold text-violet-600 dark:text-violet-400">Cooca Support</span>
                        <span>•</span>
                        <span class="text-violet-500 font-medium animate-pulse">sedang mengetik...</span>
                    </div>
                    <div class="rounded-2xl rounded-tl-xs px-4 py-3 bg-white dark:bg-[#27272A] border border-gray-200/80 dark:border-white/10 shadow-xs flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5 py-0.5">
                            <span class="w-2 h-2 rounded-full bg-violet-600 dark:bg-violet-400 animate-bounce [animation-delay:-0.32s]"></span>
                            <span class="w-2 h-2 rounded-full bg-violet-600 dark:bg-violet-400 animate-bounce [animation-delay:-0.16s]"></span>
                            <span class="w-2 h-2 rounded-full bg-violet-600 dark:bg-violet-400 animate-bounce"></span>
                        </div>
                        <span class="text-[12px] text-gray-500 dark:text-gray-400 italic">mengetik balasan...</span>
                    </div>
                </div>
            </div>

            {{-- CHAT INPUT COMPOSER OR SESSION ENDED BANNER --}}
            <template x-if="isSessionEnded">
                <div class="p-3.5 bg-amber-50/90 dark:bg-[#201D1A] border-t border-amber-200/80 dark:border-amber-900/50 flex flex-col items-center gap-2 text-center shrink-0">
                    <div class="flex items-center gap-1.5 text-[12px] font-medium text-amber-800 dark:text-amber-300">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span x-text="sessionEndedReason || 'Sesi chat otomatis diakhiri karena tidak ada balasan selama 5 menit.'"></span>
                    </div>
                    <button type="button" @click="startNewChatSession()" 
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-semibold text-[12.5px] shadow-sm flex items-center gap-1.5 transition-all hover:scale-105 active:scale-95">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>Mulai Sesi Chat Baru</span>
                    </button>
                </div>
            </template>

            <template x-if="!isSessionEnded">
                <div class="p-3 bg-white dark:bg-[#1E1E24] border-t border-gray-200 dark:border-white/10 shrink-0">
                    <form @submit.prevent="sendFollowupMessage()" class="flex items-center gap-2">
                        <input type="text" x-model="newMessage" 
                               placeholder="Ketik balasan pesan Anda..." 
                               :disabled="isSending"
                               class="flex-1 h-10 px-3.5 rounded-xl bg-gray-50 dark:bg-[#27272A] border border-gray-200 dark:border-white/10 text-[13px] text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                        
                        <button type="submit" 
                                :disabled="!newMessage || !newMessage.trim() || isSending"
                                class="w-10 h-10 rounded-xl bg-violet-600 hover:bg-violet-700 text-white flex items-center justify-center disabled:opacity-40 transition-colors shadow-sm shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </form>
                </div>
            </template>
        </div>

    </div>

</div>

{{-- STYLES FOR SAFE ELEVATION & POSITIONS --}}
<style>
    .floating-live-chat-fab {
        bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px)) !important;
    }
    .floating-live-chat-window {
        bottom: calc(10.5rem + env(safe-area-inset-bottom, 0px)) !important;
    }
    @media (min-width: 1024px) {
        .floating-live-chat-fab {
            bottom: 1.5rem !important;
        }
        .floating-live-chat-window {
            bottom: 6.25rem !important;
        }
        /* Elevate WhatsApp FAB when Live Chat is active on desktop */
        .floating-wa-fab {
            bottom: 6rem !important;
        }
    }
    @media (max-width: 1023px) {
        /* On mobile, elevate WhatsApp FAB above the Live Chat button */
        .floating-wa-fab {
            bottom: calc(10.25rem + env(safe-area-inset-bottom, 0px)) !important;
        }
    }
</style>

<script>
function coocaLiveChatWidget() {
    return {
        isOpen: false,
        showGreeting: false,
        isSubmitting: false,
        isSending: false,
        isWaitingReply: false,
        isReverbConnected: false,
        isSessionEnded: false,
        sessionEndedReason: '',
        errorMessage: '',
        sessionToken: localStorage.getItem('cooca_livechat_session') || '',
        unreadCount: 0,
        newMessage: '',
        formData: {
            name: '',
            phone: '',
            email: '',
            message: '',
        },
        visitorData: {
            name: localStorage.getItem('cooca_livechat_name') || '',
            phone: localStorage.getItem('cooca_livechat_phone') || '',
            email: localStorage.getItem('cooca_livechat_email') || '',
        },
        messages: [],
        pusherInstance: null,
        reverbChannel: null,
        pollInterval: null,
        timeoutCheckInterval: null,

        loadPusher(callback) {
            if (typeof window.Pusher !== 'undefined') {
                if (callback) callback();
                return;
            }
            if (document.getElementById('cooca-pusher-sdk')) {
                const s = document.getElementById('cooca-pusher-sdk');
                s.addEventListener('load', () => { if (callback) callback(); });
                return;
            }
            const s = document.createElement('script');
            s.id = 'cooca-pusher-sdk';
            s.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
            s.async = true;
            s.onload = () => { if (callback) callback(); };
            document.head.appendChild(s);
        },

        dismissGreeting() {
            this.showGreeting = false;
            // 7 days dismissal: 7 * 24 * 60 * 60 * 1000 = 604800000 ms
            localStorage.setItem('cooca_greeting_dismissed_until', String(Date.now() + 604800000));
        },

        init() {
            // Restore visitor info into formData if present
            if (this.visitorData.name) this.formData.name = this.visitorData.name;
            if (this.visitorData.phone) this.formData.phone = this.visitorData.phone;
            if (this.visitorData.email) this.formData.email = this.visitorData.email;

            // Delayed greeting: 20s or 50% scroll, suppressed if dismissed in last 7 days or on mobile
            const dismissedUntil = parseInt(localStorage.getItem('cooca_greeting_dismissed_until') || '0', 10);
            const isSuppressed = Date.now() < dismissedUntil;

            if (!isSuppressed && !this.sessionToken) {
                let triggered = false;
                const activateGreeting = () => {
                    if (triggered || this.isOpen || this.sessionToken) return;
                    triggered = true;
                    this.showGreeting = true;
                    window.removeEventListener('scroll', handleScroll);
                };

                const timer = setTimeout(activateGreeting, 20000);

                const handleScroll = () => {
                    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
                    if (scrollable > 0 && (window.scrollY / scrollable) >= 0.5) {
                        clearTimeout(timer);
                        activateGreeting();
                    }
                };
                window.addEventListener('scroll', handleScroll, { passive: true });
            }

            // If session already exists, restore chat history immediately
            if (this.sessionToken) {
                this.loadChatHistory();
                this.connectReverb();
            }

            // Fallback polling every 3.5 seconds for resilience
            this.pollInterval = setInterval(() => {
                if (this.sessionToken && !this.isSessionEnded) {
                    this.pollMessages();
                }
            }, 3500);

            // Client-side 5-minute inactivity check every 5 seconds
            this.timeoutCheckInterval = setInterval(() => {
                if (this.sessionToken && !this.isSessionEnded) {
                    this.checkInactivityTimeout();
                }
            }, 5000);
        },

        checkInactivityTimeout() {
            if (!this.sessionToken || this.isSessionEnded || this.messages.length === 0) return;

            const lastMsg = this.messages[this.messages.length - 1];
            // Only timeout if the last message was sent by admin / support and waiting for visitor reply
            if (lastMsg && (lastMsg.is_cooca || lastMsg.is_from_page)) {
                const msgTime = lastMsg.created_at ? new Date(lastMsg.created_at).getTime() : 0;
                if (msgTime > 0) {
                    const elapsedSeconds = Math.floor((Date.now() - msgTime) / 1000);
                    if (elapsedSeconds >= 300) { // 5 minutes without visitor reply
                        this.handleSessionEnded('Sesi chat otomatis diakhiri karena tidak ada balasan dari Anda selama 5 menit.');
                    }
                }
            }
        },

        handleSessionEnded(reason) {
            this.isSessionEnded = true;
            this.sessionEndedReason = reason || 'Sesi chat otomatis diakhiri karena tidak ada balasan dalam 5 menit.';
            this.isWaitingReply = false;

            // Ensure polite closing message bubble appears before session ended banner
            const hasClosingMsg = this.messages.some(m => (m.is_cooca || m.is_from_page) && m.message && (
                m.message.toLowerCase().includes('izin mengakhiri sesi') || 
                m.message.toLowerCase().includes('mengakhiri sesi')
            ));

            if (!hasClosingMsg && this.messages.length > 0) {
                const name = this.visitorData?.name ? (' ' + this.visitorData.name.trim()) : '';
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                this.messages.push({
                    id: 'closing_' + Date.now(),
                    message: `Baik Kak${name}, jika sudah tidak ada hal lain yang ingin ditanyakan, saya izin mengakhiri sesi live chat ini terlebih dahulu ya. Terima kasih banyak telah berkonsultasi dengan COOCA. Semoga usaha Kakak semakin sukses dan lancar! Jika sewaktu-waktu membutuhkan bantuan seputar operasional atau fitur COOCA, silakan mulai sesi chat baru kapan saja. 🙏😊`,
                    from_name: 'Cooca Support',
                    is_cooca: true,
                    is_from_page: true,
                    time: timeStr,
                    time_relative: 'Baru saja',
                    date: 'Hari ini',
                    created_at: now.toISOString(),
                });
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }

            // Clear active session in localStorage so user can easily start a fresh session
            localStorage.removeItem('cooca_livechat_session');
            this.sessionToken = '';
            if (this.pusherInstance) {
                try { this.pusherInstance.disconnect(); } catch (_) {}
            }
        },

        startNewChatSession() {
            this.isSessionEnded = false;
            this.sessionEndedReason = '';
            this.sessionToken = '';
            this.messages = [];
            this.newMessage = '';
            this.formData.message = '';
            localStorage.removeItem('cooca_livechat_session');
            if (this.visitorData.name) this.formData.name = this.visitorData.name;
            if (this.visitorData.phone) this.formData.phone = this.visitorData.phone;
            if (this.visitorData.email) this.formData.email = this.visitorData.email;
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.unreadCount = 0;
                this.showGreeting = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },

        async startChatSession() {
            this.errorMessage = '';
            this.isSubmitting = true;

            try {
                const res = await fetch('/live-chat/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.sessionToken = data.session_token;
                    this.visitorData = data.visitor;
                    this.messages = [data.message];
                    this.isSessionEnded = false;
                    this.sessionEndedReason = '';
                    this.isWaitingReply = true;

                    // Store in localStorage
                    localStorage.setItem('cooca_livechat_session', this.sessionToken);
                    localStorage.setItem('cooca_livechat_name', this.visitorData.name);
                    localStorage.setItem('cooca_livechat_phone', this.visitorData.phone);
                    localStorage.setItem('cooca_livechat_email', this.visitorData.email);

                    // Connect Reverb WebSocket for real-time messages
                    this.connectReverb();

                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });

                    // If immediate reply arrived with the start payload
                    if (data.reply) {
                        setTimeout(() => {
                            if (!this.messages.some(m => m.id === data.reply.id)) {
                                this.messages.push(data.reply);
                                this.playChime();
                            }
                            this.isWaitingReply = false;
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        }, 700);
                    }
                } else {
                    this.errorMessage = data.message || 'Gagal memulai live chat. Silakan periksa kembali input Anda.';
                }
            } catch (err) {
                this.errorMessage = 'Terjadi gangguan jaringan: ' + err.message;
            } finally {
                this.isSubmitting = false;
            }
        },

        async sendFollowupMessage() {
            const text = (this.newMessage || '').trim();
            if (!text || this.isSending) return;

            if (this.isSessionEnded || !this.sessionToken) {
                this.handleSessionEnded('Sesi chat telah berakhir. Silakan mulai sesi baru.');
                return;
            }

            const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            const now = new Date();
            const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

            // 1. OPTIMISTIC UI: Immediately render in dialog without waiting for AI/network
            const optimisticMsg = {
                id: tempId,
                temp_id: tempId,
                message: text,
                from_name: this.visitorData?.name || 'Anda',
                is_cooca: false,
                is_from_page: false,
                time: timeStr,
                time_relative: 'Baru saja',
                date: 'Hari ini',
                created_at: now.toISOString(),
                is_optimistic: true,
            };

            this.messages.push(optimisticMsg);
            this.newMessage = '';
            this.isWaitingReply = true; // Show animated typing dots immediately!
            this.isSending = true;

            this.$nextTick(() => {
                this.scrollToBottom();
            });

            try {
                const res = await fetch('/live-chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        session_token: this.sessionToken,
                        message: text
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    // Replace temporary optimistic message with persistent server record
                    const idx = this.messages.findIndex(m => m.id === tempId || m.temp_id === tempId);
                    if (idx !== -1) {
                        this.messages.splice(idx, 1, data.message);
                    } else if (!this.messages.some(m => m.id === data.message.id)) {
                        this.messages.push(data.message);
                    }

                    // If server already returned the AI response in payload
                    if (data.reply) {
                        setTimeout(() => {
                            if (!this.messages.some(m => m.id === data.reply.id)) {
                                this.messages.push(data.reply);
                                this.playChime();
                            }
                            this.isWaitingReply = false;
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        }, 700);
                    }

                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                } else if (data.session_ended || res.status === 410 || res.status === 404) {
                    this.messages = this.messages.filter(m => m.id !== tempId);
                    this.isWaitingReply = false;
                    this.handleSessionEnded(data.message);
                } else {
                    this.messages = this.messages.filter(m => m.id !== tempId);
                    this.isWaitingReply = false;
                    this.newMessage = text; // restore message to composer
                    alert(data.message || 'Gagal mengirim pesan.');
                }
            } catch (err) {
                this.isWaitingReply = false;
                alert('Gangguan koneksi: ' + err.message);
            } finally {
                this.isSending = false;
            }
        },

        async loadChatHistory() {
            if (!this.sessionToken) return;

            try {
                const res = await fetch(`/live-chat/history?session_token=${encodeURIComponent(this.sessionToken)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.messages)) {
                        this.messages = data.messages;
                        if (data.visitor && data.visitor.name) {
                            this.visitorData = data.visitor;
                        }
                        if (data.session_ended) {
                            this.handleSessionEnded('Sesi chat otomatis diakhiri karena tidak ada balasan dari Anda selama 5 menit.');
                        } else {
                            this.checkInactivityTimeout();
                        }
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    } else if (data.session_ended) {
                        this.handleSessionEnded(data.message);
                    }
                }
            } catch (e) {
                console.warn('[LiveChat] History fetch notice:', e);
            }
        },

        async pollMessages() {
            if (!this.sessionToken || this.isSending || this.isSubmitting || this.isSessionEnded) return;

            const lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : '';

            try {
                const res = await fetch(`/live-chat/poll?session_token=${encodeURIComponent(this.sessionToken)}&after_id=${encodeURIComponent(lastId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.session_ended) {
                        this.handleSessionEnded('Sesi chat otomatis diakhiri karena tidak ada balasan dari Anda selama 5 menit.');
                        return;
                    }
                    if (data.success && Array.isArray(data.messages) && data.messages.length > 0) {
                        data.messages.forEach(incoming => {
                            if (!this.messages.some(m => m.id === incoming.id)) {
                                this.messages.push(incoming);
                                this.isWaitingReply = false;
                                if (!this.isOpen && (incoming.is_cooca || incoming.is_from_page)) {
                                    this.unreadCount++;
                                    this.playChime();
                                }
                            }
                        });
                        this.checkInactivityTimeout();
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                }
            } catch (e) {}
        },

        connectReverb() {
            if (!this.sessionToken) return;

            this.loadPusher(() => {
                if (typeof Pusher === 'undefined') return;

                try {
                    const reverbKey = '{{ config('broadcasting.connections.reverb.key') }}' || 'cooca_reverb_key_2026';
                    const reverbHost = '{{ config('broadcasting.connections.reverb.options.host') }}' || window.location.hostname;
                    const reverbPort = {{ (int) (config('broadcasting.connections.reverb.options.port') ?: 8080) }};
                    const reverbScheme = '{{ config('broadcasting.connections.reverb.options.scheme') ?: 'http' }}';

                    if (this.pusherInstance) {
                        try { this.pusherInstance.disconnect(); } catch (_) {}
                    }

                    this.pusherInstance = new Pusher(reverbKey, {
                        wsHost: reverbHost,
                        wsPort: reverbPort,
                        wssPort: reverbPort,
                        forceTLS: reverbScheme === 'https',
                        enabledTransports: ['ws', 'wss'],
                        cluster: 'mt1',
                    });

                    this.pusherInstance.connection.bind('connected', () => {
                        this.isReverbConnected = true;
                        console.log('[LiveChat] Connected to Laravel Reverb WebSocket.');
                    });

                    this.pusherInstance.connection.bind('disconnected', () => {
                        this.isReverbConnected = false;
                    });

                const channelName = 'livechat.' + this.sessionToken;
                this.reverbChannel = this.pusherInstance.subscribe(channelName);

                this.reverbChannel.bind('message.sent', (data) => {
                    console.log('[LiveChat] New message broadcast received:', data);
                    
                    // Prevent duplicate if already in array
                    if (!this.messages.some(m => m.id === data.id)) {
                        this.messages.push({
                            id: data.id,
                            message: data.message,
                            media_url: data.media_url,
                            media_type: data.media_type,
                            from_name: data.from_name,
                            is_cooca: data.is_cooca,
                            is_from_page: data.is_from_page,
                            time: data.time,
                            date: data.date,
                            created_at: data.created_at || new Date().toISOString(),
                        });

                        this.isWaitingReply = false;
                        if (this.typingTimer) clearTimeout(this.typingTimer);

                        if (!this.isOpen && (data.is_cooca || data.is_from_page)) {
                            this.unreadCount++;
                            this.playChime();
                        }

                        this.checkInactivityTimeout();

                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                });

                // Listen for live agent typing events
                this.reverbChannel.bind('agent.typing', (data) => {
                    this.isWaitingReply = true;
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                    if (this.typingTimer) clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(() => {
                        this.isWaitingReply = false;
                    }, 6000);
                });
            } catch (err) {
                console.warn('[LiveChat] Reverb initialization warning:', err);
            }
            });
        },

        confirmResetSession() {
            if (confirm('Apakah Anda ingin mengakhiri sesi chat ini dan memulai chat baru?')) {
                this.startNewChatSession();
                if (this.pusherInstance) {
                    try { this.pusherInstance.disconnect(); } catch (_) {}
                }
            }
        },

        scrollToBottom() {
            const el = document.getElementById('coocaLiveChatMessages');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        playChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.35);
            } catch (_) {}
        },

        formatMessage(text, isIncoming = true) {
            if (!text) return '';

            // 1. Sanitize raw HTML tags to prevent XSS injection
            let safe = String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            // 2. Parse Markdown bold **bold text**
            safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold">$1</strong>');

            // 3. Define action button badge styles
            const btnClass = isIncoming
                ? 'inline-flex items-center gap-1.5 px-3 py-1 my-1 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-semibold text-[12px] shadow-xs hover:shadow transition-all active:scale-95 no-underline align-middle mx-1 cursor-pointer'
                : 'inline-flex items-center gap-1.5 px-3 py-1 my-1 rounded-xl bg-white hover:bg-violet-50 text-violet-700 font-semibold text-[12px] shadow-xs hover:shadow transition-all active:scale-95 no-underline align-middle mx-1 cursor-pointer';

            const linkClass = isIncoming
                ? 'text-violet-600 dark:text-violet-400 font-semibold underline hover:text-violet-700 break-all inline-flex items-center gap-1'
                : 'text-white font-semibold underline hover:opacity-90 break-all inline-flex items-center gap-1';

            const externalIcon = '<svg class="w-3.5 h-3.5 opacity-90 shrink-0 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';

            // 4. Parse Markdown links [Title](URL) into styled clickable action buttons
            safe = safe.replace(/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^\s\)]+)\)/g, (match, title, url) => {
                return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="${btnClass}"><span>${title}</span> ${externalIcon}</a>`;
            });

            // 5. Parse any remaining bare URLs (https://... or http://...) that are not inside an <a> tag
            const parts = safe.split(/(<a\b[^>]*>.*?<\/a>)/g);
            for (let i = 0; i < parts.length; i++) {
                if (!parts[i].startsWith('<a')) {
                    parts[i] = parts[i].replace(/(https?:\/\/[^\s<]+)/g, `<a href="$1" target="_blank" rel="noopener noreferrer" class="${linkClass}"><span>$1</span> ${externalIcon}</a>`);
                }
            }
            safe = parts.join('');

            return safe;
        }
    }
}
</script>
