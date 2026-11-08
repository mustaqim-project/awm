## 2026-11-07T14:33:23Z
You are the UI Simplification & Responsive Layout Specialist Worker for COOCA.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui
Read ORIGINAL_REQUEST.md at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read PROJECT.md at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Read the survey report at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_ui_survey\survey_report.md

Read the skill files:
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\responsive-ui-ux\SKILL.md
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-layout-hierarchy-reorganizer\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP (You own ONLY these files; do not touch other files):
- resources/views/app/community/events.blade.php
- resources/views/app/community/index.blade.php
- resources/views/app/community/event_payment.blade.php
- resources/views/app/crm/members.blade.php
- resources/views/app/crm/vouchers.blade.php
- resources/views/app/customers/index.blade.php

TASK OBJECTIVES:
1. Community Events (`resources/views/app/community/events.blade.php`):
   - Eliminate Double Header: Remove or simplify the redundant inner banner `<h1>` card that repeats the sticky topbar `headerTitle`. Standardize the breadcrumb with canonical navigation.
   - Action row ergonomics: In "Tiket & Pendaftaran Saya", separate the primary button ("Buka Tiket Digital") from the destructive "Batal" action with proper visual hierarchy, padding, and confirmation safeguard.
   - Ensure tap targets are >= 44px on mobile (375px).
2. Community Index (`resources/views/app/community/index.blade.php`):
   - Fix mobile DOM ordering: On mobile viewports (375px), ensure community feed content is prominently visible without having to scroll past the entire left sidebar (profile, composer button, quota widget). Use responsive order classes (e.g. `order-2 lg:order-1` or collapsible mobile profile summary).
   - Ensure feed action icons (Reply, Repost, Like, Share) and carousel controls have >= 44px tap targets.
   - Preserve all RBAC `@can` guards previously established in M1.
3. CRM Members (`resources/views/app/crm/members.blade.php`):
   - Fix header consistency: Standardize `headerTitle` in `@extends('layouts.app', ...)` so the topbar displays proper localized title.
   - Mobile KPI Snap Slider: Convert the 4 vertically stacking Bento KPI cards on mobile (375px) to a responsive horizontal swipe snap slider (`flex sm:grid sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto sm:overflow-x-visible snap-x snap-mandatory no-scrollbar pb-2 sm:pb-0`), where each card has `min-w-[260px] sm:min-w-0 snap-start flex-shrink-0 sm:flex-shrink`.
   - Ensure customer card mini metrics and table action buttons have clean hierarchy and >= 44px tap targets.
4. CRM Vouchers (`resources/views/app/crm/vouchers.blade.php`):
   - Eliminate Triple "Buat Voucher" redundancy: Retain the primary `<x-module-header>` slot action button; streamline or differentiate secondary in-page callouts so there aren't 3 identical buttons in 100 vertical pixels.
   - Mobile KPI Snap Slider: Convert the 4 Bento KPI tiles to a responsive horizontal snap slider on mobile viewports.
5. Customers Index (`resources/views/app/customers/index.blade.php`):
   - Fix translation domain leakage: Replace 6 `purchasing.*` translation key references (`purchasing.terjadi_kesalahan...`, `purchasing.unduh_template_xlsx`, etc.) with proper canonical `customers.*` keys.
   - Ensure table action buttons, modal form controls, and filter bar have clear hierarchy, zero overlap, and >= 44px touch targets.
   - Preserve all RBAC `@can` guards established in M1.
6. Verification:
   - Run syntax validation (`php -l`) on all 6 files.
   - Run view compilation checks / render tests:
     `php artisan test --filter=Customer`
     `php artisan test --filter=Crm`
     `php artisan test --filter=Community`

Write your handoff report to:
c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\handoff.md

Report back via send_message when complete.


