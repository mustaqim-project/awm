# Technical Investigation Report: HRM, Import, and Inventory Modules Audit & Documentation Survey

**Investigator**: Teamwork Explorer (`teamwork_explorer`)  
**Workspace**: `c:\laragon\www\cooca_core`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_audit_docs\`  
**Date & Timestamp**: 2026-11-08T19:10:00Z  

---

## 1. Observation

### 1.1 Business Owner Documentation Status (`resources/views/app/docs/`)
- **Directory Structure Observed**:
  - `resources/views/app/docs/index.blade.php` (963 lines)
  - `resources/views/app/docs/partials/`:
    - `sec_ai.blade.php`
    - `sec_ai_studio.blade.php`
    - `sec_analytics.blade.php`
    - `sec_approvals.blade.php`
    - `sec_billing.blade.php`
    - `sec_community.blade.php`
    - `sec_crm.blade.php`
    - `sec_customers.blade.php`
    - `sec_feedback.blade.php`
    - `sec_finance.blade.php`
    - `sec_storefront.blade.php`
- **Documentation Gaps**:
  1. **Missing Partials**:
     - `sec_hrm.blade.php` does **not** exist in `partials/`. In `index.blade.php` lines 705–734, HRM exists only as a superficial 30-line inline summary (`#sec-hrm`) with 4 generic bullet points. It lacks the 20-industry matrix, shift setup guidelines, biometric face registration, and payroll generation procedures.
     - `sec_inventory.blade.php` does **not** exist in `partials/`. In `index.blade.php` lines 542–605, Inventory exists only as a 63-line inline section (`#sec-inventory`) showing a single coffee recipe example. It lacks multi-warehouse workflows, stock opname step-by-step, stock transfer guides, maker-checker shrinkage explanation, and the 20-industry matrix.
     - `sec_import.blade.php` does **not** exist in `partials/` or anywhere in `index.blade.php`. Import is only mentioned in a single callout tip in onboarding (line 466). There is no sidebar navigation item, no anchor `#sec-import`, and no documentation for spreadsheet schemas or duplicate resolution strategies.
  2. **Established Documentation Conventions in Cooca**:
     - **Anchor & Container Pattern**:
       `<section id="sec-<module>" class="scroll-mt-24 space-y-6" x-show="(activeTab === 'all' || activeTab === '<tab_name>') && matchesSearch('...')">`
     - **Header Section**: Gradient icon squircle, module title, sub-badges (`POV Business Owner`, `Standar Enterprise`, `Anti-Fraud Safe`, `20 Sektor`), and concise subtitle.
     - **Direct Action Links**: Quick action buttons wrapped in permission/role gates using `@if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission(...))`.
     - **Executive Summary (POV Business Owner)**: Conversational, jargon-free Indonesian addressing real operational challenges (eliminating leaks, peace of mind).
     - **4 Core Pillars Bento Grid**: Visual breakdown of 4 primary system advantages.
     - **Alur Kerja Langkah Demi Langkah**: Concrete chronological workflows (Step 1 to Step 4/5) with callouts for edge cases.
     - **20-Industry Applicability Matrix**: Matriks Implementasi Praktis untuk 20 Jenis Usaha UMKM categorized into 6 clusters:
       1. *Kuliner & F&B* (5): Kafe & Resto Dine-in, Food Truck & Street Food, Bakery & Roti, Katering & Bento, Depot Air & Minuman.
       2. *Manufaktur & Kerajinan* (5): Konveksi & Garment, Percetakan & Offset, Mebel & Furniture, Bengkel Bubut & Logam, Kerajinan Tangan / Craft.
       3. *Ritel & Apotek* (2): Minimarket & Kelontong, Apotek & Toko Obat.
       4. *Jasa Harian* (4): Bengkel Mobil & Motor, Barbershop & Salon, Laundry Kiloan, Cuci Mobil & Detailing.
       5. *Jasa Proyek* (3): Kontraktor & Renovasi, Wedding Organizer, Digital IT & Creative Agency.
       6. *Distribusi & Agro* (1): Distributor Grosir B2B & Agrobisnis.
     - **RBAC, Segregation of Duties & Anti-Fraud Security**: Explain maker-checker policies, audit trails, and data safety.
     - **Business Owner FAQ**: Interactive Alpine accordion (`x-data="{ expandedFaq: null }"`) answering top 4–5 common questions.
     - **Sidebar Navigation Entry**: Synchronized inside `resources/views/app/docs/index.blade.php` sidebar navigation under the matching cluster.

