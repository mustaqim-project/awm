# Comprehensive Mobile Modules & Android Production Audit Report

**Auditor:** `explorer_survey_modules_1`  
**Date:** 2026-11-03  
**Target Mobile Repository:** `C:\laragon\www\cooca_my_own` (Flutter)  
**Target Backend Repository:** `C:\laragon\www\cooca_core` (Laravel 11, PHP 8.3)  
**SSOT Live Endpoint:** `https://cooca.id` (Live Production API)  
**Mode:** READ-ONLY Deep Specification & Code Survey

---

## 1. Executive Summary & Scope

This audit provides a complete, line-by-line inspection of all 14 functional mobile modules in `cooca_my_own` and the Android production build configuration against the live backend architecture in `cooca_core`. The goal is to identify and eradicate all artificial mock fallbacks, fake data lists, simulation responses, and hardcoded structures, while establishing an exact contract mapping to live backend endpoints.

### Key Audit Findings:
1. **Pervasive Mock Fallbacks in Offline / Failure Paths**:
   - **POS Catalog**: `_getDefaultStarterProducts()` hardcodes 24+ dummy products across 7 business industries whenever local cache is empty.
   - **Inventory**: `_materials` and `_movementLogs` in `inventory_screen.dart` define hardcoded static lists (`mat_1`..`mat_7`, `mov_1`..`mov_3`) which contaminate inventory valuation.
   - **Dashboard**: `_buildHourlyTrendCard` renders a static `switch` statement for sales bars, ignoring live `state.hourlyTrend` from the API.
   - **HRM & Attendance**: `AttendanceService.getPortalData()` returns a static fake employee record (`usr-local`) on cache/network failure.
   - **Settings & Profile**: User profile editing modifies in-memory state with a fake `Future.delayed(500ms)` without sending a network request to the backend. Cache clearing is a simulated SnackBar.
2. **Endpoint Mismatches & API Gaps**:
   - Courier dispatch in `pos_service.dart` calls `/api/v1/pos/orders/$orderId/dispatch-courier`, whereas the live backend route is `POST /api/v1/commerce/orders/{order}/request-pickup`.
   - Account recovery in `auth_service.dart` generates fake ticket IDs `REC-YYYY-XXXX` locally, even though `POST /api/v1/account-recovery` and `GET /api/v1/account-recovery/status/{ticket}` already exist on the backend.
   - In-app notification center renders synthetic alerts from local state, while FCM token registration endpoints on the backend (`/api/v1/devices/fcm-token`) are unintegrated.
3. **Android Production Build Vulnerabilities**:
   - `buildTypes.release` is configured with `signingConfig = signingConfigs.getByName("debug")`, signing production APKs with the Android debug keystore.
   - `AndroidManifest.xml` enforces `android:usesCleartextTraffic="true"`, permitting unencrypted HTTP traffic.
   - R8 / ProGuard code obfuscation and resource shrinking are disabled (`isMinifyEnabled` and `isShrinkResources` omitted).
   - Raw `Image.network` is used throughout the UI instead of `cached_network_image`, causing high memory consumption and repetitive bandwidth usage.

---

## 2. Module-by-Module Audit (14 Modules)

### Module 1: Authentication & Session
- **Mobile Files**:
  - `lib/core/services/auth_service.dart`
  - `lib/screens/login_screen.dart`
  - `lib/screens/business_switcher_sheet.dart`
- **Observed Problems & Mocks**:
  1. *Hardcoded fallback business* (`auth_service.dart:480-487`):
     ```dart
     return [
       {
         'id': 'b1',
         'name': 'Kopi Nusantara Mandiri',
         'location': 'Cabang Utama (Tebet, Jaksel)',
       }
     ];
     ```
     When `getBusinesses()` encounters a network or parse failure, it returns this hardcoded coffee shop business instead of reporting the true error.
  2. *Simulated Account Recovery* (`auth_service.dart:405-467`):
     `submitAccountRecovery` generates a fake ticket `REC-${DateTime.now().year}-${1000 + Random().nextInt(9000)}` and simulates a 1-second delay without calling any backend endpoint.
