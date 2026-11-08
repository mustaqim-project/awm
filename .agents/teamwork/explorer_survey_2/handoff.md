# Handoff Report: Backend Controllers, Routes, and RBAC Authorization Survey

- **Survey Target**: `invoices` and `labor-machines` modules
- **Surveyor**: `explorer_survey_2`
- **Working Directory**: `C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\`
- **Date**: 2026-11-08T23:12:00Z
- **Reference Standard**: [`laravel-permission/SKILL.md`](file:///c:/Users/Ulfah%20Ramadhani/.gemini/config/skills/laravel-permission/SKILL.md) & [ORIGINAL_REQUEST.md](file:///c:/laragon/www/cooca_core/.agents/teamwork/ORIGINAL_REQUEST.md)

---

## 1. Observation

### 1.1 Relevant Controllers Identified

1. **Invoices Module**:
   - **Primary Web Controller**: `App\Http\Controllers\Web\InvoiceWebController` (`app/Http/Controllers/Web/InvoiceWebController.php`, 678 lines).
     - Handles customer commerce billing, invoices list, generation, PDF printing, Excel export, payment recording, confirmation (stock deduction & auto-journaling), term reminders, voiding, and draft deletion.
   - **API Controller**: `App\Http\Controllers\Api\V1\Sales\InvoiceController` (`app/Http/Controllers/Api/V1/Sales/InvoiceController.php`, 238 lines).
     - Handles REST API endpoints for sales invoices (`index`, `store`, `show`, `update`, `recordPayment`, `destroy`).
   - **Purchasing Bills (Related Module)**: `App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController` (`app/Http/Controllers/Web/Purchasing/SupplierInvoiceWebController.php`) and API `App\Http\Controllers\Api\V1\Purchasing\SupplierInvoiceController`.
     - Handles supplier/vendor invoices (bills) under the `purchasing.bills` permission.
   - **Platform Admin (Backoffice)**: `App\Http\Controllers\Admin\AdminB2bController` (`app/Http/Controllers/Admin/AdminB2bController.php`).
     - Handles platform-wide B2B invoices.
   - **SaaS Subscription Invoices**: `App\Http\Controllers\Web\SubscriptionCheckoutWebController` (`invoice` action for platform subscription receipts).

2. **Labor & Machines Module**:
   - **Primary Web Controller**: `App\Http\Controllers\Web\LaborMachineWebController` (`app/Http/Controllers/Web/LaborMachineWebController.php`, 178 lines).
     - Handles web operations: listing labor rates and machines (`index`), creating labor rates (`storeLabor`), updating labor rates (`updateLabor`), deleting labor rates (`destroyLabor`), creating machines (`storeMachine`), updating machines (`updateMachine`), and deleting machines (`destroyMachine`).
   - **API Controllers**:
     - `App\Http\Controllers\Api\V1\LaborRateController` (`app/Http/Controllers/Api/V1/LaborRateController.php`, 126 lines) — `index`, `store`, `show`, `update`, `destroy`, `hourlyEquivalent`.
     - `App\Http\Controllers\Api\V1\MachineController` (`app/Http/Controllers/Api/V1/MachineController.php`, 119 lines) — `index`, `store`, `show`, `update`, `destroy`, `costPerHour`.
     - `App\Http\Controllers\Api\V1\CostModelLaborController` & `CostModelMachineController` — handles assigning labor and machine resources to specific product Cost Models (HPP recipes).

---

### 1.2 Routing Declarations

In `routes/owner.php`:

#### Invoices Routes (`routes/owner.php:329-345`):
```php
// Invoices Management & Generator (Commerce Billing)
Route::get('/invoices/create', [InvoiceWebController::class, 'create'])->middleware('require.permission:invoices.create')->name('invoices.create');
Route::post('/invoices', [InvoiceWebController::class, 'store'])->middleware(['require.permission:invoices.create', 'entitlement:invoice'])->name('invoices.store');
Route::middleware('require.permission:invoices.view')->group(function (): void {
    Route::get('/invoices/export-excel', [InvoiceWebController::class, 'exportExcel'])->middleware('require.permission:invoices.export')->name('invoices.export-excel');
    Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceWebController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf', [InvoiceWebController::class, 'pdf'])->name('invoices.pdf');
});
Route::get('/invoices/{invoice}/edit', [InvoiceWebController::class, 'edit'])->middleware('require.permission:invoices.edit')->name('invoices.edit');
Route::put('/invoices/{invoice}', [InvoiceWebController::class, 'update'])->middleware('require.permission:invoices.edit')->name('invoices.update');
Route::post('/invoices/{invoice}/payments', [InvoiceWebController::class, 'recordPayment'])->middleware('require.permission:invoices.record_payment')->name('invoices.payments.store');
Route::post('/invoices/{invoice}/confirm', [InvoiceWebController::class, 'confirm'])->middleware('require.permission:invoices.create')->name('invoices.confirm');
Route::post('/invoices/{invoice}/remind', [InvoiceWebController::class, 'sendTermReminder'])->middleware('require.permission:invoices.view')->name('invoices.remind');
Route::post('/invoices/{invoice}/void', [InvoiceWebController::class, 'void'])->middleware('require.permission:invoices.edit')->name('invoices.void');
Route::delete('/invoices/{invoice}', [InvoiceWebController::class, 'destroy'])->middleware('require.permission:invoices.delete')->name('invoices.destroy');
```

#### Labor & Machines Routes (`routes/owner.php:372-383`):
```php
// Labor Rates & Machine Costs
Route::middleware(['module:labor_machines', 'require.permission:labor_machines.view'])->group(function (): void {
    Route::get('/labor-machines', [LaborMachineWebController::class, 'index'])->name('labor-machines.index');
});
Route::middleware(['module:labor_machines', 'require.permission:labor_machines.manage'])->group(function (): void {
    Route::post('/labor-rates', [LaborMachineWebController::class, 'storeLabor'])->name('labor-rates.store');
    Route::put('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'updateLabor'])->name('labor-rates.update');
    Route::delete('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'destroyLabor'])->name('labor-rates.destroy');
    Route::post('/machines', [LaborMachineWebController::class, 'storeMachine'])->name('machines.store');
    Route::put('/machines/{machine}', [LaborMachineWebController::class, 'updateMachine'])->name('machines.update');
    Route::delete('/machines/{machine}', [LaborMachineWebController::class, 'destroyMachine'])->name('machines.destroy');
});
```

---

### 1.3 Controller-Level RBAC State

#### `InvoiceWebController` (`app/Http/Controllers/Web/InvoiceWebController.php:28-60`):
- Implements `Illuminate\Routing\Controllers\HasMiddleware`:
```php
final class InvoiceWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:invoices.view', only: [
                'index',
                'show',
                'print',
                'pdf',
                'sendTermReminder',
            ]),
            new Middleware(['require.permission:invoices.create', 'entitlement:invoice'], only: [
                'create',
                'store',
                'confirm',
            ]),
            new Middleware('require.permission:invoices.edit', only: [
                'edit',
                'update',
                'void',
            ]),
            new Middleware('require.permission:invoices.record_payment', only: [
                'recordPayment',
            ]),
            new Middleware('require.permission:invoices.export', only: [
                'exportExcel',
            ]),
            new Middleware('require.permission:invoices.delete', only: [
                'destroy',
            ]),
        ];
    }
