# Handoff Report: UI Views Survey & Layout Simplification Audit

**Agent**: Explorer 1 (UI Views Explorer)  
**Date**: 2026-11-07  
**Scope**: All Blade views in `resources/views/app/analytics`, `resources/views/app/approvals`, and `resources/views/app/billing`  
**Referenced Directives**: `/ui-simplify-layout`, `cooca-agent-directive`, `/laravel-rbac`, `/laravel-module-audit`

---

## 1. Observation

### 1.1 Complete Directory & File Inventory (9 Blade Files)
A comprehensive recursive scan was conducted on the designated paths. Exactly 9 Blade template files exist across the 3 target modules:

| Module | File Relative Path | Lines | Bytes | Template Nature |
|---|---|---|---|---|
| **Analytics** | `resources/views/app/analytics/index.blade.php` | 573 | 36,137 | Extends `layouts.app` |
| **Approvals** | `resources/views/app/approvals/inbox.blade.php` | 381 | 25,374 | Extends `layouts.app` |
| **Approvals** | `resources/views/app/approvals/history.blade.php` | 192 | 12,863 | Extends `layouts.app` |
| **Approvals** | `resources/views/app/approvals/rules.blade.php` | 522 | 37,321 | Extends `layouts.app` |
| **Billing** | `resources/views/app/billing/limits.blade.php` | 2,694 | 227,308 | Extends `layouts.app` (uses `<x-module-header>`) |
| **Billing** | `resources/views/app/billing/checkout.blade.php` | 1,090 | 80,343 | Extends `layouts.app` (uses `<x-module-header>`) |
| **Billing** | `resources/views/app/billing/payment.blade.php` | 905 | 67,557 | Extends `layouts.app` (uses `<x-module-header>`) |
| **Billing** | `resources/views/app/billing/history.blade.php` | 443 | 32,113 | Extends `layouts.app` (uses `<x-module-header>`) |
| **Billing** | `resources/views/app/billing/invoice.blade.php` | 1,014 | 51,789 | Standalone HTML5 document with `@page` print styles |

Syntax check (`php -l`) was executed on all 9 files: **0 syntax errors detected**.

---

### 1.2 Module-by-Module Detailed Observations

#### A. Module `analytics` (`resources/views/app/analytics/index.blade.php`)
1. **Header Architecture & Redundancy**:
   - `index.blade.php:1-5` passes `'headerTitle' => __('analytics.title')` and `'headerSubtitle' => __('analytics.subtitle')` to `layouts.app`.
   - `resources/views/layouts/partials/topbar.blade.php:205-211` automatically renders `$headerTitle` and `$headerSubtitle` in the global topbar.
   - However, inside the view body (`index.blade.php:214-229`), an internal card box (`rounded-[20px] bg-white/85 ...`) renders another header block:
     ```blade
     <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
         {{ __('analytics.executive_report') }}
     </div>
     <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
         ...
         <span>{{ __('analytics.growth_trends') }}</span>
     </h1>
     ```
     This results in **duplicate `<h1>` titles and subtitle clutter** between the macOS topbar and the content area.
2. **Mobile KPI Metrics Bloat (Bento Card Bloat)**:
   - `index.blade.php:316-397` arranges 4 KPI cards (Total Omzet, Total Transaksi & AOV, Laba Kotor & Margin, Estimasi Laba Bersih) using `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`.
   - On mobile viewports (375px–430px), these 4 cards stack vertically, consuming >750px of vertical height before the user reaches the first chart (`analyticsTrendChart`).
   - Per `/ui-simplify-layout` (§Horizontal Snap Slider), metric rows on mobile should use horizontal swipe snap: `flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0`.
3. **Controls & Form Wrapping**:
   - `index.blade.php:232-286` puts the period segmented buttons, location selector dropdown, print button, and formal report link in a single flex container.
   - On 375px mobile, these wrap into 3–4 fragmented rows with uneven heights.
   - Touch targets for segmented buttons are `min-h-[36px]`, slightly under Apple HIG 44×44px recommendation for mobile primary touch targets.
