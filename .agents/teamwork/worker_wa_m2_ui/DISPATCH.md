## 2026-11-07T06:06:08Z
You are a teamwork_worker implementing Milestone 2: WhatsApp Views UI Simplification & RBAC for the WhatsApp module.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m2_ui
Your identity: worker_wa_m2_ui

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY: Read c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md and c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md before starting your work.
Study the detailed UI investigation and exact refactoring diffs in:
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_ui\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\handoff.md

Exclusive Write Ownership (Files you are allowed to modify):
- resources/views/app/whatsapp/index.blade.php
- resources/views/app/whatsapp/broadcast.blade.php
- resources/views/app/whatsapp/broadcast_detail.blade.php
- resources/views/app/whatsapp/logs.blade.php

Detailed Tasks to Implement:
1. In `resources/views/app/whatsapp/index.blade.php`:
   - Header actions: Demote "Kotak Masuk" (`route('whatsapp.inbox.index')`) from solid blue `#007AFF` to tonal secondary (`bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80`); replace `@if (\App\Support\Context::hasPermission('pos.terminal'))` with native `@can('pos.terminal')`.
   - Connected account card: Remove the duplicated "Buka Kotak Masuk" button from the card footer. Wrap the "Putuskan Koneksi" (Disconnect) button in `@can('whatsapp.manage') ... @endcan`.
   - Embedded Signup Connect button: Wrap in `@can('whatsapp.manage') ... @endcan`.
   - Service active toggle (`is_active`): Wrap with `@can('whatsapp.manage')`; when `@cannot('whatsapp.manage')`, add `disabled` attribute, `cursor-not-allowed`, and opacity styling so read-only users cannot toggle the merchant's service.
   - Auto-receipt form: Elevate the Save button to solid blue `#007AFF` with check icon and shadow; wrap submit button with `@can('whatsapp.manage')`; when `@cannot('whatsapp.manage')`, make the textarea `disabled` with `cursor-not-allowed`.
   - Test send console: Wrap test send button with `@can('whatsapp.manage')`; disable phone and message inputs for users who cannot manage.
   - Meta Catalog Blast buttons and card template links: Wrap with `@can('whatsapp.manage')`.
   - Ensure all touch targets maintain minimum 44×44px (`min-h-[44px]`).

2. In `resources/views/app/whatsapp/broadcast.blade.php`:
   - Desktop table eye action button (around line 219): Upgrade touch target from 32×32px (`w-8 h-8`) to 44×44px (`min-w-[44px] min-h-[44px] w-9 h-9 rounded-[10px]`).
   - Standardize native `@can('whatsapp.manage')` on:
     - Header "Buat Siaran Baru" button.
     - Empty state "Mulai Broadcast" button.
     - Modal Composer Submit Blast button.
   - Preserve all accessibility attributes: `role="dialog"`, `aria-modal="true"`, `aria-labelledby="broadcastModalTitle"`, `aria-label="{{ __('whatsapp.close_btn') }}"`.

3. In `resources/views/app/whatsapp/broadcast_detail.blade.php`:
   - Header action button "Buat Siaran Baru": Wrap with `@can('whatsapp.manage') ... @endcan`.
   - "Buat Siaran Serupa" CTA: Wrap with `@can('whatsapp.manage') ... @endcan`.
   - Preserve `min-h-[44px]` and `min-w-[44px]` touch targets.

4. In `resources/views/app/whatsapp/logs.blade.php`:
   - Header actions:
     - Wrap "Ekspor Excel" in `@can('reports.export') ... @endcan` (and `@can('whatsapp.view')`).
     - Wrap "Bersihkan Storage" (Pruning) button in `@can('whatsapp.manage') ... @endcan`.
   - Desktop table eye action button (around line 202): Upgrade touch target from 32×32px (`w-8 h-8`) to 44×44px (`min-w-[44px] min-h-[44px] w-9 h-9 rounded-[10px]`).
   - Storage Pruning modal: Wrap modal template and trigger with `@can('whatsapp.manage') ... @endcan`.
   - Preserve all accessibility attributes: `role="dialog"`, `aria-modal="true"`, `aria-labelledby="inspectorModalTitle"`, `aria-labelledby="pruneModalTitle"`.

5. Verification:
   - Run `php artisan view:cache` to ensure all Blade views compile with zero syntax errors, then `php artisan view:clear`.
   - Run `php artisan test tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php` to verify existing tests continue to pass with 28/28 passed.
   - Run `php artisan test tests/Feature/WhatsApp/WhatsAppLogsExportTest.php` to verify 5/5 passed.

6. Report:
   - Update `progress.md` with your status.
   - Deliver handoff report in `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m2_ui\handoff.md`.
   - Send completion message to parent when done.
