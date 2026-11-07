# Handoff Report: AI & AI Studio Routes, Controllers & RBAC Audit

**Date**: 2026-11-07  
**Agent**: Explorer 2 (Controllers, Routes & RBAC)  
**Target Repository**: `c:\laragon\www\cooca_core`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2`  
**Parent Agent**: `e2aae656-229b-42bb-a7be-8273bdd8a984`

---

## 1. Observation

### 1.1 Route Mapping & Route-Level Middleware
Direct inspection of `routes/owner.php` and `routes/admin.php` revealed all route groups governing AI and AI Studio:

#### A. AI Digital Company / AI Office (`routes/owner.php:638-702`)
Route group is currently guarded at the route level with:
`Route::middleware(['require.permission:ai.access', 'entitlement:ai'])->prefix('cooca-ai')->name('cooca-ai.')->group(...)`
- `GET /cooca-ai` (`cooca-ai.index`) -> `AiCompanyWebController@lobby`
- `GET /cooca-ai/office` (`cooca-ai.office.view`) -> `AiCompanyWebController@lobby`
- `GET /cooca-ai/office/executive` (`cooca-ai.office.executive`) -> `AiCompanyWebController@executiveOffice`
- `GET /cooca-ai/office/operations` (`cooca-ai.office.operations`) -> `AiCompanyWebController@operationsOffice`
- `GET /cooca-ai/office/growth` (`cooca-ai.office.growth`) -> `AiCompanyWebController@growthOffice`
- `POST /cooca-ai/ask` (`cooca-ai.ask`) -> `AiCompanyWebController@ask`
- `POST /cooca-ai/daily-check` (`cooca-ai.daily-check`) -> `AiCompanyWebController@triggerDailyCheck`
- `POST /cooca-ai/teamwork` (`cooca-ai.teamwork`) -> `AiCompanyWebController@teamwork`
- `POST /cooca-ai/goal` (`cooca-ai.goal`) -> `AiCompanyWebController@goal`
- `POST /cooca-ai/grill-me` (`cooca-ai.grill-me`) -> `AiCompanyWebController@grillMe`
- `POST /cooca-ai/boost` (`cooca-ai.boost`) -> `AiCompanyWebController@boost`
- `POST /cooca-ai/schedule` (`cooca-ai.schedule`) -> `AiCompanyWebController@schedule`
- `GET /cooca-ai/actions` (`cooca-ai.actions`) -> `AiCompanyWebController@actions`
- `POST /cooca-ai/actions/{proposal}/approve` (`cooca-ai.actions.approve`) -> `AiCompanyWebController@approveAction`
- `POST /cooca-ai/actions/{proposal}/revise` (`cooca-ai.actions.revise`) -> `AiCompanyWebController@reviseAction`
- `POST /cooca-ai/actions/{proposal}/reject` (`cooca-ai.actions.reject`) -> `AiCompanyWebController@rejectAction`
- `GET /cooca-ai/history` (`cooca-ai.history`) -> `AiCompanyWebController@history`
- `GET /cooca-ai/providers` (`cooca-ai.providers`) -> `AiCompanyWebController@providers`
- `POST /cooca-ai/providers` (`cooca-ai.providers.store`) -> `AiCompanyWebController@storeProvider`
- `POST /cooca-ai/providers/test` (`cooca-ai.providers.test`) -> `AiCompanyWebController@testProvider`
- `POST /cooca-ai/providers/detect-models` (`cooca-ai.providers.detect-models`) -> `AiCompanyWebController@detectModels`
- `GET /cooca-ai/agents/avatars` (`cooca-ai.agents.avatars`) -> `AiCompanyWebController@getAgentAvatars`
- `POST /cooca-ai/agents/{role}/avatar` (`cooca-ai.agents.avatar.update`) -> `AiCompanyWebController@updateAgentAvatar`
- `GET /cooca-ai/live-metrics` (`cooca-ai.live-metrics`) -> `AiCompanyWebController@liveMetrics`
- `GET /cooca-ai/rules` (`cooca-ai.rules.index`) -> `AiCompanyWebController@getRules`
- `POST /cooca-ai/rules` (`cooca-ai.rules.store`) -> `AiCompanyWebController@storeRule`
- `PATCH /cooca-ai/rules/{rule}/toggle` (`cooca-ai.rules.toggle`) -> `AiCompanyWebController@toggleRule`
- `DELETE /cooca-ai/rules/{rule}` (`cooca-ai.rules.destroy`) -> `AiCompanyWebController@deleteRule`
- Legacy 301 redirects and POST aliases are declared at `routes/owner.php:675-702` under `/ai/*`.
- Guardrail routes for Social Media Inbox (`routes/owner.php:1002-1005`) also route to `AiCompanyWebController@getRules`, `@storeRule`, `@toggleRule`, `@deleteRule`.

#### B. POS AI & Analytics (`routes/owner.php:631-636`)
Route group is currently guarded at route level with:
`Route::middleware('require.permission:ai.access')->group(...)`
- `GET /pos/ai` (`pos.ai.index`) and `GET /cooca-ai/pos` (`cooca-ai.pos`) -> `PosAiWebController@index`
- `POST /pos/ai/ask` (`pos.ai.ask`) -> `PosAiWebController@ask` (with route middleware `entitlement:ai`)
- `POST /pos/ai/execute-action` (`pos.ai.execute-action`) -> `PosAiWebController@executeAction` (with route middleware `entitlement:ai`)

#### C. AI Studio Projects & Generation (`routes/owner.php:704-729`)
Route group has clean routing without route-level permission middleware:
`Route::prefix('ai-studio')->name('ai-studio.')->group(...)`
- `GET /ai-studio` (`ai-studio.index`) -> `OwnerAiStudioWebController@index`
- `GET /ai-studio/create` (`ai-studio.create`) -> `OwnerAiStudioWebController@create`
- `POST /ai-studio/projects` (`ai-studio.projects.store`) -> `OwnerAiStudioWebController@store`
- `GET /ai-studio/projects/{project}` (`ai-studio.show`) -> `OwnerAiStudioWebController@show`
- `DELETE /ai-studio/projects/{project}` (`ai-studio.projects.destroy`) -> `OwnerAiStudioWebController@destroy`
- `POST /ai-studio/prompt/compile` (`ai-studio.prompt.compile`) -> `OwnerAiStudioWebController@compilePrompt`
- `POST /ai-studio/generate` (`ai-studio.generate`) -> `OwnerAiStudioWebController@dispatchGeneration`
- `GET /ai-studio/projects/{project}/generations/{generation}/status` (`ai-studio.generation.status`) -> `OwnerAiStudioWebController@generationStatus`
- `POST /ai-studio/assets/{asset}/export-to-catalog` (`ai-studio.assets.export-to-catalog`) -> `OwnerAiStudioWebController@exportToCatalog`
- `POST /ai-studio/assets/{asset}/export-to-social-media` (`ai-studio.assets.export-to-social-media`) -> `OwnerAiStudioWebController@exportToSocialMedia`
- `POST /ai-studio/assets/{asset}/toggle-favorite` (`ai-studio.assets.toggle-favorite`) -> `OwnerAiStudioWebController@toggleFavoriteAsset`
- `DELETE /ai-studio/assets/{asset}` (`ai-studio.assets.destroy`) -> `OwnerAiStudioWebController@destroyAsset`
- `GET /ai-studio/credit-history` (`ai-studio.credit-history`) -> `OwnerAiStudioWebController@creditHistory`
- `GET /ai-studio/topup-packages` (`ai-studio.topup.packages`) -> `OwnerAiStudioWebController@topupPackages`
- `POST /ai-studio/topup` (`ai-studio.topup.order`) -> `OwnerAiStudioWebController@orderTopup`
- `GET /ai-studio/topup/{payment}/status` (`ai-studio.topup.status`) -> `OwnerAiStudioWebController@checkTopupStatus`

#### D. AI Studio Inspiration Library (`routes/owner.php:730-745`)
- `GET /ai-studio/inspiration` (`ai-studio.inspiration.index`) -> `OwnerInspirationWebController@index`
- `GET /ai-studio/inspiration/search` (`ai-studio.inspiration.search`) -> `OwnerInspirationWebController@search`
- `GET /ai-studio/inspiration/categories` (`ai-studio.inspiration.categories`) -> `OwnerInspirationWebController@categories`
- `GET /ai-studio/inspiration/collections` (`ai-studio.inspiration.collections`) -> `OwnerInspirationWebController@collections`
- `POST /ai-studio/inspiration/collections` (`ai-studio.inspiration.collections.store`) -> `OwnerInspirationWebController@storeCollection`
- `GET /ai-studio/inspiration/collections/{collection}` (`ai-studio.inspiration.collections.show`) -> `OwnerInspirationWebController@showCollection`
- `POST /ai-studio/inspiration/collections/{collection}/items` (`ai-studio.inspiration.collections.items.store`) -> `OwnerInspirationWebController@addItemToCollection`
- `DELETE /ai-studio/inspiration/collections/{collection}/items/{item}` (`ai-studio.inspiration.collections.items.destroy`) -> `OwnerInspirationWebController@removeItemFromCollection`
- `GET /ai-studio/inspiration/favorites` (`ai-studio.inspiration.favorites`) -> `OwnerInspirationWebController@favorites`
- `POST /ai-studio/inspiration/favorites/toggle` (`ai-studio.inspiration.favorites.toggle`) -> `OwnerInspirationWebController@toggleFavorite`
- `GET /ai-studio/inspiration/my-library` (`ai-studio.inspiration.my-library`) -> `OwnerInspirationWebController@myLibrary`
- `POST /ai-studio/inspiration/my-library/upload` (`ai-studio.inspiration.my-library.upload`) -> `OwnerInspirationWebController@uploadAsset`
- `POST /ai-studio/inspiration/use-as-reference` (`ai-studio.inspiration.use-as-reference`) -> `OwnerInspirationWebController@useAsReference`
- `GET /ai-studio/inspiration/{inspiration}` (`ai-studio.inspiration.show`) -> `OwnerInspirationWebController@show`

---

### 1.2 Controller-Level Authorization Status

| Controller | Implements `HasMiddleware` | Constructor Middleware | Method-Level Checks | Encapsulation Status |
|---|---|---|---|---|
| `OwnerAiStudioWebController` | **YES** (`line 48`) | None | `abort_unless($payment->business_id === $business->id, 403)` | **Compliant** (`ai_studio.view`, `generate`, `manage`) |
| `OwnerInspirationWebController` | **YES** (`line 38`) | None | Tenant abort check (`line 307, 332, 383`) | **Compliant** (`ai_studio.view`, `manage`) |
| `AdminAiTokenController` | **YES** (`line 28`) | None | None | **Compliant** (`require.admin.permission`) |
| `AdminAiStudioVisualController` | **YES** (`line 22`) | None | None | **Compliant** (`require.admin.permission`) |
| `AdminAiStudioSourceController` | **YES** (`line 18`) | None | None | **Compliant** (`require.admin.permission`) |
| `AdminAiStudioPricingController` | **YES** (`line 20`) | None | None | **Compliant** (`require.admin.permission`) |
| `AdminInspirationManagementController`| **YES** (`line 31`) | None | None | **Compliant** (`require.admin.permission`) |
| `AiCompanyWebController` | **NO** (`line 41`) | **NO** | `approveAction` calls `canApprove` (`line 496`) | **NON-COMPLIANT** (0 Controller RBAC) |
| `PosAiWebController` | **NO** (`line 15`) | **NO** | `executeAction` uses FormRequest | **NON-COMPLIANT** (0 Controller RBAC) |

**Verbatim Code Quote - Non-compliant `AiCompanyWebController.php:41`**:
```php
final class AiCompanyWebController extends Controller
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator = new AiOrchestrator(),
        private readonly AiActionExecutor $actionExecutor = new AiActionExecutor(),
        private readonly AiActionPolicy $actionPolicy = new AiActionPolicy(),
        private readonly AiProviderManager $providerManager = new AiProviderManager(),
        private readonly AiBusinessTeamworkService $teamworkService = new AiBusinessTeamworkService(),
    ) {}
    // 29 public methods without static middleware() method
}
```

---

### 1.3 Database Seeders & Permission Matrix (`database/seeders/RbacSeeder.php`)

Direct inspection of `database/seeders/RbacSeeder.php:178-187` revealed:
```php
// ─── AI Assistant ─────────────────────────────────────────
'ai.access'                   => ['AI Assistant & Rekomendasi',             'ai',          'Mengakses fitur prediksi stok, analitik pintar, dan chatbot AI.'],

// ─── AI Studio & Viral Inspiration ───────────────────────
'ai_studio.view'              => ['Lihat AI Studio',                        'ai_studio',   'Melihat dashboard AI Studio dan perpustakaan inspirasi viral.'],
'ai_studio.generate'          => ['Generate Gambar AI Studio',              'ai_studio',   'Membuat gambar produk dan media sosial dengan AI Studio.'],
'ai_studio.manage'            => ['Kelola AI Studio',                       'ai_studio',   'Mengelola proyek, aset inspirasi, dan riwayat generasi AI Studio.'],
'admin.ai_studio.sources'     => ['Kelola Sumber Provider AI Studio',       'ai_studio',   'Mengatur konektor sumber dan kredensial API provider inspirasi.'],
'admin.ai_studio.pricing'     => ['Kelola Tarif Kredit AI Studio',          'ai_studio',   'Mengatur margin dan konversi kredit generasi AI.'],
```

#### Discovery of Phantom Permission `ai.approve`:
In `app/Domain/Ai/Policy/AiActionPolicy.php:68`:
```php
return Context::hasPermission('ai.approve');
```
In `app/Http/Requests/Ai/ExecuteAiPosActionRequest.php:21, 25`:
```php
if ($actionType === 'create_invoice') {
    return Context::hasPermission('invoices.create') || Context::hasPermission('pos.orders') || Context::hasPermission('ai.approve');
}
if ($actionType === 'create_quotation') {
    return Context::hasPermission('quotations.create') || Context::hasPermission('pos.orders') || Context::hasPermission('ai.approve');
}
```
`ai.approve` is checked in business logic, but **does NOT exist in `RbacSeeder.php`**!

#### Discovery of Missing `ai.manage`:
Endpoints `storeProvider`, `testProvider`, `detectModels` (which manage external API Keys and endpoints for OpenAI/Anthropic/Gemini) and guardrails endpoints `storeRule`, `toggleRule`, `deleteRule` in `AiCompanyWebController` currently have no dedicated permission. Any user with generic `ai.access` who sends a POST to `/cooca-ai/providers` can reconfigure company API keys!

---

### 1.4 View-Level Authorization Scan (31 Blade Files)

Scanning `resources/views/app/ai/` (13 files) and `resources/views/app/ai-studio/` (11 files) revealed that **29 out of 31 views** have ZERO authorization guards around interactive action buttons and forms:

1. `resources/views/app/ai/actions.blade.php`:
   - Approve action form (`cooca-ai.actions.approve`): **Unprotected**
   - Revise action form (`cooca-ai.actions.revise`): **Unprotected**
   - Reject action form (`cooca-ai.actions.reject`): **Unprotected**
2. `resources/views/app/ai/offices/executive.blade.php`:
   - Quick Reject & Approve forms (`cooca-ai.actions.reject`, `cooca-ai.actions.approve`): **Unprotected**
3. `resources/views/app/ai/offices/operations.blade.php`:
   - Quick Reject & Approve forms: **Unprotected**
4. `resources/views/app/ai/offices/growth.blade.php`:
   - Quick Reject & Approve forms: **Unprotected**
5. `resources/views/app/ai/providers.blade.php`:
   - Form Save Provider (`cooca-ai.providers.store`): **Unprotected**
   - Test Provider button (`cooca-ai.providers.test`): **Unprotected**
   - Detect Models button (`cooca-ai.providers.detect-models`): **Unprotected**
6. `resources/views/app/ai-studio/create.blade.php`:
   - Form Create Project (`ai-studio.projects.store`): **Unprotected**
   - Compile Prompt button (`ai-studio.prompt.compile`): **Unprotected**
7. `resources/views/app/ai-studio/show.blade.php`:
   - Dispatch Generation button (`ai-studio.generate`): **Unprotected**
   - Export to Catalog modal & submit (`ai-studio.assets.export-to-catalog`): **Unprotected**
   - Export to Social Media modal & submit (`ai-studio.assets.export-to-social-media`): **Unprotected**
   - Delete Asset button (`ai-studio.assets.destroy`): **Unprotected**
   - Delete Project button (`ai-studio.projects.destroy`): **Unprotected**
8. `resources/views/app/ai-studio/inspiration/collections.blade.php` & `collection-detail.blade.php`:
   - Create Collection modal & submit (`ai-studio.inspiration.collections.store`): **Unprotected**
   - Delete Item from Collection form (`ai-studio.inspiration.collections.items.destroy`): **Unprotected**
   - Use As Reference form (`ai-studio.inspiration.use-as-reference`): **Unprotected**
9. `resources/views/app/ai-studio/inspiration/my-library.blade.php`:
   - Upload Asset modal & submit (`ai-studio.inspiration.my-library.upload`): **Unprotected**
10. `resources/views/app/ai-studio/inspiration/index.blade.php` & `favorites.blade.php`:
    - Favorite toggle button: **Unprotected**
    - Use As Reference submit: **Unprotected**
11. `resources/views/app/ai-studio/partials/topup-modal.blade.php`:
    - Order Top-up submit (`ai-studio.topup.order`): **Unprotected**

---

## 2. Logic Chain

1. **Premise 1 (Requirement R2 & Skill Directive)**:
   The user requested `/laravel-rbac` and Controller-Level RBAC. According to `C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md`:
   - Permission middleware MUST be encapsulated directly inside controllers using Laravel 11+ `HasMiddleware` interface.
   - Route-level middleware `->middleware('require.permission:...')` in `routes/owner.php` should be removed to achieve *Clean Routing*.
   - Action buttons, forms, and tabs in Blade views MUST be guarded with `@if (\App\Support\Context::hasPermission(...))`.
2. **Premise 2 (Observation of Controllers)**:
   `OwnerAiStudioWebController` and `OwnerInspirationWebController` already adhere to `HasMiddleware`, mapping permissions `ai_studio.view`, `ai_studio.generate`, and `ai_studio.manage`.
   However, `AiCompanyWebController` and `PosAiWebController` lack `HasMiddleware`. Their protection currently relies solely on the route group in `routes/owner.php`.
3. **Premise 3 (Granularity & Principle of Least Privilege)**:
   The route group currently applies `require.permission:ai.access` broadly to all 29 methods in `AiCompanyWebController`. This allows any employee role (such as cashier or warehouse staff with `ai.access`) to hit `/cooca-ai/providers` and alter third-party API keys or guardrails. By introducing `ai.manage` and `ai.approve`, sensitive actions are separated from standard view/chat interactions.
4. **Premise 4 (Elimination of Phantom Permissions)**:
   `AiActionPolicy.php:68` checks `Context::hasPermission('ai.approve')`, but `ai.approve` is missing from `RbacSeeder.php`. Because `Context::hasPermission('ai.approve')` checks registered permission slugs, it fails for any role other than hardcoded owner/admin bypasses. Registering `ai.approve` in `RbacSeeder.php` turns a phantom permission into a recognized system capability.
5. **Premise 5 (Defense-in-Depth UI Guarding)**:
   Server-side authorization rejects unauthorized requests with `403 Forbidden`, but good UX dictates that users without permission should not see action buttons that trigger 403 errors. Wrapping action buttons in Blade views with `@if (\App\Support\Context::hasPermission(...) || \App\Support\Context::isAdminOrOwner())` completes the defense-in-depth model.

---

## 3. Caveats

1. **Entitlement vs. Permission**:
   `routes/owner.php` applies `entitlement:ai` on `cooca-ai` and `pos/ai/ask`. `entitlement:ai` checks business subscription tiers (SaaS plan capability), while `require.permission:*` checks user role authorization. When migrating permission checks into controllers, `entitlement:ai` should remain either on the route group or also be encapsulated if desired. Keeping `entitlement:ai` on the route group is standard in Cooca for SaaS feature-tier gating.
2. **Owner & Admin Super-Access**:
   In Cooca, `Context::isOwner()` and `Context::isAdminOrOwner()` bypass permission checks. In `RbacSeeder.php`, `owner` automatically receives all permission slugs (`$ownerSlugs = $allSlugs;`). Therefore, adding `ai.approve` and `ai.manage` will automatically be granted to owners, while explicitly empowering managers and admins.
3. **Backwards Compatibility**:
   Legacy route fallbacks `/ai/*` point to `AiCompanyWebController`. Because `HasMiddleware` applies to the controller itself regardless of route URI, legacy routes will be automatically secured without requiring redundant route middleware.

---

## 4. Conclusion & Actionable Roadmap

### 4.1 Permission Definitions to Add to `database/seeders/RbacSeeder.php`

Add the following to `permissionMatrix()`:
```php
// ─── AI Assistant & Digital Company ───────────────────────
'ai.access'                   => ['AI Assistant & Rekomendasi',             'ai',          'Mengakses fitur prediksi stok, analitik pintar, chatbot AI, dan kantor virtual.'],
'ai.approve'                  => ['Otorisasi & Eksekusi Aksi AI',           'ai',          'Menyetujui, merevisi, atau menolak usulan aksi otomatis agen AI serta mengeksekusi rekomendasi POS.'],
'ai.manage'                   => ['Kelola AI & Konfigurasi Provider',       'ai',          'Mengatur kredensial API key provider AI, model LLM, aturan guardrails DO/DONT, dan konfigurasi avatar agen.'],

// ─── AI Studio & Viral Inspiration ───────────────────────
'ai_studio.view'              => ['Lihat AI Studio',                        'ai_studio',   'Melihat dashboard AI Studio dan perpustakaan inspirasi viral.'],
'ai_studio.generate'          => ['Generate Gambar AI Studio',              'ai_studio',   'Membuat gambar produk dan media sosial dengan AI Studio.'],
'ai_studio.manage'            => ['Kelola AI Studio',                       'ai_studio',   'Mengelola proyek, aset inspirasi, dan riwayat generasi AI Studio.'],
```

Map in `roleDefinitions()`:
- `owner`: Automatically gets all slugs.
- `manager`: Add `'ai.approve'`.
- `admin`: Add `'ai.approve'`, `'ai.manage'`.
- `cashier`, `warehouse`, `finance`, `staff`: Retain `'ai.access'`.

---

### 4.2 File-by-File Controller-Level RBAC Enforcement

#### 1. `app/Http/Controllers/Web/Ai/AiCompanyWebController.php`
- Add `use Illuminate\Routing\Controllers\HasMiddleware;`
- Add `use Illuminate\Routing\Controllers\Middleware;`
- Class declaration: `final class AiCompanyWebController extends Controller implements HasMiddleware`
- Implement `middleware()` method:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:ai.access', only: [
            'index',
            'lobby',
            'executiveOffice',
            'operationsOffice',
            'growthOffice',
            'history',
            'actions',
            'liveMetrics',
            'getAgentAvatars',
            'getRules',
            'ask',
            'triggerDailyCheck',
            'teamwork',
            'goal',
            'grillMe',
            'boost',
            'schedule',
        ]),
        new Middleware('require.permission:ai.approve', only: [
            'approveAction',
            'reviseAction',
            'rejectAction',
        ]),
        new Middleware('require.permission:ai.manage', only: [
            'providers',
            'storeProvider',
            'testProvider',
            'detectModels',
            'updateAgentAvatar',
            'storeRule',
            'toggleRule',
            'deleteRule',
        ]),
    ];
}
```

#### 2. `app/Http/Controllers/Web/Ai/PosAiWebController.php`
- Add `use Illuminate\Routing\Controllers\HasMiddleware;`
- Add `use Illuminate\Routing\Controllers\Middleware;`
- Class declaration: `final class PosAiWebController extends Controller implements HasMiddleware`
- Implement `middleware()` method:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:ai.access', only: [
            'index',
            'ask',
        ]),
        new Middleware('require.permission:ai.approve', only: [
            'executeAction',
        ]),
    ];
}
```

#### 3. `routes/owner.php` Clean Routing Migration
- Lines 632-636: Remove `require.permission:ai.access` from POS AI group:
  ```php
  // SEBELUM:
  Route::middleware('require.permission:ai.access')->group(function (): void {
      Route::get('/pos/ai', [PosAiWebController::class, 'index'])->name('pos.ai.index');
      ...
  });

  // SESUDAH:
  Route::group(function (): void {
      Route::get('/pos/ai', [PosAiWebController::class, 'index'])->name('pos.ai.index');
      Route::post('/pos/ai/ask', [PosAiWebController::class, 'ask'])->middleware('entitlement:ai')->name('pos.ai.ask');
      Route::post('/pos/ai/execute-action', [PosAiWebController::class, 'executeAction'])->middleware('entitlement:ai')->name('pos.ai.execute-action');
  });
  ```
- Lines 638-672: Remove `require.permission:ai.access` from `cooca-ai` prefix:
  ```php
  // SEBELUM:
  Route::middleware(['require.permission:ai.access', 'entitlement:ai'])->prefix('cooca-ai')->name('cooca-ai.')->group(...);

  // SESUDAH:
  Route::middleware('entitlement:ai')->prefix('cooca-ai')->name('cooca-ai.')->group(...);
  ```
- Lines 674-702: Remove `require.permission:ai.access` from legacy `ai` prefix:
  ```php
  // SEBELUM:
  Route::middleware(['require.permission:ai.access', 'entitlement:ai'])->prefix('ai')->name('ai.')->group(...);

  // SESUDAH:
  Route::middleware('entitlement:ai')->prefix('ai')->name('ai.')->group(...);
  ```

---

### 4.3 View-Level Authorization Protection Roadmap

| View File | UI Component / Action | Required Guard Condition |
|---|---|---|
| `resources/views/app/ai/actions.blade.php` | Approve, Revise, Reject forms & buttons | `@if (\App\Support\Context::hasPermission('ai.approve') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai/offices/executive.blade.php` | Proposal Approve/Reject buttons | `@if (\App\Support\Context::hasPermission('ai.approve') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai/offices/growth.blade.php` | Proposal Approve/Reject buttons | `@if (\App\Support\Context::hasPermission('ai.approve') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai/offices/operations.blade.php` | Proposal Approve/Reject buttons | `@if (\App\Support\Context::hasPermission('ai.approve') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai/providers.blade.php` | Provider save form, Test button, Detect Models button | `@if (\App\Support\Context::hasPermission('ai.manage') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai/providers.blade.php` | If no `ai.manage`, show read-only notice banner | `@else <div class="alert ...">{{ __('ai.providers.readonly_notice') }}</div> @endif` |
| `resources/views/app/ai/index.blade.php` | "Terapkan Rekomendasi" (Execute Action Draft) modal button | `@if (\App\Support\Context::hasPermission('ai.approve') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/app/ai-studio/index.blade.php` | "Buat Proyek Baru" header link/button | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/index.blade.php` | Top-up Kredit button & modal trigger | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::hasPermission('billing.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/create.blade.php` | Form submit & Prompt Compiler button | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/show.blade.php` | "Generate Variasi" button | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/show.blade.php` | "Export ke Katalog" modal button | `@if (\App\Support\Context::hasPermission('products.create') \|\| \App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/show.blade.php` | "Export ke Media Sosial" modal button | `@if (\App\Support\Context::hasPermission('social_media.manage') \|\| \App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/show.blade.php` | "Hapus Aset" & "Hapus Proyek" buttons | `@if (\App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/inspiration/index.blade.php` | "Gunakan Sebagai Referensi" form submit | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/inspiration/collections.blade.php` | "Buat Koleksi" button & modal form | `@if (\App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/inspiration/collection-detail.blade.php` | "Hapus Item dari Koleksi" form | `@if (\App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/app/ai-studio/inspiration/my-library.blade.php` | "Upload Aset Referensi" button & modal form | `@if (\App\Support\Context::hasPermission('ai_studio.manage') \|\| \App\Support\Context::isOwner())` |
| `resources/views/layouts/partials/sidebar.blade.php` | Submenu `cooca-ai.providers` | `@if (\App\Support\Context::hasPermission('ai.manage') \|\| \App\Support\Context::isAdminOrOwner())` |
| `resources/views/layouts/partials/sidebar.blade.php` | Submenu `ai-studio.create` | `@if (\App\Support\Context::hasPermission('ai_studio.generate') \|\| \App\Support\Context::isOwner())` |

---

## 5. Verification Method

To independently verify all findings and test subsequent implementations:

1. **Verify Syntax of Controllers and Views**:
   ```bash
   php -l app/Http/Controllers/Web/Ai/AiCompanyWebController.php
   php -l app/Http/Controllers/Web/Ai/PosAiWebController.php
   php -l app/Http/Controllers/Web/AiStudio/OwnerAiStudioWebController.php
   php -l app/Http/Controllers/Web/AiStudio/OwnerInspirationWebController.php
   php -l database/seeders/RbacSeeder.php
   ```

2. **Verify RBAC Seeder Execution (Idempotent)**:
   ```bash
   php artisan db:seed --class=RbacSeeder --force
   ```
   *Expected result*: Confirms all permissions including `ai.approve` and `ai.manage` are registered and assigned to target roles without errors.

3. **Verify Route Registration & Middleware Binding**:
   ```bash
   php artisan route:list --name=cooca-ai
   php artisan route:list --name=ai-studio
   php artisan route:list --name=pos.ai
   ```
   *Expected result*: All endpoints correctly mapped to controller actions with clean routes.

4. **Execute Feature Test Suite**:
   ```bash
   php artisan test tests/Feature/AiStudio/AiStudioWebControllerTest.php
   php artisan test tests/Feature/AiDigitalCompanyTest.php
   ```
   *Expected result*: 100% tests pass without regression.

5. **Invalidation Conditions**:
   - If any endpoint in `AiCompanyWebController` returns `403` to an authenticated `owner` or `admin`, verify that `Context::isOwner()` is properly checked or `owner` has all permission slugs in `RbacSeeder.php`.
   - If a cashier receives `403` on `cooca-ai.index`, verify that `ai.access` remains mapped to `cashier` in `RbacSeeder.php`.
