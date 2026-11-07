# Comprehensive RBAC & Security Survey Report: Community, CRM, and Customers Modules

**Repository:** `cooca_core` (Multi-tenant SaaS ERP for Indonesian MSMEs)  
**Survey Date:** 2026-11-07  
**Scope:** Community, CRM, and Customers modules (Controllers, Routes, Seeders, Blade View Guards, IDOR & Multi-tenant isolation)  
**Standard Compliance:** Laravel 11+ `HasMiddleware` Controller-Level RBAC, Clean Routing, Single Source of Truth `RbacSeeder.php`, Apple HIG & Zero Slop Blade View Guards.

---

## 1. Executive Summary

| Module | Controller-Level RBAC Status | Route-Level RBAC Status | Seeder & Matrix Status | Blade View Guards Status | Production Readiness Risk |
|---|---|---|---|---|---|
| **Customers** | ✅ Fully guarded via `HasMiddleware` (5 permissions, 8 methods) | ⚠️ Redundant route-level middleware declarations | ✅ Fully registered in `RbacSeeder.php` | ⚠️ 2 missing guards (Edit modal template & Detail modal edit button) | **LOW** (Minor UI leaks) |
| **CRM & Loyalty** | ✅ Fully guarded via `HasMiddleware` (2 permissions, 6 methods) | ⚠️ Redundant route-level middleware declarations | ✅ Fully registered in `RbacSeeder.php` | ✅ Fully guarded across all actions | **LOW** (Clean & robust) |
| **Community** | ❌ **CRITICAL DEFECT:** `middleware()` returns `[]` (0/16 methods guarded) | ❌ **CRITICAL DEFECT:** Zero permission middleware on all 16 routes | ❌ **CRITICAL DEFECT:** Zero `community.*` permissions exist in `RbacSeeder.php` | ❌ **CRITICAL DEFECT:** Zero `@can` / permission checks in all 3 Community views | **HIGH** (Unrestricted access for all authenticated roles) |

### Key Takeaways:
1. **Community module is currently completely unprotected by RBAC:**
   - Any authenticated business member (cashier, staff, warehouse, viewer) can create posts, comment, repost, like, delete posts, register for paid/free events, cancel registrations, check-in, and simulate payments without any role or permission verification.
   - `CommunityWebController` declares `implements HasMiddleware`, but its `middleware()` method returns an empty array `return [];`.
   - `database/seeders/RbacSeeder.php` defines zero `community.*` permissions.
   - Blade views in `resources/views/app/community/` contain zero authorization guards (`@can` or `Context::hasPermission`).
2. **Customers module is structurally strong with minor view guard gaps:**
   - Controller properly implements `HasMiddleware` with granular permissions (`customers.view`, `customers.create`, `customers.edit`, `customers.delete`, `customers.export`, `entitlement:customer`).
   - Tenant isolation and IDOR protections are strictly enforced.
   - **Gaps:** In `resources/views/app/customers/index.blade.php`, Modal 2 (Edit Customer modal sheet) is rendered in HTML without `@can('customers.edit')`, and the Edit button in Modal 3 (Customer Detail view) lacks permission protection.
3. **CRM module is well-guarded:**
   - Properly encapsulated via `HasMiddleware` with `crm.view` and `crm.manage`.
   - All actions in `members.blade.php` and `vouchers.blade.php` are guarded.
   - Route-level middleware declarations in `routes/owner.php` are redundant with controller definitions.

---

## 2. Controllers & Authorization Architecture Audit

In COOCA, application authorization follows a 2-tier model:
- **`AppServiceProvider` Gate Integration:**
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
  This allows both standard Laravel `@can('slug')` / `$this->authorize('slug')` and `Context::hasPermission('slug')` to evaluate tenant permissions.
- **Controller-Level RBAC Standard:** Controllers implement `Illuminate\Routing\Controllers\HasMiddleware` and define static `middleware(): array` mapping permissions to actions using `only: [...]`.