---

### 1.2 Module Audit & Production Readiness

#### A. HRM Module (`app/Http/Controllers/Web/Hrm/HrmWebController.php`, 1,868 lines)
- **RBAC Implementation**:
  - Implements `Illuminate\Routing\Controllers\HasMiddleware`.
  - `require.permission:users.view` is enforced on: `index`, `showPayroll`, `showPayslip`, `exportPayrollExcel`, `exportPayrollCsv`, `exportPayrollBankCsv`.
  - `require.permission:users.manage` is enforced on: `storeEmployee`, `updateEmployee`, `destroyEmployee`, `storeLoan`, `cancelLoan`, `createPayroll`, `storePayroll`, `approvePayroll`, `payPayroll`, `destroyPayroll`, `approveCorrection`, `rejectCorrection`, `registerEmployeeFace`, `resetEmployeeFace`, `storeLocation`, `updateLocation`, `destroyLocation`, `storeShift`, `updateShift`, `destroyShift`, `storeSchedule`, `updateSchedule`, `destroySchedule`, `exportEmployees`, `employeeTemplate`, `previewEmployeeImport`, `executeEmployeeImport`, `exportAttendance`, `attendanceTemplate`, `previewAttendanceImport`, `executeAttendanceImport`.
  - Direct employee actions (`clockIn`, `clockOut`, `storeCorrection`):
    - Handled via internal guard: `abort_unless($user && ($user->isBusinessOwner() || Context::hasPermission('users.view') || Context::hasPermission('users.manage') || $business->users()->where('users.id', $user->id)->exists()), 403, 'Akses ditolak.');`
  - Attachment streaming (`streamCorrectionAttachment`):
    - Multi-tenant isolation verified (`$correction->business_id === $business->id`), user access check (`$correction->user_id === $user->id || Context::hasPermission(...)`), directory traversal prevention (`abort_if(str_contains($path, '..'), 403)`), and storage disk existence check.
  - Public payslip token view (`publicPayslip(string $token)`):
    - Token lookup with `firstOrFail()` without requiring login session, allowing staff to view their pay statement securely.
- **Validation & FormRequests**:
  - No dedicated FormRequest classes in `app/Http/Requests/Hrm/`. Validation is written inline (`$request->validate([...])`).
- **Database Transaction Safety**:
  - Domain services `AttendanceService` (lines 264, 446, 764) and `PayrollRunService` (lines 49, 342) use `DB::transaction(...)` properly.
  - In `HrmWebController::storeEmployee` (lines 345–365): `User::create` and `BusinessMembership::create` are performed in sequence without an enclosing `DB::transaction(...)`. If membership creation fails, an orphaned `User` record remains in the database.
- **Views**:
  - `resources/views/app/hrm/index.blade.php` contains 8 segmented tabs (`employees`, `attendance`, `corrections`, `locations`, `shifts`, `schedules`, `payrolls`, `loans`). Modals and tabs are segmented into `modals/` and `partials/`.

#### B. Import Module (`app/Http/Controllers/Web/ImportWebController.php`, 403 lines)
- **RBAC & Authorization Audit**:
  - **CRITICAL DEFECT**: `ImportWebController` does **not** implement `HasMiddleware` and declares **zero** controller-level RBAC rules!
  - In `routes/owner.php` (line 210), the import routes are grouped under:
    `Route::middleware('require.permission:materials.view,products.view')->group(...)`
  - **Security Impact**:
    - Users with read-only view permission (`materials.view` or `products.view`) are granted access to execute destructive import mutations (`executeMaterials`, `executeProducts`, `executeRecipes`, `executeInventory`).
    - `executeProducts` modifies catalog products and should strictly require `products.create` or `products.manage`.
    - `executeMaterials` modifies raw materials and should strictly require `materials.create` or `materials.manage`.
    - `executeRecipes` modifies BOM formulas and should strictly require `products.manage` or `materials.manage`.
    - `executeInventory` modifies live inventory stock balances and should strictly require `inventory.manage`.
    - `downloadInventoryTemplate` and `previewInventory` should require `inventory.view`.
