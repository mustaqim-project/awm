# Empirical Challenge Report: Milestone M1 — RBAC & Controller Security

**Challenger**: Challenger M1 (Empirical Security Challenger)  
**Parent**: `e2aae656-229b-42bb-a7be-8273bdd8a984`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\challenger_m1_1`  
**Target Worker**: Worker M1  
**Verdict**: **APPROVE**  
**Date**: 2026-11-07  

---

## 1. Observation

### 1.1 Implementation Artifacts Audited
The implementation provided by Worker M1 across 4 files was directly inspected:
1. `database/seeders/RbacSeeder.php`:
   - Lines 179-181: Granular permissions `ai.access`, `ai.approve`, and `ai.manage` declared in `permissionMatrix()`.
   - Line 290: `'ai.approve'` assigned to `$managerSlugs`.
   - Line 310: `'ai.manage'` assigned to `'admin'` role definition.
   - Lines 330, 348: `'ai.access'` retained on `'cashier'` and `'warehouse'` roles without leaking `ai.approve` or `ai.manage`.
   - Line 300: `$ownerSlugs = $allSlugs;` ensuring `owner` retains all permissions.

2. `app/Http/Controllers/Web/Ai/AiCompanyWebController.php`:
   - Lines 38-39 & 43: Implements `Illuminate\Routing\Controllers\HasMiddleware`.
   - Lines 48-86: Granular `public static function middleware(): array`:
     - `require.permission:ai.access` on 17 read/chat methods (`index`, `lobby`, `executiveOffice`, `operationsOffice`, `growthOffice`, `history`, `actions`, `liveMetrics`, `getAgentAvatars`, `getRules`, `ask`, `triggerDailyCheck`, `teamwork`, `goal`, `grillMe`, `boost`, `schedule`).
     - `require.permission:ai.approve` on 3 proposal action methods (`approveAction`, `reviseAction`, `rejectAction`).
     - `require.permission:ai.manage` on 8 provider and rule mutation methods (`providers`, `storeProvider`, `testProvider`, `detectModels`, `updateAgentAvatar`, `storeRule`, `toggleRule`, `deleteRule`).

3. `app/Http/Controllers/Web/Ai/PosAiWebController.php`:
   - Lines 12-13 & 17: Implements `HasMiddleware`.
   - Lines 22-33: Granular `middleware()`:
     - `require.permission:ai.access` on `index`, `ask`.
     - `require.permission:ai.approve` on `executeAction`.

4. `routes/owner.php`:
   - Lines 632-635, 637, 673: Removed outer `require.permission:ai.access` group wrappers while strictly maintaining tenant entitlement `entitlement:ai`.

### 1.2 Empirical Stress-Test Execution
To independently prove the authorization boundaries, an 18-scenario adversarial test suite was authored and executed in `tests/Feature/AiRbacEmpiricalStressTest.php`.

#### Test Suite Command & Verbatim Output
Command executed:
```bash
php artisan test tests/Feature/AiRbacEmpiricalStressTest.php
```
Result:
```json
{"tool":"phpunit","result":"passed","tests":18,"passed":18,"assertions":60,"duration_ms":39624}
```

Full AI Feature Suite Command & Verbatim Output:
```bash
php artisan test tests/Feature/AiCompanyWorkflowTest.php tests/Feature/PosAiEngineTest.php tests/Feature/AiRbacEmpiricalStressTest.php
```
Result:
```json
{"tool":"phpunit","result":"passed","tests":32,"passed":32,"assertions":130,"duration_ms":41865}
```

### 1.3 Verbatim Empirical Results per Challenge Dimension

| # | Adversarial Test Scenario | Role Tested | Endpoint | Expected Result | Actual Observed Result | Status |
|---|---------------------------|-------------|----------|-----------------|------------------------|--------|
| 1 | Action Proposal Approval | Cashier (no `ai.approve`) | `POST /cooca-ai/actions/{proposal}/approve` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`, proposal remains PENDING) | PASS |
| 2 | Action Proposal Approval | Warehouse (no `ai.approve`) | `POST /cooca-ai/actions/{proposal}/approve` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`, proposal remains PENDING) | PASS |
| 3 | Action Proposal Revision & Rejection | Cashier (no `ai.approve`) | `POST /cooca-ai/actions/{proposal}/revise`, `reject` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`) | PASS |
| 4 | POS AI Action Execution (with `pos.orders` & `invoices.create`) | Cashier (no `ai.approve`) | `POST /pos/ai/execute-action` | HTTP 403 Forbidden | `403 Forbidden` (Intercepted before FormRequest `authorize()`) | PASS |
| 5 | POS AI Purchase Order Action Execution | Warehouse (no `ai.approve`) | `POST /pos/ai/execute-action` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`) | PASS |
| 6 | AI Provider Storage / Modification | Manager (no `ai.manage`) | `POST /cooca-ai/providers` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`, `required_permissions: ['ai.manage']`) | PASS |
| 7 | AI Guardrail Rule Creation, Toggle & Deletion | Manager (no `ai.manage`) | `POST /cooca-ai/rules`, `PATCH .../toggle`, `DELETE ...` | HTTP 403 Forbidden | `403 Forbidden` across all 3 rule management methods | PASS |
| 8 | AI Provider Page Access | Manager (no `ai.manage`) | `GET /cooca-ai/providers` | HTTP 403 Forbidden | `403 Forbidden` (`FORBIDDEN_PERMISSION`) | PASS |
| 9 | AI Provider & Rule Modification | Cashier (no `ai.manage`) | `POST /cooca-ai/providers`, `POST /cooca-ai/rules` | HTTP 403 Forbidden | `403 Forbidden` on both endpoints | PASS |
| 10 | Authorized Action Proposal Revision & Rejection | Manager (has `ai.approve`) | `POST /cooca-ai/actions/{proposal}/revise`, `reject` | HTTP 200 OK | `200 OK` (Payload updated, proposal successfully rejected) | PASS |
| 11 | Authorized AI Guardrail Rule Creation | Admin (has `ai.manage`) | `POST /cooca-ai/rules` | HTTP 201 Created | `201 Created` (`ai_guardrails` table updated in database) | PASS |
| 12 | Full Owner Management Access | Owner (all permissions) | `GET /cooca-ai/providers`, `POST /cooca-ai/rules` | HTTP 200/201 OK | `200 OK` on providers, `201 Created` on rules | PASS |
| 13 | Permitted Viewing & Chat Access | Cashier (has `ai.access`) | `GET /cooca-ai`, `GET /pos/ai` | HTTP 200 OK | `200 OK` on both lobby and POS Cockpit | PASS |
| 14 | Base Access Gating | Viewer (no `ai.access`) | `GET /cooca-ai`, `GET /pos/ai` | HTTP 403 Forbidden | `403 Forbidden` (`required_permissions: ['ai.access']`) | PASS |
| 15 | Unauthenticated Guest Protection | Guest (unauthenticated) | `POST /cooca-ai/actions/{proposal}/approve` | HTTP 401/302 Redirect | `401 Unauthorized` / `302 Redirect` to login | PASS |
| 16 | Cross-Tenant Isolation | Owner of Tenant B | `POST /cooca-ai/actions/{proposal_A}/approve` | HTTP 404 / 403 | `404 Not Found` (Tenant barrier enforced) | PASS |
| 17 | Legacy Route Controller Enforcement | Cashier / Manager | `POST /ai/actions/{proposal}/approve`, `POST /ai/providers` | HTTP 403 Forbidden | `403 Forbidden` (Controller middleware protects legacy fallbacks) | PASS |
| 18 | Web Browser Form Redirection | Cashier (web request) | `GET /cooca-ai/providers` | HTTP 302 Redirect | `302 Redirect` to `route('portal')` with session error flash | PASS |

