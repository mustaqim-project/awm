## 2026-11-08T06:45:54Z
You are an Explorer subagent (Explorer 3: Feedback & Finance Module Health, Tests & Docs Survey).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_docs\

MANDATORY FIRST STEP:
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

You MUST read and follow these skill references:
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-module-audit\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\business-owner-doc-generator\SKILL.md

YOUR MISSION:
Exhaustively investigate the module health, existing test suites, error handling, and business owner documentation structure for Feedback and Finance in COOCA Core.

SPECIFIC SURVEY CHECKS:
1. Module Health & Error Handling:
   - Check input validation, exception handling, flash notifications (`session('success')`, `session('error')`), and empty state views in Feedback and Finance.
   - Look for potential runtime exceptions, undefined variables, or unhandled null states.
2. Existing Test Coverage:
   - Search `tests/Feature` and `tests/Unit` for any tests covering Feedback and Finance.
   - Run tests if applicable (`php artisan test --filter=...`) via command or inspect test code to see what passes or fails.
   - Note what tests exist and what test coverage is missing to verify acceptance criteria (zero overlap, 403 on unauthorized, route verification).
3. Business Owner User Guide Documentation:
   - Inspect existing documentation structure in `resources/views/app/docs/`:
     - Inspect `resources/views/app/docs/index.blade.php` and `resources/views/app/docs/partials/`.
     - How are modules documented (e.g. `sec_customers.blade.php`, `sec_crm.blade.php`, `sec_community.blade.php`, etc.)?
     - What format and structure is used (Bento Apple HIG, 4-step workflow, 20-industry matrix, direct verified `route(...)` links)?
     - How is the Left Sidebar TOC updated in `docs/index.blade.php`?
     - Determine the exact requirements for `sec_feedback.blade.php` and `sec_finance.blade.php`.

DELIVERABLE:
Write a comprehensive report to:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_docs\audit_docs_survey_report.md
And write your final handoff to:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_docs\handoff.md

## 2026-11-08T14:21:39Z
**Context**: Server restart recovery
**Content**: A server restart occurred and paused tasks. Please resume your Phase 0 survey of module health, existing test suites, error handling, and business owner documentation structure for Feedback and Finance in `resources/views/app/docs/`.
**Action**: Finish your survey, write `c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_docs\audit_docs_survey_report.md` and `handoff.md`, then send a completion message back.
