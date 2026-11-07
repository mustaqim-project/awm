# Testing Infrastructure & Test Coverage Investigation for WhatsApp Module

## 1. Observation

### 1.1 Existing WhatsApp Test Files & Current Coverage
A comprehensive scan of `tests/` revealed the following WhatsApp-related test suites:
- **`tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php`** (1,105 lines, 44.5 KB, 28 tests):
  - Line 23: Class `MerchantWhatsAppWebFeatureTest extends TestCase` using `RefreshDatabase`.
  - Line 31: Seeds `\Database\Seeders\RbacSeeder::class`.
  - Lines 41-64: `createMerchant()` helper attaches a user with `'role' => 'owner'`.
  - Tests verify: dashboard rendering (connected & disconnected), logs rendering, broadcast listing & creation, Meta onboarding config & disconnect, SSRF media validation, test send phone normalization & throttling, receipt audit logging, PII masking, outlet targeting, and responsive WCAG touch targets.
  - **CRITICAL GAP**: All 28 tests run strictly as `'role' => 'owner'`. There is **ZERO** test coverage asserting HTTP 403 Forbidden or unauthorized redirection for non-owner roles (e.g., `cashier`, `staff`, or `viewer` without `whatsapp.manage`).
- **`tests/Feature/WhatsApp/MetaStandardTemplatesIntegrationTest.php`** (310 lines, 11.7 KB):
  - Tests Admin Meta standard template catalog deployment, sync, and multi-language support.
- **`tests/Feature/WhatsApp/MetaWhatsAppCloudApiTest.php`** (535 lines, 21.0 KB):
  - Tests Meta Cloud API webhooks, verify token handshakes (lines 134, 158 assert HTTP 403 for invalid webhook secret), message delivery status webhooks, and token encryption.
- **`tests/Feature/WhatsApp/WhatsAppLogsExportTest.php`** (261 lines, 9.9 KB):
  - Tests streaming Excel export (`whatsapp.logs.export`), two-sheet spreadsheet layout, KPI cards, and message ledger.
- **`tests/Feature/WhatsApp/WhatsAppTemplateSyncTest.php`** (217 lines, 8.0 KB):
  - Tests synchronization of approved Meta message templates from Graph API v26.0.
- **`tests/Feature/Pos/PosWhatsAppReceiptApiTest.php`**:
  - Tests POS receipt dispatch via WhatsApp from POS terminal.
- **`tests/Unit/`**:
  - Contains domain/unit tests (`BasePolicyTest.php`, `BusinessScopeTest.php`, etc.); no WhatsApp-specific unit tests exist as WhatsApp features are integration-level web interactions.

### 1.2 Test Execution Setup & Database Environment
- **Test Runner**: PHPUnit 12.5.12 executed via Laravel Artisan test runner (`php artisan test`).
- **Configuration in `phpunit.xml`**:
  - Line 8-13: Test suites `Unit` (`tests/Unit`) and `Feature` (`tests/Feature`).
  - Line 21: `<env name="APP_ENV" value="testing"/>`
  - Line 28: `<env name="DB_CONNECTION" value="sqlite"/>`
  - Line 29: `<env name="DB_DATABASE" value=":memory:"/>`
  - Line 32: `<env name="QUEUE_CONNECTION" value="sync"/>`
