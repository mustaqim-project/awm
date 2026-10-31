## 2026-10-31T02:59:35Z
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
and the project specification at:
c:\laragon\www\cooca_core\PROJECT.md

Your role is E2E Test Writer.
Your working directory is:
c:\laragon\www\cooca_core\.agents\teamwork\e2e_test_writer_1

Mission:
Establish the E2E Testing Track infrastructure for the localization project and publish `TEST_READY.md`.

Requirements:
1. Create `TEST_INFRA.md` at project root following the prompt's `TEST_INFRA.md` template.
2. Build an automated, robust E2E test runner script at `scratch/verify_localization_suite.py` that verifies:
   - Tier 1: Deep Audit of `resources/views/app` via `python scratch/deep_audit_v2.py resources/views/app` (0 issues expected).
   - Tier 2: Deep Audit of `resources/views/admin` via `python scratch/deep_audit_v2.py resources/views/admin` (0 issues expected).
   - Tier 3: Blade Syntax Validation on all 272 view files via `php scratch/fast_syntax_check.php` (0 syntax errors expected).
   - Tier 4: Language File Parity & Syntax Verification:
     - All 66 files in `lang/id` and `lang/en` pass `php -l`.
     - 100% key parity between `lang/id/*.php` and `lang/en/*.php` (0 missing keys in either direction).
     - No duplicate or corrupted keys.
   - Tier 5: Full Laravel View Cache Verification (`php artisan view:clear && php artisan view:cache`).
3. The runner must support CLI flags (e.g. `--tier 1`, `--tier 2`, `--full`) and return exit code 0 if all tests pass, or non-zero on any failure, printing structured test results.
4. Test the runner in the current workspace (confirming it accurately detects baseline failures).
5. Publish `TEST_READY.md` at project root summarizing the test suite, test runner command, tier breakdown, and acceptance criteria.
6. Write a complete handoff report to `c:\laragon\www\cooca_core\.agents\teamwork\e2e_test_writer_1\handoff.md` and send a message when done.
Remember: DO NOT modify any view templates or translation files. Only create/edit files in `scratch/`, `TEST_INFRA.md`, and `TEST_READY.md`.
