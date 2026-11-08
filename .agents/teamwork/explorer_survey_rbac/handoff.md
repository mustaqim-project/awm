# RBAC & Controller Authorization Survey Report: HRM, Import, and Inventory Modules

**Investigator**: teamwork_explorer (Read-Only Subagent)  
**Date**: 2026-11-08  
**Scope**: HRM, Import, and Inventory modules (`app/Http/Controllers`, `routes/owner.php`, `routes/api.php`, `database/seeders/RbacSeeder.php`, `resources/views/app/hrm`, `resources/views/app/import`, `resources/views/app/inventory`)

---

## 1. Observation

### 1.1 Controller-Level RBAC & Route Inventory

#### A. Web Controllers
1. **`App\Http\Controllers\Web\Hrm\HrmWebController`** (`app/Http/Controllers/Web/Hrm/HrmWebController.php:42`):
   - Implements `Illuminate\Routing\Controllers\HasMiddleware`.
   - `public static function middleware(): array` (lines 44–89) defines:
     - `require.permission:users.view` on `['index', 'showPayroll', 'showPayslip', 'exportPayrollExcel', 'exportPayrollCsv', 'exportPayrollBankCsv']`.
     - `require.permission:users.manage` on 28 management actions (`storeEmployee`, `updateEmployee`, `destroyEmployee`, `storeLoan`, `cancelLoan`, `createPayroll`, `storePayroll`, `approvePayroll`, `payPayroll`, `destroyPayroll`, `approveCorrection`, `rejectCorrection`, `registerEmployeeFace`, `resetEmployeeFace`, `storeLocation`, `updateLocation`, `destroyLocation`, `storeShift`, `updateShift`, `destroyShift`, `storeSchedule`, `updateSchedule`, `destroySchedule`, `exportEmployees`, `employeeTemplate`, `previewEmployeeImport`, `executeEmployeeImport`, `exportAttendance`, `attendanceTemplate`, `previewAttendanceImport`, `executeAttendanceImport`).
   - Unregistered in `middleware()`:
     - `clockIn`, `clockOut`, `storeCorrection`: Use in-method context check (`$business->users()->where('users.id', $user->id)->exists()`).
     - `streamCorrectionAttachment`: In-method check (`$correction->user_id === $user->id || Context::hasPermission('users.view') || Context::hasPermission('users.manage')`).
     - `publicPayslip`: Public tokenized endpoint via `routes/public.php:287`.
   - **Critical Anomaly**: All HRM actions borrow permissions from `users.*` (`users.view`, `users.manage`) instead of dedicated HRM domain permissions (`hrm.*`, `hrm.payroll.*`). A user granted `users.view` (e.g., to see staff lists) automatically gains access to see all company payroll data, salaries, and bank accounts.
   - **Route Duplication**: `routes/owner.php` lines 465–528 redundantly attach `->middleware('require.permission:users.manage')` to individual routes inside `Route::prefix('hrm')->name('hrm.')->group(...)`, while omitting it on `hrm.index`, `payrolls.show`, `payrolls.export*`, `payslips.show`, relying unevenly on controller middleware.

2. **`App\Http\Controllers\Web\ImportWebController`** (`app/Http/Controllers/Web/ImportWebController.php:17`):
   - **Does NOT implement `HasMiddleware`**.
   - Contains NO controller-level authorization middleware declarations whatsoever.
   - Contains NO in-method permission checks (only checks billing entitlement `canImportData($business)`).
   - Relies completely on route group in `routes/owner.php:210`:
     ```php
     Route::middleware('require.permission:materials.view,products.view')->group(function (): void { ... });
     ```
   - **Severe Security Vulnerability**: Route-level middleware accepts `materials.view` OR `products.view`. Any user with read-only view permission for either materials or products can invoke `executeMaterials`, `executeProducts`, `executeRecipes`, and `executeInventory`, thereby creating, overwriting, and modifying critical products, BOM recipes, costs, and inventory balances without `materials.create`, `products.create`, `costing.manage`, or `inventory.manage`.

