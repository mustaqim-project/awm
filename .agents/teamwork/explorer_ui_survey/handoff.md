# Handoff Report: UI Views Survey & Layout Simplification Audit

**Agent**: UI & View Survey Explorer  
**Date**: 2026-11-07  
**Scope**: All Blade views in `resources/views/app/community/`, `resources/views/app/crm/`, and `resources/views/app/customers/`  
**Referenced Directives**: `cooca-agent-directive`, `/ui-simplify-layout`, `/responsive-ui-ux`, `/ui-layout-hierarchy-reorganizer`, `/laravel-rbac`, `/laravel-module-audit`

---

## 1. Observation

### 1.1 Complete Blade File Inventory (6 Files)
A full recursive file scan on the target module directories identified exactly 6 Blade template files:

| Module | File Relative Path | Lines | Bytes | Template Nature | Syntax (`php -l`) |
|---|---|---|---|---|---|
| **Community** | `resources/views/app/community/events.blade.php` | 638 | 45,261 | Extends `layouts.app` | No syntax errors detected |
| **Community** | `resources/views/app/community/event_payment.blade.php` | 295 | 16,879 | Extends `layouts.app` | No syntax errors detected |
| **Community** | `resources/views/app/community/index.blade.php` | 1,277 | 87,752 | Extends `layouts.app` | No syntax errors detected |
| **CRM** | `resources/views/app/crm/members.blade.php` | 763 | 57,060 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |
| **CRM** | `resources/views/app/crm/vouchers.blade.php` | 365 | 27,141 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |
| **Customers** | `resources/views/app/customers/index.blade.php` | 1,502 | 117,347 | Extends `layouts.app` + `<x-module-header>` | No syntax errors detected |

All 6 files were tested with `php -l` and returned: `No syntax errors detected`.

---

### 1.2 Verbatim Code Observations by File

#### A. `resources/views/app/community/events.blade.php`
1. **Header Duplication & Hardcoded Indonesian String**:
   - Lines 1–5:
     ```blade
     @extends('layouts.app', [
         'title' => __('community.agenda_event_komunitas_cooca'),
         'headerTitle' => 'Agenda Event Komunitas',
         'headerSubtitle' => 'Seminar bisnis, pelatihan kasir POS, dan meetup eksklusif untuk seluruh pengguna Cooca',
     ])
     ```
     `'headerTitle'` is a hardcoded string and renders in `layouts/partials/topbar.blade.php:205-211`.
   - Lines 141–155: Inside the content canvas:
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
     Direct double header between topbar and inner banner.
2. **Action Row Proximity on Mobile**:
   - Lines 386–407: In "Tiket & Pendaftaran Saya", the primary button ("Buka Tiket Digital") and the destructive button ("Batal") are placed side-by-side in `flex items-center gap-2 pt-2 border-t`, causing crowding and risk of accidental cancellation on mobile touchscreens.
3. **Redundant Code Block**:
   - Lines 19–121 and 416–636 contain ~220 lines of Alpine.js ticket logic and modal template that are copied verbatim in `community/index.blade.php:66-168` and `895-1080`.

#### B. `resources/views/app/community/event_payment.blade.php`
1. **Header Hierarchy**:
   - Lines 1–5: Sets `'headerTitle' => __('community.ticket_payment_event')` in `@extends`.
   - Lines 91–103: Uses a back navigation link (`mb-5 flex items-center justify-between gap-3`).
   - No redundant `<h1>` banner in the body.
2. **Mobile Layout**:
   - Grid `grid-cols-1 lg:grid-cols-12 gap-6`. On 375px, QRIS card (max-w-[320px]) and QR SVG (`w-64 h-64`) stack above event summary without horizontal overflow.
3. **Action Hierarchy**:
   - Line 182–186: "Cek Status Pembayaran" button (`py-3 text-xs font-bold bg-[#007AFF]`) is full width on mobile and 44px height.

#### C. `resources/views/app/community/index.blade.php`
1. **Mobile DOM Stacking Flaw**:
   - Lines 177–272: Left sidebar (`aside lg:col-span-3` containing user profile card, nav rail, composer button, and storage quota widget) sits before `main lg:col-span-6` in DOM.
   - On 375px mobile, the entire sidebar stacks vertically above the feed, forcing users to scroll >350px before reaching the event slider or thread posts.
2. **Touch Target Size**:
   - Lines 674–714: Feed action icons (Reply, Repost, Like, Share) use `p-1.5 rounded-full`, creating ~32×32px hit areas below Apple HIG 44px recommendation.
3. **RBAC Moderation Gap**:
   - Line 644:
     ```blade
     @if ((string) $post->owner_id === (string) auth()->id())
         <form method="POST" action="{{ route('community.destroy', $post) }}" ...>
     ```
     Only the author can delete a post. Admins or moderators with `community.manage` cannot remove inappropriate posts.