4. **Empty State & Charts**:
   - Top 10 products table (`index.blade.php:485-491` and `526-530`) has well-crafted `@empty` states on both desktop table (`colspan="6"`) and mobile card list.
   - Donut chart (`index.blade.php:175-178`) handles empty transactions gracefully by falling back to `['{{ __('analytics.no_transaction_yet') }}']` with `[1]` placeholder slice.
5. **RBAC & Authorization Gap**:
   - Line 281: `<a href="{{ route('reports.index') }}" ...>`
   - Line 452: `<a href="{{ route('reports.index', ['tab' => 'stock']) }}" ...>`
   - Neither link checks `@can('reports.view')` or `Context::hasPermission('reports.view')`. Users without `reports.view` will see these links and encounter 403 upon clicking.

---

#### B. Module `approvals` (`resources/views/app/approvals/`)

##### 1. `inbox.blade.php` (MAR Inbox)
1. **Header Duplication**:
   - Line 1-5 passes `headerTitle` and `headerSubtitle` to `@extends`.
   - Line 11-24 renders an inner card with `<i data-lucide="inbox"></i>`, uppercase `{{ __('approvals.badge_tiered_auth') }}`, and `<h1>{{ __('approvals.inbox_title') }}</h1>`.
2. **CRITICAL RBAC Gap on Action Buttons**:
   - Lines 132-146:
     ```blade
     <button type="button" @click="openRejectModal('{{ $ticket->id }}', ...)">
         <i data-lucide="x-circle" class="w-4 h-4"></i>
         <span>{{ __('approvals.btn_reject') }}</span>
     </button>
     <button type="button" @click="openApproveModal('{{ $ticket->id }}', ...)">
         <i data-lucide="check-circle-2" class="w-4 h-4"></i>
         <span>{{ __('approvals.btn_approve_level', ['level' => $ticket->current_level]) }}</span>
     </button>
     ```
   - These approve and reject buttons are **completely unguarded** in the Blade view. Any user who has `approvals.view` will see active "Tolak" and "Setujui" buttons on every ticket. Clicking and submitting causes an unexpected 403 Forbidden because `POST /approvals/{approvalRequest}/approve` requires `approvals.manage`.
   - In addition, line 31: `@if(\App\Support\Context::isOwner() || \App\Support\Context::isAdminOrOwner())` gates the "Kelola Aturan" button by role hardcoding rather than permission check `approvals.manage`.
3. **Mobile Layout & Button Proximity**:
   - Line 87-148: Ticket card uses `flex flex-col lg:flex-row lg:items-center justify-between gap-5`.
   - In mobile view (375px), the action buttons sit at the bottom with `flex items-center gap-3 shrink-0 pt-3 lg:pt-0`. "Setujui Level 1" (`px-6`) and "Tolak" (`px-4`) crowd each other and can clip or wrap on narrow screens. A 2-column grid (`grid grid-cols-2 sm:flex sm:w-auto gap-2.5`) creates symmetric, comfortable 44px tap targets.
4. **Modals & Overlap**:
   - Approve modal uses `z-[200]` and reject alertdialog uses `z-[220]` with `x-teleport="body"` and backdrop blur. Complies with the centralized z-index scale.
   - Textareas use `text-[16px] sm:text-xs` (prevents iOS Safari auto-zoom).
5. **Empty State**:
   - Lines 150-160: `@empty` block features `check-check` icon and polite copy.

##### 2. `history.blade.php` (Audit Archive)
1. **Header Duplication**:
   - Lines 1-5 passes `headerTitle` and `headerSubtitle` to `@extends`, yet lines 11-24 renders an internal `<header>` with `history` icon and `history_title`.
2. **Filters & Layout**:
   - Filter bar (`lines 35-55`) uses horizontal scrolling pill links (`overflow-x-auto pb-1 no-scrollbar`).
   - Desktop table (`hidden md:block overflow-x-auto`) and mobile card list (`block md:hidden`) are separated cleanly.
   - Status badges adhere to the single-badge anti-pill rule: either Approved (green `check-circle-2`) or Rejected (red `x-circle`).
3. **Empty State**:
   - Fully handled on both desktop table (`colspan="6"`) and mobile card list.

##### 3. `rules.blade.php` (Approval Plafond Policy Settings)
1. **Header Duplication**:
   - Lines 1-5 passes `headerTitle` and `headerSubtitle` to `@extends`, while lines 11-24 renders an inner header with `sliders` icon and `rules_title`.
