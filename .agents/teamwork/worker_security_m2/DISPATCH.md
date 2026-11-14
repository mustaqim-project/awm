## 2026-11-03T02:35:01Z
You are worker_security_m2, an implementation worker.
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_security_m2\
Original request path: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Project plan path: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Survey findings path: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_security_1\analysis.md and c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_security_1\handoff.md

Relevant skills:
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\find-security-vulnerabilities-in-code\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may modify:
- C:\laragon\www\cooca_my_own\android\app\src\main\kotlin\com\cooca\myown\cooca_my_own\MainActivity.kt
- C:\laragon\www\cooca_my_own\lib\screens\attendance_screen.dart
- C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart
- C:\laragon\www\cooca_my_own\lib\core\services\location_biometric_service.dart
- C:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Hrm\AttendanceApiController.php
- C:\laragon\www\cooca_core\app\Domain\HRM\AttendanceService.php
- Backend database migration for security columns in `C:\laragon\www\cooca_core\database\migrations\`
- Test files: C:\laragon\www\cooca_my_own\test\attendance_security_test.dart and C:\laragon\www\cooca_core\tests\Feature\Api\AttendanceSecurityRemediationTest.php

YOUR MISSION  IMPLEMENT ANDROID SECURITY & FAKE GPS PREVENTION FOR ATTENDANCE (Milestone 2):
1. **Native Android MethodChannel in `MainActivity.kt`**:
   - In `android/app/src/main/kotlin/com/cooca/myown/cooca_my_own/MainActivity.kt`, implement MethodChannel `com.cooca.myown/security` with method `checkDeviceSecurity`.
   - Inspect Android system settings:
     - `Settings.Global.getInt(contentResolver, Settings.Global.DEVELOPMENT_SETTINGS_ENABLED, 0) == 1` -> `is_dev_mode`
     - `Settings.Global.getInt(contentResolver, Settings.Global.ADB_ENABLED, 0) == 1` -> `is_adb_enabled`
     - Mock location check: official Android Location APIs (`Location.isMock()`, `Location.isFromMockProvider()` or AppOpsManager `OPSTR_MOCK_LOCATION`)
     - Root status check: common root paths (`/system/bin/su`, `/system/xbin/su`, `/sbin/su`, `/data/local/su`, test-keys) -> `is_rooted`
     - Stable pseudo device ID: `Settings.Secure.ANDROID_ID` -> `device_id`
2. **Flutter Security Integration & 4-Tier Risk-Proportionate Policy**:
   - In `location_biometric_service.dart`, invoke the `com.cooca.myown/security` MethodChannel.
   - Combine with `position.isMocked` and coordinate consistency/jitter checks.
   - 4-Tier Policy:
     - Level 0 (Clean): Normal flow.
     - Level 1 (Developer Options or USB Debugging ON, but NO mock location): Allow attendance with non-blocking amber warning banner/chip and transmit audit flag. Do NOT falsely classify as Fake GPS!
     - Level 3 (Mock Location active / `isMocked == true`): Strict block. Display clear alert with step-by-step guidance on disabling mock location providers in Developer Options.
3. **Pre-Attendance Submission Checks**:
   - In `attendance_screen.dart`, verify: location permission granted, GPS enabled, accuracy threshold (`accuracy <= 250m`), mock location flag false, and geofence distance calculation.
4. **Transmit Security Telemetry to Backend**:
   - In `attendance_service.dart`, update `clockIn()` and `clockOut()` payloads to transmit `is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, and optional `play_integrity_token`.
5. **Backend Attendance Validation & Graceful Degradation in `cooca_core`**:
   - In `AttendanceApiController.php`, update validation rules to accept `is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, `play_integrity_token`.
   - In `AttendanceService.php`, reject attendance immediately with HTTP 422/403 if `is_mock === true`.
   - Store `device_id`, `is_mock`, `is_dev_mode`, and security audit flags in `attendances` table (create migration if columns needed).
   - If Play Integrity token is absent or cloud credentials are not active locally, gracefully degrade and rely on native integrity signals without crashing.
6. **Verification & Testing**:
   - Write comprehensive automated tests in `test/attendance_security_test.dart` in `cooca_my_own` and `tests/Feature/Api/AttendanceSecurityRemediationTest.php` in `cooca_core`.
   - Run `flutter analyze` and `flutter test test/attendance_security_test.dart`.
   - Run `php artisan test tests/Feature/Api/AttendanceSecurityRemediationTest.php`.
   - Verify zero errors and all tests pass.

DELIVERABLES:
Document your exact changes, build/test execution commands, and output in `c:\laragon\www\cooca_core\.agents\teamwork\worker_security_m2\handoff.md`.
Send a message when completed.

## 2026-11-03T02:51:49Z
**Context**: Milestone 2 Android Security Implementation
**Content**: Heartbeat check. Please update your current implementation status, what step you are currently on, and update progress.md with your latest timestamp.
**Action**: Report current status and continue execution.