- **Live Backend Endpoints**:
  - `POST /api/v1/auth/login` (`AuthController::login`)  Throttled to 10 req/min.
  - `GET /api/v1/auth/me` (`AuthController::me`)  Returns user profile & role.
  - `GET /api/v1/businesses` or `GET /api/v1/me/businesses` (`BusinessController::index`)  Returns tenant businesses.
  - `POST /api/v1/businesses/switch` or `POST /api/v1/me/active-business` (`BusinessController::setActive`)  Sets active tenant session cookie/token context.
  - `POST /api/v1/account-recovery` (`AccountRecoveryApiController::store`)  Real public endpoint for account recovery requests.
  - `GET /api/v1/account-recovery/status/{ticket}` (`AccountRecoveryApiController::status`)  Real status checking endpoint.
- **Remediation**:
  - Remove fake `REC-` ticket generator and fake business `b1`. Wire directly to `/api/v1/account-recovery` and `/api/v1/businesses`.

---

### Module 2: Dashboard
- **Mobile Files**:
  - `lib/screens/dashboard_screen.dart`
  - `lib/state/merchant_state.dart`
- **Observed Problems & Mocks**:
  1. *Hardcoded Hourly Trend Bar Heights* (`dashboard_screen.dart:1647-1656`):
     ```dart
     final pct = switch (h) {
       8 => 0.25,
       10 => 0.45,
       12 => 0.95,
       14 => 0.70,
       16 => 0.35,
       18 => 0.85,
       20 => 0.60,
       22 => 0.20,
       _ => 0.10,
     };
     ```
     The hourly sales graph ignores `state.hourlyTrend` returned by `/api/v1/mobile/dashboard/pulse` and renders this static switch pattern.
  2. *Hardcoded Insight Copy* (`dashboard_screen.dart:1755`):
     `"Puncak penjualan tertinggi gerai Anda tercatat pada jam 12:00 - 14:00."` is statically written in the widget.
  3. *Static Unsplash Avatar* (`dashboard_screen.dart:246`):
     `NetworkImage('https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?...')` is hardcoded instead of displaying the authenticated user's photo or monogram initials.
  4. *Missing Recent Orders Call*:
     `state.fetchRecentPosOrders()` is not called inside `_fetchDashboardData()`, causing the recent transactions card to remain empty unless the user first navigates into the POS screen.
- **Live Backend Endpoints**:
  - `GET /api/v1/mobile/dashboard/pulse` (`MobileOwnerPulseApiController::pulse`)  Returns `revenue_today`, `pos_revenue`, `growth_pct`, `hourly_trend`, `active_shift`, and `recent_orders`.
  - `GET /api/v1/mobile/dashboard/analytics` (`MobileOwnerPulseApiController::analytics`)  Returns comprehensive cockpit KPIs.
- **Remediation**:
  - Bind hourly trend bars directly to `state.hourlyTrend[hour]['revenue'] / maxRevenue`.
  - Replace static Unsplash avatar with `state.currentUserName` initials and dynamic profile photo.
  - Include `fetchRecentPosOrders()` in `_fetchDashboardData()`.

---

### Module 3: POS / Cashier
- **Mobile Files**:
  - `lib/screens/pos_screen.dart`
  - `lib/state/merchant_state.dart`
  - `lib/core/services/pos_service.dart`
- **Observed Problems & Mocks**:
  1. *Massive Starter Products Mock* (`merchant_state.dart:1231-1576, 1612-1614`):
     `_getDefaultStarterProducts()` instantiates 24+ static products across 7 industry categories (`starter_ws_*`, `starter_ph_*`, `starter_ld_*`, `starter_srv_*`, `starter_mf_*`, `starter_rtl_*`, `starter_fb_*`) with fake prices, static stock levels, and hardcoded Unsplash image URLs. This method is invoked by `_loadOfflineCachedCatalog()` when local storage is empty, causing phantom products to appear on fresh installs.
- **Live Backend Endpoints**:
  - `GET /api/v1/pos/terminal/bootstrap` (`PosTerminalController::terminalData`)  Returns authenticated cashier context, product catalog with modifiers, active categories, payment channels, and open shift details.
  - `POST /api/v1/pos/terminal/checkout` (`PosTerminalController::checkout`)  Processes transactions, discounts, taxes, and multi-payment splits.
  - `POST /api/v1/pos/sync/batch` (`PosSyncApiController::batchSync`)  Idempotent batch sync for offline-created transactions.
