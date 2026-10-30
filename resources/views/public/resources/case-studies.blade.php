@extends('layouts.public_marketing')

@php
    $caseStudyConfig = config('marketing.case_studies', []);
    $caseStudies = $caseStudyConfig['items'] ?? [];
    $caseStudyDisclaimer = $caseStudyConfig['disclaimer'] ?? 'Angka merupakan ilustrasi berdasarkan skenario umum dan bukan jaminan hasil.';
@endphp

@section('title', 'Studi Kasus UMKM: Efisiensi Operasional Nyata | COOCA')
@section('description', 'Pelajari contoh skenario UMKM F&B, ritel, bengkel, dan laundry menertibkan stok gudang harian dan menutup kebocoran kasir bersama COOCA.')
@section('og_title', 'Studi Kasus UMKM: Efisiensi Operasional Nyata | COOCA')
@section('og_description', 'Pelajari contoh skenario UMKM F&B, ritel, bengkel, dan laundry menertibkan stok gudang harian dan menutup kebocoran kasir bersama COOCA.')
@section('canonical', route('public.resources.case-studies'))
@section('og_type', 'article')
@section('keywords', 'studi kasus umkm, contoh skenario bisnis, efisiensi bisnis umkm, sistem kasir multi cabang, kontrol hpp makanan, aplikasi bengkel motor, software erp indonesia')

@push('seo')
    <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Contoh Skenario & Studi Kasus UMKM Indonesia - COOCA",
  "description": "Kumpulan contoh skenario implementasi Business Operating System COOCA pada UMKM di berbagai sektor industri di Indonesia.",
  "url": "{{ route('public.resources.case-studies') }}",
  "provider": {
    "@type": "Organization",
    "name": "COOCA",
    "url": "{{ url('/') }}"
  },
  "mainEntity": {
    "@type": "ItemList",
    "itemListElement": [
      @foreach ($caseStudies as $idx => $cs)
      {
        "@type": "ListItem",
        "position": {{ $idx + 1 }},
        "name": "{{ addslashes($cs['title']) }}"
      }{{ $loop->last ? '' : ',' }}
      @endforeach
    ]
  }
}
</script>
@endpush

