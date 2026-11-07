# UI & View Survey Report: Community, CRM, and Customers Modules

**Repository**: `cooca_core`  
**Date**: 2026-11-07  
**Scope**: Presentation layer (Blade views) in `resources/views/app/community/`, `resources/views/app/crm/`, and `resources/views/app/customers/`  
**Target Viewports**: Mobile (375px – 430px) and Desktop (1280px – 1440px)  
**Referenced Directives**: `cooca-agent-directive`, `/ui-simplify-layout`, `/responsive-ui-ux`, `/ui-layout-hierarchy-reorganizer`, `/laravel-rbac`, `/laravel-module-audit`

---

## Executive Summary

A comprehensive investigation was conducted across all Blade views within the **Community**, **CRM**, and **Customers** modules. Exactly **6 Blade template files** (comprising 4,840 total lines of code) represent the presentation layer for these modules. All 6 files pass PHP syntax validation (`php -l`) with 0 syntax errors.

While the views follow modern Tailwind CSS styling with Apple HIG aesthetics (backdrop blur, rounded cards, modal sheets, and semantic color palettes), the survey identified **6 critical categories of presentation layer issues**:
1. **Redundant & Inconsistent Header Hierarchies**: Double `<h1>` occurrences in `community/events.blade.php`, title duplications in `crm/vouchers.blade.php`, and a systemic architectural mismatch between `@extends('layouts.app', ['headerTitle' => ...])` and `<x-module-header>`.
2. **Action Proliferation & Button Redundancy**: In `crm/vouchers.blade.php`, **three identical "Buat Voucher" buttons** exist on a single screen within 100 vertical pixels.
3. **Mobile Vertical Bloat (Bento KPI Stacking)**: In `crm/members.blade.php` and `crm/vouchers.blade.php`, 4 separate KPI tiles stack vertically on mobile (375px), forcing the user to scroll over 550px–600px before reaching filters or data. In `community/index.blade.php`, the entire left sidebar (profile, nav rail, storage quota) renders above the feed on mobile, pushing actual community content ~350px down.
4. **Sub-44px Touch Targets & Action Cramping**: Several interactive buttons (carousel scroll buttons, post action buttons, table action buttons, and modal secondary controls) measure 32px–36px, failing Apple HIG minimum 44×44px tap target guidelines.
5. **View-Level RBAC Gaps**: In `customers/index.blade.php`, the Edit Customer modal template is rendered unconditionally without `@if (\App\Support\Context::hasPermission('customers.edit'))`, and the Detail Modal contains an unguarded "Edit" button that allows read-only users to trigger the edit dialog. In `community/index.blade.php`, post deletion is hardcoded only to the original author, preventing community moderators or owners from removing inappropriate content.
6. **Cross-Module Terminology / Translation Leakage**: In `customers/index.blade.php`, 5 translation keys were copied verbatim from the `purchasing` domain (`purchasing.terjadi_kesalahan...`, `purchasing.unduh_template_xlsx`), violating modular isolation.

---

## 1. Inventory of Surveyed Blade Views

| # | Module | File Path | Lines | Bytes | Template Nature | PHP Syntax (`php -l`) |
|---|---|---|---|---|---|---|
| 1 | **Community** | `resources/views/app/community/events.blade.php` | 638 | 45,261 | Extends `layouts.app` | No syntax errors detected |
| 2 | **Community** | `resources/views/app/community/event_payment.blade.php` | 295 | 16,879 | Extends `layouts.app` | No syntax errors detected |
| 3 | **Community** | `resources/views/app/community/index.blade.php` | 1,277 | 87,752 | Extends `layouts.app` | No syntax errors detected |
| 4 | **CRM** | `resources/views/app/crm/members.blade.php` | 763 | 57,060 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |
| 5 | **CRM** | `resources/views/app/crm/vouchers.blade.php` | 365 | 27,141 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |
| 6 | **Customers** | `resources/views/app/customers/index.blade.php` | 1,502 | 117,347 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |
| **TOTAL** | **3 Modules** | **6 Files** | **4,840** | **351,440** | - | **100% Valid Syntax** |

---

## 2. In-Depth Module-by-Module Survey Findings

---

### 2.1 Module: Community

#### View A: `resources/views/app/community/events.blade.php` (Agenda & Event Komunitas)

