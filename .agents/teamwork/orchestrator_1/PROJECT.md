# Project: Comprehensive Mobile Application Audit & OpenShift Bug Remediation

## Architecture
- **Mobile Target**: Flutter application (`C:\laragon\www\cooca_my_own`)
- **Backend Reference**: Laravel 11 Multi-Tenant ERP (`C:\laragon\www\cooca_core`)
- **Production Single Source of Truth**: `https://cooca.id`
- **Data Flow & Security Architecture**:
  - Flutter Mobile communicates via `ApiClient` (Bearer token, `X-Business-Id`, JSON headers) to `https://cooca.id` / `cooca_core`.
  - Android native layer (`MainActivity.kt`) communicates with Flutter via MethodChannel `com.cooca.myown/security` to transmit device security telemetry (Developer Options, ADB, Mock Provider flag, Root status).
  - Shift lifecycle managed via `PosShiftController` & `PosShiftService` with strict cashier isolation and multi-register support.
  - Attendance check-in validated by `AttendanceApiController` & `AttendanceService` combining Haversine geofencing, facial biometrics, and client/server mock location rejection.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | OpenShift Error Parsing Fix | Eliminate `response.containsKey('data')` trap in `ApiClient` and `PosService` so HTTP 4xx/5xx errors are never treated as success. | M1 | Survey R1 |
| 2 | Shift Cache Dropout Protection | Prevent purging of `activeShift` in `LocalStorageService` on network errors or offline launches. | M1 | Survey R1 |
| 3 | Backend Shift Cashier Isolation | Fix `PosShiftService::getActiveShift()` to prevent cashiers from hijacking other users' open shifts or closing another cashier's drawer. | M1 | Survey R1 |
| 4 | Terminal / POS Register Support | Support `pos_register_id` in mobile shift opening and backend concurrency check. | M1 | Survey R1 |
| 5 | OpenShift Modal UI State & Error Handling | Keep modal open on validation error with red banner; dismiss and navigate to POS only on verified success. | M1 | Survey R1 |
| 6 | Smart Float & Denominations Breakdown | Auto-suggest starting cash float from previous shift actual closing balance and add denominations counter. | M1 | Survey R1 |
| 7 | Android Native Security MethodChannel | Implement `checkDeviceSecurity()` in `MainActivity.kt` checking Developer Options, ADB, Mock Provider flag, and Root status. | M2 | Survey R2 |
| 8 | 4-Tier Risk-Proportionate Attendance Policy | Level 1: Developer Options warning banner; Level 3: Strict Fake GPS / Mock Location block with user remediation steps. | M2 | Survey R2 |
| 9 | Pre-Attendance Submission Verification | Validate location permission, GPS enabled, accuracy threshold (<250m), coordinate jitter check, and geofence distance. | M2 | Survey R2 |
| 10 | Backend Attendance Security Telemetry | Accept and validate `is_mock`, `device_id`, `is_dev_mode`, and security signals in `AttendanceApiController` & `AttendanceService`. | M2 | Survey R2 |
| 11 | Play Integrity Graceful Degradation | Support Play Integrity token evaluation with seamless fallback to native signals if credentials/GMS are absent locally. | M2 | Survey R2 |
| 12 | Eliminate POS Starter Products Mock | Remove `_getDefaultStarterProducts()` (24+ dummy products across 7 sectors); enforce real API catalog or empty state. | M3 | Survey R3 |
| 13 | Eliminate Inventory Mock & Fake Valuation | Remove static `_materials` and `_movementLogs`; connect to live inventory API endpoints and calculate real valuation. | M3 | Survey R3 |
| 14 | Fix Dashboard Hourly Sales Chart & Avatars | Bind `state.hourlyTrend` from live API pulse instead of hardcoded switch statement; remove static Unsplash avatars. | M3 | Survey R3 |
| 15 | Live Settings & Profile Persistence | Connect `edit_user_profile_modal.dart` to `PUT /api/v1/profile` and implement real SharedPreferences / cache clearance. | M3 | Survey R3 |
| 16 | Fix API Route Mismatches | Align courier dispatch (`commerce/orders/{order}/request-pickup`), payment verification, and account recovery routes with backend. | M3 | Survey R3 |
| 17 | Purge Auth & HRM Fallback Mocks | Remove fake business fallback (`Kopi Nusantara Mandiri`) and fake portal employee fallback (`usr-local`). | M3 | Survey R3 |
| 18 | Role, Permission & Multi-Industry Gating | Enforce backend Spatie permissions and verify zero cross-industry feature leakage. | M3 | Survey R3 |
| 19 | Android Release Build & Non-Debuggable Config | Set `debuggable = false`, remove debug keystore signing in release, enable R8 minification/shrinking with ProGuard rules. | M4 | Survey R4 |
| 20 | HTTPS Enforcement & Cleartext Removal | Remove `android:usesCleartextTraffic="true"` from production `AndroidManifest.xml`. | M4 | Survey R4 |
| 21 | Mobile Performance & Image Caching | Integrate `cached_network_image`, prevent memory leaks, ensure API request deduplication and offline resilience. | M4 | Survey R4 |
| 22 | Automated Testing Suite | Execute `flutter analyze`, existing unit/widget/integration tests, and newly authored tests for shifts, security, and modules. | M5 | Survey R5 |
| 23 | Audit Documentation Deliverables | Author the 5 mandatory audit markdown reports with structured finding records. | M5 | Survey R5 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | OpenShift Lifecycle Remediation & Hardening | Features 1-6 (ApiClient fix, PosService parsing, cache protection, backend isolation, modal UI states, smart float) | none | IN_PROGRESS (worker_openshift_m1) |
| M2 | Android Security & Fake GPS Attendance | Features 7-11 (MainActivity MethodChannel, 4-tier policy, pre-attendance checks, backend telemetry validation) | none | IN_PROGRESS (worker_security_m2) |
| M3 | Comprehensive Mobile Feature Audit & Dummy Data Elimination | Features 12-18 (Purge all mocks in POS catalog, inventory, dashboard, settings, auth, HRM, fix route mismatches) | M1 | PLANNED |
| M4 | Android Production Build Configuration & Performance | Features 19-21 (Non-debuggable release, signing config, HTTPS enforcement, ProGuard/R8, image caching) | M1, M2 | PLANNED |
| M5 | Verification, Automated Testing & Audit Reports | Features 22-23 (`flutter analyze`, test execution, 5 audit markdown reports, Forensic Integrity Audit) | M1, M2, M3, M4 | PLANNED |

