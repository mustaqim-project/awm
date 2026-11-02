# Technical Investigation Report: Mobile Codebase Readiness (`cooca_my_own`) for Phase 1

**Investigator**: `explorer_survey_2` (Teamwork Explorer)  
**Date**: 2026-11-02  
**Target Codebase**: `c:\laragon\www\cooca_my_own`  
**Integration Scope**: Phase 1 — Petty Cash / Quick Expense Kasir & Owner Approval Center  

---

## Executive Summary

The mobile Flutter repository `c:\laragon\www\cooca_my_own` is in an exceptionally healthy state:
- `flutter analyze` runs cleanly with **0 issues / 0 warnings / 0 errors**.
- `flutter test` executes with **10/10 passing tests**.
- Apple HIG design tokens (`ios_tokens.dart`, `ios_adaptive.dart`), theme system, and haptic feedback utilities are well established and consistently utilized.
- Both `camera: ^0.12.1` and `image_picker: ^1.2.4` are already declared in `pubspec.yaml`, with camera and photo library permissions already configured in `android/app/src/main/AndroidManifest.xml` and `ios/Runner/Info.plist`.
- The architecture cleanly separates concerns: `ApiClient` singleton for Sanctum HTTP communications, domain service classes (`PosService`, etc.), `MerchantState` (ChangeNotifier singleton) for reactive state, and `LocalStorageService` for offline and session storage.

Below are the comprehensive technical findings across all 5 requested investigation areas.

---

## 1. POS & Shift Management

### 1.1. Codebase Exploration (`lib/screens/pos/` & `lib/screens/pos/pos_shift_modal.dart`)
- **File**: `lib/screens/pos/pos_shift_modal.dart` (781 lines)
- **Component**: `PosShiftModal` is a stateful Cupertino bottom sheet (`showModalBottomSheet`) with:
  - Drag handle grabber, header bar with live shift status badge and close button.
  - Segmented control (when shift is open) with 3 tabs:
    1. `Shift Aktif` (Current drawer status, opening float, expected cash, closing cash input & variance calculator, closing action button)
    2. `Mutasi Kas` (Cash In / Cash Out recorder calling `_posService.recordCashMovement`)
    3. `Riwayat Shift` (Past shift list with status, opening cash, closing actual cash)

### 1.2. How Shifts are Started, Monitored, and Closed
1. **Starting a Shift**:
   - The user inputs the opening cash float in `_cashController` (default Rp 100.000).
   - Tapping "Buka Shift Kasir" calls `widget.state.openShift(openingCash: _enteredCash, notes: _notesController.text)` (line 144).
   - In `MerchantState.openShift` (`lib/state/merchant_state.dart`, lines 1480-1497):
     - Calls `_posService.openShift(openingCash: openingCash, notes: notes, locationId: selectedLocationId)`.
     - Sends `POST /api/v1/pos/shifts/open`.
     - On response `success == true`, parses `activeShift = PosShift.fromJson(...)`.
     - Stores in local storage via `LocalStorageService().setCachedPosActiveShift(sData)`.
     - Triggers `notifyListeners()` and `refreshPosCatalog()`.
2. **Monitoring Active Shifts**:
   - On opening `PosShiftModal`, `_initShiftData()` (lines 63-102):
     - Calls `_posService.getActiveShift(locationId: widget.state.selectedLocationId)` (`GET /api/v1/pos/shifts/active`).
     - If an active shift exists, updates `widget.state.updateActiveShift(liveShift)` and caches it.
     - Fetches live shift summary via `_posService.getShiftSummary(shiftId)` (`GET /api/v1/pos/shifts/$shiftId/summary`).
     - Populates `_liveShiftSummary` map (contains `expected_cash`, sales breakdowns, cash movements).
   - In `pos_screen.dart` (lines 1265-1289):
     - The top header renders a live status pill: green dot + `"Shift: {cashierName}"` if open, or red dot + `"Shift Kasir Ditutup"` if closed.