- **File Upload Handling & Memory Limits**:
  - File validation rule: `['file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt']]`.
  - In `DataImportService::readSpreadsheetRows` (lines 2006–2012):
    `$spreadsheet = IOFactory::load($realPath);`
    `$worksheet = $spreadsheet->getActiveSheet();`
    `$sheetData = $worksheet->toArray(null, true, true, true);`
  - PhpSpreadsheet loads the full DOM into memory, consuming 150MB–300MB of RAM for large spreadsheets. A 5MB XLSX can easily exceed PHP's default memory limit or execution time limit.
  - Client-side JSON payload passing:
    In `executeProducts`, `executeMaterials`, `executeRecipes`, and `executeInventory`, parsed preview rows are sent to the client and then submitted back as a JSON string (`'rows' => ['required']`).
    If an attacker modifies the JSON payload between preview and execute, unverified values could be submitted.
- **Database Transaction Safety**:
  - `executeMaterialImport` (line 861), `executeProductImport` (line 1194), `executeRecipeImport` (line 1517), and `executeInventoryImport` (line 1930) in `DataImportService.php` are properly wrapped in `DB::transaction(...)`.
- **Inconsistency in Error Handling**:
  - In `executeMaterials` (line 270), the controller cancels the import if any row has `'status' => 'error'`. In `executeProducts`, `executeRecipes`, and `executeInventory`, rows with errors are simply skipped silently without blocking the import.

#### C. Inventory Module (`app/Http/Controllers/Web/Inventory/InventoryWebController.php`, 839 lines)
- **RBAC Implementation**:
  - Implements `HasMiddleware`.
  - `require.permission:inventory.view` on: `stocks`, `movements`, `opnames`, `transfers`, `exportBlankOpname`.
  - `require.permission:inventory.manage` on: `quickAdjust`, `approveAdjustment`, `rejectAdjustment`, `storeOpname`, `previewOpnameImport`, `executeOpnameImport`, `reconcileOpname`, `storeTransfer`, `receiveTransfer`.
  - All public controller methods are protected.
- **Maker-Checker & Supervisor PIN Gates**:
  - In `quickAdjust` (lines 153–167): Supervisor PIN is enforced for shrinkage losses (diffValue > Rp 100.000 or diffQty > 10).
  - High-value stock loss (nominal >= threshold or percentage reduction > 20% with diff >= 5 or diff > 50) initiated by non-owners is routed to `status = 'pending_approval'`.
- **Database Transaction Safety**:
  - `StockService` properly uses `DB::transaction(...)` with pessimistic locking (`lockForUpdate()`) for `recordMovement`, `reconcileStockOpname`, `dispatchStockTransfer`, and `completeStockTransfer`.
  - **Transaction Gap in Controller**:
    - `quickAdjust` (lines 251–296): `StockAdjustment::create`, `StockAdjustmentItem::create`, `$this->stockService->recordMovement`, and `$this->journalService->recordStockAdjustmentJournal` run without an outer `DB::transaction(...)`. A journal failure leaves an orphaned completed adjustment.
    - `storeTransfer` (lines 702–726): `StockTransfer::create` and the loop of `StockTransferItem::create` run without an outer `DB::transaction(...)`. A mid-loop failure leaves a corrupted transfer header with missing items.
- **UI Architecture**:
  - Unified `<x-module-tabs module="inventory" />` is used across all 4 inventory views (`stocks.blade.php`, `movements.blade.php`, `opname.blade.php`, `transfers.blade.php`).

---

