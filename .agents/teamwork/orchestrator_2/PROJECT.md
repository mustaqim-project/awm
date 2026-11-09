# Project: COOCA SaaS ERP — Invoices & Labor-Machines Production Readiness

## Architecture
- **Framework**: Laravel 11.x on PHP 8.2+, Blade, Alpine.js, Tailwind CSS, Lucide Icons.
- **Target Modules**:
  - `Invoices Module` (`app/Http/Controllers/Web/InvoiceWebController.php`, `resources/views/app/invoices/`, `app/Models/Invoice.php`, `app/Models/InvoiceItem.php`)
  - `Labor & Machines Module` (`app/Http/Controllers/Web/LaborMachineWebController.php`, `resources/views/app/labor-machines/`, `app/Models/LaborRate.php`, `app/Models/Machine.php`)
  - `Documentation System` (`app/Http/Controllers/Web/DocumentationWebController.php`, `resources/views/app/docs/`)
- **Authorization & Security**: Spatie / Native multi-tenant RBAC with `Context::hasPermission()`, `Context::isOwner()`, `Context::isAdminOrOwner()`, Controller-Level `HasMiddleware`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Controller-Level RBAC in LaborMachineWebController | Implement `HasMiddleware` with `labor_machines.view` and `labor_machines.manage` | M1 | Survey Explorer 2 |
| 2 | Tenant Isolation & IDOR Protection in LaborMachineWebController | Add defensive `abort_unless($record->business_id === $business->id, 403)` on `updateLabor`, `destroyLabor`, `updateMachine`, `destroyMachine` | M1 | Survey Explorer 2 & 3 |
| 3 | Clean Routing Migration | Remove redundant route-level `require.permission` in `routes/owner.php` where controller handles it | M1 | Survey Explorer 2 |
| 4 | Permission Synchronization in labor-machines/index | Change incorrect `costing.manage` checks to `@can('labor_machines.manage')` | M1 | Survey Explorer 1, 2, 3 |
| 5 | Critical JS Syntax Fix in invoices/print.blade.php | Fix unclosed `else` block & function at lines 472-477 breaking PDF & print triggers | M2 | Survey Explorer 1 |
| 6 | Primary Action Consolidation in create/edit invoices | Eliminate competing duplicate solid `#007AFF` Save buttons; establish clear primary vs secondary hierarchy | M2 | Survey Explorer 1 |
| 7 | Action Consolidation in labor-machines/index | Consolidate 4 competing "Add" buttons into context-aware primary button in header and clean section headers | M2 | Survey Explorer 1 |
| 8 | View-Level Auth Gating in invoices create & edit | Wrap top Save button with `@can('invoices.create')` / `@can('invoices.edit')` | M2 | Survey Explorer 1 & 2 |
| 9 | View-Level Auth Gating in invoices index & show | Wrap PDF and Print links with `@can('invoices.export')` or `@can('invoices.view')` | M2 | Survey Explorer 1 & 2 |
| 10 | Mobile Touch Target Remediation in invoices/index | Replace cramped 32px 4-button mobile layout with clean chevron link to detail and overflow menu | M2 | Survey Explorer 1 |
| 11 | Mobile Touch Target & Dangerous Proximity Fix in labor-machines | Upgrade Edit/Delete touch targets to min-h-[44px] and ensure >=8px spacing to prevent accidental deletes | M2 | Survey Explorer 1 |
| 12 | Modal Canvas Expansion in labor-machines | Expand `sm:max-w-md` modals to comfortable Bento layout (`max-w-4xl`) | M2 | Survey Explorer 1 |
| 13 | Table Dropdown Overflow Clipping Fix | Ensure action dropdown in `invoices/index.blade.php` does not get clipped by table scroll container (`x-teleport="body"`) | M2 | Survey Explorer 1 |
| 14 | i18n Localization in labor-machines | Wrap 40+ hardcoded Indonesian strings with `__()` helpers | M2 | Survey Explorer 1 |
| 15 | Business Owner Docs: Labor-Machines | Create `resources/views/app/docs/partials/sec_labor_machines.blade.php` with UMKM POV, 20-industry matrix, and rate calculation formulas | M3 | Survey Explorer 3 |
| 16 | Business Owner Docs: Invoices | Create `resources/views/app/docs/partials/sec_invoices.blade.php` replacing 36-line stub with full guide, status lifecycles, and verified route links | M3 | Survey Explorer 3 |
| 17 | Documentation System Integration | Register both partials in `resources/views/app/docs/index.blade.php` (sidebar TOC, search index, and navigation) | M3 | Survey Explorer 3 |
| 18 | Dedicated Feature Test Suite for LaborMachineWebController | Create `tests/Feature/LaborMachineWebControllerTest.php` testing auth, tenant isolation, RBAC, and CRUD | M4 | Survey Explorer 3 |
| 19 | Comprehensive Module Audit & Verification | Run full test suite, syntax checks (`php -l`), and verify production readiness per `laravel-module-audit` | M4 | Survey Explorer 3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | M1: Controller-Level RBAC & Tenant Security | `LaborMachineWebController.php`, `routes/owner.php`, permission alignment in `labor-machines/index.blade.php` | none | DONE |
| 2 | M2: UI Simplification, Layout Ergonomics & View Auth | Blade templates in `resources/views/app/invoices/` and `resources/views/app/labor-machines/` | M1 | IN_PROGRESS |
| 3 | M3: Business Owner User Documentation | `resources/views/app/docs/partials/sec_labor_machines.blade.php`, `sec_invoices.blade.php`, `resources/views/app/docs/index.blade.php` | none | PLANNED |
| 4 | M4: Module Production Readiness Audit & Verification | `tests/Feature/LaborMachineWebControllerTest.php`, full test execution, PHP linting, final gate pass | M1, M2, M3 | PLANNED |

