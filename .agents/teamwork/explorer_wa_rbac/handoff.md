# WhatsApp Module RBAC, Controllers, Routes & Views Investigation Report

**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_rbac`  
**Date**: 2026-11-07  
**Status**: Investigation Complete (Read-Only)  

---

## 1. Observation

### 1.1 WhatsApp Controllers Identified
From filesystem analysis of `app/Http/Controllers`:
1. **Tenant/Owner Web Controllers (Core WhatsApp Module)**:
   - `app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php` (461 lines)
     - Implements `Illuminate\Routing\Controllers\HasMiddleware`
     - Methods: `index`, `getQr`, `checkStatus`, `startSession`, `disconnect`, `updateSettings`, `verifyMetaCredentials`, `testSend`, `sendOrderReceipt`, `logs`, `exportLogs`.
   - `app/Http/Controllers/Web/WhatsApp/WhatsAppBroadcastWebController.php` (302 lines)
     - Implements `Illuminate\Routing\Controllers\HasMiddleware`
     - Methods: `index`, `create`, `store`, `show`, `estimateRecipients`.
   - `app/Http/Controllers/Web/WhatsApp/MetaWhatsAppOnboardingController.php` (473 lines)
     - Implements `Illuminate\Routing\Controllers\HasMiddleware`
     - Methods: `getSignupConfig`, `exchangeCode`, `getStatus`, `disconnect`.
2. **Platform Superadmin Controllers**:
   - `app/Http/Controllers/Admin/AdminWhatsAppController.php` (512 lines)  Manages platform-level Meta templates, admin blasts, subscription reminders, and server gateway.
   - `app/Http/Controllers/Admin/AdminSettingController.php` (`testWhatsAppConfig` at line 163).
3. **Public / Webhook / API Controllers**:
   - `app/Http/Controllers/Api/V1/WhatsAppWebhookController.php`  Inbound Baileys webhook.
   - `app/Http/Controllers/Api/V1/WhatsApp/MetaWhatsAppWebhookController.php`  Inbound official Meta Cloud API webhook.
   - `app/Http/Controllers/Api/V1/PosReceiptApiController.php`  POS endpoint `sendWhatsAppReceipt`.

---

### 1.2 WhatsApp Routes Identified
From `routes/owner.php` lines 925–962:
```php
// WhatsApp Gateway Toko
Route::prefix('whatsapp')->name('whatsapp.')->middleware(['module:channels_marketing', 'require.permission:whatsapp.view'])->group(function (): void {
    Route::get('/', [WhatsAppWebController::class, 'index'])->name('index');
    Route::get('/inbox', fn() => redirect()->route('social-media.inbox.index', ['channel' => 'whatsapp']))->name('inbox.index');
    Route::get('/qr', [WhatsAppWebController::class, 'getQr'])->name('qr');
    Route::get('/status', [WhatsAppWebController::class, 'checkStatus'])->name('status');
    Route::get('/logs', [WhatsAppWebController::class, 'logs'])->name('logs.index');
    Route::get('/logs/export', [WhatsAppWebController::class, 'exportLogs'])->name('logs.export');
    Route::post('/orders/{order}/receipt', [WhatsAppWebController::class, 'sendOrderReceipt'])->name('orders.receipt');

    Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
        Route::post('/start', [WhatsAppWebController::class, 'startSession'])->name('start');
        Route::post('/disconnect', [WhatsAppWebController::class, 'disconnect'])->name('disconnect');
        Route::post('/settings', [WhatsAppWebController::class, 'updateSettings'])->name('settings');
        Route::post('/test', [WhatsAppWebController::class, 'testSend'])->middleware('throttle:5,1')->name('test');
        Route::post('/verify-meta', [WhatsAppWebController::class, 'verifyMetaCredentials'])->name('verify-meta');
    });

    // Meta WhatsApp Cloud API Official Embedded Signup & Onboarding
    Route::prefix('meta')->name('meta.')->group(function (): void {
        Route::get('/config', [MetaWhatsAppOnboardingController::class, 'getSignupConfig'])->name('config');
        Route::get('/status', [MetaWhatsAppOnboardingController::class, 'getStatus'])->name('status');

        Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
            Route::post('/exchange-code', [MetaWhatsAppOnboardingController::class, 'exchangeCode'])->name('exchange-code');
            Route::post('/disconnect', [MetaWhatsAppOnboardingController::class, 'disconnect'])->name('disconnect');
        });
    });

    // WhatsApp Broadcast Promosi
    Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
        Route::get('/broadcast', [WhatsAppBroadcastWebController::class, 'index'])->name('broadcast.index');
        Route::get('/broadcast/create', [WhatsAppBroadcastWebController::class, 'create'])->name('broadcast.create');
        Route::get('/broadcast/estimate', [WhatsAppBroadcastWebController::class, 'estimateRecipients'])->name('broadcast.estimate');
        Route::post('/broadcast', [WhatsAppBroadcastWebController::class, 'store'])->middleware('entitlement:whatsapp')->name('broadcast.store');
        Route::get('/broadcast/{campaign}', [WhatsAppBroadcastWebController::class, 'show'])->name('broadcast.show');
    });
});
```

---

### 1.3 RBAC Authorization Architecture in COOCA
1. **Mechanism**:
   - COOCA uses a custom-built, multi-tenant RBAC system rather than `spatie/laravel-permission`.
   - Core Models: `App\Models\Permission`, `App\Models\Role`, `business_users` pivot, and `App\Support\Context`.
   - Middleware: `App\Http\Middleware\RequirePermission` aliased as `'require.permission'` in `bootstrap/app.php`.
   - Module Gating: `App\Domain\Template\ModuleRegistry` groups permissions under industry modules (`MODULE_CHANNELS_MARKETING` governs `'whatsapp.view'` and `'whatsapp.manage'`).
2. **Current Database Seeders**:
   - `database/seeders/RbacSeeder.php` lines 109–111:
     ```php
     // ─── WhatsApp Gateway ─────────────────────────────────────
     'whatsapp.view'   => ['Lihat WhatsApp Gateway',      'whatsapp', 'Melihat status koneksi QR code dan riwayat log pengiriman pesan struk.'],
     'whatsapp.manage' => ['Kelola WhatsApp & Broadcast', 'whatsapp', 'Menghubungkan nomor WhatsApp, konfigurasi gateway, dan kirim blast promo.'],
     ```
   - Global Roles Assignment in `RbacSeeder.php`:
     - `owner`: all permissions (`$allSlugs`) $\rightarrow$ includes `whatsapp.view`, `whatsapp.manage`.
     - `manager`: lines 257–258 $\rightarrow$ includes `whatsapp.view`, `whatsapp.manage`.
     - `admin`: inherits `manager` slugs $\rightarrow$ includes `whatsapp.view`, `whatsapp.manage`.
     - `viewer`: line 417 $\rightarrow$ includes `whatsapp.view` (read-only).
     - `cashier`, `warehouse`, `finance`, `staff`: do not have `whatsapp.*` permissions by default.
3. **Critical Finding: Gate and `@can` Integration**:
   - In `app/Providers/AppServiceProvider.php` lines 138–149:
     ```php
     // Platform Admin RBAC Gate Integration
     \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
         if ($user instanceof \App\Models\Admin) {
             if ($user->isSuperAdmin()) {
                 return true;
             }

             return $user->hasPermission($ability) ? true : null;
         }

         return null;
     });
     ```
   - Observation: When `$user` is an instance of `App\Models\User` (merchant users in `/owner`), `Gate::before` returns `null`.
   - Because no individual `Gate::define` exists for `'whatsapp.view'` or `'whatsapp.manage'`, any call to `@can('whatsapp.manage')` in Blade views returns `false` even for Owners!
   - This explains why views across COOCA currently use `@if (\App\Support\Context::hasPermission('...'))` instead of `@can(...)`.
   - For `@can(...)` directives to work as requested, `Gate::before` must bridge to `Context::hasPermission($ability)`.
4. **Sidebar Navigation Discrepancy**:
   - In `app/Support/Navigation/NavigationRegistry.php` lines 490–496:
     ```php
     [
         'key'           => 'whatsapp-gateway',
         'label'         => 'WhatsApp Gateway',
         'icon'          => 'message-circle',
         'route'         => 'whatsapp.index',
         'active_routes' => ['whatsapp.index', 'whatsapp.status', 'whatsapp.qr', 'whatsapp.logs.*'],
         'permission'    => 'whatsapp.manage',
     ],
     ```
   - WhatsApp Gateway route requires `whatsapp.view`, but `NavigationRegistry` requires `whatsapp.manage`! Consequently, a `viewer` role cannot see the WhatsApp Gateway link in the sidebar navigation.

---

### 1.4 Controller-Level Guards and 403 Forbidden Behavior
1. **Current `RequirePermission` Behavior**:
   In `app/Http/Middleware/RequirePermission.php` lines 64–81:
   - If `$request->expectsJson() || $request->is('api/*')`: returns JSON with HTTP `403 Forbidden` (`FORBIDDEN_PERMISSION`).
   - If `$request->routeIs('portal')`: calls `abort(403)`.
   - Otherwise (standard browser GET): returns `redirect()->route('portal')->with('error', '...')` (HTTP 302).
2. **Current Controller `middleware()`**:
   - `WhatsAppWebController.php`: implements `HasMiddleware`, maps `whatsapp.view` to `index`, `getQr`, `checkStatus`, `logs`, `exportLogs`; `whatsapp.manage` to `startSession`, `disconnect`, `updateSettings`, `testSend`, `verifyMetaCredentials`; `reports.export` to `exportLogs`; `pos.orders` to `sendOrderReceipt`.
   - `WhatsAppBroadcastWebController.php`: implements `HasMiddleware`, applies `require.permission:whatsapp.manage` across all methods.
   - `MetaWhatsAppOnboardingController.php`: implements `HasMiddleware`, maps `whatsapp.view` to `getSignupConfig`, `getStatus`; `whatsapp.manage` to `exchangeCode`, `disconnect`.
3. **HTTP 403 on Direct Access**:
   When an unauthorized user makes an AJAX/JSON request (`getJson`, `postJson`, `fetch()` with `Accept: application/json`), `RequirePermission` already returns an **HTTP 403 Forbidden** response.
   If direct browser navigation must also strictly return HTTP 403 instead of redirecting to `portal`:
   - Either: `can:whatsapp.view` / `can:whatsapp.manage` middleware is used via Laravel's native Authorize middleware (which calls `Gate::authorize()` and throws `AuthorizationException` $\rightarrow$ renders `errors/403.blade.php`).
   - Or: `abort_unless(Context::hasPermission('whatsapp.view'), 403)` is added at controller entry.

---

### 1.5 View Action Protection Inventory (`resources/views/app/whatsapp/`)

#### A. `resources/views/app/whatsapp/index.blade.php`
| Line # | Element | Current Guard | Target Permission / Directive | Severity |
|---|---|---|---|---|
| 24–30 | POS Terminal Action Link | `@if (\App\Support\Context::hasPermission('pos.terminal'))` | `@can('pos.terminal')` | Low (already guarded via Context) |
| 52–57 | Settings Hub Banner Link (`settings.index?tab=whatsapp`) | None (Unprotected) | `@can('settings.view')` or `@can('whatsapp.manage')` | Medium (links to settings) |
| 146–149 | "Putuskan Koneksi" (Disconnect Meta) Button | **None (Unprotected)** | `@can('whatsapp.manage')` | **High** (dangerous action visible to view-only users) |
| 162–172 | "Daftar Akun WhatsApp Resmi" (Embedded Signup) Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |
| 223–228 | Service Status Toggle Switch (`is_active`) | **None (Unprotected)** | `@can('whatsapp.manage')` (disable/hide toggle) | **High** (triggers AJAX update to settings) |
| 282–287 | Save Receipt Settings Submit Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |
| 318–326 | Test Message "Kirim Pesan Uji Coba" Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |
| 366–370 | Meta Catalog "Kirim Blast dengan Template Ini" Link | **None (Unprotected)** | `@can('whatsapp.manage')` | Medium (links to broadcast composer) |
| 515–520 | Template Card "Gunakan Template" Links | **None (Unprotected)** | `@can('whatsapp.manage')` | Medium (links to broadcast composer) |
| 594–599 | Supervisor PIN Disconnect Confirm Button | Inside modal | `@can('whatsapp.manage')` | Low (modal opened from disconnect button) |

#### B. `resources/views/app/whatsapp/broadcast.blade.php`
| Line # | Element | Current Guard | Target Permission / Directive | Severity |
|---|---|---|---|---|
| 19–25 | Header "Buat Siaran Baru" Action Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |
| 126–132 | Empty State "Buat Pesan Siaran Pertama" Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |
| 811–817 | Modal Composer "Jadwalkan & Kirim Siaran" Submit Button | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |

#### C. `resources/views/app/whatsapp/broadcast_detail.blade.php`
| Line # | Element | Current Guard | Target Permission / Directive | Severity |
|---|---|---|---|---|
| 91–98 | "Buat Siaran Serupa" CTA Link | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` | `@can('whatsapp.manage')` | Medium (convert to `@can`) |