---

## 2. Logic Chain

1. **Premise 1 — Former Phantom Permission Gap**:
   Prior to M1, `AiActionPolicy.php:68` checked `Context::hasPermission('ai.approve')`, but `ai.approve` did not exist in `RbacSeeder.php`. Furthermore, `ExecuteAiPosActionRequest.php:21` fell back to `pos.orders` or `invoices.create`, allowing regular cashiers to trigger automated AI actions draft execution.

2. **Premise 2 — Controller Middleware Precedence**:
   In Laravel 11, `HasMiddleware` executes before FormRequest authorization. By applying `new Middleware('require.permission:ai.approve', only: ['executeAction'])` on `PosAiWebController`, any incoming request from a user without `ai.approve` is intercepted by `RequirePermission` and rejected with `403 Forbidden`. Test #4 empirically proved this: a Cashier equipped with `pos.orders` and `invoices.create` was strictly rejected with 403 Forbidden when calling `POST /pos/ai/execute-action`.

3. **Premise 3 — Provider & Guardrail Secret Protection**:
   Configuring third-party AI keys (`OPENAI_API_KEY`, Gemini, Anthropic) and company guardrails represents critical operational security. By binding `require.permission:ai.manage` to all 8 provider and rule mutation methods in `AiCompanyWebController`, managers and cashiers are entirely locked out (Tests #6, #7, #8, #9). Only `admin` and `owner` can mutate AI providers and rules (Tests #11, #12).

4. **Premise 4 — Clean Architecture & Route Freedom**:
   By stripping `require.permission:ai.access` from `routes/owner.php`, routes are decoupled from authorization concerns while maintaining `entitlement:ai`. The controller remains the single source of truth for its own method permissions, eliminating duplicate middleware checks.

5. **Premise 5 — Zero Regressions**:
   All 14 existing feature tests in `AiCompanyWorkflowTest.php` and `PosAiEngineTest.php` continue to pass without a single failure or regression. Combined with the 18 new adversarial tests, the AI suite totals 32 passing tests and 130 assertions.

---

## 3. Caveats

- **View Directives Scope**:
  This challenge verified backend controller-level RBAC enforcement. Hiding action buttons, forms, and tabs in Blade views (e.g. `@if(Context::hasPermission('ai.approve') || Context::isAdminOrOwner())`) is the explicit scope of Milestone M2. However, because backend controller middleware is active and verified, unauthorized UI tampering or direct POST requests will reliably receive `403 Forbidden`.
- **In-Memory SQLite Testing**:
  Automated tests run against SQLite `:memory:` with `RbacSeeder`. The seeder was also confirmed to execute cleanly on MySQL/MariaDB in Worker M1's report (`php artisan db:seed --class=RbacSeeder --force`).

---

## 4. Conclusion

**Verdict: APPROVE**

Worker M1's implementation is verified as complete, robust, and empirically sound:
1. `ai.approve` and `ai.manage` permissions are officially registered and seeded.
2. `AiCompanyWebController` and `PosAiWebController` correctly enforce controller-level RBAC via `HasMiddleware`.
3. Unauthorized roles (Cashier, Warehouse, Manager without `ai.manage`) are strictly blocked with `403 Forbidden` from proposal execution and provider configuration.
4. Authorized roles (Owner, Admin, Manager with `ai.approve`) perform their permitted operations without error.
5. All 18 adversarial tests and 14 regression tests pass with 100% success.

---

## 5. Verification Method

Any team member or orchestrator can independently re-verify this assessment with the following commands:

```bash
# 1. Run the comprehensive adversarial stress test suite:
php artisan test tests/Feature/AiRbacEmpiricalStressTest.php

# 2. Run the combined AI module feature test suite:
php artisan test tests/Feature/AiCompanyWorkflowTest.php tests/Feature/PosAiEngineTest.php tests/Feature/AiRbacEmpiricalStressTest.php

# 3. Verify route middleware bindings directly:
php artisan route:list --name=cooca-ai.actions.approve -v
php artisan route:list --name=cooca-ai.providers.store -v
php artisan route:list --name=pos.ai.execute-action -v
```

### Invalidation Conditions:
- If `POST /pos/ai/execute-action` returns `200` for a Cashier lacking `ai.approve`, verify `PosAiWebController::middleware()`.
- If `POST /cooca-ai/providers` returns anything other than `403` for a Manager lacking `ai.manage`, verify `AiCompanyWebController::middleware()`.
