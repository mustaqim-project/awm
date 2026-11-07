# BRIEFING — 2026-11-07T07:08:00Z

## Mission
Thoroughly inspect all routes, controllers, middleware, and authorization/RBAC structures governing AI and AI Studio modules in COOCA SaaS ERP to prepare for Controller-Level RBAC and view-level authorization enforcement.

## 🔒 My Identity
- Archetype: explorer
- Roles: Mobile Codebase Explorer; Explorer 2 (Controllers, Routes & RBAC)
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2
- Original parent: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Current parent: e2aae656-229b-42bb-a7be-8273bdd8a984
- Milestone: AI & AI Studio RBAC, Routes & Controller Survey

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- Mobile codebase target is c:\laragon\www\cooca_my_own (from prior assignment)
- Read-only investigation: do NOT modify any source code files. Write only to .agents/teamwork/explorer_survey_2/
- Keep progress.md updated with "Last visited: [timestamp]"
- Deliver findings to handoff.md in working directory
- Send message to caller when complete

## Current Parent
- Conversation ID: e2aae656-229b-42bb-a7be-8273bdd8a984
- Updated: 2026-11-07T07:08:00Z

## Investigation State
- **Explored paths**:
  - `routes/owner.php` (lines 625-745, lines 996-1005)
  - `routes/admin.php` (lines 95-145)
  - `app/Http/Controllers/Web/Ai/AiCompanyWebController.php` (29 public methods)
  - `app/Http/Controllers/Web/Ai/PosAiWebController.php` (4 public methods)
  - `app/Http/Controllers/Web/AiStudio/OwnerAiStudioWebController.php` (17 public methods)
  - `app/Http/Controllers/Web/AiStudio/OwnerInspirationWebController.php` (15 public methods)
  - `app/Http/Controllers/Admin/AdminAi*.php` & `AdminInspirationManagementController.php`
  - `app/Domain/Ai/Policy/AiActionPolicy.php`
  - `app/Http/Requests/Ai/ExecuteAiPosActionRequest.php`
  - `database/seeders/RbacSeeder.php`
  - `resources/views/app/ai/` (13 blade files)
  - `resources/views/app/ai-studio/` (11 blade files)
  - `resources/views/layouts/partials/sidebar.blade.php`
  - `tests/Feature/AiStudio/AiStudioWebControllerTest.php` (22/22 passed)
  - `tests/Feature/AiDigitalCompanyTest.php`
- **Key findings**:
  - `OwnerAiStudioWebController` and `OwnerInspirationWebController` already implement `HasMiddleware` with permissions `ai_studio.view`, `ai_studio.generate`, `ai_studio.manage`.
  - `AiCompanyWebController` and `PosAiWebController` do NOT implement `HasMiddleware` and rely solely on route-level `require.permission:ai.access`.
  - Sensitive endpoints like `storeProvider`, `testProvider`, `detectModels` (which manage external API keys), `storeRule`, `toggleRule`, `deleteRule`, and action proposal approvals lack granular Controller-Level RBAC.
  - Phantom permission `ai.approve` is actively used in `AiActionPolicy.php` and `ExecuteAiPosActionRequest.php`, but is NOT registered in `RbacSeeder.php`.
  - Missing permission `ai.manage` is needed for AI Provider API keys and Guardrails.
  - 29 of 31 blade views lack view-level authorization wrapping (`@can` / `Context::hasPermission`) on action buttons, modals, and forms.
  - Multi-tenant IDOR protection is solid across controllers (`where('business_id', $business->id)->findOrFail(...)`).
- **Unexplored areas**: None. Comprehensive survey completed.

## Key Decisions Made
- Roadmap will prescribe implementing `HasMiddleware` on `AiCompanyWebController` and `PosAiWebController`.
- Register `ai.approve` and `ai.manage` in `RbacSeeder.php`.
- Provide exact file-by-file changes and Blade wrapping matrices.

## Artifact Index
- DISPATCH.md — Initial dispatch message
- BRIEFING.md — Persistent context & identity
- progress.md — Liveness & status tracking
- inspect_ai_rbac.py — Script checking HasMiddleware and methods
- find_ai_permissions.py — Script scanning permission slugs across codebase
- inspect_views_auth.py — Script scanning views for auth guards and actions
- map_view_routes.py — Script mapping routes invoked by each view
- handoff.md — 5-component handoff report