#### D. `resources/views/app/whatsapp/logs.blade.php`
| Line # | Element | Current Guard | Target Permission / Directive | Severity |
|---|---|---|---|---|
| 27–31 | "Ekspor Excel" Button (`whatsapp.logs.export`) | **None (Unprotected in view)** | `@can('reports.export')` | **High** (controller requires `reports.export`, unprivileged users encounter error) |
| 32–36 | "Bersihkan Penyimpanan" (Storage Pruning) Button | **None (Unprotected in view)** | `@can('whatsapp.manage')` | Medium (administrative cleanup action) |
| 463–466 | Pruning Modal Confirm Button | Inside modal | `@can('whatsapp.manage')` | Medium |

---

## 2. Logic Chain

1. **Premise 1**: Requirements R2 and the acceptance criteria dictate that WhatsApp controller actions enforce RBAC checks, views use `@can` directives, database seeders register all required permissions, and unauthorized attempts receive HTTP 403 Forbidden.
2. **Premise 2 (Seeder Analysis)**: In `database/seeders/RbacSeeder.php`, `whatsapp.view` and `whatsapp.manage` are registered in `permissionMatrix()` and allocated to `owner`, `manager`, `admin`, and `viewer` (view only). However, in `app/Support/Navigation/NavigationRegistry.php`, `whatsapp-gateway` is assigned `whatsapp.manage` instead of `whatsapp.view`, improperly hiding the menu from view-only roles.
3. **Premise 3 (Gate Bridge)**: Laravel Blade `@can($ability)` dispatches to `Gate::check($ability)`. Currently, `AppServiceProvider::boot()` only handles `\App\Models\Admin` inside `Gate::before`, returning `null` for `\App\Models\User`. Without defining abilities or bridging `Gate::before` to `\App\Support\Context::hasPermission($ability)`, `@can(...)` in views will always return `false` for merchant users. Bridging `Gate::before` solves `@can` across all Blade views system-wide without breaking existing code.
4. **Premise 4 (Controller Guards & 403)**:
   - In `WhatsAppWebController`, `WhatsAppBroadcastWebController`, and `MetaWhatsAppOnboardingController`, `HasMiddleware` is already implemented.
   - For JSON/AJAX requests, `RequirePermission` middleware returns HTTP 403 with `{ success: false, error_code: 'FORBIDDEN_PERMISSION' }`.
   - If direct HTTP GET browser access must also return HTTP 403 (instead of 302 redirect to portal), controller methods or `can:` middleware can be used, triggering `abort(403)` or `AuthorizationException`.
