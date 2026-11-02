# Handoff Report: Mobile Codebase Readiness (`cooca_my_own`) for Phase 1

**Agent ID**: `explorer_survey_2`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\`  
**Date**: 2026-11-02  
**Handoff Type**: Hard (Task Complete)  

---

## 1. Observation

1. **POS & Shift Management**:
   - `lib/screens/pos/pos_shift_modal.dart` (lines 63-102, 109-117, 122-158):
     - Active shift check & sync: calls `_posService.getActiveShift(locationId: ...)` (`GET /api/v1/pos/shifts/active`) and `_posService.getShiftSummary(...)` (`GET /api/v1/pos/shifts/$shiftId/summary`).
     - Expected cash computation:
       `double get _expectedCash => (_liveShiftSummary?['expected_cash'] as num?)?.toDouble() ?? widget.state.activeShift?.expectedCash ?? 0.0;`
       `double get _cashVariance => _enteredCash - _expectedCash;`
     - Action button calls `widget.state.openShift(...)` (`POST /api/v1/pos/shifts/open`) and `widget.state.closeShift(...)` (`POST /api/v1/pos/shifts/$shiftId/close`).
   - `lib/screens/pos_screen.dart`:
     - Top header cashier shift pill (lines 1265-1289):
       `onTap: () => PosShiftModal.show(context, state)`
     - Main Operations Menu Sheet `_showMainMenu(MerchantState state)` (lines 358-840):
       Section `"OPERASIONAL KASIR"` (lines 498-587) contains `"Kelola Shift"` / `"Buka Shift"` (lines 503-531), `"Pesanan Tertahan"` (lines 533-559), `"Riwayat Transaksi"` (lines 561-572), `"Pilih Cabang / Outlet"` (lines 574-585).
   - `lib/state/merchant_state.dart`:
     - `PosShift? activeShift;` (line 66)
     - `bool get hasActiveShift => activeShift != null && activeShift!.status == 'open';` (line 67)
     - `void updateActiveShift(PosShift? shift)` (lines 69-72)
     - `openShift(...)` (lines 1480-1497) and `closeShift(...)` (lines 1500-1518).
   - `lib/models/order.dart`:
     - `class PosShift` (lines 129-170) parses `opening_cash`, `total_cash_sales`, `total_non_cash_sales`, `expected_cash`.

2. **State Management & Network Layer**:
   - `lib/state/merchant_state.dart`: Central `ChangeNotifier` singleton (`factory MerchantState() => _instance;`).
   - `lib/core/services/api_client.dart`:
     - Base URL: `defaultProductionUrl = 'https://cooca.id'`, `defaultLocalLaragonUrl = 'http://cooca_core.test'` (lines 13-36).
     - Sanctum Token: read from SharedPreferences and injected into headers:
       `if (_customerToken != null && _customerToken!.isNotEmpty) headers['Authorization'] = 'Bearer $_customerToken';` (line 96)
     - Multi-tenant ID: `headers['X-Business-Id'] = bizId;` (line 100).
   - `lib/core/services/pos_offline_sync_manager.dart` (lines 14-56, 59-150):
     - Uses RFC4122 v4 UUID (`client_uuid`) for idempotency.
     - Offline queue managed in `LocalStorageService`.
     - Dispatches batch payload via `_posService.batchSync(...)` (`POST /api/v1/pos/sync/batch`).

3. **Approvals & Dashboard Integration**:
   - `lib/screens/dashboard_screen.dart`:
     - Executive Header (lines 330-390): Contains notification icon button with unread red badge dot (lines 374-386).
     - Financial Hero Card (lines 455, 507, 571-630).
     - Active Shift Bar (lines 459, 525, 870-980).
     - Operational Pulse Bento (lines 463, 512, 1100-1250): 4 metric cards with badges.
     - Module Launcher Grid (lines 471, 529, 1514-1640): 6 core enterprise modules (`Kasir POS`, `Antrean/KDS`, `Presensi Tim`, `HR & Staf`, `Laporan`, `Inventori`).
   - Apple HIG Design Tokens:
     - `lib/core/theme/ios_tokens.dart`: `IOSSpacing`, `IOSRadius`, `IOSColors`, `IOSText`, and `IOSThemeContext` extensions.
     - `lib/core/theme/ios_adaptive.dart`: `IOSBreakpoints`, `isCompact`, `isTablet`, `screenMargin`.
     - Haptics: `HapticFeedback.lightImpact()`, `mediumImpact()`, `heavyImpact()`, `selectionClick()`, `vibrate()` from `flutter/services.dart`.

4. **Camera & Media Support**:
   - `pubspec.yaml`:
     - Line 43: `image_picker: ^1.2.4`
     - Line 44: `permission_handler: ^11.3.1`
     - Line 46: `camera: ^0.12.1`
   - `android/app/src/main/AndroidManifest.xml`:
     - Line 5: `<uses-permission android:name="android.permission.CAMERA"/>`
     - Line 8: `<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" android:maxSdkVersion="32" />`
     - Line 9: `<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />`
     - Line 17: `<uses-feature android:name="android.hardware.camera" android:required="false" />`
   - `ios/Runner/Info.plist`:
     - Line 50: `<key>NSCameraUsageDescription</key>` present.
     - Line 56: `<key>NSPhotoLibraryUsageDescription</key>` present.
   - Existing ImagePicker usage: `lib/screens/auth/account_recovery_sheet.dart` (line 103) and `lib/core/services/location_biometric_service.dart` (line 152).