3. **`App\Http\Controllers\Web\Inventory\InventoryWebController`** (`app/Http/Controllers/Web/Inventory/InventoryWebController.php:39`):
   - Implements `Illuminate\Routing\Controllers\HasMiddleware`.
   - `public static function middleware(): array` (lines 41–63) defines:
     - `require.permission:inventory.view` on `['stocks', 'movements', 'opnames', 'transfers', 'exportBlankOpname']`.
     - `require.permission:inventory.manage` on `['quickAdjust', 'approveAdjustment', 'rejectAdjustment', 'storeOpname', 'previewOpnameImport', 'executeOpnameImport', 'reconcileOpname', 'storeTransfer', 'receiveTransfer']`.
   - Fully covers 100% of all public operational methods.
   - `routes/owner.php` lines 810–826 contain redundant route-level middleware declarations (`require.permission:inventory.view`, `require.permission:inventory.manage`).

4. **`App\Http\Controllers\Web\Warehouse\WarehouseWebController`** (`app/Http/Controllers/Web/Warehouse/WarehouseWebController.php:30`):
   - Implements `HasMiddleware`.
   - `require.permission:warehouse.view` on `['index', 'show']`.
   - `require.permission:warehouse.manage` on `['store', 'edit', 'update', 'destroy']`.

---

