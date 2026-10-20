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

        <div class="flex items-center gap-2">
            <button type="button" @click="syncMessages()" :disabled="isSyncing"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-[#1F2937] dark:text-white bg-white dark:bg-[#1C1C1E] border border-[#E5E7EB] dark:border-white/10 hover:bg-[#F9FAFB] dark:hover:bg-white/[0.04] transition-all flex items-center gap-1.5 shadow-xs">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#1877F2]" :class="isSyncing ? 'animate-spin' : ''"></i>
                <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Pesan'"></span>
            </button>
            <a href="{{ route('admin.social-media.index') }}"
                class="min-h-[36px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-[#1877F2] bg-[#E7F3FF] dark:bg-[#1877F2]/20 hover:bg-[#DBEAFE] transition-all flex items-center gap-1.5">
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
                @if(($counts['all'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-black/5 dark:bg-white/10">{{ $counts['all'] }}</span>
                @endif
            </button>

            <button type="button" @click="setChannel('messenger')"
                :class="currentChannel === 'messenger' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Messenger</span>
                @if(($counts['messenger'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#1877F2]/15 text-[#1877F2] font-bold">{{ $counts['messenger'] }}</span>
                @endif
            </button>

            <button type="button" @click="setChannel('instagram')"
                :class="currentChannel === 'instagram' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Instagram</span>
                @if(($counts['instagram'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#E1306C]/15 text-[#E1306C] font-bold">{{ $counts['instagram'] }}</span>
                @endif
            </button>

            <button type="button" @click="setChannel('whatsapp')"
                :class="currentChannel === 'whatsapp' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>WhatsApp</span>
                @if(($counts['whatsapp'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#25D366]/15 text-[#25D366] font-bold">{{ $counts['whatsapp'] }}</span>
                @endif
            </button>

            <button type="button" @click="setChannel('facebook_comments')"
                :class="currentChannel === 'facebook_comments' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Komentar Facebook</span>
                @if(($counts['facebook_comments'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#1877F2]/15 text-[#1877F2] font-bold">{{ $counts['facebook_comments'] }}</span>
                @endif
            </button>

            <button type="button" @click="setChannel('instagram_comments')"
                :class="currentChannel === 'instagram_comments' ? 'bg-[#E7F3FF] text-[#1877F2] font-semibold dark:bg-[#1877F2]/20 dark:text-[#64D2FF]' : 'text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]'"
                class="px-4 py-1.5 rounded-[10px] text-[13.5px] transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0">
                <span>Komentar Instagram</span>
                @if(($counts['instagram_comments'] ?? 0) > 0)
                    <span class="text-[11px] px-1.5 py-0.2 rounded-full bg-[#E1306C]/15 text-[#E1306C] font-bold">{{ $counts['instagram_comments'] }}</span>
                @endif
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

                    {{-- QUICK FILTER PILLS ROW --}}
                    <div class="flex items-center justify-between gap-1 overflow-x-auto scrollbar-none pt-0.5">
                        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                            <button type="button" @click="setStatus('unread')"
                                :class="currentStatus === 'unread' ? 'bg-[#1877F2] text-white font-medium border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[12px] border transition-colors whitespace-nowrap">
                                Belum Dibaca
                            </button>

                            <button type="button" @click="setStatus('priority')"
                                :class="currentStatus === 'priority' ? 'bg-[#1877F2] text-white font-medium border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[12px] border transition-colors whitespace-nowrap">
                                Prioritas
                            </button>

                            <button type="button" @click="setStatus('ad_replies')"
                                :class="currentStatus === 'ad_replies' ? 'bg-[#1877F2] text-white font-medium border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[12px] border transition-colors whitespace-nowrap">
                                Balasan iklan
                            </button>

                            <button type="button" @click="setStatus('followup')"
                                :class="currentStatus === 'followup' ? 'bg-[#1877F2] text-white font-medium border-[#1877F2]' : 'bg-white dark:bg-[#2C2C2E] text-[#374151] dark:text-[#D1D5DB] border-[#E5E7EB] dark:border-white/10 hover:bg-[#F3F4F6]'"
                                class="px-2.5 py-1 rounded-[8px] text-[12px] border transition-colors whitespace-nowrap">
                                Tindak lanjut
                            </button>
                        </div>

                        {{-- Sliders icon filter --}}
                        <button type="button" @click="toggleStatusFilterDropdown()"
                            title="Filter Tambahan"
                            class="w-7 h-7 rounded-[8px] text-[#4B5563] dark:text-[#9CA3AF] hover:text-[#111827] dark:hover:text-white hover:bg-[#F3F4F6] dark:hover:bg-white/5 flex items-center justify-center shrink-0">
                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                        </button>
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
                                :class="selectedThread && selectedThread.id === t.id ? 'bg-[#E7F3FF]/70 dark:bg-[#1877F2]/15 border-l-4 border-[#1877F2]' : 'hover:bg-[#F9FAFB] dark:hover:bg-white/[0.03]'"
                                class="p-3.5 cursor-pointer transition-colors flex items-start gap-3 relative">

                                {{-- Avatar with Channel Badge --}}
                                <div class="relative shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E2E8F0] to-[#CBD5E1] dark:from-[#374151] dark:to-[#1F2937] flex items-center justify-center font-bold text-[13px] text-[#334155] dark:text-[#F1F5F9]">
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

                                    {{-- Badges row --}}
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-md font-medium"
                                            :class="getStatusBadgeClass(t.status)"
                                            x-text="formatStatusLabel(t.status)"></span>

                                        <template x-if="t.post_snippet">
                                            <span class="text-[10.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate max-w-[150px] flex items-center gap-0.5">
                                                <i data-lucide="message-square" class="w-2.5 h-2.5"></i>
                                                <span x-text="t.post_snippet"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

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

                    {{-- DETAIL HEADER --}}
                    <div class="px-5 py-3.5 border-b border-[#E5E7EB] dark:border-white/10 flex items-center justify-between gap-3 bg-white dark:bg-[#1C1C1E] shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E2E8F0] to-[#CBD5E1] dark:from-[#374151] dark:to-[#1F2937] flex items-center justify-center font-bold text-[14px] text-[#334155] dark:text-[#F1F5F9] shrink-0">
                                <span x-text="selectedThread ? getInitials(selectedThread.name) : ''"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-[14.5px] font-bold text-[#111827] dark:text-white truncate" x-text="selectedThread ? selectedThread.name : ''"></h2>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold text-white flex items-center gap-1"
                                        :class="selectedThread ? getChannelBadgeClass(selectedThread.channel) : ''">
                                        <i :data-lucide="selectedThread ? getChannelIcon(selectedThread.channel) : 'message-circle'" class="w-2.5 h-2.5"></i>
                                        <span x-text="selectedThread ? formatChannelName(selectedThread.channel) : ''"></span>
                                    </span>
                                </div>
                                <p class="text-[11.5px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5">
                                    ID: <span x-text="selectedThread ? selectedThread.customer_id : ''"></span>
                                    • Aktif: <span x-text="selectedThread ? selectedThread.date : ''"></span>
                                </p>
                            </div>
                        </div>

                        {{-- ACTIONS BAR --}}
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Status Switcher Dropdown --}}
                            <div class="relative" x-data="{ openStatus: false }">
                                <button type="button" @click="openStatus = !openStatus"
                                    class="h-8 px-2.5 rounded-[8px] border border-[#D1D5DB] dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-[12px] font-medium text-[#374151] dark:text-white hover:bg-[#F9FAFB] flex items-center gap-1.5 transition-colors">
                                    <span class="w-2 h-2 rounded-full" :class="selectedThread && selectedThread.is_unread ? 'bg-[#1877F2]' : 'bg-[#34C759]'"></span>
                                    <span x-text="selectedThread ? formatStatusLabel(selectedThread.status) : 'Status'"></span>
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

                    {{-- POST BANNER (IF APPLICABLE) --}}
                    <div x-show="selectedThread && selectedThread.post_snippet"
                        class="px-5 py-2.5 bg-[#F9FAFB] dark:bg-white/[0.02] border-b border-[#E5E7EB] dark:border-white/10 text-[12px] text-[#4B5563] dark:text-[#9CA3AF] flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#1877F2] shrink-0"></i>
                            <span class="font-medium text-[#111827] dark:text-white shrink-0">Komentar pada postingan:</span>
                            <span class="truncate italic" x-text="selectedThread ? selectedThread.post_snippet : ''"></span>
                        </div>
                    </div>

                    {{-- MESSAGES STREAM BODY --}}
                    <div id="messagesContainer" class="flex-1 p-5 overflow-y-auto space-y-4 min-h-0 bg-[#F9FAFB]/50 dark:bg-black/10">

                        <div class="text-center my-2">
                            <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/10 text-[#6B7280] dark:text-[#9CA3AF]"
                                x-text="selectedThread ? selectedThread.date : 'Hari ini'"></span>
                        </div>

                        <template x-if="selectedThread && selectedThread.messages">
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
                                                        <span class="text-[11px] font-semibold text-[#1877F2] block mb-0.5" x-text="msg.from_name"></span>
                                                        <p class="text-[13px] text-[#1F2937] dark:text-[#F3F4F6] whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
                                                    </div>
                                                    <span class="text-[10px] text-[#9CA3AF] px-1 block" x-text="msg.time"></span>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Outbound Admin/Official Reply --}}
                                        <template x-if="msg.is_from_page">
                                            <div class="flex items-end justify-end gap-2.5 ml-auto max-w-[85%] sm:max-w-[70%]">
                                                <div class="space-y-1 text-right">
                                                    <div class="bg-[#1877F2] text-white p-3.5 rounded-[16px] rounded-br-[4px] shadow-xs text-left">
                                                        <div class="flex items-center gap-1.5 mb-1 text-[11px] font-semibold text-blue-100">
                                                            <i data-lucide="badge-check" class="w-3.5 h-3.5 text-white"></i>
                                                            <span>Cooca Official</span>
                                                        </div>
                                                        <p class="text-[13px] text-white whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
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

                    {{-- REPLY CONSOLE (BOTTOM COMPOSER) --}}
                    <div class="p-3.5 sm:p-4 bg-white dark:bg-[#1C1C1E] border-t border-[#E5E7EB] dark:border-white/10 shrink-0 space-y-2.5">
                        {{-- Quick Responses Pills --}}
                        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1">
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
                            <button type="button" @click="insertTemplate('Halo Kak, untuk pendaftaran paket Cooca Pro dan Enterprise silakan kunjungi menu Berlangganan di https://cooca.id/pricing')"
                                class="px-2.5 py-1 rounded-full text-[11px] bg-[#F3F4F6] dark:bg-white/5 hover:bg-[#E5E7EB] text-[#374151] dark:text-[#D1D5DB] transition-colors whitespace-nowrap">
                                Info Langganan
                            </button>
                        </div>

                        {{-- Textarea & Send button --}}
                        <div class="flex items-end gap-2">
                            <div class="flex-1 min-w-0 relative">
                                <textarea x-model="replyText" @keydown.enter.prevent="if(!$event.shiftKey) sendReply()"
                                    rows="2"
                                    placeholder="Tulis balasan resmi sebagai Cooca Official... (Tekan Enter untuk kirim, Shift+Enter untuk baris baru)"
                                    class="w-full p-2.5 sm:p-3 rounded-[12px] bg-[#F9FAFB] dark:bg-[#2C2C2E] border border-[#D1D5DB] dark:border-white/15 text-[13px] text-[#111827] dark:text-white placeholder-[#9CA3AF] focus:outline-hidden focus:border-[#1877F2] resize-none transition-colors"></textarea>
                            </div>

                            <button type="button" @click="sendReply()" :disabled="!replyText.trim() || isSending"
                                class="h-[46px] px-4 sm:px-5 rounded-[12px] bg-[#1877F2] hover:bg-[#166FE5] disabled:opacity-50 disabled:cursor-not-allowed text-white text-[13px] font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all shrink-0">
                                <i data-lucide="send" class="w-4 h-4" :class="isSending ? 'animate-pulse' : ''"></i>
                                <span class="hidden sm:inline" x-text="isSending ? 'Mengirim...' : 'Kirim'"></span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
function adminInboxHub() {
    return {
        allThreads: @json($allThreads ?? []),
        displayedThreads: @json($threads ?? []),
        selectedThread: null,
        currentChannel: @json($channel ?? 'all'),
        currentStatus: @json($status ?? 'all'),
        searchKeyword: @json($search ?? ''),
        replyText: '',
        isSending: false,
        isSyncing: false,

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
            this.searchKeyword = '';
            this.filterThreads();
        },

        filterThreads() {
            const channel = this.currentChannel;
            const status = this.currentStatus;
            const search = (this.searchKeyword || '').toLowerCase().trim();

            this.displayedThreads = this.allThreads.filter(t => {
                // Channel check
                let matchChannel = true;
                if (channel !== 'all') {
                    if (channel === 'messenger') {
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

                // Search check
                let matchSearch = true;
                if (search) {
                    const name = (t.name || '').toLowerCase();
                    const lastMsg = (t.last_message || '').toLowerCase();
                    matchSearch = name.includes(search) || lastMsg.includes(search);
                }

                return matchChannel && matchStatus && matchSearch;
            });

            // Adjust selected thread
            if (this.displayedThreads.length > 0) {
                if (!this.selectedThread || !this.displayedThreads.some(t => t.id === this.selectedThread.id)) {
                    this.selectedThread = this.displayedThreads[0];
                }
            } else {
                this.selectedThread = null;
            }

            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        selectThread(thread) {
            this.selectedThread = thread;
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

        async sendReply() {
            if (!this.selectedThread || !this.replyText.trim() || this.isSending) return;

            const threadId = this.selectedThread.id;
            const message = this.replyText.trim();
            this.isSending = true;

            try {
                const response = await fetch(`/admin/inbox/reply/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Optimistic append
                    const newReply = data.reply || {
                        id: Date.now(),
                        message: message,
                        created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        sender: 'Cooca Official',
                    };

                    this.selectedThread.messages.push({
                        id: newReply.id,
                        from_name: 'Cooca Official',
                        message: newReply.message,
                        is_from_page: true,
                        time: newReply.created_at,
                        date: 'Hari ini',
                    });

                    this.selectedThread.is_unread = false;
                    this.selectedThread.status = 'read';

                    this.replyText = '';
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
                    // Success notification
                    alert(data.message || 'Sinkronisasi berhasil.');
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isSyncing = false;
            }
        }
    }
}
</script>
@endpush
@endsection
