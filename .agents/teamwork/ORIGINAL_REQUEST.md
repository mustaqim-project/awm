# Original User Request

## Approved Task Prompt — 2026-11-03T02:13:01Z

Comprehensive Mobile Application Audit & OpenShift Bug Remediation across the Flutter mobile app (C:\laragon\www\cooca_my_own), referencing backend contracts at C:\laragon\www\cooca_core and connecting directly to the live production API at https://cooca.id as Single Source of Truth.

Working directory: C:\laragon\www\cooca_my_own
Integrity mode: development

## Requirements

### R1. OpenShift Investigation and Root-Cause Remediation (Stage 1 Critical)
Investigate and resolve all OpenShift bugs in the Flutter mobile application at C:\laragon\www\cooca_my_own against the backend API at https://cooca.id as the Single Source of Truth (with C:\laragon\www\cooca_core as code contract reference). Verify and fix the entire lifecycle: shift opening validation, initial cash drawer balance retrieval, outlet/branch/warehouse/user validation, API response and error handling, shift status synchronization between mobile and back office, offline handling, prevention of duplicate shift openings or shifts opened by another device, navigation on success/failure, and loading/error/success UI states. Address the root cause directly without superficial workarounds.

### R2. Android Security & Fake GPS Prevention for Attendance (Stage 1 Critical)
Implement and verify mobile-side detection and backend validation for GPS-based attendance:
- Detect Developer Options and USB Debugging with a configurable, risk-proportionate policy and clear user messaging.
- Detect Android mock locations via official Android location APIs, inspect coordinate anomalies and timestamp/accuracy consistency, and reject spoofed locations.
- Evaluate Play Integrity API and root status with graceful handling if cloud credentials are not active locally.
- Enforce pre-attendance submission checks (location permission, GPS enabled, accuracy threshold, mock location flag, geofence check) and transmit relevant integrity signals to backend endpoints for final validation before attendance is accepted.

### R3. Comprehensive Mobile Feature Audit & Elimination of Dummy/Hardcoded Data (Stage 2)
Audit all mobile pages, menus, modules, and functions (Authentication & Session, Dashboard, POS / Cashier, Shift Management, Sales & Transactions, Products & Categories, Inventory & Stock Management, Purchasing, Customers & Suppliers, Finance & Reports, HRM & Attendance, Employee Management, Settings, Notifications). Ensure 100% of dynamic business data originates from live API endpoints at https://cooca.id. Eliminate all hardcoded JSON, dummy data, mock responses, fake APIs, static lists, hardcoded tenant/user IDs, nominal amounts, permissions, and simulation fallbacks. Enforce backend role/permission and multi-industry module gating with zero cross-tenant or cross-role data leakage.

### R4. Android Production Build Configuration & Performance Hardening
Configure and verify the production Android build: enforce release build, non-debuggable (`debuggable = false`), HTTPS enforcement, removal of debug endpoints, elimination of hardcoded secrets, and proper production signing configuration. Audit performance and stability: memory leaks, widget lifecycle, API request deduplication, network timeout and retry handling, pagination, image loading, offline resilience, and responsiveness across Android screen sizes.

### R5. Verification, Automated Testing, and Audit Documentation
Perform functional testing, security testing, and build testing using automated test suites (`flutter analyze`, existing repository tests in cooca_my_own and cooca_core, and newly authored unit, widget, and integration tests) and verify the Android release build. Generate the five required audit documentation reports:
- `mobile_comprehensive_audit_report.md`
- `mobile_bug_fix_report.md`
- `mobile_api_integration_audit.md`
- `android_security_attendance_report.md`
- `mobile_testing_results.md`
Each finding in the reports must include: Feature Name, File Location, Problem Description, Root Cause, Risk Level (Critical, High, Medium, Low), Remediation Applied, Test Verification Results, and Status (Fixed, Verified, Pending).

## Acceptance Criteria

### Stage 1: OpenShift Remediation & Attendance Security
- [ ] OpenShift flow completes successfully end-to-end against https://cooca.id, correctly handling initial cash retrieval, outlet/user validation, and error states without crashing.
- [ ] Duplicate shift creation and cross-device shift conflicts are prevented and handled with appropriate user feedback.
- [ ] Attendance flow detects mock location / Fake GPS and rejects attendance attempts with location spoofing on client and backend.
- [ ] Developer Options and USB Debugging status are detected with clear user-facing guidance without misclassifying safe devices as Fake GPS.

### Stage 2: Feature Audit & Backend Integration
- [ ] All dynamic business data across audited mobile modules is fetched from https://cooca.id; zero active dummy data, mock responses, or static simulation data remain in production code paths.
- [ ] Role, permission, and multi-industry module visibility strictly conform to backend permissions, preventing unauthorized access via direct navigation.

### Build Integrity & Quality
- [ ] Android release configuration is verified non-debuggable (`debuggable = false`) with release signing and HTTPS.
- [ ] `flutter analyze` reports zero errors, and all automated unit/widget/integration tests pass.
- [ ] Android release build (`flutter build apk --release` / app bundle) succeeds.

### Documentation Deliverables
- [ ] All five audit reports (`mobile_comprehensive_audit_report.md`, `mobile_bug_fix_report.md`, `mobile_api_integration_audit.md`, `android_security_attendance_report.md`, `mobile_testing_results.md`) are created with complete structured finding records.

## Follow-up — 2026-11-03T05:24:18Z

All test discrepancies and static analysis issues are resolved. flutter analyze reports 0 issues (clean), flutter test passes 98/98 tests across the full suite (100% green). You may proceed with Victory Audit Round 2 immediately.
