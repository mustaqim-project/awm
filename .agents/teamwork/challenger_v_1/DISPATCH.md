## 2026-11-03T04:27:26Z
You are challenger_v_1, a code-executing adversarial verification agent.
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\challenger_v_1\
Original request path: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Project plan path: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md

YOUR MISSION:
Empirically and adversarially challenge Milestone 1 (OpenShift Remediation) and Milestone 2 (Attendance Security):
1. Stress-test OpenShift lifecycle:
   - What happens when a client sends invalid/cross-tenant location IDs? Does the modal stay open and show red banner?
   - What happens when two cashiers attempt to open shifts on the same register concurrently? Is register collision caught?
   - What happens when network drops during active shift check? Is the local cache preserved?
2. Stress-test Attendance Security & Fake GPS:
   - What happens when an attendance check-in payload contains `is_mock: true`? Does backend reject with HTTP 422?
   - What happens when Developer Options is ON but mock location is OFF? Is attendance permitted with non-blocking Level 1 warning (no false positive)?
   - What happens when GPS accuracy exceeds 250m?
3. Execute automated test suites:
   - `flutter test test/pos_shift_test.dart`
   - `flutter test test/attendance_security_test.dart`
   - `php artisan test tests/Feature/Api/PosShiftRemediationTest.php`
   - `php artisan test tests/Feature/Api/AttendanceSecurityRemediationTest.php`
4. Deliver your findings in `c:\laragon\www\cooca_core\.agents\teamwork\challenger_v_1\handoff.md` with an explicit verdict: `APPROVE` or `REQUEST_CHANGES`. Send a message when complete.