### 2.1. `App\Http\Controllers\Web\CustomerWebController`
- **File:** `app/Http/Controllers/Web/CustomerWebController.php` (310 lines)
- **Interface:** `implements HasMiddleware`
- **Middleware Definition:**
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
- **Method Coverage Analysis:**
  | Method | Action Type | Enforced Permission | Tenant / IDOR Guard | Status |
  |---|---|---|---|---|
  | `index(Request $request)` | Read (List & Metric) | `customers.view` | `Customer::where('business_id', $business->id)` | ✅ Secure |
  | `store(Request $request)` | Write (Create) | `customers.create` + `entitlement:customer` | `'business_id' => $business->id` | ✅ Secure |
  | `update(Request $request, Customer $customer)` | Write (Update) | `customers.edit` | `abort_unless($customer->business_id === $business->id, 403)` | ✅ Secure |
  | `destroy(Customer $customer)` | Write (Delete) | `customers.delete` | `abort_unless($customer->business_id === $business->id, 403)` + Credit debt check | ✅ Secure |
  | `export(Request $request)` | Read / Stream | `customers.export` | `Customer::where('business_id', $business->id)` | ✅ Secure |
  | `downloadTemplate()` | Download Template | `customers.view` | Scoped via DataExchangeService | ✅ Secure |
  | `previewImport(Request $request)` | Read / Parse | `customers.create` | Scoped via DataExchangeService | ✅ Secure |
  | `executeImport(Request $request)` | Write (Batch) | `customers.create` + `entitlement:customer` | Scoped via DataExchangeService | ✅ Secure |
- **Findings:**
  - 100% of methods are protected by controller-level middleware.
  - IDOR and tenant boundary checks are rigorous.
  - Soft-deletion validation guards prevent deleting customers with outstanding receivables (`current_credit_balance > 0`).

### 2.2. `App\Http\Controllers\Web\Crm\CrmWebController`
- **File:** `app/Http/Controllers/Web/Crm/CrmWebController.php` (241 lines)
- **Interface:** `implements HasMiddleware`
- **Middleware Definition:**
  ```php
  public static function middleware(): array
  {
      return [
          new Middleware('require.permission:crm.view', only: ['members', 'pointHistories', 'vouchers']),
          new Middleware('require.permission:crm.manage', only: ['recordCreditPayment', 'storeVoucher', 'toggleVoucher']),
      ];
  }
  ```
- **Method Coverage Analysis:**
  | Method | Action Type | Enforced Permission | Tenant / IDOR Guard | Status |
  |---|---|---|---|---|
  | `members(Request $request)` | Read (Members list) | `crm.view` | `Customer::where('business_id', $business->id)` | ✅ Secure |
  | `pointHistories(Customer $customer)` | Read (Points history) | `crm.view` | `abort_unless($customer->business_id === $business->id, 403)` | ✅ Secure |
  | `vouchers(Request $request)` | Read (Vouchers list) | `crm.view` | `Voucher::where('business_id', $business->id)` | ✅ Secure |
  | `recordCreditPayment(...)` | Write (Financial) | `crm.manage` | `abort_unless($customer->business_id === $business->id, 403)` + Debt balance check + AuditLog | ✅ Secure |
  | `storeVoucher(Request $request)` | Write (Promo) | `crm.manage` | `'business_id' => $business->id` + Unique code per business + AuditLog | ✅ Secure |
  | `toggleVoucher(..., Voucher $voucher)` | Write (Status) | `crm.manage` | `abort_unless($voucher->business_id === $business->id, 403)` + AuditLog | ✅ Secure |
- **Findings:**
  - 100% of methods are protected.
  - Critical financial mutation (`recordCreditPayment`) triggers automated journal creation via `LoyaltyService` and logs to `AuditLog`.

### 2.3. `App\Http\Controllers\Web\CommunityWebController`
- **File:** `app/Http/Controllers/Web/CommunityWebController.php` (911 lines)
- **Interface:** `implements HasMiddleware`
- **Middleware Definition:**
  ```php
  public static function middleware(): array
  {
      return []; // CRITICAL GAP: Empty array returns NO middleware
  }
  ```