#### B. API Controllers
1. **`App\Http\Controllers\Api\V1\Hrm\HrmApiController`** (`app/Http/Controllers/Api\V1\Hrm\HrmApiController.php:28`):
   - **Does NOT implement `HasMiddleware`**.
   - Contains NO middleware method and NO in-method permission checks.
   - `routes/api.php` lines 1539–1547:
     ```php
     Route::prefix('hrm')->group(function (): void {
         Route::get('/hub', [HrmApiController::class, 'hub']);
         Route::post('/employees', [HrmApiController::class, 'storeEmployee']);
         Route::put('/employees/{membership}', [HrmApiController::class, 'updateEmployee']);
         Route::post('/shifts', [HrmApiController::class, 'storeShift']);
         Route::post('/loans', [HrmApiController::class, 'storeLoan']);
         Route::post('/corrections/{correction}/approve', [HrmApiController::class, 'approveCorrection']);
         Route::post('/corrections/{correction}/reject', [HrmApiController::class, 'rejectCorrection']);
     });
     ```
   - **Severe Vulnerability**: The entire API route group has NO permission middleware. Any authenticated workspace user can call `/api/v1/hrm/hub` (which exposes every employee's base salary, allowances, bank account number, NIK, NPWP, active loans, and payroll history), create employees (`storeEmployee`), mutate salaries and roles (`updateEmployee`), create loans (`storeLoan`), and approve attendance corrections (`approveCorrection`).

2. **`App\Http\Controllers\Api\V1\Hrm\AttendanceApiController`** (`app/Http/Controllers/Api\V1\Hrm\AttendanceApiController.php:29`):
   - **Does NOT implement `HasMiddleware`**.
   - Routes in `routes/api.php` lines 1506–1534 have no route-level permission middleware.
   - Some methods use manual in-method checks (`approveCorrection`, `rejectCorrection`, `resetFace`, `registerFace`).
   - In lines 968 and 1011, it explicitly checks `Context::hasPermission('hrm.manage')`, which is a **phantom permission** not registered in `RbacSeeder.php`.

3. **`App\Http\Controllers\Api\V1\Inventory\InventoryController`** and siblings (`StockOpnameController`, `StockTransferController`, `InventoryGoodsReceiptController`, `InventoryPurchaseOrderController`):
   - Do NOT implement `HasMiddleware`.
   - Rely solely on route-level middleware in `routes/api.php` lines 1344–1368 (`require.permission:inventory.view`, `require.permission:inventory.manage`).

---

### 1.2 Seeders & Permission Registry Observation

Inspecting `database/seeders/RbacSeeder.php`:
1. **Existing Inventory Permissions** (`permissionMatrix()`, lines 91–96):
   - `'inventory.view'` => `['Lihat Stok', 'inventory', 'Melihat stok real-time dan riwayat mutasi.']`
   - `'inventory.manage'` => `['Mutasi & Opname Stok', 'inventory', 'Kelola transfer gudang, penyesuaian stok, dan rekonsiliasi opname.']`
   - `'warehouse.view'` => `['Lihat Gudang & Lokasi', 'inventory', 'Melihat daftar master multi-gudang dan lokasi penyimpanan barang.']`
   - `'warehouse.manage'` => `['Kelola Gudang & Lokasi', 'inventory', 'Menambah, mengubah, dan mengatur master lokasi gudang penyimpanan.']`
2. **HRM Module Permissions**: **ZERO** permissions defined.
   - The string `'hrm.'` does not appear anywhere in `RbacSeeder.php`.
   - Existing code either abuses `users.view` / `users.manage` or phantom checks `hrm.manage`.
3. **Import Module Permissions**: **ZERO** permissions defined.
   - The string `'import.'` does not appear anywhere in `RbacSeeder.php`.
4. **Role Assignments** (`roleDefinitions()`, lines 212–453):
   - `owner`: `$allSlugs` (all permissions).
   - `manager`: Has `inventory.view`, `inventory.manage`, `warehouse.view`, `warehouse.manage`, `users.view`. Missing dedicated HRM and Import permissions.
   - `admin`: `$managerSlugs` + `['users.manage', 'roles.view', 'ai.manage', ...]`.
   - `warehouse`: Has `inventory.view`, `inventory.manage`, `warehouse.view`, `warehouse.manage`, `purchasing.view`.
   - `finance`: Has `costing.view_margin`, `invoices.*`, `accounting.*`, `expenses.*`. Has NO access to `hrm.payroll.*` or `import.view`.
   - `staff`, `cashier`, `viewer`: Have `inventory.view`, but no write access.

---

### 1.3 View-Level Authorization Guards Observation

32 Blade templates surveyed across `resources/views/app/hrm`, `resources/views/app/import`, and `resources/views/app/inventory`:
- Total files: 32 (27 HRM, 1 Import, 4 Inventory).
- **Instances of `@can` or `Context::hasPermission`**: Found in only **1 file** out of 32:
  - `resources/views/app/hrm/payroll/show.blade.php`: Lines 68 and 121 (`@if(\App\Support\Context::hasPermission('users.manage'))` wrapping approve, delete, and pay buttons).
  - `resources/views/app/hrm/index.blade.php`: Lines 952 and 976 (wraps `showImportEmployeeModal` and `showImportAttendanceModal` in `users.manage`).
- **Unguarded Actions**:
  - `resources/views/app/import/index.blade.php`:
    - All 4 tabs (Materials, Products, Recipes, Inventory), file selectors, preview buttons, and execute import buttons are visible and active for any viewer.
  - `resources/views/app/inventory/stocks.blade.php`:
    - Line 61: "Import Stok" link (`route('import.index', ['tab' => 'inventory'])`) is visible to all viewers.
    - Line 237 (desktop table) & Line 295 (mobile cards): "Quick Adjust" button (`openAdjust(...)`) is visible to all viewers.
  - `resources/views/app/inventory/opname.blade.php`:
    - Line 197: "Import Opname" button (`showImportModal = true`) is visible to all viewers.
    - Line 204: "Tambah Opname Baru" button (`showCreateModal = true`) is visible to all viewers.
    - Lines 298, 353: "Rekonsiliasi" button (`openReconcile(...)`) is visible to all viewers.
  - `resources/views/app/inventory/transfers.blade.php`:
    - Line 66: "Transfer Antar Lokasi" button (`showCreateModal = true`) is visible to all viewers.
    - Lines 149, 206: "Konfirmasi Terima" button (`openReceive(...)`) is visible to all viewers.
  - `resources/views/app/hrm/index.blade.php`:
    - Line 1001: "Tambah Lokasi" button is completely unguarded.
    - Line 1009: "Tambah Shift" button is completely unguarded.
    - Line 1017: "Atur Jadwal Roster" button is completely unguarded.
    - Line 1025: "Buat Payroll Baru" button (`route('hrm.payrolls.create')`) is completely unguarded.
    - Line 1033: "Catat Kasbon" button is completely unguarded.
    - Line 946: "Export Karyawan" link is unguarded.
    - Line 970: "Export Presensi" link is unguarded.
  - `resources/views/app/hrm/partials/tab-employees.blade.php`:
    - Line 167: Edit Employee button (`openEditEmployee`) is unguarded.
    - Line 180: Delete Employee button (`hrm.employees.destroy`) only checks `@if(! $isOwner)`, without checking user authorization.
    - Lines 136, 144: Biometric enrollment button (`openFaceRegister`) is unguarded.
  - `resources/views/app/hrm/partials/tab-locations.blade.php`:
    - Lines 75, 88: Edit & Delete Location buttons are unguarded.
  - `resources/views/app/hrm/partials/tab-shifts.blade.php`:
    - Line 90: Edit Shift button is unguarded.
  - `resources/views/app/hrm/partials/tab-payrolls.blade.php`:
    - Line 79: Delete Payroll Draft button (`hrm.payrolls.destroy`) only checks `@if($p->status === 'draft')`, unguarded by permission.
  - `resources/views/app/hrm/partials/tab-loans.blade.php`:
    - Line 67: Cancel Loan button (`hrm.loans.cancel`) only checks loan status, unguarded by permission.
  - `resources/views/app/hrm/partials/tab-corrections.blade.php`:
    - Line 81: "Review Koreksi" button (`openReviewCorrection`) is unguarded.

---

## 2. Logic Chain

1. **Principle of Least Privilege & Clean Routing (Laravel 11 `HasMiddleware`)**:
   - COOCA standards require controller-level authorization via `HasMiddleware` so routes remain clean and authorization logic is tightly coupled to the controller.
   - `InventoryWebController` and `WarehouseWebController` properly adhere to this pattern.
   - `ImportWebController` and `HrmApiController` violate this pattern completely, having zero controller middleware.
2. **Cross-Tenant & IDOR Protection**:
   - All examined web controllers correctly scope database queries with `Context::requireBusiness()` and `$business->id`.
   - However, in `HrmApiController` (API), while tenant scoping is present, role authorization is absent. This allows tenant privilege escalation (e.g., a cashier can read all executive salaries via `/api/v1/hrm/hub` or update salary parameters via `PUT /api/v1/hrm/employees/{membership}`).
3. **Permission Coupling & Misattribution**:
   - In `HrmWebController`, relying on `users.view` and `users.manage` creates privilege conflation. A company owner wanting an office manager to invite users in `settings` is forced to give that user full access to see and disburse payroll.
   - Introducing `hrm.view`, `hrm.manage`, `hrm.payroll.view`, `hrm.payroll.manage`, and `hrm.attendance.manage` resolves this conflation cleanly.
4. **View-Level Defense-in-Depth**:
   - Server-side enforcement in controllers is the absolute security perimeter.
   - However, rendering buttons for unauthorized actions causes severe UX friction (users see buttons, click them, fill forms, and then hit 403 Forbidden).
   - Wrapping write actions with `@if(\App\Support\Context::hasPermission(...))` or `@can(...)` ensures unauthorized users only see information relevant to their role.

---

## 3. Caveats

1. **Backward Compatibility with Existing Workspace Roles**:
   - Existing businesses in the database have roles with permissions already synced from earlier versions of `RbacSeeder`.
   - If new permissions (`hrm.*`, `import.*`) are added, running `php artisan db:seed --class=RbacSeeder --force` will update system default roles (`business_id = null`).
   - For custom tenant roles, a database migration or sync routine should be considered so existing managers don't lose access upon permission split.
2. **Dual-Check Compatibility During Transition**:
   - In controllers and views, checking `Context::hasPermission('hrm.view') || Context::hasPermission('users.view')` during a transition period prevents breaking existing users who were granted `users.view` prior to the split.
3. **Public Payslip Endpoint**:
   - `publicPayslip(string $token)` in `HrmWebController` uses UUID/token validation rather than login session. It is routed in `routes/public.php` and must remain exempt from `HasMiddleware` permission guards.

---

## 4. Conclusion & Actionable Recommendations

### 4.1 Master Controller & Permission Matrix

| Module | Controller | Recommended Interface | Method | Guard Permission | Notes |
|---|---|---|---|---|---|
| **HRM** | `HrmWebController` | `implements HasMiddleware` | `index`, `showPayslip` | `hrm.view` (or `users.view`) | Main hub view |
| | | | `showPayroll`, `exportPayroll*` | `hrm.payroll.view` | Confidential financial data |
| | | | `storeEmployee`, `updateEmployee`, `destroyEmployee` | `hrm.manage` | Staff roster management |
| | | | `storeLocation`, `updateLocation`, `destroyLocation` | `hrm.manage` | Location & geofence |
| | | | `storeShift`, `updateShift`, `destroyShift` | `hrm.manage` | Work shift definitions |
| | | | `storeSchedule`, `updateSchedule`, `destroySchedule` | `hrm.manage` | Roster calendar |
| | | | `exportEmployees`, `employeeTemplate`, `*EmployeeImport` | `hrm.manage` | Employee data exchange |
| | | | `exportAttendance`, `attendanceTemplate`, `*AttendanceImport` | `hrm.attendance.manage` | Attendance log exchange |
| | | | `approveCorrection`, `rejectCorrection` | `hrm.attendance.manage` | Attendance corrections |
| | | | `createPayroll`, `storePayroll`, `approvePayroll`, `payPayroll`, `destroyPayroll` | `hrm.payroll.manage` | Payroll execution |
| | | | `storeLoan`, `cancelLoan` | `hrm.payroll.manage` (or `hrm.manage`) | Staff loans |
| | | | `registerEmployeeFace`, `resetEmployeeFace` | `hrm.manage` | Biometric enrollment |
| | `HrmApiController` | `implements HasMiddleware` | `hub` | `hrm.view` | Block salary leak for staff |
| | | | `storeEmployee`, `updateEmployee`, `storeShift` | `hrm.manage` | Staff & shift API |
| | | | `storeLoan` | `hrm.payroll.manage` | Loan recording API |
| | | | `approveCorrection`, `rejectCorrection` | `hrm.attendance.manage` | Attendance review API |
| | `AttendanceApiController` | In-method / Middleware | `checkIn`, `clockOut`, `today`, `portal`, `payslips` | Authenticated membership | Self-service mobile actions |
| | | | `approveCorrection`, `rejectCorrection`, `requestRevision` | `hrm.attendance.manage` | Supervisor approval |
| | | | `registerFace`, `resetFace` | `hrm.manage` | Face enrollment |
| **Import** | `ImportWebController` | `implements HasMiddleware` | `index`, `download*Template` | `import.view` (or module views) | Template downloading |
| | | | `previewProducts`, `executeProducts` | `products.create` (or `import.manage`) | Product catalog import |
| | | | `previewMaterials`, `executeMaterials` | `materials.create` (or `import.manage`) | Materials import |
| | | | `previewRecipes`, `executeRecipes` | `costing.manage` (or `import.manage`) | BOM recipes import |
| | | | `previewInventory`, `executeInventory` | `inventory.manage` (or `import.manage`) | Stock level import |
| **Inventory** | `InventoryWebController` | `implements HasMiddleware` | `stocks`, `movements`, `opnames`, `transfers`, `exportBlankOpname` | `inventory.view` | Already implemented |
| | | | `quickAdjust`, `approveAdjustment`, `rejectAdjustment`, `storeOpname`, `*OpnameImport`, `reconcileOpname`, `storeTransfer`, `receiveTransfer` | `inventory.manage` | Already implemented |
| | `InventoryController` (API) | `implements HasMiddleware` | `stocks`, `movements` | `inventory.view` | Encapsulate from route |
| | | | `adjust` | `inventory.manage` | Encapsulate from route |
| | `StockOpnameController` (API) | `implements HasMiddleware` | `index`, `show` | `inventory.view` | Encapsulate from route |
| | | | `store`, `reconcile` | `inventory.manage` | Encapsulate from route |
| | `StockTransferController` (API)| `implements HasMiddleware` | `index`, `show` | `inventory.view` | Encapsulate from route |
| | | | `store`, `dispatchTransfer`, `receive` | `inventory.manage` | Encapsulate from route |

---

### 4.2 Seeders Action Plan (`database/seeders/RbacSeeder.php`)

Add the following entries to `permissionMatrix()`:
```php
// ─── Human Resource Management (HRM) & Payroll ───────────
'hrm.view'              => ['Lihat Modul SDM / HRM',         'hrm', 'Melihat daftar karyawan, kehadiran, lokasi, shift, dan jadwal kerja.'],
'hrm.manage'            => ['Kelola Karyawan & SDM',         'hrm', 'Menambah, mengubah data karyawan, biometrik, lokasi geofence, shift, dan jadwal roster.'],
'hrm.attendance.manage' => ['Kelola & Koreksi Absensi',      'hrm', 'Menyetujui/menolak tiket koreksi presensi dan import log fingerprint.'],
'hrm.payroll.view'      => ['Lihat Data Gaji & Slip',        'hrm', 'Melihat riwayat penggajian bulanan, rincian take-home pay, dan slip gaji karyawan.'],
'hrm.payroll.manage'    => ['Kelola & Eksekusi Payroll',     'hrm', 'Membuat draft penggajian, persetujuan gaji, bayar gaji, hapus payroll, dan kelola kasbon.'],

// ─── Unified Data Exchange / Import ───────────────────────
'import.view'           => ['Lihat Data Exchange Hub',       'import', 'Melihat hub import dan mengunduh spreadsheet template resmi.'],
'import.manage'         => ['Eksekusi Import Data Massal',   'import', 'Mengunggah dan mengeksekusi import massal produk, bahan baku, resep, dan stok.'],
```

Update `roleDefinitions()` in `RbacSeeder.php`:
- `manager`: Add `'hrm.view', 'hrm.manage', 'hrm.attendance.manage', 'hrm.payroll.view', 'hrm.payroll.manage', 'import.view', 'import.manage'`.
- `finance`: Add `'hrm.payroll.view', 'hrm.payroll.manage', 'import.view'`.
- `warehouse`: Add `'import.view'`.

---

### 4.3 View Guards Action Plan

In Blade views, guard buttons using `@if (\App\Support\Context::hasPermission('...'))` or `@can`:
1. **`resources/views/app/import/index.blade.php`**:
   - Wrap product execution in `Context::hasPermission('products.create') || Context::hasPermission('import.manage')`.
   - Wrap material execution in `Context::hasPermission('materials.create') || Context::hasPermission('import.manage')`.
   - Wrap recipe execution in `Context::hasPermission('costing.manage') || Context::hasPermission('import.manage')`.
   - Wrap inventory execution in `Context::hasPermission('inventory.manage') || Context::hasPermission('import.manage')`.
2. **`resources/views/app/inventory/stocks.blade.php`**:
   - Wrap "Import Stok" link in `@if (\App\Support\Context::hasPermission('inventory.manage'))`.
   - Wrap "Quick Adjust" buttons in `@if (\App\Support\Context::hasPermission('inventory.manage'))`.
3. **`resources/views/app/inventory/opname.blade.php`**:
   - Wrap "Import Opname" and "Tambah Opname Baru" buttons in `@if (\App\Support\Context::hasPermission('inventory.manage'))`.
   - Wrap "Rekonsiliasi" buttons in `@if (\App\Support\Context::hasPermission('inventory.manage'))`.
4. **`resources/views/app/inventory/transfers.blade.php`**:
   - Wrap "Transfer Antar Lokasi" and "Konfirmasi Terima" in `@if (\App\Support\Context::hasPermission('inventory.manage'))`.
5. **`resources/views/app/hrm/index.blade.php` & partials**:
   - Wrap "Tambah Lokasi", "Tambah Shift", "Atur Jadwal Roster", "Edit/Delete Employee", "Face Enroll" in `@if (\App\Support\Context::hasPermission('hrm.manage'))`.
   - Wrap "Buat Payroll Baru", "Hapus Draft Payroll", "Approve", "Pay", and "Catat/Batalkan Kasbon" in `@if (\App\Support\Context::hasPermission('hrm.payroll.manage'))`.
   - Wrap "Review Koreksi Presensi" in `@if (\App\Support\Context::hasPermission('hrm.attendance.manage'))`.

---

## 5. Verification Method

To verify these findings independently:
1. **Controller Inspection**:
   - Run: `Select-String -Path app/Http/Controllers/Web/ImportWebController.php -Pattern "HasMiddleware"` (Returns 0 matches).
   - Run: `Select-String -Path app/Http/Controllers/Web/Hrm/HrmWebController.php -Pattern "users.manage|users.view"` (Returns lines 47, 55).
   - Run: `Select-String -Path app/Http/Controllers/Api/V1/Hrm/HrmApiController.php -Pattern "HasMiddleware"` (Returns 0 matches).
2. **Seeder Inspection**:
   - Run: `Select-String -Path database/seeders/RbacSeeder.php -Pattern "'hrm\."` (Returns 0 matches).
   - Run: `Select-String -Path database/seeders/RbacSeeder.php -Pattern "'import\."` (Returns 0 matches).
3. **View Guards Inspection**:
   - Run: `Select-String -Path resources/views/app/inventory/*.blade.php -Pattern "hasPermission"` (Returns 0 matches).
   - Run: `Select-String -Path resources/views/app/import/*.blade.php -Pattern "hasPermission"` (Returns 0 matches).
4. **Post-Implementation Regression Command**:
   - Test execution: `php artisan test --filter=Rbac` or `php artisan test --filter=Hrm`
   - Route listing check: `php artisan route:list --name=hrm` and `php artisan route:list --name=import`
