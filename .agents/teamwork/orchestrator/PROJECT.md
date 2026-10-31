# Project: COOCA UI/UX & Interface Architecture Remediation

## Architecture
- Target Scope: Blade views in `resources/views/app` identified in `docs/UI_UX_AUDIT_REPORT.md`.
- Layout Architecture: `layouts.app` shell, sticky topbar (`topbar.blade.php`), mobile bottom tab bar (`fixed inset-x-0 bottom-0 z-30 lg:hidden`), and modal dialog architectures.
- Shared Components: `<x-module-header>`, `<x-dropdown>`, standard Apple HIG modals, responsive tables.
- Remediation Framework: 4-phase engineering roadmap from Chapter 7 of `docs/UI_UX_AUDIT_REPORT.md`:
  - Phase 1: Quick Wins & Layout Normalization (Double Header elimination across 44 views, Modal Backdrop wrapper refactoring across 76 locations in 21 views).
  - Phase 2: Modal Standardization (127 cramped modals to Bento 2-column) & Table Action Consolidation (kebab overflow dropdowns).
  - Phase 3: Mobile Ergonomics & Responsive Overhaul (≥44px touch targets, mobile safe-area bottom padding in 32 views, 23 responsive grids, 13 SVG emoji replacements).
  - Phase 4: Monolith Modularization (POS `terminal.blade.php` and HRM `hrm/index.blade.php` sub-component extraction).
  - Milestone 5: Integrity & Build Verification (`php artisan view:cache`, Blade syntax check, forensic audit).

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Double Header Elimination | Remove `headerTitle`/`headerSubtitle` redundancy across 44 views using `<x-module-header>` | M1 | UI_UX_AUDIT_REPORT §3.2 |
| 2 | Modal Backdrop Architecture | Separate backdrop overlay from dialog container across 76 locations in 21 views | M1 | UI_UX_AUDIT_REPORT §5.2.1 |
| 3 | Bento XXL Modal Standardization | Upgrade 127 cramped form modals to responsive Bento 2-column canvases | M2 | UI_UX_AUDIT_REPORT §5.2.2 |
| 4 | Table Action Consolidation | Consolidate cluttered table action buttons into single primary action + kebab dropdown | M2 | UI_UX_AUDIT_REPORT §5.2.4 |
| 5 | Mobile Safe-Area Bottom Padding | Add `pb-28 sm:pb-32 lg:pb-10` padding across 32 views missing bottom cushion | M3 | UI_UX_AUDIT_REPORT §5.1 #13 |
| 6 | Responsive Multi-Column Grids | Convert 23 hardcoded `grid-cols-3/4` to responsive `grid-cols-1 sm:grid-cols-2 lg:grid-cols-...` | M3 | UI_UX_AUDIT_REPORT §5.2.7 |
| 7 | SVG Iconography Standardization | Replace raw Unicode emojis with standardized SVG components across 13 views | M3 | UI_UX_AUDIT_REPORT §5.2.8 |
| 8 | Table Action Touch Target Elevation | Ensure all table action controls meet minimum 44×44px touch bounding box | M3 | UI_UX_AUDIT_REPORT §5.2.3 |
| 9 | POS Terminal Monolith Modularization | Decompose `resources/views/app/pos/terminal.blade.php` (7,158 lines, 20 modals) into sub-views | M4 | UI_UX_AUDIT_REPORT §4.1 |
| 10 | HRM Index Monolith Modularization | Decompose `resources/views/app/hrm/index.blade.php` (5,705 lines, 15 modals) into sub-views | M4 | UI_UX_AUDIT_REPORT §4.42 |
| 11 | View Cache & Syntax Verification | Validate `php artisan view:cache` and Blade syntax across 100% modified templates | M5 | User Request R3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 0 | Survey & Deep Technical Mapping | Map line numbers, AST patterns, and sub-component boundaries across all 4 phases | none | DONE |
| 1 | Phase 1: Header & Modal Architecture | 44 Double Header views + 76 Backdrop locations in 21 views | M0 | IN_PROGRESS |
| 2 | Phase 2: Bento Modals & Table Actions | 127 Form modals + Table action dropdown consolidation | M1 | PLANNED |
| 3 | Phase 3: Mobile Ergonomics & Grids | 32 views bottom padding + 23 responsive grids + 13 SVG emojis + ≥44px targets | M2 | PLANNED |
| 4 | Phase 4: POS & HRM Monolith Modularization | `pos/terminal.blade.php` & `hrm/index.blade.php` decomposition | M3 | PLANNED |
| 5 | Phase 5: Verification & Forensic Audit | `php artisan view:cache`, Blade syntax verification, Forensic Auditor review | M4 | PLANNED |

## Interface Contracts
### View Component Contracts
- Alpine.js State Preservation: Modal triggers (`x-show`, `x-cloak`, `@click`), form submit actions, table event listeners, and data-binding variables (`x-model`, `:value`) must maintain identical identifiers and scopes.
- Route Bindings: All existing routes, URL parameters (`$order`, `$product`, etc.), and query params must remain intact.
- Multi-Tenant & Industry Directives: Directives such as `@if(tenant()->isWorkshop())`, `@if(tenant()->hasDineInFeature())`, `@can(...)`, and `isModuleEnabled(...)` must remain untouched in their respective scopes.
- Blade Modularization Sub-view Inclusions: Sub-components extracted to `partials/` or `modals/` must be included via `@include('app.pos.modals.close-shift')` or `@include('app.hrm.partials.tab-employees')`, passing parent scope `$viewData` seamlessly.