## Interface Contracts
### LaborMachineWebController RBAC
- Controller implements `Illuminate\Routing\Controllers\HasMiddleware`:
  - `require.permission:labor_machines.view`: `index`
  - `require.permission:labor_machines.manage`: `storeLabor`, `updateLabor`, `destroyLabor`, `storeMachine`, `updateMachine`, `destroyMachine`
- Tenant Isolation:
  - `updateLabor`: `abort_unless($laborRate->business_id === $business->id, 403);`
  - `destroyLabor`: `abort_unless($laborRate->business_id === $business->id, 403);`
  - `updateMachine`: `abort_unless($machine->business_id === $business->id, 403);`
  - `destroyMachine`: `abort_unless($machine->business_id === $business->id, 403);`

### View Permissions Mapping
- Invoices:
  - Create: `@can('invoices.create')`
  - Edit: `@can('invoices.edit')`
  - Delete: `@can('invoices.delete')`
  - Record Payment: `@can('invoices.record_payment')`
  - Export / Print: `@can('invoices.export')` or `@can('invoices.view')`
- Labor & Machines:
  - View: `@can('labor_machines.view')`
  - Manage (Add/Edit/Delete Rates & Machines): `@can('labor_machines.manage')`

## Code Layout
- Controllers:
  - `app/Http/Controllers/Web/InvoiceWebController.php`
  - `app/Http/Controllers/Web/LaborMachineWebController.php`
- Routes:
  - `routes/owner.php`
- Views:
  - `resources/views/app/invoices/index.blade.php`
  - `resources/views/app/invoices/create.blade.php`
  - `resources/views/app/invoices/edit.blade.php`
  - `resources/views/app/invoices/show.blade.php`
  - `resources/views/app/invoices/print.blade.php`
  - `resources/views/app/labor-machines/index.blade.php`
- Documentation:
  - `resources/views/app/docs/index.blade.php`
  - `resources/views/app/docs/partials/sec_labor_machines.blade.php`
  - `resources/views/app/docs/partials/sec_invoices.blade.php`
- Tests:
  - `tests/Feature/LaborMachineWebControllerTest.php`
  - `tests/Feature/Invoice/InvoiceModuleAuditTest.php`
