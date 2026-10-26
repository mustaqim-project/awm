<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="overflow-x-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Admin Console - Cooca' }}</title>

    <!-- Theme is applied before paint so the default light surface never flashes dark. -->
    <script>
        window.COOCA_LOCALE = '{{ app()->getLocale() }}';
        window.COOCA_TIMEZONE = 'Asia/Jakarta';
        window.COOCA_TZ_ABBR = 'WIB';

        (() => {
            try {
                const savedTheme = localStorage.getItem('cooca-admin-theme') || localStorage.getItem('cooca-theme') ||
                    'light';
                const isDark = savedTheme === 'dark' || (savedTheme === 'system' && window.matchMedia(
                    '(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.dataset.theme = savedTheme;

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    const currentTheme = localStorage.getItem('cooca-admin-theme') || localStorage.getItem(
                        'cooca-theme') || 'light';
                    if (currentTheme === 'system') {
                        document.documentElement.classList.toggle('dark', e.matches);
                    }
                });
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts Preconnect & Inter / JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Favicon -->
    @php
        $adminFavicon = \App\Models\SystemSetting::get('site_favicon');
        $adminFaviconUrl =
            \App\Domain\Storage\AdminStorage::publicUrl($adminFavicon) ?? asset('assets/image/1785229034_favicon.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $adminFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $adminFaviconUrl }}">

    <!-- Tailwind CSS CDN with Apple System Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', 'Inter',
                            'system-ui', 'sans-serif'
                        ],
                        mono: ['"JetBrains Mono"', 'Menlo', 'Monaco', 'Courier New', 'monospace'],
                    },
                    spacing: {
                        '4.5': '1.125rem',
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            gray: '#8E8E93',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 (Modal Dialogs & Apple HIG Theme) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js (Apple System Visuals) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <style>
        /* COOCA UI/UX Design System v2.0 - Apple System Colors & Vibrancy */
        :root {
            --color-accent: #007AFF;
            --color-accent-dark: #0A84FF;
            --color-success: #34C759;
            --color-success-dark: #30D158;
            --color-warning: #FF9500;
            --color-warning-dark: #FF9F0A;
            --color-danger: #FF3B30;
            --color-danger-dark: #FF453A;
            --color-ai: #AF52DE;
            --color-ai-dark: #BF5AF2;
            --color-info: #5856D6;
            --color-info-dark: #5E5CE6;
            --color-teal: #30B0C7;
            --color-teal-dark: #40C8E0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", Inter, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
        }

        /* Modal Stacking Architecture: All modals use <template x-teleport="body"> with designated z-index layers (z-[200], z-[210], z-[220]). */

        /* Universal Apple HIG Form Controls & Dropdown Styling (§Dark Mode Accessibility) */
        select {
            color-scheme: light;
        }

        select option,
        select optgroup {
            background-color: #FFFFFF;
            color: #000000;
        }

        html.dark select,
        .dark select {
            color-scheme: dark;
        }

        html.dark select option,
        html.dark select optgroup,
        .dark select option,
        .dark select optgroup {
            background-color: #1C1C1E !important;
            color: #FFFFFF !important;
        }

        html.dark select option:checked,
        .dark select option:checked {
            background-color: #007AFF !important;
            color: #FFFFFF !important;
        }

        html.dark select option:hover,
        html.dark select option:focus,
        .dark select option:hover,
        .dark select option:focus {
            background-color: #2C2C2E !important;
            color: #FFFFFF !important;
        }

        html.dark select option:disabled,
        .dark select option:disabled {
            background-color: #1C1C1E !important;
            color: rgba(235, 235, 245, 0.38) !important;
        }

        /* macOS Source List vibrancy sidebar */
        .sidebar-material {
            background: rgba(242, 242, 247, 0.85);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
        }

        .dark .sidebar-material {
            background: rgba(28, 28, 30, 0.85);
        }

        /* Toolbar material */
        .toolbar-material {
            background: rgba(242, 242, 247, 0.78);
            backdrop-filter: blur(20px) saturate(190%);
            -webkit-backdrop-filter: blur(20px) saturate(190%);
        }

        .dark .toolbar-material {
            background: rgba(22, 22, 24, 0.82);
        }

        /* Thick material for sheets/modals */
        .sheet-material {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .dark .sheet-material {
            background: rgba(36, 36, 38, 0.96);
        }

        /* Mobile anti-auto-zoom requirement (font min 16px on mobile inputs) */
        @media (max-width: 640px) {

            input,
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Apple Alert Dialog Styling for SweetAlert2 */
        .swal2-popup {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-radius: 18px !important;
            color: #000000 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
            width: 320px !important;
            padding: 1.25rem !important;
        }

        .dark .swal2-popup {
            background: rgba(44, 44, 46, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #FFFFFF !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Sleek Apple-style scrollbar for macOS Source List sidebar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
        }

        .dark .sidebar-scroll {
            scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 9999px;
        }

        .dark .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        /* Utility for Apple HIG horizontal swipe tabs without scrollbar blowout */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')

    @stack('styles')
    @stack('head')
</head>

@php
    $currentAdmin = Auth::guard('admin')->user();
    $pendingSubscriptionsCount = \App\Models\SubscriptionPayment::where('status', 'awaiting_approval')->count();
    $pendingRecoveriesCount = \App\Models\AccountRecoveryRequest::where(
        'status',
        \App\Models\AccountRecoveryRequest::STATUS_PENDING,
    )->count();
    $pendingSettlementsBadge = \App\Models\PaymentSettlement::where(
        'status',
        \App\Models\PaymentSettlement::STATUS_PENDING,
    )->count();
    try {
        $adminUnreadInboxCount = \App\Models\SocialMediaComment::where('is_platform', true)->where('status', 'unread')->count();
    } catch (\Throwable $e) {
        $adminUnreadInboxCount = 0;
    }

    $allModules = [
        // Ringkasan & Tenant
        ['perm' => 'admin.dashboard.view', 'title' => 'Dashboard Beranda', 'desc' => 'Ringkasan performa & metrik platform', 'url' => route('admin.dashboard'), 'icon' => 'layout-dashboard', 'cat' => 'Tenant'],
        ['perm' => 'admin.businesses.view', 'title' => 'Bisnis & Tenant UMKM', 'desc' => 'Daftar workspace UMKM & status akun tenant', 'url' => route('admin.businesses.index'), 'icon' => 'building-2', 'cat' => 'Tenant'],
        ['perm' => 'admin.users.view', 'title' => 'Basis Pengguna', 'desc' => 'Akun terdaftar, pemilik usaha & staf kasir', 'url' => route('admin.users.index'), 'icon' => 'users', 'cat' => 'Tenant'],
        ['perm' => 'admin.account_recoveries.view', 'title' => 'Pemulihan Akun Bisnis', 'desc' => 'Verifikasi identitas & reset akses tenant', 'url' => route('admin.account-recoveries.index'), 'icon' => 'shield-alert', 'cat' => 'Tenant'],

        // Keuangan & Penjualan
        ['perm' => 'admin.finance.view', 'title' => 'Buku Kas & Keuangan', 'desc' => 'Arus kas platform, laba rugi & rekonsiliasi', 'url' => route('admin.finance.index'), 'icon' => 'wallet', 'cat' => 'Keuangan'],
        ['perm' => 'admin.b2b.view', 'title' => 'Penjualan B2B & Faktur', 'desc' => 'Penawaran, SO, faktur komersial, retur & cetak PDF', 'url' => route('admin.b2b.index'), 'icon' => 'briefcase', 'cat' => 'Keuangan'],
        ['perm' => 'admin.subscriptions.view', 'title' => 'Langganan & Billing', 'desc' => 'Approval bukti bayar paket langganan SaaS', 'url' => route('admin.subscriptions.index'), 'icon' => 'receipt', 'cat' => 'Keuangan'],
        ['perm' => 'admin.settlements.view', 'title' => 'Pencairan Merchant', 'desc' => 'Verifikasi transfer settlement saldo merchant', 'url' => route('admin.settlements.index'), 'icon' => 'arrow-down-to-dot', 'cat' => 'Keuangan'],
        ['perm' => 'admin.billing_packages.view', 'title' => 'Paket & Harga', 'desc' => 'Katalog tier berlangganan & kuota AI', 'url' => route('admin.billing-packages.index'), 'icon' => 'layers-3', 'cat' => 'Keuangan'],
        ['perm' => 'admin.promos.view', 'title' => 'Kode Promo & Voucher', 'desc' => 'Kupon diskon langganan & voucher promosi', 'url' => \Illuminate\Support\Facades\Route::has('admin.promos.index') ? route('admin.promos.index') : url('/admin/promos'), 'icon' => 'ticket-percent', 'cat' => 'Keuangan'],
        ['perm' => 'admin.payment_accounts.view', 'title' => 'Rekening Platform', 'desc' => 'Rekening tujuan pembayaran resmi Cooca', 'url' => route('admin.payment-accounts.index'), 'icon' => 'credit-card', 'cat' => 'Keuangan'],

        // Komunikasi & Layanan
        ['perm' => 'admin.inbox.view', 'title' => 'Kotak Masuk (Inbox)', 'desc' => 'Omnichannel WhatsApp, Instagram & komentar', 'url' => route('admin.inbox.index'), 'icon' => 'inbox', 'cat' => 'Layanan'],
        ['perm' => 'admin.whatsapp.view', 'title' => 'WhatsApp Gateway', 'desc' => 'Status server WhatsApp OTP & notifikasi resmi', 'url' => route('admin.whatsapp.index'), 'icon' => 'message-circle', 'cat' => 'Layanan'],
        ['perm' => 'admin.social_media.view', 'title' => 'Media Sosial Platform', 'desc' => 'Konfigurasi integrasi Meta & TikTok', 'url' => route('admin.social-media.index'), 'icon' => 'share-2', 'cat' => 'Layanan'],
        ['perm' => 'admin.feedback.view', 'title' => 'Feedback & Bug', 'desc' => 'Laporan kendala & ide fitur dari tenant', 'url' => route('admin.feedback.bugs.index'), 'icon' => 'messages-square', 'cat' => 'Layanan'],

        // Pemasaran & Konten
        ['perm' => 'admin.leads.view', 'title' => 'Database Leads Prospek', 'desc' => 'Database prospek & kontak calon merchant', 'url' => route('admin.leads.index'), 'icon' => 'users-round', 'cat' => 'Pemasaran'],
        ['perm' => 'admin.events.view', 'title' => 'Event Komunitas UMKM', 'desc' => 'Meetup, seminar, workshop & absensi peserta', 'url' => route('admin.events.index'), 'icon' => 'calendar', 'cat' => 'Pemasaran'],
        ['perm' => 'admin.posts.view', 'title' => 'Artikel Blog & Edukasi', 'desc' => 'CMS artikel publikasi & edukasi bisnis', 'url' => route('admin.posts.index'), 'icon' => 'file-text', 'cat' => 'Pemasaran'],
        ['perm' => 'admin.templates.view', 'title' => 'Template Excel Master', 'desc' => 'Katalog spreadsheet akuntansi & stok UMKM', 'url' => route('admin.templates.index'), 'icon' => 'file-spreadsheet', 'cat' => 'Pemasaran'],
        ['perm' => 'admin.word_filters.view', 'title' => 'Moderasi Kata Terlarang', 'desc' => 'Filter kata judi online, scam & konten terlarang', 'url' => route('admin.word-filters.index'), 'icon' => 'shield-ban', 'cat' => 'Pemasaran'],
        // Sistem & Keamanan
        ['perm' => 'admin.roles.view', 'title' => 'Hak Akses & Peran (RBAC)', 'desc' => 'Peran administrator, matriks izin & staf', 'url' => route('admin.roles.index'), 'icon' => 'shield-check', 'cat' => 'Sistem'],
        ['perm' => 'admin.ai_tokens.view', 'title' => 'AI Engine & Token Console', 'desc' => 'Kelola engine multi-provider AI & pantau konsumsi token platform', 'url' => route('admin.ai-tokens.index'), 'icon' => 'sparkles', 'cat' => 'Sistem'],
        ['perm' => 'admin.settings.view', 'title' => 'Pengaturan Sistem', 'desc' => 'Konfigurasi Google OAuth, TriPay & SMTP', 'url' => route('admin.settings.index'), 'icon' => 'sliders', 'cat' => 'Sistem'],
        ['perm' => 'admin.error_logs.view', 'title' => 'Log Error & Diagnostik', 'desc' => 'Pemantauan runtime exception & debug log', 'url' => route('admin.error-logs.index'), 'icon' => 'terminal', 'cat' => 'Sistem'],
        ['perm' => 'admin.profile.view', 'title' => 'Profil Administrator', 'desc' => 'Kelola identitas akun & ubah kata sandi', 'url' => route('admin.profile.index'), 'icon' => 'key-round', 'cat' => 'Sistem'],
    ];
    $allowedSpotlightModules = array_values(array_filter($allModules, function ($m) use ($currentAdmin) {
        return $currentAdmin?->hasPermission($m['perm']);
    }));
@endphp

<body
    class="min-h-screen bg-[#F4F4F7] dark:bg-[#161618] text-black dark:text-white antialiased flex flex-col overflow-x-hidden"
    x-data="{
        sidebarOpen: false,
        quickActionOpen: false,
        spotlightOpen: false,
        profileDropdownOpen: false,
        themeDropdownOpen: false,
        isFullscreen: false,
        theme: localStorage.getItem('cooca-admin-theme') || localStorage.getItem('cooca-theme') || 'light',
        applyTheme(value) {
            this.theme = value;
            localStorage.setItem('cooca-admin-theme', value);
            localStorage.setItem('cooca-theme', value);
            const dark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = value;
            this.themeDropdownOpen = false;
        },
        toggleFullscreen() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                const docEl = document.documentElement;
                if (docEl.requestFullscreen) {
                    docEl.requestFullscreen().catch(() => {});
                } else if (docEl.webkitRequestFullscreen) {
                    docEl.webkitRequestFullscreen();
                } else if (docEl.mozRequestFullScreen) {
                    docEl.mozRequestFullScreen();
                } else if (docEl.msRequestFullscreen) {
                    docEl.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        },
        searchQuery: '',
        modules: {{ \Illuminate\Support\Js::from($allowedSpotlightModules) }},
        get filteredModules() {
            if (!this.searchQuery.trim()) return this.modules;
            const q = this.searchQuery.toLowerCase();
            return this.modules.filter(m => m.title.toLowerCase().includes(q) || m.desc.toLowerCase().includes(q) || m.cat.toLowerCase().includes(q));
        },
        openSpotlight() {
            this.spotlightOpen = true;
            this.searchQuery = '';
            this.$nextTick(() => {
                document.getElementById('admin-spotlight-input')?.focus();
                lucide.createIcons();
            });
        },
        init() {
            const handleFs = () => {
                this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
            };
            document.addEventListener('fullscreenchange', handleFs);
            document.addEventListener('webkitfullscreenchange', handleFs);
            document.addEventListener('mozfullscreenchange', handleFs);
            document.addEventListener('MSFullscreenChange', handleFs);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.theme === 'system') {
                    document.documentElement.classList.toggle('dark', e.matches);
                }
            });
            this.$watch('searchQuery', () => {
                this.$nextTick(() => lucide.createIcons());
            });
        }
    }" @keydown.window.cmd.k.prevent="openSpotlight()"
    @keydown.window.ctrl.k.prevent="openSpotlight()"
    @keydown.escape.window="spotlightOpen = false; quickActionOpen = false; profileDropdownOpen = false; themeDropdownOpen = false">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/35 backdrop-blur-[3px] lg:hidden"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;"></div>

    <!-- Admin Sidebar (macOS Source List vibrancy) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 bottom-0 left-0 z-50 w-72 sidebar-material border-r border-black/[0.06] dark:border-white/[0.08] transition-transform duration-300 ease-out lg:translate-x-0 flex flex-col shadow-sm">

        <div class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll px-3 pt-4 pb-4">
            <!-- Brand Banner -->
            <div
                class="flex items-center justify-between px-2 pb-3.5 border-b border-black/[0.04] dark:border-white/[0.06]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group min-w-0">
                    @php
                        $adminLogoDark = \App\Models\SystemSetting::get('site_logo_dark');
                        $adminLogoLight = \App\Models\SystemSetting::get('site_logo_light');
                        $adminLogoDarkUrl =
                            \App\Domain\Storage\AdminStorage::publicUrl($adminLogoDark) ??
                            asset('assets/image/1785229034_logo_dark.png');
                        $adminLogoLightUrl =
                            \App\Domain\Storage\AdminStorage::publicUrl($adminLogoLight) ??
                            asset('assets/image/1785229034_logo_dark.png');
                        $adminAppName = \App\Models\SystemSetting::get('app_name', 'Cooca');
                    @endphp
                    <div class="h-8 max-w-[130px] flex items-center shrink-0">
                        <img src="{{ $adminLogoLightUrl }}" alt="{{ $adminAppName }}"
                            class="h-7 w-auto object-contain dark:hidden">
                        <img src="{{ $adminLogoDarkUrl }}" alt="{{ $adminAppName }}"
                            class="h-7 w-auto object-contain hidden dark:block">
                    </div>
                    <div class="border-l border-black/10 dark:border-white/10 pl-2 leading-tight min-w-0">
                        <span
                            class="font-extrabold text-[13px] tracking-tight text-black dark:text-white truncate block">Admin</span>
                        <span
                            class="text-[10px] font-medium text-black/45 dark:text-white/45 truncate block">Operations</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false"
                    class="lg:hidden p-2 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10 transition-colors shrink-0"
                    aria-label="Tutup navigasi">
                    <i data-lucide="x" class="w-4.5 h-4.5" stroke-width="2"></i>
                </button>
            </div>

            <!-- Navigation Links (Apple HIG 5-Pillar Clean Architecture - Uniform Blue Monochromatic Icons) -->
            <nav class="space-y-0.5 pt-2.5">
                @php
                    $currentAdmin = Auth::guard('admin')->user();
                @endphp

                <!-- ========================================== -->
                <!-- Group 1: Ringkasan & Tenant                -->
                <!-- ========================================== -->
                @if ($currentAdmin?->hasAnyPermission(['admin.dashboard.view', 'admin.businesses.view', 'admin.users.view', 'admin.account_recoveries.view']))
                    <div class="px-3 pt-2 pb-1 text-[10.5px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                        Ringkasan &amp; Tenant
                    </div>

                    @if ($currentAdmin?->hasPermission('admin.dashboard.view'))
                        @php $isActive = request()->routeIs('admin.dashboard'); @endphp
                        <a href="{{ route('admin.dashboard') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Dashboard Beranda</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.businesses.view'))
                        @php $isActive = request()->routeIs('admin.businesses.*'); @endphp
                        <a href="{{ route('admin.businesses.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="building-2" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Bisnis &amp; Tenant UMKM</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.users.view'))
                        @php $isActive = request()->routeIs('admin.users.*'); @endphp
                        <a href="{{ route('admin.users.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="users" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Basis Pengguna</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.account_recoveries.view'))
                        @php $isActive = request()->routeIs('admin.account-recoveries.*'); @endphp
                        <a href="{{ route('admin.account-recoveries.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Pemulihan Akun</span>
                            </div>
                            @if ($pendingRecoveriesCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shadow-xs tabular-nums shrink-0 ml-2 {{ $isActive ? 'bg-white text-[#007AFF]' : 'bg-[#FF9500] text-white' }}">
                                    {{ $pendingRecoveriesCount }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endif

                <!-- ========================================== -->
                <!-- Group 2: Keuangan & Penjualan              -->
                <!-- ========================================== -->
                @if ($currentAdmin?->hasAnyPermission(['admin.finance.view', 'admin.b2b.view', 'admin.subscriptions.view', 'admin.settlements.view', 'admin.billing_packages.view', 'admin.promos.view', 'admin.payment_accounts.view']))
                    <div class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                        Keuangan &amp; Penjualan
                    </div>

                    @if ($currentAdmin?->hasPermission('admin.finance.view'))
                        @php $isActive = request()->routeIs('admin.finance.*'); @endphp
                        <a href="{{ route('admin.finance.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="wallet" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Buku Kas &amp; Keuangan</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.b2b.view'))
                        @php $isActive = request()->routeIs('admin.b2b.*'); @endphp
                        <a href="{{ route('admin.b2b.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="briefcase" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Penjualan B2B &amp; Faktur</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.subscriptions.view'))
                        @php $isActive = request()->routeIs('admin.subscriptions.*'); @endphp
                        <a href="{{ route('admin.subscriptions.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="receipt" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Langganan &amp; Billing</span>
                            </div>
                            @if ($pendingSubscriptionsCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shadow-xs tabular-nums shrink-0 ml-2 {{ $isActive ? 'bg-white text-[#007AFF]' : 'bg-[#FF3B30] text-white' }}">
                                    {{ $pendingSubscriptionsCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.settlements.view'))
                        @php $isActive = request()->routeIs('admin.settlements.*'); @endphp
                        <a href="{{ route('admin.settlements.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="arrow-down-to-dot" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Pencairan Merchant</span>
                            </div>
                            @if ($pendingSettlementsBadge > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shadow-xs tabular-nums shrink-0 ml-2 {{ $isActive ? 'bg-white text-[#007AFF]' : 'bg-[#FF9500] text-white' }}">
                                    {{ $pendingSettlementsBadge }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.billing_packages.view'))
                        @php $isActive = request()->routeIs('admin.billing-packages.*'); @endphp
                        <a href="{{ route('admin.billing-packages.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="layers-3" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Paket &amp; Harga</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.promos.view'))
                        @php $isActive = request()->routeIs('admin.promos.*') || request()->is('admin/promos*'); @endphp
                        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.promos.index') ? route('admin.promos.index') : url('/admin/promos') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="ticket-percent" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Kode Promo &amp; Voucher</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.payment_accounts.view'))
                        @php $isActive = request()->routeIs('admin.payment-accounts.*'); @endphp
                        <a href="{{ route('admin.payment-accounts.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="credit-card" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Rekening Platform</span>
                            </div>
                        </a>
                    @endif
                @endif

                <!-- ========================================== -->
                <!-- Group 3: Komunikasi & Layanan              -->
                <!-- ========================================== -->
                @if ($currentAdmin?->hasAnyPermission(['admin.inbox.view', 'admin.whatsapp.view', 'admin.social_media.view', 'admin.feedback.view']))
                    <div class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                        Komunikasi &amp; Layanan
                    </div>

                    @if ($currentAdmin?->hasPermission('admin.inbox.view'))
                        @php $isActive = request()->routeIs('admin.inbox.*'); @endphp
                        <a href="{{ route('admin.inbox.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="inbox" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Kotak Masuk (Inbox)</span>
                            </div>
                            @if ($adminUnreadInboxCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shadow-xs tabular-nums shrink-0 ml-2 {{ $isActive ? 'bg-white text-[#007AFF]' : 'bg-[#007AFF] text-white' }}">
                                    {{ $adminUnreadInboxCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.whatsapp.view'))
                        @php $isActive = request()->routeIs('admin.whatsapp.*'); @endphp
                        <a href="{{ route('admin.whatsapp.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="message-circle" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">WhatsApp Gateway</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.social_media.view'))
                        @php $isActive = request()->routeIs('admin.social-media.*'); @endphp
                        <a href="{{ route('admin.social-media.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="share-2" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Media Sosial Platform</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.feedback.view'))
                        @php $isActive = request()->routeIs('admin.feedback.*'); @endphp
                        <a href="{{ route('admin.feedback.bugs.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="messages-square" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Feedback &amp; Bug</span>
                            </div>
                        </a>
                    @endif
                @endif

                <!-- ========================================== -->
                <!-- Group 4: Pemasaran & Konten                -->
                <!-- ========================================== -->
                @if ($currentAdmin?->hasAnyPermission(['admin.leads.view', 'admin.events.view', 'admin.posts.view', 'admin.templates.view', 'admin.word_filters.view', 'admin.legal_pages.view']))
                    <div class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                        Pemasaran &amp; Konten
                    </div>

                    @if ($currentAdmin?->hasPermission('admin.leads.view'))
                        @php $isActive = request()->routeIs('admin.leads.*'); @endphp
                        <a href="{{ route('admin.leads.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="users-round" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Database Leads Prospek</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.events.view'))
                        @php $isActive = request()->routeIs('admin.events.*'); @endphp
                        <a href="{{ route('admin.events.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="calendar" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Event Komunitas UMKM</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.posts.view'))
                        @php $isActive = request()->routeIs('admin.posts.*'); @endphp
                        <a href="{{ route('admin.posts.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="file-text" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Artikel Blog &amp; Edukasi</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.templates.view'))
                        @php $isActive = request()->routeIs('admin.templates.*'); @endphp
                        <a href="{{ route('admin.templates.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Template Excel Master</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.word_filters.view'))
                        @php $isActive = request()->routeIs('admin.word-filters.*'); @endphp
                        <a href="{{ route('admin.word-filters.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="shield-ban" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Moderasi Kata Terlarang</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.legal_pages.view'))
                        @php $isActive = request()->routeIs('admin.legal-pages.*'); @endphp
                        <a href="{{ route('admin.legal-pages.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="scale" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Kebijakan &amp; Legalitas</span>
                            </div>
                        </a>
                    @endif
                @endif

                <!-- ========================================== -->
                <!-- Group 5: Sistem & Keamanan                 -->
                <!-- ========================================== -->
                @if ($currentAdmin?->hasAnyPermission(['admin.roles.view', 'admin.ai_tokens.view', 'admin.settings.view', 'admin.smtp.view', 'admin.error_logs.view']))
                    <div class="px-3 pt-3.5 pb-1 text-[10.5px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                        Sistem &amp; Keamanan
                    </div>

                    @if ($currentAdmin?->hasPermission('admin.roles.view'))
                        @php $isActive = request()->routeIs('admin.roles.*'); @endphp
                        <a href="{{ route('admin.roles.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Hak Akses &amp; Peran</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.ai_tokens.view'))
                        @php $isActive = request()->routeIs('admin.ai-tokens.*'); @endphp
                        <a href="{{ route('admin.ai-tokens.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="sparkles" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">AI Engine &amp; Tokens</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.settings.view'))
                        @php $isActive = request()->routeIs('admin.settings.*') || request()->routeIs('admin.smtp.*'); @endphp
                        <a href="{{ route('admin.settings.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="sliders" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Pengaturan Sistem</span>
                            </div>
                        </a>
                    @endif

                    @if ($currentAdmin?->hasPermission('admin.error_logs.view'))
                        @php $isActive = request()->routeIs('admin.error-logs.*'); @endphp
                        <a href="{{ route('admin.error-logs.index') }}"
                            class="group flex items-center justify-between px-2.5 h-[38px] rounded-[11px] transition-all duration-150 {{ $isActive ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold' : 'text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105' }}">
                                    <i data-lucide="terminal" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }}" stroke-width="2"></i>
                                </div>
                                <span class="whitespace-nowrap truncate min-w-0 flex-1 text-[13px] tracking-tight">Log Error &amp; Diagnostik</span>
                            </div>
                        </a>
                    @endif
                @endif
            </nav>
        </div>

        <!-- Admin Profile Footer Bento Inset -->
        <div class="p-3 border-t border-black/[0.04] dark:border-white/[0.06]">
            <a href="{{ route('admin.profile.index') }}"
                class="flex items-center justify-between gap-2.5 p-2 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] hover:bg-black/5 dark:hover:bg-white/10 active:scale-[0.99] transition-all group"
                title="Kelola Profil & Ganti Kata Sandi">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <div
                        class="w-8 h-8 rounded-[9px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate flex-1 min-w-0 text-left">
                        <div class="text-[12.5px] font-bold text-black dark:text-white truncate">
                            {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[10.5px] font-medium text-[#8E8E93] dark:text-[#98989D] truncate">
                            {{ Auth::guard('admin')->user()->email ?? '' }}
                        </div>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] group-hover:translate-x-0.5 transition-all shrink-0"></i>
            </a>
        </div>
    </aside>

    <!-- ============================================================ -->
    <!-- Topbar / Toolbar - FIXED positioning (cross-browser reliable) -->
    <!-- Must be OUTSIDE any overflow container to function correctly -->
    <!-- ============================================================ -->
    <header
        class="fixed top-0 left-0 right-0 lg:left-72 z-30 toolbar-material border-b border-black/[0.06] dark:border-white/[0.08] px-3.5 sm:px-6 h-[64px] sm:h-[70px] py-2 flex items-center justify-between gap-3 sm:gap-4 min-w-0">
        <!-- Left Cluster: Mobile Menu Trigger + Title & Subtitle -->
        <div class="flex items-center gap-3 min-w-0 py-1">
            <button @click="sidebarOpen = true"
                class="lg:hidden w-10 h-10 -ml-1 rounded-[10px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all flex items-center justify-center shrink-0 cursor-pointer"
                aria-label="Buka navigasi">
                <i data-lucide="menu" class="w-5 h-5" stroke-width="2"></i>
            </button>
            <div class="min-w-0 flex flex-col justify-center">
                <h1
                    class="text-[17px] sm:text-[18px] font-extrabold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight truncate leading-snug">
                    {{ $headerTitle ?? 'Admin Console' }}
                </h1>
                <p class="text-[11px] text-black/50 dark:text-white/50 truncate hidden md:block leading-none mt-0.5">
                    {{ $headerSubtitle ?? 'Pusat Manajemen Sistem Cooca (cooca.id)' }}
                </p>
            </div>
        </div>

        <!-- Right Cluster: Search Pill + Control Capsule (No Multi-Lang) + Divider + User Profile Dropdown -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Global Spotlight Search Trigger Pill (Desktop & Tablet) -->
            <button type="button" @click="openSpotlight()"
                class="hidden md:flex items-center justify-between gap-2 px-3.5 h-9 w-64 lg:w-80 rounded-full bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-all cursor-pointer text-[12.5px] group">
                <div class="flex items-center gap-2 truncate">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors shrink-0"></i>
                    <span class="truncate text-black/55 dark:text-white/55 text-[12px]">Cari menu, produk, atau transaksi (Ctrl+K)...</span>
                </div>
                <kbd class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shrink-0 shadow-2xs">
                    Ctrl K
                </kbd>
            </button>

            <!-- Mobile Search Trigger Button (< md) -->
            <button type="button" @click="openSpotlight()"
                class="md:hidden w-9 h-9 rounded-full text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 flex items-center justify-center transition-all cursor-pointer shrink-0"
                title="Cari menu, produk, atau transaksi (Ctrl+K)" aria-label="Cari">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>

            <!-- Unified System Controls Capsule (Desktop / Tablet sm+) - STRICTLY NO MULTI-LANGUAGE -->
            <div class="hidden sm:flex items-center p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 gap-0.5">
                <!-- Fullscreen Toggle Button -->
                <button type="button" @click="toggleFullscreen()"
                    :title="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"
                    aria-label="Toggle Fullscreen"
                    class="flex h-7 w-7 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/70 dark:hover:bg-white/10 transition-all items-center justify-center shrink-0 active:scale-95 cursor-pointer">
                    <i x-show="!isFullscreen" data-lucide="maximize" class="w-3.5 h-3.5"></i>
                    <i x-show="isFullscreen" data-lucide="minimize" class="w-3.5 h-3.5 text-[#007AFF]" style="display: none;"></i>
                </button>

                <!-- Theme Switcher (Light / Dark / System) -->
                <div class="relative" @click.outside="themeDropdownOpen = false" @keydown.esc.window="themeDropdownOpen = false">
                    <button type="button" @click="themeDropdownOpen = !themeDropdownOpen"
                        :aria-expanded="themeDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                        :title="'Ganti Tema: ' + (theme === 'dark' ? 'Gelap' : (theme === 'system' ? 'Sistem' : 'Terang'))"
                        aria-label="Ganti Tema"
                        class="h-7 w-7 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/70 dark:hover:bg-white/10 transition-all flex items-center justify-center shrink-0 active:scale-95 cursor-pointer">
                        <span x-show="theme === 'light'">
                            <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        </span>
                        <span x-show="theme === 'dark'" style="display: none;">
                            <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                        </span>
                        <span x-show="theme === 'system'" style="display: none;">
                            <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        </span>
                    </button>

                    <!-- Theme Dropdown Menu (Apple Glass Squircle) -->
                    <div x-show="themeDropdownOpen" x-transition role="menu"
                        class="absolute right-0 mt-2 w-36 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-[13px]"
                        style="display: none;">
                        <button type="button" @click="applyTheme('light')" role="menuitem"
                            class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                            :class="theme === 'light' ? 'bg-[#FF9500]/10 text-[#FF9500] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                            <div class="flex items-center gap-2">
                                <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>Terang</span>
                            </div>
                            <span x-show="theme === 'light'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                        </button>
                        <button type="button" @click="applyTheme('dark')" role="menuitem"
                            class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                            :class="theme === 'dark' ? 'bg-[#5856D6]/10 text-[#5856D6] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                            <div class="flex items-center gap-2">
                                <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>Gelap</span>
                            </div>
                            <span x-show="theme === 'dark'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                        </button>
                        <button type="button" @click="applyTheme('system')" role="menuitem"
                            class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                            :class="theme === 'system' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                            <div class="flex items-center gap-2">
                                <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Sistem</span>
                            </div>
                            <span x-show="theme === 'system'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hairline Vertical Separator -->
            <div class="h-4 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>

            <!-- User Profile Dropdown Pill (Apple macOS / iOS Account Menu) -->
            <div class="relative" @click.outside="profileDropdownOpen = false" @keydown.esc.window="profileDropdownOpen = false">
                <button type="button" @click="profileDropdownOpen = !profileDropdownOpen"
                    :aria-expanded="profileDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                    class="flex items-center gap-2 p-0.5 sm:p-1 sm:pl-1 sm:pr-2.5 rounded-full hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition-all active:scale-95 cursor-pointer"
                    title="Menu Administrator: {{ Auth::guard('admin')->user()->name ?? 'Agung Mustaqim' }}">
                    <!-- Squircle Avatar -->
                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                        {{ substr(Auth::guard('admin')->user()->name ?? 'Ag', 0, 2) }}
                    </div>
                    <div class="text-left hidden md:block max-w-[140px]">
                        <div class="text-[13px] font-bold text-black dark:text-white truncate leading-tight">
                            {{ Auth::guard('admin')->user()->name ?? 'Agung Mustaqim' }}
                        </div>
                        <div class="text-[11px] font-medium text-black/50 dark:text-white/50 truncate leading-tight">
                            Cooca Indonesia
                        </div>
                    </div>
                    <i data-lucide="chevron-down"
                        class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0 hidden sm:block"
                        :class="profileDropdownOpen ? 'rotate-180' : ''"></i>
                </button>

                <!-- Profile Popover Sheet (macOS Sonoma / Apple HIG Style) -->
                <div x-show="profileDropdownOpen" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    role="menu"
                    class="absolute right-0 mt-2 w-64 rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.18)] p-2 z-50 space-y-1 text-[13px]"
                    style="display: none;">

                    <!-- Admin Details Header -->
                    <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#007AFF] dark:text-[#0A84FF] font-bold text-sm flex items-center justify-center shrink-0">
                            {{ substr(Auth::guard('admin')->user()->name ?? 'Ag', 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-black dark:text-white truncate text-[13px]">
                                {{ Auth::guard('admin')->user()->name ?? 'Agung Mustaqim' }}
                            </p>
                            <p class="text-[11px] text-black/50 dark:text-white/50 truncate">
                                {{ Auth::guard('admin')->user()->email ?? 'admin@cooca.id' }}
                            </p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                    {{ Auth::guard('admin')->user()?->roles?->pluck('name')->first() ?? 'Administrator' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="space-y-0.5 pt-1">
                        <a href="{{ route('admin.profile.index') }}" @click="profileDropdownOpen = false"
                            class="w-full px-2.5 py-2 rounded-[8px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2.5 transition active:scale-[0.98]">
                            <i data-lucide="user" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="font-medium">Profil Administrator</span>
                        </a>

                        @if (Auth::guard('admin')->user()?->hasPermission('admin.settings.view'))
                            <a href="{{ route('admin.settings.index') }}" @click="profileDropdownOpen = false"
                                class="w-full px-2.5 py-2 rounded-[8px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2.5 transition active:scale-[0.98]">
                                <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                <span class="font-medium">Pengaturan Sistem</span>
                            </a>
                        @endif

                        <a href="{{ route('landing') }}" target="_blank" @click="profileDropdownOpen = false"
                            class="w-full px-2.5 py-2 rounded-[8px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center justify-between gap-2.5 transition active:scale-[0.98]">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i data-lucide="external-link" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                <span class="font-medium truncate">Website Publik</span>
                            </div>
                            <span class="text-[10px] text-black/40 dark:text-white/40">Tab Baru</span>
                        </a>
                    </div>

                    <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>

                    <!-- Logout Action Button -->
                    <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full px-2.5 py-2 rounded-[8px] text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/15 flex items-center gap-2.5 transition active:scale-[0.98] font-semibold text-left cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i>
                            <span>Keluar (Logout)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area (lg:pl-72 for sidebar offset, pt for fixed header height) -->
    <div class="lg:pl-72 flex flex-col flex-1 min-h-screen min-w-0 w-full pt-[64px] sm:pt-[72px]">

        <!-- macOS Sonoma Spotlight Quick Navigator Modal -->
        <template x-teleport="body">
            <div x-show="spotlightOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" aria-labelledby="spotlight-title" role="dialog"
                aria-modal="true" @keydown.escape.window="spotlightOpen = false">
                <div x-show="spotlightOpen" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="spotlightOpen = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    aria-hidden="true"></div>

                <div class="relative z-10 min-h-full flex items-start justify-center p-4 pt-16 sm:pt-24 pointer-events-none" @click.self="spotlightOpen = false">
                    <div x-show="spotlightOpen" x-transition:enter="ease-out duration-200 transform"
                        x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                        x-transition:leave="ease-in duration-150 transform"
                        x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0"
                        @click.outside="spotlightOpen = false"
                        class="relative pointer-events-auto w-full max-w-xl rounded-[22px] sheet-material border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden divide-y divide-black/[0.06] dark:divide-white/[0.08]">

                    <!-- Search Input Bar -->
                    <div class="p-3.5 flex items-center gap-3">
                        <i data-lucide="search" class="w-5 h-5 text-[#007AFF] shrink-0" stroke-width="2"></i>
                        <input type="text" id="admin-spotlight-input" x-model="searchQuery"
                            placeholder="Ketik untuk mencari modul sistem..."
                            class="w-full bg-transparent border-none outline-none text-black dark:text-white text-[15px] placeholder-black/40 dark:placeholder-white/40 font-medium">
                        <button type="button" @click="spotlightOpen = false"
                            class="px-2 py-1 rounded-[8px] text-[11px] font-semibold text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10">
                            ESC
                        </button>
                    </div>

                    <!-- Module Items List -->
                    <div class="max-h-[380px] overflow-y-auto sidebar-scroll p-2 space-y-1">
                        <template x-for="item in filteredModules" :key="item.url">
                            <a :href="item.url"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-[12px] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/15 text-black dark:text-white group transition-colors">
                                <div
                                    class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                                    <i :data-lucide="item.icon" class="w-4 h-4" stroke-width="1.8"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[13.5px] font-bold group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors truncate"
                                            x-text="item.title"></span>
                                        <span
                                            class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-black/[0.04] dark:bg-white/[0.06] text-black/45 dark:text-white/45"
                                            x-text="item.cat"></span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate"
                                        x-text="item.desc"></p>
                                </div>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] transition-colors shrink-0"></i>
                            </a>
                        </template>
                        <div x-show="filteredModules.length === 0"
                            class="py-8 text-center text-[13px] text-black/45 dark:text-white/45">
                            Tidak ada modul yang cocok dengan kata kunci pencarian.
                        </div>
                    </div>

                    <!-- Footer Hint -->
                    <div
                        class="px-4 py-2.5 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between text-[11px] text-black/45 dark:text-white/45">
                        <span>Navigasi Cepat Apple HIG</span>
                        <span>Tekan <kbd class="font-mono font-bold">ESC</kbd> untuk menutup</span>
                    </div>
                </div>
            </div>
        </div>
    </template>

        <!-- Flash Messages & Content Body Canvas -->
        {{--
            NOTE: session('success') and session('status') are intentionally
            omitted here. The AppAlert script block at the bottom of this layout
            fires toasts for these flash keys via session()->pull(). Showing them
            inline here AND as AppAlert toasts caused a double-notification bug
            (user saw a "dismiss popup" on every reload after a redirect).
            Individual views may still render their own inline flash banners.
        --}}
        <main
            class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-[1440px] w-full min-w-0 mx-auto space-y-5 sm:space-y-6 pb-28 lg:pb-12">
            @php
                $errorList = is_array($errors ?? null) ? $errors : (($errors ?? null) instanceof \Illuminate\Support\ViewErrorBag || ($errors ?? null) instanceof \Illuminate\Contracts\Support\MessageBag ? $errors->all() : []);
            @endphp
            @if (!empty($errorList))
                <div
                    class="rounded-[18px] px-5 py-4 bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-[13px] space-y-1.5 backdrop-blur-md shadow-sm">
                    <div class="font-bold flex items-center gap-2 text-[#C41E17] dark:text-[#FF453A]">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                        <span>Mohon Periksa Input Berikut:</span>
                    </div>
                    <ul
                        class="list-disc list-inside pl-2 space-y-0.5 text-[12px] font-medium text-[#C41E17]/90 dark:text-[#FF453A]/90">
                        @foreach ($errorList as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Clean Minimalist Hairline Footer (Desktop Mode) -->
        <footer
            class="hidden lg:flex border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px] text-[#8E8E93] dark:text-[#98989D] items-center justify-between mt-auto bg-transparent">
            <div class="flex items-center gap-2.5">
                <span>Pusat Operasi Superadmin &amp; Manajemen SaaS</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="tabular-nums">Cooca Platform Operations v2.0</span>
                <span>•</span>
                <span>&copy; {{ date('Y') }} Cooca Indonesia</span>
            </div>
        </footer>
    </div>

    <!-- Full-Style Floating Bottom Navigation Bar (Apple iOS 18) -->
    <nav aria-label="Navigasi Utama Mobile"
        class="fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 lg:hidden pb-[env(safe-area-inset-bottom)] transition-all duration-300">
        <div
            class="h-16 rounded-[24px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.08] dark:border-white/[0.12] shadow-2xl shadow-black/15 dark:shadow-black/60 px-2 sm:px-4 flex items-center justify-around relative">

            <!-- Item 1: Dashboard -->
            @if($currentAdmin?->hasPermission('admin.dashboard.view'))
            <a href="{{ route('admin.dashboard') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all {{ request()->routeIs('admin.dashboard') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.dashboard') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Beranda</span>
            </a>
            @endif

            <!-- Item 2: Bisnis (Tenants) -->
            @if($currentAdmin?->hasPermission('admin.businesses.view'))
            <a href="{{ route('admin.businesses.index') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all {{ request()->routeIs('admin.businesses.*') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="building-2" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.businesses.*') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Bisnis</span>
            </a>
            @endif

            <!-- Center Action Button: Elevated Center Trigger (Aksi Cepat Admin) -->
            @if($currentAdmin?->hasAnyPermission(['admin.businesses.view', 'admin.subscriptions.view', 'admin.finance.view', 'admin.users.view', 'admin.whatsapp.view', 'admin.social_media.view', 'admin.ai_tokens.view', 'admin.settings.view']))
            <button type="button" @click="quickActionOpen = true; $nextTick(() => lucide.createIcons())"
                aria-label="Aksi Cepat Admin"
                class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#0051D5] -mt-6 flex items-center justify-center text-white shadow-lg shadow-[#007AFF]/40 ring-4 ring-[#F4F4F7] dark:ring-[#161618] active:scale-95 transition-all cursor-pointer">
                <i data-lucide="sparkles" class="w-5 h-5 text-white" stroke-width="2.2"></i>
            </button>
            @endif

            <!-- Item 4: Billing & Subscriptions -->
            @if($currentAdmin?->hasPermission('admin.subscriptions.view'))
            <a href="{{ route('admin.subscriptions.index') }}"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] transition-all relative {{ request()->routeIs('admin.subscriptions.*') ? 'text-[#007AFF] font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="receipt" class="w-5 h-5"
                    stroke-width="{{ request()->routeIs('admin.subscriptions.*') ? '2.5' : '1.8' }}"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Billing</span>
                @if ($pendingSubscriptionsCount > 0)
                    <span
                        class="absolute top-1.5 right-2 w-2 h-2 rounded-full bg-[#FF9500] ring-2 ring-white dark:ring-[#1C1C1E]"></span>
                @endif
            </a>
            @endif

            <!-- Item 5: Menu Drawer Trigger -->
            <button type="button" @click="sidebarOpen = true" aria-label="Buka Menu Navigasi Lengkap"
                class="flex flex-col items-center justify-center min-w-[50px] h-12 rounded-[14px] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white active:scale-95 transition-all">
                <i data-lucide="menu" class="w-5 h-5" stroke-width="1.8"></i>
                <span class="text-[10px] tracking-tight mt-0.5">Menu</span>
            </button>
        </div>
    </nav>

    <!-- Admin Quick Action Bottom Sheet (iOS 18 Action Sheet) -->
    <div x-show="quickActionOpen" x-cloak class="relative z-50 lg:hidden" aria-labelledby="quick-action-title"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="quickActionOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="quickActionOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity">
        </div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end justify-center p-0 sm:p-4">
            <div x-show="quickActionOpen" x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0" @click.outside="quickActionOpen = false"
                class="w-full max-w-lg rounded-t-[28px] sm:rounded-[28px] sheet-material p-5 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-4">

                <!-- iOS Grabber Handle -->
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-1 mb-2 sm:hidden"></div>

                <!-- Header -->
                <div
                    class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                            <i data-lucide="sparkles" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="quick-action-title" class="text-[15px] font-bold text-black dark:text-white">Pusat
                                Aksi Cepat Admin</h3>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Pintas manajemen platform Cooca</p>
                        </div>
                    </div>
                    <button type="button" @click="quickActionOpen = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all"
                        aria-label="Tutup">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Quick Action Buttons List (Touch targets 48-52px) -->
                <div class="grid grid-cols-2 gap-2.5">
                    @if($currentAdmin?->hasPermission('admin.businesses.view'))
                    <a href="{{ route('admin.businesses.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="building-2" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Semua Tenant</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Kelola workspace UMKM</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.subscriptions.view'))
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 relative min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                            <i data-lucide="receipt" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        @if ($pendingSubscriptionsCount > 0)
                            <span
                                class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500] text-white shadow-sm tabular-nums">
                                {{ $pendingSubscriptionsCount }}
                            </span>
                        @endif
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Approval Billing</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Verifikasi bukti transfer</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.b2b.view'))
                    <a href="{{ route('admin.b2b.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="briefcase" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Penjualan B2B</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Penawaran, SO &amp; Faktur</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.finance.view'))
                    <a href="{{ route('admin.finance.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="wallet" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Buku Kas &amp; Keuangan</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Arus kas &amp; Laba Rugi</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.users.view'))
                    <a href="{{ route('admin.users.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#5856D6]/15 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                            <i data-lucide="users" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Basis Pengguna</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Akun owner &amp; kasir</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.whatsapp.view'))
                    <a href="{{ route('admin.whatsapp.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#25D366]/15 text-[#25D366] flex items-center justify-center">
                            <svg width="18" height="18" class="w-[18px] h-[18px] text-[#25D366] fill-current"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Dual WhatsApp</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Gateway OTP &amp; Notif</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.social_media.view'))
                    <a href="{{ route('admin.social-media.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center">
                            <i data-lucide="share-2" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Media Sosial</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Meta App &amp; Webhook</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.ai_tokens.view'))
                    <a href="{{ route('admin.ai-tokens.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Token AI Global</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Monitoring kuota AI</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.settings.view'))
                    <a href="{{ route('admin.settings.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                            <i data-lucide="sliders" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Pengaturan Platform</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">OAuth, TriPay, WA, Social &amp;
                                SMTP</div>
                        </div>
                    </a>
                    @endif

                    @if($currentAdmin?->hasPermission('admin.roles.view'))
                    <a href="{{ route('admin.roles.index') }}"
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all flex flex-col gap-2 min-h-[52px]">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4.5 h-4.5" stroke-width="1.8"></i>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Hak Akses &amp; Peran</div>
                            <div class="text-[10px] text-black/50 dark:text-white/50">Manajemen RBAC Platform</div>
                        </div>
                    </a>
                    @endif
                </div>

                <!-- Footer button -->
                <button type="button" @click="quickActionOpen = false"
                    class="w-full h-12 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all">
                    Tutup Menu Aksi
                </button>
            </div>
        </div>
    </div>

    @php
        $flashSuccess = session()->pull('success');
        $flashStatus = session()->pull('status');
        $flashError = session()->pull('error');
        $flashWarning = session()->pull('warning');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            @if ($flashSuccess)
                AppAlert.success(@json($flashSuccess));
            @endif
            @if ($flashStatus)
                AppAlert.info(@json($flashStatus));
            @endif
            @if ($flashError)
                AppAlert.error(@json($flashError));
            @endif
            @if ($flashWarning)
                AppAlert.warning(@json($flashWarning));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