3. **Closing a Shift & Expected Cash Calculation**:
   - The cashier counts drawer physical cash and enters it in `_cashController`.
   - `_expectedCash` is retrieved via getter (lines 109-114):
     ```dart
     double get _expectedCash {
       if (_liveShiftSummary != null && _liveShiftSummary!['expected_cash'] != null) {
         return (_liveShiftSummary!['expected_cash'] as num).toDouble();
       }
       return widget.state.activeShift?.expectedCash ?? 0.0;
     }
     ```
   - Variance is computed in real-time as `_cashVariance = _enteredCash - _expectedCash` (line 117):
     - `_cashVariance == 0`: Green pill `"Selisih Kas: Pas (Rp 0)"`.
     - `_cashVariance > 0`: Blue pill `"Selisih Lebih: +Rp X"`.
     - `_cashVariance < 0`: Red pill `"Selisih Kurang: -Rp X"`.
   - Tapping "Tutup Shift Sekarang" calls `widget.state.closeShift(shiftId: shiftId, actualCash: _enteredCash, notes: ...)` (line 125).
   - In `MerchantState.closeShift` (lines 1500-1518):
     - Calls `_posService.closeShift(shiftId: targetShiftId, actualCash: actualCash, notes: notes)`.
     - Sends `POST /api/v1/pos/shifts/$shiftId/close`.
     - On success: sets `activeShift = null`, clears `LocalStorageService().setCachedPosActiveShift(null)`, and calls `notifyListeners()`.

### 1.3. Where Shift Actions are Accessed in `PosScreen` & Insertion Point for Petty Cash
In `lib/screens/pos_screen.dart`, shift actions can be accessed from:
1. **Header Shift Status Pill** (lines 1265-1290): Tapping the shift pill directly opens `PosShiftModal.show(context, state)`.
2. **Main POS Operations Menu Sheet** (`_showMainMenu`, lines 358-840):
   - Triggered by the top menu button.
   - Section `"OPERASIONAL KASIR"` (lines 498-587) contains:
     - Item 1: `title: state.hasActiveShift ? 'Kelola Shift' : 'Buka Shift'` (opens `PosShiftModal`)
     - Item 2: `title: 'Pesanan Tertahan'` (opens `PosHeldOrdersModal`)
     - Item 3: `title: 'Riwayat Transaksi'` (opens `PosHistoryModal`)
     - Item 4: `title: 'Pilih Cabang / Outlet'` (opens `_showLocationPicker`)
3. **Payment Interceptor** (`_openPaymentSheet`, lines 237-253): If `!state.hasActiveShift`, blocks payment and prompts opening shift.

**Recommended Integration Points for Petty Cash / Quick Expense**:
1. **In `PosScreen._showMainMenu`**: Add a dedicated tile under `"OPERASIONAL KASIR"` directly beneath `'Kelola Shift'`:
   - Title: `'Petty Cash / Pengeluaran Kas'`
   - Subtitle: `'Catat kas keluar operasional darurat & foto nota'`
   - Icon: `CupertinoIcons.money_dollar_circle_fill` or `CupertinoIcons.arrow_down_doc_fill`
   - Tap action: `PosExpenseModal.show(context, state)`
2. **Inside `PosShiftModal`**:
   - In the `Mutasi Kas` segment (or as an actionable button in the drawer summary card), provide a direct button: `"Catat Petty Cash / Kas Keluar (+Foto Nota)"` that opens `PosExpenseModal`.
   - When an expense is recorded via `PosExpenseModal`, automatically refresh `_initShiftData()` so that the new expense is deducted from `_expectedCash`.

### 1.4. How POS Stores Active Shift Details in State
- In `lib/state/merchant_state.dart`:
  - `PosShift? activeShift;` (line 66)
  - `bool get hasActiveShift => activeShift != null && activeShift!.status == 'open';` (line 67)
  - `void updateActiveShift(PosShift? shift) { activeShift = shift; notifyListeners(); }` (lines 69-72)
- In `lib/models/order.dart` (`class PosShift`, lines 129-170):
  - Fields: `id`, `cashierName`, `locationName`, `openingCash`, `status`, `openedAt`, `totalCashSales`, `totalNonCashSales`, `expectedCash`.
  - For Phase 1, `PosShift` can be extended to include `totalExpenses` (`total_expenses` or `cash_expenses`).
- Persisted locally:
  - Cached via `LocalStorageService().setCachedPosActiveShift(...)` as JSON map in SharedPreferences so state survives app reload.

---

## 2. State Management & API/Network Layer

### 2.1. State Management Architecture
- The mobile app uses **`ChangeNotifier`** and the built-in **`ListenableBuilder`** pattern (no heavyweight BLoC/Redux overhead).
- Central state: `MerchantState` (`lib/state/merchant_state.dart`, 2423 lines, singleton pattern `factory MerchantState() => _instance`).
- State fields are mutated and broadcast via `notifyListeners()`.
- Views observe state changes via `ListenableBuilder(listenable: MerchantState(), builder: (context, _) { ... })`.