### 1.3 Test Suite Status (`tests/Feature/` and `tests/Unit/`)

#### 1. Test Execution Results:
| Test Suite | Tests | Result | Assertions | Notes |
|---|---|---|---|---|
| `tests/Feature/HrmModuleAuditTest.php` | 6 | **PASS** | 22 | Multi-tenant isolation, RBAC redirects, audit log generation verified. |
| `tests/Feature/InventoryModuleAuditTest.php` | 16 | **PASS** | 44 | Stock movements, adjust permissions, maker-checker approvals verified. |
| `tests/Feature/Inventory/StockTransferDispatchAndReceiveTest.php` | 4 | **PASS** | 30 | Warehouse-to-outlet stock transfers, lock integrity verified. |
| `tests/Feature/InventoryMakerCheckerSecurityTest.php` | 7 | **PASS** | 45 | Shrinkage PIN, supervisor approvals, audit logs verified. |
| `tests/Feature/ProductAndRecipeImportTest.php` | 10 | **3 FAIL / 7 PASS** | 43 | Failures caused by modernized sheet names in `DataExchangeService` (see below). |
| `tests/Feature/HrmAndMonthlyPayrollTest.php` | 10 | **PASS** | 50+ | Payroll generation, allowances, tax PTKP, and THP verified. |
| `tests/Feature/HrmAttendanceBiometricAndExceptionTest.php` | 8 | **5 FAIL / 3 PASS** | 40+ | Failures caused by missing registered face template in test fixtures before clock-in when biometric enforcement is enabled. |

#### 2. Root Cause of Failures in Test Suites:
- **`ProductAndRecipeImportTest.php`**:
  `DataExchangeService.php` was updated to standard uppercase Indonesian worksheet titles:
  - Material template: `['INPUT_BAHAN_BAKU', 'DATA_REFERENSI', 'PANDUAN_IMPORT']`
  - Product template: `['INPUT_PRODUK', 'DATA_REFERENSI', 'PANDUAN_IMPORT']`
  - Inventory template: `['INPUT_SALDO_STOK', 'DATA_REFERENSI', 'PANDUAN_IMPORT']`
  `ProductAndRecipeImportTest.php` lines 113, 218, 324 asserted the obsolete sheet titles (`'Template Bahan Baku'`, `'Template Stok Awal'`).
- **`HrmAttendanceBiometricAndExceptionTest.php`**:
  When business policy enforces facial biometric attendance, clock-in calls throw:
  `"Template biometrik wajah karyawan belum terdaftar. Anda wajib mendaftarkan wajah terlebih dahulu sebelum dapat melakukan presensi."`
  The test cases (lines 225, 255, 308, 334) skipped calling `registerEmployeeFace` before testing WFA/WFH and shift calculations. Additionally, line 150 failed on temporary photo cleanup assertion on Windows path format.

---

## 2. Logic Chain

1. **Authorization Reasoning**:
   - Web application security requires Defense-in-Depth. While `routes/owner.php` provides edge routing middleware, controller-level middleware (`HasMiddleware`) guarantees that actions cannot be invoked with mismatched permissions regardless of how routes are grouped or aliased.
   - In `ImportWebController`, relying solely on the outer route group `materials.view,products.view` violates the Principle of Least Privilege: users with read-only access can post to `/import/inventory/execute` and overwrite active warehouse stock balances.
   - Enforcing `require.permission:materials.manage` (or `materials.create`), `products.manage` (or `products.create`), and `inventory.manage` via `HasMiddleware` inside `ImportWebController` directly resolves this vulnerability.

2. **Database Integrity Reasoning**:
   - Stock adjustments, stock transfers, and employee creation represent atomic business transactions.
   - In `InventoryWebController::quickAdjust`, creating the adjustment record, writing movement records, and writing double-entry accounting journal entries must either all succeed or all roll back.
   - Wrapping `quickAdjust`, `storeTransfer`, and `HrmWebController::storeEmployee` inside `DB::transaction(...)` eliminates partial database writes during hardware/network/database interruptions.