1. **Header Structure & Double Header Issue**:
   - Lines 1–5:
     ```blade
     @extends('layouts.app', [
         'title' => __('community.agenda_event_komunitas_cooca'),
         'headerTitle' => 'Agenda Event Komunitas',
         'headerSubtitle' => 'Seminar bisnis, pelatihan kasir POS, dan meetup eksklusif untuk seluruh pengguna Cooca',
     ])
     ```
     Notice that `'headerTitle'` is a hardcoded Indonesian string rather than localized via `__('community.agenda_event_title')`.
   - In `resources/views/layouts/partials/topbar.blade.php:205-211`, the global application topbar displays `$headerTitle` as an `<h1>` and `$headerSubtitle` as `<p>`.
   - Inside the view body (lines 141–163), an internal "Header Banner" card is rendered:
     ```blade
     <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl ...">
         <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
             Agenda &amp; Pelatihan Komunitas
         </h1>
         <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
             Tingkatkan wawasan bisnis, strategi penjualan POS, dan perluas relasi dengan sesama pelaku usaha...
         </p>
     </div>
     ```
   - **Finding**: Direct **Double Header** conflict. The user sees "Agenda Event Komunitas" in the sticky macOS topbar, followed immediately by another massive card containing `<h1>Agenda & Pelatihan Komunitas</h1>` and duplicate explanatory text.
   - Breadcrumb navigation (lines 124–138) is manually written using raw HTML `nav` and `i data-lucide="chevron-right"` instead of the canonical `<x-breadcrumb>` component.

2. **Mobile Responsiveness (375px vs 1280px)**:
   - Header banner action button (line 156–162): Uses `flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4`. On mobile 375px, the "Feed Komunitas" link expands to full width (`flex-1 sm:flex-none`), creating good touch surface.
   - Segmented navigation tabs (lines 166–182): Two buttons ("Event Mendatang" and "Tiket & Pendaftaran Saya") placed in a flex row. On 375px mobile, text with counters can squeeze:
     ```blade
     <button class="px-4 py-2 rounded-xl text-xs ...">
     ```
     Touch height is ~36px (under 44px).
   - Event Cards Grid (lines 195–313): `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`. Collapses cleanly to single-column on mobile.
   - In "Tiket & Pendaftaran Saya" card action row (lines 386–407):
     ```blade
     <div class="flex items-center gap-2 pt-2 border-t ...">
         <button ... class="flex-1 py-2 rounded-xl text-xs font-bold ...">
             Buka Tiket Digital
         </button>
         <form ...>
             <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 ...">
                 Batal
             </button>
         </form>
     </div>
     ```
     The destructive "Batal" action sits immediately adjacent to the primary "Buka Tiket Digital" button without clear separation or confirmation badge, risking accidental cancel tap on touchscreens.

3. **Action Button Hierarchy & Tap Target Analysis**:
   - Event card footer: "Daftar Sekarang (Gratis)" / "Beli Tiket" uses `w-full py-2.5 rounded-xl text-xs font-bold` (~38px height). Primary action is distinctively colored (`bg-[#007AFF]` for free vs `bg-amber-600` for paid).
   - Modal close button (lines 458 & 632) implements `min-w-[44px] min-h-[44px]` (meets Apple HIG).
   - Modal internal inputs: Venue QR input (line 614) uses `h-9` (36px height), below the 44px recommendation.

4. **Empty State Quality**:
   - Lines 186–194: Upcoming events empty state with `calendar-x` icon and localized explanation (`__('community.belum_ada_event_mendatang')`).
   - Lines 325–333: My registrations empty state with `ticket` icon and clear call to action to check the upcoming tab.
   - **Quality**: High, informative, and visually aligned.

5. **Code Duplication with `community/index.blade.php`**:
   - Lines 19–121 (Alpine.js ticket & check-in logic) and lines 416–636 (Digital Ticket Modal) are **100% duplicate** of lines 66–168 and 895–1080 in `community/index.blade.php`. This ~220-line block should be abstracted into a reusable Blade component `<x-community.ticket-modal />` to avoid maintenance drift.

---

#### View B: `resources/views/app/community/event_payment.blade.php` (Pembayaran QRIS Tiket Event)

