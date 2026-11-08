# Handoff Report — Explorer Survey 3: Module Audit Readiness & Documentation Survey (`invoices` & `labor-machines`)

## 1. Observation

Direct code-level and filesystem observations across `invoices` and `labor-machines` modules:

### 1.1 Documentation Architecture Observations
- **Docs Root & Controller**:
  - Routing: `routes/owner.php:542` defines `Route::get('/docs', [DocumentationWebController::class, 'index'])->name('docs.index');`.
  - Controller: `app/Http/Controllers/Web/DocumentationWebController.php` renders `resources/views/app/docs/index.blade.php`.
- **Existing Documentation Modules**:
  - `resources/views/app/docs/partials/` contains 14 dedicated partials: `sec_ai.blade.php`, `sec_ai_studio.blade.php`, `sec_analytics.blade.php`, `sec_approvals.blade.php`, `sec_billing.blade.php`, `sec_community.blade.php`, `sec_crm.blade.php`, `sec_customers.blade.php`, `sec_feedback.blade.php`, `sec_finance.blade.php`, `sec_hrm.blade.php`, `sec_import.blade.php`, `sec_inventory.blade.php`, and `sec_storefront.blade.php`.
  - Each dedicated partial strictly adheres to the Business Owner User Guideline standard:
    1. Header with semantic icon, title, subtitle, and verified actionable route links (`route('...')`).
    2. Executive Summary (POV Business Owner with 3 high-impact value propositions).
    3. 3–4 Step Practical Workflow.
    4. 20 Industries Matrix across 6 Clusters.
    5. Anti-Fraud, Security, and Permission Controls.
    6. Business Owner FAQ.
- **Invoices Documentation Status**:
  - Currently, `invoices` has **no dedicated partial** in `resources/views/app/docs/partials/`.
  - In `resources/views/app/docs/index.blade.php:614-650`, it is represented only by an obsolete, 36-line inline stub under `#sec-sales-b2b` mentioning only high-level quotation-to-invoice without the 20-industry matrix, anti-fraud controls, or detailed step-by-step guidance.
- **Labor & Machines Documentation Status**:
  - Currently **100% missing** from `resources/views/app/docs/`.
  - There is no partial `sec_labor_machines.blade.php`, no `#sec-labor-machines` anchor, no sidebar TOC item in `index.blade.php`, and no search keywords in `matchesSearch(...)`.

### 1.2 Models, Relationships & Database Schema Observations
- **Invoices Module**:
  - **Models**:
    - `App\Models\Invoice` (`app/Models/Invoice.php:17`): Uses `Auditable`, `BelongsToBusiness`, `HasUuid`, `SoftDeletes`.
    - `App\Models\InvoiceItem` (`app/Models/InvoiceItem.php:14`): Uses `HasUuid`.
    - `App\Models\InvoicePayment` (`app/Models/InvoicePayment.php:14`): Uses `Auditable`, `BelongsToBusiness`, `HasUuid`.
  - **Database Migrations & Foreign Keys**:
    - `database/migrations/2026_08_27_000038_create_invoices_tables.php`:
      - `invoices`: `business_id` (FK -> `businesses.cascadeOnDelete`), `customer_id` (FK -> `customers.restrictOnDelete`), `purchase_order_id` (nullable FK -> `purchase_orders.nullOnDelete`), `sales_order_id` (nullable FK -> `sales_orders`), `created_by` (nullable FK -> `users.nullOnDelete`), composite unique `['business_id', 'invoice_number']`, indexes on `status`, `invoice_date`, `due_date`, `customer_id`.
      - `invoice_items`: `invoice_id` (FK -> `invoices.cascadeOnDelete`), `product_id` (nullable FK -> `products.nullOnDelete`), `unit_id` (FK -> `units.restrictOnDelete`).
      - `invoice_payments`: `business_id` (FK -> `businesses.cascadeOnDelete`), `invoice_id` (FK -> `invoices.cascadeOnDelete`), `created_by` (nullable FK -> `users.nullOnDelete`).
    - `2026_09_03_000002_add_location_id_to_invoices_table.php`: Adds `location_id` (FK -> `locations.nullOnDelete`).
    - `2026_10_12_000002_add_sender_and_recipient_name_to_invoices_table.php`: Adds `sender_name`, `recipient_name`, `attachment_info`.
  - **Status Transitions**:
    - Defined in `Invoice::class`: `STATUS_DRAFT = 'draft'`, `STATUS_SENT = 'sent'`, `STATUS_UNPAID = 'unpaid'`, `STATUS_PARTIALLY_PAID = 'partially_paid'`, `STATUS_PAID = 'paid'`, `STATUS_OVERDUE = 'overdue'`, `STATUS_VOID = 'void'`.
    - Workflow: `Invoice::recalculateTotals()` (`app/Models/Invoice.php:147-201`) dynamically transitions status based on payments, due date, and void/draft protection.
