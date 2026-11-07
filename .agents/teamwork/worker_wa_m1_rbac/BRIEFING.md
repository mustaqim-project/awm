# BRIEFING — 2026-11-07T05:45:00Z

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
- Updated: 2026-11-07T05:45:00Z

## Task Summary
- **What to build**:
  1. Bridge Laravel `@can` to COOCA tenant permissions in `AppServiceProvider.php`.
  2. Change navigation permission for `whatsapp-gateway` from `whatsapp.manage` to `whatsapp.view` in `NavigationRegistry.php`.
  3. Update/verify middleware in `WhatsAppWebController.php` with proper permissions (`whatsapp.view`, `whatsapp.manage`, `reports.export`, `pos.orders`, throttles).
  4. Update/verify middleware in `WhatsAppBroadcastWebController.php` (`whatsapp.manage`, `entitlement:whatsapp`, throttle on `store`).
  5. Update/verify middleware in `MetaWhatsAppOnboardingController.php` (`whatsapp.view` on getSignupConfig/getStatus, `whatsapp.manage` on exchangeCode/disconnect).
- **Success criteria**:
  - Middleware rules accurately reflect dispatch specs.
  - Gate::before bridges Context::hasPermission properly.
  - Navigation item permits view-only users.
  - MerchantWhatsAppWebFeatureTest passes.
- **Interface contracts**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
- **Code layout**: Laravel 11 / Laravel 12 standard layout in c:\laragon\www\cooca_core

## Key Decisions Made
- [Pending investigation]

## Change Tracker
- **Files modified**: None yet
- **Build status**: Pending
- **Pending issues**: None

## Quality Status
- **Build/test result**: Not yet executed
- **Lint status**: Not yet evaluated
- **Tests added/modified**: Existing tests to run; M3 worker will add new test suite

## Loaded Skills
- None specified in dispatch

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\DISPATCH.md` — Assigned instructions
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\BRIEFING.md` — Agent working memory
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\progress.md` — Progress heartbeat
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_wa_m1_rbac\handoff.md` — Final handoff report
