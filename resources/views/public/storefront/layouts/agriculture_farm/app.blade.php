{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM MASTER LAYOUT (`layouts/agriculture_farm/app.blade.php`)--}}
{{-- Bespoke Agrarian layout shell with DM Serif Display, fresh morning canvas --}}
{{-- ========================================================================= --}}
@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
    $bundleDir = 'agriculture_farm';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0A140E' : ($activeTheme['bg_color'] ?? '#F8FAF6') }}">
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
                    themeColorMeta.setAttribute('content', isDark ? '{{ $activeTheme['dark_bg'] ?? '#0A140E' }}' : '{{ $activeTheme['bg_color'] ?? '#F8FAF6' }}');
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
        'title' => $pageTitle ?? (View::hasSection('title') ? trim((string) View::yieldContent('title')) : ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Sentra Hasil Tani & Kebun Organik'))),
        'description' => $ogDescription ?? (View::hasSection('description') ? trim((string) View::yieldContent('description')) : ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Beli hasil panen segar berkualitas, bibit tanaman unggul, dan sarana pertanian langsung dari petani binaan ' . $business->name . '.')))),
        'keywords' => View::hasSection('keywords') ? trim((string) View::yieldContent('keywords')) : ($landingPage->meta_keywords ?? null),
    ])

    @stack('seo')

    @if (!empty($defaultBrandImg))
        <link rel="apple-touch-icon" href="{{ $defaultBrandImg }}">
    @endif

    {{-- Dynamic Google Fonts: DM Serif Display & Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN & Agrarian Theme Tokens --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"DM Serif Display"', 'serif'],
                        body: ['"Plus Jakarta Sans"', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] ?? '#15803D' }}',
                            accent: '{{ $activeTheme['accent_color'] ?? '#B45309' }}',
                            bg: '{{ $activeTheme['bg_color'] ?? '#F8FAF6' }}',
                            card: '{{ $activeTheme['card_bg'] ?? '#FFFFFF' }}',
                            badgeBg: '{{ $activeTheme['badge_bg'] ?? '#15803D15' }}',
                            badgeText: '{{ $activeTheme['badge_text'] ?? '#15803D' }}',
                        }
                    },
                    borderRadius: {
                        'theme': '{{ $activeTheme['border_radius'] ?? '18px' }}',
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

    {{-- Authentic Agri Theme Dynamic CSS Tokens --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --theme-primary: {{ $activeTheme['primary_color'] ?? '#15803D' }};
            --theme-accent: {{ $activeTheme['accent_color'] ?? '#B45309' }};
            --theme-bg: {{ $activeTheme['bg_color'] ?? '#F8FAF6' }};
            --theme-card: {{ $activeTheme['card_bg'] ?? '#FFFFFF' }};
            --theme-radius: 18px;
            --font-heading: 'DM Serif Display', serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
            --theme-bg: #0A140E;
            --theme-card: #112117;
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
            box-shadow: 0 4px 20px -2px rgba(21, 128, 61, 0.04);
            border: 1px solid rgba(21, 128, 61, 0.08);
        }

        .dark .theme-surface {
            background-color: #112117;
            border-color: rgba(34, 197, 94, 0.15);
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

<body class="min-h-screen flex flex-col text-neutral-900 dark:text-emerald-50 transition-colors duration-300 antialiased selection:bg-emerald-700 selection:text-white"
    x-data="{
        mobileMenuOpen: false
    }">

    {{-- 1. Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis Kebun &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke umum.</span>
        </div>
    @endif

    {{-- 2. Agri Harvest Announcement Bar --}}
    @if ($landingPage->announcement_badge)
        <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-green-900 text-white text-xs sm:text-sm font-medium py-2 px-4 text-center tracking-wide shadow-xs flex items-center justify-center gap-2">
            <i data-lucide="sprout" class="w-3.5 h-3.5 text-amber-300 shrink-0"></i>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @else
        <div class="bg-emerald-900 text-emerald-100 text-[11px] sm:text-xs font-medium py-1.5 px-4 text-center tracking-wide flex items-center justify-center gap-2 border-b border-emerald-800/40">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
            <span>Musim Panen Segar &bull; Pengiriman langsung dari kebun binaan mitra tani Nusantara</span>
        </div>
    @endif

    {{-- 3. Farm Operating Days Status Alert --}}
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
            <span>Jadwal Petik Kebun Libur Hari Ini ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Pesanan yang masuk akan dipetik dan dikirim pada hari operasional berikutnya.</span>
        </div>
    @endif

    {{-- 4. Agriculture Theme Navbar --}}
    @include('public.storefront.themes.agriculture_farm.navbar')

    {{-- 5. Main Content Area (pb-20 for Apple mobile bottom tab bar space) --}}
    <main class="flex-1 w-full pb-20 md:pb-0">
        @yield('content')
    </main>

    {{-- 6. Floating Elements & Bento Widgets --}}
    @include('public.storefront.components.cart_floating_pill')

    @if ($hasWhatsapp)
        @include('public.storefront.components.whatsapp_inquiry', [
            'hasWhatsapp' => $hasWhatsapp,
            'business' => $business,
            'landingPage' => $landingPage,
            'mode' => 'floating'
        ])
    @endif

    @include('public.storefront.components.order_track_modal', ['business' => $business])

    {{-- Apple iOS Mobile Bottom Tab Bar (§PRD-07 & §web-ios-pwa-redesign) --}}
    @include('public.storefront.components.mobile_bottom_tab_bar', [
        'business' => $business,
        'landingPage' => $landingPage,
        'activeTheme' => $activeTheme,
        'hasWhatsapp' => $hasWhatsapp ?? false,
    ])

    {{-- 7. Agriculture Theme Footer --}}
    @include('public.storefront.themes.agriculture_farm.footer')

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