# Project: HRM, Import, and Inventory Modules Remediation & Production Readiness

## Architecture
- **Framework**: Laravel 11 (PHP 8.2+) with Blade templates, Alpine.js, Tailwind CSS, and Spatie/custom RBAC.
- **Multi-Tenant Scoping**: All operations scoped by `Context::requireBusiness()` and `$business->id`.
- **Controller-Level RBAC**: All controllers must implement `Illuminate\Routing\Controllers\HasMiddleware` with explicit permission definitions per action.
- **View Architecture**: Modular Blade partials with single primary CTA per context, Apple HIG touch targets (≥ 44px), mobile safe area padding (`pb-28 lg:pb-12`), and view-level authorization wrappers (`@if(Context::hasPermission(...))`).
- **Documentation Architecture**: Standalone partials in `resources/views/app/docs/partials/sec_<module>.blade.php` integrated into `resources/views/app/docs/index.blade.php` with 20-industry implementation matrices and interactive route helpers.

---

## Feature Inventory

| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| F1 | Controller-Level RBAC in Import | Implement `HasMiddleware` in `ImportWebController` protecting execute and preview actions | M1 | Survey (RBAC) |
| F2 | Controller-Level RBAC in HRM API | Implement `HasMiddleware` in `HrmApiController` to prevent unauthorized salary and staff mutations | M1 | Survey (RBAC) |
| F3 | Dedicated HRM & Import Permissions Seeding | Register `hrm.*` and `import.*` permissions and role assignments in `RbacSeeder.php` | M1 | Survey (RBAC) |
| F4 | Granular HRM Web Controller Permissions | Update `HrmWebController` to check `hrm.*` permissions with fallback to `users.*` | M1 | Survey (RBAC) |
| F5 | View-Level Authorization Guards | Wrap sensitive action buttons with `@if(Context::hasPermission(...))` in HRM, Import, and Inventory views | M1 | Survey (RBAC) |
| F6 | Payroll Create Floating Bar Mobile Fix | Adjust floating action bar offset to `bottom-[calc(68px+env(safe-area-inset-bottom))] lg:bottom-4` in `hrm/payroll/create.blade.php` | M2 | Survey (UI) |
| F7 | Inventory Mobile Safe-Area Padding | Standardize bottom padding to `pb-28 lg:pb-12` in `stocks.blade.php`, `opname.blade.php`, and `transfers.blade.php` | M2 | Survey (UI) |
| F8 | Eliminate Duplicate Primary CTAs in HRM | Remove redundant CTA buttons inside card headers in `tab-corrections.blade.php` and `tab-locations.blade.php` | M2 | Survey (UI) |
| F9 | HRM Schedule Filter Visual Hierarchy | Restyle filter button to neutral/tonal in `tab-schedules.blade.php` preserving single primary CTA | M2 | Survey (UI) |
| F10 | Apple HIG Touch Targets in Import & Payslip | Upgrade sub-44px buttons to `min-h-[44px]` in `import/index.blade.php` and `hrm/payroll/payslip.blade.php` | M2 | Survey (UI) |
| F11 | Import Preview Mobile Cards & Attendance Filter Fix | Add responsive card layout for preview rows in Import Hub; fix vertical padding on mobile in `tab-attendance.blade.php` | M2 | Survey (UI) |
| F12 | Transaction Safety in Multi-Step Mutations | Wrap `quickAdjust`, `storeTransfer`, and `storeEmployee` in `DB::transaction(...)` | M4 | Survey (Audit) |
| F13 | Import Test Suite Synchronization | Synchronize sheet names in `ProductAndRecipeImportTest.php` with `DataExchangeService` | M4 | Survey (Audit) |
| F14 | Biometric Attendance Test Fixture Fix | Ensure test fixtures register face templates before clocking in in `HrmAttendanceBiometricAndExceptionTest.php` | M4 | Survey (Audit) |
| F15 | Author `sec_hrm.blade.php` Documentation | Comprehensive Business Owner guide with 20-industry matrix, payroll workflow, geofencing, and FAQ | M3 | Survey (Docs) |
| F16 | Author `sec_import.blade.php` Documentation | Comprehensive Business Owner guide with 20-industry matrix, template guide, living lookups, and FAQ | M3 | Survey (Docs) |
| F17 | Author `sec_inventory.blade.php` Documentation | Comprehensive Business Owner guide with 20-industry matrix, opname, transfers, maker-checker, and FAQ | M3 | Survey (Docs) |
| F18 | Docs Index Integration | Replace inline summaries with `@include` partials in `resources/views/app/docs/index.blade.php` and add sidebar link | M3 | Survey (Docs) |
| F19 | Comprehensive Security & Quality Gate | Validate test suite (`php artisan test`), Blade compilation (`php artisan view:cache`), and forensic audit | M4 | Survey (Gate) |

---

## Milestones

| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M0 | Survey & Technical Discovery | Comprehensive survey by 3 Explorers (UI, RBAC, Audit/Docs) | none | DONE |
| M1 | Controller-Level RBAC Enforcement & Permissions | F1, F2, F3, F4, F5: `ImportWebController`, `HrmApiController`, `RbacSeeder.php`, `HrmWebController`, View Guards | M0 | DONE |
| M2 | UI Layout Simplification & Decluttering | F6, F7, F8, F9, F10, F11: HRM views, Inventory views, Import Hub layout | M0 | DONE |
| M3 | Business Owner Documentation & User Guides | F15, F16, F17, F18: `sec_hrm.blade.php`, `sec_import.blade.php`, `sec_inventory.blade.php`, `index.blade.php` | M0 | DONE |
| M4 | Module Audit, Verification & Quality Gate | F12, F13, F14, F19: Transaction safety, test fixes, review, challenge, and forensic audit | M1, M2, M3 | DONE |

---

## Interface Contracts & Code Layout

### Exclusive File Ownership:
- **Milestone 1 (RBAC)**:
  - `app/Http/Controllers/Web/ImportWebController.php`
  - `app/Http/Controllers/Api/V1/Hrm/HrmApiController.php`
  - `app/Http/Controllers/Web/Hrm/HrmWebController.php`
  - `database/seeders/RbacSeeder.php`
  - `routes/owner.php` (lines 209–235, 465–528)
- **Milestone 2 (UI)**:
  - `resources/views/app/hrm/payroll/create.blade.php`
  - `resources/views/app/hrm/payroll/payslip.blade.php`
  - `resources/views/app/hrm/partials/tab-corrections.blade.php`
  - `resources/views/app/hrm/partials/tab-locations.blade.php`
  - `resources/views/app/hrm/partials/tab-schedules.blade.php`
  - `resources/views/app/hrm/partials/tab-attendance.blade.php`
  - `resources/views/app/hrm/index.blade.php`
  - `resources/views/app/inventory/stocks.blade.php`
  - `resources/views/app/inventory/opname.blade.php`
  - `resources/views/app/inventory/transfers.blade.php`
  - `resources/views/app/import/index.blade.php`
- **Milestone 3 (Docs)**:
  - `resources/views/app/docs/partials/sec_hrm.blade.php`
  - `resources/views/app/docs/partials/sec_import.blade.php`
  - `resources/views/app/docs/partials/sec_inventory.blade.php`
  - `resources/views/app/docs/index.blade.php`
- **Milestone 4 (Audit & Gate)**:
  - `app/Http/Controllers/Web/Inventory/InventoryWebController.php`
  - `tests/Feature/ProductAndRecipeImportTest.php`
  - `tests/Feature/HrmAttendanceBiometricAndExceptionTest.php`