@section('content')
    <div x-data="{
        activeFilter: 'all',
        openFaq: null,
        activeCaseModal: null,
        toggleFaq(idx) {
            this.openFaq = this.openFaq === idx ? null : idx;
            this.refreshIcons();
        },
        openDetail(caseId) {
            this.activeCaseModal = caseId;
            this.refreshIcons();
        },
        closeDetail() {
            this.activeCaseModal = null;
        },
        refreshIcons() {
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        }
    }" x-init="refreshIcons()"
        class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Apple Bento Modern Dark Style without Breadcrumb) ═══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glows --}}
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-10 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full">

                    <!-- Left Column: Headline, Value Proposition & Actions -->
                    <div class="lg:col-span-6 space-y-6 text-left flex flex-col items-start w-full">
                        <div class="space-y-3 w-full">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.06] border border-white/10 text-xs sm:text-[13px] font-semibold text-[#00C4D8] backdrop-blur-md mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <span>Contoh Skenario &amp; Solusi Nyata</span>
                            </div>

                            <h1
                                class="text-2.5xl xs:text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-black tracking-tight text-white leading-[1.15] text-balance break-words text-left">
                                Contoh Skenario: Bagaimana UMKM Menutup Kebocoran Kasir &amp; <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Naik
                                    Kelas</span>
                            </h1>
                        </div>

                        <p
                            class="text-sm sm:text-base lg:text-lg text-slate-300 leading-relaxed font-normal text-pretty text-left max-w-xl">
                            Bukan klaim berlebihan tanpa bukti. Pelajari simulasi bagaimana kedai kopi, minimarket kelontong,
                            bengkel motor, dan konveksi menata ulang operasional harian, mengeliminasi selisih kasir,
                            dan membuka cabang baru dengan tenang.
                        </p>

                        <!-- Direct Action Buttons -->
                        <div
                            class="flex flex-col sm:flex-row items-stretch sm:items-center justify-start gap-3.5 pt-2 w-full sm:w-auto">
                            <a href="#katalog-studi-kasus"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200 min-h-[48px]">
                                <span>Pilih Kategori Usaha</span>
                                <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all min-h-[48px]">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Coba COOCA Gratis</span>
                            </a>
                        </div>

                        <!-- Tangible Impact Indicators with Prompt 2 Disclaimer -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-white/10 w-full">
                            <div class="p-3 rounded-2xl bg-[#060B1E]/80 border border-white/10">
                                <div class="text-xl sm:text-2xl font-black text-emerald-400 tabular-nums">12.4%*</div>
                                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Hemat Bahan Baku</div>
                            </div>

                            <div class="p-3 rounded-2xl bg-[#060B1E]/80 border border-white/10">
                                <div class="text-xl sm:text-2xl font-black text-[#00C4D8] tabular-nums">5 Menit*</div>
                                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Tutup Buku Shift</div>
                            </div>

                            <div class="p-3 rounded-2xl bg-[#060B1E]/80 border border-white/10">
                                <div class="text-xl sm:text-2xl font-black text-amber-400 tabular-nums">99.8%*</div>
                                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Kesesuaian Stok</div>
                            </div>

                            <div class="p-3 rounded-2xl bg-[#060B1E]/80 border border-white/10">
                                <div class="text-xl sm:text-2xl font-black text-purple-400 tabular-nums">0%*</div>
                                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Kasbon Macet</div>
                            </div>

                            <div class="col-span-2 sm:col-span-4 pt-1">
                                <p class="text-[11px] text-slate-400 italic text-left">
                                    * {{ $caseStudyDisclaimer }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Example Scenario UI Bento (Prompt 2 Compliant) -->
                    <div class="lg:col-span-6 relative w-full mt-4 lg:mt-0">
                        <div
                            class="relative rounded-2xl sm:rounded-3xl bg-gradient-to-b from-[#0E1E45]/90 to-[#0A122C]/90 p-4 sm:p-6 shadow-2xl border border-white/15 ring-1 ring-white/10 backdrop-blur-xl text-white overflow-hidden space-y-4">
                            {{-- Spotlight Decoration --}}
                            <div
                                class="absolute -top-24 -right-24 w-48 h-48 bg-[#007AFF]/25 rounded-full blur-3xl pointer-events-none">
                            </div>

                            <!-- Header Scenario Badge -->
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div>
                                    <div class="text-xs font-bold text-white">Skenario Operasional F&amp;B</div>
                                    <div class="text-[11px] text-slate-400">Kedai Kopi - 3 Gerai (Bandung)</div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-slate-300 bg-white/10 border border-white/15 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                    Contoh Skenario
                                </span>
                            </div>

                            <!-- Before & After Direct Comparison -->
                            <div class="space-y-2.5">
                                <div class="p-3 rounded-xl bg-rose-950/30 border border-rose-500/25">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-rose-400">
                                        Kondisi Sebelum Sistem
                                    </div>
                                    <div class="text-xs text-slate-300 mt-1 leading-relaxed">
                                        Takaran susu berbeda per barista. Selisih 45 liter susu fresh per bulan tanpa
                                        kejelasan rekonsiliasi kasir.
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/30">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">
                                        Hasil Setelah COOCA
                                    </div>
                                    <div class="text-xs text-emerald-100 mt-1 leading-relaxed font-medium">
                                        BOM Resep otomatis memotong stok bahan per porsi kopi. Biaya bahan baku (Food
                                        Cost) terpangkas 12.4%* dan kasir tutup buku tepat 5 menit.
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline & Disclaimer -->
                            <div class="pt-2 border-t border-white/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>Estimasi Implementasi: 2 Hari Kerja</span>
                                </div>
                                <span class="font-bold text-[#00C4D8]">Modul: Kasir + BOM Resep</span>
                            </div>
                            <div class="text-[10px] text-slate-400 italic">
                                * {{ $caseStudyDisclaimer }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. KATALOG STUDI KASUS & FILTER TAB ═════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section id="katalog-studi-kasus" class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-10">

            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        DAFTAR SKENARIO PER INDUSTRI
                    </div>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Pilih Sektor Bisnis yang Relevan dengan Usaha Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Setiap industri memiliki tantangan operasional yang berbeda. Telusuri contoh skenario solusi dan alur kerja di lapangan.
                    </p>
                </div>

                <!-- Segmented Control Filters -->
                <div
                    class="flex flex-wrap items-center gap-1.5 p-1.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[16px] shadow-sm">
                    <button @click="activeFilter = 'all'; refreshIcons();"
                        :class="activeFilter === 'all' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Semua Industri
                    </button>
                    <button @click="activeFilter = 'fnb'; refreshIcons();"
                        :class="activeFilter === 'fnb' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        F&amp;B / Resto
                    </button>
                    <button @click="activeFilter = 'retail'; refreshIcons();"
                        :class="activeFilter === 'retail' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Retail &amp; Toko
                    </button>
                    <button @click="activeFilter = 'workshop'; refreshIcons();"
                        :class="activeFilter === 'workshop' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Bengkel &amp; Servis
                    </button>
                    <button @click="activeFilter = 'services'; refreshIcons();"
                        :class="activeFilter === 'services' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Laundry &amp; Jasa
                    </button>
                    <button @click="activeFilter = 'fashion'; refreshIcons();"
                        :class="activeFilter === 'fashion' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Konveksi &amp; Fashion
                    </button>
                </div>
            </div>

            <!-- Bento Grid of Case Studies (Data-Driven from config/marketing.php) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @foreach ($caseStudies as $case)
                    <div x-show="activeFilter === 'all' || activeFilter === '{{ $case['industry'] }}'"
                        class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <span
                                    class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-[8px]">
                                    {{ $case['industry_label'] }}
                                </span>
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $case['stat_badge'] }}*
                                </span>
                            </div>

                            <!-- Status Badge (Prompt 2 Compliant) -->
                            <div>
                                @if (($case['status'] ?? 'illustration') === 'verified')
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        Pelanggan Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/10 border border-black/[0.04] dark:border-white/10 px-2.5 py-0.5 rounded-full">
                                        <i data-lucide="file-text" class="w-3 h-3 text-[#007AFF]"></i>
                                        Contoh Skenario
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                                {{ $case['title'] }}
                            </h3>

                            <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                                <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                    <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                    {{ $case['challenge'] }}
                                </div>
                                <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                    <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                    {{ $case['solution'] }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                            @if (!empty($case['solution_route']) && \Illuminate\Support\Facades\Route::has($case['solution_route']))
                                <a href="{{ route($case['solution_route']) }}"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                    {{ $case['solution_label'] }} &rarr;
                                </a>
                            @else
                                <span class="text-xs font-semibold text-slate-400">{{ $case['solution_label'] }}</span>
                            @endif
                            <button @click="openDetail({{ $case['id'] }})"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                <span>Baca Kisah Lengkap</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                @endforeach

                <!-- Disclaimer Banner under Grid -->
                <div class="col-span-full p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-xs text-slate-500 dark:text-slate-400 text-center italic">
                    * {{ $caseStudyDisclaimer }}
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. MATRIKS PERBANDINGAN SEBELUM VS SESUDAH ═══════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="border-y border-black/[0.06] dark:border-white/[0.08] py-16 sm:py-20 bg-white/50 dark:bg-[#151B2B]/40">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        MATRIKS TRANSFORMASI
                    </div>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Operasional Sebelum vs Sesudah Menggunakan COOCA
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Perubahan langsung yang dirasakan oleh pemilik usaha dan staf kasir setelah menggunakan sistem yang
                        rapi.
                    </p>
                </div>

                <!-- Bento Comparison Table -->
                <div
                    class="overflow-x-auto rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-black/[0.06] dark:border-white/[0.08] text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="py-4 px-5 w-1/4">Aspek Operasional</th>
                                <th class="py-4 px-5 w-3/8 text-rose-600 dark:text-rose-400 bg-rose-500/[0.03]">Sebelum
                                    Pakai COOCA</th>
                                <th class="py-4 px-5 w-3/8 text-emerald-600 dark:text-emerald-400 bg-emerald-500/[0.03]">
                                    Sesudah Pakai COOCA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.06] dark:divide-white/[0.08] text-sm">
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Tutup Kasir Harian</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Manual hitung
                                    nota kertas 2–3 jam tiap malam, rawan selisih uang kas fisik.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Otomatis balance dalam 5 menit, laporan kasir langsung terkirim ke WhatsApp owner.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Kontrol Stok &amp; Bahan
                                    Baku</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Stok hanya
                                    dihitung saat opname bulanan, selisih bahan basi sering tidak terlacak.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Resep BOM terpotong otomatis tiap transaksi kasir, alert stok menipis real-time.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Piutang &amp; Kasbon
                                    Pelanggan</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Dicatat di bon
                                    kertas terselip, sering lupa ditagih sampai berbulan-bulan.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Plafon kredit terkunci di sistem, pengingat jatuh tempo otomatis via WhatsApp.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Pengawasan Multi-Cabang
                                </td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Owner harus
                                    datang keliling fisik ke tiap cabang untuk cek setoran tunai.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Dashboard sentral dari HP, omzet dan laba per cabang terlihat detik demi detik.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ IMPLEMENTASI STUDI KASUS ══════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-6">
            <div class="text-center space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    TANYA JAWAB IMPLEMENTASI
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Pertanyaan Seputar Penerapan COOCA di Lapangan
                </h2>
            </div>

            <div class="space-y-3 pt-2">
                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(1)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Berapa lama proses implementasi dari sistem manual ke COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 1" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Rata-rata UMKM hanya membutuhkan 1 hingga 3 hari kerja. Anda dapat langsung mengimpor data produk
                        dan pelanggan menggunakan template Excel yang sudah kami sediakan, lalu kasir dapat langsung
                        digunakan pada tablet atau komputer apa pun tanpa instalasi server lokal rumit.
                    </div>
                </div>

                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(2)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Apakah staf dan kasir yang belum terbiasa komputer bisa mengoperasikannya?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Sangat bisa. Antarmuka kasir COOCA dirancang dengan tombol sentuh besar (min 48px), visual foto
                        menu/produk yang jelas, dan alur pembayaran satu sentuhan. Staf baru rata-rata dapat melayani
                        pembeli secara mandiri dalam waktu latihan kurang dari 30 menit.
                    </div>
                </div>

                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(3)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Bagaimana jika koneksi internet di toko fisik tiba-tiba mati?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Modul Kasir POS COOCA mendukung mode offline lokal. Kasir tetap dapat melayani antrean belanja,
                        menghitung kembalian uang tunai, dan mencetak struk thermal. Ketika koneksi internet menyala
                        kembali, transaksi akan otomatis tersinkronisasi ke server pusat.
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. CONVERSION CTA SECTION ════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
            <div
                class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-center space-y-6 shadow-sm">
                <div class="max-w-2xl mx-auto space-y-3">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        MULAI TRANSFORMASI USAHA
                    </div>
                    <h3
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Siap Menjadikan Usaha Anda Kisah Sukses Berikutnya?
                    </h3>
                    <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Tinggalkan pencatatan manual yang menguras tenaga dan waktu istirahat Anda. Uji coba COOCA sekarang
                        dan rasakan kemudahan mengontrol bisnis dari genggaman.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
                    <a href="{{ route('register') }}"
                        class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 active:scale-[0.98] transition-all shadow-sm">
                        <span>Mulai Uji Coba Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.demo') }}"
                        class="h-12 px-7 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] text-slate-900 dark:text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <i data-lucide="play" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                        <span>Lihat Demonstrasi Kasir</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. MODAL SHEET: Detail Kisah Studi Kasus (Prompt 2 Compliant) ════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <div x-show="activeCaseModal !== null" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-case-title" role="dialog" aria-modal="true">

            <div class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-opacity"
                @click="closeDetail()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8 space-y-6">

                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                DETAIL SKENARIO STUDI KASUS</div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-case-title">
                                Simulasi Langkah Transformasi Operasional
                            </h3>
                        </div>
                        <button @click="closeDetail()"
                            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#2C2C2E] text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Dynamic Case Details for Modals -->
                    @foreach ($caseStudies as $case)
                        <div x-show="activeCaseModal === {{ $case['id'] }}" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="font-bold text-base text-slate-900 dark:text-white">
                                    {{ $case['details']['title'] }}
                                </div>
                                @if (($case['status'] ?? 'illustration') === 'verified')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full shrink-0">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        Pelanggan Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/10 border border-black/[0.04] dark:border-white/10 px-2.5 py-0.5 rounded-full shrink-0">
                                        <i data-lucide="file-text" class="w-3 h-3 text-[#007AFF]"></i>
                                        Contoh Skenario
                                    </span>
                                @endif
                            </div>

                            @if (($case['status'] ?? 'illustration') === 'verified' && !empty($case['verified_data']['owner_quote']))
                                <blockquote class="p-3.5 rounded-[12px] bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-xs italic text-emerald-900 dark:text-emerald-200">
                                    "{{ $case['verified_data']['owner_quote'] }}"
                                    <div class="mt-1 font-semibold not-italic text-emerald-700 dark:text-emerald-400">
                                        — {{ $case['verified_data']['owner_name'] }}, {{ $case['city'] }}
                                    </div>
                                </blockquote>
                            @endif

                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                {{ $case['details']['description'] }}
                            </p>

                            <div class="space-y-2 pt-1">
                                @foreach ($case['details']['steps'] as $step)
                                    <div
                                        class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                        <strong class="text-slate-900 dark:text-white block text-xs mb-1">
                                            {{ $step['step'] }}
                                        </strong>
                                        <span class="text-xs text-slate-600 dark:text-slate-300">
                                            {{ $step['desc'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-3 rounded-[12px] bg-slate-50 dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] text-xs text-slate-500 dark:text-slate-400 italic">
                                * {{ $caseStudyDisclaimer }}
                            </div>
                        </div>
                    @endforeach

                    <!-- Modal Actions -->
                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('register') }}"
                            class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                            Mulai Terapkan di Usaha Anda &rarr;
                        </a>
                        <button @click="closeDetail()"
                            class="h-10 px-5 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
