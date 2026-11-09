# Empirical Challenge & Verification Report: Milestone M1 (Controller-Level RBAC & Clean Routing)

- **Agent**: `challenger_m1_1` (Critic & Specialist)
- **Target Work Product**: Milestone M1 changes by `worker_m1`
- **Date**: 2026-11-09T06:48:00Z
- **Verdict**: **APPROVE**

---

## 1. Observation

### 1.1 Source Code and Controller Contracts
1. **`app/Http/Controllers/Web/LaborMachineWebController.php`**:
   - Implements `Illuminate\Routing\Controllers\HasMiddleware` (line 20).
   - Method `public static function middleware(): array` (lines 25–38) declares:
     ```php
     return [
         new Middleware('require.permission:labor_machines.view', only: ['index']),
         new Middleware('require.permission:labor_machines.manage', only: [
             'storeLabor',
             'updateLabor',
             'destroyLabor',
             'storeMachine',
             'updateMachine',
             'destroyMachine',
         ]),
     ];
     ```
   - Listing method `index()` (lines 48–56) enforces active tenant query scoping:
     ```php
     $laborRates = LaborRate::where('business_id', $business->id)->latest()->get();
     $machines = Machine::where('business_id', $business->id)->latest()->get();
     ```
   - IDOR guards are explicitly placed on all single-record mutation methods:
     - `updateLabor` (lines 112–114):
       ```php
       $business = Context::business();
       abort_unless($business !== null, 403);
       abort_unless($laborRate->business_id === $business->id, 403);
       ```
     - `destroyLabor` (lines 286–288):
       ```php
       $business = Context::business();
       abort_unless($business !== null, 403);
       abort_unless($laborRate->business_id === $business->id, 403);
       ```
     - `updateMachine` (lines 224–226):
       ```php
       $business = Context::business();
       abort_unless($business !== null, 403);
       abort_unless($machine->business_id === $business->id, 403);
       ```
     - `destroyMachine` (lines 322–324):
       ```php
       $business = Context::business();
       abort_unless($business !== null, 403);
       abort_unless($machine->business_id === $business->id, 403);
       ```
   - `AuditLog` dispatches (lines 86–102, 146–163, 198–215, 259–276, 300–312, 335–347) capture action tags `labor_rates.created`, `labor_rates.updated`, `labor_rates.deleted`, `machines.created`, `machines.updated`, and `machines.deleted` with metadata and old/new values.

2. **`routes/owner.php`**:
   - Invoices routes (lines 330–343) have eliminated redundant route-level middleware declarations (`require.permission:invoices.*`), relying completely on `InvoiceWebController::middleware()`.
   - Labor-machines routes (lines 371–379) are cleanly grouped under `Route::middleware('module:labor_machines')->group(...)` with all 7 routes intact and mapped directly to `LaborMachineWebController`:
     - `GET /labor-machines` -> `labor-machines.index`
     - `POST /labor-rates` -> `labor-rates.store`
     - `PUT /labor-rates/{laborRate}` -> `labor-rates.update`
     - `DELETE /labor-rates/{laborRate}` -> `labor-rates.destroy`
     - `POST /machines` -> `machines.store`
     - `PUT /machines/{machine}` -> `machines.update`
     - `DELETE /machines/{machine}` -> `machines.destroy`

3. **`resources/views/app/labor-machines/index.blade.php`**:
   - Lines 138, 169, 337, 410, 483, 538, 619, and 694 all check `@can('labor_machines.manage')` or `Context::hasPermission('labor_machines.manage')`, eliminating former mismatch with `costing.manage`.

### 1.2 Empirical Test Execution Results
An empirical test harness was written and executed at `tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php`:
- **Execution Command**:
  ```powershell
  php artisan test tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php
  ```
- **Result Output**:
  ```json
  {"tool":"phpunit","result":"passed","tests":9,"passed":9,"assertions":134,"duration_ms":30831}
  ```
  *Verdict: 9 passed, 0 failed, 134 assertions.*

Specific test scenarios proven:
1. `test_labor_machine_web_controller_implements_has_middleware_and_declares_exact_contracts`: Controller implements `HasMiddleware`; reflection inspection verifies exact middleware names and action arrays matching interface contracts.
2. `test_route_names_for_labor_machines_are_intact_and_resolve_correctly`: All 7 route names resolve to `LaborMachineWebController` and accept appropriate HTTP verbs (GET/POST/PUT/DELETE).
3. `test_route_names_for_invoices_are_intact_and_resolve_correctly`: All 14 invoice route names resolve to `InvoiceWebController` and accept designated HTTP verbs.
4. `test_unauthorized_user_lacking_labor_machines_view_cannot_access_index`: Users lacking `labor_machines.view` receive HTTP `403 Forbidden` (`FORBIDDEN_PERMISSION`) on JSON requests and are redirected to `route('portal')` on browser requests.
5. `test_user_with_labor_machines_view_can_access_index_and_data_is_tenant_scoped`: Users with `labor_machines.view` receive `200 OK`; assertions prove records belonging to another tenant (`business_id`) are excluded from output.
6. `test_user_lacking_labor_machines_manage_cannot_execute_mutations`: Users with read-only access attempting to call `storeLabor`, `updateLabor`, `destroyLabor`, `storeMachine`, `updateMachine`, or `destroyMachine` receive HTTP `403 Forbidden` (`FORBIDDEN_PERMISSION`).
7. `test_authorized_user_can_execute_all_labor_and_machine_mutations_and_dispatches_audit_logs`: Authorized users successfully perform all 6 CRUD operations, create records with correct active `business_id`, and generate verified `AuditLog` records with correct action slugs and payload diffs.
8. `test_idor_cross_tenant_update_and_destroy_are_defensively_rejected`: Cross-tenant attack attempts by Business B against Business A records are blocked at multiple layers (layer 1: `BusinessScope` / route model binding, layer 2: explicit controller `abort_unless(..., 403)`). Direct controller invocations with mismatched models throw `HttpException(403)`. Target records in the database remain untouched and undeleted.
9. `test_invoices_controller_level_rbac_enforcement`: Invoices module routes correctly enforce permission requirements via `InvoiceWebController::middleware()`, rejecting unauthorized requests with 403 while granting authorized access.