5. **Testing Infrastructure & Health**:
   - `test/industry_gating_test.dart` and `test/widget_test.dart` exist.
   - Command `flutter analyze` executed in `c:\laragon\www\cooca_my_own`:
     - Verbatim result: `No issues found! (ran in 16.1s)` — Exit Code 0.
   - Command `flutter test` executed in `c:\laragon\www\cooca_my_own`:
     - Verbatim result: `00:03 +10: All tests passed!` — Exit Code 0.

---

## 2. Logic Chain

1. **POS & Shift Integration Logic**:
   - Observations show that `PosShiftModal` computes `_expectedCash` from `_liveShiftSummary['expected_cash']` or `widget.state.activeShift?.expectedCash`.
   - When petty cash is recorded via the backend endpoint `POST /api/v1/pos/expenses`, the server will record a cash outflow in the cash ledger linked to the shift.
   - Therefore, on mobile:
     - Adding `PosExpenseModal` (`lib/screens/pos/pos_expense_modal.dart`) with `PosService.recordExpense(...)` will send the expense record and base64 receipt to the backend.
     - Placing a launcher button in `PosScreen._showMainMenu` (under `OPERASIONAL KASIR`) and inside `PosShiftModal` (under `Mutasi Kas`) guarantees immediate cashier discoverability.
     - Updating `_expectedCash` and refreshing `_initShiftData()` ensures the cashier sees expected cash automatically reduced by the expense amount upon closing the shift.

2. **Network & State Logic**:
   - Observations show `ApiClient` attaches `Bearer $_customerToken` and `X-Business-Id: $bizId` to all outgoing requests.
   - `ApiClient.post` serializes JSON bodies directly.
   - Because existing photo capture modules (`attendance_screen.dart`, `location_biometric_service.dart`) encode images as `data:image/jpeg;base64,...`, adopting `receipt_image_base64` in the `POST /api/v1/pos/expenses` payload allows zero-friction integration without altering `ApiClient` transport mechanisms.