- **Cache Trap & Execution Dependency**:
  - When `bootstrap/cache/config.php` exists, Laravel's cached configuration overrides `phpunit.xml`, attempting to connect to MySQL database `cooca_id`. This causes `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'cooca_id.users' doesn't exist` during `RefreshDatabase`.
  - In `composer.json` lines 55-58:
    ```json
    "test": [
        "@php artisan config:clear --ansi @no_additional_args",
        "@php artisan test"
    ]
    ```
  - Executing `php artisan config:clear` ensures `phpunit.xml` SQLite `:memory:` environment is properly honored. When cleared, tests run cleanly in-memory without affecting the development MySQL database.

### 1.3 RBAC Permission Structure & Verification Pattern
- **Routes (`routes/owner.php` lines 925-965)**:
  - Route prefix `/whatsapp` is wrapped in middleware `module:channels_marketing` and `require.permission:whatsapp.view`.
  - Sensitive actions (`/start`, `/disconnect`, `/settings`, `/test`, `/verify-meta`, `/meta/exchange-code`, `/meta/disconnect`, `/broadcast/*`) are wrapped in `require.permission:whatsapp.manage`.
  - Export route `/logs/export` requires `whatsapp.view`, `reports.export`, and `entitlement:export`.
- **Controller-Level Middleware (`HasMiddleware` interface)**:
  - `WhatsAppWebController.php` (lines 30-53):
    - `require.permission:whatsapp.view` on `['index', 'getQr', 'checkStatus', 'logs', 'exportLogs']`.
    - `require.permission:whatsapp.manage` on `['startSession', 'disconnect', 'updateSettings', 'testSend', 'verifyMetaCredentials']`.
    - `require.permission:reports.export` & `entitlement:export` on `['exportLogs']`.
    - `require.permission:pos.orders` on `['sendOrderReceipt']`.
  - `WhatsAppBroadcastWebController.php` (lines 30-37):
    - `require.permission:whatsapp.manage` on all actions (`index`, `create`, `store`, `show`, `estimateRecipients`).
    - `entitlement:whatsapp` & `throttle:10,1` on `store`.
  - `MetaWhatsAppOnboardingController.php` (lines 37-53):
    - `require.permission:whatsapp.view` on `['getSignupConfig', 'getStatus']`.
    - `require.permission:whatsapp.manage` on `['exchangeCode', 'disconnect']`.
- **Permission Seeder (`database/seeders/RbacSeeder.php`)**:
  - Line 110: `'whatsapp.view' => ['Lihat WhatsApp Gateway', 'whatsapp', '...']`
  - Line 111: `'whatsapp.manage' => ['Kelola WhatsApp & Broadcast', 'whatsapp', '...']`
  - Lines 257-258: Role `manager` is granted `whatsapp.view` and `whatsapp.manage`.
  - Line 417: Role `viewer` is granted `whatsapp.view` ONLY.
  - Roles `cashier`, `warehouse`, `finance`, `staff` have NEITHER `whatsapp.view` nor `whatsapp.manage`.
  - Role `owner` automatically possesses all permissions via `Context::isAdminOrOwner()`.
- **RBAC Middleware Mechanism (`app/Http/Middleware/RequirePermission.php`)**:
  - Lines 49-51: If user is owner or admin, access is immediately granted (`$next($request)`).
  - Lines 64-81: When unauthorized user lacks required permission:
    - If request expects JSON (`$request->expectsJson()` or `api/*`): returns `response()->json([...], Response::HTTP_FORBIDDEN)` (HTTP 403 Forbidden with `error_code: 'FORBIDDEN_PERMISSION'`).
    - If standard web request: redirects to route `portal` with flash error `Anda tidak memiliki izin akses untuk halaman tersebut.`.
- **Established Verification Pattern in Codebase (`FinanceModuleAuditTest.php` lines 92-112, `CustomerModuleAuditTest.php` lines 192-224)**:
  - Unauthorized web access: `$this->actingAs($unauthorized)->get(route(...))->assertRedirect(route('portal'));`
  - Unauthorized JSON/API access: `$this->actingAs($unauthorized)->getJson(route(...))->assertForbidden();`
  - Unauthorized write action: `$this->actingAs($unauthorized)->postJson(route(...), [...])->assertForbidden();`
  - Authorized access: `$this->actingAs($authorized)->get(route(...))->assertOk();`

### 1.4 Blade Directives & Gate Integration Finding
- Currently, Blade templates (`index.blade.php`, `broadcast.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`) check permissions using `@if (\App\Support\Context::hasPermission('...'))`.
- In `app/Providers/AppServiceProvider.php` (lines 139-149):
  ```php
  Gate::before(function ($user, string $ability) {
      if ($user instanceof \App\Models\Admin) {
          if ($user->isSuperAdmin()) return true;
          return $user->hasPermission($ability) ? true : null;
      }
      return null;
  });
  ```
- **CRITICAL ARCHITECTURAL FINDING**: `Gate::before` currently only delegates permissions for `\App\Models\Admin`. For tenant `\App\Models\User`, Gate returns `null`, causing any `@can('whatsapp.manage')` in Blade to evaluate to `false` even for owners!
- To properly support `@can('whatsapp.manage')` and `@can('whatsapp.view')` in Blade as requested, `AppServiceProvider::boot()` should bridge `User` to `\App\Support\Context::hasPermission($ability)`:
  ```php
  if ($user instanceof \App\Models\User) {
      return \App\Support\Context::hasPermission($ability) ? true : null;
  }
  ```

### 1.5 Blade View Compilation & Render Integrity
- Executing `php artisan view:cache` completed with exit code 0 (`INFO Blade templates cached successfully.`).
- Executing `php artisan view:clear` completed with exit code 0 (`INFO Compiled views cleared successfully.`).
- All 4 WhatsApp Blade files (`resources/views/app/whatsapp/index.blade.php`, `broadcast.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`) currently compile cleanly without Blade syntax errors.

---

## 2. Logic Chain

1. **Premise 1**: Acceptance criteria require:
   - Unauthorized users attempting direct access to WhatsApp controller endpoints receive HTTP 403 Forbidden.
   - Authorized users can access endpoints successfully.
   - Blade views in `resources/views/app/whatsapp` conditionally display actions using `@can` directives.
   - Blade views compile and render without syntax errors or missing variables.
   - All existing test suites pass without regression (`php artisan test`).
2. **Premise 2**: Current WhatsApp tests in `tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php` exclusively authenticate as `'role' => 'owner'`. Not a single test asserts HTTP 403 Forbidden or RBAC unauthorized restrictions.
3. **Premise 3**: The application's RBAC middleware (`RequirePermission.php`) returns HTTP 403 Forbidden when `$request->expectsJson()` is true, and redirects to `portal` for browser web requests.
4. **Premise 4**: System roles seeded by `RbacSeeder.php` provide distinct tiers:
   - `owner` & `manager`: Have `whatsapp.view` and `whatsapp.manage`.
   - `viewer`: Has `whatsapp.view` only (read-only).
   - `cashier` & `staff`: Have neither `whatsapp.view` nor `whatsapp.manage`.
5. **Premise 5**: In order for `@can('whatsapp.manage')` and `@can('whatsapp.view')` to evaluate accurately in Blade templates, Laravel's Gate must be informed of the active tenant user's permissions via `Context::hasPermission($ability)`.
6. **Inference**: A dedicated test class or expanded feature test suite is required to verify:
   - Role isolation (Cashier/Staff -> 403 Forbidden on JSON, redirect to portal on web).
   - Granular privilege isolation (Viewer -> 200 OK on index/logs, 403 Forbidden on manage/broadcast/disconnect, action buttons hidden in rendered HTML).
   - Full privilege execution (Owner/Manager -> 200 OK, action buttons visible).
   - Zero regression across all 28 existing tests in `MerchantWhatsAppWebFeatureTest.php`.

---

## 3. Caveats

- **Config Cache Dependency**: Tests MUST be executed without cached configuration (`php artisan config:clear` before `php artisan test`), otherwise Laravel falls back to MySQL `cooca_id` database and triggers table collision errors.
- **Gate Integration**: If `@can` directives are used in Blade without updating `Gate::before` in `AppServiceProvider.php`, `@can` will return false for all tenant users. Alternatively, `@if (\App\Support\Context::hasPermission(...))` works immediately without touching `AppServiceProvider`.
- **SSRF and External API Calls**: Tests for Meta Cloud API and WhatsApp server interactions must maintain HTTP faking (`Http::fake(...)`) to prevent external network calls during automated test execution.

---

## 4. Conclusion

The testing infrastructure in COOCA is robust and well-structured with PHPUnit 12, SQLite in-memory database, and comprehensive seeders (`RbacSeeder`). However, **existing WhatsApp test coverage completely lacks authorization/RBAC verification**. 

To satisfy the acceptance criteria, the implementation team must:
1. Ensure `Gate::before` bridges `App\Models\User` to `\App\Support\Context::hasPermission($ability)` if `@can` directives are implemented in Blade.
2. Maintain controller-level middleware in `WhatsAppWebController`, `WhatsAppBroadcastWebController`, and `MetaWhatsAppOnboardingController`.
3. Add a comprehensive RBAC test suite (`tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php` or within `MerchantWhatsAppWebFeatureTest.php`) verifying 403 Forbidden on JSON endpoints, redirection on web endpoints, and proper element hiding in Blade views for unauthorized roles.

---

## 5. Verification Method

### 5.1 Step-by-Step Test Execution Commands

```powershell
# 1. Clear any cached configuration to ensure SQLite in-memory testing environment
php artisan config:clear

# 2. Verify all Blade views compile without syntax errors
php artisan view:cache
php artisan view:clear

# 3. Run existing WhatsApp feature test suite to verify no regressions
php artisan test tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php

# 4. Run the new WhatsApp RBAC Security Test suite
php artisan test tests/Feature/WhatsApp/WhatsAppRbacSecurityTest.php

# 5. Run related integration tests
php artisan test tests/Feature/WhatsApp/WhatsAppLogsExportTest.php
php artisan test tests/Feature/WhatsApp/MetaWhatsAppCloudApiTest.php
```

### 5.2 Concrete Testing Matrix & Test Plan Specification

The table below defines the exact test scenarios, roles, target routes, and expected assertions required:

| Test Case | Role / Permission | Request Type | Target Route | Expected Response / Assertion |
|---|---|---|---|---|
| `test_guest_redirected_to_login` | Unauthenticated | Web GET | `whatsapp.index` | `assertRedirect(route('login'))` |
| `test_cashier_without_whatsapp_perms_blocked_on_web` | `cashier` (No WA perms) | Web GET | `whatsapp.index`<br>`whatsapp.logs.index`<br>`whatsapp.broadcast.index` | `assertRedirect(route('portal'))` |
| `test_cashier_without_whatsapp_perms_receives_403_json` | `cashier` (No WA perms) | JSON GET/POST | `whatsapp.index`<br>`whatsapp.logs.index`<br>`whatsapp.meta.config`<br>`whatsapp.test`<br>`whatsapp.disconnect`<br>`whatsapp.broadcast.store` | `assertForbidden()`<br>`assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION'])` |
| `test_viewer_with_view_only_can_view_index_and_logs` | `viewer` (`whatsapp.view` only) | Web GET | `whatsapp.index`<br>`whatsapp.logs.index` | `assertOk()` |
| `test_viewer_with_view_only_blocked_from_manage_endpoints` | `viewer` (`whatsapp.view` only) | JSON POST | `whatsapp.test`<br>`whatsapp.disconnect`<br>`whatsapp.meta.exchange-code`<br>`whatsapp.meta.disconnect`<br>`whatsapp.broadcast.store` | `assertForbidden()` |
| `test_viewer_ui_does_not_render_restricted_action_buttons` | `viewer` (`whatsapp.view` only) | Web GET | `whatsapp.index`<br>`whatsapp.broadcast.index`<br>`whatsapp.logs.index` | `assertDontSee(__('whatsapp.new_broadcast_btn'))`<br>`assertDontSee(__('whatsapp.storage_pruning_btn'))` |
| `test_owner_can_access_all_endpoints_and_see_actions` | `owner` (Full access) | Web GET & JSON | All WhatsApp routes | `assertOk()`<br>Action buttons rendered |
| `test_manager_with_manage_perm_can_execute_all_actions` | `manager` (`whatsapp.manage`) | JSON POST | `whatsapp.test`<br>`whatsapp.broadcast.store` | `assertOk()` / Success response |
| `test_blade_views_render_without_missing_variables` | `owner` & `viewer` | Web GET | `index`, `broadcast`, `detail`, `logs` | `assertOk()` across both `id` and `en` locales |

### 5.3 Concrete Test Code Template for Implementation Agent

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Business;
use App\Models\User;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppRbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $viewer;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name'     => 'Toko Kopi RBAC',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);

        // 1. Owner (Omnipotent)
        $this->owner = User::factory()->create(['name' => 'Owner WA']);
        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // 2. Viewer (whatsapp.view only)
        $this->viewer = User::factory()->create(['name' => 'Viewer WA']);
        $this->business->users()->attach($this->viewer->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'viewer',
        ]);
        $this->viewer->update(['active_business_id' => $this->business->id]);

        // 3. Cashier (No WA permissions)
        $this->cashier = User::factory()->create(['name' => 'Kasir WA']);
        $this->business->users()->attach($this->cashier->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'cashier',
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);
    }

    public function test_unauthorized_cashier_blocked_with_403_on_json_endpoints(): void
    {
        // Index & Meta Config
        $this->actingAs($this->cashier)->getJson(route('whatsapp.index'))->assertForbidden();
        $this->actingAs($this->cashier)->getJson(route('whatsapp.meta.config'))->assertForbidden();
        $this->actingAs($this->cashier)->getJson(route('whatsapp.logs.index'))->assertForbidden();

        // Write Actions
        $this->actingAs($this->cashier)->postJson(route('whatsapp.test'), ['phone' => '08123456789'])->assertForbidden();
        $this->actingAs($this->cashier)->postJson(route('whatsapp.disconnect'))->assertForbidden();
        $this->actingAs($this->cashier)->postJson(route('whatsapp.broadcast.store'), [
            'title'   => 'Test',
            'message' => 'Test message',
        ])->assertForbidden();
    }

    public function test_unauthorized_cashier_redirected_to_portal_on_web_requests(): void
    {
        $this->actingAs($this->cashier)->get(route('whatsapp.index'))->assertRedirect(route('portal'));
        $this->actingAs($this->cashier)->get(route('whatsapp.logs.index'))->assertRedirect(route('portal'));
        $this->actingAs($this->cashier)->get(route('whatsapp.broadcast.index'))->assertRedirect(route('portal'));
    }

    public function test_viewer_can_view_index_and_logs_but_forbidden_on_manage_actions(): void
    {
        // Allowed Read Actions
        $this->actingAs($this->viewer)->get(route('whatsapp.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('whatsapp.logs.index'))->assertOk();

        // Forbidden Manage Actions
        $this->actingAs($this->viewer)->postJson(route('whatsapp.test'))->assertForbidden();
        $this->actingAs($this->viewer)->postJson(route('whatsapp.disconnect'))->assertForbidden();
        $this->actingAs($this->viewer)->postJson(route('whatsapp.meta.disconnect'))->assertForbidden();
        $this->actingAs($this->viewer)->postJson(route('whatsapp.broadcast.store'))->assertForbidden();
    }

    public function test_viewer_ui_hides_action_buttons_requiring_manage(): void
    {
        $response = $this->actingAs($this->viewer)->get(route('whatsapp.broadcast.index'));
        $response->assertOk();
        // New broadcast button should NOT be rendered for viewer
        $response->assertDontSee(__('whatsapp.new_broadcast_btn'));
    }

    public function test_owner_can_access_all_endpoints_and_see_action_buttons(): void
    {
        $response = $this->actingAs($this->owner)->get(route('whatsapp.broadcast.index'));
        $response->assertOk();
        $response->assertSee(__('whatsapp.new_broadcast_btn'));
    }
}
```

### 5.4 Invalidation Conditions
This investigation's findings and test plan remain valid unless:
1. `RequirePermission.php` middleware logic is modified to alter the 403 / redirect behavior.
2. `RbacSeeder.php` matrix alters permission assignments for `whatsapp.view` or `whatsapp.manage`.
3. Route definitions in `routes/owner.php` are relocated or altered.