- **Method Coverage Analysis:**
  | Method | Action Type | Current Protection | Recommended Permission | Vulnerability Assessment |
  |---|---|---|---|---|
  | `index(Request $request)` | Read (Feed & Events) | None (`return []`) | `community.view` | Unauthorized roles can access discussion forum and internal posts |
  | `events(Request $request)` | Read (Event Agenda) | None (`return []`) | `community.view` | Unauthorized roles can access event listing |
  | `getEventTicket(..., CommunityEvent $event)` | Read (Ticket QR/Barcode) | User check only | `community.view` | Anyone can retrieve tickets |
  | `showEventPayment(..., CommunityEventRegistration $reg)` | Read (Payment page) | User check only | `community.view` | Accessible without permission |
  | `checkEventPaymentStatus(...)` | Read (Polling) | User check only | `community.view` | Accessible without permission |
  | `store(Request $request)` | Write (Create Post) | None (`return []`) | `community.post` | Cashiers/viewers can spam posts to business network |
  | `toggleLike(..., CommunityPost $post)` | Write (Like) | None (`return []`) | `community.post` | Accessible without permission |
  | `toggleRepost(..., CommunityPost $post)` | Write (Repost) | None (`return []`) | `community.post` | Accessible without permission |
  | `comment(..., CommunityPost $post)` | Write (Comment) | None (`return []`) | `community.post` | Any user can publish comments |
  | `markNotificationsRead(Request $request)` | Write (State) | None (`return []`) | `community.view` | Accessible without permission |
  | `destroy(..., CommunityPost $post)` | Write (Delete Post) | `$post->owner_id === $user->id` | `community.post` (or `community.manage` for admin) | Author check only, no RBAC guard |
  | `registerEvent(..., CommunityEvent $event)` | Write (Event Registration) | None (`return []`) | `community.events.register` | Unentitled staff can register company for paid/free seats |
  | `cancelEventRegistration(...)` | Write (Cancel Registration) | User check only | `community.events.register` | Accessible without permission |
  | `checkinLocation(..., CommunityEvent $event)` | Write (GPS Checkin) | User check only | `community.events.register` | Accessible without permission |
  | `checkinBarcode(..., CommunityEvent $event)` | Write (Barcode Checkin) | User check only | `community.events.register` | Accessible without permission |
  | `simulateEventPayment(...)` | Write (Payment bypass) | Local/testing env check | `community.events.register` / `community.manage` | Bypasses payment without role restriction in dev |

---

## 3. Routes Audit & Clean Routing Analysis

Routes are defined in `routes/owner.php` and loaded in `routes/web.php` under the owner middleware stack:
`['web', 'auth:web', 'wa.otp', 'profile.complete', 'business.context', 'business.active']`.

### 3.1. Customers Routes (`routes/owner.php:240-248`)
```php
// Existing route declarations:
Route::get('/customers', [CustomerWebController::class, 'index'])->middleware('require.permission:customers.view')->name('customers.index');
Route::get('/customers/export', [CustomerWebController::class, 'export'])->middleware('require.permission:customers.export')->name('customers.export');
Route::get('/customers/template', [CustomerWebController::class, 'downloadTemplate'])->middleware('require.permission:customers.view')->name('customers.template');
Route::post('/customers/import/preview', [CustomerWebController::class, 'previewImport'])->middleware('require.permission:customers.create')->name('customers.import.preview');
Route::post('/customers/import/execute', [CustomerWebController::class, 'executeImport'])->middleware(['require.permission:customers.create', 'entitlement:customer'])->name('customers.import.execute');
Route::post('/customers', [CustomerWebController::class, 'store'])->middleware(['require.permission:customers.create', 'entitlement:customer'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerWebController::class, 'update'])->middleware('require.permission:customers.edit')->name('customers.update');
Route::delete('/customers/{customer}', [CustomerWebController::class, 'destroy'])->middleware('require.permission:customers.delete')->name('customers.destroy');
```
*Clean Routing Remediation:*  
Since `CustomerWebController::middleware()` already declares all 5 permissions and the `entitlement:customer` middleware via Laravel 11's `HasMiddleware`, the `->middleware(...)` calls in `routes/owner.php` are redundant. They can be removed for clean, encapsulated routing.

### 3.2. CRM & Loyalty Routes (`routes/owner.php:830-838`)
```php
// Existing route declarations:
Route::middleware('require.permission:crm.view')->group(function (): void {
    Route::get('/crm/members', [CrmWebController::class, 'members'])->name('crm.members.index');
    Route::get('/crm/customers/{customer}/points', [CrmWebController::class, 'pointHistories'])->name('crm.customers.points');
    Route::get('/crm/vouchers', [CrmWebController::class, 'vouchers'])->name('crm.vouchers.index');
});
Route::post('/crm/customers/{customer}/credit-payment', [CrmWebController::class, 'recordCreditPayment'])->middleware('require.permission:crm.manage')->name('crm.customers.credit-payment');
Route::post('/crm/vouchers', [CrmWebController::class, 'storeVoucher'])->middleware('require.permission:crm.manage')->name('crm.vouchers.store');
Route::post('/crm/vouchers/{voucher}/toggle', [CrmWebController::class, 'toggleVoucher'])->middleware('require.permission:crm.manage')->name('crm.vouchers.toggle');
```
*Clean Routing Remediation:*  
`CrmWebController::middleware()` already encapsulates `crm.view` and `crm.manage`. The outer `Route::middleware('require.permission:crm.view')` group and per-route `->middleware('require.permission:crm.manage')` can be cleaned up.