### 2.2. Base URL & API Client
- **File**: `lib/core/services/api_client.dart` (238 lines).
- **Base URL Definition**:
  - `defaultProductionUrl = 'https://cooca.id'`
  - `defaultLocalLaragonUrl = 'http://cooca_core.test'`
  - Logic: In debug mode on desktop/web, defaults to `http://cooca_core.test`. On Android/iOS emulators/devices, defaults to `https://cooca.id` (or user-configurable server IP).
  - Can be dynamically updated via SharedPreferences key `cooca_marketplace_api_base_url`.
- **Methods**: `get()`, `post()`, `put()`, `delete()` with standard 15-second timeouts and JSON response processing.

### 2.3. Auth Tokens (Sanctum) Storage & Transport
- **Storage**:
  - `LocalStorageService` (`lib/core/services/local_storage_service.dart`) stores the Sanctum token in SharedPreferences under key `cooca_auth_token` and `cooca_customer_token`.
- **Transport**:
  - In `ApiClient._headers()`:
    ```dart
    Map<String, String> _headers({Map<String, String>? extra}) {
      final headers = <String, String>{
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      if (_customerToken != null && _customerToken!.isNotEmpty) {
        headers['Authorization'] = 'Bearer $_customerToken';
      }
      final bizId = LocalStorageService().getActiveBusinessId();
      if (bizId != null && bizId.isNotEmpty) {
        headers['X-Business-Id'] = bizId;
      }
      ...
    }
    ```
  - Both Bearer token and active tenant header `X-Business-Id` are automatically attached to every request.

### 2.4. Image / Photo Payload Protocol
- Notice that `ApiClient.post` transmits JSON payloads.
- Existing photo uploads in `cooca_my_own` (e.g. `attendance_screen.dart`, `in_app_biometric_camera_sheet.dart`, `location_biometric_service.dart`) encode images as Data URI strings:
  `'data:image/jpeg;base64,' + base64Encode(bytes)`.
- The backend `POST /api/v1/pos/expenses` will accept `receipt_image_base64`, ensuring 100% compatibility with `ApiClient.post` without requiring complex multipart stream conversions.

### 2.5. Offline Sync Mechanism (`PosOfflineSyncManager`)
- **File**: `lib/core/services/pos_offline_sync_manager.dart` (218 lines).
- **Pattern**:
  - Extends `ChangeNotifier` (singleton).
  - Generates RFC4122 v4 UUID (`client_uuid`) for idempotency.
  - Queues transactions in `LocalStorageService`.
  - Performs connectivity checks via `_api.testConnection()` (`GET /api/v1/health`).
  - Batch synchronizes pending transactions via `_posService.batchSync(payload)` (`POST /api/v1/pos/sync/batch`).
  - Marks each record as `syncing`, `synced`, or `failed` according to server-authoritative response.

---

## 3. Approvals & Dashboard Integration

### 3.1. Codebase Exploration (`lib/screens/dashboard_screen.dart`)
- **File**: `lib/screens/dashboard_screen.dart` (2243 lines).
- Implements Apple HIG Executive Enterprise Dashboard with:
  1. Ambient mesh lighting (`_buildAmbientLighting`)
  2. Executive Header (`_buildExecutiveHeader`):
     - Executive Avatar with green status dot (taps to `BusinessSwitcherSheet`).
     - Greeting and Business Selector dropdown pill.
     - Notification Bell button with red dot badge (lines 330-390, navigates to `NotificationScreen`).
     - System Settings Gear button (navigates to `SystemSettingsScreen`).
  3. Financial Hero Card (`_buildFinancialHeroCard`): Apple Titanium style card with today's revenue, gross profit, and transaction count.
  4. Active Cashier Shift Bar (`_buildActiveShiftBar`, lines 459, 525): Dynamic Island status strip.
  5. Operational Pulse Bento (`_buildOperationalPulseSection`, lines 1100-1250):
     - Metric 1: Kasir Aktif
     - Metric 2: Antrean Masak / Servis / Cucian / Resep
     - Metric 3: Pesanan Masuk Online (badge: "Perlu Konfirmasi" / "Bersih")
     - Metric 4: Peringatan Stok Menipis (badge: "Perlu Restock" / "Aman")
  6. Hourly Sales Trend Chart (`_buildHourlyTrendCard`)
  7. 6 Core Enterprise Module Launchers (`_buildModuleLauncherSection`, lines 1514-1640):
     - `Kasir POS` -> `PosScreen`
     - `Antrean / KDS` -> `KdsScreen`
     - `Presensi Tim` -> `AttendanceScreen`
     - `HR & Staf` -> `HrProfileScreen`
     - `Laporan` -> `ReportsScreen`
     - `Inventori` -> `InventoryScreen`
  8. Critical Low Stock Alerts section (shown conditionally if `state.criticalStocks.isNotEmpty`)
  9. Recent Orders Feed (`_buildRecentOrdersSection`)