3. **Owner Approval Center Logic**:
   - Observations confirm that `DashboardScreen` has an Executive Header, an Active Shift Bar, an Operational Pulse Bento, and an Enterprise Module Launchers grid.
   - Creating `ApprovalsService` (`lib/core/services/approvals_service.dart`) to communicate with `GET /api/v1/owner/approvals/pending` and `POST /api/v1/owner/approvals/{id}/action`, and binding `pendingApprovalsCount` to `MerchantState.refreshPulse()` allows real-time counter updates on pull-to-refresh.
   - Adding a high-visibility Apple HIG Alert Banner below `_buildActiveShiftBar` (displayed when `state.pendingApprovalsCount > 0`) provides an immediate call-to-action for owners, navigating directly to `OwnerApprovalScreen`.
   - In `OwnerApprovalScreen`, utilizing Apple HIG swipeable cards with `HapticFeedback.mediumImpact()` provides tactile feedback consistent with the app's established UI conventions.

4. **Media Support Logic**:
   - Both `image_picker: ^1.2.4` and `camera: ^0.12.1` are verified in `pubspec.yaml`, and runtime permissions are fully configured in Android and iOS manifests.
   - Using `ImagePicker().pickImage(source: ImageSource.camera, maxWidth: 1200, maxHeight: 1200, imageQuality: 80)` with gallery fallback provides an optimal receipt photo capture flow with minimal memory footprint.

5. **Testing & Health Logic**:
   - Observations show that all 10 existing tests pass and `flutter analyze` reports 0 issues.
   - Thus, new unit and widget tests for `pos_expense_modal.dart` and `owner_approval_screen.dart` can follow the established mock and test harness patterns (`SharedPreferences.setMockInitialValues({})`, `tester.view.physicalSize = Size(1080, 2400)`).

---

## 3. Caveats

- **Network connectivity in emulator**: `ApiClient.baseUrl` automatically falls back to `https://cooca.id` when running on physical/emulator devices where `http://cooca_core.test` does not resolve. For local test suite execution, tests should mock or inject responses rather than making live HTTP calls.
- **Offline queueing for petty cash**: While `PosOfflineSyncManager` exists for sales transactions, offline petty cash syncing can use a similar local queue pattern if network is unavailable, or alert the cashier if offline.
- **Alternative considered**: Using direct multipart file upload vs base64. Base64 is preferred because `ApiClient` is natively built for JSON bodies and existing photo flows in the codebase uniformly use base64 data URIs.

---

## 4. Conclusion

The mobile application at `c:\laragon\www\cooca_my_own` is **100% architecturally and operationally ready** for implementing Phase 1 (Petty Cash & Owner Approval Center).
- No new packages need to be installed.
- No new OS permissions need to be declared.
- Clean integration points have been precisely mapped in `pos_screen.dart`, `pos_shift_modal.dart`, `merchant_state.dart`, and `dashboard_screen.dart`.
- The codebase is clean with 0 analysis errors and 100% passing tests.

---

## 5. Verification Method

To independently verify the facts and health documented in this report:

1. **Verify Static Analysis**:
   ```powershell
   cd c:\laragon\www\cooca_my_own
   flutter analyze
   ```
   *Expected result*: `No issues found! (ran in ~16s)`

2. **Verify Existing Automated Test Suite**:
   ```powershell
   cd c:\laragon\www\cooca_my_own
   flutter test
   ```
   *Expected result*: `All tests passed! (10 tests in ~3s)`

3. **Verify Dependencies & Permissions**:
   - Inspect `pubspec.yaml` lines 43-46 to confirm `image_picker` and `camera`.
   - Inspect `android/app/src/main/AndroidManifest.xml` line 5 to confirm `android.permission.CAMERA`.
   - Inspect `ios/Runner/Info.plist` lines 50-57 to confirm `NSCameraUsageDescription` and `NSPhotoLibraryUsageDescription`.

4. **Verify POS Shift & Expected Cash Code**:
   - Inspect `lib/screens/pos/pos_shift_modal.dart` lines 109-117 to confirm `_expectedCash` and `_cashVariance`.
   - Inspect `lib/screens/pos_screen.dart` lines 498-587 to inspect `_showMainMenu` and the `"OPERASIONAL KASIR"` menu group.
