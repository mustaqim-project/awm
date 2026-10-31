@extends('layouts.app', [
    'title' => __('docs.buku_panduan_dokumentasi_penggunaan'),
    'headerTitle' => 'Pusat Dokumentasi & Panduan Cooca',
    'headerSubtitle' => 'Panduan terpadu pengoperasian seluruh modul ERP, Kasir POS, dan administrasi bisnis Cooca'
])

@section('content')
<div class="max-w-[1400px] mx-auto space-y-6 pb-28 lg:pb-16"
    x-data="{
        searchQuery: '',
        activeTab: 'all',
        expandedFaq: null,
        copiedShortcut: null,
        printGuide() {
            window.print();
        },
        copyText(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                this.copiedShortcut = text;
                setTimeout(() => { this.copiedShortcut = null; }, 2000);
            }
        },
        matchesSearch(text) {
            if (!this.searchQuery.trim()) return true;
            return text.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
        }
    }">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER & QUICK SEARCH HERO         -->
    <!-- ===================================================== -->
    <header class="relative overflow-hidden rounded-[20px] backdrop-blur-xl bg-gradient-to-br from-white/90 via-white/80 to-white/60 dark:from-[#1C1C1E]/90 dark:via-[#1C1C1E]/80 dark:to-[#1C1C1E]/60 border border-black/5 dark:border-white/10 p-5 sm:p-7 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
        <!-- Subtle Glow Aesthetic -->
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#007AFF]/10 dark:bg-[#0A84FF]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-[#34C759]/10 dark:bg-[#30D158]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-1.5 max-w-2xl">
                <!-- Minimal Breadcrumb -->
                <nav class="flex items-center gap-1.5 text-[11px] font-medium text-black/50 dark:text-white/50">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black/80 dark:text-white/80 font-semibold">Pusat Bantuan &amp; Pengetahuan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Dokumentasi Penggunaan</span>
                </nav>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shadow-[0_2px_8px_rgba(0,122,255,0.3)]">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </span>
                    <span>{{ __('docs.buku_panduan_dokumentasi_cooca') }}</span>
                </h1>
                <p class="text-[13px] sm:text-[14px] text-black/60 dark:text-white/60 leading-relaxed">
                    Panduan praktis pengoperasian seluruh ekosistem Cooca: dari transaksi kasir POS, kalkulasi HPP resep otomatis, manajemen inventori multi-gudang, hingga pembukuan akuntansi dan penggajian.
                </p>
            </div>

            <!-- Action Buttons: Print PDF & Lapor Kendala -->
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <button type="button" @click="printGuide()"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/75 dark:text-white/75 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95 cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>{{ __('docs.cetak_simpan_pdf') }}</span>
                </button>
                @if(\App\Support\Context::isOwner())
                    <a href="{{ route('feedback.bugs.create') }}"
                        class="h-9 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                        <i data-lucide="message-square-plus" class="w-3.5 h-3.5"></i>
                        <span>{{ __('docs.butuh_bantuan_teknis') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Live Instant Search Bar -->
        <div class="relative mt-6">
            <div class="relative flex items-center">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-4 pointer-events-none"></i>
                <input type="text" x-model="searchQuery"
                    placeholder="{{ __('docs.cari_topik_panduan_contoh_tutup_shift') }}"
                    class="w-full h-11 pl-11 pr-24 rounded-xl bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] sm:text-[14px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-[#007AFF] focus:bg-white dark:focus:bg-[#1C1C1E] transition">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                    class="absolute right-3 p-1 rounded-md text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white text-xs font-semibold">
                    Reset
                </button>
            </div>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. MAIN CONTENT LAYOUT (TWO COLUMNS)                 -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- 3A. LEFT SIDEBAR: QUICK TOC NAVIGATOR (DESKTOP STICKY - MATCH SIDEBAR STYLE) -->
        <aside class="hidden lg:block lg:col-span-3 sticky top-24 space-y-3">
            <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-3.5 space-y-3 shadow-2xs max-h-[calc(100vh-120px)] overflow-y-auto scrollbar-none">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10 px-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="book-marked" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="text-[11px] font-bold tracking-wider uppercase text-black/60 dark:text-white/60">Daftar Isi Panduan</span>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#007AFF]/10 text-[#007AFF]">5 Klaster</span>
                </div>

                <nav class="space-y-3 text-[12.5px]">

                    <!-- KLASTER 1: CORE BUSINESS -->
                    <div class="space-y-1">
                        <div class="px-2 pt-1 pb-0.5 text-[10px] font-extrabold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                            Core Business
                        </div>

                        <!-- Onboarding & Setup Awal -->
                        <a href="#sec-onboarding" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-amber-500/15 text-amber-500 dark:bg-amber-400/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <i data-lucide="rocket" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Onboarding &amp; Setup Awal</span>
                        </a>

                        <!-- Kasir POS & Pembayaran -->
                        <a href="#sec-pos" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.kasir_pos_pembayaran') }}</span>
                        </a>

                        <!-- Penjualan B2B & Invoice -->
                        <a href="#sec-sales-b2b" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-blue-500/15 text-blue-600 dark:bg-blue-400/20 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Penjualan B2B &amp; Invoice</span>
                        </a>

                        <!-- 3D Virtual Office & Asisten AI -->
                        <div class="space-y-0.5">
                            <a href="#sec-ai" @click="if(activeTab !== 'all') activeTab = 'all'"
                                class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-bold text-black dark:text-white hover:bg-[#AF52DE]/10 dark:hover:bg-[#AF52DE]/15 hover:text-[#AF52DE] transition-all group">
                                <span class="w-6 h-6 rounded-[7px] bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                                    <i data-lucide="box" class="w-3.5 h-3.5"></i>
                                </span>
                                <div class="flex items-center justify-between flex-1 min-w-0">
                                    <span class="truncate tracking-tight text-[12px]">{{ __('docs.virtual_office_ai') }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">{{ __('docs.twenty_businesses') }}</span>
                                </div>
                            </a>

                            <!-- AI Subtree Items (Sidebar Style) -->
                            <div class="ml-5 pl-3 py-1 space-y-1 border-l border-black/10 dark:border-white/10 text-[11px]">
                                <a href="#sec-ai" @click="if(activeTab !== 'all') activeTab = 'all'"
                                    class="flex items-center gap-2 px-2 py-0.5 rounded-[6px] text-black/65 dark:text-white/65 hover:text-[#AF52DE] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="truncate">{{ __('docs.tanya_ai_pos_kasir') }}</span>
                                </a>
                                <a href="#sec-ai" @click="if(activeTab !== 'all') activeTab = 'all'"
                                    class="flex items-center gap-2 px-2 py-0.5 rounded-[6px] text-black/65 dark:text-white/65 hover:text-[#AF52DE] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 shrink-0"></span>
                                    <span class="truncate">Lobi 3 Kantor Spesialis</span>
                                </a>
                                <a href="#sec-ai" @click="if(activeTab !== 'all') activeTab = 'all'"
                                    class="flex items-center gap-2 px-2 py-0.5 rounded-[6px] text-black/65 dark:text-white/65 hover:text-[#AF52DE] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span class="truncate">Action Center &amp; Proposal</span>
                                </a>
                                <a href="#sec-ai" @click="if(activeTab !== 'all') activeTab = 'all'"
                                    class="flex items-center gap-2 px-2 py-0.5 rounded-[6px] text-black/65 dark:text-white/65 hover:text-[#AF52DE] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 shrink-0"></span>
                                    <span class="truncate font-semibold text-[#AF52DE] dark:text-[#BF5AF2]">{{ __('docs.matriks_20_industri_umkm') }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Komunitas & Event UMKM -->
                        <a href="#sec-community" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-amber-500/15 text-amber-600 dark:bg-amber-400/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <i data-lucide="messages-square" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.komunitas_event_umkm') }}</span>
                        </a>
                    </div>

                    <!-- KLASTER 2: INVENTORY & PROCUREMENT -->
                    <div class="space-y-1 pt-1 border-t border-black/5 dark:border-white/5">
                        <div class="px-2 pt-1.5 pb-0.5 text-[10px] font-extrabold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                            Inventory &amp; Supply Chain
                        </div>

                        <!-- Inventori & Resep HPP -->
                        <a href="#sec-inventory" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.stok_resep_multi_gudang') }}</span>
                        </a>

                        <!-- Pembelian & Supplier (PO) -->
                        <a href="#sec-procurement" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-[#30B0C7]/15 text-[#30B0C7] dark:bg-[#40C8E0]/20 dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Pengadaan &amp; Supplier (PO)</span>
                        </a>
                    </div>

                    <!-- KLASTER 3: FINANCE & ACCOUNTING -->
                    <div class="space-y-1 pt-1 border-t border-black/5 dark:border-white/5">
                        <div class="px-2 pt-1.5 pb-0.5 text-[10px] font-extrabold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                            Finance &amp; Accounting
                        </div>

                        <!-- Keuangan & Akuntansi -->
                        <a href="#sec-finance" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158] flex items-center justify-center shrink-0">
                                <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Kas, Bank &amp; Akuntansi</span>
                        </a>

                        <!-- Otorisasi MAR & Anti-Fraud -->
                        <a href="#sec-security" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-emerald-600/15 text-emerald-600 dark:bg-emerald-400/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.otorisasi_mar_anti_fraud') }}</span>
                        </a>
                    </div>

                    <!-- KLASTER 4: HRM & CHANNELS -->
                    <div class="space-y-1 pt-1 border-t border-black/5 dark:border-white/5">
                        <div class="px-2 pt-1.5 pb-0.5 text-[10px] font-extrabold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                            Human Resources &amp; CRM
                        </div>

                        <!-- Karyawan & Penggajian -->
                        <a href="#sec-hrm" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-sky-500/15 text-sky-500 dark:bg-sky-400/20 dark:text-sky-400 flex items-center justify-center shrink-0">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.karyawan_penggajian') }}</span>
                        </a>

                        <!-- Loyalitas & WhatsApp Gateway -->
                        <a href="#sec-crm" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-pink-500/15 text-pink-500 dark:bg-pink-400/20 dark:text-pink-400 flex items-center justify-center shrink-0">
                                <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Loyalitas &amp; WhatsApp Gateway</span>
                        </a>
                    </div>

                    <!-- KLASTER 5: SYSTEM & SETTINGS -->
                    <div class="space-y-1 pt-1 border-t border-black/5 dark:border-white/5">
                        <div class="px-2 pt-1.5 pb-0.5 text-[10px] font-extrabold tracking-wider uppercase text-black/40 dark:text-white/40 select-none">
                            System &amp; Panduan
                        </div>

                        <!-- Langganan & Token AI -->
                        <a href="#sec-billing" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-amber-500/15 text-amber-500 dark:bg-amber-400/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">{{ __('docs.langganan_kuota_token') }}</span>
                        </a>

                        <!-- Pintasan Keyboard -->
                        <a href="#sec-shortcuts" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                                <i data-lucide="command" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Pintasan Keyboard</span>
                        </a>

                        <!-- Tanya Jawab (FAQ) -->
                        <a href="#sec-faq" @click="if(activeTab !== 'all') activeTab = 'all'"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-[10px] font-semibold text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-[#007AFF] transition-all group">
                            <span class="w-6 h-6 rounded-[7px] bg-rose-500/15 text-rose-500 dark:bg-rose-400/20 dark:text-rose-400 flex items-center justify-center shrink-0">
                                <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium text-[12px]">Tanya Jawab (FAQ)</span>
                        </a>
                    </div>

                </nav>
            </div>

            <!-- Quick Info Card -->
            <div class="rounded-2xl p-4 bg-gradient-to-br from-[#007AFF]/10 to-[#5856D6]/10 border border-[#007AFF]/20 text-xs space-y-2">
                <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                    <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Pencarian Cepat Spotlight</span>
                </div>
                <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tekan tombol <kbd class="px-1.5 py-0.5 rounded bg-black/10 dark:bg-white/10 font-mono text-[10px] font-bold">Ctrl + K</kbd> atau <kbd class="px-1.5 py-0.5 rounded bg-black/10 dark:bg-white/10 font-mono text-[10px] font-bold">Cmd + K</kbd> kapan saja untuk membuka menu instan.
                </p>
            </div>
        </aside>

        <!-- 3B. RIGHT MAIN COLUMN: COMPREHENSIVE DOCUMENTATION CONTENT -->
        <main class="lg:col-span-9 space-y-8">

            <!-- ===================================================== -->
            <!-- SECTION 1: ONBOARDING & SETUP AWAL (GETTING STARTED)  -->
            <!-- ===================================================== -->
            <section id="sec-onboarding" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'getting_started') && matchesSearch('onboarding setup toko profil cabang pajak pengguna karyawan')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-bold text-sm">
                            1
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.onboarding_setup_awal_usaha') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Langkah wajib 5 menit sebelum memulai penjualan</p>
                        </div>
                    </div>
                    <a href="{{ route('settings.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>{{ __('docs.buka_pengaturan') }}</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Step 1.1 -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-[11px]">1.1</span>
                            <span>Profil Usaha &amp; Identitas Struk</span>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                            Buka menu <strong>Pengaturan &rarr; Profil Bisnis</strong>. Isi nama toko, logo, alamat lengkap, dan nomor kontak WhatsApp. Informasi ini akan otomatis tercetak pada struk kasir dan dokumen faktur resmi.
                        </p>
                    </div>

                    <!-- Step 1.2 -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-[11px]">1.2</span>
                            <span>Pajak (PB1/PPN) &amp; Service Charge</span>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                            Tentukan apakah harga jual sudah termasuk pajak (*Tax Inclusive*) atau ditambahkan di kasir (*Tax Exclusive*). Aktifkan juga biaya layanan (*Service Charge*) khusus untuk resto/cafe bila diperlukan.
                        </p>
                    </div>

                    <!-- Step 1.3 -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-[11px]">1.3</span>
                            <span>Multi-Gudang &amp; Cabang Awal</span>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                            Buka <strong>Inventori &rarr; Gudang</strong>. Buat minimal satu gudang utama (contoh: *Gudang Pusat* atau *Toko Utama*). Setiap mutasi stok kasir dan pembelian akan terhubung ke gudang ini.
                        </p>
                    </div>

                    <!-- Step 1.4 -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-[11px]">1.4</span>
                            <span>Akun Staf &amp; Hak Akses (RBAC)</span>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                            Buka <strong>Pengaturan &rarr; Peran &amp; Hak Akses</strong>. Daftarkan akun kasir, admin gudang, atau staf dapur dengan membatasi akses sensitif (misal: kasir tidak bisa melihat laba rugi atau menghapus riwayat audit).
                        </p>
                    </div>
                </div>

                <!-- Pro Tips Callout -->
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 flex items-start gap-3 text-xs text-amber-900 dark:text-amber-200">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Tips Master Data:</strong>
                        <span> Jika Anda memiliki ratusan produk yang sudah tercatat di Excel, gunakan fitur <strong>Import Excel</strong> di sidebar untuk mengunggah seluruh produk dan stok awal sekaligus dalam hitungan detik.</span>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 2: KASIR MODERN (POS) & PRINTER BLUETOOTH    -->
            <!-- ===================================================== -->
            <section id="sec-pos" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'pos') && matchesSearch('kasir pos retail f&b dine in meja split bill qris printer shift kas modal tutup')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold text-sm">
                            2
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.kasir_modern_pos_pembayaran_shift') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Pengoperasian harian kasir untuk toko retail maupun resto dine-in</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('pos.terminal'))
                        <a href="{{ route('pos.terminal') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_mesin_kasir') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <!-- POS Step-by-Step Flow Card -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('docs.alur_standar_kasir_sehari_hari') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-[#007AFF]">Langkah 1: Buka Shift</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Kasir memasukkan modal awal uang tunai laci (*Starting Cash*) saat memulai jam kerja.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-[#007AFF]">Langkah 2: Input Pesanan</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Scan barcode produk atau pilih menu. Untuk resto, tentukan nomor meja dan modifier produk.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-[#007AFF]">Langkah 3: Pembayaran</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Pilih metode bayar: Tunai (Quick Cash), QRIS Statis/Dinamis, Transfer Bank, atau Split Bill.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-[#007AFF]">Langkah 4: Tutup Shift (Z)</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Hitung uang kas fisik, sistem mencocokkan selisih kas, lalu cetak laporan Z-Report kasir.</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2 text-xs text-black/70 dark:text-white/70">
                        <h4 class="font-bold text-black dark:text-white text-sm">{{ __('docs.fitur_kasir_unggulan_di_cooca') }}</h4>
                        <ul class="space-y-2 list-disc list-inside">
                            <li><strong>Split Bill (Bagi Tagihan):</strong> Pelanggan dalam satu meja dapat membayar terpisah per orang atau membagi total tagihan menjadi beberapa pembayaran.</li>
                            <li><strong>Kitchen Display &amp; Tiket Dapur (KDS):</strong> Setelah pesanan disimpan, tiket makanan/minuman otomatis terkirim ke printer dapur dan layar barista.</li>
                            <li><strong>QRIS Dinamis Otomatis:</strong> Nilai transaksi otomatis tertanam pada barcode QRIS pelanggan tanpa perlu kasir mengetik ulang nominal di EDC.</li>
                            <li><strong>Konfigurasi Printer Bluetooth / USB:</strong> Buka <strong>Pengaturan Kasir &rarr; Printer</strong>. Cooca mendukung printer thermal ukuran 58mm dan 80mm standar industri.</li>
                        </ul>
                    </div>
                </div>

                <!-- Anti-Fraud Warning Box -->
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 flex items-start gap-3 text-xs text-rose-900 dark:text-rose-200">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Keamanan &amp; Deteksi Fraud Kasir:</strong>
                        <span> Setiap tindakan void transaksi, pembatalan pesanan yang sudah tercetak, atau pemberian diskon di atas batas normal akan tercatat di <strong>Audit Anti-Fraud</strong> dan mewajibkan otorisasi PIN Supervisor/Owner.</span>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 3: INVENTORI, RESEP BOM & HPP OTOMATIS        -->
            <!-- ===================================================== -->
            <section id="sec-inventory" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'inventory') && matchesSearch('inventori resep bom hpp bahan baku stock opname mutasi stok barang minimum')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center font-bold text-sm">
                            3
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">Inventori, Resep &amp; Kalkulasi HPP</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Pelacakan stok bahan baku dan perhitungan Harga Pokok Penjualan otomatis</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('products.view'))
                        <a href="{{ route('products.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_katalog_produk_resep') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold text-black dark:text-white">Bagaimana Resep (Bill of Materials) Bekerja?</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                            Khusus bisnis kuliner (F&amp;B) atau manufaktur perakitan, Cooca mengaitkan produk jadi dengan bahan baku penyusunnya. Saat kasir menjual 1 porsi *"Kopi Susu Gula Aren"*, Cooca secara otomatis memotong stok:
                        </p>
                    </div>

                    <!-- Flow Diagram Card -->
                    <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 text-xs font-mono space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2 text-black dark:text-white font-bold">
                            <span>1 Cup Kopi Susu Gula Aren Terjual</span>
                            <span class="text-emerald-500">&rarr; Potong Stok Bahan Otomatis:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-black/70 dark:text-white/70">
                            <div class="p-2 rounded bg-white dark:bg-black/20 border border-black/5 dark:border-white/5">
                                • Biji Kopi Espresso: 18 Gram
                            </div>
                            <div class="p-2 rounded bg-white dark:bg-black/20 border border-black/5 dark:border-white/5">
                                • Susu Fresh Milk: 120 mL
                            </div>
                            <div class="p-2 rounded bg-white dark:bg-black/20 border border-black/5 dark:border-white/5">
                                • Gula Aren Cair: 25 mL
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 text-xs">
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-black dark:text-white">Kalkulasi HPP Real-Time</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">
                                Jika harga beli susu naik dari supplier, HPP kopi susu otomatis ter-update sehingga margin keuntungan bersih di dashboard selalu presisi.
                            </p>
                        </div>
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-black dark:text-white">Stock Opname &amp; Kartu Stok</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">
                                Catat stok fisik berkala melalui menu <strong>Stock Opname</strong>. Selisih stok dicatat ke akun penyesuaian inventori secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 4: PENGADAAN & PEMBELIAN (PROCUREMENT)        -->
            <!-- ===================================================== -->
            <section id="sec-procurement" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'sales_purchasing') && matchesSearch('pembelian procurement po purchase order supplier grn penerimaan barang tagihan ap')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-500 flex items-center justify-center font-bold text-sm">
                            4
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.pengadaan_pembelian_po_ke_supplier') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Alur standar pengadaan barang, penerimaan surat jalan, dan jatuh tempo hutang dagang</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('purchasing.view'))
                        <a href="{{ route('purchase-orders.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_purchase_orders') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-indigo-500">1. Terbitkan PO</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Pilih supplier, tentukan kuantiti barang dan harga satuan yang disepakati.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-indigo-500">2. Terima Barang (GRN)</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Saat barang tiba di gudang, admin memverifikasi fisik barang dan stok gudang otomatis bertambah.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                            <span class="font-bold text-indigo-500">3. Faktur Beli &amp; Hutang</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Sistem mencatat kewajiban hutang dagang (AP) lengkap dengan jadwal tanggal jatuh tempo.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 5: PENJUALAN B2B, QUOTATION & INVOICE        -->
            <!-- ===================================================== -->
            <section id="sec-sales-b2b" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'sales_purchasing') && matchesSearch('penjualan b2b quotation so sales order surat jalan delivery order invoice piutang ar')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-500 flex items-center justify-center font-bold text-sm">
                            5
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">Penjualan B2B, Penawaran &amp; Invoice</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Transaksi grosir partai besar, pesanan tender, dan manajemen penagihan piutang</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('sales.view'))
                        <a href="{{ route('sales.orders.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_pesanan_penjualan') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <p class="leading-relaxed">
                        Untuk transaksi korporat atau pelanggan grosir, Cooca menyediakan siklus penjualan terintegrasi:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-black dark:text-white">Quotation &rarr; Sales Order (SO)</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Kirim surat penawaran resmi berformat PDF ke pelanggan. Saat disetujui, konversi langsung menjadi Sales Order.</p>
                        </div>
                        <div class="p-3 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-black dark:text-white">Surat Jalan &rarr; Invoice Penagihan</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Cetak Surat Jalan (Delivery Order) untuk tim logistik pengiriman, kemudian terbitkan Invoice bertempo (Term of Payment).</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 6: KEUANGAN, KAS & LAPORAN AKUNTANSI         -->
            <!-- ===================================================== -->
            <section id="sec-finance" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'finance') && matchesSearch('keuangan kas bank akuntansi laporan laba rugi neraca arus kas p&l jurnal ekspor excel')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-500 flex items-center justify-center font-bold text-sm">
                            6
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.keuangan_pembukuan_laporan_finansial') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Otomasi jurnal akuntansi tanpa perlu menjadi akuntan bersertifikasi</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('reports.view'))
                        <a href="{{ route('reports.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_laporan_bisnis') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-purple-600 dark:text-purple-400">Laba Rugi (P&amp;L Real-Time)</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Memantau omzet kotor, beban pokok penjualan (HPP), biaya sewa, listrik, dan laba bersih usaha Anda setiap detik.</p>
                        </div>
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-purple-600 dark:text-purple-400">Neraca Saldo &amp; Valuasi Aset</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Ketahui posisi kas tunai di laci, saldo rekening bank, total nilai aset stok gudang, dan kewajiban hutang usaha.</p>
                        </div>
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-purple-600 dark:text-purple-400">Ekspor Excel &amp; PDF Siap Audit</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Seluruh laporan penjualan dan keuangan dapat diunduh ke format XLSX dengan rumus akuntansi rapi untuk keperluan pelaporan pajak.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 7: HRM, PRESENSI ONLINE & GAJI KARYAWAN       -->
            <!-- ===================================================== -->
            <section id="sec-hrm" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'hrm') && matchesSearch('hrm karyawan gaji shift presensi selfie gps komisi kasir slip gaji payroll')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-500 flex items-center justify-center font-bold text-sm">
                            7
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.karyawan_presensi_gps_penggajian_hrm') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Manajemen kehadiran shift kerja dan slip gaji otomatis</p>
                        </div>
                    </div>
                    @if(\App\Support\Context::hasPermission('users.view'))
                        <a href="{{ route('hrm.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('docs.buka_data_karyawan') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <ul class="space-y-2 list-disc list-inside">
                        <li><strong>Presensi GPS &amp; Radius Kantor:</strong> Karyawan dapat absen masuk dan pulang melalui smartphone masing-masing dengan validasi titik GPS radius toko.</li>
                        <li><strong>Pengaturan Shift Kerja:</strong> Tetapkan shift pagi, siang, dan malam dengan toleransi keterlambatan menit otomatis.</li>
                        <li><strong>Perhitungan Komisi Penjualan:</strong> Khusus kasir atau sales, Cooca dapat mengkalkulasi bonus omzet yang otomatis masuk ke komponen slip gaji bulanan.</li>
                        <li><strong>Cetak Slip Gaji PDF:</strong> Sekali klik untuk menerbitkan slip gaji resmi berlogo perusahaan ke karyawan.</li>
                    </ul>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 8: CRM, LOYALITAS & WHATSAPP BROADCAST        -->
            <!-- ===================================================== -->
            <section id="sec-crm" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'all') && matchesSearch('crm loyalitas member poin whatsapp broadcast pelanggan diskon toko online storefront')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-pink-500/15 text-pink-500 flex items-center justify-center font-bold text-sm">
                            8
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.crm_pelanggan_poin_notifikasi_whatsapp') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Retensi pelanggan loyal dan katalog toko online digital</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <span class="font-bold text-black dark:text-white">Tiering Member &amp; Poin Belanja</span>
                        <p class="text-black/60 dark:text-white/60 leading-relaxed">
                            Pelanggan otomatis naik tingkat (*Silver &rarr; Gold &rarr; Platinum*) berdasarkan akumulasi belanja. Poin yang terkumpul dapat dipotongkan langsung sebagai diskon di kasir POS.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-2xs">
                        <span class="font-bold text-black dark:text-white">Broadcast WhatsApp &amp; Bukti Struk</span>
                        <p class="text-black/60 dark:text-white/60 leading-relaxed">
                            Kirim struk digital ramah lingkungan langsung ke WhatsApp pelanggan tanpa memboroskan kertas thermal, serta kirim ucapan promo khusus member.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 9: KOMUNITAS BISNIS & EVENT UMKM COOCA        -->
            <!-- ===================================================== -->
            <section id="sec-community" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'community') && matchesSearch('komunitas community event seminar workshop bazaar tiket qris repost komen')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-teal-500/15 text-teal-500 flex items-center justify-center font-bold text-sm">
                            9
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.komunitas_event_jejaring_bisnis_cooca') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Wadah berbagi ide, tips omzet, dan tiket barcode workshop offline</p>
                        </div>
                    </div>
                    <a href="{{ route('community.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>{{ __('docs.buka_komunitas') }}</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <p class="leading-relaxed">
                        Fitur Komunitas Cooca dirancang untuk mempertemukan ribuan pemilik usaha di seluruh Indonesia:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1">
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-teal-600 dark:text-teal-400">Diskusi &amp; Topik Tren</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Bahas strategi harga, perizinan BPOM/Halal, rekrutmen karyawan, dan inspirasi promosi menggunakan tagar seperti <code>#TipsBisnis</code>.</p>
                        </div>
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-teal-600 dark:text-teal-400">Pendaftaran Event &amp; QRIS</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Ikuti seminar bisnis eksklusif. Untuk event berbayar, pembayaran instan via QRIS langsung mengonfirmasi tiket Anda.</p>
                        </div>
                        <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 space-y-1">
                            <span class="font-bold text-teal-600 dark:text-teal-400">Tiket Barcode &amp; Presensi Lokasi</span>
                            <p class="text-black/60 dark:text-white/60 text-[11px]">Check-in di venue event menggunakan scan barcode tiket digital atau deteksi lokasi GPS venue.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 10: OTORISASI (MAR) & AUDIT ANTI-FRAUD       -->
            <!-- ===================================================== -->
            <section id="sec-security" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'all') && matchesSearch('keamanan otorisasi mar approval audit log anti-fraud fraud manipulasi hak akses')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-600/15 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            10
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.otorisasi_mar_sistem_anti_fraud') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Perlindungan bisnis dari kecurangan kasir dan manipulasi laporan</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <span class="font-bold text-black dark:text-white text-sm">Multi-Approval Rules (MAR)</span>
                            <p class="text-black/60 dark:text-white/60 text-xs leading-relaxed">
                                Anda dapat mengatur agar transaksi pembelian atau pengeluaran kas di atas nominal tertentu (misal &gt; Rp 5.000.000) wajib disetujui (Approved) oleh Owner sebelum dana dapat dicairkan.
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <span class="font-bold text-black dark:text-white text-sm">Pusat Deteksi Fraud Real-Time</span>
                            <p class="text-black/60 dark:text-white/60 text-xs leading-relaxed">
                                Cooca memiliki algoritma pendeteksi pola anomali: kasir yang sering membatalkan transaksi setelah cetak nota, perbedaan jam presensi fisik, atau penghapusan mutasi stok secara tidak wajar.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 11: LANGGANAN & TOKEN AI                     -->
            <!-- ===================================================== -->
            <section id="sec-billing" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'all') && matchesSearch('langganan billing kuota batas token ai patungan paket free solo upgrade')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-bold text-sm">
                            11
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">{{ __('docs.langganan_kuota_fitur_token_ai') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Pengelolaan kapasitas bisnis dan aktivasi asisten kecerdasan buatan</p>
                        </div>
                    </div>
                    <a href="{{ route('billing.limits') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>{{ __('docs.cek_kuota_fitur') }}</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 text-xs text-black/70 dark:text-white/70 shadow-2xs">
                    <p class="leading-relaxed">
                        Cooca menawarkan paket fleksibel dari <strong>Free Solo</strong> hingga <strong>Cooca Unlimited</strong>. Jika kuota produk atau invoice bulanan Anda mencapai batas, tingkatkan paket Anda dengan mudah melalui transfer bank atau QRIS langsung dari menu <strong>Langganan &amp; Kuota</strong>.
                    </p>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 12: ASISTEN PINTAR AI & INTELIJEN BISNIS      -->
            <!-- (PANDUAN LENGKAP 20 JENIS USAHA & LINK TUTORIAL)      -->
            <!-- ===================================================== -->
            @include('app.docs.partials.sec_ai')

            <!-- ===================================================== -->
            <!-- SECTION 13: PINTASAN KEYBOARD (SHORTCUTS)            -->
            <!-- ===================================================== -->
            <section id="sec-shortcuts" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'shortcuts') && matchesSearch('shortcut pintasan tombol keyboard f1 f2 f4 bayar cepat cari')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-bold text-sm">
                            13
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">Tabel Pintasan Keyboard (Keyboard Shortcuts)</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Percepat transaksi kasir dan navigasi sistem tanpa menyentuh mouse</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 text-[11px] font-bold text-black/60 dark:text-white/60 uppercase">
                                <tr>
                                    <th class="px-4 py-3">Tombol Pintas</th>
                                    <th class="px-4 py-3">Lokasi / Modul</th>
                                    <th class="px-4 py-3">Fungsi Pintasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-[#007AFF]">
                                        <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">F2</kbd>
                                    </td>
                                    <td class="px-4 py-3 text-black/70 dark:text-white/70">Mesin Kasir (POS)</td>
                                    <td class="px-4 py-3 text-black/90 dark:text-white font-medium">Buka Dialog Pembayaran Cepat (Quick Pay)</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-[#007AFF]">
                                        <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">F4</kbd>
                                    </td>
                                    <td class="px-4 py-3 text-black/70 dark:text-white/70">Mesin Kasir (POS)</td>
                                    <td class="px-4 py-3 text-black/90 dark:text-white font-medium">Fokus ke Kolom Cari Produk / Barcode</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-[#007AFF]">
                                        <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">Ctrl + K</kbd> / <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">Cmd + K</kbd>
                                    </td>
                                    <td class="px-4 py-3 text-black/70 dark:text-white/70">Global (Seluruh Layar)</td>
                                    <td class="px-4 py-3 text-black/90 dark:text-white font-medium">Buka Navigasi Cepat Spotlight Palette</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-[#007AFF]">
                                        <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">Esc</kbd>
                                    </td>
                                    <td class="px-4 py-3 text-black/70 dark:text-white/70">Global (Seluruh Layar)</td>
                                    <td class="px-4 py-3 text-black/90 dark:text-white font-medium">Tutup Modal Dialog / Popup Aktif</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-[#007AFF]">
                                        <kbd class="px-2 py-1 rounded bg-black/5 dark:bg-white/10 border border-black/10 dark:border-white/10">Ctrl + P</kbd>
                                    </td>
                                    <td class="px-4 py-3 text-black/70 dark:text-white/70">Nota Kasir / Laporan</td>
                                    <td class="px-4 py-3 text-black/90 dark:text-white font-medium">Cetak Dokumen atau Cetak Ulang Struk</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECTION 14: FAQ & PEMECAHAN MASALAH (TROUBLESHOOTING) -->
            <!-- ===================================================== -->
            <section id="sec-faq" class="space-y-4"
                x-show="(activeTab === 'all' || activeTab === 'faq') && matchesSearch('faq tanya jawab kendala error masalah printer selisih lupa sandi bluetooth')">

                <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center font-bold text-sm">
                            14
                        </span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">Tanya Jawab Populer (FAQ) &amp; Solusi Kendala</h2>
                            <p class="text-xs text-black/50 dark:text-white/50">Pertanyaan umum dan solusi cepat penanganan masalah operasional</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- FAQ 1 -->
                    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
                        <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                            class="w-full p-4.5 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <span>Bagaimana jika printer thermal bluetooth tidak mau mencetak struk?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                                :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="expandedFaq === 1" x-collapse class="px-4.5 pb-4.5 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                            <p>1. Pastikan Bluetooth perangkat kasir (laptop/tablet) dalam status aktif dan sudah di-pairing dengan printer.</p>
                            <p>2. Buka menu <strong>Pengaturan &rarr; Printer Kasir</strong> di Cooca, pilih nama printer yang sesuai dan lakukan uji coba *"Test Print"*.</p>
                            <p>3. Pastikan kertas thermal tidak terpasang terbalik dan penutup printer terkunci rapat.</p>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
                        <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                            class="w-full p-4.5 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <span>Mengapa kas fisik laci kasir berbeda dengan perhitungan sistem saat tutup shift?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                                :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="expandedFaq === 2" x-collapse class="px-4.5 pb-4.5 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                            <p>Perbedaan (selisih lebih atau selisih kurang) biasanya disebabkan oleh salah kembalian manual, kas masuk/keluar non-penjualan yang lupa dicatat di tombol *"Uang Keluar Kas"*, atau transaksi non-tunai yang keliru diinput sebagai tunai.</p>
                            <p>Cooca akan tetap mencatat selisih tersebut ke dalam laporan audit pertanggungjawaban kasir sehingga pembukuan buku besar tetap akurat dan seimbang.</p>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
                        <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                            class="w-full p-4.5 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <span>Apakah Cooca tetap bisa digunakan saat internet toko mati (Offline)?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                                :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="expandedFaq === 3" x-collapse class="px-4.5 pb-4.5 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                            <p>Ya. Terminal kasir Cooca dilengkapi arsitektur local storage caching. Kasir tetap bisa melayani transaksi offline dan mencetak struk belanja. Ketika koneksi internet pulih kembali, data transaksi akan otomatis tersinkronisasi ke server pusat Cooca.</p>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-2xs">
                        <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                            class="w-full p-4.5 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <span>Bagaimana cara mengundang akuntan atau konsultan luar untuk melihat pembukuan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                                :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="expandedFaq === 4" x-collapse class="px-4.5 pb-4.5 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                            <p>Buka <strong>Pengaturan &rarr; Peran &amp; Hak Akses</strong>. Buat peran baru *"Akuntan Eksternal"* dengan hak akses hanya-baca (*Read-Only*) untuk menu Keuangan, Laporan, dan Jurnal. Dengan ini, akuntan dapat mengunduh neraca dan laba rugi tanpa bisa memodifikasi master data atau transaksi kasir.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- FOOTER BANNER: CONTACT SUPPORT                        -->
            <!-- ===================================================== -->
            <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-br from-black/90 to-black/80 dark:from-white/10 dark:to-white/5 text-white flex flex-col sm:flex-row items-center justify-between gap-5 shadow-lg">
                <div class="space-y-1 text-center sm:text-left">
                    <h3 class="text-base sm:text-lg font-bold">{{ __('docs.masih_memiliki_pertanyaan_atau_mengalami_kendala') }}</h3>
                    <p class="text-xs text-white/70 max-w-lg">
                        Tim teknis dan konsultan bisnis Cooca siap membantu memastikan kelancaran operasional usaha Anda setiap hari.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if(\App\Support\Context::isOwner())
                        <a href="{{ route('feedback.bugs.create') }}"
                            class="px-5 py-2.5 rounded-xl bg-white text-black hover:bg-white/90 text-xs font-bold transition active:scale-95 shadow-sm">{{ __('docs.buka_tiket_kendala') }}</a>
                    @endif
                    <a href="https://wa.me/6281234567890?text=Halo%20Tim%20Cooca,%20saya%20butuh%20panduan%20penggunaan" target="_blank"
                        class="px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>WhatsApp Dukungan</span>
                    </a>
                </div>
            </div>

        </main>
    </div>

</div>
@endsection
