# Comprehensive Deep-Dive Investigation: Android Security & Fake GPS Prevention for Attendance (R2)

**Author:** `explorer_survey_security_1` (Teamwork Explorer)  
**Date:** 2026-11-03  
**Target Mobile Repo:** `C:\laragon\www\cooca_my_own`  
**Target Backend Repo:** `C:\laragon\www\cooca_core`  
**Live Production API:** `https://cooca.id`  
**Scope:** Stage 1 Critical — Requirement R2: Android Security & Fake GPS Prevention for Attendance

---

## Executive Summary

This investigation delivers an end-to-end audit of attendance processing, location tracking, device security, and anti-spoofing across both the Flutter mobile client (`cooca_my_own`) and the Laravel backend (`cooca_core`). 

### Key Findings at a Glance
1. **Client-Side Mock Detection is Present but Vulnerable**:
   - `cooca_my_own` currently relies solely on `geolocator: ^14.1.1`'s `position.isMocked` property in `location_biometric_service.dart`.
   - No multi-sample coordinate jitter checks, altitude verification, bearing/speed consistency, or velocity/teleportation checks exist.
   - If an attacker bypasses the client or uses root-level hooks (LSPosed, Smali Patcher, Magisk), `isMocked` returns `false`.
2. **Zero Developer Options, USB Debugging, or Root Detection**:
   - Neither `MainActivity.kt` nor any Flutter service detects `DEVELOPMENT_SETTINGS_ENABLED`, `ADB_ENABLED`, `su` binary, Magisk, or root status.
   - `MainActivity.kt` is a blank 6-line `FlutterActivity()` without any native MethodChannels.
3. **Zero Play Integrity API Implementation**:
   - No Play Integrity token request or verification exists in either mobile or backend.
   - No graceful degradation framework is in place for devices lacking Google Play Services (GMS) or when cloud credentials are unconfigured locally.
4. **Backend Contracts Completely Blind to Device Integrity & Mock Flags**:
   - In `cooca_core` (`AttendanceApiController.php`), the backend validation contract only checks `latitude`, `longitude`, `accuracy`, `address`, `notes`, `photo`, `face_data`, and `location_id`.
   - The backend does **not** accept, validate, or record `is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, or `integrity_token`.
   - If an attacker calls `POST /api/v1/attendance/check-in` directly via HTTP (curl/Postman) with spoofed coordinates and normal accuracy (`accuracy: 15.0`), the backend accepts the attendance without any mock detection!
   - The database table `attendances` has no columns for storing `is_mock`, `accuracy`, `device_id`, or security audit telemetry.

---

## Part 1: Mobile Codebase Inspection (`C:\laragon\www\cooca_my_own`)

### 1.1 Attendance Screen (`lib/screens/attendance_screen.dart`)
- **File Location**: `C:\laragon\www\cooca_my_own\lib\screens\attendance_screen.dart` (2,373 lines).
- **Observed Workflow**:
  1. *Location Check*: `_refreshDeviceLocation()` (lines 159–207) queries `_sensorService.getCurrentDeviceLocation()`.
  2. *Geofence Verification*: Lines 180–196 calculate distance to the configured office location:
     ```dart
     final dist = _sensorService.calculateDistanceMeters(
       result.latitude!,
       result.longitude!,
       officeLat,
       officeLng,
     );
     ```
  3. *Clock-In / Clock-Out Trigger*: `_triggerAttendanceAction({required bool isClockOut})` (lines 210–325):
     - Line 213: Enforces face template enrollment (`_employee?['face_registered'] == true`).
     - Line 224: Checks permissions via `DevicePermissionService().ensureAttendancePermissions()`.
     - Lines 240–249: Rejects if `!locResult.success`. If `locResult.isMocked == true`, triggers `_showSecurityAlert()`:
       ```dart
       if (locResult.isMocked) {
         _showSecurityAlert(
           title: 'Fake GPS / Mock Location Terdeteksi',
           message: 'Sistem keamanan COOCA mendeteksi adanya aplikasi lokasi palsu pada perangkat Anda. Nonaktifkan developer mock location sebelum melakukan absensi.',
         );
         return;
       }
       ```
     - Line 253–282: Validates geofence; if outside allowed radius, prompts `_showExternalAssignmentDialog()`.
     - Lines 287–314: Opens `InAppBiometricCameraSheet.show(...)` and invokes `_attendanceService.clockIn()` or `clockOut()`.
- **Observed Defect**:
  - The attendance action stops locally if `locResult.isMocked` is true, but **never sends security audit telemetry to the server**.
  - No check exists for Developer Options or USB Debugging prior to attendance submission.

### 1.2 Sensor & Location Service (`lib/core/services/location_biometric_service.dart`)
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\location_biometric_service.dart` (214 lines).
- **Observed Implementation**:
  - Uses `Geolocator.getCurrentPosition(locationSettings: LocationSettings(accuracy: LocationAccuracy.high, timeLimit: Duration(seconds: 10)))` (lines 77–82).
  - Fallback: Lines 84–88 fallback to `Geolocator.getLastKnownPosition()`.
  - Mock check (lines 99–108):
    ```dart
    if (position.isMocked) {
      return DeviceLocationResult(
        success: false,
        latitude: position.latitude,
        longitude: position.longitude,
        accuracy: position.accuracy,
        isMocked: true,
        errorMessage: 'Peringatan Keamanan: Terdeteksi penggunaan Fake GPS / Mock Location pada perangkat Anda...',
      );
    }
    ```
  - Accuracy threshold (lines 111–119):
    ```dart
    static const double maxAcceptableAccuracyMeters = 250.0;
    if (position.accuracy > maxAcceptableAccuracyMeters) {
      return DeviceLocationResult(
        success: false,
        accuracy: position.accuracy,
        errorMessage: 'Akurasi sinyal GPS terlalu rendah (${position.accuracy.toStringAsFixed(1)}m > 250m)...',
      );
    }
    ```
  - Haversine distance formula implemented in lines 199–212 matching backend logic.
