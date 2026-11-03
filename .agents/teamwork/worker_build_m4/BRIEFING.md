# BRIEFING — 2026-11-03T03:14:00Z

## Mission
Harden Android Production Build Configuration & Performance for cooca_my_own Flutter project (Milestone 4).

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_build_m4\
- Original parent: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Milestone: Milestone 4 (Android Build & Performance)

## 🔒 Key Constraints
- Exclusive write ownership:
  - C:\laragon\www\cooca_my_own\android\app\build.gradle.kts
  - C:\laragon\www\cooca_my_own\android\app\proguard-rules.pro
  - C:\laragon\www\cooca_my_own\android\app\src\main\AndroidManifest.xml
  - C:\laragon\www\cooca_my_own\pubspec.yaml
- DO NOT CHEAT. All implementations must be genuine.
- Enforce non-debuggable release build, remove debug signing in release, enable minification & resource shrinking, add proguard-rules.pro.
- Enforce HTTPS & remove usesCleartextTraffic="true".
- Add cached_network_image: ^3.4.1 to pubspec.yaml and verify flutter pub get and flutter analyze.

## Current Parent
- Conversation ID: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Updated: 2026-11-03T03:14:00Z

## Task Summary
- **What to build**: Android release build configuration hardening, ProGuard keep rules, HTTPS enforcement, cached_network_image integration.
- **Success criteria**:
  1. `build.gradle.kts` release build has `isDebuggable = false`, proper release signing with env/property fallback, `isMinifyEnabled = true`, `isShrinkResources = true`, and references `proguard-rules.pro`.
  2. `proguard-rules.pro` authored with Flutter, Android, Kotlin coroutines, JSON serialization, and MethodChannel keep rules.
  3. `AndroidManifest.xml` has `android:usesCleartextTraffic="true"` removed while retaining necessary location, camera, and internet permissions.
  4. `pubspec.yaml` has `cached_network_image: ^3.4.1` added, `flutter pub get` and `flutter analyze` pass with 0 errors/issues.
- **Interface contracts**: PROJECT.md
- **Code layout**: C:\laragon\www\cooca_my_own\

## Key Decisions Made
- [Initial] Follow minimal change principle and verify with `flutter pub get` and `flutter analyze`.

## Artifact Index
- DISPATCH.md — assignment record
- BRIEFING.md — persistent situational awareness
- progress.md — liveness and progress tracker
- handoff.md — final 5-component handoff report

## Change Tracker
- **Files modified**: [TBD]
- **Build status**: [TBD]
- **Pending issues**: None

## Quality Status
- **Build/test result**: [TBD]
- **Lint status**: [TBD]
- **Tests added/modified**: [TBD]

## Loaded Skills
- **Source**: C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
  - **Local copy**: C:\laragon\www\cooca_core\.agents\teamwork\worker_build_m4\skills\cooca-agent-directive.md
  - **Core methodology**: Cooca multi-tenant ecosystem directives, anti-slop, clean architecture, security-first.
- **Source**: C:\Users\Ulfah Ramadhani\.gemini\config\skills\find-security-vulnerabilities-in-code\SKILL.md
  - **Local copy**: C:\laragon\www\cooca_core\.agents\teamwork\worker_build_m4\skills\find-security-vulnerabilities-in-code.md
  - **Core methodology**: White-box code security review, identifying hardcoded credentials, cleartext traffic, and authorization flaws.
