# Handoff Report: Android Security & Fake GPS Prevention for Attendance (R2)

**Agent ID**: `explorer_survey_security_1`  
**Handoff Type**: Hard (Task Complete)  
**Date**: 2026-11-03  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_security_1\`  
**Target Mobile Repo**: `C:\laragon\www\cooca_my_own`  
**Target Backend Repo**: `C:\laragon\www\cooca_core`  

---

## 1. Observation

### Mobile Client (`C:\laragon\www\cooca_my_own`)
1. **Attendance Flow in `lib/screens/attendance_screen.dart`**:
   - Lines 163–175: Location is fetched via `_sensorService.getCurrentDeviceLocation(context: context)` and assigns `_isMockLocationDetected = result.isMocked`.
   - Lines 240–250: In `_triggerAttendanceAction`, check-in/out stops if `locResult.isMocked` is true:
     ```dart
     if (locResult.isMocked) {
       _showSecurityAlert(
         title: 'Fake GPS / Mock Location Terdeteksi',
         message: 'Sistem keamanan COOCA mendeteksi adanya aplikasi lokasi palsu pada perangkat Anda. Nonaktifkan developer mock location sebelum melakukan absensi.',
       );
       return;
     }
     ```
   - Lines 293–313: Coordinates are sent to backend:
     ```dart
     result = await _attendanceService.clockIn(
       latitude: locResult.latitude,
       longitude: locResult.longitude,
       accuracy: locResult.accuracy,
       address: officeName,
       notes: _selectedNoteOption,
       photo: base64Photo,
     );
     ```
2. **Location & Sensor Service in `lib/core/services/location_biometric_service.dart`**:
   - Lines 77–82: Requests `Geolocator.getCurrentPosition(locationSettings: LocationSettings(accuracy: LocationAccuracy.high, timeLimit: Duration(seconds: 10)))`.
   - Lines 86–88: Falls back to `Geolocator.getLastKnownPosition()` if high accuracy fails.
   - Lines 99–108: Checks `if (position.isMocked) { return DeviceLocationResult(..., isMocked: true); }`.
   - Lines 111–119: Checks `if (position.accuracy > maxAcceptableAccuracyMeters)` ($250.0\text{m}$).
   - **Gaps**: No verification of coordinate jitter, altitude, bearing, velocity, or timestamp freshness.
3. **Attendance Network Service in `lib/core/services/attendance_service.dart`**:
   - Lines 90–114 (`clockIn`) and lines 116–140 (`clockOut`): Request bodies assemble only `latitude`, `longitude`, `accuracy`, `address`, `notes`, `photo`, and `face_data`.
   - **Gaps**: `is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, and `play_integrity_token` are **never** transmitted to the server.
4. **Android Native Configuration in `C:\laragon\www\cooca_my_own\android`**:
   - `android/app/src/main/kotlin/com/cooca/myown/cooca_my_own/MainActivity.kt`: Contains 6 lines total:
     ```kotlin
     package com.cooca.myown.cooca_my_own
     import io.flutter.embedding.android.FlutterActivity
     class MainActivity : FlutterActivity()
     ```
     **Zero** native method channels for mock location, developer options, USB debugging, root detection, or Play Integrity.
   - `android/app/src/main/AndroidManifest.xml`: Line 28 has `android:usesCleartextTraffic="true"`.
   - `pubspec.yaml`: Contains `geolocator: ^14.1.1` and `permission_handler: ^11.3.1`, but **no** device security, root detection, or Play Integrity packages.

### Backend Repo (`C:\laragon\www\cooca_core`)
1. **Attendance API Controller in `app/Http/Controllers/Api/V1/Hrm/AttendanceApiController.php`**:
   - Lines 46–55 (`checkIn`) and 100–108 (`checkOut`):
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
   - **Gaps**: Does **not** validate `is_mock`, `device_id`, `is_dev_mode`, or `play_integrity_token`.
2. **Domain Service in `app/Domain/HRM/AttendanceService.php`**:
   - Lines 66–72: Verifies `accuracy <= MAX_GPS_ACCURACY_METERS` ($250.0\text{m}$).
   - Lines 158–172: Calculates Haversine distance and checks `$calculatedDistance > $allowedRadius`.
   - Lines 74–107: Verifies face biometrics against registered template with 3D liveness.
   - **Gaps**: Does **not** check mock location flags or device security signals.
3. **Database Migration in `database/migrations/2026_09_21_000002_create_hrm_attendances_and_corrections_tables.php`**:
   - `attendances` table lacks columns for `device_id`, `is_mock`, `accuracy`, and `security_flags`.
4. **Audit Logging**:
   - `AuditLog` in lines 314–337 of `AttendanceService.php` logs only `request()->ip()` and `request()->userAgent()`, missing device ID and integrity telemetry.

---

## 2. Logic Chain