- **Remediation**:
  - Eliminate `_getDefaultStarterProducts()`. When offline cache is empty and network is unavailable, show an empty state prompting the user to sync their store catalog online.

---

### Module 4: Shift Management
- **Mobile Files**:
  - `lib/screens/pos/pos_shift_modal.dart`
  - `lib/core/services/pos_service.dart`
- **Observed Problems & Mocks**:
  1. *Hardcoded Default Opening Cash* (`pos_shift_modal.dart:105`):
     `_cashController.text = '100000';`
     Forces a default modal opening cash of Rp 100,000 instead of leaving the controller empty or initializing from the backend's previous closing cash float.
- **Live Backend Endpoints**:
  - `GET /api/v1/pos/shifts/current` or `GET /api/v1/pos/shifts/active` (`PosShiftController::current`)  Returns the currently active register shift.
  - `POST /api/v1/pos/shifts/open` (`PosShiftController::open`)  Body: `{'initial_cash': double, 'notes': string}`.
  - `POST /api/v1/pos/shifts/{posShift}/close` (`PosShiftController::close`)  Body: `{'closing_cash': double, 'notes': string}`.
  - `POST /api/v1/pos/shifts/{posShift}/cash-movement` (`PosShiftController::recordCashMovement`)  Records cash in / cash out.
- **Remediation**:
  - Set `_cashController.text = ''` or prefill with verified previous shift float.

---

### Module 5: Sales & Transactions
- **Mobile Files**:
  - `lib/screens/pos/pos_history_modal.dart`
  - `lib/screens/pos/pos_online_orders_modal.dart`
  - `lib/core/services/pos_service.dart`
- **Observed Problems & Mocks**:
  1. *Courier Dispatch Route Mismatch* (`pos_service.dart:703` & `pos_online_orders_modal.dart:229`):
     Calls `/api/v1/pos/orders/$orderId/dispatch-courier`.
     In backend `routes/api.php` line 1520, the active fulfillment route is:
     `POST /api/v1/commerce/orders/{order}/request-pickup` (`MerchantOrderApiController::requestPickup`).
  2. *Payment Verification Route Mismatch*:
     `pos_service.dart` calls `/api/v1/pos/orders/$orderId/verify-payment`.
     In backend `routes/api.php` line 1519, the route is:
     `POST /api/v1/commerce/orders/{order}/verify-payment` (`MerchantOrderApiController::verifyPayment`).
- **Live Backend Endpoints**:
  - `GET /api/v1/pos/orders` (`PosOrderController::index`)  Lists POS transaction history.
  - `GET /api/v1/commerce/orders/incoming` (`PosIncomingOrderApiController::incomingOrders`)  Incoming omnichannel & QR orders.
  - `POST /api/v1/commerce/orders/{order}/request-pickup` (`MerchantOrderApiController::requestPickup`)  Instant logistics dispatch.
  - `POST /api/v1/pos/orders/{posOrder}/void` (`PosOrderController::void`)  Requires `pos.supervisor_pin`.
- **Remediation**:
  - Update `pos_service.dart` to target `/api/v1/commerce/orders/$orderId/request-pickup` and `/api/v1/commerce/orders/$orderId/verify-payment`.

---

### Module 6: Products & Categories
- **Mobile Files**:
  - `lib/state/merchant_state.dart`
  - `lib/core/services/pos_service.dart`
- **Observed Problems & Mocks**:
  - Products catalog offline fallback polluted by `_getDefaultStarterProducts()`.
- **Live Backend Endpoints**:
  - `GET /api/v1/products` (`ProductController::index`)  Full product list with pagination.
  - `GET /api/v1/product-categories` (`ProductCategoryController::index`)  Category taxonomy.
  - `GET /api/v1/pos/terminal/search-products` (`PosTerminalController::searchProducts`)  Search by name/SKU/barcode.
  - `GET /api/v1/pos/modifiers` (`PosModifierApiController::index`)  Modifier groups & options.
- **Remediation**:
  - Persist only real product responses from `/api/v1/products` into local SQLite/SharedPreferences cache.

---

### Module 7: Inventory & Stock Management
- **Mobile Files**:
  - `lib/screens/inventory_screen.dart`
  - `lib/screens/inventory/inventory_opname_tab.dart`
  - `lib/core/services/inventory_service.dart`
