@extends('layouts.app', [
    'title' => __('marketplace.chat_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.chat_header_title'),
    'headerSubtitle' => __('marketplace.chat_page_subtitle'),
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12"
    x-data="marketplaceChatCockpit({
        conversations: {{ Js::from($conversations->items()) }},
        initialActiveId: '{{ $conversations->first()?->id }}'
    })">

    <!-- MODULE HEADER & PERSISTENT MARKETPLACE TABS -->
    <x-module-header
        module="marketplace"
        :title="__('marketplace.chat_page_title')"
        :subtitle="__('marketplace.chat_module_subtitle')">
        <x-slot:actions>
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                <span>Live Chat • Real-time</span>
            </div>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('marketplace.back_to_hub') }}</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- CHAT COCKPIT: TWO PANE LAYOUT -->
    <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.04)] grid grid-cols-1 lg:grid-cols-12 min-h-[640px] max-h-[780px]">

        <!-- LEFT PANE: THREADS LIST (4 COLS) -->
        <div class="lg:col-span-4 border-r border-black/[0.06] dark:border-white/[0.08] flex flex-col h-full bg-black/[0.01] dark:bg-white/[0.01]">
            <!-- Search & Filter Bar -->
            <div class="p-3.5 border-b border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" x-model="searchQuery" placeholder="{{ __('marketplace.chat_search_placeholder') }}"
                        class="w-full h-9 pl-9 pr-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.05] border border-black/5 dark:border-white/5 text-[12.5px] text-black dark:text-white placeholder:text-black/40 focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30">
                </div>

                <!-- Channel Filter Pills -->
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-0.5">
                    <button type="button" @click="channelFilter = 'all'"
                        :class="channelFilter === 'all' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.05] text-black/60 dark:text-white/60 hover:text-black'"
                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold transition-all shrink-0">{{ __('marketplace.all') }}</button>
                    <button type="button" @click="channelFilter = 'tiktok_shop'"
                        :class="channelFilter === 'tiktok_shop' ? 'bg-[#000000] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.05] text-black/60 dark:text-white/60 hover:text-black'"
                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold transition-all shrink-0 flex items-center gap-1">
                        <i data-lucide="video" class="w-3 h-3"></i>
                        <span>TikTok Shop</span>
                    </button>
                    <button type="button" @click="channelFilter = 'tokopedia'"
                        :class="channelFilter === 'tokopedia' ? 'bg-[#03AC0E] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.05] text-black/60 dark:text-white/60 hover:text-black'"
                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold transition-all shrink-0">Tokopedia</button>
                    <button type="button" @click="channelFilter = 'shopee'"
                        :class="channelFilter === 'shopee' ? 'bg-[#EE4D2D] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.05] text-black/60 dark:text-white/60 hover:text-black'"
                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold transition-all shrink-0">Shopee</button>
                </div>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto divide-y divide-black/[0.04] dark:divide-white/[0.05] scrollbar-thin">
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <div @click="selectConversation(conv)"
                        :class="activeConv?.id === conv.id ? 'bg-[#007AFF]/10 dark:bg-[#007AFF]/20 border-l-4 border-l-[#007AFF]' : 'hover:bg-black/[0.02] dark:hover:bg-white/[0.02] border-l-4 border-l-transparent'"
                        class="p-3.5 cursor-pointer transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-semibold text-[13px] text-black dark:text-white truncate" x-text="conv.participant_name || 'Buyer'"></span>
                                <!-- Channel Badge -->
                                <span class="px-1.5 py-0.5 rounded-[6px] text-[10px] font-bold shrink-0"
                                    :class="{
                                        'bg-black text-white': conv.channel === 'tiktok_shop',
                                        'bg-[#03AC0E]/15 text-[#03AC0E]': conv.channel === 'tokopedia',
                                        'bg-[#EE4D2D]/15 text-[#EE4D2D]': conv.channel === 'shopee'
                                    }"
                                    x-text="conv.channel === 'tiktok_shop' ? 'TikTok' : (conv.channel === 'tokopedia' ? 'Tokped' : 'Shopee')">
                                </span>
                            </div>
                            <span class="text-[10.5px] text-black/40 dark:text-white/40 shrink-0 whitespace-nowrap" x-text="formatTime(conv.last_message_at)"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[12px] text-black/60 dark:text-white/60 truncate" x-text="conv.last_message_content || '{{ __('marketplace.chat_no_messages_yet') }}'"></p>
                            <template x-if="conv.unread_count > 0">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center shrink-0"
                                    x-text="conv.unread_count"></span>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="filteredConversations.length === 0">
                    <div class="p-8 text-center text-black/40 dark:text-white/40">
                        <i data-lucide="message-square-off" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        <p class="text-[12.5px] font-medium">{{ __('marketplace.chat_no_threads') }}</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- RIGHT PANE: ACTIVE CHAT MESSAGES (8 COLS) -->
        <div class="lg:col-span-8 flex flex-col h-full bg-white dark:bg-[#1C1C1E]">
            <template x-if="activeConv">
                <div class="flex flex-col h-full">
                    <!-- Chat Header -->
                    <div class="p-3.5 px-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-[#007AFF]/15 text-[#007AFF] font-bold text-[14px] flex items-center justify-center shrink-0">
                                <span x-text="(activeConv.participant_name || 'B').charAt(0)"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-[14px] text-black dark:text-white truncate" x-text="activeConv.participant_name"></h3>
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase"
                                        :class="{
                                            'bg-black text-white': activeConv.channel === 'tiktok_shop',
                                            'bg-[#03AC0E]/15 text-[#03AC0E]': activeConv.channel === 'tokopedia',
                                            'bg-[#EE4D2D]/15 text-[#EE4D2D]': activeConv.channel === 'shopee'
                                        }"
                                        x-text="activeConv.channel === 'tiktok_shop' ? 'TikTok Shop' : activeConv.channel"></span>
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40 truncate">
                                    Conversation ID: <span class="font-mono" x-text="activeConv.external_conversation_id"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="refreshMessages()" :disabled="loadingMessages"
                                class="h-8 px-3 rounded-[8px] text-[11.5px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.05] hover:bg-black/[0.08] flex items-center gap-1.5 transition">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': loadingMessages }"></i>
                                <span>{{ __('marketplace.chat_refresh') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Stream -->
                    <div class="flex-1 p-5 overflow-y-auto space-y-3.5 scrollbar-thin bg-black/[0.01] dark:bg-white/[0.01]" id="chat-messages-container">
                        <template x-for="msg in messages" :key="msg.id">
                            <div :class="msg.sender_role === 'BUYER' ? 'flex justify-start' : 'flex justify-end'">
                                <div :class="msg.sender_role === 'BUYER' ? 'bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white rounded-2xl rounded-tl-sm' : 'bg-[#007AFF] text-white rounded-2xl rounded-tr-sm shadow-xs'"
                                    class="max-w-[78%] px-4 py-2.5 text-[13px] leading-relaxed break-words space-y-1">
                                    <template x-if="msg.media_url">
                                        <div class="mb-1 rounded-[10px] overflow-hidden max-w-[240px]">
                                            <img :src="msg.media_url" alt="Media chat" class="w-full object-cover">
                                        </div>
                                    </template>
                                    <p x-text="msg.content_text"></p>
                                    <div :class="msg.sender_role === 'BUYER' ? 'text-black/40 dark:text-white/40' : 'text-white/70'"
                                        class="text-[10px] text-right tabular-nums pt-0.5"
                                        x-text="formatTime(msg.marketplace_created_at || msg.created_at)"></div>
                                </div>
                            </div>
                        </template>

                        <template x-if="messages.length === 0 && !loadingMessages">
                            <div class="h-full flex items-center justify-center text-center text-black/40 dark:text-white/40 py-12">
                                <p class="text-[13px]">{{ __('marketplace.chat_no_history') }}</p>
                            </div>
                        </template>
                    </div>

                    <!-- Message Composer Bar -->
                    <div class="p-3.5 px-5 border-t border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-[#1C1C1E]">
                        <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            <input type="text" x-model="newMessage" placeholder="{{ __('marketplace.chat_reply_placeholder') }}"
                                :disabled="sendingMessage"
                                class="flex-1 h-11 px-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white placeholder:text-black/40 focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]/30">
                            <button type="submit" :disabled="sendingMessage || !newMessage.trim()"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-95 text-white font-bold text-[13px] shadow-sm flex items-center gap-1.5 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                <i data-lucide="send" class="w-4 h-4" x-show="!sendingMessage"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="sendingMessage"></i>
                                <span>{{ __('marketplace.chat_btn_send') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </template>

            <template x-if="!activeConv">
                <div class="h-full flex flex-col items-center justify-center text-center p-8 text-black/40 dark:text-white/40">
                    <div class="w-16 h-16 rounded-full bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mb-3">
                        <i data-lucide="messages-square" class="w-8 h-8 opacity-40"></i>
                    </div>
                    <h3 class="font-bold text-[15px] text-black dark:text-white mb-1">{{ __('marketplace.chat_select_prompt_title') }}</h3>
                    <p class="text-[12.5px] max-w-sm">{{ __('marketplace.chat_select_prompt_desc') }}</p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function marketplaceChatCockpit(config) {
    return {
        conversations: config.conversations || [],
        searchQuery: '',
        channelFilter: 'all',
        activeConv: null,
        messages: [],
        newMessage: '',
        loadingMessages: false,
        sendingMessage: false,
        pollTimer: null,

        init() {
            if (this.conversations.length > 0) {
                const initial = this.conversations.find(c => c.id === config.initialActiveId) || this.conversations[0];
                this.selectConversation(initial);
            }
            // Auto-polling for new incoming messages without page reload
            this.pollTimer = setInterval(() => {
                this.pollIncomingUpdates();
            }, 4000);
        },

        async pollIncomingUpdates() {
            try {
                // Poll conversation list to catch new threads or unread messages
                const res = await fetch('/marketplace-hub/chat' + (this.channelFilter !== 'all' ? '?channel=' + this.channelFilter : ''), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && Array.isArray(data.conversations)) {
                    for (const incoming of data.conversations) {
                        const existing = this.conversations.find(c => c.id === incoming.id);
                        if (existing) {
                            existing.last_message_content = incoming.last_message_content;
                            existing.last_message_at = incoming.last_message_at;
                            if (this.activeConv?.id !== existing.id) {
                                existing.unread_count = incoming.unread_count;
                            }
                        } else {
                            this.conversations.unshift(incoming);
                        }
                    }
                }

                // If an active conversation is open, refresh messages silently
                if (this.activeConv && !this.sendingMessage) {
                    const msgRes = await fetch(`/marketplace-hub/chat/${this.activeConv.id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const msgData = await msgRes.json();
                    if (msgData.success && msgData.conversation) {
                        const newMsgList = msgData.conversation.messages || [];
                        if (newMsgList.length !== this.messages.length) {
                            this.messages = newMsgList;
                            this.scrollToBottom();
                        }
                    }
                }
            } catch (e) {
                // silent background poll
            }
        },

        get filteredConversations() {
            return this.conversations.filter(c => {
                const matchChannel = (this.channelFilter === 'all') || (c.channel === this.channelFilter);
                const query = this.searchQuery.toLowerCase().trim();
                const matchQuery = !query ||
                    (c.participant_name && c.participant_name.toLowerCase().includes(query)) ||
                    (c.external_conversation_id && c.external_conversation_id.toLowerCase().includes(query)) ||
                    (c.last_message_content && c.last_message_content.toLowerCase().includes(query));
                return matchChannel && matchQuery;
            });
        },

        selectConversation(conv) {
            this.activeConv = conv;
            this.refreshMessages();
        },

        async refreshMessages() {
            if (!this.activeConv) return;
            this.loadingMessages = true;
            try {
                const res = await fetch(`/marketplace-hub/chat/${this.activeConv.id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.conversation) {
                    this.messages = data.conversation.messages || [];
                    this.activeConv.unread_count = 0;
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error("Gagal memuat pesan:", e);
            } finally {
                this.loadingMessages = false;
            }
        },

        async sendMessage() {
            if (!this.activeConv || !this.newMessage.trim()) return;
            this.sendingMessage = true;
            const textToSend = this.newMessage.trim();

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/marketplace-hub/chat/${this.activeConv.id}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message: textToSend, type: 'TEXT' })
                });

                const data = await res.json();
                if (data.success && data.message) {
                    this.messages.push(data.message);
                    this.activeConv.last_message_content = textToSend;
                    this.activeConv.last_message_at = new Date().toISOString();
                    this.newMessage = '';
                    this.scrollToBottom();
                } else {
                    alert(data.error || @json(__('marketplace.failed_send_message')));
                }
            } catch (e) {
                alert(@json(__('marketplace.terjadi_kesalahan_jaringan')));
            } finally {
                this.sendingMessage = false;
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages-container');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return isNaN(d) ? '' : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    };
}
</script>
@endsection