2. **RBAC Gap on Create Rule Action Buttons**:
   - Lines 31-37: The header button `openCreateModal()` is **NOT** wrapped in `approvals.manage` permission check.
   - Lines 132-139: In the empty state, the CTA button `openCreateModal()` (`btn_create_first_rule`) is also **NOT** wrapped in `approvals.manage`.
   - In contrast, rule card edit and delete buttons (`lines 101-120`) ARE wrapped in `@if(\App\Support\Context::hasPermission('approvals.manage'))`. This creates an inconsistency where read-only users see "Tambah Aturan", but cannot edit or delete rules.
3. **Semantic Icon Usage**:
   - Create: `plus` (`w-4 h-4`).
   - Edit: `edit-3` (`w-3.5 h-3.5`).
   - Delete: `trash-2` (`w-3.5 h-3.5`).
   - Complies 100% with the Semantic Icon Registry; no duplicate `+` character in text.
4. **Modals**:
   - Create and Edit modals use XXL responsive bottom sheet (`rounded-t-[28px] sm:rounded-[24px]` with grab bar) and teleport to body.

---

#### C. Module `billing` (`resources/views/app/billing/`)

##### 1. `limits.blade.php` (SaaS Billing & Limit Dashboard - 2,694 lines)
1. **Header Standard**:
   - Uses `<x-module-header>` (`lines 28-51`) and `<x-module-tabs module="billing">`. Omits `headerTitle` from `@extends`, effectively preventing topbar header redundancy.
2. **Button Hierarchy & Semantic Icons**:
   - Top action slot: History link (secondary outline) + Upgrade/Renew button (primary `#007AFF`, guarded by `billing.manage`).
   - Line 1400: `<a href="{{ route('billing.checkout', ['type' => 'storage']) }}"><i data-lucide="plus"></i> Top Up Storage</a>`. Icon `plus` is used for topup; changing to `hard-drive` or `database` provides clearer semantic distinction per `/ui-simplify-layout`.
3. **Storage File Manager Modal (Lines 2338-2510)**:
   - Built with Bento XXL modal standard: `w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] max-h-[90vh]`.
   - Has debounced live search (`@input.debounce.300ms="fetchFiles(1)"`), category filter, pagination, loading spinner, and empty state.
   - **RBAC Gap in Modal**: In the desktop table (`lines 2451-2458`) and mobile card list (`lines 2480-2487`), the `confirmDelete(f)` button is rendered directly inside Alpine `x-for="f in files"` without gating on `billing.manage`. View-only billing users can see the delete button in the file manager modal.
4. **Card Nesting & Comparison Table**:
   - The 8-group comparison matrix table is collapsed by default (`expandedMatrix: false`) with `x-collapse` and `overflow-x-auto`. This prevents severe infinite page bloat on mobile.
   - Safe area `pb-28 lg:pb-10` prevents mobile bottom bar overlap.

##### 2. `checkout.blade.php` (Order & Subscription Checkout - 1,090 lines)
1. **Layout & Responsiveness**:
   - Uses `<x-module-header>` and `<x-module-tabs>`.
   - Layout is structured as 2-column desktop (`lg:col-span-2` steps vs `lg:col-span-1` sticky summary `lg:sticky lg:top-24`).
   - Mobile sticky price indicator (`lines 343-356`) gives instant feedback without obstructing the form.
2. **Buttons & Actions**:
   - Segmented toggle for Monthly vs Annual.
   - Radio cards for Tier selection and Gateway selection.
   - Submit button (`lines 1043-1067`): Properly guarded by `@if (\App\Support\Context::hasPermission('billing.manage'))`. Shows double-submit prevention spinner (`:disabled="isSubmitting"`), and when read-only, displays a clean notice (`billing.readonly_warning`).
   - Touch targets are all $\ge 44$px (`min-h-[44px]` and `min-h-[48px]`).

##### 3. `payment.blade.php` (TriPay Payment Instruction & Status - 905 lines)
1. **Layout & Stepper**:
   - Uses `<x-module-header>` with Back and Print Invoice actions.
   - 4-Phase Stepper (`Order Dibuat` $\rightarrow$ `Bayar TriPay` $\rightarrow$ `Verifikasi Otomatis` $\rightarrow$ `Paket Aktif`) adheres to Quiet Stepper design without competing visual affordance.