- **Observed Problems & Mocks**:
  1. *Hardcoded Materials List* (`inventory_screen.dart:60-152`):
     7 static raw material items (`mat_1` to `mat_7`) with fixed prices and supplier names:
     ```dart
     final List<Map<String, dynamic>> _materials = [
       {'id': 'mat_1', 'name': 'Biji Kopi Arabika Gayo', 'sku': 'RAW-COF-01', ...},
       ...
       {'id': 'mat_7', 'name': 'Paper Cup 12oz + Lid', ...},
     ];
     ```
  2. *Hardcoded Stock Movement Logs* (`inventory_screen.dart:155-186` & `inventory_opname_tab.dart:228-250`):
     `_movementLogs` contains 3 static entries (`mov_1`, `mov_2`, `mov_3`). If the live API returns an empty array, the UI falls back to displaying these static mock records.
  3. *Valuation Calculation Contamination* (`inventory_screen.dart:281-284`):
     `_calculateTotalValuation` iterates over the hardcoded `_materials` array, displaying an inaccurate asset valuation in the UI.
- **Live Backend Endpoints**:
  - `GET /api/v1/inventory/stocks` (`InventoryController::stocks`)  Real item stocks and alert levels.
  - `GET /api/v1/inventory/movements` (`InventoryController::movements`)  Real audit trail of stock adjustments, sales deductions, and goods receipts.
  - `POST /api/v1/inventory/adjustments` (`InventoryController::adjust`)  Adjust stock count.
  - `GET /api/v1/inventory/opnames` & `POST /api/v1/inventory/opnames` (`StockOpnameController`)  Stock opname sessions.
  - `POST /api/v1/inventory/opnames/{stockOpname}/reconcile` (`StockOpnameController::reconcile`)  Reconcile physical inventory.
- **Remediation**:
  - Purge `_materials` and `_movementLogs` mock lists from `inventory_screen.dart`.
  - Bind inventory table and valuation exclusively to `_inventoryService.getStocks()` and `_inventoryService.getMovements()`.

---

### Module 8: Purchasing
- **Mobile Files**:
  - `lib/screens/purchasing/goods_receipt_screen.dart`
  - `lib/core/services/inventory_service.dart`
- **Observed Problems & Mocks**:
  - No dummy lists in `goods_receipt_screen.dart`. However, instant stock-in currently lacks full offline queuing support.
- **Live Backend Endpoints**:
  - `GET /api/v1/purchasing/purchase-orders` (`PurchaseOrderController::index`)  Purchase orders.
  - `GET /api/v1/inventory/purchase-orders/pending` (`InventoryPurchaseOrderController::pending`)  POs awaiting goods receipt.
  - `POST /api/v1/inventory/goods-receipts` (`InventoryGoodsReceiptController::store`)  Inbound stock batch tracking.
  - `POST /api/v1/purchasing/stock-in` (`GoodsReceiptController::instantStockIn`)  Instant receiving without PO.
- **Remediation**:
  - Connect barcode scanner in `goods_receipt_screen.dart` to `/api/v1/inventory/purchase-orders/pending`.

---

### Module 9: Customers & Suppliers
- **Mobile Files**:
  - `lib/screens/pos/pos_customer_debt_sheet.dart`
  - `lib/core/services/pos_service.dart`
- **Observed Problems & Mocks**:
  - No fake data found; direct REST endpoints are used.
- **Live Backend Endpoints**:
  - `GET /api/v1/crm/customers` (`CustomerController::index`)  Customer directory.
  - `GET /api/v1/suppliers` (`SupplierController::index`)  Supplier directory.
  - `GET /api/v1/customers/{customer}/debts` (`CustomerDebtApiController::debts`)  Unpaid customer balances.
  - `POST /api/v1/customers/{customer}/repay-debt` (`CustomerDebtApiController::repayDebt`)  Record debt payment.
- **Remediation**:
  - Ensure search input in customer selection utilizes debounce (300ms) to avoid request flooding.

---

### Module 10: Finance & Reports
- **Mobile Files**:
  - `lib/screens/reports_screen.dart`
  - `lib/core/services/reports_service.dart`