3. **Documentation Structure Reasoning**:
   - All high-grade Cooca modules (`finance`, `crm`, `customers`, `analytics`, `approvals`, `feedback`) follow the standard documented in Section 1.1 with standalone partial views in `resources/views/app/docs/partials/sec_<module>.blade.php` and inclusion in `index.blade.php`.
   - HRM, Import, and Inventory currently lack standalone partials. Replacing the brief inline text in `index.blade.php` with modular includes for `sec_hrm.blade.php`, `sec_inventory.blade.php`, and adding `sec_import.blade.php` will establish 100% uniformity across the Cooca documentation system.

---

## 3. Recommendations for Implementation Phase

### R1. UI Simplification & Decluttering
- **HRM (`resources/views/app/hrm/`)**:
  - Standardize segmented navigation buttons to prevent wrapping on tablet/mobile screens (horizontal scroll with hidden scrollbars).
  - Clean up touch target heights to ensure 44px minimum thumb zone on all modal actions and tab selectors.
- **Import (`resources/views/app/import/index.blade.php`)**:
  - The single 2,185-line view should maintain clean separation between the 4 primary tabs (Materials, Products, Recipes, Inventory) and the 12-module directory bento grid.
  - Streamline action buttons on mobile so preview and execute actions do not collide or overflow card boundaries.
- **Inventory (`resources/views/app/inventory/`)**:
  - Retain `<x-module-tabs module="inventory" />`.
  - Refine mobile responsiveness of the Quick Adjust modal so unit cost and quantity inputs do not push action buttons below the fold on small screens.

### R2. Controller-Level RBAC Enforcement
- **`ImportWebController`**:
  - Implement `Illuminate\Routing\Controllers\HasMiddleware`.
  - Declare:
    ```php
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:materials.view,products.view,inventory.view', only: ['index']),
            new Middleware('require.permission:materials.view', only: ['downloadMaterialTemplate', 'previewMaterials']),
            new Middleware('require.permission:materials.manage', only: ['executeMaterials']),
            new Middleware('require.permission:products.view', only: ['downloadProductTemplate', 'downloadRecipeTemplate', 'previewProducts', 'previewRecipes']),
            new Middleware('require.permission:products.manage', only: ['executeProducts', 'executeRecipes']),
            new Middleware('require.permission:inventory.view', only: ['downloadInventoryTemplate', 'previewInventory']),
            new Middleware('require.permission:inventory.manage', only: ['executeInventory']),
        ];
    }
    ```
  - In `routes/owner.php`, update route middleware definitions to align with controller-level rules.

### R3. Module Audit & Transaction Safety
- **Wrap Multi-Step Mutations in `DB::transaction(...)`**:
  - `InventoryWebController::quickAdjust`: Wrap the entire block creating `StockAdjustment`, `StockAdjustmentItem`, `recordMovement`, and `recordStockAdjustmentJournal`.
  - `InventoryWebController::storeTransfer`: Wrap `StockTransfer::create` and `StockTransferItem::create` loop.
  - `HrmWebController::storeEmployee`: Wrap `User::create` and `BusinessMembership::create`.
- **Import Memory & Validation Hardening**:
  - Enforce a maximum row limit in preview (e.g. 5,000 rows) to prevent PHP memory exhaustion.
  - Re-validate row status during `executeProducts`, `executeRecipes`, and `executeInventory` to ensure rows marked with `error` are not inadvertently executed.
- **Synchronize Test Assertions**:
  - Update `tests/Feature/ProductAndRecipeImportTest.php` lines 113, 145, 167, 189, 218, 324 to use `INPUT_BAHAN_BAKU`, `INPUT_PRODUK`, `INPUT_RESEP_BOM`, `INPUT_SALDO_STOK`, and `DATA_REFERENSI`.

### R4. Business Owner Documentation Creation
Create the 3 missing documentation partial views in `resources/views/app/docs/partials/`:
1. `sec_hrm.blade.php`:
   - Comprehensive guide on employee management, attendance geofencing, facial biometric registration, shift scheduling, loans/kasbon, and automatic payroll runs.
   - 20-industry applicability matrix covering shift & attendance needs for all 6 clusters.
   - Interactive route buttons to `hrm.index` tabs.
