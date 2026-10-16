@php
    $activeBiz = $activeBiz ?? \App\Support\Context::business();
    $navEntitlement = $navEntitlement ?? app(\App\Domain\Billing\EntitlementService::class);
    $navUsage = $navUsage ?? ($activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null);
    $isCorePlan = $navUsage['is_core'] ?? false;

    $activeModuleKey = \App\Support\Navigation\NavigationRegistry::getActiveModuleKey();

    $isPosRoute = (request()->routeIs('pos.*') && !request()->routeIs('pos.modifiers.*')) || $activeModuleKey === 'pos';
    $isB2bSalesRoute = request()->routeIs('sales.*') || request()->routeIs('invoices.*') || in_array($activeModuleKey, ['sales'], true);
    $isSalesRoute = $isPosRoute || $isB2bSalesRoute || request()->routeIs('customers.*') || request()->routeIs('crm.*') || in_array($activeModuleKey, ['sales', 'crm'], true);
    $isPurchasingRoute =
        request()->routeIs('purchasing.*') ||
        request()->routeIs('purchase-orders.*') ||
        request()->routeIs('purchase.returns.*') ||
        request()->routeIs('suppliers.*') ||
        $activeModuleKey === 'purchasing';
    $isInventoryRoute =
        request()->routeIs('products.*') ||
        request()->routeIs('services.*') ||
        request()->routeIs('materials.*') ||
        request()->routeIs('inventory.*') ||
        request()->routeIs('warehouse.*') ||
        request()->routeIs('import.*') ||
        request()->routeIs('product-categories.*') ||
        request()->routeIs('material-categories.*') ||
        request()->routeIs('units.*') ||
        request()->routeIs('marketplace-hub.*') ||
        request()->routeIs('pos.modifiers.*') ||
        in_array($activeModuleKey, ['products', 'materials', 'inventory'], true);
    $isFinanceRoute =
        request()->routeIs('finance.*') ||
        request()->routeIs('calculator.*') ||
        request()->routeIs('labor-machines.*') ||
        request()->routeIs('profitability.*') ||
        request()->routeIs('simulator.*') ||
        request()->routeIs('reports.*') ||
        request()->routeIs('pos.reports.*') ||
        request()->routeIs('hrm.*') ||
        request()->routeIs('tax.*') ||
        in_array($activeModuleKey, ['finance', 'accounting'], true);
    $isMarketingRoute =
        request()->routeIs('storefront.*') ||
        request()->routeIs('landing-page.*') ||
        request()->routeIs('whatsapp.*') ||
        request()->routeIs('social-media.*') ||
        in_array($activeModuleKey, ['communication', 'storefront'], true);
    $isChannelsRoute =
        request()->routeIs('storefront.*') ||
        request()->routeIs('landing-page.*') ||
        request()->routeIs('whatsapp.*') ||
        request()->routeIs('social-media.*') ||
        in_array($activeModuleKey, ['communication', 'storefront'], true);
    $isApprovalsRoute = request()->routeIs('approvals.*') || $activeModuleKey === 'approvals';
    $isSettingsRoute =
        request()->routeIs('settings.*') ||
        request()->routeIs('approval-rules.*') ||
        request()->routeIs('audit-logs.*') ||
        request()->routeIs('roles.*') ||
        request()->routeIs('billing.*') ||
        request()->routeIs('community.*') ||
        request()->routeIs('feedback.*') ||
        request()->routeIs('docs.*') ||
        request()->routeIs('profile.*') ||
        $isApprovalsRoute;

    // Granular RBAC Permissions
    $hasPosModule =
        ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_RETAIL) ?? true) ||
        ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) ?? true);

    $canAccessPos =
        (request()->routeIs('pos.*') || $hasPosModule) &&
        (
            \App\Support\Context::hasPermission('pos.terminal') ||
            \App\Support\Context::hasPermission('pos.orders') ||
            \App\Support\Context::hasPermission('pos.reports') ||
            \App\Support\Context::hasPermission('pos.kitchen') ||
            \App\Support\Context::hasPermission('pos.tables')
        );

    $canAccessB2bSales =
        (request()->routeIs('sales.*') || request()->routeIs('invoices.*') || ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES) ?? true)) &&
        (
            \App\Support\Context::hasPermission('sales.view') ||
            \App\Support\Context::hasPermission('sales.pipeline') ||
            \App\Support\Context::hasPermission('invoices.view') ||
            \App\Support\Context::hasPermission('sales.returns')
        );

    $canAccessCrm =
        \App\Support\Context::hasPermission('customers.view') || \App\Support\Context::hasPermission('crm.view');

    $canAccessSales = $canAccessPos || $canAccessB2bSales || $canAccessCrm;

    $canAccessPurchasing =
        (request()->routeIs('purchasing.*') || request()->routeIs('purchase-orders.*') || request()->routeIs('purchase.returns.*') || request()->routeIs('suppliers.*') || ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_PROCUREMENT) ?? true)) &&
        (
            \App\Support\Context::hasPermission('purchasing.view') ||
            \App\Support\Context::hasPermission('purchasing.manage') ||
            \App\Support\Context::hasPermission('purchasing.bills') ||
            \App\Support\Context::hasPermission('purchase.returns') ||
            \App\Support\Context::hasPermission('receiving.manage') ||
            \App\Support\Context::hasPermission('master_data.suppliers.view')
        );

    $canAccessInventory =
        \App\Support\Context::hasPermission('products.view') ||
        \App\Support\Context::hasPermission('materials.view') ||
        \App\Support\Context::hasPermission('inventory.view') ||
        \App\Support\Context::hasPermission('inventory.manage') ||
        \App\Support\Context::hasPermission('warehouse.view') ||
        \App\Support\Context::hasPermission('warehouse.manage') ||
        \App\Support\Context::hasPermission('pos.modifiers');

    $hasCostingModule =
        ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true) ||
        ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true);

    $canAccessCosting =
        (request()->routeIs('calculator.*') || request()->routeIs('simulator.*') || request()->routeIs('labor-machines.*') || request()->routeIs('profitability.*') || $hasCostingModule) &&
        (
            \App\Support\Context::hasPermission('costing.view_margin') ||
            \App\Support\Context::hasPermission('costing.manage') ||
            \App\Support\Context::hasPermission('labor_machines.view') ||
            \App\Support\Context::hasPermission('reports.costing')
        );

    $canAccessFinance =
        \App\Support\Context::hasPermission('accounting.view') ||
        \App\Support\Context::hasPermission('finance.cash_bank') ||
        \App\Support\Context::hasPermission('finance.receivables') ||
        \App\Support\Context::hasPermission('finance.payables') ||
        \App\Support\Context::hasPermission('expenses.view') ||
        \App\Support\Context::hasPermission('expenses.manage');

    $canAccessReports =
        \App\Support\Context::hasPermission('reports.view') || \App\Support\Context::hasPermission('pos.reports');

    $canAccessHrm = \App\Support\Context::hasPermission('users.manage') || \App\Support\Context::isOwner();
    $canAccessEmployees = $canAccessHrm;

    $canAccessChannels =
        \App\Support\Context::hasPermission('whatsapp.view') ||
        \App\Support\Context::hasPermission('whatsapp.manage');

    $canAccessStorefront =
        \App\Support\Context::hasPermission('storefront.orders.view') ||
        \App\Support\Context::hasPermission('storefront.manage') ||
        \App\Support\Context::hasPermission('storefront.shipping.manage') ||
        \App\Support\Context::hasPermission('storefront.reservations.manage') ||
        \App\Support\Context::hasPermission('storefront.po.manage') ||
        \App\Support\Context::hasPermission('cms.manage') ||
        \App\Support\Context::isOwner();

    $canAccessApprovals =
        \App\Support\Context::hasPermission('approvals.view') ||
        \App\Support\Context::hasPermission('approvals.manage') ||
        \App\Support\Context::isOwner();
    $canAccessSettings = \App\Support\Context::hasPermission('settings.view') || \App\Support\Context::isOwner();
    $canAccessAuditLogs = \App\Support\Context::hasPermission('audit_logs.view') || \App\Support\Context::isOwner() || $canAccessSettings;
    $canAccessRoles = \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::isOwner();
    $canAccessBilling = \App\Support\Context::hasPermission('billing.view') || \App\Support\Context::isOwner();
    $canAccessMasterData =
        \App\Support\Context::hasPermission('master_data.material_categories.view') ||
        \App\Support\Context::hasPermission('master_data.product_categories.view') ||
        \App\Support\Context::hasPermission('master_data.units.view') ||
        \App\Support\Context::hasPermission('master_data.suppliers.view');

    $canAccessSystem =
        $canAccessMasterData ||
        $canAccessSettings ||
        $canAccessAuditLogs ||
        $canAccessRoles ||
        $canAccessBilling ||
        \App\Support\Context::isOwner();

    $sidebarBiz = \App\Support\Context::business();
    $sidebarStoreSetting = $sidebarBiz?->storeSetting;
    $sidebarShowReservation = request()->routeIs('storefront.reservations.*')
        || (
            $sidebarBiz
            && $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION)
            && ($sidebarStoreSetting->allow_reservation ?? true)
        );
    $sidebarShowShipping = request()->routeIs('storefront.shipping.*')
        || (
            $sidebarBiz
            && $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING)
            && ($sidebarStoreSetting->allow_delivery ?? true)
        );