1. **Header Structure & Hierarchy**:
   - Lines 1–5: Passes `'headerTitle' => __('community.ticket_payment_event')` and `'headerSubtitle' => __('community.ticket_payment_subtitle')` to `layouts.app`.
   - Content area starts with clean top back navigation (lines 91–103) with a status badge ("Menunggu Pembayaran QRIS" / "Pembayaran Lunas").
   - **Finding**: **Zero Double Header**. Does not render a redundant `<h1>` inside the content canvas.

2. **Mobile Responsiveness (375px vs 1280px)**:
   - Bento layout uses `grid grid-cols-1 lg:grid-cols-12 gap-6`.
   - Left column (7 cols) displays QRIS container with countdown timer; Right column (5 cols) displays event details and price breakdown.
   - On mobile 375px:
     - Left column cleanly stacks over right column.
     - QRIS card container is constrained to `max-w-[320px] w-full mx-auto` with a 256×256px QR image/SVG (`w-64 h-64`). This comfortably fits within 375px screen width (375 - 2×12px outer padding = 351px available width).
     - Countdown timer (`x-text="countdownFormatted"`) is prominently placed at top right of the QRIS box.

3. **Action Button Hierarchy & Touch Targets**:
   - Primary action "Cek Status Pembayaran" (line 182–186): `w-full sm:flex-1 py-3 rounded-xl text-xs font-bold text-white bg-[#007AFF]`. Height is ~44px, compliant with Apple HIG.
   - Sandbox simulate button (line 189–198): Only displayed in `local` or `testing` environments, cleanly styled as secondary button (`bg-emerald-50 text-emerald-700`).
   - Back button (line 92–96): Has small hit area (`text-xs font-semibold`). Recommend adding `min-h-[44px] flex items-center` for touchscreen ease.

4. **Empty State & Error Handling**:
   - Real-time polling occurs every 6 seconds. If payment expires or fails, localized feedback messages display gracefully in an Alpine.js message banner (`feedbackMsg`).

---

#### View C: `resources/views/app/community/index.blade.php` (Feed Diskusi Komunitas & Event Slider)

1. **Header Structure & Layout Architecture**:
   - Lines 1–5: Passes `'headerTitle' => __('community.header_title')` and `'headerSubtitle' => __('community.header_subtitle')` to `layouts.app`.
   - Content body does not render a redundant `<h1>`.
   - Architecture: 3-column asymmetric layout (inspired by X/Twitter / Threads):
     - Left Sidebar (`aside lg:col-span-3`): User Profile card, Nav Rail, "Tulis Ide Bisnis" button, Storage Quota widget.
     - Center Column (`main lg:col-span-6`): Upcoming Events Slider, Active filter banner, Activity notifications / Post Composer & Thread feed.
     - Right Sidebar (`aside lg:col-span-3`): Community Search Bar, Top 5 Trending 24h topics, Nearest Event widget, Copyright footer.

2. **Mobile Responsiveness (375px vs 1280px)**:
   - **Critical Mobile Flaw (Sidebar Dom Order Stacking)**:
     - In the DOM, Left Sidebar (`aside lg:col-span-3`) precedes the main feed (`main lg:col-span-6`).
     - On desktop (1280px), `grid grid-cols-12` places them side-by-side.
     - On mobile (375px–430px), the layout collapses to 1 column. Because the Left Sidebar is first in the DOM, mobile users see the User Profile Card, the Nav Rail (4 items), the "Tulis Ide Bisnis" button, and the Storage Quota card **before ever seeing the first post or event**! This pushes actual feed content >350px below the fold.
     - **Recommendation**: On mobile, use flex order or responsive hiding: `order-2 lg:order-1` or collapse the sidebar into a compact horizontal segment / mobile drawer so the feed takes visual precedence.
   - **Horizontal Event Slider (lines 323–421)**:
     - Implements `flex gap-3.5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-1 pt-1 -mx-1 px-1` with item width `w-[280px] sm:w-[310px]`.
     - This horizontal snap scroll pattern prevents vertical bloat and provides an engaging browsing experience on 375px mobile.
   - **Composer Toolbar (lines 559–596)**:
     - Contains image upload icon, quick hashtag pills (`#TipsBisnis`, `#ManajemenPOS`, `#ArusKas`), char counter, and "Posting" button.
     - On 375px mobile, `#ManajemenPOS` and `#ArusKas` are correctly hidden via `hidden sm:inline-block`, preventing toolbar overflow.