2. **Lifecycle State Handling & Zero Overlap**:
   - Real-time countdown timer and adaptive polling (every 3s with `visibilitychange` awareness).
   - When expired or cancelled, QR code is hidden immediately and replaced with an expired notice banner + re-order CTA button (`min-h-[44px]`), preventing merchants from paying into expired gateways.
3. **Touch Targets & Microcopy**:
   - Nominal copy button has feedback (`copiedText === 'nominal'`).
   - Re-check status button has animated spinner.

##### 4. `history.blade.php` (Billing History - 443 lines)
1. **Layout & Snap Slider**:
   - Top 4 Command Pillars use the **Horizontal Snap Slider** pattern on mobile (`lines 58-152`):
     `flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0`.
     This saves ~75% vertical scroll on mobile devices!
2. **Table & Mobile Card List**:
   - Desktop table uses sticky right action column with shadow:
     `sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]`.
   - Mobile card list provides separate `printer` and `quick_detail` touch targets $\ge 44$px.
   - Empty state has Choose Plan CTA guarded by `billing.manage`.

##### 5. `invoice.blade.php` (Official Subscription Tax Invoice - 1,014 lines)
1. **Document Nature**:
   - Standalone HTML5 document with `@page { size: A4 portrait; margin: 10mm 12mm; }`.
   - Floating top action bar (no-print) includes Back, Print, and Download PDF buttons (`min-h-[44px]`).
2. **Client-Side PDF Generation**:
   - Uses `html2pdf.bundle.min.js`.
   - Pre-capture lock (`element.classList.add('pdf-render-mode')`) enforces strict 794px desktop A4 layout with exact column widths and zero overlap, regardless of whether the user triggers the download on a 375px phone or a 4K display.

---

## 2. Logic Chain

### Premise 1: Direct Observation of Redundant Headers
- Observation: `analytics/index.blade.php:1-5`, `approvals/inbox.blade.php:1-5`, `approvals/history.blade.php:1-5`, and `approvals/rules.blade.php:1-5` declare `headerTitle` and `headerSubtitle` in `@extends('layouts.app')`.
- Observation: `layouts/partials/topbar.blade.php:205-211` renders these in the topbar header.
- Observation: Each of these 4 files subsequently creates an internal card container rendering another `<h1>` and subtitle.
- Deduction: This violates `/ui-simplify-layout` ("Satu layar, satu tujuan... Judul yang mengulang breadcrumb atau label berulang bisa dihapus") and causes vertical screen space waste, especially on mobile.
- Solution: Unify these views to either standard `<x-module-header>` (which already encapsulates breadcrumbs, badge, title, subtitle, and action buttons in a single squircle header, matching `billing`) or cleanly separate toolbar controls without duplicate `<h1>`.

### Premise 2: Direct Observation of RBAC View Guard Holes
- Observation: In `approvals/inbox.blade.php:132-146`, the "Tolak" and "Setujui" buttons are rendered for all authenticated users who reach the inbox.
- Observation: In `ApprovalWebController.php:30-37`, `approve` and `reject` routes strictly require `require.permission:approvals.manage`.
- Observation: In `approvals/rules.blade.php:31-37` and `132-139`, the "Tambah Aturan" and "Buat Aturan Pertama" buttons are rendered without permission checks, while the controller requires `approvals.manage` for `rulesStore`.
- Observation: In `analytics/index.blade.php:281, 452`, the links to `reports.index` are displayed without checking `reports.view`.
- Observation: In `billing/limits.blade.php:2453, 2481`, the delete file buttons inside the modal are not gated by `billing.manage`.
- Deduction: Unprotected UI controls expose unauthorized users to dead-end interactions and 403 HTTP error screens, violating R2 of `ORIGINAL_REQUEST.md` ("Amankan seluruh elemen aksi dan navigasi di dalam view Blade dengan pengecekan permission... sehingga elemen aksi tidak tampak bagi pengguna tanpa izin akses").
- Solution: Wrap each mutating action button with `@can('module.permission')` or `@if(\App\Support\Context::hasPermission('...'))`.