5. **Premise 5 (Route Cleanliness & IDOR Protection)**:
   - In `routes/owner.php`, `sendOrderReceipt` is located inside the `require.permission:whatsapp.view` route group, blocking cashiers who have `pos.orders` but lack `whatsapp.view`. Moving permission encapsulation into `WhatsAppWebController` resolves this conflict.
   - All models (`PosOrder`, `WhatsAppBroadcastCampaign`, `WhatsAppSession`) are queried by `business_id` scoping or `abort(404)` on mismatch, correctly preventing IDOR.

---

## 3. Caveats

1. **Read-Only Scope**: This report contains investigation findings and recommendations only. No application files have been modified.
2. **Test Suite Baseline**: Running `php artisan test tests/Feature/WhatsApp/` revealed 51 passed and 7 failed tests. The 7 failures pre-exist and relate to Meta API mock response mismatch and `app_id` loaded from `SystemSetting` vs `Config`. These are independent of the RBAC authorization logic.
3. **Granularity Decision**: While `whatsapp.view` and `whatsapp.manage` adequately cover current capabilities, if future business requirements demand separating broadcast marketing from API/session management, a dedicated `whatsapp.broadcast` permission could be introduced into `RbacSeeder.php` and `ModuleRegistry.php`.

---

## 4. Conclusion

