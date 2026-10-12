{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE MASTER LAYOUT (`layouts/bakery_cake/app.blade.php`) --}}
{{-- Warm Hearthstone Canvas, Cormorant Garamond & Fresh-from-the-Oven Aesthetics --}}
{{-- ========================================================================= --}}
@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
    $bundleDir = 'bakery_cake';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#18110B' : ($activeTheme['bg_color'] ?? '#FFFDF5') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Persistent Artisan Dark Mode Script --}}
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
                    themeColorMeta.setAttribute('content', isDark ? '{{ $activeTheme['dark_bg'] ?? '#18110B' }}' : '{{ $activeTheme['bg_color'] ?? '#FFFDF5' }}');
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
        'title' => $pageTitle ?? (View::hasSection('title') ? trim((string) View::yieldContent('title')) : ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Artisan Bakery & Patisserie'))),
        'description' => $ogDescription ?? (View::hasSection('description') ? trim((string) View::yieldContent('description')) : ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Nikmati kelezatan roti artisan segar dari oven, kue tart perayaan custom, dan pastry mentega murni dari ' . $business->name . '.')))),
        'keywords' => View::hasSection('keywords') ? trim((string) View::yieldContent('keywords')) : ($landingPage->meta_keywords ?? null),
    ])

    @stack('seo')

    @if (!empty($defaultBrandImg))
        <link rel="apple-touch-icon" href="{{ $defaultBrandImg }}">
    @endif

    {{-- Dynamic Google Fonts: Cormorant Garamond & Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN & Bakery Theme Tokens --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        body: ['"Plus Jakarta Sans"', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] ?? '#D97706' }}',
                            accent: '{{ $activeTheme['accent_color'] ?? '#BE185D' }}',
                            bg: '{{ $activeTheme['bg_color'] ?? '#FFFDF5' }}',
                            card: '{{ $activeTheme['card_bg'] ?? '#FFFFFF' }}',
                            badgeBg: '{{ $activeTheme['badge_bg'] ?? '#D9770615' }}',
                            badgeText: '{{ $activeTheme['badge_text'] ?? '#D97706' }}',
                        },
                        bakery: {
                            50: '#FFFDF5',
                            100: '#FEF9E7',
                            200: '#FCF0C3',
                            300: '#F9E295',
                            400: '#F5CE5D',
                            500: '#EDB732',
                            600: '#D9981E',
                            700: '#B57416',
                            800: '#925717',
                            900: '#784617',
                            950: '#45240A',
                            crust: '#92400E',
                            butter: '#F59E0B',
                            velvet: '#BE185D',
                            rose: '#E11D48',
                            hearth: '#18110B',
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

    {{-- Authentic Bakery Dynamic CSS Tokens --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --theme-primary: {{ $activeTheme['primary_color'] ?? '#D97706' }};
            --theme-accent: {{ $activeTheme['accent_color'] ?? '#BE185D' }};
            --theme-bg: {{ $activeTheme['bg_color'] ?? '#FFFDF5' }};
            --theme-card: {{ $activeTheme['card_bg'] ?? '#FFFFFF' }};
            --theme-radius: 18px;
            --font-heading: 'Cormorant Garamond', Georgia, serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
            --theme-bg: #18110B;
            --theme-card: #221810;
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
            box-shadow: 0 4px 20px -2px rgba(217, 119, 6, 0.05);
            border: 1px solid rgba(217, 119, 6, 0.12);
        }

        .dark .theme-surface {
            background-color: #221810;
            border-color: rgba(245, 158, 11, 0.18);
            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.55);
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

        /* Subtle Warm Hearth Pattern */
        .bg-bakery-pattern {
            background-image: radial-gradient(rgba(217, 119, 6, 0.06) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .dark .bg-bakery-pattern {
            background-image: radial-gradient(rgba(245, 158, 11, 0.06) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    {{-- Universal Typography Hierarchy --}}
    @include('layouts.partials.typography')

    @stack('styles')
</head>

<body class="min-h-screen flex flex-col text-neutral-900 dark:text-amber-50 transition-colors duration-300 antialiased selection:bg-amber-700 selection:text-white bg-bakery-pattern"
    x-data="{
        mobileMenuOpen: false
    }">

    {{-- 1. Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Dapur Roti &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke umum.</span>
        </div>
    @endif

    {{-- 2. Fresh Oven Batch Announcement Bar --}}
    @if ($landingPage->announcement_badge)
        <div class="bg-gradient-to-r from-amber-900 via-amber-800 to-yellow-950 text-white text-xs sm:text-sm font-medium py-2 px-4 text-center tracking-wide shadow-xs flex items-center justify-center gap-2 border-b border-amber-700/30">
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300 shrink-0"></i>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @else
        <div class="bg-gradient-to-r from-[#2A180E] via-[#3D2314] to-[#2A180E] text-amber-100 text-[11px] sm:text-xs font-medium py-1.5 px-4 text-center tracking-wide flex items-center justify-center gap-2 border-b border-amber-900/50">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
            <span>Batch Hangat Dari Oven &bull; Ragi Alami &amp; Mentega Murni &bull; Pre-Order Custom Cake &amp; Hampers</span>
        </div>
    @endif

    {{-- 3. Bakery Operating Days Alert --}}
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
            <span>Dapur Roti Libur Hari Ini ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Pesanan yang masuk akan dipanggang dan dikirim pada hari operasional berikutnya.</span>
        </div>
    @endif

    {{-- 4. Bakery Theme Navbar --}}
    @include('public.storefront.themes.bakery_cake.navbar')

    {{-- 5. Main Content Area (pb-24 for Apple mobile bottom tab bar comfort) --}}
    <main class="flex-1 w-full pb-24 md:pb-0">
        @yield('content')
    </main>

    {{-- 6. Floating Elements & Bento Widgets --}}
    @includeFirst(['public.storefront.components.' . $bundleDir . '.cart_floating_pill', 'public.storefront.components.cart_floating_pill'])

    @if ($hasWhatsapp)
        @includeFirst(['public.storefront.components.' . $bundleDir . '.whatsapp_inquiry', 'public.storefront.components.whatsapp_inquiry'], [
            'hasWhatsapp' => $hasWhatsapp,
            'business' => $business,
            'landingPage' => $landingPage,
            'mode' => 'floating'
        ])
    @endif

    @includeFirst(['public.storefront.components.' . $bundleDir . '.order_track_modal', 'public.storefront.components.order_track_modal'], ['business' => $business])

    {{-- Apple iOS Mobile Bottom Tab Bar (§PRD-07 & §web-ios-pwa-redesign) --}}
    @includeFirst(['public.storefront.components.' . $bundleDir . '.mobile_bottom_tab_bar', 'public.storefront.components.mobile_bottom_tab_bar'], [
        'business' => $business,
        'landingPage' => $landingPage,
        'activeTheme' => $activeTheme,
        'hasWhatsapp' => $hasWhatsapp ?? false,
    ])

    {{-- 7. Bakery Theme Footer --}}
    @include('public.storefront.themes.bakery_cake.footer')

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