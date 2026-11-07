## 2026-11-07T07:15:23Z
You are Worker M1 (RBAC Implementation Worker).
Your Working Directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m1
Your parent is: e2aae656-229b-42bb-a7be-8273bdd8a984

MANDATORY INSTRUCTIONS:
1. You MUST read the Original Request file first:
   c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. Read the Project Plan & Architecture:
   c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
3. Read Explorer 2's detailed handoff report:
   c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\handoff.md
4. Relevant skill to consult:
   C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE FILE OWNERSHIP (You own and may edit ONLY these files):
- database/seeders/RbacSeeder.php
- app/Http/Controllers/Web/Ai/AiCompanyWebController.php
- app/Http/Controllers/Web/Ai/PosAiWebController.php
- routes/owner.php

TASKS TO IMPLEMENT:
1. `database/seeders/RbacSeeder.php`:
   - In `permissionMatrix()`: Add `ai.approve` and `ai.manage` under the AI module group with appropriate titles, group, and descriptions (see Explorer 2 handoff Section 4.1).
   - In `roleDefinitions()`: Assign `ai.approve` to `manager` and `admin`; assign `ai.manage` to `admin` (`owner` automatically receives all slugs). Ensure `cashier`, `warehouse`, etc. retain `ai.access`.
2. `app/Http/Controllers/Web/Ai/AiCompanyWebController.php`:
   - Implement `Illuminate\Routing\Controllers\HasMiddleware`.
   - Add `public static function middleware(): array` with granular method protection:
     - `require.permission:ai.access` for standard viewing and chat/office methods (`index`, `lobby`, `executiveOffice`, `operationsOffice`, `growthOffice`, `history`, `actions`, `liveMetrics`, `getAgentAvatars`, `getRules`, `ask`, `triggerDailyCheck`, `teamwork`, `goal`, `grillMe`, `boost`, `schedule`).
     - `require.permission:ai.approve` for proposal actions (`approveAction`, `reviseAction`, `rejectAction`).
     - `require.permission:ai.manage` for provider config and rules (`providers`, `storeProvider`, `testProvider`, `detectModels`, `updateAgentAvatar`, `storeRule`, `toggleRule`, `deleteRule`).
3. `app/Http/Controllers/Web/Ai/PosAiWebController.php`:
   - Implement `HasMiddleware`.
   - Add `public static function middleware(): array`:
     - `require.permission:ai.access` for `index`, `ask`.
     - `require.permission:ai.approve` for `executeAction`.
4. `routes/owner.php`:
   - Clean routing: remove route-level `require.permission:ai.access` from POS AI (`routes/owner.php:632-636`), `cooca-ai` prefix (`routes/owner.php:638-672`), and legacy `ai` prefix (`routes/owner.php:674-702`), while keeping `entitlement:ai`.

VERIFICATION & TESTING:
Run commands and document results:
1. `php -l database/seeders/RbacSeeder.php`
2. `php -l app/Http/Controllers/Web/Ai/AiCompanyWebController.php`
3. `php -l app/Http/Controllers/Web/Ai/PosAiWebController.php`
4. `php -l routes/owner.php`
5. `php artisan db:seed --class=RbacSeeder --force`
6. `php artisan route:list --name=cooca-ai` and `php artisan route:list --name=pos.ai`
7. `php artisan test tests/Feature/AiCompanyWorkflowTest.php tests/Feature/PosAiEngineTest.php`

OUTPUT:
Write your full handoff report to `c:\laragon\www\cooca_core\.agents\teamwork\worker_m1\handoff.md`.
Include modified files, exact changes made, verification commands and verbatim outputs.
Send a message to parent when done.
