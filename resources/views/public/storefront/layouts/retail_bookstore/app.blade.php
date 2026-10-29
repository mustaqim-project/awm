{{-- ========================================================================= --}}
{{-- Bibliotheca (Toko Buku & Stationery) MASTER LAYOUT (`layouts/retail_bookstore/app.blade.php`)      --}}
{{-- Bespoke Buku, Alat Tulis, Musik & Perlengkapan Sekolah theme with Merriweather & Inter typography   --}}
{{-- ========================================================================= --}}
@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
    $bundleDir = 'retail_bookstore';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0B0F19' : ($activeTheme['bg_color'] ?? '#FFFBEB') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Persistent Elegant Dark Mode Initialization --}}
    <script>
        (function() {
            const savedDark = localStorage.getItem('storefront_dark_mode');
            if (savedDark === 'true') {
                document.documentElement.classList.add('dark');
            } else if (savedDark === 'false') {
                document.documentElement.classList.remove('dark');
            }
        })();

        window.toggleDarkMode = function() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            try {
                localStorage.setItem('storefront_dark_mode', isDark ? 'true' : 'false');
                const themeColorMeta = document.querySelector('meta[name="theme-color"]');
                if (themeColorMeta) {
                    themeColorMeta.setAttribute('content', isDark ? '{{ $activeTheme['dark_bg'] ?? '#0B0F19' }}' : '{{ $activeTheme['bg_color'] ?? '#FFFBEB' }}');
                }
            } catch (e) {}
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        };
    </script>

    {{-- Apple HIG Mobile Web App Compatibility & PWA Meta Tags (§web-ios-pwa-redesign) --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $business->name }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="format-detection" content="telephone=no">

    {{-- SEO & Social Media Metadata Architecture --}}
    @php
        $defaultBrandImg = $business->logo_url ?: ($landingPage->logo_url ?: $landingPage->hero_image_url);
    @endphp
    @include('public.partials.seo', [
        'siteName' => $business->name,
        'fallbackImage' => $defaultBrandImg ?: asset('assets/seo/cooca-og-default.jpg'),
        'url' => $canonicalUrl ?? (View::hasSection('canonical') ? trim((string) View::yieldContent('canonical')) : url('/' . $business->slug)),
        'title' => $pageTitle ?? (View::hasSection('title') ? trim((string) View::yieldContent('title')) : ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Toko Buku & Alat Tulis'))),
        'description' => $ogDescription ?? (View::hasSection('description') ? trim((string) View::yieldContent('description')) : ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Kunjungi toko online resmi ' . $business->name . '. Layanan Toko Buku & Alat Tulis tepercaya.')))),
        'keywords' => View::hasSection('keywords') ? trim((string) View::yieldContent('keywords')) : ($landingPage->meta_keywords ?? null),
    ])

    @stack('seo')

    @if (!empty($defaultBrandImg))
        <link rel="apple-touch-icon" href="{{ $defaultBrandImg }}">
    @endif

    {{-- Dynamic Google Fonts for Bibliotheca (Toko Buku & Stationery) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN & Bibliotheca (Toko Buku & Stationery) Tokens --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"Merriweather"', 'sans-serif'],
                        body: ['"Inter"', 'sans-serif'],
                        sans: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] ?? '#78350F' }}',
                            accent: '{{ $activeTheme['accent_color'] ?? '#D97706' }}',
                            bg: '{{ $activeTheme['bg_color'] ?? '#FFFBEB' }}',
                            card: '{{ $activeTheme['card_bg'] ?? '#FFFFFF' }}',
                            badgeBg: '{{ $activeTheme['badge_bg'] ?? '#78350F15' }}',
                            badgeText: '{{ $activeTheme['badge_text'] ?? '#78350F' }}',
                        }
                    },
                    borderRadius: {
                        'theme': '{{ $activeTheme['border_radius'] ?? '12px' }}',
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide Icons --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Global JavaScript Bridge: i18n & Context Injection --}}
    <script>
        window.COOCA_I18N = @js(__('storefront.messages'));
        window.COOCA_LANG = @js($currentLocale);
        window.COOCA_CURRENCY = 'IDR';
        window.COOCA_BUSINESS_ID = @js($business->id);
    </script>

    {{-- Bibliotheca (Toko Buku & Stationery) Dynamic CSS Tokens --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --theme-primary: {{ $activeTheme['primary_color'] ?? '#78350F' }};
            --theme-accent: {{ $activeTheme['accent_color'] ?? '#D97706' }};
            --theme-bg: {{ $activeTheme['bg_color'] ?? '#FFFBEB' }};
            --theme-card: {{ $activeTheme['card_bg'] ?? '#FFFFFF' }};
            --theme-radius: 12px;
            --font-heading: 'Merriweather', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
            --theme-bg: #0B0F19;
            --theme-card: #141A26;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--theme-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .dark body {
            background-color: var(--theme-bg);
        }

        h1, h2, h3, h4, .font-heading {
            font-family: var(--font-heading);
            letter-spacing: -0.015em;
        }

        .font-mono, .tabular-nums {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .theme-surface {
            background-color: var(--theme-card);
            border-radius: var(--theme-radius);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .dark .theme-surface {
            background-color: #141A26;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.45);
        }

        .theme-btn-primary {
            background-color: var(--theme-primary);
            color: #FFFFFF;
            border-radius: var(--theme-radius);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .theme-btn-primary:hover {
            opacity: 0.94;
            transform: translateY(-1px);
        }

        .theme-btn-primary:active {
            transform: translateY(0) scale(0.98);
        }
    </style>

    {{-- Universal Typography Hierarchy --}}
    @include('layouts.partials.typography')

    @stack('styles')
</head>

<body class="min-h-screen flex flex-col text-neutral-900 dark:text-neutral-100 transition-colors duration-300 antialiased"
    x-data="{
        mobileMenuOpen: false
    }">

    @include('public.storefront.components.demo_banner')
    {{-- 1. Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke umum.</span>
        </div>
    @endif

    {{-- 2. Announcement Bar --}}
    @if ($landingPage->announcement_badge)
        <div class="text-white text-xs sm:text-sm font-medium py-2 px-4 text-center tracking-wide shadow-xs flex items-center justify-center gap-2"
             style="background-color: #78350F;">
            <i data-lucide="book-open" class="w-3.5 h-3.5 shrink-0"></i>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @else
        <div class="bg-neutral-900 text-neutral-200 dark:bg-neutral-950 text-[11px] sm:text-xs font-medium py-1.5 px-4 text-center tracking-wide flex items-center justify-center gap-2 border-b border-white/10">
            <span class="inline-block w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #78350F;"></span>
            <span>Koleksi Buku Terlengkap & 100% Original • Alat Tulis Sekolah & Kantor</span>
        </div>
    @endif

    {{-- 3. Store Operating Days Status Alert --}}
    @php
        $dayMap = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        $operatingDays = (array) ($storeSetting?->operating_days ?? []);
        $currentDayOfWeek = strtolower(now()->format('l'));
        $isStoreOpenToday = empty($operatingDays) || in_array($currentDayOfWeek, $operatingDays, true);
    @endphp
    @if (!$isStoreOpenToday)
        <div class="bg-amber-500/10 border-b border-amber-500/20 py-2 px-4 text-amber-900 dark:text-amber-300 text-xs text-center flex items-center justify-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-amber-500 shrink-0"></i>
            <span>Toko Sedang Libur Hari Ini ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Pesanan yang masuk akan diproses pada hari operasional berikutnya.</span>
        </div>
    @endif

    {{-- 4. Theme Navbar --}}
    @includeFirst([
        "public.storefront.layouts.retail_bookstore.navbar",
        "public.storefront.themes.retail_bookstore.navbar",
        "public.storefront.layouts.navbar"
    ])

    {{-- 5. Main Content Area (pb-20 for Apple mobile bottom tab bar space) --}}
    <main class="flex-1 w-full pb-20 md:pb-0">
        @yield('content')
    </main>

    {{-- 6. Floating Elements & Bento Widgets --}}
    @includeFirst(["public.storefront.components.retail_bookstore.cart_floating_pill", 'public.storefront.components.cart_floating_pill'])

    @if ($hasWhatsapp ?? false)
        @includeFirst(["public.storefront.components.retail_bookstore.whatsapp_inquiry", 'public.storefront.components.whatsapp_inquiry'], [
            'hasWhatsapp' => $hasWhatsapp ?? false,
            'business' => $business,
            'landingPage' => $landingPage,
            'mode' => 'floating'
        ])
    @endif

    @includeFirst(["public.storefront.components.retail_bookstore.order_track_modal", 'public.storefront.components.order_track_modal'], ['business' => $business])

    {{-- Apple iOS Mobile Bottom Tab Bar (§PRD-07 & §web-ios-pwa-redesign) --}}
    @includeFirst(["public.storefront.components.retail_bookstore.mobile_bottom_tab_bar", 'public.storefront.components.mobile_bottom_tab_bar'], [
        'business' => $business,
        'landingPage' => $landingPage,
        'activeTheme' => $activeTheme,
        'hasWhatsapp' => $hasWhatsapp ?? false,
    ])

    {{-- 7. Theme Footer --}}
    @includeFirst([
        "public.storefront.layouts.retail_bookstore.footer",
        "public.storefront.themes.retail_bookstore.footer",
        "public.storefront.layouts.footer"
    ])

    {{-- 8. Alpine.js Global Cart Store --}}
    <script>
        document.addEventListener('alpine:init', () => {
            const STORAGE_KEY = 'cooca_cart_{{ $business->id }}';

            Alpine.store('cart', {
                items: [],

                init() {
                    const serverDbItems = @json($dbCartItems ?? null);
                    if (serverDbItems && serverDbItems.length > 0) {
                        this.items = serverDbItems;
                        this.save();
                    } else {
                        try {
                            const local = localStorage.getItem(STORAGE_KEY);
                            this.items = local ? JSON.parse(local) : [];
                        } catch (e) {
                            this.items = [];
                        }
                    }
                },

                save() {
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
                    } catch (e) {}
                },

                add(product, qty = 1, notes = '') {
                    qty = Math.max(1, parseInt(qty) || 1);
                    const existing = this.items.find(item => item.id === product.id);
                    if (existing) {
                        existing.quantity += qty;
                        if (notes) existing.notes = notes;
                    } else {
                        this.items.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price || product.selling_price || 0),
                            image_url: product.image_url || null,
                            quantity: qty,
                            notes: notes || '',
                        });
                    }
                    this.save();
                },

                remove(productId) {
                    this.items = this.items.filter(item => item.id !== productId);
                    this.save();
                },

                updateQty(productId, qty) {
                    qty = parseInt(qty) || 0;
                    if (qty <= 0) {
                        this.remove(productId);
                    } else {
                        const item = this.items.find(i => i.id === productId);
                        if (item) {
                            item.quantity = qty;
                            this.save();
                        }
                    }
                },

                clear() {
                    this.items = [];
                    this.save();
                },

                count() {
                    return this.items.reduce((acc, item) => acc + (parseInt(item.quantity) || 0), 0);
                },

                subtotal() {
                    return this.items.reduce((acc, item) => acc + ((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1)), 0);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>

</html>