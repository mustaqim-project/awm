## 2026-11-07T05:43:58Z
You are a teamwork_worker implementing Milestone 1: RBAC Core & Controller Guards for the WhatsApp module.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac
Your identity: worker_wa_m1_rbac

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY: Read c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md and c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md before starting your work.
Also study the explorer reports at:
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_rbac\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_test\handoff.md

Exclusive Write Ownership (Files you are allowed to modify):
- app/Providers/AppServiceProvider.php
- app/Support/Navigation/NavigationRegistry.php
- app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php
- app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php
- app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php

Tasks to Implement:
1. In `app/Providers/AppServiceProvider.php`:
   Update `Gate::before` so that when `$user` is an instance of `\App\Models\User`, it delegates to `\App\Support\Context::hasPermission($ability) ? true : null`. This bridges Laravel's native `@can` directive to COOCA's tenant permission system.
2. In `app/Support/Navigation/NavigationRegistry.php`:
   Update line ~495 for `whatsapp-gateway` item: change `'permission' => 'whatsapp.manage'` to `'permission' => 'whatsapp.view'`. This allows view-only users to see the WhatsApp Gateway menu.
3. In `app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php`:
   Ensure `HasMiddleware` properly maps:
   - `require.permission:whatsapp.view` on `['index', 'getQr', 'checkStatus', 'logs']`
   - `require.permission:whatsapp.manage` on `['startSession', 'disconnect', 'updateSettings', 'testSend', 'verifyMetaCredentials']`
   - `require.permission:whatsapp.view`, `require.permission:reports.export`, and `entitlement:export` on `['exportLogs']`
   - `require.permission:pos.orders` on `['sendOrderReceipt']`
   - appropriate rate throttling (`throttle:5,1` on `testSend`, `throttle:10,1` on others)
4. In `app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php`:
   Ensure `require.permission:whatsapp.manage` guards all actions, plus `entitlement:whatsapp` and `throttle:10,1` on `store`.
5. In `app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php`:
   Ensure `require.permission:whatsapp.view` guards `['getSignupConfig', 'getStatus']`, and `require.permission:whatsapp.manage` guards `['exchangeCode', 'disconnect']`.
6. Run verification:
   - Remember to run `php artisan config:clear` before tests to honor SQLite `:memory:`.
   - Run tests: `php artisan test tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php` to verify existing tests pass without regression.
7. Document all changes and verification outputs in `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\handoff.md`.
8. Update `progress.md` with your steps.
9. Send a message to your parent when done.
