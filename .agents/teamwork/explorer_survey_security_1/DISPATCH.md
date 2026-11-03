## 2026-11-03T02:16:01Z
You are explorer_survey_security_1, a read-only exploration agent.
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_security_1\
Original request path: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

CRITICAL INSTRUCTIONS:
- You are READ-ONLY. Do NOT modify source code or implement fixes.
- Read ORIGINAL_REQUEST.md first.
- Target Mobile Repo: C:\laragon\www\cooca_my_own
- Target Backend Repo: C:\laragon\www\cooca_core
- Live API: https://cooca.id

YOUR MISSION:
Perform a comprehensive deep-dive investigation into Android Security & Fake GPS Prevention for Attendance (R2 of requirements):
1. Locate and examine all attendance and location handling in `C:\laragon\www\cooca_my_own`:
   - Attendance screens (e.g., `lib/screens/attendance/*`, check-in/check-out UI).
   - Location services, geofencing logic, GPS accuracy checks, coordinate calculations.
   - Dependencies in `pubspec.yaml` (e.g. `geolocator`, `location`, `safe_device`, `trust_fall`, `root_checker`, or native method channels).
   - Android native configuration in `android/app/src/main/AndroidManifest.xml` (location permissions, foreground service, etc.).
2. Investigate Fake GPS / Mock Location detection capabilities:
   - Official Android location API flags (e.g. `Location.isMock()`, `Location.isFromMockProvider()`).
   - Coordinate anomalies, altitude, speed, timestamp consistency, accuracy thresholds.
   - Current gaps in mock location detection in mobile app.
3. Investigate Developer Options and USB Debugging detection:
   - Android `Settings.Global.DEVELOPMENT_SETTINGS_ENABLED`, `Settings.Global.ADB_ENABLED`.
   - How to implement a configurable, risk-proportionate policy (warn vs block, user-friendly guidance) so safe devices are NOT falsely blocked as Fake GPS.
4. Investigate Play Integrity API and Root detection:
   - Root detection methods (su binary, test-keys, Magisk, Busybox).
   - Play Integrity API evaluation and graceful degradation when Google Play Services or cloud credentials are not active locally.
5. Investigate Backend Attendance Contracts in `C:\laragon\www\cooca_core`:
   - Backend routes (`routes/api.php`) for attendance check-in/check-out.
   - Controllers, validation rules, request parameters (latitude, longitude, accuracy, is_mock, integrity signals, device_id).
   - Current backend geofence calculation and anti-spoofing validation logic.
   - Gap analysis between what mobile sends vs what backend validates.

DELIVERABLES:
Write a detailed report to `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_security_1\analysis.md` and `handoff.md`.
Include concrete file paths, line numbers, observed code snippets, architecture gaps, and specific remediation recommendations.
Send a message when your handoff is ready.