- **Labor & Machines Module**:
  - **Models**:
    - `App\Models\LaborRate` (`app/Models/LaborRate.php:17`): Uses `Auditable`, `BelongsToBusiness`, `HasFactory`, `HasSlug`, `HasUuid`, `SoftDeletes`.
    - `App\Models\Machine` (`app/Models/Machine.php:16`): Uses `Auditable`, `BelongsToBusiness`, `HasFactory`, `HasSlug`, `HasUuid`, `SoftDeletes`.
    - `App\Models\CostModelLabor` (`app/Models/CostModelLabor.php`): Links `CostModel` with `LaborRate`.
    - `App\Models\CostModelMachine` (`app/Models/CostModelMachine.php`): Links `CostModel` with `Machine`.
  - **Database Migrations & Schema**:
    - `database/migrations/2026_08_17_000017_create_labor_rates_table.php`:
      - `labor_rates`: `business_id` (FK -> `businesses.cascadeOnDelete`), `currency_id` (nullable FK -> `currencies.nullOnDelete`), `basis` (`hourly`, `daily`, `monthly`, `per_unit`, `per_project`, `per_task`), `rate_amount`, `is_subcontractor`, `overtime_multiplier`, `working_days_per_month` (default 22), `working_hours_per_day` (default 8.00), `utilization_rate` (default 80.00), soft deletes, composite unique `['business_id', 'slug']`.
    - `database/migrations/2026_08_17_000018_create_machines_table.php`:
      - `machines`: `business_id` (FK -> `businesses.cascadeOnDelete`), `purchase_price`, `residual_value`, `useful_life_hours`, `maintenance_cost_per_hour`, `electricity_cost_per_hour`, soft deletes, composite unique `['business_id', 'slug']`.
  - **Calculation Services**:
    - `App\Domain\Labor\LaborCostService` (`app/Domain/Labor/LaborCostService.php:10`):
      - Monthly productive hours formula: `days * hoursPerDay * (utilization / 100)`.
      - Hourly equivalent conversion handles `hourly`, `daily`, `monthly` with division-by-zero protection (`$hours > 0 ? ($rate->rate_amount / $hours) : 0.0`).
      - Single labor assignment cost with overtime multiplier: `(regularHours * hourlyRate) + (overtimeHours * hourlyRate * multiplier)`.
    - `App\Domain\Machine\MachineCostService` (`app/Domain/Machine/MachineCostService.php:10`):
      - Hourly straight-line depreciation: `($purchase_price - $residual_value) / $useful_life_hours`.
      - Total machine cost per hour: `depreciationPerHour + maintenance_cost_per_hour + electricity_cost_per_hour`.

### 1.3 Controller-Level RBAC & View Permission Observations
- **`InvoiceWebController`**:
  - Implements `Illuminate\Routing\Controllers\HasMiddleware` (`app/Http/Controllers/Web/InvoiceWebController.php:28`).
  - Declares controller-level permission middleware (`invoices.view`, `invoices.create`, `invoices.edit`, `invoices.record_payment`, `invoices.export`, `invoices.delete`) and entitlement guard (`entitlement:invoice`).
  - Defensively validates tenant isolation with `abort_unless($invoice->business_id === $business->id, 403);` in all mutating and viewing methods (lines 240, 270, 344, 357, 415, 526, 568, 606, 647).