#### D. `resources/views/app/crm/members.blade.php`
1. **Header Inconsistency**:
   - Lines 1–3: `@extends('layouts.app', ['title' => __('crm.members_title') . ' - Cooca'])`. Omits `headerTitle`, causing topbar to display default "Dashboard".
   - Lines 92–95: `<x-module-header module="crm" title="{{ __('crm.title') }}" subtitle="{{ __('crm.subtitle') }}" />`
   - Line 227: `<h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('crm.title') }}</h2>`
2. **Mobile KPI Metrics Bloat**:
   - Lines 102–172: 4 Bento KPI cards arranged with `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`. On mobile (375px), all 4 cards stack vertically, consuming >620px of vertical space before the user reaches the filter bar or table.
3. **Mobile Customer Card Metric Stacking**:
   - Lines 452–482: Each customer card uses `grid grid-cols-1 sm:grid-cols-3 gap-2`, which stacks 3 mini boxes vertically inside every customer card on mobile.
4. **RBAC Link Gap**:
   - Lines 370 & 500: `<a href="{{ route('customers.index', ['search' => $m->name]) }}" ...>` is not wrapped in `@if (\App\Support\Context::hasPermission('customers.view'))`.

#### E. `resources/views/app/crm/vouchers.blade.php`
1. **Critical Triple Action Proliferation**:
   - Line 24–28 in `<x-module-header>` slot:
     ```blade
     @if (\App\Support\Context::hasPermission('crm.manage'))
         <button type="button" @click="showVoucherModal = true" class="h-11 min-h-[44px] px-4 rounded-[12px] bg-[#007AFF] ...">
             <span>{{ __('crm.create_voucher') }}</span>
         </button>
     @endif
     ```
   - Line 104–108 in Bento Tile 4:
     ```blade
     @if (\App\Support\Context::hasPermission('crm.manage'))
         <button type="button" @click="showVoucherModal = true" class="w-full h-11 px-4 rounded-[12px] bg-[#007AFF] ...">
             <span>{{ __('crm.create_voucher_btn') }}</span>
         </button>
     @endif
     ```
   - Line 126–131 in Section Banner card:
     ```blade
     @if (\App\Support\Context::hasPermission('crm.manage'))
         <button type="button" @click="showVoucherModal = true" class="h-11 px-5 rounded-[12px] bg-[#007AFF] ...">
             <span>{{ __('crm.create_voucher_btn') }}</span>
         </button>
     @endif
     ```
   - **Finding**: 3 identical primary action buttons rendered on the same view within 100 vertical pixels.
2. **Mobile KPI Stacking**:
   - Lines 50–112: 4 cards stack vertically on 375px mobile (= ~550px vertical scroll).

#### F. `resources/views/app/customers/index.blade.php`
1. **Critical RBAC Gap on Edit Modal**:
   - Line 730: Add Modal is guarded with `@if (\App\Support\Context::hasPermission('customers.create'))`.
   - Line 923: Edit Modal (`<div x-show="showEditModal">`) has **ZERO permission guards**. The full edit form template is injected into the DOM for any user.
2. **Critical RBAC Gap on Detail Modal "Edit" Button**:
   - Lines 1205–1209: Inside the customer detail profile modal:
     ```blade
     <button type="button" @click="showDetailModal = false; openEditModal(selectedCustomer.id)"
         class="h-12 px-6 rounded-[14px] bg-black/[0.05] ...">
         <i data-lucide="edit-3" class="w-4 h-4"></i>
         <span>{{ __('common.edit') }}</span>
     </button>
     ```
     This button is **completely unguarded**. A user with read-only permission (`customers.view`) can click "Edit" in the detail modal and invoke `openEditModal()`, bypassing view protection!
3. **Cross-Module Translation Leakage**:
   - Lines 137, 139: `{{ __('purchasing.terjadi_kesalahan_saat_mengunggah_file') }}`
   - Lines 208, 210: `{{ __('purchasing.terjadi_kesalahan_sistem_saat_mengeksekusi_import') }}` / `{{ __('purchasing.terjadi_kesalahan_sistem') }}`
   - Lines 1276, 1302: `{{ __('purchasing.unduh_template_excel') }}` / `{{ __('purchasing.unduh_template_xlsx') }}`
   - 6 occurrences of `purchasing.*` keys inside the `customers` view.
4. **Mobile 375px Bento KPI Padding**:
   - Lines 287–379 uses `grid grid-cols-2 lg:grid-cols-4 gap-4`. Tile 3 with `p-5` restricts content width to ~123px, risking text wrap on currency strings ("Rp 120.000.000").

---

## 2. Logic Chain

