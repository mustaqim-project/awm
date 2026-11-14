# Handoff Report  explorer_survey_modules_1

**To:** `parent` (`73d9d035-59a2-45d3-a207-90bc9a5adcc4`)  
**From:** `explorer_survey_modules_1` (Read-Only Specification Investigator & Auditor)  
**Date:** 2026-11-03  
**Artifacts Generated:**  
- Full Analysis Report: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_modules_1\analysis.md`

---

## 1. Observation

Direct code observations from inspecting the codebase:

1. **POS Catalog Dummy Fallback**:
   - In `C:\laragon\www\cooca_my_own\lib\state\merchant_state.dart` lines 1231–1576:
     ```dart
     List<ProductItem> _getDefaultStarterProducts(String industry) {
       switch (industry.toLowerCase()) {
         case 'workshop':
           return [
             ProductItem(id: 'starter_ws_1', name: 'Oli Mesin Matic 10W-30 0.8L', price: 48000, ...),
             ProductItem(id: 'starter_ws_2', name: 'Jasa Servis Ringan + Tune Up', price: 55000, isService: true, ...),
             ...
     ```
     Invoked at lines 1612–1614:
     ```dart
     if (allProducts.isEmpty) {
       allProducts = _getDefaultStarterProducts(businessType);
     }
     ```
   - Observed behavior: When offline cache is empty, 24+ static products across 7 industry templates are loaded with fake prices and Unsplash image URLs.

2. **Inventory Screen Mock Data & Contaminated Valuation**:
   - In `C:\laragon\www\cooca_my_own\lib\screens\inventory_screen.dart` lines 60–152 and 155–186:
     ```dart
     final List<Map<String, dynamic>> _materials = [
       {'id': 'mat_1', 'name': 'Biji Kopi Arabika Gayo', 'sku': 'RAW-COF-01', ...},
       ...
     ];
     final List<Map<String, dynamic>> _movementLogs = [
       {'id': 'mov_1', 'timestamp': 'Hari ini, 08:30', ...},
       ...
     ];
     ```
   - At lines 281–284:
     ```dart
     double _calculateTotalValuation() {
       return _materials.fold(0.0, (acc, item) {
         return acc + ((item['stock'] as num) * (item['cost'] as num));
       });
     }
     ```
   - Observed behavior: Total inventory valuation displayed in the UI is computed by multiplying stock by cost of the hardcoded `_materials` array, completely bypassing live inventory balances.

3. **Dashboard Static Switch & Unsplash Profile**:
   - In `C:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart` lines 1647–1656:
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
   - At line 246:
     ```dart
     backgroundImage: const NetworkImage('https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&auto=format&fit=crop&q=80')
     ```
   - Observed behavior: Hourly sales chart uses static hardcoded heights, and avatar uses a static stock photo.

4. **Endpoint Mismatch in Courier Dispatch & Payment Verification**:
   - In `C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` line 703:
     `final response = await _api.post('/api/v1/pos/orders/$orderId/dispatch-courier', body: body);`
   - In `C:\laragon\www\cooca_core\routes\api.php` line 1520:
     `Route::post('/{order}/request-pickup', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'requestPickup']);` under prefix `commerce/orders`.
   - Observed behavior: Mobile attempts to post to a nonexistent `/api/v1/pos/orders/...` route, resulting in 404 on live production.

5. **Simulated Settings & Profile Update**:
   - In `C:\laragon\www\cooca_my_own\lib\screens\settings\edit_user_profile_modal.dart` lines 63–71:
     ```dart
     widget.state.updateCurrentUserProfile(name: newName, phone: _phoneController.text.trim());
     await Future.delayed(const Duration(milliseconds: 500));
     ```
   - In `C:\laragon\www\cooca_my_own\lib\screens\settings\system_settings_screen.dart` lines 60–69:
     ```dart
     ScaffoldMessenger.of(context).showSnackBar(
       const SnackBar(content: Text('Cache memori lokal berhasil dibersihkan.')),
     );
     ```
   - Observed behavior: Neither cache eviction nor user profile network updates actually occur.

6. **Android Production Build Vulnerabilities**:
   - In `C:\laragon\www\cooca_my_own\android\app\build.gradle.kts` lines 34–38:
     ```kotlin
     buildTypes {
         release {
             signingConfig = signingConfigs.getByName("debug")
         }
     }
     ```
   - In `C:\laragon\www\cooca_my_own\android\app\src\main\AndroidManifest.xml` line 28:
     `android:usesCleartextTraffic="true"`
   - In `C:\laragon\www\cooca_my_own\pubspec.yaml`:
     `cached_network_image` is absent.

---

## 2. Logic Chain

1. From Observation 1, `_loadOfflineCachedCatalog()` in `merchant_state.dart` loads static dummy products whenever `allProducts` is empty. Because fresh installs start with an empty cache, any network failure or first-time offline open will immediately display fake products to the user.
2. From Observation 2, `_calculateTotalValuation()` in `inventory_screen.dart` strictly folds over the static `_materials` list. Therefore, regardless of what products or stock levels exist on the backend, the inventory screen displays an incorrect hardcoded asset total.
3. From Observation 3, the hourly trend widget in `dashboard_screen.dart` does not read `state.hourlyTrend`, rendering the same fake peaks at 12:00 regardless of true business sales activity.
4. From Observation 4, `pos_service.dart` calls `/api/v1/pos/orders/$orderId/dispatch-courier`, while backend `routes/api.php` line 1520 exposes `POST /api/v1/commerce/orders/{order}/request-pickup`. As a consequence, courier dispatch requests in production fail with route not found.
5. From Observation 5, profile modifications in `edit_user_profile_modal.dart` only alter local memory variables and clear cache does not invoke any storage eviction. These actions give a false illusion of persistence.
6. From Observation 6, configuring `buildTypes.release` with the debug signing key produces release binaries that are insecure, rejectable by Google Play, and tamper-vulnerable. Allowing `usesCleartextTraffic="true"` leaves merchant API tokens exposed to unencrypted packet sniffing.

---

## 3. Caveats

- **External Hardware Devices**: Bluetooth thermal receipt printing and cash drawer kick mechanisms in `pos_terminals_screen.dart` require physical ESC/POS hardware to verify timing and baud rates.
- **Biometric Hardware**: In-app camera selfie capture and GPS geofencing verification require physical device testing on Android hardware (emulators may spoof GPS coordinates).
- **No Source Code Modified**: In strict compliance with the assigned mission, this audit was 100% READ-ONLY. No code changes have been applied to `cooca_my_own` or `cooca_core`.

---

## 4. Conclusion

The mobile application in `cooca_my_own` has a well-structured Apple HIG UI architecture and robust permission checks via Spatie mapping. However, it suffers from:
1. **Fallback Mock Pollution**: Hardcoded starter products, mock inventory lists, static dashboard graphs, and fake employee fallback structures that deceive the user during network hiccups.
2. **API Route Mismatches**: Key transactional features (courier dispatch, payment verification, account recovery) targeting nonexistent endpoints instead of the implemented backend routes in `cooca_core`.
3. **Android Security & Build Flaws**: Release signing using the debug keystore, cleartext HTTP enabled in production, missing R8 obfuscation, and missing image caching.

All findings have been fully documented with exact line numbers, code quotes, and live backend endpoint mappings in `analysis.md`.

---

## 5. Verification Method

To independently verify these findings:

1. **Verify Starter Products Mock**:
   Inspect `C:\laragon\www\cooca_my_own\lib\state\merchant_state.dart` at line 1231 and verify `_getDefaultStarterProducts`.
2. **Verify Inventory Hardcoded Data**:
   Inspect `C:\laragon\www\cooca_my_own\lib\screens\inventory_screen.dart` at line 60 (`_materials`) and line 281 (`_calculateTotalValuation`).
3. **Verify Dashboard Static Hourly Switch**:
   Inspect `C:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart` at line 1647.
4. **Verify Endpoint Route Mismatch**:
   Compare `C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` line 703 (`/api/v1/pos/orders/$orderId/dispatch-courier`) with `C:\laragon\www\cooca_core\routes\api.php` line 1520 (`POST /api/v1/commerce/orders/{order}/request-pickup`).
5. **Verify Android Build Configuration**:
   Inspect `C:\laragon\www\cooca_my_own\android\app\build.gradle.kts` line 36 (`signingConfigs.getByName("debug")`) and `C:\laragon\www\cooca_my_own\android\app\src\main\AndroidManifest.xml` line 28 (`android:usesCleartextTraffic="true"`).