### Premise 3: Direct Observation of Mobile Metric Bloat
- Observation: In `analytics/index.blade.php:316-397`, 4 KPI cards are rendered via `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`.
- Observation: In `billing/history.blade.php:59`, the 4 command pillars are rendered with `flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4`.
- Deduction: The Snap Slider in `billing/history` proves that horizontal swipe on mobile saves over 700px of scrolling while maintaining full desktop grid layout without breaking any tests. Applying this pattern to `analytics/index.blade.php` eliminates vertical bloat and brings trends charts into the primary viewport.

---

## 3. Caveats
1. **Physical Browser Pixel Rendering**: Observations are based on static code parsing, AST inspection, stylesheet analysis, and automated test execution. Physical rendering on real hardware screens (e.g. Safari iOS on iPhone 15 Pro) was simulated via CSS media query analysis.
2. **Third-Party Chart.js & html2pdf.js Runtime**: Chart canvas elements (`analyticsTrendChart`, `analyticsHourlyChart`, `analyticsPaymentChart`) and PDF generation (`html2pdf`) depend on client-side JavaScript execution and CDN availability.
3. **No Code Modification Undertaken**: In strict compliance with Explorer 1's read-only mandate, no Blade files or controllers were modified during this survey.

---

## 4. Conclusion & Actionable Remediation Matrix

### 4.1 Summary of Survey Findings

| File | Status | Header Redundancy | Overlap Risk | RBAC View Guard Gap | Mobile Optimization Needed |
|---|---|---|---|---|---|
| `analytics/index.blade.php` | Needs Cleanup | **Yes** (Duplicate `<h1>`) | Low | **Yes** (Missing `@can('reports.view')` on 2 links) | Convert 4 KPI cards to Snap Slider; improve control wrapping |
| `approvals/inbox.blade.php` | Needs Remediation | **Yes** (Duplicate `<h1>`) | Low–Med | **CRITICAL** (Approve/Reject buttons unguarded) | Symmetrical 2-col button grid on mobile; wrap actions in `@can` |
| `approvals/history.blade.php` | Clean | **Yes** (Duplicate `<h1>`) | Zero | None (Read-only) | Remove redundant inner header card |
| `approvals/rules.blade.php` | Needs Remediation | **Yes** (Duplicate `<h1>`) | Low | **Yes** (Create rule buttons unguarded) | Wrap create buttons in `@can('approvals.manage')` |
| `billing/limits.blade.php` | Excellent | None (Uses `<x-module-header>`) | Zero | **Minor** (Delete button in File Manager modal) | Add `canManage` guard to Alpine modal template |
| `billing/checkout.blade.php` | Excellent | None (Uses `<x-module-header>`) | Zero | None (Guarded by `billing.manage`) | None (Well-structured) |
| `billing/payment.blade.php` | Excellent | None (Uses `<x-module-header>`) | Zero | None (Safe lifecycle expiry handling) | None (Well-structured) |
| `billing/history.blade.php` | Benchmark | None (Uses `<x-module-header>`) | Zero | None (Fully guarded) | Standard reference for Snap Slider & sticky table |
| `invoice.blade.php` | Benchmark | None (Standalone Print Sheet) | Zero | None (View-only receipt) | Standard reference for A4 PDF lock |

---

### 4.2 Specific Remediation Plan per File

#### 1. `resources/views/app/analytics/index.blade.php`
- **R1.1**: Remove redundant `headerTitle` and `headerSubtitle` from `@extends` or replace lines 214-229 with a clean `<x-module-header>` that neatly houses period controls and action buttons.
- **R1.2**: Convert KPI cards at lines 316-397 to Horizontal Snap Slider:
  ```html
  <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0">
  ```
- **R1.3**: Wrap lines 281 and 452 with `@can('reports.view')`:
  ```blade
  @can('reports.view')
  <a href="{{ route('reports.index') }}" ...>...</a>
  @endcan
  ```

#### 2. `resources/views/app/approvals/inbox.blade.php`
- **R2.1**: Wrap Approve and Reject action buttons (`lines 132-146`) with permission check:
  ```blade
  @if(\App\Support\Context::hasPermission('approvals.manage'))
      <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-black/[0.06] dark:border-white/[0.08]">
          <button type="button" @click="openRejectModal(...)" class="flex-1 sm:flex-initial min-h-[44px] ...">...</button>
          <button type="button" @click="openApproveModal(...)" class="flex-1 sm:flex-initial min-h-[44px] ...">...</button>
      </div>
  @endif
  ```
