# UI/UX Architectural Survey & Audit Report: Management, Settings, Finance, Reports, CRM, HR & Analytics Views

**Agent**: Explorer Survey 3  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3`  
**Target Codebase Scope**: `resources/views/app/` (Back-Office, Management, Analytics, Governance, and Operational Modules)  
**Evaluation Standards**: Bento Apple HIG v2.0 Design System, Multi-Industry Zero Feature Leakage, UI Simplify Layout & Action Proximity, Reports & Multi-Domain Dashboard Blueprint.

---

## 1. Observation

A full static and structural survey was conducted across 61 Blade view files (totaling 51,084 lines of code) spanning 19 submodules and root files under `resources/views/app/`.

### 1.1 Complete Inventory of Surveyed Back-Office Views

| Domain / Category | Submodules / Files | File Count | Total Lines | Architectural Role |
|---|---|:---:|:---:|---|
| **Dashboards & Analytics** | `dashboard.blade.php`, `calculator.blade.php`, `analytics/index.blade.php`, `reports/index.blade.php`, `profitability/index.blade.php`, `simulator/index.blade.php` | 6 | 5,584 | Operational Cockpit, Business Intelligence, BEP Costing, Financial Statement Reports |
| **Finance & Accounting** | `finance/expenses.blade.php`, `finance/journals.blade.php`, `finance/payables.blade.php`, `finance/receivables.blade.php`, `finance/accounting/*` (5 files), `finance/cash-bank/*` (2 files), `finance/edc-terminals/index.blade.php`, `finance/external-reconciliation/index.blade.php`, `finance/payout-accounts/index.blade.php`, `finance/settlements/*` (2 files), `invoices/*` (5 files), `tax/index.blade.php`, `billing/*` (5 files) | 27 | 18,347 | General Ledger, Cash/Bank Accounts, AP/AR, Tax Simulators, Invoicing, Billing & Limits |
| **CRM, Members & Feedback** | `customers/index.blade.php`, `crm/members.blade.php`, `crm/vouchers.blade.php`, `feedback/index.blade.php`, `feedback/create.blade.php`, `feedback/show.blade.php`, `community/index.blade.php`, `community/events.blade.php`, `community/event_payment.blade.php` | 9 | 5,409 | Customer Directory, Loyalty Points & Vouchers, Product Feedback Tickets, Community Hub |
| **HRM & Workforce** | `hrm/index.blade.php`, `hrm/payroll/create.blade.php`, `hrm/payroll/payslip.blade.php`, `hrm/payroll/show.blade.php`, `labor-machines/index.blade.php` | 5 | 8,621 | Employee Profiles, GPS Geofence & Biometrics, Shifts/Rosters, Payroll, Labor & Machine Rates |
| **Settings & Governance** | `settings/index.blade.php`, `settings/roles.blade.php`, `settings/integrations/mcp.blade.php`, `settings/integrations/social_media.blade.php`, `settings/integrations/whatsapp.blade.php`, `profile/edit.blade.php`, `profile/verify-contact.blade.php`, `roles/index.blade.php`, `security/audit-logs/index.blade.php`, `security/audit-logs/show.blade.php`, `approvals/history.blade.php`, `approvals/inbox.blade.php`, `approvals/rules.blade.php`, `master-data/index.blade.php` | 14 | 13,123 | Multi-Outlet Branches, RBAC Roles & Permissions, Append-Only Audit Trail, Approval Rules, MCP Integrations |
| **TOTAL SURVEYED** | **19 Submodules & Root Files** | **61** | **51,084** | **Complete Back-Office & Management Ecosystem** |

---

### 1.2 Direct Code Observations & Verbatim Snippets

#### Observation O1: Monolithic View Bloat with 15 Inline Modals and 10 Tables
- **File**: `resources/views/app/hrm/index.blade.php` (5,705 lines)
- **Direct observation**: Contains 10 data tables (Lines 1049, 1604, 1828, 2013, 2197, 2432, 2607, 2807, 5380, 5620) and 15 complete modal dialogues spanning lines 2988 to 5498 (over 2,510 continuous lines of modal markup without a single partial template):
  - Line 2988: `showFaceRegisterModal` (Face registration)
  - Line 3134: `showFaceAttendanceModal` (Face attendance)
  - Line 3261: `showAddLocationModal` (Add geofence location)
  - Line 3401: `showEditLocationModal` (Edit geofence location)
  - Line 3543: `showAddEmployeeModal` (Add employee)
  - Line 3871: `showEditEmployeeModal` (Edit employee)
  - Line 4193: `showAddLoanModal` (Add employee cash loan / kasbon)
  - Line 4304: `showAddCorrectionModal` (Add attendance correction)
  - Line 4433: `showReviewCorrectionModal` (Review correction approval)
  - Line 4612: `showAddShiftModal` (Add shift)
  - Line 4755: `showEditShiftModal` (Edit shift)
  - Line 4899: `showAddScheduleModal` (Add roster/schedule)
  - Line 5064: `showEditScheduleModal` (Edit roster/schedule)
  - Line 5229: `showImportEmployeeModal` (Import employee CSV/Excel)
  - Line 5465: `showImportAttendanceModal` (Import attendance CSV/Excel)

#### Observation O2: Massive Inline JavaScript within Blade Templates
- **File**: `resources/views/app/settings/index.blade.php` (4,143 lines)
  - Direct observation: Lines 2838 to 4143 (1,306 lines) consist entirely of raw JavaScript within `<script>` tags rather than modular Alpine scripts or external assets.
- **File**: `resources/views/app/settings/integrations/mcp.blade.php` (3,363 lines)
  - Direct observation: Lines 127 to 537 contain 411 lines of inline JavaScript defining `mcpSettingsHub()`, while lines 538 to 3364 contain 2,826 lines of complex permissions tables and token generation sheets.

#### Observation O3: Complete Absence of Bento Apple HIG in Community Views
- **Files**: `resources/views/app/community/index.blade.php` (1,279 lines), `community/events.blade.php` (638 lines), `community/event_payment.blade.php` (295 lines)
- **Direct observation**: 98 total instances of legacy Tailwind border radius (`rounded-2xl`, `rounded-xl`) and 50 instances of legacy borders (`border-slate-200/80`, `border-gray-200`). Zero instances of Bento Apple HIG hairline borders (`border-black/[0.06] dark:border-white/[0.08]`) or squircle cards (`rounded-[20px]`).
- Example at `community/index.blade.php:179`:
  ```html
  <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 shadow-2xs">
  ```
- Example at `community/index.blade.php:229`:
  ```html
  <span id="unread-notif-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">
  ```
  *(Violates Apple HIG ban on `animate-pulse` on static text counters).*

#### Observation O4: Responsive Table Horizontal Clipping (Missing `overflow-x-auto`)
- **File**: `resources/views/app/finance/accounting/balance-sheet.blade.php`
  - Lines 436-438:
    ```html
    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
        <div class="max-h-[380px] overflow-y-auto">
            <table class="w-full text-left text-[12px] border-collapse">
    ```
  - Direct observation: Table has `overflow-y-auto` but lacks `overflow-x-auto`. On screens < 400px, multi-column accounting rows clip horizontally without ability to scroll.
- **File**: `resources/views/app/security/audit-logs/show.blade.php`
  - Lines 83-84:
    ```html
    <div class="rounded-[18px] border border-black/5 dark:border-white/10 overflow-hidden">
        <table class="w-full text-left text-[13.5px]">
    ```
  - Direct observation: 3-column forensic diff table is wrapped in `overflow-hidden` instead of `overflow-x-auto`.
- **File**: `resources/views/app/customers/index.blade.php`
  - Lines 1427-1429:
    ```html
    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
        <div class="max-h-[380px] overflow-y-auto">
            <table class="w-full text-left text-[12px] border-collapse">
    ```
  - Direct observation: Import preview modal table has `overflow-y-auto` only; columns overflow and clip horizontally.

#### Observation O5: Header Action Overcrowding (Button Clutter)
- **File**: `resources/views/app/finance/cash-bank/index.blade.php`
  - Lines 20-47: 5 individual action buttons rendered side-by-side inside `<x-module-header>`:
    1. Line 20: `button @click="showAccountModal = true"` (`btn_add_account`)
    2. Line 25: `button @click="showInflowModal = true"` (`btn_cash_inflow` - green)
    3. Line 30: `button @click="showOutflowModal = true"` (`btn_cash_outflow` - red)
    4. Line 35: `button @click="showTransferModal = true"` (`btn_cash_transfer` - blue)
    5. Line 42: `a href="{{ route('finance.cash-bank.ledger') }}"` (`btn_cash_ledger`)
  - Direct observation: Header wraps into 3 rows on tablet and mobile viewports, displacing title and violating single-primary CTA discipline.

#### Observation O6: Pill & Badge Inflation in Approvals Inbox
- **File**: `resources/views/app/approvals/inbox.blade.php`
  - Lines 41-73: Filter tabs are wrapped in `rounded-full` pills, each containing an internal `rounded-full` count badge.
  - Lines 92-97:
    ```html
    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] uppercase tracking-wider">
        {{ $ticket->getDocumentTypeLabel() }}
    </span>
    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#FF9500]">
        {{ __('approvals.waiting_level', ['current' => $ticket->current_level, 'total' => $ticket->total_levels]) }}
    </span>
    ```
  - Direct observation: 3-4 pill badges rendered side-by-side in ticket cards, causing visual noise and pill fatigue.

#### Observation O7: Raw Unicode Emojis and Symbols
- **File**: `resources/views/app/master-data/index.blade.php`
  - Lines 740, 801, 945, 1010: Raw unicode `✕` (`\u2715`) used for modal close and clear buttons:
    ```html
    <button type="button" @click="showAddModal = false"
        class="w-7 h-7 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">✕</button>
    ```
  - Direct observation: 28x28px (`w-7 h-7`) size violates Apple HIG minimum 44x44px touch target; literal unicode symbol used instead of Lucide icon `<i data-lucide="x">`.
- **File**: `resources/views/app/community/index.blade.php`: Line 521 uses literal unicode star `★` (`\u2605`).
- **File**: `resources/views/app/billing/invoice.blade.php`: Line 891 uses literal unicode checkmark `✓` (`\u2713`).

#### Observation O8: Hardcoded UI Text in Core Reports and Profiles
- **File**: `resources/views/app/reports/index.blade.php`:
  - Line 609: `<h3>Analisis Umur Hutang Vendor (AP Aging)</h3>`
  - Line 597: `Tidak ada piutang invoice yang belum lunas.`
  - Lines 625-630: `No. Tagihan Vendor`, `Supplier / Vendor`, `Tgl Tagihan`, `Jatuh Tempo`, `Keterlambatan`, `Sisa Hutang`
  - Line 650: `Belum Jatuh Tempo`
  - Lines 139, 149: `Export Excel`, `Cetak`
- **File**: `resources/views/app/profile/edit.blade.php`:
  - Lines 3-4: `'headerTitle' => 'Pengaturan Akun & Keamanan'`, `'headerSubtitle' => 'Kelola informasi...'`
  - Lines 41-42: `Alamat email Anda belum diverifikasi`, `Cek kotak masuk...`
  - Line 76: `Terdapat beberapa kesalahan validasi:`
- **File**: `resources/views/app/labor-machines/index.blade.php`:
  - Lines 1-4: `'title' => 'Tenaga Kerja & Mesin'`, `'headerTitle' => 'Biaya Tenaga Kerja & Mesin'`
  - Lines 118-134: `Dashboard`, `Biaya Produksi`, `Tenaga Kerja & Mesin`, `Konversi tarif upah...`
  - Lines 143, 148: `Tambah Tarif Upah`, `Tambah Mesin Baru`

---

## 2. Logic Chain

1. **Premise 1 (Maintainability & Performance)**: `hrm/index.blade.php` (5,705 LOC), `settings/index.blade.php` (4,143 LOC), and `mcp.blade.php` (3,363 LOC) embed massive data tables, modals, and JavaScript directly into single view files (Observation O1, O2).
   - *Inference*: Compiling and rendering multi-thousand line Blade views containing 10+ unpaginated modal DOM subtrees imposes severe DOM weight, slows initial render, increases memory usage during compilation, and makes isolated code modifications extremely risky. Modals should be decoupled into modular Blade components (`@include` or `<x-*>`).

2. **Premise 2 (Design System Alignment)**: The COOCA Bento Apple HIG v2.0 specification mandates consistent continuous squircle cards (`rounded-[20px]`/`rounded-[24px]`), hairline borders (`border-black/[0.06] dark:border-white/[0.08]`), and dark surfaces (`#1C1C1E`).
   - *Inference*: `community/*` and `tax/index.blade.php` completely bypass these standards with legacy `rounded-2xl` and `border-slate-200/80` or `rounded-3xl` with dark buttons (Observation O3). This produces an inconsistent visual experience where community and tax screens feel like a third-party app bolted onto COOCA.

3. **Premise 3 (Mobile Touch & Responsive Usability)**: On mobile viewports (360px–390px), data tables wider than 360px will break out of card boundaries or truncate unless wrapped in a dedicated horizontal scroll container (`overflow-x-auto`).
   - *Inference*: `finance/accounting/balance-sheet.blade.php`, `security/audit-logs/show.blade.php`, and `customers/index.blade.php` wrap tables in `overflow-y-auto` or `overflow-hidden` without `overflow-x-auto` (Observation O4). Users accessing the balance sheet or audit diffs on mobile cannot view all financial columns, breaking usability for mobile business owners.

4. **Premise 4 (Action Hierarchy & Cognitive Load)**: Apple HIG guidelines and the *UI Simplify Layout* skill dictate that every screen must have exactly one primary action, with secondary actions grouped into consolidated menus.
   - *Inference*: `finance/cash-bank/index.blade.php` renders 5 action buttons in the header (Observation O5). This visual competition causes analysis paralysis and causes buttons to wrap awkwardly on mobile devices. Adopting the Dashboard's `quickMenuOpen` popover pattern solves this cleanly.

5. **Premise 5 (Multi-Language Parity & Icon Consistency)**: Zero Hardcoded Text is mandatory for 100% bilingual parity (ID/EN), and Unicode emojis/symbols in interactive controls are strictly prohibited in favor of Lucide SVGs (WCAG accessible touch targets $\ge 44\times44\text{px}$).
   - *Inference*: `master-data/index.blade.php` uses `✕` with a 28x28px touch target (Observation O7), while `reports/index.blade.php`, `profile/edit.blade.php`, and `labor-machines/index.blade.php` have hardcoded Indonesian strings (Observation O8). When English users navigate these screens, they encounter untranslated Indonesian text and broken touch targets.

---

## 3. Caveats

1. **Read-Only Investigation**: All observations were gathered via static code inspection and AST analysis without modifying any codebase files in `resources/views/app/` or database schemas.
2. **Browser Layout Rendering vs. Blade Markup**: Certain CSS classes (such as `overflow-x-auto` on higher-level parents like `<x-app-layout>`) may provide partial horizontal scrolling, but internal `overflow-hidden` on immediate parent divs will still clip tables locally.
3. **No Code Modification Undertaken**: In strict compliance with the dispatch directive, no Blade files or stylesheets were edited during this survey.

---

## 4. Conclusion: Ranked Findings & Actionable Remediation Plan

### 4.1 Detailed Findings Matrix

| Finding ID | View File Path | Issue Description | Severity | Concrete Improvement Recommendation |
|---|---|---|:---:|---|
| **F-01** | `resources/views/app/hrm/index.blade.php` | Monolithic 5,705-line view embedding 10 full tables and 15 complete modal dialogues inline. | **CRITICAL** | Decompose the 15 modals into dedicated partials in `resources/views/app/hrm/partials/` (e.g., `modal-add-employee.blade.php`, `modal-face-registration.blade.php`) and lazy-load table tabs. |
| **F-02** | `resources/views/app/settings/index.blade.php` | Over 1,300 lines of inline JavaScript in `<script>` tags across 7 tabs. | **HIGH** | Extract inline JS into external modular Alpine component `public/js/app/settings-hub.js` or Alpine store. |
| **F-03** | `resources/views/app/settings/integrations/mcp.blade.php` | 3,236 lines of inline JS and dense permissions matrices in a single view file; uses `bg-neutral-900 dark:bg-white` instead of Bento Primary Blue. | **HIGH** | Refactor permissions matrix into dedicated partials; standardize action buttons to Apple Primary Blue (`bg-[#007AFF] text-white`). |
| **F-04** | `resources/views/app/community/index.blade.php`, `events.blade.php`, `event_payment.blade.php` | 100% legacy Tailwind styling (`rounded-2xl`, `border-slate-200/80`, `shadow-2xs`), completely missing Bento Apple HIG continuous squircle tokens; fake pulse dot on notification counter. | **HIGH** | Migrate community views to Bento Apple HIG v2.0: replace `rounded-2xl` with `rounded-[20px]`, `border-slate-200` with hairline `border-black/[0.06] dark:border-white/[0.08]`; remove `animate-pulse` from notification badge. |
| **F-05** | `resources/views/app/finance/accounting/balance-sheet.blade.php` (L:437), `security/audit-logs/show.blade.php` (L:83), `customers/index.blade.php` (L:1428) | Financial and forensic diff tables wrapped in `overflow-y-auto` or `overflow-hidden` without `overflow-x-auto`. | **HIGH** | Add `overflow-x-auto` to table wrapper divs to prevent column clipping on mobile screens (< 400px). |
| **F-06** | `resources/views/app/tax/index.blade.php` | 80 instances of legacy `rounded-3xl`/`rounded-2xl`; black primary CTA button clashing with system blue; lacks tabular-nums on summary numbers. | **HIGH** | Standardize container cards to `rounded-[20px]`, form controls to `rounded-[12px]`, CTA buttons to `bg-[#007AFF] text-white`, and add `tabular-nums` to all monetary outputs. |
| **F-07** | `resources/views/app/finance/cash-bank/index.blade.php` (L:20-47) | Header action overcrowding: 5 full-size action buttons rendered side-by-side in `<x-module-header>`. | **MEDIUM** | Consolidate secondary actions (`Catat Kas Masuk`, `Catat Kas Keluar`, `Transfer Kas`) into a single "Aksi Cepat Kas" popover menu with `quickMenuOpen`, keeping only `Tambah Akun` as primary CTA. |
| **F-08** | `resources/views/app/approvals/inbox.blade.php` (L:41-73, 92-97) | Pill & Badge Inflation: nested `rounded-full` pills in filter tabs, and 3-4 pill badges side-by-side per ticket card. | **MEDIUM** | Convert filter tabs to Apple macOS segmented control (`rounded-[10px]` bar); replace ticket type badge with pure typographic overline and retain only 1 status badge per card. |
| **F-09** | `resources/views/app/billing/limits.blade.php` | Inconsistent dark mode surface: mixes Tailwind Slate (`dark:bg-slate-900`, `dark:border-slate-800`) with iOS dark palette. | **MEDIUM** | Replace `dark:bg-slate-900` with `dark:bg-[#1C1C1E]` and `dark:border-slate-800` with `dark:border-white/[0.08]`. |
| **F-10** | `resources/views/app/master-data/index.blade.php` (L:740, 801, 945, 1010) | Raw unicode `✕` (`\u2715`) with undersized 28x28px (`w-7 h-7`) touch targets. | **MEDIUM** | Replace `✕` with `<i data-lucide="x" class="w-4 h-4"></i>` and ensure button container meets minimum 44x44px touch target (`min-h-[44px] min-w-[44px] sm:min-h-[32px] sm:min-w-[32px]`). |
| **F-11** | `resources/views/app/reports/index.blade.php` (L:85, 139, 149, 597, 609, 625-650) | Hardcoded Indonesian strings in Aging tables, export buttons, and empty states. | **MEDIUM** | Wrap all strings with `__()` and register missing keys in `lang/id/analytics.php` and `lang/en/analytics.php`. |
| **F-12** | `resources/views/app/labor-machines/index.blade.php` (L:1-5, 118-148), `profile/edit.blade.php` (L:3-5, 41-76) | Hardcoded Indonesian strings in page headers, breadcrumbs, and error validation summaries. | **MEDIUM** | Migrate all hardcoded texts to localization helper `__()`. |
| **F-13** | `resources/views/app/simulator/index.blade.php` (L:18-54), `reports/index.blade.php` (L:122, 136, 146, 164), `profile/edit.blade.php` (L:37, 54, 63, 73, 96) | Inconsistent icon implementation: raw inline `<svg>` elements mixed with Lucide icons. | **LOW** | Replace raw inline `<svg>` tags with standard `<i data-lucide="...">` icons for consistent stroke weight and rendering. |
| **F-14** | `resources/views/app/hrm/index.blade.php` (Zero Industry Gating) | Complete lack of multi-industry adaptability in a 5,705-line workforce view. | **LOW** | Integrate industry-specific workforce tabs using `$business->template_code` (e.g. shift rosters for F&B/Retail vs project billable hours for Agency/Service). |

---

### 4.2 Multi-Industry Adaptability & Isolation Evaluation

1. **Exemplary Implementations (Gold Standard)**:
   - `resources/views/app/calculator.blade.php`: Features adaptive presets (`applyPreset()`) customized for Workshop (`oli`, `tuneup`, `rem`, `servis_berkala`), Laundry (`kiloan`, `bedcover`, `dryclean`, `sepatu`), and F&B/Retail (`kopi`, `makanan`, `roti`). Gating correctly switches between material and labor rates.
   - `resources/views/app/customers/index.blade.php`: Automotive vehicle fields (`vehicle_license_plate`, `vehicle_model`, `vehicle_mileage`) in Add Modal, Edit Modal, and Customer Detail sheets are strictly gated via `@if($isWorkshop)`, guaranteeing Zero Feature Leakage into Retail, F&B, or Agensi tenants.
   - `resources/views/app/dashboard.blade.php`: HPP benchmarks dynamically adapt via `$defaultHpp = match(true) { isWorkshop => 45k, isRetail => 35k, isPharmacy => 25k, isLaundry => 5k, default => 15k }`.

2. **Gaps & Improvement Areas**:
   - `resources/views/app/hrm/index.blade.php`: Presents identical HR workflows regardless of whether the business is a small coffee shop (1-3 baristas), an auto garage (mechanics with commission), or a factory (hourly machine labor). Adding context-aware presets would dramatically simplify the UI for micro-businesses.
   - `resources/views/app/labor-machines/index.blade.php`: Currently separated from core costing and HRM. For manufacturing and workshop tenants, machine rates should be cross-linked directly within product recipe BOMs and service orders.

---

## 5. Verification Method

To independently verify all findings and statistics documented in this report:

1. **Inventory & View Count Verification**:
   ```bash
   python -c "import os; views = r'c:\laragon\www\cooca_core\resources\views\app'; target = ['analytics','finance','reports','profitability','crm','customers','hrm','labor-machines','settings','profile','roles','security','approvals','master-data','tax','billing','feedback','community','simulator']; print('Target files:', len([os.path.join(r, f) for d in target for r, dirs, files in os.walk(os.path.join(views, d)) for f in files if f.endswith('.blade.php')] + [os.path.join(views, 'dashboard.blade.php'), os.path.join(views, 'calculator.blade.php')]))"
   ```
   *Expected result*: Exactly 61 files matching the inventory.

2. **Inspect HRM Monolithic View & Inline Modals**:
   ```bash
   python -c "with open(r'c:\laragon\www\cooca_core\resources\views\app\hrm\index.blade.php', 'r', encoding='utf-8') as f: lines = f.readlines(); print('Total lines:', len(lines)); print('Fixed inset-0 modals:', sum(1 for l in lines if 'class=\"fixed inset-0 z-[200] overflow-y-auto\"' in l)); print('Tables:', sum(1 for l in lines if '<table' in l))"
   ```
   *Expected result*: 5,705 lines, 15 modals, 10 tables.

3. **Verify Table Overflow Missing in Balance Sheet & Audit Logs**:
   ```bash
   python -c "for fp in [r'c:\laragon\www\cooca_core\resources\views\app\finance\accounting\balance-sheet.blade.php', r'c:\laragon\www\cooca_core\resources\views\app\security\audit-logs\show.blade.php']: 
       with open(fp, 'r', encoding='utf-8') as f: 
           c = f.read(); print(fp.split('\\\\')[-1], 'has overflow-x-auto:', 'overflow-x-auto' in c)"
   ```
   *Expected result*: Both return `False`.

4. **Verify Unicode Emojis and Symbols**:
   ```bash
   python -c "for fp in [r'c:\laragon\www\cooca_core\resources\views\app\master-data\index.blade.php', r'c:\laragon\www\cooca_core\resources\views\app\community\index.blade.php']:
       with open(fp, 'r', encoding='utf-8') as f:
           for i, l in enumerate(f, 1):
               if any(s in l for s in ['\u2715', '\u2605']): print(f'{fp.split(chr(92))[-1]}:{i} -> {l.strip()[:60]}')"
   ```
   *Expected result*: Lines 740, 801, 945, 1010 in master-data with `✕` and Line 521 in community with `★`.

5. **Invalidation Conditions**:
   - If `hrm/index.blade.php` is decomposed into partials under 1,000 LOC, Finding F-01 is invalidated.
   - If `overflow-x-auto` is added to lines 437 in `balance-sheet.blade.php` and line 83 in `security/audit-logs/show.blade.php`, Finding F-05 is invalidated.
   - If `community/*` templates are refactored to use Bento tokens `rounded-[20px]` and `border-black/[0.06] dark:border-white/[0.08]`, Finding F-04 is invalidated.
