# BRIEFING  2026-11-03T03:15:00Z

## Mission
Eliminate dummy/mock data across POS catalog, inventory, dashboard, settings, auth, and HRM in `cooca_my_own`, fix route mismatches, and bind directly to live endpoints.

## 🔒 My Identity
- Archetype: implementer
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\
- Original parent: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Milestone: Milestone 3 (Comprehensive Mobile Feature Audit & Dummy Data Elimination)

## 🔒 Key Constraints
- DO NOT CHEAT: All implementations must be genuine. No hardcoded test results, mock fallbacks, or dummy implementations.
- EXCLUSIVE WRITE OWNERSHIP:
  - C:\laragon\www\cooca_my_own\lib\state\merchant_state.dart
  - C:\laragon\www\cooca_my_own\lib\screens\inventory_screen.dart
  - C:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart
  - C:\laragon\www\cooca_my_own\lib\screens\settings\edit_user_profile_modal.dart
  - C:\laragon\www\cooca_my_own\lib\screens\settings\system_settings_screen.dart
  - C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart
  - C:\laragon\www\cooca_my_own\lib\core\services\auth_service.dart
  - C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart
  - Test files: C:\laragon\www\cooca_my_own\test\dummy_data_elimination_test.dart
- `flutter analyze` must pass with 0 errors.
- `flutter test test/dummy_data_elimination_test.dart` must pass 100%.

## Current Parent
- Conversation ID: 73d9d035-59a2-45d3-a207-90bc9a5adcc4
- Updated: 2026-11-03T03:15:00Z

## Task Summary
- **What to build**:
  1. POS Catalog: Eliminate `_getDefaultStarterProducts(businessType)` in `merchant_state.dart`; return empty catalog with Apple HIG empty state prompt.
  2. Inventory Screen: Eliminate static `_materials` and `_movementLogs` in `inventory_screen.dart`; fetch real products/stocks from `state.allProducts` or live API; compute real valuation; add Apple HIG empty state.
  3. Dashboard: Eliminate static `switch(h)` at line 1647; bind `state.hourlyTrend` from live pulse response; remove static Unsplash avatar at line 246 and use user initials/live avatar.
  4. Settings & Profile: Replace simulated `Future.delayed(500ms)` in `edit_user_profile_modal.dart` with `PUT /api/v1/profile` via `ApiClient`; in `system_settings_screen.dart`, implement real cache clearing (image cache, local storage).
  5. Route Mismatches in `pos_service.dart`: Fix line 703 (`dispatchCourier` -> `POST /api/v1/commerce/orders/$orderId/request-pickup`) and line 660 (`verifyPayment` -> `POST /api/v1/commerce/orders/$orderId/verify-payment`).
  6. Auth & HRM Fallback Mocks: In `auth_service.dart`, eliminate fake business `b1` and fake ticket `REC-`; in `attendance_service.dart`, eliminate `usr-local` fallback.
  7. Verification: Write tests in `test/dummy_data_elimination_test.dart`, run `flutter analyze` and `flutter test`.
- **Success criteria**: 0 errors on flutter analyze, 100% test pass, no dummy/fake data remaining.
- **Interface contracts**: `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md`
- **Code layout**: `C:\laragon\www\cooca_my_own`

## Key Decisions Made
- [TBD]

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\DISPATCH.md`  Dispatch record
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\BRIEFING.md`  Situational awareness
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\progress.md`  Liveness & progress tracker
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\handoff.md`  Final handoff report

## Change Tracker
- **Files modified**: None yet
- **Build status**: Untested
- **Pending issues**: None

## Quality Status
- **Build/test result**: Untested
- **Lint status**: 0 violations (target)
- **Tests added/modified**: `test/dummy_data_elimination_test.dart` (pending)

## Loaded Skills
- **Source**: `C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md`
  - **Local copy**: `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\skills\cooca-agent-directive.md`
  - **Core methodology**: Single source of truth, zero-manual UI, multi-skill collaboration, history-first.
- **Source**: `C:\Users\Ulfah Ramadhani\.gemini\config\skills\flutter-ios-design\SKILL.md`
  - **Local copy**: `c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\skills\flutter-ios-design.md`
  - **Core methodology**: Apple HIG look on iOS & Android, clarity/deference/depth, uncluttered styled controls.
