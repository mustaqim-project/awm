# RBAC & Authorization Survey: Analytics, Approvals, and Billing

**Explorer**: Explorer 2 (RBAC Authorization Explorer)  
**Date**: 2026-11-07  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey`  
**Target Modules**: `analytics`, `approvals`, `billing` in `resources/views/app/`  

---

## 1. Observation

### 1.1 Routes & Controllers Mapping

#### A. Analytics Module
- **Route Definition**:
  - `routes/owner.php` line 398:
    ```php
    Route::get('/analytics', [AnalyticsWebController::class, 'index'])->name('analytics.index');
    ```
  - Note: In `routes/owner.php`, the route does NOT declare route-level middleware; it relies on controller-level middleware.
  - An API counterpart exists in `routes/api.php` line 1576:
    ```php
    Route::get('/analytics', [\App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController::class, 'analytics'])->middleware('require.permission:dashboard.view');
    ```
- **Controller**: `app/Http/Controllers/Web/AnalyticsWebController.php`
  - Implements: `Illuminate\Routing\Controllers\HasMiddleware` (line 26)
  - Controller-level Middleware (lines 31–36):
    ```php
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:reports.view', only: ['index']),
        ];
    }
    ```
  - Method: `public function index(Request $request): View|JsonResponse` (lines 41–116)
  - Renders View: `resources/views/app/analytics/index.blade.php` (line 115) or returns JSON if `$request->ajax() || $request->wantsJson()` (lines 108–113).
  - Multi-tenant tenant check: Line 43 calls `Context::requireBusiness()`, and Location filtering validates tenant ownership (`Location::where('business_id', $business->id)...`, line 46).

#### B. Approvals Module
- **Route Definitions**: `routes/owner.php` lines 274–291:
  ```php
  Route::middleware('require.permission:approvals.view')->group(function (): void {
      Route::get('/approvals', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'inbox'])->name('approvals.inbox');
      Route::get('/approvals/history', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'history'])->name('approvals.history');
  });
  Route::middleware('require.permission:approvals.manage')->group(function (): void {
      Route::post('/approvals/{approvalRequest}/approve', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'approve'])->name('approvals.approve');
      Route::post('/approvals/{approvalRequest}/reject', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'reject'])->name('approvals.reject');
      Route::get('/settings/approval-rules', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesIndex'])->name('approval-rules.index');
      Route::post('/settings/approval-rules', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesStore'])->name('approval-rules.store');
      Route::put('/settings/approval-rules/{approvalRule}', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesUpdate'])->name('approval-rules.update');
      Route::delete('/settings/approval-rules/{approvalRule}', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesDestroy'])->name('approval-rules.destroy');

      // Aliases
      Route::get('/settings/rules/approvals', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesIndex'])->name('settings.approval-rules.index');
      Route::post('/settings/rules/approvals', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesStore'])->name('settings.approval-rules.store');
      Route::put('/settings/rules/approvals/{approvalRule}', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesUpdate'])->name('settings.approval-rules.update');
      Route::delete('/settings/rules/approvals/{approvalRule}', [\App\Http\Controllers\Web\Approval\ApprovalWebController::class, 'rulesDestroy'])->name('settings.approval-rules.destroy');
  });
  ```
- **Controller**: `app/Http/Controllers/Web/Approval/ApprovalWebController.php`
  - Implements: `Illuminate\Routing\Controllers\HasMiddleware` (line 21)
  - Controller-level Middleware (lines 26–39):
    ```php
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:approvals.view', only: ['inbox', 'history']),
            new Middleware('require.permission:approvals.manage', only: [
                'approve',
                'reject',
                'rulesIndex',
                'rulesStore',
                'rulesUpdate',
                'rulesDestroy',
            ]),
        ];
    }
    ```
  - Methods:
    - `inbox(Request $request): View` -> renders `app.approvals.inbox` (line 69)
    - `history(Request $request): View` -> renders `app.approvals.history` (line 81)
    - `approve(ApprovalRequest $approvalRequest, Request $request): RedirectResponse` (line 87)
    - `reject(ApprovalRequest $approvalRequest, Request $request): RedirectResponse` (line 128)
    - `rulesIndex(): View` -> renders `app.approvals.rules` (line 182)
    - `rulesStore(Request $request): RedirectResponse` (line 188)
    - `rulesUpdate(ApprovalRule $approvalRule, Request $request): RedirectResponse` (line 237)
    - `rulesDestroy(ApprovalRule $approvalRule): RedirectResponse` (line 288)

#### C. Billing Module
- **Route Definitions**: `routes/owner.php` lines 568–583:
  ```php
  Route::get('/billing', [BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing');
  Route::get('/billing/limits', [BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing.limits');
  Route::get('/patungan', [SubscriptionCheckoutWebController::class, 'checkout'])->middleware('require.permission:billing.manage')->name('billing.patungan');
  Route::post('/billing/upgrade', [BillingAndLimitWebController::class, 'upgrade'])->middleware('require.permission:billing.manage')->name('billing.upgrade');
  Route::get('/billing/checkout', [SubscriptionCheckoutWebController::class, 'checkout'])->middleware('require.permission:billing.manage')->name('billing.checkout');
  Route::post('/billing/promo/validate', [SubscriptionCheckoutWebController::class, 'validatePromo'])->middleware('require.permission:billing.manage')->name('billing.promo.validate');
  Route::post('/billing/order', [SubscriptionCheckoutWebController::class, 'store'])->middleware('require.permission:billing.manage')->name('billing.order.store');
  Route::match(['GET', 'POST'], '/billing/payments/{payment}', [SubscriptionCheckoutWebController::class, 'payment'])->middleware('require.permission:billing.view')->name('billing.payment.show');
  Route::get('/billing/payments/{payment}/status', [SubscriptionCheckoutWebController::class, 'checkStatus'])->middleware('require.permission:billing.view')->name('billing.payment.status');
  Route::get('/billing/payments/{payment}/invoice', [SubscriptionCheckoutWebController::class, 'invoice'])->middleware('require.permission:billing.view')->name('billing.payment.invoice');
  Route::post('/billing/payments/{payment}/upload-proof', [SubscriptionCheckoutWebController::class, 'uploadProof'])->middleware('require.permission:billing.manage')->name('billing.payment.upload');
  Route::get('/billing/payments/{payment}/proof', [SubscriptionCheckoutWebController::class, 'viewProof'])->middleware('require.permission:billing.view')->name('billing.payment.proof');
  Route::get('/billing/history', [SubscriptionCheckoutWebController::class, 'history'])->middleware('require.permission:billing.view')->name('billing.history');
  Route::post('/billing/storage/recalculate', [BillingAndLimitWebController::class, 'recalculateStorage'])->middleware('require.permission:billing.manage')->name('billing.storage.recalculate');
  Route::get('/billing/storage/files', [BillingAndLimitWebController::class, 'listFiles'])->middleware('require.permission:billing.view')->name('billing.storage.files');
  Route::delete('/billing/storage/files/{storageFile}', [BillingAndLimitWebController::class, 'destroyStorageFile'])->middleware('require.permission:billing.manage')->name('billing.storage.files.destroy');
  ```
- **Controllers**:
  1. `app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php`
     - Implements: `Illuminate\Routing\Controllers\HasMiddleware` (line 21)
     - Controller-level Middleware (lines 26–32):
       ```php
       public static function middleware(): array
       {
           return [
               new Middleware('require.permission:billing.view', only: ['index', 'listFiles']),
               new Middleware('require.permission:billing.manage', only: ['upgrade', 'recalculateStorage', 'destroyStorageFile']),
           ];
       }
       ```
     - Method `index()`: renders `resources/views/app/billing/limits.blade.php` (line 65)
  2. `app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php`
     - Implements: `Illuminate\Routing\Controllers\HasMiddleware` (line 27)
     - Controller-level Middleware (lines 32–38):
       ```php
       public static function middleware(): array
       {
           return [
               new Middleware('require.permission:billing.view', only: ['payment', 'checkStatus', 'invoice', 'viewProof', 'history']),
               new Middleware('require.permission:billing.manage', only: ['checkout', 'validatePromo', 'store', 'uploadProof']),
           ];
       }
       ```
     - Methods and Views:
       - `checkout()`: renders `resources/views/app/billing/checkout.blade.php` (line 141)
       - `payment()`: renders `resources/views/app/billing/payment.blade.php` (line 458)
       - `invoice()`: renders `resources/views/app/billing/invoice.blade.php` (line 547)
       - `history()`: renders `resources/views/app/billing/history.blade.php` (line 587)

---

### 1.2 Authorization Architecture & Gate Mechanics in Cooca

- **Gate Registration in `app/Providers/AppServiceProvider.php` (lines 139–153)**:
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
  *Direct consequence*: Any call in Blade to `@can('foo.bar')` or `@cannot('foo.bar')` automatically invokes `Gate::before`, which executes `\App\Support\Context::hasPermission('foo.bar')`. This guarantees 100% interoperability between native Laravel Blade directives and Cooca's multi-tenant permission engine.

- **`Context::hasPermission(string $permission)` in `app/Support/Context.php` (lines 197–216)**:
  1. Checks if business has disabled the module (`$business->isPermissionEnabled($permission)`). If disabled, returns `false` (even for Owner).
  2. If user role is `owner`, returns `true` (Owner has superuser rights for enabled modules).
  3. Checks `$membership->hasPermission($permission)`.
  4. Fallback checks `in_array($permission, self::permissions(), true)`.

- **Middleware `require.permission` in `app/Http/Middleware/RequirePermission.php`**:
  - Registered in `bootstrap/app.php` line 54: `'require.permission' => \App\Http\Middleware\RequirePermission::class`.
  - Supports multiple variadic permission arguments (`string ...$permissions`).
  - Grants access if user has ANY of the specified permissions (`$hasAnyPermission`).
  - For API/JSON requests: returns HTTP 403 Forbidden with JSON error payload `{ "error_code": "FORBIDDEN_PERMISSION" }`.
  - For Web requests: redirects to `route('portal')` with a flash alert message.

---

### 1.3 Database Seeders Inspection (`database/seeders/RbacSeeder.php`)

- **Permissions Currently Defined**:
  - `approvals.view`: `['Lihat Kotak Masuk Persetujuan', 'approvals', 'Melihat daftar dokumen yang menunggu persetujuan (MAR Inbox).']` (line 103)
  - `approvals.manage`: `['Otorisasi & Kelola Aturan Persetujuan', 'approvals', 'Menyetujui, menolak tiket dokumen, dan mengatur aturan plafon approval.']` (line 104)
  - `reports.view`: `['Lihat Laporan', 'reports', 'Akses halaman laporan dan analitik.']` (line 132)
  - `reports.financial`: `['Laporan Finansial (Laba/Rugi)', 'reports', 'Melihat laporan laba rugi, arus kas, dan analitik pendapatan.']` (line 133)
  - `reports.export`: `['Export Laporan', 'reports', 'Export laporan ke Excel/PDF.']` (line 134)
  - `reports.costing`: `['Laporan HPP & Kalkulasi', 'reports', 'Akses laporan kalkulator HPP dan profitabilitas.']` (line 135)
  - `billing.view`: `['Lihat Paket & Tagihan', 'billing', 'Melihat paket langganan dan riwayat tagihan.']` (line 175)
  - `billing.manage`: `['Kelola Langganan & Upgrade', 'billing', 'Mengelola paket langganan Cooca, upgrade, dan riwayat tagihan.']` (line 176)

- **Critical Seeders Observations**:
  1. **Analytics has NO dedicated permission in `RbacSeeder.php`**: There is no `analytics.view` or `analytics.export`. The web controller borrows `reports.view`.
  2. **Approvals bundles approval actions and rule administration into a single permission (`approvals.manage`)**: A manager who should only be authorized to click approve/reject on transactions currently has to be given `approvals.manage`, which also allows them to view, edit, and delete company approval rules (`ApprovalRule`).
  3. **Role Assignments in `RbacSeeder.php`**:
     - `owner`: Has all permissions.
     - `manager`: Has `approvals.view`, `approvals.manage`, `reports.view`, `reports.financial`, `reports.export`, `reports.costing`. Does NOT have billing permissions.
     - `admin`: Has `manager` permissions + user management. Does NOT have billing.
     - `finance`: Has `reports.view`, `reports.financial`, `reports.export`, but does NOT have `approvals.view` or `approvals.manage`.
     - `cashier`, `warehouse`, `staff`: Have neither reports, approvals, nor billing permissions.

---

### 1.4 Blade View Directives & Guards Inspection

#### A. `resources/views/app/analytics/index.blade.php`
- **Total lines**: 573
- **Current status of permission guards**: **ZERO** `@can` or `Context::hasPermission` guards exist in the entire file.
- **Unguarded Elements**:
  1. *Print Button* (lines 274–278):
     ```blade
     <button type="button" @click="window.print()" class="...">
         <i data-lucide="printer" class="w-3.5 h-3.5 text-[#007AFF]"></i>
         <span>{{ __('analytics.print_report') }}</span>
     </button>
     ```
     -> Lacks `@can('reports.export')` or `@can('analytics.export')`.
  2. *Link to Formal Financial Reports* (lines 281–286):
     ```blade
     <a href="{{ route('reports.index') }}" class="...">
         <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
         <span>{{ __('analytics.formal_reports') }}</span>
     </a>
     ```
     -> Rendered unconditionally; lacks `@can('reports.view')`.
  3. *Stock Valuation Report Link* (lines 452–455):
     ```blade
     <a href="{{ route('reports.index', ['tab' => 'stock']) }}" ...>
     ```
     -> Lacks `@can('reports.view')` or `@can('inventory.view')`.
  4. *Confidential Margin & Profit Information*:
     - Card 3: Laba Kotor & Margin % (lines 360–377)
     - Card 4: Estimasi Laba Bersih & Biaya Operasional (lines 380–396)
     - Top 10 Product Table & Mobile Cards Gross Profit and Margin % columns (lines 467–468, 478–483, 507–508, 521–523)
     -> Rendered to any user who enters the page without checking if they have `reports.financial` or `costing.view_margin`. Roles like general store supervisors or branch viewers could see confidential gross margin percentages.

#### B. `resources/views/app/approvals/inbox.blade.php`
- **Total lines**: 381
- **Unguarded & Inconsistent Elements**:
  1. *Approval Rules Header Link* (lines 31–36):
     ```blade
     @if(\App\Support\Context::isOwner() || \App\Support\Context::isAdminOrOwner())
     <a href="{{ route('approval-rules.index') }}" ...>
     @endif
     ```
     -> Inconsistent with RBAC: The route and controller require `approvals.manage`, but the Blade view hardcodes `Context::isOwner() || Context::isAdminOrOwner()`. It should use `@can('approvals.manage')` or `@can('approvals.rules.manage')`.
  2. *Approve & Reject Quick Action Buttons* (lines 131–146):
     ```blade
     <button type="button" @click="openRejectModal('{{ $ticket->id }}', ...)" ...>
         <span>{{ __('approvals.btn_reject') }}</span>
     </button>
     <button type="button" @click="openApproveModal('{{ $ticket->id }}', ...)" ...>
         <span>{{ __('approvals.btn_approve_level', ['level' => $ticket->current_level]) }}</span>
     </button>
     ```
     -> **CRITICAL RBAC DEFECT**: Neither the Reject nor the Approve button is wrapped in any permission check or `@can` directive! Every user with read access (`approvals.view`) sees clickable Approve/Reject buttons. Clicking them opens the confirmation modal, but submitting fails with 403 Forbidden.
     -> Furthermore, the view does not check if the current logged-in user is actually authorized for that specific ticket level via `$workflowService->canUserApprove($ticket, Context::user())`.

#### C. `resources/views/app/approvals/history.blade.php`
- **Total lines**: 192
- **Guard status**: Purely read-only display. Consistent with `approvals.view`. No action buttons or data mutations present.

#### D. `resources/views/app/approvals/rules.blade.php`
- **Total lines**: 522
- **Unguarded Elements**:
  1. *"Tambah Aturan" Button in Header* (lines 31–37):
     ```blade
     <button type="button" @click="openCreateModal()" ...>
         <i data-lucide="plus" class="w-4 h-4"></i>
         <span>{{ __('approvals.btn_add_rule') }}</span>
     </button>
     ```
     -> Not guarded with `@can('approvals.manage')`.
  2. *"Buat Aturan Pertama" Button in Empty State* (lines 132–139):
     ```blade
     <button type="button" @click="openCreateModal()" ...>
     ```
     -> Not guarded with `@can('approvals.manage')`.
  3. *Edit and Delete Actions on Rule Cards* (lines 101–120):
     Uses `@if(\App\Support\Context::hasPermission('approvals.manage'))`. Functional, but should be standardized to `@can('approvals.manage')`.

#### E. `resources/views/app/billing/limits.blade.php`
- **Total lines**: 2694
- **Guard status**:
  - Top header action (checkout/choose plan) has `@if (\App\Support\Context::hasPermission('billing.manage'))` (line 43).
  - Storage recalculation button has `@if (\App\Support\Context::hasPermission('billing.manage'))` (line 1395).
  - Storage file delete buttons have `@if (\App\Support\Context::hasPermission('billing.manage'))` (lines 1687, 1726).
- **Unguarded Elements**:
  - *Tier Selection / Upgrade CTA Buttons in 4-Tier Plan Grid* (lines 477–482, 547–550, and other tiers):
    ```blade
    <a :href="'{{ route('billing.checkout', ['tier' => 'standard']) }}&cycle=' + pricingCycle" class="...">
        <span>{{ __('billing.btn_choose_standard') }}</span>
    </a>
    ```
    -> Not wrapped with `@can('billing.manage')`! A user with read-only billing access (`billing.view`, such as an external auditor or accountant) sees clickable "Pilih Standard" / "Pilih Premium" buttons that redirect them to `billing.checkout` and trigger an HTTP 403 Forbidden error.

#### F. `resources/views/app/billing/checkout.blade.php`, `history.blade.php`, `payment.blade.php`, `invoice.blade.php`
- `checkout.blade.php`: Entire route is locked to `billing.manage`. Line 1043 additionally guards submit button with `@if (\App\Support\Context::hasPermission('billing.manage'))`.
- `history.blade.php`: Line 45 guards the checkout CTA with `@if (\App\Support\Context::hasPermission('billing.manage'))`. Detail & receipt links are read-only, matching `billing.view`.
- `payment.blade.php`: Read-only dynamic payment page with auto-polling. Re-order link on expired orders (line 841) should be guarded with `@can('billing.manage')`.
- `invoice.blade.php`: Read-only invoice with print and PDF download.

---

## 2. Logic Chain

1. **Premise 1 (Controller Authorization Guardrails)**: Cooca architecture requires controller-level RBAC (`HasMiddleware`) to prevent unauthorized execution even if routes or UI links are leaked.
   - *Observation*: `AnalyticsWebController`, `ApprovalWebController`, `BillingAndLimitWebController`, and `SubscriptionCheckoutWebController` all implement `HasMiddleware` and define method-level middleware.
   - *Observation*: However, `AnalyticsWebController` uses `require.permission:reports.view` because `analytics.view` was never seeded in `RbacSeeder.php`.

2. **Premise 2 (Zero Feature / Action Leakage in UI)**: The UI must not display buttons, actions, or links that lead to 403 Forbidden errors or leak sensitive company metrics.
   - *Observation*: In `inbox.blade.php`, the Approve and Reject buttons are rendered unconditionally without `@can` checks.
   - *Observation*: In `rules.blade.php`, the "Tambah Aturan" buttons in the header and empty state lack `@can('approvals.manage')`.
   - *Observation*: In `limits.blade.php`, the tier upgrade CTAs in the 4-tier pricing grid lack `@can('billing.manage')`.
   - *Observation*: In `analytics/index.blade.php`, sensitive financial data (Net Profit, Gross Profit, Product Margins) is rendered without checking `reports.financial` or `costing.view_margin`.

3. **Premise 3 (Granular Seeder Parity)**: Seeders must serve as the single source of truth for all system roles and permissions.
   - *Observation*: `approvals.manage` conflates ticket approval with approval rules governance.
   - *Observation*: `analytics.view` and `analytics.export` are missing from `RbacSeeder.php`.

4. **Conclusion from Logic Chain**:
   - The controllers have solid multi-tenant and route-level protection, but the seeders lack granular action permissions, and the Blade views have noticeable gaps where action buttons and sensitive data are displayed to users lacking requisite privileges.

---

## 3. Caveats

1. **Backward Compatibility**: If `analytics.view` is enforced strictly in `AnalyticsWebController`, existing users/roles that currently access `/analytics` via `reports.view` would lose access until `RbacSeeder` is re-run. Therefore, the controller middleware must accept both:
   `new Middleware('require.permission:analytics.view,reports.view', only: ['index'])`.
2. **Approval Workflow Multi-Level Logic**: An operator might have `approvals.manage` globally, but might not be the designated approver for Level 2 or Level 3 of a specific document (e.g. only Owner can approve Level 3). Thus, the UI guard in `inbox.blade.php` must combine permission checking (`@can('approvals.manage')`) with workflow eligibility (`$workflowService->canUserApprove($ticket, Context::user())`).
3. **No Database Writes Performed**: In accordance with the Explorer mandate, this investigation is strictly read-only; no code or seeder changes were executed.

---

## 4. Conclusion & Actionable Remediation Plan

### Remediation Step 1: Update Database Seeder (`database/seeders/RbacSeeder.php`)

Add the following granular permissions to `permissionMatrix()`:
```php
// ─── Analytics Suite ───────────────────────────────────────
'analytics.view'               => ['Lihat Suite Analitik',                   'reports',     'Melihat dashboard eksekutif analitik tren omzet, transaksi, dan jam sibuk kasir.'],
'analytics.export'             => ['Cetak & Ekspor Analitik',                'reports',     'Mencetak laporan eksekutif analitik atau mengekspor data tren.'],

// ─── Approval Workflow / MAR Granular ─────────────────────
'approvals.action'             => ['Otorisasi Dokumen (Setujui / Tolak)',    'approvals',   'Menyetujui atau menolak tiket dokumen transaksi pada level otorisasi yang berwenang.'],
'approvals.rules.manage'       => ['Kelola Aturan Plafon Approval',          'approvals',   'Mengatur, mengubah, dan menghapus aturan plafon dan matriks bertingkat persetujuan dokumen.'],
```

Update role definitions in `roleDefinitions()`:
- `owner`: Automatically receives all permissions via `$allSlugs`.
- `admin`: Add `analytics.view`, `analytics.export`, `approvals.action`, `approvals.rules.manage`.
- `manager`: Add `analytics.view`, `analytics.export`, `approvals.action`. (Keep `approvals.rules.manage` restricted to Owner/Admin unless explicitly configured).
- `finance`: Add `analytics.view`, `analytics.export`, `approvals.view`.

---

### Remediation Step 2: Controllers Modernization & Backward-Compatible Middleware

1. **`app/Http/Controllers/Web/AnalyticsWebController.php`**:
   Update `middleware()` to support both `analytics.view` and `reports.view`:
   ```php
   public static function middleware(): array
   {
       return [
           new Middleware('require.permission:analytics.view,reports.view', only: ['index']),
       ];
   }
   ```

2. **`app/Http/Controllers/Web/Approval/ApprovalWebController.php`**:
   Update `middleware()` to enforce granular permissions while supporting legacy `approvals.manage`:
   ```php
   public static function middleware(): array
   {
       return [
           new Middleware('require.permission:approvals.view', only: ['inbox', 'history']),
           new Middleware('require.permission:approvals.action,approvals.manage', only: ['approve', 'reject']),
           new Middleware('require.permission:approvals.rules.manage,approvals.manage', only: [
               'rulesIndex',
               'rulesStore',
               'rulesUpdate',
               'rulesDestroy',
           ]),
       ];
   }
   ```

---

### Remediation Step 3: Blade Views Hardening & Directive Standardization

1. **`resources/views/app/analytics/index.blade.php`**:
   - Wrap Print button (line 274) with:
     ```blade
     @canany(['analytics.export', 'reports.export'])
         <button type="button" @click="window.print()" class="...">
             <i data-lucide="printer" class="w-3.5 h-3.5 text-[#007AFF]"></i>
             <span>{{ __('analytics.print_report') }}</span>
         </button>
     @endcanany
     ```
   - Wrap "Laporan Formal" link (line 281) with:
     ```blade
     @can('reports.view')
         <a href="{{ route('reports.index') }}" class="...">
             <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
             <span>{{ __('analytics.formal_reports') }}</span>
         </a>
     @endcan
     ```
   - Protect Gross Profit & Net Profit cards (lines 360–396) and top products margin column:
     Check `@canany(['costing.view_margin', 'reports.financial'])` before rendering margin percentage and gross profit. If false, display sanitized `***` or mask with a locked tooltip.

2. **`resources/views/app/approvals/inbox.blade.php`**:
   - Standardize Rules link (line 31) to `@canany(['approvals.rules.manage', 'approvals.manage'])`.
   - Wrap the Approve and Reject buttons (lines 131–146) with:
     ```blade
     @canany(['approvals.action', 'approvals.manage'])
         @if(app(\App\Domain\Approval\ApprovalWorkflowService::class)->canUserApprove($ticket, \App\Support\Context::user()))
             <button type="button" @click="openRejectModal(...)" class="...">
                 <i data-lucide="x-circle" class="w-4 h-4"></i>
                 <span>{{ __('approvals.btn_reject') }}</span>
             </button>
             <button type="button" @click="openApproveModal(...)" class="...">
                 <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                 <span>{{ __('approvals.btn_approve_level', ['level' => $ticket->current_level]) }}</span>
             </button>
         @else
             <span class="text-xs text-black/40 dark:text-white/40 italic">
                 {{ __('approvals.waiting_other_approver') }}
             </span>
         @endif
     @endcanany
     ```

3. **`resources/views/app/approvals/rules.blade.php`**:
   - Wrap "Tambah Aturan" header button (line 31) with `@canany(['approvals.rules.manage', 'approvals.manage'])`.
   - Wrap empty state "Buat Aturan Pertama" button (line 132) with `@canany(['approvals.rules.manage', 'approvals.manage'])`.
   - Standardize line 101 to `@canany(['approvals.rules.manage', 'approvals.manage'])`.

4. **`resources/views/app/billing/limits.blade.php`**:
   - In the 4-tier plan cards (lines 477, 547, etc.), wrap the "Pilih Standard" / "Pilih Premium" upgrade buttons with:
     ```blade
     @can('billing.manage')
         <a :href="'{{ route('billing.checkout', ['tier' => 'standard']) }}&cycle=' + pricingCycle" class="...">
             <span>{{ __('billing.btn_choose_standard') }}</span>
             <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
         </a>
     @else
         <button type="button" disabled title="{{ __('billing.only_owner_upgrade') }}" class="w-full min-h-[44px] h-11 py-2.5 rounded-[12px] text-xs font-semibold text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] cursor-not-allowed flex items-center justify-center gap-1.5">
             <i data-lucide="lock" class="w-3.5 h-3.5"></i>
             <span>{{ __('billing.upgrade_locked') }}</span>
         </button>
     @endcan
     ```

5. **`resources/views/layouts/partials/sidebar.blade.php`**:
   - Line 1732: Guard Analytics navigation item with `@canany(['analytics.view', 'reports.view'])` so that users with only POS report rights (cashiers) do not see the inaccessible executive analytics link.

---

## 5. Verification Method

To independently verify these findings:

1. **Verify Existing Tests**:
   ```powershell
   php artisan test tests/Feature/AnalyticsWebTest.php tests/Feature/ApprovalModuleAuditTest.php tests/Feature/BillingModuleAuditTest.php
   ```
   *Expected Result*: All 24 test cases pass without regressions.

2. **Inspect Gate Integration**:
   Inspect `app/Providers/AppServiceProvider.php` lines 139–153 to confirm `Gate::before` delegates User checks to `Context::hasPermission()`.

3. **Inspect Views for Missing Directives**:
   Search for `@can` in `resources/views/app/analytics/index.blade.php`:
   ```powershell
   Select-String -Path "resources\views\app\analytics\index.blade.php" -Pattern "@can"
   ```
   *Confirmed Result*: 0 matches.

4. **Invalidation Conditions**:
   - If `AnalyticsWebController` is refactored to require a new permission without updating `RbacSeeder`, existing tests referencing `reports.view` will fail with 302 redirect to portal.
   - If Blade view buttons are hidden without providing fallback styling or checking `$workflowService->canUserApprove`, authorized approvers may fail to see actionable buttons.