### 3.2. Where Pending Approval Badge / Counter Can Be Cleanly Integrated
There are three complementary, high-impact touchpoints in `DashboardScreen`:
1. **Dynamic Alert Banner (Recommended Priority 1)**:
   - Positioned immediately beneath `_buildActiveShiftBar` (around lines 460 / 526).
   - Displayed conditionally when `state.pendingApprovalsCount > 0`:
     - Styled as an Apple HIG rounded card with soft amber/orange accent (`Color(0xFFFF9500)`).
     - Renders an alert icon (`CupertinoIcons.shield_lefthalf_fill`), bold text: `"${state.pendingApprovalsCount} Permintaan Persetujuan Tertunda"`, subtitle: `"Void transaksi, diskon & absensi memerlukan otorisasi owner"`, and a trailing badge or chevron.
     - Tapping directly navigates to `OwnerApprovalScreen`.
2. **Operational Pulse Bento (Priority 2)**:
   - In `_buildOperationalPulseSection`, provide an approval pulse metric or badge showing pending count and badge status (`"Perlu Otorisasi"` in amber).
3. **Module Launcher Grid (Priority 3)**:
   - In `_buildModuleLauncherSection`, add `"Pusat Persetujuan"` (`OwnerApprovalScreen`) as an operational module launcher featuring a dynamic badge indicator with the pending counter.

### 3.3. Navigation Path to `OwnerApprovalScreen`
- Destination path: `lib/screens/approvals/owner_approval_screen.dart`
- Navigation invocation:
  ```dart
  Navigator.push(
    context,
    CupertinoPageRoute(builder: (_) => const OwnerApprovalScreen()),
  );
  ```
- Accessible from:
  1. The new Dashboard Pending Approval Banner
  2. The Module Launchers grid in `DashboardScreen`
  3. The `NotificationScreen` approval alert items

### 3.4. Apple HIG Styling Tokens & Haptic Feedback Utilities
- **`lib/core/theme/ios_tokens.dart`**:
  - `IOSSpacing`: `xxs` (4), `xs` (8), `sm` (12), `md` (16, standard margin), `lg` (20), `xl` (24), `minTouchTarget` (44.0).
  - `IOSRadius`: `cell` (10), `card` (14), `button` (14), `sheet` (22), `badge` (8).
  - `IOSColors`: `accent` (Cupertino blue `#007AFF`), `systemGroupedBackground`, `secondarySystemGroupedBackground`, `success` (`#34C759`), `destructive` (`#FF3B30`), `warning` (`#FF9500`).
  - Context extensions: `context.isDark`, `context.groupedBackground`, `context.secondaryGroupedBackground`, `context.sheetBackground`, `context.labelColor`, `context.cardBorderColor`.
  - Typography: `IOSText.largeTitle`, `title1`, `title2`, `title3`, `headline`, `body`, `callout`, `subhead`, `footnote`, `caption1`.
- **`lib/core/theme/ios_adaptive.dart`**:
  - `IOSBreakpoints`: `regular: 600`, `wide: 1024`, `readableWidth: 672`.
  - Context helpers: `context.isCompact`, `context.isTablet`, `context.screenMargin`.
  - `IOSReadableContent` for tablet readable centering.
- **Haptic Feedback**:
  - Native Flutter `package:flutter/services.dart`:
    - `HapticFeedback.lightImpact()`: on tap / selection
    - `HapticFeedback.mediumImpact()`: on swipe threshold / action trigger
    - `HapticFeedback.heavyImpact()`: on destructive / reject action
    - `HapticFeedback.selectionClick()`: on tab switch
    - `HapticFeedback.vibrate()`: on validation error

