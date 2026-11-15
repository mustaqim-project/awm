{{-- Apple HIG (macOS Sonoma & iOS 18) Sidebar Rail Styles --}}
<style>
    /* Calm Apple-style rhythm for the expanded navigation rail (Harmonized with Admin Panel). */
    :root {
        --sidebar-row: 2.375rem; /* 38px matching admin panel h-[38px] */
        --sidebar-subrow: 2.125rem; /* 34px */
        --sidebar-icon: 1rem; /* 16px matching admin w-4 h-4 */
        --sidebar-icon-box: 1.75rem; /* 28px matching admin w-7 h-7 */
        --sidebar-radius: 0.6875rem; /* 11px matching admin rounded-[11px] */
        --sidebar-group-gap: 0.625rem;
    }

    .sidebar-nav {
        padding: 0.75rem 0.625rem !important;
        line-height: 1.25rem;
    }

    .sidebar-nav .sidebar-item {
        min-height: var(--sidebar-row) !important;
        height: var(--sidebar-row);
        padding: 0.3125rem 0.625rem !important;
        border-radius: var(--sidebar-radius) !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav>div {
        margin-bottom: var(--sidebar-group-gap);
    }

    /* Primary Sidebar Icon Badge (Matching Admin Panel w-7 h-7 rounded-[8px]) */
    .sidebar-icon-box {
        width: 1.75rem !important;
        height: 1.75rem !important;
        min-width: 1.75rem !important;
        min-height: 1.75rem !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        background-color: rgba(0, 122, 255, 0.10) !important;
        color: #007AFF !important;
        transition: all 0.15s ease !important;
        box-sizing: border-box !important;
    }

    .dark .sidebar-icon-box {
        background-color: rgba(0, 122, 255, 0.20) !important;
        color: #0A84FF !important;
    }

    .sidebar-item:hover .sidebar-icon-box {
        background-color: rgba(0, 122, 255, 0.15) !important;
        transform: scale(1.05);
    }

    .sidebar-icon-box>i,
    .sidebar-icon-box>svg {
        width: 1rem !important; /* 16px */
        height: 1rem !important;
        min-width: 1rem !important;
        min-height: 1rem !important;
        flex-shrink: 0 !important;
        color: #007AFF !important;
        stroke-width: 2 !important;
    }

    .dark .sidebar-icon-box>i,
    .dark .sidebar-icon-box>svg {
        color: #0A84FF !important;
    }

    .sidebar-nav .sidebar-item>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]),
    .sidebar-nav .sidebar-item-inner>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]),
    .sidebar-nav .sidebar-item-inner>div>i,
    .sidebar-nav .sidebar-item>svg:not(.lucide-chevron-down),
    .sidebar-nav .sidebar-item-inner>svg:not(.lucide-chevron-down) {
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
        letter-spacing: 0.05em;
    }

    /* Nested source-list groups: consistent inset, row height, and breathing room. */
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        margin-left: 1rem !important;
        padding: 0.375rem 0.25rem 0.375rem 0.875rem !important;
        border-left-color: var(--border) !important;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a {
        min-height: var(--sidebar-subrow) !important;
        height: var(--sidebar-subrow);
        margin: 0.125rem 0;
        padding: 0.375rem 0.625rem !important;
        border-radius: 0.5625rem !important;
        gap: 0.625rem !important;
        line-height: 1.25rem;
        box-sizing: border-box;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>i,
    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>a>svg {
        width: 1rem !important; /* 16px crisp submenu icons */
        height: 1rem !important;
        flex: 0 0 1rem;
    }

    .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        margin-top: 0.5rem;
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
        padding-top: 0.3125rem !important;
        padding-bottom: 0.3125rem !important;
        padding-left: 0.625rem !important;
        padding-right: 0.625rem !important;
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
            padding: 0.75rem 0.625rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-item {
            min-height: 2.375rem !important;
            height: 2.375rem !important;
            padding-top: 0.3125rem !important;
            padding-bottom: 0.3125rem !important;
            padding-left: 0.625rem !important;
            padding-right: 0.625rem !important;
            border-radius: 0.6875rem !important;
        }

        .app-sidebar:not(.is-collapsed) .sidebar-nav>div>div[x-show="!sidebarCollapsed"] {
            margin-top: 0.625rem !important;
            margin-bottom: 0.25rem !important;
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
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) {
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

        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) .sidebar-item-inner {
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
        .app-sidebar.is-collapsed .sidebar-item-inner:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>div {
            margin: 0 auto !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Primary Icons: strictly 18px block centered on X=38px */
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>svg:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-chevron),
        .app-sidebar.is-collapsed .sidebar-item-inner:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>svg,
        .app-sidebar.is-collapsed .sidebar-item-inner:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>i,
        .app-sidebar.is-collapsed .sidebar-item-inner:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>div>svg,
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) .sidebar-icon-box>svg,
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) .sidebar-icon-box>i {
            margin: 0 auto !important;
            width: 1.125rem !important;
            /* 18px */
            height: 1.125rem !important;
            /* 18px */
            flex-shrink: 0 !important;
            display: block !important;
        }

        /* Strictly hide all dropdown chevrons in collapsed rail */
        .app-sidebar.is-collapsed [data-lucide="chevron-down"]:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *),
        .app-sidebar.is-collapsed .lucide-chevron-down:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *),
        .app-sidebar.is-collapsed svg.lucide-chevron-down:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *),
        .app-sidebar.is-collapsed i[data-lucide="chevron-down"]:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *),
        .app-sidebar.is-collapsed .sidebar-chevron:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) {
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

        /* Strictly hide all text labels and badges in collapsed RAIL items, PRESERVING icon boxes and EXCLUDING flyout panel */
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>span:not(.sidebar-icon-box),
        .app-sidebar.is-collapsed .sidebar-item-inner:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>span:not(.sidebar-icon-box),
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)>div:not(.sidebar-item-inner):not(.sidebar-icon-box) {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Direct link icon box normalization in collapsed rail: clean centered glyph */
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *) .sidebar-icon-box {
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
            width: 16rem !important;
            min-width: 16rem !important;
            padding: 0.5rem !important;
            border-radius: 0.875rem !important;
            background-color: rgba(255, 255, 255, 0.98) !important;
            border: 1px solid rgba(60, 60, 67, 0.12) !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16), 0 2px 8px rgba(0, 0, 0, 0.06) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            max-height: 85vh !important;
            overflow-y: auto !important;
            overscroll-behavior: contain !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel {
            background-color: rgba(28, 28, 30, 0.98) !important;
            border-color: rgba(255, 255, 255, 0.14) !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.55), 0 2px 8px rgba(0, 0, 0, 0.25) !important;
        }

        /* Flyout Header Title (Category / Group Name) */
        .app-sidebar.is-collapsed .sidebar-flyout-panel > div:first-child {
            display: block !important;
            visibility: visible !important;
            padding: 0.375rem 0.625rem 0.5rem 0.625rem !important;
            font-size: 0.8125rem !important; /* 13px matching admin text-[13px] */
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
            color: rgba(0, 0, 0, 0.85) !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
            margin-bottom: 0.375rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel > div:first-child {
            color: rgba(255, 255, 255, 0.9) !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Flyout Action Items (Horizontal link rows with icon + label) */
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item,
        .app-sidebar.is-collapsed .sidebar-flyout-panel a,
        .app-sidebar.is-collapsed .sidebar-flyout-panel button {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            min-height: 2.125rem !important; /* 34px */
            max-height: none !important;
            padding: 0.4rem 0.625rem !important;
            margin: 2px 0 !important;
            border-radius: 8px !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: flex-start !important;
            align-items: center !important;
            gap: 0.625rem !important; /* 10px spacing between icon and text */
            box-sizing: border-box !important;
            text-decoration: none !important;
            color: rgba(0, 0, 0, 0.75) !important;
            background-color: transparent !important;
            box-shadow: none !important;
            cursor: pointer !important;
            position: relative !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item,
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel a,
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel button {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        /* Hover Feedback */
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item:hover,
        .app-sidebar.is-collapsed .sidebar-flyout-panel a:hover,
        .app-sidebar.is-collapsed .sidebar-flyout-panel button:hover {
            color: #000000 !important;
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item:hover,
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel a:hover,
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel button:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Active / Current Route State */
        .app-sidebar.is-collapsed .sidebar-flyout-panel a[aria-current="page"],
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item[aria-current="page"],
        .app-sidebar.is-collapsed .sidebar-flyout-panel a.font-semibold,
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item.font-semibold {
            background-color: rgba(0, 122, 255, 0.1) !important;
            color: #007AFF !important;
            font-weight: 600 !important;
        }

        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel a[aria-current="page"],
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item[aria-current="page"],
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel a.font-semibold,
        .dark .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item.font-semibold {
            background-color: rgba(10, 132, 255, 0.16) !important;
            color: #0A84FF !important;
            font-weight: 600 !important;
        }

        /* Flyout Icons: strictly inline 15px icon */
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item > i,
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item > svg,
        .app-sidebar.is-collapsed .sidebar-flyout-panel a > i,
        .app-sidebar.is-collapsed .sidebar-flyout-panel a > svg {
            width: 0.9375rem !important; /* 15px */
            height: 0.9375rem !important;
            min-width: 0.9375rem !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
            color: inherit !important;
        }

        /* Flyout Text Labels: FULLY VISIBLE, CLEAN TYPOGRAPHY */
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-item > span,
        .app-sidebar.is-collapsed .sidebar-flyout-panel a > span,
        .app-sidebar.is-collapsed .sidebar-flyout-panel button > span,
        .app-sidebar.is-collapsed .sidebar-flyout-panel span:not(.sidebar-icon-box) {
            display: block !important;
            visibility: visible !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            opacity: 1 !important;
            font-size: 0.8125rem !important; /* 13px */
            line-height: 1.25rem !important;
            text-align: left !important;
            flex: 1 1 auto !important;
            color: inherit !important;
        }

        /* Flyout Tooltip Pill (Single Item Direct Links) */
        .app-sidebar.is-collapsed .sidebar-flyout-panel.sidebar-flyout-tooltip {
            width: auto !important;
            min-width: auto !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 0.8125rem !important; /* 13px matching admin */
            font-weight: 600 !important;
            border-radius: 0.625rem !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18) !important;
        }

        /* Flyout Icon Box normalization */
        .app-sidebar.is-collapsed .sidebar-flyout-panel .sidebar-icon-box {
            display: flex !important;
            visibility: visible !important;
            width: 1.25rem !important;
            height: 1.25rem !important;
            min-width: 1.25rem !important;
            margin: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* 7. Active Squircle State (macOS Sonoma Selection) */
        .app-sidebar.is-collapsed .sidebar-item:not(.sidebar-flyout-panel *):not(.sidebar-flyout-wrapper *)[aria-current="page"] {
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

    /* Final source-list normalization: every expanded navigation row matches Admin Panel geometry. */
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item,
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item) {
        display: flex !important;
        align-items: center !important;
        min-height: 2.375rem !important; /* 38px matching admin h-[38px] */
        height: 2.375rem !important;
        padding: 0.3125rem 0.625rem !important; /* 5px 10px matching admin px-2.5 */
        border-radius: 0.6875rem !important; /* 11px matching admin rounded-[11px] */
        font-size: 0.8125rem !important; /* 13px matching admin text-[13px] */
        line-height: 1.25rem !important;
        letter-spacing: -0.01em !important;
        box-sizing: border-box !important;
        transition: all 0.15s ease !important;
    }

    /* Inactive text high-contrast Apple typography */
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item:not([aria-current="page"]):not(.bg-\[\#007AFF\]),
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item):not([aria-current="page"]):not(.bg-\[\#007AFF\]),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item):not([aria-current="page"]):not(.bg-\[\#007AFF\]) {
        color: rgba(28, 28, 30, 0.85) !important;
        font-weight: 500 !important;
    }

    .dark .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item:not([aria-current="page"]):not(.bg-\[\#007AFF\]),
    .dark .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item):not([aria-current="page"]):not(.bg-\[\#007AFF\]),
    .dark .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item):not([aria-current="page"]):not(.bg-\[\#007AFF\]) {
        color: rgba(242, 242, 247, 0.85) !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item:not([aria-current="page"]):not(.bg-\[\#007AFF\]):hover {
        background-color: rgba(0, 0, 0, 0.04) !important;
        color: #000000 !important;
    }

    .dark .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item:not([aria-current="page"]):not(.bg-\[\#007AFF\]):hover {
        background-color: rgba(255, 255, 255, 0.06) !important;
        color: #FFFFFF !important;
    }

    /* Icon Sizing in Expanded Rail */
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-icon-box *),
    .app-sidebar:not(.is-collapsed) .sidebar-nav .sidebar-item>svg:not(.lucide-chevron-down):not(.sidebar-icon-box *),
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>i:not(.sidebar-icon-box *),
    .app-sidebar:not(.is-collapsed) .sidebar-nav a:not(.sidebar-item)>svg:not(.sidebar-icon-box *),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>i:not(.lucide-chevron-down):not([data-lucide="chevron-down"]):not(.sidebar-icon-box *),
    .app-sidebar:not(.is-collapsed) .sidebar-nav button:not(.sidebar-item)>svg:not(.lucide-chevron-down):not(.sidebar-icon-box *) {
        width: 1rem !important; /* 16px */
        height: 1rem !important;
        flex: 0 0 1rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"] {
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
    }

    .app-sidebar:not(.is-collapsed) .sidebar-nav div[x-show*="Open && !sidebarCollapsed"]>div:not([x-show]) {
        height: 1.25rem !important;
        min-height: 1.25rem !important;
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        margin-top: 0.375rem !important;
        margin-bottom: 0.25rem !important;
        font-size: 0.65625rem !important; /* 10.5px matching admin text-[10.5px] */
        line-height: 1rem !important;
    }

    /* ====== Apple HIG Active State: Matches Admin Panel Solid System Blue ====== */
    .sidebar-nav a[aria-current="page"],
    .sidebar-nav button[aria-current="page"],
    .sidebar-nav a.bg-\[\#007AFF\],
    .sidebar-nav button.bg-\[\#007AFF\] {
        background-color: #007AFF !important;
        color: #FFFFFF !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 3px rgba(0, 122, 255, 0.25) !important;
    }

    .dark .sidebar-nav a[aria-current="page"],
    .dark .sidebar-nav button[aria-current="page"],
    .dark .sidebar-nav a.bg-\[\#007AFF\],
    .dark .sidebar-nav button.bg-\[\#007AFF\] {
        background-color: #007AFF !important;
        color: #FFFFFF !important;
        box-shadow: 0 1px 3px rgba(0, 122, 255, 0.35) !important;
    }

    .sidebar-nav a[aria-current="page"] .sidebar-icon-box,
    .sidebar-nav button[aria-current="page"] .sidebar-icon-box,
    .sidebar-nav a.bg-\[\#007AFF\] .sidebar-icon-box,
    .sidebar-nav button.bg-\[\#007AFF\] .sidebar-icon-box {
        background-color: rgba(255, 255, 255, 0.20) !important;
        color: #FFFFFF !important;
    }

    .sidebar-nav a[aria-current="page"] .sidebar-icon-box>i,
    .sidebar-nav a[aria-current="page"] .sidebar-icon-box>svg,
    .sidebar-nav button[aria-current="page"] .sidebar-icon-box>i,
    .sidebar-nav button[aria-current="page"] .sidebar-icon-box>svg,
    .sidebar-nav a.bg-\[\#007AFF\] .sidebar-icon-box>i,
    .sidebar-nav a.bg-\[\#007AFF\] .sidebar-icon-box>svg,
    .sidebar-nav button.bg-\[\#007AFF\] .sidebar-icon-box>i,
    .sidebar-nav button.bg-\[\#007AFF\] .sidebar-icon-box>svg,
    .sidebar-nav a[aria-current="page"]>i,
    .sidebar-nav a[aria-current="page"]>svg {
        color: #FFFFFF !important;
    }

    .sidebar-nav a[aria-current="page"] span,
    .sidebar-nav button[aria-current="page"] span {
        color: #FFFFFF !important;
    }

    .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(255, 255, 255, 0.2) !important;
        color: #FFFFFF !important;
    }

    .dark .sidebar-nav a[aria-current="page"]>span[class*="ml-auto"] {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #FFFFFF !important;
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

    /* 5. Secondary Sidebar (macOS Sonoma Source List Polish) */
    #secondary-sidebar nav a[aria-current="page"] {
        background-color: #007AFF !important;
        color: #FFFFFF !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 8px rgba(0, 122, 255, 0.28) !important;
    }

    #secondary-sidebar nav a[aria-current="page"] i,
    #secondary-sidebar nav a[aria-current="page"] svg {
        color: #FFFFFF !important;
    }

    #secondary-sidebar a:focus-visible,
    #secondary-sidebar button:focus-visible {
        outline: 2px solid rgba(0, 122, 255, 0.65) !important;
        outline-offset: 2px !important;
        border-radius: 0.5rem !important;
    }

    .dark #secondary-sidebar a:focus-visible,
    .dark #secondary-sidebar button:focus-visible {
        outline-color: rgba(10, 132, 255, 0.85) !important;
    }

    /* 6. Suppress Primary Sidebar Flyout when Secondary Sidebar is Visible on Desktop */
    @media (min-width: 1024px) {
        body:has(#secondary-sidebar:not([style*="display: none"])) .app-sidebar.is-collapsed .sidebar-flyout-wrapper {
            display: none !important;
            pointer-events: none !important;
        }
    }
</style>