@endphp

@include('layouts.partials.sidebar-styles')

{{-- Sidebar Navigation (Apple HIG / macOS Sonoma Style) --}}
<aside
    x-data="{ touchStartX: 0 }"
    @touchstart="touchStartX = $event.touches[0].clientX"
    @touchend="if (touchStartX - $event.changedTouches[0].clientX > 45) { sidebarOpen = false; }"
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'lg:w-[76px] is-collapsed': sidebarCollapsed,
        'lg:w-[272px]': !sidebarCollapsed
    }"
    @keydown.esc.window="sidebarOpen = false"
    class="app-sidebar fixed inset-y-0 left-0 z-50 w-72 max-w-[84vw] h-screen max-h-screen glass-nav flex flex-col justify-between transition-all duration-300 ease-out max-lg:rounded-r-[20px] max-lg:shadow-[0_20px_50px_rgba(0,0,0,0.25)]">

    {{-- 1. Brand Logo Header (macOS Toolbar Height h-14) --}}
    <div
        class="sidebar-header h-14 flex items-center justify-between px-4 border-b border-black/5 dark:border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group overflow-hidden">
            <div id="tour-active-business"
                class="w-8 h-8 rounded-[9px] bg-[#007AFF] text-white flex items-center justify-center shadow-[0_1px_2px_rgba(0,122,255,0.3)] group-hover:scale-105 active:scale-95 transition-all shrink-0">
                <i data-lucide="boxes" class="w-4 h-4 text-white"></i>
            </div>
            <div x-show="!sidebarCollapsed" x-transition.opacity
                class="sidebar-brand-text overflow-hidden whitespace-nowrap">
                <div class="font-semibold text-[15px] text-black dark:text-white tracking-tight leading-none">Cooca
                </div>
                <div class="text-[10px] text-black/45 dark:text-white/45 font-medium tracking-wide mt-0.5">Business OS
                </div>
            </div>
        </a>
        {{-- Mobile Drawer Close Button --}}
        <button @click="sidebarOpen = false" type="button" aria-label="Tutup Menu"
            class="lg:hidden text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white p-1.5 rounded-[8px] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] active:scale-95 transition-all cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- 2. Active Tenant / Business Switcher Capsule --}}
    @if ($activeBiz)
        <div class="sidebar-tenant-wrapper px-3 py-2 border-b border-black/5 dark:border-white/10 shrink-0">
            <div class="relative group"
                :title="sidebarCollapsed ? @js($activeBiz->name . ' (Klik untuk Ganti Bisnis)') : ''">
                <a href="{{ route('businesses.select') }}" id="tour-switch-business"
                    aria-label="Ganti Bisnis: {{ $activeBiz->name }}"
                    class="sidebar-tenant-card group/card p-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between transition-all hover:border-[#007AFF]/40 hover:bg-[#007AFF]/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
                    <div class="sidebar-tenant-inner flex items-center gap-2.5 overflow-hidden">
                        @if ($activeBiz->logo_url)
                            <div
                                class="sidebar-tenant-icon w-7 h-7 rounded-[7px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                <img src="{{ $activeBiz->logo_url }}" alt="{{ $activeBiz->name }}"
                                    class="w-full h-full object-contain">
                            </div>
                        @else
                            <div
                                class="sidebar-tenant-icon w-7 h-7 rounded-[7px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            </div>
                        @endif
                        <div class="overflow-hidden" x-show="!sidebarCollapsed" x-transition.opacity>
                            <div
                                class="text-[10px] text-black/40 dark:text-white/40 font-medium uppercase tracking-wider">
                                Bisnis Aktif</div>
                            <div class="text-[12px] font-semibold text-black dark:text-white truncate">
                                {{ $activeBiz->name }}</div>
                        </div>
                    </div>
                    <span aria-hidden="true" x-show="!sidebarCollapsed"
                        class="p-1.5 rounded-[7px] text-black/40 dark:text-white/40 group-hover/card:text-[#007AFF] dark:group-hover/card:text-[#0A84FF] transition-colors shrink-0">
                        <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
                <template x-if="sidebarCollapsed">
                    <a href="{{ route('businesses.select') }}" class="absolute inset-0 z-10"
                        title="Ganti Bisnis: {{ $activeBiz->name }}"></a>
                </template>
            </div>
        </div>
    @endif

    {{-- 3. Navigation Links (Clean macOS Sidebar Architecture - No Card Boxes) --}}
        {{-- 3. Navigation Links (Clean macOS Sidebar Architecture - 9 Standard Domain Clusters) --}}
    <nav aria-label="Navigasi utama"
        class="sidebar-nav flex-1 overflow-y-auto px-2.5 py-3 space-y-3 min-h-0 overscroll-contain text-xs"
        x-data="{
            salesOpen: {{ ($isB2bSalesRoute || $isSalesRoute) ? 'true' : 'false' }},
            posOpen: {{ $isPosRoute ? 'true' : 'false' }},
            b2bSalesOpen: {{ $isB2bSalesRoute ? 'true' : 'false' }},
            inventoryOpen: {{ $isInventoryRoute ? 'true' : 'false' }},
            purchasingOpen: {{ $isPurchasingRoute ? 'true' : 'false' }},
            financeOpen: {{ $isFinanceRoute ? 'true' : 'false' }},
            accountingOpen: {{ (request()->routeIs('finance.coa.*') || request()->routeIs('finance.journals.*') || request()->routeIs('finance.general-ledger') || request()->routeIs('finance.balance-sheet') || request()->routeIs('finance.trial-balance') || request()->routeIs('finance.reconciliations.*')) ? 'true' : 'false' }},
            costingOpen: {{ (request()->routeIs('calculator.*') || request()->routeIs('simulator.*') || request()->routeIs('labor-machines.*') || request()->routeIs('profitability.*')) ? 'true' : 'false' }},
            marketingOpen: {{ ($isChannelsRoute || (isset($isMarketingRoute) && $isMarketingRoute)) ? 'true' : 'false' }},
            reportsOpen: {{ (request()->routeIs('reports.*') || request()->routeIs('analytics.*') || request()->routeIs('pos.reports.*') || request()->routeIs('tax.*')) ? 'true' : 'false' }},
            settingsOpen: {{ $isSettingsRoute ? 'true' : 'false' }},
            activeFlyout: null
        }">

        {{-- ======================================================== --}}
        {{-- KLASTER 1: CORE BISNIS                                   --}}
        {{-- ======================================================== --}}
        @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view') || $canAccessPos || $canAccessB2bSales || $canAccessStorefront || $canAccessInventory)
        <div class="space-y-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.core_business') }}
                <span class="sr-only">{{ __('navigation.clusters.daily_ops') }} {{ __('navigation.cluster_daily_ops') }}</span>
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 1.1 Dashboard Utama --}}
            @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view'))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'dashboard'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('dashboard') }}" id="tour-nav-dashboard"
                        {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.dashboard_main') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.dashboard_main') }}</span>
                    </a>
                </div>
            @endif

            {{-- 1.2 Kasir & POS --}}
            @if ($canAccessPos)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'pos'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-pos" data-tour-group="pos"
                        @click="posOpen = !posOpen" role="button"
                        :aria-expanded="posOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? @js(($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_terminal')) : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isPosRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="calculator"
                                class="w-4 h-4 {{ $isPosRoute ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ ($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_terminal') }}</span>
                            <span class="sr-only">{{ __('navigation.pos_terminal') }}</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="posOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    <div x-show="posOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.terminal') }}" id="tour-nav-pos-terminal"
                                {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.terminal') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 {{ request()->routeIs('pos.terminal') ? 'text-white' : 'text-[#007AFF] dark:text-[#0A84FF]' }} shrink-0"></i>
                                <span class="truncate font-semibold">{{ __('navigation.pos_open') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.orders.index') }}" id="tour-nav-pos-orders"
                                {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('pos.orders.*') && !request()->routeIs('pos.orders.kitchen') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.pos_shifts') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('pos.kitchen') && ($activeBiz && $activeBiz->hasDineInFeature()))
                            <a href="{{ route('pos.kitchen.index') }}" id="tour-nav-pos-kitchen"
                                {{ request()->routeIs('pos.kitchen.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.kitchen.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="chef-hat" class="w-3.5 h-3.5 {{ request()->routeIs('pos.kitchen.*') ? 'text-white' : 'text-[#FF9500]' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.kitchen_kds') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('pos.tables') && ($activeBiz && $activeBiz->hasDineInFeature()))
                            <a href="{{ route('pos.tables.index') }}" id="tour-nav-pos-tables"
                                {{ request()->routeIs('pos.tables.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.tables.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5 {{ request()->routeIs('pos.tables.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.dinein_tables') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Collapsed Flyout for POS --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'pos'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ ($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_terminal') }}
                        </div>
                        @if (\App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.terminal') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span class="font-semibold text-[#007AFF]">{{ __('navigation.pos_open') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::hasPermission('pos.terminal'))
                            <a href="{{ route('pos.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>{{ __('navigation.pos_shifts') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.kitchen') && ($activeBiz && $activeBiz->hasDineInFeature()))
                            <a href="{{ route('pos.kitchen.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="chef-hat" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>{{ __('navigation.kitchen_kds') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('pos.tables') && ($activeBiz && $activeBiz->hasDineInFeature()))
                            <a href="{{ route('pos.tables.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                <span>{{ __('navigation.dinein_tables') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 1.3 Penjualan B2B & Faktur --}}
            @if ($canAccessB2bSales)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'b2bSales'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-b2b" data-tour-group="b2b"
                        @click="b2bSalesOpen = !b2bSalesOpen" role="button"
                        :aria-expanded="b2bSalesOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? '{{ __('navigation.b2b_sales') }}' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isB2bSalesRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="file-text"
                                class="w-4 h-4 {{ $isB2bSalesRoute ? 'text-[#5856D6]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.b2b_sales') }}</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="b2bSalesOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    <div x-show="b2bSalesOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}" id="tour-nav-sales-orders"
                                {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 {{ request()->routeIs('sales.*') && !request()->routeIs('sales.returns.*') && !request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.sales_orders') }}</span>
                            </a>

                            <a href="{{ route('sales.quotations.index') }}" id="tour-nav-sales-quotations"
                                {{ request()->routeIs('sales.quotations.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.quotations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="file-signature" class="w-3.5 h-3.5 {{ request()->routeIs('sales.quotations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.quotations') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.index') }}" id="tour-nav-invoices"
                                {{ request()->routeIs('invoices.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('invoices.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.invoices') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}" id="tour-nav-sales-returns"
                                {{ request()->routeIs('sales.returns.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('sales.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 {{ request()->routeIs('sales.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.sales_returns') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Collapsed Flyout for B2B Sales --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'b2bSales'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.b2b_sales') }}
                        </div>
                        @if (\App\Support\Context::hasPermission('sales.view'))
                            <a href="{{ route('sales.orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ __('navigation.sales_orders') }}</span>
                            </a>
                            <a href="{{ route('sales.quotations.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="file-signature" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ __('navigation.quotations') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('invoices.view'))
                            <a href="{{ route('invoices.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>{{ __('navigation.invoices') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('sales.returns'))
                            <a href="{{ route('sales.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>{{ __('navigation.sales_returns') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 1.4 Pesanan Toko Online (Daily Operational Orders) --}}
            @if ($canAccessStorefront && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT) ?? true))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'storefront_orders'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('storefront.orders.index') }}" id="tour-nav-storefront-orders"
                        {{ request()->routeIs('storefront.orders.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.storefront_orders') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('storefront.orders.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#007AFF]/8 dark:hover:bg-[#007AFF]/15 hover:text-[#007AFF] dark:hover:text-[#0A84FF]' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('storefront.orders.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight">{{ __('navigation.storefront_orders') }}</span>
                        </div>
                    </a>

                    {{-- Collapsed Flyout for Storefront Orders --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'storefront_orders'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.storefront_orders') }}
                        </div>
                        <a href="{{ route('storefront.orders.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.storefront_orders') }}</span>
                        </a>
                        @if ($sidebarShowReservation)
                            <a href="{{ route('storefront.reservations.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ ($activeBiz && $activeBiz->isWorkshop()) ? __('navigation.booking_service') : ($activeBiz?->hasDineInFeature() ? __('navigation.reservations_resto') : __('navigation.reservations')) }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 1.4B Reservasi Meja / Booking Servis (Tergantung Industri) --}}
            @if ($sidebarShowReservation && $canAccessStorefront)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'reservations'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('storefront.reservations.index') }}" id="tour-nav-storefront-reservations"
                        {{ request()->routeIs('storefront.reservations.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? @js(($activeBiz && $activeBiz->isWorkshop()) ? __('navigation.booking_service') : ($activeBiz?->hasDineInFeature() ? __('navigation.reservations_resto') : __('navigation.reservations'))) : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('storefront.reservations.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#007AFF]/8 dark:hover:bg-[#007AFF]/15 hover:text-[#007AFF] dark:hover:text-[#0A84FF]' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('storefront.reservations.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight">{{ ($activeBiz && $activeBiz->isWorkshop()) ? __('navigation.booking_service') : ($activeBiz?->hasDineInFeature() ? __('navigation.reservations_resto') : __('navigation.reservations')) }}</span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- 1.5 Layanan & Servis (Universal across all 20+ industries) --}}
            @php
                $canAccessServicesNav = ($canAccessInventory || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
                    && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) ?? true);
            @endphp
            @if ($canAccessServicesNav)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'services'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('services.index') }}" id="tour-nav-services"
                        {{ request()->routeIs('services.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.services') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('services.*') ? 'bg-[#34C759] text-white shadow-[0_2px_8px_rgba(52,199,89,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#34C759]/8 dark:hover:bg-[#34C759]/15 hover:text-[#34C759] dark:hover:text-[#30D158]' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('services.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/12 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="{{ ($activeBiz && $activeBiz->isWorkshop()) ? 'wrench' : 'briefcase' }}" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight">{{ __('navigation.services') }}</span>
                        </div>
                    </a>

                    {{-- Collapsed Flyout for Services --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'services'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.services') }}
                        </div>
                        <a href="{{ route('services.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="{{ ($activeBiz && $activeBiz->isWorkshop()) ? 'wrench' : 'briefcase' }}" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>{{ __('navigation.services') }}</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 1.6 Asisten Cerdas AI / 3D Virtual Office --}}
            @if (\App\Support\Context::hasPermission('ai.access') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'ai'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('cooca-ai.index') }}" id="tour-nav-ai"
                        {{ request()->routeIs('cooca-ai.*') || request()->routeIs('ai.*') || request()->routeIs('pos.ai.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '3D Virtual Office' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('cooca-ai.*') || request()->routeIs('ai.*') || request()->routeIs('pos.ai.*') ? 'bg-gradient-to-r from-[#AF52DE] to-[#5856D6] text-white shadow-[0_2px_8px_rgba(175,82,222,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#AF52DE]/8 dark:hover:bg-[#AF52DE]/15 hover:text-[#AF52DE] dark:hover:text-[#BF5AF2]' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('cooca-ai.*') || request()->routeIs('ai.*') || request()->routeIs('pos.ai.*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="box" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight font-bold">3D Virtual Office</span>
                        </div>
                    </a>

                    @if (request()->routeIs('cooca-ai.*') || request()->routeIs('ai.*') || request()->routeIs('pos.ai.*'))
                        <div x-show="!sidebarCollapsed" class="ml-4 pl-3 py-1 space-y-0.5 border-l border-black/10 dark:border-white/10 mt-1 text-[12px]">
                            <a href="{{ route('cooca-ai.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] font-medium transition-colors {{ (request()->routeIs('cooca-ai.index') || request()->routeIs('cooca-ai.office.*')) && !request()->routeIs('pos.ai.*') && !request()->routeIs('cooca-ai.actions*') && !request()->routeIs('cooca-ai.history*') && !request()->routeIs('cooca-ai.providers*') ? 'bg-black/5 dark:bg-white/10 text-[#AF52DE] dark:text-[#BF5AF2] font-bold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">AI Studio</span>
                            </a>
                            @if ($canAccessPos && Route::has('pos.ai.index'))
                            <a href="{{ route('pos.ai.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] font-medium transition-colors {{ request()->routeIs('pos.ai.*') ? 'bg-black/5 dark:bg-white/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate">AI POS Intelligence</span>
                            </a>
                            @endif
                            <a href="{{ route('cooca-ai.actions') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] font-medium transition-colors {{ request()->routeIs('cooca-ai.actions*') ? 'bg-black/5 dark:bg-white/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                <span class="truncate">{{ __('ai.navigation.action_center') }}</span>
                            </a>
                            <a href="{{ route('cooca-ai.history') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] font-medium transition-colors {{ request()->routeIs('cooca-ai.history*') ? 'bg-black/5 dark:bg-white/10 text-sky-600 dark:text-sky-400 font-bold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                                <span class="truncate">{{ __('ai.navigation.session_history') }}</span>
                            </a>
                            <a href="{{ route('cooca-ai.providers') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] font-medium transition-colors {{ request()->routeIs('cooca-ai.providers*') ? 'bg-black/5 dark:bg-white/10 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                <span class="truncate">{{ __('ai.navigation.settings') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 1.7 Pending Approvals Pill --}}
            @php
                $pendingApprovalCount = $activeBiz && $canAccessApprovals ? \Illuminate\Support\Facades\Cache::remember("tenant_{$activeBiz->id}_pending_approvals_count", 120, fn() => \App\Models\ApprovalRequest::where('business_id', $activeBiz->id)->pending()->count()) : 0;
                $recentHighRiskCount = $activeBiz && $canAccessAuditLogs ? \Illuminate\Support\Facades\Cache::remember("tenant_{$activeBiz->id}_high_risk_logs_count", 300, fn() => \App\Models\AuditLog::where('business_id', $activeBiz->id)->highRisk()->where('created_at', '>=', now()->subDays(7))->count()) : 0;
            @endphp
            @if ($pendingApprovalCount > 0)
                <div x-show="!sidebarCollapsed" class="ml-4 pl-3 py-1 space-y-0.5 border-l border-black/10 dark:border-white/10 mt-1">
                    <a href="{{ route('approvals.inbox') }}" id="tour-nav-approvals"
                        {{ request()->routeIs('approvals.*') ? 'aria-current="page"' : '' }}
                        class="sidebar-item flex items-center justify-between px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-colors text-black/70 dark:text-white/70 hover:bg-black/[0.03] dark:hover:bg-white/[0.04]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                            <span class="truncate">{{ __('navigation.pending_approvals') }}</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30 shrink-0">
                            {{ $pendingApprovalCount }}
                        </span>
                    </a>
                </div>
            @endif

            {{-- 1.8 Komunitas & Event (Aktif Semua Jenis Subscribe) --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'community'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('community.index') }}" id="tour-nav-community"
                    {{ request()->routeIs('community.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.owner_community') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('community.*') ? 'bg-[#FF9500] text-white shadow-[0_2px_8px_rgba(255,149,0,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#FF9500]/8 dark:hover:bg-[#FF9500]/15 hover:text-[#FF9500] dark:hover:text-[#FF9F0A]' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('community.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="users-round" class="w-3.5 h-3.5"></i>
                    </span>
                    <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                        <span class="truncate tracking-tight">{{ __('navigation.owner_community') }}</span>
                    </div>
                </a>
            </div>
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 2: INVENTORY                                     --}}
        {{-- ======================================================== --}}
        @if ($canAccessInventory || $canAccessPurchasing || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.inventory') }}
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 2.1 Stok Persediaan (Stock Inventory) --}}
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true)
                @if (\App\Support\Context::hasPermission('inventory.view'))
                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'stocks'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('inventory.stocks') }}" id="tour-nav-inventory-stocks"
                            {{ request()->routeIs('inventory.stocks') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.stocks_warehouse') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('inventory.stocks') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('inventory.stocks') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.stocks_warehouse') }}</span>
                        </a>
                    </div>
                @endif

                {{-- 2.2 Lokasi Gudang (Warehouse Management Hub) --}}
                @if (\App\Support\Context::hasPermission('warehouse.view') || \App\Support\Context::isOwner())
                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'warehouses'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('warehouse.index') }}" id="tour-nav-warehouses"
                            {{ request()->routeIs('warehouse.*') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.warehouse_locations') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('warehouse.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('warehouse.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight font-medium" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.warehouse_locations') }}</span>
                        </a>
                    </div>
                @endif

                @if (\App\Support\Context::hasPermission('inventory.manage'))
                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'transfers'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('inventory.transfers.index') }}" id="tour-nav-inventory-transfers"
                            {{ request()->routeIs('inventory.transfers.*') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.stock_transfers') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.transfers.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('inventory.transfers.*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.stock_transfers') }}</span>
                        </a>
                    </div>

                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'opnames'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('inventory.opnames.index') }}" id="tour-nav-inventory-opnames"
                            {{ request()->routeIs('inventory.opnames.*') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.stock_opnames') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.opnames.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('inventory.opnames.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.stock_opnames') }}</span>
                        </a>
                    </div>

                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'movements'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('inventory.movements') }}" id="tour-nav-inventory-movements"
                            {{ request()->routeIs('inventory.movements') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.stock_movements') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inventory.movements') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('inventory.movements') ? 'bg-white/20 text-white' : 'bg-[#30B0C7]/15 text-[#30B0C7] dark:bg-[#40C8E0]/20 dark:text-[#40C8E0]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.stock_movements') }}</span>
                        </a>
                    </div>
                @endif
            @endif

            {{-- 2.2 Pengadaan & Pembelian (Purchasing) --}}
            @if ($canAccessPurchasing)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'purchasing'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-group-purchasing" data-tour-group="purchasing"
                        @click="purchasingOpen = !purchasingOpen" role="button"
                        :aria-expanded="purchasingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? '{{ __('navigation.purchasing_group') }}' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ $isPurchasingRoute ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="truck"
                                class="w-4 h-4 {{ $isPurchasingRoute ? 'text-[#30B0C7]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.purchasing_group') }}</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="purchasingOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    <div x-show="purchasingOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        @if (\App\Support\Context::hasPermission('purchasing.view'))
                            <a href="{{ route('purchase-orders.index') }}" id="tour-nav-purchase-orders"
                                {{ request()->routeIs('purchase-orders.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchase-orders.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.purchase_orders') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('purchasing.bills'))
                            <a href="{{ route('purchasing.bills.index') }}"
                                {{ request()->routeIs('purchasing.bills.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchasing.bills.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 {{ request()->routeIs('purchasing.bills.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.supplier_bills') }}</span>
                            </a>
                        @endif

                        @if (\App\Support\Context::hasPermission('purchase.returns'))
                            <a href="{{ route('purchase.returns.index') }}"
                                {{ request()->routeIs('purchase.returns.*') ? 'aria-current="page"' : '' }}
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('purchase.returns.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                                <i data-lucide="undo" class="w-3.5 h-3.5 {{ request()->routeIs('purchase.returns.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                                <span class="truncate">{{ __('navigation.purchase_returns') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Collapsed Flyout for Purchasing --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'purchasing'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.purchasing_group') }}
                        </div>
                        @if (\App\Support\Context::hasPermission('purchasing.view'))
                            <a href="{{ route('purchase-orders.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ __('navigation.purchase_orders') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchasing.bills'))
                            <a href="{{ route('purchasing.bills.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                <span>{{ __('navigation.supplier_bills') }}</span>
                            </a>
                        @endif
                        @if (\App\Support\Context::hasPermission('purchase.returns'))
                            <a href="{{ route('purchase.returns.index') }}"
                                class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                                <i data-lucide="undo" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                <span>{{ __('navigation.purchase_returns') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 3: FINANCE DAN ACOUNTING                          --}}
        {{-- ======================================================== --}}
        @if ($canAccessFinance || $canAccessCosting || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.finance_accounting') }}
                <span class="sr-only">{{ __('navigation.clusters.reports_finance') }} {{ __('navigation.cluster_finance_reports') }}</span>
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 3.1 Kas & Bank --}}
            @if ($canAccessFinance)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'cash_bank'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('finance.cash-bank.index') }}" id="tour-nav-cash-bank"
                        {{ request()->routeIs('finance.cash-bank.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.cash_bank') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('finance.cash-bank.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('finance.cash-bank.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight font-semibold">{{ __('navigation.cash_bank') }}</span>
                            <span class="sr-only">{{ __('navigation.cash_bank_books') }}</span>
                        </div>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'expenses'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('finance.expenses.index') }}" id="tour-nav-expenses"
                        {{ request()->routeIs('finance.expenses.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.expenses') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.expenses.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('finance.expenses.*') ? 'bg-white/20 text-white' : 'bg-[#FF3B30]/15 text-[#FF3B30] dark:bg-[#FF453A]/20 dark:text-[#FF453A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.expenses') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'receivables'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('finance.receivables') }}"
                        {{ request()->routeIs('finance.receivables') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.receivables') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.receivables') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('finance.receivables') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.receivables') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'payables'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('finance.payables') }}"
                        {{ request()->routeIs('finance.payables') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.payables') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.payables') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('finance.payables') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.payables') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'settlements'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('finance.settlements.index') }}"
                        {{ request()->routeIs('finance.settlements.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.settlements') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.settlements.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('finance.settlements.*') ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.settlements') }}</span>
                    </a>
                </div>
            @endif

            {{-- 3.2 Akuntansi Perusahaan --}}
            @php
                $showCorporateAccounting = $sidebarBiz ? $sidebarBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_ACCOUNTING_CORPORATE) : true;
            @endphp
            @if ($showCorporateAccounting && \App\Support\Context::hasPermission('accounting.view'))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'accounting'"
                    @mouseleave="activeFlyout = null">
                    <button type="button"
                        @click="accountingOpen = !accountingOpen" role="button"
                        :aria-expanded="accountingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? '{{ __('navigation.accounting_corporate') }}' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ (request()->routeIs('finance.coa.*') || request()->routeIs('finance.journals.*') || request()->routeIs('finance.general-ledger') || request()->routeIs('finance.balance-sheet') || request()->routeIs('finance.trial-balance') || request()->routeIs('finance.reconciliations.*')) ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="book-open"
                                class="w-4 h-4 {{ (request()->routeIs('finance.coa.*') || request()->routeIs('finance.journals.*') || request()->routeIs('finance.general-ledger') || request()->routeIs('finance.balance-sheet') || request()->routeIs('finance.trial-balance') || request()->routeIs('finance.reconciliations.*')) ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.accounting_corporate') }}</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="accountingOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    <div x-show="accountingOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        <a href="{{ route('finance.coa.index') }}"
                            {{ request()->routeIs('finance.coa.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.coa.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="list-tree" class="w-3.5 h-3.5 {{ request()->routeIs('finance.coa.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.coa') }}</span>
                        </a>

                        <a href="{{ route('finance.journals.index') }}"
                            {{ request()->routeIs('finance.journals.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.journals.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 {{ request()->routeIs('finance.journals.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.journals') }}</span>
                        </a>

                        <a href="{{ route('finance.general-ledger') }}"
                            {{ request()->routeIs('finance.general-ledger') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.general-ledger') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="book-open" class="w-3.5 h-3.5 {{ request()->routeIs('finance.general-ledger') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.general_ledger') }}</span>
                        </a>

                        <a href="{{ route('finance.balance-sheet') }}"
                            {{ request()->routeIs('finance.balance-sheet') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.balance-sheet') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="scale" class="w-3.5 h-3.5 {{ request()->routeIs('finance.balance-sheet') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.balance_sheet') }}</span>
                        </a>

                        <a href="{{ route('finance.trial-balance') }}"
                            {{ request()->routeIs('finance.trial-balance') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.trial-balance') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="table-properties" class="w-3.5 h-3.5 {{ request()->routeIs('finance.trial-balance') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.trial_balance') }}</span>
                        </a>

                        <a href="{{ route('finance.reconciliations.index') }}"
                            {{ request()->routeIs('finance.reconciliations.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.reconciliations.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="git-compare" class="w-3.5 h-3.5 {{ request()->routeIs('finance.reconciliations.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.bank_reconciliation') }}</span>
                        </a>
                    </div>

                    {{-- Collapsed Flyout for Corporate Accounting --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'accounting'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.accounting_corporate') }}
                        </div>
                        <a href="{{ route('finance.coa.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="list-tree" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.coa') }}</span>
                        </a>
                        <a href="{{ route('finance.journals.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.journals') }}</span>
                        </a>
                        <a href="{{ route('finance.general-ledger') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.general_ledger') }}</span>
                        </a>
                        <a href="{{ route('finance.balance-sheet') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="scale" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.balance_sheet') }}</span>
                        </a>
                        <a href="{{ route('finance.reconciliations.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="git-compare" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.bank_reconciliation') }}</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 3.3 Kalkulasi HPP & Biaya Pokok --}}
            @if ($canAccessCosting)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'costing'"
                    @mouseleave="activeFlyout = null">
                    <button type="button" id="tour-nav-calculator"
                        @click="costingOpen = !costingOpen" role="button"
                        :aria-expanded="costingOpen ? 'true' : 'false'"
                        :title="sidebarCollapsed ? '{{ __('navigation.costing_margin') }}' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] text-left text-[13px] font-medium transition-all active:scale-[0.98] {{ (request()->routeIs('calculator.*') || request()->routeIs('simulator.*') || request()->routeIs('labor-machines.*') || request()->routeIs('profitability.*')) ? 'bg-black/[0.05] dark:bg-white/[0.06] text-black dark:text-white font-semibold' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                        <div class="sidebar-item-inner flex items-center gap-2.5 min-w-0">
                            <i data-lucide="calculator"
                                class="w-4 h-4 {{ (request()->routeIs('calculator.*') || request()->routeIs('simulator.*') || request()->routeIs('labor-machines.*') || request()->routeIs('profitability.*')) ? 'text-[#007AFF]' : 'text-black/50 dark:text-white/50' }} shrink-0"></i>
                            <span class="truncate font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.costing_margin') }}</span>
                            <span class="sr-only">{{ __('navigation.hpp_cost_calculator') }}</span>
                        </div>
                        <i data-lucide="chevron-down" x-show="!sidebarCollapsed"
                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40 transition-transform duration-200 shrink-0"
                            :class="costingOpen ? 'rotate-180 text-black/70 dark:text-white/70' : ''"></i>
                    </button>

                    <div x-show="costingOpen && !sidebarCollapsed"
                        x-transition:enter="transition-all ease-out duration-150"
                        class="pl-3 pr-1 py-0.5 space-y-0.5 border-l border-black/5 dark:border-white/10 ml-4">
                        <a href="{{ route('calculator.index') }}"
                            {{ request()->routeIs('calculator.index') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('calculator.index') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="calculator" class="w-3.5 h-3.5 {{ request()->routeIs('calculator.index') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.calculator_hpp') }}</span>
                        </a>

                        <a href="{{ route('simulator.index') }}" id="tour-nav-simulator"
                            {{ request()->routeIs('simulator.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('simulator.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="cpu" class="w-3.5 h-3.5 {{ request()->routeIs('simulator.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.pricing_simulator') }}</span>
                        </a>

                        @if (\App\Support\Context::hasPermission('labor_machines.view') && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true))
                        <a href="{{ route('labor-machines.index') }}" id="tour-nav-labor-machines"
                            {{ request()->routeIs('labor-machines.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('labor-machines.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="hammer" class="w-3.5 h-3.5 {{ request()->routeIs('labor-machines.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.labor_machines') }}</span>
                        </a>
                        @endif

                        <a href="{{ route('profitability.index') }}" id="tour-nav-profitability"
                            {{ request()->routeIs('profitability.*') ? 'aria-current="page"' : '' }}
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profitability.*') ? 'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' : 'text-black/65 dark:text-white/65 hover:bg-black/[0.04] dark:hover:bg-white/[0.05] hover:text-black dark:hover:text-white' }}">
                            <i data-lucide="pie-chart" class="w-3.5 h-3.5 {{ request()->routeIs('profitability.*') ? 'text-white' : 'text-black/40 dark:text-white/40' }} shrink-0"></i>
                            <span class="truncate">{{ __('navigation.profitability_bep') }}</span>
                        </a>
                    </div>

                    {{-- Collapsed Flyout for Costing --}}
                    <div x-show="sidebarCollapsed && activeFlyout === 'costing'" x-transition.opacity
                        class="fixed left-[84px] -mt-8 w-60 p-2 rounded-[14px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.18)] z-50 space-y-1 pointer-events-auto max-h-[85vh] overflow-y-auto overscroll-contain"
                        style="display: none;">
                        <div class="px-2.5 py-1 font-semibold text-xs text-black dark:text-white border-b border-black/5 dark:border-white/10 pb-1.5 mb-1">
                            {{ __('navigation.costing_margin') }}
                        </div>
                        <a href="{{ route('calculator.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('navigation.calculator_hpp') }}</span>
                        </a>
                        <a href="{{ route('simulator.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="cpu" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                            <span>{{ __('navigation.pricing_simulator') }}</span>
                        </a>
                        <a href="{{ route('profitability.index') }}"
                            class="sidebar-item flex items-center gap-2 px-2.5 py-1.5 rounded-[7px] text-xs text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                            <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                            <span>{{ __('navigation.profitability_bep') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 4: CRM (CUSTOMER RELATIONSHIP MANAGEMENT)         --}}
        {{-- ======================================================== --}}
        @if ($canAccessCrm || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.crm') }}
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 4.1 Data Pelanggan --}}
            @if (\App\Support\Context::hasPermission('customers.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'customers'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('customers.index') }}" id="tour-nav-customers"
                        {{ request()->routeIs('customers.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.customers_data') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('customers.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('customers.*') ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.customers_data') }}</span>
                    </a>
                </div>
            @endif

            {{-- 4.2 Membership & Loyalty --}}
            @if (\App\Support\Context::hasPermission('crm.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'crm_members'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('crm.members.index') }}"
                        {{ request()->routeIs('crm.members.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.crm_members') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.members.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('crm.members.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.crm_members') }}</span>
                    </a>
                </div>

                {{-- 4.3 Kupon & Voucher CRM --}}
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'crm_vouchers'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('crm.vouchers.index') }}"
                        {{ request()->routeIs('crm.vouchers.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.crm_vouchers') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.vouchers.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('crm.vouchers.*') ? 'bg-white/20 text-white' : 'bg-[#FF2D55]/15 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.crm_vouchers') }}</span>
                    </a>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 5: HRM (HUMAN RESOURCE MANAGEMENT)                --}}
        {{-- ======================================================== --}}
        @if ($canAccessHrm || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.hrm') }}
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 5.1 Portal & Presensi Karyawan --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'portal'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('portal') }}" id="tour-nav-portal"
                    {{ request()->routeIs('portal') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.portal_attendance') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('portal') ? 'bg-[#34C759] text-white shadow-[0_2px_8px_rgba(52,199,89,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-[#34C759]/8 dark:hover:bg-[#34C759]/15 hover:text-[#34C759] dark:hover:text-[#30D158]' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('portal') ? 'bg-white/20 text-white' : 'bg-[#34C759]/12 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.portal_attendance') }}</span>
                </a>
            </div>

            {{-- 5.2 Karyawan & Staf SDM --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'hrm'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('hrm.index') }}"
                    {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.payrolls.*')) ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.employees_hrm') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.payrolls.*')) ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.payrolls.*')) ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="users-2" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.employees_hrm') }}</span>
                </a>
            </div>

            {{-- 5.3 Penggajian & Payroll --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'hrm_payroll'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('hrm.payrolls.index') }}"
                    {{ request()->routeIs('hrm.payrolls.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.hrm_payroll') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.payrolls.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('hrm.payrolls.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="badge-percent" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.hrm_payroll') }}</span>
                </a>
            </div>
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 6: SOCIAL MEDIA DAN MARKETING                     --}}
        {{-- ======================================================== --}}
        @if ($canAccessStorefront || $canAccessChannels || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.marketing') }}
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 6.1 Landing Page & Toko Online --}}
            @if ($canAccessStorefront)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'landing_page'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('landing-page.edit') }}"
                        {{ request()->routeIs('landing-page.edit') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.landing_page') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('landing-page.edit') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('landing-page.edit') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.landing_page') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'landing_popup'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('landing-page.popup.edit') }}"
                        {{ request()->routeIs('landing-page.popup.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.popup_promo') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('landing-page.popup.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('landing-page.popup.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.popup_promo') }}</span>
                    </a>
                </div>

                @if ($sidebarShowShipping)
                    <div class="relative group"
                        @mouseenter="if(sidebarCollapsed) activeFlyout = 'shipping_settings'"
                        @mouseleave="activeFlyout = null">
                        <a href="{{ route('storefront.shipping.index') }}"
                            {{ request()->routeIs('storefront.shipping.*') ? 'aria-current="page"' : '' }}
                            :title="sidebarCollapsed ? '{{ __('navigation.shipping_settings') }}' : ''"
                            class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.shipping.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('storefront.shipping.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.shipping_settings') }}</span>
                        </a>
                    </div>
                @endif

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'storefront_settings'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('storefront.settings.index') }}"
                        {{ request()->routeIs('storefront.settings.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.storefront_settings') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('storefront.settings.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('storefront.settings.*') ? 'bg-white/20 text-white' : 'bg-[#8E8E93]/15 text-[#8E8E93] dark:bg-[#98989D]/20 dark:text-[#98989D]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.storefront_settings') }}</span>
                    </a>
                </div>
            @endif

            {{-- 6.2 WhatsApp Business --}}
            @if ($canAccessChannels)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'whatsapp'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('whatsapp.index') }}"
                        {{ request()->routeIs('whatsapp.index') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.whatsapp_business') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.index') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('whatsapp.index') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.whatsapp_business') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'whatsapp_broadcast'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('whatsapp.broadcast.index') }}"
                        {{ request()->routeIs('whatsapp.broadcast.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.whatsapp_broadcast') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('whatsapp.broadcast.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.whatsapp_broadcast') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'whatsapp_logs'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('whatsapp.logs.index') }}"
                        {{ request()->routeIs('whatsapp.logs.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.whatsapp_logs') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.logs.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('whatsapp.logs.*') ? 'bg-white/20 text-white' : 'bg-black/5 text-black/60 dark:bg-white/10 dark:text-white/60' }} flex items-center justify-center shrink-0">
                            <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.whatsapp_logs') }}</span>
                    </a>
                </div>

                {{-- 6.3 Social Media Omnichannel --}}
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'social_media'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('social-media.index') }}"
                        {{ request()->routeIs('social-media.index') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.social_media_omni') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.index') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('social-media.index') ? 'bg-white/20 text-white' : 'bg-[#FF2D55]/15 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.social_media_omni') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'social_posts'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('social-media.posts.index') }}"
                        {{ request()->routeIs('social-media.posts.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.social_posts') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.posts.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('social-media.posts.*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.social_posts') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'social_calendar'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('social-media.calendar') }}"
                        {{ request()->routeIs('social-media.calendar') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.social_calendar') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.calendar') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('social-media.calendar') ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.social_calendar') }}</span>
                    </a>
                </div>

                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'social_inbox'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('social-media.inbox.index') }}"
                        {{ request()->routeIs('social-media.inbox.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.social_inbox') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.inbox.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('social-media.inbox.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="inbox" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.social_inbox') }}</span>
                    </a>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 7: REPORTING                                     --}}
        {{-- ======================================================== --}}
        @if ($canAccessReports || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.reporting') }}
                <span class="sr-only">{{ __('navigation.clusters.reports_finance') }} {{ __('navigation.cluster_finance_reports') }}</span>
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 7.1 Pusat Laporan & Ringkasan --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'reports'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('reports.index') }}" id="tour-nav-reports"
                    {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.reports_center') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('reports.index') && !request()->query('tab') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.reports_center') }}</span>
                </a>
            </div>

            {{-- 7.2 Analitik Bisnis --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'analytics'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('analytics.index') }}" id="tour-nav-analytics"
                    {{ request()->routeIs('analytics.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.business_analytics') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('analytics.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('analytics.*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.business_analytics') }}</span>
                </a>
            </div>

            {{-- 7.3 Laporan Laba Rugi --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'income_statement'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('reports.index', ['tab' => 'income_statement']) }}"
                    {{ request()->fullUrlIs('*tab=income_statement*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.income_statement') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=income_statement*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->fullUrlIs('*tab=income_statement*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.income_statement') }}</span>
                </a>
            </div>

            {{-- 7.4 Laporan Arus Kas --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'cash_flow'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('reports.index', ['tab' => 'cash_flow']) }}"
                    {{ request()->fullUrlIs('*tab=cash_flow*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.cash_flow') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=cash_flow*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->fullUrlIs('*tab=cash_flow*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.cash_flow') }}</span>
                </a>
            </div>

            {{-- 7.5 Laporan Kasir POS --}}
            @if (\App\Support\Context::hasPermission('pos.reports'))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'pos_reports'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('pos.reports.index') }}" id="tour-nav-pos-reports"
                        {{ request()->routeIs('pos.reports.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.pos_reports') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('pos.reports.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('pos.reports.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.pos_reports') }}</span>
                    </a>
                </div>
            @endif

            {{-- 7.6 Laporan Valuasi Stok --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'stock_reports'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('reports.index', ['tab' => 'stock']) }}"
                    {{ request()->fullUrlIs('*tab=stock*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.stock_valuation') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->fullUrlIs('*tab=stock*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->fullUrlIs('*tab=stock*') ? 'bg-white/20 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.stock_valuation') }}</span>
                </a>
            </div>

            {{-- 7.7 Laporan Pajak --}}
            @if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner() || $canAccessFinance)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'tax_reports'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('tax.index') }}" id="tour-nav-tax"
                        {{ request()->routeIs('tax.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.tax_reports') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('tax.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('tax.*') ? 'bg-white/20 text-white' : 'bg-[#FF2D55]/15 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="percent" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.tax_reports') }}</span>
                    </a>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 8: MASTER DATA                                   --}}
        {{-- ======================================================== --}}
        @if ($canAccessInventory || $canAccessPurchasing || $canAccessMasterData || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.master_data') }}
                <span class="sr-only">{{ __('navigation.cluster_master_data') }}</span>
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 8.1 Master Produk & Menu --}}
            @if (\App\Support\Context::hasPermission('products.view'))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'products'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('products.index') }}" id="tour-nav-products"
                        {{ request()->routeIs('products.*') || request()->routeIs('pos.modifiers.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.products_catalog') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ request()->routeIs('products.*') || request()->routeIs('pos.modifiers.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('products.*') || request()->routeIs('pos.modifiers.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="flex items-center justify-between flex-1 min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                            <span class="truncate tracking-tight font-semibold">{{ __('navigation.products_catalog') }}</span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- 8.2 Marketplace Hub --}}
            @if (\App\Support\Context::hasPermission('marketplace.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'marketplace_hub'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('marketplace-hub.index') }}" id="tour-nav-marketplace-hub"
                        {{ request()->routeIs('marketplace-hub.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.marketplace_hub') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('marketplace-hub.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('marketplace-hub.*') ? 'bg-white/20 text-white' : 'bg-[#FF2D55]/15 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight font-medium" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.marketplace_hub') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.3 Bahan Baku & Resep --}}
            @if (\App\Support\Context::hasPermission('materials.view') && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'materials'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('materials.index') }}" id="tour-nav-materials"
                        {{ request()->routeIs('materials.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.materials_recipes') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('materials.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('materials.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.materials_recipes') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.4 Pemasok & Vendor --}}
            @if (\App\Support\Context::hasPermission('master_data.suppliers.view') || \App\Support\Context::hasPermission('purchasing.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'suppliers'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('suppliers.index') }}" id="tour-nav-suppliers"
                        {{ request()->routeIs('suppliers.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.suppliers') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('suppliers.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.25)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('suppliers.*') ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.suppliers') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.5 Kategori Produk --}}
            @if (\App\Support\Context::hasPermission('master_data.product_categories.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'product_categories'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('product-categories.index') }}"
                        {{ request()->routeIs('product-categories.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.product_categories') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('product-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('product-categories.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.product_categories') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.6 Kategori Bahan Baku --}}
            @if ((\App\Support\Context::hasPermission('master_data.material_categories.view') || \App\Support\Context::isOwner()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true))
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'material_categories'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('material-categories.index') }}"
                        {{ request()->routeIs('material-categories.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.material_categories') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('material-categories.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('material-categories.*') ? 'bg-white/20 text-white' : 'bg-[#FF2D55]/15 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="tags" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.material_categories') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.7 Satuan Ukuran --}}
            @if (\App\Support\Context::hasPermission('master_data.units.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'units'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('units.index') }}"
                        {{ request()->routeIs('units.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.measurement_units') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('units.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('units.*') ? 'bg-white/20 text-white' : 'bg-[#30B0C7]/15 text-[#30B0C7] dark:bg-[#40C8E0]/20 dark:text-[#40C8E0]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="scale" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.measurement_units') }}</span>
                    </a>
                </div>
            @endif

            {{-- 8.8 Import Data Excel --}}
            @if (\App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'import'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('import.index') }}" id="tour-nav-import"
                        {{ request()->routeIs('import.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.excel_import') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('import.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('import.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.excel_import') }}</span>
                    </a>
                </div>
            @endif
        </div>
        @endif

        {{-- ======================================================== --}}
        {{-- KLASTER 9: SETTING (PUSAT PENGATURAN TERPADU)             --}}
        {{-- ======================================================== --}}
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
        <div class="space-y-1 pt-1">
            <div x-show="!sidebarCollapsed"
                class="px-2.5 pt-1 pb-1 text-[11px] font-bold tracking-wider uppercase text-black/50 dark:text-white/50 select-none">
                {{ __('navigation.clusters.settings_hub') }}
                <span class="sr-only">{{ __('navigation.cluster_settings_hub') }}</span>
            </div>
            <div x-show="sidebarCollapsed"
                class="sidebar-separator w-8 mx-auto my-1 border-t border-black/5 dark:border-white/10">
            </div>

            {{-- 9.1 Pengaturan Umum Usaha --}}
            @if ($canAccessSettings)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'settings'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('settings.index') }}" id="tour-nav-settings"
                        {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.settings_general') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-semibold transition-all active:scale-[0.98] {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'bg-white/20 text-white' : 'bg-black/5 text-black/70 dark:bg-white/10 dark:text-white/70' }} flex items-center justify-center shrink-0">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight font-semibold" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.settings_general') }}</span>
                    </a>
                </div>
            @endif

            {{-- 9.2 Profil Pengguna --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'profile'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('profile.edit') }}"
                    {{ request()->routeIs('profile.edit') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? '{{ __('navigation.user_profile') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('profile.edit') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('profile.edit') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.user_profile') }}</span>
                </a>
            </div>

            {{-- 9.3 Aturan Otorisasi (MAR) --}}
            @if (\App\Support\Context::hasPermission('approvals.manage') || \App\Support\Context::hasPermission('approval.rules.manage') || \App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'approval_rules'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('approval-rules.index') }}" id="tour-nav-approval-rules"
                        {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.mar_rules') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('approval-rules.*') || request()->routeIs('settings.approval-rules.*') ? 'bg-white/20 text-white' : 'bg-[#34C759]/15 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.mar_rules') }}</span>
                    </a>
                </div>
            @endif

            {{-- 9.4 Log Audit & Deteksi Fraud --}}
            @if ($canAccessAuditLogs)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'audit_logs'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('settings.audit-logs.index') }}" id="tour-nav-audit-logs"
                        {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.audit_anti_fraud') }}' : ''"
                        class="sidebar-item w-full flex items-center justify-between px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-white/20 text-white' : 'bg-[#FF3B30]/15 text-[#FF3B30] dark:bg-[#FF453A]/20 dark:text-[#FF453A]' }} flex items-center justify-center shrink-0">
                                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                            </span>
                            <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.audit_anti_fraud') }}</span>
                        </div>
                        @if (isset($recentHighRiskCount) && $recentHighRiskCount > 0)
                            <span x-show="!sidebarCollapsed" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-white/20 text-white border-white/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30' }} border shrink-0">
                                {{ $recentHighRiskCount }}
                            </span>
                        @endif
                    </a>
                </div>
            @endif

            {{-- 9.5 Peran & Hak Akses Pengguna --}}
            @if ($canAccessRoles)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'roles'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('roles.index') }}"
                        {{ request()->routeIs('roles.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.roles_permissions') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('roles.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('roles.*') ? 'bg-white/20 text-white' : 'bg-[#5856D6]/15 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.roles_permissions') }}</span>
                    </a>
                </div>
            @endif

            {{-- 9.6 Langganan & Kuota Fitur --}}
            @if ($canAccessBilling)
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'billing'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('billing.limits') }}" id="tour-nav-billing"
                        {{ request()->routeIs('billing.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.billing_subscription') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('billing.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('billing.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.billing_subscription') }}</span>
                    </a>
                </div>
            @endif

            {{-- 9.7 Optimasi & Pembersihan Penyimpanan --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'storage_pruning'"
                @mouseleave="activeFlyout = null">
                <button type="button" @click="$dispatch('open-storage-pruning-modal')"
                    :title="sidebarCollapsed ? '{{ __('common.storage_pruning_title') }}' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white cursor-pointer">
                    <span class="w-6 h-6 rounded-[7px] bg-[#007AFF]/12 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="database" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight text-left" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('common.storage_pruning_title') }}</span>
                </button>
            </div>

            {{-- 9.8 Buku Panduan & Dokumentasi Sistem Cooca --}}
            <div class="relative group"
                @mouseenter="if(sidebarCollapsed) activeFlyout = 'docs'"
                @mouseleave="activeFlyout = null">
                <a href="{{ route('docs.index') }}" id="tour-nav-docs"
                    {{ request()->routeIs('docs.*') ? 'aria-current="page"' : '' }}
                    :title="sidebarCollapsed ? 'Panduan & Dokumentasi' : ''"
                    class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('docs.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                    <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('docs.*') ? 'bg-white/20 text-white' : 'bg-[#007AFF]/15 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    </span>
                    <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>Panduan &amp; Dokumentasi</span>
                </a>
            </div>

            {{-- 9.9 Bantuan & Pelaporan Bug --}}
            @if (\App\Support\Context::isOwner())
                <div class="relative group"
                    @mouseenter="if(sidebarCollapsed) activeFlyout = 'support'"
                    @mouseleave="activeFlyout = null">
                    <a href="{{ route('feedback.bugs.index') }}"
                        {{ request()->routeIs('feedback.*') ? 'aria-current="page"' : '' }}
                        :title="sidebarCollapsed ? '{{ __('navigation.help_support') }}' : ''"
                        class="sidebar-item w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] text-[13px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('feedback.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.05] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white' }}">
                        <span class="w-6 h-6 rounded-[7px] {{ request()->routeIs('feedback.*') ? 'bg-white/20 text-white' : 'bg-[#FF9500]/15 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="life-buoy" class="w-3.5 h-3.5"></i>
                        </span>
                        <span class="truncate tracking-tight" x-show="!sidebarCollapsed" x-transition.opacity>{{ __('navigation.help_support') }}</span>
                    </a>
                </div>
            @endif
        </div>
        @endif
    </nav>

    @if ($canAccessBilling)
        {{-- 4. Subscription Plan Card (Apple HIG Special Design Card) --}}
        <div
            class="sidebar-bottom p-2.5 border-t border-black/5 dark:border-white/10 shrink-0 bg-white/75 dark:bg-[#1C1C1E]/75 backdrop-blur-md">
            <!-- Collapsed Sidebar: Compact Squircle Action -->
            <div x-show="sidebarCollapsed" class="sidebar-plan-collapsed flex justify-center py-1">
                @if ($isCorePlan)
                    <a href="{{ route('billing.limits') }}" title="Cooca (∞ Unlimited) - Kelola Kuota"
                        class="w-10 h-10 rounded-[10px] bg-[#34C759]/12 border border-[#34C759]/30 text-[#34C759] dark:text-[#30D158] flex items-center justify-center hover:bg-[#34C759]/20 active:scale-95 transition-all shadow-2xs">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </a>
                @else
                    <a href="{{ route('billing.patungan') }}" title="Paket Free (Solo) - Tingkatkan ke Cooca"
                        class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-[0_2px_8px_rgba(0,122,255,0.3)]">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </a>
                @endif
            </div>

            <!-- Expanded Sidebar: Rich Apple HIG Card -->
            <div x-show="!sidebarCollapsed" class="sidebar-plan-expanded" x-transition.opacity>
                @if ($isCorePlan)
                    {{-- Active Core Plan Card --}}
                    <div
                        class="relative overflow-hidden rounded-[12px] p-3 border border-[#34C759]/25 bg-gradient-to-br from-[#34C759]/10 via-[#30D158]/5 to-transparent dark:from-[#30D158]/15 dark:via-[#30D158]/5 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <div class="flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse shrink-0"></span>
                                <span class="text-[12px] font-semibold text-black dark:text-white truncate">Cooca</span>
                            </div>
                            <span
                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/25 shrink-0">
                                Aktif
                            </span>
                        </div>

                        <div class="space-y-1 text-[11px] text-black/60 dark:text-white/60">
                            <div class="flex items-center justify-between">
                                <span>Akses Fitur:</span>
                                <span class="font-semibold text-[#34C759] dark:text-[#30D158] flex items-center gap-1">
                                    <i data-lucide="infinity" class="w-3 h-3"></i>
                                    <span>∞ Unlimited</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span>{{ __('navigation.ai_engine_label') }} <span class="sr-only">Token AI (Top-up):</span></span>
                                <span class="font-medium text-black/80 dark:text-white/80 flex items-center gap-1">
                                    <i data-lucide="key" class="w-3 h-3 text-purple-500"></i>
                                    <span>BYOAI (API Key)</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 pt-0.5">
                            <a href="{{ route('billing.limits') }}"
                                class="flex-1 h-7 px-2.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/75 dark:text-white/75 text-[11px] font-medium flex items-center justify-center gap-1 transition-all active:scale-[0.97]">
                                <span>{{ __('navigation.manage_quota') }}</span>
                                <i data-lucide="chevron-right" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                            </a>
                            <button type="button" @click="$dispatch('open-storage-pruning-modal')" title="{{ __('common.storage_pruning_title') }}"
                                class="h-7 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white text-[11px] font-medium flex items-center justify-center gap-1 transition-all active:scale-[0.97] cursor-pointer">
                                <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                <span>{{ __('common.storage_pruning_preview_btn') }}</span>
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Free Plan Upgrade Card (macOS Sonoma Frosted Glass + iOS Gradient) --}}
                    <div
                        class="relative overflow-hidden rounded-[12px] p-3 border border-black/5 dark:border-white/10 bg-gradient-to-br from-[#007AFF]/10 via-[#5856D6]/5 to-transparent dark:from-[#007AFF]/15 dark:via-[#5856D6]/10 dark:to-transparent space-y-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
                        {{-- Header Badge & Title --}}
                        <div class="flex items-center justify-between gap-1.5">
                            <span class="text-[12px] font-semibold text-black dark:text-white truncate">{{ __('navigation.plan_free_solo') }}</span>
                            <span
                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] border border-[#FF9500]/25 shrink-0">
                                Free
                            </span>
                        </div>

                        {{-- Usage Meters --}}
                        <div class="space-y-1.5 text-[11px]">
                            {{-- Katalog Produk --}}
                            <div class="space-y-0.5">
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                                    <span>{{ __('navigation.products_quota_label') }}</span>
                                    <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $navUsage['products']['used'] ?? 0 }} / 50
                                    </span>
                                </div>
                                <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#007AFF] rounded-full transition-all duration-300"
                                        style="width: {{ min(100, $navUsage['products']['percent'] ?? 0) }}%"></div>
                                </div>
                            </div>

                            {{-- Resep / BOM --}}
                            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true)
                            <div class="space-y-0.5">
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                                    <span>{{ __('navigation.recipes_quota_label') }}</span>
                                    <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $navUsage['recipes']['used'] ?? 0 }} / 20
                                    </span>
                                </div>
                                <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#5856D6] rounded-full transition-all duration-300"
                                        style="width: {{ min(100, $navUsage['recipes']['percent'] ?? 0) }}%"></div>
                                </div>
                            </div>
                            @endif

                            {{-- Invoice Bulan Ini --}}
                            <div class="space-y-0.5">
                                <div
                                    class="flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                                    <span>{{ __('navigation.invoices_quota_label') }}</span>
                                    <span class="font-medium text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $navUsage['invoices_this_month']['used'] ?? 0 }} / 10
                                    </span>
                                </div>
                                <div class="w-full h-1 bg-black/5 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#AF52DE] rounded-full transition-all duration-300"
                                        style="width: {{ min(100, $navUsage['invoices_this_month']['percent'] ?? 0) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action CTA Button --}}
                        <div class="space-y-1.5 pt-0.5">
                            <a href="{{ route('billing.patungan') }}"
                                class="w-full h-7.5 px-3 rounded-[8px] bg-gradient-to-r from-[#007AFF] to-[#5856D6] hover:opacity-95 text-white text-[11px] font-semibold flex items-center justify-center gap-1.5 shadow-[0_2px_6px_rgba(0,122,255,0.25)] transition-all active:scale-[0.97] cursor-pointer">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ __('billing.action_upgrade') }}</span>
                            </a>
                            <div class="flex items-center justify-between text-[11px] pt-0.5 px-0.5">
                                <a href="{{ route('billing.limits') }}"
                                    class="text-black/55 dark:text-white/55 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition">
                                    {{ __('navigation.quota_details') }}
                                </a>
                                <button type="button" @click="$dispatch('open-storage-pruning-modal')" title="{{ __('common.storage_pruning_title') }}"
                                    class="text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white flex items-center gap-1 transition cursor-pointer">
                                    <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                    <span>{{ __('common.storage_pruning_preview_btn') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</aside>