- **Observed Gaps**:
  - `getLastKnownPosition()` can return an outdated or replayed location from when a mock location app was active earlier.
  - Zero anomaly checks on `position.altitude`, `position.speed`, `position.heading`, or consecutive coordinate delta.
  - No check on `position.accuracy <= 0.0` or suspiciously constant accuracy (e.g. exactly 1.0m or 5.0m).

### 1.3 Permissions Handling (`lib/core/services/device_permission_service.dart`)
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\device_permission_service.dart` (527 lines).
- **Observed Capabilities**:
  - Robust handling of `Permission.camera`, `Permission.locationWhenInUse`, `Geolocator.isLocationServiceEnabled()`.
  - Batch requests via `ensureAttendancePermissions()` and native Apple HIG Cupertino alert dialogs (`showSettingsRedirectModal`, `showEnableGpsDialog`).
- **Gaps**:
  - Does not check `AppOpsManager` or settings for mock location or developer options.

### 1.4 API Client & Attendance Network Service
- **File Locations**:
  - `C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart` (lines 90–140)
  - `C:\laragon\www\cooca_my_own\lib\core\services\api_client.dart`
- **Observed Payload in `clockIn` / `clockOut`**:
  ```dart
  final body = <String, dynamic>{};
  if (latitude != null) body['latitude'] = latitude;
  if (longitude != null) body['longitude'] = longitude;
  if (accuracy != null) body['accuracy'] = accuracy;
  if (address != null) body['address'] = address;
  if (notes != null) body['notes'] = notes;
  if (photo != null) {
    body['photo'] = photo;
    body['face_data'] = photo;
  }
  final response = await _api.post('/api/v1/attendance/check-in', body: body);
  ```
- **Observed Gaps**:
  - **No `is_mock` or `is_mocked` sent**.
  - **No `device_id` / `device_uuid` sent**.
  - **No `device_security_signals` sent** (developer options, adb status, root status).
  - **No `play_integrity_token` sent**.
  - **No timestamp/nonce sent** to prevent replay attacks.

### 1.5 Android Native Configuration & Dependencies
- **`pubspec.yaml`**:
  - Current location package: `geolocator: ^14.1.1`
  - Current permission package: `permission_handler: ^11.3.1`
  - Current camera package: `camera: ^0.12.1`
  - **Missing**: No security, root detection, or Play Integrity plugin installed.
- **`android/app/src/main/AndroidManifest.xml`**:
  - Permissions declared:
    - `<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>`
    - `<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>`
    - `<uses-permission android:name="android.permission.CAMERA"/>`
  - Security Issue: `android:usesCleartextTraffic="true"` is enabled on line 28!
- **`android/app/src/main/kotlin/.../MainActivity.kt`**:
  - Pure template:
    ```kotlin
    package com.cooca.myown.cooca_my_own
    import io.flutter.embedding.android.FlutterActivity
    class MainActivity : FlutterActivity()
    ```
  - **No native MethodChannel exists** to interact with Android security APIs.

---

## Part 2: Fake GPS & Mock Location Detection Deep Dive

### 2.1 Official Android Location APIs
On Android, mock locations are managed through distinct OS APIs across API levels:
1. **API 31+ (Android 12+)**:
   - `android.location.Location.isMock(): Boolean`
   - Official, non-deprecated method. In Android 12+, all mock location providers (whether from test providers, mock apps, or simulated hardware) set this flag directly on the `Location` object.
2. **API 18 to 30 (Android 4.3 to 11)**:
   - `android.location.Location.isFromMockProvider(): Boolean`
   - Checks if the location came from a mock provider registered via `LocationManager.addTestProvider()`.
3. **API < 18 (Legacy Android)**:
   - `Settings.Secure.getString(contentResolver, Settings.Secure.ALLOW_MOCK_LOCATION) != "0"`
   - Deprecated in API 23+ (Marshmallow).

### 2.2 Why `position.isMocked` via Geolocator Fails Against Sophisticated Spoofing
While `geolocator: ^14.1.1` calls `location.isMock()` / `location.isFromMockProvider()` internally, relying on it alone leaves major vulnerabilities:
1. **Smali Patcher & Xposed / LSPosed Modules**:
   - Root-level frameworks hook `android.location.Location.isFromMockProvider()` and `isMock()` directly in the Android runtime (ART), forcing them to return `false`.
2. **Mock Location App Selection in AppOps**:
   - In Android 6.0+, selecting an app in "Select mock location app" grants `AppOpsManager.OPSTR_MOCK_LOCATION` to that app. If this AppOp is granted to any third-party app currently running, mock location is primed even before a coordinate is injected.
3. **Stale Location Cache via `getLastKnownPosition()`**:
   - If GPS signal is weak, `location_biometric_service.dart` falls back to `getLastKnownPosition()`. A cached coordinate from hours or days ago (when a mock provider was momentarily enabled) could be accepted without freshness validation.

### 2.3 Required Multi-Layer Heuristic Anti-Spoofing Detection
To achieve robust Fake GPS detection, the mobile app and backend must implement multi-layered heuristic checks:

| Detection Layer | Mechanism | Indicators of Mock Location / Spoofing |
|---|---|---|
| **Layer 1: OS Flag** | `Location.isMock()` / `isFromMockProvider()` | Returns `true` on standard mock apps. |
| **Layer 2: AppOps Query** | `AppOpsManager` query for `OPSTR_MOCK_LOCATION` | Active mock location provider configured in Developer Options. |
| **Layer 3: Timestamp Freshness** | `location.elapsedRealtimeNanos` vs `SystemClock.elapsedRealtimeNanos()` | If delta $> 30,000\text{ ms}$, coordinate is an injected or cached stale buffer. |
| **Layer 4: Coordinate Micro-Jitter** | Sample 2–3 consecutive GPS readings over 1–2 seconds | Real GPS satellites have atmospheric noise causing natural micro-fluctuations ($\pm 0.000005^\circ$ to $0.00002^\circ$). Mock apps frequently output static, identical coordinates down to the 7th decimal place with $0.000000^\circ$ variance. |
| **Layer 5: Accuracy & Altitude Realism** | Inspect `accuracy`, `altitude`, `verticalAccuracy` | Suspicious if: `accuracy == 0.0`, `accuracy == 1.0` constantly, `altitude == 0.000` with 0 vertical accuracy, or accuracy $> 250\text{m}$. |
| **Layer 6: Velocity / Teleportation Filter** | $\text{Distance}(\text{point}_1, \text{point}_2) / \Delta t$ | Speed $> 150\text{ km/h}$ ($> 41.6\text{ m/s}$) for urban staff indicates GPS teleportation. |

---

## Part 3: Developer Options and USB Debugging Detection

### 3.1 Android Native Setting Indicators
- **Developer Options Enabled**:
  ```kotlin
  val isDevMode = Settings.Global.getInt(
      context.contentResolver,
      Settings.Global.DEVELOPMENT_SETTINGS_ENABLED,
      0
  ) != 0
  ```
- **USB Debugging (ADB) Enabled**:
  ```kotlin
  val isAdbEnabled = Settings.Global.getInt(
      context.contentResolver,
      Settings.Global.ADB_ENABLED,
      0
  ) != 0
  ```

### 3.2 Analysis of False Positive Risk (Safe Devices vs Fraud)
- **The Core Problem**: Millions of ordinary Android users, engineers, and tech-savvy staff enable Developer Options for completely legitimate reasons:
  - Adjusting UI animation scale (0.5x) for a faster UI experience.
  - Enabling Bluetooth audio codecs (LDAC/aptX) or force dark mode.
  - Testing other apps or working as part-time developers.
- **Critical Policy**:
  - **Developer Options Enabled $\neq$ Fake GPS Active!**
  - If the application hard-blocks every device that has Developer Options or USB Debugging enabled, merchants will experience massive support tickets, false fraud accusations, and employee lockouts.
  - Requirement R2 specifically demands:
    > *"Detect Developer Options and USB Debugging with a configurable, risk-proportionate policy and clear user messaging so safe devices are NOT falsely blocked as Fake GPS."*

### 3.3 Four-Tier Risk-Proportionate Policy

```
┌────────────────────────────────────────────────────────────────────────┐
│                      DEVICE SECURITY CLASSIFICATION                    │
├─────────────────┬────────────────────────────────┬─────────────────────┤
│ Risk Tier       │ Technical Condition            │ Action & UX Policy  │
├─────────────────┼────────────────────────────────┼─────────────────────┤
│ Level 0: Clean  │ Dev Mode: OFF                  │ Allowed             │
│                 │ Mock Location: OFF             │ Full green badge    │
│                 │ Accuracy: Valid (<250m)        │                     │
├─────────────────┼────────────────────────────────┼─────────────────────┤
│ Level 1: Notice │ Dev Mode: ON                   │ Allowed (Non-Block) │
│ (Safe Device)   │ ADB: ON or OFF                 │ Amber Warning Chip  │
│                 │ Mock App Selected: NONE        │ Non-intrusive alert │
│                 │ `isMocked == false`            │ Audit log recorded  │
├─────────────────┼────────────────────────────────┼─────────────────────┤
│ Level 2: Strict │ Dev Mode: ON                   │ Enterprise Policy   │
│ (High Security) │ ADB: ON (Connected)            │ Configurable        │
│                 │ Merchant policy: Enforce ADB Off│ Warn or Require Off │
├─────────────────┼────────────────────────────────┼─────────────────────┤
│ Level 3: Fraud  │ Mock Location App Selected OR  │ BLOCKED             │
│ (Fake GPS)      │ `Location.isMock() == true` OR │ Clear Step-by-Step  │
│                 │ Anomaly / Spoof detected       │ Remediation Dialog  │
└─────────────────┴────────────────────────────────┴─────────────────────┘
```

#### Remediation Guidance UI for Level 3 (Fake GPS):
When Level 3 is triggered, display a user-friendly Apple HIG / Android Material alert explaining:
> **"Lokasi Palsu (Mock Location) Terdeteksi"**  
> *"Sistem keamanan COOCA mendeteksi fitur lokasi palsu aktif pada perangkat Anda.*  
>  
> *Langkah Penonaktifan:*  
> *1. Buka Pengaturan Perangkat > Opsi Pengembang (Developer Options).*  
> *2. Cari menu 'Pilih aplikasi lokasi palsu' (Select mock location app).*  
> *3. Pilih 'Tidak Ada' (None) atau nonaktifkan aplikasi Fake GPS.*  
> *4. Kembali ke COOCA dan coba presensi kembali."*

---

## Part 4: Play Integrity API and Root Detection

### 4.1 Root Detection Heuristics
Root access compromises the Android trust chain, allowing hooks into GPS and system clocks. Root detection must evaluate:
1. **`su` Binary Existence in Common Paths**:
   - `/system/bin/su`, `/system/xbin/su`, `/sbin/su`, `/system/sd/xbin/su`, `/system/bin/failsafe/su`, `/data/local/su`, `/data/local/bin/su`, `/data/local/xbin/su`.
2. **Root Management Package Presence**:
   - `com.topjohnwu.magisk`, `eu.chainfire.supersu`, `com.koushikdutta.superuser`, `com.noshufou.android.su`, `com.thirdparty.superuser`.
3. **Build Tags / Test-Keys Check**:
   - `android.os.Build.TAGS != null && android.os.Build.TAGS.contains("test-keys")`.
4. **Dangerous Properties**:
   - `ro.debuggable == "1"`, `ro.secure == "0"`.
5. **System Partition Writable**:
   - Attempting to check write access or mount flags on `/system` or `/vendor`.

### 4.2 Play Integrity API Architecture
Google Play Integrity API is the official successor to SafetyNet:
1. **Workflow**:
   - Mobile app requests an integrity token by providing a cryptographic `nonce` (e.g. SHA-256 of `user_id` + `timestamp` + `business_id` + request payload).
   - Google Play Services communicates with Google servers and issues an encrypted token.
   - Mobile app sends the token to the backend API.
   - Backend decrypts and verifies the token using Google API client credentials.
2. **Verdict Fields**:
   - `appRecognitionVerdict`: `PLAY_RECOGNIZED` (app matches official Play Store binary).
   - `deviceRecognitionVerdict`:
     - `MEETS_BASIC_INTEGRITY`: Passes basic checks (emulators/modified runtimes may fail).
     - `MEETS_DEVICE_INTEGRITY`: Hardware-backed device running genuine Google Play Services.
     - `MEETS_STRONG_INTEGRITY`: Hardware keystore backed, locked bootloader, factory OS.

### 4.3 Graceful Degradation Strategy
The application operates in varied environments (local development, QA emulators, private staging, devices without Google Play Services like Huawei HMS, and offline networks). A strict requirement is that missing cloud credentials or absent GMS must **never** break or crash the attendance flow:
- **Client-Side Graceful Handling**:
  - Query `GoogleApiAvailability.getInstance().isGooglePlayServicesAvailable(context)`.
  - If GMS is not available, or Play Integrity token generation throws an exception (e.g. network timeout, unlinked GCP project), catch the error cleanly:
    - Set `play_integrity_token = null`.
    - Set `integrity_status = "unavailable"` or `"degraded"`.
    - Continue the attendance submission with local device security signals (`is_rooted`, `is_dev_mode`, `is_mock`).
- **Backend Graceful Handling**:
  - Config check: `config('services.play_integrity.enabled')` (default `false` in local/staging).
  - If disabled or if `play_integrity_token` is null:
    - Skip Google Cloud token verification.
    - Validate native signals (`is_mock`, `accuracy`, geofence distance, facial liveness).
    - Log audit warning: `"Play Integrity token skipped (degraded mode: local/unconfigured)"`.
  - In production with `PLAY_INTEGRITY_ENABLED=true`: Verify token only if present; if missing on non-GMS device, apply fallback risk evaluation.

---

## Part 5: Backend Attendance Contracts in `cooca_core`

### 5.1 Route & Controller Analysis
- **Routes**: `routes/api.php` lines 1460–1464:
  ```php
  Route::prefix('attendance')->group(function (): void {
      Route::post('/check-in', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkIn']);
      Route::post('/clock-in', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkIn']);
      Route::post('/check-out', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkOut']);
      Route::post('/clock-out', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkOut']);
  });
  ```
- **Controller Validation**: `AttendanceApiController.php` lines 46–55 & 100–108:
  ```php
  $validated = $request->validate([
      'latitude' => ['nullable', 'numeric', 'between:-90,90'],
      'longitude' => ['nullable', 'numeric', 'between:-180,180'],
      'accuracy' => ['nullable', 'numeric', 'min:0'],
      'address' => ['nullable', 'string', 'max:500'],
      'notes' => ['nullable', 'string', 'max:255'],
      'photo' => ['nullable'],
      'face_data' => ['nullable'],
      'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
  ]);
  ```

### 5.2 Domain Service Rules (`app/Domain/HRM/AttendanceService.php`)
- **Accuracy Verification** (lines 66–72):
  ```php
  $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
  if ($accuracy !== null && $accuracy > self::MAX_GPS_ACCURACY_METERS) {
      $maxAcc = (int) self::MAX_GPS_ACCURACY_METERS;
      throw ValidationException::withMessages([
          'gps' => "Akurasi sinyal GPS perangkat Anda terlalu rendah ({$accuracy}m > {$maxAcc}m) atau terdeteksi sinyal simulasi/palsu...",
      ]);
  }
  ```
- **Geofence Haversine Check** (lines 158–172):
  - Calculates distance between `$userLat, $userLng` and `$targetLocation->latitude, $targetLocation->longitude`.
  - Rejects if `$calculatedDistance > $allowedRadius` (unless employee has active WFH/WFA exception or free location mode).
- **Biometric Face Verification** (lines 74–107):
  - Enforces registered template and passes to `FaceVerificationService::verifyFace()`.

### 5.3 Database Schema Analysis (`attendances` table)
- **Migration**: `database/migrations/2026_09_21_000002_create_hrm_attendances_and_corrections_tables.php`
- **Existing Columns**:
  - `clock_in_at`, `clock_in_lat`, `clock_in_lng`, `clock_in_distance_meters`, `clock_in_address`, `clock_in_status`, `clock_in_notes`
  - `clock_out_at`, `clock_out_lat`, `clock_out_lng`, `clock_out_distance_meters`, `clock_out_address`, `clock_out_status`, `clock_out_notes`
  - `work_duration_minutes`, `late_minutes`, `status`, `is_geofenced`, `face_verified`, `face_similarity_score`, `exception_policy_id`
- **Missing Columns in `attendances` table**:
  - `device_id` (`varchar(100)`)
  - `is_mock` (`boolean`, default `false`)
  - `clock_in_accuracy` (`decimal(6,2)`)
  - `clock_out_accuracy` (`decimal(6,2)`)
  - `security_flags` (`json` for storing `{is_dev_mode, is_adb, is_rooted, play_integrity_status}`)

### 5.4 Gap Analysis Matrix: Mobile vs Backend

| Security Attribute | Mobile (`cooca_my_own`) Status | Backend (`cooca_core`) Status | Security Vulnerability / Architectural Gap |
|---|---|---|---|
| **`is_mock` Detection** | Checked locally via `geolocator.position.isMocked` | **Ignored** (Not in validation rules, not saved) | **Critical**: Direct API submission bypasses check entirely. |
| **`accuracy` Threshold** | Checked ($< 250\text{m}$) in Dart | Checked ($< 250\text{m}$) in PHP | **Medium**: No check for $0.0\text{m}$ (fake GPS zero-jitter). Accuracy not stored in DB. |
| **Developer Options** | **Not checked** | **Not checked** | **High**: Cannot distinguish safe devices from active mock environments. |
| **USB Debugging (ADB)** | **Not checked** | **Not checked** | **Medium**: No detection of wired ADB coordinate simulation. |
| **Root Status** | **Not checked** | **Not checked** | **High**: Rooted devices can hook Android APIs and spoof coordinates undetected. |
| **Play Integrity API** | **Not implemented** | **Not implemented** | **Medium**: No hardware attestation or app authenticity verification. |
| **Device Identification** | Not sent in attendance payload | Not validated in attendance controller | **High**: Cannot correlate suspicious attendance to specific hardware devices. |
| **Timestamp / Nonce** | Not sent (only relies on server `now()`) | Server uses `now()` | **Mitigated**: Server enforces authoritative server clock for attendance time. |
| **Geofence Distance** | Calculated via Haversine | Calculated via Haversine | **Aligned**: Both match mathematical Haversine ($R = 6,371,000\text{m}$). |
| **Face Biometric** | Captured via front camera, sent base64 | Decrypted template, 3D liveness & cosine match | **Aligned**: 3D liveness and matching implemented securely on backend. |

---

## Part 6: Concrete Remediation Recommendations

### 6.1 Native Android Bridge: Security & Mock Detection MethodChannel
Implement a lightweight, zero-dependency Kotlin Security Bridge in `android/app/src/main/kotlin/com/cooca/myown/cooca_my_own/MainActivity.kt` responding to channel `com.cooca.myown/security`:
1. `checkDeviceSecurity`: Returns a structured map:
   - `is_mock_location`: Boolean (checks `Location.isMock()`, `Location.isFromMockProvider()`, and `AppOpsManager.OPSTR_MOCK_LOCATION`).
   - `is_dev_mode`: Boolean (`Settings.Global.DEVELOPMENT_SETTINGS_ENABLED`).
   - `is_adb_enabled`: Boolean (`Settings.Global.ADB_ENABLED`).
   - `is_rooted`: Boolean (su binary paths check, test-keys check, Magisk package check).
   - `is_emulator`: Boolean (checks `Build.FINGERPRINT`, `Build.MODEL`, `Build.HARDWARE`).
   - `device_id`: Secure hardware or install ID (`Settings.Secure.ANDROID_ID`).
2. Benefit: Avoids third-party package dependency conflicts and compiles directly with Android 12–15 modern SDKs.

### 6.2 Mobile Dart Security Service (`DeviceSecurityService.dart`)
Create `lib/core/services/device_security_service.dart`:
- Communicates via `MethodChannel('com.cooca.myown/security')`.
- Performs multi-sample GPS jitter verification (takes 2 location fixes 800ms apart; verifies coordinate difference $> 0.0$ and speed plausibility).
- Classifies risk into Level 0 (Clean), Level 1 (Dev Mode Only - Informative), and Level 3 (Mock GPS Active - Block).
- Integrates gracefully into `AttendanceScreen` before opening `InAppBiometricCameraSheet`.

### 6.3 Extended Attendance API Contract (`cooca_core`)
1. **Extend Request Validation** in `AttendanceApiController.php`:
   ```php
   $validated = $request->validate([
       'latitude' => ['nullable', 'numeric', 'between:-90,90'],
       'longitude' => ['nullable', 'numeric', 'between:-180,180'],
       'accuracy' => ['nullable', 'numeric', 'min:0'],
       'address' => ['nullable', 'string', 'max:500'],
       'notes' => ['nullable', 'string', 'max:255'],
       'photo' => ['nullable'],
       'face_data' => ['nullable'],
       'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
       // NEW Security Telemetry Fields
       'device_id' => ['nullable', 'string', 'max:100'],
       'is_mock' => ['nullable', 'boolean'],
       'is_dev_mode' => ['nullable', 'boolean'],
       'is_adb_enabled' => ['nullable', 'boolean'],
       'is_rooted' => ['nullable', 'boolean'],
       'play_integrity_token' => ['nullable', 'string', 'max:5000'],
   ]);
   ```
2. **Backend Enforcement Rules** in `AttendanceService.php`:
   - If `$data['is_mock'] === true` $\rightarrow$ Reject with `ValidationException: "Presensi ditolak: Terdeteksi sinyal lokasi palsu (Fake GPS/Mock Location)."`.
   - If `$data['is_rooted'] === true` and business policy is strict $\rightarrow$ Flag for review or reject.
   - If `$data['accuracy'] === 0.0` or `$data['accuracy'] < 0.5` $\rightarrow$ Flag as suspicious synthetic GPS.
   - If `$data['is_dev_mode'] === true` but `$data['is_mock'] === false` $\rightarrow$ Accept attendance and log informational audit trail without blocking.
3. **Database Migration**:
   - Add `device_id`, `is_mock`, `accuracy`, and `security_flags` to `attendances` table.
4. **Audit Trail**:
   - Record `is_mock`, `is_dev_mode`, `is_rooted`, and `device_id` in `AuditLog` metadata.

---

## Conclusion
The investigation confirms that while basic location geofencing and facial biometrics are operational, critical vulnerabilities exist in Fake GPS prevention, device security signal handling, and backend contract validation. The recommended multi-layered architecture enforces client-side heuristics and native Android checks while closing the backend validation gap, ensuring zero false-positive blocks for safe devices with Developer Options.