### 3.3. Community Routes (`routes/owner.php:546-565`)
```php
// Existing route declarations (COMPLETELY UNGUARDED):
Route::get('/community', [CommunityWebController::class, 'index'])->name('community.index');
Route::post('/community', [CommunityWebController::class, 'store'])->name('community.store');
Route::post('/community/{post}/like', [CommunityWebController::class, 'toggleLike'])->name('community.like');
Route::post('/community/{post}/comment', [CommunityWebController::class, 'comment'])->name('community.comment');
Route::post('/community/{post}/repost', [CommunityWebController::class, 'toggleRepost'])->name('community.repost');
Route::post('/community/notifications/read', [CommunityWebController::class, 'markNotificationsRead'])->name('community.notifications.read');
Route::delete('/community/{post}', [CommunityWebController::class, 'destroy'])->name('community.destroy');

Route::get('/community/events', [CommunityWebController::class, 'events'])->name('community.events.index');
Route::post('/community/events/{event}/register', [CommunityWebController::class, 'registerEvent'])->name('community.events.register');
Route::post('/community/events/{event}/cancel', [CommunityWebController::class, 'cancelEventRegistration'])->name('community.events.cancel');
Route::get('/community/events/{event}/ticket', [CommunityWebController::class, 'getEventTicket'])->name('community.events.ticket');
Route::post('/community/events/{event}/checkin-location', [CommunityWebController::class, 'checkinLocation'])->name('community.events.checkin-location');
Route::post('/community/events/{event}/checkin-barcode', [CommunityWebController::class, 'checkinBarcode'])->name('community.events.checkin-barcode');

Route::get('/community/events/registrations/{registration}/payment', [CommunityWebController::class, 'showEventPayment'])->name('community.events.payment.show');
Route::get('/community/events/registrations/{registration}/payment-status', [CommunityWebController::class, 'checkEventPaymentStatus'])->name('community.events.payment.status');
Route::post('/community/events/registrations/{registration}/simulate-pay', [CommunityWebController::class, 'simulateEventPayment'])->name('community.events.payment.simulate');
```
*Finding:*  
There is no route-level middleware and no controller-level middleware. Once `CommunityWebController::middleware()` is populated, routing will remain clean while full RBAC security is enforced.

---

## 4. Seeders & Permissions Architecture Audit

### 4.1. Existing Permissions in `database/seeders/RbacSeeder.php`
Lines 23-30 of `RbacSeeder.php`:
```php
// ─── Customers / CRM ─────────────────────────────────────
'customers.view'   => ['Lihat Daftar Pelanggan', 'customers', 'Melihat daftar dan detail pelanggan.'],
'customers.create' => ['Tambah Pelanggan',       'customers', 'Menambahkan pelanggan baru.'],
'customers.edit'   => ['Edit Pelanggan',         'customers', 'Mengubah data pelanggan.'],
'customers.delete' => ['Hapus Pelanggan',        'customers', 'Menghapus data pelanggan.'],
'customers.export' => ['Export Pelanggan',       'customers', 'Export data pelanggan ke Excel/CSV.'],
'crm.view'         => ['Lihat CRM & Loyalitas',  'customers', 'Melihat tier membership pelanggan, riwayat poin, dan status voucher.'],
'crm.manage'       => ['Kelola CRM & Loyalitas', 'customers', 'Mengatur tingkatan tier membership, formula reward poin, dan voucher promo.'],
```

