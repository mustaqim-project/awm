## 2026-11-08T15:31:05Z
You are a Test Writer subagent (Security & QA Test Writer).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_verify\

MANDATORY FIRST STEP:
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
And read the project blueprint at:
c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Also read the handoffs from the implementation workers:
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_rbac\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_docs\handoff.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_ui\handoff.md

You MUST read and follow these skill references:
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-module-audit\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may ONLY edit or create:
tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php

DO NOT modify any production source code, seeders, or view files!

TASK SPECIFICATIONS:
Create `tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php` using standard PHPUnit/Pest Laravel feature test conventions (extending `Tests\TestCase` with `RefreshDatabase` or standard tenant mock/factory setup like existing tests in `tests/Feature/FeedbackModuleAuditTest.php` and `tests/Feature/FinanceModuleAuditTest.php`):
1. Test RBAC 403 Forbidden enforcement on Feedback endpoints:
   - Verify unauthenticated user is redirected to login or returns 401/403.
   - Verify authenticated tenant user without `feedback.view` (e.g. cashier or custom user without permission) receives 403 Forbidden on `route('feedback.bugs.index')` and `route('feedback.features.index')`.
   - Verify authenticated tenant user without `feedback.create` receives 403 Forbidden on `route('feedback.bugs.create')` and `POST route('feedback.bugs.store')`.
2. Test RBAC 200 OK access on Feedback endpoints:
   - Verify Owner and users with `feedback.view` / `feedback.create` receive 200 OK on feedback listings and creation views.
3. Test RBAC 403 Forbidden enforcement on Finance endpoints:
   - Verify user without `finance.cash_bank` receives 403 Forbidden on `route('finance.cash-bank.index')` and settlements actions.
   - Verify user without `finance.payables` receives 403 Forbidden on payables actions.
   - Verify user without `finance.receivables` receives 403 Forbidden on receivables actions.
   - Verify user without `accounting.view` receives 403 Forbidden on `route('finance.accounting.coa')`, `balance-sheet`, `trial-balance`, `general-ledger`.
4. Test SettlementProofController security:
   - Verify staff without `finance.cash_bank` receives 403 Forbidden when attempting to view settlement transfer proof.
5. Test Documentation Integration:
   - Verify `route('documentation.index')` returns 200 OK and contains `#sec-feedback` and `#sec-finance`, their 20-industry matrices, and verified route buttons.
6. Test View Compilation:
   - Programmatically assert that all 19 views across `resources/views/app/feedback` and `resources/views/app/finance` compile cleanly without errors.

VERIFICATION:
- Run `php -l tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php`.
- Run `php artisan test tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php`.
- Run full suite of related feature tests:
  `php artisan test tests/Feature/FeedbackModuleAuditTest.php tests/Feature/FinanceModuleAuditTest.php tests/Feature/DocumentationFeatureTest.php tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php`

DELIVERABLES:
Write a complete handoff report to:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_verify\handoff.md`
Report the test cases created, assertions count, syntax verification, and test outputs. Then send a completion message to the orchestrator.
