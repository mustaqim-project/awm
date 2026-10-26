@extends('layouts.admin')
@section('title', 'Kotak Masuk (Inbox) - COOCA Admin')

@section('content')
<div class="space-y-4 max-w-[1400px] w-full min-w-0 mx-auto pb-24 lg:pb-10" x-data="adminInboxHub()" x-init="init()">

    {{-- TOP BAR TITLE & SYNC BAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 min-w-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-[14px] bg-[#1877F2] text-white flex items-center justify-center shadow-sm shadow-[#1877F2]/25 shrink-0">
                <i data-lucide="inbox" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[19px] sm:text-[22px] font-bold text-[#111827] dark:text-white tracking-tight truncate">Kotak Masuk (Inbox)</h1>
                <p class="text-[12.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate">Pusat percakapan omnichannel: Messenger, Instagram Direct, WhatsApp, dan Komentar</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="syncMessages()" :disabled="isSyncing"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-[#1F2937] dark:text-white bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 hover:bg-[#F9FAFB] dark:hover:bg-white/[0.04] transition-all flex items-center gap-1.5 shadow-xs">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#1877F2]" :class="isSyncing ? 'animate-spin' : ''"></i>
                <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Pesan'"></span>
            </button>
            <button type="button" @click="openAiSettings = true"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/40 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-all flex items-center gap-1.5 shadow-xs">
                <i data-lucide="bot" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                <span>Kelola AI Chatbot & Pedoman</span>
                <span x-show="aiConfig.enabled" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">ON</span>
                <span x-show="!aiConfig.enabled" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600 dark:bg-white/10 dark:text-slate-400">OFF</span>
            </button>
            <button type="button" @click="openMetaSettings = true"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-[#1877F2] bg-[#E7F3FF] dark:bg-[#1877F2]/20 hover:bg-[#DBEAFE] transition-all flex items-center gap-1.5">
                <i data-lucide="key" class="w-3.5 h-3.5"></i>
                <span>Kredensial Meta</span>
            </button>
            <a href="{{ route('admin.social-media.index') }}"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-medium text-[#4B5563] dark:text-[#9CA3AF] bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 hover:bg-[#F9FAFB] dark:hover:bg-white/[0.04] transition-all flex items-center gap-1.5">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                <span>Kelola Akun Platform</span>
            </a>
        </div>
    </div>

    {{-- MAIN OMNICHANNEL INBOX CARD (MATCHING USER SCREENSHOT) --}}
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 shadow-xs overflow-hidden flex flex-col min-h-[680px] lg:h-[calc(100vh-190px)]">

        {{-- 1. TOP CHANNEL TABS BAR --}}
        <div class="px-4 py-2.5 border-b border-[#E5E7EB] dark:border-white/10 overflow-x-auto scrollbar-none flex items-center gap-2 bg-white dark:bg-[#1C1C1E] shrink-0">
            <button type="button" @click="setChannel('all')"
                :class="currentChannel === 'all' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Semua pesan</span>
                <span x-show="(counts.all || 0) > 0" x-text="counts.all" class="text-[11px] px-1.5 py-0.2 rounded-full bg-black/5 dark:bg-white/10 font-bold"></span>
            </button>

            <button type="button" @click="setChannel('live_chat')"
                :class="currentChannel === 'live_chat' ? 'bg-[#7C3AED]/15 text-[#7C3AED] font-semibold dark:bg-[#7C3AED]/25 dark:text-[#A78BFA]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span class="w-2 h-2 rounded-full bg-[#10B981] animate-pulse"></span>
                <span>Live Chat Website</span>
                <span x-show="(counts.live_chat || 0) > 0" x-text="counts.live_chat" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#7C3AED]/15 text-[#7C3AED] font-bold"></span>
            </button>

            <button type="button" @click="setChannel('messenger')"
                :class="currentChannel === 'messenger' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Messenger</span>
                <span x-show="(counts.messenger || 0) > 0" x-text="counts.messenger" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#1877F2]/15 text-[#1877F2] font-bold"></span>
            </button>

            <button type="button" @click="setChannel('instagram')"
                :class="currentChannel === 'instagram' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Instagram</span>
                <span x-show="(counts.instagram || 0) > 0" x-text="counts.instagram" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#E1306C]/15 text-[#E1306C] font-bold"></span>
            </button>

            <button type="button" @click="setChannel('whatsapp')"
                :class="currentChannel === 'whatsapp' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>WhatsApp</span>
                <span x-show="(counts.whatsapp || 0) > 0" x-text="counts.whatsapp" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#25D366]/15 text-[#25D366] font-bold"></span>
            </button>

            <button type="button" @click="setChannel('facebook_comments')"
                :class="currentChannel === 'facebook_comments' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Komentar Facebook</span>
                <span x-show="(counts.facebook_comments || 0) > 0" x-text="counts.facebook_comments" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#1877F2]/15 text-[#1877F2] font-bold"></span>
            </button>

            <button type="button" @click="setChannel('instagram_comments')"
                :class="currentChannel === 'instagram_comments' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Komentar Instagram</span>
                <span x-show="(counts.instagram_comments || 0) > 0" x-text="counts.instagram_comments" class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#E1306C]/15 text-[#E1306C] font-bold"></span>
            </button>
        </div>

        {{-- 2. TWO-COLUMN SPLIT PANE --}}
        <div class="flex-1 flex flex-col md:flex-row min-h-0 overflow-hidden">

            {{-- LEFT COLUMN: SEARCH, QUICK FILTERS & THREAD LIST --}}
            <div class="w-full md:w-[380px] lg:w-[410px] shrink-0 border-r border-[#E5E7EB] dark:border-white/10 flex flex-col bg-white dark:bg-[#1C1C1E] min-h-0">

                {{-- SEARCH AND KELOLA BUTTON ROW --}}
                <div class="p-3 border-b border-[#E5E7EB] dark:border-white/10 space-y-2.5 shrink-0">
                    <div class="flex items-center gap-2">
                        {{-- Search Input with icon --}}
                        <div class="relative flex-1 min-w-0">
                            <i data-lucide="search" class="w-4 h-4 text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input type="text" x-model="searchKeyword" @input.debounce.300ms="filterThreads()"
                                placeholder="Cari"
                                class="w-full h-9 pl-9 pr-3 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-[#1877F2] transition-colors">
                            <button type="button" x-show="searchKeyword" @click="searchKeyword = ''; filterThreads()"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        {{-- Kelola Button with layers icon --}}
                        <div class="relative" x-data="{ openManage: false }">
                            <button type="button" @click="openManage = !openManage"
                                class="h-9 px-3 rounded-[8px] border border-[#D1D5DB] dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-[13px] font-medium text-[#1F2937] dark:text-white hover:bg-[#F9FAFB] dark:hover:bg-white/[0.04] flex items-center gap-1.5 transition-colors shrink-0">
                                <i data-lucide="layers" class="w-4 h-4 text-[#4B5563] dark:text-[#9CA3AF]"></i>
                                <span>Kelola</span>
                            </button>

                            {{-- Dropdown Kelola --}}
                            <div x-show="openManage" @click.outside="openManage = false" x-cloak
                                class="absolute right-0 top-10.5 w-60 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-lg py-1.5 z-30 text-[12.5px]">
                                <div class="px-3 py-1.5 text-[10.5px] font-bold uppercase tracking-wider text-[#6B7280] dark:text-[#9CA3AF]">
                                    Aksi Kotak Masuk
                                </div>
                                <button type="button" @click="syncMessages(); openManage = false"
                                    class="w-full text-left px-3 py-2 text-[#1F2937] dark:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] flex items-center gap-2">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                    <span>Sinkronkan Pesan Sekarang</span>
                                </button>
                                <button type="button" @click="markAllAsRead(); openManage = false"
                                    class="w-full text-left px-3 py-2 text-[#1F2937] dark:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] flex items-center gap-2">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Tandai Semua Sudah Dibaca</span>
                                </button>
                                <button type="button" @click="openAiSettings = true; openManage = false"
                                    class="w-full text-left px-3 py-2 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/20 flex items-center gap-2 font-medium">
                                    <i data-lucide="bot" class="w-3.5 h-3.5 text-purple-600"></i>
                                    <span>Kelola AI Chatbot & Pedoman</span>
                                </button>
                                <button type="button" @click="openMetaSettings = true; openManage = false"
                                    class="w-full text-left px-3 py-2 text-[#1877F2] dark:text-[#64D2FF] hover:bg-[#E7F3FF]/60 dark:hover:bg-[#1877F2]/10 flex items-center gap-2 font-medium">
                                    <i data-lucide="key" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                    <span>Pengaturan Kredensial Meta & Webhook</span>
                                </button>
                                <div class="my-1 border-t border-[#E5E7EB] dark:border-white/10"></div>
                                <a href="{{ route('admin.social-media.index') }}"
                                    class="block px-3 py-2 text-[#1F2937] dark:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] flex items-center gap-2">
                                    <i data-lucide="settings" class="w-3.5 h-3.5 text-[#6B7280]"></i>
                                    <span>Kelola Saluran & Akun Meta</span>
                                </a>
                                <a href="{{ route('admin.whatsapp.index') }}"
                                    class="block px-3 py-2 text-[#1F2937] dark:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] flex items-center gap-2">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-[#25D366]"></i>
                                    <span>Kelola WhatsApp Gateway</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- QUICK FILTER PILLS ROW (TAGS & STATUS WITH SOFT COLORS) --}}
                    <div class="flex items-center justify-between gap-1 overflow-x-auto scrollbar-none pt-0.5 relative" x-data="{ openFilterMore: false }">
                        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-0.5">
                            {{-- Semua --}}
                            <button type="button" @click="resetFilters()"
                                :class="(currentStatus === 'all' && currentTag === 'all') ? 'bg-[#1877F2] text-white font-semibold border-[#1877F2] shadow-2xs' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap">
                                Semua
                            </button>

                            {{-- Belum Dibaca --}}
                            <button type="button" @click="setStatus('unread')"
                                :class="currentStatus === 'unread' ? 'bg-[#1877F2] text-white font-semibold border-[#1877F2] shadow-2xs' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap flex items-center gap-1">
                                <span>Belum Dibaca</span>
                                <template x-if="counts.unread > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                        :class="currentStatus === 'unread' ? 'bg-white text-[#1877F2]' : 'bg-[#1877F2]/10 text-[#1877F2]'"
                                        x-text="counts.unread"></span>
                                </template>
                            </button>

                            {{-- Tag: Prioritas --}}
                            <button type="button" @click="setTag('prioritas')"
                                :class="currentTag === 'prioritas' ? 'bg-amber-100 text-amber-900 border-amber-300 font-semibold dark:bg-amber-900/60 dark:text-amber-200' : 'bg-white dark:bg-[#2C2C2E] text-amber-800 dark:text-amber-300 border-amber-200/70 hover:bg-amber-50/50'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap flex items-center gap-1">
                                <span>⭐ Prioritas</span>
                                <template x-if="counts.tag_prioritas > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200" x-text="counts.tag_prioritas"></span>
                                </template>
                            </button>

                            {{-- Tag: Client --}}
                            <button type="button" @click="setTag('client')"
                                :class="currentTag === 'client' ? 'bg-blue-100 text-blue-900 border-blue-300 font-semibold dark:bg-blue-900/60 dark:text-blue-200' : 'bg-white dark:bg-[#2C2C2E] text-blue-800 dark:text-blue-300 border-blue-200/70 hover:bg-blue-50/50'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap flex items-center gap-1">
                                <span>👤 Client</span>
                                <template x-if="counts.tag_client > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200" x-text="counts.tag_client"></span>
                                </template>
                            </button>

                            {{-- Tag: Prospek --}}
                            <button type="button" @click="setTag('prospek')"
                                :class="currentTag === 'prospek' ? 'bg-emerald-100 text-emerald-900 border-emerald-300 font-semibold dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-white dark:bg-[#2C2C2E] text-emerald-800 dark:text-emerald-300 border-emerald-200/70 hover:bg-emerald-50/50'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap flex items-center gap-1">
                                <span>🎯 Prospek</span>
                                <template x-if="counts.tag_prospek > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200" x-text="counts.tag_prospek"></span>
                                </template>
                            </button>

                            {{-- Tag: Tindak Lanjut --}}
                            <button type="button" @click="setTag('tindak_lanjut')"
                                :class="currentTag === 'tindak_lanjut' ? 'bg-indigo-100 text-indigo-900 border-indigo-300 font-semibold dark:bg-indigo-900/60 dark:text-indigo-200' : 'bg-white dark:bg-[#2C2C2E] text-indigo-800 dark:text-indigo-300 border-indigo-200/70 hover:bg-indigo-50/50'"
                                class="px-2.5 py-1 rounded-[8px] text-[11.5px] border transition-colors whitespace-nowrap flex items-center gap-1">
                                <span>⏳ Tindak Lanjut</span>
                                <template x-if="counts.tag_tindak_lanjut > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200" x-text="counts.tag_tindak_lanjut"></span>
                                </template>
                            </button>
                        </div>

                        {{-- Filter Lanjutan Dropdown Trigger --}}
                        <div class="relative shrink-0">
                            <button type="button" @click="openFilterMore = !openFilterMore"
                                title="Filter Tambahan (Komplain, VIP, Balasan Iklan)"
                                class="w-7 h-7 rounded-[8px] text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-[#F3F4F6] dark:hover:bg-white/5 border border-transparent hover:border-[#E5E7EB] flex items-center justify-center transition-colors">
                                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                            </button>

                            <div x-show="openFilterMore" @click.outside="openFilterMore = false" x-cloak
                                class="absolute right-0 top-8.5 w-52 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-lg p-1.5 z-30 text-[12px] space-y-1">
                                <div class="px-2 py-1 text-[11px] font-bold text-[#6B7280] uppercase tracking-wider">Filter Tag & Status</div>
                                <button type="button" @click="setTag('komplain'); openFilterMore = false"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-rose-50 text-rose-800 flex items-center justify-between">
                                    <span class="flex items-center gap-2"><span>🚨</span> <span>Komplain</span></span>
                                    <span class="text-[10.5px] px-1.5 py-0.2 rounded-full bg-rose-100 font-bold" x-text="counts.tag_komplain || 0"></span>
                                </button>
                                <button type="button" @click="setTag('vip'); openFilterMore = false"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-purple-50 text-purple-800 flex items-center justify-between">
                                    <span class="flex items-center gap-2"><span>💎</span> <span>VIP</span></span>
                                    <span class="text-[10.5px] px-1.5 py-0.2 rounded-full bg-purple-100 font-bold" x-text="counts.tag_vip || 0"></span>
                                </button>
                                <button type="button" @click="setStatus('ad_replies'); openFilterMore = false"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-sky-50 text-sky-800 flex items-center justify-between">
                                    <span class="flex items-center gap-2"><span>📢</span> <span>Balasan Iklan</span></span>
                                    <span class="text-[10.5px] px-1.5 py-0.2 rounded-full bg-sky-100 font-bold" x-text="counts.ad_replies || 0"></span>
                                </button>
                                <div class="border-t border-[#E5E7EB] dark:border-white/10 pt-1 mt-1">
                                    <button type="button" @click="resetFilters(); openFilterMore = false"
                                        class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-gray-100 text-gray-700 font-medium">
                                        Bersihkan Semua Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- THREAD LIST CONTAINER --}}
                <div class="flex-1 overflow-y-auto min-h-0 relative">

                    {{-- EMPTY STATE: EXACT MATCHING SCREENSHOT --}}
                    <div x-show="displayedThreads.length === 0" class="h-full flex flex-col items-center justify-center p-6 text-center select-none">
                        {{-- 3D CHAT BUBBLES VECTOR ILLUSTRATION (MATCHING USER SCREENSHOT) --}}
                        <div class="w-48 h-36 relative flex items-center justify-center">
                            <svg viewBox="0 0 200 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
                                {{-- Bubble 1 (Top Left) --}}
                                <g filter="url(#filter0_d)">
                                    <circle cx="56" cy="40" r="8" fill="#E2E8F0"/>
                                    <rect x="68" y="32" width="46" height="16" rx="8" fill="#E2E8F0"/>
                                </g>
                                {{-- Bubble 2 (Top Right) --}}
                                <g filter="url(#filter1_d)">
                                    <circle cx="144" cy="58" r="8" fill="#94A3B8"/>
                                    <rect x="88" y="50" width="48" height="16" rx="8" fill="#CBD5E1"/>
                                </g>
                                {{-- Bubble 3 (Mid Left) --}}
                                <g filter="url(#filter2_d)">
                                    <circle cx="56" cy="76" r="8" fill="#CBD5E1"/>
                                    <rect x="68" y="68" width="58" height="16" rx="8" fill="#E2E8F0"/>
                                </g>
                                {{-- Main Chat Card with text lines --}}
                                <g filter="url(#filter3_d)">
                                    <rect x="64" y="94" width="76" height="34" rx="8" fill="#94A3B8"/>
                                    <rect x="74" y="103" width="56" height="5" rx="2.5" fill="#CBD5E1"/>
                                    <rect x="74" y="113" width="38" height="5" rx="2.5" fill="#CBD5E1"/>
                                </g>
                                <defs>
                                    <filter id="filter0_d" x="46" y="24" width="72" height="36" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feDropShadow dx="0" dy="2" stdDeviation="1.5" flood-opacity="0.06"/>
                                    </filter>
                                    <filter id="filter1_d" x="84" y="44" width="72" height="36" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feDropShadow dx="0" dy="2" stdDeviation="1.5" flood-opacity="0.06"/>
                                    </filter>
                                    <filter id="filter2_d" x="46" y="62" width="84" height="36" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feDropShadow dx="0" dy="2" stdDeviation="1.5" flood-opacity="0.06"/>
                                    </filter>
                                    <filter id="filter3_d" x="58" y="90" width="88" height="46" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feDropShadow dx="0" dy="3" stdDeviation="2" flood-opacity="0.1"/>
                                    </filter>
                                </defs>
                            </svg>
                        </div>

                        {{-- Headings from screenshot --}}
                        <h3 class="text-[14.5px] font-bold text-[#111827] dark:text-white mt-2">Tidak ada pesan</h3>
                        <p class="text-[12.5px] text-[#6B7280] dark:text-[#9CA3AF] max-w-[240px] mt-1 leading-relaxed">
                            Pesan yang dikirim ke Halaman Anda akan ditampilkan di sini.
                        </p>

                        <div class="mt-4 flex items-center gap-2">
                            <button type="button" @click="resetFilters()"
                                class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium text-[#1877F2] bg-[#E7F3FF] hover:bg-[#DBEAFE] transition-colors">
                                Reset Filter
                            </button>
                            <button type="button" @click="syncMessages()"
                                class="px-3 py-1.5 rounded-[8px] text-[12px] font-medium text-[#374151] dark:text-white border border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6] transition-colors flex items-center gap-1">
                                <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                                <span>Cek Pesan Baru</span>
                            </button>
                        </div>
                    </div>

                    {{-- POPULATED THREADS LIST --}}
                    <div x-show="displayedThreads.length > 0" class="divide-y divide-[#E5E7EB] dark:divide-white/10">
                        <template x-for="t in displayedThreads" :key="t.id">
                            <div @click="selectThread(t)"
                                :class="selectedThread && selectedThread.id === t.id ? (isCommentChannel(t.channel) ? 'bg-[#E7F3FF]/70 dark:bg-[#1877F2]/15 border-r-4 border-[#D946EF]' : 'bg-[#E7F3FF]/70 dark:bg-[#1877F2]/15 border-l-4 border-[#1877F2]') : 'hover:bg-[#F9FAFB] dark:hover:bg-white/[0.03]'"
                                class="p-3.5 cursor-pointer transition-colors flex items-start gap-3 relative">

                                {{-- A. COMMENT THREAD VIEW (MATCHING SCREENSHOT 1 & 2) --}}
                                <template x-if="isCommentChannel(t.channel)">
                                    <div class="flex items-start gap-3 w-full min-w-0">
                                        {{-- Post Thumbnail with Social Badge Overlay --}}
                                        <div class="relative shrink-0 w-11 h-11 rounded-[8px] overflow-hidden bg-slate-100 dark:bg-slate-800 border border-[#E5E7EB] dark:border-white/10 flex items-center justify-center">
                                            <template x-if="t.post?.media_url">
                                                <img :src="t.post.media_url" 
                                                     class="w-full h-full object-cover" 
                                                     alt="Post thumbnail"
                                                     onerror="this.style.display='none'">
                                            </template>
                                            <template x-if="!t.post?.media_url">
                                                <div class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                                                    <i :data-lucide="t.channel === 'instagram_comments' ? 'instagram' : 'facebook'" class="w-5 h-5 text-slate-400"></i>
                                                </div>
                                            </template>
                                            <span class="absolute bottom-0 right-0 w-4.5 h-4.5 rounded-tl-[6px] flex items-center justify-center text-white ring-1 ring-white dark:ring-[#1C1C1E]"
                                                  :class="t.channel === 'instagram_comments' ? 'bg-[#E1306C]' : 'bg-[#1877F2]'">
                                                <i :data-lucide="t.channel === 'instagram_comments' ? 'instagram' : 'facebook'" class="w-2.5 h-2.5"></i>
                                            </span>
                                        </div>

                                        {{-- Thread Summary (Post snippet & User commented) --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-[12.5px] font-semibold text-[#111827] dark:text-white truncate"
                                                      :class="t.is_unread ? 'font-bold text-[#111827] dark:text-white' : ''"
                                                      x-text="t.post_snippet || t.name"></span>
                                                <span class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF] shrink-0" x-text="t.time"></span>
                                            </div>

                                            <p class="text-[12px] text-[#4B5563] dark:text-[#9CA3AF] truncate mt-0.5">
                                                <span class="font-medium text-[#111827] dark:text-gray-200" x-text="t.name"></span> commented
                                            </p>

                                            {{-- Badges row (Status + Tags with Soft Colors) --}}
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                <template x-if="t.is_human_takeover">
                                                    <span class="text-[9.5px] px-1.5 py-0.5 rounded-[4px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40 flex items-center gap-0.5" title="Percakapan di-takeover human agent">
                                                        <i data-lucide="user-check" class="w-2.5 h-2.5"></i>
                                                        <span>Human</span>
                                                    </span>
                                                </template>
                                                <template x-if="!t.is_human_takeover">
                                                    <span class="text-[9.5px] px-1.5 py-0.5 rounded-[4px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40 flex items-center gap-0.5" title="AI Auto-Pilot aktif">
                                                        <i data-lucide="bot" class="w-2.5 h-2.5"></i>
                                                        <span>AI Auto</span>
                                                    </span>
                                                </template>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-[5px] font-medium"
                                                    :class="getStatusBadgeClass(t.status)"
                                                    x-text="formatStatusLabel(t.status)"></span>

                                                <template x-for="tg in (t.tags || [])" :key="tg">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-[5px] font-medium border"
                                                        :class="getTagBadgeClass(tg)"
                                                        x-text="formatTagLabel(tg)"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- B. DIRECT MESSAGE THREAD VIEW (MESSENGER, INSTAGRAM DM, WA) --}}
                                <template x-if="!isCommentChannel(t.channel)">
                                    <div class="flex items-start gap-3 w-full min-w-0">
                                        {{-- Avatar with Channel Badge (Soft flat surface, no gradient) --}}
                                        <div class="relative shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-[#F1F5F9] dark:bg-[#27272A] border border-[#E2E8F0] dark:border-white/10 flex items-center justify-center font-bold text-[13px] text-[#334155] dark:text-[#F1F5F9]">
                                                <span x-text="getInitials(t.name)"></span>
                                            </div>

                                            {{-- Channel Icon Badge --}}
                                            <span class="absolute -bottom-1 -right-1 w-4.5 h-4.5 rounded-full flex items-center justify-center text-white ring-2 ring-white dark:ring-[#1C1C1E]"
                                                :class="getChannelBadgeClass(t.channel)">
                                                <i :data-lucide="getChannelIcon(t.channel)" class="w-2.5 h-2.5"></i>
                                            </span>
                                        </div>

                                        {{-- Thread Summary --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-[13px] font-semibold text-[#111827] dark:text-white truncate"
                                                    :class="t.is_unread ? 'font-bold text-[#111827] dark:text-white' : ''"
                                                    x-text="t.name"></span>
                                                <span class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF] shrink-0" x-text="t.time"></span>
                                            </div>

                                            <p class="text-[12px] text-[#4B5563] dark:text-[#9CA3AF] truncate mt-0.5"
                                                :class="t.is_unread ? 'font-semibold text-[#111827] dark:text-[#E5E7EB]' : ''"
                                                x-text="t.last_message"></p>

                                            {{-- Badges row (Status + Tags with Soft Colors) --}}
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                <template x-if="t.is_human_takeover">
                                                    <span class="text-[9.5px] px-1.5 py-0.5 rounded-[4px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40 flex items-center gap-0.5" title="Percakapan di-takeover human agent">
                                                        <i data-lucide="user-check" class="w-2.5 h-2.5"></i>
                                                        <span>Human</span>
                                                    </span>
                                                </template>
                                                <template x-if="!t.is_human_takeover">
                                                    <span class="text-[9.5px] px-1.5 py-0.5 rounded-[4px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40 flex items-center gap-0.5" title="AI Auto-Pilot aktif">
                                                        <i data-lucide="bot" class="w-2.5 h-2.5"></i>
                                                        <span>AI Auto</span>
                                                    </span>
                                                </template>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-[5px] font-medium"
                                                    :class="getStatusBadgeClass(t.status)"
                                                    x-text="formatStatusLabel(t.status)"></span>

                                                <template x-for="tg in (t.tags || [])" :key="tg">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-[5px] font-medium border"
                                                        :class="getTagBadgeClass(tg)"
                                                        x-text="formatTagLabel(tg)"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Unread Dot --}}
                                <div x-show="t.is_unread" class="w-2.5 h-2.5 rounded-full bg-[#1877F2] shrink-0 mt-1.5"></div>
                            </div>
                        </template>
                    </div>

                </div>
            </div>

            {{-- RIGHT COLUMN: CONVERSATION CANVAS --}}
            <div class="flex-1 flex flex-col bg-white dark:bg-[#1C1C1E] min-h-0 overflow-hidden">

                {{-- CASE A: NO THREAD SELECTED (CLEAN CANVAS MATCHING SCREENSHOT) --}}
                <div x-show="!selectedThread" class="h-full flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-[#1C1C1E]">
                    <div class="w-16 h-16 rounded-[22px] bg-[#F3F4F6] dark:bg-white/5 flex items-center justify-center text-[#9CA3AF] mb-3">
                        <i data-lucide="messages-square" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-[15px] font-bold text-[#111827] dark:text-white">Pusat Balasan Pesan Omnichannel</h3>
                    <p class="text-[13px] text-[#6B7280] dark:text-[#9CA3AF] max-w-sm mt-1 leading-relaxed">
                        Pilih percakapan dari daftar di sebelah kiri untuk membaca pesan lengkap dan mengirim balasan resmi sebagai Cooca Official.
                    </p>
                    <div class="flex items-center gap-2 mt-4 text-[12px] text-[#6B7280]">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#1877F2]"></span> Messenger
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#E1306C]"></span> Instagram
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#25D366]"></span> WhatsApp
                        </span>
                    </div>
                </div>

                {{-- CASE B: THREAD SELECTED (DETAIL CHAT & REPLY COMPOSER) --}}
                <div x-show="selectedThread" class="h-full flex flex-col min-h-0 overflow-hidden">

                    {{-- ========================================================================= --}}
                    {{-- B1. KOMENTAR INSTAGRAM & FACEBOOK VIEW (MATCHING USER SCREENSHOT 1 & 2) --}}
                    {{-- ========================================================================= --}}
                    <template x-if="isCommentChannel(selectedThread.channel)">
                        <div class="h-full flex flex-col min-h-0 overflow-hidden">
                            {{-- POST HEADER BAR --}}
                            <div class="px-5 py-3 border-b border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 bg-white dark:bg-[#1C1C1E] shrink-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-[8px] overflow-hidden bg-slate-100 dark:bg-slate-800 border border-[#E5E7EB] dark:border-white/10 shrink-0 flex items-center justify-center">
                                        <template x-if="selectedThread.post?.media_url">
                                            <img :src="selectedThread.post.media_url" 
                                                 class="w-full h-full object-cover" 
                                                 alt="Post thumbnail"
                                                 onerror="this.style.display='none'">
                                        </template>
                                        <template x-if="!selectedThread.post?.media_url">
                                            <div class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                                                <i :data-lucide="selectedThread.channel === 'instagram_comments' ? 'instagram' : 'facebook'" class="w-5 h-5 text-slate-400"></i>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h2 class="text-[13.5px] font-bold text-[#111827] dark:text-white truncate" 
                                                x-text="selectedThread.post?.title || selectedThread.post_snippet || 'Postingan Media Sosial'"></h2>
                                        </div>
                                        <div class="text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5 flex flex-wrap items-center gap-x-2">
                                            <span x-text="(selectedThread.post?.likes || 0) + ' suka'"></span>
                                            <span>·</span>
                                            <span x-text="(selectedThread.post?.comments_count || 0) + ' komentar'"></span>
                                            <span>·</span>
                                            <span x-text="selectedThread.post?.published_at || ''"></span>
                                        </div>
                                        {{-- Active Tags in Header --}}
                                        <div x-show="(selectedThread.tags || []).length > 0" class="flex flex-wrap items-center gap-1 mt-1">
                                            <template x-for="tg in (selectedThread.tags || [])" :key="tg">
                                                <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.2 rounded-[5px] font-medium border"
                                                    :class="getTagBadgeClass(tg)">
                                                    <span x-text="formatTagLabel(tg)"></span>
                                                    <button type="button" @click.stop="removeThreadTag(selectedThread, tg)" class="hover:opacity-75" title="Hapus tag">×</button>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Header Actions --}}
                                <div class="flex items-center gap-1.5 shrink-0">
                                    {{-- Takeover Toggle Button (Comments) --}}
                                    <button type="button" @click="toggleThreadTakeover(selectedThread)"
                                        :class="selectedThread.is_human_takeover ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-700/50' : 'bg-purple-50 hover:bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-700/50'"
                                        class="h-8 px-2.5 rounded-[8px] border text-[11.5px] font-semibold flex items-center gap-1.5 transition-all shadow-xs cursor-pointer"
                                        :title="selectedThread.is_human_takeover ? 'Percakapan di-takeover human agent. Klik untuk melepas takeover dan kembalikan ke AI.' : 'AI Auto-Pilot aktif. Klik untuk takeover percakapan oleh human agent.'">
                                        <template x-if="selectedThread.is_human_takeover">
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-amber-600"></i>
                                                <span class="hidden sm:inline" x-text="selectedThread.takeover_by_name ? 'Takeover: ' + selectedThread.takeover_by_name : 'Human Takeover'"></span>
                                                <span class="sm:hidden">Human</span>
                                                <span class="text-[9.5px] bg-amber-200/80 dark:bg-amber-900/60 px-1 py-0.2 rounded font-bold">Lepas</span>
                                            </span>
                                        </template>
                                        <template x-if="!selectedThread.is_human_takeover">
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="bot" class="w-3.5 h-3.5 text-purple-600"></i>
                                                <span class="hidden sm:inline">AI Auto-Pilot</span>
                                                <span class="sm:hidden">AI</span>
                                                <span class="text-[9.5px] bg-purple-200/80 dark:bg-purple-900/60 px-1 py-0.2 rounded font-bold">Takeover</span>
                                            </span>
                                        </template>
                                    </button>

                                    <template x-if="selectedThread.post?.permalink">
                                        <a :href="selectedThread.post.permalink" target="_blank" rel="noopener noreferrer"
                                           class="h-8 px-2.5 rounded-[8px] bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-[#1877F2] dark:text-[#60A5FA] text-[12px] font-medium flex items-center gap-1.5 transition-colors"
                                           :title="selectedThread.channel === 'instagram_comments' ? 'Buka postingan di Instagram' : 'Buka postingan di Facebook'">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            <span class="hidden sm:inline" x-text="selectedThread.channel === 'instagram_comments' ? 'Buka di IG' : 'Buka di FB'"></span>
                                        </a>
                                    </template>

                                    {{-- Tag Dropdown Button --}}
                                    <div class="relative" x-data="{ openTagDrop: false, customTagVal: '' }">
                                        <button type="button" @click="openTagDrop = !openTagDrop"
                                            title="Kelola Tag & Kategori"
                                            class="h-8 px-2.5 rounded-[8px] border border-[#E5E7EB] dark:border-white/10 text-[#4B5563] dark:text-[#9CA3AF] hover:bg-gray-50 dark:hover:bg-white/5 text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                                            <i data-lucide="tag" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                            <span class="hidden sm:inline">Tag</span>
                                            <template x-if="(selectedThread.tags || []).length > 0">
                                                <span class="px-1.5 py-0.2 rounded-full text-[9.5px] font-bold bg-[#1877F2]/10 text-[#1877F2]" x-text="(selectedThread.tags || []).length"></span>
                                            </template>
                                            <i data-lucide="chevron-down" class="w-3 h-3 text-[#9CA3AF]"></i>
                                        </button>

                                        <div x-show="openTagDrop" @click.outside="openTagDrop = false" x-cloak
                                            class="absolute right-0 top-9.5 w-56 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xl p-2 z-30 text-[12px] space-y-1.5">
                                            <div class="px-1.5 py-0.5 text-[11px] font-bold text-[#6B7280] uppercase tracking-wider">Tag Percakapan</div>
                                            <div class="space-y-0.5 max-h-48 overflow-y-auto">
                                                <template x-for="defTag in availableTags" :key="defTag">
                                                    <button type="button" @click="toggleThreadTag(selectedThread, defTag)"
                                                        class="w-full text-left px-2 py-1.5 rounded-[8px] hover:bg-slate-50 dark:hover:bg-white/5 flex items-center justify-between transition-colors">
                                                        <span class="inline-flex items-center gap-2">
                                                            <span class="w-2.5 h-2.5 rounded-full" :class="{
                                                                'bg-amber-400': defTag === 'prioritas',
                                                                'bg-blue-500': defTag === 'client',
                                                                'bg-emerald-500': defTag === 'prospek',
                                                                'bg-indigo-500': defTag === 'tindak_lanjut',
                                                                'bg-rose-500': defTag === 'komplain',
                                                                'bg-purple-500': defTag === 'vip'
                                                            }"></span>
                                                            <span class="font-medium text-[#111827] dark:text-white" x-text="formatTagLabel(defTag)"></span>
                                                        </span>
                                                        <template x-if="(selectedThread.tags || []).includes(defTag)">
                                                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>

                                            <div class="pt-2 border-t border-[#E5E7EB] dark:border-white/10 flex items-center gap-1">
                                                <input type="text" x-model="customTagVal" @keydown.enter.prevent="if(customTagVal.trim()){ addCustomTag(selectedThread, customTagVal.trim()); customTagVal=''; }"
                                                    placeholder="Tag kustom..."
                                                    class="flex-1 h-7 px-2 rounded-[6px] bg-slate-100 dark:bg-black/30 border border-slate-200 dark:border-white/10 text-[11px] focus:outline-hidden focus:border-[#1877F2]">
                                                <button type="button" @click="if(customTagVal.trim()){ addCustomTag(selectedThread, customTagVal.trim()); customTagVal=''; }"
                                                    class="h-7 px-2 rounded-[6px] bg-[#1877F2] text-white text-[11px] font-bold">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" title="Bintang / Prioritas" @click="toggleThreadTag(selectedThread, 'prioritas')" class="w-8 h-8 rounded-[8px] border border-[#E5E7EB] dark:border-white/10 text-[#4B5563] dark:text-[#9CA3AF] hover:bg-gray-50 dark:hover:bg-white/5 flex items-center justify-center transition-colors">
                                        <i data-lucide="star" class="w-4 h-4" :class="(selectedThread.tags || []).includes('prioritas') || selectedThread.status === 'priority' ? 'fill-amber-400 text-amber-500' : ''"></i>
                                    </button>
                                    <button type="button" title="Tandai Belum Dibaca" @click="updateThreadStatus('unread')" class="w-8 h-8 rounded-[8px] border border-[#E5E7EB] dark:border-white/10 text-[#4B5563] dark:text-[#9CA3AF] hover:bg-gray-50 dark:hover:bg-white/5 flex items-center justify-center transition-colors">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" title="Tandai Selesai" @click="updateThreadStatus('resolved')" class="w-8 h-8 rounded-[8px] border border-[#E5E7EB] dark:border-white/10 text-[#4B5563] dark:text-[#9CA3AF] hover:bg-gray-50 dark:hover:bg-white/5 flex items-center justify-center transition-colors">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- COMMENTS STREAM BODY: SEPARATION OF VISITOR COMMENT & COOCA OFFICIAL SUB-REPLIES --}}
                            <div id="messagesContainer" class="flex-1 p-5 overflow-y-auto space-y-4 min-h-0 bg-white dark:bg-[#1C1C1E]">
                                
                                {{-- 1. KOMENTAR UTAMA (DARI PENGUNJUNG) --}}
                                <div class="rounded-[16px] border border-[#E5E7EB] dark:border-white/10 bg-[#F9FAFB]/70 dark:bg-white/[0.02] p-4 transition-all">
                                    <div class="flex items-center justify-between gap-2 mb-2.5 pb-2 border-b border-[#E5E7EB]/70 dark:border-white/5">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-slate-200/90 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                                            <i data-lucide="user" class="w-3 h-3 text-slate-500"></i>
                                            <span>Komentar Pengunjung</span>
                                        </span>
                                        <span class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]" x-text="getParentComment(selectedThread).time_exact || getParentComment(selectedThread).time"></span>
                                    </div>

                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                            {{-- Visitor Avatar (Soft flat surface, no gradient) --}}
                                            <div class="w-9 h-9 rounded-full bg-[#F1F5F9] dark:bg-[#27272A] border border-[#E2E8F0] dark:border-white/10 flex items-center justify-center text-[12px] font-bold text-slate-700 dark:text-slate-200 shrink-0 mt-0.5">
                                                <span x-text="getInitials(getParentComment(selectedThread).from_name)"></span>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-[13.5px] text-[#111827] dark:text-white" x-text="getParentComment(selectedThread).from_name"></span>
                                                </div>
                                                <p class="text-[13.5px] text-[#1F2937] dark:text-[#F3F4F6] mt-1 whitespace-pre-wrap leading-relaxed font-normal" x-text="getParentComment(selectedThread).message"></p>

                                                {{-- Action Subline --}}
                                                <div class="flex items-center gap-3 mt-2.5 text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF]">
                                                    <span x-text="getParentComment(selectedThread).time"></span>
                                                    <span>·</span>
                                                    <button type="button" @click="startReplyComment(getParentComment(selectedThread))" class="font-semibold text-[#1877F2] hover:underline flex items-center gap-1">
                                                        <i data-lucide="reply" class="w-3 h-3"></i>
                                                        <span>Balas</span>
                                                    </button>
                                                    <span>·</span>
                                                    <button type="button" @click="openPrivateDmModal(getParentComment(selectedThread))" class="font-semibold text-emerald-600 hover:underline flex items-center gap-1">
                                                        <i data-lucide="send" class="w-3 h-3"></i>
                                                        <span>Kirim Pesan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Heart / Like Icon --}}
                                        <button type="button" @click="getParentComment(selectedThread).liked = !getParentComment(selectedThread).liked" class="text-gray-400 hover:text-red-500 transition-colors p-1 shrink-0">
                                            <i data-lucide="heart" class="w-4 h-4" :class="getParentComment(selectedThread).liked ? 'fill-red-500 text-red-500' : ''"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- 2. SUB-THREAD BALASAN (REPLIES CONTAINER) --}}
                                <div class="relative ml-4 pl-4 sm:ml-6 sm:pl-6 border-l-2 border-indigo-200 dark:border-indigo-900/60 space-y-3 pt-1">
                                    {{-- Thread Connector Header --}}
                                    <div class="flex items-center gap-2 text-[11.5px] font-semibold text-[#6B7280] dark:text-[#9CA3AF] -ml-6 sm:-ml-8 pl-1">
                                        <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-950/80 border border-indigo-300 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                            <i data-lucide="corner-down-right" class="w-2.5 h-2.5"></i>
                                        </span>
                                        <span>Sub-Balasan (<span x-text="getThreadReplies(selectedThread).length"></span>)</span>
                                    </div>

                                    {{-- KONDISI JIKA BELUM ADA BALASAN SAMA SEKALI --}}
                                    <div x-show="getThreadReplies(selectedThread).length === 0" 
                                         class="p-3.5 rounded-[14px] bg-amber-50/60 dark:bg-amber-950/20 border border-dashed border-amber-300/80 dark:border-amber-900/40 text-[12px] flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                                            <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-600"></i>
                                            <span>Belum ada balasan dari Cooca Official untuk komentar ini.</span>
                                        </div>
                                        <button type="button" @click="startReplyComment(getParentComment(selectedThread))"
                                                class="px-3 py-1 rounded-[8px] bg-[#1877F2] hover:bg-[#166FE5] text-white text-[11.5px] font-semibold flex items-center gap-1 shrink-0 transition-colors">
                                            <i data-lucide="reply" class="w-3 h-3"></i>
                                            <span>Balas Sekarang</span>
                                        </button>
                                    </div>

                                    {{-- DAFTAR SUB-BALASAN --}}
                                    <template x-for="(reply, rIdx) in getThreadReplies(selectedThread)" :key="reply.id || rIdx">
                                        <div class="space-y-1">
                                            {{-- A. SUB BALASAN DARI COOCA OFFICIAL (Soft pastel solid, no gradient) --}}
                                            <template x-if="reply.is_cooca || reply.is_from_page">
                                                <div class="rounded-[14px] bg-[#F0F7FF] dark:bg-[#1877F2]/10 border border-[#BAE6FD] dark:border-[#1877F2]/30 p-3.5 sm:p-4 shadow-xs">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                                            {{-- Cooca Official Avatar with Verified Badge --}}
                                                            <div class="relative shrink-0">
                                                                <div class="w-8 h-8 rounded-full overflow-hidden bg-black ring-2 ring-[#1877F2]/30">
                                                                    <img src="{{ asset('assets/image/cooca-icon.png') }}" class="w-full h-full object-cover" alt="Cooca Official">
                                                                </div>
                                                                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-[#1877F2] text-white flex items-center justify-center ring-1 ring-white dark:ring-[#1C1C1E]">
                                                                    <i data-lucide="check" class="w-2 h-2"></i>
                                                                </span>
                                                            </div>

                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-[13px] text-[#111827] dark:text-white" x-text="reply.from_name || 'Cooca Indonesia'"></span>
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#1877F2] text-white shadow-xs">
                                                                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                                                                        <span>Balasan Resmi Cooca (Admin)</span>
                                                                    </span>
                                                                </div>
                                                                <p class="text-[13px] text-[#1F2937] dark:text-[#E5E7EB] mt-1 whitespace-pre-wrap leading-relaxed font-normal" x-text="reply.message"></p>

                                                                <div class="flex items-center gap-2 mt-2 text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                                                                    <span x-text="reply.time"></span>
                                                                    <span>·</span>
                                                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Terkirim ke Media Sosial</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Heart / Like Icon --}}
                                                        <button type="button" @click="reply.liked = !reply.liked" class="text-gray-400 hover:text-red-500 transition-colors p-1 shrink-0">
                                                            <i data-lucide="heart" class="w-4 h-4" :class="reply.liked ? 'fill-red-500 text-red-500' : ''"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- B. SUB BALASAN DARI PENGUNJUNG LAIN --}}
                                            <template x-if="!reply.is_cooca && !reply.is_from_page">
                                                <div class="rounded-[16px] bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 p-3.5 shadow-xs">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                                            {{-- Visitor Avatar --}}
                                                            <div class="w-7 h-7 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-200 shrink-0 mt-0.5">
                                                                <span x-text="getInitials(reply.from_name)"></span>
                                                            </div>

                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-[12.5px] text-[#111827] dark:text-white" x-text="reply.from_name"></span>
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-600 dark:bg-white/10 dark:text-slate-300">Balasan Pengunjung</span>
                                                                </div>
                                                                <p class="text-[12.5px] text-[#374151] dark:text-[#D1D5DB] mt-1 whitespace-pre-wrap leading-relaxed" x-text="reply.message"></p>

                                                                <div class="flex items-center gap-2.5 mt-2 text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                                                                    <span x-text="reply.time"></span>
                                                                    <span>·</span>
                                                                    <button type="button" @click="startReplyComment(reply)" class="font-semibold text-[#1877F2] hover:underline">
                                                                        Balas
                                                                    </button>
                                                                    <span>·</span>
                                                                    <button type="button" @click="openPrivateDmModal(reply)" class="font-semibold text-emerald-600 hover:underline flex items-center gap-1">
                                                                        <span>Kirim Pesan</span>
                                                                        <i data-lucide="send" class="w-2.5 h-2.5"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Heart / Like Icon --}}
                                                        <button type="button" @click="reply.liked = !reply.liked" class="text-gray-400 hover:text-red-500 transition-colors p-1 shrink-0">
                                                            <i data-lucide="heart" class="w-4 h-4" :class="reply.liked ? 'fill-red-500 text-red-500' : ''"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- BOTTOM COMMENT COMPOSER (MATCHING SCREENSHOT 1) --}}
                            <div class="p-3.5 bg-white dark:bg-[#1C1C1E] border-t border-[#E5E7EB] dark:border-white/10 shrink-0 space-y-2">
                                {{-- Active Reply Context Indicator --}}
                                <div x-show="replyTargetComment" class="px-3 py-1.5 rounded-[8px] bg-[#E7F3FF] dark:bg-[#1877F2]/20 flex items-center justify-between text-[12px] text-[#1877F2]">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <i data-lucide="corner-down-right" class="w-3.5 h-3.5"></i>
                                        <span>Membalas komentar ke <strong x-text="'@' + (replyTargetComment?.from_name || 'pengguna')"></strong></span>
                                    </div>
                                    <button type="button" @click="cancelReplyTarget()" class="text-gray-500 hover:text-gray-700 ml-2">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-black shrink-0 hidden sm:block">
                                        <img src="{{ asset('assets/image/cooca-icon.png') }}" class="w-full h-full object-cover" alt="Cooca Official">
                                    </div>
                                    <button type="button" @click="generateAiReplyForCurrent()" :disabled="isGeneratingAi"
                                        title="Bantu Susun Balasan Cerdas dengan AI sesuai Pedoman Kerja"
                                        class="h-10 px-3 rounded-[10px] bg-purple-50 dark:bg-purple-950/30 hover:bg-purple-100 dark:hover:bg-purple-900/40 border border-purple-200 dark:border-purple-800/40 text-purple-700 dark:text-purple-300 text-[12px] font-semibold flex items-center gap-1.5 transition-colors shrink-0 shadow-xs">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" :class="isGeneratingAi ? 'animate-spin' : ''"></i>
                                        <span class="hidden sm:inline" x-text="isGeneratingAi ? 'Menyusun...' : 'AI Balas'"></span>
                                    </button>
                                    <div class="flex-1 relative flex items-center">
                                        <input type="text" x-model="replyText" @keydown.enter.prevent="sendCommentReply()"
                                               :placeholder="replyTargetComment ? 'Balas ke @' + replyTargetComment.from_name + '...' : 'Tambahkan komentar...'"
                                               class="w-full h-10 px-3.5 pr-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-[#1877F2]">
                                        <button type="button" @click="replyText += ' 😊'" class="absolute right-3 text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white">
                                            <i data-lucide="smile" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <button type="button" @click="sendCommentReply()" :disabled="!replyText.trim() || isSending"
                                            class="w-10 h-10 rounded-[10px] bg-[#1877F2] hover:bg-[#166FE5] text-white flex items-center justify-center disabled:opacity-50 transition-colors shrink-0">
                                        <i data-lucide="send" class="w-4 h-4" :class="isSending ? 'animate-pulse' : ''"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ========================================================================= --}}
                    {{-- B2. DIRECT MESSAGES VIEW (MESSENGER, INSTAGRAM DM, WA)                   --}}
                    {{-- ========================================================================= --}}
                    <template x-if="!isCommentChannel(selectedThread.channel)">
                        <div class="h-full flex flex-col min-h-0 overflow-hidden">
                            {{-- DM DETAIL HEADER --}}
                            <div class="px-5 py-3.5 border-b border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 bg-white dark:bg-[#1C1C1E] shrink-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-[#F1F5F9] dark:bg-[#27272A] border border-[#E2E8F0] dark:border-white/10 flex items-center justify-center font-bold text-[14px] text-[#334155] dark:text-[#F1F5F9] shrink-0">
                                        <span x-text="getInitials(selectedThread.name)"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h2 class="text-[14.5px] font-bold text-[#111827] dark:text-white truncate" x-text="selectedThread.name"></h2>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold text-white flex items-center gap-1"
                                                :class="getChannelBadgeClass(selectedThread.channel)">
                                                <i :data-lucide="getChannelIcon(selectedThread.channel)" class="w-2.5 h-2.5"></i>
                                                <span x-text="formatChannelName(selectedThread.channel)"></span>
                                            </span>
                                        </div>
                                        <div class="text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5 flex flex-wrap items-center gap-x-2">
                                            <template x-if="selectedThread.email">
                                                <span class="inline-flex items-center gap-1 text-slate-700 dark:text-slate-300">
                                                    <i data-lucide="mail" class="w-3 h-3 text-slate-400"></i>
                                                    <span x-text="selectedThread.email"></span>
                                                </span>
                                            </template>
                                            <template x-if="selectedThread.phone">
                                                <a :href="'https://wa.me/' + selectedThread.phone.replace(/[^0-9]/g, '')" target="_blank"
                                                   class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline font-medium" rel="noopener noreferrer">
                                                    <i data-lucide="phone" class="w-3 h-3"></i>
                                                    <span x-text="selectedThread.phone"></span>
                                                    <span class="text-[9.5px] bg-emerald-100 dark:bg-emerald-950/60 px-1 py-0.2 rounded font-normal text-emerald-700 dark:text-emerald-300">Chat WA</span>
                                                </a>
                                            </template>
                                            <span>• ID: <span x-text="selectedThread.customer_id"></span></span>
                                            <span>• Aktif: <span x-text="selectedThread.date"></span></span>
                                        </div>

                                        {{-- Active Tags in Header --}}
                                        <div x-show="(selectedThread.tags || []).length > 0" class="flex flex-wrap items-center gap-1 mt-1">
                                            <template x-for="tg in (selectedThread.tags || [])" :key="tg">
                                                <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.2 rounded-[5px] font-medium border"
                                                    :class="getTagBadgeClass(tg)">
                                                    <span x-text="formatTagLabel(tg)"></span>
                                                    <button type="button" @click.stop="removeThreadTag(selectedThread, tg)" class="hover:opacity-75" title="Hapus tag">×</button>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- ACTIONS BAR --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    {{-- Takeover Toggle Button (Direct Chat) --}}
                                    <button type="button" @click="toggleThreadTakeover(selectedThread)"
                                        :class="selectedThread.is_human_takeover ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-700/50' : 'bg-purple-50 hover:bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-700/50'"
                                        class="h-8 px-2.5 rounded-[8px] border text-[11.5px] font-semibold flex items-center gap-1.5 transition-all shadow-xs cursor-pointer"
                                        :title="selectedThread.is_human_takeover ? 'Percakapan di-takeover human agent. Klik untuk melepas takeover dan kembalikan ke AI.' : 'AI Auto-Pilot aktif. Klik untuk takeover percakapan oleh human agent.'">
                                        <template x-if="selectedThread.is_human_takeover">
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-amber-600"></i>
                                                <span class="hidden sm:inline" x-text="selectedThread.takeover_by_name ? 'Takeover: ' + selectedThread.takeover_by_name : 'Human Takeover'"></span>
                                                <span class="sm:hidden">Human</span>
                                                <span class="text-[9.5px] bg-amber-200/80 dark:bg-amber-900/60 px-1 py-0.2 rounded font-bold">Lepas</span>
                                            </span>
                                        </template>
                                        <template x-if="!selectedThread.is_human_takeover">
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="bot" class="w-3.5 h-3.5 text-purple-600"></i>
                                                <span class="hidden sm:inline">AI Auto-Pilot</span>
                                                <span class="sm:hidden">AI</span>
                                                <span class="text-[9.5px] bg-purple-200/80 dark:bg-purple-900/60 px-1 py-0.2 rounded font-bold">Takeover</span>
                                            </span>
                                        </template>
                                    </button>

                                    {{-- Tag Dropdown Button --}}
                                    <div class="relative" x-data="{ openDmTagDrop: false, customDmTagVal: '' }">
                                        <button type="button" @click="openDmTagDrop = !openDmTagDrop"
                                            title="Kelola Tag & Kategori"
                                            class="h-8 px-2.5 rounded-[8px] border border-[#D1D5DB] dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-[12px] font-medium text-[#374151] dark:text-white hover:bg-[#F9FAFB] flex items-center gap-1.5 transition-colors">
                                            <i data-lucide="tag" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                            <span class="hidden sm:inline">Tag</span>
                                            <template x-if="(selectedThread.tags || []).length > 0">
                                                <span class="px-1.5 py-0.2 rounded-full text-[9.5px] font-bold bg-[#1877F2]/10 text-[#1877F2]" x-text="(selectedThread.tags || []).length"></span>
                                            </template>
                                            <i data-lucide="chevron-down" class="w-3 h-3 text-[#9CA3AF]"></i>
                                        </button>

                                        <div x-show="openDmTagDrop" @click.outside="openDmTagDrop = false" x-cloak
                                            class="absolute right-0 top-9.5 w-56 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xl p-2 z-30 text-[12px] space-y-1.5">
                                            <div class="px-1.5 py-0.5 text-[11px] font-bold text-[#6B7280] uppercase tracking-wider">Tag Percakapan</div>
                                            <div class="space-y-0.5 max-h-48 overflow-y-auto">
                                                <template x-for="defTag in availableTags" :key="defTag">
                                                    <button type="button" @click="toggleThreadTag(selectedThread, defTag)"
                                                        class="w-full text-left px-2 py-1.5 rounded-[8px] hover:bg-slate-50 dark:hover:bg-white/5 flex items-center justify-between transition-colors">
                                                        <span class="inline-flex items-center gap-2">
                                                            <span class="w-2.5 h-2.5 rounded-full" :class="{
                                                                'bg-amber-400': defTag === 'prioritas',
                                                                'bg-blue-500': defTag === 'client',
                                                                'bg-emerald-500': defTag === 'prospek',
                                                                'bg-indigo-500': defTag === 'tindak_lanjut',
                                                                'bg-rose-500': defTag === 'komplain',
                                                                'bg-purple-500': defTag === 'vip'
                                                            }"></span>
                                                            <span class="font-medium text-[#111827] dark:text-white" x-text="formatTagLabel(defTag)"></span>
                                                        </span>
                                                        <template x-if="(selectedThread.tags || []).includes(defTag)">
                                                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>

                                            <div class="pt-2 border-t border-[#E5E7EB] dark:border-white/10 flex items-center gap-1">
                                                <input type="text" x-model="customDmTagVal" @keydown.enter.prevent="if(customDmTagVal.trim()){ addCustomTag(selectedThread, customDmTagVal.trim()); customDmTagVal=''; }"
                                                    placeholder="Tag kustom..."
                                                    class="flex-1 h-7 px-2 rounded-[6px] bg-slate-100 dark:bg-black/30 border border-slate-200 dark:border-white/10 text-[11px] focus:outline-hidden focus:border-[#1877F2]">
                                                <button type="button" @click="if(customDmTagVal.trim()){ addCustomTag(selectedThread, customDmTagVal.trim()); customDmTagVal=''; }"
                                                    class="h-7 px-2 rounded-[6px] bg-[#1877F2] text-white text-[11px] font-bold">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status Switcher Dropdown --}}
                                    <div class="relative" x-data="{ openStatus: false }">
                                        <button type="button" @click="openStatus = !openStatus"
                                            class="h-8 px-2.5 rounded-[8px] border border-[#D1D5DB] dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-[12px] font-medium text-[#374151] dark:text-white hover:bg-[#F9FAFB] flex items-center gap-1.5 transition-colors">
                                            <span class="w-2 h-2 rounded-full" :class="selectedThread.is_unread ? 'bg-[#1877F2]' : 'bg-[#34C759]'"></span>
                                            <span x-text="formatStatusLabel(selectedThread.status)"></span>
                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#9CA3AF]"></i>
                                        </button>

                                        <div x-show="openStatus" @click.outside="openStatus = false" x-cloak
                                            class="absolute right-0 top-9.5 w-44 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-lg py-1 z-20 text-[12px]">
                                            <button type="button" @click="updateThreadStatus('unread'); openStatus = false"
                                                class="w-full text-left px-3 py-1.5 hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#1877F2]"></span>
                                                <span>Tandai Belum Dibaca</span>
                                            </button>
                                            <button type="button" @click="updateThreadStatus('read'); openStatus = false"
                                                class="w-full text-left px-3 py-1.5 hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                                <span>Tandai Sudah Dibaca</span>
                                            </button>
                                            <button type="button" @click="updateThreadStatus('priority'); openStatus = false"
                                                class="w-full text-left px-3 py-1.5 hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
                                                <span>Tandai Prioritas</span>
                                            </button>
                                            <button type="button" @click="updateThreadStatus('ad_replies'); openStatus = false"
                                                class="w-full text-left px-3 py-1.5 hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#AF52DE]"></span>
                                                <span>Balasan Iklan</span>
                                            </button>
                                            <button type="button" @click="updateThreadStatus('followup'); openStatus = false"
                                                class="w-full text-left px-3 py-1.5 hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                                                <span>Perlu Tindak Lanjut</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MESSAGES STREAM BODY --}}
                            <div id="messagesContainer" class="flex-1 p-5 overflow-y-auto space-y-4 min-h-0 bg-[#F9FAFB]/50 dark:bg-black/10">
                                <div class="text-center my-2">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/10 text-[#6B7280] dark:text-[#9CA3AF]"
                                        x-text="selectedThread.date || 'Hari ini'"></span>
                                </div>

                                <template x-if="selectedThread.messages">
                                    <div class="space-y-3.5">
                                        <template x-for="(msg, idx) in selectedThread.messages" :key="idx">
                                            <div>
                                                {{-- Inbound Customer Message --}}
                                                <template x-if="!msg.is_from_page">
                                                    <div class="flex items-end gap-2.5 max-w-[85%] sm:max-w-[70%]">
                                                        <div class="w-7 h-7 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-200 shrink-0 mb-1">
                                                            <span x-text="getInitials(msg.from_name)"></span>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <div class="bg-white dark:bg-[#2C2C2E] p-3.5 rounded-[16px] rounded-bl-[4px] border border-[#E5E7EB] dark:border-white/10 shadow-xs">
                                                                <span class="text-[11px] font-semibold block mb-1" 
                                                                      :class="selectedThread.channel === 'whatsapp' ? 'text-emerald-600 dark:text-emerald-400' : (['instagram', 'instagram_dm'].includes(selectedThread.channel) ? 'text-[#E1306C]' : 'text-[#1877F2]')"
                                                                      x-text="msg.from_name"></span>
                                                                
                                                                {{-- Attached Media Image --}}
                                                                <template x-if="msg.media_url">
                                                                    <div class="mb-2 rounded-[12px] overflow-hidden max-w-[280px] sm:max-w-xs cursor-pointer border border-black/10 dark:border-white/10 group relative" @click="window.open(msg.media_url, '_blank')">
                                                                        <img :src="msg.media_url" class="w-full h-auto max-h-56 object-cover group-hover:scale-102 transition-transform duration-200" alt="Lampiran Foto">
                                                                    </div>
                                                                </template>

                                                                <p x-show="msg.message && msg.message !== '[Foto]'" class="text-[13px] text-[#1F2937] dark:text-[#F3F4F6] whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
                                                            </div>
                                                            <span class="text-[10px] text-[#9CA3AF] px-1 block" x-text="msg.time"></span>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Outbound Admin/Official Reply with Channel-specific Soft Solid Bubbles (No gradients) --}}
                                                <template x-if="msg.is_from_page">
                                                    <div class="flex items-end justify-end gap-2.5 ml-auto max-w-[85%] sm:max-w-[70%]">
                                                        <div class="space-y-1 text-right">
                                                            <div class="p-3.5 rounded-[16px] rounded-br-[4px] shadow-xs text-left text-white"
                                                                 :class="selectedThread.channel === 'whatsapp' ? 'bg-[#008069]' : (['instagram', 'instagram_dm'].includes(selectedThread.channel) ? 'bg-[#E1306C]' : 'bg-[#1877F2]')">
                                                                
                                                                {{-- Official Header with Channel Badge --}}
                                                                <div class="flex items-center gap-1.5 mb-1.5 text-[11px] font-semibold text-white/90">
                                                                    <i :data-lucide="selectedThread.channel === 'whatsapp' ? 'message-circle' : (['instagram', 'instagram_dm'].includes(selectedThread.channel) ? 'instagram' : 'badge-check')" class="w-3.5 h-3.5 text-white"></i>
                                                                    <span x-text="selectedThread.channel === 'whatsapp' ? 'Cooca WhatsApp Official' : (['instagram', 'instagram_dm'].includes(selectedThread.channel) ? 'Cooca Instagram Official' : 'Cooca Official')"></span>
                                                                </div>

                                                                {{-- Attached Media Image in Reply --}}
                                                                <template x-if="msg.media_url">
                                                                    <div class="mb-2 rounded-[12px] overflow-hidden max-w-[280px] sm:max-w-xs cursor-pointer border border-white/20 group relative" @click="window.open(msg.media_url, '_blank')">
                                                                        <img :src="msg.media_url" class="w-full h-auto max-h-56 object-cover group-hover:scale-102 transition-transform duration-200" alt="Lampiran Foto">
                                                                    </div>
                                                                </template>

                                                                <p x-show="msg.message && msg.message !== '[Foto]'" class="text-[13px] text-white whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
                                                            </div>
                                                            <span class="text-[10px] text-[#9CA3AF] px-1 block text-right" x-text="msg.time"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- DM REPLY CONSOLE (BOTTOM COMPOSER) WITH IMAGE ATTACHMENT --}}
                            <div class="p-3.5 sm:p-4 bg-white dark:bg-[#1C1C1E] border-t border-[#E5E7EB] dark:border-white/10 shrink-0 space-y-2.5">
                                {{-- Attached Image Preview Chip --}}
                                <div x-show="attachedImageUrl" class="p-2.5 rounded-[12px] bg-blue-50 dark:bg-[#1877F2]/10 border border-blue-200 dark:border-[#1877F2]/20 flex items-center justify-between gap-3 animate-in fade-in">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-10 h-10 rounded-[8px] overflow-hidden bg-white border border-blue-300 dark:border-white/10 shrink-0">
                                            <img :src="attachedImageUrl" class="w-full h-full object-cover" alt="Lampiran">
                                        </div>
                                        <div class="min-w-0 text-[12px]">
                                            <span class="font-bold text-blue-900 dark:text-blue-200 block truncate">Foto/Gambar Terlampir</span>
                                            <span class="text-[11px] text-blue-700 dark:text-blue-300 truncate block" x-text="attachedImageFilename || 'Siap dikirim ke pelanggan'"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeAttachedImage()" class="p-1 rounded-full text-blue-600 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                {{-- Quick Responses Pills & AI Assistant Trigger --}}
                                <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1">
                                    <button type="button" @click="generateAiReplyForCurrent()" :disabled="isGeneratingAi"
                                        title="Bantu Susun Balasan Cerdas dengan AI berdasarkan percakapan pelanggan dan Pedoman Kerja"
                                        class="px-3 py-1 rounded-full text-[11.5px] font-semibold bg-purple-100 dark:bg-purple-950/60 hover:bg-purple-200 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40 flex items-center gap-1.5 transition-all whitespace-nowrap shadow-xs shrink-0">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" :class="isGeneratingAi ? 'animate-spin' : ''"></i>
                                        <span x-text="isGeneratingAi ? 'AI sedang berpikir...' : 'Bantu Jawab dengan AI'"></span>
                                    </button>
                                    <span class="text-[11px] font-semibold text-[#9CA3AF] shrink-0 mx-1">|</span>
                                    <span class="text-[11px] font-semibold text-[#9CA3AF] shrink-0 mr-1">Template:</span>
                                    <button type="button" @click="insertTemplate('Halo Kak! Terima kasih telah menghubungi kami. Ada yang bisa kami bantu? 😊')"
                                        class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                                        Sapaan Ramah
                                    </button>
                                    <button type="button" @click="insertTemplate('Halo Kak, mohon informasikan nomor handphone / email akun Cooca Anda agar dapat kami cek segera ya.')"
                                        class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                                        Minta Info Akun
                                    </button>
                                    <button type="button" @click="insertTemplate('Terima kasih atas masukannya Kak! Masukan ini telah kami teruskan ke tim teknis Cooca.')"
                                        class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                                        Respons Masukan
                                    </button>
                                </div>

                                {{-- Textarea & Action Bar with Image Attachment --}}
                                <div class="flex items-end gap-2">
                                    {{-- File Upload Trigger --}}
                                    <input type="file" x-ref="mediaFileInput" @change="handleFileUpload($event)" accept="image/*" class="hidden">
                                    <button type="button" @click="$refs.mediaFileInput.click()" :disabled="isUploadingMedia" title="Lampirkan Gambar (Maks 5MB)"
                                            class="h-[46px] w-[46px] rounded-[12px] border border-[#D1D5DB] dark:border-white/15 bg-[#F9FAFB] dark:bg-[#2C2C2E] hover:bg-[#F3F4F6] dark:hover:bg-white/5 text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#1877F2] flex items-center justify-center transition-colors shrink-0">
                                        <i data-lucide="image" class="w-5 h-5" :class="isUploadingMedia ? 'animate-spin text-[#1877F2]' : ''"></i>
                                    </button>

                                    <div class="flex-1 min-w-0 relative">
                                        <textarea x-model="replyText" @keydown.enter.prevent="if(!$event.shiftKey) sendReply()"
                                            rows="2"
                                            :placeholder="attachedImageUrl ? 'Tulis pesan/keterangan gambar (opsional)... (Enter untuk kirim)' : 'Tulis balasan resmi sebagai Cooca Official... (Tekan Enter untuk kirim, Shift+Enter untuk baris baru)'"
                                            class="w-full p-2.5 sm:p-3 rounded-[12px] bg-[#F9FAFB] dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-[#1877F2] resize-none transition-colors"></textarea>
                                    </div>

                                    <button type="button" @click="sendReply()" :disabled="(!replyText.trim() && !attachedImageUrl) || isSending || isUploadingMedia"
                                        class="h-[46px] px-4 sm:px-5 rounded-[12px] bg-[#1877F2] hover:bg-[#166FE5] disabled:opacity-50 disabled:cursor-not-allowed text-white text-[13px] font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all shrink-0">
                                        <i data-lucide="send" class="w-4 h-4" :class="isSending ? 'animate-pulse' : ''"></i>
                                        <span class="hidden sm:inline" x-text="isSending ? 'Mengirim...' : 'Kirim'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

            </div>

            {{-- 3. RIGHT COLUMN: POST INSPECTOR (FOR COMMENTS) & CONTACT PROFILE (FOR DIRECT CHAT) --}}
            {{-- 3A. POST INSPECTOR FOR COMMENT THREADS --}}
            <div x-show="selectedThread && isCommentChannel(selectedThread.channel)" x-cloak
                 class="hidden lg:flex w-[320px] xl:w-[350px] shrink-0 border-l border-[#E5E7EB] dark:border-white/10 bg-white dark:bg-[#1C1C1E] flex-col h-full overflow-y-auto">
                
                {{-- Post Media Preview Banner (Soft flat surface, no gradient) --}}
                <div class="p-4 border-b border-[#E5E7EB] dark:border-white/10 bg-slate-50/50 dark:bg-black/10">
                    <div class="rounded-[12px] overflow-hidden border border-[#E5E7EB] dark:border-white/10 shadow-xs aspect-video bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <template x-if="selectedThread?.post?.media_url">
                            <img :src="selectedThread?.post?.media_url" 
                                 class="w-full h-full object-cover" 
                                 alt="Post Media Banner"
                                 onerror="this.style.display='none'">
                        </template>
                        <template x-if="!selectedThread?.post?.media_url">
                            <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-slate-100 dark:bg-slate-800 text-[#6B7280]">
                                <i :data-lucide="selectedThread?.channel === 'instagram_comments' ? 'instagram' : 'facebook'" class="w-8 h-8 opacity-60 mb-1"></i>
                                <span class="text-[11px] font-medium">Postingan Media Sosial</span>
                            </div>
                        </template>
                    </div>

                    {{-- Link to Open Post on Instagram / Facebook --}}
                    <template x-if="selectedThread?.post?.permalink">
                        <a :href="selectedThread.post.permalink" target="_blank" rel="noopener noreferrer"
                           class="mt-3 w-full py-2 px-3 rounded-[10px] bg-[#1877F2]/10 hover:bg-[#1877F2]/20 dark:bg-[#1877F2]/20 dark:hover:bg-[#1877F2]/30 text-[#1877F2] dark:text-[#60A5FA] text-[12px] font-semibold flex items-center justify-center gap-1.5 transition-colors">
                            <i :data-lucide="selectedThread?.channel === 'instagram_comments' ? 'instagram' : 'facebook'" class="w-4 h-4"></i>
                            <span x-text="selectedThread?.channel === 'instagram_comments' ? 'Buka di Instagram' : 'Buka di Facebook'"></span>
                            <i data-lucide="external-link" class="w-3.5 h-3.5 ml-auto"></i>
                        </a>
                    </template>
                </div>

                {{-- Post Details, Tags & Full Caption --}}
                <div class="p-4 space-y-4 flex-1 text-[12.5px] leading-relaxed">
                    {{-- Likes & Published Time --}}
                    <div class="flex items-center justify-between text-[#6B7280] dark:text-[#9CA3AF] text-[12px]">
                        <span class="font-bold text-[#111827] dark:text-white" x-text="(selectedThread?.post?.likes || 0) + ' suka'"></span>
                        <span x-text="selectedThread?.post?.published_at || ''"></span>
                    </div>

                    {{-- Author & Title line --}}
                    <div class="text-[#111827] dark:text-white leading-normal">
                        <strong class="font-bold mr-1.5" x-text="selectedThread?.post?.author || (selectedThread?.channel === 'facebook_comments' ? 'Cooca Indonesia' : 'cooca.indonesia')"></strong>
                        <span class="font-medium" x-text="selectedThread?.post?.title || ''"></span>
                    </div>

                    {{-- TAGS & LABELS CARD --}}
                    <div class="p-3 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#E5E7EB] dark:border-white/10 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[11.5px] text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                <span>Label & Tag Thread</span>
                            </span>
                        </div>

                        {{-- Tag Badges List --}}
                        <div class="flex flex-wrap items-center gap-1.5 min-h-6">
                            <template x-for="tg in (selectedThread.tags || [])" :key="tg">
                                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-[6px] font-medium border"
                                    :class="getTagBadgeClass(tg)">
                                    <span x-text="formatTagLabel(tg)"></span>
                                    <button type="button" @click.stop="removeThreadTag(selectedThread, tg)" class="hover:opacity-75 font-bold" title="Hapus tag">×</button>
                                </span>
                            </template>
                            <span x-show="!(selectedThread.tags || []).length" class="text-[11px] text-[#9CA3AF] italic">Belum ada tag. Pilih tag di bawah:</span>
                        </div>

                        {{-- Quick Tag Toggle Buttons --}}
                        <div class="flex flex-wrap items-center gap-1 pt-1 border-t border-[#E5E7EB]/70 dark:border-white/5">
                            <template x-for="avail in availableTags" :key="avail">
                                <button type="button" @click="toggleThreadTag(selectedThread, avail)"
                                    class="text-[10px] px-2 py-0.5 rounded-[5px] border transition-colors flex items-center gap-1"
                                    :class="(selectedThread.tags || []).includes(avail) ? 'bg-[#1877F2] text-white border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:bg-slate-100'">
                                    <span x-text="(selectedThread.tags || []).includes(avail) ? '✓ ' + formatTagLabel(avail) : '+ ' + formatTagLabel(avail)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Full Caption with paragraph breaks --}}
                    <div class="text-[#4B5563] dark:text-[#D1D5DB] whitespace-pre-line text-[12.5px] leading-relaxed font-normal"
                         x-text="selectedThread?.post?.caption || ''"></div>

                    {{-- Promo / Free Custom Badge Card (Soft solid surface, no gradient) --}}
                    <div class="pt-2 border-t border-[#E5E7EB] dark:border-white/10">
                        <div class="p-3 rounded-[12px] bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/40 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center text-[12px] shadow-xs">🎁</span>
                                <div>
                                    <span class="font-bold text-[12px] text-amber-950 dark:text-amber-200 uppercase tracking-wide block">Free Custom</span>
                                    <span class="text-[11px] text-amber-800 dark:text-amber-300">Tersedia untuk pengguna aktif</span>
                                </div>
                            </div>
                            <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3B. CONTACT & THREAD PROFILE FOR DIRECT CHAT (WHATSAPP, MESSENGER, INSTAGRAM DM) --}}
            <div x-show="selectedThread && !isCommentChannel(selectedThread.channel)" x-cloak
                 class="hidden lg:flex w-[320px] xl:w-[350px] shrink-0 border-l border-[#E5E7EB] dark:border-white/10 bg-white dark:bg-[#1C1C1E] flex-col h-full overflow-y-auto">
                
                {{-- Customer Profile Header Banner --}}
                <div class="p-5 border-b border-[#E5E7EB] dark:border-white/10 bg-slate-50/50 dark:bg-black/10 text-center flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-[#F1F5F9] dark:bg-[#27272A] border-2 border-[#E2E8F0] dark:border-white/10 flex items-center justify-center font-bold text-[20px] text-[#334155] dark:text-[#F1F5F9] mb-2.5 shadow-xs">
                        <span x-text="getInitials(selectedThread.name)"></span>
                    </div>
                    <h3 class="text-[15px] font-bold text-[#111827] dark:text-white" x-text="selectedThread.name"></h3>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold text-white flex items-center gap-1"
                            :class="getChannelBadgeClass(selectedThread.channel)">
                            <i :data-lucide="getChannelIcon(selectedThread.channel)" class="w-2.5 h-2.5"></i>
                            <span x-text="formatChannelName(selectedThread.channel)"></span>
                        </span>
                        <span class="text-[10.5px] px-2 py-0.5 rounded-md font-medium"
                            :class="getStatusBadgeClass(selectedThread.status)"
                            x-text="formatStatusLabel(selectedThread.status)"></span>
                    </div>
                </div>

                {{-- Customer Info & Tag Management --}}
                <div class="p-4 space-y-4 flex-1 text-[12.5px]">
                    {{-- CONTACT DETAIL ITEMS --}}
                    <div class="p-3 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#E5E7EB] dark:border-white/10 space-y-2">
                        <span class="font-bold text-[11px] text-[#6B7280] uppercase tracking-wider block mb-1">Informasi Kontak</span>
                        <template x-if="selectedThread.phone">
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60 dark:border-white/5">
                                <span class="text-slate-500">Telepon / WhatsApp</span>
                                <a :href="'https://wa.me/' + selectedThread.phone.replace(/[^0-9]/g, '')" target="_blank"
                                   class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1" rel="noopener noreferrer">
                                    <i data-lucide="phone" class="w-3 h-3"></i>
                                    <span x-text="selectedThread.phone"></span>
                                </a>
                            </div>
                        </template>
                        <template x-if="selectedThread.email">
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60 dark:border-white/5">
                                <span class="text-slate-500">Email</span>
                                <span class="font-medium text-slate-900 dark:text-white" x-text="selectedThread.email"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between py-1 border-b border-slate-200/60 dark:border-white/5">
                            <span class="text-slate-500">Customer ID</span>
                            <span class="font-mono text-[11.5px] text-slate-700 dark:text-slate-300" x-text="selectedThread.customer_id"></span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-500">Waktu Terakhir</span>
                            <span class="text-slate-700 dark:text-slate-300" x-text="selectedThread.time + ' · ' + (selectedThread.date || '')"></span>
                        </div>
                    </div>

                    {{-- TAGS & LABELS MANAGER CARD --}}
                    <div class="p-3 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] border border-[#E5E7EB] dark:border-white/10 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[11.5px] text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                <span>Label & Tag Percakapan</span>
                            </span>
                        </div>

                        {{-- Active Tag Badges --}}
                        <div class="flex flex-wrap items-center gap-1.5 min-h-6">
                            <template x-for="tg in (selectedThread.tags || [])" :key="tg">
                                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-[6px] font-medium border"
                                    :class="getTagBadgeClass(tg)">
                                    <span x-text="formatTagLabel(tg)"></span>
                                    <button type="button" @click.stop="removeThreadTag(selectedThread, tg)" class="hover:opacity-75 font-bold" title="Hapus tag">×</button>
                                </span>
                            </template>
                            <span x-show="!(selectedThread.tags || []).length" class="text-[11px] text-[#9CA3AF] italic">Belum ada tag. Pilih tag di bawah:</span>
                        </div>

                        {{-- Quick Tag Toggle Buttons --}}
                        <div class="flex flex-wrap items-center gap-1 pt-1 border-t border-[#E5E7EB]/70 dark:border-white/5">
                            <template x-for="avail in availableTags" :key="avail">
                                <button type="button" @click="toggleThreadTag(selectedThread, avail)"
                                    class="text-[10px] px-2 py-0.5 rounded-[5px] border transition-colors flex items-center gap-1"
                                    :class="(selectedThread.tags || []).includes(avail) ? 'bg-[#1877F2] text-white border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:bg-slate-100'">
                                    <span x-text="(selectedThread.tags || []).includes(avail) ? '✓ ' + formatTagLabel(avail) : '+ ' + formatTagLabel(avail)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    {{-- EXPANDED DIRECT MESSAGE (DM) MODAL DIALOG WITH CHAT HISTORY --}}
    <div x-show="activePrivateDm" @keydown.escape.window="activePrivateDm = null" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5">
        <div @click.outside="activePrivateDm = null"
             class="bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 rounded-[24px] shadow-2xl w-full max-w-2xl h-[650px] max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            {{-- Header: Customer Profile, Channel Badge, Actions & Close Button --}}
            <div class="px-5 py-3.5 bg-[#F9FAFB] dark:bg-[#2C2C2E] border-b border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 rounded-full bg-[#F1F5F9] dark:bg-[#27272A] border border-[#E2E8F0] dark:border-white/10 flex items-center justify-center font-bold text-[13px] text-[#334155] dark:text-[#F1F5F9]">
                            <span x-text="activePrivateDm ? getInitials(activePrivateDm.customer_name || activePrivateDm.from_name) : 'U'"></span>
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full flex items-center justify-center text-white ring-2 ring-white dark:ring-[#2C2C2E]"
                              :class="activePrivateDm?.target_channel === 'messenger' ? 'bg-[#1877F2]' : 'bg-[#E1306C]'">
                            <i :data-lucide="activePrivateDm?.target_channel === 'messenger' ? 'message-circle' : 'instagram'" class="w-2.5 h-2.5"></i>
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[14.5px] font-bold text-[#111827] dark:text-white truncate" x-text="activePrivateDm?.customer_name || activePrivateDm?.from_name || 'Pengguna'"></span>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold text-white flex items-center gap-1"
                                  :class="activePrivateDm?.target_channel === 'messenger' ? 'bg-[#1877F2]' : 'bg-[#E1306C]'">
                                <i :data-lucide="activePrivateDm?.target_channel === 'messenger' ? 'message-circle' : 'instagram'" class="w-2.5 h-2.5"></i>
                                <span x-text="activePrivateDm?.target_channel === 'messenger' ? 'Messenger' : 'Instagram Direct'"></span>
                            </span>
                        </div>
                        <p class="text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5">
                            Kirim pesan privat langsung ke inbox akun pelanggan
                        </p>
                    </div>
                </div>

                {{-- Header Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="directToDmOnly(activePrivateDm)"
                            class="px-2.5 py-1.5 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-[#D1D5DB] dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 text-[11.5px] font-medium text-[#1877F2] flex items-center gap-1.5 transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Buka di Ruang DM</span>
                    </button>
                    <button type="button" @click="activePrivateDm = null" class="w-8 h-8 rounded-[8px] text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Context Callout: Originating Comment Reference --}}
            <div class="px-5 py-2.5 bg-blue-50/70 dark:bg-[#1877F2]/10 border-b border-blue-100 dark:border-[#1877F2]/20 flex items-center justify-between gap-3 text-[12px] shrink-0">
                <div class="flex items-center gap-2 truncate text-blue-900 dark:text-blue-200">
                    <i data-lucide="reply" class="w-3.5 h-3.5 text-[#1877F2] shrink-0"></i>
                    <span class="truncate">
                        Membalas komentar: <strong class="font-semibold" x-text="'&quot;' + (activePrivateDm?.message || '') + '&quot;'"></strong>
                    </span>
                </div>
                <span class="text-[11px] text-blue-600 dark:text-blue-400 shrink-0 font-medium hidden sm:inline">
                    Resmi Meta Private Reply
                </span>
            </div>

            {{-- CHAT HISTORY AREA (HISTORY CHAT) --}}
            <div id="dmHistoryContainer" class="flex-1 p-4 sm:p-5 overflow-y-auto space-y-3.5 min-h-0 bg-[#F9FAFB]/60 dark:bg-black/15">
                {{-- Loading Skeleton --}}
                <div x-show="isLoadingDmHistory" class="flex flex-col items-center justify-center h-48 space-y-2">
                    <div class="w-6 h-6 border-2 border-[#1877F2] border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-[12px] text-gray-500">Memuat riwayat chat...</span>
                </div>

                {{-- Empty History State --}}
                <div x-show="!isLoadingDmHistory && dmChatHistory.length === 0" class="h-full flex flex-col items-center justify-center text-center p-6 select-none">
                    <div class="w-14 h-14 rounded-2xl bg-[#E7F3FF] dark:bg-[#1877F2]/20 text-[#1877F2] flex items-center justify-center mb-2.5">
                        <i data-lucide="messages-square" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-[14px] font-bold text-gray-900 dark:text-white">Belum Ada Riwayat Pesan Pribadi</h4>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 max-w-sm mt-1 leading-relaxed">
                        Ini adalah pesan privat pertama ke <strong x-text="'@' + (activePrivateDm?.customer_name || activePrivateDm?.from_name || 'pengguna')"></strong>. Balasan Anda akan terkirim langsung ke inbox Instagram DM / Messenger resmi mereka.
                    </p>
                </div>

                {{-- Render Chat History Messages --}}
                <div x-show="!isLoadingDmHistory && dmChatHistory.length > 0" class="space-y-3">
                    <div class="text-center my-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-black/5 dark:bg-white/10 text-[#6B7280] dark:text-[#9CA3AF]">
                            Riwayat Percakapan
                        </span>
                    </div>

                    <template x-for="(hMsg, hIdx) in dmChatHistory" :key="hMsg.id || hIdx">
                        <div>
                            {{-- Customer Incoming Message (Left Bubble) --}}
                            <template x-if="!hMsg.is_cooca && !hMsg.is_from_page">
                                <div class="flex items-end gap-2.5 max-w-[85%] sm:max-w-[75%]">
                                    <div class="w-7 h-7 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-200 shrink-0 mb-1">
                                        <span x-text="getInitials(hMsg.from_name)"></span>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="bg-white dark:bg-[#2C2C2E] p-3 rounded-[16px] rounded-bl-[3px] border border-[#E5E7EB] dark:border-white/10 shadow-xs">
                                            <span class="text-[10.5px] font-semibold text-[#1877F2] block mb-0.5" x-text="hMsg.from_name"></span>
                                            {{-- Attached Image --}}
                                            <template x-if="hMsg.media_url">
                                                <div class="mb-2 rounded-[10px] overflow-hidden border border-black/5 dark:border-white/10 max-w-[260px]">
                                                    <a :href="hMsg.media_url" target="_blank" rel="noopener">
                                                        <img :src="hMsg.media_url" class="w-full h-auto max-h-48 object-cover hover:opacity-95 transition-opacity" alt="Lampiran Foto" />
                                                    </a>
                                                </div>
                                            </template>
                                            <p class="text-[13px] text-[#1F2937] dark:text-[#F3F4F6] whitespace-pre-wrap leading-relaxed" x-text="hMsg.message"></p>
                                        </div>
                                        <span class="text-[10px] text-[#9CA3AF] px-1 block" x-text="hMsg.time"></span>
                                    </div>
                                </div>
                            </template>

                            {{-- Cooca Official Outbound Message (Right Bubble) --}}
                            <template x-if="hMsg.is_cooca || hMsg.is_from_page">
                                <div class="flex items-end justify-end gap-2.5 ml-auto max-w-[85%] sm:max-w-[75%]">
                                    <div class="space-y-1 text-right">
                                        <div class="text-white p-3 rounded-[16px] rounded-br-[3px] shadow-xs text-left"
                                             :class="activePrivateDm?.target_channel === 'messenger' ? 'bg-[#1877F2]' : 'bg-[#E1306C]'">
                                            <div class="flex items-center gap-1.5 mb-1 text-[10.5px] font-semibold text-white/90">
                                                <i data-lucide="badge-check" class="w-3 h-3 text-white"></i>
                                                <span>Cooca Official</span>
                                            </div>
                                            {{-- Attached Image --}}
                                            <template x-if="hMsg.media_url">
                                                <div class="mb-2 rounded-[10px] overflow-hidden bg-black/20 border border-white/20 max-w-[260px]">
                                                    <a :href="hMsg.media_url" target="_blank" rel="noopener">
                                                        <img :src="hMsg.media_url" class="w-full h-auto max-h-48 object-cover hover:opacity-95 transition-opacity" alt="Lampiran Foto" />
                                                    </a>
                                                </div>
                                            </template>
                                            <p class="text-[13px] text-white whitespace-pre-wrap leading-relaxed" x-text="hMsg.message"></p>
                                        </div>
                                        <span class="text-[10px] text-[#9CA3AF] px-1 block text-right" x-text="hMsg.time"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- COMPOSER & TEMPLATES FOOTER --}}
            <div class="p-4 bg-white dark:bg-[#1C1C1E] border-t border-[#E5E7EB] dark:border-white/10 shrink-0 space-y-2.5">
                {{-- Preview Media Attachment in Modal --}}
                <div x-show="modalAttachedImageUrl" x-cloak class="flex items-center gap-2 p-2 bg-blue-50 dark:bg-white/5 border border-blue-200 dark:border-white/10 rounded-[10px]">
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-black/10 shrink-0 border border-black/10">
                        <img :src="modalAttachedImageUrl" class="w-full h-full object-cover" alt="Preview" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-[11.5px] font-semibold text-gray-800 dark:text-gray-200 truncate block" x-text="modalAttachedImageFilename || 'Foto lampiran siap dikirim'"></span>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Siap dikirimkan ke Meta Graph API
                        </span>
                    </div>
                    <button type="button" @click="removeModalAttachedImage()" class="w-6 h-6 rounded-full hover:bg-rose-100 dark:hover:bg-rose-900/30 text-rose-500 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Quick Templates Pills --}}
                <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-0.5">
                    <span class="text-[11px] font-semibold text-[#9CA3AF] shrink-0 mr-1">Template:</span>
                    <button type="button" @click="privateDmText = 'Halo Kak! Terima kasih telah menghubungi kami. Ada yang bisa kami bantu? 😊'"
                            class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                        Sapaan Ramah
                    </button>
                    <button type="button" @click="privateDmText = 'Halo Kak, mohon informasikan nomor handphone / email akun Cooca Anda agar dapat kami bantu cek lebih lanjut ya.'"
                            class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                        Minta Info Akun
                    </button>
                    <button type="button" @click="privateDmText = 'Halo Kak, untuk informasi paket dan konsultasi gratis implementasi COOCA, bisa kirimkan detail bisnis Anda disini ya!'"
                            class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                        Konsultasi Paket
                    </button>
                </div>

                {{-- Textarea & Action Bar --}}
                <div class="flex items-end gap-2.5">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-black shrink-0 mb-1 hidden sm:block">
                        <img src="{{ asset('assets/image/cooca-icon.png') }}" class="w-full h-full object-cover" alt="Cooca">
                    </div>
                    <div class="flex-1 relative">
                        <textarea id="privateDmTextarea" x-model="privateDmText" @keydown.enter.prevent="if(!$event.shiftKey) submitPrivateDm()"
                                  rows="2"
                                  :placeholder="modalAttachedImageUrl ? 'Tulis pesan/keterangan gambar (opsional)... (Enter untuk kirim)' : 'Tulis pesan DM resmi ke pelanggan... (Enter untuk kirim, Shift+Enter untuk baris baru)'"
                                  class="w-full p-3 rounded-[12px] bg-[#F9FAFB] dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-[#1877F2] resize-none transition-colors"></textarea>
                    </div>

                    {{-- Image Upload Trigger for Modal --}}
                    <input type="file" x-ref="modalMediaFileInput" @change="handleModalFileUpload($event)" accept="image/*" class="hidden">
                    <button type="button" @click="$refs.modalMediaFileInput.click()" :disabled="isUploadingModalMedia" title="Lampirkan Gambar (Maks 5MB)"
                            class="h-[46px] w-[46px] rounded-[12px] border border-[#D1D5DB] dark:border-white/15 bg-[#F9FAFB] dark:bg-[#2C2C2E] hover:bg-[#F3F4F6] dark:hover:bg-white/5 text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#1877F2] flex items-center justify-center transition-colors shrink-0">
                        <i data-lucide="image" class="w-5 h-5" :class="isUploadingModalMedia ? 'animate-spin text-[#1877F2]' : ''"></i>
                    </button>

                    <button type="button" @click="submitPrivateDm()" :disabled="(!privateDmText.trim() && !modalAttachedImageUrl) || isSendingDm || isUploadingModalMedia"
                            class="h-[46px] px-4 sm:px-5 rounded-[12px] bg-[#1877F2] hover:bg-[#166FE5] disabled:opacity-50 disabled:cursor-not-allowed text-white text-[13px] font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all shrink-0">
                        <i data-lucide="send" class="w-4 h-4" :class="isSendingDm ? 'animate-pulse' : ''"></i>
                        <span x-text="isSendingDm ? 'Mengirim...' : 'Kirim DM'"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL PENGATURAN KREDENSIAL META & WEBHOOK --}}
    <div x-show="openMetaSettings" @keydown.escape.window="openMetaSettings = false" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="openMetaSettings = false"
            class="bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 rounded-[20px] shadow-2xl max-w-2xl w-full p-6 space-y-5 overflow-hidden">
            
            <div class="flex items-start justify-between gap-4 border-b border-[#E5E7EB] dark:border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center shrink-0">
                        <i data-lucide="key" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-bold text-[#111827] dark:text-white">Pengaturan Kredensial Meta & Webhook</h2>
                        <p class="text-[12.5px] text-[#6B7280] dark:text-[#9CA3AF]">Integrasi 1 Token & 1 Webhook untuk Messenger, Instagram, Komentar, dan WhatsApp</p>
                    </div>
                </div>
                <button type="button" @click="openMetaSettings = false" class="text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            @if(Auth::guard('admin')->user()?->hasPermission('admin.inbox.manage'))


            <form action="{{ route('admin.inbox.settings') }}" method="POST" class="space-y-4">
                @csrf

                {{-- INFO CALLOUT WEBHOOK --}}
                <div class="p-3.5 rounded-[12px] bg-[#F0F7FF] dark:bg-[#1877F2]/10 border border-[#BAE0FF] dark:border-[#1877F2]/20 space-y-2 text-[12px]">
                    <div class="font-bold text-[#1877F2] flex items-center gap-1.5">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span>URL Callback Webhook Meta Anda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $metaSettings['webhook_url'] ?? url('/api/v1/meta/omnichannel/webhook') }}" id="metaWebhookUrlInput"
                            class="flex-1 bg-white dark:bg-[#2C2C2E] border border-[#BAE0FF] dark:border-[#1877F2]/30 text-[12px] font-mono px-2.5 py-1.5 rounded-lg select-all">
                        <button type="button" @click="navigator.clipboard.writeText(document.getElementById('metaWebhookUrlInput').value); alert('URL Webhook berhasil disalin!')"
                            class="px-3 py-1.5 bg-[#1877F2] text-white rounded-lg font-medium hover:bg-[#166FE5] shrink-0 text-[11.5px]">
                            Salin URL
                        </button>
                    </div>
                    <p class="text-[#4B5563] dark:text-[#9CA3AF] text-[11px] leading-relaxed">
                        Gunakan URL ini di <strong>Meta Developer > Messenger > Webhooks</strong> dan <strong>WhatsApp > Configuration</strong>. Subscribe event: <code class="bg-white/80 dark:bg-black/30 px-1 py-0.5 rounded">messages</code>, <code class="bg-white/80 dark:bg-black/30 px-1 py-0.5 rounded">feed</code>, <code class="bg-white/80 dark:bg-black/30 px-1 py-0.5 rounded">comments</code>.
                    </p>
                </div>

                {{-- SYSTEM USER TOKEN --}}
                <div class="space-y-1">
                    <label class="block text-[12.5px] font-semibold text-[#374151] dark:text-[#D1D5DB]">
                        System User Access Token (Meta) <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="meta_system_user_token" value="{{ $metaSettings['meta_system_user_token'] ?? '' }}"
                        placeholder="EAA... (Token Pengguna Sistem Meta)"
                        class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:border-[#1877F2] focus:outline-hidden">
                    <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                        Token dari Pengaturan Bisnis Meta (Business Settings) > System Users dengan izin yang sudah Anda miliki (<code>pages_messaging</code>, <code>instagram_manage_messages</code>, dll).
                    </p>
                </div>

                {{-- GRID FOR IDs --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    {{-- PAGE ID --}}
                    <div class="space-y-1">
                        <label class="block text-[12.5px] font-semibold text-[#374151] dark:text-[#D1D5DB]">
                            Facebook Page ID
                        </label>
                        <input type="text" name="meta_page_id" value="{{ $metaSettings['meta_page_id'] ?? '' }}"
                            placeholder="Contoh: 1340316975827711"
                            class="w-full h-9.5 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:border-[#1877F2] focus:outline-hidden">
                        <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">ID Halaman untuk Messenger & Komentar Post.</p>
                    </div>

                    {{-- INSTAGRAM ID --}}
                    <div class="space-y-1">
                        <label class="block text-[12.5px] font-semibold text-[#374151] dark:text-[#D1D5DB]">
                            Instagram Business Account ID
                        </label>
                        <input type="text" name="meta_instagram_id" value="{{ $metaSettings['meta_instagram_id'] ?? '' }}"
                            placeholder="Contoh: 17841439846162016"
                            class="w-full h-9.5 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:border-[#1877F2] focus:outline-hidden">
                        <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">ID Akun Bisnis Instagram terkait Halaman.</p>
                    </div>

                    {{-- WHATSAPP PHONE NUMBER ID --}}
                    <div class="space-y-1">
                        <label class="block text-[12.5px] font-semibold text-[#374151] dark:text-[#D1D5DB]">
                            WhatsApp Phone Number ID
                        </label>
                        <input type="text" name="whatsapp_phone_number_id" value="{{ $metaSettings['whatsapp_phone_number_id'] ?? '' }}"
                            placeholder="Contoh: 104523456789123"
                            class="w-full h-9.5 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:border-[#1877F2] focus:outline-hidden">
                        <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">ID Nomor Telepon dari WhatsApp Cloud API.</p>
                    </div>

                    {{-- WEBHOOK VERIFY TOKEN --}}
                    <div class="space-y-1">
                        <label class="block text-[12.5px] font-semibold text-[#374151] dark:text-[#D1D5DB]">
                            Webhook Verify Token
                        </label>
                        <input type="text" name="social_media_webhook_verify_token" value="{{ $metaSettings['social_media_webhook_verify_token'] ?? 'cooca_meta_social_webhook_token' }}"
                            placeholder="cooca_meta_social_webhook_token"
                            class="w-full h-9.5 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:border-[#1877F2] focus:outline-hidden">
                        <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">Token verifikasi saat memasukkan webhook di Meta.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#E5E7EB] dark:border-white/10">
                    <button type="button" @click="openMetaSettings = false"
                        class="h-9.5 px-4 rounded-[10px] text-[13px] font-medium text-[#4B5563] dark:text-[#9CA3AF] hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9.5 px-5 rounded-[10px] bg-[#1877F2] hover:bg-[#166FE5] text-white text-[13px] font-bold shadow-sm transition-all flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Kredensial</span>
                    </button>
                </div>
            </form>
            @endif

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL BENTO APPLE HIG: KELOLA AI CHATBOT & PEDOMAN KERJA (DO'S & DON'TS)   --}}
    {{-- ========================================================================= --}}
    <div x-show="openAiSettings" @keydown.escape.window="openAiSettings = false" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5">
        <div @click.outside="openAiSettings = false"
             class="bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 rounded-[24px] shadow-2xl w-full max-w-4xl h-[780px] max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 bg-[#F9FAFB] dark:bg-[#2C2C2E] border-b border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shadow-sm shadow-purple-500/25 shrink-0">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-[16px] font-bold text-[#111827] dark:text-white truncate">Kelola AI Chatbot & Pedoman Kerja</h2>
                            <span x-show="aiConfig.enabled" class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Aktif</span>
                            <span x-show="!aiConfig.enabled" class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-200 text-slate-600 dark:bg-white/10 dark:text-slate-400">Non-Aktif</span>
                        </div>
                        <p class="text-[12px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5">
                            Pusat informasi sistem, aturan Do's & Don'ts, serta pengaturan kecerdasan buatan untuk merespon percakapan pelanggan
                        </p>
                    </div>
                </div>

                <button type="button" @click="openAiSettings = false" class="w-8 h-8 rounded-[8px] text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Navigation Tabs --}}
            <div class="px-6 py-2 border-b border-[#E5E7EB] dark:border-white/10 flex items-center gap-2 overflow-x-auto scrollbar-none bg-white dark:bg-[#1C1C1E] shrink-0">
                <button type="button" @click="aiActiveTab = 'status'"
                    :class="aiActiveTab === 'status' ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-semibold border-purple-200 dark:border-purple-800/40' : 'text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white border-transparent'"
                    class="px-3.5 py-1.5 rounded-[10px] border text-[12.5px] flex items-center gap-1.5 transition-all whitespace-nowrap">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                    <span>Status & Mode Operasi</span>
                </button>

                <button type="button" @click="aiActiveTab = 'info'"
                    :class="aiActiveTab === 'info' ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-semibold border-purple-200 dark:border-purple-800/40' : 'text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white border-transparent'"
                    class="px-3.5 py-1.5 rounded-[10px] border text-[12.5px] flex items-center gap-1.5 transition-all whitespace-nowrap">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Informasi Sistem (Knowledge Base)</span>
                </button>

                <button type="button" @click="aiActiveTab = 'dos_donts'"
                    :class="aiActiveTab === 'dos_donts' ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-semibold border-purple-200 dark:border-purple-800/40' : 'text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white border-transparent'"
                    class="px-3.5 py-1.5 rounded-[10px] border text-[12.5px] flex items-center gap-1.5 transition-all whitespace-nowrap">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Pedoman Kerja (Do's & Don'ts)</span>
                </button>

                <button type="button" @click="aiActiveTab = 'test'"
                    :class="aiActiveTab === 'test' ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-semibold border-purple-200 dark:border-purple-800/40' : 'text-[#6B7280] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white border-transparent'"
                    class="px-3.5 py-1.5 rounded-[10px] border text-[12.5px] flex items-center gap-1.5 transition-all whitespace-nowrap">
                    <i data-lucide="play-circle" class="w-3.5 h-3.5"></i>
                    <span>Uji Coba Chatbot (AI Sandbox)</span>
                </button>
            </div>

            {{-- Modal Body (Scrollable Bento Container) --}}
            <div class="flex-1 p-6 overflow-y-auto space-y-5 bg-[#F9FAFB]/50 dark:bg-black/10 min-h-0 text-[13px]">
                
                {{-- TAB 1: STATUS & MODE OPERASI --}}
                <div x-show="aiActiveTab === 'status'" class="space-y-4">
                    {{-- 1. Switch Enable / Disable --}}
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <span class="font-bold text-[#111827] dark:text-white block">Status AI Chatbot di Kotak Masuk</span>
                            <span class="text-[12px] text-[#6B7280] dark:text-[#9CA3AF] block mt-0.5">
                                Aktifkan kemampuan AI untuk memberikan draf balasan dan memproses pesan masuk pelanggan.
                            </span>
                        </div>
                        <button type="button" @click="aiConfig.enabled = !aiConfig.enabled"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                            :class="aiConfig.enabled ? 'bg-purple-600' : 'bg-gray-300 dark:bg-gray-700'">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                :class="aiConfig.enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>

                    {{-- 2. Operation Mode (Cards) --}}
                    <div class="space-y-2">
                        <label class="block text-[12.5px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                            Mode Operasi Chatbot
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- Assistant Mode --}}
                            <div @click="aiConfig.mode = 'assistant'"
                                 class="p-4 rounded-[16px] border cursor-pointer transition-all flex flex-col justify-between"
                                 :class="aiConfig.mode === 'assistant' ? 'bg-purple-50/60 dark:bg-purple-950/30 border-purple-300 dark:border-purple-700 shadow-xs ring-1 ring-purple-400/30' : 'bg-white dark:bg-[#2C2C2E] border-[#E5E7EB] dark:border-white/10 hover:border-gray-300'">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                            Rekomendasi (Aman)
                                        </span>
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                             :class="aiConfig.mode === 'assistant' ? 'border-purple-600 bg-purple-600 text-white' : 'border-gray-400'">
                                            <i x-show="aiConfig.mode === 'assistant'" data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-[14px] text-[#111827] dark:text-white">Mode Asisten (Human-in-the-Loop)</h4>
                                    <p class="text-[12px] text-[#6B7280] dark:text-[#9CA3AF] mt-1 leading-relaxed">
                                        AI membantu menyusun draf balasan instan dengan tombol <em>"Bantu Jawab dengan AI"</em>. Admin/operator tetap memeriksa dan mengirim balasan secara manual.
                                    </p>
                                </div>
                            </div>

                            {{-- Auto-Pilot Mode --}}
                            <div @click="aiConfig.mode = 'auto_pilot'"
                                 class="p-4 rounded-[16px] border cursor-pointer transition-all flex flex-col justify-between"
                                 :class="aiConfig.mode === 'auto_pilot' ? 'bg-purple-50/60 dark:bg-purple-950/30 border-purple-300 dark:border-purple-700 shadow-xs ring-1 ring-purple-400/30' : 'bg-white dark:bg-[#2C2C2E] border-[#E5E7EB] dark:border-white/10 hover:border-gray-300'">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                            Otomatis Penuh (24/7)
                                        </span>
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                             :class="aiConfig.mode === 'auto_pilot' ? 'border-purple-600 bg-purple-600 text-white' : 'border-gray-400'">
                                            <i x-show="aiConfig.mode === 'auto_pilot'" data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-[14px] text-[#111827] dark:text-white">Mode Auto-Pilot (Balasan Otomatis)</h4>
                                    <p class="text-[12px] text-[#6B7280] dark:text-[#9CA3AF] mt-1 leading-relaxed">
                                        AI membalas pesan masuk pelanggan secara otomatis seketika pesan diterima melalui webhook/polling pada saluran yang Anda beri izin di bawah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Covered Channels Checklist --}}
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-3">
                        <label class="block text-[12.5px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                            Saluran yang Diaktifkan AI
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="live_chat" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">Live Chat Website</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="whatsapp" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">WhatsApp Official</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="instagram" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">Instagram Direct</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="messenger" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">Facebook Messenger</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="facebook_comments" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">Komentar Facebook</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" value="instagram_comments" x-model="aiConfig.channels" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-[12px] font-medium text-[#111827] dark:text-white">Komentar Instagram</span>
                            </label>
                        </div>
                    </div>

                    {{-- 4. Model & Temperature --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-2">
                            <label class="block text-[12px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                                Model AI Terhubung
                            </label>
                            <select x-model="aiConfig.model"
                                class="w-full h-10 px-3 rounded-[10px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-[#D1D5DB] dark:border-white/15 text-[12.5px] font-mono text-[#111827] dark:text-white focus:outline-hidden focus:border-purple-500">
                                <template x-for="mod in (aiConfig.platform_engine?.available_models || [])" :key="mod.id">
                                    <option :value="mod.id" x-text="mod.id + ' (' + (mod.tier || 'balanced') + ')'"></option>
                                </template>
                                <option value="ag/gemini-3.8-flash-high">ag/gemini-3.8-flash-high (Default Recommended)</option>
                                <option value="ag/gemini-3.8-flash-medium">ag/gemini-3.8-flash-medium</option>
                                <option value="ag/gemini-3.8-flash-low">ag/gemini-3.8-flash-low</option>
                                <option value="cx/gpt-6-astra-xhigh">cx/gpt-6-astra-xhigh</option>
                                <option value="cx/gpt-5.6-sol">cx/gpt-5.6-sol</option>
                            </select>
                            <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                                Model AI yang dikoneksikan via tunnel platform OpenAI-Compatible engine terpusat.
                            </p>
                        </div>

                        <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[12px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                                    Tingkat Kepatuhan (Temperature)
                                </label>
                                <span class="font-mono text-[12px] font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300" x-text="aiConfig.temperature"></span>
                            </div>
                            <input type="range" min="0" max="1" step="0.05" x-model="aiConfig.temperature"
                                class="w-full accent-purple-600 cursor-pointer">
                            <div class="flex justify-between text-[10.5px] text-[#6B7280] dark:text-[#9CA3AF]">
                                <span>0.0 (Ketat & Bebas Halusinasi)</span>
                                <span>0.3 (Optimal CS)</span>
                                <span>1.0 (Kreatif)</span>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Greeting Template --}}
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-2">
                        <label class="block text-[12px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                            Template Sapaan Fallback
                        </label>
                        <input type="text" x-model="aiConfig.greeting_template"
                            placeholder="Halo Kak {customer_name}! Terima kasih sudah menghubungi COOCA..."
                            class="w-full h-10 px-3 rounded-[10px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-[#D1D5DB] dark:border-white/15 text-[12.5px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-purple-500">
                        <p class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                            Gunakan tag <code>{customer_name}</code> untuk otomatis menyisipkan nama pelanggan saat sapaan pembuka.
                        </p>
                    </div>
                </div>

                {{-- TAB 2: INFORMASI SISTEM (KNOWLEDGE BASE) --}}
                <div x-show="aiActiveTab === 'info'" class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3.5 rounded-[14px] bg-purple-50/80 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-800/30">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0"></i>
                            <span class="text-[12.5px] text-purple-950 dark:text-purple-200">
                                Pengetahuan resmi ini menjadi acuan kebenaran mutlak (Single Source of Truth) bagi AI dalam menjawab pelanggan.
                            </span>
                        </div>
                        <button type="button" @click="loadDefaultSystemInfo()"
                            class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-purple-200 dark:border-purple-800/40 text-[11.5px] font-semibold text-purple-700 dark:text-purple-300 hover:bg-purple-100 transition-colors shrink-0 shadow-2xs">
                            Muat Template Resmi COOCA
                        </button>
                    </div>

                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[12px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                                Teks Pengetahuan Sistem & Bisnis
                            </label>
                            <span class="text-[11px] text-[#6B7280]" x-text="(aiConfig.system_info || '').length + ' karakter'"></span>
                        </div>
                        <textarea x-model="aiConfig.system_info" rows="16"
                            placeholder="Tulis profil bisnis, daftar produk, rincian harga, panduan registrasi, dan jam kerja..."
                            class="w-full p-3.5 rounded-[12px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-[#D1D5DB] dark:border-white/15 text-[12.5px] leading-relaxed font-mono text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-purple-500 resize-y transition-colors"></textarea>
                    </div>
                </div>

                {{-- TAB 3: PEDOMAN KERJA (DO'S & DON'TS) --}}
                <div x-show="aiActiveTab === 'dos_donts'" class="space-y-4">
                    <p class="text-[12.5px] text-[#6B7280] dark:text-[#9CA3AF] leading-relaxed">
                        Aturan ketat yang mengunci etika dan batasan kerja AI. Pastikan AI mematuhi hal yang diwajibkan (Do's) dan tidak pernah melanggar pantangan (Don'ts).
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- KOLOM DO'S --}}
                        <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-emerald-200 dark:border-emerald-800/40 shadow-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-emerald-100 dark:border-emerald-950/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <h4 class="font-bold text-[13.5px] text-emerald-900 dark:text-emerald-300">DO'S (Wajib Dilakukan)</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400" x-text="(aiConfig.dos || []).length + ' Aturan'"></span>
                            </div>

                            {{-- List of Do's --}}
                            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="(item, idx) in (aiConfig.dos || [])" :key="idx">
                                    <div class="p-2.5 rounded-[10px] bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 flex items-start justify-between gap-2 text-[12px]">
                                        <div class="flex items-start gap-2 min-w-0">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold shrink-0 mt-0.5">✓</span>
                                            <span class="text-emerald-950 dark:text-emerald-200 leading-snug" x-text="item"></span>
                                        </div>
                                        <button type="button" @click="removeDoItem(idx)" class="text-gray-400 hover:text-rose-600 shrink-0 p-0.5" title="Hapus aturan">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Add New Do Item --}}
                            <div class="pt-2 border-t border-emerald-100 dark:border-emerald-950/40 flex items-center gap-1.5">
                                <input type="text" x-model="newDoText" @keydown.enter.prevent="addDoItem()"
                                    placeholder="Tambah pedoman Do's baru..."
                                    class="flex-1 h-9 px-3 rounded-[8px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-emerald-200 dark:border-emerald-800/40 text-[12px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-emerald-500">
                                <button type="button" @click="addDoItem()"
                                    class="h-9 px-3 rounded-[8px] bg-emerald-600 hover:bg-emerald-700 text-white text-[12px] font-bold shrink-0 transition-colors">
                                    + Tambah
                                </button>
                            </div>
                        </div>

                        {{-- KOLOM DON'TS --}}
                        <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-rose-200 dark:border-rose-800/40 shadow-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-rose-100 dark:border-rose-950/40">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <h4 class="font-bold text-[13.5px] text-rose-900 dark:text-rose-300">DON'TS (Dilarang Keras)</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400" x-text="(aiConfig.donts || []).length + ' Larangan'"></span>
                            </div>

                            {{-- List of Don'ts --}}
                            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="(item, idx) in (aiConfig.donts || [])" :key="idx">
                                    <div class="p-2.5 rounded-[10px] bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 flex items-start justify-between gap-2 text-[12px]">
                                        <div class="flex items-start gap-2 min-w-0">
                                            <span class="text-rose-600 dark:text-rose-400 font-bold shrink-0 mt-0.5">✕</span>
                                            <span class="text-rose-950 dark:text-rose-200 leading-snug" x-text="item"></span>
                                        </div>
                                        <button type="button" @click="removeDontItem(idx)" class="text-gray-400 hover:text-rose-600 shrink-0 p-0.5" title="Hapus larangan">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Add New Don't Item --}}
                            <div class="pt-2 border-t border-rose-100 dark:border-rose-950/40 flex items-center gap-1.5">
                                <input type="text" x-model="newDontText" @keydown.enter.prevent="addDontItem()"
                                    placeholder="Tambah pantangan Don't baru..."
                                    class="flex-1 h-9 px-3 rounded-[8px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-rose-200 dark:border-rose-800/40 text-[12px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-rose-500">
                                <button type="button" @click="addDontItem()"
                                    class="h-9 px-3 rounded-[8px] bg-rose-600 hover:bg-rose-700 text-white text-[12px] font-bold shrink-0 transition-colors">
                                    + Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 4: UJI COBA CHATBOT (AI SANDBOX) --}}
                <div x-show="aiActiveTab === 'test'" class="space-y-4">
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-[#E5E7EB] dark:border-white/10 shadow-xs space-y-3">
                        <label class="block text-[12.5px] font-bold text-[#374151] dark:text-[#D1D5DB] uppercase tracking-wider">
                            Simulasi Pertanyaan Pelanggan
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="aiTestMessage" @keydown.enter.prevent="runAiTest()"
                                placeholder="Ketik pesan simulasi (misal: 'Halo, harga paket Pro berapa ya? Bisa minta diskon?')..."
                                class="flex-1 h-11 px-3.5 rounded-[12px] bg-[#F9FAFB] dark:bg-[#1C1C1E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-purple-500">
                            <button type="button" @click="runAiTest()" :disabled="!aiTestMessage.trim() || isTestingAi"
                                class="h-11 px-5 rounded-[12px] bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-[13px] font-bold flex items-center gap-2 shadow-sm transition-all shrink-0">
                                <i data-lucide="play" class="w-4 h-4" :class="isTestingAi ? 'animate-spin' : ''"></i>
                                <span x-text="isTestingAi ? 'Memproses...' : 'Uji Balasan AI'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- AI Test Output Window --}}
                    <div x-show="aiTestResult" class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-purple-200 dark:border-purple-800/40 shadow-xs space-y-2.5 animate-in fade-in">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-[13px] text-[#111827] dark:text-white">Hasil Simulasi Balasan AI:</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-[#6B7280]">
                                <span class="font-mono bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded" x-text="aiTestResult?.model"></span>
                                <span>·</span>
                                <span x-text="(aiTestResult?.latency_ms || 0) + ' ms'"></span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-[12px] bg-[#F9FAFB] dark:bg-black/20 border border-[#E5E7EB] dark:border-white/5 text-[13px] text-[#1F2937] dark:text-[#F3F4F6] whitespace-pre-wrap leading-relaxed"
                             x-text="aiTestResult?.reply"></div>

                        <div class="pt-1 flex items-center justify-between text-[11px] text-[#6B7280]">
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                Mematuhi Aturan Do's & Pantangan Don'ts
                            </span>
                            <button type="button" @click="insertTemplate(aiTestResult.reply); openAiSettings = false"
                                class="text-purple-600 dark:text-purple-400 font-semibold hover:underline">
                                Pakai Jawaban Ini di Composer Chat →
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Modal Footer Actions --}}
            <div class="px-6 py-3.5 bg-[#F9FAFB] dark:bg-[#2C2C2E] border-t border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                <div class="text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF] hidden sm:block">
                    Perubahan langsung berlaku untuk seluruh agen CS dan balasan otomatis.
                </div>

                <div class="flex items-center gap-2.5 ml-auto">
                    <button type="button" @click="openAiSettings = false"
                        class="h-9.5 px-4 rounded-[10px] text-[13px] font-medium text-[#4B5563] dark:text-[#9CA3AF] hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        Tutup
                    </button>
                    <button type="button" @click="saveAiSettings()" :disabled="isSavingAi"
                        class="h-9.5 px-5 rounded-[10px] bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-[13px] font-bold shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4" :class="isSavingAi ? 'animate-spin' : ''"></i>
                        <span x-text="isSavingAi ? 'Menyimpan...' : 'Simpan Pedoman & Konfigurasi'"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
function adminInboxHub() {
    return {
        allThreads: @json($allThreads ?? []),
        displayedThreads: @json($threads ?? []),
        selectedThread: null,
        counts: @json($counts ?? []),
        currentChannel: @json($channel ?? 'all'),
        currentStatus: @json($status ?? 'all'),
        currentTag: @json($currentTag ?? 'all'),
        availableTags: ['prioritas', 'client', 'prospek', 'tindak_lanjut', 'komplain', 'vip'],
        customTagVal: '',
        customDmTagVal: '',
        openFilterMore: false,
        searchKeyword: @json($search ?? ''),
        replyText: '',
        replyTargetComment: null,
        activePrivateDm: null,
        privateDmText: '',
        attachedImageUrl: '',
        attachedImageFilename: '',
        isUploadingMedia: false,
        modalAttachedImageUrl: '',
        modalAttachedImageFilename: '',
        isUploadingModalMedia: false,
        dmChatHistory: [],
        isLoadingDmHistory: false,
        isSendingDm: false,
        isSending: false,
        isSyncing: false,
        pollTimer: null,
        openMetaSettings: false,
        openAiSettings: false,
        aiActiveTab: 'status',
        aiConfig: @json($aiSettings ?? []),
        isSavingAi: false,
        isGeneratingAi: false,
        aiTestMessage: 'Halo Kak, mau tanya harga paket Pro COOCA berapa ya per bulan dan apa saja keunggulannya?',
        aiTestResult: null,
        isTestingAi: false,
        newDoText: '',
        newDontText: '',

        init() {
            // Select first thread if available
            if (this.displayedThreads.length > 0) {
                this.selectedThread = this.displayedThreads[0];
            }
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });

            // Start background polling for live incoming messages every 3.5 seconds
            this.pollTimer = setInterval(() => {
                this.pollLiveUpdates();
            }, 3500);

            // Connect to Laravel Reverb WebSockets for instant message broadcasting
            this.initReverbBroadcasting();
        },

        initReverbBroadcasting() {
            if (typeof Pusher === 'undefined') return;

            try {
                const reverbKey = '{{ config('broadcasting.connections.reverb.key') }}' || 'cooca_reverb_key_2026';
                const reverbHost = '{{ config('broadcasting.connections.reverb.options.host') }}' || window.location.hostname;
                const reverbPort = {{ (int) (config('broadcasting.connections.reverb.options.port') ?: 8080) }};
                const reverbScheme = '{{ config('broadcasting.connections.reverb.options.scheme') ?: 'http' }}';

                const pusher = new Pusher(reverbKey, {
                    wsHost: reverbHost,
                    wsPort: reverbPort,
                    wssPort: reverbPort,
                    forceTLS: reverbScheme === 'https',
                    enabledTransports: ['ws', 'wss'],
                    cluster: 'mt1',
                });

                const adminChannel = pusher.subscribe('admin.inbox');
                adminChannel.bind('message.sent', (data) => {
                    console.log('[AdminInbox] Reverb real-time broadcast received:', data);
                    this.pollLiveUpdates();
                });
            } catch (err) {
                console.warn('[AdminInbox] Reverb listener init notice:', err);
            }
        },

        async pollLiveUpdates() {
            if (this.isPolling || this.isSending || this.isSendingDm || this.isSyncing) return;
            this.isPolling = true;

            try {
                const url = `/admin/inbox/poll?channel=${this.currentChannel}&status=${this.currentStatus}&tag=${encodeURIComponent(this.currentTag || 'all')}&search=${encodeURIComponent(this.searchKeyword || '')}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.threads) {
                        this.allThreads = data.threads;
                        if (data.counts) {
                            this.counts = data.counts;
                        }
                        this.filterThreads(false);

                        // If a thread is currently open, seamlessly sync its messages
                        if (this.selectedThread) {
                            const updated = this.allThreads.find(t => 
                                t.id === this.selectedThread.id || 
                                (t.customer_id && t.customer_id === this.selectedThread.customer_id && t.channel === this.selectedThread.channel)
                            );
                            if (updated && updated.messages && (
                                updated.messages.length !== (this.selectedThread.messages?.length || 0) ||
                                updated.last_message !== this.selectedThread.last_message
                            )) {
                                const oldLen = this.selectedThread.messages?.length || 0;
                                this.selectedThread.messages = updated.messages;
                                this.selectedThread.replies = updated.replies || [];
                                this.selectedThread.parent_comment = updated.parent_comment || null;
                                this.selectedThread.last_message = updated.last_message;
                                this.selectedThread.time = updated.time;
                                this.selectedThread.status = updated.status;
                                this.selectedThread.is_unread = updated.is_unread;
                                if (updated.messages.length > oldLen) {
                                    this.$nextTick(() => {
                                        this.scrollToBottom();
                                        if (window.lucide) window.lucide.createIcons();
                                    });
                                }
                            }
                        }

                        // If DM Modal dialog is currently open, auto-sync its messages too in real-time
                        if (this.activePrivateDm && !this.isLoadingDmHistory) {
                            const cId = this.activePrivateDm.customer_id || this.activePrivateDm.from_id;
                            const cName = this.activePrivateDm.customer_name || this.activePrivateDm.from_name;
                            const ch = this.activePrivateDm.target_channel || 'instagram';
                            try {
                                const queryParams = new URLSearchParams({
                                    customer_id: cId || '',
                                    customer_name: cName || '',
                                    channel: ch,
                                });
                                const hRes = await fetch(`/admin/inbox/customer-history?${queryParams.toString()}`, {
                                    headers: { 'Accept': 'application/json' }
                                });
                                if (hRes.ok) {
                                    const hData = await hRes.json();
                                    if (hData.success && Array.isArray(hData.messages) && hData.messages.length !== this.dmChatHistory.length) {
                                        this.dmChatHistory = hData.messages;
                                        this.$nextTick(() => {
                                            this.scrollDmHistoryToBottom();
                                            if (window.lucide) window.lucide.createIcons();
                                        });
                                    }
                                }
                            } catch (_) {}
                        }
                    }
                }
            } catch (e) {
                // Background polling fails silently without interrupting admin
            } finally {
                this.isPolling = false;
            }
        },

        isCommentChannel(channel) {
            return ['facebook_comments', 'instagram_comments', 'facebook'].includes(channel);
        },

        setChannel(channel) {
            this.currentChannel = channel;
            this.filterThreads();
        },

        setStatus(status) {
            if (this.currentStatus === status) {
                this.currentStatus = 'all';
            } else {
                this.currentStatus = status;
            }
            this.filterThreads();
        },

        setTag(tag) {
            if (this.currentTag === tag) {
                this.currentTag = 'all';
            } else {
                this.currentTag = tag;
            }
            this.filterThreads();
        },

        toggleStatusFilterDropdown() {
            // cycle quick statuses
            const states = ['all', 'unread', 'priority', 'ad_replies', 'followup'];
            const nextIdx = (states.indexOf(this.currentStatus) + 1) % states.length;
            this.currentStatus = states[nextIdx];
            this.filterThreads();
        },

        resetFilters() {
            this.currentChannel = 'all';
            this.currentStatus = 'all';
            this.currentTag = 'all';
            this.searchKeyword = '';
            this.filterThreads();
        },

        filterThreads(resetSelection = true) {
            const channel = this.currentChannel;
            const status = this.currentStatus;
            const tag = this.currentTag;
            const search = (this.searchKeyword || '').toLowerCase().trim();

            this.displayedThreads = this.allThreads.filter(t => {
                // Channel check
                let matchChannel = true;
                if (channel !== 'all') {
                    if (channel === 'live_chat') {
                        matchChannel = (t.channel === 'live_chat');
                    } else if (channel === 'messenger') {
                        matchChannel = (t.channel === 'messenger');
                    } else if (channel === 'instagram') {
                        matchChannel = ['instagram', 'instagram_dm'].includes(t.channel);
                    } else if (channel === 'whatsapp') {
                        matchChannel = (t.channel === 'whatsapp');
                    } else if (channel === 'facebook_comments') {
                        matchChannel = ['facebook', 'facebook_comments'].includes(t.channel);
                    } else if (channel === 'instagram_comments') {
                        matchChannel = (t.channel === 'instagram_comments');
                    }
                }

                // Status check
                let matchStatus = true;
                if (status !== 'all') {
                    if (status === 'unread') {
                        matchStatus = (t.is_unread || t.status === 'unread');
                    } else {
                        matchStatus = (t.status === status);
                    }
                }

                // Tag check
                let matchTag = true;
                if (tag && tag !== 'all') {
                    const curTag = String(tag).toLowerCase().trim();
                    const threadTags = Array.isArray(t.tags) ? t.tags.map(x => String(x).toLowerCase()) : [];
                    if (curTag === 'prioritas') {
                        matchTag = threadTags.includes('prioritas') || t.status === 'priority';
                    } else if (curTag === 'client') {
                        matchTag = threadTags.includes('client') || threadTags.includes('pelanggan');
                    } else if (curTag === 'prospek') {
                        matchTag = threadTags.includes('prospek');
                    } else if (curTag === 'tindak_lanjut') {
                        matchTag = threadTags.includes('tindak_lanjut') || t.status === 'followup';
                    } else if (curTag === 'komplain') {
                        matchTag = threadTags.includes('komplain');
                    } else if (curTag === 'vip') {
                        matchTag = threadTags.includes('vip');
                    } else {
                        matchTag = threadTags.includes(curTag);
                    }
                }

                // Search check
                let matchSearch = true;
                if (search) {
                    const name = (t.name || '').toLowerCase();
                    const lastMsg = (t.last_message || '').toLowerCase();
                    matchSearch = name.includes(search) || lastMsg.includes(search);
                }

                return matchChannel && matchStatus && matchTag && matchSearch;
            });

            // Adjust selected thread
            if (this.displayedThreads.length > 0) {
                const stillExists = this.selectedThread && this.displayedThreads.some(t => 
                    t.id === this.selectedThread.id || 
                    (t.customer_id && t.customer_id === this.selectedThread.customer_id && t.channel === this.selectedThread.channel)
                );
                if (!this.selectedThread || (!stillExists && resetSelection)) {
                    this.selectedThread = this.displayedThreads[0];
                }
            } else {
                if (resetSelection) {
                    this.selectedThread = null;
                }
            }

            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        selectThread(thread) {
            this.selectedThread = thread;
            if (thread.is_unread) {
                thread.is_unread = false;
                if (thread.status === 'unread') thread.status = 'read';
                this.updateThreadStatus('read');
            }
            this.replyTargetComment = null;
            this.replyText = '';
            this.$nextTick(() => {
                this.scrollToBottom();
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        insertTemplate(text) {
            this.replyText = text;
        },

        scrollToBottom() {
            const el = document.getElementById('messagesContainer');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        getInitials(name) {
            if (!name) return 'U';
            return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
        },

        getChannelBadgeClass(channel) {
            switch(channel) {
                case 'live_chat': return 'bg-[#7C3AED]';
                case 'messenger': return 'bg-[#1877F2]';
                case 'instagram':
                case 'instagram_dm':
                case 'instagram_comments': return 'bg-[#E1306C]';
                case 'whatsapp': return 'bg-[#25D366]';
                case 'facebook':
                case 'facebook_comments': return 'bg-[#1877F2]';
                default: return 'bg-slate-500';
            }
        },

        getChannelIcon(channel) {
            switch(channel) {
                case 'live_chat': return 'message-circle';
                case 'messenger': return 'message-circle';
                case 'instagram':
                case 'instagram_dm': return 'instagram';
                case 'instagram_comments': return 'message-square';
                case 'whatsapp': return 'message-circle';
                case 'facebook':
                case 'facebook_comments': return 'message-square';
                default: return 'message-square';
            }
        },

        formatChannelName(channel) {
            switch(channel) {
                case 'live_chat': return 'Live Chat Website';
                case 'messenger': return 'Messenger';
                case 'instagram':
                case 'instagram_dm': return 'Instagram DM';
                case 'instagram_comments': return 'Instagram Komentar';
                case 'whatsapp': return 'WhatsApp';
                case 'facebook':
                case 'facebook_comments': return 'Facebook Komentar';
                default: return channel;
            }
        },

        getStatusBadgeClass(status) {
            switch(status) {
                case 'unread': return 'bg-[#E7F3FF] text-[#1877F2]';
                case 'priority': return 'bg-[#FFF4E5] text-[#FF9500]';
                case 'ad_replies': return 'bg-[#F3E8FF] text-[#AF52DE]';
                case 'followup': return 'bg-[#E0F2FE] text-[#0284C7]';
                case 'read':
                case 'resolved': return 'bg-[#DCFCE7] text-[#15803D]';
                default: return 'bg-gray-100 text-gray-700';
            }
        },

        formatStatusLabel(status) {
            switch(status) {
                case 'unread': return 'Belum Dibaca';
                case 'priority': return 'Prioritas';
                case 'ad_replies': return 'Balasan Iklan';
                case 'followup': return 'Tindak Lanjut';
                case 'read': return 'Sudah Dibaca';
                case 'resolved': return 'Selesai';
                default: return status || 'Belum Dibaca';
            }
        },

        getParentComment(thread) {
            if (!thread) return { from_name: 'Pengguna', message: '', time: '' };
            if (thread.parent_comment) return thread.parent_comment;
            if (Array.isArray(thread.messages) && thread.messages.length > 0) return thread.messages[0];
            return {
                id: thread.id,
                comment_id: thread.comment_id,
                from_id: thread.customer_id,
                from_name: thread.name || 'Pengguna',
                message: thread.last_message || '',
                is_from_page: false,
                is_cooca: false,
                role: 'visitor',
                time: thread.time_relative || thread.time || 'Baru saja',
                time_exact: thread.time || '',
                date: thread.date || 'Hari ini',
                parent_id: null,
            };
        },

        getThreadReplies(thread) {
            if (!thread) return [];
            if (Array.isArray(thread.replies)) return thread.replies;
            if (Array.isArray(thread.messages) && thread.messages.length > 1) {
                return thread.messages.slice(1);
            }
            return [];
        },

        startReplyComment(msg) {
            this.replyTargetComment = msg;
            this.$nextTick(() => {
                const input = document.querySelector('input[placeholder*="komentar"]');
                if (input) input.focus();
            });
        },

        cancelReplyTarget() {
            this.replyTargetComment = null;
        },

        async openPrivateDmModal(msg) {
            const parent = this.getParentComment(this.selectedThread);
            const targetMsg = msg || parent;
            const channel = this.selectedThread ? this.selectedThread.channel : 'instagram_comments';
            const targetDmChannel = (channel === 'facebook_comments' || channel === 'facebook') ? 'messenger' : 'instagram';

            const customerId = targetMsg?.from_id || this.selectedThread?.customer_id;
            const customerName = targetMsg?.from_name || this.selectedThread?.name;

            this.activePrivateDm = {
                ...targetMsg,
                customer_id: customerId,
                customer_name: customerName,
                target_channel: targetDmChannel,
                channel: channel,
                post_title: this.selectedThread?.post?.title || this.selectedThread?.post_snippet || 'Postingan Media Sosial',
            };
            this.privateDmText = '';
            this.dmChatHistory = [];
            this.isLoadingDmHistory = true;

            // 1. Immediately look up matching threads in memory for instant UX
            const localThread = this.allThreads.find(t =>
                (t.channel === targetDmChannel || ['instagram', 'instagram_dm', 'messenger', 'whatsapp'].includes(t.channel)) &&
                (t.customer_id === customerId || t.name === customerName)
            );
            if (localThread && Array.isArray(localThread.messages) && localThread.messages.length > 0) {
                this.dmChatHistory = [...localThread.messages];
            }

            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });

            // 2. Concurrently fetch fresh server history from database
            try {
                const queryParams = new URLSearchParams({
                    customer_id: customerId || '',
                    customer_name: customerName || '',
                    channel: targetDmChannel,
                });

                const res = await fetch(`/admin/inbox/customer-history?${queryParams.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.messages)) {
                        this.dmChatHistory = data.messages;
                    }
                }
            } catch (e) {
                console.warn('Failed to load customer history:', e);
            } finally {
                this.isLoadingDmHistory = false;
                this.$nextTick(() => {
                    this.scrollDmHistoryToBottom();
                    if (window.lucide) window.lucide.createIcons();
                    const textarea = document.getElementById('privateDmTextarea');
                    if (textarea) textarea.focus();
                });
            }
        },

        scrollDmHistoryToBottom() {
            const el = document.getElementById('dmHistoryContainer');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        async handleFileUpload(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB');
                event.target.value = '';
                return;
            }

            this.isUploadingMedia = true;
            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch('/admin/inbox/upload-media', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.attachedImageUrl = data.url;
                    this.attachedImageFilename = data.filename || file.name;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                } else {
                    alert(data.message || 'Gagal mengupload gambar.');
                }
            } catch (err) {
                alert('Gagal mengupload gambar: ' + err.message);
            } finally {
                this.isUploadingMedia = false;
                event.target.value = '';
            }
        },

        removeAttachedImage() {
            this.attachedImageUrl = '';
            this.attachedImageFilename = '';
        },

        async handleModalFileUpload(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB');
                event.target.value = '';
                return;
            }

            this.isUploadingModalMedia = true;
            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch('/admin/inbox/upload-media', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.modalAttachedImageUrl = data.url;
                    this.modalAttachedImageFilename = data.filename || file.name;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                } else {
                    alert(data.message || 'Gagal mengupload gambar.');
                }
            } catch (err) {
                alert('Gagal mengupload gambar: ' + err.message);
            } finally {
                this.isUploadingModalMedia = false;
                event.target.value = '';
            }
        },

        removeModalAttachedImage() {
            this.modalAttachedImageUrl = '';
            this.modalAttachedImageFilename = '';
        },

        async submitPrivateDm() {
            if (!this.activePrivateDm || (!this.privateDmText.trim() && !this.modalAttachedImageUrl) || this.isSendingDm || this.isUploadingModalMedia) return;

            const commentId = this.activePrivateDm.id || this.selectedThread?.id;
            const message = this.privateDmText.trim();
            const imageUrl = this.modalAttachedImageUrl || null;
            this.isSendingDm = true;

            try {
                const response = await fetch(`/admin/inbox/send-private-dm/${commentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: message, image_url: imageUrl })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const newReply = data.reply || {
                        id: Date.now(),
                        from_name: 'Cooca Indonesia',
                        message: message || (imageUrl ? '[Foto]' : ''),
                        media_url: imageUrl,
                        media_type: imageUrl ? 'image' : null,
                        is_from_page: true,
                        is_cooca: true,
                        role: 'cooca',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        date: 'Hari ini',
                    };

                    // Instantly append to dialog history
                    this.dmChatHistory.push(newReply);
                    this.privateDmText = '';
                    this.modalAttachedImageUrl = '';
                    this.modalAttachedImageFilename = '';

                    // Update thread status if on comment
                    if (this.selectedThread) {
                        this.selectedThread.status = 'replied';
                    }

                    this.$nextTick(() => {
                        this.scrollDmHistoryToBottom();
                        if (window.lucide) window.lucide.createIcons();
                        const textarea = document.getElementById('privateDmTextarea');
                        if (textarea) textarea.focus();
                    });
                } else {
                    alert(data.message || 'Gagal mengirim pesan DM.');
                }
            } catch (err) {
                alert('Terjadi kesalahan saat mengirim pesan DM: ' + err.message);
            } finally {
                this.isSendingDm = false;
            }
        },

        directToDmOnly(activeDm) {
            if (!activeDm) return;
            const targetChannel = (activeDm.channel === 'facebook_comments' || this.selectedThread?.channel === 'facebook_comments') ? 'messenger' : 'instagram';
            const customerId = activeDm.from_id || this.selectedThread?.customer_id;

            this.activePrivateDm = null;
            this.setChannel(targetChannel);
            this.filterThreads();

            let dmThread = this.displayedThreads.find(t => t.customer_id === customerId)
                        || this.allThreads.find(t => t.customer_id === customerId);

            if (dmThread) {
                this.selectThread(dmThread);
            }
        },

        async sendCommentReply() {
            if (!this.selectedThread || !this.replyText.trim() || this.isSending) return;

            const threadId = this.selectedThread.id;
            let textToSend = this.replyText.trim();
            if (this.replyTargetComment && !textToSend.includes('@' + this.replyTargetComment.from_name)) {
                textToSend = `@${this.replyTargetComment.from_name} ${textToSend}`;
            }

            this.isSending = true;

            try {
                const response = await fetch(`/admin/inbox/reply/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: textToSend })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const newReply = data.reply || {
                        id: Date.now(),
                        message: textToSend,
                        from_name: 'Cooca Indonesia',
                        is_from_page: true,
                        is_cooca: true,
                        role: 'cooca',
                        time: 'Baru saja',
                        time_exact: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        date: 'Hari ini',
                    };

                    // Add to replies array
                    if (!this.selectedThread.replies) {
                        this.selectedThread.replies = [];
                    }
                    this.selectedThread.replies.push(newReply);

                    // Also keep messages array synchronized
                    if (!this.selectedThread.messages) {
                        this.selectedThread.messages = [this.getParentComment(this.selectedThread)];
                    }
                    this.selectedThread.messages.push(newReply);

                    this.selectedThread.is_unread = false;
                    this.selectedThread.status = 'read';

                    this.replyText = '';
                    this.replyTargetComment = null;

                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else {
                    alert(data.message || 'Gagal mengirim balasan komentar.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.isSending = false;
            }
        },

        async sendReply() {
            if (!this.selectedThread || (!this.replyText.trim() && !this.attachedImageUrl) || this.isSending || this.isUploadingMedia) return;

            const threadId = this.selectedThread.id;
            const message = this.replyText.trim();
            const imageUrl = this.attachedImageUrl || null;
            this.isSending = true;

            try {
                const response = await fetch(`/admin/inbox/reply/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: message, image_url: imageUrl })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const newReply = data.reply || {
                        id: Date.now(),
                        message: message || (imageUrl ? '[Foto]' : ''),
                        media_url: imageUrl,
                        media_type: imageUrl ? 'image' : null,
                        created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        sender: 'Cooca Official',
                    };

                    this.selectedThread.messages.push({
                        id: newReply.id,
                        from_name: 'Cooca Official',
                        message: newReply.message,
                        media_url: newReply.media_url || imageUrl,
                        media_type: newReply.media_type || (imageUrl ? 'image' : null),
                        is_from_page: true,
                        is_cooca: true,
                        role: 'cooca',
                        time: newReply.created_at || 'Baru saja',
                        date: 'Hari ini',
                    });

                    this.selectedThread.last_message = newReply.message || '[Foto]';
                    this.selectedThread.is_unread = false;
                    this.selectedThread.status = 'read';

                    this.replyText = '';
                    this.attachedImageUrl = '';
                    this.attachedImageFilename = '';

                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else {
                    alert(data.message || 'Gagal mengirim balasan.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.isSending = false;
            }
        },

        async updateThreadStatus(status) {
            if (!this.selectedThread) return;
            const threadId = this.selectedThread.id;

            try {
                const response = await fetch(`/admin/inbox/toggle-status/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status })
                });

                if (response.ok) {
                    this.selectedThread.status = status;
                    this.selectedThread.is_unread = (status === 'unread');
                    this.filterThreads();
                }
            } catch (err) {
                console.error('Failed to update status:', err);
            }
        },

        async markAllAsRead() {
            this.allThreads.forEach(t => {
                t.is_unread = false;
                if (t.status === 'unread') t.status = 'read';
            });
            this.filterThreads();
        },

        async syncMessages() {
            if (this.isSyncing) return;
            this.isSyncing = true;

            try {
                const response = await fetch('/admin/inbox/sync-meta', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message || 'Sinkronisasi berhasil.');
                    if (data.count && data.count > 0) {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Gagal menyinkronkan data.');
                    if (data.message && data.message.includes('belum diisi')) {
                        this.openMetaSettings = true;
                    }
                }
            } catch (err) {
                alert('Terjadi kendala saat menyinkronkan data: ' + err.message);
            } finally {
                this.isSyncing = false;
            }
        },

        async toggleThreadTag(thread, tag) {
            if (!thread || !tag) return;
            tag = String(tag).toLowerCase().trim();
            if (!Array.isArray(thread.tags)) {
                thread.tags = [];
            }
            const hasTag = thread.tags.map(t => String(t).toLowerCase()).includes(tag);
            const action = hasTag ? 'remove' : 'add';

            // Optimistic update locally
            if (action === 'add') {
                thread.tags.push(tag);
                if (tag === 'prioritas') thread.status = 'priority';
            } else {
                thread.tags = thread.tags.filter(t => String(t).toLowerCase() !== tag);
                if (tag === 'prioritas' && thread.status === 'priority') thread.status = 'read';
            }

            // Sync with all instances in allThreads
            this.allThreads.forEach(t => {
                if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                    t.tags = [...thread.tags];
                    if (tag === 'prioritas') t.status = thread.status;
                }
            });

            this.recalculateCounts();
            this.filterThreads(false);

            try {
                const response = await fetch(`/admin/inbox/tags/${thread.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action, tag })
                });
                const data = await response.json();
                if (response.ok && data.success && Array.isArray(data.tags)) {
                    thread.tags = data.tags;
                    this.allThreads.forEach(t => {
                        if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                            t.tags = [...data.tags];
                        }
                    });
                    this.recalculateCounts();
                    this.filterThreads(false);
                }
            } catch (err) {
                console.error('Failed to toggle tag:', err);
            }
        },

        async toggleThreadTakeover(thread, action = null) {
            if (!thread) return;
            const targetAction = action !== null ? action : (thread.is_human_takeover ? 'release' : 'takeover');
            const prevStatus = thread.is_human_takeover;

            // Optimistic update
            thread.is_human_takeover = (targetAction === 'takeover');

            this.allThreads.forEach(t => {
                if (t.id === thread.id || (t.thread_key && thread.thread_key && t.thread_key === thread.thread_key)) {
                    t.is_human_takeover = thread.is_human_takeover;
                    if (targetAction === 'takeover') {
                        t.takeover_by_name = t.takeover_by_name || 'Admin COOCA';
                    }
                }
            });

            setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 50);

            try {
                const response = await fetch(`/admin/inbox/toggle-takeover/${thread.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action: targetAction })
                });
                const data = await response.json();
                if (data.success) {
                    thread.is_human_takeover = data.is_human_takeover;
                    thread.takeover_by_name = data.takeover_by_name;
                    thread.takeover_at = data.takeover_at;
                    this.allThreads.forEach(t => {
                        if (t.id === thread.id || (t.thread_key && thread.thread_key && t.thread_key === thread.thread_key)) {
                            t.is_human_takeover = data.is_human_takeover;
                            t.takeover_by_name = data.takeover_by_name;
                            t.takeover_at = data.takeover_at;
                        }
                    });
                } else {
                    thread.is_human_takeover = prevStatus;
                    alert(data.message || 'Gagal mengubah status takeover.');
                }
            } catch (err) {
                thread.is_human_takeover = prevStatus;
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 50);
            }
        },

        async addCustomTag(thread, tag) {
            if (!thread || !tag) return;
            tag = String(tag).toLowerCase().trim();
            if (!tag) return;

            if (!Array.isArray(thread.tags)) {
                thread.tags = [];
            }
            if (!thread.tags.map(t => String(t).toLowerCase()).includes(tag)) {
                thread.tags.push(tag);
                this.allThreads.forEach(t => {
                    if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                        t.tags = [...thread.tags];
                    }
                });
                this.recalculateCounts();
                this.filterThreads(false);
            }

            try {
                const response = await fetch(`/admin/inbox/tags/${thread.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action: 'add', tag })
                });
                const data = await response.json();
                if (response.ok && data.success && Array.isArray(data.tags)) {
                    thread.tags = data.tags;
                    this.allThreads.forEach(t => {
                        if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                            t.tags = [...data.tags];
                        }
                    });
                    this.recalculateCounts();
                    this.filterThreads(false);
                }
            } catch (err) {
                console.error('Failed to add custom tag:', err);
            }
        },

        async removeThreadTag(thread, tag) {
            if (!thread || !tag) return;
            tag = String(tag).toLowerCase().trim();

            if (Array.isArray(thread.tags)) {
                thread.tags = thread.tags.filter(t => String(t).toLowerCase() !== tag);
                if (tag === 'prioritas' && thread.status === 'priority') {
                    thread.status = 'read';
                }
                this.allThreads.forEach(t => {
                    if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                        t.tags = [...thread.tags];
                        if (tag === 'prioritas' && t.status === 'priority') t.status = 'read';
                    }
                });
                this.recalculateCounts();
                this.filterThreads(false);
            }

            try {
                const response = await fetch(`/admin/inbox/tags/${thread.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action: 'remove', tag })
                });
                const data = await response.json();
                if (response.ok && data.success && Array.isArray(data.tags)) {
                    thread.tags = data.tags;
                    this.allThreads.forEach(t => {
                        if (t.id === thread.id || (t.customer_id && thread.customer_id && t.customer_id === thread.customer_id)) {
                            t.tags = [...data.tags];
                        }
                    });
                    this.recalculateCounts();
                    this.filterThreads(false);
                }
            } catch (err) {
                console.error('Failed to remove tag:', err);
            }
        },

        recalculateCounts() {
            const threads = this.allThreads || [];
            this.counts = {
                ...this.counts,
                all: threads.length,
                unread: threads.filter(t => t.is_unread).length,
                priority: threads.filter(t => t.status === 'priority' || (Array.isArray(t.tags) && t.tags.includes('prioritas'))).length,
                tag_prioritas: threads.filter(t => (Array.isArray(t.tags) && t.tags.includes('prioritas')) || t.status === 'priority').length,
                tag_client: threads.filter(t => Array.isArray(t.tags) && (t.tags.includes('client') || t.tags.includes('pelanggan'))).length,
                tag_prospek: threads.filter(t => Array.isArray(t.tags) && t.tags.includes('prospek')).length,
                tag_tindak_lanjut: threads.filter(t => (Array.isArray(t.tags) && t.tags.includes('tindak_lanjut')) || t.status === 'followup').length,
                tag_komplain: threads.filter(t => Array.isArray(t.tags) && t.tags.includes('komplain')).length,
                tag_vip: threads.filter(t => Array.isArray(t.tags) && t.tags.includes('vip')).length,
            };
        },

        getTagBadgeClass(tag) {
            const t = String(tag || '').toLowerCase().trim();
            switch (t) {
                case 'prioritas':
                    return 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-800/50';
                case 'client':
                case 'pelanggan':
                    return 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-200 dark:border-blue-800/50';
                case 'prospek':
                    return 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:border-emerald-800/50';
                case 'tindak_lanjut':
                    return 'bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-800/50';
                case 'komplain':
                    return 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-200 dark:border-rose-800/50';
                case 'vip':
                    return 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-950/40 dark:text-purple-200 dark:border-purple-800/50';
                default:
                    return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-white/10 dark:text-gray-200 dark:border-white/10';
            }
        },

        formatTagLabel(tag) {
            if (!tag) return '';
            const t = String(tag).toLowerCase().trim();
            switch (t) {
                case 'prioritas': return 'Prioritas';
                case 'client': return 'Client';
                case 'pelanggan': return 'Pelanggan';
                case 'prospek': return 'Prospek';
                case 'tindak_lanjut': return 'Tindak Lanjut';
                case 'komplain': return 'Komplain';
                case 'vip': return 'VIP';
                default:
                    return t.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
        },

        async generateAiReplyForCurrent() {
            if (!this.selectedThread) {
                alert('Pilih percakapan terlebih dahulu.');
                return;
            }

            let targetCommentId = this.selectedThread.id;
            if (this.selectedThread.comments && this.selectedThread.comments.length > 0) {
                const parent = this.getParentComment(this.selectedThread);
                targetCommentId = parent.id || targetCommentId;
            }

            this.isGeneratingAi = true;
            try {
                const res = await fetch(`/admin/inbox/generate-ai-reply/${targetCommentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const data = await res.json();
                if (data.success && data.reply) {
                    this.replyText = data.reply;
                    this.showToastNotification('Draf balasan AI berhasil dibuat (' + (data.model || 'AI') + ')');
                } else {
                    alert(data.message || data.error || 'Gagal membuat draf AI.');
                }
            } catch (err) {
                console.error('[AdminInboxAi] Error generating reply:', err);
                alert('Terjadi kesalahan koneksi saat memanggil AI.');
            } finally {
                this.isGeneratingAi = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async saveAiSettings() {
            this.isSavingAi = true;
            try {
                const res = await fetch('/admin/inbox/ai-settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(this.aiConfig)
                });

                const data = await res.json();
                if (data.success) {
                    this.aiConfig = data.settings;
                    this.showToastNotification('Pengaturan AI Chatbot & Pedoman Kerja berhasil disimpan!');
                    this.openAiSettings = false;
                } else {
                    alert(data.message || 'Gagal menyimpan pengaturan.');
                }
            } catch (err) {
                console.error('[AdminInboxAi] Error saving settings:', err);
                alert('Gagal menyimpan pengaturan AI.');
            } finally {
                this.isSavingAi = false;
            }
        },

        async runAiTest() {
            if (!this.aiTestMessage.trim()) return;

            this.isTestingAi = true;
            this.aiTestResult = null;
            try {
                const res = await fetch('/admin/inbox/ai-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        message: this.aiTestMessage,
                        system_info: this.aiConfig.system_info,
                        dos: this.aiConfig.dos,
                        donts: this.aiConfig.donts,
                        model: this.aiConfig.model,
                        temperature: this.aiConfig.temperature
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.aiTestResult = data;
                } else {
                    alert(data.message || data.error || 'Gagal memproses simulasi AI.');
                }
            } catch (err) {
                console.error('[AdminInboxAi] Error running test:', err);
                alert('Koneksi simulasi AI gagal.');
            } finally {
                this.isTestingAi = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        addDoItem() {
            const val = (this.newDoText || '').trim();
            if (!val) return;
            if (!Array.isArray(this.aiConfig.dos)) {
                this.aiConfig.dos = [];
            }
            this.aiConfig.dos.push(val);
            this.newDoText = '';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        removeDoItem(idx) {
            if (Array.isArray(this.aiConfig.dos)) {
                this.aiConfig.dos.splice(idx, 1);
            }
        },

        addDontItem() {
            const val = (this.newDontText || '').trim();
            if (!val) return;
            if (!Array.isArray(this.aiConfig.donts)) {
                this.aiConfig.donts = [];
            }
            this.aiConfig.donts.push(val);
            this.newDontText = '';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        removeDontItem(idx) {
            if (Array.isArray(this.aiConfig.donts)) {
                this.aiConfig.donts.splice(idx, 1);
            }
        },

        loadDefaultSystemInfo() {
            this.aiConfig.system_info = `[PROFIL COOCA INDONESIA & BUSINESS OPERATING SYSTEM]
COOCA adalah platform SaaS Business Operating System (BOS) All-in-One yang dirancang khusus untuk memajukan UMKM Indonesia. COOCA menghubungkan seluruh operasional bisnis mulai dari kasir POS, manajemen inventori multi-gudang, kalkulasi HPP resep otomatis (BOM), pembukuan keuangan real-time, channel penjualan omnichannel (WhatsApp, Toko Online Digital, Shopee, Tokopedia, TikTok Shop), HRM & absensi GPS, hingga asisten AI bisnis dalam satu sistem terpadu tanpa software terpisah dan dengan sinkronisasi 0 detik delay.

[KATALOG RESMI PAKET & HARGA BERLANGGANAN]
(Sumber Data Tunggal: CMS Billing Packages Platform COOCA)
- Uji Coba Gratis (Free Trial): 14 Hari Akses Penuh ke semua fitur tanpa perlu kartu kredit dan tanpa komitmen.
- Paket Solo (Gratis): Cocok untuk usaha perorangan yang baru merintis pencatatan transaksi kasir sederhana.
- Paket Standard Plan:
  * Harga Bulanan: Rp 39.000 / bulan
  * Harga Tahunan: Rp 390.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: UMKM Pemula (toko kelontong, kafe kecil, bengkel rumahan) dengan kuota hingga 1.000 transaksi kasir/bulan, 1 outlet/toko, dan master data produk dasar.
- Paket Premium Plan (Paling Populer & Rekomendasi):
  * Harga Bulanan: Rp 89.000 / bulan
  * Harga Tahunan: Rp 890.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: Bisnis berkembang & multi-cabang (hingga 3 outlet cabang/gudang), kuota transaksi kasir tanpa batas, manajemen harga per cabang (Multi-Branch Pricing), sinkronisasi Omnichannel Marketplace (Shopee, Tokopedia, TikTok Shop), integrasi WhatsApp Gateway, dan analitik bisnis AI.
- Paket Prestige Plan (Enterprise & Waralaba):
  * Harga Bulanan: Rp 199.000 / bulan
  * Harga Tahunan: Rp 1.990.000 / tahun (Promo: Hemat 2 Bulan)
  * Target Segmen: Jaringan korporat, waralaba, dan distributor besar dengan outlet tanpa batas, kuota penyimpanan cloud besar, prioritas bantuan teknis 24/7, integrasi API kustom, dan SLA 99.9%.

[KUOTA TOKEN AI & PENYIMPANAN CLOUD (STORAGE)]
- Kuota Token AI Bawaan: 10.000.000 token / bulan sudah termasuk dalam paket langganan aktif.
- Top-Up Token AI Tambahan: Rp 50.000 per 1.000.000 token (dapat dibeli instan secara mandiri di dashboard).
- Kapasitas Dasar Storage Owner: 3 GB ruang penyimpanan cloud gratis untuk seluruh cabang.
- Top-Up Storage Tambahan: Rp 50.000 per 1 GB (bersifat permanen dan diakumulasikan).
- Metode Pembayaran Resmi: QRIS Dinamis Otomatis (GoPay, OVO, ShopeePay, DANA, BCA Mobile, Livin, BRImo) dan Virtual Account Bank (BCA, Mandiri, BNI, BRI, Permata) dengan verifikasi otomatis detik itu juga tanpa perlu kirim bukti transfer manual.

[PANDUAN SETUP AWAL & ONBOARDING (5 MENIT MEMULAI)]
1. Profil Usaha & Identitas Struk: Buka menu Pengaturan -> Profil Bisnis (/settings?tab=general). Masukkan nama usaha, logo (PNG transparan 500x500px), alamat, dan nomor WhatsApp resmi. Data ini otomatis tercetak pada struk belanja kasir dan dokumen faktur resmi.
2. Pajak & Service Charge: Buka menu Pengaturan -> Pajak & Biaya (/settings?tab=pos_financial). Atur pajak PB1/PPN (Tax Inclusive atau Tax Exclusive) serta Service Charge resto bila ada.
3. Multi-Gudang & Cabang: Buka menu Inventori -> Gudang (/inventory). Buat minimal 1 gudang utama (contoh: Gudang Pusat / Toko Utama).
4. Staf & Hak Akses (RBAC): Buka menu Pengaturan -> Peran & Hak Akses (/roles). Daftarkan akun kasir atau staf gudang dengan membatasi akses ke laporan keuangan sensitif.
5. Import Excel: Unggah ratusan produk dan stok awal sekaligus via tombol Import Excel di sidebar.

[PANDUAN OPERASIONAL KASIR MODERN (POS)]
- 4 Langkah Kasir Harian:
  1. Buka Shift: Masukkan modal kas awal laci (Starting Cash).
  2. Input Pesanan: Scan barcode (F4) atau klik menu produk. Untuk resto, tentukan meja tamu dan modifier (topping/level pedas).
  3. Pembayaran Cepat: Tekan F2 untuk Quick Pay. Pilih metode Tunai, QRIS Dinamis, Transfer Bank, atau Split Bill (bagi tagihan per orang/nominal).
  4. Tutup Shift: Hitung uang kas fisik laci, sistem mencocokkan selisih kas, lalu cetak laporan Z-Report kasir.
- Kitchen Display & Tiket Dapur (KDS): Tiket pesanan makanan otomatis terkirim ke printer dapur dan layar barista.
- Printer Kasir: Buka Pengaturan Kasir -> Printer. Mendukung printer thermal 58mm dan 80mm standar Bluetooth & USB.
- Anti-Fraud Kasir: Void transaksi, pembatalan pesanan yang sudah tercetak, atau diskon di atas batas wewenang mewajibkan PIN Supervisor 6-digit dan terekam di Audit Log.
- Mode Offline Caching: Jika internet toko terputus, kasir tetap bisa bertransaksi dan mencetak struk. Data otomatis tersinkronisasi kembali ke server saat internet menyala.

[PANDUAN STOK, RESEP BOM & HPP OTOMATIS]
- Resep Bill of Materials (BOM): Menu makanan/minuman dikaitkan ke bahan baku mentah di menu Produk -> Atur Resep (/products/{id}/bom). Saat kasir menjual 1 porsi Es Kopi Susu, stok espresso, susu, gula aren, dan cup otomatis terpotong per gram/ml di gudang cabang tersebut.
- HPP Real-Time: Jika harga beli bahan mentah dari pemasok naik, HPP menu otomatis ter-update sehingga laba kotor selalu presisi.
- Stock Opname Berkala: Pada menu Inventori -> Stock Opname (/inventory/opnames), unduh Lembar Hitung Kosong untuk staf, input hasil hitung fisik, lalu klik Rekonsiliasi agar sistem menyesuaikan stok dan mencatat selisih ke laporan laba-rugi.
- Transfer Antar-Gudang: Distribusi stok dari Gudang Pusat ke Cabang melalui menu Inventori -> Transfer Stok (/inventory/transfers) dengan status Pending In-Transit dan tombol Konfirmasi Terima di cabang tujuan.

[PANDUAN PENGADAAN (PURCHASING) & PENJUALAN B2B]
- Pengadaan Supplier (PO): Buat Purchase Order di /purchase-orders -> Terima barang fisik gudang (GRN) -> Sistem mencatat faktur hutang dagang (AP) beserta jadwal jatuh tempo.
- Penjualan B2B & Grosir: Buat Surat Penawaran (Quotation PDF) -> Konversi jadi Sales Order (SO) di /sales-orders -> Terbitkan Surat Jalan (Delivery Order) -> Terbitkan Faktur Penagihan (Invoice AR bertempo).

[PANDUAN PEMBUKUAN KEUANGAN & AKUNTANSI]
- Laporan Laba Rugi (P&L): Pantau omzet kotor, beban pokok penjualan (HPP), biaya operasional (listrik, sewa, gaji), dan laba bersih riil setiap saat di /reports.
- Neraca Saldo: Memantau saldo kas fisik di laci, rekening bank, total valuasi stok gudang, dan kewajiban hutang.
- Rekonsiliasi Bank: Buka /finance/reconciliations untuk mencocokkan mutasi rekening koran CSV (BCA, Mandiri, BRI, BNI) dengan buku kas COOCA. Catat biaya admin bank melalui tombol Buat Pengeluaran Cepat.
- Ekspor Laporan: Seluruh laporan penjualan dan finansial dapat diunduh ke format XLSX dan PDF siap audit.

[PANDUAN KARYAWAN, PRESENSI GPS & PENGGAJIAN (HRM)]
- Presensi GPS Geofencing: Karyawan melakukan absensi masuk/pulang lewat smartphone dengan validasi radius toko (rekomendasi radius: 50-100 meter di Pengaturan Cabang).
- Pengaturan Shift: Atur shift kerja pagi/siang/malam dengan toleransi keterlambatan menit otomatis di /hrm.
- Perhitungan Gaji & Komisi: Bonus penjualan kasir terkalkulasi otomatis ke slip gaji bulanan dan dapat dicetak ke PDF resmi.

[PANDUAN CRM, LOYALITAS & OMNICHANNEL]
- Member Tiering: Akumulasi belanja menaikkan tingkat member (Silver -> Gold -> Platinum) dengan poin yang bisa dipotongkan sebagai diskon belanja di kasir POS.
- WhatsApp Broadcast & Struk Digital: Struk belanja ramah lingkungan otomatis terkirim ke WhatsApp pelanggan tanpa kertas.
- Integrasi Marketplace: Sinkronisasi produk, harga, dan stok otomatis ke Shopee, Tokopedia, dan TikTok Shop untuk mencegah overselling.
- QR Order Meja Dine-In: Pelanggan scan kode QR di meja, buka menu digital interaktif, pesan langsung dari HP, dan bayar via QRIS otomatis.

[KOLEKSI 8 KALKULATOR BISNIS UMKM GRATIS (cooca.id/kalkulator)]
1. Kalkulator HPP: Menghitung biaya bahan baku + operasional per unit/porsi.
2. Kalkulator BEP: Mengetahui titik impas penjualan agar tidak merugi.
3. Kalkulator Margin & Markup: Menentukan harga jual berdasarkan target margin keuntungan.
4. Kalkulator Laba Bersih: Menghitung laba riil setelah dikurangi seluruh beban usaha.
5. Kalkulator Gaji Karyawan: Menghitung gaji pokok, komisi, dan tunjangan staf.
6. Kalkulator PPh Final 0.5%: Menghitung kewajiban pajak UMKM omzet bruto (PP 23/2018).
7. Kalkulator Omzet Harian: Menentukan target omzet harian untuk mencapai target bulanan.
8. Simulasi What-If: Simulasi dampak kenaikan biaya bahan baku terhadap laba bersih.

[PANDUAN PEMECAHAN MASALAH TEKNIS (TROUBLESHOOTING)]
- Printer thermal bluetooth tidak mencetak: Pastikan bluetooth aktif dan sudah di-pairing, buka menu Pengaturan Kasir -> Printer di COOCA, pilih nama printer dan lakukan "Test Print", pastikan kertas thermal tidak terpasang terbalik.
- Selisih kas laci saat tutup shift: Periksa apakah ada pengeluaran kas kecil yang lupa dicatat di tombol "Uang Keluar Kas" atau transaksi non-tunai yang keliru diinput sebagai tunai.
- Karyawan gagal absen "di luar jangkauan": Buka Pengaturan -> Cabang, periksa titik koordinat latitude/longitude di Google Maps, naikkan radius geofence menjadi 50 atau 100 meter.
- PIN Supervisor salah saat void pesanan: Owner dapat mereset 6 digit PIN Supervisor baru di Pengaturan -> Keuangan & Kasir POS.
- Logo struk tidak muncul / rusak: Gunakan file format PNG transparan dengan resolusi ideal 500x500 pixel.
- Struk tidak terkirim ke WhatsApp: Periksa status koneksi di Pengaturan -> WhatsApp Gateway, pastikan nomor pelanggan diawali kode negara 628...
- Muncul Akses Ditolak (403): Minta Owner memberikan hak akses melalui Pengaturan -> Peran & Hak Akses (contoh: izin accounting.view untuk laporan keuangan).

[JAM OPERASIONAL & LAYANAN RESMI]
- WhatsApp Hotline Resmi Customer Service: +62 852-8786-4176 (Tersedia panduan foto & video call)
- Email Dukungan Resmi: support@cooca.id
- Jam Layanan Customer Service: Setiap Hari (Senin - Minggu) pukul 08:00 - 22:00 WIB (SLA respon < 15 menit).
- Layanan Darurat Server & Infrastruktur: Siaga 24 Jam 7 Hari seminggu (24/7).
- Website Resmi: https://cooca.id
- Pendaftaran Uji Coba Gratis: https://cooca.id/register`;
        },

        showToastNotification(msg) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-[12px] bg-purple-900 text-white text-[12.5px] font-medium shadow-xl flex items-center gap-2 animate-in fade-in slide-in-from-bottom-2 duration-200';
            toast.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400"></span> <span>${msg}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endpush
@endsection
