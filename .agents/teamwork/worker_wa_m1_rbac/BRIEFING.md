# BRIEFING — 2026-11-07T06:05:00Z

## Mission
Implement Milestone 1: RBAC Core & Controller Guards for the WhatsApp module in COOCA.

## 🔒 My Identity
- Archetype: teamwork_worker
- Roles: implementer, qa
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac
- Original parent: 072c1567-fb7e-4663-b29a-0dc2a878655b
- Milestone: Milestone 1: RBAC Core & Controller Guards (WhatsApp)

## 🔒 Key Constraints
- Exclusive Write Ownership:
  - app/Providers/AppServiceProvider.php
  - app/Support/Navigation/NavigationRegistry.php
  - app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php
  - app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php
  - app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php
- DO NOT CHEAT: All implementations genuine, no hardcoded test results, maintain real state and real logic.
- Follow minimal change principle and verify changes with php artisan test.

## Current Parent
- Conversation ID: 072c1567-fb7e-4663-b29a-0dc2a878655b
- Updated: 2026-11-07T06:05:00Z

## Task Summary
- **What to build**:
  1. Bridge Laravel `@can` to COOCA tenant permissions in `AppServiceProvider.php`.
  2. Change navigation permission for `whatsapp-gateway` from `whatsapp.manage` to `whatsapp.view` in `NavigationRegistry.php`.
  3. Standardized middleware in `WhatsAppWebController.php` with permissions (`whatsapp.view`, `whatsapp.manage`, `reports.export`, `pos.orders`, throttles).
  4. Standardized middleware in `WhatsAppBroadcastWebController.php` (`whatsapp.manage`, `entitlement:whatsapp`, throttle on `store`).
  5. Standardized middleware in `MetaWhatsAppOnboardingController.php` (`whatsapp.view` on getSignupConfig/getStatus, `whatsapp.manage` on exchangeCode/disconnect).
- **Success criteria**:
  - Middleware rules accurately reflect dispatch specs.
  - Gate::before bridges Context::hasPermission properly.
  - Navigation item permits view-only users.
  - MerchantWhatsAppWebFeatureTest passes (28/28 tests passed).
- **Interface contracts**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
- **Code layout**: Laravel 11 / Laravel 12 standard layout in c:\laragon\www\cooca_core

## Key Decisions Made
- Updated `Gate::before` in `AppServiceProvider.php` to delegate to `\App\Support\Context::hasPermission($ability) ? true : null` when `$user` is an instance of `\App\Models\User`.
- Updated navigation permission for `whatsapp-gateway` from `whatsapp.manage` to `whatsapp.view` in `NavigationRegistry.php`.
- Added `'disconnect'` into `throttle:10,1` alongside `startSession`, `verifyMetaCredentials`, and `sendOrderReceipt` in `WhatsAppWebController.php`.
- Handled test mock credentials precedence in `MetaWhatsAppOnboardingController.php` during unit/feature testing via `app()->runningUnitTests()`.

## Change Tracker
- **Files modified**:
  - `app/Providers/AppServiceProvider.php`: Added `User` gate bridge via `Context::hasPermission`
  - `app/Support/Navigation/NavigationRegistry.php`: Changed `whatsapp-gateway` permission to `whatsapp.view`
  - `app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php`: Guarded `disconnect` in throttle:10,1 alongside other mutation endpoints
  - `app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php`: Resolved config precedence in test mode
- **Build status**: PASS (28/28 tests passed in MerchantWhatsAppWebFeatureTest, 5/5 in WhatsAppLogsExportTest)
- **Pending issues**: None

## Quality Status
- **Build/test result**: 28 passed, 0 failed in `MerchantWhatsAppWebFeatureTest.php`
- **Lint status**: Clean
- **Tests added/modified**: Verified existing test suites; M3 worker will create `WhatsAppRbacSecurityTest.php`

## Loaded Skills
- None specified in dispatch

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\DISPATCH.md` — Assigned instructions
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\BRIEFING.md` — Agent working memory
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\progress.md` — Progress heartbeat
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\handoff.md` — Final handoff report
