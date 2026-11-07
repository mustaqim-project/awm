# Project: COOCA SaaS ERP — AI & AI Studio Production Enhancement

## Architecture
- **Framework**: Laravel 11.x on PHP 8.2+, Alpine.js, Tailwind CSS, Lucide Icons, Three.js WebGL canvas.
- **Modules**:
  - `COOCA AI / Digital Company` (`app/Http/Controllers/Web/Ai/`, `resources/views/app/ai/`, `app/Domain/Ai/`)
  - `POS AI Assistant` (`app/Http/Controllers/Web/Ai/PosAiWebController.php`, `resources/views/app/ai/index.blade.php`)
  - `AI Studio & Viral Inspiration` (`app/Http/Controllers/Web/AiStudio/`, `resources/views/app/ai-studio/`, `app/Domain/AiStudio/`)
  - `Documentation System` (`app/Http/Controllers/Web/DocumentationWebController.php`, `resources/views/app/docs/`)
- **Authorization & Security**: Spatie / Native multi-tenant RBAC with `Context::hasPermission()`, `Context::isOwner()`, `Context::isAdminOrOwner()`, Controller-Level `HasMiddleware`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | RBAC Seeder Registration | Register `ai.approve` and `ai.manage` permissions in `RbacSeeder.php` and assign to roles | M1 | Survey Explorer 2 [DONE] |
| 2 | Controller-Level RBAC in AiCompanyWebController | Implement `HasMiddleware` with `ai.access`, `ai.approve`, `ai.manage` | M1 | Survey Explorer 2 [DONE] |
| 3 | Controller-Level RBAC in PosAiWebController | Implement `HasMiddleware` with `ai.access`, `ai.approve` | M1 | Survey Explorer 2 [DONE] |
| 4 | Clean Routing Migration | Remove route-level `require.permission:ai.access` from `routes/owner.php` | M1 | Survey Explorer 2 [DONE] |
| 5 | UI Simplification: Header CTA Consolidation | Enforce single primary action in `lobby.blade.php`, `ai-studio/index.blade.php`, `ai-studio/show.blade.php` | M2 | Survey Explorer 1 |
| 6 | Mobile Overlap Remediation | Fix toast position in `providers.blade.php`, HUD in `agent_inspector_hud.blade.php`, canvas overlays in `virtual_office_canvas.blade.php` | M2 | Survey Explorer 1 |
| 7 | Modal z-Index & Teleportation | Standardize modals to `z-[200]` and wrap with `<template x-teleport="body">` in `topup-modal.blade.php`, `collections.blade.php`, `my-library.blade.php`, `index.blade.php` | M2 | Survey Explorer 1 |
| 8 | Touch Target & Ergonomics | Enforce >= 44x44px touch targets on cards and swipeable snap slider on `ai/index.blade.php` quick prompts; remove `+` text prefix | M2 | Survey Explorer 1 |
| 9 | Consultation Modal Fix | Fix syntax error on line 101 (`-->`), dual Light/Dark mode classes, distinct user vs AI message bubbles | M2 | Survey Explorer 1 |
| 10 | Polling Optimization | Refactor `ai-studio/show.blade.php` polling to use lightweight status endpoint with 60-attempt timeout | M2 | Survey Explorer 3 |
| 11 | View-Level Authorization Protection | Wrap action buttons and forms in `ai/` and `ai-studio/` views with `@if(Context::hasPermission(...))` | M2 | Survey Explorer 2 |
| 12 | Business Owner AI Studio Docs | Create `resources/views/app/docs/partials/sec_ai_studio.blade.php` with 20-industry matrix and verified route links | M3 | Survey Explorer 3 |
| 13 | Docs Registration & TOC Sidebar | Include `sec_ai_studio.blade.php` in `docs/index.blade.php` and add `#sec-ai-studio` to Left Sidebar TOC | M3 | Survey Explorer 3 |
| 14 | Resilient AI Fallback Engine | Catch provider connection/auth errors in `AiOrchestrator` and fallback gracefully to `RuleBasedFallbackProvider` | M4 | Survey Explorer 3 |
| 15 | AI Digital Company Test Fix | Fix unmocked HTTP call in `AiDigitalCompanyTest.php:148` | M4 | Survey Explorer 3 |
| 16 | E2E Regression & Quality Verification | Run full test suite (`php artisan test`) and syntax check (`php -l`) across all modified files | M4 | Survey Explorer 3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | M1: RBAC & Controller Security | `RbacSeeder.php`, `AiCompanyWebController.php`, `PosAiWebController.php`, `routes/owner.php` | none | DONE |
| 2 | M2: UI Simplification & View Auth | Blade views in `resources/views/app/ai/` and `resources/views/app/ai-studio/` | M1 | IN_PROGRESS |
| 3 | M3: Business Owner Documentation | `resources/views/app/docs/partials/sec_ai_studio.blade.php`, `resources/views/app/docs/index.blade.php` | none | PLANNED |
| 4 | M4: Resilience, Test Fix & Module Audit | `AiOrchestrator.php`, `AiDigitalCompanyTest.php`, full test execution | M1, M2, M3 | PLANNED |

