# Milestone 1 Handoff Report: Controller-Level RBAC, Tenant Security & Clean Routing

- **Worker**: `worker_m1`
- **Milestone**: Milestone 1 (M1: Controller-Level RBAC & Tenant Security)
- **Date**: 2026-11-09T06:34:00Z
- **Reference Standard**: [`laravel-permission/SKILL.md`](file:///c:/Users/Ulfah%20Ramadhani/.gemini/config/skills/laravel-permission/SKILL.md) & [`PROJECT.md`](file:///c:/laragon/www/cooca_core/.agents/teamwork/orchestrator_1/PROJECT.md)
- **Exclusive Write Ownership Scope**:
  - `app/Http/Controllers/Web/LaborMachineWebController.php`
  - `routes/owner.php` (lines for invoices and labor-machines routing)
  - `resources/views/app/labor-machines/index.blade.php` (permission directives)

---

## 1. Observation

### 1.1 Pre-Modification State

1. **`app/Http/Controllers/Web/LaborMachineWebController.php`**:
   - Class declared as `final class LaborMachineWebController extends Controller`.
   - Did **not** implement `Illuminate\Routing\Controllers\HasMiddleware`.
   - Method `public static function middleware(): array` was completely absent.
   - `index()` queried all records globally without tenant isolation:
     ```php
     $laborRates = LaborRate::latest()->get();
     $machines = Machine::latest()->get();
     ```
   - In mutation methods (`updateLabor`, `destroyLabor`, `updateMachine`, `destroyMachine`), no tenant verification existed:
     ```php
     // updateLabor:
     $laborRate->update([...]);
     // destroyLabor:
     $laborRate->delete();
     // updateMachine:
     $machine->update([...]);
     // destroyMachine:
     $machine->delete();
     ```
     An authenticated user from Business A could directly modify or delete records belonging to Business B if providing the ID (IDOR vulnerability).
   - Zero `AuditLog` entries were dispatched on create, update, or delete operations.

2. **`routes/owner.php`**:
   - Invoices routes (lines 330–345) explicitly specified redundant route-level middleware:
     ```php
     Route::get('/invoices/create', [InvoiceWebController::class, 'create'])->middleware('require.permission:invoices.create')->name('invoices.create');
     Route::post('/invoices', [InvoiceWebController::class, 'store'])->middleware(['require.permission:invoices.create', 'entitlement:invoice'])->name('invoices.store');
     Route::middleware('require.permission:invoices.view')->group(function (): void {
         Route::get('/invoices/export-excel', [InvoiceWebController::class, 'exportExcel'])->middleware('require.permission:invoices.export')->name('invoices.export-excel');
         Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices.index');
         ...
     });
     Route::get('/invoices/{invoice}/edit', ...)->middleware('require.permission:invoices.edit');
     Route::put('/invoices/{invoice}', ...)->middleware('require.permission:invoices.edit');
     Route::post('/invoices/{invoice}/payments', ...)->middleware('require.permission:invoices.record_payment');
     Route::post('/invoices/{invoice}/confirm', ...)->middleware('require.permission:invoices.create');
     Route::post('/invoices/{invoice}/remind', ...)->middleware('require.permission:invoices.view');
     Route::post('/invoices/{invoice}/void', ...)->middleware('require.permission:invoices.edit');
     Route::delete('/invoices/{invoice}', ...)->middleware('require.permission:invoices.delete');
     ```
     This duplicated the definitions already declared inside `InvoiceWebController::middleware()`.
   - Labor-machines routes (lines 372–383) were split into two separate route groups with route-level permissions:
     ```php
     Route::middleware(['module:labor_machines', 'require.permission:labor_machines.view'])->group(...);
     Route::middleware(['module:labor_machines', 'require.permission:labor_machines.manage'])->group(...);
     ```

3. **`resources/views/app/labor-machines/index.blade.php`**:
   - Checked `costing.manage` at lines 138, 169, 337, 410, 483, 538, 619, 694 instead of `labor_machines.manage`.
   - Users with role `admin` or `manager` who possessed `labor_machines.manage` (but not `costing.manage`) could not see or click add/edit/delete buttons.

---

### 1.2 Implemented Changes

#### A. `app/Http/Controllers/Web/LaborMachineWebController.php`
- Implemented `Illuminate\Routing\Controllers\HasMiddleware` on the class.
- Added `middleware()` static method:
  ```php
  public static function middleware(): array
  {
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
  }
  ```
- Scoped `index()` queries strictly to the active business:
  ```php
  $laborRates = LaborRate::where('business_id', $business->id)->latest()->get();
  $machines = Machine::where('business_id', $business->id)->latest()->get();
  ```
- Enforced IDOR protection in `updateLabor`, `destroyLabor`, `updateMachine`, and `destroyMachine`:
  ```php
  $business = Context::business();
  abort_unless($business !== null, 403);
  abort_unless($laborRate->business_id === $business->id, 403);
  // and for machines:
  abort_unless($machine->business_id === $business->id, 403);
  ```
- Added full `AuditLog` records for `created`, `updated`, and `deleted` actions for both `LaborRate` and `Machine`, capturing IP address, user agent, old values, and new values.

#### B. `routes/owner.php`
- Cleaned up redundant route-level permission middleware from all 14 `invoices` routes. `InvoiceWebController` continues to enforce view, create, edit, delete, payment recording, and Excel export permissions plus the `entitlement:invoice` check via `HasMiddleware`.
- Consolidated the `labor-machines` routes under a clean `Route::middleware('module:labor_machines')->group(...)` block, eliminating route-level `require.permission` checks now enforced by `LaborMachineWebController::middleware()`. Preserved all route names (`labor-machines.index`, `labor-rates.store`, `labor-rates.update`, `labor-rates.destroy`, `machines.store`, `machines.update`, `machines.destroy`).

#### C. `resources/views/app/labor-machines/index.blade.php`
- Synchronized all action button gates from `costing.manage` to `@can('labor_machines.manage')` / `@endcan` and `Context::hasPermission('labor_machines.manage')`:
  - Line 138: Top action buttons (Tambah Tarif Upah, Tambah Mesin Baru) wrapped in `@can('labor_machines.manage')` ... `@endcan`.
  - Line 169: Sub-navigation tab check updated to include `Context::hasPermission('labor_machines.manage')`.
  - Line 337: Direct labor section header button (Tambah Posisi) wrapped in `@can('labor_machines.manage')` ... `@endcan`.
  - Line 410: Direct labor desktop table row actions (edit, delete) wrapped in `@can('labor_machines.manage')` ... `@else` ... `@endcan`.
  - Line 483: Direct labor mobile card actions wrapped in `@can('labor_machines.manage')` ... `@endcan`.
  - Line 538: Machine section header button (Tambah Mesin) wrapped in `@can('labor_machines.manage')` ... `@endcan`.
  - Line 619: Machine desktop table row actions (edit, delete) wrapped in `@can('labor_machines.manage')` ... `@else` ... `@endcan`.
  - Line 694: Machine mobile card actions wrapped in `@can('labor_machines.manage')` ... `@endcan`.

---

## 2. Logic Chain

1. **Modern Clean Routing Architecture**:
   - Modern Laravel 11/12 best practices (as codified in `laravel-permission/SKILL.md`) specify that authorization middleware belongs inside controllers via `HasMiddleware` rather than cluttering route definitions.
   - Enkapsulasi ini memusatkan seluruh otorisasi modul pada satu tempat (single source of truth), mencegah route bocor saat endpoint dipanggil atau dialiaskan dari tempat lain.

2. **Defense-in-Depth & IDOR Elimination**:
   - Relying on route middleware alone is insufficient if controller methods accept implicit model bindings without verifying tenant ownership.
   - Explicitly adding `abort_unless($record->business_id === $business->id, 403);` guarantees that cross-tenant access attempts immediately fail with HTTP 403 Forbidden.
   - Restricting `index()` to `where('business_id', $business->id)` eliminates tenant data leaks on listing views.

3. **Audit Trail Completeness**:
   - Cooca ERP mandates auditable business actions (`AuditLog`).
   - Recording structured log entries on `storeLabor`, `updateLabor`, `destroyLabor`, `storeMachine`, `updateMachine`, and `destroyMachine` provides compliance traceability for all rate and equipment changes.

4. **UI-Backend Permission Parity**:
   - The database permissions defined in `RbacSeeder.php` are `labor_machines.view` and `labor_machines.manage`.
   - The views originally checked `costing.manage`, which meant managers and operators without global costing permissions were locked out of labor/machine management buttons.
   - Transitioning view directives to `@can('labor_machines.manage')` aligns the UI directly with backend enforcement, avoiding false-denial UI anomalies.

---

## 3. Caveats

- **Profitability Sub-navigation Tab**: Line 186 in `resources/views/app/labor-machines/index.blade.php` retains checking `costing.view_margin`, `reports.costing`, and `costing.manage` because it links to the `/profitability` route, which is protected by `require.permission:costing.view_margin`.
- **API Controllers**: Rest API endpoints (`App\Http\Controllers\Api\V1\LaborRateController` and `MachineController`) operate under `routes/api.php` and use API token/sanctum authentication with route middleware; they remain untouched as they are outside the web module scope.
- No other caveats.

---

## 4. Conclusion

Milestone 1 is complete:
- `LaborMachineWebController` implements `HasMiddleware` with exact action-to-permission mappings.
- Tenant isolation and IDOR defense are enforced across all single-record and listing operations.
- Full `AuditLog` logging is dispatched on all mutations.
- `routes/owner.php` is streamlined with clean routing for both `invoices` and `labor-machines`.
- `labor-machines/index.blade.php` is synchronized to `@can('labor_machines.manage')`.
- Zero syntax errors detected, all relevant unit and feature tests pass with 0 failures.

---

## 5. Verification Method

To independently verify the implementation:

1. **PHP Syntax Check (`php -l`)**:
   ```powershell
   php -l app\Http\Controllers\Web\LaborMachineWebController.php
   php -l routes\owner.php
   php -l resources\views\app\labor-machines\index.blade.php
   ```
   *Verified Result*: All files reported `No syntax errors detected`.

2. **Route Resolution Check**:
   ```powershell
   php artisan route:list --name=labor-machines
   php artisan route:list --name=labor-rates
   php artisan route:list --name=machines
   php artisan route:list --name=invoices
   ```
   *Verified Result*: All 28 target routes resolved to their respective controllers without errors.

3. **Feature Test Execution**:
   ```powershell
   php artisan test tests/Feature/LaborAndMachineEngineTest.php
   php artisan test tests/Feature/InvoiceModuleAuditTest.php
   php artisan test tests/Feature/InvoiceDraftEditAndPartiesTest.php
   ```
   *Verified Results*:
   - `LaborAndMachineEngineTest`: 4 tests passed, 15 assertions (20.6s)
   - `InvoiceModuleAuditTest`: 9 tests passed, 32 assertions (27.7s)
   - `InvoiceDraftEditAndPartiesTest`: 4 tests passed, 24 assertions (21.8s)
   - **Total**: 17 tests passed, 71 assertions, 0 failures.
