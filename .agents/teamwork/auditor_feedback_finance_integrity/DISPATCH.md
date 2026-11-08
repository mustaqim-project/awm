## 2026-11-08T15:57:06Z
You are a Forensic Auditor subagent (Forensic Integrity Auditor).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\auditor_feedback_finance_integrity\

MANDATORY FIRST STEP:
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
And read the project blueprint and feature inventory at:
c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Also read all worker and test handoffs:
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_rbac\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_docs\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_ui\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_verify\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_settlement_proof_fix\handoff.md

You MUST read and follow these skill references:
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-module-audit\SKILL.md

YOUR MISSION:
Perform an exhaustive forensic integrity audit across all changes made for Feedback and Finance modules:
1. Genuine Implementation Audit:
   - Verify that all code changes contain authentic business logic and not mock/stub bypasses.
   - Check if any test results, assertions, or expected outputs were hardcoded in production source code or seeders.
   - Verify that `database/seeders/RbacSeeder.php` genuinely creates permissions and maps roles.
   - Verify that `FeedbackWebController.php` genuinely enforces middleware authorization and not dummy passes.
   - Verify that `SettlementProofController.php` genuinely verifies `Context::hasPermission('finance.cash_bank')`.
   - Verify that `sec_feedback.blade.php` and `sec_finance.blade.php` are genuine, comprehensive documentation files (≥ 400 lines and ≥ 600 lines) with real 20-industry matrices, and not copy-paste placeholders.
   - Verify that view `@can(...)` guards in `payables.blade.php`, `receivables.blade.php`, etc., are genuine authorization directives.
2. Git Diff & Change Footprint Verification:
   - Check git status and inspect modified files to ensure no malicious code, backdoor, or unrelated files were altered.
3. Automated Integrity Verification:
   - Run tests: `php artisan test tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php`
   - Run syntax check: `php -l` on all modified files.

DELIVERABLES:
Write your forensic audit report to:
`c:\laragon\www\cooca_core\.agents\teamwork\auditor_feedback_finance_integrity\audit_report.md`
And write your handoff to:
`c:\laragon\www\cooca_core\.agents\teamwork\auditor_feedback_finance_integrity\handoff.md`
Your handoff must clearly state your verdict: **CLEAN** (zero integrity violations, all implementations genuine) or **INTEGRITY VIOLATION**.
Then send a completion message to the orchestrator.