3. **Action Button Hierarchy & Touch Targets**:
   - Quick Action "Tulis Ide Bisnis" (`py-2.5 rounded-xl text-xs font-bold bg-[#007AFF]`) is distinct.
   - Post action bar (Reply, Repost, Like, Share) in lines 674–714:
     - Each icon button uses `p-1.5 rounded-full`.
     - The physical bounding box is approximately 32×32px. On mobile touchscreens, users with larger thumbs may mis-tap between "Reply" and "Repost" or "Like".
     - **Recommendation**: Increase padding or specify `min-w-[40px] min-h-[40px] flex items-center justify-center` to prevent mis-clicks.

4. **Empty States**:
   - Upcoming events in slider (lines 316–321): Clean dashed container with `calendar-x`.
   - Notifications tab (lines 477–483): Icon `bell-off` and clear text.
   - Feed listings (lines 775–785): Friendly empty state with `messages-square` tailored for both `my_posts` and general feed.

5. **RBAC & Authorization Audit**:
   - Post deletion (line 644):
     ```blade
     @if ((string) $post->owner_id === (string) auth()->id())
         <form method="POST" action="{{ route('community.destroy', $post) }}" ...>
     ```
     This check strictly limits deletion to the post author. An administrator or business owner with `community.manage` or `community.delete` permission cannot moderate or remove offensive posts from other users in their business feed!
     - **Recommendation**: Update guard to:
       ```blade
       @if ((string) $post->owner_id === (string) auth()->id() || \App\Support\Context::hasPermission('community.manage'))
       ```

---

### 2.2 Module: CRM

#### View A: `resources/views/app/crm/members.blade.php` (Direktori Member & Loyalitas CRM)

1. **Header Structure & Double Header Issue**:
   - Lines 1–3:
     ```blade
     @extends('layouts.app', [
         'title' => __('crm.members_title') . ' - Cooca',
     ])
     ```
     Notice that `'headerTitle'` and `'headerSubtitle'` are **omitted**. As a result, the application topbar falls back to default `'Dashboard'`.
   - Lines 92–95: Inside the content canvas, the view renders:
     ```blade
     <x-module-header
         module="crm"
         title="{{ __('crm.title') }}"
         subtitle="{{ __('crm.subtitle') }}" />
     <x-module-tabs module="crm" />
     ```
   - Then, at line 227 (inside the table container card):
     ```blade
     <h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('crm.title') }}</h2>
     ```
   - **Finding**: Inconsistent topbar behavior across modules. While `community` sets `headerTitle` in `@extends`, `crm` does not set it, causing the topbar to show "Dashboard" while `<x-module-header>` shows "CRM & Loyalty Membership". Furthermore, `{{ __('crm.title') }}` is rendered twice inside the content area.

2. **Mobile Responsiveness (375px vs 1280px)**:
   - **Bento Hero KPI Metrics Bloat (lines 102–172)**:
     - Uses `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`.
     - 4 KPI cards: Total Members, Poin Beredar, Piutang Kasbon, and Quick Action Tray.
     - On mobile (375px), these 4 cards stack vertically, consuming **over 620px of vertical viewport height** before the user can see search filters or member records!
     - **Recommendation**: Convert mobile layout to a 2×2 grid (`grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4`) or a horizontal swipe snap slider (`flex gap-3 overflow-x-auto snap-x snap-mandatory sm:grid sm:grid-cols-2 lg:grid-cols-4`).
   - **Filter Bar Cramping on Mobile (lines 177–219)**:
     - Search input (`flex-1 w-full`) stacks above `flex items-center gap-2 w-full lg:w-auto` containing Tier Select, Segment Select, Filter Button, and Reset Link.
     - On 375px mobile, having two `<select>` elements plus two buttons in a single unconstrained flex row causes select labels to truncate or buttons to shrink.
     - **Recommendation**: On mobile, split filter into 2 rows: search input on top, 2 selects in a 2-column grid (`grid grid-cols-2 gap-2`), with Filter and Reset full width below.
   - **Mobile Customer Card Layout (lines 385–523)**:
     - In the mobile card view, each member contains a 3-column mini bento grid (lines 452–482):
       ```blade
       <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">
       ```
       On 375px screen, this stacks 3 separate mini boxes (Poin Belanja, Total Belanja, Kasbon) vertically inside **every single customer card**! If there are 15 customers, that creates 45 stacked mini-boxes, bloating page height drastically.
     - **Recommendation**: Change to `grid-cols-3 gap-1.5` on mobile so the 3 metrics sit side-by-side in a single row.