```
- Direct Object Reference (IDOR) Protection:
  - Every method executing on a single invoice model (`show`, `edit`, `update`, `recordPayment`, `pdf`, `destroy`, `confirm`, `void`, `sendTermReminder`) explicitly verifies tenant ownership:
    ```php
    $business = Context::requireBusiness();
    abort_unless($invoice->business_id === $business->id, 403);
    ```
  - Input relations (`customer_id`, `product_id`, `purchase_order_id`, `location_id`) are validated against `$business->id`.
  - Mutation methods emit structured `AuditLog` records.

#### `LaborMachineWebController` (`app/Http/Controllers/Web/LaborMachineWebController.php:17`):
- **Does NOT implement `HasMiddleware`**:
```php
final class LaborMachineWebController extends Controller
{
    public function __construct(
        private readonly LaborCostService $laborService = new LaborCostService,
        private readonly MachineCostService $machineService = new MachineCostService
    ) {}
```
- Has **ZERO** Controller-Level middleware declarations (`middleware()` method is absent).
- Direct Object Reference (IDOR) Deficiencies:
  - `updateLabor`: Neither calls `Context::requireBusiness()` nor checks `$laborRate->business_id === $business->id`.
  - `destroyLabor`: Neither calls `Context::requireBusiness()` nor checks `$laborRate->business_id === $business->id`.
  - `updateMachine`: Neither calls `Context::requireBusiness()` nor checks `$machine->business_id === $business->id`.
  - `destroyMachine`: Neither calls `Context::requireBusiness()` nor checks `$machine->business_id === $business->id`.
- Audit Logging Deficiencies:
  - Mutation actions (`storeLabor`, `updateLabor`, `destroyLabor`, `storeMachine`, `updateMachine`, `destroyMachine`) record **zero** `AuditLog` entries.

---

### 1.4 Database Seeders & Permission Matrix

In `database/seeders/RbacSeeder.php`:

#### Permission Definitions (`database/seeders/RbacSeeder.php:59-65, 200-201`):
| Permission Slug | Name | Category | Description |
|---|---|---|---|
| `invoices.view` | Lihat Faktur | `invoices` | Melihat daftar dan detail faktur penjualan. |
| `invoices.create` | Buat Faktur | `invoices` | Menerbitkan faktur penjualan baru. |
| `invoices.edit` | Edit Faktur | `invoices` | Mengubah faktur yang belum dikirim. |
| `invoices.delete` | Hapus Faktur | `invoices` | Menghapus faktur penjualan. |
| `invoices.record_payment` | Catat Pembayaran Faktur | `invoices` | Mencatat pelunasan piutang faktur. |
| `invoices.export` | Export Faktur | `invoices` | Export data faktur ke Excel/CSV. |
| `labor_machines.view` | Lihat Upah Kerja & Mesin | `labor` | Melihat daftar tarif upah kerja dan biaya mesin. |
| `labor_machines.manage` | Kelola Upah Kerja & Mesin | `labor` | Menambah dan mengubah tarif upah kerja dan biaya mesin produksi. |

#### Role Mapping (`database/seeders/RbacSeeder.php:220-475`):
| Role | Invoices Permissions | Labor & Machine Permissions | Notes |
|---|---|---|---|
| `owner` | All (`view`, `create`, `edit`, `delete`, `record_payment`, `export`) | All (`view`, `manage`) | Full access |
| `admin` | `view`, `create`, `edit`, `record_payment`, `export` | `view`, `manage` | Inherits manager slugs + admin ops |
| `manager` | `view`, `create`, `edit`, `record_payment`, `export` | `view`, `manage` | All except `invoices.delete` |
| `finance` | `view`, `create`, `edit`, `record_payment`, `export` | None | Dedicated to billing/AR/AP |
| `cashier` | `view`, `create`, `record_payment` | None | Restricted (no edit, delete, export) |
| `staff` | `view`, `create`, `record_payment` | None | General operational sales/POS |
| `viewer` | `view` | None | Read-only |
| `warehouse` | None | None | Isolated to logistics & inventory |

---

### 1.5 View-Layer Permission Guards & Discrepancies

#### In `resources/views/app/labor-machines/index.blade.php`:
- Lines 138, 337, 410, 483, 538, 619, 694 guard UI action buttons with:
  `@if (\App\Support\Context::hasPermission('costing.manage'))`
- **Discrepancy**: The backend routes and seeders use `labor_machines.manage`. Users granted `labor_machines.manage` (who do not have `costing.manage`) cannot see or access the Create/Edit/Delete buttons on the interface.

#### In `resources/views/app/invoices/show.blade.php`:
- Line 58: `$canRelease = $invoice->status === 'draft' && (\App\Support\Context::hasPermission('invoices.create') || \App\Support\Context::hasPermission('invoices.edit'));`
- Line 62: `$canVoid = !in_array($invoice->status, ['draft', 'void']) && $invoice->paid_amount == 0 && (\App\Support\Context::hasPermission('invoices.delete') || \App\Support\Context::hasPermission('invoices.edit'));`
- **Discrepancy**:
  - `InvoiceWebController::confirm()` requires `invoices.create`. In the view, a user with only `invoices.edit` sees the "Rilis Faktur" button, but submitting results in a `403 Forbidden`.
  - `InvoiceWebController::void()` requires `invoices.edit`. In the view, the guard checks `invoices.delete || invoices.edit`.

---

## 2. Logic Chain

1. **Premise 1 (Laravel 11+ Architecture & Clean Routing Standard)**:
   - According to `laravel-permission/SKILL.md` (Pillar 2) and `references/controller-middleware-migration-guide.md`, controllers in modern Laravel (Framework `^13.17`) should implement `Illuminate\Routing\Controllers\HasMiddleware` to encapsulate authorization.
   - Route files (`routes/owner.php`) should be clean, declarative, and free of duplicated permission middleware chains.
2. **Premise 2 (Invoice Controller State vs Route Redundancy)**:
   - `InvoiceWebController` already implemented `HasMiddleware` with exact action mappings (`invoices.view`, `invoices.create`, `invoices.edit`, `invoices.record_payment`, `invoices.export`, `invoices.delete`).
   - However, `routes/owner.php` lines 330-345 still retain duplicate route-level middlewares (`->middleware('require.permission:invoices.xxx')`).
   - Therefore, the routes for `invoices` in `routes/owner.php` are redundant and should be cleaned to follow the encapsulated pattern exhibited by `SupplierInvoiceWebController`.
3. **Premise 3 (LaborMachine Controller Gap & IDOR Risk)**:
   - `LaborMachineWebController` currently implements NO middleware.
   - Authorization relies entirely on two route group wrappers in `routes/owner.php`.
   - Furthermore, mutation actions (`updateLabor`, `destroyLabor`, `updateMachine`, `destroyMachine`) lack explicit business scoping (`abort_unless($record->business_id === $business->id, 403)`), creating a potential IDOR vulnerability if invoked outside route group context.
   - Therefore, `LaborMachineWebController` must implement `HasMiddleware`, encapsulate `module:labor_machines`, `labor_machines.view`, and `labor_machines.manage`, and enforce explicit tenant checks.
4. **Premise 4 (Permission Registry Completeness)**:
   - `RbacSeeder.php` already contains all 6 invoice permissions (`invoices.view`, `create`, `edit`, `delete`, `record_payment`, `export`) and both labor-machine permissions (`labor_machines.view`, `manage`).
   - All standard business roles (`owner`, `admin`, `manager`, `cashier`, `finance`, `staff`, `viewer`, `warehouse`) have appropriate assignments.
   - No missing or phantom permission exists in the database seeder matrix.
5. **Premise 5 (UI View Alignment)**:
   - `labor-machines/index.blade.php` mistakenly checks `costing.manage` for all button actions instead of `labor_machines.manage`.
   - `invoices/show.blade.php` has a mismatch where `$canRelease` allows `invoices.edit` even though the controller strictly mandates `invoices.create`.
   - Aligning view guards with controller permissions ensures zero 403 errors and consistent UI state.

---

## 3. Caveats

1. **Entitlement Middleware on Invoices**:
   - `InvoiceWebController` binds `entitlement:invoice` to `create`, `store`, and `confirm` in its `HasMiddleware` definition. This checks plan quota limits (e.g. Free plan invoice quotas). Any route cleanup must ensure `entitlement:invoice` remains active on the controller level.
2. **Custom Business Roles**:
   - COOCA allows business owners to define custom roles (`$membership->customRole`). If a business owner creates a custom role with `labor_machines.manage` only, fixing the view condition (`costing.manage` $\rightarrow$ `labor_machines.manage`) is necessary for them to use the feature.
3. **API Controllers (`Api/V1`)**:
   - API controllers (`Sales\InvoiceController`, `LaborRateController`, `MachineController`) currently rely on `routes/api.php` route middleware. While this survey focuses on the primary web modules requested (`resources/views/app/invoices` and `resources/views/app/labor-machines`), migrating API controllers to `HasMiddleware` in a future refactor would provide full defense-in-depth across the API surface.

---

## 4. Conclusion & Actionable Design

### 4.1 Proposed Clean Routing (`routes/owner.php`)

```php
// =========================================================================
// Invoices Management & Generator (Commerce Billing)
// Clean Routing: Permission & Entitlement encapsulated in InvoiceWebController
// =========================================================================
Route::get('/invoices/create', [InvoiceWebController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceWebController::class, 'store'])->name('invoices.store');
Route::get('/invoices/export-excel', [InvoiceWebController::class, 'exportExcel'])->name('invoices.export-excel');
Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices.index');
Route::get('/invoices/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
Route::get('/invoices/{invoice}/print', [InvoiceWebController::class, 'print'])->name('invoices.print');
Route::get('/invoices/{invoice}/pdf', [InvoiceWebController::class, 'pdf'])->name('invoices.pdf');
Route::get('/invoices/{invoice}/edit', [InvoiceWebController::class, 'edit'])->name('invoices.edit');
Route::put('/invoices/{invoice}', [InvoiceWebController::class, 'update'])->name('invoices.update');
Route::post('/invoices/{invoice}/payments', [InvoiceWebController::class, 'recordPayment'])->name('invoices.payments.store');
Route::post('/invoices/{invoice}/confirm', [InvoiceWebController::class, 'confirm'])->name('invoices.confirm');
Route::post('/invoices/{invoice}/remind', [InvoiceWebController::class, 'sendTermReminder'])->name('invoices.remind');
Route::post('/invoices/{invoice}/void', [InvoiceWebController::class, 'void'])->name('invoices.void');
Route::delete('/invoices/{invoice}', [InvoiceWebController::class, 'destroy'])->name('invoices.destroy');

// =========================================================================
// Labor Rates & Machine Costs
// Clean Routing: Module & Permissions encapsulated in LaborMachineWebController
// =========================================================================
Route::get('/labor-machines', [LaborMachineWebController::class, 'index'])->name('labor-machines.index');
Route::post('/labor-rates', [LaborMachineWebController::class, 'storeLabor'])->name('labor-rates.store');
Route::put('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'updateLabor'])->name('labor-rates.update');
Route::delete('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'destroyLabor'])->name('labor-rates.destroy');
Route::post('/machines', [LaborMachineWebController::class, 'storeMachine'])->name('machines.store');
Route::put('/machines/{machine}', [LaborMachineWebController::class, 'updateMachine'])->name('machines.update');
Route::delete('/machines/{machine}', [LaborMachineWebController::class, 'destroyMachine'])->name('machines.destroy');
```

---

### 4.2 Proposed Controller-Level RBAC for `LaborMachineWebController`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Labor\LaborCostService;
use App\Domain\Machine\MachineCostService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class LaborMachineWebController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('module:labor_machines'),
            new Middleware('require.permission:labor_machines.view', only: [
                'index',
            ]),
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

    public function __construct(
        private readonly LaborCostService $laborService = new LaborCostService,
        private readonly MachineCostService $machineService = new MachineCostService
    ) {}

    public function index(): View
    {
        $business = Context::requireBusiness();

        $laborRates = LaborRate::where('business_id', $business->id)->latest()->get();
        $machines = Machine::where('business_id', $business->id)->latest()->get();

        return view('app.labor-machines.index', compact('business', 'laborRates', 'machines'));
    }

    public function storeLabor(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        // validation & creation with AuditLog
        ...
    }

    public function updateLabor(Request $request, LaborRate $laborRate): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($laborRate->business_id === $business->id, 403);
        // validation & update with AuditLog
        ...
    }

    public function destroyLabor(LaborRate $laborRate): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($laborRate->business_id === $business->id, 403);
        // deletion with AuditLog
        ...
    }

    public function storeMachine(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        // validation & creation with AuditLog
        ...
    }

    public function updateMachine(Request $request, Machine $machine): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($machine->business_id === $business->id, 403);
        // validation & update with AuditLog
        ...
    }

    public function destroyMachine(Machine $machine): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($machine->business_id === $business->id, 403);
        // deletion with AuditLog
        ...
    }
}
```

---

### 4.3 Proposed View Guard Fixes

1. **In `resources/views/app/labor-machines/index.blade.php`**:
   - Replace `@if (\App\Support\Context::hasPermission('costing.manage'))` with:
     ```blade
     @if (\App\Support\Context::hasPermission('labor_machines.manage') || \App\Support\Context::hasPermission('costing.manage'))
     ```
   - Locations:
     - Line 138 (Header action buttons: "Tambah Tarif Upah", "Tambah Mesin Baru")
     - Line 337 (Labor section desktop table header action)
     - Line 410 (Labor table row: edit and delete buttons)
     - Line 483 (Labor mobile card: edit and delete buttons)
     - Line 538 (Machine section desktop table header action)
     - Line 619 (Machine table row: edit and delete buttons)
     - Line 694 (Machine mobile card: edit and delete buttons)

2. **In `resources/views/app/invoices/show.blade.php`**:
   - Line 58: Fix `$canRelease` to require `invoices.create`:
     ```php
     $canRelease = $invoice->status === 'draft' && \App\Support\Context::hasPermission('invoices.create');
     ```
   - Line 62: Align `$canVoid` strictly with controller requirement:
     ```php
     $canVoid = !in_array($invoice->status, ['draft', 'void']) && (float) $invoice->paid_amount === 0.0 && \App\Support\Context::hasPermission('invoices.edit');
     ```

---

## 5. Verification Method

To independently verify all findings and validate future implementations:

1. **Syntax Linting**:
   ```powershell
   php -l app\Http\Controllers\Web\InvoiceWebController.php
   php -l app\Http\Controllers\Web\LaborMachineWebController.php
   Get-ChildItem -Path "resources\views\app\invoices", "resources\views\app\labor-machines" -Filter "*.blade.php" -Recurse | ForEach-Object { php -l $_.FullName }
   ```
   *Expected outcome*: Every file reports `No syntax errors detected`.

2. **Run Feature Tests for Invoices**:
   ```powershell
   php artisan test tests/Feature/InvoiceModuleAuditTest.php
   ```
   *Verified result*: 9 passed, 32 assertions (`0 failures`).

3. **Verify Route List Authorization Mapping**:
   ```powershell
   php artisan route:list --name=invoices
   php artisan route:list --name=labor-machines
   ```
   *Expected outcome*: Routes resolve to their respective controllers without unhandled exceptions.

4. **Verify RBAC Seeder Idempotency**:
   ```powershell
   php artisan db:seed --class=RbacSeeder --force
   ```
   *Expected outcome*: All permissions and roles are synced without duplicate key exceptions.