1. **Authorization Architecture**: COOCA uses an internal multi-tenant RBAC system managed by `Context`, `Permission`, `Role`, and `RequirePermission` middleware, categorized under `ModuleRegistry::MODULE_CHANNELS_MARKETING`.
2. **Permissions Status**:
   - `whatsapp.view` and `whatsapp.manage` are present in `database/seeders/RbacSeeder.php`.
   - `NavigationRegistry.php` line 495 requires updating from `whatsapp.manage` to `whatsapp.view` to permit view-only users to see the Gateway navigation item.
3. **`@can` Directive Readiness**:
   - `AppServiceProvider.php` currently lacks a `Gate::before` handler for `App\Models\User`.
   - A 5-line extension in `AppServiceProvider.php` bridges Laravel's Gate to `Context::hasPermission($ability)`, allowing `@can` to function seamlessly across all Blade views.
4. **Vulnerable UI Actions Identified**:
   - Several critical buttons in `index.blade.php` (Disconnect Meta, Service Toggle, Template Broadcast CTA) and `logs.blade.php` (Export Excel, Storage Pruning) currently lack permission checks and are visible to unauthorized users.
5. **Controller Guards**:
   - All three WhatsApp controllers implement `HasMiddleware`.
   - To strictly guarantee HTTP 403 Forbidden for unauthorized requests across all endpoints, the controller guards should be harmonized with `can:` middleware or verified via `getJson` assertions.