## Milestone 1 Outputs
- `database/seeders/RbacSeeder.php`: registered `ai.access`, `ai.approve`, `ai.manage`; synced to owner (114), manager (88), admin (93).
- `app/Http/Controllers/Web/Ai/AiCompanyWebController.php`: implements `HasMiddleware`, 28 methods protected.
- `app/Http/Controllers/Web/Ai/PosAiWebController.php`: implements `HasMiddleware`, 3 methods protected.
- `routes/owner.php`: clean routing achieved.
- Verification: 100% tests pass (18 stress tests + 17 legacy route tests + 14 workflow tests + 22 AI studio tests). Gate verdict: PASS.

## Interface Contracts
### Controller RBAC ↔ Middleware
- `AiCompanyWebController` implements `HasMiddleware`:
  - `ai.access`: `index`, `lobby`, `executiveOffice`, `operationsOffice`, `growthOffice`, `history`, `actions`, `liveMetrics`, `getAgentAvatars`, `getRules`, `ask`, `triggerDailyCheck`, `teamwork`, `goal`, `grillMe`, `boost`, `schedule`
  - `ai.approve`: `approveAction`, `reviseAction`, `rejectAction`
  - `ai.manage`: `providers`, `storeProvider`, `testProvider`, `detectModels`, `updateAgentAvatar`, `storeRule`, `toggleRule`, `deleteRule`
- `PosAiWebController` implements `HasMiddleware`:
  - `ai.access`: `index`, `ask`
  - `ai.approve`: `executeAction`

### View Directives ↔ Permissions (Milestone M2)
- Actions approval: `\App\Support\Context::hasPermission('ai.approve') || \App\Support\Context::isAdminOrOwner()`
- Provider config & rules: `\App\Support\Context::hasPermission('ai.manage') || \App\Support\Context::isAdminOrOwner()`
- AI Studio generation: `\App\Support\Context::hasPermission('ai_studio.generate') || \App\Support\Context::isOwner()`
- AI Studio management (export, delete, collections): `\App\Support\Context::hasPermission('ai_studio.manage') || \App\Support\Context::isOwner()`

## Code Layout
- Controllers:
  - `app/Http/Controllers/Web/Ai/AiCompanyWebController.php`
  - `app/Http/Controllers/Web/Ai/PosAiWebController.php`
- Seeders:
  - `database/seeders/RbacSeeder.php`
- Routes:
  - `routes/owner.php`
- Views:
  - `resources/views/app/ai/`
  - `resources/views/app/ai-studio/`
  - `resources/views/app/docs/`
- Domain & Tests:
  - `app/Domain/Ai/Orchestration/AiOrchestrator.php`
  - `tests/Feature/AiDigitalCompanyTest.php`