1. **Premise 1**: Observation 1.1 & 1.3 show that `cooca_my_own` intercepts mock location exclusively on the client side via `position.isMocked` and halts execution locally without informing the backend.
2. **Premise 2**: Observation 1.3 & Backend 1 show that `attendance_service.dart` does not transmit `is_mock` or device integrity parameters, and `AttendanceApiController.php` does not validate or expect them.
3. **Inference 1 (Security Bypass)**: An attacker who modifies the Flutter binary, uses Frida/Xposed hooks, or directly submits HTTP requests (`curl -X POST https://cooca.id/api/v1/attendance/check-in`) with crafted GPS coordinates and `accuracy: 15.0` completely bypasses client-side detection. The backend accepts and persists the attendance without any anti-spoofing rejection.
4. **Premise 3**: Observation 1.4 confirms `MainActivity.kt` has no MethodChannel, and no Dart code checks `Settings.Global.DEVELOPMENT_SETTINGS_ENABLED`, `ADB_ENABLED`, or root indicators.
5. **Inference 2 (Lack of Risk-Proportionate Visibility)**: The app currently cannot distinguish between a developer using legitimate Developer Options (e.g. for animation scales) and an adversary with an active Mock Location Provider. Hardcoding a blanket block on Developer Options would create severe false positives; however, having zero detection prevents applying an intelligent warning or audit policy.
6. **Premise 4**: Observation 1.4 & Backend 1 show no Play Integrity API integration, but Requirement R2 requires evaluation with graceful degradation when credentials/GMS are inactive.
7. **Inference 3 (Production Readiness Architecture)**: A dual-layer architecture is required:
   - A native Android Kotlin MethodChannel to inspect `isMock`, `AppOpsManager`, `DEVELOPMENT_SETTINGS_ENABLED`, `ADB_ENABLED`, and root binaries.
   - An expanded backend API contract that validates `is_mock`, records `device_id` and security audit signals, and degrades gracefully when Play Integrity cloud credentials are not active locally.

---

## 3. Caveats

1. **Live Production API Testing**: The live production API at `https://cooca.id` was not subjected to active penetration attacks (e.g., submitting fake attendance payloads) in accordance with read-only investigation rules.
2. **Third-Party Play Integrity Console Binding**: Verifying a real Google Play Integrity token requires linking the app package `com.cooca.myown.cooca_my_own` to a Google Cloud Project in the Google Play Console with active production signing credentials.
3. **iOS Platform Scope**: This investigation focused primarily on Android security (mock location, developer settings, ADB, Play Integrity, root). iOS equivalent checks (Cydia/Jailbreak detection, location simulation via Xcode/LocationSimulation) should follow the same risk-proportionate contract design.

---

## 4. Conclusion

The current attendance security architecture in `cooca_my_own` and `cooca_core` has solid foundations in geofencing and facial biometrics, but exhibits critical gaps in GPS spoofing prevention and device integrity:
1. **Fake GPS defense is purely client-side** and easily bypassed via direct API interaction or root hooks.
2. **Developer Options and USB Debugging are completely unmonitored**, precluding a risk-proportionate policy.
3. **The backend API contract is blind to device integrity**, omitting `is_mock`, `device_id`, and security signals.

### Specific Remediation Recommendations:
1. **Implement Native Kotlin MethodChannel (`com.cooca.myown/security`) in `MainActivity.kt`**:
   - Expose `checkDeviceSecurity()` returning `is_mock_location`, `is_dev_mode`, `is_adb_enabled`, `is_rooted`, `is_emulator`, and `device_id`.
2. **Implement Multi-Sample Heuristic Jitter Check in Mobile**:
   - Sample two GPS fixes 800ms apart; verify coordinate variance and speed plausibility before accepting coordinates.
3. **Apply 4-Tier Risk-Proportionate Policy**:
   - Level 0 (Clean): Allow.
   - Level 1 (Dev Mode ON, but no mock app): Allow with non-blocking amber warning chip + audit flag.
   - Level 3 (Mock Location Active / `isMocked == true`): Block with step-by-step remediation guidance.
4. **Extend Backend API Contract in `cooca_core`**:
   - Add `is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, and `play_integrity_token` to `AttendanceApiController` validation rules.
   - In `AttendanceService.php`, reject immediately if `is_mock === true`.
   - Update `attendances` table and `AuditLog` to store device telemetry.
5. **Implement Play Integrity Graceful Degradation**:
   - If GMS is absent or cloud credentials are unconfigured, fallback gracefully to local native security signals without blocking attendance.

---

## 5. Verification Method

### How to Independently Verify Observations:
1. **Verify Mobile Code**:
   - Inspect `C:\laragon\www\cooca_my_own\lib\screens\attendance_screen.dart` lines 240–250 and 293–313.
   - Inspect `C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart` lines 90–114.
   - Inspect `C:\laragon\www\cooca_my_own\android\app\src\main\kotlin\com\cooca\myown\cooca_my_own\MainActivity.kt`.
2. **Verify Backend Code**:
   - Inspect `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Hrm\AttendanceApiController.php` lines 46–55.
   - Inspect `c:\laragon\www\cooca_core\app\Domain\HRM\AttendanceService.php` lines 61–173.
   - Inspect `c:\laragon\www\cooca_core\database\migrations\2026_09_21_000002_create_hrm_attendances_and_corrections_tables.php`.
3. **Run Automated Tests**:
   - In `cooca_core`, run:
     ```bash
     php artisan test tests/Feature/Api/AttendanceApiTest.php
     php artisan test tests/Feature/HrmAttendanceGeofenceTest.php
     php artisan test tests/Feature/AttendanceConcurrencyAndSecurityTest.php
     ```
4. **Invalidation Conditions**:
   - This analysis is invalidated if `cooca_core` already has an unmerged branch or middleware enforcing `is_mock` validation, or if `MainActivity.kt` is replaced with an existing security method channel.