3. **Action Button Hierarchy & Touch Targets**:
   - Quick Action Tray (lines 152–171): "Tambah Pelanggan" (`h-10`, primary `#007AFF`) and "Kelola Voucher" (`h-9`, neutral). Clear hierarchy.
   - Mobile card action row (lines 485–520):
     - "Pelunasan": `h-11 px-3 bg-[#34C759]` (Green, 44px).
     - "Riwayat Poin": `h-11 px-3 bg-[#FF9500]/10` (Orange, 44px).
     - "Profil": `h-11 px-3.5 bg-black/[0.05]` (Neutral, 44px).
     - WhatsApp: `h-11 w-11 bg-[#34C759]/10` (Green square, 44px).
     - **Assessment**: Superb ergonomic thumb zone implementation for mobile.
   - Desktop table action row (lines 360–377): Sticky right column with 32×32px icon buttons (`w-8 h-8 rounded-[8px]`).

4. **Empty State**:
   - Lines 532–553: Clean empty state with `users` icon, localized heading and description, and "Tambah Pelanggan Baru" button protected by `customers.create`.

5. **RBAC & Authorization Audit**:
   - Lines 156, 163, 341, 362, 486, 543, 559 consistently check `Context::hasPermission('customers.create')` and `Context::hasPermission('crm.manage')`.
   - **Gaps**:
     - Line 370 & Line 500: Links to `route('customers.index', ['search' => $m->name])` are unguarded. If a user has `crm.view` but lacks `customers.view`, clicking these links results in a 403 Forbidden error.
     - **Recommendation**: Wrap profile links with `@if (\App\Support\Context::hasPermission('customers.view'))`.

---

#### View B: `resources/views/app/crm/vouchers.blade.php` (Manajemen Voucher & Promo CRM)

1. **Header Structure & Critical Action Proliferation**:
   - Lines 1–3: Passes `'title' => __('crm.vouchers_title') . ' - Cooca'`. Omits `headerTitle`.
   - Lines 19–30: Renders `<x-module-header>`:
     ```blade
     <x-module-header
         module="crm"
         title="{{ __('crm.vouchers_title') }}"
         subtitle="{{ __('crm.vouchers_subtitle') }}">
         @if (\App\Support\Context::hasPermission('crm.manage'))
             <button type="button" @click="showVoucherModal = true" ...>
                 <span>{{ __('crm.create_voucher') }}</span>
             </button>
         @endif
     </x-module-header>
     ```
   - In Tile 4 of Bento Hero (lines 100–111):
     ```blade
     @if (\App\Support\Context::hasPermission('crm.manage'))
         <button type="button" @click="showVoucherModal = true" ...>
             <span>{{ __('crm.create_voucher_btn') }}</span>
         </button>
     @endif
     ```
   - In the Section Banner Card (lines 118–132):
     ```blade
     <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 ...">
         <div class="space-y-1">
             <h3 class="text-base font-bold text-black dark:text-white">{{ __('crm.vouchers_title') }}</h3>
             <p class="text-[13px] text-black/60 dark:text-white/60">...</p>
         </div>
         @if (\App\Support\Context::hasPermission('crm.manage'))
             <button type="button" @click="showVoucherModal = true" ...>
                 <span>{{ __('crm.create_voucher_btn') }}</span>
             </button>
         @endif
     </div>
     ```
   - **CRITICAL FINDING: Triple Redundant Action Buttons**:
     The primary action "Buat Voucher" is rendered **THREE SEPARATE TIMES** on this page within 100 vertical pixels!
     1. Inside `<x-module-header>` action slot.
     2. Inside Bento Tile 4 Action Tray.
     3. Inside the Section Banner Card.
     Furthermore, the title `{{ __('crm.vouchers_title') }}` is rendered twice (in `<x-module-header>` and in the banner card).
     - **Recommendation**: Strictly eliminate 2 redundant buttons per `/ui-simplify-layout`:
       - Retain only the primary button in `<x-module-header>` action slot.
       - Repurpose Tile 4 into an analytical KPI metric (e.g. "Total Nilai Diskon Terpakai" or "Rata-rata Diskon").
       - Remove the banner card or streamline it into a clean section label without a duplicate button.