- **`LaborMachineWebController`**:
  - **DOES NOT IMPLEMENT** `HasMiddleware` (`app/Http/Controllers/Web/LaborMachineWebController.php:17`).
  - Relies solely on route-level middleware in `routes/owner.php:373-376`.
  - Lacks defensive tenant check `abort_unless($item->business_id === $business->id, 403);` in `updateLabor`, `destroyLabor`, `updateMachine`, `destroyMachine`.
  - **View Permission Mismatch**:
    - In `resources/views/app/labor-machines/index.blade.php:138, 337, 410, 483, 538, 619, 694`, the Blade view checks:
      `@if (\App\Support\Context::hasPermission('costing.manage'))`
    - BUT in `routes/owner.php:376`, the mutating routes require:
      `require.permission:labor_machines.manage`.
    - In `database/seeders/RbacSeeder.php:200-201`, the registered permissions are `labor_machines.view` and `labor_machines.manage`. If a role has `labor_machines.manage` but not `costing.manage`, the UI hides the action buttons even though the user has the required permission!

### 1.4 Test Suites & Static Health
- **Existing Test Suites**:
  - `tests/Feature/InvoiceModuleAuditTest.php`: 9 tests, 32 assertions — **PASSED** (exit code 0, 52.8s).
  - `tests/Feature/LaborAndMachineEngineTest.php`: 4 tests, 15 assertions — **PASSED** (exit code 0, 22.7s).
  - `tests/Feature/InvoiceDraftEditAndPartiesTest.php`, `InvoiceStockAndJournalIntegrationTest.php`, `Commerce/InvoiceTest.php`: 14 tests, 61 assertions — **PASSED** (exit code 0, 33.5s).
- **Static Health & Linter Checks**:
  - `php -l` executed on all controllers, models, and domain services: **0 syntax errors**.
  - `php artisan view:cache` executed: **"INFO Blade templates cached successfully"** (0 Blade syntax errors).
- **Testing Coverage Gap**:
  - Web routes for `LaborMachineWebController` (`labor-machines.index`, `labor-rates.store`, `labor-rates.update`, `labor-rates.destroy`, `machines.store`, `machines.update`, `machines.destroy`) have **0 feature tests**. Existing `LaborAndMachineEngineTest.php` tests only API routes (`/api/v1/labor-rates`) and service calculations.

---

## 2. Logic Chain

1. **Premise 1 (Documentation Architecture)**:
   - In COOCA, the standard for all core modules is a dedicated partial under `resources/views/app/docs/partials/sec_{module}.blade.php` with direct navigation from `resources/views/app/docs/index.blade.php`.
   - `labor-machines` currently has no presence in docs, while `invoices` is confined to a 36-line stub without 20-industry breakdown.
   - *Inference*: Both modules require dedicated partials (`sec_invoices.blade.php` and `sec_labor_machines.blade.php`) integrated into `index.blade.php` with verified route links.

2. **Premise 2 (RBAC & Controller Compliance)**:
   - The task specification (R2) and `laravel-rbac` mandate **Controller-Level RBAC** (`implements HasMiddleware`), database seeder synchronization, and view permission alignment.
   - `InvoiceWebController` already complies with `HasMiddleware`, but `LaborMachineWebController` does not implement `HasMiddleware`.
   - Furthermore, `labor-machines/index.blade.php` checks `costing.manage` instead of `labor_machines.manage`.
   - *Inference*: `LaborMachineWebController` must be updated to implement `HasMiddleware`, and `labor-machines/index.blade.php` must be aligned to check `labor_machines.manage` (or fallback `costing.manage`).

3. **Premise 3 (Multi-Tenant Security & IDOR)**:
   - In `InvoiceWebController`, every single route model binding is guarded with `abort_unless($invoice->business_id === $business->id, 403);`.
   - In `LaborMachineWebController`, `updateLabor`, `destroyLabor`, `updateMachine`, and `destroyMachine` lack explicit tenant ownership verification.
   - *Inference*: While `BelongsToBusiness` provides a global scope, explicit `abort_unless` checks must be added to `LaborMachineWebController` for defense-in-depth against IDOR vulnerabilities.

4. **Premise 4 (Production Readiness & Testing)**:
   - The backend calculation logic and database migrations for both modules are robust and tested (`InvoiceModuleAuditTest` and `LaborAndMachineEngineTest` are 100% green).
   - However, `LaborMachineWebController` web requests (HTML form submissions, redirects, flash messages, RBAC blocks) currently lack a dedicated Feature test suite.
   - *Inference*: A dedicated test suite (e.g., `tests/Feature/LaborMachineWebControllerTest.php`) should be created to assert web CRUD operations, validation errors, and permission gating for `labor-machines`.

