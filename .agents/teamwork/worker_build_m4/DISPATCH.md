## 2026-11-03T03:13:11Z
[Message] timestamp=2026-11-03T03:13:11Z sender=73d9d035-59a2-45d3-a207-90bc9a5adcc4 priority=MESSAGE_PRIORITY_HIGH content=You are worker_build_m4, an implementation worker for Android Build & Performance.
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_build_m4\
Original request path: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Project plan path: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Survey findings path: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_modules_1\analysis.md and c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_modules_1\handoff.md

Relevant skills:
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\find-security-vulnerabilities-in-code\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may modify:
- C:\laragon\www\cooca_my_own\android\app\build.gradle.kts
- C:\laragon\www\cooca_my_own\android\app\proguard-rules.pro
- C:\laragon\www\cooca_my_own\android\app\src\main\AndroidManifest.xml
- C:\laragon\www\cooca_my_own\pubspec.yaml

YOUR MISSION — ANDROID PRODUCTION BUILD CONFIGURATION & PERFORMANCE HARDENING (Milestone 4):
1. **Enforce Non-Debuggable Release Build in `android/app/build.gradle.kts`**:
   - In `buildTypes.release`:
     - Explicitly set `isDebuggable = false`.
     - Remove `signingConfig = signingConfigs.getByName("debug")`. Implement proper production signing config block (with fallback to environment variables / release keystore properties).
     - Enable code shrinking and obfuscation: `isMinifyEnabled = true` and `isShrinkResources = true`.
     - Reference ProGuard rules: `proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")`.
2. **Author `android/app/proguard-rules.pro`**:
   - Add proper Flutter and Android keep rules:
     - Keep Flutter engine and plugin wrapper classes.
     - Keep Kotlin coroutines, JSON serialization/deserialization models, and native MethodChannels.
3. **Enforce HTTPS & Remove Cleartext in `AndroidManifest.xml`**:
   - In `android/app/src/main/AndroidManifest.xml`:
     - Remove `android:usesCleartextTraffic="true"` (line 28) so cleartext unencrypted HTTP is strictly prohibited in production.
     - Verify permissions: keep necessary fine/coarse location, camera, and internet permissions.
4. **Performance & Image Caching in `pubspec.yaml`**:
   - Add `cached_network_image: ^3.4.1` to `pubspec.yaml` dependencies.
   - Run `flutter pub get` in `C:\laragon\www\cooca_my_own` to verify clean resolution.
5. **Static Analysis & Build Verification**:
   - Run `flutter analyze` in `C:\laragon\www\cooca_my_own`. Ensure 0 errors and 0 issues.
   - Verify Android gradle syntax and configuration.

DELIVERABLES:
Document your exact changes, build/test execution commands, and output in `c:\laragon\www\cooca_core\.agents\teamwork\worker_build_m4\handoff.md`.
Send a message when completed.