## 2026-11-08T19:17:30Z
You are a frontend UI/UX implementation subagent (teamwork_worker).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the project plan and UI/RBAC survey reports at:
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_ui\handoff.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_rbac\handoff.md`
- Relevant skills: `C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md`, `C:\Users\Ulfah Ramadhani\.gemini\config\skills\responsive-ui-ux\SKILL.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\`

Update your `progress.md` in your working directory as you complete each step.

EXCLUSIVE FILE OWNERSHIP (You may modify ONLY these files):
- `resources/views/app/hrm/payroll/create.blade.php`
- `resources/views/app/hrm/payroll/payslip.blade.php`
- `resources/views/app/hrm/partials/tab-corrections.blade.php`
- `resources/views/app/hrm/partials/tab-locations.blade.php`
- `resources/views/app/hrm/partials/tab-schedules.blade.php`
- `resources/views/app/hrm/partials/tab-attendance.blade.php`
- `resources/views/app/hrm/index.blade.php`
- `resources/views/app/inventory/stocks.blade.php`
- `resources/views/app/inventory/opname.blade.php`
- `resources/views/app/inventory/transfers.blade.php`
- `resources/views/app/import/index.blade.php`

Tasks (Features F5, F6, F7, F8, F9, F10, F11):
1. **Mobile Bottom Navigation Clearing (P1)**:
   - `resources/views/app/hrm/payroll/create.blade.php`: Change floating calculation bar from `bottom-4` to `bottom-[calc(68px+env(safe-area-inset-bottom))] lg:bottom-4` so it floats safely above the mobile bottom navigation bar (`#bottom-nav`).
   - `resources/views/app/inventory/stocks.blade.php`, `resources/views/app/inventory/opname.blade.php`, `resources/views/app/inventory/transfers.blade.php`: Change container padding from `pb-12` to `pb-28 lg:pb-12` (matching `movements.blade.php`) so pagination and cards are never clipped by the mobile bottom navigation bar.
2. **Button De-duplication in HRM Tabs (P1)**:
   - In `resources/views/app/hrm/partials/tab-corrections.blade.php`: Remove duplicate CTA button in the card header (lines 16–20) because `hrm/index.blade.php` already renders the unified tab CTA button for `activeTab === 'corrections'`.
   - In `resources/views/app/hrm/partials/tab-locations.blade.php`: Remove duplicate CTA button in the card header (lines 16–20) because `hrm/index.blade.php` already renders the unified tab CTA button for `activeTab === 'locations'`.
3. **Button Hierarchy & Single Primary CTA (P2)**:
   - In `resources/views/app/hrm/partials/tab-schedules.blade.php`: Restyle filter submit button (line 38) from solid indigo `bg-[#5856D6]` to neutral/tonal (`bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white font-semibold`), preserving solid indigo solely for the primary "Atur Jadwal Roster" action.
   - In `resources/views/app/hrm/partials/tab-attendance.blade.php`: Remove `pt-5` on the filter button wrapper and align with input fields cleanly on mobile multiline wrap.
4. **Touch Target & Button Sizing (P2)**:
   - In `resources/views/app/hrm/payroll/payslip.blade.php`: Upgrade action buttons from `h-10 sm:h-9 min-h-[40px]` to `min-h-[44px] h-11 px-4`.
   - In `resources/views/app/import/index.blade.php`: Upgrade sub-44px buttons across domain cards (`h-7` and `h-8`) to `min-h-[40px] sm:min-h-[32px] px-3.5`, and segmented tab buttons to `min-h-[44px] sm:min-h-[36px]`.
   - In `resources/views/app/inventory/stocks.blade.php` and `transfers.blade.php`: Upgrade table actions from `w-9 h-9 min-h-[36px]` to `w-10 h-10 min-w-[40px] min-h-[40px] sm:w-9 sm:h-9`.
5. **Empty State in Import Hub (P2)**:
   - In `resources/views/app/import/index.blade.php`: Add empty state row inside preview tables when `filteredRows.length === 0` to prevent a blank void on filtering.
6. **View-Level Authorization Guards (F5)**:
   - Wrap sensitive buttons in `inventory/stocks.blade.php` (Import Stok, Quick Adjust), `opname.blade.php` (Import Opname, Tambah Opname, Rekonsiliasi), and `transfers.blade.php` (Transfer Antar Lokasi, Konfirmasi Terima) in `@if(\App\Support\Context::hasPermission('inventory.manage'))`.
   - Wrap sensitive buttons in `hrm/index.blade.php` and partials (Tambah Lokasi, Tambah Shift, Atur Jadwal Roster, Buat Payroll Baru, Catat Kasbon, Review Koreksi) in the corresponding `Context::hasPermission(...)` checks.
   - Wrap execute import buttons in `import/index.blade.php` in the corresponding manage permissions (`materials.manage`, `products.manage`, `inventory.manage`).
7. **Verification**:
   - Execute `php artisan view:cache` to confirm 100% clean Blade compilation without syntax errors.


## 2026-11-08T19:32:18Z
**Context**: Milestone M2 UI Simplification across HRM, Import, and Inventory.
**Content**: Status check on your UI modifications, button de-duplication, touch targets, and view guards.
**Action**: Please report your progress on the assigned view files, update `progress.md`, and deliver `handoff.md` once complete.


## 2026-11-08T19:39:45Z
**Context**: Milestone M2 UI Simplification
**Content**: Checking in on `import/index.blade.php` and `hrm/index.blade.php` completion status.
**Action**: Please report if you encountered any blocking issues or if you are running `php artisan view:cache`.