- **Observed Problems & Mocks**:
  1. *Hardcoded Quick HPP Calculator Defaults* (`reports_screen.dart:68-72`):
     `_quickHpp = 15000.0; _quickMargin = 40.0;`
  2. *Derivation from Local State on Failure* (`reports_screen.dart:98-115`):
     If `/api/v1/reports/cockpit` fails, the screen synthesizes cockpit data using local `MerchantState` metrics.
- **Live Backend Endpoints**:
  - `GET /api/v1/reports/cockpit` or `GET /api/v1/mobile/dashboard/analytics` (`MobileOwnerPulseApiController::analytics`)  Complete profit/loss, revenue, and gross margin analytics.
  - `GET /api/v1/finance/dashboard` (`FinanceController::dashboard`)  Financial health overview.
  - `GET /api/v1/finance/expenses` & `POST /api/v1/finance/expenses` (`FinanceController`)  Operational expense tracking.
  - `GET /api/v1/pos/reports/sales-summary` (`PosReportController::salesSummary`)  Shift & transaction breakdown.
- **Remediation**:
  - Handle API failure by displaying an error state with a "Coba Lagi" (Retry) action instead of synthesizing numbers.

---

### Module 11: HRM & Attendance
- **Mobile Files**:
  - `lib/screens/attendance_screen.dart`
  - `lib/core/services/attendance_service.dart`
- **Observed Problems & Mocks**:
  1. *Static Employee Profile Fallback* (`attendance_service.dart:30-79`):
     ```dart
     'employee': {
       'id': 'usr-local',
       'name': name,
       'job_title': role.toUpperCase(),
       'tenure_text': 'Aktif',
       'leave_allowance': 12,
       'compensation': {'base_salary': 0.0, 'fixed_allowances': 0.0},
     }
     ```
     When offline or when the network call fails, this synthetic employee record is injected into the portal state.
- **Live Backend Endpoints**:
  - `GET /api/v1/attendance/portal` (`AttendanceApiController::portal`)  Complete staff personal profile, active shift, today's attendance record, and 7-day stats.
  - `POST /api/v1/attendance/clock-in` & `POST /api/v1/attendance/clock-out` (`AttendanceApiController::checkIn` / `checkOut`)  Clock in/out with geolocation and biometric selfie verification.
  - `POST /api/v1/attendance/corrections` (`AttendanceApiController::storeCorrection`)  File attendance correction.
  - `GET /api/v1/attendance/payslips` (`AttendanceApiController::payslips`)  Staff payroll slips.
- **Remediation**:
  - Cache the last valid response from `/api/v1/attendance/portal` in `LocalStorageService`. If no cache exists, display an explicit offline indicator rather than generating a fake profile.

---

### Module 12: Employee Management (HRM Hub)
- **Mobile Files**:
  - `lib/core/services/hrm_service.dart`
- **Observed Problems & Mocks**:
  1. *Zeroed Hub Data Fallback* (`hrm_service.dart:31-57`):
     `getDefaultHubData()` returns an empty placeholder schema with zeroes when `/api/v1/hrm/hub` is unreachable.
  2. *Correction Approval Route Duplication*:
     `hrm_service.dart` defines `/api/v1/hrm/corrections/$id/approve`, while `routes/api.php` also exposes `/api/v1/attendance/corrections/{correction}/approve`. Both controllers exist, but consistency is needed.
- **Live Backend Endpoints**:
  - `GET /api/v1/hrm/hub` (`HrmApiController::hub`)  Management overview: staff count, payroll THP, active loans, pending corrections.
  - `POST /api/v1/hrm/employees` (`HrmApiController::storeEmployee`)  Create staff member.
  - `PUT /api/v1/hrm/employees/{membership}` (`HrmApiController::updateEmployee`)  Update staff member.
  - `POST /api/v1/hrm/shifts` (`HrmApiController::storeShift`)  Create shift schedule.
  - `POST /api/v1/hrm/loans` (`HrmApiController::storeLoan`)  Record employee advance/loan.
- **Remediation**:
  - Pass the exact server error message to the UI instead of falling back to default zeroed data.

---

### Module 13: Settings
- **Mobile Files**:
  - `lib/screens/settings/system_settings_screen.dart`
  - `lib/screens/settings/edit_user_profile_modal.dart`
  - `lib/screens/settings/pos_terminals_screen.dart`
  - `lib/core/services/api_client.dart`