## Interface Contracts

### 1. Shift Management Contract
- `POST /api/v1/pos/shifts/open`:
  - Request: `{"opening_cash": double, "notes": string?, "location_id": uuid?, "pos_register_id": uuid?, "opening_denominations": map?}`
  - Response (200): `{"success": true, "data": PosShiftObject, "message": "Shift kasir berhasil dibuka."}`
  - Response (422): `{"success": false, "message": "The selected location id is invalid.", "errors": {...}}`
- `GET /api/v1/pos/shifts/active`:
  - Query: `?location_id=uuid`
  - Response (200, shift open): `{"success": true, "active_shift": PosShiftObject, "summary": {...}}`
  - Response (200, no open shift): `{"success": true, "active_shift": null}`
  - Error (4xx/5xx): `{"success": false, "message": string}`

### 2. Device Security & Attendance Contract
- Native MethodChannel: `com.cooca.myown/security` -> `checkDeviceSecurity()`
  - Returns: `{"is_mock_location": bool, "is_dev_mode": bool, "is_adb_enabled": bool, "is_rooted": bool, "is_emulator": bool, "device_id": string}`
- `POST /api/v1/hrm/attendance/check-in` & `check-out`:
  - Request: `{"latitude": double, "longitude": double, "accuracy": double, "is_mock": bool, "device_id": string, "is_dev_mode": bool, "is_rooted": bool, "play_integrity_token": string?, "address": string?, "notes": string?, "photo": base64?}`
  - Response (200): `{"success": true, "message": "Presensi masuk berhasil dicatat.", "data": AttendanceRecord}`
  - Response (403/422 on Mock Location): `{"success": false, "message": "Presensi ditolak: Terdeteksi manipulasi lokasi (Mock Location / Fake GPS)."}`

### 3. Commerce Pickup Order Contract
- `POST /api/v1/commerce/orders/{order}/request-pickup`:
  - Request: `{"courier_code": string?, "notes": string?}`
  - Response (200): `{"success": true, "message": "Permintaan penjemputan kurir berhasil dibuat.", "data": {...}}`

## Code Layout
- Mobile Target: `C:\laragon\www\cooca_my_own`
  - `lib/core/services/api_client.dart`
  - `lib/core/services/pos_service.dart`
  - `lib/core/services/attendance_service.dart`
  - `lib/core/services/location_biometric_service.dart`
  - `lib/screens/pos/pos_shift_modal.dart`
  - `lib/screens/attendance_screen.dart`
  - `lib/screens/inventory_screen.dart`
  - `lib/screens/dashboard_screen.dart`
  - `lib/screens/settings/*`
  - `lib/state/merchant_state.dart`
  - `android/app/src/main/kotlin/com/cooca/myown/cooca_my_own/MainActivity.kt`
  - `android/app/build.gradle.kts`
  - `android/app/src/main/AndroidManifest.xml`
- Backend Reference: `C:\laragon\www\cooca_core`
  - `app/Domain/Pos/PosShiftService.php`
  - `app/Http/Controllers/Api/V1/Pos/PosShiftController.php`
  - `app/Http/Controllers/Api/V1/Hrm/AttendanceApiController.php`
  - `app/Domain/HRM/AttendanceService.php`