---

## 5. Concrete Implementation Recommendations

### Step 1: Enable `@can` Support for Merchant Users in `AppServiceProvider.php`
Add the `App\Models\User` check to `Gate::before` in `app/Providers/AppServiceProvider.php`:
```php
\Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
    if ($user instanceof \App\Models\Admin) {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission($ability) ? true : null;
    }

    if ($user instanceof \App\Models\User) {
        return \App\Support\Context::hasPermission($ability) ? true : null;
    }

    return null;
});
```

### Step 2: Fix Navigation Permission in `app/Support/Navigation/NavigationRegistry.php`
Update line 495:
```php
// BEFORE:
'permission'    => 'whatsapp.manage',

// AFTER:
'permission'    => 'whatsapp.view',
```

### Step 3: Standardize Controller-Level Guards
In `WhatsAppWebController.php`:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:whatsapp.view', only: [
            'index',
            'getQr',
            'checkStatus',
            'logs',
        ]),
        new Middleware('require.permission:whatsapp.manage', only: [
            'startSession',
            'disconnect',
            'updateSettings',
            'testSend',
            'verifyMetaCredentials',
        ]),
        new Middleware('require.permission:whatsapp.view', only: ['exportLogs']),
        new Middleware('require.permission:reports.export', only: ['exportLogs']),
        new Middleware('entitlement:export', only: ['exportLogs']),
        new Middleware('require.permission:pos.orders', only: ['sendOrderReceipt']),
        new Middleware('throttle:5,1', only: ['testSend']),
        new Middleware('throttle:10,1', only: ['startSession', 'verifyMetaCredentials', 'sendOrderReceipt']),
    ];
}
```
In `WhatsAppBroadcastWebController.php`:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:whatsapp.manage'),
        new Middleware('entitlement:whatsapp', only: ['store']),
        new Middleware('throttle:10,1', only: ['store']),
    ];
}
```
In `MetaWhatsAppOnboardingController.php`:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:whatsapp.view', only: [
            'getSignupConfig',
            'getStatus',
        ]),
        new Middleware('require.permission:whatsapp.manage', only: [
            'exchangeCode',
            'disconnect',
        ]),
        new Middleware('throttle:10,1', only: [
            'exchangeCode',
            'disconnect',
        ]),
    ];
}
```

### Step 4: Protect Blade Views with `@can` Directives
1. **`resources/views/app/whatsapp/index.blade.php`**:
   - Wrap Disconnect button (line 146) in `@can('whatsapp.manage') ... @endcan`.
   - Wrap Embedded Signup (line 162) in `@can('whatsapp.manage') ... @endcan`.
   - Wrap or disable Service Toggle (line 224) for users without `whatsapp.manage`.
   - Wrap Receipt Settings Save button (line 282) in `@can('whatsapp.manage') ... @endcan`.
   - Wrap Test Send button (line 318) in `@can('whatsapp.manage') ... @endcan`.
   - Wrap Template Broadcast links (lines 366 and 515) in `@can('whatsapp.manage') ... @endcan`.
   - Replace `@if (\App\Support\Context::hasPermission('pos.terminal'))` with `@can('pos.terminal')`.
2. **`resources/views/app/whatsapp/broadcast.blade.php`**:
   - Replace `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` at lines 19, 126, and 811 with `@can('whatsapp.manage') ... @endcan`.
3. **`resources/views/app/whatsapp/broadcast_detail.blade.php`**:
   - Replace `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` at line 91 with `@can('whatsapp.manage') ... @endcan`.
4. **`resources/views/app/whatsapp/logs.blade.php`**:
   - Wrap "Ekspor Excel" (line 27) in `@can('reports.export') ... @endcan`.
   - Wrap "Bersihkan Penyimpanan" (lines 32 and 463) in `@can('whatsapp.manage') ... @endcan`.

### Step 5: Clean Route Group in `routes/owner.php`
Extract `sendOrderReceipt` or encapsulate permission checks entirely at the controller level so that POS operators without `whatsapp.view` can dispatch order receipts.

---

## 6. Verification Method

1. **Verify Seeder & Gate Integration**:
   ```bash
   php artisan db:seed --class=RbacSeeder --force
   ```
2. **Verify Feature Tests for RBAC**:
   Create a dedicated feature test `tests/Feature/WhatsApp/WhatsAppRbacFeatureTest.php` with:
   - User without `whatsapp.view` accessing `whatsapp.index` via `getJson` $\rightarrow$ `assertStatus(403)`.
   - User without `whatsapp.manage` posting to `whatsapp.start` via `postJson` $\rightarrow$ `assertStatus(403)`.
   - User without `whatsapp.manage` posting to `whatsapp.broadcast.store` via `postJson` $\rightarrow$ `assertStatus(403)`.
   - User with `whatsapp.view` accessing `whatsapp.index` $\rightarrow$ `assertOk()`.
   - User with `whatsapp.view` inspecting Blade output $\rightarrow$ does NOT see Disconnect button or Template Broadcast CTA.
   - User with `whatsapp.manage` $\rightarrow$ sees all management action buttons.
3. **Execute Test Command**:
   ```bash
   php artisan test tests/Feature/WhatsApp/
   ```
4. **Invalidation Conditions**:
   - If `@can` checks fail to resolve permissions for authenticated merchant users.
   - If an unauthorized user can execute `whatsapp.start` or `whatsapp.disconnect`.
   - If `whatsapp.logs.export` can be downloaded without `reports.export` permission.
