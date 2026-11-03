## 2026-11-03T03:12:44Z
You are worker_modules_m3, an implementation worker.
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_modules_m3\
Original request path: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Project plan path: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Survey findings path: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_modules_1\analysis.md and c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_modules_1\handoff.md

Relevant skills:
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- C:\Users\Ulfah Ramadhani\.gemini\config\skills\flutter-ios-design\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may modify:
- C:\laragon\www\cooca_my_own\lib\state\merchant_state.dart
- C:\laragon\www\cooca_my_own\lib\screens\inventory_screen.dart
- C:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart
- C:\laragon\www\cooca_my_own\lib\screens\settings\edit_user_profile_modal.dart
- C:\laragon\www\cooca_my_own\lib\screens\settings\system_settings_screen.dart
- C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart
- C:\laragon\www\cooca_my_own\lib\core\services\auth_service.dart
- C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart
- Test files: C:\laragon\www\cooca_my_own\test\dummy_data_elimination_test.dart

YOUR MISSION — ELIMINATE DUMMY/MOCK DATA & CONNECT TO LIVE ENDPOINTS (Milestone 3):
1. **POS Catalog Dummy Starter Products Elimination**:
   - In `lib/state/merchant_state.dart`:
     - Eliminate `_getDefaultStarterProducts(businessType)` (lines 1231–1576) containing 24+ hardcoded dummy products with fake prices and static Unsplash URLs.
     - When offline cache or catalog is empty, do NOT populate with fake products. Return an empty catalog with a clean empty-state message and pull-to-refresh prompt to load from the live backend catalog.
2. **Inventory Screen Real Data & Real Valuation**:
   - In `lib/screens/inventory_screen.dart`:
     - Eliminate static lists `_materials` (lines 60–152) and `_movementLogs` (lines 155–186).
     - Fetch live inventory materials and products from `widget.state.allProducts` or live inventory API (`/api/v1/products` / `/api/v1/mobile/inventory/items`).
     - Fix `_calculateTotalValuation()` to compute real asset valuation based on live inventory quantities and purchase costs, not static mock arrays.
     - Handle empty inventory state gracefully with Apple HIG empty state illustration and refresh button.
3. **Dashboard Real Hourly Trend & Live Profile Data**:
   - In `lib/screens/dashboard_screen.dart`:
     - Eliminate static `switch(h)` at line 1647 (`8 => 0.25, 10 => 0.45, 12 => 0.95...`).
     - Bind `state.hourlyTrend` directly from the live API response of `GET /api/v1/mobile/dashboard/pulse`.
     - Remove static Unsplash photo URL at line 246; bind the authenticated user's real avatar URL or generate a clean initials avatar fallback.
4. **Settings & Profile Live Persistence**:
   - In `lib/screens/settings/edit_user_profile_modal.dart`:
     - Replace simulated in-memory update and `Future.delayed(500ms)` with a real API call: `PUT /api/v1/profile` via `ApiClient`.
     - Update local user profile state and persistent storage upon receiving success response.
   - In `lib/screens/settings/system_settings_screen.dart`:
     - Replace simulated SnackBar with real cache eviction: purge image cache, clear expired cached orders, and refresh memory state.
5. **API Route Mismatches Fix in `pos_service.dart`**:
   - Line 703 (`dispatchCourier`): Change endpoint from `/api/v1/pos/orders/$orderId/dispatch-courier` to live backend route `POST /api/v1/commerce/orders/$orderId/request-pickup`.
   - Line 660 (`verifyPayment`): Change endpoint from `/api/v1/pos/orders/$orderId/verify-payment` to live backend route `POST /api/v1/commerce/orders/$orderId/verify-payment`.
6. **Auth & HRM Fallback Mocks Removal**:
   - In `lib/core/services/auth_service.dart`:
     - Line 480: Eliminate fallback to fake business `b1: Kopi Nusantara Mandiri`. Throw proper authentication/context error if business context is empty.
     - Line 405 (`submitAccountRecovery`): Eliminate fake ticket ID generation (`REC-YYYY-XXXX`). Send real `POST /api/v1/account-recovery` or handle error response.
   - In `lib/core/services/attendance_service.dart`:
     - Line 30 (`getPortalData`): Eliminate static `usr-local` fallback. Handle network failure cleanly without injecting fake employee data.
7. **Verification & Testing**:
   - Write comprehensive automated tests in `test/dummy_data_elimination_test.dart` asserting that starter products mock is gone, hourly trend reads live state, inventory calculates from real products, and route mismatches are resolved.
   - Run `flutter analyze` (enforce 0 errors).
   - Run `flutter test test/dummy_data_elimination_test.dart`.


## 2026-11-03T03:23:33Z
**Context**: Milestone 3 Dummy Data Elimination
**Content**: Worker_build_m4 has completed Milestone 4 with 0 analyzer errors and verified release configs. Please report your progress on the 7 module remediation items in Milestone 3, and update progress.md.
**Action**: Report current status and execution progress.


## 2026-11-03T03:41:29Z
**Context**: Milestone 3 Dummy Data Elimination
**Content**: Heartbeat status check. Please update what files you have modified so far, what step you are currently working on, and update progress.md with your latest timestamp.
**Action**: Report current status and continue execution.