- **Observed Problems & Mocks**:
  1. *Simulated Local Cache Clear* (`system_settings_screen.dart:60-69`):
     Tapping "Bersihkan Cache" displays a success SnackBar without clearing image memory cache or local storage.
  2. *Ephemeral Settings State* (`system_settings_screen.dart:168-204`):
     `_soundHapticsEnabled` and `_selectedLanguage` are stored only in widget state and reset upon navigating away.
  3. *In-Memory Profile Edit* (`edit_user_profile_modal.dart:63-71` & `merchant_state.dart:41-45`):
     Updating user profile changes in-memory variables and executes `await Future.delayed(500ms)` without sending a PUT/POST request to `/api/v1/profile`.
- **Live Backend Endpoints**:
  - `GET /api/v1/profile` (`ProfileController::show`)  Current user profile.
  - `PUT /api/v1/profile` (`ProfileController::update`)  Updates name, phone, avatar.
  - `POST /api/v1/profile/change-password` (`ProfileController::changePassword`)  Updates password.
  - `GET /api/v1/profile/businesses/{business}` & `PUT /api/v1/profile/businesses/{business}` (`ProfileController`)  Tenant business profile.
- **Remediation**:
  - Wire `_saveProfile` in `edit_user_profile_modal.dart` to `PUT /api/v1/profile`.
  - Implement actual cache clearing via `PaintingBinding.instance.imageCache.clear()` and `LocalStorageService.clearTempCache()`.
  - Persist app preferences (`haptics`, `language`) in `SharedPreferences`.

---

### Module 14: Notifications
- **Mobile Files**:
  - `lib/screens/notification_screen.dart`
  - `lib/state/merchant_state.dart`
- **Observed Problems & Mocks**:
  1. *Empty Unconnected Notifications Array* (`merchant_state.dart:734`):
     `final List<NotificationItem> merchantNotifications = [];` is initialized to empty and never populated from a backend API.
  2. *Entirely Synthetic In-App Notification Center* (`notification_screen.dart:28-127`):
     The notification screen creates dynamic alerts entirely from client-side state (`outOfStockProducts`, `lowStockProducts`, `kdsOrders`, `pendingOfflineTxCount`, and `hasActiveShift`). While useful as reactive UI cues, true server-dispatched alerts and FCM pushes are unhandled.
- **Live Backend Endpoints**:
  - `POST /api/v1/devices/fcm-token` (`MobileDeviceTokenApiController::registerB2bToken`)  Registers FCM device token for push notifications.
  - `DELETE /api/v1/devices/fcm-token` (`MobileDeviceTokenApiController::unregisterB2bToken`)  Unregisters token on logout.
- **Remediation**:
  - Integrate Firebase Cloud Messaging (FCM) client in Flutter and register the device token against `POST /api/v1/devices/fcm-token`.
  - Add backend route for `/api/v1/notifications` if a persistent notification inbox is required.

---

## 3. Master Findings Matrix

