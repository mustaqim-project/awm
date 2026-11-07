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
