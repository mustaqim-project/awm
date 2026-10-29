@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
    $bundleDir = 'booking_appointment';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0E0B17' : ($activeTheme['bg_color'] ?? '#F5F3FF') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Persistent Dark Mode Script --}}
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
                    themeColorMeta.setAttribute('content', isDark ? '{{ $activeTheme['dark_bg'] ?? '#0E0B17' }}' : '{{ $activeTheme['bg_color'] ?? '#F5F3FF' }}');
                }
            } catch (e) {}
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        };
    </script>

    {{-- Apple HIG & Mobile Web App Meta Tags (§responsive-ui-ux & §web-ios-pwa-redesign) --}}
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
        'title' => $pageTitle ?? (View::hasSection('title') ? trim((string) View::yieldContent('title')) : ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Reservasi Layanan & Janji Temu Resmi'))),
        'description' => $ogDescription ?? (View::hasSection('description') ? trim((string) View::yieldContent('description')) : ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Reservasi jadwal kunjungan dan sesi layanan profesional di ' . $business->name . ' dengan konfirmasi instan tanpa antre.')))),
        'keywords' => View::hasSection('keywords') ? trim((string) View::yieldContent('keywords')) : ($landingPage->meta_keywords ?? null),
    ])

    @stack('seo')

    @if (!empty($defaultBrandImg))
        <link rel="apple-touch-icon" href="{{ $defaultBrandImg }}">
    @endif

    {{-- Dynamic Google Fonts Loader: Plus Jakarta Sans (Headings) & Inter (Body UI) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN & Theme Configuration --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                        body: ['"Inter"', 'sans-serif'],
                        sans: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        booking: {
                            50: '#F5F3FF',
                            100: '#EDE9FE',
                            200: '#DDD6FE',
                            300: '#C4B5FD',
                            400: '#A78BFA',
                            500: '#8B5CF6',
                            600: '#7C3AED',
                            700: '#6D28D9',
                            800: '#5B21B6',
                            900: '#4C1D95',
                            950: '#2E1065',
                        },
                        obsidian: {
                            800: '#1D172E',
                            900: '#161224',
                            950: '#0E0B17',
                        },
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] ?? '#7C3AED' }}',
                            accent: '{{ $activeTheme['accent_color'] ?? '#F43F5E' }}',
                            bg: '{{ $activeTheme['bg_color'] ?? '#F5F3FF' }}',
                            card: '{{ $activeTheme['card_bg'] ?? '#FFFFFF' }}',
                            badgeBg: '{{ $activeTheme['badge_bg'] ?? '#7C3AED15' }}',
                            badgeText: '{{ $activeTheme['badge_text'] ?? '#7C3AED' }}',
                        }
                    },
                    borderRadius: {
                        'theme': '{{ $activeTheme['border_radius'] ?? '20px' }}',
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide Icons --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Global JavaScript Bridge --}}
    <script>
        window.COOCA_I18N = @js(__('storefront.messages'));
        window.COOCA_LANG = @js($currentLocale);
        window.COOCA_CURRENCY = 'IDR';
        window.COOCA_BUSINESS_ID = @js($business->id);
    </script>

    {{-- Dynamic Booking & Appointment Design Tokens --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --theme-primary: {{ $activeTheme['primary_color'] ?? '#7C3AED' }};
            --theme-accent: {{ $activeTheme['accent_color'] ?? '#F43F5E' }};
            --theme-bg: {{ $activeTheme['bg_color'] ?? '#F5F3FF' }};
            --theme-card: {{ $activeTheme['card_bg'] ?? '#FFFFFF' }};
            --theme-radius: {{ $activeTheme['border_radius'] ?? '20px' }};
            --font-heading: 'Plus Jakarta Sans', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
            --theme-bg: #0E0B17;
            --theme-card: #161224;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--theme-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .dark body {
            background-color: #0E0B17;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: var(--font-heading);
            letter-spacing: -0.02em;
        }

        .tabular-nums, .font-mono {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        /* Subtle Slot Grid Texture */
        .bg-booking-pattern {
            background-image: radial-gradient(rgba(124, 58, 237, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .dark .bg-booking-pattern {
            background-image: radial-gradient(rgba(167, 139, 250, 0.07) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Custom Scrollbar */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- Universal Typography Hierarchy --}}
    @include('layouts.partials.typography')

    @stack('styles')
</head>

<body class="min-h-screen flex flex-col text-slate-900 dark:text-purple-100 bg-booking-pattern transition-colors duration-300 antialiased selection:bg-purple-600 selection:text-white"
    x-data="{
        mobileMenuOpen: false
    }">

    @include('public.storefront.components.demo_banner')
    {{-- 1. Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis &mdash; Halaman reservasi ini masih berstatus draft privat dan belum dipublikasikan ke umum.</span>
        </div>
    @endif

    {{-- 2. Announcement Bar --}}
    @if ($landingPage->announcement_badge)
        <div class="bg-gradient-to-r from-purple-700 via-purple-800 to-rose-600 text-white text-xs sm:text-sm font-semibold py-2 px-4 text-center tracking-wide shadow-xs flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @endif

    {{-- 3. Operating Days Alert --}}
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
        <div class="bg-purple-500/10 border-b border-purple-500/20 py-2.5 px-4 text-purple-900 dark:text-purple-200 text-xs text-center flex items-center justify-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0"></i>
            <span>{{ __('storefront.contact.closed_today') }} ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Reservasi slot online tetap dapat diajukan untuk hari kerja berikutnya.</span>
        </div>
    @endif

    {{-- 4. Theme Navbar --}}
    @if (view()->exists("public.storefront.themes.booking_appointment.navbar"))
        @include("public.storefront.themes.booking_appointment.navbar")
    @else
        @include('public.storefront.layouts.navbar')
    @endif

    {{-- 5. Main Content Area (pb-24 on mobile ensures bottom tab bar never covers content) --}}
    <main class="flex-1 w-full pb-24 md:pb-0">
        @yield('content')
    </main>

    {{-- 6. Floating Cart & Widgets --}}
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

    {{-- Apple HIG Mobile Bottom Tab Bar --}}
    @includeFirst(['public.storefront.components.' . $bundleDir . '.mobile_bottom_tab_bar', 'public.storefront.components.mobile_bottom_tab_bar'], [
        'business' => $business,
        'landingPage' => $landingPage,
        'activeTheme' => $activeTheme,
        'hasWhatsapp' => $hasWhatsapp ?? false,
    ])

    {{-- 7. Theme Footer --}}
    @if (view()->exists("public.storefront.themes.booking_appointment.footer"))
        @include("public.storefront.themes.booking_appointment.footer")
    @else
        @include('public.storefront.layouts.footer')
    @endif

    {{-- 8. Alpine.js Global Cart & Booking Store --}}
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
                            duration: product.duration || '60 Menit',
                            notes: notes || '',
                        });
                    }
                    this.save();

                    // Dispatch visual feedback event
                    window.dispatchEvent(new CustomEvent('cart-item-added', { detail: { product } }));
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

            // Global Order Tracking Store Compatibility
            Alpine.store('orderTracking', {
                isOpen: false,
                identifier: '',
                loading: false,

                open() {
                    this.isOpen = true;
                },
                close() {
                    this.isOpen = false;
                },
                submit(slug) {
                    if (!this.identifier.trim()) return;
                    this.loading = true;
                    window.location.href = '/' + slug + '/pesanan/' + encodeURIComponent(this.identifier.trim());
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