---

## 4. Camera & Media Support

### 4.1. Package Dependencies (`pubspec.yaml`)
Inspecting `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  cupertino_icons: ^1.0.8
  intl: ^0.20.3
  google_fonts: ^9.0.0
  fl_chart: ^1.2.0
  http: ^1.2.2
  shared_preferences: ^2.3.2
  geolocator: ^14.1.1
  image_picker: ^1.2.4         # <-- ALREADY INSTALLED
  permission_handler: ^11.3.1   # <-- ALREADY INSTALLED
  google_sign_in: ^7.2.0
  camera: ^0.12.1               # <-- ALREADY INSTALLED
  mobile_scanner: ^7.4.2
  ...
```
- Both `camera` and `image_picker` are already present in `pubspec.yaml`. No new pub dependencies are required.

### 4.2. Android Permissions (`android/app/src/main/AndroidManifest.xml`)
Verified permissions:
- `<uses-permission android:name="android.permission.CAMERA"/>` (Line 5)
- `<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" android:maxSdkVersion="32" />` (Line 8)
- `<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />` (Line 9)
- `<uses-feature android:name="android.hardware.camera" android:required="false" />` (Line 17)
- `<application android:usesCleartextTraffic="true">` (Line 27)
All required Android camera and storage permissions are present and properly declared.

### 4.3. iOS Permissions (`ios/Runner/Info.plist`)
Verified keys in `ios/Runner/Info.plist`:
- `<key>NSCameraUsageDescription</key>` (Line 50):
  `<string>COOCA memerlukan izin kamera untuk verifikasi biometrik wajah (Face Recognition Attendance) dan pemindaian barcode/QR kasir.</string>`
- `<key>NSPhotoLibraryUsageDescription</key>` (Line 56):
  `<string>COOCA memerlukan izin galeri untuk memilih logo toko, foto produk, dan lampiran struk pembayaran.</string>`
Camera and gallery access for receipts are explicitly authorized in iOS metadata.

### 4.4. Existing Camera & Media Usage Patterns
- `lib/core/widgets/in_app_biometric_camera_sheet.dart`: Demonstrates direct live stream via `CameraController` with front/back camera switching.
- `lib/core/services/location_biometric_service.dart`: Demonstrates `ImagePicker().pickImage(source: ImageSource.camera, ...)` with byte reading and base64 encoding.
- `lib/screens/auth/account_recovery_sheet.dart`: Demonstrates `ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 80)`.
For `PosExpenseModal`, `ImagePicker().pickImage(source: ImageSource.camera, maxWidth: 1200, maxHeight: 1200, imageQuality: 80)` with gallery fallback provides the optimal balance of speed, image sharpness, and compact base64 size.

---

## 5. Testing Infrastructure & Codebase Health

### 5.1. Existing Tests in `test/`
- **`test/industry_gating_test.dart`** (286 lines):
  - Group 1: `MerchantState` Business Isolation (Zero Feature Leakage between F&B, Retail, Workshop, Laundry, Pharmacy).
  - Group 2: `PosPrinterModal` Gating (Verifies kitchen ticket copy options are hidden for non-food tenants).
  - Group 3: `DashboardScreen` Module Gating (Verifies adaptive titles and zero leakage for pure retail).
- **`test/widget_test.dart`** (13 lines):
  - Smoke test initializing `SharedPreferences.setMockInitialValues({})` and pumping `CoocaMyOwnApp`.

### 5.2. Mock Patterns & Test Utilities
- Mock initial preferences: `SharedPreferences.setMockInitialValues({})`.
- Viewport sizing for mobile/tablet in widget tests:
  ```dart
  tester.view.physicalSize = const Size(1080, 2400);
  tester.view.devicePixelRatio = 2.0;
  addTearDown(() {
    tester.view.resetPhysicalSize();
    tester.view.resetDevicePixelRatio();
  });
  ```
- Component pumping: `MaterialApp(home: Scaffold(body: ...))`.
- Finder assertions: `expect(find.text('...'), findsOneWidget)`.

### 5.3. Verification Execution Results
1. **`flutter analyze`**:
   - Command: `cd c:\laragon\www\cooca_my_own; flutter analyze`
   - Result: `No issues found! (ran in 16.1s)` — **Exit Code 0, 0 issues**.