---

## 3. Caveats

1. **Read-Only Scope**:
   - In accordance with explorer subagent constraints, no source code, Blade views, or test files were modified during this investigation.
2. **Live Browser Touch Targets**:
   - Mobile tap targets and touch zones were analyzed statically from CSS classes in Blade templates (`h-8`, `min-h-[44px]`). Physical screen rendering on real mobile hardware was not performed.
3. **Third-Party Integration**:
   - PDF generation via DomPDF was statically checked; live font rendering and printer spooling were not executed on an external printer device.

---

## 4. Conclusion

1. **Documentation Readiness**:
   - **Target File 1**: `resources/views/app/docs/partials/sec_invoices.blade.php` must be created following the 6-section Business Owner format with verified routes (`invoices.index`, `invoices.create`, `invoices.export-excel`, `sales.orders.index`, `finance.receivables`, `customers.index`).
   - **Target File 2**: `resources/views/app/docs/partials/sec_labor_machines.blade.php` must be created covering 20 industries with verified routes (`labor-machines.index`, `calculator.index`, `profitability.index`, `materials.index`, `boms.index`).
   - **Target Integration**: `resources/views/app/docs/index.blade.php` must include both partials and register them in the sticky left sidebar TOC and search dictionary.

2. **Controller-Level RBAC & Security**:
   - `LaborMachineWebController`:
     - Add `implements HasMiddleware` with explicit middleware mapping for `labor_machines.view` (index) and `labor_machines.manage` (store, update, destroy).
     - Add `abort_unless($laborRate->business_id === $business->id, 403);` and `abort_unless($machine->business_id === $business->id, 403);` across all mutation methods.
   - `labor-machines/index.blade.php`:
     - Replace `@if (\App\Support\Context::hasPermission('costing.manage'))` with `@if (\App\Support\Context::hasPermission('labor_machines.manage') || \App\Support\Context::hasPermission('costing.manage'))` across all 7 action button locations.

3. **UI / Layout Simplification**:
   - In `labor-machines/index.blade.php`, enlarge mobile action buttons from `h-8 w-8` (32px) to minimum 44px tap targets (`min-h-[44px] min-w-[44px]`).
   - Eliminate hardcoded Indonesian labels in `labor-machines` modals and align with `lang/id/labor_machines.php` and `lang/en/labor_machines.php`.

4. **Testing & QA**:
   - Create `tests/Feature/LaborMachineWebControllerTest.php` to cover web CRUD, validation, and RBAC rejection for unprivileged roles.

---

## 5. Verification Method

To independently reproduce and verify all findings:

1. **Verify Documentation Structure**:
   ```powershell
   Get-ChildItem -Path "resources\views\app\docs\partials"
   Select-String -Path "resources\views\app\docs\index.blade.php" -Pattern "sec-sales-b2b|sec_labor"
   ```
2. **Verify Route Existence**:
   ```powershell
   php artisan route:list | Select-String "invoices|labor-machines|calculator|profitability"
   ```
3. **Verify RBAC Declarations in Controllers**:
   ```powershell
   Select-String -Path "app\Http\Controllers\Web\InvoiceWebController.php" -Pattern "implements HasMiddleware"
   Select-String -Path "app\Http\Controllers\Web\LaborMachineWebController.php" -Pattern "implements HasMiddleware"
   ```
4. **Verify View Permission Discrepancy in `labor-machines/index.blade.php`**:
   ```powershell
   Select-String -Path "resources\views\app\labor-machines\index.blade.php" -Pattern "costing.manage"
   ```
5. **Run Existing Test Suites**:
   ```powershell
   php artisan test tests/Feature/InvoiceModuleAuditTest.php
   php artisan test tests/Feature/LaborAndMachineEngineTest.php
   php artisan test tests/Feature/InvoiceDraftEditAndPartiesTest.php
   php artisan test tests/Feature/InvoiceStockAndJournalIntegrationTest.php
   ```
6. **Verify Blade Compilation**:
   ```powershell
   php artisan view:cache
   php artisan view:clear
   ```