2. `sec_import.blade.php`:
   - Unified guide for mass Excel/CSV import covering Materials, Products, BOM Recipes, and Initial Inventory Stock.
   - Explanation of Living Lookups, dropdown validations, and duplicate strategies (`skip` vs `update`).
   - 20-industry applicability matrix explaining how each industry migrates existing spreadsheets into Cooca.
   - Direct download links for all templates.
3. `sec_inventory.blade.php`:
   - Deep-dive guide into multi-warehouse stock management, real-time HPP calculations, stock opname physical counts, inter-warehouse transfers, and shrinkage maker-checker controls.
   - 20-industry applicability matrix covering inventory dynamics for all 6 clusters.
   - Interactive route buttons to `inventory.stocks`, `movements`, `opnames`, `transfers`.
4. Update `resources/views/app/docs/index.blade.php`:
   - Replace lines 542–605 with `@include('app.docs.partials.sec_inventory')`.
   - Replace lines 705–734 with `@include('app.docs.partials.sec_hrm')`.
   - Add `@include('app.docs.partials.sec_import')`.
   - Add `#sec-import` navigation link in the left sidebar under Core Business / Master Data.

---

## 4. Caveats
- No project source files, tests, or documentation views were modified during this investigation (strict read-only execution).
- The memory consumption of PhpSpreadsheet on shared hosting depends on `memory_limit` (typically 128MB to 512MB in production).
- Test execution times may vary depending on local SQLite/MySQL database disk I/O.

---

## 5. Conclusion
1. **HRM Module**:
   Architecture is robust with full controller-level RBAC (`HasMiddleware`). Needs transaction wrapping on employee creation and a comprehensive Business Owner documentation section (`sec_hrm.blade.php`).
2. **Import Module**:
   Contains a critical RBAC gap: lacks `HasMiddleware` and allows users with view-only permissions to run bulk imports. Needs controller-level RBAC, sheet name test synchronization in `ProductAndRecipeImportTest.php`, and a dedicated documentation partial (`sec_import.blade.php`).
3. **Inventory Module**:
   Strong domain architecture and maker-checker security. Needs transaction safety in `quickAdjust` and `storeTransfer`, plus a comprehensive documentation section (`sec_inventory.blade.php`).
4. **Documentation**:
   The Cooca documentation system has established high-standard patterns (POV Business Owner, 20-industry matrix, interactive routes, FAQ accordions). Implementing `sec_hrm.blade.php`, `sec_import.blade.php`, and `sec_inventory.blade.php` will achieve complete documentation coverage across the platform.

---

## 6. Verification Method

To independently reproduce and verify all findings:
1. **Inspect Controller Middleware**:
   - `view_file` on `app/Http/Controllers/Web/ImportWebController.php` (observe lack of `HasMiddleware`).
   - `view_file` on `app/Http/Controllers/Web/Hrm/HrmWebController.php:42-89`.
   - `view_file` on `app/Http/Controllers/Web/Inventory/InventoryWebController.php:39-63`.
2. **Inspect Route Permissions**:
   - Search `routes/owner.php` lines 209–235 (observe `materials.view,products.view` on import execute routes).
3. **Inspect Documentation Files**:
   - `dir resources/views/app/docs/partials/` (confirm absence of `sec_hrm`, `sec_import`, `sec_inventory`).
   - `view_file` on `resources/views/app/docs/index.blade.php:542-605` and `705-734`.
4. **Execute Test Suite**:
   ```powershell
   php artisan test tests/Feature/HrmModuleAuditTest.php
   php artisan test tests/Feature/InventoryModuleAuditTest.php
   php artisan test tests/Feature/Inventory/StockTransferDispatchAndReceiveTest.php
   php artisan test tests/Feature/InventoryMakerCheckerSecurityTest.php
   php artisan test tests/Feature/ProductAndRecipeImportTest.php
   ```