### 1.3 Full Regression Test Suite Execution
- `tests/Feature/InvoiceModuleAuditTest.php`: 9 tests passed, 32 assertions (57.3s)
- `tests/Feature/InvoiceDraftEditAndPartiesTest.php`: 4 tests passed, 24 assertions (15.8s)
- `tests/Feature/LaborAndMachineEngineTest.php`: 4 tests passed, 15 assertions (15.9s)
- `tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php`: 9 tests passed, 134 assertions (30.8s)
- **Cumulative Regression Total**: 26 tests passed, 205 assertions, 0 failures.

### 1.4 Syntax Linting (`php -l`)
```powershell
php -l app/Http/Controllers/Web/LaborMachineWebController.php
php -l routes/owner.php
php -l resources/views/app/labor-machines/index.blade.php
php -l tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php
```
All four files reported `No syntax errors detected`.

---

## 2. Logic Chain

1. **Controller-Level RBAC Enforcement**:
   - From Observation 1.1, `LaborMachineWebController` implements `HasMiddleware` and configures `require.permission:labor_machines.view` for `index` and `require.permission:labor_machines.manage` for all 6 mutation actions.
   - Observation 1.2 (tests 1, 4, 6) confirms that requests lacking these permissions are halted with HTTP 403 Forbidden prior to method execution.
   - Therefore, controller-level RBAC is correctly enforced independent of route-level definitions.

2. **Clean Routing Integrity**:
   - Observation 1.1 shows that duplicate `require.permission` declarations were removed from `routes/owner.php` for both invoices and labor-machines.
   - Observation 1.2 (tests 2, 3, 9) proves that all 21 route names remain registered in Laravel's RouteCollection, resolve to the intended controller methods, and maintain full authorization enforcement via controller middleware.
   - Therefore, route cleaning did not introduce regressions or route naming breakages.

3. **Tenant Security & IDOR Elimination**:
   - Observation 1.1 reveals explicit `abort_unless($record->business_id === $business->id, 403)` checks on `updateLabor`, `destroyLabor`, `updateMachine`, and `destroyMachine`.
   - Observation 1.2 (test 8) executes simulated adversary attacks from Business B against Business A. Direct controller execution throws 403, and HTTP route binding rejects foreign IDs. Database assertions verify no record alteration occurred.
   - Observation 1.2 (test 5) verifies listing queries are filtered by `where('business_id', $business->id)`, preventing cross-tenant data exposure.
   - Therefore, tenant isolation and IDOR defenses are robustly implemented with defense-in-depth.

4. **UI Permission Synchronization**:
   - Observation 1.1 confirms that `resources/views/app/labor-machines/index.blade.php` now gates all action buttons using `@can('labor_machines.manage')`.
   - This matches the backend requirement of `LaborMachineWebController::middleware()`, eliminating previous authorization desynchronization where users with `labor_machines.manage` were incorrectly blocked by `costing.manage` checks.

---

## 3. Caveats

- **View Presentation & Layout**: View visual hierarchy and button spacing were checked for syntax and authorization directives, but full UI ergonomics, no-overlap redesign, and bento layout adjustments are reserved for Milestone M2 as planned in `PROJECT.md`.
- No other caveats.

---

## 4. Conclusion

The implementation delivered by `worker_m1` for Milestone M1 is empirically robust, secure, and compliant with all project requirements:
- Controller-level RBAC adheres to Laravel 11 `HasMiddleware` standards.
- Route names and resolution contracts are 100% intact.
- Multi-tenant IDOR protection is established at both model-scope and controller levels.
- Full audit trails are recorded on all state mutations.
- 26 tests and 205 assertions pass cleanly across the regression boundary.

**Final Verdict: APPROVE**

---

## 5. Verification Method

To independently reproduce and verify this assessment:

1. **Run PHP Linting**:
   ```powershell
   php -l app/Http/Controllers/Web/LaborMachineWebController.php
   php -l routes/owner.php
   php -l resources/views/app/labor-machines/index.blade.php
   php -l tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php
   ```

2. **Run Dedicated Empirical RBAC & Clean Routing Test Suite**:
   ```powershell
   php artisan test tests/Feature/LaborMachineRbacCleanRoutingEmpiricalTest.php
   ```

3. **Run Invoices & Calculation Regression Test Suites**:
   ```powershell
   php artisan test tests/Feature/InvoiceModuleAuditTest.php
   php artisan test tests/Feature/InvoiceDraftEditAndPartiesTest.php
   php artisan test tests/Feature/LaborAndMachineEngineTest.php
   ```

4. **Invalidation Conditions**:
   - Any failure in `LaborMachineRbacCleanRoutingEmpiricalTest.php` indicates RBAC bypass or broken route contracts.
   - Unauthenticated/unauthorized users obtaining HTTP 200 on mutation endpoints invalidates controller middleware.
   - Modifying a model from Business A while authenticated in Business B without a 403/404 exception invalidates IDOR defense.
