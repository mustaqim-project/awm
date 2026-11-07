# Project: WhatsApp Module UI Simplification & RBAC Enforcement

## Architecture
- **Framework**: Laravel 11, PHP 8.2+, Blade Templating, Alpine.js, Tailwind CSS
- **Module Path**: `resources/views/app/whatsapp/`
  - `index.blade.php` (Gateway Cockpit, connection cards, service toggles, auto-receipt, test console, Meta message catalog)
  - `broadcast.blade.php` (Broadcast campaigns hub, delivery metrics, campaign listing, composer modal sheet)
  - `broadcast_detail.blade.php` (Delivery report, KPI cards, message preview, recipient status table, live polling)
  - `logs.blade.php` (Message transmission logs, status filters, log inspector sheet, storage pruning modal)
- **RBAC Architecture**:
  - Tenant RBAC: `App\Models\Permission`, `App\Models\Role`, `App\Support\Context::hasPermission($ability)`
  - Permissions: `whatsapp.view` (read-only view access), `whatsapp.manage` (full gateway connection, settings, test send, broadcasts, storage pruning), `reports.export` (Excel export)
  - Gate Bridge: `Gate::before` in `AppServiceProvider.php` bridging `App\Models\User` to `Context::hasPermission($ability)` for native Blade `@can` support
  - Controller Guards: Laravel 11 `HasMiddleware` in `WhatsAppWebController`, `WhatsAppBroadcastWebController`, and `MetaWhatsAppOnboardingController`
  - HTTP 403 Forbidden: `RequirePermission` middleware returns 403 on JSON/API requests (`expectsJson()`); unauthorized web GET requests redirect to `portal`
- **UI Design System**:
  - Apple HIG / Bento architecture: clean layout, distinct primary/secondary/destructive action hierarchy, 44×44px minimum touch targets, zero overlapping elements across desktop & mobile.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Gate::before User RBAC Bridge | Enable native Blade `@can` for tenant `User` via `Context::hasPermission($ability)` in `AppServiceProvider.php` | M1 (RBAC Core) | survey (all explorers) |
| 2 | Navigation Registry Permission Fix | Set `whatsapp-gateway` sidebar item permission to `whatsapp.view` so view-only users can access the gateway | M1 (RBAC Core) | survey (explorer_wa_rbac) |
| 3 | Controller Middleware Guards | Standardize controller-level `HasMiddleware` guards across WhatsApp controllers for strict permission enforcement | M1 (RBAC Core) | survey (explorer_wa_rbac, ORIGINAL_REQUEST R2) |
| 4 | `index.blade.php` UI Simplification & RBAC | Demote header button, remove duplicate inbox button, guard disconnect/toggle/receipt/test/catalog actions with `@can`, disable read-only form controls | M2 (UI Views) | survey (explorer_wa_ui, ORIGINAL_REQUEST R1, R2) |
| 5 | `broadcast.blade.php` UI & Touch Targets | Fix table eye action button touch target to 44px (`min-w-[44px] min-h-[44px]`), apply `@can('whatsapp.manage')` on header, empty state & blast submit | M2 (UI Views) | survey (explorer_wa_ui, ORIGINAL_REQUEST R1, R2) |
| 6 | `broadcast_detail.blade.php` RBAC Guards | Guard header "Buat Siaran Baru" and "Buat Siaran Serupa" CTA with `@can('whatsapp.manage')` | M2 (UI Views) | survey (explorer_wa_ui, ORIGINAL_REQUEST R2) |
| 7 | `logs.blade.php` UI & RBAC Enforcement | Fix table eye button to 44px hit area, guard Excel export with `@can('reports.export')`, guard storage pruning button & modal with `@can('whatsapp.manage')` | M2 (UI Views) | survey (explorer_wa_ui, ORIGINAL_REQUEST R1, R2) |
| 8 | WhatsApp RBAC Security Test Suite | Create `WhatsAppRbacSecurityTest.php` asserting 403 Forbidden on JSON endpoints, redirection on web, and Blade UI element hiding | M3 (Verification) | survey (explorer_wa_test, ORIGINAL_REQUEST R2) |
| 9 | Blade Compilation & Regression Verification | Verify `php artisan view:cache` and run all 28 existing tests in `MerchantWhatsAppWebFeatureTest.php` without regression | M3 (Verification) | survey (explorer_wa_test, ORIGINAL_REQUEST R1, R2) |
| 10 | Forensic Integrity Audit Gate | Execute `teamwork_auditor` check verifying genuine implementations, zero dummy facades, and clean RBAC/UI architecture | M3 (Verification) | orchestrator audit gate |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 0 | Phase 0: Survey & Technical Discovery | Map views, controllers, seeders, and test baseline via 3 parallel Explorers | none | DONE |
| 1 | Milestone 1: RBAC Core & Controller Guards | `AppServiceProvider.php` Gate bridge, `NavigationRegistry.php`, and WhatsApp controller middleware guards | M0 | DONE |
| 2 | Milestone 2: WhatsApp Views UI Simplification & RBAC | Refactor `index`, `broadcast`, `broadcast_detail`, and `logs` Blade templates per Bento/Apple HIG & `@can` directives | M1 | DONE |
| 3 | Milestone 3: Test Verification & Forensic Audit Gate | `WhatsAppRbacSecurityTest`, `view:cache`, full regression test, and Forensic Audit gate | M1, M2 | DONE |