1. **Premise 1 (Layout Simplification & Header Hygiene)**:
   - Observation: `community/events.blade.php` defines a topbar title and also renders an internal `<h1>` card with identical text. `crm/vouchers.blade.php` renders 3 duplicate "Buat Voucher" buttons.
   - Inference: Redundant headers and duplicated action buttons create cognitive clutter, violate `/ui-simplify-layout` principles, and waste vertical screen real estate.
   - Deduction: Removing the inner banner `<h1>` in `events.blade.php` and deduplicating the "Buat Voucher" buttons in `vouchers.blade.php` will restore clean visual hierarchy.

2. **Premise 2 (Mobile-First Ergonomics & Viewport Optimization)**:
   - Observation: In `crm/members.blade.php` and `crm/vouchers.blade.php`, 4 KPI cards stack vertically (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`). In `community/index.blade.php`, the left sidebar is placed before the feed in DOM.
   - Inference: On 375px mobile screens, vertical stacking forces users through >500px of scrolling before reaching primary functional content.
   - Deduction: Converting KPI grids to a 2×2 layout (`grid-cols-2 lg:grid-cols-4`) on mobile, and reordering the community sidebar to appear below or alongside feed content on mobile, directly resolves mobile vertical bloat.

3. **Premise 3 (RBAC Enforcement & Principle of Least Privilege)**:
   - Observation: In `customers/index.blade.php:923`, the Edit Modal template is not guarded by `@if (\App\Support\Context::hasPermission('customers.edit'))`, and in line 1205, the Detail Modal contains an unguarded "Edit" button that directly opens the edit modal.
   - Inference: Any user with read-only access (`customers.view`) can see and trigger the edit modal from the detail view.
   - Deduction: Enclosing both the modal template and the detail modal edit button in `@if (\App\Support\Context::hasPermission('customers.edit'))` eliminates this authorization leakage.

4. **Premise 4 (Modular Isolation & Zero Feature Leakage)**:
   - Observation: In `customers/index.blade.php:137, 208, 1276`, translation strings from `purchasing` are referenced.
   - Inference: A customer management view referencing purchasing keys violates module boundaries and risks untranslated or mismatched copy.
   - Deduction: Replacing these keys with `customers.*` and `common.*` keys restores domain isolation.

---

## 3. Caveats

1. **Read-Only Investigation Scope**:
   - As an explorer agent, no code modifications were applied directly to the Blade files or controllers. All recommendations are packaged as verified observations and actionable tasks for the implementing agent.
2. **Backend Route Middleware Gaps**:
   - This survey focused on the Blade presentation layer. A preliminary scan of `routes/owner.php` indicated that Community routes currently lack `require.permission` middleware guards. This must be verified and addressed by the RBAC explorer and implementer.
3. **Component Reusability**:
   - The ~220 lines of duplicate ticket modal logic between `community/events.blade.php` and `community/index.blade.php` can be refactored into a shared component (`<x-community.ticket-modal />`), but this should be coordinated to prevent breaking existing Alpine bindings.

---

## 4. Conclusion

The presentation layer of Community, CRM, and Customers has a solid visual foundation, 100% valid PHP syntax, and thoughtful Apple HIG touches. However, it requires targeted remediation in 4 areas:
1. **Header & Button Simplification**: Remove double headers in `community/events.blade.php`; eliminate the 2 redundant "Buat Voucher" buttons in `crm/vouchers.blade.php`.
2. **Responsive Mobile Tuning**: Transition 4-card vertical stacks in CRM to 2×2 grids; reorder the community sidebar on mobile; adjust `p-5` padding in customer KPI tiles.
3. **RBAC Guarding**: Protect the Edit Modal and the Detail Modal "Edit" button in `customers/index.blade.php` with `customers.edit`; gate customer profile links in CRM with `customers.view`; update community post deletion to permit moderation by `community.manage`.
4. **Domain Terminology Isolation**: Replace 6 `purchasing.*` translation keys in `customers/index.blade.php` with `customers.*` keys.

---

## 5. Verification Method

### 5.1 Syntax Verification
Run syntax validation across all 6 Blade views:
```powershell
$files = @(
    "resources\views\app\community\events.blade.php",
    "resources\views\app\community\event_payment.blade.php",
    "resources\views\app\community\index.blade.php",
    "resources\views\app\crm\members.blade.php",
    "resources\views\app\crm\vouchers.blade.php",
    "resources\views\app\customers\index.blade.php"
)
foreach ($f in $files) { php -l $f }
```
**Expected**: All 6 files return `No syntax errors detected`.

### 5.2 Automated Test Suite
Run project tests covering the target domains:
```powershell
php artisan test --filter=Customer
php artisan test --filter=Crm
php artisan test --filter=Community
```

### 5.3 Invalidation Conditions
- An `<h1>` title appears both in the topbar and the main page canvas of `events.blade.php`.
- More than 1 "Buat Voucher" button is visible simultaneously on `crm/vouchers.blade.php`.
- A user logged in without `customers.edit` permission can open the Customer Detail modal and click an active "Edit" button.
- Any Blade file in `resources/views/app/customers/` references `__('purchasing....')`.