Existing System Roles Assignment in `RbacSeeder.php`:
| Permission | owner | admin | manager | cashier | warehouse | finance | staff | viewer |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `customers.view` | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ |
| `customers.create` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `customers.edit` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `customers.delete` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `customers.export` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `crm.view` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| `crm.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### 4.2. Missing Permissions Required for Community Module
Currently, **ZERO** `community.*` permissions exist in `RbacSeeder.php`.

To ensure full RBAC coverage without over-complication, the following **3 core permissions** (or 4 granular permissions) must be registered in `RbacSeeder::permissionMatrix()`:

```php
// ─── Komunitas & Event UMKM ──────────────────────────────
'community.view'            => ['Lihat Komunitas & Event',        'community', 'Melihat forum diskusi komunitas UMKM, notifikasi, dan agenda event.'],
'community.post'            => ['Posting & Interaksi Komunitas',  'community', 'Membuat postingan baru, menyukai, merepost, dan berkomentar di forum diskusi.'],
'community.events.register' => ['Pendaftaran Event Komunitas',   'community', 'Mendaftar event pelatihan/seminar, melakukan pembayaran tiket, dan check-in kehadiran.'],
'community.manage'          => ['Kelola & Moderasi Komunitas',    'community', 'Mengelola dan memoderasi postingan komunitas serta konfigurasi event.'],
```

Recommended Role Mapping in `RbacSeeder::roleDefinitions()`:
| Permission | owner | admin | manager | cashier | warehouse | finance | staff | viewer |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `community.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `community.post` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| `community.events.register` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `community.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

*Note on Module Isolation (`ModuleRegistry.php`):*  
In `app/Models/Business.php:534`, any permission not listed in `ModuleRegistry::getModulesForPermission($permissionSlug)` is treated as a **core permission** (always enabled across all business tiers, Free through Prestige). This matches COOCA's policy that the UMKM Community Feed is a universal feature.

---

## 5. Blade View Guards Detailed Audit

Audit of all Blade views in `resources/views/app/customers`, `resources/views/app/crm`, and `resources/views/app/community`.

### 5.1. `resources/views/app/customers/index.blade.php` (1,502 lines)
| Line(s) | UI Element / Action | Current Guard | Required / Recommended Guard | Defect Severity |
|---|---|---|---|---|
| 231-238 | "Export Excel" header button | `@if (Context::hasPermission('customers.export'))` | `@can('customers.export')` | ✅ Guarded (Refactor to `@can`) |
| 240-246 | "Import Excel" header button | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded (Refactor to `@can`) |
| 248-253 | "Tambah Pelanggan" header button | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded (Refactor to `@can`) |
| 524-531 | Table row "Edit" icon button | `@if (Context::hasPermission('customers.edit'))` | `@can('customers.edit')` | ✅ Guarded (Refactor to `@can`) |
| 533-546 | Table row "Delete" form button | `@if (Context::hasPermission('customers.delete'))` | `@can('customers.delete')` | ✅ Guarded (Refactor to `@can`) |
| 670-676 | Mobile card "Edit" button | `@if (Context::hasPermission('customers.edit'))` | `@can('customers.edit')` | ✅ Guarded (Refactor to `@can`) |
| 678-690 | Mobile card "Delete" form button | `@if (Context::hasPermission('customers.delete'))` | `@can('customers.delete')` | ✅ Guarded (Refactor to `@can`) |
| 713-720 | Empty state "Tambah Pelanggan Pertama" | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded (Refactor to `@can`) |
| 730-920 | Modal 1 Template: Tambah Pelanggan | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded |
| **922-1080** | **Modal 2 Template: Edit Pelanggan** | **NONE** (unwrapped `<template x-teleport="body">`) | **`@can('customers.edit')`** | ⚠️ **DEFECT:** Modal HTML rendered to unauthorized users |
| **1205-1209** | **Detail Modal "Edit" action button** | **NONE** (`<button @click="openEditModal(...)">`) | **`@can('customers.edit')`** | ⚠️ **DEFECT:** Unauthorized viewers can trigger Edit dialog |
| 1225-1490 | Modal 4 Template: Import Excel Preview | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded |

### 5.2. `resources/views/app/crm/members.blade.php` (763 lines)
| Line(s) | UI Element / Action | Current Guard | Required / Recommended Guard | Defect Severity |
|---|---|---|---|---|
| 156-161 | Quick Action "Tambah Pelanggan" | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded |
| 163-169 | Quick Action "Kelola Voucher Diskon" | `@if (Context::hasPermission('crm.manage') && $canCrmLoyalty)` | `@can('crm.manage')` | ✅ Guarded |
| 341-349 | Table "Bayar Piutang" button | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 362-369 | Sticky action column "Bayar Piutang" | `@if ($m->current_credit_balance > 0 && Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 486-492 | Mobile card "Pelunasan" button | `@if ($m->current_credit_balance > 0 && Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 494-498 | Mobile card "Riwayat Poin" button | None (Accessible to viewers with `crm.view`) | Safe for viewers | ✅ Safe |
| 543-549 | Empty state "Tambah Pelanggan Pertama" | `@if (Context::hasPermission('customers.create'))` | `@can('customers.create')` | ✅ Guarded |
| 559-670 | Modal: Pelunasan Kasbon Piutang | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |

### 5.3. `resources/views/app/crm/vouchers.blade.php` (365 lines)
| Line(s) | UI Element / Action | Current Guard | Required / Recommended Guard | Defect Severity |
|---|---|---|---|---|
| 23-29 | Header "Buat Voucher Diskon" | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 103-109 | Action Tray "Buat Voucher" button | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 125-131 | Grid Banner "Buat Voucher" button | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 196-207 | Card "Aktifkan/Nonaktifkan" form | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 230-236 | Empty state "Terbitkan Voucher Pertama" | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |
| 245-360 | Modal: Buat Voucher Baru | `@if (Context::hasPermission('crm.manage'))` | `@can('crm.manage')` | ✅ Guarded |

### 5.4. `resources/views/app/community/index.blade.php` (1,114 lines)
| Line(s) | UI Element / Action | Current Guard | Required Guard | Defect Severity |
|---|---|---|---|---|
| **249-254** | **Header "Tulis Diskusi Baru" button** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **509-595** | **Post Composer Form (`community.store`)** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **573-585** | **Trending Hashtag Quick Add buttons** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **645-653** | **Post Delete Form (`community.destroy`)** | Author check only | **`@can('community.post')` or `@can('community.manage')`** | ❌ **CRITICAL DEFECT** |
| **685-693** | **Repost button (`toggleRepost()`)** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **696-704** | **Like button (`toggleLike()`)** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **758-768** | **Comment Form (`submitComment()`)** | **NONE** | **`@can('community.post')`** | ❌ **CRITICAL DEFECT** |
| **404-411** | **Banner Event Register form** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **391-395** | **Banner Event "Bayar Tiket Sekarang" link** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **1045-1049**| **Ticket Modal GPS Check-in button** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **1067-1071**| **Ticket Modal Barcode Check-in button** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **1085-1091**| **Ticket Modal Cancel Registration form**| **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |

### 5.5. `resources/views/app/community/events.blade.php` (644 lines)
| Line(s) | UI Element / Action | Current Guard | Required Guard | Defect Severity |
|---|---|---|---|---|
| **282-286** | **"Bayar Tiket Sekarang" button (`payment.show`)** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **289-294** | **"Lihat Tiket Digital" button (`viewTicket`)** | **NONE** | **`@can('community.view')`** | ⚠️ High |
| **297-304** | **Event Registration Form (`events.register`)** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **389-393** | **My Tickets Tab "Bayar Sekarang" link** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **402-406** | **My Tickets Tab "Batal Pendaftaran" form** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **593-597** | **Ticket Modal GPS Check-in button** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |
| **615-619** | **Ticket Modal Barcode Check-in button** | **NONE** | **`@can('community.events.register')`** | ❌ **CRITICAL DEFECT** |

### 5.6. `resources/views/app/community/event_payment.blade.php` (295 lines)
| Line(s) | UI Element / Action | Current Guard | Required Guard | Defect Severity |
|---|---|---|---|---|
| 182-186 | "Cek Status Pembayaran" button | None | `@can('community.events.register')` or `@can('community.view')` | Safe / Medium |
| **190-198** | **"Simulasi Bayar QRIS Sukses" form** | `app()->environment('local', 'testing')` only | **`@can('community.events.register')`** | ⚠️ High (Bypasses RBAC in dev) |

---

## 6. Concrete Remediation & Implementation Roadmap

### 6.1. Step 1: Register Community Permissions in `database/seeders/RbacSeeder.php`
In `permissionMatrix()`:
```php
// ─── Komunitas & Event UMKM ──────────────────────────────
'community.view'            => ['Lihat Komunitas & Event',        'community', 'Melihat forum diskusi komunitas UMKM, notifikasi, dan agenda event.'],
'community.post'            => ['Posting & Interaksi Komunitas',  'community', 'Membuat postingan baru, menyukai, merepost, dan berkomentar di forum diskusi.'],
'community.events.register' => ['Pendaftaran Event Komunitas',   'community', 'Mendaftar event pelatihan/seminar, melakukan pembayaran tiket, dan check-in kehadiran.'],
'community.manage'          => ['Kelola & Moderasi Komunitas',    'community', 'Mengelola dan memoderasi postingan komunitas serta konfigurasi event.'],
```

In `roleDefinitions()`:
- Add to `$managerSlugs`:
  ```php
  'community.view',
  'community.post',
  'community.events.register',
  'community.manage',
  ```
- Add to `cashier`:
  ```php
  'community.view',
  'community.events.register',
  ```
- Add to `staff`:
  ```php
  'community.view',
  'community.post',
  'community.events.register',
  ```
- Add to `finance`, `warehouse`, `viewer`:
  ```php
  'community.view',
  ```

### 6.2. Step 2: Implement `HasMiddleware` in `CommunityWebController.php`
Replace empty `middleware()` in `app/Http/Controllers/Web/CommunityWebController.php`:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:community.view', only: [
            'index',
            'events',
            'getEventTicket',
            'showEventPayment',
            'checkEventPaymentStatus',
            'markNotificationsRead',
        ]),
        new Middleware('require.permission:community.post', only: [
            'store',
            'toggleLike',
            'toggleRepost',
            'comment',
            'destroy',
        ]),
        new Middleware('require.permission:community.events.register', only: [
            'registerEvent',
            'cancelEventRegistration',
            'checkinLocation',
            'checkinBarcode',
            'simulateEventPayment',
        ]),
    ];
}
```

### 6.3. Step 3: Patch View Guards in Blade Templates

#### In `resources/views/app/customers/index.blade.php`:
1. Wrap Modal 2 (Edit Pelanggan) template (lines 922-1080) in:
   ```blade
   @if (\App\Support\Context::hasPermission('customers.edit'))
       <template x-teleport="body">
           <!-- Edit Modal content -->
       </template>
   @endif
   ```
2. Wrap line 1205 (Edit button inside Detail Modal) in:
   ```blade
   @if (\App\Support\Context::hasPermission('customers.edit'))
       <button type="button" @click="showDetailModal = false; openEditModal(selectedCustomer.id)" ...>
           <i data-lucide="edit-3" class="w-4 h-4"></i>
           <span>{{ __('common.edit') }}</span>
       </button>
   @endif
   ```

#### In `resources/views/app/community/index.blade.php`:
1. Wrap composer header button (line 249) and composer card (line 509) with `@can('community.post')` (or `@if(\App\Support\Context::hasPermission('community.post'))`).
2. Wrap like, repost, and comment form buttons with `@can('community.post')`.
3. Wrap event register form (line 404) and pay link (line 391) with `@can('community.events.register')`.
4. Wrap ticket checkin buttons (lines 1045, 1067) and cancel form (line 1085) with `@can('community.events.register')`.

#### In `resources/views/app/community/events.blade.php`:
1. Wrap event register form (line 297), payment button (line 282, line 389), and cancel form (line 402) with `@can('community.events.register')`.
2. Wrap ticket modal checkin buttons (lines 593, 615) with `@can('community.events.register')`.

---

## 7. Verification & Automated Testing Plan

1. **Database Seeder Verification:**
   ```bash
   php artisan db:seed --class=RbacSeeder --force
   ```
   Verify all new permissions are created in `permissions` table and mapped in `permission_role`.

2. **PHP Syntax & Linter Validation:**
   ```bash
   php -l app/Http/Controllers/Web/CustomerWebController.php
   php -l app/Http/Controllers/Web/Crm/CrmWebController.php
   php -l app/Http/Controllers/Web/CommunityWebController.php
   php -l database/seeders/RbacSeeder.php
   ```

3. **Feature Test Suite:**
   Create / update `tests/Feature/CommunityRbacTest.php` testing:
   - User without `community.view` gets 403 Forbidden accessing `/community` and `/community/events`.
   - User without `community.post` gets 403 Forbidden attempting `POST /community` (create post) and `POST /community/{post}/comment`.
   - User without `community.events.register` gets 403 Forbidden attempting `POST /community/events/{event}/register`.
   - User with `owner` or `manager` role successfully performs all actions (200 OK / 302 Redirect).
   - Test execution:
     ```bash
     php artisan test tests/Feature/CustomerWebFeatureTest.php tests/Feature/CrmWebFeatureTest.php tests/Feature/CommunityFeatureTest.php
     ```
