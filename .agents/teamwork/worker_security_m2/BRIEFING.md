# BRIEFING — 2026-11-03T02:37:00Z

## Mission
Implement Android Security & Fake GPS Prevention for Attendance (M2) across Flutter mobile (cooca_my_own) and Laravel backend (cooca_core).

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_security_m2\
- Original parent: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Milestone: M2 - Android Security & Fake GPS Prevention for Attendance

## 🔒 Key Constraints
- Exclusive write ownership:
  - C:\laragon\www\cooca_my_own\android\app\src\main\kotlin\com\cooca\myown\cooca_my_own\MainActivity.kt
  - C:\laragon\www\cooca_my_own\lib\screens\attendance_screen.dart
  - C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart
  - C:\laragon\www\cooca_my_own\lib\core\services\location_biometric_service.dart
  - C:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Hrm\AttendanceApiController.php
  - C:\laragon\www\cooca_core\app\Domain\HRM\AttendanceService.php
  - Database migration in C:\laragon\www\cooca_core\database\migrations\
  - Test files: C:\laragon\www\cooca_my_own\test\attendance_security_test.dart and C:\laragon\www\cooca_core\tests\Feature\Api\AttendanceSecurityRemediationTest.php
- Mandatory Integrity: No cheating, no hardcoded test shortcuts, no dummy/facade implementations.
- 4-Tier Risk-Proportionate policy: Level 1 (Dev options/ADB ON, mock OFF) allows attendance with warning chip + telemetry; Level 3 (Mock Location active) strictly blocks attendance.
- Play Integrity graceful degradation: if cloud credentials are absent locally, fallback to native integrity signals without crashing.

## Current Parent
- Conversation ID: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Updated: not yet

## Task Summary
- **What to build**: Android native MethodChannel in MainActivity.kt, Flutter 4-tier security & jitter detection, attendance pre-submission verification, security telemetry payload in attendance service, backend validation & migration in cooca_core, and automated tests.
- **Success criteria**: Flutter analyze and tests pass; Laravel tests pass; zero regressions.
- **Interface contracts**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md § Interface Contracts
- **Code layout**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md § Code Layout

## Key Decisions Made
- Planned 4-tier security assessment model: Level 0 (clean), Level 1 (dev options/ADB enabled, non-blocking), Level 2 (emulator/root warning, non-blocking unless policy dictates), Level 3 (mock location / fake GPS actively detected, hard block).
- Planned backend migration to add `device_id`, `is_mock`, `is_dev_mode`, `is_rooted`, `accuracy`, and `security_flags` to `attendances` table.

## Artifact Index
- [TBD]

## Change Tracker
- **Files modified**: none
- **Build status**: pending
- **Pending issues**: none

## Quality Status
- **Build/test result**: pending
- **Lint status**: clean
- **Tests added/modified**: pending

## Loaded Skills
- Source: C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
  - Core methodology: Multi-skill synergy, zero-manual UI, security & tenant isolation, genuine testing.
- Source: C:\Users\Ulfah Ramadhani\.gemini\config\skills\find-security-vulnerabilities-in-code\SKILL.md
  - Core methodology: White-box security review and proof-of-concept verification.