| # | Module | File Location | Problem / Mock Found | Live Backend Endpoint | Remediation Needed |
|---|---|---|---|---|---|
| 1 | Auth & Session | `lib/core/services/auth_service.dart:480` | Fallback business `b1: Kopi Nusantara Mandiri` | `GET /api/v1/businesses` | Eliminate hardcoded fallback; show real network error. |
| 2 | Auth & Session | `lib/core/services/auth_service.dart:405` | Local fake ticket `REC-YYYY-XXXX` & simulated delay | `POST /api/v1/account-recovery` | Wire directly to backend account recovery endpoint. |
| 3 | Dashboard | `lib/screens/dashboard_screen.dart:1647` | Hardcoded `switch (h)` hourly bar heights | `GET /api/v1/mobile/dashboard/pulse` | Bind graph directly to `state.hourlyTrend`. |
| 4 | Dashboard | `lib/screens/dashboard_screen.dart:246` | Static Unsplash profile picture URL | `GET /api/v1/auth/me` | Display authenticated user initials or profile avatar. |
| 5 | POS / Cashier | `lib/state/merchant_state.dart:1231` | `_getDefaultStarterProducts()` 24+ fake products | `GET /api/v1/pos/terminal/bootstrap` | Remove starter mock; show empty catalog state if un-synced. |
| 6 | Shift Management | `lib/screens/pos/pos_shift_modal.dart:105` | Hardcoded default opening cash `100000` | `GET /api/v1/pos/shifts/current` | Initialize with empty string or verified previous float. |
| 7 | Sales & Orders | `lib/core/services/pos_service.dart:703` | Route mismatch: `/api/v1/pos/orders/$id/dispatch-courier` | `POST /api/v1/commerce/orders/{order}/request-pickup` | Fix endpoint path in `pos_service.dart`. |
| 8 | Sales & Orders | `lib/core/services/pos_service.dart:660` | Route mismatch: `/api/v1/pos/orders/$id/verify-payment` | `POST /api/v1/commerce/orders/{order}/verify-payment` | Fix endpoint path in `pos_service.dart`. |
| 9 | Products | `lib/state/merchant_state.dart:1612` | Offline products populated from static starters | `GET /api/v1/products` | Populate local cache only from real server catalog. |
| 10 | Inventory | `lib/screens/inventory_screen.dart:60` | Hardcoded `_materials` list (7 raw materials) | `GET /api/v1/inventory/stocks` | Remove static list; bind table to live stocks. |
| 11 | Inventory | `lib/screens/inventory_screen.dart:155` | Hardcoded `_movementLogs` (3 static logs) | `GET /api/v1/inventory/movements` | Remove static logs; bind to live stock movements. |
| 12 | Inventory | `lib/screens/inventory_screen.dart:281` | Valuation calculation uses fake `_materials` | `GET /api/v1/inventory/stocks` | Calculate total valuation from verified stock data. |
| 13 | Finance & Reports | `lib/screens/reports_screen.dart:68` | Hardcoded HPP calculator defaults (15000 / 40%) | `GET /api/v1/reports/cockpit` | Retrieve store-specific cost averages from API. |
| 14 | HRM & Attendance | `lib/core/services/attendance_service.dart:30` | Static employee profile fallback `usr-local` | `GET /api/v1/attendance/portal` | Cache last server response; show offline state on error. |
| 15 | Employee Mgmt | `lib/core/services/hrm_service.dart:31` | Zeroed fallback structure on server failure | `GET /api/v1/hrm/hub` | Return true error status to caller instead of mock data. |
| 16 | Settings | `lib/screens/settings/system_settings_screen.dart:60` | Simulated cache clearing with fake SnackBar | N/A (Device storage) | Clear `PaintingBinding` image cache & temp storage. |
| 17 | Settings | `lib/screens/settings/edit_user_profile_modal.dart:63` | In-memory profile update with 500ms delay | `PUT /api/v1/profile` | Send HTTP update to backend profile endpoint. |
| 18 | Notifications | `lib/screens/notification_screen.dart:28` | Synthetic notifications generated from local state | `POST /api/v1/devices/fcm-token` | Connect FCM push tokens and real notification stream. |

---

## 4. Role/Permission & Multi-Industry Gating Audit

### Role & Permission Enforcement
1. **SSOT Contract**:
   - The backend `/api/v1/context/current` endpoint acts as the single source of truth for user permissions, returning:
     - `role`: string (e.g., `owner`, `admin`, `cashier`, `kitchen_staff`)
     - `is_owner`: boolean
     - `permissions`: array of Spatie permissions (e.g., `pos.terminal`, `pos.kitchen`, `dashboard.view`, `inventory.manage`)
2. **Mobile Mapping**:
   - `MerchantState.hasPermission(String permission)` checks if the user is owner or if the permission exists in `_activePermissions`.
   - `AccessGuard.navigate()` intercepts route requests and directs unauthorized users to `NoAccessScreen`.
3. **Audit Verification**:
   - Gating is properly enforced in UI navigation drawers and buttons.
   - **Recommendation**: Ensure direct route transitions in `MaterialApp.onGenerateRoute` also evaluate `AccessGuard.canAccess()` so deep links or direct navigator pushes cannot bypass checks.

### Multi-Industry Zero Feature Leakage
COOCA supports 25 UMKM industries across 6 core sectors: F&B, Retail, Services, Workshop/Bengkel, Laundry, and Pharmacy.
1. **Backend Context**:
   - `/api/v1/context/current` provides industry flags: `is_food_industry`, `has_dine_in`, `is_workshop`, `is_laundry`, `is_pharmacy`, `is_service`, `is_retail`, and `disabled_modules`.
