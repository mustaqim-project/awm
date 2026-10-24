{{-- Apple HIG (macOS Sonoma & iOS 18) Sidebar Rail Styles --}}
<style>
    /* Calm Apple-style rhythm for the expanded navigation rail. */
    :root {
        --sidebar-row: 2.75rem;
        --sidebar-subrow: 2.5rem;
        --sidebar-icon: 1rem;
        --sidebar-radius: 0.625rem;
        --sidebar-group-gap: 0.75rem;
    }

    .sidebar-nav {
        padding: 1rem 0.75rem !important;
        line-height: 1.25rem;
    }

    .sidebar-nav .sidebar-item {
        min-height: var(--sidebar-row) !important;
        height: var(--sidebar-row);
        padding: 0.5rem 0.75rem !important;
        border-radius: var(--sidebar-radius) !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav>div {
        margin-bottom: var(--sidebar-group-gap);
    }

    .sidebar-nav .sidebar-item>i,
    .sidebar-nav .sidebar-item-inner>i,
    .sidebar-nav .sidebar-item-inner>div>i,
    .sidebar-nav .sidebar-item>svg,
    .sidebar-nav .sidebar-item-inner>svg {
        width: var(--sidebar-icon) !important;
        height: var(--sidebar-icon) !important;
        flex: 0 0 var(--sidebar-icon);
    }

    .sidebar-nav>div>div[x-show="!sidebarCollapsed"] {
        min-height: 1.25rem;
        display: flex;
        align-items: center;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        letter-spacing: 0.06em;
    }

    /* Nested source-list groups: consistent inset, row height, and breathing room. */
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        margin-left: 1rem !important;
        padding: 0.5rem 0.25rem 0.5rem 0.875rem !important;
        border-left-color: var(--border) !important;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a {
        min-height: var(--sidebar-subrow) !important;
        height: var(--sidebar-subrow);
        margin: 0.125rem 0;
        padding: 0.5rem 0.75rem !important;
        border-radius: var(--sidebar-radius) !important;
        gap: 0.625rem !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>i,
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>svg {
        width: 0.875rem !important;
        height: 0.875rem !important;
        flex: 0 0 0.875rem;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        margin-top: 0.625rem;
        margin-bottom: 0.25rem;
        padding: 0.25rem 0.75rem !important;
        min-height: 1.25rem;
        line-height: 1rem;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show])+a {
        margin-top: 0.125rem;
    }

    /* Every actionable navigation row shares one touch target and type scale. */
    .sidebar-nav a:not(.sidebar-item),
    .sidebar-nav button:not(.sidebar-item) {
        min-height: var(--sidebar-row) !important;
        height: var(--sidebar-row);
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        border-radius: var(--sidebar-radius) !important;
        font-size: 0.8125rem !important;
        line-height: 1.25rem !important;
        box-sizing: border-box;
    }

    .sidebar-nav a:not(.sidebar-item)>i,
    .sidebar-nav a:not(.sidebar-item)>svg,
    .sidebar-nav button:not(.sidebar-item)>i,
    .sidebar-nav button:not(.sidebar-item)>svg {
        width: var(--sidebar-icon) !important;
        height: var(--sidebar-icon) !important;
        flex: 0 0 var(--sidebar-icon);
    }

    @media (max-width: 1023px) {
        .app-sidebar {
            width: 288px !important;
            max-width: 86vw !important;
        }

        .sidebar-header {
            height: 4rem !important;
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }

        .sidebar-tenant-wrapper {
            padding: 1rem 0.875rem !important;
        }
    }

    @media (min-width: 1024px) {
        .app-sidebar:not(.is-collapsed) {
            width: 272px !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-header {
            height: 4rem !important;
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-tenant-wrapper {
            padding: 0.875rem 1rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-nav {
            padding: 0.875rem 0.75rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-item {
            min-height: 2.75rem !important;
            padding-top: 0.625rem !important;
            padding-bottom: 0.625rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-nav>div>div[x-show="!sidebarCollapsed"] {
            margin-top: 0.75rem !important;
            margin-bottom: 0.375rem !important;
        }
    }

    @media (min-width: 1024px) {

        /* 1. Collapsed Sidebar Container (Clean 76px Icon Rail) */
        .app-sidebar.is-collapsed {
            width: 76px !important;
        }

        /* 2. Brand Logo Header: Perfectly Centered on X=38px Axis */
        .app-sidebar.is-collapsed .sidebar-header {
            padding-left: 0 !important;
            padding-right: 0 !important;
            justify-content: center !important;
        }

        .app-sidebar.is-collapsed .sidebar-header a {
            justify-content: center !important;
            width: 100% !important;
            gap: 0 !important;
        }

        .app-sidebar.is-collapsed .sidebar-header #tour-active-business {
            margin: 0 auto !important;
        }

        .app-sidebar.is-collapsed .sidebar-brand-text {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* 3. Tenant / Active Business Switcher: Uniform 40x40 Squircle Centered on X=38px */
        .app-sidebar.is-collapsed .sidebar-tenant-wrapper {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            display: flex !important;
            justify-content: center !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-card {
            width: 2.5rem !important;
            /* 40px */
            height: 2.5rem !important;
            /* 40px */
            min-width: 2.5rem !important;
            min-height: 2.5rem !important;
            max-width: 2.5rem !important;
            max-height: 2.5rem !important;
            padding: 0 !important;
            margin: 0 auto !important;
            border-radius: 10px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            border-color: rgba(60, 60, 67, 0.08) !important;
            box-sizing: border-box !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-tenant-card {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-inner {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            height: 100% !important;
            gap: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-icon {
            margin: 0 auto !important;
            border: none !important;
            background: transparent !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-icon img {
            width: 1.5rem !important;
            height: 1.5rem !important;
            border-radius: 6px !important;
            object-fit: contain !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-icon svg,
        .app-sidebar.is-collapsed .sidebar-tenant-icon i {
            width: 1.25rem !important;
            height: 1.25rem !important;
            color: #007AFF !important;
        }

        .app-sidebar.is-collapsed .sidebar-tenant-inner>div:not(.sidebar-tenant-icon),
        .app-sidebar.is-collapsed #tour-switch-business {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* 4. Navigation Container */
        .app-sidebar.is-collapsed .sidebar-nav {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0.625rem !important;
            padding-bottom: 0.625rem !important;
            overflow-x: hidden !important;
        }

        .app-sidebar.is-collapsed .sidebar-nav>div {
            margin-bottom: 0 !important;
        }

        .app-sidebar.is-collapsed .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        /* 5. Navigation Items (Links & Buttons): 40x40 Squircles centered at X=38px */
        .app-sidebar.is-collapsed .sidebar-item {
            width: 2.5rem !important;
            /* 40px */
            height: 2.5rem !important;
            /* 40px */
            min-width: 2.5rem !important;
            min-height: 2.5rem !important;
            max-width: 2.5rem !important;
            max-height: 2.5rem !important;
            padding: 0 !important;
            margin-left: auto !important;
            margin-right: auto !important;
            margin-top: 3px !important;
            margin-bottom: 3px !important;
            border-radius: 10px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 0 !important;
            box-sizing: border-box !important;
            cursor: pointer !important;
            position: relative !important;
        }

        .app-sidebar.is-collapsed .sidebar-item .sidebar-item-inner {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            height: 100% !important;
            min-width: 0 !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Center child div if wrapped (e.g. WhatsApp icon) */
        .app-sidebar.is-collapsed .sidebar-item-inner>div {
            margin: 0 auto !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Primary Icons: strictly 18px block centered on X=38px */
        .app-sidebar.is-collapsed .sidebar-item>svg:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item-inner>svg,
        .app-sidebar.is-collapsed .sidebar-item-inner>i,
        .app-sidebar.is-collapsed .sidebar-item-inner>div>svg,
        .app-sidebar.is-collapsed .sidebar-item .sidebar-icon-box>svg,
        .app-sidebar.is-collapsed .sidebar-item .sidebar-icon-box>i {
            margin: 0 auto !important;
            width: 1.125rem !important;
            /* 18px */
            height: 1.125rem !important;
            /* 18px */
            flex-shrink: 0 !important;
            display: block !important;
        }

        /* Strictly hide all dropdown chevrons in collapsed rail */
        .app-sidebar.is-collapsed [data-lucide="chevron-down"],
        .app-sidebar.is-collapsed .lucide-chevron-down,
        .app-sidebar.is-collapsed svg.lucide-chevron-down,
        .app-sidebar.is-collapsed i[data-lucide="chevron-down"],
        .app-sidebar.is-collapsed .sidebar-chevron {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            position: absolute !important;
            pointer-events: none !important;
            opacity: 0 !important;
        }

        /* Strictly hide all text labels and badges in collapsed items, PRESERVING icon boxes */
        .app-sidebar.is-collapsed .sidebar-item>span:not(.sidebar-icon-box),
        .app-sidebar.is-collapsed .sidebar-item-inner>span:not(.sidebar-icon-box),
        .app-sidebar.is-collapsed .sidebar-item>div:not(.sidebar-item-inner):not(.sidebar-icon-box) {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Direct link icon box normalization in collapsed rail: clean centered glyph */
        .app-sidebar.is-collapsed .sidebar-item .sidebar-icon-box {
            display: flex !important;
            visibility: visible !important;
            width: 1.5rem !important;
            height: 1.5rem !important;
            margin: 0 auto !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Hide section titles and inline submenus in collapsed mode */
        .app-sidebar.is-collapsed .sidebar-nav>div>div[x-show="!sidebarCollapsed"],
        .app-sidebar.is-collapsed .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        /* 6. Collapsed Flyout Popover: Smooth hover bridge (no 8px dead zone gap) */
        .app-sidebar.is-collapsed .sidebar-flyout-wrapper {
            position: fixed !important;
            left: 70px !important;
            padding-left: 12px !important;
            z-index: 100 !important;
        }

        .app-sidebar.is-collapsed .sidebar-flyout-panel {
            width: 15.5rem !important;
            padding: 0.625rem !important;
            border-radius: 0.875rem !important;
            background-color: rgba(255, 255, 255, 0.98) !important;
            border: 1px solid rgba(60, 60, 67, 0.12) !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.14), 0 2px 6px rgba(0, 0, 0, 0.05) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            max-height: 85vh !important;
            overflow-y: auto !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel {
            background-color: rgba(28, 28, 30, 0.98) !important;
            border-color: rgba(255, 255, 255, 0.14) !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45), 0 2px 6px rgba(0, 0, 0, 0.2) !important;
        }

        /* 7. Active Squircle State (macOS Sonoma Selection) */
        .app-sidebar.is-collapsed .sidebar-item[aria-current="page"] {
            border-radius: 10px !important;
            box-shadow: 0 1px 3px rgba(0, 122, 255, 0.35) !important;
        }

        /* 7. Separator Lines: Subtle Hairline centered at X=38px */
        .app-sidebar.is-collapsed .sidebar-separator {
            width: 1.75rem !important;
            /* 28px */
            margin-left: auto !important;
            margin-right: auto !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
            height: 1px !important;
            display: block !important;
            border-top-width: 1px !important;
            border-color: rgba(60, 60, 67, 0.08) !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-separator {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* 8. Bottom Plan Card Area: Strictly Toggle Collapsed vs Expanded */
        .app-sidebar.is-collapsed .sidebar-bottom {
            padding: 0.625rem 0 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .app-sidebar.is-collapsed .sidebar-plan-collapsed {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            margin: 0 auto !important;
        }

        .app-sidebar.is-collapsed .sidebar-plan-collapsed a {
            margin: 0 auto !important;
        }

        .app-sidebar.is-collapsed .sidebar-plan-expanded {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            pointer-events: none !important;
        }

        /* Expanded Sidebar State for Plan Card */
        .app-sidebar:not(.is-collapsed) .sidebar-plan-collapsed {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-plan-expanded {
            display: block !important;
            width: 100% !important;
        }
    }

    /* Final source-list normalization: every expanded navigation row uses one geometry. */
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item) {
        display: flex !important;
        align-items: center !important;
        min-height: 2.75rem !important;
        height: 2.75rem !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.625rem !important;
        font-size: 0.8125rem !important;
        line-height: 1.25rem !important;
        box-sizing: border-box !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>svg,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>svg,
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>i,
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>svg {
        width: 1rem !important;
        height: 1rem !important;
        flex: 0 0 1rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        height: 1.5rem !important;
        min-height: 1.5rem !important;
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.25rem !important;
        font-size: 0.6875rem !important;
        line-height: 1rem !important;
    }

    /* ====== Apple HIG polish & responsive hardening (added) ====== */
    /* 1. Seleksi sidebar ala Apple: tinted translusen, bukan pill solid + shadow */
    .sidebar-nav a[aria-current="page"],
    .sidebar-nav button[aria-current="page"] {
        background-color: rgba(0, 122, 255, 0.12) !important;
        color: #007AFF !important;
        box-shadow: none !important;
    }

    .dark .sidebar-nav a[aria-current="page"],
    .dark .sidebar-nav button[aria-current="page"] {
        background-color: rgba(10, 132, 255, 0.18) !important;
        color: #0A84FF !important;
    }

    .sidebar-nav a[aria-current="page"]>i,
    .sidebar-nav a[aria-current="page"]>svg {
        color: #007AFF !important;
    }

    .dark .sidebar-nav a[aria-current="page"]>i,
    .dark .sidebar-nav a[aria-current="page"]>svg {
        color: #0A84FF !important;
    }

    .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(0, 122, 255, 0.14) !important;
        color: #007AFF !important;
    }

    .dark .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(10, 132, 255, 0.2) !important;
        color: #0A84FF !important;
    }

    /* 2. Focus ring ala Apple untuk navigasi keyboard */
    .app-sidebar a:focus-visible,
    .app-sidebar button:focus-visible {
        outline: 2px solid rgba(0, 122, 255, 0.65) !important;
        outline-offset: 2px !important;
        border-radius: 0.5rem !important;
    }

    .dark .app-sidebar a:focus-visible,
    .dark .app-sidebar button:focus-visible {
        outline-color: rgba(10, 132, 255, 0.85) !important;
    }

    /* 3. Samsung-galaxy & lanskap pendek: sembunyikan kartu paket agar nav tetap terlihat */
    @media (max-height: 640px) {
        .sidebar-bottom {
            display: none !important;
        }
    }

    /* 4. Pastikan konten tenant wrapper tidak meluber di drawer sempit */
    .sidebar-tenant-wrapper {
        min-width: 0 !important;
    }

    .sidebar-tenant-inner {
        min-width: 0 !important;
    }
</style>
