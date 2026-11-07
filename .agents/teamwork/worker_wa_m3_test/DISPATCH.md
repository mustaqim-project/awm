## 2026-11-07T06:40:06Z
You are a teamwork_worker implementing Milestone 3: Test Verification Suite for the WhatsApp module.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m3_test
Your identity: worker_wa_m3_test

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY: Read c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md and c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md before starting your work.
Study the detailed test plan, template, and findings in:
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_test\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m2_ui\handoff.md

Exclusive Write Ownership (Files you are allowed to modify/create):
- tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php

Tasks to Implement:
1. Create `tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php` covering the full RBAC verification matrix:
   - Unauthenticated guest: redirected to login on protected routes.
   - Unauthorized user (`cashier`/`staff` without `whatsapp.view` or `whatsapp.manage`):
     - Receives HTTP 403 Forbidden on JSON endpoints (`getJson`, `postJson`) across `whatsapp.index`, `whatsapp.meta.config`, `whatsapp.logs.index`, `whatsapp.test`, `whatsapp.disconnect`, `whatsapp.broadcast.store`.
     - Redirected to `portal` on standard web GET requests.
   - Read-only user (`viewer` with `whatsapp.view` only):
     - Can view `whatsapp.index` and `whatsapp.logs.index` (HTTP 200 OK).
     - Receives HTTP 403 Forbidden when attempting manage actions (`whatsapp.test`, `whatsapp.disconnect`, `whatsapp.broadcast.store`, `whatsapp.meta.disconnect`).
     - Rendered HTML hides restricted actions (e.g. `assertDontSee(__('whatsapp.disconnect_btn'))`, `assertDontSee(__('whatsapp.storage_pruning_btn'))`).
   - Privileged user (`owner` / `manager` with `whatsapp.manage`):
     - Can view all pages and see all action buttons (HTTP 200 OK).
     - Can execute mutating actions successfully.
2. Run test execution:
   - Clear config cache: `php artisan config:clear`
   - Run new security test: `php artisan test tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php`
   - Run existing feature tests: `php artisan test tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php`
   - Run logs export test: `php artisan test tests/Feature/WhatsApp/WhatsAppLogsExportTest.php`
   - Verify Blade compilation: `php artisan view:cache` followed by `php artisan view:clear`
3. Document all execution commands and outputs in `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m3_test\handoff.md`.
4. Update `progress.md` with your steps.
5. Send completion message to parent when done.
