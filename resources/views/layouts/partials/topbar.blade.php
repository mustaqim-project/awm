@php
    $activeBiz = $activeBiz ?? \App\Support\Context::business();
    $currentUser = auth()->user();

    $canAccessPos =
        \App\Support\Context::hasPermission('pos.terminal') ||
        \App\Support\Context::hasPermission('pos.orders') ||
        \App\Support\Context::hasPermission('pos.reports') ||
        \App\Support\Context::hasPermission('pos.kitchen') ||
        \App\Support\Context::hasPermission('pos.tables');

    $canAccessB2bSales =
        \App\Support\Context::hasPermission('sales.view') ||
        \App\Support\Context::hasPermission('sales.pipeline') ||
        \App\Support\Context::hasPermission('invoices.view') ||
        \App\Support\Context::hasPermission('sales.returns');

    $canAccessCrm =
        \App\Support\Context::hasPermission('customers.view') || \App\Support\Context::hasPermission('crm.view');

    $canAccessSales = $canAccessPos || $canAccessB2bSales || $canAccessCrm;

    $canAccessPurchasing =
        \App\Support\Context::hasPermission('purchasing.view') ||
        \App\Support\Context::hasPermission('purchasing.manage') ||
        \App\Support\Context::hasPermission('purchasing.bills') ||
        \App\Support\Context::hasPermission('purchase.returns') ||
        \App\Support\Context::hasPermission('receiving.manage') ||
        \App\Support\Context::hasPermission('master_data.suppliers.view');

    $canAccessInventory =
        \App\Support\Context::hasPermission('products.view') ||
        \App\Support\Context::hasPermission('materials.view') ||
        \App\Support\Context::hasPermission('inventory.view') ||
        \App\Support\Context::hasPermission('inventory.manage') ||
        \App\Support\Context::hasPermission('warehouse.view') ||
        \App\Support\Context::hasPermission('warehouse.manage') ||
        \App\Support\Context::hasPermission('pos.modifiers');

    $canAccessFinance =
        \App\Support\Context::hasPermission('accounting.view') ||
        \App\Support\Context::hasPermission('finance.cash_bank') ||
        \App\Support\Context::hasPermission('finance.receivables') ||
        \App\Support\Context::hasPermission('finance.payables') ||
        \App\Support\Context::hasPermission('expenses.view') ||
        \App\Support\Context::hasPermission('expenses.manage');

    $canAccessReports =
        \App\Support\Context::hasPermission('reports.view') || \App\Support\Context::hasPermission('pos.reports');

    $canAccessStorefront =
        \App\Support\Context::hasPermission('storefront.orders.view') ||
        \App\Support\Context::hasPermission('storefront.manage') ||
        \App\Support\Context::hasPermission('storefront.shipping.manage') ||
        \App\Support\Context::hasPermission('storefront.reservations.manage') ||
        \App\Support\Context::hasPermission('cms.manage') ||
        \App\Support\Context::isOwner();

    $canAccessChannels =
        \App\Support\Context::hasPermission('whatsapp.view') ||
        \App\Support\Context::hasPermission('whatsapp.manage') ||
        \App\Support\Context::isOwner();

    $canAccessMarketing = $canAccessCrm || $canAccessStorefront || $canAccessChannels;

    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
    $canAccessRoles = \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner();
    $canAccessBilling = \App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner();