- **R2.2**: Change line 31 from `@if(\App\Support\Context::isOwner() || \App\Support\Context::isAdminOrOwner())` to `@if(\App\Support\Context::hasPermission('approvals.manage'))`.
- **R2.3**: Consolidate header block (`lines 11-38`) into standard `<x-module-header>`.

#### 3. `resources/views/app/approvals/history.blade.php`
- **R3.1**: Consolidate header block (`lines 11-32`) to eliminate double title with topbar.

#### 4. `resources/views/app/approvals/rules.blade.php`
- **R4.1**: Wrap "Tambah Aturan" header button (`lines 31-37`) with:
  ```blade
  @if(\App\Support\Context::hasPermission('approvals.manage'))
      <button type="button" @click="openCreateModal()" ...>...</button>
  @endif
  ```
- **R4.2**: Wrap "Buat Aturan Pertama" empty state button (`lines 132-139`) with `@if(\App\Support\Context::hasPermission('approvals.manage'))`.
- **R4.3**: Consolidate header block (`lines 11-39`) into standard `<x-module-header>`.

#### 5. `resources/views/app/billing/limits.blade.php`
- **R5.1**: In file manager modal template (`lines 2451-2458` and `2480-2487`), guard delete file button with Alpine condition `x-show="canManage"` where `canManage: {{ \App\Support\Context::hasPermission('billing.manage') ? 'true' : 'false' }}`.
- **R5.2**: Update line 1401 storage top-up icon from `plus` to `hard-drive` or `database` per Semantic Icon Registry.

#### 6. `resources/views/app/billing/{checkout, payment, history, invoice}.blade.php`
- Retain current structure as benchmarks for responsive layout, double-submit protection, adaptive polling, Snap Slider, and A4 print lock.

---

## 5. Verification Method

To independently verify the observations, conclusions, and remediation requirements:

### 1. Syntax Verification
Run PHP linter across all 9 Blade files:
```powershell
Get-ChildItem -Recurse resources/views/app/analytics, resources/views/app/approvals, resources/views/app/billing -File | ForEach-Object { php -l $_.FullName }
```
*Expected*: `No syntax errors detected` across all 9 files.

### 2. Automated Feature Test Suites
Run the verified test suites for the 3 modules:
```powershell
php artisan test tests/Feature/AnalyticsWebTest.php
php artisan test tests/Feature/ApprovalModuleAuditTest.php
php artisan test tests/Feature/BillingModuleAuditTest.php
```
*Verified Result*:
- `AnalyticsWebTest`: 9 passed (37 assertions)
- `ApprovalModuleAuditTest`: 7 passed (29 assertions)
- `BillingModuleAuditTest`: 8 passed (19 assertions)
- **Total: 24 tests passed, 0 failures, 0 errors**.

### 3. RBAC View Guard Audit Verification
Inspect Blade files for unguarded buttons:
```powershell
Select-String -Path "resources\views\app\approvals\inbox.blade.php" -Pattern "openApproveModal|openRejectModal" -Context 3,3
Select-String -Path "resources\views\app\approvals\rules.blade.php" -Pattern "openCreateModal" -Context 3,3
Select-String -Path "resources\views\app\analytics\index.blade.php" -Pattern "reports\.index" -Context 2,2
```
*Expected*: Confirm lines 132-146 in `inbox.blade.php`, lines 31-37 and 132-139 in `rules.blade.php`, and lines 281 and 452 in `analytics/index.blade.php` lack permission wrappers.

### 4. Header Redundancy Verification
Check topbar `$headerTitle` usage vs in-view `<h1>` tags:
```powershell
Select-String -Path "resources\views\app\analytics\index.blade.php", "resources\views\app\approvals\*.blade.php" -Pattern "<h1"
```
*Expected*: Multiple `<h1>` tags are found in `analytics/index.blade.php`, `approvals/inbox.blade.php`, `approvals/history.blade.php`, and `approvals/rules.blade.php`.
