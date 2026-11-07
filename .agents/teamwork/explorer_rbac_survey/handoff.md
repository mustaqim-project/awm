# Handoff Report: RBAC & Security Survey for Community, CRM, and Customers Modules

**Working Directory:** `c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey`  
**Target Milestone:** RBAC & Security Survey for Community, CRM, and Customers Modules  
**Author:** Teamwork Explorer (RBAC & Security Survey Explorer)  
**Recipient / Parent:** `8d27681a-70c2-4f72-a481-2fbed64c6df8`  
**Full Findings Report:** `c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey\survey_report.md`  

---

## 1. Observation

Direct observations and verbatim code references from the COOCA codebase:

### 1.1. Controllers & Authorization Enforcement
1. **`App\Http\Controllers\Web\CustomerWebController` (`app/Http/Controllers/Web/CustomerWebController.php` lines 18-33):**
   - Implements `Illuminate\Routing\Controllers\HasMiddleware`.
   - Method `middleware()` defines 6 middleware mappings:
     ```php
     public static function middleware(): array
     {
         return [
             new Middleware('require.permission:customers.view', only: ['index', 'downloadTemplate']),
             new Middleware('require.permission:customers.export', only: ['export']),
             new Middleware('require.permission:customers.create', only: ['store', 'previewImport', 'executeImport']),
             new Middleware('require.permission:customers.edit', only: ['update']),
             new Middleware('require.permission:customers.delete', only: ['destroy']),
             new Middleware('entitlement:customer', only: ['store', 'executeImport']),
         ];
     }
     ```
   - All 8 public controller actions are mapped.
   - Tenant isolation & IDOR guards:
     - `index`: `Customer::where('business_id', $business->id)`
     - `store`: `'business_id' => $business->id`
     - `update` (line 149): `abort_unless($customer->business_id === $business->id, 403);`
     - `destroy` (line 281): `abort_unless($customer->business_id === $business->id, 403);` and blocks deletion if `current_credit_balance > 0` (lines 284-296).

2. **`App\Http\Controllers\Web\Crm\CrmWebController` (`app/Http/Controllers/Web/Crm/CrmWebController.php` lines 22-33):**
   - Implements `HasMiddleware`.
   - Method `middleware()` defines:
     ```php
     public static function middleware(): array
     {
         return [
             new Middleware('require.permission:crm.view', only: ['members', 'pointHistories', 'vouchers']),
             new Middleware('require.permission:crm.manage', only: ['recordCreditPayment', 'storeVoucher', 'toggleVoucher']),
         ];
     }
     ```
   - All 6 public controller actions are mapped.
   - Tenant isolation & IDOR guards:
     - `members`: `Customer::where('business_id', $business->id)`
     - `pointHistories` (line 90): `abort_unless($customer->business_id === $business->id, 403);`
     - `recordCreditPayment` (line 102): `abort_unless($customer->business_id === $business->id, 403);`
     - `vouchers`: `Voucher::where('business_id', $business->id)`
     - `storeVoucher` (line 185): `'business_id' => $business->id`
     - `toggleVoucher` (line 221): `abort_unless($voucher->business_id === $business->id, 403);`

3. **`App\Http\Controllers\Web\CommunityWebController` (`app/Http/Controllers/Web/CommunityWebController.php` lines 36-46):**
   - Declares `implements HasMiddleware`.
   - Method `middleware()` returns an empty array:
     ```php
     public static function middleware(): array
     {
         return [];
     }
     ```
   - **Critical observation:** All 16 public actions (`index`, `events`, `getEventTicket`, `showEventPayment`, `checkEventPaymentStatus`, `simulateEventPayment`, `store`, `toggleLike`, `toggleRepost`, `comment`, `markNotificationsRead`, `destroy`, `registerEvent`, `cancelEventRegistration`, `checkinLocation`, `checkinBarcode`) execute with NO controller-level permission check.

### 1.2. Routes Registry
1. **`routes/owner.php` lines 240-248 (Customers):**
   - Routes declare `->middleware('require.permission:...')` directly on routes, which duplicates what is already encapsulated inside `CustomerWebController::middleware()`.
2. **`routes/owner.php` lines 830-838 (CRM):**
   - Routes declare `Route::middleware('require.permission:crm.view')->group(...)` and `->middleware('require.permission:crm.manage')`, duplicating `CrmWebController::middleware()`.
3. **`routes/owner.php` lines 546-565 (Community):**
   - All 16 routes (`GET /community`, `POST /community`, `POST /community/{post}/like`, `POST /community/{post}/comment`, `POST /community/{post}/repost`, `POST /community/notifications/read`, `DELETE /community/{post}`, `GET /community/events`, `POST /community/events/{event}/register`, `POST /community/events/{event}/cancel`, `GET /community/events/{event}/ticket`, `POST /community/events/{event}/checkin-location`, `POST /community/events/{event}/checkin-barcode`, `GET /community/events/registrations/{registration}/payment`, `GET /community/events/registrations/{registration}/payment-status`, `POST /community/events/registrations/{registration}/simulate-pay`) have ZERO attached permission middleware.

### 1.3. Seeders & Permissions
In `database/seeders/RbacSeeder.php`:
1. `permissionMatrix()` lines 24-30 defines:
   - `customers.view`, `customers.create`, `customers.edit`, `customers.delete`, `customers.export`
   - `crm.view`, `crm.manage`
2. `permissionMatrix()` defines **ZERO** permissions matching `community.*`.
3. `roleDefinitions()` lines 202-427 maps customers and crm to standard roles (`owner`, `manager`, `admin`, `cashier`, `staff`, `viewer`), but no roles have community permissions.