@endphp
<style>
    .app-topbar {
        height: 3.5rem; /* 56px macOS Toolbar standard */
    }

    @media (max-width: 639px) {
        .app-topbar {
            height: 3.25rem; /* 52px iOS Navigation Bar standard */
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .app-topbar-title h1 {
            font-size: 15px !important;
        }
    }

    /* Apple HIG Focus Ring for Accessible Navigation */
    .app-topbar a:focus-visible,
    .app-topbar button:focus-visible {
        outline: 2px solid rgba(0, 122, 255, 0.65) !important;
        outline-offset: 2px !important;
        border-radius: 0.5rem !important;
    }

    .dark .app-topbar a:focus-visible,
    .dark .app-topbar button:focus-visible {
        outline-color: rgba(10, 132, 255, 0.85) !important;
    }
</style>
<!-- Topbar Header (Apple macOS / iOS Toolbar Architecture) -->
<header
    x-data="{
        theme: localStorage.getItem('cooca-theme') || 'light',
        themeDropdownOpen: false,
        langDropdownOpen: false,
        profileOpen: false,
        showLogoutConfirm: false,
        isFullscreen: false,
        setTheme(val) {
            this.theme = val;
            localStorage.setItem('cooca-theme', val);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (val === 'dark' || (val === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            document.documentElement.setAttribute('data-theme', val);
            this.themeDropdownOpen = false;
            window.dispatchEvent(new CustomEvent('cooca-theme-changed', { detail: { theme: val } }));
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
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        }
    }"
    class="app-topbar h-13 sm:h-14 glass-header sticky top-0 z-[35] flex items-center justify-between px-3 sm:px-6 lg:px-8 border-b border-black/5 dark:border-white/10 transition-all">
    <!-- Left Cluster: Mobile Trigger + Desktop Sidebar Toggle + Title -->
    <div class="app-topbar-title flex items-center gap-2 sm:gap-3 min-w-0 flex-1 mr-2 sm:mr-4">
        <!-- Mobile Drawer Toggle Button -->
        <button id="tour-mobile-menu-btn"
            @click="sidebarOpen = true; window.dispatchEvent(new CustomEvent('sidebar-opened'))"
            type="button"
            class="lg:hidden w-9 h-9 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 flex items-center justify-center shrink-0 transition-all cursor-pointer"
            title="{{ __('navigation.toggle_navigation') }}" aria-label="{{ __('navigation.toggle_navigation') }}">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <!-- Desktop Sidebar Collapse Toggle Button (macOS Window Control Style) -->
        <button @click="toggleSidebarCollapse()" type="button"
            class="hidden lg:flex items-center justify-center w-8.5 h-8.5 text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white rounded-[8px] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 shrink-0 transition-all cursor-pointer"
            :title="sidebarCollapsed ? '{{ __('navigation.expand_sidebar') }}' : '{{ __('navigation.collapse_sidebar') }}'" aria-label="Toggle Sidebar">
            <i :data-lucide="sidebarCollapsed ? 'panel-left-open' : 'panel-left-close'" class="w-4 h-4"></i>
        </button>

        <!-- Header Title & Subtitle Hierarchy -->
        <div class="min-w-0 flex-1">
            <h1 title="{{ $headerTitle ?? $title ?? 'Cooca' }}"
                class="text-[15px] sm:text-[16px] font-semibold text-black dark:text-white truncate leading-tight tracking-tight">
                {{ $headerTitle ?? $title ?? 'Cooca' }}
            </h1>
            <p title="{{ $headerSubtitle ?? __('navigation.default_subtitle') }}"
                class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block max-w-md truncate leading-tight mt-0.5">
                {{ \Illuminate\Support\Str::words($headerSubtitle ?? __('navigation.default_subtitle'), 15, '...') }}
            </p>
        </div>
    </div>

    <!-- Right Cluster: Actions & Control Tools (Clean Apple HIG Layout) -->
    <div class="app-topbar-actions flex items-center gap-1.5 sm:gap-2.5 shrink-0">
        <!-- Global Spotlight Search Trigger Button (Desktop md+) -->
        <button type="button" @click="$dispatch('open-spotlight')"
            class="hidden md:flex items-center gap-2 px-3 h-8.5 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-all cursor-pointer text-[12px] group"
            title="{{ __('navigation.search_placeholder') }}">
            <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors"></i>
            <span class="text-[12px] font-normal text-black/50 dark:text-white/50">{{ __('navigation.search_placeholder') }}</span>
            <kbd class="ml-1.5 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shadow-2xs">
                <span>Ctrl K</span>
            </kbd>
        </button>

        <!-- Mobile Search Trigger Button (< md) -->
        <button type="button" @click="$dispatch('open-spotlight')"
            class="md:hidden w-9 h-9 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 flex items-center justify-center transition-all cursor-pointer shrink-0"
            title="{{ __('navigation.search_placeholder') }}" aria-label="{{ __('common.search') }}">
            <i data-lucide="search" class="w-4 h-4"></i>
        </button>


        <!-- Unified System Controls Capsule (Desktop / Tablet sm+) -->
        <div class="hidden sm:flex items-center p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 gap-0.5">
            <!-- Fullscreen Toggle Button (Desktop md+) -->
            <button type="button" @click="toggleFullscreen()"
                :title="isFullscreen ? '{{ __('navigation.fullscreen_exit') }}' : '{{ __('navigation.fullscreen_enter') }}'"
                aria-label="Toggle Fullscreen"
                class="hidden md:flex h-7 w-7 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/70 dark:hover:bg-white/10 transition-all items-center justify-center shrink-0 active:scale-95 cursor-pointer">
                <i x-show="!isFullscreen" data-lucide="maximize" class="w-3.5 h-3.5"></i>
                <i x-show="isFullscreen" data-lucide="minimize" class="w-3.5 h-3.5 text-[#007AFF]"
                    style="display: none;"></i>
            </button>

            <!-- Bento Apple HIG Language Switcher (ID / EN) -->
            <div class="relative" @click.outside="langDropdownOpen = false"
                @keydown.esc.window="langDropdownOpen = false">
                <button type="button" @click="langDropdownOpen = !langDropdownOpen"
                    :aria-expanded="langDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                    aria-controls="language-menu"
                    title="{{ __('common.switch_language') }}"
                    aria-label="{{ __('common.switch_language') }}"
                    class="h-7 px-2 rounded-[6px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-white/70 dark:hover:bg-white/10 transition-all flex items-center justify-center gap-1 shrink-0 active:scale-95 cursor-pointer text-[11px] font-bold tracking-wider">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-black/50 dark:text-white/50"></i>
                    <span class="uppercase">{{ app()->getLocale() }}</span>
                </button>

                <!-- Language Dropdown Menu (Apple Glass Squircle) -->
                <div id="language-menu" x-show="langDropdownOpen" x-transition role="menu" aria-label="{{ __('common.switch_language') }}"
                    class="absolute right-0 mt-2 w-44 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-[13px]"
                    style="display: none;">
                    <a href="{{ route('locale.switch', ['locale' => 'id']) }}" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer {{ app()->getLocale() === 'id' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">ID</span>
                            <span>{{ __('navigation.lang_id') }}</span>
                        </div>
                        @if(app()->getLocale() === 'id')
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        @endif
                    </a>
                    <a href="{{ route('locale.switch', ['locale' => 'en']) }}" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">EN</span>
                            <span>{{ __('navigation.lang_en') }}</span>
                        </div>
                        @if(app()->getLocale() === 'en')
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Theme Switcher (Light / Dark / System) -->
            <div class="relative" @click.outside="themeDropdownOpen = false"
                @keydown.esc.window="themeDropdownOpen = false">
                <button type="button" @click="themeDropdownOpen = !themeDropdownOpen"
                    :aria-expanded="themeDropdownOpen ? 'true' : 'false'" aria-haspopup="menu"
                    aria-controls="theme-menu"
                    :title="'{{ __('navigation.theme_toggle') }}: ' + (theme === 'dark' ? '{{ __('navigation.theme_dark') }}' : (theme === 'system' ? '{{ __('navigation.theme_system') }}' : '{{ __('navigation.theme_light') }}'))"
                    aria-label="{{ __('navigation.theme_toggle') }}"
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
                <div id="theme-menu" x-show="themeDropdownOpen" x-transition role="menu" aria-label="{{ __('navigation.theme_toggle') }}"
                    class="absolute right-0 mt-2 w-40 rounded-[12px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-1 z-50 space-y-0.5 text-[13px]"
                    style="display: none;">
                    <button type="button" @click="setTheme('light')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                        :class="theme === 'light' ? 'bg-[#FF9500]/10 text-[#FF9500] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sun" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                            <span>{{ __('navigation.theme_light') }}</span>
                        </div>
                        <span x-show="theme === 'light'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('dark')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                        :class="theme === 'dark' ? 'bg-[#5856D6]/10 text-[#5856D6] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="moon" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                            <span>{{ __('navigation.theme_dark') }}</span>
                        </div>
                        <span x-show="theme === 'dark'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                    <button type="button" @click="setTheme('system')" role="menuitem"
                        class="w-full px-2.5 py-1.5 rounded-[7px] flex items-center justify-between gap-2 text-left font-medium transition active:scale-98 cursor-pointer"
                        :class="theme === 'system' ? 'bg-[#007AFF]/10 text-[#007AFF] font-semibold' :
                            'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'">
                        <div class="flex items-center gap-2">
                            <i data-lucide="monitor" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.theme_system') }}</span>
                        </div>
                        <span x-show="theme === 'system'"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vertical Separator (Desktop / Tablet sm+) -->
        <div class="h-4 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>

        <!-- User Profile Dropdown (Apple macOS / iOS Account Menu) -->
        <div class="relative" @click.outside="profileOpen = false"
            @keydown.esc.window="profileOpen = false">
            <button type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen ? 'true' : 'false'"
                aria-haspopup="menu" aria-controls="profile-menu"
                class="flex items-center gap-2 p-0.5 sm:p-1 sm:pl-1 sm:pr-2.5 rounded-full hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition-all active:scale-95 cursor-pointer"
                title="{{ __('navigation.user_profile') }}: {{ $currentUser->name ?? 'User' }}">
                <!-- Squircle Avatar -->
                <div
                    class="w-8 h-8 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-xs text-[#007AFF] shrink-0 shadow-2xs">
                    {{ substr($currentUser->name ?? 'U', 0, 2) }}
                </div>
                <div class="text-left hidden md:block max-w-[120px]">
                    <div class="text-[13px] font-semibold text-black dark:text-white truncate leading-tight">
                        {{ $currentUser->name ?? 'User' }}
                    </div>
                    <div class="text-[11px] text-black/55 dark:text-white/55 truncate leading-tight">
                        {{ $activeBiz->name ?? 'Owner' }}
                    </div>
                </div>
                <i data-lucide="chevron-down"
                    class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0 hidden sm:block"
                    :class="profileOpen ? 'rotate-180' : ''"></i>
            </button>

            <!-- Profile Popover Sheet (macOS Sonoma / iOS Style) -->
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1" id="profile-menu" role="menu"
                aria-label="{{ __('navigation.user_profile') }}"
                class="absolute right-0 mt-2 w-68 rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-2 z-50 space-y-1 text-[13px]"
                style="display: none;">

                <!-- User Details Header -->
                <div
                    class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-full bg-[#007AFF]/12 border border-[#007AFF]/25 flex items-center justify-center font-bold text-sm text-[#007AFF] shrink-0">
                        {{ substr($currentUser->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-black dark:text-white truncate text-[13px]">
                            {{ $currentUser->name ?? 'User' }}
                        </p>
                        <p class="text-[11px] text-black/50 dark:text-white/50 truncate">
                            {{ $currentUser->email ?? '' }}
                        </p>
                    </div>
                </div>

                <!-- Mobile Quick Controls (Theme & Language Switcher inside Popover for Phone Users) -->
                <div class="sm:hidden space-y-1.5 py-1.5 px-2 bg-black/[0.02] dark:bg-white/[0.02] rounded-[10px] border border-black/5 dark:border-white/5">
                    <!-- Theme Switcher Segmented Control -->
                    <div class="flex items-center justify-between text-[11px] text-black/75 dark:text-white/75">
                        <span class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="palette" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('navigation.theme_toggle') ?? 'Tema' }}</span>
                        </span>
                        <div class="flex items-center gap-0.5 bg-black/5 dark:bg-white/10 p-0.5 rounded-[7px] text-[10px] font-semibold">
                            <button type="button" @click="setTheme('light')"
                                :class="theme === 'light' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF9500] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'"
                                class="px-1.5 py-0.5 rounded-[5px] transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="sun" class="w-3 h-3"></i>
                                <span>{{ __('navigation.theme_light') ?? 'Terang' }}</span>
                            </button>
                            <button type="button" @click="setTheme('dark')"
                                :class="theme === 'dark' ? 'bg-white dark:bg-[#1C1C1E] text-[#5856D6] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'"
                                class="px-1.5 py-0.5 rounded-[5px] transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="moon" class="w-3 h-3"></i>
                                <span>{{ __('navigation.theme_dark') ?? 'Gelap' }}</span>
                            </button>
                            <button type="button" @click="setTheme('system')"
                                :class="theme === 'system' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white'"
                                class="px-1.5 py-0.5 rounded-[5px] transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="monitor" class="w-3 h-3"></i>
                                <span>Auto</span>
                            </button>
                        </div>
                    </div>

                    <!-- Language Switcher Segmented Control -->
                    <div class="flex items-center justify-between text-[11px] text-black/75 dark:text-white/75">
                        <span class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('common.switch_language') ?? 'Bahasa' }}</span>
                        </span>
                        <div class="flex items-center gap-1 bg-black/5 dark:bg-white/10 p-0.5 rounded-[7px] text-[10px] font-semibold">
                            <a href="{{ route('locale.switch', ['locale' => 'id']) }}"
                                class="px-2 py-0.5 rounded-[5px] transition-all {{ app()->getLocale() === 'id' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">ID</a>
                            <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
                                class="px-2 py-0.5 rounded-[5px] transition-all {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">EN</a>
                        </div>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="space-y-0.5 pt-0.5">
                    <a href="{{ route('profile.edit') }}" @click="profileOpen = false"
                        class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-98">
                        <i data-lucide="key" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                        <span>{{ __('navigation.user_profile') }}</span>
                    </a>

                    @if (\App\Support\Context::isOwner())
                        <a href="{{ route('feedback.bugs.index') }}" @click="profileOpen = false"
                            class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-98">
                            <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('navigation.support_help') }}</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('onboarding.restart') }}">
                        @csrf
                        <button type="submit" @click="profileOpen = false"
                            class="w-full px-2.5 py-1.5 rounded-[7px] text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-98 text-left cursor-pointer">
                            <i data-lucide="help-circle"
                                class="w-3.5 h-3.5 text-black/45 dark:text-white/45 shrink-0"></i>
                            <span>{{ __('navigation.restart_tour') }}</span>
                        </button>
                    </form>
                </div>

                <!-- Desktop Language Switcher (Inside Profile as Well) -->
                <div class="hidden sm:block border-t border-black/5 dark:border-white/10 my-1"></div>
                <div class="hidden sm:flex px-2.5 py-1.5 items-center justify-between text-[12px] text-black/75 dark:text-white/75">
                    <span class="flex items-center gap-2">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                        <span>{{ __('common.switch_language') ?? 'Bahasa' }}</span>
                    </span>
                    <div class="flex items-center gap-1 bg-black/5 dark:bg-white/10 p-0.5 rounded-[8px] text-[11px] font-semibold">
                        <a href="{{ route('locale.switch', ['locale' => 'id']) }}"
                            class="px-2 py-0.5 rounded-[6px] transition-all {{ app()->getLocale() === 'id' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">ID</a>
                        <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
                            class="px-2 py-0.5 rounded-[6px] transition-all {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] shadow-2xs font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">EN</a>
                    </div>
                </div>

                <div class="border-t border-black/5 dark:border-white/10 my-1"></div>

                @if (session()->has('admin_impersonator_id'))
                    <div class="p-1 mb-1 bg-[#FF9500]/10 dark:bg-[#FF9F0A]/15 rounded-[10px] border border-[#FF9500]/25">
                        <form method="POST" action="{{ route('admin.businesses.leave-impersonation') }}">
                            @csrf
                            <button type="submit"
                                class="w-full px-2.5 py-1.5 rounded-[8px] text-[#FF9500] dark:text-[#FF9F0A] hover:bg-[#FF9500]/15 flex items-center gap-2 transition active:scale-98 text-left font-bold text-[12px] cursor-pointer">
                                <i data-lucide="arrow-left-to-line" class="w-3.5 h-3.5 shrink-0" stroke-width="2.5"></i>
                                <span>Kembali ke Admin Console</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Logout Option with Apple Alert Modal Trigger -->
                <div>
                    <button type="button" @click="profileOpen = false; showLogoutConfirm = true"
                        class="w-full px-2.5 py-1.5 rounded-[7px] text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 flex items-center gap-2 transition active:scale-98 text-left font-medium cursor-pointer">
                        <i data-lucide="log-out" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>{{ __('common.logout') }}</span>
                    </button>
                </div>
            </div>

            <!-- Apple Alert Confirmation Sheet / Modal (No-Panic Microcopy) -->
            <template x-teleport="body">
                <div x-show="showLogoutConfirm"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4"
                    style="display: none;"
                    @click.self="showLogoutConfirm = false"
                    @keydown.esc.window="showLogoutConfirm = false">
                    <div @click.stop
                        x-show="showLogoutConfirm"
                        x-transition:enter="transition ease-out duration-200 transform"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150 transform"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[20px] shadow-[0_24px_64px_rgba(0,0,0,0.3)] border border-black/10 dark:border-white/15 p-5 text-center transition-all">
                        
                        <div class="w-12 h-12 mx-auto rounded-full bg-[#FF3B30]/10 dark:bg-[#FF453A]/15 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center mb-3.5">
                            <i data-lucide="log-out" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white leading-snug">
                            {{ __('common.logout_confirm_title') }}
                        </h3>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-relaxed">
                            {{ __('common.logout_confirm_msg') }}
                        </p>

                        <div class="flex items-center gap-2.5 mt-5">
                            <button type="button" @click="showLogoutConfirm = false"
                                class="flex-1 py-2.5 px-3 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[13px] font-semibold text-black/70 dark:text-white/70 transition active:scale-98 cursor-pointer">
                                {{ __('common.cancel') }}
                            </button>
                            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 px-3 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-[13px] font-bold text-white shadow-[0_2px_8px_rgba(255,59,48,0.3)] transition active:scale-98 cursor-pointer">
                                    {{ __('common.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</header>

{{-- ========================================================================= --}}
{{-- APPLE HIG SPOTLIGHT COMMAND PALETTE MODAL (CTRL+K / CMD+K)                --}}
{{-- ========================================================================= --}}
<div x-data="{
    isOpen: false,
    spotlightOpen: false,
    query: '',
    category: 'all',
    selectedIndex: 0,
    categories: [
        { id: 'all', label: '{{ __('common.all') }}' },
        { id: 'Dashboard', label: '{{ __('navigation.clusters.daily_ops') }}' },
        @if ($canAccessSales)
            { id: 'Kasir & Penjualan', label: '{{ ($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_terminal') }}' },
        @endif
        @if ($canAccessInventory)
            { id: 'Produk & Stok', label: '{{ __('navigation.clusters.master_data') }}' },
        @endif
        @if ($canAccessPurchasing)
            { id: 'Pembelian & Supplier', label: '{{ __('navigation.suppliers') }}' },
        @endif
        @if ($canAccessMarketing)
            { id: 'Pelanggan & Pemasaran', label: '{{ __('navigation.crm_customers') }}' },
        @endif
        @if ($canAccessFinance)
            { id: 'Keuangan & Biaya', label: '{{ __('navigation.clusters.reports_finance') }}' },
        @endif
        @if ($canAccessReports)
            { id: 'Laporan & Analitik', label: '{{ __('navigation.sales_report') }}' },
        @endif
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            { id: 'Pengaturan Usaha', label: '{{ __('navigation.clusters.settings_hub') }}' },
        @endif
    ],
    items: [
        // 1. Dashboards (Overview)
        @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view'))
        { title: 'Beranda Dashboard', desc: 'Ringkasan performa penjualan, omset, dan laba kotor bisnis', category: 'Dashboard', route: '{{ route('dashboard') }}', icon: 'layout-dashboard', keywords: 'beranda home executive overview penjualan sales omset revenue laba profit summary' },
        @endif
        { title: 'Portal Karyawan & Presensi', desc: 'Jam kerja real-time, cuaca, dan absensi masuk/pulang', category: 'Dashboard', route: '{{ route('portal') }}', icon: 'clock', keywords: 'portal presensi absen attendance jam kerja clock in out kehadiran cuaca staff' },
        @if ($canAccessFinance)
            { title: 'Ringkasan Finansial & Kas', desc: 'Saldo kas, mutasi rekening bank, dan ringkasan arus kas', category: 'Dashboard', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'keuangan kas bank cash bank accounting overview saldo balance flow' },
        @endif
        @if ($canAccessPos)
            { title: 'Ringkasan Kasir POS', desc: 'Aktivitas transaksi kasir toko, riwayat shift, dan pesanan kasir', category: 'Dashboard', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'kasir pos cashier transaksi shift kas orders meja receipt summary' },
        @endif
        @if ($canAccessB2bSales && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES) ?? true))
            { title: 'Ringkasan Penjualan B2B', desc: 'Pesanan penjualan B2B, penawaran harga, dan faktur piutang', category: 'Dashboard', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'b2b penjualan sales orders so faktur invoice pipeline' },
        @endif
        @if ($canAccessStorefront && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT) ?? true))
            { title: 'Ringkasan Pesanan Toko Online', desc: 'Aktivitas pesanan checkout etalase toko online', category: 'Dashboard', route: '{{ route('storefront.orders.index') }}', icon: 'store', keywords: 'toko online etalase pesanan order web storefront checkout' },
        @endif
        @if ($canAccessFinance || \App\Support\Context::isOwner())
            { title: 'Ringkasan Karyawan & Payroll', desc: 'Daftar pegawai, kasbon, dan ringkasan gaji bulanan', category: 'Dashboard', route: '{{ route('hrm.index') }}', icon: 'users', keywords: 'hrm karyawan pegawai staf staff payroll gaji salary upah wages' },
        @endif
        @if (($canAccessCrm || $canAccessSales) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY) ?? true))
            { title: 'Ringkasan Pelanggan & Member', desc: 'Statistik basis pelanggan setia, poin loyalitas, dan piutang', category: 'Dashboard', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'crm pelanggan member poin loyalty customer diskon discount reward' },
        @endif
        @if ($canAccessInventory && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true))
            { title: 'Ringkasan Gudang & Persediaan', desc: 'Monitoring level stok gudang, lokasi penyimpanan, dan stok kritis', category: 'Dashboard', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'gudang stok inventory warehouse persediaan stock alert habis' },
        @endif
        @if (\App\Support\Context::isOwner() && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true))
            { title: 'Ringkasan Pemasaran Digital', desc: 'Jadwal posting media sosial, kalender konten, dan inbox', category: 'Dashboard', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'marketing sosmed instagram tiktok facebook wa broadcast social media' },
        @endif
        { title: 'Asisten Cerdas AI Cooca', desc: 'Konsultasi bisnis, analisis penjualan, dan rekomendasi otomatis', category: 'Dashboard', route: '{{ route('pos.ai.index') }}', icon: 'bot', keywords: 'ai asisten bot analisa cerdas konsultasi pintar assistant intelligence' },

        @if ($canAccessSales)
            // 2. Kasir & Penjualan
            @if ($canAccessPos)
                { title: '{{ ($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_open') }}', desc: 'Terminal kasir cepat untuk melayani transaksi kasir harian', category: 'Kasir & Penjualan', route: '{{ route('pos.terminal') }}', icon: 'calculator', keywords: 'kasir pos cashier terminal jualan sales bayar pay struk receipt checkout register resto resto_pos' },
                { title: 'Riwayat Transaksi Kasir', desc: 'Daftar struk penjualan kasir POS dan rekap shift kasir', category: 'Kasir & Penjualan', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'transaksi transaction kasir cashier struk shift nota rekap history orders' },
                @if (($activeBiz && $activeBiz->isFoodIndustry()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) ?? true))
                    { title: 'Layar Dapur (KDS)', desc: 'Tampilan pesanan makanan & minuman langsung untuk staf dapur', category: 'Kasir & Penjualan', route: '{{ route('pos.kitchen.index') }}', icon: 'chef-hat', keywords: 'kitchen dapur kds order masak bar makanan resto food display' },
                    { title: 'Meja & QR Resto', desc: 'Tata kelola denah meja, nomor meja, dan cetak QR ordering', category: 'Kasir & Penjualan', route: '{{ route('pos.tables.index') }}', icon: 'layout-grid', keywords: 'meja table qr resto cafe dine in pesan self order' },
                @endif
            @endif
            @if ($canAccessB2bSales && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES) ?? true))
                { title: 'Pesanan Penjualan (Sales Orders)', desc: 'Daftar pesanan penjualan produk ke pelanggan atau klien', category: 'Kasir & Penjualan', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'pesanan penjualan so sales order b2b klien customer order' },
                { title: 'Surat Penawaran (Quotations)', desc: 'Buat dan kelola surat penawaran harga resmi untuk pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.quotations.index') }}', icon: 'file-text', keywords: 'penawaran quotation harga proposal quote pricing' },
                { title: 'Faktur Penjualan (Invoices)', desc: 'Tagihan faktur resmi, status tempo, dan penerimaan pelunasan piutang', category: 'Kasir & Penjualan', route: '{{ route('invoices.index') }}', icon: 'file-check', keywords: 'faktur invoice piutang tagihan bayar cicil tempo due date' },
                { title: 'Retur Penjualan', desc: 'Pencatatan pengembalian barang atau komplain retur pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur penjualan sales return refund pengembalian komplain ganti' },
            @endif
        @endif

        @if ($canAccessInventory)
            // 3. Produk & Persediaan
            { title: 'Katalog Produk & Menu', desc: 'Daftar barang dagangan, harga jual, dan resep produk', category: 'Produk & Stok', route: '{{ route('products.index') }}', icon: 'package', keywords: 'produk product barang menu katalog catalog makanan minuman sku items' },
            { title: 'Integrasi Marketplace', desc: 'Hub multi-channel Shopee, TikTok Shop, dan Tokopedia dengan sinkronisasi harga & stok', category: 'Produk & Stok', route: '{{ route('marketplace-hub.index') }}', icon: 'shopping-bag', keywords: 'marketplace shopee tiktok shop tokopedia integrasi channel online pesanan sync' },
            { title: '{{ ($activeBiz && ($activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) || $activeBiz->isServiceSector() || $activeBiz->isWorkshop())) ? __('navigation.services_workshop') : __('navigation.services') }}', desc: 'Katalog jasa pengerjaan, servis teknis, dan tarif per jam', category: 'Produk & Stok', route: '{{ route('services.index') }}', icon: 'wrench', keywords: 'jasa service layanan servis technical tarif ongkos kerja hourly spk bengkel workshop pkb' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true)
                { title: 'Bahan Baku & Resep (BOM)', desc: 'Master bahan mentah, komponen produksi, dan kartu resep', category: 'Produk & Stok', route: '{{ route('materials.index') }}', icon: 'boxes', keywords: 'bahan baku raw materials resep bom formula bumbu recipe ingredients' },
            @endif
            { title: 'Varian & Opsi Tambahan', desc: 'Topping, level pedas, ukuran cup, dan modifikasi pesanan', category: 'Produk & Stok', route: '{{ route('pos.modifiers.index') }}', icon: 'layers', keywords: 'varian modifier topping opsi pilihan ekstra add on variants' },
            { title: 'Stok Gudang & Saldo', desc: 'Informasi sisa fisik stok produk dan bahan baku di semua gudang', category: 'Produk & Stok', route: '{{ route('inventory.stocks') }}', icon: 'archive', keywords: 'stok gudang stock balance saldo inventory fisik sisa kuantitas' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true)
            { title: 'Lokasi Gudang', desc: 'Pengaturan multi-lokasi gudang, toko, outlet, dan cabang', category: 'Produk & Stok', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'lokasi gudang warehouse storage cabang outlet toko simpan location' },
            @endif
            { title: 'Opname Stok Fisik', desc: 'Penyesuaian stok berkala dan rekonsiliasi selisih fisik gudang', category: 'Produk & Stok', route: '{{ route('inventory.opnames.index') }}', icon: 'clipboard-check', keywords: 'opname stock opname fisik cek selisih audit gudang cocok count' },
            { title: 'Transfer Stok Gudang', desc: 'Surat jalan perpindahan barang antar cabang atau lokasi gudang', category: 'Produk & Stok', route: '{{ route('inventory.transfers.index') }}', icon: 'arrow-left-right', keywords: 'transfer mutasi stock transfer antar gudang cabang kirim jalan' },
            { title: 'Kategori Produk', desc: 'Pengelompokan jenis dan kategori barang jualan', category: 'Produk & Stok', route: '{{ route('product-categories.index') }}', icon: 'folder-tree', keywords: 'kategori produk product category kelompok barang jenis' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true)
            { title: 'Kategori Bahan Baku', desc: 'Pengelompokan jenis bahan mentah dan material produksi', category: 'Produk & Stok', route: '{{ route('material-categories.index') }}', icon: 'folder', keywords: 'kategori bahan material categories mentah produksi' },
            @endif
            { title: 'Satuan Ukur & Konversi', desc: 'Master unit satuan (kg, gram, pcs, liter) dan konversi rasio', category: 'Produk & Stok', route: '{{ route('units.index') }}', icon: 'scale', keywords: 'satuan ukur unit conversion kg gram liter pcs dus uom' },
            { title: 'Impor & Ekspor Excel', desc: 'Impor massal produk, bahan baku, resep, dan stok via Excel', category: 'Produk & Stok', route: '{{ route('import.index') }}', icon: 'file-spreadsheet', keywords: 'import ekspor excel csv download template massal bulk' },
        @endif

        @if ($canAccessPurchasing && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_PROCUREMENT) ?? true))
            // 4. Pembelian & Supplier
            { title: 'Pesanan Pembelian (PO)', desc: 'Surat pesanan pembelian pengadaan barang ke supplier pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchase-orders.index') }}', icon: 'truck', keywords: 'po purchase order beli purchasing supplier pemasok pengadaan procurement' },
            { title: 'Tagihan Supplier (Bills)', desc: 'Daftar kewajiban utang dagang pembelian dari pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchasing.bills.index') }}', icon: 'receipt', keywords: 'tagihan bill supplier bill utang supplier invoice pembelian tempo ap' },
            { title: 'Supplier & Pemasok', desc: 'Direktori kontak supplier, alamat, dan syarat pembayaran', category: 'Pembelian & Supplier', route: '{{ route('suppliers.index') }}', icon: 'building-2', keywords: 'supplier vendor pemasok distributor rekanan kulakan contacts' },
            { title: 'Retur Pembelian', desc: 'Pengembalian barang rusak atau cacat ke pihak supplier', category: 'Pembelian & Supplier', route: '{{ route('purchase.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur beli purchase return supplier rusak kembalikan dana potong utang' },
        @endif

        @if ($canAccessMarketing)
            // 5. Pelanggan & Pemasaran
            @if ($canAccessCrm && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY) ?? true))
                { title: 'Data Pelanggan (CRM)', desc: 'Buku kontak pelanggan, nomor WhatsApp, riwayat transaksi', category: 'Pelanggan & Pemasaran', route: '{{ route('customers.index') }}', icon: 'users', keywords: 'pelanggan customer contacts kontak wa crm pembeli langganan' },
                { title: 'Member & Loyalitas Poin', desc: 'Program keanggotaan member, perolehan poin, dan reward loyalitas', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'member loyalitas poin loyalty reward kupon program tier' },
                { title: 'Voucher Diskon Promosi', desc: 'Kode kupon promosi, diskon persentase, dan voucher potongan harga', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.vouchers.index') }}', icon: 'ticket', keywords: 'voucher diskon promo discount kupon coupon potongan harga promo' },
            @endif
            @if ($canAccessStorefront && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT) ?? true))
                { title: 'Pesanan Toko Online', desc: 'Daftar pesanan masuk dari toko online dan status pembayaran', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.orders.index') }}', icon: 'shopping-bag', keywords: 'toko online etalase katalog web storefront checkout online orders' },
                { title: 'Desain Halaman Toko (Mini-Site)', desc: 'Kustomisasi tema, banner, warna, dan tampilan web toko online', category: 'Pelanggan & Pemasaran', route: '{{ route('landing-page.edit') }}', icon: 'globe', keywords: 'landing page website desain tema etalase toko online mini site web builder' },
                @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION) ?? true)
                    { title: 'Reservasi & Booking Online', desc: 'Kelola pesanan reservasi meja atau booking layanan pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.reservations.index') }}', icon: 'calendar-check', keywords: 'reservasi booking meja reservation jadwal reservasi janji temu' },
                @endif
                @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING) ?? true)
                    { title: 'Pengaturan Ongkos Kirim', desc: 'Aturan ongkir kurir toko dan integrasi ekspedisi logistik pengiriman', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.shipping.index') }}', icon: 'truck', keywords: 'ongkir shipping rate pengiriman tarif kurir logistik biteship' },
                @endif
                { title: 'Pengaturan Toko Online & Pembayaran', desc: 'Aturan checkout, metode pembayaran transfer/QRIS, dan jam buka etalase', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.settings.index') }}', icon: 'store', keywords: 'toko online etalase qris transfer manual rekening checkout pengaturan jam buka' },
            @endif
            @if ($canAccessChannels && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true))
                { title: 'WhatsApp Bisnis Toko', desc: 'Koneksi perangkat WhatsApp dan pengiriman struk belanja otomatis', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.index') }}', icon: 'message-square', keywords: 'whatsapp wa koneksi qr barcode struk auto sender messaging' },
                { title: 'Pesan Siaran WhatsApp (Broadcast)', desc: 'Kirim pesan siaran promo massal langsung ke kontak pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.broadcast.index') }}', icon: 'radio', keywords: 'broadcast siaran wa blast massal pesan promosi broadcast' },
                { title: 'Media Sosial Omnichannel', desc: 'Integrasi dan jadwal posting ke Instagram, Facebook, dan TikTok', category: 'Pelanggan & Pemasaran', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'sosmed media sosial instagram tiktok facebook threads posting schedule' },
            @endif
        @endif

        @if ($canAccessFinance)
            // 6. Keuangan & Biaya
            { title: 'Kas & Rekening Bank', desc: 'Pencatatan mutasi kas masuk, kas keluar, dan saldo bank', category: 'Keuangan & Biaya', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'kas bank mutasi uang cash flow rekening tunai masuk keluar balance' },
            { title: 'Pengeluaran Operasional', desc: 'Beban biaya operasional toko, listrik, air, sewa, dan konsumsi', category: 'Keuangan & Biaya', route: '{{ route('finance.expenses.index') }}', icon: 'banknote', keywords: 'pengeluaran expense operational biaya listrik bon sewa gaji opex' },
            { title: 'Daftar Piutang Usaha', desc: 'Daftar tagihan yang belum dibayar oleh pelanggan atau rekanan', category: 'Keuangan & Biaya', route: '{{ route('finance.receivables') }}', icon: 'trending-up', keywords: 'piutang ar accounts receivable customer belum bayar tempo tagihan invoice' },
            { title: 'Daftar Utang Usaha', desc: 'Daftar kewajiban utang jatuh tempo kepada pihak pemasok', category: 'Keuangan & Biaya', route: '{{ route('finance.payables') }}', icon: 'trending-down', keywords: 'utang ap accounts payable supplier jatuh tempo bayar kewajiban bills' },
            { title: 'Pencairan Dana Penjualan (Settlement)', desc: 'Rekonsiliasi pencairan saldo gateway pembayaran ke rekening bank', category: 'Keuangan & Biaya', route: '{{ route('finance.settlements.index') }}', icon: 'landmark', keywords: 'settlement pencairan dana qris gateway tripay tarik saldo payout' },

            @php
                $topbarShowCorporateAccounting = $activeBiz ? $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_ACCOUNTING_CORPORATE) : true;
            @endphp
            @if ($topbarShowCorporateAccounting)
                { title: 'Bagan Akun (Chart of Accounts)', desc: 'Daftar struktur akun buku besar hierarki multi-tier standar akuntansi', category: 'Keuangan & Biaya', route: '{{ route('finance.coa.index') }}', icon: 'list-tree', keywords: 'coa bagan akun chart of accounts perkiraan kode akun hierarki aktiva kewajiban ekuitas' },
                { title: 'Buku Jurnal Keuangan', desc: 'Jurnal umum debit-kredit otomatis dari seluruh transaksi usaha', category: 'Keuangan & Biaya', route: '{{ route('finance.journals.index') }}', icon: 'file-text', keywords: 'jurnal journal entry akuntansi debit kredit transaksi pembukuan balance' },
                { title: 'Buku Besar Umum (General Ledger)', desc: 'Rincian mutasi kronologis debit-kredit akun dengan audit trail lengkap', category: 'Keuangan & Biaya', route: '{{ route('finance.general-ledger') }}', icon: 'book-open', keywords: 'buku besar general ledger gl akun mutasi rincian saldo running' },
                { title: 'Rekonsiliasi Bank', desc: 'Pencocokan rekening koran bank dengan catatan kas & bank sistem', category: 'Keuangan & Biaya', route: '{{ route('finance.reconciliations.index') }}', icon: 'git-compare', keywords: 'rekonsiliasi bank bank reconciliation rekening koran statement matching' },
            @endif

            { title: 'Hitung HPP & Margin Produk', desc: 'Kalkulator biaya pokok produksi akurat berdasarkan formula ABC', category: 'Keuangan & Biaya', route: '{{ route('calculator.index') }}', icon: 'calculator', keywords: 'hpp cogs cost hitung biaya pokok margin harga modal abc costing' },
            { title: 'Simulator Harga Jual', desc: 'Simulasi target margin laba dan analisis sensitivitas harga', category: 'Keuangan & Biaya', route: '{{ route('simulator.index') }}', icon: 'gauge', keywords: 'simulator harga jual margin simulasi skenario what if price simulator' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true)
                { title: 'Biaya Mesin & Tenaga Kerja', desc: 'Pengaturan tarif kerja per jam dan biaya operasional mesin', category: 'Keuangan & Biaya', route: '{{ route('labor-machines.index') }}', icon: 'cog', keywords: 'tarif mesin machine tenaga kerja labor upah listrik jam kerja overhead' },
            @endif
            { title: 'Data Karyawan & Slip Gaji', desc: 'Penggajian bulanan staf, kasbon pinjaman, dan cetak slip gaji', category: 'Keuangan & Biaya', route: '{{ route('hrm.index') }}', icon: 'badge-cent', keywords: 'gaji payroll slip gaji payslip karyawan upah hrm kasbon pinjaman' },
            { title: 'Perhitungan Pajak Karyawan', desc: 'Perhitungan tarif efektif rata-rata (TER) PPh 21 gaji karyawan', category: 'Keuangan & Biaya', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak tax pph 21 ter karyawan gaji payroll tax potongan spt' },
        @endif

        @if ($canAccessReports)
            // 7. Laporan & Analitik
            { title: 'Pusat Laporan Bisnis', desc: 'Suite analitik dan ringkasan eksekutif seluruh laporan usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index') }}', icon: 'bar-chart-3', keywords: 'laporan reports pusat report analitik analytics suite bisnis lengkap summary' },
            @if ($topbarShowCorporateAccounting ?? true)
                { title: 'Neraca Posisi Keuangan (Balance Sheet)', desc: 'Laporan posisi keuangan aset, liabilitas, dan ekuitas perusahaan', category: 'Laporan & Analitik', route: '{{ route('finance.balance-sheet') }}', icon: 'scale', keywords: 'neraca balance sheet posisi keuangan aset pasiva modal sak emkm ekuitas assets liabilities' },
                { title: 'Neraca Saldo (Trial Balance)', desc: 'Daftar saldo debit dan kredit seluruh akun buku besar periode berjalan', category: 'Laporan & Analitik', route: '{{ route('finance.trial-balance') }}', icon: 'table-properties', keywords: 'neraca saldo trial balance debit kredit pembukuan penutupan saldo awal mutasi' },
            @endif
            { title: 'Laporan Laba Rugi (Profit & Loss)', desc: 'Laporan pendapatan bersih, total beban, dan laba/rugi usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'income_statement']) }}', icon: 'pie-chart', keywords: 'laba rugi income statement profit loss net profit omset beban pnl' },
            { title: 'Laporan Arus Kas (Cash Flow)', desc: 'Laporan pergerakan kas dari operasional, investasi, dan pendanaan', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'cash_flow']) }}', icon: 'line-chart', keywords: 'arus kas cash flow kas masuk kas keluar net cash aliran dana statement' },
            { title: 'Laporan Penjualan Kasir POS', desc: 'Rincian omset kasir, metode pembayaran, kasir bertugas, dan item terlaris', category: 'Laporan & Analitik', route: '{{ route('pos.reports.index') }}', icon: 'receipt', keywords: 'laporan kasir pos sales report penjualan omset struk rekap kasir best seller' },
            { title: 'Valuasi & Perputaran Stok', desc: 'Nilai aset persediaan gudang dan rasio perputaran barang', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'stock']) }}', icon: 'boxes', keywords: 'valuasi inventory valuation persediaan nilai stok aset turnover gudang perputaran' },
            { title: 'Kartu Mutasi Stok Gudang', desc: 'Riwayat kronologis masuk, keluar, dan penyesuaian stok produk', category: 'Laporan & Analitik', route: '{{ route('inventory.movements') }}', icon: 'activity', keywords: 'mutasi stok stock card movement kartu stok log masuk keluar pergerakan kartu' },
            { title: 'Analisis Margin & Titik Impas (BEP)', desc: 'Perhitungan Break Even Point unit & rupiah dan analisis profitabilitas', category: 'Laporan & Analitik', route: '{{ route('profitability.index') }}', icon: 'target', keywords: 'bep break even point titik impas margin analisa profit balik modal margin analysis' },
            { title: 'Kalkulasi & Kepatuhan Pajak', desc: 'Simulasi PPh Final UMKM 0.5%, PPh 21 TER karyawan, dan PPN', category: 'Laporan & Analitik', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak tax compliance pph final umkm pph 21 ter ppn setoran pajak vat' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true)
                { title: 'Analitik Media Sosial', desc: 'Wawasan jangkauan, impresi, dan engagement konten media sosial', category: 'Laporan & Analitik', route: '{{ route('social-media.insights.index') }}', icon: 'trending-up', keywords: 'analitik medsos social media insights jangkauan impresi engagement statistik' },
            @endif
        @endif

        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            // 8. Pengaturan Usaha
            { title: 'Profil Pengguna', desc: 'Informasi akun pengguna, ganti password, dan kontak WhatsApp', category: 'Pengaturan Usaha', route: '{{ route('profile.edit') }}', icon: 'user', keywords: 'profil user profile akun password kontak hp whatsapp sandi account' },
            @if ($canAccessSettings)
                { title: 'Pengaturan Usaha & Cabang', desc: 'Identitas bisnis, logo usaha, mata uang, dan alamat toko', category: 'Pengaturan Usaha', route: '{{ route('settings.index') }}', icon: 'settings', keywords: 'pengaturan business settings usaha bisnis cabang toko logo nama alamat mata uang' },
            @endif
            @if ($canAccessRoles)
                { title: 'Hak Akses & Peran Staf (RBAC)', desc: 'Kelola peran kasir, admin gudang, finance, dan hak akses staf', category: 'Pengaturan Usaha', route: '{{ route('roles.index') }}', icon: 'shield-check', keywords: 'peran role permissions hak akses staf kasir rbac wewenang access control' },
            @endif
            @if (\App\Support\Context::hasPermission('approvals.view') || \App\Support\Context::isOwner())
                { title: 'Persetujuan Dokumen (MAR)', desc: 'Pusat otorisasi bertingkat Maker-Approver-Releaser dan tiket pengadaan', category: 'Pengaturan Usaha', route: '{{ route('approvals.inbox') }}', icon: 'stamp', keywords: 'approval persetujuan mar otorisasi po expense biaya maker approver releaser authorization' },
            @endif
            @if (\App\Support\Context::hasPermission('audit_logs.view') || \App\Support\Context::isOwner() || $canAccessSettings)
                { title: 'Jejak Audit & Anti-Fraud', desc: 'Penjelajah log forensik mutasi data sensitif, visual diff, dan deteksi kecurangan', category: 'Pengaturan Usaha', route: '{{ route('settings.audit-logs.index') }}', icon: 'shield-alert', keywords: 'audit log jejak forensik diff riwayat void fraud anti fraud keamanan ip log trail security' },
            @endif
            @if ($canAccessBilling)
                { title: 'Paket Berlangganan & Kuota', desc: 'Status paket langganan aktif, batas pemakaian fitur, dan upgrade', category: 'Pengaturan Usaha', route: '{{ route('billing.limits') }}', icon: 'credit-card', keywords: 'paket langganan subscription kuota billing limit quota upgrade tagihan perpanjang' },
            @endif
            { title: 'Buku Panduan & Dokumentasi', desc: 'Panduan lengkap cara penggunaan seluruh fitur ERP, Kasir POS, dan modul Cooca', category: 'Pengaturan Usaha', route: '{{ route('docs.index') }}', icon: 'book-open', keywords: 'panduan dokumentasi docs guide manual tutorial cara pakai buku petunjuk kasir pos stok akuntansi' },
            { title: 'Bantuan & Kontak Dukungan', desc: 'Layanan bantuan teknis dan laporan kendala penggunaan sistem', category: 'Pengaturan Usaha', route: '{{ route('feedback.bugs.index') }}', icon: 'help-circle', keywords: 'bantuan support help tiket cs panduan kendala error kontak bug tickets' },
            { title: 'Komunitas & Event Bisnis', desc: 'Wadah berbagi insight bisnis, pendaftaran event, dan presensi jejaring UMKM', category: 'Pengaturan Usaha', route: '{{ route('community.index') }}', icon: 'users', keywords: 'komunitas community owner event seminar workshop bazaar forum diskusi tips umkm kolaborasi jejaring bisnis' },
        @endif
    ],
    get filteredItems() {
        let q = this.query.trim().toLowerCase();
        let cat = this.category;
        return this.items.filter(item => {
            let matchCat = (cat === 'all') || (item.category === cat);
            if (!matchCat) return false;
            if (!q) return true;
            return item.title.toLowerCase().includes(q) ||
                   item.desc.toLowerCase().includes(q) ||
                   (item.keywords && item.keywords.toLowerCase().includes(q));
        });
    },
    open() {
        this.isOpen = true;
        this.spotlightOpen = true;
        this.query = '';
        this.category = 'all';
        this.selectedIndex = 0;
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
            if (window.lucide) window.lucide.createIcons();
        });
    },
    close() {
        this.isOpen = false;
        this.spotlightOpen = false;
    },
    selectNext() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex + 1) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    selectPrev() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    executeSelected() {
        if (this.filteredItems.length > 0 && this.filteredItems[this.selectedIndex]) {
            window.location.href = this.filteredItems[this.selectedIndex].route;
        }
    },
    scrollToSelected() {
        this.$nextTick(() => {
            const el = document.getElementById('spotlight-item-' + this.selectedIndex);
            if (el) el.scrollIntoView({ block: 'nearest' });
        });
    }
}"
@keydown.window.prevent.cmd.k="open()"
@keydown.window.prevent.ctrl.k="open()"
@open-spotlight.window="open()">

    <!-- Backdrop Modal (Frosted Glass Apple Blur) -->
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-start justify-center pt-12 sm:pt-20 px-3 sm:px-4"
        style="display: none;">

        <!-- Modal Dialog Box (Apple HIG Bento Architecture) -->
        <div @click.stop
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            class="w-full max-w-2xl bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[20px] shadow-[0_24px_64px_rgba(0,0,0,0.3)] border border-black/10 dark:border-white/15 overflow-hidden flex flex-col max-h-[82vh] transition-all">

            <!-- Search Input Header Row -->
            <div class="p-3.5 sm:p-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input x-ref="searchInput"
                    x-model="query"
                    @input="selectedIndex = 0"
                    @keydown.arrow-down.prevent="selectNext()"
                    @keydown.arrow-up.prevent="selectPrev()"
                    @keydown.enter.prevent="executeSelected()"
                    @keydown.esc="close()"
                    type="text"
                    placeholder="{{ __('navigation.search_placeholder') }}"
                    class="flex-1 bg-transparent border-none text-base sm:text-[15px] font-medium text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-0">
                <button type="button" @click="close()"
                    class="p-1.5 rounded-[7px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-all cursor-pointer"
                    title="{{ __('common.close') }} (Esc)">
                    <kbd class="px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shadow-sm">Esc</kbd>
                </button>
            </div>

            <!-- Category Filter Chips Bar -->
            <div class="px-3.5 sm:px-4 py-2 border-b border-black/5 dark:border-white/10 flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button"
                        @click="category = cat.id; selectedIndex = 0; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })"
                        :class="category === cat.id ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/65 dark:text-white/65 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                        class="px-2.5 py-1 rounded-[7px] text-[11px] whitespace-nowrap transition-all shrink-0 cursor-pointer">
                        <span x-text="cat.label"></span>
                    </button>
                </template>
            </div>

            <!-- Results Scrollable List Area -->
            <div class="flex-1 overflow-y-auto p-2 sm:p-3 space-y-1 min-h-[160px] max-h-[50vh] overscroll-contain">
                <!-- Result Item Loop -->
                <template x-for="(item, idx) in filteredItems" :key="item.route">
                    <a :href="item.route"
                        :id="'spotlight-item-' + idx"
                        @mouseenter="selectedIndex = idx"
                        :class="selectedIndex === idx ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'"
                        class="flex items-center justify-between p-2.5 rounded-[12px] transition-all cursor-pointer group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.04] dark:bg-white/[0.08] text-[#007AFF] dark:text-[#0A84FF]'"
                                class="w-8 h-8 rounded-[8px] flex items-center justify-center shrink-0 transition-colors">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-semibold truncate leading-tight" x-text="item.title"></span>
                                    <span :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.05] dark:bg-white/[0.1] text-black/55 dark:text-white/55'"
                                        class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-medium tracking-wide"
                                        x-text="item.category"></span>
                                </div>
                                <p :class="selectedIndex === idx ? 'text-white/80' : 'text-black/50 dark:text-white/50'"
                                    class="text-[11px] truncate leading-tight mt-0.5"
                                    x-text="item.desc"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 pl-2">
                            <span :class="selectedIndex === idx ? 'opacity-100 text-white' : 'opacity-0'"
                                class="text-[11px] font-medium transition-opacity flex items-center gap-1">
                                <span>{{ __('navigation.spotlight_open') }}</span>
                                <kbd class="px-1.5 py-0.5 rounded bg-white/25 text-[10px] font-mono">↵</kbd>
                            </span>
                        </div>
                    </a>
                </template>

                <!-- Empty State (No Results Found) -->
                <div x-show="filteredItems.length === 0"
                    class="py-10 px-4 text-center space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/40 dark:text-white/40">
                        <i data-lucide="search-x" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[14px] font-semibold text-black dark:text-white">{{ __('navigation.search_no_results') }}</div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        <span x-text="'&quot;' + query + '&quot;'"></span>
                    </p>
                </div>
            </div>

            <!-- Footer Keyboard Navigation Helper Bar -->
            <div class="p-2.5 sm:px-4 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 select-none">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↑</kbd>
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↓</kbd>
                        <span>{{ __('navigation.spotlight_navigate') }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↵</kbd>
                        <span>{{ __('navigation.spotlight_open') }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">Esc</kbd>
                    <span>{{ __('navigation.spotlight_close') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