2. **Mobile Responsiveness (375px vs 1280px)**:
   - 4 Bento KPI tiles stack vertically on mobile (375px) = ~550px of vertical scroll before reaching vouchers.
   - Vouchers Grid (lines 136–210): `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4`. Collapses to 1 column on mobile smoothly.
   - Voucher code copy button (line 146–149) provides instant feedback (`copiedCode === v.code`).
   - Voucher toggle button (line 200–205): `w-full h-11 min-h-[44px] sm:h-10 rounded-[12px]`. Fully compliant with 44px tap targets on mobile.

3. **Empty State**:
   - Lines 220–239: High quality empty state with `ticket` icon, clear copy, and permission-gated "Terbitkan Voucher Pertama" button.

4. **RBAC & Authorization Audit**:
   - All management actions (modal, create buttons, toggle status) are rigorously protected by `Context::hasPermission('crm.manage')`.

---

### 2.3 Module: Customers

#### View A: `resources/views/app/customers/index.blade.php` (Buku Direktori Pelanggan)

1. **Header Structure & Double Header Analysis**:
   - Lines 1–3: Passes `'title' => __('customers.title') . ' - Cooca'`. Omits `headerTitle`.
   - Lines 227–254: Renders `<x-module-header>` with 3 action buttons:
     - Export Excel (`customers.export`)
     - Import Excel (`customers.create`)
     - Tambah Pelanggan (`customers.create`)
   - Line 410 (Table container header): Renders `<h2>{{ __('customers.directory_title') }}</h2>`.
   - **Finding**: Well-structured hierarchy. The table container header uses "Direktori & Buku Kontak Pelanggan" (`customers.directory_title`), avoiding duplicate naming with the module header title "Buku Pelanggan & Rekanan".

2. **Mobile Responsiveness (375px vs 1280px)**:
   - **Bento Hero Metrics (lines 287–379)**:
     - Uses `grid grid-cols-2 lg:grid-cols-4 gap-4`.
     - Unlike CRM (which uses `grid-cols-1`), Customers uses `grid-cols-2` on mobile. This keeps vertical height compact (only 2 rows = ~220px).
     - **However**: In Tile 3 ("Total Piutang Kasbon", lines 338–349):
       ```blade
       <div class="text-xl sm:text-2xl font-bold tracking-tight text-[#FF3B30] tabular-nums">
           Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
       </div>
       ```
       On a 375px screen, each card has width ~(375 - 32 - 16)/2 = ~163px. With `p-5` (40px horizontal padding), content width is only ~123px! A value like "Rp 150.000.000" overflows or wraps into 3 broken lines.
       - **Recommendation**: Adjust padding and font size for mobile: `p-3.5 sm:p-5`, and `text-lg sm:text-2xl truncate` with title tooltip.
   - **Search & Filter Bar (lines 382–404)**:
     - `flex flex-col sm:flex-row items-center gap-3`. Responsive and uncluttered.
   - **Mobile Customer Card View (lines 554–693)**:
     - Clean, well-spaced card presentation.
     - Contact & Metrics summary uses 2-column grid (`grid grid-cols-2 gap-2 text-[12px]`) displaying WhatsApp contact and B2B Terms / Email.
     - Ergonomic thumb zone action buttons: "Chat WA" (`flex-1 h-11`), "Profil" (`flex-1 h-11`), "Edit" (`h-11 px-3.5`), "Hapus" (`h-11 w-11`). Every tap target meets or exceeds 44px.

