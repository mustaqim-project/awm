@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
    $bundleDir = 'service_fitness';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0B0F19' : ($activeTheme['bg_color'] ?? '#FAF7F2') }}">
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
                    themeColorMeta.setAttribute('content', isDark ? '{{ $activeTheme['dark_bg'] ?? '#0B0F19' }}' : '{{ $activeTheme['bg_color'] ?? '#FAF7F2' }}');
                }
            } catch (e) {}
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        };
    </script>

    {{-- Favicon --}}
    @if ($business->logo_url || $landingPage->logo_url)
        <link rel="icon" href="{{ $business->logo_url ?: $landingPage->logo_url }}">
    @endif

    {{-- Canonical Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{{ $activeTheme['google_fonts'] ?? 'family=Plus+Jakarta+Sans:wght@400;500;600;700' }}&display=swap" rel="stylesheet">

    {{-- Tailwind CSS & Font Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['{{ $activeTheme['font_body'] ?? 'Inter' }}', 'sans-serif'],
                        heading: ['{{ $activeTheme['font_heading'] ?? 'Plus Jakarta Sans' }}', 'sans-serif'],
                    },
                    colors: {
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] ?? '#007AFF' }}',
                            accent: '{{ $activeTheme['accent_color'] ?? '#34C759' }}',
                            bg: '{{ $activeTheme['bg_color'] ?? '#FAF7F2' }}',
                            card: '{{ $activeTheme['card_bg'] ?? '#FFFFFF' }}',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --theme-primary: {{ $activeTheme['primary_color'] ?? '#007AFF' }};
            --theme-accent: {{ $activeTheme['accent_color'] ?? '#34C759' }};
            --theme-bg: {{ $activeTheme['bg_color'] ?? '#FAF7F2' }};
            --theme-card: {{ $activeTheme['card_bg'] ?? '#FFFFFF' }};
            --theme-radius: {{ $activeTheme['border_radius'] ?? '16px' }};
        }
        body {
            font-family: '{{ $activeTheme['font_body'] ?? 'Inter' }}', sans-serif;
            background-color: var(--theme-bg);
        }
        .font-heading {
            font-family: '{{ $activeTheme['font_heading'] ?? 'Plus Jakarta Sans' }}', sans-serif;
        }
    </style>
</head>

<body class="bg-theme-bg text-black dark:text-white transition-colors duration-200 min-h-screen flex flex-col justify-between antialiased selection:bg-theme-primary selection:text-white">
    {{-- Header / Navbar --}}
    @if (view()->exists("public.storefront.themes.service_fitness.navbar"))
        @include("public.storefront.themes.service_fitness.navbar")
    @elseif (view()->exists("public.storefront.layouts.service_fitness.navbar"))
        @include("public.storefront.layouts.service_fitness.navbar")
    @else
        @include("public.storefront.layouts.navbar")
    @endif

    {{-- Main Content --}}
    <main class="flex-1 w-full pb-20 md:pb-0">
        @yield('content')
    </main>

    
    {{-- Floating Elements & Bento Widgets --}}
    @includeFirst(["public.storefront.components.service_fitness.cart_floating_pill", 'public.storefront.components.cart_floating_pill'])

    @if ($hasWhatsapp ?? false)
        @includeFirst(["public.storefront.components.service_fitness.whatsapp_inquiry", 'public.storefront.components.whatsapp_inquiry'], [
            'hasWhatsapp' => $hasWhatsapp ?? false,
            'business' => $business,
            'landingPage' => $landingPage,
            'mode' => 'floating'
        ])
    @endif

    @includeFirst(["public.storefront.components.service_fitness.order_track_modal", 'public.storefront.components.order_track_modal'], ['business' => $business])

    {{-- Apple iOS Mobile Bottom Tab Bar (§PRD-07 & §web-ios-pwa-redesign) --}}
    @includeFirst(["public.storefront.components.service_fitness.mobile_bottom_tab_bar", 'public.storefront.components.mobile_bottom_tab_bar'], [
        'business' => $business,
        'landingPage' => $landingPage,
        'activeTheme' => $activeTheme,
        'hasWhatsapp' => $hasWhatsapp ?? false,
    ])
    {{-- Footer --}}
    @if (view()->exists("public.storefront.themes.service_fitness.footer"))
        @include("public.storefront.themes.service_fitness.footer")
    @elseif (view()->exists("public.storefront.layouts.service_fitness.footer"))
        @include("public.storefront.layouts.service_fitness.footer")
    @else
        @include("public.storefront.layouts.footer")
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>