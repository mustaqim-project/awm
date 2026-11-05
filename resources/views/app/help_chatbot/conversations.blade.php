@extends('layouts.app', ['title' => 'Riwayat Percakapan Asisten AI - COOCA'])

@section('content')
<div x-data="coocaConversationHistoryPage()" class="space-y-6 pb-28 sm:pb-32 lg:pb-10">

    {{-- Bento Apple HIG Header --}}
    <x-module-header
        title="Riwayat Asisten AI & Panduan"
        subtitle="Telusuri, lanjutkan, atau kelola sesi tanya-jawab panduan modul dan analisis kecerdasan bisnis Anda."
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Asisten AI', 'url' => null],
            ['label' => 'Riwayat Percakapan', 'url' => null]
        ]">
        <x-slot:actions>
            <button type="button"
                    @click="openNewChat()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-tr from-[#007AFF] via-[#5856D6] to-[#AF52DE] text-white text-xs font-semibold shadow-[0_4px_16px_rgba(0,122,255,0.25)] hover:shadow-[0_6px_20px_rgba(0,122,255,0.35)] hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Percakapan Baru</span>
            </button>
        </x-slot:actions>
    </x-module-header>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- 1. BENTO STAT METRICS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
        {{-- Total Sesi --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Total Sesi Percakapan</p>
                <h3 class="text-2xl font-extrabold text-black dark:text-white mt-1">{{ number_format($stats['total'] ?? 0) }}</h3>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Tersimpan dalam database</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
        </div>

        {{-- Help Center (Panduan) --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Panduan Fitur (How-To)</p>
                <h3 class="text-2xl font-extrabold text-black dark:text-white mt-1">{{ number_format($stats['help_center'] ?? 0) }}</h3>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Bebas Form &bull; Zero Data Leakage</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>

        {{-- Business Assistant (Analitik Data) --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Business Assistant (BI)</p>
                <h3 class="text-2xl font-extrabold text-black dark:text-white mt-1">{{ number_format($stats['business_assistant'] ?? 0) }}</h3>
                <p class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5 font-medium">Omzet, Stok &bull; Strict RBAC</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
        </div>
    </div>

    {{-- 2. FILTER & SEARCH CONTROL BAR --}}
    <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        {{-- Segmented Mode Tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl bg-black/[0.04] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 select-none overflow-x-auto">
            <a href="{{ route('help-chatbot.conversations', array_filter(['search' => $search])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ empty($activeType) ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Semua Sesi
            </a>
            <a href="{{ route('help-chatbot.conversations', array_filter(['type' => 'help_center', 'search' => $search])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeType === 'help_center' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Panduan Fitur
            </a>
            <a href="{{ route('help-chatbot.conversations', array_filter(['type' => 'business_assistant', 'search' => $search])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeType === 'business_assistant' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Business Assistant
            </a>
        </div>

        {{-- Search Input Form --}}
        <form method="GET" action="{{ route('help-chatbot.conversations') }}" class="relative w-full sm:w-72">
            @if($activeType)
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Cari topik percakapan..."
                   class="w-full pl-9 pr-8 py-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:ring-2 focus:ring-[#007AFF] transition">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            @if($search !== '')
                <a href="{{ route('help-chatbot.conversations', array_filter(['type' => $activeType])) }}"
                   class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs"
                   title="Reset pencarian">
                    &times;
                </a>
            @endif
        </form>
    </div>

    {{-- 3. CONVERSATION TABLE & CARD CONTAINER --}}
    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs overflow-hidden">
        @if($conversations->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/40 dark:text-white/40">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-black dark:text-white">Belum Ada Riwayat Percakapan</h4>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">
                    {{ $search ? 'Tidak ada percakapan yang cocok dengan kata kunci pencarian Anda.' : 'Mulai tanyakan cara menggunakan fitur COOCA atau analisis data bisnis Anda kapan saja.' }}
                </p>
                <div class="pt-2">
                    <button type="button"
                            @click="openNewChat()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#007AFF] text-white text-xs font-semibold shadow-xs hover:bg-[#0071E3] transition cursor-pointer">
                        Mulai Tanya Asisten AI
                    </button>
                </div>
            </div>
        @else
            {{-- Mobile Card Stack View --}}
            <div class="block sm:hidden divide-y divide-black/5 dark:divide-white/5">
                @foreach($conversations as $item)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-black dark:text-white block leading-snug">{{ $item->title ?: 'Percakapan Tanpa Judul' }}</span>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($item->type === 'business_assistant')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            Business Assistant
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-[#007AFF] border border-blue-500/20">
                                            Panduan Fitur
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-black/40 dark:text-white/40">
                                        {{ $item->messages_count ?? 0 }} pesan
                                    </span>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono text-black/40 dark:text-white/40 shrink-0">
                                {{ $item->updated_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button"
                                    @click="resumeChat('{{ $item->id }}', '{{ $item->type }}')"
                                    class="flex-1 py-1.5 px-3 rounded-lg bg-[#007AFF] text-white text-[11px] font-semibold text-center hover:bg-[#0071E3] transition active:scale-95 cursor-pointer">
                                Lanjutkan Chat
                            </button>
                            <button type="button"
                                    @click="previewHistory('{{ $item->id }}')"
                                    class="py-1.5 px-3 rounded-lg bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 text-[11px] font-semibold hover:bg-black/10 dark:hover:bg-white/15 transition active:scale-95 cursor-pointer">
                                Isi Pesan
                            </button>
                            <form method="POST"
                                  action="{{ route('help-chatbot.conversations.delete', $item->id) }}"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi percakapan ini?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Hapus percakapan"
                                        class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-500/10 transition active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table View --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs text-black/80 dark:text-white/80">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/10 dark:border-white/10 text-[10px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Topik Percakapan</th>
                            <th class="py-3 px-4">Kategori Layanan</th>
                            <th class="py-3 px-4 text-center">Jumlah Pesan</th>
                            <th class="py-3 px-4">Terakhir Diperbarui</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($conversations as $item)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                                <td class="py-3.5 px-4 font-semibold text-black dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full {{ $item->type === 'business_assistant' ? 'bg-purple-500' : 'bg-[#007AFF]' }}"></div>
                                        <span class="truncate max-w-xs md:max-w-md">{{ $item->title ?: 'Percakapan Tanpa Judul' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($item->type === 'business_assistant')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Business Assistant
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-[#007AFF] border border-blue-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                            Panduan Fitur
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono">
                                    <span class="px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 font-semibold text-[11px]">
                                        {{ $item->messages_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-black/50 dark:text-white/50 text-[11px]">
                                    {{ $item->updated_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                @click="resumeChat('{{ $item->id }}', '{{ $item->type }}')"
                                                class="px-2.5 py-1 rounded-lg bg-[#007AFF] text-white text-[11px] font-semibold hover:bg-[#0071E3] transition active:scale-95 cursor-pointer">
                                            Lanjutkan Chat
                                        </button>
                                        <button type="button"
                                                @click="previewHistory('{{ $item->id }}')"
                                                class="px-2.5 py-1 rounded-lg bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 text-[11px] font-semibold hover:bg-black/10 dark:hover:bg-white/15 transition active:scale-95 cursor-pointer">
                                            Lihat Isi
                                        </button>
                                        <form method="POST"
                                              action="{{ route('help-chatbot.conversations.delete', $item->id) }}"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi percakapan ini?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Hapus percakapan"
                                                    class="p-1 rounded-lg text-rose-500 hover:bg-rose-500/10 transition active:scale-95 cursor-pointer">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($conversations->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $conversations->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- 4. MODAL PREVIEW DETAIL PERCAKAPAN --}}
    <div x-show="previewModalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">

        <div @click.away="previewModalOpen = false"
             class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-3xl shadow-2xl border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[85vh]">

            {{-- Modal Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-[#007AFF] via-[#5856D6] to-[#AF52DE] text-white flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold truncate" x-text="previewData.title || 'Detail Percakapan'"></h3>
                    <p class="text-[11px] text-white/80" x-text="previewData.type === 'business_assistant' ? 'Mode: Business Assistant' : 'Mode: Panduan Fitur'"></p>
                </div>
                <button type="button"
                        @click="previewModalOpen = false"
                        class="w-7 h-7 rounded-lg hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                    &times;
                </button>
            </div>

            {{-- Modal Body: Message History Stream --}}
            <div class="flex-1 p-4 overflow-y-auto space-y-3 text-xs">
                <template x-if="previewLoading">
                    <div class="py-12 text-center text-black/50 dark:text-white/50">
                        <div class="w-6 h-6 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                        <span>Memuat isi percakapan...</span>
                    </div>
                </template>

                <template x-if="!previewLoading && previewMessages.length === 0">
                    <div class="py-12 text-center text-black/40 dark:text-white/40">
                        Tidak ada riwayat pesan dalam sesi ini.
                    </div>
                </template>

                <template x-for="(msg, idx) in previewMessages" :key="idx">
                    <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.sender === 'user'
                                ? 'bg-[#007AFF] text-white rounded-2xl rounded-tr-xs max-w-[85%] px-3.5 py-2.5 shadow-xs'
                                : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white rounded-2xl rounded-tl-xs max-w-[90%] px-3.5 py-3 border border-black/5 dark:border-white/10 shadow-xs space-y-1.5'">
                            <div class="text-[10px] font-bold opacity-75" x-text="msg.sender === 'user' ? 'Anda' : 'Asisten AI'"></div>
                            <div class="leading-relaxed whitespace-pre-line" x-text="msg.content"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="p-3 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                <button type="button"
                        @click="previewModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer">
                    Tutup
                </button>
                <button type="button"
                        @click="resumeChat(previewData.id, previewData.type)"
                        class="px-4 py-2 rounded-xl bg-[#007AFF] text-white text-xs font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                    Buka di Widget Chat &rarr;
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function coocaConversationHistoryPage() {
    return {
        previewModalOpen: false,
        previewLoading: false,
        previewData: {},
        previewMessages: [],

        openNewChat() {
            window.dispatchEvent(new CustomEvent('cooca-open-chatbot', {
                detail: { newSession: true }
            }));
        },

        resumeChat(conversationId, type) {
            this.previewModalOpen = false;
            window.dispatchEvent(new CustomEvent('cooca-open-chatbot', {
                detail: {
                    conversationId: conversationId,
                    mode: type
                }
            }));
        },

        async previewHistory(conversationId) {
            this.previewModalOpen = true;
            this.previewLoading = true;
            this.previewData = {};
            this.previewMessages = [];

            try {
                const res = await fetch(`/help-chatbot/history/${conversationId}`);
                if (res.ok) {
                    const data = await res.json();
                    this.previewData = data.conversation || {};
                    this.previewMessages = data.messages || [];
                }
            } catch (e) {
                console.error('Failed to load history', e);
            } finally {
                this.previewLoading = false;
            }
        }
    };
}
</script>
@endsection