2. **`flutter test`**:
   - Command: `cd c:\laragon\www\cooca_my_own; flutter test`
   - Result:
     ```
     00:03 +10: All tests passed!
     ```
   - **Exit Code 0, 10/10 tests passed**.

---

## 6. Implementation Architecture Plan for Mobile Phase 1

### Component 1: Petty Cash / Quick Expense Kasir
1. **Service Method** in `PosService` (`lib/core/services/pos_service.dart`):
   ```dart
   Future<Map<String, dynamic>> recordExpense({
     required String shiftId,
     required double amount,
     required String category,
     String? notes,
     String? receiptImageBase64,
   }) async {
     final body = <String, dynamic>{
       'pos_shift_id': shiftId,
       'amount': amount,
       'category': category,
       'notes': notes ?? '',
       if (receiptImageBase64 != null) 'receipt_image_base64': receiptImageBase64,
     };
     final response = await _api.post('/api/v1/pos/expenses', body: body);
     ...
   }
   ```
2. **State Method** in `MerchantState` (`lib/state/merchant_state.dart`):
   - `recordPosExpense({required double amount, required String category, String? notes, String? receiptImageBase64})`
   - Invokes `_posService.recordExpense(...)`.
   - On success, updates `activeShift?.expectedCash` locally and calls `notifyListeners()`.
3. **UI Modal Component** (`lib/screens/pos/pos_expense_modal.dart`):
   - Apple HIG bottom sheet (`IOSRadius.sheet`).
   - Category selector chips (Operasional, Bahan Baku/Perlengkapan, Logistik/Bensin, Kebersihan, Lain-lain).
   - Nominal input with Indonesian currency formatting (`Rp`).
   - Catatan / Keterangan text field.
   - Camera button (`Foto Bukti Nota`) utilizing `ImagePicker` (camera or gallery), rendering image preview thumbnail with clear button.
   - Saves record and shows green success toast.
4. **Trigger Points**:
   - Added to `PosScreen._showMainMenu` under `OPERASIONAL KASIR`.
   - Added to `PosShiftModal` under `Mutasi Kas`.
5. **Expected Cash Integration**:
   - Updating `PosShiftModal._expectedCash` and `_initShiftData()` so that the new expense immediately reduces expected cash.

### Component 2: Owner Approval Center
1. **Service Class** (`lib/core/services/approvals_service.dart`):
   - `getPendingApprovals()` -> `GET /api/v1/owner/approvals/pending`
   - `processApprovalAction(String id, {required String action, String? reason})` -> `POST /api/v1/owner/approvals/$id/action`
2. **State Integration** in `MerchantState` (`lib/state/merchant_state.dart`):
   - `List<Map<String, dynamic>> pendingApprovals = [];`
   - `int get pendingApprovalsCount => pendingApprovals.length;`
   - `Future<void> fetchPendingApprovals()`
   - `Future<Map<String, dynamic>> processApprovalAction(...)`
   - Hooked into `refreshPulse()` on dashboard pull-to-refresh.
3. **Screen Component** (`lib/screens/approvals/owner_approval_screen.dart`):
   - Apple HIG grouped list with filter tabs: `Semua`, `Void Pesanan`, `Diskon`, `Presensi`.
   - Interactive Swipeable Cards (Swipe right to Approve [Green], Swipe left to Reject [Red]) with `HapticFeedback.mediumImpact()`.
   - Confirmation dialog with optional reason field on reject.
4. **Dashboard Integration**:
   - In `lib/screens/dashboard_screen.dart`:
     - Render `_buildPendingApprovalsBanner()` directly under `_buildActiveShiftBar` when `state.pendingApprovalsCount > 0`.
     - Add `"Pusat Persetujuan"` module launcher card in `_buildModuleLauncherSection`.

### Component 3: Automated Testing
1. `test/pos_expense_test.dart`:
   - Unit test for expense recording, validation, expected cash deduction.
   - Widget test for `PosExpenseModal` form interactions and category selection.
2. `test/owner_approvals_test.dart`:
   - Unit test for approvals parsing and status updating.
   - Widget test for `OwnerApprovalScreen` and Dashboard pending approval banner visibility.

---

## Conclusion

The mobile repository `c:\laragon\www\cooca_my_own` is completely prepared and clean for Phase 1 implementation. All necessary platform dependencies (`image_picker`, `camera`, `permission_handler`) and system permissions are in place. The state management and API layers are modular and ready for the new endpoints.
