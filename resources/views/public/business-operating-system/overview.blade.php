@extends('layouts.public_marketing')

@section('title', 'Business Operating System untuk Kendali Operasional Bisnis | COOCA')
@section('description', 'COOCA adalah Business Operating System terpadu untuk UMKM Indonesia. Hubungkan kasir, gudang, keuangan, channel penjualan, dan otomasi dalam satu platform.')
@section('keywords', 'business operating system, sistem operasional bisnis, software manajemen bisnis terpadu, erp umkm indonesia, platform bisnis terintegrasi')

@section('og_title', 'Business Operating System untuk Kendali Operasional Bisnis | COOCA')
@section('og_description', 'BOS terpadu untuk UMKM Indonesia. Hubungkan kasir POS, stok gudang, pembukuan riil, pesanan online, dan otomasi dalam satu sistem.')

@push('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "COOCA Business Operating System",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud, Android, iOS",
        "description": "Business Operating System terintegrasi untuk mengendalikan seluruh operasional, inventory, keuangan, kasir, dan channel penjualan bisnis dari satu platform.",
        "url": "{{ route('public.bos.overview') }}",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        },
        "publisher": {
            "@type": "Organization",
            "name": "COOCA Indonesia",
            "url": "{{ url('/') }}"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Beranda",
                "item": "{{ route('landing') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Business Operating System",
                "item": "{{ route('public.bos.overview') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Overview",
                "item": "{{ route('public.bos.overview') }}"
            }
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Apa perbedaan Business Operating System dengan aplikasi kasir (POS) biasa?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Aplikasi kasir biasa hanya mencatat apa yang dijual di kasir dan mencetak struk belanja. Sedangkan Business Operating System (COOCA) menghubungkan kasir tersebut dengan gudang bahan baku (stok terpotong otomatis), pembukuan keuangan (jurnal laba rugi terbit otomatis), database pelanggan CRM, notifikasi WhatsApp, hingga laporan manajemen owner secara real-time."
                }
            },
            {
                "@type": "Question",
                "name": "Apakah bisnis saya yang masih 1 outlet cocok menggunakan sistem ini?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sangat cocok. Justru memulai dengan sistem terpadu sejak 1 gerai akan mencegah kekacauan data saat bisnis Anda berekspansi ke cabang 2, 3, dan seterusnya. Anda tidak perlu repot migrasi sistem di kemudian hari karena COOCA dirancang modular dari skala pemula hingga multi-cabang."
                }
            },
            {
                "@type": "Question",
                "name": "Apakah data keuangan dan resep bisnis saya aman dan terpisah dari bisnis lain?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Ya. COOCA menerapkan arsitektur isolasi multi-tenant yang ketat. Setiap bisnis memiliki partisi data independen dengan enkripsi AES-256 dan kontrol hak akses berbasis role (Kasir, Manajer, Owner). Staf kasir tidak akan bisa melihat laporan laba bersih atau resep rahasia yang hanya boleh diakses oleh pemilik."
                }
            },
            {
                "@type": "Question",
                "name": "Perangkat apa saja yang dibutuhkan untuk menjalankan COOCA?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Anda tidak perlu membeli perangkat komputer mahal. Kasir dapat dijalankan cukup dari smartphone Android atau tablet yang sudah ada, terhubung ke printer thermal Bluetooth portabel. Manajemen stok dan pembukuan dapat diakses fleksibel dari browser laptop atau PC mana pun."
                }
            }
        ]
    }
    </script>
@endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Business OS Architecture - Full Viewport) ═══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            <!-- Ambient Background Glows -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Without Breadcrumbs) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile, Tablet & Desktop) -->
                    <div class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse shrink-0"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                {{ __('public_bos.overview_kicker') }}
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[2.75rem] xl:text-[3.5rem] font-extrabold text-white tracking-tight leading-[1.22] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                {{ __('public_bos.overview_title') }} <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">{{ __('public_bos.overview_title_accent') }}</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            {{ __('public_bos.overview_subtitle') }}
                        </p>

                        <!-- Action Buttons (Left-Aligned on Mobile, Tablet & Desktop) -->
                        <div class="pt-1 flex flex-wrap items-center justify-start gap-2.5 sm:gap-3.5 w-full sm:w-auto">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>{{ __('public_bos.btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>{{ __('public_bos.btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-8 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>{{ __('public_bos.overview_cta_try') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @endif
                            <a href="{{ route('public.bos.how-it-works') }}"
                                class="h-10 sm:h-12 px-4 sm:px-6 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <i data-lucide="play" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#00C4D8]"></i>
                                <span>{{ __('public_bos.overview_cta_how') }}</span>
                            </a>
                        </div>

                        <!-- Reassurance Checkpoints -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3.5 sm:gap-x-5 gap-y-1.5 text-[11px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ __('public_bos.overview_trust_no_duplicate') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ __('public_bos.overview_trust_devices') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ __('public_bos.overview_trust_secure') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Interactive Apple Bento Architecture Simulation (6 Cols) -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Cockpit Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Floating Card Top-Right (Tablet & Desktop) -->
                        <div
                            class="hidden sm:block absolute -top-4 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.2)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[10.5px] text-slate-400 font-medium">{{ __('public_bos.overview_cockpit_financial_acc') }}</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-base font-extrabold text-white tabular-nums tracking-tight mt-0.5">{{ __('public_bos.overview_cockpit_single_data') }}</div>
                            <div class="text-[10.5px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>{{ __('public_bos.overview_cockpit_no_discrepancy') }}</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left (Tablet & Desktop) -->
                        <div
                            class="hidden sm:block absolute -bottom-4 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[155px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[10.5px] text-slate-400 font-medium">{{ __('public_bos.overview_cockpit_pipeline') }}</div>
                            <div class="text-base font-extrabold text-white tabular-nums tracking-tight mt-0.5">{{ __('public_bos.overview_cockpit_instant_sync') }}</div>
                            <div class="text-[10.5px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <span>{{ __('public_bos.overview_cockpit_auto_connected') }}</span>
                            </div>
                        </div>

                        <!-- Main Cockpit Window Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-3.5 sm:p-5 lg:p-6 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-3 sm:space-y-4 text-white relative z-10 overflow-hidden">
                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- macOS Window Header -->
                            <div class="flex items-center justify-between gap-2 border-b border-white/10 pb-3">
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                </div>
                                <span class="text-xs font-mono font-medium text-slate-300 truncate">{{ __('public_bos.overview_cockpit_engine_title') }}</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 shrink-0 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {{ __('public_bos.overview_cockpit_synced') }}
                                </span>
                            </div>

                            <!-- 3 Layer Stack Visualizer -->
                            <div class="space-y-2.5">
                                <!-- Layer 1: Channels -->
                                <div
                                    class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3 hover:bg-white/[0.07] transition-all">
                                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold shrink-0">
                                            <i data-lucide="shopping-bag" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs sm:text-sm font-bold text-white truncate">{{ __('public_bos.overview_cockpit_ch_title') }}</div>
                                            <div class="text-[10.5px] sm:text-[11px] text-slate-400 truncate">{{ __('public_bos.overview_cockpit_ch_desc') }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10.5px] font-mono text-emerald-400 font-semibold px-2 py-0.5 rounded bg-emerald-500/10 shrink-0">{{ __('public_bos.overview_cockpit_ch_badge') }}</span>
                                </div>

                                <!-- Connector Indicator -->
                                <div class="flex justify-center text-slate-500 py-0">
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5 text-[#00C4D8]/80 animate-bounce"></i>
                                </div>

                                <!-- Layer 2: Operating Core -->
                                <div
                                    class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-gradient-to-r from-blue-950/70 to-cyan-950/70 border border-[#00C4D8]/30 hover:border-[#00C4D8]/50 transition-all">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                            <div
                                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#00C2FF]/20 text-[#00C2FF] flex items-center justify-center font-bold shrink-0">
                                                <i data-lucide="cpu" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs sm:text-sm font-bold text-white truncate">{{ __('public_bos.overview_cockpit_core_title') }}</div>
                                                <div class="text-[10.5px] sm:text-[11px] text-slate-300 truncate">{{ __('public_bos.overview_cockpit_core_desc') }}</div>
                                            </div>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#00C2FF]/20 text-[#00C2FF] border border-[#00C2FF]/30 shrink-0">{{ __('public_bos.overview_cockpit_core_badge') }}</span>
                                    </div>
                                </div>

                                <!-- Connector Indicator -->
                                <div class="flex justify-center text-slate-500 py-0">
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5 text-[#00C4D8]/80 animate-bounce"></i>
                                </div>

                                <!-- Layer 3: Owner Control & Financial Ledger -->
                                <div
                                    class="p-3 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3 hover:bg-white/[0.07] transition-all">
                                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                                            <i data-lucide="line-chart" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs sm:text-sm font-bold text-white truncate">{{ __('public_bos.overview_cockpit_owner_title') }}</div>
                                            <div class="text-[10.5px] sm:text-[11px] text-slate-400 truncate">{{ __('public_bos.overview_cockpit_owner_desc') }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10.5px] font-mono font-bold text-emerald-400 tabular-nums px-2 py-0.5 rounded bg-emerald-500/10 shrink-0">{{ __('public_bos.overview_cockpit_owner_badge') }}</span>
                                </div>
                            </div>

                            <!-- Live Status Banner -->
                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between text-xs text-slate-400 font-mono">
                                <span>{{ __('public_bos.overview_cockpit_footer_left') }}</span>
                                <span class="text-emerald-400 font-semibold">{{ __('public_bos.overview_cockpit_footer_right') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. THE PROBLEM: KEKACAUAN SISTEM BISNIS TERFRAGMENTASI ═══════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#FF9500]">{{ __('public_bos.overview_problem_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        {{ __('public_bos.overview_problem_title') }}
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        {{ __('public_bos.overview_problem_subtitle') }}
                    </p>
                </div>

                <!-- Bento 3-Grid Pain Points -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pain 1 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="copy-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_problem_1_title') }}</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_problem_1_desc') }}
                        </p>
                    </div>

                    <!-- Pain 2 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_problem_2_title') }}</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_problem_2_desc') }}
                        </p>
                    </div>

                    <!-- Pain 3 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_problem_3_title') }}</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_problem_3_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. ARSITEKTUR LAYER COOCA: BAGAIMANA SEMUA TERHUBUNG ═════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">{{ __('public_bos.overview_arch_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        {{ __('public_bos.overview_arch_title') }}
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        {{ __('public_bos.overview_arch_desc') }}
                    </p>
                </div>

                <!-- Bento Asymmetric 5-Layer Composition -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Layer 1 (Span 2) -->
                    <div
                        class="lg:col-span-2 p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#007AFF] uppercase">{{ __('public_bos.overview_layer_1_badge') }}</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">{{ __('public_bos.overview_layer_1_type') }}</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_layer_1_title') }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_layer_1_desc') }}
                        </p>
                        <div class="pt-2 flex flex-wrap gap-2 text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">{{ __('public_bos.overview_layer_1_tag_pos') }}</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">{{ __('public_bos.overview_layer_1_tag_qr') }}</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">{{ __('public_bos.overview_layer_1_tag_web') }}</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">{{ __('public_bos.overview_layer_1_tag_po') }}</span>
                        </div>
                    </div>

                    <!-- Layer 2 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#34C759] uppercase">{{ __('public_bos.overview_layer_2_badge') }}</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]">{{ __('public_bos.overview_layer_2_type') }}</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_layer_2_title') }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_layer_2_desc') }}
                        </p>
                    </div>

                    <!-- Layer 3 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#FF9500] uppercase">{{ __('public_bos.overview_layer_3_badge') }}</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">{{ __('public_bos.overview_layer_3_type') }}</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_layer_3_title') }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_layer_3_desc') }}
                        </p>
                    </div>

                    <!-- Layer 4 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#AF52DE] uppercase">{{ __('public_bos.overview_layer_4_badge') }}</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">{{ __('public_bos.overview_layer_4_type') }}</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_layer_4_title') }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_layer_4_desc') }}
                        </p>
                    </div>

                    <!-- Layer 5 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#00C2FF] uppercase">{{ __('public_bos.overview_layer_5_badge') }}</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#00C2FF]/10 text-[#00C2FF]">{{ __('public_bos.overview_layer_5_type') }}</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_control_title') }}</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_control_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. WORKFLOW NYATA: DARI TRANSAKSI HINGGA LAPORAN OWNER ══════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-2xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-500">{{ __('public_bos.overview_flow_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        {{ __('public_bos.overview_flow_title') }}
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        {{ __('public_bos.overview_flow_desc') }}
                    </p>
                </div>

                <!-- Workflow 4-Step Interactive Card Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    <!-- Step 1 -->
                    <div
                        class="p-4 sm:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-2.5 sm:space-y-3 relative">
                        <span class="text-[10px] sm:text-xs font-mono font-bold text-slate-400">{{ __('public_bos.overview_step_1_badge') }}</span>
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="scan" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_step_1_title') }}</h3>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_step_1_desc') }}
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="p-4 sm:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-2.5 sm:space-y-3 relative">
                        <span class="text-[10px] sm:text-xs font-mono font-bold text-slate-400">{{ __('public_bos.overview_step_2_badge') }}</span>
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                            <i data-lucide="package-minus" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_step_2_title') }}</h3>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_step_2_desc') }}
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="p-4 sm:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-2.5 sm:space-y-3 relative">
                        <span class="text-[10px] sm:text-xs font-mono font-bold text-slate-400">{{ __('public_bos.overview_step_3_badge') }}</span>
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold">
                            <i data-lucide="book-open-check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_step_3_title') }}</h3>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_step_3_desc') }}
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div
                        class="p-4 sm:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-2.5 sm:space-y-3 relative">
                        <span class="text-[10px] sm:text-xs font-mono font-bold text-slate-400">{{ __('public_bos.overview_step_4_badge') }}</span>
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center font-bold">
                            <i data-lucide="smartphone" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold leading-snug text-balance break-words">{{ __('public_bos.overview_step_4_title') }}</h3>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            {{ __('public_bos.overview_step_4_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. FAQ SEO SPESIFIK BUSINESS OPERATING SYSTEM ═══════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">{{ __('public_bos.overview_faq_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        {{ __('public_bos.overview_faq_title') }}
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-pretty">
                        {{ __('public_bos.overview_faq_desc') }}
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <!-- FAQ 1 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">{{ __('public_bos.overview_faq_1_q') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            {{ __('public_bos.overview_faq_1_a') }}
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">{{ __('public_bos.overview_faq_2_q') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            {{ __('public_bos.overview_faq_2_a') }}
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">{{ __('public_bos.overview_faq_3_q') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            {{ __('public_bos.overview_faq_3_a') }}
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">{{ __('public_bos.overview_faq_4_q') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 4" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            {{ __('public_bos.overview_faq_4_a') }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. RELATED MODULES (TOPICAL CLUSTER INTERNAL LINKS) ═════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">{{ __('public_bos.overview_related_kicker') }}</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight leading-snug text-balance break-words">{{ __('public_bos.overview_related_title') }}</h3>
                    </div>
                    <a href="{{ route('public.erp.erp') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline shrink-0">
                        <span>{{ __('public_bos.overview_related_all') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    <a href="{{ route('public.erp.pos') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="monitor" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#007AFF] transition-colors leading-snug text-balance">{{ __('public_bos.overview_mod_pos_title') }}
                        </div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">{{ __('public_bos.overview_mod_pos_desc') }}</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="boxes" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#34C759] transition-colors leading-snug text-balance">{{ __('public_bos.overview_mod_inv_title') }}
                        </div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">{{ __('public_bos.overview_mod_inv_desc') }}</p>
                    </a>

                    <a href="{{ route('public.erp.accounting') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#FF9500] transition-colors leading-snug text-balance">{{ __('public_bos.overview_mod_acc_title') }}
                        </div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">{{ __('public_bos.overview_mod_acc_desc') }}</p>
                    </a>

                    <a href="{{ route('public.omnichannel.whatsapp') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-emerald-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="message-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-emerald-500 transition-colors leading-snug text-balance">{{ __('public_bos.overview_mod_wa_title') }}
                        </div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">{{ __('public_bos.overview_mod_wa_desc') }}</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 7. BOTTOM CONVERSION CALL TO ACTION ══════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div
                        class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#007AFF]/20 rounded-full blur-[140px] pointer-events-none">
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">{{ __('public_bos.overview_cta_bottom_kicker') }}</span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-[1.2] text-balance break-words">
                                {{ __('public_bos.overview_cta_bottom_title') }}
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed text-pretty">
                                {{ __('public_bos.overview_cta_bottom_desc') }}
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>{{ __('public_bos.overview_cta_bottom_btn_free') }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.pricing') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>{{ __('public_bos.overview_cta_bottom_btn_pricing') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