### 1.4. Blade View Guards
1. **`resources/views/app/customers/index.blade.php`:**
   - Lines 922-1080: Modal 2 Template (`<!-- MODAL 2: EDIT PELANGGAN -->`) is completely unwrapped; rendered in DOM without `@can('customers.edit')`.
   - Lines 1205-1209: The Edit button inside Modal 3 (`openEditModal(selectedCustomer.id)`) is unwrapped; visible to any user who can view customer details.
2. **`resources/views/app/crm/members.blade.php` & `vouchers.blade.php`:**
   - Fully guarded with `@if (\App\Support\Context::hasPermission('...'))` for all action buttons, credit settlement, and promo vouchers.
3. **`resources/views/app/community/index.blade.php`, `events.blade.php`, `event_payment.blade.php`:**
   - **Zero `@can` or permission checks anywhere in the files.**
   - Unwrapped action controls include: post composer form (`community.store`), delete button, like/repost/comment forms, event registration form (`community.events.register`), QRIS pay links, ticket GPS/barcode check-in buttons, cancel registration form, and simulate payment form.

---

## 2. Logic Chain

1. **Premise 1:** In Laravel 11 and COOCA architectural standard, authorization should be encapsulated in controllers using `HasMiddleware` and synchronized with Blade view guards and database seeders.
2. **Premise 2:** `AppServiceProvider` integrates Gate with tenant context via `Gate::before(fn ($user, $ability) => Context::hasPermission($ability))`. Therefore, `@can('permission.slug')` and `Context::hasPermission('permission.slug')` both work as valid authorization directives in Blade.
3. **Deduction 1 (Community Vulnerability):**
   - Because `CommunityWebController::middleware()` returns `[]`, AND `routes/owner.php` has no route-level middleware, AND `RbacSeeder.php` has no permissions, ANY authenticated user (including `viewer`, `cashier`, `warehouse`) can perform write operations on community posts and register/cancel events.
   - Therefore, `community.view`, `community.post`, `community.events.register`, and `community.manage` MUST be registered in `RbacSeeder.php`, wired into `CommunityWebController::middleware()`, and wrapped around action buttons in `resources/views/app/community/*.blade.php`.
4. **Deduction 2 (Customers & CRM Refinement):**
   - `CustomerWebController` and `CrmWebController` already have robust `HasMiddleware` declarations and IDOR protections.
   - However, in `customers/index.blade.php`, Modal 2 (Edit Modal template) and line 1205 (Detail modal Edit button) leak edit access to users without `customers.edit`. Wrapping them in `@can('customers.edit')` eliminates this leak.
   - Route-level middleware in `routes/owner.php` for Customers and CRM can be cleaned up to avoid redundancy.

---

## 3. Caveats

1. **No Source Code Changes Made:** As an explorer agent with read-only constraints, no application code, routes, or seeders were altered during this survey.
2. **Simulate Payment Endpoint:** The endpoint `community.events.payment.simulate` currently checks `abort_unless(app()->environment('local', 'testing'), 403)`. It should also require `community.events.register` or `community.manage` to prevent unentitled dev accounts from executing test payouts.
3. **Module Toggleability:** `community` is not currently registered as a toggleable module in `ModuleRegistry.php`. According to `Business::isPermissionEnabled()`, unmapped permissions are treated as universal core permissions, meaning they are enabled for all business subscription tiers. If Community should become a toggleable module in the future, it should also be added to `ModuleRegistry.php`.

---

## 4. Conclusion

1. **CRM module** is production-ready from an RBAC perspective.
2. **Customers module** requires minor Blade view patches (wrapping Modal 2 and the detail edit button with `@can('customers.edit')`).
3. **Community module** requires full RBAC implementation:
   - Register 4 permissions (`community.view`, `community.post`, `community.events.register`, `community.manage`) in `RbacSeeder.php`.
   - Populate `CommunityWebController::middleware()` with `require.permission` mappings.
   - Add `@can(...)` checks to action elements in `resources/views/app/community/index.blade.php`, `events.blade.php`, and `event_payment.blade.php`.
4. Comprehensive findings and code remediation snippets have been saved to `c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey\survey_report.md`.

---

## 5. Verification Method

To independently reproduce and verify these findings:

1. **Inspect Controller Middleware Declarations:**
   - Run: `Select-String -Path "app/Http/Controllers/Web/CommunityWebController.php" -Pattern "middleware" -Context 0,7`
   - Observe `return [];` in `CommunityWebController::middleware()`.
   - Run: `Select-String -Path "app/Http/Controllers/Web/CustomerWebController.php" -Pattern "middleware" -Context 0,12`
   - Observe full mappings in `CustomerWebController::middleware()`.

2. **Inspect RbacSeeder Permissions:**
   - Run: `Select-String -Path "database/seeders/RbacSeeder.php" -Pattern "community"`
   - Observe zero matching entries in `RbacSeeder.php`.

3. **Inspect Blade Views for Missing Guards:**
   - Inspect `resources/views/app/customers/index.blade.php` at line 922 and line 1205.
   - Inspect `resources/views/app/community/index.blade.php` at line 509 (`<form method="POST" action="{{ route('community.store') }}"`).

4. **Execute Current Automated Test Suites:**
   ```bash
   php artisan test tests/Feature/CommunityFeatureTest.php
   php artisan test tests/Feature/CustomerWebFeatureTest.php tests/Feature/CrmWebFeatureTest.php
   ```
   Both test suites currently pass (13 tests in CommunityFeatureTest, 11 tests in Customer/Crm test).