3. **CRITICAL View-Level RBAC Gaps**:
   - **Gap 1: Unconditional Rendering of Edit Modal Template (line 923)**:
     - The Add Customer modal (line 730) is correctly wrapped in:
       ```blade
       @if (\App\Support\Context::hasPermission('customers.create'))
           <template x-teleport="body">
               <div x-show="showAddModal" ...>
       ```
     - However, the Edit Customer modal (line 923) has **NO PERMISSION CHECK**:
       ```blade
       <!-- MODAL 2: EDIT PELANGGAN -->
       <template x-teleport="body">
           <div x-show="showEditModal" ...>
               <form method="POST" :action="'/customers/' + (editCustomer.slug || editCustomer.id)" ...>
       ```
       This means the entire edit form markup, inputs, and submit button are injected into the DOM for any user who visits this page (even read-only viewers).
   - **Gap 2: Unguarded "Edit" Button in Detail Modal (line 1205)**:
     - Inside the Customer Detail Profile modal (lines 1205–1209):
       ```blade
       <button type="button" @click="showDetailModal = false; openEditModal(selectedCustomer.id)"
           class="h-12 px-6 rounded-[14px] bg-black/[0.05] ...">
           <i data-lucide="edit-3" class="w-4 h-4"></i>
           <span>{{ __('common.edit') }}</span>
       </button>
       ```
       This button is **completely unguarded**! While the main table hides the edit button from unauthorized users (`@if (\App\Support\Context::hasPermission('customers.edit'))`), any user who can view customer details can simply click this "Edit" button, which calls `openEditModal()` and opens the edit modal form!
       - **Recommendation**: Wrap both the edit button in the detail modal and the Edit Modal template itself with `@if (\App\Support\Context::hasPermission('customers.edit'))`.

4. **Cross-Module Terminology / Translation Leakage**:
   - In `customers/index.blade.php`, multiple strings were copied from the `purchasing` module without translating or localizing under `customers`:
     - Line 137: `{{ __('purchasing.terjadi_kesalahan_saat_mengunggah_file') }}`
     - Line 139: `{{ __('purchasing.terjadi_kesalahan_saat_mengunggah_file') }}`
     - Line 208: `{{ __('purchasing.terjadi_kesalahan_sistem_saat_mengeksekusi_import') }}`
     - Line 210: `{{ __('purchasing.terjadi_kesalahan_sistem') }}`
     - Line 1276: `{{ __('purchasing.unduh_template_excel') }}`
     - Line 1302: `{{ __('purchasing.unduh_template_xlsx') }}`
   - **Finding**: Direct violation of domain isolation. The customer management interface should use `customers.*` or `common.*` translation keys, never `purchasing.*`.

5. **Modals & Canvas XXL Responsiveness**:
   - Add Modal & Edit Modal (`lg:max-w-5xl`): 2-column layout on desktop, smoothly translates to bottom sheet with drag handle on mobile (`rounded-t-[28px] max-h-[92vh] overflow-y-auto`).
   - Import Modal (`lg:max-w-6xl`): Highly sophisticated interactive pre-commit validation preview with dropzone, duplicate strategy selector (Skip vs Update), and status filters (All, Valid, Duplicate, Error). Table is horizontally scrollable with sticky header.

---

## 3. Comprehensive Synthesis & Recommendations Matrix