## Interface Contracts
### Gate & Permissions Contract
- Ability `whatsapp.view`: Read-only access to WhatsApp gateway dashboard, status, and message logs.
- Ability `whatsapp.manage`: Full operational permissions (connect/disconnect WhatsApp session, update receipt settings, send test message, create and send broadcast campaigns, prune log storage).
- Ability `reports.export`: Permission to trigger Excel spreadsheet export of message logs.
- Gate Hook: In `AppServiceProvider.php`:
  ```php
  Gate::before(function ($user, string $ability) {
      if ($user instanceof \App\Models\Admin) {
          return $user->isSuperAdmin() ? true : ($user->hasPermission($ability) ? true : null);
      }
      if ($user instanceof \App\Models\User) {
          return \App\Support\Context::hasPermission($ability) ? true : null;
      }
      return null;
  });
  ```

### HTTP Authorization Contract
- Unauthorized JSON/AJAX requests (`Accept: application/json` or `isJson()`): HTTP 403 Forbidden with payload:
  `{ "success": false, "error_code": "FORBIDDEN_PERMISSION", "message": "..." }`
- Unauthorized Web GET requests: Redirect to `route('portal')` with session error flash.

### UI Layout & Button Hierarchy Contract
- Primary Actions: Solid `#007AFF` blue, white text, 44px min touch target, reserved for primary CTA per section.
- Secondary Actions: Tonal gray/white background (`bg-black/[0.05] dark:bg-white/[0.08]`), 44px min touch target.
- Destructive Actions: Tonal red (`bg-[#FF3B30]/10 text-[#FF3B30]`), 44px min touch target, guarded by confirmation and `@can('whatsapp.manage')`.
- Table Row Actions: `min-w-[44px] min-h-[44px]` touch target, centered SVG icon.
- Read-Only State: When user lacks `whatsapp.manage`, mutating form inputs and toggles are disabled (`disabled` attribute, `cursor-not-allowed`, reduced opacity), and action buttons are omitted via `@can`.

## Code Layout
- `app/Providers/AppServiceProvider.php` (Gate::before bridge)
- `app/Support/Navigation/NavigationRegistry.php` (sidebar permissions)
- `app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php` (controller middleware)
- `app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php` (controller middleware)
- `app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php` (controller middleware)
- `resources/views/app/whatsapp/index.blade.php` (view refactor)
- `resources/views/app/whatsapp/broadcast.blade.php` (view refactor)
- `resources/views/app/whatsapp/broadcast_detail.blade.php` (view refactor)
- `resources/views/app/whatsapp/logs.blade.php` (view refactor)
- `tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php` (new test suite)