2. **Mobile Implementation**:
   - `MerchantState.enforceIndustryGating()` suppresses F&B features (`gofood`, `dine_in`, table management) for retail and service templates.
   - `canAccessKds` explicitly returns `false` if `isRetail` is true (`if (isRetail) return false;`).
   - Dynamic tab switching in `pos_screen.dart` hides table layouts when `hasDineIn` is false.
3. **Audit Verification**:
   - Zero feature leakage is maintained in the UI. No restaurant features appear when logging in with a retail or service account.

---

## 5. Android Production Build & Performance Audit (R4)

### A. Android Build Configuration (`android/app/build.gradle.kts`)
1. **Debug Keystore in Release Build** (*Critical Security Issue*):
   - In `android/app/build.gradle.kts` lines 34-38:
     ```kotlin
     buildTypes {
         release {
             signingConfig = signingConfigs.getByName("debug")
         }
     }
     ```
     **Risk**: Production release APKs/AABs are signed with the default Android debug keystore. This prevents legitimate Google Play Store distribution, fails Play Protect validation, and allows malicious repackaging.
     **Remediation**: Configure a production `release` signing config backed by environment variables or `key.properties`.
2. **ProGuard / R8 Obfuscation Disabled**:
   - `isMinifyEnabled` and `isShrinkResources` are missing from `buildTypes.release`.
   - `proguard-rules.pro` does not exist in `android/app/`.
   - **Risk**: Decompiled APKs reveal cleartext class names, API endpoints, and logic. Unused code and resources are not stripped, increasing APK download size.
   - **Remediation**: Enable `isMinifyEnabled = true` and `isShrinkResources = true` in `buildTypes.release`, and add keep rules for Flutter, Cupertino icons, and platform channels.

### B. Network Security (`AndroidManifest.xml`)
1. **Cleartext Traffic Permitted**:
   - `android/app/src/main/AndroidManifest.xml` line 28 specifies:
     `android:usesCleartextTraffic="true"`
   - **Risk**: Allows unencrypted HTTP traffic over the wire, exposing bearer auth tokens and merchant transaction data to Man-In-The-Middle (MITM) inspection on public Wi-Fi networks.
   - **Remediation**: Set `android:usesCleartextTraffic="false"` for production. If local HTTP debugging is required, use `android/app/src/debug/AndroidManifest.xml` or configure `network_security_config.xml`.

### C. Performance & Memory Leak Audit
1. **Lack of Image Caching Library**:
   - `pubspec.yaml` does not include `cached_network_image`.
   - The app uses standard `Image.network` and `NetworkImage` throughout (`dashboard_screen.dart`, `pos_screen.dart`).
   - **Risk**: Every list scroll or screen rebuild causes images to be re-downloaded or re-decoded in RAM, leading to memory spikes, UI jank, and high network consumption.
   - **Remediation**: Add `cached_network_image: ^3.3.1` to `pubspec.yaml` and wrap network images in `CachedNetworkImage` with bounded disk/memory cache.
2. **Missing HTTP Request Cancellation & Deduplication**:
   - `ApiClient` uses the basic `http` package without `CancelToken` or in-flight request deduplication.
   - Rapid tab switching can leave abandoned requests running in the background, consuming CPU and bandwidth.
   - **Remediation**: Implement an active request tracker or migrate to `dio` with request cancellation on screen disposal.

---

## 6. Actionable Implementation Roadmap

1. **Phase 1: API Endpoint Alignment & Mock Purging**:
   - Update `pos_service.dart` courier and payment verification endpoints.
   - Remove `_getDefaultStarterProducts()`, `_materials`, and `_movementLogs` mock arrays.
   - Connect `EditUserProfileModal` to `PUT /api/v1/profile`.
   - Wire `submitAccountRecovery` to `POST /api/v1/account-recovery`.
2. **Phase 2: Android Production Build Hardening**:
   - Configure release signing credentials in `android/app/build.gradle.kts`.
   - Remove `android:usesCleartextTraffic="true"` from `main/AndroidManifest.xml`.
   - Enable R8 shrinking and create `proguard-rules.pro`.
3. **Phase 3: Performance Optimization**:
   - Add `cached_network_image` and replace all raw `Image.network` instances.
   - Implement debouncing on search fields and proper image cache eviction on logout.