| Module | File | Identified Issue | Severity | Proposed Concrete Fix |
|---|---|---|---|---|
| **Community** | `events.blade.php` | Double Header: Topbar title vs inner `<h1>Agenda & Pelatihan Komunitas</h1>` banner card | **Medium** | Remove inner `<h1>` banner card. Pass localized `'headerTitle' => __('community.agenda_event_komunitas_cooca')` to `@extends`, or replace banner card with a slim category filter bar. |
| **Community** | `events.blade.php` | Breadcrumb manually written with raw HTML/icons | **Low** | Replace lines 124–138 with standard `<x-breadcrumb :items="[['label' => __('common.dashboard'), 'url' => route('dashboard')], ['label' => __('community.title'), 'url' => route('community.index')], ['label' => __('community.agenda_events')]]" />`. |
| **Community** | `events.blade.php` | Dangerous proximity of "Batal" button next to "Buka Tiket" on mobile | **Medium** | Restructure action row: "Buka Tiket Digital" full width primary button; "Batalkan Pendaftaran" as small secondary text link below or behind a popover menu. |
| **Community** | `events.blade.php` & `index.blade.php` | ~220 lines of duplicate Ticket Modal & Checkin JS | **Medium** | Extract ticket modal into `<x-community.ticket-modal />` reusable component. |
| **Community** | `index.blade.php` | Mobile DOM order bloat: Left sidebar stacks before feed on 375px | **High** | On mobile, reorder DOM via `order-2 lg:order-1` or collapse left sidebar items into a compact tab rail so feed content starts immediately below header. |
| **Community** | `index.blade.php` | Small tap targets (~32px) on feed action buttons (Reply, Repost, Like, Share) | **Medium** | Increase padding to `min-w-[40px] min-h-[40px] flex items-center justify-center` for touchscreen compliance. |
| **Community** | `index.blade.php` | Post deletion restricted to author only; moderators cannot delete | **High** | Update check to `@if ((string)$post->owner_id === (string)auth()->id() \|\| \App\Support\Context::hasPermission('community.manage'))`. |
| **CRM** | `members.blade.php` | Header Inconsistency: Topbar displays "Dashboard" fallback while `<x-module-header>` displays title | **Medium** | Pass `'headerTitle' => __('crm.title')` and `'headerSubtitle' => __('crm.subtitle')` to `@extends('layouts.app')` for unified topbar sync. |
| **CRM** | `members.blade.php` | Mobile KPI Stacking: 4 cards stack vertically (~620px height) | **High** | Adopt `grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4` (or horizontal swipe snap slider) to reduce mobile scroll overhead by 60%. |
| **CRM** | `members.blade.php` | Filter Bar crowding on 375px mobile | **Medium** | Use 2-row layout on mobile: search full-width on top, 2 selects in 2-column grid below, Filter/Reset full width. |
| **CRM** | `members.blade.php` | Mobile customer card stacks 3 mini bento boxes vertically | **Medium** | Change `grid grid-cols-1 sm:grid-cols-3 gap-2` to `grid grid-cols-3 gap-1.5` so metrics sit side-by-side. |
| **CRM** | `members.blade.php` | Unguarded customer profile links (403 risk) | **Medium** | Wrap `route('customers.index', ...)` links with `@if (\App\Support\Context::hasPermission('customers.view'))`. |
| **CRM** | `vouchers.blade.php` | **Triple Redundant "Buat Voucher" Buttons** | **High** | Remove duplicate button from Tile 4 and from Banner card. Keep only the primary button in `<x-module-header>` slot. Repurpose Tile 4 into an analytical metric tile. |
| **CRM** | `vouchers.blade.php` | Mobile KPI Stacking: 4 cards stack vertically (~550px height) | **High** | Convert to `grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4` or swipe snap. |
| **Customers** | `index.blade.php` | **Unguarded Edit Modal Template** | **High** | Wrap `showEditModal` template (line 923) with `@if (\App\Support\Context::hasPermission('customers.edit'))`. |
| **Customers** | `index.blade.php` | **Unguarded "Edit" Button in Detail Modal** | **High** | Wrap "Edit" button inside Detail modal (line 1205) with `@if (\App\Support\Context::hasPermission('customers.edit'))`. |
| **Customers** | `index.blade.php` | Cross-module translation leakage (`purchasing.*` keys) | **Medium** | Replace `purchasing.terjadi_kesalahan...` and `purchasing.unduh_template_xlsx` with proper `customers.*` and `common.*` keys. |
| **Customers** | `index.blade.php` | Mobile 375px KPI card padding causing text wrapping on currency | **Medium** | Change card padding from `p-5` to `p-3.5 sm:p-5` and apply `truncate` with `title` attribute on monetary values. |

---

## 4. Verification and Validation Checklist

For subsequent implementation agents:
- [ ] Run `php -l` on all modified Blade files:
  ```powershell
  php -l resources/views/app/community/events.blade.php
  php -l resources/views/app/community/event_payment.blade.php
  php -l resources/views/app/community/index.blade.php
  php -l resources/views/app/crm/members.blade.php
  php -l resources/views/app/crm/vouchers.blade.php
  php -l resources/views/app/customers/index.blade.php
  ```
- [ ] Verify automated test suite:
  ```powershell
  php artisan test --filter=Customer
  php artisan test --filter=Crm
  php artisan test --filter=Community
  ```
- [ ] Responsive viewport check:
  - 375px (iPhone SE / standard mobile portrait)
  - 768px (iPad portrait / tablet)
  - 1280px (Standard laptop desktop)
- [ ] RBAC verification:
  - Login as non-editor role (e.g., cashier / viewer) -> Verify Add, Edit, Delete, Import, Export buttons and modals are completely absent from DOM.
  - Verify clicking Detail Modal does NOT expose an active "Edit" button.
